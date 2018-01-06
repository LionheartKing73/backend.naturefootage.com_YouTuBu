<?php

/**
 * this is job executor
 * @author nikita.bunenkov
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'definition.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

$time = microtime(true);

try {
    notify('CRON starts: ' . (IS_LOCAL ? 'local'  : $_SERVER['environment']));


    $config = require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

    // determine executor mode
    $mode = isset($argv[1]) ? $argv[1] : \SortRating\Mode::FULL;

    // if mode not exists => end of execution
    if (!in_array($mode, \SortRating\Mode::all())) {
        throw new RuntimeException("Mode $mode not supported");
    }

    // create pdo instance
    $dbh = createPDO(
        $config['db']['master']['hostname'],
        $config['db']['master']['database'],
        $config['db']['master']['username'],
        $config['db']['master']['password']
    );

    // add PDO instance to log instance
    \SortRating\Log\Factory::instance(\SortRating\Log\DB::class)->setPDO($dbh);

    // process script
    switch ($mode) {
        case \SortRating\Mode::COLLECTING:
            collecting($config['data-collectors'], $dbh);
            break;
        case \SortRating\Mode::SYNCHRONISATION:
            synchronisation($dbh);
            break;
        case \SortRating\Mode::FULL:
            collecting($config['data-collectors'], $dbh);
            synchronisation($dbh);
            break;
    }
} catch (Exception $ex) {
    notify($ex->getMessage() . " : \n" . print_r($ex, true));
} finally {
    notify("CRON ends. Execution time: " . (microtime(true) - $time));
}
die();