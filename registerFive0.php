<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Special Projects - SPDST</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="icon" href="./favicon.png">

  
<script>
    function myCopy(regID) {
      /* Get the text field */
      var copyText = document.getElementById(regID);

      /* Select the text field */
      copyText.select();
      document.execCommand("copy");

      /* Alert the copied text */
      alert("Message Copied!");
    }
</script>

<style>
.copy-me {
    opacity:0;
    width:0.1px;
    height:0.1px; 
}
</style>
</head>

<body>
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\registerFive0\\";//prod server
        $target_root = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";//prod server
      }else{
         $target_dir = "registerFive0/";//dev server 
         $target_root = "";//dev server 
      }

//write new mappings to the config file
if(isset($_GET["seen"])){
    if(trim($_GET["seen"]) != "" && trim($_GET["regType"]) != ""){
    $myfile = $target_dir."registerMap.txt";
    //echo file_exists($myfile);
    $mapping = trim($_GET["seen"])."|".trim($_GET["regType"])."\r\n";
    file_put_contents($myfile,$mapping, FILE_APPEND | LOCK_EX);
    header('Location: registerFive0.php?mode=unknown');
    }
}      
      
      
      
      
$orbitTablesSheet = 'Orbit_Prod_Archive_Register_5_0_2022.xlsx';
$registerMap = 'registerMap.txt';
$cimsUnitFile = '';

$files1 = scandir($target_dir);
    /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
        
        foreach($files1 as $key=>$file){
            if(strpos($file,'UNIT') !== false){
                $cimsUnitFile = $file;
            }
        }

$activeUnits = array();//if the unit is in the CIMS files it is alive in JDE        
//loop the CIMS file to get a list of active units.
                $thisFile = fopen($target_dir.$cimsUnitFile, "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                     $field = explode("|", $aline);
                     if(count($field) > 1){
                         $activeUnits[count($activeUnits)] = $field[0];
                     }
                     //print $field[0].'<br>';
                    }//end while
                }
                fclose($thisFile); 
                
$registerMapping = array();
//loop the Register file to get a list of registers and Orbit DB IDs for them.
                $thisFile = fopen($target_dir.$registerMap, "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                     $field = explode("|", $aline);
                     if(count($field) > 1){
                      $registerMapping[$field[0]] = $field[1];
                     }
                     //print $field[0].' = '.$field[1].'<br>';
                    }//end while
                }
                fclose($thisFile); 
/*
 A = UnitName
 B = UnitNumber
 C = Keyer
 D = SalesDate
 E = RegNameInSaleTable
 F = RegNameInRegTable
 G = RegType
 H = NetSales
 I = StatusIs
 J = SetupBy
 K = SetupDate
 L = UniqueID
 M = SectorName
 N = SaleModule
 */                
set_time_limit(5000); 
$registerData = array();    
$registerDataLW = array();
//now we scan the Orbit Excel Sheet and go fishing for register info.                
                $inputFileName = $target_dir.$orbitTablesSheet;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //each row
                        $registerData[count($registerData)] = array(
                            'UnitName' => trim($val["A"]),
                            'UnitNumber' => trim($val["B"]),
                            'Keyer' => trim($val["C"]),
                            'SalesDate' => trim($val["D"]),
                            'RegNameInSaleTable' => trim($val["E"]),
                            'RegNameInRegTable' => trim($val["F"]),
                            'RegType' => trim($val["G"]),
                            'NetSales' => trim($val["H"]),
                            'StatusIs' => trim($val["I"]),
                            'SetupBy' => trim($val["J"]),
                            'SetupDate' => trim($val["K"]),
                            'UniqueID' => trim($val["L"]),
                            'SectorName' => trim($val["M"]),
                            'SaleModule' => trim($val["N"])
                        );
                        
                        //last write wins version
                        $registerDataLW[trim($val["L"])] = array(
                            'UnitName' => trim($val["A"]),
                            'UnitNumber' => trim($val["B"]),
                            'Keyer' => trim($val["C"]),
                            'SalesDate' => trim($val["D"]),
                            'RegNameInSaleTable' => trim($val["E"]),
                            'RegNameInRegTable' => trim($val["F"]),
                            'RegType' => trim($val["G"]),
                            'NetSales' => trim($val["H"]),
                            'StatusIs' => trim($val["I"]),
                            'SetupBy' => trim($val["J"]),
                            'SetupDate' => trim($val["K"]),
                            'UniqueID' => trim($val["L"]),
                            'SectorName' => trim($val["M"]),
                            'SaleModule' => trim($val["N"])
                        );
                        //print $val["A"];
                        //print $val["C"];
                        //print "<br>".var_dump($val)."<br>";
                        
                }
                unset($objPHPExcel);
                unset($sheetData );                
                
                //var_dump($registerData);

