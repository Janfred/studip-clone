<?
/*
sem_browse.inc.php - Universeller Seminarbrowser zum Includen, Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

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

//Settings for the Script

/*If you want to switch the colors of the header of each different group
(e.g. Semester or Dozent) set this to TRUE. If it it not set, the color is
always a light red */
$sem_browse_switch_headers=FALSE;

//includes
require_once "config.inc.php";
require_once "config_tools_semester.inc.php";
require_once "dates.inc.php";
require_once "visual.inc.php";
require_once "functions.php";
require_once ("lib/classes/StudipSemSearch.class.php");
require_once ("lib/classes/StudipSemTreeViewSimple.class.php");
require_once ("lib/classes/StudipSemRangeTreeViewSimple.class.php");


//init classes
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$cssSw=new cssClassSwitcher; // Klasse für Zebra-Design
$cssSw->enableHover();
$search_obj = new StudipSemSearch("search_sem", false);


$sess->register("sem_browse_data");
$sess->register("_marked_sem");
//Alle frisch reingekommenen Variablen in Sessionvariable uebernehmen

if ($s_range) $sem_browse_data["s_range"]=$s_range;
if ($id) $sem_browse_data["id"]=$id;
if ($oid) $sem_browse_data["oid"]=$oid;
if ($oid2) $sem_browse_data["oid2"]=$oid2;
if ($extern) $sem_browse_data["extern"]=$extern;
if ($extend) $sem_browse_data["extend"]=$extend;
if ($sset) $sem_browse_data["sset"]=$sset;
if ($cmd) $sem_browse_data["cmd"]=$cmd;
if ($level) $sem_browse_data["level"]=$level;
if ($group_by) $sem_browse_data["group_by"]=$group_by;
if ($start_item_id) $sem_browse_data["start_item_id"] = $start_item_id;

if (isset($_REQUEST[$search_obj->form_name . "_scope_choose"])){
	$sem_browse_data["start_item_id"] = $_REQUEST[$search_obj->form_name . "_scope_choose"];
}
if (isset($_REQUEST[$search_obj->form_name . "_range_choose"])){
	$sem_browse_data["start_item_id"] = $_REQUEST[$search_obj->form_name . "_range_choose"];
}

if (isset($_REQUEST[$search_obj->form_name . "_sem"])){
	$sem_browse_data['default_sem'] = $_REQUEST[$search_obj->form_name . "_sem"];
} elseif (!isset($sem_browse_data['default_sem'])){
	 $sem_browse_data['default_sem'] = get_sem_num_sem_browse();
}
if ((!$sem_browse_data["cmd"]) && ($root_mode)){
	$sem_browse_data["cmd"]="xts";
}
if ($sem_browse_data["cmd"] == "xts"){
	$sem_browse_data['level'] = 'f';
}
//default group_by mode
if (!$sem_browse_data["group_by"])
$sem_browse_data["group_by"]="semester";

if (($i_page=="show_bereich.php") && (!$extern))
	$sem_browse_data["extern"]=TRUE;
elseif (!$extern)
	$sem_browse_data["extern"]=FALSE;
//Zuruecksetzen
if ($search_obj->new_search_button_clicked){
	unset($level);
	$reset_all = true;
}

if ($reset_all){
	$tmp_cmd = $sem_browse_data["cmd"];
	$sem_browse_data = array();
	$sem_browse_data["cmd"] = $tmp_cmd;
	$sem_browse_data["default_sem"] = get_sem_num_sem_browse();
	$_marked_sem = array();
}

if($sem_browse_data["default_sem"] != 'all'){
	$default_sems[0] = $sem_browse_data["default_sem"];
}
if ($sem_browse_data['cmd'] != 'xts'){
	unset($default_sems);
}


if ($level==0) $sem_browse_data["extern"] == FALSE;
//Zuruecksetzen

if (!isset ($sem_browse_data["level"])) {
	$sem_browse_data["level"]="f";
}

