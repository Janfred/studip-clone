<?
/*
This file is part of StudIP -
chat_nicklist.php
Zeigt die Nicklist
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

$chatServer=new ChatShmServer();

?>
<html>
<head>
       <title>Chat Nicklist</title>
       <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body style="background-color:#EEEEEE;background-image:url('pictures/steel1.jpg');">
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
                         echo "<a href=\"javascript:parent.coming_home('about.php?username=".$chatUserDetail[$chatid]["nick"]."')\">".$chatUserDetail[$chatid]["fullname"]."</a><br>(".$chatUserDetail[$chatid]["nick"].")";
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
