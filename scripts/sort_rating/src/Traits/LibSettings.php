<?php

namespace SortRating\Traits;

use PDO;

/**
 * Trait LibSettings
 *
 * get lib settings from table to local field
 * trtait should be used with classes which used PdoUser trait
 *
 * @see PdoUser
 *
 * @package SortRating\Traits
 */
trait LibSettings
{
    /**
     * @var string - table name
     */
    private $_settingsTable = 'lib_settings';

    /**
     * @var array
     */
    private $_settings = null;

    /**
     * get array of settings keys to take values from db
     *
     * @return array
     */
    abstract protected function _settingsKeys();

    /**
     * get values from {$this->_settingsTable} and save it to {$this->_settings}
     *
     * return void
     */
    private function _getLibSettings()
    {
        if (is_null($this->_settings)) {
            $statement = $this->getPDO()->prepare(
                "SELECT name, value FROM {$this->_settingsTable} 
                WHERE name IN({$this->_prepareArray($this->_settingsKeys(), true)})"
            );
            
            $statement->execute();

            $this->_settings = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    }
}