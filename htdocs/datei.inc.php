<?php
/*
datei.inc.php - basale Routinen zur Dateiverwaltung, dienen zum Aufbau des Ordnersystems
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Cornelis Kater <ckater@gwdg.de>

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

function parse_header($header){
	if (!is_array($header)){
		$header = explode("\n",trim($header));
	}
	for($i = 0; $i < count($header); ++$i){
		$parts = null;
		$matches = preg_match('/^\S+:/', $header[$i], $parts);
		if ($matches){
			$key = trim(substr($parts[0],0,-1));
			$value = trim(substr($header[$i], strlen($parts[0])));
			$ret[$key] = $value;
		} else {
			$ret[trim($header[$i])] = trim($header[$i]);
		}
	}
	return $ret;
}

function parse_link($link) {
	global $name, $the_file_name, $the_link, $locationheader, $parsed_link;
	if (substr($link,0,6) == "ftp://") {
		// Parsing an FTF-Adress		
		$url_parts = @parse_url( $link );
		$documentpath = $url_parts["path"];
		$ftp = ftp_connect($url_parts["host"]);
		if (!$url_parts["user"]) $url_parts["user"] = "anonymous";
		if (!$url_parts["pass"]) $url_parts["pass"] = "rstockm%40gwdg.de";
		if (!ftp_login($ftp,$url_parts["user"],$url_parts["pass"])) {
      			ftp_quit($ftp);
      			// die("Error: can't login");
      			return FALSE;
   		}
   		$parsed_link["Content-Length"] = ftp_size($ftp, $documentpath);
		ftp_quit($ftp);
		if ($parsed_link["Content-Length"] != "-1")
			$parsed_link["HTTP/1.0 200 OK"] = "HTTP/1.0 200 OK";
		else
			$parsed_link = FALSE;
		return $parsed_link;
				
	} else {
	
	
	$url_parts = @parse_url( $link );
	if (!empty( $url_parts["path"])){
		$documentpath = $url_parts["path"];
	} else {
		$documentpath = "/";
	}
	if ( !empty( $url_parts["query"] ) ) {
		$documentpath .= "?" . $url_parts["query"];
	}
	$host = $url_parts["host"];
	$port = $url_parts["port"];
	if (empty( $port ) ) $port = "80";
	$socket = @fsockopen( $host, $port, $errno, $errstr, 10 );
	if (!$socket) {
		//echo "$errstr ($errno)<br />\n";
	} else {
		fputs($socket, "HEAD ".$documentpath." HTTP/1.0\nHost: $host\n\n");
   		socket_set_timeout($socket,2);
   		while (!feof($socket)) {
	       		$response .= fgets($socket,4096);
	   	}
	   	fclose($socket);
	}
	$parsed_link = parse_header($response);
	//print_r ($parsed_link);
	
	// Weg über einen Locationheader:
	
	if (($parsed_link["HTTP/1.1 302 Found"] || $parsed_link["HTTP/1.0 302 Found"]) && $parsed_link["Location"]) {
		$the_file_name = basename($url_parts["path"]);
		$the_link = $parsed_link["Location"];
		parse_link($parsed_link["Location"]);
	}
	return $parsed_link;
	}
}


function createSelectedZip ($file_ids, $perm_check = TRUE) {
	global $TMP_PATH, $UPLOAD_PATH, $ZIP_PATH, $SessSemName;
	$db = new DB_Seminar();
		
	$zip_file_id=md5(uniqid("jabba"));
	
	//create temporary Folder
	exec ("mkdir $TMP_PATH/$zip_file_id");
	$tmp_full_path="$TMP_PATH/$zip_file_id";
	
	//create folder content
	$in="('".join("','",$file_ids)."')";	
	$query = sprintf ("SELECT dokument_id, filename FROM dokumente WHERE dokument_id IN %s %s ORDER BY name, filename", $in, ($perm_check) ? "AND seminar_id = '".$SessSemName[1]."'" : "");
	$db->query($query);
	while ($db->next_record()) {
		$docs++;
		exec ("cp '$UPLOAD_PATH/".$db->f("dokument_id")."' '$tmp_full_path/[".($docs)."] ".$db->f("filename") ."'");
	}

	//zip stuff
	exec ("cd $tmp_full_path && ".$ZIP_PATH." -9 -r ".$TMP_PATH."/".$zip_file_id." * ");
 	exec ("rm -r $tmp_full_path");
 	exec ("mv ".$TMP_PATH."/".$zip_file_id.".zip ".$TMP_PATH."/".$zip_file_id);
 	
 	return $zip_file_id;
}


function createFolderZip ($folder_id) {
	global $TMP_PATH, $ZIP_PATH;
	$zip_file_id=md5(uniqid("jabba"));
	
	//create temporary Folder
	exec ("mkdir $TMP_PATH/$zip_file_id");
	$tmp_full_path="$TMP_PATH/$zip_file_id";
	
	//create folder comntent
	createTempFolder ($folder_id, $tmp_full_path);

	//zip stuff
	exec ("cd $tmp_full_path && ".$ZIP_PATH." -9 -r ".$TMP_PATH."/".$zip_file_id." * ");
 	exec ("rm -r $tmp_full_path");
 	exec ("mv ".$TMP_PATH."/".$zip_file_id.".zip ".$TMP_PATH."/".$zip_file_id);
 	
 	return $zip_file_id;
}

function createTempFolder ($folder_id, $tmp_full_path, $perm_check = TRUE) {
	global $UPLOAD_PATH, $SessSemName;
	$db = new DB_Seminar();

	//copy all documents from this folder to the temporary folder
	$linkinfo = FALSE;
	$query = sprintf ("SELECT dokument_id, filename, url FROM dokumente WHERE range_id = '%s' %s ORDER BY name, filename", $folder_id, ($perm_check) ? "AND seminar_id = '".$SessSemName[1]."'" : "");
	$db->query($query);
	while ($db->next_record()) {
		if ($db->f("url")!="") {  // just a linked file
			$linkinfo .= "\n\r".$db->f("filename");
		} else {
			$docs++;
			exec ("cp '$UPLOAD_PATH/".$db->f("dokument_id")."' '$tmp_full_path/[".($docs)."] ".$db->f("filename") ."'");
		}
	}
	if ($linkinfo) {
		$linkinfo = _("Hinweis: die folgenden Dateien sind nicht im Archiv enthalten, da sie lediglich verlinkt wurden:").$linkinfo;
		exec ("touch $tmp_full_path/info.txt");
		$fp = fopen ("$tmp_full_path/info.txt","w");
		fwrite ($fp,$linkinfo);
		fclose ($fp);
	}
	
	$db->query("SELECT folder_id, name FROM folder WHERE range_id = '$folder_id' ORDER BY name");
	while ($db->next_record()) {
		$folders++;
		$tmp_sub_full_path = $tmp_full_path."/[".$folders."] ".prepareFilename($db->f("name"), FALSE);
		exec ("mkdir '$tmp_sub_full_path' ");
		createTempFolder($db->f("folder_id"), $tmp_sub_full_path, $perm_check);
	}
	return TRUE;
}



function getFolderChildren($folder_id){
	static $folder_children;
	static $folder_num_children;
	if (!isset($folder_num_children[$folder_id])){
		$db = new DB_Seminar();
		$db->query ("SELECT folder_id FROM folder WHERE range_id='$folder_id'");
		while ($db->next_record()) {
			$folder_children[$folder_id][] = $db->Record[0];
		}
		$folder_num_children[$folder_id] = $db->num_rows();
	}
	return array($folder_children[$folder_id],$folder_num_children[$folder_id]);
}

function getFolderId($parent_id, $in_recursion = false){
	static $kidskids;
		if (!$kidskids || !$in_recursion){
			$kidskids = array();
		}
		$kids = getFolderChildren($parent_id);
		if ($kids[1]){
			$kidskids = array_merge($kidskids,$kids[0]);
			for ($i = 0; $i < $kids[1]; ++$i){
				getFolderId($kids[0][$i],true);
			}
		}
		return (!$in_recursion) ? $kidskids : null;
	}
/*
function getFolderId(&$folders,$parent_id){
	if(!$folders) $folders[]=$parent_id;
	$db = new DB_Seminar;
	$db->query ("SELECT folder_id FROM folder WHERE range_id='$parent_id'");
		while ($db->next_record()) {
			getFolderId($folders,$db->f("folder_id"));
			$folders[]=$db->f("folder_id");
		}
	return;
}
*/

