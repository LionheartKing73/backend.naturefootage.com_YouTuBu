<?php

class Hints_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_hints_count() {
        return $this->db->count_all('lib_hints');
    }

    function get_hints_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_hints');
        $res = $query->result_array();
        return $res;
    }

    function save_hint($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_hints', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_hints', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_hint($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_hints');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_hints($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_hints', array('id' => $id));
            }
        }
    }

    function get_fields_list(){
        return array(
            array('name' => 'clip_description', 'title' => 'Clip Description'),
            array('name' => 'clip_notes', 'title' => 'Clip Notes'),
            array('name' => 'date_filmed', 'title' => 'Date Filmed'),
            array('name' => 'collection', 'title' => 'Collection'),
            array('name' => 'brand', 'title' => 'Brand'),
            array('name' => 'add_collection', 'title' => 'Collection'),
            array('name' => 'license_type', 'title' => 'License Type'),
            array('name' => 'price_level', 'title' => 'Price Level'),
            array('name' => 'released', 'title' => 'Releases'),
            array('name' => 'property_released', 'title' => 'Property Released'),
            array('name' => 'model_property_released', 'title' => 'Model and Property Released'),
            array('name' => 'file_formats', 'title' => 'File Formats'),
            array('name' => 'shot_type', 'title' => 'Shot Type'),
            array('name' => 'subject_category', 'title' => 'Subject Category'),
            array('name' => 'primary_subject', 'title' => 'Primary Subject'),
            array('name' => 'other_subject', 'title' => 'Other Subject'),
            array('name' => 'appearance', 'title' => 'Appearance'),
            array('name' => 'actions', 'title' => 'Actions'),
            array('name' => 'time', 'title' => 'Time'),
            array('name' => 'habitat', 'title' => 'Habitat'),
            array('name' => 'concept', 'title' => 'Concept'),
            array('name' => 'location', 'title' => 'Location'),
            array('name' => 'country', 'title' => 'Country'),
        );
    }
}