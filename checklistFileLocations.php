<?php
session_start();

//locations of resources
  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\";//prod server
        $mapLocation = "maps\\";
        $dataLocation = "data\\";
        $backupLocation = "backup\\";
        $pirate_file = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\checklist\pirates\bank";//prod server
        $pirate_list = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\checklist\pirates\ListOfPirates.txt";//prod server
        $calendarFolder = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\checklist\calendars\\";
        $calendarFolderForHttp = "file://canada.compassgroup.corp/lo/Accounting%20SPDST/checklist/calendars/";
        $target_root = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";
      }else{
         $target_dir = "checklist/";//dev server 
         $mapLocation = "maps/";
         $dataLocation = "data/";
         $backupLocation = "backup/";
         $pirate_file = "checklist/pirates/bank";//dev server 
         $pirate_list = "checklist/pirates/ListOfPirates.txt";//dev server
         $calendarFolder = "checklist/calendars/";
         $calendarFolderForHttp = $calendarFolder;
         $target_root = "/";
         
      }

      
/////Setup the Pirate Coin Game/////  
//priate game start
//setup simple encryption
// Store the cipher method
$ciphering = "AES-128-CTR";
// Use OpenSSl Encryption method
$iv_length = openssl_cipher_iv_length($ciphering);
$options = 0;      
// Non-NULL Initialization Vector for encryption
$encryption_iv = '1234567891011121';
// Store the encryption key
$encryption_key = "MelWillNeverFigureThisOut";  
// Non-NULL Initialization Vector for decryption
$decryption_iv = '1234567891011121';
// Store the decryption key
$decryption_key = "MelWillNeverFigureThisOut";

//get our list of 
 $fn = fopen($pirate_list,"r");
 $players = array();//for the pirate coin game
  while(! feof($fn))  {
	$result = trim(fgets($fn));
        $players[$result] = "";//in this array the player name is the key, we will update the null in the code below
  }
  //var_dump($players);

  fclose($fn);
  unset($fn);//clean up mem
  
//now lets see what is in the pirate bank
$fn = fopen($pirate_file,"r");
$piratesLastMovement = array();
$piratePersonalMessage = array();
//$players = array();

while(! feof($fn))  {
	$result = fgets($fn);
        $encryption = $result;
	$playerLineData = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
        //debug var print
        $explodePlayerLineData = explode("|",$playerLineData);
        if(count($explodePlayerLineData) > 1){
            //debug echo
            //echo $explodePlayerLineData[1].' - '.$explodePlayerLineData[0]."<br>";
            if(array_key_exists($explodePlayerLineData[1],$players)){
                //print "<BR>In if Array Key";//debug print
                $players[$explodePlayerLineData[1]] = $explodePlayerLineData[0];
                $piratesLastMovement[$explodePlayerLineData[1]] = $explodePlayerLineData[2];
                $piratePersonalMessage[$explodePlayerLineData[1]] = $explodePlayerLineData[3];
            }
        }else{//if the file is hacked and the pipe is gone it will hit this block of code
            //print "Line hacked!<br>";
        }
        
}

fclose($fn);
unset($fn);//clean up mem
//var_dump($players);