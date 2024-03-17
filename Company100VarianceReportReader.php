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


print '<a href=".\index.php">Home</a> | <a href="Company100VarianceReportReader.php">Strick Mode</a> | <a href="'.$_SERVER['REQUEST_URI'].'?NoAuth">No Auth Mode</a><hr>';
ini_set('max_execution_time', 0); // 0 = Unlimited
//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
$files1 = scandir($target_dir);

//files we want
$bams101report = '';
$givexReport = '';
//we need the unit name from The Looks Stuff Up Machine, we will try Melissa's file since the name seems to stay static

foreach($files1 as $key=>$value){
    if(strpos(strtolower($value),'hl0101') !== false){
        $bams101report = $value;//last write would win
    }

    if(strpos(strtolower($value),'cws') !== false){
        $givexReport = $value;//last write would win, this filename looks like 2022-03-11.csv so room for error here
    }
    
}

if($bams101report !== '' && $givexReport !== ''){//make sure we have all the data we need.
    
    //set_time_limit(5000);//extend the script timeout to 5 more minutes
     $bams101ArrayV2 = array();//make a unique ID and stick all instances in a sub array, then if main element is > 1 we have some sort of dup to look at
     $giveCWSArrayV2 = array();

     
    //let loop the Bams report and build our data array.
    /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
        $inputFileName = $target_dir.$bams101report;
        //code based on the looks stuff up machine
        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //A = Location ID
                    //B = DBA Name
                    //C = Currenty Code
                    //D = Terminal ID
                    //E = Batch Number
                    //F = Invoice Number
                    //G = Submitted Date
                    //H = Card Type
                    //I = Cardholder Number
                    //J = Trans Amount
                    //K = Entry Mode
                    //L = Entry Desc
                    //M = Trans Date
                    //N = Trans Time
                    //O = Auth Code
                    //P = Status
                    //Q = I/C Code
                    //R = Service Code
                    //S = Auth
                    //T = Wallet Identifier
                    //U = Airline Tickets Number
                    //V = Transaction Integerity Class
                    //print trim($val["C"])."<br>";
                      if(trim($val["E"]) != "" && strpos(trim($val["H"]),'Card') === false){

                        $tempDistinct = cardTransaltion(trim($val["H"])).substr(trim($val["I"]),-4).str_replace(".00","",trim($val["J"])).trim($val["O"]);
                        if(isset($_GET['NoAuth'])){$tempDistinct = cardTransaltion(trim($val["H"])).substr(trim($val["I"]),-4).str_replace(".00","",trim($val["J"]));}//option to loosen up our distinct creation
                        $bams101ArrayV2[$tempDistinct][count($bams101ArrayV2[$tempDistinct])] = array(
                                            //"BatchNo" => trim($val["E"]),
                                            "CardType" => trim($val["H"]),
                                            "CardNo" => trim($val["I"]),
                                            "TransAmount" => trim($val["J"]),
                                            "TransDate" => trim($val["M"]),
                                            "TransTime" => trim($val["N"]),
                                            "AuthCode" => trim($val["O"])
                                        );
                        
                      }
                    }

                unset($objPHPExcel);
                unset($sheetData ); 
        
    //let loop the Givex report and build our data array.
    /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
        $inputFileName = $target_dir.$givexReport;
        //code based on the looks stuff up machine
        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //A = order_type
                    //B = payment_type
                    //C = cc_masked_number
                    //D = cc_type
                    //E = cc_trans_auth
                    //F = cc_trans_ts
                    //G = cc_trans_amount
                    //H = trans_order_id
                    //I = trans_type
                    //J = cc_trans_ip
                    //K = refund_shipping_amt
                    //L = shipping_taxes
                    //M = total_certval_amt
                    //N = serial_range
                    //O = user_login

                    if(trim($val["E"]) != "" && strpos(trim($val["H"]),'Card') === false && trim($val["D"]) != 'cc_type'){
                        
                        $tempDistinct = cardTransaltion(trim($val["D"])).substr(trim($val["C"]),-4).trim($val["G"]).trim($val["E"]);
                        if(isset($_GET['NoAuth'])){$tempDistinct = cardTransaltion(trim($val["D"])).substr(trim($val["C"]),-4).trim($val["G"]);}//option to loosen up our distinct creation
                        //printMe($tempDistinct);
                        $giveCWSArrayV2[$tempDistinct][count($giveCWSArrayV2[$tempDistinct])] = array(
                                            //"BatchNo" => trim($val["E"]),
                                            "CardType" => trim($val["D"]),
                                            "CardNo" => trim($val["C"]),
                                            "TransAmount" => trim($val["G"]),
                                            "TransDateTime" => trim($val["F"]),
                                            //"TransTime" => trim($val["N"]),
                                            "AuthCode" => trim($val["E"])
                                        );
                        
                      }
                      }                  
                    

                unset($objPHPExcel);
                unset($sheetData );             
                
    //this is the output
    print 'Givex CWS Sheet '.$givexReport.'<br>';
    print 'BAMS HL0101 Report '.$bams101report.'<br><br>';
    
    /*
    foreach($bams101Array as $key=>$val){
        foreach($val as $key2=>$val2){
            print $val2." | ";
        }
        print "<br>";
    }
    
    foreach($giveCWSArray as $key=>$val){
        foreach($val as $key2=>$val2){
            print $val2." | ";
        }
        print "<br>";
    }
    */
    /////////////////////////////////////////////////
   $dupCheckMessage = 'Hurray, no dups in this data!<br>';
   $listOfDups = '';
    foreach($bams101ArrayV2 as $key => $val){
        if(count($val) > 1){
            $dupCheckMessage = 'Ugh, here are the dups in this data. <button onclick="myCopy(\'table1\')">Copy Table</button><br><br>';
            foreach($val as $key2=>$val2){
                $listOfDups .= "<tr><td>HL0101</td><td>".$val2["AuthCode"]."</td><td>".$val2["CardType"]."</td><td>".$val2["CardNo"]."</td><td>".$val2["TransDate"]."</td><td>".$val2["TransTime"]."</td><td>".$val2["TransAmount"]."</td></tr>";
            }
            //now call in the matching transactions from the Givex report
            //print $key."<br>";
            foreach($giveCWSArrayV2[$key] as $keyGive =>$valGive){
                $tempDateTime = explode(" ", $valGive["TransDateTime"]);
                $listOfDups .= "<tr><td>CWS</td><td>".$valGive["AuthCode"]."</td><td>".$valGive["CardType"]."</td><td>".$valGive["CardNo"]."</td><td>".$tempDateTime[0]."</td><td>".$tempDateTime[1]."</td><td>".forceDoubleZero($valGive["TransAmount"])."</td></tr>";
            }
            $listOfDups .= "<tr><td colspan=\"7\" style=\"background: black;\"></td></tr>";//makes a black separater
        }
    }
    if($listOfDups != ''){
        print $dupCheckMessage."<table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>".$listOfDups."</table>";
        print '<textarea id="table1" class="copy-me"><table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>'.$listOfDups.'</table></textarea>';
    }else{
        print $dupCheckMessage;
    }
    /////////////////////////////////
   $extraCheckMessage = '<br><br>Hurray, all Auth codes on the HL0101 have a match on the Givex report!<br>';
   $listOfextra = '';
   $countMissingTransactions = 0;
    foreach($bams101ArrayV2 as $key => $val){
        //print var_dump($val)."<br>";
        if(!isset($giveCWSArrayV2[$key])){
                    foreach($val as $val2){
                    $countMissingTransactions++;
                    $extraCheckMessage = '<br><br>Ugh, here are the extra Auth Code(s) in the HL0101 report. There are '.$countMissingTransactions.' of them :( <button onclick="myCopy(\'table2\')">Copy Table</button><br><br>';
                    $listOfextra .= "<tr><td>HL0101</td><td>".$val2["AuthCode"]."</td><td>".$val2["CardType"]."</td><td>".$val2["CardNo"]."</td><td>".$val2["TransDate"]."</td><td>".$val2["TransTime"]."</td><td>".$val2["TransAmount"]."</td></tr>";
                    }
                }
    }
    if($listOfextra != ''){
        print $extraCheckMessage."<table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>".$listOfextra."</table>";
        print '<textarea id="table2" class="copy-me"><table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>'.$listOfextra.'</table></textarea>';
    }else{
        print $extraCheckMessage;
    }
    /////////////////////////////////////
    /////////////////////////////////
   $extraCheckMessage = '<br><br>Hurray, all Auth codes on the Givex Report have a match on the HL0101 report!<br>';
   $listOfextra = '';
   $countMissingTransactions = 0;
    foreach($giveCWSArrayV2 as $key => $val){//$bams101ArrayV2
        //print $key."<br>";
        if(!isset($bams101ArrayV2[$key])){
                    foreach($val as $val2){
                    $countMissingTransactions++;
                    $extraCheckMessage = '<br><br>Ugh, here are the extra Auth Code(s) in the Givex report. There are '.$countMissingTransactions.' of them :( <button onclick="myCopy(\'table3\')">Copy Table</button><br><br>';
                    $tempDateTime = explode(" ", $valGive["TransDateTime"]);
                    $listOfextra .= "<tr><td>CWS</td><td>".$val2["AuthCode"]."</td><td>".$val2["CardType"]."</td><td>".$val2["CardNo"]."</td><td>".$tempDateTime[0]."</td><td>".$tempDateTime[1]."</td><td>".forceDoubleZero($val2["TransAmount"])."</td></tr>";
                    }
                }
    }
    if($listOfextra != ''){
        print $extraCheckMessage."<table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>".$listOfextra."</table>";
        print '<textarea id="table3" class="copy-me"><table><tr><th>Report</th><th>Auth Code</th><th>Card Type</th><th>Card No.</th><th>Trans Date</th><th>Trans Time</th><th>Amount</th></tr>'.$listOfextra.'</table></textarea>';
    }else{
        print $extraCheckMessage;
    }
    /////////////////////////////////////
}else{//check for all sheets required has failed
    
    print "I am missing a sheet to work! I need the Bams HL0101 report and the Givex CWS report.";
}

function makeSureWeHaveANumber($y){
    if(trim($y) == ''){$y = 0;}
    return $y;
}

function cardTransaltion($x){
    $x = strtoupper($x);
    $x = str_replace("MASTERCARD", "MC", $x);
    $x = str_replace(" ", "", $x);
    $x = str_replace("DEBIT", "", $x);
    //print $x."<br>";
    return $x;
}

function printMe($x){
    print $x."<br>";
}

function forceDoubleZero($x){
    $test = explode(".",$x);
    if($test[1] == ""){
        return $test[0].".00";
    }else{
        return $x;
    }
}