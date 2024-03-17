<?php
ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
session_cache_limiter('private');
session_cache_expire(0);
//ini_set('display_errors',1);
//error_reporting(E_ALL);
// Show all information, defaults to INFO_ALL
//phpinfo();
include_once '.\_timestampLogger.php';
$dispaly = '<h2>Special Projects ~ Data Science Tools (SPDST)</h2><div id="msg"></div>
<div id="displayInfo"><hr><p>Mobile & Ecom Tools</p><hr>
<a href="http://'.$_SERVER['HTTP_HOST'].'/DHPayCalculator2022.php">DH Calculator 2022 (Freedom Pay)</a><br><i>2022 update to work with CSV files from Freedom Pay and the CDL API reports.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/nutrisliceDailyRecTool.php">Nutrislice Daily Rec Sheet</a><br><i>Used to make a daily balance sheet.</i><br><br>
<hr><p>App Support iTrade Tools</p><hr>
<a href="http://'.$_SERVER['HTTP_HOST'].'/checklist3.php">Checklist 3.0</a><br><i>This is where we take our chances on the open sea to collect bounty and make sure we get all our daily tasks accomplished.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/itnFileBot.php">ITN File Bot</a><br><i>Manages the massive amount of ITN files we get.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/ascii_invoice_dup_check.php">Ascii Invoice Dup Check</a><br><i>Used to inspect multipul files for duplicate invoices...</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/itnaudit.php">ITN Audit Tool (Remix)</a><br><i>Load up a batch of ITN files in your last name folder and watch the magic appear on screen.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/dcntool.php">DCNT (Customer Number Tool)</a><br><i>Load iTrade files in the folder, load the webpage and you will get a list of all customer numbers we have not marked a seen. After we have investigated the invoice, we can mark it as seen so it does not come up again or we can click the report button and it will end the file to report to Foodbuy as not our data.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/CIMSAccessChanges.php">CIMS Access Changes</a><br><i>Load the CIMS Unit User Ref files in the folder, load the webpage and you will get a list user access that has dropped off from CIMS.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/JDEVendorChanges.php">JDE Vendor Changes</a><br><i>Load the CIMS Supp Ref files in the folder, load the webpage and you will get a list Vendors that have dropped off from Orbit.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/regularExpressionFinder.php">Regular Expression Finder</a><br><i>Looking for a needle in the haystack of txt based files? This is the tool. Works with vast amounts of invoice or log files.. any txt file really. Has an invoice number list generator built in. It\'s great for those pesky 3600+ invoice days when things go sideways.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/OLRReportReaderV2.php">OLR Recipe Maker V2</a><br><i>Drop the latest Online Reporting Access Query.xlsx from Hubble in your personal folder and search up anyone you want to see the easy bake access recipe for OLR. This version looks at everything, even if there is no district setup at a cost center.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/ZipThruRecSheetMaker.php">ZipThru Rec Sheet Maker</a><br><i>A tool to help setup the monthly rec sheet for the week. Place your three reports in your personal folder and then follow the onscreen prompts for some copy & paste magic.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/CIMS_JDE_RefFileReader.php">JDE Ref file to Accrual Copy and Paste</a><br><i>This is the last script we ever want to need, it means we are having a very bad day and we need to put the CIMS JDE Ref file or Estimate File into our personal folder then click this button so we can get an easy copy & paste accrual done manually in JDE :s. Yes, this will work with either of those Ref files.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/registerFive0.php">Register Five-0</a><br><i>Tool used to keep Orbit regsiters clean and to help us make a list of \'what is out there\' as we are often asked for that.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/Company100VarianceReportReader.php">Company 100 Balance Helper Tool.</a><br><i>This can scan the Givex CWS report and work with the HL01010 report to find crazy transactions.</i><br><br>
<a href="http://'.$_SERVER['HTTP_HOST'].'/OrbitFineHelper.php">Orbit Fine Helper.</a><br><i>Stick the Orbit Deliquent report in your personal folder and watch the magic. This tool plays with the Looks Stuff Up Machine data as well.</i><br><br></div>';

print $dispaly;

print '<div id="footer"><hr>Connected as '.$_SESSION['name'].'</div>';
ob_end_flush();
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
<script type="text/javascript">
    function loading(){
            document.getElementById("displayInfo").innerHTML = '<span id="wait" style="font-size: 80px;">.</span>';
            document.getElementById("footer").innerHTML = "";
            document.getElementById("msg").innerHTML = "<i>Crushing the big data, please stand by ;)</i>";
    } 
   
   //used when changing reports
   var dots = window.setInterval( function() {
    var wait = document.getElementById("wait");
    if ( wait.innerHTML.length > 20 ) 
        wait.innerHTML = "";
    else 
        wait.innerHTML += ".";
    }, 100);
   
</script>
<body>


<form action="index.php" method="post">
   <p>Override Folder:<br><input type="text" name="override" /><br><input type="submit" name="submit" value="Engage" /></p>
</form>
</body>
</html>
