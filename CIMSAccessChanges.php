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
print '<a href=".\index.php">Home</a> | <a href=".\CIMSAccessChanges.php" onclick="reloadPage()">Restart</a><hr>';
//if(isset($_POST['name'])){print $_POST['name']."<br><br>";}else{print "Post not set :(<br>";}
//ini_set('display_errors',1);
//error_reporting(E_ALL);
  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\CIMS Access Changes\\";//prod server
      }else{
         $target_dir = "CIMS Access Changes/";//dev server 
      }

$fileDataByDay = array();
$fileUnitsExistByDay = array();     
$fileNameForHumans = array();
$files1 = scandir($target_dir);
$dropDownListOfNames = array();

    foreach($files1 as $key=>$value){
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
            if(!in_array(trim($tempData[0]), $dropDownListOfNames)){$dropDownListOfNames[count($dropDownListOfNames)] = trim($tempData[0]);}
            //print $aline;
            if(trim($tempData[1]) != ""){
                $lookFor = $tempData[1];
                if(isset($_POST['name'])){
                    $lookFor = $aline;
                  if(!in_array($lookFor, $fileUnitsExistByDay[$arrayKeyName]) && (strtolower(trim($tempData[0])) == strtolower(trim($_POST['name'])))){$fileUnitsExistByDay[$arrayKeyName][count($fileUnitsExistByDay[$arrayKeyName])] = $lookFor;}
                }else{
                    if(!in_array($lookFor, $fileUnitsExistByDay[$arrayKeyName])){$fileUnitsExistByDay[$arrayKeyName][count($fileUnitsExistByDay[$arrayKeyName])] = $lookFor;}
                }                   
          }
       }
     }
    }
  }

/**
//output
$table = '<table border="1"><tr>';

foreach($fileNameForHumans as $key => $val){
    $table .= '<th>'.$val.'</th>';
}
$table .= '</tr><tr>';//close table header

$i = 1;//arrayKeyCounter
foreach($fileUnitsExistByDay as $key => $val){
    $table .= "<td valign=\"top\">";
    asort($val);

    foreach($val as $key2 => $val2){
        if(!in_array($val2, $fileUnitsExistByDay[$i])){    
            $table .= $val2."<br>";
        }
    }
    $i++;
    $table .= "</td>";
}

$table .= '</tr></table>';

print $table;
*/
//search option
asort($dropDownListOfNames);  
print '<form action="./CIMSAccessChanges.php" method="post" onsubmit="showDiv()">
    Search: <select name="name">';
foreach($dropDownListOfNames as $val){
    if(isset($_POST['name']) && $_POST['name'] == $val){
        print '<option value="'.$val.'" SELECTED>'.$val.'</option>';
    }else{
        print '<option value="'.$val.'">'.$val.'</option>';
    }
}
print '</select>    
<input type="submit" value="Engage!"><br><hr/>
</form>';   
    
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
            
            asort($fileUnitsExistByDay[$fileNameForHumans[$i-1]]);
            foreach($fileUnitsExistByDay[$fileNameForHumans[$i-1]] as $keys => $val2){
                if(!in_array($val2,$fileUnitsExistByDay[$val])){
                    print str_replace("|"," - ", $val2)." all access has been removed...";
                    $restored = "no more logs to review!";
                        for($x=$i+1; $x < count($fileNameForHumans);$x++){
                            $restored = "access was never restored!";
                            if(in_array($val2,$fileUnitsExistByDay[$fileNameForHumans[$x]])){
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

//var_dump($fileUnitsExistByDay[0]);

?>
    
</div>
<div id="loadingGif" style="display:none"><img src="./images/standby<?php print rand(1,10)?>.gif"></div>
</body>
<html>