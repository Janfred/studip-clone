<?
/**
* resourcesControl.php
*
* the controlling body of the resource-management
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		resourcesControl.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesControl.php
// zentrale Steuerung der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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



/*****************************************************************************
Requires & Registers
/*****************************************************************************/

require_once ('lib/include/reiter.inc.php');
require_once ('lib/msg.inc.php');
require_once ('lib/visual.inc.php');
require_once ('config.inc.php');
require_once ("config_tools_semester.inc.php");
require_once 'lib/functions.php';
require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/views/Msg.class.php");

$sess->register("resources_data");
$resources_data = unserialize($resources_data);
$globalPerm = getGlobalPerms($user->id);
$msg = new Msg;
$db=new DB_Seminar;
$db2=new DB_Seminar;


/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

// if directly editing a request from request list,
// set working_pos and reload for the request
if ($view == "edit_request") {
	if (isset($edit)) {
		foreach ($resources_data['requests_working_on'] as $key => $val) {
			if ($val['request_id'] == $edit) {
				$resources_data['requests_working_pos'] = $key;
				break;
			}
		}
	}

	$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["reload"] = TRUE;
	//hmm, zu früh an dieser Stelle. Notwendig?
	//page_close(NULL);
}

//handle values
include ("$RELATIVE_PATH_RESOURCES/lib/evaluate_values.php");

//load content, text, pictures and stuff
include ("$RELATIVE_PATH_RESOURCES/views/page_intros.inc.php");

/*****************************************************************************
Kopf der Ausgabe
/*****************************************************************************/
if (isset($_REQUEST['print_view'])){
	$_include_stylesheet = "style_print.css"; // use special stylesheet for printing
}

include ('lib/include/html_head.inc.php');
if ($quick_view_mode != "no_nav" && !isset($_REQUEST['print_view']))
	include ('lib/include/header.php');

//load correct nav
if ($view_mode == "oobj")
	include ('lib/include/links_openobject.inc.php');
elseif (($view_mode == "no_nav") || ($view_mode == "search") || isset($_REQUEST['print_view']))
	;
else
	include ("$RELATIVE_PATH_RESOURCES/views/links_resources.inc.php");


?>
<script type="text/javascript">
function check_opener(obj){
	if (window.opener && obj.href){
		window.opener.location.href = obj.href;
		window.opener.focus();
		return false;
	}
	return true;
}
</script>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<?
	if (!isset($_REQUEST['print_view'])){
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
							if (!$infobox)
									print "<tr><td class=\"blank\">&nbsp; </td></tr>";
							$msg->displayAllMsg("line");
							print "<tr><td class=\"blank\">&nbsp; </td></tr>";
						}
						if ($page_intro) {
						?>
						<tr>
							<td class="blank"><? (!$infobox) ? print "<br />":"" ?>
								<table width="99%" align="center" border="0" cellpadding="2" cellspacing ="0">
									<tr><td>
										<font size="-1"><? echo $page_intro ?></font><br />&nbsp;
									</td></tr>
								</table>
							</td>
						</tr>
						<?
						}
	}
	?>
	<tr>
		<td class="blank" valign ="top">

	<?

/*****************************************************************************
Treeview, die Strukturdarstellung, views: resources, _resources, make_hierarchie
/*****************************************************************************/
if ($view == "resources" || $view == "_resources"){
	require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoots.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowThread.class.php");


	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	$range_id = $user->id;

	$resUser=new ResourcesUserRoots($range_id);
	$thread=new ShowThread();

	$roots=$resUser->getRoots();
	if (is_array($roots)) {
		foreach ($roots as $a) {
			$thread->showThreadLevel($a);
		}
		echo "<br />&nbsp;";
	} else {
		echo "</td></tr>";
		$msg->displayMsg(12);
	}

	if ($edit_structure_object) {
		echo "</form>";
	}

}

/*****************************************************************************
Listview, die Listendarstellung, views: lists, _lists, openobject_main
/*****************************************************************************/
if ($view == "lists" || $view == "_lists" || $view == "openobject_main") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowList.class.php");

	$list=new ShowList();

	if ($resources_data["list_recurse"])
		$list->setRecurseLevels(-1);
	else
		$list->setRecurseLevels(0);

	if ($view != "openobject_main")
		$list->setAdminButtons(TRUE);

	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	if ($view == "openobject_main") {
		if (!$list->showRangeList($SessSemName[1])) {
			echo "</td></tr>";
			$msg->displayMsg(13);
		}
	} else {
		if (!$list->showListObjects($resources_data["list_open"])) {
			echo "</td></tr>";
			$msg->displayMsg(14);
		}
	}

	if ($edit_structure_object) {
		echo "</form>";
	}
}

