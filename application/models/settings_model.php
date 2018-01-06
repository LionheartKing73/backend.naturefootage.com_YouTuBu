<?php
class Settings_model extends CI_Model {

    function Settings_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function save_settings($sets)
    {
        $query = $this->db->get('lib_settings');
        $rows = $query->result_array();
        
//        foreach($rows as $row){ 
//          $this->db_master->where('id', $row['id']);
//          $this->db_master->update('lib_settings', array('value'=>''));
//        }
        
        foreach($sets as $k=>$val){
          $data['value'] = $val;  
            
          $this->db_master->where('name', $k);
          $this->db_master->update('lib_settings', $data);
        }
    }

    #------------------------------------------------------------------------------------------------
    
    function get_settings()
    {
        $query = $this->db->query('select * from lib_settings');
        return $query->result_array();
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_setting($name)
    {

        $query = $this->db->get_where('lib_settings', array('name'=>$name), 1);

        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $result = $row['value'];
        }
        
        return $result;
        
    }


}
