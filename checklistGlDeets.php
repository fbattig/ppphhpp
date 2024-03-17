<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
$district = "";//setup, well need to lift this from the ORL sheet
$sectorMatcher = "";//used for units less then 9999 for overheads so we can find out who is Sector Controller
$sectorMatcherDisplay = "";
$departmentTest = array();
//print "The current local time is".time();

      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\randomUnitSheets\\";//prod server
        $target_root = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";//prod server
      }else{
         $target_dir = "checklist/randomUnitSheets/";//dev server 
         $target_root = "";//dev server 
      }

  
      $filename = $target_dir."GL_Info.csv";
      
 if(file_exists($filename)) {  
 $outPut = array();     
 $glGroupMessage = "";//might not need, only if looking up a GL grouping
 
    print "<h3>Here is what we know about { ".$_POST['glToLookUp']." }</h3>";
    $testType = explode(".",$_POST['glToLookUp']);
                     //var_dump($testType);
    
                $thisFile = fopen($filename, "r");
                if ($thisFile) {//open file
                    while (!feof($thisFile)) {
                     $aline = fgets($thisFile);
                  
                     $csvField = explode(",", $aline);
                     
                        
                        if(trim($_POST['glToLookUp']) == '600000' || trim($_POST['glToLookUp']) == '400000' || trim($_POST['glToLookUp']) == '700000' || trim($_POST['glToLookUp']) == '900000'){//look for a GL Grouping request

                            $canAdd = FALSE;
                            switch(trim($_POST['glToLookUp'])){
                                case "600000":
                                    $glGroupMessage = "<h3>Total Base Labour</h3><i>Any GL between '600000' - '670325' and NOT ( including GLs between '640600' - '670100' or in '610200', '630300','630325', '620500','620510', '620550' )</i><br><br>";
                                    $canAdd = totalBaseLabour($csvField[1]);
                                    break;
                                    ;
                                case "400000":
                                    $glGroupMessage = "<h3>Total Revenue</h3><i>Where the Account OptStatement is 100 (Revenue) and NOT including '486620.1656', '486630.1653'</i><br><br>";
                                    $canAdd = totalRevenue($csvField[1],$csvField[2],$csvField[12]);
                                    break;
                                case "700000":
                                    $glGroupMessage = "<h3>Total Cost</h3><i>Product Cost, Paper Cost, Direct Operating Cost, Labour Cost, Indirect Operating Cost, Corporate/Department Expense) and NOT including '791400', '778100'</i><br><br>";
                                    $canAdd = totalCost($csvField[1],$csvField[12]);
                                    break;
                                case "900000":
                                    $glGroupMessage = "<h3>Total Net Profit</h3><i>Sales Revenue, Subsidy Revenue, Sundry Income, Product Cost, Paper Cost, Direct Operating Cost, Labour Cost, Indirect Operating Cost, Operating Fees, Corporate/Department Expense) and NOT including '791000','791400', '778100','486630'</i><br><br>";
                                    $canAdd = totalNetProfit($csvField[1],$csvField[12]);
                                    break;
                               
                            }
                            
                            //if ok, add it
                            if($canAdd){$outPut[count($outPut)] = array("ObjectAccount"=>$csvField[1],"SubAccount"=>$csvField[2],"Description"=>$csvField[4],"OpStatement"=>$csvField[12],"PE"=>$csvField[6]);}
                            //loop back to the next line
                        }else{//not GL Grouping request so other types to look ups
                               if(count($testType) == 1){//wild card, no period detected to mean sub test
                                  if(strpos(strtolower($aline),strtolower(trim($_POST['glToLookUp'])))){
                                       $outPut[count($outPut)] = array("ObjectAccount"=>$csvField[1],"SubAccount"=>$csvField[2],"Description"=>$csvField[4],"OpStatement"=>$csvField[12],"PE"=>$csvField[6]);
                                  }
                               }else{
                                  // print $testType[1]."<br>";
                                   if(strpos(strtolower(trim($csvField[1])),strtolower(trim($testType[0]))) !== false){//account test
                                       //print $testType[1]."|".$csvField[1].".".$csvField[2]."<br>";
                                       if(strpos($csvField[2],$testType[1])!== false){//sub account test, I couldn't get this working all in one test but oh well.
                                           //print "Bingo<br>";
                                           $outPut[count($outPut)] = array("ObjectAccount"=>$csvField[1],"SubAccount"=>$csvField[2],"Description"=>$csvField[4],"OpStatement"=>$csvField[12],"PE"=>$csvField[6]);
                                       }
                                  }
                               }


                       }
                    }//end while
                    fclose($thisFile); 
                }//close file
                
                if(count($outPut) > 0 || $glGroupMessage != ""){
                    
                    if($glGroupMessage != ""){
                            print $glGroupMessage;
                            print "<table border=\"1\"><tr><th>Obj. Acc.</th><th>Sub Acc.</th><th>PE</th><th>Description</th><th>Op. Code</th></tr>";
                            foreach($outPut as $key => $data){
                                print "<tr><td>".$data["ObjectAccount"]."</td><td>".str_replace("\" \"","",$data["SubAccount"])."</td><td>".str_replace("\" \"","",$data["PE"])."</td><td>".$data["Description"]."</td><td>".str_replace("\" \"","",$data["OpStatement"])."</td></tr>";
                            }
                            print "</table>";
                    }else{
                            print "<table border=\"1\"><tr><th>Obj. Acc.</th><th>Sub Acc.</th><th>PE</th><th>Description</th><th>Op. Code</th><th>Notes</th></tr>";
                            foreach($outPut as $key => $data){
                                print "<tr><td>".$data["ObjectAccount"]."</td><td>".str_replace("\" \"","",$data["SubAccount"])."</td><td>".str_replace("\" \"","",$data["PE"])."</td><td>".$data["Description"]."</td><td>".str_replace("\" \"","",$data["OpStatement"])."</td><td>".glNoteMaker($data["ObjectAccount"],str_replace("\" \"","",$data["SubAccount"]),str_replace("\" \"","",$data["OpStatement"]))."</td></tr>";
                            }
                            print "</table>";
                    }  
                }else{
                    print "<br><br><img src=\"images\Nothing.jfif\">";
                }
  
 }else{
     
     print "<br><br><br><br><br><br><br><center>The GL_Info.csv file is missing. I can't tell you anything without it.";
     print "<br><br><br>";
     print "<img src=\"images\Sad Face.png\"></center>";
 }
 
