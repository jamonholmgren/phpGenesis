<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	Email Library
 *	
 *	Use instead of PHP's default mail function for HTML formatting.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	@todo Plain text/html versions
 *	@todo More email options
 *	@package phpGenesis
 */

// email_library last edited 12/11/2009 by Jamon Holmgren
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
			
			$headers .= "Return-Path: {$from}\r\n";
			
			return mail($to, $subject, $message, $headers);
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
	 *	Uses the thirdparty plugin class "PHPMailer" to send emails. Can handle attachmetns in the
	 *	options array by adding a filename (or array of filenames) to "attachment".
	 *
	 *	Usage example: 
	 *	phpmailer_send('test@example.com', 'Sweet attachment', 'Check it out', 'me@example.com', array('attachment' => 'filename.jpg'));
	 *
	 **/
	if(!function_exists("phpmailer_send")) {
		function phpmailer_send($to, $subject, $message, $from = NULL, $options = array()) {
			if(!thirdparty_plugin_is_loaded("phpmailer/class.phpmailer.php")) {
				// Kevin make sure this works.
				load_thirdparty_plugin("phpmailer/class.phpmailer.php");
			}
			$mail = new PHPMailer(TRUE); // the true param means it will throw exceptions on errors, which we need to catch
			
			if(is_array($options['smtp'])) { // legacy, do not use.
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->Host = $options['smtp']['host'];
				$mail->Port = $options['smtp']['port'];
				$mail->Username = $options['smtp']['username'];
				$mail->Password = $options['smtp']['password'];
				if($options['smtp']['debug']) $mail->SMTPDebug = 2;
				$mail->SMTPSecure = $options['smtp']['secure'];

			} elseif(is_array(settings("smtp"))) {
				$mail->IsSMTP();
				$mail->SMTPAuth = true;													// Should always be true (we don't want to use an un-authenticated smtp server...)
				$mail->Host = settings("smtp", "host");					// host
				$mail->Port = settings("smtp", "port");					// port
				$mail->Username = settings("smtp", "username");	// username
				$mail->Password = settings("smtp", "password");	// Password
				$mail->SMTPSecure = settings("smtp", "secure");	// ssl/tls
				if(settings("smtp", "debug")) $mail->SMTPDebug = 2;
			}
			if(settings("smtp", "from")) { // if we have a from address, use the from field to set the ReplyTo.
				$mail->SetFrom(settings("smtp", "from"));
				if(is_array($from)) {
					$mail->AddReplyTo($from[1], $from[0]);
				} else {
					$mail->AddReplyTo($from);
				}
			} else {											// otherwise just use the from field and don't bother with the ReplyTo
				if(is_array($from)) {
					$mail->SetFrom($from[1], $from[0]);
				} else {
					$mail->SetFrom($from);
				}
			}
			if(is_array($to)) {
				foreach($to as $name => $recip) {
					$mail->AddAddress($recip, $name);
				}
			} else {
				$mail->AddAddress($to);
			}
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			
			
			if(isset($options['attachment'])) {
				if(is_array($options['attachment'])) {
					foreach($options['attachment'] as $at) {
						$mail->AddAttachment($at);	
					}
				} else {
					$mail->AddAttachment($options['attachment']);	
				}
			}

			if(isset($options['cc'])) {
				$mail->AddCC($options['cc']);
			}
			if(!$result = $mail->Send()) {
				return "Mailer Error: " . $mail->ErrorInfo;
			} else {
				return $result;
			}
		}	
	}
?>