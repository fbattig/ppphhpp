<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);//skip errors
//include_once '.\_timestampLogger.php';
////////////////////////////////////////////////////////////////////////
//if you can can get a list of already loaded invoices from dev, make an array here and it will add to the output 
//if edi files are load you will also get a nice invoice list.. for ascii its a best guess based on line terminator :)
////////////////////////////////////////////////////////////////////////

//2021.. add invoice watch list
/*
$watchFile = fopen("InvoiceWatchList.txt","r");
while(! feof($watchFile))
  {
  echo fgets($watchFile). "<br />";
  }

fclose($watchFile);
*/
#--------------------------------------------------------------------------->>>>>>>>>>>>>>>>>>>>>
$target_dir ='.\DevBox\\';
#--------------------------------------------------------------------------->>>>>>>>>>>>>>>>>>>>>

$files1 = scandir($target_dir);
$line = array();
$currentInvoiceNumber = '';
$invoiceCount = '';
$usedInvoiceNumber = array();
$dupInvoiceNumber = array();
$missingPriceValue = array();
$invoicesInACreditPosition = array();
$postiveValueLineDetected = FALSE;
$negativeValueLineDetected = FALSE;
set_time_limit(5000);

foreach($files1 as $key=>$value){
if($key > 1){

$file = fopen($target_dir.$value, "r");
print $target_dir;

if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               if(!mb_detect_encoding($aline, 'ASCII', true)){$line[trim(substr($aline,468,20))]['Non Ascii Detection'] = 'Y';}
               //scan for non-ascii chrataures

               if(trim(substr($aline,5,20)) != ''){//catch blank rows
               if($currentInvoiceNumber != substr($aline,468,20)){
                   if($postiveValueLineDetected == TRUE && $negativeValueLineDetected == TRUE){$invoicesInACreditPosition[count($invoicesInACreditPosition)] = $currentInvoiceNumber;}
                   $currentInvoiceNumber = substr($aline,468,20);
                   if(in_array($currentInvoiceNumber,$usedInvoiceNumber)){$dupInvoiceNumber[count($dupInvoiceNumber)]=$currentInvoiceNumber;}
                   $usedInvoiceNumber[count($usedInvoiceNumber)]=$currentInvoiceNumber;
                   $invoiceCount++;
                   $postiveValueLineDetected = FALSE;
                   $negativeValueLineDetected = FALSE;
                  }
               $line[trim(substr($aline,468,20))]['Distributor Customer Number'] = trim(substr($aline,5,20)); 
               $line[trim(substr($aline,468,20))]['Distribution Center ID'] = trim(substr($aline,498,30));//House
               $line[trim(substr($aline,468,20))]['Transaction Date'] = trim(substr($aline,117,8));
               //$line[trim(substr($aline,468,20))]['Transaction Quantity'] = trim(substr($aline,280,10));
               $currentLIneValue = trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));
               $line[trim(substr($aline,468,20))]['Calculated Total'] += $currentLIneValue;
               if($currentLIneValue > 0){
                   $postiveValueLineDetected = TRUE;
               }
               if($currentLIneValue < 0){
                   $negativeValueLineDetected = TRUE;
               }
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
               $line[trim(substr($aline,468,20))]['recalculated'] = $line[trim(substr($aline,468,20))]['recalculated'] + ((trim(substr($aline,280,10)) * trim(substr($aline,543,10))) + trim(substr($aline,568,10)) + trim(substr($aline,578,10)));
               
                }
        }
        
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
   }
  } 
}

$outputA = '<table border = "1"><tr><th>Supplier</th><th>Invoice No.</th><th>DCN</th><th>House</th><th>Total</th><th>Recalculated</th><th>Variance</th><th>Status</th><th>Invoice Date</th><th>File Date</th><th>Date Diff</th><th>File Spec Issue Detected</th><th>Customer Name</th><th>Street</th><th>City</th><th>Province</th><th>Postal</th><th>File</th><th></th></tr>';
$output = '';

$greaterThan = 0;
if(isset($_GET["gt"])){
    $greaterThan = $_GET["gt"];
}

$greaterThanDate = -1;

if(isset($_GET["dt"])){
    $greaterThanDate = $_GET["dt"];
}

