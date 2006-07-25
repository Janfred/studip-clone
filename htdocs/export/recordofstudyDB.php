<?php

/**

 * Creates a record of study and exports the data to pdf (database)

 *

 * @author      Christian Bauer <alfredhitchcock@gmx.net>

 * @version     $Exp

 * @copyright   2003 Stud.IP-Project

 * @access      public

 * @module      recordofstudy

 */
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");

/**
 * collect the current seminars and concerning semesters from the archiv	
 *
 * @access  private
 * @returns array the semesters
 *
 */
function getSemesters(){
	global $user;
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	// creating the list of avaible semester
	for ($i=0;$i<sizeof($all_semester); $i++){
//		$position = sizeof($all_semester)+1-$i;
		$semestersAR[$i]["beginn"] = $all_semester[$i]["beginn"];
		$semestersAR[$i]["id"] = $i;
		$semestersAR[$i]["idname"] = $all_semester[$i]["name"];
		$semestersAR[$i]["name"] = convertSemester($all_semester[$i]["name"]);
	}

	// adding the semester from avaible archiv-items
	$db = &new DB_Seminar ();
	$db->query ("SELECT archiv.start_time, archiv.semester, archiv.start_time "
		. "FROM archiv_user LEFT "
		. "JOIN archiv  USING (seminar_id) "
		. "WHERE archiv_user.user_id = '".$user->id."' "
		. "GROUP BY archiv.semester ORDER BY start_time DESC");
	while ($db->next_record()) {
		$found = 0;
		for ($j=0; $j<sizeof($all_semester); $j++){
			if (in_array($db->f("semester"), $all_semester[$j],1))
				$found++;
		}
		if ($found == 0){
			$semestersAR[$i]["beginn"] = $db->f("start_time");
			$semestersAR[$i]["id"] = $i;
			$semestersAR[$i]["idname"] = $db->f("semester");
			$semestersAR[$i]["name"] = convertSemester($db->f("semester"));
			$semestersAR[$i]["onlyarchiv"] = 1;
			$i++;
		}

	}
	return $semestersAR;
}

/**
 * collects the basic data from the db
 *
 * @access  private
 * @returns array 	the basic data
 *
 */
function getBasicData(){
global $user;
	$db = &new DB_Seminar ();
	// Für eigene Fehlerroutine am Ende auf "no" schalten!...
	$db->Halt_On_Error = "yes";


	// get field of study
	$db->query("SELECT user_studiengang.*,studiengaenge.name "
		. "FROM user_studiengang LEFT JOIN studiengaenge USING (studiengang_id) "
		. "WHERE user_id = '".$user->id."' "
		. "ORDER BY studiengang_id");
	$db->query ($query);

	while ($db->next_record()) {
		$fieldofstudy .= $db->f("name")." ";
	}

	//get fullname
	$db->query("SELECT user_info.title_front as tv"
		.", user_info.title_rear as tr"
		.", auth_user_md5.Vorname vn"
		.", auth_user_md5.Nachname as nn"
		." FROM auth_user_md5 LEFT JOIN user_info USING (user_id)"
		." WHERE auth_user_md5.user_id = '".$user->id."'");
//		." HAVING (sem_number <= $i AND (sem_number_end >= $i OR sem_number_end = -1))");
	$db->next_record();
	$fullname = htmlReady($db->f("tv")." ".$db->f("tr")." ".$db->f("vn")." ".$db->f("nn"));

	return array(
		"fieldofstudy"	=> $fieldofstudy,
		"studentname"	=> $fullname,
	);
}

/**
 * gets the field of study of the current user from the db
 *
 * @access  private
 * @returns string 	the field of study 
 *
 */
function getFieldOfStudy(){
	global $user;
	
	$db = &new DB_Seminar ();
	// Für eigene Fehlerroutine am Ende auf "no" schalten!...
	$db->Halt_On_Error = "yes";
	

	// get field of study
	$db->query("SELECT user_studiengang.*,studiengaenge.name FROM user_studiengang LEFT JOIN studiengaenge USING (studiengang_id) WHERE user_id = '".$user->id."' ORDER BY studiengang_id");

	while ($db->next_record()) {
		$fieldofstudy .= $db->f("name")." ";
	}
	return $fieldofstudy;
}
 
/**
 * gets the complete name of the student
 *
 * @access  private
 * @returns string 	the complete name
 *
 */
