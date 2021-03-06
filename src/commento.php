<?php

namespace Commento;

if (!function_exists('debug')) {
    function debug($label, $value)
    {
        print_r("<pre>$label:\n".htmlspecialchars(print_r($value, 1))."</pre>\n");
    }
}

define('COMMENTO_PATH', dirname($_SERVER['SCRIPT_FILENAME']));

/**
 * @brief look for a php-commento-config.php file in a parent directory and load it. If COMMENTO_CONFIG_PATH is defined in the calling script, use that path (mostly for testing purposes)
 */
function loadConfiguration()
{
    $result = false;
    if (!defined('COMMENTO_HAS_CONFIG_FILE') || COMMENTO_HAS_CONFIG_FILE) {
        if (defined('COMMENTO_CONFIG_PATH')) {
            if (file_exists(COMMENTO_CONFIG_PATH)) {
                include_once(COMMENTO_CONFIG_PATH);
                $result = true;
            }
        } else {
            $configPath = COMMENTO_PATH;
            // TODO: probably not windows compatible
            while ($configPath !== '/') {
                $configPath = $configPath.'/php-commento-config.php';
                if (file_exists($configPath)) {
                    include_once($configPath);
                    $result = true;
                    break;
                }
                $configPath = dirname(dirname($configPath));
            }
        }
    }

    // set the default configs (these are the constants you can set in the php-comment-config.php file)
    defined('COMMENTO_DATA_PATH') || define('COMMENTO_DATA_PATH', COMMENTO_PATH.'/data');
    defined('COMMENTO_DATA_URL_PATH') || define('COMMENTO_DATA_URL_PATH', COMMENTO_DATA_PATH.'/url.json');
    defined('COMMENTO_DATA_COMMENTS_PATH') || define('COMMENTO_DATA_COMMENTS_PATH', COMMENTO_DATA_PATH.'/comments');

    return $result;
}

/**
 * @return array(string, string)
 */
function run($path, $request)
{
    $result = '';
    $resultType = 'json';

    switch($path) {
        case 'get':
            list($result, $resultType) = routeGet($request);
        break;
        case 'create':
            list($result, $resultType) = routeCreate($request);
        break;
        case 'install':
            $resultType = 'html';
            install();
            $result = "<p>Installation successful</p>";
        break;
        default:
            if (!headers_sent()) {
                header("HTTP/1.0 404 Not Found");
            }
            $result = [
                'success' => false,
                'message' =>'path not found',
            ];
    }
    return array($result, $resultType);
}

function render($result, $resultType)
{
    if ($resultType === 'json') {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo(json_encode($result));
    } elseif ($resultType === 'html') {
        // TODO: add the html5 document structure around body
        echo($result);
    }
}

function routeGet($request)
{
    $comments = [];
    $message = '';
    if (array_key_exists('url', $request)) {
        $comments = array_values(getComments($request['url']));
    } else {
        $message = 'no url defined';
    }
    $result = [
        'success' => true,
        'message' => $message,
        'comments' => $comments,
    ];
    return array($result, 'json');
}

function routeCreate($request)
{
    $success = false;
    if (array_key_exists('url', $request)
     && array_key_exists('comment', $request)
     && array_key_exists('name', $request)
     && array_key_exists('parent', $request)
    ) {
        $success = addComment(
            $request['url'],
            [
                'url' => $request['url'],
                'comment' => $request['comment'],
                'name' => $request['name'],
                'parent' => $request['parent'],
            ]
        );
    }
    $result = [
        'success' => $success,
        'message' => null,
        'comments' => [],
    ];
}

function createCommentsFile($filename)
{
    $item = [
        'maxId' => 0,
        'comments' => [],
    ];
    file_put_contents(COMMENTO_DATA_COMMENTS_PATH.'/'.$filename, json_encode($item));
}

/**
 * @brief given the url, returns the name of the file containing the conments. if the file does not exist yet, it gets created.
 * @param <string> $url the page sending the request
 * @return <string> the json file with the list of comments
 * TODO:
 * - maybe we can use the url with all / and . stripped
 * - use the same $url for the same origin (http[s], rtrim(/)
 *   ask on stackoverflow if this is a good way.
 * $url = 'http://ideale.ch/blah/blah.php';
 * $urlTokens = parse_url($url);
 * print_r($urlTokens);
 * $urlPath = implode('_', array_filter(explode('/', $urlTokens['path'])));
 * print_r($urlTokens['host'].($urlPath !== '' ? '_'.$urlPath : ''));
 */