function doc_count ($parent_id) {
	$db=new DB_Seminar;
	$arr = getFolderId($parent_id);
	$arr[] = $parent_id;
	$in="('".join("','",$arr)."')";
	$db->query ("SELECT count(*) as count FROM dokumente WHERE range_id IN $in");
	$db->next_record();
	return $db->Record[0];
}


function doc_newest ($parent_id) {
	$db=new DB_Seminar;
	$arr = getFolderId($parent_id);
	$arr[] = $parent_id;
	$in="('".join("','",$arr)."')";
	$db->query ("SELECT max(chdate), max(mkdate) FROM dokumente WHERE range_id IN $in ");
	$db->next_record();
	if ($db->Record[0] > $db->Record[1])
		return $db->Record[0];
	else
		return $db->Record[1];
}

function doc_challenge ($parent_id){
	$db=new DB_Seminar;
	$arr = getFolderId($parent_id);
	$arr[] = $parent_id;
	$in="('".join("','",$arr)."')";
	$db->query ("SELECT dokument_id FROM dokumente WHERE range_id IN $in");
	while($db->next_record()) $result[] = $db->Record[0];
	return $result;
}

function move_item ($item_id, $new_parent) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	if ($item_id != $new_parent) {
		$db->query ("UPDATE dokumente SET range_id='$new_parent' WHERE dokument_id = '$item_id'");
		if (!$db->affected_rows()) {
			//we want to move a folder, so we have first to check if we want to move a folder in a subordinated folder
			$db2->query ("SELECT range_id FROM folder WHERE folder_id = '$new_parent'");
			while ($db2->next_record()) {
				if ($db2->f("range_id") == $item_id)
					$target_is_child=TRUE;
				$db2->query ("SELECT range_id FROM folder WHERE folder_id = '".$db2->f("range_id")."' ");
			}
			if (!$target_is_child)
				$db->query ("UPDATE folder SET range_id='$new_parent' WHERE folder_id = '$item_id'");
		}
	}	
		
	if ($db->affected_rows());		
		return TRUE;
	}
	
function edit_item ($item_id, $type, $name, $description, $protected=0, $url = "") {
	global $the_file_name;
	$db=new DB_Seminar;
	if (!$url) {
		$db->query("SELECT filename FROM dokumente WHERE dokument_id = '$item_id'");	
		if($db->next_record())
			$the_file_name = $db->f("filename");
	} else {
		$url_parts = parse_url($url);
		$the_file_name = basename($url_parts['path']);	
	}
	if ($protected == "on") $protected=1;
	if ($type)
		$db->query("UPDATE folder SET name='$name', description='$description' WHERE folder_id ='$item_id'");
	else
		$db->query("UPDATE dokumente SET name='$name', description='$description', protected='$protected', url='$url', filename='$the_file_name' WHERE dokument_id ='$item_id'");	
	
	if ($db->affected_rows())
		return TRUE;
	}

function create_folder ($name, $description, $parent_id) {
	global $user;
	
	$db=new DB_Seminar;
	$id=md5(uniqid("salmonellen"));
	
	$db->query("INSERT INTO folder SET name='$name', folder_id='$id', description='$description', range_id='$parent_id', user_id='".$user->id."', mkdate='".time()."', chdate='".time()."'");
	if ($db->affected_rows()) {
		return $id;
		}
	}
	
## Upload Funktionen ################################################################################

//Ausgabe des Formulars
function form($refresh = FALSE) {
	global $PHP_SELF,$UPLOAD_TYPES,$range_id,$SessSemName,$SemUserStatus,$user;
	
	//Hier kommt wohl ein root oder admin, der nicht in der Seminartabelle steht... also uebernehmen wir globale Rechte
	if (!$SemUserStatus) {
		$db=new DB_Seminar;
		$db->query("SELECT perms FROM auth_user_md5 WHERE user_id = '".$user->id."'");
		$db->next_record();
		$SemUserStatus=$db->f("perms");
		}
		
	//erlaubte Dateigroesse aus Regelliste der Config.inc.php auslesen
	if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
		$max_filesize=$UPLOAD_TYPES[$SessSemName["art_num"]]["file_sizes"][$SemUserStatus];
		}
	else {
		$max_filesize=$UPLOAD_TYPES["default"]["file_sizes"][$SemUserStatus];
		}
	
	$c=1;
	
	if (!$refresh)
		$print="\n<br /><br />" . _("Sie haben diesen Ordner zum Upload ausgew&auml;hlt:") . "<br /><br /><center><table width=\"90%\" style=\"{border-style: solid; border-color: #000000;  border-width: 1px;}\" border=0 cellpadding=2 cellspacing=3>";
	else
		$print="\n<br /><br />" . _("Sie haben diese Datei zum Aktualisieren ausgew&auml;hlt. Sie <b>&uuml;berschreiben</b> damit die vorhandene Datei durch eine neue Version!") . "<br /><br /><center><table width=\"90%\" style=\"{border-style: solid; border-color: #000000;  border-width: 1px;}\" border=0 cellpadding=2 cellspacing=3>";	
	$print.="\n";
	$print.="\n<tr><td class=\"steel1\" width=\"20%\"><font size=-1><b>";
	
	//erlaubte Upload-Typen aus Regelliste der Config.inc.php auslesen
	if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
		if ($UPLOAD_TYPES[$SessSemName["art_num"]]["type"] == "allow") {
			$i=1;
			$print.= _("Unzul&auml;ssige Dateitypen:") . "</b><font></td><td class=\"steel1\" width=\"80%\"><font size=-1>";
			foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
				if ($i !=1)
					$print.= ", ";				
				$print.= strtoupper ($ft);
				$i++;
				}
			}
		else {
			$i=1;
			$print.= _("Zul&auml;ssige Dateitypen:") . "</b><font></td><td class=\"steel1\" width=\"80%\"><font size=-1>";
			foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
				if ($i !=1)
					$print.= ", ";				
				$print.= strtoupper ($ft);
				$i++;
				}		
			}
		}
	else {
		if ($UPLOAD_TYPES["default"]["type"] == "allow") {
			$i=1;
			$print.= _("Unzul&auml;ssige Dateitypen:") . "</b><font></td><td class=\"steel1\" width=\"80%\"><font size=-1>";
			foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
				if ($i !=1)
					$print.= ", ";				
				$print.= strtoupper ($ft);
				$i++;
				}
			}
		else {
			$i=1;
			$print.= _("Zul&auml;ssige Dateitypen:") . "</b></td><font><td class=\"steel1\" width=\"80%\"><font size=-1>";
			foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
				if ($i !=1)
					$print.= ", ";				
				$print.= strtoupper ($ft);
				$i++;
				}
			}
		}
	$print.="</font></td></tr>";
	$print.="\n<tr><td class=\"steel1\" width=\"20%\"><font size=-1><b>" . _("Maximale Gr&ouml;&szlig;e:") . "</b></font></td><td class=\"steel1\" width=\"80%\"><font size=-1><b>".($max_filesize / 1048576)." </b>" . _("Megabyte") . "</font></td></tr>";
	$print.= "\n<form enctype=\"multipart/form-data\" NAME=\"upload_form\" action=\"" . $PHP_SELF . "\" method=\"post\">";
	$print.= "<tr><td class=\"steelgraudunkel\" colspan=2><font size=-1>" . _("1. Klicken Sie auf <b>'Durchsuchen...'</b>, um eine Datei auszuw&auml;hlen.") . " </font></td></tr>";
	$print.= "\n<tr>";
	$print.= "\n<td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Dateipfad:") . "&nbsp;</font><br />";
	$print.= "&nbsp;<INPUT NAME=\"the_file\" TYPE=\"file\"  style=\"width: 70%\" SIZE=\"30\">&nbsp;</td></td>";
	$print.= "\n</tr>";
	if (!$refresh) {
		$print.= "<tr><td class=\"steelgraudunkel\" colspan=2><font size=-1>" . _("2. Geben Sie eine kurze Beschreibung und einen Namen f&uuml;r die Datei ein.") . "</font></td></tr>";
		$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Name:") . "&nbsp;</font><br>";
		$print.= "\n&nbsp;<input type=\"TEXT\" name=\"name\" style=\"width: 70%\" size=\"40\" maxlength\"255\" /></td></tr>";
		$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Beschreibung:") . "&nbsp;</font><br>";
		$print.= "\n&nbsp;<TEXTAREA NAME=\"description\"  style=\"width: 70%\" COLS=40 ROWS=3 WRAP=PHYSICAL></TEXTAREA>&nbsp;</td></tr>";
		$print.= "\n<tr><td class=\"steelgraudunkel\"colspan=2 ><font size=-1>" . _("3. Klicken Sie auf <b>'absenden'</b>, um die Datei hochzuladen") . "</font></td></tr>";
	} else
		$print.= "\n<tr><td class=\"steelgraudunkel\"colspan=2 ><font size=-1>" . _("2. Klicken Sie auf <b>'absenden'</b>, um die Datei hochzuladen und damit die alte Version zu &uuml;berschreiben.") . "</font></td></tr>";
	$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"center\" valign=\"center\">";	
	$print.= "\n<input type=\"image\" " . makeButton("absenden", "src") . " value=\"Senden\" align=\"absmiddle\" onClick=\"return upload_start();\" name=\"create\" border=\"0\">";
	$print.="&nbsp;<a href=\"$PHP_SELF?cancel_x=true\">" . makeButton("abbrechen", "img") . "</a></td></tr>";	
	$print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"upload\">";	
	$print.= "\n<input type=\"hidden\" name=\"upload_seminar_id\" value=\"".$SessSemName[1]."\">";	
	$print.= "\n</form></table><br /></center>";
	
	return $print;
	}

