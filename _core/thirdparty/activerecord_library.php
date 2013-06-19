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

			public function print_pre() {
				print_pre($this->attributes());
			}
			public function last_sql() {
				return parent::table()->last_sql;
			}
			/**
			 * Gets the values from an enum (select) field.
			 * 
			 * @return array
			 */
			public function enum_values($field) {
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
			public static function update_sort($field, $new_order, $reverse_array = "auto", $extra_updates = NULL, $extra_conditions = NULL) {
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
			
			/**
			 *	Performs an "upsert" rather than an insert. Proper unique index columns must be set up before using this.
			 *
			 *	Use this in place of ->save().
			 *	
			 *	return @boolean
			**/
			public function upsert($validate = FALSE) {
				if($validate && !$this->_validate())
					return false;
				
				$table = static::table();
				$table_name = static::table_name();
				
				$id_field = $this->get_primary_key(true);

				$attributes = $this->attributes();
				unset($attributes[$id_field]);
				
				$update_list = "";
				$query_values = array_merge(array_values($attributes), array_values($attributes)); // the same array twice (for insert and update)
				
				foreach($attributes as $k => $v) {
					$update_list .= ", {$k} = ?";
					if(is_object($v)) $attributes[$k] = date(DATE_ATOM, $v->getTimestamp());
				}
				
				$key_list = implode(", ", array_keys($attributes));
				$value_list = rtrim(str_repeat("?,", count($attributes)), ","); // implode("', '", $attributes);
				
				$q = "
					INSERT INTO {$table_name} 
						({$key_list}) VALUES ({$value_list})
					ON DUPLICATE KEY 
					UPDATE 
						{$id_field} = LAST_INSERT_ID({$id_field})
						{$update_list}
				";
				
				$new_id = static::connection()->query($q, $query_values); // do the upsert
				
				$this->assign_attribute($id_field, static::connection()->insert_id());
				
				return true;
			}
			
			/**
			 * Checks if the field is an object before running the format method
			 * 
			 * @return string
			 */
			public function safe_format($fieldname, $format = "n/j/Y") {
				if(is_object($this->$fieldname)) {
					return $this->$fieldname->format($format);
				} else {
					return "";
				}
			}
		}
		
		class phpGenesisExtendedModel extends phpGenesisModel {
			/**
			 *	These methods allow you to see what attributes are really dirty.
			 *	phpActiverecord marks any attribute as "dirty" when you set it,
			 *	even though the value hasn't changed. This does a more thorough
			 *	job and also allows you to see the previous value of an attribute.
			 *	
			 */
			protected $_original_attributes;
			
			static $after_save = array('reset_original_attributes');
			
			public function after_construct() {
				$this->reset_original_attributes();
			}
			
			public function reset_original_attributes() {
				$this->_original_attributes = $this->attributes();
				if(method_exists($this, "after_save")) $this->after_save();
			}
			
			public function is_really_dirty() {
				if(count($this->really_dirty_attributes())) return TRUE;
				return FALSE;
			}
			
			public function attribute_is_really_dirty($field) {
				if($this->read_attribute($field) !== $this->_original_attributes[$field]) {
					if(is_object($this->read_attribute($field)) && is_object($this->_original_attributes[$field]) && method_exists($this->read_attribute($field), "format")) {
						if($this->read_attribute($field)->format(DATE_ATOM) == $this->_original_attributes[$field]->format(DATE_ATOM)) return FALSE;
					}
					return TRUE;
				}
				return FALSE;
			}
			
			public function really_dirty_attributes() {
				$dirty_attributes = array();
				foreach($this->attributes() as $field => $value) {
					if($field != "updated_at" && $this->attribute_is_really_dirty($field)) $dirty_attributes[$field] = $value;
				}
				return $dirty_attributes;
			}
			
			public function original_attributes() {
				return $this->_original_attributes;
			}
			
			public function original_attribute($field) {
				return $this->_original_attributes[$field];
			}
		}
	}
?>