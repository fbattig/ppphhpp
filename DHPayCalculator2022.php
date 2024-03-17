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
      alert("Table Copied!");
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
error_reporting(E_ERROR | E_PARSE);//skip errors

print '<a href=".\index.php">Home</a><hr>';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){//needed to get the log file location
       // $target_dir2 = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";
        $looksStuffUpFile = '\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\randomUnitSheets\Units information.xlsx';
        
      }else{
         //$target_dir2 = "";
         $looksStuffUpFile = './checklist/randomUnitSheets/Units information.xlsx';
      }
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
$files1 = scandir($target_dir);

//files we want
$freedomPaySheet = '';
$jdeLogReport = '';
//we need the unit name from The Looks Stuff Up Machine, we will try Melissa's file since the name seems to stay static

if(!file_exists($looksStuffUpFile)){ 
    //print "BINGO! We have the Unit File.<br><br>";
    $looksStuffUpFile = "";
}

foreach($files1 as $key=>$value){
    if(strpos($value,'TransactionRequest') !== false){
        $freedomPaySheet = $value;//last write would win
    }

    if(strpos($value,'-') !== false){
        $jdeLogReport = $value;//last write would win, this filename looks like 2022-03-11.csv so room for error here
        //print $jdeLogReport."<br><br>";
    }
    
}

