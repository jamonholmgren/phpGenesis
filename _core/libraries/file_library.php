<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
/**
 *	File Library
 *	
 *	Various file related functions
 *	
 *	phpGenesis by Jamon Holmgren and Tim Santeford
 *
 *	@todo Add more functionality
 *	@package phpGenesis
 */

// file_library
// TO-DO
//	See Above
	
	/**
	 *	Returns the size of the given folder in kilobytes (k) or megabytes (m) (default)
	 *
	 *	Works in Linux or Windows environments.
	 *	
	 *	@return int
	 */
	if(!function_exists("folder_size")) {
		function folder_size($folder, $unit = "m") {
			if(in_str("WIN", PHP_OS)) {
				$folder = str_replace("/", "\\", $folder);
				$output = shell_exec("dir {$folder} /s /w /-c"); // all subdirectories
				$lines = explode("\n", $output);
				$lines = array_reverse($lines); // start at the end.
				foreach($lines as $line) {
					if(in_str("File(s)", $line)) {
						$parts = explode("File(s)", $line);
						$size = trim(str_replace("bytes", "", array_last($parts))) / 1024 / 1024; // get in MB
						break;
					}
				}
			} else {
				$output = shell_exec("du -s -{$unit} {$folder}");
				$size = $output; // pretty easy here
			}
			return (int)$size;
		}
	}
	
	/**
	 * file_get_csv()
	 *
	 * Gets a CSV file and returns a multidimensional array. $delim allows you to specify what separates columns,
	 * so for tab-delimited use '\t'.
	 *
	 */
		if(!function_exists("file_get_csv")) {
			function file_get_csv($filename, $delim = ",", $useheaders = FALSE) {
				if(file_exists($filename)) {
					if($fh = fopen($filename, "r")) {
						while ($data = fgetcsv($fh, 3000, $delim)) {
							$num = count($data);
							$row += 1;
							for($c = 0; $c < $num; $c += 1) {
							if($useheaders === TRUE) {
								if($row <= 1) {
									$headers[$c] = $data[$c];
								}
								$key = $headers[$c];
							} else {
								$key = $c;
							}
							$data_array[$row][$key] = $data[$c];
						 }
					 }
					 fclose($fh);
					 if(is_array($data_array)) return $data_array;
				 }
			 }
			 return NULL;
		 }
	 }

	/**
	 *	file_get_array() by Jamon Holmgren. Exclude files by putting them in the $exclude
	 * string separated by pipes ('|'). Returns an array with filenames as strings.
	 *
	 * @return array
	**/
	function file_get_array($path, $exclude = ".|..", $recursive = false) {
		$path = rtrim($path, "/") . "/";
		$folder_handle = opendir($path);
		$exclude_array = explode("|", $exclude);
		$result = array();
		while(false !== ($filename = readdir($folder_handle))) {
			if(!in_array(strtolower($filename), $exclude_array)) {
				if(is_dir($path . $filename . "/")) {
				  if($recursive) $result[] = file_array($path, $exclude, true);
				} else {
				  $result[] = $filename;
				}
			}
		}
		return $result;
	}


	/** 
	 * file_scan_folder( directory to scan, filter, user_function )
	 * expects path to directory and optional an extension to filter
	 * and a user-defined function
	 * 
	 * to use this function to get all files and directories in an array, write:
	 * $filestructure = scan_directory_recursively('path/to/directory');
	 *
	 * to use this function to scan a directory and filter the results, write:
	 * $fileselection = scan_directory_recursively('directory', 'extension');
	 *
	 * to run a function on every file that this finds, write:
	 * $fileselection = scan_directory_recursively('directory', 'extension', 'function_to_run'
	 *
	 * NOTE: by returning false, your user-defined function will halt execution for the rest of
	 * the current folder. return true to continue.
	 *
	 * @param string $directory
	 * @param string $filter
	 * @param string $user_function
	 * @return array
	 */
	function file_scan_folder($directory, $filter = FALSE, $user_function = false) {
		$directory = rtrim($directory, "/");
		if(!file_exists($directory) || !is_dir($directory)) {
			return FALSE;
		} elseif(is_readable($directory)) {
			$directory_list = opendir($directory);
			while (FALSE !== ($file = readdir($directory_list))) {
				if($file != '.' && $file != '..') {
					$path = $directory.'/'.$file;
					if(is_readable($path)) {
						$subdirectories = explode('/',$path);
						if(is_dir($path)) {
							$recur_result = file_scan_folder($path, $filter, $user_function);
							if($recur_result === false) return false;
							$directory_tree[] = array(
								'path'    => $path,
								'name'    => end($subdirectories),
								'kind'    => 'directory',
								'content' => $recur_result);
						} elseif(is_file($path)) {
							$extension = end(explode('.',end($subdirectories)));
							if($filter === FALSE || $filter == $extension) {
								$file_details = array(
									'path'      => $path,
									'name'      => end($subdirectories),
									'extension' => $extension,
									'size'      => filesize($path),
									'kind'      => 'file');
								$directory_tree[] = $file_details;
								if($user_function) {
									if($user_function($file_details) === false) return false;
								}
							}
						}
					}
				}
			} // endwhile
			closedir($directory_list);
			return $directory_tree;
		} else {
			return FALSE;
		}
	} // end file_scan_folder

?>