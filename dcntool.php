<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Special Projects - SPDST</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="icon" href="./favicon.png">

  
<script>
    function myCopy(invoice,lang) {
      /* Get the text field */
      var copyText = document.getElementById(invoice+lang);

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
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
print '<a href=".\index.php">Home</a><hr>';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){//needed to get the log file location
        $target_dir2 = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";
      }else{
         $target_dir2 = "";
      }
////////////////////////////////////////////////////////////////////////
//write new mappings to the config file
if(isset($_GET["seen"])){
    
    $myfile = $target_dir2."DCNTConfig.txt";
    //echo file_exists($myfile);
    $makeText = str_replace("zueff","\r\n",trim($_GET["seen"]));
    $mapping = $makeText."\r\n";
    file_put_contents($myfile,$mapping, FILE_APPEND | LOCK_EX);
    header('Location: dcntool.php');
}
      
if(isset($_GET["report"])){
    $myfile = $target_dir2."DCNTReport.txt";
    //echo file_exists($myfile);
    $mapping = trim($_GET["report"])."\r\n";
    file_put_contents($myfile,$mapping, FILE_APPEND | LOCK_EX);
    $myfile = $target_dir2."DCNTConfig.txt";
    file_put_contents($myfile,$mapping, FILE_APPEND | LOCK_EX);
    header('Location: dcntool.php');
}
          
      
////////////////////////////////////////////////////////////////////////
$files1 = scandir($target_dir);
$files1[count($files1)] = 'DCNTConfig.txt';//maually add that now to the file array
$files1[count($files1)] = 'DCNTNotes.txt';//maually add that now to the file array
$lastInvoiceNum = '0';
$knownMappings = array();
$knownMappingsMatch = array();
$invoiceCount = -1;
$notes = '<div style="border-style: dotted;padding: 1px;border-width: thin;"><i>Team Notes</i>';

print '<b>Welcome to the DCN Tool, your tool to help weed out Foodbuy data in ours.</i>';

