<?
/*
* copy_form_visual.inc.php
* used by /public/copy_assi.php
* Part of Alternative Copy Mechanism (ACM) 
* written by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
<form name="copy_form" method="post"
 action="copy_assi.php?cmd=do_copy">
  <table border="0">
    <tbody>
      <tr>
        <td class="blank">&nbsp;</td>
      </tr>
      <tr>
        <td class="blank"><? echo _("Wählen Sie hier einen Namen für die Kopie und welche Daten des gewählten Seminares Sie in die Kopie übernehmen möchten.") ?>
        </td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Seminarname") ?>:</td>
        <td><input size="30" name="newSemName" value='<? echo _("Kopie von ").$SessSemName[0] ?>'</td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Download Ordner") ?>: </td>
        <td><input name="want_download_folder" value="true" type="checkbox"></td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Benutzergruppen") ?>: </td>
        <td><input name="want_status_group" value="true" type="checkbox"></td>
      </tr>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Gruppen Ordner") ?>: </td>
        <td><input name="want_status_group_folder" value="true" onchange="checkRelations()" type="checkbox"></td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Termine") ?>: </td>
        <td><input name="want_schedules" value="true" onchange="checkRelations()" type="checkbox"></td>
      </tr>
      <tr class="steel1">
        <td width="180"><? echo _("Sitzungs Ordner") ?>: </td>
        <td><input name="want_folder_issue" value="true" onchange="checkRelations()" type="checkbox"></td>
      </tr>
      <tr class="steel1">
        <td width="180"><? echo _("als Download Ordner") ?>: </td>
        <td><input name="want_issue_folder_as_download_folder" value="true" onchange="checkRelations()" type="checkbox"></td>
      </tr>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Forenthemen") ?>: </td>
        <td><input name="want_discussion_issue" value="true" onchange="checkRelations()" type="checkbox"></td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Foreneinträge") ?>: </td>
        <td><input name="want_discussion" value="true" type="checkbox"></td>
      </tr>
      <tr class="steel1">
        <td width="180"><? echo _("Internes Wiki") ?>: </td>
        <td><input name="copy_wiki" value="true" type="checkbox"></td>
      </tr>
      <tr class="steelgraulight">
        <td width="180"><? echo _("Info Seite") ?>: </td>
        <td><input name="copy_scm" value="true" type="checkbox"></td>
      </tr>
    </tbody>
  </table>
  <br>
  <table border="0">
    <tbody>
      <tr colspan="2">
        <td> <input value="<? echo _("Kopiervorgang starten")?>" type="submit"> <input value="<? echo _("Zur&uuml;cksetzen")?> type="reset"></td>
      </tr>
    </tbody>
  </table>
</form>

	<? 
	echo "SeminarID: ".$SessSemName[1];
	?>
	</blockquote>
	<br />
	</td></tr>
	</table>

<script language="JavaScript" type="text/javascript">
<!--
function checkRelations()
{
	//document.copy_form.want_download_folder.value=="true";
	
	if ( document.copy_form.want_issue_folder_as_download_folder.checked==true && document.copy_form.want_folder_issue.checked==true )
	{
		document.copy_form.want_folder_issue.checked=false;
		alert(<? echo _("'Hinweis !  \n\n Sitzungsordner können entweder als \"Sitzungsordner\" oder als \n \"Download Ordner\" kopiert werden.\n \n Im ersten Fall ist auch das Kopieren von Terminen nötig, im zweiten Fall nicht.'")?>);
	}
	
	if ( document.copy_form.want_folder_issue.checked==true || document.copy_form.want_discussion_issue.checked==true )
	{
		if ( document.copy_form.want_schedules.checked==false )
		{
			document.copy_form.want_schedules.checked=true;
			alert(<? echo _("'Hinweis ! \n\nWenn Sitzungordner oder Forenthemen kopiert werden sollen, \nmüssen auch die Termine kopiert werden, da diese \nOrdnertypen mit Terminen verknüft sind.'")?>);
		}
	}
	
	if ( document.copy_form.want_status_group_folder.checked == true )
	{
		document.copy_form.want_status_group.checked = true;
	}
}
//-->
</script>

