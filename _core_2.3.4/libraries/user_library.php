<?php
	/**
	 *	User Library
	 *	
	 *	Supercedes the old users_library.php.
	 *	
	 *	Not meant to be used in this form. Create a User model that extends UserLibrary and then go from there.
	 *	
	 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
	 *	
	 *	phpGenesis by Jamon Holmgren and Tim Santeford
	 *	
	 *	Maintained by ClearSight Studio
	 *	
	 *	@todo 
	 * 	@package phpGenesis
	**/
	
	load_library("session"); // Need sessions to work.
	load_library("cookie"); // Also need cookies.
	/**
	 * Sets up the User database and the admin user.
	 * 
	 * @return NULL
	 */
	if(!function_exists("user_library_setup")) {
		function user_library_setup($username, $email, $password) {
			$dsn = 'mysql:dbname=' . settings("db", "database") . ';host=' . settings("db", "host");
			$db_user = settings("db", "username");
			$db_password = settings("db", "password");
			
			try {
					$dbh = new PDO($dsn, $db_user, $db_password);
			} catch (PDOException $e) {
					echo 'Connection failed: ' . $e->getMessage();
			}
			
			$success = $dbh->query("
				CREATE TABLE IF NOT EXISTS `users` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`username` varchar(255) NOT NULL,
					`email` varchar(255) DEFAULT NULL,
					`password` varchar(255) NOT NULL,
					`password_salt` varchar(255) DEFAULT NULL,
					`name` varchar(255) DEFAULT NULL,
					`level` varchar(1024) DEFAULT NULL,
					`last_login` datetime DEFAULT NULL,
					`last_active` datetime DEFAULT NULL,
					`last_login_failure` datetime DEFAULT NULL,
					`consecutive_login_failures` smallint(5) unsigned NOT NULL DEFAULT '0',
					`newpass_key` varchar(40) DEFAULT NULL,
					`newpass_expiration` datetime DEFAULT NULL,
					`created_at` timestamp NULL DEFAULT NULL,
					`updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`),
					UNIQUE KEY `id_UNIQUE` (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			
			$success2 = $dbh->query("
				CREATE TABLE IF NOT EXISTS `user_sessions` (
					`session_id` varchar(40) COLLATE utf8_bin NOT NULL,
					`user_id` int(11) unsigned NOT NULL DEFAULT '0',
					`user_agent` varchar(255) COLLATE utf8_bin NOT NULL,
					`last_ip` int(10) unsigned NOT NULL,
					`last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`created_at` timestamp NULL DEFAULT NULL,
					`updated_at` timestamp NULL DEFAULT NULL,
					PRIMARY KEY (`session_id`,`user_id`),
					KEY `IX_user_session_user` (`user_id`) USING BTREE,
					CONSTRAINT `FK_user_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
			");
	
			
			if($success && $success2) {
				$success->closeCursor(); // release the memory. PDO is weird.
				$success2->closeCursor();
				
				$admin_user = new UserLibrary(array(
					"username" => $username,
					"password" => $password,
					"email" => $email,
					"level" => array_first(settings("user", "levels")),
					"name" => "Administrative",
				));
				$admin_user->save();
				die("User library setup complete.");
			} else {
				throw new Exception("Couldn't create user library tables!");
			}
		}
	}
	
	/**
	 *	For backwards compatibility and convenience
	**/
	if(!function_exists("user_id")) {
		function user_id() {
			if(User::logged_in() && User::current()) {
				return User::current()->id;
			}
			return NULL;
		}
	}
	
	if(!class_exists("UserLibrary")) {
		class UserLibrary extends phpGenesisModel {
			static $table_name = "users";
			
			// Sorta-singleton for logged in user.
			private static $logged_in, $current_user, $error_message;
			
			// Validations. These can be overridden.
			static $validates_presence_of = array(
				array('username'),
				array('password'),
			);
			
			static $validates_uniqueness_of = array(
				array("username"),
				array("email", "allow_null" => TRUE, "allow_blank" => TRUE),
			);
			
			static $validates_format_of = array(
				array('email', 'with' => "^([a-zA-Z0-9]+([\.+_-][a-zA-Z0-9]+)*)@(([a-zA-Z0-9]+((\.|[-]{1,2})[a-zA-Z0-9]+)*)\.[a-zA-Z]{2,6})$^", 'allow_null' => TRUE),
			);
			// Encrypts any version of the password that is passed in for saving purposes.
			public function set_password($new_password) {
				if(strlen((string)$new_password) > 0) {
					$this->assign_attribute("password", $this->hashed_password($new_password));
				}
			}
			
			// Compares the entered password with the database one
			public function verify_password($password) {
				return ($this->hashed_password($password) == $this->password);
			}
			
			// Returns an encrypted password
			private function hashed_password($password) {
				// load_library("encryption"); // And lets do secure hashing
				if($this->password_salt === NULL) {
					$salt = quid(25);
					$this->password_salt = $salt;
				} else {
					$salt = $this->password_salt;
				}
				return core_hash($password . $salt);
			}
			
			// Attempt to log in. Will fail if too many failures recently or if password is wrong.
			// Returns true or false.
			public static function log_in($username, $password, $remember = FALSE) {
				if(self::logged_in()) {
					return TRUE;
				} else {
					UserSessionLibrary::purge(); // let's remove old sessions first, to be sure.
					
					if($user = self::find_by_username($username)) {
						// Check to see if they have too many login failures within the past few minutes. After 5 tries it will start making you wait a minute between tries.
						if(!$user->last_login_failure || ($user->last_login_failure && ($user->consecutive_login_failures < 5 || $user->last_login_failure->getTimestamp() < (time() - 60)))) {
							if($user->verify_password($password)) {
								self::log_in_user($user); // Good to go!
								
								// Regenerate the session
								// session_regenerate_id();
								
								if($remember) {
									load_library("cookie");
									cookie("remember", session_id());
								}
								
								return TRUE;
							} else {
								$user->record_failed_login_attempt()->save();
								self::$error_message = "Wrong username or password.";
								return FALSE;
							}
						} else {
							// Trying too many passwords too fast. Wait for a minute before trying again.
							self::$error_message = "You've tried too many times -- wait a minute and try again.";
							return FALSE;
						}
					} else {
						// No user by that name
						self::$error_message = "Wrong username or password.";
						return FALSE;
					}
				}
			}
			
			public static function log_in_user($user, $session = NULL) {
				$user->last_login = date(DATE_ATOM);
				$user->activity_ping()->save();
				if(cookie_isset("remember")) cookie("remember", session_id());
				
				// Setting the current login status and user.
				self::$logged_in = TRUE;
				self::$current_user = $user;
				
				$session_id = session_id();
				if(!$session && !$session = UserSessionLibrary::find_by_session_id_and_user_id($session_id, $user->id)) {
					$session = new UserSessionLibrary();
				}
				$session->user_id = $user->id;
				$session->session_id = session_id();
				$session->save();
			}
			
			// Returns the currently logged in user (object) OR NULL if not logged in.
			public static function current() {
				if(self::logged_in() && self::$current_user) return self::$current_user;
				return NULL;
			}
			
			// Return TRUE or FALSE
			public static function logged_in() {
				if(self::$logged_in === NULL) {
					// check if they have a current session first.
					if(session_id()) {
						$session = UserSessionLibrary::find_by_session_id(session_id());
					}
					
					// Or a cookie?
					if(!$session) {
						load_library("cookie");
						if(cookie_isset("remember")) {
							$session = UserSessionLibrary::find_by_session_id(cookie("remember"));
						}
					}
					
					if($session) {
						if($session->valid()) {
							// Log in using the regular or remember me cookie session
							$user = self::find_by_id($session->user_id);
							if($user) {
								self::log_in_user($user, $session); // Hurray!
							} else {
								$session->delete(); // no point in keeping it around.
							}
						} else {
							// Someone else trying to steal a session? Might want to make the user log in again here.
						}
					} else {
						// Sorry, couldn't log in.
					}
				}
				
				if(self::$logged_in === NULL) {
					// Since we couldn't log in with session or cookie, guess we're not really logged in!
					self::$logged_in = FALSE;
				}
				
				return (bool)self::$logged_in;
			}
			
			public static function log_out() {
				unset_cookie("remember");
				$session = UserSessionLibrary::find_by_session_id(session_id());
				if($session) $session->delete();
				self::$logged_in = FALSE;
				self::$current_user = NULL;
				session_regenerate_id();
			}
			
			public static function error() {
				return self::$error_message;
			}
			
			
			/*********************************** Instance methods **************************************/
			
			private function record_failed_login_attempt() {
				$this->last_login_failure = date(DATE_ATOM);
				$this->consecutive_login_failures += 1;
				return $this;
			}
			private function clear_failed_login_attempts() {
				$this->last_login_failure = NULL;
				$this->consecutive_login_failures = 0;
				return $this;
			}
			private function activity_ping() {
				$this->last_active = date(DATE_ATOM);
				$this->clear_failed_login_attempts();
				return $this;
			}
			
			public function send_reset_password() {
				load_library("email");
				if(setting_isset("user", "reset_email_template")) {
					$template = settings("user", "reset_email_template");
				} else {
					$template = array(
						"from" => "\"" . settings("site", "name") . "\" <" . EMAIL . ">",
						"subject" => settings("site", "name") . " Password Reset",
						"message" => "
							<p>Your password is ready to be reset. Visit the link below to set a new password.</p>
							<p><a href='" . BASE_URL . "/users/reset/?reset_key=%reset_key%'>" . BASE_URL . "/users/reset/?reset_key=%reset_key%</a></p>
						",
					);
				}
				
				$reset_key = quid(15);
				$this->newpass_key = $reset_key;
				$this->newpass_expiration = date(DATE_ATOM, strtotime("+60 minutes"));
				$this->save();
				
				$template['message'] = str_replace("%reset_key%", $reset_key, $template['message']);
				return email_send($this->email, $template['subject'], $template['message'], $template['from']);
			}
			
			// Returns TRUE if the current user's level is at or above the $level requested.
			// $level can be a rank (0 through whatever, lower is better) or a specific name.
			public function level($level = 0) {
				$levels = settings("user", "levels");
				$current_level_number = array_search($this->level, $levels);
				
				if(is_int($level)) {
					$required_level_number = $level;
				} else {
					$required_level_number = array_search($level, $levels);
				}
				
				if($current_level_number !== FALSE && $current_level_number <= $required_level_number) return TRUE;
				return FALSE;
			}
			
			// Sees if the user is at the highest level possible.
			public function is_admin() {
				return $this->level(0);
			}
		}
	}
	
	/**
	 *	Handles user sessions. You do not usually need to extend this model or use it at all.
	**/
	if(!class_exists("UserSessionLibrary")) {
		class UserSessionLibrary extends phpGenesisModel {
			static $table_name = "user_sessions";
			
			// Remove old sessions
			public static function purge() {
				self::query("
					DELETE FROM user_sessions
					WHERE last_login < '" . date(DATE_ATOM, strtotime("-30 days")) . "'
				");
			}
			
			// Run tests to verify the session.
			public function valid() {
				// Test User Agent string (hashed for additional security)
				if($this->user_agent != core_hash($_SERVER['HTTP_USER_AGENT'])) return FALSE;
				
				// Test how long ago that session was last accessed
				// if($this->last_login && $this->last_login->getTimestamp() < strtotime("-24 hours")) return FALSE;
				
				// Guess everything is okay. More or less. I hate session fixation. It's hard to protect against.
				return TRUE;
			}
			
			function before_save() {
				$this->user_agent = core_hash($_SERVER['HTTP_USER_AGENT']);
				$this->last_login = date(DATE_ATOM);
			}
		}
	}
?>