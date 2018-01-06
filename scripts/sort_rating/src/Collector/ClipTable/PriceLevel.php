<?php

namespace SortRating\Collector\ClipTable;

use SortRating\Collector\AbstractDataCollector;

/**
 * Class PriceLevel
 * ticket number: FSEARCH-1248
 * -- Any clip where lib_clips: price_level = 4
 * -- Add rating value to lib_settings with weight of lib_settings.name = gold_price_rating value
 *
 * check lib_clips.price_level, if == 4 => set {$this->_stateTable()}.gold_price = 1, else set 0
 *
 * @package SortRating\Collector\ClipTable
 *
 * @author nikita.bunenkov
 */
class PriceLevel extends AbstractDataCollector
{
    private $_countTable = 'lib_clips';

    private $_stateTableField = 'gold_price';

    private $_GOLD_PRICE = 4;

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
                        SELECT id as item_id, IF( price_level = {$this->_GOLD_PRICE}, 1, 0 ) as value 
                        FROM {$this->_countTable}  
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