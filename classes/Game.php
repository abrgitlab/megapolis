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
    private $room;

    /**
     * @var $barn_data mixed
     */
    private $barn_data;

    /**
     * @var $friends Friend[]
     */
    private $friends = [];

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
            Bot::$last_room_id = $this->room->getId();
            $this->room = new Room($id, false);
        } else {
            Bot::$last_room_id = 0;
            $this->room = new Room($id, true);
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
        $friends = $this->room->getLocationData()->getElementsByTagName('friends');
        if ($friends) {
            foreach ($friends->item(0)->childNodes as $friend_item) {
                if ($friend_item->localName == 'friend') {
                    $friend = new Friend();

                    if ($friend->getId() == 0) {
                        $this->friends[] = $friend;
                        $friend->loadFromXmlNode($friend_item);
                    }
                }
            }
        }
    }

    /**
     * Посещение друзей
     */
    public function visitFriends() {
        foreach ($this->friends as $friend) {
            if ($friend->getId() != '-41' && $friend->getId() != '-43') {
                $friend->visit();
            }
        }
    }

    /**
     * @return Friend[]
     */
    public function getFriendsForInviteInGamblingZone() {
        $result = [];
        foreach ($this->friends as $friend) {
            if ($friend->getSendRequests()) {
                if (isset($friend->getSendRequests()->gambling_zone_staff->user) && count($friend->getSendRequests()->gambling_zone_staff->user) > 0 && $friend->getSendRequests()->gambling_zone_staff->user[0] == $friend->getId()) {
                    $result[] = $friend;
                }
            }
        }

        return $result;
    }

    /**
     * Принимает подарки от друзей
     */
    public function receiveGifts() {
        $received_gifts = [];
        foreach ($this->friends as $friend) {
            if (isset($friend->getRequests()->send_gift_new)) {
                foreach ($friend->getRequests()->send_gift_new->st_items as $gift_id) {
                    $city_item_name = $this->getCityItemById($gift_id);
                    if ($city_item_name)
                        $received_gifts[] = array('name' => 'send_gift_new', 'friend_id' => $friend->getId(), 'st_item' => $gift_id, 'gift_name' => $city_item_name);
                    else
                        echo "Неизвестный материал, id $gift_id\n";
                }
            }
        }

        if (count($received_gifts) > 0) {
            $cached = [[
                'command' => 'mass_commit_request',
                'cmd_id' => $this->popCmdId(),
                'room_id' => $this->room->getId(),
                'data' => urlencode(json_encode($received_gifts)),
                'uxtime' => time()
            ]];

            $this->checkAndPerform($cached);

            echo 'Принято подарков: ' . count($received_gifts) . "\n";
        }
    }

    /**
     * Отправляет друзей в игровую зону
     */
    public function sendFriendsToGamblingZone() {
        foreach ($this->friends as $friend) {
            if (isset($friend->getRequests()->gambling_zone_staff_back->user) && count($friend->getRequests()->gambling_zone_staff_back->user) == 0) {
                $cached = [[
                    'command' => 'commit_request',
                    'cmd_id' => $this->popCmdId(),
                    'room_id' => $this->room->getId(),
                    'name' => 'gambling_zone_staff_back',
                    'friend_id' => $friend->getId(),
                    'item_id' => 0,
                    'uxtime' => time()
                ]];

                $this->checkAndPerform($cached);
            }
        }
    }

    /**
     * Открываем сундук
     */
    public function openChest() {
        $roll_counter = $this->room->getLocationData()->getElementsByTagName('country')->item(0)->attributes->getNamedItem('roll_counter')->nodeValue;

        $chest = $this->room->getLocationData()->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest');
        if ($chest)
            $chest = json_decode($chest->nodeValue);
        $chest_actions = $this->room->getLocationData()->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest_actions');
        $chest_time_last_open = time();
        if ($chest_actions) {
            $chest_actions = json_decode($chest_actions->nodeValue);
            $chest_time_last_open = $chest_actions->chest_event16->last_open;
        }

        $chest_action_tower1 = null;
        $chest_action_chest1 = null;
        foreach($this->room->getBarnData()->childNodes->item(0)->childNodes as $barn) {
            if ($barn->localName == 'chest_action_tower1') {
                $chest_action_tower1 = $barn->attributes->getNamedItem('quantity')->nodeValue;
            }

            if ($barn->localName == 'chest_action_chest1') {
                $chest_action_chest1 = $barn->attributes->getNamedItem('quantity')->nodeValue;
            }
        }

        if (time() - $chest_time_last_open > 3600 && $chest_action_chest1 > 0) {
            echo "Открываем сундук\n";

            $cached = [[
                'command' => 'chest_action_open_chest',
                'cmd_id' => $this->popCmdId(),
                'roll_counter' => $roll_counter,
                'room_id' => $this->room->getId(),
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
     * Забирает выручку с выполненных контрактов,
     * заключает новые, собирает монеты
     */
    public function signContracts() {
        $this->room->signContracts();
    }

    /**
     * Отправляет друзей играть в казино
     */
    public function casinoPickFriends() {
        $this->room->casinoPickFriends();
    }

    /**
     * Возвращает имя объекта по его идентификатору
     * @param $id int
     * @return null|string
     */
    public function getCityItemById($id) {
        foreach ($this->city_items as $item_name => $city_item) {
            if (isset($city_item['id']) && $city_item['id'] == $id)
                return $item_name;
        }

        return null;
    }

    /**
     * @return Friend[]
     */
    public function getFriends() {
        return $this->friends;
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

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&room_id=' . $this->room->getId() . '&owner_id=' . $friend_id . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN();
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

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&daily_gift=2&room_id=' . $this->room->getId() . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN();
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }
}