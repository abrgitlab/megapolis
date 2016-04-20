<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 20.04.16
 * Time: 22:53
 */

//$current_time = time();
$current_time = mktime(0, 0, 1, 4, 24, 2016);
$current_time = mktime(1, 0, 1, 4, 24, 2016);
$current_time = mktime(2, 0, 1, 4, 24, 2016);
$current_time = mktime(3, 0, 1, 4, 24, 2016);
$current_time = mktime(4, 0, 1, 4, 24, 2016);
$current_time = mktime(5, 0, 1, 4, 24, 2016);
$current_time = mktime(6, 0, 1, 4, 24, 2016);
$current_time = mktime(7, 0, 1, 4, 24, 2016);
$current_time = mktime(8, 0, 1, 4, 24, 2016);
$current_time = mktime(9, 0, 1, 4, 24, 2016);
$current_time = mktime(10, 0, 1, 4, 24, 2016);
$current_time = mktime(11, 0, 1, 4, 24, 2016);
$current_time = mktime(12, 0, 1, 4, 24, 2016);
$current_time = mktime(13, 0, 1, 4, 24, 2016);
$current_time = mktime(14, 0, 1, 4, 24, 2016);
$current_time = mktime(15, 0, 1, 4, 24, 2016);
$current_time = mktime(16, 0, 1, 4, 24, 2016);
$current_time = mktime(17, 0, 1, 4, 24, 2016);
$current_time = mktime(18, 0, 1, 4, 24, 2016);
$current_time = mktime(19, 0, 1, 4, 24, 2016);
$current_time = mktime(20, 0, 1, 4, 24, 2016);
$current_time = mktime(21, 0, 1, 4, 24, 2016);
$current_time = mktime(21, 30, 1, 4, 24, 2016);
$current_time = mktime(22, 0, 1, 4, 24, 2016);
$current_time = mktime(23, 0, 1, 4, 24, 2016);
$current_time = mktime(23, 30, 1, 4, 24, 2016);

echo date('d:m:Y H:i:s, N', $current_time) . "\n";

$dateParams = [
    'min' => date('i', $current_time),
    'hour' => date('H', $current_time),
    'dow' => date('N', $current_time)
];

if ($dateParams['hour'] >= 8)
    $next_time = $current_time + rand(3600, 5400);
elseif (($dateParams['dow'] == 6 && $dateParams['hour'] > 2 || $dateParams['dow'] == 7 && $dateParams['hour'] > 3) && $dateParams['hour'] < 12)
    $next_time = strtotime('12:00', $current_time) + rand(0, 1800);
elseif ($dateParams['dow'] >= 1 && $dateParams['dow'] <= 5 && $dateParams['hour'] > 1 && $dateParams['hour'] < 8)
    $next_time = strtotime('08:00', $current_time) + rand(0, 1800);
else
    $next_time = $current_time + rand(3600, 5400);

echo date('d:m:Y H:i:s, N', $next_time) . "\n";