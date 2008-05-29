<?
# Lifter002: 
/**
* Account-Migration from Stud.IP to ILIAS.
*
* This file calls functions to create ILIAS-Useraccounts
* and to connect them with Stud.IP-Accounts.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		migration2studip
* @package		ELearning
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// migration2studip.php
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

/**
* Asks for ILIAS-user-authentification
*
* This function echos a question for username and password of an ILIAS-Account.
*
* @access	public
*/
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

/**
* checks ILIAS-user-authentification
*
* This function checks if the given password and username are correct.
*
* @access	public
*/
function check_ilias_auth()
{
	global $ilias_uname, $ilias_pw, $out;
	$db = new DB_Ilias;
	if ($ilias_uname == "")
		return false;
	$db->query("SELECT passwort FROM benutzer WHERE benutzername = '" . $ilias_uname . "'");
	if (($db->next_record()) AND ($db->f("passwort") == crypt($ilias_pw,substr($ilias_pw,0,2))) )
	{
		echo "<input type=\"hidden\" name=\"ilias_uname\" value=\"$ilias_uname\">";
		echo "<input type=\"hidden\" name=\"ilias_pw\" value=\"$ilias_pw\">";
//		echo "<b>" . ("Authentifizierung f&uuml;r ILIAS war erfolgreich.") . "</b><br><br>";
		return true;
	}
 	if ($out == true)
 		echo "<b>" . _("Der Benutzername oder das Passwort f&uuml;r ILIAS ist nicht korrekt.") . "</b><br>";
 	return false;
}

/**
* Stud.IP-user-authentification
*
* This function checks if the given password and username are correct.
*
* @access	public
*/
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

/**
* Asks for Stud.IP-user-authentification
*
* This function echos a question for username and password of a Stud.IP-Account.
*
* @access	public
*/
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("user");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once ('config.inc.php');
include_once ('lib/visual.inc.php');
include_once 'lib/functions.php';
include_once ('lib/msg.inc.php');

if ($ILIAS_CONNECT_ENABLE)
{
	$GLOBALS['ALWAYS_SELECT_DB'] = true;
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_db_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_user_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_view_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_linking_functions.inc.php");

	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

	if (isset($do_open))
		$print_open_admin[$do_open] = true;
	elseif (isset($do_close))
		$print_open_admin[$do_close] = false;
	$sess->register("print_open_admin");

	if (isset($back_x))
		unset($mode);
	$this_ilias_id = get_connected_user_id($auth->auth["uid"]);
	if (($mode == "s2i") AND (($this_ilias_id == false) OR isset($ja_x)))
	{
		if (isset($ja2_x))
		{
			if ((get_studip_user($ilias_id) != false) AND (get_studip_user($ilias_id) != $auth->auth["uname"]))
				die(_("Dieser ILIAS-Account ist noch einem bestehenden Stud.IP-User verbunden und kann daher nicht gel&ouml;scht werden. Bitte wenden Sie sich an den/die AdministratorIn."));
			// Loeschen von ILIAS-Accounts, auch wenn sie nicht mit dem User verbunden sind
			if ($this_ilias_id == false)
				$ilias_id = get_ilias_user_id($username_prefix . $auth->auth["uname"]);
			else
				$ilias_id = $this_ilias_id;
			if (delete_ilias_user( $ilias_id ))
				$deleted_msg = _("Alter Account wurde gel&ouml;scht.");/**/
		}
		$creation_result = create_ilias_user($auth->auth["uid"]);
		$created = true;
	}
	if ( (check_ilias_auth()) AND ($mode == "connect") AND (($this_ilias_id == false) OR isset($ja_x)))
		$connect_result = connect_users($auth->auth["uid"], get_ilias_user_id($ilias_uname));
	$out = true;

	$username = $auth->auth["uname"];
	include ('lib/include/links_about.inc.php');
?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="topic" colspan="3">&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/icon-lern.gif">&nbsp;<b><? echo _("Verbindung der Accounts von ILIAS- und Stud.IP-NutzerInnen");?></b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan="3">&nbsp;
			</td>
		</tr>
		<tr valign="top">
                <td width="1%" class="blank">
                &nbsp;
                </td>
     			<td width="90%" class="blank">
	<form method="POST" action="<? echo $PHP_SELF; ?>">
	<table>
<?
	if (isset($delete))
	{
		my_info(sprintf(_("Wenn Sie fortfahren, wird das Lernmodul mit dem Titel %s unwiderruflich gel&ouml;scht. Soll dieses Lernmodul wirklich gel&ouml;scht werden?"), "<b>" . $del_title . "</b>"));
		?><tr><td><br><center>
		<a href="<? echo link_delete_module($del_inst, $del_id); ?>" target="_blank"><? echo makeButton("ja", "img"); ?>&nbsp;
		<a href="<? echo $PHP_SELF; ?>"><? echo makeButton("nein", "img"); ?></center>
		<?
	}
	elseif (!in_array($mode, array("i2s", "s2i", "connect")))
	{
		$infobox = array	(array ("kategorie"  => _("Information:"),
				"eintrag" => array	(array (	"icon" => "ausruf_small.gif",
										"text"  => sprintf(_("Diese Seite organisiert die Verbindung zwischen BenutzerInnen von ILIAS und Stud.IP. F&uuml;r die Benutzung von Lernmodulen muss je einem Stud.IP-Account ein ILIAS-Account zugeordnet sein."), "<br><i>", "</i>") ) ) ) );
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-posting.gif" ,
										"text"  => _("W&auml;hlen Sie eine Option.") );
		if (($this_ilias_id != false) AND ($perm->have_perm("autor")))
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
										"text"  => sprintf(_("Hier k&ouml;nnen Sie ein %s neues Lernmodul anlegen%s. Das Modul muss anschlie&szlig;end noch zugewiesen werden."), "<a href=\"" . link_new_module() ."\" target=\"_blank\">", "</a>")
									);

		if ($this_ilias_id != false)
			my_info( _("Ihrem Stud.IP-Account ist bereits ein ILIAS-Account zugeordnet. Diese Zuordnung k&ouml;nnen Sie nachtr&auml;glich noch &auml;ndern, dabei gehen allerdings die bereits in ILIAS gespeicherten Daten verloren.") );
		else
			my_info( _("<b>Ihr Stud.IP-Account ist bisher mit keinem ILIAS-Account verkn&uuml;pft</b>. Auf dieser Seite k&ouml;nnen Sie eine Zuordnung der Accounts herstellen. Die Zuordnung k&ouml;nnen Sie nachtr&auml;glich noch &auml;ndern, dabei gehen allerdings die bereits in ILIAS gespeicherten Daten verloren.") );

		echo "<tr><td>";
		if ($this_ilias_id != false)
		{
			show_user_modules($auth->auth["uid"]);
			echo "";
		}
		echo "<br><b>" . _("Sie k&ouml;nnen einen neuen Account anlegen oder einen bestehenden einbinden:") . "</b><br><br>";
