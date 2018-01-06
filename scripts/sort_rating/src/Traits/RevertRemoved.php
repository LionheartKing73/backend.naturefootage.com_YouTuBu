<?php

namespace SortRating\Traits;

/**
 * Trait RevertRemoved, trait to use with AbstractDataCollector instances (!) only
 * which have the same table structure and class contains properties:
 * - $_stateTableField - name of a field in a state table, which contains state data for this data collector
 * - $_countTable - name of table where to check removed data from
 * - $_countTableItemId - name of a field in a count table, which is related to item_id, item_id in most cases
 *
 * @package SortRating\Traits
 *
 * @author nikita.bunenkov
 */
trait RevertRemoved
{

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
                  WHERE {$this->_countTableItemId} = state.item_id
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
                  WHERE {$this->_countTableItemId} = state.item_id
                )"
        );
        $statement->execute();
    }
}
