<?
/* vim: noexpandtab */
/*
admin_seminare1.php - Seminar-Verwaltung von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("tutor");

$hash_secret = "dslkjjhetbjs";

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once('lib/dates.inc.php'); // Funktionen zum Loeschen von Terminen
require_once('lib/datei.inc.php'); // Funktionen zum Loeschen von Dokumenten
require_once 'lib/functions.php';
require_once('lib/visual.inc.php');
require_once('lib/admission.inc.php');
require_once('lib/statusgruppe.inc.php');	//Funktionen der Statusgruppen
require_once('lib/classes/StudipSemTreeSearch.class.php');
require_once('lib/classes/DataFieldEntry.class.php');

$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenGrunddaten";

//Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE.=_("Verwaltung der Grunddaten");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//get ID from a open Seminar
if ($SessSemName[1])
	$s_id=$SessSemName[1];

//Change header_line if open object
$header_line = getHeaderLine($s_id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   // Output of Stud.IP head
echo $links;

?>

<SCRIPT language="JavaScript">
<!--

function checkname(){
 var checked = true;
 if (document.details.Name.value.length<3) {
    alert("<?=_("Bitte geben Sie einen Namen für die Veranstaltung ein!")?>");
 		document.details.Name.focus();
    checked = false;
    }
 return checked;
}

function checkdata(command){
 var checked = true;
 if (!checkname())
 	checked = false;

 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.submit();
 }
 return checked;
}


function checkdata_without_bereich(command){
 var checked = true;
 if (!checkname())
 	checked = false;
 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.submit();
 }
 return checked;
}

//-->
</SCRIPT>

<?

## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$db5 = new DB_Seminar;
$db6 = new DB_Seminar;

$cssSw = new cssClassSwitcher;

$user_id = $auth->auth["uid"];
$msg = "";

//get ID, if a Veranstaltung is open
if ($SessSemName[1])
	$s_id=$SessSemName[1];

$st_search = new StudipSemTreeSearch($s_id,"details");
#$DataFields = new DataFields($s_id);


$label_lock_text = '<img src="'.$GLOBALS['ASSETS_URL'].'images/info.gif" align="middle"';
$label_lock_text .= tooltip(_("Sie dürfen nicht alle Daten dieser Veranstaltung verändern. Diese Sperrung ist von einem/einer AdministratorIn vorgenommen worden."),TRUE,TRUE).">";


function auth_check() {
	global $perm,$s_id;
	return $perm->have_studip_perm("tutor",$s_id);
}


if (isset($s_id) && !$perm->have_perm("admin") && $SEMINAR_LOCK_ENABLE) {
	$lock_status = "attributes";
	$lockdata = $lock_rules->getSemLockRule($s_id);
  $lock_text = $lock_rules->getLockText();
}

function get_Institutes($id) {
	$stmt = DBManager::get()->prepare(
	  "SELECT sem_tree_id FROM seminar_sem_tree s ".
	  "WHERE s.seminar_id=?");
	$stmt->execute(array($id));

	$inst_tree = array();
	while ($row = $stmt->fetch()) {
		$inst_tree[] = array("tree" => get_sem_tree($row["sem_tree_id"]),
		                     "id"   => $row["sem_tree_id"]);
	}
	return $inst_tree;
}


function get_sem_tree($id, $path=array()) {
	$db = DBManager::get();
	$stmt = $db->prepare("SELECT parent_id, name, studip_object_id ".
	                     "FROM sem_tree WHERE sem_tree_id = ?");
	$stmt->execute(array($id));
	$row = $stmt->fetch();

	if ($row["parent_id"] == "root" && $row["name"] == "") {
		$stmt2 = $db->prepare("SELECT Name FROM Institute WHERE Institut_id=?");
		$stmt2->execute(array("studip_object_id"));
		$row2 = $stmt2->fetch();
		$path[] = $row2["Name"];
	}

	if ($row["name"] != "") {
		$path[] = $row["name"];
	}

	if ($row["parent_id"] != "root" && $row["parent_id"] != '') {
		return get_sem_tree($row["parent_id"], $path);
	}

	krsort($path);
	return $path;
}

function format_sem_tree($array) {
	foreach($array as $key => $val) {
		$string .= $val."&nbsp;>&nbsp;";
	}
	$string = substr($string, 0, strlen($string) - 8);
	return $string;
}

function get_dozent_data($s_id, $_fullname_sql, $locked = false, $lock_text = '')
{
	global $PHP_SELF;
	$db = new DB_Seminar();
	$db->query("SELECT ". $_fullname_sql['full_rev'] .
             " AS fullname, seminar_user.user_id, seminar_user.position," .
                " status, username" .
             " FROM seminar_user " .
                " LEFT JOIN auth_user_md5 USING(user_id)" .
                " LEFT JOIN user_info USING(user_id)" .
             " WHERE Seminar_id = '$s_id'" .
             " AND Status = 'dozent'" .
             " ORDER BY seminar_user.position, Nachname");

	if ($db->nf())
  {
		$out[] = "<table>";
		$i = 0;
		while ($db->next_record())
    {
			$out[] = "<tr>";

      if (!$locked)
      {
			  $out[]= "<td>";
			$href = "?delete_doz=".$db->f("username"). "&s_id=".$s_id."#anker";
			$img_src = "images/trash.gif";
  			$out[] = "<a href='{$PHP_SELF}{$href}'>";
  			$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  			$out[] = "</a>";
  			$out[]= "</td>";

			if ($db->nf() > 1)
        {
  				// move up (if not first)
  				$out[] = "<td>";
  				if ($i > 0)
        {
						$href = "?moveup_doz=".$db->f("username"). "&s_id=".$s_id."&".time()."#anker";
            $img_src = "images/move_up.gif";
  						$out[] = "<a href='{$PHP_SELF}{$href}'>";
  						$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  						$out[] = "</a>";
        }
          $out[] = "</td>";
  				// move down (if not last)
  				$out[] = "<td>";
				if ($i < $db->nf() - 1)
          {
						$href = "?movedown_doz=".$db->f("username"). "&s_id=".$s_id."&".time()."#anker";
						$img_src = "images/move_down.gif";
  						$out[] = "<a href='{$PHP_SELF}{$href}'>";
  						$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  						$out[] = "</a>";
				}
          $out[] = "</td>";
  			}
  		}
			$out[] = "<td>";
			$out[] = "<font size=\"-1\"><b>".htmlReady($db->f("fullname")).
               " (". $db->f("username") . ")</b></font>";

			$out[] = "</td>";
			if (!$locked)
			{
			if ($GLOBALS['DENOTATIONS'])
        {
  				$out[] = "<td>";
  				$out[] = "<select name=\"\" size=1>";
  				foreach ($GLOBALS['DENOTATIONS'] as $denot) {
  					$out[] = "<option>$denot";
  				}
  				$out[] = "</select>";
  				$out[] = "</td>";
  			}
      }
			$out[] = "</tr>";
			$i++;
    }

    if ($locked)
    {
      $out[] = "<tr>";
      $out[] = "<td>";
      $out[] = "{$lock_text}";
      $out[] = "</td>";
      $out[] = "</tr>";
    }

		$out[] = "</table>";
  }
	else
  {   // FIXME: How to detemine workgroup_mode.
      // Case not possible, at least one project leader is needed.
      $workgroup_mode = 1;
		$name = $workgroup_mode ? _("LeiterInnen") : _("DozentInnen");
		$out[] = "<font size=\"-1\">&nbsp;  ";
		$out[] = sprintf(_("Keine %s gew&auml;hlt."), $name);
		$out[] = "</font><br >";

    if ($locked)
    {
      $out[] = "{$lock_text}<br/>";
    }

	}
	return implode("\n", $out);
}

function get_tutor_data($s_id, $_fullname_sql, $locked = false, $lock_text = '')
{
	global $PHP_SELF;
	$db = new DB_Seminar();
	$db->query("SELECT ". $_fullname_sql['full_rev'] .
             " AS fullname, seminar_user.user_id, seminar_user.position," .
                " status, username" .
             " FROM seminar_user " .
                " LEFT JOIN auth_user_md5 USING(user_id)" .
                " LEFT JOIN user_info USING(user_id)" .
             " WHERE Seminar_id = '$s_id'" .
             " AND Status = 'tutor'" .
             " ORDER BY seminar_user.position, Nachname");

	if ($db->nf())
  {
		$out[] = "<table>";
		$i = 0;
		while ($db->next_record())
    {
			$out[] = "<tr>";
			if (!$locked)
			{
  			$out[]= "<td>";
			$href =   "?delete_tut=".$db->f("username"). "&s_id=".$s_id."#anker";
			$img_src = "images/trash.gif";

  			$out[] = "<a href='{$PHP_SELF}{$href}'>";
  			$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  			$out[] = "</a>";

  			$out[]= "</td>";

			if ($db->nf() > 1)
        {
  				// move up (if not first)
  				$out[] = "<td>";
  				if ($i > 0)
        {
						$href = "?moveup_tut=".$db->f("username"). "&s_id=".$s_id."&".time()."#anker";
						$img_src = "images/move_up.gif";

  						$out[] = "<a href='{$PHP_SELF}{$href}'>";
  						$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  						$out[] = "</a>";
        }
          $out[] = "</td>";
  				// move down (if not last)
  				$out[] = "<td>";
				if ($i < $db->nf() - 1)
          {
					$href = "?movedown_tut=".$db->f("username"). "&s_id=".$s_id."&".time()."#anker";
					$img_src = "images/move_down.gif";

  					$out[] = "<a href='{$PHP_SELF}{$href}'>";
  					$out[] = "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
  					$out[] = "</a>";
				}
          $out[] = "</td>";
  			}
  		}
			$out[] = "<td>";
			$out[] = "<font size=\"-1\"><b>".htmlReady($db->f("fullname")).
               " (". $db->f("username") . ")</b></font>";

			$out[] = "</td>";
			$out[] = "</tr>";
			$i++;
    }

    if ($locked)
    {
      $out[] = "<tr>";
      $out[] = "<td>";
      $out[] = "{$lock_text}";
      $out[] = "</td>";
      $out[] = "</tr>";
    }

		$out[] = "</table>";
  }
	else
  {   // FIXME: How to detemine workgroup_mode.
      // Case not possible, at least one project leader is needed.
      $workgroup_mode = 1;
		$name = $workgroup_mode ? _("Mitglieder") : _("TutorInnen");
		$out[] = "<font size=\"-1\">&nbsp;  ";
		$out[] = sprintf(_("Keine %s gew&auml;hlt."), $name);
		$out[] = "</font><br >";

    if ($locked)
    {
      $out[] = "{$lock_text}<br/>";
    }

	}
	return implode("\n", $out);
}

// move Dozenten
if ($moveup_doz)
{
   if ($perm->have_studip_perm("dozent",$s_id))
   {
      move_dozent($moveup_doz, $s_id, "up");

      $user_moved = TRUE;
   }
	else
   {
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";

   }
}

if ($movedown_doz)
{
	if ($perm->have_studip_perm("dozent",$s_id))
   {
      move_dozent($movedown_doz, $s_id, "down");

      $user_moved = TRUE;
	}
   else
   {
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";
   }
}

function move_dozent ($username, $s_id, $direction)
{
	$user_id = get_userid($username);

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query("SELECT position FROM seminar_user" .
                   " WHERE Seminar_id = '$s_id'" .
                   " AND user_id ='$user_id' ");

	if ($db->next_record())
   {
		$position = $db->f('position');
		$position_alt = $position;
		if ($direction == "up") $position--;
		if ($direction == "down") $position++;

		$db->query( "UPDATE seminar_user" .
                  " SET position =  '$position_alt'" .
                  " WHERE Seminar_id = '$s_id'" .
                  "  AND status = 'dozent' " .
                  "  AND position = '$position'");

		$db2->query( "UPDATE seminar_user" .
                  " SET position =  '$position'" .
                  " WHERE Seminar_id = '$s_id'" .
                  " AND status = 'dozent' " .
                  " AND user_id = '$user_id'");

		if ($db->affected_rows() && $db2->affected_rows()) return true;
	}
	return false;
}
// move Tutoren
if ($moveup_tut)
{
   if ($perm->have_studip_perm("dozent",$s_id))
   {
      move_tutor($moveup_tut, $s_id, "up");

      $user_moved = TRUE;
   }
	else
   {
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";

   }
}

if ($movedown_tut)
{
	if ($perm->have_studip_perm("dozent",$s_id))
   {
      move_tutor($movedown_tut, $s_id, "down");

      $user_moved = TRUE;
	}
   else
   {
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";
   }
}

function move_tutor ($username, $s_id, $direction)
{
	$user_id = get_userid($username);

	$db=new DB_Seminar;
	$db->query("SELECT position FROM seminar_user" .
                   " WHERE Seminar_id = '$s_id'" .
                   " AND user_id ='$user_id' ");

	if ($db->next_record())
   {
		if ($direction == "up")
      {
			$position = $db->f("position") - 1;
      }
		if ($direction == "down")
      {
			$position = $db->f("position") + 1;
		}
      $position_alt = $db->f("position");

		$db->query( "UPDATE seminar_user" .
                  " SET position =  '$position_alt'" .
                  " WHERE Seminar_id = '$s_id'" .
                  "  AND status = 'tutor' " .
                  "  AND position = '$position'");

		if (!$db->affected_rows())
      {
         return false;
      }

		$db->query( "UPDATE seminar_user" .
                  " SET position =  '$position'" .
                  " WHERE Seminar_id = '$s_id'" .
                  " AND status = 'tutor' " .
                  " AND user_id = '$user_id'");
		if (!$db->affected_rows())
      {
         return false;
      }
      return true;
	}
}

//delete Tutoren/Dozenten
if ($delete_doz) {
	if ($perm->have_studip_perm("dozent",$s_id)) {
		$db2->query ("SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND status = 'dozent' ");
		if (($auth->auth["perm"] == "dozent") && ($delete_doz == get_username($user_id)))
			$msg .= "error§" . _("Sie d&uuml;rfen sich nicht selbst aus der Veranstaltung austragen.") . "§";
		elseif ($db2->nf() <2)
			$msg .= sprintf ("error§" . _("Die Veranstaltung muss wenigstens <b>einen</b> %s eingetragen haben! Tragen Sie zun&auml;chst einen anderen ein, um diesen zu l&ouml;schen.") . "§", ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["workgroup_mode"]) ? _("Leiter") : _("Dozenten"));
		else {

         $db2->query ( "SELECT position " .
                       " FROM seminar_user " .
                       " WHERE Seminar_id = '$s_id' " .
                       " AND user_id = '".get_userid($delete_doz)."' ");

         $db2->next_record();
         $position = $db2->f("position");

			$db2->query ("DELETE FROM seminar_user" .
                      " WHERE Seminar_id = '$s_id'" .
                      " AND user_id ='".get_userid($delete_doz)."' ");

			if ($db2->affected_rows())
         {
            re_sort_dozenten($s_id, $position);

				$msg .= "msg§" . sprintf(_("Der Nutzer <b>%s</b> wurde aus der Veranstaltung gel&ouml;scht."), get_fullname_from_uname($delete_doz,'full',true)) . "§";
				$user_deleted=TRUE;
				RemovePersonStatusgruppeComplete ($delete_doz, $s_id);
			}
		}
	} else
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";
}

if ($delete_tut) {
	if ($perm->have_studip_perm("dozent",$s_id)) {

         $db2->query ( "SELECT position " .
                       " FROM seminar_user " .
                       " WHERE Seminar_id = '$s_id' " .
                       " AND user_id = '".get_userid($delete_tut)."' ");

         $db2->next_record();
         $position = $db2->f("position");

		   $db2->query ("DELETE FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id ='".get_userid($delete_tut)."' ");
		if ($db2->affected_rows()) {

         re_sort_tutoren($s_id, $position);

			$msg .= "msg§" . sprintf(_("Der Nutzer <b>%s</b> wurde aus der Veranstaltung gel&ouml;scht."), get_fullname_from_uname($delete_tut,'full',true)) . "§";
			$user_deleted=TRUE;
			RemovePersonStatusgruppeComplete ($delete_tut, $s_id);
		}
	} else
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";
}

// Change Seminar parameters
if ($s_send) {
	$run = TRUE;

	if (!auth_check()) {
		$msg .= "error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.") . "§";
		$run = FALSE;
	}

	//Load necessary data from the saved lecture
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '$s_id' ");
	$db->next_record();

	// Do we have all necessary data?
	if (empty($Name) && !$lockdata[$lock_status]['Name']) {
		$msg .= "error§" . _("Bitte geben Sie den <b>Namen der Veranstaltung</b> ein!") . "§";
		$run = FALSE;
	}

	if (empty($Institut)) {
		$msg .= "error§" . _("Bitte geben Sie eine <b>Heimat-Einrichtung</b> an!") . "§";
		$run = FALSE;
	}

	if ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["bereiche"]) {

	if (!$lockdata[$lock_status]['sem_tree']
			|| $perm->have_perm('admin'))
	{
    if (empty($_REQUEST['details_chooser'])) {
      $msg .= "error§" . _("Bitte geben Sie wenigstens einen <b>Studienbereich</b> an!") . "§";
      $run = FALSE;
		} else {
			for ($i = 0; $i < count($_REQUEST['details_chooser']);++$i) {
				if ($_REQUEST['details_chooser'][$i] != '0') $dochnoch = "ja";
			}
			if ($dochnoch != "ja") {
				$msg .= "error§" . _("Sie haben nur einen ung&uuml;ltigen Studienbereich ausgew&auml;hlt. Bitte geben Sie wenigstens einen <b>Studienbereich</b> an!") . "§";
				$run = FALSE;
			}
		}
	}

	}


	//we have to select at least one Dozent!
	if (($perm->have_perm("admin")) && (!$add_doz)) {
		$db2->query ("SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND status = 'dozent' ");
		if ($db2->nf() == 0) {
			$msg .= sprintf ("error§" . _("Bitte geben Sie wenigstens <b>einen</b> %s an.") . "§", ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["workgroup_mode"]) ? "Leiter" : "Dozenten");
			$run = FALSE;
		}
	}

	//Checks for admission turnout (only important if an admission is set)
	if ($db->f("admission_type") && !$lockdata[$lock_status]['admission_turnout']) {
		if ($turnout < 1) {
			$msg .= "error§" . _("Diese Veranstaltung ist teilnahmebeschr&auml;nkt. Daher m&uuml;ssen Sie wenigstens einen Teilnehmenden zulassen!") . "§";
			$run=FALSE;
		}
		if (($run) && ($turnout < $db->f("admission_turnout")))
			$msg .= "info§" . _("Diese Veranstaltung ist teilnahmebeschr&auml;nkt. Wenn Sie die Anzahl der Teilnehmenden verringern, m&uuml;ssen Sie evtl. NutzerInnen, die bereits einen Platz in der Veranstaltung erhalten haben, manuell entfernen!") . "§";
		if ($turnout > $db->f("admission_turnout"))
			$do_update_admission=TRUE;
	}

	if ($run) { // alle Angaben ok
    ## Create timestamps
    $start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
    $duration = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr)-$start_time;

    if ($Schreibzugriff < $Lesezugriff)
			$Schreibzugriff = $Lesezugriff;			// hier wusste ein Dozent nicht, was er tat

    ## Update Seminar information.

    $update_data = array();

    if (!$lockdata[$lock_status]['VeranstaltungsNummer'])
    {
    	$update_data['Veranstaltungsnummer'] = $VeranstaltungsNummer;
    }

    if (!$lockdata[$lock_status]['Institut_id'])
    {
    	if ($perm->have_studip_perm("dozent",$s_id))
    	{
    		$update_data['Institut_id'] = $Institut;
    	}
    }

    if (!$lockdata[$lock_status]['Name'])
    {
    	$update_data['Name'] = $Name;
    }

    if (!$lockdata[$lock_status]['Untertitel'])
    {
    	$update_data['Untertitel'] = $Untertitel;
    }

    if (!$lockdata[$lock_status]['Beschreibung'])
    {
    	$update_data['Beschreibung'] = $Beschreibung;
    }

    if (!$lockdata[$lock_status]['Sonstiges'])
    {
    	$update_data['Sonstiges'] = $Sonstiges;
    }

    if (!$lockdata[$lock_status]['art'])
    {
    	$update_data['art'] = $art;
    }

    if (!$lockdata[$lock_status]['teilnehmer'])
    {
    	$update_data['teilnehmer'] = $teilnehmer;
    }

    if (!$lockdata[$lock_status]['voraussetzungen'])
    {
    	$update_data['vorrausetzungen'] = $vorrausetzungen;
    }

    if (!$lockdata[$lock_status]['lernorga'])
    {
    	$update_data['lernorga'] = $lernorga;
    }

    if (!$lockdata[$lock_status]['ects'])
    {
    	$update_data['ects'] = $ects;
    }

    if (!$lockdata[$lock_status]['admission_turnout'])
    {
    	$update_data['admission_turnout'] = $turnout;
    }

     if (!$lockdata[$lock_status]['Ort'])
    {
    	$update_data['Ort'] = $room;
    }

    $query = null;

    $updated_seminar = false;

    if (sizeof($update_data) > 0)
    {
   		$count = 0;

   		$query = "UPDATE seminare SET ";

   		foreach($update_data as $key => $value)
   		{
   			if ($count > 0)
   			{
   				$query .= ", ";
   			}

   			$query .= " $key='$value' ";


   			$count ++;
   		}

		$query .= "WHERE Seminar_id='$s_id'";

   		$db->query($query);

   		$updated_seminar = true;
    }

/*
    $query = "UPDATE seminare SET Veranstaltungsnummer='$VeranstaltungsNummer', ";
    if ($perm->have_studip_perm("dozent",$s_id))
			$query .="Institut_id='$Institut', ";
    $query .= "Name='$Name', Untertitel='$Untertitel',
			status='$Status', Beschreibung='$Beschreibung',
			Sonstiges='$Sonstiges', art='$art', teilnehmer='$teilnehmer',
			vorrausetzungen='$vorrausetzungen', lernorga='$lernorga',
			leistungsnachweis='$leistungsnachweis', ects='$ects', admission_turnout='$turnout', Ort='$room' ";
*/

    if ($do_update_admission)
    	update_admission($s_id);

    if ($updated_seminar
    		&& $db->affected_rows()) {
			$msg .= "msg§" . _("Die Grund-Daten der Veranstaltung wurden ver&auml;ndert.") . "§";
			$db->query("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id='$s_id'");
		}

		//Starttime des Seminar ermitteln
		$query = "SELECT start_time FROM seminare WHERE Seminar_id = '$s_id' ";
		$db->query($query);
		$db->next_record();
		$temp_admin_seminare_start_time=$db->f("start_time");

		//a Dozent was added

		if ($add_doz_x
				&& $perm->have_studip_perm("dozent",$s_id)
				&& !$lockdata[$lock_status]['dozent']) {
			$add_doz_id=get_userid($add_doz);
			$group=select_group($temp_admin_seminare_start_time);
            $next_pos = get_next_position("dozent",$s_id);
			$query = "SELECT user_id, status FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$add_doz_id'";
			$db2->query($query);
			if ($db2->next_record()){ //User schon da
				if($db2->f('status') != 'dozent'){
					$query = "UPDATE seminar_user SET status = 'dozent', position='$next_pos' WHERE Seminar_id = '$s_id' AND user_id = '$add_doz_id'";
				} else {
					$query = '';
				}
			} else {						//User noch nicht da
				$query = "INSERT INTO seminar_user SET Seminar_id = '$s_id', user_id = '$add_doz_id', status = 'dozent', gruppe = '$group', admission_studiengang_id = '', mkdate = '".time()."', position = '$next_pos'";
			}
			if($query){
				$db3->query($query);					//Dozent eintragen
				$user_added = TRUE;
			}
		}

		//a Tutor was added
		if ($add_tut_x
				&& $perm->have_studip_perm("dozent",$s_id)
				&& !$lockdata[$lock_status]['tutor']) {
			$add_tut_id=get_userid($add_tut);
			$group=select_group($temp_admin_seminare_start_time);
			$query = "SELECT user_id, status FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$add_tut_id'";
			$db2->query($query);
			$next_pos = get_next_position("tutor", $s_id);
			if ($db2->next_record()) {
				if ($db2->f("status") == "dozent"){		// User schon da aber Dozent, also nix tun! (Selbstdegradierung ist zwar schoen, wollen wir aber nicht, sonst ist der Dozent futsch)
					$query = '';
				} else {							//User schon da aber was anderes (unterhalb Tutor), also Hochstufen.
					$query = "UPDATE seminar_user SET status = 'tutor', position='$next_pos', visible='yes' WHERE Seminar_id = '$s_id' AND user_id = '$add_tut_id'";
				}
			} else {								//User noch nicht da
				$query = "INSERT INTO seminar_user SET Seminar_id = '$s_id', user_id = '$add_tut_id', status = 'tutor', gruppe = '$group', mkdate = '".time()."', position='$next_pos', visible='yes'";
			}
			if ($query) {
				$db3->query($query);				//Tutor eintragen
				$user_added = TRUE;
				$query = "DELETE FROM admission_seminar_user WHERE seminar_id = '$s_id' AND user_id = '$add_tut_id' ";
				$db3->query($query);				//delete possible entrys in wainting list
				if ($db3->affected_rows()) renumber_admission($s_id);
			}
		}

		if (!$lockdata[$lock_status]['seminar_inst'])
		{
		// delete all old participating institutions, then write new list
		if (($b_institute) || ($Institut)) {
			$query = "DELETE from seminar_inst where Seminar_id='$s_id'";
			$db3->query($query);
		}

		if ($b_institute) {
			while (list($key,$val) = each($b_institute)) {       // alle ausgewählten beteiligten Institute durchlaufen
				$query = "INSERT INTO seminar_inst values('$s_id','$val')";
				$db3->query($query);			     // Institut eintragen
			}
		}

		// Heimat-Institut ebenfalls eintragen, wenn noch nicht da
		$query = "INSERT IGNORE INTO seminar_inst values('$s_id','$Institut')";
		$db3->query($query);

		}

		//Update the additional data-fields
		if (is_array($datafield_id)) {
			$ffCount = 0; // number of processed form fields
			foreach ($datafield_id as $i=>$id) {
				$struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));
				$entry  = DataFieldEntry::createDataFieldEntry($struct, $s_id);
				$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
				if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
					$entry->setValue('');
					$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
				}
				elseif ($numFields == 1)
					$entry->setValue($datafield_content[$ffCount]);
				else
					$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
				$ffCount += $numFields;
				if ($entry->isValid() && !$lockdata[$lock_status][$id])
					$entry->store();
				else
					$invalidEntries[$id] = $entry;
			}
			$msg .= "msg§" . _("Die Grunddaten der Veranstaltung wurden ver&auml;ndert.") . "§";
			if (count($invalidEntries) > 0)
				$msg .= "error§" . _("Fehlerhafte Eingaben (s.u.) wurden nicht gespeichert") . "§";
		}
	}  // end if ($run)

	//Bereiche aendern
	if ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["bereiche"]) {
		if (isset($_REQUEST['details_chooser'])) {
			$st_search->insertSelectedRanges();
			if ($st_search->num_inserted) {
				$msg .= "msg§" . sprintf(_("%s Studienbereiche hinzugefügt."),$st_search->num_inserted) ."§";
			}
			if ($st_search->num_deleted) {
				$msg .= "msg§" . sprintf(_("%s Studienbereiche gel&ouml;scht."),$st_search->num_deleted) ."§";
			}
			if ($st_search->num_deleted || $st_search->num_inserted) {
				$st_search->init();
			}
		}
	} else {
		// nur alte Eintraege rauswerfen, falls voher Kategorie mit Bereichen gewaehlt war
		$query = "DELETE from seminar_sem_tree where seminar_id='$s_id'";
		$db3->query($query);
	}

}  // end if ($s_send)


