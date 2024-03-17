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
    width:0px;
    height:0px; 
}
</style>
</head>

<body>
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';
require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
$varianceFile = '';

$files1 = scandir($target_dir);

foreach($files1 as $key=>$value){
    $varianceFile = $value;//last write wins
}

$coremarkArray = array();
$normalVendorArray = array();
$priceVarianceArray = array();
$qntVarianceArray = array();

$inputFileName = $target_dir.$varianceFile;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                
                foreach($sheetData as $key=>$val){
                    set_time_limit(5000);//extend the script timeout to 5 more minutes
                    
                    //skip Adjustements
                    if(isNotAdjustment(trim($val['C']))){
                        //Coremark vs Regular

                        if(strpos(trim($val['G']),'CAN_CMK') !== false && trim($val['S']) == trim($val['P'])){//coremark we are just checking to see if the tax line recals fine
                            $coremarkArray[$val['G']][trim($val['C'])]['taxVar'] = $coremarkArray[$val['G']][trim($val['C'])]['taxVar'] + str_replace("$","",trim($val['AR']));
                            $coremarkArray[$val['G']][trim($val['C'])]['date'] = trim($val['H']);//last write wins
                            $coremarkArray[$val['G']][trim($val['C'])]['unit'] = trim($val['F']);//last write wins
                        }else{
                            if(trim($val['G']) != "" && trim($val['G']) != 'Supplier Id' && trim($val['S']) == trim($val['P'])){
                                //print 'Building Regular '.trim($val['G']).'<br>';
                                $normalVendorArray[trim($val['G'])][trim($val['C'])][count($normalVendorArray[trim($val['G'])][trim($val['C'])])] = array('itemNo' => trim($val['K']),
                                                                                                                                                                'itemDes' => trim($val['N']),
                                                                                                                                                                'originalGST' => str_replace("$","",trim($val['AG'])),
                                                                                                                                                                'originalPST' => str_replace("$","",trim($val['AL'])),                                
                                                                                                                                                                'recalcGST' => str_replace("$","",trim($val['AJ'])),
                                                                                                                                                                'recalcPST' => str_replace("$","",trim($val['AM'])),
                                                                                                                                                                'taxVariance' => str_replace("$","",trim($val['AR'])),
                                                                                                                                                                'date' => trim($val['H']),
                                                                                                                                                                'qntApproved' => trim($val['S']),
                                                                                                                                                                'unit'=> trim($val['F'])
                                                                                                                                                                )
                            ;}
                        }

                        //now all even Coremark to look for price changes
                        if(trim($val['G']) != "" && trim($val['G']) != 'Supplier Id'){
                            $priceVaraince = str_replace("$","",trim($val['Y']));
                            if($priceVaraince != 0){
                                $priceVarianceArray[trim($val['G'])][trim($val['C'])][count($priceVarianceArray[trim($val['G'])][trim($val['C'])])] = array('priceVar' => $priceVaraince,
                                                                                                                                                                'itemNo' => trim($val['K']),
                                                                                                                                                                'itemDes' => trim($val['N']),
                                                                                                                                                                'originalPrice' => str_replace("$","",trim($val['U'])),
                                                                                                                                                                'recalcPrice' => str_replace("$","",trim($val['X'])),
                                                                                                                                                                'date' => trim($val['H']),
                                                                                                                                                                'qntApproved' => trim($val['S']),
                                                                                                                                                                'unit'=> trim($val['F'])
                                                                                                                                                                )
                                        ;}
                        }     
                        //now all again to look for item qnt adj.
                        if(trim($val['G']) != "" && trim($val['G']) != 'Supplier Id'){
                            $qntVaraince = trim($val['T']);
                            if($qntVaraince != 0){
                                $qntVarianceArray[trim($val['G'])][trim($val['C'])][count($qntVarianceArray[trim($val['G'])][trim($val['C'])])] = array(        'itemNo' => trim($val['K']),
                                                                                                                                                                'itemDes' => trim($val['N']),
                                                                                                                                                                'originalQnt' => trim($val['P']),
                                                                                                                                                                'adjustedQnt' => trim($val['S']),
                                                                                                                                                                'recalcPrice' => str_replace("$","",trim($val['X'])),
                                                                                                                                                                'date' => trim($val['H']),
                                                                                                                                                                'variance' => str_replace("$","",trim($val['AR'])),
                                                                                                                                                                'unit'=> trim($val['F']),
                                                                                                                                                                'comment' => trim($val['O'])
                                                                                                                                                                )
                                        ;}                

                            //print $val['G']."<br>";
                        }//end if All G for price checking
                    }//end if Adj. 

                }//ens sheetData loop
                //var_dump($qntVarianceArray);
                /*
                print 'Price Variance<br>';
                var_dump($priceVarianceArray);
                print '<hr/>';
                print 'Coremark Tax Check<br>';
                var_dump($coremarkArray);
                print '<hr/>';
                print 'Normal Vendor Tax Check<br>';
                var_dump($normalVendorArray);
                 * */
                print "<h1>CIMS Variance Report Scrubber</h1><i>Anything where the total variance was less then a dollar has been removed from these reports.</i>";
                print "<h2>Item price variance test.</h2>";
                set_time_limit(5000);//extend the script timeout to 5 more minutes
                if(count($priceVarianceArray) > 0){
                    $priceMsg = "Sweet, there are no price variances to review!<br></br>";
                    $priceTableData = array();
                    foreach($priceVarianceArray as $key=>$val){
                        $tempVendor = $key;
                        foreach($val as $key2=>$val2){
                            foreach($val2 as $key3=>$val3){
                                $tempPriceVaraince = round((-1*($val3['originalPrice'] - $val3['recalcPrice'])),2);
                                $tempLineVariance = round(-1*(($val3['originalPrice']*$val3['qntApproved']) - ($val3['recalcPrice']*$val3['qntApproved'])),2);
                                if($tempLineVariance > 1 || $tempLineVariance < -1){
                                    $priceTableData[count($priceTableData)] = "<tr><td>".$tempVendor."</td><td>".$key2."</td><td>".$val3['itemNo']."</td><td>".$val3['itemDes']."</td><td>".$val3['qntApproved']."</td><td>".$val3['originalPrice']."</td><td>".$val3['recalcPrice']."</td><td>".$tempPriceVaraince."</td><td>".($val3['originalPrice']*$val3['qntApproved'])."</td><td>".($val3['recalcPrice']*$val3['qntApproved'])."</td><td>".$tempLineVariance."</td><td>".$val3['date']."</td><td>".$val3['unit']."</td><td></td></tr>";
                                    $priceMsg = "Ugh, i've detected these price variances to review! <button onclick=\"myCopy('table1')\">Copy Table</button> <button onclick=\"myCopy('table1nh')\">NH Copy Table</button></br></br>";
                                }                            
                            }
                        }
                       }
                    
                      print $priceMsg;
                      if(count($priceTableData) > 0){
                        print '<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Approved Qty.</th><th>Original Price</th><th>Recalc Price</th><th>Price Variance</th><th>Original Value</th><th>New Value</th><th>Line Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';
                        foreach($priceTableData as $aRow){
                                   print $aRow;
                               }
                        print "</table>";
                        
                        //one more time for the copy paster
                        print '<textarea id="table1" class="copy-me">Price variance report.<br><table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Approved Qty.</th><th>Original Price</th><th>Recalc Price</th><th>Price Variance</th><th>Original Value</th><th>New Value</th><th>Line Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';
                        foreach($priceTableData as $aRow){
                                   print $aRow;
                               }
                        print "</table></textarea>";
                        
                        //one more time for no header copy paster
                        print '<textarea id="table1nh" class="copy-me"><table border="1">';
                        foreach($priceTableData as $aRow){
                                   print $aRow;
                               }
                        print "</table></textarea>";
                        
                      }
                }else{//we would only get here if there was no price adj.
                    print "Sweet, there are no price variances to review!<br><br>";
                }
                
                print "<h2>Coremark tax varaince test.</h2>";
                set_time_limit(5000);//extend the script timeout to 5 more minutes
                if(count($coremarkArray) > 0){
                    $coremarkMsg = "Sweet, there are no issues with the taxes on the Coremark invoices!</br></br>";
                    $coremarkTableData = array();                   
                    foreach($coremarkArray as $key=>$val){
                        $tempVendor = $key;
                        
                        foreach($val as $key2=>$val2){
                            if(abs(round($val2['taxVar'],0)) != 0){
                              $coremarkTableData[count($coremarkTableData)] = "<tr><td>".$tempVendor."</td><td>".$key2."</td><td>".round($val2['taxVar'],2)."</td><td>".$val2['date']."</td><td>".$val2['unit']."</td><td></td></tr>"; 
                              $coremarkMsg = "Ugh, here are the Coremark invoices with tax variances! <button onclick=\"myCopy('table2')\">Copy Table</button> <button onclick=\"myCopy('table2nh')\">NH Copy Table</button><br><br>";
                            }
                          }
                       }
                    
                       print $coremarkMsg;
                       if(count($coremarkTableData) != 0){
                           print '<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Tax Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';
                           foreach($coremarkTableData as $aRow){
                               print $aRow;
                           }
                           print '</table>';
                           
                            //one more time for the copy paster
                           print '<textarea id="table2" class="copy-me">Coremark tax variance report.<br><table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Tax Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';
                           foreach($coremarkTableData as $aRow){
                               print $aRow;
                           }
                           print '</table></textarea>';
                           
                           //one more time for NH copy paster
                           print '<textarea id="table2nh" class="copy-me"><table border="1">';
                           foreach($coremarkTableData as $aRow){
                               print $aRow;
                           }
                           print '</table></textarea>';
                       }
                       
                }else{//if there are no invoices we would hit this.
                    print "Sweet, there are no issues with the taxes on the Coremark invoices!<br><br>";
                }
                
                print "<h2>Quantity adjustments on the original invoice.</h2>";
                set_time_limit(5000);//extend the script timeout to 5 more minutes
                if(count($qntVarianceArray) > 0){
                    $qntAdjustTestMsg = "Sweet, there are no quantity adjustments to review!</br></br>";
                    $qntAdjustTestTableData = array();
                    foreach($qntVarianceArray as $key=>$val){
                        $tempVendor = $key;
                        foreach($val as $key2=>$val2){
                            foreach($val2 as $key3=>$val3){
                                
                                if(($val3['originalQnt'] - $val3['adjustedQnt']) != 0){
                                    $qntAdjustTestTableData[count($qntAdjustTestTableData)] = "<tr><td>".$tempVendor."</td><td>".$key2."</td><td>".$val3['itemNo']."</td><td>".$val3['itemDes']."</td><td>".$val3['originalQnt']."</td><td>".$val3['adjustedQnt']."</td><td>".$val3['recalcPrice']."</td><td>".$val3['variance']."</td><td>".$val3['date']."</td><td>".$val3['unit']."</td><td>".$val3['comment']."</td></tr>";
                                    $qntAdjustTestMsg = "Ugh, i've detected these quantity adjustments to reveiw! <button onclick=\"myCopy('table3')\">Copy Table</button> <button onclick=\"myCopy('table3nh')\">NH Copy Table</button></br></br>";
                                }   
                            }
                        }
                       }
                   
                    print $qntAdjustTestMsg;
                    if(count($qntAdjustTestTableData) > 0){
                        print '<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Shipped Qty.</th><th>Adjusted Qty.</th><th>Recalc Unit Price</th><th>Line Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';   
                       foreach($qntAdjustTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table>";
                        
                        //one more time for the copy paster
                        print '<textarea id="table3" class="copy-me">Quantity Adjustment report.<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Shipped Qty.</th><th>Adjusted Qty.</th><th>Recalc Unit Price</th><th>Line Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';   
                       foreach($qntAdjustTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table></textarea>";
                        
                        //one more time for the copy paster
                        print '<textarea id="table3nh" class="copy-me"><table border="1">';   
                       foreach($qntAdjustTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table></textarea>";
                        
                    }
                }else{//we would only ever get here if there where no invoices on the report
                    print "Sweet, there are Quantity Adjustments to review!<br><br>";
                }

                print "<h2>Traditional tax variance test.</h2>";
                set_time_limit(5000);//extend the script timeout to 5 more minutes
                if(count($normalVendorArray) > 0){
                    $normalTaxTestMsg = "Sweet, there are no tax variances to review!</br></br>";
                    $normalTaxTestTableData = array();
                    foreach($normalVendorArray as $key=>$val){
                        $tempVendor = $key;
                        foreach($val as $key2=>$val2){
                            foreach($val2 as $key3=>$val3){
                                $tempTaxVariance = round(-1*((makeSureWeHaveANumber($val3['originalGST'])-makeSureWeHaveANumber($val3['recalcGST']))+(makeSureWeHaveANumber($val3['originalPST'])-makeSureWeHaveANumber($val3['recalcPST']))),2);
                                if($tempTaxVariance > 1 || $tempTaxVariance < -1){
                                    $normalTaxTestTableData[count($normalTaxTestTableData)] = "<tr><td>".$tempVendor."</td><td>".$key2."</td><td>".$val3['itemNo']."</td><td>".$val3['itemDes']."</td><td>".makeSureWeHaveANumber($val3['originalGST'])."</td><td>".makeSureWeHaveANumber($val3['recalcGST'])."</td><td>".makeSureWeHaveANumber($val3['originalPST'])."</td><td>".makeSureWeHaveANumber($val3['recalcPST'])."</td><td>".$tempTaxVariance."</td><td>".$val3['date']."</td><td>".$val3['unit']."</td><td></td></tr>";
                                    $normalTaxTestMsg = "Ugh, i've detected these tax variances to reveiw! <button onclick=\"myCopy('table4')\">Copy Table</button> <button onclick=\"myCopy('table4nh')\">NH Copy Table</button></br></br>";
                                }   
                            }
                        }
                       }
                   
                    print $normalTaxTestMsg;
                    if(count($normalTaxTestTableData) > 0){
                        print '<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Orignal GST/HST</th><th>Recalc GST/HST</th><th>Original PST</th><th>Recalc PST</th><th>Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';   
                       foreach($normalTaxTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table>";
                        
                        //one more time for the copy paster
                        print '<textarea id="table4" class="copy-me">Tax variance report.<table border="1"><tr><th>Vendor</th><th>Invoice No.</th><th>Item No.</th><th>Description</th><th>Orignal GST/HST</th><th>Recalc GST/HST</th><th>Original PST</th><th>Recalc PST</th><th>Variance</th><th>Date</th><th>Unit No.</th><th>Comment</th></tr>';   
                       foreach($normalTaxTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table></textarea>";
                        
                        //one more time for the copy paster
                        print '<textarea id="table4nh" class="copy-me"><table border="1">';   
                       foreach($normalTaxTestTableData as $aRow){
                           print $aRow;
                       }
                        print "</table></textarea>";
                        
                    }
                }else{//we would only ever get here if there where no invoices on the report
                    print "Sweet, there are no tax variances to review!<br><br>";
                }

                
function makeSureWeHaveANumber($x){
    if(trim($x) == ''){
        return '0.00';
    }else{
        return $x;
    }
}                

function isNotAdjustment($x){
    $test = substr($x, -2, 1);
       if($test == 'A'){
           //print $x."<br>";
           return FALSE;
       }else{
           return TRUE;
       }
          
}