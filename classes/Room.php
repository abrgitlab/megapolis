<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */
class Room
{

    /**
     * @var $id int
     */
    public $id;

    /**
     * @var $location_data mixed
     */
    public $location_data;

    /**
     * @var $barn_data DOMDocument|mixed
     */
    public $barn_data;

    /**
     * @var $field_data DOMDocument|mixed
     */
    public $field_data;

    /**
     * @var $military_orders DOMDocument|mixed
     */
    private $military_orders;

    private static $military_conveyors = [
        //Наименование цеха => [id прототипов]
        'conveyor_armored_cars_line' => [1059282, 1059288, 1059294, 1059300, 1059306, 1059312], //Бронеавтомобили
        'conveyor_infantry_fighting_vehicle' => [1059318, 1059324, 1059330, 1059336, 1059342, 1059348], //БМП
        'conveyor_armored_troop_carrier' => [1059354, 1059360, 1059366, 1059372, 1059378, 1059384], //БТР
        'conveyor_light_tanks' => [1059102, 1059108, 1059114, 1059120, 1059126, 1059132], //Лёгкие танки
        'conveyor_medium_tanks' => [1059138, 1059144, 1059150, 1059156, 1059162, 1059168], //Средние танки
        'conveyor_self_propelled_artillery' => [1059210, 1059216, 1059222, 1059228, 1059234, 1059240], //САУ
        'conveyor_heavy_tanks' => [1059174, 1059180, 1059186, 1059192, 1059198, 1059204], //Тяжёлые танки
        'conveyor_multiple_rocket_launch_system' => [1059246, 1059252, 1059258, 1059264, 1059270, 1059276], //РСЗО

        'conveyor_lifesaving_underwater_vehicle' => [1060002, 1060008, 1060014, 1060057, 1060063, 1060069], //Батискафы
        'conveyor_diesel_submarines' => [1060020, 1060026, 1060032, 1060075, 1060081, 1060087], //Дизельные подлодки
        'conveyor_nuclear_submarines' => [1060039, 1060045, 1060051, 1060093, 1060099, 1060105], //Атомные подлодки

        'conveyor_transport_helicopters' => [1059692, 1059698, 1059704, 1059800, 1059806, 1059812], //Транспортные вертолёты
        'conveyor_attack_planes' => [1059656, 1059662, 1059668, 1059728, 1059734, 1059740], //Штурмовики
        'conveyor_attack_helicopters' => [1059674, 1059680, 1059686, 1059746, 1059752, 1059758], //Ударные вертолёты
        'conveyor_fighters' => [1059602, 1059608, 1059614, 1059710, 1059716, 1059722], //Истребители
        'conveyor_tactical_bombers' => [1059638, 1059644, 1059650, 1059764, 1059770, 1059776], //Бомбардировщики TB
        'conveyor_strategic_bombers' => [1059620, 1059626, 1059632, 1059782, 1059788, 1059794], //Бомбардировщики SB
        'conveyor_drones' => [1059818, 1059824, 1059830, 1059836], //Беспилотники

        'conveyor_landing_ships' => [1059928, 1059934, 1059940, 1059983, 1059989, 1059995], //Десантные суда
        'conveyor_ships_of_coastal_zone' => [1059910, 1059916, 1059922, 1060152, 1060158, 1060164], //Корабли
        'conveyor_cruisers' => [1059892, 1059898, 1059904, 1060170, 1060176, 1060182], //Крейсеры
        'conveyor_helicopter_carriers' => [1059965, 1059971, 1059977], //Вертолётоносцы
        'conveyor_aircraft_carriers' => [1059947, 1059953, 1059959], //Авианосцы

        'conveyor_air_defense_missiles' => [1059428, 1059434, 1059440, 1059446, 1059452, 1059458], //ЗРК
        'conveyor_coastal_missiles' => [1059464, 1059470, 1059476, 1059482, 1059488, 1059494], //БРК
        'conveyor_mobile_missiles' => [1059392, 1059398, 1059404, 1059410], //ПРК
        'conveyor_intercontinental_missiles' => [1059500, 1059512], //МБР
    ];

