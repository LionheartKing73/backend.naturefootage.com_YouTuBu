<?php

namespace SortRating\Log;

use SortRating\Contracts\Exception;
use SortRating\Traits\PdoUser;
use PDO;

/**
 * Class DB
 * @package SortRating\Log
 * @author nikita.bunenkov
 */
class DB implements \SortRating\Contracts\Log
{
    use PdoUser;

    private $_table = '__sort_rating_state_exception_log';

    /**
     * @param Exception $ex
     * @return bool (true - save ok, false - not ok)
     */
    public function exception(\Exception $ex)
    {
        $value = print_r($ex, true);
        $statement = $this->getPDO()->prepare(
            "INSERT INTO {$this->_table} (exception) VALUE (:exception)"
        );

        $statement->bindParam(":exception", $value);

        return $statement->execute();
    }
}