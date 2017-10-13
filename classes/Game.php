<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 10:55
 */

require_once 'Bot.php';
require_once 'Room.php';
require_once 'Friend.php';

class Game
{

    public static $files_directory;

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
     * @var $city_items []
     */
    public $city_items;

    /**
     * @var $city_item_ids []
     */
    public $city_item_ids;

    /**
     * @var $city_requests []
     */
    private $city_requests;

    /**
     * @var $city_quests []
     */
    public $city_quests;

    /**
     * @var $room_id Room
     */
    public $room;

    /**
     * @var $friends Friend[]
     */
    public $friends = [];

    /**
     * @var $available_gifts []
     */
    private $available_gifts;

    /**
     * @var $user_data SimpleXMLElement
     */
    private $user_data;

    /**
     * @var $session_key string
     */
    private $session_key;

    /**
     * @var $online boolean
     */
    public $online;

    /**
     * @var int
     */
    public $server_time;

    public $neighborhood_id;

    /**
     * @inheritdoc
     */
    function __construct($online = true)
    {
        $this->online = $online;

        Game::$files_directory = MEGAPOLIS_PATH . "/files";

        $user_data_file_name = MEGAPOLIS_PATH . DIRECTORY_SEPARATOR . 'rooms' . DIRECTORY_SEPARATOR . 'user_stat';
        if ($online) {
            $this->checkUpdates();

           $this->associate();
            $user_data = $this->getUserStat();
            file_put_contents($user_data_file_name, $user_data);
        } else {
            $old_revisions = $this->getCachedRevisions();
            $this->revision = $old_revisions[count($old_revisions) - 1];
            $user_data = file_get_contents($user_data_file_name);
        }

        $this->loadYaml();

        $this->user_data = simplexml_load_string($user_data);

        $this->cmd_id = $this->user_data->attributes()->server_cmd_id->__toString();
        $this->session_key = $this->user_data->attributes()->session_key->__toString();
        $this->server_time = $this->user_data->attributes()->server_time->__toString();
        $room_id = $this->user_data->attributes()->room_id->__toString();
        $this->room = new Room($room_id, $this->user_data);
        $this->loadGiftsData();

        if (isset($this->user_data->neighborhoods) && isset($this->user_data->neighborhoods[0]->attributes()->neighborhoods)) {
            $this->neighborhood_id = json_decode($this->user_data->neighborhoods[0]->attributes()->neighborhoods->__toString())[0];
        }
    }

    public function getCityAttribute($attribute) {
        if (isset($this->user_data->attributes()->{$attribute})) {
            return $this->user_data->attributes()->{$attribute}->__toString();
        }

        return null;
    }

