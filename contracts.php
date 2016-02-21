<?php
/**
 * Created by notepad.exe.
 * User: daemon
 * Date: 04.10.15
 * Time: 1:21
 */

$options = getopt('', ['long']);

$host = 'web146.socialquantum.com';
$host_static = 'mb.static.socialquantum.ru';
$build = '10623';
$client_version = '2.70.' . $build;
$iauth = '277997eba7f4e51051b0a0a9450afe73';
$user_id = 'UD_5cd98e974c0fec35013c4790';

$tidy = new Tidy();
$tidy_config = array(
    'indent' => true,
    'clean' => true,
    'input-xml' => true,
    'output-xml' => true,
    'wrap' => false
);

$long = isset($options['long']);

echo "Проверка наличия обновлений\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host_static/mobile_assets/revision.xml?rand=" . time());
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, false);
$revision_data = gzdecode(curl_exec($ch));
curl_close($ch);

$revision_data = $tidy->repairString($revision_data, $tidy_config);

$revision_data_xml = new DOMDocument();
$revision_data_xml->loadXML($revision_data);

$revision = $revision_data_xml->getElementsByTagName('revision_info')->item(0)->attributes->getNamedItem('revision')->nodeValue;
if (!file_exists(dirname(__FILE__) . "/city_items.yml.$revision")) {
    echo "Получение обновлений\n";
    $city_items_yaml = file_get_contents("http://mb.static.socialquantum.ru/mobile_assets/city_items.yml?rev=$revision");
    file_put_contents(dirname(__FILE__) . "/city_items.yml.$revision", $city_items_yaml);
    $city_items = yaml_parse($city_items_yaml);
} else {
    $city_items = yaml_parse(file_get_contents(dirname(__FILE__) . "/city_items.yml.$revision"));
}

$city_items_by_id = [];
foreach ($city_items as $item_name => $city_item) {
    if (isset($city_item['id']) && $city_item['id'] > 0)
        $city_items_by_id[$city_item['id']] = $item_name;
}

$rn = 0;

$material_list = array('poker_trophy', 'golden_dice', 'bracelet_winner', 'gold_medal', 'gambler_cup', 'bar_of_gold');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&no_field=true&social_id[SQ]=abr_mail%40mail.ru&device_id=45cca1a3-973a-3f7f-b226-da8d8301cfb6&platform=android&build=$build&app=city&device_model=Genymotion%20vbox86p&os=4.1.1&gloc=ru&dloc=ru&net=wf&social=sqsocial%3Aprod&odin_id=949c34f735162b0bd21f1f63db51cc2bb9e935ac&android_id=f337e0e35a1e6dd5&mac=0800270cc3c5&advertising_id=e4959f11-12a8-4cb1-a5d3-0c3649406e3b");
$my_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

$my_data = $tidy->repairString($my_data, $tidy_config);

$my_data_xml = new DOMDocument();
$my_data_xml->loadXML($my_data);

$cmd_id = $my_data_xml->getElementsByTagName('country')->item(0)->attributes->getNamedItem('server_cmd_id')->nodeValue;
++$cmd_id;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&revision=android-$client_version&access_token=$iauth&lang=ru&client_type=android&room_id=0&odin_id=949c34f735162b0bd21f1f63db51cc2bb9e935ac&android_id=f337e0e35a1e6dd5&mac=0800270cc3c5&advertising_id=e4959f11-12a8-4cb1-a5d3-0c3649406e3b&device_id=45cca1a3-973a-3f7f-b226-da8d8301cfb6&first_request=true&location=&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

$location_data = $tidy->repairString($location_data, $tidy_config);

$location_data_xml = new DOMDocument();
$location_data_xml->loadXML($location_data);

$friends = [];
$friends_for_invite_in_gambling_zone = [];
$friends_for_send_to_gambling_zone = [];

$received_gifts = [];

