<?php

$dbConfig = __DIR__ . DIRECTORY_SEPARATOR
    . '..'
    . DIRECTORY_SEPARATOR
    . '..'
    . DIRECTORY_SEPARATOR
    . 'application'
    . DIRECTORY_SEPARATOR
    . 'config'
    . DIRECTORY_SEPARATOR
    . 'database.php';


$db = null;

if (file_exists($dbConfig)) {
    require $dbConfig;
}

return $db;