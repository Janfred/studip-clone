<?php
/**
* Displays information about a selected person.
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$first_name
*	$last_name
*	$user_id
*	$directory_search_pc    (page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		show_private.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_private.php
// Personal details
// Copyright (c) 2003 Florian Hansen <f1701h@gmx.net>
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
    * workaround for PHPDoc
    *
    * Use this if module contains no elements to document!
    * @const PHPDOC_DUMMY
    */
    define("PHPDOC_DUMMY", TRUE);

	include_once("wap_adm.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_buttons.inc.php");

	wap_adm_start_card();

        $db = new DB_Seminar;
        $q_string  = "SELECT privatnr, privadr ";
        $q_string .= "FROM user_info ";
        $q_string .= "WHERE user_id = \"$user_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $private_nr  = $db-> f("privatnr");
        $private_adr = $db-> f("privadr");

        echo "<p align=\"left\">";

        if ($private_adr)
            echo wap_txt_encode_to_wml($private_adr) . "<br/>";

        if ($private_nr)
        {
            echo wap_txt_encode_to_wml(_("Tel:")) . "&#32;";
            echo wap_txt_encode_to_wml($private_nr) . "<br/>";
        }

        echo "</p>";

        echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_back();
        echo    "<go href=\"show_user.php\">";
        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo        "<postfield name=\"first_name\" value=\"$first_name\"/>";
        echo        "<postfield name=\"last_name\" value=\"$last_name\"/>";
        echo        "<postfield name=\"user_id\" value=\"$user_id\"/>";
        echo        "<postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>";
        echo    "</go>";
        echo "</anchor><br/>";

        echo "<anchor>" . wap_buttons_new_search();
        echo    "<go href=\"directory.php\">";
        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo    "</go>";
        echo "</anchor><br/>";

        wap_buttons_menu_link ($session_id);
        echo "</p>";

    wap_adm_end_card();
?>