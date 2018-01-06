<?php

class Tagcloud extends CI_Controller {

    var $id;
    var $langs;
    var $error;
    
	function Tagcloud()
	{
		parent::__construct();	
 
        $this->load->model('tagcloud_model','tm');

        $this->id = intval($this->uri->segment(4));  
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings();
        $this->save_filter_data();  
	}
        	
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
        show_404();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $filter = $this->get_filter_data();
        $order = $this->get_sort();
        $limit = $this->get_limit(); 

        $all = $this->tm->get_tags_count($this->langs, $filter);

        $data['phrases'] = $this->tm->get_tags_list($this->langs, $filter, $order, $limit);
        $data['uri'] = $this->api->prepare_uri(); 
        $data['lang'] = $this->langs;
        $data['filter'] = $this->session->userdata('filter_tagcloud');
        
        $this->path = 'Manage system / Tagcloud';
        
        $content = $this->load->view('tagcloud/view', $data, true);
        $pagination = $this->api->get_pagination('tagcloud/view',$all,$this->settings['perpage']);
        
        $this->out($content, $pagination);    
    }

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {   
        $filter = $this->session->userdata('filter_tagcloud');    

        if($this->input->post('save') && $this->check_details()){ 
           $this->tm->save_tag($this->id, $this->langs, $filter['type']);
           
           if($this->id) $this->api->log('log_tagcloud_edit', $this->id);   
           else $this->api->log('log_tagcloud_new');
          
           redirect($this->langs.'/tagcloud/view');     
        }
        
        $data = ($this->error) ? $_POST : $this->tm->get_tag($this->id, $this->langs);
                  
        $data['filter'] = $filter['type'];  
        $data['lang'] = $this->langs;
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Manage system / Tagcloud / ' . $action;
 
        $content = $this->load->view('tagcloud/edit', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function move() {
    	$this->tm->move_to_stop($this->id);
    	redirect($this->langs.'/tagcloud/view');
    }
    
    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->tm->delete_tags($ids);
        $this->api->log('log_tagcloud_delete', $ids); 
        redirect($this->langs.'/tagcloud/view');
    }   
      
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('phrase')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }
        
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {
      if($this->input->post('sort')){
         if($this->input->post('sort')) $temp['sort'] = intval($this->input->post('sort'));
         else $temp['sort'] = 0;
       }
    
       if($this->input->post('filter')){
         if($this->input->post('filter')) $temp['type'] = intval($this->input->post('type'));
         else $temp['type'] = 0;
         
         $this->session->set_userdata(array('filter_tagcloud'=>$temp)); 
       }
    }
    
    #------------------------------------------------------------------------------------------------

    function get_sort() {
      $filter_tagcloud = $this->session->userdata('filter_tagcloud');

      if($filter_tagcloud){
        $type = intval($filter_tagcloud['type']);
        if ($type == 2) {
          return ' ORDER BY phrase ';
        }
        $sort = intval($filter_tagcloud['sort']);
        $sort_clauses = array(
          array(
            ' ORDER BY phrase ',
            ' ORDER BY phrase DESC ',
            ' ORDER BY times, phrase ',
            ' ORDER BY times DESC, phrase '
          ),
          array(
            ' ORDER BY phrase ',
            ' ORDER BY phrase DESC ',
            ' ORDER BY weight, phrase ',
            ' ORDER BY weight DESC, phrase '
          )
        );
        return $sort_clauses[$type][$sort];
      }
      return ' ORDER BY phrase ';
    }

    #------------------------------------------------------------------------------------------------
            
    function get_filter_data()
    {  
        $filter_tagcloud = $this->session->userdata('filter_tagcloud');

        if($filter_tagcloud){
          $type = $filter_tagcloud['type'];  
          return ($type) ? 'type='.$type : 'type=0';
        }    
        return 'type=0';
    } 
        
    #------------------------------------------------------------------------------------------------
        
    function get_limit()
    { 
        return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
    }
            
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error), 1);
    }       
}
