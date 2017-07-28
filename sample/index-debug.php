<?php

// error_reporting(E_ALL);
// ini_set('display_errors', true);
// ini_set('display_startup_errors', true);

include_once('commento.php');

// debug('_REQUEST', $_REQUEST);
// debug('_SERVER', $_SERVER);


define('COMMENTO_HAS_CONFIG_FILE', false);
define('COMMENTO_DATA_PATH', dirname($_SERVER['DOCUMENT_ROOT']).'/php-commento-data');

Commento\loadConfiguration();

// if (!Commento\loadConfiguration()) {
//     // $resultType = 'html';
//     // $result = "<p>Cannot load the configuration file.</p>";
// }


$urlParameter = parse_url(substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['PHP_SELF']).'/')));

list($result, $resultType) = Commento\run($urlParameter['path'], $_REQUEST);

Commento\render($result, $resultType);
