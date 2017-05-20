<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>PHP-Commento Testing</title>
    <style>
        .test-ok {
            color:darkgreen;
        }
        .test-fail {
            color:darkred;
        }
    </style>
  </head>
  <body>
<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

// debug('_REQUEST', $_REQUEST);
// debug('_SERVER', $_SERVER);

function testRenderValue($value) {
    if (is_null($value)) {
        return '&lt;null&gt;';
    } elseif (is_bool($value)) {
        return $value ? '&lt;true&gt;' : '&lt;false&gt;';
    } else {
        return htmlspecialchars(print_r($value, 1));
    }
}
function testIdentical($label, $value, $expect) {
    $output = [];
    if ($value === $expect) {
        $output[] = '<p class="test-ok">'.$label.': ok</p>';
    } else {
        $output[] = '<p class="test-fail">'.$label.': failed</p>';
        $output[] = '<p">result:</p>';
        $output[] = '<pre>'.testRenderValue($value).'</pre>';
        $output[] = '<p">expected:</p>';
        $output[] = '<pre>'.testRenderValue($expect).'</pre>';
    }
    echo(implode("\n", $output)."\n");
}

function testSection($title)
{
    echo("<h2>$title</h2>\n");
}

define('COMMENTO_CONFIG_PATH', __DIR__.'/config-test.php');

include_once('src/commento.php');

Commento\loadConfiguration();

// if the test configuration has been loaded and the data-test directory already exists, delete it
Comment\clear('data-test');

if (!file_exists(COMMENTO_DATA_URL_PATH)) {
    Commento\install();
}

testSection('The empty store');
    
testIdentical('install has created an empty url list', json_decode(file_get_contents(COMMENTO_DATA_URL_PATH), true), []);

testIdentical('get new url gives empty store', Commento\getCommentStore('http://test'), ['maxId' => 0, 'comments' => []]);

testIdentical('get new url gives empty list', Commento\getComments('http://test'), []);

testSection('The first comment ');

testIdentical('adding an empty comment returns false', Commento\addComment('http://test', []), false);

// TODO: add a version to the url file
$comment = [
    'url' => 'http://test',
    'comment' => 'this is my comment',
    'name' => 'The Commenter',
    'parent' => -1,
];
testIdentical('adding a comment returns true', Commento\addComment('http://test', $comment), true);

$comments = Commento\getComments('http://test');

testIdentical('the first comment has id 1', $comments[1]['id'], 1);

testIdentical('the first comment has a timestamp', $comments[1]['timestamp'] !== '', true);

unset($comments[1]['timestamp']);
unset($comments[1]['id']);

testIdentical('getting the first comment', $comments, [1 => $comment]);

testSection('The second comment ');

$secondComment = [
    'url' => 'http://test',
    'comment' => 'this is another comment',
    'name' => 'Another Commenter',
    'parent' => -1,
];

testIdentical('adding a second comment returns true', Commento\addComment('http://test', $secondComment), true);

$comments = Commento\getComments('http://test');

for ($i = 1; $i <= 2; $i++) {
    testIdentical('the '.$i.'th comment has id '.$i, $comments[$i]['id'], $i);
    unset($comments[$i]['timestamp']);
    unset($comments[$i]['id']);
}

testIdentical('getting two comments', $comments, [1 => $comment, 2 => $secondComment]);

$thirdComment = [
    'url' => 'http://second.test',
    'comment' => 'this is a comment for another url',
    'name' => 'The Other One',
    'parent' => -1,
];

testSection('The second url');

testIdentical('adding a comment for a second url returns true', Commento\addComment('http://second.test', $thirdComment), true);

testIdentical('there are still two comments for the first url', count(Commento\getComments('http://test')), 2);

testIdentical('The second url has the third comm', $comments, [1 => $comment, 2 => $secondComment]);

$comments = Commento\getComments('http://second.test');

testIdentical('the comment for the second url has id 1', $comments[1]['id'], 1);

unset($comments[1]['timestamp']);
unset($comments[1]['id']);

testIdentical('getting the comment for the second url', $comments, [1 => $thirdComment]);

testSection('The reply');

$replyComment = [
    'url' => 'http://second.test',
    'comment' => 'the reply',
    'name' => 'Another Commenter',
    'parent' => 1,
];

testIdentical('adding a reply to the first comment in the second url returns true', Commento\addComment('http://second.test', $replyComment), true);

$comments = Commento\getComments('http://second.test');

for ($i = 1; $i <= 2; $i++) {
    testIdentical('the '.$i.'th comment has id '.$i, $comments[$i]['id'], $i);
    unset($comments[$i]['timestamp']);
    unset($comments[$i]['id']);
}

testIdentical('getting the comments with the reply', $comments, [1 => $thirdComment, 2 => $replyComment]);

?>
  </body>
</html>
