<?
	load_library("user");
	
	class User extends UserLibrary {
		/******************************************* Permissions *************************************************/
		function can_manage_users() {
			return $this->level("Admin");
		}
		function can_manage_user($id) {
			return $id == $this->id || $this->can_manage_users();
		}
		
	}
?>