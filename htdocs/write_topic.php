<?php
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

?>

<html>
<head>
<?IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
    echo "</head></html>";
    die;
}

IF  ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben, dazu auch JS Check auf Name
?>
<SCRIPT language="JavaScript">
<!--
function pruefe_name(){
 var re_nachname = /^([a-zA-ZÄÖÜ][^0-9"´'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_nachname.test(document.forumwrite.nobodysname.value)==false) {
 	alert("Bitte geben Sie Ihren tatsächlichen Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
  if (document.forumwrite.nobodysname.value=="unbekannt") {
 	alert("Bitte geben Sie Ihren Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
 return checked;
}
// -->
</SCRIPT>
<?
}
?>
<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<?
	echo "<BODY>";

	include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren --

	require_once("forum.inc.php");
	require_once("visual.inc.php");
	require_once("functions.php");
	require_once("msg.inc.php");

	include "check_sem_entry.inc.php"; //hier wird der Zugang zum Seminar ueberprueft	

?>


<table class="blank" width="100%" cellspacing=0 border=0><tr>

<?

// Freies Seminar mit Schreibrecht fuer Nobody?

IF ($user->id == "nobody"){
	$db=new DB_Seminar;
	$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id='$SessSemName[1]' AND Schreibzugriff=0");
	IF ($db->num_rows()) $pass=TRUE;
	ELSE $pass=FALSE;
	}

if (!(have_sem_write_perm()) OR $pass==TRUE) {
if (!isset($Create) || $Create != "abschicken") {
	if (isset($topic_id)) {
		$db=new DB_Seminar;
		$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
		if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
			echo "</body></html>";
			page_close();
			die;
			}
		while ($db->next_record()){
			$name = $db->f("name");
			echo"<td class=steel2 colspan=2>&nbsp; &nbsp; <b><font size=2>".htmlReady($name)."</font></b></td>";
			echo "\n</tr><tr>";
			$parent_description = formatReady($db->f("description"));
	  	if (ereg("\[quote",$parent_description) AND ereg("\[/quote\]",$parent_description))
				$parent_description = quotes_decode($parent_description);
			printcontent ("100%","",$parent_description,"");
			echo "\n</tr><tr><td colspan=2 class=steel>&nbsp; </td></tr><tr><td colspan=2 class=steel1><blockquote>";
			
			}
		print "<br><b>Hierzu antworten:</b><br><br>\n";
	} else {
		$topic_id = "0";
		$root_id = "0";
		$name = "";
	}

IF  ($user->id == "nobody") echo "<form name=forumwrite method=post action=\"write_topic.php\" onsubmit=\"return pruefe_name()\">"; // bei nobody mit namen pruefen
ELSE echo "<form name=forumwrite method=post action=\"write_topic.php\">";

?>
<input type=hidden name="parent_id" value="<? print $topic_id; ?>">
<input type=hidden name="root_id" value="<? print $root_id; ?>">
<? print "&Uuml;berschrift: <br><input type=text name=name value=\"";

if ($topic_id != "0" OR $topic_id > 0) {
	if (substr($name,0,3)=="Re:")
		print htmlReady($name);
	else
		print "Re: ".htmlReady($name);
	}
else
	print""
?>
" size=60>
<input type=hidden name=author value = "<?
$db=new DB_Seminar;
$tmp = $auth->auth["uname"];
$db->query ("SELECT Vorname , Nachname , username FROM auth_user_md5 WHERE username = '$tmp'");
while ($db->next_record())
	 echo $db->f("Vorname")." " . $db->f("Nachname");
?>
" size=20><br><br>
<?	IF  ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben
		$description =	"<b>Ihr Name:</b>&nbsp; <input type=text size=50 name=nobodysname onchange=\"return pruefe_name()\" value='unbekannt'><br><br><input type=hidden name=update value='".$write."'>";
				} 
	echo $description;
?>	
Ihr Beitrag:
<br><textarea name=description cols=60 rows=12></textarea><br>
<input type="submit" name=Create value="abschicken">
</form>
<?
}
else
	{
	if ($parent_id) {
		$db=new DB_Seminar;
		$db->query("SELECT * FROM px_topics WHERE topic_id='$parent_id' AND Seminar_id ='$SessSemName[1]'");
		if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
			echo "</body></html>";
		  page_close();
			die;
		}
	}
	IF ($nobodysname) $author = $nobodysname;
	$topic_id = CreateTopic ($name, $author, $description, $parent_id, $root_id);
	parse_window( "msg§Ihr Beitrag wurde erfolgreich ins System &uuml;bernommen§info§Sie k&ouml;nnen dieses Fenster jetzt schliessen.<br>Um Ihr neues Posting zu sehen, m&uuml;ssen Sie das Hauptfenster aktualisieren!§", "§", "Schreiben erfolgreich", FALSE);
}
}

else {
      $msg=have_sem_write_perm();
      parse_window($msg, "§", "Schreiben nicht m&ouml;glich");
      }
?>
<?php
  // Save data back to database.
  page_close()
?>
</blockquote></td></tr></table>
</body>
</html>