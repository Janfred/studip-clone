<?php
/*
GUI for Seminar.class.php und all aggregated classes
Copyright (C) 2005-2007 Till Glöggler <tgloeggl@uos.de>

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

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenAendernVonZeitenUndTerminen";

// -- here you have to put initialisations for the current page
$sess->register('sd_open');
$sess->register('raumzeitFilter');

if ($list) {
	$sess->unregister('temporary_id');
	unset($temporary_id);
}

if (isset($_REQUEST['seminar_id'])) {
	$sess->register('temporary_id');
	$temporary_id = $_REQUEST['seminar_id'];
}

if (isset($temporary_id)) {
	$id = $temporary_id;
} else {
	$id = $SessSemName[1];
}


require_once ('lib/classes/Seminar.class.php');
require_once ('lib/raumzeit/raumzeit_functions.inc.php');
require_once ('lib/dates.inc.php');

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
	$resList = new ResourcesUserRoomsList($user->id, TRUE, FALSE, TRUE);
}

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Verwaltung von Zeiten und Raumangaben");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//Change header_line if open object
$header_line = getHeaderLine($id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

if (!$perm->have_studip_perm('tutor', $id)) {
	die;
}

unQuoteAll();

$sem = new Seminar($id);
$sem->checkFilter();

$semester = new SemesterData();
$_LOCKED = FALSE;
if ($SEMINAR_LOCK_RULE) {
	require_once ('/lib/classes/LockRules.class.php');
	$lockRule = new LockRules();
	$data = $lockRule->getSemLockRule($id);
	if ($data['attributes']['room_time'] && !$perm->have_perm('admin')) {
		$_LOCKED = TRUE;
		$sem->createInfo(_("Diese Seite ist für die Bearbeitung gesperrt. Sie können die Daten einsehen, jedoch nicht verändern."));
	}
}

// Workaround for multiple submit buttons
foreach ($_REQUEST as $key => $val) {
	if ( ($key[strlen($key)-2] == '_') && ($key[strlen($key)-1] == 'x') ) {
		$cmd = substr($key, 0, (strlen($key) - 2));
	}
}

// what to do with the text-field
if ($GLOBALS['RESOURCES_ENABLE']) {
	if ( (($_REQUEST['freeRoomText'] != '') && ($_REQUEST['room'] != 'nothing')) || (($_REQUEST['freeRoomText_sd'] != '') && ($_REQUEST['room_sd'] != 'nothing'))) {
		$sem->createError("Sie k&ouml;nnen nur eine freie Raumangabe machen, wenn sie \"keine Buchung, nur Textangabe\" ausw&auml;hlen!");
		unset($_REQUEST['freeRoomText']);
		unset($_REQUEST['room']);
		unset($_REQUEST['freeRoomText_sd']);
		unset($_REQUEST['room_sd']);
		unset($cmd);
		$open_close_id = $_REQUEST['singleDateID'];
		$cmd = 'open';
	}
}

require_once('lib/raumzeit.inc.php');
$sem->registerCommand('open', 'raumzeit_open');
$sem->registerCommand('close', 'raumzeit_close');
$sem->registerCommand('delete_singledate', 'raumzeit_delete_singledate');
$sem->registerCommand('undelete_singledate', 'raumzeit_undelete_singledate');
$sem->registerCommand('checkboxAction', 'raumzeit_checkboxAction');
$sem->registerCommand('bookRoom', 'raumzeit_bookRoom');
$sem->registerCommand('selectSemester', 'raumzeit_selectSemester');
$sem->registerCommand('addCycle', 'raumzeit_addCycle');
$sem->registerCommand('doAddCycle', 'raumzeit_doAddCycle');
$sem->registerCommand('editCycle', 'raumzeit_editCycle');
$sem->registerCommand('deleteCycle', 'raumzeit_deleteCycle');
$sem->registerCommand('doDeleteCycle', 'raumzeit_doDeleteCycle');
$sem->registerCommand('doAddSingleDate', 'raumzeit_doAddSingleDate');
$sem->registerCommand('editSingleDate', 'raumzeit_editSingleDate');
$sem->registerCommand('editDeletedSingleDate', 'raumzeit_editDeletedSingleDate');
$sem->registerCommand('freeText', 'raumzeit_freeText');
$sem->registerCommand('removeRequest', 'raumzeit_removeRequest');
$sem->registerCommand('removeSeminarRequest', 'raumzeit_removeSeminarRequest');
$sem->processCommands();

// create infobox with semester-chooser and status-messages
$messages = array();

while ($msg = $sem->getNextMessage()) {
	$messages[] = $msg;
}

// get possible start-weeks
$start_weeks = array();

$tmp_first_date = getCorrectedSemesterVorlesBegin(get_sem_num($sem->getStartSemester()));
$all_semester = $semester->getAllSemesterData();

foreach ($all_semester as $val) {
	if ( ($val['beginn'] < $tmp_first_date) && ($val['ende'] > $tmp_first_date) ) {
		$end_date = $val['vorles_ende'];
	}
}

$i = 0;
while ($tmp_first_date < $end_date) {
	$start_weeks[$i]['text'] = ($i+1) .'. '. _("Startwoche") .' ('. _("ab") .' '. date("d.m.Y",$tmp_first_date).')';
	$start_weeks[$i]['selected'] = ($sem->getStartWeek() == $i);

	$i++;
	$tmp_first_date = $tmp_first_date + (7 * 24 * 60 * 60);
}

// template-like output
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="blank" width="100%" align="center" valign="top">
			<table width="99%" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td colspan="9" class="blue_gradient">
					&nbsp;<B><?=_("Regelmäßige Zeiten")?></B>
				</td>
			</td>
				<tr>
					<TD colspan="9" class="blank">
						<? if (!$_LOCKED) { ?>
						<FORM action="<?=$PHP_SELF?>" method="post">
						<? } ?>
						<FONT size="-1">
						&nbsp;<?=_("Startsemester")?>:&nbsp;
						<?
							if ($perm->have_perm('tutor')) {
								echo "<SELECT name=\"startSemester\">\n";
								$all_semester = $semester->getAllSemesterData();
								foreach ($all_semester as $val) {
									echo '<OPTION value="'.$val['beginn'].'"';
									if ($sem->getStartSemester() == $val['beginn']) {
										echo ' selected';
									}
									echo '>'.$val['name']."</OPTION>\n";
								}
								echo "</SELECT>\n";
							} else {
								$all_semester = $semester->getAllSemesterData();
								foreach ($all_semester as $val) {
									if ($sem->getStartSemester() == $val['beginn']) {
										echo $val["name"];
									}
								}
							}
						?>
						, <?=_("Dauer")?>:
						<? if (!$_LOCKED) { ?>
						<SELECT name="endSemester">
							<OPTION value="0"<?=($sem->getEndSemester() == 0) ? ' selected' : ''?>>1 <?=_("Semester")?></OPTION>
							<?
							if ($perm->have_perm("admin")) {		// admins or higher may do everything
								foreach ($all_semester as $val) {
									if ($val['beginn'] > $sem->getStartSemester()) {		// can be removed, if we always need all Semesters
										echo '<OPTION value="'.$val['beginn'].'"';
										if ($sem->getEndSemester() == $val['beginn']) {
											echo ' selected';
										}
										echo '>'.$val['name'].'</OPTION>';
									}
								}
								?>
								<OPTION value="-1"<?=($sem->getEndSemester() == -1) ? 'selected' : ''?>><?=_("unbegrenzt")?></OPTION>
								<?
							} else {		// dozent or tutor may only selecte a duration of one or two semesters or what admin has choosen
								$sem2 = '';
								foreach ($all_semester as $val) {
									if (($sem2 == '') && ($val['beginn'] > $sem->getStartSemester())) {
										echo '<OPTION value="'.$val['beginn'].'"'.(($sem->getEndSemester() == $val['beginn']) ? ' selected' : '').'>2 '._("Semester").'</OPTION>';
										$sem2 = $val['beginn'];
									}
									if ( ($val['beginn'] == $sem->getEndSemester() && ($sem2 != $val['beginn']))) {
										echo '<OPTION value="'.$val['beginn'].'"'.(($sem->getEndSemester() == $val['beginn']) ? ' selected' : '').'>'.$val['name'].'</OPTION>';
									}
								}
								if ($sem->getEndSemester() == -1) {
									?>
									<OPTION value="-1" selected>unbegrenzt</OPTION>
									<?
								}
							}
							?>
						</SELECT>
						<? } else {
							switch ($sem->getEndSemester()) {
								case '0':
									echo _("1 Semester");
									break;

								case '-1':
									echo _("unbegrenzt");
									break;
								
								default:
									foreach ($all_semester as $val) {
										if ($val['beginn'] == $sem->getEndSemester()) {
											echo $val['beginn'];
										}
									}
									break;
							}
						} ?>
						&nbsp;&nbsp;
						<br />
						<?=_("Turnus")?>:
						<? if (!$_LOCKED) { ?>
						<SELECT name="turnus">
							<OPTION value="0"<?=$sem->getTurnus() ? '' : 'selected'?>><?=_("w&ouml;chentlich");?></OPTION>
							<OPTION value="1"<?=$sem->getTurnus() ? 'selected' : ''?>><?=_("zweiw&ouml;chentlich")?></OPTION>
						</SELECT>
						<? } else {
							echo (!$sem->getTurnus()) ? _("w&ouml;chentlich") : _("zweiw&ouml;chentlich");
						} ?>
						&nbsp;&nbsp;
						<?=_("beginnt in der")?>:
						<? if (!$_LOCKED) { ?>
						<select name="startWeek">
						<?
							foreach ($start_weeks as $value => $data) :

								echo '<option value="'.$value.'"';
								if ($data['selected']) echo ' selected="selected"';
								echo '>'.$data['text'].'</option>', "\n";

							endforeach;
						?>
						</SELECT>
						</FONT>
						&nbsp;&nbsp;
						<INPUT type="image" <?=makebutton('uebernehmen', 'src')?> align="absmiddle">
						<INPUT type="hidden" name="cmd" value="selectSemester">
						</FORM>
						<? } else {
							echo ($sem->getStartWeek() + 1) .'. '. _("Semesterwoche");
						} ?>
					</TD>
				</TR>
				<?
				$turnus = $sem->getFormattedTurnusDates();		// string representation of all CycleData-objects is retrieved as an associative array: key: CycleDataID, val: string
					//TODO: string representation should not be collected by a big array, but with the toString method of the CycleData-object
					foreach ($sem->metadate->cycles as $metadate_id => $val) {		// cycle trough all CycleData objects
						if (!$tpl['room'] = $sem->getFormattedPredominantRooms($metadate_id)) {		// getPredominantRoom returns the predominant booked room
							$tpl['room'] = _("keiner");
						}

						/* get StatOfNotBookedRooms returns an array:
						 * open:  			number of rooms with no booking
						 * all:					number of singleDates, which can have a booking
						 * open_rooms:	array of singleDates which have no booking
						 */
						$tpl['ausruf'] = $sem->getBookedRoomsTooltip($metadate_id);
						$tpl['anfragen'] = $sem->getRequestsInfo($metadate_id);
						$tpl['class'] = $sem->getCycleColorClass($metadate_id);

						$tpl['md_id'] = $metadate_id;
						$tpl['date'] = $turnus[$metadate_id];
						$tpl['mdDayNumber'] = $val->day;
						$tpl['mdStartHour'] = $val->start_stunde;
						$tpl['mdEndHour'] = $val->end_stunde;
						$tpl['mdStartMinute'] = $val->start_minute;
						$tpl['mdEndMinute'] = $val->end_minute;
						$tpl['mdDescription'] = htmlReady($val->description);

						include('lib/raumzeit/templates/metadate.tpl');

						if ($sd_open[$metadate_id]) {
							$termine =& $sem->getSingleDatesForCycle($metadate_id);
							?>
							<FORM action="<?=$PHP_SELF?>" method="post" name="Formular">
							<INPUT type="hidden" name="cycle_id" value="<?=$metadate_id?>">
				<TR>
					<TD align="center" colspan="9" class="steel1">
						<TABLE cellpadding="1" cellspacing="0" border="0" width="90%">
							<?
							$every2nd = 1;
							$all_semester = $semester->getAllSemesterData();
							$grenze = 0;
							if (sizeof($termine) == 0) {
								foreach ($all_semester as $val) {
									if ($val['beginn'] == $raumzeitFilter) {
										$sem_name = $val['name'];break;
									}
								}
								parse_msg('error§'.sprintf(_("Für das %s gibt es für diese regelmäßige Zeit keine Termine!"), '<b>'.$sem_name.'</b>').'§', '§', 'steel1');
							} else foreach ($termine as $singledate_id => $val) {
								if ( ($grenze == 0) || ($grenze < $val->getStartTime()) ) {
									foreach ($all_semester as $zwsem) {
										if ( ($zwsem['beginn'] < $val->getStartTime()) && ($zwsem['ende'] > $val->getStartTime()) ) {
											$grenze = $zwsem['ende'];
											?>
											<TR>
												<TD class="steelgraulight" align="center" colspan="9">
													<B><?=$zwsem['name']?></B>
												</TD>
											</TR>
											<?
										}
									}
								}
								// Template fuer einzelnes Datum
								$tpl['checked'] = '';
								$tpl = getTemplateDataForSingleDate($val, $metadate_id);
								$tpl['cycle_sd'] = TRUE;

								if ($sd_open[$singledate_id] && ($open_close_id == $singledate_id)) {
									include('lib/raumzeit/templates/openedsingledate.tpl');
								} else {
									unset($sd_open[$singledate_id]);
									include('lib/raumzeit/templates/singledate.tpl');
								}
								// Ende Template einzelnes Datum
							}
							?>
						</TABLE>
					</TD>
				</TR>
				<? if (sizeof($termine) > 0) : ?>
				<TR>
					<TD class="steel1" colspan="9" align="center">
						<?
							$tpl['width'] = '90%';
							$tpl['cycle_id'] = $metadate_id;
							include('lib/raumzeit/templates/actions.tpl');
						?>
					</TD>
				</TR>
				<?
				endif;
						}
						echo "</FORM>";
					}

				if ($newCycle) {
			?>
				<TR>
					<?
					if (isset($_REQUEST['day'])) {
						$tpl['day'] = $_REQUEST['day'];	
					} else {
						$tpl['day'] = 1;
					}
					$tpl['start_stunde'] = $_REQUEST['start_stunde'];	
					$tpl['start_minute'] = $_REQUEST['start_minute'];	
					$tpl['end_stunde'] = $_REQUEST['end_stunde'];	
					$tpl['end_minute'] = $_REQUEST['end_minute'];	
					include('lib/raumzeit/templates/addcycle.tpl')
					?>
				</TR>
			<?
				}
			?>
				<? if (!$_LOCKED) { ?>
				<TR>
					<TD class="blank" colspan="9">
						<br />
						<font size="-1">
							&nbsp;&nbsp;
							<?=_("Regelmäßigen Zeiteintrag")?>
							<a href="<?=$PHP_SELF?>?cmd=addCycle#newCycle">
								<img <?=makebutton('hinzufuegen', 'src')?> border="0" align="absmiddle">
							</a>
						</font>
					</TD>
				</TR>
				<? } ?>
				<TR>
					<TD colspan="9" class="blank">&nbsp;</TD>
				</TR>
				<TR>
					<TD colspan="9" class="blue_gradient">
						<A name="irregular_dates">
						&nbsp;<B><?=_("Unregelm&auml;&szlig;ige Termine/Blocktermine")?></B>
					</TD>
				</TR>
				<? if ($termine =& $sem->getSingleDates(true, true)) { ?>
				<TR>
					<TD align="center" colspan="9" class="steel1">
						<FORM action="<?=$PHP_SELF?>" method="post" name="Formular">
						<TABLE cellpadding="1" cellspacing="0" border="0" width="100%">
							<?
							$count = 0;
							$every2nd = 1;
							$grenze = 0;
							foreach ($termine as $key => $val) {
								$tpl['checked'] = '';
								$tpl = getTemplateDataForSingleDate($val);

								if ( ($grenze == 0) || ($grenze < $val->getStartTime()) ) {
									foreach ($all_semester as $zwsem) {
										if ( ($zwsem['beginn'] < $val->getStartTime()) && ($zwsem['ende'] > $val->getStartTime()) ) {
											$grenze = $zwsem['ende'];
											?>
											<TR>
												<TD class="steelgraulight" align="center" colspan="9">
													<B><?=$zwsem['name']?></B>
												</TD>
											</TR>
											<?
										}
									}
								}

								if ($sd_open[$val->getSingleDateID()] && ($open_close_id == $val->getSingleDateID())) {
									include('lib/raumzeit/templates/openedsingledate.tpl');
								} else {
									unset($sd_open[$val->getSingleDateID()]);
									include('lib/raumzeit/templates/singledate.tpl');
								}
								$count++;
							}
							?>
						</TABLE>
				<? } ?>
				<? if ($count) { ?>
						<?
							$tpl['width'] = '100%';
							include('lib/raumzeit/templates/actions.tpl');
						?>
						</FORM>
					</TD>
				</TR>
				<? } ?>


				<tr>
					<td colspan="9" class="blank">&nbsp;</td>
				</tr>

				<? if (!$_LOCKED) { ?>
				<TR>
					<TD>
					<SCRIPT type ="text/javascript">
					function block_fenster () {
						f1 = window.open("blockveranstaltungs_assistent.php", "Zweitfenster", "width=500,height=600,toolbar=no, menubar=no, scrollbars=yes");
						f1.focus();
					}
					</SCRIPT>
						<FONT size="-1">
							&nbsp;<?=_("Blockveranstaltungstermine")?>
						</FONT>
						 <A href="javascript:window.block_fenster()"><?=makebutton("anlegen")?></A>
					</TD>
				</TR>
				<? if (isset($cmd) && ($cmd == 'createNewSingleDate')) {
					include('lib/raumzeit/templates/addsingledate.tpl');
				} else { ?>
				<TR>
					<TD colspan="9" class="blank">
						<FONT size="-1">
							&nbsp;einen neuen Termin
							<A href="<?=$PHP_SELF?>?cmd=createNewSingleDate#newSingleDate">
								<IMG <?=makebutton('erstellen', 'src')?> align="absmiddle" border="0">
							</A>
						</FONT>
					</TD>
				</TR>
				<? } ?>
				<tr>
					<td class="blank" colspan="9">&nbsp;</td>
				</tr>
				<?
				}

				if (!$_LOCKED && $RESOURCES_ENABLE && $RESOURCES_ALLOW_ROOM_REQUESTS) { ?>
				<tr>
					<td colspan="9" class="blue_gradient">
						<a name="irregular_dates">
						&nbsp;<b><?=_("Raum anfordern")?></b>
					</td>
				</tr>
				<tr>
					<td class="blank" colspan="9" style="padding-left: 6px">
						<font size="-1">
							<?=_("Hier können Sie für die gesamte Veranstaltung, also für alle regelmäßigen und unregelmäßigen Termine, eine Raumanfrage erstellen. Um für einen einzelnen Termin eine Raumanfrage zu erstellen, klappen Sie diesen auf und wählen dort \"Raumanfrage erstellen\"");?>
						</font>
					</td>
				</tr>
				<tr>
					<td class="blank" colspan="9">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="blank" colspan="9">
						<?
						if ($sem->hasRoomRequest()) {
							$req_info = $sem->getRoomRequestInfo();
						?>
						<div style="{border:1px solid black;background:#FFFFDD}">
							&nbsp;<?=_("Für diese Veranstaltung liegt eine noch offene Raumanfrage vor.")?>
							<a href="javascript:alert('<?=$req_info?>')">
								<img src="<?=$GLOBALS['ASSETS_URL']?>images/info.gif" alt="<?=$req_info?>" border="0" align="absmiddle">
							</a>
						</div>
						<br />
						<? } ?>
						<font size="-1">
							&nbsp;Raumanfrage
							<A href="admin_room_requests.php?seminar_id=<?=$id?>">
								<? if ($req_info) {
								?>
									<img <?=makebutton('bearbeiten', 'src')?> align="absmiddle" border="0">
								<?
								} else {
								?>
									<img <?=makebutton('erstellen', 'src')?> align="absmiddle" border="0">
								<?
								} ?>
							</A>
							<? if ($req_info) { ?>
							&nbsp;oder&nbsp;
							<A href="<?=$PHP_SELF?>?cmd=removeSeminarRequest">
								<img <?=makebutton('zurueckziehen', 'src')?> align="absmiddle" border="0">
							</A>
						</FONT>
						<? } ?>
					</TD>
				</TR>
				<TR>
					<TD colspan="9" class="blank">&nbsp;</TD>
				</tr>
			<? } ?>	

			</table>
		</td>
		<td align="left" valign="top" class="blank">
				<?
					// print info box:
					// get template
					$infobox_template =& $GLOBALS['template_factory']->open('infobox/infobox_raumzeit');

					// get a list of semesters (as display options)
					$semester_selectionlist = raumzeit_get_semesters($sem, $semester, $raumzeitFilter);

					// fill attributes
					$infobox_template->set_attribute('picture', 'schedule.jpg');
					$infobox_template->set_attribute("selectionlist_title", "Semesterauswahl"); 
					$infobox_template->set_attribute('selectionlist', $semester_selectionlist);    
					if (sizeof($messages) > 0) {
							$infobox_template->set_attribute('messages', $messages);
					}
					// render template
					echo $infobox_template->render();

				?>
			</td>
		</tr>
</TABLE>
<?
$sem->store();
page_close();
