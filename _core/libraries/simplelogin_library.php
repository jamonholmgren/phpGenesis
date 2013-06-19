<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	SimpleLogin Library
 *	
 *	Quick and dirty plaintext user/pass login system.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 *	@todo Document this library
 * @package phpGenesis
 */

// simplelogin_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	See above


	//	Instructions:
	//	In the config.php file, just set simplelogin in the preload library settings:
	//			settings("simplelogin", "users", array("username1" => "password1", "username2" => "password2"));
	//			settings("simplelogin", "adminroot", "admin"); // restricted area root folder
	//			settings("simplelogin", "adminlogin", "admin"); // login page
	//			settings("simplelogin", "adminhome", "admin/dashboard"); // admin home page after login
	//	Note that the adminroot itself, e.g. http://www.example.com/admin/ will not be protected, as this should be
	//	the login page.
	//
	
	load_library("input");
	load_library("cookie");
	load_library("hooks");

	register_hook("page_load", "simple_page_load");
	
	/**
	 *	Handles login/logout automatically using settings passed in options or set in config
	 *	
	 *	Call this at the top of the page where you have the SimpleLogin form
	 *	
	 *	@return bool
	 */
	if(!function_exists("simplelogin")) {
		function simplelogin($validusers = NULL, $redirect_on_logout = NULL, $options = array()) {
			if($validusers === NULL) $validusers = settings("simplelogin", "adminusers");
			if($redirect_on_logout === NULL) $redirect_on_logout = settings("simplelogin", "adminlogin");
			
			if(segment(0) == "log-out" || segment(0) == "logout") {
				simplelogout($redirect_on_logout);
				return false;
			}
			
			if(globals("simplelogin", "loggedin") === true) return true; // just verify once per load
			// Options
			$username_field = "username";
			$password_field = "password";
			$timeout = 14; // in days
			
			if(isset($options['username_field'])) {
				$username_field = $options['username_field'];
			} elseif(globals("simplelogin", "adminusernamefield") === true) {
				$username_field = globals("simplelogin", "adminusernamefield");
			}
			if(isset($options['password_field'])) {
				$password_field = $options['password_field'];
			} elseif(globals("simplelogin", "adminpasswordfield") === true) {
				$password_field = globals("simplelogin", "adminpasswordfield");
			}
			if(isset($options['timeout'])) $timeout = $options['timeout'];
			
			$validated = false;
			
			// check login, if they are trying to log in
			if(input_post($username_field) !== NULL && input_post($password_field) !== NULL) {
				globals("simplelogin", "loginattempt", true); // not logged in, but trying
				$user_post = input_post($username_field);
				$pass_post = md5(input_post($password_field));
				$redirect_after = settings("simplelogin", "adminhome");
			} else {
				$user_post = cookies($username_field);
				$pass_post = cookies($password_field);
				$redirect_after = false;
			}
			if(isset($validusers[$user_post]) && md5($validusers[$user_post]) == $pass_post) $validated = true;
			
			// check cookies
			if($validated) {
				cookies($username_field, $user_post, $timeout);
				cookies($password_field, $pass_post, $timeout);
				globals("simplelogin", "loggedin", true);
				globals("simplelogin", "user", $user_post);
				/*
				if($redirect_after) {
					redirect(page_url($redirect_after));
				}
				*/
			} else {
				simplelogout(false);
			}
			
			return $validated;
		}
	} // end simplelogin
	
	/**
	 *	Checks is there is a logged in user
	 *	
	 *	Check this function on every page you want SimpleLogin to protect
	 *	
	 *	@return bool
	 */
	if(!function_exists("simpleloggedin")) {
		function simpleloggedin() {
			if(globals("simplelogin", "loggedin") === true) return true;
			return false;
		}
	} // simpleloggedin
	
	/**
	 *	Logs the user out - called by simplelogin()
	 *	
	 *	@return NULL
	 */
	if(!function_exists("simplelogout")) {
		function simplelogout($redirect_on_logout = "/", $options = array()) {
			// Default Options
			$username_field = "username";
			$password_field = "password";
			if(isset($options['username_field'])) $username_field = $options['username_field'];
			if(isset($options['password_field'])) $password_field = $options['password_field'];

			unset_cookie($username_field);
			unset_cookie($password_field);
			globals("simplelogin", "loggedin", false);
			if($redirect_on_logout !== false) redirect(page_url($redirect_on_logout));
			return NULL;
		}
	} // end simplelogout
	
	/**
	 *	Cached alias of simpleloggedin()
	 *	
	 *	@return string
	 */
	if(!function_exists("simple_user")) {
		function simple_user() {
			if(simpleloggedin()) return globals("simplelogin", "user");
			return false;
		}
	}
	
	/**
	 *	Optional - handles page redirection
	 *	
	 *	@return NULL
	 */
	if(!function_exists("simple_page_load")) {
		function simple_page_load() {
			if(is_array(settings("simplelogin", "adminusers"))
				&& settings("simplelogin", "adminroot") !== NULL
				&& settings("simplelogin", "adminlogin") !== NULL
				&& settings("simplelogin", "adminhome") !== NULL
			) {
				$in_admin = false; $in_login_page = false; $login_in_admin = false; $in_404 = false; $login_attempt = false;
				
				$adminroot = trim(settings("simplelogin", "adminroot"), "/");
				$adminlogin = trim(settings("simplelogin", "adminlogin"), "/");
				$adminhome = trim(settings("simplelogin", "adminhome"), "/");
				$login_attempt = globals("simplelogin", "loginattempt");
				
				if(strpos(segments_page(), $adminroot . "/") !== false) $in_admin = true;
				if(segments_page() == $adminlogin) $in_login_page = true;
				if(segments_page() == settings('pages', '404_page')) $in_404 = true;
				if($adminlogin == $adminroot || $adminlogin == $adminhome) $login_in_admin = true;
				if($in_admin && !$in_login_page) $in_restricted = true; // in admin but not in login
				
				globals("simplelogin", "in_admin", $in_admin);
				globals("simplelogin", "in_restricted", $in_restricted);
				globals("simplelogin", "in_login_page", $in_login_page);

				if($in_admin) {
					$logged_in = simplelogin(settings("simplelogin", "adminusers"), "/" . trim(settings("simplelogin", "adminroot"), "/") . "/");
					// Error code 113 means that the login form didn't redirect like it should have.
					if(globals("simplelogin", "login_form_submitted")) die("Login failed. Error code 135.");
					
					if($in_404) {
						// no worries
					} elseif($in_login_page && $logged_in && !$login_in_admin) {
						// logged in and requesting login page redirect to admin home
						redirect(settings("simplelogin", "adminhome"));
					} elseif($in_restricted && !$logged_in && !$in_login_page) {
						// trying to access protected admin pages without being logged in
						redirect(settings("simplelogin", "adminlogin"));
					} else { 
						// admin login page OR logged in already
					}
				} else {
					// not trying to access a restricted page
				}
			} else {
				echo "<pre>SimpleLogin: Username and password settings not valid. Need adminroot, adminlogin, adminhome, and adminuser\n";
				// echo "adminusers:" . settings("simplelogin", "adminusers") . "\n";
				// print_r(settings("simplelogin", "adminusers"));
				echo "adminroot:" . settings("simplelogin", "adminroot") . "\n";
				echo "adminlogin:" . settings("simplelogin", "adminlogin") . "\n";
				echo "adminhome:" . settings("simplelogin", "adminhome") . "\n";
				die();
			}
		}
	} // end simple_page_load
?>