<?php
if (!session_id()) {
    session_cache_limiter('private');
    session_cache_expire(0);
    session_start();

}
// session_start();
// session_cache_limiter('private');
// session_cache_expire(0);
//script to join data from two sheets together
//the code above as to be the first thing seen if we want the power to share data between link clicks.. or what is known as a session...

//further remove any chance of cache 
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
error_reporting(E_ALL);

$sheetIssueNoDisplayXLSX = TRUE;//let the user know there is a sheet error, well flip this to false when we have data
$sheetIssueNoDisplayCSV = TRUE;//let the user know there is a sheet error, well flip this to false when we have data

//all our scripts right access to a log.. not important to joining two sheets usally I just call in a recycled script like this...
//   include_once '.\_timestampLogger.php';
//but here is the code from it
if(!isset($_SESSION['name'])){//me stating that if we don't know who is running the script let's figure that out...
$nameTemp1 = explode("\\",$_SERVER['REMOTE_USER']);//Remote_User is basically the windows user name the person logged into thier computer with
$nameTemp2 = explode(".", $nameTemp1[1]);//at Compass we are firstname dot lastname so I can break appart the username and get a last name... hench the last names in the file area
$_SESSION['name'] = $nameTemp2[1];//$nameTemp2 has two pieces thanks to my explode call above... [0] is firstname [1] is lastname
if($_SESSION['name'] == ""){$_SESSION['name'] = 'DevBox';}//my laptop is not setup to pass the remote user name so if what I tried failed to fill that variable I know we are on my dev box... I just have a folder called devbox to stick reports in and figure things out.
}

//so now we can tell which server we are on let's setup a variable so we can find our files...
  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\".$_SESSION['name']."\\";//prod server, our folders are not on the same server as the code so we have to call over a network
        $target_dirX = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";//for log file
        }else{
         $target_dir = $_SESSION['name']."/";//dev server, all local.. super easy to do
         $target_dirX = "C:\\SmartInvoice\\";
        
      }

//old though, still happens, at first I wasn't sure if I should log access to our tools.. so I made this.. this just writes who called what script and when.. keeps 1000 records and them deletes 1001 kinda thing
$filename=$target_dirX."lastActionTimeStamp.tmp";
$log = file_get_contents($filename);
$logLines = explode("\r\n", $log);
#$logString = time().' ~ '.$_SERVER['REMOTE_USER'].' ~ '.$_SERVER['REQUEST_URI']."\r\n";
foreach($logLines as $logKey => $logVal){
    if($logKey < 10000){//only keep 10000 entries
   # $logString .= $logVal."\r\n";
    }else{
        break;
    }
}
#file_put_contents($filename, $logString );

//now we get into the more unique part of this script, some stuff is recycled but not like the first 50ish lines

//Excel is a pain to talk too but smarter people than me have made a code api to help so we are going to call it in for the help..basically other peoples scripts
/** Include PHPExcel_IOFactory */
require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
 
//place holder
$ubberData = array();

