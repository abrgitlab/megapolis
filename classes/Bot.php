<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */

require_once 'Config.php';
require_once 'Game.php';
require_once 'Room.php';

class Bot
{

    public static $host = 'web155.socialquantum.com';
    public static $host_static = 'mb.static.socialquantum.ru';
    public static $build = '21751';
    public static $client_version = '3.91';
    public static $iauth = '277997eba7f4e51051b0a0a9450afe73';
    public static $user_id = 'UD_5cd98e974c0fec35013c4790';
    public static $odin_id = '949c34f735162b0bd21f1f63db51cc2bb9e935ac';
    public static $android_id = 'f337e0e35a1e6dd5';
    public static $device_id = '0d594f8e-f575-3c25-901a-75d76d79af8c';
    public static $mac = '0800270cc3c5';
    public static $advertising_id = 'e4959f11-12a8-4cb1-a5d3-0c3649406e3b';
    public static $telegram_bot_token = '411382774:AAHjTH-9dxBfecr8RTd4anfIFWzcSmy4xMU';
    public static $telegram_permitted_senders = [221931497/*, 419035810*/];

    public static $STDOUT = 'stdout';
    public static $TELEGRAM = 'telegram';
    public static $DEBUG = 'debug';

    /**
     * @var $options array
     */
    public static $options;

    /**
     * @var $tidy_config array
     */
    public static $tidy_config;

    /**
     * @var $curl resource
     */
    public static $curl;

    /**
     * @var $last_room_id int
     */
    public static $last_room_id;

    /**
     * @var $game Game
     */
    public static $game;

    /**
     * @var $telegram Longman\TelegramBot\Telegram
     */
    public static $telegram;

    /**
     * @inheritdoc
     */
    function __construct()
    {
        Bot::$options = getopt('D', ['long', 'manual', 'force', 'debug']);
        Bot::$options['long'] = isset(Bot::$options['long']);
        Bot::$options['manual'] = isset(Bot::$options['manual']);
        Bot::$options['force'] = isset(Bot::$options['force']);
        Bot::$options['debug'] = isset(Bot::$options['D']) || isset(Bot::$options['debug']);
        Bot::$options['telegram'] = false;
        Bot::$options['telegram_recipient'] = null;

        Bot::$curl = curl_init();
        curl_setopt(Bot::$curl, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . Bot::$client_version . '.' . Bot::$build, 'Accept: */*', 'Accept-Encoding: gzip'));
        curl_setopt(Bot::$curl, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * @inheritdoc
     */
    function __destruct()
    {
        curl_close(Bot::$curl);
    }

    /**
     * Запускает бота
     */
    public function start() {
        $config = new Config();

        Bot::$options['telegram'] = $config->telegram;
        if (Bot::$options['telegram'] == true) {
            if (($config->telegram_recipient == null || !in_array($config->telegram_recipient, Bot::$telegram_permitted_senders))) {
                Bot::log('Попытка несанкционированного запуска бота из телеграм от ID ' . $config->telegram_recipient);
                $config->long = false;
                $config->telegram = false;
                $config->telegram_recipient = null;
                $config->commit();
                return;
            }
            Bot::$options['telegram_recipient'] = $config->telegram_recipient;
            Bot::$options['manual'] = true;
            Bot::log('Логирование будет также направлятся и в телеграм', [Bot::$DEBUG]);
            Bot::$telegram = new Longman\TelegramBot\Telegram(Bot::$telegram_bot_token, 'abr_mega_bot');
        }

        if (isset($config->long)) {
            Bot::$options['long'] = true;
        }

        if (!Bot::$options['force']) {
            if ($config->lock == true) {
                Bot::log('Выполнение скрипта заблокировано параметром lock в конфиге', (Bot::$options['telegram']) ? [Bot::$DEBUG, Bot::$TELEGRAM] : [Bot::$DEBUG]);
                return;
            }
            if ($config->next_time != null) {
                if (time() < $config->next_time && !Bot::$options['manual']) {
                    Bot::log('Время следующего выполнения щё не наступило. Скрипт запустится не раньше ' . date('H:i:s', $config->next_time), [Bot::$DEBUG]);
                    return;
                }
            }
        }

        $dateParams = $config->getDateParams(time());
        if (!Bot::$options['manual']) {
            //Пн-Пт > 21:30
            if ($dateParams['dow'] >= 1 && $dateParams['dow'] <= 5 && ($dateParams['hour'] == 21 && $dateParams['min'] >= 30 || $dateParams['hour'] > 21))
                Bot::$options['long'] = true;

            //Все дни > 23:30
            if ($dateParams['hour'] == 23 && $dateParams['min'] >= 30)
                Bot::$options['long'] = true;

            //Все дни < 08:00
            if ($dateParams['hour'] < 8)
                Bot::$options['long'] = true;

            //Сб, Вс < 12:00
            if (($dateParams['dow'] == 7 || $dateParams['dow'] == 6) && $dateParams['hour'] < 12)
                Bot::$options['long'] = true;
        }

        if (Bot::$options['long'])
            Bot::log('Будут подписаны длинные контракты', [Bot::$DEBUG]);
        else
            Bot::log('Будут подписаны короткие контракты', [Bot::$DEBUG]);

        $config->lock = true;
        $config->next_time = null;
        $config->long = false;
        $config->telegram = false;
        $config->telegram_recipient = null;
        $config->commit();

        Bot::$game = new Game();
        Bot::$game->loadFriends();
        if (Bot::$game->room->id != 0)
            Bot::$game->changeRoom(0);
        Bot::$game->visitFriends();
        if (date('H') < 22 || date('H') == 23 && date('i') >= 20)
            Bot::$game->sendGifts();
        else
            Bot::$game->sendGifts(true);
        Bot::$game->receiveGifts();
        Bot::$game->acceptFriends();
        Bot::$game->sendFriendsToGamblingZone();
        Bot::$game->sendFuelToFriends();
        Bot::$game->discardAskMaterial();
        Bot::$game->handleLetters();
        Bot::$game->openChest();
        Bot::$game->room->doFactoryWork('chinese');
        Bot::$game->room->doFactoryWork('egyptian');
        Bot::$game->room->doFactoryWork('middle_ages');
        Bot::$game->room->doMilitaryWork();
        Bot::$game->room->signContracts();
        if (!Bot::$options['manual'])
            Bot::$game->room->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(5);
        Bot::$game->room->signContracts();
        Bot::$game->room->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(2);
        Bot::$game->room->signContracts();
        Bot::$game->room->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(4);
        Bot::$game->room->casinoPickFriends();
        Bot::$game->room->signContracts();
        Bot::$game->room->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(1);
        Bot::$game->room->signContracts();
        Bot::$game->room->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(0);
        Bot::$game->showLetters();

        $config->generateNextStartTime();
        $config->lock = false;
        $config->commit();

        Bot::log('Выполнено в ' . date('H:i:s'), [Bot::$STDOUT, Bot::$TELEGRAM]);
        Bot::log('Следующее выполнение - не раньше ' . date('H:i:s', $config->next_time), [Bot::$STDOUT, Bot::$TELEGRAM]);
    }

    public static function log($text, $options = ['stdout']) {
        if (Bot::$options[Bot::$TELEGRAM] && in_array(Bot::$TELEGRAM, $options)) {
            Longman\TelegramBot\Request::sendMessage(['chat_id' => Bot::$options['telegram_recipient'], 'text' => $text]);
        }
        if (in_array(Bot::$STDOUT, $options) || in_array(Bot::$DEBUG, $options) && Bot::$options[Bot::$DEBUG]) {
            echo "$text\n";
        }
    }

}
