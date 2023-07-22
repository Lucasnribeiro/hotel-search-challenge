<?php 

namespace Api;

require_once __DIR__ . '/vendor/autoload.php';

use Search\HotelSearch;

HotelSearch::configure([
    "https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_1.json",
    "https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_2.json"
]);

$latitude     = isset($_GET['latitude']) ? $_GET['latitude'] : '38.718515'; 
$longitude    = isset($_GET['longitude']) ? $_GET['longitude'] : '-9.144147'; 
$orderby      = isset($_GET['orderby']) ? $_GET['orderby'] : '';

$results = HotelSearch::getHotels($latitude, $longitude, $orderby);

$hotels = [];
foreach ($results as $hotel) {
    $hotelData = [
        'name' => $hotel[0],
        'distance' => floatval($hotel[4]),
        'price' => floatval($hotel[3])
    ];
    $hotels[] = $hotelData;
}

$json = json_encode($hotels);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");

echo $json;