if ($sem_browse_data['level'] == "vv" || $level=="vv"){
	$sem_tree = new StudipSemTreeViewSimple($sem_browse_data["start_item_id"], ((!is_array($default_sems)) ? false : $default_sems));
	$sem_browse_data['cmd'] = "qs";
	if ($level == "vv"){
		$_open_items = null;
		$sem_browse_data['level'] = "vv";
	}
}

if ($sem_browse_data['level'] == "ev" || $level=="ev"){
	$sem_number = (!is_array($default_sems)) ? false : $default_sems;
	$sem_status = (is_array($_sem_status)) ? $_sem_status : false;
	$range_tree = new StudipSemRangeTreeViewSimple($sem_browse_data["start_item_id"],$sem_number,$sem_status);
	$sem_browse_data['cmd'] = "qs";
	if ($level == "ev"){
		$_open_items = null;
		$sem_browse_data['level'] = "ev";
	}
}

if ($_REQUEST['cmd'] == "show_sem_range"){
	$args = null;
	if ($sem_browse_data['default_sem'] != 'all'){
		$args = array('sem_number' => $default_sems);
	}
	$the_tree =& TreeAbstract::GetInstance("StudipSemTree",$args);
	$tmp = explode("_",$_REQUEST['item_id']);
	$item_id = $tmp[0];
	$with_kids = isset($tmp[1]);
	$sem_ids = $the_tree->getSemIds($item_id,$with_kids);
	if (is_array($sem_ids)){
		$_marked_sem = array_flip($sem_ids);
	} else {
		$_marked_sem = array();
	}
	$sem_browse_data['sset'] = true;
}

if ($_REQUEST['cmd'] == "show_sem_range_tree"){
	$tmp = explode("_",$_REQUEST['item_id']);
	$item_id = $tmp[0];
	$range_object =& RangeTreeObject::GetInstance($item_id);
	if (isset($tmp[1])){
		$inst_ids = $range_object->getAllObjectKids();
	}
	$inst_ids[] = $range_object->item_data['studip_object_id'];
	$db_view = new DbView();
	$db_view->params[0] = $inst_ids;
	$db_view->params[1] = (is_array($_sem_status)) ? " AND c.status IN('" . join("','",$_sem_status) ."')" : "";
	$db_view->params[2] = (is_array($default_sems)) ? " HAVING sem_number IN (" . join(",",$default_sems) .")" : "";
	$db_snap = new DbSnapshot($db_view->get_query("view:SEM_INST_GET_SEM"));
	if ($db_snap->numRows){
		$sem_ids = $db_snap->getRows("Seminar_id");
		$_marked_sem = array_flip($sem_ids);
	} else {
		$_marked_sem = array();
	}
	$sem_browse_data['sset'] = true;
}

if ($_REQUEST['cmd'] == "show_class"){
	$db->query("SELECT Seminar_id from seminare WHERE seminare.visible='1' AND seminare.status IN ('" . join("','", $_sem_status) . "')"); // OK_VISIBLE
	$snap = new DbSnapshot($db);
	$sem_ids = $snap->getRows("Seminar_id");
	if (is_array($sem_ids)){
		$_marked_sem = array_flip($sem_ids);
	}
	$sem_browse_data['sset'] = true;
	$sem_browse_data['cmd'] = "qs";
}

