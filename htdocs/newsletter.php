<?php
/*
newsletter.php - Newsletter-Verwaltung von Stud.IP.
Copyright (C) 2002 Ralf Stockmann <rstockm@uni-goettingen.de>

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

	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	require_once("visual.inc.php");
	require_once("messaging.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");


// Newsletter arrays

	// Standard

	$newsletter[0]["name"] = "Stud.IP Newsletter";
	$newsletter[0]["SQL"] = "WHERE perms != 'user' AND perms != 'autor'";
	$newsletter[0]["text"] = "Hallo dies ist ein Text";

	// weitere

	$newsletter[1]["name"] = "Stud.IP Admin-Newsletter";
	$newsletter[1]["SQL"] = "WHERE perms = 'admin'";
	$newsletter[1]["text"] = "Hallo dies ist ein noch ein Text";
	
// Hilfsfunktionen


function CheckPersonInNewsletter ($username, $newsletter_id)    // Funktion, mit der man innerhalb einer Newsletter-Personengruppe suchen kann
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT * ".
		"FROM auth_user_md5 ".
		$newsletter[$newsletter_id]["SQL"].
		" AND user_id = '$user_id'";
	$db->query($query); 
	if ($db->next_record()) {
		$status = "letter";
	} else {
		$status = FALSE;
	}
	return $status;
}

function CheckPersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man die Ausnahmeliste durchsucht
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT status FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'";
	$db->query($query); 
	if ($db->next_record()) {
		if ($db->f("status") == 1) {
			$status = "added";
		} else {
			 $status = "removed";
		}
	} else {
		$status = FALSE;
	}
	return $status;
}

function AddPersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Positivliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "removed") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder in den Newsletter aufgenommen.§";
	} elseif (CheckPersonInNewsletter($username, $newsletter_id) != "letter" AND $status != "added")  {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '1', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde in den Newsletter aufgenommen.§";
	}
	return $msg;
}

function RemovePersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Negativliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "added") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder aus dem Newsletter gel&ouml;scht.§";
	} elseif ($status != "removed") {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '0', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde aus dem Newsletter gel&ouml;scht.§";
	}
	return $msg;
}

function PrintRemoveSearch ($search_exp, $newsletter_id)    // Funktion, mit der man innerhalb einer Newsletter-Personengruppe suchen kann
{ global $newsletter;
	$db=new DB_Seminar;
	$query = "SELECT DISTINCT * ".
		"FROM auth_user_md5 ".
		$newsletter[$newsletter_id]["SQL"].
		" AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ";
	$db->query($query); 
	if (!$db->num_rows()) {
		echo "&nbsp; keine Treffer&nbsp; ";
	} else {
		echo $db->num_rows()." Treffer:&nbsp; ";
		echo "&nbsp; <select name=\"remove\">";
		while ($db->next_record()) {
			printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,35).")", $db->f("perms"));
		}
		echo "</select>&nbsp; ";
		printf ("<input type=\"IMAGE\" name=\"search\" src=\"./pictures/buttons/abschicken-button.gif\" border=\"0\" value=\" Personen austragen\" %s>&nbsp;  ", tooltip("Person austragen"));
	}
}

function PrintAddSearch ($search_exp, $newsletter_id)    // Funktion, mit der man innerhalb einer Newsletter-Personengruppe suchen kann
{ global $newsletter;
	$db=new DB_Seminar;
	$query = "SELECT DISTINCT * ".
		"FROM auth_user_md5 WHERE".
		" Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%' ORDER BY Nachname ";
	$db->query($query); 
	if (!$db->num_rows()) {
		echo "&nbsp; keine Treffer&nbsp; ";
	} else {
		echo $db->num_rows()." Treffer:&nbsp; ";
		echo "&nbsp; <select name=\"add\">";
		while ($db->next_record()) {
			printf ("<option value=\"%s\">%s - %s\n", $db->f("username"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,35).")", $db->f("perms"));
		}
		echo "</select>&nbsp; ";
		printf ("<input type=\"IMAGE\" name=\"search\" src=\"./pictures/buttons/abschicken-button.gif\" border=\"0\" value=\" Person eintragen\" %s>&nbsp;  ", tooltip("Person eintragen"));
	}
}

function PrintExclusions($newsletter_id)
{ global $newsletter, $cssSw, $PHP_SELF;
	$db=new DB_Seminar;
	$db->query ("SELECT status, auth_user_md5.* FROM newsletter LEFT JOIN auth_user_md5 USING(user_id)  WHERE newsletter_id = '$newsletter_id'");
	echo "<table border=\"0\" cellspacing=\"0\" align= \"center\" cellpadding=\"0\" width=\"80%\">";
	while ($db->next_record()) {
		$cssSw->switchClass(); 
		printf ("<tr><td class=\"%s\">%s, %s</td><td class=\"%s\">%s (%s)</td><td class=\"%s\">%s</td><td class=\"%s\">", $cssSw->getClass(), $db->f("Nachname"), $db->f("Vorname"), $cssSw->getClass(), $db->f("username"), $db->f("perms"), $cssSw->getClass(), $db->f("Email"), $cssSw->getClass());
		if ($db->f("status") == "0") {
			printf ("<a href=\"$PHP_SELF?add=%s\">wieder eintragen</a>",$db->f("username"));
		} else {
			printf ("<a href=\"$PHP_SELF?remove=%s\">Aus Liste austragen</a>",$db->f("username"));
		}
		echo "</td>";
	}
	echo "</table>";
}

function SendLetter($newsletter_id)
{ global $newsletter, $cssSw;
	$db=new DB_Seminar;
	$db->query ("SELECT * FROM auth_user_md5 ".$newsletter[$newsletter_id]["SQL"]."GROUP BY Email ORDER BY Nachname");
	echo "<table border=\"0\" cellspacing=\"0\" align= \"center\" cellpadding=\"0\" width=\"80%\">";
	while ($db->next_record()) {
		$cssSw->switchClass(); 
		printf ("<tr><td class=\"%s\">%s, %s</td><td class=\"%s\">%s (%s)</td><td class=\"%s\">%s</td><td class=\"%s\">", $cssSw->getClass(), $db->f("Nachname"), $db->f("Vorname"), $cssSw->getClass(), $db->f("username"), $db->f("perms"), $cssSw->getClass(), $db->f("Email"), $cssSw->getClass());
		if (CheckPersonNewsletter ($db->f("username"), $newsletter_id) == "removed") {
			echo "<b>ausgetragen</b>";
		} else {
			echo "eingetragen";
		}
		echo "</td>";
	}
	
	// Positivliste
	
	$db->query ("SELECT auth_user_md5.* FROM newsletter LEFT JOIN auth_user_md5 USING(user_id) WHERE newsletter_id = '$newsletter_id' AND status = '1'");
	while ($db->next_record()) {
		$cssSw->switchClass(); 
		printf ("<tr><td class=\"%s\">%s, %s</td><td class=\"%s\">%s (%s)</td><td class=\"%s\">%s</td><td class=\"%s\">Positivliste</td>", $cssSw->getClass(), $db->f("Nachname"), $db->f("Vorname"), $cssSw->getClass(), $db->f("username"), $db->f("perms"), $cssSw->getClass(), $db->f("Email"), $cssSw->getClass());
	}
	echo "</table>";
}


// Initialisierungen und Abfragen

$cssSw=new cssClassSwitcher;
if (!$newsletter_id) {    // keine Newsletter ausgewaehlt - auf Standard stellen
	$newsletter_id = 0;
}
if ($remove) {
	$msg = RemovePersonNewsletter($remove,$newsletter_id);
}
if ($add) {
	$msg = AddPersonNewsletter($add,$newsletter_id);
}


// Ausgabeteil

?>

<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"align="left"><b>&nbsp;Newsletter - Seite</b></td>
</tr>
<tr>
	<td class="blank">
		<blockquote><br>	
		Auf dieser Seite wird der Stud.IP Newsletter organisiert.
		</blockquote>
	</td>
</tr>
<?
if ($msg) parse_msg($msg);
?>


<tr>
	<td class="blank">
		<blockquote><br>	
		<table border=0 align="center" cellspacing=0 cellpadding=5 width=80%>
			<tr>
				<td class="steel1"><b>Aktiver Newsletter:</b>&nbsp; 
<?				echo $newsletter[$newsletter_id]["name"]."<br><b>Abfrage:</b>&nbsp; "; 
				echo $newsletter[$newsletter_id]["SQL"]."<br><b>Treffer:</b>&nbsp; "; 
				$db=new DB_Seminar;
				$db->query ("SELECT * FROM auth_user_md5 ".$newsletter[$newsletter_id]["SQL"]."GROUP BY Email ORDER BY Nachname");
				echo $db->num_rows();
?>				
				</td>
			</tr>
		</table>
		</blockquote>
		<br>
		<form action="<? echo $PHP_SELF ?>" method="POST">
<?
		if ($search_exp) {
			PrintRemoveSearch($search_exp, $newsletter_id);
		} else {
			echo "<font size=\"-1\">&nbsp; Austragen</font><br>";
			echo "&nbsp; <img src=\"./pictures/down.gif\">&nbsp; <input type=\"text\" name=\"search_exp\" value=\"\">";
			printf ("<input type=\"IMAGE\" name=\"search\" src= \"./pictures/suchen.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip("Person suchen"));
		} 
		echo "</form>";
?>
		<form action="<? echo $PHP_SELF ?>" method="POST">
<?
		if ($search_add) {
			PrintAddSearch($search_add, $newsletter_id);
		} else {
			echo "<font size=\"-1\">&nbsp; Eintragen</font><br>";
			echo "&nbsp; <img src=\"./pictures/up.gif\">&nbsp; <input type=\"text\" name=\"search_add\" value=\"\">";
			printf ("<input type=\"IMAGE\" name=\"search\" src= \"./pictures/suchen.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip("Person suchen"));
		} 
		echo "</form>";

		echo "&nbsp; Ausnahmen:";

		PrintExclusions($newsletter_id);

		echo "<br>&nbsp; Versandstatus:";

		SendLetter($newsletter_id);
?>		
	</td>
</tr>
</table>
<?
  page_close();
?>
</body>
</html>