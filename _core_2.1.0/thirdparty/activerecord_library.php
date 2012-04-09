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
						$types = split(',', $types);
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
		}
	}
?>