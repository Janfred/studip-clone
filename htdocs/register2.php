<?php 


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Register_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

if ($auth->auth["uid"] == "nobody") {
	$auth->logout();
	header("Location: register2.php");
	page_close();
	die;
}

?>
<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>
<body bgcolor="#ffffff">

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php 
	include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<body>

<table width ="100%" cellspacing=0 cellpadding=2>
<tr>
	<td class="topic" colspan=6><b>&nbsp;Herzlich Willkommen</b>
	</td>
</tr>

<tr>
	<td class="blank" colspan=6>&nbsp;
		<blockquote>
		Ihre Registrierung wurde erfolgreich vorgenommen.<br><br>
		Das System wird Ihnen zur Best&auml;tigung eine Email zusenden.<br>
		Bitte rufen Sie die Email ab und folgen Sie den Anweisungen, um Schreibrechte im System zu bekommen.<br>
		<br>
		<a href="index.php">Hier</a> geht es wieder zur Startseite.<br>
		<br>
		</blockquote>
	</td>
</tr>	
</table>

<?php page_close() ?>
</body>
</html>
<!-- $Id$ -->