<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Special Projects - SPDST</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="author" content="SitePoint">

  <link rel="icon" href="./favicon.png">

</head>

<body>
    
<?php    
include_once '.\_timestampLogger.php';
include_once ".\PHPExcel-1.8\Classes\PHPExcel\IOFactory.php";
$unitKnowledge = array();//$key will be district
$TMUnitKnowledge = array();//$key will be district
$districtKnowledge = array();
$dataLinesFound = 0;
$districtsFound = array();//used to tell the code what districts to bother checking for the recipie
$unitsFound = array();//units the user needs
$foundUnitCount = 0;
$output = "";

////////// Online Reporting Access Query.xlsx ////////////////////

if(file_exists($target_dir."\Online Reporting Access Query.xlsx")){//make sure we have the hubble report

        //first we need to search
        if(!$_POST['search']){
            print '<b>Search the Online Reporting Report for all access that someone should curently have!</b><br><br>';
            print '<form action="OLRReportReaderV2.php" method="post">
            Search: <input type="text" name="search" /><br />
          <input type="submit" name="submit" value="Submit" />
          </form>';
        }else{//ok search
            print '<a href="OLRReportReaderV2.php">Reset</a><br><br>';
            //print "found file";
            $objPHPExcel = PHPExcel_IOFactory::load($target_dir."\Online Reporting Access Query.xlsx");

            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,false,true);
            //setup what we know from our report
            foreach($sheetData as $key => $data){
                
                                     //setup the unit knowledge garden
                                     $unitKnowledge[$data["A"]] = array("Unit Type"=>$data["A"], "Sector" =>$data["F"], "President"=>$data["G"], "RVP"=>$data["H"], "RD"=>$data["I"], "District"=>$data["J"], "DMA"=>$data["K"],"OLR1"=>$data["L"], "OLR2"=>$data["M"], "OLR3"=>$data["N"]);
                                     $tempD = explode("-",$data["J"]);
                                     $tempD2 = str_replace(' ', '', $tempD[0]);
                                     if($tempD2 == ''){$tempD2 = 'None';}
                                     //print $tempD2." == ".$data["A"]."<br>";
                                     $districtKnowledge[$tempD2][count($districtKnowledge[$tempD2])] = $data["A"];
                                     $unitLineByLineToSearch[$data["A"]] = $data["A"].$data["F"].$data["G"].$data["H"].$data["I"].$data["J"].$data["K"].$data["L"].$data["M"].$data["N"];
            }
            ////end report loop/////
            //var_dump($districtKnowledge);
            ////////now look at each unit /////////
                                        //unit knowledge tester
                                        //var_dump($TMUnitKnowledge);
                            $output .= "<table border=\"1\"><th>Unit</th><th>Dist</th><th>Pres</th><th>RVP</th><th>RD</th><th>DMA</th><th>Code 1</th><th>Code 2</th><th>Code 3</th><th>Sector</th>";
                            $foundUnitCount = 0;
                            $searchString = $_POST['search'];
                            $andSearchLogic = explode("&",$searchString);//added 4/13/2021 cause of J.Belanger != Johanne Belanger
                            foreach($unitLineByLineToSearch as $key => $data){
                                $searchItemFound = FALSE;
                                foreach($andSearchLogic as $key2 => $data2){
                                    if(strpos(strtolower(str_replace(" ","",$data)),strtolower(str_replace(" ","",$data2))) !== false){$searchItemFound = TRUE;$foundUnitCount++; }
                                }
                                if ($searchItemFound) {
                                    //print $key."<br>";
                                    $output .= "<tr><td>".$key."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["District"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["President"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["RVP"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["RD"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["DMA"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["OLR1"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["OLR2"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["OLR3"]."</td>";
                                    $output .= "<td>".$unitKnowledge[$key]["Sector"]."</td></tr>";
                                    
                                    $tempD = explode("-",$unitKnowledge[$key]["District"]);
                                    $tempD2 = str_replace(' ', '', $tempD[0]);
                                    if($tempD2 == ''){$tempD2 = 'None';}
                                    //print $tempD2."<br>";
                                    $districtsFound[$tempD2][count($districtsFound[$tempD2])] = $key;//array with the dist as the key and the units the person is tagged as the values
                                }
                            }
                            $output .= "</table>";
                            $recipe = "";
                            $recipe .= "<b>".$_POST['search']." needs access to ".$foundUnitCount." units!</b><BR><BR>";
                            $recipe .= "Here is the recipe...<br><br>";
                            
                            //var_dump($districtsFound);
                               foreach($districtsFound as $key => $data){
                                   $ddUnits = "";
                                   $recipe .= "<hr><b>Dist. ".$key."</b><br>";
                                   foreach($data as $units => $unit){
                                       //print $unit."<br>";
                                       $ddUnits .= $unit.",";
                                   }
                                   
                                   $recipe .= "Add: ".str_replace(",",", ",substr($ddUnits,0,-1))."<br>";
                                   
                                   
                               }     
                            print $recipe."<br><br>".$output;
            
        }
}else{//no file found.. do that first
    print "Please load the Online Reporting Access Query.xlsx file into your Tool folder.";
    die;
}
//////////////Online Reporting Access Query.xlsx/////////////////////////