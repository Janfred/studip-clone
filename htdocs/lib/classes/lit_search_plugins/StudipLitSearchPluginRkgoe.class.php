<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginRkgoe.class.php
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

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/lit_search_plugins/StudipLitSearchPluginZ3950Abstract.class.php");

/**
* Plugin for retrieval using Z39.50 
*
* 
*
* @access	public	
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
**/
class StudipLitSearchPluginRkgoe extends StudipLitSearchPluginZ3950Abstract{
	
	
	function StudipLitSearchPluginRkgoe(){
		parent::StudipLitSearchPluginZ3950Abstract();
		$this->description = "Göttinger Gesamtkatalog (Regionalkatalog Göttingen)";
		$this->z_host = "z3950.gbv.de:20010/rkgoe";
		$this->z_options = array('user' => '999', 'password' => 'abc');
		$this->z_syntax = "UNIMARC";
		$this->convert_umlaute = true;
		$this->z_profile = array('1016' => _("Basisindex [ALL]"), '2' => _("Körperschaftsname [KOS]"),
								'3' => _("Kongress [KNS]"),'4' => _("Titelstichwörter [TIT]"),
								'5' => _("Serienstichwörter [SER]"), '7' => _("ISBN [ISB]"),
								'8' => _("ISSN [ISS]"), '12' => _("PICA Prod.-Nr [PPN]"),
								'21' => _("alle Klassifikationen [SYS]"), '1004' => _("Person, Author [PER]"),
								'1005' => _("Körperschaften [KOR]"), '1006' => _("Kongresse [KON]"),
								'1007' => _("alle Nummern [NUM]"));
		$this->mapping = array('001' => array('field' => 'accession_number', 'callback' => 'simpleMap', 'cb_args' => ''),
								'010' => array('field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'ISBN: $a '),
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
	}
}
?>
