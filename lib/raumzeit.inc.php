<?
# Lifter002: TODO
require_once('log_events.inc.php');
define('DO_NOT_APPEND_MESSAGES', false);
/*
 * Command handlers
 */
function raumzeit_open() {
	global $sd_open;
	$sd_open[$_REQUEST['open_close_id']] = true;
}

function raumzeit_close() {
	global $sd_open;
	$sd_open[$_REQUEST['open_close_id']] = false;
	unset ($sd_open[$_REQUEST['open_close_id']]);
}

function raumzeit_delete_singledate() {
	global $sem;

	$termin = $sem->getSingleDate($_REQUEST['sd_id'], $_REQUEST['cycle_id']);

	if (!$_REQUEST['approveDelete'] && $termin->getIssueIDs()) {
		if($GLOBALS["RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW"]){
			$sem->createQuestion( _("Achtung: Diesem Termin ist im Ablaufplan ein Thema zugeordnet. Titel und Beschreibung des Themas bleiben erhalten und können in der Expertenansicht des Ablaufplans einem anderen Termin wieder zugeordnet werden."). '<br/>'. _("Wollen Sie diesen Termin wirklich löschen?"), $GLOBALS['PHP_SELF']."?cmd=delete_singledate&cycle_id={$_REQUEST['cycle_id']}&sd_id={$_REQUEST['sd_id']}&approveDelete=TRUE");
		}else{
			$sem->createQuestion( _("Diesem Termin ist ein Thema zugeordnet. Wollen Sie diesen Termin wirklich löschen?"), $GLOBALS['PHP_SELF']."?cmd=delete_singledate&cycle_id={$_REQUEST['cycle_id']}&sd_id={$_REQUEST['sd_id']}&approveDelete=TRUE");
		}
	} else {
		if ($_REQUEST['approveDelete']) {
			if($GLOBALS["RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW"]){
				$sem->createMessage(sprintf(_("Sie haben den Termin %s gelöscht, dem ein Thema zugeorndet war. Sie können das Thema in der %sExpertenansicht des Ablaufplans%s einem anderen Termin (z.B. einem Ausweichtermin) zuordnen."), $termin->toString(), '<a href="themen.php?cmd=changeViewMode&newFilter=expert">', '</a>'));
			}else{
				$sem->createMessage(sprintf(_("Der Termin %s wurde gelöscht!"), $termin->toString()));
			}
		} else {
			$sem->createMessage(sprintf(_("Der Termin %s wurde gelöscht!"), $termin->toString()));
		}
		$sem->deleteSingleDate($_REQUEST['sd_id'], $_REQUEST['cycle_id']);
	}
}

function raumzeit_undelete_singledate() {
	global $sem;

	$termin = $sem->getSingleDate($_REQUEST['sd_id'], $_REQUEST['cycle_id']);
	$sem->createMessage(sprintf(_("Der Termin %s wurde wiederhergestellt!"), $termin->toString()));
	$sem->unDeleteSingleDate($_REQUEST['sd_id'], $_REQUEST['cycle_id']);
}

