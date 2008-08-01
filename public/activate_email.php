<?php

if (!isset($_SESSION)) {
	page_open(array('sess' => 'Seminar_Session', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));
}

function head($headline, $red=False) {
$class = '';
if($red)
	$class = 'write';
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic<?=$class ?>" colspan=3 align="left">
 <img src="<?=$GLOBALS['ASSETS_URL']?>/images/mailnachricht.gif" border="0" align="absmiddle">
 <b>&nbsp;<?= $headline; ?></b>
</td></tr>
<tr><td style="background-color: #fff; padding: 1.5em;">
<?php
}

function footer() {
	echo '</td></tr></table></div> <br />';
}

function reenter_mail() {
	echo _('Sollten Sie keine E-Mail erhalten haben, können Sie sich einen neuen Aktivierungsschlüssel zuschicken lassen. Geben Sie dazu Ihre gewünschte E-Mail Adresse unten an:');
	echo '<form action="activate_email.php" method="post">'
		.'<input type="hidden" name="uid" value="'. htmlReady($_REQUEST['uid']) .'" />'
		.'<table><tr><td>'. _('E-Mail:') .'</td><td><input name="email1" /></td></tr>'
		.'<tr><td>'. _('Wiederholung:') . '</td><td><input name="email2" /></td></tr></table>'
		.makeButton("abschicken", "input"). '</form>';
}

function mail_explain() {
	echo _('Sie haben Ihre E-Mail Adresse geändert. Um diese frei zu schalten müssen Sie den Ihnen an Ihre neue Adresse zugeschickten Aktivierungs Schlüssel im unten stehenden Eingabefeld eintragen.');
	echo '<br><form action="activate_email.php" method="post"><input name="key" /><input name="uid" type="hidden" value="'.$_REQUEST['uid'].'" /><br>'
		.makeButton("abschicken","input"). '</form><br><br>';

}

if(!$_REQUEST['uid'])
	header("Location: index.php");

// display header
$plugins = array();
$current_page = _('E-Mail Aktivierung');

require_once 'lib/functions.php';
include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';

$uid = mysql_escape_string($_REQUEST['uid']);
if($_REQUEST['key']) {
	$db = new DB_Seminar(sprintf("SELECT validation_key FROM auth_user_md5 WHERE user_id='%s'", $uid));
	$db->next_record();
	$key = $db->f('validation_key');
	if($_REQUEST['key'] == $key) {
		head($topic);
		$db->query(sprintf('UPDATE auth_user_md5 SET validation_key="" WHERE user_id="%s";', $uid));
		echo _('Ihre E-Mail Adresse wurde erfolgreich geändert.');
		printf(' <a href="index.php">%s</a>', _('Zum Login'));
		unset($_SESSION['half_logged_in']);
		footer();
	} else if ($key == '') {
		head($current_page);
		echo _('Ihre E-Mail Adresse ist bereits geändert.');
		printf(' <a href="index.php">%s</a>', _('Zum Login'));
		footer();
	} else {
		head(_('Warnung'), True);
		echo _("Falcher Bestätigungscode.");
		footer();
		head($current_page);
		mail_explain();
		reenter_mail();
		footer();
	}

} else if($_REQUEST['email1'] && $_REQUEST['email2'] && $_SESSION['semi_logged_in']) {
	if($_REQUEST['email1'] == $_REQUEST['email2']) {
		// change mail
		require_once('lib/edit_about.inc.php');

		edit_email($uid, $_REQUEST['email1']);
		$_SESSION['semi_logged_in'] = False;
		
		head($current_page);
		echo _('An %s wurde ein Aktivierungslink geschickt.');
		footer();
	} else {
		head();
		echo sprintf('<b>%s</b>', _('Die eingegebenen E-Mail Adressen stimmen nicht überein. Bitte überprüfen Sie Ihre Eingabe.'));
		reenter_mail();
	}
} else if ($_SESSION['semi_logged_in']) {
	head($current_page);
	mail_explain();
	reenter_mail();
	footer();
} else {
	// this never happens unless someone manipulates urls
	// maybe handle more "beautiful" - but normal user dont see it...
	echo 'permission denied.';
}

echo '</body></html>';
page_close();
die();
?>
