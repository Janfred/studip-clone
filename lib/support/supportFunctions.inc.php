<?
# Lifter002: TODO
/**
* supportFunction.inc.php
* 
* functions for supportDB
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		supportFunction.inc.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// suppportFunction.inc.php
// Funktionen der SupportDB
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


function calculateGlobalPoints ($range_id) {
	$db = new DB_Seminar;
	
	$query = sprintf("SELECT given_points FROM support_contract WHERE range_id = '%s'", $range_id);
	
	$db->query($query);
	
	while ($db->next_record()) {
		$global_points = $global_points + $db->f("given_points");
	}
	
	return $global_points;
}

function calculateGlobalRemainingPoints ($range_id) {
	$db = new DB_Seminar;
	
	$query = sprintf("SELECT used_points FROM support_event LEFT JOIN support_request USING (request_id) LEFT JOIN support_contract USING (contract_id) WHERE range_id = '%s'", $range_id);
	
	$db->query($query);
	
	while ($db->next_record()) {
		$used_points = $used_points + $db->f("used_points");
	}
	
	return (calculateGlobalPoints ($range_id) - $used_points);
}

function countUnassignedTopics ($range_id) {
	$db = new DB_Seminar;
	
	$query = sprintf("SELECT COUNT(*) AS count FROM px_topics LEFT OUTER JOIN support_request USING (topic_id) WHERE support_request.topic_id IS NULL AND seminar_id = '%s' AND parent_id ='0'", $range_id);

	$db->query($query);
	$db->next_record();
	
	return $db->f("count");
}