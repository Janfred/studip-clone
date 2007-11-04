<?php
function get_error_image($error){
	$errorstring = "image proxy error - " . $error;
	$imagefile = $GLOBALS['STUDIP_BASE_PATH'] . '/data/image_proxy_cache/' . md5($errorstring);
	if(!file_exists($imagefile)){
		$width  = ImageFontWidth(3) * strlen($errorstring) + 3;
		$height = ImageFontHeight(3) + 3 ;
		$im = imagecreate($width,$height);
		$background_color = imagecolorallocate($im, 255, 255, 255); 
		$text_color = imagecolorallocate($im, 255, 0, 0);
		imagestring($im, 3, 1, 1,  $errorstring, $text_color);
		imagegif($im, $imagefile);
	}
	return $imagefile;
}
$MAX_CONTENT_LENGTH = 1000000;
ob_start();
require_once "lib/datei.inc.php";
$url = urldecode($_GET['url']);
$headers = parse_link($url,3);
if($headers['response_code'] != 200 || $headers['Content-Length'] > $MAX_CONTENT_LENGTH || (strpos($headers['Content-Type'],'image') === false)){
	if ($headers['response_code'] != 200) $error = (int)$headers['response_code'];
	elseif ($headers['Content-Length'] > $MAX_CONTENT_LENGTH) $error = 'too big';
	elseif (strpos($headers['Content-Type'],'image') === false) $error = 'no image';
	$imagefile = get_error_image($error);
	$last_modified = filemtime($imagefile);
} else {
	$imagefile = $STUDIP_BASE_PATH . '/data/image_proxy_cache/' . md5($url);
	$last_modified = strtotime($headers['Last-Modified']);
	$file_exists = file_exists($imagefile);
	if(!$file_exists || ($file_exists && filemtime($imagefile) < $last_modified)){
		$image = null;
		$c = 0;
		$f = fopen($url, 'rb');
		if($f){
			while (!feof($f)) {
				$image .= fread($f, 8192);
				++$c;
				if($c * 8192 > $MAX_CONTENT_LENGTH)	break;		
			}
			fclose($f);
		}
		if($c * 8192 < $MAX_CONTENT_LENGTH){
			$f = fopen($imagefile, 'wb');
			fwrite($f, $image);
			fclose($f);
			touch($imagefile, $last_modified);
		} else {
			$imagefile = get_error_image('too big');
			$last_modified = filemtime($imagefile);
		}
	}
}
$if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
if($if_modified_since == $last_modified){
	header("HTTP/1.0 304 Not Modified");
	exit();
}
header('Last-Modified: '. gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
header('Content-Length: '. filesize($imagefile));
header('Content-Type: image/gif');
//header("Pragma: public");
//header("Cache-Control: private");
ob_end_flush();
readfile($imagefile);
?>
