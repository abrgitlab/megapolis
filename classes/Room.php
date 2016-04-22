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
     * @var $room_id int
     */
    private $room_id;

    /**
     * @var $location_data mixed
     */
    private $location_data;

    /**
     * @var $barn_data DOMDocument|mixed
     */
    private $barn_data;

    /**
     * @var $field_data DOMDocument|mixed
     */
    private $field_data;

    /**
     * @var $city_goods int
     */
    private $city_goods = 0;

    /**
     * @inheritdoc
     */
    function __construct($room_id, $first_request)
    {
        $this->room_id = $room_id;

        $location_data = Bot::getGame()->getRoomStat($this->room_id, $first_request);
        $location_data = Bot::$tidy->repairString($location_data, Bot::$tidy_config);

        $this->location_data = new DOMDocument();
        $this->location_data->loadXML($location_data);

        $this->loadFieldData();
        $this->loadBarnData();
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

            foreach($this->barn_data->childNodes->item(0)->childNodes as $barn) {
                if ($barn->localName == 'city_goods') {
                    $this->city_goods = $barn->attributes->getNamedItem('quantity')->nodeValue;
                }
            }
        }
    }

    /**
     * Забирает выручку с выполненных контрактов,
     * заключает новые, собирает монеты
     */
    public function signContracts() {
        echo "Работа с контрактами в комнате $this->room_id\n";

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
                            'cmd_id' => Bot::getGame()->popCmdId(),
                            'room_id' => $this->room_id,
                            'item_id' => $field_id
                        ];
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
                            $contract_data = $contract_data['long'];
                        elseif (isset($contract_data['short']))
                            $contract_data = $contract_data['short'];
                        else
                            $contract_data = null;

                        if ($contract_data) {
                            $cached[] = [
                                'command' => 'put',
                                'cmd_id' => Bot::getGame()->popCmdId(),
                                'room_id' => $this->room_id,
                                'item_id' => $field_id,
                                'klass' => $contract_data['contract']
                            ];

                            if (isset($contract_data['additional_fields']))
                                foreach ($contract_data['additional_fields'] as $key => $value) {
                                    $cached_part[$key] = $value;
                                }

                            $friends = [];
                            foreach (Bot::getGame()->getFriends() as $friend) {
                                $friends[] = $friend->getId();
                            }

                            if (isset($contract_data['friends_request']) && $contract_data['friends_request']) {
                                $cached[] = [
                                    'command' => 'send_request',
                                    'cmd_id' => Bot::getGame()->popCmdId(),
                                    'room_id' => $this->room_id,
                                    'name' => 'visit_' . $contract_data['contract'],
                                    'friend_ids' => implode('%2C', $friends),
                                    'item_id' => $field_id
                                ];
                            }

                            if (isset($contract_data['quest_inc_counter'])) {
                                $cached[] = [
                                    'command' => 'quest_inc_counter',
                                    'cmd_id' => Bot::getGame()->popCmdId(),
                                    'room_id' => $this->room_id,
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

            Bot::getGame()->checkAndPerform($cached);
        }

        $cached = [];
        //if ($this->room_id != 0) { //Временно блокируем основную локацию от получения монет
            foreach ($this->field_data->childNodes->item(0)->childNodes as $field) {
                if ($field->attributes !== NULL) {
                    $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                    $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                    if ($field_state == 5) {
                        $cached[] = [
                            'command' => 'clean',
                            'cmd_id' => Bot::getGame()->popCmdId(),
                            'room_id' => $this->room_id,
                            'item_id' => $field_id
                        ];
                    }
                }
            }
        //}

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём получения монеток $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            Bot::getGame()->checkAndPerform($cached);
        }
    }

    /**
     *
     */
    public function casinoPickFriends() {
        if ($this->room_id != 4)
            return;

        $material_list = array('poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold');

        echo "Работа с друзьями в казино\n";

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

        $contracts = [];

        $friends_for_invite_in_gambling_zone = [];
        foreach (Bot::getGame()->getFriendsForInviteInGamblingZone() as $friend) {
            $friends_for_invite_in_gambling_zone[] = $friend->getId();
        }

        foreach ($room_staff as $friend_id => $friend) {
            if (isset($friend->time_end)) {
                if ($friend->time_end == 0) {
                    ++$barn_amount[$friend->material_id];

                    $cached[] = [
                        'command' => 'pick_room_staff',
                        'cmd_id' => Bot::getGame()->popCmdId(),
                        'room_id' => $this->room_id,
                        'friend_id' => $friend_id,
                        'item_id' => '39052472'
                    ];

                    $friends_for_invite_in_gambling_zone[] = $friend_id; //TODO: проверить, правильно ли я делаю, приглашая поиграть соседа, с которого только что снял барыш
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
                    'cmd_id' => Bot::getGame()->popCmdId(),
                    'roll_counter' => $roll_counter,
                    'friend_id' => $friend_id,
                    'item_id' => '39052472',
                    'room_id' => $this->room_id
                ];
                if ($contracts['15306'] < 6)
                    $cached_part['contract_id'] = '15306';
                else {
                    if ($contracts['15305'] < 6)
                        $cached_part['contract_id'] = '15305';
                    else
                        break;
                }
//                ++$roll_counter;
            }
        }

        if (count($friends_for_invite_in_gambling_zone) > 0) {
            $cached[] = [
                'command' => 'send_mass_request',
                'cmd_id' => Bot::getGame()->popCmdId(),
                'room_id' => $this->room_id,
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

            Bot::getGame()->checkAndPerform($cached);
        }

        $cached = [];

        foreach($barn_amount as $barn_id => $barn_count) {
            if ($barn_count > 50) {
                for ($i = 50; $i < $barn_count; ++$i) {
                    $cached[] = [
                        'command' => 'sell_barn',
                        'cmd_id' => Bot::getGame()->popCmdId(),
                        'room_id' => $this->room_id,
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

            Bot::getGame()->checkAndPerform($cached);
        }
    }

    /**
     * Возвращает id комнаты
     * @return int
     */
    public function getId() {
        return $this->room_id;
    }

    /**
     * Возвращает нестроевые данные комнаты
     * @return DOMDocument|mixed
     */
    public function getBarnData() {
        return $this->barn_data;
    }

    /**
     * Возвращает данные комнаты
     * @return DOMDocument|mixed
     */
    public function getLocationData() {
        return $this->location_data;
    }

}