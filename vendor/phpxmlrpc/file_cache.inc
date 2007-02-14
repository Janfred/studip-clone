<?php

// Caching using simple file swap cache. PHP4/PHP5
// Caching code by Rasmus Lerdorf
// February 1, 2006
// Note: If you are using PHP 5.1 or newer, you can save
// a system call by replacing time() with SERVER['REQUEST_TIME']

// The ./ location puts the file in the root of your
// web server,  usually

class File_Cache
{

	// If you make different types of requests, you'll want to implement
	// some sort of mapping from the request to unique filenames.

	function File_Cache()
	{
		if (! isset($GLOBALS['cache_timeout']))
		{
			$GLOBALS['cache_timeout'] = 7200; // two hours 
		}
		if (! isset($GLOBALS['TMP_PATH']))
		{
			$GLOBALS['TMP_PATH'] = "/tmp"; // path for cache_files
		}
	}

	function generate_cache_filename($key)
	{
		@$this->cache_filename = $GLOBALS['TMP_PATH']."/xmlrpc_$key";
		return @$this->cache_filename ;
	}

	function cache($key, $data)
	{
		if ($GLOBALS['allow_webservice_caching'])
		{
			$this->generate_cache_filename($key);
			$tmpf = tempnam($GLOBALS['TMP_PATH'],'YWS');
			$fp = fopen($tmpf,"w");
			fwrite($fp, serialize($data));
			fclose($fp);
			rename($tmpf, $this->cache_filename);
		}
		// This is the main caching routine. More robust error checking should
		// be implemented.
	}

	function request_cache($key, $timeout=7200) 
	{
		if ($GLOBALS['allow_webservice_caching'])
		{
			$this->generate_cache_filename($key);

			if(!file_exists($this->cache_filename) || filemtime($this->cache_filename) < (time()-$timeout)) {
				return null;
			} else {
				return unserialize(file_get_contents($this->cache_filename));
			}
		}
		return null;
	}
}

?>
