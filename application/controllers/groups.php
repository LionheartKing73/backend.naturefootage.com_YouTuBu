<?php

class Groups extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    
	function Groups()
	{
		parent::__construct();	
          
        $this->load->model('groups_model','gm');
        $this->api->save_sort_order('groups');   
         
        $this->langs = $this->uri->segment(1); 
        $this->id = $this->uri->segment(4);  
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
        $order = $this->api->get_sort_order('groups');

        $data['groups'] = $this->gm->get_groups_list($filter, $order);
        $data['uri'] = $this->api->prepare_uri(); 
        $data['filter'] = $this->session->userdata('filter_groups');  
        $data['lang'] = $this->langs;
        $content = $this->load->view('groups/view', $data, true);
        
        $this->path = 'Permission manager / User groups';

        $this->out($content);    
    }
    
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->gm->change_visible($this->input->post('id'));
        $this->api->log('log_groups_visible', $this->input->post('id'));    
        redirect($this->langs.'/groups/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
 
        $this->api->log('log_groups_delete', $ids);  
                 
        $this->gm->delete_groups($ids);
        redirect($this->langs.'/groups/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
        if($this->input->post('save') && $this->check_details($this->id)){
          $this->gm->save_group($this->id);
          
          if($this->id) $this->api->log('log_groups_edit', $this->id);   
          else $this->api->log('log_groups_new');
          
          redirect($this->langs.'/groups/view'); 
        }

        $data = ($this->error) ? $_POST : $this->gm->get_group($this->id); 
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Permission manager / User groups / ' . $action;
         
        $content = $this->load->view('groups/edit', $data, true);  
        $this->out($content); 
    }

    #------------------------------------------------------------------------------------------------
    
    function permissions()
    {
      if($this->input->post('save')){
        $this->set_permissions();
      }
      
      $list = $this->gm->get_group_permission($this->id);
      
      foreach ($list as $item)
      {
        if ($item['group_id'])
        {
          $permissions[$item['code']]['value'] = 1;
        }
        else 
        {
          $permissions[$item['code']]['value'] = 0;
        }
        $permissions[$item['code']]['id'] = $item['id'];
      }
      if (count($permissions))
      {
        foreach ($permissions as $key => $item)
        {
          if (!strstr($key, '-'))
          {
            $temp[$key]['class']['id'] = $item['id'];
            $temp[$key]['class']['value'] = $item['value'];
          }
          else 
          {
            $class_key = substr($key, 0, strpos($key, '-'));
            $action_key = substr($key, strpos($key, '-')+1);
            $temp[$class_key]['actions'][$action_key]['id'] = $item['id'];
            $temp[$class_key]['actions'][$action_key]['value'] = $item['value'];
          }
        }
      }
      $data['permissions'] = $temp;
      $data['lang'] = $this->langs;
      
      $this->path = 'Permission manager / User groups / Permissions';
  
      $content = $this->load->view('groups/permissions', $data, true);
      $this->out($content); 
    }

    #------------------------------------------------------------------------------------------------
    
    function logs()
    {
        $limit = $this->get_log_limit();
        $all = $this->gm->get_logs_count($this->id);
        
        $data['logs'] = $this->gm->get_logs($this->id, $limit); 
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;

        $content = $this->load->view('groups/logs', $data, true); 
        $pagination = $this->api->get_pagination('groups/logs/'.$this->id,$all,$this->settings['perpage']); 
         
        $this->out($content, $pagination); 
    }
            
    #------------------------------------------------------------------------------------------------
            
    function check_details($id=null)
    {  
       if(!$this->input->post('title')){
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
         $this->session->set_userdata(array('filter_groups'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_groups = $this->session->userdata('filter_groups');

        if($filter_groups){
 
          $active = $filter_groups['active'];    
          
          if($active) $where[] = ($active==1) ? 'lug.active=1' : 'lug.active=0';
          if(count($where)) return ' and '.implode(' and ',$where);
        }
        return '';
    } 
          
    #------------------------------------------------------------------------------------------------
       
    function set_permissions()
    {
      if($this->input->post('save')){
  
        $this->gm->set_group_permission($this->input->post('id'), $this->id);
        
        $this->api->log('log_group_permission_set', $this->id);
         
        redirect($this->langs.'/groups/view/'); 
      } 
    }
    
    #------------------------------------------------------------------------------------------------
     
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content,'path'=>$this->path,
          'pagination'=>$pagination,'error'=>$this->error),1);
    }       
}