//We want to show the search forms only in non-browsing mode
if ((!$sem_browse_data["extern"]) ) {

	//Quicksort Formular... fuer die eiligen oder die DAUs....
	if (($sem_browse_data["cmd"]=="qs") || ($sem_browse_data["cmd"]=="") || (!isset($sem_browse_data["cmd"]))) {
		$search_obj->search_fields['qs_choose']['content'] = array('title' => _("Titel"), 'lecturer' => _("DozentIn"), 'comment' => _("Kommentar"));
		$search_obj->search_fields['type']['class'] = ($show_class) ? $show_class : "all";
		echo "<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
		echo $search_obj->getFormStart("$PHP_SELF?send=yes");
		echo "<tr><td height=\"40\" class=\"steel1\" align=\"center\" valign=\"middle\" ><font size=\"-1\">";
		echo _("Schnellsuche:") . "&nbsp;";
		echo $search_obj->getSearchField("qs_choose",array('style' => 'vertical-align:middle;font-size:9pt;'));
		if ($sem_browse_data['level'] == "vv"){
			$search_obj->sem_tree =& $sem_tree->tree;
			if ($sem_tree->start_item_id != 'root'){
				$search_obj->search_scopes[] = $sem_tree->start_item_id;
			}
			echo "&nbsp;" . _("in:") . "&nbsp;" . $search_obj->getSearchField("scope_choose",array('style' => 'vertical-align:middle;font-size:9pt;'),$sem_tree->start_item_id);
			echo "\n<input type=\"hidden\" name=\"level\" value=\"vv\">";
		}
		if ($sem_browse_data['level'] == "ev"){
			$search_obj->range_tree =& $range_tree->tree;
			if ($range_tree->start_item_id != 'root'){
				$search_obj->search_ranges[] = $range_tree->start_item_id;
			}
			echo "&nbsp;" . _("in:") . "&nbsp;" . $search_obj->getSearchField("range_choose",array('style' => 'vertical-align:middle;font-size:9pt;'),$range_tree->start_item_id);
			echo "\n<input type=\"hidden\" name=\"level\" value=\"ev\">";
		}
		echo "&nbsp;";

		echo $search_obj->getSearchField("quick_search",array( 'style' => 'vertical-align:middle;font-size:9pt;','size' => 20));
		echo $search_obj->getSearchButton(array('style' => 'vertical-align:middle'));
		//echo $search_obj->getNewSearchButton(array('style' => 'vertical-align:middle'), _("Suchergebnis löschen und neue Suche starten"));
		//echo "&nbsp;<a href=\"$PHP_SELF?cmd=xts";
		//echo "\"><img align=\"middle\" " . makeButton("erweitertesuche","src") . tooltip(_("Erweitertes Suchformular aufrufen")) ." border=\"0\"></a>";

		echo "</td></tr>";
		echo $search_obj->getFormEnd();
		echo "</table>\n";
	}

	//Extended Sortformular, fuer Leute mit mehr GRiPS...
	if (($sem_browse_data["cmd"]=="xts"))
	{
		$search_obj->attributes_default = array('style' => 'width:100%;font-size:10pt;');
		$search_obj->search_fields['type']['class'] = ($show_class) ? $show_class : "all";
		$search_obj->search_fields['type']['size'] = 40 ;
		echo $search_obj->getFormStart("$PHP_SELF?send=yes");
		echo "<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Titel:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("title");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Typ:") . "</td><td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("type",array('style' => 'width:*;font-size:10pt;'));
		echo "</td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Untertitel:") . " </td>";
		echo "<td  class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("sub_title");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Semester:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("sem",array('style' => 'width:*;font-size:10pt;'),$sem_browse_data['default_sem']);
		echo "</td></tr>";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Kommentar:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("comment");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">&nbsp;</td><td class=\"steel1\" align=\"left\" width=\"35%\">&nbsp; </td></tr>\n";
		echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("DozentIn:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("lecturer");
		echo "</td><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Verkn&uuml;pfung:") . " </td>";
		echo "<td class=\"steel1\" align=\"left\" width=\"35%\">";
		echo $search_obj->getSearchField("combination",array('style' => 'width:*;font-size:10pt;'));
		echo "</td></tr>\n";
		$tmp_cs=4;
		if (!$hide_bereich) {
			$tmp_cs=2;
			echo "<tr><td class=\"steel1\" align=\"right\" width=\"15%\">" . _("Bereich:") . " </td>";
			echo "<td class=\"steel1\" align=\"left\" width\"35%\">";
			echo $search_obj->getSearchField("scope");
			echo "</td><td class=\"steel1\" colspan=\"2\">&nbsp</td></tr>";
		}
		echo "<tr><td class=\"steel1\">&nbsp</td><td class=\"steel1\" align=\"left\">";
		echo $search_obj->getSearchButton();
		echo "&nbsp;";
		echo $search_obj->getNewSearchButton();
		echo "</td><td class=\"steel1\">&nbsp;</td><td class=\"steel1\"><a href=\"$PHP_SELF?cmd=qs&level=f";
		echo  "\"><img " . makeButton("schnellsuche", "src") . tooltip(_("Zur Schnellsuche zurückgehen")) ." border=0></a></td></tr>\n";
		echo "<tr><td class=\"steel1\" colspan=4 align=\"center\"></td></tr>";
		echo $search_obj->getFormEnd();
		echo "</table>\n";
	}

	//header to reset (start a new) search
}
/*if (!$sem_browse_data["extern"]) {
	echo "<form action=\"$PHP_SELF\" method=\"POST\"><table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
	echo "<tr><td class=\"steel1\" align=\"center\">";
	echo "<a href=\"$PHP_SELF?reset_all=true\">".makeButton("neuesuche")."</a>\n";
	echo "</table>\n";
} else {
	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
}
*/
//Parser zur Auswertung des Suchstrings


