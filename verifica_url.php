<?php

//rudi 7/10/2015 jsonp crashou no mobile nesse ajax, passando pra cors
header("Access-Control-Allow-Origin: *");

$url = $_GET['url'] . "/../testes/index.html";

$ch = @curl_init($url);
@curl_setopt($ch, CURLOPT_HEADER, TRUE);
@curl_setopt($ch, CURLOPT_NOBODY, TRUE);
@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
@curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
@curl_setopt($ch, CURLOPT_TIMEOUT, 6);
$status = array();
preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch), $status);

if ($status[1] == 200) {
    $resp = 'T';
} else {
    $resp = 'F';
}

if ($_GET['callback']) {
    $e = $_GET['callback'] . "(" . json_encode($resp) . ");";
} else {
    $e = json_encode($resp);
}

echo $e;
