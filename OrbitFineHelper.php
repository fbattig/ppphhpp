<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Special Projects - SPDST</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="icon" href="./favicon.png">

  
<script>
    function myCopy(tableID) {
      /* Get the text field */
      var copyText = document.getElementById(tableID);

      /* Select the text field */
      copyText.select();
      document.execCommand("copy");

      /* Alert the copied text */
      alert(tableID + "Copied!");
    }
</script>

<style>
.copy-me {
    opacity:0;
    width:0.1px;
    height:0.1px; 
}
table, td, th {
            border: 1px solid black;
            text-align:center;
         }
</style>
</head>

<body>

<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
print '<a href=".\index.php">Home</a><hr>';
include_once '.\_timestampLogger.php';
require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\randomUnitSheets\\";//prod server
        $target_root = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\".$_SESSION['name'];//prod server
      }else{
         $target_dir = "checklist/randomUnitSheets/";//dev server 
         $target_root = $_SESSION['name']."/";//dev server 
      }

//write new mappings to the config file
      //var_dump($_GET);
if(isset($_GET["code"])){
    //print "hjiauweraew".trim($_GET["code"])."<Br>";
    if(trim($_GET["code"]) != "" && trim($_GET["email"]) != "" && strpos(trim($_GET["email"]),'@')!== false){
    $myfile = $target_dir."codeCipher.txt";
    //echo file_exists($myfile);
    $mapping = trim($_GET["code"])."|".trim($_GET["email"])."\r\n";
    file_put_contents($myfile,$mapping, FILE_APPEND | LOCK_EX);
    header('Location: OrbitFineHelper.php');
    }
}   
      
$codeCipherMapFile = 'codeCipher.txt';     
$codeCipher = array();

                $thisFile = fopen($target_dir.$codeCipherMapFile, "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                     $field = explode("|", $aline);
                     if(count($field) > 1){
                      $codeCipher[$field[0]] = $field[1];
                     }
                     //print $field[0].' = '.$field[1].'<br>';
                    }//end while
                }
                fclose($thisFile); 
 
 
$files1 = scandir($target_root);
$DSubmissionsFile = '';

foreach($files1 as $key=>$value){
    if(strpos($value,'DSubmissions') !== false){
        $DSubmissionsFile = $value;//last write would win
    }
}


$files2 = scandir($target_dir);
$onlineReportingReport = "";
$accountingContactList = "";

foreach($files2 as $key=>$value){
    //print $value;
    if(strpos($value,'Online Reporting Access Query')!== false && strpos($value,'~$') === false){
        $onlineReportingReport = $value;//last write would win
    }
    if(strpos($value,'Accounting Contact List')!== false && strpos($value,'~$') === false){
        $accountingContactList = $value;//last write would win
    }
}


