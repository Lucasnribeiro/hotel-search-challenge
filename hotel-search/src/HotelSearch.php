<?php

namespace Search;
class HotelSearch {
    // Default API endpoints
    private static $api_endpoints   = [];

    private static $distanceCache   = [];
    private static $hotels          = [];


    function __construct(){
        self::$api_endpoints = [
            "https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_1.json",
            "https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_2.json"
        ];
    }

    // Configure API endpoints
    public static function configure($endpoints) {
        self::$api_endpoints = $endpoints;
    }

    /**
     *  Here we call the API endpoints, merge the data and pass it down into the sorting function.
     * 
     * @return array
     */
    public static function getHotels($latitude, $longitude, $orderby = "proximity") {
        $endpoints = self::$api_endpoints;

        // Retrieve hotel data from API endpoints
        foreach ($endpoints as $endpoint) {

            $response = file_get_contents($endpoint);

            if ($response !== false) {

                $hotelsData = json_decode($response, true);
                self::$hotels = array_merge(self::$hotels, $hotelsData['message']);

            } else {
                throw new \Exception("Failed to fetch hotel data from $endpoint.");
            }
        }

        // Sort the hotels based on latitude, longitude, and the orderby parameter
        $sortedHotels = self::sortHotels($latitude, $longitude, $orderby);
        return $sortedHotels;
    }

    /**
     *  This is the core function. It iterates over the returned data while also dealing
     *  with invalid fields, like wrong indexes for hotel names or hotels with no coordinates.
     * 
     *  @return array
     */
    private static function sortHotels($latitude, $longitude, $orderby) {
        // Iterate over hotels (backwards to remove invalid data without affecting loop)
        for ($index = count(self::$hotels) - 1; $index >= 0; $index--) {
            
            $hotel              = self::$hotels[$index];
            $correctData        = self::ensureCorrectData($hotel);
        
            if ($correctData) {
                // Update hotel data with corrected values
                self::$hotels[$index]         = $correctData;
                // Calculate and append the distance to the hotel
                self::$hotels[$index][]       = self::calculateDistance($latitude, $longitude, (float) $correctData[1], (float) $correctData[2]);
            } else {
                // Remove hotel with invalid data
                array_splice(self::$hotels, $index, 1);
            }
        }

        // Sort the hotels based on the specified orderby parameter
        if ($orderby == "proximity" || $orderby == "") {

            usort(self::$hotels, function($a, $b) {
                $epsilon = 0.00001; 
            
                $difference = $a[4] - $b[4];
                if (abs($difference) < $epsilon) {
                    return 0; // Numbers are practically equal
                } elseif ($difference > 0) {
                    return 1; // $a[4] is greater than $b[4]
                } else {
                    return -1; // $b[4] is greater than $a[4]
                }
            });    
        } 
        
        if ($orderby == "pricepernight") {

            usort(self::$hotels, function($a, $b) {
                return $a[3] - $b[3];
            });
        
        }

        return self::$hotels;
    }

    /**
     * This function is necessary to deal with data inconsistencies.
     * Currently it removes null values encountered on the index = 1 on some hotels,
     * Identifies invalid coordinates (currently if the values are either 0 or empty)
     * Also checks the index = 1 is a latitude, if not, takes the string and merge it to the name of the hotel
     * 
     * @return mixed
     */
    private static function ensureCorrectData($hotel) {
        // Remove invalid latitude or longitude values
        if ($hotel[1] === null) {

            array_splice($hotel, 1, 1);

        }

        // Check for invalid latitude or longitude values
        if ($hotel[1] === "0" || $hotel[2] === "0" || $hotel[1] === "" || $hotel[2] === "") {

            return false;

        }

        // Check if the latitude value matches the expected pattern
        if (preg_match('/^-?([1-8]?[0-9]\.\d+|90\.[0]+)$/', $hotel[1])) {

            return $hotel;

        } else {
            // Concatenate the hotel name with the incorrect latitude value
            $hotel[0] = "{$hotel[0]} {$hotel[1]}";
            // Remove the incorrect latitude value from the hotel data
            array_splice($hotel, 1, 1);
            return $hotel;

        }
    }


    /**
     * This function calculates the distance using the Haversine formula between two coordinates.
     * 
     * @return float
     */
    private static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $cacheKey = "{$lat1}_{$lon1}_{$lat2}_{$lon2}";

        if (isset(self::$distanceCache[$cacheKey])) {
            return self::$distanceCache[$cacheKey];
        }

        $earthRadius = 6371; // Radius of the Earth in kilometers

        // Convert latitude and longitude to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Calculate the differences between coordinates
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Apply the Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance
        $distance = $earthRadius * $c;

        self::$distanceCache[$cacheKey] = $distance;

        return $distance;
    }
}