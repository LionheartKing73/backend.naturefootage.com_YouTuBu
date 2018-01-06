<?php

/**
 * @property Sections_model $km
 */
class Sections extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('sections_model', 'km');
        $this->langs = $this->uri->segment(1);
        $this->id = $this->uri->segment(4);
        $this->settings = $this->api->settings();
    }

    function view() {
        $limit = $this->_get_limit();
        $this->path = 'Manage system / Sections';
        $paging = $this->api->get_pagination(
                'sections/view', $this->km->get_section_count(
                        $this->_get_filter()
                ), $this->settings['perpage']
        );
        $data = array(
            'lang' => $this->langs,
            'paging' => $paging,
            'filter' => $this->_get_filter(),
            'sections' => $this->km->get_section_list(
                    $this->_get_filter(), $limit
            )
        );
        $content = $this->load->view('sections/view', $data, true);
        print_r($content);
        $this->out($content);
    }

    function add() {
        $data = array(
            'name' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('name')),
        );
        if (isset($data['name'])) {
            $this->km->add_section($data);
        }
        redirect('sections/view');
    }

    function edit($id) {
        $data = array(
            'name' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('name')),
        );
        if (isset($data['name']) && !empty($id)) {
            $this->km->edit_section($id, $data);
        }
        redirect('sections/view');
    }

    function delete() {
        if ($this->input->post('id')) {
            $ids = $this->input->post('id');
        } else {
            $ids = array($this->id);
        }
        if ($ids) {
            $this->km->delete_section($ids);
        }
        redirect('sections/view');
    }

    function _get_limit() {
        return array(
            (integer) $this->uri->segment(4),
            $this->settings['perpage']
        );
    }

    function _get_filter() {
        $filter = $this->input->post('filter');
        if (!$filter) {
            $filter = $this->session->userdata('keywords-filter');
        } else {
            $this->session->set_userdata('keywords-filter', $filter);
        }
        return array(
            'keyword' => ( isset($filter['keyword']) ) ? $filter['keyword'] : NULL,
            'collection' => ( isset($filter['collection']) ) ? $filter['collection'] : NULL,
            'section' => ( isset($filter['section']) ) ? $filter['section'] : NULL,
        ); 
    }

}
