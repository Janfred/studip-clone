<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("admin");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/dbviews/literatur.view.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitCatElement.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitSearch.class.php");

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   //hier wird der "Kopf" nachgeladen
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins

function my_session_var($var, $id = false){
	if (!$id){
		$id = md5($GLOBALS['PHP_SELF']);
	}
	if (!$GLOBALS['sess']->is_registered($id)){
		$GLOBALS['sess']->register($id);
	}
	if (is_array($var)){
		foreach ($var as $name){
			if (isset($_REQUEST[$name])){
				$GLOBALS[$id][$name] = $_REQUEST[$name];
			} else {
				$_REQUEST[$name] = $GLOBALS[$id][$name];
			}
			$GLOBALS[$name] =& $GLOBALS[$id][$name];
		}
	} else {
		if (isset($_REQUEST[$var])){
			$GLOBALS[$id][$var] = $_REQUEST[$var];
		} else {
			$_REQUEST[$var] = $GLOBALS[$id][$var];
		}
		$GLOBALS[$var] =& $GLOBALS[$id][$var];
	}
}

my_session_var(array('_semester_id','_inst_id','_anker_id','_open','_lit_data','_lit_data_id'));

$db = new DB_Seminar();
$db2 = new DB_Seminar();
$_semester = new SemesterData();
$element = new StudipLitCatElement();



if (isset($_REQUEST['open_element'])){
	$_open[$_REQUEST['open_element']] = true;
	$_anker_id = $_REQUEST['open_element'];
}
if (isset($_REQUEST['close_element'])){
	unset($_open[$_REQUEST['close_element']]);
	$_anker_id = $_REQUEST['close_element'];
}
if (isset($_GET['_catalog_id'])){
	$_anker_id = $_GET['_catalog_id'];
}

if (isset($_REQUEST['_semester_id']) && $_REQUEST['_semester_id'] != 'all'){
	$_sem_sql = "  LEFT JOIN seminare s ON (c.seminar_id=s.Seminar_id)
				LEFT JOIN semester_data sd
				ON (( s.start_time <= sd.beginn AND sd.beginn <= ( s.start_time + s.duration_time )
				OR ( s.start_time <= sd.beginn AND s.duration_time =  - 1 )) AND semester_id='" . $_REQUEST['_semester_id'] . "') 
				LEFT JOIN lit_list d ON (s.Seminar_id = d.range_id AND semester_id IS NOT NULL)";
	$_sem_sql2 = "INNER JOIN semester_data sd
				ON (( s.start_time <= sd.beginn AND sd.beginn <= ( s.start_time + s.duration_time )
				OR ( s.start_time <= sd.beginn AND s.duration_time =  - 1 )) AND semester_id='" . $_REQUEST['_semester_id'] . "') ";
} else {
	$_sem_sql = " LEFT JOIN lit_list d ON (c.seminar_id = d.range_id) ";
	$_sem_sql2 = "";
}

$_is_fak = false;

