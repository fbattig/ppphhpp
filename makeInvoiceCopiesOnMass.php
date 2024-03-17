<?php
session_start();
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//loop through a CIMS data file set and extra invoice copies based on this array..
include_once '.\_timestampLogger.php';

$wanted = array(
'2088592',
'2092830',
'2096018',
'2098899',
'2100461',
'2098673',
'2101531',
'2103034',
'2102311',
'2104198',
'2102567',
'2106281',
'2108957',
'2109961',
'2109933',
'2111061',
'2111108',
'2112293',
'2112579',
'2114097',
'2116003',
'2115435',
'2116101',
'2115463',
'2116546',
'2117888',
'2117930',
'2118383',
'2118921',
'2118923',
'2119318',
'2119230'
);

//var_dump($wanted);
$files1 = scandir($target_dir);
$line = array();
$currentInvoiceNumber = '';
$fileRebuild = '';

foreach($files1 as $key=>$value){
if($key > 1){

//echo $value."<br>";
$file = fopen($target_dir.$value, "r");

if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               if(in_array(trim(substr($aline,468,20)), $wanted)){
                   $fileRebuild .= $aline;              
        }
      }
     }
             
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
 }
}

//print $target_dir;
//var_dump($fileRebuild);

$outputFile = $target_dir.'CIMSInvoiceFileMassCopyEPZ.txt';
// Write the contents to the file
file_put_contents($outputFile, $fileRebuild);

print 'done.';