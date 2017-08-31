<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */

require_once 'Bot.php';
require_once 'Contracts.php';
require_once 'Game.php';

class Room
{

    /**
     * @var $id int
     */
    public $id;

    /**
     * @var $location_data SimpleXMLElement
     */
    public $location_data;

    /**
     * @var $barn_data []
     */
    public $barn;

    /**
     * @var $field_data []
     */
    public $field;

    /**
     * @var $military_orders stdClass
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
        'conveyor_drones' => [1059818, 1059824, 1059830, 1059836, 1059842, 1059848], //Беспилотники

        'conveyor_landing_ships' => [1059928, 1059934, 1059940, 1059983, 1059989, 1059995], //Десантные суда
        'conveyor_ships_of_coastal_zone' => [1059910, 1059916, 1059922, 1060152, 1060158, 1060164], //Корабли
        'conveyor_cruisers' => [1059892, 1059898, 1059904, 1060170, 1060176, 1060182], //Крейсеры
        'conveyor_helicopter_carriers' => [1059965, 1059971, 1059977], //Вертолётоносцы
        'conveyor_aircraft_carriers' => [1059947, 1059953, 1059959], //Авианосцы

        'conveyor_air_defense_missiles' => [1059428, 1059434, 1059440, 1059446, 1059452, 1059458], //ЗРК
        'conveyor_coastal_missiles' => [1059464, 1059470, 1059476, 1059482, 1059488, 1059494], //БРК
        'conveyor_mobile_missiles' => [1059392, 1059398, 1059404, 1059410, 1059416, 1059422], //ПРК
        'conveyor_intercontinental_missiles' => [1059500, 1059506, 1059512, 1059518, 1059524/*, 1059530*/], //МБР

        'conveyor_communications_satellites' => [1060113, 1060118/*, 10601204*/, 1060130, 1060136, 1060142], //Спутники связи
        'conveyor_navigation_satellites' => [1060148, 1060190/*, 1060196*/, 1060202/*, 1060208, 1060214*/], //Спутники навигации
        'conveyor_observation_satellites' => [1060220, 1060226, 1060232, 1060238, 1060244/*, 1060378*/] //Спутники разведки
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
            $room_file_name = MEGAPOLIS_PATH . DIRECTORY_SEPARATOR . 'rooms' . DIRECTORY_SEPARATOR . $this->id;
            if (Bot::$game->online) {
                $location_data = Bot::$game->getRoomStat($this->id);
                file_put_contents($room_file_name, $location_data);
            } else
                $location_data = file_get_contents($room_file_name);

            $this->location_data = simplexml_load_string($location_data);
        }

        $this->loadFieldData();
        $this->loadBarnData();

        if ($this->id == 0)
            $this->loadMilitaryOrdersData();
    }

    public function loadFieldData() {
        if (isset($this->location_data->field[0])) {
            $this->field = [];
            foreach ($this->location_data->field[0] as $item) {
                $current = [];
                foreach ($item->attributes() as $attribute_name => $attribute_value) {
                    $current[$attribute_name] = $attribute_value->__toString();
                }
                if (!isset($this->field[$item->getName()]))
                    $this->field[$item->getName()] = [];
                array_push($this->field[$item->getName()], $current);
            }
        }
    }

    public function loadBarnData() {
        if (isset($this->location_data->barn[0])) {
            $this->barn = [];
            foreach ($this->location_data->barn[0] as $item) {
                $this->barn[$item->getName()] = [];
                foreach ($item->attributes() as $attribute_name => $attribute_value) {
                    $this->barn[$item->getName()][$attribute_name] = $attribute_value->__toString();
                }
            }
        }
    }

    public function loadMilitaryOrdersData() {
        if (isset($this->location_data->military_orders[0])) {
            $this->military_orders = json_decode($this->location_data->military_orders->__toString());
        }
    }

    /**
     * Забирает выручку с выполненных контрактов, заключает новые
     */
    public function signContracts() {
        Bot::log("Работа с контрактами в комнате $this->id", [Bot::$STDOUT, Bot::$TELEGRAM]);

        $contracts_list = Contracts::getContractsList($this);

        $cached = [];
        foreach($this->field as $field_name => $field_items) {
            foreach ($field_items as $field) {
                if (isset($contracts_list[$field_name])) {
                    $contract_data = $contracts_list[$field_name];
                    if (in_array('pick', $contract_data['actions'])) {
                        $field_state = $field['state'];

                        if ($field_state == 4) {
                            $cached[] = [
                                'command' => 'pick',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id']
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
        }

        foreach($this->field as $field_name => $field_items) {
            foreach ($field_items as $field) {
                if (isset($contracts_list[$field_name])) {
                    $contract_data = $contracts_list[$field_name];
                    if (in_array('put', $contract_data['actions'])) {
                        $field_state = $field['state'];

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
                                    'item_id' => $field['id'],
                                    'klass' => $contract['contract']
                                ];

                                if (isset($contract['additional_fields']))
                                    foreach ($contract['additional_fields'] as $key => $value) {
                                        $cached[count($cached) - 1][$key] = $value;
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
                                        'item_id' => $field['id']
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
        }

        if (count($cached) > 0) {
            Bot::log('Ждём получения прибыли и подписания новых контрактов ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём получения прибыли и подписания новых контрактов $i сек.");
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
        foreach($this->field as $field_name => $field_items) {
            foreach ($field_items as $field) {
                $field_state = $field['state'];

                if ($field_state == 5) {
                    $cached[] = [
                        'command' => 'clean',
                        'cmd_id' => Bot::$game->popCmdId(),
                        'room_id' => $this->id,
                        'item_id' => $field['id']
                    ];
                }
            }
        }

        if (count($cached) > 0) {
            Bot::log('Ждём получения монеток ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём получения монеток $i сек.");
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
        foreach ($this->military_orders as $index => $military_orders_item) {
            $priority[$military_orders_item->military_points] = [];
            foreach ($military_orders_item->models as $order_model) {
                $priority[$military_orders_item->military_points][] = $order_model->item_id;
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
        foreach ($this->military_orders as $military_order) {
            if (isset($military_order->models)) {
                foreach ($military_order->models as $model) {
                    if (!isset($models_required[$model->item_id]))
                        $models_required[$model->item_id] = $model->quantity;
                    else
                        $models_required[$model->item_id] += $model->quantity;
                }
            }
        }

        $cached = [];
        $models_for_sale = []; //Юнитов для продажи
        $models_for_buy = []; //Юнитов для покупки
        //$conveyor_ids = [];
        foreach (Room::$military_conveyors as $military_conveyor_name => $military_conveyor) {
            if (isset($this->field[$military_conveyor_name])) {
                $queue = $this->field[$military_conveyor_name][0]['queue'];
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
//                                'item_id' => $military_conveyor['id'],
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

                foreach (Room::$military_conveyors[$military_conveyor_name] as $model) {
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
                if (count(Room::$military_conveyors[$military_conveyor_name]) > 0) { //Заполним пустые слоты конвейера продукцией из самого дорогого типа для данного конвейера
                    $model_left = Room::$military_conveyors[$military_conveyor_name][count(Room::$military_conveyors[$military_conveyor_name]) - 1];
//                    $model_left = Room::$military_conveyors[$field->localName][0]; //На время задания ставим самую быстропроизводимую продукцию
                    $left_slots = 3 - $queue_length;
                    if ($left_slots > 0)
                        $models_for_buy[$model_left] = $left_slots;
                }
            }
        }

        if (count($cached) > 0) {
            Bot::log('Ждём сбора произведённой военной продукции ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём сбора произведённой военной продукции $i сек.");
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
            Bot::log('Ждём продажи произведённой военной продукции ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём продажи произведённой военной продукции $i сек.");
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
                            'item_id' => $this->field[$military_conveyor][0]['id'],
                            'klass' => 'production_' . Bot::$game->getCityItemById($model)['item_name']
                        ];
                        break;
                    }
                }
        }

        if (count($cached) > 0) {
            Bot::log('Ждём начала производства военной продукции ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём начала производства военной продукции $i сек.");
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    /**
     * Работа с китайскими и египетскими фабриками
     *
     * @param $name string
     */
     public function doFactoryWork($name) {
        $items = [
            'chinese' => ['casket', 'bronze_statuette', 'antique_teapot', 'ceramic_vase', 'jade_medallion', 'hair_comb'],
            'egyptian' => ['ankh', 'scarab', 'uskh', 'eye_of_horus', 'ancient_vase', 'statuette_bastet']
        ];

        $museums = [
            'chinese' => ['museum_chinese_civilization_stage3'],
            'egyptian' => ['museum_egyptian_civilization_stage3', 'museum_egyptian_civilization_stage2', 'museum_egyptian_civilization_stage1']
        ];

        $items_count = [];
        foreach ($items[$name] as $item) {
            $items_count[Bot::$game->city_items[$item . '_production']['id']] = $this->getBarnQuantity($item);
        }

        $cached = [];
        $fields = [];
        foreach ($museums[$name] as $museum) {
            if (isset($this->field[$museum]))
                $fields[$museum] = $this->field[$museum];
        }

        foreach($fields as $fieldName => $fieldItems) {
            foreach ($fieldItems as $field) {
                $queue_length = 0;
                if (isset($field['queue']) && $field['queue'] != '') {
                    $queue_items = explode(',', $field['queue']);
                    $queue_length = count($queue_items);
                    foreach ($queue_items as $queue_item) {
                        $conveyor = explode(':', $queue_item);

                        if ($conveyor[1] == 3) {
                            $cached[] = [
                                'command' => 'pick',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id'],
                                'index' => 0,
                                'klass' => Bot::$game->getCityItemById($conveyor[0])['item_name']
                            ];

                            ++$items_count[$conveyor[0]];
                            --$queue_length;
                        }
                    }
                }

                for ($i = $queue_length; $i < 3; ++$i) {
                    if ($name == 'chinese') {
                        if ($fieldName == 'museum_chinese_civilization_stage3') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id'],
                                'klass' => Bot::$game->getCityItemById('20080411')['item_name']
                            ];
                            //++$items_count['20080411'];
                            break;
                        }
                    } else if ($name == 'egyptian') {
                        if ($fieldName == 'museum_egyptian_civilization_stage1') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id'],
                                'klass' => Bot::$game->getCityItemById('20080557')['item_name']
                            ];
                            //++$items_count['20080557'];
                            break;
                        } elseif ($fieldName == 'museum_egyptian_civilization_stage2') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id'],
                                'klass' => Bot::$game->getCityItemById('20080559')['item_name']
                            ];
                            //++$items_count['20080559'];
                            break;
                        } elseif ($fieldName == 'museum_egyptian_civilization_stage3') {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::$game->popCmdId(),
                                'room_id' => $this->id,
                                'item_id' => $field['id'],
                                'klass' => Bot::$game->getCityItemById('20080561')['item_name']
                            ];
                            //++$items_count['20080561'];
                            break;
                        }
                    }
                }
            }
        }

        if (count($cached) > 0) {
            if ($name == 'chinese')
                Bot::log('Ждём обработки конвейера китайской фабрики ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            elseif ($name == 'egyptian')
                Bot::log('Ждём обработки конвейера египетской фабрики ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);

            for ($i = count($cached); $i > 0; --$i) {
                if ($name == 'chinese')
                    Bot::log("Ждём обработки конвейера китайской фабрики $i сек.");
                elseif ($name == 'egyptian')
                    Bot::log("Ждём обработки конвейера египетской фабрики $i сек.");

                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

        $cached = [];
        foreach ($items[$name] as $item) {
            for ($i = 0; $i < $items_count[Bot::$game->city_items[$item . '_production']['id']]; ++$i) {
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
            if ($name == 'chinese')
                Bot::log('Ждём продажи китайских вещей ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            elseif ($name == 'egyptian')
                Bot::log('Ждём продажи египетских вещей ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);

            for ($i = count($cached); $i > 0; --$i) {
                if ($name == 'chinese')
                    Bot::log("Ждём продажи китайских вещей $i сек.");
                elseif ($name == 'egyptian')
                    Bot::log("Ждём продажи египетских вещей $i сек.");

                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }
    }

    public function casinoPickFriends() {
        if ($this->id != 4)
            return;

        $material_list = array('poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold', 'silk_robe', 'gold_signet', 'gold_chain');

        $room_staff = json_decode($this->location_data->attributes()->room_staff->__toString());

        $roll_counter = $this->location_data->attributes()->roll_counter->__toString();

        $barn_amount = [];

        foreach ($material_list as $item) {
            $barn_amount[$this->barn[$item]['id']] = $this->getBarnQuantity($item);
        }

        $cached_pick = [];
        $cached_put = [];

        $contracts = [
            '15305' => 0,
            '15306' => 0,
            '15362' => 0
        ];

        $friends_for_invite_in_gambling_zone = [];
        foreach (Bot::$game->friends as $friend) {
            if ($friend->send_requests && !$friend->new_friend) {
                if (
                    (
                        !isset($friend->send_requests->gambling_zone_staff->user)/* ||
                        !in_array($friend->id, $friend->send_requests->gambling_zone_staff->user)*/
                    ) &&
                    /*!(isset($friend->requests->gambling_zone_staff_back->user) && count($friend->requests->gambling_zone_staff_back->user) == 0 && !$friend->new_friend) &&*/
                    !isset($room_staff->{$friend->id}) &&
                    !$friend->new_friend
                ) {
                    $friends_for_invite_in_gambling_zone[] = $friend->id;
                }
            }
        }

        foreach ($room_staff as $friend_id => $friend) {
            if (!isset($friend->time_end)) {
                $cached_put[] = [
                    'command' => 'put_room_staff',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'roll_counter' => $roll_counter++,
                    'friend_id' => $friend_id,
                    'item_id' => '39052472',
                    'room_id' => $this->id
                ];
                if ($contracts['15306'] < 10)
                    $cached_put[count($cached_put) - 1]['contract_id'] = '15306';
                elseif ($contracts['15362'] < 4)
                    $cached_put[count($cached_put) - 1]['contract_id'] = '15362';
                elseif ($contracts['15305'] < 10)
                    $cached_put[count($cached_put) - 1]['contract_id'] = '15305';
                else
                    break;

                $index = array_search($friend_id, $friends_for_invite_in_gambling_zone);
                if ($index !== false)
                    unset($friends_for_invite_in_gambling_zone[$index]);
            } elseif ($friend->time_end == 0) {
                $cached_pick[] = [
                    'command' => 'pick_room_staff',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'room_id' => $this->id,
                    'friend_id' => $friend_id,
                    'item_id' => '39052472'
                ];

                ++$barn_amount[$friend->material_id];

                $friends_for_invite_in_gambling_zone[] = $friend_id;
            } elseif ($friend->time_end > 0) {
                if (isset($contracts[$friend->contract_id]))
                    ++$contracts[$friend->contract_id];
                else
                    $contracts[$friend->contract_id] = 0;
            }
        }

        Bot::log('Друзья для приглашения в казино:');
        foreach (Bot::$game->friends as $friend) {
            $index = array_search($friend->id, $friends_for_invite_in_gambling_zone);
            if ($index)
                Bot::log($friend->id . ': ' . $friend->city_name . ', ' . $friend->first_name);
        }

        $cached = array_merge($cached_put, $cached_pick);

        if (count($cached) > 0) {
            Bot::log('Работа с друзьями в казино ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Работа с друзьями в казино $i сек.");
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }

    }

    /**
     *
     */
    public function casinoPickFriendsOld() { //TODO: полностью пересмотреть алгоритм работы
        if ($this->id != 4)
            return;

        $material_list = array('poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold', 'silk_robe', 'gold_signet', 'gold_chain');

        $room_staff = json_decode($this->location_data->attributes()->room_staff->__toString());
        $roll_counter = $this->location_data->attributes()->roll_counter->__toString();

        $barn_amount = [];

        foreach ($material_list as $item) {
            $barn_amount[$this->barn[$item]['id']] = $this->getBarnQuantity($item);
        }

        $cached = [];

        $contracts = [
            '15305' => 0,
            '15306' => 0,
            '15362' => 0
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
                if ($contracts['15306'] < 10)
                    $cached[count($cached) - 1]['contract_id'] = '15306';
                elseif ($contracts['15362'] < 4)
                    $cached[count($cached) - 1]['contract_id'] = '15362';
                elseif ($contracts['15305'] < 10)
                    $cached[count($cached) - 1]['contract_id'] = '15305';
                else
                    break;

                $index = array_search($friend_id, $friends_for_invite_in_gambling_zone);
                if ($index !== false)
                    unset($friends_for_invite_in_gambling_zone[$index]);

                ++$roll_counter;
            }
        }

        Bot::log('Друзья для приглашения в казино:');
        foreach ($friends_for_invite_in_gambling_zone as $friend) {
            Bot::log($friend);
        }

        /*if (count($friends_for_invite_in_gambling_zone) > 0) {
            foreach ($friends_for_invite_in_gambling_zone as $friend_id) {
                $cached[] = [
                    'command' => 'send_request',
                    'cmd_id' => Bot::$game->popCmdId(),
                    'room_id' => $this->id,
                    'name' => 'gambling_zone_staff',
                    'friend_id' => $friend_id,
                    'item_id' => '39052472'
                ];
            }
//            $cached[] = [
//                'command' => 'send_mass_request',
//                'cmd_id' => Bot::$game->popCmdId(),
//                'room_id' => $this->id,
//                'name' => 'gambling_zone_staff',
//                'friend_ids' => implode('%2C', $friends_for_invite_in_gambling_zone)
//            ];
        }*/

        /*$cached[] = [
            'command' => 'send_request',
            'cmd_id' => Bot::$game->popCmdId(),
            'room_id' => $this->id,
            'name' => 'gambling_zone_staff',
            'friend_id' => 'UD_e1b9e702db9dcf393641114c',
            'item_id' => '39052472'
        ];*/

        if (count($cached) > 0) {
            Bot::log('Работа с друзьями в казино ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Работа с друзьями в казино $i сек.");
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            /*$result = Bot::$game->checkAndPerform($cached);
            var_dump($result);*/
        }

        /*$cached = [];

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
            Bot::log('Ждём продажи материалов ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём продажи материалов $i сек.");
                //echo "Ждём продажи материалов $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::$game->checkAndPerform($cached);
        }*/
    }

    public function getBarnQuantity($name) {
        if (isset($this->barn[$name]['quantity']))
            return $this->barn[$name]['quantity'];

        return 0;
    }

}
