<?
	// meta title |.....................................................................| 70 characters max
	meta("title", "User Login");
	// meta description |.....................................................................................................................................................| 150 characters max
	meta("description", "");
	
	load_library("form");
	
	// Log in using the social login plugin if that's what they used
	/*
		load_library("social_login");
		if(social_login_user()) {
			$social_user = social_login_user();
			//$user = User::find_by_email($social_user['email']);		
			//var_dump($user->email); var_dump($social_user['email']); die();
			if(!$user = User::find_by_identifier($social_user['identifier'])) {
				if($social_user['email']) $user = User::find_by_email($social_user['email']);
				if(!$user) {
					$user = new User();
					$user->username = $social_user['preferredUsername'] . " at " . $social_user['providerName'];
					$user->email = $social_user['email'];
					$user->password = md5(uniqid(rand(), TRUE));
					$user->identifier = $social_user['identifier'];
					if($user->save()) {
						notice_add("success", "New user created");
					} else {
						echo "Couldn't save user";
						echo (string)$user->errors;
						$user->print_pre();
						die_print_pre($social_user);
					}
				}
			}
			User::log_in_user($user);
		}
	*/
	
	// Log in using the standard form
	if(form_posted("login", FALSE)) {
		$username = form_validate("username", "trim|val_required");
		$password = form_validate("password", "val_required");
		$remember = form_validate_checkbox("remember", TRUE, FALSE);
		
		if(form_is_valid()) {
			if(User::log_in($username, $password, $remember)) {
				notice_add("info", "Successfully logged in! Welcome!", "login");
			} else {
				notice_add("error", User::error(), "login");
			}
		} else {
			notice_add("error", "You forgot to fill out one of the fields!", "login");
		}
	}

	if(User::logged_in()) redirect("/users/");
?>
<? layout_open("default"); ?>
	<? layout_section("content"); ?>
		<?=notices_show()?>
		<? if(User::logged_in()): ?>
			<p><a href="/users/?logout=true">Log Out</a></p>
		<? else: ?>
			<?=social_login_login_button()?>
			<?=form_open("login")?>
				<?=form_textbox("username", "Username", form_value("username"))?>
				<?=form_password("password", "Password")?>
				<?=form_checkbox("remember", "Remember me", form_value("remember"), array("class" => "inline-field"))?>
				<?=form_button("submit", "", "Log In")?> <a href="/users/reset/" class="remember-me">Forgot Password?</a>
			<?=form_close()?>
		<? endif; ?>
	<? layout_section_close(); ?>
<? layout_close(); ?>
