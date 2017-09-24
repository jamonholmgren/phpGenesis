<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Cache Library
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	@todo Complete this library
 *	@package phpGenesis
 */
// cache_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Complete cache_purge()
//	Test in real world site


// cache_library() - CURRENTLY IN BETA - not for production use

	/* settings('cache', 'path', APP_FOLDER."/cache"); */

	cache_purge();
  
	/**
	 * Incomplete.
	 * 
	 * @return false
	 */
  function cache_load($key) {
		$cachefile = settings('cache', 'path') . "/" . $key;
		$cachetime = 120 * 60; // 2 hours  
		
		if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
			echo "LOADED";
			include($cachefile);  
			return true;
		}
			
		globals('cache', 'current_cache_file', $cachefile);
		ob_start(); // start the output buffer  
  	// Your normal PHP script and HTML content here  
 		// BOTTOM of your script  
		return false;
	}
	
	/**
	 * Incomplete.
	 * 
	 * @return NULL
	 */
	function cache_save() {	
		$fp = fopen(globals('cache', 'current_cache_file'), 'w'); // open the cache file for writing 
 		fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file  
		fclose($fp); // close the file 
			echo "SAVED"; 
	}
	
	/**
	 * Incomplete.
	 * 
	 * @return NULL
	 */
	function cache_purge() {
		// Not implemented yet
	}
	
?>