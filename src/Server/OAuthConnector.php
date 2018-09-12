<?php

namespace Ever2BoostPHP\Server;

use Ever2BoostPHP\Command\Browser;
use Ever2BoostPHP\Command\Ever2BoostPHP;
use Ever2BoostPHP\Helper\App;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Socket\Server;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class OAuthConnector
 *
 * @package Ever2BoostPHP\Server
 */
class OAuthConnector
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $oauthToken;

    /**
     * OAuthConnector constructor.
     *
     * @param LoopInterface   $loop
     * @param int             $port
     * @param bool            $sandbox
     * @param string          $consumerKey
     * @param string          $consumerSecret
     * @param Browser         $browser
     * @param OutputInterface $output
     * @param Filesystem      $filesystem
     */
    public function __construct(
        LoopInterface $loop,
        int $port,
        bool $sandbox,
        string $consumerKey,
        string $consumerSecret,
        Browser $browser,
        OutputInterface $output,
        Filesystem $filesystem
    ) {
        $this->loop = $loop;
        $this->port = $port;
        $this->sandbox = $sandbox;
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->browser = $browser;
        $this->output = $output;
        $this->filesystem = $filesystem;
    }

    public function receiveToken(): string
    {
        $socket = new Server($this->port, $this->loop);
        $urlCallback = str_replace('tcp:', 'http:', $socket->getAddress());
        $oauthHandler = new OAuthHandlerExtended($this->sandbox);
        $urlOauth = $oauthHandler->authorize($this->consumerKey, $this->consumerSecret, $urlCallback);

        $this->browser->open($urlOauth);
        $this->output->writeln([
            \sprintf(
                'If it did not open any browser, open this url: %s',
                $urlOauth
            ),
            '',
        ]);

        $serverRequest = new \React\Http\Server(function (ServerRequestInterface $request) use (
            $urlCallback
        ) {
            $queryStringArray = [];
            \parse_str($request->getUri()->getQuery(), $queryStringArray);

            if (0 === \count($queryStringArray)) {
                return new Response(200, [
                    'Content-Type' => 'text/plain',
                ], "No querystring, wait the other...\n");
            }

            /* the sdk is like a black magic, so I wont modified to much the class */
            $oauthHandler = new OAuthHandlerExtended($this->sandbox);
            $token = $oauthHandler->authorize($this->consumerKey, $this->consumerSecret, $urlCallback, [
                'oauth_token' => $queryStringArray['oauth_token'],
                'oauth_verifier' => $queryStringArray['oauth_verifier'],
            ]);

            $this->filesystem->dumpFile(
                App::homeFolder().'/'.Ever2BoostPHP::TOKEN_FILENAME,
                $token['oauth_token']
            );

            $this->oauthToken = $token['oauth_token'];
            $this->loop->stop();
        });

        $serverRequest->listen($socket);
        $this->loop->run();

        return $this->oauthToken;
    }
}