//Expressions for grouping
switch ($sem_browse_data["group_by"]) {
	case "einrichtung":
	$order_by_exp="Institut ASC";
	break;
	case "bereich":
	$order_by_exp="bereich ASC";
	break;
	case "typ":
	$order_by_exp="status ASC";
	break;
	case "dozent":
	$order_by_exp="Nachname ASC";
	break;
	case "semester":
	$order_by_exp="start_time DESC";
	break;
	default:
	$order_by_exp="start_time DESC";
	break;
}

//calculate colspans
$rows = ($sem_browse_data["extend"] == "yes") ? 8 : 5;
$rightspan = ($sem_browse_data["extend"] == "yes") ? 3 : 2;
$leftspan = $rows-$rightspan;
if (($sem_browse_data["group_by"] == "einrichtung" || $sem_browse_data["group_by"] == "semester" || $sem_browse_data["group_by"] == "dozent")
|| ($sem_browse_data["group_by"] == "einrichtung" && $sem_browse_data["extend"] == "yes")) {
	$leftspan--;
	$rows--;
}

ob_start(); //Outputbuffering start


if ($search_obj->search_button_clicked){
	$search_obj->override_sem = $default_sems;
	$search_obj->doSearch();
	if ($search_obj->found_rows){
		$_marked_sem = $search_obj->search_result->getDistinctRows("seminar_id");
	} else {
		$_marked_sem = array();
	}
	$sem_browse_data['sset'] = true;
	$sem_browse_data["level"]="s";
}