// Details-Formular
if (($s_id) && (auth_check())) {
  $db->query("SELECT x.*, y.Name AS Institut FROM seminare x LEFT JOIN Institute y USING (institut_id) WHERE x.Seminar_id = '$s_id'");
  $db->next_record();
  $user_id = $auth->auth["uid"];
  $db2->query("select * from seminar_user where Seminar_id = '$s_id' and user_id = '$user_id'");
  $db2->next_record();
  $my_perms = $db2->f("status");
  if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME)
		$tmp_typ = _("Veranstaltung");
  else
		$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];

	?>
	<table border=0 align="center" cellspacing=0 cellpadding=0 width="100%">
	<tr><td class="blank" colspan=2><br>
	<?
	parse_msg($msg);
	?>
	</td></tr><tr><td class="blank" colspan=2>
	<table border=0 align="center" cellspacing=0 cellpadding=2 width="99%">
	<?

	// ab hier Anzeigeroutinen ///////////////////////////////////////////////
	echo "<form name=\"details\" method=\"post\" action=\"$PHP_SELF#anker\">";
	?>
		<input type="hidden" name="s_id"   value="<?php $db->p("Seminar_id") ?>">
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
        <td class="<?= $cssSw->getClass() ?>" align=right><b><?=_("Name der Veranstaltung")?></b>
        <? if ($lockdata[$lock_status]["Name"]) : ?>
          <?=  $label_lock_text;?>
        <? endif; ?>
      </td>
				<td class="<?= $cssSw->getClass()?>" align=left colspan=2>&nbsp;
        <? if (! $lockdata[$lock_status]["Name"]) : ?>
              <input type="text" name="Name" onchange="checkname()" size=58 maxlength=254 value="<?= htmlReady($db->f("Name")) ?>" >
        <? else : ?>
              <input readonly disabled type="text" name="Name" onchange="checkname()" size=58 maxlength=254 value="<?= htmlReady($db->f("Name")) ?>" >
        <? endif; ?>
				</td>
			</tr>
			<tr>
        <td class="<?= $cssSw->getClass() ?>" align=right><?=_("Untertitel der Veranstaltung")?>
          <? if ($lockdata[$lock_status]["Untertitel"]) { echo $label_lock_text; }?></td>
        </td>
        <td class="<?= $cssSw->getClass() ?>" align=left colspan=2>
          &nbsp;
          <?if (! $lockdata[$lock_status]["Untertitel"]) : ?>
            <input type="text" name="Untertitel" size=58 maxlength=254 value="<?php echo htmlReady($db->f("Untertitel")) ?>" >
          <? else : ?>
            <input readonly disabled type="text" name="Untertitel" size=58 maxlength=254 value="<?php echo htmlReady($db->f("Untertitel")) ?>" >
          <? endif; ?>
			</tr>
			<tr>
        <td class="<?= $cssSw->getClass() ?>" align=right><b><?=_("Typ der Veranstaltung")?></b>
          <? if ($lockdata[$lock_status]["status"]) { echo $label_lock_text;}?>
        </td>
				<td class="<?= $cssSw->getClass() ?>"  align=left colspan=2>&nbsp;
        <? if ($lockdata[$lock_status]["status"]) : ?>
          <input type="hidden" name="Status" value="<?= $db->f("status"); ?>">
          <?= $SEM_TYPE[$db->f("status")]["name"]; ?>
          <?= "&nbsp;" . _("in der Kategorie") . " <b>".htmlReady($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"])."</b>" ?>
          <br/><?= $lock_text ?>

        <? else : ?>
          <select name="Status">
          <?
          if (!$perm->have_perm("admin")) {
            $i=0;
            for ($i=1; $i <= sizeof($SEM_TYPE); $i++) {
              if ($SEM_TYPE[$i]["class"] == $SEM_TYPE[$db->f("status")]["class"])
                printf ("<option %s value=%s>%s</option>", $db->f("status")== $i ? "selected" : "", $i, htmlReady($SEM_TYPE[$i]["name"]));
            }
          } else {
            foreach ($SEM_TYPE as $sem_type_id => $sem_type) {
              if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]
                  || !$SEM_CLASS[$sem_type["class"]]["bereiche"])
                printf("<option %s value=%s>%s (%s)</option>",
                      $db->f("status") == $sem_type_id ? "selected" : "",
                      $sem_type_id,
                      htmlReady($sem_type["name"]),
                      htmlReady($SEM_CLASS[$sem_type["class"]]["name"]));
            }
            printf ("</select></td>");
          } ?>
        <? endif; ?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Art der Veranstaltung")?>

				  <? if ($lockdata[$lock_status]["art"]) : ?>
	          <?= $label_lock_text ?>
	        <? endif; ?>
	      </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;

        <? if (! $lockdata[$lock_status]["art"]) : ?>
          <input type="text" name="art" size=30 maxlength=254 value="<?php echo htmlReady($db->f("art")) ?>" >
        <? else: ?>
          <input readonly disabled type="text" name="art" size=30 maxlength=254 value="<?php echo htmlReady($db->f("art")) ?>" >
        <? endif; ?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Veranstaltungs-Nummer")?>
				  <? if ($lockdata[$lock_status]["VeranstaltungsNummer"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
        <? if (! $lockdata[$lock_status]["VeranstaltungsNummer"]) : ?>
          <input type="text" name="VeranstaltungsNummer" size="20" maxlength="32" value="<?php echo htmlReady($db->f("VeranstaltungsNummer")) ?>">
        <? else : ?>
          <input readonly disabled type="text" name="VeranstaltungsNummer" size="20" maxlength="32" value="<?php echo htmlReady($db->f("VeranstaltungsNummer")) ?>">
        <? endif; ?>
      </td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("ECTS-Punkte")?>
				  <? if ($lockdata[$lock_status]["ects"]) : ?>
            <?= $label_lock_text;?>
          <? endif; ?>

				</td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (!$lockdata[$lock_status]["ects"]) : ?>
            <input type="text" name="ects" size="6" maxlength="32" value="<?php echo htmlReady($db->f("ects")) ?>">
          <? else: ?>
            <input readonly disabled type="text" name="ects" size="6" maxlength="32" value="<?php echo htmlReady($db->f("ects")) ?>">
          <? endif; ?>
        </td>
			</tr>
			<tr>
        <td class="<? echo $cssSw->getClass() ?>" align=right>
          <? printf ("%s" . _("max. TeilnehmerInnenanzahl") . "%s", ($db->f("admission_type")) ? "<b>" : "",  ($db->f("admission_type")) ? "</b>" : ""); ?>
          <? if ($lockdata[$lock_status]["admission_turnout"]) : ?>
            <?= $label_lock_text ?>
          <? endif; ?>

        </td>
        <td class="<? echo $cssSw->getClass() ?>"  align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["admission_turnout"]) : ?>
            <input type="int" name="turnout" size=6 maxlength=4 value="<?php echo $db->f("admission_turnout") ?>">
          <? else : ?>
            <input readonly disabled type="int" name="turnout" size=6 maxlength=4 value="<?php echo $db->f("admission_turnout") ?>">
          <? endif; ?>
        </td>
			</tr>
			<tr>
        <td class="<? echo $cssSw->getClass() ?>" align=right>
          <?=_("Beschreibung")?>
          <? if ($lockdata[$lock_status]["Beschreibung"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["Beschreibung"]) : ?>
            <textarea name="Beschreibung" cols=58 rows=6 ><?php echo htmlReady($db->f("Beschreibung")) ?></textarea>
          <? else: ?>
            <textarea readonly disabled name="Beschreibung" cols=58 rows=6 ><?php echo htmlReady($db->f("Beschreibung")) ?></textarea>
          <? endif; ?>
        </td>
			</tr>
			<tr>
				<?
          if ($my_perms != "tutor"
                && !$lockdata[$lock_status]["Institut_id"] ) {
						echo "<td class=\"".$cssSw->getClass()."\" align=right><b>" . _("Heimat-Einrichtung") . "</b></td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<select name=\"Institut\">";
						if (!$perm->have_perm("admin"))
							$db3->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND (inst_perms = 'dozent' OR inst_perms = 'tutor')) ORDER BY is_fak,Name");
						else if (!$perm->have_perm("root"))
							$db3->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') ORDER BY is_fak,Name");
						else
							$db3->query("SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
						while ($db3->next_record()) {
							printf ("<option %s style=\"%s\" value=\"%s\"> %s</option>", $db3->f("Institut_id") == $db->f("Institut_id") ? "selected" : "",
								($db3->f("is_fak")) ? "font-weight:bold;" : "", $db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
							if ($db3->f("Institut_id") == $db->f("Institut_id")){
								$found_home_inst = true;
							}
							if ($db3->f("is_fak") && $db3->f("inst_perms") == "admin"){
								$db2->query("SELECT a.Institut_id, a.Name FROM Institute a
											 WHERE fakultaets_id='" . $db3->f("Institut_id") . "' AND a.Institut_id!='" .$db3->f("Institut_id") . "' ORDER BY Name");
								while($db2->next_record()){
									printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("Institut_id") == $db->f("Institut_id") ? "selected" : "",
										$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
								}
							}
						}
						if ($perm->get_perm() == 'dozent' && !$found_home_inst){
							printf("<option selected value=\"%s\"> %s</option>", $db->f("Institut_id") , htmlReady(my_substr($db->f("Institut"),0,60)));
						}
						echo "</select>";
					} else {
						echo "<td class=\"".$cssSw->getClass()."\" align=right>" . _("Heimat-Einrichtung") ;
            if ($lockdata[$lock_status]["Institut_id"]) { echo $label_lock_text;}
						echo "</td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<input type=\"HIDDEN\" name=\"Institut\" value=\"".$db->f("Institut_id")."\" />";
						echo "<b>".htmlReady($db->f("Institut"))."</b>";
						echo "<br/>" . $lock_rules->getLockText();
					}

				?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("beteiligte Einrichtungen")?>
          <? if ($lockdata[$lock_status]["seminar_inst"]) : ?>
				          <?= $label_lock_text; ?>
        <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["seminar_inst"]) : ?>
            <select  name="b_institute[]" MULTIPLE SIZE=8 >
					<?php
					$db3->query("SELECT Name,a.Institut_id,b.Institut_id as beteiligt FROM Institute a LEFT JOIN seminar_inst b ON(a.Institut_id=b.Institut_id AND Seminar_id='$s_id') WHERE a.Institut_id=a.fakultaets_id ORDER BY Name");
					while ($db3->next_record()) {
						printf ("<option %s style=\"font-weight:bold;\" value=\"%s\"> %s</option>", ($db3->f("beteiligt") && ($db3->f("beteiligt") != $db->f("Institut_id"))) ? "selected" : "",
								$db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
						$db2->query("SELECT a.Institut_id, a.Name,b.Institut_id as beteiligt FROM Institute a LEFT JOIN seminar_inst b ON(a.Institut_id=b.Institut_id AND Seminar_id='$s_id')
						WHERE fakultaets_id='" . $db3->f("Institut_id") . "' AND a.Institut_id!='" .$db3->f("Institut_id") . "' ORDER BY Name" );
						while($db2->next_record()){
							printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", ($db2->f("beteiligt") && ($db2->f("beteiligt") != $db->f("Institut_id"))) ? "selected" : "",
								$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
						}
					}
					?>
            </select>
          <? else: ?>
            <?
              $db3->query("SELECT a.Name,a.Institut_id,b.Institut_id as beteiligt
                FROM seminare s
                LEFT JOIN seminar_inst b
                ON (s.Seminar_id = b.Seminar_id)
                LEFT JOIN Institute a
                ON (a.Institut_id=b.Institut_id AND s.Seminar_id='$s_id')
                WHERE a.Institut_id=a.fakultaets_id
                AND s.Institut_id != b.institut_id ORDER BY a.Name");

              ?>
              <? if ($db3->num_rows() == 0) : ?>
                <?= _('keine beteiligte Einrichtung'); ?>
              <? else : ?>
                <ul>
                  <? while($db3->next_record()) : ?>
                    <li>
                      <?= htmlReady(my_substr($db3->f("Name"),0,60)) ?>
                    </li>
                  <? endwhile ; ?>
                </ul>
              <? endif; ?>
  						<? echo "<br/>" . $lock_rules->getLockText(); ?>

        <? endif; ?>
        </td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				<?
				if (($user_added) || ($user_deleted) || ($reset_search_x) || ($search_exp_tut) || ($search_exp_doz) || ($user_moved) )
					print "<a name=\"anker\"></a>";
				?>
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>     <!-- Dozenten und Tutoren -->
			<?
			//Fuer Tutoren eine Sonderregelung, da sie nicht alle Daten aendern duerfen
			if ($my_perms == "tutor") {
				?>
				<td class="<? echo $cssSw->getClass() ?>" align="right"><? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) echo  _("DozentInnen"); else echo _("LeiterInnen");?>
				<? if ($lockdata[$lock_status]["dozent"]) : ?>
				  <?= $label_lock_text; ?>
        <? endif; ?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" colspan="2">&nbsp;
				<?
				$db3->query("SELECT ". $_fullname_sql['full'] ." FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE status = 'dozent' AND Seminar_id='$s_id' ORDER BY Nachname");
				$i=0;
				while ($db3->next_record()) {
					if ($i)
						echo ", ";
					echo "<b>" . htmlReady($db3->f(0)) . "</b>";
					$i++;
				}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) echo _("TutorInnen"); else echo _("Mitglieder");?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
				<?
				$db3->query("SELECT ". $_fullname_sql['full'] ." FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE status = 'tutor' AND Seminar_id='$s_id' ORDER BY position, Nachname");
				$i=0;
				while ($db3->next_record()) {
					if ($i)
						echo ", ";
					echo "<b>" . htmlReady($db3->f(0)) . "</b>";
					$i++;
				}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <font color="#FF0000"><?=_("Die Personendaten k&ouml;nnen Sie mit Ihrem Status nicht bearbeiten!")?></font></td>
				<?
			} else {

				if ($perm->have_perm("admin"))
					printf ("<td %s align=right><b>%s</b></td>", $cssSw->getFullClass(), (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? _("DozentInnen") : _("LeiterInnen"));
				else
					printf ("<td %s align=right>%s %s</td>", $cssSw->getFullClass(), (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? _("DozentInnen") : _("LeiterInnen"), $lockdata[$lock_status]["dozent"] ? $label_lock_text : '');

				?>
				<td class="<? echo $cssSw->getClass() ?>" align="left" colspan=1>

            <?= get_dozent_data($s_id,$_fullname_sql, $lockdata[$lock_status]['dozent'], $lock_rules->getLockText()) ?>

				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" valign="top">
					<?
					$no_doz_found=TRUE;
					if (($search_exp_doz) && ($search_doz_x)) {
						$search_exp_doz = trim($search_exp_doz);
						if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["only_inst_user"]) {
							$query3 = sprintf("SELECT institut_id FROM seminar_inst WHERE seminar_id = '%s'", $s_id);
							$db3->query($query3);
							$clause="AND Institut_id IN (";
							$i=0;
							while ($db3->next_record()) {
								if ($i)
									$clause.=", ";
								$clause.=" '".$db3->f("institut_id")."' ";
								$i++;
							}
							$clause.=")";
							$db4->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms = 'dozent' $clause AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");
						} else
							$db4->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id)  WHERE perms = 'dozent' AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");
						if ($db4->num_rows()) {
							$no_doz_found=FALSE;
							printf ("<font size=-1>" . _("<b>%s</b> NutzerIn gefunden:") . "<br />", $db4->num_rows());
							print "<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" ".tooltip(_("NutzerIn hinzufügen"))." border=\"0\" name=\"add_doz\" />";
							print "&nbsp; <select name=\"add_doz\">";
							while ($db4->next_record()) {
								printf ("<option value=\"%s\">%s </option>", $db4->f("username"), htmlReady(my_substr($db4->f("fullname") ." (" . $db4->f("username"). ")",  0, 30)));
							}
							print "</select></font>";
							print "<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" ".tooltip(_("Neue Suche starten"))." border=\"0\" name=\"reset_search\" />";
						}
					}
					if ($no_doz_found) {
						?>
						<?if (!$lockdata[$lock_status]["dozent"]) { ?>
						<font size=-1>
						<? printf ("%s %s", (($search_exp_doz) && ($no_doz_found)) ? _("Keinen Nutzenden gefunden.") . " <a name=\"anker\"></a>" : "",   (!$search_exp_doz) ? (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? _("DozentIn hinzuf&uuml;gen") : _("LeiterIn hinzuf&uuml;gen")  : "");?>
						</font><br />
						<input type="TEXT" size="30" maxlength="255" name="search_exp_doz" />&nbsp;
						<input type="IMAGE" src="<?= $GLOBALS['ASSETS_URL'] ?>images/suchen.gif" <? echo tooltip(_("Suche starten")) ?> border="0" name="search_doz" /><br />
						<font size=-1><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.")?></font>
						<?
					}
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<hr width="99%" align="right">
				<td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align="right">
				  <? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"])
				    echo _("TutorInnen"); else echo _("Mitglieder");?>
					  <? if ($lockdata[$lock_status]["tutor"]) : ?>
						  <?= $label_lock_text ?>
						<? endif; ?>

				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left">

               <?= get_tutor_data($s_id,$_fullname_sql, $lockdata[$lock_status]['tutor'], $lock_rules->getLockText()) ?>

				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" valign="top">
					<?
					$no_tut_found=TRUE;
					if (!$lockdata[$lock_status]["tutor"]) {
					if (($search_exp_tut) && ($search_tut_x)) {
						$search_exp_tut = trim($search_exp_tut);
						if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["only_inst_user"]) {
							$query3 = sprintf("SELECT institut_id FROM seminar_inst WHERE seminar_id = '%s'", $s_id);
							$db3->query($query3);
							$clause="AND Institut_id IN (";
							$i=0;
							while ($db3->next_record()) {
								if ($i)
									$clause.=", ";
								$clause.="'".$db3->f("institut_id")."'";
								$i++;
							}
							$clause.=")";
							$db4->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms IN ('tutor', 'dozent') $clause AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");
						} else
							$db4->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE perms IN ('tutor', 'dozent') AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");
						if ($db4->num_rows()) {
							$no_tut_found=FALSE;
							printf ("<font size=-1>" . _("<b>%s</b> NutzerIn gefunden:") . "<br />", $db4->num_rows());
							print "<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" ".tooltip(_("NutzerIn hinzufügen"))." border=\"0\" name=\"add_tut\" />";
							print "&nbsp; <select name=\"add_tut\">";
							while ($db4->next_record()) {
								printf ("<option value=\"%s\">%s </option>", $db4->f("username"), htmlReady(my_substr($db4->f("fullname")." (".$db4->f("username").")", 0, 30)));
							}
							print "</select></font>";
							print "<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" ".tooltip(_("neue Suche starten"))." border=\"0\" name=\"reset_search\" />";
						}
					}
					if ($no_tut_found) {
						?>
						<font size=-1>
						<? printf ("%s %s", (($search_exp_tut) && ($no_tut_found)) ? _("Keinen Nutzenden gefunden.") . "<a name=\"anker\"></a>" : "",   (!$search_exp_tut) ? (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? _("TutorIn hinzuf&uuml;gen") : _("Mitglied hinzuf&uuml;gen")  : "");?>
						</font><br />
						<input type="TEXT" size="30" maxlength="255" name="search_exp_tut" />&nbsp;
						<input type="IMAGE" src="<?= $GLOBALS['ASSETS_URL'] ?>images/suchen.gif" <? echo tooltip(_("Suche starten")) ?> border="0" name="search_tut" /><br />
						<font size=-1><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.")?></font>
						<?
					}
          }
      }
					?>
				</td>
				</td>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<hr width="99%" align="right">
				<td>

				<?
				}
				?>
			</tr>
			<?
			//Bereichsauswahl
			if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) {
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><b><?=_("Studienbereich(e)")?></b>
                <? if ($lockdata[$lock_status]["sem_tree"]) { echo $label_lock_text;}?>
                </td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<?
                        if ($lockdata[$lock_status]["sem_tree"]) {
                            $institutes = get_Institutes($s_id);
                            $SET_SEM_PATHS = Array();
                            $sess->register("SET_SEM_PATHS");
                            foreach($institutes as $sem_path)
                            {
                                $SET_SEM_PATHS[implode("|",get_sem_tree($sem_path["id"]))] = true;
                            }
                            for ($i=0;$i<count($institutes);$i++) {
                                echo "<font size=-1>".format_sem_tree($institutes[$i]["tree"])."</font>";
                                echo "<br>";
                            }
                            echo $lock_text;
                        } else {
					echo "\n<div align=\"left\">&nbsp;";
					echo $st_search->getSearchField(array('style' => 'vertical-align:middle;','size'=>30));
					echo "&nbsp;";
					echo $st_search->getSearchButton(array('style' => 'vertical-align:middle;'));
					echo "<br>&nbsp;&nbsp;<span style=\"font-size:10pt;\">" . _("Geben Sie zur Suche den Namen des Studienbereiches ein.");
					if ($st_search->num_search_result !== false){
						echo "<br><a name=\"anker\">&nbsp;&nbsp;</a><b>" . sprintf(_("Ihre Suche ergab %s Treffer."),$st_search->num_search_result) . (($st_search->num_search_result) ? _(" (Suchergebnisse werden blau angezeigt)") : "") . "</b>";
					}
					echo "</span><br>&nbsp;";
					echo $st_search->getChooserField(array('size' => 12, 'onChange' => 'checkbereich()'),70);
					echo "</div>";
				?>
				</td>
			<?
			}
			?>
			<tr>
				<td class="<? $cssSw->switchClass();  echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>

			<tr>
	<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("TeilnehmerInnen")?>
          <? if ($lockdata[$lock_status]["teilnehmer"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (!$lockdata[$lock_status]["teilnehmer"]) : ?>
            <textarea name="teilnehmer" cols=58 rows=3 ><?= htmlReady($db->f("teilnehmer")) ?></textarea>
          <? else: ?>
             <textarea disabled readonly name="teilnehmer" cols=58 rows=3 ><?= htmlReady($db->f("teilnehmer")) ?></textarea>
          <? endif; ?>
        </td>
			</tr>

			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Voraussetzungen")?>
          <? if ($lockdata[$lock_status]["voraussetzungen"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
				</td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["vorraussetzungen"]) : ?>
            <textarea name="vorrausetzungen" cols=58 rows=3><?= htmlReady($db->f("vorrausetzungen")) ?></textarea>
          <? else : ?>
            <textarea disabled readonly name="vorrausetzungen" cols=58 rows=3><?= htmlReady($db->f("vorrausetzungen")) ?></textarea>
          <? endif; ?>
      </td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Lernorganisation")?>
          <? if ($lockdata[$lock_status]["lernorga"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
        <? if (! $lockdata[$lock_status]["lernorga"]) : ?>
          <textarea name="lernorga" cols=58 rows=3 ><?php echo htmlReady($db->f("lernorga")) ?></textarea>
        <? else: ?>
          <textarea disabled readonly name="lernorga" cols=58 rows=3 ><?php echo htmlReady($db->f("lernorga")) ?></textarea>        <? endif; ?>
        </td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Leistungsnachweis")?>
          <? if ($lockdata[$lock_status]["leistungsnachweis"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
				</td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["leistungsnachweis"]) : ?>
            <textarea name="leistungsnachweis" cols=58 rows=3 ><?= htmlReady($db->f("leistungsnachweis")) ?></textarea>
          <? else: ?>
            <textarea disabled readonly name="leistungsnachweis" cols=58 rows=3 ><?= htmlReady($db->f("leistungsnachweis")) ?></textarea>          <? endif; ?>
        </td>
			</tr>
			<tr>
        <td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Ort")?>
          <? if ($lockdata[$lock_status]["Ort"]) : ?>
            <?= $label_lock_text; ?>
          <? endif; ?>
        </td>
        <td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["Ort"]) : ?>
            <textarea name="room" cols=58 rows=3 ><?= htmlReady($db->f("Ort")) ?></textarea>
          <? else: ?>
						<textarea disabled readonly name="room" cols=58 rows=3 ><?= htmlReady($db->f("Ort")) ?></textarea>
					<? endif; ?>
				<br />&nbsp; <font size="-1"><b><?=_("Achtung:")."&nbsp;</b>"._("Diese Ortsangabe wird nur angezeigt, wenn keine Angaben aus Zeiten oder Sitzungsterminen gemacht werden k&ouml;nnen.");?></font>
				</td>
			</tr>
			<?
			//add the free adminstrable datafields
#			$localFields = $DataFields->getLocalFields($s_id);
			$localEntries = DataFieldEntry::getDataFieldEntries($s_id);
			foreach ($localEntries as $entry) {
				$id = $entry->structure->getID();  // datafield id
				$color = '#000000';
				if ($invalidEntries[$id]) {        // if entered value is invalid...
					$entry = $invalidEntries[$id];  // ... we keep it and show it in the corresponding form fields
					$entry->structure->load();      // get all structure information from the database (view permissions etc.)
					$color = 'ff0000';              // the corresponding name is highlighted
				}

			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right width="30%">
					<font color="<?=$color?>"><?=htmlReady($entry->getName())?></font>

					<? if ($lockdata[$lock_status][$entry->structure->getID()]) : ?>
            <?= $label_lock_text ?>
          <? endif; ?>
    		</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<?
					if ($perm->have_perm($entry->structure->getEditPerms())
						&& !$lockdata[$lock_status][$entry->structure->getID()])
					{
						print '&nbsp;&nbsp;' . $entry->getHTML('datafield_content[]', $entry->structure->getID());
					?>
					   <input type="hidden" name="datafield_id[]" value="<?=$entry->structure->getID()?>">
					   <input type="hidden" name="datafield_type[]" value="<?=$entry->getType() ?>">
					<?
					}
					else {
							$input_field = $entry->getHTML('datafield_content[]', $entry->structure->getID());
							$input_field_locked = preg_replace('/<input /', '<input disabled readonly ', $input_field);
   					?>

	   				<?= $input_field_locked ?><br />
		   			<font size="-1>">&nbsp; <?="<i>"._("(Das Feld ist f&uuml;r die Bearbeitung gesperrt und kann nur durch einen Administrator ver&auml;ndert werden.)")."</i>"?></font>
			      <?
					}
					?>
				</td>
			</tr>
			<?
			}
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><?=_("Sonstiges")?>
          <? if ($lockdata[$lock_status]["Sonstiges"]) : ?>
            <?= $label_lock_text ?>
          <? endif; ?>

				</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp;
          <? if (! $lockdata[$lock_status]["Sonstiges"]) : ?>
            <textarea name="Sonstiges" cols=58 rows=3 ><?= htmlReady($db->f("Sonstiges")) ?></textarea>
          <? else: ?>
						<textarea disabled readonly name="Sonstiges" cols=58 rows=3 ><?= htmlReady($db->f("Sonstiges")) ?></textarea>
					<? endif; ?>
				</td>
			</tr>
			<?
			$mkstring=date ("d.m.Y, G:i", $db->f("mkdate"));
			if (!$db->f("mkdate"))
				$mkstring=_("unbekannt");
			$chstring=date ("d.m.Y, G:i", $db->f("chdate"));
			if (!$db->f("chdate"))
				$chstring=_("unbekannt");
			?>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" colspan=3 align="right">
					<?
					printf("<font size=-1><i>" . _("Veranstaltung angelegt am %s, letzte &Auml;nderung der Veranstaltungsdaten am %s") . "</i></font>&nbsp; <br />&nbsp; ", "<b>$mkstring</b>", "<b>$chstring</b>");
					?>
				</td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
		</form>
	</table>
	</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<?
}
?>
</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
?>
