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
     * @var $barn_data mixed
     */
    private $barn_data;

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

//        $location_data = Bot::getGame()->requestToServer('room_stat', ['first_request' => $first_request, 'room_id' => $this->room_id]);
        $location_data = Bot::getGame()->getRoomStat($this->room_id, $first_request);
        $location_data = Bot::$tidy->repairString($location_data, Bot::$tidy_config);

        $this->location_data = new DOMDocument();
        $this->location_data->loadXML($location_data);

        $this->loadBarnData();
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