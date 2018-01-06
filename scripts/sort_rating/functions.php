<?php

/**
 * create PDO object from values
 *
 * @param $host
 * @param $dbname
 * @param $user
 * @param $password
 * @return PDO
 */
function createPDO($host, $dbname, $user, $password) {
    $pdo = new PDO(
        'mysql:host=' . $host . ';dbname=' . $dbname,
        $user,
        $password
    );

    // to set PDO to throw exception on invalid queries
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

/**
 * procedure for collecting state data
 */
function collecting(array $dataCollectors, PDO $dbh) {
    $collector = \SortRating\Collector::fromArray($dataCollectors, $dbh);
    $collector->run();
}

/**
 * procedure for synchronisation clips with state data
 */
function synchronisation(PDO $dbh) {
    $synchronizer = new \SortRating\Synchronizer($dbh);
    $synchronizer->run();
}

/**
 * system log
 * @param string $message
 * @return bool
 */
function notify($message) {
    if (IS_LOCAL) {
        echo $message . PHP_EOL;
    } else {
        $to = 'nikita.bunenkov@boldendeavours.com';
        $subj = 'Sort Ranking Execution';
        return mail($to, $subj, $message);
    }
}