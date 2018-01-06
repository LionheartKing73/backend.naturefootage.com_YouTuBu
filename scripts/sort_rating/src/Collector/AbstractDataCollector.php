<?php

namespace SortRating\Collector;

use SortRating\Contracts\DataCollector;
use SortRating\Traits\PdoUser;
use SortRating\Exceptions\DataCollector as DataCollectorException;
use PDOException;

/**
 * Class AbstractDataCollector
 * connects trait PdoUser, implementing interface meethods delegate to inherit classes
 *
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
abstract class AbstractDataCollector implements DataCollector
{
    use PdoUser;

    /**
     * state table name
     * @var string
     */
    private $_stateTable = '__sort_rating_state';

    /**
     * update limit for state table
     * @var int
     */
    private $_updateLimit = 100000;

    /**
     * process query with specified offset an limit
     *
     * @param int $limit
     * @param int $offset
     * @return void
     */
    protected abstract function _processPartitial($limit, $offset);

    public function collect()
    {
        // get total rows from state table
        $total = $this->getPDO()->query("SELECT COUNT(id) FROM {$this->_stateTable()}")->fetchColumn();

        // set start offset
        $offset = 0;

        // set limit for query execution
        $limit = $this->_updateLimit();

        try {
            // revert removed to default if necessary
            if ($this->_revertRemovedToDefault()) {
                $this->_processRevertToDefault();
            }
            
            $this->getPDO()->beginTransaction();
            while ($offset < $total) {
                $this->_processPartitial($limit, $offset);
                $offset += $limit;
            }
            $this->getPDO()->commit();
        } catch (PDOException $exception) {
            $this->getPDO()->rollBack();
            throw new DataCollectorException(
                "Caught while processing collecting in " . __CLASS__,
                DataCollectorException::STORAGE_EXCEPTION,
                $exception
            );
        }

        return $this;
    }

    /**
     * revert to default, override this in inherit class to implement reverting value to default
     */
    protected function _processRevertToDefault()
    {
    }

    /**
     * name of state table
     * @return string
     */
    protected function _stateTable()
    {
        return $this->_stateTable;
    }

    /**
     * limit for join table select
     * @return int
     */
    protected function _updateLimit()
    {
        return $this->_updateLimit;
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
        return true;
    }
}