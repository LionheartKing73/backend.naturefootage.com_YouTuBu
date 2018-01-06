<?php

namespace SortRating\Contracts;

/**
 * Interface Log - common log intrerface
 * @package SortRating\Contracts
 * @author nikita.bunenkov
 */
interface Log
{
    /**
     * @param Exception $ex
     * @return bool (true - save ok, false - not ok)
     */
    public function exception(\Exception $ex);
}