//build out different views
set_time_limit(5000); 

print '<h2>Orbit Register Five-0 Tool</h2>';
print '<a href="./index.php">Home</a>';
print ' | <a href="./registerFive0.php?mode=listAll">List All</a>';
print ' | <a href="./registerFive0.php?mode=cleanUp">What to clean up?</a>';
print ' | <a href="./registerFive0.php?mode=unknown">What is unknown?</a>';
print ' | <a href="./registerFive0.php?mode=lastRevenuActive">What is open and lasted keyed?</a>';

if(isset($_GET['mode'])){
    if($_GET['mode'] ==  'listAll'){
        print '<br><br>List all mode: '.listAll($registerData,$registerMapping);
    }
    
    if($_GET['mode'] ==  'cleanUp'){
        print '<br><br>Clean up mode: '.cleanUp($registerDataLW,$registerMapping,$activeUnits);
    }
    
    if($_GET['mode'] ==  'unknown'){
        print '<br><br>Unknown mode: '.unknown($registerDataLW,$registerMapping,$activeUnits);
    }
    if($_GET['mode'] ==  'lastRevenuActive'){
        print '<br><br>Last Reported Revenue From Active Units: '.lastRevenuActive($registerDataLW,$registerMapping,$activeUnits);
    }
    
    
}else{
    print '<br><br>Make sure you put the current copy of the CIMS Unit Reference file in this tools root folder and use Orbit Excel to open and update the Orbit_Prod_Archive_Register_5_0_2022.xlsx sheet.';
}

//last revenue reported from active units
function lastRevenuActive($registerData,$registerMapping,$activeUnits){   
    $counter = 0;
    $output = '<table border="1">'; 
    $headerSwitch = TRUE;
    foreach($registerData as $key => $val){
        if($headerSwitch){
            $output .= "<tr><th>".$val['UnitName']."</th><th>".$val['UnitNumber']."</th><th>".$val['Keyer']."</th><th>".$val['SalesDate']."</th><th>".$val['RegNameInSaleTable']."</th><th>".$val['RegNameInRegTable']."</th><th>".$val['RegType']."</th><th>".$val['NetSales']."</th><th>".$val['StatusIs']."</th><th>".$val['SetupBy']."</th><th>".$val['SetupDate']."</th><th>".$val['UniqueID']."</th><th>Actual Type</th><th>".$val['SectorName']."</th><th>".$val['SaleModule']."</th></tr>";
            $headerSwitch = FALSE;
        }else{
            if(($val['SaleModule'] == 'Open' && $val['StatusIs'] != 'Disabled')  || (notInCims($val['UnitNumber'],$activeUnits))){
                $output .= "<tr><td>".$val['UnitName']."</td><td>".$val['UnitNumber']."</td><td>".$val['Keyer']."</td><td>".$val['SalesDate']."</td><td>".$val['RegNameInSaleTable']."</td><td>".$val['RegNameInRegTable']."</td><td>".$val['RegType']."</td><td>".$val['NetSales']."</td><td>".$val['StatusIs']."</td><td>".$val['SetupBy']."</td><td>".$val['SetupDate']."</td><td>".$val['UniqueID']."</td><td>".registerType($val['UniqueID'],$registerMapping)."</td><td>".$val['SectorName']."</td><td>".$val['SaleModule']."</td></tr>";
                $counter ++;
            }
        }
    }      
    $output .= '</table>'; 
    return "Records here are open units and the last keyed revenu to an active register. There are ".$counter." records.<br><br>".$output;
}

