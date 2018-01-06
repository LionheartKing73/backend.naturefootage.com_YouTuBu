<?php

namespace SortRating\Contracts;

/**
 * Interface DataCollector
 * @package SortRating\Contracts
 * @author nikita.bunenkov
 */
interface DataCollector
{
    /**
     * collect data from sources, (update state values)
     * @throws \SortRating\Exceptions\DataCollector
     * @return $this
     */
    public function collect();
}