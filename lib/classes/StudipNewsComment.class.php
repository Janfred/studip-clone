<?php
# Lifter002: TODO
/**
* StudipNewsComments.class.php
*
*
*
*
* @author	André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access	public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 André Noack <noack@data-quest>,
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

require_once 'lib/classes/SimpleORMap.class.php';

define('STUDIPNEWSCOMMENTS_DB_TABLE', 'comments');

class StudipNewsComments extends SimpleORMap {

	function &GetNewsComments($news_id, $as_objects = false){
		$ret = array();
		$db =& new DB_Seminar();
		$query = "SELECT " . STUDIPNEWSCOMMENTS_DB_TABLE . ".* FROM "
					. STUDIPNEWSCOMMENTS_DB_TABLE . " WHERE object_id='$news_id' ORDER BY chdate DESC";
		$db->query($query);
		while ($db->next_record()){
			if (!$as_objects){
				$ret[$db->f('comment_id')] = $db->Record;
			} else {
				$ret[$db->f('comment_id')] =& new StudipNewsComments();
				$ret[$db->f('comment_id')]->setData($db->Record, true);
				$ret[$db->f('comment_id')]->is_new = false;
			}
		}
		return $ret;
	}
	
	function GetNumNewsComments($news_id){
		$db =& new DB_Seminar();
		$query = "SELECT COUNT(*) FROM "
					. STUDIPNEWSCOMMENTS_DB_TABLE . " WHERE object_id='$news_id'";
		$db->query($query);
		$db->next_record();
		return $db->f(0);
	}
	
	function StudipNewsComments($id = null){
		parent::SimpleORMap($id);
	}
}
?>
