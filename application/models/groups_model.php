<?php
class Groups_model extends CI_Model {

    function Groups_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #------------------------------------------------------------------------------------------------
    
    function get_groups_list($filter, $order)
    {
        $query = $this->db->query('select lug.*, DATE_FORMAT(lug.ctime, \'%d.%m.%Y %T\') as ctime, count(lu.id) as users from lib_users_groups as lug left join lib_users as lu on lu.group_id=lug.id where lug.id>0'.$filter.' group by lug.id '.$order);
        return $query->result_array();
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function change_visible($ids)
    {   
        if(count($ids)){      
            foreach($ids as $id){
               $this->db_master->query('UPDATE lib_users_groups set active = !active where id='.$id);
            }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_groups($ids)
    {    
        if(count($ids)){
            foreach($ids as $id){
                $query_default = $this->db->query('select id from lib_users_groups where is_default = 1');
                $default = $query_default->result_array();
                $this->db_master->update('lib_users', array('group_id'=>$default[0]['id']), array('group_id'=>$id));
                $this->db_master->delete('lib_users_groups', array('id'=>$id));
            }
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_group($id)
    {  
        $query = $this->db->query('select * from lib_users_groups where id='.intval($id));
        $row = $query->result_array();
        return $row[0]; 
    }    
    
    #------------------------------------------------------------------------------------------------
    
    function save_group($id)
    {
        $data_content['title'] = $this->input->post('title');
  
        if($id){
          $this->db_master->where('id', $id);
          $this->db_master->update('lib_users_groups', $data_content);
        }
        else{   
          $data_content['ctime'] = date('Y-m-d H:i:s');  
          $this->db_master->insert('lib_users_groups', $data_content);
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_group_permission($group_id)
    {
      $group = $this->get_group($group_id);

      $query = $this->db->query('select lib_permissions.id as id, code, group_id from lib_permissions left join lib_group_permission on lib_permissions.id = lib_group_permission.permission_id and lib_group_permission.group_id = '.$group_id.' where lib_permissions.is_client = '.(($group['is_client']/* || $group['is_editor']*/) ? 1 : 0));
      return $query->result_array();       
    }
    
    #------------------------------------------------------------------------------------------------
    
    function set_group_permission($data, $group_id)
    {      
      $this->db_master->delete('lib_group_permission', array('group_id'=>$group_id));
      if (@$data)
      {
        foreach ($data as $value)
        {    
          $permissions = array('permission_id' => $value, 'group_id' => $group_id);
          $this->db_master->insert('lib_group_permission', $permissions);
        }
      }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_group_by_user($user_id)
    {      
       $query = $this->db->query('select lib_users_groups.* from lib_users_groups inner join lib_users on lib_users_groups.id = lib_users.group_id where lib_users.id = '.intval($user_id));

       $rows = $query->result_array();  
       return $rows[0];
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_editor_group_id()
    {
       $query = $this->db->get_where('lib_users_groups', array('is_editor'=>1));
       $row = $query->result_array();
       return $row[0]['id']; 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_client_group_id()
    {
       $query = $this->db->get_where('lib_users_groups', array('is_client'=>1));
       $row = $query->result_array();
       return $row[0]['id']; 
    }

    function get_provider_group_id()
    {
       $query = $this->db->get_where('lib_users_groups', array('is_editor'=>1));
       $row = $query->result_array();
       return $row[0]['id'];
    }

    function get_administrator_group_id()
    {
        $query = $this->db->get_where('lib_users_groups', array('is_admin'=>1));
        $row = $query->result_array();
        return $row[0]['id'];
    }

    function get_lab_group_id()
    {
        $query = $this->db->get_where('lib_users_groups', array('is_lab'=>1));
        $row = $query->result_array();
        return $row[0]['id'];
    }

    function is_user_in_lab_group($user_id){
        $query = $this->db->query("
        SELECT * FROM lib_users u
        INNER JOIN lib_users_groups ug ON u.group_id=ug.id
        WHERE ug.is_lab=1 AND u.id=" . $user_id);
        return (bool)$query->num_rows();
    }
}
