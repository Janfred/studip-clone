<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// image_proxy.php
//
// Copyright (c) 2007 André Noack <noack@data-quest.de>
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
//$Id:$

$IMAGE_PROXY_PATH = $GLOBALS['STUDIP_BASE_PATH'] . '/data/image_proxy_cache/';
$IMAGE_PROXY_MAX_CONTENT_LENGTH = 1000000;
$IMAGE_PROXY_CACHE_LIFETIME = 86400;
$IMAGE_PROXY_MAX_FILES_IN_CACHE = 3000;
$IMAGE_PROXY_GC_PROBABILITY = 1;

function get_error_image($error){
	global $IMAGE_PROXY_PATH;
	$errorstring = "image proxy error - " . $error;
	$imagefile = $IMAGE_PROXY_PATH . md5($errorstring);
	if(!file_exists($imagefile)){
		$width  = ImageFontWidth(3) * strlen($errorstring) + 3;
		$height = ImageFontHeight(3) + 3 ;
		$im = imagecreate($width,$height);
		$background_color = imagecolorallocate($im, 255, 255, 255); 
		$text_color = imagecolorallocate($im, 255, 0, 0);
		imagestring($im, 3, 1, 1,  $errorstring, $text_color);
		imagegif($im, $imagefile);
	}
	return array(md5($errorstring), filesize($imagefile));
}

function check_image_cache($id) {
	global $IMAGE_PROXY_CACHE_LIFETIME,$IMAGE_PROXY_PATH;
	$db = new DB_Seminar();
	$ret = null;
	$query = "SELECT *, UNIX_TIMESTAMP(chdate) as last_modified FROM image_proxy_cache WHERE id='$id' AND chdate > FROM_UNIXTIME(".(time() - $IMAGE_PROXY_CACHE_LIFETIME).")";
	$db->query($query);
	if ($db->next_record()){
		$ret = array($db->f('id'), $db->f('last_modified'), $db->f('length'), $db->f('type'));
		if($db->f('error')){
			$ret[0] = md5("image proxy error - " . $db->f('error'));
		}
	}
	return $ret;
}

function refresh_image_cache($id,$type,$length,$error){
	$db = new DB_Seminar();
	$db->queryf("REPLACE INTO image_proxy_cache (id,type,length,error) VALUES ('%s','%s','%s','%s')",
	$id,
	mysql_escape_string($type),
	mysql_escape_string($length),
	mysql_escape_string($error));
	return check_image_cache($id);
}

function garbage_collect_image_cache(){
	global $IMAGE_PROXY_MAX_FILES_IN_CACHE,$IMAGE_PROXY_PATH;
	$db = new DB_Seminar();
	$db->query("SELECT COUNT(*) FROM image_proxy_cache");
	$db->next_record();
	if($db->f(0) > $IMAGE_PROXY_MAX_FILES_IN_CACHE){
		$delete = array();
		$db->query("SELECT id FROM image_proxy_cache ORDER BY chdate ASC LIMIT " . ($db->f(0) - $IMAGE_PROXY_MAX_FILES_IN_CACHE));
		while($db->next_record()){
			$delete[] = $db->f(0);
			@unlink($IMAGE_PROXY_PATH . $db->f(0));
		}
		$db->query("DELETE FROM image_proxy_cache WHERE id IN('".join("','",$delete)."')");
	}
}
ob_start();
require_once "lib/datei.inc.php";
if ((mt_rand() % 100) < $IMAGE_PROXY_GC_PROBABILITY ){
	garbage_collect_image_cache();
}
$url = urldecode($_GET['url']);
$id = md5($url);
if(!($check = check_image_cache($id))){
	$error = '';
	$headers = parse_link($url,3);
	if($headers['response_code'] != 200 || $headers['Content-Length'] > $IMAGE_PROXY_MAX_CONTENT_LENGTH || (strpos($headers['Content-Type'],'image') === false)){
		if (!$headers['response_code']) $error = 'no response';
		elseif ($headers['response_code'] != 200) $error = (int)$headers['response_code'];
		elseif ($headers['Content-Length'] > $IMAGE_PROXY_MAX_CONTENT_LENGTH) $error = 'too big';
		elseif (strpos($headers['Content-Type'],'image') === false) $error = 'no image';
		list(, $length) = get_error_image($error);
		$check = refresh_image_cache($id,'image/gif',$length,$error);
	} else {
		$imagefile = $IMAGE_PROXY_PATH . $id;
		$image = null;
		$c = 0;
		$f = fopen($url, 'rb');
		if($f){
			while (!feof($f)) {
				$image .= fread($f, 8192);
				++$c;
				if($c * 8192 > $IMAGE_PROXY_MAX_CONTENT_LENGTH)	break;		
			}
			fclose($f);
		}
		if($c * 8192 < $IMAGE_PROXY_MAX_CONTENT_LENGTH){
			$f = fopen($imagefile, 'wb');
			fwrite($f, $image);
			fclose($f);
			$check = refresh_image_cache($id, $headers['Content-Type'] ,filesize($imagefile), '');
		} else {
			list(, $length) = get_error_image('too big');
			$check = refresh_image_cache($id,'image/gif',$length,'too big');
		}
	}
}
list($id, $last_modified, $length, $type) = $check;
$if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
if($if_modified_since == $last_modified){
	header("HTTP/1.0 304 Not Modified");
	exit();
}
header('Expires: '. gmdate('D, d M Y H:i:s', $last_modified + $IMAGE_PROXY_CACHE_LIFETIME ) . ' GMT');
header('Last-Modified: '. gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
header('Content-Length: '. $length);
header('Content-Type: '. $type);
ob_end_flush();
readfile($IMAGE_PROXY_PATH . $id);
?>
