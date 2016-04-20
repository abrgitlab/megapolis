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
    private $wish_list;

    /**
     * @var $send_requests mixed
     */
    private $send_requests;

    /**
     * @var $help_points int;
     */
    private $help_points = 0;

    /**
     * @var $requests mixed
     */
    private $requests;

    /**
     * @var $room_data DOMNode
     */
    private $room_data;

    /**
     * @param $xml_node DOMNode
     */
    public function loadFromXmlNode($xml_node) {
        $this->id = $xml_node->attributes->getNamedItem('id')->nodeValue;
        $wish_list = $xml_node->attributes->getNamedItem('wish_list');
        if ($wish_list)
            $this->wish_list = explode(',', $wish_list->nodeValue);

        $send_requests = $xml_node->attributes->getNamedItem('send_requests');
        if ($send_requests)
            $this->send_requests = json_decode($send_requests->nodeValue);

        $help_points = $xml_node->attributes->getNamedItem('help_points');
        if ($help_points)
            $this->help_points = $help_points->nodeValue;

        $requests = $xml_node->attributes->getNamedItem('requests');
        if ($requests)
            $this->requests = json_decode($requests->nodeValue);
    }

    public function visit() {
        $friend_rooms = ['0', '5', '2', '4', '1'];
        $last_room_id = $friend_rooms[0];

        foreach ($friend_rooms as $room_id) {
            if ($this->help_points > 0) {
                echo 'Заходим к другу с ID ' . $this->id . ' в комнату '. $room_id . '\n';
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

    public function getRequests() {
        return $this->requests;
    }

}