//		echo "<input type=\"RADIO\" name=\"mode\" value=\"i2s\">&nbsp;";
//		echo _("Neuen Stud.IP-Account anlegen und einem bestehenden ILIAS-NutzerInnen-Account zuordnen") . "<br><br>";
		echo "<input type=\"RADIO\" name=\"mode\" value=\"s2i\" checked>&nbsp;";
		echo _("F&uuml;r den aktuellen Stud.IP-Account einen passenden ILIAS-Account anlegen. Die Rechte des Stud.IP-Accounts werden analog nach ILIAS &uuml;bertragen.") . "<br><br>";
		echo "<input type=\"RADIO\" name=\"mode\" value=\"connect\">&nbsp;";
		echo _("Den aktuellen Stud.IP-Account mit einem bestehenden ILIAS-Account verbinden. Gesetzte Rechte bleiben dabei bestehen und werden nicht mit synchronisiert.") . "<br><br>";
	}
	else
	{
		echo "<tr><td><input type=\"hidden\" name=\"mode\" value=\"$mode\">&nbsp;";
		$infobox = array	(array ("kategorie"  => _("Information:"),
				"eintrag" => array	(array (	"icon" => "ausruf_small.gif",
										"text"  => sprintf(_("Diese Seite organisiert die Verbindung zwischen BenutzerInnen von ILIAS und Stud.IP."), "<br><i>", "</i>") ) ) ) );
		$auth_mode = true;
		switch($mode)
		{
			case "i2s":
				if (!check_ilias_auth())
				{
					ilias_auth_user();
				}
				echo "</td></tr>";
//					if ($check_result != "")
//						my_error($check_result);
				if ($auth_mode == true)
				{
					if (create_studip_user($ilias_uname))
						my_msg( _("Es wurde ein neuer Stud.IP-Account f&uuml;r Sie angelegt."));
					else
						my_error( _("Beim Anlegen des Accounts ist ein Fehler aufgetreten."));
				}
			break;
			case "s2i":
//				if (!check_studip_auth())
//					studip_auth_user();
				echo "</td></tr>";
				if ($auth_mode == true)
				{
					if ((get_connected_user_id($auth->auth["uid"]) != false) AND !isset($ja_x) AND !$created)
					{
						my_info( _("Ihrem Stud.IP-Account wurde bereits ein ILIAS-Account zugeordnet. Wenn Sie fortfahren, wird diese Zuordnung &uuml;berschrieben und ein neuer Account angelegt. Soll die alte Zuordnung gel&ouml;scht werden?"));
						?>
						<tr><td><br><br><center>
						<input type="IMAGE" <? echo makeButton("ja", "src"); ?> name="ja" value="<? echo _("Ja"); ?>">&nbsp;
						<input type="IMAGE" <? echo makeButton("nein", "src"); ?> name="back" value="<? echo _("Nein"); ?>"></center>
						<?
					}
					else
					{
						if (isset($deleted_msg)) my_msg( $deleted_msg);
						if ($creation_result === true)
							my_msg( _("Es wurde ein neuer ILIAS-Account f&uuml;r Sie angelegt."));
						else
						{
							my_info( "<b>" . $creation_result . "</b>" ._(" Wenn Sie fortfahren, wird dieser Account &uuml;berschrieben und ein neuer Account angelegt. Soll der alte Account gel&ouml;scht werden?"));
							?>
							<tr><td><br><br><center>
							<input type="IMAGE" <? echo makeButton("ja", "src"); ?> name="ja2" value="<? echo _("Ja"); ?>">&nbsp;
							<input type="IMAGE" <? echo makeButton("nein", "src"); ?> name="back" value="<? echo _("Nein"); ?>"></center>
							<input type="hidden" name="ja_x" value="dudelda">
							<?
						}
					}
				}
			break;
			case "connect":
				$infobox[1]["kategorie"] = _("Aktionen:");
					$infobox[1]["eintrag"][] = array (	"icon" => "icon-posting.gif" ,
												"text"  => _("Geben Sie bitte Ihre Logindaten ein.") );
				if (!check_ilias_auth())
					ilias_auth_user();
//				if (!check_studip_auth())
//					studip_auth_user();
				echo "</td></tr>";
				if ($auth_mode == true)
				{
					if (get_ilias_user_id($ilias_uname) == $this_ilias_id)
						my_info( _("Dieser ILIAS-Account ist Ihrem Stud.IP-Account bereits zugeordnet."));
					elseif ((get_connected_user_id($auth->auth["uid"]) != false) AND !isset($ja_x))
					{
						my_info( _("Dem Stud.IP-Account wurde bereits ein ILIAS-Account zugeordnet. Wenn Sie fortfahren, wird diese Zuordnung von ihrer neuen Eingabe &uuml;berschrieben. Soll der alte Eintrag gel&ouml;scht werden?"));
						?>
						<tr><td><br><br><center>
						<input type="IMAGE" <? echo makeButton("ja", "src"); ?> name="ja" value="<? echo _("Ja"); ?>">&nbsp;
						<input type="IMAGE" <? echo makeButton("nein", "src"); ?> name="back" value="<? echo _("Nein"); ?>"></center>
						<?
					}
					elseif ($connect_result)
						my_msg( _("Die Accounts wurden verbunden."));
					else
						my_error( _("Beim Verbinden der Accounts ist ein Fehler aufgetreten."));
				}
			break;
			default: $auth_mode = false;
		}
	}
