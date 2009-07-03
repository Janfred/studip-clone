<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* ExternModuleTemplatePersBrowser.class.php
* 
* 
* 
*
* @author		Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id: ExternModuleTemplatePersons.class.php 6854 2006-10-18 16:04:09Z pthiene $
* @access		public
* @modulegroup	extern
* @module		ExternModuleTemplatePersBrowser
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplatePersBrowser.class.php
// 
// Copyright (C) 2009 Peter Thienel <thienel@data-quest.de>,
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
require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');
require_once('lib/dates.inc.php');
require_once('lib/classes/TreeAbstract.class.php');
require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/lib/extern_functions.inc.php');
global $_fullname_sql;


class ExternModuleTemplatePersBrowse extends ExternModule {

	var $markers = array();
	var $approved_params = array();
	var $range_tree;
	
	/**
	*
	*/
	function ExternModuleTemplatePersBrowse ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
		$this->data_fields = array(
				'Nachname', 'Telefon', 'raum', 'Email', 'sprechzeiten'
		);
		$this->registered_elements = array(
				'LinkInternListCharacters' => 'LinkInternTemplate',
				'LinkInternListInstitutes' => 'LinkInternTemplate',
				'LinkInternPersondetails' => 'LinkInternTemplate',
				'TemplateListCharacters' => 'TemplateGeneric',
				'TemplateListInstitutes' => 'TemplateGeneric',
				'TemplateListPersons' => 'TemplateGeneric',
				'TemplateMain' => 'TemplateGeneric'
		);
		
		$this->field_names = array
		(
				_("Name"),
				_("Telefon"),
				_("Raum"),
				_("Email"),
				_("Sprechzeiten")
		);
		
		$this->approved_params = array('item_id', 'initiale');
		
		$this->range_tree = TreeAbstract::GetInstance('StudipRangeTree');
		
