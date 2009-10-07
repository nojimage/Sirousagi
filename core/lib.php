<?php
/**
 * Path setting & include
 *
 * PHP versions 5
 *
 * Copyright 2009, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @version    Sirousagi 0.4
 * @author     nojimage <nojimage at gmail.com>
 * @copyright  2009 nojimage
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://php-tips.com/
 * @package    sirousagi
 * @subpackage sirousagi.core
 * @since      File available since Release 0.1
 * @modifiedby nojimage <nojimage at gmail.com>
 *
 */

if (!defined('DS')) {
    /**
     * DIRECTORY_SEPARATOR alias
     * @var string
     */
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('PS')) {
    /**
     * PATH_SEPARATOR alias
     * @var string
     */
    define('PS', PATH_SEPARATOR);
}

/**
 * Bot Root dir
 * @var string
 */
define('ROOT', dirname(dirname(__FILE__)));

/**
 * Core lib files dir
 * @var string
 */
define('CORE', dirname(__FILE__));

/**
 * Config files dir
 * @var string
 */
define('CONFIG_DIR', ROOT . DS . 'config' . DS);

/**
 * 
 * @var string
 */
define('EXTLIBS', ROOT . DS . 'extlibs' . DS);

/**
 * 
 * @var string
 */
define('EXTLIBS_PEAR', EXTLIBS . DS . 'pear' . DS);

// -- set include 
set_include_path(CORE . PS . EXTLIBS . PS . EXTLIBS_PEAR . PS . get_include_path());

// -- load libs
include_once(CONFIG_DIR .'core.php');
include_once('basic.php');
include_once('smart_irc_ja.php');
include_once('feature_base.php');