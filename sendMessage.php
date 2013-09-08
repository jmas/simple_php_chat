<?php

require_once('global.php');
require_once('IpToColorName.php');

$messageContent = isset($_POST['content']) === false && strlen($_POST['content']) > 0 ? null: (string) $_POST['content'];

function insertMessage($messageContent)
{
	$messageContent = trim($messageContent);

	if (strlen($messageContent) === 0) {
		return 'Message can\'t be empty.';
	}

	$ip = $_SERVER['REMOTE_ADDR'];

	// Get color of IP address
	$ipToColorName = new IpToColorName($ip);

	$userColor = $ipToColorName->getColorHexValue('');
	$userContrastColor = getContrastYIQ($userColor);

	$userId = getSessionValue('user.id');
	$userId = $userId ? $userId: '0';

	$query = '
		INSERT INTO message(
			content,
			color,
			contrast_color,
			user_id
		) VALUES (
			:messageContent,
			:userColor,
			:userContrastColor,
			:userId
		)
	';

	$error = false;

	$db = getDb();

	$sth = $db->prepare($query);

	$sth->bindValue(':messageContent', $messageContent, PDO::PARAM_STR);
	$sth->bindValue(':userColor', $userColor, PDO::PARAM_STR);
	$sth->bindValue(':userContrastColor', $userContrastColor, PDO::PARAM_STR);
	$sth->bindValue(':userId', $userId, PDO::PARAM_STR);

	if ($sth->execute() === false) {
		$error = $sth->errorInfo();
	}

	return $error;
}

echo json_encode(array(
	'error' => insertMessage($messageContent),
));
