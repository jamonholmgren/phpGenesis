<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Email Library
 *	
 *	Use instead of PHP's default mail function for HTML formatting.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 *	@todo Plain text/html versions
 *	@todo More email options
 *	@package phpGenesis
 */

// email_library last edited 03/28/2011 by Silas J. Matson
// TO-DO
//	See above
		
	/**
	 * Friendly alias for mail(). Allows HTML formatting.
	 * 
	 * Use $options['cc'] or ['bcc'] to send carbon copies
	 * 
	 * @return bool
	 */
	if(!function_exists("email_send")) {
		function email_send($to, $subject, $message, $from, $options = array()) {
			$headers  = "From: {$from}\r\n";

			if(isset($options['cc'])) {
				if(is_array($options['cc'])) $options['cc'] = implode(",", $options['cc']);
				$headers .= "CC: {$options['cc']}\r\n";
			}
			
			if(isset($options['bcc'])) {
				if(is_array($options['bcc'])) $options['bcc'] = implode(",", $options['bcc']);
				$headers .= "BCC: {$options['bcc']}\r\n";
			}
			
			$headers .= "Reply-To: {$from}\r\n";
			$headers .= "Return-Path: {$from}\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			
			if(is_array($to)) $to = implode(",", $to);
			
			$params = "Return-Path: {$from}\r\n";
			
			return mail($to, $subject, $message, $headers, $params);
		}
	} // end email_send
	
	/**
	 * Alias for email_send()
	 * 
	 * @return bool
	 */
	if(!function_exists("email")) {
		function email($to, $subject, $message, $from, $options = array()) {
			return email_send($to, $subject, $message, $from, $options);
		}
	} // end email alias
	
	/**
	 *	Uses the thirdparty plugin class "PHPMailer" to send emails. 
	 *	Can handle carbon copies in the options array (Must use smtp)
	 *	Can handle attachments in the options array by adding a filename 
	 *	(or array of filenames) to "attachment".
	 *
	 *	Usage example: 
	 *	phpmailer_send('test@example.com', 'Sweet attachment', 'Check it out', 'me@example.com', array('attachment' => 'filename.jpg', 'cc' => 'you@example.com'));
	 *
	 **/
	if(!function_exists("phpmailer_send")) {
		function phpmailer_send($to, $subject, $message, $from, $options = array()) {
			if(!thirdparty_plugin_is_loaded("phpmailer/class.phpmailer.php")) {
				// Kevin make sure this works.
				load_thirdparty_plugin("phpmailer/class.phpmailer.php");
			}
			$mail = new PHPMailer(TRUE); // the true param means it will throw exceptions on errors, which we need to catch
			
			// Kevin fill out the rest here
			
			if(is_array($from)) {
				$mail->SetFrom($from[1], $from[0]);
			} else {
				$mail->SetFrom($from);
			}
			$mail->AddAddress($to);
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			
			if(isset($options['bcc'])){
				if(is_array($options['bcc'])) {
					foreach($options as $bc) {
						$mail->AddBCC($bc, '');
					}
				} else {
					$mail->AddBCC($options['bcc'], "");
				}
			}
			
			if(isset($options['cc'])){
				if(is_array($options['cc'])) {
					foreach($options as $cc) {
						$mail->AddCC($cc, '');
					}
				} else {
					$mail->AddCC($options['cc'], "");
				}
			}
			
			if(isset($options['attachment'])) {
				if(is_array($options['attachment'])) {
					foreach($options['attachment'] as $at) {
						$mail->AddAttachment($at);	
					}
				} else {
					$mail->AddAttachment($options['attachment']);	
				}
			}
			
			return $mail->Send();
		}	
	}
?>