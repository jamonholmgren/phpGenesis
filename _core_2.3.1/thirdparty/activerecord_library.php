<?
	/**
	 *	Includes and initializes the ActiveRecord thirdparty plugin. Just autoload this library in your config.
	 *	
	 *	Need settings("activerecord", "models", APP_FOLDER . "/models"); to be set unless you're using /models as your default.
	 *	
	 *	Uses settings("db", "username") and password, host, and database settings in config too.
	 *	
	 *	
	 */
	if(!function_exists("activerecord_init")) {
		function activerecord_init() {
			$default_error = error_reporting();
			
			load_thirdparty_plugin("activerecord/ActiveRecord.php");
			ActiveRecord\Config::initialize(function($cfg) {
				$model_dir = APP_FOLDER . "/models";
				if(setting_isset("activerecord", "models")) $model_dir = settings("activerecord", "models");
				$cfg->set_model_directory($model_dir);
				$cfg->set_connections(array(
					'development' => 'mysql://' . settings("db", "username") . ':' . settings("db", "password") . '@' . settings("db", "host") . '/' . settings("db", "database"),
				));
			});
			error_reporting($default_error);
		}
	}
		
	if(settings("db", "enabled")) {
		activerecord_init();
		
		/**
		 *	Set up phpGenesis base class
		 *	
		 */
		class phpGenesisModel extends ActiveRecord\Model {
			function print_pre() {
				print_pre($this->attributes());
			}
			function last_sql() {
				return parent::table()->last_sql;
			}
			
			/**
			 * Gets the values from an enum (select) field.
			 * 
			 * @return array
			 */
			function enum_values($field) {
				$result = parent::query("SHOW COLUMNS FROM " . self::table_name());
				while($row = $result->fetch()) {
					if($row['field'] == $field) {
						$types = $row['type'];
						$beginStr = strpos($types, "(") + 1;
						$endStr = strpos($types, ")");
						$types = substr($types, $beginStr, $endStr - $beginStr);
						$types = str_replace("'", "", $types);
						$types = explode(',', $types);
						if($set_keys) {
							$t2 = $types;
							unset($types);
							foreach($t2 as $k => $v) {
								$types[$v] = $v;
							}
						}
						if($sorted) sort($types);
						break;
					}
				}
				return $types;
			}
			
			/**
			 *	Updates a sort field (actually it can be any type of field) with key => value pairs, one of which is
			 *	the ID of the row and one of which is the new value. Uses only one query.
			 *	
			 *	$id_method is set to "auto" and it tries to determine whether the $new_order array was given in
			 *	ID => sort or sort => ID. You can override by passing in TRUE or FALSE yourself.
			 *
			 *	You can pass in extra updates by setting $extra_updates to a string like this: "account_id = 9, project_id = 29"
			 *	
			 *	You can also do some extra conditions if you want by passing in a string to $extra_conditions like this: "name = 'Untitled' OR name = 'Something'";
			 *	
			 */
			function update_sort($field, $new_order, $reverse_array = "auto", $extra_updates = NULL, $extra_conditions = NULL) {
				$query = "UPDATE " . self::table_name() . " SET {$field} = (CASE id \n";
				$id_list = array();
				
				foreach($new_order as $sort => $id) {
					// Determines if the array given was sort => ID or ID => sort.
					if($reverse_array == "auto") {
						$reverse_array = (bool)($sort != 0); // If the first given key is zero, we don't need to reverse the array.
					}
					
					if($reverse_array) list($id, $sort) = array($sort, $id); // flip them
					$query .= " WHEN {$id} THEN {$sort}\n";
					$id_list[] = $id;
				}
				$query .= " END)\n";
				if($extra_updates) $extra_updates = ", {$extra_updates}\n";
				$query .= $extra_updates;
				if($extra_conditions) $extra_conditions = " AND ({$extra_conditions})\n";
				$query .= " WHERE id IN (" . implode(",", $id_list) . ") {$extra_conditions}";
				self::query($query);
			}
		}
	}
?>