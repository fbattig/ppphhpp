<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);//skip errors
include_once '.\_timestampLogger.php';
print '<a href=".\index.php">Home</a><hr>';
////////////////////////////////////////////////////////////////////////
//if you can can get a list of already loaded invoices from dev, make an array here and it will add to the output 
//if edi files are load you will also get a nice invoice list.. for ascii its a best guess based on line terminator :)
////////////////////////////////////////////////////////////////////////
$files1 = scandir($target_dir);
$line = array();
$currentInvoiceNumber = '';
$invoiceCount = '';
$usedInvoiceNumber = array();
$dupInvoiceNumber = array();
$missingPriceValue = array();
set_time_limit(5000);

foreach($files1 as $key=>$value){
$currentInvoiceNumber = '';

    if($key > 1){


$file = fopen($target_dir.$value, "r");

if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               if(trim(substr($aline,5,20)) != ''){//catch blank rows
               if($currentInvoiceNumber != substr($aline,468,20)){
                   $currentInvoiceNumber = substr($aline,468,20);
                   if(in_array($currentInvoiceNumber,$usedInvoiceNumber)){$dupInvoiceNumber[count($dupInvoiceNumber)]=trim($currentInvoiceNumber);}
                   $usedInvoiceNumber[count($usedInvoiceNumber)]=$currentInvoiceNumber;
                   $invoiceCount++;        
                  }               
               $line[$invoiceCount][trim(substr($aline,468,20))]['Distributor Customer Number'] = trim(substr($aline,5,20)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Distribution Center ID'] = trim(substr($aline,498,30));//House
               $line[$invoiceCount][trim(substr($aline,468,20))]['Transaction Date'] = trim(substr($aline,117,8));
               //$line[trim(substr($aline,468,20))]['Transaction Quantity'] = trim(substr($aline,280,10)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Calculated Total'] += trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));
               //$line[trim(substr($aline,468,20))]['Transaction Volume'] = trim(substr($aline,290,10)); 
               //$line[trim(substr($aline,468,20))]['Invoice Number'] = trim(substr($aline,468,20));
               $line[$invoiceCount][trim(substr($aline,468,20))]['Province'] = trim(substr($aline,240,20));
               $line[$invoiceCount][trim(substr($aline,468,20))]['Customer City'] = trim(substr($aline,303,30)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Distributor Customer Name'] = trim(substr($aline,25,40)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Customer Street Address'] = trim(substr($aline,65,40)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Customer Postal'] = trim(substr($aline,105,12)); 
               /*
               if(trim(substr($aline,125,20)) == '067000412943'){
                   print  $currentInvoiceNumber."<br>";
               }
               */
               $test = trim(substr($aline,543,10));
               if($test == ""){
                   $missingPriceValue[count($missingPriceValue)] = $currentInvoiceNumber;
               }
               
               $line[$invoiceCount][trim(substr($aline,468,20))]['Total Amount Due'] = trim(substr($aline,588,14)); 
               $line[$invoiceCount][trim(substr($aline,468,20))]['Supplier ID'] = trim(substr($aline,1138,20));
               $line[$invoiceCount][trim(substr($aline,468,20))]['file name'] = trim($value);
                }
        }
        
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
   }
  } 
}

$unbalancedStat = 0;
$totalInvoiceCountStat = 0;
$totalOlderThan10 = 0;

$outputA = '<table border = "1"><tr><th>Supplier</th><th>Duplicate</th><th>Invoice No.</th><th>DCN</th><th>House</th><th>Total</th><th>Status</th><th>Invoice Date</th><th>File Date</th><th>Date Diff</th><th>File Spec Issue Detected</th><th>Customer Name</th><th>Street</th><th>City</th><th>Province</th><th>Postal</th></tr>';
$output = '';

$greaterThan = 0;
if(isset($_GET["gt"])){
    $greaterThan = $_GET["gt"];
}

$greaterThanDate = -1;
if(isset($_GET["dt"])){
    $greaterThanDate = $_GET["dt"];
}

