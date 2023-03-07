<?php
function parseInput($input) {
    $input = "".$input;
    if (strlen($input) > 0) {
      $input = "".(int)$input;
    }
    return $input;
  }


  echo parseInput("15.3")."\n";
  echo parseInput("-15.3")."\n";
  echo parseInput("a15.3")."\n";
  echo parseInput(15.3);
?>
