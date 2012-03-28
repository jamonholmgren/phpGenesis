<?
	// meta title |.....................................................................| 70 characters max
	meta("title", "Reset Password");
	// meta description |.....................................................................................................................................................| 150 characters max
	meta("description", "Use this form to reset your password!");
	
	load_library("form");
	
	if(form_posted("forgot_password", FALSE)) {
		$email = form_validate("email", "trim|val_email|val_required");
		
		if(form_is_valid() && $user = User::find_by_email($email)) {
			$user->send_reset_password();
			notice_add("success", "Please check your email for the reset password link. This link will work for the next hour.", "login");
		} else {
			notice_add("error", "Couldn't reset password - check your email address.", "login");
		}
	}
	
	if(form_posted("reset_password", FALSE)) {
		$reset_key = form_validate("reset_key", "trim|val_required");
		$new_password = form_validate("password", "trim|val_required");
		$conf_pass = form_validate("conf_pass", "trim|val_required");
		
		if(form_is_valid() && $new_password == $conf_pass) {
			$user = User::find_by_newpass_key($reset_key);
			if($user->newpass_expiration && $user->newpass_expiration->getTimestamp() > time()) {
				$user->password = $new_password;
				$user->newpass_expiration = NULL;
				$user->newpass_key = NULL;
				
				if($user->save()) {
					notice_add("success", "Great! Now log in with your new password.", "login");
					redirect("/users/login/");
				} else {
					notice_add("error", "Couldn't save the new password!", "login");
				}
			} else {
				notice_add("error", "Sorry, that link is no longer valid. Please try resetting your password again.");
			}
		} else {
			notice_add("error", "Couldn't reset password - make sure your passwords match.", "login");
		}
	}
?>
<? layout_open("default"); ?>
	<? layout_section("content"); ?>
		<?=notices_show()?>
		<? if(input_get("reset_key") === NULL): ?>
			<?=form_open("forgot_password")?>
				<?=form_textbox("email", "Email: ", form_value("email"))?>
				<?=form_button("submit", "", "Send Reset Link")?>
			<?=form_close()?>
		<? else: ?>
			<?=form_open("reset_password")?>
				<?=form_textbox("reset_key", "Reset Password Key: ", input_get("reset_key"))?>
				<?=form_password("password", "Password: ", "")?>
				<?=form_password("conf_pass", "Confirm: ", "")?>
				<?=form_button("submit", "", "Reset Password")?>
			<?=form_close()?>
		<? endif; ?>
	<? layout_section_close(); ?>
<? layout_close(); ?>