foreach($line as $key => $val){
    
    //figure out our dates
        $tempFileDate = explode('_',$val['file name']);
        $date1ForCalc = strtotime(substr($val['Transaction Date'],4,4).'-'.substr($val['Transaction Date'],0,2).'-'.substr($val['Transaction Date'],2,2));
        $date2ForCalc = strtotime(substr($tempFileDate[3],0,4).'-'.substr($tempFileDate[3],4,2).'-'.substr($tempFileDate[3],6,2));
        $dtDiff = round((abs($date1ForCalc-$date2ForCalc)/86400),0);
        $byPassCreditCheckAsWeHaveAnInvoiceInACreditPosition = FALSE;
        foreach($invoicesInACreditPosition as $invoiceCheck){
            if($invoiceCheck == $key){
                $byPassCreditCheckAsWeHaveAnInvoiceInACreditPosition = TRUE;
            }
        }
        //print $key."<br>";
        //print $dtDiff;
          if(($val['Total Amount Due'] > $greaterThan && !isset($_GET["all"]) && $dtDiff > $greaterThanDate) || ($byPassCreditCheckAsWeHaveAnInvoiceInACreditPosition == TRUE && !isset($_GET["all"]) && $dtDiff > $greaterThanDate)){

          
    $balanced = TRUE;
    $fileDup2 = FALSE;
    $output = '<tr>';
    $output .= '<td>'.$val['Supplier ID'].'</td>';
    if(in_array($key, $dupInvoiceNumber)){
        $output .= '<td><span style="color:red;"><a href="ascii_checker_v2.php?file='.$val['file name'].'&invoice='.$key.'" target="_BLANK">'.$key.'</a><br>In 2 source files!</span></td>';
        $fileDup2 = TRUE;
    }else{
        $output .= '<td><a href="ascii_checker_v2.php?file='.$val['file name'].'&invoice='.$key.'" target="_BLANK">'.$key.'</a></td>';   
    }
    //print $val['Non Ascii Detection']."<br>";
    if(isset($val['Non Ascii Detection'])){//if a specail characture was found make this red
        $output .= '<td><span style="color:red;">'.$val['Distributor Customer Number'].' - !!FAIL!!</span></td>';
    }else{
        $output .= '<td>'.$val['Distributor Customer Number'].'</td>';
    }
    $output .= '<td>'.$val['Distribution Center ID'].'</td>';
    if($byPassCreditCheckAsWeHaveAnInvoiceInACreditPosition == TRUE && $val['Total Amount Due'] < 0 && !isset($_GET["lt"])){
        $output .= '<td style="color: red;"><span class="blink">'.$val['Total Amount Due'].'</span></td>';
        }else{
        $output .= '<td>'.$val['Total Amount Due'].'</td>';
    }
    $output .= '<td>'.$val['recalculated'].'</td>';
    $output .= '<td style="background-color: #B0E0E6">'.abs(round(abs($val['Total Amount Due']) - abs($val['recalculated']),2)).'</td>';
    $calcTestString = calcTesting($val['Total Amount Due'], $val['Calculated Total'], $val['recalculated'],$fileDup2);
    $output .= '<td>'.$calcTestString.'</td>';   
    $output .= '<td>'.substr($val['Transaction Date'],0,2).'/'.substr($val['Transaction Date'],2,2).'/'.substr($val['Transaction Date'],4,4).'</td>';        
    $output .= '<td>'.substr($tempFileDate[3],4,2).'/'.substr($tempFileDate[3],6,2).'/'.substr($tempFileDate[3],0,4).'</td>';
    $output .= '<td style="background-color: #B0E0E6">'.$dtDiff.'</td>';
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
      $output .= '<td>'.$val['file name'].'</td>';
      $output .= '<td>';
      if(strpos($calcTestString, "black") !== FALSE || strpos($calcTestString, "X2")){
        $output .= '<a href="ascii_invoice_copy_maker.php?file='.$val['file name'].'&invoice='.$key.'" target="_BLANK">Extract</a>';   
      }
       $output .=  '</td>';
       
    $output .= '</tr>';
    
        if(isset($_GET["mode"])){
            if($balanced == FALSE){
              $outputA .= $output;  
            }
        }else{
            $outputA .= $output;
        }
    }//end main if
    set_time_limit(5000);          
}

$outputA .= '</table>';

//var_dump($line);

print "<!doctype html>

<html lang=\"en\">
<head>
  <meta charset=\"utf-8\">
  <title>ITN Audit</title>

<style>

.blink{    
  -webkit-animation: 1s linear infinite condemned_blink_effect; /* for Safari 4.0 - 8.0 */
  animation: 1s linear infinite condemned_blink_effect;
}

/* for Safari 4.0 - 8.0 */
@-webkit-keyframes condemned_blink_effect {
  0% {
    visibility: hidden;
  }
  50% {
    visibility: hidden;
  }
  100% {
    visibility: visible;
  }
}

@keyframes condemned_blink_effect {
  0% {
    visibility: hidden;
  }
  50% {
    visibility: hidden;
  }
  100% {
    visibility: visible;
  }
}
td {
  text-align: center;
}
</style>
<body>
";
print '<a href=".\index.php">Home</a><hr>';
print '<a href="itnaudit.php">No Credits (default)</a> | <a href="itnaudit.php?mode">Unbalanced</a> | <a href="itnaudit.php?dt=1">Date Diff Greater Than</a> | <a href="itnauditInvoiceCredits.php">Inv. Credit</a> | <a href="itnaudit.php?gt=4000">All $ Greater Than</a> | <a href="itnaudit.php?gt=4000&mode">Unballanced $ Greater Than</a> | <a href="itnauditHeatMapMaker.php">Heat Map Maker Mode</a>  | <a href="itnauditFullListing.php">Stat Mode</a><hr/>';

    $mode = "Default mode, no credits... <br><br>";
    if(isset($_GET["mode"])){
        $mode = "Unbalanced Invoices... <br><br>";
    }
    
    print $mode;
    
print $outputA;


function calcTesting($totalHeader, $totalOldCalc, $totalNewCalc,$fileDup2){
    $message = '';
    if(abs((abs($totalHeader) - abs($totalOldCalc))) < 0.75){
        $message = '<span style="color:black;">Balanced</span>';
    }else{
        $message = '<span style="color:red;">Unbalanced | '.$totalOldCalc.'</span>';
    } 
    $halfOfOldCalc = $totalOldCalc / 2;
    if(abs($halfOfOldCalc) != abs($totalHeader) && (abs($totalNewCalc)-abs($totalOldCalc)) > 0.10){
        if((abs(round(abs($totalHeader) - abs($totalNewCalc),2)) > 0.75)){
            $message .= '<br><span style="color:red;">Recalculation Issue!</span>';
        }
    }
    
    //one last check, we want to know if this is a case of a perfect dup
    if($totalHeader == $halfOfOldCalc && $fileDup2 == FALSE){
       $message = '<span style="color:#ff8c00;">X2 Dup?<br>'.$totalOldCalc.'</span>';
        
    }
    return $message;
}

?>

    </body>
</html>