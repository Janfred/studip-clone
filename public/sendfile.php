<?php
# Lifter002: 
/**
* sendfile.php
* 
* Send files to the browser an does permchecks
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, André Noack André Noack <andre.noack@gmx.net>
* @version		$Id$
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
if (ini_get('zlib.output_compression')){
	@ini_set('zlib.output_compression','0');
}
if (!ini_get('allow_url_fopen')){
	@ini_set('allow_url_fopen','1');
}
ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ('config.inc.php');
require_once ('lib/datei.inc.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('lib/classes/StudipLitList.class.php');

$db=new DB_Seminar;
$db2=new DB_Seminar;

if (isset($file_id))
	$file_id = escapeshellcmd(basename($file_id));
	
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
		$path_file=$STUDIP_BASE_PATH . "/" . $PATH_EXPORT . "/".$file_id;
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
		$path_file = get_upload_file_path($file_id);
	break;
}

//replace bad charakters to avoid problems when saving the file
$file_name = prepareFilename(basename(stripslashes(rawurldecode($file_name))));

if ($zip && is_file($path_file)) {
	$tmp_id = md5(uniqid("suppe"));
	$zip_path_file = "$TMP_PATH/$tmp_id";
	$tmp_file_name = escapeshellcmd("$TMP_PATH/$file_name");
	@copy($path_file, $tmp_file_name);
	create_zip_from_file( $tmp_file_name, "$zip_path_file.zip");
	$file_name = $file_name . ".zip";
	$path_file = $zip_path_file . ".zip";
	@unlink($tmp_file_name);
}



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
		case "swf": 
			$content_type="application/x-shockwave-flash";
			$content_disposition="inline";		
		break;
		case "csv": 
			$content_type="text/csv";
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
$db->query("SELECT seminar_id AS object_id, filesize, range_id FROM dokumente WHERE dokument_id = '".$file_id."' ");
$db->next_record();
$object_id = $db->f('object_id');

$skip_check=FALSE;
if ($type == 0 || $type == 6) {
	$object_type = get_object_type($object_id);
	if ($object_type == "inst" || $object_type == "fak"){
		$skip_check = TRUE;
	}
	$folder_tree =& TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $object_id));
	if (!$folder_tree->isDownloadFolder($db->f('range_id'), $GLOBALS['user']->id)) {
		$no_access = true;
		$skip_check = TRUE;
	}
}

// Rechtecheck
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
	if (!$perm->have_perm("user")) {
		if ($type == 0 || $type == 6) {
			$db->query("SELECT Lesezugriff FROM seminare LEFT JOIN dokumente USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
			$db->next_record();
			if ($db->f("Lesezugriff") != 0)
				$no_access=TRUE;
		} else {
			$no_access=TRUE; //nobody darf nie an das Archiv
		}
	} elseif (!$perm->have_perm("root")) {
		if ($type == 1) {
			$db->query ("SELECT archiv.seminar_id FROM archiv LEFT JOIN archiv_user USING (seminar_id) WHERE archiv_file_id = '".$file_id."' ");
			if (!$db->next_record()){
				$no_access = TRUE;
			} else {
				$no_access = (archiv_check_perm($db->f("seminar_id")) ? FALSE : TRUE);
			}
		} else {
			$no_access = ($perm->have_studip_perm('user', $object_id) ? FALSE : TRUE);
		}
	}
}

//Nachricht bei verbotenem Download
if ($no_access) {
	if ($type)
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Archiv"), '<a href="archiv.php?back=TRUE"><b>&nbsp;', '</b></a>') . '<br />&nbsp;' ;
	else
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Downloadbereich"), '<a href="folder.php?back=TRUE"><b>&nbsp;', '</b></a>') . '<br />&nbsp;' ;

	// Start of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

	parse_window('error§' . _("Sie haben keine Zugriffsberechtigung f&uuml;r diesen Download!"), '§', _("Download nicht m&ouml;glich"), $add_msg);
	include ('lib/include/html_end.inc.php');
	page_close();
	die;
}

// Check bei verlinkten Dateien ob sie erreichbar sind

if ($type == 6) {
	$link_data = parse_link($path_file);
	if (!($link_data['HTTP/1.0 200 OK'] || $link_data['HTTP/1.1 200 OK'])) {
		include ('lib/include/html_head.inc.php'); // Output of html head
		include ('lib/include/header.php');   // Output of Stud.IP head
		$add_msg= sprintf(_("%sZur&uuml;ck%s zum Downloadbereich"), '<a href="folder.php?back=TRUE"><b>&nbsp;', '</b></a>') . '<br />&nbsp;' ;
		parse_window('error§' . _("Diese Datei wird von einem externen Server geladen und ist dort momentan nicht erreichbar!"), '§', _("Download nicht m&ouml;glich"), $add_msg);
		include ('lib/include/html_end.inc.php');
		page_close();
		die;
	}
}

//Datei verschicken
if ($type == 6) {
	$db->query("SELECT filesize FROM dokumente WHERE dokument_id = '$file_id'");
	if ($db->next_record())
		$filesize = $db->f("filesize");
	if (!$filesize || $filesize==0)
		$filesize = FALSE;
} elseif ($type != 5){
	$filesize = @filesize($path_file);
} else {
	$filesize = strlen($the_data);
}

header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
if ($_SERVER['HTTPS'] == "on"){
	header("Pragma: public");
	header("Cache-Control: private");
} else {
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
}
header("Cache-Control: post-check=0, pre-check=0", false);
header("Content-Type: $content_type; name=\"$file_name\"");
header("Content-Description: File Transfer");
header("Content-Transfer-Encoding: binary");
header("Accept-Ranges: bytes");
if ($filesize != FALSE) header("Content-Length: $filesize");
header("Content-Disposition: $content_disposition; filename=\"$file_name\"");
ob_end_flush();

if ($type != 5){
	@readfile_chunked($path_file);
	if(!in_array($type, array(1,2,3,4,5))){
		TrackAccess($file_id, 'dokument');
	}
} else {
	echo $the_data;
}


//remove temporary file after zipping
if ($zip || $type == 4) {
	@unlink($path_file);
}

// Save data back to database.
page_close();
?>
