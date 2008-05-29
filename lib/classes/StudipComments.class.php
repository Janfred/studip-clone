<?php
# Lifter002: TODO
/**
* StudipComments.class.php
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
// Copyright (C) 2005 Tobias Thelen ,	<tthelen@uni-osnabrueck.de>
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

define('STUDIPCOMMENTS_DB_TABLE', 'comments');

class StudipComments extends SimpleORMap {

	function StudipComments($id = null){
		parent::SimpleORMap($id);
	}

	function NumCommentsForObject($object_id) {
		$query = "SELECT COUNT(*) AS count FROM comments WHERE object_id='$object_id'";
		$db = new DB_Seminar();
		$db->query($query);
		$db->next_record();
		return $db->f("count");
	}
	
	function NumCommentsForObjectSinceLastVisit($object_id, $comments_since = 0, $exclude_user_id = null) {
		$query = "SELECT COUNT(*) AS count FROM comments WHERE object_id='$object_id'";
		$query .= " AND chdate > " . (int)$comments_since;
		if ($exclude_user_id) $query .= " AND user_id != '$exclude_user_id'";
		$db = new DB_Seminar();
		$db->query($query);
		$db->next_record();
		return $db->f("count");
	}
	
	function GetCommentsForObject($object_id) {
		global $_fullname_sql;
		$comments=array();
		$query="SELECT comments.content, comments.mkdate, " . $_fullname_sql['full'] ." AS fullname, a.username AS username, comments.comment_id FROM comments LEFT JOIN auth_user_md5 a USING (user_id) LEFT JOIN user_info USING (user_id) WHERE object_id='$object_id' ORDER BY comments.mkdate";
		$db=new DB_Seminar();
		$db->query($query);
		while ($db->next_record()) {
			$comments[]=array($db->f("content"), $db->f("fullname"), $db->f("username"), date("d.m.Y - H:i",$db->f("mkdate")), $db->f("comment_id"));
		}
		return $comments;
	}
	
	function DeleteCommentsByObject($object_ids){
		if (!is_array($object_ids)){
			$object_ids = array($object_ids);
		}
		$query = "DELETE FROM comments WHERE object_id IN ('" . join("','", $object_ids). "')";
		$db = new DB_Seminar($query);
		return $db->affected_rows();
	}
}

?>
