<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */
class Game
{

    //TODO: анализ списка желаний друзей и раздаривание материалов

    public static $casino_materials = ['poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold'];
    public static $friends_exception = [
        /*'GC_edf35cc69e155f6e9d5777ff',
        'UD_cd78ad8577c178ec91904023',
        'GC_1df222d4ccb142f7947194e9',
        'UD_7c5fc1389ed1fda161958c75',
        'UD_55377e7ad03d7ad5dddf4257',
        'UD_af1b46ee6e58002ce81bb1c2',
        'UD_c489bde8529b908e7877c0e7',
        'UD_bf639cd63c606f536a1d7b9a',
        'UD_d9944b5e05dc71f2bac4d4fe',
        'GC_79f4352d6d9e9a27d54de5d9',
        'UD_010f298b1f6d8751974a4701',
        'UD_0f58411f7b8b14e19abde9f4',
        'UD_f9551fc6a87fa1b9b2f2b38b',
        'UD_bd88d3680e19cbaed0e6838f',
        'GC_40565cfc34d3621d518ff310',
        'UD_e51f71c25e5e4a579047915f',
        'UD_3b79ee90fd30515dbc701e53'*/
    ];

    /**
     * @var $rn int
     */
    public $rn = 0;

    /**
     * @var $cmd_id int
     */
    public $cmd_id;

    /**
     * @var $revision int
     */
    private $revision;

    /**
     * @var $city_items mixed
     */
    private $city_items;

    /**
     * @var $room_id Room
     */
    public $room;

    /**
     * @var $friends Friend[]
     */
    public $friends = [];

    /**
     * @var $available_gifts array
     */
    private $available_gifts;

    /**
     * @inheritdoc
     */
    function __construct()
    {
        $this->checkUpdates();
        $this->loadCityItems();

        $user_data = $this->getUserStat();

        $user_data = Bot::$tidy->repairString($user_data, Bot::$tidy_config);

        $user_data_xml = new DOMDocument();
        $user_data_xml->loadXML($user_data);

        $this->cmd_id = $user_data_xml->getElementsByTagName('country')->item(0)->attributes->getNamedItem('server_cmd_id')->nodeValue;
    }

    /**
     * Проверяет новые данные city_items
     * и скачивает их, если обновления имеются
     */
    public function checkUpdates() {
        echo "Проверка наличия обновлений\n";
        $revision_data = $this->getRevision();
        $revision_data = Bot::$tidy->repairString($revision_data, Bot::$tidy_config);

        $revision_data_xml = new DOMDocument();
        $revision_data_xml->loadXML($revision_data);

        $this->revision = $revision_data_xml->getElementsByTagName('revision_info')->item(0)->attributes->getNamedItem('revision')->nodeValue;
        if (!file_exists(BASE_PATH . "/city_items.yml.$this->revision")) {
            echo "Получение обновлений\n";
            $city_items_yaml = file_get_contents("http://mb.static.socialquantum.ru/mobile_assets/city_items.yml?rev=$this->revision");
            file_put_contents(BASE_PATH . "/city_items.yml.$this->revision", $city_items_yaml);
        }
    }

    /**
     * Загружает данные комнаты с номером id
     * @param $id int
     */
    public function changeRoom($id) {
        if ($this->room) {
            Bot::$last_room_id = $this->room->id;
            $this->room = new Room($id, false);
        } else {
            Bot::$last_room_id = 0;
            $this->room = new Room($id, true);
            $this->loadGiftsData();
        }
    }

    /**
     * Загружает данные объектов игры из yml-фалйа
     */
    public function loadCityItems() {
        $this->city_items = yaml_parse(file_get_contents(BASE_PATH . "/city_items.yml.$this->revision"));
    }

    /**
     * Загружает список друзей
     */
    public function loadFriends() {
        $friends = $this->room->location_data->getElementsByTagName('friends');
        if ($friends) {
            $letters_amount = 0;
            foreach ($friends->item(0)->childNodes as $friend_item) {
                if ($friend_item->localName == 'friend') {
                    $friend = new Friend();

                    $friend->loadFromXmlNode($friend_item);
                    if (!in_array($friend->id, Game::$friends_exception))
                        $this->friends[] = $friend;

                    if (count($friend->letters) > 0 && !$friend->pending) {
                        echo $friend->id . "\n";
                        var_dump($friend->letters);
                        $letters_amount += count($friend->letters);
                    }
                }
            }
            echo "Писем: $letters_amount\n";
        }
    }