function raumzeit_checkboxAction() {
	global $sem, $choosen;
	switch ($_REQUEST['checkboxAction']) {
		case 'chooseAll':
			break;

		case 'chooseNone':
			break;

		case 'invert':
			foreach ($_REQUEST['singledate'] as $val) {
				$choosen[$val] = TRUE;
			}
			break;

		case 'deleteChoosen':
			//TODO: what if deletion leads to an empty regular entry? -> the regular entry should be deleted too
			if (!$_REQUEST['singledate']) break;
			$msg = _("Folgende Termine wurden gelöscht:").'<br/>';
			foreach ($_REQUEST['singledate'] as $val) {
				$termin = $sem->getSingleDate($val, $_REQUEST['cycle_id']);
				$msg .= '<li>'.$termin->toString().'<br/>';
				unset($termin);
				$sem->deleteSingleDate($val, $_REQUEST['cycle_id']);
			}
			$sem->createMessage($msg);
			break;

		case 'unDeleteChoosen':
			if (!$_REQUEST['singledate']) break;	// if there were no singleDates choosen, stop.
			$msg = _("Folgende Termine wurden wieder hergestellt:").'<br/>';
			foreach ($_REQUEST['singledate'] as $val) {
				if ($sem->unDeleteSingleDate($val, $_REQUEST['cycle_id'])) {		// undelete retrieved singleDate
					$termin = $sem->getSingleDate($val, $_REQUEST['cycle_id']);		// retrieve singleDate
					$msg .= $termin->toString().'<br/>';													// add string representation to message
					unset($termin);																								// we never now, if the variable persists...
				}
			}
			$sem->createMessage($msg);
			break;

		case 'deleteAll':
			if ($_REQUEST['cycle_id']) {
				if ($_REQUEST['approveDeleteAll'] != TRUE) {	// security-question
					$sem->createQuestion(_("Sie haben ausgewählt, alle Termine eines regelmäßigen Eintrages zu löschen. Dies hat zur Folge, dass der regelmäßige Termin ebenfalls gelöscht wird.").'<br/>'.sprintf(_("Sind Sie sicher, dass Sie den regelmäßigen Eintrag \"%s\" löschen möchten?"), '<b>'.$sem->metadate->cycles[$_REQUEST['cycle_id']]->toString().'</b>'), $GLOBALS['PHP_SELF']."?cmd=checkboxAction&checkboxAction=deleteAll&cycle_id={$_REQUEST['cycle_id']}&approveDeleteAll=TRUE");
				} else {											// deletion approved, so we do the job
					$msg = sprintf(_("Der regelmäßige Termin \"%s\" wurde gelöscht."), '<b>'.$sem->metadate->cycles[$_REQUEST['cycle_id']]->toString().'<b/>');
					$sem->createMessage($msg);	// create a message
					$sem->deleteCycle($_REQUEST['cycle_id']);
				}
			} else {
				if ($_REQUEST['approveDeleteAll'] != TRUE) {	// security question
					$sem->createQuestion( _("Sie haben ausgewählt, alle unregelmäßigen Termine dieser Veranstaltung zu löschen.").'<br/>'._("Sind Sie sicher, dass Sie alle unregelmäßigen Termine dieser Veranstaltung löschen möchten?"), $GLOBALS['PHP_SELF']."?cmd=checkboxAction&checkboxAction=deleteAll&cycle_id={$_REQUEST['cycle_id']}&approveDeleteAll=TRUE");
				} else {									// deletion approved, so we do the job
					$msg = _("Folgende Termine wurden gelöscht:").'<br/>';
					$singleDates =& $sem->getSingleDates();	// get all irrgeular singleDates of the seminar
					foreach ($singleDates as $key => $val) {				// walk through each and delete it
						// TODO: this functionality should be better implemented into the Seminar.class.php
						$msg .= $val->toString().'<br/>';			// add string-representation of the current singleDate to the message
						unset($sem->irregularSingleDates[$key]);	// we unset that singleDate, otherwise it would show up on the page although deleted already.
						$val->delete();												// delete the singleDate
					}
					$sem->createMessage($msg);							// create a message
				}
			}
			break;

		case 'chooseEvery2nd':
			break;
	}
}

function raumzeit_bookRoom() {
	global  $sem;
	if (!$_REQUEST['singledate']) return;
	$resObj =& ResourceObject::Factory($_REQUEST['room']);
	$raum = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
  $termin_count = 0;
  $ex_termin_count = 0;
	if ($_REQUEST['room'] == 'retreat') {
		$msg = _("Für folgende Termine wurde die Raumbuchung aufgehoben:").'<br/>';
	} else {
		$msg = sprintf(_("Für folgende Termine wurde der Raum \"%s\" gebucht:"), $raum)."<br/>";
	}
  $error_msg = sprintf(_("Für folgende gelöschte Termine wurde Raum \"%s\" nicht gebucht:"), $raum)."<br/>";
	foreach ($_REQUEST['singledate'] as $val) {
		$termin = $sem->getSingleDate($val, $_REQUEST['cycle_id']);
		if (!$termin->isExTermin()) {
			if ($sem->bookRoomForSingleDate($val, $_REQUEST['room'], $_REQUEST['cycle_id'])) {
      	$termin_count++;
				$msg .= $termin->toString()."<br/>";
			}
		} else
    {
			$error_msg .= $termin->toString()."<br/>";
      $ex_termin_count++;
    }
		unset($termin);
	}
  if ($termin_count > 0) {
    $sem->createMessage($msg);
  }

  if (($ex_termin_count > 0 ) && ($_REQUEST['room'] != 'retreat')) {
    $sem->createError($error_msg);
  }
}

