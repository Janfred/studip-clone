<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_lernmodule.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

function ilias_auth_user()
{
	global $ilias_uname, $ilias_pw, $auth_mode;
	echo _("Bitte geben Sie die Logindaten Ihres ILIAS-Accounts an!") . "<br><br>";
	echo _("Benutzername:") . "<br>"; ?>
	 <input type="text" name="ilias_uname" size=20 maxlength=63 value="<? echo $ilias_uname; ?>"><br>
	<? echo _("Passwort:") . "<br>"; ?>
	 <input type="password" name="ilias_pw" size=20 maxlength=32><br><br><br>
	<?
	$auth_mode = false;
}

function check_ilias_auth()
{
	global $ilias_uname, $ilias_pw;
	$db = new DB_Ilias;
	if ($ilias_uname == "")
		return false;
	$db->query("SELECT passwort FROM benutzer WHERE benutzername = '" . $ilias_uname . "'");
	if (($db->next_record()) AND ($db->f("passwort") == crypt($ilias_pw,substr($ilias_pw,0,2))) )
	{
		echo "<input type=\"hidden\" name=\"ilias_uname\" value=\"$ilias_uname\">";
		echo "<input type=\"hidden\" name=\"ilias_pw\" value=\"$ilias_pw\">";
		echo "<b>" . ("Authentifizierung f&uuml;r ILIAS war erfolgreich.") . "</b><br><br>";
		return true;
	}
	echo "<b>" . _("Der Benutzername oder das Passwort f&uuml;r ILIAS ist nicht korrekt.") . "</b><br>";
	return false;
}

function check_studip_auth()
{
	global $studip_uname, $studip_pw;
	$db = new DB_Seminar;
	if ($studip_uname == "")
		return false;
	$db->query("SELECT password FROM auth_user_md5 WHERE username = '" . $studip_uname . "'");
	if (($db->next_record()) AND ($db->f("password") == md5($studip_pw)) )
	{
		echo "<input type=\"hidden\" name=\"studip_uname\" value=\"$studip_uname\">";
		echo "<input type=\"hidden\" name=\"studip_pw\" value=\"$studip_pw\">";
		echo "<b>" . ("Authentifizierung f&uuml;r Stud.IP war erfolgreich.") . "</b><br><br>";
		return true;
	}
	echo "<b>" ._("Der Benutzername oder das Passwort f&uuml;r Stud.IP ist nicht korrekt.") . "</b><br>";
	return false;
}

function studip_auth_user()
{
	global $studip_uname, $studip_pw, $auth_mode;
	echo _("Bitte geben Sie die Logindaten Ihres Stud.IP-Accounts an!") . "<br><br>";
	echo _("Benutzername:") . "<br>"; ?>
	 <input type="text" name="studip_uname" size=20 maxlength=63 value="<? echo $studip_uname; ?>"><br>
	<? echo _("Passwort:") . "<br>"; ?>
	 <input type="password" name="studip_pw" size=20 maxlength=32><br><br><br>
	<?
	$auth_mode = false;
}

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
	
include_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
include_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
include_once ($ABSOLUTE_PATH_STUDIP."/msg.inc.php");

