<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//ini_set('display_errors',1);
//error_reporting(E_ALL);



include_once '.\_timestampLogger.php';

if(isset($_GET['action'])){


        /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

        $files1 = scandir($target_dir);

        $givexTransReport = "";
        $jdeTBReport = "";
        $webMerchantSalesReport = "";
        $newUnitDetector = array();
        foreach($files1 as $key=>$value){
            if(is_file($target_dir.$value) && strpos($value, 'Transactions Report')){//Givex Transactions Report
                $givexTransReport = str_replace("~","",$target_dir.$value);
            }
            if(is_file($target_dir.$value) && strpos($value, 'TB')){//JDE Trial Balance
                $jdeTBReport = str_replace("~","",$target_dir.$value);
            }
            if(is_file($target_dir.$value) && strpos($value, 'List of Transactions')){//Web Merchant Sales
                $webMerchantSalesReport = str_replace("~","",$target_dir.$value);
            }
        }//end foreach

        $greenLightThis = TRUE;//as in we found all the reports

        if($givexTransReport == ""){
            print "I cant find a report with Givex Transactions Report in it's file name. Sorry I can't help you until you fix that.";
            $greenLightThis = FALSE;
        }

        if($jdeTBReport == ""){
            print "I cant find a report with JDE Trial Balance in it's file name. Sorry I can't help you until you fix that.";
            $greenLightThis = FALSE;
        }

        if($webMerchantSalesReport == ""){
            print "I cant find a report with Web Merchant Sales in it's file name. Sorry I can't help you until you fix that.";
            $greenLightThis = FALSE;
        }

        if($greenLightThis){
             $givexTab = array();
                //start with the Givex Report
                $inputFileName = $givexTransReport;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                //var_dump($sheetData);
                foreach($sheetData as $key=>$val){
                    //each row
                    //print "<br>".$key.' = '.$val."<br>";
                        foreach($val as $letter=>$cellValue){
                            //each coloum
                            if($key != 1 && $key < count($sheetData)){//skip the header row and the last row that has the totals
                                $givexTab[$key][$letter] = $cellValue;
                            }
                        }
                }
                unset($objPHPExcel);
                unset($sheetData );

                //Now lets get the JDE TB data to add to cells O thru R for the copy paste to the Givex Tab
                $jdeTBDataForGivexTab = array();
                $inputFileName = $jdeTBReport;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                //var_dump($sheetData);
                foreach($sheetData as $key=>$val){
                        if($key != 1 && $key != count($sheetData)){//skip the header row and the last row that has the totals
                            $tempVar = explode(".",$val['A']);
                            $compassUnit = trim($tempVar[0]);
                            //print "<br>".$key.' = '.$compassUnit.' with '.$val['G']."<br>";
                            $jdeTBDataForGivexTab[$compassUnit] = array('O' => $val['A'], 'P' => "Def Inc.- Corp. Zipthru Card", 'Q' => $val['G'], 'R' => $compassUnit);
                        }
                        set_time_limit(5000);
                }
                unset($objPHPExcel);
                unset($sheetData );       

                //var_dump($jdeTBDataForGivexTab);




                //GIVEX TAB COPY PASTER
                $varianceForSummaryTab = array();
                $printGivexTabTable = "";
                $printGivexTabTable .= '<table style="border:1px solid black;text-align:center;">';
                $printGivexTabTable .= '<tr><td></td><td colspan="11"><span style="color:rgb(0, 112, 192)">Givex Liability (Transaction Report)</span></td><td></td><td style="background-color:grey;"></td><td colspan="4"><span style="color:rgb(0, 112, 192)">GL Balance (JDE Trial Balance)</span></td></tr>';
                $printGivexTabTable .= '<tr><td><i>Trans</i></td><td rowspan="2"><b>Store Name</b></td><td colspan="2"><b>Activiation</b></td><td colspan="2"><b>Redemption</b></td><td colspan="2"><b>Increment</b></td><td colspan="2"><b>Adjustment</b></td><td colspan="2"><b>Total</b></td><td></td><td style="background-color:grey;"></td><td rowspan="2"><b>Account Number</b></td><td rowspan="2"><b>Account Description</b></td><td rowspan="2"><b>GL Balance</b></td><td></td></tr>';
                $printGivexTabTable .= '<tr><td><i>Report</i></td><td>Qty</td><td>Amount</td><td>Qty</td><td>Amount</td><td>Qty</td><td>Amount</td><td>Qty</td><td>Amount</td><td>Qty</td><td>Amount</td><td></td><td style="background-color:grey;"></td><td></td></tr>';
                foreach($givexTab as $val){
                    //add anything missing from the JDE TB Report
                    if(!isset($jdeTBDataForGivexTab[$val["A"]])){$jdeTBDataForGivexTab[$val["A"]] = array('O' => $val['A'].".255095", 'P' => "Def Inc.- Corp. Zipthru Card", 'Q' => 0, 'R' => $val['A']);}
                    //print $val["A"].' is '.$val["L"].' + '.$jdeTBDataForGivexTab[$val["A"]]["Q"].' which is '.($val["L"] + $jdeTBDataForGivexTab[$val["A"]]["Q"]).'<br>';
                    $varianceForSummaryTab[$val["A"]] = round(str_replace(",","",$val["L"]) + str_replace(",","",$jdeTBDataForGivexTab[$val["A"]]["Q"]),2);
                    $printGivexTabTable .= "<tr><td>".$val["A"]."</td><td>".$val["B"]."</td><td>".$val["C"]."</td><td>".$val["D"]."</td><td>".$val["E"]."</td><td>".$val["F"]."</td><td>".$val["G"]."</td><td>".$val["H"]."</td><td>".$val["I"]."</td><td>".$val["J"]."</td><td>".$val["K"]."</td><td>".$val["L"]."</td><td></td><td style=\"background-color:grey;\"></td><td>".$jdeTBDataForGivexTab[$val["A"]]["O"]."</td><td>".$jdeTBDataForGivexTab[$val["A"]]["P"]."</td><td>".$jdeTBDataForGivexTab[$val["A"]]["Q"]."</td><td>".$jdeTBDataForGivexTab[$val["A"]]["R"]."</td></tr>";
                    if(!in_array($newUnitDetector,$val["A"])){$newUnitDetector[count($newUnitDetector)] = $val["A"];}
                    unset($jdeTBDataForGivexTab[$val["A"]]);
                }
                set_time_limit(5000);
                foreach($jdeTBDataForGivexTab as $key=>$val){
                    $varianceForSummaryTab[$val["R"]] = $val["Q"];//could remove this if we want to see the stuff in JDE but not on the Givex report
                    $printGivexTabTable .= "<tr><td>".$val["R"]."</td><td><i>Not on Givex Report</i></td><td>0</td><td>0.00</td><td>0</td><td>0.00</td><td>0</td><td>0.00</td><td>0</td><td>0.00</td><td>0</td><td>0.00</td><td></td><td style=\"background-color:grey;\"></td><td>".$val["O"]."</td><td>".$val["P"]."</td><td>".$val["Q"]."</td><td>".$val["R"]."</td></tr>";
                    if(!in_array($newUnitDetector,$val["R"])){$newUnitDetector[count($newUnitDetector)] = $val["R"];}
                }
                set_time_limit(5000);
                $printGivexTabTable .= '</table>';
            ///end heavy lifting
                
                ///display the givex tab table and pretty much die
                if($_GET['action'] == 'givex'){
                        print $printGivexTabTable;                   
                }
                
                //go after the stuff for the Summary tab so we have more work to do before we can display that
                if($_GET['action'] == 'summary' || $_GET['action'] == 'test'){
                    //get a list of ZIPTHRU units
                    $zipThruUnits = array();
                    $file = fopen($target_dirX."ZipThruUnits.txt", "r");
                        if ($file) { 
                                    while (!feof($file)) {
                                         $zipThruUnits[count($zipThruUnits)] = trim(fgets($file));
                                    }
                        }else{
                            print "Where did the ZipThruUnits.txt go?";
                            die;
                        }
                        
                        set_time_limit(5000);
                            $loads = array();
                            $inputFileName = $webMerchantSalesReport;
                            //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                            //var_dump($sheetData);
                            foreach($sheetData as $key=>$val){
                                if($val["C"] == "Sale"){$loads[$val["K"]] = -1*(trim($val["D"]));}
                            }
                            unset($objPHPExcel);
                            unset($sheetData );     
  

                     //check for new units that are not in the config
                     foreach($newUnitDetector as $foundUnit){
                         if(!in_array($foundUnit,$zipThruUnits) && $foundUnit != '100'){print "ERROR, found unit ".$foundUnit." in the Givex or JDE TB Report";}
                     }       
                        
                        set_time_limit(5000);
                    $summaryTabOutput = '<table border="1">';
                    foreach($zipThruUnits as $unit){
                        $theVarianceForThisUnitIs = "";
                        //figure out variance
                        if($varianceForSummaryTab[$unit] < 20 && $varianceForSummaryTab[$unit] > -20 && $varianceForSummaryTab[$unit] != 0){
                            $theVarianceForThisUnitIs = $varianceForSummaryTab[$unit];
                        }
                        //check for loads
                        
                        
                        
                        //test vs go time
                        if($_GET['action'] == 'test'){
                            $summaryTabOutput .= "<tr><td>".$unit."</td><td>".$loads[$unit]."</td><td></td><td></td><td></td><td></td><td></td><td></td><td>".(-1 * $theVarianceForThisUnitIs)."</td></tr>";
                        }
                        if($_GET['action'] == 'summary'){
                            $summaryTabOutput .= "<tr><td></td><td>".$loads[$unit]."</td><td></td><td></td><td></td><td></td><td></td><td></td><td>".(-1 * $theVarianceForThisUnitIs)."</td></tr>";
                        }
                    }
                    $summaryTabOutput .= '</table>';
                    set_time_limit(5000);
                    print $summaryTabOutput;
                }
        }else{
            print "<br><br>Reports missing, end of script.";
        }
}else{// we need to know which tab the user is going to be working with
    print "Hello ".$_SESSION['name']."! Which part of the ZipThru report are you building?<br><br><br><a href=\"ZipThruRecSheetMaker.php?action=givex\">Givex Tab</a><i> (Paste as normal in cell A1)</i><br><br><br><a href=\"ZipThruRecSheetMaker.php?action=test\">Test Summary Tab</a><i> (Paste as value in cell D8)</i><br><br><br><a href=\"ZipThruRecSheetMaker.php?action=summary\">Summary Tab</a><i> (Paste as value in cell D8)</i>";
    print "<br><br><br><hr/><h4>Please note, you have keep the reports closed or I won't be able to read them.</h4>";
    
}