//Anzeige des Suchergebnis (=Seminarebene)
if ($sem_browse_data['level'] != 'f' && ($sem_browse_data["level"]=="s" || $sem_browse_data['sset'])) {
	$sem_browse_data["level"] = "s";
	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n<table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
	if (is_array($_marked_sem) && count($_marked_sem)) {
		$query = ("SELECT seminare.Seminar_id, seminare.status, seminare.Name,
			seminare.Schreibzugriff, seminare.Lesezugriff , Institute.Name AS Institut,Institute.Institut_id,
			seminar_sem_tree.sem_tree_id AS bereich, " . $_fullname_sql['full_rev'] ." AS fullname, auth_user_md5.username,
			" . $_views['sem_number_sql'] . " AS sem_number FROM seminare
			LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent')
			LEFT JOIN auth_user_md5 USING (user_id)
			LEFT JOIN user_info USING (user_id)
			LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id)
			LEFT JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.Seminar_id)
			LEFT JOIN Institute USING (Institut_id)
			WHERE seminare.visible='1' AND seminare.Seminar_id IN('" . join("','", array_keys($_marked_sem)) . "') ORDER BY $order_by_exp, seminare.Name "); // OK_VISIBLE

		$db->query($query);
		$snap = new DbSnapShot($db);
		if ($sem_browse_data["extend"] == "yes"){
			//Meinen Status ermitteln
			$db2->query("SELECT status FROM seminar_user WHERE Seminar_id IN('" . join("','", array_keys($_marked_sem)) . "') AND user_id = '{$auth->auth['uid']}'");
			while ($db2->next_record()){
				$my_status[$db2->f("Seminar_id")] = $db2->f("status");
			}
		}
		if (($sem_browse_data["sset"]) || ($sem_browse_data["extern"])) {
			printf ("<tr><td nowrap class=\"steel1\" colspan=\"%s\">", $leftspan);
			//Change/view the group method
			print ("<font size=-1><b>" . _("Gruppierung:") . "</b>&nbsp;<select name=\"group_by\" style=\"vertical-align:middle;\">");
			printf ("<option %s value=\"semester\">" . _("Semester") . "</option>", (($sem_browse_data["group_by"]=="semester") || (!$sem_browse_data["group_by"])) ? "selected" : "");
			printf ("<option %s value=\"bereich\">" . _("Bereich") . "</option>", ($sem_browse_data["group_by"]=="bereich") ? "selected" : "");
			printf ("<option %s value=\"dozent\">" . _("DozentIn") . "</option>", ($sem_browse_data["group_by"]=="dozent") ? "selected" : "");
			printf ("<option %s value=\"typ\">" . _("Typ") . "</option>", ($sem_browse_data["group_by"]=="typ") ? "selected" : "");
			printf ("<option %s value=\"einrichtung\">" . _("Einrichtung") . "</option>", ($sem_browse_data["group_by"]=="einrichtung") ? "selected" : "");
			print ("</select>&nbsp; <input border=\"0\" style=\"vertical-align:middle;\" type=\"IMAGE\" " . makeButton("uebernehmen","src") . tooltip(_("Anzeigeoptionen übernehmen")) . "/></font>");
			print("&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <font size=-1>&nbsp;");
			printf(_("<b>%s</b> Veranstaltungen gefunden."),count($_marked_sem));
			print ("</font> </td>");
			//Show how many items were found
			printf ("<td class=\"steel1\" nowrap align=\"right\" colspan=%s>", $rightspan);
			echo"<a href=\"" . $PHP_SELF;
			if ($sem_browse_data["extend"] != "yes") {
				echo "?extend=yes\"><img align=\"middle\" " . makeButton("erweiterteansicht","src") . tooltip(_("Zur erweiterten Ansicht umschalten")) ." border=0>";
			} else {
				echo "?extend=no\"><img align=\"middle\" " . makeButton("normaleansicht", "src") . tooltip(_("Zur normalen Ansicht umschalten")) ."  border=0>";
			}
			echo "</a></font></td></tr>";
		}

		ob_end_flush();
		ob_start();

		//init the cols
		if ($sem_browse_data["extend"]=="yes") {
			?>
			<colgroup>
			<col width="30%">
			<col width="15%">
			<col width="10%">
			<col width="15%">
			<?
			if ($sem_browse_data["group_by"] != "semester") print "<col width=\"10%\">";
			?>
			<col width="10%">
			<col width="10%">
			</colgroup>
			<?
		} else {
			?>
			<colgroup>
			<col width="40%">
			<col width="15%">
			<?
			if ($sem_browse_data["group_by"] != "einrichtung") print "<col width=\"20%\">";
			if ($sem_browse_data["group_by"] != "dozent") print "<col width=\"20%\">";
			if ($sem_browse_data["group_by"] != "semester") print "<col width=\"5%\">";
			?>
			</colgroup>
			<?
		}
		?>
		<tr align="center">
		<td class="steel" align="left"><font size="-1">
		<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="1" height="20" valign="top">
		<b><?=_("Name")?></b></font>
		</td>
		<td class="steel" valign="bottom">
		<font size="-1"><b><?=_("Zeit")?></b></font>
		</td>
		<?
		if ($sem_browse_data["group_by"] != "einrichtung") {
			?>
			<td class="steel" valign="bottom">
			<font size="-1"><b><?=_("Einrichtungen")?></b></font>
			</td>
			<?
		}
		if ($sem_browse_data["group_by"] != "dozent") {
			?>
			<td class="steel" valign="bottom">
			<font size="-1"><b><?=_("DozentInnen")?></b></font>
			</td>
			<?
		}
		if ($sem_browse_data["group_by"] != "semester") {
			?>
			<td class="steel" valign="bottom">
			<font size="-1"><b><?=_("Semester")?></b></font>
			</td>
			<?
		}
		if ($sem_browse_data["extend"]=="yes") {
			if ($sem_browse_data["group_by"] != "typ") {
				?>
				<td class="steel" valign="bottom">
				<font size=-1><b><?=_("Typ")?></b></font>
				</td>
				<?
			}
			?>
			<td class="steel" valign="bottom">
			<font size=-1><b>Lesen</b> / <b><font size=-1><?=_("Schreiben")?></b></font>
			</td>
			<td class="steel" valign="bottom">
			<font size=-1><b><?=_("Mein Status")?></b></font>
			</td>
			<?
		}
		echo "</tr>";

	$group=1;
	$data = $snap->getGroupedResult("Seminar_id");
	if ($sem_browse_data["group_by"] == "bereich"){
		$the_tree =& TreeAbstract::GetInstance("StudipSemTree");
	}
	foreach($data as $seminar_id => $value) {
		$repeat = true;
		while($repeat){
			$cssSw->switchClass();
			if ($group==8)
			$group=1;

			//Create the group headers
			switch ($sem_browse_data["group_by"]) {
				case "semester":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_sem"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					if ($group_header_name != $search_obj->sem_dates[key($value['sem_number'])]['name']){
						$group_header_name = $search_obj->sem_dates[key($value['sem_number'])]['name'];
						$group_header_class=$group;
						$group++;
						$print_header_name = true;
					} else {
						$print_header_name = false;
					}
				}
				break;
				case "bereich":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_bereich"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp = $the_tree->getShortPath(key($value['bereich']));
					if ($group_header_name != $tmp){
						$group_header_name = $tmp;
						if ($repeat === true){
							$repeat = count($value['bereich']);
						}
						next($value['bereich']);
						$group_header_class=$group;
						$group++;
						$print_header_name = true;
					} else {
						$print_header_name = false;
						next($value['bereich']);
						unset($tmp);
					}
				}
				break;
				case "typ":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_status"] == "alle")) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					$tmp = $SEM_TYPE[key($value['status'])]["name"]." (". $SEM_CLASS[$SEM_TYPE[key($value['status'])]["class"]]["name"].")";
					if ($group_header_name != $tmp){
						$group_header_name=$tmp;
						$group_header_class=$group;
						$group++;
						$print_header_name = true;
					} else {
						$print_header_name = false;
						unset($tmp);
					}
				}
				break;
				case "dozent":
				if ((($sem_browse_data["cmd"] != "qs") && ($sem_browse_data["s_dozent"])) || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					if ($group_header_name != key($value['fullname'])){
						$group_header_name=key($value['fullname']);
						if ($repeat === true){
							$repeat = count($value['fullname']);
						}
						next($value['fullname']);
						$group_header_class=$group;
						$group++;
						$print_header_name = true;
					} else {
						$print_header_name = false;
						next($value['fullname']);
						}
				}
				break;
				case "einrichtung":
				if (($sem_browse_data["cmd"] != "qs") || ($sem_browse_data["level"] == "s") || ($sem_browse_data["level"] == "sbb")|| ($sem_browse_data["level"]=="b")) {
					if ($group_header_name != key($value['Institut'])){
						$group_header_name = key($value['Institut']);
						if ($repeat === true){
							$repeat = count($value['Institut']);
						}
						next($value['Institut']);
						$group_header_class = $group;
						$group++;
						$print_header_name = true;
					} else {
						$print_header_name = false;
						next($value['Institut']);

					}
				}
				break;
			}

			$repeat = ($repeat === true) ? false : $repeat-1;

			//Put group_by headers
			if ($print_header_name)
			printf ("<tr> <td class=\"steelgroup%s\" colspan=%s><font size=-1><b>&nbsp;%s</b></font></td></tr>", ($sem_browse_switch_headers) ? $group_header_class : "1", $rows, htmlReady($group_header_name));
			//create name-field
			echo"<tr ".$cssSw->getHover()."><font size=-1>";

			//----------------------

			//create Turnus field
			$temp_turnus_string=view_turnus($seminar_id, TRUE);

			//Shorten, if string too long (add link for details.php)
			if (strlen($temp_turnus_string) >70) {
				$temp_turnus_string=substr($temp_turnus_string, 0, strpos(substr($temp_turnus_string, 70, strlen($temp_turnus_string)), ",") +71);
				$temp_turnus_string.="...&nbsp;<a href=\"".$target_url."?".$target_id."=".$seminar_id."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
			}
			echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>".$temp_turnus_string."</font></td>";

			//----------------------

			//create the Einrichtungen Colummn
			if ($sem_browse_data["group_by"] != "einrichtung") {
				$inst_ids = $value["Institut_id"];
				$inst_names = $value["Institut"];
				$einrichtungen ="";
				$i=0;
				while ( $i < 3 ) {
					if ($i < count($inst_ids)){
						if ($i)
						$einrichtungen .= ", ";
						$einrichtungen .= "<a href=\"institut_main.php?auswahl=".key($inst_ids)."\">".htmlReady(key($inst_names))."</a>";
						//more than 2 Einrichtungen are two much, link to the details.php for more info
						if ($i==2)
						$einrichtungen .= ",...&nbsp;<a href=\"".$target_url."?".$target_id."=".$seminar_id."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
						next($inst_ids);
						next($inst_names);
					}
					$i++;
				}
				if ($einrichtungen == "")
				$einrichtungen = "- - -";
				echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>". $einrichtungen . "&nbsp;</font></td>";
			}

			//----------------------

			//create the Dozenten Colummn
			if ($sem_browse_data["group_by"] != "dozent") {
				$doz_uname = $value["username"];
				$doz_fullname = $value["fullname"];
				$dozname ="";
				$i=0;
				while ($i < 4) {
					if ($i < count($doz_uname)){
						if ($i)
						$dozname .= ", ";
						$dozname .= "<a href=\"about.php?username=".key($doz_uname)."\">".htmlReady(key($doz_fullname))."</a>";
						//more than 3 Dozenten are two much, link to the details.php for more info
						if ($i==3)
						$dozname .= ",...&nbsp;<a href=\"".$target_url."?".$target_id."=".$seminar_id."&send_from_search=true&send_from_search_page=$PHP_SELF\">(mehr) </a>";
						next($doz_uname);
						next($doz_fullname);
					}
					$i++;
				}
				if ($dozname == "")
				$dozname = "- - -";
				echo"<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>" . $dozname . "&nbsp;</font></td>";
			}

			//----------------------

			//create the Semester colummn
			if ($sem_browse_data["group_by"] != "semester")
			echo "<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>".$search_obj->sem_dates[key($value["sem_number"])]['name']."</font></td>";

			//----------------------

			//create extended fields
			if ($sem_browse_data["extend"]=="yes") {
				//Typ
				if ($sem_browse_data["group_by"] != "typ")
				echo "<td class=\"".$cssSw->getClass()."\" align=center><font size=-1>", $SEM_TYPE[key($value["status"])]["name"]." <br>(Kategorie ", $SEM_CLASS[$SEM_TYPE[key($value["status"])]["class"]]["name"],")</font></td>";

				$mein_status = $my_status[$seminar_id];

				//Ampel-Schaltung
				if ($mein_status) { // wenn ich im Seminar schon drin bin, darf ich auf jeden Fall lesen
					echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
				} else {
					switch(key($value["Lesezugriff"])){
						case 0 :
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
						break;
						case 1 :
						if ($perm->have_perm("autor"))
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
						break;
						case 2 :
						if ($perm->have_perm("autor"))
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
						break;
						case 3:
						if ($perm->have_perm("autor"))
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp; ";
						else
						echo"<td class=\"".$cssSw->getClass()."\" align=center><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp; ";
					}
				}

				if ($mein_status == "dozent" || $mein_status == "tutor" || $mein_status == "autor") { // in den Fällen darf ich auf jeden Fall schreiben
					echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
				} else {
					switch(key($value["Schreibzugriff"])){
						case 0 :
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
						break;
						case 1 :
						if ($perm->have_perm("autor"))
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gruen.gif\" width=\"11\" height=\"16\"></td>";
						else
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
						break;
						case 2 :
						if ($perm->have_perm("autor"))
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gelb.gif\" width=\"11\" height=\"16\"></td>";
						else
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
						break;
						case 3:
						if ($perm->have_perm("autor"))
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_gelb.gif\" width=\"11\" height=\"16\"></td>";
						else
						echo"<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_rot.gif\" width=\"11\" height=\"16\"></td>";
					}
				}
				echo "<td class=\"".$cssSw->getClass()."\" align=\"center\"><font size=-1>";
				//Meinen Status ausgeben
				if ($mein_status) {
					echo $mein_status;
				} else {
					echo "&nbsp;";
				}
				echo "</font></td>";
			}
			echo"</tr>";
		}
	}
	echo"</font></td></tr><tr><td class=\"blank\">&nbsp;<br></td></tr>";
	ob_end_flush();
	} elseif ($search_obj->search_button_clicked) {
		echo "<tr><td class=\"blank\" colspan=2><font size=-1><b>" . _("Es wurden keine Veranstaltungen gefunden.");
		if ($search_obj->found_rows === false){
			echo "<br>" . _("(Der Suchbegriff fehlt oder ist zu kurz)");
		}
		echo "</b></font></td></tr>";
	}
