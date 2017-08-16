<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.08.17
 * Time: 13:30
 */

require_once 'vendor/autoload.php';

define('MEGAPOLIS_PATH', __DIR__);

$bot = new Longman\TelegramBot\Telegram('411382774:AAHjTH-9dxBfecr8RTd4anfIFWzcSmy4xMU', 'abr_mega_bot');
$bot->setWebhook('https://mega.abr-daemon.ru/', ['certificate' => MEGAPOLIS_PATH . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'wildcard.abr-daemon.ru.pem']);

?>