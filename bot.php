<?php
/**
 * IRC Bot
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
 */
chdir(dirname(__FILE__));
require_once('core/sirousagi.php');

define('PID_FILE', 'bot.pid');

if (file_exists(PID_FILE)) {
    echo 'pid file exists' . "\n";
    exit;
}

if ($fp = fopen(PID_FILE, 'w')) {
    fwrite($fp, getmypid());
    fclose($fp);
}

BotSirousagi::create()->start();

unlink(PID_FILE);
