<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Membership Library
 *	
 *	This library is depricated. Please use the "users" library for all future development This documentation is for debugging old code ONLY
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 * @package phpGenesis
 */

// membership_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Add ability to register without requiring activation
		
	/**
	 *	Define LOGGED_IN_VALUE
	 */
	define ("LOGGED_IN_VALUE",  "1");
	/**
	 *	Define LOGGED_OUT_VALUE
	 */
	define ("LOGGED_OUT_VALUE", "0");
	
	load_library('session');	
	load_library('cookie');	
	load_library('db');
	
	/**
	 *	Logs the user in using private function _do_login()
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_login")) {
		function member_login($username, $password, $rememberme) {
			$username = db_escape_string($username);

			$user_row = db_query_row("SELECT id, password, salt, banned FROM users WHERE username = '{$username}'");
		
			if (is_array($user_row)) {			
				if ($user_row['banned'] == 0) {
					if ($user_row['password'] == md5($password.$user_row['salt'])) {
					
						// create auto login
						if ($rememberme == true) {
							_install_remember_me_key($user_row['id']);
						} else {
							_uninstall_remember_me_key();					
						}			
						
						_do_login($user_row['id'], $username);
						return true;
					}
				}			
			}
			
			// log failed attempt
			db_query("INSERT INTO user_login_attempts (username, ip_address) VALUES ('{$username}', '{$_SERVER['REMOTE_ADDR']}')");
			return false;		
		}
	} // member_login	
	
	/**
	 *	Private function that does the magic of the user_login()
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_do_login")) {
		function _do_login($user_id, $username=NULL) {

			db_query("UPDATE users SET last_login = NOW(), last_ip = '{$_SERVER['REMOTE_ADDR']}', newpass_key=NULL, newpass_time=NULL WHERE id = {$user_id}");
	
			if($username==NULL) {
				$user_row = db_query_row("SELECT username FROM users WHERE id = '{$user_id}'");
				$username = $user_row['username'];
			}

			session_regenerate(true); // Prevents Session Fixation
			unset_session('FORM_KEY');
			session('USER_ID', $user_id);
			session('USER_NAME', $username);
			session('LOGIN_STATUS', LOGGED_IN_VALUE);
			session('LAST_LOGIN', time());
			_member_ping_last_active($user_id); // set last active
		}
	} // _do_login	
	
	/**
	 *	Sets the remember me cookie
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_install_remember_me_key")) {
		function _install_remember_me_key($user_id) {
			_member_purge_user_autologin();
			$remember_key = md5(mt_rand().microtime());
			cookies('remember_key', $remember_key);
			db_query("INSERT INTO user_autologin (key_id, user_id, user_agent, last_ip, last_login) VALUES ('{$remember_key}', '{$user_id}', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."', NOW())");
		}
	} // _install_remember_me_key	
	
	/**
	 *	Unsets the remember me cookie
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_uninstall_remember_me_key")) {
		function _uninstall_remember_me_key() {  
			if (cookie_isset('remember_key') && session_isset('USER_ID')) { 
				$user_id = session('USER_ID');
				$remember_key = db_escape_string(cookies('remember_key'));
				db_query("DELETE FROM user_autologin WHERE user_id='{$user_id}' AND key_id='{$remember_key}'");
				unset_cookie('remember_key');	
			}
		}
	} // _uninstall_remember_me_key	
	
	/**
	 *	Checks if the users last login event was within the settings() defined in the config 
	 *	
	 *	@return bool
	 */
	if(!function_exists("_member_ping_last_active")) {
		function _member_ping_last_active($id) {
			// Only does this if settings("membership", "last_active_ping_frequency") is set to some value.
			if(settings("membership", "last_active_ping_frequency") !== NULL && session("MEMBER_LAST_ACTIVE") < strtotime("" . settings('membership', 'last_active_ping_frequency') . " minutes ago")) {
				// db_update("users", "WHERE id = {$id}", array("last_active" => time()));
				db_query("UPDATE users SET last_active = NOW() WHERE id = {$id}");
				session("MEMBER_LAST_ACTIVE", time());
				return true;
			}
			return NULL;
		}
	} // _member_ping_last_active	
	
	/**
	 *	Checks if the user is trusted
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_trusted")) {
		function member_trusted() { print_pre($_SESSION); 
			return (member_logged_in() && session_isset('LAST_LOGIN') && (time() - intval(session('LAST_LOGIN'))) <= intval(settings('membership', 'trust_timeout')));
		}
	} // member_trusted	
	
	/**
	 *	Checks how many times a user's login attempt has failed
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_require_captcha")) {
		function member_require_captcha($username=NULL) {
			static $value; if (isset($value) && $value == true) { return true; }
			
			$f = intval(settings('membership', 'failed_attempts'));
			
			if (!isset($username)) {
				$row = db_query_row("SELECT COUNT(*) AS attempts FROM user_login_attempts WHERE ip_address='{$_SERVER['REMOTE_ADDR']}' AND time > ADDDATE(NOW(), INTERVAL -10 MINUTE)");
				$value = $row['attempts'] > $f;
			} else {
				$username = db_escape_string($username);
				$row = db_query_row("SELECT COUNT(*) AS attempts FROM user_login_attempts WHERE (username='{$username}' || ip_address='{$_SERVER['REMOTE_ADDR']}') AND time > ADDDATE(NOW(), INTERVAL -10 MINUTE)");
				$value = $row['attempts'] > $f;
			}
			
			return $value;
		}
	} // member_require_captcha	
	
	/**
	 *	Returns the user id stored in the session
	 *	
	 *	@return int
	 */
	if(!function_exists("member_user_id")) {
		function member_user_id() {
			return (int)session('USER_ID');
		}
	} // member_user_id	
	
	/**
	 *	Returns user name stored in the session
	 *	
	 *	@return string
	 */
	if(!function_exists("member_user_name")) {
		function member_user_name() {
			return session('USER_NAME');
		}
	} // member_user_name	
	
	/**
	 *	Returns true/false if user registration is open
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_allow_register")) {
		function member_allow_register() {		
			return settings('membership', 'allow_register') === true;
		}
	} // member_allow_register	
	
	/**
	 *	Checks if the user is logged in
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_logged_in")) {
		function member_logged_in() {	
			if (session_isset('LOGIN_STATUS') && session('LOGIN_STATUS') == LOGGED_IN_VALUE) {
				// last active ping
				_member_ping_last_active(member_user_id());
				return true;
			} else {
				if (cookie_isset('remember_key')) {
					$remember_key = cookies('remember_key');
					$autologin_row = db_query_row("SELECT user_id, user_agent, last_ip FROM user_autologin WHERE key_id = '{$remember_key}'");
					if (is_array($autologin_row)) {			
						if ($autologin_row['user_agent'] == $_SERVER['HTTP_USER_AGENT']
							&& $autologin_row['last_ip'] == $_SERVER['REMOTE_ADDR']) {
							
							_do_login($autologin_row['user_id']);
							_uninstall_remember_me_key();
							_install_remember_me_key($autologin_row['user_id']);
							
							return true; 
						}
					}
				}
			}	
			return false;
		}
	} // member_logged_in	
	
	/**
	 *	Unsets user's remember me key and session
	 *	
	 *	@return NULL
	 */
	if(!function_exists("member_logout")) {
		function member_logout() {		
			_uninstall_remember_me_key();
			session('LOGIN_STATUS', LOGGED_OUT_VALUE);				
		}
	} // member_logout	
	
	/**
	 *	Sets a key that will allow users to reset their password if they can't login
	 *	
	 *	@return string
	 */
	if(!function_exists("member_get_password_reset_key")) {
		function member_get_password_reset_key($userid) {
			$newpass_key = md5(mt_rand().microtime());

			db_query("UPDATE users SET newpass_key='{$newpass_key}', newpass_time=NOW() WHERE username='{$userid}' OR email='{$userid}'");
			
			return $newpass_key;
		}
	} // member_get_password_reset_key	
	
	/**
	 *	If you set the $user_id field it will use the user id instead of the newpass_key. Use the newpass_key if they're requesting their own new password, as
	 *	the newpass_key should be emailed to them as part of the URL they use to reset their password.
	 *
	 *	@return bool
	 */
	if(!function_exists("member_reset_password")) {
		function member_reset_password($newpass_key, $password, $user_id = NULL) {	
			member_purge_password_reset();
			
			$salt = md5(mt_rand() . microtime());
			$new_password = md5($password . $salt);		
			if($user_id === NULL) {
				$newpass_key = db_escape_string($newpass_key);
				$where = "WHERE newpass_key='{$newpass_key}'";
			} else {
				$where = "WHERE users.id = {$user_id} ";
			}
			if(db_query("UPDATE users SET password='{$new_password}', salt='{$salt}', newpass_key=NULL, newpass_time=NULL {$where}")) {
				return true;
			} else {
				return false;
			}
		}
	} // member_reset_password	
	
	/**
	 *	Unsets the user reset password key
	 *	
	 *	@return NULL
	 */
	if(!function_exists("member_purge_password_reset")) {
		function member_purge_password_reset() {
			db_query("UPDATE users SET newpass_key=NULL, newpass_time=NULL WHERE ADDDATE(newpass_time, INTERVAL 1 DAY) < NOW()");	
		}
	} // member_purge_password_reset	
	
	/**
	 *	Registers the user and returns either a user_id or user_temp_id
	 *	
	 *	@return int
	 */
	if(!function_exists("member_register")) {
		function member_register($username, $password, $email, $require_activation = true) {
			$username = db_escape_string($username);
			$email = db_escape_string($email);

			$activation_key = md5(mt_rand().microtime());
			$salt = md5(mt_rand().microtime());
			
			$user_info = array(	'username'=>$username,
								'password' => md5($password.$salt),
								'salt' => $salt,
								'email' => $email,
								'last_ip' => $_SERVER['REMOTE_ADDR']);
			
			if($require_activation) {
				$user_info['activation_key'] = $activation_key;
				$user_temp_id = db_insert("user_temp", $user_info);
				
				// Send Email
				$email_from = settings('membership','register_email_from');
				$email_subject = settings('membership','register_email_subject');
				$email_body = str_replace('{%activation_key%}', $activation_key, settings('membership','register_email_body'));
				
				load_library('email');
				email($email, $email_subject, $email_body, $email_from);
			} else {
				$user_temp_id = db_insert("users", $user_info);
			}
			
			return $user_temp_id;	
		}
	} // _do_login	
	
	/**
	 *	Activates a user based on the given activation_key and password. Username is not required.
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_activate")) {
		function member_activate($activation_key, $password) {
			_member_purge_user_temp();
			
			$activation_key = db_escape_string($activation_key);

			// Remove temp user record
			$user_info = db_query_row("SELECT id, username, password, salt, email FROM user_temp WHERE activation_key = '{$activation_key}' AND password = MD5(concat('{$password}',salt))");
			if ($user_info !== false) {
				$user_temp_id = $user_info['id'];
				db_delete('user_temp', 'id', $user_temp_id);		
				unset($user_info['id']);
			} else { return false; }
			
			// Add new user record
			$user_info['last_ip'] = $_SERVER['REMOTE_ADDR'];
			$user_info['created'] = date('Y-m-d H:i:s');		
			$user_id = db_insert("users", $user_info);
			
			return true;
		}
	} // member_activate	
	
	/**
	 *	Private function that deletes all temporary users that haven't been activated.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_member_purge_user_temp")) {
		function _member_purge_user_temp() {
			db_query("DELETE FROM user_temp WHERE ADDDATE(created, INTERVAL 1 DAY) < NOW()");
		}
	} // _member_purge_user_temp	
	
	/**
	 *	Removes all saved autologins
	 *	
	 *	@return NULL
	 */
	if(!function_exists("_member_purge_user_autologin")) {
		function _member_purge_user_autologin() {
			$timeout = settings('membership', 'remember_me_timeout');
			db_query("DELETE FROM user_autologin WHERE ADDDATE(last_login, INTERVAL {$timeout} DAY) < NOW()");
		}
	} // _member_purge_user_autologin	
	
	/**
	 *	Checks to see if the username exists and returns true if it does not exist
	 *	
	 *	@return bool
	 */
	if(!function_exists("member_check_username")) {
		function member_check_username($username) {
			return (db_query_row("SELECT id FROM users WHERE username = '{$username}'") === false) && (db_query_row("SELECT id FROM user_temp WHERE username = '{$username}'") === false);
		}
	} // member_check_username	

?>