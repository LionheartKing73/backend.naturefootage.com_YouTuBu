<?php
class Tagcloud_model extends CI_Model {

    function Tagcloud_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_tags_count($lang, $filter)
    {
        $query = $this->db->query('select * from lib_search where lang='.$this->db->escape($lang).' and '.$filter);
        return $query->num_rows(); 
    }
 
    #------------------------------------------------------------------------------------------------
    
    function get_tags_list($lang, $filter, $order, $limit)
    {
        $query = $this->db->query(
          'select * from lib_search where lang='.$this->db->escape($lang).' and '
            . $filter . $order . $limit);
        return $query->result_array();
    } 
    
    #------------------------------------------------------------------------------------------------

    function delete_tags($ids)
    {    
        if(count($ids)){
            foreach($ids as $id){
                $this->db_master->delete('lib_search', array('id'=>$id));
            }
        }
    }   
    
    #------------------------------------------------------------------------------------------------
    
    function get_tag($id, $lang)
    {  
        $query = $this->db->query('select * from lib_search where lang='.$this->db->escape($lang).' and id='.$id);
        $row = $query->result_array();
        return $row[0]; 
    }

    #------------------------------------------------------------------------------------------------
    
    function save_tag($id, $lang, $filter)
    {
        $data_content['phrase'] = $this->input->post('phrase'); 
        $data_content['weight'] = $this->input->post('weight');  
        $data_content['type'] = $filter; 
        $data_content['lang'] = $lang;

        if($id){
          $this->db_master->where('id', $id);
          $this->db_master->update('lib_search', $data_content);
        }
        else    
          $this->db_master->insert('lib_search', $data_content);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function move_to_stop($id) {
    	$this->db_master->query('UPDATE lib_search SET type = 2 WHERE id = ? AND type = 0', $id);
    }

    function get_top_keywords($provider_id, $limit = 20){
        $this->db->where('provider_id', $provider_id);
        $this->db->where('type', 0);
        $this->db->order_by('times', 'desc');
        $this->db->limit($limit);
        $query = $this->db->get('lib_search');
        $res = $query->result_array();
        return $res;
    }
}
