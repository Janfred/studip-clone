<?
function getTemplateDataForSingleDate($val, $cycle_id = '') {
	global $_REQUEST, $every2nd, $choosen, $id, $showSpecialDays, $rz_switcher;

	if (!isset($rz_switcher)) {
		$rz_switcher = 1;
	}

	if (!isset($showSpecialDays)) $showSpecialDays = TRUE;
	$every2nd = 1 - $every2nd;

	$tpl['cycle_id'] = $cycle_id;							// CycleData-ID (entspricht einer einzelnen regelmäßigen Veranstaltungszeit
	$tpl['date'] = $val->toString();	// Text-String für Datum
	$tpl['class'] = 'steelgreen';							// Standardklasse
	$tpl['sd_id'] = $val->getSingleDateID();	// Die ID des aktuellen Einzeltermins (kann an CycleData oder Seminar hängen)
	$tpl['type'] = $val->getDateType();
	$tpl['art'] = $val->getTypeName();
	$tpl['freeRoomText'] = htmlReady($val->getFreeRoomText());
	$tpl['comment'] = htmlReady($val->getComment());

	/* css-Klasse und deleted-Status für das Template festlegen,
   * je nachdem ob es sich um einen gelöschten Termin handelt oder nicht */
	if ($val->isExTermin()) {
		$tpl['deleted'] = true;
		$tpl['class'] = 'steelred';
	} else {
		$tpl['deleted'] = false;
		$tpl['class'] = 'steelgreen';
	}

	/* entscheidet, ob der aktuelle Termin ausgewählt ist oder nicht,
   * je nachdem, welche Auswahlart aktiviert wurde */
	$tpl['checked'] = '';

	if ($_REQUEST['cycle_id'] == $cycle_id) {
		switch ($_REQUEST['checkboxAction']) {
			case 'chooseAll':
				$tpl['checked'] = 'checked';
				break;
			case 'chooseNone':
				$tpl['checked'] = '';
				break;
			case 'invert':
				if ($choosen[$val->getTerminID()]) {
					$tpl['checked'] = '';
				} else {
					$tpl['checked'] = 'checked';
				}
				break;
			case 'deleteChoosen':
				break;
			case 'deleteAll':
				break;
			case 'chooseEvery2nd':
				if ($every2nd) {
					$tpl['checked'] = 'checked';
				} else {
					$tpl['checked'] = '';
				}
				break;
		}
	} else if ($cycle_id != '') {
		if ($val->getStartTime() >= time()) {
			$tpl['checked'] = 'checked';
		}
	}

	/* css-Klasse auswählen, sowie Template-Feld für den Raum mit Text füllen */
	if ($GLOBALS['RESOURCES_ENABLE']) {
		if ($val->getResourceID()) {
			$resObj =& ResourceObject::Factory($val->getResourceID());
			$tpl['room'] = _("Raum: ");
			$tpl['room'] .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
			$tpl['class'] = 'steelgreen';
		} else {
			$tpl['room'] = '('._("kein gebuchter Raum").')';
			if ($val->isExTermin()) {
				if ($name = $val->isHoliday()) {
					$tpl['room'] = '('._($name).')';
				} else {
					$tpl['room'] = '('._("wurde gel&ouml;scht").')';
				}
			} else {
				if ($val->getFreeRoomText()) {
					$tpl['room'] = '('.htmlReady($val->getFreeRoomText()).')';
				}
				if (($name = $val->isHoliday()) && $showSpecialDays) {
					$tpl['room'] .= '&nbsp;('._($name).')';
				}
			}
			$tpl['class'] = 'steelred';
		}
	} else {
		$tpl['room'] = '';
		if ($rz_switcher == 1) {
			$tpl['class'] = 'steel1';
			$rz_switcher  = 2;
		} else {
			$tpl['class'] = 'steelgraulight';
			$rz_switcher  = 1;
		}

	}

	/* Füllt die Variablen für Edit-Felder */
	$tpl['day'] = date('d',$val->getStartTime());
	$tpl['month'] = date('m',$val->getStartTime());
	$tpl['year'] = date('Y',$val->getStartTime());
	$tpl['start_stunde'] = date('H',$val->getStartTime());
	$tpl['start_minute'] = date('i',$val->getStartTime());
	$tpl['end_stunde'] = date('H',$val->getEndTime());
	$tpl['end_minute'] = date('i',$val->getEndTime());

	if ($val->hasRoomRequest()) {
		$tpl['room_request'] = true;
		$tpl['ausruf']  = _("F&uuml;r diesen Termin existiert eine Raumanfrage:");
		$tpl['ausruf'] .= '\n\n'.$val->getRoomRequestInfo();
	} else {
		$tpl['room_request'] = false;
	}

	$tpl['seminar_id'] = $id;

	// fertiges Template-Array zurückgeben
	return $tpl;
}

