<?php
/*
teilnehmer.php - Anzeige der Teilnehmer eines Seminares
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

include "$ABSOLUTE_PATH_STUDIP/seminar_open.php"; //hier werden die sessions initialisiert

require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/admission.inc.php");	//Funktionen der Teilnehmerbegrenzung
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");	//Funktionen der Statusgruppen
require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");	//Funktionen des Nachrichtensystems
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");		//We need the config for some parameters of the class of the Veranstaltung
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php";
}

// Start  of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   //hier wird der "Kopf" nachgeladen

checkObject();
checkObjectModule("participants");

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");

$messaging=new messaging;
$cssSw=new cssClassSwitcher;

if ($sms_msg)
	$msg=rawurldecode($sms_msg);

// Aenderungen nur in dem Seminar, in dem ich gerade bin...
	$id=$SessSemName[1];

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$db5=new DB_Seminar;

echo "<table cellspacing=\"0\" border=\"0\" width=\"100%\">";

/*
 * This function checks if a the given user has to be shown (is in the array
 * of downpulled users)
 *
 * @param  user_id integer
 *
 * returns boolean
 *
 */
function is_opened($user_id) {
	global $open_users;

	if (!isset($open_users)) return FALSE;
	if (array_search($user_id, $open_users) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

if (($cmd == "change_view") && (isset($view_order))) {
	if (!isset($indikator)) {
		$sess->register("indikator");
	}
	$indikator = $view_order;
}

// get user_id if somebody wants more infos about an user
if (($cmd == "allinfos") && ($rechte)) {
	if (isset($$area)) {
		unset($$area);
		$sess->unregister($area);
	} else {
		$sess->register($area);
		$$area = TRUE;
	}
}

if ((($cmd == "moreinfos") || ($cmd == "lessinfos")) && ($rechte)) {
	$db->query("SELECT user_id FROM auth_user_md5 WHERE username = '$username'");
	$db->next_record();
	$user_id = $db->f("user_id");
	if (!isset($open_users)) {
		$sess->register("open_users");
		$open_users = array();
	}
	if (($z = array_search($user_id, $open_users)) === FALSE) {
		$open_users[] = $user_id;
	} else {
		unset($open_users[$z]);
	}
	sort ($open_users);
}

// edit special seminar_info of an user

if ($cmd == "change_userinfo") {
	//first we have to check if he is really "Dozent" of this seminar
	if ($rechte) {
		$db->query("UPDATE admission_seminar_user SET comment = '$userinfo' WHERE seminar_id = '$id' AND user_id = '$user_id'");
		$db->query("UPDATE seminar_user SET comment = '$userinfo' WHERE Seminar_id = '$id' AND user_id = '$user_id'");
		$msg = "msg§" . _("Die Zusatzinformationen wurden ge&auml;ndert.") . "§";
	}
	$cmd = "moreinfos";
}

// Aktivitaetsanzeige an_aus

if ($cmd=="showscore") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("UPDATE seminare SET showscore = '1' WHERE Seminar_id = '$id'");
		$msg = "msg§" . _("Die Aktivit&auml;tsanzeige wurde aktiviert.") . "§";
	}
}

if ($cmd=="hidescore") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("UPDATE seminare SET showscore = '0' WHERE Seminar_id = '$id'");
		$msg = "msg§" . _("Die Aktivit&auml;tsanzeige wurde deaktiviert.") . "§";
	}
}

// Hier will jemand die Karriereleiter rauf...

