<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2009. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the 
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com

// image_library last edited 	10/19/2010 by Michael Berkompas
// TO-DO
//	Add more functionality eventually

	if(!extension_loaded('gd')) { die("The GD extension is not loaded!"); } 

	function image_set_transparency($type) {
		switch ($type) {
			case "png":
				// turning off alpha blending (to ensure alpha channel information is preserved, rather than removed (blending with the rest of the image in the form of black))
				imagealphablending($simage, false);

				// turning on alpha channel information saving (to ensure the full range of transparency is preserved)
				imagesavealpha($simage, true);
				
				// integer representation of the color black (rgb: 0,0,0, Transparancy: 0-127)
				$background = imagecolorallocate($simage, 255, 255, 255, 127);
				// removing the black from the placeholder
				// imagecolortransparent($simage, $background);



				break;
			case "gif":
				// integer representation of the color black (rgb: 0,0,0)
				$background = imagecolorallocate($simage, 0, 0, 0);
				// removing the black from the placeholder
				imagecolortransparent($simage, $background);

				break;
		}
	}

	/**
	 * Image resizer utility. Accepts GIF, JPEG, or PNGs and resizes to GIF, JPEG, or PNG.
	 * Types include scale (no cropping) , crop (fills max_width/max_height), confine (scales only if size is greater then the max sizes)
	 *
	 * @return boolean
	 */
	function image_resize($source, $dest, $max_width, $max_height, $type='scale', $dest_type = "jpg") {		
		if(!file_exists($source)) return NULL;
		list($orig_width, $orig_height, $orig_type) = getimagesize($source);
		$src = false;
		if($orig_type == 1) $src = imagecreatefromgif($source);
		if($orig_type == 2) $src = imagecreatefromjpeg($source);
		if($orig_type == 3) $src = imagecreatefrompng($source);
		if(!$src) die("Not a JPG, PNG, or GIF");
		
		if ($type=='scale') {
			$scale_width = $max_width / $orig_width;
			$scale_height = $max_height / $orig_height;
			
			$scale = $scale_width < $scale_height ? $scale_width : $scale_height;		
			$new_width = round($orig_width * $scale);
			$new_height = round($orig_height * $scale);			
			
			$tmp = imagecreatetruecolor($new_width, $new_height);
			
			image_set_transparency($dest_type);
			imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
		} elseif($type == "crop") {
			$scale_width = $max_width / $orig_width;
			$scale_height = $max_height / $orig_height;
			
			$scale = $scale_width > $scale_height ? $scale_width : $scale_height;		
			$new_width = round($orig_width * $scale);
			$new_height = round($orig_height * $scale);
			
			$s_y = floor(($new_height - $max_height) / 2) * -1;
			$s_x = floor(($new_width - $max_width) / 2) * -1;		
			
			$tmp = imagecreatetruecolor($max_width, $max_height);			
			
			image_set_transparency($dest_type);
			imagecopyresampled($tmp, $src, $s_x, $s_y, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
		} elseif($type == "confine") {
			if ($orig_width > $max_width || $orig_height > $max_height) {
				$scale_width = $max_width / $orig_width;
				$scale_height = $max_height / $orig_height;

				$scale = $scale_width > $scale_height ? $scale_width : $scale_height;
				$new_width = round($orig_width * $scale);
				$new_height = round($orig_height * $scale);

				$s_y = floor(($new_height - $max_height) / 2) * -1;
				$s_x = floor(($new_width - $max_width) / 2) * -1;

				$tmp = imagecreatetruecolor($max_width, $max_height);
				
				image_set_transparency($dest_type);
				imagecopyresampled($tmp, $src, $s_x, $s_y, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
			} else {
				rename($source, $dest);
				return;
			}
		} else {
			die("image_resize doesn't recognize" . strip_tags($type));
		}
		
		if(file_exists($dest)) unlink($dest);
		
		switch($dest_type) {
			case "png":	return imagepng($tmp, $dest, 80); break;
			case "gif":	return imagegif($tmp, $dest, 80); break;
			default:	return imagejpeg($tmp, $dest, 80); break;
		}
		return NULL;
	} 

?>