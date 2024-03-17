<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title>App Support Team Check List Search</title>

<style>
    .upper {
       background-color: black;
      color: white;
      margin: 20px;
      padding: 20px;
    }

    td{
        padding: 2px;
    }
    h4{
        padding: 2px;
        margin: 2px;
    }
</style>

</head>

<body>
<?php
if(isset($_POST['searchString'])){
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    //we want 3 charactures before we actually search stuff
    if(strlen(trim($_POST['searchString'])) < 3){
        header("Location: ".$actual_link);
    }
    print '<a href="'.$actual_link.'">Reset</a><br><br>';
    
      if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\";//prod server
        $dataLocation = "data\\";
      }else{
         $target_dir = "checklist/";//dev server 
         $dataLocation = "data/";
      }
    
    $searchString = strtolower($_POST['searchString']);  
    
    $results = array();
    //now collect a list of files to scan
    $filesS = scandir($target_dir.$dataLocation);
    $files1 = array_reverse($filesS);
    
    foreach($files1 as $key=>$val){
        if(trim($val) != "." && trim($val) != ".."){
                //print $val."<br>";//file name here
                $dateVal = explode(".",$val);
                $file = fopen($target_dir.$dataLocation.$val, "r");
                    if ($file) {
                    while (!feof($file)) {
                            //inside a file
                            $aline = fgets($file);
                            //$pipeFieldData = explode("|",$aline);
                            //print $pipeFieldData[6]."<hr/>";
                            $lookAt = explode("~",$aline);
                            $escalationCheck = explode("|",$lookAt[0]);
                            //print $escalationCheck[0]."<br>";
                            if($escalationCheck[0] == "Escalations"){
                                $lookAt[1] = $aline;//put it all back cause this is an escalation link we want to search all of it unlike a regular line
                            }
                            if(strpos(strtolower($lookAt[1]),$searchString) !== false){
                                    $results[$dateVal[0]][count($results[$dateVal[0]])] = $aline;
                            }
                    }
                }
                
        }
    }
    
    
    //display results
    foreach($results as $key=>$val){
        print substr($key,0,2)." ".substr($key,2,-4)." ".substr($key,-4)."<br><table border=\"1\">";
        foreach($val As $key2=>$val2){
            $twoParts = explode("~",$val2);
            $partOneParts = explode("|",$twoParts[0]);
            $partTwoParts = explode("|",$twoParts[1]);
            print "<tr><td>".$partOneParts[0]."</td><td>".$partOneParts[1]."</td><td>".$partTwoParts[0]."</td><td>".$partTwoParts[1]."</td><td>".$partTwoParts[2]."</td></tr>";
        }
        print "<table/><br>";
    }
    
}else{
    //display search form
    print 'Search the check list data for old entries:<form action="checklistSearch.php" method="post">
    <input type="input" name="searchString" id="searchString">
    <input type="submit" value="Engage">
    </form>'; 
}
 ?>
    </body>
</html>