if(count($files1) > 2){
    
    
 //$noDataWeNeedInFile = TRUE;
foreach($files1 as $key=>$value){
    //$noDataWeNeedInFile = TRUE; 
if($key > 1){
    // $value.'<br>';
if($value == "DCNTConfig.txt" || $value == "DCNTNotes.txt"){//move the config to the root after my deleting :s
    $file = fopen($target_dir2.$value, "r");
}else{
    $file = fopen($target_dir.$value, "r");
}
if ($file) { 
      while (!feof($file)) {
            $aline = fgets($file);
            //print trim(substr($aline,468,20));
            //print "<br>";
            if($value == "DCNTConfig.txt"){
                //vendor|DCN|F or C
                            $tempMapping = explode("|",$aline);
                            //print $aline;
                            $knownMappingsMatch[count($knownMappingsMatch)] = trim($tempMapping[0])."|".trim($tempMapping[1]);
                            $knownMappings[count($knownMappings)]['Vendor'] = $tempMapping[0];
                            $knownMappings[count($knownMappings)]['DCN'] = $tempMapping[1];
            }
            
            if($value == "DCNTNotes.txt"){
                //vendor|DCN|F or C
                $notes .= '<p>'.$aline.'</p>';       
            }
            
            if($value != "DCNTConfig.txt" && $value != "DCNTNotes.txt"){
               
                
                $explodeForEdi = explode("~",$aline);
               //print $aline;
               if($explodeForEdi > 1){
                    //this should be good as only EDI files have tildas
                   foreach($explodeForEdi as $key => $val){
                       //print $val."<br>";
                       //BIG*---* %Inovice Number%
                       //N1*ST* %Name% *--* %DCN%
                       //
                       $tempReExplode = explode("*",$val);
                       if($tempReExplode[0] == 'BIG'){
                           //print "<hr/>";
                           $invoiceList[$invoiceCount]['Invoice Number'] = $tempReExplode[2];
                           $invoiceCount++;
                           $n1Counter = 0;
                           $nAddyCounter = 0;
                           $getSaptuoAddyStuff = 0;
                       }
                       
                       if($tempReExplode[1] == 'VN'){
                           $invoiceList[$invoiceCount - 1]['Vendor'] = $tempReExplode[2];
                       }
                       
                       if($n1Counter == 1 && $nAddyCounter != 2){
                           
                           if($nAddyCounter == 0){
                               //print $tempReExplode[0]."<hr/>";
                                $invoiceList[$invoiceCount - 1]['Street'] = $tempReExplode[1];
                           }
                           if($nAddyCounter == 1){
                               //print $tempReExplode[0]."<hr/>";
                                $invoiceList[$invoiceCount - 1]['City'] = $tempReExplode[1];
                                $invoiceList[$invoiceCount - 1]['Province'] = $tempReExplode[2];
                                $invoiceList[$invoiceCount - 1]['Postal'] = $tempReExplode[3];
                           }
                           $nAddyCounter++;
                       }
                       if($tempReExplode[1] == 'ST'){
                             $n1Counter++;
                             $invoiceList[$invoiceCount - 1]['Name'] = $tempReExplode[2];
                             $invoiceList[$invoiceCount - 1]['DCN'] = $tempReExplode[4];
                       }
                       
                       //Saputo seems to get the invoice number using the Pepso expodes above but nothing else, so lest get more here and pray it doesnt break the pespi logic
                       if(trim($tempReExplode[2]) == 'SAPTO'){
                           $invoiceList[$invoiceCount - 1]['Vendor'] = $tempReExplode[2];
                           //$getSaptuoAddyStuff = 1;
                       }
                       if(trim($tempReExplode[1]) == 'LW'){
                           $invoiceList[$invoiceCount - 1]['Name'] = $tempReExplode[2];
                           $invoiceList[$invoiceCount - 1]['DCN'] = $tempReExplode[4];
                           $getSaptuoAddyStuff = 1;
                       }
                       if($getSaptuoAddyStuff == 1 && trim($tempReExplode[0]) == 'N3'){
                           $invoiceList[$invoiceCount - 1]['Street'] = $tempReExplode[1];
                           $getSaptuoAddyStuff = 2;
                       }
                       if($getSaptuoAddyStuff == 2 && trim($tempReExplode[0]) == 'N4'){
                           $invoiceList[$invoiceCount - 1]['City'] = $tempReExplode[1];
                           $invoiceList[$invoiceCount - 1]['Province'] = $tempReExplode[2];
                           $invoiceList[$invoiceCount - 1]['Postal'] = $tempReExplode[3];
                           $getSaptuoAddyStuff = 3;
                       }
                    }
               }
               
               
               //this should be ok, it should only trip for an Acsii file
               if(strlen($aline) == 1160 && (trim(substr($aline,468,20)) != $lastInvoiceNum)){//see if we need this invoice number as ascii is a repeating loop each line that has this number over and over again
                            //see if we need our file name cause this is a new file
                            //print "in Accii loop<hr/>";
                            //add the invoice number to the list
                            $invoiceCount++;
                            $lastInvoiceNum = trim(substr($aline,468,20));
                            $invoiceList[$invoiceCount]['Invoice Number'] = trim(substr($aline,468,20));
                            $invoiceList[$invoiceCount]['Vendor'] = trim(substr($aline,1138,20));
                            $invoiceList[$invoiceCount]['Street'] = trim(substr($aline,65,40));
                            $invoiceList[$invoiceCount]['City'] = trim(substr($aline,303,30));
                            $invoiceList[$invoiceCount]['Province'] = trim(substr($aline,240,20));
                            $invoiceList[$invoiceCount]['Postal'] = trim(substr($aline,105,12));
                            $invoiceList[$invoiceCount]['Name'] = trim(substr($aline,25,40));
                            $invoiceList[$invoiceCount]['DCN'] = trim(substr($aline,5,20));
                            $invoiceList[$invoiceCount]['Total'] = trim(substr($aline,588,14));
                            $invoiceList[$invoiceCount]['Date'] = trim(substr($aline,117,8));
                            $invoiceList[$invoiceCount]['file'] = $value;
                            //$noDataWeNeedInFile = FALSE;
                            }

        }  
        
      }
 
    fclose($file);
        //if($noDataWeNeedInFile == TRUE && $value != "DCNsnifferConfig.txt" && $value != ".."){print "try delete ".$target_dir2.$value."<br>";unlink($target_dir2.$value);}
   }
  } 
  }
}

