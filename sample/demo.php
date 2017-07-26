<?php

include_once('commento.php');

define('COMMENTO_HAS_CONFIG_FILE', false);
define('COMMENTO_DATA_PATH', dirname($_SERVER['DOCUMENT_ROOT']).'/php-commento-demo-data');

Commento\loadConfiguration();

define('COMMENTO_DATA_DEMO_PATH', COMMENTO_DATA_PATH.'/demo.json');

function deleteAfter30Minutes()
{
    if ((file_exists(COMMENTO_DATA_DEMO_PATH) && is_writable(COMMENTO_DATA_DEMO_PATH)) || is_writable(COMMENTO_DATA_PATH)) {
        if ($demo = json_decode(file_get_contents(COMMENTO_DATA_DEMO_PATH), true)) {
            if (((time() - $demo['creation']) / 60) > 30) {
                Commento\clear('php-commento-demo-data');
                Commento\install();
                $demo = ['creation' => time()];
            }
        } else {
            $demo = ['creation' => time()];
        }
        file_put_contents(COMMENTO_DATA_DEMO_PATH, json_encode($demo));
    } else {
        die(COMMENTO_DATA_DEMO_PATH.' does not exist or is not writable');
    }
}

deleteAfter30Minutes();

$urlParameter = parse_url(substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['PHP_SELF']).'/')));

list($result, $resultType) = Commento\run($urlParameter['path'], $_REQUEST);

Commento\render($result, $resultType);
