<?
/*
kalenderLinks.php 0.7.5-20010224
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
	
	if($i_page == "kalender.php"){
		?><td class="links1b" align="right" nowrap><a class="links1b" href="kalender.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Terminkalender&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align="absmiddle"></td><?
	}
	else{
		?><td class="links1" align="right" nowrap><a class="links1" href="kalender.php"><font color="#000000" size="2"><b>&nbsp; &nbsp; Terminkalender&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align="absmiddle"></td><?
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


if($i_page == "kalender.php"){
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";
		
	if($cmd == "showday" || $cmd == "add" || $cmd == "del"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=showday<? if($atime) echo "&atime=$atime" ?>">Tag&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=showday<? if($atime) echo "&atime=$atime" ?>">Tag&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	
	if(($i_page == "kalender.php" && $cmd == "") || $cmd == "showweek"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=showweek<? if($atime) echo "&atime=$atime" ?>">Woche&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=showweek<? if($atime) echo "&atime=$atime" ?>">Woche&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}

	if ($cmd == "showmonth"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=showmonth<? if($atime) echo "&atime=$atime" ?>">Monat&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=showmonth<? if($atime) echo "&atime=$atime" ?>">Monat&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}

	if ($cmd == "showyear"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=showyear<? if($atime) echo "&atime=$atime" ?>">Jahr&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=showyear<? if($atime) echo "&atime=$atime" ?>">Jahr&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a> <?
	}
	
	if ($cmd == "edit"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=edit<? if($atime) echo "&atime=$atime" ?>">Bearbeiten&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=edit<? if($atime) echo "&atime=$atime" ?>">Bearbeiten&nbsp;&nbsp;</a> <?
	}
	
	if ($cmd == "bind"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=bind<? if($atime) echo "&atime=$atime" ?>">Einbinden&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=bind<? if($atime) echo "&atime=$atime" ?>">Einbinden&nbsp;&nbsp;</a> <?
	}
	
/*	if ($cmd == "import"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=import<? if($atime) echo "&atime=$atime" ?>">Importieren&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=import<? if($atime) echo "&atime=$atime" ?>">Importieren&nbsp;&nbsp;</a> <?
	} */
	
	if ($cmd == "changeview"){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="kalender.php?cmd=changeview<? if($atime) echo "&atime=$atime" ?>">Ansicht anpassen&nbsp;&nbsp;</a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="kalender.php?cmd=changeview<? if($atime) echo "&atime=$atime" ?>">Ansicht anpassen&nbsp;&nbsp;</a> <?
	}
	
	echo"<br>";
}


elseif ($i_page == "mein_stundenplan.php"){
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";	

	if(!$change_view){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="mein_stundenplan.php">Stundenplan&nbsp; &nbsp; </a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="mein_stundenplan.php">Stundenplan&nbsp; &nbsp; </a> <?
	}

	?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  target="_new" href="mein_stundenplan.php?print_view=TRUE">Druckansicht&nbsp; &nbsp; </a> <?

	if($change_view){
		?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="mein_stundenplan.php?change_view=TRUE">Ansicht anpassen&nbsp; &nbsp; </a> <?
	}
	else{
		?><img src="pictures/forumgrau.gif" border="0"><a class="links2"  href="mein_stundenplan.php?change_view=TRUE">Ansicht anpassen&nbsp; &nbsp; </a> <?
	}

	echo"<br>";
}

else echo"<tr><td class=\"steel1\">&nbsp;";

echo"</td></tr><tr><td class=\"reiterunten\">&nbsp; </td></tr></table>";

?>