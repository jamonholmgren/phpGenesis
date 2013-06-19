<?php
	// Don't modify this file
	require("init.php"); // Load initial definitions, such as where the core and app folders are.


	if(file_exists(CORE_FOLDER . "/core.php")) {
		require(CORE_FOLDER . "/core.php"); // start the core
	} else {
		echo "<html><body><div style='font-family:arial, sans-serif;font-size:14px;color:#999;padding:12px;margin:40px auto;text-align:center;border:solid 1px #AAA;width:400px;'>";
		echo "Sorry, couldn't find the phpGenesis core.<br />";
		echo "Check <strong>config.php</strong> CORE_FOLDER setting. Currently set to<br />";
		echo "<pre style='font-size:11px;background-color:#EEE; padding:4px;'>" . CORE_FOLDER . "</pre>";
		echo "</div></body></html>";
	}
?>