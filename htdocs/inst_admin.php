<?php
/*
inst_admin.php - Instituts-Mitarbeiter-Verwaltung von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
$perm->check("admin");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins
		
require_once("msg.inc.php"); //Ausgaberoutinen an den User
require_once("config.inc.php"); //Grunddaten laden
require_once("visual.inc.php"); //htmlReady
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");	//Funktionen der Statusgruppen
	
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;	

$db->query ("SELECT Name, type FROM Institute WHERE Institut_id = '$inst_id'");
if ($db->next_record())
	$tmp_typ = $INST_TYPE[$db->f("type")]["name"];
$tmp_name=$db->f("Name");


function perm_select($name,$global_perm,$default)
{
$possible_perms=array("user","autor","tutor","dozent");
$counter=0;
echo "<select name=\"$name\">";
if ($global_perm == "admin")
	echo "<option selected>admin</option>";  // einmal admin, immer admin...
else {
	while ($counter <= 4 ) {
		echo "<option";
		if ($default==$possible_perms[$counter])  echo" selected";
		echo">$possible_perms[$counter]</option>";
		if ($possible_perms[$counter]==$global_perm) break;
		$counter++;
	}
}
echo "</select>";
return;
}

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left">&nbsp;<b>
		<?
		echo $tmp_typ, ": ", htmlReady(substr($tmp_name, 0, 60));
		if (strlen($tmp_name) > 60)
			echo "... ";
		echo " -  Mitarbeiter";
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>&nbsp;
		</td>
	</tr>

<?

//zeigen wir eine einzelne Person an?

if (isset($details)) {
	$db->query("SELECT auth_user_md5.*, user_inst.*, Institute.Name FROM auth_user_md5 LEFT JOIN user_inst USING (user_id) LEFT JOIN Institute USING (Institut_id) WHERE username = '$details' AND user_inst.Institut_id = '$inst'");
	while ($db->next_record()) {
		?>
		<tr>
		<td class="blank" colspan=2>
		<table border=0 align="center" cellspacing=0 cellpadding=2>
			<form method="POST" name="edit" action="inst_admin.php">
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" height="30"><b>&nbsp;Einrichtung:</b></td>
				<td class="<? echo $cssSw->getClass() ?>" ><?php  echo htmlReady($db->f("Name")) ?></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" height="30"><b>&nbsp;Name:</b></td>
				<td class="<? echo $cssSw->getClass() ?>" ><?php  echo $db->f("Vorname") . "   " . $db->f("Nachname") ?></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Status in der Einrichtung:&nbsp;</b></td>
				<td class="<? echo $cssSw->getClass() ?>" >
				<?
				perm_select("perms",$db->f("perms"),$db->f("inst_perms"));
				?>
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Gruppe / Funktion in der Einrichtung:&nbsp;</b></td>
				<td class="<? echo $cssSw->getClass() ?>" >
			<?	
			$user_id = $db->f("user_id")	;
			$query = "SELECT * FROM statusgruppe_user LEFT JOIN statusgruppen USING (statusgruppe_id) WHERE range_id ='$inst' AND user_id ='$user_id'";
			$db2 ->query($query);	
			$tmptxt = "";
			while ($db2->next_record()) {
				 $tmptxt .= $db2->f("name").", ";
			}
			echo htmlReady(substr($tmptxt,0,-2));
			
			?>	
			&nbsp; 
			</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Raum:</b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="raum" size=24 maxlength=31 value="<?php echo htmlReady($db->f("raum")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Sprechstunde:</b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="sprechzeiten" size=24 maxlength=63 value="<?php echo htmlReady($db->f("sprechzeiten")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Telefon:</b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="Telefon" size=24 maxlength=31 value="<?php echo htmlReady($db->f("Telefon")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?> >
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;Fax:</b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="Fax" size=24 maxlength=31 value="<?php echo htmlReady($db->f("Fax")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align=center>&nbsp;
				<input type="hidden" name="u_id"  value="<?php $db->p("user_id") ?>">
				<input type="hidden" name="ins_id"  value="<?php $db->p("Institut_id") ?>">
				<input type="IMAGE" name="u_edit" src="pictures/buttons/uebernehmen-button.gif" border=0 value="ver&auml;ndern">&nbsp;
				<input type="IMAGE" name="u_kill"  src="pictures/buttons/loeschen-button.gif" border=0  value=" l&ouml;schen ">&nbsp;
				<input type="IMAGE" name="nothing"  src="pictures/buttons/abbrechen-button.gif" border=0  value="abbrechen ">
				</td>
			</tr>
			<tr>
				<td class="blank"  colspan=2 class="blank">&nbsp;</td></tr>
			
			<? // links to everywhere
			print "<tr><td  class=\"steel1\" colspan=2 align=\"center\">";
				printf("&nbsp;pers&ouml;nliche Homepage <a href=\"about.php?username=%s\"><img src=\"pictures/einst.gif\" border=0 alt=\"Zur pers&ouml;nlichen Homepage des Benutzers\" align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp", $db->f("username"));
				printf("&nbsp;Nachricht an Benutzer <a href=\"sms.php?cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an den Benutzer verschicken\" border=0 align=\"texttop\"></a>", $db->f("username"));
			print "</td></tr>";
			?>
			</form>
		</table>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<?
	} // Ende der while-Schleife
} // Ende der Detail-Ansicht
	
else {

	// haben wir was uebergeben bekommen?

	if ( is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
    		if ($perms!="") { //hoffentlich auch was Sinnvolles?
			$db->query("SELECT Vorname, Nachname, perms FROM auth_user_md5 WHERE user_id = '$u_id'");
			while ($db->next_record()) {
				$scherge=$db->f("perms");
				$Fullname = $db->f("Vorname") . " " . $db->f("Nachname");
			}

			// Hier darf fast keiner was:

			if (isset($u_kill_x)) {
				if (!$perm->have_perm("root") && $scherge=='admin')
					my_error("<b>Sie haben keine Berechtigung einen Administrator dieser Einrichtung zu l&ouml;schen.</b>");
				else {
					$db2->query("DELETE from user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
					my_msg ("<b>$Fullname wurde aus der Einrichtung ausgetragen.</b>");
					// raus aus allen Statusgruppen
					RemovePersonStatusgruppeComplete (get_username($u_id), $ins_id);
				}
			} 

			if (isset($u_edit_x)) {
				if (!$perm->have_perm("root") && $scherge=='admin' && $u_id != $auth->auth["uid"])
					my_error("<b>Sie haben keine Berechtigung einen anderen Administrator dieser Einrichtung zu ver&auml;ndern.</b>");
				else {

					if ($perms=='autor' AND $scherge=='user') {
						my_error("<b>Sie k&ouml;nnen den User nicht auf AUTOR hochstufen, da er im gesamten System nur den Status USER hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte einen der Systemadministratoren.</b>");
					}
					elseif ($perms=='tutor' AND ($scherge=='user' OR $scherge=='autor')) {
						my_error("<b>Sie k&ouml;nnen den User nicht auf TUTOR hochstufen, da er im gesamten System nur den Status ".$scherge." hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte einen der Systemadministratoren.</b>");
					}
					elseif ($perms=='dozent' AND ($scherge=='user' OR $scherge=='autor' OR $scherge=='tutor')) {
						my_error("<b>Sie k&ouml;nnen den User nicht auf DOZENT hochstufen, da er im gesamten System nur den Status ".$scherge." hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte einen der Systemadministratoren.</b>");
					}
					elseif ($perms=='admin' AND ($scherge=='user' OR $scherge=='autor' OR $scherge=='tutor' OR $scherge=='dozent')) {
						my_error("<b>Sie k&ouml;nnen den User nicht auf ADMIN hochstufen, da er im gesamten System nur den Status ".$scherge." hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte einen der Systemadministratoren.</b>");
					}
					elseif ($perms=='root') {
						my_error("<b>Sie k&ouml;nnen den User nicht auf ROOT hochstufen, dieser Status ist im System nicht vorgesehen.</b>");
					}
					elseif ($scherge == 'admin' && $perms != 'admin') {
						my_error("<b>Globale Administratoren k&ouml;nnen auch an Einrichtung nur den Status \"admin\" haben.</b>");
					}
					else { //na, dann muss es wohl sein (grummel)
						$query = "UPDATE user_inst SET inst_perms='$perms' , Funktion='$inst_funktion' , raum='$raum' , Telefon='$Telefon' , Fax='$Fax' , sprechzeiten='$sprechzeiten' WHERE Institut_id = '$ins_id' AND user_id = '$u_id'";
						$db2->query($query);
						my_msg("<b>Status&auml;nderung f&uuml;r $Fullname durchgef&uuml;hrt.</b>");
					}
				}
			}
			$inst_id=$ins_id;
		}
	} // Ende HTTP-POST-VARS

	// Jemand soll ans Institut...
	if (isset($berufen_x) && $ins_id != "") {
    		if ($u_id == "0")
			my_error("<b>Bitte eine Person ausw&auml;hlen!</b>");
		else {		
	 	
			$db->query("SELECT *  FROM user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
			if (($db->next_record()) && ($db->f("inst_perms") != "user")) {
				// der Admin hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Institut
				my_error("<b>Die Person ist bereits in der Einrichtung eingetragen. Bitte verwenden Sie die untere Tabelle, um Rechte etc. zu &auml;ndern!</b>");
			} else {  // mal nach dem globalen Status sehen
				$db3->query("SELECT Vorname, Nachname, perms FROM auth_user_md5 WHERE user_id = '$u_id'");
				$db3->next_record();
				$Fullname = $db3->f("Vorname") . " " . $db3->f("Nachname");
				if ($db3->f("perms") == "root")
					my_error("<b>roots k&ouml;nnen nicht berufen werden!</b>");
				elseif ($db3->f("perms") == "admin") {
					if ($perm->have_perm("root")) {
					    // als admin aufnehmen
					    $db2->query("INSERT into user_inst (user_id, Institut_id, inst_perms, Funktion) values ('$u_id', '$ins_id', 'admin', '14')");
					    my_msg("<b>$Fullname wurde als \"admin\" in die Einrichtung aufgenommen.</b>");
					} else {
					    my_error("<b>Sie haben keine Berechtigung einen admin zu berufen!</b>");
					}
				} else {
					//ok, aber nur hochstufen (hat sich selbst schonmal gemeldet als Student an dem Inst)
					if ($db->f("inst_perms") == "user")
						$db2->query("UPDATE user_inst SET inst_perms='autor', Funktion='0' WHERE user_id='$u_id' AND Institut_id = '$ins_id' ");
					// ok, als das aufnehmen was er global ist aufnehmen.
					else
						$globalperms = get_global_perm($u_id);				
						$db2->query("INSERT into user_inst (user_id, Institut_id, inst_perms, Funktion) values ('$u_id', '$ins_id', '$globalperms', '0')");
					if ($db2->affected_rows())
						my_msg("<b>$Fullname wurde als \"$globalperms\" in die Einrichtung aufgenommen. Bitte verwenden Sie die untere Tabelle, um Rechte etc. zu &auml;ndern!</b>");
					else
						parse_msg ("error§<b>$Fullname konnte nicht in die Einrichtung aufgenommen werden!§");
				}
			}
		}
		$inst_id=$ins_id;
	}


?>
	<tr>
		<td class="blank" colspan=2>
<?

		
//Abschnitt zur Auswahl und Suche von neuen Personen
if ($inst_id != "" && $inst_id !="0") {
	$db->query("SELECT Name FROM Institute WHERE Institut_id ='$inst_id'");
	$db->next_record();
	$inst_name=$db->f("Name");
	if (isset($search_exp))
		{
		// Der Admin will neue Sklaven ins Institut berufen...
			if (!$search_exp) //wenn leerer Suchaussruck, verwutzen (aus Datenschutzgruenden)
				$search_exp=md5(uniqid(rand()));
			$db->query ("SELECT DISTINCT auth_user_md5.user_id, Vorname, Nachname, username, perms  FROM auth_user_md5 LEFT JOIN user_inst ON user_inst.user_id=auth_user_md5.user_id AND Institut_id = '$inst_id' WHERE perms !='root' AND (user_inst.inst_perms = 'user' OR user_inst.inst_perms IS NULL) AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ");		
			?>
			<blockquote>Auf dieser Seite k&ouml;nnen Sie Personen der Einrichtung <b><? echo htmlReady($inst_name) ?></b> zuordnen, Daten ver&auml;ndern und Berechtigungen vergeben.<br /><br /></blockquote>
			<table width="100%" border="0" bgcolor="#C0C0C0" bordercolor="#FFFFFF" cellpadding="2" cellspacing="0">			
			<form action="<? echo $PHP_SELF, "?inst_id=", $inst_id ?>" method="POST">
			<tr>
				<td class="blank" colspan=2>
				<blockquote>
					<table width="50%" border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="steel1">
						<font size=-1><b>neue Person der Einrichtung zuordnen</b><br>
						es wurden <? echo $db->num_rows() ?> Benutzer gefunden.<br>
						<?
						if ($db->num_rows()) {
						?>bitte w&auml;hlen Sie die zu berufende Person aus der Liste aus.</font>
						</td>
					</tr>
					<tr>
						<td class="steel1"><select name="u_id" size="1">
						<?
						//Alle User auswaehlen, auf die der Suchausdruck passt und die im Institut nicht schon was sind. Selected werden hierdurch 
//						printf ("<option value=\"0\">-- bitte ausw&auml;hlen --\n");
						while ($db->next_record())
							printf ("<option value=\"%s\">%s, %s (%s) - %s\n", $db->f("user_id"), $db->f("Nachname"), $db->f("Vorname"), $db->f("username"), $db->f("perms"));
							?>
							</select>&nbsp;
						<input type="hidden" name="ins_id" value="<?echo $inst_id;?>"><br />
						<input type="IMAGE" name="berufen" src="pictures/buttons/hinzufuegen-button.gif" border=0 value="berufen">
					<? } ?>
						<input type="IMAGE" name="reset" src="pictures/buttons/neuesuche-button.gif" border=0 value="Neue Suche">
						</td>
					</tr>
					</table>
				</blockquote>
				</td>
			</tr>
			</form>
		</table>
		<br>
		<?
		} // Ende der Berufung
	else
		{
		// Der Admin will neue Sklaven ins Institut berufen... aber erst mal suchen
		?>
			<blockquote>Auf dieser Seite k&ouml;nnen Sie Personen der Einrichtung <b><? echo htmlReady($inst_name) ?></b> zuordnen, Daten ver&auml;ndern und Berechtigungen vergeben. <br> Um weitere Personen als Mitarbeiter hinzuzuf&uuml;gen, benutzen Sie die Suche. <br /><br /></blockquote>
			<table width="100%" border="0" cellpadding="2" cellspacing="0">
			<form action="<? echo $PHP_SELF ?>" method="POST">
			<tr>
				<td class="blank" colspan=2>
				<blockquote>
					<table width="50%" border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="steel1">
						<font size=-1><b>neue Person der Einrichtung zuordnen</b><br>
						bitte geben Sie Vornamen, Nachnamen oder den Usernamen ein:<br></font>
						</td>
					</tr>
					<tr>
						<td class="steel1"><input type="TEXT" size=20 maxlength=255 name="search_exp"><br />
						<input type="IMAGE" name="search_user" src="pictures/buttons/suchestarten-button.gif" border=0 value="Suche starten ">
						&nbsp;<input type="hidden" name="inst_id" value="<?echo $inst_id;?>">
						</td>
					</tr>
					</table>
				</blockquote>
				</td>
			</tr>
			</form>
		</table>
		<br>
		<?
		}

	//nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
	if (!isset($sortby) || $sortby=="") 
		$sortby = "Nachname";

	//entweder wir gehoeren auch zum Institut oder sind global root und es ist ein Institut ausgewählt
	$db2->query("SELECT Institut_id FROM user_inst WHERE Institut_id = '$inst_id' AND user_id = '$user->id'");
	if ($db2->num_rows() > 0 || ($perm->have_perm("root") && isset($inst_id))) {  
	  	$query = "SELECT * FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) WHERE Institut_id ='$inst_id' AND inst_perms !='user' ORDER BY $sortby";
		$db->query($query);

		//Ausgabe der Tabellenueberschrift
		print ("<tr><td class=\"blank\" colspan=2><blockquote>");
		print ("<b>Bereits der Einrichtung zugeordnet:</b><br><br />");
		print ("<table width=\"90%\" border=0 cellspacing=0 cellpadding=2>");
		print ("<tr>");

		if ($db->num_rows() > 0) {
			// wir haben ein Ergebnis
			echo "<th width=\"15%\"><a href=\"inst_admin.php?sortby=Vorname&inst_id=$inst_id\">Vorname</a></th>";
			echo "<th width=\"15%\"><a href=\"inst_admin.php?sortby=Nachname&inst_id=$inst_id\">Nachname</a></th>";
			echo "<th width=\"15%\"><a href=\"inst_admin.php?sortby=inst_perms&inst_id=$inst_id\">Status </a></th>";
			echo "<th width=\"15%\">Gruppe / Funktion</th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=raum&inst_id=$inst_id\">Raum Nr.</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=sprechzeiten&inst_id=$inst_id\">Sprechzeit</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=Telefon&inst_id=$inst_id\">Telefon</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=Fax&inst_id=$inst_id\">Fax</a></th>";
			echo "</tr>";

			//anfuegen der daten an tabelle in schleife...

	  	while ($db->next_record()) {
	  			$user_id = $db->f("user_id");
	  			$query = "SELECT * FROM statusgruppe_user LEFT JOIN statusgruppen USING (statusgruppe_id) WHERE range_id ='$inst_id' AND user_id ='$user_id'";
				$db2 ->query($query);
	  			$cssSw->switchClass();
				ECHO "<tr valign=middle align=left>";
				
				  if ($perm->have_perm("root") || $db->f("inst_perms") != "admin" || $db->f("username") == $auth->auth["uname"])
					printf ("<td class=\"%s\">%s</td><td class=\"%s\"><a href=\"%s?details=%s&inst=%s\">%s</a></td>", $cssSw->getClass(), $db->f("Vorname"),  $cssSw->getClass(), $PHP_SELF, $db->f("username"), $db->f("Institut_id"), $db->f("Nachname"));	 
				else
					printf ("<td class=\"%s\">&nbsp;%s</td><td class=\"%s\">%s</td>", $cssSw->getClass(), $db->f("Vorname"), $cssSw->getClass(), $db->f("Nachname"));	 ?>
	
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo $db->f("inst_perms"); ?></td>
				<td class="<? echo $cssSw->getClass() ?>"  align="left"><?
				
				$tmptxt = "";
				while ($db2->next_record()) {
					 $tmptxt .= $db2->f("name").", ";
				}
				echo htmlReady(substr($tmptxt,0,-2));
				
				?>
				
				&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("raum")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("sprechzeiten")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("Telefon")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("Fax")); ?></td>
				</tr>
				<?php
//	endif;
				print ("</tr>");
			}

			//Link fuer tolle Rundmailfunktion wird hier gebastelt
	
			echo"</table><br><b>Rundmail an alle Mitarbeiter verschicken</b><br><br>&nbsp;Bitte hier <a href=\"mailto:";
			$db->seek(0);	

			$kommaja=false;
	
			while ($db->next_record()) {
				if ($db->f("inst_perms")!='user') { 
					if ($kommaja) echo", ";
					echo $db->f("Email");
					$kommaja=true;
				}
			}
			echo"\">klicken</a><br /><br /></blockquote></td></tr>";

			print("</table>");
		} else { // wir haben kein Ergebnis
			printf("</table>Es wurde niemand gefunden! Bevor Sie die Mitarbeiterliste dieser Einrichtung bearbeiten k&ouml;nnen, m&uuml;ssen Sie der Einrichtung zuerst Mitarbeiter zuordnen.<br /><br />");
		}
	}	
}
}
?>

</table>
<?
	  page_close()
 ?>
</body>
</html>
<!-- $Id$ -->
