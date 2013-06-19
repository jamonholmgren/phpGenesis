<?php
	if(!function_exists("purifier_load")) {
		function purifier_load() {
			if(!thirdparty_plugin_is_loaded("htmlpurifier/HTMLPurifier.standalone.php")) {
				load_thirdparty_plugin("htmlpurifier/HTMLPurifier.standalone.php");
				
				// set up HTMLPurifier
				$tmp_config = HTMLPurifier_Config::createDefault();
				if(is_array(settings("input", "config"))) {
					foreach(settings("input", "config") as $key => $value) {
						$tmp_config->set($key, $value);
					}
				}
				
				globals('objects', 'htmlpurifier', new HTMLPurifier($tmp_config));
				unset($tmp_config);
			}
			return globals("objects", 'htmlpurifier');
		}
	}
	if(!function_exists("purifier_clean")) {
		function purifier_clean($input) {
			$p = purifier_load();
			if(is_object($p)) return $p->purify($input);
			die("Couldn't load HTML Purifier object!");
		}
	}
?>
