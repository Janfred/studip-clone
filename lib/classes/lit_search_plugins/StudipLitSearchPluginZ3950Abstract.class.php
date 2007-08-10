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

require_once ("lib/classes/StudipLitCatElement.class.php");
require_once ("lib/classes/lit_search_plugins/StudipLitSearchPluginAbstract.class.php");

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
	var $convert_umlaute = false;
	var $z_accession_bib = "";
	var $z_accession_re = false; // RegEx to check for valid accession number
	
	function StudipLitSearchPluginZ3950Abstract(){
		parent::StudipLitSearchPluginAbstract();
		$this->z_hits =& $this->search_result['z_hits'];
		// UNIMARC mapping
		$this->mapping['UNIMARC'] = array('001' => array('field' => 'accession_number', 'callback' => 'simpleMap', 'cb_args' => ''),
								'010' => array('field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'ISBN: $a'),
								'101' => array('field' => 'dc_language', 'callback' => 'simpleMap', 'cb_args' => '$a'),
								'200' => array('field' => 'dc_title', 'callback' => 'simpleMap', 'cb_args' => '$a $e' . chr(10) . '$f'),
								'210' => array(	array('field' => 'dc_date', 'callback' => 'simpleMap', 'cb_args' => '$d-01-01'),
												array('field' => 'dc_publisher', 'callback' => 'simpleMap', 'cb_args' => '$c, $a')),
								'215' => array('field' => 'dc_format', 'callback' => 'simpleMap', 'cb_args' => '$a, $c'),
								'225' => array('field' => 'dc_relation', 'callback' => 'simpleMap', 'cb_args' => '$a, $v'),
								'300' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => 'Abstract: $a' . chr(10)),
								'328' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => 'Dissertation note:$a' . chr(10)),
								'463' => array('field' => 'dc_publisher', 'callback' => 'simpleMap', 'cb_args' => '$t, $v'),
								'606' => array('field' => 'dc_subject', 'callback' => 'simpleMap', 'cb_args' => ' $a '),
								'700' => array('field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a, $b'),
								'701' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
								'702' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
								'710' => array('field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a, $b'),
								'711' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
								'712' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
								'856' => array('field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'URL: $u '),
								);
		
		//MARC mapping
		$this->mapping['MARC'] = array(	'001' => array('field' => 'accession_number', 'callback' => 'simpleMap', 'cb_args' => ''),
										'008' => array(	array('field' => 'dc_language', 'callback' => 'simpleFixFieldMap', 'cb_args' => array('start'=>35,'length'=>3)),
												array('field' => 'dc_date', 'callback' => 'simpleFixFieldMap', 'cb_args' => array('start'=>7,'length'=>4,'template'=>'{result}-01-01'))),
										'020' => array('field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'ISBN: $a'),
										'245' => array('field' => 'dc_title', 'callback' => 'simpleMap', 'cb_args' => '$a $b $h'),
										'260' => array('field' => 'dc_publisher', 'callback' => 'simpleMap', 'cb_args' => '$a $b'),
										'256' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => '$a' . chr(10)),
										'300' => array('field' => 'dc_format', 'callback' => 'simpleMap', 'cb_args' => '$a $b $c $e'),
										'440' => array('field' => 'dc_relation', 'callback' => 'simpleMap', 'cb_args' => '$a $v'),
										'500' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => '$a' . chr(10)),
										'502' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => 'Dissertation note:$a' . chr(10)),
										'518' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => '$a' . chr(10)),
										'520' => array('field' => 'dc_subject', 'callback' => 'simpleMap', 'cb_args' => '$a' . chr(10)),
										'533' => array('field' => 'dc_description', 'callback' => 'simpleMap', 'cb_args' => '$n' . chr(10)),
										'600' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'610' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'611' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'630' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'650' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'651' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'652' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'653' => array('field' => 'dc_subject', 'callback' => 'simpleListMap', 'cb_args' => false),
										'773' => array('field' => 'dc_publisher', 'callback' => 'simpleMap', 'cb_args' => '$t, $g, $d'),
										'100' => array('field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a'),
										'700' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a','dc_contributor','$a;')),
										'110' => array('field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a, $b'),
										'111' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
										'710' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
										'711' => array('field' => 'dc_creator', 'callback' => 'notEmptyMap', 'cb_args' => array('$a, $b','dc_contributor','$a, $b;')),
										'856' => array('field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'URL: $u '),
										);
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
        yaz_wait(($options = array('timeout' => $this->z_timeout)));
		if (yaz_errno($this->z_id)){
			$this->addError("error", sprintf(_("Fehler bei der Suche: %s"), yaz_error($this->z_id)));
			$this->doResetSearch();
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
	
	function doCheckAccession($accession_number){
		if (!$this->z_accession_bib){
			$this->addError("error", sprintf(_("Attribut für Zugriffsnummer fehlt! (%s)"), strtolower(get_class($this))));
			return false;
		}
		if (!$accession_number){
			$this->addError("error", sprintf(_("Zugriffsnummer fehlt!")));
			return false;
		}
		if (!$this->checkAccessionNumber($accession_number)){
			$this->addError("error", sprintf(_("Zugriffsnummer hat falsches Format für diesen Katalog!")));
			return false;
		}
		if ($this->z_hits){
			$save_result = $this->search_result;
			$save_z_hits = $this->z_hits;
			$this->search_result = array();
			$restore_result = true;
		}
		$this->search_result['rpn'] = '@attr 1=' . $this->z_accession_bib . ' ' . $accession_number ;
		$ret = $this->doSearch();
		if ($restore_result){
				$this->search_result = $save_result;
				$this->z_hits = $save_z_hits;
		} else {
			$this->search_result = array();
		}
		return $ret;
	}
	
	function checkAccessionNumber($accession_number){
		if (!$this->z_accession_re){
			return true;
		} else {
			return preg_match($this->z_accession_re, $accession_number);
		}
	}
	
	function parseSearchValues(){
		$rpn = false;
		$search_values = $this->search_values;
		if (is_array($search_values)){
			$rpn_front = "";
			$rpn_end = "";
			for ($i = 0 ; $i < count($search_values); ++$i){
				$term = $search_values[$i]['search_term'];
				if (strlen($term)){
					if ($this->convert_umlaute){
						$term = $this->ConvertUmlaute($term);
					}
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
					$rpn_end .= " \"" . $term . "\" ";
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
		$plugin_mapping = $this->mapping[$this->z_syntax];
							echo "<hr><pre>".print_r($record,1)."</pre><hr>";

		if ($record){
			$cat_element = new StudipLitCatElement();
			$cat_element->setValue("user_id", $GLOBALS['auth']->auth['uid']);
			$cat_element->setValue("catalog_id", $this->sess_var_name . "__" . $rn );
			$cat_element->setValue("lit_plugin", $this->getPluginName());
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
				if (isset($plugin_mapping[$code])){
					$mapping = (is_array($plugin_mapping[$code][0])) ? $plugin_mapping[$code] : array($plugin_mapping[$code]);
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
		$trim_chars = array('/',',',':');
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
			$result = trim(preg_replace('/(\$[0-9a-z])/', "", $result));
			$last_char = substr($result,-1);
			if (in_array($last_char,$trim_chars)){
				$result = substr($result,0,-1);
			}

		} else {
			$result = trim($data);
		}
		$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result);
		return;
	}
	
	function simpleListMap(&$cat_element, $data, $field, $args){
		$result = trim(preg_replace('/\s*\$[0-9a-z]\s*/', "; ", $data),';');
		$result = (($cat_element->getValue($field)) ? $cat_element->getValue($field) . '; ' . $result : $result);
		$cat_element->setValue($field, $result);
		return;
	}
	
	function simpleFixFieldMap(&$cat_element, $data, $field, $args){
		if (is_array($args) && $data != ""){
			$result = substr($data,$args['start'],$args['length']);
			if ($args['template']){
				$result = str_replace('{result}',$result, $args['template']);
			}
			$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result);
		}
		return;
	}
	
	function notEmptyMap(&$cat_element, $data, $field, $args){
		if (!$cat_element->getValue($field)){
			$this->simpleMap($cat_element, $data, $field, $args[0]);
		} else {
			$this->simpleMap($cat_element, $data, $args[1], $args[2]);
		}
		return;
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
	
	function ConvertUmlaute($text){
		$text = str_replace("ä","ae",$text);
		$text = str_replace("Ä","Ae",$text);
		$text = str_replace("ö","oe",$text);
		$text = str_replace("Ö","Oe",$text);
		$text = str_replace("ü","ue",$text);
		$text = str_replace("Ü","Ue",$text);
		$text = str_replace("ß","ss",$text);
	
		$text = str_replace("É","E",$text);
		$text = str_replace("È","E",$text);
		$text = str_replace("Ê","E",$text);
		$text = str_replace("á","ae",$text);
		$text = str_replace("à","ae",$text);
		$text = str_replace("é","e",$text);
		$text = str_replace("è","e",$text);
		$text = str_replace("î","i",$text);
		$text = str_replace("í","i",$text);
		$text = str_replace("ì","i",$text);
		$text = str_replace("ç","c",$text);
		
		return $text;
	}
}
?>
