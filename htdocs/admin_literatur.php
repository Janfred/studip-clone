<?php
/*
admin_literatur.php - Literaturverwaltung von Stud.IP
Copyright (C) 2000 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
	 $perm->check("autor");
	?>
<html>
<head>
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
	<body bgcolor=white>
</head>


<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php
	include "header.php";   //hier wird der "Kopf" nachgeladen
	include "links_admin.inc.php";  //Linkleiste fuer admins

	require_once "functions.php";
	require_once "msg.inc.php";
	require_once "visual.inc.php";

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	 if ($range_id && !$perm->have_perm("root"))
	     {
	     //Sicherheitscheck
	      $range_perm=get_perm($range_id);
	     if ($SessSemName["class"]=="sem" && ($range_perm!="admin" && $range_perm!="dozent" && $range_perm!="tutor")) 
		die;
	     elseif ($SessSemName["class"]=="inst" && ($range_perm!="admin")) 
	     	die;
	     elseif ($SessSemName["class"]=="fak" && ($range_perm!="admin")) 
	     	die;
	     elseif ((!$SessSemName["class"]) && $range_id!=$user->id)  
	     	die;
	     }

	//Sicherheitscheck ob was zum Bearbeiten gewaehlt ist.
	if (!$range_id) {
		echo "</tr></td></table>";
		die;
	}
	  	
 	//maximale spaltenzahl berechnen
	if ($auth->auth["jscript"]) $max_col = round($auth->auth["xres"] / 12 );
		else $max_col =  64 ; //default für 640x480


	 if($aendern && $range_id)
		 {
		 if ($new_entry)
			 {
			 $db->query("INSERT INTO literatur VALUES ('$lit_id','$range_id','$user->id','$literatur','$links', '".time()."', '".time()."')");
			 if ($db->affected_rows()) $result="msg§Listen ge&auml;ndert";
			 }

		 else
			 {
			 $db->query("UPDATE literatur SET user_id='$user->id', literatur='$literatur', links='$links' WHERE literatur_id='$lit_id'");
			 if ($db->affected_rows()) {
			 	$result="msg§Literatur und Links ge&auml;ndert";
			 	$db->query("UPDATE literatur SET chdate='".time()."' WHERE literatur_id='$lit_id'");
			 	}
			 }

		 }

	$db->query ("SELECT Name, status FROM seminare WHERE Seminar_id = '$range_id'");
	if (!$db->next_record()) {
		$db->query ("SELECT Name, type FROM Institute WHERE Institut_id = '$range_id'");
			if ($db->next_record()) {
				$tmp_typ = $INST_TYPE[$db->f("type")]["name"];
		}
	} else
		if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME) 	
			$tmp_typ = "Veranstaltung"; 
		else
			$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];

	$tmp_name=$db->f("Name");

	 ?>
       	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="topic" colspan=2>&nbsp;<b>
	<?
	echo $tmp_typ, ": ", htmlReady(substr($tmp_name, 0, 60));
		if (strlen($tmp_name) > 60)
			echo "... ";
		echo " -  Literatur und Links";
	?></b></td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<?
	
       if (isset($result)) 
	     	{
	     	$result=rawurldecode($result);
		parse_msg($result);
		$result="";
		}
	
	?>
	<tr>
		<td class="blank" colspan=2>
			<blockquote>Sie k&ouml;nnen hier die Literaturliste und Links bearbeiten.
			Im Feld Links k&ouml;nnen Sie Links normal eingeben, diese werden anschliessend automatisch als Hyperlinks dargestellt.
			</blockquote>
	<? 
	  echo"<form action=\"$PHP_SELF\" method=\"POST\">";
	  ?><table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
	    <tr valign="top" align="center">
	    <th width="100%" colspan=2 class="steelgraudunkel">Literaturliste</th>
	    </tr><?
	  $db->query("SELECT * FROM literatur WHERE range_id='$range_id'");
	  if (!$db->num_rows())
		{
		$new_entry=TRUE;
		$literatur="Keine Einträge";
		$links="Keine Einträge";
		$hash_secret = "blafasel25";
		$lit_id=md5(uniqid($hash_secret));
		$autor=get_fullname();
		}
	     else
		 {
		 $db->next_record();
		 $db2->query("SELECT username FROM auth_user_md5 WHERE user_id = '".$db->f("user_id")."'");
		 $db2->next_record();
		 $new_entry=FALSE;
		 $literatur=htmlReady($db->f("literatur"));
		 $links=htmlReady($db->f("links"));
		 $lit_id=$db->f("literatur_id");
		 $autor=get_fullname($db->f("user_id"));
		 }
	  echo"<tr><td width=\"100%\" align=\"center\" colspan=2>";
	  echo"<textarea  name=\"literatur\"  style=\"width: 100%\" cols=$max_col rows=10 wrap=virtual >$literatur</textarea></td></tr>";
	  echo"<tr><td width=\"50%\" align=\"left\" class=\"steel1\">&nbsp; &Auml;nderungen <input type=\"IMAGE\" align=\"absmiddle\" name=\"send_button\" value=\"Änderungen vornehmen\" border=0 src=\"pictures/buttons/uebernehmen-button.gif\"></td>";
	  echo"<td width=\"50%\" align=\"center\" class=\"steel1\"><font size=-1>Eingestellt von: <b><a href=\"about.php?username=".$db2->f("username")."\">$autor</a></b>";
	  if ($db->f("chdate"))
	  	echo ", letzte &Auml;nderung am ".date("d.m.y", $db->f("chdate"))." um ",date("H:i", $db->f("chdate"));
	  echo "</font></td>";
	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
	  echo"<input type=\"HIDDEN\" name=\"lit_id\" value=\"$lit_id\">";
	  echo"<input type=\"HIDDEN\" name=\"new_entry\" value=\"$new_entry\">";
	  echo"<input type=\"HIDDEN\" name=\"ebene\" value=\"$ebene\">";
	  echo"<input type=\"HIDDEN\" name=\"aendern\" value=\"TRUE\">";	  
	  echo"</td></tr>";
	  echo"</table><br><br>";

    ?><table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
	    <tr valign="top" align="center">
	    <th width="100%" colspan=2 class="steelgraudunkel">Links</th>
	    </tr><?

	  echo"<form action=\"$PHP_SELF\" method=\"POST\"><tr><td width=\"100%\" align=\"center\" colspan=\"2\">";
	  echo"<textarea  name=\"links\"  style=\"width: 100%\" cols=$max_col rows=10 wrap=virtual >$links</textarea></td></tr>";
 	  echo"<tr><td width=\"50%\" align=\"left\" class=\"steel1\">&nbsp; &Auml;nderungen <input type=\"IMAGE\" align=\"absmiddle\" name=\"send_button\" value=\"Änderungen vornehmen\" border=0 src=\"pictures/buttons/uebernehmen-button.gif\"></td>";
	  echo"<td width=\"50%\" align=\"center\" class=\"steel1\"><font size=-1>Eingestellt von: <b><a href=\"about.php?username=".$db2->f("username")."\">$autor</a></b>";
		  if ($db->f("chdate"))
	  	echo ", letzte &Auml;nderung am ".date("d.m.y", $db->f("chdate"))." um ",date("H:i", $db->f("chdate"));
	  echo "</font></td>";
	  echo"</td></tr>";
	  echo"</table><br></form></td></tr></table>";
	  
	page_close();
 ?>
</body>
</html>