		parent::ExternModule($range_id, $module_name, $config_id, $set_config, $global_id);
	}
	
	function setup () {
		$this->elements['LinkInternListCharacters']->real_name = _("Verlinkung der alpabetischen Liste zur Personenliste");
		$this->elements['LinkInternListCharacters']->link_module_type = array(16);
		$this->elements['LinkInternListInstitutes']->real_name = _("Verlinkung der Einrichtungsliste zur Personenliste");
		$this->elements['LinkInternListInstitutes']->link_module_type = array(16);
		$this->elements['LinkInternPersondetails']->real_name = _("Verlinkung der Personenliste zum Modul MitarbeiterInnendetails");
		$this->elements['LinkInternPersondetails']->link_module_type = array(2, 14);
		$this->elements['TemplateMain']->real_name = _("Haupttemplate");
		$this->elements['TemplateListInstitutes']->real_name = _("Einrichtungsliste");
		$this->elements['TemplateListPersons']->real_name = _("Personenliste");
		$this->elements['TemplateListCharacters']->real_name = _("Liste mit Anfangsbuchstaben der Nachnamen");
		
	}
	
	function toStringEdit ($open_elements = '', $post_vars = '', $faulty_values = '', $anker = '') {
		
		$this->updateGenericDatafields('TemplateListPersons', 'user');
		$this->elements['TemplateMain']->markers = $this->getMarkerDescription('TemplateMain');
		$this->elements['TemplateListInstitutes']->markers = $this->getMarkerDescription('TemplateListInstitutes');
		$this->elements['TemplateListPersons']->markers = $this->getMarkerDescription('TemplateListPersons');
		$this->elements['TemplateListCharacters']->markers = $this->getMarkerDescription('TemplateListCharacters');
		$this->getContentListInstitutes();
		return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
	}
	
	function getMarkerDescription ($element_name) {
	
		$markers['TemplateMain'] = array(
			array('<!-- BEGIN PERS_BROWSER -->', ''),
			array('###LISTCHARACTERS###', _("Auflistung der Anfangsbuchstaben")),
			array('###LISTINSTITUTES###', _("Auflistung der Einrichtungen")),
			array('###LISTPERSONS###', _("Auflistung der gefundenen Personen")),
			array('<!-- END PERS_BROWSER -->', '')
		);
		
		$markers['TemplateListInstitutes'] = array(
			array('<!-- BEGIN LIST_INSTITUTES -->', ''),
			array('<!-- BEGIN INSTITUTE -->', ''),
			array('###INSTITUTE_NAME###', _("Name der Einrichtung (erster Level im Einrichtungsbaum)")),
			array('###INSTITUTE_COUNT_USER###', _("Anzahl der Personen innerhalb der Einrichtung (und untergeordneten Einrichtungen)")),
			array('###URL_LIST_PERSONS###', _("URL zur Personenlist mit diesem Anfangsbuchstaben")),
			array('<!-- END INSTITUTE -->', ''),
			array('<!-- END LIST_INSTITUTES -->', '')
		);
		
		$markers['TemplateListCharacters'] = array(
			array('<!-- BEGIN LIST_CHARACTERS -->', ''),
			array('<!-- BEGIN CHARACTER -->', ''),
			array('###CHARACTER_USER###', _("Anfangsbuchstabe der Namen zur Verlinkung nach alpabetische Übersicht")),
			array('###CHARACTER_COUNT_USER###', _("Anzahl der Personennamen mit diesem Anfangsbuchstaben")),
			array('###URL_LIST_PERSONS###', _("URL zur Personenlist mit diesem Anfangsbuchstaben")),
			array('<!-- END CHARACTER -->', ''),
			array('<!-- END LIST_CHARACTERS -->', '')
		);
		
		$markers['TemplateListPersons'] = array(
			array('<!-- BEGIN LIST_PERSONS -->', ''),
			array('<!-- BEGIN NO-PERSONS -->', ''),
			array('<!-- END NO-PERSONS -->', ''),
			array('<!-- BEGIN PERSON -->', ''),
			array('###FULLNAME###', ''),
			array('###LASTNAME###', ''),
			array('###FIRSTNAME###', ''),
			array('###TITLEFRONT###', ''),
			array('###TITLEREAR###', ''),
			array('###PERSONDETAIL-HREF###', ''),
			array('###USERNAME###', ''),
			array('###PHONE###', ''),
			array('###ROOM###', ''),
			array('###EMAIL###', ''),
			array('###OFFICEHOURS###', ''),
			array('###PERSON-NO###', ''),
			$this->insertDatafieldMarkers('user', $markers, 'TemplateList'),
			array('<!-- END PERSON -->', ''),
			array('<!-- END LIST_PERSONS -->', '')
		);
	
		return $markers[$element_name];
	}
	
	function getContent ($args = null, $raw = false) {
		if ($raw) {
			$this->setRawOutput();
		}
		
		if (trim($this->config->getValue('TemplateListInstitutes', 'template'))) {
			$content['PERS_BROWSER']['LISTINSTITUTES'] = $this->elements['TemplateListInstitutes']->toString(array('content' => $this->getContentListInstitutes(), 'subpart' => 'LIST_INSTITUTES'));
		}
		if (trim($this->config->getValue('TemplateListCharacters', 'template'))) {
			$content['PERS_BROWSER']['LISTCHARACTERS'] = $this->elements['TemplateListCharacters']->toString(array('content' => $this->getContentListCharacters(), 'subpart' => 'LIST_CHARACTERS'));
		}
		if (trim($this->config->getValue('TemplateListPersons', 'template'))) {
			$content['PERS_BROWSER']['LISTPERSONS'] = $this->elements['TemplateListPersons']->toString(array('content' => $this->getContentListPersons(), 'subpart' => 'LIST_PERSONS'));
		}
		
		return $content;
	}
	
	function getContentListPersons () {
		if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
			$nameformat = 'full_rev';
		}
		
		$sort = $this->config->getValue('Main', 'sort');
		$query_order = '';
		foreach ($sort as $key => $position) {
			if ($position > 0) {
				$query_order[$position] = $this->data_fields[$key];
			}
		}
		if ($query_order) {
			ksort($query_order, SORT_NUMERIC);
			$query_order = ' ORDER BY ' . implode(',', $query_order);
		}
		
		$module_params = $this->getModuleParams($this->approved_params);
		
		$db = new DB_Seminar();
		
		if ($module_params['initiale']) {
			if ($this->config->getValue('Main', 'onlylecturers')) {
				$current_semester = get_sem_num(time());
				$query = sprintf("SELECT ui.Institut_id, "
				. "su.user_id, ui.externdefault "
				. "FROM seminar_user su "
				. "LEFT JOIN seminare s USING (seminar_id) "
				. "LEFT JOIN auth_user_md5 aum USING(user_id) "
				. "LEFT JOIN user_inst ui USING(user_id) "
				. "WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER('%s') "
				. "AND su.status = 'dozent' "
				. "AND s.visible=1 "
				. "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) "
				. "ORDER BY ui.priority, ui.externdefault",
				substr($module_params['initiale'], 0, 1),
				$GLOBALS['_views']['sem_number_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_end_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_end_sql']);
			} else {
					// get only users with the given status
				$query = sprintf("SELECT ui.Institut_id, ui.user_id, ui.externdefault "
					. "FROM user_inst ui "
					. "LEFT JOIN auth_user_md5 aum USING(user_id) "
					. "WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER('%s') "
					. "AND ui.inst_perms IN('%s') "
					. "AND ui.visible=1 "
					. "ORDER BY ui.priority, ui.externdefault",
					substr($module_params['initiale'], 0, 1),
					implode("','", $this->config->getValue('Main', 'instperms')));
			}
		} else if ($module_params['item_id']) {
			if ($this->config->getValue('Main', 'onlylecturers')) {
				$current_semester = get_sem_num(time());
				// get only users with status dozent in an visible seminar in the current semester
				$query = sprintf("SELECT ui.Institut_id, ui.user_id, ui.externdefault "
					. "FROM range_tree rt "
					. "LEFT JOIN user_inst ui ON rt.studip_object_id = ui.Institut_id "
					. "LEFT JOIN seminar_user su USING(user_id) "
					. "LEFT JOIN seminare s USING (seminar_id) "
					. "WHERE rt.item_id IN('%s') "
					. "AND su.status = 'dozent' "
					. "AND ui.visible=1 "
					. "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) "
					. "ORDER BY ui.priority, ui.externdefault",
					implode("','", $this->range_tree->getKidsKids($module_params['item_id'])),
					$GLOBALS['_views']['sem_number_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_end_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_end_sql']);
			} else {
				// get only users with the given status
				$query = sprintf("SELECT ui.Institut_id, ui.user_id, ui.externdefault "
					. "FROM range_tree rt "
					. "LEFT JOIN user_inst ui ON rt.studip_object_id = ui.Institut_id "
					. "WHERE rt.item_id IN('%s') "
					. "AND ui.inst_perms IN('%s') "
					. "AND ui.visible=1 "
					. "ORDER BY ui.priority, ui.externdefault",
					implode("','", $this->range_tree->getKidsKids($module_params['item_id'])),
					implode("','", $this->config->getValue('Main', 'instperms')));
			}
		} else {
			return array();
		}
			
		$db->query($query);
		
		$user_list = array();
		while ($db->next_record()) {
			if (!isset($user_list[$db->f('user_id')])) {
				$user_list[$db->f('user_id')] = $db->f('user_id') . $db->f('Institut_id');
			}
		}
		
		if (sizeof($user_list) == 0) {
			return array();
		}
		
		$query = sprintf("SELECT ui.Institut_id, ui.raum, ui.sprechzeiten, ui.Telefon, "
			. "inst_perms,	Email, aum.user_id, username, "
			. "%s AS fullname, aum.Nachname "
			. "FROM user_inst ui LEFT JOIN auth_user_md5 aum USING(user_id)"
			. "LEFT JOIN user_info uin USING(user_id) "
			. "WHERE CONCAT(ui.user_id, ui.Institut_id) IN ('%s') "
			. "ORDER BY aum.Nachname ",
			$GLOBALS['_fullname_sql'][$nameformat],
			implode("','", $user_list));
		$db->query($query);
		
		$j = 0;
		while ($db->next_record()) {
			$content['PERSONS']['PERSON'][$j]['FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
			$content['PERSONS']['PERSON'][$j]['LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
			$content['PERSONS']['PERSON'][$j]['FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
			$content['PERSONS']['PERSON'][$j]['TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
			$content['PERSONS']['PERSON'][$j]['TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
			$content['PERSONS']['PERSON'][$j]['PERSONDETAIL-HREF'] = $this->elements['LinkInternPersondetails']->createUrl(array('link_args' => 'username=' . $db->f('username')));
			$content['PERSONS']['PERSON'][$j]['USERNAME'] = $db->f('username');
			$content['PERSONS']['PERSON'][$j]['PHONE'] = ExternModule::ExtHtmlReady($db->f('Telefon'));
			$content['PERSONS']['PERSON'][$j]['ROOM'] = ExternModule::ExtHtmlReady($db->f('raum'));
			$content['PERSONS']['PERSON'][$j]['EMAIL'] = ExternModule::ExtHtmlReady($db->f('Email'));
			$content['PERSONS']['PERSON'][$j]['OFFICEHOURS'] = ExternModule::ExtHtmlReady($db->f('sprechzeiten'));
			$content['PERSONS']['PERSON'][$j]['PERSON-NO'] = $j + 1;
			
			// generic data fields
			if (is_array($generic_datafields)) {
				$localEntries = DataFieldEntry::getDataFieldEntries($db->f('user_id'), 'user');
				$k = 1;
				foreach ($generic_datafields as $datafield) {
					if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
						$localEntry = trim($localEntries[$datafield]->getDisplayValue());
						if ($localEntry) {
							$content['PERSONS']['PERSON'][$j]['DATAFIELD_' . $k] = ExternModule::ExtFormatReady($localEntry, TRUE, TRUE);
						}
					}
					$k++;
				}
			}
			$j++;
		}
		
		return $content;
	}
	
	
	function getContentListCharacters () {
		$content = array();
		// FILTER:
		// - müssen Dozent in aktiver Veranstaltung sein
		// - müssen Gruppe in ihrer Einrichtung zugeordnet sein
		
		$db = new DB_Seminar();
		
	//	$query = sprintf("SELECT COUNT(ui.user_id) as count_initiale, UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale FROM user_inst ui LEFT JOIN auth_user_md5 aum USING (user_id) WHERE ui.inst_perms IN ('%s') AND TRIM(aum.Nachname) != '' GROUP BY initiale", implode("','", $this->config->getValue('Main', 'instperms')));
		
		
		if ($this->config->getValue('Main', 'onlylecturers')) {
			$current_semester = get_sem_num(time());
			$query = sprintf("SELECT COUNT(DISTINCT(aum.user_id)) as count_user, "
				. "UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale "
				. "FROM seminar_user su "
				. "LEFT JOIN seminare s USING (seminar_id) "
				. "LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id "
				. "WHERE su.status = 'dozent' AND s.visible = 1 "
				. "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) "
				. "AND TRIM(aum.Nachname) != '' "
				. "GROUP BY initiale",
				$GLOBALS['_views']['sem_number_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_end_sql'],
				$current_semester,
				$GLOBALS['_views']['sem_number_end_sql']);
		} else {
			$query = sprintf("SELECT COUNT(DISTINCT(ui.user_id)) as count_user, "
				. "UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale "
				. "FROM user_inst ui "
				. "LEFT JOIN auth_user_md5 aum USING (user_id) "
				. "WHERE ui.inst_perms IN ('%s') "
				. "AND TRIM(aum.Nachname) != '' "
				. "GROUP BY initiale",
				implode("','", $this->config->getValue('Main', 'instperms')));
		}
		
		$db->query($query);
		while ($db->next_record()) {
			$content['LIST_CHARACTERS']['CHARACTER'][] = array(
				'CHARACTER_USER' => ExternModule::ExtHtmlReady($db->f('initiale')),
				'CHARACTER_COUNT_USER' => ExternModule::ExtHtmlReady($db->f('count_user')),
				'URL_LIST_PERSONS' => $this->getLinkToModule('LinkInternListCharacters', array('initiale' => $db->f('initiale'))));
		}
		return $content;
	}
	
	function getContentListInstitutes () {
		$content = array();
		// getting the institutes from the first level
		$first_levels = $this->range_tree->getKids('root');
	//	var_dump($first_levels);
		$current_semester = get_sem_num(time());
		$db = new DB_Seminar();
		$db_count = new DB_Seminar();
		$query = "SELECT rt.item_id, IF(rt.studip_object_id = '', rt.name, i.Name) AS instname FROM range_tree rt LEFT JOIN Institute i ON (rt.studip_object_id = i.Institut_id) WHERE rt.parent_id = 'root' ORDER BY priority ASC";
		$db->query($query);
		
		while ($db->next_record()) {
			if ($this->config->getValue('Main', 'onlylecturers')) {
				// get only users with status dozent in an visible seminar in the current semester
				$query = sprintf("SELECT COUNT(DISTINCT(su.user_id)) AS count_user "
					. "FROM range_tree rt "
					. "LEFT JOIN user_inst ui ON rt.studip_object_id = ui.Institut_id "
					. "LEFT JOIN seminar_user su USING(user_id) "
					. "LEFT JOIN seminare s USING (seminar_id) "
					. "LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id "
					. "WHERE rt.item_id IN('%s') "
					. "AND su.status = 'dozent' "
					. "AND ui.visible=1 "
					. "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1)))",
					implode("','", $this->range_tree->getKidsKids($db->f('item_id'))),
					$GLOBALS['_views']['sem_number_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_end_sql'],
					$current_semester,
					$GLOBALS['_views']['sem_number_end_sql']);
			} else {
				// get only users with the given status
				$query = sprintf("SELECT COUNT(DISTINCT(ui.user_id)) AS count_user "
					. "FROM range_tree rt "
					. "LEFT JOIN user_inst ui ON rt.studip_object_id = ui.Institut_id "
					. "WHERE rt.item_id IN('%s') "
					. "AND ui.inst_perms IN('%s') "
					. "AND ui.visible=1 ",
					implode("','", $this->range_tree->getKidsKids($db->f('item_id'))),
					implode("','", $this->config->getValue('Main', 'instperms')));
			}
			
			$db_count->query($query);
			
			while ($db_count->next_record()) {
				if ($db_count->f('count_user') > 0) {
					$content['LIST_INSTITUTES']['INSTITUTE'][] = array(
						'INSTITUTE_NAME' => ExternModule::ExtHtmlReady($db->f('instname')),
						'INSTITUTE_COUNT_USER' => $db_count->f('count_user'),
						'URL_LIST_PERSONS' => $this->getLinkToModule('LinkInternListInstitutes', array('item_id' => $db->f('item_id'))));
				}
			}
		}
		
		return $content;
	}
	
	function printout ($args) {
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		echo $this->elements['TemplateMain']->toString(array('content' => $this->getContent($args), 'subpart' => 'PERS_BROWSE'));
		
	}
	
	function printoutPreview () {
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		echo $this->elements['TemplateMain']->toString(array('content' => $this->getContent(), 'subpart' => 'PERS_BROWSE', 'hide_markers' => FALSE));
		
	}
	
}

?>