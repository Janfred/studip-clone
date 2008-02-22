<?php
/* vim: noexpandtab */
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
									.'title="RSS" href="' . $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'rss.php?id='.$rss_id.'"/>';
	}
}

$HELP_KEYWORD="Basis.Startseite"; // set keyword for new help
$CURRENT_PAGE = _("Startseite");
// Start of Output

include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';

// only for authenticated users
if ($auth->is_authenticated() && $user->id != 'nobody') {
	//                     Menuename, Link, Target
	$menue[ 1] = array( _("Meine Veranstaltungen"), 'meine_seminare.php', false);
	$menue[ 2] = array( _("Veranstaltung hinzuf&uuml;gen"), 'sem_portal.php', false);
	$menue[ 3] = array( _("Mein Planer"), 'calendar.php', false);
	$menue[ 4] = array( _("Terminkalender"), 'calendar.php', false);
	$menue[ 5] = array( _("Adressbuch"), 'contact.php', false);
	$menue[ 6] = array( _("Stundenplan"), 'mein_stundenplan.php', false);
	$menue[ 7] = array( _("pers&ouml;nliche Homepage"), 'about.php', false);
	$menue[ 8] = array( _("individuelle Einstellungen"), 'edit_about.php?view=allgemein', false);
	$menue[ 9] = array( _("Suchen"), 'auswahl_suche.php', false);
	$menue[10] = array( _("Personensuche"), 'browse.php', false);
	$menue[11] = array( _("Veranstaltungssuche"), 'sem_portal.php', false);

	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.Allgemeines");
	} else {
		$help_url="help/index.php";
	}
	$menue[12] = array( _("Hilfe"), $help_url, '_new');
	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.SchnellEinstiegKomplett");
	} else {
		$help_url="help/index.php?help_page=schnelleinstieg.htm";
	}
	$menue[13] = array( _("Schnelleinstieg"), $help_url, '_new');
	// dozent
	$menue[20] = array( _("Verwaltung von Veranstaltungen"), 'adminarea_start.php?list=TRUE', false);
	$menue[21] = array( _("neue Veranstaltung anlegen"), 'admin_seminare_assi.php?new_session=TRUE', false);
	// admin
	$menue[30] = array( _("Veranstaltungen an meinen Einrichtungen"), 'meine_seminare.php', false);
	$menue[31] = array( _("Verwaltung von Veranstaltungen"), 'adminarea_start.php?list=TRUE', false);
	$menue[32] = array( _("Verwaltung von Einrichtungen"), 'admin_institut.php?list=TRUE', false);
	$menue[33] = array( _("globale Benutzerverwaltung"), 'new_user_md5.php', false);
	if($GLOBALS['STM_ENABLE']) $menue[34] = array( _("Studienmodule"), 'auswahl_module.php', false);// root
	$menue[40] = array( _("Veranstaltungs&uuml;bersicht"), 'sem_portal.php', false);
	$menue[41] = array( _("Verwaltung globaler Einstellungen"), 'new_user_md5.php', false);

	if ($GLOBALS["PLUGINS_ENABLE"]) {
            $plugin_entries = array();
            // plugins activated
            $pluginengine = PluginEngine::getPluginPersistence("System");
            $activatedplugins = $pluginengine->getAllActivatedPlugins();
            if (!empty($activatedplugins)) {
                foreach ($activatedplugins as $activatedplugin) {
                    if ($activatedplugin->hasNavigation() &&
                        $activatedplugin->getDisplayType(SYSTEM_PLUGIN_STARTPAGE)) {
                        $plugin_entries[] = array(
                            'plugin' => $activatedplugin,
                            'navigation' => $activatedplugin->getNavigation()
                        );
                    }
                }
            }

            if ($perm->have_perm('admin')) {
		// plugins activated
		$pluginengine = PluginEngine::getPluginPersistence("Administration");
		$activatedplugins = $pluginengine->getAllActivatedPlugins();
                if (!empty($activatedplugins)) {
                    foreach ($activatedplugins as $activatedplugin) {
			if ($activatedplugin->hasTopNavigation()) {
                            $plugin_entries[] = array(
                                'plugin' => $activatedplugin,
                                'navigation' => $activatedplugin->getTopNavigation()
                            );
                        }
                    }
                }
            }

            foreach ($plugin_entries as $plugin_entry) {
                $activatedplugin = $plugin_entry['plugin'];
                $pluginnav = $plugin_entry['navigation'];
                $pluginid = $activatedplugin->getPluginid();
                $menue["pluginnav_" . $pluginid] = array(
                    $pluginnav->getDisplayname(),
                    $pluginnav->getLink(),
                    false);

                $submenu = array();
                foreach ($pluginnav->getSubMenu() as $subkey => $subitem) {
                    $menue["pluginnav_" . $pluginid . '_' . $subkey] = array(
                        $subitem->getDisplayname(),
                        $subitem->getLink(),
                        false);
                    $submenu[] = "pluginnav_" . $pluginid . '_' . $subkey;
                }
                $pluginmenue[] = array("pluginnav_" . $pluginid, $submenu);
            }
        }

	$sem_create_perm = (in_array(get_config('SEM_CREATE_PERM'), array('root','admin','dozent')) ? get_config('SEM_CREATE_PERM') : 'dozent');

	if ($perm->have_perm('root')) { // root
		$ueberschrift = _("Startseite f&uuml;r Root bei Stud.IP");
		//                 array(mainmenue number, array(submenue numbers));
		$menue_auswahl[] = array(40, array());
		$menue_auswahl[] = array(31, array());
		$menue_auswahl[] = array(32, array());
		$menue_auswahl[] = array(41, array());
		if($GLOBALS['STM_ENABLE']) $menue_auswahl[] = array(34, array());

	} elseif ($perm->have_perm('admin')) { // admin
		$ueberschrift = _("Startseite f&uuml;r AdministratorInnen bei Stud.IP");
		$menue_auswahl[] = array(30, array());
		$menue_auswahl[] = array(31, array());
		$menue_auswahl[] = array(32, array());
		if($GLOBALS['STM_ENABLE']) $menue_auswahl[] = array(34, array());
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(33, array());
	} elseif ($perm->have_perm('dozent')) { // dozent
		$ueberschrift = _("Startseite f&uuml;r DozentInnen bei Stud.IP");
		$menue_auswahl[] = array( 1, array());
		$menue_auswahl[] = array(20, ($sem_create_perm == 'dozent' ? array(21) : array()));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array(8));
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));
		if($GLOBALS['STM_ENABLE']) $menue_auswahl[] = array(34, array());
	} elseif ($perm->have_perm('autor')) { // autor, tutor
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
		$menue_auswahl[] = array( 1, array(2));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array(8));
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));

	} else { // user
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
		$menue_auswahl[] = array( 1, array(2));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array());
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));
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
						my_info('<font size=-1>' . sprintf(_("Sie haben noch nicht auf Ihre %s Bestätigungsmail %s geantwortet.<br>Bitte holen Sie dies nach, um Stud.IP Funktionen wie das Belegen von Veranstaltungen nutzen zu können.<br>Bei Problemen wenden Sie Sich an: %s"),'<a href="'.$help_url.'" target="new">','</a>', '<a href="mailto:'.$GLOBALS['UNI_CONTACT'].'">'.$GLOBALS['UNI_CONTACT'].'</a></font>')); ?>
					</td></tr>
				</table>
			</td>
			<td class="blank" align="right" valign="top" background="<?= $GLOBALS['ASSETS_URL'] ?>images/sms3.jpg"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="235" height="1"></td>
		</tr>
		</table>
		<br><br>
	<?
	}

        // insert plugin menus
        if (!empty($pluginmenue)) {
                foreach ($pluginmenue as $item) {
                        $menue_auswahl[] = $item;
                }
        }

	// Display banner ad
	if ($GLOBALS['BANNER_ADS_ENABLE']) {
		require_once 'lib/banner_show.inc.php';
		banner_show();
	}

