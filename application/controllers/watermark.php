<?php

class Watermark extends CI_Controller {

    var $id; 
    var $langs;
    var $settings;
    var $error;
    
	function Watermark()
	{
		parent::__construct();	
        
        $this->load->model('watermark_model','wm');  

        $this->id = $this->uri->segment(4);
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings();
	}
	
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
     
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        if($this->input->post('upload')) 
        {
          if (!$this->wm->upload_image())
          {
            $this->error = $this->lang->line('invalid_file_type');
          }
        }
       // if($this->input->post('delete')) $this->im->delete_thumbs($this->id, $this->langs);
    
        $watermark = $this->wm->get_watermark();
        
        $data['image'] = $watermark['image'] ? $this->config->item('wm_path').$watermark['image'] : '';
        $data['text'] = $watermark['text'];
        $data['lang'] = $this->langs;
        
        $content = $this->load->view('watermark/view', $data, true);  
        $this->out($content); 
    }
    

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
      if($this->input->post('save')){
        $this->wm->save_watermark();
        
        $this->api->log('log_watermark_edit');   
         
        redirect($this->langs.'/watermark/view'); 
      }
        
      $data = $this->wm->get_watermark();  
       
      $content = $this->load->view('watermark/edit', $data, true);  
      $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function delete()
    {
      $this->wm->delete_watermark();
      
      $this->api->log('log_watermark_image_delete');   
       
      redirect($this->langs.'/watermark/view'); 
      
    }
    
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null, $type=1)
    {        
        $this->builder->output(array('content'=>$content,'pagination'=>$pagination,'error'=>$this->error),$type);    
    }
    
}
?>
