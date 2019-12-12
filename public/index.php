<?php

require realpath(__DIR__ . '/../src/vendor/autoload.php');
require realpath(__DIR__ . '/../src/classes/cache.php');
require realpath(__DIR__ . '/../src/classes/geocode.php');
require realpath(__DIR__ . '/../src/classes/instagram.php');
require realpath(__DIR__ . '/../src/includes/common.php');

/* Begin routing */
require realpath(__DIR__ . '/../src/includes/endpoints-cache.php');
require realpath(__DIR__ . '/../src/includes/endpoints-geocode.php');

$match = $app->match();

// Call closure or throw 404 status
if( $match && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] );
} else {
	// no route was matched
	header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}

/* End routing */