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
error_reporting(E_ERROR | E_PARSE);//skip errors
    include_once '.\_timestampLogger.php';

//Jan 2020, hack to let the DCNT use this screen as well
if(isset($_GET['DCNT'])){
    $theFile = $target_dir.$_GET['DCNT'];
    $goBack = "";
}

if(isset($_GET['file'])){
    $theFile = $target_dir.$_GET['file'];
    $goBack = "";
}
//Jan 2020, hack to arrive from ascii main tool.

if($theFile != ""){
$line = array();
$i = 0;
$invoiceCount = 0;
$enviroFees = 0;
$extraCharges = 0;
$invoiceNumbers = array();
$currentInvoiceNumber = '';
$nonAsciiDetection = array();
$vendor = "";
$invoiceDate = "";
$dcn = "";
//$catMaker = array();
//$invoiceToVendorCodeMaker = array();
$file = fopen($theFile, "r");
if ($file) {
      while (!feof($file)) {
               $aline = fgets($file);
               if(!mb_detect_encoding($aline, 'ASCII', true)){
                   if(!in_array(substr($aline,468,20), $nonAsciiDetection)){$nonAsciiDetection[count($nonAsciiDetection)] = trim(substr($aline,468,20));}    
               }
               if($currentInvoiceNumber != substr($aline,468,20)){$currentInvoiceNumber = substr($aline,468,20);$invoiceNumbers[$invoiceCount]=$currentInvoiceNumber;$invoiceCount++;}               
               $line[$i]['Line Count'] = strlen($aline);
               //$line[$i]['Distributor ID Number'] = trim(substr($aline,0,5));
               $line[$i]['DCN'] = trim(substr($aline,5,20));
               //$line[$i]['Distributor Customer Name'] = trim(substr($aline,25,40)); 
               //$line[$i]['Customer Street Address'] = trim(substr($aline,65,40)); 
               //$line[$i]['Customer Postal'] = trim(substr($aline,105,12)); 
               $line[$i]['Invoice Date'] = trim(substr($aline,117,8));
               $line[$i]['Distributor Item Number'] = trim(substr($aline,125,20)); 
               //$line[$i]['Brand'] = trim(substr($aline,145,20)); 
               $line[$i]['Description'] = trim(substr($aline,165,50)); 
               $line[$i]['Pack Size'] = trim(substr($aline,215,15)); 
               //$line[$i]['Unit of Measure'] = trim(substr($aline,230,10)); 
               //$line[$i]['Customer Province'] = trim(substr($aline,240,20)); 
               //$line[$i]['Manufacturer Number'] = trim(substr($aline,260,20));
               $line[$i]['Transaction Quantity'] = trim(substr($aline,280,10)); 
               $line[$i]['Transaction Volume'] = trim(substr($aline,290,10)); 
               //$line[$i]['Transaction Currency'] = trim(substr($aline,300,3)); 
               //$line[$i]['Customer City'] = trim(substr($aline,303,30)); 
               //$line[$i]['Manufacturer Name'] = trim(substr($aline,333,30)); 
               //$line[$i]['Manufacturer Item Number'] = trim(substr($aline,363,20)); 
               //$line[$i]['UPC'] = trim(substr($aline,383,25)); 
               //$line[$i]['Category'] = trim(substr($aline,408,30)); 
               //$line[$i]['Subcategory'] = trim(substr($aline,438,30)); 
               $line[$i]['Invoice Number'] = trim(substr($aline,468,20)); 
               //$line[$i]['Category Code'] = trim(substr($aline,488,10)); 
               //$line[$i]['Distribution Center ID'] = trim(substr($aline,498,30)); 
               //$line[$i]['Substitution Indicator'] = trim(substr($aline,528,1)); 
              // $line[$i]['GTIN'] = trim(substr($aline,529,14)); 
               $line[$i]['Price'] = trim(substr($aline,543,10)); 
               //$line[$i]['Broken Case Code'] = trim(substr($aline,553,5)); 
               $line[$i]['GST Tax Code'] = trim(substr($aline,558,5)); 
               $line[$i]['PST Tax Code'] = trim(substr($aline,563,5)); 
               $line[$i]['Tax Amount GST'] = trim(substr($aline,568,10)); 
               $line[$i]['Tax Amount PST'] = trim(substr($aline,578,10)); 
               $line[$i]['Total Amount Due'] = trim(substr($aline,588,14)); 
               //$line[$i]['Trade Discount'] = trim(substr($aline,602,14)); 
               //$line[$i]['Freight Charge'] = trim(substr($aline,616,14)); 
               //$line[$i]['Fuel Surcharge'] = trim(substr($aline,630,14)); 
               //$line[$i]['Ecology Charge'] = trim(substr($aline,644,14)); 
               //$line[$i]['Freight GST'] = trim(substr($aline,658,14)); 
               //$line[$i]['Freight PST'] = trim(substr($aline,672,14));
               //$line[$i]['Extra Charge 6'] = trim(substr($aline,728,14)); 
               //$line[$i]['Extra Charge 7'] = trim(substr($aline,742,14)); 
               //$line[$i]['Extra Charge 8'] = trim(substr($aline,756,14)); 
               //$line[$i]['Extra Charge 9'] = trim(substr($aline,770,14)); 
               //$line[$i]['Extra Charge 10'] = trim(substr($aline,784,14)); 
               //$line[$i]['Container Deposit'] = trim(substr($aline,798,14)); 
               //$line[$i]['Adjustments'] = trim(substr($aline,812,14)); 
               //$line[$i]['Split Case Percent'] = trim(substr($aline,826,6));
               //$line[$i]['Additional Code 1'] = trim(substr($aline,832,4));
               //$line[$i]['Additional Code Amount 1'] = trim(substr($aline,836,14));
               //$line[$i]['Additional Code 2'] = trim(substr($aline,850,4)); 
               //$line[$i]['Additional Code Amount 2'] = trim(substr($aline,854,14)); 
               //$line[$i]['Additional Code 3'] = trim(substr($aline,868,4)); 
               //$line[$i]['Additional Code Amount 3'] = trim(substr($aline,872,14)); 
               //$line[$i]['Additional Code 4'] = trim(substr($aline,886,4)); 
               //$line[$i]['Additional Code Amount 4'] = trim(substr($aline,890,14)); 
               //$line[$i]['GST-HST No'] = trim(substr($aline,904,40)); 
               //$line[$i]['Remit Name'] = trim(substr($aline,944,40)); 
               //$line[$i]['Remit Address'] = trim(substr($aline,984,100)); 
               //$line[$i]['Remit City'] = trim(substr($aline,1084,40)); 
               //$line[$i]['Remit Prov'] = trim(substr($aline,1124,2)); 
               //$line[$i]['Remit Postal Code'] = trim(substr($aline,1126,12)); 
               $line[$i]['Vendor'] = trim(substr($aline,1138,20));
               $i++;
               //add the extracharges once
               if($i == 1){
                   $extraCharges = $line[0]['Extra Charge 3'] + $line[0]['Extra Charge 4'] + $line[0]['Extra Charge 5'];
               }
               //added 2/12/2018 to self generate cataloges
            //if(!isset($catMaker[trim(substr($aline,125,20))])){$catMaker[trim(substr($aline,125,20))] = array("Pack Size"=>substr($aline,215,15),"Description"=>trim(substr($aline,165,50)));}
            //if(!isset($invoiceToVendorCodeMaker[trim(substr($aline,468,20))])){$invoiceToVendorCodeMaker[trim(substr($aline,468,20))] = array("DCN"=>substr($aline,5,20));}
        }
    fclose($file);
    
  } else {
      //error reading file
}

//display back
/*
for($x=0;$x <= $i;$x++){//will make into links at somepoint
    print $x;
    if($x < $i){print ' | ';}
}
*/


//setup some validation stuff
$validation[0] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[1] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[2] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[3] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[4] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[5] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[6] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[7] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[8] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[9] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[10] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[11] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[12] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[13] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[14] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[15] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[16] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[17] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[18] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[19] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[20] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[21] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[22] = array('foodbuy'=>'yes', 'cims'=>'no');
$validation[23] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[24] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[25] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[26] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[27] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[28] = array('foodbuy'=>'yes', 'cims'=>'yes');
$validation[29] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[30] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[31] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[32] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[33] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[34] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[35] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[36] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[37] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[38] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[39] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[40] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[41] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[42] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[43] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[44] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[45] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[46] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[47] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[48] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[49] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[50] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[51] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[52] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[53] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[54] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[55] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[56] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[57] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[58] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[59] = array('foodbuy'=>'no', 'cims'=>'no');
$validation[60] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[61] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[62] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[63] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[64] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[65] = array('foodbuy'=>'no', 'cims'=>'yes');
$validation[66] = array('foodbuy'=>'no', 'cims'=>'yes');

$tableHeader = '';
$tableRows = '';
$invoiceTotalForHeaderDetailMatch = 0;
$invoiceTotalCalPrice = 0;
$invoiceTotalCalGST = 0;
$invoiceTotalCalPST = 0;
$numberOfItemsWithPst = 0;
$numberOfItemsWithGst = 0;
$itemValueWithGst = 0;
$itemValueWithPst = 0;
$makeHeader = TRUE;

$invoiceNumberToDisplay = trim($invoiceNumbers[0]);
if(isset($_GET['Invoice'])){$invoiceNumberToDisplay = $_GET['Invoice'];}//DCNT come in like this
if(isset($_GET['invoice'])){$invoiceNumberToDisplay = $_GET['invoice'];}//Audit like this

foreach($line as $key=>$value){
    $z = 0;
        if(trim($value['Invoice Number']) == $invoiceNumberToDisplay){
            //revers engineer the math
            if(trim($value['Distributor Item Number']) == 'C680' || trim($value['Distributor Item Number']) == 'B570'){
                 $enviroFees += ($value['Transaction Quantity'] * $value['Price']);
            }else{
                $invoiceTotalCalPrice += ($value['Transaction Quantity'] * $value['Price']);
            }
            
            if(trim($value['GST Tax Code']) > 0 && trim($value['Tax Amount GST']) != 0){
                $numberOfItemsWithGst++;
                $invoiceTotalCalGST += $value['Tax Amount GST'];
                $itemValueWithGst += ($value['Transaction Quantity'] * $value['Price']);
            }
            if(trim($value['PST Tax Code']) > 0 && trim($value['Tax Amount PST']) != 0){
                $numberOfItemsWithPst++;
                $invoiceTotalCalPST += $value['Tax Amount PST'];
                $itemValueWithPst += ($value['Transaction Quantity'] * $value['Price']);
            }
            
            if(trim($value['Distributor Item Number']) == 'TOTAL-TAX'){
                $invoiceTotalCalPST += $value['Tax Amount PST'];
                $invoiceTotalCalGST += $value['Tax Amount GST'];
                
            }
            
            $invoiceTotalForHeaderDetailMatch = trim($value['Total Amount Due']);
            $lineTotalAfterTax = ($value['Transaction Quantity'] * $value['Price']) + $value['Tax Amount GST'] + $value['Tax Amount PST'];
            $vendor = $value['Vendor'];
            $invoiceDate = $value['Invoice Date'];
            $dcn = $value['DCN'];
            
            $tableRows .= '<tr>';
            $injectLineTotalAfterTaxColumnCount = 0;
            $isSacCharge = FALSE;
        foreach($value as $key2=>$value2){
            $injectLineTotalAfterTaxA = '';
            $injectLineTotalAfterTaxB = '';
            if($injectLineTotalAfterTaxColumnCount == 34){
                $injectLineTotalAfterTaxA = '<th>Injected Line Total After Tax</th>';
                $injectLineTotalAfterTaxB = '<td>'.$lineTotalAfterTax.'</td>';
            }
            
            if(trim($value2) == 'C680' || trim($value2) == 'B570' || trim($value2) == 'D260'){
                $isSacCharge = TRUE;//skip validation then
            }
        if($key2 != 'Invoice Number'){
        if($makeHeader){$tableHeader .= '<th>'.$key2.' ('.strlen($value2).')</th>'.$injectLineTotalAfterTaxA;}    
        //setup our colours for validation
        $span = '<span>';
        //if($validation[$z]['foodbuy'] == 'yes'){$span = '<span style="color: blue;">CERES Only Required';}
        //if($validation[$z]['cims'] == 'yes' && !$isSacCharge){$span = '<span style="color: red;">CIMS Required';}
        
         if($key2 != 'Line Count'){
          if(trim($value2) != ''){
              if(!mb_detect_encoding($value2, 'ASCII', true)){//added to catch regitan french charactures
                    $tableRows .= '<td><span style="color: red;">'.str_replace(' ', '&nbsp;', $value2).'</span></td>';
                    }else{
                     $tableRows .= '<td>'.str_replace(' ', '&nbsp;', $value2).'</td>'.$injectLineTotalAfterTaxB;
                    } 
          }else{
            $tableRows .= '<td>'.$span.str_replace(' ', '&nbsp;', $value2).'</span></td>'.$injectLineTotalAfterTaxB;  
          }
        }else{
            if(!isset($_GET['DCNT'])){$tableRows .= '<td>'.($key+1).'</td>';}else{$tableRows .= '<td>'.($key+1).'</td>';}
         }
        }
         //do more cals
        $z++;
        $injectLineTotalAfterTaxColumnCount++;
        }
        $makeHeader = FALSE;//only need it once
        $tableRows .= '</tr>';
      }
    }



if($invoiceNumberToDisplay != ''){
if(!isset($_GET['DCNT'])){print 'Filename: '.$_GET['file'].'<br/><br/>';}

print $goBack;

}

$moveThisPartOfTheOutput = "";
if($tableHeader != ''){
    $calculatedInvoiceTotalForMatching = round(($invoiceTotalCalPrice + $invoiceTotalCalGST + $invoiceTotalCalPST + $enviroFees),2);
    $precentVarianceAllowed = 2.4;
    $variance = $calculatedInvoiceTotalForMatching - ($invoiceTotalForHeaderDetailMatch - $extraCharges);
    $sign = '';
    if($variance > 0.01){$sign = '+';}
    $headertotalDispaly = " = ".$invoiceTotalForHeaderDetailMatch;
    if($extraCharges != 0){
        print 'TOTAL TAX DUE DETECTED, this is a tax flag only invoice!<br>';
        $headertotalDispaly = " @ ".$invoiceTotalForHeaderDetailMatch." - ".$extraCharges." = ".($invoiceTotalForHeaderDetailMatch - $extraCharges);
    }
    print "<b>Vendor: </b>".$vendor."<br>";
    print "<b>Invoice No: </b>".$invoiceNumberToDisplay."<br>";
    print "<b>Customer No: </b>".$dcn."<br>";
    print "<b>Invoice Date: </b>".$invoiceDate."<br>";
    print "<b>Invoice total: </b>".$headertotalDispaly."<br><br>";
    $moveThisPartOfTheOutput .= 'Calculated Total = '.$calculatedInvoiceTotalForMatching.' | '.'Variance '.$sign.round($variance, 2);
    //2.4% variance is ok
    $calGSTRate = 0;
    $calPSTRate = 0;
    if($invoiceTotalCalGST != 0){$calGSTRate = round(abs($invoiceTotalCalGST) / abs($itemValueWithGst) * 100, 2);}
    if($invoiceTotalCalPST != 0){$calPSTRate = round(abs($invoiceTotalCalPST) / abs($itemValueWithPst) * 100, 2);}

    
    if(abs($invoiceTotalForHeaderDetailMatch - $calculatedInvoiceTotalForMatching - $extraCharges) > abs((($precentVarianceAllowed / 100) * $invoiceTotalForHeaderDetailMatch))){print '<br><span style="color: red;">Header Detail Mismatch!!! '.round(($invoiceTotalForHeaderDetailMatch - ($invoiceTotalCalPrice + $invoiceTotalCalGST + $invoiceTotalCalPST + $enviroFees)), 2).'</span>';}
    $moveThisPartOfTheOutput .= '<br> Calculated Amount Before Tax/Fee\'s = '.($invoiceTotalCalPrice).' | Calculated GST = '.$invoiceTotalCalGST.' @ '.$calGSTRate.'% | Calculated PST = '.$invoiceTotalCalPST.' @ '.$calPSTRate.'% | Calculated Enviro Fee = '.$enviroFees;
    $moveThisPartOfTheOutput .= '<br> Calculated Items with GST = '.$numberOfItemsWithGst.' | Calculated Total For Items With GST = '.$itemValueWithGst;
    $moveThisPartOfTheOutput .= '<br> Calculated Items with PST = '.$numberOfItemsWithPst.' | Calculated Total For Items With PST = '.$itemValueWithPst;
    print '<table border = 1><tr>'.$tableHeader.'</tr>'.$tableRows.'</table>';
    if(!isset($_GET['DCNT'])){print '<br>'.$moveThisPartOfTheOutput;}
}

}else{//no file name received so close this window
    header('Location: index.php');
}
?>
</body>
</html>