<?php

  function parseInput($input) {
    $input = "".$input;
    if (strlen($input) > 0) {
      $input = "".(int)$input;
    }
    return $input;
  }

  function removeDecimal($value) {
    $pos = strrpos($value,'.');
    if ($pos === false) {
      return "".$value;
    }

    return "".substr("".$value, 0, $pos);
  }

  function translateWeatherCode($code) {
    switch($code) {

      // sunny
      case 1000:
      case "1000":
      case "clear":
        return "113";    

      // partly cloudy
      case 1003:
      case "1003":
      case "partlycloudy":
        return "116";
 
      // cloudy
      case 1006:
      case "1006":
      case "cloudy":
        return "119";
 
      // overcast
      case 1009:
      case "1009":
      case "mostlycloudy":
        return "122";
 
      // mist
      case 1030:
      case "1030":
        return "143";
 
      // patchy rain nearby
      case 1063:
      case "1063":
        return "176";
 
      // patchy snow nearby
      case 1066:
      case "1066":
        return "179";
 
      // patchy sleet nearby
      case 1069:
      case "1069":
        return "182";
 
      // patchy freezing drizzle nearby
      case 1072:
      case "1072":
        return "185";
 
      // thundery outbreaks nearby
      case 1087:
      case "1087":
        return "200";
 
      // blowing snow 
      case 1114:
      case "1114":
        return "227";
 
      // blizzard 
      case 1117:
      case "1117":
        return "230";
 
      // fog 
      case 1135:
      case "1135":
        return "248";
 
      // freezing fob 
      case 1147:
      case "1147":
        return "260";
 
      // patchy light drizzle 
      case 1150:
      case "1150":
        return "263";
 
      // light drizzle 
      case 1153:
      case "1153":
        return "266";
 
      // freezing drizzle
      case 1168:
      case "1168":
        return "281";
 
      // heavy freezing drizzle
      case 1171:
      case "1171":
        return "284";
 
      // patchy light rain
      case 1180:
      case "1180":
        return "293";
 
      // light rain
      case 1183:
      case "1183":
        return "296";
 
      // moderate rain at times
      case 1186:
      case "1186":
      case "rain":
      case "chancerain":
        return "299";
 
      // moderate rain 
      case 1189:
      case "1189":
        return "302";
 
      // heavy rain at times
      case 1192:
      case "1192":
        return "305";
 
      // heavy rain
      case 1195:
      case "1195":
        return "308";
 
      // light freezing rain
      case 1198:
      case "1198":
        return "311";
 
      // moderate or heavy freezing rain
      case 1201:
      case "1201":
        return "314";
 
      // light sleet 
      case 1204:
      case "1204":
        return "317";
 
      // moderate or heavy sleet
      case 1207:
      case "1207":
        return "320";
 
      // patchy light snow
      case 1210:
      case "1210":
        return "323";
 
      // light snow
      case 1213:
      case "1213":
        return "326";
 
      // patchy moderate snow
      case 1216:
      case "1216":
        return "329";
 
      // moderate snow
      case 1219:
      case "1219":
        return "332";
 
      // patchy heavy snow
      case 1222:
      case "1222":
        return "335";
 
      // heavy snow
      case 1225:
      case "1225":
        return "338";
 
      // ice pellets
      case 1237:
      case "1237":
        return "350";
 
      // light rain shower
      case 1240:
      case "1240":
        return "353";
 
      // moderate or heavy rain shower 
      case 1243:
      case "1243":
        return "356";
 
      // torrential rain shower
      case 1246:
      case "1246":
        return "359";
 
      // light sleet showers
      case 1249:
      case "1249":
        return "362";
 
      // moderate or heavy sleet showers
      case 1252:
      case "1252":
        return "365";
 
      // light snow showers
      case 1255:
      case "1255":
        return "368";
 
      // moderate or heavy snow showers 
      case 1258:
      case "1258":
        return "371";
 
      // light showers of ice pellets
      case 1261:
      case "1261":
        return "374";
 
      // moderate or heavy showers of ice pellets
      case 1264:
      case "1264":
        return "377";
 
      // patchy light rain in area with thunder
      case 1273:
      case "1273":
        return "386";
 
      // moderate or heavy rain in area with thunder 
      case 1276:
      case "1276":
        return "389";
 
      // patchy light snow in area with thunder
      case 1279:
      case "1279":
        return "392";
 
      // moderate or heavy snow in area with thunder
      case 1282:
      case "1282":
        return "395";
 
      default:
        return "0";
    }
  }

?>
