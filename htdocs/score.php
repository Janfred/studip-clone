<?php
        page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));



?>
<html>
<head>
        <link rel="stylesheet" href="style.css" type="text/css">
        <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
        <body bgcolor=white>

<title>Stud.IP</title>
</head>


<?php
        include "$ABSOLUTE_PATH_STUDIP/seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php
        include "$ABSOLUTE_PATH_STUDIP/header.php";   //hier wird der "Kopf" nachgeladen
        require_once("$ABSOLUTE_PATH_STUDIP/functions.php");   //hier wird der "Kopf" nachgeladen
        require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");   //hier wird der "Kopf" nachgeladen
?>
<body>
<br>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;Stud.IP-Score</td>
</tr>
<tr>
<td class="blank" align = left width="60%">&nbsp; <br><blockquote>
Auf dieser Seite k&ouml;nnen Sie abrufen, wie weit Sie in der Stud.IP-Score aufgestiegen sind. Je aktiver Sie sich im System verhalten, desto h&ouml;her klettern Sie!
<?

$score = getscore();
$user_id=$user->id; //damit keiner schummelt...

////////////////////////// schreiben des Wertes
$db=new DB_Seminar;
$cssSw=new cssClassSwitcher;

IF ($cmd=="write") {
	$query = "UPDATE user_info "
		." SET score = $score"
		." WHERE user_id = '$user_id'";
		$db->query($query);
	}
	
IF ($cmd=="kill") {
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = 0"
		." WHERE user_id = '$user_id'";
		$db->query($query);
	}

////////////////////////// Angabe der eigenen Werte (immer)

echo "<br><br><b>Ihre Score:&nbsp; ".$score."</b>";
echo "<br><b>Ihr Titel</b> ;-)&nbsp; <b>".gettitel($score)."</b>";
echo "<br><br><a href='score.php?cmd=write'>Diesen Wert hier ver&ouml;ffentlichen</a>";
?>

</blockquote></td>
<td class="blank" align = right><img src="pictures/board2.jpg" border="0"></td>
</tr>
</table>
<table width="100%" border=0 cellpadding=0 cellspacing=0><tr><td class=blank>
<br><br><blockquote>Hier sehen Sie die Score der Nutzer, die Ihre Werte ver&ouml;ffentlicht haben:</blockquote>&nbsp; </td></tr>
<tr><td class="blank">
<?

///////////////////////// Liste aller die mutig (oder eitel?) genug sind

$rang = 1;
$db=new DB_Seminar;
$db->query("SELECT b.*,score FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0 ORDER BY score DESC");
if ($db->num_rows()) {
	echo "<table width=\"99%\" align=\"center\" border=0 cellpadding=2 cellspacing=0>";
	while ($db->next_record()) {
		$kill = "";
		$cssSw->switchClass();
		IF ($db->f("user_id")==$user_id) $kill = "&nbsp; &nbsp; <a href='score.php?cmd=kill'>[l&ouml;schen]</a>";
		echo "<tr><td class=\"".$cssSw->getClass()."\" width=1% class=steel1 nowrap align=right>".$rang.".<td class=\"".$cssSw->getClass()."\" width=10% class=steel1 nowrap>"
		."&nbsp; &nbsp; <a href='about.php?username=".$db->f("username")."'>".$db->f("Vorname")."&nbsp; ".$db->f("Nachname")."</a></td>"
		."<td class=\"".$cssSw->getClass()."\" width=10% class=steel1>".$db->f("score")."</td><td class=\"".$cssSw->getClass()."\" width=10% class=steel1>".gettitel($db->f("score"))
		.$kill
		."</td></tr>";
		$rang++;
		}
	echo "</table>\n";
	}

          page_close()
 ?>
 </td></tr></table>
</body>
</html>