if ($ILIAS_CONNECT_ENABLE)
{

	include_once ("$ABSOLUTE_PATH_STUDIP/$RELATIVE_PATH_LEARNINGMODULES" ."/lernmodul_user_functions.inc.php"); // Funktionen f&uuml;r ILIAS-User

	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="topic" colspan="2"><b>Migration von ILIAS-NutzerInnen-Accounts nach Stud.IP</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan="2">&nbsp; 
			</td>
		</tr>
		<tr valign="top">
     			<td width="90%" class="blank">
	<form method="POST" action="<? echo $PHP_SELF; ?>">
<?
	if (isset($back_x))
		unset($mode);
	if (!in_array($mode, array("i2s", "s2i", "connect")))
	{
		$infobox = array	(array ("kategorie"  => _("Information:"),
				"eintrag" => array	(array (	"icon" => "pictures/ausruf_small.gif",
										"text"  => sprintf(_("Diese Seite organisiert die Verbindung zwischen BenutzerInnen von ILIAS und Stud.IP."), "<br><i>", "</i>") ) ) ) );
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/icon-posting.gif" ,
										"text"  => _("W&auml;hlen Sie eine Option.") );
		echo "<br><b>" . _("W&auml;hlen Sie bitte eine der folgenden M&ouml;glichkeiten:") . "</b><br><br><br>";
		echo "<input type=\"RADIO\" name=\"mode\" value=\"i2s\">&nbsp;";
		echo _("Neuen Stud.IP-Account anlegen und einem bestehenden ILIAS-NutzerInnen-Account zuordnen") . "<br><br>";
		echo "<input type=\"RADIO\" name=\"mode\" value=\"s2i\" checked>&nbsp;";
		echo _("F&uuml;r den aktuellen Stud.IP-Account einen passenden ILIAS-Account anlegen") . "<br><br>";
		echo "<input type=\"RADIO\" name=\"mode\" value=\"connect\">&nbsp;";
		echo _("Den aktuellen Stud.IP-Account mit einem bestehenden ILIAS-Account verbinden") . "<br><br>";
	}
	else 
	{
		echo "<input type=\"hidden\" name=\"mode\" value=\"$mode\">&nbsp;";
		$infobox = array	(array ("kategorie"  => _("Information:"),
				"eintrag" => array	(array (	"icon" => "pictures/ausruf_small.gif",
										"text"  => sprintf(_("Diese Seite organisiert die Verbindung zwischen BenutzerInnen von ILIAS und Stud.IP."), "<br><i>", "</i>") ) ) ) );
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/icon-posting.gif" ,
										"text"  => _("Geben bitte Sie ihre Logindaten ein.") );
		$auth_mode = true;
		switch($mode)
		{	
			case "i2s":
				if (!check_ilias_auth()) 
					ilias_auth_user();
				if ($auth_mode == true)
				{
					if (create_studip_user($ilias_uname))
						echo _("Es wurde ein neuer Stud.IP-Account f&uuml;r Sie angelegt.");
					else
						echo _("Beim Anlegen des Accounts ist ein Fehler aufgetreten.");
				}
			break;
			case "s2i":
//				if (!check_studip_auth()) 
//					studip_auth_user();
				if ($auth_mode == true)
				{
					if (create_ilias_user($auth->auth["uname"] /*$studip_uname*/))
						echo _("Es wurde ein neuer ILIAS-Account f&uuml;r Sie angelegt.");
					else
						echo _("Beim Anlegen des Accounts ist ein Fehler aufgetreten.");
				}
			break;
			case "connect":
				if (!check_ilias_auth()) 
					ilias_auth_user();
//				if (!check_studip_auth()) 
//					studip_auth_user();
				if ($auth_mode == true)
				{
					if (connect_users($auth->auth["uname"] /*$studip_uname*/, $ilias_uname))
						echo _("Die Accounts wurden verbunden.");
					else
						echo _("Beim der Verbindung der Accounts ist ein Fehler aufgetreten.");
				}
			break;
			default: $auth_mode = false;
		}
	}
?>
		<br>
		<!--<input type="IMAGE" <? echo makeButton("zurueck", "src"); ?> name="back" value="<? echo _("Zur&uuml;ck"); ?>">-->
		<? if ($auth_mode == false) { ?>
		<input type="IMAGE" <? echo makeButton("weiter", "src"); ?> name="next" value="<? echo _("Weiter"); ?>">
		<? } ?>
		<br>
		<br>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
<?
			print_infobox ($infobox,"pictures/lernmodule.jpg");
?>		
		</td>		
	</tr>
	</table>
<?
}
else 
{
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	parse_window ("error§" . _("Das Verbindungsmodul f&uuml;r ILIAS-Lernmodule ist nicht eingebunden. Damit Lernmodule verwendet werden k&ouml;nnen, muss die Verbindung zu einer ILIAS-Installation in den Systemeinstellungen hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "§",
				_("Lernmodule nicht eingebunden"));
}
page_close();
?>
</body>
</html>