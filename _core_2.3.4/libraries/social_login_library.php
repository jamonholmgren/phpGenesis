<? if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
	/**
	 *	Social Login Library
	 *	
	 *	Everything you will ever need to login a user via Janrain Engage
	 *	
	 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
	 *	
	 *	phpGenesis by Jamon Holmgren and Tim Santeford
	 *	
	 *	Maintained by ClearSight Studio
	 *
	 * @package phpGenesis
	 */
	
	load_library("notice");
	
	if(!setting_isset("social_login", "api_key")) die("settings('social_login', 'api_key') must be set in the config");
	
	// Process response from Engage server - Auto called
	if(!function_exists("social_login_process")) {
		function social_login_process() {
			if(input_get("state")) {
				if(input_get("state") == session("social_login_state")) {
					if(input_post("token") && strlen(input_post("token")) == 40) {
						$token = input_post("token");
						$post_data = array(
							"token" => $token,
							"apiKey" => settings("social_login", "api_key"),
							"format" => "json",
						);
						if(settings("social_login", "pro")) $post_data['extended'] = 'true';
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
						curl_setopt($curl, CURLOPT_POST, true);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
						curl_setopt($curl, CURLOPT_HEADER, false);
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($curl, CURLOPT_FAILONERROR, true);
						$result = curl_exec($curl);
						if($result){
							$auth_info = json_decode($result, true);
							if($auth_info['stat'] == 'ok') {
								if(settings("social_login", "pro")) {
									// advanced features (paid accounts only) - add later
									// get_contacts
									// activity
									
									// for now, just login normally
									session("social_login_user", $auth_info['profile']);
									if(dev()) notice_add("success", "User logged in. Check session ('social_login_user') for user information");
								} else {
									// Basic account - sign in
									session("social_login_user", $auth_info['profile']);
									if(dev()) notice_add("success", "User logged in. Check session ('social_login_user') for user information");
								}
							}
						} else {
							notice_add("error", 'Curl error: ' . curl_error($curl));
							notice_add("error", 'HTTP code: ' . curl_errno($curl));
							//echo "<pre>";
							//var_dump($post_data);
						}
						curl_close($curl);
					} else {
						notice_add("error", "Authorization Canceled");
					}
				} else {
					notice_add("error", "CSRF attack detected");
				}
			}
		}
	}
	
	if(!function_exists("social_login_log_out")) {
		function social_login_log_out() {
			unset_session("social_login_user");
			unset_session("social_login_state");
		}
	}
	if(!function_exists("social_login_user")) {
		function social_login_user() {
			return session("social_login_user");
		}
	}
	
	// auto-called
	if(!function_exists("social_login_javascript")) {
		function social_login_javascript() {
			return '
				<script type="text/javascript">
					var rpxJsHost = (("https:" == document.location.protocol) ? "https://" : "http://static.");
					document.write(unescape("%3Cscript src=\'" + rpxJsHost + "rpxnow.com/js/lib/rpx.js\' type=\'text/javascript\'%3E%3C/script%3E"));
				</script>
				<script type="text/javascript">
					RPXNOW.overlay = true;
					RPXNOW.language_preference = \'en\';
				</script>
			';
		}
	}

	if(!function_exists("social_login_login_button")) {
		function social_login_login_button($text = "Sign In", $url = NULL) {
			if(!session("social_login_state")) session("social_login_state", md5(uniqid(rand(), TRUE))); // CSRF protection
			if($url === NULL) {
				$url = BASE_URL . "/" . segments_full() . "?state=" . session("social_login_state");
			} else {
				$url = $url . "?state=" . session("social_login_state");
			}
			return '<a class="rpxnow" onclick="return false;" href="https://' . settings("social_login", "username") . '.rpxnow.com/openid/v2/signin?token_url=' . $url . '">' . $text . '</a>';
		}
	}
	
	if(!function_exists("social_login_embedded")) {
		function social_login_embedded($url = NULL, $width = "400px", $height = "240px") {
			if(!session("social_login_state")) session("social_login_state", md5(uniqid(rand(), TRUE))); // CSRF protection
			if($url === NULL) {
				$url = BASE_URL . "/" . segments_full() . "?state=" . session("social_login_state");
			} else {
				$url = $url . "?state=" . session("social_login_state");
			}
			return '<iframe src="http://' . settings("social_login", "username") . '.rpxnow.com/openid/embed?token_url=' . $url . '" scrolling="no" frameBorder="no" allowtransparency="true" style="width:' . $width . ';height:' . $height . '"></iframe>';
		}
	}
	
	
	register_foot_block("social_login_javascript", social_login_javascript());
	social_login_process();
?>