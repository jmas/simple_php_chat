<?php

file_exists('config.php') AND require_once('config.php');

defined('DB_PATH') OR define('DB_PATH', 'mysql:dbname=dbname;host=127.0.0.1');
defined('DB_USERNAME') OR define('DB_USERNAME', 'root');
defined('DB_PASSWORD') OR define('DB_PASSWORD', 'password');
defined('SESSION_ID') OR define('SESSION_ID', '__globals');

/**
 * Get data from some $data array by $key.
 * @param $key string Key of data. Can be key.subkey
 * @param $defultValue mixed Default value of data if $key not founded
 * @param $data array|null Data array for search. If null it takes $_REQUEST
 * @return mixed Data of $data array by key
 */
function getData($key, $defaultValue=null, $data=null)
{
    if ($data === null) {
        $data = $_REQUEST;
    }

    if (strstr($key, '.') === false) {
        return empty($data[$key]) === false ? $data[$key]: $defaultValue;
    } else {
        $fieldNameSegments = explode('.', $key);
        $fieldValue = &$data;

        while ($fieldNameSegment = array_shift($fieldNameSegments)) {
            if (empty($fieldValue[$fieldNameSegment]) === false) {
                $fieldValue = &$fieldValue[$fieldNameSegment];
            } else {
                $fieldValue = $defaultValue;
                break;
            }
        }

        return $fieldValue;
    }

    return $defaultValue;
}

/**
 *
 */
function setSessionValue($key, $value)
{
    if (session_id() === '') {
        session_start();
    }

    $sessionId = SESSION_ID;

    if (empty($_SESSION[SESSION_ID]) === true) {
        $_SESSION[SESSION_ID] = array();
    }

    $_SESSION[SESSION_ID][$key] = $value;
}

/**
 *
 */
function getSessionValue($key)
{

    if (session_id() === '') {
        session_start();
    }

    if (empty($_SESSION[SESSION_ID]) === true) {
        $_SESSION[SESSION_ID] = array();
    }

    return getData($key, null, $_SESSION[SESSION_ID]);
}

/**
 *
 */
function deleteSessionKey($key)
{
    if (session_id() === '') {
        session_start();
    }

    unset( $_SESSION[SESSION_ID][$key] );
}

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
