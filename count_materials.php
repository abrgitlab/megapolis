<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 07.06.17
 * Time: 18:43
 */

require_once 'classes/Bot.php';
require_once 'classes/Game.php';
require_once 'classes/Room.php';

define('BASE_PATH', __DIR__);

$bot = new Bot();
Bot::$game = new Game(false);

for ($i = 0; $i <= 5; ++$i) {
    if ($i === 3)
        continue;

    Bot::$game->changeRoom($i);

    $items_for_construct = [];

    $room = Bot::$game->room;
    foreach(Bot::$game->room->field_data->childNodes->item(0)->childNodes as $field) {
        if ($field->localName !== null) {
            if (isset(Bot::$game->city_items[$field->localName])) {
                $item = Bot::$game->city_items[$field->localName];
                $item_name = $field->localName;
                do {
                    echo $item_name;
                    if (isset($item['materials_quantity'])) {
                        $items_for_construct[$item_name] = $item['materials_quantity'];
                    }
                    if (isset($item['produce']) && gettype($item['produce']) === 'string' && $item['produce'] !== $item_name) {
                        $item_name = $item['produce'];
                        $item = Bot::$game->city_items[$item['produce']];
                        echo ' -> ';
                    } else {
                        $item = null;
                        echo "\n";
                    }
                } while ($item !== null);
            }
        }
    }

    $materials_needed = [];
    foreach ($items_for_construct as $consumes) {
        foreach ($consumes as $material => $quantity) {
            if (isset($materials_needed[$material]))
                $materials_needed[$material] += $quantity;
            else
                $materials_needed[$material] = $quantity;
        }
    }

    var_dump($materials_needed);
}