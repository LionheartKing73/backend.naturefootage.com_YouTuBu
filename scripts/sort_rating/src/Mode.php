<?php

namespace SortRating;


use SortRating\Traits\ConstantsAll;

abstract class Mode
{
    use ConstantsAll;

    // collecting mode
    const COLLECTING = 'collect';

    // synchronisation mode
    const SYNCHRONISATION = 'sync';

    // full job mode
    const FULL = 'all';
}