<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

// debug('_REQUEST', $_REQUEST);
// debug('_SERVER', $_SERVER);

include_once('src/commento.php');

if (!Commento\loadConfiguration()) {
    // $resultType = 'html';
    // $result = "<p>Cannot load the configuration file.</p>";
}

$resultType = 'json';

$urlParameter = parse_url(substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['PHP_SELF']).'/')));
// debug('urlParameter', $urlParameter);

switch($urlParameter['path']) {
    case 'get':
        $comments = [];
        $message = '';
        if (array_key_exists('url', $_REQUEST)) {
            $comments = array_values(Commento\getComments($_REQUEST['url']));
        } else {
            $message = 'no url defined';
        }
        $result = [
            'success' => true,
            'message' => $message,
            'comments' => $comments,
        ];
    break;
    case 'create':
        // TODO: all html escaped; formatting uses markdown (on create or on get?)
        $success = false;
        if (array_key_exists('url', $_REQUEST)
         && array_key_exists('comment', $_REQUEST)
         && array_key_exists('name', $_REQUEST)
         && array_key_exists('parent', $_REQUEST)
        ) {
            $success = Commento\addComment(
                $_REQUEST['url'],
                [
                    'url' => $_REQUEST['url'],
                    'comment' => $_REQUEST['comment'],
                    'name' => $_REQUEST['name'],
                    'parent' => $_REQUEST['parent'],
                ]
            );
        }
        $result = [
            'success' => $success,
            'message' => null,
            'comments' => [],
        ];
    break;
    case 'install':
        $resultType = 'html';
        Commento\install();
        $result = "<p>Installation successful</p>";
    break;
    default:
        $result = [
            'success' => false,
            'message' =>'path not found',
        ];
}

if ($resultType === 'json') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo(json_encode($result));
} elseif ($resultType === 'html') {
    // TODO: add the html5 document structure around body
    echo($result);
}
