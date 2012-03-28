<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Hooks Library
 *	
 *	Just in case you are curious how hooks work.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *
 * @package phpGenesis
 */

// hooks_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	nothing for now
	
	// [System Hooks]
	// 404 - Before the 404 page is displayed - return true to stop routing, string to reroute to, or false to continue as normal
	
	// [Page Hooks]
	// write_head
	// write_foot
	
	/**
	 *	Alias for call_hook("write_head");
	 *
	 */
	if(!function_exists("head_hook")) {
		function head_hook() {
			call_hook('write_head');
		}
	} // end head_hook
	
	/**
	 *	Alias for call_hook("write_foot");
	 *
	 */
	if(!function_exists("foot_hook")) {
		function foot_hook() {
			call_hook('write_foot');
		}
	} // end foot_hook
	
	/**
	 *	Sets global hooks for use in call_hook()
	 *	
	 *	@return NULL
	 */
	if(!function_exists("register_hook")) {
		function register_hook($hook_name, $callback, $library = NULL) {
			if (!global_isset('hooks', $hook_name)) {
				globals('hooks', $hook_name, array());
			}
			$temp_array = globals("hooks", $hook_name);
			$temp_array[] = array($library, $callback);
			globals('hooks', $hook_name, $temp_array);
		}
	} // end register_hook
	
	/**
	 *	Loops through global hooks and triggers their callback function
	 *	If any function returns true, stop executing hooks loop.
	 *	
	 *	@return string
	 */
	if(!function_exists("call_hook")) {
		function call_hook($hook_name) {
			$break_loop = NULL;
			if (global_isset('hooks', $hook_name)) {
				foreach (globals('hooks', $hook_name) as $hook_array) {
					list($library, $callback) = $hook_array;
					if($library !== NULL) load_library($library);
					$break_loop = $callback();
					if($break_loop) break;
				}
			}
			if($break_loop) return $break_loop;
		}
	} // end call_hook
	
?>