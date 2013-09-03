<?php

define('DB_PATH', 'mysql:dbname=chat;host=127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'password');

/**
 * Get PDO connection.
 */
function getDb()
{
	static $db;

	if ($db === null) {
		$db = new PDO(DB_PATH, DB_USERNAME, DB_PASSWORD);
	}

	return $db;
}
