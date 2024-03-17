<?php 
ob_start(); 

// Define the target directory for scanning files
include_once './_timestampLogger.php';

// Initialize variables
$lastInvoiceNumberUsed = '';
$lastUsedLineItemNumber = 0;
$InvoiceNumberList = array();
$InvoicesThatRepeatLineItems = array();
$vendorsDetected = array();
$fileList = "";
$line = array();
$currentInvoiceNumber = '';
$invoiceNumbers = array();
$invoiceCount = 0;
$i = 0;

$files = scandir($target_dir);
?>

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
////////////////////////////////////////////////////////////////////////
print '<a href=".\index.php">Home</a><hr>';
print '<h3>Ascii File Checker - CIMS Dulplocate Invoice Issues Tool</h3>';

////////////////////////////////////////////////////////////////////////


if(count($files) > 2){
  
    print "This tool can be used to see if the same invoice number appears in an invoice file or multipul invoice files if loaded into the special folder. When a Vendor does attempt to load the invoice more then once in CIMS on the same day the files seem to pass through to the archives but there is no trace of the files in the CIMS log to trouble shoot.<br><br>";

foreach($files as $key=>$value){
if($key > 1){
    if(strtolower($value) != 'isloaded.txt'){$fileList .= $value.'<br>';}

$file = fopen($target_dir.$value, "r");
//print $target_dir.$value."<br>";
if ($file) {
       set_time_limit(5000);
      while (!feof($file)) {
               $aline = fgets($file);
               if(strtolower($value) == 'isloaded.txt'){
                   $InvoiceNumberList[count($InvoiceNumberList)] = trim($aline);
               }else{
               if($currentInvoiceNumber != substr($aline,468,20)){$currentInvoiceNumber = substr($aline,468,20);$invoiceNumbers[$invoiceCount]=$currentInvoiceNumber;$invoiceCount++;}               
               

               //check for repeating item numbers, added when we hag Regtian suprise us with sac charges
               if(substr($aline,125,20) == $lastUsedLineItemNumber){
                   if(!in_array($currentInvoiceNumber, $InvoicesThatRepeatLineItems)){$InvoicesThatRepeatLineItems[count($InvoicesThatRepeatLineItems)] = $currentInvoiceNumber;}
               }
               $lastUsedLineItemNumber = substr($aline,125,20);
               
               
               $line[$i]['File Name'] = $value;
               $line[$i]['Distributor Customer Number'] = substr($aline,5,20);
               $line[$i]['Invoice Number'] = trim(substr($aline,468,20));
               $line[$i]['Supplier ID'] = substr($aline,1138,20); 
               //now build a list of numbers so we can dup detect
                                
               if($lastInvoiceNumberUsed != $line[$i]['Invoice Number']){//cause in a file there can be multiple acsending rows with the same invoice number but if that squence happens again its an issue in the CIMS upload process... this will help catch that issue in the file(s) we are comparing 
                 $InvoiceNumberList[count($InvoiceNumberList)] = $line[$i]['Invoice Number'];
                 $lastInvoiceNumberUsed = $line[$i]['Invoice Number'];
               }
               if(!in_array(substr($aline,1138,20), $vendorsDetected) && substr($aline,1138,20) != ""){$vendorsDetected[count($vendorsDetected)] = substr($aline,1138,20);}
               $i++;
            }
        }
        $currentInvoiceNumber = '';
    }
    set_time_limit(5000); 
   # fclose($handle);
 }
}
//display, cause this is borrowed from ascii_checker it loops all the lines in a file, so if an invoice is spread over multiple acsending lines it will skip
$invoiceNumberCounts = array_count_values($InvoiceNumberList);//pivots the array so x,x,x,y would be key x = 3, key y = 1

//var_dump($invoiceNumberCounts);

//var_dump($line);
print "<br><table border = 1>";
$fileNameCheck = '';
foreach($line as $key=>$value){
    
    if(trim($value['Invoice Number']) != ""){//skip blanks
                $currentLineInvoiceNumberFileName = $value['Invoice Number'].$value['File Name'];
                if($fileNameCheck != $currentLineInvoiceNumberFileName){
                    if($invoiceNumberCounts[$value['Invoice Number']] > 1){
                        print "<tr><td><span style=\"color: red;\">".$value['Invoice Number']."</span></td><td>".$value['File Name']."</td></tr>";
                    }else{
                        print "<tr><td>".$value['Invoice Number']."</td><td>".$value['File Name']."</td></tr>";
                    }
                    $fileNameCheck = $value['Invoice Number'].$value['File Name'];
                }
    }
}

    print "</table>";
    print "<br><br><b>Files checked:</b><br>".$fileList;
    print '<hr/><b>Vendors Detected</b><br><br>';
foreach($vendorsDetected as $val){print $val.'<br>';}
print '<hr/>';
    print "<hr><i>NOTE: There is a secondary function this tool can be used to perform. If you leave a txt file named 'isloaded.txt' in the special folder with invoices number, one per line, the code will compare that list to the invoices found in the invoice files and point out which invoices are in the files. This is useful when something goes wonky with the invoice upload job in CIMS and only part of the set of invoices are loaded from a file or any reason there is a potential discrepancy in what should have loaded. The Dev person for CIMS could provide you with the list of invoices already loaded for the specific vender from Prod and in the specific questionable time frame.</i>";
}else{
    print $firstName.', please load some .txt files in the shared folder for this app <a href="file:\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\ascii_invoice_dup_check">here</a>! Then <a href="'.$_SERVER['REQUEST_URI'].'">reload</a> this page.';
}

?>
</body>
</html>