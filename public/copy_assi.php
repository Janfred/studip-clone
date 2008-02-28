<?php

/*
copy_assi.php - Dummy zum Einstieg in Veranstaltungskopieren
Copyright (C) 2004 Tobias Thelen <tthelen@uni-osnabrueck.de>

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
/*

* Modified/Extended Version to support an alternative copy mechanism (ACM)
* Based on Studip 1.6.0-1
* Alternative Copy Mechanism (ACM) Version 0.5
* Copyright (C) 2008 Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("dozent");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once 'lib/functions.php';

// -- here you have to put initialisations for the current page

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Kopieren der Veranstaltung");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//get ID from a open Institut
if ($SessSemName[1])
$header_object_id = $SessSemName[1];
else
$header_object_id = $admin_admission_data["sem_id"];

//Change header_line if open object
$header_line = getHeaderLine($header_object_id);
if ($header_line)
$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

require_once 'lib/visual.inc.php';

if ($SessSemName[1] && !$cmd) {
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
	<? 
	printf(_("Die Veranstaltung wurde zum Kopieren ausgewählt."). " <br><br>");
	printf(_("Sie haben an dieser Stelle zwei Möglichkeiten. <br><br>"));
	printf(_("1. Sie kopieren nach Ihre Veranstaltung mit der alten Kopierfunktion, via Assistent.<br><br>"));
	printf(_("&nbsp; &nbsp; Diese Methode hat allerdings den Nachteil, das keine Inhalte kopiert werden können.<br><br>"));
	printf(_("2. Sie kopieren mit der neuen Kopierfunktion<br><br>"));
	printf(_("&nbsp; &nbsp; Bei dieser Methode geben Sie nur den neuen Titel für die Kopie an und die Inhalte, die in die Kopie übernommen werden sollen<br><br>"));

	printf(_("Für Methode 1 klicken Sie %shier%s."),'<a href="admin_seminare_assi.php?cmd=do_copy&cp_id='.$SessSemName[1].'&start_level=TRUE&class=1">','</a><br><br>');

	printf(_("Für Methode 2 klicken Sie %shier%s."),'<a href="copy_assi.php?cmd=show_copy_form">','</a><br><br>');
	?>

	</blockquote>
	<br />
	</td></tr>
	</table>
<?php
}

if ($cmd=="show_copy_form")
{
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
		<form name ="copy_form" method="POST" action="copy_assi.php?cmd=do_copy" >
		<table width="300px">
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>Wählen Sie hier einen Namen für die Kopie und welche Daten des gewählten Seminares Sie in die Kopie übernehmen möchten.
	</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr class="steelgraulight" ><td width="8%">Seminarname:</td><td><input type="text" name="newSemName" value="<? echo $SessSemName[0] ?>" ></td></tr>
			<tr class="steel1" ><td>Download Ordner: </td><td><input type="checkbox" name="want_download_folder" value="true" ></td></tr>
			<tr class="steelgraulight" ><td>Benutzergruppen: </td><td><input type="checkbox" name="want_status_group" value="true"></td></tr>
			<tr class="steel1" ><td>Gruppen Ordner: </td><td><input type="checkbox" name="want_status_group_folder" value="true" onChange="checkRelations()" ></td></tr>
			<tr class="steelgraulight" ><td>Termine: </td><td><input type="checkbox" name="want_schedules" value="true" ></td></tr>
			<tr class="steel1" ><td>Sitzungs Ordner: </td><td><input type="checkbox" name="want_folder_issue" value="true" onChange="checkRelations()" ></td></tr>
			<tr class="steelgraulight" ><td>Forenthemen: </td><td><input type="checkbox" name="want_discussion_issue" value="true" onChange="checkRelations()" ></td></tr>
			<tr class="steel1" ><td>Foreneinträge: </td><td><input type="checkbox" name="want_discussion" value="true" ></td></tr>
			<tr class="steelgraulight" ><td>Internes Wiki: </td><td><input type="checkbox" name="copy_wiki" value="true" ></td></tr>
			<tr class="steel1" ><td>Info Seite: </td><td><input type="checkbox" name="copy_scm" value="true" ></td></tr>
			<tr colspan="2"><td> <input type="submit" value="Kopiervorgang starten"> </td></tr>
		</table>
	</form>
	<? 
	echo "SeminarID: ".$SessSemName[1];
	?>
	</blockquote>
	<br />
	</td></tr>
	</table>
	<?
}

if ($cmd=="do_copy")
{

	if ($auth->auth['perm'] == 'dozent' || $auth->auth['perm'] == 'admin' || $auth->auth['perm'] == 'root')
	{
		require_once('lib/classes/tools/DataCopyTool.class.php');

		echo "Sie dürfen kopieren";
		$user_id = $auth->auth['uid'];

		$source_seminar_id = $SessSemName[1];

		$copyTool = new DataCopyTool($source_seminar_id,null,$user_id);

		$newSemData["Name"]=$newSemName;

		$target_seminar_id = $copyTool->copySeminar( $newSemData );

		if ( $copy_scm == "true" ) // simple to copy needs no tree structure, so we do it diectly
		{
			$copyTool->copyScm();
		}

		if ( $copy_wiki == "true" ) // simple to copy needs no tree structure, so we do it diectly
		{
			$copyTool->copyWiki();
		}

		if ( $want_download_folder  || $want_schedules || $want_status_group || $want_discussion )
		{
			if 	( $want_download_folder )
			{
				$wantFlags["want_download_folder"] = $want_download_folder=="true";
			}

			if 	( $want_discussion )
			{
				$wantFlags["want_discussion"] = $want_discussion=="true";
			}
			
			if 	( $want_schedules )
			{
				$wantFlags["want_schedules"] = $want_schedules=="true";

				if 	( $want_folder_issue )
				{
					$wantFlags["want_folder_issue"] = $want_folder_issue=="true";
				}
				if 	( $want_discussion_issue )
				{
					$wantFlags["want_discussion_issue"] = $want_discussion_issue=="true";
				}
			}
			if 	( $want_status_group )
			{
				$wantFlags["want_status_group"] = $want_status_group=="true";

				if 	( $want_status_group_folder )
				{
					$wantFlags["want_status_group_folder"] = $want_status_group_folder=="true";
				}
			}
			$copyTool->copyContentData($wantFlags);
		}
		
		echo "<br><br><a href='seminar_main.php?auswahl=$target_seminar_id'>Zur neuen Veranstaltung</a>";

	}
	else
	{
		echo "Sie haben nicht die Berechtigung diese Veranstaltung zu kopieren";
	}

}
?>
<script language="JavaScript" type="text/javascript">
function checkRelations()
{
	//document.copy_form.want_download_folder.value=="true";
	
	if ( document.copy_form.want_folder_issue.checked==true || document.copy_form.want_discussion_issue.checked==true )
	{
		document.copy_form.want_schedules.checked=true;
	}
	
	if ( document.copy_form.want_status_group_folder.checked == true )
	{
		document.copy_form.want_status_group.checked = true;
	}
}
</script>
<?
include ('lib/include/html_end.inc.php');
page_close();
?>