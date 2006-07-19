<?php
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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- hier muessen Seiten-Initialisierungen passieren --

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/show_news.php");
require_once("$ABSOLUTE_PATH_STUDIP/show_dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/DbView.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/dbviews/sem_tree.view.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/DbSnapshot.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/DataFields.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/guestbook.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/object.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/score.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/StudipLitList.class.php");


if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php";
	if ($_REQUEST['kill_chat']){
		chat_kill_chat($_REQUEST['kill_chat']);
	}

}
if ($GLOBALS['VOTE_ENABLE']) {
	include_once ("$ABSOLUTE_PATH_STUDIP/show_vote.php");
}
if (get_config('NEWS_RSS_EXPORT_ENABLE')){
	$news_author_id = StudipNews::GetRssIdFromUserId(get_userid($_REQUEST['username']));
	if($news_author_id){
		$stmp = new studip_smtp_class();
		$_include_additional_header = '<link rel="alternate" type="application/rss+xml" '
									.'title="RSS" href="' . $stmp->url . 'rss.php?id='.$news_author_id.'"/>';
	}
}

// Start  of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");

if ($rssusername) $username = $rssusername;
?>
<script language="Javascript">
function open_im()
{
	fenster=window.open("studipim.php","im_<?=$user->id;?>","scrollbars=yes,width=400,height=300","resizable=no");
}
</script>

<?php

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

//Wenn kein Username uebergeben wurde, wird der eigene genommen:
if (!isset($username) || $username == "")
	$username = $auth->auth["uname"];


//3 zeilen wegen username statt id zum aufruf... in $user_id steht jetzt die user_id (sic)
$db->query("SELECT * FROM auth_user_md5  WHERE username ='$username'");
$user_gesperrt = FALSE;
if ($perm->have_perm("root"))
        $db->query("SELECT * FROM auth_user_md5  WHERE username ='$username'");
else
        $db->query("SELECT * FROM auth_user_md5  WHERE username ='$username' AND locked=0");

$db->next_record();

if ($perm->have_perm("root") && $db->f("locked")==1)
        $user_gesperrt = TRUE;

if (!$db->nf()) {
	parse_window ("error§"._("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!")."<br />"._(" Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers ge&auml;ndert hat, oder der Nutzer gel&ouml;scht wurde.")."§", "§", _("Benutzer nicht gefunden"));
	die;
} else
	$user_id=$db->f("user_id");


// count views of Page
if ($auth->auth["uid"]!=$user_id) {
	object_add_view($user_id);
}

if ($auth->auth["uid"]==$user_id)
	$homepage_cache_own = time();

$DataFields = new DataFields($user_id);

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
$db->query("SELECT user_info.* , auth_user_md5.*,". $_fullname_sql['full'] . " AS fullname FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '$user_id'");
$db->next_record();

//daten anzeigen
IF (($user_id==$user->id AND $perm->have_perm("autor")) OR $perm->have_perm("root") OR $admin_darf == TRUE) { // Es werden die Editreiter angezeigt, wenn ich &auml;ndern darf
	include ("$ABSOLUTE_PATH_STUDIP/links_about.inc.php");
}

?>

<table align="center" width="100%" border="0" cellpadding="1" cellspacing="0" valign="top">
<tr><td class="topic" align="right" colspan=2>&nbsp;</td></tr>
<?
if ($msg)
{
	echo"<tr><td class=\"steel1\"colspan=2><br>";
	parse_msg ($msg, "§", "steel1");
	echo"</td></tr>";
}
?>

<tr><td class="steel1" align="center" valign="center"><img src="pictures/blank.gif" width=205 height=5><br />
<?

// hier wird das Bild ausgegeben

if(!file_exists("./user/".$user_id.".jpg")) {
	echo "&nbsp;<img src=\"./user/nobody.jpg\" width=\"200\" height=\"250\"" . tooltip(_("kein persönliches Bild vorhanden")).">";
} else {
	?>&nbsp;<img src="./user/<?echo $user_id; ?>.jpg" border=1 <?=tooltip($db->f("fullname"));?>></td><?
}

// Hier der Teil fuer die Ausgabe der normalen Daten
?>
<td class="steel1"  width="99%" valign ="top" rowspan=2><br><blockquote>
<? echo "<b><font size=7>".htmlReady($db->f("fullname"))."</font></b><br><br>";?>
<? echo "<b>&nbsp;" . _("E-mail:") . " </b><a href=\"mailto:". $db->f("Email")."\">".htmlReady($db->f("Email"))."</a><br>";
IF ($db->f("privatnr")!="") echo "<b>&nbsp;" . _("Telefon (privat):") . " </b>". htmlReady($db->f("privatnr"))."<br>";
IF ($db->f("privatcell")!="") echo "<b>&nbsp;" . _("Mobiltelefon:") . " </b>". htmlReady($db->f("privatcell"))."<br>";
IF ($db->f("privadr")!="") echo "<b>&nbsp;" . _("Adresse (privat):") . " </b>". htmlReady($db->f("privadr"))."<br>";
IF ($db->f("Home")!="") {
	$home=$db->f("Home");
	$home=FixLinks(htmlReady($home));
	echo "<b>&nbsp;" . _("Homepage:") . " </b>".$home."<br>";
}

