<?php 
	/**
	 * PHPGENESIS PLUpload Plugin
	 *
	 * @package	PLUpload for PHPGenesis
	 * @category	Forms
	 * @author	Michael Berkompas
	 * @link	http://www.plupload.com
	 */

  if(!function_exists("load_plupload_files")) {
  	function load_plupload_files() {
  		register_css("plupload-styles", "http://api.devcsd.com/plupload/css/plupload.queue.css", "all", 2);
  		register_javascript("gears-init", "http://api.devcsd.com/plupload/js/gears_init.js", 2, false, true); 
  		register_javascript("browser-plus", "http://bp.yahooapis.com/2.4.21/browserplus-min.js", 3, false, true); 
  		register_javascript("plupload", "http://api.devcsd.com/plupload/js/plupload.min.js", 4, false, true); 
  		register_javascript("plupload-gears", "http://api.devcsd.com/plupload/js/plupload.gears.min.js", 5, false, true); 
  		register_javascript("plupload-silverlight", "http://api.devcsd.com/plupload/js/plupload.silverlight.min.js", 6, false, true); 
  		register_javascript("plupload-flash", "http://api.devcsd.com/plupload/js/plupload.flash.min.js", 7, false, true); 
  		register_javascript("plupload-browserplus", "http://api.devcsd.com/plupload/js/plupload.browserplus.min.js", 8, false, true); 
  		register_javascript("plupload-html5", "http://api.devcsd.com/plupload/js/plupload.html5.min.js", 9, false, true); 
  		register_javascript("plupload-queue-jquery", "http://api.devcsd.com/plupload/js/jquery.plupload.queue.min.js", 10, false, true); 
  	}
	}
	
	if(!function_exists("load_plupload_php")) {
	  function load_plupload_php($targetDir = '', $cleanupTargetDir = false, $maxFileAge = 3600) {
  		if($targetDir == '') $targetDir = UPLOADS_FOLDER . "/plupload";
  		define("PLUPLOAD_TARGET_DIR", $targetDir);
  		define("PLUPLOAD_CLEANUP_TARGET_DIR", $cleanupTargetDir);
  		define("PLUPLOAD_MAX_FILE_AGE", $maxFileAge);
		
  		// HTTP headers for no cache etc
  		header('Content-type: text/plain; charset=UTF-8');
  		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  		header("Cache-Control: no-store, no-cache, must-revalidate");
  		header("Cache-Control: post-check=0, pre-check=0", false);
  		header("Pragma: no-cache");

  		// Settings
  		$targetDir = PLUPLOAD_TARGET_DIR;
  		$cleanupTargetDir = PLUPLOAD_CLEANUP_TARGET_DIR; // Remove old files
  		$maxFileAge = PLUPLOAD_MAX_FILE_AGE; // Temp file age in seconds

  		// 5 minutes execution time
  		@set_time_limit(5 * 60);

  		// Uncomment this one to fake upload time
  		// usleep(5000);

  		// Get parameters
  		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
  		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
  		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

  		// Clean the fileName for security reasons
  		$fileName = preg_replace('/[^\w\._-]+/', '', $fileName);

  		// Make sure the fileName is unique but only if chunking is disabled
  		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
  			$ext = strrpos($fileName, '.');
  			$fileName_a = substr($fileName, 0, $ext);
  			$fileName_b = substr($fileName, $ext);

  			$count = 1;
  			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
  				$count++;

  			$fileName = $fileName_a . '_' . $count . $fileName_b;
  		}

  		// Create target dir
  		if (!file_exists($targetDir))
  			@mkdir($targetDir);

  		// Remove old temp files
  		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
  			while (($file = readdir($dir)) !== false) {
  				$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

  				// Remove temp files if they are older than the max age
  				if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
  					@unlink($filePath);
  			}

  			closedir($dir);
  		} else
  			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

  		// Look for the content type header
  		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
  			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

  		if (isset($_SERVER["CONTENT_TYPE"]))
  			$contentType = $_SERVER["CONTENT_TYPE"];

  		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
  		if (strpos($contentType, "multipart") !== false) {
  			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  				// Open temp file
  				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
  				if ($out) {
  					// Read binary input stream and append it to temp file
  					$in = fopen($_FILES['file']['tmp_name'], "rb");

  					if ($in) {
  						while ($buff = fread($in, 4096))
  							fwrite($out, $buff);
  					} else
  						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

  					fclose($out);
  					@unlink($_FILES['file']['tmp_name']);
  				} else
  					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
  			} else
  				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
  		} else {
  			// Open temp file
  			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
  			if ($out) {
  				// Read binary input stream and append it to temp file
  				$in = fopen("php://input", "rb");

  				if ($in) {
  					while ($buff = fread($in, 4096))
  						fwrite($out, $buff);
  				} else
  					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

  				fclose($out);
  			} else
  				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
  		}

  		// Return JSON-RPC response
  		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  	}
  }

?>