<?
/**
* Input Window for the Chat
* 
* This script prints a HTML input form and handles color changing and quitting the chat with some JavaScript
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup		chat_modules
* @module		chat_input
* @package		Chat
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_nicklist.php
// Shows the nicklist
// Copyright (c) 2002 André Noack <andre.noack@gmx.net>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	page_close();
	die;
}
require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatShmServer.class.php";
//Studip includes
require_once $ABSOLUTE_PATH_STUDIP."msg.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";

$chatServer = &new ChatShmServer;
$chatServer->caching = true;

?>
<html>
<head>
	   <title>ChatInput</title>
	   <style type="text/css">
<!--
<?php
 include $ABSOLUTE_PATH_STUDIP."style.css";
?>
-->
</style>

<script type="text/javascript">
/**
* JavaScript 
*/
   function doQuit(){
		document.inputform.chatInput.value="/quit bye";
		document.inputform.submit();
	}

/**
* JavaScript 
*/
	function doColorChange(){
		for(i=0;i<document.inputform.chatColor.length;++i)
			if(document.inputform.chatColor.options[i].selected == true){
			document.inputform.chatInput.value="/color " +
			document.inputform.chatColor.options[i].value;
			document.inputform.submit();
			}
	}
</script>
<script type="text/javascript">
/**
* JavaScript 
*/
	function printhelp(){
		document.inputform.chatInput.value="/help";
		document.inputform.submit();
	}
 </script>

</head>
<body style="background-color:white;background-image:url('<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/steel1.jpg');">
<?
//darf ich überhaupt hier sein ?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
	 ?><table width="100%"><tr><?
	 my_error("Du bist nicht in diesem Chat
	 angemeldet!",$class="blank",$colspan=1,false);
	 ?></tr></table></body></html><?
	 page_close();
	 die;
}


//neue chatnachricht einfügen
if ($chatInput) {
	if ($chatServer->isActiveUser($user->id,$chatid)){
		  $chatInput=stripslashes($chatInput);
		  $chatServer->addMsg($user->id,$chatid,$chatInput);
		  //evtl Farbe umstellen
		  $cmdStr=trim(substr($chatInput." ",1,strpos($chatInput," ")-1));
		  $msgStr=trim(strstr($chatInput," "));
		  if ($cmdStr=="color" AND $msgStr!="" AND $msgStr!="\n" AND $msgStr!="\r")
			   $chatServer->chatUser[$user->id][$chatid]["color"]=$msgStr;
		  }

}

?>
<form method="post" action="<?=$PHP_SELF?>" name="inputform">
<input type="hidden" name="chatid" value="<?=$chatid?>">
<div align="center">
	   <table width="98%" border="0" bgcolor="white" cellspacing="0" cellpadding="0" align="center">
		  <tr>
				  <td width="80%" align="left" class="topic" ><b>&nbsp;Chat -
		  <?=$chatServer->chatDetail[$chatid]["name"]?></b></td><td width="20%" align="right" class="topic" ><a href="javascript:printhelp();"><img src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/hilfe.gif" border=0 align="texttop" <?=tooltip("zur Hilfe")?>></a>&nbsp; <a href="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>show_smiley.php" target=new><img src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/smile/smile.gif" border=0 align="absmiddle" <?=tooltip("Alle verfügbaren Smileys anzeigen")?>></a>&nbsp; </td>
		   </tr>
		   <tr>
			   <td align="center" colspan=2 valign="center">

					<table width="100%" border="0" cellspacing="3">
					  <tr valign="middle">
							  <td align="left" valign="center">Message:</td>
							  <td width="60%" valign="center">
			<div align="center" valign="center">
						  <input type="text" name="chatInput" size=18
			  style="width: 100%" >
			  </div>
							  </td>
							  <td align="left" valign="center">
				  <select name="chatColor" onChange="doColorChange();">
				  <?
				  foreach($chatColors as $c){
					print "<option style=\"color:$c;\" value=\"$c\" ";
						 if ($chatServer->chatUser[$user->id][$chatid]["color"]==$c){
						 $selected=true;
						 print " selected ";
						 }
				print ">$c</option>\n";
				}
					if (!$selected) print "<option style=\"color:".$chatServer->chatUser[$user->id][$chatid]["color"].";\" value=\"".$chatServer->chatUser[$user->id][$chatid]["color"]."\" selected>user</option>\n";
				?>	
				  </select>
				  </td>
				  <td align="center" valign="center">
					<input type="IMAGE" name="Submit"
				src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/buttons/absenden-button.gif" <?=tooltip("Nachricht senden")?> border="0" value="senden">
						</td>
				<td align="right" valign="center">
							<a href="javascript:doQuit();"><img <?=tooltip("Chat verlassen")?> src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/buttons/abbrechen-button.gif" border="0"></a>
						</td>
					  </tr>
					</table>
				  </td>
				</tr>
			  </table>
</div>
	</form>
<script>
document.inputform.chatInput.focus();
</script>
</body>
</html>
<?
page_close();
?>