if ($perm->have_perm("root") && $user_gesperrt) {
        echo "<BR><B><FONT COLOR=\"RED\" SIZE=\"+1\">"._("BENUTZER IST GESPERRT!")."</FONT></B><BR>\n";
}

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
	//statusgruppen
	if ($gruppen = GetStatusgruppen($institut,$user_id)){
		echo "&nbsp;" . htmlReady(join(", ", array_values($gruppen)));
	}
	echo "<font size=-1>";
	IF ($db3->f("raum")!="")
		echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Raum:") . " </b>", htmlReady($db3->f("raum"));
	IF ($db3->f("sprechzeiten")!="")
		echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Sprechzeit:") . " </b>", htmlReady($db3->f("sprechzeiten"));
	IF ($db3->f("Telefon")!="")
		echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Telefon:") . " </b>", htmlReady($db3->f("Telefon"));
	IF ($db3->f("Fax")!="")
		echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Fax:") . " </b>", htmlReady($db3->f("Fax"));

	echo "</font><br>";
}
echo "</blockquote></td></tr>"
?>

</td></tr><tr>
<td class="steel1" height=99% align="left" valign="top">
<?
echo "<font size=\"-1\">&nbsp;"._("Besucher dieser Homepage:")."&nbsp;".object_return_views($user_id)."</font><br>";

// Die Anzeige der Stud.Ip-Score
$score = new Score(get_userid($username));

if ($score->IsMyScore()) {
	echo "&nbsp;<a href=\"score.php\" " . tooltip(_("Zur Highscoreliste")) . "><font size=\"-1\">"
		. _("Ihr Stud.IP-Score:") . " ".$score->ReturnMyScore()."<br>&nbsp;"
		. _("Ihr Rang:") . " ".$score->ReturnMyTitle()."</a></font><br />";
} elseif ($score->ReturnPublik()) {
	$scoretmp = $score->GetScore(get_userid($username));
	$title = $score->gettitel($scoretmp, $score->GetGender(get_userid($username)));
	echo "&nbsp;<a href=\"score.php\"><font size=\"-1\">"
	. _("Stud.IP-Score:") . " ".$scoretmp."<br>&nbsp;"
	. _("Rang:") . " ".$title."</a></font><br />";
}

if ($username==$auth->auth["uname"]) {
	if ($auth->auth["jscript"])
		echo "<br>&nbsp;<font size=\"-1\"><a href='javascript:open_im();'>" . _("Stud.IP Messenger starten") . "</a></font>";
} else {
	if (CheckBuddy($username)==FALSE)
		echo "<br /><font size=\"-1\">&nbsp;<a href=\"$PHP_SELF?cmd=add_user&add_uname=$username&username=$username\">" . _("zu Buddies hinzuf&uuml;gen") . "</a></font>";
	echo "<br /><font size=\"-1\"> <a href=\"sms_send.php?sms_source_page=about.php&rec_uname=", $db->f("username"),"\">&nbsp;" . _("Nachricht an Nutzer") . "&nbsp;<img style=\"vertical-align:middle\" src=\"pictures/nachricht1.gif\" " . tooltip(_("Nachricht an Nutzer verschicken")) . " border=0 align=texttop></a></font>";

}

// Export dieses Users als Vcard
echo "<br /><font size=\"-1\"><a href=\"contact_export.php?username=$username\">&nbsp;"._("vCard herunterladen")."&nbsp;<img style=\"vertical-align:middle\" src=\"pictures/vcardexport.gif\" border=\"0\" ".tooltip(_("als vCard exportieren"))."></a></font>";

echo "<br>&nbsp; ";
echo "</td>";

echo "</tr></table><br>\n";

// News zur person anzeigen!!!

($perm->have_perm("autor") AND $auth->auth["uid"]==$user_id) ? $show_admin=TRUE : $show_admin=FALSE;
if (show_news($user_id, $show_admin, 0, $about_data["nopen"], "100%", 0, $about_data))
	echo "<br>";



