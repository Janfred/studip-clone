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
class StudipLitSearchPluginTIBUBOpac extends StudipLitSearchPluginZ3950Abstract{
	
	
	function StudipLitSearchPluginTIBUBOpac(){
		parent::StudipLitSearchPluginZ3950Abstract();
		$this->description = "Technische Informationsbibliothek / Universitätsbibliothek Hannover";
		$this->z_host = "z3950.gbv.de:20010/tib_opc";
		$this->z_options = array('user' => '999', 'password' => 'abc');
		$this->z_syntax = "MARC";
		$this->convert_umlaute = true;
		$this->z_accession_bib = "12";
		$this->z_accession_re = '/[0-9]{8}[0-9X]{1}/';
		$this->z_profile = array('1016' => _("alle Wörter [ALL]"),
					 '1004' => _("Person, Autor [PER]"),					 					 
					 '4' => _("Titelstichwörter [TIT]"),
					 '5' => _("Stichwörter Serie/Zeitschrift [SER]"),
					 '1005' => _("Stichwörter Körperschaft [KOR]"),					 					 
					 '46' => _("Schlagwörter [SWW]"),					 					 
					 '54' => _("Signatur [SGN]"),					 					 
					 '1007' => _("alle Nummern (ISBN, ISSN ...) [NUM]")
		); /* '4' => _("Titelanfänge [TAF]"),
		   herausgenommen, da keine Unterscheidung anhand von Wort oder Phrase durchgeführt wird. 
		   */
	}
}
?>

