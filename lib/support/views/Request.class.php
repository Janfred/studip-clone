<?
# Lifter002: TODO
/**
* Request.class.php
*
* shows the list of requests
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		Request.class.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Request.class.php
// stellt die Liste der Requests zur Verfuegung
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
Request, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/
require_once ($RELATIVE_PATH_SUPPORT."/views/ShowTreeRow.class.php");
require_once ($RELATIVE_PATH_SUPPORT."/lib/RequestObject.class.php");

class Request extends ShowTreeRow {
	var $db;
	var $db2;

	function Request() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}

	function showRequests ($contract_id, $search_exp = '', $show_all) {
		$db = new DB_Seminar;
		$query = sprintf("SELECT request_id FROM support_request WHERE contract_id = '%s' %s ORDER BY date DESC %s", $contract_id, ($search_exp) ? "AND name LIKE '%$search_exp%' " : "", (($this->getRequestsCount($contract_id) > 10) && (!$show_all)) ? "LIMIT 0,10" : "");
		$db->query($query);
		while ($db->next_record()) {
			$this->showListObject($db->f("request_id"));
		}
	}

	function getRequestsCount ($contract_id) {
		$db = new DB_Seminar;
		$query = sprintf("SELECT count(request_id) AS count FROM support_request WHERE contract_id = '%s' ", $contract_id);
		$db->query($query);
		$db->next_record();
		return 	$db->f("count");
	}

	function showSearchForm($search_exp) {
		?>
		<table align="center" width="99%" cellpadding="2" cellspacing="0">
		<tr>
			<td class="steel1" align="center">
				<font size=-1>freie Suche:&nbsp;
				<input name="search_exp"  type="TEXT" style="{font-size:8pt; vertikal-align: middle;}" size=30 maxlength=255 value="<? echo $search_exp; ?>" />
				<input type="IMAGE" align="absmiddle"  <? echo makeButton ("suchestarten", "src") ?> name="start_search" border=0 value="<?=_("Suche starten")?>">
			</td>
		</tr>
		</table>
		<?
	}

	//private
	function showListObject ($request_id) {
		global $supportdb_data, $edit_req_object, $RELATIVE_PATH_SUPPORT, $PHP_SELF, $SessSemName, $supporter, $perm, $user, $_fullname_sql;

		//Object erstellen
		$reqObject=new RequestObject($request_id);

		//Daten vorbereiten
		$icon="<img src=\"".$GLOBALS['ASSETS_URL']."images/cont_folder2.gif\" />";

		if (!$supportdb_data["req_opens"]) {
			$supportdb_data["req_opens"][$reqObject->getId()] = TRUE;
			$supportdb_data["actual_req"] = $reqObject->getId();
		}

		if ($supportdb_data["req_opens"][$reqObject->getId()]) {
			$link=$PHP_SELF."?req_close=".$reqObject->getId()."#a";
			$open="open";
			if ($supportdb_data["actual_req"] == $reqObject->getId())
				echo "<a name=\"a\"></a>";
		} else {
			$link=$PHP_SELF."?req_open=".$reqObject->getId()."#a";
			$open="close";
		}

		if ($edit_req_object == $reqObject->id) {
			echo "<a name=\"a\"></a>";
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"40\" maxlength=\"255\" name=\"req_name\" value=\"".htmlReady($reqObject->getName())."\" />";
		} elseif ($reqObject->getName()) {
			$titel = htmlReady($reqObject->getName());
		} else
			$titel = _("kein Titel");

		//create a link on the titel, too
		if (($link) && ($edit_req_object != $reqObject->id))
			$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";

		//request user
		if (($edit_req_object == $reqObject->id) && ($supporter)) {
			$this->db->query("SELECT " . $_fullname_sql['no_title_rev'] . ", auth_user_md5.user_id FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE status IN ('tutor','autor')  AND Seminar_id='".$SessSemName[1]."' ORDER BY Nachname");
			if ($this->db->nf()) {
				$zusatz =  _("von:")." <select name=\"req_user_id\" style=\"{font-size:8pt;};\">\n";
				while ($this->db->next_record()) {
					$zusatz .= sprintf ("<option %s style=\"{font-size:8pt;}\" value=\"%s\">%s</option>\n", $this->db->f("user_id") == $reqObject->getUserId() ? "selected" : "", $this->db->f("user_id"), htmlReady(my_substr($this->db->f(0),0,30)));
				}
				$zusatz .= "</select>\n";
			} else
				$zusatz = _("von:")." "._("unbekannt");
		} elseif ($reqObject->getUserId()) {
			$zusatz = sprintf(_("von:")." <a href=\"about.php?username=%s\"><font color=\"#333399\">%s</font></a>", get_username($reqObject->getUserId()), get_fullname($reqObject->getUserId(),'full',1));
		} else
			$zusatz = _("von:")." "._("unbekannt");

		$new=TRUE;
		if ($open == "open") {
			$content = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
			$content .= sprintf ("<tr><td width=\"10%%\"><b><font size=\"-1\">"._("Medium:")."</font></b></td><td width=\"10%%\" align=\"left\"><font size=\"-1\">");
			if (($edit_req_object == $reqObject->id) && ($supporter)) {
				$content .= "<select style=\"{font-size:8pt;}\" name=\"req_channel\">";
				$content .= sprintf ("<option %s value=\"FALSE\">"._("unbekannt")."</option>", (!$reqObject->getChannel()) ? "selected" : "");
				$content .= sprintf ("<option %s value=\"1\">"._("E-Mail")."</option>", ($reqObject->getChannel() == 1) ? "selected" : "");
				$content .= sprintf ("<option %s value=\"2\">"._("Telefon")."</option>", ($reqObject->getChannel() == 2) ? "selected" : "");
				$content .= sprintf ("<option %s value=\"3\">"._("Feedback-Forum")."</option>", ($reqObject->getChannel() == 3) ? "selected" : "");
				$content .= sprintf ("<option %s value=\"4\">"._("Support-Forum")."</option>", ($reqObject->getChannel() == 4) ? "selected" : "");
				$content .= "</select>";
			} else
				switch ($reqObject->getChannel()) {
					case 1:
						$content .= _("eMail");
					break;
					case 2:
						$content .= _("Telefon");
					break;
					case 3:
						$content .= _("Feedback-Forum der Installation");
					break;
					case 4:
						$content .= _("Support-Forum");
					break;
					default:
						$content .= _("unbekannt");
					break;
				}
			$content .= "</font></td><td width=\"10%\">&nbsp;</td>";
			$content .= sprintf("<td width=\"10%%\"><b><font size=\"-1\">"._("Datum:")."</font></b></td><td width=\"40%%\" align=\"left\"><font size=\"-1\">");
			if (($edit_req_object == $reqObject->id) && ($supporter)) {
				$content .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"req_day\" value=\"".date("d", $reqObject->getDate())."\" />.";
				$content .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"req_month\" value=\"".date("m", $reqObject->getDate())."\" />.";
				$content .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"4\" maxlength=\"4\" name=\"req_year\" value=\"".date("Y", $reqObject->getDate())."\" />";
				$content .= "&nbsp;um&nbsp;<input style=\"{font-size:8pt;}\" type=\"text\"size=2 maxlength=\"2\" name=\"req_hour\" value=\"".date("H", $reqObject->getDate())."\" />:";
				$content .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"req_min\" value=\"".date("i", $reqObject->getDate())."\" />"._("Uhr");
				$content .= sprintf ("<input type=\"HIDDEN\" name=\"sent_req_id\" value=\"%s\" />", $reqObject->id);
			} else
				$content .= date("d.m.Y H:i", $reqObject->getDate());

			$content .="</font></td></tr>\n";

			$content .="<tr><td colspan=\"5\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"3\"  /></td></tr>\n";

			//Topic assignment
			if (($edit_req_object == $reqObject->id) && ($supporter)) {
				$content .= sprintf ("<tr><td width=\"20%%\"><b><font size=\"-1\">"._("Beitrag im Forum:")."</font></b></td><td width=\"80%%\" align=\"left\" colspan=\"4\"><font size=\"-1\">");

				$query = sprintf("SELECT px_topics.name, px_topics.topic_id, date FROM px_topics LEFT OUTER JOIN support_request USING (topic_id) WHERE (support_request.topic_id IS NULL AND seminar_id = '%s' AND parent_id ='0') OR px_topics.topic_id = '%s'", $SessSemName[1], $reqObject->getTopicId());
				$this->db->query($query);

				$content .= "<select style=\"{font-size:8pt;}\" name=\"req_topic_id\">";
				$content .= sprintf ("<option %s value=\"FALSE\">--"._("kein Forenthema")."--</option>", (!$reqObject->getTopicId()) ? "selected" : "");

				while ($this->db->next_record()) {
					$content .= sprintf ("<option %s value=\"%s\">%s</option>", ($this->db->f("topic_id") == $reqObject->getTopicId()) ? "selected" : "", $this->db->f("topic_id"), $this->db->f("name"));
				}
				$content .= "</select>";

			} elseif ($reqObject->getTopicId()) {
				$content .= sprintf ("<tr><td width=\"20%%\"><b><font size=\"-1\">"._("Beitrag im Forum:")."</font></b></td><td width=\"80%%\" align=\"left\" colspan=\"4\"><font size=\"-1\">");
				$query = sprintf("SELECT name FROM px_topics WHERE topic_id = '%s'", $reqObject->getTopicId());
				$this->db->query($query);
				$this->db->next_record();

				$content .= sprintf ("<a href=\"forum.php?topic_id=%s&all=TRUE&open=%s\">%s</a>", $reqObject->getTopicId(), $reqObject->getTopicId(), htmlReady($this->db->f("name")));
			}

			$content .= "</font></td></tr>\n";

			$content .= "</table>";
			$content .= "<br /><b><font size=\"-1\">"._("Bearbeitungszeiten:")."</font></b><br />";

			$query = sprintf("SELECT event_id, user_id, begin, end, used_points FROM support_event WHERE request_id = '%s' ORDER BY begin", $reqObject->getId());
			$this->db->query($query);

			if ($this->db->nf()) {
				$rows = 0;
				$content .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"90%\">\n";
				$content .= "<tr><td width=\"20%%\"><font size=\"-1\">"._("Beginn:")."</td><td width=\"20%%\"><font size=\"-1\">"._("Ende:")."</td><td width=\"20%%\"><font size=\"-1\">"._("Punkte:")."</td><td width=\"20%%\"><font size=\"-1\">"._("bearbeitet von:")."</td></tr>";
				while ($this->db->next_record()) {
					$rows++;
					if ($rows <= $this->db->nf()) {
						$content .= "<tr><td colspan=\"5\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /><td><td><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /></td></tr>\n";
						$content .= "<tr><td colspan=\"5\" style=\"{background-image: url('".$GLOBALS['ASSETS_URL']."images/line.gif')};\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /><td><td><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /></td></tr>\n";
						$content .= "<tr><td colspan=\"5\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"3\"  /><td><td><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /></td></tr>\n";
					}
					$content .= "<tr>";
					if ($supportdb_data["evt_edits"][$this->db->f("event_id")]) {
						//we need the user_id's from all the supporter (=dozenten)
						$this->db2->query("SELECT " . $_fullname_sql['no_title_rev'] . ", auth_user_md5.user_id FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE (status IN ('dozent') AND Seminar_id='".$SessSemName[1]."') ORDER BY Nachname");

						//edit evemt start time
						$content .= "<td width=\"20%%\"><font size=\"-1\">";
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_begin_day[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />.", date("d", $this->db->f("begin")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_begin_month[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />.", date("m", $this->db->f("begin")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_begin_year[]\" size=\"4\" maxlength=\"4\" value=\"%s\" />&nbsp;", date("Y", $this->db->f("begin")));
						$content .= sprintf ("<br /><input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_begin_hour[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />:", date("H", $this->db->f("begin")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_begin_min[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />", date("i", $this->db->f("begin")));
						$content .= "</font></td>";

						//edit event end time
						$content .= "<td width=\"20%%\"><font size=\"-1\">";
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_end_day[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />.", date("d", $this->db->f("end")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_end_month[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />.", date("m", $this->db->f("end")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_end_year[]\" size=\"4\" maxlength=\"4\" value=\"%s\" />&nbsp;", date("Y", $this->db->f("end")));
						$content .= sprintf ("<br /><input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_end_hour[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />:", date("H", $this->db->f("end")));
						$content .= sprintf ("<input type=\"TEXT\" style=\"{font-size:8pt;}\" name=\"evt_end_min[]\" size=\"2\" maxlength=\"2\" value=\"%s\" />", date("i", $this->db->f("end")));
						$content .= "</font></td>";

						$content .= sprintf ("<td width=\"20%%\" valign=\"top\"><font size=\"-1\">%s</font></td>", ($this->db->f("used_points")) ? $this->db->f("used_points") : _("werden automatisch berechnet"));

						//edit event supporter
						$content .= "<td width=\"30%%\" valign=\"top\"><font size=\"-1\">";
						$content .= "<select style=\"{font-size:8pt;}\" name=\"evt_user_id[]\">";
						$content .= sprintf ("<option %s value=\"FALSE\">"._("unbekannt")."</option>", (!$reqObject->getUserId()) ? "selected" : "");
						while ($this->db2->next_record()) {
							$content .= sprintf ("<option %s style=\"{font-size:8pt;}\" value=\"%s\">%s</option>\n", $this->db2->f("user_id") == $this->db->f("user_id") ? "selected" : "", $this->db2->f("user_id"), htmlReady(my_substr($this->db2->f(0),0,30)));
						}
						$content .= "</select>\n";
						$content .= "<br /><input type=\"HIDDEN\" name=\"evt_id[]\" value=\"".$this->db->f("event_id")."\" />";
						$content .= "</td>";

						$content .= "<td width=\"10%%\" align=\"right\" valign=\"top\"><font size=\"-1\">";
						$content .= "<input type=\"IMAGE\" name=\"evt_sent\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" ".tooltip(_("Diesen Eintrag speichern"))." />";
						$content .= "&nbsp;&nbsp;<a href=\"$PHP_SELF?kill_evt=".$this->db->f("event_id")."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" ".tooltip(_("Diesen Eintrag löschen"))."/></a>";
						$content .= "</td></tr>";

					} else {
						$content .= sprintf ("<td width=\"20%%\"><font size=\"-1\">%s</font></td>", date("d.m.Y H:i", $this->db->f("begin")));
						$content .= sprintf ("<td width=\"20%%\"><font size=\"-1\">%s</font></td>", date("d.m.Y H:i", $this->db->f("end")));
						$content .= sprintf ("<td width=\"20%%\"><font size=\"-1\">%s</font></td>", $this->db->f("used_points"));
						$content .= sprintf ("<td width=\"30%%\"><font size=\"-1\"><a href=\"about.php?username=%s\">%s</a></font></td>", get_username($this->db->f("user_id")), htmlReady(get_fullname($this->db->f("user_id"))));
						$content .= sprintf ("<td width=\"10%%\" align=\"right\" valign=\"top\"><a href=\"$PHP_SELF?edit_evt=".$this->db->f("event_id")."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=\"0\" ".tooltip(_("Diesen Eintrag bearbeiten"))."/>&nbsp;&nbsp;</a><a href=\"$PHP_SELF?kill_evt=".$this->db->f("event_id")."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" ".tooltip(_("Diesen Eintrag löschen"))."/></a></td>");
					}
					$content .= "</tr>\n";
				}
				$content .= "</table>";
			} else {
				$content .= "<font size=\"-1\">"._("Diese Anfrage wurde noch nicht bearbeitet")."</font><br />";
			}
			if ($supporter)
				$content .= "<a href=\"$PHP_SELF?create_evt=$reqObject->id\"><img src=\"".$GLOBALS['ASSETS_URL']."images/add_right.gif\" border=\"0\" ".tooltip (_("Bearbeitungszeit hinzufügen"))."/></a>";
		}
		if ($supporter) {
			if ($edit_req_object == $reqObject->id) {
				$edit = "<br />&nbsp;<input align=\"absmiddle\" type=\"IMAGE\" ".makeButton("uebernehmen", "src")." ".tooltip(_("Daten der Anfrage übernehmen"))." />";
				$edit .= "&nbsp;<a href=\"$PHP_SELF?cancel_edit_req=$reqObject->id\">".makeButton("abbrechen")."</a>&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			if ($reqObject->isDeleteable()) {
				$edit .= "<a href=\"$PHP_SELF?kill_req=$reqObject->id\" style=\"{vertical-align:middle;}\" valign=\"absmiddle\">".makeButton("loeschen")."</a>";
			}
			if ($edit_req_object != $reqObject->id)
				$edit .= "&nbsp;<a href=\"$PHP_SELF?edit_req=$reqObject->id\" style=\"{vertical-align:middle;}\" valign=\"absmiddle\"><img ".makeButton("bearbeiten", "src")." ".tooltip(_("Die Anfrage bearbeiten"))." border=\"0\"/></a>";
		}

		//Daten an Ausgabemodul senden
		$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
	}
}
