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
$files1 = scandir($target_dir);
$pageLink = str_replace("/", "", $_SERVER["PHP_SELF"]);
$invoiceCount = '';
$lastInvoiceNum = '';

$displayOut = "<table border=\"1\">";

foreach($files1 as $key=>$value){
    if($key > 1){

        //print $target_dir.$value."<br>";
    $file = fopen($target_dir.$value, "r");
        if ($file) {
            //check what kind of file
            $fileNameExplode = explode("_",$value);
            if($fileNameExplode[0] == "APDOWNLOAD"){
             while (!feof($file)) {
               $aline = fgets($file);
               $lineData = explode(",",$aline);

                        if($aline != "" && $lineData[1] != 'H'){
                                    $displayOut .= "<tr><td>".substr($lineData[0],0,-6).".".str_replace("-",".",substr($lineData[6],0,-9))."</td><td>".$lineData[5]."</td><td>CIMS Invoice ".$lineData[2]."</td></tr>";
                                    $displayOut .= "<tr><td>".substr($lineData[0],0,-6).".z</td><td>".(-1 * $lineData[5])."</td><td>CIMS Invoice ".$lineData[2]."</td></tr>";

                                    if($lastInvoiceNum != $lineData[2]){
                                        $invoiceCount++;
                                        $lastInvoiceNum = $lineData[2];
                                    }

                        }
             }
             
            }//end ap file if
            if($fileNameExplode[0] == "ESTIMATEDOWNLOAD"){
                while (!feof($file)) {
               $aline = fgets($file);
               //print $aline."<br>";
               $lineData = trim(str_replace(" ","",$aline));
               $pos = strpos($aline, '-');//only the reverse would have a minus and we make our own below
                        if($aline != "" && $pos === false){
                                    $displayOut .= "<tr><td>".substr($lineData,-5).".".substr($lineData,2,6).".".substr($lineData,8,4)."</td><td>".(substr($lineData,21,14) / 100)."</td><td>CIMS Estimate ".substr($lineData,16,2)." ".substr($lineData,18,2)." ".substr($lineData,12,4)."</td></tr>";
                                    $displayOut .= "<tr><td>".substr($lineData,-5).".z</td><td>".(-1*(substr($lineData,21,14) / 100))."</td><td>CIMS Estimate ".substr($lineData,16,2)." ".substr($lineData,18,2)." ".substr($lineData,12,4)."</td></tr>";

                        }
             }
            }//end estimate file if
        }
    }
}

$displayOut .= "</table>";

print $displayOut;

if($invoiceCount > 0){
  print '<script type="text/javascript">
  alert("There are '.( $invoiceCount - 1 ).' invoices in this file.");
</script>';
}
?>

</body>
<html>