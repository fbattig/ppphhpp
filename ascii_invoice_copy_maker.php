<?php
error_reporting(E_ERROR | E_PARSE);//skip errors
session_start();
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//loop through a CIMS data file set and extra invoice copies based on this array..
include_once '.\_timestampLogger.php';

if(isset($_GET['file']) && isset($_GET['invoice'])){
    $theFile = trim($_GET['file']);
    $theInvoice = trim($_GET['invoice']);

$fileRebuild = '';

//print $target_dir.$theFile.'<br>';

$file = fopen($target_dir.$theFile, "r");
$balanceMaker = 0;//used to stop the loop when we have balance... for those times when they stack 2 or 3 copies of the invoice together
$newCalculatedTotal = 0;
$reportedInvoiceTotal = 0;
if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               //print trim(substr($aline,468,20))." = ".$theInvoice."<br>";
               $reportedInvoiceTotal = trim(substr($aline,588,14));//we want the reported invoice total since this is a perfect dup. Setup the break point mathimatically with this :)
               //print "<br>";
               if(trim(substr($aline,468,20)) == $theInvoice){
                   //add to the balance 
                   //transaction volume + tax 1 + tax 2 = Total Amount Due and then stop
                   $balanceMaker += trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));
                   //print $balanceMaker." ".$newCalcTotal."<br>";
                   //if($newCalcTotal <= trim(substr($aline,588,14))){//this one seemed to cause an extra line to be added with Nestle
                   if($balanceMaker <= $reportedInvoiceTotal){    
                    $fileRebuild .= $aline;
                    $newCalculatedTotal += trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));//only do this here as the last loop will bring the other variable uo in total so we want the second last value from this loop :)
                   }else{
                       break;
                   }
                   //print 'ping<br>';
        }
      }
     }
             
    fclose($file);

//print $target_dir;
//var_dump($fileRebuild);

        
    //make a unique-ish file name
    $tempFileName = explode('_',$theFile);
    $newFileName = $tempFileName[0]."_".$tempFileName[1]."_Invoice_Extract_".$theInvoice."_".$_SESSION['name'][0].$_SESSION['name'][1].'.txt';
$outputFile = $target_dir.$newFileName;
// Write the contents to the file
file_put_contents($outputFile, $fileRebuild);

print 'Extracted copy total is: $'.$newCalculatedTotal.'. This should match the vendors header total of $'.$reportedInvoiceTotal.'.<br>'.$newFileName.' with invoice '.$theInvoice.' has been made.';
}else{//when URL data is missing
    print 'No data provided. Sorry';
}