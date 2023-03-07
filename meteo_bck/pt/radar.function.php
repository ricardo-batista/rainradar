<?php
  function fetchRadarImages($numOfImages) {
    include("vars.php");
    include("radar.gif.php"); 

    $radarUrl = "http://www.ipma.pt/pt/otempo/obs.radar/";
    $radarResourcesUrl = "http://www.ipma.pt/resources.www/data/observacao/radar/imagens/";

    $jsonArray = array();
    $jsonArray["status"] = "ok";
    $jsonArray["images"] = array();

    $html = file_get_contents($radarUrl);
    $imgsStart = "onchange=\"javascript: processFileCombo(this);\"";

    $pos = strrpos($html, $imgsStart);
    if ($pos !== false) {
      $htmlImages = substr($html, $pos + strlen($imgsStart));

      $failureCount = 0;
      $n = 0;
      do {

        $imageUrlStart = "<option value=\"";
        $imageUrlEnd = "\">";

        $posImageUrlStart = strpos($htmlImages, $imageUrlStart);
        $posImageUrlEnd = strpos($htmlImages, $imageUrlEnd);

        if ($posImageUrlStart !== false && $posImageUrlEnd !== false) {

          $posImageUrlStart += strlen($imageUrlStart);

          $imageUrl = substr($htmlImages, $posImageUrlStart, $posImageUrlEnd-$posImageUrlStart);
          $htmlImages = substr($htmlImages, $posImageUrlEnd + strlen($imageUrlEnd));

          $time = "";
          $dateTime = "";
          $dateTimeEnd = "</option>";

          $posDateTimeEnd = strpos($htmlImages, $dateTimeEnd);

          if ($posDateTimeEnd !== false) {
            $dateTime = substr($htmlImages, 0, $posDateTimeEnd);

            $dateTimeSeparator = " ";
            $posTime = strpos($dateTime, $dateTimeSeparator);
            if ($posTime !== false) {
              $time = substr($dateTime, $posTime+strlen($dateTimeSeparator), strlen($dateTime));
            }
          }

          set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            if (0 === error_reporting()) {
              return false;
            }
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
          });

          try {
            $imageNameRaw = $n."_raw.jpg";
            $imageNameCropped = $n.".jpg";

            if (copy($radarResourcesUrl.$imageUrl, $radarLocalDir.$imageNameRaw)) {

              $imageRaw = imagecreatefromjpeg($radarLocalDir.$imageNameRaw);
              $imageWidth = getimagesize($radarLocalDir.$imageNameRaw)[0];
              $imageHeight = getimagesize($radarLocalDir.$imageNameRaw)[1];

              $cropArray = array("x"=>0, "y"=>0, "width"=>$imageWidth-121, "height"=>$imageHeight);
              $imageCropped = imagecrop($imageRaw, $cropArray);

              imagejpeg($imageCropped, $radarLocalDir.$imageNameCropped, 100);

              $imageArray = array();
              $imageArray["index"] = "".$n;
              $imageArray["time"] = $time;
              $imageArray["dateTime"] = $dateTime;
              $imageArray["imageName"] = $imageNameCropped;
              $imageArray["imageUrl"] = $imageUrl;

              $jsonArray["images"][] = $imageArray;
            }
          }
          catch(Exception $e) {
            $failureCount++;

            if ($failureCount >= 3) {
              $jsonArray["status"] = "fail";
              break;
            }
            else {
              continue;
            }
          }

          restore_error_handler();
  		  }

  		  $n++;

  	  } while ($n < $numOfImages);

      if ($jsonArray != null && $jsonArray["images"] != null && count($jsonArray) > 0 && $jsonArray["status"] == "ok") {
        file_put_contents($radarLocalDir."radar.json", json_encode($jsonArray));
      }
    }

    createGIF();
  }
?>
