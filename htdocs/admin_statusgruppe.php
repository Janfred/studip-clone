<?
/*
admin_statusgruppe.php - Statusgruppen-Verwaltung von Stud.IP.
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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
	$auth->login_if($auth->auth["uid"] == "nobody");
	$perm->check("tutor");

## Set this to something, just something different...
  $hash_secret = "dslkjjhetbjs";
  
	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");

	require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
		
// Rechtecheck



	if ($range_id && !$perm->have_perm("root")) {
		//Sicherheitscheck

	  $range_perm=get_perm($range_id);
	  if (($ebene=="sem") && ($range_perm!="admin" && $range_perm!="dozent" && $range_perm!="tutor")) 
			die;
		if (($ebene=="inst") && ($range_perm!="admin"))
			die;
		if (($ebene=="fak") && ($range_perm!="admin")) 
			die;
	}


	
		//Sicherheitscheck ob was zum Bearbeiten gewaehlt ist.
	if (!$range_id) {
		echo "</tr></td></table>";
		die;
	}

// Hilfsfunktionen

function MakeUniqueID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));

	$db->query ("SELECT statusgruppe_id FROM statusgruppen WHERE statusgruppe_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}

function PrintAllStatusgruppen ()
{
        echo "<select name=\"move_old_statusgruppe\">";
	$db=new DB_Seminar;
	$db->query ("SELECT DISTINCT name FROM statusgruppen ORDER BY name ASC");
	while ($db->next_record()) {
		printf ("<option>%s</option>",$db->f("name"));
	}
	echo "</select>";
}

function GetAllSelected ()
{	global $range_id;
	$zugeordnet[] = "";
  	$db3=new DB_Seminar;
	$db3->query ("SELECT DISTINCT user_id FROM statusgruppe_user LEFT JOIN statusgruppen USING(statusgruppe_id) WHERE range_id = '$range_id'");
	while ($db3->next_record()) {
		$zugeordnet[] = $db3->f("user_id");
	}
	RETURN $zugeordnet;
}

function PrintAktualStatusgruppen ()
{	global $range_id, $PHP_SELF;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id, size FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position ASC");
	$AnzahlStatusgruppen = $db->num_rows();
	if ($AnzahlStatusgruppen == 0) {
		$infobox = array	(			
		array  ("kategorie"  => "Information:",
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => "Es sind noch keine Statusgruppen angelegt. Bitte nutzen Sie die obere Zeile, um f&uuml;r diesen Bereich Statusgruppen anzulegen!"
									)
			)
		    )
		);
		echo "<br>";
		// print the info_box
		print_infobox ($infobox,"pictures/seminare.jpg");
	}
	$i = 0;
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		$size = $db->f("size");
		echo "<table width=\"95%\" border=\"0\">
			        <tr> 
				          <td width=\"5%\">";
		printf ("            	  <input type=\"IMAGE\" name=\"%s\" src=\"./pictures/move.gif\" border=\"0\">&nbsp; </td>", $statusgruppe_id);
		printf ("	          <td width=\"95%%\" class=\"topic\">&nbsp; %s</td>",$db->f("name"));
		printf ( "	          <td width=\"5%%\"><a href=\"$PHP_SELF?cmd=remove_statusgruppe&statusgruppe_id=%s&range_id=%s\"><img src=\"pictures/lighttrash.gif\" width=\"11\" height=\"17\" border=\"0\"></a></td>",$statusgruppe_id, $range_id);
		echo 	"</tr>";

		$db2->query ("SELECT statusgruppe_user.user_id, Vorname, Nachname, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) WHERE statusgruppe_id = '$statusgruppe_id'");
		$k = 1;
		while ($db2->next_record()) {
			if ($k > $size) {
				$farbe = "#AAAAAA";
			} else {
				$farbe = "#000000";
			}
			printf ("     <tr><td><font color=\"%s\">$k</font></td>", $farbe);
			printf ("       <td class=\"steel1\">%s&nbsp; %s</td>",$db2->f("Vorname"), $db2->f("Nachname"));
			printf ( "	   <td width=\"5%%\"><a href=\"$PHP_SELF?cmd=remove_person&statusgruppe_id=%s&username=%s&range_id=%s\"><img src=\"pictures/trash.gif\" width=\"11\" height=\"17\" border=\"0\"></a></td>", $statusgruppe_id, $db2->f("username"), $range_id);
			echo "	</tr>";
			$k++;
		}
		while ($k <= $db->f("size")) {
			echo "     <tr> 
  			                  <td><font color=\"#FF4444\">$k</font></td>";
			printf ("       <td class=\"blank\">&nbsp; </td>");
			printf ( "	   <td width=\"5%%\">&nbsp; </td>");
			echo "	</tr>";
			$k++;
		} 
		$i++;
		echo "</table>";
		if ($i < $AnzahlStatusgruppen) {
			printf ("<p align=\"center\"><a href=\"$PHP_SELF?cmd=swap&statusgruppe_id=%s&range_id=%s\"><img src=\"pictures/move_up.gif\" width=\"13\" height=\"11\" border=\"0\"></a>",$statusgruppe_id, $range_id); 
			printf ("&nbsp; &nbsp; &nbsp; <a href=\"$PHP_SELF?cmd=swap&statusgruppe_id=%s&range_id=%s\"><img src=\"pictures/move_down.gif\" width=\"13\" height=\"11\" border=\"0\"></a> </p>",$statusgruppe_id, $range_id);
		}
	}
}

function PrintSearchResults ($search_exp)
{	global $SessSemName, $range_id;
		$db=new DB_Seminar;
		if (get_object_type($range_id) == "sem") {
			$query = "SELECT a.user_id, username, Vorname, Nachname, perms FROM auth_user_md5 a ".		
			"LEFT JOIN seminar_user b ON (b.user_id=a.user_id AND b.seminar_id='$range_id')  ".
			"WHERE perms IN ('autor','tutor','dozent') AND ISNULL(b.seminar_id) AND ".
			"(username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ".
			"ORDER BY Nachname";
		} else {
			$query = "SELECT DISTINCT auth_user_md5.user_id, Vorname, Nachname, username, perms ".
			"FROM auth_user_md5 LEFT JOIN user_inst ON user_inst.user_id=auth_user_md5.user_id AND Institut_id = '$inst_id' ".
			"WHERE perms !='root' AND (user_inst.inst_perms = 'user' OR user_inst.inst_perms IS NULL) ".
			"AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ";
		}
		$db->query($query); // results all users which are not in the seminar
		if (!$db->num_rows()) {
			echo "&nbsp; keine Treffer&nbsp; ";
		} else {
			echo "&nbsp; <select name=\"Freesearch[]\" size=\"10\" multiple>";
			while ($db->next_record()) {
				printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,35).")", $db->f("perms"));
			}
			echo "</select>";
		}
}

function PrintAktualMembers ($range_id)
{	
	$bereitszugeordnet = GetAllSelected();
	if (get_object_type($range_id) == "sem") {
		echo "<font size=\"-1\">&nbsp; TeilnehmerInnen der Veranstaltung</font><br>";
		$query = "SELECT seminar_user.user_id, username, Nachname, Vorname, perms FROM auth_user_md5 LEFT JOIN seminar_user USING(user_id)  WHERE Seminar_id = '$range_id' ORDER BY Nachname ASC";
	} else {
		echo "<font size=\"-1\">&nbsp; MitarbeiterInnen des Instituts</font><br>";
		$query = "SELECT user_inst.user_id, username, Nachname, Vorname, inst_perms AS perms FROM auth_user_md5 LEFT JOIN user_inst USING(user_id)  WHERE Institut_id = '$range_id' AND inst_perms != 'user' ORDER BY Nachname ASC";
	}
	echo "&nbsp; <select size=\"10\" name=\"AktualMembers[]\" multiple>";
	$db=new DB_Seminar;
	$db->query ($query);
	while ($db->next_record()) {
		if (in_array($db->f("user_id"), $bereitszugeordnet)) {
			$tmpcolor = "#777777";
		} else {
			$tmpcolor = "#000000";
		}
		printf ("<option style=\"color:%s;\" value=\"%s\">%s - %s\n", $tmpcolor, $db->f("username"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,35).")", $db->f("perms"));
	}
	echo "</select>";
}

//Hilfsfunktion, erzeugt eine lsite mit allen ids der einrichtungen
function get_inst_list(){
	global $range_id;
	$db = new DB_Seminar("SELECT Institut_id FROM seminar_inst WHERE seminar_id='$range_id'"); //beteiligte Einrichtungen
	$value_list = "";
	$result[] = $SessSemName[5]; //Heimatinstitut
	if ($db->num_rows()){
		while($db->next_record()) {
			$result[] = $db->Record[0];
		}
	}
	$value_list = "'".join("','",$result)."'";
	return $value_list;
}

function PrintInstitutMembers ()
{	global $range_id;
	echo "<font size=\"-1\">&nbsp; MitarbeiterInnen der Institute</font><br>";
	echo "&nbsp; <select name=\"InstitutMembers\">";
	$db=new DB_Seminar;
	$value_list = get_inst_list();
	$query = "SELECT DISTINCT b.user_id, username, Vorname, Nachname, inst_perms, perms FROM user_inst a ".
			"LEFT JOIN auth_user_md5  b USING(user_id) ".
			"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$range_id')  ".
			"WHERE a.Institut_id IN($value_list) AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) ORDER BY Nachname";
	$db->query($query); // ergibt alle berufbaren Personen
		printf ("<option>---</option>");
	while ($db->next_record()) {
		printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,35).")", $db->f("perms"));
	}
	echo "</select>";
}


// Abfrage der Formulare und Aktionen

//	echo $range_id;

	// neue Statusgruppe hinzufuegen

	if (($cmd=="add_new_statusgruppe") && ($new_statusgruppe_name != "")) {
		$statusgruppe_id = MakeUniqueID();
		$mkdate = time();
		$chdate = time();
		$db=new DB_Seminar;
		$db->query ("SELECT position FROM statusgruppen WHERE range_id = '$range_id' ORDER BY position DESC");
		if ($db->next_record()) {
			$position = $db->f("position")+1;
		} else {
			$position = "1";
		}
		$db->query("INSERT INTO statusgruppen SET statusgruppe_id = '$statusgruppe_id', name = '$new_statusgruppe_name', range_id= '$range_id', position='$position', size = '$new_statusgruppe_size', mkdate = '$mkdate', chdate = '$chdate'");
	}
	
	// bestehende Statusgruppe in Textfeld
	
	if ($cmd=="move_old_statusgruppe")  {
		$statusgruppe_name = $move_old_statusgruppe;		
	} else {
		$statusgruppe_name = "Name der Gruppe";
	}

	// zuordnen von Personen zu einer Statusgruppe

	if ($cmd=="move_person" && ($AktualMembers !="" || $InstitutMembers !="---" || $Freesearch !=""))  {
		while (list($key, $val) = each ($HTTP_POST_VARS)) {
//			echo "<b>$key</b>: $val<br>";
			$statusgruppe_id = substr($key, 0, -2);
		}
		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$mkdate = time();
		if ($AktualMembers != "") {
			for ($i  = 0; $i < sizeof($AktualMembers); $i++) {
				$user_id = get_userid($AktualMembers[$i]);
				$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
				if (!$db->next_record()) {
					$db2->query("INSERT INTO statusgruppe_user SET statusgruppe_id = '$statusgruppe_id', user_id = '$user_id'");
				}
			}
		}
		if (isset($InstitutMembers) && $InstitutMembers != "---") {
			$user_id = get_userid($InstitutMembers);
			$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
			if (!$db->next_record()) {			
				$db->query("INSERT INTO statusgruppe_user SET statusgruppe_id = '$statusgruppe_id', user_id = '$user_id'");
				$db->query("INSERT INTO seminar_user SET Seminar_id = '$range_id', user_id = '$user_id', status = 'autor', gruppe = '6' , mkdate = '$mkdate'");
			}
		}
		if ($Freesearch != "") {
			for ($i  = 0; $i < sizeof($Freesearch); $i++) {
				$user_id = get_userid($Freesearch[$i]);
				$db->query("SELECT * FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
				if (!$db->next_record()) {
					$db2->query("INSERT INTO statusgruppe_user SET statusgruppe_id = '$statusgruppe_id', user_id = '$user_id'");
					if (get_object_type($range_id) == "sem") {
						$db2->query("INSERT INTO seminar_user SET Seminar_id = '$range_id', user_id = '$user_id', status = 'autor', gruppe = '6' , mkdate = '$mkdate'");
					} 
				}
			}
		}
	}
	
	// Entfernen von Personen aus einer Statusgruppe
	
	if ($cmd=="remove_person") {
		$user_id = get_userid($username);
		$db=new DB_Seminar;
		$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");
	}
	
	// Entfernen von Statusgruppen

	if ($cmd=="remove_statusgruppe") {
		$db=new DB_Seminar;
		$db->query("SELECT position FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");
		if ($db->next_record()) {
			$position = $db->f("position");
		}
		$db=new DB_Seminar;
		$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id'");
		$db->query("DELETE FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");

		// Neusortierung
		
		$db->query("SELECT * FROM statusgruppen WHERE range_id = '$range_id' AND position > '$position'");
		while ($db->next_record()) {
			$new_position = $db->f("position")-1;
			$statusgruppe_id = $db->f("statusgruppe_id");
			$db2=new DB_Seminar;
			$db2->query("UPDATE statusgruppen SET position =  '$new_position' WHERE statusgruppe_id = '$statusgruppe_id'");
		}
	}
	
	// Aendern der Position

	if ($cmd=="swap") {
		$db->query("SELECT * FROM statusgruppen WHERE statusgruppe_id = '$statusgruppe_id'");
		if ($db->next_record()) {
			$current_position = $db->f("position");
			$next_position = $current_position + 1;
			$db2=new DB_Seminar;
			$db2->query("UPDATE statusgruppen SET position =  '$next_position' WHERE statusgruppe_id = '$statusgruppe_id'");
			$db2->query("UPDATE statusgruppen SET position =  '$current_position' WHERE range_id = '$range_id' AND position = '$next_position' AND statusgruppe_id != '$statusgruppe_id'");
		}
	}

// Ende Abfrage Formulare



// Beginn Darstellungsteil

	$db=new DB_Seminar;
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
		echo " -  Statusgruppen";
	?></b></td></tr><tr><td class="blank" colspan="2">&nbsp; </td></tr></table>

<table class="blank" width="100%" border="0" cellspacing="0">
  <tr>
    <td align="right" width="50%" class="blank">
 	<form action="<? echo $PHP_SELF ?>?cmd=move_old_statusgruppe" method="POST">
 	<?
  	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
  	?>
            &nbsp;
	<? 
	PrintAllStatusgruppen (); 
	?>
    	<input type="IMAGE" name="move_old_statusgruppe" src="./pictures/move.gif" border=0 value="Statusgruppe uebernehmen">&nbsp;  
        </form><br></td>
    <td align="right" width="50%" NOWRAP class="blank">
	<form action="<? echo $PHP_SELF ?>?cmd=add_new_statusgruppe" method="POST">
	<?
  	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
  	?>
        <font size="2">Name: </font>
        <input type="text" name="new_statusgruppe_name" value="<? echo $statusgruppe_name;?>">
        &nbsp; &nbsp; &nbsp; <font size="2">Anzahl:</font> 
        <input name="new_statusgruppe_size" type="text" value="" size="3">
        &nbsp; &nbsp; &nbsp; <b>Einf&uuml;gen</b>&nbsp; 
    	<input type="IMAGE" name="add_new_statusgruppe" src="./pictures/move_down.gif" border=0 value=" neue Statusgruppe ">&nbsp;  &nbsp; &nbsp; 
      </form><br></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0">
 <form action="<? echo $PHP_SELF ?>?cmd=move_person" method="POST">
	<?
  	  echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
  	?>
  <tr>
    <td class="steel1" valign="top" width="50%">
    	<br>
	<?
	if (get_object_type($range_id) == "sem" || get_object_type($range_id) == "inst") {
		PrintAktualMembers ($range_id);
	}
	?>
	<br><br>
	<?
	if (get_object_type($range_id) == "sem") {
		PrintInstitutMembers ();
	}
	?>
          <br><br>
	<?
	if ($search_exp) {
		PrintSearchResults($search_exp);
		echo "<input type=\"IMAGE\" name=\"search\" src= \"./pictures/rewind.gif\" border=\"0\" value=\" Personen suchen\">&nbsp;  ";
	} else {
		echo "<font size=\"-1\">&nbsp; freie Personensuche</font><br>";
		echo "&nbsp; <input type=\"text\" name=\"search_exp\" value=\"\">";
		echo "<input type=\"IMAGE\" name=\"search\" src= \"./pictures/suchen.gif\" border=\"0\" value=\" Personen suchen\">&nbsp;  ";
	} 
	?>                            
	<br><br>
    </td>
    <td class="blank" width="50%" align="center" valign="top"> 
	<?
	PrintAktualStatusgruppen ();
	?>
	<br>&nbsp; 
    </td>
  </tr>
 </form>
</table>
<p>&nbsp;</p>
<?

// Ende Darstellungsteil

page_close();
?>
</body>
</html>