//var_dump();
foreach($line as $aKey => $aInvoice){
    foreach($aInvoice as $key => $val){
        if(trim($val['Supplier ID']) != ""){//for some reason in Prod thier is a blank here but not in DEV ¯\_(ツ)_/¯
            $totalInvoiceCountStat++;
            $balanced = TRUE;
        $output = '<tr>';
        $output .= '<td>'.$val['Supplier ID'].'</td>';
        if(in_array($key, $dupInvoiceNumber)){
            $output .= '<td><span style="color:red;">Yes</span></td>';
        }else{
            $output .= '<td>No</td>';   
        }
        $output .= '<td><a href="ascii_checker_v2.php?file='.$val['file name'].'&invoice='.$key.'" target="_BLANK">'.$key.'</a></td>'; 
        $output .= '<td>'.$val['Distributor Customer Number'].'</td>';
        $output .= '<td>'.$val['Distribution Center ID'].'</td>';
        $output .= '<td>'.$val['Total Amount Due'].'</td>';
      if(abs((abs($val['Total Amount Due']) - abs($val['Calculated Total']))) < 0.75){
             $output .= '<td>Balanced</td>';
        }else{
               $output .= '<td><span style="color:red;">Unbalanced | '.$val['Calculated Total'].'<span></td>';
               $unbalancedStat++;
        $balanced = FALSE;
    }
        $output .= '<td>'.substr($val['Transaction Date'],0,2).'/'.substr($val['Transaction Date'],2,2).'/'.substr($val['Transaction Date'],4,4).'</td>';
         $tempFileDate = explode('_',$val['file name']);
        $date1ForCalc = strtotime(substr($val['Transaction Date'],4,4).'-'.substr($val['Transaction Date'],0,2).'-'.substr($val['Transaction Date'],2,2));
        $date2ForCalc = strtotime(substr($tempFileDate[3],0,4).'-'.substr($tempFileDate[3],4,2).'-'.substr($tempFileDate[3],6,2));
        $output .= '<td>'.substr($tempFileDate[3],4,2).'/'.substr($tempFileDate[3],6,2).'/'.substr($tempFileDate[3],0,4).'</td>';
        $dateDiff = round((abs($date1ForCalc-$date2ForCalc)/86400),0);
            if($dateDiff > 14){$totalOlderThan10++;}
        $output .= '<td>'.$dateDiff.'</td>';

            if(in_array($key,$missingPriceValue)){
            $output .= '<td>Error!</td>';
            }else{
                $output .= '<td></td>';
            }
            
        $output .= '<td>'.$val['Distributor Customer Name'].'</td>';      
        $output .= '<td>'.$val['Customer Street Address'].'</td>';      
        $output .= '<td>'.$val['Customer City'].'</td>';     
        $output .= '<td>'.$val['Province'].'</td>';
        $output .= '<td>'.str_replace(" ","",$val['Customer Postal']).'</td>';    
        $output .= '</tr>';
        $outputA .= $output;

        set_time_limit(5000);   
        }
    }
}

$outputA .= '</table>';

print '<a href="itnaudit.php">No Credits (default)</a> | <a href="itnaudit.php?mode">Unbalanced</a> | <a href="itnaudit.php?dt=1">Date Diff Greater Than</a> | <a href="itnauditInvoiceCredits.php">Inv. Credit</a> | <a href="itnaudit.php?gt=4000">All $ Greater Than</a> | <a href="itnaudit.php?gt=4000&mode">Unballanced $ Greater Than</a> | <a href="itnauditHeatMapMaker.php">Heat Map Maker Mode</a> | <a href="itnauditFullListing.php">Stat Mode</a><hr/>';

    $mode = "Stat mode... Total Invoice Count = ".$totalInvoiceCountStat." | Unbalanced = ".$unbalancedStat." | Duplicates = ".count($dupInvoiceNumber)." | Old = ".$totalOlderThan10."<br><br>";

    print $mode;
    
    //var_dump($dupInvoiceNumber);
    
print $outputA;


?>