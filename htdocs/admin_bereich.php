<?php
/*
admin_bereich.php - Bereichs-Verwaltung von Stud.IP.
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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


## straight from the Seminars...
  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
  $perm->check("root");

## Set this to something, just something different...
  $hash_secret = "sieliebtenundsieschlugensich";

## If is set 'cancel', we leave the adminstration form...
 if (isset($cancel)) unset ($i_view);

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

<body>


<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert

// hier muessen Seiten-Initialisierungen passieren

	include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<body>

<?php
	include "links_admin.inc.php";  //Linkleiste fuer admins
	require_once ("msg.inc.php"); //Funktionen fuer Nachrichtenmeldungen
	require_once ("visual.inc.php");
?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Verwaltung der Studienbereiche</b></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>


<?php

###
### Submit Handler
###

## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;


## Check if there was a submission


while ( is_array($HTTP_POST_VARS) 
     && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {
  

  ## Zugeordnetes Seminar rauswerfen
  case "kill_sem":
  	{
	$db->query("DELETE FROM seminar_bereich WHERE seminar_id = '$seminar_id' AND bereich_id ='$i_view'");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Die Zuordnung wurde aufgehoben");
	break;
  	}
  	
  ## Seminar zuordnen
  case "add_sem":
  	{
	$db->query("INSERT INTO seminar_bereich VALUES ('$seminar_id', '$i_view')");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Die Veranstaltung wurde dem Bereich zugeordnet");
	break;
  	}

  ## Create a new Bereich
  case "create":
    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben sie eine Bezeichnug f&uuml;r das Fach ein!</b>");
      break;
    }
    
    ## Does the Bereich already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("SELECT * FROM bereiche WHERE name='$Name'");
    if ($db->nf()>0) {
      my_error(" <b>Der Bereich \"".htmlReady(stripslashes($Name))."\" existiert bereits!");
      break;
    }

    ## Create an id
    $i_id=md5(uniqid($hash_secret));

		## Einfuegen des Bereichs

    $query = "INSERT INTO bereiche VALUES('$i_id','$Name', '$Beschreibung', '".time()."', '".time()."') ";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: $query </b>");
      break;
    }
    
		$i_view = $i_id; // nach dem Anlegen eines Bereiches bleiben wir in der Detail-Ansicht, um Veranstaltungen zuordnen zu koennen...
    my_msg("<b>Der Bereich \"".htmlReady(stripslashes($Name))."\" wurde angelegt.</b>");
  break;

  ## Change Bereichname
  case "i_edit":

    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben Sie eine Bezeichnug f&uuml;r den Bereich ein!</b>");
      break;
    }
		
    ## Update Bereich information.
    $query = "UPDATE bereiche SET name='$Name' , beschreibung='$Beschreibung' WHERE bereich_id = '$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
	my_error("<b>Datenbankoperation gescheitert: $query</b>");
      	break;
    	}
    else 
    	$db->query("UPDATE bereiche SET chdate='".time()."' ");
    
    my_msg("<b>Die Daten des Bereichs \"".htmlReady(stripslashes($Name))."\" wurden ver&auml;ndert.</b>");
  break;

  ## Delete the Bereich
  case "i_kill":
    ## Bereich in use?
		$db->query("SELECT * FROM seminar_bereich  WHERE bereich_id = '$i_id'");
    if ($db->next_record()) {
      my_error("<b>Dieser Bereich kann nicht gel&ouml;scht werden, da er noch mit Veranstaltungen verkn&uuml;pft ist!</b>");
      break;
    }
    
    ## Delete that Bereich
    $query = "DELETE FROM bereiche WHERE bereich_id='$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: </b> $query</b>");
      break;
    }
    
		unset($i_view);  // wenn wir den Bereich gelöscht haben, wollen wir nicht in die Detail-Ansicht dieses Bereiches...
    my_msg("<b>Der Bereich \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht!");
  break;
  
  default:
  break;
 }
}



//Anzeige der Faecherdaten; das tatseachliche Aenderungsmodul

if ($i_view)
	{
 		if ($i_view<>"new")
	  	{
		$db->query("SELECT bereiche.*, count(seminar_bereich.bereich_id) AS number FROM bereiche LEFT JOIN seminar_bereich USING(bereich_id) WHERE bereiche.bereich_id = '$i_view' GROUP BY bereich_id");
		$db->next_record();
		}
		
		$i_id= $db->f("bereich_id");
		
  ?>
  <tr><td class="blank" colspan=2>
  <table border=0 bgcolor="#eeeeee" align="center" width="50%" cellspacing=2 cellpadding=2>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr><td>Bereichname: </td><td><input type="text" name="Name" size=60 maxlength=254 value="<?php echo htmlReady($db->f("name")) ?>"></td></tr>
	<tr><td>Beschreibung: </td><td><textarea cols=50 ROWS=4 name="Beschreibung" value="<?php $db->p("beschreibung") ?>"><?php echo htmlReady($db->f("beschreibung")) ?></textarea></td></tr>
	<tr><td colspan=2 align="center">
	
	<? 
	if ($i_view<>"new")
		{
		if ($db->f("number") < 1):
			?>
			<input type="submit" name="i_kill" value=" L&ouml;schen ">
			<?
		endif;
		?>
		<input type="submit" name="i_edit" value=" Ver&auml;ndern ">
		<input type="hidden" name="i_id"   value="<?php $db->p("bereich_id") ?>">
		<?
		}
	else
		{
		echo "<input type=\"submit\" name=\"create\" value=\"Anlegen\">";
		}
	?>
	<input type="submit" name="cancel" value=" Abbrechen ">
	<input type="hidden" name="i_view" value="<? echo $i_view; ?>">
	</form></td></tr></table>
	<br><br>
 
  	<?
  	
  	if ($i_view<>"new")
		{
 		$db->query("SELECT * FROM seminare LEFT  JOIN seminar_bereich USING (seminar_id) WHERE bereich_id = '$i_id'");
 		?>
 		<table border=0 align="center" width="80%" cellspacing=2 cellpadding=2>
 		<tr><td width="100%" colspan=2><br>&nbsp;Diesem Bereich sind folgende Veranstaltungen zugeordnet:<br><br></th></tr>
 		<tr><th width="80%" align="center">Name</th><th width="20%" align="center">Aktion</th><tr>
		<?
 		while ($db->next_record())
 			{
 			echo"<tr><td>", htmlReady($db->f("Name")), "</td><td align=\"center\"><form method=\"POST\" name=\"kill_s\" action=", $PHP_SELF, "><input type=\"submit\" name=\"kill_sem\" value=\" Zuordnung aufheben\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"><input type=\"hidden\" name=\"seminar_id\" value=\"", $db->f("seminar_id"),"\"></td></form></tr>";
 			}
 		echo"<tr><td><form method=\"POST\" name=\"add_s\" action=", $PHP_SELF, "><select name=\"seminar_id\" size=1>";
 		$db2->query("SELECT Name, Seminar_id FROM seminare ORDER BY Name");
 		while ($db2->next_record())
 			{
 			$stmp = $db2->f("Seminar_id");
 			$db->query("SELECT * FROM seminar_bereich WHERE bereich_id = '$i_view' AND seminar_id = '$stmp'");
	 		IF (!$db->next_record())
	 			echo "<option value=".$db2->f("Seminar_id").">", substr($db2->f("Name"),0,80);
 			}
 		echo "</select></td><td align=\"center\"><input type=\"submit\" name=\"add_sem\" value=\" Zuordnen\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"></td></form></tr>";
 		echo "</table><br><br>";
  		}
  	}

### Output Bereich administration forms, including all updated
### information, if we come here after a submission...

if (!$i_view)
{
?>
  <tr><td class="blank" colspan=2><b><a href="<?echo $PHP_SELF?>?i_view=new">&nbsp;Neuen Bereich anlegen</a><b><br><br></td></tr>	
	<tr><td class="blank" colspan=2>
  <table align=center bg="#ffffff" width="80%" border=0 cellpadding=2 cellspacing=2>
  <tr valign=top align=middle>
  <th width="80%"><a href="<?echo $PHP_SELF?>?sortby=name">Name des Bereichs</a></th>
  <th width="20%"><a href="<?echo $PHP_SELF?>?sortby=number">Anzahl der Veranstaltungen</a></th>
  </tr>
<?  
  ## nachsehen, ob wir ein Sortierkriterium haben, sonst nach Name
  if (!isset($sortby) || $sortby=="") $sortby = "name";
  ## Traverse the result set
	$db->query("SELECT bereiche.*, count(seminar_bereich.bereich_id) AS number FROM bereiche LEFT JOIN seminar_bereich USING(bereich_id) GROUP BY bereich_id ORDER BY $sortby");
  while ($db->next_record()):

//Aufbauen der &Uuml;bersichtstabelle

?>
 <!-- existing Bereich -->
 <tr valign=middle align=left>
 <td><a href="<?echo $PHP_SELF?>?i_view=<?$db->p("bereich_id")?>">&nbsp;<?php echo htmlReady($db->f("name")) ?></a></td>
 <td align=center>&nbsp;<?php $db->p("number") ?></td>
</tr>
 <?php
  endwhile;
?>
</table><br><br>
</td></tr>
<?php
}
echo"</table>";
page_close();
?>
</body>
</html>
<!-- $Id$ -->