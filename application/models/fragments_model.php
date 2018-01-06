<?php
class Fragments_model extends CI_Model {

    function Fragments_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_fragment_by_id($id)
    {  
        $query = $this->db->query('select * FROM lib_fragments where id = ' . intval($id));
        $row = $query->result_array();
        return $row[0]; 
    }

    function get_fragment_by_time($clip_id, $start_time, $end_time)
    {
        $query = $this->db->query('SELECT * FROM lib_fragments
            WHERE start_time = \'' . floatval($start_time) . '\'
            AND end_time = \'' . floatval($end_time) . '\'
            AND clip_id  = ' . (int)$clip_id);

        $row = $query->result_array();
        return $row[0];
    }

    #------------------------------------------------------------------------------------------------

    function save_fragment($clip_id, $start_time = 0.00, $end_time = 0.00)
    {
        $existing = $this->get_fragment_by_time($clip_id, $start_time, $end_time);
        if ($existing) {
            return $existing['id'];
        }

        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['clip_id'] = $clip_id;

        $this->db_master->insert('lib_fragments', $data);
        $id = $this->db_master->insert_id();
        return $id;
    }
   
}