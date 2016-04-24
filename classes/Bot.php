<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */
class Bot
{

    public static $host = 'web155.socialquantum.com';
    public static $host_static = 'mb.static.socialquantum.ru';
    public static $build = '12149';
    public static $client_version = '2.85';
    public static $iauth = '277997eba7f4e51051b0a0a9450afe73';
    public static $user_id = 'UD_5cd98e974c0fec35013c4790';

    /**
     * @var $options array
     */
    public static $options;

    /**
     * @var $tidy Tidy
     */
    public static $tidy;

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
     * @var $config Config
     */
    private $config;

    /**
     * @var $game Game
     */
    private static $game;

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

        Bot::$tidy = new Tidy();
        Bot::$tidy_config = [
            'indent' => true,
            'clean' => true,
            'input-xml' => true,
            'output-xml' => true,
            'wrap' => false
        ];

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
        $this->config = new Config();

        $this->config->lock = true;
        $this->config->next_time = null;
        $this->config->commit();

        Bot::$game = new Game();
        Bot::$game->changeRoom(0);
        Bot::$game->loadFriends();
        Bot::$game->visitFriends();
        //Bot::$game->sendGifts(); //TODO: хреново работает
        Bot::$game->receiveGifts();
        Bot::$game->sendFriendsToGamblingZone();
        //Bot::$game->openChest();
        Bot::$game->getRoom()->signContracts();
        if (!isset(Bot::$options['manual'])) //Временно блокируем основную локацию от получения монет во время ручного запуска
            Bot::$game->getRoom()->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(5);
        Bot::$game->getRoom()->signContracts();
        Bot::$game->getRoom()->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(2);
        Bot::$game->getRoom()->signContracts();
        Bot::$game->getRoom()->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(4);
        Bot::$game->getRoom()->casinoPickFriends();
        Bot::$game->getRoom()->signContracts();
        Bot::$game->getRoom()->getCoins();
        Bot::$game->applyHelp();

        Bot::$game->changeRoom(1);
        Bot::$game->getRoom()->signContracts();
        Bot::$game->getRoom()->getCoins();
        Bot::$game->applyHelp();

        $this->config->generateNextStartTime();
        $this->config->lock = false;
        $this->config->commit();

        echo 'Выполнено в ' . date('H:i:s') . "\n";
        echo 'Следующее выполнение - не раньше ' . date('H:i:s', $this->config->next_time) . "\n";
    }

    public static function getGame() {
        return Bot::$game;
    }

}