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
			load_thirdparty_plugin("activerecord/ActiveRecord.php");
			ActiveRecord\Config::initialize(function($cfg) {
				$model_dir = APP_FOLDER . "/models";
				if(setting_isset("activerecord", "models")) $model_dir = settings("activerecord", "models");
				$cfg->set_model_directory($model_dir);
				$cfg->set_connections(array(
					'development' => 'mysql://' . settings("db", "username") . ':' . settings("db", "password") . '@' . settings("db", "host") . '/' . settings("db", "database"),
				));
			});
		}
	}
	
	activerecord_init();
?>