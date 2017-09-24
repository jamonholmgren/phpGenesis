<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Cookie Library
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 * @package phpGenesis
 */

// cookie_library last edited 2/4/2010 by Jamon Holmgren (added help text)
// TO-DO
//	nothing for now

	load_library('input');	
	
	/**
	 *	Private function that does the the grunt work for cookies()
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_set_cookie")) {
		function _set_cookie($cookie, $value = NULL, $expire_time = NULL) {
			if ($expire_time == NULL) { // Use settings default
				$expire_time = time() + settings('cookie', 'default_expire');
			} else {
				$expire_time = (int)$expire_time;
				if ($expire_time <> 0) { // Add seconds to time();
					$expire_time = time() + $expire_time;					
				} else {
					$expire_time = 0;
				}				
			}			
			
			setcookie($cookie, $value, $expire_time, settings('cookie', 'path'), settings('cookie', 'domain'));	
			$_COOKIE[$cookie] = $value;
			return NULL; // not set
		}
	} // _set_cookie
	
	/**
	 * Without the value, cookies() returns the current value (NULL if not set)
	 * With the value, sets the cookie. Use unset_cookie() to remove it.
	 * Alias is cookie()
	 * 
	 * @return boolean|string
	 */
	if(!function_exists("cookies")) {
		function cookies($cookie, $value = NULL, $expire_days = 30) {
			if($value === NULL) {
				if(cookie_isset($cookie)) return input_cookie($cookie);
				return NULL;
			} else {
				return _set_cookie($cookie, $value, $expire_days * 24 * 60 * 60);
			}
		}
	} // cookies
	
	/**
	 * Alias of cookies()
	 *
	 * @return boolean|string
	 */
	if(!function_exists("cookie")) {
		function cookie($cookie, $value = NULL, $expire_days = 30) {
			return cookies($cookie, $value, $expire_days);
		}
	} // alias of cookies
	
	
	/**
	 * Checks if a cookie has been set and returns true or false
	 *
	 * @return boolean
	 */
	if(!function_exists("cookie_isset")) {
		function cookie_isset($cookie) {
			if(isset($_COOKIE[$cookie])) return true;
			return false;
		} 
	} // cookie_isset
	
	/**
	 * Deletes cookie.
	 *
	 * @return NULL
	 */
	if(!function_exists("unset_cookie")) {
		function unset_cookie($cookie) {
			setcookie($cookie, '', time()-2592000, settings('cookie', 'path'), settings('cookie', 'domain'));	
			unset($_COOKIE[$cookie]);
		}
	} // unset_cookie
?>