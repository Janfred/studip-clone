<?
/*
folder.php - Anzeige und Verwaltung des Ordnersystems
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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
*/

//Standard herstellen

$cssSw=new cssClassSwitcher;	

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
<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center"  border=0>
<form action="<?echo $PHP_SELF?>?view=Forum" method="POST">
<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>"><blockquote><b>Java-Script Hovereffekte</b></td><td class="<? echo $cssSw->getClass() ?>">
<?
IF ($auth->auth["jscript"]) {
	echo "<input type=CHECKBOX name='jshover' value=1";
	IF($forum["jshover"]==1) 
	    echo " checked";
	echo ">";
	}
ELSE echo "Sie m&uuml;ssen in Ihrem Browser Javascript aktivieren um dieses Feature nutzen zu k&ouml;nnen.";
?>
</td><td  class="<? echo $cssSw->getClass() ?>"><br><blockquote>Mit dieser Funktion k&ouml;nnen sie durch reines &Uuml;berfahren der Themen&uuml;berschriften im Forum den entsprechenden Beitrag lesen. Sie k&ouml;nnen sich so sehr schnell und effizient auch durch l&auml;ngere Diskussionen arbeiten. Da jedoch die Ladezeit der Seite erheblich ansteigt, empfehlen wir diese Einstellung nur f&uuml;r Nutzer die mindestens eine ISDN Verbindung haben.<br><br></td></tr>
<tr  <? $cssSw->switchClass() ?>><td  class="<? echo $cssSw->getClass() ?>"><blockquote><b>Neue Beitr&auml;ge immer aufgeklappt</b></td><td class="<? echo $cssSw->getClass() ?>"><input type="CHECKBOX" name="neuauf" value="1"<?IF($forum["neuauf"]==1) echo " checked";?>></td><td  class="<? echo $cssSw->getClass() ?>"><br><blockquote>Neue Postings sind immer automatisch aufgeklappt<br><br></td></tr>
<input type="HIDDEN" name="forumsend" value="bla">
<tr  <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>">&nbsp; </td><td  class="<? echo $cssSw->getClass() ?>" colspan=2><br /><font size=-1><input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 value="&Auml;nderungen &uuml;bernehmen"></font>&nbsp;</td></tr>		
</form>		
</table><br />
<? IF ($forumsend=="anpassen") {
	echo " </td></tr></table>";
	die;
	}