    /**
     * Обрабатывает список писем
     */
    public function handleLetters() {

    }

    /**
     * Принимает просящихся в друзья
     */
    public function acceptFriends() {
        foreach ($this->friends as $friend) {
            if (isset($friend->requests->invite_suggested_neighbors) && isset($friend->requests->pending)) {
                if ($friend->pending === true && $friend->requests->invite_suggested_neighbors->count > 0) {
                    echo 'Принимаем в друзья город ' . $friend->id . "\n";
                    $this->processAcceptFriend($friend->id, $friend->requests->invite_suggested_neighbors->pushed);
                }
            }
        }
    }

    /**
     * Посещение друзей
     */
    public function visitFriends() {
        foreach ($this->friends as $friend) {
            if ($friend->id != '-41' && $friend->id != '-43') {
                $friend->visit();
            }
        }
    }

    public function applyHelp() {
        $cached = [];
        foreach ($this->friends as $friend) {
            foreach ($friend->help_items as $helpItem => $value) {
                if ($value == $this->room->id) {
                    $cached[] = [
                        'command' => 'apply_help',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'item_id' => $helpItem,
                        'friend_id' => $friend->id
                    ];
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Получение помощи от друзей $i сек.\n";
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }

            $this->checkAndPerform($cached);
        }
    }

    /**
     * @return Friend[]
     */
    public function getFriendsForInviteInGamblingZone() {
        $result = [];
        foreach ($this->friends as $friend) {
            if ($friend->send_requests) {
                if (isset($friend->send_requests->gambling_zone_staff->user) && count($friend->send_requests->gambling_zone_staff->user) > 0 && $friend->send_requests->gambling_zone_staff->user[0] == $friend->id) {
                    $result[] = $friend;
                }
            }
        }

        return $result;
    }

    /**
     * Загружает данные о подарках
     */
    public function loadGiftsData() {
        $this->available_gifts = [];
        $gifts_data = $this->room->location_data->getElementsByTagName('gifts');
        if ($gifts_data) {
            $gifts_data_xml = new DOMDocument();
            $gifts_data_xml->loadXML($this->room->location_data->saveXML($gifts_data->item(0)));

            $available_gifts = $gifts_data_xml->getElementsByTagName('available');
            if ($available_gifts) {
                $available_gifts_xml = new DOMDocument();
                $available_gifts_xml->loadXML($gifts_data_xml->saveXML($available_gifts->item(0)));

                foreach ($available_gifts->item(0)->childNodes as $gift) {
                    if (get_class($gift) == 'DOMElement') {
                        for ($i = 0; $i < $gift->attributes->getNamedItem('quantity')->nodeValue; ++$i)
                            $this->available_gifts[] = $gift->attributes->getNamedItem('id')->nodeValue;
                    }
                }
            }
        }
    }

    /**
     * Принимает подарки от друзей
     */
    public function receiveGifts() {
        $received_gifts = [];
        foreach ($this->friends as $friend) {
            if (isset($friend->requests->send_gift_new)) {
                foreach ($friend->requests->send_gift_new->st_items as $gift_id) {
                    $city_item_name = $this->getCityItemById($gift_id)['item_name'];
                    if ($city_item_name)
                        $received_gifts[] = array('name' => 'send_gift_new', 'friend_id' => $friend->id, 'st_item' => $gift_id, 'gift_name' => $city_item_name);
                    else
                        echo "Неизвестный материал, id $gift_id\n";
                }
            }
        }

        if (count($received_gifts) > 0) {
            $cached = [[
                'command' => 'mass_commit_request',
                'cmd_id' => $this->popCmdId(),
                'room_id' => $this->room->id,
                'data' => urlencode(json_encode($received_gifts)),
                'uxtime' => time()
            ]];

            $this->checkAndPerform($cached);

            echo 'Принято подарков: ' . count($received_gifts) . "\n";
        }
    }

    /**
     * Раздаривает подарки друзьям
     */
    public function sendGifts($send_the_rest_gifts = false) {
        $sending_gifts = [];
        foreach ($this->friends as $friend) {
            if ($friend->next_gift_time < 0) {
                foreach ($friend->wish_list as $item) {
                    if (in_array($item, $this->available_gifts)) {
                        $sending_gifts[$item][] = $friend->id;
                    }
                }
            }
        }

        $friends_proceeded = [];

        $cached = [];
        while (count($sending_gifts) > 0 && count($this->available_gifts) > 0) {
            $item = array_keys($sending_gifts)[0];
            $friend = $sending_gifts[$item][array_keys($sending_gifts[$item])[0]];
            $friends_proceeded[] = $friend;
            $cached[] = [
                'command' => 'send_gift',
                'cmd_id' => $this->popCmdId(),
                'room_id' => $this->room->id,
                'item_id' => $item,
                'type_id' => $item,
                'second_user_id' => $friend
            ];

            $removing_available_gift = array_search($item, $this->available_gifts);
            unset($this->available_gifts[$removing_available_gift]);

            if (!in_array($item, $this->available_gifts)) {
                unset($sending_gifts[$item]);
            }

            foreach ($sending_gifts as $sending_item => $recipients) {
                $removing_recipient = array_search($friend, $recipients);
                if ($removing_recipient !== false) {
                    unset($sending_gifts[$sending_item][$removing_recipient]);
                    if (count($sending_gifts[$sending_item]) == 0) {
                        unset($sending_gifts[$sending_item]);
                    }
                }
            }
        }

        if ($send_the_rest_gifts) {
            foreach ($this->friends as $friend) {
                if ($friend->next_gift_time < 0 && !in_array($friend, $friends_proceeded) && count($this->available_gifts) > 0) {
                    $cached[] = [
                        'command' => 'send_gift',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'item_id' => $this->available_gifts[array_keys($this->available_gifts)[0]],
                        'type_id' => $this->available_gifts[array_keys($this->available_gifts)[0]],
                        'second_user_id' => $friend->id
                    ];
                    unset($this->available_gifts[array_keys($this->available_gifts)[0]]);
                }
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Ждём раздаривания подарков $i сек.\n";
                $current = [$cached[count($cached) - $i]];
                $current[0]['uxtime'] = time();
                $this->checkAndPerform($current);
                sleep(1);
            }
        }
    }

    /**
     * Отправляет друзей в игровую зону
     */
    public function sendFriendsToGamblingZone() {
        $cached = [];
        foreach ($this->friends as $friend) {
            if (isset($friend->requests->gambling_zone_staff_back->user) && count($friend->requests->gambling_zone_staff_back->user) == 0) {
                $cached[] = [
                    'command' => 'commit_request',
                    'cmd_id' => $this->popCmdId(),
                    'room_id' => $this->room->id,
                    'name' => 'gambling_zone_staff_back',
                    'friend_id' => $friend->id,
                    'item_id' => 0
                ];
            }
        }

        if (count($cached) > 0) {
            for ($i = count($cached); $i > 0; --$i) {
                echo "Отправляем друзей в игровую зону $i сек.\n";
                $current = [$cached[count($cached) - $i]];
                $current[0]['uxtime'] = time();
                $this->checkAndPerform($current);
                sleep(1);
            }
        }
    }

    /**
     * Открываем сундук
     */
    public function openChest() {
        $roll_counter = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('roll_counter')->nodeValue;

        /*$chest = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest');
        if ($chest)
            $chest = json_decode($chest->nodeValue);*/
        $chest_actions = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest_actions');
        $chest_time_last_open = time();
        if ($chest_actions) {
            $chest_actions = json_decode($chest_actions->nodeValue);
            $chest_time_last_open = $chest_actions->chest_event16->last_open;
        }

        $chest_action_tower1 = null;
        $chest_action_chest1 = $this->room->getBarn('chest_action_chest1');

        if (time() - $chest_time_last_open > 3600 && $chest_action_chest1 !== null && $chest_action_chest1 > 0) {
            echo "Открываем сундук\n";

            $cached = [[
                'command' => 'chest_action_open_chest',
                'cmd_id' => $this->popCmdId(),
                'roll_counter' => $roll_counter,
                'room_id' => $this->room->id,
                'name' => 'chest_event16',
                'v' => 2,
                'type' => 'coins',
                'uxtime' => time()
            ]];

            $this->checkAndPerform($cached);

            sleep(1);
        }
    }

    /**
     * Возвращает имя объекта по его идентификатору
     * @param $id int
     * @return null|string
     */
    public function getCityItemById($id) {
        foreach ($this->city_items as $item_name => $city_item) {
            if (isset($city_item['id']) && $city_item['id'] == $id) {
                $city_item['item_name'] = $item_name;
                return $city_item;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function popRN() {
        return ++$this->rn;
    }

    /**
     * @return int
     */
    public function popCmdId() {
        return ++$this->cmd_id;
    }

    /**
     * @return string
     */
    public function getUserStat() {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&no_field=true&social_id[SQ]=abr_mail%40mail.ru&device_id=45cca1a3-973a-3f7f-b226-da8d8301cfb6&platform=android&build=' . Bot::$build . '&app=city&device_model=Genymotion%20vbox86p&os=4.1.1&gloc=ru&dloc=ru&net=wf&social=sqsocial%3Aprod&odin_id=949c34f735162b0bd21f1f63db51cc2bb9e935ac&android_id=f337e0e35a1e6dd5&mac=0800270cc3c5&advertising_id=e4959f11-12a8-4cb1-a5d3-0c3649406e3b';
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @return string
     */
    public function getRevision() {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host_static . '/mobile_assets/revision.xml?rand=' . time());
        curl_setopt(Bot::$curl, CURLOPT_POST, false);
        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $room_id int
     * @param $first_request bool
     * @return string
     */
    public function getRoomStat($room_id, $first_request = false) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        if ($first_request) {
            $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&revision=android-' . Bot::$client_version . '.' . Bot::$build . '&access_token=' . Bot::$iauth . '&lang=ru&client_type=android&room_id=' . $room_id . '&odin_id=949c34f735162b0bd21f1f63db51cc2bb9e935ac&android_id=f337e0e35a1e6dd5&mac=0800270cc3c5&advertising_id=e4959f11-12a8-4cb1-a5d3-0c3649406e3b&device_id=45cca1a3-973a-3f7f-b226-da8d8301cfb6&first_request=true&location=&rn=' . $this->popRN();
        } else {
            $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&room_id=' . Bot::$last_room_id . '&change_room=1&view_room_id=' . $room_id . '&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&client_type=android&rn=' . $this->popRN();
        }
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $friend_id string
     * @param $last_room_id int
     * @param $room_id int
     * @return string
     */
    public function visitFriend($friend_id, $last_room_id, $room_id) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&view_friend_id=' . $friend_id . '&room_id=' . $last_room_id . '&change_room=1&view_room_id=' . $room_id . '&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&client_type=android&rn=' . $this->popRN();
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $friend_id string
     * @param $cached array
     * @return string
     */
    public function checkAndPerformFriend($friend_id, $cached) {
        $cached_string = '';
        $cached_id = 0;
        foreach ($cached as $cached_item_array) {
            foreach ($cached_item_array as $cached_item_key => $cached_item_value) {
                $cached_string .= '&cached[' . $cached_id . '][' . $cached_item_key . ']=' . $cached_item_value;
            }
            ++$cached_id;
        }

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/check_and_perform');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&room_id=' . $this->room->id . '&owner_id=' . $friend_id . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN();
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    public function processAcceptFriend($friend_id, $pushed) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/process');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&command=commit_request&cmd_id=' . $this->popCmdId() . '&room_id=' . $this->room->id . '&name=invite_suggested_neighbors&friend_id=' . $friend_id . '&count=1&pushed=' . $pushed . '&room_id=' . $this->room->id . '&only_head=1&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN();
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $cached array
     * @return string
     */
    public function checkAndPerform($cached) {
        $cached_string = '';
        $cached_id = 0;
        foreach ($cached as $cached_item_array) {
            foreach ($cached_item_array as $cached_item_key => $cached_item_value) {
                $cached_string .= '&cached[' . $cached_id . '][' . $cached_item_key . ']=' . $cached_item_value;
            }
            ++$cached_id;
        }

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/check_and_perform');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&room_id=' . $this->room->id . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN();
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }
}