    /**
     * Проверяет новые данные city_items и city_requests
     * и скачивает их, если обновления имеются
     */
    public function checkUpdates() {
        Bot::log('Проверка наличия обновлений', [Bot::$STDOUT, Bot::$TELEGRAM]);
        $revision_data = $this->getRevision();
        $revision_data = simplexml_load_string($revision_data);
        $this->revision = $revision_data->attributes()->revision->__toString();
        if (!is_dir(Game::$files_directory))
            mkdir(Game::$files_directory);

        $files_for_loading = [];
        $old_cache = $this->getCachedRevisions();

        if (!is_dir(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision))
            mkdir(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision);

        if (!file_exists(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_items.yml"))
            $files_for_loading[] = 'city_items';
        if (!file_exists(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_requests.yml"))
            $files_for_loading[] = 'city_requests';
        if (!file_exists(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_quests.yml"))
            $files_for_loading[] = 'city_quests';

        if (count($files_for_loading) > 0) {
            Bot::log('Получение обновлений', [Bot::$STDOUT, Bot::$TELEGRAM]);

            foreach ($files_for_loading as $file) {
                $yaml = file_get_contents("http://mb.static.socialquantum.ru/mobile_assets/$file.yml?rev=$this->revision");
                file_put_contents(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "/$file.yml", $yaml);
            }

            foreach ($old_cache as $old_catalog) {
                Bot::log('Удаляем ' . Game::$files_directory . DIRECTORY_SEPARATOR . $old_catalog, [Bot::$DEBUG]);
                $this->deleteDir(Game::$files_directory . DIRECTORY_SEPARATOR . $old_catalog);
            }
        }
    }

    private function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Возвращает имена каталогов со старыми версиями кешированных файлов
     * @return array
     */
    private function getCachedRevisions() {
        $files = scandir(Game::$files_directory);
        $old_directories = [];
        foreach ($files as $index => $file) {
            if (!in_array($file, ['.', '..', $this->revision]))
                $old_directories[] = $file;
        }

        return $old_directories;
    }

    /**
     * Загружает данные комнаты с номером id
     * @param $id int
     */
    public function changeRoom($id) {
        if ($this->room !== null)
            Bot::$last_room_id = $this->room->id;

        $this->room = new Room($id, false);
    }

    /**
     * Загружает данные объектов игры из yml-файла
     */
    public function loadYaml() {
        $this->city_items = yaml_parse(file_get_contents(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_items.yml"));
        $this->city_requests = yaml_parse(file_get_contents(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_requests.yml"));
        $this->city_quests = yaml_parse(file_get_contents(Game::$files_directory . DIRECTORY_SEPARATOR . $this->revision . DIRECTORY_SEPARATOR . "city_quests.yml"));

        $this->city_item_ids = [];
        foreach ($this->city_items as $item_name => $item) {
            $this->city_item_ids[$item['id']] = $item;
            $this->city_item_ids[$item['id']]['item_name'] = $item_name;
        }
    }

    /**
     * Загружает список друзей
     */
    public function loadFriends() {
        if (isset($this->user_data->friends[0])) {
            foreach ($this->user_data->friends[0] as $friend_item) {
                if ($friend_item->getName() == 'friend') {
                    $friend = new Friend();

                    $friend->loadFromXmlNode($friend_item);

                    $friend_is_neighborhood = false;
                    if ($this->neighborhood_id != null) {
                        foreach ($friend->neighborhoods as $n_id)
                            if ($n_id == $this->neighborhood_id)
                                $friend_is_neighborhood = true;
                    }
                    if (!$friend->pending || $friend->is_bot || $friend_is_neighborhood || $friend->new_friend)
                        $this->friends[] = $friend;
                }
            }
        }
        Bot::log('Друзей: ' . count($this->friends), [Bot::$DEBUG]);
    }

    /**
     * Показывает количество и содержимое писем
     */
    public function showLetters() {
        $letters_amount = 0;
//        foreach ($this->friends as $friend) {
//            if (count($friend->letters) > 0 && !$friend->new_friend) {
//                Bot::log($friend->id/*, [Bot::$DEBUG]*/);
//                //var_dump($friend->letters);
//                $letters_amount += count($friend->letters);
//            }
//        }
//        Bot::log("Писем: $letters_amount"/*, [Bot::$DEBUG]*/);

        foreach ($this->friends as $friend) {
            foreach ($friend->requests as $request_name => $request) {
                if (
                    !in_array($request_name, Friend::$requests_not_letters) &&
                    isset($request->count) &&
                    isset($request->user) &&
                    $request->count == 0 &&
                    in_array(Bot::$user_id, $request->user) &&
                    $request->time > $this->server_time &&
                    $friend->active &&
                    !$friend->new_friend
                ) {
                    ++$letters_amount;
                }
            }
        }

        Bot::log("Писем обработано: $letters_amount"/*, [Bot::$DEBUG]*/);
    }

    /**
     * Отказывает друзьям в просьбе материалов
     */
    public function discardAskMaterial() {
        $items = [];
        foreach ($this->friends as $friend) {
            if (!$friend->new_friend) {
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
        }

        if (count($items) > 0) {
            Bot::log('Отказываем друзьям в материалах ' . count($items) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($items); $i > 0; --$i) {
                Bot::log("Отказываем друзьям в материалах $i сек.");
                $current = [$items[count($items) - $i]];
                $current[0]['uxtime'] = time();
                $this->checkAndPerform($current);
                sleep(1);
            }
        }
    }

    /**
     * Отправляет нефть друзьям
     */
    public function sendFuelToFriends() {
        $items = [];
        foreach ($this->friends as $friend) {
            if (!$friend->new_friend) {
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
        }

        if (count($items) > 0) {
            Bot::log('Отправка нефти друзьям ' . count($items) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($items); $i > 0; --$i) {
                Bot::log("Отправка нефти друзьям $i сек.");
                $current = [$items[count($items) - $i]];
                $current[0]['uxtime'] = time();
                $this->checkAndPerform($current);
                sleep(1);
            }
        }
    }

    /**
     * Удаляет невыгодные письма
     */
    public function handleLetters() { //TODO: определять, когда достигнут дневной лимит отвеченных писем
        $items = [];
        foreach ($this->friends as $friend) {
            if (!$friend->new_friend) {
                foreach ($friend->letters as $letter_name => $letter_params) {

                    $profit = [
                        'expirience' => 0,
                        'coins' => 0
                    ];
                    $request_template = $this->city_requests['requests'][$letter_name];
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
                    } elseif ($profit['expirience'] < 100 && $profit['coins'] < 2000) {
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
        }

        if (count($items) > 0) {
            Bot::log('Обработка писем ' . count($items) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($items); $i > 0; --$i) {
                Bot::log("Обработка писем $i сек.");
                $current = [$items[count($items) - $i]];
                $current[0]['uxtime'] = time();
                $this->checkAndPerform($current);
                sleep(1);
            }
        }
    }

    /**
     * Принимает просящихся в друзья
     */
    public function acceptFriends() {
        foreach ($this->friends as $friend) {
            if ($friend->new_friend) {
            //if (isset($friend->letters['invite_suggested_neighbors'])) {
                Bot::log('Принимаем в друзья город ' . $friend->city_name, [Bot::$STDOUT, Bot::$TELEGRAM]);
                $this->processAcceptFriend($friend->id, $friend->letters['invite_suggested_neighbors']->pushed);

                //$friend->new_friend = false; //TODO: получать результат добавления. Если друг добавлен, то $friend->new_friend = false, если добавить не получилось, удаляем из списка друзей
                //unset($friend->letters['invite_suggested_neighbors']);
            }
        }
    }

    /**
     * Посещение друзей
     */
    public function visitFriends() {
        Bot::log('Заходим к друзьям...', [Bot::$TELEGRAM]);
        foreach ($this->friends as $friend) {
            if ($friend->id != '-41' && $friend->id != '-43' && !$friend->new_friend) {
                if ($friend->visit())
                    $this->goHome();
            }
        }
    }

    /**
     * Возвращаемся в родной город
     */
    public function goHome() { //TODO: а чё оно пустое?

    }

    /**
     * Получение помощи от друзей
     */
    public function applyHelp() {
        $cached = [];
        foreach ($this->friends as $friend) {
            if (!$friend->new_friend) {
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
        }

        if (count($cached) > 0) {
            Bot::log('Получение помощи от друзей ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Получение помощи от друзей $i сек.");
                $cached[count($cached) - $i]['uxtime'] = time();
                sleep(1);
            }
        }

        $this->checkAndPerform($cached);
    }

    /**
     * @return Friend[]
     */
    /*public function getFriendsForInviteInGamblingZone() {
        $result = [];
        foreach ($this->friends as $friend) {
            if ($friend->send_requests && !$friend->new_friend) {
                if (
                    !isset($friend->send_requests->gambling_zone_staff) &&
                    !(
                        isset($friend->requests->gambling_zone_staff_back->user) &&
                        count($friend->requests->gambling_zone_staff_back->user) == 0 &&
                        $this->server_time - $friend->requests->gambling_zone_staff_back->pushed > 86400
                    )
                ) {
                    $result[] = $friend;
                }
            }
        }

        return $result;
    }*/

    /**
     * Загружает данные о подарках
     */
    public function loadGiftsData() {
        $this->available_gifts = [];
        if (isset($this->user_data->gifts[0]->available[0])) {
            foreach ($this->user_data->gifts[0]->available[0] as $item) {
                if (isset($item->attributes()->quantity) && isset($item->attributes()->id)) {
                    for ($i = 0; $i < $item->attributes()->quantity->__toString(); ++$i) {
                        $this->available_gifts[] = $item->attributes()->id->__toString();
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
            if (isset($friend->requests->send_gift_new) && !$friend->new_friend) {
                foreach ($friend->requests->send_gift_new->st_items as $gift_id) {
                    $city_item_name = $this->getCityItemById($gift_id)['item_name'];
                    if ($city_item_name)
                        $received_gifts[] = array('name' => 'send_gift_new', 'friend_id' => $friend->id, 'st_item' => $gift_id, 'gift_name' => $city_item_name);
                    else
                        Bot::log("Неизвестный материал, id $gift_id", [Bot::$STDOUT, Bot::$TELEGRAM]);
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

            Bot::log('Принято подарков: ' . count($received_gifts), [Bot::$STDOUT, Bot::$TELEGRAM]);
        }
    }

    /**
     * Раздаривает подарки друзьям
     */
    public function sendGifts($send_the_rest_gifts = false) {
        $sending_gifts = [];
        foreach ($this->friends as $friend) {
            if ($friend->next_gift_time < 0 && !$friend->new_friend) {
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
            Bot::log('Ждём раздаривания подарков ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Ждём раздаривания подарков $i сек.");
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
            if (isset($friend->requests->gambling_zone_staff_back->user) && count($friend->requests->gambling_zone_staff_back->user) == 0 && !$friend->new_friend) {
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
            Bot::log('Отправляем друзей в игровую зону ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
            for ($i = count($cached); $i > 0; --$i) {
                Bot::log("Отправляем друзей в игровую зону $i сек.");
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
        $chest_name = 'chest_event29';

        if (!isset($this->room->location_data->attributes()->chest_actions))
            return;

        $chest_actions = json_decode($this->room->location_data->attributes()->chest_actions->__toString());

        $chest_actions_keys = get_object_vars($chest_actions);
        if (count($chest_actions_keys) === 0)
            return;

        foreach ($chest_actions_keys as $key => $item) {
            $chest_name = $key;
            break;
        }

        $chest_time_last_open = $chest_actions->$chest_name->last_open;

        $roll_counter = $this->room->location_data->attributes()->roll_counter->__toString();

        $chest_action_tower1 = null;
        $chest_action_chest1 = $this->room->getBarnQuantity('chest_action_chest1');

        if ($this->server_time - $chest_time_last_open > 3600 && $chest_action_chest1 > 0) {
            Bot::log('Открываем сундук', [Bot::$STDOUT, Bot::$TELEGRAM]);

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
        if (isset($this->city_item_ids[$id]))
            return $this->city_item_ids[$id];

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
     * @return string
     */
    public function associate() {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/associate');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&no_field=true&social_id[SQ]=abr_mail%40mail.ru&social_id[GS]=105865456413157542698&social_id[GPG]=105865456413157542698&device_id=' . Bot::$device_id . '&platform=android&build=' . Bot::$build . '&app=city&device_model=Genymotion%20vbox86p&os=4.1.1&gloc=ru&dloc=ru&net=wf&social=sqsocial%3Aprod&odin_id=' . Bot::$odin_id . '&android_id=' . Bot::$android_id . '&mac=' . Bot::$mac . '&advertising_id=' . Bot::$advertising_id;
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @return string
     */
    public function getUserStat() {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&revision=android-' . Bot::$client_version . '.' . Bot::$build . '&allow_personal_information=1&user_first_name=%D0%94%D0%BC%D0%B8%D1%82%D1%80%D0%B8%D0%B9&user_last_name=%D0%9C%D0%B0%D0%BB%D0%B0%D1%85%D0%BE%D0%B2&user_sex=0&access_token=' . Bot::$iauth . '&lang=ru&client_type=android&room_id=0&odin_id=' . Bot::$odin_id . '&android_id=' . Bot::$android_id . '&mac=' . Bot::$mac . '&advertising_id=' . Bot::$advertising_id . '&device_id=' . Bot::$device_id . '&first_request=true&location=&rn=' . $this->popRN() . '&content_rev=' . $this->revision . '&app_store_name=com.android.vending';
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @return string
     */
    public function getRevision() {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host_static . '/mobile_assets/revision.xml?rand=' . time());
        curl_setopt(Bot::$curl, CURLOPT_POST, false);
        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $room_id int
     * @return string
     */
    public function getRoomStat($room_id) {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&room_id=' . Bot::$last_room_id . '&change_room=1&view_room_id=' . $room_id . '&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        Bot::log("\n$url\n", [Bot::$DEBUG]);

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
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&view_friend_id=' . $friend_id . '&room_id=' . $last_room_id . '&change_room=1&view_room_id=' . $room_id . '&lang=ru&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $from_friend string
     * @return string
     */
    public function goHomeRequest($from_friend) {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/get_user_stat');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&owner_id=' . $from_friend . '&room_id=' . $this->room->id . '&change_room=1&view_room_id=' . $this->room->id . '&lang=ru&client_type=android&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $friend_id string
     * @param $cached array
     * @return string
     */
    public function checkAndPerformFriend($friend_id, $cached) {
        if (!$this->online && count($cached) == 0)
            return null;

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
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $friend_id string
     * @param $pushed string
     * @return string
     */
    public function processAcceptFriend($friend_id, $pushed) {
        if (!$this->online)
            return null;

        curl_setopt(Bot::$curl, CURLOPT_URL, 'http://' . Bot::$host . '/city_server_sqint_prod/process');
        curl_setopt(Bot::$curl, CURLOPT_POST, true);

        $url = 'daily_gift=2&iauth=' . Bot::$iauth . '&user_id=' . Bot::$user_id . '&session_key=' . $this->session_key . '&command=commit_request&cmd_id=' . $this->popCmdId() . '&room_id=' . $this->room->id . '&name=invite_suggested_neighbors&friend_id=' . $friend_id . '&count=1&pushed=' . $pushed . '&room_id=' . $this->room->id . '&only_head=1&serv_ver=1&lang=ru&rand=0.' . rand(0, 9999999) . '&live_update=true&rn=' . $this->popRN() . '&content_rev=' . $this->revision;
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }

    /**
     * @param $cached array
     * @return string
     */
    public function checkAndPerform($cached) {
        if (!$this->online || count($cached) == 0)
            return null;

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
        Bot::log("\n$url\n", [Bot::$DEBUG]);

        curl_setopt(Bot::$curl, CURLOPT_POSTFIELDS, $url);

        return gzdecode(curl_exec(Bot::$curl));
    }
}
