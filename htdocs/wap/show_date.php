<?php
/**
* Displays the details of a requested dates
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$date_id
*	$event_id
*	$num_days
*	$event_sem_name
*	$events_pc			(page counter)
*	$event_dates_pc		(page counter)
*	$dates_search_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.11	05.09.2003	17:30:21
* @access		public
* @modulegroup	wap_modules
* @module		show_date.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_date.php
// Date datails
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
    * Maximum of characters used for event description
    * @const MAX_DESCR_LENGTH
    */
    define("MAX_DESCR_LENGTH", 250);

	include_once("wap_adm.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_buttons.inc.php");
	require_once ($GLOBALS["ABSOLUTE_PATH_STUDIP"]
	            . $GLOBALS["RELATIVE_PATH_CALENDAR"]
		        . "/calendar_func.inc.php");

	$session_user_id = wap_adm_start_card($session_id);
    if ($session_user_id)
    {
        $db = new DB_Seminar();
        $q_string  = "SELECT content, description, date, end_time ";
        $q_string .= "FROM termine ";
        $q_string .= "WHERE termin_id = \"$date_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $event_start = $db-> f("date");
        $event_end   = $db-> f("end_time");
        $event_title = $db-> f("content");
        $event_descr = $db-> f("description");

        $week_day_start = wday($event_start, "SHORT");
        $week_day_end   = wday($event_end, "SHORT");
        $date_start     = date("d.m.", $event_start);
        $date_end       = date("d.m.", $event_end);
        $time_start     = date("H:i", $event_start);
        $time_end       = date("H:i", $event_end);

        echo "<p align=\"center\">";

        if ($event_sem_name)
        {
            $short_event_sem_name = wap_txt_shorten_text($event_sem_name, WAP_TXT_LINE_LENGTH);
            echo "<b>";
            echo wap_txt_encode_to_wml($short_event_sem_name);
            echo "</b><br/>";
        }

        $short_event_title = wap_txt_shorten_text($event_title, WAP_TXT_LINE_LENGTH);
        echo "<b>" . wap_txt_encode_to_wml($short_event_title) . "</b><br/>";
        echo "$week_day_start, $date_start, $time_start<br/>";
        echo "</p>";

        echo "<p align=\"left\">";
        $short_event_descr = wap_txt_shorten_text($event_descr, MAX_DESCR_LENGTH, "cut_end");
        echo wap_txt_encode_to_wml($short_event_descr);
        echo "</p>";

        echo "<p align=\"center\">";
        echo wap_txt_encode_to_wml(_("bis"));
        if ($date_start != $date_end)
            echo " $week_day_end, $date_end,";
        echo " $time_end";
        echo "</p>";

        echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_back();
        if ($event_id)
        {
            echo "<go href=\"event_dates.php\">";
            echo    "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo    "<postfield name=\"event_id\" value=\"$event_id\"/>";
            echo    "<postfield name=\"events_pc\" value=\"$events_pc\"/>";
            echo    "<postfield name=\"event_dates_pc\" value=\"$event_dates_pc\"/>";
            echo "</go>";
        }
        else
        {
            echo "<go href=\"dates_search.php\">";
            echo    "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo    "<postfield name=\"num_days\" value=\"$num_days\"/>";
            echo    "<postfield name=\"dates_search_pc\" value=\"$dates_search_pc\"/>";
            echo "</go>";
        }
        echo "</anchor><br/>";

        wap_buttons_menu_link($session_id);
        echo "</p>";
    }
	wap_adm_end_card();
?>