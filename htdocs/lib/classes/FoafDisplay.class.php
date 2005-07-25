<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// FoafDisplay.class.php
//
// Copyright (c) 2005 Tobias Thelen <tthelen@uni-osnabrueck.de>
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

require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/UserConfig.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
		
/**
* Calculate and display "Friend of a friend lists" 
*
* for a given user the current user can see how many "steps"
* (in terms of buddy list entry hops) are neccessary to connect himself with
* the user at hand (whose homepage is currently viewed).
*
* @author		Tobias Thelen <tthelen@uni-osnabrueck.de>
* @version		$Id$
*/
class FoafDisplay {
	var $db; // Database connection
	var $user_id; // start of connecting chain
	var $target_id; // end of connecting chain
	var $foaf_list; // steps of connection
	var $target_username; // used for open/close link on target user's hp

	/**
	* Initialise FoafDisplay object and calculate list.
	*
	* @param	user_id	Watching user
	* @param	user_id	Watched user
	* @param	string	Watched user's username (performance saver)
	*/
	function FoafDisplay($user_id, $target_id, $target_username) {
		$this->db=new DB_Seminar();
		$this->user_id=$user_id;
		$this->target_id=$target_id;
		$this->target_username=$target_username;
		$this->foaf_list=array();

		$this->calculate();
	}

	/**
	* Calculate foaf list.
	*
	* Uses smart DB joins to find connections. Thanks to 
	* Manuel Wortmann (post@manuel-wortmann.de) for the code!
	*
	* @access	private
	*/
	function calculate() {
	   
		$sql="SELECT * FROM contact WHERE owner_id='".$this->user_id."' AND buddy='1'";
		$this->db->query($sql);

		// user has no buddies at all -> fail (-1)
		if ($this->db->num_rows()==0) {
			$this->foaf_list=array();
			return;
		}

		// check for direct connection
		while ($this->db->next_record()) {	
			if ($this->db->f("user_id")==$this->target_id) {
				$this->foaf_list=array($this->user_id,$this->target_id);
				return;
			}
		}

	   
		//Anfangen Tabellen zu verknuepfen
		// 1 - 2 - 3
		$sql   =   "SELECT t1.user_id as c1 FROM contact as t1, contact as t2 where t1.owner_id='".$this->user_id."' and t2.user_id='".$this->target_id."' and t1.user_id=t2.owner_id and t1.buddy='1' and t2.buddy='1' limit 1";
		$this->db->query($sql);
		if ($this->db->next_record()) {
			$this->foaf_list=array($this->user_id,$this->db->f("c1"),$this->target_id);
			return;
		}

		// 1 - 2 - 3 - 4
		$sql="SELECT t1.user_id as c1,t2.user_id as c2 FROM contact as t1,contact as t2,contact as t3 where t1.owner_id='".$this->user_id."' and t3.user_id='".$this->target_id."' and t1.user_id=t2.owner_id and t2.user_id=t3.owner_id and t1.buddy='1' and t2.buddy='1' and t3.buddy='1' limit 1";
		$this->db->query($sql);
		if ($this->db->next_record()) {
			$this->foaf_list=array($this->user_id,$this->db->f("c1"),$this->db->f("c2"),$this->target_id);
			return;
		}

		// 1 - 2 - 3 - 4 - 5
		$sql="SELECT t1.user_id as c1,t2.user_id as c2,t3.user_id as c3 FROM contact as t1,contact as t2,contact as t3,contact as t4 where t1.owner_id='".$this->user_id."' and t4.user_id='".$this->target_id."' and t1.user_id=t2.owner_id and t2.user_id=t3.owner_id and t3.user_id=t4.owner_id and t1.buddy='1' and t2.buddy='1' and t3.buddy='1' and t4.buddy='1' limit 1";
		$this->db->query($sql);
		if ($this->db->next_record()) {
			$this->foaf_list=array($this->user_id,$this->db->f("c1"),$this->db->f("c2"),$this->db->f("c3"),$this->target_id);
			return;
		}

		// 1 - 2 - 3 - 4 - 5 - 6
		$sql="SELECT t1.user_id as c1,t2.user_id as c2,t3.user_id as c3,t4.user_id as c4 FROM contact as t1,contact as t2,contact as t3,contact as t4,contact as t5 where t1.owner_id='".$this->user_id."' and t5.user_id='".$this->target_id."' and t1.user_id=t2.owner_id and t2.user_id=t3.owner_id and t3.user_id=t4.owner_id and t4.user_id=t5.owner_id and t1.buddy='1' and t2.buddy='1' and t3.buddy='1' and t4.buddy='1' and t5.buddy='1' limit 1";
		$this->db->query($sql);
		if ($this->db->next_record()) {
			$this->foaf_list=array($this->user_id,$this->db->f("c1"),$this->db->f("c2"),$this->db->f("c3"),$this->db->f("c4"),$this->target_id);
		}

		return;
	}

