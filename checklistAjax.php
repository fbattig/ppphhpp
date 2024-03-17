<?php
session_start();
error_reporting(E_ALL);
if(!isset($_SESSION['pirateCoolDown'])){$_SESSION['pirateCoolDown'] = 0;}

//setup our file locations
include_once("checklistFileLocations.php");
   
$currentDate = date('m/d/Y');
$dataFile = str_replace("/","",$currentDate).".txt";

$rowToUpdate = $_POST["dbid"];//we start at line one not zero
$status = $_POST["status"];
$newNote = trim($_POST["note"]);
$panic = $_POST["panic"];

$returnToThem = "DB Error!";//over write if all goes well
//debug the file location
//print $goodNewsFile = str_replace("ListOfPirates","GoodNews",$pirate_list);


//now figure out who is looking at this
$nameTemp1 = explode("\\",$_SERVER['REMOTE_USER']);
if(isset($nameTemp1[1])){$userName = $nameTemp1[1];}
if(!isset($userName)){$userName = "DevBox";}

$date = new DateTime();

//start priate coin game, each time a task is closed the team all get coins.. but not at the same rate :)     
if($status == 'Complete' || (strlen($newNote) > 18 && ($date->getTimestamp() - $_SESSION['pirateCoolDown']) > 600) || $panic == 'yes' ){
    $_SESSION['pirateCoolDown'] = $date->getTimestamp();

    $youLose = rand(1,4);
    $myfile = fopen($pirate_file, "w") or die("Unable to open file!");
    
    foreach($players as $player=>$cash){
        
        $lastCash = $cash;//so we can see the up down movement;
        if($panic == $yes){//punished
            if($player == $userName){
                $newCash = $cash - $youLose;
                $upDown = $newCash - $cash;
                $simple_string = $newCash."|".$player."|".$upDown."|because they reopened a task!";
            }else{//no change to player cause someone else paniced
                $simple_string = $cash."|".$player."|".$piratesLastMovement[$player]."|".$piratesLastMessage[$player];
            }
        }else{//not panic, normal
            $pirateChance = rand(1,100000);
            $awesomeTopUpPirateCash = 0;
            if($pirateChance == 42){$awesomeTopUpPirateCash = 500;}
            if($player == $userName){
                $newCash = $cash + rand(-4,10) + $awesomeTopUpPirateCash;
                $upDown = $newCash - $cash;
                $simple_string = $newCash."|".$player."|".$upDown."|".funPirateMessage($lastCash,$newCash,$player,$pirate_list);
            }else{
                //1 in 10 chance
                $theyWantMoreRandomChancesForMoney = rand(1,200);
                if($theyWantMoreRandomChancesForMoney == 4){
                    $awesomeTopUpPirateCash = rand(3,20);
                }
                //back to normal chance
                $newCash = $cash + rand(-1,5) + $awesomeTopUpPirateCash;
                $upDown = $newCash - $cash;                
                $simple_string = $newCash."|".$player."|".$upDown."|".funPirateMessage($lastCash,$newCash,$player,$pirate_list);
            }
            /*
            if($pirateChance == 999){//super rare chance you lose all
                $newCash = 0;
                $upDown = $newCash - $cash;  
                $simple_string = $newCash."|".$player."|".$upDown;
            }
             */
        }
        //write back to the bank
            //debug var print
            //print $simple_string."<br>";
            fwrite($myfile,openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv)."\n");
    }
    fclose($myfile);
}
//end pirate coin game   


