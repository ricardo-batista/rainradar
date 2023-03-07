<?php

  // https://api.apixu.com/v1/forecast.json?key=ff3f3cda5a9f4af78ef84228170606&q=Paris
  function fetchWeather() {
    include("vars.php");
    include("functions.php");

    $DEBUG = false;

    $weatherUrl = "https://api.apixu.com/v1/forecast.json";
    $historyUrl = "https://api.apixu.com/v1/history.json";

    $paramNumOfDays = "days";
    $paramKey = "key";
    $paramQuery = "q";
    $paramFormat = "format";
    $paramInterval = "tp";
    $paramDate = "dt";

    $numOfDays = "8";
    //$key = "ff3f3cda5a9f4af78ef84228170606";
    $key = "17867f89a61b457b910104052182202";
    $format = "json";

    $citiesArray = array("Aveiro,Portugal", "Beja,Portugal", "Braga,Portugal", "Braganca,Portugal",
        "Castelo Branco,Portugal", "Coimbra,Portugal", "Evora,Portugal", "Faro,Portugal",
        "Guarda,Portugal", "Leiria,Portugal", "Lisboa,Portugal", "Portalegre,Portugal",
        "Porto,Portugal", "Santarem,Portugal", "Setubal+Portugal", "Viana do Castelo,Portugal",
        "Vila Real,Portugal", "Viseu,Portugal", "Funchal,Portugal", "Ponta Delgada,Portugal",
        "Caldas da Rainha,Portugal");

    foreach ($citiesArray as $k => $value) {

      if ($DEBUG && $k != 20) {
        continue;
      }

      $filename = $weatherLocalDir ."weather.".$k.".json";  
      $weatherData = array();

      // get current weather conditions
      $url = $weatherUrl ."?".
        $paramKey ."=". $key ."&". 
        $paramQuery ."=". urlencode($value) ."&".
        $paramNumOfDays ."=". $numOfDays;
      $jsonData = file_get_contents($url);

      if ($DEBUG) {
        echo $url."\n";
      }

      if ($jsonData != null) {

        $data = json_decode($jsonData, true);

        if ($data != null) {

          $currentCondition = $data['current'];
          if ($currentCondition != null) {
              
              $temperature = "";
              if (array_key_exists('temp_c', $currentCondition)) {
                $temperature = $currentCondition['temp_c'];
              } else if (array_key_exists('temp_f', $currentCondition)) {
                $temperature = $currentCondition['temp_f'] ."F";
              }
              $weatherData['temperature'] = removeDecimal($temperature);

              $feelsLike = "";
              if (array_key_exists('feelslike_c', $currentCondition)) {
                $feelsLike = $currentCondition['feelslike_c'];
              } else if (array_key_exists('feelslike_f', $currentCondition)) {
                $feelsLike = $currentCondition['feelslike_f'];
              }
              $weatherData['feels_like'] = removeDecimal($feelsLike);

              $windSpeed = "";
              if (array_key_exists('wind_kph', $currentCondition)) {
                $windSpeed = $currentCondition['wind_kph'];
              }
              $weatherData['wind_speed'] = "".$windSpeed;

              $windDegree = "";
              if (array_key_exists('wind_degree', $currentCondition)) {
                $windDegree = $currentCondition['wind_degree'];
              }
              $weatherData['wind_degree'] = "".$windDegree;
              
              $humidity = "";
              if (array_key_exists('humidity', $currentCondition)) {
                $humidity = $currentCondition['humidity'];
              }
              $weatherData['humidity'] = "".$humidity;

              $visibility = "";
              if (array_key_exists('vis_km', $currentCondition)) {
                $visibility = $currentCondition['vis_km'];
              }
              $weatherData['visibility'] = "".$visibility;

              $pressure = "";
              if (array_key_exists('pressure_mb', $currentCondition)) {
                $pressure = $currentCondition['pressure_mb'];
              }
              $weatherData['pressure'] = "".$pressure;

              $weatherCode = "0";
              $condition = $currentCondition['condition'];
              if ($condition != null) {
                $weatherCode = $condition['code'];
              }
              $weatherData['code'] = translateWeatherCode($weatherCode);
          }

          $request = $data['location'];
          if ($request != null) {
              $city = $request['name'];
              $weatherData['city'] = $city;
          }

          $forecastDataArray = array();
          $forecastDataHourlyArray = array();
          $forecastContainer = $data['forecast'];
          if ($forecastContainer != null) {
  
            $forecastArray = $forecastContainer['forecastday'];
            if ($forecastArray != null) {

              foreach ($forecastArray as $fKey => $fValue) {
                $forecastDate = $fValue['date'];
                $forecastData['date'] = "".$forecastDate;

                date_default_timezone_set('Europe/Lisbon');
                $todayDate = date('Y-m-d', time());
                $todayHour = date('H', time());

                $isToday = $forecastDate == $todayDate;
                
                $chanceOfRain = "0";
                $weatherCode = "0";

                $dayForecast = $fValue['day'];
                if ($dayForecast != null) {

                  $forecastMaxTemperature = $dayForecast['maxtemp_c'];
                  $forecastData['max'] = removeDecimal($forecastMaxTemperature);

                  $forecastMinTemperature = $dayForecast['mintemp_c'];
                  $forecastData['min'] = removeDecimal($forecastMinTemperature);

                  $chanceOfRain = $dayForecast['totalprecip_mm'];

                  // convert mm to %
                  if ($chanceOfRain > 1) {
                    $chanceOfRain = 100;
                  } else {
                    $chanceOfRain = $chanceOfRain * 100;
                  }

                  $conditionForecast = $dayForecast['condition'];
                  if ($conditionForecast != null) {
                    $weatherCode = $conditionForecast['code'];
                  }
                }

                $forecastData['rain'] = "".$chanceOfRain;
                $forecastData['rain_unit'] = '%';
                $forecastData['code'] = translateWeatherCode($weatherCode);

                $forecastSunrise = "";
                $forecastSunset = "";
                $forecastMoonrise = "";
                $forecastMoonset = "";
                $forecastAstronomy = $fValue['astro'];
                if ($forecastAstronomy != null) {
                  $forecastSunrise = $forecastAstronomy['sunrise'];
                  $forecastSunset = $forecastAstronomy['sunset'];
                  $forecastMoonrise = $forecastAstronomy['moonrise'];
                  $forecastMoonset = $forecastAstronomy['moonset'];
                }
                $forecastData['sunrise'] = "".$forecastSunrise;
                $forecastData['sunset'] = "".$forecastSunset;
                $forecastData['moonrise'] = "".$forecastMoonrise;
                $forecastData['moonset'] = "".$forecastMoonset;

                $forecastData['uvIndex'] = ""; // no data anymore

                $forecastDataArray[] = $forecastData;
              }

              $weatherData['forecast'] = $forecastDataArray;
              $weatherData['hourly'] = $forecastDataHourlyArray;
            }
          }
        }
      }

      // get history weather (hourly conditions)
      $url = $historyUrl ."?".
        $paramKey ."=". $key ."&". 
        $paramQuery ."=". urlencode($value) ."&".
        $paramDate ."=". date("Y-m-d");
      $jsonData = file_get_contents($url);

      if ($DEBUG) {
        echo $url."\n";
      }

      if ($jsonData != null) {

        $data = json_decode($jsonData, true);

        if ($data != null) {

          $forecastDataHourlyArray = array();
          $forecastContainer = $data['forecast'];
          if ($forecastContainer != null) {
  
            $forecastArray = $forecastContainer['forecastday'];
            if ($forecastArray != null) {

              foreach ($forecastArray as $fKey => $fValue) {
                $forecastDate = $fValue['date'];
                $forecastData['date'] = "".$forecastDate;

                date_default_timezone_set('Europe/Lisbon');
                $todayDate = date('Y-m-d', time());
                $todayHour = date('H', time());

                $isToday = $forecastDate == $todayDate;

                $chanceOfRain = "0";
                $weatherCode = "0";

                 $hourlyArray = array();
                 if (array_key_exists("hour", $fValue)) {
                   $hourlyArray = $fValue['hour'];
                 }

                $numItems = count($hourlyArray);
                $i = 0;

                foreach ($hourlyArray as $hourlyKey => $hourlyValue) {

                  $isLastItem = ++$i === $numItems;

                  $hourlyTimeArray = explode(' ', $hourlyValue['time']);
                  $hourlyHour = explode(':', $hourlyTimeArray[1])[0];
                  $hourlyMinute = explode(':', $hourlyTimeArray[1])[1];

                  // for today's values, ignore past times (except last one)
                  if ($isToday && !$isLastItem && $todayHour > $hourlyHour) {
                    continue;
                  }
                  
                  $hourlyChanceOfRain = $hourlyValue['precip_mm'];

                  // convert mm to %
                  if ($hourlyChanceOfRain > 1) {
                    $hourlyChanceOfRain = 100;
                  } else {
                    $hourlyChanceOfRain = $hourlyChanceOfRain * 100;
                  }

                  if ($hourlyChanceOfRain > $chanceOfRain) {
                    $chanceOfRain = "".$hourlyChanceOfRain;
                  }

                  $condition = $hourlyValue['condition'];
                  if ($condition != null) {
                    if ($hourlyHour == "12") {
                      $weatherCode = $condition['code'];
                    } else if ($weatherCode == "0") {
                      $weatherCode = $condition['code'];
                    }
                  }

                  if (count($forecastDataHourlyArray) <= 8) {
                    $forecastDataHourly['hour'] = "".$hourlyHour. ":" .$hourlyMinute;
                    $forecastDataHourly['temperature'] = removeDecimal($hourlyValue['temp_c']);
                    $forecastDataHourly['wind_speed'] = "".$hourlyValue['wind_kph'];
                    $forecastDataHourly['wind_degree'] = "".$hourlyValue['wind_degree'];
                    $condition = $hourlyValue['condition'];
                    if ($condition != null) {
                      $forecastDataHourly['code'] = translateWeatherCode($condition['code']);
                    }

                    // convert mm to %
                    $forecastDataHourlyRain = $hourlyValue['precip_mm'];
                    if ($forecastDataHourlyRain > 1) {
                      $forecastDataHourlyRain = 100;
                    } else {
                      $forecastDataHourlyRain = $forecastDataHourlyRain * 100;
                    }

                    $forecastDataHourly['rain'] = "".$forecastDataHourlyRain;
                    $forecastDataHourly['rain_unit'] = '%';
                    $forecastDataHourlyArray[] = $forecastDataHourly;
                  }
                }

              }

              $weatherData['hourly'] = $forecastDataHourlyArray;
            }
          }
        }

        $weatherJsonData = array('data' => $weatherData);

        file_put_contents($filename, json_encode($weatherJsonData));
      }

      sleep(1);
    }
  }

?>
