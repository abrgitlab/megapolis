<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */
class Game
{

    //TODO: при любых отмене/принятии письма, удалять ето письмо из списка

    public static $files_directory;

//    public static $casino_materials = ['poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold'];

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
    public $city_items;

    /**
     * @var $requests_items mixed
     */
    private $requests_items;

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
     * @var $user_data mixed
     */
    private $user_data;

    /**
     * @var $session_key string
     */
    private $session_key;

    /**
     * @inheritdoc
     */
    function __construct()
    {
        Game::$files_directory = BASE_PATH . "/files";

        $this->checkUpdates();
        $this->loadCityItems();

        $this->associate();
        $user_data = $this->getUserStat();

        //Из-за UTF-8 в CDATA в разделе marketplace php не парсит xml
        $user_data = preg_replace('/<marketplace>.*<\/marketplace>/', '', $user_data);
        $user_data = preg_replace('/<neighborhoods.*<\/neighborhoods>/smi', '', $user_data);
        $user_data = preg_replace('/<items_activity .*<\/items_activity>/', '', $user_data);
        $user_data = preg_replace('/<quests_activity>.*<\/quests_activity>/', '', $user_data);
        $user_data = preg_replace('/<game_requests .*<\/game_requests>/', '', $user_data);
        $user_data = preg_replace('/<support>.*<\/support>/', '', $user_data);

        $user_data = Bot::$tidy->repairString($user_data, Bot::$tidy_config);

        $this->user_data = new DOMDocument();
        $this->user_data->loadXML($user_data);

        $this->cmd_id = $this->user_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('server_cmd_id')->nodeValue;
        $this->session_key = $this->user_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('session_key')->nodeValue;
        $room_id = $this->user_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('room_id')->nodeValue;
        $this->room = new Room($room_id, $this->user_data);
        $this->loadGiftsData();
    }

    /**
     * Проверяет новые данные city_items и city_requests
     * и скачивает их, если обновления имеются
     */
    public function checkUpdates() {
        echo "Проверка наличия обновлений\n";
        $revision_data = $this->getRevision();
        $revision_data = Bot::$tidy->repairString($revision_data, Bot::$tidy_config);

        $revision_data_xml = new DOMDocument();
        $revision_data_xml->loadXML($revision_data);

        $this->revision = $revision_data_xml->getElementsByTagName('revision_info')->item(0)->attributes->getNamedItem('revision')->nodeValue;
        if (!is_dir(Game::$files_directory))
            mkdir(Game::$files_directory);

        $files_for_loading = [];
        if (!file_exists(Game::$files_directory . "/city_items.yml.$this->revision"))
            $files_for_loading[] = 'city_items';
        if (!file_exists(Game::$files_directory . "/city_requests.yml.$this->revision"))
            $files_for_loading[] = 'city_requests';

        if (count($files_for_loading) > 0) {
            echo "Получение обновлений\n";
            $files = scandir(Game::$files_directory);
            $old_files = [];
            foreach ($files as $index => $file) {
                if (!in_array($file, ['.', '..']))
                    $old_files[] = $file;
            }

            foreach ($files_for_loading as $file) {
                $yaml = file_get_contents("http://mb.static.socialquantum.ru/mobile_assets/$file.yml?rev=$this->revision");
                file_put_contents(Game::$files_directory . "/$file.yml.$this->revision", $yaml);

                foreach ($old_files as &$old_file) {
                    if (substr($old_file, 0, strlen($file)) == $file) {
                        if (Bot::$options['debug'])
                            echo 'Удаляем ' . Game::$files_directory . '/' . $old_file . "\n";
                        unlink(Game::$files_directory . '/' . $old_file);
                        unset($old_file);
                    }
                }
            }
        }
    }

    /**
     * Загружает данные комнаты с номером id
     * @param $id int
     */
    public function changeRoom($id) {
        Bot::$last_room_id = $this->room->id;
        $this->room = new Room($id, false);
    }

    /**
     * Загружает данные объектов игры из yml-файла
     */
    public function loadCityItems() {
        $this->city_items = yaml_parse(file_get_contents(Game::$files_directory . "/city_items.yml.$this->revision"));
        $this->requests_items = yaml_parse(file_get_contents(Game::$files_directory . "/city_requests.yml.$this->revision"));
    }

    /**
     * Загружает список друзей
     */
    public function loadFriends() {
        $friends = $this->user_data->getElementsByTagName('friends');
        if ($friends) {
            foreach ($friends->item(0)->childNodes as $friend_item) {
                if ($friend_item->localName == 'friend') {
                    $friend = new Friend();

                    $friend->loadFromXmlNode($friend_item);
                    $friend_is_neighborhood = false;
                    foreach ($friend->neighborhoods as $n_id)
                        if ($n_id == Bot::$neighborhood_id)
                            $friend_is_neighborhood = true;
                    if (!$friend->pending || $friend->id < 0 || $friend_is_neighborhood)
                        $this->friends[] = $friend;
                }
            }
        }
        if (Bot::$options['debug']) echo 'Друзей: ' . count($this->friends) . "\n";
    }