foreach($location_data_xml->getElementsByTagName('friends')->item(0)->childNodes as $friend) {
    if ($friend->localName == 'friend') {
        $friend_id = $friend->attributes->getNamedItem('id')->nodeValue;

        if ($friend_id == 0) {
            $friends[] = $friend_id;
            $wish_list = $friend->attributes->getNamedItem('wish_list');
            $send_requests = $friend->attributes->getNamedItem('send_requests');
            if ($wish_list != NULL)
                $wish_list = $wish_list->nodeValue;
            if ($send_requests != NULL) {
                $send_requests = json_decode($send_requests->nodeValue);
                if (isset($send_requests->gambling_zone_staff->user) && count($send_requests->gambling_zone_staff->user) > 0 && $send_requests->gambling_zone_staff->user[0] == $friend_id) {
                    $friends_for_invite_in_gambling_zone[] = $friend_id;
                }
            }
            //TODO: анализ списка желаний друзей и раздаривание материалов
        }

        if ($friend_id != '-41' && $friend_id != '-43') {
            $friend_rooms = ['0', '5', '2', '4', '1'];
            $last_friend_room_id = $friend_rooms[0];

            $works = $friend->attributes->getNamedItem('help_points')->nodeValue;
            foreach ($friend_rooms as $friend_room) {
                if ($works > 0) {
                    echo "Заходим к другу с ID $friend_id в комнату $friend_room\n";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&view_friend_id=$friend_id&room_id=$last_friend_room_id&change_room=1&view_room_id=$friend_room&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
                    $friend_data = gzdecode(curl_exec($ch));
                    curl_close($ch);
                    ++$rn;

                    $last_friend_room_id = $friend_room;

                    $friend_data = $tidy->repairString($friend_data, $tidy_config);

                    $friend_data_xml = new DOMDocument();
                    $friend_data_xml->loadXML($friend_data);

                    $ids = [];
                    foreach ($friend_data_xml->getElementsByTagName('field')->item(0)->childNodes as $building) {
                        if (count($ids) >= $works)
                            break;

                        if ($building->localName != NULL) {
                            if (substr($building->localName, strlen($building->localName) - 4, 4) == '_new') {
                                $ids[] = ['name' => $building->localName, 'id' => $building->attributes->getNamedItem('id')->nodeValue];
                            }
                        }
                    }

                    $cached_string = '';
                    $cached_id = 0;
                    foreach ($ids as $id) {
                        $cached_part = "&cached[$cached_id][command]=help&cached[$cached_id][cmd_id]=$cmd_id&cached[$cached_id][room_id]=0&cached[$cached_id][owner_id]=" . $friend->attributes->getNamedItem('id')->nodeValue . "&cached[$cached_id][item_id]=" . $id['id'] . "&cached[$cached_id][friend_id]=" . $friend->attributes->getNamedItem('id')->nodeValue . "&cached[$cached_id][klass]=" . $id['name'] . "&cached[$cached_id][uxtime]=" . time();
                        ++$cmd_id;
                        ++$cached_id;

                        $cached_string .= $cached_part;
                    }

                    $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=0&owner_id=" . $friend->attributes->getNamedItem('id')->nodeValue . "&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
                    curl_exec($ch);
                    curl_close($ch);
                    ++$rn;

                    $works -= count($ids);
                }
            }
        }

        $friend_requests = $friend->attributes->getNamedItem('requests');
        if ($friend_requests != NULL) {
            $friend_requests = json_decode($friend_requests->nodeValue);
            if (isset($friend_requests->send_gift_new)) {
                foreach ($friend_requests->send_gift_new->st_items as $gift_id) {
                    if (isset($city_items_by_id[$gift_id]))
                        $received_gifts[] = array('name' => 'send_gift_new', 'friend_id' => $friend_id, 'st_item' => $gift_id, 'gift_name' => $city_items_by_id[$gift_id]);
                    else
                        echo "Неизвестный материал, id $gift_id\n";
                }
            }

            if (isset($friend_requests->ask_material_common)) {
                //TODO: понять, как отказывать друзьям
            }

            if (isset($friend_requests->gambling_zone_staff_back->user) && count($friend_requests->gambling_zone_staff_back->user) == 0) {
                $cached_string = "&cached[0][command]=commit_request&cached[0][cmd_id]=$cmd_id&cached[0][room_id]=0&cached[0][name]=gambling_zone_staff_back&cached[0][friend_id]=$friend_id&cached[0][item_id]=0&cached[0][uxtime]=" . time();
                ++$cmd_id;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=0&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn");
                curl_exec($ch);
                curl_close($ch);
                ++$rn;
            }

//            if ($friend_id == '-42' && isset($friend_requests->stock_exchange_request)) {
//                echo "Отмена запроса на посещение биржи\n";
//
//                $cached_string = "&cached[0][command]=discard_request&cached[0][cmd_id]=$cmd_id&cached[0][room_id]=0&cached[0][name]=stock_exchange_request&cached[0][friend_id]=$friend_id&cached[0][uxtime]=" . time();
//                ++$cmd_id;
//
//                $ch = curl_init();
//                curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
//                curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//                curl_setopt($ch, CURLOPT_POST, true);
//                curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=0&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn");
//                curl_exec($ch);
//                curl_close($ch);
//                ++$rn;
//            }
        }
    }
}

