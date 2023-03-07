<?php
    $DEVELOPMENT = false;

    if ($DEVELOPMENT) {
        $weatherLocalDir = "/Users/ricardo/Developer/www/rainradar/backend/br/weather/";
        $radarLocalDir = "/Users/ricardo/Developer/www/rainradar/backend/br/radar/";
    } else {
        $weatherLocalDir = "/var/www/meteo/br/weather/";
        $radarLocalDir = "/var/www/meteo/br/radar/";
    }
?>
