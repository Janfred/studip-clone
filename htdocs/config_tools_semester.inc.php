<?
/**
* config_tools_semester.inc.php
* 
* create some constants for semester data
* 
* @access		public
* @package		studip_core
* @modulegroup	config
* @module		config_tools_semester.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// config_tools_semester.inc.php
// hier werden ein paar Semester-Konstanten errechnet
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
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

// fill SEMESTER-Array with values, usable only once!!
function semester_makeSemesterArray() {
    $SEMESTER[1]=array("name"=>"WS 2000/01", "beginn"=>mktime(0,0,0,10,1,2000), "ende"=>mktime(23,59,59,3,31,2001), "vorles_beginn"=>mktime(0,0,0,10,14,2000), "vorles_ende"=>mktime(23,59,59,2,17,2001), "past"=>FALSE); 		# Daten ueber das WS 2000/01
    $SEMESTER[2]=array("name"=>"SS 2001", "beginn"=>mktime(0,0,0,4,1,2001), "ende"=>mktime(23,59,59,9,30,2001), "vorles_beginn"=>mktime(0,0,0,4,16,2001), "vorles_ende"=>mktime(23,59,59,7,15,2001), "past"=>FALSE); 			# Daten ueber das SS 2001
    $SEMESTER[3]=array("name"=>"WS 2001/02", "beginn"=>mktime(0,0,0,10,1,2001), "ende"=>mktime(23,59,59,3,31,2002), "vorles_beginn"=>mktime(0,0,0,10,15,2001), "vorles_ende"=>mktime(23,59,59,2,17,2002), "past"=>FALSE); 		# Daten ueber das WS 2001/02
    $SEMESTER[4]=array("name"=>"SS 2002", "beginn"=>mktime(0,0,0,4,1,2002), "ende"=>mktime(23,59,59,9,30,2002), "vorles_beginn"=>mktime(0,0,0,4,8,2002), "vorles_ende"=>mktime(23,59,59,7,7,2002), "past"=>FALSE); 				# Daten ueber das SS 2002
    $SEMESTER[5]=array("name"=>"WS 2002/03", "beginn"=>mktime(0,0,0,10,1,2002), "ende"=>mktime(23,59,59,3,31,2003), "vorles_beginn"=>mktime(0,0,0,10,14,2002), "vorles_ende"=>mktime(23,59,59,2,14,2003), "past"=>FALSE); 		# Daten ueber das WS 2002/03
    $SEMESTER[6]=array("name"=>"SS 2003", "beginn"=>mktime(0,0,0,4,1,2003), "ende"=>mktime(23,59,59,9,30,2003), "vorles_beginn"=>mktime(0,0,0,4,22,2003), "vorles_ende"=>mktime(23,59,59,7,20,2003), "past"=>FALSE); 			# Daten ueber das SS 2003
    $SEMESTER[7]=array("name"=>"WS 2003/04", "beginn"=>mktime(0,0,0,10,1,2003), "ende"=>mktime(23,59,59,3,31,2004), "vorles_beginn"=>mktime(0,0,0,10,20,2003), "vorles_ende"=>mktime(23,59,59,2,8,2004), "past"=>FALSE); 		# Daten ueber das WS 2003/04
    $SEMESTER[8]=array("name"=>"SS 2004", "beginn"=>mktime(0,0,0,4,1,2004), "ende"=>mktime(23,59,59,9,30,2004), "vorles_beginn"=>mktime(0,0,0,4,5,2004), "vorles_ende"=>mktime(23,59,59,7,11,2004), "past"=>FALSE);
    return $SEMESTER;

}

// Script to insert Array-Entries about each term into the database (see above, usable only once)
function semester_insertIntoSemesterdataFromArray ($SEMESTER) {
    if ($db = new DB_Seminar) {
        //print_r($db);   
    }
    $db->query("use seminar");

    for ($i=1; $i <= sizeof($SEMESTER); $i++) {
        $tmp_id=md5(uniqid("lesukfhsdkuh"));
        if (!$db->query("INSERT INTO semester_data ".
                        "(semester_id,name,start_date,expire_date,lecture_start,lecture_end) ".
                        "VALUES ('".$tmp_id."','".$SEMESTER[$i][name]."','".$SEMESTER[$i][beginn]."','".$SEMESTER[$i][ende]."',".
                        "'".$SEMESTER[$i][vorles_beginn]."','".$SEMESTER[$i][vorles_ende]."')")) {
                            echo "Fehler! Einf&uuml;gen in die DB!!";
                        }

    }
}

// finally get all Semester from db 
function semester_getAllSemester($db) {
    $db->query("use seminar");
    if (!$rs=$db->query("SELECT * FROM semester_data")) {
        echo "ERROR in fetching data";
        return 0;
    }
    $i = 1;
    while ($db->next_record()) {
        $all_semesters[$i]["semester_id"] = $db->f("semester_id");
        $all_semesters[$i]["name"] = $db->f("name");
        $all_semesters[$i]["beginn"] = $db->f("start_date");
        $all_semesters[$i]["ende"] = $db->f("expire_date");
        $all_semesters[$i]["vorles_beginn"] = $db->f("lecture_start");
        $all_semesters[$i]["vorles_ende"] = $db->f("lecture_end");
        $all_semesters[$i]["past"] = FALSE;
        $i++;
    }
    return $all_semesters;
}

function semester_insertNewSemester($db, $name, $startDay, $startMonth, $startYear, $expireDay, $expireMonth, $expireYear, $lectureStartDay, $lectureStartMonth, $lectureStartYear, $lectureExpireDay, $lectureExpireMonth, $lectureExpireYear, $description) {
    $secret = "juppheidiheida";
    $tmp_id=md5(uniqid($secret));
    if (!$db->query("INSERT INTO semester_data ".
                    "(semester_id,name,start_date,expire_date,lecture_start,lecture_end,description) ".
                    "VALUES ('".$tmp_id."','".$name."','".mktime(0,0,0,$startMonth,$startDay,$startYear)."','".mktime(0,0,0,$expireMonth,$expireDay,$expireYear)."',".
                    "'".mktime(0,0,0,$lectureStartMonth,$lectureStartDay,$lectureStartYear)."','".mktime(0,0,0,$lectureExpireMonth,$lectureExpireDay,$lectureExpireYear)."','".$description."')")) {
                        echo "Fehler! Einf&uuml;gen in die DB!";
                        return 0;
                    }
    else return 1;
}


function semester_editExistingSemester($db, $name, $startDay, $startMonth, $startYear, $expireDay, $expireMonth, $expireYear, $lectureStartDay, $lectureStartMonth, $lectureStartYear, $lectureExpireDay, $lectureExpireMonth, $lectureExpireYear, $description, $semester_id) {
   if (!$db->query("UPDATE semester_data SET ".
                    "name='".$name."',start_date='".mktime(0,0,0,$startMonth,$startDay,$startYear)."',".
                    "expire_date='".mktime(0,0,0,$expireMonth,$expireDay,$expireYear)."',".
                    "lecture_start='".mktime(0,0,0,$lectureStartMonth,$lectureStartDay,$lectureStartYear)."',".
                    "lecture_end='".mktime(0,0,0,$lectureExpireMonth,$lectureExpireDay,$lectureExpireYear)."',".
                    "description='".$description."' ".
                    "WHERE semester_id='".$semester_id."'")) {
                        echo "Fehler! Einf&uuml;gen in die DB!";
                        return 0;
                    }
    else return 1;
}


function semester_checkFormField($name, $startDay, $startMonth, $startYear, $expireDay, $expireMonth, $expireYear, $lectureStartDay, $lectureStartMonth, $lectureStartYear, $lectureExpireDay, $lectureExpireMonth, $lectureExpireYear) {
    //echo $startDay.$startMonth.$startYear;
    $errorcount = 0;
    if (strlen($name)==0) {
        $error[$errorcount] .= _("Name");
        $errorcount++;
    }
    if (!(is_numeric($startDay) && is_numeric($startMonth) && is_numeric($startYear) && checkdate($startMonth, $startDay, $startYear))) {
        $error[$errorcount] .= _("Startdatum");
        $errorcount++;
    }
    if (!(is_numeric($expireDay) && is_numeric($expireMonth) && is_numeric($expireYear) && checkdate($expireMonth, $expireDay, $expireYear))) {
        $error[$errorcount] .= _("Enddatum");
        $errorcount++;
    }
    if (!(is_numeric($lectureStartDay) && is_numeric($lectureStartMonth) && is_numeric($lectureStartYear) && checkdate($lectureStartMonth, $lectureStartDay, $lectureStartYear))) {
        $error[$errorcount] .= _("Vorlesungsbeginn");
        $errorcount++;
    }
    if (!(is_numeric($lectureExpireDay) && is_numeric($lectureExpireMonth) && is_numeric($lectureExpireYear) && checkdate($lectureExpireMonth, $lectureExpireDay, $lectureExpireYear))) {
        $error[$errorcount] .= _("Vorlesungsende");
        $errorcount++;
    }
   
    if ($errorcount) {
        $data = _("Fehler! Folgende Felder sind ungültig:&nbsp;");
        for ($i=0; $i<count($error); $i++) {
            $data .= "$error[$i]";
            if ($i!=(count($error)-1)) {
                $data .= ",&nbsp;";
            } else {
                $data .= "&nbsp;";
            }
        }
        $data .= "!";
        return $data;
    }
    //now compare dates
    if ((mktime(0,0,0,$expireMonth,$expireDay,$expireYear)-mktime(0,0,0,$startMonth,$startDay,$startYear))<0) {
        return "Das Datum des Semesterendes muss groesser sein als das Datum des Semesteranfangs";
    }
    if ((mktime(0,0,0,$lectureExpireMonth,$lectureExpireDay,$lectureExpireYear)-mktime(0,0,0,$lectureStartMonth,$lectureStartDay,$lectureStartYear))<0) {
        return "Das Datum des Vorlesungsendes muss groesser sein als das Datum des Vorlesunganfangs";
    }
    if (((mktime(0,0,0,$lectureStartMonth,$lectureStartDay,$lectureStartYear)-mktime(0,0,0,$startMonth,$startDay,$startYear))<0) || ((mktime(0,0,0,$expireMonth,$expireDay,$expireYear)-mktime(0,0,0,$lectureExpireMonth,$lectureExpireDay,$lectureExpireYear))<0)) {
        return "Der Vorlesungszeitraum muss innerhalb des Semesters liegen";
    }
    
    return 1;


}

//list all Semesters
function semester_showSemesters($all_semesters) {
    $showSemesters = "<table align=center bg=\"#ffffff\" width=\"85%\" border=0 cellpadding=2 cellspacing=0><tr><td><br></td></tr>";
    $count = count($all_semesters);
    for ($i=1; $i<=$count; $i++) {
        $showSemesters .= semester_showSemester($all_semesters[$i], $i);
    }
    $showSemester.= "</table><br><br></td></tr>";
    return $showSemesters;
}

function semester_makeNewSemesterButton($link) {
    $button = "<tr><td class=\"blank\" colspan=2><b><a href=\"".$link."?new=1\">&nbsp;"._("Neues Semester anlegen")."</a><b><br><br></td></tr>";
    return $button;
}

//make new Semester-Entry-Form
function semester_showNewSemesterForm($link, $cssSw, $name="", $startDay="", $startMonth="", $startYear="", $expireDay="", $expireMonth="", $expireYear="", $lectureStartDay="", $lectureStartMonth="", $lectureStartYear="", $lectureExpireDay="", $lectureExpireMonth="", $lectureExpireYear="", $description="", $modus="", $semester_id="") {
    $data =     "<form method=\"POST\" name=\"newSemester\" action=\"".$link."\">";
	$data .=    "<tr><td class=\"";
    $cssSw->switchClass(); 
    $data .=    "".$cssSw->getClass()."\">"._("Name des Semesters:")."</td><td class=".$cssSw->getClass()."><input type=\"text\" name=\"Name\" value=\"".$name."\"size=60 maxlength=254></td></tr>";
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">"._("Beschreibung:")."</td><td class=\"".$cssSw->getClass()."\"><textarea cols=50 ROWS=4 name=\"Description\">".$description."</textarea></td></tr>";
    $cssSw->switchClass(); 
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">"._("Beginn des Semesters:")."</td><td class=\"".$cssSw->getClass()."\"><table cellspacing=0 cellpadding=0 border=0><tr><td width=\"30%\"><input type=\"text\" name=\"StartDay\" value=\"".$startDay."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"StartMonth\" value=\"".$startMonth."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"StartYear\" value=\"".$startYear."\" size=\"4\" maxlength=\"4\">";
    $data .=    "</td></tr></table></td></tr>";
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">"._("Ende des Semesters:")."</td><td class=\"".$cssSw->getClass()."\"><table cellspacing=0 cellpadding=0 border=0><tr><td width=\"30%\"><input type=\"text\" name=\"ExpireDay\" value=\"".$expireDay."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"ExpireMonth\" value=\"".$expireMonth."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"ExpireYear\" value=\"".$expireYear."\" size=\"4\" maxlength=\"4\">";
    $data .=    "</td></tr></table></td></tr>";
    $cssSw->switchClass();
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">"._("Vorlesungsbeginn:")."</td><td class=\"".$cssSw->getClass()."\"><table cellspacing=0 cellpadding=0 border=0><tr><td width=\"30%\"><input type=\"text\" name=\"LectureStartDay\" value=\"".$lectureStartDay."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"LectureStartMonth\" value=\"".$lectureStartMonth."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"LectureStartYear\" value=\"".$lectureStartYear."\" size=\"4\" maxlength=\"4\">";
    $data .=    "</td></tr></table></td></tr>";
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">"._("Vorlesungsende:")."</td><td class=\"".$cssSw->getClass()."\"><table cellspacing=0 cellpadding=0 border=0><tr><td width=\"30%\"><input type=\"text\" name=\"LectureExpireDay\" value=\"".$lectureExpireDay."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"LectureExpireMonth\" value=\"".$lectureExpireMonth."\" size=\"2\" maxlength=\"2\">";
    $data .=    ".</td>";
    $data .=    "<td width=\"30%\" class=\"".$cssSw->getClass()."\"><input type=\"text\" name=\"LectureExpireYear\" value=\"".$lectureExpireYear."\" size=\"4\" maxlength=\"4\">";
    $data .=    "</td></tr></table></td></tr>";
    $cssSw->switchClass();
    $data .=    "<tr><td class=\"".$cssSw->getClass()."\">";
    $data .=    "</td><td class=\"".$cssSw->getClass()."\"><br><br>";
    if ($modus=="change") {
        $data.= "<input type=\"IMAGE\" name=\"edit\" value=\""._("Bearbeiten")."\"".makeButton("bearbeiten", "src").">&nbsp;&nbsp;";
    } else {    
    $data .=    "<input type=\"hidden\" name=\"newEntry\" value=\"1\">";
    $data .=    "<input type=\"IMAGE\" name=\"create\" value=\""._("Anlegen")."\"".makeButton("anlegen", "src").">&nbsp;&nbsp;";
    }
    $data .=    "<input type=\"hidden\" name=\"semester_id\" value=\"".$semester_id."\">";
    $data .=    "<a href=\"admin_semester.php\"><img ".makeButton("abbrechen", "src")." border=0></a>";
    //$data .=    "<input type=\"IMAGE\" name=\"cancel\" value=\""._("abbrechen")."\"".makeButton("abbrechen", "src").">";
    $data .=    "</td></tr>";
    $data .=    "</form>";
    return $data;

}

function semester_showSemesterHeader(){
    $data =     "<tr><td class=\"blank\" colspan=2>";
    $data .=    "<table align=center bg=\"#ffffff\" width=\"85%\" border=0 cellpadding=2 cellspacing=0>";
    $data .=    "<tr valign=top align=middle>";
    $data .=    "<th align=left width=\"20%\">"._("Name des Semesters")."</th>";
    $data .=    "<th align=left width=\"10%\">"._("Beginn")."</th>";
    $data .=    "<th align=left width=\"10%\">"._("Ende")."</th>";
    $data .=    "<th align=left width=\"20%\">"._("Vorlesungsbeginn")."</th>";
    $data .=    "<th align=left width=\"25%\">"._("Vorlesungsende")."</th>";
    $data .=    "</tr></table>";
    return $data;
}

function semester_getSemesterDataFromId($db, $semester_id) {
    if (!$db->query( "SELECT * FROM semester_data ".
                    "WHERE semester_id=\"".$semester_id."\"")) {
        echo "Error! Fetching data from database!";
        return 0;        
    }
    if (!$db->next_record()) {
        echo "Error! Fetching data from database";
        return 0;
    } else {
    $semesterData["semester_id"]=$db->f("semester_id");
    $semesterData["name"]=$db->f("name");
    $semesterData["description"]=$db->f("description");
    $semesterData["startDay"]=date("j",$db->f("start_date"));
    $semesterData["startMonth"]=date("n",$db->f("start_date"));
    $semesterData["startYear"]=date("Y",$db->f("start_date"));
    $semesterData["expireDay"]=date("j",$db->f("expire_date"));
    $semesterData["expireMonth"]=date("n",$db->f("expire_date"));
    $semesterData["expireYear"]=date("Y",$db->f("expire_date"));
    $semesterData["lectureStartDay"]=date("j",$db->f("lecture_start"));
    $semesterData["lectureStartMonth"]=date("n",$db->f("lecture_start"));
    $semesterData["lectureStartYear"]=date("Y",$db->f("lecture_start"));
    $semesterData["lectureExpireDay"]=date("j",$db->f("lecture_end"));
    $semesterData["lectureExpireMonth"]=date("n",$db->f("lecture_end"));
    $semesterData["lectureExpireYear"]=date("Y",$db->f("lecture_end"));
    $semesterData["semester_token"]=$db->f("semester_token");
    return $semesterData;
    }
}

//list one Semester
function semester_showSemester($semester, $i) {
   if (($i % 2) == 0) {
        $style = "steel1";
   } else {
        $style = "steelgraulight";
   }
   $row =   "<tr>";
   $row .=  "<td class=".$style." width=\"20%\">".$semester[name]."</td>";
   $row .=  "<td class=".$style." width=\"10%\">".date("d.m.Y", $semester[beginn])."</td>";
   $row .=  "<td class=".$style." width=\"10%\">".date("d.m.Y", $semester[ende])."</td>";
   $row .=  "<td class=".$style." width=\"20%\">".date("d.m.Y", $semester[vorles_beginn])."</td>";
   $row .=  "<td class=".$style." width=\"10%\">".date("d.m.Y", $semester[vorles_ende])."</td>";
   $row .=  "<td align=\"RIGHT\" class=".$style." width=\"15%\"><a href=\"admin_semester.php?change=1&semester_id=".$semester[semester_id]."\"><img src=\"pictures/buttons/bearbeiten-button.gif\" border=0></a></td>";
   $row .=  "</tr>";
   return $row;
}

//Checken ob es sich um vergangene Semester handelt + checken, welches das aktuelle Semester ist und Daten daraus verwenden (Alte Version)

$i=1;
for ($i; $i <= sizeof($SEMESTER); $i++)
	{ 
	if ($SEMESTER[$i]["ende"] < time()) $SEMESTER[$i]["past"]=TRUE;
	if (($SEMESTER[$i]["beginn"] < time()) && ($SEMESTER[$i]["ende"] >time()))
		{
		$VORLES_BEGINN=$SEMESTER[$i]["vorles_beginn"];
		$VORLES_ENDE=$SEMESTER[$i]["vorles_ende"];
		$SEM_BEGINN=$SEMESTER[$i]["beginn"];
		$SEM_ENDE=$SEMESTER[$i]["ende"];
		$SEM_NAME=$SEMESTER[$i]["name"];
		$SEM_ID=$i;
		if ($i<sizeof ($SEMESTER))
			{
			$VORLES_BEGINN_NEXT=$SEMESTER[$i+1]["vorles_beginn"];
			$VORLES_ENDE_NEXT=$SEMESTER[$i+1]["vorles_ende"];
			$SEM_BEGINN_NEXT=$SEMESTER[$i+1]["beginn"];
			$SEM_ENDE_NEXT=$SEMESTER[$i+1]["ende"];
			$SEM_NAME_NEXT=$SEMESTER[$i+1]["name"];			
			$SEM_ID_NEXT=$i+1;
			}
		}
	}

// newer Version; gets the data from the database
// still undocumented, so don't use..

/*
$db=new DB_Seminar;
$SEMESTER = semester_getAllSemester($db);

$i=1;
for ($i; $i <= sizeof($SEMESTER); $i++)
	{ 
	if ($SEMESTER[$i]["ende"] < time()) $SEMESTER[$i]["past"]=TRUE;
	if (($SEMESTER[$i]["beginn"] < time()) && ($SEMESTER[$i]["ende"] >time()))
		{
		$VORLES_BEGINN=$SEMESTER[$i]["vorles_beginn"];
		$VORLES_ENDE=$SEMESTER[$i]["vorles_ende"];
		$SEM_BEGINN=$SEMESTER[$i]["beginn"];
		$SEM_ENDE=$SEMESTER[$i]["ende"];
		$SEM_NAME=$SEMESTER[$i]["name"];
		$SEM_ID=$i;
		if ($i<sizeof ($SEMESTER))
			{
			$VORLES_BEGINN_NEXT=$SEMESTER[$i+1]["vorles_beginn"];
			$VORLES_ENDE_NEXT=$SEMESTER[$i+1]["vorles_ende"];
			$SEM_BEGINN_NEXT=$SEMESTER[$i+1]["beginn"];
			$SEM_ENDE_NEXT=$SEMESTER[$i+1]["ende"];
			$SEM_NAME_NEXT=$SEMESTER[$i+1]["name"];			
			$SEM_ID_NEXT=$i+1;
			}
		}
	}
//print_r($SEMESTER);
*/
    
?>
