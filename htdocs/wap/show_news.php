<?php
/**
* Displays the details of a requested news entry.
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$news_id
*	$event_id
*	$news_pc		(page counter)
*	$events_pc		(page counter)
*	$event_news_pc	(page counter)
*	$show_news_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		show_news.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_news.php
// News details
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
        if ($show_news_pc)
        {
            $page_counter = $show_news_pc;
        }
        else
        {
            $page_counter = 0;
        }

        $db = new DB_Seminar();

        $q_string  = "SELECT body, date, topic FROM news ";
        $q_string .= "WHERE news_id=\"$news_id\"";

        $db-> query("$q_string");
        $db-> next_record();
        $news_title = $db-> f("topic");
        $news_body  = $db-> f("body");
        $news_date  = $db-> f("date");

        $num_pages   = 0;
        $news_part   = wap_txt_devide_text($news_body, $page_counter, $num_pages);
        $news_date   = date("d.m.Y", $news_date);
        $short_title = wap_txt_shorten_text($news_title, 2 * WAP_TXT_LINE_LENGTH);

        if ($page_counter == 0)
        {
	        echo "<p align=\"center\">";
	        echo "<b>" . wap_txt_encode_to_wml($short_title) . "</b><br/>";
	        echo wap_txt_encode_to_wml(_("Vom")) . " $news_date";
	        echo "</p>";
    	}

        echo "<p align=\"left\">";
        echo wap_txt_encode_to_wml($news_part);
        echo "</p>";

        echo "<p align=\"right\">";
        if ($num_pages > 0)
        {
            if ($page_counter < $num_pages)
            {
                $page_counter_v = $page_counter + 1;
                echo "<anchor>" . wap_buttons_forward_part($page_counter_v, $num_pages + 1);
                echo    "<go href=\"show_news.php\">";
                echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                echo        "<postfield name=\"news_id\" value=\"$news_id\"/>";
                echo        "<postfield name=\"show_news_pc\" value=\"$page_counter_v\"/>";
                if ($event_id)
                {
		            echo    "<postfield name=\"event_id\" value=\"$event_id\"/>";
        		    echo    "<postfield name=\"events_pc\" value=\"$events_pc\"/>";
		            echo    "<postfield name=\"event_news_pc\" value=\"$event_news_pc\"/>";
                }
                else
                {
		            echo    "<postfield name=\"news_pc\" value=\"$news_pc\"/>";
		        }
                echo    "</go>";
                echo "</anchor><br/>";
            }
            if ($page_counter > 0)
            {
                $page_counter_v = $page_counter - 1;
                echo "<anchor>" . wap_buttons_back_part($page_counter_v, $num_pages + 1);
                echo    "<go href=\"show_news.php\">";
                echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                echo        "<postfield name=\"news_id\" value=\"$news_id\"/>";
                echo        "<postfield name=\"show_news_pc\" value=\"$page_counter_v\"/>";
                if ($event_id)
                {
		            echo    "<postfield name=\"event_id\" value=\"$event_id\"/>";
        		    echo    "<postfield name=\"events_pc\" value=\"$events_pc\"/>";
		            echo    "<postfield name=\"event_news_pc\" value=\"$event_news_pc\"/>";
                }
                else
                {
		            echo    "<postfield name=\"news_pc\" value=\"$news_pc\"/>";
		        }
                echo    "</go>";
                echo "</anchor><br/>";
            }
        }
        echo "<anchor>" . wap_buttons_back();
        if ($event_id)
        {
            echo "<go href=\"event_news.php\">";
            echo    "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo    "<postfield name=\"event_id\" value=\"$event_id\"/>";
            echo    "<postfield name=\"events_pc\" value=\"$events_pc\"/>";
            echo    "<postfield name=\"event_news_pc\" value=\"$event_news_pc\"/>";
            echo "</go>";
        }
        else
        {
            echo "<go href=\"news.php\">";
            echo    "<postfield name=\"session_id\" value=\"$session_id\"/>";
            echo    "<postfield name=\"news_pc\" value=\"$news_pc\"/>";
            echo "</go>";
        }
        echo "</anchor><br/>";

        wap_buttons_menu_link($session_id);
        echo "</p>";
    }
	wap_adm_end_card();
?>