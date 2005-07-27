<?php
function get_group_names($group_field, $groups){
	global $SEM_TYPE, $SEM_CLASS;
	$groupcount = 1;
	if ($group_field == 'sem_tree_id'){
		$the_tree =& TreeAbstract::GetInstance("StudipSemTree", array("build_index" => true));
	}
	if ($group_field == 'sem_number'){
		$all_semester = SemesterData::GetSemesterArray();
	}
	foreach ($groups as $key => $value){
			switch ($group_field){
					case 'sem_number':
					$ret[$key] = $all_semester[$key]['name'];
					break;
					
					case 'sem_tree_id':
					if ($the_tree->tree_data[$key]) {
						//$ret[$key] = $the_tree->getShortPath($key);
						$ret[$key][0] = $the_tree->tree_data[$key]['name'];
						$ret[$key][1] = $the_tree->getShortPath($the_tree->tree_data[$key]['parent_id']);
					} else {
						//$ret[$key] = _("keine Studienbereiche eingetragen");
						$ret[$key][0] = _("keine Studienbereiche eingetragen");
						$ret[$key][1] = '';
					}
					break;
					
					case 'sem_status':
					$ret[$key] = $SEM_TYPE[$key]["name"]." (". $SEM_CLASS[$SEM_TYPE[$key]["class"]]["name"].")";
					break;
					
					case 'not_grouped':
					$ret[$key] = _("keine Gruppierung");
					break;
					
					case 'gruppe':
					$ret[$key] = _("Gruppe")." ".$groupcount;
					$groupcount++;
					break;
					
					default:
					$ret[$key] = 'unknown';
					break;
			}
	}
	return $ret;
}

function sort_groups($group_field, &$groups){
	
	switch ($group_field){
		
		case 'sem_number':
			krsort($groups, SORT_NUMERIC);
		break;
		
		case 'gruppe':
			ksort($groups, SORT_NUMERIC);
		break;
		
		case 'sem_tree_id':
			uksort($groups, create_function('$a,$b',
				'$the_tree =& TreeAbstract::GetInstance("StudipSemTree", array("build_index" => true));
				return (int)($the_tree->tree_data[$a]["index"] - $the_tree->tree_data[$b]["index"]);
				'));
		break;
		
		case 'sem_status':
		uksort($groups, create_function('$a,$b',
				'global $SEM_CLASS,$SEM_TYPE;
				return strnatcasecmp($SEM_TYPE[$a]["name"]." (". $SEM_CLASS[$SEM_TYPE[$a]["class"]]["name"].")",
									$SEM_TYPE[$b]["name"]." (". $SEM_CLASS[$SEM_TYPE[$b]["class"]]["name"].")");'));
		break;
		default:
	}
	
	foreach ($groups as $key => $value){
		usort($value, create_function('$a,$b', 
		'if ($a["gruppe"] != $b["gruppe"]){
			return (int)($a["gruppe"] - $b["gruppe"]);
		} else {
			return strnatcmp($a["name"], $b["name"]);
		}'));
		$groups[$key] = $value;
	}
	return true;

}

function check_group_new($group_members, $my_obj){
	$group_last_modified = false;
	foreach ($group_members as $member){
		$seminar_content = $my_obj[$member['seminar_id']];
		if ($seminar_content['visitdate'] <= $seminar_content["chdate"]
			|| $seminar_content['neuepostings']
			|| $seminar_content['neuedokumente']
			|| $seminar_content['neuenews']
			|| $seminar_content['neuetermine']
			|| $seminar_content['neuelitlist']
			|| $seminar_content['neuscmcontent']
			|| $seminar_content['neuewikiseiten']
			|| $seminar_content['neuevotes']){
			$last_modified = ($seminar_content["chdate"] > $seminar_content['last_modified'] ? $seminar_content["chdate"] : $seminar_content['last_modified']);
			if ($last_modified > $group_last_modified){
				$group_last_modified = $last_modified;
			}
		}
	}
	return $group_last_modified;
}

function correct_group_sem_number(&$groups, &$my_obj){
	if (is_array($groups)){
		$sem_data = SemesterData::GetSemesterArray();
		//end($sem_data);
		//$max_sem = key($sem_data);
		foreach ($sem_data as $sem_key => $one_sem){
			if (!$one_sem['past']){
				$current_sem = $sem_key;
				break;
			}
		}
		if (isset($sem_data[$current_sem + 1])){
			$max_sem = $current_sem + 1;
		} else {
			$max_sem = $current_sem;
		}
		foreach ($my_obj as $seminar_id => $values){
			if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
				if ($values['sem_number_end'] == -1 && $values['sem_number'] != $current_sem){
					unset($groups[$values['sem_number']][$seminar_id]);
					fill_groups($groups, $current_sem, array('seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']));
					if (!count($groups[$values['sem_number']])) unset($groups[$values['sem_number']]);
				} else {
					$to_sem = $values['sem_number_end'];
					for ($i = $values['sem_number']; $i <= $to_sem; ++$i){
						fill_groups($groups, $i, array('seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']));
					}
				}
				if ($GLOBALS['user']->cfg->getValue(null, 'SHOWSEM_ENABLE')){
					$sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
					$sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
					$my_obj[$seminar_id]['name'] .= $sem_name;
				}
			}
		}
		return true;
	}
	return false;
}

function add_sem_name(&$my_obj){
	if ($GLOBALS['user']->cfg->getValue(null, 'SHOWSEM_ENABLE')){
		$sem_data = SemesterData::GetSemesterArray();
		foreach ($my_obj as $seminar_id => $values){
			if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
				$sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
				$sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
				$my_obj[$seminar_id]['name'] .= $sem_name;
			} else {
				$my_obj[$seminar_id]['name'] .= " (" . $sem_data[$values['sem_number']]['name'] . ") ";
			}
		}
	}
	return true;
}

function fill_groups(&$groups, $group_key, $group_entry){
	if (is_null($group_key)){
		$group_key = 'not_grouped';
	}
	$group_entry['name'] = strtolower($group_entry['name']);
	$group_entry['name'] = str_replace("ä","ae",$group_entry['name']);
	$group_entry['name'] = str_replace("ö","oe",$group_entry['name']);
	$group_entry['name'] = str_replace("ü","ue",$group_entry['name']);
	if (!is_array($groups[$group_key]) || (is_array($groups[$group_key]) && !in_array($group_entry, $groups[$group_key]))){
		$groups[$group_key][$group_entry['seminar_id']] = $group_entry;
		return true;
	} else {
		return false;
	}
}
?>