function getCommentsFilename($url)
{
    $result = null;

    $urlList = json_decode(file_get_contents(COMMENTO_DATA_URL_PATH), true);
    if (array_key_exists($url, $urlList)) {
        $result = $urlList[$url];
    } else {
        $result = basename(tempnam(COMMENTO_DATA_COMMENTS_PATH, 'commento-'));
        createCommentsFile($result);
        $urlList[$url] = $result;
        file_put_contents(COMMENTO_DATA_URL_PATH, json_encode($urlList));
    }
    return $result;
}

/**
 * @param <string> $url the page sending the request
 * @return <string> the content of the json file as a string
 */
function getCommentStore($url)
{
    return json_decode(file_get_contents(COMMENTO_DATA_COMMENTS_PATH.'/'.getCommentsFilename($url)),true);
}

/**
 * sample hook
 *
 * @param function $event lambda accepting an array as parameter
 *      and returning an array.
 */
function eventGetCommentsFilter($event = null)
{
    static $getCommentsFilter = [];
    if (isset($event) && is_callable($event)) {
        $getCommentsFilter[] = $event;
    } else {
        return $getCommentsFilter;
    }
}

function getComments($url)
{
    $result = getCommentStore($url)['comments'];
    // First bits for a hooks system
    // Commento\eventGetCommentsFilter(function($a) {return "---&gt; ".$a;});
    foreach (eventGetCommentsFilter() as $e) {$result = array_map($e, $result);}
    return $result;
}

/**
 * @brief check if all fields of the comment are fine
 * @param array $comment
 */
function isCommentValid($comment, $comments) {
    foreach (['url', 'comment', 'name', 'parent'] as $item) {
        if (!array_key_exists($item, $comment)) {
            return false;
        }
    }
    if (($comment['parent'] != -1) && !array_key_exists($comment['parent'], $comments['comments'])) {
        return false;
    }
    return true;
}

/**
 * TODO:
 * - locking the file between read and write?
 *   - it does not work with `file_get_contents()` and does not work anyways.
 *   - http://stackoverflow.com/questions/8412870/php-can-you-read-a-file-that-has-an-exclusive-lock-on-it suggest that locking there are databases
 *   - for now, we accept that comments could get lost if submitted at the same time (which is unlikely)
 */
function addComment($url, $comment)
{
    // TODO: lock the file
    $comments = getCommentStore($url);
    if (isCommentValid($comment, $comments)) {
        $comments = $comments;
        $id = ++$comments['maxId'];
        $comments['comments'][$id] = [
            'id' => $id,
            'url' => $comment['url'],
            'comment' => htmlspecialchars($comment['comment'], ENT_QUOTES),
            'name' => htmlspecialchars($comment['name'], ENT_QUOTES),
            'timestamp' => date(DATE_ATOM), // date('Y-m-d-HTI:s.u+P'),
            'parent' => (int) $comment['parent'],
        ];
        file_put_contents(COMMENTO_DATA_COMMENTS_PATH.'/'.getCommentsFilename($url), json_encode($comments));
        return true;
    }
    return false;
}

/**
 * delete the content of COMMENTO_DATA_PATH
 *
 * this function should never be triggered from a route
 *
 * through the $dataDir we try to make it unlikely, that we delete a complete unrelated directory.
 *
 * @param $dataDir name of the directory containing the data.
 */
function clear($dataDir)
{
    if ((basename(COMMENTO_DATA_PATH) == $dataDir) && file_exists(COMMENTO_DATA_URL_PATH)) {
        unlink(COMMENTO_DATA_URL_PATH);
        array_map('unlink', glob(COMMENTO_DATA_COMMENTS_PATH.'/*'));
        rmdir(COMMENTO_DATA_COMMENTS_PATH);
    }
}


/**
 * create the file structure in COMMENTO_DATA_PATH
 */
function install()
{
    if (file_exists(COMMENTO_DATA_URL_PATH)) {
        die('PHP-Commento is already installed ('.COMMENTO_DATA_URL_PATH.' exists already)');
    }
    if (!file_exists(COMMENTO_DATA_PATH)) {
        if (is_file(COMMENTO_DATA_PATH)) {
            die(
                "Please delete the file ".dirname(COMMENTO_DATA_PATH)."\n".
                "or set COMMENTO_DATA_PATH to a writable directory."
            );
        }
        if (is_writable(dirname(COMMENTO_DATA_PATH))) {
            mkdir(COMMENTO_DATA_PATH);
        } else {
        }
    }
    if (!is_writable(COMMENTO_DATA_PATH)) {
        die(
            "Please make ".dirname(COMMENTO_DATA_PATH)." writable<br>\n".
            "or create ".COMMENTO_DATA_PATH." and make it writable<br>\n".
            "or set COMMENTO_DATA_PATH to a writable directory."
        );
    }
    file_put_contents(COMMENTO_DATA_URL_PATH, json_encode([]));
    mkdir(COMMENTO_DATA_COMMENTS_PATH);
}
