<?
/**
* ShowToolsRequests.class.php
* 
* room-management tool for room-admins
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ToolsRequestResolve.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowToolsRequests.class.php
// die Suchmaschine fuer Ressourcen
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
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

require_once ($ABSOLUTE_PATH_STUDIP."/cssClassSwitcher.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Seminar.class.php");


$cssSw = new cssClassSwitcher;

/**
* ShowToolsRequests, room-management tool for room-admin
*
* @access	public	
* @author	Cornelis Kater <kater@data-quest.de>
* @version	$Id$
* @package	resources
**/
class ShowToolsRequests {
	var $db;
	var $db2;
	var $cssSw;			//the cssClassSwitcher
	var $requests;			//the requests i'am responsibel for
	
	function ShowToolsRequests() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}
	
	function getMyOpenSemRequests() {
		if (!$this->requests)
			$this->requests = getMyRoomRequests();

		if (is_array($this->requests)) {
			foreach ($this->requests as $val) {
				if ((!$val["closed"]) && ($val["my_sem"]))
					$count++;
			}
		}
		return $count;
	}

	function getMyOpenResRequests() {
		if (!$this->requests)
			$this->requests = getMyRoomRequests();

		if (is_array($this->requests)) {
			foreach ($this->requests as $val) {
				if ((!$val["closed"]) && ($val["my_res"]))
					$count++;
			}
		}
		return $count;
	}
	
	function getMyOpenRequests() {
		if (!$this->requests)
			$this->requests = getMyRoomRequests();
		
		if (is_array($this->requests)) {
			foreach ($this->requests as $val) {
				if (!$val["closed"])
					$count++;
			}
		}
		return $count;
	}
	
	function selectSemInstituteNames($inst_id) {
		$query = sprintf("SELECT a.Name AS inst_name, b.Name AS fak_name FROM Institute a LEFT JOIN Institute b ON (a.fakultaets_id = b.Institut_id) WHERE a.Institut_id = '%s' ", $inst_id);
		$this->db->query($query);
		$this->db->next_record();
		return;
	}

	function selectDates($seminar_id, $termin_id = '') {
		$query = sprintf("SELECT * FROM termine WHERE range_id = '%s' %s ORDER BY date, content", $seminar_id, ($termin_id) ? "AND termin_id = '".$termin_id."'" : "");
		$this->db->query($query);
		return;
	}
	
	function showToolStart() {
		global $PHP_SELF, $cssSw;
		
		$open_requests = $this->getMyOpenRequests();
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?tools_requests_start=1">
			<input type="HIDDEN" name="view" value="edit_request" />
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>"><font size=-1><b><?=_("aktueller Status")?></b><br />
					<?
					if ($open_requests)
						printf (_("Es liegen insgesamt <b>%s</b> nicht aufgel&ouml;ste Anfragen vor - <br />davon <b>%s</b> von Veranstaltungen und <b>%s</b> auf Ressourcen, auf die Sie Zugriff haben."), $open_requests, $this->getMyOpenSemRequests(), $this->getMyOpenResRequests());
					else
						printf (_("Es liegen im Augenblick keine unaufgel&ouml;sten Anfragen vor."));
					?>
					</font>
				</td>
			</tr>
			<? $cssSw->switchClass();
			if ($open_requests) {
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>"><font size=-1><b><?=_("Optionen beim Aufl&ouml;sen")?></b><br />
					<?
					print _("Sie k&ouml;nnen die vorliegenden Anfragen mit folgenden Optionen aufl&ouml;sen:");
					?>
					<br /><br /></font>
					<table border="0" cellpadding="2" cellspacing="0">
						<tr>
							<td width="48%" valign="top">
								<font size="-1">
								<?
								print _("Art der Anfragen:");
								print "<br /><br /><input type=\"RADIO\" name=\"resolve_requests_mode\" value=\"sem\" checked />&nbsp;"._("alle Anfragen");
								print "<br /><input type=\"RADIO\" name=\"resolve_requests_mode\" value=\"res\" />&nbsp;"._("nur Anfragen von meinen Veranstaltungen");
								print "<br /><input type=\"RADIO\" name=\"resolve_requests_mode\" value=\"all\" />&nbsp;"._("nur Anfragen auf meine R&auml;ume");
								?>
								</font>
							</td>
							<td width="4%">
							&nbsp;
							</td>
							<td width="48%">
								<font size="-1">
								<?
								print _("Sortierung der Anfragen:");
								print "<br /><br /><input type=\"RADIO\" name=\"resolve_requests_order\" value=\"complex\" checked />&nbsp;"._("komplexere zuerst (Raumgr&ouml;&szlig;e und  gew&uuml;nschte Eigenschaften)");
								print "<br /><input type=\"RADIO\" name=\"resolve_requests_order\" value=\"oldest\" />&nbsp;"._("&auml;ltere zuerst");
								print "<br /><input type=\"RADIO\" name=\"resolve_requests_order\" value=\"newest\" />&nbsp;"._("neue zuerst");
								?>
								</font>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<? $cssSw->switchClass(); ?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="center">
					<?
					print "<input type=\"IMAGE\" name=\"start_single_mode\" ".makeButton("starten", "src")." />";
					?>
			</td>
			</tr>			
			<?
			}
			?>
			</form>
		</table>
		<br /><br />
		<?
	}
	
	function showRequest($request_id) {
		global $PHP_SELF, $cssSw, $resources_data;

		$reqObj = new RoomRequest($request_id);
		$semObj = new Seminar($reqObj->getSeminarId());

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?working_on_request=<?=$request_id?>">
			<input type="HIDDEN" name="view" value="edit_request" />
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan="2" width="96%" valign="top">
					<b><?=$semObj->getName()?></b> <br />
					<font size="-1">
						<?
						$this->selectSemInstituteNames($semObj->getInstitutId());
						print "&nbsp;&nbsp;&nbsp;&nbsp;"._("Art der Anfrage:")." ".(($reqObj->getTerminId()) ? _("Einzeltermin einer Veranstalung") : (($semObj->getMetaDateType() == 1) ?_("alle Termine einer unregelm&auml;&szlig;igen Veranstaltung") :_("regelm&auml;&szlig;ige Veranstaltungszeiten")))."<br />";
						print "&nbsp;&nbsp;&nbsp;&nbsp;"._("Erstellt von:")." ".htmlReady(get_fullname($reqObj->getUserId()))."<br />";
						print "&nbsp;&nbsp;&nbsp;&nbsp;"._("verantwortliche Einrichtung:")." ".htmlReady($this->db->f("inst_name"))."<br />";
						print "&nbsp;&nbsp;&nbsp;&nbsp;"._("verantwortliche Fakult&auml;t:")." ".htmlReady($this->db->f("fak_name"))."<br />&nbsp;";
						?>
					</font>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%" valign="top">
					<font size="-1"><b><?=_("angeforderte Belegungszeiten:")?></b><br /><br />
					<?
					if (!$reqObj->getTerminId()) {
						if ($semObj->getMetaDateType() == 0) {
							if ($metadates = $semObj->getFormattedTurnusDates()) {
								$i=0;
								$tmp_assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
								foreach ($metadates as $key=>$val) {
									printf ("<font color=\"blue\"><i><b>%s</b></i></font>. %s<br />", $key+1, $val);
									if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"])
										$resObj = new ResourceObject($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$i]["resource_id"]);
									else
										$resObj = new ResourceObject($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$tmp_assign_ids[$i]]["resource_id"]);
									if ($link = $resObj->getFormattedLink(TRUE, TRUE, TRUE))
										print "&nbsp;&nbsp;&nbsp;&nbsp;$link<br />";
									$i++;
								}

								if ($semObj->getCycle() == 1)
									print "<br />"._("w&ouml;chentlich");
								elseif ($semObj->getCycle() == 2)
									print "<br />"._("zweiw&ouml;chentlich");
								print ", "._("ab:")." ".date("d.m.Y", $semObj->getFirstDate());
							} else
								print _("nicht angegeben");
						} else {
							$this->selectDates($reqObj->getSeminarId());
							if ($this->db->nf()) {
								$i=1;
								while ($this->db->next_record()) {
									printf ("<font color=\"blue\"><i><b>%s</b></i></font>. %s%s<br />", $i, date("d.m.Y, H:i", $this->db->f("date")), ($this->db->f("date") != $this->db->f("end_time")) ? " - ".date("H:i", $this->db->f("end_time")) : "");
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key=>$val) {
										if ($val["termin_id"] == $this->db->f("termin_id")) {
											$resObj = new ResourceObject($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$key]["resource_id"]);
											if ($link = $resObj->getFormattedLink(TRUE, TRUE, TRUE))
												print "&nbsp;&nbsp;&nbsp;&nbsp;$link<br />";
										}
									}
									$i++;
								}
							} else
								print _("nicht angegeben");
						}
					} else {
						$this->selectDates($reqObj->getSeminarId(), $reqObj->getTerminId());
						$tmp_assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
						if ($this->db->nf()) {
							$i=1;
							while ($this->db->next_record()) {
								printf ("<font color=\"blue\"><i><b>%s</b></i></font>. %s%s<br />", $i, date("d.m.Y, H:i", $this->db->f("date")), ($this->db->f("date") != $this->db->f("end_time")) ? " - ".date("H:i", $this->db->f("end_time")) : "");
								$resObj = new ResourceObject($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$tmp_assign_ids[$i-1]]["resource_id"]);
								if ($link = $resObj->getFormattedLink(TRUE, TRUE, TRUE))
									print "&nbsp;&nbsp;&nbsp;&nbsp;$link<br />";
								$i++;
							}
						} else
							print _("nicht angegeben");
					}
					?>
					</font>
				</td>
				<td style="border-left:1px dotted black; background-image: url('pictures/steel4.jpg')" width="51%" rowspan="3" valign="top">
					<table cellpadding="2" cellspacing="0" border="0" width="90%">
						<tr>
							<td width="70%">
								<font size="-1"><b><?=_("angeforderter Raum:")?></b></font>
							</td>
							<?
							$cols=0;
							if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"]))
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key => $val) {
										$cols++;
										print "<td width=\1%\" align=\"left\"><font size=\"-1\" color=\"blue\"><i><b>".$cols.".</b></i></font></td>";
									}
							} else {
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]))
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key => $val) {
										$cols++;
										print "<td width=\1%\" align=\"left\"><font size=\"-1\" color=\"blue\"><i><b>".$cols.".</b></i></font></td>";
									}
							}
							?>
							<td width="29%">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td width="70%">
							<font size="-1">
							<?
							if ($request_resource_id = $reqObj->getResourceId()) {
								$resObj = new ResourceObject($request_resource_id);
								print "<img src=\"./pictures/info.gif\" ".tooltip(_("Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:")." \n".$resObj->getPlainProperties(TRUE), TRUE, TRUE)." />";
								print "&nbsp;".$resObj->getFormattedLink(TRUE, TRUE, TRUE);
							} else
								print _("Es wurde kein Raum angefordert.");
		
							?>
							</font>
							</td>
							<?
							$i=0;
							if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
								foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key => $val) {
									print "<td width=\"1%\" nowrap><font size=\"-1\">";
									if ($request_resource_id) {
										if ($request_resource_id == $val["resource_id"]) {
											print "<img src=\"pictures/haken_transparent.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showGroupOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$request_resource_id], $val["events_count"], $val["overlap_events_count"][$request_resource_id], $val["termin_ids"]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", ($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i, $request_resource_id, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i] == $request_resource_id) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
									} else
										print "&nbsp;";
									print "</font></td>";
									$i++;
								}
							} elseif (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"])) {
								foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key => $val) {
									print "<td width=\"1%\" nowrap><font size=\"-1\">";
									if ($request_resource_id) {
										if ($request_resource_id == $val["resource_id"]) {
											print "<img src=\"pictures/haken_transparent.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$request_resource_id][$key], $val["events_count"], $val["overlap_events_count"][$request_resource_id]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", ($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i, $request_resource_id, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i] == $request_resource_id) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
									} else
										print "&nbsp;";
									print "</font></td>";
									$i++;
								}
							}
							?>
							<td width="29%">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="<?=$cols+2?>">
								<font size="-1"><b><?=_("weitere passende R&auml;ume:")?></b></font>
							</td>
						</tr>
						<?
						if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"]))
							foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"] as $key=>$val) {
								if ($val["type"] == "matching")
									$matching_rooms[$key] = TRUE;
								if ($val["type"] == "clipped")
									$clipped_rooms[$key] = TRUE;
							}
									
						if (sizeof($matching_rooms)) {
							foreach ($matching_rooms as $key=>$val) {
							?>
						<tr>
							<td width="70%"><font size="-1">
								<?
								$resObj = new ResourceObject($key);
								print "<img src=\"./pictures/info.gif\" ".tooltip(_("Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:")." \n".$resObj->getPlainProperties(TRUE), TRUE, TRUE)." />";
								print "&nbsp;".$resObj->getFormattedLink(TRUE, TRUE, TRUE);
								//printf ("&nbsp;<a target=\"_new\" href=\"%s\">%s</a><br />", $resObj->getLink(TRUE), htmlReady($resObj->getName()));
							?>
							</td>
							<?
							$i=0;
							if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"])) {
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key2 => $val2) {
										print "<td width=\"1%\" nowrap><font size=\"-1\">";
										if ($key == $val2["resource_id"]) {
											print "<img src=\"pictures/haken_transparent.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showGroupOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", (($semObj->getMetaDateType() == 1) && (!$reqObj->getTerminId())) ? $val2["termin_id"] : $i, $key, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
										print "</font></td>";
										$i++;
									}
								}
							} else {								
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"])) {
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key2 => $val2) {
										print "<td width=\"1%\" nowrap><font size=\"-1\">";
										if ($key == $val2["resource_id"]) {
											print "<img src=\"pictures/haken_transparent.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$key][$key2], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", (($semObj->getMetaDateType() == 1) && (!$reqObj->getTerminId())) ? $val2["termin_id"] : $i, $key, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i] == $key) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
										print "</font></td>";
										$i++;
									}
								}
							}
							?>
							<td width="29%">
								&nbsp;
							</td>
						</font></td>
						</tr>
							<?
							}
						} else
							print "<tr><td width=\"100%\" colspan=\"".($cols+1)."\"><font size=\"-1\">"._("keine gefunden")."</font></td></tr>";
						//Clipped Rooms
						if (sizeof($clipped_rooms)) {
						?>						
						<tr>
							<td width="100%" colspan="<?=$cols+2?>">
								<font size="-1"><b><?=_("R&auml;ume aus der Merkliste:")?></b></font>
							</td>
						</tr>
						<?
							foreach ($clipped_rooms as $key=>$val) {
						?>
						<tr>
							<td width="70%"><font size="-1">
								<?
								$resObj = new ResourceObject($key);
								print "<img src=\"./pictures/info.gif\" ".tooltip(_("Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:")." \n".$resObj->getPlainProperties(TRUE), TRUE, TRUE)." />";
								print "&nbsp;".$resObj->getFormattedLink(TRUE, TRUE, TRUE);
								//printf ("&nbsp;<a target=\"_new\" href=\"%s\">%s</a><br />", $resObj->getLink(TRUE), htmlReady($resObj->getName()));
							?>
							</td>
							<?
							$i=0;
							if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"])) {
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key2 => $val2) {
										print "<td width=\"1%\" nowrap><font size=\"-1\">";
										if ($key == $val2["resource_id"]) {
											print "<img src=\"pictures/haken_transparent.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showGroupOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", (($semObj->getMetaDateType() == 1) && (!$reqObj->getTerminId())) ? $val2["termin_id"] : $i, $key, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
										print "</font></td>";
										$i++;
									}
								}
							} else {									
								if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"])) {
									foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key2 => $val2) {
										print "<td width=\"1%\" nowrap><font size=\"-1\">";
										if ($key == $val2["resource_id"]) {
											print "<img src=\"pictures/haken.gif\" ".tooltip(_("Dieser Raum ist augenblicklich gebucht"), TRUE, TRUE)." />";
										} else {
											$overlap_status = $this->showOverlapStatus($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"][$key][$key2], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()]);
											print $overlap_status["html"];
											printf ("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s/>", ($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i, $key, ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][($semObj->getMetaDateType() == 1) ? $val2["termin_id"] : $i] == $key) ? "checked" : "", ($overlap_status["status"] == 2) ? "disabled" : "");
										}
										print "</font></td>";
										$i++;
									}
								}
							}
							?>
							<td width="29%">
								&nbsp;
							</td>
						</font></td>
						</tr>
						<?
							}
						}
						?>
					</table>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%" valign="top">
					<font size="-1"><b><?=_("gew&uuml;nschte Raumeigenschaften:")?></b><br /><br />
					<?
					$properties = $reqObj->getProperties();
					if (sizeof($properties)) {
					?>
						<table width="99%" cellspacing="0" cellpadding="2" border="0">
						<?
						
						foreach ($properties as $key=>$val) {
							?>
							<tr>
								<td width="50%">
									<li><font size="-1"><?=htmlReady($val["name"])?></font></li>
								</td>
								<td width="50%"><font size="-1">
								<?
								switch ($val["type"]) {
									case "bool":
										/*printf ("%s", ($val["state"]) ?  htmlReady($val["options"]) : " - ");*/
									break;
									case "num":
									case "text";
										print htmlReady($val["state"]);
									break;
									case "select";
										$options=explode (";",$val["options"]);
										foreach ($options as $a) {
											if ($val["state"] == $a) 
												print htmlReady($a);
										}
									break;
								}
								?></font>
								</td>
							</tr>
							<?
						}						
						?>
						</table>
						<?
					} else
						print _("Es wurden keine Raumeigenschaften gew&uuml;nscht.");
					?>
					</font>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="35%" valign="top">
					<font size="-1"><b><?=_("Kommentar des Anfragenden:")?></b><br /><br />
					<?
					if ($comment = $reqObj->getComment())
						print $comment;
					else
						print _("Es wurde kein Kommentar eingegeben");
					?>
					</font>
				</td>
			
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan="2" width="96%" valign="top" align="center">
				<? 
				// can we dec?
				if ($resources_data["requests_working_pos"] > 0) {
					$d = -1;
					if ($resources_data["skip_closed_requests"])
						while ((!$resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $d]["request_id"]]) && ($resources_data["requests_working_pos"] + $d > 0))
							$d--;
					if ((sizeof($resources_data["requests_open"]) > 1) && (($resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $d]["request_id"]]) || (!$resources_data["skip_closed_requests"])))
						$inc_possible = TRUE;
				} 
				if ($inc_possible) {
					print("&nbsp;<input type=\"IMAGE\" name=\"dec_request\" ".makeButton("zurueck", "src")." border=\"0\" />");
				} 
				print("&nbsp;<input type=\"IMAGE\" name=\"cancel_edit_request\" ".makeButton("abbrechen", "src")." border=\"0\" />");
				print("&nbsp;<input type=\"IMAGE\" name=\"save_state\" ".makeButton("speichern", "src")." border=\"0\" />");
				
				// can we inc?
				if ($resources_data["requests_working_pos"] < sizeof($resources_data["requests_working_on"])-1) {
					$i = 1;
					if ($resources_data["skip_closed_requests"])
						while ((!$resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $i]["request_id"]]) && ($resources_data["requests_working_pos"] + $i < sizeof($resources_data["requests_working_on"])-1))
							$i++;
					if ((sizeof($resources_data["requests_open"]) > 1) && (($resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $i]["request_id"]]) || (!$resources_data["skip_closed_requests"])))
						$dec_possible = TRUE;
				} 
								
				if ($dec_possible) {
					print("&nbsp;<input type=\"IMAGE\" name=\"inc_request\" ".makeButton("weiter", "src")." border=\"0\" />");
				} 
				if (sizeof($resources_data["requests_open"]) > 1)
					printf ("<br /><font size=\"-1\">" . _("<b>%s</b> von <b>%s</b> Anfragen wurden noch nicht bearbeitet.") . "</font>", sizeof($resources_data["requests_open"]), sizeof($resources_data["requests_working_on"]));
				?>
				</td>
			</tr>
		</form>
		</table>
		<br /><br />
		<?
	}
	
	function showGroupOverlapStatus($overlaps, $events_count, $overlap_events_count, $group_dates) {
		if ($overlap_events_count) {
			if ($overlap_events_count >= round($events_count * ($GLOBALS['RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE'] / 100))) {
				if ($overlap_events_count == 1)
					$desc.=sprintf(_("Es existieren Überschneidungen zur gewünschten Belegungszeit.")."\n");
				else
					$desc.=sprintf(_("Es existieren Überschneidungen zu mehr als %s%% aller gewünschten Belegungszeiten.")."\n", $GLOBALS['RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE']);
				$html = "<img src=\"pictures/ampel_rot.gif\" ".tooltip($desc, TRUE, TRUE)." />";
				$status = 2;
			} else {
				$desc.=sprintf(_("Einige der gewünschten Belegungszeiten überschneiden sich mit eingetragenen Belegungen:\n"));
				foreach ($group_dates as $key=>$val) { 
					if ($overlaps[$key]) 
						foreach ($overlaps[$key] as $key2=>$val2) 
							$desc.=sprintf(_("%s von %s bis %s Uhr")."\n", date("d.m.Y", $val2["begin"]), date("H:i", $val2["begin"]), date("H:i", $val2["end"]));
				}
				$html = "<img src=\"pictures/ampel_gelb.gif\" ".tooltip($desc, TRUE, TRUE)." />";
				$status = 1;
			}
		} else {
			$html = "<img src=\"pictures/ampel_gruen.gif\" ".tooltip(_("Es existieren keine Überschneidungen"), TRUE, TRUE)."/>";
			$status = 0;
		}
		return array("html"=>$html, "status"=>$status);
	}


	function showOverlapStatus($overlaps, $events_count, $overlap_events_count) {
		if (is_array($overlaps)) {
			if ($overlap_events_count >= round($events_count * ($GLOBALS['RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE'] / 100))) {
				if ($overlap_events_count == 1)
					$desc.=sprintf(_("Es existieren Überschneidungen zur gewünschten Belegungszeit.")."\n");
				else
					$desc.=sprintf(_("Es existieren Überschneidungen zu mehr als %s%% aller gewünschten Belegungszeiten.")."\n", $GLOBALS['RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE']);
				$html = "<img src=\"pictures/ampel_rot.gif\" ".tooltip($desc, TRUE, TRUE)." />";
				$status = 2;
			} else {
				$desc.=sprintf(_("Einige der gewünschten Belegungszeiten überschneiden sich mit eingetragenen Belegungen:\n"));
				foreach ($overlaps as $val) { 
					$desc.=sprintf(_("%s von %s bis %s Uhr")."\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("H:i", $val["end"]));
				}
				$html = "<img src=\"pictures/ampel_gelb.gif\" ".tooltip($desc, TRUE, TRUE)." />";
				$status = 1;
			}
		} else {
			$html = "<img src=\"pictures/ampel_gruen.gif\" ".tooltip(_("Es existieren keine Überschneidungen"), TRUE, TRUE)."/>";
			$status = 0;
		}
		return array("html"=>$html, "status"=>$status);
	}
	
}