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
     * @inheritdoc
     */
    function __construct()
    {
        if (file_exists(BASE_PATH . '/config.json')) {
            $config = json_decode(file_get_contents(BASE_PATH . '/config.json'), true);
            if ($config == null) {
                if (Bot::$options['debug']) echo "Конфиг невозможно распарсить\n";
            } else {
                if (!isset(Bot::$options['force'])) {
                    if (isset($config['lock']) && $config['lock'] == true) {
                        if (Bot::$options['debug']) echo "Выполнение скрипта заблокировано параметром lock в конфиге\n";
                        exit;
                    }
                    if (isset($config['next_time'])) {
                        $this->next_time = $config['next_time'];
                        if (time() < $this->next_time && !isset(Bot::$options['manual'])) {
                            if (Bot::$options['debug']) echo 'Время следующего выполнения щё не наступило. Скрипт запустится не раньше ' . date('H:i:s', $this->next_time) . " \n";
                            exit;
                        }
                    }
                }
            }
        } else {
            if (Bot::$options['debug']) echo "Конфиг не найден\n";
        }

        $dateParams = $this->getDateParams(time());
        if (!isset(Bot::$options['manual'])) {
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

        if (Bot::$options['debug']) {
            if (Bot::$options['long'])
                echo "Будут подписаны длинные контракты\n";
            else
                echo "Будут подписаны короткие контракты\n";
        }
    }

    /**
     *
     */
    private function getDateParams($time) {
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

        file_put_contents(BASE_PATH . '/config.json', json_encode($config));
    }

    /**
     * Генерирует следующее время выполнения скрипта
     */
    public function generateNextStartTime() {
        $time = time();
        $dateParams = $this->getDateParams($time);
        if ($dateParams['hour'] >= 8)
            $this->next_time = $time + rand(3600, 5400);
        elseif (($dateParams['dow'] == 6 && $dateParams['hour'] > 2 || $dateParams['dow'] == 7 && $dateParams['hour'] > 3) && $dateParams['hour'] < 12)
            $this->next_time = strtotime('12:00', $time) + rand(0, 1800);
        elseif ($dateParams['dow'] >= 1 && $dateParams['dow'] <= 5 && $dateParams['hour'] > 1 && $dateParams['hour'] < 8)
            $this->next_time = strtotime('08:00', $time) + rand(0, 1800);
        else
            $this->next_time = $time + rand(3600, 5400);
    }

}