//open the DB file to update it.
$hideNoteDateIfCurrentDay = ' ('.$currentDate.')';
$lineData = "";//need to rebuild our txt file
$lineCounter = 0;//use to find the line we are updating, line 0 is the team note
if(isset($_POST["dbid"])){//make sure Ajax is calling and not a luck user goofing around
        $theDBFile = fopen($target_dir.$dataLocation.$dataFile, "r");
                    if ($theDBFile) {
                        while (!feof($theDBFile)) {//loop and get the existing data
                                $aline = fgets($theDBFile);
                                        if($rowToUpdate == $lineCounter){//line to update
                                            
                                            /*
                                            $mapBlock = explode("~",$aline);
                                            $oldNoteTemp = explode("|",$mapBlock[1]);
                                            $oldNote = $oldNoteTemp[2];
                                            if($newNote != ""){
                                                $theNote = $oldNote[2]."<p><span style=\"color:#404040;font-size:0.75em;\"><i>".$userName." (".$currentDate.")</i></span><br>".$newNote."</p>";
                                            }else{
                                                $theNote = $oldNote[2];
                                            }
                                             * */
                                            $theNote = "";
                                            $mapBlock = explode("~",$aline);
                                            $oldNote = explode("|",$mapBlock[1]);
                                            
                                            if($newNote == ""){
                                                $theNote = $oldNote[2];
                                            }else{
                                                $ubberTempNote = "<p><span style=\"color:#404040;font-size:0.75em;\"><i>".$userName." (".$currentDate.")</i></span><br>".$newNote."</p>";
                                                $oldNote[2] = str_replace($ubberTempNote,'',$oldNote[2]);
                                                $theNote = $oldNote[2].$ubberTempNote;
                                            }
                                            
                                            switch ($status) {
                                                    case 'Not Started':
                                                        $lineData .= $mapBlock[0]."~".$status."||".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = "TBD<br>".makeStatusReturner($status,$rowToUpdate)."~".makeNoteReturner($rowToUpdate,$theNote,$currentDate)."~pink";
                                                        break;
                                                    case 'Working it':
                                                        $lineData .= $mapBlock[0]."~".$status."|".$userName."|".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = $userName."<br>".makeStatusReturner($status,$rowToUpdate);
                                                        if($panic == 'yes'){$returnToThem .= "<br><br>ミ●﹏☉ミ<br>That cost you ".$youLose." Chuck Bucks!";}
                                                        $returnToThem .= "~".makeNoteReturner($rowToUpdate,$theNote,$currentDate)."~cyan";
                                                        break;
                                                    case 'Complete':
                                                        $lineData .= $mapBlock[0]."~".$status."|".$userName."|".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = $userName."<br>".$status."<div class=\"emergReopen\"><a onclick=\"panic(".$rowToUpdate.")\">&#8226;&#8226;&#8226;</a></div>~".str_replace($hideNoteDateIfCurrentDay,"",$theNote)."~lightgreen";
                                                        break;
                                                    case 'NA':
                                                        $lineData .= $mapBlock[0]."~".$status."|".$userName."|".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = $userName."<br>".$status."<div class=\"emergReopen\"><a onclick=\"panic(".$rowToUpdate.")\">&#8226;&#8226;&#8226;</a></div>~".str_replace($hideNoteDateIfCurrentDay,"",$theNote)."~lightgrey";
                                                        break;
                                                    default:
                                                        $returnToThem = "Error in code or Map~Get Help!~lightgrey";
                                                        break;
                                                }
                                        }else{
                                            $lineData .= $aline;
                                        }
                                $lineCounter++;
                             }
                    }
                 fclose($theDBFile);
                 
                 if(trim($lineData) != "" && ($oldFileLookForChange != $lineData)){//make sure we are not hosed with no data
                  file_put_contents($target_dir.$dataLocation.$dataFile, $lineData);
                  file_put_contents($target_dir.$backupLocation.time(), $lineData);
                  }
}

print $returnToThem;


function makeNoteReturner($row,$note,$date){
    $hideNoteDateIfCurrentDay = ' ('.$date.')';
    $r = str_replace($hideNoteDateIfCurrentDay,"",$note)."<br><textarea class=\"form".$row."\" id=\"textbox".$row."\" name=\"note\" form=\"form".$row."\" rows=\"2\" ></textarea><button type=\"button\" class=\"button2\" onclick=\"updateDBNotes('form".$row."')\">&#x2713;</button>";
    
    return $r;
}

function makeStatusReturner($status,$formID){
       $r = '<select class="form'.$formID.'" name="status" id="status'.$formID.'" onchange="updateDBNotes(\'form'.$formID.'\')">
          <option value="Not Started">Not Started</option>
          <option value="Working it">Working it</option>
          <option value="Complete">Complete</option>
          <option value="NA">NA</option>
          </select>';
    if($status == 'Working it'){
        $r = '<select class="form'.$formID.'" name="status" id="status'.$formID.'" onchange="updateDBNotes(\'form'.$formID.'\')">
          <option value="Not Started">Not Started</option>
          <option value="Working it" selected>Working it</option>
          <option value="Complete">Complete</option>
          <option value="NA">NA</option>
          </select>';
    }
    if($status == 'Complete'){
        $r = 'Complete';
    }
    if($status == 'NA'){
        $r = 'NA';
    }
       
    return $r;
}

function funPirateMessage($oldCash,$newCash,$player,$pirate_list){
    $name = explode(".", $player);
    $getsMoney = array();
    $losesMoney = array();
    //good news
    $goodNewsFile = str_replace("ListOfPirates","GoodNews",$pirate_list);
    $fn = fopen($goodNewsFile,"r");
    while(! feof($fn))  {
	$result = fgets($fn);
        $getsMoney[count($getsMoney)] = trim($result).".";//in this array the player name is the key, we will update the null in the code below
    }

    fclose($fn);
    unset($fn);//clean up mem

    //bad news
    $badNewsFile = str_replace("ListOfPirates","BadNews",$pirate_list);
    $fn = fopen($badNewsFile,"r");
    while(! feof($fn))  {
	$result = fgets($fn);
        $losesMoney[count($losesMoney)] = trim($result).".";//in this array the player name is the key, we will update the null in the code below
    }

    fclose($fn);
    unset($fn);//clean up mem
    
    
    $movement = $oldCash - $newCash;
    if($movement != 0){
        if($movement < 0){
            //postive message
            $myRandom = rand(0, (count($getsMoney) - 1));//might have to be -1?
             return $getsMoney[$myRandom];
        }else{
            //negative message
            $myRandom = rand(0, (count($losesMoney) - 1));//might have to be -1?
             return $losesMoney[$myRandom];
        }
    }else{
        return "just chillin out.";
    }
}