//kill the forbidden characters, shorten filename to 31 Characters
function prepareFilename($filename, $shorten = FALSE) {
	$bad_characters = array (":", chr(92), "/", "\"", ">", "<", "*", "|", "?");
	$replacements = array ("", "", "", "'", "", "", "", "", "", "");
	
	$filename=str_replace($bad_characters, $replacements, $filename);
	
	if ($filename{0} == ".")
		$filename = substr($filename, 1, strlen($filename));
	
	if ($shorten) {
		$ext = getFileExtension ($filename);
		$filename = substr(substr($filename, 0, strrpos($filename,$ext)-1), 0, (30 - strlen($ext))).".".$ext;
	}
	return ($filename);
}

//Diese Funktion dient zur Abfrage der Dateierweiterung
function getFileExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }

	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);

	return $ext;
}

//Check auf korrekten Upload
function validate_upload($the_file) {
	global $UPLOAD_TYPES,$the_file_size, $msg, $the_file_name, $SessSemName,$SemUserStatus, $user, $auth;

	//Hier kommt wohl ein root oder admin, der nicht in der Seminartabelle steht... also uebernehmen wir globale Rechte
	if (!$SemUserStatus) {
		$db=new DB_Seminar;
		$db->query("SELECT perms FROM auth_user_md5 WHERE user_id = '".$user->id."'");
		$db->next_record();
		$SemUserStatus=$db->f("perms");
		}
	
	//erlaubte Dateigroesse aus Regelliste der Config.inc.php auslesen
	if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
		$max_filesize=$UPLOAD_TYPES[$SessSemName["art_num"]]["file_sizes"][$SemUserStatus];
		}
	else {
		$max_filesize=$UPLOAD_TYPES["default"]["file_sizes"][$SemUserStatus];
		}
	
	$error = FALSE;
	if ($the_file == "none") { # haben wir eine Datei?
		$emsg.= "error§" . _("Sie haben keine Datei zum Hochladen ausgew&auml;hlt!") . "§";
	} else { # pruefen, ob der Typ stimmt

		//Die Dateierweiterung von dem Original erfragen
		$pext = strtolower(getFileExtension($the_file_name));
		if ($pext == "doc")
			$doc=TRUE;
		
		//Erweiterung mit Regelliste in config.inc.php vergleichen
		if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
			if ($UPLOAD_TYPES[$SessSemName["art_num"]]["type"] == "allow") {
				$t=TRUE;
				$i=1;
				foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
					if ($pext == $ft)
						$t=FALSE;
					if ($i !=1)
						$exts.=",";
					$exts.=" ".strtoupper($ft);
					$i++;
					}
				if (!$t) {
					if ($i==2)
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen den Dateityp %s nicht hochladen!"), trim($exts)) . "§";
					else
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen die Dateitypen %s nicht hochladen!"), trim($exts)) . "§";
					if ($doc)
						if (!$auth->auth["jscript"])
							$emsg.= "info§" . sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_new\" href=\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\">", "</a>") . "§";
						else
							$emsg.= "info§<script language=\"Javascript\">{fenster=window.open(\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\",\"help\",\"scrollbars=yes,width=620,height=400\");} </script>" . _("Hilfe zum Upload von Word-Dokumenten bekommen Sie in dem soeben ge&ouml;ffneten Hilfefenster!") . "§";
					}
				}
			else {
				$t=FALSE;
				$i=1;
				foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
					if ($pext == $ft)
						$t=TRUE;
					if ($i !=1)
						$exts.=",";
					$exts.=" ".strtoupper($ft);
					$i++;
					}
				if (!$t) {
					if ($i==2)
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen nur den Dateityp %s hochladen!"), trim($exts)) . "§";
					else
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen nur die Dateitypen %s hochladen!"), trim($exts)) . "§";
					if ($doc)
						if (!$auth->auth["jscript"])
							$emsg.= "info§" . sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_new\" href=\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\">", "</a>") . "§";
						else
							$emsg.= "info§<script language=\"Javascript\">{fenster=window.open(\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\",\"help\",\"scrollbars=yes,width=620,height=400\");} </script>" . _("Hilfe zum Upload von Word-Dokumenten bekommen Sie in dem soeben ge&ouml;ffneten Hilfefenster!") . "§";
					}
				}
			}
		else {
			if ($UPLOAD_TYPES["default"]["type"] == "allow") {
				$t=TRUE;
				$i=1;
				foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
					if ($pext == $ft)
						$t=FALSE;
					if ($i !=1)
						$exts.=",";
					$exts.=" ".strtoupper($ft);
					$i++;
					}
				if (!$t) {
					if ($i==2)
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen den Dateityp %s nicht hochladen!"), trim($exts)) . "§";
					else
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen die Dateitypen %s nicht hochladen!"), trim($exts)) . "§";
					if ($doc)
						if (!$auth->auth["jscript"])
							$emsg.= "info§" . sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_new\" href=\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\">", "</a>") . "§";
						else
							$emsg.= "info§<script language=\"Javascript\">{fenster=window.open(\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\",\"help\",\"scrollbars=yes,width=620,height=400\");} </script>" . _("Hilfe zum Upload von Word-Dokumenten bekommen Sie in dem soeben ge&ouml;ffneten Hilfefenster!") . "§";
					}
				}					      				
			else {
				$t=FALSE;
				$i=1;
				foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
					if ($pext == $ft)
						$t=TRUE;
					if ($i !=1)
						$exts.=",";
					$exts.=" ".strtoupper($ft);
					$i++;
					}
				if (!$t) {
					if ($i==2)
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen nur den Dateityp %s hochladen!"), trim($exts)) . "§";
					else
						$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Sie d&uuml;rfen nur die Dateitypen %s hochladen!"), trim($exts)) . "§";
					if ($doc)
						if (!$auth->auth["jscript"])
							$emsg.= "info§" . sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_new\" href=\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\">", "</a>") . "§";
						else
							$emsg.= "info§" . sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_new\" href=\"help/index.php?referrer_page=datei.inc.php&doc=TRUE\">", "</a>") . "§";
					}
				}
			}
		
		//pruefen ob die Groesse stimmt.
		if ($the_file_size > $max_filesize) {
			$emsg.= "error§" . sprintf(_("Die Datei konnte nicht &uuml;bertragen werden: Die maximale Gr&ouml;sse f&uuml;r einen Upload (%s Megabyte) wurde &uuml;berschritten!"), $max_filesize / 1048576);
		}
	}
	if ($emsg) {
		$msg.=$emsg;
		return FALSE;
		}
	else
		return TRUE;
} 

