<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
// vim: noexpandtab
/*
index.php - Startseite von Stud.IP (anhaengig vom Status)
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
// $Id$

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));

$auth->login_if($again && ($auth->auth['uid'] == 'nobody'));

// database object
$db=new DB_Seminar;

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
if (isset($set_language)) {
	$sess->register('forced_language');
	$forced_language = $set_language;
	$_language = $set_language;
}

// store  user-specific language preference
if ($auth->is_authenticated() && $user->id != 'nobody') {
	// store last language click
	if (isset($forced_language)) {
		$db->query("UPDATE user_info SET preferred_language = '$forced_language' WHERE user_id='$user->id'");
		$_language = $forced_language;
		$sess->unregister('forced_language');
	}
}

include 'lib/seminar_open.php'; // initialise Stud.IP-Session
require_once 'config.inc.php';
require_once 'lib/functions.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/MessageBox.class.php';
include_once 'lib/classes/RSSFeed.class.php';
// -- hier muessen Seiten-Initialisierungen passieren --

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

$sess->register('index_data');

//Auf und Zuklappen News
require_once 'lib/showNews.inc.php';
process_news_commands($index_data);

// Auf- und Zuklappen Termine
if ($dopen)
	$index_data['dopen']=$dopen;

if ($dclose)
	$index_data['dopen']='';

if (get_config('NEWS_RSS_EXPORT_ENABLE') && ($auth->is_authenticated() && $user->id != 'nobody')){
	$rss_id = StudipNews::GetRssIdFromRangeId('studip');
	if($rss_id){
		$_include_additional_header = '<link rel="alternate" type="application/rss+xml" '
									.'title="RSS" href="rss.php?id='.$rss_id.'">';
	}
}

$HELP_KEYWORD="Basis.Startseite"; // set keyword for new help
$CURRENT_PAGE = _("Startseite");
// Start of Output
$GLOBALS['body_id'] = 'index';
$navigation = Navigation::getItem('/start');

include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';

// only for authenticated users
if ($auth->is_authenticated() && $user->id != 'nobody') {
	if ($perm->have_perm('root')) { // root
		$ueberschrift = _("Startseite f&uuml;r Root bei Stud.IP");
	} elseif ($perm->have_perm('admin')) { // admin
		$ueberschrift = _("Startseite f&uuml;r AdministratorInnen bei Stud.IP");
	} elseif ($perm->have_perm('dozent')) { // dozent
		$ueberschrift = _("Startseite f&uuml;r DozentInnen bei Stud.IP");
	} elseif ($perm->have_perm('autor')) { // autor, tutor
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
	} else { // user
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
		// Warning for Users
	?>
		<div align="center">
		<table width="70%" border=0 cellpadding=0 cellspacing=0 >
		<tr><td class="topicwrite" colspan=3><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/nachricht1.gif" align="texttop"><b>&nbsp;<?=_("Bestätigungsgsmail beachten!")?></b></td></tr>
		<tr>
			<td width="5%" class="blank" valign="middle">&nbsp;</td>
			<td width="90%" class="blank" valign="top">
				<table cellpadding="2">
					<tr><td class="blank" colspan="2">
					<?
						if (get_config("EXTERNAL_HELP")) {
							$help_url=format_help_url("Basis.AnmeldungMail");
						} else {
							$help_url="help/index.php?help_page=ii_bestaetigungsmail.htm";
						}
						echo MessageBox::info(sprintf(_("Sie haben noch nicht auf Ihre %s Bestätigungsmail %s geantwortet.<br>Bitte holen Sie dies nach, um Stud.IP Funktionen wie das Belegen von Veranstaltungen nutzen zu können.<br>Bei Problemen wenden Sie Sich an: %s"),'<a href="'.$help_url.'" target="_blank">','</a>', '<a href="mailto:'.$GLOBALS['UNI_CONTACT'].'">'.$GLOBALS['UNI_CONTACT'].'</a>')); ?>
					</td></tr>
				</table>
			</td>
			<td class="blank" align="right" valign="top" background="<?= $GLOBALS['ASSETS_URL'] ?>images/sms3.jpg"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="235" height="1"></td>
		</tr>
		</table>
		<br><br>
	<?
	}

	// Display banner ad
	if ($GLOBALS['BANNER_ADS_ENABLE']) {
		require_once 'lib/banner_show.inc.php';
		banner_show();
	}

// display menue
?>

		<table class="index_box" border="0" cellpadding="2" cellspacing="0" style="margin:auto">
			<tr>
				<td class="topic" colspan="2" align="left"><img class="middle" src="<?=$GLOBALS['ASSETS_URL']?>images/home.gif" alt="Home-Icon"> <b><?=$ueberschrift?></b></td>
			</tr>
			<tr>
				<td class="blank" valign="top" style="padding-left:25px; width:80%;">
				<table align="left" cellpadding="4">
				<? foreach ($navigation as $nav) : ?>
					<? if ($nav->isVisible()) : ?>
						<tr><td><div class="mainmenu" align="left">
						<? if (is_internal_url($url = $nav->getURL())) : ?>
							<a href="<?= URLHelper::getLink($url) ?>">
						<? else : ?>
							<a href="<?= htmlspecialchars($url) ?>" target="_blank">
						<? endif ?>
						<?= htmlReady($nav->getTitle()) ?></a>
					<? endif ?>
					<? $pos = 0 ?>
					<? foreach ($nav as $subnav) : ?>
						<? if ($subnav->isVisible()) : ?>
							<font size="-1">
							<?= $pos++ ? ' / ' : '<br>' ?>
							<? if (is_internal_url($url = $subnav->getURL())) : ?>
								<a href="<?= URLHelper::getLink($url) ?>">
							<? else : ?>
								<a href="<?= htmlspecialchars($url) ?>" target="_blank">
							<? endif ?>
							<?= htmlReady($subnav->getTitle()) ?></a>
							</font>
						<? endif ?>
					<? endforeach ?>
					</div></td></tr>
				<? endforeach ?>
				</table>
				</td>
				<td class="indexpage" align="right" valign="top"><img src="<?=$GLOBALS['ASSETS_URL']?>images/blank.gif" width="390" height="100" alt=""></td>
			</tr>
		</table>
		<br>
<?

	// display news
	if (show_news('studip', $perm->have_perm('root'), 0, $index_data['nopen'], "70%", $LastLogin, $index_data))
		echo "<br>\n";

	// display dates
	if (!$perm->have_perm('admin')) { // only dozent, tutor, autor, user
		include 'lib/show_dates.inc.php';
		$start = time();
		$end = $start + 60 * 60 * 24 * 7;
		if ($GLOBALS['CALENDAR_ENABLE']) {
			show_all_dates($start, $end, TRUE, FALSE, $index_data['dopen']);
		} else {
			show_dates($start, $end, $index_data['dopen']);
		}
	}

	// display votes
	if ($GLOBALS['VOTE_ENABLE']) {
		include 'lib/vote/vote_show.inc.php';
		show_votes ('studip', $auth->auth['uid'], $perm);
	}

	if ($GLOBALS["PLUGINS_ENABLE"]){
		// PluginEngine aktiviert.
		// Prüfen, ob PortalPlugins vorhanden sind.
		$activatedportalplugins = PluginEngine::getPlugins('PortalPlugin');

		foreach ($activatedportalplugins as $activatedportalplugin){
			if (!$activatedportalplugin->hasAuthorizedView()){
				// skip this plugin
				continue;
			}
			// hier nun die PortalPlugins anzeigen
?>
		<table class="index_box" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td class="topic"><img class="middle" src="<?=$activatedportalplugin->getPluginiconname()?>"> <b><?=$activatedportalplugin->getDisplaytitle()?></b></td>
				<td class="topic" align="right">
					<? if ($activatedportalplugin->hasAdministration()) : ?>
						<? if ($admin_link = $activatedportalplugin->getAdminLink()) : ?>
							<a href="<?= $admin_link ?>" title="<?= _("Administration") ?>">
								<?= Assets::img('pfeillink.gif', array('alt' => _("Administration"))) ?>
						<? endif ?>
					<? endif ?>
				</td>
			</tr>
			<tr>
				<td class="index_box_cell" colspan="2">
				<?=$activatedportalplugin->showOverview()?>
				</td>
			</tr>
		</table>
		<br>

<?
		}
	}
	page_close(); // end session

	$db->query(sprintf("SELECT * FROM rss_feeds WHERE user_id='%s' AND hidden=0 ORDER BY priority",$auth->auth["uid"]));
	while ($db->next_record()) {
		if ($db->f("name")!="" && $db->f("url")!="") {
			$feed = new RSSFeed($db->f("url"));
			if($db->f('fetch_title') && $feed->ausgabe->channel['title']) $feedtitle = $feed->ausgabe->channel['title'];
			else $feedtitle = $db->f("name");
?>
		<table class="index_box" border="0" cellpadding="2" cellspacing="0" style="margin:auto">
			<tr>
				<td class="topic"><b><?=htmlReady($feedtitle)?></b></td>
			</tr>
			<tr>
				<td class="index_box_cell">
					<? $feed->rssfeed_start(); ?>
				</td>
			</tr>
		</table>
		<br>

<?
		}
	}
?>
	</div>
<?
} else { //displaymodul for nobody
	$index_nobody_template = $GLOBALS['template_factory']->open('index_nobody');
	$db=new DB_Seminar();
	$db->query("SELECT count(*) from seminare");
	$db->next_record();
	$index_nobody_template->set_attribute('num_active_courses', $db->f(0));
	$db->query("SELECT count(*) from auth_user_md5");
	$db->next_record();
	$index_nobody_template->set_attribute('num_registered_users', $db->f(0));
	$index_nobody_template->set_attribute('num_online_users', get_users_online_count(10));

	if ($_REQUEST['logout'])
	{
		$index_nobody_template->set_attribute('logout', true);
	}

	echo $index_nobody_template->render();

	if ($GLOBALS["PLUGINS_ENABLE"])
	{
		$activatedportalplugins = PluginEngine::getPlugins('PortalPlugin');

		foreach ($activatedportalplugins as $activatedplugin)
		{
			if ($activatedplugin->hasUnauthorizedView())
			{
?>
		<br>
		<div align="center">
		<table class="index_box" border="0" cellpadding="2" cellspacing="0" >
			<tr>
				<td class="topic"><img class="middle" src="<?=$activatedplugin->getPluginiconname()?>"> <b><?= $activatedplugin->getDisplaytitle() ?></b></td>
			</tr>
			<tr>
				<td class="index_box_cell">
					<?= $activatedplugin->showOverview(false) ?>
				</td>
			</tr>
		</table>
		</div>

<?php
			}
		}
	}
}

include 'lib/include/html_end.inc.php';
page_close();
