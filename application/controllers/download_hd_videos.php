<?php

class Download_hd_videos extends CI_Controller {

    var $error;
    var $langs;

    function Download_hd_videos() {
        parent::__construct();

        $this->load->model('download_hd_videos_model');
    }

    #------------------------------------------------------------------------------------------------

    function index() {
	
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function view() {

        $this->path = 'Clips Section / Download HD Videos';

        $content = $this->load->view('download_hd_videos/view', $data, true);
    
    }

    #------------------------------------------------------------------------------------------------
    
	

}
