<?php
/*
admin_news.php - Ändern der News von Stud.IP
Copyright (C) 2001	André Noack <andre.noack@gmx.net>

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
//$Id$
page_open(array("sess"=> "Seminar_Session", "auth" =>"Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("autor");
require_once "$ABSOLUTE_PATH_STUDIP/messaging.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page


// Klassendefinition
class studip_news {
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
	var $full_username;
	var $news_perm=array();
	var $max_col;
	var $xres;

	function studip_news() {
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
				$news_range_name=$this->news_perm[$news_range_id]["name"];
			}
			elseif ($news_range_id=="studip"){
				$news_range_name="Stud.IP System News";
			}
			elseif ($news_range_id!=""){
				$object_type = get_object_type($news_range_id);
				switch ($object_type){
					case "sem":
					$query="SELECT Name FROM seminare WHERE Seminar_id='$news_range_id'";
					$this->db->query($query);
					$this->db->next_record();
					$news_range_name = $this->db->f("Name");
					break;

					case "inst":
					case "fak":
					$query="SELECT Name FROM Institute WHERE Institut_id='$news_range_id'";
					$this->db->query($query);
					$this->db->next_record();
					$news_range_name = $this->db->f("Name");
					break;

					default:
					$news_range_name = get_fullname($news_range_id, 'full', false);
				}
			} else {
				$this->news_range=$news_range_id=$this->user_id;
				$this->range_name=$news_range_name=$this->full_username;
			}
		} else {
			$this->modus = "";
			$this->news_range=$news_range_id=$this->user_id;
			$this->range_name=$news_range_name=$this->full_username;
		}
		$this->news_range=$news_range_id;
		$this->range_name=$news_range_name;
	}

	function get_news_by_range($range,$limit) {
		$this->news_query="";
		if ($range==$this->user_id)
			$query="SELECT * FROM news WHERE user_id='$range' ORDER BY date DESC";
		else
			$query="SELECT * FROM news_range LEFT JOIN news USING (news_id) WHERE news_range.range_id='$range' ORDER BY date DESC";
		if ($limit)
			$query=$query." LIMIT $limit";
		$this->db->query($query);
		while ($this->db->next_record()) {
			$this->news_query[$this->db->f("news_id")] = array ("topic" => $this->db->f("topic"), "body" => $this->db->f("body"), 
														"date" => $this->db->f("date"), "user_id" =>$this->db->f("user_id"), 
														"author" =>$this->db->f("author"),"expire" =>$this->db->f("expire"));
		}
	}

	function get_one_news($news_id) {
		global $perm,$_fullname_sql;
		$this->news_query="";
		$this->db->query("SELECT * FROM news WHERE news_id='$news_id'");
		if ($this->db->next_record()) {
			$this->news_query = array("news_id"=>$news_id, "topic" => $this->db->f("topic"), "body" => $this->db->f("body"), 
									"date" => $this->db->f("date"), "user_id" =>$this->db->f("user_id"), "author" =>$this->db->f("author"),
									"expire" =>$this->db->f("expire"));
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
		$this->get_news_by_range($id,$limit=100);
		if (!is_array($this->news_query)) {
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
		if ($news_id AND $news_id!="new_entry")
			$this->get_one_news($news_id);
		else {
			$this->news_query = array("news_id"=>"new_entry", "topic" => "", "body" => "", "date" => $aktuell, "user_id" =>$this->user_id, "author" =>$this->full_username, "expire" => "604800");
			if ($perm->have_perm("admin")){
				$this->search_range(mysql_escape_string($this->range_name));
			}
		}
		if ($this->news_query["user_id"]==$this->user_id) 
			$this->modus="";
		echo "\n<tr> <td class=\"blank\" align=\"center\"><br />";
		echo "\n<form action=\"".$this->p_self("cmd=news_submit")."\" method=\"POST\">";
		echo "\n<input type=\"HIDDEN\" name=\"news_id\" value=\"".$this->news_query["news_id"]."\">";
		echo "\n<input type=\"HIDDEN\" name=\"user_id\" value=\"".$this->news_query["user_id"]."\">";
		echo "\n<input type=\"HIDDEN\" name=\"author\" value=\"".$this->news_query["author"]."\">";
		echo "\n</td></tr>";
		echo "\n<tr> <td class=\"blank\" align=\"center\"><br />";
		echo "\n<table width=\"99%\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\">";
		echo "\n<tr><td class=\"steel1\" width=\"70%\"><b>" . _("Autor:") . "</b>&nbsp;". htmlReady($this->news_query["author"]) ."<br><br><b>" . _("&Uuml;berschrift")
			. "</b><br><input type=\"TEXT\" style=\"width: 50%\" size=\"".floor($this->max_col*.5*.8)."\" maxlength=\"255\" name=\"topic\" value=\""
			.htmlReady($this->news_query["topic"])."\"><br>";
		list ($body,$admin_msg)=explode("<admin_msg>",$this->news_query["body"]);
		echo "\n<br><b>" . _("Inhalt") . "</b><br><textarea name=\"body\" style=\"width: 100%\" cols=\"".floor($this->max_col*.8*.8)."\" rows=\"10\"	  wrap=\"virtual\">"
			.htmlReady($body)."</textarea><br></td>";
		echo "\n<td class=\"steelgraulight\" width=\"30%\">" . _("Geben Sie hier die &Uuml;berschrift und den Inhalt Ihrer News ein.") 
			. "<br><br>" . _("Im unteren Bereich k&ouml;nnen Sie ausw&auml;hlen, in welchen Bereichen Ihre News angezeigt wird.");
		echo "\n<br><br>" . _("Klicken Sie danach hier, um die &Auml;nderungen zu &uuml;bernehmen.") . "<br><br><center>"
			. "<INPUT TYPE=\"IMAGE\" name=\"news_submit\" " . makeButton("uebernehmen","src") . tooltip(_("Änderungen übernehmen")) ."  border=\"0\" ></center></td></tr>";
		if ($this->news_query['date'] > $aktuell) {
			$news_date = $this->news_query['date'];
			$date_offset = 0;
		} else {
			$news_date = $aktuell;
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
		for ($i = 2; $i <= 12; $i += 2) {
			$temp = mktime(23,59,59,strftime("%m",$news_date),strftime("%d",$news_date) + ($i * 7),strftime("%y",$news_date)) - $news_date;
			echo "\n<option value=\"" . $temp . "\" ";
			if ($this->news_query["expire"] == $temp) 
				echo"selected";
			echo ">";
			printf(_("%s Wochen (%s)"),$i ,strftime("%d.%m.%y",($temp + $news_date)));
			echo "</option>";
		}
		echo "</select></td></tr></table></td></tr>";
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
		echo "\n<tr ".$cssSw->getHover()."><td ".$cssSw->getFullClass()." width=\"90%\">".$this->news_query["author"]."</td>";
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
		echo "\n</form></td></tr>";
		if ($perm->have_perm("admin")) {
			echo "<tr><td class=\"blank\" colspan=2>";
			echo "<table class=\"blank\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">";
			echo "\n<form action=\"".$this->p_self("cmd=edit")."\" method=\"POST\"><input type=\"HIDDEN\" name=\"edit_news\" value=\"".$this->news_query["news_id"]."\">";
			echo "\n<tr><td class=\"blank\"><b>" . _("Einen weiteren Bereich hinzuf&uuml;gen:") . "<br /></td></tr>";
			echo "\n<tr><td class=\"steel1\"><font size=-1>" . _("Hier k&ouml;nnen Sie weitere Bereiche, auf die Sie Zugriff haben, der Auswahl hinzuf&uuml;gen") . "</font><br />";
			echo "<br><input style=\"vertical-align:middle;\" type=\"TEXT\"  name=\"search\" size=\"20\">&nbsp; <input type=\"IMAGE\" name=\"submit\""
				. makeButton("suchestarten","src") . tooltip(_("Suche starten")) . " border=\"0\" style=\"vertical-align:middle;\"></div></td></tr></form></table><br />";
		}
		echo "</table>";
	}


	function update_news($news_id,$author,$topic,$body,$user_id,$date,$expire,$add_range) {
		global $auth;
		if ($news_id) {
			if($this->check_news_perm($news_id)) {
				if ($news_id=="new_entry") {
					$seed="blafasel3547";
					$news_id=md5(uniqid($seed));
					$flag=TRUE;
					$user_id=$this->user_id;
					$author=$this->full_username;
					if (!$date) 
						$date=time();
					$this->db->query("INSERT INTO news (news_id,author,topic,body,user_id,date,expire) VALUES ('$news_id','$author','$topic','$body','$user_id','$date','$expire')");
					if ($this->db->affected_rows()) 
					$this->msg .= "msg§" . _("Ok, Ihre neue News wurde gespeichert!") . "§";
				} else {
					if ($this->news_query["topic"]!=stripslashes($topic)
					OR $this->news_query["body"]!=stripslashes($body) 
					OR $this->news_query["date"]!=$date 
					OR $this->news_query["expire"]!=$expire) {
						if ($this->news_query['date'] != $date && $this->news_query["expire"] == $expire){
							$expire = ($this->news_query['date'] + $this->news_query["expire"]) - $date;
						}
						
						if ($this->modus=="admin" AND $user_id!=$this->user_id) {
							$admin="<admin_msg>";
							$admin .= sprintf(_("Zuletzt aktualisiert von %s (%s) am %s"),$this->full_username,$auth->auth["uname"],date("d.m.y",time()));
							$body.=$admin;
						}
						$this->db->query("UPDATE news SET topic='$topic',body='$body',date='$date',expire='$expire' WHERE news_id='$news_id'");
						if ($this->db->affected_rows()) {
							$this->msg .= "msg§ " . _("Die News wurde ver&auml;ndert!") . "§";
							if ($this->modus=="admin" AND $user_id!=$this->user_id) {
								setTempLanguage($user_id);
								$this->sms[$user_id] = sprintf(_("Ihre News \"%s\" wurde von einer Administratorin oder einem Administrator verändert!"),$this->news_query["topic"]) ."\n";
								restoreLanguage();
							}
						}
					}
					if ($add_range) {
						reset($this->range_detail);
						while (list ($range,$details)=each($this->range_detail)) {
							if(!in_array($range,$add_range)) {
								if($this->news_perm[$range]["perm"] OR $auth->auth["perm"]=="root") {
									$this->db->query("DELETE FROM news_range WHERE news_id='$news_id' AND range_id='$range'");
									if ($this->db->affected_rows()) {
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
								$this->db->query("INSERT INTO news_range (news_id,range_id) VALUES ('$news_id','$add_range[$i]')");
								if ($this->db->affected_rows()) {
									if ($this->modus=="admin" AND $user_id!=$this->user_id) {
											setTempLanguage($user_id);
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde hinzugefügt."),$this->news_perm[$add_range[$i]]["name"]);
											restoreLanguage();
										} else {
											$msg .="\n" .sprintf(_("Der Bereich: %s wurde hinzugefügt."),$this->news_perm[$add_range[$i]]["name"]);
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
								$this->sms[$user_id] = sprintf(_("Ihre News \"%s\" wurde von einem Administrator verändert!"),$this->news_query["topic"]) ."\n" . $msg;
						}
					}
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
					$this->db->query("DELETE FROM news WHERE news_id='$kill_news[$i]'");
					$this->db->query("DELETE FROM news_range WHERE news_id='$kill_news[$i]'");
					if ($this->modus=="admin" AND $this->news_query["user_id"]!=$this->user_id) {
						setTempLanguage($this->news_query["user_id"]);
						$this->sms[$this->news_query["user_id"]] .= sprintf(_("Ihre News \"%s\" wurde von einer Administratorin oder einem Administrator gelöscht!"),$this->news_query["topic"]) ."\n";
						restoreLanguage();
					}
					$kill_count++;
				}
				
			}
			$this->msg.="msg§" . sprintf(_("Es wurden %s News gel&ouml;scht!"),$kill_count) . "§";
		}
		else $this->msg.="error§" . _("Sie haben keine News zum l&ouml;schen ausgew&auml;hlt!") . "§";
	}

	function search_range($search_str) {
		global $perm,$auth,$_fullname_sql;
		if ($perm->have_perm("root")) {
			$query="SELECT a.user_id,". $_fullname_sql['full'] . " AS full_name,username FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE CONCAT(Vorname,' ',Nachname,' ',username) LIKE '%$search_str%'";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("user_id")]=array("type"=>"user","name"=>$this->db->f("full_name")."(".$this->db->f("username").")");
			}
			$query="SELECT Seminar_id,Name FROM seminare WHERE Name LIKE '%$search_str%'";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Seminar_id")]=array("type"=>"sem","name"=>$this->db->f("Name"));
			}
			$query="SELECT Institut_id,Name, IF(Institut_id=fakultaets_id,'fak','inst') AS inst_type FROM Institute WHERE Name LIKE '%$search_str%'";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Institut_id")]=array("type"=>$this->db->f("inst_type"),"name"=>$this->db->f("Name"));
			}
		} elseif ($perm->have_perm("admin")) {
			$query="SELECT b.Seminar_id,b.Name from user_inst AS a LEFT JOIN  seminare AS b USING (Institut_id) WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND	b.Name LIKE '%$search_str%'";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Seminar_id")]=array("type"=>"sem","name"=>$this->db->f("Name"));
			}
			$query="SELECT b.Institut_id,b.Name from user_inst AS a LEFT JOIN	Institute AS b USING (Institut_id) WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND a.institut_id!=b.fakultaets_id AND  b.Name LIKE '%$search_str%'";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Institut_id")]=array("type"=>"inst","name"=>$this->db->f("Name"));
			}
			if ($perm->is_fak_admin()) {
				$query = "SELECT d.Seminar_id,d.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)  
				LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id) LEFT JOIN seminare d USING(Institut_id) 
				WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND d.Name LIKE '%$search_str%'";
				$this->db->query($query);
				while($this->db->next_record()){
					$this->search_result[$this->db->f("Seminar_id")]=array("type"=>"sem","name"=>$this->db->f("Name"));
				}
				$query = "SELECT c.Institut_id,c.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)  
				LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id) 
				WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND c.Name LIKE '%$search_str%'";
				$this->db->query($query);
				while($this->db->next_record()){
					$this->search_result[$this->db->f("Institut_id")]=array("type"=>"inst","name"=>$this->db->f("Name"));
				}
				$query = "SELECT b.Institut_id,b.Name FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)  
				WHERE a.user_id='$this->user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) AND b.Name LIKE '%$search_str%'";
				$this->db->query($query);
				while($this->db->next_record()){
					$this->search_result[$this->db->f("Institut_id")]=array("type"=>"fak","name"=>$this->db->f("Name"));
				}
			}
			
		} elseif ($perm->have_perm("tutor")) {
			$query="SELECT b.Seminar_id,b.Name from seminar_user AS a LEFT JOIN  seminare AS b USING (Seminar_id)	WHERE a.user_id='$this->user_id' AND a.status IN ('dozent','tutor') ";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Seminar_id")]=array("type"=>"sem","name"=>$this->db->f("Name"));
			}
			$query="SELECT b.Institut_id,b.Name from user_inst AS a LEFT JOIN  Institute AS b USING (Institut_id) WHERE a.user_id='$this->user_id' AND a.inst_perms IN ('dozent','tutor') ";
			$this->db->query($query);
			while($this->db->next_record()) {
				$this->search_result[$this->db->f("Institut_id")]=array("type"=>"inst","name"=>$this->db->f("Name"));
			}
			//$this->search_result[$this->user_id]=array("type"=>"user","name"=>$this->full_username."(".$auth->auth["uname"].")");
		}
		
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
		$output[0].= "</th><th align=\"center\" width=\"10%\">" . _("Anzeigen ?") . "</th></tr>";
		$not_in="";
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
			$add.="NOT IN ($not_in)";
		else 
			$add="";
		if ($perm->have_perm("tutor") && is_array($this->search_result)) {
			reset($this->search_result);
			while (list ($range,$details) = each($this->search_result)) {
				if ($details["type"]==$type) {
					$cssSw->switchClass();
					$output[1].= "\n<tr ".$cssSw->getHover()."><td	".$cssSw->getFullClass()."  width=\"90%\">".htmlReady($details["name"])."</td>";
					$output[1].= "\n<td  ".$cssSw->getFullClass()." width=\"10%\" align=\"center\"><input type=\"CHECKBOX\" name=\"add_range[]\" value=\"".$range."\"";
					if ($range==$this->news_range AND $this->news_query["news_id"]=="new_entry") 
						$output[1].=" checked ";
					$output[1].="></td></tr>";
				}
			}
		} else {
			if ($query){
				$this->db->query($query.$add);
				while($this->db->next_record()) {
					$cssSw->switchClass();
					$output[1].= "\n<tr ".$cssSw->getHover()."><td	".$cssSw->getFullClass()." width=\"90%\">".$this->db->f("name")."</td>";
					$output[1].= "\n<td	".$cssSw->getFullClass()." width=\"10%\" align=\"center\"><input type=\"CHECKBOX\" name=\"add_range[]\" value=\"".$this->db->f("id")."\"";
					if ($this->db->f("id")==$this->news_range AND $this->news_query["news_id"]=="new_entry") 
						$output[1].=" checked ";
					$output[1].="></td></tr>";
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
		$query="SELECT seminare.Seminar_id AS id,seminar_user.status,Name FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE seminar_user.user_id='".$this->user_id."' AND seminar_user.status IN ('dozent','tutor')";
		$this->db->query($query);
		while($this->db->next_record()) {
			if ($this->db->f("status")=="tutor") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>2);
			if ($this->db->f("status")=="dozent") $this->news_perm[$this->db->f("id")]=array("name"=>$this->db->f("Name"),"perm"=>2);
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
			LEFT JOIN Institute c ON(c.fakultaets_id = b.institut_id AND c.fakultaets_id!=c.institut_id) LEFT JOIN seminare d USING(Institut_id) 
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
		if ($auth->auth["perm"]=="root") 
			$this->news_perm["studip"]=array("name"=>"Stud.IP News","perm"=>3);
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

	function send_sms() {
		$msg_object = new messaging();
		while (list($user_id,$msg) = each($this->sms)) {
			$this->db->query("SELECT username FROM auth_user_md5 WHERE user_id='$user_id'");
			$this->db->next_record();
			$user_name=$this->db->f("username");
			$msg_object->insert_message(mysql_escape_string($msg), $user_name, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("News geändert"));
		}
	}

}	//Ende Klassendefintion

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head



echo "\n" . cssClassSwitcher::GetHoverJSFunction() . "\n";

if(!$news_range_id) {
	$sess->register("news_range_id");
	$sess->register("news_range_name");
}

if ($range_id == 'self') {
	$range_id = $user->id;	
}

if ($range_id){
	$news_range_id = $range_id;
}

if ($SessSemName[1] && ($list || $view)) {
	$news_range_id = $SessSemName[1];
	$news_range_name = $SessSemName[0];
}

$news = new studip_news();


if ($list || $view || (($news_range_id != $user->id) && ($news_range_id != 'studip')) ){
		include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");	//Linkleiste fuer admins
} else {
		include ("$ABSOLUTE_PATH_STUDIP/links_about.inc.php"); //Linkliste persönlicher Bereich
}



?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td class="topic"><b>&nbsp;
<?=_("Newsverwaltung")?></b> <font size="-1">(<?=_("gew&auml;hlter Bereich:")?> <b><?=htmlReady($news_range_name)?></b>)</font></td></tr>
<?

if ($perm->have_perm("admin"))	{
	if ($cmd=="search") {
		if (!$search) {
			$news->msg .= "error§" . _("Sie haben keinen Suchbegriff eingegeben!") . "§";
			$cmd="";
		} else {
			$news->search_range($search);
			if (is_array($news->search_result) && !count($news->search_result)) 
				$news->msg.="info§" . _("Die Suche ergab keine Treffer!") . "§";
			$cmd="";
		}
	}
}


if ($cmd=="news_submit") {
	$edit_news=$news->update_news($news_id,$author,$topic,$body,$user_id,$date,$expire,$add_range) ;
	if ($edit_news) 
		$cmd="edit";
	else 
		$cmd="";
}

if ($news->msg) {
	echo "<tr><td class=\"blank\"><br />";
	parse_msg($news->msg,"§","blank","1");
	echo "</td></tr>";
}
$news->msg="";

if ($cmd=="edit") {
	if ($perm->have_perm("admin") && $search) {
		if ($search) 
			$news->search_range($search);
			if (is_array($news->search_result) && !count($news->search_result)) {
			echo "<tr><td class=\"blank\"><br />";
			parse_msg("info§" . _("Die Suche ergab keine Treffer!") . "§","§","blank","1",FALSE);
			echo "</td></tr>";
		}
	}
	if ($auth->auth["perm"]=="dozent" OR $auth->auth["perm"]=="tutor") 
		$news->search_range("blah");
	$news->edit_news($edit_news);
}

if ($cmd=="kill") {
	$news->kill_news($kill_news);
	$cmd="";
}

if ($news->msg) {
	echo "<tr><td class=\"blank\"><br />";
	parse_msg($news->msg,"§","blank","1");
	echo "</td></tr>";	
}
$news->msg="";

if ($cmd=="new_entry") {
	if ($auth->auth["perm"]=="dozent" OR $auth->auth["perm"]=="tutor") 
		$news->search_range("blah");
	$news->edit_news();
}

if (!$cmd OR $cmd=="show") {
	if ($news->sms) 
		$news->send_sms();
	if ($perm->have_perm("tutor")) {
		if ($perm->have_perm("admin")) {
			echo"\n<tr><td class=\"blank\"><blockquote><br /><b>" . _("Bereichsauswahl") . "</b><br />&nbsp; </blockquote></td></tr>\n";
			echo "<tr><td class=\"blank\"><blockquote>";
			echo "<table width=\"50%\" cellspacing=0 cellpadding=2 border=0>";
			echo "<form action=\"".$news->p_self("cmd=search")."\" method=\"POST\">";
			echo "<tr><td class=\"steel1\">";
			echo "&nbsp; <font size=-1>" . _("Geben Sie einen Suchbegriff ein, um weitere Bereiche zu finden!") . "</font><br /><br />";
			echo "&nbsp; <INPUT TYPE=\"TEXT\" style=\"vertical-align:middle;\" name=\"search\" size=\"20\">&nbsp;&nbsp;";
			echo "<input type=\"IMAGE\" style=\"vertical-align:middle;\" name=\"submit\" " . makeButton("suchestarten","src") . tooltip( _("Suche starten")) ." border=\"0\">";
			echo "</td></tr></form></table>\n";
			echo "</blockquote>";
			echo "</td></tr>";
		} else
			$news->search_range("blah");
		echo "\n<tr><td class=\"blank\"><blockquote>";
		if ($perm->have_perm("admin"))
		echo "<hr>";
		echo "<br /><b>" . _("verf&uuml;gbare Bereiche");
		echo "</b></blockquote></td></tr>\n ";
		$typen = array("user"=>_("Benutzer"),"sem"=>_("Veranstaltung"),"inst"=>_("Einrichtung"),"fak"=>_("Fakult&auml;t"));
		$my_cols=3;
		if ($perm->have_perm("tutor")){
			echo "\n<tr><td class=\"blank\"><blockquote>";
			echo "<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Sie k&ouml;nnen&nbsp; <b>Pers&ouml;nliche News</b> bearbeiten") . "</font>&nbsp;";
			echo "<a href=\"".$news->p_self("range_id=$user->id")."\">&nbsp; <img style=\"vertical-align:middle;\" " . makeButton("bearbeiten","src") . tooltip(_("Persönliche News bearbeiten")) ." border=\"0\"></a>";
		}
		if ($perm->have_perm("root")) {
			$my_cols=4;
			echo "<font size=\"-1\" style=\"vertical-align:middle;\">&nbsp; " . _("<i>oder</i> <b>Systemweite News</b> bearbeiten") . "</font>&nbsp;";
			echo "<a href=\"".$news->p_self("range_id=studip")."\">&nbsp;<img style=\"vertical-align:middle;\" " . makeButton("bearbeiten","src") . tooltip(_("Systemweite News bearbeiten")) ." border=\"0\"></a>";
		}
		if ($news->search_result)
			echo "<br><br><font size=\"-1\" style=\"vertical-align:middle;\">" . _("<i>oder</i> <b>hier</b> einen der gefundenen Bereiche ausw&auml;hlen:") . "&nbsp;</font>";
		
		if ($perm->have_perm("tutor"))
			echo "</blockquote></td></tr>";
		
		if ($news->search_result) {
			echo "\n<tr><td width=\"100%\" class=\"blank\"><blockquote>";
			echo "<table width=\"".round(0.88*$news->xres)."\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">";
			$css = new CssClassSwitcher(array("steel1","steel1"));
			$css->hoverenabled = TRUE;
			$css->switchClass();
			while (list($typen_key,$typen_value)=each ($typen)) {
				if (!$perm->have_perm("root") AND $typen_key=="user") 
					continue;
				echo "\n<td class=\"steel1\" width=\"".floor(100/$my_cols)."%\" align=\"center\" valign=\"top\"><b>$typen_value</b><br><font size=\"-1\">";
				reset($news->search_result);
				while (list ($range,$details) = each ($news->search_result)) {
					if ($details["type"]==$typen_key) {
						echo "\n<div ".$css->getHover()."><a href=\"".$news->p_self("range_id=$range&view_mode=$typen_key")."\">".htmlReady($details["name"]);
						echo ($details["anzahl"]) ? " (".$details["anzahl"].")" : " (0)";
						echo "</a></div>";
					}
				}
				echo "\n</font></td>";
			}
			echo"\n</table></blockquote></td></tr>";
		}
	}
	echo "\n<tr><td class=\"blank\"><br /><blockquote>";
	echo "<form action=\"".$news->p_self("cmd=new_entry&range_id=$news_range_id&view_mode=$view_mode")."\" method=\"POST\">";
	echo "<hr width=\"100%\"><br /><b>" . _("gew&auml;hlter Bereich:") . " </b>".htmlReady($news_range_name). "<br /><br />";
	echo "<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Eine neue News im gew&auml;hlten Bereich erstellen") . "</font>&nbsp;";
	echo "<input type=\"IMAGE\" style=\"vertical-align:middle;\" name=\"new_entry\" " .makeButton("erstellen","src") . tooltip(_("Eine neue News erstellen")) . " border=\"0\">";
	echo "</b></blockquote></form></td></tr>\n ";
	if (!$news->show_news($news_range_id)) {
		echo "\n<tr><td class=\"blank\"><blockquote>";
		echo "<font size=\"-1\" style=\"vertical-align:middle;\">" . _("Im gew&auml;hlten Bereich sind keine News vorhanden!") . "<br><br>";
		echo "</blockquote></td></tr>";
	}
}
echo"\n</table></html>";
page_close();
?>
