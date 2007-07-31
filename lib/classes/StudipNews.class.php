<?php
/**
* StudipNews.class.php
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
require_once 'lib/classes/StudipComments.class.php';
require_once 'lib/classes/Config.class.php';
require_once 'lib/object.inc.php';

define('STUDIPNEWS_DB_TABLE', 'news');

class StudipNews extends SimpleORMap {

	var $ranges = array();

	function &GetNewsByRange($range_id, $only_visible = false, $as_objects = false){
		$ret = array();
		$db = new DB_Seminar();
		if ($only_visible){
			$clause = " AND date < UNIX_TIMESTAMP() AND (date+expire) > UNIX_TIMESTAMP() ";
		}
		$query = "SELECT " . STUDIPNEWS_DB_TABLE . ".* FROM " . STUDIPNEWS_DB_TABLE . "_range
					INNER JOIN " . STUDIPNEWS_DB_TABLE . " USING(news_id) WHERE range_id='$range_id' "
					. $clause . " ORDER BY date DESC, chdate DESC, topic ASC";
		$db->query($query);
		while ($db->next_record()){
			$ret[$db->f('news_id')] = $db->Record;
		}
		return ($as_objects ? StudipNews::GetNewsObjects($ret) : $ret);
	}

	function &GetNewsByAuthor($user_id, $as_objects = false){
		$ret = array();
		$db = new DB_Seminar();
		$query = "SELECT " . STUDIPNEWS_DB_TABLE . ".* FROM "
					. STUDIPNEWS_DB_TABLE . " WHERE user_id='$user_id' ORDER BY date DESC, chdate DESC";
		$db->query($query);
		while ($db->next_record()){
			$ret[$db->f('news_id')] = $db->Record;
		}
		return ($as_objects ? StudipNews::GetNewsObjects($ret) : $ret);
	}

	function &GetNewsByRSSId($rss_id, $as_objects = false){
		if ($user_id = StudipNews::GetUserIDFromRssID($rss_id)){
			return StudipNews::GetNewsByRange($user_id, true, $as_objects);
		} else {
			return array();
		}
	}

	function &GetNewsObjects($news_result){
		$objects = array();
		if (is_array($news_result)){
			foreach($news_result as $id => $result){
				$objects[$id] =& new StudipNews();
				$objects[$id]->setData($result, true);
				$objects[$id]->is_new = false;
			}
		}
		return $objects;
	}

	function GetUserIdFromRssID($rss_id){
		$ret = StudipNews::GetRangeIdFromRssID($rss_id);
		return $ret['range_id'];
	}

	function GetRssIdFromUserId($user_id){
		return StudipNews::GetRssIdFromRangeId($user_id);
	}
	
	function GetRangeFromRssID($rss_id){
		if ($rss_id){
			$db = new DB_Seminar("SELECT range_id,range_type FROM news_rss_range WHERE rss_id='$rss_id'");
			if ($db->next_record())	return array('range_id' => $db->f(0), 'range_type' => $db->f(1));
		}
		return false;
	}
	
	function GetRangeIdFromRssID($rss_id){
		$ret = StudipNews::GetRangeIdFromRssID($rss_id);
		return $ret['range_id'];
	}
	
	function GetRssIdFromRangeId($range_id){
		$db = new DB_Seminar("SELECT rss_id FROM news_rss_range WHERE range_id='$range_id'");
		$db->next_record();
		return $db->f(0);
	}
	
	function SetRssId($range_id, $type = false){
		if (!$type){
			$type = get_object_type($range_id);
			if ($type == 'fak') $type = 'inst';
		}
		$rss_id = md5('StudipRss'.$range_id);
		$db = new DB_Seminar("REPLACE INTO news_rss_range (range_id,rss_id,range_type) VALUES ('$range_id','$rss_id','$type')");
		$db->next_record();
		return $db->affected_rows();
	}
	
	function UnsetRssId($range_id){
		$db = new DB_Seminar("DELETE FROM news_rss_range WHERE range_id='$range_id'");
		$db->next_record();
		return $db->affected_rows();
	}
	
	function GetAdminMsg($user_id, $date){
		return sprintf(_("Zuletzt aktualisiert von %s (%s) am %s"),get_fullname($user_id) ,get_username($user_id) ,date("d.m.y",$date));
	}

	function DoGarbageCollect(){
		$db =& new DB_Seminar();
		$cfg =& Config::GetInstance();
		if (!$cfg->getValue('NEWS_DISABLE_GARBAGE_COLLECT')){
			$db->query("SELECT news.news_id FROM news where (date+expire)<UNIX_TIMESTAMP() ");
			while($db->next_record()) {
				$result[$db->Record[0]] = true;
			}
			$db->query("SELECT news_range.news_id FROM news_range LEFT JOIN news USING (news_id) WHERE ISNULL(news.news_id)");
			while($db->next_record()) {
				$result[$db->Record[0]] = true;
			}
			$db->query("SELECT news.news_id FROM news LEFT JOIN news_range USING (news_id) WHERE range_id IS NULL");
			while($db->next_record()) {
				$result[$db->Record[0]] = true;
			}
			if (is_array($result)) {
				$kill_news = "('".join("','",array_keys($result))."')";
				$db->query("DELETE FROM news WHERE news_id IN $kill_news");
				$killed = $db->affected_rows();
				$db->query("DELETE FROM news_range WHERE news_id IN $kill_news");
				object_kill_visits(null, array_keys($result));
				object_kill_views(array_keys($result));
				StudipComments::DeleteCommentsByObject(array_keys($result));
			}
			return $killed;
		}
	}

	function TouchNews($news_id, $touch_stamp = null){
		$ret = false;
		if(!$touch_stamp) $touch_stamp = time();
		$news =& new StudipNews($news_id);
		if(!$news->is_new){
			$news->setValue('date', mktime(0,0,0,strftime("%m",$touch_stamp),strftime("%d",$touch_stamp),strftime("%y",$touch_stamp)));
			$ret = $news->store();
			$news->triggerChdate();
		}
		return $ret;
	}
	
	function DeleteNewsRanges($range_id){
		$db =& new DB_Seminar("DELETE FROM news_range WHERE range_id='$range_id'");
		$ret = $db->affected_rows();
		StudipNews::DoGarbageCollect();
		return $ret;
	}
	
	function DeleteNewsByAuthor($user_id){
		foreach (StudipNews::GetNewsByAuthor($user_id, true) as $news){
			$deleted += $news->delete();
		}
		return $deleted;
	}
	
	function StudipNews($id = null){
		$this->db_table = STUDIPNEWS_DB_TABLE;
		parent::SimpleORMap($id);
	}

	function restore(){
		$ret = parent::restore();
		$this->restoreRanges();
		return $ret;
	}

	function restoreRanges(){
		$this->ranges = array();
		if (!$this->is_new){
			$this->db->query("SELECT range_id FROM {$this->db_table}_range WHERE news_id='".$this->getId()."'");
			while($this->db->next_record()){
				$this->ranges[$this->db->f(0)] = true;
			}
		}
		return count($this->ranges);
	}

	function store(){
		$ret = parent::store();
		$this->storeRanges();
		return $ret;
	}

	function storeRanges(){
		if (!$this->is_new){
			$where_query = $this->getWhereQuery();
			if ($where_query){
				$this->db->query("DELETE FROM {$this->db_table}_range WHERE  news_id='".$this->getId()."'");
				if (count($this->ranges)){
					foreach($this->getRanges() as $range_id){
						$this->db->query("INSERT INTO {$this->db_table}_range SET range_id='$range_id',news_id='".$this->getId()."'");
					}
				}
				return count($this->ranges);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getRanges(){
		return array_keys($this->ranges);
	}

	function issetRange($range_id){
		return isset($this->ranges[$range_id]);
	}

	function addRange($range_id){
		if (!$this->issetRange($range_id)){
			return ($this->ranges[$range_id] = true);
		} else {
			return false;
		}
	}

	function deleteRange($range_id){
		if ($this->issetRange($range_id)){
			unset($this->ranges[$range_id]);
			return true;
		} else {
			return false;
		}
	}

	function setData($data, $reset = false){
		$count = parent::setData($data, $reset);
		if ($reset){
			$this->restoreRanges();
		}
		return $count;
	}

	function delete() {
		$this->ranges = array();
		$this->storeRanges();
		object_kill_visits(null, $this->getId());
		object_kill_views($this->getId());
		StudipComments::DeleteCommentsByObject($this->getId());
		parent::delete();
		return true;
	}
}
/*
$test =& StudipNews::GetNewsByRange('1c4aacc51b8feea444d85d7183bff9fe');
echo "<pre>";
print_r($test);
$test =& StudipNews::GetNewsByRange('1c4aacc51b8feea444d85d7183bff9fe', true);
print_r($test);
*/
?>
