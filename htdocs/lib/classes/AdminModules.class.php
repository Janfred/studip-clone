<?
/**
* AdminModules.class.php
* 
* administrate modules (global and local for institutes and Veranstaltungen)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		AdminModules.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AdminModules.class.php
// Administration fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen)
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

require_once $ABSOLUTE_PATH_STUDIP.("functions.php");
require_once $ABSOLUTE_PATH_STUDIP.("forum.inc.php");
require_once $ABSOLUTE_PATH_STUDIP.("config.inc.php");
require_once $ABSOLUTE_PATH_STUDIP.("lib/classes/Modules.class.php");
require_once $ABSOLUTE_PATH_STUDIP.("datei.inc.php");

class AdminModules extends Modules {
	var $db;
	
	function AdminModules() {
		$this->db = new DB_Seminar;
		//please add here the special messages for modules you need consistency checks (defined below in this class)
		$this->registered_modules["forum"]["name"] = _("Forum");
		$this->registered_modules["forum"]["msg_warning"] = _("Wollen Sie wirklich das Forum deaktivieren und damit alle Diskussionbeitr&auml;ge l&ouml;schen?");
		$this->registered_modules["forum"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Forums werden <b>%s</b> Postings ebenfalls gel&ouml;scht!");
		$this->registered_modules["forum"]["msg_activate"] = _("Das Forum kann jederzeit aktiviert werden.");
		$this->registered_modules["forum"]["msg_deactivate"] = _("Das Forum kann jederzeit deaktiviert werden.");


		$this->registered_modules["documents"]["name"] = _("Dateiordner");
		$this->registered_modules["documents"]["msg_warning"] = _("Wollen Sie wirklich den Dateiordner deaktivieren und damit alle hochgeladenen Dokumente l&ouml;schen?");
		$this->registered_modules["documents"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Dateiordners werden <b>%s</b> Dateien ebenfalls gel&ouml;scht!");
		$this->registered_modules["documents"]["msg_activate"] = _("Der Dateiordner kann jederzeit aktiviert werden.");
		$this->registered_modules["documents"]["msg_deactivate"] = _("Der Dateiordner kann jederzeit deaktiviert werden.");

		$this->registered_modules["schedule"]["name"] = _("Ablaufplan");
		$this->registered_modules["schedule"]["msg_activate"] = _("Die Ablaufplanverwaltung kann jederzeit aktiviert werden.");
		$this->registered_modules["schedule"]["msg_deactivate"] = _("Die Ablaufplanverwaltung kann jederzeit deaktiviert werden.");

		$this->registered_modules["participants"]["name"] = _("TeilnehmerInnen");
		$this->registered_modules["participants"]["msg_activate"] = _("Die TeilnehmerInnenverwaltung kann jederzeit aktiviert werden.");
		$this->registered_modules["participants"]["msg_deactivate"] = _("Die TeilnehmerInnenverwaltung kann jederzeit deaktiviert werden. Bachten Sie, dass Sie dann keine normalen Teilnehmer verwalten k&ouml;nnen!");

		$this->registered_modules["personal"]["name"] = _("Personal");
		$this->registered_modules["personal"]["msg_activate"] = _("Die Personalliste kann jederzeit aktiviert werden.");
		$this->registered_modules["personal"]["msg_deactivate"] = _("Die Personalliste kann jederzeit deaktiviert werden.");

		$this->registered_modules["literature"]["name"] = _("Literatur");
		$this->registered_modules["literature"]["msg_activate"] = _("Die Literaturverwaltung kann jederzeit aktiviert werden.");
		$this->registered_modules["literature"]["msg_deactivate"] = _("Die Literaturverwaltung kann jederzeit deaktiviert werden.");

		$this->registered_modules["ilias_connect"]["name"] = _("Lernmodule");
		$this->registered_modules["ilias_connect"]["msg_warning"] = _("Wollen Sie wirklich die Anbindung an ILIAS-Lernmodule deaktivieren und damit alle bestehenden Verkn&uuml;pfungen mit Lernmodulen l&ouml;schen?");
		$this->registered_modules["ilias_connect"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren der Anbindung an ILIAS-Lernmodule werden <b>%s</b> Verkn&uuml;pfungen mit Lernmodulen aufgel&ouml;st!");
		$this->registered_modules["ilias_connect"]["msg_activate"] = _("Die Anbindung zu Ilias Lernmodulen  kann jederzeit aktiviert werden.");
		$this->registered_modules["ilias_connect"]["msg_deactivate"] = _("Die Anbindung zu Ilias Lernmodulen kann jederzeit deaktiviert werden.");

		$this->registered_modules["chat"]["name"] = _("Chat");
		$this->registered_modules["chat"]["msg_activate"] = _("Der Chat kann jederzeit aktiviert werden.");
		$this->registered_modules["chat"]["msg_deactivate"] = _("Der Chat kann jederzeit deaktiviert werden.");

		$this->registered_modules["support"]["name"] = _("SupportDB");
		$this->registered_modules["support"]["msg_activate"] = _("Die SupportDB kann jederzeit aktiviert werden.");
		$this->registered_modules["support"]["msg_deactivate"] = _("Die SupportDB kann jederzeit deaktiviert werden.");

		$this->registered_modules["wiki"]["name"] = _("Wiki-Web");
		$this->registered_modules["wiki"]["msg_warning"] = _("Wollen Sie wirklich das Wiki deaktivieren und damit alle Seitenversionen l&ouml;schen?");
		$this->registered_modules["wiki"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Wiki-Webs werden <b>%s</b> Seitenversionen ebenfalls gel&ouml;scht!");
		$this->registered_modules["wiki"]["msg_activate"] = _("Das Wiki-Web kann jederzeit aktiviert werden.");
		$this->registered_modules["wiki"]["msg_deactivate"] = _("Das Wiki-Web kann jederzeit deaktiviert werden.");

		$this->registered_modules["impuls_ec"]["name"] = _("Impuls EC");
		$this->registered_modules["impuls_ec"]["msg_activate"] = _("Die Impuls-Module können jederzeit aktiviert werden.");
		$this->registered_modules["impuls_ec"]["msg_deactivate"] = _("Die Impuls-Module können jederzeit deaktiviert werden.");

		$this->registered_modules["vips"]["name"] = _("ViPS (Virtuelles Prüfungssystem)");
		$this->registered_modules["vips"]["msg_activate"] = _("ViPS kann jederzeit aktiviert werden.");
		$this->registered_modules["vips"]["msg_deactivate"] = _("ViPS kann jederzeit deaktiviert werden.");
	}
	
	function getModuleForumExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(topic_id) as items FROM px_topics WHERE Seminar_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleForumDeactivate($range_id) {
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		$query = sprintf ("SELECT topic_id FROM px_topics WHERE Seminar_id='%s'", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$query2 = sprintf ("DELETE FROM px_topics WHERE topic_id='%s'", $db->f("topic_id"));
			$db2->query($query2);
		
			$query2 = sprintf ("UPDATE termine SET topic_id = NULL WHERE topic_id='%s'", $db->f("topic_id"));
			$db2->query($query2);
		}
	}
	
	function moduleForumActivate($range_id) {
		global $user;

		//create a default folder
		CreateTopic(_("Allgemeine Diskussionen"), get_fullname($user_id), _("Hier ist Raum für allgemeine Diskussionen"), 0, 0, $range_id);
	}	
	
	function getModuleDocumentsExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(dokument_id) as items FROM dokumente WHERE seminar_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleDocumentsDeactivate($range_id) {
		$db = new DB_Seminar;

		//first, delete the folders with parent = range
		recursiv_folder_delete($range_id);
		
		$query = sprintf ("SELECT termin_id FROM termine WHERE range_id = '%s' ", $range_id);
		
		$db->query($query);
		
		//then delete the folder with parent = termin_id
		while ($db->next_record()) {
			recursiv_folder_delete($db->f("termin_id"));
		}
	}
	
	function moduleDocumentsActivate($range_id) {
		global $user;
		
		$db = new DB_Seminar;

		//create a default folder
		$db->query("INSERT INTO folder SET folder_id='".md5(uniqid("sommervogel"))."', range_id='".$range_id."', user_id='".$user_id."', name='"._("Allgemeiner Dateiordner")."', description='"._("Ablage für allgemeine Ordner und Dokumente der Veranstaltung")."', mkdate='".time()."', chdate='".time()."'");
	}	

	function getModuleIlias_ConnectExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(seminar_id) as items FROM seminar_lernmodul WHERE seminar_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleIlias_ConnectDeactivate($range_id) {
		$db = new DB_Seminar;

		$query = sprintf ("DELETE FROM seminar_lernmodul WHERE seminar_id='%s'", $range_id);
		$db->query($query);
	}
	
	function getModuleWikiExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(keyword) as items FROM wiki WHERE range_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleWikiDeactivate($range_id) {
		return 0;
	}

	function moduleImpuls_ECDeactivate($range_id) {
		return 0;
	}
}
