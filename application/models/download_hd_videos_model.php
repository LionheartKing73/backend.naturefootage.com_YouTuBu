<?php

class Download_hd_videos_model extends CI_Model {

    function Download_hd_videos_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #-----------------------------------------------------------------------------------------------

}

