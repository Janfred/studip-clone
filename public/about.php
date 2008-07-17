<?php
# Lifter002: TODO
# Lifter005: TODO - studipim
// vim: noexpandtab
/*
about.php - Anzeige der persoenlichen Userseiten von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Niclas Nohlen <nnohlen@gwdg.de>

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

$Id$
*/
if (isset($_REQUEST['username'])) $username = $_REQUEST['username'];

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));
$perm->check("user");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- hier muessen Seiten-Initialisierungen passieren --

require_once 'lib/functions.php';
require_once('config.inc.php');
require_once('lib/dates.inc.php');
require_once('lib/messaging.inc.php');
require_once('lib/msg.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once('lib/showNews.inc.php');
require_once('lib/show_dates.inc.php');
require_once('lib/classes/DbView.class.php');
require_once('lib/dbviews/sem_tree.view.php');
require_once('lib/classes/DbSnapshot.class.php');
require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/classes/guestbook.class.php');
require_once('lib/object.inc.php');
require_once('lib/classes/score.class.php');
require_once('lib/classes/SemesterData.class.php');
require_once('lib/user_visible.inc.php');
require_once('lib/classes/StudipLitList.class.php');
require_once('lib/classes/Avatar.class.php');


function prettyViewPermString ($viewPerms) {
	switch ($viewPerms) {
		case 'all'   : return 'alle';
		case 'root'  : return 'Systemadministratoren';
		case 'admin' : return 'Administratoren';
		case 'dozent': return 'Dozenten';
		case 'dozent': return 'Dozenten';
		case 'tutor' : return 'Tutoren';
		case 'autor' : return 'Studenten';
		case 'user'  : return 'Nutzer';
	}
	return '';
}


function isDataFieldArrayEmpty ($array) {
	foreach ($array as $v)
		if (trim($v->getValue()) != '')
			return false;
	return true;
}


if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
	if ($_REQUEST['kill_chat']){
		chat_kill_chat($_REQUEST['kill_chat']);
	}
}

if ($GLOBALS['VOTE_ENABLE']) {
	include_once ("lib/vote/vote_show.inc.php");
}
if (get_config('NEWS_RSS_EXPORT_ENABLE')){
	$news_author_id = StudipNews::GetRssIdFromUserId(get_userid($_REQUEST['username']));
	if($news_author_id){
		$stmp = new studip_smtp_class();
		$_include_additional_header = '<link rel="alternate" type="application/rss+xml" '
									.'title="RSS" href="' . $stmp->url . 'rss.php?id='.$news_author_id.'"/>';
	}
}

if ($rssusername) $username = $rssusername;
//Wenn kein Username uebergeben wurde, wird der eigene genommen:
if (!isset($username) || $username == "") $username = $auth->auth["uname"];

$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$semester = new SemesterData;

$sess->register("about_data");
$msging = new messaging;
$msg = "";

//Buddie hinzufuegen
if ($cmd=="add_user")
	$msging->add_buddy ($add_uname, 0);


//Auf und Zuklappen Termine
if ($dopen)
	$about_data["dopen"]=$dopen;

if ($dclose)
	$about_data["dopen"]='';

//Auf und Zuklappen News
process_news_commands($about_data);

if ($sms_msg) {
	$msg = $sms_msg;
	$sms_msg = '';
	$sess->unregister('sms_msg');
}

//3 zeilen wegen username statt id zum aufruf... in $user_id steht jetzt die user_id (sic)
$db->query("SELECT * FROM auth_user_md5  WHERE username ='$username'");
$db->next_record();

// Help
$HELP_KEYWORD = "Basis.Homepage";
if($db->f('user_id') == $user->id && !$db->f('locked')){
	$CURRENT_PAGE = _("Meine persönliche Homepage");
	$user_id = $db->f("user_id");
} elseif ($db->f('user_id') && ($perm->have_perm("root") || (!$db->f('locked') && get_visibility_by_id($db->f("user_id"))))) {
	$CURRENT_PAGE = _("Persönliche Homepage")  . ' - ' . get_fullname($db->f('user_id'));
	$user_id = $db->f("user_id");
} else {
	$CURRENT_PAGE = _("Persönliche Homepage");
	unset($user_id);
}

# get the layout template
$layout = $GLOBALS['template_factory']->open('layouts/base_without_infobox');

# and start the output buffering
ob_start();



