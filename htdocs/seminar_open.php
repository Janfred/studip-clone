<?php
/*
seminar_open.php - Initialises a Stud.IP sesssion
Copyright (C) 2000 Stefan Suchi <suchi@data-quest.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

// set default Values for messaging
function check_messaging_default() {
	global $my_messaging_settings;
	
	if (!$my_messaging_settings) {
		$my_messaging_settings=array(
			"show_only_buddys"=>FALSE, 
			"delete_messages_after_logout"=>FALSE,
			"start_messenger_at_startup"=>FALSE,
			"active_time"=>5,
			"default_setted"=>time(),
			"last_login"=>0
			);
		}			
	}
	
	
// set default Values for schedule (timetable)	
function check_schedule_default() {
	global $my_schedule_settings;
		
	if (!$my_schedule_settings) {
		$my_schedule_settings=array(
			"glb_start_time"=>8, 
			"glb_end_time"=>19,
			"glb_days"=>array(
				"mo"=>"TRUE",
				"di"=>"TRUE",
				"mi"=>"TRUE",
				"do"=>"TRUE",
				"fr"=>"TRUE",
				"sa"=>"",
				"so"=>""
			),
			"default_setted"=>time()
		);
	}		
}

// set default Values for calendar	
function check_calendar_default(){
	global $calendar_user_control_data;
	
	if(!$calendar_user_control_data){
		$calendar_user_control_data = array(
			"view"             => "showweek",
			"start"            => 9,
			"end"              => 20,
			"step_day"         => 900,
			"step_week"        => 3600,
			"type_week"        => "LONG",
			"holidays"         => TRUE,
			"sem_data"         => TRUE,
			"link_edit"        => FALSE,
			"bind_seminare"    => "",
			"ts_bind_seminare" => 0,
			"number_of_events" => 0,
			"delete"           => 6
		);
	}
}


require_once("$ABSOLUTE_PATH_STUDIP/language.inc.php");


// user init starts here
if ($auth->is_authenticated() && $user->id != "nobody") {
	if ($SessionStart > $CurrentLogin) {      // just logged in
		// register all user variables
		$LastLogin=$CurrentLogin;
		$CurrentLogin=$SessionStart;
		$user->register("loginfilelast");
		$user->register("loginfilenow");
		$user->register("CurrentLogin");
		$user->register("LastLogin");
		$user->register("forum");
		$user->register("writemode");  // forum postings
		$user->register("my_messaging_settings");
		$user->register("my_schedule_settings");
		$user->register("my_personal_sems");
		
		//garbage collect for user variables
		// loginfilenow und loginfilelast
		$db = new DB_Seminar();
		if (is_array($loginfilenow)){
			$tmp_sem_inst = array();
			$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id IN('" . join("','",array_keys($loginfilenow)) . "')");
			while ($db->next_record()){
				$tmp_sem_inst[$db->f(0)] = true;
			}
			$db->query("SELECT Institut_id FROM Institute WHERE Institut_id IN('" . join("','",array_keys($loginfilenow)) . "')");
			while ($db->next_record()){
				$tmp_sem_inst[$db->f(0)] = true;
			}
			foreach ($loginfilenow as $key => $value){
				if(!isset($tmp_sem_inst[$key])){
					unset($loginfilenow[$key]);
					unset($loginfilelast[$key]);
				}
			}
			unset($tmp_sem_inst);
		}
		
		// call default functions
		check_messaging_default();
		check_schedule_default();
		if($CALENDAR_ENABLE){
			$user->register("calendar_user_control_data");
			check_calendar_default();
		}
	}
}


// session init starts here
if ($SessionStart==0) { 
	$SessionStart=time(); 
	$SessionSeminar="";
	$SessSemName="";
	$sess->register("SessionStart");
	$sess->register("SessionSeminar");
	$sess->register("SessSemName");

	// Language Settings
	$sess->register("_language");
	// try to get accepted languages from browser
	if (!isset($_language))
		$_language = get_accepted_languages();
	if (!$_language)
		$_language = $DEFAULT_LANGUAGE; // else use system default
}		
	
// init of output via I18N

$_language_path = init_i18n($_language);

// function to get the name of the current page in $i_page

$url=parse_url($PHP_SELF);
$i_page_array = explode("/" , $url[path]);
end ($i_page_array);
$i_page = current($i_page_array);
unset($url); unset($i_page_array);
	
// function to get the parameters of the current page in array $i_query

$i_query = explode('&',getenv("QUERY_STRING"));

//include "tracking.inc.php"; //teomporaer. hier wird der User getrackt. 
?>