// display menue
?>

	<div align="center">
		<table class="index_box" border="0" cellpadding="2" cellspacing="0" >
			<tr>
				<td class="topic" colspan="2" align="left"><img src="<?=$GLOBALS['ASSETS_URL']?>images/home.gif" align="absmiddle" /> <b><?=$ueberschrift?></b></td>
			</tr>
			<tr>
				<td class="blank" valign="top" style="padding-left:25px; width:80%;">
				<table align="left" cellpadding="4">
<?
				for ($i=0; $i < count($menue_auswahl); $i++) { // mainmenue
					if ($menue[$menue_auswahl[$i][0]][1]) {
						echo	'<tr><td><div class="mainmenu" align="left"><a href="'.$menue[$menue_auswahl[$i][0]][1]. '"'.
						(($menue[$menue_auswahl[$i][0]][2])? ' target="'.$menue[$menue_auswahl[$i][0]][2].'"':'').
						'>'. $menue[$menue_auswahl[$i][0]][0]. '</a>';

					} else {
						echo	'<tr><td><div class="mainmenu">'. $menue[$menue_auswahl[$i][0]][0];
					}
					for ($k = 0; $k < count($menue_auswahl[$i][1]); $k++) { // submenue
						echo	(($k == 0)? '<br />':'&nbsp;/&nbsp;');
						echo	'<font size="-1"><a href="',$menue[$menue_auswahl[$i][1][$k]][1].'"'.
							(($menue[$menue_auswahl[$i][1][$k]][2])? ' target="'.$menue[$menue_auswahl[$i][1][$k]][2].'"':'').
							'>'. $menue[$menue_auswahl[$i][1][$k]][0].'</a></font>';
					}
					echo	'</div></td></tr>', "\n";
				}