?>
<table width="100%" cellspacing=0 cellpadding=0 border=0>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Verwendete Literatur:")?></b>
		</td>
	</tr>
	<?
	if ($msg) {
		echo "<tr> <td class=\"blank\" colspan=2><br />";
		parse_msg ($msg);
		echo "</td></tr>";
	}
	?>
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<form name="choose_institute" action="<?=$PHP_SELF?>?send=1" method="POST">
			<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
				<tr>
					<td class="steel1">
						<font size=-1><br /><b><?=_("Bitte w&auml;hlen Sie die Einrichtung und das Semester aus, f&uuml;r die Sie die Literaturliste anschauen wollen:")?></b><br/>&nbsp; </font>
					</td>
				</tr>
				<tr>
					<td class="steel1">
					<font size=-1><select name="_inst_id" size="1" style="vertical-align:middle">
					<?
					if ($auth->auth['perm'] == "root"){
						$db->query("SELECT a.Institut_id, a.Name, 1 AS is_fak, COUNT(DISTINCT(catalog_id)) as anzahl FROM Institute a 
									LEFT JOIN Institute b ON (a.Institut_id = b.fakultaets_id AND b.fakultaets_id != b.Institut_id)
									LEFT JOIN seminar_inst c USING(Institut_id)
									$_sem_sql
									LEFT JOIN lit_list_content e USING(list_id)
									WHERE a.Institut_id=a.fakultaets_id
									GROUP BY a.Institut_id ORDER BY Name");
					} elseif ($auth->auth['perm'] == "admin") {
						$db->query("SELECT a.Institut_id,b.Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,COUNT(DISTINCT(catalog_id)) as anzahl
									FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
									LEFT JOIN Institute f ON (f.fakultaets_id=b.institut_id OR f.Institut_id=b.Institut_id)
									LEFT JOIN seminar_inst c USING(Institut_id)
									$_sem_sql
									LEFT JOIN lit_list_content e USING(list_id)
									WHERE a.user_id='$user->id' AND a.inst_perms='admin' 
									GROUP BY a.Institut_id ORDER BY is_fak,Name");
					}
				
					printf ("<option value=\"-1\">%s</option>\n", _("-- bitte Einrichtung ausw&auml;hlen --"));
					while ($db->next_record()){
						printf ("<option value=\"%s\" style=\"%s\" %s>%s </option>\n", $db->f("Institut_id"),($db->f("is_fak") ? "font-weight:bold;" : ""),
								($db->f("Institut_id") == $_REQUEST['_inst_id'] ? " selected " : ""), htmlReady(substr($db->f("Name"), 0, 70)) . " (" . $db->f("anzahl") . ")");
						if ($db->f("is_fak")){
							if ($db->f("Institut_id") == $_REQUEST['_inst_id']){
								$_is_fak = true;
							}
							$db2->query("SELECT a.Institut_id, a.Name, COUNT(DISTINCT(catalog_id)) as anzahl FROM Institute a
										LEFT JOIN seminar_inst c USING(Institut_id)
										$_sem_sql
										LEFT JOIN lit_list_content e USING(list_id)
										WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND a.institut_id!='" .$db->f("Institut_id") . "'
										GROUP BY a.Institut_id ORDER BY Name");
							while ($db2->next_record()){
								printf("<option value=\"%s\" %s>&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", $db2->f("Institut_id"), 
								($db2->f("Institut_id") == $_REQUEST['_inst_id'] ? " selected " : ""),htmlReady(substr($db2->f("Name"), 0, 70)) . " (" . $db2->f("anzahl") . ")");
							}
						}
					}
					?>
				</select>&nbsp;
				<select name="_semester_id" style="vertical-align:middle">
					<option value="all"><?=_("alle")?></option>
					<?
					foreach($_semester->getAllSemesterData() as $sem){
						?>
						<option value="<?=$sem['semester_id']?>" <?=($sem['semester_id'] == $_REQUEST['_semester_id'] ? " selected " : "")?>><?=htmlReady($sem['name'])?></option>
						<?
					}
					?>
				</select>
				</font>&nbsp;
				<input type="IMAGE" <?=makeButton("auswaehlen", "src")?> border=0 align="absmiddle" value="bearbeiten">
				</td>
			</tr>
			<tr>
				<td class="steel1">
					&nbsp; 
				</td>
			</tr>
			<tr>
				<td class="blank">
					&nbsp; 
				</td>
			</tr>
		</table>
		</form>
		<form name="check_elements" action="<?=$PHP_SELF?>?cmd=check" method="POST">
		<?
	if ($_is_fak){
		$sql = "SELECT f.*
				FROM Institute a INNER JOIN seminar_inst c USING (Institut_id)
				INNER JOIN seminare s USING(seminar_id)
				$_sem_sql2
				INNER JOIN lit_list d ON (c.seminar_id = d.range_id)
				INNER JOIN lit_list_content e USING(list_id)
				INNER JOIN lit_catalog f USING(catalog_id)
				WHERE fakultaets_id='" . $_REQUEST['_inst_id'] . "' GROUP BY e.catalog_id ORDER BY dc_date";
		$sql2 = "SELECT s.Name,s.Seminar_id,admission_turnout, COUNT(DISTINCT(su.user_id)) AS participants FROM Institute a INNER JOIN seminar_inst c USING (Institut_id)
				INNER JOIN seminare s USING(seminar_id)
				$_sem_sql2
				INNER JOIN lit_list d ON (c.seminar_id = d.range_id)
				INNER JOIN lit_list_content e USING(list_id)
				LEFT JOIN seminar_user su ON (c.seminar_id = su.seminar_id)
				WHERE fakultaets_id='" . $_REQUEST['_inst_id'] . "' AND catalog_id='?' GROUP BY s.Seminar_id ORDER BY s.Name";
	} else {
		$sql = "SELECT f.*
				FROM seminar_inst c 
				INNER JOIN seminare s USING(seminar_id)
				$_sem_sql2
				INNER JOIN lit_list d ON (c.seminar_id = d.range_id)
				INNER JOIN lit_list_content e USING(list_id)
				INNER JOIN lit_catalog f USING(catalog_id)
				WHERE c.institut_id='" . $_REQUEST['_inst_id'] . "' GROUP BY e.catalog_id ORDER BY dc_date";
		$sql2 = "SELECT s.Name,s.Seminar_id, admission_turnout, COUNT(DISTINCT(su.user_id)) AS participants FROM seminar_inst c 
				INNER JOIN seminare s USING(seminar_id)
				$_sem_sql2
				INNER JOIN lit_list d ON (c.seminar_id = d.range_id)
				INNER JOIN lit_list_content e USING(list_id)
				LEFT JOIN seminar_user su ON (c.seminar_id = su.seminar_id)
				WHERE c.institut_id='" . $_REQUEST['_inst_id'] . "' AND catalog_id='?' GROUP BY s.Seminar_id ORDER BY s.Name";
	}
	if ($_lit_data_id != md5($sql)){
		$db->query($sql);
		$_lit_data_id = md5($sql);
		while ($db->next_record()){
			$_lit_data[$db->f('catalog_id')] = $db->Record;
		}
	}
	foreach ($_lit_data as $cid => $data){
		$element->setValues($data);
		if ($element->getValue('catalog_id')){
			if ($_anker_id == $element->getValue('catalog_id')){
				$icon = "<a name=\"anker\"><img src=\"pictures/cont_lit.gif\" border=\"0\" align=\"bottom\"></a>";
			} else {
				$icon = "<img src=\"pictures/cont_lit.gif\" border=\"0\" align=\"bottom\">";
			}
			$addon = '<input type="checkbox" name="_check_list[]" value="' . $element->getValue('catalog_id') . '">';
			$open = isset($_open[$element->getValue('catalog_id')]) ? 'open' : 'close';
			$link = $PHP_SELF . '?' . (isset($_open[$element->getValue('catalog_id')]) ? 'close' : 'open') . '_element=' . $element->getValue('catalog_id') . '#anker';
			$titel = '<a href="' . $link . '" class="tree">' . htmlReady(my_substr($element->getShortName(),0,85)) . '</a>';
			echo "\n<table width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" align=\"center\"><tr>";
			printhead(0,0,$link,$open,true,$icon,$titel,$addon);
			echo "\n</tr></table>";
			if ($open == 'open'){
				$edit = "";
				$content = "";
				$estimated_p = 0;
				$participants = 0;
				$edit .= "<a href=\"$PHP_SELF?_catalog_id=" . $element->getValue("catalog_id") . "#anker\">"
				. "<img " .makeButton("jetzttesten","src") . tooltip(_("Verfügbarkeit überprüfen"))
				. " border=\"0\"></a>&nbsp;";
				$edit .= "<a href=\"admin_lit_element.php?_catalog_id=" . $element->getValue("catalog_id") . "\">"
				. "<img " .makeButton("details","src") . tooltip(_("Detailansicht dieses Eintrages ansehen."))
				. " border=\"0\"></a>&nbsp;";
				echo "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";
				$content .= "<b>" . _("Titel:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("dc_title"),true,true) . "<br>";
				$content .= "<b>" . _("Autor; weitere Beteiligte:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("authors"),true,true) . "<br>";
				$content .= "<b>" . _("Erschienen:") ."</b>&nbsp;&nbsp;" . htmlReady($element->getValue("published"),true,true) . "<br>";
				$content .= "<b>" . _("Identifikation:") ."</b>&nbsp;&nbsp;" . fixLinks(htmlReady($element->getValue("dc_identifier"),true,true)) . "<br>";
				if ($element->getValue("lit_plugin") != "Studip"){
					$content .= "<b>" . _("Externer Link:") ."</b>&nbsp;&nbsp;";
					if (($link = $element->getValue("external_link"))){
						$content.= formatReady(" [" . $element->getValue("lit_plugin"). "]" . $link);
					} else {
						$content .= _("(Kein externer Link vorhanden.)");
					}
					$content .= "<br>";
				}
				$db2->query(str_replace('?', $element->getValue('catalog_id'), $sql2));
				$content .= "<b>" . _("Veranstaltungen:") . "</b>&nbsp;&nbsp;";
				while ($db2->next_record()){
					$content .= '<a href="details.php?sem_id=' . $db2->f('Seminar_id') . '&send_from_search=1&send_from_search_page=' . $PHP_SELF . '">' . htmlReady(my_substr($db2->f("Name"),0,50)) . "</a>, ";
					$estimated_p += $db2->f('admission_turnout');
					$participants += $db2->f('participants');
				}
				$content = substr($content,0,-2);
				$content .= "<br>";
				$content .= "<b>" . _("Teilnehmeranzahl (erwartet/angemeldet):") . "</b>&nbsp;&nbsp;";
				$content .= ($estimated_p ? $estimated_p : _("unbekannt"));
				$content .= ' / ' . (int)$participants;
				$content .= "<br>";
				if ($_REQUEST['_catalog_id'] == $element->getValue('catalog_id') ){
					$_lit_data[$cid]['check_accession'] = StudipLitSearch::CheckZ3950($element->getValue('accession_number'));
				}
				if (is_array($_lit_data[$cid]['check_accession'])){
					$content .= "<div style=\"margin-top: 10px;border: 1px solid black;padding: 5px; width:96%;\"<b>" ._("Verf&uuml;gbarkeit in externen Katalogen:") . "</b><br>";
					foreach ( $_lit_data[$cid]['check_accession'] as $plugin_name => $ret){
						$content .= "<b>&nbsp;{$plugin_name}&nbsp;</b>";
						if ($ret['found']){
							$content .= _("gefunden") . "&nbsp;";
							$element->setValue('lit_plugin', $plugin_name);
							if (($link = $element->getValue("external_link"))){
								$content.= formatReady(" [" . $element->getValue("lit_plugin"). "]" . $link);
							} else {
								$content .= _("(Kein externer Link vorhanden.)");
							}
						} elseif (count($ret['error'])){
							$content .= '<span style="color:red;">' . htmlReady($ret['error'][0]['msg']) . '</span>';
						} else {
							$content .= _("<u>nicht</u> gefunden") . "&nbsp;";
						}
						$content .= "<br>";
					}
					$content .= "</div>";
				}
				printcontent(0,0,$content,$edit);
				echo "\n</table>";
			}
		}
	}
	?>
	</td>
	</tr>
	<tr>
	<td class="blank">
	&nbsp; 
	</td>
	</tr>
	</table>
	</form>
<?
page_close();
?>
