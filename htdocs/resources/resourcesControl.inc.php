<?
/*
resourcesControl.php - 0.8
Steuerung fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

/*****************************************************************************
Requires & Registers
/*****************************************************************************/

require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesVisual.inc.php");

$sess->register("resources_data");
$my_perms= new ResourcesPerms;
$msg = new ResourcesMsg;
$db=new DB_Seminar;

/*****************************************************************************
Kopf der Ausgabe
/*****************************************************************************/
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
include ("$ABSOLUTE_PATH_STUDIP/header.php");

/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

include ("$RELATIVE_PATH_RESOURCES/lib/evaluate_values.php");


//Create Reitersystem
if ($SessSemName[1])
	include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
else
	include ("$RELATIVE_PATH_RESOURCES/views/links_resources.inc.php");

include ("$RELATIVE_PATH_RESOURCES/views/page_intros.inc.php");

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic" >&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Ressourcen"><b>&nbsp;<? echo $title; ?></b></td>
	</tr>
	<?
	if ($infobox) {
	?>
	<tr>
		<td class="blank">&nbsp;
		</td>
	</tr>
	<?
	}
	?>
	<tr>
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<?
						if ($msg->checkMsgs()) {	
							print "<tr><td class=\"blank\">&nbsp; </td></tr>";
							$msg->displayAllMsg($view_mode = "line");
						}
						if ($page_intro) {
						?>
						<tr>
							<td class="blank"><? (!$infobox) ? print "<br />":"" ?>
								<blockquote>
								<? echo $page_intro ?>
								&nbsp; </blockquote>
							</td>
						</tr>	
						<?
						}
						?>
						<tr>
							<td class="blank" valign ="top">
	
	<?	

/*****************************************************************************
Treeview, die Strukturdarstellung, views: resources, _resources, make_hierarchie
/*****************************************************************************/
if ($resources_data["view"]=="resources" || $resources_data["view"]=="_resources"){

	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	$range_id = $user->id;

	$resUser=new ResourcesRootThreads($range_id);
	$thread=new ShowThread();
	
	$roots=$resUser->getRoots();
	if (is_array($roots)) {
		foreach ($roots as $a) {
			$thread->showThreadLevel($a);
		}
		echo "<br />&nbsp;";			
	} else {
		echo "</td></tr>";
		parse_msg ("info§Es sind keine Objekte beziehungsweise Ebene angelegt, auf die Sie Zugriff haben. <br />Sie k&ouml;nnen eine neue Ebene erzeugen, wenn sie \"Neue Hierarchie erzeugen\" anw&auml;hlen.");
	}

	if ($edit_structure_object) {
		echo "</form>";
	}
	
}

/*****************************************************************************
Listview, die Listendarstellung, views: lists, _lists, openobject_main
/*****************************************************************************/
if ($resources_data["view"]=="lists" || $resources_data["view"]=="_lists" || $resources_data["view"]=="openobject_main") {

	$list=new ShowList();

	if ($resources_data["list_recurse"])
		$list->setRecurseLevels(-1);
	else
		$list->setRecurseLevels(0);
		
	if ($resources_data["view"] != "openobject_main")
		$list->setAdminButtons(TRUE);
	
	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}
	
	if ($resources_data["view"]=="openobject_main") {
		if (!$list->showRangeList($SessSemName[1])) {
			echo "</td></tr>";
			parse_msg ("info§Es existieren keine Ressourcen, die Sie in dieser Veranstaltung belegen k&ouml;nnen.");
		}
	} else {
		if (!$list->showList($resources_data["list_open"])) {
			echo "</td></tr>";
			parse_msg ("info§Sie haben keine Ebene ausgew&auml;hlt. Daher kann keine Liste erzeugt werden. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie unter \"&Uuml;bersicht\" einen Ebene bzw. Ressource in der Hierachie aus.");
		}
	}
	
	if ($edit_structure_object) {
		echo "</form>";
	}
}

