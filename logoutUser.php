<?php

require_once('global.php');

deleteSessionKey('user');

echo json_encode(array(
	'error' => false,
));