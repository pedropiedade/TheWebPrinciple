<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header('Content-type: application/json');

require_once "weather.class.php";

$myWeather = new weather();
$weather = $myWeather->getWeather();
echo $weather;