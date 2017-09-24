<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Security Library
 *	
 *	Provides functions to help prevent CSRF (seasurf) attacks. Prerequisites: session_library. If users_library is loaded, uses the current user_id() also.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 * @package phpGenesis
 */
	
	
	/**
	 *	Generates a security token from the session id and app token.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("security_token")) {
		function security_token($object = "", $action = "", $id = 0) {
			load_library("session");
			$uid = 0;
			if(library_is_loaded("users")) $uid = user_id();
			return core_hash(APP_ID . session_key() . $uid . $object . $action . $id, 12);
		}
	}
	
	/**
	 *	Returns the security token string to be used in a query string
	 *	
	 *	@return string
	 */
	if(!function_exists("security_phrase")) {
		function security_phrase($object = "", $action = "", $id = 0) {
			return "_pgt=" . security_token($object, $action, $id);
		}
	}

	
	/**
	 *	Compares the given security token with the real one and returns the result.
	 *	
	 *	@return boolean
	 */
	if(!function_exists("security_valid")) {
		function security_valid($object = "", $action = "", $id = 0) {
			$submitted_token = input_get("_pgt");
			$valid_token = security_token($object, $action, $id);
			if($valid_token == $submitted_token) return TRUE;
			return FALSE;
		}
	}
	
	/**
	 *	Checks the referring URL and verifies that it matches the BASE_URL. Not considered super safe.
	 *	
	 *	@return boolean
	 */
	if(!function_exists("security_check")) {
		function security_check() {
			$http_referer = domain_name($_SERVER['HTTP_REFERER']); // where'd it come from?
			$expected_referer = domain_name(); // current domain
			if($http_referer == $expected_referer) return TRUE;
			return FALSE;
		}
	}
	
?>