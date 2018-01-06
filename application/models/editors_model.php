<?php
class Editors_model extends CI_Model {

    var $cat_path;
    var $cat_dir;
    
    function Editors_model()
    {   
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('groups_model','gm');    
        
        $this->cat_dir = $this->config->item('cat_dir');    
        $this->cat_path = $this->config->item('cat_path');
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_editors_count($filter)
    {
        $group_id = $this->gm->get_editor_group_id();
        
        $query = $this->db->query('select lc.id from lib_users as lc where lc.group_id='.$group_id.$filter);
        return $query->num_rows();
    }

    #------------------------------------------------------------------------------------------------
    
    function get_editors_list($filter=null, $order=null, $limit=null)
    {
        $group_id = $this->gm->get_editor_group_id();
        
        $query = $this->db->query(
          'SELECT u.*, c.name country
          FROM lib_users u
          LEFT JOIN lib_countries c ON c.id = u.country_id
          WHERE u.group_id=' . $group_id . ' ' . $filter.$order.$limit);
        return $query->result_array();
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function change_visible($ids)
    {     
        if(count($ids)){      
            foreach($ids as $id){
               $this->db_master->query('UPDATE lib_users set active = !active where id='.$id);
            }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_editors($ids)
    {    
        if(count($ids)){
           foreach($ids as $id){
              $this->db_master->delete('lib_users', array('id'=>$id));
            }
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_editor($id)
    {
        $rows = $this->db->query(
          'SELECT lc.*, lcc.name country, lcc.currency
          FROM lib_users lc
          LEFT JOIN lib_countries lcc ON lcc.id = lc.country_id
          WHERE lc.id=?', $id)->result_array();
        
        return $rows[0];
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_profile($id)
    {
       $query = $this->db->query('select cl.*, DATE_FORMAT(cl.ctime, \'%M %Y\') as since from lib_users as cl where cl.id ='.$id);
       $rows = $query->result_array();
       return $rows[0];
    }
    
    #------------------------------------------------------------------------------------------------
      
    function get_content_count($id)
    {
       $query = $this->db->query('select count(id) as num from lib_images where client_id = '.$id);
       $rows = $query->result_array();
       $data['images'] = $rows[0]['num'];
       
       $query = $this->db->query('select count(id) as num from lib_clips where client_id = '.$id);
       $rows = $query->result_array();
       $data['clips'] = $rows[0]['num'];
       
       return $data;
    }
    
    #------------------------------------------------------------------------------------------------   
        
    function get_image_path($data)
    {
        if($data['resource']) return $this->cat_path.$data['id'].'.'.$data['resource']; 
        else return '';
    } 
    
    #------------------------------------------------------------------------------------------------

    function save_commision_data($id)
    {
       $data['commision'] = $this->input->post('commision');
       $this->db_master->update('lib_users', $data, array('id'=>$id));
    }

    #------------------------------------------------------------------------------------------------

    function send_ftp_details($id)
    {
       $editor = $this->get_editor($id);
       
       if($editor['active']){
           
         $this->load->library('email');   
       
         $config['mailtype'] = 'html';
         $config['wordwrap'] = 0; 
         $this->email->initialize($config); 
      
         $temp['fname'] = $editor['fname'];  
         $temp['lname'] = $editor['lname'];  
         $temp['login'] = $editor['login'];
         $temp['password'] = $editor['password'];
         $temp['admin_email'] = $this->settings['admin_email'];

         $data['body'] = $this->load->view('main/mail/ftp', $temp, true); 
         
         $this->email->from($this->settings['email']);
         $this->email->subject($this->config->item('title') . ' Library - FTP details');
         $this->email->message($data['body']);
         $this->email->to($editor['email']);
         $this->email->send(); 
       }
    }         
}
