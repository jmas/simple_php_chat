<?php

require_once('global.php');

deleteSessionKey('user');

$db = getDb();

$query = '
	INSERT INTO user(
		first_name
	) VALUES(
		"Guest"
	)
';

$sth = $db->prepare($query);
$sth->execute();

$userId = $db->lastInsertId();

setSessionValue('user', array(
	'id' => $userId,
));

echo json_encode(array(
	'error' => false,
));