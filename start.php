<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */

require_once 'classes/Bot.php';
require_once 'classes/Config.php';
require_once 'classes/Game.php';
require_once 'classes/Room.php';
require_once 'classes/Friend.php';

define('BASE_PATH', __DIR__);

$bot = new Bot();
$bot->start();