// alle persoenlichen Termine anzeigen, aber keine privaten
if ($GLOBALS['CALENDAR_ENABLE']) {
	$temp_user_perm = get_global_perm($user_id);
	if ($temp_user_perm != "root" && $temp_user_perm != "admin") {
		$start_zeit = time();
		($perm->have_perm("autor") AND $auth->auth["uid"] == $user_id) ? $show_admin = TRUE : $show_admin = FALSE;
		if (show_personal_dates($user_id, $start_zeit, -1, FALSE, $show_admin, $about_data["dopen"]))
			echo "<br>";
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
	show_votes ($username, $auth->auth["uid"], $perm, YES);
}

// show Guestbook

if (!$guestpage)
	$guestpage = 0;
$guest = new Guestbook($user_id,$admin_darf,$guestpage);

if ($guestbook && $perm->have_perm('autor'))
	$guest->actionsGuestbook($guestbook,$post,$deletepost,$studipticket);

if ($guest->active == TRUE || $guest->rights == TRUE) {
	$guest->showGuestbook();
	echo "<br>";
}

// show chat info
if ($GLOBALS['CHAT_ENABLE']){
	if (chat_show_info($user_id))
		echo "<br>";
}


//test Ausgabe von Literaturlisten
if ( ($lit_list = StudipLitList::GetFormattedListsByRange($user_id)) ) {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Literaturlisten") . " </b></td>";
	$cs = 1;
	if ($user_id == $auth->auth['uid']){
		echo '<td align="right" class="topic">&nbsp;<a href="admin_lit_list.php?_range_id=self"><img src="pictures/pfeillink.gif" border="0" ' . tooltip(_("Literaturlisten bearbeiten")) . '>&nbsp;</td>';
		$cs = 2;
	}
	printf ("</tr><tr><td colspan=\"$cs\" class=\"steel1\">&nbsp;</td></tr><tr><td colspan=\"$cs\" class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td colspan=\"$cs\" class=\"steel1\">&nbsp;</td></tr></table><br>\n",$lit_list);
	unset($cs);
}

// Hier wird der Lebenslauf ausgegeben:
if ($db->f("lebenslauf")!="") {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Lebenslauf") . " </b></td></tr>";
	printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($db->f("lebenslauf")));
}

// Ausgabe Hobbys

if ($db->f("hobby")!="") {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Hobbies") . " </b></td></tr>";
	printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($db->f("hobby")));
}

//Ausgabe von Publikationen

if ($db->f("publi")!="") {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Publikationen") . " </b></td></tr>";
	printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($db->f("publi")));
}

// Ausgabe von Arbeitsschwerpunkten

if ($db->f("schwerp")!="") {
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Arbeitsschwerpunkte") . " </b></td></tr>";
	printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($db->f("schwerp")));
}

//add the free administrable datafields (these field are system categories - the user is not allowed to change the catgeories)
$localFields = $DataFields->getLocalFields($user_id, 'user', $auth->auth['perm']);

foreach ($localFields as $val) {
	if ($DataFields->checkPermission($perm, $val["view_perms"], $auth->auth["uid"], $user_id)) {
		if ($val["content"]) {
			echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . htmlReady($val["name"]) . " </b></td></tr>";
			printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($val["content"]));
		}
	}
}

//add the own categories - this ones are self created by the user
$db2->query("SELECT * FROM kategorien WHERE range_id = '$user_id' ORDER BY priority");
while ($db2->next_record())  {
	$head=$db2->f("name");
	$body=$db2->f("content");
	if ($db2->f("hidden")!='1') { // oeffentliche Rubrik
		printf ("<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;%s </b></td></tr>", htmlReady($head));
		printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($body));
	} elseif ($db->f("user_id")==$user->id) {  // nur ich darf sehen
		printf ("<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;%s </b> (%s)</td></tr>", htmlReady($head), _("f&uuml;r andere unsichtbar"));
		printf ("<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>%s</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n",formatReady($body));
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
			$output .= "<br><font size=\"+1\"><b>$sem_name</b></font><br><br>";
			$snap->sortRows("Name");
			while ($snap->nextRow()) {
				$ver_name = $snap->getField("Name");
				$sem_number_start = $snap->getField("sem_number");
				$sem_number_end = $snap->getField("sem_number_end");
				if ($sem_number_start != $sem_number_end){
					$ver_name .= " (" . $all_semester[$sem_number_start]['name'] . " - ";
					$ver_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $all_semester[$sem_number_end]['name']) . ")";
				}
				$output .= "<b><a href=\"details.php?sem_id=" . $snap->getField("Seminar_id") . "\">" . htmlReady($ver_name) . "</a></b><br>";
			}
		}
	}
	if ($output){
		echo "<table class=\"blank\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><b>&nbsp;" . _("Veranstaltungen") . "</b></td></tr><tr><td class=\"steel1\"><blockquote>";
		echo $output;
		echo "</blockquote></td></tr><tr><td class=\"steel1\">&nbsp;</td></tr></table><br>\n";
	}
}

// Save data back to database.
page_close();
?>
</body>
</html>
