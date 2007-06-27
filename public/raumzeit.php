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


// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');

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
$infobox = array();
$messages = array();

while ($msg = $sem->getNextMessage()) {
	$messages[] = $msg;
}

if (sizeof($messages) > 0) {
	$infobox[] = raumzeit_parse_messages($messages);
}

$info_zw['kategorie'] = _("Informationen:");
$info_zw['eintrag'][] = array ('icon' => 'ausruf_small.gif',
	'text'  =>_("Hier können Sie alle Termine der Veranstaltung verwalten.")
);

$infobox[] = $info_zw;

$infobox[] = raumzeit_get_semester_chooser($sem, $semester, $raumzeitFilter);

// show legend for regular dates, if necessary
if ($GLOBALS['RESOURCES_ENABLE']) {
	$info = array();

	$info['kategorie'] = _("Legende:");
	$info['eintrag'][] = array (
		'icon' => 'steelrot.jpg" height="20" width="25" alt="',
		'text' => _("Kein Termin hat eine Raumbuchung!")
	);
	$info['eintrag'][] = array (
		'icon' => 'steelgelb.jpg" height="20" width="25" alt="',
		'text' => _("Mindestens ein Termin hat keine Raumbuchung!")
	);
	$info['eintrag'][] = array (
		'icon' => 'steelgruen.jpg" height="20" width="25" alt="',
		'text' => _("Alle Termine haben eine Raumbuchung.")
	);

	$infobox[] = $info;
}

// template-like output
?>
<TABLE width="100%" border="0" cellpadding="0" cellspacing="0">
	<TR>
		<TD colspan="9" class="topic">
			&nbsp; <B><?=getHeaderLine($id)." -  "._("allgemeine Zeiten");?></B>
		</TD>
	</TR>

	<tr>
		<td class="blank" width="100%" align="center">
			<br />
			<table width="99%" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td colspan="9" class="steelkante">
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
						<SELECT name="startWeek">
							<OPTION value="0"<?=($sem->getStartWeek() == '0') ? ' selected' : ''?>>1. <?=_("Semesterwoche");?></OPTION>
							<OPTION value="1"<?=($sem->getStartWeek() == '1') ? ' selected' : ''?>>2. <?=_("Semesterwoche");?></OPTION>
						</SELECT>
						</FONT>
						&nbsp;&nbsp;
						<INPUT type="image" <?=makebutton('auswaehlen', 'src')?> align="absmiddle">
						<INPUT type="hidden" name="cmd" value="selectSemester">
						</FORM>
						<? } else {
							echo ($sem->getStartWeek()) ? '2. ' : '1. ', _("Semesterwoche");
						} ?>
					</TD>
				</TR>
				<? if (!$_LOCKED && $RESOURCES_ENABLE && $RESOURCES_ALLOW_ROOM_REQUESTS) { ?>
				<TR>
					<TD class="blank" colspan="9">
						&nbsp;
					</TD>
				</TR>
				<TR>
					<TD class="blank" colspan="9">
						<?
						if ($sem->hasRoomRequest()) {
							$req_info = $sem->getRoomRequestInfo();
						?>
						<DIV style="{border:1px solid black;background:#FFFFDD}">
							&nbsp;<?=_("Für diese Veranstaltung liegt eine noch offene Raumanfrage vor.")?>
							<A href="javascript:alert('<?=$req_info?>')">
								<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/info.gif" alt="<?=$req_info?>" border="0" align="absmiddle">
							</A>
						</DIV>
						<BR />
						<? } ?>
						<FONT size="-1">
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
				</TR>
				<?
				}
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
							?>
							<FORM action="<?=$PHP_SELF?>" method="post" name="Formular">
							<INPUT type="hidden" name="cycle_id" value="<?=$metadate_id?>">
							<?
							$termine =& $sem->getSingleDatesForCycle($metadate_id);
							?>
				<TR>
					<TD align="center" colspan="9" class="steel1">
						<TABLE cellpadding="1" cellspacing="0" border="0" width="90%">
							<?
							$every2nd = 1;
							$all_semester = $semester->getAllSemesterData();
							$grenze = 0;
							foreach ($termine as $singledate_id => $val) {
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
						&nbsp;&nbsp;
						<A href="<?=$PHP_SELF?>?cmd=addCycle#newCycle">
							<IMG <?=makebutton('feldhinzufuegen', 'src')?> border="0">
						</A>
					</TD>
				</TR>
				<? } ?>
				<TR>
					<TD colspan="9" class="blank">&nbsp;</TD>
				</TR>
				<TR>
					<TD colspan="9" class="steelkante">
						<A name="irregular_dates">
						&nbsp;<B><?=_("Unregelm&auml;&szlig;ige Termine/Blocktermine")?></B>
					</TD>
				</TR>
				<TR>
					<TD colspan="9" class="blank">&nbsp;</TD>
				</TR>
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
				<? }
				} 

				if ($termine =& $sem->getSingleDates(true)) { ?>
				<TR>
					<TD align="center" colspan="9" class="steel1">
						<FORM action="<?=$PHP_SELF?>" method="post" name="Formular">
						<TABLE cellpadding="1" cellspacing="0" border="0" width="100%">
							<?
							$count = 0;
							$every2nd = 1;
							foreach ($termine as $key => $val) {
								$tpl['checked'] = '';
								$tpl = getTemplateDataForSingleDate($val);

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
				<TR>
					<TD colspan="9" class="blank">&nbsp;</TD>
				</TR>
			</TABLE>
			</TD>
			<td align="left" valign="top" class="blank">
				<?
					print_infobox ($infobox, 'board2.jpg');
				?>
			</td>
		</TR>
</TABLE>
<?
$sem->store();
page_close();
