<?php
# Lifter002: TODO
/**
* AdminNewsController.class.php
*
*
*
*
* @author	André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access	public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 André Noack <noack@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
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

require_once 'lib/classes/StudipNews.class.php';
require_once 'lib/messaging.inc.php';
require_once 'lib/visual.inc.php';
require_once 'lib/functions.php';

function callback_cmp_newsarray($a, $b) {
	return strnatcasecmp($a['name'], $b['name']); // Case insensitive string comparisons using a "natural order" algorithm
//	return strcasecmp($a['name'], $b['name']); //Binary safe case-insensitive string comparison
}

class AdminNewsController {
	var $db;			  //Datenbankverbindung
	var $modus;
	var $self;	   //enthält $PHP_SELF
	var $msg;		   //Nachricht für msg.inc.php
	var $sms=array();		   //private Nachricht wegen Admin zugriff
	var $news_query=array();
	var $range_detail=array();
	var $search_result=array();
	var $user_id;
	var $news_range;
	var $range_name;
	var $range_type;
	var $full_username;
	var $news_perm=array();
	var $max_col;
	var $xres;

	function AdminNewsController() {
		global $PHP_SELF,$perm,$auth,$news_range_id,$news_range_name;
		$this->self = $PHP_SELF;
		if ($auth->auth["jscript"]) {
			$this->max_col = floor($auth->auth["xres"] / 10 );
			$this->xres=$auth->auth["xres"];
		} else {
			$this->max_col =  64 ; //default für 640x480
			$this->xres=640;
		}
		$this->user_id=$auth->auth["uid"];
		$this->db = new DB_Seminar;
		$this->full_username = get_fullname(false, 'full', false);
		$this->get_news_perm();

		if ($this->news_perm[$news_range_id]["perm"]>=2 OR $perm->have_perm("root")) {
			$this->modus = "admin";
			if ($this->news_perm[$news_range_id]["name"]){
				$news_range_name = $this->news_perm[$news_range_id]["name"];
				$news_range_type = get_object_type($news_range_id);
			}
			elseif ($news_range_id=="studip"){
				$news_range_name="Stud.IP System News";
				$news_range_type='studip';
			}
			elseif ($news_range_id!=""){
				$object_type = get_object_type($news_range_id);
				switch ($object_type){
					case "sem":
					case "inst":
					case "fak":
						$object_name = get_object_name($news_range_id, $object_type);
						$news_range_name = $object_name['name'];
						$news_range_type = $object_type;
					break;

					default:
					$news_range_name = get_fullname($news_range_id, 'full', false);
					$news_range_type = 'user';
				}
			} else {
				$this->news_range=$news_range_id=$this->user_id;
				$this->range_name=$news_range_name=$this->full_username;
				$this->range_type=$news_range_type='user';
			}
		} else {
			$this->modus = "";
			$this->news_range=$news_range_id=$this->user_id;
			$this->range_name=$news_range_name=$this->full_username;
			$this->range_type=$news_range_type='user';
		}
		$this->news_range=$news_range_id;
		$this->range_name=$news_range_name;
		$this->range_type=$news_range_type;
	}

	function get_news_by_range($range) {
		$this->news_query = null;
		if ($range == $this->user_id){
			$this->news_query =& StudipNews::GetNewsByAuthor($this->user_id);
		} else {
			$this->news_query =& StudipNews::GetNewsByRange($range);
		}
	}

	function get_one_news($news_id) {
		global $perm,$_fullname_sql;
		
		$this->news_query = null;
		$news_obj =& new StudipNews($news_id);
		if (!$news_obj->is_new) {
			$this->news_query = $news_obj->content;
			$query="SELECT a.range_id,b.user_id, ". $_fullname_sql['full'] ." AS author,".
					" c.Seminar_id, c.Name AS seminar_name,d.Institut_id,d.Name AS institut_name, IF(d.Institut_id=d.fakultaets_id,'fak','inst') AS inst_type ".
					" FROM news_range AS a LEFT JOIN auth_user_md5 AS b ON (b.user_id=a.range_id) LEFT JOIN user_info USING(user_id) ".
					" LEFT JOIN seminare AS c ON (c.Seminar_id=a.range_id)  LEFT JOIN Institute AS d ON (d.Institut_id=a.range_id) ".
					" WHERE news_id='$news_id'";
			$this->db->query($query);
			while ($this->db->next_record()) {
				if ($this->db->f("user_id")) {
					$this->range_detail[$this->db->f("range_id")]= array("type"=>"pers","name"=>$this->db->f("author"));
				}
				if ($this->db->f("Seminar_id")) {
					$this->range_detail[$this->db->f("range_id")]= array("type"=>"sem","name"=>$this->db->f("seminar_name"));
				}
				if ($this->db->f("Institut_id")) {
					$this->range_detail[$this->db->f("range_id")]= array("type"=>$this->db->f("inst_type"),"name"=>$this->db->f("institut_name"));
				}
			}
			if ($perm->have_perm("root")) {
				$this->db->query("SELECT * FROM news_range WHERE news_id='$news_id' AND range_id='studip'");
				if ($this->db->next_record())
					$this->range_detail[$this->db->f("range_id")]= array("type"=>"sys","name"=>"Stud.IP System News");
				}
			}
	}

	function show_news($id){
		global $auth;
		$cssSw= new cssClassSwitcher();
		$cssSw->enableHover();
		$this->get_news_by_range($id);
		if (!is_array($this->news_query) || !count($this->news_query) ) {
			$this->msg .= "info§" . _("Keine News vorhanden!") . "§";
			return FALSE;
		}
		if ($this->news_perm[$id]["perm"]<2 AND $auth->auth["perm"]!="root") {
			$this->msg .= "error§" . _("Sie d&uuml;rfen diesen News-Bereich nicht administrieren!") . "§";
			return FALSE;
		}
		echo "\n<tr><td width=\"100%\" class=\"blank\"><blockquote>";
		echo "\n<form action=\"".$this->p_self("cmd=kill")."\" method=\"POST\">";
		echo "<table class=\"blank\" align=\"left\" width=\"".round(0.88*$this->xres)."\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";
		echo "\n<tr><td class=\"blank\" colspan=\"4\" align=\"left\" style=\"vertical-align:middle;\"><font size=-1 >" . _("Vorhandene News im gew&auml;hlten Bereich:") . "<br>";
		echo "</td><td class=\"blank\" colspan=\"4\" align=\"right\" style=\"vertical-align:middle;\"><font size=-1 >" . _("Markierte News l&ouml;schen");
		echo "\n<input type=\"IMAGE\" style=\"vertical-align:middle;\" name=\"kill\" " . makeButton("loeschen","src") . tooltip(_("Markierte News löschen")) . " border=\"0\" >&nbsp;&nbsp;</td></tr>";
		echo "\n<tr><th width=\"15%\">" . _("&Uuml;berschrift") . "</th><th width=\"20%\">" . _("Inhalt") . "</th><th width=\"20%\">"
			. _("Autor") . "</th><th width=\"10%\">" . _("Einstelldatum") . "</th><th width=\"10%\">" . _("Ablaufdatum") . "</th><th width=\"15%\">"
			. _("Bearbeiten") . "</th><th width=\"10%\">" . _("L&ouml;schen") . "</th></tr>";
		while (list ($news_id,$details) = each ($this->news_query)) {
			$cssSw->switchClass();
			echo "\n<tr ".$cssSw->getHover()."><td class=\"".$cssSw->getClass()."\" width=\"15%\" align=\"center\"><font size=\"-1\"><b>".htmlReady($details["topic"])."</b></font></td>";
			list ($body,$admin_msg)=explode("<admin_msg>",$details["body"]);
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"25%\" align=\"center\"><font size=\"-1\">".htmlready(mila($body))."</font></td>";
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"15%\" align=\"center\"><font size=\"-1\">".htmlReady($details["author"])."</font></td>";
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"10%\" align=\"center\">".strftime("%d.%m.%y", $details["date"])."</td>";
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"10%\" align=\"center\">".strftime("%d.%m.%y", ($details["date"]+$details["expire"]))."</td>";
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"15%\" align=\"center\"><a href=\"".$this->p_self("cmd=edit&edit_news=$news_id")."\"><img "
				. makeButton("bearbeiten","src") . tooltip(_("Diese News bearbeiten")) . " border=\"0\"></a></td>";
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"10%\" align=\"center\">";
			if ($this->news_perm[$id]["perm"]==3 OR $auth->auth["perm"]=="root" OR $details["user_id"]==$this->user_id)
				echo "<input type=\"CHECKBOX\" name=\"kill_news[]\" value=\"$news_id\" " . tooltip(_("Diese News zum Löschen vormerken"),false) . ">";
			else
				echo "<font color=\"red\">" . _("Nein") . "</font>";
			echo "</td></tr>";
		}
		echo "\n<tr><td class=\"blank\" colspan=8>&nbsp; </td></tr>";
		echo "\n</table></form><br><br></blockquote></td></tr>";
		return TRUE;
	}

	function edit_news($news_id=0) {
		global $perm;
		$aktuell=mktime(0,0,0,strftime("%m",time()),strftime("%d",time()),strftime("%y",time()));
		if ($news_id && $news_id != "new_entry")
			$this->get_one_news($news_id);
		else {
			$this->news_query = array("news_id"=>"new_entry",
										"topic" => "",
										"body" => "",
										"date" => $aktuell,
										"user_id" => $this->user_id,
										"author" => $this->full_username,
										"expire" => 604800,
										"allow_comments" => 0);
			if ($perm->have_perm("admin")){
				$this->search_result[$this->news_range] = array('type' => $this->range_type, 'name' => $this->range_name);
			}
		}
		if (isset($_REQUEST['news_range_search_x'])) {
			$this->news_query['topic'] = stripslashes($_REQUEST['topic']);
			$this->news_query['body'] = stripslashes($_REQUEST['body']);
			$this->news_query['date'] = $_REQUEST['date'];
			$this->news_query['expire'] = $_REQUEST['expire'];
			$this->news_query['allow_comments'] = $_REQUEST['allow_comments'];
		}
		if ($this->news_query["user_id"]==$this->user_id)
			$this->modus="";
		echo "\n<tr> <td class=\"blank\" align=\"center\"><br />";
		echo "\n<form action=\"".$this->p_self("cmd=news_edit")."\" method=\"POST\">";
		echo "\n<input type=\"HIDDEN\" name=\"news_id\" value=\"".$this->news_query["news_id"]."\">";
		echo "\n<input type=\"HIDDEN\" name=\"user_id\" value=\"".$this->news_query["user_id"]."\">";
		echo "\n<input type=\"HIDDEN\" name=\"author\" value=\"".$this->news_query["author"]."\">";
		echo "\n</td></tr>";
		echo "\n<tr> <td class=\"blank\" align=\"center\"><br />";
		echo "\n<table width=\"99%\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\">";
		echo "\n<tr><td class=\"steel1\" width=\"70%\"><b>" . _("Autor:") . "</b>&nbsp;". htmlReady($this->news_query["author"]) ."<br><br><b>" . _("&Uuml;berschrift")
			. "</b><br><input type=\"TEXT\" style=\"width: 100%\" size=\"".floor($this->max_col*.5*.8)."\" maxlength=\"255\" name=\"topic\" value=\""
			.htmlReady($this->news_query["topic"])."\"><br>";
		list ($body,$admin_msg)=explode("<admin_msg>",$this->news_query["body"]);
		echo "\n<br><b>" . _("Inhalt") . "</b><br><textarea name=\"body\" style=\"width: 100%\" cols=\"".floor($this->max_col*.8*.8)."\" rows=\"10\"	  wrap=\"virtual\">"
			.htmlReady($body)."</textarea><br></td>";
		echo "\n<td class=\"steelgraulight\" width=\"30%\">" . _("Geben Sie hier die &Uuml;berschrift und den Inhalt Ihrer News ein.")
			. "<br><br>" . _("Im unteren Bereich k&ouml;nnen Sie ausw&auml;hlen, in welchen Bereichen Ihre News angezeigt wird.");
		echo "\n<br><br>" . _("Klicken Sie danach hier, um die &Auml;nderungen zu &uuml;bernehmen.") . "<br><br><center>"
			. "<INPUT TYPE=\"IMAGE\" name=\"news_submit\" " . makeButton("uebernehmen","src") . tooltip(_("Änderungen übernehmen")) ."  border=\"0\" ></center></td></tr>";

		$news_date = $this->news_query['date'];
		if ($news_date != $aktuell) {
			$date_offset = 0;
		} else {
			$date_offset = 1;
		}
		echo "\n<tr><td class=\"blank\" colspan=\"2\">" . _("Einstelldatum:") . " <select name=\"date\"><option value=\"".$news_date."\" selected>".strftime("%d.%m.%y", $news_date)."</option>";
		for ($i = $date_offset; $i <= $date_offset+13; ++$i) {
			$temp = mktime(0,0,0,strftime("%m",$aktuell),strftime("%d",$aktuell) + $i,strftime("%y",$aktuell));
			echo "\n<option value=\"".$temp."\">".strftime("%d.%m.%y",$temp)."</option>";
		}
		echo "</select>&nbsp;&nbsp;&nbsp;" . _("G&uuml;ltigkeitsdauer:") . " <select name=\"expire\">";
		if ($this->news_query["news_id"] != "new_entry"){
			if ($date_offset){
				$this->news_query["expire"] = ($this->news_query['date'] + $this->news_query["expire"]) - $news_date;
				if ($this->news_query['expire'] < mktime(23,59,59,strftime("%m",$news_date),strftime("%d",$news_date),strftime("%y",$news_date)) - $news_date){
					$this->news_query['expire'] = mktime(23,59,59,strftime("%m",$news_date),strftime("%d",$news_date),strftime("%y",$news_date)) - $news_date;
				}
			}
			echo "\n<option value=\"" . $this->news_query["expire"] . "\" selected>";
			printf(_("bis zum %s"), strftime("%d.%m.%y",($this->news_query["expire"] + $news_date)));
			echo "</option>";
		}
		for ($i = 2; $i <= 24; $i += 2) {
			$temp = mktime(23,59,59,strftime("%m",$news_date),strftime("%d",$news_date) + ($i * 7),strftime("%y",$news_date)) - $news_date;
			echo "\n<option value=\"" . $temp . "\" ";
			if ($this->news_query["expire"] == $temp)
				echo"selected";
			echo ">";
			printf(_("%s Wochen (%s)"),$i ,strftime("%d.%m.%y",($temp + $news_date)));
			echo "</option>";
		}
		echo "</select></td></tr>";
		echo "<tr><td class=\"blank\">"._("Kommentare zulassen")."&nbsp;<input name=\"allow_comments\" value=\"1\" type=\"checkbox\" style=\"vertical-align:middle\"";
		if ($this->news_query["allow_comments"]) print " checked";
		echo "></td></tr>";
		echo "\n</table></td></tr>";
		echo "\n<tr><td class=\"blank\"><hr width=\"99%\"></td></tr>";
		echo "\n<tr><td class=\"blank\">&nbsp; <b>" . _("In diesen Bereichen wird die News angezeigt:") . "</b><br /><br /></td></tr>";
		echo "\n<tr><td class=\"blank\"><table class=\"blank\" width=\"99%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">";
		$cssSw=new cssClassSwitcher;
		$cssSw->enableHover();
		if ($perm->have_perm("root")) {
			echo "\n<tr><th width=\"90%\" align=\"left\">" . _("System-Bereich:") . "</th><th align=\"center\" width=\"10%\">" . _("Anzeigen ?") . "</th></tr>";
			echo "\n<tr ".$cssSw->getHover()."><td	".$cssSw->getFullClass()." width=\"90%\">" . _("Systemweite News") . "</td>";
			echo "\n<td	".$cssSw->getFullClass()." width=\"10%\" align=\"center\"><input type=\"CHECKBOX\" name=\"add_range[]\" value=\"studip\"";
			if ($this->range_detail["studip"]["type"] OR ($this->news_range=="studip" AND $news_id=="new_entry"))
				echo "checked";
			echo "></td></tr>";
		}
		echo "\n<tr><th width=\"90%\" align=\"left\">" . _("Pers&ouml;nlicher Bereich:") . "</th><th align=\"center\" width=\"10%\">" . _("Anzeigen ?") . "</th></tr>";
		echo "\n<tr ".$cssSw->getHover()."><td ".$cssSw->getFullClass()." width=\"90%\">".htmlReady($this->news_query["author"])."</td>";
		echo "\n<td	 ".$cssSw->getFullClass()." width=\"10%\" align=\"center\">";
		if ($this->news_perm[$this->news_query["user_id"]]["perm"] OR $this->news_query["user_id"]==$this->user_id) {
			echo"<input type=\"CHECKBOX\" name=\"add_range[]\" value=\"".$this->news_query["user_id"]."\"";
			if ($this->range_detail[$this->news_query["user_id"]]["type"] OR ($this->news_range==$this->user_id AND $news_id=="new_entry"))
				echo "checked";
			echo "></td></tr>";
		} else {
			if ($this->range_detail[$this->news_query["user_id"]]["type"])
				echo _("Ja") . "<input type=\"HIDDEN\" name=\"add_range[]\" value=\"".$this->news_query["user_id"]."\">";
			else
				echo _("Nein");
			echo"</td></tr>";
		}
		$this->list_range_details("sem");
		$this->list_range_details("inst");
		$this->list_range_details("fak");
		echo "\n<tr><td class=\"blank\"> &nbsp; </td>";
		echo "\n</td></tr>";
		if ($perm->have_perm("admin")) {
			echo "<tr><td class=\"blank\" colspan=2>";
			echo "<table class=\"blank\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">";
			echo "\n<tr><td class=\"blank\"><b>" . _("Einen weiteren Bereich hinzuf&uuml;gen:") . "<br /></td></tr>";
			echo "\n<tr><td class=\"steel1\"><font size=-1>" . _("Hier k&ouml;nnen Sie weitere Bereiche, auf die Sie Zugriff haben, der Auswahl hinzuf&uuml;gen") . "</font><br />";
			echo "<br><input style=\"vertical-align:middle;\" type=\"TEXT\"  name=\"search\" size=\"20\">&nbsp; <input type=\"IMAGE\" name=\"news_range_search\""
				. makeButton("suchestarten","src") . tooltip(_("Suche starten")) . " border=\"0\" style=\"vertical-align:middle;\"></div></td></tr></form></table><br />";
		}
		echo "</form></table>";
	}


	function update_news($news_id,$author,$topic,$body,$user_id,$date,$expire,$add_range, $allow_comments) {
		global $auth;
		if ($news_id) {
			if($this->check_news_perm($news_id)) {
				if ($news_id == "new_entry") {
					$news_obj =& new StudipNews();
					$flag = TRUE;
					$news_obj->setValue('user_id', $this->user_id);
					$news_obj->setValue('author', $this->full_username);
					$news_obj->setValue('date', ($date ? $date : time()));
					$news_obj->setValue('topic', stripslashes($topic));
					$news_obj->setValue('body', stripslashes($body));
					$news_obj->setValue('expire', $expire);
					$news_obj->setValue('allow_comments', $allow_comments);
					if ($news_obj->store()){
						$this->msg .= "msg§" . _("Ok, Ihre neue News wurde gespeichert!") . "§";
					}
				} else {
					if ($this->news_query["topic"]!=stripslashes($topic)
					OR $this->news_query["body"]!=stripslashes($body)
					OR $this->news_query["date"]!=$date
					OR $this->news_query["allow_comments"]!=$allow_comments
					OR $this->news_query["expire"]!=$expire) {
						$news_obj =& new StudipNews($news_id);
						if ($this->news_query['date'] != $date && $this->news_query["expire"] == $expire){
							$expire = ($this->news_query['date'] + $this->news_query["expire"]) - $date;
						}
						$news_obj->setValue('date', $date);
						$news_obj->setValue('topic', stripslashes($topic));
						$news_obj->setValue('body', stripslashes($body));
						$news_obj->setValue('expire', $expire);
						$news_obj->setValue('allow_comments', $allow_comments);
						if ($this->modus == "admin" && $user_id != $this->user_id) {
							$news_obj->setValue('chdate_uid', $this->user_id);
						} else {
							$news_obj->setValue('chdate_uid', '');
						}
						if ($news_obj->store()) {
							$this->msg .= "msg§ " . _("Die News wurde ver&auml;ndert!") . "§";
							if ($this->modus=="admin" AND $user_id!=$this->user_id) {
								setTempLanguage($user_id);
								$this->sms[$user_id] = sprintf(_("Ihre News \"%s\" wurde von einem Administrator verändert!"),$this->news_query["topic"])
													."\n" . get_fullname() . ' ('.get_username().')'. "\n";
								restoreLanguage();
							}
						}
					}
					if ($add_range) {
						if (!is_object($news_obj)){
							$news_obj =& new StudipNews($news_id);
						}
						reset($this->range_detail);
						while (list ($range,$details)=each($this->range_detail)) {
							if(!in_array($range,$add_range)) {
								if($this->news_perm[$range]["perm"] OR $auth->auth["perm"]=="root") {
									if ($news_obj->deleteRange($range)) {
										if ($this->modus=="admin" AND $user_id!=$this->user_id) {
											setTempLanguage($user_id);
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde gelöscht."),$details["name"]);
											restoreLanguage();
										} else {
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde gelöscht."),$details["name"]);
										}
									}
								}
							}
						}
					}
				}
				if (!$add_range) {
					$this->msg="info§" . _("Sie haben keinen Bereich für Ihre News ausgew&auml;hlt. Ihre News wird damit nirgends angezeigt!")."§";
					return $news_id;
				} else {
					for ($i=0;$i<count($add_range);$i++) {
						if (!$this->range_detail[$add_range[$i]]["name"]) {
							if($this->news_perm[$add_range[$i]]["perm"] OR $auth->auth["perm"]=="root") {
								if ($news_obj->addRange($add_range[$i])) {
									if ( !($range_name = $this->news_perm[$add_range[$i]]["name"]) ){
										list($range_name,) = array_values(get_object_name($add_range[$i], get_object_type($add_range[$i])));
									}
									if ($this->modus=="admin" AND $user_id!=$this->user_id) {
											setTempLanguage($user_id);
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde hinzugefügt."),$range_name);
											restoreLanguage();
										} else {
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde hinzugefügt."),$range_name);
										}
								}
							}
						}
					}
					if ($msg) {
						$this->msg.="msg§".htmlReady($msg,true,true)."§";
						if ($this->modus=="admin" AND $user_id!=$this->user_id) {
							if ($this->sms[$user_id])
								$this->sms[$user_id] .= $msg;
							else
								$this->sms[$user_id] = sprintf(_("Ihre News \"%s\" wurde von einem Administrator verändert!"),$this->news_query["topic"])
													."\n" . get_fullname() . ' ('.get_username().')'. "\n" . $msg;
						}
					}
				$news_obj->storeRanges();
				}
			}
		} else {
			$this->msg="error§" . _("Fehler: Keine news_id &uuml;bergeben!") . "§";
		}
		return FALSE;
	}

	function kill_news($kill_news) {
		if ($kill_news) {
			if (!is_array($kill_news))
				$kill_news=array($kill_news);
			$kill_count=0;
			for ($i=0;$i<count($kill_news);$i++) {
				if ($this->check_news_perm($kill_news[$i],3)) {
					$news =& new StudipNews($kill_news[$i]);
					if ($this->modus=="admin" AND $this->news_query["user_id"]!=$this->user_id) {
						setTempLanguage($this->news_query["user_id"]);
						$this->sms[$this->news_query["user_id"]] .= sprintf(_("Ihre News \"%s\" wurde von einer Administratorin oder einem Administrator gelöscht!")
																	,$news->getValue('topic')) ."\n" . get_fullname() . ' ('.get_username().')';
						restoreLanguage();
					}
					$kill_count += $news->delete();
				}

			}
			$this->msg.="msg§" . sprintf(_("Es wurden %s News gel&ouml;scht!"),$kill_count) . "§";
		}
		else $this->msg.="error§" . _("Sie haben keine News zum l&ouml;schen ausgew&auml;hlt!") . "§";
	}

	function search_range($search_str = false) {
		$this->search_result = (array)$this->search_result + (array)search_range($search_str, true);
		if (is_array($this->search_result) && count($this->search_result)){
			$query="SELECT range_id,COUNT(range_id) AS anzahl FROM news_range WHERE range_id IN ('".implode("','",array_keys($this->search_result))."') GROUP BY range_id";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("range_id")]["anzahl"]=$this->db->f("anzahl");
			}
		}
	}
		
	//Hilfsfunktionen
	function list_range_details($type) {
		global $auth,$perm;
		$cssSw=new cssClassSwitcher();
		$cssSw->enableHover();
		$output="";
		$output[0]="\n<tr><th width=\"90%\" align=\"left\">";
		switch ($type) {
			case "sem" : $output[0].= _("Veranstaltungen");break;
			case "inst" : $output[0].= _("Einrichtungen"); $query="SELECT Institute.Institut_id AS id,Name AS name FROM user_inst LEFT JOIN Institute ON(user_inst.Institut_id=Institute.Institut_id AND Institute.Institut_id!=fakultaets_id) WHERE NOT ISNULL(Institute.Institut_id) AND user_inst.user_id='".$this->user_id."' AND user_inst.inst_perms='autor'";$add=" AND user_inst.Institut_id ";break;
			
			case "fak" : $output[0].= _("Fakult&auml;ten"); $query="SELECT Institute.Institut_id AS id,Name AS name FROM user_inst LEFT JOIN Institute ON(user_inst.Institut_id=Institute.Institut_id AND Institute.Institut_id=fakultaets_id) WHERE NOT ISNULL(Institute.Institut_id) AND user_inst.user_id='".$this->user_id."' AND user_inst.inst_perms='autor'";$add=" AND user_inst.Institut_id ";break;
		}
		$output[0] .= '</th><th align="center" width="10%">' . _("Anzeigen ?") . '</th></tr>';
		$not_in = "";

		reset($this->range_detail);
		
		while (list ($range,$details) = each ($this->range_detail)) {
			if ($details["type"]==$type) {
				$cssSw->switchClass();
				$output[1].= "\n<tr ".$cssSw->getHover()."><td class=\"".$cssSw->getClass()."\" width=\"90%\">".htmlReady($details["name"])."</td>\n<td class=\"".$cssSw->getClass()."\"  width=\"10%\" align=\"center\">";
				//$output[1].= "\n<td width=\"10%\" align=\"center\"><input type=\"CHECKBOX\" name=\"add_range[]\" value=\"".$range."\" checked></td></tr>";
				if ($this->news_perm[$range]["perm"] OR $auth->auth["perm"]=="root") {
					$output[1].="<input type=\"CHECKBOX\" name=\"add_range[]\" value=\"".$range."\" checked></td></tr>";
				} else {
					$output[1].=_("Ja") . "<input type=\"HIDDEN\" name=\"add_range[]\" value=\"$range\"></td></tr>";
				}
				if ($not_in)
					$not_in=$not_in.",";
				$not_in=$not_in."'$range'";
				if ($perm->have_perm("tutor") && is_array($this->search_result)) {
					$this->search_result[$range]="used";
				}
			}
		}
		if ($not_in)
			$add .= "NOT IN ($not_in)";
		else
			$add = '';
		if ($perm->have_perm('tutor') && is_array($this->search_result)) {
			reset($this->search_result);
			while (list ($range,$details) = each($this->search_result)) {
				if ($details["type"]==$type) {
					$cssSw->switchClass();
					$output[1].= "\n<tr ".$cssSw->getHover().'><td	'.$cssSw->getFullClass(). '  width="90%">' .htmlReady($details['name']).'</td>';
					$output[1].= "\n<td  ".$cssSw->getFullClass(). ' width="10%" align="center"><input type="CHECKBOX" name="add_range[]" value="' . $range. '"';
					if ($range == $this->news_range AND $this->news_query['news_id'] == 'new_entry')
						$output[1] .= ' checked ';
					$output[1] .= '></td></tr>';
				}
			}
		} else {
			if ($query){
				$this->db->query($query.$add.' ORDER BY name');
				while($this->db->next_record()) {
					$cssSw->switchClass();
					$output[1].= "\n<tr ".$cssSw->getHover().'><td	'.$cssSw->getFullClass(). ' width="90%">'.$this->db->f('name').'</td>';
					$output[1].= "\n<td ".$cssSw->getFullClass().' width="10%" align="center"><input type="CHECKBOX" name="add_range[]" value="' . $this->db->f('id').'"';
					if ($this->db->f('id') == $this->news_range AND $this->news_query['news_id'] == 'new_entry')
						$output[1].= ' checked ';
					$output[1] .= '></td></tr>';
				}
			}
		}
		if ($output[1])
			echo $output[0].$output[1];
	}

	function p_self($par="") {
		return "$this->self?$par";
	}

	function get_news_perm() {
		global $auth,$perm;
		$this->news_perm[$this->user_id]=array("name"=>$this->full_username,"perm"=>3);
		if ($auth->auth["perm"]=="root"){
			$this->news_perm["studip"]=array("name"=>"Stud.IP News","perm"=>3);
		} else {
			if (in_array($auth->auth["perm"], array("dozent","tutor"))){
				$query="SELECT seminare.Seminar_id AS id,seminar_user.status,Name FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE seminar_user.user_id='".$this->user_id."' AND seminar_user.status IN ('dozent','tutor')";
				$this->db->query($query);
				while($this->db->next_record()) {
					if ($this->db->f("status")=="tutor") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>2);
					if ($this->db->f("status")=="dozent") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>2);
				}
			}
			if ($auth->auth["perm"]=="admin") {
				$query="SELECT b.Seminar_id AS id,b.Name from user_inst AS a LEFT JOIN	 seminare AS b USING (Institut_id) WHERE a.user_id='$this->user_id' AND a.inst_perms='admin'";
				$this->db->query($query);
				while($this->db->next_record()) {
					$this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>3);
				}
			}
			$query="SELECT Institute.Institut_id AS id,Name,user_inst.inst_perms AS status	FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_inst.user_id='".$this->user_id."' AND user_inst.inst_perms IN ('admin','dozent','tutor','autor')";
			$this->db->query($query);
			while($this->db->next_record()) {
				if ($this->db->f("status")=="tutor" OR $this->db->f("status")=="autor" OR $this->db->f("status")=="dozent") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>2);
				if ($this->db->f("status")=="admin") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>3);
			}
			$query = "SELECT b.Institut_id,b.Name,a.inst_perms AS status FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
			WHERE a.user_id='$this->user_id' AND a.inst_perms IN ('admin','autor') AND NOT ISNULL(b.Institut_id)";
			$this->db->query($query);
			while($this->db->next_record()) {
				if ($this->db->f("status")=="autor") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>1);
				if ($this->db->f("status")=="admin") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>3);
			}
			if ($perm->is_fak_admin()){
				$query = "SELECT d.Seminar_id,d.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
				LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id) LEFT JOIN seminare d ON(d.institut_id=c.institut_id)
				WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id)";
				$this->db->query($query);
				while($this->db->next_record()){
					$this->news_perm[$this->db->f("Seminar_id")]=array("name"=>$this->db->f("Name"),"perm"=>3);
				}
				$query = "SELECT c.Institut_id,c.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
				LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id)
				WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id)";
				$this->db->query($query);
				while($this->db->next_record()){
					$this->news_perm[$this->db->f("Institut_id")]=array("name"=>$this->db->f("Name"),"perm"=>3);
				}
			}
		}
	}

	function check_news_perm($news_id,$check=2) {
		global $auth;
		if ($news_id=="new_entry")
			return TRUE;
		$this->get_one_news($news_id);
		if ($auth->auth["perm"]=="root")
			return TRUE;
		if ($this->news_query["user_id"]==$this->user_id)
			return TRUE;
		elseif ($this->modus!="admin")
			$this->msg.="error§" . _("Sie d&uuml;rfen nur Ihre eigenen News ver&auml;ndern") . "§";
		if ($this->modus=="admin") {
			reset($this->range_detail);
			while (list ($range,$details) = each ($this->range_detail)) {
				if ($this->news_perm[$range]["perm"]>=$check)
					return TRUE;
			}
			$this->msg.="error§" . _("Sie haben keine Berechtigung diese News zu bearbeiten") . "§";
		}
		return FALSE;
	}

	function get_news_range_perm($range_id){
		return ($GLOBALS['perm']->get_perm() == 'root' ? 3 : $this->news_perm[$range_id]["perm"]);
	}
	
	function send_sms() {
		$msg_object = new messaging();
		while (list($user_id,$msg) = each($this->sms)) {
			$msg_object->insert_message(mysql_escape_string($msg), get_username($user_id) , "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("News geändert"));
		}
	}

}	//Ende Klassendefintion
?>