function raumzeit_selectSemester() {
	global $sem, $semester;
		
		if (!$semester) $semester = new SemesterData();

    $start_semester = $_REQUEST['startSemester'];
    $end_semester   = $_REQUEST['endSemester'];

		// The user meant actually to choose "1 Semester"
		if ($start_semester == $end_semester) {
			$end_semester = 0;
		}

    // test, if start semester is before the end semester
    // btw.: end_semester == 0 means a duration of one semester (ja logisch! :) )
    if ($end_semester != 0 && $end_semester != -1 && $start_semester >= $end_semester) {
        $sem->createError(_("Das Startsemester liegt nach dem Endsemester!"));
        return FALSE;
    } else {
    	$sem->setStartSemester($_REQUEST['startSemester']);
    	$sem->setEndSemester($_REQUEST['endSemester']);
			$sem->removeAndUpdateSingleDates();
    	$sem->setTurnus($_REQUEST['turnus']);
    	$sem->setStartWeek($_REQUEST['startWeek']);

			// apply new filter for choosen semester (if necessary)
			$current_semester = $semester->getCurrentSemesterData();

			// If the new duration includes the current semester, we set the semester-chooser to the current semester
			if ($current_semester['beginn'] >= $sem->getStartSemester() && $current_semester['beginn'] <= $sem->getEndSemesterVorlesEnde()) {
				$sem->setFilter($current_semester['beginn']);
			} else {
				// otherwise we set it to the first semester
				$sem->setFilter($sem->getStartSemester());
			}
    }
}

function raumzeit_addCycle() {
	global $sem, $newCycle;
	$sem->createInfo(_("Geben Sie nun unten die Zeiten für den neu zu erstellenden regelmäßigen Termin an!"));
	$newCycle=true;
}

function raumzeit_doAddCycle() {
	global $sem, $newCycle;
	if ($cycle_id = $sem->addCycle($_REQUEST)) {	// the template 'addmetadate.tpl' has form-fields, just passed through here.
		$info = $sem->metadate->cycles[$cycle_id]->toString();
		$sem->createMessage(sprintf(_("Die regelmäßige Veranstaltungszeit \"%s\" wurde hinzugefügt!"),'<b>'.$info.'</b>'));
	} else {
		$sem->createError(_("Die regelmäßige Veranstaltungszeit konnte nicht hinzugefügt werden! Bitte überprüfen Sie ihre Eingabe."));
		$newCycle = true;
	}
}

function raumzeit_editCycle() {
	global $sem;
	$sem->editCycle($_REQUEST);
}

function raumzeit_deleteCycle() {
	global $sem;
	$sem->createQuestion(sprintf(_("Sind Sie sicher, dass Sie den regelm&auml;&szlig;igen Eintrag \"%s\" l&ouml;schen m&ouml;chten?"), '<b>'.$sem->metadate->cycles[$_REQUEST['cycle_id']]->toString().'</b>'), $GLOBALS['PHP_SELF']."?cmd=doDeleteCycle&cycle_id=".$_REQUEST['cycle_id']);
}

function raumzeit_doDeleteCycle() {
	global $sem;
	$sem->createMessage(sprintf(_("Der regelm&auml;&szlig;ige Eintrag \"%s\" wurde gel&ouml;scht."), '<b>'.$sem->metadate->cycles[$_REQUEST['cycle_id']]->toString().'</b>'));
	$sem->deleteCycle($_REQUEST['cycle_id']);
}

