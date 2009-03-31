<?php
# Lifter001: TODO
# Lifter002: TEST (mriehe)
# Lifter005: TODO
/**
 * score.php - Stud.IP Highscore List
 *
 * PHP Version 5
 *
 * @author		Stefan Suchi <suchi@gmx.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @access		public
 * @copyright 	2000-2009 Stud.IP
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check('user');

//Imports
require_once 'lib/seminar_open.php'; // initialise Stud.IP-Session
require_once 'lib/functions.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/score.class.php';
require_once 'lib/object.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/classes/Avatar.class.php';

//Basics
$HELP_KEYWORD="Basis.VerschiedenesScore"; // external help keyword
$CURRENT_PAGE=_("Stud.IP-Score");

/* --- Actions -------------------------------------------------------------- */
$score = new Score($user->id);
if($_REQUEST['cmd']=="write")
{
	$score->PublishScore();
}
if($_REQUEST['cmd']=="kill")
{
	$score->KillScore();
}

// Liste aller die mutig (oder eitel?) genug sind
$db = new DB_Seminar();
$db->query("SELECT a.user_id,username,score,geschlecht, " .$_fullname_sql['full'] ." AS fullname FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0 AND locked=0 AND ".get_vis_query('b')." ORDER BY score DESC");
while ($db->next_record())
{
	$person = array(
		"userid" => $db->f("user_id"),
		"username" => $db->f("username"),
		"avatar" => Avatar::getAvatar($db->f("user_id"))->getImageTag(Avatar::SMALL),
		"name" => htmlReady($db->f("fullname")),
		"content" => $score->GetScoreContent($db->f("user_id")),
		"score" => $db->f("score"),
		"title" => $score->GetTitel($db->f("score"), $db->f("geschlecht"))

	);
	$persons[] = $person;
}


/* --- View ----------------------------------------------------------------- */
$template = $GLOBALS['template_factory']->open('score');
$template->set_attribute('persons', $persons);
$template->set_attribute('user', $user);
$template->set_attribute('score', $score);
$template->set_layout("layouts/base");
echo $template->render();
page_close();
?>
