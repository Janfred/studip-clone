<?
/*
This file is part of StudIP -
chat_client.php
Das Ausgabefenster des Chats. Hier werden alle Chat Events bearbeitet
Copyright (c) 2002 André Noack <andre.noack@gmx.net>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	page_close();
	die;
}

require "ChatShmServer.class.php";
//Studip includes
require "msg.inc.php";
require "visual.inc.php";
require "messaging.inc.php";

//shutdown funktion, wird automatisch bei skriptende aufgerufen
function chatLogout(){
     global $userid,$chatid,$chatServer;
     $chatServer->removeUser($userid,$chatid);
}

//Hilfsfunktion, druckt script tags
function printJs($code){
     printf("<script type=\"text/Javascript\">%s</script>\n", $code);
}
//Hilfsfunktion, gibt vollen Namen inkl usernamen zurück
//function fullNick($userid) {
//     global $chatServer,$chatid;
//     return $chatServer->chatUser[$userid][$chatid]["fullname"]." (".$chatServer->chatUser[$userid][$chatid]["nick"].")";
//}

function fullNick($userid) {
     global $chatServer,$chatid;
     return $chatServer->chatUser[$userid][$chatid]["nick"];
}

//Hilfsfunktion, unterscheidet zwischen öffentlichen und privaten System Nachrichten
function chatSystemMsg(&$msg,&$output){
     global $user,$chatServer;
     $id=substr(strrchr ($msg[0],":"),1);
     if (!$id) {
          printJs("parent.frames['frm_nicklist'].location.href=parent.frames['frm_nicklist'].location.href;");
          $output=strftime("%T",floor($msg[2]))."<i> [chatbot] $msg[1]</i><br>";
     }
     elseif ($user->id==$id){
          $output=strftime("%T",floor($msg[2]))."<i> [chatbot] $msg[1]</i><br>";
     }
     return;
}
//Die Funktionen für die Chatkommandos, für jedes Kommando in $chatCmd muss es eine Funktion geben
function chatCommand_color($msgStr,&$output){
     global $user,$chatServer,$chatid;
     if (!$msgStr OR $msgStr=="\n" OR $msgStr=="\r") return;
     $chatServer->chatUser[$user->id][$chatid]["color"]=$msgStr;
     $chatServer->shmCt->store(&$chatServer->chatUser,CHAT_USER_KEY);
     $chatServer->addMsg("system:$user->id",$chatid,"Deine <font color=\"$msgStr\">Schriftfarbe</font> wurde geändert!");
     return;
}

function chatCommand_quit($msgStr,&$output){
     global $user,$chatServer,$chatid,$userQuit;
     $chatServer->removeUser($user->id,$chatid);
     $chatServer->addMsg("system",$chatid,fullNick($user->id)." verlässt den Chat und sagt: ".formatReady($msgStr));
     echo "Du hast den Chat verlassen!<br>";
     echo "Chatfenster wird in 3 s geschlossen!<br>";
     printJs("window.scrollBy(0, 500);");
     printJs("setTimeout('parent.close()',3000);");
     flush();
     $userQuit=true;  //dirty deeds...

}

function chatCommand_me($msgStr,&$output){
     global $user,$chatServer,$chatid;
     $chatServer->addMsg("system",$chatid,"<b>".fullNick($user->id)." ".$msgStr."</b>");

}

function chatCommand_help($msgStr,&$output){
     global $user,$chatServer,$chatid,$chatCmd;
     $str="Mögliche Kommandos:";
     foreach($chatCmd as $cmd => $text)
          $str.="<br><b>$cmd</b>$text";
     $chatServer->addMsg("system:$user->id",$chatid,$str);
}

function chatCommand_private($msgStr,&$output){
     global $user,$chatServer,$chatid;
     $recid=$chatServer->getIdFromNick($chatid,trim(substr($msgStr." ",0,strpos($msgStr," "))));
     $privMsgStr=trim(strstr($msgStr," "));
     if ($chatServer->isActiveUser($recid,$chatid)){
          $chatServer->addMsg("system:$user->id",$chatid,"Deine Botschaft an ".fullNick($recid)." wurde übermittelt.");
          $chatServer->addMsg("system:$recid",$chatid,"Eine geheime Botschaft von ".fullNick($user->id)
               .":<font color=\"".$chatServer->chatUser[$user->id][$chatid]["color"]."\"> ".$privMsgStr
               ."</font>");
     }
     else
          $chatServer->addMsg("system:$user->id",$chatid,trim(substr($msgStr." ",0,strpos($msgStr," ")))." ist in diesem Chat nicht bekannt.");
          
}

function chatCommand_kick($msgStr,&$output){
     global $user,$chatServer,$chatid;
     if ($chatServer->chatUser[$user->id][$chatid]["perm"]){
          $kickid=$chatServer->getIdFromNick($chatid,trim(substr($msgStr." ",0,strpos($msgStr," ")-1)));
          if ($chatServer->removeUser($kickid,$chatid)){
               $chatServer->addMsg("system",$chatid,fullNick($kickid)." wurde von ".fullNick($user->id)." aus dem Chat geworfen!");
          }
          else {
               $chatServer->addMsg("system:$user->id",$chatid,fullNick($kickid)." ist nicht in diesem Chat!");
          }
     }
     else
          $chatServer->addMsg("system:$user->id",$chatid,"Du darfst hier niemanden rauswerfen!");
          
}

function chatCommand_sms($msgStr,&$output){
     global $user,$chatServer,$chatid;
     $recUserName=trim(substr($msgStr." ",0,strpos($msgStr," ")));
     $smsMsgStr=trim(strstr($msgStr," "));
     if (!$recUserName OR !$smsMsgStr) return;
     if (messaging::insert_sms($recUserName,$smsMsgStr))
          $chatServer->addMsg("system:$user->id",$chatid,"Deine SMS an [".$recUserName."] wurde übermittelt.");
     else
          $chatServer->addMsg("system:$user->id",$chatid,"Fehler, deine SMS konnte nicht übermittelt werden!");
}

     
//Simpler Kommandoparser
function chatCommand(&$msg,&$output){
     global $user,$chatServer,$chatCmd,$chatid;
     $cmdStr=trim(substr($msg[1]." ",1,strpos($msg[1]," ")-1));
     $msgStr=trim(strstr($msg[1]," "));
     if (!$chatCmd[$cmdStr]) {
          $chatServer->addMsg("system:$user->id",$chatid,"Unbekanntes Kommando: $cmdStr");
          return;
     }
     $chatFunc="chatCommand_".$cmdStr;
     $chatFunc($msgStr,&$output);       //variabler Funktionsaufruf!
}


//Die Ausgabeschleife, läuft endlos wenn keine Abbruchbedingung erreicht wird
function outputLoop($chatid){
     global $user,$chatServer,$userQuit;
     
     $lastPingTime=0;
     $lastMsgTime=time()-1;
     setlocale ("LC_TIME", "de_DE");
     set_time_limit(0);       //wir sind nicht zu stoppen...
     ignore_user_abort(1);    //es sei denn wir werden brutal ausgebremst :)

     while(!connection_aborted()){

          $currentMsTime=$chatServer->getMsTime();
//Timeout vorbeugen
          if (($currentMsTime-$lastPingTime) > CHAT_TO_PREV_TIME) {
               echo"<!-- -->\n";
               flush();
               $lastPingTime=$currentMsTime;
          }
//Gibt es neue Nachrichten ?
          $newMsg=$chatServer->getMsg($chatid,$lastMsgTime);
          if ($newMsg) {
               foreach($newMsg as $msg){
                    $output="";
                    if (substr($msg[0],0,6)=="system") {
                         chatSystemMsg(&$msg,&$output);
                         if (!$output) continue;
                    }
                    elseif (substr($msg[1],0,1)=="/") {
                         if ($msg[0]==$user->id) chatCommand(&$msg,&$output);
                         if (!$output) continue;
                    }
                    if (!$output){
                         $output="<font color=\"".$chatServer->chatUser[$msg[0]][$chatid]["color"]."\">"
                              .strftime("%T",floor($msg[2]))." [".fullNick($msg[0])."] "
                              .formatReady($msg[1])."</font><br>";
                    }
                    echo $output;
                    printJs("window.scrollBy(0, 500);");
                    flush();
                    $lastPingTime=$currentMsTime;
               }
               $lastMsgTime=$msg[2];


          }

          if ($userQuit) break; //...done dirt cheap
          
//wurden wir zwischenzeitlich gekickt?
          if (!$chatServer->isActiveUser($user->id,$chatid)){
               echo "Du musstest den Chat verlassen...<br>";
               echo "<a href=\"javascript:parent.location.href='chat_login.php?chatid=$chatid';\">Hier</a> kannst du versuchen wieder einzusteigen.<br>";
               printJs("window.scrollBy(0, 500);");
               flush();
               break;
          }
//Allzulange rumidlen soll keiner
          if ((time()-$chatServer->chatUser[$user->id][$chatid]["action"]) > CHAT_IDLE_TIMEOUT) {
               echo "<b>IDLE TIMOUT</b> du wurdest aus dem Chat entfernt!<br>";
               $chatServer->addMsg("system",$chatid,$chatServer->chatUser[$user->id][$chatid]["fullname"]." (".$chatServer->chatUser[$user->id][$chatid]["nick"].") hat nichts mehr zu sagen...");
               echo "<a href=\"javascript:parent.location.href='chat_login.php?chatid=$chatid';\">Hier</a> kannst du versuchen wieder einzusteigen.<br>";
               printJs("window.scrollBy(0, 500);");
               flush();
               break;
          }

          usleep(CHAT_SLEEP_TIME);
     }
     //echo "Output beendet";

}

//main()

$chatServer=new ChatShmServer;
$userQuit=false;

?>
<html>
<head>
       <title>ChatAusgabe</title>
       <link rel="stylesheet" href="style.css" type="text/css">


</head>
<body style="background-color:#EEEEEE;background-image:url('pictures/steel1.jpg');font-size:10pt;">
<?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
     ?><table width="100%"><tr><?
     my_error("Du bist nicht in diesem Chat angemeldet!",$class="blank",$colspan=1);
     ?></tr></table></body></html><?
     page_close();
     die;
}
echo "\n<b>Hallo ".fullNick($user->id).",<br> willkommen im Raum: "
	.$chatServer->chatDetail[$chatid]["name"]."</b><br>";

register_shutdown_function("chatLogout");   //für korrektes ausloggen am Ende!
outputLoop($chatid);
$userid=$user->id; //konservieren für shutdown_function
page_close();
?>
