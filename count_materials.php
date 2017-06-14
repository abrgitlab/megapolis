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

define('HIERARCHICALLY', true);

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
                    if (HIERARCHICALLY && isset($item['produce']) && gettype($item['produce']) === 'string' && $item['produce'] !== $item_name) {
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

foreach ($materials_needed as $material => $quantity) {
    foreach(Bot::$game->room->barn_data->childNodes->item(0)->childNodes as $barn) {
        if ($barn->localName === $material) {
            $quantity_having = $barn->getAttribute('quantity');
            $materials_needed[$material] -= $quantity_having;
            break;
        }
    }
}

arsort($materials_needed);

$excludes = ['competition_asian_dragon_point'];

$materials_amount = 0;
$full_amount = 0;
foreach ($materials_needed as $material => $quantity) {
    $city_item = Bot::$game->city_items[$material];
    if ($quantity > 0 && !in_array($material, $excludes) && (!isset($city_item['pseudo_item']) || $city_item['pseudo_item'] == false)) {
        echo $city_item['description'] . ": $quantity\n";
        ++$materials_amount;
        $full_amount += $quantity;
    }
}

Bot::$game->changeRoom(1);

$excludes = ['mining_industry_materials_object_base', 'military_enginery_base'];

$materials_for_giving = [];
foreach(Bot::$game->room->barn_data->childNodes->item(0)->childNodes as $barn) {
    if ($barn->localName !== null) {
        $city_item = Bot::$game->city_items[$barn->localName];
        if ($city_item !== null && isset($city_item['shop_department']) && isset($city_item['super_class']) && ($city_item['shop_department'] !== 'materials' || in_array($city_item['super_class'], $excludes)))
            continue;
        if ($barn->hasAttribute('quantity')) {
            $barn_quantity = $barn->getAttribute('quantity');
            if (isset($materials_needed[$barn->localName])) {
                $quantity_left = $barn_quantity - $materials_needed[$barn->localName];
                if ($quantity_left > 0) {
                    $materials_for_giving[$barn->localName] = $quantity_left;
                }
            } else {
                $materials_for_giving[$barn->localName] = $barn_quantity;
            }
        }
    }
}

echo "Итого необходимо: материалов $materials_amount, общее количество $full_amount\n\n";

arsort($materials_for_giving);

$materials_amount = 0;
$full_amount = 0;
foreach ($materials_for_giving as $material => $quantity) {
    if ($quantity > 0) {
        $city_item = Bot::$game->city_items[$material];
        echo $city_item['description'] . ": $quantity\n";
        ++$materials_amount;
        $full_amount += $quantity;
    }
}
echo "Итого можно раздарить: материалов $materials_amount, общее количество $full_amount\n";