if($freedomPaySheet !== '' && $looksStuffUpFile !== '' && $jdeLogReport !== ''){//make sure we have all the data we need.
    //we have all the sheets we need yay!
    $file = fopen($target_dir.$freedomPaySheet, "r");

    $dateInFreedomPayFile = '';
    $lineDataTemp = array();//just for coding
    $freedomPayStoreDataConsolidated = array();
    $freedomPayStoreTransationLineData = array();
    $freedomPayStoreTransationOverFiftyBucks = array();
    $unitNamesFromLooksStuffUp = array();
    
    
    if ($file) {
          while (!feof($file)) {
                   $aline = fgets($file);
                   $lineData = explode(",",$aline);
                   //if $lineData[0] = DecisionFraud then it's a card transaction to check out
                   //if $lineData[0] starts with All Transactions By Business Date it will have the date
                    //if(stripos($lineData[5],'SPOON')){print $lineData[5]."<br>";} // this doesn't seem to work anymore
                    $omitTester = explode("-",$lineData[5]);
                    
                   if(trim($lineData[0]) == 'Fraud Decision' && !strpos($lineData[5],'CHARTWELLS') && $omitTester[0] != 'SPOONFED' && trim($lineData[27]) != 'Void'){
                       //print $lineData[0].$lineData[17]."<br>";
                       //do some mid card math
                       //DBA = $lineData[5]
                       //CARD TYPE = $lineData[19]
                       //ApprovedAmount = $lineData[17]
                       //Store ID = $lineData[7] *probably won't use this
                       //Card = $lineData[20]
                       //Invoice Number = $lineData[14]
                       $tempUnitNumber = substr(trim($lineData[5]), -5);
                       
                       //something like this for dup detection, we can loop the file one more time later and snif them out
                       //print $tempUnitNumber." = ".$lineData[5]."<br>";
                       $lineDataTemp[count($lineDataTemp)] = $tempUnitNumber." | ".$lineData[17]." | ".$lineData[20];
                       
                       //transactions over 50
                       if($lineData[17] > 50){
                           $freedomPayStoreTransationOverFiftyBucks[$tempUnitNumber][count($freedomPayStoreTransationOverFiftyBucks)] = array('DBA' => $lineData[5],'Card' => $lineData[19], 'Amount' => $lineData[17], 'StoreID' => $lineData[7], 'CardNo' => $lineData[20], 'InvoiceNo' => $lineData[14]);
                       }
                       
                       //now we need to make our consolidated Amex, Visa, Mastercard
                       
                       if($lineData[17] > 0){
                           if(isset($freedomPayStoreDataConsolidated[$tempUnitNumber])){
                               //add to that card total
                               $freedomPayStoreDataConsolidated[$tempUnitNumber][$lineData[19]] = $freedomPayStoreDataConsolidated[$tempUnitNumber][$lineData[19]] + $lineData[17];
                           }else{
                               //first hit at that unit so setup the array   
                               $freedomPayStoreDataConsolidated[$tempUnitNumber] = array("DBA" => $lineData[5],"Amex" =>  0, "Visa" =>  0, "Mastercard" =>  0, "APIAmex" => 0, "APIVisa" => 0, "APIMC" => 0);
                               $freedomPayStoreDataConsolidated[$tempUnitNumber][$lineData[19]] = $freedomPayStoreDataConsolidated[$tempUnitNumber][$lineData[19]] + $lineData[17];
                           }
                       }
                       
                       
                   }
                   if(strpos($lineData[0],'All Transactions By Business Date') !== false){
                       //shoud be the line with the date in it
                       $tempDate = explode(":",$lineData[0]);
                       $dateInFreedomPayFile = $tempDate[1];
                   }
                   
          }
    }
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
    
    
    $file = fopen($target_dir.$jdeLogReport, "r");
    $dateInJDELogReport = '';
    $mealPlanStart = '';
    $mealPlanEnd = '';
    if ($file) {
          while (!feof($file)) {
                   $aline = fgets($file);
                   $lineData = explode(",",$aline);
                   $testFirstField = str_replace('"','',trim($lineData[0]));
                   
                   if($testFirstField == 'Unit'){//this should always be the first row in the report, we need to find all the meal plan crap and make a total
                       foreach($lineData as $key=>$val){
                           if(str_replace('"','',trim($val)) == 'Amex'){$mealPlanStart = $key + 1;}
                           if(str_replace('"','',trim($val)) == 'Transaction Count'){$mealPlanEnd = $key;}
                       }
                   }
                   //print $mealPlanStart." to ".$mealPlanEnd."<br>";
                   
                   if($testFirstField != 'Unit' && $testFirstField != 'Totals' && $testFirstField != ''){
                        //print $aline[0]."<br>";
                        //first write wins
                       if($dateInJDELogReport == ''){
                            $tempDate = explode("-",str_replace('"','',$lineData[1]));
                            $dateInJDELogReport = $tempDate[1].'/'.$tempDate[2].'/'.$tempDate[0];
                       }
                       //print $lineData[0]." = ".$lineData[7]."<br>";
                        //$lineData[0] = unit number
                        //$lineData[1] = Transaction Date
                        //$lineData[2] = Download Date
                        //$lineData[3] = Total Sales (sum of all brands)
                        //$lineData[4] = Discounts
                        //$lineData[5] = GST/HST
                        //$lineData[6] = PST
                        //$lineData[7] = Visa
                        //$lineData[8] = MasterCard
                        //$lineData[9] = Amex
                        //$lineData[10] = Taxable Dining
                        //$lineData[11] = Tax Exempt Dining
                        //$lineData[12] = Trent Cash
                        //$lineData[13] = Flex Account
                        //$lineData[14] = Main Meal Plan
                        //$lineData[17] = Transaction Count
                       //if(isset($freedomPayStoreDataConsolidated[trim($lineData[0])])){
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['totalSales'] = $lineData[3];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['discounts'] = $lineData[4];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['GSTHST'] = $lineData[5];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['PST'] = $lineData[6];                            
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['APIVisa'] = $lineData[7];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['APIMC'] = $lineData[8];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['APIAmex'] = $lineData[9];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['taxableDining'] = $lineData[10];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['taxExemptDining'] = $lineData[11];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['trentCash'] = $lineData[12];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['mealPlan'] = $lineData[13];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['mainMealPlan'] = $lineData[14];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['flexAccount'] = $lineData[15];
                            //$freedomPayStoreDataConsolidated[trim($lineData[0])]['bridgeBucks'] = $lineData[16];
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['transactionCount'] = $lineData[17];
                            
                            //loop throught the meal plan stuff
                            
                            for($i = $mealPlanStart; $i < $mealPlanEnd;$i++){
                                //print $i.'<br>';
                                $freedomPayStoreDataConsolidated[trim($lineData[0])]['mealPlanTotal'] = $freedomPayStoreDataConsolidated[trim($lineData[0])]['mealPlanTotal'] + $lineData[$i];
                            }
                            
                       //}else{
                            /*
                            if(trim($lineData[9]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['APIAmex'] = $lineData[9];}
                            if(trim($lineData[8]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['APIVisa'] = $lineData[7];}
                            if(trim($lineData[7]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['APIMC'] = $lineData[8];}
                            if(trim($lineData[10]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['taxableDining'] = $lineData[10];}
                            if(trim($lineData[11]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['taxExemptDining'] = $lineData[11];}
                            if(trim($lineData[12]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['trentCash'] = $lineData[12];}
                            if(trim($lineData[13]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['flexAccount'] = $lineData[13];}
                            if(trim($lineData[14]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['mainMealPlan'] = $lineData[14];}
                            if(trim($lineData[15]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['transactionCount'] = $lineData[15];}
                            if(trim($lineData[3]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['totalSales'] = $lineData[3];}
                            if(trim($lineData[4]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['discounts'] = $lineData[4];}
                            if(trim($lineData[5]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['GSTHST'] = $lineData[5];}
                            if(trim($lineData[6]) != ''){$freedomPayStoreDataConsolidated[trim($lineData[0])]['PST'] = $lineData[6];}
                            
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['Amex'] = 0;
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['Visa'] = 0;
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['Mastercard'] = 0;
                            $freedomPayStoreDataConsolidated[trim($lineData[0])]['DBA'] = '<i>Not Detected in FP</i>';
                             *  */
                            
                      // }
                        
                   }
          }
    }
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
    
    
    //now lets get some unit info to help the user with a unit name, this is browoing a file from the looks stuff up machine.
    /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
        $inputFileName = $looksStuffUpFile;
        //code based on the looks stuff up machine
        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                $firstRow = true;
                $unitNumberIndex = "";
                $unitNameIndex = "";
                        
                foreach($sheetData as $key=>$val){
                    //find which cell has unit number and name
                    if($firstRow){
                            //print $val["A"]." | ".$val["B"]." | ".$val["C"]."<BR>";
                            //play seek and search...got be in one of these
                            if($val["A"] == 'Unit Number'){$unitNumberIndex = "A";}
                            if($val["B"] == 'Unit Number'){$unitNumberIndex = "B";}
                            if($val["C"] == 'Unit Number'){$unitNumberIndex = "C";}
                            if($val["D"] == 'Unit Number'){$unitNumberIndex = "D";}
                            if($val["E"] == 'Unit Number'){$unitNumberIndex = "E";}
                            if($val["F"] == 'Unit Number'){$unitNumberIndex = "F";}
                            if($val["G"] == 'Unit Number'){$unitNumberIndex = "G";}
                            if($val["H"] == 'Unit Number'){$unitNumberIndex = "H";}
                            //play seek and search...got be in one of these
                            if($val["A"] == 'Unit Name'){$unitNameIndex = "A";}
                            if($val["B"] == 'Unit Name'){$unitNameIndex = "B";}
                            if($val["C"] == 'Unit Name'){$unitNameIndex = "C";}
                            if($val["D"] == 'Unit Name'){$unitNameIndex = "D";}
                            if($val["E"] == 'Unit Name'){$unitNameIndex = "E";}
                            if($val["F"] == 'Unit Name'){$unitNameIndex = "F";}
                            if($val["G"] == 'Unit Name'){$unitNameIndex = "G";}
                            if($val["H"] == 'Unit Name'){$unitNameIndex = "H";}
                            
                            $firstRow = false;
                        }else{
                           $unitNamesFromLooksStuffUp[trim($val[$unitNumberIndex])] = trim($val[$unitNameIndex]);   
                        }
                    }

                unset($objPHPExcel);
                unset($sheetData ); 
        
    //this is the output
    print 'Freedom Pay Sheet '.$freedomPaySheet.' with date of '.$dateInFreedomPayFile.".<br>";
    print 'JDE Log Report '.$jdeLogReport.' with date of '.$dateInJDELogReport.".<br><br>";
    /*was for dev out
    foreach($lineDataTemp as $valueOut){
        print $valueOut."<br>";
    }
    */
    ksort($freedomPayStoreDataConsolidated);
    //main prntout
    $totalFPVisa = 0;
    $totalAPIVisa = 0;
    $totalFPMC = 0;
    $totalAPCMC = 0;
    $totalFPAmex = 0;
    $totalAPIAmex = 0;
    $totalJDEAPI = 0;
    
    print "<table border=\"1\"><tr><th>Unit No.</th><th>DBA</th><th>Unit Name</th><th>FP Visa</th><th>API Visa</th><th>+/-</th><th>FP MC</th><th>API MC</th><th>+/-</th><th>FP Amex</th><th>API Amex</th><th>+/-</th><th>Dashboard<br>Meal Plans</th><th>JDE API Total</th><th>+/-</th></tr>";
    //. "<th>Discounts</th><th>Gst/Hst</th><th>Pst</th><th>Taxable Dining</th><th>Tax Exempt Dining</th><th>Trent Cash</th><th>Flex Account</th><th>Main Meal Plan</th><th>Transaction Count</th></tr>";
    foreach($freedomPayStoreDataConsolidated as $key=>$value){
        $totalFPVisa = $totalFPVisa + makeSureWeHaveANumber($value['Visa']);
        $totalAPIVisa = $totalAPIVisa + makeSureWeHaveANumber($value['APIVisa']);
        $totalFPMC = $totalFPMC + makeSureWeHaveANumber($value['Mastercard']);
        $totalAPCMC = $totalAPCMC + makeSureWeHaveANumber($value['APIMC']);
        $totalFPAmex = $totalFPAmex + makeSureWeHaveANumber($value['Amex']);
        $totalAPIAmex = $totalAPIAmex + makeSureWeHaveANumber($value['APIAmex']);
        $totalJDEAPI = $totalJDEAPI + makeSureWeHaveANumber($value['mealPlanTotal']);
        //print 'Unit '.$key." had Amex of $".$value['Amex'].' and Visa $'.$value['Visa'].' and Mastercard $'.$value['Mastercard'].'<br>';
        print "<tr><td>".$key."</td><td>".$value['DBA']."</td><td>".$unitNamesFromLooksStuffUp[$key]."</td><td>".makeSureWeHaveANumber($value['Visa'])."</td><td>".makeSureWeHaveANumber($value['APIVisa'])."</td><td>".testForBalance($value['Visa'],$value['APIVisa'])."</td><td>".makeSureWeHaveANumber($value['Mastercard'])."</td><td>".makeSureWeHaveANumber($value['APIMC'])."</td><td>".testForBalance($value['Mastercard'],$value['APIMC'])."</td><td>".makeSureWeHaveANumber($value['Amex'])."</td><td>".makeSureWeHaveANumber($value['APIAmex'])."</td><td>".testForBalance($value['Amex'],$value['APIAmex'])."</td><td></td><td>".makeSureWeHaveANumber($value['mealPlanTotal'])."</td><td></td></tr>";
                //"<td>".makeSureWeHaveANumber($value['discounts'])."</td><td>".makeSureWeHaveANumber($value['GSTHST'])."</td><td>".makeSureWeHaveANumber($value['PST'])."</td><td>".makeSureWeHaveANumber($value['taxableDining'])."</td><td>".makeSureWeHaveANumber($value['taxExemptDining'])."</td><td>".makeSureWeHaveANumber($value['trentCash'])."</td><td>".makeSureWeHaveANumber($value['flexAccount'])."</td><td>".makeSureWeHaveANumber($value['mainMealPlan'])."</td><td>".makeSureWeHaveANumber($value['transactionCount'])."</td></tr>";
    }
    print '<tr><td></td><td></td><td><b>Totals</b></td><td>'.$totalFPVisa.'</td><td>'.$totalAPIVisa.'</td><td></td><td>'.$totalFPMC.'</td><td>'.$totalAPCMC.'</td><td></td><td>'.$totalFPAmex.'</td><td>'.$totalAPIAmex.'</td><td></td><td></td><td>'.$totalJDEAPI.'</td><td></td></tr>';
    print '</table>';

    
    /*
    //var_dump($lineDataTemp);
    //loop back through looking for dup transations
    $file = fopen($target_dir.$freedomPaySheet, "r");
    $potentialDup = array();
    if ($file) {
          while (!feof($file)) {
                   $aline = fgets($file);
                   $sussCount = 0;
                   $lineData = explode(",",$aline);
                   //if $lineData[0] = DecisionFraud then it's a card transaction to check out
                   //if $lineData[0] starts with All Transactions By Business Date it will have the date
                   //print $lineData[0];
                   if(trim($lineData[0]) == 'Fraud Decision'){
                       $tempUnitNumber = explode("-",$lineData[5]);
                       $tester = $tempUnitNumber[2]." | ".$lineData[17]." | ".$lineData[20];
                       foreach($lineDataTemp as $value){
                           
                           if($value == $tester){$sussCount++;}
                       }
                       
                       //if sussCount is more than two we have a potential dup
                       if($sussCount > 1){
                           //DBA = $lineData[5]
                           //CARD TYPE = $lineData[19]
                          //ApprovedAmount = $lineData[17]
                          //Store ID = $lineData[7] *probably won't use this
                          //Card = $lineData[20]
                          //Invoice Number = $lineData[14]
                           $tempUnitNumber = explode("-",$lineData[5]);
                                $potentialDup[$tempUnitNumber[2]][count($potentialDup[$tempUnitNumber[2]])] = array('DBA' => $lineData[5],'Card' => $lineData[19], 'Amount' => $lineData[17], 'StoreID' => $lineData[7], 'CardNo' => $lineData[20], 'InvoiceNo' => $lineData[14]);
                       }
                   }
          }
    }
    fclose($file);
    set_time_limit(5000);//extend the script timeout to 5 more minutes
    */
    //now let them know if we found potential dups
    if(count($potentialDup) > 0){
        ksort($potentialDup);
        print "<br><b>These transactions are kinda suss and might be duplcates.</b><br>";
        print "<table><tr><th>Unit No.</th><th>DBA</th><th>Amount</th><th>Type</th><th>Card</th><th>Invoice</th><th>Store ID</th></tr>";
            foreach($potentialDup as $key => $value){
                foreach($value as $val){
                    print "<tr><td>".$key."</td><td>".$val['DBA']."</td><td>".$val['Amount']."</td><td>".$val['Card']."</td><td>".$val['CardNo']."</td><td>".$val['InvoiceNo']."</td><td>".$val['StoreID']."</td></tr>";
                }
            }
        print "</table>";    
    }else{
        print "<br>There was nothing suss in the data regarding duplicate transactions.";
    }
    
        //now let them know transactions over 50
    if(count($freedomPayStoreTransationOverFiftyBucks) > 0){
        ksort($freedomPayStoreTransationOverFiftyBucks);
        print "<br><b>Here are the transactions over 50 dollars.</b><br>";
        print "<table><tr><th>Unit No.</th><th>DBA</th><th>Amount</th><th>Type</th><th>Card</th><th>Invoice</th><th>Store ID</th></tr>";
            foreach($freedomPayStoreTransationOverFiftyBucks as $key => $value){
                foreach($value as $val){
                    print "<tr><td>".$key."</td><td>".$val['DBA']."</td><td>".$val['Amount']."</td><td>".$val['Card']."</td><td>".$val['CardNo']."</td><td>".$val['InvoiceNo']."</td><td>".$val['StoreID']."</td></tr>";
                }
            }
        print "</table>";    
    }else{
        print "<br>There are no 50 dollar transactions.";
    }
    
    
    
    
    
}else{//check for all sheets required has failed
    
    print "I am missing a sheet to work! The Freedom Pay report needs to have TransactionRequest in the title and be a CSV file and can not contain a dash. The JDE file must contain a dash and also be a CSV file, here is an example: 2022-03-11.csv";
}

function makeSureWeHaveANumber($y){
    if(trim($y) == ''){$y = 0;}
    return $y;
}

function testForBalance($x,$y){
    if(trim($y) == ''){$y = 0;}
    if(trim($x) == ''){$x = 0;}
    if(abs(round($x,2)) == abs(round($y,2))){
        return 0;
    }else{
        return '<span style="color:red">'.round(($x-$y),2).'</span>';
    }
}
