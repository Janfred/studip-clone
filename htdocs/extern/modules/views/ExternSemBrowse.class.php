<?
global $ABSOLUTE_PATH_STUDIP;
global $RELATIVE_PATH_CALENDAR;
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/SemBrowse.class.php");

class ExternSemBrowse extends SemBrowse {
	
	var $config;
	
	function ExternSemBrowse (&$config, $sem_browse_data_init = array()) {
		global $_REQUEST;
		$this->config = $config;
		
		$this->group_by_fields = array(	array('name' => _("Semester"), 'group_field' => 'sem_number'),
										array('name' => _("Bereich"), 'group_field' => 'bereich'),
										array('name' => _("DozentIn"), 'group_field' => 'fullname', 'unique_field' => 'username'),
										array('name' => _("Typ"), 'group_field' => 'status'),
										array('name' => _("Einrichtung"), 'group_field' => 'Institut', 'unique_field' => 'Institut_id'));
												
		$sem_browse_data = $sem_browse_data_init;
		
		$this->sem_browse_data =& $sem_browse_data;
		$level_change = isset($_REQUEST['start_item_id']);
		for ($i = 0; $i < count($this->persistent_fields); ++$i){
			if (isset($_REQUEST[$this->persistent_fields[$i]])){
			$this->sem_browse_data[$this->persistent_fields[$i]] = $_REQUEST[$this->persistent_fields[$i]];
			}
		}
		
		$this->search_obj = new StudipSemSearch("search_sem", false);
		$this->search_obj->search_fields['qs_choose']['content'] = array('title' => _("Titel"), 'lecturer' => _("DozentIn"), 'comment' => _("Kommentar"));
		$this->search_obj->search_fields['type']['class'] = $this->sem_browse_data['show_class'];
		
		if (isset($_REQUEST[$this->search_obj->form_name . "_scope_choose"])){
			$this->sem_browse_data["start_item_id"] = $_REQUEST[$this->search_obj->form_name . "_scope_choose"];
		}
		if (isset($_REQUEST[$this->search_obj->form_name . "_range_choose"])){
			$this->sem_browse_data["start_item_id"] = $_REQUEST[$this->search_obj->form_name . "_range_choose"];
		}
		if (isset($_REQUEST[$this->search_obj->form_name . "_sem"])){
			$this->sem_browse_data['default_sem'] = $_REQUEST[$this->search_obj->form_name . "_sem"];
		}
		
		if (isset($_REQUEST['keep_result_set']) || $this->sem_browse_data['sset'] || (count($this->sem_browse_data['search_result']) && $this->sem_browse_data['show_entries'])){
			$this->show_result = true;
		}
		
		if ($this->sem_browse_data['cmd'] == "qs"){
			$this->sem_browse_data['default_sem'] = "all";
		}
		
		if($this->sem_browse_data["default_sem"] != 'all'){
			$this->sem_number[0] = $this->sem_browse_data["default_sem"];
		} else {
			$this->sem_number = false;
		}
		
		if ($this->sem_browse_data['level'] == "vv"){
			if (!$this->sem_browse_data['start_item_id']){
				$this->sem_browse_data['start_item_id'] = "root";
			}
			$this->sem_tree = new StudipSemTreeViewSimple($this->sem_browse_data["start_item_id"], $this->sem_number);
			$this->sem_browse_data['cmd'] = "qs";
			if ($_REQUEST['cmd'] != "show_sem_range" && $level_change && !$this->search_obj->search_button_clicked ){
				$this->get_sem_range($this->sem_browse_data["start_item_id"], false);
				$this->show_result = true;
				$this->sem_browse_data['show_entries'] = "level";
				$this->sem_browse_data['sset'] = false;
			}
		}

		if ($this->sem_browse_data['level'] == "ev") {
			if (!$this->sem_browse_data['start_item_id']){
				$this->sem_browse_data['start_item_id'] = "root";
			}
			$sem_status = (is_array($this->sem_browse_data['sem_status'])) ? $this->sem_browse_data['sem_status'] : false;
			$this->range_tree = new StudipSemRangeTreeViewSimple($sem_browse_data["start_item_id"],$this->sem_number,$sem_status);
			$this->sem_browse_data['cmd'] = "qs";
			$this->get_sem_range_tree($this->sem_browse_data["start_item_id"], false);
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = "level";
			$this->sem_browse_data['sset'] = false;
		}
		
		
		if ($this->search_obj->search_button_clicked && !$this->search_obj->new_search_button_clicked){
			$this->search_obj->override_sem = $this->sem_number;
			$this->search_obj->doSearch();
			if ($this->search_obj->found_rows){
				$this->sem_browse_data['search_result'] = array_flip($this->search_obj->search_result->getRows("seminar_id"));
			} else {
				$this->sem_browse_data['search_result'] = array();
			}
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = false;
			$this->sem_browse_data['sset'] = true;
		}
		
		
		if ($_REQUEST['cmd'] == "show_sem_range"){
			$tmp = explode("_",$_REQUEST['item_id']);
			$this->get_sem_range($tmp[0],isset($tmp[1]));
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? "sublevels" : "level";
			$this->sem_browse_data['sset'] = false;
		}

		if ($_REQUEST['cmd'] == "show_sem_range_tree"){
			$tmp = explode("_",$_REQUEST['item_id']);
			$this->get_sem_range_tree($tmp[0],isset($tmp[1]));
			$this->show_result = true;
			$this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? "sublevels" : "level";
			$this->sem_browse_data['sset'] = false;
		}
		
		if (isset($_REQUEST['do_show_class']) && count($this->sem_browse_data['sem_status'])){
			$this->get_sem_class();
		}
	}
	