if ($cmd=="pleasure") {
	//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere nicht zu Tutoren befoerdern!
	if ($rechte AND $SemUserStatus!="tutor")  {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username' AND perms!='user' AND perms!='autor'");
		if ($db->next_record()) {
			$userchange=$db->f("user_id");
			$fullname = $db->f("fullname");
			$db->query("UPDATE seminar_user SET status='tutor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
			$msg = "msg§" . sprintf(_("Bef&ouml;rderung von %s durchgef&uuml;hrt"), $fullname) . "§";
		}
		else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

// jemand ist der anspruchsvollen Aufgabe eines Tutors nicht gerecht geworden...

if ($cmd=="pain") {
	//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere Tutoren nicht rauskicken!
	if ($rechte AND $SemUserStatus!="tutor") {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$fullname = $db->f("fullname");
		$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
		if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
			$msg = "msg§" . sprintf (_("Das Mitglied %s wurde entlassen und auf den Status 'Autor' zur&uuml;ckgestuft."), $fullname) . "§";
		} else {
			$msg = "msg§" . sprintf (_("Der/die TutorIn %s wurde entlassen und auf den Status 'Autor' zur&uuml;ckgestuft."), $fullname) . "§";
		}
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

// jemand ist zu bloede, sein Seminar selbst zu abbonieren...

if ($cmd=="schreiben") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username' AND perms != 'user'");
		if ($db->next_record()) {
			$userchange=$db->f("user_id");
			$fullname = $db->f("fullname");
			$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
			$msg = "msg§" . sprintf(_("User %s wurde als Autor in die Veranstaltung aufgenommen."), $fullname) . "§";
		}
		else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

// jemand sollte erst mal das Maul halten...

if ($cmd=="lesen") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$fullname = $db->f("fullname");
		$db->query("UPDATE seminar_user SET status='user' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
		$msg = "msg§" . sprintf(_("Der/die AutorIn %s wurde auf den Status 'Leser' zur&uuml;ckgestuft."), $fullname) . "§";
		$msg.= "info§" . _("Um jemanden permanent am Schreiben zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Schreiben nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.") . "<br>\n"
				. _("Dann k&ouml;nnen sich weitere BenutzerInnen nur noch mit Kenntnis des Veranstaltungs-Passworts als 'Autor' anmelden.") . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

// und tschuess...

if ($cmd=="raus") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$fullname = $db->f("fullname");
		$db->query("DELETE FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$userchange'");

		setTempLanguage($userchange);
		if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
			$message= sprintf(_("Ihr Abonnement der Veranstaltung **%s** wurde von einem/r LeiterIn oder AdministratorIn aufgehoben."), $SessSemName[0]);
		} else {
			$message= sprintf(_("Ihr Abonnement der Veranstaltung **%s** wurde von einem/r DozentIn oder AdministratorIn aufgehoben."), $SessSemName[0]);
		}
		restoreLanguage();

		$messaging->insert_message($message, $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")."&nbsp;"._("Abonnement aufgehoben"), TRUE);

		// raus aus allen Statusgruppen
		RemovePersonStatusgruppeComplete ($username, $id);

		//Pruefen, ob es Nachruecker gibt
		update_admission($id);

		$msg = "msg§" . sprintf(_("LeserIn %s wurde aus der Veranstaltung entfernt."), $fullname) . "§";
		$msg.= "info§" . _("Um jemanden permanent am Lesen zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Lesen nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.") . "<br>\n"
				. _("Dann k&ouml;nnen sich weitere BenutzerInnen nur noch mit Kenntnis des Veranstaltungs-Passworts anmelden.") . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

//aus der Anmelde- oder Warteliste entfernen
if ($cmd=="admission_raus") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$fullname = $db->f("fullname");
		$db->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$userchange'");

		setTempLanguage($userchange);
		if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
			if (!$accepted) {
				$message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn von der Warteliste der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
			} else {
				 $message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn aus der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
			}
		} else {
			if (!$accepted) {
				$message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn von der Warteliste der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
			} else {
				 $message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn aus der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
			}
		}
		restoreLanguage();

		$messaging->insert_message($message, $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")."&nbsp;"._("nicht zugelassen in Veranstaltung"), TRUE);

		//Warteliste neu sortieren
		renumber_admission($id);
		if ($accepted)
			update_admission($id);

		$msg = "msg§" . sprintf(_("LeserIn %s wurde aus der Anmelde bzw. Warteliste entfernt."), $fullname) . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

//aus der Anmelde- oder Warteliste in die Veranstaltung hochstufen / aus der freien Suche als Tutoren oder Autoren eintragen
if ((($cmd=="admission_rein") || ($cmd=="add_user")) && ($username)){
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {

		$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$fullname = $db->f("fullname");

		$status = (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"] && (($db->f("perms") == "tutor" || $db->f("perms") == "dozent")) && ($perm->have_studip_perm("dozent", $id))) ? "tutor" : "autor";

		if ($cmd == "add_user") $status="autor"; // otherwise, students with GLOBAL status tutor immediately have the status of a tutor in this seminar. Makes no sense!
		//But: perhaps a better solution?

		$admission_user = insert_seminar_user($id, $userchange, $status, ($accepted) ? TRUE : FALSE);
		//Only if user was on the waiting list
		if ($admission_user) {
			setTempLanguage($userchange);
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				if (!$accepted) {
					$message = sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn aus der Warteliste in die Veranstaltung **%s** aufgenommen und sind damit zugelassen."), $SessSemName[0]);
				} else {
					$message = sprintf(_("Sie wurden von einem/r LeiterIn oder AdministratorIn zum/r TeilnehmerIn der Veranstaltung **%s** hochgestuft und sind damit zugelassen."), $SessSemName[0]);
				}
			} else {
				if (!$accepted) {
					$message = sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn aus der Warteliste in die Veranstaltung **%s** aufgenommen und sind damit zugelassen."), $SessSemName[0]);
				} else {
				 	$message = sprintf(_("Sie wurden von einem/r DozentIn oder AdministratorIn vom Status **vorläufig akzeptiert** zum/r TeilnehmerIn der Veranstaltung **%s** hochgestuft und sind damit zugelassen."), $SessSemName[0]);
				}
			}
			restoreLanguage();
			$messaging->insert_message($message, $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")."&nbsp;"._("Eintragung in Veranstaltung"), TRUE);
		}

		//Warteliste neu sortieren
		renumber_admission($id);

		if ($cmd=="add_user")
			$msg = "msg§" . sprintf(_("NutzerIn %s wurde in die Veranstaltung mit dem Status <b>%s</b> eingetragen."), $fullname, $status) . "§";
		else
			if (!$accepted) {
				$msg = "msg§" . sprintf(_("NutzerIn %s wurde aus der Anmelde bzw. Warteliste mit dem Status <b>%s</b> in die Veranstaltung eingetragen."), $fullname, $status) . "§";
			} else {
				$msg = "msg§" . sprintf(_("NutzerIn %s wurde mit dem Status <b>%s</b> endgültig akzeptiert und damit in die Veranstaltung aufgenommen."), $fullname, $status) . "§";
			}
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}


// so bin auch ich berufen?

if (isset($add_tutor_x)) {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte AND $SemUserStatus!="tutor") {
				// nur wenn wer ausgewaehlt wurde
		if ($u_id != "0") {
			$query = "SELECT DISTINCT b.user_id, username, Vorname, Nachname, inst_perms, perms FROM seminar_inst d LEFT JOIN user_inst a USING(Institut_id) ".
			"LEFT JOIN auth_user_md5  b USING(user_id) ".
			"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$SessSemName[1]')  ".
			"WHERE d.seminar_id = '$SessSemName[1]' AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) ORDER BY Nachname";
			$db->query($query);
				// wer versucht denn da wen nicht zugelassenen zu berufen?
			if ($db->next_record()) {
				// so, Berufung ist zulaessig
				$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$u_id'");
				if ($db2->next_record()) {
					// der Dozent hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Seminar. Na, auch egal...
					if ($db2->f("status") == "autor" || $db2->f("status") == "user") {
						// gehen wir ihn halt hier hochstufen
						$db2->query("UPDATE seminar_user SET status='tutor' WHERE Seminar_id = '$id' AND user_id = '$u_id'");
						if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
							$msg = "msg§" . sprintf (_("%s wurde zum Mitglied bef&ouml;rdert."), get_fullname($u_id)) . "§";
						} else {
							$msg = "msg§" . sprintf (_("%s wurde auf den Status 'Tutor' bef&ouml;rdert."), get_fullname($u_id)) . "§";
						}
						//kill from waiting user
						$db2->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$u_id'");
						//reordner waiting list
						renumber_admission($id);
					} else {
						;	// na, das ist ja voellig witzlos, da tun wir einfach nix.
							// Nicht das sich noch ein Dozent auf die Art und Weise selber degradiert!
					}
				} else {  // ok, einfach aufnehmen.
					insert_seminar_user($id, $u_id, "tutor", FALSE);

					if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
						$msg = "msg§" . sprintf (_("%s wurde als Mitglied in die Veranstaltung aufgenommen."), get_fullname($u_id));
					} else {
						$msg = "msg§" . sprintf (_("%s wurde als Tutor in die Veranstaltung aufgenommen."), get_fullname($u_id));
					}

					setTempLanguage($userchange);
					if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
						$message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn in die Veranstaltung **%s** aufgenommen."), $SessSemName[0]);
					} else {
						$message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn in die Veranstaltung **%s** aufgenommen."), $SessSemName[0]);
					}
					restoreLanguage();
					$messaging->insert_message($message, get_username($u_id), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")."&nbsp;"._("Eintragung in Veranstaltung"), TRUE);
				}
			}
			else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
		}
		else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
	}
	else $msg ="error§" . _("Netter Versuch! vielleicht beim n&auml;chsten Mal!") . "§";
}

//Alle fuer das Losen anstehenden Veranstaltungen bearbeiten (wenn keine anstehen wird hier nahezu keine Performance verbraten!)
check_admission();


if ($perm->have_perm("dozent")) {
	if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"])
		$gruppe = array ("dozent" => _("DozentInnen"),
					  "tutor" => _("TutorInnen"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"),
					  "accepted" => _("Vorl&auml;ufig akzeptierte TeilnehmerInnen"));
	else
		$gruppe = array ("dozent" => _("LeiterInnen"),
					  "tutor" => _("Mitglieder"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"),
					  "accepted" => _("Vorl&auml;ufig akzeptierte TeilnehmerInnen"));
} else {
	if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"])
		$gruppe = array ("dozent" => _("DozentInnen"),
					  "tutor" => _("TutorInnen"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"));
	else
		$gruppe = array ("dozent" => _("LeiterInnen"),
					  "tutor" => _("Mitglieder"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"));
}

?>

<tr>
		<td class="topic" ><b>&nbsp;<? echo $SessSemName["header_line"] . " - " . _("TeilnehmerInnen"); ?></b>
		</td>
		<td align="right" class="topic"> <?

			$db3->query ("SELECT showscore  FROM seminare WHERE Seminar_id = '$SessionSeminar'");
			while ($db3->next_record()) {
				if ($db3->f("showscore") == 1) {
					if ($rechte) {
						printf ("<a href=\"$PHP_SELF?cmd=hidescore\"><img src=\"pictures/showscore1.gif\" border=\"0\" %s>&nbsp; &nbsp; </a>", tooltip(_("Aktivitätsanzeige eingeschaltet. Klicken zum Ausschalten.")));
					} else {
						echo "&nbsp; ";
					}
					$showscore = TRUE;
				} else {
					if ($rechte) {
						printf ("<a href=\"$PHP_SELF?cmd=showscore\"><img src=\"pictures/showscore0.gif\" border=\"0\" %s>&nbsp; &nbsp; </a>", tooltip(_("Aktivitätsanzeige ausgeschaltet. Klicken zum Einschalten.")));
					} else {
						echo "&nbsp; ";
					}
					$showscore = FALSE;
				}
			}
		?>
		</td>
	</tr>

	<? if ($rechte) { ?>

	<tr>
		<td class="blank" colspan="10" align="left">
			<form name="sortierung" method="post" action="<?=$PHP_SELF?>">
    		<table class="blank" border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td class="blank">&nbsp;</td>
					</tr>
					<tr>
      			<td class="steelkante2" valign="middle">
							<img src="pictures/blank.gif" height="22" width="5">
						</td>
      			<td class="steelkante2" valign="middle">
							<font size="-1"><?=_("Sortierung:")?>&nbsp;</font>
						<? if (isset($indikator) && ($indikator == "abc")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="pictures/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Alphabetisch")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=abc&cmd=change_view">
								<img src="pictures/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Alphabetisch")?></font>
							</a>
							&nbsp;
						<? } ?>
						<? if (isset($indikator) && ($indikator == "date")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="pictures/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Anmeldedatum")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=date&cmd=change_view">
								<img src="pictures/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Anmeldedatum")?></font>
							</a>
							&nbsp;
						<? } ?>
						<? if (isset($indikator) && ($indikator == "active")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="pictures/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Aktivität")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=active&cmd=change_view">
								<img src="pictures/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Aktivität")?></font>
							</a>
							&nbsp;
						<? } ?>
						</td>
						<td><img src="pictures/balken.jpg"></td>
					<tr>
				</table>
			</form>
		</td>
	</tr>

	<? } ?>

	<tr>
		<td class="blank" width="100%" colspan="2">&nbsp;
			<?
			if ($msg) parse_msg($msg);
			?>
		</td>
	</tr>
<tr>
	<td class="blank" colspan="2">

	<table width="99%" border="0"  cellpadding="2" cellspacing="0" align="center">

<?
//Index berechnen
$db3->query ("SELECT count(dokument_id) AS count_doc FROM dokumente WHERE seminar_id = '$SessionSeminar'");
if ($db3->next_record()) {
	$aktivity_index_seminar = $db3->f("count_doc") * 5;
}
$db3->query ("SELECT count(topic_id) AS count_post FROM px_topics WHERE Seminar_id = '$SessionSeminar'");
if ($db3->next_record()) {
	$aktivity_index_seminar += $db3->f("count_post");
}
$db3->query ("SELECT count(user_id) AS count_pers FROM seminar_user WHERE Seminar_id = '$SessionSeminar'");
if ($db3->next_record() && $db3->f("count_pers")) {
	$aktivity_index_seminar /= $db3->f("count_pers");
}

//Veranstaltungsdaten holen
$db3->query ("SELECT admission_type, admission_selection_take_place FROM seminare WHERE Seminar_id = '$SessionSeminar'");
$db3->next_record();

while (list ($key, $val) = each ($gruppe)) {

	if (!isset($sortby) || $sortby == "") {
		switch($indikator) {
			case "date":
				$sortby .= "mkdate";
				break;

			case "active":
				$sortby = "doll DESC";
				break;

			default:
				$sortby .= "Nachname";
				break;
		}
	}

	if (isset($sortby)) {
		$sort = "ORDER BY $sortby";
	} else {
		$sort = "";
	}

	$counter=1;

	if ($key == "accepted") {  // modify query if user is in admission_seminar_user and not in seminar_user
		$tbl = "admission_seminar_user";
		$tbl2 = "";
		$tbl3 = "s";
	} else {
		$tbl = "seminar_user";
		$tbl2 = "admission_";
		$tbl3 = "S";
	}

	$db->query ("SELECT $tbl.mkdate, comment, $tbl.user_id, ". $_fullname_sql['full'] ." AS fullname, username, status, count(topic_id) AS doll,  studiengaenge.name, ".$tbl.".".$tbl2."studiengang_id AS studiengang_id FROM $tbl LEFT JOIN px_topics USING (user_id,".$tbl3."eminar_id) LEFT JOIN auth_user_md5 ON (".$tbl.".user_id=auth_user_md5.user_id) LEFT JOIN user_info USING (user_id) LEFT JOIN studiengaenge ON (".$tbl.".".$tbl2."studiengang_id = studiengaenge.studiengang_id) WHERE ".$tbl.".".$tbl3."eminar_id = '$SessionSeminar' AND status = '$key'  GROUP by ".$tbl.".user_id $sort");

	if ($db->num_rows()) { //Only if Users were found...
	// die eigentliche Teil-Tabelle

	echo "<tr height=28>";
	if ($showscore==TRUE)
		echo "<td class=\"steel\" width=\"1%\">&nbsp; </td>";
	print "<td class=\"steel\" width=\"1%\" align=\"center\" valign=\"middle\">";
	if ($rechte) {
		$show_area = "show_".$key;
		if (isset($$show_area)) {
			$image = "forumgraurunt.gif";
			$tooltiptxt = _("Informationsfelder wieder hochklappen");
		} else {
			$image = "forumgrau.gif";
			$tooltiptxt = _("Alle Informationsfelder aufklappen");
		}
		print "<a href=\"$PHP_SELF?cmd=allinfos&area=show_$key\">";
		print "<img src=\"pictures/$image\" border=\"0\" ".tooltip($tooltiptxt)."></a>";
	} else {
		print "&nbsp; ";
	}
	print "</td>";
	printf("<td class=\"steel\" width=\"29%%\" align=\"left\"><img src=\"pictures/blank.gif\" width=\"1\" height=\"20\"><font size=\"-1\"><b>%s</b></font></td>", $val);
	if ($key != "dozent" && $rechte) {
		printf("<td class=\"steel\" width=\"1%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Anmeldedatum"));
	} else if ($key == "dozent" && $rechte) {
		printf("<td class=\"steel\" width=\"9%%\" align=\"center\" valign=\"bottom\">&nbsp;</td>");
	}
	printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Postings"));
	printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Dokumente"));
	printf("<td class=\"steel\" width=\"9%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Nachricht"));


	if ($rechte) {

		if ($db3->f("admission_type"))
			$width=15;
		else
			$width=20;

		if ($key == "dozent") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><b>&nbsp;</b></td>", $width);
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><b>&nbsp;</b></td>", $width);
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=\"center\" colspan=\"2\"><b>&nbsp;</b></td>";
		}

		if ($key == "tutor") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><font size=\"-1\"><b>&nbsp;</b></font></td>", $width);
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("Mitglied entlassen"));
			} else {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width,  _("TutorIn entlassen"));
			}
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";
		}

		if ($key == "autor") {
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>",  $width, _("als Mitglied eintragen"));
			} else {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>",  $width, _("als TutorIn eintragen"));
			}
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("Schreibrecht entziehen"));
			if ($db3->f("admission_type"))
				printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Kontingent"));
		}

		if ($key == "user") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("Schreibrecht erteilen"));
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("BenutzerIn entfernen"));
			if ($db3->f("admission_type"))
				print"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";
		}

		if ($key == "accepted") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("Akzeptieren"));
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", $width, _("BenutzerIn entfernen"));
			if ($db3->f("admission_type"))
				print"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";

		}
	}

	echo "</tr>";
	$c=1;
	while ($db->next_record()) {

	if ($c % 2) {   // switcher fuer die Klassen
		$class="steel1";
		$class2="colorline";
	} else {
		$class="steelgraulight";
		$class2="colorline2";
	}

//  Elemente holen

	$Dokumente = 0;
	$UID = $db->f("user_id");
	$db2->query ("SELECT count(dokument_id) AS doll FROM dokumente WHERE seminar_id = '$SessionSeminar' AND user_id = '$UID' GROUP by seminar_id");
	while ($db2->next_record()) {
		$Dokumente = $db2->f("doll");
	}
	$postings_user = $db->f("doll");

// Aktivitaet berechnen

	if ($showscore == TRUE) {
		$aktivity_index_user =  (($postings_user + (5 * $Dokumente)) / $aktivity_index_seminar) * 100;
		if ($aktivity_index_user > 100) {
			$offset = $aktivity_index_user / 4;
			if ($offset < 0) {
				$offset = 0;
			} elseif ($offset > 200) {
				$offset = 200;
			}
			$red = dechex(200-$offset) ;
			$green = dechex(200);
			$blue = dechex(200-$offset) ;
			if ($offset > 184)  {
				$red = "0".$red;
				$blue = "0".$blue;
			}
		} else {
			$red = dechex(200);
			$green = dechex($aktivity_index_user * 2) ;
			$blue = dechex($aktivity_index_user * 2) ;
			if ($aktivity_index_user < 8)  {
				$green = "0".$green;
				$blue = "0".$blue;
			}
		}
	}


// Anzeige der eigentlichen Namenzeilen

	echo "<tr>";
	if ($showscore == TRUE) {
		printf("<td bgcolor=\"#%s%s%s\" class=\"%s\">", $red, $green,$blue, $class2);
		printf("<img src=\"pictures/blank.gif\" %s width=\"10\" heigth=\"10\"></td>", tooltip(_("Aktivität: ").round($aktivity_index_user)."%"));
	}

	if ($rechte) {
		if (is_opened($db->f("user_id"))) {
			$link = $PHPSELF."?cmd=lessinfos&username=".$db->f("username")."#".$db->f("username");
			$img = "forumgraurunt.gif";
		} else {
			$link = $PHPSELF."?cmd=moreinfos&username=".$db->f("username")."#".$db->f("username");
			$img = "forumgrau.gif";
		}
	}

	$anker = "<A name=\"".$db->f("username")."\">";
	printf ("<td class=\"%s\" nowrap>%s<font size=\"-1\">&nbsp;%s.</td>", $class, $anker, $c);
	printf ("<td class=\"%s\">", $class);
	if ($rechte) {
		printf ("<A href=\"%s\"><img src=\"pictures/%s\" border=\"0\"", $link, $img);
		echo tooltip(sprintf(_("Weitere Informationen über %s"), $db->f("username")));
		echo ">&nbsp;</A>";
	}

	printf ("<font size=\"-1\"><a href = about.php?username=%s>", $db->f("username"));
	echo htmlReady($db->f("fullname")) ."</a></font></td>";
	if ($key != "dozent" && $rechte) {
		if ($db->f("mkdate")) {
			echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".date("d.m.y,",$db->f("mkdate"))."&nbsp;".date("H:i:s",$db->f("mkdate"))."</font></td>";
		} else {
			echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">"._("unbekannt")."</font></td>";
		}
	} else if ($key == "dozent" && $rechte) {
		echo "<td class=\"$class\" align=\"center\">&nbsp;</td>";
	}
	echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".$db->f("doll")."</font></td>";
	echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".$Dokumente."</font></td>";

	echo "<td class=\"$class\" align=\"center\">";
	if ($GLOBALS['CHAT_ENABLE']){
		echo chat_get_online_icon($db->f("user_id"),$db->f("username"),$SessSemName[1]) . "&nbsp;";
	}

	printf ("<a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" %s border=\"0\"></a>", $db->f("username"), tooltip(_("Nachricht an User verschicken")));

	echo "</td>";

	// Befoerderungen und Degradierungen
	$username=$db->f("username");
	if ($rechte) {

		// Tutor entlassen
		if ($key == "tutor" AND $SemUserStatus!="tutor") {
			echo "<td class=\"$class\">&nbsp</td>";
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=pain&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		elseif ($key == "autor") {
			// zum Tutor befördern
			if ($SemUserStatus!="tutor") {
				if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"])
					$db2->query ("SELECT DISTINCT user_id FROM seminar_inst LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$UID' AND seminar_id ='$SessSemName[1]' AND inst_perms!='user' AND inst_perms!='autor'");
				else
					$db2->query ("SELECT user_id FROM auth_user_md5  WHERE perms IN ('tutor', 'dozent') AND user_id = '$UID' ");
				if ($db2->next_record()) {
					echo "<td class=\"$class\" align=\"center\">";
					echo "<a href=\"$PHP_SELF?cmd=pleasure&username=$username\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>";
				} else echo "<td class=\"$class\" >&nbsp;</td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// Schreibrecht entziehen
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=lesen&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		// Schreibrecht erteilen
		elseif ($key == "user") {
			$db2->query ("SELECT perms, user_id FROM auth_user_md5 WHERE user_id = '$UID' AND perms != 'user'");
			if ($db2->next_record()) { // Leute, die sich nicht zurueckgemeldet haben duerfen auch nicht schreiben!
				echo "<td class=\"$class\" align=\"center\">";
				echo "<a href=\"$PHP_SELF?cmd=schreiben&username=$username\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// aus dem Seminar werfen
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=raus&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		elseif ($key == "accepted") { // temporarily accepted students
			// forward to autor
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_rein&username=%s&accepted=1\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>", $class, $username);
			// kick
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=admission_raus&username=$username&accepted=1\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		else { // hier sind wir bei den Dozenten
			echo "<td colspan=\"3\" class=\"$class\" >&nbsp;</td>";
		}

		if ($db3->f("admission_type")) {
			if ($key == "autor" || $key == "user")
				printf ("<td width=\"80%%\" align=\"center\" class=\"%s\"><font size=-1>%s%s</font></td>", $class, ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"), (!$db->f("name") && !$db->f("studiengang_id") == "all") ?  "&nbsp; ": "");
			else
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp;</td>", $class);
		}

		// info-field for users
		$show_area = "show_".$key;
		if ((is_opened($db->f("user_id")) || isset($$show_area)) && $rechte) { // show further userinfosi

		?>
			<tr>
				<td class=<?=$class?> colspan=9>
					<form action="<?=$PHPSELF."#".$db->f("username")?>" method="POST">
					<table border="0">
						<tr>
							<td width="25%">
								<font size="-1"><?=_("Bemerkungen:")?></font><br/>
								<TEXTAREA name="userinfo" rows=3 cols=30><?=$db->f("comment")?></TEXTAREA>
							</td>
							<td>&nbsp;</td>
							<td class="<?=$class?>" align="left" valign="top" width="30%">
							</td>
							<td class="<?=$class?>" align="center" width="15%">
								<font size="-1"><?=_("&Auml;nderungen")?></font><br />
								<INPUT type="image" <?=makeButton("uebernehmen", "src")?>>
								<INPUT type="hidden" name="user_id" value="<?=$db->f("user_id")?>">
								<INPUT type="hidden" name="cmd" value="change_userinfo">
								<INPUT type="hidden" name="username" value="<?=$db->f("username")?>">
							</td>
						</tr>
					</table>
				</form>
				</td>
		<?
		}

	} // Ende der Dozenten/Tutorenspalten


	print("</tr>\n");
	$c++;
} // eine Zeile zuende

if ($rechte) {
	if ($db3->f("admission_type"))
		$colspan=7;
	else
		$colspan=6;
} else
	$colspan=4;

if ($showscore==TRUE)
	$colspan++;

	echo "<tr><td class=\"blank\" colspan=\"$colspan\">&nbsp;</td></tr>";

} // eine Gruppe zuende
}
echo "</table>\n";

echo "</td></tr>\n";  // Auflistung zuende

// Warteliste
if ($rechte) {
	$db->query ("SELECT admission_seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname , username, studiengaenge.name, position, admission_seminar_user.studiengang_id, status FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) LEFT JOIN studiengaenge ON (admission_seminar_user.studiengang_id=studiengaenge.studiengang_id)  WHERE admission_seminar_user.seminar_id = '$SessionSeminar' AND admission_seminar_user.status != 'accepted' ORDER BY position, name");
	if ($db->num_rows()) { //Only if Users were found...

		// die eigentliche Teil-Tabelle
		echo "<tr><td class=\"blank\" colspan=\"2\">";
		echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
		echo "<tr height=\"28\">";
		printf ("<td class=\"steel\" width=\"%s%%\" align=\"left\"><img src=\"pictures/blank.gif\" width=\"1\" height=\"20\"><font size=\"-1\"><b>%s</b></font></td>", ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30",  ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1) ? _("Warteliste") : _("Anmeldeliste"));
		if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
			printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("Position"));
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\">&nbsp; </td>");
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("Nachricht"));
		printf("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("eintragen"));
		printf("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("entfernen"));
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td></tr>\n", _("Kontingent"));


		while ($db->next_record()) {
			if ($db->f("status") == "claiming") { // wir sind in einer Anmeldeliste und brauchen Prozentangaben
				$db2=new DB_Seminar;
				$admission_studiengang_id = $db->f("studiengang_id");
				$admission_seminar_id = $db->f("seminar_id");
				$plaetze = round ($db->f("admission_turnout") * ($db->f("quota") / 100));  // Anzahl der Plaetze in dem Studiengang in den ich will
				$db2->query("SELECT count(*) AS wartende FROM admission_seminar_user WHERE seminar_id = '$admission_seminar_id' AND studiengang_id = '$admission_studiengang_id'");
				if ($db2->next_record())
					$wartende = ($db2->f("wartende"));   // Anzahl der Personen die auch in diesem Studiengang auf einen Platz lauern
						 if ($plaetze >= $wartende)
							$admission_chance = 100;   // ich komm auf jeden Fall rein
				else
					$admission_chance = round (($plaetze / $wartende) * 100); // mehr Bewerber als Plaetze
			}

			$cssSw->switchClass();
			printf ("<tr><td width=\"%s%%\" class=\"%s\" align=\"left\"><font size=\"-1\"><a href=\"about.php?username=%s\">%s</a></font></td>",  ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30", $cssSw->getClass(), $db->f("username"), $db->f("fullname"));
			if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=\"-1\">%s</font></td>", $cssSw->getClass(), $db->f("position"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp; </td>", $cssSw->getClass());

			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" %s border=\"0\"></a></td>",$cssSw->getClass(), $db->f("username"), tooltip(_("Nachricht an User verschicken")));

			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_rein&username=%s\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>", $cssSw->getClass(), $db->f("username"));
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_raus&username=%s\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>", $cssSw->getClass(), $db->f("username"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=\"-1\">%s</font></td></tr>\n", $cssSw->getClass(), ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"));
		}
		print "</table>";
	}
}

// Der Dozent braucht mehr Unterstuetzung, also Tutor aus der(n) Einrichtung(en) berufen...
//Note the option "only_inst_user" from the config.inc. If it is NOT setted, this Option is disabled (the functionality will do in this case do seachform below)
if ($rechte AND $SemUserStatus!="tutor" AND $SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"]) {
	$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, inst_perms, perms FROM seminar_inst d LEFT JOIN user_inst a USING(Institut_id) ".
	"LEFT JOIN auth_user_md5  b USING(user_id) LEFT JOIN user_info USING(user_id) ".
	"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$SessSemName[1]')  ".
	"WHERE d.seminar_id = '$SessSemName[1]' AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) GROUP BY a.user_id ORDER BY Nachname";

	$db->query($query); // ergibt alle berufbaren Personen
	?>

	<tr>
		<td class=blank colspan=2>&nbsp;
		</td>
	</tr>
	<tr><td class=blank colspan=2>

	<table width="99%" border="0" cellpadding="2" cellspacing="0" border="0" align="center">
	<form action="<? echo $PHP_SELF ?>" method="POST">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size="-1"><b><?=_("MitarbeiterInnen der Einrichtung(en)")?></b></font></td>
		<td class="steel1" width="40%" align="left"><select name="u_id" size="1">
		<?
		printf("<option value=\"0\">- -  %s - -\n", _("bitte ausw&auml;hlen"));
		while ($db->next_record())
			printf("<option value=\"%s\">%s - %s\n", $db->f("user_id"), my_substr($db->f("fullname")." (".$db->f("username"),0,35).")", $db->f("inst_perms"));
		?>
		</select></td>
		<td class="steel1" width="20%" align="center"><font size=-1><? if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) print _("als TutorIn"); else print _("als Mitglied") ?></font><br />
		<input type="IMAGE" name="add_tutor" <?=makeButton("eintragen", "src")?> border="0" value=" <?=_("Als TutorIn berufen")?> "></td>
	</tr></form></table>
<?

} // Ende der Berufung

//insert autors via free search form
if ($rechte) {
	if ($search_exp) {
		$search_exp = trim($search_exp);
		$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, perms FROM auth_user_md5 a ".
			"LEFT JOIN user_info USING(user_id) LEFT JOIN seminar_user b ON (b.user_id=a.user_id AND b.seminar_id='$SessSemName[1]')  ".
			"WHERE perms IN ('autor','tutor','dozent') AND ISNULL(b.seminar_id) AND ".
			"(username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ".
			"ORDER BY Nachname";
		$db->query($query); // results all users which are not in the seminar
		?>

	<tr>
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
	<tr><td class=blank colspan=2>
	<a name="freesearch"></a>
	<table width="99%" border="0" cellpadding="2" cellspacing="0" border=0 align="center">
	<form action="<? echo $PHP_SELF ?>?cmd=add_user" method="POST">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size=-1><b><?=_("Gefundene Nutzer")?></b></font></td>
		<td class="steel1" width="40%" align="left"><select name="username" size="1">
		<?
		printf("<option value=\"0\">- -  %s - -\n", _("bitte ausw&auml;hlen"));
		while ($db->next_record())
			printf("<option value=\"%s\">%s - %s\n", $db->f("username"), my_substr($db->f("fullname")." (".$db->f("username"),0,35).")", $db->f("perms"));
		?>
		</select></td>
		<td class="steel1" width="20%" align="center"><font size=-1><? if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"]) print _("als AutorIn") ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font><br />
		<input type="IMAGE" name="add_user" <?=makeButton("eintragen", "src")?> align="absmiddle" border=0 value=" <?=_("Als AutorIn berufen")?> ">&nbsp;<a href="<? echo $PHP_SELF ?>"><?=makeButton("neuesuche")?></a></td>

	</tr></form></table>
		<?
	} else { //create a searchform
		?>
	<tr>
		<td class=blank colspan=2>&nbsp;
		</td>
	</tr>
	<tr><td class=blank colspan=2>
	<table width="99%" border="0" cellpadding="2" cellspacing="0" border=0 align="center">
	<form action="<?=$PHP_SELF?>#freesearch" method="POST">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size=-1><b><?=_("Nutzer in die Veranstaltung eintragen")?></b></font>
		<br /><font size=-1>&nbsp; <? printf(_("Bitte geben Sie den Vornamen, Nachnamen %s oder Usernamen zur Suche ein"), "<br />&nbsp;")?> </font></td>
		<td class="steel1" width="40%" align="left">
		<input type="TEXT" name="search_exp" size="40" maxlength="255" />
		<td class="steel1" width="20%" align="center">
		<input type="IMAGE" name="start_search" <?=makeButton("suchestarten", "src")?> border=0 value=" <?=_("Suche starten")?> "></td>
	</tr></form></table>
	<?
		if (($EXPORT_ENABLE) AND ($perm->have_studip_perm("tutor", $SessSemName[1])))
		{
			include_once($ABSOLUTE_PATH_STUDIP . $PATH_EXPORT . "/export_linking_func.inc.php");
//			echo "<table width=\"99%\"><tr><td colspan=$colspan align=right class=\"steel1\"><br>" . export_button($SessSemName[1], "person", $SessSemName[0], "html", "html-teiln") . "</td></tr></table>";
			echo "<br><b>&nbsp;<font size=\"-1\">" . export_link($SessSemName[1], "person", $SessSemName[0], "rtf", "rtf-teiln") . "</font></b>";
		}

	}
	?>
	<tr>
		<td class=blank colspan=2>&nbsp;
		</td>
	</tr>
	<?

} // end insert autor

echo "</td></tr></table>";

// Save data back to database.
page_close()
?>
</body>
</html>