//all that we know jack about
function unknown($registerData,$registerMapping,$activeUnits){
    $counter = 0;
    $output = '<table border="1">'; 
    $headerSwitch = TRUE;
    foreach($registerData as $key => $val){
        if($headerSwitch){
            $output .= "<tr><th>".$val['UnitName']."</th><th>".$val['UnitNumber']."</th><th>".$val['Keyer']."</th><th>".$val['SalesDate']."</th><th>".$val['RegNameInSaleTable']."</th><th>".$val['RegNameInRegTable']."</th><th>".$val['RegType']."</th><th>".$val['NetSales']."</th><th>".$val['StatusIs']."</th><th>".$val['SetupBy']."</th><th>".$val['SetupDate']."</th><th>".$val['UniqueID']."</th><th>Actual Type</th><th>".$val['SectorName']."</th><th>".$val['SaleModule']."</th><th>Msg Copy</th></tr>";
            $headerSwitch = FALSE;
        }else{
            if(($val['SaleModule'] == 'Open' && $val['StatusIs'] != 'Disabled' && registerType($val['UniqueID'],$registerMapping) == '')  || (notInCims($val['UnitNumber'],$activeUnits))){
                $output .= "<tr><td>".$val['UnitName']."</td><td>".$val['UnitNumber']."</td><td>".$val['Keyer']."</td><td>".$val['SalesDate']."</td><td>".$val['RegNameInSaleTable']."</td><td>".$val['RegNameInRegTable']."</td><td>".$val['RegType']."</td><td>".$val['NetSales']."</td><td>".$val['StatusIs']."</td><td>".$val['SetupBy']."</td><td>".$val['SetupDate']."</td><td>".$val['UniqueID']."</td><td>".makeUpdateForm($val['UniqueID'])."</td><td>".$val['SectorName']."</td><td>".$val['SaleModule']."</td><td>".ourMessage($val)."</td></tr>";
                $counter ++;
            }
        }
    }      
    $output .= '</table>'; 
    return "Records here are registers we know jack about that are active in Orbit and could be used to key revenu. There are ".$counter." records.<br><br>".$output;
}

//all that need to be cleaned up
function cleanUp($registerData,$registerMapping,$activeUnits){
    $counter = 0;
    $output = '<table border="1">'; 
    $headerSwitch = TRUE;
    foreach($registerData as $key => $val){
        if($headerSwitch){
            $output .= "<tr><th>".$val['UnitName']."</th><th>".$val['UnitNumber']."</th><th>".$val['Keyer']."</th><th>".$val['SalesDate']."</th><th>".$val['RegNameInSaleTable']."</th><th>".$val['RegNameInRegTable']."</th><th>".$val['RegType']."</th><th>".$val['NetSales']."</th><th>".$val['StatusIs']."</th><th>".$val['SetupBy']."</th><th>".$val['SetupDate']."</th><th>".$val['UniqueID']."</th><th>Actual Type</th><th>".$val['SectorName']."</th><th>".$val['SaleModule']."</th></tr>";
            $headerSwitch = FALSE;
        }else{
            if(($val['SaleModule'] == 'Closed' && $val['StatusIs'] != 'Disabled') || (notInCims($val['UnitNumber'],$activeUnits) && $val['StatusIs'] != 'Disabled')){
                $output .= "<tr><td>".$val['UnitName']."</td><td>".$val['UnitNumber']."</td><td>".$val['Keyer']."</td><td>".$val['SalesDate']."</td><td>".$val['RegNameInSaleTable']."</td><td>".$val['RegNameInRegTable']."</td><td>".$val['RegType']."</td><td>".$val['NetSales']."</td><td>".$val['StatusIs']."</td><td>".$val['SetupBy']."</td><td>".$val['SetupDate']."</td><td>".$val['UniqueID']."</td><td>".registerType($val['UniqueID'],$registerMapping)."</td><td>".$val['SectorName']."</td><td>".$val['SaleModule']."</td></tr>";
                $counter ++;
            }
        }
    }      
    $output .= '</table>'; 
    return "Records here are registers that should be disabled in Orbit. Either the unit has closed according to the CIMS Ref file or the Sales Module in Orbit is off. There are ".$counter." records.<br><br>".$output;
}

