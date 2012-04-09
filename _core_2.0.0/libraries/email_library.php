<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// email_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	Plain text/html versions
//	More email options
		
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
			
			return mail($to, $subject, $message, $headers);
		}
	} // end email_send
	
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
		function phpmailer_send($to, $subject, $message, $from, $options = array()) {
			if(!thirdparty_plugin_is_loaded("phpmailer/class.phpmailer.php")) {
				// Kevin make sure this works.
				load_thirdparty_plugin("phpmailer/class.phpmailer.php");
			}
			$mail = new PHPMailer(TRUE); // the true param means it will throw exceptions on errors, which we need to catch
			
			// Kevin fill out the rest here
			
			$mail->SetFrom($from);
			$mail->AddAddress($to);
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
			
			return $mail->Send();
		}	
	}
?>