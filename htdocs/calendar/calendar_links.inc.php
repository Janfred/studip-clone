<?
/*
calendar_links.inc.php 0.8-20020701
Reiternavigation im Bereich 'Mein Terminkalender'
und 'Mein Stundenplan' in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthien@gmx.de>

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

?>

<table cellpadding="0" cellspacing="0" border="0">
	<tr>

<?
	
	if($i_page == "calendar.php"){
		?><td class="links1b" align="right" nowrap><a class="links1b" href="calendar.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Terminkalender&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align="absmiddle"></td><?
	}
	else{
		?><td class="links1" align="right" nowrap><a class="links1" href="calendar.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Terminkalender&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align="absmiddle"></td><?
	}

	if($i_page == "mein_stundenplan.php"){
		?><td class="links1b" align="right" nowrap><a class="links1b" href="mein_stundenplan.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Stundenplan&nbsp; &nbsp; </b></font></a><img src="pictures/reiter4.jpg" align="absmiddle"></td><?
	}
	else{
		?><td class="links1" align="right" nowrap><a class="links1" href="mein_stundenplan.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Stundenplan&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align="absmiddle"></td><?
	}

?>
	</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="100%">

<?


if($i_page == "calendar.php"){
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";
		
	if($cmd == "showday" || $cmd == "add" || $cmd == "del"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=showday<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Tag&nbsp; &nbsp; &nbsp; </b></b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=showday<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Tag&nbsp; &nbsp; &nbsp; </b></font></a><?
	}
	
	if(($i_page == "calendar.php" && $cmd == "") || $cmd == "showweek"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=showweek<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Woche &nbsp; &nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=showweek<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Woche &nbsp; &nbsp;</b></font></a><?
	}

	if ($cmd == "showmonth"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=showmonth<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Monat &nbsp; &nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=showmonth<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Monat &nbsp; &nbsp;</b></font></a><?
	}

	if ($cmd == "showyear"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=showyear<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Jahr &nbsp; &nbsp; </b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=showyear<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Jahr &nbsp; &nbsp; </b></font></a><?
	}
	
	if ($cmd == "edit"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=edit<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Bearbeiten &nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=edit<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Bearbeiten &nbsp;</b></font></a><?
	}
	
	if ($cmd == "bind"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=bind<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Einbinden &nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=bind<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Einbinden &nbsp;</b></font></a><?
	}
	
/*	if ($cmd == "import"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=import<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Importieren&nbsp;&nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=import<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Importieren&nbsp;&nbsp;</b></font></a><?
	} */
	
	if ($cmd == "changeview"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="calendar.php?cmd=changeview<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Ansicht anpassen &nbsp;</b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="calendar.php?cmd=changeview<? if($atime) echo "&atime=$atime" ?>"><font color="#000000" size="2"><b>Ansicht anpassen &nbsp;</b></font></a><?
	}
	
	echo"<br />";
}


elseif ($i_page == "mein_stundenplan.php"){
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";	

	if(!$change_view){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="mein_stundenplan.php"><font color="#000000" size="2"><b>Stundenplan&nbsp; &nbsp; </b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="mein_stundenplan.php"><font color="#000000" size="2"><b>Stundenplan&nbsp; &nbsp; </b></font></a><?
	}

	?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  target="_new" href="mein_stundenplan.php?print_view=TRUE"><font color="#000000" size="2"><b>Druckansicht&nbsp; &nbsp; </b></font></a><?

	if($change_view){
		?><img src="pictures/forumrot.gif" border="0"><a class="links1"  href="mein_stundenplan.php?change_view=TRUE"><font color="#000000" size="2"><b>Ansicht anpassen&nbsp; &nbsp; </b></font></a><?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links1"  href="mein_stundenplan.php?change_view=TRUE"><font color="#000000" size="2"><b>Ansicht anpassen&nbsp; &nbsp; </b></font></a><?
	}

	echo"<br />";
}

else echo"<tr><td class=\"steel1\">&nbsp;";

echo"</td></tr><tr><td class=\"reiterunten\">&nbsp; </td></tr></table>";

?>