//all in a list
function listAll($registerData,$registerMapping){
    $counter = 0;
    $output = '<table border="1">'; 
    $headerSwitch = TRUE;
    foreach($registerData as $key => $val){
        if($headerSwitch){
            $output .= "<tr><th>".$val['UnitName']."</th><th>".$val['UnitNumber']."</th><th>".$val['Keyer']."</th><th>".$val['SalesDate']."</th><th>".$val['RegNameInSaleTable']."</th><th>".$val['RegNameInRegTable']."</th><th>".$val['RegType']."</th><th>".$val['NetSales']."</th><th>".$val['StatusIs']."</th><th>".$val['SetupBy']."</th><th>".$val['SetupDate']."</th><th>".$val['UniqueID']."</th><th>Actual Type</th><th>".$val['SectorName']."</th><th>".$val['SaleModule']."</th></tr>";
            $headerSwitch = FALSE;
        }else{
            $output .= "<tr><td>".$val['UnitName']."</td><td>".$val['UnitNumber']."</td><td>".$val['Keyer']."</td><td>".$val['SalesDate']."</td><td>".$val['RegNameInSaleTable']."</td><td>".$val['RegNameInRegTable']."</td><td>".$val['RegType']."</td><td>".$val['NetSales']."</td><td>".$val['StatusIs']."</td><td>".$val['SetupBy']."</td><td>".$val['SetupDate']."</td><td>".$val['UniqueID']."</td><td>".registerType($val['UniqueID'],$registerMapping)."</td><td>".$val['SectorName']."</td><td>".$val['SaleModule']."</td></tr>";
            $counter ++;
        }
    }      
    $output .= '</table>'; 
    return "Query to Orbit pulls the last unique instance of a register reporting revenu from Orbit. So if there was an Operator change or a register name change it becomes a record in this table. In some cases 2 or 3 hits on this report are really 1 regsiter in Orbit. There are ".$counter." records.<br><br>".$output;
}


function registerType($x,$regArray){
    if(isset($regArray[$x])){
        return $regArray[$x];
    }else{
        return '';
    }
}

function notInCims($x,$activeUnits){
    foreach($activeUnits as $val){
        if($x = $val){
            return FALSE;
            break;
        }
    }
    return TRUE;
}

function ourMessage($val){
    
    $tempName = explode(".",$val['Keyer']);
    
    if($val['UnitNumber'][0] == '4'){
        return "<button onclick=\"myCopy('".$val['UniqueID']."')\">Msg</button><textarea id=\"".$val['UniqueID']."\" class=\"copy-me\">Bonjour ". ucfirst($tempName[0]).", nous en train de mettons à jour nos dossiers et avons remarqué que vous avez récemment saisi des ventes dans le caisse nommé, ".$val['RegNameInSaleTable'].", dans Orbit chez unité ".$val['UnitNumber'].". Pouvez-vous confirmer de quel modèle/type physique de caisse il s'agit ?</textarea>";
        //return 'French';
    }else{
        return "<button onclick=\"myCopy('".$val['UniqueID']."')\">Msg</button><textarea id=\"".$val['UniqueID']."\" class=\"copy-me\">Hi ". ucfirst($tempName[0]).", we are currently updating our records and noticed you recently keyed revenue to the register named, ".$val['RegNameInSaleTable'].", in Orbit at unit number ".$val['UnitNumber'].". Can you confirm what model / physical type of register this is?</textarea>";
        //return 'English';
    }
}

function makeUpdateForm($x){
    return "<form action=\"registerFive0.php\" method=\"get\"><input type=\"hidden\" name=\"seen\" value=\"".$x."\"><input type=\"text\" id=\"regType\" name=\"regType\"><input type=\"submit\" value=\"&check;\"></form>";
}

?>
    

</body>
<html>