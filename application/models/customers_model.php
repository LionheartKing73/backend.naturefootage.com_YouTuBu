<?php

/**
 * Class Customers_model
 * @property Groups_model $gm
 */
class Customers_model extends CI_Model {

    function Customers_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        
        $this->load->model('groups_model','gm');
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_customers_count($filter)
    {
        $group_id = $this->gm->get_client_group_id();  
  
        $query = $this->db->query('select lc.id from lib_users as lc where lc.group_id='.intval($group_id).$filter);
        return $query->num_rows();
    }

    #------------------------------------------------------------------------------------------------
    
    function get_customers_list($filter=null, $order=null, $limit=null)
    {
        $group_id = $this->gm->get_client_group_id();  
  
        $query = $this->db->query('select lc.*, DATE_FORMAT(lc.ctime, \'%d.%m.%Y %T\') as ctime, DATE_FORMAT(lc.last_login, \'%d.%m.%Y %T\') as last_login from lib_users as lc where lc.group_id='.intval($group_id).'  '.$filter.$order.$limit);
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
    
    function delete_customers($ids)
    {    
        if(count($ids)){
           foreach($ids as $id){
              $this->db_master->delete('lib_users', array('id'=>$id));
               
              #bins
              $query = $this->db->get_where('lib_lb', array('client_id'=>$id));
              $rows = $query->result_array();
               
              foreach((array)$rows as $row)
              $this->db_master->delete('lib_lb_items', array('lb_id'=>$row['id']));
               
              $this->db_master->delete('lib_lb', array('client_id'=>$row['id']));
               
              #orders
              $query = $this->db->get_where('lib_orders', array('client_id'=>$id));
              $rows = $query->result_array();
               
              foreach((array)$rows as $row){
                $this->db_master->delete('lib_orders_items', array('order_id'=>$row['id']));
              }
              $this->db_master->delete('lib_orders', array('client_id'=>$row['id']));
            }
        }
    }

    #------------------------------------------------------------------------------------------------
    
    function get_customer($id)
    {
        $query = $this->db->query('select * from lib_users where id = ' . $id);
        $rows = $query->result_array();
        return $rows[0];
    }

    #------------------------------------------------------------------------------------------------
    
    function get_customer_name($id)
    {
        $query = $this->db->query('select concat(fname," ",lname) as name from lib_users where id='.$id);
        $rows = $query->result_array();
        return $rows[0]['name'];
    }
    
    #------------------------------------------------------------------------------------------------
    
    function save_corporate_data($id)
    {
       $data['corporate_balance'] = $this->input->post('corporate_balance');
       $data['corporate_discount'] = $this->input->post('corporate_discount');
       $data['corporate_active'] = intval($this->input->post('corporate_active'));
       
       $this->db_master->update('lib_users', $data, array('id'=>$id));
    }

    function get_customer_id_by_login($login, $provider = 0)
    {
//        $group_id = $this->gm->get_client_group_id();
//        $sql = "SELECT id FROM lib_users WHERE group_id = ? AND login = ? AND provider_id = ?";
//        $query = $this->db->query($sql, array($group_id, $login, $provider));
        $sql = "SELECT id FROM lib_users WHERE login = ?";
        $query = $this->db->query($sql, array($login));
        $rows = $query->result_array();
        return $rows[0]['id'];
    }
}