?>
<script language="Javascript">
function open_im() {
  fenster = window.open("studipim.php",
                        "im_<?=$GLOBALS['user']->id;?>",
                        "scrollbars=yes,width=400,height=300",
                        "resizable=no");
}
</script>

<?php

if (!$user_id){
	if ($db->f("user_id")) {
		throw new Exception(_("Diese Homepage ist nicht verfügbar."));
	} else {
		throw new Exception(_("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!"));
	}
}

// count views of Page
if ($auth->auth["uid"]!=$user_id) {
	object_add_view($user_id);
}

if ($auth->auth["uid"]==$user_id)
	$homepage_cache_own = time();

//Wenn er noch nicht in user_info eingetragen ist, kommt er ohne Werte rein
$db->query("SELECT user_id FROM user_info WHERE user_id ='$user_id'");
if ($db->num_rows()==0) {
	$db->query("INSERT INTO user_info (user_id) VALUES ('$user_id')");
}

//Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
$admin_darf = FALSE;
$db->query("SELECT b.inst_perms FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.user_id = '$user_id') AND (b.inst_perms = 'autor' OR b.inst_perms = 'tutor' OR b.inst_perms = 'dozent') AND (a.user_id = '$user->id') AND (a.inst_perms = 'admin')");
if ($db->num_rows())
	$admin_darf = TRUE;
