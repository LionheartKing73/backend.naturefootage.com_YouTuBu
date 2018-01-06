<?php

class Keywords_users_model extends CI_Model {

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
                    $parts[] = "lib_user_keywords.{$field} LIKE '%{$value}%'";
                }
            }
            $string = implode(' AND ', $parts);
            $string = (!empty($string) ) ? ' AND ' . $string : NULL;
        }
        return 'WHERE lib_user_keywords.old = 0 AND lib_user_keywords.provider_id = 0 ' . $string;
    }

    ### Методы доступа к данным ########################################################################################

    function get_keywords_count($filter) {
        $filter = $this->_create_filter_string($filter);
        $row = $this->_get_row(
                $this->db->query("
                SELECT COUNT( lib_user_keywords.id ) AS 'count'
                FROM lib_user_keywords;"
                )
        );
        return ( isset($row['count']) ) ? $row['count'] : 0;
    }

    function get_frontends_list($filter = array(), $limit = array(), $order_by = '') {
        if ($filter) {
            foreach ($filter as $param => $value) {
                $this->db->where($param, $value);
            }
        }
        if ($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if ($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_frontends');
        $res = $query->result_array();
        return $res;
    }

    function update_basic_status($data) {
        if (is_array($data)) {
            foreach ($data as $id => $status) {
                $this->db_master->update(
                        'lib_user_keywords', array('basic' => $status), array('id' => $id), 1
                );
            }
        }
    }

    function delete_keywords($ids) {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete(
                        'lib_user_keywords', array('id' => $id), 1
                );
            }
        }
    }

    function add_keyword($data) {
        if (isset($data['keyword'])) {
            $this->db_master->insert('lib_user_keywords', $data);
            return $this->db_master->insert_id();
        }
        return false;
    }

    function edit_keyword($id, $data) {
        if (isset($data['keyword']) && !empty($id)) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_user_keywords', $data);
            return true;
        }
        return false;
    }

    function get_keywords_list($filter, $limit) {
        $limit = $this->_create_limit_string($limit);
        $filter = $this->_create_filter_string($filter);
        $result = $this->db->query("
            SELECT c.*,d.name
            FROM lib_user_keywords c LEFT JOIN lib_collections d ON c.collection_id = d.id
            
            {$limit};"
        );
        return $this->_get_array($result);
    }

    function get_collections_list() {

        $query = $this->db->get('lib_collections');
        $res = $query->result_array();
        return $res;
    }

}
