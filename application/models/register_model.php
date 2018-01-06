<?php
/**
 * @property Users_model $users_model
 * @property Groups_model $groups_model
 * @property Settings_model $settings_model
 * @property CI_DB_active_record $db_master
 */
class Register_model extends CI_Model {

    var $settings;
    
    function Register_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('users_model');
        $this->load->model('groups_model');
        $this->load->model('settings_model');

        $this->settings = $this->api->settings();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_client($id)
    {
       $query = $this->db->query('select u.*, c.name country, c.code country_code
        from lib_users u
        left join lib_countries c on c.id = u.country_id
        where u.id = ?', $id);
       $rows = $query->result_array();
       return $rows[0];
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_client_by_login($login)
    {
       $group_id = $this->groups_model->get_client_group_id();  
       
       $query = $this->db->query('select * from lib_users where group_id='.intval($group_id).' and login ='.$this->db->escape($login));  
       return $query->result_array();
    }

    #------------------------------------------------------------------------------------------------
    
    function get_editor_by_login($login)
    {
       $group_id = $this->groups_model->get_editor_group_id();
        
       $query = $this->db->query('select * from lib_users where group_id='.intval($group_id).' and login ='.$this->db->escape($login));  
       return $query->result_array();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_user_by_login($login)
    {
       $query = $this->db->query('select * from lib_users where login ='.$this->db->escape($login));  
       return $query->result_array();
    }

    #------------------------------------------------------------------------------------------------
    
    function is_email_unique($email, $id) {
      if ($id) {
        $query = $this->db->query('SELECT id FROM lib_users WHERE email = ? AND id <> ?', $email, $id);
      }
      else {
        $query = $this->db->query('SELECT id FROM lib_users WHERE email = ?', $email);
      }
      $result = $query->result_array();
      
      return empty($result);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_client_group()
    {
       $query = $this->db->query('select id from lib_users_groups where is_client = 1');  
       $rows = $query->result_array();
       return $rows[0];
    }

    #------------------------------------------------------------------------------------------------
    
    function save_editor($avatar = null)
    {
       $data['group_id'] = $this->groups_model->get_editor_group_id();
       $data['fname'] = $this->input->post('fname',true); 
       $data['lname'] = $this->input->post('lname',true); 
       $data['email'] = $this->input->post('email',true); 
       $data['login'] = $this->input->post('login',true);
       $data['password'] = $this->input->post('pass',true);
       $data['ctime'] = date('Y-m-d H:i:s'); 
       $data['country_id'] = 247;
       if($avatar) $data['avatar'] = $avatar;
      
       if ($id = $this->input->post('id',true))
       {
         $this->db_master->where('id', $id);
         $this->db_master->update('lib_users', $data);
       }
       else 
       {
        $data['active'] = 0; 
        $this->db_master->insert('lib_users', $data);
        $this->send_confirmation('editor');
       }
       $this->builder->login_user($data['login'], $data['password']);

       $this->users_model->check_upload_folders(3, $data);
    }
    
    #------------------------------------------------------------------------------------------------

    function save_client () {
        $data[ 'group_id' ] = $this->groups_model->get_client_group_id();
        $data[ 'fname' ] = $this->input->post( 'fname', TRUE );
        $data[ 'lname' ] = $this->input->post( 'lname', TRUE );
        $data[ 'email' ] = $this->input->post( 'email', TRUE );
        $data[ 'provider_id' ] = $this->input->post( 'provider_id', TRUE );
        $data[ 'register_frontend' ] = $this->input->post( 'frontend_id', TRUE );
        $data[ 'prefix' ] = '';//$this->input->post( 'prefix', TRUE );
        $data[ 'login' ] = $this->input->post( 'login', TRUE );
        $data[ 'password' ] = $this->input->post( 'pass', TRUE );
        $data[ 'ctime' ] = date( 'Y-m-d H:i:s' );
        $data[ 'active' ] = 0;
        //$this->settings_model->debugLog(__CLASS__.'->'.__FUNCTION__.' START');
        //$this->settings_model->debugLog($data);
        $insert=false;


        if ( $id = $this->input->post( 'id', TRUE ) ) {
            //$this->settings_model->debugLog('UPDATE');
            $this->db_master->where( 'id', $id );
            $this->db_master->update( 'lib_users', $data );
        } else {
            $this->db_master->insert( 'lib_users', $data );

            $query = $this->db_master->query('select id from lib_users where login ='.$this->db->escape($data[ 'login' ]));
            $user=$query->result_array();
            if ( !empty($user[0]) ) {
                $id = $user[0]['id'];
                $this->load->helper( 'emailer' );
                $this->load->model( 'cpregister_model');
                // Отправляем пользователю
                $link=$this->cpregister_model->CreateActivateLink($id);
                $insert=true;
                //$this->settings_model->debugLog('INSERT ID:'.$id.' -> '.$link);
            } else {
                return FALSE;
            }
        }


        //$this->builder->login_user($data['login'], $data['password']);
        $meta = $this->input->post('meta');
        // Company
        if(empty($meta['company_name'])) $meta['company_name']=$data[ 'fname' ].' '.$data[ 'lname' ];

        $meta['lic_name'] = $data[ 'fname' ].' '.$data[ 'lname' ];
        $meta['bill_name'] = $data[ 'fname' ].' '.$data[ 'lname' ];
        $meta['ship_name'] = $data[ 'fname' ].' '.$data[ 'lname' ];

        $meta['lic_company'] = $meta['company_name'];
        $meta['bill_company'] = $meta['company_name'];
        $meta['ship_company'] = $meta['company_name'];

        $meta['lic_country'] = $meta['country'];
        $meta['bill_country'] = $meta['country'];
        $meta['ship_country'] = $meta['country'];

        $meta['lic_phone'] = $meta['phone'];
        $meta['bill_phone'] = $meta['phone'];
        $meta['ship_phone'] = $meta['phone'];

        $meta['lic_street1'] = '';
        $meta['bill_street1'] = '';
        $meta['ship_street1'] = '';

        $meta['lic_state'] = '';
        $meta['bill_state'] = '';
        $meta['ship_state'] = '';

        $meta['lic_city'] = '';
        $meta['bill_city'] = '';
        $meta['ship_city'] = '';

        $meta['lic_zip'] = '';
        $meta['bill_zip'] = '';
        $meta['ship_zip'] = '';


        if($meta) {
            $this->update_meta($id, $meta);
        }
        if($insert){
            Emailer::GetInstance()->LoadTemplate( 'touser-registration-confirmation' )
                ->TakeSenderSystem()
                //->TakeRecipientFromLogin( $this->input->post( 'login', TRUE ) )
                ->SetRecipientEmail($this->input->post( 'email', TRUE ))
                ->SetTemplateValue( 'verification', 'link', $link )
                ->SetTemplateValue( 'user', $data )
                ->SetMailType('html')
                ->Send();
            Emailer::GetInstance()->Clear();
            // Отправляем администратору
            Emailer::GetInstance()->LoadTemplate( 'toadmin-user-register' )
                ->TakeSenderSystem()
                ->TakeRecipientAdmin()
                ->SetTemplateValue( 'register', 'login', $data[ 'login' ] )
                ->SetTemplateValue( 'register', 'provider', $data[ 'provider_id' ] )
                ->SetMailType('html')
                ->Send();
            Emailer::GetInstance()->Clear();
            // Отправляем провайдеру
            /*Emailer::GetInstance()->LoadTemplate( 'toprovider-user-register' )
                ->TakeSenderSystem()
                ->TakeRecipientFromId( $data[ 'provider_id' ] )
                ->SetTemplateValue( 'register', 'login', $data[ 'login' ] )
                ->SetMailType('html')
                ->Send();
            Emailer::GetInstance()->Clear();*/
        }
        return TRUE;
    }
    function register_email($id,$user){
        $this->load->helper( 'emailer' );
        $this->load->model( 'cpregister_model');
        $link=$this->cpregister_model->CreateActivateLink($id);
        Emailer::GetInstance()->LoadTemplate( 'touser-registration-confirmation' )
            ->TakeSenderSystem()
            //->TakeRecipientFromLogin( $this->input->post( 'login', TRUE ) )
            ->SetRecipientEmail($user['email'])
            ->SetTemplateValue( 'verification', 'link', $link )
            ->SetTemplateValue( 'user', $user )
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
        // Отправляем администратору
        Emailer::GetInstance()->LoadTemplate( 'toadmin-user-register' )
            ->TakeSenderSystem()
            ->TakeRecipientAdmin()
            ->SetTemplateValue( 'register', 'login', $user[ 'login' ] )
            ->SetTemplateValue( 'register', 'provider', $user[ 'provider_id' ] )
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
        return $link;
    }

    function update_meta($user_id, $meta = array()) {

        if($user_id && $meta) {
            foreach($meta as $key => $value) {
                $this->db->select('id, meta_value');
                $query = $this->db->get_where('lib_users_meta', array('meta_key' => $key, 'user_id' => $user_id));
                $res = $query->result_array();
                if($res) {
                    if($res[0]['meta_value'] != $value) {
                        $this->db_master->where('id', $res[0]['id']);
                        $this->db_master->update('lib_users_meta', array('meta_value' => $value));
                    }
                }
                else {
                    $this->db_master->insert('lib_users_meta', array('user_id' => $user_id, 'meta_key' => $key, 'meta_value' => $value ?: ""));
                }
            }
        }
    }


    function update_client()
    {
        $data['fname'] = $this->input->post('fname',true);
        $data['lname'] = $this->input->post('lname',true);
        $data['email'] = $this->input->post('email',true);
        $data['login'] = $this->input->post('login',true);
        $data['password'] = $this->input->post('pass',true);
        $data['provider_id'] = (int)$this->input->post('provider_id',true);

        if(!$data['password']){
            unset($data['password']);
        }

        if ($data['login'] && $data['provider_id'])
        {
            if($this->input->post('is_admin',true)){
                unset($data['provider_id']);
                $provider_id  = (int)$this->input->post('provider_id', true);
                $this->db_master->where('id', $provider_id);
                $this->db_master->update('lib_users', $data);
            }
            else{
                $this->db_master->where(array('login' => $this->input->post('login',true), 'provider_id' => (int)$this->input->post('provider_id', true)));
                $this->db_master->update('lib_users', $data);
            }
        }
    }
        
    #------------------------------------------------------------------------------------------------
    
    function add_subscriber()
    { 
    }

    #------------------------------------------------------------------------------------------------
    
    function apply_corporate()
    {
       $this->load->library('email');   
       
       $config['mailtype'] = 'html';
       $config['wordwrap'] = 0; 
       $this->email->initialize($config); 
      
       $temp['fname'] = $this->input->post('fname', true);  
       $temp['lname'] = $this->input->post('lname', true);  
       $temp['email'] = $this->input->post('email',true);
       $temp['password'] = $this->input->post('pass',true);
       $temp['admin_email'] = $this->settings['admin_email'];

       $data['body'] = $this->load->view('main/mail/corporate', $temp, true); 
         
       $this->email->from($this->settings['email']);
       $this->email->subject('Footage library - corporate account application'); 
       $this->email->message($data['body']);
       $this->email->to($this->settings['admin_email']);
       $this->email->send();      
    }

    #------------------------------------------------------------------------------------------------
    
    function send_confirmation($type='register')
    {
       $this->load->library('email');   
       
       $config['mailtype'] = 'html';
       $config['wordwrap'] = 0; 
       $this->email->initialize($config); 
      
       $temp['fname'] = $this->input->post('fname', true);  
       $temp['lname'] = $this->input->post('lname', true);  
       $temp['login'] = $this->input->post('login',true);
       $temp['password'] = $this->input->post('pass',true);
       $temp['admin_email'] = $this->settings['admin_email'];

       $data['body'] = $this->load->view('main/mail/'.$type, $temp, true); 
         
       $this->email->from($this->settings['email']);
       $this->email->subject($this->config->item('vendor_name')
         . ' Library - register confirmation message');
       $this->email->message($data['body']);
       $this->email->to($this->input->post('email',true));
       $this->email->send();
    }
                    
    #------------------------------------------------------------------------------------------------
    
    function get_countries()
    { 
      $query = $this->db->get('lib_countries');  
      $rows = $query->result_array();
     
      return array_merge(array($rows['246']), array($rows['247']),$rows); 
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_country($id)
    { 
      $this->db->where('id', $id);
      $query = $this->db->get('lib_countries');  
      $rows = $query->result_array();
     
      return $rows[0]; 
    }  
    
    #------------------------------------------------------------------------------------------------
    
    function upload_avatar($login)
    {
      if(is_uploaded_file($_FILES['avatar']['tmp_name']))
      {
        $size = getimagesize($_FILES['avatar']['tmp_name']);

       if($size[0] <= 100 && $size[1] <= 100 && ($size['mime'] == 'image/jpeg' || $size['mime'] == 'image/gif'))
       {
          $dest_dir = $this->config->item('avatar_dir');
          $ext = strtolower(substr($_FILES['avatar']['name'],-3));
          
          $file = $login.'.'.$ext;
          copy($_FILES['avatar']['tmp_name'], $dest_dir.'/'.$file);
          
          return $file;
        } 
        return false;
      }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function delete_avatar()
    {
      $id = $this->input->post('id',true);
      $editor = $this->get_client($id);
      if ($editor['avatar'])
      {
        $data['avatar'] = '';
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_users', $data);
        @unlink($this->config->item('avatar_dir').'/'.$editor['avatar']);
      } 
    }

    function update_views($id, $remote_addr){
        $query = $this->db->get_where('lib_provider_views_statistic', array('provider_id' => $id, 'remote_addr' => $remote_addr), 1);
        $res = $query->result_array();
        if($res){
            $this->db_master->set('count', 'count + 1', FALSE);
            $this->db_master->where('id', $res[0]['id']);
            $this->db_master->update('lib_provider_views_statistic');
        }
        else{
            $data = array(
                'provider_id' => $id,
                'remote_addr' => $remote_addr,
                'count' => 1
            );
            $this->db_master->insert('lib_provider_views_statistic', $data);
        }
    }

    function get_views_count($id){
        $this->db->where('provider_id', $id);
        $this->db->from('lib_provider_views_statistic');
        return $this->db->count_all_results();
    }

    function add_follower($login, $provider_id){
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login, $provider_id);
        if($user_id){
            $data = array(
                'user_id' => $user_id,
                'provider_id' => $provider_id
            );
            $this->db->where($data);
            $this->db->from('lib_followers');
            if(!$this->db->count_all_results()){
                $this->db_master->insert('lib_followers', $data);
            }
        }
    }

    function get_followers_count($provider_id){
        $this->db->select('user_id');
        $this->db->where('provider_id', $provider_id);
        $this->db->from('lib_followers');
        return $this->db->count_all_results();
    }

    function get_likes_count($provider_id){
        $this->db->select('id');
        $this->db->where('client_id', $provider_id);
        $this->db->where('like_count <>', 0);
        $this->db->from('lib_clips');
        return $this->db->count_all_results();
    }

    function get_purchases_count($provider_id){
        $this->db->select('oi.id');
        $this->db->from('lib_orders o');
        $this->db->join('lib_users u', 'o.client_id = u.id AND u.provider_id = ' . (int)$provider_id);
        $this->db->join('lib_orders_items oi', 'oi.order_id = o.id');
        $this->db->where('o.status', 3);
        return $this->db->count_all_results();
    }
    
}