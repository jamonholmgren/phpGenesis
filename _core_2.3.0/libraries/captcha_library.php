<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// captcha_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Nothing for now

//	If you're going to use reCAPTCHA, you have to create an account and get new app keys for 
//	each domain you use it on. It's free. http://recaptcha.net/
	
	load_library('input');
	
	load_thirdparty_plugin("recaptcha/recaptchalib.php");
	
	if(!function_exists("captcha_html")) {
		function captcha_html() {
			return recaptcha_get_html(settings('recaptcha', 'publickey'), globals('captcha', 'error'));
		}
	} // captcha_html
	
	if(!function_exists("captcha_validate")) {
		function captcha_validate() {
			static $value; if (isset($value)) { return $value; }
			
			if (input_post("recaptcha_response_field") != NULL) {  
				$resp = recaptcha_check_answer (settings('recaptcha', 'privatekey'),$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
		
				if ($resp->is_valid) { 
					$value = true; 				
				} else { 
					globals('captcha', 'error', "Security code incorrect."); //  $resp->error
					$value = false;			
				}
			} else {  
				globals('captcha', 'error', "Security code incorrect.");
				$value = false;		
			}
			
			return $value;
		}
	} // captcha_validate
	
	if(!function_exists("captcha_error")) {
		function captcha_error() { 
			return globals('captcha', 'error');
		}
	} // captcha_error
?>