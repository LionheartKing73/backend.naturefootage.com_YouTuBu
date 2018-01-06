<?php

namespace SortRating\Collector;
use SortRating\Traits\RevertRemoved;

/**
 * Class Clipbins
 *
 * collect data from clipbin relation table, compare it with current state data
 * and update if it was changed since kast job processed
 *
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class Clipbins extends AbstractDataCollector
{
    use RevertRemoved;
    
    private $_stateTableField = 'clipbins';

    private $_countTable = 'lib_lb_items';
    
    private $_countTableItemId = 'item_id';

    /**
     * process query with specified offset an limit
     *
     * @param int $limit
     * @param int $offset
     * @return void
     */
    protected function _processPartitial($limit, $offset)
    {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable()} as state
                JOIN (
                    SELECT {$this->_countTableItemId} as item_id, COUNT({$this->_countTableItemId}) as value 
                    FROM {$this->_countTable} GROUP BY item_id
                    LIMIT {$limit}
                    OFFSET {$offset}
                ) as count_table 
                ON state.item_id = count_table.item_id
                SET state.need_update = 1, state.{$this->_stateTableField} = count_table.value
                WHERE state.{$this->_stateTableField} != count_table.value"
        );
        $statement->execute();
    }
}