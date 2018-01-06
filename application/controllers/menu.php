<?php

class Menu extends CI_Controller {

    var $id;
    var $langs;
    var $error;
    
	function Menu()
	{
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
 
        $this->load->model('menu_model','mm'); 

        $this->id = $this->uri->segment(4); 
        $this->langs = $this->uri->segment(1); 
        
        $this->save_filter_data();  
	}

    #------------------------------------------------------------------------------------------------
    
    function top()
    {
        $this->save_filter_data(0);
        $this->path = 'Manage system / Top menu';
        $this->view(); 
    }

    #------------------------------------------------------------------------------------------------
    
    function bottom()
    {
        $this->save_filter_data(1);
        $this->path = 'Manage system / Bottom menu';
        $this->view(); 
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

        $data['menus'] = $this->mm->get_menu_list($this->langs, $filter);
        $data['uri'] = $this->api->prepare_uri(); 
        $data['lang'] = $this->langs;
        $data['filter'] = $this->session->userdata('filter_menu'); 
        
        $content = $this->load->view('menu/view', $data, true);
        $this->out($content);    
    }

    #------------------------------------------------------------------------------------------------
        
    function ord()
    {   
        $ids = $this->input->post('ord');         
        
        if(count($ids)){      
          foreach($ids as $menu_id=>$ord){
            $this->db_master->where('id', $menu_id);
            $this->db_master->update('lib_menu', array('ord'=>$ord));
          }
          $this->api->log('log_menu_ord');
        }
        redirect($this->langs.'/menu/view');
    } 
        
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->mm->change_visible($this->input->post('id')); 
        $this->api->log('log_menu_visible', $this->input->post('id'));  
        redirect($this->langs.'/menu/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->api->log('log_menu_delete', $ids); 
        $this->mm->delete_menu($ids);  
        redirect($this->langs.'/menu/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
        $menu_id = intval($this->uri->segment(4));
        $filter = $this->session->userdata('filter_menu');

        if($this->input->post('delete')){ 
          $this->delete_image($this->input->post('sid'));    
        }
        
        if($this->input->post('save') && $this->check_details()){
          $sub_id = $this->mm->save_menu($this->id, $this->langs, $filter);
          
          if($this->id) $this->api->log('log_menu_edit', $this->id);   
          else $this->api->log('log_menu_new');
          
          $this->upload_image($sub_id);
          redirect($this->langs.'/menu/view');   
        }
        
        $data = $_POST;
          
        if(!$this->error){ 
          $data = $this->mm->get_menu($this->id, $this->langs); 
          $data['picture'] = $this->get_image_path($row[0]);
        }
        
        $menu_types = array('Top menu', 'Bottom menu');
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Manage system / ' . $menu_types[$filter['type']] . ' / ' . $action;
        
        $temp = $this->mm->get_resources($this->langs);
        $data['cats'] = $temp['cats'];
        $data['subs'] = $temp['subs'];
        
        $data['parents'] = $this->mm->get_parents($this->id, $this->langs, $filter);
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
         
        $content = $this->load->view('menu/edit', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('link')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }
        
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data($tp=null)
    {  
       if($this->input->post('filter') || $tp!==null){
         if($this->input->post('filter')) $temp['type'] = intval($this->input->post('type')); 
         else $temp['type'] = intval($tp);
         
         $this->session->set_userdata(array('filter_menu'=>$temp)); 
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_menu = $this->session->userdata('filter_menu');

        if($filter_menu){
          $type = $filter_menu['type'];  
          $where[] = ($type==1) ? 'lm.type=1' : 'lm.type=0';
          
          if(count($where)) return ' and '.implode(' and ',$where);
        }    
        return '';
    } 

    #------------------------------------------------------------------------------------------------
        
    function upload_image($menu_id)
    {
        if(is_uploaded_file($_FILES['mimg']['tmp_name'])){
           $ext = substr($_FILES['mimg']['name'],-3); 
           $menu_dir = $this->config->item('menu_dir');
          
           if($this->api->check_ext($ext,'img')){ 
             @copy($_FILES['mimg']['tmp_name'], $menu_dir.$menu_id.'.'.$ext); 
             $this->mm->update_resource($id, $resource);
           }
           else
             $this->errors = $this->lang->line('incorrect_image');
           
           $this->api->log('log_menu_upload', $menu_id);   
        }  
    }
    
    #------------------------------------------------------------------------------------------------
        
    function delete_image($id)
    {
        $menu_dir = $this->config->item('menu_dir');
        $row = $this->mm->get_content($id);
        
        @unlink($menu_dir.$id.'.'.$row['resource']); 
        $this->mm->update_resource($id);
        
        $this->api->log('log_menu_unlink', $id);
    }
        
    #------------------------------------------------------------------------------------------------   
        
    function get_image_path($data)
    {
        $path = $this->config->item('menu_path');  
        if($data['resource']) return $path.$data['sid'].'.'.$data['resource']; 
        else return '';
    }    

    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error), 1);
    }       
}