//der eigentliche Upload
function upload($the_file) {
	global $UPLOAD_PATH, $dokument_id,$the_file_name, $msg;

	if (!validate_upload($the_file)) {
		return FALSE;
		} 
	else { # cool, es geht weiter

		//Dokument_id erzeugen
		$dokument_id=md5(uniqid(rand()));

		//Erzeugen des neuen Speicherpfads
		$newfile = "$UPLOAD_PATH/$dokument_id";

		//Kopieren und Fehlermeldung
		if (!@copy($the_file,$newfile)) {
			$msg.= "error§" . _("Datei&uuml;bertragung gescheitert!");
			return FALSE;
		} else {
			$msg="msg§" . _("Die Datei wurde erfolgreich auf den Server &uuml;bertragen!");
			return TRUE;
		}
	}
} 


//Erzeugen des Datenbankeintrags zur Datei
function insert_entry_db($range_id, $sem_id=0, $refresh = FALSE) {
	global $the_file_name, $the_file_size, $dokument_id, $description, $name, $user, $upload_seminar_id;
	
	$date = time();				//Systemzeit
	$user_id = $user->id;			// user_id erfragen...
	$range_id = trim($range_id); 		// laestige white spaces loswerden
	$description = trim($description);  	// laestige white spaces loswerden
	$name = trim($name);  			// laestige white spaces loswerden
	
	if (!$name)
		$name = $the_file_name;
	
	if ($the_file_size > 0) {
		$db=new DB_Seminar;
	
		if (!$refresh)
			$query	 = sprintf ("INSERT INTO dokumente SET dokument_id='%s', description='%s', mkdate='%s', chdate='%s', range_id='%s', filename='%s', name='%s', "
					. "user_id='%s', seminar_id='%s', filesize='%s', autor_host='%s'",
					$dokument_id, $description, $date, $date, $range_id, $the_file_name, $name, 
					$user_id, $upload_seminar_id, $the_file_size, getenv("REMOTE_ADDR"));
		else	
			$query	 = sprintf ("UPDATE dokumente SET dokument_id='%s', chdate='%s', filename='%s', "
					. "user_id='%s', filesize='%s', autor_host='%s' WHERE dokument_id = '%s' ",
					$dokument_id, $date, $the_file_name, $user_id, $the_file_size, getenv("REMOTE_ADDR"), $refresh);
	
		$db->query($query);
		if ($db->affected_rows())
			return TRUE;
		else
			return FALSE;
	} else
		return FALSE;
}





function JS_for_upload() {

	global $UPLOAD_TYPES, $SessSemName;
	
	?>
	 <SCRIPT LANGUAGE="JavaScript">
	<!-- Begin

	function open_helptext() {
		fenster=window.open("help/index.php?referrer_page=datei.inc.php&doc=TRUE","help","scrollbars=yes,width=620,height=400");
	}

	var upload=false;

	function upload_end()
	{
	if (upload)
		{
		msg_window.close();
		}
	return;
	}

	function upload_start()
	{
	file_name=document.upload_form.the_file.value
	if (!file_name)
	     {
	     alert("<?=_("Bitte wählen Sie eine Datei aus!")?>");
	     document.upload_form.the_file.focus();
	     return false;
	     }

	if (file_name.charAt(file_name.length-1)=="\"") {
	 ende=file_name.length-1; }
	else  {
	 ende=file_name.length;  }
	
	ext=file_name.substring(file_name.lastIndexOf(".")+1,ende);
	ext=ext.toLowerCase();
		
	if (<?
	if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
		if ($UPLOAD_TYPES[$SessSemName["art_num"]]["type"] == "allow") {
			$i=1;
			foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
				if ($i !=1)
					echo " && ";				
				echo "ext == \"$ft\"";
				$i++;
				if ($ft=="doc")
					$deny_doc=TRUE;
				}
			}
		else {
			$i=1;
			$deny_doc=TRUE;
			foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
				if ($i !=1)
					echo " && ";				
				echo "ext != \"$ft\"";
				$i++;
				if ($ft=="doc")
					$deny_doc=FALSE;
				}
			}
		}
	else {
		if ($UPLOAD_TYPES["default"]["type"] == "allow") {
			$i=1;
			foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
				if ($i !=1)
					echo " && ";				
				echo "ext == \"$ft\"";
				$i++;
				if ($ft=="doc")
					$deny_doc=TRUE;
				}
			}
		else {
			$i=1;
			$deny_doc=TRUE;
			foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
				if ($i !=1)
					echo " && ";
				echo "ext != \"$ft\"";
				$i++;
				if ($ft=="doc")
					$deny_doc=FALSE;
				}
			}
		}
	?>)
	     {
	     alert("<?=_("Dieser Dateityp ist nicht zugelassen!")?>");
	     document.upload_form.the_file.focus();
	     <? if ($deny_doc) { ?>
		if (ext == "doc")
			open_helptext();
	     <? } ?>
	     
	     return false;
	     }

	if (file_name.lastIndexOf("/") > 0)
	     {
	     file_only=file_name.substring(file_name.lastIndexOf("/")+1,ende);
	     }
	if (file_name.lastIndexOf("\\") > 0)
	     {
	     file_only=file_name.substring(file_name.lastIndexOf("\\")+1,ende);
	     }

	msg_window=window.open("","messagewindow","height=250,width=200,left=20,top=20,scrollbars=no,resizable=no,toolbar=no");
	msg_window.document.write("<html><head><title>Datei Upload</title></head>");
	msg_window.document.write("<body bgcolor='#ffffff'><center><p><img src='pictures/alienupload.gif' width='165' height='125'></p>");
	msg_window.document.write("<p><font face='arial, helvetica, sans-serif'><b>&nbsp;"+file_only+"</b><br>&nbsp;<?=_("wird hochgeladen.")?><br>&nbsp;<?=_("Bitte haben sie etwas Geduld!")?><br /></font></p></body></html>");

	upload=true;

	return true;
	}

	// End -->
	</script>
	<?
	}


//Steuerungsfunktion 
function upload_item ($range_id, $create = FALSE, $echo = FALSE, $refresh = FALSE) {
	global $the_file;

	if ($create) {
		if (upload($the_file))
			if (insert_entry_db($range_id, 0, $refresh))
				if ($refresh)
					delete_document($refresh, TRUE);
		return;
		} 
	 else {
		if ($echo) {
			echo form($refresh);
			return;
			}
		else
			return form($refresh);
		}
}


function insert_link_db($range_id, $the_file_size, $refresh = FALSE) {
	global $the_file_name, $the_link, $description, $name, $user, $upload_seminar_id, $protect;
	
	$date = time();				//Systemzeit
	$user_id = $user->id;			// user_id erfragen...
	$range_id = trim($range_id); 		// laestige white spaces loswerden
	$description = trim($description);  	// laestige white spaces loswerden
	$name = trim($name);  			// laestige white spaces loswerden
	$dokument_id=md5(uniqid(rand()));
	
	// $the_file_name = substr(strrchr($the_link,"/"), 1);
	
	$url_parts = parse_url($the_link);
	$the_file_name = basename($url_parts['path']);
	
	if ($protect=="on")
		$protect = 1;
	
	if (!$name)
		$name = $the_file_name;
	
	$db=new DB_Seminar;
	
		if (!$refresh)
			$query	 = sprintf ("INSERT INTO dokumente SET dokument_id='%s', description='%s', mkdate='%s', chdate='%s', range_id='%s', filename='%s', name='%s', "
					. "user_id='%s', seminar_id='%s', filesize='%s', autor_host='%s', url='%s', protected='$protect'",
					$dokument_id, $description, $date, $date, $range_id, $the_file_name, $name, 
					$user_id, $upload_seminar_id, $the_file_size, getenv("REMOTE_ADDR"), $the_link);
		else	
			$query	 = sprintf ("UPDATE dokumente SET dokument_id='%s', chdate='%s', filename='%s', "
					. "user_id='%s', filesize='%s', autor_host='%s' WHERE dokument_id = '%s' ",
					$dokument_id, $date, $the_file_name, $user_id, $the_file_size, getenv("REMOTE_ADDR"), $refresh);
	
		$db->query($query);
		if ($db->affected_rows())
			return TRUE;
		else
			return FALSE;
}


