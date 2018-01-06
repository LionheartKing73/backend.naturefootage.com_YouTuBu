<?php
class Permissions_model extends CI_Model {

    function Permissions_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_permissions_list()
    {
      $query = $this->db->query('select * from lib_permissions order by is_client, code');
      return $query->result_array();       
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_permission($id)
    {  
        $query = $this->db->query('select * from lib_permissions where id='.intval($id));
        $row = $query->result_array();
        return $row[0]; 
    }  
    
    #------------------------------------------------------------------------------------------------
    
    function save_permission($id)
    {
        $data_content['code'] = $this->input->post('code');
        $data_content['is_client'] = $this->input->post('is_client');
  
        if($id){
          $this->db_master->where('id', $id);
          $this->db_master->update('lib_permissions', $data_content);
        }
        else{   
          $this->db_master->insert('lib_permissions', $data_content);
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function delete_permissions($ids)
    {    
      if(count($ids)){
        foreach($ids as $id){
            $this->db_master->delete('lib_permissions', array('id'=>$id));
        }
      }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_permission_by_code($code)
    {
      $query = $this->db->query('select * from lib_permissions where code = \''.$code.'\'');
      return $query->result_array();   
    }
   
}
