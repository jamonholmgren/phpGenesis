<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// seo_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	nothing for now

	if(!function_exists("seo_write_headers")) {
		function seo_write_headers() {
			echo canonical();
			echo robots();
		}
	} // end seo_write_headers
	
	if(!function_exists("canonical")) {
		function canonical($url = NULL) { 
			if($url === NULL) {
				return global_isset('meta', 'canonical') ? 
					"<link rel='canonical' href='" . globals('meta', 'canonical') . "' />" :
					'';
			} else { 
				globals('meta', 'canonical', $url);
			}
		}
	} // end canonical
	
	if(!function_exists("robots")) {
		function robots() { 
			$options = array();
			if (global_isset('meta', 'nofollow') &&  globals('meta', 'nofollow') == true) {
				$options[] = "nofollow";	
			}
			if (global_isset('meta', 'noindex') &&  globals('meta', 'noindex') == true) {
				$options[] = "noindex";
			}
			if (global_isset('meta', 'noarchive') &&  globals('meta', 'noarchive') == true) {
				$options[] = "noarchive";
			}
			
			if (count($options) > 0 ) {
				return 	"<meta name='robots' content='" . implode(",", $options) . "'>";
			}
		}
	} // end robots
	
	if(!function_exists("noarchive")) {
		function noarchive($on = true) { 			
			globals('meta', 'noarchive', $on);
		}
	} // end noarchive
	
	if(!function_exists("nofollow")) {
		function nofollow($on = true) { 			
			globals('meta', 'nofollow', $on);
		}
	} // end nofollow

	if(!function_exists("noindex")) {
		function noindex($on = true) {		
			globals('meta', 'noindex', $on);
		}
	} // end noindex
	
	/**
	 *	Returns a url-friendly string for use in a URL slug
	 *	
	 *	@return string
	 */
	if(!function_exists("slugify")) {
		function slugify($string) {
			$string = strtolower(trim(htmlspecialchars_decode($string, ENT_QUOTES))); // convert to lower
			$string = preg_replace("/[^a-z0-9\-\_\s]/", "", $string); // lower case, dashes, and spaces only allowed
			$string = preg_replace("/\s/", "-", $string); // replace any spaces with dashes
			$string = trim(trim($string, "-"), "_"); // trim off any leading or trailing dashes and underscores
			return $string;
		}
	} // end slugify
	
	/**
	 *	Returns a user-friendly string from a slugified string
	 *	
	 *	@return string
	 */
	if(!function_exists("deslugify")) {
		function deslugify($string) {
			return ucwords(str_replace(array("-", "/", "-and-", "_"), array(" ", " - ", "&amp;", " "), $string));
		}
	}
	
?>