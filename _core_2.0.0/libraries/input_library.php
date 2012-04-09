<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

	if(!function_exists("input_clean")) {
		function input_clean($input, $sanitizer = "filter") {
			if(is_array($input)) { 
				return input_clean_array($input); 
			}
			if($sanitizer == "html" || $sanitizer == "htmlpurifier") {
				return input_clean_html($input);
			} elseif($sanitizer == 'simple') {
				return input_clean_simple($input);
			} else {
				return input_clean_filter($input);
			}
		}
	} // end input_clean
	
	/**
	 *	For compatibility with pre-5.2 PHP versions. filter_var() is essentially a glorified strip_tags()
	 *	so we're just de-glorifying it here.
	**/
	if(!function_exists("filter_var")) {
		function filter_var($input) {
			return strip_tags($input);
		}
	} // end filter_var
	
	if(!function_exists("input_clean_filter")) {
		function input_clean_filter($input) {
			return filter_var($input, FILTER_SANITIZE_STRING);
		}
	} // end input_clean_filter
	
	if(!function_exists("input_clean_simple")) {
		function input_clean_simple($input) {
			$string = stripslashes($input); // Get rid of magic quotes slashes or any slashes whatsoever...
			$string = strip_tags($string); // Get rid of PHP and Javascript script attacks
			return addslashes($string); // Escape strings (quick and dirty way)
		}
	} // end input_clean_simple
	
	/**
	 * TODO: Make this less HTMLPurifier-specific
	 */
	if(!function_exists("input_clean_html")) {
		function input_clean_html($input) {
			load_library("htmlpurifier");
			return purifier_clean($input);
		}
	}

	if(!function_exists("input_clean_array")) {
		function input_clean_array($array) {
			if(is_array($array)) {
				$clean_array = array();
				foreach($array as $k => $v) {
					if(is_array($v)) {
						$clean_array[$k] = input_clean_array($v); // recursive function
					} else {
						$clean_array[$k] = input_clean($v);
					}
				}
				return $clean_array;
			} else {
				return input_clean($array); // not an array, but clean it anyway
			}
		}
	}
	
	if(!function_exists("input_post")) {
		function input_post($key, $html = false) {
			if(isset($_POST[$key])) {
				if(!global_isset('input_post_cleaned', $key)) {
					$sanitizer = settings("input", "sanitizer");
					if($html) $sanitizer = settings("input", "htmlsanitizer");
					$_POST[$key] = input_clean($_POST[$key], $sanitizer);
					globals('input_post_cleaned', $key, true);
				}
				return $_POST[$key];
			} else {
				return NULL; // not set
			}
		}
	} // end input_post
	if(!function_exists("input_post_html")) {
		function input_post_html($key) {
			return input_post($key, true);
		}
	} // end input_post_html
	
	if(!function_exists("input_get")) {
		function input_get($key, $html = false) {
			if(isset($_GET[$key])) {
				if(!global_isset('input_get_cleaned', $key)) {
					$sanitizer = settings("input", "sanitizer");
					if($html) $sanitizer = settings("input", "htmlsanitizer");
					$_GET[$key] = input_clean($_GET[$key], $sanitizer);
					globals('input_get_cleaned', $key, true);
				}
				return $_GET[$key];
			} else {
				return NULL; // not set
			}
		}
	} // end input_get
	if(!function_exists("input_get_html")) {
		function input_get_html($key) {
			return input_get($key, true);
		}
	} // end input_get_html

	if(!function_exists("input_request")) {
		function input_request($key, $html = false) {
			if(isset($_REQUEST[$key])) {
				if(!global_isset('input_req_cleaned', $key)) {
					$sanitizer = settings("input", "sanitizer");
					if($html) $sanitizer = settings("input", "htmlsanitizer");
					$_REQUEST[$key] = input_clean($_REQUEST[$key], $sanitizer);
					globals('input_req_cleaned', $key, true);
				}
				return $_REQUEST[$key];
			} else {
				return NULL; // not set
			}
		}
	} // end input_request
	if(!function_exists("input_request_html")) {
		function input_request_html($key) {
			return input_request($key, true);
		}
	} // end input_request_html

	if(!function_exists("input_cookie")) {
		function input_cookie($key, $html = false) {
			if(isset($_COOKIE[$key])) {
				if(!global_isset('input_cookie_cleaned', $key)) {
					$sanitizer = settings("input", "sanitizer");
					if($html) $sanitizer = settings("input", "htmlsanitizer");
					$_COOKIE[$key] = input_clean($_COOKIE[$key], $sanitizer);
					globals('input_cookie_cleaned', $key, true);
				}
				return $_COOKIE[$key];
			} else {
				return NULL; // not set
			}
		}
	} // end input_cookie
	if(!function_exists("input_cookie_html")) {
		function input_cookie_html($key) {
			return input_cookie($key, true);
		}
	} // end input_cookie_html

	
	/**
	 * Returns a cleaned array containing name, type, tmp_name, error, size
	 *	
	 * @return array
	 */
	if(!function_exists("input_file_array")) {
		function input_file_array($key) {
			if(isset($_FILES[$key])) {
				if(!global_isset('input_file_cleaned', $key)) {
					$_FILES[$key] = input_clean_array($_FILES[$key]);
					globals('input_file_cleaned', $key, true);
				}
				return $_FILES[$key];
			} else {
				return NULL; // not set
			}
		}
	} // end input_file_array
		
?>