function raumzeit_doAddSingleDate() {
	global $sem;
	$termin = new SingleDate();
	$start = mktime($_REQUEST['start_stunde'], $_REQUEST['start_minute'], 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']);
	$ende = mktime($_REQUEST['end_stunde'], $_REQUEST['end_minute'], 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']);
	$termin->setTime($start, $ende);
	$termin->setDateType($_REQUEST['dateType']);

	if ($start < $sem->filterStart || $ende > $sem->filterEnd) {
		$sem->setFilter('all');
	}
	$sem->addSingleDate($termin);
	$sem->bookRoomForSingleDate($termin->getSingleDateID(), $_REQUEST['room']);
	$sem->createMessage(sprintf(_("Der Termin %s wurde hinzugefügt!"), '<b>'.$termin->toString().'</b>'));
	$sem->store();
}

function raumzeit_editDeletedSingleDate() {
	global $sem, $sd_open, $perm;

	if (!$perm->have_perm('admin')) {
		$sem->createError(_("Ihnen fehlt die Berechtigung um den Kommentar von gelöschten Terminen zu ändern!"));
		return;
	}

	unset($sd_open[$_REQUEST['singleDateID']]);	// we close the choosen singleDate, that it does not happen that we have multiple singleDates open -> could lead to confusion, which singleDate is meant to be edited
	if ($_REQUEST['cycle_id'] != '') {
		// the choosen singleDate is connected to a cycleDate
		$termin =& $sem->getSingleDate($_REQUEST['singleDateID'], $_REQUEST['cycle_id']);
	} else {
		// the choosen singleDate is irregular, so we can edit it directly
		$termin =& $sem->getSingleDate($_REQUEST['singleDateID']);
	}

	$old_comment = $termin->getComment();
	$termin->setComment($_REQUEST['comment']);
	if($termin->getComment() != $old_comment) {
		$sem->createMessage(sprintf(_("Der Kommtentar des gelöschten Termins %s wurde geändert."), '<b>'.$termin->toString().'</b>'));
	} else {
		$sem->createInfo(sprintf(_("Der gelöschte Termin %s wurde nicht verändert."), '<b>'.$termin->toString().'</b>'));
	}

	$termin->store();
}

function raumzeit_editSingleDate() {
	global $sem, $sd_open;
	unset($sd_open[$_REQUEST['singleDateID']]);	// we close the choosen singleDate, that it does not happen that we have multiple singleDates open -> could lead to confusion, which singleDate is meant to be edited
	// generate time-stamps to we can compare directly
	$start = mktime($_REQUEST['start_stunde'], $_REQUEST['start_minute'], 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']);
	$ende = mktime($_REQUEST['end_stunde'], $_REQUEST['end_minute'], 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']);
	if ($_REQUEST['cycle_id'] != '') {
		// the choosen singleDate is connected to a cycleDate
		$termin =& $sem->getSingleDate($_REQUEST['singleDateID'], $_REQUEST['cycle_id']);
		if (($termin->getStartTime() != $start) || ($termin->getEndTime() != $ende)) {	// if we have changed the time of the date, it is not a regular time-slot any more, so we have to move it to the irregularSingleDates of the seminar
			$termin->setExTermin(true);		// deletes the singleDate out of the regular cycleData
			$termin->store();							// save back to database directly

			// create a new irregularSingleDate for the seminar
			$new_termin = new SingleDate();
			if ($new_termin->setTime($start, $ende)) {
				$new_termin->setDateType($_REQUEST['dateType']);
				$new_termin->setFreeRoomText($_REQUEST['freeRoomText_sd']);
				$new_termin->store();
				$sem->addSingleDate($new_termin);
				$sem->bookRoomForSingleDate($new_termin->getSingleDateID(), $_REQUEST['room_sd']);

				$sem->createInfo(sprintf(_("Der Termin %s wurde aus der Liste der regelmäßigen Termine gelöscht und als unregelmäßiger Termin eingetragen, da Sie die Zeiten des Termins verändert haben, so dass dieser Termin nun nicht mehr regelmäßig ist. Außerdem wurde die bisherige Raumbuchung aufgehoben."), '<b>'.$termin->toString().'</b>'));
			}
			$sem->appendMessages($new_termin->getMessages());

		} else {
			// we did not change the times, so we can edit the regular singleDate
			$sem->bookRoomForSingleDate($termin->getSingleDateID(), $_REQUEST['room_sd'], $_REQUEST['cycle_id']);
			$termin->setDateType($_REQUEST['dateType']);
			$termin->setFreeRoomText($_REQUEST['freeRoomText_sd']);
			$sem->createMessage(sprintf(_("Der Termin %s wurde geändert!"), '<b>'.$termin->toString().'</b>'));
			$termin->store();
			$sem->appendMessages($termin->getMessages());
		}
		$sem->readSingleDatesForCycle($_REQUEST['cycle_id'], true);
	} else {
		// the choosen singleDate is irregular, so we can edit it directly
		$termin =& $sem->getSingleDate($_REQUEST['singleDateID']);

		if (  $termin->setTime($start, $ende) 
    ||    $termin->getFreeRoomText()!=$_REQUEST['freeRoomText_sd']
    ||    $termin->getDateType!=$_REQUEST['dateType'] ) {

			$termin->setDateType($_REQUEST['dateType']);
			$termin->setFreeRoomText($_REQUEST['freeRoomText_sd']);
			$termin->store();
			$sem->bookRoomForSingleDate($_REQUEST['singleDateID'], $_REQUEST['room_sd']);
			$sem->createMessage(sprintf(_("Der Termin %s wurde geändert!"), '<b>'.$termin->toString().'</b>'));
		}
		$sem->appendMessages($termin->getMessages());
	}
}


function raumzeit_freeText() {
	global $sem, $sd_open;
	if (is_array($_REQUEST['singledate'])) {
		foreach($_REQUEST['singledate'] as $termin_id)
		{
			if ($_REQUEST['cycle_id'] != '') {
				$termin = $sem->getSingleDate($termin_id, $_REQUEST['cycle_id']);
				$sem->bookRoomForSingleDate($termin_id, $_REQUEST['room'], $_REQUEST['cycle_id']);
				$sem->metadate->cycles[$_REQUEST['cycle_id']]->termine = null;
			} else {
				$termin = $sem->getSingleDate($termin_id);
				$sem->bookRoomForSingleDate($termin_id, $_REQUEST['room']);
				$sem->irregularSingleDates = null;
			}
			if ($termin->setTime($start, $ende) //äh $start, $ende? woher?
					|| $termin->getFreeRoomText()!=$_REQUEST['freeRoomText']
					|| $termin->getDateType!=$_REQUEST['dateType'] ) {

				$termin->setDateType($_REQUEST['dateType']);
				$termin->setFreeRoomText($_REQUEST['freeRoomText']);
				$termin->store();
				$sem->createMessage(sprintf(_("Der Termin %s wurde geändert!"), '<b>'.$termin->toString().'</b>'));
			}
			$sem->appendMessages($termin->getMessages());
		}
	} else {
		$sem->createInfo(_("Sie haben keinen Termin ausgewählt!"));
	}
}

function raumzeit_removeRequest() {
	global $sem;
	
	$termin =& $sem->getSingleDate($_REQUEST['singleDateID'], $_REQUEST['cycle_id']);
	// logging >>>>>>
	log_event("SEM_DELETE_SINGLEDATE_REQUEST", $sem->getId(), $termin->toString());
	// logging <<<<<<
	$termin->removeRequest();
	$sem->createMessage(sprintf(_("Die Raumanfrage für den Termin %s wurde gelöscht."), $termin->toString()));
}

function raumzeit_removeSeminarRequest() {
	global $sem;
	$sem->removeSeminarRequest();
	$sem->createMessage(sprintf(_("Die Raumanfrage für die Veranstaltung wurde gelöscht.")));
}
?>
