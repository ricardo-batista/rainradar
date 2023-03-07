<?php
    $DEVELOPMENT = false;

    if ($DEVELOPMENT) {
        $weatherLocalDir = "/Users/ricardo/Developer/www/rainradar/backend/pt/weather/";
        $radarLocalDir = "/Users/ricardo/Developer/www/rainradar/backend/pt/radar/";
    } else {
        $weatherLocalDir = "/var/www/meteo/pt/weather/";
        $radarLocalDir = "/var/www/meteo/pt/radar/";
    }
?>