//var_dump($knownMappingsMatch);
$countOfCrapToDealWith = 0;
$tableDisplay = '';
$dismissAll = '';
$swapKey = '';
if($invoiceList > 0){
    $tableDisplay .= "<br><table border = 1><tr><th></th><th>Vendor</th><th>Invoice No.</th><th>DCN</th><th>Unit Name</th><th>Street</th><th>City</th><th>Prov.</th><th>Postal</th><th>Total</th><th>Copy</th><th></th></tr>";
foreach($invoiceList as $key => $val){
    $ourTest = $val['Vendor']."|".$val['DCN'];
    if(!in_array($ourTest, $knownMappingsMatch)){
        $countOfCrapToDealWith++;
        $xVendor = $val['Vendor'];
        if(substr($xVendor,0,7) == 'CANUNFI'){$xVendor = 'UNFI';}
        if(substr($xVendor,0,7) == 'CAN_CMK'){$xVendor = 'Coremark';}
        $xInvoiceNo = $val['Invoice Number'];
        $xAccountName = $val['Name'];
        $xDCN = $val['DCN'];
        $xDate = substr($val['Date'],0,2)."-".substr($val['Date'],2,2)."-".substr($val['Date'],4,4);
        $xTotalEn = "$".$val['Total'];
        $xTotalFr = $val['Total']."$";
      $tableDisplay .= "<tr><td><form action=\"dcntool.php\" method=\"get\"><input type=\"hidden\" name=\"seen\" value=\"".$val['Vendor']."|".$val['DCN']."\"><input type=\"submit\" value=\"Seen\"></form></td>";
      $tableDisplay .= "<td>".$val['Vendor']."</td><td><a href=\"./ascii_checker_v3.php?DCNT=".$val['file']."&Invoice=".$val['Invoice Number']."\" target=\"__BLANK\">".$val['Invoice Number']."</a></td><td>".$val['DCN']."</td><td>".$val['Name']."</td><td>".$val['Street']."</td><td>".$val['City']."</td><td>".$val['Province']."</td><td>".$val['Postal']."</td><td>".$val['Total']."</td><td><button onclick=\"myCopy('".$val['Vendor'].$val['Invoice Number']."','En')\">En</button><button onclick=\"myCopy('".$val['Vendor'].$val['Invoice Number']."','Fr')\">Fr</button>";
      $tableDisplay .= "<textarea id=\"".$val['Vendor'].$val['Invoice Number']."En\" class=\"copy-me\">Hello, we have found an invoice in the new CIMS data that seems to be for your unit. Can you confirm this is your invoice?&#xa;&#xa;Vendor : ".$xVendor."&#xa;Account Name : ".$xAccountName."&#xa;Customer No : ".$xDCN."&#xa;Invoice No: ".$xInvoiceNo."&#xa;Invoice Date: ".$xDate."&#xa;Invoice Total: ".$xTotalEn."</textarea>";
      $tableDisplay .= "<textarea id=\"".$val['Vendor'].$val['Invoice Number']."Fr\" class=\"copy-me\">Bonjour, nous avons trouvé une facture dans les données de CIMS aujourd'hui qui semble être pour votre unité. Pouvez-vous confirmer qu'elle est votre facture ?&#xa;&#xa;Fournisseur : ".$xVendor."&#xa;Nom De Compte : ".$xAccountName."&#xa;Numéro de client : ".$xDCN."&#xa;Numéro de facture: ".$xInvoiceNo."&#xa;Date de facturation: ".$xDate."&#xa;Total de la facture: ".$xTotalFr."</textarea>";
      $tableDisplay .= "</td><td><form action=\"dcntool.php\" method=\"get\"><input type=\"hidden\" name=\"report\" value=\"".$val['Vendor']."|".$val['DCN']."\"><input type=\"submit\" value=\"Report\"></form></td></tr>";
    
      $dismissAll .= $swapKey.$val['Vendor']."|".$val['DCN']; 
      $swapKey = "zueff";
     }
}

    $tableDisplay .= "</table>";
    print " There are ".$countOfCrapToDealWith." invoices.<br><br>".$notes."</div>".$tableDisplay;
    if($dismissAll != ""){
        print "<br><br><form action=\"dcntool.php\" method=\"get\"><input type=\"hidden\" name=\"seen\" value=\"".$dismissAll."\"><input type=\"submit\" value=\"Dismiss All\"></form>";
    }
}


?>
    

</body>
<html>