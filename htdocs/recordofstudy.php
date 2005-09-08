<?php
/**

 * Creates a record of study and exports the data to pdf

 *

 * @author      Christian Bauer <alfredhitchcock@gmx.net>

 * @version     $Id$

 * @copyright   2003 Stud.IP-Project

 * @access      public

 * @module      recordofstudy

 */

/* ************************************************************************** *
/*
/* the structure of the pdf-template:
/*
/*		-- form 'university'		// the name of the university
/*		-- form 'fieldofstudy'		// the field of study
/*		-- form 'studentname'		// the complete name of the student
/*		-- form 'semester'			// the semester
/*		-- form 'semesternumber'	// the semester number
/*
/*         (X := 0 -> last entry)/*
/*		-- form 'seminarnumber.X'	// the number of the seminar
/*		-- form 'tutor.X'			// the complete tutor name
/*		-- form 'sws.X'				// the average hours per semester
/*		-- form 'description.X'		// the name (+ discription) of the seminar
/* 																			  *
/* ************************************************************************* */

/* ************************************************************************** *
/*																			  *
/* initialise Stud.IP-Session												  *
/*																			  *
/* ************************************************************************* */
page_open (array ("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
		  "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check ("autor");
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");
require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php");
{
// needed session-variables
$sess->register("seminars");
$sess->register("semestersAR");
$sess->register("template");
}

/* **END*of*initialise*Stud.IP-Session*********************************** */

/* ************************************************************************** *
/*																			  *
/* including needed files													  *
/*																			  *
/* ************************************************************************* */
// if you wanna create a pdf no html-header should be send to the browser
if (!isset($_GET["create_pdf"])){
	require_once($ABSOLUTE_PATH_STUDIP . "html_head.inc.php");
	require_once($ABSOLUTE_PATH_STUDIP . "header.php");
	require_once($ABSOLUTE_PATH_STUDIP . "links_seminare.inc.php");
	include_once($ABSOLUTE_PATH_STUDIP . $PATH_EXPORT ."/recordofstudy.lib.php");
	include_once($ABSOLUTE_PATH_STUDIP . $PATH_EXPORT ."/recordofstudyDB.php");
}
/* **END*of*initialize*post/get*variables*********************************** */

/* ************************************************************************** *
/*																			  *
/* identify the current site-mode  											  *
/*																			  *
/* ************************************************************************* */
$semester = $_POST['semester'];
if(	(isset($_POST["semester_selected_x"])) || (isset($_POST["add_seminars_x"])) ||
	(isset($_POST["delete_seminars_x"])))
	$mode = "edit";
elseif (isset($_POST["create_pdf_x"]))
	$mode = "pdf_assortment";
elseif (isset($_GET["create_pdf"]))
	$mode = "create_pdf";
else
	$mode = "new";

/* **END*of*identify*the*current*site-mode*********************************** */


/* ************************************************************************** *
/*																			  *
/* collecting the data  													  *
/*																			  *
/* ************************************************************************* */
$infobox = createInfoxboxArray($mode);

if ($mode == "new"){
	// collect the current seminars and concerning semesters from the archiv	
	$semestersAR = getSemesters();
}
elseif ($mode == "edit"){
	global $UNI_NAME;
	
	// get the basic data
	if ($_POST['template']){
		$template = $_POST['template'];
	};

	$university = htmlReady($_POST['university']);
	if (empty($university)) $university = $UNI_NAME;
	$fieldofstudy = htmlReady($_POST['fieldofstudy']);
	if (empty($fieldofstudy)) $fieldofstudy = getFieldOfStudy();
	$studentname = htmlReady($_POST['studentname']);
	if (empty($studentname)) $studentname = getStudentname();
	$semesterid = htmlReady($_POST['semesterid']);
	$semester = htmlReady($_POST['semester']);
	if (empty($semester))
		$semester = $semestersAR[$semesterid]["name"];
	$semesternumber = htmlReady($_POST['semesternumber']);

	$basicdata = array(
		"university"	=> $university,
		"fieldofstudy"	=> $fieldofstudy,
		"studentname"	=> $studentname,
		"semester"		=> $semester,
		"semesternumber"=> $semesternumber	
	);
	
	// get the seminars from the db
	if ($semester = $_POST['semester_selected_x']){
		$seminareAR = getSeminare($semesterid,$_POST['onlyseminars']);
	}
	// get the seminars from post
	else{
		$seminare_max = $_POST['seminare_max'];
		$deletenumbers = 0;
		for($i=0;$i+1<=$seminare_max;$i++){
			
			// delete this entry
			if(($_POST['delete'.$i]) &&
			  (!($_POST['add_seminars_x']) && (($_POST['delete'.$i])))){
				$deletenumbers++;
			}
			else{
				// adding this one to the current seminas-array
				$seminarnumber = htmlReady($_POST['seminarnumber'.$i]);
				$tutor = htmlReady($_POST['tutor'.$i]);
				$sws = htmlReady($_POST['sws'.$i]);
				$description = htmlReady($_POST['description'.$i]);
			
				$seminareAR[$i-$deletenumbers] = array(
					"id" 			=> $i,
					"seminarid" 	=> $seminarid,
					"seminarnumber" => $seminarnumber,
					"tutor" 		=> $tutor,
					"sws"			=> $sws,
					"description" 	=> $description 
				);
			}
		}
	}
	
	// this is the new max of seminar_fields
	$seminars_max = $i;

	// add new ones
	if(($_POST['add_seminars_x']) && (!($_POST['delete'.$i]))){
		$numberofnew = $_POST['newseminarfields'];
		for($i=1;$i<=$numberofnew;$i++){
			$seminareAR[$i+$seminare_max] = array("id" => $i+$seminars_max);
		}
	
	}
}
elseif($mode == "pdf_assortment"){
	
	// the last entry
	$seminare_max = $_POST['seminare_max'];
	
	// the basic data
	$university = $_POST['university'];
	$fieldofstudy = $_POST['fieldofstudy'];
	$studentname = $_POST['studentname'];
	$semester = $_POST['semester'];
	$semesternumber = $_POST['semesternumber'];
	$seminars = array (
		"university" => $university,
		"fieldofstudy" => $fieldofstudy,
		"studentname" => $studentname,
		"semester" => $semester,
		"semesternumber" => $semesternumber
	);
	// creating the seminare-arrays cut into ones with the size of 10
	$runner = 10;
	// $j is the current page
	for($j=0;$j<=$seminare_max/10;$j++){
		// $runner notices the last entry 
		if ($j+1>$seminare_max/10)
			$runner = $seminare_max%10;
		// $i is the current page-entry (0-9)
		for($i=0;$i+1<=$runner;$i++){
				// $y is the running nummber from 0 -> last seminar
				$y = $i+($j*10);
				$seminars[$j][$i]["seminarnumber"] = $_POST['seminarnumber'.$y];
				$seminars[$j][$i]["tutor"] = $_POST['tutor'.$y];
				$seminars[$j][$i]["sws"] = $_POST['sws'.$y];
				$seminars[$j][$i]["description"] = $_POST['description'.$y];
		}
	}
	$exemptions = array (10,20,30,40,50,60,70,80,90,100);
	if (in_array($seminare_max,$exemptions))
		$j--;
	$seminars["numberofseminars"] = $seminare_max;
	$seminars["numberofpages"] = $j;
}
elseif($mode == 'create_pdf'){
	global $record_of_study_templates;
	$pdf_file['full_path'] = (($_SERVER['SERVER_PORT'] == 443)? 'https':'http').'://' . $_SERVER['HTTP_HOST'] . $CANONICAL_RELATIVE_PATH_STUDIP . $PATH_EXPORT . '/'.$record_of_study_templates[$template]['template'];
	$pdf_file['filename'] = $record_of_study_templates[$template]['template'];
	$fdfAR = createFdfAR($seminars);
};

/* **END*of*collecting*the*data********************************************* */

/* ************************************************************************** *
/*																			  *
/* displays the site	  													  *
/*																			  *
/* ************************************************************************* */

if ($mode == "new"){
	printSiteTitle();
	printSelectSemester($infobox,$semestersAR);
}
elseif ($mode == "edit"){
	printSiteTitle($basicdata["semester"]);
	
	// display a notice for the user?
	if (sizeof($seminareAR) > 10)
		$notice = "above_limit";
	elseif (sizeof($seminareAR) < 1)
		$notice = "empty";
	
	printRecordOfStudies($infobox, $basicdata, $seminareAR, $notice);
}
elseif ($mode == "pdf_assortment"){
	printSiteTitle($seminars["semester"]);
	printPdfAssortment($infobox, $seminars);
}
elseif ($mode == "create_pdf"){
	printPDF($pdf_file ,$fdfAR);
}

page_close ();
/* **END*of*displays*the*site*********************************************** */


/* ************************************************************************** *
/*																			  *
/* private functions														  *
/*																			  *
/* ************************************************************************* */

/**
 * creates an array with the data to fill the pdf
 *
 * @access  private
 * @param   string $seminars	the seminars
 * @returns array				an array with the data for the pdf
 *
 */
function createFdfAR($seminars){

	$page = $_GET['page']-1;
	$university = $seminars["university"];
	$fieldofstudy = $seminars["fieldofstudy"];
	$studentname = $seminars["studentname"];
	$semester = $seminars["semester"];
	$semesternumber = $seminars["semesternumber"];
	
	$fdfAR = array (
		"university" => $university,
		"fieldofstudy" => $fieldofstudy,
		"studentname" => $studentname,
		"semester" => $semester,
		"semesternumber" => $semesternumber
	);

	for($i=0;$i+1<=10;$i++){
			$fdfAR["seminarnumber.".$i] = $seminars[$page][$i]["seminarnumber"];
			$fdfAR["tutor.".$i] = $seminars[$page][$i]["tutor"];
			$fdfAR["sws.".$i] = $seminars[$page][$i]["sws"];
			$fdfAR["description.".$i] = $seminars[$page][$i]["description"];
	}
	return $fdfAR;
}

/**
 * creates a fdf and sends it to the browser
 *
 * @access  private
 * @param   string $pdf_file	the URL of the pdf-template
 * @param   array $pdf_data		the key and values to send
 *
 */
function printPDF ($pdf_file, $pdf_data) {
	$fdf = "%FDF-1.2\n%‚„œ”\n";
	$fdf .= "1 0 obj \n<< /FDF ";
	$fdf .= "<< /Fields [\n"; 

	foreach ($pdf_data as $key => $val)
		$fdf .= "<< /V ($val)/T ($key) >> \n";

	$fdf .= "]\n/F (".$pdf_file["full_path"].") >>";
	$fdf .= ">>\nendobj\ntrailer\n<<\n";
	$fdf .= "/Root 1 0 R \n\n>>\n";
	$fdf .= "%%EOF";
	
	// Now we display the FDF data which causes Acrobat to start
	header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
	header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	if ($_SERVER['HTTPS'] == "on")
		header("Pragma: public");
	else
		header("Pragma: no-cache");
	header("Cache-Control: private");
	header("Content-Type: application/vnd.fdf");
	header("Content-disposition: inline; filename=\"".$pdf_file["filename"]."\"");
	echo $fdf;
}

/**
 * replaces the semester token
 *
 * @access  public
 * @param   string $semname	a semestertitle (exampl: 'SS 2003')
 * @returns string         	the full semestertitle
 *
 */
function convertSemester($semname){
	global $SEMESTER;

	if ($semname[0].$semname[1] == "WS")	
		return str_replace("WS", _("Wintersemester"),$semname);
	elseif ($semname[0].$semname[1] == "SS")
		return str_replace("SS", _("Sommersemester"),$semname);
	else
		return $semname;
}

/**
 * creates an array which conntains infobox labels
 *
 * @access  private
 * @param   string $mode	the current site-mode
 * @returns array         	an array with infobox labels
 *
 */
function createInfoxboxArray($mode){
	if ($mode == "new"){
		$infobox = array	(	
			array ("kategorie"  => "Information:",
				"eintrag" => array	(	
						array	 (	"icon" => "pictures/ausruf_small.gif",
								"text"  => _("Um eine Druckansicht Ihrer Veranstaltungen zu erstellen, wählen Sie bitte zunächst das entsprechende Semester aus und engen gegebenenfalls ihre Suchabfrage ein.")
								),
						)
			),
		);
	}
	elseif ($mode == "edit") {
		$infobox = array(	
			array  ("kategorie"  => "Information:",
					"eintrag" =>	array (
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => _("Erstellen sie ihre Veranstaltungsübersicht und bearbeiten sie fehlende oder falsche Einträge.")
									),
									)
			),
			array  ("kategorie" => "Aktionen:",
					"eintrag" => array(
						array (	"icon" => "pictures/trash.gif",
								"text"  => _("Löschen sie nicht benötigte Veranstallungen mit Hilfe der Markierungsboxen und/oder fügen sie  beliebig viele neue Veranstallungen hinzu.")
								),
						array (	"icon" => "pictures/icon-disc.gif",
								"text"  => _("Nachdem alle Informationen korrekt angezeigt werden, erstellen sie ihre Veranstaltungsübersicht mit Hilfe des Buttons 'speichern'.")
								),
								)
			),
		);
	}
	elseif ($mode == "pdf_assortment"){
		$infobox = array(	
			array  ("kategorie"  => "Information:",
					"eintrag" =>	array (
							array (	"icon" => "pictures/icon-posting.gif",
									"text"  => _("Über den/die Link(s) können sie sich ihre Veranstaltungsübersicht anzeigen lassen.")
									),
									)
			)
		);
	};
	
	return $infobox;
}

/**
 * sorts an multidim-array
 *
 * @access  private
 * @param   array $array		the array to sort
 * @param   int/string $sort	the index to be sorted
 * @param   string $order		ASC/DESC
 * @param   int $left			the left end index
 * @param   int $right			the rigt end index
 * @returns array				the sorted array
 *
 */
function sortSemestersArray($array,$sort = 0,$order = "ASC",$left = 0,$right = -1){ 
	if ($right == -1){
		$right = count($array);
	}
	
	$left_dump = $left;
	$right_dump = $right;
	$mitte = $array[($left + $right) / 2][$sort];

	if($right_dump > $left_dump){
		do { 
			if ($order == "ASC"){
				while($array[$left_dump][$sort]<$mitte) $left_dump++;
				while($array[$right_dump][$sort]>$mitte) $right_dump--;
			} else {
				while($array[$left_dump][$sort]>$mitte) $left_dump++;
				while($array[$right_dump][$sort]<$mitte) $right_dump--;
			}

			if($left_dump <= $right_dump){
				$tmp = $array[$left_dump];
				$array[$left_dump++] = $array[$right_dump];
				$array[$right_dump--] = $tmp;
			}

		} while($left_dump <= $right_dump);

		$array = sortSemestersArray($array,$sort,$order,$left, $right_dump); 
		$array = sortSemestersArray($array,$sort,$order,$left_dump,$right); 
	}
	return $array; 
}

?>
