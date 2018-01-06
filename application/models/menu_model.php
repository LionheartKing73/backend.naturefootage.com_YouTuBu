<?php
class Menu_model extends CI_Model {

    function Menu_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #------------------------------------------------------------------------------------------------
    
    function get_menu_list($lang, $filter)
    {      
        $query = $this->db->query('select lm.*, DATE_FORMAT(lm.ctime, \'%d.%m.%Y %T\') as ctime, lmc.title from lib_menu as lm left join lib_menu_content as lmc on lm.id=lmc.menu_id and lmc.lang='.$this->db->escape($lang).' where lm.id>0'.$filter.' order by lm.parent_id, lm.ord');
        $rows = $query->result_array();
        
        foreach($rows as $row){
          $row['title'] = ($row['title']) ? $row['title'] : '-'; 
           
          if($row['parent_id']){
            $row['display'] = 'none';  
            $tree[$row['parent_id']]['child'][] = $row;
          }
          else{
            $row['display'] = 'block'; 
            $tree[$row['id']] = $row;
          }
        }
        
        if(count($tree)){
          foreach($tree as $v){
            $list[] = $v;
          
            if($v['child']){
              foreach($v['child'] as $val){
                $list[] = $val; 
              }
            }  
          }
        }
        return $list;   
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function change_visible($ids)
    {     
        if(count($ids)){      
           foreach($ids as $id){
              $this->db_master->query('UPDATE lib_menu set active = !active where id='.$id);
           }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_menu($ids)
    {    
        if(count($ids)){
           foreach($ids as $menu_id){
              $query = $this->db->get_where('lib_menu', array('parent_id'=>$menu_id));
              $rows = $query->result_array();   
            
              if(count($rows)){
                foreach($rows as $row){
                  $this->db_master->delete('lib_menu', array('id'=>$row['id']));
                  $this->db_master->delete('lib_menu_content', array('menu_id'=>$row['id']));
                }
              }  
              $this->db_master->delete('lib_menu', array('id'=>$menu_id));
              $this->db_master->delete('lib_menu_content', array('menu_id'=>$menu_id));
            }
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_menu($id, $lang)
    {  
        $query = $this->db->query('select lm.*, lmc.title, lmc.resource, lmc.id as sid from lib_menu as lm left join lib_menu_content as lmc on lm.id=lmc.menu_id and lmc.lang='.$this->db->escape($lang).' where lm.id='.intval($id));
        $row = $query->result_array();
        return $row[0];
    }    

    #------------------------------------------------------------------------------------------------
    
    function get_parents($id, $lang, $filter)
    {  
        $query = $this->db->query('select lm.id, lmc.title from lib_menu as lm left join lib_menu_content as lmc on lm.id=lmc.menu_id and lmc.lang='.$this->db->escape($lang).' where lm.parent_id=0 and lm.type='.$filter['type'].' and lm.id!='.intval($id));
        return $query->result_array();
    } 

    #------------------------------------------------------------------------------------------------
    
    function update_resource($id, $resource='')
    {  
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_menu_content', array('resource'=>$resource));
    } 
 
    #------------------------------------------------------------------------------------------------
    
    function get_content($id)
    {  
        $query = $this->db->get_where('lib_menu_content', array('menu_id'=>$id));
        $row = $query->result_array(); 
        return $row[0];
    } 
               
    #------------------------------------------------------------------------------------------------
    
    function save_menu($id, $lang, $filter)
    {
        $data_menu['parent_id'] = $this->input->post('parent_id');
        $data_menu['link'] = $this->input->post('link');
        $data_menu['ord'] = intval($this->input->post('ord'));
        $data_menu['target'] = $this->input->post('target'); 
        $data_menu['type'] = $filter['type'];

        $data_content['menu_id'] = $id;
        $data_content['lang'] = $this->langs;
        $data_content['title'] = $this->input->post('title');
            
        if($id){
           $this->db_master->where('id', $id);
           $this->db_master->update('lib_menu', $data_menu);
              
           $query = $this->db->get_where('lib_menu_content', array('menu_id'=>$id,'lang'=>$lang));
           $row = $query->result_array();
           $sub_id = $row[0]['id'];
              
           if(count($row)){
              $this->db_master->where('id', $sub_id);
              $this->db_master->update('lib_menu_content', $data_content);
            }
            else {
              $this->db_master->insert('lib_menu_content', $data_content);
              $sub_id = $this->db_master->insert_id();
            }
          }
          else{   
            $data_menu['ctime'] = date('Y-m-d H:i:s');  
            $this->db_master->insert('lib_menu', $data_menu);

            $data_content['menu_id'] = $this->db_master->insert_id();
            $this->db_master->insert('lib_menu_content', $data_content);
            $sub_id = $this->db_master->insert_id();
          }
         return $sub_id; 
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_resources($lang)
    {  
        $query = $this->db->query('select lp.*, lpc.title from lib_pages as lp left join lib_pages_content as lpc on lp.id=lpc.page_id and lpc.lang='.$this->db->escape($lang).' where lp.id>0');
        $rows = $query->result_array();
        
        foreach($rows as $row){
          $cats[$row['id']] = '"'.$row['id'].':'.$row['title'].'"';
          
          $temp = array();
          $temp[] = '"'.$row['alias1'].'"';  
          $temp[] = '"'.$row['alias2'].'"';
          $temp[] = '"publication/content/'.$row['id'].'.html"';
          
          $subs[$row['id']] = implode(',',$temp);
        }
        
        $id = max(array_keys($cats)) + 1;
        
        $cats[$id] = '"'.$id.':News"';
        $subs[$id] = '"news.html"';

        return array('cats'=>implode(',',$cats), 'subs'=>$subs);
    }     
}
