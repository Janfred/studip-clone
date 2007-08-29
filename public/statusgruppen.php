<?
/*
statusgruppen.php - Statusgruppen-Anzeige von Stud.IP.
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

  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));

	include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ('lib/visual.inc.php');
require_once ('lib/statusgruppe.inc.php');
require_once 'lib/functions.php';

$HELP_KEYWORD="Basis.InVeranstaltungGruppen";
$CURRENT_PAGE = $SessSemName["header_line"]. " - " . _("Funktionen / Gruppen");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

checkObject();
checkObjectModule("participants");

include ('lib/include/links_openobject.inc.php');

$cssSw=new cssClassSwitcher;

// Hilfsfunktionen

// groupmail:
// create mailto:-Link fpr
// - groups (filter-argument ignored)
// - seminars (filter=empty or =all: Mail to all accepted participants)
//            (filter=prelim: Mail to all preliminarily accepted partic.)
//            (filter=waiting: Mail to all waiting or claiming partic.)
function groupmail($range_id, $filter="") {
	$type = get_object_type($range_id);
	if ($type == "group") {
		$db=new DB_Seminar;
		$db->query ("SELECT Email FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) WHERE statusgruppe_id = '$range_id'");
		while ($db->next_record()) {
			$mailpersons .= ";".$db->f("Email");
		}
		$mailpersons = substr($mailpersons,1);
		return $mailpersons;
	}
	if ($type == "sem") {
		$db=new DB_Seminar;
		if ($filter=="" || $filter=="all") {
			$db->query ("SELECT Email FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE Seminar_id = '$range_id'");
		} else if ($filter=="prelim") {
			$db->query ("SELECT Email FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = '$range_id' AND status='accepted'");

		} else if ($filter=="waiting") {
			$db->query ("SELECT Email FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = '$range_id' AND (status='awaiting' OR status='claiming')");
		} else {
			echo "<p>ERROR: unknown filter: $filter</p>";
		}
		while ($db->next_record()) {
			$mailpersons .= ";".$db->f("Email");
		}
		$mailpersons = substr($mailpersons,1);
		return $mailpersons;
	}
}


function PrintAktualStatusgruppen () {
	global $_fullname_sql,$SessSemName, $PHP_SELF, $rechte, $user;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id, size, selfassign FROM statusgruppen WHERE range_id = '$SessSemName[1]' ORDER BY position ASC");
	$AnzahlStatusgruppen = $db->num_rows();
	$i = 0;
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		$size = $db->f("size");
		$groupmails = groupmail($statusgruppe_id);
		echo "<table width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" border=\"0\"><tr>";
		printf ("<td width=\"90%%\" class=\"topic\"><font size=\"-1\"><b>&nbsp;%s &nbsp;%s</b></font>",
		CheckAssignRights($statusgruppe_id,$user->id,$SessSemName[1])?"<a href=\"$PHP_SELF?assign=$statusgruppe_id\"><img src=\"".$GLOBALS['ASSETS_URL']."images/move.gif\" border=\"0\"". tooltip(_("In diese Gruppe eintragen"))."></a>":"",
		htmlReady($db->f("name")));

		$limit = GetStatusgruppeLimit($statusgruppe_id);
		if ($limit!=FALSE && $db->f("selfassign") > 0) {
			$voll = CountMembersPerStatusgruppe ($statusgruppe_id);
			if ($voll >= $limit)
				$limitcolor = "#FF5555";
			else
				$limitcolor = "55FF55";
			echo "<font size = \"2\" color=$limitcolor>&nbsp;&nbsp;-&nbsp;&nbsp;";
			printf ("%s von %s Plätzen belegt",$voll, $limit);
			echo "&nbsp;</font>";
		}
		printf ("</td><td width=\"10%%\"class=\"topic\" valign=\"middle\" align=\"right\" nowrap>");

		if ((CheckUserStatusgruppe($statusgruppe_id, $user->id) || $rechte) && ($folder_id = CheckStatusgruppeFolder($statusgruppe_id)) ){
			echo "<a href=\"folder.php?cmd=tree&open=$folder_id#anker\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/icon-disc.gif\" ".tooltip(_("Dateiordner vorhanden"))."></a>&nbsp;";
		}

		if ($rechte || CheckUserStatusgruppe($statusgruppe_id, $user->id)) {  // nicht alle duerfen Gruppenmails/Gruppensms verschicken
			printf ("&nbsp;<a href=\"sms_send.php?sms_source_page=statusgruppen.php&group_id=%s&emailrequest=1&subject=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/mailnachricht.gif\" " . tooltip(_("Systemnachricht mit Emailweiterleitung an alle Gruppenmitglieder verschicken")) . " border=\"0\"></a>&nbsp;", $statusgruppe_id, rawurlencode($SessSemName[0]));
			printf ("&nbsp;<a href=\"sms_send.php?sms_source_page=statusgruppen.php&group_id=%s&subject=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" " . tooltip(_("Systemnachricht an alle Gruppenmitglieder verschicken")) . " border=\"0\"></a>&nbsp;", $statusgruppe_id, rawurlencode($SessSemName[0]));
		} else {
			echo "&nbsp;";
		}
		printf ("</td>");
		echo 	"</tr>";

		if (!$rechte) {
			$db2->query("SELECT user_id, visible FROM seminar_user WHERE Seminar_id = '".$SessSemName[1]."'");
			while ($db2->next_record()) {
				$visio[$db2->f('user_id')] = ($db2->f('visible') == 'yes') ? true : false;
			}
			$db2->query("SELECT user_id, visible FROM admission_seminar_user WHERE seminar_id = '".$SessSemName[1]."'");
			while ($db2->next_record()) {
				$visio[$db2->f('user_id')] = ($db2->f('visible') == 'yes') ? true : false;
			}
		}

		$db2->query ("SELECT statusgruppe_user.user_id, " . $_fullname_sql['full'] ." AS fullname, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE statusgruppe_id = '$statusgruppe_id' ORDER BY position ASC");
		$k = 1;
		while ($db2->next_record()) {
			if ($k % 2) {
				$class="steel1";
			} else {
				$class="steelgraulight";
			}
			echo '<tr>';
			echo '<td width="90%" class="'.$class.'">';
			if ($visio[$db2->f('user_id')] || ($db2->f('user_id') == $user->id) || $rechte) {
				printf("<font size=\"-1\"><a href = \"about.php?username=%s\">&nbsp;%s</a>", $db2->f("username"), htmlReady($db2->f("fullname")));
				if  (($db2->f('user_id') == $user->id) && !($visio[$db2->f('user_id')]) && !$rechte) {
					echo ' ' . _("(unsichtbar)");
				}
				echo '</font>';
			} else {
				echo '<font size="-1" color="#666666">'. _("(unsichtbareR NutzerIn)"). '</font>';
			}

			echo '</td>';
			printf ("<td width=\"10%%\"class=\"$class\" align=\"right\">");
			if (CheckSelfAssign($statusgruppe_id) && $user->id == $db2->f("user_id"))
				printf ("<a href=\"$PHP_SELF?delete_id=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" " . tooltip(_("Aus dieser Gruppe austragen")) . " border=\"0\"></a>&nbsp; ", $statusgruppe_id);
			if (($visio[$db2->f('user_id')] || $rechte) && ($db2->f('user_id') != $user->id))
				printf ("<a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" " . tooltip(_("Systemnachricht an User verschicken")) . " border=\"0\"></a>", $db2->f("username"));
			printf ("&nbsp;</td>");
			echo "</tr>";
			$k++;
		}
		$i++;
		echo "</table><br><br>";
	}
}

function PrintNonMembers ($range_id)
{
	global $_fullname_sql, $rechte, $user, $PHP_SELF;
	$bereitszugeordnet = GetAllSelected($range_id);
	$db=new DB_Seminar;
	$query = "SELECT seminar_user.user_id, username, " . $_fullname_sql['full'] ." AS fullname, perms, seminar_user.visible FROM seminar_user  LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE Seminar_id = '$range_id' ORDER BY Nachname ASC";
	$db->query ($query);
	if ($db->num_rows() >sizeof($bereitszugeordnet)-1) { // there are non-grouped members
		echo "<table width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" border=\"0\"><tr>";
		print ("<td width=\"100%%\" colspan=\"2\" class=\"steel\"><font size=\"-1\"><b>&nbsp;" . _("keiner Funktion oder Gruppe zugeordnet") . "</b></font> <img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=\"25\"></td>");
		echo 	"</tr>";
		$k = 1;
		while ($db->next_record()) {
			if (!in_array($db->f("user_id"), $bereitszugeordnet)) {
				if ($k % 2) {
					$class="steel1";
				} else {
					$class="steelgraulight";
				}
				printf ("<tr>");
				if ($rechte || $db->f("visible")=="yes" || $db->f("user_id")==$user->id) {
					printf ("<td width=\"90%%\" class=\"%s\"><font size=\"-1\"><a href = about.php?username=%s>&nbsp;%s</a>%s</font></td>",$class, $db->f("username"), htmlReady($db->f("fullname")), ($db->f("user_id")==$user->id && $db->f("visible")!="yes") ? " "._("(unsichtbar)") : '');
					printf ("<td width=\"10%%\"class=\"$class\" align=\"right\">");
					printf ("<a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" " . tooltip(_("Systemnachricht an User verschicken")) . " border=\"0\"></a>", $db->f("username"));
					printf ("&nbsp;</td>");
				} else {
					printf ("<td width=\"90%%\" class=\"%s\"><font size=\"-1\" color=\"#666666\">". _("(unsichtbareR NutzerIn)"). "</font></td>", $class);
					printf ("<td width=\"10%%\"class=\"$class\" align=\"right\">");
					printf ("&nbsp;</td>");
				}
				echo "	</tr>";
				$k++;
			}
		}
	echo "</table><br><br>";
	}
	if ($k > 1) {
		$Memberstatus = 1;
	} else {
		$Memberstatus = 2;
	}
	if (sizeof($bereitszugeordnet) < 2) {
		$Memberstatus = 0;
	}
	return $Memberstatus;
}

// Command-Parsing

if ($assign)
	if (GetRangeOfStatusgruppe($assign)==$SessSemName[1] && CheckAssignRights($assign, $user->id, $SessSemName[1]))
		InsertPersonStatusgruppe($user->id, $assign);

if ($delete_id)
	if (GetRangeOfStatusgruppe($delete_id)==$SessSemName[1] && CheckUserStatusgruppe($delete_id, $user->id))
		RemovePersonStatusgruppe($user->username, $delete_id);

// Beginn Darstellungsteil

?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="blank" valign="top"><br>
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<?
	if ($sms_msg){
		parse_msg ($sms_msg);
		$sms_msg = '';
		$sess->unregister('sms_msg');
	}
	?>
	<tr valign="top">
     <td width="90%" class="blank">
			<?
			PrintAktualStatusgruppen();
			$anzahltext = PrintNonMembers($SessSemName[1]);

			if ($anzahltext == 1) {
				$Memberstatus = _("Nicht alle Personen sind einer Funktion / Gruppe zugeordnet.");
			}
			if ($anzahltext == 2) {
				$Memberstatus = _("Alle Personen sind mindestens einer Funktion / Gruppe zugeordnet.");
			}
			if ($anzahltext == 0) {
				$Memberstatus = _("Niemand ist einer Funktion / Gruppe zugeordnet.");
			}

			if (($EXPORT_ENABLE) AND ($perm->have_studip_perm("tutor", $SessSemName[1])))
			{
				include_once($PATH_EXPORT . "/export_linking_func.inc.php");
				echo "<br><b>&nbsp;<font size=\"-1\">" . export_link($SessSemName[1], "person", $SessSemName[0], "rtf", "rtf-gruppen", "status") . "</font></b>";
			}
			?>
		</td>
	</tr>
	</table>
	</td>
	<td width="270" class="blank" align="center" valign="top">

	<?
	list($self_assign_all, $self_assign_exclusive) = CheckSelfAssignAll($SessSemName[1]);

	$infobox = array	(
		array  ("kategorie"  => _("Information:"),
			"eintrag" => array	(
				array (	"icon" => "ausruf_small.gif",
											"text"  => $Memberstatus
				)
			)
		)
	);
	if($self_assign_exclusive){
		$infobox[0]["eintrag"][] = array ("icon" => "ausruf_small.gif" ,
									"text"  => _("In dieser Veranstaltung können Sie sich nur in eine der möglichen Gruppen eintragen.")
									);

	}

	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "nachricht1.gif" ,
									"text"  => _("Um Personen eine systeminterne Kurznachricht zu senden, benutzen Sie bitte das normale Briefsymbol.")
								);
		$infobox[1]["eintrag"][] = array (	"icon" => "move.gif" ,
									"text"  => _("In Gruppen mit diesem Symbol können Sie sich selbst eintragen. Klicken Sie auf das jeweilige Symbol um sich einzutragen.")
								);
		$infobox[1]["eintrag"][] = array (	"icon" => "trash.gif" ,
									"text"  => _("Aus diesen Gruppen können Sie sich selbst austragen.")
								);
	if ($rechte) {
		$adr_all=groupmail($SessSemName[1], "all");
		$adr_prelim=groupmail($SessSemName[1], "prelim");
		$adr_waiting=groupmail($SessSemName[1], "waiting");
		$link_mail_all = $adr_all ? "<a href=\"sms_send.php?course_id=".$SessSemName[1]."&emailrequest=1&subject=".rawurlencode($SessSemName[0])."&filter=all\">" : NULL;
		$link_mail_prelim = $adr_prelim ? "<a href=\"sms_send.php?course_id=".$SessSemName[1]."&emailrequest=1&subject=".rawurlencode($SessSemName[0])."&filter=prelim\">" : NULL;
		$link_mail_waiting = $adr_waiting ? "<a href=\"sms_send.php?course_id=".$SessSemName[1]."&emailrequest=1&subject=".rawurlencode($SessSemName[0])."&filter=waiting\">" : NULL;
		#$link_mail_all = $adr_all ? "<a href=\"mailto:".$adr_all."?subject=".rawurlencode($SessSemName[0])."\">" : NULL;
		#$link_mail_prelim = $adr_prelim ?  "<a href=\"mailto:".$adr_prelim."?subject=".rawurlencode($SessSemName[0])."\">" : NULL;
		#$link_mail_waiting = $adr_waiting ? "<a href=\"mailto:".$adr_waiting."?subject=".rawurlencode($SessSemName[0])."\">" : NULL;
		$infobox[1]["eintrag"][] = array (	"icon" => "einst.gif",
								"text"  => sprintf(_("Um Gruppen anzulegen und ihnen Personen zuzuordnen nutzen Sie %sFunktionen / Gruppen verwalten%s."), "<a href=\"admin_statusgruppe.php?view=statusgruppe_sem&new_sem=TRUE&range_id=$SessSemName[1]\">", "</a>")
								);
		if ($anzahltext > 0) {
			$infobox[1]["eintrag"][] = array (	"icon" => "mailnachricht.gif" ,
									"text"  => _("Mit dem erweiterten Briefsymbol können Sie eine E-Mail an alle Gruppenmitglieder verschicken.")
								);
		}
		if ($link_mail_all) {
			$infobox[1]["eintrag"][] = array (	"icon" => "ausruf_small.gif" ,
									"text"  => sprintf(_("Um eine E-Mail an alle TeilnehmerInnen der Veranstaltung zu versenden, klicken Sie %shier%s."), $link_mail_all, "</a>")
								);
		}
		if ($link_mail_waiting) {
			$infobox[1]["eintrag"][] = array (	"icon" => "ausruf_small.gif" ,
									"text"  => sprintf(_("Um eine E-Mail an alle TeilnehmerInnen auf der Warteliste zu versenden, klicken Sie %shier%s."), $link_mail_waiting, "</a>")
								);
		}
		if ($link_mail_prelim) {
			$infobox[1]["eintrag"][] = array (	"icon" => "ausruf_small.gif" ,
									"text"  => sprintf(_("Um eine E-Mail an alle vorläufig akzeptierten TeilnehmerInnen zu versenden, klicken Sie %shier%s."), $link_mail_prelim, "</a>")
								);
		}
	}

	print_infobox ($infobox,"groups.jpg");

	?>
	</td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
</table>
<p>
<?php

// Ende Darstellungsteil
include ('lib/include/html_end.inc.php');
page_close();
?>
