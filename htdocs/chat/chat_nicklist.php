<?
/**
* Ausgabe der Nicklist
* 
* 
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup	chat_modules
* @module		chat_nicklist
* @package		Chat
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
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

$chatServer = &new ChatShmServer;
$chatServer->caching = true;

?>
<html>
<head>
	   <title>Chat Nicklist</title>
	   <style type="text/css">
<!--
<?php
 include $ABSOLUTE_PATH_STUDIP."style.css";
?>
-->
</style>

</head>
<body style="background-color:#EEEEEE;background-image:url('<?=$CANONICAL_RELATIVE_PATH_STUDIP?>pictures/steel1.jpg');">
<?
//darf ich überhaupt hier sein ?
if (!$chatServer->isActiveUser($user->id,$chatid)) {
	 ?><table width="100%"><tr><?
	 my_error("Du bist nicht in diesem Chat angemeldet!",$class="blank",$colspan=1);
	 ?></tr></table></body></html><?
	 page_close();
	 die;
}
?>
<div align="center">
<table align="center" border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="2"  width="95%">
  <tr>
	 <td align="center">
		  <table align="center" border="0" cellpadding="1" cellspacing="1" width="100%">
			   <tr>
					<td class="topic" align="center"><b>Nicklist</b></td>
			   </tr>
			   <?
			   foreach ($chatServer->chatUser as $chatUserId => $chatUserDetail){
					if ($chatUserDetail[$chatid]["action"]){
						 echo "\n<tr><td><span style=\"font-size:10pt\">";
						 if ($chatUserDetail[$chatid]["perm"])  echo "<b>";
						 echo "<a href=\"javascript:parent.coming_home('{$CANONICAL_RELATIVE_PATH_STUDIP}about.php?username=".$chatUserDetail[$chatid]["nick"]."')\">".$chatUserDetail[$chatid]["fullname"]."</a><br>(".$chatUserDetail[$chatid]["nick"].")";
						 if ($chatUserDetail[$chatid]["perm"])  echo "</b>";
						 echo "</span></td></tr>";
					}
			   }
			   ?>
		  </table>
	 </td>
  </tr>
</table>
</div>
</body>
</html>
<?
page_close();
?>