function getStudentname(){
	global $user;
	
	$db = &new DB_Seminar ();
	// Für eigene Fehlerroutine am Ende auf "no" schalten!...
	$db->Halt_On_Error = "yes";
	
	//get fullname
	$db->query("SELECT user_info.title_front as tv"
		.", user_info.title_rear as tr"
		.", auth_user_md5.Vorname vn"
		.", auth_user_md5.Nachname as nn"
		." FROM auth_user_md5 LEFT JOIN user_info USING (user_id)"
		." WHERE auth_user_md5.user_id = '".$user->id."'");
	$db->next_record();
	$fullname = htmlReady($db->f("tv")." ".$db->f("tr")." ".$db->f("vn")." ".$db->f("nn"));

	return $fullname;
}

/**
 * gets the seminars of the currents user from the db
 *
 * @access  private
 * @param   string $semesterid		the selected semester id
 * @param   boolean $onlyseminars	could reduce the assortment
 * @returns array 	the seminars
 *
 */
function getSeminare($semesterid,$onlyseminars){
	global $user,$semestersAR,$SEM_CLASS,$SEM_TYPE,$_fullname_sql;
	
	$db = &new DB_Seminar ();
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	$i = 0;
	// if its not an archiv-only-semester, get the current ones
	if(!$semestersAR[$semesterid]["onlyarchiv"]){

		// the status the user should have in the seminar
		$status = "autor";
		
		// some stolen code from a.noack :)
		foreach ($all_semester as $key => $value){
			$sem_start_times[] = $value['beginn'];
		}
		foreach ($SEM_CLASS as $key => $value){
			if ($value['bereiche']){
				foreach($SEM_TYPE as $type_key => $type_value){
					if($type_value['class'] == $key)
						$allowed_sem_status[] = $type_key;
				}
			}
		}	


		// new seminars
		$db2 = &new DB_Seminar ();
		
		$query = "SELECT b.Seminar_id,b.Name,b.Untertitel,b.VeranstaltungsNummer, "
			. "INTERVAL(start_time," . join(",",$sem_start_times) .") AS sem_number , "
			. "IF(duration_time=-1,-1,INTERVAL(start_time+duration_time," . join(",",$sem_start_times) .")) AS sem_number_end "
			. "FROM seminar_user a LEFT JOIN seminare b USING(Seminar_id) WHERE ";
		
		if($onlyseminars)
			$query .= ((is_array($allowed_sem_status)) ? " b.status IN('" . join("','",$allowed_sem_status) . "') AND " : "") ." ";
			
		$query .= " a.user_id='".$user->id."' AND a.status='".$status."' "
			. "HAVING (sem_number <= ".$semestersAR[$semesterid]["id"]." AND (sem_number_end >= ".$semestersAR[$semesterid]["id"]." OR sem_number_end = -1))";
		$db->query($query);	

		while ($db->next_record()) {
			$seminarid = $db->f("Seminar_id");
			$name = $db->f("Name");
			$seminarnumber = $db->f("VeranstaltungsNummer");
			$description = $db->f("Untertitel");
				if ($description)
					$name .= ": ".$description;
			$sem_number_start = $db->f("sem_number");
			$sem_number_end = $db->f("sem_number_end");

			$db2->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id)  LEFT JOIN user_info USING(user_id) WHERE seminar_user.Seminar_id = '".$seminarid."' AND status = 'dozent' ORDER BY Nachname");
			$tutor = '';
			while($db2->next_record()){
				if ($tutor) $tutor .= "; ";
				$tutor .= $db2->f("fullname");
			}

			$seminare[$i] = array(
				"id" 			=> $i,
				"seminarid" 	=> $seminarid,
				"seminarnumber" => $seminarnumber,
				"tutor" 		=> $tutor,
				"sws"			=> "",
				"description" 	=> $name 
				);
			$i++;
		}
	}

	//archiv seminars
	$db->query ("SELECT archiv.name, archiv.seminar_id, archiv_user.status, archiv.VeranstaltungsNummer, archiv.name, archiv.semester, archiv.untertitel, archiv.studienbereiche, archiv.dozenten "
		. "FROM archiv_user LEFT JOIN archiv  USING (seminar_id) "
		. "WHERE archiv_user.user_id = '".$user->id."' AND archiv.semester = '".$semestersAR[$semesterid]["idname"]."'");
	while($db->next_record()){

		$seminarid = $db->f("seminar_id");
		$name = $db->f("name");
		$seminarnumber = $db->f("VeranstaltungsNummer");
		$description = $db->f("untertitel");
			if ($description)
				$name .= ": ".$description;	
		$tutor = $db->f("dozenten");
		$semesterDB = $db->f("semester");

		if( (!$onlyseminars) || 
			($onlyseminars && $db->f("studienbereiche")))
			$seminare[$i] = array(
			"id" 			=> $i,
			"seminarid" 	=> $seminarid,
			"seminarnumber" => $seminarnumber,
			"tutor" 		=> $tutor,
			"description" 	=> $name 
			);
		$i++;
	}
	
	return $seminare;
}
?>
