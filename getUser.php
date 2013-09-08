<?php

require_once('global.php');

$error = false;

$id = empty($_GET['id']) ? null: $_GET['id'];

if ($id === null) {
	$id = getSessionValue('user.id');
}

function getUser($id)
{
	$query = '
		SELECT
			*
		FROM
			user
		WHERE
			id = :id
	';

	$db = getDb();

	$sth = $db->prepare($query);

	$sth->bindValue(':id', $id);

	if ($sth->execute() === false) {
		throw new Exception('Can\' read from DB.');
	}

	$data = $sth->fetch();

	if ($data === false) {
		throw new Exception('User not found.');
	}

	return $data;
}

$userData = null;

if ($id !== null) {
	try {
		$userData = getUser($id);
	} catch (Exception $e) {
		$error = $e->getMessage();
	}
} else {
	$error = 'User not logged.';
}

echo json_encode(array(
	'error'=>$error,
	'user'=>$userData,
));