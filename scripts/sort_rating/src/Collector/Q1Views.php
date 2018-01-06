<?php

namespace SortRating\Collector;

/**
 * Class Q1Views
 * РHard to explain what exactl data is collected here, some view statistic from exta_statistic query
 * Collected  data compared to stored state data, update provided if data was changed since last job processed
 *
 * @package SortRating\Collector
 * @author nikita,bunenkov
 */
class Q1Views extends AbstractDataCollector
{
    private $_stateTableField = 'q1_views';

    private $_countTable = 'lib_clips_extra_statistic';

    private $_countTableItemId = 'clip_id';

    protected function _processPartitial($limit, $offset) {
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable()} as state
                    JOIN (
                        SELECT {$this->_countTableItemId} as item_id, COUNT({$this->_countTableItemId}) as value 
                        FROM {$this->_countTable} 
                        WHERE action_type = 1 
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
                WHERE {$this->_stateTableField}  != DEFAULT({$this->_stateTableField}) 
                AND NOT EXISTS (
                  SELECT 1 FROM {$this->_countTable} 
                  WHERE action_type = 1 AND {$this->_countTableItemId} = state.item_id
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
                SET state.need_update = 1, state.{$this->_stateTableField} = DEFAULT
                WHERE state.{$this->_stateTableField} != DEFAULT({$this->_stateTableField})
                AND NOT EXISTS (
                  SELECT 1 FROM {$this->_countTable} 
                  WHERE action_type = 1 AND {$this->_countTableItemId} = state.item_id
                )"
        );
        $statement->execute();
    }
}