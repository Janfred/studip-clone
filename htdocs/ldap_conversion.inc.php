<?
/** 
 *  Info-Screen on first login - Conversion from Normal to Ldap-Authentification
 */
function no_special_username($name) {
	
	$matches = array("@","_","-");
	for ($i = 0; $i < strlen($name); $i++) {
		for ($j = 0; $j < sizeof($matches); $j++) {
			if ($name[$i] == $matches[$j]) return FALSE;
		}
	}
	return TRUE;
}

function ldap_convert($userid) {
	global $ldap_cmd, $ldap_pwd, $check_ldap, $sess, $PHP_SELF, $ABSOLUTE_PATH_STUDIP;

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;

	$info_de = "Sehr geeherteR NutzerIn,<br/>"
					. "<br/>Wir stellen bis zum 1. Juni 2004 auf ein anderes Login-Verfahren (LDAP) um.<br/>"
					. "<br/>Um sich weiterhin einloggen zu können, benötigen Sie ihr Rechenzentrums-Passwort, welches Sie Anfang des Ersten Semesters erhalten haben.<br/>"
					. "<br/>Ihr Passwort für stud.IP k&ouml;nnen Sie zukünftig nur noch auf der Verwaltungsseite des Rechenzentrums &auml;ndern.<br/>"
					. "<a href=\"https://www-ssl.rz.uni-osnabrueck.de/ldap\" target=\"_blank\">Klicken Sie hier, um zur Verwaltungs-Seite zu gelangen. &gt;</a><br/>"
					. "<br/>Sie k&ouml;nnen Ihren Account jetzt auf das neue Login-Verfahren umstellen, wenn Sie ihr Rechenzentrums-Passwort kennen.<br/>"
					. "F&uuml;llen Sie daf&uuml;r das folgende Formular aus und klicken Sie auf \"weiter\".<br/>";
	$info_de2 = "<br/><a href=\"$PHP_SELF\">Klicken Sie hier, um ohne eine &Auml;nderung fortzufahren. &gt;</a><br/>";

	$info_en = "Dear User,<br/>"
					. "<br/>Until 1st of June 2004 we are going to change the login procedure (LDAP).<br/>"
					. "<br/>Due to this, you will need your password from the \"RechenZentrum (RZ)\", which you should have received at the beginning of the first semester.<br/>"
					. "<br/>In future you can change your password only on the administration-site of the \"Rechenzentrum\".<br/>"
					. "<a href=\"https://www-ssl.rz.uni-osnabrueck.de/ldap\" target=\"_blank\">Click here to proceed to the administration-site &gt;</a><br/>"
					. "<br/>You can change your account now, if you know your \"RZ\"-password.<br/>"
					.	"Fill in the following form and click on \"continue\".<br/>";
	$info_en2 = "<br/><a href=\"$PHP_SELF\">Click here to proceed without any change &gt;</a><br/>";

	$de_success = "Die &Auml;nderung wurde durchgef&uuml;hrt<br/>Sie k&ouml;nnen sich in Zukunft nur noch mit Ihrem Rechenzentrums-Passwort anmelden!<br/><br/>";
	$de_failure = "Die &Auml;nderung konnte NICHT durchgef&uuml;hrt werden!<br/>Haben Sie vielleicht das falsche Passwort verwendet?<br/><br/>";

	$en_success = "The changes have been applied.<br/>In future you can login ONLY with the \"RZ\"-password!<br/><br/>";
	$en_failure = "The changes have NOT been applied!<br/>Perhaps you chose the wrong password?<br/><br/>";

	$db->query("SELECT username, auth_plugin FROM auth_user_md5 WHERE user_id = '$userid'");
	$db->next_record();
	if (($db->f("auth_plugin") == NULL) && no_special_username($db->f("username")) || $ldap_cmd) {
		$db2->query("SELECT preferred_language FROM user_info WHERE user_id = '$userid'");
		$db2->next_record();
	?>
		<table width="80%" align="center" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan="3" valign="top">
				<img src="pictures/login.gif" border="0"><b>&nbsp;<?=($db2->f("preferred_language") == "de_DE") ? "Umstellung auf LDAP" : "Conversion to LDAP"?></b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td class="blank" width="1%"></td>
			<td class="blank">
			<?
				if ($ldap_cmd == "order") {
					require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/auth_plugins/StudipAuthLdapOS.class.php");
					$ldap = new StudipAuthLdapOS();
	
					if ($ldap->doLdapBind($db->f("username"),$ldap_pwd)) {
						$sess->unregister("check_ldap");
						$db3->query("UPDATE auth_user_md5 SET auth_plugin = 'ldapos' WHERE username = '".$db->f("username")."'");
						if ($db2->f("preferred_language") == "de_DE") 
							echo $de_success;
						else 
							echo $en_success;
					} else {
						if ($db2->f("preferred_language") == "de_DE") 
							echo $de_failure;
						else 
							echo $en_failure;
						echo "<form action=\"$PHP_SELF\" method=\"post\">";
						echo "<input type=\"hidden\" name=\"ldap_cmd\" value=\"nothing\">";
						echo "<input type=\"image\" ".makebutton("zurueck","src").">";
						echo "</form>";
					}
					echo "<a href=\"$PHP_SELF\">Studip &gt;</a><br/>";
				} else {
			?>
					<form action="<?=$PHP_SELF?>" method="post">
						<?=($db2->f("preferred_language") == "de_DE") ? $info_de : $info_en?>
						<br/>&nbsp;&nbsp;<b><?=_("Passwort:")?></b>&nbsp;
						<input type="password" name="ldap_pwd"><br/>
						<input type="hidden" name="ldap_cmd" value="order"><br/>
						&nbsp;&nbsp;<input type="image" <?=makebutton("weiter","src")?>><br/><br/>
						<?=($db2->f("preferred_language") == "de_DE") ? $info_de2 : $info_en2?>
					</form>
			<? } ?>
			</td>
			<td class="blank" width="1%"></td>
		</tr>	
		<tr>
			<td class="blank" colspan="3">&nbsp;</td>
		</tr>
		</table>
		<?
		page_close();
		die;
	} else {
		$sess->unregister("check_ldap");
	}
}
?>