    /**
     * Показывает количество и содержимое писем
     * TODO: временная функция
     */
    public function showLetters() {
        $letters_amount = 0;
        foreach ($this->friends as $friend) {
            if (count($friend->letters) > 0) {
                echo $friend->id . "\n";
                var_dump($friend->letters);
                $letters_amount += count($friend->letters);
            }
        }
        echo "Писем: $letters_amount\n";
    }

    /**
     * Отказывает друзьям в просьбе материалов
     */
    public function discardAskMaterial() {
        $items = [];
        foreach ($this->friends as $friend) {
            foreach ($friend->letters as $letter_name => $letter_params) {
                if ($letter_name == 'ask_material_common') {
                    $items[] = [
                        'command' => 'discard_request',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'name' => $letter_name,
                        'friend_id' => $friend->id
                    ];

                    unset($friend->letters['ask_material_common']);
                }
            }
        }

        for ($i = count($items); $i > 0; --$i) {
            echo "Отказываем друзьям в материалах $i сек.\n";
            $current = [$items[count($items) - $i]];
            $current[0]['uxtime'] = time();
            $this->checkAndPerform($current);
            sleep(1);
        }
    }

    /**
     * Отправляет нефть друзьям
     */
    /*public function sendFuelToFriends() {
        $items = [];
        foreach ($this->friends as $friend) {
            foreach ($friend->letters as $letter_name => $letter_params) {
                if ($letter_name == 'request_fuel') {
                    $items[] = [
                        'command' => 'commit_request',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'name' => $letter_name,
                        'friend_id' => $friend->id
                    ];

                    unset($friend->letters['request_fuel']);
                }
            }
        }

        for ($i = count($items); $i > 0; --$i) {
            echo "Отправка нефти друзьям $i сек.\n";
            $current = [$items[count($items) - $i]];
            $current[0]['uxtime'] = time();
            $this->checkAndPerform($current);
            sleep(1);
        }
    }*/

    /**
     * Удаляет невыгодные письма
     */
    public function handleLetters() { //TODO: определять, когда достигнут дневной лимит отвеченных писем
        $items = [];
        foreach ($this->friends as $friend) {
            foreach ($friend->letters as $letter_name => $letter_params) {

                $profit = [
                    'expirience' => 0,
                    'coins' => 0
                ];
                $request_template = $this->requests_items['requests'][$letter_name];
                if (isset($request_template['reward'])) {
                    foreach ($request_template['reward'] as $reward) {
                        if (isset($reward['items'])) {
                            foreach ($reward['items'] as $item) {
                                if (isset($item['exp']['min_quantity'])) {
                                    $profit['expirience'] = $item['exp']['min_quantity'];
                                }
                                if (isset($item['coins']['min_quantity'])) {
                                    $profit['coins'] = $item['coins']['min_quantity'];
                                }
                            }
                            break;
                        }
                    }
                }

                if ($profit['expirience'] >= 200) {
                    $items[] = [
                        'command' => 'commit_request',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'name' => $letter_name,
                        'friend_id' => $friend->id
                    ];

                    unset($friend->letters[$letter_name]);
                } elseif ($profit['expirience'] < 100 && $profit['coins'] < 500) {
                    $items[] = [
                        'command' => 'discard_request',
                        'cmd_id' => $this->popCmdId(),
                        'room_id' => $this->room->id,
                        'name' => $letter_name,
                        'friend_id' => $friend->id
                    ];

                    unset($friend->letters[$letter_name]);
                }
            }
        }

        for ($i = count($items); $i > 0; --$i) {
            echo "Обработка писем $i сек.\n";
            $current = [$items[count($items) - $i]];
            $current[0]['uxtime'] = time();
            $this->checkAndPerform($current);
            sleep(1);
        }
    }

    /**
     * Принимает просящихся в друзья
     */
    public function acceptFriends() {
        foreach ($this->friends as $friend) {
            /*if (isset($friend->requests->invite_suggested_neighbors) && isset($friend->requests->pending)) {
                if ($friend->pending === true && $friend->requests->invite_suggested_neighbors->count > 0) {
                    echo 'Принимаем в друзья город ' . $friend->id . "\n";
                    $this->processAcceptFriend($friend->id, $friend->requests->invite_suggested_neighbors->pushed);
                }
            }*/
            if (isset($friend->letters['invite_suggested_neighbors'])) {
                echo 'Принимаем в друзья город ' . $friend->id . "\n";
                $this->processAcceptFriend($friend->id, $friend->letters['invite_suggested_neighbors']->pushed);

                unset($friend->letters['invite_suggested_neighbors']);
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
                $this->goHome();
            }
        }
    }

    /**
     * Возвращаемся в родной город
     */
    public function goHome() {

    }

    public function goToSnowville() {
        $this->changeRoom(100);
    }

    public function doSnowvilleProductionWork() {
        if ($this->room->id != 100)
            return;

        $this->room->doSnowvilleFactoryWork();
    }

