<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// loader_library last edited 12/11/2009 by Jamon Holmgren
// TO-DO
//	nothing for now
		
	globals('libraries', 'loader', true);

	/**
	 * Includes app specific libraries from the app's include folder
	 *
	 * @param string $include_file
	 */
	if(!function_exists("app_include")) {
		function app_include($include_file, $args = array()) {
			foreach((array)$args as $k => $v) $$k = $v; // makes $arg variables into local variables
			$tmp = APP_FOLDER . "/includes/" . $include_file . ".php";
			if(file_exists($tmp)) { include($tmp); return true; }
			return false;
		}
	} // end app_include

	/**
	 * Includes flat files from the /content folder.
	 *
	 * @param string $include_file
	 */
	if(!function_exists("content_include")) {
		function content_include($include_file) {
			$tmp = BASE_FOLDER . "/content/" . $include_file . "_content.php";
			if(file_exists($tmp)) { include($tmp); return true; }
			return false;
		}
	} // end content_include

	/**
	 * Loads a library by first looking in the plugins folder then in the
	 * Core's library folder.
	 *
	 * @param string|array $library_name
	 * @return boolean
	 */
	if(!function_exists("load_library")) {
		function load_library($library_name) {

			if (is_array($library_name)) {
				$loaded = true;
				foreach ($library_name as $library) {
					$loaded = $loaded && load_library($library);
				}
				return $loaded;
			}

			// Check if already loaded
			if(globals('libraries', $library_name)) return true;
			
			$lib_loaded = false;

			// Check for plugin library
			$filename = PLUGINS_FOLDER . '/libraries/' . $library_name . '_library.php';
			if(file_exists($filename)) {
				include_once($filename);
				$lib_loaded = true;
			}

			// Check for thirdparty library
			$filename = PLUGINS_FOLDER . '/thirdparty/' . $library_name . '_library.php';
			if(file_exists($filename)) {
				include_once($filename);
				$lib_loaded = true;
			}

			// Check for core library
			$filename = CORE_FOLDER . '/libraries/' . $library_name . '_library.php';
			if(file_exists($filename)) {
				include_once($filename);
				$lib_loaded = true;
			}
			
			// Check for core thirdparty library
			$filename = CORE_FOLDER . '/thirdparty/' . $library_name . '_library.php';
			if(file_exists($filename)) {
				include_once($filename);
				$lib_loaded = true;
			}
			
			if (!$lib_loaded) { die("Could not find the library named: " . $library_name . " in " . PLUGINS_FOLDER . " or " . CORE_FOLDER); }
			globals('libraries', $library_name, true);
			return $lib_loaded;
		}
	} // end load_library
	
	
	/**
	 * Alias for load_library
	 *
	 */
	if(!function_exists("load_libraries")) {
		function load_libraries($libraries) {
			return load_library($libraries);
		}
	} // load_libraries
	

	if(!function_exists("library_is_loaded")) {
		function library_is_loaded($library_name) {
			if(globals('libraries', $library_name)) return true;
			return false;
		}
	} // end library_is_loaded

	if(!function_exists("load_thirdparty_plugin")) {
		function load_thirdparty_plugin($thirdparty_plugin_name) {
			if(thirdparty_plugin_is_loaded($thirdparty_plugin_name)) return true;
			if(file_exists(PLUGINS_FOLDER . "/thirdparty/" . $thirdparty_plugin_name)) {
				include(PLUGINS_FOLDER . "/thirdparty/" . $thirdparty_plugin_name);
			} elseif(file_exists(CORE_FOLDER . "/thirdparty/" . $thirdparty_plugin_name)) {
				include(CORE_FOLDER . "/thirdparty/" . $thirdparty_plugin_name);
			} else {
				die("Couldn't load third party plugin " . $thirdparty_plugin_name);
			}
			globals("thirdparty", $thirdparty_plugin_name, true);
		}
	} // load_thirdparty_plugin

	if(!function_exists("thirdparty_plugin_is_loaded")) {
		function thirdparty_plugin_is_loaded($thirdparty_plugin_name) {
			if(globals('thirdparty', $thirdparty_plugin_name)) return true;
			return false;
		}
	} // end thirdparty_plugin_is_loaded
	
	
	if(!function_exists("load_model")) {
		function load_model($model_name) {
			$model_name = str_replace("_model.php", "", $model_name);
			if(model_is_loaded($model_name)) return true;
			if(file_exists(APP_FOLDER . "/models/" . $model_name . "_model.php")) {
				include(APP_FOLDER . "/models/" . $model_name . "_model.php");
				$model_loaded = true;
			} else {
				$model_loaded = false;
			}
			globals("model", $model_name, $model_loaded);
			return $model_loaded;
		}
	} // load_model
	
	/**
	 * Alias for load_model that can handle an array of models to load.
	 * This function is useful when you need to load several models on one page.
	 * 
	 */
	if(!function_exists("load_models")) {
		function load_models($models) {
			if(is_array($models)) {
				foreach($models as $model) {
					$return[$model] = load_model($model);
				}
				return $return;
			}
		}
	} // load_models
	
	if(!function_exists("model_is_loaded")) {
		function model_is_loaded($model_name) {
			$model_name = str_replace("_model.php", "", $model_name);
			if(globals('model', $model_name)) return true;
			return false;
		}
	} // end model_is_loaded
	
?>