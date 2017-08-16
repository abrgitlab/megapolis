<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.08.17
 * Time: 13:30
 */

 function regHandler($cert, $token, $murl)
 {
     $url = "https://api.telegram.org/bot" . $token . "/setWebhook";
     $ch = curl_init();
     $optArray = array(
         CURLOPT_URL => $url,
         CURLOPT_POST => true,
         CURLOPT_SAFE_UPLOAD => false,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POSTFIELDS => array('url' => $murl, 'certificate' => '@' . realpath($cert))
     );
     curl_setopt_array($ch, $optArray);

     $result = curl_exec($ch);
     echo "<pre>";
     print_r($result);
     echo "</pre>";
     curl_close($ch);
 }

 $token = '411382774:AAHjTH-9dxBfecr8RTd4anfIFWzcSmy4xMU';
 $path = 'ssl/wildcard.abr-daemon.ru.pem';
 $handlerurl = 'https://mega.abr-daemon.ru/'; // ИЗМЕНИТЕ ССЫЛКУ

 regHandler($path, $token, $handlerurl);
?>