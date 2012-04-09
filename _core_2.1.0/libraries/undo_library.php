<?php if(!defined("CORE_FOLDER")) die("Hacking attempt logged from IP " . $_SERVER['REMOTE_ADDR']);
//	phpGenesis
//	Copyright (c) 2010. All Rights Reserved. This is commercial software.
//	No part of it may be distributed, sold, or otherwise used without the
//  express written consent of the copyright owners.
//
//  Copyright Owners:
//		Jamon Holmgren - jamon@jamonholmgren.com
//		Tim Santeford - tsantef@gmail.com
	/*
		MySQL Create:
			NOT DEFINED YET - USE BELOW AS A GUIDE AND PASTE IT HERE

		MSSQL Create:
			CREATE TABLE [dbo].[undo](
				[id] [int] IDENTITY(1,1) NOT NULL,
				[table_name] [varchar](255) NOT NULL,
				[row_id] [int] NOT NULL,
				[row_data] [text] NOT NULL,
				[created] [int] NOT NULL,
			 CONSTRAINT [PK_tblUndo] PRIMARY KEY CLUSTERED
				( [id] ASC ) WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
			) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

	*/


	// Undo db tablename
	if(!defined("UNDO_TABLENAME")) define("UNDO_TABLENAME", "undo");

	// Backup types
	define("UNDO_FULL", 0);
	define("UNDO_DIFFERENTIAL", 1);
	define("UNDO_INCREMENTAL", 2);

	/**
	 *	Stores undo information in a table. Define "UNDO_TABLENAME" if you want a different table name for the
	 *	undo information.
	 *
	 *	$new_data is currently not used and not required, but will be useful later for differential/incremental backups.
	 *
	 *	$backup_type is also currently not used.
	 *
	 *	@return boolean
	**/
	if(!function_exists("undo_store")) {
		function undo_store($table_name, $row_id, $old_data, $new_data = NULL, $backup_type = UNDO_FULL) {
			load_library("db");

			$undo_data['table_name'] = $table_name;
			$undo_data['row_id'] = $row_id;
			$undo_data['created'] = time();
			$undo_data['row_data'] = serialize($old_data);

			return db_insert(UNDO_TABLENAME, $undo_data);
		}
	}

	/**
	 *	Returns an array with all undo restore points, if any. Returns false if none.
	 *
	 *	Only returns id and create date/time.
	 *
	 *	@return array
	**/
	if(!function_exists("undo_list")) {
		function undo_list($table_name, $row_id, $timestamp = NULL) {
			load_library("db");
			if(!$timestamp) $timestamp = time();

			$undo_list = db_query_rows("
				SELECT id, created
				FROM " . UNDO_TABLENAME . "
				WHERE row_id = {$row_id}
					AND created <= {$timestamp}
				ORDER BY created DESC
			");

			return $undo_list;
		}
	}

	/**
	 *	Returns an array with the last undo restore point, if any. Returns false if none.
	 *
	 *	Only returns id and create date/time.
	 *
	 *	@return array
	**/
	if(!function_exists("undo_get_last")) {
		function undo_get_last($table_name, $row_id, $timestamp = NULL) {
			load_library("db");
			if(!$timestamp) $timestamp = time();

			$undo_list = db_query_row("
				SELECT id, created
				FROM " . UNDO_TABLENAME . "
				WHERE row_id = {$row_id}
					AND created <= {$timestamp}
				ORDER BY created DESC
			");

			return $undo_list;
		}
	}

	/**
	 *	Returns the most recent backup based on $timestamp (defaults to now) or an undo id if given for the first argument.
	 *
	 *	Doesn't actually update the row, but returns the data so the app can. It also deletes the undo
	 *	unless $delete_after is set to false.
	 *
	 *	@return array
	**/
	if(!function_exists("undo_restore")) {
		function undo_restore($table_name, $row_id = NULL, $timestamp = NULL, $delete_after = TRUE) {
			load_library("db");

			if($row_id == NULL && is_numeric($table_name) && (int)$table_name > 0) {
				$undo_id = (int)$table_name;
				$undo_data = db_get_row(UNDO_TABLENAME, "id", $undo_id);
			} else {
				if($timestamp == NULL) $timestamp = time();
				$undo_data = db_query_row("
					SELECT id, row_data FROM " . UNDO_TABLENAME . "
					WHERE row_id = {$row_id} AND created <= {$timestamp}
					ORDER BY created DESC
				");
			}

			if(is_array($undo_data)) {
				if($delete_after) db_delete(UNDO_TABLENAME, "id", $undo_data['id']);
				return unserialize($undo_data['row_data']);
			}
			return false;
		}
	}

	/**
	 *	Returns a backup for previewing purposes. Can take either a timestamp or an undo ID.
	 *
	 *	@return array
	**/
	if(!function_exists("undo_preview")) {
		function undo_preview($table_name, $row_id = NULL, $timestamp = NULL) {
			return undo_restore($table_name, $row_id, $timestamp, FALSE);
		}
	}
	

?>