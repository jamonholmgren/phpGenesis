<?
	if(!thirdparty_plugin_is_loaded("SpellCorrector/SpellCorrector.php")) {
		load_thirdparty_plugin("SpellCorrector/SpellCorrector.php");
	}
	
	if(!function_exists("spelling_correct")) {
		function spelling_correct($word) {
			return SpellCorrector::correct($word);
		}
	}
?>