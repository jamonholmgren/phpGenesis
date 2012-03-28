<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	SEO Library
 *	
 *	Various SEO related functions.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *
 * @package phpGenesis
 */

// seo_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Set above with @todo

	/**
	 *	Echos canonical() and robots()
	 *	
	 *	@return null
	 */
	if(!function_exists("seo_write_headers")) {
		function seo_write_headers() {
			echo canonical();
			echo robots();
		}
	} // end seo_write_headers
	
	/**
	 *	Returns HTML for a <link rel="canonical"> from globals settings
	 *	
	 *	@return string
	 */
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
	
	/**
	 *	Returns HTML for <meta name="robots"> from globals settings
	 *	
	 *	@return string
	 */
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
	
	/**
	 *	Sets global "noarchive" for use in robots()
	 *	
	 *	@return null
	 */
	if(!function_exists("noarchive")) {
		function noarchive($on = true) { 			
			globals('meta', 'noarchive', $on);
		}
	} // end noarchive
	
	/**
	 *	Sets global "nofollow" for use in robots()
	 *	
	 *	@return null
	 */
	if(!function_exists("nofollow")) {
		function nofollow($on = true) { 			
			globals('meta', 'nofollow', $on);
		}
	} // end nofollow
	
	/**
	 *	Sets global "noindex" for use in robots()
	 *	
	 *	@return null
	 */
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
			$string = ucwords(str_replace("_id", "", $string)); // remove any *_id strings ...
			return ucwords(str_replace(array("-", "/", "-and-", "_"), array(" ", " - ", "&amp;", " "), $string));
		}
	}
	
	/**
	 *	Tracks a visitor's referring URL for later use. Defaults to storing this information for one day.
	 *	
	 *	@return void
	 */
	if(!function_exists("seo_track_referrer")) {
		function seo_track_referrer($days_to_store = 1) {
			load_library("cookie");
			if(!cookie_isset("seo_referrer")) cookie("seo_referrer", $_SERVER['HTTP_REFERER'], $days_to_store);
		}
	} if(settings("seo", "track_referrer")) register_hook("before_page_load", "seo_track_referrer");
	
	/**
	 *	Returns the previously set referrer.
	 *	
	 *	@return string
	 */
	if(!function_exists("seo_get_referrer")) {
		function seo_get_referrer() {
			load_library("cookie");
			return cookie("seo_referrer");
		}
	}
	
	
	/**
	 *	Returns a string that has been improved in immeasurable ways.
	 *	
	 *	@return string
	 */
	if(!function_exists("chuckify")) {
		function chuckify($string) {
			$chuckisms = array(
				"Move it up one pixel.",
				"Refresh?",
				"Did I ever tell you I owned a software company?",
				"Move it to the right five pixels.",
				"I pretty much taught Oscar Myre SEO. He had to hire me once he learned how smart I was.",
				"I think Google poop-listed us.",
				"Carylyn is trying to take over the company.",
			);
			$a = explode(". ", $string);
			$new_string = "";
			foreach((array)$a as $index => $s) {
				$new_string .= $s;
				if($index < array_count($a) - 1) $new_string .= ". " . array_random($chuckisms) . " ";
			}
			return $new_string;
		}
	}
	
?>