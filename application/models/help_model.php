<?php
class Help_model extends CI_Model {

    function Help_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #------------------------------------------------------------------------------------------------
    
    function get_help_list()
    {      
        $query = $this->db->query('select * from lib_help order by parent_id, ord');
        $rows = $query->result_array();
        
        foreach($rows as $row){
          $row['pdfplus'] = ($row['pdf']) ? '+' : '';
          $row['videoplus'] = ($row['video']) ? '+' : '';
          
          if($row['parent_id']) $tree[$row['parent_id']]['child'][] = $row;
          else $tree[$row['id']] = $row;
        }
        return $tree;   
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function change_ord($ids)
    {     
       if(count($ids)){      
          foreach($ids as $id=>$ord){
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_help', array('ord'=>$ord));
          }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_help($ids, $dir)
    {    
        if(count($ids)){
           foreach($ids as $id){
              $query = $this->db->get_where('lib_help', array('parent_id'=>$id));
              $rows = $query->result_array();   
            
              if(count($rows)){
                foreach($rows as $row){
                  $this->delete_topic($row['id'], $dir);  
                }
              }  
              $this->delete_topic($id, $dir);
            }
        }
    }

    #------------------------------------------------------------------------------------------------
    
    function delete_topic($id, $dir)
    {  
        $query = $this->db->get_where('lib_help', array('id'=>$id));
        $row = $query->result_array();

        @unlink($dir.$id.'.'.$row[0]['pdf']); 
        @unlink($dir.$id.'.'.$row[0]['video']);  
        $this->db_master->delete('lib_help', array('id'=>$id));
    }
        
    #------------------------------------------------------------------------------------------------
    
    function get_topic($id)
    {  
        $query = $this->db->query('select * from lib_help where id='.intval($id));
        $row = $query->result_array();
        return $row[0];
    }    

    #------------------------------------------------------------------------------------------------
    
    function get_parents($id)
    {  
        $query = $this->db->query('select * from lib_help where parent_id=0 and id!='.intval($id));
        return $query->result_array();
    } 

    #------------------------------------------------------------------------------------------------
    
    function update_resource($id, $resource='pdf', $value='')
    {  
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_help', array($resource=>$value));
    } 
 
    #------------------------------------------------------------------------------------------------
    
    function get_content($id)
    {  
        $query = $this->db->get_where('lib_menu_content', array('menu_id'=>$id));
        $row = $query->result_array(); 
        return $row[0];
    } 
               
    #------------------------------------------------------------------------------------------------
    
    function save_topic($id)
    {
        $data['title'] = $this->input->post('title');
        $data['parent_id'] = $this->input->post('parent_id'); 
        $data['annotation'] = $this->input->post('annotation'); 
        $data['ord'] = $this->input->post('ord'); 

        if($id){
           $this->db_master->where('id', $id);
           $this->db_master->update('lib_help', $data);
        }
        else{   
           $data['ctime'] = date('Y-m-d H:i:s');  
           $this->db_master->insert('lib_help', $data);
           $id = $this->db_master->insert_id();
        }
        return $id;
    }  
}
