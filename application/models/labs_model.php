<?php

/**
 * Class Labs_model
 * @property CI_DB_active_record $db_master
 */
class Labs_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_labs_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_labs');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_labs');
    }

    function get_labs_id_list(){
        $query = $this->db->query("SELECT * FROM lib_labs");
        $res = array();

        foreach($query->result_array() as $row){
            $res[] = $row['id'];
        }

        return array_values($res);
    }

    function get_labs_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_labs');
        $res = $query->result_array();
        return $res;
    }

    function save_lab($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_labs', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_labs', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_lab($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_labs');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_labs($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_labs', array('id' => $id));
            }
        }
    }

    function get_lab_ids_by_user_id($user_id){
        $query = $this->db->query("SELECT lab_id FROM lib_labs_users WHERE user_id=" . $user_id);
        $res = array();
        foreach($query->result_array() as $row){
            $res['lab_id'] = $row['lab_id'];
        }
        return array_values($res);
    }

    function get_lab_user_ids($id){
        $this->db->select('user_id');
        $this->db->where('lab_id', $id);
        $query = $this->db->get('lib_labs_users');
        $res = array();
        foreach($query->result_array() as $row){
            $res[] = $row['user_id'];
        }
        return $res;
    }

    function save_lab_users($lab_id, array $user_ids, array $selected_user_ids){
        //empty previous values
        $this->db_master->where('lab_id', $lab_id);
        $this->db_master->where_in('user_id', $user_ids);
        $this->db_master->delete('lib_labs_users');

        //insert selected user ids
        foreach($selected_user_ids as $id){
            $this->db_master->set('lab_id', $lab_id);
            $this->db_master->set('user_id', $id);
            $this->db_master->insert('lib_labs_users');
        }
    }

    function get_available_users($group_id, $lab_id, $filter = array(), $limit = array(), $order_by = ''){
        //get excluded user_ids, for this lab
        $excluded_ids = array();
        $this->db->where_not_in('lab_id', $lab_id);
        $query = $this->db->get('lib_labs_users');
        foreach($query->result_array() as $row){
            $excluded_ids[$row['user_id']] = $row['user_id'];
        }

        $this->db->select('lib_users.*');
        $this->db->join('lib_users_groups', 'lib_users.group_id=lib_users_groups.id');
        $this->db->where('lib_users_groups.id', $group_id);
        if(!empty($excluded_ids)){
            $this->db->where_not_in('lib_users.id', $excluded_ids);
        }

        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit){
            $this->db->limit($limit['perpage'], $limit['start']);
        }
        if($order_by){
            $this->db->order_by($order_by);
        }

        $query = $this->db->get('lib_users');

        $res = $query->result_array();
        return $res;
    }
}