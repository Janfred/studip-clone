<?
/**
* helper functions for handling contacts
* 
* helper functions for handling contacts
*
* @author				Ralf Stockmann <rstockm@gwdg.de>
* @version			$Id$
* @access				public
* @package			studip_core
* @modulegroup	library
* @module				contact.inc.php
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",false);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// statusgruppe.inc.php
// Copyright (c) 2002 Ralf Stockmann <rstockm@gwdg.de>
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

/**
* built a not existing ID
*
* @access private
* @return	string
*/
function MakeUniqueContactID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));
	$db->query ("SELECT contact_id FROM contact WHERE contact_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueContactID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}

function AddNewContact ($user_id)
{ 	// Inserting an new contact
	global $user;
	$contact_id = MakeUniqueContactID();
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id'");	
	if (!$db->next_record()) 	// nur wenn es die Kombination owner/user noch nicht gibt
		$db->query("INSERT INTO contact SET contact_id = '$contact_id', owner_id = '$owner_id', user_id= '$user_id'");
	return $contact_id;	
} 

function ShowUserInfo ($user_id)
{ 	// Inserting an new contact
	global $user, $open;
	$output = "";
	$db=new DB_Seminar;
	$db->query ("SELECT Email, username FROM auth_user_md5 WHERE user_id = '$user_id'");	
	if ($db->next_record()) {	
		$basicinfo["Email"] = "<a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a>";
		$basicinfo["Stud.IP"] = "<a href=\"about.php?username=".$db->f("username")."\">Home</a>";
	}

	// diese Infos hat jeder
	while(list($key,$value) = each($basicinfo)) {
		$output .= "<tr><td class=\"steelgraulight\" width=\"100\"><font size=\"2\">".$key.":</font></td><td class=\"steelgraulight\" width=\"250\"><font size=\"2\">".$value."</font></td></tr>";
	}

	// hier Zusatzinfos

	if ($open == $user_id || $open == "all") {
		$db->query ("SELECT * FROM user_info WHERE user_id = '$user_id'");	
		if ($db->next_record()) {	
			if ($db->f("Home")!="")
				$userinfo["Homepage"] = "<a href=\"".$db->f("Home")."\">".$db->f("Home")."</a>";
			if ($db->f("privatnr")!="")
				$userinfo["Privat Tel."] = $db->f("privatnr");
			if ($db->f("privadr")!="")
				$userinfo["Addresse"] = $db->f("privadr");
		}
		$db->query ("SELECT sprechzeiten, raum, user_inst.telefon, user_inst.fax, Name, Institute.Institut_id FROM user_inst LEFT JOIN Institute USING(Institut_id) WHERE user_id = '$user_id' AND inst_perms != 'user'");	
		while ($db->next_record()) {	
			$userinfo["Institut"] = "<a href=\"institut_main.php?auswahl=".$db->f("Institut_id")."\">".$db->f("Name")."</a>";
			if ($db->f("raum")!="")
				$userinfo["Raum"] = $db->f("raum");
			if ($db->f("sprechzeiten")!="")
				$userinfo["Sprechzeiten"] = $db->f("sprechzeiten");
			if ($db->f("telefon")!="")
				$userinfo["Dienst Tel."] = $db->f("telefon");			
			if ($db->f("fax")!="")
				$userinfo["Dienst Fax"] = $db->f("fax");
		}
		if (sizeof($userinfo)>0) {
			while(list($key,$value) = each($userinfo)) {
				$output .= "<tr><td class=\"steel1\" width=\"100\"><font size=\"2\">".$key.":</font></td><td class=\"steel1\" width=\"250\"><font size=\"2\">".$value."</font></td></tr>";
			}
		}
		if(file_exists("./user/".$user_id.".jpg")) {
			$output.="<tr><td class=\"steel1\" colspan=\"2\" width=\"350\"><img src=\"./user/".$user_id.".jpg\" border=1></td>";
		}
	}
	return $output;	
} 


function ShowContact ($contact_id)
{	// Ausgabe eines Kontaktes
	global $PHP_SELF, $open, $filter;
	$db=new DB_Seminar;
	$db->query ("SELECT user_id FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {
		if ($open == $db->f("user_id") || $open == "all") {
			$lastrow =  	"<tr><td colspan=\"2\" class=\"steel1\" align=\"right\">"
						."<a href=\"$PHP_SELF?cmd=delete&contact_id=$contact_id\"><img src=\"pictures/nutzer.gif\" border=\"0\"></a>&nbsp; "
						."<a href=\"$PHP_SELF?cmd=delete&contact_id=$contact_id\"><img src=\"pictures/einst.gif\" border=\"0\"></a>&nbsp; "
						."<a href=\"$PHP_SELF?cmd=delete&contact_id=$contact_id\"><img src=\"pictures/trash_att.gif\" border=\"0\"></a></td></tr>"
						."<tr><td colspan=\"2\" class=\"steelgraulight\" align=\"center\"><a href=\"$PHP_SELF?filter=$filter\"><img src=\"pictures/forumgraurauf.gif\" border=\"0\"></a></td></tr>";
		} else {
			$lastrow = "<tr><td colspan=\"2\" class=\"steelgraulight\" align=\"center\"><a href=\"$PHP_SELF?filter=$filter&open=".$db->f("user_id")."\"><img src=\"pictures/forumgraurunt.gif\" border=\"0\"></a></td></tr>";
		}			
		$output = "<table cellspacing=\"0\" width=\"350\" class=\"blank\">
					<tr>
						<td class=\"topic\" colspan=\"2\">"
							.get_nachname($db->f("user_id")).", ".get_vorname($db->f("user_id"))."</td>"
							."
						</td>
					</tr>"
						.ShowUserInfo ($db->f("user_id"))
						. $lastrow
				."</table>";
	} else {
		$output = "Fehler!";
	}
	return $output;
}

function DeleteContact ($contact_id)
{	// loeschen eines Kontaktes
	global $user;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db->query ("SELECT owner_id, user_id FROM contact WHERE contact_id = '$contact_id'");	
	if ($db->next_record()) {
		if ($db->f("owner_id")!=$user->id) {
			$ouput = "Sie haben kein Zugriffsrecht auf diesen Kontakt!";	
		} else {
			$user_id = $db->f("user_id");
			$owner_id = $db->f("owner_id");
			$db->query ("DELETE FROM contact WHERE contact_id = '$contact_id'");	
			$db->query ("DELETE FROM contact_userinfo WHERE contact_id = '$contact_id'");
			$db2->query ("SELECT DISTINCT statusgruppe_user.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$owner_id' AND user_id = '$user_id'");		
			WHILE ($db2->next_record()) {	
				$statusgruppe_id = $db2->f("statusgruppe_id");
				$db3->query ("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$statusgruppe_id' AND user_id = '$user_id'");	
			}
			$output = "Kontakt gel&ouml;scht";
		}
	}
	return $output;
}


function PrintAllContact($filter="")
{	global $user, $open, $filter;
	$i = 1;
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT contact_id, nachname FROM contact LEFT JOIN auth_user_md5 using(user_id) WHERE owner_id = '$owner_id' ORDER BY nachname");	
	$middle = round($db->num_rows()/2);
	echo "<table class=\"blank\" width=\"700\" align=center cellpadding=\"10\"><tr><td valign=\"top\" width=\"350\" class=\"blank\">";
	while ($db->next_record()) {
		if ($filter=="" || strtolower(substr($db->f("nachname"),0,1))==$filter) {
			$contact_id = $db->f("contact_id");
			echo ShowContact ($contact_id);
			echo "<br>";
			if ($i==$middle) { //Spaltenumbruch
				echo "</td><td valign=\"top\" width=\"350\" class=\"blank\">";
			}
		}
		$i++;
	}
	echo "</td></tr></table>";
}

?>