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
            //не окончательно
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
            //не окончательно
            'mobile_phone_system_center_stage4' => [
                'short' => [
                    'contract' => 'ggg_connection',
                    'additional_fields' => [
                        'affected_items' => '31861965%3A37113832%2C38468457%2C39749269%2C39734593%2C39856375%2C35741945%2C35861946%2C28557984%2C39856156%2C35958662%2C35767257%2C30516461%2C28558161%2C28557634%2C28199082%2C34707051%2C34053392%2C29488690%2C28239235%2C33759002%2C35958760%2C28558178%2C34879378%2C34152209%2C28198974%2C35649209%2C29505614%2C38592107%2C32833679%2C46028446%2C40198010%2C30079491%2C35862765%2C30516462%2C28239236%2C35727249%2C37646714%2C35019780%3B31811512%3A35958674%2C48176662%2C36380765%2C34176308%2C35851055%2C34977101%2C34343912%2C36249841%2C35920361%2C29007227%2C35958568%2C29299986%3B40966797%3A%3B40966833%3A41689198%2C41778317%2C39715714%2C41924920%2C41652564%3B69184661%3A%3B69184662%3A66263497%2C66307550%2C69171339%2C67471287%2C70254386%2C67912604%2C70254432%2C70254389%2C70254373%2C70254401%2C70254460%2C70254379%2C70254404%2C70254461%3B70286818%3A%3B70286821%3A%3B'
                    ]
                ],
                'actions' => ['pick', 'put']
            ],

            //Деревообрабатывающий завод
            //не окончательно
            'sawmill_large' => [
                'short' => [
                    'contract' => 'chipboard'
                ],
                'long' => [
                    'contract' => 'parquet_surface'
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
            //не окончательно
            'central_port_stage4' => [
                'short' => [
                    'contract' => 'industrial_fishing'
                ],
                'actions' => ['pick', 'put']
            ],

            //SQ-Сити
            //не окончательно
            'sq_city_stage3' => [
                'short' => [
                    'contract' => 'government_proceedings'
                ],
                'long' => [
                    'contract' => 'business_center_rent'
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
            //не окончательно
            'uaythollsky_palace_stage2' => [
                'short' => [
                    'contract' => 'salute_of_guns_combat'
                ],
                'actions' => ['pick', 'put']
            ],

            //Национальный центр кинематографии
            //не окончательно
            'national_center_cinema' => [
                'short' => [
                    'contract' => 'awarding_of_prizes'
                ],
                'actions' => ['pick', 'put']
            ],

            //Ассоциация тенниса
            //не окончательно
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
            //не окончательно
            'gothic_castle_stage3' => [
                'short' => [
                    'contract' => 'parade_monsters'
                ],
                'actions' => ['pick', 'put']
            ],

            //Виноградная ферма
            //не окончательно
            'grape_farm_stage3' => [
                'short' => [
                    'contract' => 'festival_fr_cuisine'
                ],
                'actions' => ['pick', 'put']
            ],

            //Поло-клуб
            //не окончательно
            'polo_club_stage1' => [
                'short' => [
                    'contract' => 'champ_horse_polo'
                ],
                'actions' => ['pick', 'put']
            ],

            //Експоцентр
            //не окончательно
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
            //не окончательно
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
            //не окончательно
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
            //не окончательно
            'space_research_institute_stage3' => [
                'short' => [
                    'contract' => 'confirm_extraterrestrial_civilizations', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'send_encrypted_messages', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центральная панорама
            //не окончательно
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
            //не окончательно
            'pirate_fort_stage3' => [
                'short' => [
                    'contract' => 'premiere_film_pirates'
                ],
                'actions' => ['pick', 'put']
            ],

            //Администрация зоопарка
            'administration_zoo_up3' => [
                'short' => [
                    'contract' => 'exhibition_of_birds', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Ассоциация рок-н-ролла
            //не окончательно
            'association_rock_and_roll' => [
                'short' => [
                    'contract' => 'festival_of_young_rock_musicians', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр военно-исторической реконструкции
            //не окончательно
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
            //не окончательно
            'architectural_academy_stage1' => [
                'short' => [
                    'contract' => 'architectural_projects_ecology_estimation'
                ],
                'actions' => ['pick', 'put']
            ],

            //Новогодний Таймс-сквер
            //не окончательно
            'times_square_stage2' => [
                'short' => [
                    'contract' => 'show_retro_films'
                ],
                'actions' => ['pick', 'put']
            ],

            //Агентство праздников
            //не окончательно
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
            //не окончательно
            'megapolis_university_training' => [
                'short' => [
                    'contract' => 'term_paper_writing'
                ],
                'actions' => ['pick', 'put']
            ],

            //Театр "Лебедь"
            //не окончательно
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
            //не окончательно
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
            //не окончательно
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
            //не окончательно
            'club_pilots_stage3' => [
                'short' => [
                    'contract' => 'championship_airslalom'
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр парусного спорта
            //не окончательно
            'sailing_center_stage4' => [
                'short' => [
                    'contract' => 'freestyle_on_the_water', 'friends_request' => true
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
            //не окончательно
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
            //не окончательно
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
            //не окончательно
            'center_environmental_studies_stage3' => [
                'short' => [
                    'contract' => 'international_summit_green_energy'
                ],
                'actions' => ['pick', 'put']
            ],

            //Плавучий екополис
            //не окончательно
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
            //не окончательно
            'financial_center_prime' => [
                'short' => [
                    'contract' => 'exchange_trading'
                ],
                'actions' => ['pick', 'put']
            ],

            //Хелипорт
            //не окончательно
            'heliport_stage3' => [
                'short' => [
                    'contract' => 'passenger_flights'
                ],
                'actions' => ['pick', 'put']
            ],

            //Валютный фонд Мегаполиса
            //не окончательно
            'monetary_fund_megapolis_stage2' => [
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
            //не окончательно
            /*'port_warehouse_stage4' => [
                'short' => [
                    'contract' => 'city_goods_export'
                ],
                'actions' => ['pick']
            ],*/

            //Администрация проекта
            'project_administration_stage2' => [
                'short' => [
                    'contract' => 'project_conference', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'remote_areas_water_supply', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Морская разведывательная платформа
            //не окончательно
            'maritime_intelligence_platform_stage1' => [
                'short' => [
                    'contract' => 'trial_run_lifting_mechanism'
                ],
                'actions' => ['pick', 'put']
            ],

            //Стадион "Мегаполис-арена"
            'stadium_megapolis_arena_stage5' => [
                'short' => [
                    'contract' => 'childrens_championship', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Королевский выставочный центр
            'royal_exhibition_building_stage2' => [
                'short' => [
                    'contract' => 'guided_tours_through_halls_exhibition_center'
                ],
                'actions' => ['pick', 'put']
            ],

            //Ворота востока
            //не окончательно
            'gates_of_east_stage3' => [
                'short' => [
                    'contract' => 'silk_order'
                ],
                'actions' => ['pick', 'put']
            ],

            //Площадь победы
            'town_square_stage1' => [
                'short' => [
                    'contract' => 'the_concert'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт исследования космических тел
            'institute_for_cosmic_bodies_stage1' => [
                'short' => [
                    'contract' => 'the_study_of_extraterrestrial_bodies'
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр парашютной подготовки
            'school_of_paratroopers_stage2' => [
                'short' => [
                    'contract' => 'demonstrations_paratroopers'
                ],
                'actions' => ['pick', 'put']
            ],

            //Банк международных расчётов
            'bank_inter_settlement_stage2' => [
                'short' => [
                    'contract' => 'meeting_founders_bank'
                ],
                'actions' => ['pick', 'put']
            ],

            //Док субмарин
            //не окончательно
            'sea_naval_station_stage5' => [
                'short' => [
                    'contract' => 'patrol_maritime_air_space'
                ],
                'actions' => ['pick', 'put']
            ],

            //Телебашня "Небесное дерево"
            //не окончательно
            'tokyo_skytree_stage2' => [
                'short' => [
                    'contract' => 'the_native_megapolis'
                ],
                'actions' => ['pick', 'put']
            ],

            //Генеральный штаб ВМФ
            //не окончательно
            'navy_headquarters_stage2' => [
                'short' => [
                    'contract' => 'education_senior_officers'
                ],
                'long' => [
                    'contract' => 'carrying_naval_exercises'
                ],
                'actions' => ['pick', 'put']
            ],

            //Судовая ремонтная служба
            //не окончательно
            'ship_repair_service_stage1' => [
                'short' => [
                    'contract' => 'tasting_seafood'
                ],
                'actions' => ['pick', 'put']
            ],

            //Аеровокзал
            //не окончательно
            'airport_stage2_level2' => [
                'short' => [
                    'contract' => 'airsport_competition'
                ],
                'actions' => ['pick', 'put']
            ],

            //Администрация водных гонок
            //не окончательно
            'water_races_administration_stage1' => [
                'short' => [
                    'contract' => 'check_condition_cars'
                ],
                'actions' => ['pick', 'put']
            ],

            //М-Сити
            //не окончательно
            'm_city_stage2' => [
                'short' => [
                    'contract' => 'job_fair_for_young_proff'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт природных явлений
            //не окончательно
            'institute_natural_phenomena_stage1' => [
                'short' => [
                    'contract' => 'sampling_from_bottom_crater'
                ],
                'actions' => ['pick', 'put']
            ],

            //Гидротехнический комплекс
            'base_hydrotechnicians_stage3' => [
                'short' => [
                    'contract' => 'installing_geomembranes'
                ],
                'long' => [
                    'contract' => 'extreme_canyon_tour', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Бизнес-джет
            //не окончательно
            'business_jet1_buildsite' => [
                'short' => [
                    'contract' => 'custom_route_flight1'
                ],
                'actions' => ['pick', 'put']
            ],

            //Грузовой терминал
            //не окончательно
            'cargo_airplane7_stage1' => [
                'short' => [
                    'contract' => 'drugs_dispatch1'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт геокриологии
            //не окончательно
            'institute_geocryology_stage1' => [
                'short' => [
                    'contract' => 'measuring_size_glacier'
                ],
                'actions' => ['pick', 'put']
            ],

            //Отельный комплекс "Конрад"
            //не окончательно
            'hotel_konrad_stage2' => [
                'short' => [
                    'contract' => 'organization_charity_reception'
                ],
                'actions' => ['pick', 'put']
            ],

            //Департамент мелиорации
            //не окончательно
            'department_land_reclamation_stage2' => [
                'short' => [
                    'contract' => 'data_collecting_submarine'
                ],
                'long' => [
                    'contract' => 'submarine_repair'
                ],
                'actions' => ['pick', 'put']
            ]
        ],

        //1-я комната
        [
            //НИИ геологии
            //не окончательно
            'geological_institute_stage2' => [
                'short' => [
                    'contract' => 'research_of_solid'
                ],
                'actions' => ['pick', 'put']
            ],

            //Транспортный вокзал "Лазурная река"
            /*'busstation_azure_river_up1' => [
                'short' => [
                    'contract' => 'long_distance_bus'
                ],
                'actions' => ['pick', 'put']
            ],*/

            //Вибрационный грохот
            /*'clinker_technic2' => [
                'actions' => ['pick']
            ],*/

            //Вибрационный грохот
            /*'clinker_technic1' => [
                'actions' => ['pick']
            ],*/

            //Роторная дробилка
            /*'crushing_plant2' => [
                'actions' => ['pick']
            ],*/

            //Вибрационная мельница
            /*'crushing_plant1' => [
                'actions' => ['pick']
            ],*/

            //Полноповортный екскаватор
            /*'coal_mine_medium_excavator' => [
                'actions' => ['pick']
            ],*/

            //Цепной екскаватор
            /*'coal_mine_small_excavator' => [
                'actions' => ['pick']
            ],*/

            //Камнекольный станок
            /*'natural_stone_mine_technic2' => [
                'actions' => ['pick']
            ],*/

            //Камнекольный станок
            /*'natural_stone_mine_technic1' => [
                'actions' => ['pick']
            ],*/

            //Угольный комплекс
            /*'coal_industry' => [
                'actions' => ['pick']
            ],*/

            //Горно-обогатительный комбинат
            /*'mining_processing_plant_stage3' => [
                'actions' => ['pick']
            ],*/

            //Завод сыпучих материалов
            /*'cement_plant_final' => [
                'actions' => ['pick']
            ],*/

            //Камнедробильный комплекс
            /*'stone_crushing_plant_mining_up1' => [
                'actions' => ['pick']
            ],*/

            //Прокатно-калибровочный цех
            /*'rolling_mill_stage3' => [
                'actions' => ['pick']
            ],*/

            //Строительно-промышленный комплекс
            /*'construction_and_industrial_complex_final' => [
                'actions' => ['pick']
            ],*/

            //Комплекс железобетонных изделий
            /*'complex_of_concrete_products_stage2' => [
                'actions' => ['pick']
            ],*/

            //Металлургический комбинат
            /*'iron_and_steel_works' => [
                'actions' => ['pick']
            ]*/
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

            //Роял отель на воде
            'hotel_on_water_stage4' => [
                'short' => [
                    'contract' => 'press_conference', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Аквапарк
            'aquapark_stage4' => array(
                'short' => array(
                    'contract' => 'artificial_surf_champ'
                ),
//                'quest_inc_counter' => [
//                    'on' => 'put',
//                    'quest_id' => '20417',
//                    'counter' => '0',
//                    'count' => '100'
//                ],
                'actions' => ['pick', 'put']
            ),

            //Морской терминал
            'marine_terminal_stage5' => [
                'short' => [
                    'contract' => 'delivery_tourists_on_liner',
                ],
//                'quest_inc_counter' => [
//                    'on' => 'pick',
//                    'quest_id' => '10148',
//                    'counter' => '0',
//                    'count' => '200'
//                ],
                'actions' => ['pick', 'put']
            ],

            //Институт римской цивилизации
            //не окончательно
            'institute_roman_civilization_stage2' => [
                'short' => [
                    'contract' => 'archaeological_expertise_ruins', 'friends_request' => true
                ],
                'long' => [
                    'contract' => 'excursion_pompei'
                ],
                'actions' => ['pick', 'put']
            ],

            //Термальные ванны
            'termal_bath_stage2' => [
                'short' => [
                    'contract' => 'mineral_baths', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Императорский дворец
            //не окончательно
            'imperial_palace_stage1' => [
                'short' => [
                    'contract' => 'launching_sky_lanterns', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Обсерватория
            //не окончательно
            'observatory_stage3' => [
                'short' => [
                    'contract' => 'lecture_cosmology', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Центр исторической реконструкции
            //не окончательно
            'hist_reconstruction_center_stage1' => [
                'short' => [
                    'contract' => 'sale_of_longtailed_boats'
                ],
                'actions' => ['pick', 'put']
            ],

            'restoration_work_service_stage1' => [
                'short' => [
                    'contract' => 'seascape_admiration'
                ],
                'actions' => ['pick', 'put']
            ]
        ],

        //3-я комната
        [],

        //4-я комната
        [
            //Турбовинтовой самолёт
            //не окончательно
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
            //не окончательно
            'airport_gamble_plane1_stage2' => [
                'short' => [
                    'contract' => 'organization_of_jumps'
                ],
                'long' => [
                    'contract' => 'regional_flights', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Паромная станция
            //не окончательно
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
            ],

            //Фундамент департамента природных катаклизмов
            //не окончательно
            'department_natural_disasters_buildsite' => [
                'short' => [
                    'contract' => 'sale_products_igneous_rocks'
                ],
                'actions' => ['pick', 'put']
            ],

            //Департамент развития туризма
            //не окончательно
            'department_tourism_development_stage2' => [
                'short' => [
                    'contract' => 'release_tools_cutting_stone'
                ],
                'long' => [
                    'contract' => 'inauguration_of_city_in_rock', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Ассоциация гонщиков Гран-при
            //не окончательно
            'gambling_racers_association_grand_prix_stage2' => [
                'short' => [
                    'contract' => 'license_racers_association'
                ],
                'long' => [
                    'contract' => 'auto_racing_las_megas'
                ],
                'actions' => ['pick', 'put']
            ],

            //Уловитель молний
            //не окончательно
            'catcher_lightning_stage5' => [
                'short' => [
                    'contract' => 'trial_run_trap_lightning'
                ],
                'long' => [
                    'contract' => 'study_ball_lightning'
                ],
                'actions' => ['pick', 'put']
            ],

            //Лаборатория биолюминесценции
            //не окончательно
            'lab_bioluminescence_stage2' => [
                'short' => [
                    'contract' => 'luminescence_in_nature'
                ],
                'actions' => ['pick', 'put']
            ],

            //Департамент природных катаклизмов
            //не окончательно
            'department_natural_disasters_stage1' => [
                'short' => [
                    'contract' => 'sale_products_igneous_rocks'
                ],
                'long' => [
                    'contract' => 'article_about_blue_lights'
                ],
                'actions' => ['pick', 'put']
            ],

            //Институт полярного сияния
            //не окончательно
            'institute_aurora_stage1' => [
                'short' => [
                    'contract' => 'experiment_accelerate_particle'
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
            //не окончательно
            'victory_stadium_stage2' => [
                'short' => [
                    'contract' => 'preparation_of_stadiums_for_winter_games'
                ],
                'actions' => ['pick', 'put']
            ],

            //Медиа-центр
            //не окончательно
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

            //Площадка аеростатов
            //не окончательно
            'area_aerostats_stage3' => [
                'short' => [
                    'contract' => 'parachute_training'
                ],
                'long' => [
                    'contract' => 'festival_kites', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Шоу-площадка "Фантазия"
            //не окончательно
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
            //не окончательно
            'headquarters_secret_organization_stage3' => [
                'short' => [
                    'contract' => 'creating_network_agents'
                ],
                'actions' => ['pick', 'put']
            ],

            //Тренировочный центр скалолазанья
            //не окончательно
            'climbing_training_center_stage3' => [
                'short' => [
                    'contract' => 'set_school_climbers'
                ],
                'long' => [
                    'contract' => 'demo_performances_climbers'
                ],
                'actions' => ['pick', 'put']
            ],

            //Отель "Снежная вершина"
            //не окончательно
            'hotel_snowy_peak_stage2' => [
                'short' => [
                    'contract' => 'development_insulated_tramcars'
                ],
                'actions' => ['pick', 'put']
            ],

            //Праздничная площадь
            //не окончательно
            'christmas_square_stage2' => [
                'short' => [
                    'contract' => 'dancing_on_ice', 'friends_request' => true
                ],
                'actions' => ['pick', 'put']
            ],

            //Ворота востока
            //не окончательно
            'gates_of_east_stage3' => [
                'short' => [
                    'contract' => 'silk_order'
                ],
                'actions' => ['pick', 'put']
            ],

            //Банк международных расчётов
            'bank_inter_settlement_stage2' => [
                'short' => [
                    'contract' => 'meeting_founders_bank'
                ],
                'actions' => ['pick', 'put']
            ],

            //Королевский выставочный центр
            'royal_exhibition_building_stage2' => [
                'short' => [
                    'contract' => 'guided_tours_through_halls_exhibition_center'
                ],
                'actions' => ['pick', 'put']
            ],

            //М-Сити
            //не окончательно
            'm_city_stage2' => [
                'short' => [
                    'contract' => 'job_fair_for_young_proff'
                ],
                'actions' => ['pick', 'put']
            ],

            //Отельный комплекс "Конрад"
            //не окончательно
            'hotel_konrad_stage2' => [
                'short' => [
                    'contract' => 'organization_charity_reception'
                ],
                'actions' => ['pick', 'put']
            ],

            //Китайская ассоциация архитектуры
            //не окончательно
            'china_association_arch_stage2' => [
                'short' => [
                    'contract' => 'viewing_film_wall'
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
        $city_goods = $room->getBarnQuantity('city_goods');
        if ($room->id == 0 && $city_goods >= 5) {
            $result['port_warehouse_stage4']['actions'][] = 'put';
        }

        return $result;
    }
}
