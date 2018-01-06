<?php

namespace SortRating\Collector;

use SortRating\Contracts\DataCollector;
use SortRating\Traits\PdoUser;
use SortRating\Exceptions\DataCollector as DataCollectorException;
use PDOException;


/**
 * Delete rows from state table which not presented in clips table
 *
 * Class DeleteUnexisted
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class DeleteUnexisted implements DataCollector
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
                "DELETE FROM {$this->_stateTable} 
                  WHERE item_id NOT IN (SELECT id FROM {$this->_clipsTable})"
            );
            $statement->execute();
        } catch (PDOException $exception) {
            throw new DataCollectorException(
                "Caught while deleting non existing rows from state table",
                DataCollectorException::STORAGE_EXCEPTION,
                $exception
            );
        }

        return $this;
    }
}