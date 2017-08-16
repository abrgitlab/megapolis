<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.08.17
 * Time: 14:12
 */

require_once 'classes/Bot.php';
require_once 'classes/Config.php';

require_once 'vendor/autoload.php';

define('MEGAPOLIS_PATH', __DIR__);

$telegram = new Longman\TelegramBot\Telegram(Bot::$telegram_bot_token, 'abr_mega_bot');

$message = null;
try {
    $telegram->handle();
    $message = \GuzzleHttp\json_decode($telegram->getCustomInput());
} catch (Longman\TelegramBot\Exception\TelegramException $e) {}

if ($message != null && isset($message->message->text) && isset($message->message->from->id)) {
    if (in_array($message->message->from->id, Bot::$telegram_permitted_senders)) {
        if ($message->message->text == '/runlong' || $message->message->text == '/run') {
            Bot::$options['telegram'] = true;
            Bot::$options['telegram_recipient'] = $message->message->from->id;

            $config = new Config();

            if ($config->lock) {
                Bot::log('Выполнение скрипта заблокировано параметром lock в конфиге', [Bot::$TELEGRAM]);
                return;
            }

            $config->long = ($message->message->text == '/runlong');
            $config->telegram = true;
            $config->telegram_recipient = $message->message->from->id;
            $config->commit();

            $seconds_left = 61 - date('s');
            if ($seconds_left > 59)
                $seconds_left = 60 - $seconds_left;
            Bot::log("Запуск через $seconds_left сек.", [Bot::$TELEGRAM]);
        }
    }
} elseif ($message == null) {
    header('HTTP/1.1 404 Not Found');
}
