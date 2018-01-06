<?php

class Download_buckets extends CI_Controller {

    var $error;
    var $langs;

    function Download_buckets() {
        parent::__construct();

        $this->load->model('download_buckets_model', 'bm');
    }

    #------------------------------------------------------------------------------------------------

    function index() {
	
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function view() {

        if ($this->input->post('downloadFiles')) {
            $this->bm->DownloadContent($this->input->post('values'));
        }

        $data['bucket_List'] = $this->bm->get_bucket_list();

        $this->path = 'Clips Section / Download From s3';

        $content = $this->load->view('download_buckets/view', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------
    #------------------------------------------------------------------------------------------------

    function out($content = null, $pagination = null) {
//        echo 'out';die;
        $this->builder->output(array('content' => $content, 'path' => $this->path,
            'pagination' => $pagination, 'error' => $this->error, 'message' => $this->message), 1);
    }

}