/*****************************************************************************
Objecteigenschaften bearbeiten, views: edit_object_properties
/*****************************************************************************/
if ($view == "edit_object_properties" || $view == "objects") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditResourceData.class.php");

	if ($resources_data["actual_object"]) {
		$editObject=new EditResourceData($resources_data["actual_object"]);
		$editObject->showPropertiesForms();
	} else {
		echo "</td></tr>";
		$msg->displayMsg(15);
	}
}


/*****************************************************************************
Objecteigenschaften anzeigen, views: openobject_details
/*****************************************************************************/
if (($view == "openobject_details")  || ($view == "view_details")) {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowObject.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");

	//$perms = new ResourceObjectPerms($resources_data["actual_object"]);
	//echo $perms->getUserPerm();

	if ($resources_data["actual_object"]) {
		$viewObject = new ShowObject($resources_data["actual_object"]);
		$viewObject->showProperties();
	} else {
		echo "</td></tr>";
		$msg->displayMsg(16);
	}
}

/*****************************************************************************
Objectberechtigungen bearbeiten, views: edit_object_perms
/*****************************************************************************/
if ($view == "edit_object_perms") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditResourceData.class.php");

	if ($resources_data["actual_object"]) {
		$editObject=new EditResourceData($resources_data["actual_object"]);
		$editObject->showPermsForms();
	} else {
		echo "</td></tr>";
		$msg->displayMsg(15);
	}
}

/*****************************************************************************
Objectbelegung bearbeiten, views: edit_object_assign, openobject_assign
/*****************************************************************************/
if ($view == "edit_object_assign" || $view == "openobject_assign") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditResourceData.class.php");

	if ($view == "edit_object_assign") {
		$suppress_infobox = TRUE;
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
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
			<?
		}

	if ($resources_data["actual_object"]) {
		$editObject=new EditResourceData($resources_data["actual_object"]);
		$editObject->setUsedView($view);
		if ($edit_assign_object){
			$resources_data["actual_assign"]=$edit_assign_object;
		}
		$editObject->showScheduleForms($resources_data["actual_assign"]);
	} else {
		echo "</td></tr>";
		$msg->displayMsg(15);
	}
}

/*****************************************************************************
Typen verwalten, views: edit_types
/*****************************************************************************/
if ($view == "edit_types") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditSettings.class.php");

	$editSettings=new EditSettings;
	$editSettings->showTypesForms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_properties
/*****************************************************************************/
if ($view == "edit_properties") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditSettings.class.php");

	$editSettings=new EditSettings;
	$editSettings->showPropertiesForms();
}

/*****************************************************************************
Berechtigungen verwalten, views: edit_perms
/*****************************************************************************/
if ($view == "edit_perms") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditSettings.class.php");

	$editSettings=new EditSettings;
	$editSettings->showPermsForms();
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($view == "view_schedule" || $view == "openobject_schedule") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowSchedules.class.php");
	if ($resources_data["actual_object"]) {
		$ViewSchedules=new ShowSchedules($resources_data["actual_object"]);
		$ViewSchedules->setStartTime($resources_data["schedule_start_time"]);
		$ViewSchedules->setEndTime($resources_data["schedule_end_time"]);
		$ViewSchedules->setLengthFactor($resources_data["schedule_length_factor"]);
		$ViewSchedules->setLengthUnit($resources_data["schedule_length_unit"]);
		$ViewSchedules->setWeekOffset($resources_data["schedule_week_offset"]);
		$ViewSchedules->setUsedView($view);

		$ViewSchedules->navigator();
		$suppress_infobox = TRUE;
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
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
			<?
		if (($resources_data["schedule_start_time"]) && ($resources_data["schedule_end_time"]))
			if ($resources_data["schedule_mode"] == "list") //view List
				$ViewSchedules->showScheduleList($schedule_start_time, $schedule_end_time);
			else
				$ViewSchedules->showScheduleGraphical($schedule_start_time, $schedule_end_time);
	} else {
		echo "</td></tr>";
		$msg->displayMsg(15);
	}
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($view == "view_sem_schedule") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowSemSchedules.class.php");
	if ($resources_data["actual_object"]) {
		$ViewSchedules =& new ShowSemSchedules($resources_data["actual_object"], $resources_data['sem_schedule_semester_id'],$resources_data['sem_schedule_timespan']);
		$ViewSchedules->setUsedView($view);
		$ViewSchedules->navigator($_REQUEST['print_view']);
		$suppress_infobox = TRUE;
		?>						</td>
							</tr>
						</table>
					</td>
				<?
				if ($infobox && !isset($_REQUEST['print_view'])) {
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
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
			<?
		if (($resources_data["sem_schedule_semester_id"]) && ($resources_data["sem_schedule_timespan"]))
			if ($resources_data["schedule_mode"] == "list") //view List
				$ViewSchedules->showScheduleList($_REQUEST['print_view']);
			else
				$ViewSchedules->showScheduleGraphical($_REQUEST['print_view']);
	} else {
		echo "</td></tr>";
		$msg->displayMsg(15);
	}
}

