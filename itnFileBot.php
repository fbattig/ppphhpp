<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\itnFileBot\\";//prod server
      }else{
         $target_dir = "itnFileBot/";//dev server 
      }
///////////April 2020/////////////      
  //on monday when you first run this code all the old files from last week where still here and skew the grid.. so this will reload the page if while scanning the files it finds old data    
  $oldDataDetected = FALSE; 
///back to orignal code//////      
////////////////////////////////////////////////////////////////////////
//if you can can get a list of already loaded invoices from dev, make an array here and it will add to the output 
//if edi files are load you will also get a nice invoice list.. for ascii its a best guess based on line terminator :)
////////////////////////////////////////////////////////////////////////
$report = "";
$files1 = scandir($target_dir);
$line = array();
$invoiceCount = '';
$supplierIdsFound = array();
$lastSupplierUsed = '';
$usedInvoiceNumber = array();
$missingPriceValue = array();
$vendorConfig = array();
$newFiles = array();
$invoiceCountingLog = 'Invoice Number,Invoice Date,Vendor Group,House,File Date,File Name'."\r\n";
$fileIssueLog = 'Vendor,Period Invoice Is For,Invoice Date,Invoice #,Customer #,Amount,Unit,Reported Missing,Reported by Name and Phone Number,Did you ask them to process in ORBIT? When?,Account Support Associate,Unbalanced or Not Sent?,House,Filename'."\r\n";
$lastFriday = array();
$superOld = array();
$vendorNote = array();

// set dates used for files (example: we are looking for a file on Friday with Thursday's date)
$today = date('m/d/Y');

$saturday = date('m/d/Y', strtotime("last Friday +1 day"));

$sunday = date('m/d/Y', strtotime("last Friday +2 day"));

$monday = date('m/d/Y', strtotime("last Friday +3 day"));

$tuesday = date('m/d/Y', strtotime("last Friday +4 day"));

$wednesday = date('m/d/Y', strtotime("last Friday +5 day"));

$thursday = date('m/d/Y', strtotime("last Friday +6 day"));

if(date('D') == 'Fri'){
   $friday = $today;
}else{
    $friday = date('m/d/Y', strtotime("last Friday +7 day"));
}


if($today == $saturday){$dow = 1;}
if($today == $sunday){$dow = 2;}
if($today == $monday){$dow = 3;}
if($today == $tuesday){$dow = 4;}
if($today == $wednesday){$dow = 5;}
if($today == $thursday){$dow = 6;}
if($today == $friday){$dow = 7;}

//set dates used for the grid (We need the real date to appear on the grid
$hFriday = date('m/d/Y', strtotime("last Friday"));//adding 6/19/2019 to make the grid show last friday invoices from this load
$gSaturday = date('m/d/Y', strtotime("last Saturday"));
$gSunday = date('m/d/Y', strtotime("last Saturday +1 day"));
$gMonday = date('m/d/Y', strtotime("last Saturday +2 day"));
$gTuesday = date('m/d/Y', strtotime("last Saturday +3 day"));
$gWednesday = date('m/d/Y', strtotime("last Saturday +4 day"));
$gThursday = date('m/d/Y', strtotime("last Saturday +5 day"));
$gFriday = date('m/d/Y', strtotime("last Saturday +6 day")); 


//building new version visuals to see what is what 6/19/2019
if(isset($_GET['new'])){
    print "Grids current Friday <b>".$friday."</b><br>";
    print "Our new last Friday <b>".$hFriday."</b><br>";
}

//var_dump($currentWeekDates);print '<br><br>';

