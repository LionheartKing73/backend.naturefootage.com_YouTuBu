<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Class Search_filters_model
 *
 * simple model for lib_search_filters table
 * base functionality:
 * - save sql where condition in db table for forward use
 * - get sql where statement from table by filters hash value
 *
 * @author nikita.bunenkov
 */
class Search_filters_model extends CI_Model
{
    /**
     * table name
     *
     * @var string
     */
    private $table = 'lib_search_filters';

    /**
     * simple cache, where key is hash and value is filter string
     *
     * @var array
     */
    private $cache = [];

    /**
     * @param string $hash filters hash to get from db
     *
     * @return string|null filter string, or null if no filter for this hash value
     */
    public function getFilter($hash)
    {
        $filter = null;

        if (empty($hash) || !is_string($hash)) {
            return $filter;
        }

        // checked if this hash already processed
        if (array_key_exists($hash, $this->cache)) {
            return $this->cache[$hash];
        }

        // execute query and get result array
        $queryResult = $this->db_master->get_where($this->table, ['hash' => $hash], 1)->result_array();

        // get filter value
        if (!empty($queryResult)) {
            $filter = current(array_column($queryResult, 'filter'));
        }

        // save value to cache, only if not empty
        if (!empty($filter)) {
            $this->cache[$hash] = $filter;
        }

        return $filter;
    }

    /**
     * @param string $filter filter string to save
     *
     * @return string|false - filters hash on success save (filter not exists in table) or false if filter was not saved
     */
    public function saveFilter($filter)
    {
        if (empty($filter) || !is_string($filter)) {
            return false;
        }

        $hash = $this->hash($filter);

        // if filter already exist, save nothing
        if ($this->getFilter($hash)) {
            return false;
        }

        // if saved successfully
        if ((bool) $this->db_master->insert($this->table, compact('hash', 'filter'))) {
            return $hash;
        }

        return false;
    }

    /**
     * same as saveFilter, but on existed row in db returns its hash
     *
     * @see $this->saveFilter
     *
     * @param string $filter
     *
     * @return string|false false if filter not saved and not isset in db
     */
    public function saveFilterOrGetExisted($filter)
    {
        // try to save it first
        $hash = $this->saveFilter($filter);

        // save returned false, look like filter row already exists
        if (!$hash) {
            // hash filter
            $hash = $this->hash($filter);
            // and try to find its row
            if (!$this->getFilter($hash)) {
                // if nothing found, return false
                $hash = false;
            }
        }

        return $hash;
    }

    /**
     * get cached string
     *
     * @param $string to hash
     *
     * @return string hashed value
     */
    public function hash($string)
    {
        return md5($string);
    }
}