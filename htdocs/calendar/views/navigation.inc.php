<?

require($RELATIVE_PATH_CALENDAR . "/calendar_links.inc.php");

if($cmd != "changeview"){
?>
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="topic">&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Termine"><b>&nbsp;<? echo $title; ?></b></td>
		</tr>
<?
	if($intro){
?>
		<tr><td class="blank">&nbsp;
			<blockquote>
				Dieser Terminkalender verwaltet Ihre Termine. Sie k&ouml;nnen Termine eintragen, &auml;ndern, 
				gruppieren und sich &uuml;bersichtlich anzeigen lassen.
			</blockquote>
		</td></tr>
<?
	}
	else
		echo '<tr><td class="blank" height="15" width="100%">&nbsp;</td></tr>';
		
	echo "</table>";
}

?>
