<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 15:08
 */
class Friend
{

    private static $requests_not_letters = ['send_gift_new'];

    /**
     * @var $id string
     */
    public $id;

    /**
     * @var $wish_list array
     */
    public $wish_list = [];

    /**
     * @var int
     */
    public $next_gift_time;

    /**
     * @var $send_requests mixed
     */
    public $send_requests;

    /**
     * @var $help_points int;
     */
    private $help_points = 0;

    /**
     * @var $help_points array;
     */
    public $help_items = [];

    /**
     * @var $requests mixed
     */
    public $requests;

    /**
     * @var $letters array
     */
    public $letters = [];

    /**
     * @var $active boolean
     */
    public $active = false;

    /**
     * @var $pending boolean
     */
    public $pending = true;

    /**
     * @var $room_data DOMNode
     */
    private $room_data;

    /**
     * @var $city_name string
     */
    public $city_name;

    /**
     * @var $neighborhoods array
     */
    public $neighborhoods = [];

    /**
     * @param $xml_node DOMNode
     */
    public function loadFromXmlNode($xml_node) {
        $this->id = $xml_node->attributes->getNamedItem('id')->nodeValue;
        $wish_list = $xml_node->attributes->getNamedItem('wish_list');
        if ($wish_list)
            $this->wish_list = explode(',', $wish_list->nodeValue);

        $next_gift_time = $xml_node->attributes->getNamedItem('next_gift_time');
        if ($next_gift_time)
            $this->next_gift_time = $next_gift_time->nodeValue;

        $send_requests = $xml_node->attributes->getNamedItem('send_requests');
        if ($send_requests)
            $this->send_requests = json_decode($send_requests->nodeValue);

        $help_points = $xml_node->attributes->getNamedItem('help_points');
        if ($help_points)
            $this->help_points = $help_points->nodeValue;

        $requests = $xml_node->attributes->getNamedItem('requests');
        if ($requests)
            $this->requests = json_decode($requests->nodeValue);

        $pending = $xml_node->attributes->getNamedItem('pending');
        if ($pending)
            $this->pending = $pending->nodeValue == 'true';

        $active = $xml_node->attributes->getNamedItem('active');
        if ($active)
            $this->active = $active->nodeValue == 'true';

        $city_name = $xml_node->attributes->getNamedItem('city_name');
        if ($city_name)
            $this->city_name = utf8_decode($city_name->nodeValue);

        $help_items = $xml_node->attributes->getNamedItem('help_items');
        if ($help_items) {
            if ($help_items->nodeValue != '') {
                $help_items = explode(',', $help_items->nodeValue);
                foreach ($help_items as $help_item) {
                    $item = explode(':', $help_item);
                    $this->help_items[$item[0]] = $item[1];
                }
            }
        }

        $neighborhoods = $xml_node->attributes->getNamedItem('neighborhoods');
        if ($neighborhoods)
            $this->neighborhoods = json_decode($neighborhoods->nodeValue);

        if ($this->requests) {
            foreach ($this->requests as $request_name => $request) {
                if (!in_array($request_name, Friend::$requests_not_letters) && isset($request->count) && isset($request->user) && ($request->count > 0) && !in_array(Bot::$user_id, $request->user) && ($request->time > time()) && ($this->active)) {
                    $this->letters[$request_name] = $request;
                }
            }
        }
    }

    public function visit() {
        $friend_rooms = [0, 5, 2, 4, 1];
        $last_room_id = $friend_rooms[0];

        Bot::log('Заходим к друзьям...', [Bot::$TELEGRAM]);
        foreach ($friend_rooms as $room_id) {
            if ($this->help_points > 0) {
                Bot::log('Заходим к другу с ID ' . $this->id . ' в комнату '. $room_id);
                //echo 'Заходим к другу с ID ' . $this->id . ' в комнату '. $room_id . "\n";
                $room_data = Bot::$game->visitFriend($this->id, $last_room_id, $room_id);
                $room_data = preg_replace('/<neighborhoods.*<\/neighborhoods>/smi', '', $room_data);
                $room_data = preg_replace('/<friend_neighborhoods.*<\/friend_neighborhoods>/', '', $room_data);
                $room_data = preg_replace('/<items_activity .*<\/items_activity>/', '', $room_data);
                $room_data = preg_replace('/<quests_activity>.*<\/quests_activity>/', '', $room_data);
                $room_data = preg_replace('/<military_orders .*<\/military_orders>/', '', $room_data);
                $room_data = preg_replace('/<game_requests .*<\/game_requests>/', '', $room_data);
                $room_data = preg_replace('/<support>.*<\/support>/', '', $room_data);

                $last_room_id = $room_id;

                $room_data = Bot::$tidy->repairString($room_data, Bot::$tidy_config);

                $this->room_data = new DOMDocument();
                $this->room_data->loadXML($room_data);

                $ids = [];
                foreach ($this->room_data->getElementsByTagName('field')->item(0)->childNodes as $building) {
                    if (count($ids) >= $this->help_points)
                        break;

                    if ($building->localName != NULL) {
                        if (substr($building->localName, strlen($building->localName) - 4, 4) == '_new') {
                            $ids[] = ['name' => $building->localName, 'id' => $building->attributes->getNamedItem('id')->nodeValue];
                        }
                    }
                }

                $cached = [];
                foreach ($ids as $id) {
                    $cached[] = [
                        'command' => 'help',
                        'cmd_id' => Bot::$game->popCmdId(),
                        'room_id' => $room_id,
                        'owner_id' => $this->id,
                        'item_id' => $id['id'],
                        'friend_id' => $this->id,
                        'klass' => $id['name'],
                        'uxtime' => time()
                    ];
                }

                Bot::$game->checkAndPerformFriend($this->id, $cached);

                $this->help_points -= count($ids);
            }
        }
    }

}