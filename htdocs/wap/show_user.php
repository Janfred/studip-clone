<?php
/**
* Shows general information about a user
*
* Parameters received via stdin<br/>
* <code>
*   $session_id
*   $first_name
*   $last_name
*   $user_id
*   $directory_search_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.11	05.09.2003	14:43:11
* @access		public
* @modulegroup	wap_modules
* @module		dates_search.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_user.php
// User information
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
        $q_string  = "SELECT " . $_fullname_sql['full'];
        $q_string .= " AS komplett_name";
        $q_string .= ", Email FROM auth_user_md5 ";
        $q_string .= "INNER JOIN user_info USING (user_id) ";
        $q_string .= "WHERE auth_user_md5.user_id='$user_id'";
        $db-> query("$q_string");
        $db-> next_record();

        $complete_name = $db-> f("komplett_name");
        $e_mail        = $db-> f("Email");

        $q_string  = "SELECT privatnr, privadr ";
        $q_string .= "FROM user_info ";
        $q_string .= "WHERE user_id = \"$user_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $private_nr  = $db-> f("privatnr");
        $private_adr = $db-> f("privadr");

        echo "<p align=\"left\">";
        echo wap_txt_encode_to_wml($complete_name) . "<br/>";
        echo wap_txt_encode_to_wml($e_mail) . "<br/>";
        echo "</p>";

        $q_string  = "SELECT institute.Name, institute.Institut_id ";
        $q_string .= "FROM user_inst, institute ";
        $q_string .= "WHERE user_inst.user_id = '$user_id'";
        $q_string .= "AND user_inst.Institut_id = institute.Institut_id ";
        $q_string .= "AND user_inst.inst_perms != 'user' ";
        $q_string .= "ORDER BY institute.Name";
        $db-> query("$q_string");

        if ($private_nr || $private_adr)
        {
            echo "<p align=\"left\">";
            echo "<anchor>" . wap_txt_encode_to_wml(_("Privat"));
            echo    "<go href=\"show_private.php\">";
            echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo        "<postfield name=\"first_name\" value=\"$first_name\"/>";
            echo        "<postfield name=\"last_name\" value=\"$last_name\"/>";
            echo        "<postfield name=\"user_id\" value=\"$user_id\"/>";
            echo        "<postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>";
            echo    "</go>";
            echo "</anchor>";
            echo "</p>";
        }

        while ($db-> next_record())
        {
            $inst_name = $db-> f("Name");
            $inst_id   = $db-> f("Institut_id");

            $short_name = wap_txt_shorten_text($inst_name, WAP_TXT_LINK_LENGTH * 2);
            echo "<p align=\"left\">";
            echo "<anchor>" . wap_txt_encode_to_wml($short_name);
            echo    "<go href=\"show_institute.php\">";
            echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo        "<postfield name=\"first_name\" value=\"$first_name\"/>";
            echo        "<postfield name=\"last_name\" value=\"$last_name\"/>";
            echo        "<postfield name=\"user_id\" value=\"$user_id\"/>";
            echo        "<postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>";
            echo        "<postfield name=\"inst_id\" value=\"$inst_id\"/>";
            echo    "</go>";
            echo "</anchor>";
            echo "</p>";
        }

        echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_back();
        echo    "<go href=\"directory_search.php\">";
        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo        "<postfield name=\"first_name\" value=\"$first_name\"/>";
        echo        "<postfield name=\"last_name\" value=\"$last_name\"/>";
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