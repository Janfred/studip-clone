<?php
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
?>

<html>
<head>

<?
IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
	echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
	echo "</head></html>";
	die;
	}

IF ($htmlversion == TRUE) {
	?>
	<title>Stud.IP</title>
		<link rel="stylesheet" href="style.css" type="text/css">
	</head>
	<? 
	}
ELSE {
	?>
	<title>Stud.IP</title>
		<link rel="stylesheet" href="style_print.css" type="text/css">
	</head>
	<? 
	}
	
?>
<body bgcolor=white>
<?
//////////////////////////////////////////////////////////////////////////

include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren

// include "header.php";   //hier wird der "Kopf" nachgeladen
require_once "functions.php";
require_once "visual.inc.php";
require_once "msg.inc.php";
require_once "dates.inc.php"; 
require_once "archiv.inc.php";

IF ($htmlversion==TRUE) echo "<a href=\"$PHP_SELF\">zur Druck-Ansicht</a>";
ELSE echo "<a href=\"$PHP_SELF?htmlversion=true\">zur HTML-Ansicht</a>";
echo "<h1>Forum:&nbsp; ".$SessSemName[0]."</h1>";
echo Export_Topic($SessSemName[1]);

echo "<table width=100% border=0 cellpadding=2 cellspacing=0>";
echo "<tr><td><i><font size=-1>Stand: ".date("d.m.y",time()).", ".date("G:i", time())." Uhr.</font></i></td><td align=\"right\"><font size=-2><img src=\"pictures/logo2b.gif\"><br />&copy; ".date("Y", time())." v.$SOFTWARE_VERSION&nbsp; &nbsp; </font></td></tr>";
echo "</table>\n";

  // Save data back to database.
  page_close()
 ?>
</body></body>
</html>