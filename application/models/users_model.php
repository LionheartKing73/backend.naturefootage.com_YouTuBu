<?php
class Users_model extends CI_Model {
  private $_endCounter=3;

  function Users_model() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);
      $this->load->helper('emailer');
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_users_count($filter) {
    $query = $this->db->query('select lu.id from lib_users as lu, lib_users_groups as lug where lu.group_id=lug.id '.$filter);
    return $query->num_rows();
  }

  #------------------------------------------------------------------------------------------------
  
  function get_users_list($filter, $order, $limit) {
    $query = $this->db->query('select lu.*, DATE_FORMAT(lu.ctime, \'%d.%m.%Y %T\') as ctime, lug.title as groups from lib_users as lu, lib_users_groups as lug where lu.group_id=lug.id '.$filter.$order.$limit);
    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function getUsersListByGroupId($group_id,$latter=0) {
    $alphabeticalFilter = $this->alphabeticalIndexToFilter($latter);
    if($group_id != 13){
        $query = $this->db->query('SELECT lu.id, lu.provider_id, lu.fname, lu.lname, lu.site, lu.email, lu.login, lum.meta_value AS company_name FROM lib_users AS lu
            LEFT JOIN lib_users_meta AS lum ON lu.id=lum.user_id AND lum.meta_key=\'company_name\' WHERE group_id='.$group_id.' AND lu.fname '.$alphabeticalFilter.'
            AND lu.active !=0 GROUP BY lu.id ORDER BY company_name, lu.fname');
    }else{
        $query = $this->db->query('SELECT lu.id, lu.provider_id, lu.fname, lu.lname, lu.site, lu.email, lu.login, lum.meta_value AS company_name, luma.meta_value AS description, lumav.meta_value AS avatar
            FROM lib_users AS lu
            LEFT JOIN lib_users_meta AS lum ON lu.id = lum.user_id
            AND lum.meta_key = \'company_name\'
            INNER JOIN lib_users_meta AS luma ON lu.id = luma.user_id
            AND luma.meta_key = \'description\' AND luma.meta_value RLIKE("[:alnum:]")
            LEFT JOIN lib_users_meta AS lumav ON lu.id = lumav.user_id
            AND lumav.meta_key = \'avatar\'
            INNER JOIN lib_clips AS lc ON lu.id = lc.client_id AND lc.active !=0
            WHERE group_id='.$group_id.' AND lum.meta_value '.$alphabeticalFilter.' AND lu.active !=0 GROUP BY lu.id ORDER BY company_name ASC');
    }


    return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------
  /**
    * @param $latter (0)
    *
    * @return string (REGEXP "^a.*|^b.*|^c.*")
  */
  function alphabeticalIndexToFilter($latter=0) {
      $alphas = range('a', 'z');
      $filter = ' REGEXP "';
      for($i=0;$i<$this->_endCounter;$i++){
          $filter .= '^'.$alphas[$latter+$i].'.*|';
          if($alphas[$latter+$i] == 'z') break;
      }
      $filter = substr($filter,0,-1);
      $filter .= '" ';
      return $filter;
  }
  #-----------------------------------------------------------------------------
  
  function notify_editor_active($user) {
     $this->load->library('email');

     $config['mailtype'] = 'html';
     $config['wordwrap'] = 0;
     $this->email->initialize($config);

     $temp['fname'] = $user->fname;
     $temp['lname'] = $user->lname;
     $temp['login'] = $user->login;
     $temp['password'] = $user->password;

     /*$message = $this->load->view('main/mail/notify_editor', $temp, true);

     $this->email->from($this->api->settings('admin_email'));
     $this->email->subject('You account at ' . $_SERVER['HTTP_HOST']);
     $this->email->message($message);
     $this->email->to($user->email);
     $this->email->send();*/

      $emailer = Emailer::GetInstance();
      $emailer->LoadTemplate('touser-activation');
      $emailer->TakeSenderSystem();
      $emailer->SetRecipientEmail($user->email);
      $emailer->SetTemplateValue('user', $temp);
      $emailer->SetMailType('html');
      $emailer->Send();
      $emailer->Clear();


  }
  
  #------------------------------------------------------------------------------------------------
  
  function change_visible($ids) {
    if(count($ids)) {

      $data = $this->db->query(
        'SELECT u.*
        FROM lib_users u
        INNER JOIN lib_users_groups ug ON ug.id = u.group_id
        WHERE ug.is_editor = 1 AND u.active = 0 AND u.id IN (' . implode(',', $ids) . ')' )
        ->result();
      if ($data) {
        $inactive_editors = array();
        foreach ($data as $row) {
          $inactive_editors[$row->id] = $row;
        }
      }

      foreach($ids as $id) {
        if($id==1) continue;
        $this->db_master->query('UPDATE lib_users set active = !active where id=' . $id);
        if (!empty($inactive_editors[$id])) {
          $this->notify_editor_active($inactive_editors[$id]);
        }
      }
    }
  } 
  
  #------------------------------------------------------------------------------------------------
  
  function delete_users($ids) {
    if(count($ids)) {
        $frontends=$this->db->get_where('lib_frontends',array('status'=>1))->result_array();
      foreach($ids as $id) {
        //$this->db_master->update('lib_users', array('active'=>0), array('id'=>$id));
          $user=$this->db->get_where('lib_users',array('id' => $id))->result_array();
          foreach($frontends as $host){
              $this->curlSend('http://'.$host['host_name'].'/login?action=remove',array('login'=>$user[0]['login'],'token'=>$user[0]['token']));
          }
          //$this->curlSend('http://dan.uhdfootage.local/login?action=remove',array('login'=>$user[0]['login'],'token'=>$user[0]['token']));
          $this->db_master->delete('lib_users', array('id' => $id));
          $this->db_master->delete('lib_users_meta', array('user_id' => $id));
          $this->db_master->delete('lib_followers', array('provider_id' => $id));
          $this->db_master->delete('lib_provider_exclusive_rate', array('provider_id' => $id));
          $this->db_master->delete('lib_provider_rf_exclusive_rate', array('provider_id' => $id));
          $this->db_master->delete('lib_provider_views_statistic', array('provider_id' => $id));
          $this->db_master->delete('lib_frontends', array('provider_id' => $id));
      }
    }
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_user($id) {
    $query = $this->db->query('select * from lib_users where id='.intval($id));
    $row = $query->result_array();
    $user=$row[0];

    $query = $this->db->query('select meta_key,meta_value from lib_users_meta where user_id='.intval($id));
    $rows = $query->result_array();
    foreach($rows as $row){
        $user['meta'][$row['meta_key']]=$row['meta_value'];
    }


      if (!isset($user['meta']['lic_name'])) $user['meta']['lic_name'] = '';
      if (!isset($user['meta']['bill_name'])) $user['meta']['bill_name'] = '';
      if (!isset($user['meta']['ship_name'])) $user['meta']['ship_name'] = '';

      if (!isset($user['meta']['lic_company'])) $user['meta']['lic_company'] = '';
      if (!isset($user['meta']['bill_company'])) $user['meta']['bill_company'] = '';
      if (!isset($user['meta']['ship_company'])) $user['meta']['ship_company'] = '';

      if (!isset($user['meta']['lic_country'])) $user['meta']['lic_country'] = '';
      if (!isset($user['meta']['bill_country'])) $user['meta']['bill_country'] = '';
      if (!isset($user['meta']['ship_country'])) $user['meta']['ship_country'] = '';

      if (!isset($user['meta']['lic_phone'])) $user['meta']['lic_phone'] = '';
      if (!isset($user['meta']['bill_phone'])) $user['meta']['bill_phone'] = '';
      if (!isset($user['meta']['ship_phone'])) $user['meta']['ship_phone'] = '';

      if (!isset($user['meta']['lic_street1'])) $user['meta']['lic_street1'] = '';
      if (!isset($user['meta']['bill_street1'])) $user['meta']['bill_street1'] = '';
      if (!isset($user['meta']['ship_street1'])) $user['meta']['ship_street1'] = '';

      if (!isset($user['meta']['lic_state'])) $user['meta']['lic_state'] = '';
      if (!isset($user['meta']['bill_state'])) $user['meta']['bill_state'] = '';
      if (!isset($user['meta']['ship_state'])) $user['meta']['ship_state'] = '';

      if (!isset($user['meta']['lic_city'])) $user['meta']['lic_city'] = '';
      if (!isset($user['meta']['bill_city'])) $user['meta']['bill_city'] = '';
      if (!isset($user['meta']['ship_city'])) $user['meta']['ship_city'] = '';

      if (!isset($user['meta']['lic_zip'])) $user['meta']['lic_zip'] = '';
      if (!isset($user['meta']['bill_zip'])) $user['meta']['bill_zip'] = '';
      if (!isset($user['meta']['ship_zip'])) $user['meta']['ship_zip'] = '';


    return $user;
  }


	function GetUserByLogin ( $login ) {
		$query = $this->db->query( "SELECT u . * , f.host_name FROM lib_users AS u LEFT JOIN lib_frontends AS f ON u.id = f.provider_id WHERE login = '{$login}' LIMIT 1" );
		$row = ( is_object( $query ) ) ? $query->result_array() : array();
        $user=$row[0];
        $query = $this->db->query('select meta_key,meta_value from lib_users_meta where user_id='.intval($user['id']));
        $rows = $query->result_array();
        foreach($rows as $row){
            $user['meta'][$row['meta_key']]=$row['meta_value'];
        }
        return $user;
	}

    function GetUserByEmail ( $email ) {
        $query = $this->db->query( "SELECT u . * , f.host_name FROM lib_users AS u LEFT JOIN lib_frontends AS f ON u.id = f.provider_id WHERE email = '{$email}' LIMIT 1" );
        $row = ( is_object( $query ) ) ? $query->result_array() : array();
        $user=$row[0];
        $query = $this->db->query('select meta_key,meta_value from lib_users_meta where user_id='.intval($user['id']));
        $rows = $query->result_array();
        foreach($rows as $row){
            $user['meta'][$row['meta_key']]=$row['meta_value'];
        }
        return $user;
    }

    function GetUserByActivationKey($login,$activation_key){
        $query = $this->db->query("SELECT u . * , f.host_name FROM lib_users AS u LEFT JOIN lib_frontends AS f ON u.id = f.provider_id WHERE login = '{$login}' AND activation_key = '{$activation_key}' LIMIT 1");
        $row = ( is_object( $query ) ) ? $query->result_array() : array();
        $user=$row[0];
        $query = $this->db->query('select meta_key,meta_value from lib_users_meta where user_id='.intval($user['id']));
        $rows = $query->result_array();
        foreach($rows as $row){
            $user['meta'][$row['meta_key']]=$row['meta_value'];
        }
        return $user;
    }

    function SaveActivationKey($login,$activation_key,$frontend_url){
        $this->db_master->where('login', $login);
        $this->db_master->update('lib_users', array('activation_key'=>$activation_key));
        $user= $this->GetUserByLogin($login);
        $this->SendActivationKey($user,$frontend_url);
        return $user;
    }
    function SendActivationKey($user,$frontend_url){
        $this->load->helper('emailer');
        Emailer::GetInstance()->LoadTemplate('touser-activation-key')
            ->TakeSenderSystem()
            ->SetRecipientEmail($user['email'])
            ->SetTemplateValue('user', $user)
            ->SetTemplateValue('frontend', 'url', $frontend_url)
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
    }
    function ResetUserPass($login,$pass){
        $this->db_master->where('login', $login);
        $this->db_master->update('lib_users', array('password'=>$pass,'activation_key'=>''));
        return $this->GetUserByLogin($login);
    }

  #------------------------------------------------------------------------------------------------
    function GetFrontendByUserId ($userId){
        $query = $this->db->query('SELECT f.host_name,f.name FROM lib_users AS u INNER JOIN `lib_frontends` AS f ON  f.id = u.register_frontend WHERE u.id='.intval($userId).' LIMIT 1');
        $rows = $query->result_array();
        return $rows[0];
    }

    function validField($str){
        return $str;//addslashes($str);//preg_replace('/[^,\.\+:\/\?\=\@\&A-Za-z0-9 _-]/i', '', $str);
    }
  #------------------------------------------------------------------------------------------------

    function save_user($id) {

        $data_content['fname'] = $this->validField($this->input->post('fname'));
        $data_content['lname'] = $this->validField($this->input->post('lname'));
        $data_content['group_id'] = $this->input->post('group_id');
        $data_content['email'] = $this->validField($this->input->post('email'));
        $data_content['login'] = $this->validField($this->input->post('login'));
        $data_content['password'] = $this->validField($this->input->post('password'));
        $data_content['prefix'] = $this->input->post('prefix');
        $data_content['site'] = $this->validField($this->input->post('site'));
		$data_content['zoho_id'] = $this->validField($this->input->post('zoho_id'));
		$data_content['provider_credits'] = $this->validField($this->input->post('provider_credits'));
		$data_content['enable_hdvideo'] = $this->input->post('enable_hdvideo') ? 1 : 0;
        $data_content['exclusive'] = $this->input->post('exclusive') ? 1 : 0;
        $data_content['storage_account'] = $this->input->post('storage_account') ? 1 : 0;

        if ($id != ''){
        $userPref = $this->db->query("SELECT prefix FROM lib_users WHERE id = ".$id." LIMIT 1 ")->result_array();
        }

        if ($data_content['group_id']   == 13){

            if(($data_content['prefix'] == '') OR ($data_content['prefix'] != $userPref[0]['prefix'])){

            if (strlen($data_content['lname']) < 3){
                $fl = substr($data_content['fname'], 0, 2);
            }else{
                $fl = substr($data_content['fname'], 0, 1);
            }

            for ($j = 1; $j <5; $j++){
                for ($i = 1; $i <6-$j; $i++){

                    $prefix = substr($data_content['fname'], 0, $j).substr($data_content['lname'], 0, $i);
                    $query = $this->db->query('SELECT * FROM lib_users WHERE prefix = "'.$prefix.'" LIMIT 1');

                    if($query->num_rows() == 0){
                        $data_content['provider_intent'] = '1';
                        $data_content['prefix'] = strtoupper($prefix);
                        break 2;
                    }
                }
            }

            }
        }

        $user = $this->get_user($id);

        if($user['storage_account'] != $data_content['storage_account']) {
            $data_content['storage_account_changed'] = 1;
        }

        $meta = $this->input->post('meta');

        /*
        $meta['lic_name'] = $data_content[ 'fname' ].' '.$data_content[ 'lname' ];
        $meta['bill_name'] = $data_content[ 'fname' ].' '.$data_content[ 'lname' ];
        $meta['ship_name'] = $data_content[ 'fname' ].' '.$data_content[ 'lname' ];


        if(!$meta['company_name']){
            $meta['company_name'] = '';
        }

        if(!$meta['country']){
            $meta['country'] = '';
        }

        if(!$meta['phone']){
            $meta['phone'] = '';
        }

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

*/
        if($id) {
            $this->db_master->where('id',  $id);
            $this->db_master->update('lib_users', $data_content);
        }
        else{
            $user=$this->db->get_where('lib_users',array('login'=>$data_content['login'],'email'=>$data_content['email']))->result_array();
            if(!empty($user[0])) return false;
            $data_content['ctime'] = date('Y-m-d H:i:s');
            $data_content['token'] = md5(uniqid() . microtime() . rand());
            $this->db_master->insert('lib_users', $data_content);
            $id = $this->db_master->insert_id();
            $this->frontends_insert_user($data_content['login'],$data_content['password'],$data_content['email'],$data_content['token'],$data_content['fname'],$data_content['lname'],$data_content['site'],$data_content['zoho_id'],$meta);
        }

        if($meta) {
            $this->update_meta($id, $meta);
        }
    }

    #------------------------------------------------------------------------------------------------

    function frontends_insert_user($login,$pass,$email,$token,$fname='',$lname='',$site='',$meta=array()){
        $user= array(
            'user_login' => $login,
            'user_pass' => $pass,
            'user_nicename' => $login,
            'user_email' => $email,
            'user_url' => $site,
            'user_registered' => date('Y-m-d H:i:s'),
            'display_name' => (!empty($fname) || !empty($lname))?$fname.' '.$lname : $login,
            'token' => $token
        );
        $meta['user_login']= $login;
        $meta['token']= $token;
        $frontends=$this->db->get_where('lib_frontends',array('status'=>1))->result_array();
       foreach($frontends as $host){
           $this->curlSend('http://'.$host['host_name'].'/login?action=insertuser',$user);
           $this->curlSend('http://'.$host['host_name'].'/login?action=insertusermeta',$meta);
       }
        /*$this->curlSend('http://dan.uhdfootage.local/login?action=insertuser',$user);
        $this->curlSend('http://dan.uhdfootage.local/login?action=insertusermeta',$meta);*/
    }

    #------------------------------------------------------------------------------------------------

    function save_sales_representative(){
        $data_content['fname'] = $this->validField($this->input->post('fname'));
        $data_content['lname'] = $this->validField($this->input->post('lname'));
        $data_content['color'] = $this->input->post('color');
        $data_content['rep_id'] = $this->input->post('rep_id');
        $data_content['user_id'] = $this->session->userdata('uid');
        if($data_content['rep_id'] && $data_content['user_id']){
            $this->db_master->insert('lib_sales_representatives', $data_content);
            $test = $this->db->last_query();
        }
    }

  #------------------------------------------------------------------------------------------------

    /**
     * @param $id - id of reps owner
     * @return mixed
     */
    function get_sales_representatives($id = null){
        $query_string = "select lsr.*, CONCAT(`lu`.`fname`, ' ', `lu`.`lname`) as username 
        from lib_sales_representatives as lsr 
        INNER JOIN lib_users as lu ON lu.id = lsr.user_id";
        if($id){
            $query_string.= " WHERE user_id=". $id;
        }

        $query = $this->db->query($query_string);
        return $query->result_array();
  }

  #------------------------------------------------------------------------------------------------

    function get_sales_representative($id){
        $res = array();
        if($id){
            $query = $this->db->query("
              SELECT lsr.*, lu.id as creator_id, CONCAT(`lu`.`fname`, ' ', `lu`.`lname`) as creator_name from lib_sales_representatives as lsr
              INNER JOIN lib_users as lu ON lu.id = lsr.rep_id
              WHERE lsr.id=" . $id);
            $res = $query->result_array();
        }
        return $res[0];
    }

  #------------------------------------------------------------------------------------------------

    function delete_rep($ids){
        if(count($ids)) {
            foreach($ids as $id) {
                $this->db_master->delete('lib_sales_representatives', array('id' => $id));
            }
        }
    }

  #------------------------------------------------------------------------------------------------

  function check_unique_login($id) {
     $query = $this->db->query('select id from lib_users where id!='.intval($id).' and login='.$this->db->escape($this->input->post('login')));
     
     if($query->num_rows()) return false; 
     return true;
  }
  
  #------------------------------------------------------------------------------------------------

    function check_unique_prefix($id) {
        $query = $this->db->query('select id from lib_users where id!='.intval($id).' and prefix='.$this->db->escape($this->input->post('prefix')));

        if($query->num_rows()) return false;
        return true;
    }

  #------------------------------------------------------------------------------------------------

    function check_prefix_variants($id) {

        $data_content['fname'] = $this->input->post('fname');
        $data_content['lname'] = $this->input->post('lname');
        $data_content['group_id'] = $this->input->post('group_id');

        $id = $this->input->post('id');
        $a = 0;
        if ($id != ''){
            $userPref = $this->db->query("SELECT prefix FROM lib_users WHERE id = ".$id." LIMIT 1 ")->result_array();
        }

        if ($data_content['group_id']   == 13){

            if(($data_content['prefix'] == '') OR ($data_content['prefix'] != $userPref[0]['prefix'])){
                for ($j = 1; $j <5; $j++){
                    for ($i = 1; $i <6-$j; $i++){

                        $prefix = substr($data_content['fname'], 0, $j).substr($data_content['lname'], 0, $i);
                        $query = $this->db->query('SELECT * FROM lib_users WHERE prefix = "'.$prefix.'" LIMIT 1');

                        if($query->num_rows() == 0){
                            $data_content['provider_intent'] = '1';
                            $data_content['prefix'] = strtoupper($prefix);
                            $a++;
                            break 2;
                        }
                    }
                }

            }
        }
       if ($a != 0){
           return true;
       }else{
            return false;
       }
    }

  #------------------------------------------------------------------------------------------------
  
  function get_logs($id, $limit) {
     $query = $this->db->query('select *, DATE_FORMAT(ctime, \'%d.%m.%Y %T\') as ctime from lib_log where user_id='.$id.' order by ctime desc'.$limit);
     return $query->result_array(); 
  }

  #------------------------------------------------------------------------------------------------ 
  
  function get_logs_count($id) {
     $query = $this->db->query('select * from lib_log where user_id='.$id);
     return $query->num_rows(); 
  }  
      
  #------------------------------------------------------------------------------------------------ 
  
  function save_user_permission($id, $ids) {
    if(count($ids)) {
      $data['permission'] = serialize($ids); 
     
      $this->db_master->where('id', $id);
      $this->db_master->update('lib_users', $data);
    }
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_users_groups() {
    $query = $this->db->query('select *, DATE_FORMAT(ctime, \'%d.%m.%Y %T\') as ctime from lib_users_groups');
    return $query->result_array();
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_countries() {
    $countries = $this->db->query('SELECT * FROM lib_countries ORDER BY name')->result_array();
    $top_countries = $this->db->query(
      "SELECT * FROM lib_countries WHERE code IN ('US', 'GB') ORDER BY code DESC")->result_array();
    return array_merge($top_countries, $countries);
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_content_providers($uid = null) {
    $filter = empty($uid) ? '' : ' AND u.id = ' . intval($uid);
    
    return $this->db->query(
      'SELECT u.id, u.login, u.fname, u.lname, g.title grp
      FROM lib_users u
      INNER JOIN lib_users_groups g ON g.id = u.group_id
      WHERE (g.is_editor = 1 OR g.is_admin = 1) ' . $filter . '
      ORDER BY login'
    )->result_array();
  }

  #------------------------------------------------------------------------------------------------

  function set_corporate_balance($id, $balance) {
    if ($balance < 0.00) {
      $balance = 0.00;
    }
    $this->db_master->update('lib_users', array('corporate_balance'=>$balance), array('id'=>$id));
  }

    function get_provider_by_login($login)
    {
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_provider_group_id();
        $sql = "SELECT id FROM lib_users WHERE group_id = ? AND login = ?";
        $query = $this->db->query($sql, array($group_id, $login));
        $rows = $query->result_array();
        return $rows[0]['id'];
    }

    function get_user_by_login($login)
    {
        $sql = "SELECT id FROM lib_users WHERE login = ?";
        $query = $this->db->query($sql, array($login));
        $rows = $query->result_array();
        return $rows[0]['id'];
    }

    function get_user_data_by_login($login)
    {
        $sql = "SELECT * FROM lib_users WHERE login = ? LIMIT 1";
        $query = $this->db->query($sql, array($login));
        $rows = $query->result_array();
        return $rows[0];
    }

    function get_user_by_id($id)
    {
        $sql = "SELECT * FROM lib_users WHERE id = ? LIMIT 1";
        $query = $this->db->query($sql, array($id));
        $rows = $query->result_array();
        return $rows[0];
    }

    function get_provider_by_id($id)
    {
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_provider_group_id();
        $sql = "SELECT id FROM lib_users WHERE group_id = ? AND id = ?";
        $query = $this->db->query($sql, array($group_id, $id));
        $rows = $query->result_array();
        return $rows[0]['id'];
    }

    function get_providers_list(){
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_provider_group_id();
        $query = $this->db->query('SELECT * FROM lib_users WHERE group_id = ? ORDER BY fname ASC', array($group_id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_providers_list_filtered(){
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_provider_group_id();
        $query = $this->db->query('SELECT * FROM lib_users WHERE group_id = ? AND id IN (SELECT DISTINCT client_id FROM lib_clips WHERE active = 1) ORDER BY fname ASC', array($group_id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_administrators_list(){
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_administrator_group_id();
        $query = $this->db->query('SELECT * FROM lib_users WHERE group_id = ?', array($group_id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clients_list($provider_id = 0){
        $this->load->model('groups_model');
        $group_id = $this->groups_model->get_client_group_id();
        if($provider_id)
            $query = $this->db->query('SELECT * FROM lib_users WHERE group_id = ? AND provider_id = ?', array($group_id, $provider_id));
        else
            $query = $this->db->query('SELECT * FROM lib_users WHERE group_id = ?', array($group_id));

        $rows = $query->result_array();
        return $rows;
    }

    function set_dir($id, $dir){
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_users', array('dir' => $dir));
    }

    function get_user_meta($user_id) {
        $query = $this->db->get_where('lib_users_meta', array('user_id' => $user_id));
        $rows = $query->result_array();
        $metadata = array();
        if($rows) {
            foreach($rows as $row) {
                $metadata[$row['meta_key']] = $row['meta_value'];
            }
        }
        return $metadata;
    }

    function get_wp_userdata ( array $filter = NULL ) {
        $filter = $this->create_wp_filter( $filter );
        $result = $this->db->query( "
            SELECT
                lib_users.id,
                lib_users.login AS user_login,
                lib_users.password AS user_pass,
                lib_users.email AS user_email,
                lib_users.site AS user_url,
                lib_users.ctime AS user_registered,
                NULL AS user_activation_key,
                0 AS user_status
            FROM lib_users
            {$filter}
            LIMIT 1;"
        );

        return ( is_object( $result ) ) ? $result->row_array() : array();
    }

    function get_wp_user_otherdata ($user_id) {
        $result = $this->db->query( "
            SELECT lib_users_groups.is_admin
            FROM lib_users
            JOIN lib_users_groups
            	ON lib_users_groups.id = lib_users.group_id
            WHERE lib_users.id = {$user_id}
            LIMIT 1;"
        );
        return ( is_object( $result ) ) ? $result->row_array() : array();
    }

    function get_wp_user_metadata ($user_id, $provider_id = 0) {

        $query = $this->db->get_where('lib_users', array('id' => $user_id));
        $rows = $query->result_array();
        $metadata = array();
        if($rows) {
            $user = $rows[0];
            $query = $this->db->get_where('lib_users_meta', array('user_id' => $user_id));
            $rows = $query->result_array();
            if($rows) {
                foreach($rows as $row) {
                    $metadata[$row['meta_key']] = $row['meta_value'];
                }
                $metadata['first_name'] = $user['fname'];
                $metadata['last_name'] = $user['lname'];
            }

            if ($provider_id) {
                if ($user['id'] == $provider_id) {
                    $metadata['wp_user_level'] = 10;
                    $metadata['wp_capabilities'] = array(
                        'administrator' => TRUE
                    );
                } else {
                    $this->db->select('is_admin, is_editor','is_frontend');
                    $query = $this->db->get_where('lib_users_groups', array('id' => $user['group_id']));
                    $rows = $query->result_array();
                    if($rows) {
                        $rights = $rows[0];
                        if($rights['is_frontend'] == 1) {
                            $metadata['wp_user_level'] = 10;
                            $metadata['wp_capabilities'] = array(
                                'administrator' => TRUE
                            );
                        }
						elseif($rights['is_admin'] == 1) {
                            $metadata['wp_user_level'] = 10;
                            $metadata['wp_capabilities'] = array(
                                'backend_administrator' => TRUE
                            );
                        } elseif ($rights['is_editor'] == 1) {
                            $metadata['wp_user_level'] = 9;
                            $metadata['wp_capabilities'] = array(
                                'guest_administrator' => TRUE
                            );
                        } else {
                            $metadata['wp_user_level'] = 0;
                        }
                    }
                }
            }
        }

        return $metadata;
    }

    function update_wp_userdata ( $login, $data = array() ) {
        if($data) {
            foreach($data as $k=>$v){
                if(is_array($v)){
                    foreach($v as $kk=>$vv){
                        $data[$k][$kk]=$this->validField($vv);
                    }
                }else{
                    $data[$k]=$this->validField($v);
                }
            }
            $this->db->select('id, group_id, fname, lname, email');
            $query = $this->db->get_where('lib_users', array('login' => $login));
            $rows = $query->result_array();
            if($rows) {
                $meta = array();
                if(isset($data['meta'])) {
                    $meta = $data['meta'];
                    unset($data['meta']);
                }

                if(isset($data['is_provider'])){
                    $data['provider_intent'] = 1;
                    unset($data['is_provider']);
                        //$this->load->model('groups_model');
                        //$group_id = $this->groups_model->get_provider_group_id();

                        // Wants to become a provider
                        //if($group_id != $rows[0]['group_id']) {
                            //$data['group_id'] = $group_id;
                            //$data['prefix'] = $this->create_provider_prefix($rows[0]['id'], $rows[0]['fname'], $rows[0]['lname']);

                            $this->load->helper('emailer');
    //                        $emailer = Emailer::GetInstance();
    //                        $emailer->LoadTemplate('toadmin-provider-registered');
    //                        $emailer->TakeSenderSystem();
    //                        $emailer->TakeRecipientAdmin();
    //                        $emailer->SetTemplateValue('provider', $this->get_user($rows[0]['id']));
    //                        $emailer->Send();
    //                        $emailer->Clear();

                            $emailer = Emailer::GetInstance();
                            $emailer->LoadTemplate('touser-provider-registered');
                            $emailer->TakeSenderSystem();
                            $emailer->SetRecipientEmail($rows[0]['email']);
                            $emailer->SetTemplateValue('provider', $this->get_user($rows[0]['id']));
                            $emailer->SetMailType('html');
                            $emailer->Send();
                            $emailer->Clear();

                            $emailer = Emailer::GetInstance();
                            $emailer->LoadTemplate('toadmin-provider-registered');
                            $emailer->TakeSenderSystem();
                            $emailer->SetRecipientEmail("support@footagesearch.com");
                            $emailer->SetTemplateValue('provider', $this->get_user($rows[0]['id']));
                            $emailer->SetTemplateValue( 'link','edit', 'http://'.$_SERVER['HTTP_HOST'].'/en/users/edit/'.$rows[0]['id'] );
                            $emailer->SetMailType('html');
                            $emailer->Send();
                            $emailer->Clear();

                        //}

                }

                if ($data) {
                    $this->db_master->where('id', $rows[0]['id']);
                    $this->db_master->update('lib_users', $data);
                }
                // Company
                $meta['company_name']=(empty($meta['company_name'])) ? $rows[0]['fname'].' '.$rows[0]['lname'] : $meta['company_name'];
                if($meta) {
                    $this->update_meta($rows[0]['id'], $meta);
                }
            }
        }
    }

    public function update_meta($user_id, $meta) {
        if($user_id && $meta) {
            foreach($meta as $key => $value) {
                $value=$this->validField($value);
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
                    $this->db_master->insert('lib_users_meta', array('user_id' => $user_id, 'meta_key' => $key, 'meta_value' => $value));
                }
            }
        }
    }

    private function create_wp_filter ( array $filter ) {
        if ( $filter ) {
            $filters = array();
            foreach ( $filter as $field => $value ) {
                $filters[ ] = "lib_users.{$field} = '{$value}'";
            }
            $filter = implode( ' AND ', $filters );
            $filter && $filter = "WHERE {$filter}";
            return $filter;
        }
        return NULL;
    }

    public function create_provider_prefix($id, $first_name = '', $last_name = '') {
        if($first_name && !$last_name)
            $last_name = $first_name;
        if($last_name && !$first_name)
            $first_name = $last_name;
        if($first_name && $last_name) {
            $first_name_len = strlen($first_name);
            $last_name_len = strlen($last_name);
            for($i = 0; $i < $first_name_len; $i++) {
                $first = $first_name[$i];
                for($j = 0; $j < $last_name_len; $j++) {
                    $second = $last_name[$j];
                    $prefix = strtoupper($first . $second);
                    if(!$this->is_prefix_exists($prefix))
                        return $prefix;
                }
            }
        }
        return 'CP' . $id;
    }

    function is_prefix_exists($prefix) {
        $this->db->select('id');
        $this->db->where('prefix', $prefix);
        $this->db->from('lib_users');
        return $this->db->count_all_results();
    }


    function is_username_exists($username) {
        $this->db->select('id');
        $this->db->where('login', $username);
        $this->db->from('lib_users');
        return $this->db->count_all_results();
    }

    function is_email_exists($email) {
        $this->db->select('id');
        $this->db->where('email', $email);
        $this->db->from('lib_users');
        return $this->db->count_all_results();
    }

    function is_username_valid($username){
        //return !preg_match('/[^a-zA-Z0-9_-]/i',$username);
        return !preg_match('/ |\!|\#|\$|\%|\^|\*|\(|\)/i',$username);
    }

    function get_storage_account($username) {
        $this->db->select('storage_account');
        $this->db->where('login', $username);
        $query = $this->db->get('lib_users');
        $res = $query->result_array();
        return $res[0]['storage_account'];
    }

    function get_users_with_storage_account() {
        $this->db->where('storage_account', 1);
        $query = $this->db->get('lib_users');
        $res = $query->result_array();
        return $res;
    }

    /**
     * @description return user by lab id
     * @param $lab_id
     * @return mix $user
     */
    function get_lab_user($lab_id){
        $this->db->select('lib_users');
        $this->db->join('lib_labs_users', 'lib_labs_users.user_id=lib_users.id');
        $this->db->where('lib_labs_users.lab_id', $lab_id);
        return $this->db->get()->row();
    }

    /**
     * @description return users by lab id
     * @param $lab_id
     * @return mix $user
     */
    function get_lab_users($lab_id){
        $this->db->join('lib_labs_users', 'lib_labs_users.user_id=lib_users.id');
        $this->db->where('lib_labs_users.lab_id', $lab_id);
        return $this->db->get('lib_users')->result_array();
    }

    /**
     * Enter $idOrLogin if $field FALSE return object ELSE return 'id' OR 'login' $object->$field
     * @param $idOrLogin - id OR login user
     * @param $field bool|mix - name field return if FALSE return object
     * @return mix $obj->id AND $obj->login
     */
    function get_id_and_login($idOrLogin,$field=false){
        $res= $this->db->query("SELECT id,login FROM lib_users WHERE id='$idOrLogin' OR login='$idOrLogin' LIMIT 1")->result();
        if(!$field){
            return $res[0];
        }else{
            return $res[0]->$field;
        }
    }

    function sendOfflineClips($user_id){
        $user=$this->get_user($user_id);
        $this->load->helper('emailer');
        Emailer::GetInstance()->LoadTemplate('touser-offline-clips')
            ->TakeSenderSystem()
            ->SetRecipientEmail($user['email'])
            ->SetTemplateValue('user', $user)
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
    }

    /**
     * @param $user_id
     * @return bool
     */
    function sendEmailChanged($login, $email = '')
    {
        $user=$this->get_user_data_by_login($login);
        return Emailer::sendHtmlEmailUsingTemplate($user, 'reset-email', $email);
    }

    /**
     * @param $user_id
     * @return bool
     */
    function sendPasswordChanged($login)
    {
        $user=$this->get_user_data_by_login($login);
        return Emailer::sendHtmlEmailUsingTemplate($user, 'reset-password');
    }

    function curlSend($url,$post=null){
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '60');
        if(!empty($url)){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        else{
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('result'=>$result,'status'=>$http_status);
    }

    function debug($file,$line,$msg,$note=''){
        $this->db->insert('debug',array('file'=>$file,'line'=>$line,'msg'=>addslashes($msg),'note'=>addslashes($note)));
    }
}
