<?php

class Sections_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    ### Закрытые методы \\ Хелперы #####################################################################################

    /**
     * Получение первой строки результата выборки с БД
     *
     * @param CI_DB_result $result
     *
     * @return array
     */
    private function _get_row(CI_DB_result $result) {
        if ($result instanceof CI_DB_result) {
            return $result->row_array();
        }
        return array();
    }

    /**
     * Получить все строки рузультата выборки с БД
     *
     * @param CI_DB_result $result
     *
     * @return array
     */
    private function _get_array(CI_DB_result $result) {
        if ($result instanceof CI_DB_result) {
            return $result->result_array();
        }
        return array();
    }

    private function _create_limit_string($limit) {
        list( $from, $count ) = $limit;
        return "LIMIT {$from}, {$count}";
    }

    private function _create_filter_string($filter) {
        $parts = array();
        $string = NULL;
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $parts[] = "lib_section.{$field} LIKE '%{$value}%'";
                }
            }
            $string = implode(' AND ', $parts);
            $string = (!empty($string) ) ? ' AND ' . $string : NULL;
        }
        return $string;
    }

    ### Методы доступа к данным ########################################################################################

    function get_section_count($filter) {
        $filter = $this->_create_filter_string($filter);
        $row = $this->_get_row(
                $this->db->query("
                SELECT COUNT( lib_section.id ) AS 'count'
                FROM lib_section;"
                )
        );
        return ( isset($row['count']) ) ? $row['count'] : 0;
    }


    function delete_section($ids) {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete(
                        'lib_section', array('id' => $id), 1
                );
            }
        }
    }

    function add_section($data) {
        if (isset($data['keyword'])) {
            $this->db_master->insert('lib_section', $data);
            return $this->db_master->insert_id();
        }
        return false;
    }

    function edit_section($id, $data) {
        if (isset($data['keyword']) && !empty($id)) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_section', $data);
            return true;
        }
        return false;
    }

    function get_section_list($filter, $limit) {
        $limit = $this->_create_limit_string($limit);
        $filter = $this->_create_filter_string($filter);
        $result = $this->db->query("
            SELECT * FROM lib_section             
            {$limit};"
        );
        return $this->_get_array($result);
    }


}
