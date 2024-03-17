<?php

// Check if the session is not active before starting it
if (!session_id()) {
    session_cache_limiter('private');
    session_cache_expire(0);
    session_start();

}

if(!isset($_SESSION['name'])){
#$nameTemp1 = explode("\\",$_SERVER['REMOTE_USER']);
$nameTemp2 = explode(".", $nameTemp1[1]);
$_SESSION['name'] = $nameTemp2[1];
if($_SESSION['name'] == ""){$_SESSION['name'] = 'DevBox';}
}

if(isset($_POST["override"]) && trim($_POST["override"]) != ""){
    if(trim($_POST["override"]) != "itnFileBot"){
    $_SESSION['name'] = trim($_POST["override"]);
    }
    
}

  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\".$_SESSION['name']."\\";//prod server
        $target_dirX = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\";
      }else{
         $target_dir = $_SESSION['name']."/";//dev server 
        
         $target_dirX = "C:\\SmartInvoice\\";
      }

#$filename=$target_dirX."lastActionTimeStamp.tmp";
$filename=$target_dirX."lastActionTimeStamp.tmp";
$log = file_get_contents($filename);
$logLines = explode("\r\n", $log);
#$logString = time().' ~ '.$_SERVER['REMOTE_USER'].' ~ '.$_SERVER['REQUEST_URI']."\r\n";
foreach($logLines as $logKey => $logVal){
    if($logKey < 10000){//only keep 10000 entries
 #   $logString .= $logVal."\r\n";
    }else{
        break;
    }
}
#file_put_contents($filename, $logString );

?>