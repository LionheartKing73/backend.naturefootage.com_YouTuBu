<?php

/**
 * @property Keywords_model $km
 */
class Keywords_front extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('keywords_front_model', 'km');
        $this->langs = $this->uri->segment(1);
        $this->id = $this->uri->segment(4);
        $this->settings = $this->api->settings();
    }

    function view() {
        $this->process_basic_status();
        $limit = $this->_get_limit();
        $this->path = 'Library settings / Keywords';
        $paging = $this->api->get_pagination(
                'keywords_front/view', $this->km->get_keywords_count(
                        $this->_get_filter()
                ), $this->settings['perpage']
        );
        $data = array(
            'lang' => $this->langs,
            'paging' => $paging,
            'filter' => $this->_get_filter(),
            'sections' => $this->_get_sections(),
            //'sections' => $this->km->get_frontends_list(),
            'keywords' => $this->km->get_keywords_list(
                    $this->_get_filter(), $limit
            )
        );
        $this->out(
                $this->load->view('keywords_front/view', $data, TRUE)
        );
    }

    function add() {
        $data = array(
            'keyword' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword')),
            'front_id' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section')),
            'section' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section')),
        );
        if (isset($data['keyword'])) {
            $this->km->add_keyword($data);
        }
        redirect('keywords_front/view');
    }

    function edit($id) {
        $data = array(
            'keyword' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword')),
            'front_id' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section')),
            'section' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section')),
        );
        if (isset($data['keyword']) && !empty($id)) {
            $this->km->edit_keyword($id, $data);
        }
        redirect('keywords_front/view');
    }

    function delete() {
        if ($this->input->post('id')) {
            $ids = $this->input->post('id');
        } else {
            $ids = array($this->id);
        }
        if ($ids) {
            $this->km->delete_keywords($ids);
        }
        redirect('keywords_front/view');
    }

    function process_basic_status() {
        if ($this->input->post('basic')) {
            $this->km->update_basic_status($this->input->post('basic'));
        }
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

    function _get_sections() {
        $data_content = array();
        $data_content['shot_type'] = 'Shot Type';
        $data_content['subject_category'] = 'Subject Category';
        $data_content['primary_subject'] = 'Primary Subject';
        $data_content['other_subject'] = 'Other Subject';
        $data_content['appearance'] = 'Appearance';
        $data_content['actions'] = 'Actions';
        $data_content['time'] = 'Time';
        $data_content['habitat'] = 'Habitat';
        $data_content['concept'] = 'Concept';
        $data_content['location'] = 'Location';
        return $data_content;
    }

    function out($content = NULL, $pagination = NULL, $type = 1) {
        $this->builder->output(
                array(
            'content' => $content,
            'path' => $this->path,
            'pagination' => $pagination,
            'error' => $this->error,
            'message' => $this->message
                ), $type
        );
    }

}