/*****************************************************************************
Objecteigenschaften bearbeiten, views: edit_object_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_properties" || $resources_data["view"]=="objects") {

	if ($resources_data["actual_object"]) {
		$editObject=new editObject($resources_data["actual_object"]);
		$editObject->showPropertiesForms();
	} else {
		echo "</td></tr>";
		parse_msg ("info§Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}


/*****************************************************************************
Objecteigenschaften anzeigen, views: openobject_details
/*****************************************************************************/
if ($resources_data["view"]=="openobject_details") {

	if ($resources_data["actual_object"]) {
		$viewObject = new viewObject($resources_data["actual_object"]);
		$viewObject->view_properties();
	} else {
		echo "</td></tr>";
		parse_msg ("info§Sie haben keine Objekt zum Anzeigen ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Objectberechtigungen bearbeiten, views: edit_object_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_perms") {
	if ($resources_data["actual_object"]) {
		$editObject=new editObject($resources_data["actual_object"]);
		$editObject->showPermsForms();
	} else {
		echo "</td></tr>";
		parse_msg ("info§Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Objectbelegung bearbeiten, views: edit_object_assign, openobject_assign
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_assign" || $resources_data["view"]=="openobject_assign") {
	if ($resources_data["actual_object"]) {
		$editObject=new editObject($resources_data["actual_object"]);
		$editObject->setUsedView($resources_data["view"]);
		if ($edit_assign_object)
			$assign_id=$edit_assign_object;
		$editObject->showScheduleForms($assign_id);
	} else {
		echo "</td></tr>";
		parse_msg ("info§Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
Typen verwalten, views: edit_types
/*****************************************************************************/
if ($resources_data["view"]=="edit_types") {
	
	$editSettings=new editSettings;
	$editSettings->showTypesForms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_properties") {
	
	$editSettings=new editSettings;
	$editSettings->showPropertiesForms();
}

/*****************************************************************************
Berechtigungen verwalten, views: edit_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_perms") {
	
	$editSettings=new editSettings;
	$editSettings->showPermsForms();
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($resources_data["view"]=="view_schedule" || $resources_data["view"]=="openobject_schedule") {
	
	if ($resources_data["actual_object"]) {
		$ViewSchedules=new ViewSchedules($resources_data["actual_object"]);
		$ViewSchedules->setStartTime($resources_data["schedule_start_time"]);
		$ViewSchedules->setEndTime($resources_data["schedule_end_time"]);
		$ViewSchedules->setLengthFactor($resources_data["schedule_length_factor"]);
		$ViewSchedules->setLengthUnit($resources_data["schedule_length_unit"]);	
		$ViewSchedules->setWeekOffset($resources_data["schedule_week_offset"]);	
		$ViewSchedules->setUsedView($resources_data["view"]);	
		
		$ViewSchedules->navigator();
	
		if (($resources_data["schedule_start_time"]) && ($resources_data["schedule_end_time"]))
			if ($resources_data["schedule_mode"] == "list") //view List
				$ViewSchedules->showScheduleList($schedule_start_time, $schedule_end_time);
			else
				$ViewSchedules->showScheduleGraphical($schedule_start_time, $schedule_end_time);
	} else {
		echo "</td></tr>";
		parse_msg ("info§Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt. <br />Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus.");
	}
}

/*****************************************************************************
persoenliche Einstellungen verwalten, views: edit_personal_settings
/*****************************************************************************/
if ($resources_data["view"]=="edit_personal_settings") {
	
	$editSettings=new editPersonalSettings;
	$editSettings->showPesonalSettingsForms();
}

/*****************************************************************************
Search 'n' browse
/*****************************************************************************/
if ($resources_data["view"]=="search") {
	
	$search=new ResourcesBrowse;
	$search->setStartLevel('');
	$search->setMode($resources_data["search_mode"]);
	$search->setSearchArray($resources_data["search_array"]);
	if ($resources_data["browse_open_level"])
		$search->setOpenLevel($resources_data["browse_open_level"]);
	$search->showSearch();
}



/*****************************************************************************
Seite abschliessen und Infofenster aufbauen
/*****************************************************************************/
		?>						</td>
							</tr>
						</table>
					</td>
				<?
				if ($infobox) {
					?>
					<td class="blank" width="270" align="right" valign="top">
						<? print_infobox ($infobox, $infopic);?>
					</td>
					<?
				}
			?>				
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="blank">&nbsp; 
		</td>
	</tr>
</table>
<?
page_close();
?>