/*
 * used by Seminar.class.php
 *
 * user defined sort function for issues
 */
function myIssueSort($a, $b) {
	if ($a->getPriority() == $b->getPriority()) {
		return 0;
	}
	return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
}

function sort_termine($a, $b) {
	if ($a->getStartTime() == $b->getStartTime()) return 0;
	if ($a->getStartTime() > $b->getStartTime()) {
		return 1;
	} else {
		return -1;
	}
}

function getAllSortedSingleDates(&$sem) {
	define('FILTER', 'TRUE');
	define('NO_FILTER', 'FALSE');

	$turnus = $sem->getFormattedTurnusDates();

  $termine = array();
	foreach ($sem->metadate->cycles as $metadate_id => $val) {
		$termine = array_merge($termine, $sem->getSingleDatesForCycle($metadate_id));
	}

	$termine = array_merge($termine, $sem->getSingleDates(FILTER));
	uasort ($termine, 'sort_termine');

	return $termine;
}

function getFilterForSemester($semester_id) {
	$semester = new SemesterData();
	if ($val = $semester->getSemesterData($semester_id)) {
		return array('filterStart' => $val['beginn'], 'filterEnd' => $val['ende']);
	} else {
		return FALSE;
	}
}

function get_not_visited($type, $seminar_id, $range_id = '') {
	global $user;
	$db = new DB_Seminar();
	switch ($type) {
		case 'forum':
			$db->query("SELECT visitdate as date FROM object_user_visits WHERE object_id = '$seminar_id' AND user_id = '{$user->id}' AND type='forum'");
			if ($db->next_record()) {
				$d = $db->f('date');
				$db->query("SELECT COUNT(*) AS count FROM px_topics WHERE mkdate >= $d AND Seminar_id = '$seminar_id' AND parent_id != '0' AND root_id = '$range_id'");
				$db->next_record();
				return $db->f('count');
			} else {
				return 0;
			}
			break;

		case 'document':
			$db->query("SELECT visitdate as date FROM object_user_visits WHERE object_id = '$seminar_id' AND user_id = '{$user->id}' AND type='documents'");
			if ($db->next_record()) {
				$d = $db->f('date');
				$db->query("SELECT COUNT(*) AS count FROM dokumente WHERE mkdate >= $d AND seminar_id = '$seminar_id' AND range_id = '$range_id'");
				$db->next_record();
				return $db->f('count');
			} else {
				return 0;
			}
			break;
	}
}

function unQuoteAll() {
	global $_REQUEST, $_POST, $_COOKIE, $_GET;

	function cleanArray(&$arr) {
		foreach($arr as $k => $v)
			if (is_array($v))
				cleanArray($arr[$k]);
			else
				$arr[$k] = stripslashes($v);
	}

	/// before processing anything in PHP do
	if (get_magic_quotes_gpc()) {
		cleanArray($_REQUEST);
		cleanArray($_POST);
		cleanArray($_COOKIE);
		cleanArray($_GET);
	}

}
?>
