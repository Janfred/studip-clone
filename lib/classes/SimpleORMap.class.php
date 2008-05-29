<?php
# Lifter002: TODO
/**
* SimpleORMap.class.php
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

@include_once 'lib/dbviews/table_schemes.inc.php';

class SimpleORMap {
	
	// name of the database table 
	var $db_table = '';
	
	var $content = array();
	
	var $is_new = true;
	
	// private
	
	var $db = NULL;
	var $db_fields = null;
	var $pk = null;
	
	function TableScheme ($db_table) {
		static $schemes;
		if (!isset($schemes[$db_table]) && !($schemes[$db_table] = $GLOBALS['DB_TABLE_SCHEMES'][$db_table]) ){
			$db =& new DB_Seminar("SHOW COLUMNS FROM $db_table");
			while($db->next_record()){
				$db_fields[$db->f('Field')] = array('name' => $db->f('Field'),
				'type' => $db->f('Type'),
				'key'  => $db->f('Key'));
				if ($db->f('Key') == 'PRI'){
					$pk[] = $db->f('Field');
				}
			}
			$schemes[$db_table]['db_fields'] = $db_fields;
			$schemes[$db_table]['pk'] = $pk;
		}
		return $schemes[$db_table];
	}
	
	function SimpleORMap ($id = null) {
		$this->db =& new DB_Seminar();
		if (!$this->db_table){
			if (defined(strtoupper(get_class($this)) . '_DB_TABLE')){
				$this->db_table = constant(strtoupper(get_class($this)) . '_DB_TABLE');
			} else {
				$this->db_table = strtolower(get_class($this));
			}
		}
		if (!$this->db_fields){
			$this->getTableScheme();
		}
		if ($id){
			$this->setId($id);
			$this->restore();
		}
	}
	
	function getTableScheme (){
		$scheme = SimpleORMap::TableScheme($this->db_table);
		$this->db_fields = $scheme['db_fields'];
		$this->pk = $scheme['pk'];
	}
	
	function setId ($id){
		if (!is_array($id)){
			$id = array($id);
		}
		if (count($this->pk) != count($id)){
			trigger_error( get_class($this) . ": Invalid ID, Primary Key {$this->db_table} is " .join(",",$this->pk), E_USER_WARNING);
		} else {
			foreach ($this->pk as $count => $key){
				$this->content[$key] = $id[$count];
			}
			return true;
		}
		return false;
	}
	
	function getId (){
		if (count($this->pk) == 1){
			return $this->content[$this->pk[0]];
		} else {
			foreach ($this->pk as $key){
				$id[] = $this->content[$key];
			}
			return (count($this->pk) == count($id) ? $id : false);
		}
	}
	
	function getNewId () {
		$id = false;
		if (count($this->pk) == 1){
			do {
				$id = md5(uniqid($this->db_table,1));
				$this->db->query("SELECT {$this->pk[0]} FROM {$this->db_table} "
					. "WHERE {$this->pk[0]} = '$id'");
			} while($this->db->next_record());
		}
		return $id;
	}
	
	
	function getData () {
		if ($this->haveData()) {
			return $this->content;
		} else {
			return FALSE;
		}
	}
	
	function getValue ($field) {
		return (isset($this->content[$field]) ? $this->content[$field] : null);
	}
	
	function setValue ($field, $value){
		$ret = false;
		if(!in_array($field, $this->pk) && $this->db_fields[$field]){
			if (is_float($value)) $value = str_replace(',','.',$value);
			$ret = ($this->content[$field] = $value);
		}
		return $ret;
	}
	
	function setData ($data, $reset = false) {
		$count = 0;
		if ($reset){
			$this->content = array();
		}
		if (is_array($data)){
			foreach($data as $key => $value){
				if(isset($this->db_fields[$key])){
					$this->content[$key] = $value;
					++$count;
				}
			}
		}
		return $count;
	}
	
	function haveData () {
		return count($this->content);
	}
	
	
	function getWhereQuery (){
		$where_query = null;
		$pk_not_set = array();
		foreach ($this->pk as $key){
			if (isset($this->content[$key])){
				$where_query[] = "{$this->db_table}.{$key}" . "='{$this->content[$key]}'";
			} else {
				$pk_not_set[] = $key;
			}
		}
		if (!$where_query || count($pk_not_set)){
			return false;
		}
		return $where_query;
	}
	
	function restore () {
		$where_query = $this->getWhereQuery();
		if ($where_query){
			$query = "SELECT * FROM {$this->db_table} WHERE "
					. join(" AND ", $where_query);
			$this->db->query($query);
			if ($this->db->next_record()) {
				if ($this->setData($this->db->Record, true)){
					$this->is_new = false;
					return true;
				} else {
					$this->is_new = true;
					return false;
				}
			}
		} else {
			$this->is_new = true;
			return FALSE;
		}
	}
	
	function store () {
		
		if ($this->is_new && !$this->getId()) {
			$this->setId($this->getNewId());
		}

		$where_query = $this->getWhereQuery();

		foreach ($this->content as $key => $value) {
			if (is_float($value)) $value = str_replace(',','.',$value);
			if (isset($this->db_fields[$key]) && $key != 'chdate' && $key != 'mkdate'){
				$query_part[] = "$key = '" . mysql_escape_string(trim($value)) . "' ";
			}
		}
		
		if ($where_query){
			if (!$this->is_new){
				$query = "UPDATE {$this->db_table} SET "
					. implode(',', $query_part);
				$query .= " WHERE ". join(" AND ", $where_query);
				$this->db->query($query);
				if ($this->db->affected_rows()){
					$this->triggerChdate();
				}
			} else {
			$query = "INSERT INTO {$this->db_table} SET "
					. implode(',', $query_part);
			if ($this->db_fields['mkdate']){
				$query .= " ,mkdate=UNIX_TIMESTAMP()";
			}
			if ($this->db_fields['chdate']){
				$query .= " , chdate=UNIX_TIMESTAMP()";
			}
			$this->db->query($query);
			}
			$ret = $this->db->affected_rows();
			$this->restore();
			return $ret;
		} else {
			return false;
		}
	}
	
	function triggerChdate () {
		if ($this->db_fields['chdate']){
			$this->content['chdate'] = time();
			if ($where_query = $this->getWhereQuery()){
				$this->db->query("UPDATE {$this->db_table} SET chdate={$this->content['chdate']}
							WHERE ". join(" AND ", $where_query));
				return true;
			}
		} else {
			return false;
		}
	}
	
	function delete () {
	
		if (!$this->is_new){
			$where_query = $this->getWhereQuery();
			if ($where_query){
				$query = "DELETE FROM {$this->db_table} WHERE "
						. join(" AND ", $where_query);
				$this->db->query($query);
			}
		}
		$this->is_new = true;
		$this->setData(array(), true);
		return TRUE;
	}
}
?>
