<?php
class Submission_codecs_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    //Digital file formats
    function get_submission_codecs_count() {
        return $this->db->count_all('lib_submission_codecs');
    }

    function get_submission_codecs_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_submission_codecs');
        $res = $query->result_array();
        return $res;
    }

    function save_submission_codec($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_submission_codecs', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_submission_codecs', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_submission_codec($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_submission_codecs');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_submission_codecs($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_submission_codecs', array('id' => $id));
            }
        }
    }

    function get_submission_to_delivery_data_for_ajax () {
        $this->db->select('name, delivery_category');
        $query = $this->db->get('lib_submission_codecs');
        $res = $query->result_array();
        $formatted = array();
        foreach($res as $item) {
            $formatted[$item['name']] = $item['delivery_category'];
        }
        return $formatted;
    }

}