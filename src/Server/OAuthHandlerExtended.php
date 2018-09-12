<?php

namespace Ever2BoostPHP\Server;

use Evernote\Auth\OauthHandler;
use Evernote\Exception\AuthorizationDeniedException;

/**
 * Class OAuthHandlerExtended
 *
 * @package Ever2BoostPHP\Server
 */
final class OAuthHandlerExtended extends OauthHandler
{
    /**
     * I changed this method to return url as string
     *
     * @param       $consumer_key
     * @param       $consumer_secret
     * @param       $callback
     * @param array $arr
     *
     * @return mixed|string
     * @throws AuthorizationDeniedException
     */
    public function authorize($consumer_key, $consumer_secret, $callback, $arr = [])
    {
        $this->params['oauth_callback'] = $callback;
        $this->params['oauth_consumer_key'] = $consumer_key;

        $this->consumer_secret = $consumer_secret;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // first call
        if ( ! array_key_exists('oauth_verifier', $arr) && ! array_key_exists('oauth_token', $arr)) {
            unset($this->params['oauth_token']);
            unset($this->params['oauth_verifier']);

            $temporaryCredentials = $this->getTemporaryCredentials();

            $_SESSION['oauth_token_secret'] = $temporaryCredentials['oauth_token_secret'];

            $authorizationUrl = $this->getBaseUrl('OAuth.action?oauth_token=')
                .$temporaryCredentials['oauth_token'];

            if ($this->supportLinkedSandbox) {
                $authorizationUrl .= '&supportLinkedSandbox=true';
            }

            return $authorizationUrl;

            // the user declined the authorization
        } elseif ( ! array_key_exists('oauth_verifier', $arr) && array_key_exists('oauth_token', $arr)) {
            throw new AuthorizationDeniedException('Authorization declined.');
            //the user authorized the app
        } else {
            $this->token_secret = $_SESSION['oauth_token_secret'];

            $this->params['oauth_token'] = $arr['oauth_token'];
            $this->params['oauth_verifier'] = $arr['oauth_verifier'];
            unset($this->params['oauth_callback']);

            return $this->getTemporaryCredentials();
        }
    }
}
