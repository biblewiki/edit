<?php
$txt = $_GET['text'];
$language = $_GET['language'];

$filename = 'notTranslated.txt';

if (strpos(file_get_contents($filename), $txt) === false){
    $myfile = file_put_contents($filename, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}


