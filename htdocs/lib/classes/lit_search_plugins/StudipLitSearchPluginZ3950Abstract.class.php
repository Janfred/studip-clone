<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginZ3950Abstract.class.php
// 
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/StudipLitCatElement.class.php");
require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/lit_search_plugins/StudipLitSearchPluginAbstract.class.php");

/**
*
*
* 
*
* @access	public	
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
**/
class StudipLitSearchPluginZ3950Abstract extends StudipLitSearchPluginAbstract{
	
	var $z_host;
	var $z_options = array(); // ('user' => 'dummy', 'password' => 'bla', 'persistent' => true, 'piggyback' => true);
	var $z_id;
	var $z_syntax;
	var $z_start_range = 1;
	var $z_hits = 0;
	var $z_profile = array(); // [attribute] => [name]
	var $z_timeout = 10;
	
	function StudipLitSearchPluginZ3950Abstract(){
		parent::StudipLitSearchPluginAbstract();
		$this->z_hits =& $this->search_result['z_hits'];
	}
	
	function doSearch($search_values = false){
		$rpn =& $this->search_result['rpn'];
		if ($search_values){
			$this->search_values = $search_values;
			$this->search_result = null;
			if ( !($rpn = $this->parseSearchValues()) ){
				return false;
			}
			$this->search_result['rpn'] = $rpn;
		}
		$this->z_id = yaz_connect($this->z_host,$this->z_options);
		if (!$this->z_id){
			$this->addError("error", sprintf(_("Verbindung zu %s kann nicht aufgebaut werden!"), $this->z_host));
			return false;
		}
        yaz_range($this->z_id, $this->z_start_range, 5);
        yaz_syntax($this->z_id, $this->z_syntax);
		yaz_search($this->z_id,"rpn", $rpn);
        yaz_wait(array('timeout' => $this->z_timeout));
		if (yaz_errno($this->z_id)){
			$this->addError("error", sprintf(_("Fehler bei der Suche: %s"), yaz_error($this->z_id)));
			return false;
		} else {
			$this->z_hits = yaz_hits($this->z_id);
			$this->search_result['z_hits'] = $this->z_hits;
			$fetched_records = 0;
			$end_range = (($this->z_start_range + 5) > $this->z_hits) ? $this->z_hits : $this->z_start_range + 5;
			for ($i = $this->z_start_range; $i <= $end_range; ++$i){
				$fetched_records += $this->getZRecord($i);
			}
			return $fetched_records;
		}
	}
	
	function parseSearchValues(){
		$rpn = false;
		$search_values = $this->search_values;
		if (is_array($search_values)){
			$rpn_front = "";
			$rpn_end = "";
			for ($i = 0 ; $i < count($search_values); ++$i){
				if (strlen($search_values[$i]['search_term'])){
					$rpn_end .= " @attr 1=" . $search_values[$i]['search_field'] . " ";
					switch ($search_values[$i]['search_truncate']){
						case "left":
						$truncate = "2";
						break;
						case "right":
						$truncate = "1";
						break;
						case "none":
						$truncate = "100";
						break;
					}
					$rpn_end .= " @attr 5=$truncate ";
					$rpn_end .= " \"" . $search_values[$i]['search_term'] . "\" ";
					if ($i > 0){
						switch ($search_values[$i]['search_operator']){
							case "AND":
							$rpn_front = " @and " . $rpn_front;
							break;
							case "OR":
							$rpn_front = " @or " . $rpn_front;
							break;
							case "NOT":
							$rpn_front = " @not " . $rpn_front;
							break;
						}
					}
				} else if ($i == 0) {
			$this->addError("error", _("Der erste Suchbegriff fehlt."));
			return false;
				}
			}
		}
		$rpn = $rpn_front . $rpn_end;
		return (strlen($rpn)) ? $rpn : false;
	}

	function getZRecord($rn){
		$record = yaz_record($this->z_id,$rn,"string");
		if ($record){
			$cat_element = new StudipLitCatElement();
			$cat_element->setValue("user_id", $GLOBALS['auth']->auth['uid']);
			$cat_element->setValue("catalog_id", $this->sess_var_name . "__" . $rn );
			$lines = explode("\n", $record);
			for ($i = 0; $i < count($lines); ++$i){
				$data = trim($lines[$i]);
				$code = substr($data,0,3);
				$data = trim(substr($data,3));
				$subcode =  substr($data, 0, strpos($data,' '));
				if (is_numeric($subcode) && strlen($subcode) < 3){
					$data = trim(strstr($data,' '));
				} else {
					$data = trim($data);
				}
				if (isset($this->mapping[$code])){
					$mapping = (is_array($this->mapping[$code][0])) ? $this->mapping[$code] : array($this->mapping[$code]);
					for ($j = 0; $j < count($mapping); ++$j){
						$map_method = $mapping[$j]['callback'];
						$this->$map_method($cat_element, $data, $mapping[$j]['field'], $mapping[$j]['cb_args']);
					}
				}
			}
			$this->search_result[$rn] = $cat_element->getValues();
			return 1;
		} else {
			$this->addError("error",sprintf(_("Datensatz Nummer %s konnte nicht abgerufen werden."), $rn));
			return 0;
		}
	}
	
	function simpleMap(&$cat_element, $data, $field, $args){
		if ($args != ""){
			$result = $args;
			$splitted_data = preg_split('/(\$[0-9a-z])/', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0; $i < count($splitted_data); ++$i){
				if ($splitted_data[$i]{0} == '$'){
					$token[] = $splitted_data[$i];
					$content[] = trim($splitted_data[$i+1]);
					++$i;
				}
			}
			$result = str_replace($token, $content, $result);
			$result = preg_replace('/(\$[0-9a-z])/', "", $result);
		} else {
			$result = trim($data);
		}
		$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result);
	}
	
	function getSearchFields(){
		foreach ($this->z_profile as $attribute => $name){
			$ret[] = array('name' => $name, 'value' => $attribute);
		}
		return $ret;
	}
		
	function getSearchResult($num_hit){
		if (!isset($this->search_result[$num_hit]) && $num_hit <= $this->z_hits){
			$this->z_start_range = floor($num_hit/5)*5 + 1;
			$this->doSearch();
		}
		$catalog_id = ($this->search_result[$num_hit]['catalog_id']{0} != "_") ? $this->search_result[$num_hit]['catalog_id'] : false;
		$cat_element = new StudipLitCatElement($catalog_id);
		if ($cat_element->isNewEntry()){
			$cat_element->setValues($this->search_result[$num_hit]);
			$cat_element->setValue("catalog_id", $this->sess_var_name . "__" . $num_hit);
		}
		return $cat_element;
	}
	
	function doResetSearch(){
		$this->search_result = array();
		$this->z_hits = 0;
	}
	
	function getNumHits(){
		return $this->z_hits;
	}
}
?>