?>
				</table>
				</td>
				<td class="indexpage" align="right" valign="top"><img src="<?=$GLOBALS['ASSETS_URL']?>images/blank.gif" width="390" height="100"></td>
			</tr>
		</table>
		<br/>
<?

	// display news
	if (show_news('studip', $perm->have_perm('root'), 0, $index_data['nopen'], "70%", $LastLogin, $index_data))
		echo "<br/>\n";

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
		include 'show_vote.php';
		show_votes ('studip', $auth->auth['uid'], $perm);
	}

	if ($GLOBALS["PLUGINS_ENABLE"]){
		// PluginEngine aktiviert.
		// Prüfen, ob PortalPlugins vorhanden sind.
		$portalpluginpersistence = PluginEngine::getPluginPersistence("Portal");
		$activatedportalplugins = $portalpluginpersistence->getAllActivatedPlugins();
		if (!is_array($activatedportalplugins)){
			$activatedportalplugins = array();
		}

		foreach ($activatedportalplugins as $activatedportalplugin){
			if (!$activatedportalplugin->hasAuthorizedView()){
				// skip this plugin
				continue;
			}
			// set the gettext-domain
			$domain = "gtdomain_" . strtolower(get_class($activatedportalplugin));
			bindtextdomain($domain,$plugindbenv->getBasepath() . $activatedportalplugin->getPluginpath() . "/locale");
			textdomain($domain);
			// hier nun die PortalPlugins anzeigen
?>
		<table class="index_box" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td class="topic"><img src="<?=$activatedportalplugin->getPluginiconname()?>" align="absmiddle" /> <b><?=$activatedportalplugin->getDisplaytitle()?></b></td>
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
		<br/>

<?
			// restore the domain
			textdomain("studip");
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
		<table class="index_box" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td class="topic"><b><?=htmlReady($feedtitle)?></b></td>
			</tr>
			<tr>
				<td class="index_box_cell">
					<? $feed->rssfeed_start(); ?>
				</td>
			</tr>
		</table>
		<br/>

<?
		}
	}
?>
	</div>
<?
} else { //displaymodul for nobody
	$index_nobody_template =& $GLOBALS['template_factory']->open('index_nobody');
	$index_nobody_template->set_attribute('sso_cas', array_search("CAS", $GLOBALS["STUDIP_AUTH_PLUGIN"]));
	$index_nobody_template->set_attribute('sso_shib', array_search("Shib", $GLOBALS["STUDIP_AUTH_PLUGIN"]));
	$index_nobody_template->set_attribute('self_registration_activated', get_config('ENABLE_SELF_REGISTRATION'));
	$index_nobody_template->set_attribute('free_access_activated', get_config('ENABLE_FREE_ACCESS'));
	$index_nobody_template->set_attribute('help_url', format_help_url("Basis.Allgemeines"));
	$db=new DB_Seminar();
	$db->query("SELECT count(*) from seminare");
	$db->next_record();
	$index_nobody_template->set_attribute('num_active_courses', $db->f(0));
	$db->query("SELECT count(*) from auth_user_md5");
	$db->next_record();
	$index_nobody_template->set_attribute('num_registered_users', $db->f(0));
	$index_nobody_template->set_attribute('num_online_users', get_users_online_count(10));
	echo $index_nobody_template->render();

}
?>

<?
if ($GLOBALS["PLUGINS_ENABLE"])
{
	$portalpluginpersistence = PluginEngine::getPluginPersistence("Portal");
	$activatedportalplugins = $portalpluginpersistence->getAllActivatedPlugins();
	// we already should have the activatedportalplugins here
	if (!empty($activatedportalplugins))
	{
		foreach ($activatedportalplugins as $activatedplugin)
		{
			if ($activatedplugin->hasUnauthorizedView())
			{
?>
		<div align="center">
		<table class="index_box" border="0" cellpadding="2" cellspacing="0" >
			<tr>
				<td class="topic"><img src="<?=$activatedplugin->getPluginiconname()?>"" align="absmiddle" /><b>&nbsp;<?= $activatedplugin->getDisplaytitle() ?></b></td>
			</tr>
			<tr>
				<td class="index_box_cell">
					<?= $activatedplugin->showOverview(false) ?>
				</td>
			</tr>
		</table>
		</div>
		<br/>

<?php
			}
		}
	}
}

include 'lib/include/html_end.inc.php';
page_close();
