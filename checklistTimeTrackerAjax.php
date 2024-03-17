<?php
session_start();
ini_set("session.gc_maxlifetime", "86400");
//setup our file locations
  if($_SERVER['SERVER_NAME'] == "spdst.canada.compassgroup.corp"){  
        $target_dir = "\\\\Canada.CompassGroup.Corp\lo\Accounting SPDST\\checklist\\";//prod server
        $logLocation = "timelogs\\";
      }else{
         $target_dir = "checklist/";//dev server 
         $logLocation = "timelogs/";
      }
      
      
//now figure out who is looking at this
$nameTemp1 = explode("\\",$_SERVER['REMOTE_USER']);
if(isset($nameTemp1[1])){$userName = $nameTemp1[1];}
if(!isset($userName)){$userName = "DevBox";}

$logFile = $target_dir.$logLocation.$userName.' - W'.date('W-Y',time()).'.csv';

$headers = '';
if(!file_exists($logFile)){$headers = "Task,Start Date Time,Minutes On Task\n";}

$timeOnTask = 'do nothing';


if($_SESSION['currentTaskStart'] > 0){
   $timeOnTask = round(((time() - $_SESSION['currentTaskStart'])/60),2);
   
    if($timeOnTask > (60 * 9)){$timeOnTask = 60;}//if someone says the took 9 hours on a task I will eat my hat.. slap them back to 1 hour
   
       file_put_contents($logFile, $headers.$_SESSION['currentTask'].','.date('m-d-y g:i a',$_SESSION['currentTaskStart']).','.$timeOnTask."\n", FILE_APPEND | LOCK_EX);
       $_SESSION['currentTask'] = $_POST["currentTask"];
       $_SESSION['currentTaskId'] = $_POST["currentTaskId"];
       $_SESSION['currentTaskStart'] = time();

}else{
    $_SESSION['currentTaskStart'] = time();
    $_SESSION['currentTask'] = $_POST["currentTask"];
    $_SESSION['currentTaskId'] = $_POST["currentTaskId"];
}


//print 'Returning From Time Tracker Ajax! '. $timeOnTask;

/*
$rowToUpdate = $_POST["dbid"];//we start at line one not zero
$status = $_POST["status"];
$newNote = trim($_POST["note"]);
$panic = $_POST["panic"];

$returnToThem = "DB Error!";//over write if all goes well

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
                                            
                                            $theNote = "";
                                            $mapBlock = explode("~",$aline);
                                            $oldNote = explode("|",$mapBlock[1]);
                                            
                                            if($newNote == ""){
                                                $theNote = $oldNote[2];
                                            }else{
                                                $theNote = $oldNote[2]."<p><span style=\"color:#404040;font-size:0.75em;\"><i>".$userName." (".$currentDate.")</i></span><br>".$newNote."</p>";
                                            }
                                            
                                            switch ($status) {
                                                    case 'Not Started':
                                                        $lineData .= $mapBlock[0]."~".$status."||".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = "TBD<br>".makeStatusReturner($status,$rowToUpdate)."~".makeNoteReturner($rowToUpdate,$theNote,$currentDate)."~pink";
                                                        break;
                                                    case 'Working it':
                                                        $lineData .= $mapBlock[0]."~".$status."|".$userName."|".preg_replace( "/\r|\n/", "",str_replace("~","", str_replace('|','',$theNote)))."\r\n";
                                                        $returnToThem = $userName."<br>".makeStatusReturner($status,$rowToUpdate);
                                                        if($panic == 'yes'){$returnToThem .= "<br><br>ミ●﹏☉ミ";}
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