    /**
     * @inheritdoc
     */
    function __construct($room_id, $location_data = null)
    {
        $this->id = $room_id;

        if ($location_data) {
            $this->location_data = $location_data;
        } else {
            $room_file_name = BASE_PATH . DIRECTORY_SEPARATOR . 'rooms' . DIRECTORY_SEPARATOR . $this->id;
            if (Bot::$game->online) {
                $location_data = Bot::$game->getRoomStat($this->id);
                file_put_contents($room_file_name, $location_data);
            } else
                $location_data = file_get_contents($room_file_name);

            //Из-за UTF-8 в CDATA php не парсит xml
            $location_data = preg_replace('/<marketplace>.*<\/marketplace>/', '', $location_data);
            $location_data = preg_replace('/<neighborhoods.*<\/neighborhoods>/smi', '', $location_data);
            $location_data = preg_replace('/<items_activity .*<\/items_activity>/', '', $location_data);
            $location_data = preg_replace('/<quests_activity>.*<\/quests_activity>/', '', $location_data);
            $location_data = preg_replace('/<game_requests .*<\/game_requests>/', '', $location_data);
            $location_data = preg_replace('/<support>.*<\/support>/', '', $location_data);

            $location_data = Bot::$tidy->repairString($location_data, Bot::$tidy_config);

            $this->location_data = new DOMDocument();
            $this->location_data->loadXML($location_data);
        }

        $this->loadFieldData();
        $this->loadBarnData();

        if ($this->id == 0)
            $this->loadMilitaryOrdersData();
    }

    /**
     * Загружает данные о различных зданиях
     */
    public function loadFieldData() {
        $field_data = $this->location_data->getElementsByTagName('field');
        if ($field_data) {
            $this->field_data = new DOMDocument();
            $this->field_data->loadXML($this->location_data->saveXML($field_data->item(0)));
        }
    }

    /**
     * Загружает данные о различных нестроевых объектах
     */
    public function loadBarnData() {
        $barn_data = $this->location_data->getElementsByTagName('barn');
        if ($barn_data) {
            $this->barn_data = new DOMDocument();
            $this->barn_data->loadXML($this->location_data->saveXML($barn_data->item(0)));
        }
    }

    public function loadMilitaryOrdersData() {
        $military_orders = $this->location_data->getElementsByTagName('military_orders');
        if ($military_orders) {
            $this->military_orders = new DOMDocument();
            $this->military_orders->loadXML($this->location_data->saveXML($military_orders->item(0)));
        }
    }