?>
		<tr><td>
		<br>
		<input type="hidden" name="came_from" value="<? echo $came_from; ?>">
		<input type="hidden" name="came_from_view" value="<? echo $came_from_view; ?>">

		<!--<input type="IMAGE" <? echo makeButton("zurueck", "src"); ?> name="back" value="<? echo _("Zur&uuml;ck"); ?>">-->
		<? if (($auth_mode == false) AND (!isset($delete))) { ?>
		<input type="IMAGE" <? echo makeButton("weiter", "src"); ?> name="next" value="<? echo _("Weiter"); ?>">
		<? }
/*		else
		{
			echo "&nbsp;<a href=\"./index.php\"><b>" . _("Zur&uuml;ck zu Stud.IP") . "</b></a>";
		} /**/
		?>
		<br>
		<br>
		</td></tr></table>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
<?
			if ($came_from == "admin")
				$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
											"text"  => sprintf(_("Hier gelangen Sie %s zur&uuml;ck in den Administrationsbereich %s"), "<a href=\"./admin_lernmodule.php\">", "</a>")
										);
			elseif (($came_from != "") AND ($SessSemName["class"] == "inst"))
				$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
											"text"  => sprintf(_("Hier gelangen Sie %s zur&uuml;ck zur ausgew&auml;hlten Einrichtung %s"), "<a href=\"./seminar_lernmodule.php?seminar_id=$came_from&view=$came_from_view\">", "</a>")
										);
			elseif (($came_from != "") AND ($SessSemName["class"] != "inst"))
				$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
											"text"  => sprintf(_("Hier gelangen Sie %s zur&uuml;ck zur ausgew&auml;hlten Veranstaltung %s"), "<a href=\"./seminar_lernmodule.php?seminar_id=$came_from&view=$came_from_view\">", "</a>")
										);
			print_infobox ($infobox, "lernmodule.jpg");
?>
		</td>
	</tr>
       <tr>
                <td class="blank" colspan="3">&nbsp;
                </td>
        </tr>
	</table>
<?
}
else
{
	// Start of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head
	parse_window ("error§" . _("Das Verbindungsmodul f&uuml;r ILIAS-Lernmodule ist nicht eingebunden. Damit Lernmodule verwendet werden k&ouml;nnen, muss die Verbindung zu einer ILIAS-Installation in den Systemeinstellungen hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "§",
				_("Lernmodule nicht eingebunden"));
}
include ('lib/include/html_end.inc.php');
page_close();
?>