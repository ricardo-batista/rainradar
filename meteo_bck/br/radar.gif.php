<?php
  function createGIF() {
    $SHOW_LOGS = false;
    $basePath = "/var/www/meteo/";
    $countryPath = "br/";

    require $basePath."common/animgif.php";

    $radarImagesPath = $basePath.$countryPath."radar";
    $filePath = $basePath.$countryPath."gif/radar.gif";

    // get the list of all files in the radar dir
    $files = scandir($radarImagesPath);

    // remove dots
    $files = array_diff($files, array('..', '.','radar.json'));

    // remove raw files
    $rawFiles = array();
    foreach ($files as $file) {
      if (strpos($file, 'raw') !== false) {
        $rawFiles[] = $file;
      }
    }
    $files = array_values(array_diff($files, $rawFiles));

    $frameDurations = array();
    for ($i = 0; $i < count($files); $i++) {

      // add dir to all the jpg files
      $files[$i] = $radarImagesPath."/".$files[$i];

      // duration of each frame
      if ($i < count($files) - 1 ) {
        $frameDurations[] = 50;
      } else {
        $frameDurations[] = 125;
      }

    }

    if ($SHOW_LOGS) {
      print_r($files);
      print_r($frameDurations);
    }

    $anim = new GifCreator\AnimGif();
    $anim->create($files, $frameDurations);

/*
    $gif = $anim->get();
    header("Content-type: image/gif");
    echo $gif;
    exit;
*/
    if (file_exists($filePath)) {
      unlink($filePath);  
    }
    
    $anim->save($filePath);
  }
?>