if ($view == "view_group_schedule") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowGroupSchedules.class.php");
	$room_group = RoomGroups::GetInstance();
	if (isset($room_group->room_groups[$resources_data["actual_room_group"]]['rooms'])) {
		$ViewSchedules =& new ShowGroupSchedules($resources_data['actual_room_group'], $resources_data['sem_schedule_semester_id'],$resources_data['sem_schedule_timespan'], $resources_data['group_schedule_dow']);
		$ViewSchedules->setUsedView($view);
		$ViewSchedules->navigator($_REQUEST['print_view']);
		$suppress_infobox = TRUE;
		?>						</td>
							</tr>
						</table>
					</td>
				<?
				if ($infobox && !isset($_REQUEST['print_view'])) {
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
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
			<?
		if (isset($resources_data['actual_room_group']))
			$ViewSchedules->showScheduleGraphical($_REQUEST['print_view']);
	} else {
		echo "</td></tr>";
		$msg->displayMsg(25);
		$suppress_infobox = TRUE;
	}
}

/*****************************************************************************
persoenliche Einstellungen verwalten, views: edit_personal_settings
/*****************************************************************************/
if ($view == "edit_settings") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/EditSettings.class.php");

	$editSettings=new EditSettings;
	$editSettings->showSettingsForms();
}

/*****************************************************************************
Search
/*****************************************************************************/
if ($view == "search") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ResourcesBrowse.class.php");

	$search=new ResourcesBrowse;
	$search->setStartLevel('');
	$search->setMode($resources_data["search_mode"]);
	$search->setCheckAssigns($resources_data["check_assigns"]);
	$search->setSearchOnlyRooms($resources_data["search_only_rooms"]);
	$search->setSearchArray($resources_data["search_array"]);

	if ($resources_data["browse_open_level"])
		$search->setOpenLevel($resources_data["browse_open_level"]);
	$search->showSearch();
}

/*****************************************************************************
Roomplanning
/*****************************************************************************/
if ($view == "requests_start") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowToolsRequests.class.php");

	$toolReq=new ShowToolsRequests;
	$toolReq->showToolStart();
}

if ($view == "edit_request") {
	require_once ($RELATIVE_PATH_RESOURCES."/views/ShowToolsRequests.class.php");

	$toolReq=new ShowToolsRequests;
	$toolReq->showRequest($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["request_id"]);
}

if ($view == "list_requests") {
        require_once ($RELATIVE_PATH_RESOURCES."/views/ShowToolsRequests.class.php");

        $toolReq=new ShowToolsRequests;
        $toolReq->showRequestList();
}


/*****************************************************************************
Seite abschliessen und Infofenster aufbauen
/*****************************************************************************/
if (!$suppress_infobox) {
?>
								</td>
							</tr>
						</table>
					</td>
				<?
				if ($infobox) {
					if (is_object($clipObj))  {
						$formObj = $clipObj->getFormObject();
						$clip_form_action = ($quick_view) ? $PHP_SELF . "?quick_view=$quick_view&quick_view_mode=$quick_view_mode" : $PHP_SELF;
						print $formObj->getFormStart($clip_form_action);
					}
					?>
					<td class="blank" width="270" align="center" valign="top">
						<?
						print_infobox ($infobox, $infopic);
						if (is_object($clipObj))
							$clipObj->showClip();
						?>

					</td>
					<?
					if (is_object($clipObj))  {
						print $formObj->getFormEnd();
					}
				}
			?>
				</tr>
			</table>
		</td>
	</tr>
<?
}
?>	<tr>
		<td class="blank">&nbsp;
		</td>
	</tr>
</table>
<?
$resources_data = serialize($resources_data);
page_close();
?>

