<?php
/**
 * Shout application interface.
 *
 * This file defines Horde's core API interface. Other core Horde libraries
 * can interact with Operator through this API.
 *
 * Copyright 2006-2010 Alkaloid Networks (http://projects.alkaloid.net/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you did not
 * did not receive this file, see
 * http://www.opensource.org/licenses/bsd-license.html.
 *
 * @author  Ben Klang <ben@alkaloid.net>
 * @package Operator
 */

if (!defined('OPERATOR_BASE')) {
    define('OPERATOR_BASE', dirname(__FILE__). '/..');
}

if (!defined('HORDE_BASE')) {
    /* If horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(OPERATOR_BASE. '/config/horde.local.php')) {
        include OPERATOR_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', OPERATOR_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Operator_Application extends Horde_Registry_Application
{
    /**
     * The application's version.
     *
     * @var string
     */
    public $version = 'H4 (1.0-git)';

    /**
     * TODO
     */
    public $driver = null;

    /**
     * TODO
     */
    static protected $_perms = array();

    /**
     * Initialization function.
     *
     * Global variables defined:
     *   $cache - TODO
     */
    protected function _init()
    {
        // Operator backend.
        $this->driver = Operator_Driver::factory();

        // Caching system for storing DB results
        $GLOBALS['cache'] = Horde_Cache::singleton($GLOBALS['conf']['cache']['driver'], Horde::getDriverConfig('cache', $GLOBALS['conf']['cache']['driver']));
    }

    /**
     * TODO
     */
    public function perms()
    {
        if (!empty(self::$_perms)) {
            return self::$_perms;
        }

        self::$_perms['tree']['operator']['accountcodes'] = false;
        self::$_perms['title']['operator:accountcodes'] = _("Account Codes");

        $accountcodes = Operator::getAccountCodes();
        foreach ($accountcodes as $accountcode) {
            self::$_perms['tree']['operator']['accountcodes'][$accountcode] = false;
            self::$_perms['title']['operator:accountcodes:' . $accountcode] = $accountcode;
        }

        return self::$_perms;
    }

}
