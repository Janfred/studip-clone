<?php
/**
* Displays the details of a requested institute
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$first_name
*	$last_name
*	$user_id
*	$inst_id
*	$directory_search_pc    (page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.11	04.09.2003	19:34:56
* @access		public
* @modulegroup	wap_modules
* @module		show_institute.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_institute.php
// Institute details
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
	require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");

	wap_adm_start_card();

        $db = new DB_Seminar;
        $q_string  = "SELECT Strasse, Plz ";
        $q_string .= "FROM institute ";
        $q_string .= "WHERE Institut_id = \"$inst_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $inst_street = $db-> f("Strasse");
        $inst_post   = $db-> f("Plz");

        $q_string  = "SELECT raum, Telefon, Fax, sprechzeiten ";
        $q_string .= "FROM user_inst ";
        $q_string .= "WHERE user_id = \"$user_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $user_room      = $db-> f("raum");
        $user_phone     = $db-> f("Telefon");
        $user_fax       = $db-> f("Fax");
        $user_cons_time = $db-> f("sprechzeiten");
        $user_groups    = GetStatusgruppen ($inst_id, $user_id);

        echo "<p align=\"left\">";
        if ($user_groups)
            echo join(", ", array_values($user_groups)) . "<br/>";

        if ($user_phone)
        {
            echo wap_txt_encode_to_wml(_("Tel:")) . "&#32;";
            echo wap_txt_encode_to_wml($user_phone) . "<br/>";
        }

        if ($user_fax)
        {
            echo wap_txt_encode_to_wml(_("Fax:")) . "&#32;";
            echo wap_txt_encode_to_wml($user_fax) . "<br/>";
        }

        echo wap_txt_encode_to_wml($inst_street) . ", ";
        echo wap_txt_encode_to_wml($inst_post)   . "<br/>";

        if ($user_room)
        {
            echo wap_txt_encode_to_wml(_("Raum")) . "&#32;";
            echo wap_txt_encode_to_wml($user_room) . "<br/>";
        }

		if ($user_cons_time)
		{
            echo wap_txt_encode_to_wml(_("Sprechzeiten:")) . "<br/>";
            echo wap_txt_encode_to_wml($user_cons_time);
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