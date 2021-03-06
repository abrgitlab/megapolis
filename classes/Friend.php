<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 15:08
 */

require_once 'Bot.php';

class Friend
{

    public static $requests_not_letters = ['send_gift_new'];

    /**
     * @var $id string
     */
    public $id;

    /**
     * @var $wish_list []
     */
    public $wish_list = [];

    /**
     * @var int
     */
    public $next_gift_time;

    /**
     * @var $send_requests stdClass
     */
    public $send_requests;

    /**
     * @var $help_points int;
     */
    public $help_points = 0;

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
     * @var $first_name string
     */
    public $first_name;

    /**
     * @var $neighborhoods array
     */
    public $neighborhoods = [];

    /**
     * @var $new_friend bool
     */
    public $new_friend = false;

    /**
     * @var $is_bot bool
     */
    public $is_bot = false;

    /**
     * @var $help_step_to_pass int
     */
    private $help_step_to_pass = 0;

    /**
     * @param $xml_element SimpleXMLElement
     */
    public function loadFromXmlNode($xml_element) {
        $this->id = $xml_element->attributes()->id->__toString();
        $this->is_bot = $this->id < 0;

        if (isset($xml_element->attributes()->wish_list))
            $this->wish_list = explode(',', $xml_element->attributes()->wish_list->__toString());

        if (isset($xml_element->attributes()->next_gift_time))
            $this->next_gift_time = $xml_element->attributes()->next_gift_time->__toString();

        if (isset($xml_element->attributes()->send_requests))
            $this->send_requests = json_decode($xml_element->attributes()->send_requests->__toString());

        if (isset($xml_element->attributes()->help_points))
            $this->help_points = $xml_element->attributes()->help_points->__toString();

        if (isset($xml_element->attributes()->requests))
            $this->requests = json_decode($xml_element->attributes()->requests->__toString());

        if (isset($xml_element->attributes()->pending))
            $this->pending = $xml_element->attributes()->pending->__toString() == 'true';

        if (isset($xml_element->attributes()->active))
            $this->active = $xml_element->attributes()->active->__toString() == 'true';

        if (isset($xml_element->attributes()->city_name))
            $this->city_name = $xml_element->attributes()->city_name->__toString();

        if (isset($xml_element->attributes()->first_name))
            $this->first_name = $xml_element->attributes()->first_name->__toString();

        if (isset($xml_element->attributes()->help_items)) {
            if ($xml_element->attributes()->help_items->__toString() != '') {
                $help_items = explode(',', $xml_element->attributes()->help_items->__toString());
                foreach ($help_items as $help_item) {
                    $item = explode(':', $help_item);
                    $this->help_items[$item[0]] = $item[1];
                }
            }
        }

        if (isset($xml_element->attributes()->neighborhoods))
            $this->neighborhoods = json_decode($xml_element->attributes()->neighborhoods->__toString());

        if ($this->requests) {
            foreach ($this->requests as $request_name => $request) {
                if (!in_array($request_name, Friend::$requests_not_letters) && isset($request->count) && isset($request->user) && $request->count > 0 && !in_array(Bot::$user_id, $request->user) && $request->time > Bot::$game->server_time && $this->active) {
                    $this->letters[$request_name] = $request;
                    if ($request_name == 'invite_suggested_neighbors') {
                        $this->new_friend = true;
                    } else {
                        if (isset(Bot::$game->city_requests['requests'][$request_name]['subtype']) && Bot::$game->city_requests['requests'][$request_name]['subtype'] == 'request_building_help') {
                            ++$this->help_step_to_pass;
                        }
                    }
                }
            }
        }
    }

    public function visit() {
        $result = false;

        $friend_rooms = [0, 5, 2, 4, 1];
        $last_room_id = $friend_rooms[0];

        foreach ($friend_rooms as $room_id) {
            $steps = $this->help_points - $this->help_step_to_pass;
            if ($steps > 0) {
                Bot::log('Заходим к другу ' . $this->city_name . ' в комнату '. $room_id);
                $room_data = Bot::$game->visitFriend($this->id, $last_room_id, $room_id);
                $result = true;

                $last_room_id = $room_id;

                if ($room_data) {
                    $this->room_data = simplexml_load_string($room_data);

                    $ids = [];
                    foreach ($this->room_data->field[0] as $building) {
                        if (count($ids) >= $steps)
                            break;

                        if ($building->getName() != null) {
                            if (preg_match('/_new$/', $building->getName())) {
                                $ids[] = ['name' => $building->getName(), 'id' => $building->attributes()->id->__toString()];
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
                        ];
                    }

                    if (count($cached) > 0) {
                        Bot::log('Помогаем другу ' . count($cached) . ' сек.', [Bot::$TELEGRAM]);
                        for ($i = count($cached); $i > 0; --$i) {
                            Bot::log("Помогаем другу $i сек.");
                            $cached[count($cached) - $i]['uxtime'] = time();
                            sleep(1);
                        }

                        Bot::$game->checkAndPerformFriend($this->id, $cached);
                    }

                    $this->help_points -= count($ids);
                }
            }
        }

        return $result;
    }

}