<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipFileCache.class.php
//
// 
//
// Copyright (c) 2007 André Noack <noack@data-quest.de>
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+

/**
* Cache implementation using files
*
* @package     studip
* @subpackage  cache
*
* @author    André Noack <noack@data-quest.de>
* @version   2
*/

class StudipFileCache implements StudipCache {
	
	private $dir;
	
	function __construct($args = array()) {
		$this->dir = !empty($args['dir']) ?
					 $args['dir'] : isset($GLOBALS['CACHING_FILECACHE_PATH']) ?
					 $GLOBALS['CACHING_FILECACHE_PATH'] :
					 $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . 'studip_cache';
		$this->dir = rtrim($this->dir, '\\/') . DIRECTORY_SEPARATOR;
		if(!is_dir($this->dir)){
			if(!@mkdir($this->dir, 0700)) throw new Exception('Could not create directory: ' . $this->dir);
		}
	}
	
	public function getCacheDir() {
		return $this->dir;
	}
	
	public function expire($key) {
		if($file = $this->getPathAndFile($key)){
			@unlink($file);
		}
	}
	
	public function read($key) {
		if($file = $this->check($key)){
			$f = @fopen($file, 'rb');
			if ($f) {
				@flock($f, LOCK_SH);
				$result = stream_get_contents($f);
				@flock($f, LOCK_UN);
				@fclose($f);
			}
			return $result;
		}
		return false;
	}
	
	public function write($key, $content, $expire = 43200) {
		$this->expire($key);
		$file = $this->getPathAndFile($key, ($expire ? $expire : 9999999999));
		return @file_put_contents($file, $content, LOCK_EX);
	}
	
	private function check($key){
		if($file = $this->getPathAndFile($key)){
			list($id,$expire) = explode('-',basename($file));
			if (time() < $expire) {
				return $file;
			} else {
				@unlink($file);
			}
		}
		return false;
	}
	
	private function getPathAndFile($key, $expire = null){
		$id = hash('md5', $key);
		$path = $this->dir . substr($id,0,2);
		if(!is_dir($path)){
			if(!@mkdir($path, 0700)) throw new Exception('Could not create directory: ' .$path);
		}
		if(!is_null($expire)){
			return $path . DIRECTORY_SEPARATOR . $id.'-'.(time() + $expire);
		} else {
			$files = @glob($path . DIRECTORY_SEPARATOR . $id . '*');
			if(count($files)){
				return $files[0];
			}
		}
		return false;
	}
	
	public function purge($be_quiet = true){
		if(is_dir($this->dir)){
			$now = time();
			foreach(@glob($this->dir . '*', GLOB_ONLYDIR) as $current_dir){
				foreach(@glob($current_dir . DIRECTORY_SEPARATOR . '*') as $file){
					list($id,$expire) = explode('-', basename($file));
					if ($expire < $now) {
						if(@unlink($file) && !$be_quiet){
							echo "File: $file deleted.\n";
						}
					} else if (!$be_quiet){
						echo "File: $file expires on " . strftime('%x %X', $expire) . "\n";
					}
				}
			}
		}
	}
}
?>