<?php

include_once('commento.php');

define('COMMENTO_HAS_CONFIG_FILE', false);
define('COMMENTO_DATA_PATH', dirname($_SERVER['DOCUMENT_ROOT']).'/php-commento-data');

Commento\loadConfiguration();

$urlParameter = parse_url(substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['PHP_SELF']).'/')));

list($result, $resultType) = Commento\run($urlParameter['path'], $_REQUEST);

Commento\render($result, $resultType);
