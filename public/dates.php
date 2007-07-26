<?php
/**
* dates.php
*
* Schedule for Students
*
* @author		Till Glöggler <tgloeggl@uni-osnabrueck.de>
* @version		$Id$
* @access		public
* @modulegroup		views
* @module		dates.php
* @package		studip_core
*/


// Copyright (C) 2005-2007 Till Glöggler <tgloeggl@uni-osnabrueck.de>
// This file is part of Stud.IP
// dates.php
// Anzeige des Ablaufplans einer Veranstaltung in der Studentenansicht
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");

include ("lib/seminar_open.php"); // initialise Stud.IP-Session

$id = $SessSemName[1];
$issue_open = array();

$sess->register('showDatesFilter');

require_once ('lib/classes/Seminar.class.php');
require_once ('lib/raumzeit/raumzeit_functions.inc.php');

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
}

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

checkObject();
checkObjectModule("schedule");
object_set_visit_module("schedule");

include ("lib/include/links_openobject.inc.php");

$sem = new Seminar($id);
$semester = new SemesterData();
$data = $semester->getCurrentSemesterData();
$raumzeitFilter = $data['beginn'];
$sem->checkFilter();
$themen =& $sem->getIssues();

function dates_open() {
	global $issue_open, $_REQUEST;

	$issue_open[$_REQUEST['open_close_id']] = true;
}

function dates_close() {
	global $issue_open, $_REQUEST;

	$issue_open[$_REQUEST['open_close_id']] = false;
	unset ($issue_open[$_REQUEST['open_close_id']]);
}

function dates_settype() {
	global $showDatesFilter, $type;

	$showDatesFilter = $type;
}

$sem->registerCommand('open', 'dates_open');
$sem->registerCommand('close', 'dates_close');
$sem->registerCommand('setType', 'dates_settype');
$sem->processCommands();