//here we will take a huge risk and assume the person running the script only has the two files in their folder... but we are going to need to get the file names so...
$files1 = scandir($target_dir);
//now loop through the files, one should be .xls and the other .csv so we can work with that... again a risk but let's hope the person knows thatforeach($files1 as $key=>$value){
foreach($files1 as $key=>$value){//grab the data first in this loop.. we will do another loop to marry it later
    //print $value."<br>";//debugging
    if($key > 1 && !strpos($value,"~")){//key is like the data slot number.. when doing a scandir [0] & [1] are junk.. our files will be in [2] & [3] ... so there is 4 slots here.. 0 to 3
        //now is this the excel or csv?? gonna do a string test for the file extention
        
        if(strpos($value,'csv')){//we have the CSV file so lets scope our our data
            //debug variable.. I want to see the file name while building 
            //print $value."<br>";//br will add a new line on the browser screen
            $csvExact = array_map('str_getcsv', file($target_dir.$value));// load the file to an array
            foreach($csvExact as $key2=>$value2){
                set_time_limit(5000);//gotta up the memory here, this script is a hog
                //$value2[1] is now colum B from the sheet and I can loop from the top of the sheet to the bottom
                //print $value2[1]." ".$value2[15]." ".$value2[16].'<br>';// single or double quote usally doesn't matter in PHP but you have to use the same in the line of code
                //we only care P [15] and Q [16] which are the actual Visa & Master card.. the other stuff is like pre-pay and out of scope
                if($value2[15] != 0 || $value2[17] != 0){//that means not equal to zero... so if either have a value we want it
                    //print $value2[1]." ".$value2[15]." ".$value2[16].'<br>';
                    //now we have to check if the this is net new to the ubber array or if we are just adding things to an existing record
                    if(isset($ubberData[$value2[1]])){//not new.. just add
                        $ubberData[$value2[1]]['csvVisa'] = $ubberData[$value2[1]]['csvVisa'] + $value2[17];
                        $ubberData[$value2[1]]['csvMasterCard'] = $ubberData[$value2[1]]['csvMasterCard'] + $value2[15];
                        $sheetIssueNoDisplayCSV = FALSE;//we would hit here for sure if we are good on the CSV file.
                    }else{
                        // new so setup the array entery $ubberData[unit number] = csvVisa, csvMasterCard, xlsxVisa, xlsxMastercard.. we want place holders for all 
                        $ubberData[$value2[1]] = array('csvVisa' => $value2[17],'csvMasterCard' => $value2[15], 'xlsxVisa' => 0, 'xlsxMasterCard' => 0);
                        $sheetIssueNoDisplayCSV = FALSE;
                    }//end if isset
                }//end if we have visa or mastercard data
            }//end of looping through csv
            
        }//end of the csv block
        
        if(strpos($value,'xlsx')){//this is the excel file so let's scope that data
             //pretty much recycled from the ZipThru code.. not 100% sure how this works.. this is the code I'm borrowing to read Excel
            $inputFileName = $target_dir.$value;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                //echo $objPHPExcel->getSheetCount(),' worksheet',(($objPHPExcel->getSheetCount() == 1) ? '' : 's'),' loaded<br /><br />';
                $loadedSheetNames = $objPHPExcel->getSheetNames();
                foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                        //echo '<b>Worksheet #',$sheetIndex,' -> ',$loadedSheetName,'</b><br />';
                        $objPHPExcel->setActiveSheetIndexByName($loadedSheetName);
                        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,false,true);
                        //var_dump($sheetData);
                        if(count($sheetData) > 3){$sheetIssueNoDisplayXLSX = FALSE;}//there would be more then 3 rows.. so if that is true we are good, nothing locking this file.
                        foreach($sheetData as $sheetKey=>$sheetRow){
                            set_time_limit(5000);
                            //print $sheetKey." ".$sheetCell."<br>";
                            
                            //print $sheetRow['C']."<br>"; that is the DBA name, card type is O, value is Q
                            
                            //so make the unit number, well explode on - and take [2] ... if others start using this ... this might break
                            if(strpos($sheetRow['A'],"/") && !strpos($sheetRow['A'],"t")){//seems the rows we want all have a slash in the date field.. so look for that... except when we see the letter T.. we don't want that either
                                    $tempUnitNumber = explode("-",$sheetRow['C']);
                                    $unitNumber = trim($tempUnitNumber[2]);
                                    $tempVisa = 0;
                                    $tempMC = 0;
                                    if(trim($sheetRow['O']) == 'Visa'){$tempVisa = $sheetRow['Q'];}//little different than the CSV.. we have to test card type and compensate before we figure out the array stuff
                                    if(trim($sheetRow['O']) == 'Mastercard'){$tempMC = $sheetRow['Q'];}
                                    //echo ini_get('memory_limit')."<br>";
                                    //print $sheetRow['A']."  ".$unitNumber."  ".$sheetRow['A']."  ".$unitNumber[2]."<br>";
                                    if(isset($ubberData[$unitNumber])){//same deal as the CSV.. test to see if this is a new entry in the array, if so, set it up , if not do the math
                                        $ubberData[$unitNumber]['xlsxVisa'] = $ubberData[$unitNumber]['xlsxVisa'] + $tempVisa;
                                        $ubberData[$unitNumber]['xlsxMasterCard'] = $ubberData[$unitNumber]['xlsxMasterCard'] + $tempMC;
                                        //print "hhhhhhhh<br>";
                                        $sheetIssueNoDisplayXLSX = FALSE;//100% we would hit here.
                                    }else{
                                        $ubberData[$unitNumber] = array('csvVisa' => 0,'csvMasterCard' => 0, 'xlsxVisa' => $tempVisa, 'xlsxMasterCard' => $tempMC);
                                        //print "dsfaseraewr<br>";
                                    }
                                    
                            }
                            /*this was too deep into the sheet 
                            foreach($sheetRow as $sheetRowKey => $sheetCell){//inner loop
                                //print $sheetRowKey." ".$sheetCell."<br>"; //finally in our sheet $sheetRowKey is column name so A B C.. $sheetCell is the data in the cell
                            }
                            */
                        }
                        //echo '<br />';
                }
                                unset($objPHPExcel);
                                unset($sheetData);
                        }

                    }
                }

//var_dump($givexTab);

if($sheetIssueNoDisplayXLSX || $sheetIssueNoDisplayCSV){ //if either is true throw an error to screen    
    
    if($sheetIssueNoDisplayXLSX){print "<h2>You have an error with your XLSX file, it's missing or protected. To unprotect it just open and save the sheet, it's wierd like that.</h2><br>";}
    if($sheetIssueNoDisplayCSV){print "<h2>You have an error with your CSV file.. I think it's missing.</h2>";}            
    
}else{                
    ksort($ubberData);//make this lowest to highest unit number
    //now that we have all the data normalised to one array we can loop they output
    
    //make the real grid for the copy and pasting
        print '<table border="1"><tr><th rowspan="2">Unit No.</th><th colspan="2">NS Report</th><th colspan="2">FP Report</th><th rowspan="2">Balance</th></tr>';
        print '<tr><th>Visa</th><th>MC</th><th>Visa</th><th>MC</th></tr>';
            
    foreach($ubberData as $key => $value){
        //used that print line to see what was happening during dev
        //print "Unit ".$key." has ".$value['csvVisa']." csvVisa and ".$value['csvMasterCard'].' csvMasterCard & '.$value['xlsxVisa'].' xlsxVisa and '.$value['xlsxMasterCard'].' xlsxMasterCard<br>';

        $balance = abs(round(($value['csvVisa'] + $value['csvMasterCard']) - ($value['xlsxVisa'] + $value['xlsxMasterCard']),2));//make sure we stick to 0.00 format and return the result in a positive value
        print '<tr><td><b>'.$key.'</b></td><td>'.$value['csvVisa'].'</td><td>'.$value['csvMasterCard'].'</td><td>'.$value['xlsxVisa'].'</td><td>'.$value['xlsxMasterCard'].'</td><td>'.$balance.'</td></tr>';
    }
    
    print '</table>';
}