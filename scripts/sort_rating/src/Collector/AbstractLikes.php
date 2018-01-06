<?php

namespace SortRating\Collector;

/**
 * Class AbstractLikes
 * grouped realisation of DataColelctors which work with lib_clip_rating table
 * field name and name values realisation delegated to inherited classes
 *
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
abstract class AbstractLikes extends AbstractDataCollector
{
    private $_countTable = 'lib_clip_rating';

    private $_countTableItemId = 'code';

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
                        SELECT {$this->_countTableItemId}  as item_id, count({$this->_countTableItemId} ) as value 
                        FROM {$this->_countTable} 
                        WHERE name = '{$this->_name()}' 
                        GROUP BY item_id
                        LIMIT {$limit}
                        OFFSET {$offset}
                    ) as count_table
                    ON state.item_id = count_table.item_id
                    SET state.need_update = 1, state.{$this->_field()}= count_table.value
                    WHERE state.{$this->_field()} != count_table.value"
        );
        $statement->execute();
    }

    /**
     * indicates, if value of field should be reverted to default
     * in case if there no more rows related to item in count table
     *
     * override in inherited classes to change logic
     *
     * @return bool
     */
    protected function _revertRemovedToDefault()
    {
        $statement = $this->getPDO()->prepare(
            "SELECT EXISTS (
                SELECT 1 FROM {$this->_stateTable()} as state 
                WHERE {$this->_field()}  != DEFAULT({$this->_field()}) 
                AND NOT EXISTS (
                  SELECT 1 FROM {$this->_countTable} 
                  WHERE name = '{$this->_name()}' AND {$this->_countTableItemId} = state.item_id
                )
            )"
        );
        $statement->execute();

        $res = (bool) $statement->fetchColumn();

        return $res;
    }

    /**
     * revert $_stateTableField value to default for item_id which not presentd in $_countTable anymore
     */
    protected function _processRevertToDefault()
    {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable()} as state
                SET need_update = 1, {$this->_field()} = DEFAULT
               WHERE {$this->_field()} != DEFAULT({$this->_field()})  
               AND NOT EXISTS (
                  SELECT 1 FROM {$this->_countTable} 
                  WHERE name = '{$this->_name()}' AND {$this->_countTableItemId} = state.item_id
                )"
        );

        $statement->execute();
    }

    /**
     * get value for {$_likesTable}.name field
     * @return string
     */
    protected abstract function _name();

    /**
     * get {$_stateTable}.field_name to set value to
     * @return string
     */
    protected abstract function _field();
}