    /**
     * Забирает выручку с выполненных контрактов, заключает новые
     */
    public function signContracts() {
        echo "Работа с контрактами в комнате $this->id\n";

        $contracts_list = Contracts::getContractsList($this);

        $cached = [];
        foreach($this->field_data->childNodes->item(0)->childNodes as $field) {
            if (isset($contracts_list[$field->localName])) {
                $contract_data = $contracts_list[$field->localName];
                if (in_array('pick', $contract_data['actions'])) {
                    $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                    $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                    if ($field_state == 4) {
                        $cached[] = [
                            'command' => 'pick',
                            'cmd_id' => Bot::$game->popCmdId(),
                            'room_id' => $this->id,
                            'item_id' => $field_id
                        ];

                        if (isset($contract_data['quest_inc_counter']) && $contract_data['quest_inc_counter']['on'] == 'pick') {
                            $cached[] = [
                                'command' => 'quest_inc_counter',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'quest_id' => $contract_data['quest_inc_counter']['quest_id'],
                                'counter' => $contract_data['quest_inc_counter']['counter'],
                                'count' => $contract_data['quest_inc_counter']['count']
                            ];
                        }
                    }
                }
            }
        }

        foreach($this->field_data->childNodes->item(0)->childNodes as $field) {
            if (isset($contracts_list[$field->localName])) {
                $contract_data = $contracts_list[$field->localName];
                if (in_array('put', $contract_data['actions'])) {
                    $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                    $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                    if ($field_state == 2 || $field_state == 4) {
                        if (Bot::$options['long'] && isset($contract_data['long']))
                            $contract = $contract_data['long'];
                        elseif (isset($contract_data['short']))
                            $contract = $contract_data['short'];
                        else
                            $contract = null;

                        if ($contract) {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field_id,
                                'klass' => $contract['contract']
                            ];

                            if (isset($contract['additional_fields']))
                                foreach ($contract['additional_fields'] as $key => $value) {
                                    $cached_part[$key] = $value;
                                }

                            $friends = [];
                            foreach (Bot::$game->friends as $friend) {
                                $friends[] = $friend->id;
                            }

                            if (isset($contract['friends_request']) && $contract['friends_request']) {
                                $cached[] = [
                                    'command' => 'send_request',
                                    'cmd_id' => Bot::$game->popCmdId(),
                                    'room_id' => $this->id,
                                    'name' => 'visit_' . $contract['contract'],
                                    'friend_ids' => implode('%2C', $friends),
                                    'item_id' => $field_id
                                ];
                            }

                            if (isset($contract_data['quest_inc_counter']) && $contract_data['quest_inc_counter']['on'] == 'put') {
                                $cached[] = [
                                    'command' => 'quest_inc_counter',
                                    'cmd_id' => Bot::$game->popCmdId(),
                                    'room_id' => $this->id,
                                    'quest_id' => $contract_data['quest_inc_counter']['quest_id'],
                                    'counter' => $contract_data['quest_inc_counter']['counter'],
                                    'count' => $contract_data['quest_inc_counter']['count']
                                ];
                            }
                        }
                    }
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём получения прибыли и подписания новых контрактов $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /**
     * Собирает монеты
     */
    public function getCoins() {
        $cached = [];
        foreach ($this->field_data->childNodes->item(0)->childNodes as $field) {
            if ($field->attributes !== NULL) {
                $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                if ($field_state == 5) {
                    $cached[] = [
                        'command' => 'clean',
                        'cmd_id' => Bot::$game->popCmdId(),
                        'room_id' => $this->id,
                        'item_id' => $field_id
                    ];
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём получения монеток $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /**
     * Производит военную технику, совершает военные операции и продаёт излишки военной техники
     */
    public function doMilitaryWork() {
        if ($this->id != 0)
            return;

        $models = []; //Юнитов в наличии

        $priority = [];
        $military_orders_items = (json_decode($this->military_orders->textContent, true));
        foreach ($military_orders_items as $index => $military_orders_item) {
            $priority[$military_orders_item['military_points']] = [];
            foreach ($military_orders_item['models'] as $order_model) {
                $priority[$military_orders_item['military_points']][] = $order_model['item_id'];
            }
        }
        krsort($priority);

        foreach ($priority as $military_points => $order_models) {
            foreach ($order_models as $order_model) {
                $models[$order_model] = 0;
            }
        }

        foreach (Room::$military_conveyors as $military_conveyor) {
            foreach ($military_conveyor as $model) {
                $model_name = Bot::$game->getCityItemById($model)['item_name'];
                $quantity = $this->getBarnQuantity($model_name);
                $models[$model] = $quantity;
            }
        }

        $models_required = []; //Юнитов требуется
        foreach ($military_orders_items as $military_order) {
            if (isset($military_order['models'])) {
                foreach ($military_order['models'] as $model) {
                    if (!isset($models_required[$model['item_id']]))
                        $models_required[$model['item_id']] = $model['quantity'];
                    else
                        $models_required[$model['item_id']] += $model['quantity'];
                }
            }
        }

        $cached = [];
        $models_for_sale = []; //Юнитов для продажи
        $models_for_buy = []; //Юнитов для покупки
        $conveyor_ids = [];
        foreach($this->field_data->childNodes->item(0)->childNodes as $field) { //Пробежимся по всем конвейерам
            if (isset(Room::$military_conveyors[$field->localName])) {
                $conveyor_ids[$field->localName] = $field->attributes->getNamedItem('id')->nodeValue;
                $queue = $field->attributes->getNamedItem('queue')->nodeValue;
                $queue_length = 0; //Длина очереди
                if ($queue != '') { //Рассмотрим очередь в текущем конвейере
                    $queue_items = explode(',', $queue);

                    $queue_length = count($queue_items);

                    foreach ($queue_items as $queue_item) { //Рассмотрим каждый юнит на конвейере
                        $conveyor = explode(':', $queue_item);

                        $produce_model = Bot::$game->getCityItemById($conveyor[0])['produce_model'];
                        $produce_model_id = Bot::$game->city_items[$produce_model]['id'];

                        if ($conveyor[1] == 3) { //Если юнит достроен
//TODO: раскомментировать после выполнения задания
//                            $cached[] = [ //Отправим запрос на то, чтобы убрать юнит с конвейера
//                                'command' => 'pick',
//                                'cmd_id' => Bot::$game->popCmdId(),
//                                'room_id' => $this->id,
//                                'item_id' => $field->attributes->getNamedItem('id')->nodeValue,
//                                'index' => 0,
//                                'klass' => Bot::$game->getCityItemById($conveyor[0])['item_name']
//                            ];

//                            --$queue_length; //Уменьшим значение очереди
//                            ++$models[$produce_model_id]; //Увеличим значение готовой продукции
                            if (!isset($models_for_sale[$produce_model_id])) { //Если ни одного подобного юнита нет в продаже
                                $for_sale = $models[$produce_model_id]; //Изначально юнитов с данным id для продажи = количество готовых юнитов с данным id
                                if (isset($models_required[$produce_model_id])) //Если какое-то число юнитов нужно
                                    $for_sale -= $models_required[$produce_model_id]; //Уменьшим количество продаваемых юнитов на число нужных юнитов

                                if ($for_sale > 0) { //Если количество продаваемых юнитов больше нуля
                                    $models_for_sale[$produce_model_id] = $for_sale; //Запомним их количество
                                    $models[$produce_model_id] -= $for_sale; //Уменьшим количество готовых юнитов на количество юнитов для продажи
                                }
                            } else { //Если количество продаваемых юнитов с данным id больше нуля
                                ++$models_for_sale[$produce_model_id]; //Добавим произведённый юнит к продаваемым
                                --$models[$produce_model_id]; //Вычтем его из готовых
                            }
                        } elseif ($conveyor[1] == 1 || $conveyor[1] == 2) {
                            ++$models[$produce_model_id]; //Увеличим значение готовой продукции
                        }
                    }
                }

                foreach (Room::$military_conveyors[$field->localName] as $model) {
                    if (isset($models_required[$model])) {
                        $quantity = $models_required[$model] - $models[$model]; //Вычтем число готовой продукции из числа требуемой
                        //Если количество требуемой продукции будет больше свободных слотов в конвейере, то заполним продукцией остаток конвейера.
                        //В ином случае, на конвейере останется свободное место
                        $for_buy = min($quantity, 3 - $queue_length);
                        if ($for_buy > 0) {
                            $models_for_buy[$model] = $for_buy;
                            $models_required[$model] -= $for_buy;
                            $queue_length += $for_buy;
                        }
                    }
                }
                if (count(Room::$military_conveyors[$field->localName]) > 0) { //Заполним пустые слоты конвейера продукцией из самого дорогого типа для данного конвейера
                    $model_left = Room::$military_conveyors[$field->localName][count(Room::$military_conveyors[$field->localName]) - 1];
                    $left_slots = 3 - $queue_length;
                    if ($left_slots > 0)
                        $models_for_buy[$model_left] = $left_slots;
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём сбора произведённой военной продукции $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

        $cached = [];
        foreach ($models_for_sale as $model_for_sale => $quantity) {
            for ($i = 0; $i < $quantity; ++$i)
                $cached[] = [
                    'command' => 'sell_barn',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'room_id' => $this->id,
                    'item_id' => $model_for_sale,
                    'quantity' => 1
                ];
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём продажи произведённой военной продукции $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

        $cached = [];
        foreach ($models_for_buy as $model => $quantity) {
            for ($i = 0; $i < $quantity; ++$i)
                foreach (Room::$military_conveyors as $military_conveyor => $conveyor_models) {
                    if (in_array($model, $conveyor_models)) {
                        $cached[] = [
                            'command' => 'put',
                            'cmd_id' => Bot::$game->popCmdId(),
                            'room_id' => $this->id,
                            'item_id' => $conveyor_ids[$military_conveyor],
                            'klass' => 'production_' . Bot::$game->getCityItemById($model)['item_name']
                        ];
                        break;
                    }
                }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём начала производства военной продукции $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /*
     * Работа в сноувилле
     */
    public function doSnowvilleFactoryWork() {
        $items_count = [
            '1060440' => $this->getBarnQuantity('mine_petard'),
            '1060441' => $this->getBarnQuantity('mine_rocket'),
            '1060442' => $this->getBarnQuantity('mine_pyro_box')
        ];

        $production_ids = [
            '1060440' => 'production_first_factory_mine_petard',
            '1060441' => 'production_first_factory_mine_rocket',
            '1060442' => 'production_first_factory_mine_pyro_box'
        ];

        $cached = [];
        foreach($this->field_data->childNodes->item(0)->childNodes as $field) {
            if ($field->localName == 'first_santa_factory_stage3') {
                $queue = $field->attributes->getNamedItem('queue')->nodeValue;
                $queue_length = 0;
                if ($queue != '') {
                    $queue_items = explode(',', $queue);
                    $queue_length = count($queue_items);
                    foreach ($queue_items as $queue_item) {
                        $conveyor = explode(':', $queue_item);

                        if ($conveyor[1] == 3) {
                            $cached[] = [
                                'command' => 'pick',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => 70153339,
                                'index' => 0,
                                'klass' => $production_ids[$conveyor[0]]
                            ];

                            ++$items_count[$conveyor[0]];
                            --$queue_length;
                        }
                    }
                }

                for ($i = $queue_length; $i < 3; ++$i) {
                    if ($items_count['1060441'] < min($items_count['1060440'], $items_count['1060442']) / 4 + 1) {
                        $cached[] = [
                            'command' => 'put',
                            'cmd_id' => Bot::$game->popCmdId(),
                            'room_id' => $this->id,
                            'item_id' => 70153339,
                            'klass' => $production_ids['1060441']
                        ];
                        ++$items_count['1060441'];
                    } else if ($items_count['1060440'] < $items_count['1060442']) {
                        $cached[] = [
                            'command' => 'put',
                            'cmd_id' => Bot::$game->popCmdId(),
                            'room_id' => $this->id,
                            'item_id' => 70153339,
                            'klass' => $production_ids['1060440']
                        ];
                        ++$items_count['1060440'];
                    } else {
                        $cached[] = [
                            'command' => 'put',
                            'cmd_id' => Bot::$game->popCmdId(),
                            'room_id' => $this->id,
                            'item_id' => 70153339,
                            'klass' => $production_ids['1060442']
                        ];
                        ++$items_count['1060442'];
                    }
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём обработки конвейера пиротехники $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /**
     * Работа с китайской фабрикой
     */
    public function doChineseFactoryWork() {
        $items = ['casket', 'bronze_statuette', 'antique_teapot', 'ceramic_vase', 'jade_medallion', 'hair_comb'];

        $items_count = [];
        foreach ($items as $item) {
            $items_count[Bot::$game->city_items[$item . '_production']['id']] = $this->getBarnQuantity($item);
        }

        $cached = [];
        foreach($this->field_data->childNodes->item(0)->childNodes as $field) {
            $fieldName = $field->localName;
            if ($fieldName == 'museum_chinese_civilization_stage3' || $fieldName == 'museum_chinese_civilization_stage2') {
                $fieldId = $field->attributes->getNamedItem('id')->nodeValue;
                $queue = $field->attributes->getNamedItem('queue')->nodeValue;
                $queue_length = 0;
                if ($queue != '') {
                    $queue_items = explode(',', $queue);
                    $queue_length = count($queue_items);
                    foreach ($queue_items as $queue_item) {
                        $conveyor = explode(':', $queue_item);

                        if ($conveyor[1] == 3) {
                            $cached[] = [
                                'command' => 'pick',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $fieldId,
                                'index' => 0,
                                'klass' => Bot::$game->getCityItemById($conveyor[0])['item_name']
                            ];

                            ++$items_count[$conveyor[0]];
                            --$queue_length;
                        }
                    }
                }

                for ($i = $queue_length; $i < 3; ++$i) {
                    for ($coeff = 1; true; ++$coeff) {
                        if ($fieldName == 'museum_chinese_civilization_stage2') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $fieldId,
                                'klass' => Bot::$game->getCityItemById('20080409')['item_name']
                            ];
                            ++$items_count['20080409'];
                            break;
                        } elseif ($fieldName == 'museum_chinese_civilization_stage3') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $fieldId,
                                'klass' => Bot::$game->getCityItemById('20080411')['item_name']
                            ];
                            ++$items_count['20080411'];
                            break;
                        }
                    }
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём обработки конвейера китайской фабрики $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

        $cached = [];
        foreach ($items as $item) {
            for ($i = 0; $i < $this->getBarnQuantity($item); ++$i) {
                $cached[] = [
                    'command' => 'sell_barn',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'room_id' => $this->id,
                    'item_id' => Bot::$game->city_items[$item]['id'],
                    'quantity' => 1
                ];
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём продажи китайских вещей $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /**
     *
     */
    public function casinoPickFriends() {
        if ($this->id != 4)
            return;

        $material_list = array('poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold', 'silk_robe', 'gold_signet');

        $room_staff = json_decode($this->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('room_staff')->nodeValue);
        $roll_counter = $this->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('roll_counter')->nodeValue;

        $barn_amount = [];

        foreach($this->barn_data->childNodes->item(0)->childNodes as $barn) {
            if (in_array($barn->localName, $material_list)) {
                $field_quantity = $barn->attributes->getNamedItem('quantity')->nodeValue;
                $barn_amount[$barn->attributes->getNamedItem('id')->nodeValue] = $field_quantity;
            }
        }

        $cached = [];

        $contracts = [
            '15306' => 0,
            '15305' => 0
        ];

        $friends_for_invite_in_gambling_zone = [];
        foreach (Bot::$game->getFriendsForInviteInGamblingZone() as $friend) {
            $friends_for_invite_in_gambling_zone[] = $friend->id;
        }

        foreach ($room_staff as $friend_id => $friend) {
            if (isset($friend->time_end)) {
                if ($friend->time_end == 0) {
                    ++$barn_amount[$friend->material_id];

                    $cached[] = [
                        'command' => 'pick_room_staff',
                        'cmd_id' => Bot::$game->popCmdId(),
                        'room_id' => $this->id,
                        'friend_id' => $friend_id,
                        'item_id' => '39052472'
                    ];

                    $friends_for_invite_in_gambling_zone[] = $friend_id;
                } elseif ($friend->time_end > 0) {
                    if (isset($contracts[$friend->contract_id]))
                        ++$contracts[$friend->contract_id];
                    else
                        $contracts[$friend->contract_id] = 0;
                }
            }
        }

        foreach ($room_staff as $friend_id => $friend) {
            if (!isset($friend->time_end)) {
                $cached[] = [
                    'command' => 'put_room_staff',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'roll_counter' => $roll_counter,
                    'friend_id' => $friend_id,
                    'item_id' => '39052472',
                    'room_id' => $this->id
                ];
                if ($contracts['15306'] < 6)
                    $cached_part['contract_id'] = '15306';
                else {
                    if ($contracts['15305'] < 6)
                        $cached_part['contract_id'] = '15305';
                    else
                        break;
                }
                ++$roll_counter;
            }
        }

        if (count($friends_for_invite_in_gambling_zone) > 0) {
            $cached[] = [
                'command' => 'send_mass_request',
                'cmd_id' => Bot::$game->popCmdId(),
                'room_id' => $this->id,
                'name' => 'gambling_zone_staff',
                'friend_ids' => implode('%2C', $friends_for_invite_in_gambling_zone)
            ];
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Работа с друзьями в казино $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

        $cached = [];

        foreach($barn_amount as $barn_id => $barn_count) {
            if ($barn_count > 50) {
                for ($i = 50; $i < $barn_count; ++$i) {
                    $cached[] = [
                        'command' => 'sell_barn',
                        'cmd_id' => Bot::$game->popCmdId(),
                        'room_id' => $this->id,
                        'item_id' => $barn_id,
                        'quantity' => 1
                    ];
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём продажи материалов $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    public function getBarnQuantity($name) {
        foreach($this->barn_data->childNodes->item(0)->childNodes as $barn) {
            if ($barn->localName == $name) {
                return $barn->attributes->getNamedItem('quantity')->nodeValue;
            }
        }

        return 0;
    }

}