echo "</form></table>";
} elseif ($sem_browse_data['level'] == "f"){
	echo "\n<table border=\"0\" align=\"center\" cellspacing=0 cellpadding=2 width = \"99%\">\n";
	echo "\n<tr><td align=\"center\" class=\"steelgraulight\" height=\"40\" valign=\"middle\">";
	printf(_("Suche im %sEinrichtungsverzeichnis%s"),"<a href=\"$PHP_SELF?level=ev&cmd=qs\">","</a>");
	if (!$hide_bereich){
		printf(_(" / %sVorlesungsverzeichnis%s"),"<a href=\"$PHP_SELF?level=vv&cmd=qs&view=1&reset_all=1\">","</a>");
	}
	echo "</td></tr>\n</table>";
}
if ($sem_browse_data['level'] == "vv"){
	echo "\n<table border=0 align=\"center\" cellspacing=0 cellpadding=0 width = \"99%\">\n";
	echo "\n<tr><td align=\"center\">";
	$sem_tree->showSemTree();
	echo "</td></tr>";
	echo "\n</table>";
}
if ($sem_browse_data['level'] == "ev"){
	echo "\n<table border=0 align=\"center\" cellspacing=0 cellpadding=0 width = \"99%\">\n";
	echo "\n<tr><td align=\"center\">";
	$range_tree->showSemRangeTree();
	echo "</td></tr>";
	echo "\n</table>";
}

?>
