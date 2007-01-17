<?php
// Universität Trier  -  Jörg Röpke  -  <roepke@uni-trier.de>
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginZ3950Abstract_Aleph.class.php
// 
// 
// Copyright (c) 2005 Jörg Röpke  -  <roepke@uni-trier.de>
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
require_once ("lib/classes/lit_search_plugins/StudipLitSearchPluginZ3950Abstract.class.php");


class StudipLitSearchPluginZ3950Abstract_Aleph extends StudipLitSearchPluginZ3950Abstract {
	
	var $convert_umlaute = true;
	
	var $superTitle = "";
	var $superAutor = "";
	var $superCity = "";
	var $superPublisher = "";
	
	function StudipLitSearchPluginZ3950Abstract_Aleph() { 
		parent::StudipLitSearchPluginZ3950Abstract();
										
		// USMARC mapping								
		$this->mapping['USMARC'] = array('001' => array('field' => 'accession_number', 'callback' => 'idMap', 'cb_args' => ''),
										 '010' => array('field' => 'dc_title', 'callback' => 'search_superbook', 'cb_args' => FALSE),
										 '100' => array('field' => 'dc_creator', 'callback' => 'authorMap', 'cb_args' => FALSE),
										 '104' => array('field' => 'dc_creator', 'callback' => 'authorMap', 'cb_args' => FALSE),
										 '108' => array('field' => 'dc_creator', 'callback' => 'authorMap', 'cb_args' => FALSE),
										 '112' => array('field' => 'dc_creator', 'callback' => 'authorMap', 'cb_args' => FALSE),
										 '331' => array('field' => 'dc_title', 'callback' => 'titleMap', 'cb_args' => FALSE),
										 '403' => array('field' => 'dc_identifier', 'callback' => 'isbnMap', 'cb_args' => FALSE),
										 '410' => array('field' => 'dc_publisher', 'callback' => 'cityMap', 'cb_args' => '$a $b'),
										 '412' => array('field' => 'dc_publisher', 'callback' => 'publisherMap', 'cb_args' => '$a $b $c'),
 										 '425' => array('field' => 'dc_date', 'callback' => 'simpleFixFieldMap', 'cb_args' => array('start'=>4,'length'=>4,'template'=>'{result}-01-01')),
										 '540' => array('field' => 'dc_identifier', 'callback' => 'isbnMap', 'cb_args' => FALSE)
										 );
	}
	
