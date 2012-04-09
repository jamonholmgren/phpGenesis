<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Whois Library
 *	
 *	Gathers and displays information about a given domain and it's owner.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *
 * @package phpGenesis
 */

// whois_library

	/**
	 * Returns whois information for specified domain.
	 * 
	 * Requires third party plugin Net_Whois (found in phpGenesis core thirdparty folder)
	 * 
	 * 
	 * @return string
	 */

	 // TO-DO: change to thirdparty library loader
	if(!function_exists("whois_get")) {
		function whois_get($domain, $server = NULL, $authoritative = false) {
			load_thirdparty_plugin("net_whois/Whois.php");
			$whois = new Net_Whois;
			if($authoritative) $whois->authorative = true;
			if($server === NULL) {
				$data = $whois->query($domain);
			} else {
				$data = $whois->query($domain, $server);
			}
			return $data;
		}
	} // whois_get
	if(!function_exists("whois_parse")) {
		function whois_parse($domain, $whois_data) {
			$line_array = explode("\n", $whois_data);
			$current_item = "whois";
			foreach((array)$line_array as $line_num => $line) {
				$line = str_replace("://", "%HTTPCOLON%", trim($line));
				if(strpos($line, ":") > 0) {
					list($current_item, $line) = explode(":", $line, 2);
				}
				$line = str_replace("%HTTPCOLON%", "://", $line);
				$data[$current_item] .= $line . "\n";
			}
			return $data;
		}
	} // whois_parse

?>