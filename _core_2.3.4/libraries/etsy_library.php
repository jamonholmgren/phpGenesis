<?php
	/*==============================================================================
	 * ETSY API LIBRARY
	 * Provides lightweight read_only access to etsy data based on shop_name
	 * Designed and coded by Daniel Berkompas
	 * Copyright 2010 ClearSight Studio
	==============================================================================*/
	
	/* CONFIGURATION -------------------------------------------------------------*/
	
	$etsy_mode = settings('etsy', 'mode');
	define('ETSY_API_KEY', settings('etsy', "api_key"));
	globals('etsy', 'error_message', ''); //create an error message
	
	/* configure the etsy api url differently depending on whether this is a production or test etsy site */
	
	if($etsy_mode == "sandbox") {
		define('ETSY_API_URL', "http://sandbox.openapi.etsy.com/v2/");
	} elseif($etsy_mode == "production") {
		define('ETSY_API_URL', "http://openapi.etsy.com/v2");
	}
	/* ---------------------------------------------------------------------------*/
	
	/*==============================================================================
	 * API FUNCTIONS
	 * Used by the library for internal functions and etsy interaction.
	 * Since these functions deal with live data from etsy, developers should use
	 * the database functions listed below this section instead.
	==============================================================================*/
	
	/*actually pull data from etsy -----------------------------------------------*/
	if(!function_exists("etsy_access_api")) {
		function etsy_access_api($url) { //accesses etsy with a given url and returns a php array with the result
			$first_url = ETSY_API_URL . $url . "limit=100&api_key=". ETSY_API_KEY; //add the api key and api sandbox/production url
			$ch = curl_init($first_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response_body = curl_exec($ch);
	
			$result = json_decode($response_body, true);
	
			if($result['count'] > 100) { // if there are more than 100 records to return, make multiple etsy requests and return combined array
				
				for($i = 100; $i < ($result['count'] + 100); $i = $i + 100) { //offset queries by 100
					$next_url = ETSY_API_URL . $url . "limit=100&offset=$i&api_key=". ETSY_API_KEY;
	
					$ch = curl_init($next_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
	
					$next_result = json_decode($response, true);
					$result['results'] = array_merge($result['results'], $next_result['results']);
				}
	
			}
	
			return $result;
		}
	}
	/*----------------------------------------------------------------------------*/
	
	
	if(!function_exists("etsy_pull_shop")) {
		/* Pull a shop */
		function etsy_pull_shop($shop_name) {
			$url = "/public/shops/$shop_name?";
			$result = etsy_access_api($url);
	
			if(!is_array($result)) globals('etsy', 'error_message', 'Aw, snap!  Unable to retrieve shop information.');
			return $result;
		}
	}
	
	if(!function_exists("etsy_pull_sections")) {
		/* Pull all sections associated with a shop */
		function etsy_pull_sections($shop_name) {
			$url = "/public/shops/$shop_name/sections?include=Listings&";
			$result = etsy_access_api($url);
	
			if(!is_array($result)) globals('etsy', 'error_message', 'Aw, snap!  Unable to retrieve the sections for ' . $shop_name . "'s shop.");
			return $result;
		}
	}
	
	if(!function_exists("etsy_pull_listings_by_shopname")) {
		/* Pull listings for a shop */
		function etsy_pull_listings_by_shopname($shop_name = "", $include_images = true) {
	
			if($include_images) { //decide whether to include images
				$include = "includes=Images&";
				$include_i = ",Images";
			} else {
				$include = '';
			}
	
			if($shop_name != "") { //limit by shop name, if any.  Otherwise, return all listings in Etsy. (Limited to 25)
				$shop = "shops/" . $shop_name;
			} else {
				$shop = "";
			}
	
			if($section_id != "") {
	
				$url = "/public/sections/$section_id?includes=Listings:active&";
			}
	
			//call the api
			if(!isset($url)) $url = "/public/". $shop . "/listings/active?". $include;
			$result = etsy_access_api($url);
	
			if(!is_array($result)) globals('etsy', 'error_message', 'Aw, snap!  Unable to retrieve listings for ' . $shop_name . "'s shop.");
			return $result;
	
		}
	}
	
	if(!function_exists("etsy_pull_listings_by_sectionid")) {
		/* Pull all listings associated with a particular section */
		function etsy_pull_listings_by_sectionid($section_id, $limit_field = "", $include_images = true) {
			//going to have to use this in conjuction with listing images
			if($section_id != "") {
				if($limit_field != "") {
					$limit = "&fields=" . $limit_field;
				} else {
					$limit = "";
				}
	
				$url = "/public/sections/$section_id?includes=Listings:active$limit&";
				$result = etsy_access_api($url);
	
				if(!is_array($result)) globals('etsy', 'error_message', 'Aw, snap!  Unable to retrieve listings for ' . $shop_name . "'s shop using sections.");
				return $result;
			}
		}
	}
	
	if(!function_exists("etsy_listing_images")) {
		/* Pull images for a listing */
		function etsy_listing_images($listing_id) {
			$url = "/public/listings/$listing_id/images?";
			$result = etsy_access_api($url);
	
			if(!is_array($result)) globals('etsy', 'error_message', 'Aw, snap!  Unable to retrieve images for this listing.');
			return $result;
		}
	}
	
	
	/*==============================================================================
	 * ETSY DATABASE INTERACTION
	 * Should be used by the web application/developers rather than interacting
	 * directly with etsy
	==============================================================================*/
	
	if(!function_exists("etsy_empty_cache")) {
		/* Empty the database cache */
		function etsy_empty_cache($shop_name = "") {
			//configuration
			$shop_table = settings('etsy', 'shop_table_name');
			$section_table = settings('etsy', 'section_table_name');
			$listing_table = settings('etsy', 'listing_table_name');
	
			if($shop_name != "") { //if shop name, clear cache for that shop
				db_delete($section_table, 'shop_name', $shop_name); //delete the old sections
				db_delete($listing_table, 'shop_name', $shop_name); //delete the old listings
			} else { //else, delete everything
				db_query("
					DELETE $shop_table.*
					FROM $shop_table
				");
				db_query("
					DELETE $section_table.*
					FROM $section_table
				");
				db_query("
					DELETE $listing_table.*
					FROM $listing_table
				");
			}
		}
	}
	
	
	if(!function_exists("etsy_refresh_cache")) {
		function etsy_refresh_cache($shop_name = "") { /* IMPORTANT | PRIMARY FUNCTION - REFRESHES THE CACHE */
			//configuration
			$shop_table = settings('etsy', 'shop_table_name');
			$section_table = settings('etsy', 'section_table_name');
			$listing_table = settings('etsy', 'listing_table_name');
			$refresh_interval = settings("etsy", "refresh_interval");
			
			//get the shops to check for refreshing
			if($shop_name == "") {
				$shops = db_query_rows("
					SELECT *
					FROM $shop_table
				");
			} else { // if the shop name is specified, only refresh that shop
				$shops = db_query_rows("
					SELECT *
					FROM $shop_table
					WHERE shop_name = '$shop_name'
				");
			}
	
	
			//if the shop does not exist, create one
			if(!is_array($shops)) {
				unset($shops);
	
				$new_shop = array(
						"shop_name" => $shop_name
				);
				db_insert($shop_table, $new_shop);
	
				$shops = db_query_rows("
					SELECT *
					FROM $shop_table
					WHERE shop_name = '$shop_name'
				");
			}
			
			//loop through all of the shops, updating their content
			foreach($shops as $shop) {
	
				
				if($shop['last_refresh'] < strtotime("-" . $refresh_interval) || $shop['last_refresh'] == 0) { //if this shop needs to be refreshed
					
					//PRELIMINARY STEP: CLEAR THE DATABASE
					etsy_empty_cache($shop['shop_name']);
	
					//STEP 1: Refresh the shop
	
					$etsy_shop = etsy_pull_shop($shop['shop_name']);
	
					$shop_updated = array(
							"shop_id" => $etsy_shop['results'][0]['shop_id'],
							"shop_name" => $etsy_shop['results'][0]['shop_name'],
							"shop_json_data" => base64_encode(json_encode($etsy_shop['results'][0])),
							"last_refresh" => time()
					);
	
					db_update($shop_table, "WHERE shop_name = '$shop_name'", $shop_updated);
	
					//STEP 2: Refresh the sections
	
					$etsy_sections = etsy_pull_sections($shop_updated['shop_name']);
	
					$listing_section_ids = array(); //create an array of section ids to later associate listings with
	
					foreach($etsy_sections['results'] as $section) {
						//update listing_section_ids
						$section_listings = etsy_pull_listings_by_sectionid($section['shop_section_id']);
	
						foreach($section_listings['results'][0]["Listings"] as $section_listing) {
							$listing_section_ids[$section_listing['listing_id']] = $section['shop_section_id'];
						}
	
						//update the database
						$section_updated = array(
								"section_id" => $section['shop_section_id'],
								"section_title" => $section['title'],
								"section_json_data" => base64_encode(json_encode($section)),
								"shop_name" => $shop_updated['shop_name']
						);
						db_insert($section_table, $section_updated);
					}
	
					//STEP 3: Refresh the listings
	
					$etsy_listings = etsy_pull_listings_by_shopname($shop_updated['shop_name']);
	
					foreach($etsy_listings['results'] as $listing) {
						$listing_updated = array(
								"listing_id" => $listing['listing_id'],
								"listing_title" => $listing['title'],
								"section_id" => $listing_section_ids[$listing['listing_id']],
								"listing_json_data" => base64_encode(json_encode($listing)),
								"shop_name" => $shop_updated['shop_name']
						);
						db_insert($listing_table, $listing_updated);
					}
	
				}
			}
		}
	}
	
	if(!function_exists("etsy_get_shop")) {
		/* Pulls an associative array of a shop's information from the database */
		function etsy_get_shop($shop_name = "") {
			etsy_refresh_cache($shop_name);
	
			//configuration
			$shop_table = settings('etsy', 'shop_table_name');
			$section_table = settings('etsy', 'section_table_name');
			$listing_table = settings('etsy', 'listing_table_name');
	
			if($shop_name == "") {
				$shops = db_query_rows("
					SELECT *
					FROM $shop_table
				");
			} else {
				$shops = db_query_rows("
					SELECT *
					FROM $shop_table
					WHERE shop_name = '$shop_name'
				");
			}
	
			$shops_decoded = array();
			$shop_count = 0;
	
			foreach($shops as $key => $shop) {
				$shops_decoded[$shop_count] = $shop;
				$shops_decoded[$shop_count]['shop_json_data'] = json_decode(base64_decode($shops_decoded[$shop_count]['shop_json_data']), true);
				$shop_count++;
			}
	
			return $shops_decoded;
		}
	}
	
	if(!function_exists("etsy_get_section")) {
		/* Pulls an associative array of a section's information from the database */
		function etsy_get_section($shop_name, $section_title = "") {
			etsy_refresh_cache($shop_name);
			
			//configuration
			$shop_table = settings('etsy', 'shop_table_name');
			$section_table = settings('etsy', 'section_table_name');
			$listing_table = settings('etsy', 'listing_table_name');
	
	
			if($section_title == "") {
				$sections = db_query_rows("
					SELECT *
					FROM $section_table
					WHERE shop_name = '{$shop_name}'
					ORDER BY section_title ASC
				");
			} else {
				$sections = db_query_rows("
					SELECT *
					FROM $section_table
					WHERE shop_name = '{$shop_name}'
						AND section_title = '{$section_title}'
					ORDER BY section_title ASC
				");
			}
	
			$sections_decoded = array();
			$section_count = 0;
	
			foreach($sections as $key => $section) {
				$sections_decoded[$section_count] = $section;
				$sections_decoded[$section_count]['section_json_data'] = json_decode(base64_decode($sections_decoded[$section_count]['section_json_data']), true);
				$section_count++;
			}
	
			return $sections_decoded;
		}
	}
	
	if(!function_exists("etsy_get_listing")) {
		/* Pulls an associative array of a listing's information from the database */
		function etsy_get_listing($section_id, $listing_id = "") {
			etsy_refresh_cache($shop_name);
			
			//configuration
			$shop_table = settings('etsy', 'shop_table_name');
			$section_table = settings('etsy', 'section_table_name');
			$listing_table = settings('etsy', 'listing_table_name');
	
	
			if($listing_id == "") {
				$listings = db_query_rows("
					SELECT *
					FROM $listing_table
					WHERE section_id = '{$section_id}'
				");
			} else {
				$listings = db_query_rows("
					SELECT *
					FROM $listing_table
					WHERE section_id = '{$section_id}'
						AND listing_id = '{$listing_id}'
				");
			}
	
			$listings_decoded = array();
			$listing_count = 0;
	
			if (is_array($listings)) {
				foreach($listings as $key => $listing) {
					$listings_decoded[$listing_count] = $listing;
					$listings_decoded[$listing_count]['listing_json_data'] = json_decode(base64_decode($listings_decoded[$listing_count]['listing_json_data']), true);
					$listing_count++;
				}
			}
			
			return $listings_decoded;
		}
	}
	
?>