	function print_result () {
		global $_fullname_sql,$_views,$PHP_SELF,$SEM_TYPE,$SEM_CLASS;
		if (is_array($this->sem_browse_data['search_result']) && count($this->sem_browse_data['search_result'])) {
			$query = ("SELECT seminare.Seminar_id, seminare.status, seminare.Name 
				, Institute.Name AS Institut,Institute.Institut_id,
				seminar_sem_tree.sem_tree_id AS bereich, "
				. $_fullname_sql[$this->config->getValue("Main", "nameformat")]
				. " AS fullname, auth_user_md5.username,
				" . $_views['sem_number_sql'] . " AS sem_number FROM seminare 
				LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') 
				LEFT JOIN auth_user_md5 USING (user_id) 
				LEFT JOIN user_info USING (user_id) 
				LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id)
				LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id) 
				LEFT JOIN Institute USING (Institut_id) 
				WHERE seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "') ORDER BY 
				 seminare.Name ");
			$db = new DB_Seminar($query);
			$snap = new DbSnapShot($db);
			$group_field = $this->group_by_fields[$this->sem_browse_data['group_by']]['group_field'];
			$data_fields[0] = "Seminar_id";
			if ($this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field']) {
				$data_fields[1] = $this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field'];
			}
			$group_by_data = $snap->getGroupedResult($group_field, $data_fields);
			if ($this->sem_browse_data['group_by'] == 0) {
				krsort($group_by_data, SORT_NUMERIC);
			} else {
				ksort($group_by_data, SORT_STRING);
			}
			$sem_data = $snap->getGroupedResult("Seminar_id");
			echo "\n<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
			if ($this->config->getValue("Main", "addinfo")) {
				echo "\n<tr" . $this->config->getAttributes("TableHeader", "tr") . ">";
				echo "<td><font>&nbsp;";
				echo count($sem_data) ." ";
				echo $this->config->getValue("Main", "textlectures");
				echo ", " . $this->config->getValue("Main", "textgrouping");
				$group_by_name = $this->config->getValue("Main", "aliasesgrouping");
				echo $group_by_name[$this->sem_browse_data['group_by']];
				echo "</font></td></tr>";
			}
			if (!is_object($this->sem_tree))
				$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
			else
				$the_tree =& $this->sem_tree->tree;
			
			foreach ($group_by_data as $group_field => $sem_ids) {
				echo "\n<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
				echo "<td" . $this->config->getAttributes("TableGroup", "td") . ">";
				echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
				switch ($this->sem_browse_data["group_by"]){
					case 0:
					echo $this->search_obj->sem_dates[$group_field]['name'];
					break;
					
					case 1:
					if ($the_tree->getShortPath($group_field))
						echo htmlReady($the_tree->getShortPath($group_field));
					else
						echo _("keine Studienbereiche eingetragen");
					
					break;
					
					case 2:
					echo htmlReady($group_field);
					break;
					
					case 3:
					echo htmlReady($SEM_TYPE[$group_field]["name"]." (". $SEM_CLASS[$SEM_TYPE[$group_field]["class"]]["name"].")");
					break;
					
					case 4:
					echo htmlReady($group_field);
					break;
					
				}
				echo "</font></td></tr>";
				if (is_array($sem_ids['Seminar_id'])){
					while(list($seminar_id,) = each($sem_ids['Seminar_id'])){
						echo"<td><font><a href=\"{$this->target_url}?{$this->target_id}={$seminar_id}&send_from_search=1&send_from_search_page="
						. $PHP_SELF. "?keep_result_set=1\">", htmlReady(key($sem_data[$seminar_id]["Name"])), "</a><br>";
						//create Turnus field
						$temp_turnus_string=view_turnus($seminar_id, TRUE);
						//Shorten, if string too long (add link for details.php)
						if (strlen($temp_turnus_string) >70) {
							$temp_turnus_string = substr($temp_turnus_string, 0, strpos(substr($temp_turnus_string, 70, strlen($temp_turnus_string)), ",") +71);
							$temp_turnus_string .= "...&nbsp;<a href=\"".$this->target_url."?".$this->target_id."=".$seminar_id."&send_from_search=1&send_from_search_page={$PHP_SELF}?keep_result_set=1\">(mehr) </a>";
						}
						echo "</font><font>" . $temp_turnus_string . "</font></td>";
						echo "<td><font>(";
						$doz_name = array_keys($sem_data[$seminar_id]['fullname']);
						$doz_uname = array_keys($sem_data[$seminar_id]['username']);
						if (is_array($doz_name)){
							asort($doz_name, SORT_STRING);
							$i = 0;
							foreach ($doz_name as $index => $value){
								echo "<a href=\"about.php?username=" . $doz_uname[$index] ."\">" . htmlReady($value) . "</a>";
								if($i != count($doz_name)-1){
									echo ", ";
								}
								if ($i == 3){
									echo "...&nbsp;<a href=\"".$this->target_url."?".$this->target_id."=".$seminar_id."&send_from_search=1&send_from_search_page={$PHP_SELF}?keep_result_set=1\">(mehr) </a>";
									break;
								}
								++$i;
							}
							echo ") </font></td></tr>";
						}
					}
				}
			}
			echo "</table>";
		}
	}
}
?>
