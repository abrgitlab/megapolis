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
                if (!isset($options['force'])) {
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

}