<?php
/*
admin_institut.php - Einrichtungs-Verwaltung von Stud.IP.
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

  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
  $perm->check("admin");

	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

## Set this to something, just something different...
  $hash_secret = "hgeisgczwgebt";
  
 ## If is set 'cancel', we leave the adminstration form...
 if (isset($cancel)) unset ($i_view);

require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); //Funktionen f&uuml;r Nachrichtenmeldungen
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/forum.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");

if ($RESOURCES_ENABLE) {
	require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesAssign.class.php");
}
	

###
### Submit Handler
###

## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$cssSw = new cssClassSwitcher;

## Check if there was a submission
while ( is_array($HTTP_POST_VARS) 
     && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {
  
  ## Zugeordneten Fachbereich rauswerfen
  case "kill_fach_x":
  	{
	$db->query("DELETE FROM fach_inst WHERE fach_id = '$fach_id' AND institut_id ='$i_view'");
	if ($db->affected_rows()==0)  $msg="error§Datenbankoperation gescheitert."; else $msg="msg§Die Zuordnung wurde aufgehoben";
	break;
  	}
  	
  ## Fachbereich zuordnen
  case "add_fach_x":
  	{
	$db->query("INSERT INTO fach_inst VALUES ('$fach_id', '$i_view')");
	if ($db->affected_rows()==0)  $msg="error§Datenbankoperation gescheitert."; else $msg="msg§Das Fach wurde der Einrichtung zugeordnet";
	break;
  	}
  
  
  ## Create a new Institut
  case "create_x":
    ## Do we have all necessary data?
    if (empty($Name)) {
      $msg="error§<b>Bitte geben sie eine Bezeichnung f&uuml;r die Einrichtung ein!</b>";
      $i_view="new";
      break;
    }
    
    ## Does the Institut already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("select * from Institute where Name='$Name'");
    if ($db->nf()>0) {
      $msg="error§<b>Die Einrichtung \"".htmlReady(stripslashes($Name))."\" existiert bereits!";
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
      $msg="error§<b>Datenbankoperation gescheitert: $query </b>";
      break;
    }
       ## Create default folder and discussion
    CreateTopic('Allgemeine Diskussionen', " ", 'Hier ist Raum für allgemeine Diskussionen', 0, 0, $i_id, 0);
    $db->query("INSERT INTO folder SET folder_id='".md5(uniqid(rand()))."', range_id='".$i_id."',name='Allgemeiner Dateiordner', description='Ablage für allgemeine Ordner und Dokumente der Einrichtung', mkdate='".time()."', chdate='".time()."'");
 
    $msg="msg§<b>Die Einrichtung \"".htmlReady(stripslashes($Name))."\" wurde angelegt.</b>";
   
   $i_view = $i_id;

   //This will select the new institute later for navisgation (=>links_admin.inc.php)
   $admin_inst_id =$i_id; 
  break;

  ## Change Institut name
  case "i_edit_x":

    ## Do we have all necessary data?
    if (empty($Name)) {
      $msg="error§<b>Bitte geben sie eine Bezeichnug f&uuml;r die Einrichtung ein!</b>";
      break;
    }

	## Namen der Fakultaet durch Fakultaets_id erstzen
	$db2->query("SELECT * FROM Fakultaeten WHERE Name = '$Fakultaet'");
  	if ($db2->next_record()) {
		$Fakultaet = $db2->f("Fakultaets_id");
	}
    ## Update Institut information.
    $query = "UPDATE Institute SET Name='$Name', fakultaets_id='$Fakultaet', Strasse='$strasse', Plz='$plz', url='$home', telefon='$telefon', fax='$fax', email='$email', type='$type' ,chdate=".time()." where Institut_id = '$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      	$msg="error§<b>Datenbankoperation gescheitert: $query</b>";
      	break;
     	}
    
    
    $msg="msg§<b>Die Daten der Einrichtung \"".htmlReady(stripslashes($Name))."\" wurden ver&auml;ndert.</b>";
  break;

  ## Delete the Institut
  case "i_kill_x":

    ## Institut in use?
	$db->query("SELECT * FROM seminare WHERE Institut_id = '$i_id'");
    if ($db->next_record()) {
      $msg="error§<b>Diese Einrichtung kann nicht gel&ouml;scht werden, da noch Veranstaltungen an dieser Einrichtung existieren!</b>";
      break;
    }
    
    ## Delete that Institut.
    $query = "delete from Institute where Institut_id='$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      $msg="error§<b>Datenbankoperation gescheitert: </b> $query</b>";
      break;
    }
    
	// delete users in user_inst
	$query = "DELETE FROM user_inst WHERE Institut_id='$i_id'";
	$db->query($query);
	if (($db_ar = $db->affected_rows()) > 0) {
		$msg.="msg§$db_ar Mitarbeiter gel&ouml;scht.§";
	}
	
	// delete facher in fach_inst
	$query = "DELETE FROM fach_inst WHERE Institut_id='$i_id'";
	$db->query($query);
	if (($db_ar = $db->affected_rows()) > 0) {
		$msg.="msg§$db_ar Fachzuordnungen gel&ouml;scht.§";
	}
	
	// delete literatur in literatur
	$query = "DELETE FROM literatur WHERE range_id='$i_id'";
	$db->query($query);
	if (($db_ar = $db->affected_rows()) > 0) {
		$msg.="msg§Literatur / Links gel&ouml;scht.§";
	}
	
	//deleting news is done by the garbage collector in local.inc
	
	//updating range_tree
	$query = "UPDATE range_tree SET name='$Name (in Stud.IP gelöscht)',studip_object='',studip_object_id='' WHERE studip_object_id='$i_id'";
	$db->query($query);
	if (($db_ar = $db->affected_rows()) > 0) {
		$msg.="msg§$db_ar Bereiche im Bereichsbaum angepasst§";
	}
	// Statusgruppen entfernen
	 if ($db_ar = DeleteAllStatusgruppen($i_id) > 0) {
		$msg .= "msg§$db_ar Funktionen / Gruppen gel&ouml;scht.§";
	}
	//kill all the ressources that are assigned to the Veranstaltung (and all the linked or subordinated stuff!)
	if ($RESOURCES_ENABLE) {
		$killAssign = new ResourcesAssign($u_id);
		$killAssign->delete();
	}
    
    ## delete folders and discussions
    $query = "DELETE from px_topics where Seminar_id='$i_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $msg.="msg§$db_ar Postings aus dem Forum der Einrichtung gel&ouml;scht.§";
    }
    $db_ar = recursiv_folder_delete($i_id);
    if ($db_ar > 0)
     $msg.="msg§$db_ar Dokumente gel&ouml;scht.§";


    $msg.="msg§Die Einrichtung \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht!§";
  	$i_view="delete";
	//We deleted that intitute, so we have to unset the selection 
	closeObject();
	break;
	
  default:
  break;
 }
}

//workaround
if ($i_view == "new")
	closeObject();
	
//Output starts here

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   //hier wird der "Kopf" nachgeladen 
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins


?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;<b>
	<?
	if ($i_view == "new") {
		echo "Anlegen einer neuen Einrichtung";
	} elseif ($i_view == "delete"){
		echo "Einrichtung gel&ouml;scht";
	} else {
		print getHeaderLine($i_view)." -  Grunddaten";
	}
	?></b></td>
</tr>
<?
if (isset($msg)) {
?>
<tr> 
	<td class="blank" colspan=2><br />
		<?parse_msg($msg);?>
	</td>
</tr>
<? } ?>
<tr>
	<td class="blank" colspan=2>
		&nbsp;
	</td>
</tr>

<?
if ($i_view=="delete") {
	echo "<tr><td class=\"blank\" colspan=\"2\"><table width=\"70%\" align=\"center\" class=\"steelgraulight\" >";
	echo "<tr><td><br>Die ausgewählte Einrichtung wurde gel&ouml;scht.<br> Bitte wählen Sie über das Schlüsselsymbol ";
	echo "<a href=\"admin_institut.php?list=TRUE\"><img " . tooltip("Neue Auswahl") . " align=\"absmiddle\" src=\"pictures/admin.gif\" border=\"0\"></a>";
	echo " eine andere Einrichtung aus.<br><br></td></tr></table><br><br></td></tr></table></html>";
	page_close();
	die;
}
	
	
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
  <table border=0 align="center" width="50%" cellspacing=0 cellpadding=2>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Name: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="Name" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Fakult&auml;t</td>
		<td class="<? echo $cssSw->getClass() ?>" align=left><select name="Fakultaet">
		<?php
		$db2->query("SELECT * FROM Fakultaeten ORDER BY Name");
		while ($db2->next_record()) {
			printf ("<option %s> %s</option>", $db2->f("Fakultaets_id") == $db->f("fakultaets_id") ? "selected" : "", htmlReady($db2->f("Name")));
		}
		?>
	</select></td>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Bezeichnung: </td><td class="<? echo $cssSw->getClass() ?>" ><select name="type">
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
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Strasse: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="strasse" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Strasse")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Ort: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="plz" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Plz")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Telefonnummer: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="telefon" size=32 maxlength=254 value="<?php echo htmlReady($db->f("telefon")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Faxnummer: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="fax" size=32 maxlength=254 value="<?php echo htmlReady($db->f("fax")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Emailadresse: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="email" size=32 maxlength=254 value="<?php echo htmlReady($db->f("email")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" >Homepage: </td><td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="home" size=32 maxlength=254 value="<?php echo htmlReady($db->f("url")) ?>"></td></tr>
	
	
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center">
	
	<? 
	if ($i_view<>"new")
		{
		?>
		<input type="hidden" name="i_id"   value="<?php $db->p("Institut_id") ?>">
		<input type="IMAGE" name="i_edit" src="./pictures/buttons/uebernehmen-button.gif" border=0 value=" Ver&auml;ndern ">
		<?
		if ($db->f("number") < 1):
			?>
			&nbsp;<input type="IMAGE" name="i_kill" src="./pictures/buttons/loeschen-button.gif" border=0 value=" L&ouml;schen ">
			<?
		endif;
		}
	else
		{
		echo "<input type=\"IMAGE\" name=\"create\" src=\"./pictures/buttons/anlegen-button.gif\" border=0 value=\"Anlegen\">";
		}
	?>
	<input type="hidden" name="i_view" value="<? printf ("%s", ($i_view=="new") ? "create" : $i_view);  ?>">
	</td></tr></table>
	</form>
	<br>
 
  	<?
  	
  	if ($i_view<>"new")
		{
 		$db->query("SELECT * FROM faecher LEFT  JOIN fach_inst USING (fach_id) WHERE institut_id = '$i_id'");
		$cssSw->resetClass();
 		?>
 		<table border=0 align="center" width="80%" cellspacing=0 cellpadding=2>
 		<tr><td width="100%" colspan=2><br>&nbsp;Dieser Einrichtung sind folgende Studienf&auml;cher zugeordnet:<br><br></th></tr>
 		<tr><th width="80%" align="center">Name</th><th width="20%" align="center">Aktion</th><tr>
		<?
 		while ($db->next_record()) {
 			$cssSw->switchClass();
 			echo"<tr><td class=\"".$cssSw->getClass()."\">", htmlReady($db->f("name")), "</td><td class=\"".$cssSw->getClass()."\" align=\"center\"><form method=\"POST\" name=\"kill_f\" action=", $PHP_SELF, "><input type=\"IMAGE\" name=\"kill_fach\" src=\"./pictures/buttons/entfernen-button.gif\" border=0 value=\" Zuordnung aufheben\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"><input type=\"hidden\" name=\"fach_id\" value=\"", $db->f("fach_id"),"\"></td></form></tr>";
 		}
 		$cssSw->switchClass();
 		echo"<tr><td class=\"".$cssSw->getClass()."\"><form method=\"POST\" name=\"add_f\" action=", $PHP_SELF, "><select name=\"fach_id\" size=1>";
 		$db2->query("SELECT * FROM faecher ORDER BY name");
 		while ($db2->next_record())
 			{
 			$ftmp = $db2->f("fach_id");
 			$db->query("SELECT * FROM fach_inst WHERE institut_id = '$i_view' AND fach_id = '$ftmp'");
	 		IF (!$db->next_record())
	 			echo "<option value=".$db2->f("fach_id").">", htmlReady(substr($db2->f("name"),0,80));
 			}
 		echo "</select></td><td class=\"".$cssSw->getClass()."\" align=\"center\"><input type=\"IMAGE\" src=\"./pictures/buttons/zuordnen-button.gif\" border=0 name=\"add_fach\" value=\" Zuordnen\"><input type=\"hidden\" name=\"i_view\" value=\"", $i_id, "\"></td></form></tr>";
 		echo "</table><br><br>";
  		}
  	}
}

echo"</table>";
page_close();
?>
</body>
</html>
<!-- $Id$ -->