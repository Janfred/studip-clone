<?php
# Lifter007: TODO

require_once 'lib/functions.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/Seminar.class.php';
require_once 'app/models/content_element.php';

abstract class StudipContentElement {
	
	protected $id;
	protected $data;
	
	function __construct($id){
		$this->id = $id;
		$this->restore();
	}
	
	function exists(){
		return $this->id !== null;
	}
	
	abstract function restore();
	
	abstract function isAccessible($user_id);
	
	abstract function getAbstract();
	
	abstract function getTitle();
	
	function getAbstractHtml(){
		return formatready($this->getAbstract());
	}
}

class StudipContentElementForum extends StudipContentElement {
	
	function restore(){
		$db = DBManager::Get();
		$data = $db->query("SELECT * FROM px_topics WHERE topic_id=" . $db->quote($this->id))->fetch(PDO::FETCH_ASSOC);
		if($data){
			$this->data = $data;
			$this->id = $data['topic_id'];
		} else {
			$this->id = null;
		}
		return $this->exists();
	}
	
	function isAccessible($user_id){
		if($this->exists()){
			$type = get_object_type($this->data['Seminar_id']);
			if($type == 'sem'){
				$seminar = Seminar::GetInstance($this->data['Seminar_id']);
				if($seminar->read_level == 0){
					return true;
				} else if ($seminar->read_level == 1){
					return $user_id && $user_id != 'nobody';
				} else {
					return is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm('user', $this->data['Seminar_id'], $user_id);
				}
			} else {
				return true;
			}
		}
		return false;
	}
	
	function getAbstract(){
		return $this->data['description'];
	}
	
	function getTitle(){
		return $this->data['name'];
	}
	
	function getAbstractHtml(){
		include_once 'lib/forum.inc.php';
		return formatready(forum_parse_edit($this->getAbstract()));
	}
}
