<?php
/**
* Displays the details of a short messages.
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$sms_id
*	$sms_pc			(page counter)
*	$show_sms_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		show_sms.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_sms.php
// Short message details
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

	$session_user_id = wap_adm_start_card($session_id);
	if ($session_user_id)
    {
        if ($show_sms_pc)
        {
            $page_counter = $show_sms_pc;
        }
        else
        {
            $page_counter = 0;
        }

        $db = new DB_Seminar();
        $q_string  = "SELECT user_id_snd, mkdate, message ";
        $q_string .= "FROM globalmessages ";
        $q_string .= "WHERE message_id=\"$sms_id\"";

        $db-> query("$q_string");
        $db-> next_record();
        $sender   = $db-> f("user_id_snd");
        $sms_date = $db-> f("mkdate");
        $message  = $db-> f("message");

        $num_pages    = 0;
        $message_part = wap_txt_devide_text($message, $page_counter, $num_pages);
        $short_sender = wap_txt_shorten_text($sender, WAP_TXT_LINE_LENGTH - 4);
        $sms_date     = date("d.m.Y, H:i", $sms_date);

        if ($page_counter == 0)
        {
            echo "<p align=\"center\">";
            echo "<b>" . wap_txt_encode_to_wml(_("Von")) . "&#32;";
            echo wap_txt_encode_to_wml($short_sender) . "</b><br/>";
            echo "$sms_date";
            echo "</p>";
        }

        echo "<p align=\"left\">";
        echo wap_txt_encode_to_wml($message_part);
        echo "</p>";

        echo "<p align=\"right\">";
        if ($num_pages > 0)
        {
            if ($page_counter < $num_pages)
            {
                $page_counter_v = $page_counter + 1;
                echo "<anchor>" . wap_buttons_forward_part($page_counter_v, $num_pages + 1);
                echo    "<go href=\"show_sms.php\">";
                echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                echo        "<postfield name=\"sms_id\" value=\"$sms_id\"/>";
                echo        "<postfield name=\"sms_pc\" value=\"$sms_pc\"/>";
                echo        "<postfield name=\"show_sms_pc\" value=\"$page_counter_v\"/>";
                echo    "</go>";
                echo "</anchor><br/>";
            }
            if ($page_counter > 0)
            {
                $page_counter_v = $page_counter - 1;
                echo "<anchor>" . wap_buttons_back_part($page_counter_v, $num_pages + 1);
                echo    "<go href=\"show_sms.php\">";
                echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                echo        "<postfield name=\"sms_id\" value=\"$sms_id\"/>";
                echo        "<postfield name=\"sms_pc\" value=\"$sms_pc\"/>";
                echo        "<postfield name=\"show_sms_pc\" value=\"$page_counter_v\"/>";
                echo    "</go>";
                echo "</anchor><br/>";
            }
        }
        echo "<anchor>" . wap_buttons_back();
        echo    "<go href=\"sms.php\">";
        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo        "<postfield name=\"sms_pc\" value=\"$sms_pc\"/>";
        echo    "</go>";
        echo "</anchor><br/>";

        wap_buttons_menu_link($session_id);
        echo "</p>";
    }
	wap_adm_end_card();
?>