function link_item ($range_id, $create = FALSE, $echo = FALSE, $refresh = FALSE, $link_update = FALSE) {
	global $the_link, $name, $description, $protect;

	if ($create) {
		$link_data = parse_link($the_link);
		if ($link_data["HTTP/1.0 200 OK"] || $link_data["HTTP/1.1 200 OK"] || $link_data["HTTP/1.1 302 Found"] || $parsed_link["HTTP/1.0 302 Found"]) {
			if (!$link_update) {
				if (insert_link_db($range_id, $link_data["Content-Length"], $refresh))
					if ($refresh)
						delete_link($refresh, TRUE);
				$tmp = TRUE;
			} else {
				edit_item ($link_update, FALSE, $name, $description, $protect , $the_link);
				$tmp = TRUE;
			}
		} else {
			$tmp = FALSE;	
			
		}
		return $tmp;
		
	} else {
		if ($echo) {
			echo link_form($refresh,$link_update);
			return;
		} else {
			return link_form($refresh,$link_update);
		}
	}
}

/*
function linkcheck ($URL) {
	$fp = @fopen($URL, "r");
	if (!$fp) {
		return FALSE;
	} else {
		fclose($fp);
		return TRUE;
	}
}
*/


function link_form ($range_id, $updating=FALSE) {
	global $SessSemName, $the_link, $protect, $description, $name, $folder_system_data;
	if ($folder_system_data["update_link"])
		$updating = TRUE;
	if ($protect=="on") $protect = "checked";
	$print = "";
	if ($updating == TRUE) {
		$db=new DB_Seminar;
		$db->query("SELECT * FROM dokumente WHERE dokument_id='$range_id'");
		if ($db->next_record()) {
			$the_link = $db->f("url");
			$protect = $db->f("protected");
			if ($protect==1) $protect = "checked";
			$name = $db->f("name");
			$description = $db->f("description");
		}
	}
	if ($folder_system_data["linkerror"]==TRUE) {
		$print.="<hr><img src=\"pictures/x.gif\" align=\"left\"><font color=\"red\">";
		$print.=_("&nbsp;FEHLER: unter der angegebenen Adresse wurde keine Datei gefunden.<br>&nbsp;Bitte kontrollieren Sie die Pfadangabe!");
		$print.="</font><hr>";
	}

	$print.="\n<br /><br />" . _("Sie haben diesen Ordner zum Upload ausgewählt:") . "<br /><br /><center><table width=\"90%\" style=\"{border-style: solid; border-color: #000000;  border-width: 1px;}\" border=0 cellpadding=2 cellspacing=3>";

	$print.="</font></td></tr>";
	$print.= "\n<form enctype=\"multipart/form-data\" NAME=\"link_form\" action=\"" . $PHP_SELF . "\" method=\"post\">";
	$print.= "<tr><td class=\"steelgraudunkel\" colspan=2><font size=-1>" . _("1. Geben Sie hier den <b>vollständigen Pfad</b> zu der Datei an die sie verlinken wollen.") . " </font></td></tr>";
	$print.= "\n<tr>";
	$print.= "\n<td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Dateipfad:") . "&nbsp;</font><br />";
	$print.= "&nbsp;<INPUT NAME=\"the_link\" TYPE=\"text\"  style=\"width: 70%\" SIZE=\"30\" value=$the_link>&nbsp;</td></td>";
	$print.= "\n</tr>";
	if (!$refresh) {

		$print.= "<tr><td class=\"steelgraudunkel\" colspan=2><font size=-1>" . _("2. Sie können hier angeben, ob es sich um eine urheberrechtlich geschützte Datei handelt.") . "</font></td></tr>";
		$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Geschützt:") . "&nbsp;</font>";
		$print.= "\n&nbsp;<input type=\"CHECKBOX\" name=\"protect\" $protect></td></tr>";

		$print.= "<tr><td class=\"steelgraudunkel\" colspan=2><font size=-1>" . _("3. Geben Sie eine kurze Beschreibung und einen Namen für die Datei ein.") . "</font></td></tr>";
		$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Name:") . "&nbsp;</font><br>";
		$print.= "\n&nbsp;<input type=\"TEXT\" name=\"name\" style=\"width: 70%\" size=\"40\" maxlength\"255\" value=$name></td></tr>";
						
		$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Beschreibung:") . "&nbsp;</font><br>";
		$print.= "\n&nbsp;<TEXTAREA NAME=\"description\"  style=\"width: 70%\" COLS=40 ROWS=3 WRAP=PHYSICAL>$description</TEXTAREA>&nbsp;</td></tr>";
		$print.= "\n<tr><td class=\"steelgraudunkel\"colspan=2 ><font size=-1>" . _("4. Klicken Sie auf <b>'absenden'</b>, um die Datei zu verlinken") . "</font></td></tr>";
	} else
		$print.= "\n<tr><td class=\"steelgraudunkel\"colspan=2 ><font size=-1>" . _("2. Klicken Sie auf <b>'absenden'</b>, um die Datei hochzuladen und damit die alte Version zu &uuml;berschreiben.") . "</font></td></tr>";
	$print.= "\n<tr><td class=\"steel1\" colspan=2 align=\"center\" valign=\"center\">";	
	$print.= "\n<input type=\"image\" " . makeButton("absenden", "src") . " value=\"Senden\" align=\"absmiddle\" onClick=\"return upload_start();\" name=\"create\" border=\"0\">";
	$print.="&nbsp;<a href=\"$PHP_SELF?cancel_x=true\">" . makeButton("abbrechen", "img") . "</a></td></tr>";	
	
	$print.= "\n<input type=\"hidden\" name=\"upload_seminar_id\" value=\"".$SessSemName[1]."\">";	
	if ($updating == TRUE) {
		$print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"link_update\">";	
		$print.= "\n<input type=\"hidden\" name=\"link_update\" value=\"$range_id\">";
	} else {
		$print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"link\">";		
	}
	$print.= "\n</form></table><br /></center>";

	return $print;
	
}

## Ende Upload Funktionen ################################################################################

