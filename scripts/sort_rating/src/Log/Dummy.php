<?php

namespace SortRating\Log;

/**
 * Class Dummy - var dump log
 * @package SortRating\Log
 * @author nikita.bunenkov
 */
class Dummy implements \SortRating\Contracts\Log
{

    /**
     * @param \SortRating\Exceptions\Exception $ex
     * @return bool (true - save ok, false - not ok)
     */
    public function exception(\Exception $ex)
    {
        var_dump($ex);
        return true;
    }
}