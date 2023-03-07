<?php

  function fetchWeather($source) {
     if ($source == 'wunderground') {
        fetchWeatherWunderground();
     }
  }

  // http://api.wunderground.com/api/db0dd68e9586227c/geolookup/conditions/q/-22.902778,-43.207778.json
  function fetchWeatherWunderground() {
    $SHOW_LOGS = false;

    include("vars.php");
    include("functions.php");
   
    $key = "db0dd68e9586227c";

    $citiesSuffix = ",Brasil";
    $citiesArray = array( 
      "São Paulo" => array("-23.547778", "-46.635833"),
      "Rio de Janeiro" => array("-22.902778", "-43.207778"),
      "Belo Horizonte" => array("-19.816944", "-43.955833"),
      "Recife" => array("-8.053889", "-34.880833"),
      "Brasília" => array("-15.793889", "-47.882778"),
      "Porto Alegre" => array("-30.032778", "-51.23"),
      "Salvador" => array("-12.971111", "-38.510833"),
      "Fortaleza" => array("-3.718333", "-38.542778"),
      "Curitiba" => array("-25.429722", "-49.271944"),
      "Goiania" => array("-16.666667", "-49.25"),
      "Belém" => array("-1.455833", "-48.503889"),
      "Manaus" => array("-3.1", "-60.016667"),
      "Campinas" => array("-22.905833", "-47.060833"),
      "Vitória" => array("-20.318889", "-40.337778"),
      "Santos" => array("-23.936989", "-46.325094"),
      "São José Campos" => array("-23.178889", "-45.886944"),
      "São Luís" => array("-2.53", "-44.302778"),
      "Natal" => array("-5.795", "-35.208889"),
      "Maceió" => array("-9.665833", "-35.735"),
      "João Pessoa" => array("-7.083333", "-34.833333")
    );

    $i = 0;
    foreach ($citiesArray as $cityName => $cityLatLon) {

        if ($SHOW_LOGS) {
          echo $cityName."\n";
        }

        $filename = $weatherLocalDir ."weather.".$i.".json";
        $weatherData = array();
        $forecastData = array();
        $hourlyData = array();

        // current conditions     
        $json_string = file_get_contents("http://api.wunderground.com/api/".$key."/geolookup/conditions/q/".$cityLatLon[0].",".$cityLatLon[1].".json");
        $parsed_json = json_decode($json_string);
        $location = $parsed_json->{'location'}->{'city'};

        $weatherData['temperature'] = parseInput($parsed_json->{'current_observation'}->{'temp_c'});
        $weatherData['feels_like'] = "".$parsed_json->{'current_observation'}->{'feelslike_c'};
        $weatherData['wind_speed'] = "".$parsed_json->{'current_observation'}->{'wind_kph'};
        $weatherData['wind_degree'] = "".$parsed_json->{'current_observation'}->{'wind_degrees'};
        $weatherData['humidity'] = str_replace("%","", "".$parsed_json->{'current_observation'}->{'relative_humidity'});
        $weatherData['visibility'] = "".$parsed_json->{'current_observation'}->{'visibility_km'};
        $weatherData['pressure'] = "".$parsed_json->{'current_observation'}->{'pressure_mb'};
        $weatherData['code'] = translateWeatherCode($parsed_json->{'current_observation'}->{'icon'});
        $weatherData['city'] = $cityName.", Brasil";


        // forecast
        $json_string = file_get_contents("http://api.wunderground.com/api/".$key."/geolookup/forecast/q/".$cityLatLon[0].",".$cityLatLon[1].".json");
        $parsed_json = json_decode($json_string);
        
        $forecastDayArray = $parsed_json->{'forecast'}->{'simpleforecast'}->{'forecastday'};
        foreach ($forecastDayArray as $k => $forecastDay) {

            $day = $forecastDay->{'date'}->{'day'};
            $month = $forecastDay->{'date'}->{'month'};
            $year = $forecastDay->{'date'}->{'year'};
            $rainMM = $forecastDay->{'qpf_allday'}->{'mm'};

            $forecast['date'] = "${year}-${month}-${day}";
            $forecast['max'] = parseInput($forecastDay->{'high'}->{'celsius'});
            $forecast['min'] = parseInput($forecastDay->{'low'}->{'celsius'});
            $forecast['sunrise'] = "";
            $forecast['sunset'] = "";
            $forecast['moonrise'] = "";
            $forecast['moonset'] = "";
            $forecast['rain'] = "".$rainMM;
            $forecast['rain_unit'] = " mm";
            $forecast['code'] = translateWeatherCode($forecastDay->{'icon'});
            $forecast['uvIndex'] = "";

            $forecastData[] = $forecast;
        }
        

        // hourly
        $json_string = file_get_contents("http://api.wunderground.com/api/".$key."/geolookup/hourly/q/".$cityLatLon[0].",".$cityLatLon[1].".json");
        $parsed_json = json_decode($json_string);

        $h = 0;
        $hMax = 24;

        $hourlyForecastArray = $parsed_json->{'hourly_forecast'};
        foreach ($hourlyForecastArray as $k => $hourlyForecast) {

            $hour = $hourlyForecast->{'FCTTIME'}->{'hour_padded'};
            $minute = $hourlyForecast->{'FCTTIME'}->{'min'};

            $hourly['hour'] = "${hour}:${minute}";
            $hourly['temperature'] = parseInput($hourlyForecast->{'temp'}->{'metric'});
            $hourly['wind_speed'] = $hourlyForecast->{'wspd'}->{'metric'};
            $hourly['wind_degree'] = $hourlyForecast->{'wdir'}->{'degrees'};
            $hourly['code'] = translateWeatherCode($hourlyForecast->{'icon'});
            $hourly['rain'] = $hourlyForecast->{'qpf'}->{'metric'};
            $hourly['rain_unit'] = " mm";
            

            $hourlyData[] = $hourly;
            $h++;

            if ($h >= $hMax) {
                break;
            }
        }

        // write json
        $weatherData["forecast"] = $forecastData;
        $weatherData["hourly"] = $hourlyData;
        $weatherJsonData = array('data' => $weatherData);

        file_put_contents($filename, json_encode($weatherJsonData));

        $i++;
        sleep(30); // less than 10 calls per minute, we're making 2 calls per city
    }
  }

?>
