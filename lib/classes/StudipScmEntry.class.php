<?php
# Lifter002: TODO
/**
* StudipScmEntry.class.php
*
*
*
*
* @author	André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id:$
* @access	public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2006 André Noack, Suchi & Berg GmbH <info@data-quest.de>
// 
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

require_once 'lib/classes/SimpleORMap.class.php';

define('STUDIPSCMENTRY_DB_TABLE', 'scm');

class StudipScmEntry extends SimpleORMap {

	function GetSCMEntriesForRange($range_id, $as_objects = false){
		$ret = array();
		$query = "SELECT " . STUDIPSCMENTRY_DB_TABLE . ".* FROM "
					. STUDIPSCMENTRY_DB_TABLE . " WHERE range_id='$range_id' ORDER BY mkdate";
		$db = new DB_Seminar($query);
		while ($db->next_record()){
			if (!$as_objects){
				$ret[$db->f('scm_id')] = $db->Record;
			} else {
				$ret[$db->f('scm_id')] =& new StudipScmEntry();
				$ret[$db->f('scm_id')]->setData($db->Record, true);
				$ret[$db->f('scm_id')]->is_new = false;
			}
		}
		return $ret;
	}
	
	function GetNumSCMEntriesForRange($range_id){
		$query = "SELECT COUNT(*) FROM "
					. STUDIPSCMENTRY_DB_TABLE . " WHERE range_id='$range_id'";
		$db = new DB_Seminar($query);
		$db->next_record();
		return $db->f(0);
	}
	
	function DeleteSCMEntriesForRange($range_ids){
		if (!is_array($range_ids)){
			$range_ids = array($range_ids);
		}
		$query = "DELETE FROM " . STUDIPSCMENTRY_DB_TABLE . " WHERE range_id IN ('" . join("','", $range_ids). "')";
		$db = new DB_Seminar($query);
		return $db->affected_rows();
	}
	
	function StudipScmEntry($id = null){
		parent::SimpleORMap($id);
	}

}

?>
