<?php
/**
 *	Users Library
 *	
 *	Currently used for everything related to users. Will be superseded by user_library (NOTICE: not plural) when we create an ActiveRecord version.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	@todo Rebuild this library as user_library using ActiveRecord
 * @package phpGenesis
 */
	// table name definitions
	
	if(!defined("USERS_TABLE")) define("USERS_TABLE", "users");
	if(!defined("USER_TEMP_TABLE")) define("USER_TEMP_TABLE", "user_temp");
	if(!defined("USER_AUTOLOGIN_TABLE")) define("USER_AUTOLOGIN_TABLE", "user_autologin");
	if(!defined("USER_LOGIN_ATTEMPTS_TABLE")) define("USER_LOGIN_ATTEMPTS_TABLE", "user_login_attempts");
	if(!defined("USER_PROFILES_TABLE")) define("USER_PROFILES_TABLE", "user_profiles");
	
	// Login constants
	
	if(!defined("LOGGED_IN_VALUE")) define ("LOGGED_IN_VALUE",  "1");
	if(!defined("LOGGED_OUT_VALUE")) define ("LOGGED_OUT_VALUE", "0");
	if(!defined("ACTION_KEY_PARAM")) define ("ACTION_KEY_PARAM", "kX");
	
	// Prerequisite libraries
	
	load_libraries(array('session','cookie','db'));
	
	// Methods

	/**
	 *	Returns the currently logged in user info if no field is given.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_current")) {
		function user_current($field = false) { 
			if(!isset($GLOBALS['users']['CURRENT_USER'])) $GLOBALS['users']['CURRENT_USER'] = session('CURRENT_USER');
			return ($field) ? $GLOBALS['users']['CURRENT_USER'][$field] : $GLOBALS['users']['CURRENT_USER'];
		}
	}


	/**
	 *	Returns the User ID of the currently logged in user
	 *	
	 *	@return int
	 */
	if(!function_exists("user_id")) {
		function user_id() { 
			return (int)user_current("id");
		}
	}
	
	/**
	 *	Returns the email address of the currently logged in user OR a specified user id
	 *	
	 *	@return string
	 */
	if(!function_exists("user_email")) {
		function user_email($user_id = NULL) {
			if($user_id === NULL) $user_id = user_id();
			if (!global_isset("users", "user_email_" . $user_id)) {
				$user_row = db_query_row("SELECT email FROM " . USERS_TABLE . " WHERE id = " . $user_id);
				globals("users", "user_email_" . $user_id, $user_row['email']);
			}
			return globals("users", "user_email_" . $user_id);
		}
	}
	
	
	/**
	 *	Returns the displayname/screenname of the currently logged in user
	 *	
	 *	@return string
	 */
	if(!function_exists("user_display_name")) {
		function user_display_name($user_id = NULL) {
			if($user_id === NULL) return user_current('display_name');
			$user = user_get_by_id($user_id);
			return $user['display_name'];
		}
	}
	
	/**
	 *	Checks whether registrations are allowed or not. Set in config.php.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_allow_registration")) {
		function user_allow_registration() {
			return (bool)(settings('users', 'allow_registration') === true);
		}
	}
	
	/**
	 *	Returns whether current (or specified) user is an admin or not.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_is_admin")) {
		function user_is_admin($user_id = NULL) {
			if($user_id === NULL) return user_current('is_admin');
			if((int)$user_id == 0) return false;
			$user = user_get_by_id($user_id);
			return $user['is_admin'];
		}
	}
	
	/// Public Methods ////
		
	/**
	 *	Attempt to login user, logs user and ip if failed. 
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_login")) {
		function user_login($moniker, $password = NULL, $remember_me = false) {
			if(is_array($moniker) && $password === NULL) {
				$user_row = $moniker;
				$moniker = user_moniker($user_row);
			} else {
				$user_row = user_check_password($moniker, $password);
			}
			
			if(is_array($user_row)) {		
				// create auto login
				if($remember_me) {
					_user_add_remember_me_key($user_row['id']);
				} else {
					_user_remove_remember_me_key();
				}
				
				_user_login($user_row['id'], $user_row['display_name'], $user_row['is_admin']);
				user_trust();
				return true;
			}
			
			// log failed attempt
			_user_log_failed_attempt($moniker);
			return false;
	
		}
	}
	
	/**
	 *	Checks if site settings are to use email or username as user moniker and then returns the moniker
	 *	
	 *	@return string
	 */
	if(!function_exists("user_moniker")) {
		function user_moniker($user = NULL) {
			if($user === NULL) return settings('users', 'login_type'); // return the type
			switch(settings('users', 'login_type')) {
				case 'username':
					return $user['username'];
				case 'email':
					return $user['email'];
			}
			return FALSE;
		}
	}
	
	/**
	 *	Return user information if username matches password.
	 *	
	 *	@retur array
	 */
	if(!function_exists("user_check_password")) {
		function user_check_password($moniker, $password) {
			$user_row = NULL;
			$moniker_clean = db_escape_string($moniker);
			
			switch(settings('users', 'login_type')) {
				case 'username':
					$user_row = db_query_row("SELECT id, password, salt, banned, display_name, is_admin FROM " . USERS_TABLE . " WHERE username = '{$moniker_clean}'");
					break;
				case 'email':
					$user_row = db_query_row("SELECT id, password, salt, banned, display_name, is_admin FROM " . USERS_TABLE . " WHERE email = '{$moniker_clean}'");
					break;
				default :
					die("Setting 'login_type' not set in config.php for users_library -- user_check_password()");
			}
			
			if(is_array($user_row)) {
				if($user_row['banned'] == 0) {
					if($user_row['password'] == _user_salt_password($password, $user_row['salt'])) {
						return $user_row;
					}
				}
			}
	
			return false;
		}
	}
	
	/**
	 *	Log out user, unset Remember Me
	 *	
	 *	@return null
	 */
	if(!function_exists("user_logout")) {
		function user_logout() {
			_user_remove_remember_me_key();
			session_end();
			session('LOGIN_STATUS', LOGGED_OUT_VALUE);
		}
	}
	
	/**
	 *	Return true if user is logged in, or log in the user from a cookie and return true,
	 *	or return false if can't log in.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_logged_in")) {
		function user_logged_in() {	
			if(session_isset('LOGIN_STATUS') && session('LOGIN_STATUS') == LOGGED_IN_VALUE) {
				// last active ping
				_user_last_active(user_id());
				return true; 
			} else {
				load_library("cookie");
				if(cookie_isset('remember_key')) {
					$remember_key = cookies('remember_key');
					$remember_key_clean = db_escape_string($remember_key);
					$autologin_row = db_query_row("SELECT ual.user_id, ual.user_agent FROM " . USER_AUTOLOGIN_TABLE . " ual INNER JOIN " . USERS_TABLE . " u ON u.id = ual.user_id WHERE ual.key_id = '{$remember_key_clean}'");
					if(is_array($autologin_row)) {
						if ($autologin_row['user_agent'] == $_SERVER['HTTP_USER_AGENT']) {
							$user_row = user_get_by_id($autologin_row['user_id']);
							_user_login($autologin_row['user_id'], $user_row['display_name'], $user_row['is_admin']);
							return true; 					
						} else {
							_user_remove_remember_me_key();
						}
					}
				}
			}
			return false;
		}
	}
	
	
	/**
	 *	Return true if user is still within the trust period. The trust period is set in config.php settings("users", "trust_timeout") and is set
	 *	in seconds.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_trusted")) {
		function user_trusted() {
			return (user_logged_in() && session_isset('LAST_TRUST') && (time() - (int)(session('LAST_TRUST')) <= (int)settings('users', 'trust_timeout')));
		}
	}	
	
	/**
	 *	Make user a "trusted" user
	 *	
	 *	@return null
	 */
	if(!function_exists("user_trust")) {
		function user_trust() {
			session('LAST_TRUST', time());
		}
	}
		
	/**
	 *	Returns true if failed login attempts is greater than login failure limit, which is set in config.php
	 *	settings("users", "login_failure_limit"). You can also pass in the login failure limit to this function
	 *	directly.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_requires_captcha")) {
		function user_requires_captcha($login_failure_limit = NULL, $moniker = NULL) {
			static $value; if(isset($value) && $value == true) { return true; }
			
			if($login_failure_limit == NULL) {
				$login_failure_limit = settings('users', 'login_failure_limit');
			}
			
			if (!isset($moniker)) {
				$row = db_query_row("SELECT COUNT(*) AS attempts FROM " . USER_LOGIN_ATTEMPTS_TABLE . " WHERE ip=INET_ATON('{$_SERVER['REMOTE_ADDR']}') AND time > ADDDATE(NOW(), INTERVAL -10 MINUTE)");
				$value = $row['attempts'] > $login_failure_limit;
			} else {
				$moniker_clean = db_escape_string($moniker);
				$row = db_query_row("SELECT COUNT(*) AS attempts FROM " . USER_LOGIN_ATTEMPTS_TABLE . " WHERE (moniker='{$moniker_clean}' || ip=INET_ATON('{$_SERVER['REMOTE_ADDR']}')) AND time > ADDDATE(NOW(), INTERVAL -10 MINUTE)");
				$value = $row['attempts'] > $login_failure_limit;
			}
	
			return $value;
		}
	}	
	
	/**
	 *	Returns the username if username is not already in use, false if it's already taken.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_check_username")) {
		function user_check_username($username) {
			_user_purge_user_temp();
			$username_clean = db_escape_string($username);
			$not_found = (db_query_row("SELECT id FROM " . USERS_TABLE . " WHERE username = '{$username}'") === false);
			if($not_found) return $username;
			return FALSE;
		}
	}	
	
	/**
	 *	Returns submitted email address if that address is not already in use. Otherwise, returns false.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_check_email")) {
		function user_check_email($email, $user_id = 0) {
			_user_purge_user_temp();
			$email_clean = db_escape_string($email);
			if (db_query_row("SELECT id FROM " . USERS_TABLE . " WHERE email = '{$email_clean}' AND id != {$user_id}") === false) {
				return $email_clean;
			} else {
				if(settings('form', 'errormsg_user_check_email') === NULL) settings('form', 'errormsg_user_check_email', 'Email address in use or pending activation.');
				return false;
			}
		}
	}	
	
	/**
	 *	Inserts new user into database and returns activation key or user_temp_id, depending on settings.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_register")) {
		function user_register($username, $email, $password, $display_name, $require_activation = true) {
			$salt = _user_nonce();
			$salted = _user_salt_password($password, $salt);
			
			$user_info = array(
				'username' => $username,
				'email' => $email,
				'password' => $salted,
				'salt' => $salt,
				'display_name' => $display_name,
				'last_ip' => $_SERVER['REMOTE_ADDR']
			);
			
			if($require_activation) {
				$activation_key = _user_nonce();
				$user_info['activation_key'] = $activation_key;
				$user_temp_id = db_insert(USER_TEMP_TABLE, $user_info);
				return $activation_key;
			} else {
				$user_temp_id = db_insert(USERS_TABLE, $user_info);
				return $user_temp_id;
			}
		}
	}	
	
	/**
	 *	Sends email to user with activation link and welcome message. Use these template tags:
	 *
	 *	%activation_key% (replaced with $activation_key)
	 *	
	 *	%fieldname% (uses $user_info['fieldname'])
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_send_activation")) {
		function user_send_activation($activation_key, $email_address, $user_info = array()) {
			$template = settings("users", "activation_email_template");
			$template['message'] = str_replace("%activation_key%", $activation_key, $template['message']);
			foreach($user_info as $k => $v) {
				$template['message'] = str_replace("%{$k}%", $v, $template['message']);
				$template['subject'] = str_replace("%{$k}%", $v, $template['subject']);
			}
			
			load_library("email");
			
			return email_send($email_address, $template['subject'], $template['message'], $template['from']);
		}
	}
	
	/**
	 *	Removes temp user, activates permanent user, returns user info
	 *	
	 *	@return array
	 */
	if(!function_exists("user_activate")) {
		function user_activate($activation_key, $password = NULL) {
			_user_purge_user_temp();
			
			$activation_key_clean = db_escape_string($activation_key);
			
			// Remove temp user record
			$user_info = false;
			$user_info = db_query_row("SELECT * FROM " . USER_TEMP_TABLE . " WHERE activation_key = '{$activation_key_clean}'");
			
			$salted = _user_salt_password($password, $user_info['salt']);
			
			if($user_info !== false) {
				if($password === NULL || $user_info['password'] === $salted) {
					$user_temp_id = $user_info['id'];
					
					db_delete(USER_TEMP_TABLE, 'id', $user_temp_id);
					unset($user_info['id']);
				} else { 
					return false; 
				}
			} else {
				return false;
			}
			
			if($user_info = user_create($user_info['username'], $user_info['email'], $user_info['display_name'], $user_info['password'], $user_info['salt'])) {
				return user_get_by_id($user_info);
			} else {
				return FALSE;
			}
		}
	}	
	
	/**
	 *	Does everything user_activate() does but it also logs the user in.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_activate_and_log_in")) {
		function user_activate_and_log_in($activation_key, $password = NULL) {
			if($user_info = user_activate($activation_key, $password)) {
				user_login($user_info); // log in without password
				return $user_info;
			} else {
				return FALSE;
			}
		}
	}	
	
	
	/**
	 *	Returns a list of all active users.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_rows")) {
		function user_rows($where = "") {
			return db_query_rows("
				SELECT u.*,
					upr.*,
					UNIX_TIMESTAMP(u.last_login) AS last_login,
					upr.id AS profile_id,
					u.id AS id
				FROM " . USERS_TABLE . " u
					LEFT JOIN " . USER_PROFILES_TABLE . " upr ON upr.user_id = u.id
				{$where}
				ORDER BY u.display_name
			");
		}
	}	
	/**
	 *	Returns a list of all inactive users.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_temp_rows")) {
		function user_temp_rows() {
			return db_query_rows("
				SELECT id, display_name, email, UNIX_TIMESTAMP(created) AS created, activation_key FROM " . USER_TEMP_TABLE . "
				ORDER BY display_name
			");
		}
	}	
	
	/**
	 *	Returns user info where id = $id
	 *	
	 *	@return array
	 */
	if(!function_exists("user_get_by_id")) {
		function user_get_by_id($id, $force_update = false) {
			$cache = "user_{$id}";
			if($force_update || global_isset("user", $cache) === FALSE) {
				globals("user", $cache, db_query_row("
					SELECT u.*,
						upr.*,
						upr.id AS user_profile_id,
						u.id AS id
					FROM " . USERS_TABLE . " u
						LEFT JOIN " . USER_PROFILES_TABLE . " upr ON upr.id = u.id
					WHERE u.id = {$id}
				"));
			}
			return globals("user", $cache);
		}
	}
	
	/**
	 *  Returns the id of a user with a matching username, email, or display_name
	 *  (This function does not protect you from duplicate entries. Be sure your registration 
	 *  prevents duplicates before using.)
	 *  
	 *	@return int
	 */
	if(!function_exists("user_get_id_by_other")) {
		function user_get_id_by_other($other) {
			$query = db_query_row("
				SELECT u.id
				FROM " . USERS_TABLE . " u
				WHERE u.username = '" . $other . "' OR u.email = '" . $other . "' OR u.display_name = '" . $other . "'
				LIMIT 0, 1
			");
			if(is_array($query)) {
				return $query['id'];
			} else {
				return FALSE;
			}
		}
	}
	
	/**
	 *	Creates a user in the database. Leave $salt blank unless you know what you're doing.
	 *	
	 *	@return int
	 */
	if(!function_exists("user_create")) {
		function user_create($username, $email, $display_name, $password, $salt = NULL) {
			if(!isset($salt)) {
				$salt = _user_nonce();
				$password = _user_salt_password($password, $salt);
			}
			
			$user_info = array();
			
			$user_info['username'] = $username;
			$user_info['email'] = $email;
			$user_info['display_name'] = $display_name;
			$user_info['password'] = $password;
			$user_info['salt'] = $salt;
			$user_info['created'] = date(SQL_DATE_FORMAT);
			$user_id = db_insert(USERS_TABLE, $user_info);
			
			return $user_id;
		}
	}	
	
	/**
	 *	Updates a user.
	 *	
	 *	@return int
	 */
	if(!function_exists("user_update_by_id")) {
		function user_update_by_id($id, $updated_row) { 
			return db_update(USERS_TABLE, (int)$id, $updated_row);
		}
	}	
	
	/**
	 *	Deletes a user.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("user_delete_by_id")) {
		function user_delete_by_id($id) {
			db_query("DELETE FROM " . USERS_TABLE . " WHERE id = " . (int)$id);
		}
	}	
	/**
	 *	Deletes a temp user. Returns true on success.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_delete_temp_by_id")) {
		function user_delete_temp_by_id($id) {
			if(db_query("DELETE FROM " . USER_TEMP_TABLE . " WHERE id = " . (int)$id)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}	
	
	/**
	 *	Ban a user.
	 *	
	 *	@return NULL
	 */
	if(!function_exists("user_ban_by_id")) {
		function user_ban_by_id($id) {
			db_query("UPDATE " . USERS_TABLE . " SET banned=1 WHERE id = " . (int)$id);
		}
	}	
	
	/**
	 *	Set a user's password. Uses a salt and nonce and all that noncense.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_set_password")) {
		function user_set_password($user_id, $password) {
			$salt = _user_nonce();
			$salted = _user_salt_password($password, $salt);
			return db_query("UPDATE " . USERS_TABLE . " SET password='{$salted}', salt='{$salt}', newpass_key=NULL, newpass_time=NULL WHERE id = {$user_id}");
		}
	}	
	
	/**
	 *	Resets a user's password based on the key received in a "forgot password" email.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_reset_password")) {
		function user_reset_password($newpass_key, $password) {
			$newpass_key_clean = db_escape_string($newpass_key);
			$user_row = db_query_row("SELECT * FROM " . USERS_TABLE . " WHERE newpass_time > DATE_ADD(NOW(), INTERVAL -" . settings('users', 'password_reset_timeout') . " minute) AND newpass_key = '{$newpass_key_clean}'");
			if (is_array($user_row)) {
				if(user_set_password($user_row['id'], $password)) {
					return $user_row;
				} else {
					return FALSE;
				}
			} else {
				return false;
			}
		}
	}
	
	/**
	 *	Gets a password reset key if a user forgot their password. You can use either the
	 *	username or email, regardless of which you're using to log in.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_get_password_reset_key")) {
		function user_get_password_reset_key($moniker, $use_email = FALSE) {
			$newpass_key = _user_nonce();
			
			$moniker_clean = db_escape_string($moniker);
			
			$login_type = settings('users', 'login_type');
			if($use_email === TRUE) $login_type = "email";
			
			switch ($login_type) {
				case 'username':
					db_query("UPDATE " . USERS_TABLE . " SET newpass_key='{$newpass_key}', newpass_time=NOW() WHERE username='{$moniker_clean}'");
					break;
				case 'email':
					db_query("UPDATE " . USERS_TABLE . " SET newpass_key='{$newpass_key}', newpass_time=NOW() WHERE email='{$moniker_clean}'");
					break;
				default :
					die("login_type");
			}
			
			if(db_affected_rows() > 0) {
				return $newpass_key;
			} else {
				return null;
			}
		}
	}
	
	/**
	 *	Resets a user's password and sends an email with the link.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_send_reset_password")) {
		function user_send_reset_password($email) {
			$reset_key = user_get_password_reset_key($email, TRUE);
			
			$template = settings("users", "reset_password_email_template");
			$template['message'] = str_replace("%reset_key%", $reset_key, $template['message']);
			
			load_library("email");
			
			return email_send($email, $template['subject'], $template['message'], $template['from']);

		}
	}
			
	
	/**
	 *	I don't actually know what this function does, but it looks really important.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_check_action_key")) {
		function user_check_action_key($action, $string) {
			$sent_hash = isset($_GET[ACTION_KEY_PARAM]) ? $_GET[ACTION_KEY_PARAM] : '';
			$secure_hash = core_hash(user_id() . $action . $string);
			return $sent_hash == $secure_hash;
		}
	}
	
	/**
	 *	Gets something really cool called an action_key.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_get_action_key")) {
		function user_get_action_key($action, $string) {
			return ACTION_KEY_PARAM.'='.user_action_key($action, $string);
		}
	}	
	
	/**
	 *	Gets a different version of the action_key.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_action_key")) {
		function user_action_key($action, $string) {
			return core_hash(user_id().$action.$string);
		}
	}	
	
	/**
	 *	Return the number of users, not including banned users.
	 *	
	 *	@return int
	 */
	if(!function_exists("user_count")) {
		function user_count() {
			$count_row = db_query_row("SELECT count(*) AS user_count FROM " . USERS_TABLE . " WHERE banned = 0");
			return (int)$count_row['user_count'];
		}
	}
	
	/**
	 *	Private function that logs a failed login attempt.
	 *	
	 *	@return null
	 */
	if(!function_exists("_user_log_failed_attempt")) {
		function _user_log_failed_attempt($moniker) {
			_user_purge_user_login_attempts();
			db_query("INSERT INTO " . USER_LOGIN_ATTEMPTS_TABLE . " (moniker, ip) VALUES ('{$moniker}', INET_ATON('{$_SERVER['REMOTE_ADDR']}'))");
		}
	}

	/**
	 *	Private function to log in a user and set the session.
	 *	
	 *	@return null
	 */
	if(!function_exists("_user_login")) {
		function _user_login($user_id, $display_name, $is_admin) {
			db_query("UPDATE " . USERS_TABLE . " SET last_login = NOW(), last_ip = INET_ATON('{$_SERVER['REMOTE_ADDR']}'), newpass_key=NULL, newpass_time=NULL WHERE id = {$user_id}");
			
			session_regenerate(true); // Prevents Session Fixation
			unset_session('FORM_KEY');
			$user = user_get_by_id($user_id);
			
			session('USER_ID', (int)$user_id);
			session('CURRENT_USER', $user);
			session('USER_IS_ADMIN', $user['is_admin']);
			session("USER_ROW", $user);
			session('DISPLAY_NAME', $user['display_name']);
			session('LOGIN_STATUS', LOGGED_IN_VALUE);
			session('LAST_LOGIN', time());
			
			
			_user_last_active($user_id); // set last active
		}
	}	
	
	/**
	 *	Private function that sets when the user was last active.
	 *	
	 *	@return bool
	 */
	if(!function_exists("_user_last_active")) {
		function _user_last_active($user_id) {
			// Only does this if settings("membership", "last_active_ping_frequency") is set to some value.
			$last_active_frequency = settings('users', 'last_active_frequency');
			if($last_active_frequency !== NULL && session("LAST_ACTIVE") < strtotime("{$last_active_frequency} minutes ago")) {
				db_query("UPDATE " . USERS_TABLE . " SET last_active = NOW() WHERE id = {$user_id}");
				session("LAST_ACTIVE", time());
				return true;
			}
			return NULL;
		}
	}	
	
	/**
	 *	Private function that converts an IP address to an integer.
	 *	
	 *	@return string
	 */
	if(!function_exists("_user_ip")) {
		function _user_ip() {
			return sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
		}
	}	
	
	
	/**
	 *	Private function that adds a remember me db entry and cookie.
	 *	
	 *	@return null
	 */
	if(!function_exists("_user_add_remember_me_key")) {
		function _user_add_remember_me_key($user_id) {
			_user_purge_user_autologin();
			$remember_key = _user_nonce();
			$timeout = 30;
			if(setting_isset('users', 'remember_me_timeout')) $timeout = settings('users', 'remember_me_timeout');
			cookies('remember_key', $remember_key, $timeout);
			db_query("INSERT INTO " . USER_AUTOLOGIN_TABLE . " (key_id, user_id, user_agent, last_ip, last_login) VALUES ('{$remember_key}', '{$user_id}', '" . db_escape_string($_SERVER['HTTP_USER_AGENT']) . "', '" . _user_ip() . "', NOW())");
		}
	}	
	
	/**
	 *	Private function to remove a remember me cookie and autologin.
	 *	
	 *	@return null
	 */
	if(!function_exists("_user_remove_remember_me_key")) {
		function _user_remove_remember_me_key() {
			load_library("cookie");
			if(cookie_isset('remember_key')) {
				$remember_key = cookies('remember_key');
				$remember_key_clean = db_escape_string($remember_key);
				db_query("DELETE FROM " . USER_AUTOLOGIN_TABLE . " WHERE key_id='{$remember_key_clean}'");
				unset_cookie('remember_key');
			}
		}
	}	
	
	/**
	 *	Private function to purge old autologins.
	 *	
	 *	@return null
	 */
	if(!function_exists("_user_purge_user_autologin")) {
		function _user_purge_user_autologin() {
			$timeout = 30;
			if(setting_isset("users", "remember_me_timeout")) $timeout = settings('users', 'remember_me_timeout');
			db_query("DELETE FROM " . USER_AUTOLOGIN_TABLE . " WHERE ADDDATE(last_login, INTERVAL {$timeout} DAY) < NOW()");
		}
	}	
	
	/**
	 *	Private function to purge old, unactivated users.
	 *	
	 */
	if(!function_exists("_user_purge_user_temp")) {
		function _user_purge_user_temp() {
			db_query("DELETE FROM " . USER_TEMP_TABLE . " WHERE ADDDATE(created, INTERVAL 1 DAY) < NOW()");
		}
	}	
	
	/**
	 *	Private function to purge old login attempts.
	 *	
	 */
	if(!function_exists("_user_purge_user_login_attempts")) {
		function _user_purge_user_login_attempts() {
			db_query("DELETE FROM " . USER_LOGIN_ATTEMPTS_TABLE . " WHERE ADDDATE(time, INTERVAL 7 DAY) < NOW()");
		}
	}	
	
	/**
	 *	Private function to create some noncense.
	 *	
	 */
	if(!function_exists("_user_nonce")) {
		function _user_nonce() {
			 return substr(core_hash(mt_rand().microtime()), 0, 40);
		}
	}	
	
	/**
	 *	Private function to hash up a password with a salt.
	 *	
	 */
	if(!function_exists("_user_salt_password")) {
		function _user_salt_password($password, $salt) {
			return substr(core_hash($password.$salt), 0, 40);
		}
	}
	
	/***************************************** PROFILES *******************************************/

	/**
	 *	Add a profile to a user.
	 *	
	 *	@return int
	 */
	if(!function_exists("user_profile_add")) {
		function user_profile_add($user_id, $new_row) {
			$new_row['user_id'] = $user_id;
			return db_insert(USER_PROFILES_TABLE, $new_row);
		}
	}
	
	/**	
	 *	Check if user profile is complete
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_profile_complete")) {
		function user_profile_complete($user_id) {
			$profile = user_profile_get($user_id);
			if($profile['profile_complete']) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	
	/**
	 *	Updates a profile by user ID.
	 *	
	 *	@return int
	 */
	if(!function_exists("user_profile_update")) {
		function user_profile_update($user_id, $updated_row) {
			return db_update(USER_PROFILES_TABLE, "WHERE user_id={$user_id}", $updated_row);
		}
	}
	
	/**
	 *	Get a profile by user ID. Creates an empty profile if none exists.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_profile_get")) {
		function user_profile_get($user_id = NULL, $create = true) {
			if($user_id == NULL) $user_id = user_id();
			
			$user_profile_row = db_get_row(USER_PROFILES_TABLE, "user_id", $user_id);
			if($user_id > 0 && $user_profile_row == false && $create == true) {
				user_profile_add($user_id, array());
				$user_profile_row = db_get_row(USER_PROFILES_TABLE, "user_id", $user_id);
			}
			return $user_profile_row;
		}
	}
	/**
	 *	Alias of user_profile_get().
	 *	
	 *	@return array
	 */
	if(!function_exists("user_profile")) {
		function user_profile($user_id = NULL, $create = true) {
			return user_profile_get($user_id, $create);
		}
	}
	
	
	/**
	 *	Get a profile by criteria other than user_id.
	 *	
	 *	@returns array
	 */
	if(!function_exists("user_profile_get_where")) {
		function user_profile_get_where($where) {
			$user_profile_row = db_query_row("SELECT * FROM " . USER_PROFILES_TABLE . " {$where} ");
			return $user_profile_row;
		}
	}
	
	/**
	 *	Older, deprecated version of user_test() kept for backwards-compatibility.
	 *	
	 *	@return array(?)
	 */
	if(!function_exists("user_check")) {
		function user_check($username, $email, $password, $conf_pass) {
			list($result, $message) = user_test($username, $email, $password, $conf_pass);
			if($result) return FALSE;
			return $message;
		}
	}
	
	/**
	 *	Tests user data to make sure it's valid without logging in or registering.
	 *
	 *	Returns an array with (bool)$result, (string)$message.
	 *
	 *	Usage: list($result, $message) = user_test($username, $email, $password, $conf_pass);
	 *	
	 *	@return array
	 */
	if(!function_exists("user_test")) {
		function user_test($username, $email, $password, $conf_pass) {
			$message = "";
			if($username) {
				if(!user_check_username($username)) {
					$message .= "Username already in use<br />";
				}
			} else {
				$message .= "Please enter a username<br />";
			}
			if($email) {
				if(valid_email($email)) {
					if(!user_check_email($email)) {
						$message .= "Email address already in use<br />";
					}
				} else {
					$message .= "Please use a valid email address<br />";
				}
			} else {
				$message .= "Please enter an email<br />";
			}
			if($password) {
				if($password != $conf_pass) {
					$message .= "Passwords do not match<br />";
				}
			} else {
				$message .= "Please enter a password<br />";
			}
			if($message == "") return array(TRUE, "All tests passed.");
			return array(FALSE, $message);
		}
	}
	
	/**
	 *	Returns the currently logged in user's level.
	 *	
	 *	@return string
	 */
	if(!function_exists("user_level_current")) {
		function user_level_current() {
			$user = user_current();
			if(isset($user['level'])) return $user['level'];
			return FALSE;
		}
	}

	/**
	 *	Returns if the currently logged in user level is 
	 *	greater or equal to the specified level.
	 *	
	 *	@return bool
	 */
	if(!function_exists("user_level")) {
		function user_level($level = "") {
			$levels = user_levels();
			$current_level = user_level_current();
			
			$current_level_number = 99;
			$required_level_number = 0;
			
			foreach($levels as $rank => $name) {
				if(strtolower($name) == strtolower($level)) $required_level_number = $rank;
				if(strtolower($name) == strtolower($current_level)) $current_level_number = $rank;
			}
			
			if($current_level_number <= $required_level_number) return TRUE;
			
			return FALSE;
		}
	}
	
	/**
	 *	Returns an array of set levels.
	 *	
	 *	@return array
	 */
	if(!function_exists("user_levels")) {
		function user_levels() {
			$levels = settings("users", "levels");
			if(!is_array($levels)) die("User levels not set! Check config.");
			return $levels;
		}
	}
	

?>