if (count($received_gifts) > 0) {
    $cached_string = "&cached[0][command]=mass_commit_request&cached[0][cmd_id]=$cmd_id&cached[0][room_id]=0&cached[0][data]=" . urlencode(json_encode($received_gifts)) . "&cached[0][uxtime]=" . time();
    ++$cmd_id;
    $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=0&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
    curl_exec($ch);
    curl_close($ch);
    ++$rn;
    echo 'Принято подарков: ' . count($received_gifts) . "\n";
}

signContract($location_data, 0);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=0&change_room=1&view_room_id=5&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

signContract($location_data, 5);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=5&change_room=1&view_room_id=2&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

signContract($location_data, 2);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=2&change_room=1&view_room_id=4&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

signContract($location_data, 4);
casinoPickFriend($location_data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=4&change_room=1&view_room_id=1&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

signContract($location_data, 1);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/get_user_stat");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=1&change_room=1&view_room_id=0&serv_ver=1&lang=ru&rand=0." . rand(0, 9999999) . "&client_type=android&rn=$rn");
$location_data = gzdecode(curl_exec($ch));
curl_close($ch);
++$rn;

echo 'Выполнено в ' . date('H:i:s' . "\n");

function signContract($location_data, $room_id) {
    global $tidy, $tidy_config, $host, $client_version, $iauth, $user_id, $rn, $cmd_id, $friends, $long;

    echo "Работа с контрактами в комнате $room_id\n";

    $location_data = $tidy->repairString($location_data, $tidy_config);

    $location_data_xml = new DOMDocument();
    $location_data_xml->loadXML($location_data);

    $field_data_xml = new DOMDocument();
    $field_data_xml->loadXML($location_data_xml->saveXML($location_data_xml->getElementsByTagName('field')->item(0)));

    $buildings_list = [];
    if ($room_id == 0)
        $buildings_list = array(
            //НИЯУ
            'srnu_factory' => array(
                'short' => array(
                    'contract' => 'radioisotopes_preparations'
                ),
                'long' => array(
                    'contract' => 'atomic_nuclei_transformation'
                ),
                'actions' => array('pick', 'put')
            ),

            //Институт истории
            'institute_of_history_stage1' => array(
                 'short' => array(
                     'contract' => 'stratigraphic_studies'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Дворец здоровья
            'health_palace_stage2' => array(
                 'short' => array(
                     'contract' => 'rehabilitation_of_athletes'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Городской центр спорта
            'urban_sports_center_stage2' => array(
                 'short' => array(
                     'contract' => 'tournament_on_mini_football'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Региональный центр сотовой связи
            'mobile_phone_system_center_stage4' => array(
                 'short' => array(
                     'contract' => 'ggg_connection'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Лесопырка
            'sawmill_middle' => array(
                 'short' => array(
                     'contract' => 'industrial_wood'
                 ),
                'long' => array(
                    'contract' => 'case_furniture'
                ),
                'actions' => array('pick', 'put')
            ),

            //Крейсер
            'naval_station_stage8' => array(
                 'short' => array(
                     'contract' => 'marine_corps_training'
                 ),
                'long' => array(
                    'contract' => 'patrol_maritime_borders'
                ),
                'actions' => array('pick', 'put')
            ),

            //Центр управления пароходными линиями
            'central_port_stage4' => array(
                 'short' => array(
                     'contract' => 'industrial_fishing'
                 ),
                'actions' => array('pick', 'put')
            ),

            //SQ-Сити
            'sq_city_stage2' => array(
                 'short' => array(
                     'contract' => 'government_proceedings'
                 ),
                'long' => array(
                    'contract' => 'trading'
                ),
                'actions' => array('pick', 'put')
            ),

            //Школа актёрского мастерства
            'acting_school_stage1' => array(
                 'short' => array(
                     'contract' => 'lesson_of_acting_skills'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Поместье Маунт-Вернон
            'mount_vernon_stage1' => array(
                 'short' => array(
                     'contract' => 'organization_of_the_state_reception'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Уайтхолльский дворец
            'uaythollsky_palace_stage2' => array(
                 'short' => array(
                     'contract' => 'salute_of_guns_combat'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Национальный центр кинематографии
            'national_center_cinema' => array(
                 'short' => array(
                     'contract' => 'awarding_of_prizes'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Ассоциация тенниса
            'lawn_tennis_association_stage1' => array(
                 'short' => array(
                     'contract' => 'exhibition_history_tennis'
                 ),
                'long' => array(
                    'contract' => 'welcome_participants_tournament', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Готический замок
            'gothic_castle_stage3' => array(
                 'short' => array(
                     'contract' => 'parade_monsters'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Виноградная ферма
            'grape_farm_stage3' => array(
                 'short' => array(
                     'contract' => 'festival_fr_cuisine'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Поло-клуб
            'polo_club_stage1' => array(
                 'short' => array(
                     'contract' => 'participants_championship', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Експоцентр
            'fairgrounds_stage2' => array(
                 'short' => array(
                     'contract' => 'tune_sportscar'
                 ),
                'long' => array(
                    'contract' => 'presentation_supercar'
                ),
                'actions' => array('pick', 'put')
            ),

            //Институт военных разработок
            'military_research_institute_stage1' => array(
                 'short' => array(
                     'contract' => 'introduction_of_protective_artificial_intelligence'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Геодезическая компания
            'geodesic_company_stage1' => array(
                 'short' => array(
                     'contract' => 'mineralic_analysis_territory'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Золотодобывающая корпорация
            'gold_mining_company_stage2' => array(
                 'short' => array(
                     'contract' => 'investigation_methods_enriching'
                 ),
                'long' => array(
                    'contract' => 'enrichment_gold_ore'
                ),
                'actions' => array('pick', 'put')
            ),

            //Институт исследований космоса
            'space_research_institute_stage1' => array(
                 'short' => array(
                     'contract' => 'develop_interstellar_dating', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Центральная панорама
            'statue_burning_man_stage2' => array(
                 'short' => array(
                     'contract' => 'performance_viewpoint'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Карнакский храм
            'karnak_temple_stage2' => array(
                 'short' => array(
                     'contract' => 'discovery_of_cave_painting'
                 ),
                'long' => array(
                    'contract' => 'study_of_ancient_writing'
                ),
                'actions' => array('pick', 'put')
            ),

            //Пиратский форт
            'pirate_fort_stage1' => array(
                 'short' => array(
                     'contract' => 'themed_tours'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Администрация зоопарка
            'administration_zoo_final' => array(
                 'short' => array(
                     'contract' => 'exhibition_of_birds', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Ассоциация рок-н-ролла
            'association_rock_and_roll' => array(
                 'short' => array(
                     'contract' => 'festival_of_young_rock_musicians', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Центр военно-исторической реконструкции
            'club_military_reconstruction_stage2' => array(
                 'short' => array(
                     'contract' => 'making_knightly_accoutrements'
                 ),
                'long' => array(
                    'contract' => 'joust', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Центр "Чёрная пантера"
            'center_black_panther_stage1' => array(
                 'short' => array(
                     'contract' => 'seminar_on_fashion_and_art', 'friends_request' => true
                 ),
                'long' => array(
                    'contract' => 'exhibition_expensive_clothes', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Фестивальный замок
            'colomares_castle_stage3' => array(
                 'short' => array(
                     'contract' => 'preparatory_work'
                 ),
                'long' => array(
                    'contract' => 'capture_a_fort', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Архитектурная академия
            'architectural_academy_stage1' => array(
                 'short' => array(
                     'contract' => 'architectural_projects_ecology_estimation'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Новогодний Таймс-сквер
            'times_square_stage2' => array(
                 'short' => array(
                     'contract' => 'show_retro_films'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Агентство праздников
            'holidays_agency_stage1' => array(
                 'short' => array(
                     'contract' => 'order_copyright_postcards'
                 ),
                'long' => array(
                    'contract' => 'holidays_party', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Научно-информационный центр "Фьюжн"
            'scientific_information_center_fyuzion_stage1' => array(
                 'short' => array(
                     'contract' => 'development_of_new_safety_systems', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Университет Мегаполиса
            'megapolis_university_training' => array(
                 'short' => array(
                     'contract' => 'term_paper_writing'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Театр "Лебедь"
            'swan_theater_stage1' => array(
                 'short' => array(
                     'contract' => 'theatrical_makeup_master_class'
                 ),
                'long' => array(
                    'contract' => 'shakespeare_costume_parade', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Администрация отельного комплекса "Шимао"
            'administration_hotel_complex_shima_stage1' => array(
                 'short' => array(
                     'contract' => 'inauguration_hotel_shima'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Расширение территории института
            'citys_ecology_institute_stage2' => array(
                 'short' => array(
                     'contract' => 'earth_hour'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Истребитель
            'airbase_stage6' => array(
                 'short' => array(
                     'contract' => 'pilots_training'
                 ),
                'long' => array(
                    'contract' => 'armor_transport'
                ),
                'actions' => array('pick', 'put')
            ),

            //Дрейфующая станция
            'drifting_station_stage2' => array(
                 'short' => array(
                     'contract' => 'grand_opening_loona_park'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Институт експериментальной медицины
            'medical_centre_stage3' => array(
                 'short' => array(
                     'contract' => 'medical_conference'
                 ),
                'long' => array(
                    'contract' => 'innovation_heal_methods_developing', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Реставрационный комплекс
            'restoration_complex_stage3' => array(
                 'short' => array(
                     'contract' => 'sampling_gold'
                 ),
                'long' => array(
                    'contract' => 'calculation_coordinates_city_of_gold'
                ),
                'actions' => array('pick', 'put')
            ),

            //Клуб пилотов
            'club_pilots_stage3' => array(
                 'short' => array(
                     'contract' => 'championship_airslalom'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Центр парусного спорта
            'sailing_center_stage3' => array(
                 'short' => array(
                     'contract' => 'windsurfing', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Круизная компания
            'cruise_company_stage2' => array(
                 'short' => array(
                     'contract' => 'competition_kissing_mistletoe'
                 ),
                'long' => array(
                    'contract' => 'solemn_liner_departure', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Институт демографии
            'institute_of_demography' => array(
                 'short' => array(
                     'contract' => 'family_day_at_restaurant'
                 ),
                'long' => array(
                    'contract' => 'fitness_marathon', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Оттавский комплекс для конференций
            'ottawa_convention_centre_stage1' => array(
                 'short' => array(
                     'contract' => 'onference_of_information_technology', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Выставочный зал "Ноев Ковчег"
            'noahs_ark_stage2' => array(
                 'short' => array(
                     'contract' => 'exhibition_life_on_earth'
                 ),
                'long' => array(
                    'contract' => 'installation_discworld'
                ),
                'actions' => array('pick', 'put')
            ),

            //Центральный вокзал
            'central_station' => array(
                 'short' => array(
                     'contract' => 'international_passenger_traffic', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Единая служба спасения
            'help_centre_final' => array(
                 'short' => array(
                     'contract' => 'open_help_centers'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Пирамида
            'pyramid' => array(
                 'short' => array(
                     'contract' => 'archaeological_excavations'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Площадь святого Марка
            'rains_palace_stage2' => array(
                 'short' => array(
                     'contract' => 'carnival_night_organization', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Криогенная установка
            'cryogenic_plant_stage2' => array(
                 'short' => array(
                     'contract' => 'christmas_flashmob', 'friends_request' => true
                 ),
                'long' => array(
                    'contract' => 'contest_ice_sculptures', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Центр изучения окружающей среды
            'center_environmental_studies_stage3' => array(
                 'short' => array(
                     'contract' => 'international_summit_green_energy'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Плавучий екополис
            'congress_center_hangzhou_stage2' => array(
                 'short' => array(
                     'contract' => 'introduction_graphene_capacitors'
                 ),
                'long' => array(
                    'contract' => 'hydrowave_ocean_clean', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Финансовый центр "Прайм"
            'financial_center_prime' => array(
                'short' => array(
                    'contract' => 'exchange_trading'
                ),
                'actions' => array('pick', 'put')
            ),

            //Хелипорт
            'heliport_stage2' => array(
                'short' => array(
                    'contract' => 'passenger_flights'
                ),
                'actions' => array('pick', 'put')
            ),

            //Каркас валютного фонда Мегаполиса
            'monetary_fund_megapolis_stage1' => array(
                'short' => array(
                    'contract' => 'manufacturing_gift_set_coins'
                ),
                'actions' => array('pick', 'put')
            ),

            //Информационно-аналитический центр
            'information_analysis_center_stage1' => array(
                'short' => array(
                    'contract' => 'logging_information'
                ),
                'long' => array(
                    'contract' => 'analysis_of_information_security'
                ),
                'actions' => array('pick', 'put')
            ),
        );
    elseif ($room_id == 1)
        $buildings_list = array(
            //НИИ геологии
            'geological_institute_stage2' => array(
                 'short' => array(
                     'contract' => 'research_of_solid'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Транспортный вокзал "Лазурная река"
            'busstation_azure_river_up1' => array(
                 'short' => array(
                     'contract' => 'long_distance_bus'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Вибрационный грохот
            'clinker_technic2' => array(
                'actions' => array('pick')
            ),

            //Роторная дробилка
            'crushing_plant2' => array(
                'actions' => array('pick')
            ),

            //Полноповортный екскаватор
            'coal_mine_medium_excavator' => array(
                'actions' => array('pick')
            ),

            //Камнекольный станок
            'natural_stone_mine_technic2' => array(
                'actions' => array('pick')
            ),

            //Угольный комплекс
            'coal_industry' => array(
                'actions' => array('pick')
            ),

            //Горно-обогатительный комбинат
            'mining_processing_plant_stage2' => array(
                'actions' => array('pick')
            ),

            //Завод сыпучих материалов
            'cement_plant_final' => array(
                'actions' => array('pick')
            ),

            //Камнедробильный комплекс
            'stone_crushing_plant_mining_up1' => array(
                'actions' => array('pick')
            ),

            //Прокатно-калибровочный цех
            'rolling_mill_stage3' => array(
                'actions' => array('pick')
            ),

            //Строительно-промышленный комплекс
            'construction_and_industrial_complex_final' => array(
                'actions' => array('pick')
            ),

            //Комплекс железобетонных изделий
            'complex_of_concrete_products_stage2' => array(
                'actions' => array('pick')
            ),

            //Металлургический комбинат
            'iron_and_steel_works' => array(
                'actions' => array('pick')
            )
        );
    elseif ($room_id == 2)
        $buildings_list = array(
            //Аеропорт
            'island_airport_stage2' => array(
                 'short' => array(
                     'contract' => 'local_flights'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Старинный форт
            'ancient_fort_stage2' => array(
                'actions' => array('pick')
            ),

            //Отель на воде
            'hotel_on_water_stage2' => array(
                 'short' => array(
                     'contract' => 'press_conference', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            )
        );
    /*elseif ($room_id == 3)
        $contract_list = array(
            '' => array('contract' => '', 'actions' => array('')),
        );*/
    elseif ($room_id == 4)
        $buildings_list = array(
            //Турбовинтовой самолёт
            'airport_gamble_plane2_buildsite' => array(
                 'short' => array(
                     'contract' => 'organization_of_jumps'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Реактивный самолёт
//            'airport_gamble_plane1_stage1' => array(
//                 'short' => array(
//                     'contract' => 'organization_of_jumps'
//                 ),
////                'long' => array(
////                    'contract' => '', 'friends_request' => true
////                ),
//                'actions' => array('pick', 'put')
//            ),

            //Турбореактивный самолёт
            'airport_gamble_plane1_stage2' => array(
                'short' => array(
                    'contract' => 'organization_of_jumps'
                ),
                'long' => array(
                     'contract' => 'long_distance_flights', 'friends_request' => true
                 ),
                'actions' => array('pick', 'put')
            ),

            //Паромная станция
            'gambling_ferry_station_stage1' => array(
                 'short' => array(
                     'contract' => 'crossing_cable_car'
                 ),
                'long' => array(
                    'contract' => 'master_class_paragliding', 'friends_request' => true
                ),
                'actions' => array('pick', 'put')
            ),

            //Казино "Люминус Холл"
            'casino_lyuminus_hall_stage2' => array(
                 'short' => array(
                     'contract' => 'marathon_running_lights'
                 ),
                'long' => array(
                    'contract' => 'christmas_laser_show'
                ),
                'actions' => array('pick', 'put')
            )
        );
    elseif ($room_id == 5)
        $buildings_list = array(
            //НИЯУ
            'srnu_factory' => array(
                 'short' => array(
                     'contract' => 'radioisotopes_preparations'
                 ),
                'long' => array(
                    'contract' => 'atomic_nuclei_transformation'
                ),
                'actions' => array('pick', 'put')
            ),

            //Церемониальная площадь
            'victory_stadium_stage2' => array(
                 'short' => array(
                     'contract' => 'preparation_of_stadiums_for_winter_games'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Медиа-центр
            'media_center_snow_stage1' => array(
                 'short' => array(
                     'contract' => 'meeting_competitors'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Горный отель
            'mountain_hotel_stage1' => array(
                 'short' => array(
                     'contract' => 'snowboarding'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Архитектурный исследовательский комплекс
            'macau_centre_hotel' => array(
                 'short' => array(
                     'contract' => 'deciphering_secret_blueprints'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Горный хелипорт
            'mountain_heliport_stage1' => array(
                 'short' => array(
                     'contract' => 'helicopter_tour_of_rockies'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Сервисный центр
            'area_aerostats_stage2' => array(
                 'short' => array(
                     'contract' => 'inflating_balloons'
                 ),
                'actions' => array('pick', 'put')
            ),

            //Шоу-площадка "Фантазия"
            'show_fantasy_park_stage2' => array(
                 'short' => array(
                     'contract' => 'pre_christmas_outing'
                 ),
                'long' => array(
                    'contract' => 'festive_hologram_presentation'
                ),
                'actions' => array('pick', 'put')
            )
        );

    $cached_array = [];
    $cached_id = 0;

    foreach($field_data_xml->childNodes->item(0)->childNodes as $field) {
        if (isset($buildings_list[$field->localName])) {
            $building_data = $buildings_list[$field->localName];
            if (in_array('pick', $building_data['actions'])) {
                $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                if ($field_state == 4) {
                    $cached_part = array('command' => 'pick', 'cmd_id' => $cmd_id, 'room_id' => $room_id, 'item_id' => $field_id);
                    ++$cmd_id;

                    $cached_array[] = $cached_part;
                }
            }
        }
    }

    foreach($field_data_xml->childNodes->item(0)->childNodes as $field) {
        if (isset($buildings_list[$field->localName])) {
            $building_data = $buildings_list[$field->localName];
            if (in_array('put', $building_data['actions'])) {
                $field_id = $field->attributes->getNamedItem('id')->nodeValue;
                $field_state = $field->attributes->getNamedItem('state')->nodeValue;

                if ($field_state == 2 || $field_state == 4) {
                    if ($long && isset($building_data['long']))
                        $contract_data = $building_data['long'];
                    elseif (isset($building_data['short']))
                        $contract_data = $building_data['short'];
                    else
                        $contract_data = null;

                    if ($contract_data) {
                        $cached_part = array('command' => 'put', 'cmd_id' => $cmd_id, 'room_id' => $room_id, 'item_id' => $field_id, 'klass' => $contract_data['contract']);
                        ++$cmd_id;

                        if ($field_id == '29165634' && $contract_data['contract'] == 'ggg_connection') //Хак для мобильного центра
                            $cached_part['affected_items'] = '31861965%3A35861946%2C39734593%2C39856375%2C28557984%2C34053392%2C29488690%2C35767257%2C28239235%2C28557634%2C34152209%2C30516461%2C29505614%2C28199082%2C33759002%2C35958760%2C35649209%2C32833679%2C28558178%2C35862765%2C28198974%2C40198010%2C30079491%2C30516462%2C28239236%2C35727249%2C35019780%3B31811512%3A35958674%2C48176662%2C34176308%2C34343912%2C36380765%2C35851055%2C34977101%2C35920361%2C35741945%2C36249841%2C29007227%2C35958568%2C29299986%2C35958662%2C28558161%2C34879378%3B40966797%3A%3B40966833%3A41652382%2C39715714%2C41924920%2C41689198%2C41778317%2C41652564%3B';

                        $cached_array[] = $cached_part;

                        if (isset($contract_data['friends_request']) && $contract_data['friends_request']) {
                            $cached_part = array('command' => 'send_request', 'cmd_id' => $cmd_id, 'room_id' => $room_id, 'name' => 'visit_' . $contract_data['contract'], 'friend_ids' => implode('%2C', $friends), 'item_id' => $field_id);
                            ++$cmd_id;

                            $cached_array[] = $cached_part;
                        }
                    }
                }
            }
        }
    }

    if (count($cached_array) > 0) {
        for ($i = count($cached_array); $i > 0; --$i) {
            echo "Ждём забора и подписания контрактов $i сек.\n";
            sleep(1);
        }

        $field_time = time() - count($cached_array);
        $cached_string = '';
        foreach ($cached_array as $cached_part) {
            $cached_part['uxtime'] = ++$field_time;
            foreach ($cached_part as $key => $value) {
                $cached_string .= "&cached[$cached_id][$key]=$value";
            }
            ++$cached_id;
        }

        $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=$room_id&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
        curl_exec($ch);
        curl_close($ch);
        ++$rn;
    }

    $cached_array = [];
    $cached_id = 0;

    foreach($field_data_xml->childNodes->item(0)->childNodes as $field) {
        if ($field->attributes !== NULL) {
            $field_id = $field->attributes->getNamedItem('id')->nodeValue;
            $field_state = $field->attributes->getNamedItem('state')->nodeValue;

            if ($field_state == 5) {
                $cached_part = array('command' => 'clean', 'cmd_id' => $cmd_id, 'room_id' => $room_id, 'item_id' => $field_id);
                ++$cmd_id;

                $cached_array[] = $cached_part;
            }
        }
    }

    if (count($cached_array) > 0) {
        for ($i = count($cached_array); $i > 0; --$i) {
            echo "Ждём забора монеток $i сек.\n";
            sleep(1);
        }

        $field_time = time() - count($cached_array);
        $cached_string = '';
        foreach ($cached_array as $cached_part) {
            $cached_part['uxtime'] = ++$field_time;
            foreach ($cached_part as $key => $value) {
                $cached_string .= "&cached[$cached_id][$key]=$value";
            }
            ++$cached_id;
        }

        $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=$room_id&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
        curl_exec($ch);
        curl_close($ch);
        ++$rn;
    }
}

function casinoPickFriend($location_data) {
    global $tidy, $tidy_config, $host, $client_version, $iauth, $user_id, $rn, $cmd_id, $friends_for_invite_in_gambling_zone, $material_list;

    echo "Работа с друзьями в казино\n";

    $location_data = $tidy->repairString($location_data, $tidy_config);

    $location_data_xml = new DOMDocument();
    $location_data_xml->loadXML($location_data);

    $barn_data_xml = new DOMDocument();
    $barn_data_xml->loadXML($location_data_xml->saveXML($location_data_xml->getElementsByTagName('barn')->item(0)));

    $room_staff = json_decode($location_data_xml->getElementsByTagName('country')->item(0)->attributes->getNamedItem('room_staff')->nodeValue);
    $roll_counter = $location_data_xml->getElementsByTagName('country')->item(0)->attributes->getNamedItem('roll_counter')->nodeValue;

    $cached_array = [];
    $cached_id = 0;

    $barn_amount = [];

    foreach($barn_data_xml->childNodes->item(0)->childNodes as $barn) {
        if (in_array($barn->localName, $material_list)) {
            $field_quantity = $barn->attributes->getNamedItem('quantity')->nodeValue;
            $barn_amount[$barn->attributes->getNamedItem('id')->nodeValue] = $field_quantity;
        }
    }

    $contracts = [];

    foreach ($room_staff as $friend_id => $friend) {
        if (isset($friend->time_end)) {
            if ($friend->time_end == 0) {
                ++$barn_amount[$friend->material_id];

                $cached_part = array('command' => 'pick_room_staff', 'cmd_id' => $cmd_id, 'room_id' => '4', 'friend_id' => $friend_id, 'item_id' => '39052472');
                ++$cmd_id;

                $friends_for_invite_in_gambling_zone[] = $friend_id; //TODO: проверить, правильно ли я делаю, приглашая поиграть соседа, с которого только что снял барыш

                $cached_array[] = $cached_part;
            } elseif ($friend->time_end > 0) {
                if (isset($contracts[$friend->contract_id]))
                    ++$contracts[$friend->contract_id];
                else
                    $contracts[$friend->contract_id] = 0;
            }
        }
    }

    foreach ($room_staff as $friend_id => $friend) {
        if (!isset($friend->time_end)) {
            $cached_part = array('command' => 'put_room_staff', 'cmd_id' => $cmd_id, 'roll_counter' => $roll_counter, 'friend_id' => $friend_id, 'item_id' => '39052472', 'room_id' => '4');
            if ($contracts['15306'] < 6)
                $cached_part['contract_id'] = '15306';
            else {
                if ($contracts['15305'] < 6)
                    $cached_part['contract_id'] = '15305';
                else
                    break;
            }
            ++$cmd_id;
            ++$roll_counter;

            $cached_array[] = $cached_part;
        }
    }

    if (count($friends_for_invite_in_gambling_zone) > 0) {
        $cached_part = array('command' => 'send_mass_request', 'cmd_id' => $cmd_id, 'room_id' => '4', 'name' => 'gambling_zone_staff', 'friend_ids' => implode('%2C', $friends_for_invite_in_gambling_zone));
        ++$cmd_id;

        $cached_array[] = $cached_part;
    }

    if (count($cached_array) > 0) {
        for ($i = count($cached_array); $i > 0; --$i) {
            echo "Работа с друзьями в казино $i сек.\n";
            sleep(1);
        }

        $field_time = time() - count($cached_array);
        $cached_string = '';
        foreach ($cached_array as $cached_part) {
            $cached_part['uxtime'] = ++$field_time;
            foreach ($cached_part as $key => $value) {
                $cached_string .= "&cached[$cached_id][$key]=$value";
            }
            ++$cached_id;
        }

        $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=4&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
        curl_exec($ch);
        curl_close($ch);
        ++$rn;
    }

    $cached_array = [];
    $cached_id = 0;

    foreach($barn_amount as $barn_id => $barn_count) {
        if ($barn_count > 50) {
            for ($i = 50; $i < $barn_count; ++$i) {
                $cached_part = array('command' => 'sell_barn', 'cmd_id' => $cmd_id, 'room_id' => '4', 'item_id' => $barn_id, 'quantity' => '1');
                ++$cmd_id;

                $cached_array[] = $cached_part;
            }
        }
    }

    if (count($cached_array) > 0) {
        for ($i = count($cached_array); $i > 0; --$i) {
            echo "Ждём продажи материалов $i сек.\n";
            sleep(1);
        }

        $field_time = time() - count($cached_array);
        $cached_string = '';
        foreach ($cached_array as $cached_part) {
            $cached_part['uxtime'] = ++$field_time;
            foreach ($cached_part as $key => $value) {
                $cached_string .= "&cached[$cached_id][$key]=$value";
            }
            ++$cached_id;
        }

        $url = "iauth=$iauth&user_id=$user_id&daily_gift=2&room_id=4&serv_ver=1$cached_string&lang=ru&rand=0." . rand(0, 9999999) . "&live_update=true&rn=$rn";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$host/city_server_sqint_prod/check_and_perform");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: city-android-' . $client_version, 'Accept: */*', 'Accept-Encoding: gzip'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
        curl_exec($ch);
        curl_close($ch);
        ++$rn;
    }
}
