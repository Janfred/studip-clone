<?
/**
* show_log.inc.php
*
* Stud.IP event log display functions.
*
*
* @author               Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
* @version              $Id$
* @access               public
* @package              studip_core
* @modulegroup          library
* @module               logging
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>,
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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



function get_log_action($action_id) {
	static $actions=array();
	if ($actions[$action_id]) {
		return $actions[$action_id];
	}
	$db=new DB_Seminar;
	$db->query("SELECT * FROM log_actions WHERE action_id='$action_id'");
	if ($db->next_record()) {
		$res=array("name"=>$db->f('name'),"info_template"=>$db->f('info_template'));
		$actions[$action_id]=$res;
		return $res;
	}
	return array("name"=>"unknown","info_template"=>"Error: unknown action");
}

function start_form() {
	global $PHP_SELF;
	print "<form action=$PHP_SELF method=POST>\n";
}

function end_form($from) {
	print "<input type=\"hidden\" name=\"from\" value=\"$from\">\n";
	print "</form>";
}

function showlog_search_form($actionfilter, $searchmode, $objecttype, $objs, $searchobject, $object) {
//print "<p>showlog_search_from($actionfilter, $searchmode, $objecttype, $objs, $searchobjects)";
	$db=new DB_Seminar;
	$db->query("SELECT action_id, description, SUBSTRING_INDEX(name, '_', 1) AS log_group FROM log_actions ORDER BY log_group, description");
	$options=array(array("val"=>"all","text"=>_("Alle Aktionen"),"group"=>NULL));
	while($db->next_record()) {
		$options[]=array("val"=>$db->f('action_id'), "text"=>$db->f('description'), "group"=>$db->f('log_group'));
	}
	$table=new Table(array("padding"=>3, "valign"=>"top"));
	echo $table->openRow();
	echo $table->openCell();
	echo "<span style='font-size:10px;'>";
	print "<select style='font-size:10px;' name=\"actionfilter\" size=1>\n";
	foreach ($options as $o) {
		print "<option value=$o[val]";
		if ($actionfilter==$o['val']) {
			print " selected";
		}
		if ($lastgroup !== $o['group']) {
			$lastgroup = $o['group'];
			echo ' style="border-top: 1px solid #cccccc;"';
		}
		echo ">{$o['text']}</option>\n";
	}
	print "</select>";
	echo "</span>";
	if ($searchmode=="search") {
		echo $table->openCell();
		echo "<span style='font-size:10px;'>";
		echo _("für")." &nbsp;";
		echo "<select style='font-size:10px;' name=\"objecttype\" size=1>";
		echo "<option value=\"sem\">"._("Veranstaltung");
		echo "<option value=\"inst\">"._("Einrichtung");
		echo "<option value=\"user\">"._("BenutzerIn");
		//echo "<option value=\"res\">"._("Ressource");
		echo "</select>\n";
		echo "&nbsp;";
		echo "<input type=hidden name=\"searchmode\" value=\"search\">\n";
		echo "<input style='font-size:10px;' size=20 name=\"searchobject\">";
		echo "</span>";
		echo $table->openCell();
		echo "<span style='font-size:10px;'>";
		echo "<input type=image src=\"".$GLOBALS['ASSETS_URL']."images/suchen.gif\" name=\"search\">";
		echo "&nbsp;";
		echo "</span>";
	} elseif ($searchmode=='found') {
		echo $table->openCell();
		echo "<span style='font-size:10px;'>";
		echo _("für")." &nbsp;";
		if ($objecttype=='sem') {
			echo _("Veranstaltung");
		}
		if ($objecttype=='res') {
			echo _("Ressource");
		}
		if ($objecttype=='inst') {
			echo _("Einrichtung");
		}
		if ($objecttype=='user') {
			echo _("BenutzerIn");
		}
		echo "&nbsp;";
		echo "<input style=\"font-size:10px;\" type=hidden name=\"searchmode\" value=\"found\">\n";
		echo "<input style=\"font-size:10px;\" type=hidden name=\"objecttype\" value=\"$objecttype\">\n";
		echo "<input style=\"font-size:10px;\" type=hidden name=\"searchobject\" value=\"$searchobject\">\n";
		echo "<select style=\"font-size:10px;\" name=\"object\" size=1>\n";
		foreach ($objs as $o) {
			echo "<option value=\"$o[0]\"";
			if ($o[0]==$object) {
				echo " selected";
			}
			echo ">$o[1]</option>\n";
		}
		echo "</select>";
		echo "</span>";
		echo $table->openCell();
		echo "<input type=image src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" name=\"rewind\" style=\"margin-top:-2px;\">";
	}
	echo $table->openCell();
	echo "<span style='font-size:10px;'>";
	echo "in ";
	echo "<select name=\"showlogmode\" style='font-size:10px;'><option value=\"simple\">Kompaktdarstellung</option><option value=\"details\">Detaildarstellung</option></select>";
	echo "</span>";
	echo $table->openCell();
	print "<input type=image ".makeButton("anzeigen","src")." valign=bottom border=0>";
	echo $table->close();
}

function showlog_format_time($ts) {
	if (!$ts) {
		return "invalid";
	} else {
		return date('d.m.Y',$ts)."&nbsp;".date('H:i:s',$ts);
	}
}

function showlog_format_resource($res_id) {
	$ret="";
	$resObj =& ResourceObject::Factory($res_id);
	if ($resObj->getName())
		$ret .= $resObj->getFormattedLink();
	else
		$ret .= $resid;
	return $ret;
}
function showlog_format_username($uid) {
	$uname=get_username($uid);
	if ($uname) {
		return "<a href=\"new_user_md5.php?details=$uname\">".htmlReady(get_fullname($uid))."</a>";
	} else {
		return $uid;
	}
}

function showlog_format_sem($sem_id, $maxlen=100) {
	$db=new DB_Seminar();
	$q="SELECT seminare.Name as title, seminare.VeranstaltungsNummer as number, semester_data.name as semester FROM seminare LEFT JOIN semester_data ON (seminare.start_time=semester_data.beginn) WHERE Seminar_id='$sem_id'";
	$db->query($q);
	if ($db->next_record()) {
		$title=htmlReady(my_substr($db->f('title'),0,$maxlen));
		return "<a href=\"adminarea_start.php?select_sem_id=$sem_id\">".$db->f('number')." ".$title." (".$db->f('semester').")</a>";
	} else {
		return $sem_id;
	}
}

function showlog_format_institute($inst_id, $maxlen=100) {
	$db=new DB_Seminar();
	$q="SELECT Institute.Name as title FROM Institute WHERE Institut_id='$inst_id'";
	$db->query($q);
	if ($db->next_record()) {
		$title=htmlReady(my_substr($db->f('title'),0,$maxlen));
		return "<a href=\"institut_main.php?auswahl=$inst_id\">".$title."</a>";
	} else {
		return $inst_id;
	}
}



function showlog_format_studyarea($area_id) {
	$db=new DB_Seminar();
	$q="SELECT parent_id, sem_tree.name as name, Institute.Name as iname FROM sem_tree LEFT JOIN Institute ON (sem_tree.studip_object_id=Institute.Institut_id) WHERE sem_tree_id='%s'";
	$db->query(sprintf($q,$area_id));
	if ($db->next_record()) {
		$path=array($db->f('name'));
		while ($db->f('parent_id')!="root") {
			$db->query(sprintf($q,$db->f('parent_id')));
			if ($db->next_record()) {
				if (!$db->f('name')) {
					$path[]=htmlReady($db->f('iname'));
				} else {
					$path[]=htmlReady($db->f('name'));
				}
			} else {
				break; // ERROR
			}
		}
		$path=array_reverse($path);
		return "<em>".implode(" &gt; ",$path)."</em>";
	} else {
		return $area_id;
	}
}

function showlog_format_singledate($sd_id) {
	require_once('lib/raumzeit/SingleDate.class.php');
	$termin = new SingleDate($sd_id);
	return '<em>'.$termin->toString().'</em>';
}

function showlog_format_plugin($plugin_id) {
	$pe = PluginEngine::getPluginPersistence();
	$plugin = $pe->getplugin($plugin_id);
	
	return '<em>'.$plugin->pluginname.'</em>';
}

function showlog_format_semester($sem_start_time) {
	require_once('lib/classes/SemesterData.class.php');
	$semester = new SemesterData();
	$all_semester = $semester->getAllSemesterData();
	foreach ($all_semester as $val) {
		if ($val['beginn'] == $sem_start_time) {
			return '<em>'.$val['name'].'</em>';
		}
	}
	return $sem_start_time;
}

function showlog_format_infotemplate($action, $user_id, $affected, $coaffected, $info, $dbg_info) {
	$info = htmlReady($info);
	$dbg_info = htmlReady($dbg_info);
	$text=$action['info_template'];
	$text=preg_replace('/%sem\(%affected\)/',showlog_format_sem($affected),$text);
	$text=preg_replace('/%sem\(%coaffected\)/',showlog_format_sem($coaffected),$text);
	$text=preg_replace('/%studyarea\(%affected\)/',showlog_format_studyarea($affected),$text);
	$text=preg_replace('/%studyarea\(%coaffected\)/',showlog_format_studyarea($coaffected),$text);
	$text=preg_replace('/%res\(%affected\)/',showlog_format_resource($affected),$text);
	$text=preg_replace('/%res\(%coaffected\)/',showlog_format_resource($coaffected),$text);
	$text=preg_replace('/%inst\(%affected\)/',showlog_format_institute($affected),$text);
	$text=preg_replace('/%inst\(%coaffected\)/',showlog_format_institute($coaffected),$text);
	$text=preg_replace('/%user\(%affected\)/',showlog_format_username($affected),$text);
	$text=preg_replace('/%user\(%coaffected\)/',showlog_format_username($coaffected),$text);
	$text=preg_replace('/%user/',showlog_format_username($user_id),$text);
	$text=preg_replace('/%singledate\(%affected\)/',showlog_format_singledate($affected),$text);
	$text=preg_replace('/%semester\(%coaffected\)/',showlog_format_semester($coaffected),$text);
	$text=preg_replace('/%affected/',$affected,$text);
	$text=preg_replace('/%coaffected/',$coaffected,$text);
	$text=preg_replace('/%info/',$info,$text);
	$text=preg_replace('/%dbg_info/',$dbg_info,$text);
	$text=preg_replace("/%plugin\($coaffected\)/",showlog_format_plugin($coaffected),$text);
	return $text;
}

function showlog_entries($from, $mode, $actionfilter, $searchmode, $object) {
	$table=new ZebraTable(array("bgcolor"=>"#eeeeee", "align"=>"center", "width"=>"100%", "padding"=>"4"));
	$table->setCellVAlign("top");
	echo $table->open();
	echo $table->openRow();
	echo $table->cell("<b>Zeit</b>");
	echo $table->cell("<b>Info</b>");
	echo $table->closeRow();

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	if ($actionfilter!="all" && preg_match("/[0-9]+/",$actionfilter)) {
		$add="AND action_id='$actionfilter' ";
	} else {
		$add="";
	}
	if ($searchmode=='found' && $object) {
		$add.="AND (affected_range_id='$object' OR coaffected_range_id='$object') ";
	}

	$q="SELECT COUNT(*) as c FROM log_events WHERE 1 $add ORDER BY mkdate";
	$db->query($q);
	$db->next_record();
	$numentries=$db->f("c");
	if ($from>$numentries) {
		$from = max(0,$numentries-50);
	}

	$q="SELECT * FROM log_events WHERE 1 $add ORDER BY mkdate DESC, event_id DESC LIMIT $from,50";
	$db->query($q);

	while ($db->next_record()) {
		$action=get_log_action($db->f('action_id'));
		echo $table->openRow();
		echo $table->cell("<font size=-1>".showlog_format_time($db->f('mkdate'))."</font>");
		echo $table->openCell();
		// if ($mode=='details') echo "<font size=-1>[#".$db->f('event_id')."]</font> "; // show action_id in detail mode
		echo "<font size=-1>".showlog_format_infotemplate($action,$db->f('user_id'),$db->f('affected_range_id'),$db->f('coaffected_range_id'),$db->f('info'),$db->f('dbg_info'))."</font>";
		if ($mode=='details') {
			if ($db->f('info')) {
				echo "<br><font size=-1>Info: ".stripslashes($db->f('info'))."</font>";
			}
			if ($db->f('dbg_info')) {
				echo "<br><font size=-1>Debug: ".stripslashes($db->f('dbg_info'))."</font>";
			}
		}
		echo $table->closeRow();
	}

	//echo $table->closeRow();
	echo $table->close();
	echo "<p>&nbsp;<br><font size=-1>"
		. sprintf(_("Eintrag %s - %s von %s. "),$from,min($from+50-1,$numentries),$numentries)
		. "</font>";
	if ($from>0) {
		echo "<input type=image name=\"zurueck\" ".makeButton("zurueck","src")."> ";
	}
	if ($from+50 < $numentries) {
		echo "<input type=image name=\"weiter\" ".makeButton("weiter","src").">";
	echo "</p>";
	}
}

function showlog_search_seminar($needle) {
	$db=new DB_Seminar();
	// search for active seminars
	$q="SELECT Seminar_id FROM seminare WHERE VeranstaltungsNummer like '%$needle%' OR Name like '%$needle%'";
	$db->query($q);
	$sems=array();
	while ($db->next_record()) {
		$sems[]=array($db->f("Seminar_id"),showlog_format_sem($db->f("Seminar_id"),30));
	}
	// search deleted seminars
	// SemName and Number is part of info field, old id (still in DB) is in affected column
	$q="SELECT * FROM log_events LEFT JOIN log_actions ON (log_actions.action_id=log_events.action_id) WHERE info LIKE '%$needle%' AND (log_actions.name='SEM_ARCHIVE' OR log_actions.name='SEM_DELETE_FROM_ARCHIVE')";
	$db->query($q);
	while ($db->next_record()) {
		$sems[]=array($db->f("affected_range_id"),($db->f("info")." ("._("gelöscht").")"));
	}

	return $sems;
}

function showlog_search_inst($needle) {
	$db=new DB_Seminar();
	$q="SELECT Institut_id, Name FROM Institute WHERE Name like '%$needle%'";
	$db->query($q);
	$sems=array();
	while ($db->next_record()) {
		$sems[]=array($db->f("Institut_id"),my_substr($db->f('Name'),0,28));
	}

	// search for deleted seminars
	// InstName is part of info field, old id (still in DB) is in affected column
	$q="SELECT * FROM log_events LEFT JOIN log_actions ON (log_actions.action_id=log_events.action_id) WHERE info LIKE '%$needle%' AND (log_actions.name='INST_DEL')";
	$db->query($q);
	while ($db->next_record()) {
		$sems[]=array($db->f("affected_range_id"),($db->f("info")." ("._("gelöscht").")"));
	}

	return $sems;
}

function showlog_search_user($needle) {
	global $_fullname_sql;
	$db=new DB_Seminar();
	$q="SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE Nachname LIKE '%$needle%' OR Vorname LIKE '%$needle%' OR username LIKE '%$needle%'";
	$db->query($q);
	$users=array();
	while ($db->next_record()) {
		$users[]=array($db->f("user_id"),my_substr($db->f('fullname'),0,20)." (".$db->f("username").")");
	}

	// search for deleted users
	// InstName is part of info field, old id (still in DB) is in affected column
	$q="SELECT * FROM log_events LEFT JOIN log_actions ON (log_actions.action_id=log_events.action_id) WHERE info LIKE '%$needle%' AND (log_actions.name='USER_DEL')";
	$db->query($q);
	while ($db->next_record()) {
		$users[]=array($db->f("affected_range_id"),($db->f("info")." ("._("gelöscht").")"));
	}

	return $users;
}



function showlog_search_resource($needle) {
	$db=new DB_Seminar();
	$q="SELECT resource_id FROM resources_objects WHERE name like '%$needle%'";
	$db->query($q);
	$sems=array();
	while ($db->next_record()) {
		$sems[]=array($db->f("resource_id"),showlog_format_resource($db->f("resource_id"),30));
	}
	return $sems;
}

