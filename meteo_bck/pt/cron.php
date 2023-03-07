<?php
  $task = "";
  if (isset($_REQUEST["task"])) {
    $task = $_REQUEST["task"];
  }
  else if (isset($argv)) {
    $task = $argv[1];
  }

  if ($task == "radar") {
    include("radar.function.php");
    fetchRadarImages(7);
  }
  else if ($task == "weather") {
    include("weather.function.php");
    fetchWeather();
  }
?>
