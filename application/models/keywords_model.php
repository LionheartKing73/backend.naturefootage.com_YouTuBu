<?php

class Keywords_model extends CI_Model
{

    function __construct()
    {
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
    private function _get_row(CI_DB_result $result)
    {
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
    private function _get_array(CI_DB_result $result)
    {
        if ($result instanceof CI_DB_result) {
            return $result->result_array();
        }
        return array();
    }

    private function _create_limit_string($limit)
    {
        if (!empty($limit)) {
            list($from, $count) = $limit;
            return "LIMIT {$from}, {$count}";
        } else {
            return "";
        }
    }

    private function _create_filter_string($filter)
    {
        $parts = array();
        $string = NULL;
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $parts[] = "lib_keywords.{$field} LIKE '%{$value}%'";
                }
            }
            $string = implode(' AND ', $parts);
            $string = (!empty($string)) ? ' AND ' . $string : NULL;
        }
//return 'WHERE lib_keywords.old = 0 AND lib_keywords.provider_id = 0 ' . $string;
        return 'WHERE '; //. $string;
    }

    ### Методы доступа к данным ########################################################################################

    function get_keywords_count($filter)
    {
        $parts = array();
//$filter = $this->_create_filter_string($filter);
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {

                    $parts[] = "lib_keywords.{$field} LIKE '%{$value}%'";
                }
            }
            $string .= implode(' AND  ', $parts);
            $string = (!empty($string)) ? ' AND ' . $string : NULL;
        }
        $row = $this->_get_row(
            $this->db->query("
                SELECT COUNT( lib_keywords.id ) AS 'count'
                FROM lib_keywords
                WHERE 1
                {$string}
                ;"
            )
        );
        return (isset($row['count'])) ? $row['count'] : 0;
    }

    function update_basic_status($data)
    {
        if (is_array($data)) {
            foreach ($data as $id => $status) {
                $this->db_master->update(
                    'lib_keywords', array('basic' => $status), array('id' => $id), 1
                );
            }
        }
    }

    function delete_keywords($ids)
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete(
                    'lib_keywords', array('id' => $id), 1
                );
            }
        }
    }

    function add_keyword($data)
    {
        if (isset($data['keyword'])) {
            $this->db_master->insert('lib_keywords', $data);
            return $this->db_master->insert_id();
        }
        return false;
    }

    function edit_keyword($id, $data)
    {
        if (isset($data['keyword']) && !empty($id)) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_keywords', $data);
            return true;
        }
        return false;
    }

    function get_keywords_list($filter, $limit)
    {
        $parts = array();
        $limit = $this->_create_limit_string($limit);
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {

                    $parts[] = "lib_keywords.{$field} LIKE '%{$value}%'";
                }
            }
            $string .= implode(' AND  ', $parts);
            $string = (!empty($string)) ? ' AND ' . $string : NULL;
        }

        $string = $string . " AND collection <>''";

//print_r($filter);
//$filter = $this->_create_filter_string($filter);
        $result = $this->db->query("
            SELECT lib_keywords.*
            FROM lib_keywords
            WHERE  1 
            {$string}
            {$limit};"
        );
        return $this->_get_array($result);
    }

    function get_collections_list()
    {

        $query = $this->db->get('lib_collections');
        $res = $query->result_array();
        return $res;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /* Keyword Tracking Model Functions */
    function search_keywords($data)
    {

        $keyword = $data['keyword'];
        $from = $data['dateFrom'];
        $to = $data['dateTo'];


//        $this->db->select('*');
//        $this->db->from('lib_clips_keywords');
//        $this->db->like('keyword', $keyword);
//        $this->db->where('time_created >=', $from);
//        $this->db->where('time_created <=', $to);
//        $this->db->group_by("keyword");
//        $query = $this->db->get();


        //$result = $this->db->query("SELECT primary_subject FROM lib_clips_content cc LEFT JOIN lib_clips c ON cc.clip_id = c.id WHERE cc.subject_category LIKE '%" . $keyword . "%' AND c.creation_date >= '" . $from . "' AND c.creation_date <= '" . $to . "'");
        $result = $this->db->query("SELECT keywords FROM lib_clips WHERE keywords LIKE '%" . $keyword . "%' AND ctime >= '" . $from . "' AND ctime <= '" . $to . "'");
        //echo $this->db->last_query();
        return $this->_get_array($result);


        //  $result = $query->result_array();
        // return $result;
    }

    function get_filter_keywords_count($filter)
    {
//$filter = $this->_create_keyword_filter_string($filter);
        $fil = $filter['keyword'];
        $row = $this->_get_row(
            $this->db->query("
                SELECT COUNT( lib_clips_keywords.id ) AS 'count'
                FROM lib_clips_keywords;"
            )
        );
        return (isset($row['count'])) ? $row['count'] : 0;
    }

    private function _create_keyword_filter_string($filter)
    {
        $parts = array();
        $string = NULL;
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $parts[] = "lib_clips_keywords.{$field} LIKE '%{$value}%'";
                }
            }
            $string = implode(' AND ', $parts);
            $string = (!empty($string)) ? ' AND ' . $string : NULL;
        }
//return $string;
        return 'WHERE lib_clips_keywords.time_created >= "' . date('Y-m-d', strtotime('-90 days')) . '"' . $string;
    }

    function get_filter_keywords_list($filter, $limit)
    {
        $limit = $this->_create_limit_string($limit);
        $filter = $this->_create_keyword_filter_string($filter);
//        $test = strstr($filter," ");
//        $fil = substr(strstr($test," "), 4);
        $result = $this->db->query("
            SELECT lib_clips_keywords.*
            FROM lib_clips_keywords
            {$filter}
            GROUP BY lib_clips_keywords.section_id
            {$limit};"
        );
        return $this->_get_array($result);
    }

}