	function getZRecord_superbook($rn, $my_z_id) {
		$record = yaz_record($my_z_id,$rn,"string");
		$plugin_mapping = $this->mapping[$this->z_syntax];
		$map_array = array(array());
		$index = 0;
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
					for ($k = 0; $k < count($mapping); ++$k){
						$map_array[$index]["data"] = $data;
						$map_array[$index]["method"] = $mapping[$k]['callback'];
						$map_array[$index]["field"] = $mapping[$k]['field'];
						$index++;
					}
				}
			}
			return $map_array;
		} else {
			$this->addError("error",sprintf(_("Datensatz Nummer %s konnte nicht abgerufen werden."), $rn));
			return false;
		}
	}
	

	// YAZ-Verbindung für Suche nach Superbook
	function getYAZ_superbook($my_prefix){
		
		if(strlen($my_prefix) < 13)
			return false;
		
		$my_z_id = yaz_connect($this->z_host,$this->z_options);

		yaz_range($my_z_id, $my_z_id, 1);
		yaz_syntax($my_z_id, $this->z_syntax);
		yaz_search($my_z_id,"rpn", $my_prefix);
		yaz_wait(($options = array('timeout' => $this->z_timeout)));
		if (yaz_errno($my_z_id)){
			$this->addError("error", sprintf(_("Fehler bei der Suche super: %s"), yaz_error($this->z_id)));
			$this->doResetSearch();
			return false;
		}
		else
			return $this->getZRecord_superbook(1, $my_z_id);
	}
	
	
	// suche übergeortnetem Band
	function search_superbook(&$cat_element, $data, $field, $args)
	{
		$my_title = "";
		$my_title_field = "";
		$result = "";
		
		$pos_start = strpos($data, chr(31).chr(97)) + 2;
		$result = substr($data, $pos_start);
		$my_map_array = $this->getYAZ_superbook("@attr 1=12 \"".$result."\"");
				
		for($i = 0; $i < count($my_map_array); ++$i){
			if($my_map_array[$i]["method"] == "titleMap"){
				// Supertitel vorab setzen, falls Titelfunktion bedingt durch Request nicht aufgerufen werden kann
				$this->superTitle = $this->ut_titleMap_Super($my_map_array[$i]["data"]);
				$cat_element->setValue($my_map_array[$i]["field"], $this->superTitle." (...)");
			}
			else if($my_map_array[$i]["method"] == "authorMap"){
				// Superautor vorab setzen, falls Autorfunktion bedingt durch Request nicht aufgerufen werden kann
				$this->superAutor = $this->authorMap_Super($my_map_array[$i]["data"]);
				$cat_element->setValue($my_map_array[$i]["field"], $this->superAutor);
			}
			else if($my_map_array[$i]["method"] == "cityMap"){
				// Supercity vorab setzen, falls Cityfunktion bedingt durch Request nicht aufgerufen werden kann
				$this->superCity = $this->ut_cityMap_Super($my_map_array[$i]["data"]);
				$cat_element->setValue($my_map_array[$i]["field"], $this->superCity);
			}
			else if($my_map_array[$i]["method"] == "publisherMap"){
				// Superpublisher vorab setzen, falls Publisherfunktion bedingt durch Request nicht aufgerufen werden kann
				$this->superPublisher = $this->ut_publisherMap_Super($my_map_array[$i]["data"]);
				$cat_element->setValue($my_map_array[$i]["field"], $this->superPublisher);
			}
		}
		
		// Rücksetzten
		$this->superAutor = "";
		$this->superCity = "";
		$this->superPublisher = "";
		return;
	}
	
	
	// ID Mapping für Hyperlink zum Bibliothekskatalog
	function idMap(&$cat_element, $data, $field, $args)
	{
		$length = strlen($data);
		
		if($data == "")
			return;
			
		$request = preg_split("/[\$]a/", $data);
		$cat_element->setValue($field, "IDN=".$request[1]);
		
		return;
	}
		

	// Titel
	function titleMap(&$cat_element, $data, $field, $args)
	{
		//Eventueller Subtitel oder Supertitel ermitteln
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		
		$result = str_replace('<',' ',$result);
		$result = str_replace('>',' ',$result);
		$result = trim($result);
		
		// Untergeordneter Band -> Supertitel hinzufügen
		if($this->superTitle != "")
			$cat_element->setValue($field, $this->superTitle." ".$result);
		// Haupt- bzw. Übergeordneter Band
		else
			$cat_element->setValue($field, $result);
		$this->superTitle = "";
		return;
	}
	
	// Titel übergeorneter Band
	function ut_titleMap_Super($data)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		
		$result = str_replace('<',' ',$result);
		$result = str_replace('>',' ',$result);

		$result = trim($result);

		return $result;
	}
	
	// Titel und Verlag übergeordneter Band
	function ut_publisherMap_Super($data)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		return " " . $result;
	}
	
	
	// Titel und Verlag 
	function publisherMap(&$cat_element, $data, $field, $args)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result);
		return;
	}
	
	
	// Erscheinungsort übergeordneter Band
	function cityMap(&$cat_element, $data, $field, $args)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result.":");
		return;
	}
	

	// Erscheinungsort übergeordneter Band
	function ut_cityMap_Super($data)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_end = $length - 1;
			$result = substr($data, $pos_start, $length);
		}
		return $result.":";
	}
	
	// Autor übergeordneten Band
	function authorMap_Super($data)
	{
		if($data != ""){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_ende = strpos($data, chr(57));
			if($pos_ende == NULL)
				$pos_ende = strlen($data) + 1;
			if($pos_start >= $pos_ende)
				return;
			$result = substr($data, $pos_start, $pos_ende - $pos_start - 1);
		}
		
		// Herausgeberzusatz ermitteln
		if(preg_match("[\[.*\]]", $data, $result2) == 1)
			$result .= " ".$result2[0];
		$result = str_replace('<',' ',$result);
		$result = str_replace('>',' ',$result);
		$result = trim($result);
		$this->superAutor .= " " . $result.";";
		return $this->superAutor;
	}
	
	// Autor
	function authorMap(&$cat_element, $data, $field , $args)
	{
		if($data != ""){
			$pos_start = strpos($data, chr(31).chr(97)) + 2;
			$pos_ende = strpos($data, chr(57));
			if($pos_ende == NULL)
				$pos_ende = strlen($data) + 1;
			if($pos_start >= $pos_ende)
				return;
			$result = substr($data, $pos_start, $pos_ende - $pos_start - 1);
		}
		
		// Herausgeberzusatz ermitteln
		if(preg_match("[\[.*\]]", $data, $result2) == 1)
			$result .= " ".$result2[0];
		$result = str_replace('<',' ',$result);
		$result = str_replace('>',' ',$result);
		$result = trim($result);
		
		$cat_element->setValue($field, $cat_element->getValue($field) . " " . $result.";");
		return;
	}
	
	// ISBN
	function isbnMap(&$cat_element, $data, $field, $args)
	{
		$length = strlen($data);
		if($length > 2){
			$pos_start = strpos($data, chr(31).chr(97));
			$pos_next = strrpos($data, chr(31));
			if($pos_start == $pos_next){
				$pos_start += 2;
				$result = substr($data, $pos_start, $length);
			}
			else{
				$pos_start += 2;
				$length = $pos_next - $pos_start + 1;
				$result = substr($data, $pos_start, $length);
			}
		}

		$cat_element->setValue($field, "ISBN: ".$result);
		return;
	}

}
?>
