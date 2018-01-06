<?php

namespace SortRating\Collector;

/**
 * Class AbstractClipData
 * grouped realisation of DataColelctors which work with lib_clips table
 * field name got state and clips tables realisation delegated to inherited classes
 *
 *
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
abstract class AbstractClipData extends AbstractDataCollector
{
    private $_countTable = 'lib_clips';

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
                      SELECT id as item_id, {$this->_countField()} as value FROM {$this->_countTable}
                      LIMIT {$limit}
                      OFFSET {$offset}
                    ) as count_table
                    ON state.item_id = count_table.item_id
                    SET state.need_update = 1, state.{$this->_stateField()} = count_table.value
                    WHERE state.{$this->_stateField()} != count_table.value"
        );
        $statement->execute();
    }

    /**
     * indicates, if value of field should be reverted to default
     * in case if there no more rows related to item in count table
     *
     * this is no need, because all data takes from lib_clips,
     * and rows from state table removed when it removed from lib_clips
     *
     * @return bool
     */
    protected function _revertRemovedToDefault()
    {
        return false;
    }


    /**
     * get {$this->_stateTable} field name
     * @return string
     */
    protected abstract function _stateField();

    /**
     * get {$this->_clipsTable} field name
     * @return string
     */
    protected abstract function _countField();
}