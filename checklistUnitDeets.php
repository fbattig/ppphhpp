<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
$district = "";//setup, well need to lift this from the ORL sheet
$sectorMatcher = "";//used for units less then 9999 for overheads so we can find out who is Sector Controller
$sectorMatcherDisplay = "";
$departmentTest = array();
//print "The current local time is".time();

//setup our file locations
//include_once("checklistFileLocations.php");//use custom file location for this one
//var_dump($players);
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\randomUnitSheets\\";//prod server
        $target_root = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";//prod server
      }else{
         $target_dir = "checklist/randomUnitSheets/";//dev server 
         $target_root = "";//dev server 
      }


if(is_numeric($_POST['unitToLookUp'])){    
    
    print "<h3>Here is what we know about unit ".$_POST['unitToLookUp']."</h3>";
    $files1 = scandir($target_dir);
    /** Include PHPExcel_IOFactory */
        require_once dirname(__FILE__) . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
    
    
    $orbitData = "<br><br><b>No Orbit Data Found!</b>";
    $onlineReportingData = "<br><br><b>No Online Reporting Data Found!</b>";
    $accoutningContactListData = "<br><br><b>No Accouting Contact List Data Found!</b>";
    $unitSheetFromQBData = "<br><br><b>No QB Unit Data Found!</b>";
    $midsData = "<br><br><b>No MID Data Found!</b>";
    
    foreach($files1 as $key=>$file){
        set_time_limit(5000);  
        if($key > 1){
            //do the deliquent report first
            if(strpos($file,'DSubmissions')!== false){
                $thisFile = fopen($target_dir.$file, "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                        if(strpos($aline,$_POST['unitToLookUp'])){
                            $csvField = explode(",", $aline);
                            print "<b>Orbit ".$csvField[1]." - F".$csvField[2]." P".$csvField[3]." W".$csvField[4]."</b><br>";
                            print "Purchases by ".$csvField[5]."<br>";
                            print "Sales by ".$csvField[7]."<br>";
                            $orbitData = "";
                        }
                    }//end while
                    fclose($thisFile); 
                }//close file
            }
            //end D reprot
        }
    }
    
    //special one, look in the Givex Txt file on the root from the other tool and see if they have givex.
    //print $target_root."ZipThruUnits.txt";
    if(file($target_root."ZipThruUnits.txt")){//ZipThruUnits.txt
        $zipThruFile = $target_root."ZipThruUnits.txt";
        $myFile = new SplFileObject($zipThruFile);
        while (!$myFile->eof()) {
            if(trim($myFile->fgets()) == trim($_POST['unitToLookUp']))
                print "ZipThru detected!<br>";
            }
        
    }else{
        print "ZipThru File not Found<br>";
    }
    
            
    foreach($files1 as $key=>$file){
        set_time_limit(5000);  
        if($key > 1){               
            if(strpos($file,'Online Reporting Access Query')!== false){//Online Reporting Report
                $inputFileName = $target_dir.$file;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //each row
                    if(trim($val["A"]) == trim($_POST['unitToLookUp'])){
                        print "<br><b>Online Reporting Data</b>";
                        print "<br>Description = ".$val["B"];
                        print "<br>Buisness Unit Type = ".$val["C"];
                        if(trim($val["D"]) != ""){print "<br>Closed Buisness = ".$val["D"];}
                        print "<br>Company = ".$val["E"];
                        print "<br>Sector = ".$val["F"];
                        $departmentTest = explode("-",$val["F"]);//used below
                        if(trim($val["G"]) != ""){print "<br>Pres. = ".$val["G"];}
                        if(trim($val["H"]) != ""){print "<br>RVP = ".$val["H"];}
                        if(trim($val["I"]) != ""){print "<br>RD = ".$val["I"];}
                        print "<br>Dist. = ".$val["J"];
                        $tmpD = explode("-",$val["J"]);
                        $district = trim($tmpD[0]);
                        if(trim($val["K"]) != ""){print "<br>DMA = ".$val["K"];}
                        if(trim($val["L"]) != ""){print "<br>OLR 1 = ".$val["L"];}
                        if(trim($val["M"]) != ""){print "<br>OLR 2 = ".$val["M"];}
                        if(trim($val["N"]) != ""){print "<br>OLR 3 = ".$val["N"];}
                        
                        //print "<br>".var_dump($val)."<br>";
                        $onlineReportingData = "";
                        
                        if(trim($_POST['unitToLookUp'][0]) == '0'){//if this is an overhead then we will keep our loop going and use it to find our sector controller
                           $sectorMatcher = $val["F"];
                            $sectorMatcherDisplay = $val["F"];
                        }
                    }
                    
                    //now if $sectorMatcher is not empty and not a number let's find our first unit
                    if($sectorMatcher != "" && $sectorMatcher == $val["F"] && trim($val["A"]) > 9999){
                        //print "<br><br>".$sectorMatcher.' = unit found '.trim($val["A"]);
                        $tmpD = explode("-",$val["J"]);
                        $sectorMatcher = trim($tmpD[0]);//well have to setup the district here as that is the only way to work with the accounting contact list
                        
                   }
                    
                    
                        /*
                        foreach($val as $letter=>$cellValue){
                            //each coloum
                            if($key != 1 && $key < count($sheetData)){//skip the header row and the last row that has the totals
                                $givexTab[$key][$letter] = $cellValue;
                            }
                        }
                        */
                }
                unset($objPHPExcel);
                unset($sheetData );       
            }//end online reporting report
            
        }
    }
   
    
    foreach($files1 as $key=>$file){
        set_time_limit(5000);  
        if($key > 1){
            //now lets hit the Accounting contact list
            if(strpos($file,'Accounting Contact List')!== false && ($district != "" || $sectorMatcher != "")){//Online Reporting Report
                
                $inputFileName = $target_dir.$file;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //each row
                    //print "<br>".$sectorMatcher." = ".trim($val["A"]);
                    if(trim($val["A"]) == $district || $sectorMatcher == trim($val["A"])){// was added to be able to find Sector controller for overheads
                        
                        if($sectorMatcher == "" && trim($val["C"]) != ""){//normal unit else overhead
                            print "<br><br><b>Accounting Contact List for Dist. ".$district."</b>";
                            if(trim($val["B"]) != ""){print "<br>Banker = ".$val["B"];}
                            if(trim($val["C"]) != ""){print "<br>Sect. Cont. = ".$val["C"];}
                            if(trim($val["D"]) != ""){print "<br>SDA = ".$val["D"];}
                        }
                        if($sectorMatcher != "" && trim($val["C"]) != ""){//overhead
                            print "<br><br><b>Accounting Contact List for Sector<br>".$sectorMatcherDisplay."</b>";
                            if(trim($val["C"]) != ""){print "<br>Sect. Cont. = ".$val["C"];}
                        }
                                                
                        //print "<br>".var_dump($val)."<br>";
                        $accoutningContactListData = "";
                    }

                        /*
                        foreach($val as $letter=>$cellValue){
                            //each coloum
                            if($key != 1 && $key < count($sheetData)){//skip the header row and the last row that has the totals
                                $givexTab[$key][$letter] = $cellValue;
                            }
                        }
                        */
                }
                unset($objPHPExcel);
                unset($sheetData );       
            }//end accounting contact list
            
            
        }
    }
       
    
    foreach($files1 as $key=>$file){
            set_time_limit(5000);  
        if($key > 1){
            //do the Units... this comes from QB
            if(strpos($file,'Units')!== false){
            $inputFileName = $target_dir.$file;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //each row
                    if(trim($val["B"]) == $_POST['unitToLookUp']){
                        print "<br><br><b>QB data for ".trim($val["B"])."</b>";
                        if(trim($val["H"]) != ""){print "<br>Phone = ".$val["H"];}
                        if(trim($val["I"]) != ""){print "<br>Address = ".$val["I"];}
                        if(trim($val["K"]) != ""){print "<br>Status = ".$val["K"];}
                        if(trim($val["O"]) != ""){print "<br>POS = ".$val["O"];}
                        if(trim($val["Q"]) != ""){print "<br>Contact = ".$val["Q"];}
                        
                        //print "<br>".var_dump($val)."<br>";
                        $unitSheetFromQBData = "";
                    }
                        /*
                        foreach($val as $letter=>$cellValue){
                            //each coloum
                            if($key != 1 && $key < count($sheetData)){//skip the header row and the last row that has the totals
                                $givexTab[$key][$letter] = $cellValue;
                            }
                        }
                        */
                }
                unset($objPHPExcel);
                unset($sheetData );      
            //end MID Report
            }
        }
    }
    
        foreach($files1 as $key=>$file){
            set_time_limit(5000);  
        if($key > 1){
            //do the MID
            if(strpos($file,'MIDs')!== false){
            $inputFileName = $target_dir.$file;
                //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
                $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                foreach($sheetData as $key=>$val){
                    //each row
                    if(trim($val["D"]) == $_POST['unitToLookUp']){
                        print "<br><br><b>MID data for ".trim($val["D"])."</b>";
                        if(trim($val["A"]) != ""){print "<br>MID = ".$val["A"];}
                        if(trim($val["B"]) != ""){print "<br>Amex = ".$val["B"];}
                        if(trim($val["C"]) != ""){print "<br>Bambora = ".$val["C"];}
                        if(trim($val["E"]) != ""){print "<br>Status = ".$val["E"];}
                        if(trim($val["F"]) != ""){print "<br>DBA = ".$val["F"];}
                        
                        //print "<br>".var_dump($val)."<br>";
                        $midsData = "";
                    }
                        /*
                        foreach($val as $letter=>$cellValue){
                            //each coloum
                            if($key != 1 && $key < count($sheetData)){//skip the header row and the last row that has the totals
                                $givexTab[$key][$letter] = $cellValue;
                            }
                        }
                        */
                }
                unset($objPHPExcel);
                unset($sheetData );      
            //end MID Report
            }
        }
    }
    
    //print out if a report found crap
    //comment out eventually
    /*
    print "<br><br><b>Sheets Scanned</b><br>";
    foreach($files1 as $key=>$value){
        if($key > 1){
            if(strpos($value,'~$')!== TRUE){print $value."<br>";}
        }
    }
     * */
    
    if($orbitData != ""){ print $orbitData;}
    if($onlineReportingData != ""){ print $onlineReportingData;}
    if($accoutningContactListData != ""){ print $accoutningContactListData;}
    if($unitSheetFromQBData != ""){ print $unitSheetFromQBData;}
    if($midsData != ""){ print $midsData;}

                //get the data from the department Logic sheet.
                $dFile = fopen($target_dir."departmentLogic.txt", "r");
                    if ($dFile) {
                          while (!feof($dFile)) {
                                   $aline = fgets($dFile);
                                   $tempDdata = explode("|",trim($aline));
                                   if(trim($departmentTest[0]) == $tempDdata[0]){print "<br><br><h2>We have a department here : ".$tempDdata[1]."</h2>";}
                          }
                    }
                close($dFile);


    
}else{
    if($_POST['unit'] != ""){
        print "Nice, try, I need a unit number! \"".$_POST['unit']."\" is certainly not!";
    }else{
        print "Nice, try, I need a unit number! {blank} is certainly not!";
    }
    
}