<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Special Projects - SPDST</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="icon" href="./favicon.png">

</style>

<script>


function showDiv() {
  document.getElementById('display').style.display = "none";
  document.getElementById('loadingGif').style.display = "block";
  form.submit();
}


function reloadPage() {
  document.getElementById('display').style.display = "none";
  document.getElementById('loadingGif').style.display = "block";
}

</script>
</head>

<body>

<?php
print '<div id="display">';
print '<a href=".\index.php">Home</a> | <a href=".\JDEVendorChanges.php" onclick="reloadPage()">Restart</a><hr>';
//if(isset($_POST['name'])){print $_POST['name']."<br><br>";}else{print "Post not set :(<br>";}
//ini_set('display_errors',1);
//error_reporting(E_ALL);
  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\JDE Vendor Changes\\";//prod server
      }else{
         $target_dir = "JDE Vendor Changes/";//dev server JDE Vendor Changes/
      }

$fileDataByDay = array();
$fileVendorsExistByDay = array();     
$fileNameForHumans = array();
$files1 = scandir($target_dir);


    foreach($files1 as $key=>$value){
        //print $value;
        $tempFileNameForHumans = explode("_",$value);
        if(trim($tempFileNameForHumans[1]) != ""){
            $fileNameForHumans[count($fileNameForHumans)] = substr($tempFileNameForHumans[1],4,2)."/".substr($tempFileNameForHumans[1],6,2)."/".substr($tempFileNameForHumans[1],0,4);
            $arrayKeyName = substr($tempFileNameForHumans[1],4,2)."/".substr($tempFileNameForHumans[1],6,2)."/".substr($tempFileNameForHumans[1],0,4);
            //print $value;
    $file = fopen($target_dir.$value, "r");
    if ($file) { 
      while (!feof($file)) {
            $aline = fgets($file);
            $tempData = explode("|", $aline);
            //print $aline;
            if(trim($tempData[1]) != "" && trim($tempData[12] == 0)){
                    $lookFor = "Vendor #".$tempData[0]." (".$tempData[1].")";
                    if(!in_array($lookFor, $fileVendorsExistByDay[$arrayKeyName])){$fileVendorsExistByDay[$arrayKeyName][count($fileVendorsExistByDay[$arrayKeyName])] = $lookFor;}                 
          }
       }
     }
    }
  }


//main display  
$i = 0;

foreach($fileNameForHumans as $key => $val){
    $nothingFound = "<i>...nothing to note in the logs...</i>";
    switch($i){
        case 0:
         print "<i>".$val." is your base file, nothing to report here.</i>";
            $nothingFound = "";
        break;

        default:
           print "<b>".$val."</b><br>";
            
            asort($fileVendorsExistByDay[$fileNameForHumans[$i-1]]);
            foreach($fileVendorsExistByDay[$fileNameForHumans[$i-1]] as $keys => $val2){
                if(!in_array($val2,$fileVendorsExistByDay[$val])){
                    print str_replace("|"," - ", $val2)." has been disabled...";
                    $restored = "no more logs to review!";
                        for($x=$i+1; $x < count($fileNameForHumans);$x++){
                            $restored = "access was never restored!";
                            if(in_array($val2,$fileVendorsExistByDay[$fileNameForHumans[$x]])){
                                $restored = 'access was restored '.$fileNameForHumans[$x];
                                $x = count($fileNameForHumans);
                            }
                        }
                    print $restored;
                    $nothingFound = "";
                    print "<br>";
                }
            }
        break;    
    }
        
    print $nothingFound."<hr/>";
    $i++;
}

//var_dump($fileVendorsExistByDay[0]);

?>
    
</div>
<div id="loadingGif" style="display:none"><img src="./images/standby<?php print rand(1,10)?>.gif"></div>
</body>
<html>