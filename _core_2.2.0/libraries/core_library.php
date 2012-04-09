<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// core_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	nothing for now
	globals('libraries', 'core', true);
	
	/**
	 * Returns the core version. Use this function to determine if the application is compatible with the core.
	 *
	 * @return string
	 */
	function core_version() {
		if(setting_isset('core', 'version')) return settings('core', 'version');
		return "Core version not found!";
	}
	
	/**
	 * Returns the app version. Use this function to determine if the application is live or still in beta
	 *
	 * @return string
	 */
	function app_version() {
		if(setting_isset('app', 'version')) return settings('app', 'version');
		return "App version not found!";
	}
	
	// Check if it's an ajax request or a regular page load. Can be accessed through is_ajax().
	if(!defined("IS_AJAX_REQUEST")) define('IS_AJAX_REQUEST', ($_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest" || $_SERVER['X-Requested-With'] == "XMLHttpRequest"));
	function is_ajax() {
		return (bool)IS_AJAX_REQUEST;
	}
	
	/**
	 *	Returns if the page request is ajax or not. Works with jQuery, Prototype, Mootools, and YUI.
	 */
	function request_is_ajax() {
		return is_ajax();
	}
	
	
	/**
	 * Core function to determine URL segments and route to the correct page. Normally not called anywhere but in the core.
	 *
	 * @return boolean
	 */
	function route_url($url, $recurse = true) {
		load_library("input"); // load this library on function call, since the load_library function doesn't exist when this library is loaded
		
		$url = input_clean(trim($url, '/\\'));
		$url = trim($url, '/\\');
		
		$segments = _parse_segments($url);
		$key_segments = _parse_key_segments($url);
		
		// No segments so show default page
		if (count($segments) == 0) {
			if($recurse) {
				return route_url(settings('pages', 'home_page'), false);
			} else {
				return false;
			}
		}
		
		globals('segments', 'full_array', $segments);
		globals('segments', 'key_segments', $key_segments);
		
		for($i = count($segments) - 1; $i >= 0; $i--) {
			$tmp = '';
			$root_seg = $segments[$i];
			
			if($i > 0 && ctype_digit($root_seg)) { // forget about digit-only segments -- we know they aren't a page.
				continue;
			} else {
				// Get the full path, but exclude digit-only segments.
				for($j = 0; $j <= $i; $j++) {
					if(!ctype_digit($segments[$j])) {
						$tmp = $tmp . "/" . ltrim($segments[$j], "_");
					}
				}
				
				$filename = APP_FOLDER . '/pages' . $tmp;
				
				globals('segments', 'full', implode('/', $segments));
				if(!global_isset("segments", "original")) globals('segments', 'original', implode('/', $segments));
				if(!global_isset("segments", "original_array")) globals('segments', 'original_array', $segments);
				
				globals('segments', 'page', trim($tmp, "/\\"));
				
				$segments_array = array_slice($segments, $i + 1);
				
				globals('segments', 'array', $segments_array);
				
				if(isset($segments_array[0])) {
					if($segments_array[0] == "add") {
						globals('segments', 'id', NULL);
						globals('segments', 'action', "add");
					} else {
						globals('segments', 'id', $segments_array[0]);
						if(isset($segments_array[1])) {
							globals('segments', 'action', $segments_array[1]);
						} else {
							globals('segments', 'action', "show");
						}
					}
				} else {
					globals('segments', 'id', NULL);
					globals('segments', 'action', "list");
				}
				
				if(file_exists($filename . '.php')) {
					if (call_hook('before_page_load')) { return true; } // page load was handled by event handler
					include($filename . '.php');
					call_hook('after_page_load');
					return true;
				} elseif (file_exists($filename . "/" . $segments[$i] . '.php')) {
					if (call_hook('before_page_load')) { return true; } // page load was handled by event handler
					include($filename . "/" . $segments[$i].'.php');
					call_hook('after_page_load');
					return true;
				} elseif(($root_pos = strpos($root_seg, ".")) !== false) { // Action Routing
					$pos = strpos($tmp, ".");
					$filename = APP_FOLDER . '/pages' . substr($tmp, 0, $pos);
					if(file_exists($filename . '.php')) {
						$segments = array_slice($segments, $i + 1);
						array_unshift($segments, substr($root_seg, $root_pos+1));
						globals('segments', 'array', $segments);
						// globals('segments', 'action', substr($root_seg, $root_pos+1));
						if (call_hook('before_page_load')) return true; // page load was handled by event handler
						include($filename . '.php');
						call_hook('after_page_load');
						return true;
					}
				}
			}
		}

		// 404 No page found
		$result = call_hook('404');
		if($result && globals('segments', 'reroute-attempted') != true) {
			if($result === true) {
				return false;
			} else {
				globals('segments', 'reroute-attempted', true);
				// something told it to break, so just route to what it told us
				route_url($result, false);
				return false;
			}
		} else { // no 404 hook told it to break, or couldn't resolve the one it was told, so go ahead and route it to the 404 page
			route_404($recurse);
		}
	}

	function _parse_segments($url) {
		$segments = array();
		$raw_segments = explode('/', $url);
		
		foreach($raw_segments as $seg) {
			$seg = trim($seg);
			if($seg <> '') $segments[] = $seg;
		}
		return $segments;
	}
	
	function _parse_key_segments($url) {
		$raw_segments = explode('/', $url);
		$key_segments = array();
		
		foreach($raw_segments as $seg) {
			$seg = trim($seg);
			if(!isset($key_segments[$key])) $key_segments[$key] = $seg;
			$key = $seg;
		}
		return $key_segments;
	}

	/**
	 * Core function that routes to the 404 page.
	 *
	 * @return NULL
	 */
	function route_404($recurse = true) {
		if ($recurse == true && route_url(settings('pages', '404_page'), false)) {
			header("HTTP/1.1 404 Not Found");
			die();
		} else {
			header("HTTP/1.1 404 Not Found");
			die("Page not found!");
		}
	}

	function route_access_denied($recurse = true) {
		if ($recurse == true && route_url(settings('pages', 'access_denied'), false)) {
			header("HTTP/1.1 403 Access Denied");
			die();
		} else {
			header("HTTP/1.1 403 Access Denied");
			die("403 Access Denied");
		}
	}

	function _get_request_uri() {
		$len = strlen(dirname($_SERVER['SCRIPT_NAME']));
		return substr($_SERVER['REQUEST_URI'], $len);
	}

	/**
	 * Redirects with header() various redirection codes (301, etc) to different URLs and pages.
	 *
	 * @return NULL
	 */
	function redirect($to = "", $code = 303) {
		if($to == "") { // empty
			$location = $_SERVER['REQUEST_URI'];
		} elseif(substr($to, 0, 4) == 'http') { // absolute
			$location = $to;
		} else { // Relative
			$to = BASE_URL . "/" . ltrim($to, "/");
			$location = $to;
		}
		
		switch($code) {
			case 301: header("HTTP/1.1 301 Moved Permanently"); break;	// Permanent, SEO-friendly redirect
			case 302: header('HTTP/1.1 302 Found'); break;							// Conform re-POST
			case 303: header('HTTP/1.1 303 See Other'); break;	 				// dont cache, always use GET
			case 304: header('HTTP/1.1 304 Not Modified'); break;				// use cache
			case 305: header('HTTP/1.1 305 Use Proxy'); break;					// Not useful
			case 306: header('HTTP/1.1 306 Not Used'); break;						// Not useful
			case 307: header('HTTP/1.1 307 Temporary Redirect'); break;	// Not useful
			default: die("Invalid redirect code {$code}.");							// Not recognized
		}
		
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header("Location: {$location}");
		// header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		exit();
	}

	/**
	 * Gets or sets a global variable. Specify the value to set it.
	 *
	 * @return boolean
	 */
	function &globals($library, $setting, $value = NULL) {
		if($value === NULL) {
			if(isset($GLOBALS['globals'][$library][$setting])) {
				return $GLOBALS['globals'][$library][$setting];
			}
		} else {
			$GLOBALS['globals'][$library][$setting] = $value;
		}
		$null_value = NULL;
		return $null_value;
	} // end globals

	/**
	 * Returns true if the global variable is set, false otherwise.
	 *
	 * @return boolean
	 */
	function global_isset($library, $setting) {
		if(isset($GLOBALS['globals'][$library][$setting])) return true;
		return false;
	} // end global_isset

	/**
	 * Unsets the global variable.
	 *
	 * @return NULL
	 */
	function unset_global($library, $setting) {
		unset($GLOBALS['globals'][$library][$setting]);
	} // end unset_global

	/**
	 * Gets or sets a global setting. Usually this is only set in the config.php file.
	 * If you need a global variable that will change anywhere else, use globals() instead.
	 *
	 * @return mixed
	 */
	function settings($library, $setting = NULL, $value = NULL) {
		if($value === NULL) {
			if($setting === NULL) {
				if(setting_isset($library)) return $GLOBALS['settings'][$library];
			} else {
				if(setting_isset($library, $setting)) return $GLOBALS['settings'][$library][$setting];
			}
		} else {
			$GLOBALS['settings'][$library][$setting] = $value;
		}
		return NULL; // not set
	} // end setting

	/**
	 * Returns true if the global setting is set, false otherwise.
	 *
	 * @return boolean
	 */
	function setting_isset($library, $setting = NULL) {
		if($setting === NULL) {
			if(isset($GLOBALS['settings'][$library])) return true;
		} else {
			if(isset($GLOBALS['settings'][$library][$setting])) return true;
		}
		return false;
	} // end setting_isset

	/**
	 * Unsets a global setting. Very rarely used.
	 *
	 * @return NULL
	 */
	function unset_setting($library, $setting) {
		unset($GLOBALS['settings'][$library][$setting]);
	} // end unset_setting

	/**
	 * Gets or sets a meta tag variable. Use this to specify meta information for pages.
	 * Example:
	 *
	 * In /pages/home.php before the call to the header:
	 *
	 * meta("title", "The Title of the Current Page");
	 *
	 * And in /includes/header.php in the <head> section:
	 *
	 * <title><?=meta("title")?></title>
	 *
	 * @return mixed
	 */
	if(!function_exists("meta")) {	// Becomes BODY Id
		function meta($key, $value = NULL) {
			if($value === NULL) {
				if($key == "title" && !global_isset('meta', "title")) { // if Meta Title is not explicitly set, automatically generate it.
					return segments_title();
				}
				return global_isset('meta', $key) ? globals('meta', $key) : '';
			} else {
				globals('meta', $key, $value);
			}
		}
	} // end meta
		
		
	function exception_handler($exception) {
		$ar = (array)$exception;
		print_pre($ar);
	}
	set_exception_handler("exception_handler");

	
?>