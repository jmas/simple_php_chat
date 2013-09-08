<?php

require_once('global.php');

$lastMessageTime = empty($_GET['lastMessageTime']) ? null: $_GET['lastMessageTime'];

$error = false;

if ($lastMessageTime === null) {
	$query = '
		SELECT
			t.*,
			user.id AS user_id,
			user.photo AS user_photo,
			user.profile AS user_profile
		FROM
			(SELECT
				*,
				UNIX_TIMESTAMP(time) AS unixtime
			FROM
				message
			ORDER BY time DESC LIMIT 20) AS t
			LEFT JOIN
				user
			ON
				t.user_id = user.id
		ORDER BY t.time ASC
	';
} else {
	$query = '
		SELECT
			t.*,
			UNIX_TIMESTAMP(t.time) AS unixtime,
			user.id AS user_id,
			user.photo AS user_photo,
			user.profile AS user_profile
		FROM
				message AS t
			LEFT JOIN
				user
			ON
				t.user_id = user.id
		WHERE UNIX_TIMESTAMP(t.time) > :lastMessageTime LIMIT 10
	';
}

$db = getDb();

$sth = $db->prepare($query);

$sth->bindValue(':lastMessageTime', $lastMessageTime);

if ($sth->execute() === false) {
	$error = 'Can\'t select records from DB.';
}

$messages = $sth->fetchAll();

foreach ($messages as $i => $item) {
	$messages[$i]['content'] = htmlspecialchars($item['content']);
}

echo json_encode(array(
	'error' => $error,
	'messages' => $messages,
));