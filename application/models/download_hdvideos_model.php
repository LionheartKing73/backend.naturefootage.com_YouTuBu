<?php

class Download_hdvideos_model extends CI_Model {

    function Download_hdvideos_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

   
    #------------------------------------------------------------------------------------------------
    
    function save_hdvideos($id)
    {
        $data_content['hdvideochoice'] = $this->input->post('hdvideochoice');
        if($id){
          $this->db_master->where('id', $id);
          $this->db_master->update('lib_hdvideos', $data_content);
        }
        
    }
	
	function get_hdvalue()
	{	
		$id=1;
		$query = $this->db->query('SELECT * FROM lib_hdvideos WHERE id = 1', $id);
    	$row = $query->result_array();
    	return $row[0];
	}
}

