<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Permission {

    var $CI;
    var $group;
    
    /**
     * Class constructor
    */
    function Permission()
    {
        $this->CI = &get_instance();
        $this->group = $this->get_group();
        $this->CI->permissions = $this->get_permissions();
        $this->get_access();
    }

    /**
     * Get group
    */
    function get_group()
    {
       $uid = ($this->CI->session->userdata['uid']) ? $this->CI->session->userdata['uid'] : $this->CI->session->userdata['client_uid'];
       
       if($uid){
         $query = $this->CI->db->query('select g.* from lib_users_groups as g inner join lib_users as c on g.id = c.group_id where c.id='.intval($uid));
         $group = $query->result_array();
         return $group[0];
       }
       return false;
    }

    /**
     * Get permissions
    */        
    function get_permissions()
    {
      if (!$this->group)
      {
        $query = $this->CI->db->query('select * from lib_permissions');
        $list = $query->result_array();
        foreach ($list as $item)
        {
          $permissions[$item['code']] = 0;
        }
        return $permissions;
      }
      
      $list = $this->get_permissions_list($this->group['id']);

      if ($this->group['is_client'])
      {
        foreach ($list as $item)
        {
          if ($item['is_client'])
          {
            if ($item['group_id'])
            {
              $permissions[$item['code']] = 1;
            }
            else 
            {
              $permissions[$item['code']] = 0;
            }
          }
          else 
          {
            //$permissions[$item['code']] = 0;
          }
        }
      }
      elseif ($this->group['is_admin'])
      {
        foreach ($list as $item)
        {
          $permissions[$item['code']] = 1;
        }
      }
      elseif ($this->group['is_default'])
      {
        foreach ($list as $item)
        {
          $permissions[$item['code']] = 0;
        }
      }
      else 
      {
        foreach ($list as $item)
        {
          if ($item['is_admin'])
          {
            $permissions[$item['code']] = 0;
          }
          else 
          {
            if ($item['group_id'])
            {
              $permissions[$item['code']] = 1;
            }
            else 
            {
              $permissions[$item['code']] = 0;
            }
          }
        }
      }
      return $permissions;
    }

    /**
     * Get permissions list
    */        
    function get_permissions_list($group_id)
    {
       $query = $this->CI->db->query('select * from lib_permissions LEFT JOIN lib_group_permission on lib_permissions.id = lib_group_permission.permission_id and lib_group_permission.group_id = '.$group_id . ' order by lib_permissions.id');
       return $query->result_array();  
    }
    
    /**
     * Decline request
     */
    function decline() {
        $this->CI->session->set_userdata('path_after_login', $this->CI->uri->uri_string());
        redirect('/en/login');
    }

    /**
     * Get access
    */    
    function get_access()
    {
       $permissions = $this->CI->permissions;
       $class = $this->CI->router->class;
       $action = $this->CI->router->method;

       if($class == 'solrtools' || $class == 'frontendtools' || $class == 'uploadstools' || $class == 'importdb' || $class == 'syncdb' || $class == 'clips_update'){
           return true;
       }

       if($class == 'editor' || $class == 'fapi' || $action == 'index' || $action == 'content' || $action == 'info' || $class == 'sendmail' || $class == 'aws') {
         return true;
       }

        if($class == 'invoices') {
            return true;
        }

        if($class == 'aws_jobs'){
           return true;
        }
       
       if ($this->group['is_admin']) {
         return true;
       }

       if(!key_exists($class, $permissions) || (key_exists($class, $permissions) && @$permissions[$class] == false)) {
         $this->decline();
       }
      
       else{
         $key = ($action == 'view') ? $class : $class.'-'.$action;
         
         if(!key_exists($key, $permissions) || (key_exists($key, $permissions) && @$permissions[$key] == false)) {
           $this->decline();
         }
      }
    }   
}
