<?php

class Licensing_model extends CI_Model {

    function get() {
        $query = $this->db->get('lib_licensing');
        $list = $query->result_array();
        $query->free_result();
        return $list;
    }

#------------------------------------------------------------------------------------------------

    function find($id) {
        $query = $this->db->get_where('lib_licensing', array('id' => $id));
        $row = $query->result_array();
        $query->free_result();
        if (count($row)) {
            return $row[0];
        }
    }

#------------------------------------------------------------------------------------------------

    function save($id, $data) {
        $this->db_master = $this->load->database('master', TRUE);
        $query = $this->db_master->update('lib_licensing', $data, array('id' => $id));
    }

    function update_license_by_order_id($id, $data){

        $this->db_master = $this->load->database('master', TRUE);

        $this->db_master->where('order_id', $id);
        $this->db_master->update('lib_order_license', $data);
    }

    function update_billing_by_order_id($id, $data){

        $this->db_master = $this->load->database('master', TRUE);

        $this->db_master->where('order_id', $id);
        $this->db_master->update('lib_order_billing', $data);
    }

    function get_order_billing_info($order_id){

        return $this->db->query("SELECT * FROM lib_order_billing WHERE order_id = '".$order_id."' ")->result_array();
    }

    function get_restrictions($id, $order_id){

        $restrictions = $this->db->query("SELECT restrictions FROM lib_orders WHERE id = '".$order_id."'")->result_array();

        if ($restrictions[0]['restrictions'] != ''){
            return $restrictions[0]['restrictions'];
        }else{
            $restrictions = $this->db->query("SELECT exclusions FROM lib_pricing_use WHERE id = '".$id."' LIMIT 1 ")->result_array();
            return $restrictions[0]['exclusions'];
        }

    }
}