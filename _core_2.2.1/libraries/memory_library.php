<?php
	
	/**
	 * Saves server starting memory usage, then loads each library and calculates how much
	 * resources each library uses.
	 *
	 * @return string
	 */
	if(!function_exists("memory_monitor")) {
		function memory_monitor() {
			$threshold = settings("memory", "threshold");
			$email = settings("memory", "email");
			$display = settings("memory", "display");

			$mem_peak = memory_get_peak_usage(false);
			$mem = memory_get_usage(false);

			$net_mem_peak = $mem_peak - $GLOBALS['memory']['start'];
			$net_mem = $mem - $GLOBALS['memory']['start'];

			$true_mem_peak = memory_get_peak_usage(true);
			$true_mem = memory_get_usage(true);
			
			$true_net_mem_peak = $true_mem_peak - $GLOBALS['memory']['true_start'];
			$true_net_mem = $true_mem - $GLOBALS['memory']['true_start'];
			
			if($mem_peak / 1024 > $threshold) {
				$message = "
					Actual Usage:<br />
					PHP: {$GLOBALS['memory']['start']} (" . (int)($GLOBALS['memory']['start'] / 1024) . "kb)<br />
					App Current: {$net_mem} (" . (int)($net_mem / 1024) . "kb).<br />
					App Peak: {$net_mem_peak} (" . (int)($net_mem_peak / 1024) . "kb).<br />
					---<br />
					Total peak memory usage {$mem_peak} (" . (int)($mem_peak / 1024) . "kb).<br />
					<br />
					---<br />
					<br />
					Allocation<br />
					PHP: {$GLOBALS['memory']['true_start']} (" . (int)($GLOBALS['memory']['true_start'] / 1024) . "kb)<br />
					App Current: {$true_net_mem} (" . (int)($true_net_mem / 1024) . "kb).<br />
					App Peak: {$true_net_mem_peak} (" . (int)($true_net_mem_peak / 1024) . "kb).<br />
					---<br />
					Total Peak Allocation: {$true_mem_peak} (" . (int)($true_mem_peak / 1024) . "kb).
				";
				if($display) {
					echo "<!-- " . strip_tags($message) . " --> ";
				}
				if($email) {
					load_library("email");
					email_send($email, "High memory load on " . BASE_URL . "", 
						$message . "<br />
							Site: " . BASE_URL . "<br />
							Page: " . BASE_URL . "" . $_SERVER['REQUEST_URI'] . "<br />
							Core: " . CORE_FOLDER . "",
						"memory+monitor@phpgenesis.com"
					);
				}
			}
		}
	}


?>
