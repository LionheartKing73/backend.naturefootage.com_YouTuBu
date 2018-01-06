<?php

class Editors extends CI_Controller {

   var $langs;
   var $method; 
   var $error;
    
   function Editors()
   {
      parent::__construct();	
        
      $this->load->model('editors_model','em'); 
      $this->api->save_sort_order('editors');
      
      $this->settings = $this->api->settings();
      $this->langs = $this->uri->segment(1); 
      $this->method = $this->uri->segment(3); 
      $this->id = $this->uri->segment(4); 
      $this->page = $this->uri->segment(5);
      
      $this->save_filter_data(); 
   }
	
   #------------------------------------------------------------------------------------------------         
  
   function index()
   {  
      switch($this->method){
        case 'profile': $this->profile();break;
        case 'portfolio': $this->portfolio();break;
        case 'cats': $this->cats();break;
        case 'testimonials': $this->testimonials();break;
        default: $this->profile();
      } 
   }
  
   #------------------------------------------------------------------------------------------------
    
   function view()
   { 
      $filter = $this->get_filter_data();
      $order = $this->api->get_sort_order('editors');
      $limit = $this->get_limit();
      $all = $this->em->get_editors_count($filter);
        
      $data['editors'] = $this->em->get_editors_list($filter, $order, $limit); 
      $data['uri'] = $this->api->prepare_uri(); 
      $data['filter'] = $this->session->userdata('filter_editors');  
      $data['lang'] = $this->langs;
        
      $content = $this->load->view('editors/view', $data, true);
      $pagination = $this->api->get_pagination('editors/view',$all,$this->settings['perpage']);
      
      $this->path = 'Content providers / Providers list';
        
      $this->out($content, $pagination, 1);    
   }
   
   #------------------------------------------------------------------------------------------------
        
   function visible()
   {   
      $this->em->change_visible($this->input->post('id'));
      $this->api->log('log_editors_visible', $this->input->post('id')); 
      redirect($this->langs.'/editors/view');
   } 
   
   #------------------------------------------------------------------------------------------------
        
   function delete()
   {   
      if($this->id) $ids[] = $this->id;
      else $ids = $this->input->post('id'); 
        
      $this->em->delete_editors($ids);
      $this->api->log('log_editors_delete', $ids); 
      redirect($this->langs.'/editors/view');
   }  
   
   #------------------------------------------------------------------------------------------------
        
   function details()
   {
      if($this->input->post('save')) $this->em->save_commision_data($this->id);
 
      $data['user'] = $this->em->get_editor($this->id);
      $data['id'] = ($this->id) ? $this->id : '';
      $data['lang'] = $this->langs;
      
      $this->path = 'Content providers / Provider details';
         
      $content = $this->load->view('editors/details', $data, true);         
      $this->out($content, 0, 1);  
   }
    
   #------------------------------------------------------------------------------------------------    
  
   function profile()
   {  
      $this->load->model('search_model','sm'); 
      $filter = " and li.client_id = ".$this->id;
      $limit = $this->sm->prepare_limit(0, 8);
      $order = "order by downloaded DESC";
      $data['im'] = $this->sm->get_search_results($this->langs, $filter, $limit, $order);  

      $data['lang'] = $this->langs;
      $data['id'] = $this->id; 
      $data['profile'] = $this->em->get_profile($this->id);
      $data['results'] = $this->load->view('main/ext/items', $data, true);
      $data['content_count'] = $this->em->get_content_count($this->id);

      $content['title'] = $this->lang->line('profile'); 
      $content['body'] = $this->load->view('editors/profile', $data, true);
      $this->out($content);    
   }

   #------------------------------------------------------------------------------------------------
   
   function portfolio()
   {
      $this->load->model('search_model','sm');  
    
      $perpage = $this->input->post('perpage', true);
      
      if($perpage){
        $session_data['search_perpage'] = $perpage;   
        $this->session->set_userdata($session_data); 
        redirect('editors/portfolio/'.$this->id); 
      }
      
      $ses_pp = $this->session->userdata('search_perpage');
      $this->perpage = ($ses_pp) ? $ses_pp : $this->settings['search_perpage'];

      $session_data['search_page'] = $this->uri->uri_string();   
      $this->session->set_userdata($session_data);
  
      $filter = " and li.client_id = ".$this->id;
      $limit = $this->sm->prepare_limit($this->page, $this->perpage);
      $data = $this->sm->get_search_results($this->langs, $filter, $limit);  
    
      $data['lang'] = $this->langs;
      $data['id'] = $this->id; 
      $data['profile'] = $this->em->get_profile($this->id);
      $data['perpage'] = $this->session->userdata('search_perpage');  
      $data['page_navigation'] = $this->builder->page_navigation($data['all'], $this->page, $this->perpage, 'editors/portfolio/'.$this->id.'/'); 
      $data['results'] = $this->load->view('main/ext/items', $data, true);
      $data['uri'] = $this->api->prepare_uri();
      $data['content_count'] = $this->em->get_content_count($this->id);
       
      $content['title'] = $this->lang->line('portfolio'); 
      $content['body'] = $this->load->view('editors/portfolio', $data, true);
      $this->out($content);   
   } 
  
   #------------------------------------------------------------------------------------------------
    
   function cats()
   {
       $data['lang'] = $this->langs;
       $data['id'] = $this->id;   
       $data['profile'] = $this->em->get_profile($this->id);
       $data['cats'] = $this->em->get_cats_list($this->langs, $this->id);
       $data['count'] = count($data['cats']);
       $data['content_count'] = $this->em->get_content_count($this->id);
       
       $content['title'] = $this->lang->line('categories');  
       $content['body'] = $this->load->view('editors/cats', $data, true);
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

         $this->session->set_userdata(array('filter_editors'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_editors = $this->session->userdata('filter_editors');

        if($filter_editors){
 
          $active = $filter_editors['active'];  
          $words = $filter_editors['words'];  
          
          if($active) $where[] = ($active==1) ? 'lc.active=1' : 'lc.active=0';
          if($words) $where[] = '(lc.fname like "%'.$words.'%" or lc.lname like "%'.$words.'%" or lc.company like "%'.$words.'%" or lc.email like "%'.$words.'%")';  
          
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
        
    function out($content=null, $pagination=null, $type=0)
    {        
        $this->builder->output(array('content'=>$content,'path'=>$this->path,'pagination'=>$pagination,'error'=>$this->error),$type);
    }            
}
