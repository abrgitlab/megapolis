<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */

require_once 'classes/Bot.php';
require_once 'vendor/autoload.php';

define('MEGAPOLIS_PATH', __DIR__);

$bot = new Bot();
$bot->start();