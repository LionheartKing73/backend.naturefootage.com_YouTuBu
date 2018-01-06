<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/17/17
 * Time: 20:10
 */
class Taxonomy_model extends Base_model
{
    function __construct()
    {
        parent::__construct();
    }

    protected $_table;

    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @var int
     * it is needed for debug
     */
    static $repeats = 0;

    protected function _filterValue($field_value)
    {
        $field_value = trim($field_value);
        $field_value = strtolower($field_value);
        return $field_value;
    }

    public function firstOrNew($field, $value, array $fields = [])
    {
        $query = $this->db->get_where($this->_table, [$field => $value], 1);
        $num_rows = $query->num_rows();
        if ($num_rows) {
            /**
             * We have such row already
             */
            $rows = $query->result();
            static::$repeats++;
            log_message('debug', "\n TABLE({$this->_table}) Row field = '{$field}' with value = '{$value}'' already exists. Count = " . static::$repeats);
            return $rows[0]->id;
        } else {
            /**
             * Insert a new row
             */
            $fields[$field] = $value;

            foreach ($fields as $key => $field_value) {
                $fields[$key] = $this->_filterValue($field_value);
            }

            $this->db->insert_batch($this->_table, [$fields]);
            static::$repeats++;
            log_message('debug', "\n TABLE({$this->_table}) new Row has been added, insert_id= " . $this->db->insert_id() . "\n");
            return $this->db->insert_id();
        }
    }
}