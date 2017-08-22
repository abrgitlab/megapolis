<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:58
 */
class Config
{

    /**
     * @var $lock boolean
     */
    public $lock;

    /**
     * @var $next_time int
     */
    public $next_time;

    /**
     * @var $long bool
     */
    public $long;

    /**
     * @var $telegram bool
     */
    public $telegram = false;

    /**
     * @var $telegram_recipient int
     */
    public $telegram_recipient;

    /**
     * @inheritdoc
     */
    function __construct()
    {
        if (file_exists(MEGAPOLIS_PATH . '/config.json')) {
            $config = json_decode(file_get_contents(MEGAPOLIS_PATH . '/config.json'), true);
            if ($config == null) {
                Bot::log('Конфиг невозможно распарсить', [Bot::$DEBUG]);
                //if (Bot::$options['debug']) echo "Конфиг невозможно распарсить\n";
            } else {
                $this->lock = (isset($config['lock'])) ? $config['lock'] : false;
                $this->next_time = (isset($config['next_time'])) ? $config['next_time'] : null;
                $this->long = (isset($config['long'])) ? $config['long'] : false;
                $this->telegram = (isset($config['telegram'])) ? $config['telegram'] : false;
                $this->telegram_recipient = (isset($config['telegram_recipient'])) ? $config['telegram_recipient'] : null;
            }
        } else {
            Bot::log('Конфиг не найден', [Bot::$DEBUG]);
            //if (Bot::$options['debug']) echo "Конфиг не найден\n";
        }
    }

    /**
     *
     */
    public function getDateParams($time) {
        return [
            'min' => date('i', $time),
            'hour' => date('H', $time),
            'dow' => date('N', $time)
        ];
    }

    /**
     * Записывает данные конфига в файл
     */
    public function commit() {
        $config = ['lock' => $this->lock];
        if ($this->next_time)
            $config['next_time'] = $this->next_time;
        if ($this->long)
            $config['long'] = $this->long;
        if ($this->telegram)
            $config['telegram'] = $this->telegram;
        if ($this->telegram_recipient != null)
            $config['telegram_recipient'] = $this->telegram_recipient;

        file_put_contents(MEGAPOLIS_PATH . '/config.json', json_encode($config));
    }

    /**
     * Генерирует следующее время выполнения скрипта
     */
    public function generateNextStartTime() {
        $time = time(); //TODO: раскомментировать после египетского квеста
//        $dateParams = $this->getDateParams($time);
//        if ($dateParams['hour'] >= 8)
//            $this->next_time = $time + rand(3600, 5400);
//        elseif (($dateParams['dow'] == 6 && $dateParams['hour'] > 2 || $dateParams['dow'] == 7 && $dateParams['hour'] > 3) && $dateParams['hour'] < 12)
//            $this->next_time = strtotime('12:00', $time) + rand(0, 1800);
//        elseif ($dateParams['dow'] >= 1 && $dateParams['dow'] <= 5 && $dateParams['hour'] > 1 && $dateParams['hour'] < 8)
//            $this->next_time = strtotime('08:00', $time) + rand(0, 1800);
//        else
//            $this->next_time = $time + rand(3600, 5400);
        $this->next_time = $time + rand(3700, 3900);
    }

}