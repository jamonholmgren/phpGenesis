<?php
/**
 *	Permission Library
 *	
 *	Advanced user permissions functions.
 *	
 *	phpGenesis Copyright (c) 2011. All Rights Reserved.
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *	
 *	Maintained by ClearSight Studio
 *	
 *	@todo Convert to ActiveRecord
 * @package phpGenesis
 */

	
	if(!defined("USER_GROUPS_TABLE")) define("USER_GROUPS_TABLE", "user_groups");
	if(!defined("GROUPS_TABLE")) define("GROUPS_TABLE", "groups");
	if(!defined("GROUP_PERMISSIONS_TABLE")) define("GROUP_PERMISSIONS_TABLE", "group_permissions");
	if(!defined("PERMISSIONS_TABLE")) define("PERMISSIONS_TABLE", "permissions");
	if(!defined("PERMISSION_LEVELS_TABLE")) define("PERMISSION_LEVELS_TABLE", "permission_levels");
	if(!defined("PERMISSION_TYPES_TABLE")) define("PERMISSION_TYPES_TABLE", "permission_types");

	if(!defined("PL_NO_ACCESS")) define('PL_NO_ACCESS',		0);
	if(!defined("PL_READ_ONLY")) define('PL_READ_ONLY',		10);
	if(!defined("PL_READ_ONLY_PLUS")) define('PL_READ_ONLY_PLUS',	20);
	if(!defined("PL_READ_WRITE")) define('PL_READ_WRITE',		30);
	if(!defined("PL_FULL")) define('PL_FULL',			40);
	if(!defined("PL_NEVER")) define('PL_NEVER',			99);
	
	// Permission caching
	
	if(!defined("PERMISSION_SESSION_NAME")) define('PERMISSION_SESSION_NAME',	'PERMISSION_CACHE');

	load_library('users');


