<?php

class Customers extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    
	function Customers()
	{
		parent::__construct();	

        $this->load->model('customers_model','cm'); 
        $this->api->save_sort_order('customers');  
        
        $this->id = $this->uri->segment(4);  
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings();
        
        $this->save_filter_data();   
	}
    
    #------------------------------------------------------------------------------------------------
    	
    function index()
    {
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $filter = $this->get_filter_data();
        $order = $this->api->get_sort_order('customers');
        $limit = $this->get_limit();
        
        $all = $this->cm->get_customers_count($filter);
        
        $data['customers'] = $this->cm->get_customers_list($filter, $order, $limit); 
        $data['uri'] = $this->api->prepare_uri(); 
        $data['filter'] = $this->session->userdata('filter_customers');  
        $data['lang'] = $this->langs;
        
        $this->path = 'Commerce / Customers';
        
        $content = $this->load->view('customers/view', $data, true);
        $pagination = $this->api->get_pagination('customers/view',$all,$this->settings['perpage']);
        
        $this->out($content, $pagination);    
    }
    
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->cm->change_visible($this->input->post('id'));
        $this->api->log('log_customers_visible', $this->input->post('id')); 
        redirect($this->langs.'/customers/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->cm->delete_customers($ids);
        $this->api->log('log_customers_delete', $ids); 
        redirect($this->langs.'/customers/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function details()
    {
        if($this->input->post('save')) $this->cm->save_corporate_data($this->id);

        $data['user'] = $this->cm->get_customer($this->id);
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        
        $this->path = 'Commerce / Customers / Details';
         
        $content = $this->load->view('customers/details', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {  
       $words = $this->input->post('words');
       $active = $this->input->post('active');

       if($this->input->post('filter')){
         $temp['words'] = ($words) ? $words : '';
         $temp['active'] = ($active) ? $active : '';

         $this->session->set_userdata(array('filter_customers'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_customers = $this->session->userdata('filter_customers');

        if($filter_customers){
 
          $active = $filter_customers['active'];  
          $words = $filter_customers['words'];  
          
          if($active) $where[] = ($active==1) ? 'lc.active=1' : 'lc.active=0';
          if($words) $where[] = '(lc.fname like "%'.$words.'%" or lc.lname like "%'.$words.'%" or lc.email like "%'.$words.'%")';
          
          if(count($where)) return ' and '.implode(' and ',$where);
        }
        return '';
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_limit()
    {    
        return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
    }
    
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null, $type=1)
    {        
      $this->builder->output(array('content'=>$content,'path'=>$this->path,
        'pagination'=>$pagination,'error'=>$this->error),$type);
    }       
}