//first pass checking only the root folder for new data
foreach($files1 as $key=>$value){
if($key > 1 && is_file($target_dir.$value)){


$file = fopen($target_dir.$value, "r");

if($value == 'config.csv'){
    if ($file) {
        $t = 0;
      while (!feof($file)) {
              $aline = fgets($file);
              if($t != 0){
               $temp234 = explode(",",$aline);
               if($temp234[0] != ''){
              $vendorConfig['expected'][$temp234[0]][$temp234[1]] = array(
                  'Sat'=>$temp234[2],
                  'Sun'=>$temp234[3],
                  'Mon'=>$temp234[4],
                  'Tue'=>$temp234[5],
                  'Wed'=>$temp234[6],
                  'Thu'=>$temp234[7],
                  'Fri'=>$temp234[8],
                  );
              $vendorNote[$temp234[1]] = $temp234[10];
              
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][0] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][1] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][2] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][3] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][4] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][5] = 0;
               $vendorConfig['filesReceived'][$temp234[0]][$temp234[1]][6] = 0;
               
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][0] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][1] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][2] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][3] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][4] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][5] = 0;
               $vendorConfig['invoiceInFilesReceived'][$temp234[0]][$temp234[1]][6] = 0;
                       
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][0] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][1] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][2] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][3] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][4] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][5] = 0;
               $vendorConfig['invoicesReceived'][$temp234[0]][$temp234[1]][6] = 0;
               }
              }
           $t++;
      }
     fclose($file); 
    }
}else{
if ($file && $value != 'invoiceCountingLog.csv') {
      while (!feof($file)) {
               $aline = fgets($file);
               if(trim(substr($aline,5,20)) != ''){//catch blank rows
                //gather supplier id's found
               
               if(!in_array(trim(substr($aline,1138,20)),$supplierIdsFound)){
                   $supplierIdsFound[count($supplierIdsFound)] = trim(substr($aline,1138,20));
                   //makeDir($target_dir.trim(substr($aline,1138,20)));
               }
               //used to move the file
               $lastSupplierUsed = trim(substr($aline,1138,20)).'/';
                              
                }
        }
        
    fclose($file);
        if(is_dir($target_dir.$lastSupplierUsed)){
          rename($target_dir.$value, $target_dir.$lastSupplierUsed.$value);
          $newFiles[count($newFiles)] = $value;//make a list of the new files so we know which files to fact check below for errors
        }else{
            $report .= "Error! Alien file detected - ".$value." - No archive folder found! Beep boop bop..<br>";
        }
        $lastSupplierUsed = "";
   }
  } 
 }
}

