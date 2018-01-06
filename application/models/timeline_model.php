<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timeline_model extends CI_model {

    private $id;

    public function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    public function save() {

        $timeline_id = false;

        $timeline_json = $this->input->post('timeline');
        if($timeline_json){
            $data['json'] = mysql_real_escape_string($timeline_json);
            if($user_id = $this->session->userdata('client_uid')){
                $data['user_id'] = $user_id;
            }
            $this->db_master->insert('lib_timelines', $data);
            $timeline_id = $this->db_master->insert_id();
        }

        return $timeline_id;
    }

    function get_timelines_list($filter, $order = '', $limit = null) {
        $query = $this->db->query('SELECT t.id FROM lib_timelines t WHERE 1 ' . $filter . $order . $limit);
        $rows = $query->result_array();
        return $rows;
    }

    function get_timeline($id){
        $query = $this->db->query('SELECT * FROM lib_timelines WHERE id = ' . $id);
        $list = $query->result_array();
        return $list[0];
    }
}