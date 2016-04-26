<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 15:08
 */
class Friend
{

    /**
     * @var $id string
     */
    private $id;

    /**
     * @var $wish_list array
     */
    private $wish_list = [];

    /**
     * @var int
     */
    private $next_gift_time;

    /**
     * @var $send_requests mixed
     */
    private $send_requests;

    /**
     * @var $help_points int;
     */
    private $help_points = 0;

    /**
     * @var $help_points array;
     */
    private $help_items = [];

    /**
     * @var $requests mixed
     */
    private $requests;

    /**
     * @var $room_data DOMNode
     */
    private $room_data;

    /**
     * @var $city_name string
     */
    private $city_name;

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
    }

    public function visit() {
        $friend_rooms = [0, 5, 2, 4, 1];
        $last_room_id = $friend_rooms[0];

        foreach ($friend_rooms as $room_id) {
            if ($this->help_points > 0) {
                echo 'Заходим к другу с ID ' . $this->id . ' в комнату '. $room_id . "\n";
                $room_data = Bot::getGame()->visitFriend($this->id, $last_room_id, $room_id);

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
                        'cmd_id' => Bot::getGame()->popCmdId(),
                        'room_id' => $room_id,
                        'owner_id' => $this->id,
                        'item_id' => $id['id'],
                        'friend_id' => $this->id,
                        'klass' => $id['name'],
                        'uxtime' => time()
                    ];
                }

                Bot::getGame()->checkAndPerformFriend($this->id, $cached);

                $this->help_points -= count($ids);
            }
        }
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSendRequests() {
        return $this->send_requests;
    }

    /**
     * @return mixed
     */
    public function getRequests() {
        return $this->requests;
    }

    /**
     * @return array
     */
    public function getHelpItems() {
        return $this->help_items;
    }

    /**
     * @return array
     */
    public function getWishList() {
        return $this->wish_list;
    }

    /**
     * @return int
     */
    public function getNextGiftTime() {
        return $this->next_gift_time;
    }

    public function getCityName() {
        return $this->city_name;
    }
}