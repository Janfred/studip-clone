<?
/*
blockveranstaltungs_assistent.php - Terminverwaltung von Stud.IP

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");

include ("seminar_open.php"); // initialise Stud.IP-Session
require_once("blockveranstaltungs_assistent.inc.php");
require_once("lib/functions.php");

include ("html_head.inc.php"); // Output of html head

echo "<SCRIPT> function reload_opener() { opener.location.href='".$CANONICAL_RELATIVE_PATH_STUDIP."raumzeit.php#irregular_dates'; return true;} </SCRIPT>";

if (isset($_POST['command']) && ($_POST['command'] == 'create')) {
	$return = create_block_schedule_dates($SessSemName[1],$_POST);
}

// HTML Template
?>
<table border="0" cellspacing="0" cellpadding="3" align="center">
	<tr>
		<td class="topic">
			<b>Blockveranstaltungstermine anlegen - <?=$SessSemName[0]?></b>
		</td>
	</tr>
	<tr>
		<td class="blank">
			<?
				if (!$return['ready'] && ($return['errors'])) {
					echo "<br/>";
					foreach($return['errors'] as $error) {
						echo "&nbsp;<font align=\"center\" color=\"red\"><b>$error</b></font><br/>&nbsp;";
					}
					echo "<br/>";
				}

				if ($return['ready']) {
					echo "<br/>";
					echo "<b>"._("Für folgende Termine wurden die gewählten Aktionen durchgeführt").":</b>";
					echo "<br/>";
					foreach ($return['status'] as $status) {
						echo "<LI>".$status."<br/>";
					}
					echo "<br/>";
				}
			?>
			<form method="post" action="<?=$PHP_SELF?>">
				<input type="hidden" name="command" value="create" />
				<table border="0" cellspacing="1" cellpadding="1">
					<tr>
						<td colspan="2">
							<p> <?=sprintf(_("Die Veranstaltung %s findet in folgendem Zeitraum statt"), $SessSemName[0])?>:</p>
						</td>
					</tr>
					<tr>
						<td>
							<?=_("Startdatum")?>:
						</td>
						<td>
							<input type="text" size="2" maxlength="2" name="start_day" value="<?=$_POST['start_day']?>" />
							<input type="text" size="2" maxlength="2" name="start_month" value="<?=$_POST['start_month']?>" />
							<input type="text" size="4" maxlength="4" name="start_year" value="<?=$_POST['start_year']?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?=_("Enddatum")?>:
						</td>
						<td>
							<input type="text" size="2" maxlength="2" name="end_day" value="<?=$_POST['end_day']?>" />
							<input type="text" size="2" maxlength="2" name="end_month" value="<?=$_POST['end_month']?>" />
							<input type="text" size="4" maxlength="4" name="end_year" value="<?=$_POST['end_year']?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
							<p><?=_("Die Veranstaltung findet zu folgenden Zeiten statt")?>:</p>
						</td>
					</tr>
					<tr>
						<td>
							Start:
						</td>
						<td>
							<input type="text" size="2" maxlength="2" name="start_hour" value="<?=$_POST['start_hour']?>" />
							<input type="text" size="2" maxlength="2" name="start_minute" value="<?=$_POST['start_minute']?>" />
						</td>
					</tr>
					<tr>
						<td>
							Ende:
						</td>
						<td>
							<input type="text" size="2" maxlength="2" name="end_hour" value="<?=$_POST['end_hour']?>" />
							<input type="text" size="2" maxlength="2" name="end_minute" value="<?=$_POST['end_minute']?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
							<p><?=_("Die Veranstaltung findet an folgenden Tagen statt")?>:</p>
							<input type="checkbox" name="every_day" value="1" <?=($_POST["every_day"]=='1'?"checked=checked":"")?> />&nbsp;Jeden Tag<br/>
								<br>
							<input type="checkbox" name="days[]" value="Monday"<?=day_checked('Monday')?> />&nbsp;Montag<br/>
							<input type="checkbox" name="days[]" value="Tuesday"<?=day_checked('Tuesday')?> />&nbsp;Dienstag<br/>
							<input type="checkbox" name="days[]" value="Wednesday"<?=day_checked('Wednesday')?> />&nbsp;Mittwoch<br/>
							<input type="checkbox" name="days[]" value="Thursday"<?=day_checked('Thursday')?> />&nbsp;Donnerstag<br/>
							<input type="checkbox" name="days[]" value="Friday"<?=day_checked('Friday')?> />&nbsp;Freitag<br/>
							<input type="checkbox" name="days[]" value="Saturday"<?=day_checked('Saturday')?> />&nbsp;Samstag<br/>
							<input type="checkbox" name="days[]" value="Sunday"<?=day_checked('Sunday')?> />&nbsp;Sonntag<br/>
							<br/>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<button type='submit' name='block_submit' value='clicked'/>Veranstaltungstermine erzeugen</button>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
	<tr>
		<td class="blank" align="center">
			<br/>
			<a href="javascript:reload_opener();self.close()">Assistent schließen</a>
		</td>
	</tr>
</table>
<?page_close(NULL);?>
