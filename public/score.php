<?php
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check('user');

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$HELP_KEYWORD="Basis.VerschiedenesScore"; // external help keyword
$CURRENT_PAGE = _("Stud.IP-Score");
// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

require_once 'lib/functions.php';
require_once('lib/visual.inc.php');
require_once('lib/classes/score.class.php');
require_once('lib/object.inc.php');
require_once('lib/user_visible.inc.php');
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>

<tr>
<td class="blank" align = left valign="top" width="60%"><blockquote><br><font size="-1">
<?
echo _("Auf dieser Seite können Sie abrufen, wie weit Sie im Stud.IP-Score aufgestiegen sind. Je aktiver Sie sich im System verhalten, desto höher klettern Sie!");

$cssSw=new cssClassSwitcher;
$score = new Score($user->id);

// schreiben des Wertes

IF ($cmd=="write") {
	$score->PublishScore();
}

IF ($cmd=="kill") {
	$score->KillScore();
}

// Angabe der eigenen Werte (immer)

echo "<br><br><b>" . _("Ihr Score:") . "&nbsp; ".$score->ReturnMyScore()."</b>";
echo "<br><b>" . _("Ihr Titel") . "</b> ;-)&nbsp; <b>".$score->ReturnMyTitle()."</b>";
if ($score->ReturnPublik())
	echo "<br><br><a href=\"score.php?cmd=kill\">" . _("Ihren Wert von der Liste löschen") . "</a>";
else
	echo "<br><br><a href=\"score.php?cmd=write\">" . _("Diesen Wert auf der Liste veröffentlichen") . "</a>";

?>
<br><br><b>
<?=_("Hier sehen Sie den Score der NutzerInnen, die Ihre Werte ver&ouml;ffentlicht haben:")?>
<br><br></blockquote></td>
		<td class="blank" align = right valign = "top"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/board2.jpg" border="0"></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="blank">
<?

// Liste aller die mutig (oder eitel?) genug sind
$db = new DB_Seminar();
$rang = 1;
$db->query("SELECT a.user_id,username,score,geschlecht, " .$_fullname_sql['full'] ." AS fullname FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0 AND locked=0 AND ".get_vis_query('b')." ORDER BY score DESC");
if ($db->num_rows()) {
	echo "<table width=\"99%\" align=\"center\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	while ($db->next_record()) {
			$kill = "";
			$cssSw->switchClass();
			if ($db->f("user_id")==$user->id) {
				$kill = "&nbsp; &nbsp; <a href=\"score.php?cmd=kill\">" . _("[löschen]") . "</a>";
			}
			echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"1%\" nowrap align=\"right\"><font size=\"-1\">".$rang.".</td><td class=\"".$cssSw->getClass()."\" width=\"39%\" nowrap>"
			."&nbsp; &nbsp; <a href='about.php?username=".$db->f("username")."'><font size=\"-1\">".htmlReady($db->f("fullname"))."</a></td>"
			."<td class=\"".$cssSw->getClass()."\" width=\"10%\">".$score->GetScoreContent($db->f("user_id"))."</td>"
			."<td class=\"".$cssSw->getClass()."\" width=\"20%\"><font size=\"-1\">".$db->f("score")."</td><td class=\"".$cssSw->getClass()."\" width=\"30%\"><font size=\"-1\">".$score->GetTitel($db->f("score"), $db->f("geschlecht"))
			.$kill
			."</td></tr>\n";
			$rang++;
	}
	echo "</table>\n";
	}
echo '</td></tr></table>';
include ('lib/include/html_end.inc.php');
page_close();
?>