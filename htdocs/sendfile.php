<?
/**
* sendfile.php
* 
* Send files to the browser an does permchecks
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, André Noack André Noack <andre.noack@gmx.net>
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		sendfile.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sendfile.php - Datei an Browser senden
// Copyright (C) 2000 - 2002 Cornelis Kater <ckater@gwdg.de>
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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

if(ini_get('zlib.output_compression'))
      ini_set('zlib.output_compression', 'Off');

$dont_put_headers=TRUE;
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/StudipLitList.class.php");

$db=new DB_Seminar;
$db2=new DB_Seminar;


switch ($type) {
	//We want to download from the archive (this mode performs perm checks)
	case 1: 
		$path_file=$ARCHIV_PATH."/".$file_id;
	break;
	//We want to download from the tmp/export-folder
	case 2:
		$path_file=$TMP_PATH."/export/".$file_id;
	break;
	//We want to download an XSL-Script
	case 3:
		$path_file=$ABSOLUTE_PATH_STUDIP . $PATH_EXPORT . "/".$file_id;
	break;
	//we want to download from the studip-tmp folder (this mode performs perm checks)
	case 4:
		$path_file=$TMP_PATH . "/".$file_id;
	break;
	//download lit list as tab delimited text file
	case 5:
		$path_file = false;
	break;
	//download linked file
	case 6:
		$path_file = getLinkPath($file_id);
	break;
	//we want to download from the regular upload-folder (this mode performs perm checks)
	default:
		$path_file=$UPLOAD_PATH."/".$file_id;
	break;
}
	
if ($zip) {
	$tmp_id=md5(uniqid("suppe"));
	exec ("cp '$path_file' '$TMP_PATH/".rawurldecode($file_name)."'");
	exec ("$ZIP_PATH -9 -j $TMP_PATH/$tmp_id.zip '$TMP_PATH/".rawurldecode($file_name)."'");
	$tmp_file_name="'$TMP_PATH/".rawurldecode($file_name)."'";
	$file_name=$file_name.".zip";
	$path_file="$TMP_PATH/$tmp_id.zip";
	}


//replace bad charakters to avoid problems when saving the file
$file_name = prepareFilename(rawurldecode($file_name));

if ($force_download) {
	$content_type="application/octet-stream";
	$content_disposition="attachment";
} else {
	switch (strtolower(getFileExtension ($file_name))) {
		case "txt": 
			$content_type="text/plain";
			$content_disposition="inline";
		break;
		case "css": 
			$content_type="text/css";
			$content_disposition="inline";		
		break;
		case "gif": 
			$content_type="image/gif";
			$content_disposition="inline";		
		break;
		case "jpeg": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "jpg": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "jpe": 
			$content_type="image/jpeg";
			$content_disposition="inline";		
		break;
		case "bmp": 
			$content_type="image/x-ms-bmp";
			$content_disposition="inline";		
		break;
		case "png": 
			$content_type="image/png";
			$content_disposition="inline";	
		break;
		case "wav": 
			$content_type="audio/x-wav";
			$content_disposition="inline";		
		break;
		case "ra": 
			$content_type="application/x-pn-realaudio";
			$content_disposition="inline";		
		break;
		case "ram": 
			$content_type="application/x-pn-realaudio";
			$content_disposition="inline";		
		break;
		case "mpeg": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "mpg": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "mpe": 
			$content_type="video/mpeg";
			$content_disposition="inline";		
		break;
		case "qt": 
			$content_type="video/quicktime";
			$content_disposition="inline";		
		break;
		case "mov": 
			$content_type="video/quicktime";
			$content_disposition="inline";		
		break;
		case "avi": 
			$content_type="video/x-msvideo";
			$content_disposition="inline";		
		break;
		case "rtf": 
			$content_type="application/rtf";
			$content_disposition="inline";		
		break;
		case "pdf": 
			$content_type="application/pdf";
			$content_disposition="inline";
		break;
		case "doc": 
			$content_type="application/msword";
			$content_disposition="inline";		
		break;
		case "xls": 
			$content_type="application/ms-excel";
			$content_disposition="inline";		
		break;
		case "ppt": 
			$content_type="application/ms-powerpoint";
			$content_disposition="inline";		
		break;
		case "tgz": 
		case "gz": 	
			$content_type="application/x-gzip";
			$content_disposition="inline";
		break;
		case "bz2": 
			$content_type="application/x-bzip2";
			$content_disposition="inline";		
		break;
		case "zip": 
			$content_type="application/zip";
			$content_disposition="inline";		
		break;
		default:
			$content_type="application/octet-stream";
			$content_disposition="inline";		
		break;
		}
	}

//override disposition, if available
if ($disposition)
	$content_disposition=$disposition;

//determine the type of the object we want to download a file from (only in type=0 mode!)
$db->query("SELECT seminar_id AS object_id, filesize FROM dokumente WHERE dokument_id = '".$file_id."' ");
$db->next_record();

$skip_check=FALSE;
if (!$type) {
	$object_type = get_object_type($db->f("object_id"));
	if ($object_type == "inst")
		$skip_check=TRUE;
}

// Rechteckeck

if ($type == 6){
	$skip_check = true;
}

//////////////

if ($type == 5){
	$skip_check = true;
	if (!($range_id == $user->id) && !$perm->have_studip_perm('tutor', $range_id)){
		$no_access = true;
	} else {
		$the_data = StudipLitList::GetTabbedList($range_id, $list_id);
	}
}
//permcheck
if (($type != 2) && ($type != 3) && ($type != 4) && (!$skip_check)) { //if type 2, 3 or 4 we download from some tmp directory and skip permchecks
	if (!$perm->have_perm ("user")) {
		if (!$type) {
			$db->query("SELECT Lesezugriff FROM seminare LEFT JOIN dokumente USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
			$db->next_record();
			if ($db->f("Lesezugriff") != 0)
				$no_access=TRUE;
		} else {
			$no_access=TRUE; //nobody darf nie an das Archiv
		}
	} elseif (!$perm->have_perm("root")) {
		if ($type) {
			if ($perm->have_perm("admin") && !$perm->have_perm("root"))
				$db->query ("SELECT archiv.seminar_id FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE archiv_file_id = '".$file_id."' ");
			else
				$db->query ("SELECT archiv.seminar_id FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE user_id = '".$user->id."' AND archiv_file_id = '".$file_id."' ");
			if (!$db->next_record())
				$no_access=TRUE;
			if ($perm->have_perm("admin") && !$perm->have_perm("root")) {
				$db2->query("SELECT archiv.seminar_id FROM archiv LEFT OUTER JOIN user_inst ON (heimat_inst_id = institut_id) WHERE user_inst.user_id = '".$user->id."' AND user_inst.inst_perms = 'admin'");
				while ($db2->next_record())  {
					if($db->f("seminar_id") == $db2->f("seminar_id"))
						$admin=TRUE;
					}
				if ($admin)
					$no_access=FALSE;
				else
					$no_accss=TRUE;
				}
			}
		else {
			if ($perm->have_perm("admin") && !$perm->have_perm("root"))
				$db->query ("SELECT Institut_id FROM dokumente LEFT JOIN seminare USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
			else
				$db->query ("SELECT seminar_user.user_id, Institut_id FROM dokumente LEFT JOIN seminar_user USING (seminar_id) LEFT JOIN seminare USING (seminar_id) WHERE seminar_user.user_id = '".$user->id."' AND dokument_id = '".$file_id."' ");		
			if (!$db->next_record()) 
				$no_access=TRUE;
			if ($perm->have_perm("admin") && !$perm->have_perm("root")) {
				$db2->query("SELECT institut_id FROM user_inst  WHERE user_inst.user_id = '".$user->id."' AND user_inst.inst_perms = 'admin'");
				while ($db2->next_record())  {
					if($db->f("Institut_id") == $db2->f("institut_id"))
						$admin=TRUE;
					}
				if ($admin)
					$no_access=FALSE;
				else
					$no_accss=TRUE;
				}
			}
		}
	}
	
//Nachricht bei verbotenem Download
if ($no_access) {
	if ($type)
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Archiv"), "<a href=\"archiv.php?back=TRUE\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;" ;
	else
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Downloadbereich"), "<a href=\"archiv.php?back=TRUE\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;" ;

	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	parse_window("error§" . _("Sie haben keine Zugriffsberechtigung f&uuml;r diesen Download!"), "§", _("Download nicht m&ouml;glich"), $add_msg);
	page_close();
	echo "</body>";
	die;
}

echo "hallo";

if (substr($path_file,0,6) != "ftp://") {
	echo "bin da";
	//Datei per HTTP verschicken
	if ($type != 5 && $type != 6){
		$filesize = filesize($path_file);
	} else {
		$filesize = strlen($the_data);
	}
	header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
	header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	if ($_SERVER['HTTPS'] == "on")
		header("Pragma: public");
	else
		header("Pragma: no-cache");
	header("Cache-Control: private");
	header("Expires: 0");

	header("Content-type: $content_type; name=\"".rawurldecode($file_name)."\"");
	if ($type != 6)
		header("Content-length: $filesize");
	header("Content-disposition: $content_disposition; filename=\"".rawurldecode($file_name)."\"");
	if ($type != 5){
		readfile($path_file);
		TrackAccess ($file_id);
	} else {
		echo $the_data;
	}
	echo "<hr>$path_file<hr>";
	echo "<hr>$file_name<hr>";
} else {
	echo "nein";
	//Datei von FTP verschicken
	$url_parts = @parse_url($path_file);
	$documentpath = $url_parts["path"];
	$ftp = ftp_connect($url_parts["host"]);
	if (!$url_parts["user"]) $url_parts["user"] = "anonymous";
	if (!$url_parts["pass"]) $url_parts["pass"] = "test@";
	if (!ftp_login($ftp,$url_parts["user"],$url_parts["pass"])) {
      		ftp_quit($ftp);
      		echo "Datei nicht erreichbar";
      	}
	$parsed_link["Content-Length"] = ftp_size($ftp, $documentpath);
	
	header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
	header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	if ($_SERVER['HTTPS'] == "on")
		header("Pragma: public");
	else
		header("Pragma: no-cache");
	header("Cache-Control: private");
	header("Expires: 0");

	header("Content-type: $content_type; name=\"".rawurldecode($file_name)."\"");
	header("Content-disposition: $content_disposition; filename=\"".rawurldecode($file_name)."\"");
		
	readfile($path_file);
	TrackAccess ($file_id);
	
	ftp_quit($ftp);
}

//remove temporary file after zipping
if ($zip) {
	exec ("rm $tmp_file_name");
	exec ("rm $path_file");
	}

//remove temporary file after multiple-dwonload (as zip)
if ($type == 4) {
	exec ("rm $path_file");
	}

// Save data back to database.
page_close();
?>
