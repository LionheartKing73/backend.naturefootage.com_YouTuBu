<?php

/**
 * @property Keywords_model $km
 */
class Logging_rules extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('logging_rules_model', 'km');
        $this->langs = $this->uri->segment(1);
        $this->id = $this->uri->segment(4);
        $this->settings = $this->api->settings();
    }

    function view() {
        $this->process_basic_status();
        $limit = $this->_get_limit();
        $this->path = 'Library settings / Logging rules';
        $paging = $this->api->get_pagination(
                'logging_rules/view', $this->km->get_keywords_count(
                        $this->_get_filter()
                ), $this->settings['perpage']
        );
        $data = array(
            'lang' => $this->langs,
            'paging' => $paging,
            'filter' => $this->_get_filter(),
            'sections' => $this->_get_sections(),
            'keywords' => $this->km->get_keywords_list(
                    $this->_get_filter(), $limit
            )
        );
        $this->out(
                $this->load->view('logging_rules/view', $data, TRUE)
        );
    }

    function add() {
        $data = array(
            'keyword1' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword1')),
            'keyword2' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword2')),
            'rule' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('rule')),
            'section_id' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section_id')),
        );

        if (isset($data['keyword1'])) {
            $this->km->add_keyword($data);
        }
        redirect('logging_rules/view');
    }

    function edit($id) {
        $data = array(
            'keyword1' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword1')),
            'keyword2' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('keyword2')),
            'rule' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('rule')),
            'section_id' => preg_replace('/<|>|\'|"|\(|\)/i', '', $this->input->post('section_id')),
        );
        if (isset($data['keyword1']) && !empty($id)) {
            $this->km->edit_keyword($id, $data);
        }
        redirect('logging_rules/view');
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
        redirect('logging_rules/view');
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
        $data_content['shot_type'] = 'shot_type';
        $data_content['subject_category'] = 'subject_category';
        $data_content['primary_subject'] = 'primary_subject';
        $data_content['other_subject'] = 'other_subject';
        $data_content['appearance'] = 'appearance';
        $data_content['actions'] = 'actions';
        $data_content['time'] = 'time';
        $data_content['habitat'] = 'habitat';
        $data_content['concept'] = 'concept';
        $data_content['location'] = 'location';
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
