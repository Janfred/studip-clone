<?
/**
* ExternModuleTemplatePersondetails.class.php
*
*
*
*
* @author		Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id: ExternModuleTemplatePersondetails.class.php 6854 2006-10-18 16:04:09Z pthiene $
* @access		public
* @modulegroup	extern
* @module		ExternModuleTemplatePersondetails
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplatePersondetails.class.php
//
// Copyright (C) 2007 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/lib/ExternModule.class.php');
require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/views/extern_html_templates.inc.php');
require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/classes/Avatar.class.php');
require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once('lib/dates.inc.php');
require_once('lib/classes/SemesterData.class.php');
require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/lib/extern_functions.inc.php');
if ($GLOBALS["CALENDAR_ENABLE"]) {
	require_once($GLOBALS["RELATIVE_PATH_CALENDAR"]
			. "/lib/DbCalendarEventList.class.php");
}
global $_fullname_sql;


class ExternModuleTemplatePersondetails extends ExternModule {

	var $markers = array();
	var $user_id;

	/**
	*
	*/
	function ExternModuleTemplatePersondetails ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
		$this->data_fields = array();
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$this->registered_elements = array(
				'PersondetailsLectures' => 'PersondetailsLecturesTemplate',
				'LinkInternLecturedetails' => 'LinkInternTemplate',
				'LitList',
				'TemplateMain' => 'TemplateGeneric',
				'TemplateLectures' => 'TemplateGeneric',
				'TemplateNews' => 'TemplateGeneric',
				'TemplateAppointments' => 'TemplateGeneric',
				'TemplateLitList' => 'TemplateGeneric',
				'TemplateOwnCategories' => 'TemplateGeneric'
			);
		} else {
			$this->registered_elements = array(
				'PersondetailsLectures' => 'PersondetailsLecturesTemplate',
				'LinkInternLecturedetails' => 'LinkInternTemplate',
				'LitList',
				'TemplateMain' => 'TemplateGeneric',
				'TemplateLectures' => 'TemplateGeneric',
				'TemplateNews' => 'TemplateGeneric',
				'TemplateLitList' => 'TemplateGeneric',
				'TemplateOwnCategories' => 'TemplateGeneric'
			);
		}
		$this->field_names = array();
		$this->args = array('username', 'seminar_id');

		parent::ExternModule($range_id, $module_name, $config_id, $set_config, $global_id);

	}

	function setup () {

		// setup module properties
	//	$this->elements["LinkIntern"]->link_module_type = 2;
	//	$this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");

		$this->elements['LinkInternLecturedetails']->real_name = _("Link zum Modul Veranstaltungsdetails");
		$this->elements['LinkInternLecturedetails']->link_module_type = array(4, 13);
		$this->elements['PersondetailsLectures']->real_name = _("Einstellungen für Lehrveranstaltungen");
		$this->elements['LitList']->real_name = _("Einstellungen für Literaturlisten");
		$this->elements['TemplateMain']->real_name = _("Haupttemplate");
		$this->elements['TemplateLectures']->real_name = _("Template für Lehrveranstaltungen");
		$this->elements['TemplateNews']->real_name = _("Template für News");
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$this->elements['TemplateAppointments']->real_name = _("Template für Termine");
		}
		$this->elements['TemplateLitList']->real_name = _("Template für Literaturlisten");
		$this->elements['TemplateOwnCategories']->real_name = _("Template für eigene Kategorien");

	}

	function toStringEdit ($open_elements = '', $post_vars = '',
			$faulty_values = '', $anker = '') {

		$this->updateGenericDatafields('TemplateMain', 'user');
		$this->elements['TemplateMain']->markers = $this->getMarkerDescription('TemplateMain');
		$this->elements['TemplateLectures']->markers = $this->getMarkerDescription('TemplateLectures');
		$this->elements['TemplateLitList']->markers = $this->getMarkerDescription('TemplateLitList');
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$this->elements['TemplateAppointments']->markers = $this->getMarkerDescription('TemplateAppointments');
		}
		$this->elements['TemplateNews']->markers = $this->getMarkerDescription('TemplateNews');
		$this->elements['TemplateOwnCategories']->markers = $this->getMarkerDescription('TemplateOwnCategories');

		return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);

	}

	function getMarkerDescription ($element_name) {
		$markers['TemplateMain'][] = array('__GLOBAL__', _("Globale Variablen (gültig im gesamten Template)."));
		$markers['TemplateMain'][] = array('###STUDIP-EDIT-HREF###', '');

		$markers['TemplateMain'][] = array('<!-- BEGIN PERSONDETAILS -->', '');
		$markers['TemplateMain'][] = array('###FULLNAME###', '');
		$markers['TemplateMain'][] = array('###LASTNAME###', '');
		$markers['TemplateMain'][] = array('###FIRSTNAME###', '');
		$markers['TemplateMain'][] = array('###TITLEFRONT###', '');
		$markers['TemplateMain'][] = array('###TITLEREAR###', '');
		$markers['TemplateMain'][] = array('###USERNAME###', '');
		$markers['TemplateMain'][] = array('###STATUSGROUPS###', _("Kommaseparierte Liste mit Statusgruppen"));
		$markers['TemplateMain'][] = array('###IMAGE-HREF###', '');
		$markers['TemplateMain'][] = array('###INST-NAME###', '');
		$markers['TemplateMain'][] = array('###INST-HREF###', '');
		$markers['TemplateMain'][] = array('###STREET###', '');
		$markers['TemplateMain'][] = array('###ZIPCODE###', '');
		$markers['TemplateMain'][] = array('###EMAIL###', '');
		$markers['TemplateMain'][] = array('###ROOM###', '');
		$markers['TemplateMain'][] = array('###PHONE###', '');
		$markers['TemplateMain'][] = array('###FAX###', '');
		$markers['TemplateMain'][] = array('###HOMEPAGE-HREF###', '');
		$markers['TemplateMain'][] = array('###OFFICE-HOURS###', '');
		$markers['TemplateMain'][] = array('###RESEARCH-INTERESTS###', '');
		$markers['TemplateMain'][] = array('###CV###', _("Lebenslauf"));
		$markers['TemplateMain'][] = array('###PUBLICATIONS###', '');
		$markers['TemplateMain'][] = array('###OFFICE-HOURS###', '');
		$this->insertDatafieldMarkers('user', $markers, 'TemplateMain');
		$markers['TemplateMain'][] = array('###LECTURES###', _("Inhalt aus dem Template für Veranstaltungen"));
		$markers['TemplateMain'][] = array('###NEWS###', _("Inhalt aus dem Template für News"));
		$markers['TemplateMain'][] = array('###LITERATURE###', _("Inhalt aus dem Template für Literaturlisten"));
		$markers['TemplateMain'][] = array('###APPOINTMENTS###', _("Inhalt aus dem Template für Termine"));
		$markers['TemplateMain'][] = array('###OWNCATEGORIES###', _("Inhalt aus dem Template für eigene Kategorien"));
		$markers['TemplateMain'][] = array('<!-- END PERSONDETAILS -->', '');

		$markers['TemplateLectures'][] = array('<!-- BEGIN LECTURES -->', '');
		$markers['TemplateLectures'][] = array('<!-- BEGIN SEMESTER -->', '');
		$markers['TemplateLectures'][] = array('###NAME###', '');
		$markers['TemplateLectures'][] = array('<!-- BEGIN LECTURE -->', '');
		$markers['TemplateLectures'][] = array('###TITLE###', '');
		$markers['TemplateLectures'][] = array('###SUBTITLE###', '');
		$markers['TemplateLectures'][] = array('###LECTUREDETAILS-HREF###', '');
		$markers['TemplateLectures'][] = array('<!-- END LECTURE -->', '');
		$markers['TemplateLectures'][] = array('<!-- END SEMESTER -->', '');
		$markers['TemplateLectures'][] = array('<!-- END LECTURES -->', '');

		$markers['TemplateNews'][] = array('<!-- BEGIN NEWS -->', '');
		$markers['TemplateNews'][] = array('<!-- BEGIN NO-NEWS -->', '');
		$markers['TemplateNews'][] = array('###NEWS_NO-NEWS-TEXT###', '');
		$markers['TemplateNews'][] = array('<!-- END NO-NEWS -->', '');
		$markers['TemplateNews'][] = array('<!-- BEGIN ALL-NEWS -->', '');
		$markers['TemplateNews'][] = array('<!-- BEGIN SINGLE-NEWS -->', '');
		$markers['TemplateNews'][] = array('###NEWS_TOPIC###', '');
		$markers['TemplateNews'][] = array('###NEWS_BODY###', '');
		$markers['TemplateNews'][] = array('###NEWS_DATE###', '');
		$markers['TemplateNews'][] = array('###NEWS_ADMIN-MESSAGE###', '');
		$markers['TemplateNews'][] = array('###NEWS_NO###', '');
		$markers['TemplateNews'][] = array('<!-- END SINGLE-NEWS -->', '');
		$markers['TemplateNews'][] = array('<!-- END ALL-NEWS -->', '');
		$markers['TemplateNews'][] = array('<!-- END NEWS -->', '');

		if ($GLOBALS['CALENDAR_ENABLE']) {
			$markers['TemplateAppointments'][] = array('<!-- BEGIN APPOINTMENTS -->', '');
			$markers['TemplateAppointments'][] = array('###LIST-START###', _("Startdatum der Terminliste"));
			$markers['TemplateAppointments'][] = array('###LIST-END###', _("Enddatum der Terminliste"));
			$markers['TemplateAppointments'][] = array('<!-- BEGIN NO-APPOINTMENTS -->', '');
			$markers['TemplateAppointments'][] = array('###NO-APPOINTMENTS-TEXT###', '');
			$markers['TemplateAppointments'][] = array('<!-- END NO-APPOINTMENTS -->', '');
			$markers['TemplateAppointments'][] = array('<!-- BEGIN ALL-APPOINTMENTS -->', '');
			$markers['TemplateAppointments'][] = array('<!-- BEGIN SINGLE-APPOINTMENT -->', '');
			$markers['TemplateAppointments'][] = array('###DATE###', _("Start und Endzeit oder ganztägig"));
			$markers['TemplateAppointments'][] = array('###BEGIN###', '');
			$markers['TemplateAppointments'][] = array('###END###', '');
			$markers['TemplateAppointments'][] = array('###TITLE###', '');
			$markers['TemplateAppointments'][] = array('###DESCRIPTION###', '');
			$markers['TemplateAppointments'][] = array('###LOCATION###', '');
			$markers['TemplateAppointments'][] = array('###REPETITION###', '');
			$markers['TemplateAppointments'][] = array('###CATEGORY###', '');
			$markers['TemplateAppointments'][] = array('###PRIORITY###', '');
			$markers['TemplateAppointments'][] = array('<!-- END SINGLE-APPOINTMENT -->', '');
			$markers['TemplateAppointments'][] = array('<!-- END ALL-APPOINTMENTS -->', '');
			$markers['TemplateAppointments'][] = array('<!-- END APPOINTMENTS -->', '');
		}

		$markers['TemplateLitList'] = $this->elements['LitList']->getMarkerDescription('LitList');

		$markers['TemplateOwnCategories'][] = array('<!-- BEGIN OWNCATEGORIES -->', '');
		$markers['TemplateOwnCategories'][] = array('<!-- BEGIN OWNCATEGORY -->', '');
		$markers['TemplateOwnCategories'][] = array('###OWNCATEGORY_TITLE###', '');
		$markers['TemplateOwnCategories'][] = array('###OWNCATEGORY_CONTENT###', '');
		$markers['TemplateOwnCategories'][] = array('###OWNCATEGORY_NO###', _("Laufende Nummer"));
		$markers['TemplateOwnCategories'][] = array('<!-- END OWNCATEGORY -->', '');
		$markers['TemplateOwnCategories'][] = array('<!-- END OWNCATEGORIES -->', '');

		return $markers[$element_name];
	}

	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);

		if ($range == "inst" || $range == "fak")
			return TRUE;

		return FALSE;
	}

	function getContent ($args = NULL, $raw = FALSE) {
		$instituts_id = $this->config->range_id;
		$username = $args['username'];
		$sem_id = $args['seminar_id'];

		$db_inst =& new DB_Seminar();
		$db =& new DB_Seminar();

		if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
			$nameformat = 'full';
		}

		$query_user_data = "SELECT i.Institut_id, i.Name, i.Strasse, i.Plz, i.url, ui.*, aum.*, {$GLOBALS['_fullname_sql'][$nameformat]} AS fullname, uin.user_id, uin.lebenslauf, uin.publi, uin.schwerp, uin.Home, uin.title_front, uin.title_rear FROM Institute i LEFT JOIN user_inst ui USING(Institut_id) LEFT JOIN auth_user_md5 aum USING(user_id) LEFT JOIN user_info uin USING (user_id) WHERE";

		// Mitarbeiter/in am Institut
		$db_inst->query("SELECT i.Institut_id FROM Institute i LEFT JOIN user_inst ui USING(Institut_id) LEFT JOIN auth_user_md5 aum USING(user_id) WHERE i.Institut_id = '$instituts_id' AND aum.username = '$username'");

		// Mitarbeiter/in am Heimatinstitut des Seminars
		if (!$db_inst->num_rows() && $sem_id) {
			$db_inst->query("SELECT s.Institut_id FROM seminare s LEFT JOIN user_inst ui USING(Institut_id) LEFT JOIN auth_user_md5 aum USING(user_id) WHERE s.Seminar_id = '$sem_id' AND aum.username = '$username' AND ui.inst_perms = 'dozent'");
			if($db_inst->num_rows() && $db_inst->next_record()) {
				$instituts_id = $db_inst->f('Institut_id');
			}
		}

		// an beteiligtem Institut Dozent(in)
		if (!$db_inst->num_rows() && $sem_id) {
			$db_inst->query("SELECT si.institut_id FROM seminare s LEFT JOIN seminar_inst si ON(s.Seminar_id = si.seminar_id) LEFT JOIN user_inst ui ON(si.institut_id = ui.Institut_id) LEFT JOIN auth_user_md5 aum USING(user_id) WHERE s.Seminar_id = '$sem_id' AND si.institut_id != '$instituts_id' AND ui.inst_perms = 'dozent' AND aum.username = '$username'");
			if($db_inst->num_rows() && $db_inst->next_record()) {
				$instituts_id = $db_inst->f('institut_id');
			}
		}

		// ist zwar global Dozent, aber an keinem Institut eingetragen
		if (!$db_inst->num_rows() && $sem_id) {
			$query = "SELECT aum.*, {$GLOBALS['_fullname_sql'][$nameformat]} AS fullname,  FROM auth_user_md5 aum LEFT JOIN user_info USING(user_id) WHERE username = '$username' AND perms = 'dozent'";
			$db->query($query);
		} elseif ($this->config->getValue('Main', 'defaultaddr')) {
			$db->query("$query_user_data aum.username = '$username' AND ui.externdefault = 1");
			if (!$db->num_rows()) {
				$db->query("$query_user_data aum.username = '$username' AND i.Institut_id = '$instituts_id'");
			}
		} else {
			$db->query("$query_user_data aum.username = '$username' AND i.Institut_id = '$instituts_id'");
		}

		if (!$db->next_record()) {
			die;
		}

		$this->user_id = $db->f('user_id');

		$content['__GLOBAL__']['STUDIP-EDIT-HREF'] = "{$GLOBALS['ABSOLUTE_URI_STUDIP']}edit_about.php?login=yes&view=Daten&usr_name=$username";

		$content['PERSONDETAILS']['FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
		$content['PERSONDETAILS']['LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
		$content['PERSONDETAILS']['FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
		$content['PERSONDETAILS']['TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
		$content['PERSONDETAILS']['TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
		if ($statusgroups = GetStatusgruppen($instituts_id, $this->user_id)) {
			$content['PERSONDETAILS']['STATUSGROUPS'] = ExternModule::ExtHtmlReady(join(', ', array_values($statusgroups)));
		}
		$content['PERSONDETAILS']['USERNAME'] = $db->f('username');
		$avatar = Avatar::getAvatar($this->user_id);
		if ($avatar->is_customized()) {
			$content['PERSONDETAILS']['IMAGE-HREF'] = $avatar->getURL(Avatar::NORMAL);
		}

		$gruppen = GetStatusgruppen($this->config->range_id, $db->f('user_id'));
		for ($i = 0; $i < sizeof($gruppen); $i++) {
			$content['PERSONDETAILS']['GROUPS'][$i]['GROUP'] = ExternModule::ExtHtmlReady($gruppen[$i]);
		}

		$content['PERSONDETAILS']['INST-NAME'] = ExternModule::ExtHtmlReady($db->f('Name'));
		$content['PERSONDETAILS']['INST-HREF'] = ExternModule::ExtHtmlReady(trim($db->f('url')));
		$content['PERSONDETAILS']['STREET'] = ExternModule::ExtHtmlReady($db->f('Strasse'));
		$content['PERSONDETAILS']['ZIPCODE'] = ExternModule::ExtHtmlReady($db->f('Plz'));
		$content['PERSONDETAILS']['EMAIL'] = ExternModule::ExtHtmlReady($db->f('Email'));
		$content['PERSONDETAILS']['ROOM'] = ExternModule::ExtHtmlReady($db->f('raum'));
		$content['PERSONDETAILS']['PHONE'] = ExternModule::ExtHtmlReady($db->f('Telefon'));
		$content['PERSONDETAILS']['FAX'] = ExternModule::ExtHtmlReady($db->f('Fax'));
		$content['PERSONDETAILS']['HOMEPAGE-HREF'] = ExternModule::ExtHtmlReady(trim($db->f('Home')));
		$content['PERSONDETAILS']['OFFICE-HOURS'] = ExternModule::ExtHtmlReady($db->f('sprechzeiten'));

		// generic data fields
		if ($generic_datafields = $this->config->getValue('Main', 'genericdatafields')) {
			#$datafields_obj =& new DataFields($user_id);
			#$datafields = $datafields_obj->getLocalFields($user_id);
			$localEntries = DataFieldEntry::getDataFieldEntries($user_id, 'user');
			$k = 0;
			foreach ($generic_datafields as $datafield) {
				if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
					$localEntry = trim($localEntries[$datafield]->getDisplayValue());
					if ($localEntry) {
						$content['PERSONDETAILS']["DATAFIELD_$k"] = ExternModule::ExtFormatReady($localEntry, TRUE, TRUE);
					}
				}
				$k++;
			}
		}

		$content['PERSONDETAILS']['CV'] = ExternModule::ExtFormatReady($db->f('lebenslauf'), TRUE, TRUE);
		$content['PERSONDETAILS']['RESEARCH-INTERESTS'] = ExternModule::ExtFormatReady($db->f('schwerp'), TRUE, TRUE);
		$content['PERSONDETAILS']['PUBLICATIONS'] = ExternModule::ExtFormatReady($db->f('publi'), TRUE, TRUE);

		$content['PERSONDETAILS']['LECTURES'] = $this->elements['TemplateLectures']->toString(array('content' => $this->getContentLectures(), 'subpart' => 'LECTURES'));
		$content['PERSONDETAILS']['NEWS'] = $this->elements['TemplateNews']->toString(array('content' => $this->getContentNews(), 'subpart' => 'NEWS'));
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$content['PERSONDETAILS']['APPOINTMENTS'] = $this->elements['TemplateAppointments']->toString(array('content' => $this->getContentAppointments(), 'subpart' => 'APPOINTMENTS'));
		}
		$content['PERSONDETAILS']['LITERATURE'] = $this->elements['TemplateLitList']->toString(array('content' => $this->elements['LitList']->getContent(array('user_id' => $this->user_id)), 'subpart' => 'LITLISTS'));
		$content['PERSONDETAILS']['OWNCATEGORIES'] = $this->elements['TemplateOwnCategories']->toString(array('content' => $this->getContentOwnCategories(), 'subpart' => 'OWNCATEGORIES'));

		return $content;
	}

	function getContentOwnCategories () {
		$db =& new DB_Seminar();
		$db->query("SELECT * FROM kategorien WHERE range_id = '{$this->user_id}' ORDER BY priority");
		$i = 0;
		while ($db->next_record()) {
			$content['OWNCATEGORIES']['OWNCATEGORY'][$i]['OWNCATEGORY_TITLE'] = ExternModule::ExtHtmlReady($db->f('name'));
			$content['OWNCATEGORIES']['OWNCATEGORY'][$i]['OWNCATEGORY_CONTENT'] = ExternModule::ExtFormatReady($db->f('content'));
			$content['OWNCATEGORIES']['OWNCATEGORY'][$i]['OWNCATEGORY_NO'] = $i + 1;
			$i++;
		}
		return $content;
	}

	function getContentNews () {

		$news =& StudipNews::GetNewsByRange($this->user_id, TRUE);
		if (!count($news)) {
			$content['NEWS']['NO-NEWS']['NEWS_NO-NEWS-TEXT'] = $this->config->getValue('Main', 'nodatatext');
		} else {
			$i = 0;
			foreach ($news as $news_id => $news_detail) {
				list($news_content, $admin_msg) = explode("<admin_msg>", $news_detail['body']);
				if ($admin_msg) {
					$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_ADMIN-MESSAGE'] = preg_replace('# \(.*?\)#', '', $admin_msg);
					$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] = ExternModule::ExtFormatReady($news_content);
				} else {
					$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] = ExternModule::ExtFormatReady($news_detail['body']);
				}
				$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_DATE'] = strftime($dateform, $news_detail['date']);
				$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_TOPIC'] = ExternModule::ExtHtmlReady($news_detail['topic']);
				$content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_NO'] = $i + 1;
				$i++;
			}
		}
		return $content;
	}

	function getContentAppointments () {
		if ($GLOBALS['CALENDAR_ENABLE']) {
			$event_list = new DbCalendarEventList($this->user_id);
			$content['APPOINTMENTS']['LIST-START'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat') . ' %X', $event_list->getStart()));
			$content['APPOINTMENTS']['LIST-END'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat') . ' %X', $event_list->getEnd()));
			if ($event_list->existEvent()) {
				$i = 0;
				while ($event = $event_list->nextEvent()) {
					if ($event->isDayEvent()) {
						$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['DATE'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat'), $event->getStart()) . ' (' . _("ganztägig") . ')');
					} else {
						$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['DATE'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat') . " %X", $event->getStart()));
						if (date("dmY", $event->getStart()) == date("dmY", $event->getEnd())) {
							$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['DATE'] .= ExternModule::ExtHtmlReady(strftime(" - %X", $event->getEnd()));
						} else {
							$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['DATE'] .= ExternModule::ExtHtmlReady(strftime(" - " . $this->config->getValue('Main', 'dateformat') . " %X", $event->getEnd()));
						}
					}
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['TITLE'] = ExternModule::ExtHtmlReady($event->getTitle());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['DESCRIPTION'] = ExternModule::ExtHtmlReady($event->getDescription());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['LOCATION'] = ExternModule::ExtHtmlReady($event->getLocation());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['REPETITION'] = ExternModule::ExtHtmlReady($event->toStringRecurrence());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['CATEGORY'] = ExternModule::ExtHtmlReady($event->toStringCategories());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['PRIORITY'] = ExternModule::ExtHtmlReady($event->toStringPriority());
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['START'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat') . " %X", $event->getStart()));
					$content['APPOINTMENTS']['ALL-APPOINTMENTS']['SINGLE-APPOINTMENT'][$i]['END'] = ExternModule::ExtHtmlReady(strftime($this->config->getValue('Main', 'dateformat') . " %X", $event->getEnd()));
					$i++;
				}
			} else {
				$content['APPOINTMENTS']['NO-APPOINTMENTS']['NO-APPOINTMENTS_TEXT'] = $this->config->getValue('Main', 'noappointmentstext');
			}
			return $content;
		}
		return NULL;
	}

	function getContentLectures () {
		global $attr_text_td, $end, $start;
		$db1 = new DB_Seminar();
		$semester = new SemesterData();
		$all_semester = $semester->getAllSemesterData();
		// old hard coded $SEMESTER-array starts with index 1
		array_unshift($all_semester, 0);

		// sem-types in class 1 (Lehre)
		foreach ($GLOBALS["SEM_TYPE"] as $key => $type) {
			if ($type["class"] == 1)
				$types[] = $key;
		}
		$types = implode("','", $types);

		$switch_time = mktime(0, 0, 0, date("m"), date("d") + 7 * $this->config->getValue("PersondetailsLectures", "semswitch"), date("Y"));
		// get current semester
		$current_sem = get_sem_num($switch_time) + 1;

		switch ($this->config->getValue("PersondetailsLectures", "semstart")) {
			case "previous" :
				if (isset($all_semester[$current_sem - 1])) {
					$current_sem--;
				}
				break;
			case "next" :
				if (isset($all_semester[$current_sem + 1])) {
					$current_sem++;
				}
				break;
			case "current" :
				break;
			default :
				if (isset($all_semester[$this->config->getValue("PersondetailsLectures", "semstart")])) {
					$current_sem = $this->config->getValue("PersondetailsLectures", "semstart");
				}
		}

		$last_sem = $current_sem + $this->config->getValue("PersondetailsLectures", "semrange") - 1;
		if ($last_sem < $current_sem) {
			$last_sem = $current_sem;
		}
		if (!isset($all_semester[$last_sem])) {
			$last_sem = sizeof($all_semester) - 1;
		}
		$i = 0;
		for (;$current_sem <= $last_sem; $last_sem--) {
			$query = "SELECT * FROM seminar_user su LEFT JOIN seminare s USING(seminar_id) WHERE user_id='{$this->user_id}' AND su.status LIKE 'dozent' AND ((start_time >= {$all_semester[$last_sem]['beginn']} AND start_time <= {$all_semester[$last_sem]['beginn']}) OR (start_time <= {$all_semester[$last_sem]['ende']} AND duration_time = -1)) AND s.status IN ('$types') AND s.visible = 1 ORDER BY s.mkdate DESC";

			$db1->query($query);

			if ($db1->num_rows()) {
				if (!($this->config->getValue('PersondetailsLectures', 'semstart') == 'current' && $this->config->getValue('PersondetailsLectures', 'semrange') == 1)) {
					$month = date('n', $all_semester[$last_sem]['beginn']);
					if ($month > 9) {
						$content['LECTURES']['SEMESTER'][$i]['NAME'] = $this->config->getValue('PersondetailsLectures', 'aliaswise') . date(' Y/', $all_semester[$last_sem]['beginn']) . date('y', $all_semester[$last_sem]['ende']);
					} else if ($month > 3 && $month < 10) {
						$content['LECTURES']['SEMESTER'][$i]['NAME'] = $this->config->getValue('PersondetailsLectures', 'aliassose') . date(' Y', $all_semester[$last_sem]['beginn']);
					}
				}
				$k = 0;
				while ($db1->next_record()) {
					$content['LECTURES']['SEMESTER'][$i]['LECTURE'][$k]['TITLE'] = ExternModule::ExtHtmlReady($db1->f('Name'));
					$content['LECTURES']['SEMESTER'][$i]['LECTURE'][$k]['LECTUREDETAILS-HREF'] = $this->elements['LinkInternLecturedetails']->createUrl(array('link_args' => 'seminar_id=' . $db1->f('Seminar_id')));
					if ($db1->f("Untertitel") != '') {
						$content['LECTURES']['SEMESTER'][$i]['LECTURE'][$k]['SUBTITLE'] = ExternModule::ExtHtmlReady($db1->f('Untertitel'));
					}
					$k++;
				}
			}
			$i++;
		}
		return $content;
	}

	function printout ($args) {
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);

		echo $this->elements['TemplateMain']->toString(array('content' => $this->getContent($args), 'subpart' => 'PERSONDETAILS'));

	}

	function printoutPreview () {
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);

		echo $this->elements['TemplateMain']->toString(array('content' => $this->getContent(), 'subpart' => 'PERSONDETAILS', 'hide_markers' => FALSE));

	}

}

?>
