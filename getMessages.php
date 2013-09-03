<?php

require_once('global.php');

$lastMessageTime = empty($_GET['lastMessageTime']) ? null: $_GET['lastMessageTime'];

$error = false;

if ($lastMessageTime === null) {
	$query = 'SELECT t.* FROM (SELECT *, UNIX_TIMESTAMP(time) AS unixtime FROM message ORDER BY time DESC LIMIT 20) AS t ORDER BY t.time ASC';
} else {
	$query = 'SELECT *, UNIX_TIMESTAMP(time) AS unixtime FROM message WHERE UNIX_TIMESTAMP(time) > :lastMessageTime LIMIT 10';
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