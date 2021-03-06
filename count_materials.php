<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 07.06.17
 * Time: 18:43
 */

require_once 'classes/Bot.php';
require_once 'classes/Game.php';

define('MEGAPOLIS_PATH', __DIR__);

define('PARSE_HIERARCHICALLY', true);
define('SHOW_OBJECTS_FOR_MATERIALS', false);

function checkMaterial($material) {
    $material_name_excludes = ['competition_asian_dragon_point'];
    $super_class_excludes = ['mining_industry_materials_object_base', 'military_enginery_base'];

    $city_item = Bot::$game->city_items[$material];
    if (
        $city_item !== null &&
        (
            !isset($city_item['pseudo_item']) ||
            $city_item['pseudo_item'] == false
        ) &&
        (
            !isset($city_item['room_staff_materials']) ||
            $city_item['room_staff_materials'] == false
        ) &&
        (
            !isset($city_item['shoppable']) ||
            $city_item['shoppable'] == true
        ) &&
        (
            !isset($city_item['shop_department']) ||
            $city_item['shop_department'] === 'materials'
        ) &&
        (
            !isset($city_item['exclude_from_need_materials']) ||
            $city_item['exclude_from_need_materials'] === false
        ) &&
        isset($city_item['super_class']) &&
        !in_array($city_item['super_class'], $super_class_excludes) &&
        !in_array($material, $material_name_excludes)
    )
        return true;

    return false;
}

$bot = new Bot();
Bot::$game = new Game(false);

$item_types_for_construct = [];
$items_for_construct_amount = 0;
for ($i = 0; $i <= 5; ++$i) {
    if ($i === 3)
        continue;

    Bot::$game->changeRoom($i);

    $room = Bot::$game->room;
    foreach(Bot::$game->room->field as $field_name => $field_items) {
        foreach ($field_items as $field) {
            if (isset(Bot::$game->city_items[$field_name])) {
                $item = Bot::$game->city_items[$field_name];
                $item_is_constructing = true;
                $item_id = $field['id'];
                $item_name = $field_name;
                do {
                    if (isset($item['materials_quantity']) && count($item['materials_quantity']) > 0) {
                        if (isset($item_types_for_construct[$item_name])) {
                            foreach ($item_types_for_construct[$item_name]['materials'] as $material => $quantity) {
                                $item_types_for_construct[$item_name]['materials'][$material] += $item['materials_quantity'][$material];
                            }
                        } else {
                            $item_types_for_construct[$item_name] = ['materials' => $item['materials_quantity'], 'constructing' => $item_is_constructing];
                            if ($item_is_constructing)
                                ++$items_for_construct_amount;
                        }

                        if ($item_id !== null) {
                            if (isset($field['input_fill'])) {
                                $input_fill = $field['input_fill'];
                                $input_fill = explode(',', $input_fill);
                                foreach ($input_fill as $input_fill_item) {
                                    $item_count = explode(':', $input_fill_item);
                                    $item_types_for_construct[$item_name]['materials'][Bot::$game->getCityItemById($item_count[0])['item_name']] -= $item_count[1];
                                }
                            }
                        }
                    }
                    if (PARSE_HIERARCHICALLY && isset($item['produce']) && gettype($item['produce']) === 'string' && $item['produce'] !== $item_name) {
                        $item_name = $item['produce'];
                        $item = Bot::$game->city_items[$item['produce']];
                        $item_is_constructing = false;
                    } else {
                        $item = null;
                    }

                    $item_id = null;
                } while ($item !== null);
            }
        }
    }
}

/*foreach (Bot::$game->city_items as $city_item_name => $city_item) {
    if (isset($city_item['super_class']) && $city_item['super_class'] == 'prototype_buildsite_base' && $city_item['id'] > 0 && isset($city_item['materials_quantity']) && count($city_item['materials_quantity']) > 0) {
        echo $city_item_name . "\n";
    }
}*/

Bot::$game->changeRoom(0);

ksort($item_types_for_construct);

