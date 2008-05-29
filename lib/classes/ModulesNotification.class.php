<?php
# Lifter002: TODO
/**
* ModulesNotification.class.php
* 
* check for modules (global and local for institutes and Veranstaltungen), read and write
* 
*
* @author		Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Modules.class.php
// Checks fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen), Schreib-/Lesezugriff
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once 'lib/classes/Modules.class.php';
require_once 'lib/meine_seminare_func.inc.php';

if ($GLOBALS['ILIAS_CONNECT_ENABLE']) {
	include_once ($RELATIVE_PATH_LEARNINGMODULES.'/lernmodul_db_functions.inc.php'); 
}

class ModulesNotification extends Modules {

	var $registered_notification_modules = array();
	var $subject;
	var $smtp;
	
	function ModulesNotification () {
		parent::Modules();
		$this->registered_notification_modules['news'] = array(
				'id' => 25, 'const' => '', 'sem' => TRUE, 'inst' => TRUE,
				'mes' => TRUE, 'name' => _("News"));
		$this->registered_notification_modules['votes'] = array(
				'id' => 26, 'const' => '', 'sem' => TRUE, 'inst' => FALSE,
				'mes' => TRUE, 'name' => _("Umfragen und Votings"));
		$this->registered_notification_modules['basic_data'] = array(
				'id' => 27, 'const' => '', 'sem' => TRUE, 'inst' => FALSE,
				'mes' => TRUE, 'name' => _("Grunddaten der Veranstaltung"));
		$this->subject = _("Stud.IP Benachrichtigung");
		$extend_modules = array(
				"forum" => array('mes' => TRUE, 'name' =>  _("Forum")),
				"documents" => array('mes' => TRUE, 'name' => _("Dateiordner")),
				"schedule" => array('mes' => TRUE, 'name' => _("Ablaufplan")),
				"participants" => array('mes' => FALSE, 'name' => _("TeilnehmerInnen")),
				"personal" => array('mes' => FALSE, 'name' => _("Personal")),
				"literature" => array('mes' => TRUE, 'name' => _("Literatur")),
				"ilias_connect" => array('mes' => TRUE, 'name' => _("Lernmodule")),
				"chat" => array('mes' => FALSE, 'name' => _("Chat")),
				"wiki" => array('mes' => TRUE, 'name' => _("Wiki-Web")),
				"support" => array('mes' => FALSE, 'name' => _("SupportDB")),
				"scm" => array('mes' => TRUE, 'name' => _("Freie Informationsseite")),
				"elearning_interface" => array('mes' => TRUE, 'name' => _("Lernmodule")));
		$this->registered_modules = array_merge_recursive($this->registered_modules,
				$extend_modules);
		$this->smtp =& new studip_smtp_class();
	}
	
	function getGlobalEnabledNotificationModules ($range) {
		$enabled_modules = array();
		foreach ($this->registered_modules as $name => $data) {
			if ($data[$range] && $data['mes'] && $this->checkGlobal($name)) {
				$enabled_modules[$name] = $data;
			}
		}
		foreach ($this->registered_notification_modules as $name => $data) {
			if ($data[$range]) {
				$enabled_modules[$name] = $data;
			}
		}
		return sizeof($enabled_modules) ? $enabled_modules : FALSE;
	}
	
	function getAllModules () {
		return $this->registered_modules + $this->registered_notification_modules;
	}
	
	function getAllNotificationModules () {
		$modules = array();
		foreach ($this->registered_modules as $name => $data) {
			if ($data['mes']) {
				$modules[$name] = $data;
			}
		}
		return $modules + $this->registered_notification_modules;
	}
	
	function setModuleNotification ($m_array, $range = NULL, $user_id = NULL) {
		if (!is_array($m_array)) {
			return FALSE;
		}
		if (is_null($user_id)) {
			$user_id = $GLOBALS['user']->id;
		}
		if (is_null($range)) {
			reset($m_array);
			$range = get_object_type(key($m_array));
		}
		foreach ($m_array as $range_id => $value) {
			$sum = array_sum($value);
			if ($sum > 0xffffffff) {
				return FALSE;
			}
			if ($range == 'sem') {
				$this->db->query("UPDATE seminar_user SET notification = $sum
						WHERE Seminar_id = '$range_id' AND user_id = '$user_id'");
			} else {
				return FALSE;
			//	$this->db->query("UPDATE user_inst SET mod_message = $sum
			//			WHERE Institut_id = '$range_id' AND user_id = '$user_id'");
			}
		}
		return TRUE;
	}
	
	function getModuleNotification ($range = 'sem', $user_id = NULL) {
		if (is_null($user_id)) {
			$user_id = $GLOBALS['user']->id;
		}
		if ($range == 'sem') {
			$this->db->query("SELECT Seminar_id, notification FROM seminar_user
					WHERE user_id = '$user_id'");
		} else {
			return FALSE;
		}
		$settings = array();
		while ($this->db->next_record()) {
			$settings[$this->db->f('Seminar_id')] = $this->db->f('notification');
		}
		return $settings;
	}
	
	// only range = 'sem' is implemented
	function getAllNotifications ($user_id = NULL) {
		if (is_null($user_id)) {
			$user_id = $GLOBALS['user']->id;
		}
		
		$this->db->query("SELECT s.Seminar_id, s.Name, s.chdate,
				s.start_time, s.modules, IFNULL(visitdate, 0) as visitdate
				FROM seminar_user su LEFT JOIN seminare s USING (Seminar_id)
				LEFT JOIN object_user_visits ouv
				ON (ouv.object_id = su.Seminar_id AND ouv.user_id = '$user_id'
				AND ouv.type='sem')
				WHERE su.user_id = '$user_id' AND su.status != 'user'");
		
		$my_sem = array();
		while ($this->db->next_record()){
			$my_sem[$this->db->f('Seminar_id')] = array(
				//	'visitdate' => $this->db->f('visitdate'),
					'name' => $this->db->f('Name'),
					'chdate' => $this->db->f('chdate'),
					'start_time' => $this->db->f('start_time'),
					'modules' => $this->db->f('modules'),
					'visitdate' => $this->db->f('visitdate'),
					$this->db->f('modules'));
		}
		
		$m_enabled_modules = $this->getGlobalEnabledNotificationModules('sem');
		$m_all_notifications = $this->getModuleNotification('sem', $user_id);
		$m_extended = 0;
		foreach ($this->registered_notification_modules as $m_data) {
			$m_extended += pow(2, $m_data['id']);
		}
		get_my_obj_values($my_sem, $user_id);
		$text = '';
		foreach ($my_sem as $seminar_id => $s_data) {
			$m_notification = ($s_data['modules'] + $m_extended)
					& $m_all_notifications[$seminar_id];
			$m_text = '';
			foreach ($m_enabled_modules as $m_name => $m_data) {
				if ($this->isBit($m_notification, $m_data['id'])) {
					$m_text .= $this->getModuleText($m_name, $seminar_id, $s_data, 'sem');
				}
			}
			if ($m_text) {
				$text .= "\n\n";
				$text .= sprintf(_("In der Veranstaltung \"%s\" gibt es folgende Neuigkeiten:"),
						$s_data['name']);
				$text .= "\n\n" . $m_text;
			}
		}
		if ($text) {
			$text = _("Diese Email wurde automatisch vom Stud.IP-System verschickt. Sie können auf diese Nachricht nicht antworten.")
						. "\n" . _("Sie erhalten hiermit in regelmäßigen Abständen Informationen über Neuigkeiten und Änderungen in Ihren abonierten Veranstaltungen.")
						. "\n\n" . _("Über welche Inhalte Sie informiert werden wollen, können Sie hier einstellen:")
						. "\n{$this->smtp->url}sem_notification.php"
						. "\n" . $text
						. "\n\n--\n"
						. _("Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie können darauf nicht antworten.");
		}
		return $text;
	}
	
	// only range = 'sem' is implemented
	function getModuleText ($m_name, $range_id, $r_data, $range) {
		$text = '';
		switch ($m_name) {
			case 'forum' :
				if ($r_data['neuepostings'] > 1) {
					$text = sprintf(_("%s neue Beiträge im Forum:"), $r_data['neuepostings']);
				} else if ($r_data['neuepostings'] > 0) {
					$text = ("1 neuer Beitrag im Forum:");
				}
				$redirect = '&again=yes&redirect_to=forum.php&view=neue&sort=age';
				break;
			case 'documents' :
				if ($r_data['neuedokumente'] > 1) {
					$text = sprintf(_("%s neue Dokumente hochgeladen:"), $r_data['neuedokumente']);
				} else if ($r_data['neuedokumente'] > 0) {
					$text = _("1 neues Dokument hochgeladen:");
				}
				$redirect = '&again=yes&redirect_to=folder.php&cmd=all';
				break;
			case 'schedule' :
				if ($r_data['neuetermine'] > 1) {
					$text = sprintf(_("%s neue Termine angelegt:"), $r_data['neuetermine']);
				} else if ($r_data['neuetermine'] > 0) {
					$text = _("1 neuer Termin angelegt:");
				}
				$redirect = '&again=yes&redirect_to=dates.php#a';
				break;
			case 'literature' :
				if ($r_data['neuelitlist'] > 1) {
					$text = sprintf(_("%s neue Literaturlisten angelegt"), $r_data['neuelitlist']);
				} else if ($r_data['neuelitlist'] > 0) {
					$text = _("1 neue Literaturliste angelegt");
				}
				$redirect = '&again=yes&redirect_to=literatur.php';
				break;
			case 'ilias_connect' :
				/* sinnlos...
				if ($GLOBALS['ILIAS_CONNECT_ENABLE']) {
					$mod_count = get_seminar_modules($range_id);
					if ($mod_count > 1) {
						$text = sprintf(_("%s Lernmodule sind mit der Veranstaltung verbunden:"), $mod_count);
					} else if ($mod_count > 0) {
						$text = _("1 Lernmodul ist mit der Veranstaltung verbunden:");
					}
				}
				$redirect = "&again=yes&redirect_to=seminar_lernmodule.php&seminar_id=$range_id";
				*/
				break;
			case 'elearning_interface' :
				if ($GLOBALS['ELEARNING_INTERFACE_ENABLE']) {
					if ($r_data['neuecontentmodule'] > 1) {
						$text = sprintf(_("%s neue Content-Module angelegt"), $r_data['neuecontentmodule']);
					} else if ($r_data['neuecontentmodule'] > 0) {
						$text = _("1 neues Content-Modul angelegt");
					}	
					$redirect = "&again=yes&redirect_to=elearning_interface.php&seminar_id=$range_id&view=show";
				}
				break;
			case 'wiki' :
				if ($r_data['neuewikiseiten'] > 1) {
					$text = sprintf(_("%s Wikiseiten wurden angelegt oder bearbeitet:"), $r_data['neuewikiseiten']);
				} else if ($r_data['neuewikiseiten'] > 0) {
					$text = _("1 Wikiseite wurde angelegt oder bearbeitet:");
				}
				$redirect = '&again=yes&redirect_to=wiki.php&view=listnew';
				break;
			case 'scm' :
				if ($r_data['neuscmcontent']) {
					$text = sprintf(_("Die Seite \"%s\" wurde neu angelegt oder bearbeitet:"), $r_data['scmtabname']);
				}
				$redirect = '&again=yes&redirect_to=scm.php';
				break;
			case 'votes' :
				if ($GLOBALS['VOTE_ENABLE']) {
					if ($r_data['neuevotes'] > 1) {
						$text = sprintf(_("%s neue Umfragen oder Evaluationen wurden angelegt:"), $r_data['neuevotes']);
					} else if ($r_data['neuevotes'] > 0) {
						$text = _("1 neue Umfrage oder Evaluation wurde angelegt:");
					}
				}
				$redirect = '&again=yes#votes';
				break;
			case 'news' :
				if ($r_data['neuenews'] > 1) {
					$text = sprintf(_("%s neu News wurden angelegt:"), $r_data['neuenews']);
				} else if ($r_data['neuenews']) {
					$text = _("1 neue News wurde angelegt:");
				}
				$redirect = '&again=yes';
				break;
			case 'basic_data' :
				if ($r_data['chdate'] > $r_data['visitdate']) {
					$text = _("Die Grunddaten wurden geändert:");
				}
				$redirect = '&again=yes&redirect_to=details.php';
				break;
			default :
				$redirect = '';
		}
		if ($range == 'sem' && $text != '') {
			$text .= "\n{$this->smtp->url}seminar_main.php?";
			$text .= "auswahl=$range_id$redirect\n";
		} 
		return $text;
	}
	
}
?>
