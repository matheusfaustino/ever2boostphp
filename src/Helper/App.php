<?php

namespace Ever2BoostPHP\Helper;

use Ever2BoostPHP\Command\Ever2BoostPHP;

/**
 * Class UserInfo
 *
 * @package Ever2BoostPHP\Helper
 */
class App
{
    /**
     * @see https://stackoverflow.com/a/32528391/3618650
     * @return string
     */
    public static function homeFolder(): string
    {
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = \getenv('HOME');
        if ( ! empty($home)) {
            // home should never end with a trailing slash.
            $home = \rtrim($home, '/');
        } elseif ( ! empty($_SERVER['HOMEDRIVE']) && ! empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = \rtrim($home, '\\/');
        }

        return $home ? $home.'/.'.Ever2BoostPHP::NAME : '';
    }
}
