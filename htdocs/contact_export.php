<?php
/**

 * Exports contacts to a vCard file

 *

 * @author      Christian Bauer <alfredhitchcock@gmx.net>

 * @version     $Id$

 * @copyright   2003 Stud.IP-Project

 * @access      public

 * @module      contact

 */


/* ************************************************************************** *
/*																			  *
/* Define constants															  *
/*																			  *
/* ************************************************************************* */
/* **END*of*Define*constants************************************************ */


/* ************************************************************************** *
/*																			  *
/* initialise Stud.IP-Session												  *
/*																			  *
/* ************************************************************************* */
page_open (array ("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
	"perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check ("autor");
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");

/* **END*of*initialise*Stud.IP-Session*********************************** */

/* ************************************************************************** *
/*																			  *
/* including needed files													  *
/*																			  *
/* ************************************************************************* */
// if you wanna export a vCard no html-header should be send to the browser
if (!isset($_POST["export_vcard_x"])){
	require_once($ABSOLUTE_PATH_STUDIP . "html_head.inc.php");
	require_once($ABSOLUTE_PATH_STUDIP . "header.php");
	require_once($ABSOLUTE_PATH_STUDIP . "links_sms.inc.php");
}
/* **END*of*initialize*post/get*variables*********************************** */

/* ************************************************************************** *
/*																			  *
/* identify the current site-mode											  *
/*																			  *
/* ************************************************************************* */
if (isset($_POST["export_vcard_x"]))
	$mode = "export_vcard";
else
	$mode = "select_group";
/* **END*of*identify*the*current*site-mode*********************************** */


/* ************************************************************************** *
/*																			  *
/* collecting the data  													  *
/*																			  *
/* ************************************************************************* */
if ($mode == "select_group"){
	// creats the content for the infobox
	$infobox = array (	
		array ("kategorie"  => "Information:",
			"eintrag" => array	(	
				array (	"icon" => "pictures/ausruf_small.gif",
					"text"  => _("Bitte wählen sie eine bestimme Gruppe ihres Adressbuches oder ihr vollständiges Adressbuch und drücken anschließend auf 'Export'.")
				),
			)
		),
	);

	$groups = getContactGroups();
} elseif ($mode == "export_vcard"){

	$contacts = getContactGroupData($_POST["groupid"]);
	
}

/* **END*of*collecting*the*data********************************************* */

/* ************************************************************************** *
/*																			  *
/* displays the site	  													  *
/*																			  *
/* ************************************************************************* */
if ($mode == "select_group"){

	printSiteTitle();
	printSelectGroup($infobox,$groups);
	
} elseif ($mode == "export_vcard"){

	exportVCard($contacts);

}
page_close ();
/* **END*of*displays*the*site*********************************************** */


/* ************************************************************************** *
/*																			  *
/* private functions														  *
/*																			  *
/* ************************************************************************* */

/* ************************************************************************** *
/* html-output          													  *
/* ************************************************************************* */

/**
 * displays the site title
 *
 * @access  private
 *
 */
function printSiteTitle(){
   	$html = "<table border=0 class=blank align=center cellspacing=0 cellpadding=0 width=\"100%\">\n"
    	  . "	<tr valign=top align=center>\n"
    	  . "    <td class=topic align=left colspan=\"2\">\n"
		  . "	  <img src=\"PICTURES/nutzer.gif\" alt=\"export\" align=\"texttop\">&nbsp;<b>\n"
		  . _("Adressbuch exportieren")."\n"
		  . "	  </b>\n"
    	  . "    </td>\n"
    	  . "   </tr>\n"
    	  . "</table>\n";
   	echo $html;
}

/**
 * displays the semester selection page
 *
 * @access  private
 * @param   array $infobox		the infobox for this site
 * @param   array $semestersAR	the array with the semesters to select
 *
 */
function printSelectGroup($infobox, $groups){
	$html = "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n"
		. " <tr valign=\"top\">\n"
		. "  <td width=\"99%\" NOWRAP class=\"blank\">&nbsp;\n"
		. "   <table align=\"center\" width=\"99%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n"
		. "	 <tr>"
		. "	  <td align=\"left\" valign=\"top\"><font size=\"-1\">\n"
		. _("Bitte wählen sie ein Gruppe aus, deren Daten sie in eine vCard-Datei exportieren möchten:")."\n"
		. "	   <form action=\"$PHPSELF\" method=post>\n"
		. "       &nbsp;<select name=\"groupid\" style=\"vertical-align:middle;\">\n";
	// the groups
	for ($i=0;$i<=sizeof($groups)-1;$i++){
		$html .= "        <option value=\"".$groups[$i]["id"]."\">".$groups[$i]["name"]."</option>\n";
	}
	$html .="       </select>\n"
		. createButton("export",_("Diese Gruppe nun exportieren"),"export_vcard")
		. "      </form>\n"
		. "	  </font></td>\n"
		. "	  <td align=\"right\" width=\"250\" valign=\"top\">\n";
	echo $html;
	print_infobox($infobox,"pictures/export.jpg");
	$html = "	  </td>\n"
		. "	 </tr>\n"
		. "	</table>\n"
		. "  <br></td>\n"
		. " </tr>\n"
		. "</table>\n";
	echo $html;
}

/**
 * creates an image-button
 *
 *
 * @access  private
 * @param   string $button	the button name (send to makeButton())
 * @param   string $title	the label
 * @param   string $button	the button name (optional)
 * @param   string $align	the button value (optional)
 * @returns string         	the button
 */
function createButton($button, $title, $name = NULL, $value = NULL){
	global $PHP_SELF;
	$html = "      <input type=\"image\" action=\"".$PHP_SELF."\" name=\"".$name."\" value=\"".$value."\" style=\"vertical-align:middle;\""
		  . 	   makeButton($button,"src") ." alt=\"".$title."\" title=\"".$title."\" border=0>\n";
	return $html;
}

/* ************************************************************************** *
/* db-requests          													  *
/* ************************************************************************* */

/**
 * collects the contactgroups from user	
 *
 * @access  private
 * @returns array the contact groups
 *
 */
function getContactGroups(){
	global $user;

	// all contacts
	$groups[0] = array ("id" => "all", "name" => _("Alle Einträge des Adressbuches"));

	$db=new DB_Seminar;

	$query = "SELECT name, statusgruppe_id, size "
		. "FROM statusgruppen "
		. "WHERE range_id = '".$user->id."' "
		. "ORDER BY position ASC";

	$db->query ($query);

	$i = 1;
	while ($db->next_record()){
		$groups[$i] = array(
			"id" => $db->f("statusgruppe_id"),
			"name" => $db->f("name"),
			"size" => $db->f("size")
			);
		$i++;
	}

	return $groups;
}

/**
 * collects the data from one contactgoup or all contacts
 *
 * @access  private
 * @param   string $groupID	the selected group
 * @returns array the contact group data
 *
 */
function getContactGroupData($groupID){
	global $user, $_fullname_sql;
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	
	// the users from one group
	if ($groupID != "all"){
		$query = "SELECT statusgruppe_user.user_id, statusgruppe_user.statusgruppe_id, "
			. $_fullname_sql['full'] . " AS fullname , auth_user_md5.username, auth_user_md5.Email, auth_user_md5.Vorname, auth_user_md5.Nachname, "
			. "user_info.Home, user_info.privatnr, user_info.privadr, user_info.title_front, user_info.title_rear "
			. "FROM statusgruppe_user "
			. "LEFT JOIN auth_user_md5 USING(user_id) "
			. "LEFT JOIN user_info USING (user_id) "
			. "WHERE statusgruppe_id = '$groupID'";
			
	// all contacts from this user
	} else { 
		$query = "SELECT contact.user_id, "
			. $_fullname_sql['full'] . " AS fullname , auth_user_md5.username, auth_user_md5.Email, auth_user_md5.Vorname, auth_user_md5.Nachname, "
			. "user_info.Home, user_info.privatnr, user_info.privadr, user_info.title_front, user_info.title_rear "
			. "FROM contact "
			. "LEFT JOIN auth_user_md5 USING(user_id) "
			. "LEFT JOIN user_info USING (user_id) "
			. "WHERE owner_id = '".$user->id."'";
	}
			
	$db->query($query);
	
	$i = 0;
	while ($db->next_record()){
		$contacts[$i] = array(
			"id" => $db->f("user_id"),
			"FN" => $db->f("fullname"),
			"NICKNAME" => $db->f("username"),
			"URL" => $db->f("Home"),
			"TEL" => $db->f("privatnr"),
			"ADR" => $db->f("privadr"),
			"EMAIL" => $db->f("Email"),
			"gname" => $db->f("Vorname"),
			"fname" => $db->f("Nachname"),
			"prefix" => $db->f("title_front"),
			"uffix" => $db->f("title_rear")
			);

			// collecting the office data
			$query = "SELECT a.*,b.Name as fak_name "
				. "FROM user_inst a "
				. "LEFT JOIN Institute b USING (Institut_id) "
				. "WHERE user_id = '".$contacts[$i]["id"]."' AND inst_perms != 'user'";
			$db2->query($query);
			$j = 0;
			while ($db2->next_record()){
				$contacts[$i]["fak"][$j] = array(
					"fak_name" => $db2->f("fak_name"),
					"consultation_hours" => $db2->f("sprechzeiten"),
					"room" => $db2->f("raum"),
					"TEL" => $db2->f("Telefon"),
					"FAX" => $db2->f("Fax")
				);
				$j++;
			}
		$statusgruppe_id = $db->f("statusgruppe_id");	
		$i++;
	}

	//geting the groupname	
	if ($groupID != "all"){
		$query = "SELECT name FROM statusgruppen WHERE statusgruppe_id = '".$statusgruppe_id."'";
		$db->query($query);
		$db->next_record();
		$groupname = $db->f("name");
	} else {
		$groupname	= _("StudIP-Kontakte");
	}
	$contacts["groupname"] = $groupname;
	return $contacts;
}

/**
 * collects the data from one contactgoup or all contacts
 *
 * @access  private
 * @param   string $groupID	the selected group
 * @returns array the contact group data
 *
 */
function exportVCard($contacts){

//	print "<pre>";
//	print_r($contacts);
//	print "</pre><br>";
	header("Content-type: application/octet-stream"); //application/octet-stream MIME
	header("Content-disposition: attachment; filename=".$contacts["groupname"].".vcf");


	for ($i=0;$i<=sizeof($contacts)-2;$i++){
//		$vcard .="<br>";
		$vcard .= "BEGIN:VCARD\r\n"
			. "VERSION:3.0\r\n";

		// the full name
		$vcard .= "FN:".$contacts[$i]["FN"]."\r\n";

		// the name in parts
		$vcard .= "N:";
			//Family Name
			$vcard .= $contacts[$i]["fname"];
			$vcard .= ";";
			//Given Name
			$vcard .= $contacts[$i]["gname"];
			$vcard .= ";";
			//no Additional Name in stud.ip
			$vcard .= ";";
			//Honorific Prefix
			$vcard .= $contacts[$i]["prefix"];
			$vcard .= ";";
			//Honorific Suffix
			$vcard .= $contacts[$i]["suffix"];
			$vcard .= ";";
			//closing this entry
			$vcard .= "\r\n";
		
		// the nick-name: 'NICKNAME:'
		
		// the private adress
		$vcard .= "ADR;TYPE=home:;;";
		$vcard .= $contacts[$i]["ADR"];
		$vcard .= "\r\n";

		// the private phone
		$vcard .= "TEL;TYPE=home:";
		$vcard .= $contacts[$i]["TEL"];
		$vcard .= "\r\n";
		
		// the e-mail
		$vcard .= "EMAIL;TYPE=internet:";
		$vcard .= $contacts[$i]["EMAIL"];
		$vcard .= "\r\n";
		
		// the private url
		$vcard .= "URL:";
		$vcard .= $contacts[$i]["URL"];
		$vcard .= "\r\n";

		// work data
		
		// if there is any workplace
		if (sizeof($contacts[$i]["fak"]) > 0){
			// the work adress
			$vcard .= "ADR;TYPE=work:;;";
			$vcard .= $contacts[$i]["fak"][0]["fak_name"];
			$vcard .= ",";
			$vcard .= $contacts[$i]["fak"][0]["room"];
			$vcard .= ",";
			$vcard .= $contacts[$i]["fak"][0]["consultation_hours"];
			$vcard .= "\r\n";

			// the work phone
			$vcard .= "TEL;TYPE=work:";
			$vcard .= $contacts[$i]["fak"][0]["TEL"];
			$vcard .= "\r\n";
			
			// the work fax
			$vcard .= "TEL;TYPE=work,fax:";
			$vcard .= $contacts[$i]["fak"][0]["FAX"];
			$vcard .= "\r\n";
		}
		// if there are more than one workplace
		if (sizeof($contacts[$i]["fak"]) > 1){
			$vcard .= "NOTE:";
			$vcard .= "Weitere Arbeitsplätze: ";				
			for ($j=1;$j<=sizeof($contacts[$i]["fak"]);$i++){
				// the work adress
				$vcard .= $contacts[$i]["fak"][$j]["fak_name"];
				$vcard .= "; ";
				$vcard .= "Raum: ";
				$vcard .= $contacts[$i]["fak"][$j]["room"];
				$vcard .= "; ";
				$vcard .= "Sprechstunde: ";
				$vcard .= $contacts[$i]["fak"][$j]["consultation_hours"];
				$vcard .= "; ";

				// the work phone
				$vcard .= "Tel: ";
//				$vcard .= "TEL;TYPE=work:";
				$vcard .= $contacts[$i]["fak"][$j]["TEL"];
				$vcard .= "; ";
			
				// the work fax
				$vcard .= "Fax: ";
//				$vcard .= "TEL;TYPE=work,fax:";
				$vcard .= $contacts[$i]["fak"][$j]["FAX"];
				$vcard .= "; ";
//				$vcard .= "";
			}
			$vcard .= "\r\n";
		}
		
//		$vcard .= "LABEL;TYPE=dom,home,postal,parcel:Mr.John Q. Public\, Esq.\nMail Drop: TNE QB\n123 Main Street\nAny Town\, CA  91921-1234\nU.S.A.\r\n";
		
		// the revisions and closing this entry
		$vcard .= "REV:".date("Y-m-d")."T".date("H:i:s")."Z\r\n"
			. "END:VCARD\r\n";
			
	}
	
	echo $vcard;
}
?>