<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitList.class.php
// 
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
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
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbSnapshot.class.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/dbviews/literatur.view.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/TreeAbstract.class.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/StudipLitCatElement.class.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/functions.php");


/**
* class to handle the 
*
* This class provides 
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipLitList extends TreeAbstract {
	
	var $format_default = "**{authors}** - {dc_title} - %%{published}%%";
	
	/**
	* constructor
	*
	* do not use directly, call &TreeAbstract::GetInstance("StudipRangeTree")
	* @access private
	*/ 
	function StudipLitList($range_id) {
		$this->range_id = $range_id;
		$this->range_type = get_object_type($range_id);
		if ($this->range_type == "user"){
			$this->root_name = get_fullname($range_id);
		} else {
			$this->root_name = getHeaderLine($range_id);
		}
		parent::TreeAbstract(); //calling the baseclass constructor 
	}

	/**
	* initializes the tree
	*
	* stores all rows from table range_tree in array $tree_data
	* @access public
	*/
	function init(){
		parent::init();
		$this->view->params[0] = $this->range_id;
		$rs = $this->view->get_query("view:LIT_GET_LIST_BY_RANGE");
		while ($rs->next_record()){
			$list_ids[] =  $rs->f("list_id");
			$this->tree_data[$rs->f("list_id")] = array("user_id" => $rs->f("user_id"),
													"format" => ($rs->f("format")) ? $rs->f("format") : $this->format_default,
													"chdate" => $rs->f("chdate"),
													"fullname" => $rs->f("fullname"),
													"username" => $rs->f("username")
													);
			$this->storeItem($rs->f("list_id"), "root", $rs->f("name"), $rs->f("priority"));
		}
		if (is_array($list_ids)){
			$this->view->params[0] = $list_ids;
			$rs = $this->view->get_query("view:LIT_GET_LIST_CONTENT");
			while ($rs->next_record()){
				$this->tree_data[$rs->f("list_element_id")] = array("user_id" => $rs->f("user_id"),
													"note" => $rs->f("note"),
													"chdate" => $rs->f("chdate"),
													"catalog_id" => $rs->f("catalog_id"),
													"username" => $rs->f("username"),
													"fullname" => $rs->f("fullname")
													);
				$this->storeItem($rs->f("list_element_id"), $rs->f("list_id"), $rs->f("short_name"), $rs->f("priority"));
			}
		}
		if ($this->range_type == "user"){
			$use_view = "LIT_GET_FREELIST_USER";
		} else {
			$use_view = "LIT_GET_FREELIST_RANGE";
		}
		$this->view->params[0] = $this->range_id;
		$rs = $this->view->get_query("view:$use_view");
		if ($rs->next_record()){
			$this->tree_data["root"]["user_id"] = $rs->f("user_id");
			$this->tree_data["root"]["chdate"] = $rs->f("chdate");
			$this->tree_data["root"]["fullname"] = $rs->f("fullname");
			$this->tree_data["root"]["username"] = $rs->f("username");
			$this->tree_data["root"]["content"] = $rs->f("content");
		}
		
	}
	
	function isElement($id){
		return isset($this->tree_data[$id]['catalog_id']);
	}
	
	function getListIds(){
		return $this->getKids("root");
	}
	
	function getNewListId(){
		return md5(uniqid("listbla",1));
	}
	
	function getNewListElementId(){
		return  md5(uniqid("elementbla",1));
	}
	
	function getFormattedEntry($item_id){
		if ($this->isElement($item_id)){
			$format = $this->tree_data[$this->tree_data[$item_id]['parent_id']]['format'];
			$cat_element = new StudipLitCatElement($this->tree_data[$item_id]['catalog_id']);
			$cat_element->fields['note']['value'] = $this->tree_data[$item_id]['note'];
			$content = preg_replace('/({[a-z0-9_]+})/e', "(\$cat_element->getValue(substr('\\1',1,strlen('\\1')-2))) ? \$cat_element->getValue(substr('\\1',1,strlen('\\1')-2)) : '...'", $format);
			return $content;
		} else {
			return false;
		}
	}
			
	function copyList($list_id){
		$this->view->params[] = $list_id;
		$rs = $this->view->get_query("view:LIT_GET_LIST");
		if ($rs->next_record()){
			$new_list_values['list_id'] = $this->getNewListId();
			$new_list_values['range_id'] = $this->range_id;
			$new_list_values['name'] = mysql_escape_string(_("Kopie von: ") . $rs->f("name"));
			$new_list_values['user_id'] = $rs->f("user_id");
			$new_list_values['format'] = mysql_escape_string($rs->f("format"));
			$new_list_values['priority'] = $this->getMaxPriority("root") + 1;
			if ($this->insertList($new_list_values)){
				$this->view->params[] = $this->getNewListElementId();
				$this->view->params[] = $new_list_values['list_id'];
				$this->view->params[] = $list_id;
				$rs = $this->view->get_query("view:LIT_INS_LIST_CONTENT_COPY");
				return $new_list_values['list_id'];
			}
		} 
		return false;
	}
	
	function insertElementBulk($catalog_ids, $list_id){
		if (!is_array($catalog_ids)){
			$catalog_ids[] = $catalog_ids;
		}
		$inserted = 0;
		$priority = $this->getMaxPriority($list_id);
		foreach ($catalog_ids as $cat_id){
			if ($cat_id){
				$inserted += $this->insertElement(array('catalog_id' => $cat_id, 'list_id' => $list_id,
														'list_element_id' => $this->getNewListElementId(),
														'user_id' => $GLOBALS['auth']->auth['uid'],
														'note' => '', 'priority' => ++$priority));
			}
		}
		return $inserted;
	}
	
	function updateContent($content, $user_id){
		$this->view->params[] = $content;
		if ($this->range_type == "user"){
			$use_view = "LIT_UPD_FREELIST_USER";
		} else {
			$use_view = "LIT_UPD_FREELIST_RANGE";
			$this->view->params[] = $user_id;
		}
		$this->view->params[] = $this->range_id;
		$rs = $this->view->get_query("view:$use_view");
		return $rs->affected_rows();
	}
	
	function updateElement($fields){
		if (isset($fields['list_element_id'])){
			$list_element_id = $fields['list_element_id'];
			$this->view->params[] = (isset($fields['list_id'])) ? $fields['list_id'] : $this->tree_data[$list_element_id]['parent_id'];
			$this->view->params[] = (isset($fields['catalog_id'])) ? $fields['catalog_id'] : $this->tree_data[$list_element_id]['catalog_id'];
			$this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_element_id]['user_id'];
			$this->view->params[] = (isset($fields['note'])) ? $fields['note'] : mysql_escape_string($this->tree_data[$list_element_id]['note']);
			$this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_element_id]['priority'];
			$this->view->params[] = $list_element_id;
			$rs = $this->view->get_query("view:LIT_UPD_LIST_CONTENT");
			return $rs->affected_rows();
		} else {
			return false;
		}
	}
	
	function insertElement($fields){
		if (isset($fields['list_element_id'])){
			$list_element_id = $fields['list_element_id'];
			$this->view->params[] = (isset($fields['list_id'])) ? $fields['list_id'] : $this->tree_data[$list_element_id]['parent_id'];
			$this->view->params[] = (isset($fields['catalog_id'])) ? $fields['catalog_id'] : $this->tree_data[$list_element_id]['catalog_id'];
			$this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_element_id]['user_id'];
			$this->view->params[] = (isset($fields['note'])) ? $fields['note'] : mysql_escape_string($this->tree_data[$list_element_id]['note']);
			$this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_element_id]['priority'];
			$this->view->params[] = $list_element_id;
			$rs = $this->view->get_query("view:LIT_INS_LIST_CONTENT");
			return $rs->affected_rows();
		} else {
			return false;
		}
	}
	
	function deleteElement($element_id){
		$this->view->params[] = $element_id;
		$rs = $this->view->get_query("view:LIT_DEL_LIST_CONTENT");
		return $rs->affected_rows();
	}
	
	function updateList($fields){
		if (isset($fields['list_id'])){
			$list_id = $fields['list_id'];
			$this->view->params[] = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;
			$this->view->params[] = (isset($fields['name'])) ? $fields['name'] : mysql_escape_string($this->tree_data[$list_id]['name']);
			$this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_id]['user_id'];
			$this->view->params[] = (isset($fields['format'])) ? $fields['format'] : mysql_escape_string($this->tree_data[$list_id]['format']);
			$this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_id]['priority'];
			$this->view->params[] = $list_id;
			$rs = $this->view->get_query("view:LIT_UPD_LIST");
			return $rs->affected_rows();
		} else {
			return false;
		}
	}
	
	function insertList($fields){
		if (isset($fields['list_id'])){
			$list_id = $fields['list_id'];
			$this->view->params[] = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;
			$this->view->params[] = (isset($fields['name'])) ? $fields['name'] : mysql_escape_string($this->tree_data[$list_id]['name']);
			$this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_id]['user_id'];
			$this->view->params[] = (isset($fields['format'])) ? $fields['format'] : mysql_escape_string($this->tree_data[$list_id]['format']);
			$this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_id]['priority'];
			$this->view->params[] = $list_id;
			$rs = $this->view->get_query("view:LIT_INS_LIST");
			return $rs->affected_rows();
		} else {
			return false;
		}
	}
	
	function deleteList($list_id){
		$deleted = 0;
		$this->view->params[] = $list_id;
		$rs = $this->view->get_query("view:LIT_DEL_LIST");
		$deleted += $rs->affected_rows();
		$this->view->params[] = $list_id;
		$rs = $this->view->get_query("view:LIT_DEL_LIST_CONTENT_ALL");
		$deleted += $rs->affected_rows();
		return $deleted;
	}
}
?>