if ($cmd == 'openAll') $openAll = true;
?>
<TABLE width="100%" border="0" cellpadding="2" cellspacing="0">
	<TR>
		<TD colspan="2" class="topic">
			&nbsp; <B><?=getHeaderLine($id)." -  "._("Ablaufplan");?></B>
		</TD>
	</TR>
  <TR>
		<TD align="center" class="blank" width="80%" valign="top">
			<TABLE width="99%" cellspacing="0" cellpadding="0" border="0">
				<TR>
					<TD class="steelgraulight" colspan="10" height="24" align="center">
						<A href="<?=$PHP_SELF?>?cmd=<?=($openAll) ? 'close' : 'open'?>All">
							<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/<?=($openAll) ? 'close' : 'open'?>_all.gif" border="0" <?=tooltip(sprintf("Alle Termine %sklappen", ($openAll) ? 'zu' : 'auf'))?>>
						</A>
					</TD>
				</TR>
				<TR>
					<TD colspan="10" height="3">
					</TD>
				</TR>
				<?

				$termine = getAllSortedSingleDates($sem);

				$semester = new SemesterData();
				$all_semester = $semester->getAllSemesterData();

				if (is_array($termine)){
    				foreach ($termine as $singledate_id => $singledate) {
    
    					if ( ($grenze == 0) || ($grenze < $singledate->getStartTime()) ) {
    						foreach ($all_semester as $zwsem) {
    							if ( ($zwsem['beginn'] < $singledate->getStartTime()) && ($zwsem['ende'] > $singledate->getStartTime()) ) {
    								$grenze = $zwsem['ende'];
    								?>
    								<TR>
    									<TD class="steelgraulight" align="center" colspan="9">
    										<FONT size="-1"><B><?=$zwsem['name']?></B></FONT>
    									</TD>
    								</TR>
    								<?
    							}
    						}
    					}
    
    					// Template fuer einzelnes Datum
    					$showSpecialDays = FALSE;
    					$tpl = getTemplateDataForSingleDate($singledate, $metadate_id);
    					// If "Sitzung" shall not be shown, uncomment this
    					/*if ($tpl['type'] == 1 || $tpl['type'] == 7) {
    						unset($tpl['art']);
    					}*/
    					
    					//calendar jump
    					$tpl['calendar'] = "&nbsp;<a href=\"calendar.php?cmd=showweek&atime=" . $singledate->getStartTime();
    					$tpl['calendar'] .= "\"><img style=\"vertical-align:bottom\" src=\"".$GLOBALS['ASSETS_URL']."images/popupkalender.gif\" ";
    					$tpl['calendar'] .= tooltip(sprintf(_("Zum %s in den persönlichen Terminkalender springen"), date("m.d", $singledate->getStartTime()))); 
    					$tpl['calendar'] .= ' border="0"></a>';
    
    					if ($showDatesFilter) {
    						switch ($showDatesFilter) {
    							case 'all':
    								break;
    
    							case 'others':
    								if ($tpl['type'] == 1) {
    									$tpl['deleted'] = true;
    								}
    								break;
    
    							default:
    								if ($tpl['type'] != $type) {
    									$tpl['deleted'] = true;
    								}
    								break;
    						}
    					}
    
							if ($openAll) $tpl['openall'] = true;

    					if (!$tpl['deleted'] || $tpl['comment'])  {
    						$tpl['class'] = 'printhead';
    						$tpl['cycle_id'] = $metadate_id;
    
    						$issue_id = '';
    						if (is_array($tmp_ids = $singledate->getIssueIDs())) {
    							foreach ($tmp_ids as $val) {
    								if (empty($issue_id)) {
    									if (is_object($themen[$val])) {
    										$issue_id = $val;
    									}
    								} else {
    									if (is_object($themen[$val])) {
    										$tpl['additional_themes'][] = array('title' => htmlReady($themen[$val]->getTitle()), 'desc' => formatReady($themen[$val]->getDescription()));
    									}
    								}
    							}
    						}
    						if (is_object($themen[$issue_id])) {
    							$tpl['issue_id'] = $issue_id;
    							$thema =& $themen[$issue_id];
    							$tpl['theme_title'] = htmlReady($thema->getTitle());
    							$tpl['theme_description'] = formatReady($thema->getDescription());
    							$tpl['folder_id'] = $thema->getFolderID();
    							$tpl['forumEntry'] = $thema->hasForum();
    							$tpl['fileEntry'] = $thema->hasFile();								
    							$tpl['forumCount'] = get_not_visited('forum', $id, $thema->getIssueId());
    							$tpl['fileCount'] = get_not_visited('document', $id, $thema->getFolderId());
    						}
    
    						include('lib/raumzeit/templates/singledate_student.tpl');
    					}
           }
				}
				?>
			</TABLE>
		</TD>
		<TD class="blank" align="right" valign="top">
		<?
			//Build an infobox
			$infobox[0]["kategorie"] = _("Informationen:");
			$infobox[0]["eintrag"][] = array ('icon' => "ausruf_small.gif",
					"text"  =>_("Hier finden Sie alle Termine der Veranstaltung."));
			if ($rechte) {
					$infobox[1]["kategorie"] = _("Aktionen:");
					$infobox[1]["eintrag"][] = array ('icon' => "link_intern.gif",
							"text"  =>"<a href=\"raumzeit.php?cmd=createNewSingleDate#newSingleDate\">"._("Einen neuen Termin anlegen")."</a>");
					$infobox[1]["eintrag"][] = array ('icon' => "link_intern.gif",
							"text"  =>"<a href=\"raumzeit.php\">"._("Zur Terminverwaltung")."</a>");

					$infobox[1]["eintrag"][] = array ('icon' => "link_intern.gif",
							"text"  =>"<a href=\"themen.php\">"._("Zur Ablaufplanverwaltung")."</a>");
	
			}
			print_infobox ($infobox, "schedules.jpg");
		?>
		</TD>
	</TR>
	<TR>
		<TD class="blank" colspan="5">
			&nbsp;
		</TD>
	</TR>
</TABLE>
</FORM>
<?
page_close();
