<?php

class Currencies extends CI_Controller {

    var $id; 
    var $langs;
    var $settings;
    var $error;
    
	function Currencies()
	{
		parent::__construct();	
        
        $this->load->model('currencies_model','cm');  
        
        $this->langs = $this->uri->segment(1);   
        $this->id = $this->uri->segment(4);
        
        $this->save_filter_data();  
	}
    
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
        show_404();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view($data=null)
    {
        $filter = $this->get_filter_data(); 
        
        $data['currencies'] = $this->cm->get_currencies($filter);
        $data['lang'] = $this->langs;
        $data['filter'] = $this->session->userdata('filter_currencies');

        $this->path = 'Library settings / Currencies';
        
        $content = $this->load->view('currencies/view', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function edit()
    {  
       if($this->input->post('save') && $this->check_details()){
         $this->cm->save_currency($this->id);  
         redirect($this->langs.'/currencies/view');
       }

       $data = ($this->error) ? $_POST : $this->cm->get_currency($this->id);
       $data['id'] = $this->id;
       $data['lang'] = $this->langs;
       $data['set_default'] = $this->cm->check_default();
       
       $action = $this->id ? 'Edit' : 'Add';
       $this->path = 'Library settings / Currencies / ' . $action;
       
       $content = $this->load->view('currencies/edit', $data, true);  
       $this->out($content); 
    }      
                     
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->cm->change_visible($this->input->post('id'));
        redirect($this->langs.'/currencies/view');
    } 
   
    #------------------------------------------------------------------------------------------------
    
    function delete()
    {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->cm->delete_currencies($ids);
        redirect($this->langs.'/currencies/view');  
    }
        
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('code') || !$this->input->post('rate')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }
           
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {  
       $active = $this->input->post('active');

       if($this->input->post('filter')){
         $temp['active'] = ($active) ? $active : '';
         $this->session->set_userdata(array('filter_currencies'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_currencies = $this->session->userdata('filter_currencies');

        if($filter_currencies){
 
          $active = $filter_currencies['active'];  

          if($active) $where[] = ($active==1) ? 'lc.active=1' : 'lc.active=0';  
          if(count($where)) return ' and '.implode(' and ',$where);
        }
        return '';
    }    
    
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null, $type=1)
    {        
        $this->builder->output(array('content'=>$content,'path'=>$this->path,
          'pagination'=>$pagination,'error'=>$this->error),$type);
    }       
}
?>