if($DSubmissionsFile != '' && $onlineReportingReport != ''){
    print $target_root.$DSubmissionsFile."<br><br>";
    
    $dUnitsArray = array();
    $unitListForCheckingPeoples = array();
    $fiscalDate = '';
    
    $file = fopen($target_root.'\\'.$DSubmissionsFile, "r");
    if ($file) {
          while (!feof($file)) {
                   $aline = fgets($file);
                   $lineData = explode(",",$aline);
                    if($lineData[0] != '' && trim($lineData[0]) != 'Sector'){
                        //print $aline."<br>";
                            $dUnitsArray[$lineData[0]][trim($lineData[1])] = array(
                                //"fiscalYear" => trim($lineData[2]),
                                //"fiscalPeriod" => trim($lineData[3]),
                                //"fiscalWeek" => trim($lineData[4]),
                                "userNameA" => trim($lineData[5]),
                                "purchaseSubmitTime" => trim($lineData[6]),
                                "userNameB" => trim($lineData[7]),
                                "salesSubmitTime" => trim($lineData[8]),
                                "dataEntered" => trim($lineData[9]),
                                "forceSubmitted" => trim($lineData[10]),                                
                            );
                            $fiscalDate = "F".trim($lineData[2])." P".trim($lineData[3])." W".trim($lineData[4]);//this just keeps getting over ridden but it will work.. zero care on my part of crappy coding
                            $unitListForCheckingPeoples[count($unitListForCheckingPeoples)] = trim($lineData[1]);
                    }
          }//end while
          
    }//end if file

  
    $theCCLineEveryoneElse = array();
    $districtsInvolved = array();//well have to lift this from the OLR report.. used for making emails
    $unitNameInvolved = array();//we can also make our report table nicer and get the unit names from here as well.
    $distNumInvolved = array();//used to make our table look better and have a dist number in it
    //let's get some data from the OLR report
        $inputFileName = $target_dir.$onlineReportingReport;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){//OLR
                    foreach($unitListForCheckingPeoples as $key2 => $val2){
                        if($val["A"] == $val2){
                            //print $val["A"]." == ".$val2." ".$val["B"]."<br>";
                            $unitNameInvolved[$val2] = $val["B"];//got the unit name :) ..will use the unit number as array key to fish this out in our table
                            $tempDNum = explode("-",$val["J"]);
                            $distNumInvolved[$val2] = $tempDNum[0];
                            //data looks like shit but we can get this 76 - Dist. 76 - Jan Morel,Jan Morel
                            $districtsInvolved[$val["J"]] = count($districtsInvolved[$val["J"]]);//not really going to use the count but this is a counter of how many hits per district that I can use to fish out the SDA from the contact list, dist will be the array key
                            if(trim($val["G"]) != ''){$theCCLineEveryoneElse[$val["G"]] = count($theCCLineEveryoneElse[$val["G"]]);}//Pres
                            if(trim($val["H"]) != ''){$theCCLineEveryoneElse[$val["H"]] = count($theCCLineEveryoneElse[$val["H"]]);}//RVP
                            if(trim($val["I"]) != ''){$theCCLineEveryoneElse[$val["I"]] = count($theCCLineEveryoneElse[$val["I"]]);}//RD
                        }
                    }
                    
                }
                unset($objPHPExcel);
                unset($sheetData ); 
    
                
                $theCCLineEveryoneElseFromLAO = array();
    //now let's scan the accounting contact list and fine controllers and SDA's
                $inputFileName = $target_dir.$accountingContactList;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){//contactSheet
                    foreach($districtsInvolved as $key2 => $val2){
                        //print $val."<BR>";
                        $tempDist = explode("-",$key2);
                        //print $tempDist[0]." == ".trim($val["A"])."<br>";
                        if(trim($val["A"]) == trim($tempDist[0])){// was added to be able to find Sector controller for overheads
                        
                            //print $tempDist[0]." == ".trim($val["A"])."<br>";
                            if(trim($val["C"]) != ''){$theCCLineEveryoneElseFromLAO[str_replace(array("\r", "\n"),"",$val["C"])] = count($theCCLineEveryoneElseFromLAO[$val["C"]]);}//Controller
                            if(trim($val["D"]) != ''){$theCCLineEveryoneElseFromLAO[str_replace(array("\r", "\n"),"",$val["D"])] = count($theCCLineEveryoneElseFromLAO[$val["D"]]);}//Controller
                        }

                    }
                }
                unset($objPHPExcel);
                unset($sheetData );       
                
                $contracts = array();
                //now let's get the contract type out of the JDE unit dump sheet... Unit Table From JDE.xlsx
                $thisFile = fopen($target_dir.'Unit Table From JDE.csv', "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                            $csvField = explode(",", str_replace('"','',$aline));
                            //print trim($csvField[0])." = ".trim($csvField[29])."<br>";
                            if(trim($csvField[29]) != ''){$contracts[trim($csvField[0])] = trim($csvField[29]);}
                    }//end while
                    fclose($thisFile); 
                }
    //lets see if we know all these messed up codes
                
    $allClear = true;            
    print "<br><b>Unknown DM codes to Email</b><br><br>";
    foreach($districtsInvolved as $key=>$val){
        if($key != "RO1 - Regional Offices,Sector 07" && $key != "25 - 25-Corporate,Corporate (Inc. Leisure)" && trim($key != '')){
             if(!array_key_exists($key, $codeCipher)){
                print $key." ".makeUpdateForm($key)."<br>";
                $allClear = false;
             }
        }else{
            if(trim($key != '')){$theCCLineEveryoneElseFromLAO[$key] = count($theCCLineEveryoneElseFromLAO[$key]);}
        }
    }
    
    if($allClear){print "Hurray, we know all the DM codes!<br>";}
    
    $allClear = true;
    print "<br><b>Unknown Everyone Else Codes to Email</b><br><br>";
    foreach($theCCLineEveryoneElse as $key=>$val){
        if(!array_key_exists($key, $codeCipher)){
                print $key." ".makeUpdateForm($key)."<br><br>";
                $allClear = false;
             }
    }
    if($allClear){print "Hurray, we know all the other codes!<br><br>";}
    
    $allClear = true;
    print "<br><b>Unknown SDA's & Controllers to Email</b><br><br>";
    foreach($theCCLineEveryoneElseFromLAO as $key=>$val){
        //print $key."<br>";
        if(!array_key_exists($key, $codeCipher)){
                print $key." ".makeUpdateForm($key)."<br>";
                $allClear = false;
             }
    }
    if($allClear){print "Hurray, we know all the SDA & Controller codes!<br><br>";}
    
    
    ksort($dUnitsArray);
    print "Here are the delinquent units for ".$fiscalDate.".<br><br>";
    $tableMaker = '<table border="1"><tr><th>Sector</th><th>District</th><th>Unit No.</th><th>Unit Name</th><th>Contract Type</th><th>Fiscal Date</th><th>UserName - Purchases</th><th>Date Purchases Submitted</th><th>Username - Sales</th><th>Date Sales Submitted</th><th>Data Entered</th><th> Forced Submission</th></tr>';
    foreach($dUnitsArray as $sector => $unit){
        if(trim($sector) != ''){
            foreach($unit as $key => $val){
               $tableMaker .= "<tr><td>".$sector."</td><td>".$distNumInvolved[$key]."</td><td>".$key."</td><td>".$unitNameInvolved[$key]."</td><td>".$contracts[$key]."</td><td>".$fiscalDate."</td><td>".testSumittionType1($val["userNameA"],$val["userNameA"])."</td><td>".testSumittionType1($val["userNameA"],$val["purchaseSubmitTime"])."</td><td>".testSumittionType1($val["userNameB"],$val["userNameB"])."</td><td>".testSumittionType1($val["userNameB"],$val["salesSubmitTime"])."</td><td>". testSubmittionType2($val["userNameA"], $val["userNameB"])."</td><td>Yes</td></tr>";
            }
        }
    }
    $tableMaker .= "</table>";
    
    print '<div>';
    print '<button onclick="myCopy(\'mainTable\')">Copy Table</button>';
    print '<button onclick="myCopy(\'dmEmails\')">Copy DM Emails</button>';
    print '<button onclick="myCopy(\'ccEmails\')">Copy CC Emails</button>';
    print '<button onclick="myCopy(\'accountingEmails\')">Copy Accouting Emails</button>';
    print '</div>';
    
    //figure out the email dup problem
    $dmEmailList = '';
    foreach($districtsInvolved as $key => $val){
        if(array_key_exists($key,$codeCipher)){           
            if(strpos($dmEmailList,strtolower(trim($codeCipher[$key]))) === FALSE){$dmEmailList = $dmEmailList.strtolower(trim($codeCipher[$key])).";";}         
        }
    }
    //print $dmEmailList."<br><br>";
    
    print '<textarea id="dmEmails" class="copy-me">';
        print $dmEmailList;
    print '</textarea>';
    
    $ccEmailList = '';
    foreach($theCCLineEveryoneElse as $key => $val){
        if(array_key_exists($key,$codeCipher)){        
            if(strpos($dmEmailList,strtolower(trim($codeCipher[$key]))) === FALSE && strpos($ccEmailList,strtolower(trim($codeCipher[$key]))) === FALSE){
                $ccEmailList = $ccEmailList.strtolower(trim($codeCipher[$key])).";";
                //print 'found '.strtolower(trim($codeCipher[$key]))."<br>"; 
            }
        }
    }
    //print $dmEmailList."<br><br>";
    
    print '<textarea id="ccEmails" class="copy-me">';
        print $ccEmailList;
    print '</textarea>';
    
    print '<textarea id="accountingEmails" class="copy-me">';
    foreach($theCCLineEveryoneElseFromLAO as $key => $val){
        if(array_key_exists($key,$codeCipher)){
            print trim($codeCipher[$key]).";";
        }
    }
    print '</textarea>';
    
    print '<textarea id="mainTable" class="copy-me">'.$tableMaker.'</textarea>';
    print '<br>'.$tableMaker;
    
}else{//no DSub file found so leave instructions
    print 'You must put a copy of the Delinquint Report in your personal tool folder for this to work. The tool must also be able to find the sheets in the randomUnitSheets that powers the Look Stuff Up Machine.';
}


function testSumittionType1($x,$y){
    //check to see if there was data in that module of Orbit by looking for a user that submitted it.. any user... if not don't put that stupid 1/1/1900 time stamp
    if(trim($x) != '' ){
        return str_replace("_System_User"," Auto Closed",$y);
    }else{
        return 'N/A';
    }
}

function testSubmittionType2($x,$y){
    $c = '';
    if(trim($x) != '' && trim($y) != ''){
        $c = 'Purchases & Sales';
    }
    
    if(trim($x) == '' && trim($y) != ''){
        $c = 'Sales';
    }
    
    if(trim($x) != '' && trim($y) == ''){
        $c = 'Purchases';
    }
    
    return $c;
}

function makeUpdateForm($x){
    return "<form action=\"OrbitFineHelper.php\" method=\"get\" autocomplete=\"off\"><input type=\"hidden\" id=\"code\" name=\"code\" value=\"".$x."\"><input size=\"100\" type=\"text\" id=\"email\" name=\"email\"><input type=\"submit\" value=\"&check;\"></form>";
}