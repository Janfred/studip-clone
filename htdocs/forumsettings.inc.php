<?

//Standard herstellen

if ($forumsend=="bla"){
	$forum=array(
		"jshover"=>$jshover, 
		"neuauf"=>$neuauf,
		"changed"=>"TRUE"			
		);
	}

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 align="center" border=0>
<tr>
<td class="topic" colspan=2><img src="pictures/einst.gif" border="0" align="texttop"><b>&nbsp;Einstellungen des Forums anpassen</b></td>
</tr>
<tr>
<td class="blank" colspan=2>&nbsp;
<?  IF ($forum["changed"]=="TRUE")	{ 
	// my_msg("&Auml;nderung erfolgreich"); 
	$forum["changed"]="FALSE";
	}
?>
</td>
</tr>
<tr>
<td class="blank" width=100%">


<blockquote><br>Auf dieser Seite k&ouml;nnen Sie die Bedienung des Stud.IP Forensystems an Ihre Bed&uuml;rfnisse anpassen.
</blockquote><p>
<table width="100%" border=0 cellpadding=2 cellspacing=1  border=0>
<form action="<?echo $PHP_SELF?>?view=Forum" method="POST">
<tr><td><blockquote><b>Java-Script Hovereffekte</b></td><td>
<?
IF ($auth->auth["jscript"]) {
	echo "<input type=CHECKBOX name='jshover' value=1";
	IF($forum["jshover"]==1) 
	    echo " checked";
	echo ">";
	}
ELSE echo "Sie m&uuml;ssen in Ihrem Browser Javascript aktivieren um dieses Feature nutzen zu k&ouml;nnen.";
?>
</td><td><br><blockquote>Mit dieser Funktion k&ouml;nnen sie durch reines &Uuml;berfahren der Themen&uuml;berschriften im Forum den entsprechenden Beitrag lesen. Sie k&ouml;nnen sich so sehr schnell und effizient auch durch l&auml;ngere Diskussionen arbeiten. Da jedoch die Ladezeit der Seite erheblich ansteigt, empfehlen wir diese Einstellung nur f&uuml;r Nutzer die mindestens eine ISDN Verbindung haben.<br><br></td></tr>
<tr><td><blockquote><b>Neue Beitr&auml;ge immer aufgeklappt</b></td><td><input type="CHECKBOX" name="neuauf" value="1"<?IF($forum["neuauf"]==1) echo " checked";?>></td><td><br><blockquote>Neue Postings sind immer automatisch aufgeklappt<br><br></td></tr>
<input type="HIDDEN" name="forumsend" value="bla">
<tr><td>&nbsp; </td><td  colspan=2><input type=submit name=Create value="&Auml;nderungen &uuml;bernehmen">&nbsp; </td></tr>		
</form>		
</table>
<? IF ($forumsend=="anpassen") {
	echo "</td></tr></table>";
	die;
	}