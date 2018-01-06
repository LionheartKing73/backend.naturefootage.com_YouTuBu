<?php

namespace SortRating\Collector\ClipTable;

use SortRating\Collector\AbstractDataCollector;
use SortRating\Settings;
use SortRating\Traits\LibSettings;

/**
 * Class FormatRating
 * @package SortRating\Collector\ClipTable
 * @author nikita.bunenkov
 */
class FormatRating extends AbstractDataCollector
{
    use LibSettings;

    private $_countTable = 'lib_clips';

    private $_stateTableField = 'format_rating';

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
            Settings::FORMAT_RATING_HD,
            Settings::FORMAT_RATING_ULTRA_HD,
            Settings::FORMAT_RATING_SD,
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
                            CASE sort_format
                                WHEN 1 THEN {$this->_settings[Settings::FORMAT_RATING_SD]} 
                                WHEN 2 THEN {$this->_settings[Settings::FORMAT_RATING_HD]}
                                WHEN 3 THEN {$this->_settings[Settings::FORMAT_RATING_HD]}
                                ELSE {$this->_settings[Settings::FORMAT_RATING_ULTRA_HD]}                                 
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