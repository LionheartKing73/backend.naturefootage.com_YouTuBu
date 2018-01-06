<?php

class Pricingrfprice extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $group;

    function Pricingrfprice() {
        parent::__construct();
        $this->load->model('pricing_model');
        $this->load->model('groups_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Pricing / RF prices';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'price_level, price';
        $all = $this->pricing_model->get_rfprices_count();
        $data['rfprices'] = $this->pricing_model->get_rfprices_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('pricingrfprice/view', $all, $this->settings['perpage']);
        if ($this->group['is_admin']) {
            $data['is_admin'] = true;
        }
        $content = $this->load->view('pricing/rfprice_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Pricing / Edit RF price';
        else
            $this->path = 'Pricing / Add RF price';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->pricing_model->save_rfprice($this->id);
            if ($this->id) {
                redirect($this->langs . '/pricingrfprice/view');
            } else {
                redirect($this->langs . '/pricingrfprice/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->pricing_model->get_rfprice($this->id);
        }
        $data['lang'] = $this->langs;
        $this->load->model('discount_display_model');
        $data['discount_display_types'] = $this->discount_display_model->get_rf_types_list();
        if ($this->group['is_admin']) {
            $data['is_admin'] = true;
        }
        elseif($this->group['is_editor'] && $this->id){
            $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
            $row = $this->db->get_where('lib_provider_rf_exclusive_rate', array('provider_id' => $uid, 'use_id' => $this->id), 1)->result_array();
            if($row[0])
                $data['exclusive_rate'] = $row[0]['exclusive_rate'];
        }
        $data['getcollection_list'] = $this->get_collections();
        $content = $this->load->view('pricing/rfprice_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->pricing_model->delete_rfprices($ids);
        redirect($this->langs . '/pricingrfprice/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('license')
            || !$this->input->post('budgete_rate')
            || !$this->input->post('standard_rate')
            || !$this->input->post('premium_rate')
            || !$this->input->post('exclusive_rate')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    function set_group() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }
      function get_collections ( $selected = '' ) {
        if ( $selected && is_array( $selected ) )
            $this->db->where_not_in( 'id', $selected );
        $this->db->select( '*, name value' );
        $query = $this->db->get( 'lib_collections' );
        $rows = $query->result_array();
        return $rows;
    }
}