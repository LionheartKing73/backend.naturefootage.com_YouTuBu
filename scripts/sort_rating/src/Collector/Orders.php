<?php

namespace SortRating\Collector;
use SortRating\Traits\RevertRemoved;

/**
 * Class Orders
 * collect data from oreders items relation table,
 * compare it with state data and update if data was changed since last job processed
 *
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class Orders extends AbstractDataCollector
{
    use RevertRemoved;

    private $_stateTableField = 'orders';

    private $_countTable = 'lib_orders_items';

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
                        FROM {$this->_countTable} 
                        GROUP BY item_id 
                        LIMIT {$limit}
                        OFFSET {$offset}
                    ) as count_table 
                    ON state.item_id = count_table.item_id
                    SET state.need_update = 1, state.{$this->_stateTableField} = count_table.value
                    WHERE state.{$this->_stateTableField} != count_table.value"
        );
        $statement->execute();
    }


    /**
     * revert $_stateTableField value to default for item_id which not presentd in $_countTable anymore
     */
    protected function _processRevertToDefault()
    {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable()} as state
                SET state.need_update = 1, state.{$this->_stateTableField} = DEFAULT
                WHERE state.{$this->_stateTableField} != DEFAULT({$this->_stateTableField}) AND NOT EXISTS (
                    SELECT item_id
                    FROM {$this->_countTable}
                    WHERE item_id = state.item_id
                )"
        );
        $statement->execute();
    }
}