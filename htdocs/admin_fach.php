<?php
/*
admin_fach.php - Fach-Verwaltung von Stud.IP.
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
  $hash_secret = "dudeldoe";
  
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
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Verwaltung der F&auml;cher</b></td>
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
  
  
  
  
    ## Zugeordneten Bereich rauswerfen
  case "kill_bereich":
  	{
	$db->query("DELETE FROM bereich_fach WHERE bereich_id = '$bereich_id' AND fach_id ='$i_view'");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Die Zuordnung wurde aufgehoben");
	break;
  	}
  	
  ## Bereich zuordnen
  case "add_bereich":
  	{
	$db->query("INSERT INTO bereich_fach VALUES ('$bereich_id', '$i_view')");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Der Bereich wurde dem Fach zugeordnet");
	break;
  	}
  
  ## Create a new Fach
  case "create":
    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben sie eine Bezeichnug f&uuml;r das Fach ein!</b>");
      break;
    }
    
    ## Does the Fach already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("SELECT * FROM faecher WHERE name='$Name'");
    if ($db->nf()>0) {
      my_error(" <b>Das Fach \"".htmlReady(stripslashes($Name))."\" existiert bereits!");
      break;
    }

    ## Create an id
    $i_id=md5(uniqid($hash_secret));

		## Einfuegen des Faches

    $query = "INSERT INTO faecher VALUES('$i_id','$Name','$Beschreibung', '".time()."', '".time()."') ";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: $query </b>");
      break;
    }
    
		$i_view = $i_id; // nach dem Anlegen eines Faches bleiben wir in der Detail-Ansicht, um Bereiche zuordnen zu koennen...
    my_msg("<b>Das Fach \"".htmlReady(stripslashes($Name))."\" wurde angelegt.</b>");
  break;

  ## Change Faechername
  case "i_edit":

    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben Sie eine Bezeichnug f&uuml;r das Fach ein!</b>");
      break;
    }
		
    ## Update Fach information.
    $query = "UPDATE faecher SET name='$Name', beschreibung='$Beschreibung' WHERE fach_id = '$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
	my_error("<b>Datenbankoperation gescheitert: $query</b>");
      	break;
	}
    else
    	$db->query("UPDATE faecher SET chdate='".time()."' ");
    
    my_msg("<b>Die Daten des Fachs \"".htmlReady(stripslashes($Name))."\" wurden ver&auml;ndert.</b>");
  break;

  ## Delete the Fach
  case "i_kill":
    ## Institut in use?
		$db->query("SELECT * FROM bereich_fach WHERE fach_id = '$i_id'");
    if ($db->next_record()) {
      my_error("<b>Dieses Fach kann nicht gel&ouml;scht werden, da noch Bereiche existieren!</b>");
      break;
    }
    
    ## Delete that Fach
    $query = "DELETE FROM faecher WHERE fach_id='$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: </b> $query</b>");
      break;
    }
    
		unset($i_view);  // wenn wir das Fach gelöscht haben, wollen wir nicht in die Detail-Ansicht dieses Faches...
    my_msg("<b>Das Fach \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht!");
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
		$db->query("SELECT faecher.*, count(bereich_fach.fach_id) AS number FROM faecher LEFT JOIN bereich_fach USING(fach_id) WHERE faecher.fach_id = '$i_view' GROUP BY fach_id");
		$db->next_record();
		}
		
	$i_id= $db->f("fach_id");

  ?>
  <tr><td class="blank" colspan=2>
  <table border=0 bgcolor="#eeeeee" align="center" width="50%" cellspacing=2 cellpadding=2>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr><td>Fachname: </td><td><input type="text" name="Name" size=60 maxlength=254 value="<?php echo htmlReady($db->f("name")) ?>"></td></tr>
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
		<input type="hidden" name="i_id"   value="<?php $db->p("fach_id") ?>">
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
 		$db->query("SELECT * FROM bereiche LEFT JOIN bereich_fach USING (bereich_id) WHERE fach_id = '$i_id'");
 		?>
 		<table border=0 align="center" width="80%" cellspacing=2 cellpadding=2>
 		<tr><td width="100%" colspan=2><br>&nbsp;Diesem Fach sind folgende Studienbereiche zugeordnet:<br><br></th></tr>
 		<tr><th width="80%" align="center">Name</th><th width="20%" align="center">Aktion</th><tr>
		<?
 		while ($db->next_record())
 			{
 			echo"<tr><td>", htmlReady($db->f("name")), "</td><td align=\"center\"><form method=\"POST\" name=\"kill_b\" action=", $PHP_SELF, "><input type=\"submit\" name=\"kill_bereich\" value=\" Zuordnung aufheben\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"><input type=\"hidden\" name=\"bereich_id\" value=\"", $db->f("bereich_id"),"\"></td></form></tr>";
 			}
 		echo"<tr><td><form method=\"POST\" name=\"add_b\" action=", $PHP_SELF, "><select name=\"bereich_id\" size=1>";
 		$db2->query("SELECT * FROM bereiche ORDER BY name");
 		while ($db2->next_record())
 			{
 			$btmp = $db2->f("bereich_id");
 			$db->query("SELECT * FROM bereich_fach WHERE fach_id = '$i_view' AND bereich_id = '$btmp'");
	 		IF (!$db->next_record())
 			echo "<option value=".$db2->f("bereich_id").">", substr($db2->f("name"),0,80);
 			}
 		echo "</select></td><td align=\"center\"><input type=\"submit\" name=\"add_bereich\" value=\" Zuordnen\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"></td></form></tr>";
 		echo "</table><br><br>";
  		}
  	}

### Output Fach administration forms, including all updated
### information, if we come here after a submission...

if (!$i_view)
{
?>
  <tr><td class="blank" colspan=2><b><a href="<?echo $PHP_SELF?>?i_view=new">&nbsp;Neues Fach anlegen</a><b><br><br></td></tr>	
	<tr><td class="blank" colspan=2>
  <table align=center bg="#ffffff" width="80%" border=0 cellpadding=2 cellspacing=2>
  <tr valign=top align=middle>
  <th width="80%"><a href="<?echo $PHP_SELF?>?sortby=name">Name des Fachs</a></th>
  <th width="20%"><a href="<?echo $PHP_SELF?>?sortby=number">Anzahl der Bereiche</a></th>
  </tr>
<?  
  ## nachsehen, ob wir ein Sortierkriterium haben, sonst nach Name
  if (!isset($sortby) || $sortby=="") $sortby = "name";
  ## Traverse the result set
	$db->query("SELECT faecher.*, count(bereich_fach.fach_id) AS number FROM faecher LEFT JOIN bereich_fach USING(fach_id) GROUP BY fach_id ORDER BY $sortby");
  while ($db->next_record()):

//Aufbauen der &Uuml;bersichtstabelle

?>
 <!-- existing Institut -->
 <tr valign=middle align=left>
 <td><a href="<?echo $PHP_SELF?>?i_view=<?$db->p("fach_id")?>">&nbsp;<?php echo htmlReady($db->f("name")) ?></a></td>
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