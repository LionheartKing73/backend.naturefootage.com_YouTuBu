<?php

class Download_hdvideos extends CI_Controller {

    var $error;
    var $langs;
	var $id;
    var $settings;


    function Download_hdvideos() {
        parent::__construct();

        $this->load->model('download_hdvideos_model','pm');
		
       
    }

    #------------------------------------------------------------------------------------------------

    function index() {
        show_404();
    }

    #------------------------------------------------------------------------------------------------
 
	function view() {
		$data = $this->pm->get_hdvalue();
        $this->path = 'Clips Section / Download HD Videos';
        $content = $this->load->view('download_hdvideos/view', $data, true);
		$this->out($content); 
    }
	
	
	function edit() {
		$id=1;
		if($id)
            $this->path = 'Clips Section / Edit Your Options';
		
		if($this->input->post('save'))
		{
      		$this->pm->save_hdvideos(1);
        }
		$data = $this->pm->get_hdvalue();
        $content = $this->load->view('download_hdvideos/edit', $data, true);
		$this->out($content); 
    
    }
	
    #------------------------------------------------------------------------------------------------
    function checkvalueindb () {

        // this is because I CAN NOT put it in separate action because of permissions logic
        if (!empty($_REQUEST['no_direct_output'])) {
            return $this->checkfromdb();
        }
    }

    private function checkfromdb()
    {
        header('Content-Type: application/json;');
        echo json_encode($this->pm->get_hdvalue());

        die();
    }
	function out($content=null, $pagination=null)
  {        
      $this->builder->output(array('content'=>$content,'path'=>$this->path,
        'pagination'=>$pagination,'error'=>$this->error),1);
  }

}
