<?php 
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
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
	
<?php
	if ($auth->is_authenticated() && $user->id != "nobody") {
			print "<br><b>Sie sind schon als Benutzer am System angemeldet!</b><br>";
			print "<br><a href=\"index.php\">Zur&uuml;ck</a> zur Startseite.<br>";
	} else { 
		$auth->logout();
?>

<table width="80%" align="center" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic"><img src="pictures/login.gif" border="0"><b>&nbsp;Nutzungsbedingungen</b></td></tr>
<tr><td class="blank">
<blockquote><br><br>
Stud.IP ist ein Open Source Projekt und steht unter der GPL. Das Programm
befindet sich in einer Phase der st&auml;ndigen Weiterentwicklung. F&uuml;r Vorschl&auml;ge
und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an
<a href="mailto:crew@studip.de">crew@studip.de</a> oder direkt an die
<a href="impressum.php">Entwickler</a>.<br><br>

Um den Funktionsumfang von Stud.IP nutzen zu k&ouml;nnen, m&uuml;ssen Sie sich im System anmelden.<br>
Das hat viele Vorz&uuml;ge:<br>
<blockquote><li>Zugriff auf Ihre Daten von jedem internetf&auml;higen Rechner weltweit,
<li>Anzeige neuer Mitteilungen oder Dateien seit Ihrem neuen Besuch,
<li>Eine eigene Homepage im System,
<li>die M&ouml;glichkeit anderen Teilnehmern Nachrichten zu verschicken oder mit ihnen zu chatten,
<li>und Vieles mehr.</li></blockquote><br>

<b>Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:</b><br><br>

<blockquote>
1. Bei Stud.IP besteht RealName-Pflicht. Der Benutzer oder die Benutzerin
verpflichtet sich, seinen/ihren korrekten Vornamen und Nachnamen anzugeben.
Der zum Login ben&ouml;tigte Anmeldename ist innerhalb der programmtechnisch
festgelegten Grenzen frei w&auml;hlbar.<br><br>

2. Der Benutzer oder die Benutzerin hat sicherzustellen, dass seine/ihre
angegebene E-Mailadresse g&uuml;ltig und funktionsf&auml;hig ist.<br><br>

3. Alle anderen Angaben zu Ihrer Person erfolgen freiwillig.<br>
Wenn Sie weitere Daten von sich angeben, sind diese nur f&uuml;r andere, registrierte Nutzer des Systems zug&auml;nglich.
Eine Ausnahme hiervon sind automatisch aus dem System generierte Personalverzeichnisse der beteiligten Einrichtungen.<br><br>

4.Der Benutzer oder die Benutzerin stellt sicher, da&szlig; er/sie bei der Nutzung des
Kommunikationssystems Stud.IP nicht gegen eine geltende Rechtsvorschrift
verst&ouml;&szlig;t. Insbesondere verpflichtet sich der Benutzer oder die Benutzerin:<br>
<blockquote>
a) Stud.IP weder zum Abruf noch zur Verbreitung von sitten-oder rechtswidrigen Inhalten zu benutzen.<br>

b) Die geltenden Jugendschutzvorschriften zu beachten.<br>

c) Die Privatsph&auml;re anderer zu respektieren und daher in keinem Fall bel&auml;stigende, verleumderische oder bedrohende Inhalte
einzustellen oder zu verschicken.<br>

d) Keine Anwendungen auszuf&uuml;hren, die zu einer Ver&auml;nderung der physikalischen oder logischen Struktur der genutzten Netze
f&uuml;hren k&ouml;nnen.<br>
</blockquote><br>

5. Die Nutzung von Stud.IP f&uuml;r jede andere Form von Werbe- oder
Marketingbotschaften ist nicht gestattet und verpflichtet den Benutzern oder
die Benutzerin zum Ersatz des Stud.IP entstandenen Schadens.<br><br> 

6. Der Benutzer oder die Benutzerin verpflichtet sich, seinen/ihren Zugang
gegen die unbefugte Benutzung durch Dritte zu sch&uuml;tzen. Stud.IP weist an dieser
Stelle darauf hin, da&szlig; das Passwort nicht weitergegeben werden darf. Der
Benutzer oder die Benutzerin haftet f&uuml;r jede durch sein/ihr Verhalten
erm&ouml;glichte unbefugte Benutzung seines/ihres Accounts, soweit ihn/sie ein Verschulden
trifft.<br><br> 

7. Bei einem Versto&szlig; des Benutzers oder der Benutzerin gegen die oben
aufgef&uuml;hrten Obliegenheiten erfolgt eine unverz&uuml;gliche Sperrung des Zugangs.<br><br> 
</blockquote>

<br>
<a href="register2.php"><b>Ich erkenne die Nutzungsbedingungen an</b></a><br>
<br>
<A href="index.php">Abbruch</a><br>
</blockquote>
</td></tr>
<tr><td class="blank">&nbsp;</td></tr>
</table>
<?php
}
?>

<?php page_close() ?>
</body>
</html>
<!-- $Id$ -->