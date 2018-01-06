<?php

/**
 * @property Keywords_model $km
 */
class Keywordtracking extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('keywords_model', 'km');
        $this->langs = $this->uri->segment(1);
        $this->id = $this->uri->segment(4);
        $this->settings = $this->api->settings();
    }

    function view() {
        if (isset($_POST['submit'])) {
            $keyword = $this->input->post('filter');
            $dateFrom = date('Y-m-d', strtotime($this->input->post('dateFrom')));
            $dateTo = date('Y-m-d', strtotime($this->input->post('dateTo')));
            $da = array(
                'keyword' => $keyword,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            );
            $keywords_res = $this->km->search_keywords($da);
//            $paging = $this->api->get_pagination(
//                    'keywordtracking/view', $this->km->get_filter_keywords_count(
//                            $this->_get_filter()
//                    ), $this->settings['perpage']
//            );
        }
        $this->process_basic_status();
        $limit = $this->_get_limit();
        $this->path = 'Library settings / Keywords Tracking';

        $data = array(
            'lang' => $this->langs,
            'paging' => $paging,
            'filter' => $this->_get_filter(),
            'result' => $keywords_res,
//            'keywords' => $this->km->get_filter_keywords_list(
//                    $this->_get_filter(), $limit
//            )
        );
//        'filter' => $this->_get_filter(),
//        'sections' => $this->_get_sections(),
//        'collection' => $this->km->get_collections_list(),
//        'keywords' => $this->km->get_keywords_list(
//        $this->_get_filter(),
//        $limit
//        )
        $this->out($this->load->view('keywordtracking/view', $data, TRUE));
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
        $time = $this->input->post('collection');
        if (!$filter) {
            $filter = $this->session->userdata('keywords-filter-search');
        } else {
            $this->session->set_userdata('keywords-filter-search', $filter);
        }
        return array(
            'keyword' => ( isset($filter) ) ? $filter : NULL,
            'time_created' => ( isset($time) ) ? $time : NULL,
        );
    }

    function _get_sections() {
        $data_content = array();
        $data_content['shot_type'] = '';
        $data_content['subject_category'] = '';
        $data_content['primary_subject'] = '';
        $data_content['other_subject'] = '';
        $data_content['appearance'] = '';
        $data_content['actions'] = '';
        $data_content['time'] = '';
        $data_content['habitat'] = '';
        $data_content['concept'] = '';
        $data_content['location'] = '';
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