/***************************************** GROUPS *********************************************/
	
	/**
	 *	Returns all groups.
	 *	
	 *	@returns array
	 */
	if(!function_exists("group_rows")) {
		function group_rows() {
			if(!isset($GLOBALS['users']['groups'])) {
				$GLOBALS['users']['groups'] = db_query_rows("
					SELECT * FROM " . GROUPS_TABLE . "
					ORDER BY name"
				);
			}
			return $GLOBALS['users']['groups'];
		}
	}

	/**
	 *	Alias for group_rows().
	 *	
	 *	@returns array
	 */
	if(!function_exists("groups_get_all")) {
		function groups_get_all() {
			return group_rows();
		}
	}
	
	
	/**
	 *	Gets a particular group.
	 *	
	 *	@returns array
	 */
	if(!function_exists("group_get_by_id")) {
		function group_get_by_id($id) {
			return db_query_row("SELECT * FROM " . GROUPS_TABLE  . " WHERE id = {$id}");
		}
	}
	
	/**
	 *	Inserts a group.
	 *	
	 *	@returns NULL
	 */
	if(!function_exists("group_add")) {
		function group_add($name) {
			$new_row = array();
			$new_row['name'] = $name;
			
			$group_id = db_insert(GROUPS_TABLE, $new_row);
	
			db_query("
				INSERT INTO group_permissions (group_id, permission_id, permission_level_id)
				SELECT groups.id, permissions.id, permissions.default_permission_level_id
				FROM groups
				LEFT JOIN group_permissions ON groups.id = group_permissions.group_id
				CROSS JOIN permissions
				WHERE groups.id = {$group_id} AND group_permissions.id IS NULL
			");
		}
	}
	
	/**
	 *	Updates a group.
	 *	
	 *	@returns bool
	 */
	if(!function_exists("group_update_by_id")) {
		function group_update_by_id($id, $updated_row) {
			return db_update(GROUPS_TABLE, $id, $updated_row);
		}
	}
	
	/**
	 *	Deletes a group, if deletable.
	 *	
	 *	@returns bool
	 */
	if(!function_exists("group_delete_by_id")) {
		function group_delete_by_id($id) {
			return db_query("DELETE FROM " . GROUPS_TABLE . " WHERE id = {$id} AND can_delete = 1");
		}
	}
	
	/**
	 *	Gets group permissions by group id.
	 *	
	 *	@returns array
	 */
	if(!function_exists("group_get_group_permissions_by_group_id")) {
		function group_get_group_permissions_by_group_id($group_id) {
			$cache = 'group_permissions_' . $group_id;
			if(!isset($GLOBALS['users'][$cache])) {
				$GLOBALS['users'][$cache] = db_query_rows("
					SELECT gp.id, p.name, pt.name AS permission_type, p.id AS permission_id, gp.permission_level_id
					FROM " . GROUP_PERMISSIONS_TABLE . " gp
						INNER JOIN " . PERMISSIONS_TABLE . " p ON p.id = gp.permission_id
						INNER JOIN " . PERMISSION_TYPES_TABLE . " pt ON pt.id = p.permission_type_id
					WHERE gp.group_id = {$group_id}
					ORDER BY pt.sort ASC, p.name ASC
				");
			}
			return $GLOBALS['users'][$cache];
		}
	}
	
	/**
	 *	Updates group permission levels by group id.
	 *	
	 *	@returns array
	 */
	if(!function_exists("group_update_group_permission")) {
		function group_update_group_permission($group_id, $permission_id, $level) {
			return db_query("
				UPDATE " . GROUP_PERMISSIONS_TABLE . " SET permission_level_id = {$level}
				WHERE group_id={$group_id} AND permission_id={$permission_id}
			");
		}
	}
	
	/**
	 *	Gets any groups a user is a part of.
	 *	
	 *	@returns array
	 */
	if(!function_exists("user_groups_get")) {
		function user_groups_get($user_id = NULL) {
			if($user_id == NULL) $user_id = user_id();
			$cache = "user_groups_{$user_id}";
			if(!isset($GLOBALS['users'][$cache])) {
				$GLOBALS['users'][$cache] = db_query_rows("
					SELECT ug.id AS user_group_id, g.id AS group_id, g.name AS group_name
					FROM " . USER_GROUPS_TABLE . " ug
						INNER JOIN " . GROUP_TABLE . " g ON g.id = ug.group_id
					WHERE ug.user_id = {$user_id}
					ORDER BY g.name ASC
				");
			}
			return $GLOBALS['users'][$cache];
		}
	}
	
	/**
	 *	Gets any users that are in a particular group.
	 *	
	 *	@returns array
	 */
	if(!function_exists("group_users")) {
		function group_users($group_ids) {
			$group_clauses = array();
			
			if (is_array($group_ids)) {
				foreach ($group_ids as $group_id) $group_clauses[] = "ug.group_id = '{$group_id}'";
			} else {
				$group_clauses[] = "ug.group_id = '{$group_ids}'";
			}
			
			$where_clause = implode(" OR ", $group_clauses);
			$sql = "
				SELECT DISTINCT u.*
				FROM " . USERS_TABLE . " u
					INNER JOIN " . USER_GROUPS_TABLE . " ug ON ug.user_id = u.id
				WHERE {$where_clause}
			";
			return db_query_rows($sql);
		}
	}
	
	
	/**
	 *	Adds a user to a group, if not already in the group.
	 *	
	 *	@returns mixed
	 */
	if(!function_exists("user_group_add")) {
		function user_group_add($user_id, $group_id) {	
			$result = db_query("
				INSERT INTO " . USER_GROUPS_TABLE . " (user_id, group_id)
				SELECT {$user_id}, {$group_id}
				FROM " . GROUP_TABLE . " g
					INNER JOIN " . USERS_TABLE . " u ON u.id = {$user_id}
					LEFT JOIN " . USER_GROUPS_TABLE . " ug ON ug.user_id = u.id AND ug.group_id = g.id
				WHERE g.id={$group_id} AND ug.id is NULL
			");
			return $result;
		}
	}
	
		
	/**
	 *	Deletes a user from a group.
	 *	
	 *	@returns mixed
	 */
	if(!function_exists("user_group_delete")) {
		function user_group_delete($user_id, $group_id) {		
			return db_query("DELETE FROM " . USER_GROUPS_TABLE . " WHERE user_id={$user_id} AND group_id={$group_id}");
		}
	}
	
	
	/***************************************** PERMISSIONS ****************************************/
	
	/**
	 *	Permission function to evaluate the permission level of the currently logged in user.
	 *	You can either pass in a permission feature ID or the name of the feature as the second
	 *	argument.
	 *
	 *	For example, if(can(PL_READ_WRITE, 'Users')):
	 *	
	 *	@returns bool
	 */
	if(!function_exists("can")) {
		function can($min_permission_level_id, $permission_feature) {
			if(user_id() <= 0) return false; // not logged in
			if(user_is_admin()) return true; // super admin
			
			if(is_int($permission_feature)) {
				$permission_id = $permission_feature;
			} else {
				$permissions = permissions_get_all();
				if(isset($permissions[$permission_feature]['id'])) {
					$permission_id = $permissions[$permission_feature]['id'];
				} else {
					return false; // Permission feature doesn't exist.
				}
			}
			
			$permissions = NULL;
			
			// Cache permissions for lighter db accessing.
			if(!session_isset(PERMISSION_SESSION_NAME) ) { 
				$permission_rows = permissions_get_by_user_id(user_id());			
				$permissions = array();
				if(is_array($permission_rows)) foreach ($permission_rows as $row) {
					$permissions[$row['permission_id']] = $row['permission_level_id'];
				}
				session(PERMISSION_SESSION_NAME, $permissions);
			} else { 
				$permissions = session(PERMISSION_SESSION_NAME);
			}
			
			$can = false;
			
			if(is_array($permission_id)) {
				foreach ($permission_id as $p_id) {
					$level = isset($permissions[$p_id]) ? $permissions[$p_id] : 0;
					if ($level == PL_NEVER) return false; // if we encounter a NEVER, immediately say no.
					$can = $can || ($level >= $min_permission_level_id);
				}
			} else {	
				$level = isset($permissions[$permission_id]) ? $permissions[$permission_id] : 0;
				if ($level == PL_NEVER) return false;
				$can = $can || ($level >= $min_permission_level_id);
			}
			
			return $can;
		}
	}
	
	/**
	 *	Returns (and caches) a list of all permission features.
	 *	
	 *	@returns array
	 */
	if(!function_exists("permissions_get_all")) {
		function permissions_get_all() {
			$cache = "permission_features_all";
			if(!is_array(session($cache))) {
				$permissions = db_query_rows("
					SELECT *
					FROM " . PERMISSIONS_TABLE . "
					ORDER BY name ASC
				", "name");
				session($cache, $permissions);
			}
			return session($cache);
		}
	}
	
	/**
	 *	Finds all permissions for a particular user.
	 *	
	 *	@returns array
	 */
	if(!function_exists("permissions_get_by_user_id")) {
		function permissions_get_by_user_id($user_id = NULL) {
			if($user_id == NULL) $user_id = (int)user_id();
			
			$sql = "
				SELECT permission_id, MAX(permission_level_id) AS permission_level_id
				FROM " . USER_GROUPS_TABLE . " ug
					INNER JOIN " . GROUP_PERMISSIONS_TABLE . " gp ON ug.group_id = gp.group_id
				WHERE ug.user_id = {$user_id}
				GROUP BY permission_id
			";
			
			return db_query_rows($sql, "permission_id");
		}
	}
	
	/**
	 *	Explains where permissions are inherited from
	 *	
	 *	@returns array
	 */
	if(!function_exists("user_permissions_explain")) {
		function user_permissions_explain($user_id = NULL) {
			if($user_id == NULL) $user_id = (int)user_id();
			
			$sql = "
				SELECT p.permission_type_id, pt.name AS permission_type, gp.permission_id, p.name AS permission_name, gp.permission_level_id, pl.name AS permission_level, ug.group_id, g.name AS group_name
				FROM " . USER_GROUPS_TABLE . " ug
					INNER JOIN " . GROUP_PERMISSIONS_TABLE . " gp ON ug.group_id = gp.group_id
					LEFT JOIN (
						SELECT ug.user_id, ug.group_id, gp.permission_id, gp.permission_level_id
						FROM " . USER_GROUPS_TABLE . " ug
							INNER JOIN " . GROUP_PERMISSIONS_TABLE . " gp ON ug.group_id = gp.group_id
					) ug2 ON ug2.user_id = ug.user_id AND ug2.permission_id = gp.permission_id AND gp.permission_level_id < ug2.permission_level_id
					INNER JOIN " . GROUPS_TABLE . " g ON ug.group_id = g.id
					INNER JOIN " . PERMISSIONS_TABLE . " p ON gp.permission_id = p.id
					INNER JOIN " . PERMISSION_LEVELS_TABLE . " pl ON gp.permission_level_id = pl.id
					INNER JOIN " . PERMISSION_TYPES_TABLE . " pt ON p.permission_type_id = pt.id
				WHERE ug.user_id = {$user_id}
					AND ug2.permission_level_id IS NULL
				ORDER BY pt.sort ASC, p.name ASC, g.name ASC
			";
			
			return db_query_rows($sql);
		}
	}


?>