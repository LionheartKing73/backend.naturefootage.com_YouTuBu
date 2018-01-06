<?php

class Discounts_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get() {
        $this->db->order_by('item_count');
        $list = $this->db->get('lib_discounts')->result();
        return $list;
    }

    #-----------------------------------------------------------------------------

    function load() {
        $row = $this->db->get_where('lib_discounts', array('id'=>$this->id))
            ->result_array();
        if ($row) {
            $row = $row[0];
            foreach ($row as $key=>$value) {
                $this->$key = $value;
            }
        }
    }

    #-----------------------------------------------------------------------------

    function active() {
        $this->db_master->query('UPDATE lib_discounts
      SET active = ABS(active - 1) WHERE id = ?', $this->id);
    }

    #-----------------------------------------------------------------------------

    function delete() {
        $this->db_master->delete('lib_discounts', array('id'=>$this->id));
    }

    #-----------------------------------------------------------------------------

    function save() {
        $data = array(
            'item_count' => intval($this->input->post('item_count')),
            'discount' => floatval($this->input->post('discount'))
        );
        if ($this->id) {
            $this->db_master->update('lib_discounts', $data, array('id'=>$this->id));
        } else {
            $this->db_master->insert('lib_discounts', $data);
            $this->id = $this->db_master->insert_id();
        }
    }

    #-----------------------------------------------------------------------------

    function calc_discount($count) {
        $data = $this->db->query(
            'SELECT discount
      FROM lib_discounts
      WHERE item_count <= ?
      ORDER BY item_count DESC
      LIMIT 1', $count)->result();
        if ($data) {
            return $data[0]->discount;
        } else {
            return 0;
        }
    }

    function get_duration_discounts(){
        $query = $this->db->get('lib_pricing_discounts');
        $res = $query->result_array();
        return $res;
    }

    function get_count_discounts(){
        $query = $this->db->get('lib_rf_pricing_discounts');
        $res = $query->result_array();
        return $res;
    }

    function get_duration_discount($duration, $discount_type){
        $discounts = $this->get_duration_discounts();
        $discount = false;
        $discount_value = 0;
        foreach($discounts as $item){
//            check duration and discount type
            if($duration > $item['duration'] && $item['discount'] > $discount_value && $discount_type == $item['type']){
                $discount_value = $item['discount'];
                $discount = $item;
            }
        }
        return $discount;
    }

    function get_count_discount($count){
        $discounts = $this->get_count_discounts();
        $discount = false;
        $discount_value = 0;
        if($count && $discounts){
            foreach($discounts as $item){
                if($count >= $item['count'] && $item['discount'] > $discount_value){
                    $discount_value = $item['discount'];
                    $discount = $item;
                }
            }
        }
        return $discount;
    }

}