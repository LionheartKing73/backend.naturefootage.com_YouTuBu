<?php

namespace SortRating\Collector\ClipTable;

use SortRating\Collector\AbstractDataCollector;
use PDOException;
use SortRating\Settings;
use SortRating\Traits\LibSettings;

/**
 * Class AgeRating
 * @package SortRating\Collector\ClipTable
 * @author nikita.bunenkov
 */
class AgeRating extends AbstractDataCollector
{
    use LibSettings;

    private $_stateTableField = 'age_rating';

    private $_countTable = 'lib_clips';


    /**
     * get array of settings keys to take values from db
     *
     * used in LibSettings trait
     * @see LibSettings
     *
     * @return array
     */
    protected function _settingsKeys()
    {
        return [
            Settings::AGE_RATING_MORE_THAN_YEAR,
            Settings::AGE_RATING_MORE_THAN_HALF_YEAR,
            Settings::AGE_RATING_MORE_THAN_MONTH,
            Settings::AGE_RATING_MORE_THAN_WEEK,
            Settings::AGE_RATING_LESS_THAN_WEEK,
        ];
    }

    /**
     * process query with specified offset an limit
     *
     * @param int $limit
     * @param int $offset
     * @return void
     */
    protected function _processPartitial($limit, $offset)
    {
        $this->_getLibSettings();
        $statement = $this->getPDO()->prepare(
            "UPDATE {$this->_stateTable()} as state
                    JOIN (
                        SELECT id as item_id, 
                            CASE 
                                WHEN DATE_ADD(ctime, INTERVAL 1 YEAR) < NOW() THEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_YEAR]} 
                                WHEN DATE_ADD(ctime, INTERVAL 6 MONTH) < NOW() THEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_HALF_YEAR]}
                                WHEN DATE_ADD(ctime, INTERVAL 1 MONTH) < NOW() THEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_MONTH]} 
                                WHEN DATE_ADD(ctime, INTERVAL 7 DAY) < NOW() THEN {$this->_settings[Settings::AGE_RATING_MORE_THAN_WEEK]} 
                                ELSE {$this->_settings[Settings::AGE_RATING_LESS_THAN_WEEK]} 
                            END as value 
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