	/**
	* Show Topic bar and header/content for foaf display.
	*
	* Prints a topic bar and a printhead line (with active link for
	* opening/closing content) and content, if opened.
	*
	* @access 	public
	* @param	string	open/close indication (passed by about.php)
	*/
	function show($open="close") {
		global $ABSOLUTE_PATH_STUDIP;
		if (!$open) {
			$open="close";
		}
		if ($open=="open") {
			echo "<a name=\"foaf\">";
		} 

		echo "\n<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\"
	width=\"100%\" align=\"center\">";
		echo "\n<tr valign=\"baseline\"><td class=\"topic\"><img src=\".
	/pictures/guestbook.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;";
		echo sprintf(_("Verbindung zu %s"),htmlReady(get_fullname($this->target_id)));
		print("</b></td></tr>");

		echo "\n<tr><td class=\"blank\" colspan=$colspan>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"
	width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

		if ($this->foaf_list && $open=="open") {
			$msg="<table align=center style=\"margin-top:8px;\"><tr>";
			$print_arrow=0;
			foreach ($this->foaf_list as $uid) {
				if ($print_arrow) {
					$msg.="<td valign=center align=center>&nbsp;>&nbsp;</td>";
				} else {
					$print_arrow=1;
				}
				$info=$this->user_info($uid,($uid==$this->user_id||$uid==$this->target_id));
				$msg.="<td align=center>";
				$msg.=$info["pic"];
				$msg.="<br>";
				$msg.=$info["link"];
				$msg.="</td>";
			}
			$msg.="</tr></table>";
		} 
		if ($open=="open") {
			$msg.=$this->info_text();
		}
		if (!$this->foaf_list) {
			$titel=_("Es besteht keine Verbindung.");
		} elseif (count($this->foaf_list)<=2) {
			$titel=_("Es besteht eine direkte Verbindung.");
		} else {
			$titel=sprintf(_("Es besteht eine Verbindung über %d andere NutzerInnen."),count($this->foaf_list)-2);
		}
		$link="about.php?username=".$this->target_username."&foaf_open=".($open=="open" ? "close":"open")."#foaf";
		$titel="<a href=\"$link\" class=\"tree\">$titel</a>";
		printhead("100%","0",$link,$open,0,"<img src='pictures/icon-guest.gif'>",$titel,"");
		if ($open=="open") {
			print $msg;
		}
		print "</tr></table></td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	/**
	* Gather and format info on user.
	*
	* @param	user_id	A user's id
	* @param	bool	Should user data be created even if user doesn't want to appear in foaf lists? (true if head or tail of list)
	* @return 	array	"uname"=>username, "fullname"=>Full name, 
	*			"link"=>(clickable) Name, "pic"=>HTMl code for picture
	*/
	function user_info($user_id, $ignore_ok) {
		global $_fullname_sql;
		$ret="";
		$ucfg=new UserConfig($user_id, "foaf_show_identity");
		if ($ignore_ok || $ucfg->getValue()) {
			$sql="SELECT username, $_fullname_sql[full] AS fullname FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id='$user_id'";
			$this->db->query($sql);
			$this->db->next_record();
			$ret["uname"]=$this->db->f("username");
			$ret["name"]=$this->db->f("fullname");
			if(!file_exists("./user/".$user_id.".jpg")) {
				$ret["pic"]="<a href=\"about.php?username=".$ret['uname']."\"><img border=1 src=\"./user/nobody.jpg\" height=\"100\" " .tooltip(_("kein persönliches Bild vorhanden"))."></a>";
			} else {
				$ret["pic"]="<a href=\"about.php?username=".$ret['uname']."\"><img src=\"./user/".$user_id.".jpg\" height=\"100\" border=\"1\" ".tooltip("ein Nutzer")."></a>";
			}
			$ret["link"]="<font size=-1><a href=\"about.php?username=".$ret['uname']."\">".htmlReady($ret['name'])."</a></font>";
		} else {
			$ret["pic"]="<img border=1 src=\"./user/nobody.jpg\" width=\"80\" " .tooltip(_("anonyme NutzerIn")).">";
			$ret["link"]=_("<font size=-1>anonyme NutzerIn</font>");
		}
		return $ret;
	}

	/**
	* Return info text for foaf-feature
	*
	*/
	function info_text() {
		$ucfg=new UserConfig($this->user_id, "FOAF_SHOW_IDENTITY");
		$vis=$ucfg->getValue();
		$msg="<table width=95% align=center><tr><td>";
		$msg.="<font size=-1><p>";
		$msg.=_("Die Verbindungskette (Friend-of-a-Friend-Liste) wertet Buddy-Listen-Einträge aus, um festzustellen, über wieviele Stufen (maximal fünf) sich zwei BenutzerInnen direkt oder indirekt \"kennen\".");
		$msg.=" ".sprintf(_("Die Zwischenglieder werden nur nach Zustimmung mit Namen und Bild ausgegeben. Sie selbst erscheinen derzeit in solchen Ketten %s. Klicken Sie %shier%s, um die Einstellung zu ändern."), "<b>".($vis ? _("nicht anonym") : _("anonym"))."</b>", "<a href=\"edit_about.php?view=Messaging\">","</a>");
		$msg.="</p></td></tr></table>";
		return $msg;
	}

}

