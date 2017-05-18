<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

// debug('_REQUEST', $_REQUEST);
// debug('_SERVER', $_SERVER);

include_once('src/commento.php');

if (!loadConfiguration()) {
    // $resultType = 'html';
    // $result = "<p>Cannot load the configuration file.</p>";
}

$resultType = 'json';

$urlParameter = parse_url(substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER['PHP_SELF']).'/')));
// debug('urlParameter', $urlParameter);

if ($urlParameter['path'] === 'get') {
    $comments = [];
    $message = '';
    if (array_key_exists('url', $_REQUEST)) {
        $comments = getComments($_REQUEST['url']);
    } else {
        $message = 'no url defined';
    }
    $result = [
        'success' => true,
        'message' => $message,
        'comments' => $comments,
    ];
} elseif ($urlParameter['path'] === 'create') {
    // TODO: all html escaped; formatting uses markdown (on create or on get?)
} elseif ($urlParameter['path'] === 'install') {
    $resultType = 'html';
    install();
    $result = "<p>Installation successful</p>";
} else {
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
