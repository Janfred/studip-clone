<?php
/*
admin_institut.php - Instituts-Verwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
  $perm->check("admin");

## Set this to something, just something different...
  $hash_secret = "hgeisgczwgebt";
  
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
	if (($SessSemName["class"]=="inst") || ($SessSemName["class"]=="fak")) {
		$i_view=$SessSemName[1];
		include "links1.php";  //Linkleiste fuer geoeffnetes Institut
		}
	else
		include "links_admin.inc.php";  //Linkleiste fuer admins

	require_once ("msg.inc.php"); //Funktionen f&uuml;r Nachrichtenmeldungen
	require_once("visual.inc.php");
	require_once("config.inc.php");

?>

<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Verwaltung von Einrichtungen</b></td>
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
  
  ## Zugeordneten Fachbereich rauswerfen
  case "kill_fach":
  	{
	$db->query("DELETE FROM fach_inst WHERE fach_id = '$fach_id' AND institut_id ='$i_view'");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Die Zuordnung wurde aufgehoben");
	break;
  	}
  	
  ## Fachbereich zuordnen
  case "add_fach":
  	{
	$db->query("INSERT INTO fach_inst VALUES ('$fach_id', '$i_view')");
	if ($db->affected_rows()==0)  parse_msg("error§Datenbankoperation gescheitert."); else parse_msg("msg§Das Fach wurde der Einrichtung zugeordnet");
	break;
  	}
  
  
  ## Create a new Institut
  case "create":
    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben sie eine Bezeichnug f&uuml;r die Einrichtung ein!</b>");
      break;
    }
    
    ## Does the Institut already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("select * from Institute where Name='$Name'");
    if ($db->nf()>0) {
      my_error(" <b>Die Einrichtung \"".htmlReady(stripslashes($Name))."\" existiert bereits!");
      break;
    }

    ## Create an id
    $i_id=md5(uniqid($hash_secret));
		## Namen der Fakultaet durch Fakultaets_id erstzen
		$db2->query("SELECT * FROM Fakultaeten WHERE Name = '$Fakultaet'");
  	if ($db2->next_record()) {
			$Fakultaet = $db2->f("Fakultaets_id");
		}
		## insert the Institut...
		if ($home == "")
			$home = "http://www.studip.de";
    $query = "insert into Institute values('$i_id','$Name','$Fakultaet','$strasse','$plz', '$home', '$telefon', '$email', '$fax', '$type','".time()."', '".time()."')";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: $query </b>");
      break;
    }
    
    my_msg("<b>Die Einrichtung \"".htmlReady(stripslashes($Name))."\" wurde angelegt.</b>");
		$i_view = $i_id;
  break;

  ## Change Institut name
  case "i_edit":

    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben sie eine Bezeichnug f&uuml;r die Einrichtung ein!</b>");
      break;
    }
		## Namen der Fakultaet durch Fakultaets_id erstzen
		$db2->query("SELECT * FROM Fakultaeten WHERE Name = '$Fakultaet'");
  	if ($db2->next_record()) {
			$Fakultaet = $db2->f("Fakultaets_id");
		}
    ## Update Institut information.
    $query = "UPDATE Institute SET Name='$Name', fakultaets_id='$Fakultaet', Strasse='$strasse', Plz='$plz', url='$home', telefon='$telefon', fax='$fax', email='$email', type='$type' where Institut_id = '$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      	my_error("<b>Datenbankoperation gescheitert: $query</b>");
      	break;
     	}
    else 
    	$db->query("UPDATE Institute SET chdate='".time()."'");     
    
    my_msg("<b>Die Daten der Einrichtung \"".htmlReady(stripslashes($Name))."\" wurden ver&auml;ndert.</b>");
  break;

  ## Delete the Institut
  case "i_kill":
    ## Institut in use?
		$db->query("SELECT * FROM seminare WHERE Institut_id = '$i_id'");
    if ($db->next_record()) {
      my_error("<b>Diese Einrichtung kann nicht gel&ouml;scht werden, da noch Veranstaltungen an dieser Einrichtung existieren!</b>");
      break;
    }
    
    ## Delete that Institut.
    $query = "delete from Institute where Institut_id='$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: </b> $query</b>");
      break;
    }
    
    my_msg("<b>Die Einrichtung \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht!");
  	unset($i_view);
		break;
  
  default:
  break;
 }
}



//Anzeige der Institutsdaten; das tatseachliche Aenderungsmodul
if ($i_view)
	{
	$db->query("SELECT * FROM user_inst WHERE Institut_id ='$i_view' AND user_id = '$user->id' AND inst_perms = 'admin'");
	IF ($db->next_record() OR $perm->have_perm("root"))
{		

		
	
  	if ($i_view<>"new")
  		{
		$db->query("SELECT Institute.*, count(Seminar_id) AS number FROM Institute LEFT OUTER JOIN seminare USING (Institut_id) WHERE Institute.Institut_id ='$i_view' GROUP BY seminare.Institut_id");
		$db->next_record();
		}

	$i_id= $db->f("Institut_id");
  ?>
  <tr><td class="blank" colspan=2>
  <table border=0 bgcolor="#eeeeee" align="center" width="50%" cellspacing=2 cellpadding=2>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr><td>Name: </td><td><input type="text" name="Name" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td></tr>
	<tr><td>Fakult&auml;t</td>
		<td align=left><select name="Fakultaet">
		<?php
		$db2->query("SELECT * FROM Fakultaeten ORDER BY Name");
		while ($db2->next_record()) {
			printf ("<option %s> %s</option>", $db2->f("Fakultaets_id") == $db->f("fakultaets_id") ? "selected" : "", htmlReady($db2->f("Name")));
		}
		?>
	</select></td>
	<tr><td>Bezeichnung: </td><td><select name="type">
	<? 
	$i=0;
	foreach ($INST_TYPE as $a) {
		$i++;
		if ($i==$db->f("type"))
			echo "<option selected value=$i>".$INST_TYPE[$i]["name"]."</option>";
		else
			echo "<option value=$i>".$INST_TYPE[$i]["name"]."</option>";		
		}
	?></select></td></tr>
	<tr><td>Strasse: </td><td><input type="text" name="strasse" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Strasse")) ?>"></td></tr>
	<tr><td>Ort: </td><td><input type="text" name="plz" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Plz")) ?>"></td></tr>
	<tr><td>Telefonnummer: </td><td><input type="text" name="telefon" size=32 maxlength=254 value="<?php echo htmlReady($db->f("telefon")) ?>"></td></tr>
	<tr><td>Faxnummer: </td><td><input type="text" name="fax" size=32 maxlength=254 value="<?php echo htmlReady($db->f("fax")) ?>"></td></tr>
	<tr><td>eMailadresse: </td><td><input type="text" name="email" size=32 maxlength=254 value="<?php echo htmlReady($db->f("email")) ?>"></td></tr>
	<tr><td>Homepage: </td><td><input type="text" name="home" size=32 maxlength=254 value="<?php echo htmlReady($db->f("url")) ?>"></td></tr>
	
	
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
		<input type="hidden" name="i_id"   value="<?php $db->p("Institut_id") ?>">
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
 		$db->query("SELECT * FROM faecher LEFT  JOIN fach_inst USING (fach_id) WHERE institut_id = '$i_id'");
 		?>
 		<table border=0 align="center" width="80%" cellspacing=2 cellpadding=2>
 		<tr><td width="100%" colspan=2><br>&nbsp;Dieser Einrichtung sind folgende Fachbereiche zugeordnet:<br><br></th></tr>
 		<tr><th width="80%" align="center">Name</th><th width="20%" align="center">Aktion</th><tr>
		<?
 		while ($db->next_record())
 			{
 			echo"<tr><td>", htmlReady($db->f("name")), "</td><td align=\"center\"><form method=\"POST\" name=\"kill_f\" action=", $PHP_SELF, "><input type=\"submit\" name=\"kill_fach\" value=\" Zuordnung aufheben\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"><input type=\"hidden\" name=\"fach_id\" value=\"", $db->f("fach_id"),"\"></td></form></tr>";
 			}
 		echo"<tr><td><form method=\"POST\" name=\"add_f\" action=", $PHP_SELF, "><select name=\"fach_id\" size=1>";
 		$db2->query("SELECT * FROM faecher ORDER BY name");
 		while ($db2->next_record())
 			{
 			$ftmp = $db2->f("fach_id");
 			$db->query("SELECT * FROM fach_inst WHERE institut_id = '$i_view' AND fach_id = '$ftmp'");
	 		IF (!$db->next_record())
	 			echo "<option value=".$db2->f("fach_id").">", htmlReady(substr($db2->f("name"),0,80));
 			}
 		echo "</select></td><td align=\"center\"><input type=\"submit\" name=\"add_fach\" value=\" Zuordnen\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"></td></form></tr>";
 		echo "</table><br><br>";
  		}
  	}
}


### Output Institut administration forms, including all updated
### information, if we come here after a submission...

if (!$i_view)
{
  
  // darf ich neue Institute anlegen? (nur Root)
  
  IF ($perm->have_perm("root"))
  {?>
  <tr><td class="blank" colspan=2><b><a href="<?echo $PHP_SELF?>?i_view=new">&nbsp;Neue Einrichtung anlegen</a><b><br><br></td></tr>	
   <?}?>

	<tr><td class="blank" colspan=2>
  <table align=center bg="#ffffff" width="90%" border=0 cellpadding=2 cellspacing=2>
  <tr valign=top align=middle>
  <th width="40%"><a href="<?echo $PHP_SELF?>?sortby=Name">Name der Einrichtung</a></th>
  <th width="20%"><a href="<?echo $PHP_SELF?>?sortby=fakultaets_id">Fakult&auml;t</a></th>
  <th width="20%"><a href="<?echo $PHP_SELF?>?sortby=url">Homepage</a></th>
  <th width="10%"><a href="<?echo $PHP_SELF?>?sortby=number">Veranstaltungen</a></th>
  </tr>
<?  
  ## nachsehen, ob wir ein Sortierkriterium haben, sonst nach Name
  if (!isset($sortby) || $sortby=="") $sortby = "Name";
  ## Traverse the result set
  IF ($perm->have_perm("root"))
	$db->query("SELECT Institute.*, count(Seminar_id) AS number FROM Institute LEFT OUTER JOIN seminare USING (Institut_id) GROUP BY Institut_id ORDER BY $sortby");
  ELSE
   	{
   	$us_id = $user->id;
   	$db->query("SELECT Institute.*, count(Seminar_id) AS number FROM Institute LEFT JOIN user_inst USING(Institut_id) LEFT OUTER JOIN seminare USING (Institut_id) WHERE user_inst.user_id = '$us_id' AND user_inst.inst_perms = 'admin' GROUP BY Institut_id ORDER BY $sortby");
   	}
  while ($db->next_record()):

//Aufbauen der Uebersichtstabelle

?>
 <!-- existing Institut -->
 <tr valign=middle align=left>
  <td><a href="<?echo $PHP_SELF?>?i_view=<?$db->p("Institut_id")?>">&nbsp;<?php echo htmlReady($db->f("Name")) ?></a></td>
 <td align=left>&nbsp;
 <?php
	$inst_id=$db->f("fakultaets_id");
	$db2->query("SELECT Name FROM Fakultaeten WHERE Fakultaets_id='$inst_id'");
	$db2->next_record();
	echo htmlReady($db2->f("Name"));
 ?>
</td>
<td align="left"><?php echo FixLinks(htmlReady($db->f("url"))) ?></td>
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