if ($perm->is_fak_admin()){
	$db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c ON(b.Institut_id=c.Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='$user_id'");
	if ($db->next_record())
	$admin_darf = TRUE;
}
if ($perm->have_perm("root")) {
	$admin_darf=TRUE;
}


//Her mit den Daten...
$db->query("SELECT user_info.* , auth_user_md5.*,".
           $_fullname_sql['full'] . " AS fullname ".
           "FROM auth_user_md5 ".
           "LEFT JOIN user_info USING (user_id) ".
           "WHERE auth_user_md5.user_id = '$user_id'");
$db->next_record();

// generische Datenfelder aufsammeln
$short_datafields = array();
$long_datafields  = array();
foreach (DataFieldEntry::getDataFieldEntries($user_id) as $entry) {
	if ($entry->structure->accessAllowed($perm, $auth->auth["uid"], $user_id) &&
	    $entry->getDisplayValue()) {
		if ($entry instanceof DataFieldTextareaEntry) {
			$long_datafields[] = $entry;
		}
		else {
			$short_datafields[] = $entry;
		}
	}
}



$show_tabs = ($user_id == $user->id && $perm->have_perm("autor"))
             || $perm->have_perm("root")
             || $admin_darf == TRUE;
?>


<table align="center" width="100%" border="0" cellpadding="1" cellspacing="0" valign="top">

	<? if (!$show_tabs) : ?>
		<tr>
			<td class="topic" align="right" colspan="2">&nbsp;</td>
		</tr>
	<? endif ?>

	<? if ($msg) : ?>
		<tr>
			<td class="steel1" colspan="2">
				<br>
				<?= parse_msg ($msg, "§", "steel1") ?>
			</td>
		</tr>
	<? endif ?>

	<tr>

		<td class="steel1" valign="top">
			<br>
			<?= Avatar::getAvatar($user_id)->getImageTag(Avatar::NORMAL) ?>

			<br>
			<br>

			<font size="-1">&nbsp;<?= _("Besucher dieser Homepage:") ?>&nbsp;<?= object_return_views($user_id) ?></font>
			<br>

			<?
			// Die Anzeige der Stud.Ip-Score
			$score = new Score(get_userid($username));

			if ($score->IsMyScore()) {
				echo "&nbsp;<a href=\"score.php\" " . tooltip(_("Zur Highscoreliste")) . "><font size=\"-1\">"
				     . _("Ihr Stud.IP-Score:") . " ".$score->ReturnMyScore()."<br>&nbsp;"
				     . _("Ihr Rang:") . " ".$score->ReturnMyTitle()."</a></font><br />";
			}
			elseif ($score->ReturnPublik()) {
				$scoretmp = $score->GetScore(get_userid($username));
				$title = $score->gettitel($scoretmp, $score->GetGender(get_userid($username)));
				echo "&nbsp;<a href=\"score.php\"><font size=\"-1\">"
				     . _("Stud.IP-Score:") . " ".$scoretmp."<br>&nbsp;"
				     . _("Rang:") . " ".$title."</a></font><br />";
			}

			if ($username==$auth->auth["uname"]) {
				if ($auth->auth["jscript"]) {
					echo "<br>&nbsp;<font size=\"-1\"><a href='javascript:open_im();'>" . _("Stud.IP Messenger starten") . "</a></font>";
				}
			} else {
				if (CheckBuddy($username)==FALSE) {
					echo "<br /><font size=\"-1\">&nbsp;<a href=\"$PHP_SELF?cmd=add_user&add_uname=$username&username=$username\">" . _("zu Buddies hinzuf&uuml;gen") . "</a></font>";
				}
				echo "<br /><font size=\"-1\"> <a href=\"sms_send.php?sms_source_page=about.php&rec_uname=", $db->f("username"),"\">&nbsp;" . _("Nachricht an Nutzer") . "&nbsp;<img style=\"vertical-align:middle\" src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" " . tooltip(_("Nachricht an Nutzer verschicken")) . " border=0 align=texttop></a></font>";

			}

			// Export dieses Users als Vcard
			echo "<br /><font size=\"-1\"><a href=\"contact_export.php?username=$username\">&nbsp;"._("vCard herunterladen")."&nbsp;<img style=\"vertical-align:middle\" src=\"".$GLOBALS['ASSETS_URL']."images/vcardexport.gif\" border=\"0\" ".tooltip(_("als vCard exportieren"))."></a></font>";
			?>

			<br />
			<br />
		</td>

		<td class="steel1" width="99%" valign="top">
			<br>
			<blockquote>

				<b>
					<font size="7">
						<?= htmlReady($db->f("fullname")) ?>
					</font>
				</b>
				<br>

				<? if ($db->f('motto')) : ?>
					<b>
						<font size="5"><?= htmlReady($db->f('motto')) ?></font>
					</b>
					<br>
				<? endif ?>

				<? if (!get_visibility_by_id($user_id)) : ?>
					<? if ($user_id != $user->id) : ?>
						<p>
							<font color="red"><?= _("(Dieser Nutzer ist unsichtbar.)") ?></font>
						</p>
					<? else : ?>
						<p>
							<font color="red"><?= _("(Sie sind unsichtbar. Deshalb können nur Sie diese Seite sehen.)") ?></font>
						</p>
					<? endif ?>
				<? endif ?>

				<br>

				<b>&nbsp;<?= _("E-mail:") ?></b>
				<a href="mailto:<?= $db->f('Email') ?>"><?= htmlReady($db->f("Email")) ?></a>
				<br>

				<? if ($db->f("privatnr") != "") : ?>
					<b>&nbsp;<?= _("Telefon (privat):") ?></b>
					<?= htmlReady($db->f("privatnr")) ?>
					<br>
				<? endif ?>

				<? if ($db->f("privatcell") != "") : ?>
					<b>&nbsp;<?= _("Mobiltelefon:") ?></b>
					<?= htmlReady($db->f("privatcell")) ?>
					<br>
				<? endif ?>

				<? if (get_config("ENABLE_SKYPE_INFO") &&
				       $skype_name = $user->cfg->getValue($user_id, 'SKYPE_NAME')) : ?>
					<b>&nbsp;<?= _("Skype:") ?></b>
					<a href="skype:<?= htmlReady($skype_name) ?>?call">
						<? if ($user->cfg->getValue($user_id, 'SKYPE_ONLINE_STATUS')) : ?>
							<img src="http://mystatus.skype.com/smallicon/<?= htmlReady($skype_name) ?>" style="vertical-align:middle;" width="16" height="16" alt="My status">
						<? else : ?>
							<?= Assets::img('icon_small_skype.gif', array('style' => 'vertical-align:middle;')) ?>
						<? endif ?>
						<?= htmlReady($skype_name) ?>
					</a>
					<br>
				<? endif ?>

				<? if ($db->f("privadr") != "") : ?>
					<b>&nbsp;<?= _("Adresse (privat):") ?></b>
					<?= htmlReady($db->f("privadr")) ?>
					<br>
				<? endif ?>

				<? if ($db->f("Home") != "") : ?>
					<b>&nbsp;<?= _("Homepage:") ?></b>
					<?= FixLinks(htmlReady($db->f("Home"))) ?>
					<br>
				<? endif ?>

				<? if ($perm->have_perm("root") && $db->f('locked')) : ?>
					<br>
					<b>
						<font color="red" size="+1"><?= _("BENUTZER IST GESPERRT!") ?></font>
					</b>
					<br>
				<? endif ?>

				<?
				// Anzeige der Institute an denen (hoffentlich) studiert wird:

				$db3->query("SELECT Institute.* FROM user_inst LEFT JOIN Institute  USING (Institut_id) WHERE user_id = '$user_id' AND inst_perms = 'user'");
				IF ($db3->num_rows()) {
					echo "<br><b>&nbsp;" . _("Wo ich studiere:") . "&nbsp;&nbsp;</b><br>";
					while ($db3->next_record()) {
						echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"institut_main.php?auswahl=".$db3->f("Institut_id")."\">".htmlReady($db3->f("Name"))."</a><br>";
					}
				}

				// Anzeige der Institute an denen gearbeitet wird

				$query = "SELECT a.*,b.Name FROM user_inst a LEFT JOIN Institute b USING (Institut_id) ";
				$query .= "WHERE user_id = '$user_id' AND inst_perms != 'user' AND visible = 1 ORDER BY priority ASC";
				$db3->query($query);
				IF ($db3->num_rows()) {
					echo "<br><b>&nbsp;" . _("Wo ich arbeite:") . "&nbsp;&nbsp;</b><br>";
				}

				//schleife weil evtl. mehrere sprechzeiten und institut nicht gesetzt...

				while ($db3->next_record()) {
					$institut=$db3->f("Institut_id");
					echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"institut_main.php?auswahl=".$institut."\">".htmlReady($db3->f("Name"))."</a>";

					echo "<font size=-1>";
					IF ($db3->f("raum")!="")
						echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Raum (Stud.IP):") . " </b>", htmlReady($db3->f("raum"));
					IF ($db3->f("sprechzeiten")!="")
						echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Sprechzeit (Stud.IP):") . " </b>", htmlReady($db3->f("sprechzeiten"));
					IF ($db3->f("Telefon")!="")
						echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Telefon (Stud.IP):") . " </b>", htmlReady($db3->f("Telefon"));
					IF ($db3->f("Fax")!="")
						echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Fax: (Stud.IP)") . " </b>", htmlReady($db3->f("Fax"));

					echo '<table cellspacing="0" cellpadding="0" border="0">';
					$entries = DataFieldEntry::getDataFieldEntries(array($user_id, $institut));
					if (!isDataFieldArrayEmpty($entries)) {
						foreach ($entries as $entry) {
							$view = DataFieldStructure::permMask($auth->auth['perm']) >= DataFieldStructure::permMask($entry->structure->getViewPerms());
							$show_star = false;
							if (!$view && ($user_id == $user->id)) {
								$view = true;
								$show_star = true;
							}

							if (trim($entry->getValue()) && $view) {
								echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>' . htmlReady($entry->getName()) . ": " .'&nbsp;&nbsp;</td><td>'. $entry->getDisplayValue();
								if ($show_star) echo ' *';
							}
						}
					}

					echo '</table>';

					if ($groups = GetAllStatusgruppen($institut, $user_id)) {
						$default_entries = DataFieldEntry::getDataFieldEntries(array($user_id, $institut));
						$data = get_role_data_recursive($groups, $user_id, $default_entries);
						echo '<table cellpadding="0" cellspacing="0" border="0">';
						echo $data['standard'];
						echo '</table>';
					} else {
						echo '<br/>';
					}

					echo "</font>";
					echo '<br/>';
				}

				if (($user_id == $user->id) && $has_denoted_fields) {
					echo '<br/>';
					echo '<font size="-1">';
					echo ' * Diese Felder sind nur für Sie und AdministratorInnen sichtbar.<br/>';
					echo '</font>';
				}
				?>

				<br />

				<? foreach ($short_datafields as $entry) : ?>

					<?
					$vperms = $entry->structure->getViewPerms();
					$visible = 'all' == $vperms
					           ? _("sichtbar für alle")
					           : sprintf(_("sichtbar für Sie und alle %s"),
					                     prettyViewPermString($vperms));
					?>

					&nbsp;<strong><?= htmlReady($entry->getName()) ?>:</strong>
					<?= $entry->getDisplayValue() ?>
					<span class="minor">(<?= $visible ?>)</span>
					<br />
				<? endforeach ?>

				<br />
			</blockquote>
		</td>
</tr>
</table>

<br/>

<?

// News zur person anzeigen!!!
($perm->have_perm("autor") AND $auth->auth["uid"]==$user_id) ? $show_admin=TRUE : $show_admin=FALSE;
if (show_news($user_id, $show_admin, 0, $about_data["nopen"], "100%", 0, $about_data))
	echo "<br/>";

// alle persoenlichen Termine anzeigen, aber keine privaten
if ($GLOBALS['CALENDAR_ENABLE']) {
	$temp_user_perm = get_global_perm($user_id);
	if ($temp_user_perm != "root" && $temp_user_perm != "admin") {
		$start_zeit = time();
		($perm->have_perm("autor") AND $auth->auth["uid"] == $user_id) ? $show_admin = TRUE : $show_admin = FALSE;
		if (show_personal_dates($user_id, $start_zeit, -1, FALSE, $show_admin, $about_data["dopen"]))
			echo "<br/>";
	}
}

// include and show friend-of-a-friend list
// (direct/indirect connection via buddy list)
if ($GLOBALS['FOAF_ENABLE']
	&& ($auth->auth['uid']!=$user_id)
	&& $user->cfg->getValue($user_id, 'FOAF_SHOW_IDENTITY')) {
        include("lib/classes/FoafDisplay.class.php");
        $foaf=new FoafDisplay($auth->auth['uid'], $user_id, $username);
        $foaf->show($_REQUEST['foaf_open']);
}

// include and show votes and tests
if ($GLOBALS['VOTE_ENABLE']) {
	show_votes($username, $auth->auth["uid"], $perm, YES);
}

// show Guestbook
if (!$guestpage)
	$guestpage = 0;
$guest = new Guestbook($user_id,$admin_darf,$guestpage);

if ($_REQUEST['guestbook'] && $perm->have_perm('autor'))
	$guest->actionsGuestbook($_REQUEST['guestbook'],$_REQUEST['post'],$_REQUEST['deletepost'],$_REQUEST['studipticket']);

if ($guest->active == TRUE || $guest->rights == TRUE) {
	$guest->showGuestbook();
	echo "<br/>";
}

// show chat info
if ($GLOBALS['CHAT_ENABLE']){
	if (chat_show_info($user_id))
		echo "<br/>";
}

//test Ausgabe von Literaturlisten
if ( ($lit_list = StudipLitList::GetFormattedListsByRange($user_id)) ) {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Literaturlisten") . " </b></td>";
	$cs = 1;
	if ($user_id == $auth->auth['uid']){
		echo '<td align="right" class="topic">&nbsp;<a href="admin_lit_list.php?_range_id=self"><img src="'.$GLOBALS['ASSETS_URL'].'images/pfeillink.gif" border="0" ' . tooltip(_("Literaturlisten bearbeiten")) . '>&nbsp;</td>';
		$cs = 2;
	}
	printf ("</tr><tr><td colspan=\"$cs\" class=\"steel1\">&nbsp;</td></tr><tr><td colspan=\"$cs\" class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td colspan=\"$cs\" class=\"steel1\">&nbsp;</td></tr></table><br>\n",$lit_list);
	unset($cs);
}
// Hier werden Lebenslauf, Hobbies, Publikationen und Arbeitsschwerpunkte ausgegeben:
$ausgabe_format = '<table class="blank" width="100%%" border="0" cellpadding="0" cellspacing="0"><tr><td class="topic"><b>&nbsp;%s </b>%s</td></tr><tr><td class="steel1">&nbsp;</td></tr><tr><td class="steel1"><blockquote>%s</blockquote></td></tr><tr><td class="steel1">&nbsp;</td></tr></table><br />'."\n";
$ausgabe_felder = array('lebenslauf' => _("Lebenslauf"),
			'hobby' => _("Hobbies"),
			'publi' => _("Publikationen"),
			'schwerp' => _("Arbeitsschwerpunkte")
			);

foreach ($ausgabe_felder as $key => $value) {
	if ($db->f($key) != '') {
		printf($ausgabe_format, $value, '', formatReady($db->f($key)));
	}
}

// add the free administrable datafields (these field are system categories -
// the user is not allowed to change the categories)
foreach ($long_datafields as $entry) {
	$vperms = $entry->structure->getViewPerms();
	$visible = 'all' == $vperms
	           ? _("sichtbar für alle")
	           : sprintf(_("sichtbar für Sie und alle %s"),
	                     prettyViewPermString($vperms));
	printf($ausgabe_format,
	       htmlReady($entry->getName()),
	       "($visible)",
	       $entry->getDisplayValue());
}

if ($GLOBALS["PLUGINS_ENABLE"]){
	// PluginEngine aktiviert.
	// Prüfen, ob HomepagePlugins vorhanden sind.
	$homepagepluginpersistence = PluginEngine::getPluginPersistence("Homepage");
	$activatedhomepageplugins = $homepagepluginpersistence->getAllActivatedPlugins();
	if (!is_array($activatedhomepageplugins)){
		$activatedhomepageplugins = array();
	}
	$requser = new StudIPUser();
	$requser->setUserid($user_id);

	foreach ($activatedhomepageplugins as $activatedhomepageplugin){
		$activatedhomepageplugin->setRequestedUser($requser);
		// hier nun die HomepagePlugins anzeigen
//		if ($activatedhomepageplugin->hasNavigation()){ // wieso ist hier eine Navigation erforderlich? hab das mal geaendert :-)
		if ($activatedhomepageplugin->getStatusShowOverviewPage()){
			echo '<table class="blank" width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td class="topic"><img src="'. $activatedhomepageplugin->getPluginiconname() .'" border="0" align="texttop" /><b> ' . $activatedhomepageplugin->getDisplaytitle() .' </b></td><td align="right" width="1%" class="topic" nowrap="nowrap">&nbsp;';

			if ($requser->isSameUser($activatedhomepageplugin->getUser())) {
				$admin_link = $activatedhomepageplugin->getAdminLink();
				if (NULL !== $admin_link) {
				?>
					<a href="<?= $admin_link ?>" title="<?= _("Administration") ?>">
						<?= Assets::img('pfeillink.gif', array('alt' => _("Administration"))) ?>
					</a>
				<?
				}
			}
			echo '&nbsp;</td></tr>'."\n";
			echo '<tr><td class="steel1" colspan="2">&nbsp;</td></tr><tr><td class="steel1" colspan="2"><blockquote>';
			$activatedhomepageplugin->showOverview();
			echo '</blockquote></td></tr><tr><td class="steel1" colspan="2">&nbsp;</td></tr></table><br>'."\n";
		}
	}
}
//add the own categories - this ones are self created by the user
$db2->query("SELECT * FROM kategorien WHERE range_id = '$user_id' ORDER BY priority");
while ($db2->next_record())  {
	$head=$db2->f("name");
	$body=$db2->f("content");
	if ($db2->f("hidden") != '1') { // oeffentliche Rubrik
		printf ($ausgabe_format, htmlReady($head), '', formatReady($body));
	} elseif ($db->f("user_id") == $user->id) {  // nur ich darf sehen
		printf ($ausgabe_format, htmlReady($head), ' ('._("f&uuml;r andere unsichtbar").')',formatReady($body));
	}
}
// Anzeige der Seminare
if ($perm->get_perm($user_id) == 'dozent'){
	$all_semester = SemesterData::GetSemesterArray();
	$view = new DbView();
	$output = '';
	for ($i = count($all_semester)-1; $i >= 0; --$i){
		$view->params[0] = $user_id;
		$view->params[1] = "dozent";
		$view->params[2] = " HAVING (sem_number <= $i AND (sem_number_end >= $i OR sem_number_end = -1)) ";
		$snap = new DbSnapshot($view->get_query("view:SEM_USER_GET_SEM"));
		if ($snap->numRows){
			$sem_name = $all_semester[$i]['name'];
			if ($output) $output .= '<br />';
			$output .= "<font size=\"+1\"><b>$sem_name</b></font><br><br>";
			$snap->sortRows("Name");
			while ($snap->nextRow()) {
				$ver_name = $snap->getField("Name");
				$sem_number_start = $snap->getField("sem_number");
				$sem_number_end = $snap->getField("sem_number_end");
				if ($sem_number_start != $sem_number_end){
					$ver_name .= " (" . $all_semester[$sem_number_start]['name'] . " - ";
					$ver_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $all_semester[$sem_number_end]['name']) . ")";
				}
				$output .= '<b><a href="details.php?sem_id=' . $snap->getField('Seminar_id') . '">' . htmlReady($ver_name) . '</a></b><br />';
			}
		}
	}
	if ($output){
		printf($ausgabe_format, _("Veranstaltungen"), '', $output);
	}
}

$layout->set_attribute('content_for_layout', ob_get_clean());

if ($show_tabs) {
	$layout->set_attribute('tabs', 'links_about');
}

echo $layout->render();

// Save data back to database.
page_close();
