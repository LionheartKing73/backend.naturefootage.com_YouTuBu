<?php

class News extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    
	function News()
	{
		parent::__construct();	

        $this->load->model('news_model','nm'); 
        $this->api->save_sort_order('news');  
        
        $this->id = $this->uri->segment(4);  
        $this->mode = $this->uri->segment(5); 
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings();
        
        $this->save_filter_data();   
	}
    
    #------------------------------------------------------------------------------------------------
    	
    function index()
    {
       $this->content();  
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function page()
    {
       $this->id = 0;
       $this->content();  
    }    
    
    #------------------------------------------------------------------------------------------------
    
	function content()
	{
        $temp['visual_mode'] = ($this->api->permission()) ? 1 : 0;  
        $temp['lang'] = $this->langs; 
         
        if($this->id){
          $data = $this->nm->get_news($this->id,$this->langs);  
          
          $temp['new'] = $data;
          $data['body'] = $this->load->view('news/content', $temp, true); 
        }
        else{

          $limit = $this->get_limit(); 
          $all = $this->nm->get_news_count($this->langs,'');  
          $pagination = $this->api->get_pagination('news/page',$all,$this->settings['perpage']); 
          
          $temp['news'] = $this->nm->get_news_list($this->langs, '', '', $limit);

          $data['title'] = $this->lang->line('news');
          $data['body'] = $this->load->view('news/content', $temp, true); 
        }
        $this->out($data, $pagination, 0); 
	}
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $filter = $this->get_filter_data();
        $order = $this->api->get_sort_order('news');
        $limit = $this->get_limit();

        $all = $this->nm->get_news_count($this->langs, $filter);
        
        $data['news'] = $this->nm->get_news_list($this->langs, $filter, $order, $limit); 
        $data['uri'] = $this->api->prepare_uri(); 
        $data['filter'] = $this->session->userdata('filter_news');  
        $data['lang'] = $this->langs;
        
        $this->path = 'Manage system / News';
        
        $content = $this->load->view('news/view', $data, true);
        $pagination = $this->api->get_pagination('news/view',$all,$this->settings['perpage']);
        
        $this->out($content, $pagination);    
    }
    
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->nm->change_visible($this->input->post('id'));
        $this->api->log('log_news_visible', $this->input->post('id')); 
        redirect($this->langs.'/news/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->nm->delete_news($ids);
        $this->api->log('log_news_delete', $ids); 
        redirect($this->langs.'/news/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
        $mode = ($this->mode) ? 3 : 1;

        if($this->input->post('delete')) {
            $this->nm->delete_image($this->input->post('sid'));
        }
        
        if($this->input->post('save') && $this->check_details()){

            $news_id = $this->nm->save_news($this->id, $this->langs);

            $this->nm->upload_image($news_id);
          
          if($this->id) $this->api->log('log_menu_edit', $this->id);   
          else $this->api->log('log_menu_new');
          
          if(!$this->mode)
          redirect($this->langs.'/news/view');
        }

        $data = ($this->error) ? $_POST : $this->nm->get_news($this->id,$this->langs);
        $data['picture'] = $this->nm->get_image_path($data);
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Manage system / News / ' . $action;
          
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['visual'] = ($this->mode=='visual');  
         
        $content = $this->load->view('news/edit', $data, true);  
        $this->out($content,'',$mode);
    }
    
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('annotation') || !$this->input->post('body')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }
        
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {  
       $words = $this->input->post('words');
       $active = $this->input->post('active');

       if($this->input->post('filter')){
         $temp['words'] = ($words) ? $words : '';
         $temp['active'] = ($active) ? $active : '';

         $this->session->set_userdata(array('filter_news'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_news = $this->session->userdata('filter_news');

        if($filter_news){
 
          $active = $filter_news['active'];  
          $words = $filter_news['words'];  
          
          if($active) $where[] = ($active==1) ? 'lp.active=1' : 'lp.active=0';
          if($words) $where[] = '(lpc.title like "%'.$words.'%" or lpc.body like "%'.$words.'%" or lpc.annotation like "%'.$words.'%")';  
          
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
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error), $type);
    }       
}