//$troubleShoot = array();
foreach($vendorConfig['expected'] as $key => $val){
    //print $key."<br>";
    foreach($val as $key2 => $val2){
        //$troubleShoot[count($troubleShoot)] = $key2; 
    unset($file);
    unset($files1);
    $invoicesInFile = 0;
    $deepScan = $target_dir."/".$key2;
    //print '<br>';
    //print $target_dir.$key2.'<br>';
    $files1 = scandir($deepScan);
    //var_dump($files1);
    foreach($files1 as $key3=>$value3){
              if($key3 > 1){
              set_time_limit(5000);    
              $tmpFileDate = substr($value3,strrpos($value3,"_")+1,8);
              $fileYear =  substr($tmpFileDate,0,4);
              $fileMonth = substr($tmpFileDate,4,2);
              $fileDay = substr($tmpFileDate,6,2);
              $fileDateStamp = $fileMonth.'/'.$fileDay.'/'.$fileYear;
              $trackFile = FALSE;
              $currentInvoiceNumber = '';
              $realInvoiceDate = '';
              $customerNumber = '';
              $error4016 = FALSE;
              $errorNonEnglish = FALSE;
              $errorPositveCreditLine = FALSE;
              $invoicesInFile = 0;
              
              $file = fopen($deepScan.'/'.$value3, "r");
                    $currentVendor = '';
                    $currentVendor = '';
                    $headerTotal = 0;
                    $calculatedLineTotal = 0;
                    
                    if ($file) {
                       
                    while (!feof($file)) {
                        $aline = fgets($file);
                        if(trim(substr($aline,5,20)) != ''){
                            
                            $currentVendor = trim(substr($aline,1138,20));
                            
                            if($currentInvoiceNumber != trim(substr($aline,468,20)) ){
                                
                                $lastInvoiceNum = $currentInvoiceNumber;
                                $currentInvoiceNumber = trim(substr($aline,468,20));                                   
                                
                                if($headerTotal != 0){  
                                //check for unbalanced now

                                    if(in_array($value3,$newFiles) && (abs(abs($headerTotal) - abs($calculatedLineTotal)) >= 0.70) && ($headerTotal > 0)){  
                                        //print $key." ".$lastInvoiceNum." ".$headerTotal." = ".$calculatedLineTotal." in ".$value3."<br>";
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$lastInvoiceNum.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Unbalanced,'.$key2.','.$value3."\r\n";
                                    }
                                    //error 4016
                                    if($error4016){
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$lastInvoiceNum.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Error 4016,'.$key2.','.$value3."\r\n";
                                    }
                                    //non english error
                                    if($errorNonEnglish){
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$lastInvoiceNum.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Error Non English,'.$key2.','.$value3."\r\n";
                                    } 
                                    //postiveCredit
                                    if($errorPositveCreditLine){
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$lastInvoiceNum.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Error Positive Credit Line,'.$key2.','.$value3."\r\n";
                                    } 
                                }
                                //reset for the next invoice
                                $headerTotal = 0;
                                $calculatedLineTotal = 0;
                                $error4016 = FALSE;
                                $errorNonEnglish = FALSE;
                                $errorPositveCreditLine = FALSE;
                               
                                
                                $invoicesInFile++;
                                $tempInvoiceDate = trim(substr($aline,117,8));
                                $realInvoiceDate = substr($tempInvoiceDate,0,2).'/'.substr($tempInvoiceDate,2,2).'/'.substr($tempInvoiceDate,4,4);
                                //print '<br>';
                                switch($realInvoiceDate){
                                        case $gSaturday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][0]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gSunday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][1]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gMonday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][2]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gTuesday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][3]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gWednesday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][4]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gThursday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][5]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break;
                                        case $gFriday;
                                                $vendorConfig['invoicesReceived'][$key][$currentVendor][6]++;
                                                $invoiceCountingLog .= $currentInvoiceNumber.",".$realInvoiceDate.",".$key.",".$currentVendor.",".$fileDateStamp.",".$value3."\r\n";
                                            break; 
                                        case $hFriday;
                                            if(isset($lastFriday[$currentVendor])){$lastFriday[$currentVendor] = $lastFriday[$currentVendor] + 1; }else{$lastFriday[$currentVendor] = 1;}
                                            //print $currentVendor." ".$realInvoiceDate.'<br>';
                                           break;
                                           
                                        default:
                                            if(isset($superOld[$currentVendor])){$superOld[$currentVendor] = $superOld[$currentVendor] + 1; }else{$superOld[$currentVendor] = 1;}
                                            //print $currentVendor." ".$realInvoiceDate.'<br>';
                                            break;
                                    }
                                          
                                    if(in_array($value3,$newFiles)){
                                       $headerTotal = trim(substr($aline,588,14));
                                       $customerNumber = trim(substr($aline,5,20));
                                       //track invoices used so I can loop this after and look for doubles
                                       $usedInvoiceNumber[count($usedInvoiceNumber)] = array('invoiceNo'=>$currentInvoiceNumber,'file'=>$value3,'dcn'=>$customerNumber,'date'=>$realInvoiceDate,'total'=>$headerTotal,'house' => $key2);
                                    }
                            }
                        
                            
                                //gather data about this invoice & check for known issues
                                if(in_array($value3,$newFiles)){    
                                  $calculatedLineTotal += trim(substr($aline,290,10)) + trim(substr($aline,568,10)) + trim(substr($aline,578,10));
                                  if(trim(substr($aline,280,10)) == ""){$error4016 = TRUE;}//transaction Quantity
                                  if(trim(substr($aline,290,10)) == ""){$error4016 = TRUE;}//transaction Volume
                                  if(trim(substr($aline,543,10)) == ""){$error4016 = TRUE;}//Price
                                  if((trim(substr($aline,290,10)) > 0) && (trim(substr($aline,280,10) < 0))){$errorPositveCreditLine = TRUE;}//Price
                                  if(!mb_detect_encoding($aline, 'ASCII', true)){$errorNonEnglish = TRUE;}                                                          
                                  
                                }    
                            
                        }
                     }
                    
                     //check one more time cause we are at the end of the file aka the last invoice
                            if($headerTotal != 0){  
                                //check for unbalanced now

                                    if(in_array($value3,$newFiles) && (abs(abs($headerTotal) - abs($calculatedLineTotal)) >= 0.70) && ($headerTotal > 0)){  
                                        //print $key." ".$lastInvoiceNum." ".$headerTotal." = ".$calculatedLineTotal." in ".$value3."<br>";
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$currentInvoiceNumber.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Unbalanced,'.$key2.','.$value3."\r\n";
                                    }
                                    //error 4016
                                    if($error4016){
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$currentInvoiceNumber.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Error 4016,'.$key2.','.$value3."\r\n";
                                    }
                                    //non english error
                                    if($errorNonEnglish){
                                        $fileIssueLog .= $key.',,'.$realInvoiceDate.','.$currentInvoiceNumber.','.$customerNumber.','.$calculatedLineTotal.',,'.$today.',File Bot,,,Error Non English,'.$key2.','.$value3."\r\n";
                                    }                          
                                }
                     
                     
                    //should we track the file?
                    switch($fileDateStamp){
                        case $saturday;
                                   $trackFile = TRUE; 
                                break;
                            case $sunday;
                                    $trackFile = TRUE; 
                                break;
                            case $monday;
                                    $trackFile = TRUE; 
                                break;
                            case $tuesday;
                                    $trackFile = TRUE; 
                                break;
                            case $wednesday;
                                    $trackFile = TRUE; 
                                break;
                            case $thursday;
                                    $trackFile = TRUE; 
                                break;
                            case $friday;
                                    $trackFile = TRUE; 
                                break; 
                    }
                    
                    
                    if($trackFile){
                        //print $key.' --> tracking 1. '.$currentVendor.'<br>';
                        switch($fileDateStamp){
                            case $saturday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][0]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][0] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][0] + $invoicesInFile;
                                    //print 'Saturday = '.$vendorConfig['filesReceived'][$key2][$currentVendor][0].'<br>';
                                break;
                            case $sunday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][1]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][1] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][1] + $invoicesInFile;
                                    //print 'Sunday = '.$vendorConfig['filesReceived'][$key2][$currentVendor][0].'<br>';
                                break;
                            case $monday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][2]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][2] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][2] + $invoicesInFile;
                                    //print 'Monday = '.$vendorConfig['filesReceived'][$key2][$currentVendor][0].'<br>';
                                break;
                            case $tuesday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][3]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][3] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][3] + $invoicesInFile;
                                    //print 'Tuesday = '.$vendorConfig['filesReceived'][$key2][$currentVendor][0].'<br>';
                                break;
                            case $wednesday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][4]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][4] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][4] + $invoicesInFile;
                                break;
                            case $thursday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][5]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][5] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][5] + $invoicesInFile;
                                break;
                            case $friday;
                                    $vendorConfig['filesReceived'][$key][$currentVendor][6]++;
                                    $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][6] = $vendorConfig['invoiceInFilesReceived'][$key][$currentVendor][6] + $invoicesInFile;
                                break; 
                        }
                    }
                      
                    
                }else{
                    print 'Error opening file :'.$deepScan.$value3.'<br>';
                }
                 fclose($file);
                 if($trackFile == FALSE){
                     if(unlink($deepScan.'/'.$value3)){
                         $deepScan.'/'.$value3;
                         $oldDataDetected = TRUE;
                     }else{
                         print "<h2>Old data detected but I couldn't delete it... must be server permission errors again!</h2>";
                         print $deepScan.'/'.$value3;
                     }
                 }
            } 
        }
    
    
        
    }//
}