    public function goFromSnowville() {
        if ($this->room->id != 100)
            return;

        $location_data = $this->getBackFromSnowvilleStat();

        $location_data = preg_replace('/<marketplace>.*<\/marketplace>/', '', $location_data);
        $location_data = preg_replace('/<quests_activity>.*<\/quests_activity>/', '', $location_data);
        $location_data = preg_replace('/<military_orders.*<\/military_orders>/', '', $location_data);

        $location_data = Bot::$tidy->repairString($location_data, Bot::$tidy_config);

        $location_data_xml = new DOMDocument();
        $location_data_xml->loadXML($location_data);

        Bot::$last_room_id = 100;
        $this->room = new Room(0, $location_data_xml);
    }

    /**
     * Получение помощи от друзей
     */
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
        $gifts_data = $this->user_data->getElementsByTagName('gifts');
        if ($gifts_data) {
            $gifts_data_xml = new DOMDocument();
            $gifts_data_xml->loadXML($this->user_data->saveXML($gifts_data->item(0)));

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

                unset($friend->letters['gambling_zone_staff_back']);
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
        $chest_name = 'chest_event27';

        $roll_counter = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('roll_counter')->nodeValue;

        /*$chest = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest');
        if ($chest)
            $chest = json_decode($chest->nodeValue);*/
        $chest_actions = $this->room->location_data->getElementsByTagName('country')->item(0)->attributes->getNamedItem('chest_actions');
        $chest_time_last_open = time();
        if ($chest_actions) {
            $chest_actions = json_decode($chest_actions->nodeValue);
            $chest_time_last_open = $chest_actions->$chest_name->last_open;
        }

        $chest_action_tower1 = null;
        $chest_action_chest1 = $this->room->getBarnQuantity('chest_action_chest1');

        if (time() - $chest_time_last_open > 3600 && $chest_action_chest1 > 0) {
            echo "Открываем сундук\n";

            $cached = [[
                'command' => 'chest_action_open_chest',
                'cmd_id' => $this->popCmdId(),
                'roll_counter' => $roll_counter,
                'room_id' => $this->room->id,
                'name' => $chest_name,
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
        return $this->rn++;
    }

    /**
     * @return int
     */
    public function popCmdId() {
        return ++$this->cmd_id;
    }

    /**
     *
     */
    public function associate() {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/associate');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&no_field=true&social_id[SQ]=abr_mail%40mail.ru&social_id[GS]=105865456413157542698&social_id[GPG]=105865456413157542698&device_id=' . Bot::$device_id . '&platform=android&build=' . Bot::$build . '&app=city&device_model=Genymotion%20vbox86p&os=4.1.1&gloc=ru&dloc=ru&net=wf&social=sqsocial%3Aprod&odin_id=' . Bot::$odin_id . '&android_id=' . Bot::$android_id . '&mac=' . Bot::$mac . '&advertising_id=' . Bot::$advertising_id;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);
    }

    /**
     * @return string
     */
    public function getUserStat() {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&revision=android-' . Bot::$client_version . '.' . Bot::$build . '&allow_personal_information=1&user_first_name=%D0%94%D0%BC%D0%B8%D1%82%D1%80%D0%B8%D0%B9&user_last_name=%D0%9C%D0%B0%D0%BB%D0%B0%D1%85%D0%BE%D0%B2&user_sex=0&access_token=' . Bot::$iauth . '&lang=ru&client_type=android&room_id=0&odin_id=' . Bot::$odin_id . '&android_id=' . Bot::$android_id . '&mac=' . Bot::$mac . '&advertising_id=' . Bot::$advertising_id . '&device_id=' . Bot::$device_id . '&first_request=true&location=&rn=' . $this->popRN() . '&content_rev=' . $this->revision . '&app_store_name=com.android.vending';
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
     * @return string
     */
    public function getRoomStat($room_id) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&room_id=' . Bot::$last_room_id . '&change_room=1&view_room_id=' . $room_id . '&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @return string
     */
    public function getBackFromSnowvilleStat() {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&room_id=100&change_room=1&view_room_id=0&lang=ru&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
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

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&view_friend_id=' . $friend_id . '&room_id=' . $last_room_id . '&change_room=1&view_room_id=' . $room_id . '&lang=ru&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $from_friend string
     */
    public function goHomeRequest($from_friend) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&owner_id=' . $from_friend . '&room_id=' . $this->room->id . '&change_room=1&view_room_id=' . $this->room->id . '&lang=ru&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);
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

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&room_id=' . $this->room->id . '&owner_id=' . $friend_id . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $friend_id string
     * @param $pushed string
     * @return string
     */
    public function processAcceptFriend($friend_id, $pushed) {
        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/process');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&command=commit_request&cmd_id=' . $this->popCmdId() . '&room_id=' . $this->room->id . '&name=invite_suggested_neighbors&friend_id=' . $friend_id . '&count=1&pushed=' . $pushed . '&room_id=' . $this->room->id . '&only_head=1&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
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

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&room_id=' . $this->room->id . '&serv_ver=1' . $cached_string . '&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        if (Bot::$options['debug']) echo "\n$url\n\n";

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }
}
