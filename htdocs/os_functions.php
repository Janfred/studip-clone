<?
/*
 * Some useful functions
 */
 
function check_terms($userid, $_language_path) {

	global $i_accept_the_terms;

	if ($i_accept_the_terms == "yes") return;
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;

	$db->query("SELECT * FROM active_sessions WHERE sid = '".$userid."'");
	if (!$db->next_record()) {
		?>
		<table width="80%" align="center" border=0 cellpadding=0 cellspacing=0>
		<tr><td class="topic"><img src="pictures/login.gif" border="0"><b>&nbsp;<?=_("Nutzungsbedingungen")?></b></td></tr>
		<tr><td class="blank">
		<blockquote><br><br>
		<?=_("Stud.IP ist ein Open Source Projekt und steht unter der Gnu Public Licence (GPL). Das System befindet sich in der st&auml;ndigen Weiterentwicklung.")?>
		<br><br>
		<?=_("Um den vollen Funktionsumfang von Stud.IP nutzen zu k&ouml;nnen, m&uuml;ssen Sie sich am System anmelden.")?><br>
		<?=_("Das hat viele Vorz&uuml;ge:")?><br>
		<blockquote><li><?=_("Zugriff auf Ihre Daten von jedem internetf&auml;higen Rechner weltweit,")?>
		<li><?=_("Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,")?>
		<li><?=_("Eine eigene Homepage im System,")?>
		<li><?=_("die M&ouml;glichkeit anderen TeilnehmerInnen Nachrichten zu schicken oder mit ihnen zu chatten,")?>
		<li><?=_("und vieles mehr.")?></li></blockquote><br>
		<?=_("Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:")?><br><br>
		<? include("./locale/$_language_path/LC_HELP/pages/nutzung.html"); ?>
		<center><a href="index.php?i_accept_the_terms=yes"><b><?=_("Ich erkenne die Nutzungsbedingungen an")?></b></a></center>
		<br/>	
		<? page_close();
		$db2->query("DELETE FROM active_sessions WHERE sid = '".$userid."'");
		die;
	}
}

?>

