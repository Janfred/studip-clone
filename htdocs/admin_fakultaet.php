<?php
/*
admin_fakultaet.php - Fakultaeten-Verwaltung von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

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

  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
  $perm->check("root");

## Set this to something, just something different...
  $hash_secret = "hjeroxghsdf";


?>
<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>

<body bgcolor="#ffffff">

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert

// hier muessen Seiten-Initialisierungen passieren

	include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<body>

<?php
require_once("visual.inc.php");
$cssSw=new cssClassSwitcher;

include "links_admin.inc.php";  //Linkleiste fuer admins
?>

<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Verwaltung der Fakult&auml;ten</b></td>
</tr>
<tr>
	<td class="blank" colspan=2>
		<blockquote><br>	
		Auf dieser Seite k&ouml;nnen Sie die Fakult&auml;ten, die im System verwendet werden, verwalten. Sie m&uuml;ssen mindestens eine Fakult&auml;t angelegt haben, um Institute anlegen zu k&ouml;nnen.<br>
		<b>Achtung:</b> Das L&ouml;schen einer Fakult&auml;t ist nur m&ouml;glich, wenn keine Institute in dieser Fakult&auml;t existieren.
		</blockquote>
	</td>
</tr>

<?php

###
### Submit Handler
###

## Get a database connection
$db = new DB_Seminar;

## Check if there was a submission
while ( is_array($HTTP_POST_VARS) 
     && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {

  ## Create a new Fakultaet
  case "create":
    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("Bitte geben Sie eine Bezeichnung f&uuml;r die Fakult&auml;t</B>!");
      break;
    }
    
    ## Does the Fakultaet already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("select * from Fakultaeten where Name='$Name'");
    if ($db->nf()>0) {
      my_error("Die Fakult&auml;t <B>".htmlReady(stripslashes($Name))."</B> existiert bereits!");
      break;
    }

    ## Create an id and insert the fakultaet...
    $f_id=md5(uniqid($hash_secret));
    $query = "insert into Fakultaeten values('$f_id','$Name', '".time()."', '".time()."')";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert:</b> $query");
      break;
    }
    
    my_msg("Die Fakult&auml;t \"".htmlReady(stripslashes($Name))."\" wurde erzeugt.");
  break;

  ## Change Fakultaet name
  case "f_edit":

    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("Bitte geben Sie eine Bezeichnung f&uuml;r die Fakult&auml;t ein!");
      break;
    }
    
    ## Update Fakultaet information.
    $query = "update Fakultaeten set Name='$Name' where Fakultaets_id = '$f_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      	my_msg("<b>keine Änderung an der Fakultät $Name vorgenommen.");
      	break;
    	}
    else
    	$db->query("UPDATE Fakultaeten SET chdate='".time()."' ");
    
    my_msg("Die Fakult&auml;t \"".htmlReady(stripslashes($Name))."\" wurde ver&auml;ndert.<BR>");
  break;

  ## Delete Fakultaet
  case "f_kill":
    ## Fakultaet in use?
		$db->query("SELECT * FROM Institute WHERE fakultaets_id = '$f_id'");
    if ($db->next_record()) {
      my_error("Sie k&ouml;nnen diese Fakult&auml;t nicht l&ouml;schen, da noch Institute dieser Fakult&auml;t existieren!");
      break;
    }
    
    ## Delete that Fakultaet.
    $query = "delete from Fakultaeten where Fakultaets_id='$f_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert:</b> $query");
      break;
    }
    
    my_msg("Die Fakult&auml;t \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht.<BR>");
  break;
  
  default:
  break;
 }
}

### Output Fakultaeten administration forms, including all updated
### information, if we come here after a submission...

?>
<tr><td class="blank" colspan=2>
<table border=0  align="center" cellspacing=0 cellpadding=2 width="80%">
 <tr valign=top align=middle>
  <th width="55%"><a href="admin_fakultaet.php?sortby=Name">Bezeichnung der Fakult&auml;t</a></th>
  <th width="15%"><a href="admin_fakultaet.php?sortby=number">Anzahl der Institute</a></th>
  <th width="30%">Aktion</th>
 </tr>

 <form name="add" method="post" action="<?php $sess->pself_url() ?>">
 <tr valign=middle align=left>
  <td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><input type="test" name="Name" size=50 maxlength=254 value="Bitte geben Sie hier einen Namen ein!"></td>
  <td class="<? echo $cssSw->getClass() ?>">&nbsp;</td>
  <td class="<? echo $cssSw->getClass() ?>" align=center>
		<input type="submit" name="create" value="Neu anlegen">
	</td>
 </tr>
 </form>

<?  
  ## nachsehen, ob wir ein Sortierkriterium haben, sonst nach Name
  if (!isset($sortby) || $sortby=="") $sortby = "Name";
  ## Traverse the result set
	$db->query("SELECT Fakultaeten.*, count(Institut_id) AS number FROM Fakultaeten LEFT OUTER JOIN Institute USING (Fakultaets_id) GROUP BY Fakultaets_id ORDER BY $sortby");
  while ($db->next_record()):

?>
 <!-- existing Fakultaet -->
 <form name="edit" method="post" action="<?php $sess->pself_url() ?>">
 <tr valign=middle align=left>
  <td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><input type="text" name="Name" size=50 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td>
  <td class="<? echo $cssSw->getClass() ?>" align=center><?php $db->p("number") ?></td>

  <td class="<? echo $cssSw->getClass()?>" align=center>
   <input type="hidden" name="f_id"   value="<?php $db->p("Fakultaets_id") ?>">
<?php if ($db->f("number") < 1):
?>
   <input type="submit" name="f_kill" value="L&ouml;schen">
<?
	endif;
?>
   <input type="submit" name="f_edit" value="Ver&auml;ndern">
  </td>
 </tr>
 </form>
<?php
  endwhile;
?>
</table>
<?php
  page_close();
?>
</td></tr>
	<tr>
		<td class="blank" colspan=2>&nbsp; 
		</td>
	</tr>
</table>
</body>
</html>
<!-- $Id$ -->