//do stuff with our info

foreach($usedInvoiceNumber as $key => $val){//outer loop
    $findOnce = 0;
    foreach($usedInvoiceNumber as $key2=>$val2){
        if($val['invoiceNo'] == $val2['invoiceNo']){
            if($findOnce != 0){
                    //print $findOnce.') '.$val['invoiceNo'].' '.$val['house'].' ~ '.$val2['invoiceNo'].' '.$val2['house'].'<br>';
                //write to the file issue lag that we have dups in todays load
                array('invoiceNo'=>$currentInvoiceNumber,'file'=>$value3,'dcn'=>$customerNumber,'total'=>$headerTotal,'house' => $key2);
                $fileIssueLog .= $val['house'].' ~ '.$val2['house'].',,'.$val['date'].','.$val2['invoiceNo'].','.$val2['dcn'].' ~ '.$val2['dcn'].','.$val['total'].' ~ '.$val2['total'].',,'.$today.',File Bot,,,Dup Detected,'.$val['house'].' ~ '.$val2['house'].','.$val['file'].' ~ '.$val2['file']."\r\n";
                unset($usedInvoiceNumber[$key]);
            }
            $findOnce++;
        }
    }
}
set_time_limit(5000);
//var_dump($vendorConfig['filesReceived']['Amalgamated']);
//var_dump($troubleShoot);
//var_dump($vendorConfig['invoicesReceived']);
//echo $dow.'<br>';
$tableDisplay = '<table border="1"><tr><th>&nbsp;</th><th>&nbsp;</th><th colspan="2">Danger</th><th colspan="2">Sat, '.$gSaturday.'</th><th colspan="2">Sun, '.$gSunday.'</th><th colspan="2">Mon, '.$gMonday.'</th><th colspan="2">Tue, '.$gTuesday.'</th><th colspan="2">Wed, '.$gWednesday.'</th><th colspan="2">Thu, '.$gThursday.'</th><th colspan="2">Fri, '.$gFriday.'</th><th rowspan="2">Total</th><th rowspan="2">Notes</th></tr>';
$tableDisplay .= '<tr><th>Vendor</th><th>House</th><th>+/-</th><th>Last Friday</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th><th>Files</th><th>Invoice</th></tr>';
foreach($vendorConfig['expected'] as $key => $val){
    foreach($val as $key2 =>$val2){
        //if($vendorConfig[$key][$key2][0]['Sat'] != 0){
        $tableDisplay .= '<tr><td>'.$key.'</td><td>'.$key2.'</td>';
        $tableDisplay .= '<td>'.$superOld[$key2].'</td><td>'.$lastFriday[$key2].'</td>';
         $dayCounter = 0;
         $invoiceTotal = 0;
         foreach($val2 as $key3 => $val3){
                    //colour code issue logic here via $val3
                    //d = daily
                    //w = weekly
                    //n = nothing to worry about
                    //0 = not ready for CIMS yet
                    //m = maybe.. not all the time
                    $warning = '';
                    $warning2 = '';
                    if(($val3 == 'd' || $val3 == 'w') && ($vendorConfig['filesReceived'][$key][$key2][$dayCounter] == 0 && $vendorConfig['invoicesReceived'][$key][$key2][$dayCounter] < 5)){$warning = ' style="color:blue"';}
                    if($vendorConfig['invoicesReceived'][$key][$key2][$dayCounter] != 0 && $dow == $dayCounter ){$warning2 = ' style="color:red"';}
                                        
                     $tableDisplay .= '<td><span'.$warning.'>'.$vendorConfig['filesReceived'][$key][$key2][$dayCounter].' ('. $vendorConfig['invoiceInFilesReceived'][$key][$key2][$dayCounter].')</span></td><td '.$warning2.'>'.$vendorConfig['invoicesReceived'][$key][$key2][$dayCounter].'</td>';
                     $invoiceTotal = $invoiceTotal + $vendorConfig['invoiceInFilesReceived'][$key][$key2][$dayCounter];
                     $dayCounter++;
                     
         }
        //print '<td>'.($invoiceTotal + $lastFriday[$key2] + $superOld[$key2]).'</td></tr>';//worng, double the count
        $weekTotal = $invoiceTotal-$superOld[$key2]-$lastFriday[$key2];
        if($weekTotal == 0 && strtolower(trim($vendorNote[$key2])) != 'legacy not used' && strtolower(trim($vendorNote[$key2])) != 'study'){
            $tableDisplay .= '<td><span style="color: red">'.$weekTotal.'</span></td>';
        }else{
            $tableDisplay .= '<td>'.$weekTotal.'</td>';
        }
        $tableDisplay .= '<td>'.$vendorNote[$key2].'</td></tr>';
        //}
    }
}
$tableDisplay .= '</table><br>';


$tableDisplay .= $report;

if(!isset($_GET['ceanUpReload']) && $_GET['ceanUpReload'] != 'TRUE'){
//write counting to a log
$fp = fopen($target_dir.'invoiceCountingLog.csv', 'w');
fwrite($fp, helpZeroPadCSV($invoiceCountingLog)) or die("Can't create count file");
fclose($fp);

$fp = fopen($target_dir.'invoiceIssueLog.csv', 'w');
fwrite($fp, helpZeroPadCSV($fileIssueLog)) or die("Can't create issue file");
fclose($fp);
}else{
    $tableDisplay .= "Clean up run done, you should still have the data in the log files if there is any bad from the vendors.";
}


//reload cause old data detected
if($oldDataDetected){header("Location: ./itnFileBot.php?ceanUpReload=TRUE");}

print $tableDisplay;

//functions
function makeDir($path)
{
     return is_dir($path) || mkdir($path);
}

function helpZeroPadCSV($x){
    //$x = str_replace(",0",",'0",$x);
    return $x;        
}
?>