function totalBaseLabour($gl){
    $canAdd = FALSE;
    if($gl > 600000 && $gl < 640600 ){$canAdd = TRUE;}
    if($gl > 670100 && $gl < 670326 ){$canAdd = TRUE;}
    if($gl == 640600){$gl = FALSE;}
    if($gl == 630300){$canAdd = FALSE;}
    if($gl == 630325){$canAdd = FALSE;}
    if($gl == 620500){$canAdd = FALSE;}
    if($gl == 620510){$canAdd = FALSE;}
    if($gl == 620550){$canAdd = FALSE;}
    return $canAdd;
}

function totalRevenue($gl, $sub, $opCode){//$csvField[1],$csvField[2],$csvField[12]
    $canAdd = FALSE;
    if(trim($opCode) == 100){$canAdd = TRUE;}
    if($gl == 486620 && $sub == 1656){$canAdd = FALSE;}
    if($gl == 486630 && $sub == 1653){$canAdd = FALSE;}
    return $canAdd;
}

function totalCost($gl, $opCode){//$csvField[1],$csvField[12]
    $canAdd = FALSE;
    if(trim($opCode) == 200){$canAdd = TRUE;}
    if(trim($opCode) == 210){$canAdd = TRUE;}
    if(trim($opCode) == 300){$canAdd = TRUE;}
    if(trim($opCode) == 400){$canAdd = TRUE;}
    if(trim($opCode) == 500){$canAdd = TRUE;}
    if(trim($opCode) == 600){$canAdd = TRUE;}
    if(trim($opCode) == 700){$canAdd = TRUE;}
    if($gl == 791400){$canAdd = FALSE;}
    if($gl == 778100){$canAdd = FALSE;}
    return $canAdd;
}

function totalNetProfit($gl, $opCode){
    $canAdd = FALSE;
    if(trim($opCode) == 100){$canAdd = TRUE;}
    if(trim($opCode) == 110){$canAdd = TRUE;}
    if(trim($opCode) == 190){$canAdd = TRUE;}
    if(trim($opCode) == 200){$canAdd = TRUE;}
    if(trim($opCode) == 210){$canAdd = TRUE;}
    if(trim($opCode) == 300){$canAdd = TRUE;}
    if(trim($opCode) == 400){$canAdd = TRUE;}
    if(trim($opCode) == 500){$canAdd = TRUE;}
    if(trim($opCode) == 600){$canAdd = TRUE;}
    if(trim($opCode) == 700){$canAdd = TRUE;}
    if($gl == 791000){$canAdd = FALSE;}
    if($gl == 791400){$canAdd = FALSE;}
    if($gl == 778100){$canAdd = FALSE;}
    if($gl == 486630){$canAdd = FALSE;}
    return $canAdd;
}

function glNoteMaker($gl,$sub,$opCode){
    $note = "";
    if(totalBaseLabour($gl)){
        if($note == ""){
          $note .= "Total Base Labour";
        }else{
             $note .= "<br>Total Base Labour";
        }
    }
    
    if(totalRevenue($gl, $sub, $opCode)){
        if($note == ""){    
             $note = "Total Revenue";
        }else{
             $note .= "<br>Total Revenue";
        }          
    }
    
    if(totalCost($gl, $opCode)){
        if($note == ""){    
             $note = "Total Costs";
        }else{
             $note .= "<br>Total Costs";
        }          
    }
    
    if(totalNetProfit($gl, $opCode)){
        if($note == ""){    
             $note = "Total Net Profit";
        }else{
             $note .= "<br>Total Net Profit";
        }          
    }
    
    return $note;
}