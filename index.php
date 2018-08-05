<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.08.17
 * Time: 14:12
 */

require_once 'classes/Bot.php';

require_once 'vendor/autoload.php';

define('MEGAPOLIS_PATH', __DIR__);

if (!isset($_GET['password']) || $_GET['password'] != '96Z%G2~U2C') {
    header('HTTP/1.1 404 Not Found');
    die;
}

try {
    $telegram = new Longman\TelegramBot\Telegram(Bot::$telegram_bot_token, 'abr_mega_bot');
} catch (\Longman\TelegramBot\Exception\TelegramException $e) {
    header('HTTP/1.1 404 Not Found');
    die;
}

$message = null;
try {
    $telegram->handle();
    $message = \GuzzleHttp\json_decode($telegram->getCustomInput());
} catch (Longman\TelegramBot\Exception\TelegramException $e) {}

if ($message != null && isset($message->message->text) && isset($message->message->from->id)) {
    Bot::$options['telegram'] = true;
    Bot::$options['telegram_recipient'] = $message->message->from->id;

    if ($message->message->text == '/ping') {
        Bot::log('pong', [Bot::$TELEGRAM]);
    } elseif (in_array($message->message->from->id, Bot::$telegram_permitted_senders)) {
        if ($message->message->text == '/runlong' || $message->message->text == '/run') {
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
        } elseif ($message->message->text == '/start') {
            Bot::log('Привет! Стартуй с запуском коротких контрактов /run или с запуском длинных контрактов /runlong', [Bot::$TELEGRAM]);
        } elseif ($message->message->text == '/attach') {
            $config = new Config();

            if (!$config->lock) {
                Bot::log('Не к чему приаттачиватся', [Bot::$TELEGRAM]);
                return;
            }

            if (isset(Bot::$options['telegram']) && isset(Bot::$options['telegram_recipient'])) {
                Bot::log('Уже приаттачены', [Bot::$TELEGRAM]);
                return;
            }

            file_put_contents(MEGAPOLIS_PATH . '/attach_telegram.json', json_encode([
                'telegram' => true,
                'telegram_recipient' => $message->message->from->id
            ]));

            Bot::log('Приаттачились к логу', [Bot::$TELEGRAM]);
            return;
        }
    } else {
        Bot::log('Hello! Sorry, but you have no permissions for using this bot.', [Bot::$TELEGRAM]);
    }
} elseif ($message == null) {
    header('HTTP/1.1 404 Not Found');
}
