<?php

require_once('global.php');

$messageContent = empty($_POST['content']) ? null: $_POST['content'];

$query = 'INSERT INTO message(content) VALUES(:messageContent)';

$error = false;

$db = getDb();

$sth = $db->prepare($query);

$sth->bindValue(':messageContent', $messageContent);

if ($sth->execute() === false) {
	$error = $sth->errorInfo();
}

echo json_encode(array(
	'error' => $error,
));
