<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 29.04.16
 * Time: 16:06
 */

namespace SortRating\Contracts;

/**
 * Interface CollectorContainer
 * @package SortRating\Contracts
 * @author nikita.bunenkov
 */
interface CollectorContainer extends Runnable
{
    /**
     * attach new data collector
     * @param DataCollector $collector
     * @return $this
     */
    public function attach(DataCollector $collector);

    /**
     * deattach collector
     * @param DataCollector $collector
     * @return $this
     */
    public function deattach(DataCollector $collector);
}