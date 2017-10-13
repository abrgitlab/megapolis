<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.08.17
 * Time: 13:30
 */

require_once 'vendor/autoload.php';

define('MEGAPOLIS_PATH', __DIR__);

//$bot = new Longman\TelegramBot\Telegram('411382774:AAHjTH-9dxBfecr8RTd4anfIFWzcSmy4xMU', 'abr_mega_bot');
//$bot->setWebhook('https://mega.abr-daemon.ru/?password=96Z%G2~U2C', ['certificate' => MEGAPOLIS_PATH . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'wildcard.abr-daemon.ru.pem']);

$chatBot = new Longman\TelegramBot\Telegram('476485548:AAGk5SIqPFRNlAMuueu1l_dnLEiZtTnSI9U', 'abr_mega_chat_bot');
$chatBot->setWebhook('https://mega-chat.abr-daemon.ru/?password=96Z%G2~U2C', ['certificate' => MEGAPOLIS_PATH . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'wildcard.abr-daemon.ru.pem']);
?>