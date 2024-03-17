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
//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '.\_timestampLogger.php';
////////////////////////////////////////////////////////////////
//if you can can get a list of already loaded invoices from dev, make an array here and it will add to the output 
//if edi files are load you will also get a nice invoice list.. for ascii its a best guess based on line terminator :)
$find = '';
if(isset($_GET['find']) && trim($_GET['find']) != '' ){
    $find = trim($_GET['find']);
}
print '--------------------';
print $find;
print '--------------------';
$foundIn = array();
////////////////////////////////////////////////////////////////////////
$files1 = scandir($target_dir);
$edi810count = 0;
$lastInvoiceNum = '';
$asciiInvoicesFound = 0;
$lastFileCheckedA = '';
$lastFileCheckedB = '';
$invoiceList = '<br><br>';
$customerNumbers = array();
$lineWhereFindWasFound = array();
$filesList = '<hr/>';

print '<a href=".\index.php">Home</a><hr>';
print '<b>Welcome to the Regular Expression Finder, your tool to scan vast amounts of txt based files looking for the needle in your haystack.</b><br><i>Useful for scanning 810\'s, EDI files or even FTP logs.. Recommend you only put one type of file in the search folder at a time, a mix could cause isses with the automatic invoice number list generation system if you are scanning invoice files.</i>';

if(count($files1) > 2){
    
    $filesList .= '<b>Loading</b><br>These are the files we have loaded in the special folder to check...<br/>';
    
foreach($files1 as $key=>$value){
if($key > 1){
    $filesList .= $value.'<br>';

    $tempIsCompass = FALSE;
$file = fopen($target_dir.$value, "r");
if ($file) {
      while (!feof($file)) {
            if(is_array($explodeForEdi)){unset($explodeForEdi);} 
            $aline = fgets($file);
               $explodeForEdi = explode("~",$aline);
               if($explodeForEdi > 1){
                   foreach($explodeForEdi as $key => $val){
                       $tempReExplode = explode("*",$val);
                       if($tempReExplode[0] == 'BIG'){
                           $invoiceList .= $tempReExplode[2]."<br>";
                       }
                       $tempReExplode = explode("|",$val);
                       if($tempReExplode[0] == 'BIG'){
                           $tempIsCompass = $tempReExplode[2]."<br>";
                       }
                       if($tempIsCompass != FALSE){
                           //print $tempReExplode[8];
                           //print $tempReExplode[3]."<br>";
                           if($tempReExplode[3] == 'Compass Acct?' && $tempReExplode[2] == 'Y'){
                               $invoiceList .= $tempIsCompass."<br>";
                               $tempIsCompass = FALSE;
                           }
                       }
                       
                   }
               }
               else
                   {//ACSSI File
               if(strlen($aline) == 1160 && (trim(substr($aline,468,20)) != $lastInvoiceNum)){//see if we need this invoice number as ascii is a repeating loop each line that has this number over and over again
                   //see if we need our file name cause this is a new file
                   if($lastFileCheckedA != $value){
                       $invoiceList .= $value.'<br>';
                       $lastFileCheckedA = $value;
                   }
                   //add the invoice number to the list
                   $lastInvoiceNum = trim(substr($aline,468,20));
                   $invoiceList .= $lastInvoiceNum.'<br>';
                   $asciiInvoicesFound++;
                   if(!in_array($customerNumbers, trim(substr($aline,5,20)))){$customerNumbers[count($customerNumbers)] = trim(substr($aline,5,20));}
                }
               }
             #  print $aline;
               if(strpos($aline, $find)){
                   //only add if we don't know the file name already
                   if($lastFileCheckedB != $value){
                   $foundIn[count($foundIn)] = $value;
                   $lastFileCheckedB = $value;
                   }
                   //now add it to our quick output dash board for none ivoices files.. like logs
                   $lineWhereFindWasFound[count($lineWhereFindWasFound)] = $aline;
               }
        }  
 
    fclose($file);
   }
  } 
}

//Give them a search form
print '<hr><b>Search Form</b><br><form action="'.$_SERVER['PHP_SELF'].'" method="get">
Needle: <input type="text" name="find"><br><br>
<input type="submit">
</form>';

//only show this if they actually searched for something
if(isset($find) && $find != ''){
print "<hr><b>Results</b><br/>";
if($edi810count > 0){print 'EDI file(s) detected, there were '.$edi810count.' invoices counted.<br><br>';}
if($asciiInvoicesFound != 0){print 'Ascii Invoice file(s) detected, there were '.$asciiInvoicesFound.' invoices counted.<br><br>';}
if(count($foundIn) > 0){
    print "~ <span style=\"color: #00738C;font-size: 2em;\">".$find."</span> ~ was found in the following files:";
    foreach($foundIn as $key=>$value){
        print '<br><a href="ascii_checker_v2.php?file='.$value.'" target="_BLANK">'.$value.'</a>';
    }
    if($invoiceList == '<br><br>' && (count($lineWhereFindWasFound) > 0)){
        print '<br><br>Heads Up!<br><br>';
        foreach($lineWhereFindWasFound as $aKey => $aVal){
            print $aVal.'<br>';
        }
    }
}else{
    print "~ <span style=\"color: red;font-size: 2em;\">".$find."</span> ~ was not found in any of these files :(";
}
}

}else{
    print '<hr>Before you can search though txt files you will need to place them in your working folder ('.$_SESSION['name'] .') then <a href="'.$_SERVER['REQUEST_URI'].'">reload</a> this page.';
}


if($invoiceList != '<br><br>'){
print '<hr><b>Invoice files deteceted, here is a list of all the invoice numbers.</b>'.$invoiceList;
}

if($filesList != '<hr>'){
    print $filesList;
}

if(count($customerNumbers) > 0){
    print '<hr>Here is a list of customer number used by the vender.<br>';
    foreach($customerNumbers as $val){
        print '<br>'.$val;
    }
}

?>
</body>
<html>