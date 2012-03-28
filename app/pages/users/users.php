<?php
	// meta title |.....................................................................| 70 characters max
	meta("title", "Edit User");
	// meta description |.....................................................................................................................................................| 150 characters max
	meta("description", "");
	
	load_library("form");
	load_library("social_login");
	
	if(input_get("logout") == "true") {
		User::log_out();
		social_login_log_out();
		notice_add("info", "Logged out!", "login");
	}
	
	if(!User::logged_in() || !User::current()) {
		redirect("/users/login/");
	}
	
	if(segments_action() == "edit") {
		$user = User::find_by_id(segments_id());
	} elseif(segments_action() == "add") {
		if(User::current()->can_manage_users()) $user = new User();
	}
	
	if(segments_action() == "delete") {
		if(User::current()->level(0) || segments_id() == User::current()->id) {
			$user = User::find_by_id(segments_id());
			if($user && $user->delete()) {
				notice_add("success", "Deleted user!");
				if($user->id == user_id()) {
					redirect("/users/?logout=true");
				}
				redirect("/users/");
			} else {
				notice_add("error", "Couldn't delete that user!");
			}
		} else {
			notice_add("error", "You don't have permission to delete this user!");
		}
	}
	
	if(form_posted("edit_user")) {
		if(User::current()->level(0) || segments_id() == User::current()->id) {
			$update_array = array(
				"username" => form_value("username"),
				"first_name" => form_value("first_name"),
				"last_name" => form_value("last_name"),
				"email" => form_value("email"),
			);
			$user->set_attributes($update_array);
			
			if(form_value("password")) {
				if(form_value("password") == form_value("conf_pass")) {
					$user->password = form_value("password");
				} else {
					notice_add("error", "Passwords do not match, please try again.");
				}
			}
			
			if($user->is_valid() && $user->save()) {
				notice_add("success", "Your changes were successfully saved.");
				redirect("/users/{$user->id}/edit/");
			} else {
				notice_add("error", "There was an error saving your changes. " . $user->error());
			}
		} else {
			notice_add("error", "Sorry, you don't have permission to change this user.");
		}
	}
	if(segments_action() == "list") {
		$users = User::all(array("order" => "first_name, last_name"));
	}
?>
<? layout_open("default"); ?>
	<? layout_section("content"); ?>
		<?=notices_show()?>
		<p><a href="/users/?logout=true">Log Out</a></p>
		<? if(segments_action() == "edit" || segments_action() == "add"): ?>
			<? if($user): ?>
				<? if(User::current()->can_manage_user($user->id)): ?>
					<h2><?=deslugify(segments_action())?> User</h2>
				<? else: ?>
					<h2>View User</h2>
				<? endif; ?>
				<?=form_open("edit_user", "", NULL, array(), ((User::current()->can_manage_user($user->id))? FORM_ENABLED : FORM_DISABLED))?>
					<?=form_textbox("username", "Username: ", $user->username, array("error" => (($user->errors)? $user->errors->on("username") : "")))?>
					<?=form_textbox("first_name", "First Name: ", $user->first_name, array("error" => (($user->errors)? $user->errors->on("first_name") : "")))?>
					<?=form_textbox("last_name", "Last Name: ", $user->last_name, array("error" => (($user->errors)? $user->errors->on("last_name") : "")))?>
					<?=form_textbox("email", "Email: ", $user->email, array("error" => (($user->errors)? $user->errors->on("email") : "")))?>
					<?=form_password("password", "New Password: " . (($user->is_new_record())? "" : "(leave blank for no change)"), "", array("error" => (($user->errors)? $user->errors->on("password") : "")))?>
					<?=form_password("conf_pass", "Confirm New Password: ")?>
					<?=form_button("submit", "", "Save Changes", array("control_class" => "large_button button_blue"))?>
					<p><a href="/users/">Cancel</a></p>
					<? if(!$user->is_new_record() && (User::current()->can_manage_user($user->id))): ?>
						<p><a href="/users/<?=$user->id?>/delete/" class="confirm" data-confirm="Are you sure you want to delete user? There is NO UNDO.">Delete</a></p>
					<? endif; ?>
				<?=form_close()?>
			<? else: ?>
				<p class="error">Couldn't find that user!</p>
			<? endif; ?>
		<? elseif(segments_action() == "list"): ?>
			<h2>Users</h2>
			<? if(User::current()->can_manage_users()): ?>
				<p><a href="/users/add/">Add New User</a></p>
			<? endif; ?>
			<ul class="users">
				<? if($users): ?>
					<? foreach($users as $u): ?>
						<li>
							<a href="/users/<?=$u->id?>/edit/">
								<? if($u->first_name || $u->last_name): ?>
									<?=$u->first_name?> <?=$u->last_name?>
								<? else: ?>
									<?=$u->username?>
								<? endif; ?>
							</a>
							 (<?=$u->email?>)
						</li>
					<? endforeach; ?>
				<? else: ?>
					<p class="error">There are no users! Wait, how did you get in here...?</p>
				<? endif; ?>
			</ul>
		<? endif; ?>
	<? layout_section_close(); ?>
<? layout_close(); ?>
