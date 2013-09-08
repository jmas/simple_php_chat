<?php

require_once('global.php');

$token = empty($_GET['token']) ? null: $_GET['token'];

function loginUser($token)
{
	$userId = getSessionValue('user.id');

	$url = 'https://ulogin.ru/token.php?token={TOKEN}';

	$jsonContent = file_get_contents(str_replace(
		'{TOKEN}',
		htmlspecialchars($token),
		$url
	));

	if ($jsonContent === false) {
		return 'Can\'t read user information by token.';
	}

	$data = json_decode($jsonContent, true);

	if ($data === false) {
		return 'Can\'t decode ulogin information.';
	}

	if (empty($data['error']) === false) {
		return $data['error'];
	}

	// assert('empty($data[\'first_name\']) === false
	// 	&& empty($data[\'identity\']) === false
	// 	&& empty($data[\'uid\']) === false
	// 	&& empty($data[\'network\']) === false
	// 	&& empty($data[\'profile\']) === false
	// 	&& empty($data[\'last_name\']) === false');

	$query = 'SELECT id, identity FROM user WHERE (uid = :uid AND network = :network) OR id = :id LIMIT 1';

	$db = getDb();

	$sth = $db->prepare($query);

	$sth->bindValue(':uid', $data['uid']);
	$sth->bindValue(':network', $data['network']);
	$sth->bindValue(':id', $userId);

	if ($sth->execute() === false) {
		return 'Can\'t read data from DB.';
	}

	$userData = $sth->fetch();

	if ($userData === false) {
		$query = '
			INSERT INTO user(
				first_name,
				identity,
				uid,
				network,
				profile,
				last_name,
				photo,
				photo_big
			) VALUES(
				:firstName,
				:identity,
				:uid,
				:network,
				:profile,
				:lastName,
				:photo,
				:photoBig
			)
		';

		$sth = $db->prepare($query);

		$sth->bindValue(':firstName', $data['first_name']);
		$sth->bindValue(':identity', $data['identity']);
		$sth->bindValue(':uid', $data['uid']);
		$sth->bindValue(':network', $data['network']);
		$sth->bindValue(':profile', $data['profile']);
		$sth->bindValue(':lastName', $data['last_name']);
		$sth->bindValue(':photo', $data['photo']);
		$sth->bindValue(':photoBig', $data['photo_big']);

		if ($sth->execute() === false) {
			return 'Can\'t write data to DB.';
		}

		if ($userId !== null) {
			$query = 'DELETE FROM user WHERE id = :id';

			$sth = $db->prepare($query);

			$sth->execute();
		}

		$userId = $db->lastInsertId();
	} else if (empty($userData['identity']) === true) {
		$query = '
			UPDATE
				user
			SET
				first_name = :firstName,
				identity   = :identity,
				uid        = :uid,
				network    = :network,
				profile    = :profile,
				last_name  = :lastName,
				photo      = :photo,
				photo_big  = :photoBig
		';

		$sth = $db->prepare($query);

		$sth->bindValue(':firstName', $data['first_name']);
		$sth->bindValue(':identity', $data['identity']);
		$sth->bindValue(':uid', $data['uid']);
		$sth->bindValue(':network', $data['network']);
		$sth->bindValue(':profile', $data['profile']);
		$sth->bindValue(':lastName', $data['last_name']);
		$sth->bindValue(':photo', $data['photo']);
		$sth->bindValue(':photoBig', $data['photo_big']);
	} else {
		$userId = $userData['id'];
	}

	setSessionValue('user', array(
		'id' => $userId,
	));

	return false;
}

echo json_encode(array(
	'error'=>loginUser($token),
));