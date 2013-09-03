<?php

require_once('global.php');
require_once('IpToColorName.php');

$messageContent = empty($_POST['content']) ? null: $_POST['content'];

$ip = $_SERVER['REMOTE_ADDR'];

$ipToColorName = new IpToColorName($ip);

$userColor = $ipToColorName->getColorHexValue('');
$userContrastColor = getContrastYIQ($userColor);

$query = 'INSERT INTO message(content, color, contrast_color) VALUES(:messageContent, :userColor, :userContrastColor)';

$error = false;

$db = getDb();

$sth = $db->prepare($query);

$sth->bindValue(':messageContent', $messageContent);
$sth->bindValue(':userColor', $userColor);
$sth->bindValue(':userContrastColor', $userContrastColor);

if ($sth->execute() === false) {
	$error = $sth->errorInfo();
}

echo json_encode(array(
	'error' => $error,
));
