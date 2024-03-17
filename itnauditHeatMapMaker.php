<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);//skip errors
include_once '.\_timestampLogger.php';
//print '<a href=".\index.php">Home</a><hr>';
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


foreach($files1 as $key=>$value){
if($key > 1){


$file = fopen($target_dir.$value, "r");

if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               if(trim(substr($aline,5,20)) != ''){//catch blank rows
               if($currentInvoiceNumber != substr($aline,468,20)){
                   $currentInvoiceNumber = substr($aline,468,20);
                   if(in_array($currentInvoiceNumber,$usedInvoiceNumber)){$dupInvoiceNumber[count($dupInvoiceNumber)]=$currentInvoiceNumber;}
                   $usedInvoiceNumber[count($usedInvoiceNumber)]=$currentInvoiceNumber;
                   $invoiceCount++;        
                  }               
               $line[trim(substr($aline,468,20))]['Distributor Customer Number'] = trim(substr($aline,5,20)); 
               $line[trim(substr($aline,468,20))]['Distribution Center ID'] = trim(substr($aline,498,30));//House
               $line[trim(substr($aline,468,20))]['Transaction Date'] = trim(substr($aline,117,8));
               //$line[trim(substr($aline,468,20))]['Transaction Quantity'] = trim(substr($aline,280,10)); 
               $line[trim(substr($aline,468,20))]['Calculated Total'] += trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));
               //$line[trim(substr($aline,468,20))]['Transaction Volume'] = trim(substr($aline,290,10)); 
               //$line[trim(substr($aline,468,20))]['Invoice Number'] = trim(substr($aline,468,20));
               $line[trim(substr($aline,468,20))]['Province'] = trim(substr($aline,240,20));
               $line[trim(substr($aline,468,20))]['Customer City'] = trim(substr($aline,303,30)); 
               $line[trim(substr($aline,468,20))]['Distributor Customer Name'] = trim(substr($aline,25,40)); 
               $line[trim(substr($aline,468,20))]['Customer Street Address'] = trim(substr($aline,65,40)); 
               $line[trim(substr($aline,468,20))]['Customer Postal'] = trim(substr($aline,105,12)); 
               
               /*
               if(trim(substr($aline,125,20)) == '067000412943'){
                   print  $currentInvoiceNumber."<br>";
               }
               */
               $test = trim(substr($aline,543,10));
               if($test == ""){
                   $missingPriceValue[count($missingPriceValue)] = $currentInvoiceNumber;
               }
               
               $line[trim(substr($aline,468,20))]['Total Amount Due'] = trim(substr($aline,588,14)); 
               $line[trim(substr($aline,468,20))]['Supplier ID'] = trim(substr($aline,1138,20));
               $line[trim(substr($aline,468,20))]['file name'] = trim($value);
                }
        }
        
    fclose($file);
   }
  } 
}

$outputA = '<table border = "1">';
$output = '';

foreach($line as $key => $val){
    if(trim($val['Supplier ID']) != ""){//for some reason in Prod thier is a blank here but not in DEV ¯\_(ツ)_/¯
          //figure out our dates
        $tempFileDate = explode('_',$val['file name']);
        $date1ForCalc = strtotime(substr($val['Transaction Date'],4,4).'-'.substr($val['Transaction Date'],0,2).'-'.substr($val['Transaction Date'],2,2));
        $date2ForCalc = strtotime(substr($tempFileDate[3],0,4).'-'.substr($tempFileDate[3],4,2).'-'.substr($tempFileDate[3],6,2));
        $dtDiff = round((abs($date1ForCalc-$date2ForCalc)/86400),0);
        
    $balanced = TRUE;
    $output = '<tr>';
    $output .= '<td>'.$val['Supplier ID'].'</td>';
    $output .= '<td>'.$key.'</td>';//invoice number is the key   
    $output .= '<td>'.$val['Distributor Customer Number'].'</td>';
    $output .= '<td>'.$val['Distribution Center ID'].'</td>';
    $output .= '<td>'.$val['Total Amount Due'].'</td>';
    if(abs((abs($val['Total Amount Due']) - abs($val['Calculated Total']))) < 0.75){
        $output .= '<td>Balanced</td>';
    }else{
        $output .= '<td><span style="color:red;">Unbalanced | '.$val['Calculated Total'].'<span></td>';
        $balanced = FALSE;
    }
    $output .= '<td>'.substr($val['Transaction Date'],0,2).'/'.substr($val['Transaction Date'],2,2).'/'.substr($val['Transaction Date'],4,4).'</td>';        
    $output .= '<td>'.substr($tempFileDate[3],4,2).'/'.substr($tempFileDate[3],6,2).'/'.substr($tempFileDate[3],0,4).'</td>';
    $output .= '<td>'.$dtDiff.'</td>';
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
    if(isset($_GET["mode"])){
        if($balanced == FALSE){
          $outputA .= $output;  
        }
    }else{
        $outputA .= $output;
    }

    }           
}

$outputA .= '</table>';

//print '<a href="itnaudit.php">No Credits (default)</a> | <a href="itnaudit.php?mode">Unbalanced</a> | <a href="itnaudit.php?dt=1">Date Diff Greater Than</a> | <a href="itnauditInvoiceCredits.php">Inv. Credit</a> | <a href="itnaudit.php?gt=4000">All $ Greater Than</a> | <a href="itnaudit.php?gt=4000&mode">Unballanced $ Greater Than</a> | <a href="itnauditHeatMapMaker.php">Heat Map Maker Mode</a> | <a href="itnauditFullListing.php">Stat Mode</a><hr/>';

    $mode = "All, even credits... <br><br>";
    
    //print $mode;
    
print $outputA;


?>