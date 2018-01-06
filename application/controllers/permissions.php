<?php

class Permissions extends CI_Controller {
    
	function Permissions()
	{
		parent::__construct();	
        
        $this->load->model('permissions_model','pm');  

        $this->langs = $this->uri->segment(1);
        $this->id = $this->uri->segment(4);
	}
	
	#------------------------------------------------------------------------------------------------

    function index()
    {
        show_404();
    }
	
	#------------------------------------------------------------------------------------------------

  function view()
  {
    $data['permissions'] = $this->pm->get_permissions_list();
    $data['lang'] = $this->langs;
    
    $this->path = 'Permission manager / Permissions';
    
    $content = $this->load->view('permissions/view', $data, true);  
    $this->out($content); 
  }
  
  #------------------------------------------------------------------------------------------------

  function edit()
  {
    if($this->input->post('save') && $this->check_details($this->id)){
      $this->pm->save_permission($this->id);
      
      if($this->id) $this->api->log('log_permission_edit', $this->id);   
      else $this->api->log('log_permission_add');
      
      redirect($this->langs.'/permissions/view'); 
    }

    $data = ($this->error) ? $_POST : $this->pm->get_permission($this->id); 
    $data['id'] = ($this->id) ? $this->id : '';
    $data['lang'] = $this->langs;
    
    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Permission manager / Permissions / ' . $action;
     
    $content = $this->load->view('permissions/edit', $data, true);  
    $this->out($content); 
  }
  
  #------------------------------------------------------------------------------------------------

  function delete()
  {   
      if($this->id) $ids[] = $this->id;
      else $ids = $this->input->post('id'); 

      $this->api->log('log_permission_delete', $ids);  
               
      $this->pm->delete_permissions($ids);
      redirect($this->langs.'/permissions/view');
  }
    
  #------------------------------------------------------------------------------------------------

  function check_details($id=null)
  {
    if (!$this->input->post('code'))
    {
      $this->error = $this->lang->line('empty_fields');
      return false;
    }
    return true;
  }
      
  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null)
  {        
      $this->builder->output(array('content'=>$content,'path'=>$this->path,
        'pagination'=>$pagination,'error'=>$this->error),1);
  }
   
}