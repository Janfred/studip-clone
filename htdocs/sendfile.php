<?php
/*
sendfile.php - Datei an Browser senden
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

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

$dont_put_headers=TRUE;
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once("config.inc.php");
require_once("datei.inc.php");
require_once("visual.inc.php");

$db=new DB_Seminar;
$db2=new DB_Seminar;

switch ($type) {
	//We want download from the archive
	case 1: 
		$path_file=$ARCHIV_PATH."/".$file_id;
	break;
	//We want download from the tmp-folder
	case 2:
		$path_file=$TMP_PATH."/".$file_id;
	break;
	//We want download from the regular upload-folder
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


//Ersetzung von beim Speichern gefaehrlichen Zeichen. Ich weiss, es geht mit ereg_replace besser, hab ich aber grad keine Zeit, mir die Codes reinzuziehen :)
$file_name=str_replace(":", "", rawurldecode($file_name));
$file_name=str_replace(chr(92), "", rawurldecode($file_name));
$file_name=str_replace("/", "-", rawurldecode($file_name));
$file_name=str_replace("\"", "-", rawurldecode($file_name));
$file_name=str_replace("'", "", rawurldecode($file_name));
$file_name=str_replace(">", "", rawurldecode($file_name));
$file_name=str_replace("<", "", rawurldecode($file_name));
$file_name=str_replace("*", "", rawurldecode($file_name));
$file_name=str_replace("|", "", rawurldecode($file_name));
$file_name=str_replace("?", "", rawurldecode($file_name));

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
	case "zip": 
		$content_type="application/zip";
		$content_disposition="inline";		
	break;
	default:
		$content_type="application/octet-stream";
		$content_disposition="inline";		
	break;
	}

//Bevor wir Dateien rausschmeissen, sollte passiert noch mal ein Rechtecheck. Dies erfordert allerdings auch, dass die Dateien ausserhalb des Zugriffs des www liegen!
if (!$perm->have_perm ("user")) {
	if (!$type) {
		$db->query("SELECT Lesezugriff FROM seminare LEFT JOIN dokumente USING (seminar_id) WHERE dokument_id = '".$file_id."' ");
		$db->next_record();
		if ($db->f("Lesezugriff") != 0)
			$no_access=TRUE;
		}
	else
		$no_access=TRUE; //nobody darf nie an das Archiv
	}
elseif (!$perm->have_perm("root")) {
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

//Nachricht bei verbotenem Download
if ($no_access) {
	if ($type)
		$add_msg="<a href=\"archiv.php?back=TRUE\"><b>&nbsp;Zur&uuml;ck</b></a> zum Archiv<br />&nbsp;" ;
	else
		$add_msg="<a href=\"folder.php\"><b>&nbsp;Zur&uuml;ck</b></a> zum Downloadbereich<br />&nbsp;";
	?>
	<html>
		<head>
			<title>Stud.IP</title>
		<link rel="stylesheet" href="style.css" type="text/css">
		<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
		<body bgcolor=white>
	</head>
	<body>
	<?

	include "header.php";
	parse_window("error§Sie haben keine Zugriffsberechtigung f&uuml;r diesen Download!", "§", "Download nicht m&ouml;glch", $add_msg);
	page_close();

	?>
	</body>
	<?
	die;
	}

//Datei verschicken
header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Content-type: $content_type; name=\"".rawurldecode($file_name)."\"");
header("Content-disposition: $content_disposition; filename=\"".rawurldecode($file_name)."\"");

readfile($path_file);

//temporare Datein fuer zippen loeschen
if ($zip) {
	exec ("rm $tmp_file_name");
	exec ("rm $path_file");
	}

// Save data back to database.
page_close();
?>