<?php

namespace Ever2BoostPHP\Command;


use Symfony\Component\Process\Process;

/**
 * Class Browser
 *
 * @package Ever2BoostPHP\Command
 */
class Browser
{
    private const OS_LINUX = 'LINUX';
    private const OS_MACOS = 'DARWIN';

    /**
     * @var Process
     */
    private $process;

    /**
     * Browser constructor.
     *
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Open url on browser
     *
     * @param string $url
     */
    public function open(string $url): void
    {
        $os = \php_uname('s');
        $command = '';
        switch (strtoupper($os)) {
            case self::OS_LINUX:
                $command = 'xdg-open';
                break;

            case self::OS_MACOS:
                $command = 'open';
                break;

            default:
                return;
        }

        $process = $this->process->setCommandLine(\sprintf('%s %s', $command, $url));
        $process->start();
    }
}
