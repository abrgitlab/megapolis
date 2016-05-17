<?php

/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 18:12
 */
class Contracts
{

    private static $data = [
        //0-я комната
        [
            //НИЯУ
            'srnu_factory' => [
                'short' => [
                    'contract' => 'radioisotopes_preparations'
                ],
                'long' => [
                    'contract' => 'atomic_nuclei_transformation'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт истории
            'institute_of_history_stage1' => [
                'short' => [
                    'contract' => 'stratigraphic_studies'
                ],
                'actions' => ['pick', 'put']
            ],

            //Дворец здоровья
            'health_palace_stage2' => [
                'short' => [
                    'contract' => 'rehabilitation_of_athletes'
                ],
                'actions' => ['pick', 'put']
            ],

            //Городской центр спорта
            'urban_sports_center_stage2' => [
                'short' => [
                    'contract' => 'tournament_on_mini_football'
                ],
                'actions' => ['pick', 'put']
            ],

            //Региональный центр сотовой связи
            'mobile_phone_system_center_stage4' => [
                'short' => [
                    'contract' => 'ggg_connection',
                    'additional_fields' => [
                        'affected_items' => '31861965%3A39734593%2C35861946%2C39856375%2C28557984%2C34053392%2C29488690%2C35767257%2C28239235%2C28557634%2C28199082%2C34152209%2C30516461%2C29505614%2C33759002%2C35649209%2C28198974%2C35958760%2C40198010%2C32833679%2C28558178%2C35862765%2C30079491%2C30516462%2C28239236%2C35727249%2C35019780%3B31811512%3A35958674%2C48176662%2C34176308%2C34343912%2C36380765%2C34977101%2C36249841%2C35851055%2C35920361%2C35741945%2C29007227%2C35958568%2C29299986%2C28558161%2C35958662%2C34879378%3B40966797%3A%3B40966833%3A41689198%2C41778317%2C39715714%2C41924920%2C41652564%3B'
                    ]
                ],
                'actions' => ['pick', 'put']
            ],

            //Лесопырка
            'sawmill_middle' => [
                'short' => [
                    'contract' => 'industrial_wood'
                ],
                'long' => [
                    'contract' => 'case_furniture'
                ],
                'actions' => ['pick', 'put']
            ],

            //Крейсер
            'naval_station_stage8' => [
                'short' => [
                    'contract' => 'marine_corps_training'
                ],
                'long' => [
                    'contract' => 'patrol_maritime_borders'
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр управления пароходными линиями
            'central_port_stage4' => [
                'short' => [
                    'contract' => 'industrial_fishing'
                ],
                'actions' => ['pick', 'put']
            ],

            //SQ-Сити
            'sq_city_stage2' => [
                'short' => [
                    'contract' => 'government_proceedings'
                ],
                'long' => [
                    'contract' => 'trading'
                ],
                'actions' => ['pick', 'put']
            ],

            //Школа актёрского мастерства
            'acting_school_stage1' => [
                'short' => [
                    'contract' => 'lesson_of_acting_skills'
                ],
                'actions' => ['pick', 'put']
            ],

            //Поместье Маунт-Вернон
            'mount_vernon_stage1' => [
                'short' => [
                    'contract' => 'organization_of_the_state_reception'
                ],
                'actions' => ['pick', 'put']
            ],

            //Уайтхолльский дворец
            'uaythollsky_palace_stage2' => [
                'short' => [
                    'contract' => 'salute_of_guns_combat'
                ],
                'actions' => ['pick', 'put']
            ],

            //Национальный центр кинематографии
            'national_center_cinema' => [
                'short' => [
                    'contract' => 'awarding_of_prizes'
                ],
                'actions' => ['pick', 'put']
            ],

            //Ассоциация тенниса
            'lawn_tennis_association_stage1' => [
                'short' => [
                    'contract' => 'exhibition_history_tennis'
                ],
                'long' => [
                    'contract' => 'welcome_participants_tournament', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Готический замок
            'gothic_castle_stage3' => [
                'short' => [
                    'contract' => 'parade_monsters'
                ],
                'actions' => ['pick', 'put']
            ],

            //Виноградная ферма
            'grape_farm_stage3' => [
                'short' => [
                    'contract' => 'festival_fr_cuisine'
                ],
                'actions' => ['pick', 'put']
            ],

            //Поло-клуб
            'polo_club_stage1' => [
                'short' => [
                    'contract' => 'participants_championship', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Експоцентр
            'fairgrounds_stage2' => [
                'short' => [
                    'contract' => 'tune_sportscar'
                ],
                'long' => [
                    'contract' => 'presentation_supercar'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт военных разработок
            'military_research_institute_stage1' => [
                'short' => [
                    'contract' => 'introduction_of_protective_artificial_intelligence'
                ],
                'actions' => ['pick', 'put']
            ],

            //Геодезическая компания
            'geodesic_company_stage3' => [
                'short' => [
                    'contract' => 'mineralic_analysis_territory'
                ],
                'long' => [
                    'contract' => 'study_of_the_mineral'
                ],
                'actions' => ['pick', 'put']
            ],

            //Золотодобывающая корпорация
            'gold_mining_company_stage2' => [
                'short' => [
                    'contract' => 'investigation_methods_enriching'
                ],
                'long' => [
                    'contract' => 'enrichment_gold_ore'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт исследований космоса
            'space_research_institute_stage2' => [
                'short' => [
                    'contract' => 'develop_interstellar_dating', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'calculating_coordinates_send_messages', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центральная панорама
            'statue_burning_man_stage2' => [
                'short' => [
                    'contract' => 'performance_viewpoint'
                ],
                'actions' => ['pick', 'put']
            ],

            //Карнакский храм
            'karnak_temple_stage2' => [
                'short' => [
                    'contract' => 'discovery_of_cave_painting'
                ],
                'long' => [
                    'contract' => 'study_of_ancient_writing'
                ],
                'actions' => ['pick', 'put']
            ],

            //Пиратский форт
            'pirate_fort_stage2' => [
                'short' => [
                    'contract' => 'themed_tours'
                ],
                'actions' => ['pick', 'put']
            ],

            //Администрация зоопарка
            'administration_zoo_up1' => [
                'short' => [
                    'contract' => 'exhibition_of_birds', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Ассоциация рок-н-ролла
            'association_rock_and_roll' => [
                'short' => [
                    'contract' => 'festival_of_young_rock_musicians', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр военно-исторической реконструкции
            'club_military_reconstruction_stage2' => [
                'short' => [
                    'contract' => 'making_knightly_accoutrements'
                ],
                'long' => [
                    'contract' => 'joust', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр "Чёрная пантера"
            'center_black_panther_stage1' => [
                'short' => [
                    'contract' => 'seminar_on_fashion_and_art', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'exhibition_expensive_clothes', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Фестивальный замок
            'colomares_castle_stage3' => [
                'short' => [
                    'contract' => 'preparatory_work'
                ],
                'long' => [
                    'contract' => 'capture_a_fort', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Архитектурная академия
            'architectural_academy_stage1' => [
                'short' => [
                    'contract' => 'architectural_projects_ecology_estimation'
                ],
                'actions' => ['pick', 'put']
            ],

            //Новогодний Таймс-сквер
            'times_square_stage2' => [
                'short' => [
                    'contract' => 'show_retro_films'
                ],
                'actions' => ['pick', 'put']
            ],

            //Агентство праздников
            'holidays_agency_stage1' => [
                'short' => [
                    'contract' => 'order_copyright_postcards'
                ],
                'long' => [
                    'contract' => 'holidays_party', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Научно-информационный центр "Фьюжн"
            'scientific_information_center_fyuzion_stage1' => [
                'short' => [
                    'contract' => 'development_of_new_safety_systems', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Университет Мегаполиса
            'megapolis_university_training' => [
                'short' => [
                    'contract' => 'term_paper_writing'
                ],
                'actions' => ['pick', 'put']
            ],

            //Театр "Лебедь"
            'swan_theater_stage1' => [
                'short' => [
                    'contract' => 'theatrical_makeup_master_class'
                ],
                'long' => [
                    'contract' => 'shakespeare_costume_parade', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Администрация отельного комплекса "Шимао"
            'administration_hotel_complex_shima_stage1' => [
                'short' => [
                    'contract' => 'inauguration_hotel_shima'
                ],
                'actions' => ['pick', 'put']
            ],

            //Расширение территории института
            'citys_ecology_institute_stage2' => [
                'short' => [
                    'contract' => 'earth_hour'
                ],
                'actions' => ['pick', 'put']
            ],

            //Истребитель
            'airbase_stage8' => [
                'short' => [
                    'contract' => 'pilots_training'
                ],
                'long' => [
                    'contract' => 'armor_transport'
                ],
                'actions' => ['pick', 'put']
            ],

            //Дрейфующая станция
            'drifting_station_stage2' => [
                'short' => [
                    'contract' => 'grand_opening_loona_park'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт експериментальной медицины
            'medical_centre_stage3' => [
                'short' => [
                    'contract' => 'medical_conference'
                ],
                'long' => [
                    'contract' => 'innovation_heal_methods_developing', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Реставрационный комплекс
            'restoration_complex_stage3' => [
                'short' => [
                    'contract' => 'sampling_gold'
                ],
                'long' => [
                    'contract' => 'calculation_coordinates_city_of_gold'
                ],
                'actions' => ['pick', 'put']
            ],

            //Клуб пилотов
            'club_pilots_stage3' => [
                'short' => [
                    'contract' => 'championship_airslalom'
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр парусного спорта
            'sailing_center_stage3' => [
                'short' => [
                    'contract' => 'windsurfing', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Круизная компания
            'cruise_company_stage2' => [
                'short' => [
                    'contract' => 'competition_kissing_mistletoe'
                ],
                'long' => [
                    'contract' => 'solemn_liner_departure', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт демографии
            'institute_of_demography' => [
                'short' => [
                    'contract' => 'family_day_at_restaurant'
                ],
                'long' => [
                    'contract' => 'fitness_marathon', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Оттавский комплекс для конференций
            'ottawa_convention_centre_stage1' => [
                'short' => [
                    'contract' => 'onference_of_information_technology', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Выставочный зал "Ноев Ковчег"
            'noahs_ark_stage2' => [
                'short' => [
                    'contract' => 'exhibition_life_on_earth'
                ],
                'long' => [
                    'contract' => 'installation_discworld'
                ],
                'actions' => ['pick', 'put']
            ],

            //Центральный вокзал
            'central_station' => [
                'short' => [
                    'contract' => 'international_passenger_traffic', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Единая служба спасения
            'help_centre_final' => [
                'short' => [
                    'contract' => 'open_help_centers'
                ],
                'actions' => ['pick', 'put']
            ],

            //Пирамида
            'pyramid' => [
                'short' => [
                    'contract' => 'archaeological_excavations'
                ],
                'actions' => ['pick', 'put']
            ],

            //Площадь святого Марка
            'rains_palace_stage2' => [
                'short' => [
                    'contract' => 'carnival_night_organization', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Криогенная установка
            'cryogenic_plant_stage2' => [
                'short' => [
                    'contract' => 'christmas_flashmob', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'contest_ice_sculptures', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр изучения окружающей среды
            'center_environmental_studies_stage3' => [
                'short' => [
                    'contract' => 'international_summit_green_energy'
                ],
                'actions' => ['pick', 'put']
            ],

            //Плавучий екополис
            'congress_center_hangzhou_stage2' => [
                'short' => [
                    'contract' => 'introduction_graphene_capacitors'
                ],
                'long' => [
                    'contract' => 'hydrowave_ocean_clean', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Финансовый центр "Прайм"
            'financial_center_prime' => [
                'short' => [
                    'contract' => 'exchange_trading'
                ],
                'actions' => ['pick', 'put']
            ],

            //Хелипорт
            'heliport_stage2' => [
                'short' => [
                    'contract' => 'passenger_flights'
                ],
                'actions' => ['pick', 'put']
            ],

            //Каркас валютного фонда Мегаполиса
            'monetary_fund_megapolis_stage1' => [
                'short' => [
                    'contract' => 'manufacturing_gift_set_coins'
                ],
                'actions' => ['pick', 'put']
            ],

            //Информационно-аналитический центр
            'information_analysis_center_stage2' => [
                'short' => [
                    'contract' => 'logging_information'
                ],
                'long' => [
                    'contract' => 'introduction_of_new_security_protocols', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Пассажирский авиалайнер
            'passenger_airplane3_buildsite' => [
                'short' => [
                    'contract' => 'air_walk1'
                ],
                'actions' => ['pick', 'put']
            ],

            //Пассажирский авиалайнер
            'passenger_airplane4_buildsite' => [
                'short' => [
                    'contract' => 'air_walk1'
                ],
                'actions' => ['pick', 'put']
            ],

            //Грузовой самолёт
            'cargo_airplane7_buildsite' => [
                'short' => [
                    'contract' => 'urgent_cargo_delivery1'
                ],
                'actions' => ['pick', 'put']
            ],

            //Портовый склад
            'port_warehouse_stage4' => [
                'short' => [
                    'contract' => 'city_goods_export'
                ],
                'actions' => ['pick']
            ],

            //Администрация проекта
            'project_administration_stage2' => [
                'short' => [
                    'contract' => 'oasis_ecosystem_exploration'
                ],
                'long' => [
                    'contract' => 'project_conference', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Морская разведывательная платформа
            'maritime_intelligence_platform_stage1' => [
                'short' => [
                    'contract' => 'trial_run_lifting_mechanism'
                ],
                'actions' => ['pick', 'put']
            ]
        ],

        //1-я комната
        [
            //НИИ геологии
            'geological_institute_stage2' => [
                'short' => [
                    'contract' => 'research_of_solid'
                ],
                'actions' => ['pick', 'put']
            ],

            //Транспортный вокзал "Лазурная река"
            'busstation_azure_river_up1' => [
                'short' => [
                    'contract' => 'long_distance_bus'
                ],
                'actions' => ['pick', 'put']
            ],

            //Вибрационный грохот
            'clinker_technic2' => [
                'actions' => ['pick']
            ],

            //Роторная дробилка
            'crushing_plant2' => [
                'actions' => ['pick']
            ],

            //Полноповортный екскаватор
            'coal_mine_medium_excavator' => [
                'actions' => ['pick']
            ],

            //Камнекольный станок
            'natural_stone_mine_technic2' => [
                'actions' => ['pick']
            ],

            //Угольный комплекс
            'coal_industry' => [
                'actions' => ['pick']
            ],

            //Горно-обогатительный комбинат
            'mining_processing_plant_stage3' => [
                'actions' => ['pick']
            ],

            //Завод сыпучих материалов
            'cement_plant_final' => [
                'actions' => ['pick']
            ],

            //Камнедробильный комплекс
            'stone_crushing_plant_mining_up1' => [
                'actions' => ['pick']
            ],

            //Прокатно-калибровочный цех
            'rolling_mill_stage3' => [
                'actions' => ['pick']
            ],

            //Строительно-промышленный комплекс
            'construction_and_industrial_complex_final' => [
                'actions' => ['pick']
            ],

            //Комплекс железобетонных изделий
            'complex_of_concrete_products_stage2' => [
                'actions' => ['pick']
            ],

            //Металлургический комбинат
            'iron_and_steel_works' => [
                'actions' => ['pick']
            ]
        ],

        //2-я комната
        [
            //Аеропорт
            'island_airport_stage2' => [
                'short' => [
                    'contract' => 'local_flights'
                ],
                'actions' => ['pick', 'put']
            ],

            //Старинный форт
            'ancient_fort_stage2' => [
                'short' => [
                    'contract' => 'excursion_to_historic_fort', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Отель на воде
            'hotel_on_water_stage4' => [
                'short' => [
                    'contract' => 'press_conference', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Аквапарк
            'aquapark_stage2' => array(
                'short' => array(
                    'contract' => 'conducting_childrens_holiday'
                ),
                /*'quest_inc_counter' => [
                    'on' => 'put',
                    'quest_id' => '20417',
                    'counter' => '0',
                    'count' => '100'
                ],*/
                'actions' => ['pick', 'put']
            ),

            //Морской терминал
            'marine_terminal_stage3' => [
                'short' => [
                    'contract' => 'delivery_tourists_on_yahte',
                ],
                'long' => [
                    'contract' => 'delivery_tourists_on_seaplane',
                ],
                'quest_inc_counter' => [
                    'on' => 'pick',
                    'quest_id' => '10148',
                    'counter' => '0',
                    'count' => '200'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт римской цивилизации
            'institute_roman_civilization_stage2' => [
                'short' => [
                    'contract' => 'archaeological_expertise_ruins', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'excursion_pompei'
                ],
                'actions' => ['pick', 'put']
            ]
        ],

        //3-я комната
        [],

        //4-я комната
        [
            //Турбовинтовой самолёт
            'airport_gamble_plane2_buildsite' => [
                'short' => [
                    'contract' => 'organization_of_jumps'
                ],
                'actions' => ['pick', 'put']
            ],

            //Реактивный самолёт
//            'airport_gamble_plane1_stage1' => [
//                 'short' => [
//                     'contract' => 'organization_of_jumps'
//                 ],
////                'long' => [
////                    'contract' => '', 'friends_request' => true
////                ],
//                'actions' => ['pick', 'put']
//            ],

            //Турбореактивный самолёт
            'airport_gamble_plane1_stage2' => [
                'short' => [
                    'contract' => 'organization_of_jumps'
                ],
                'long' => [
                    'contract' => 'long_distance_flights', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Паромная станция
            'gambling_ferry_station_stage2' => [
                'short' => [
                    'contract' => 'trial_run_hydropower'
                ],
                'long' => [
                    'contract' => 'inauguration_lighthouse', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Казино "Люминус Холл"
            'casino_lyuminus_hall_stage2' => [
                'short' => [
                    'contract' => 'marathon_running_lights'
                ],
                'long' => [
                    'contract' => 'christmas_laser_show'
                ],
                'actions' => ['pick', 'put']
            ]
        ],

        //5-я комната
        [
            //НИЯУ
            'srnu_factory' => [
                'short' => [
                    'contract' => 'radioisotopes_preparations'
                ],
                'long' => [
                    'contract' => 'atomic_nuclei_transformation'
                ],
                'actions' => ['pick', 'put']
            ],

            //Церемониальная площадь
            'victory_stadium_stage2' => [
                'short' => [
                    'contract' => 'preparation_of_stadiums_for_winter_games'
                ],
                'actions' => ['pick', 'put']
            ],

            //Медиа-центр
            'media_center_snow_stage1' => [
                'short' => [
                    'contract' => 'meeting_competitors'
                ],
                'actions' => ['pick', 'put']
            ],

            //Горный отель
            'mountain_hotel_stage1' => [
                'short' => [
                    'contract' => 'snowboarding'
                ],
                'actions' => ['pick', 'put']
            ],

            //Архитектурный исследовательский комплекс
            'macau_centre_hotel' => [
                'short' => [
                    'contract' => 'deciphering_secret_blueprints'
                ],
                'actions' => ['pick', 'put']
            ],

            //Горный хелипорт
            'mountain_heliport_stage1' => [
                'short' => [
                    'contract' => 'helicopter_tour_of_rockies'
                ],
                'actions' => ['pick', 'put']
            ],

            //Сервисный центр
            'area_aerostats_stage2' => [
                'short' => [
                    'contract' => 'inflating_balloons'
                ],
                'actions' => ['pick', 'put']
            ],

            //Шоу-площадка "Фантазия"
            'show_fantasy_park_stage2' => [
                'short' => [
                    'contract' => 'pre_christmas_outing'
                ],
                'long' => [
                    'contract' => 'festive_hologram_presentation'
                ],
                'actions' => ['pick', 'put']
            ],

            //Штаб-квартира секретной организации
            'headquarters_secret_organization_stage3' => [
                'short' => [
                    'contract' => 'creating_network_agents'
                ],
                'actions' => ['pick', 'put']
            ],

            //Тренировочный центр скалолазанья
            'climbing_training_center_stage3' => [
                'short' => [
                    'contract' => 'set_school_climbers'
                ],
                'long' => [
                    'contract' => 'demo_performances_climbers'
                ],
                'actions' => ['pick', 'put']
            ]
        ]
    ];

    /**
     * @param $room Room
     * @return array
     */
    public static function getContractsList($room) {
        $result = Contracts::$data[$room->id];
        $city_goods = $room->getBarn('city_goods');
        if ($room->id == 0 && $city_goods !== null && $city_goods >= 5) {
            $result['port_warehouse_stage4']['actions'][] = 'put';
        }

        return $result;
    }
}