<?php
namespace SortRating\Collector;

use SortRating\Contracts\DataCollector;
use SortRating\Traits\PdoUser;
use SortRating\Exceptions\DataCollector as DataCollectorException;
use PDOException;

/**
 * Add new rows from clips table to state table
 *
 * Class InsertNew
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class InsertNew implements DataCollector
{
    use PdoUser;

    private $_stateTable = '__sort_rating_state';

    private $_clipsTable = 'lib_clips';

    /**
     * collect data from sources, (update state values)
     * @throws \SortRating\Exceptions\DataCollector
     * @return $this
     */
    public function collect()
    {
        try {
            $statement = $this->getPDO()->prepare(
                "INSERT INTO {$this->_stateTable} (item_id) 
                SELECT id FROM {$this->_clipsTable} 
                WHERE id NOT IN (SELECT item_id FROM {$this->_stateTable})"
            );
            $statement->execute();
        } catch (PDOException $exception) {
            throw new DataCollectorException(
                "Caught while inserting new rows to state table",
                DataCollectorException::STORAGE_EXCEPTION,
                $exception
            );
        }

        return $this;
    }
}