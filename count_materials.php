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

$items_for_construct = [];
for ($i = 0; $i <= 5; ++$i) {
    if ($i === 3)
        continue;

    Bot::$game->changeRoom($i);

    $room = Bot::$game->room;
    foreach(Bot::$game->room->field_data->childNodes->item(0)->childNodes as $field) {
        if ($field->localName !== null) {
            if (isset(Bot::$game->city_items[$field->localName])) {
                $item = Bot::$game->city_items[$field->localName];
                $item_id = $field->attributes->getNamedItem('id')->nodeValue;
                $item_name = $field->localName;
                do {
                    if (isset($item['materials_quantity']) && count($item['materials_quantity']) > 0) {
                        if (isset($items_for_construct[$item_name])) {
                            foreach ($items_for_construct[$item_name] as $material => $quantity) {
                                $items_for_construct[$item_name][$material] += $item['materials_quantity'][$material];
                            }
                        } else {
                            $items_for_construct[$item_name] = $item['materials_quantity'];
                        }

                        if ($item_id !== null) {
                            if ($field->hasAttribute('input_fill')) {
                                $input_fill = $field->getAttribute('input_fill');
                                $input_fill = explode(',', $input_fill);
                                foreach ($input_fill as $input_fill_item) {
                                    $item_count = explode(':', $input_fill_item);
                                    $items_for_construct[$item_name][Bot::$game->getCityItemById($item_count[0])['item_name']] -= $item_count[1];
                                }
                            }
                        }
                    }
                    if (isset($item['produce']) && gettype($item['produce']) === 'string' && $item['produce'] !== $item_name) {
                        $item_name = $item['produce'];
                        $item = Bot::$game->city_items[$item['produce']];
                    } else {
                        $item = null;
                    }

                    $item_id = null;
                } while ($item !== null);
            }
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

arsort($materials_needed);

$excludes = ['competition_asian_dragon_point'];

$materials_amount = 0;
$full_amount = 0;
foreach ($materials_needed as $material => $quantity) {
    if ($quantity > 0 && !in_array($material, $excludes)) {
        echo "$material: $quantity\n";
        ++$materials_amount;
        $full_amount += $quantity;
    }
}

echo "Итого: материалов $materials_amount, общее количество $full_amount\n";