//create the folder-system
function display_folder_system ($folder_id, $level, $open, $lines, $change, $move, $upload, $all, $refresh=FALSE, $filelink="") {
	global $_fullname_sql,$SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow, $rechte, $anfang, $PHP_SELF, 
		$user, $SemSecLevelWrite, $SemUserStatus, $check_all;

	if (!$anfang)
		$anfang = $folder_id;
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;	
	
	$check_folder = getFolderChildren($folder_id);
	
	$lines[$level] = $check_folder[1];

	if (($check_folder[1]) || ($all)) {
	$db->query("SELECT ". $_fullname_sql['full'] ." AS fullname , username, folder_id, range_id, a.user_id, name, description, a.mkdate, a.chdate FROM folder a LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE range_id = '$folder_id' ORDER BY a.name, a.chdate");
	while (($db->next_record()) || (($all) && (!$cnt))) {	
		$cnt++; //a very hard hack to fix the problem, that no documents in view "all documents" are shown, if the "general folder" was deleted. Not good. But works...
		if (!$all) {?><table border=0 cellpadding=0 cellspacing=0 width="100%"><tr><td class="blank" valign="top" heigth=21 nowrap><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'><?}

		if ($level) { //Hier eine bezaubernde Routine um die Striche exakt wiederzugeben
			$striche = "";
				for ($i=0;$i<$level;$i++) {
					if ($i==($level-1)) {
						if ($lines[$i+1]>1) 
							$striche.= "<img src='pictures/forumstrich3.gif' border=0>"; 		//Kreuzung
						else
							$striche.= "<img src='pictures/forumstrich2.gif' border=0>"; 		//abknickend
						$lines[$i+1] -= 1;
						}
					else {
						if ($lines[$i+1]==0) 
							$striche .= "<img src='pictures/forumleer.gif' border=0>";		//Leerzelle
						else
							$striche .= "<img src='pictures/forumstrich.gif' border=0>";		//Strich
						}
					}
				if (!$all) echo $striche;				
			}
		if (!$all) echo "</td>";

		
		if (!$all) {
			$das_letzte = getFolderChildren($db->f("folder_id"));
			$letzter = $das_letzte[1]; 		// wenn $letzter = 0 ist gibt es keinen untergeordneten Ordner mehr
			$documents_count = doc_count($db->f("folder_id"));
			$newest_document = doc_newest($db->f("folder_id"));
			$dok_letzter = $documents_count; // wenn $dok_letzter = 0 ist gibt es keine Dokumente in dem Ordner
		}
		
		//Ordner aufgeklappt
		if ((strstr($open,$db->f("folder_id"))) || ($all)) { 
			$content='';
			
			//Icon auswaehlen
			if ($documents_count){ //Dokumente und Dateien vorhanden
				$icon="<img src=\"pictures/cont_folder.gif\">";	
			
			}
			else
				$icon="<img src=\"pictures/cont_folder2.gif\">";				
			
			if ($move)
				$icon="&nbsp;<a href=\"$PHP_SELF?open=".$db->f("folder_id")."_md_\"><img src=\"pictures/move.gif\" border=0 " . tooltip(_("Objekt in diesen Ordner verschieben")) . "/></a>".$icon;
			
			//Link erstellen
			$link=$PHP_SELF."?close=".$db->f("folder_id")."#anker";

			//Titelbereich erstellen
			$tmp_titel=htmlReady(mila($db->f("name")));
			if ($change == $db->f("folder_id")) { //Aenderungsmodus, Anker + Formular machen, Font tag direkt ausgeben (muss ausserhalb einer td stehen!
				$titel= "<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($db->f("name"))."\" />";
				}
			else {
				//create a link onto the titel, too
				if ($link)
					$tmp_titel = "<a href=\"$link\" class=\"tree\" >$tmp_titel</a>";

				if ($documents_count > 1)
					$titel= $tmp_titel."&nbsp;&nbsp;" . sprintf(_("(%s Dokumente)"), $documents_count);
				elseif ($documents_count)
					$titel= $tmp_titel." </b>&nbsp;&nbsp;" . _("(1 Dokument)");
				else
					$titel= $tmp_titel;		
				}
	
			//Workaround for older data from previous versions (chdate is 0)
			$chdate = (($db->f("chdate")) ? $db->f("chdate") : $db->f("mkdate"));
		
			//Zusatzangaben erstellen
			$zusatz="<a href=\"about.php?username=".$db->f("username")."\"><font color=\"#333399\">".$db->f("fullname")."</font></a>&nbsp;".date("d.m.Y - H:i",$chdate)."";			

			
			if ($loginfilelast[$SessSemName[1]] < $chdate) 
				$neuer_ordner = TRUE;
			else
				$neuer_ordner = FALSE;
			
			//Objekttitelzeile ausgeben
			if (!$all) printhead ("99%", 0, $link, "open", $neuer_ordner, $icon, $titel, $zusatz, $newest_document);
					
			//Striche erzeugen
			$striche = "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
			for ($i=0;$i<$level;$i++) {
				if ($lines[$i+1]==0) 
					$striche .= "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
				else 
					$striche .= "<td class=\"blank\" nowrap background='pictures/forumstrich.gif'><img src='pictures/forumleer2.gif'></td>";
					}
			$striche2='';
			if (($letzter > 0) || ($dok_letzter > 0))
				$striche2.= "<td class=\"blank\" nowrap background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/forumleer.gif\"></td>";
			else 
				$striche2.= "<td class=\"blank\" nowrap background=\"pictures/steel1.jpg\"><img src=\"pictures/forumleer.gif\"></td>";
			
			//Ankerlogik
			if (($change) || ($move) || ($upload)) {
				if (($change == $db->f("folder_id")) ||  ($move == $db->f("folder_id")) ||  ($upload == $db->f("folder_id")))
					echo "<a name='anker'></a>";
				}
			elseif ($db->f("folder_id") == substr($open, strlen($open) -32, strlen ($open)))
					echo "<a name='anker'></a>";
			
			//Contentbereich erstellen
			if ($change == $db->f("folder_id")) { //Aenderungsmodus, zweiter Teil
				$content.="<textarea name=\"change_description\" rows=3 cols=40>".htmlReady($db->f("description"))."</textarea><br /><br />";
				$content.="<input type=\"image\"" . makeButton("uebernehmen", "src") . " align=\"absmiddle\" value=\""._("&Auml;nderungen speichern")."\">&nbsp;";
				$content.="<input type=\"image\"" . makeButton("abbrechen", "src") . " align=\"absmiddle\" name=\"cancel\" value=\""._("Abbrechen")."\">";
				$content.= "<input type=\"hidden\" name=\"open\" value=\"".$db->f("folder_id")."_sc_\" />";
				$content.="<input type=\"hidden\" name=\"type\" value=1 />";
				}
			elseif ($db->f("description"))
				$content= htmlReady($db->f("description"), TRUE, TRUE);
			else
				$content= _("Keine Beschreibung vorhanden");
			
			if ($move == $db->f("folder_id")) 
				$content.="<br />" . sprintf(_("Dieser Ordner wurde zum Verschieben markiert. Bitte w&auml;hlen Sie das Einf&uuml;gen-Symbol %s, um ihn in den gew&uuml;nschten Ordner zu verschieben."), "<img src=\"pictures/move.gif\" border=0 " . tooltip(_("Klicken Sie auf dieses Symbol, um diesen Ordner in einen anderen Ordner einzufügen.")) . ">");
			
			if ($upload == $db->f("folder_id")) {
				$content.=upload_item ($upload,FALSE,FALSE,$refresh);
				}
				
			// Abfrage ob Dateilink eingeleitet

			if ($filelink == $db->f("folder_id")) {
				$content .= link_item($range_id);
			}

			$content.= "\n";
			$edit='';
					
			//Editbereich erstellen
			if (($change != $db->f("folder_id")) && ($upload != $db->f("folder_id")) && ($filelink != $db->f("folder_id"))) {
				if (($rechte) || ($SemUserStatus == "autor")) {
					$edit= "<a href=\"$PHP_SELF?open=".$db->f("folder_id")."_u_&rand=".rand()."#anker\">" . makeButton("dateihochladen", "img") . "</a>";
					if ($rechte)
						$edit.= "&nbsp;<a href=\"$PHP_SELF?open=".$db->f("folder_id")."_l_&rand=".rand()."#anker\">" . makeButton("link", "img") . "</a>";
					if ($documents_count)
						$edit.= "&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?folderzip=".$db->f("folder_id")."\">" . makeButton("ordneralszip", "img") . "</a>";
					if ($rechte) {
						$edit.= "&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?open=".$db->f("folder_id")."_n_#anker\">" . makeButton("neuerordner", "img") . "</a>"; 
						if (($letzter == 0) && ($dok_letzter==0) && ($db->f("range_id") != $SessSemName[1])) {						
							$edit.= " <a href=\"$PHP_SELF?open=".$db->f("folder_id")."_d_\">" . makeButton("loeschen", "img") . "</a>";
							}
						$edit.= " <a href=\"$PHP_SELF?open=".$db->f("folder_id")."_m_#anker\">" . makeButton("verschieben", "img") . "</a>";	
						if (!$level==0)
							$edit.= " <a href=\"$PHP_SELF?open=".$db->f("folder_id")."_c_#anker\">" . makeButton("bearbeiten", "img") . "</a>";
						}
					}
				}

			if (!$all) {?><td class="blank" width="*">&nbsp;</td></tr></table><table width="100%" cellpadding=0 cellspacing=0 border=0><tr><?}

			if (!$all) echo $striche.$striche2;			
			
			//Ordner-Content ausgeben
			if (!$all) printcontent ("99%", TRUE, $content, $edit);
			
			$s=0;
			if ($all) {
				$db3->query("SELECT ". $_fullname_sql['full'] ." AS fullname, username, a.user_id, a.*, IFNULL(a.name, a.filename) AS t_name FROM dokumente a LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_id = '".$folder_id."' ORDER BY a.chdate DESC");
				$documents_count = $db3->num_rows();
			} else {
				$db3->query("SELECT ". $_fullname_sql['full'] ." AS fullname, username, a.user_id, a.*, IFNULL(a.name, a.filename) AS t_name FROM dokumente a LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE range_id = '".$db->f("folder_id")."' ORDER BY t_name, a.chdate DESC");
			}
			//Hier wird der Ordnerinhalt (Dokumente) gelistet
			if ($documents_count){
				while ($db3->next_record()) { 			
					
					$s++;
					if (($dok_letzter == $s) && (!$letzter))
					$striche3="<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumstrich2.gif'></td>"; //Knick
					else
					$striche3="<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumstrich3.gif'></td>"; //Verzweigung
					
					if ($db3->f("url")!="") {
						$type = 6;
					} else {
						$type = 0;
					}
										
						//Icon auswaehlen
						if ((getFileExtension(strtolower($db3->f("filename"))) == "rtf") || (getFileExtension(strtolower($db3->f("filename"))) == "doc"))
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/rtf-icon.gif' border=0></a>";
						elseif (getFileExtension(strtolower($db3->f("filename"))) == "xls")
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/xls-icon.gif' border=0></a>";
						elseif ((getFileExtension(strtolower($db3->f("filename"))) == "zip") || (getFileExtension(strtolower($db3->f("filename"))) == "tgz") || (getFileExtension(strtolower($db3->f("filename"))) == "gz") || (getFileExtension(strtolower($db3->f("filename"))) == "bz2"))
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/zip-icon.gif' border=0></a>";
						elseif (getFileExtension(strtolower($db3->f("filename"))) == "ppt")
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/ppt-icon.gif' border=0></a>";
						elseif (getFileExtension(strtolower($db3->f("filename"))) == "pdf")
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/pdf-icon.gif' border=0></a>";
						elseif ((getFileExtension(strtolower($db3->f("filename"))) == "gif") || (getFileExtension(strtolower($db3->f("filename"))) == "jpg") ||  (getFileExtension(strtolower($db3->f("filename"))) == "jpe") ||  (getFileExtension(strtolower($db3->f("filename"))) == "jpeg") || (getFileExtension(strtolower($db3->f("filename"))) == "png") || (getFileExtension(strtolower($db3->f("filename"))) == "bmp"))
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/pic-icon.gif' border=0></a>";
						else
						$icon="<a href=\"sendfile.php/?type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\"><img src='pictures/txt-icon.gif' border=0></a>";
					
					//Link erstellen
					if (strstr($open,$db3->f("dokument_id"))) 
					$link=$PHP_SELF."?close=".$db3->f("dokument_id")."#anker";
					else
					$link=$PHP_SELF."?open=".$db3->f("dokument_id")."#anker";
					
					//Workaround for older data from previous versions (chdate is 0)
					$chdate = (($db3->f("chdate")) ? $db3->f("chdate") : $db3->f("mkdate"));
					
					//Titelbereich erstellen
					if ($change == $db3->f("dokument_id"))
					$titel= "<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($db3->f("name"))."\" />";
					else {
						$tmp_titel=mila($db3->f("t_name"));
						
						//create a link onto the titel, too
						if ($link)
						$tmp_titel = "<a href=\"$link\" class=\"tree\" >$tmp_titel</a>";
						
						//add the size
						if (($db3->f("filesize") /1024 / 1024) >= 1)
						$titel= $tmp_titel."&nbsp;&nbsp;(".round ($db3->f("filesize") / 1024 / 1024)." MB";
						else
						$titel= $tmp_titel."&nbsp;&nbsp;(".round ($db3->f("filesize") / 1024)." kB";
						
						//add number of downloads
						$titel .= " / ".(($db3->f("downloads") == 1) ? $db3->f("downloads")." "._("Download") : $db3->f("downloads")." "._("Downloads")).")";
						
						//Zusatzangaben erstellen
						$zusatz="<a href=\"about.php?username=".$db3->f("username")."\"><font color=\"#333399\">".$db3->f("fullname")."</font></a>&nbsp;".date("d.m.Y - H:i", $chdate);
						if (($all) && (!$upload) && ($db3->f("url")=="")) {
							$zusatz.=sprintf ("<input type=\"CHECKBOX\" %s name=\"download_ids[]\" value=\"%s\" />",($check_all) ? "checked" : "" , $db3->f("dokument_id"));
						}
					}
					
					?><td class="blank" width="*">&nbsp;</td></tr></table><table width="100%" cellpadding=0 cellspacing=0 border=0><tr><?
					
					if (!$all) echo $striche.$striche3;
					else {
						?><td class="blank" width="*">&nbsp;</td><?
					}
					
					//Neue Datei herausfinden
					if ($loginfilelast[$SessSemName[1]] < $chdate)
						$neue_datei = TRUE;
					else
						$neue_datei = FALSE;
					
					if ($db3->f("protected")==1)
						$icon .= "<img src=\"pictures/ausruf_small3.gif\" ".tooltip(_("Diese Datei ist urheberrechtlich geschützt!")).">";
					
					//Dokumenttitelzeile ausgeben
					if (strstr($open,$db3->f("dokument_id"))) 
					printhead ("90%", 0, $link, "open", $neue_datei, $icon, $titel, $zusatz, $chdate);
					else
					printhead ("90%", 0, $link, "close", $neue_datei, $icon, $titel, $zusatz, $chdate);
					
					//Dokumentansicht aufgeklappt 
					if (strstr($open,$db3->f("dokument_id"))) {  
						$content='';
						
						if (($dok_letzter == $s) && (!$letzter))
						$striche4="<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer2.gif'></td>";
						else
						$striche4="<td class=\"blank\" nowrap background='pictures/forumstrich.gif'><img src='pictures/forumleer2.gif'></td>";					
						
						//Ankerlogik
						if (($change) || ($move) || ($upload)) {
							if (($change == $db3->f("dokument_id")) ||  ($move == $db3->f("dokument_id")) ||  ($upload == $db3->f("dokument_id")))
							echo "<a name='anker'></a>";
						}
						elseif ($db3->f("dokument_id") == substr($open, strlen($open) -32, strlen ($open)))
						echo "<a name='anker'></a>";
						
						if ($change == $db3->f("dokument_id")) { 	//Aenderungsmodus, Formular aufbauen
							if ($db3->f("protected")==1)
								$protect = "checked";
							$content.= "\n&nbsp;<input type=\"CHECKBOX\" name=\"change_protected\" $protect>&nbsp;"._("geschützter Inhalt")."</br>";
							$content.= "<br /><textarea name=\"change_description\" rows=3 cols=40>".$db3->f("description")."</textarea><br />";
							$content.= "<input type=\"image\" " . makeButton("uebernehmen", "src") . " border=0 value=\""._("&Auml;nderungen speichern")."\" />";
							$content.= "&nbsp;<input type=\"image\" " . makeButton("abbrechen", "src") . " border=0 name=\"cancel\" value=\""._("Abbrechen")."\" />";
							$content.= "<input type=\"hidden\" name=\"open\" value=\"".$db3->f("dokument_id")."_sc_\" />";
							$content.= "<input type=\"hidden\" name=\"type\" value=0 />";
						}
						else {
							if ($db3->f("description"))
							$content= htmlReady($db3->f("description"), TRUE, TRUE);
							else
							$content= _("Keine Beschreibung vorhanden");
							$content.=  "<br /><br />" . sprintf(_("<b>Dateigr&ouml;&szlig;e:</b> %s kB"), round ($db3->f("filesize") / 1024));
							$content.=  "&nbsp; " . sprintf(_("<b>Dateiname:</b> %s "),$db3->f("filename"));
						}
						
						if ($move == $db3->f("dokument_id"))
						$content.="<br />" . sprintf(_("Diese Datei wurde zum Verschieben markiert. Bitte w&auml;hlen Sie das Einf&uuml;gen-Symbol %s, um diese Datei in den gew&uuml;nschten Ordner zu verschieben."), "<img src=\"pictures/move.gif\" border=0 " . tooltip(_("Klicken Sie dieses Symbol, um diese Datei in einen anderen Ordner einzufügen")) . ">");
						
						$content.= "\n";
						
						if ($upload == $db3->f("dokument_id")) {
							$content.=upload_item ($upload,FALSE,FALSE,$refresh);
						}
						
						//Editbereich ertstellen
						$edit='';
						if (($change != $db3->f("dokument_id")) && ($upload != $db3->f("dokument_id")) && $filelink != $db3->f("dokument_id")) {
							if ($db3->f("url")!="") {
								$type = 6;
							} else {
								$type = 0;
							}
							$edit= "&nbsp;<a href=\"sendfile.php/?type=$type&force_download=TRUE&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\">" . makeButton("herunterladen", "img") . "</a>";
							if (($type != "6") && (getFileExtension(strtolower($db3->f("filename"))) != "zip") && (getFileExtension(strtolower($db3->f("filename"))) != "tgz") && (getFileExtension(strtolower($db3->f("filename"))) != "gz") && (getFileExtension(strtolower($db3->f("filename"))) != "bz2"))
								$edit.= "&nbsp;<a href=\"sendfile.php/?zip=TRUE&type=$type&file_id=".$db3->f("dokument_id") ."&file_name=".rawurlencode($db3->f("filename"))."\">" . makeButton("alsziparchiv", "img") . "</a>";
							
							if (($rechte) || ($db3->f("user_id")==$user->id)) {
								if ($type!=6)
									$edit.= "&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?open=".$db3->f("dokument_id")."_c_#anker \">" . makeButton("bearbeiten", "img") . "</a>";
								if ($type==6)
									$edit.= "&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?open=".$db3->f("dokument_id")."_led_&rnd=".rand()."#anker \">" . makeButton("bearbeiten", "img") . "</a>";
								else
									$edit.= "&nbsp;<a href=\"$PHP_SELF?open=".$db3->f("dokument_id")."_rfu_#anker \">" . makeButton("aktualisieren", "img") . "</a>";
								$edit.= "&nbsp;<a href=\"$PHP_SELF?open=".$db3->f("dokument_id")."_m_#anker \">" . makeButton("verschieben", "img") . "</a>";	
								$edit.= "&nbsp;<a href=\"$PHP_SELF?open=".$db3->f("dokument_id")."_fd_\">" . makeButton("loeschen", "img") . "</a>";
							}
						}
						
						
						//Dokument-Content ausgeben
						?><td class="blank" width="*">&nbsp;</td></tr></table><table width="100%" cellpadding=0 cellspacing=0 border=0><tr><?				
						if (!$all) echo $striche.$striche4;
						else {
							?><td class="blank" width="*">&nbsp;</td><?
						}
						
						if ($db3->f("protected")) {
							$content .= "<br><br><hr><table><tr><td><img src=\"pictures/ausruf.gif\" valign=\"middle\"></td><td><font size=\"2\"><b>"
							._("Diese Datei ist urheberrechtlich geschützt.<br>Sie darf nur im Rahmen dieser Veranstaltung verwendet werden, jede weitere Verbreitung ist strafbar!")
							."</td></tr></table>";
						}
						if ($filelink == $db3->f("dokument_id")) {
							$content .= link_item($db3->f("dokument_id"),FALSE,FALSE,$db3->f("dokument_id"));
						}
						
						printcontent ("100%",TRUE, $content, $edit);
					}
				}
			}
			
			if (!$all) 
				echo "<td class=\"blank\">&nbsp;</td></tr></td></table>";
		}			
		
		//Ordner nicht aufgeklappt 
		else {

			//Icon auswaehlen
			if ($documents_count) //Dokumente und Dateien vorhanden
				$icon="<img src=\"pictures/cont_folder.gif\">";	
			else
				$icon="<img src=\"pictures/cont_folder2.gif\">";				

			
			if ($move)
				$icon="&nbsp;<a href=\"$PHP_SELF?open=".$db->f("folder_id")."_md_\"><img src=\"pictures/move.gif\" border=0 " . tooltip(_("Objekt in diesen Ordner verschieben")) . " /></a>".$icon;
			
			//Link erstellen
			$link=$PHP_SELF."?open=".$db->f("folder_id")."#anker";
			
			//Titelbereich erstellen
			$tmp_titel=htmlReady(mila($db->f("name")));

			//create a link onto the titel, too
			if ($link)
				$tmp_titel = "<a href=\"$link\" class=\"tree\" >$tmp_titel</a>";
			
			if ($documents_count > 1)
				$titel= $tmp_titel."&nbsp;&nbsp;" . sprintf(_("(%s Dokumente)"), $documents_count);
			elseif ($documents_count)
				$titel= $tmp_titel." &nbsp;&nbsp;" . _("(1 Dokument)");
			else
				$titel= $tmp_titel;

			//create a link onto the titel, too
			if ($link)
				$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
			
			//Workaround for older data from previous versions (chdate is 0)
			$chdate = (($db->f("chdate")) ? $db->f("chdate") : $db->f("mkdate"));

			//Zusatzangaben erstellen
			$zusatz="<a href=\"about.php?username=".$db->f("username")."\"><font color=\"#333399\">".$db->f("fullname")."</font></a>&nbsp;".date("d.m.Y - H:i",$chdate);
			
			
			if ($loginfilelast[$SessSemName[1]] < $chdate) 
				$neuer_ordner = TRUE;
			else
				$neuer_ordner = FALSE;
				
			//Objekttitelzeile ausgeben
			if (!$all) printhead ("90%", 0, $link, "close", $neuer_ordner, $icon, $titel, $zusatz, $newest_document);
			if (!$all) echo "<td class=\"blank\">&nbsp;</td></tr></td></table>";
			}

		//Rekursiv mit Unterordnern weitermachen	
		if (!$all)
			display_folder_system ($db->f("folder_id"), $level+1, $open, $lines, $change, $move, $upload, $all, $refresh, $filelink);
		}
	}
}


function getLinkPath($file_id) {
	$db = new DB_Seminar;
	$db->query("SELECT url FROM dokumente WHERE dokument_id='$file_id'");
	if ($db->next_record())
		$url = $db->f("url");
	else
		$url = FALSE;
	return $url;	
}

/*
Die function delete_document löscht ein hochgeladenes Dokument.
Der erste Parameter ist die dokument_id des zu löschenden Dokuments.
Der Rückgabewert der Funktion ist bei Erfolg TRUE.
FALSE bedeutet einen Fehler beim Loeschen des Dokumentes.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_document ($dokument_id, $delete_only_file = FALSE) {
	global $UPLOAD_PATH, $msg; // brauchen wir fuer den Pfad zu den Dokumenten

	$db = new DB_Seminar;
	$db->query("SELECT * FROM dokumente WHERE dokument_id='$dokument_id'");
	if ($db->next_record()) { 
		if ($db->f("url")=="") {   //Bei verlinkten Datein nicht nachsehen ob es Datei gibt!
			if (!unlink("$UPLOAD_PATH/$dokument_id"))
				return FALSE;
			elseif ($delete_only_file)
				return TRUE;
		}
	}
		
	// eintrag aus der Datenbank werfen
	$db->query("DELETE FROM dokumente WHERE dokument_id='$dokument_id'");
	
	if ($db->affected_rows())
		return TRUE;
	else 
		return FALSE;
	}


function delete_link ($dokument_id) {
	$db = new DB_Seminar;
	// eintrag aus der Datenbank werfen
	$db->query("DELETE FROM dokumente WHERE dokument_id='$dokument_id'");
	if ($db->affected_rows())
		return TRUE;
	else 
		return FALSE;
}

/*
Die function delete_folder löscht einen kompletten Dateiordner.
Der Parameter ist die folder_id des zu löschenden Ordners.
Der Rückgabewert der Funktion ist bei Erfolg TRUE.
FALSE bedeutet einen Fehler beim Loeschen des Dokumentes.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_folder ($folder_id) {
	global $msg; 
	
	$db = new DB_Seminar;
	
	$db->query("SELECT dokument_id FROM dokumente WHERE range_id='$folder_id'");
	while ($db->next_record())
		if ($delete_document($db->f("dokument_id")))
			$deleted++;

	$db->query("DELETE FROM folder WHERE folder_id='$folder_id'");
	if ($db->affected_rows()) {
		if ($deleted)
			$msg.="info§" . sprintf(_("Der Dateiordner und %s Dokumente wurden gel&ouml;scht"), $deleted) . "§";
		else
			$msg.="info§" . _("Der Dateiordner wurde gel&ouml;scht") . "§";
		return TRUE;
		}
	else {
		if ($deleted)
			$msg.="error§" . sprintf(_("Probleme beim L&ouml;schen des Ordners. %s Dokumente wurden gel&ouml;scht"), $deleted) . "§";
		else
			$msg.="error§" . _("Probleme beim L&ouml;schen des Ordners") . "§";
		return FALSE;
		}
	}
	

//Rekursive Loeschfunktion, loescht erst jeweils enthaltene Dokumente und dann den entsprechenden Ordner
function recursiv_folder_delete ($parent_id) {
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
		
	$doc_count = 0;
	
	$db->query ("SELECT folder_id FROM folder WHERE range_id='$parent_id'");
	 
	while ($db->next_record()) {
		$doc_count += recursiv_folder_delete($db->f("folder_id"));

		$db2->query ("SELECT dokument_id FROM dokumente WHERE range_id='".$db->f("folder_id")."'");

		while ($db2->next_record()) {	 		 	
			if (delete_document($db2->f("dokument_id")))
				$doc_count++;
			}
		 
		 $db2->query ("DELETE FROM folder WHERE folder_id ='".$db->f("folder_id")."'");
		} 
	return $doc_count;
	}


?>
