<?
/**
* language functions
* 
* helper functions for handling I18N system
* 
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	library
* @module		language.inc
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// language.inc.php
// helper functions for handling I18N system
// Copyright (c) 2003 Stefan Suchi <suchi@data-quest.de>
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


/**
* This function tries to find the preferred language  
*
* This function tries to find the preferred language.
* It returns the first accepted language from browser settings, which is installed.
* 
* @return		string	preferred user language, given in "en_GB"-style	
*
*/
function get_accepted_languages() {
	global $INSTALLED_LANGUAGES;

	$accepted_languages = explode(",", getenv("HTTP_ACCEPT_LANGUAGE"));
	if (is_array($accepted_languages) && count($accepted_languages)) {
		foreach ($accepted_languages as $temp_accepted_language) {
			foreach ($INSTALLED_LANGUAGES as $temp_language => $temp_language_settings) {
				if (substr(trim($temp_accepted_language), 0, 2) == substr($temp_language, 0, 2)) {
					$_language = $temp_language;
					break 2;
				}
			}
		}
	}
	return $_language;
}


/**
* This function starts output via i18n system in the given language  
*
* This function starts output via i18n system in the given language.
* It returns the path to the choosen language.
* 
* @param		string	the language to use for output, given in "en_GB"-style
* @return		string	the path to the language file, given in "en"-style	
*
*/
function init_i18n($_language) {
	global $_language_domain, $INSTALLED_LANGUAGES, $ABSOLUTE_PATH_STUDIP;

	if (isset($_language_domain) && isset($_language)) {
		$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];
		if ($_language != "de_DE") { // German is the original language, so we need no I18N
			putenv("LANG=$_language");
			setlocale(LC_ALL, "");
			bindtextdomain($_language_domain, "$ABSOLUTE_PATH_STUDIP/locale");
			textdomain($_language_domain);
		}
	}
	return $_language_path;
}

?>
