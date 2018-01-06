<?php

namespace SortRating\Traits;

use ReflectionClass;

/**
 * trait adds static method to get all class constants
 *
 * Class ConstantsAll
 * @package SortRating\Traits
 * @author nikita.bunenkov
 */
trait ConstantsAll
{
    /**
     *
     * @return array of all defined constants
     */
    public static function all() {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}