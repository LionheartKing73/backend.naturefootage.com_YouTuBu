<?php

return [
    // db config
    'db' => require __DIR__ . DIRECTORY_SEPARATOR . 'database.php',
    // classes to collect data from real time storage to intermediate storage
    'data-collectors' => [
        \SortRating\Collector\DeleteUnexisted::class,
        \SortRating\Collector\InsertNew::class,
        \SortRating\Collector\Orders::class,
        \SortRating\Collector\Likes\AdminLikes::class,
        \SortRating\Collector\Likes\UserLikes::class,
        \SortRating\Collector\Likes\GuestLikes::class,
        \SortRating\Collector\Clipbins::class,
        \SortRating\Collector\Q1Views::class,
//        \SortRating\Collector\MasterDownloads::class,
        \SortRating\Collector\ClipTable\Q2Views::class,
        \SortRating\Collector\ClipTable\Q2Downloads::class,
        \SortRating\Collector\ClipTable\FormatRating::class,
        \SortRating\Collector\ClipTable\AgeRating::class,
        \SortRating\Collector\ClipTable\PriceLevel::class,
    ]
];