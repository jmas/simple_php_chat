<?php

file_exists('config.php') AND require_once('config.php');

defined('DB_PATH') OR define('DB_PATH', 'mysql:dbname=dbname;host=127.0.0.1');
defined('DB_USERNAME') OR define('DB_USERNAME', 'root');
defined('DB_PASSWORD') OR define('DB_PASSWORD', 'password');

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

/**
 *
 */
function getUserColor($username)
{
        $range = COL_MAX_AVG - COL_MIN_AVG;
        $factor = $range / 256;
        $offset = COL_MIN_AVG;

        $base_hash = substr(md5($username), 0, 6);
        $b_R = hexdec(substr($base_hash,0,2));
        $b_G = hexdec(substr($base_hash,2,2));
        $b_B = hexdec(substr($base_hash,4,2));

        $f_R = floor((floor($b_R * $factor) + $offset) / COL_STEP) * COL_STEP;
        $f_G = floor((floor($b_G * $factor) + $offset) / COL_STEP) * COL_STEP;
        $f_B = floor((floor($b_B * $factor) + $offset) / COL_STEP) * COL_STEP;

        return sprintf('%02x%02x%02x', $f_R, $f_G, $f_B);
}

/**
 *
 */
function getContrastYIQ($hexcolor)
{
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r*299)+($g*587)+($b*114))/1000;
	return ($yiq >= 128) ? '000000' : 'ffffff';
}