$materials_needed = [];
foreach ($item_types_for_construct as $item_name => $item) {
    echo "$item_name\n";
    foreach ($item['materials'] as $material => $quantity) {
        $barn_quantity = 0;
        $city_item = Bot::$game->city_items[$material];
        if (checkMaterial($material)) {
            $barn_quantity = Bot::$game->room->getBarnQuantity($material);
            /*foreach(Bot::$game->room->barn as $barn) {
                if ($barn->localName !== null && $material == $barn->localName) {
                    if ($barn->hasAttribute('quantity')) {
                        $barn_quantity = $barn->getAttribute('quantity');
                        break;
                    }
                }
            }*/

            if (isset($materials_needed[$material])) {
                $materials_needed[$material]['quantity'] += $quantity;
                if ($item['constructing'] && $quantity > 0)
                    $materials_needed[$material]['need_now'] = $item['constructing'];
                if (!in_array($item_name, $materials_needed[$material]['objects']))
                    $materials_needed[$material]['objects'][] = ['object_name' => $item_name, 'quantity' => $quantity];
            } else {
                $materials_needed[$material] = ['quantity' => $quantity - $barn_quantity];
                $materials_needed[$material]['need_now'] = $item['constructing'] && $quantity > 0;
                $materials_needed[$material]['objects'] = [['object_name' => $item_name, 'quantity' => $quantity]];
            }
        }
    }
}

echo 'Итого необходимо построить объектов: ' . $items_for_construct_amount . ', шагов постройки: ' . count($item_types_for_construct) . "\n\n";

foreach(Bot::$game->room->barn as $barn_name => $barn) {
    if (!isset($materials_needed[$barn_name]) && checkMaterial($barn_name) && isset($barn['quantity'])) {
        $barn_quantity = $barn['quantity'];
        $materials_needed[$barn_name] = ['quantity' => -$barn_quantity, 'need_now' => false];
    }
}

$materials_for_giving = [];
$materials_for_taking = [];

foreach ($materials_needed as $material => $parameters) {
    if ($parameters['quantity'] > 0) {
        if (!isset($materials_for_taking[$parameters['quantity']]))
            $materials_for_taking[$parameters['quantity']] = [$material => Bot::$game->city_items[$material]['description']];
        else
            $materials_for_taking[$parameters['quantity']][$material] = Bot::$game->city_items[$material]['description'];
    } elseif ($parameters['quantity'] < 0) {
        if (!isset($materials_for_giving[-$parameters['quantity']]))
            $materials_for_giving[-$parameters['quantity']] = [$material => Bot::$game->city_items[$material]['description']];
        else
            $materials_for_giving[-$parameters['quantity']][$material] = Bot::$game->city_items[$material]['description'];
    }
}

//arsort($materials_sort);
krsort($materials_for_taking);
krsort($materials_for_giving);

$materials_amount = 0;
$full_amount = 0;
foreach ($materials_for_taking as $quantity => $materials) {
    asort($materials);
    foreach ($materials as $material => $description) {
        $parameters = $materials_needed[$material];
        echo (($parameters['need_now']) ? '+' : '-') . $description . ': ' . $quantity;
        if (SHOW_OBJECTS_FOR_MATERIALS) {
            $objects = [];
            foreach ($parameters['objects'] as $object) {
                if ($object['quantity'] > 0)
                    $objects[] = $object['object_name'] . ' => ' . $object['quantity'];
            }
            echo ' (' . implode(', ', $objects) . ')';
        }
        echo "\n";
        ++$materials_amount;
        $full_amount += $quantity;
    }
}

echo "Итого необходимо: материалов $materials_amount, общее количество $full_amount\n\n";

//asort($materials_sort);

$materials_amount = 0;
$full_amount = 0;
foreach ($materials_for_giving as $quantity => $materials) {
    asort($materials);
    foreach ($materials as $material => $description) {
        echo $description . ': ' . $quantity . "\n";
        ++$materials_amount;
        $full_amount += $quantity;
    }
}

echo "Итого можно раздарить: материалов $materials_amount, общее количество $full_amount\n";
