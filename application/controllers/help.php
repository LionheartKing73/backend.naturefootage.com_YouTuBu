<?php

class Help extends CI_Controller {

    var $id;
    var $langs;
    var $error;
    var $help_dir;
    var $help_path;
    
	function Help()
	{
		parent::__construct();	
 
        $this->load->model('help_model','hm'); 

        $this->help_dir = $this->config->item('help_dir');
        $this->help_path = $this->config->item('help_path');  
        $this->id = $this->uri->segment(4); 
        $this->langs = $this->uri->segment(1);  
	}
        	
    #------------------------------------------------------------------------------------------------

    function index()
    {
        $this->show();
    }

    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $data['topics'] = $this->hm->get_help_list();
        $data['uri'] = $this->api->prepare_uri(); 
        $data['lang'] = $this->langs;
        
        $this->path = 'Manage system / Help topics';
        
        $content = $this->load->view('help/view', $data, true);
        $this->out($content);    
    }

    #------------------------------------------------------------------------------------------------
    
    function show()    
    { 
        $data['topics'] = $this->hm->get_help_list();
        $data['uri'] = $this->api->prepare_uri(); 
        $data['path'] = $this->help_path;
        $data['lang'] = $this->langs;
        
        $content = $this->load->view('help/content', $data, true);
        $this->out($content);    
    }
   
    #------------------------------------------------------------------------------------------------
        
    function ord()
    {           
        $this->hm->change_ord($this->input->post('ord')); 
        $this->api->log('log_help_ord');  
        redirect($this->langs.'/help/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 

        $this->hm->delete_help($ids,$this->help_dir);  
        
        $this->api->log('log_help_delete', $ids);
        redirect($this->langs.'/help/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
        if($this->input->post('delete')){ 
          $this->delete_image($this->input->post('sid'));    
        }
        
        if($this->input->post('save') && $this->check_details()){
          $id = $this->hm->save_topic($this->id);
          $this->upload_resources($id); 
          
          if($this->id) $this->api->log('log_help_edit', $this->id);   
          else $this->api->log('log_help_new');
          
          if(!$this->error) redirect($this->langs.'/help/view');   
        }
        
        $data = $_POST;
          
        if(!$this->error){ 
          $data = $this->hm->get_topic($this->id); 
          $data['path'] = $this->get_path($data);
        }
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Manage system / Help topics / ' . $action;
        
        $data['parents'] = $this->hm->get_parents($this->id);
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
         
        $content = $this->load->view('help/edit', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('annotation')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }

    #------------------------------------------------------------------------------------------------
        
    function upload_resources($id)
    {
        if(is_uploaded_file($_FILES['mpdf']['tmp_name'])){ 
           $ext = substr($_FILES['mpdf']['name'],-3); 
           if($this->api->check_ext($ext,'pdf')){ 
             @copy($_FILES['mpdf']['tmp_name'], $this->help_dir.$id.'.'.$ext); 
             $this->hm->update_resource($id, 'pdf', $ext);
             $this->api->log('log_help_upload_pdf', $id); 
           }
           else
             $this->error = $this->lang->line('incorrect_pdf');
        }
        
        if(is_uploaded_file($_FILES['mvid']['tmp_name'])){ 
           $ext = substr($_FILES['mvid']['name'],-3); 
           
           if($this->api->check_ext($ext,'video')){ 
             @copy($_FILES['mvid']['tmp_name'], $this->help_dir.$id.'.'.$ext); 
             $this->hm->update_resource($id, 'video', $ext);
             $this->api->log('log_help_upload_vid', $id); 
           }
           else
             $this->error = $this->lang->line('incorrect_video');
        }
    }

    #------------------------------------------------------------------------------------------------
        
    function delres()
    {
        $res = $this->uri->segment(4); 
        $id = $this->uri->segment(5); 
        
        $data = $this->hm->get_menu($id); 

        @unlink($menu_dir.$id.'.'.$data[$res]);  
        $this->hm->update_resource($id, $res, $value=''); 
        $this->api->log('og_help_unlink_'.$res, $id);  
           
        redirect($this->langs.'/help/edit/'.$id);  
    }
        
    #------------------------------------------------------------------------------------------------   
        
    function get_path($data)
    {
        if($data['pdf']) $temp['pdfpath'] = $this->help_path.$data['id'].'.'.$data['pdf'];     
        if($data['video']) $temp['vidpath'] = $this->help_path.$data['id'].'.'.$data['video']; 
        
        return $temp;
    }    

    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error), 1);
    }       
}
