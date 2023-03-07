<?php
  function fetchRadarImages($numOfImages) {
    include("vars.php");
    include("radar.gif.php");

    $radarUrl = "http://sigma.cptec.inpe.br/cgi-bin/mapserv?map=/extra2/sigma/www/webservice/webservice_dsa.map&mode=map&mapsize=699+664&layers=prec_inst13%20colorida13%20estados%20paises%20copyright2&mapext=-88.02+-46.5+-26.22+12.54";
    
    $jsonArray = array();
    $jsonArray["status"] = "ok";
    $jsonArray["images"] = array();

    $failureCount = 0;

    set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    if (0 === error_reporting()) {
      return false;
    }
      throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    try {
        
        // get the latest image
        if (copy($radarUrl, $radarLocalDir."latest.jpg")) {

          // there is a first image, check if the latest is different 
          if (file_exists($radarLocalDir."0_raw.jpg")) {
            
            $md5Latest = md5(file_get_contents($radarLocalDir."latest.jpg"));
            $md5Raw = md5(file_get_contents($radarLocalDir."0_raw.jpg"));

            // we have a new  image, move all other images up
            if ($md5Latest != $md5Raw) {

              for ($i=8; $i >= 0; $i--) {
                if (file_exists($radarLocalDir.$i."_raw.jpg")) {
                  rename($radarLocalDir.$i."_raw.jpg", $radarLocalDir.($i+1)."_raw.jpg");
                }
                if (file_exists($radarLocalDir.$i.".jpg")) {
                  rename($radarLocalDir.$i.".jpg", $radarLocalDir.($i+1).".jpg");
                }
              }
              
              if (rename($radarLocalDir."latest.jpg", $radarLocalDir."0_raw.jpg")) {
                prepareCroppedImage($radarLocalDir."0_raw.jpg", $radarLocalDir."0.jpg");
              }

            } 

          }

          // there is no first image, make it the first
          else {
            if (rename($radarLocalDir."latest.jpg", $radarLocalDir."0_raw.jpg")) {
              prepareCroppedImage($radarLocalDir."0_raw.jpg", $radarLocalDir."0.jpg");
            } 
          }

        }

      }
      catch(Exception $e) {
          $failureCount++;

          if ($failureCount >= 3) {
              $jsonArray["status"] = "fail";
          }
      }

      restore_error_handler();

      createGIF();
  }

  function prepareCroppedImage($rawFilename, $croppedFilename) {
      $imageRaw = imagecreatefromjpeg($rawFilename);
      $imageWidth = getimagesize($rawFilename)[0];
      $imageHeight = getimagesize($rawFilename)[1];

      $cropArray = array("x"=>200, "y"=>0, "width"=>$imageWidth-225, "height"=>$imageHeight);
      $imageCropped = imagecrop($imageRaw, $cropArray);

      imagejpeg($imageCropped, $croppedFilename, 100);
  }

?>
