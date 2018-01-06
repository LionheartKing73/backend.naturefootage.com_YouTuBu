<?php
/**
 * Created by PhpStorm.
 * User: nikita
 * Date: 03.05.16
 * Time: 15:00
 */

namespace SortRating\Traits;

/**
 * Class PdoUser
 * @package SortRating\Traits
 * @author nikita.bunenkov
 */
trait PdoUser
{
    /**
     * @var \PDO
     */
    private $_dbh;

    /**
     * get PDO instance
     * @return \PDO
     */
    public function getPDO() {
        return $this->_dbh;
    }

    /**
     * set PDO instance
     * @param \PDO $dbh
     * @return $this
     */
    public function setPDO(\PDO $dbh) {
        $this->_dbh = $dbh;

        return $this;
    }


    /**
     * prepare array for use in sql query in "IN" statement
     * @param array $array
     * @param bool $withQuotes true - if need vals in array to be put in quotes
     * @return string
     */
    protected function _prepareArray(array $array, $withQuotes = false)
    {
        // just implode array, if no need in quotes
        if (false === $withQuotes) {
            return implode(', ', $array);
        }

        // function to reduce array to string
        $callback = function ($carry, $item) {;
            // wrap $item with quotes
            $nextValue = '"' . $item . '"';

            // add coma before item, if item is not the first one
            if (!empty($carry)) {
                $nextValue = ", " . $nextValue;
            }

            $carry .= $nextValue;
            return $carry;
        };

        return array_reduce($array, $callback);
    }
}