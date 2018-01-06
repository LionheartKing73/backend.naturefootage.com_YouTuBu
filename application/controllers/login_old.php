<?php

class Login extends CI_Controller {

    function Login () {
        parent::__construct();
        $this->langs = $this->uri->segment( 1 );
    }
	
    #------------------------------------------------------------------------------------------------         
    
    function index()
    {
        //echo '<pre>';
        //var_export( $_REQUEST );
        //var_export( $this->input->get( 'login' ) ); die();
        //echo '</pre>';

        //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log/login.log', json_encode($_REQUEST) . PHP_EOL, FILE_APPEND);

        if($this->uri->segment(4) == 'logout') $this->logout();

        // Форсированная авторизация через форму
        if ( ( $this->input->post( 'login' ) && $this->input->post( 'password' ) && $this->input->post( 'enter' ) && $this->input->post( 'force' ) ) ) {
            $this->session->unset_userdata(
                array (
                    'uid'   => '',
                    'login' => '',
                    'name'  => ''
                )
            );
            echo "
                <form action='/en/login' method='POST' name='go'>
                    <input type='hidden' name='login' value='{$this->input->post( 'login' )}'>
                    <input type='hidden' name='password' value='{$this->input->post( 'password' )}'>
                    <input type='hidden' name='enter' value='Submit'>
                </form>
                <script type='text/javascript'>
                    window.go.submit();
                </script>";
            die();
        }

        // Форсированная авторизация через токен
        if ( isset( $_REQUEST[ 'token' ] ) && isset( $this->session->userdata[ 'login' ] ) && ( urlencode( $this->session->userdata[ 'login' ] ) != $_REQUEST[ 'login' ] ) ) {
            $this->session->sess_destroy();
            echo "
                <form method='GET' name='go'>
                    <input type='hidden' name='login' value='{$_REQUEST[ 'login' ]}'>
                    <input type='hidden' name='id' value='{$_REQUEST[ 'id' ]}'>
                    <input type='hidden' name='token' value='{$_REQUEST[ 'token' ]}'>
                    <input type='hidden' name='backend_page' value='{$_REQUEST[ 'backend_page' ]}'>
                </form>
                <script type='text/javascript'>
                    window.go.submit();
                </script>";
            die();
        }


        if($this->api->permission()) {
            if(isset($_REQUEST['redirect_url'])){
                header('Location: ' . $_REQUEST['redirect_url']);
                exit();
            }
            elseif(isset($_REQUEST['backend_page'])){
                redirect($this->langs . '/' . $_REQUEST['backend_page']);
            }
           $data['lang'] = $this->langs;  
           $content = $this->load->view('login/wellcome', $data, true); 
           $this->out($content, 1);  
        }
        else 
           $this->form();
    }
    
    #------------------------------------------------------------------------------------------------    
    
    function form()
    {

        if($this->input->post('enter', true)){
          $login =  $this->input->post('login', true);
          $password = $this->input->post('password', true);
            $id = $this->input->post('id', true);
            
          if($login && $password){

            $user = $this->get_user_auth($login, $password, $id);

            if($user){
              $session_data['uid'] = $user->id;
              $session_data['login'] = $user->login;
              $session_data['name'] = $user->fname.' '.$user->lname;
                if($user->is_editor){
                    $session_data['client_uid'] = $user->id;
                }
             
              $old_session = array('client_uid'=>'','client_login'=>'','client_name'=>'','client_country'=>'','client_corporate'=>'','currency'=>'');
              $this->session->unset_userdata($old_session);
              $this->session->set_userdata($session_data);
              $this->api->log('log_login');
              
              $path_after_login = $this->session->userdata('path_after_login');
              $this->session->unset_userdata('path_after_login');
              if (empty($path_after_login)) {
                  $path_after_login = $this->langs.'/login';
              }
              redirect($path_after_login);
            }
            else
              $this->error = $this->lang->line('incorrect_login');
          }
          else
            $this->error = $this->lang->line('empty_login');
        }
        elseif($_REQUEST['id'] && $_REQUEST['token']){

                $user = $this->get_provider_auth($_REQUEST['id'], $_REQUEST['token'], $_REQUEST[ 'login' ]);



                if ( $user ) {
                    /*
                    // Форсированная авторизация
                    if ( $this->input->get( 'force' ) ) {
                        $this->session->unset_userdata(
                            array (
                                'uid'   => '',
                                'login' => '',
                                'name'  => ''
                            )
                        );
                        echo "
                            <form method='GET' name='go'>
                                <input type='hidden' name='id' value='{$_REQUEST['id']}'>
                                <input type='hidden' name='token' value='{$_REQUEST['token']}'>
                            </form>
                            <script type='text/javascript'>
                                window.go.submit();
                            </script>";
                        die();
                    }
                    */
                }

                if($user){
                    $session_data['uid'] = $user->id;
                    $session_data['login'] = $user->login;
                    $session_data['name'] = $user->fname.' '.$user->lname;
                    if($user->is_editor){
                        $session_data['client_uid'] = $user->id;
                    }

                    $old_session = array('client_uid'=>'','client_login'=>'','client_name'=>'','client_country'=>'','client_corporate'=>'','currency'=>'');
                    $this->session->unset_userdata($old_session);
                    $this->session->set_userdata($session_data);
                    $this->api->log('log_login');

                    if(isset($_REQUEST['redirect_url'])){
                        header('Location: ' . $_REQUEST['redirect_url']);
                        exit();
                    }
                    else{
                        if(isset($_REQUEST['backend_page'])){
                            if($_REQUEST['backend_page'])
                                $path_after_login = $this->langs . '/' . $_REQUEST['backend_page'];
                            else
                                $path_after_login = $this->langs . '/login';
                        }
                        else{
                            $path_after_login = $this->session->userdata('path_after_login');
                            $this->session->unset_userdata('path_after_login');
                        }
                        if (empty($path_after_login)) {
                            $path_after_login = $this->langs.'/login';
                        }
                    }
                    redirect($path_after_login);
                }
                else
                    $this->error = $this->lang->line('incorrect_login');


        }
          
        $this->out();
    }
 
    #------------------------------------------------------------------------------------------------
    
    function get_user_auth($login, $password, $id = false)
    {
        if($id){
            $query = $this->db->query(
              'select lu.*, lug.is_editor
              from lib_users lu, lib_users_groups lug
              where lug.is_backend=1 and lug.id=lu.group_id and lu.login='.$this->db->escape($login).
                ' and lu.password='.$this->db->escape($password).' and lu.active=1' );// and lu.id = ' . (int)$id);
        }
        else{
            $query = $this->db->query(
                'select lu.*, lug.is_editor
                from lib_users lu, lib_users_groups lug
                where lug.is_backend=1 and lug.id=lu.group_id and lu.login='.$this->db->escape($login).
                ' and lu.password='.$this->db->escape($password).' and lu.active=1 and lu.group_id <> 13 and lu.provider_id = 0');
        }
        if($query->num_rows()) return $query->row(); 
        
        return false;
    }

    function get_provider_auth($id, $token, $login = ''){
        $query = $this->db->query(
            'select lu.*, lug.is_editor
            from lib_users lu, lib_users_groups lug
            where lug.is_backend=1 and lug.id=lu.group_id and lu.token = ' . $this->db->escape($token)
                . ' and lu.active=1 and lu.login = ' . $this->db->escape($login) );// and lu.id = ' . (int)$id);
        if($query->num_rows()) return $query->row();

        return false;
    }
        
    #------------------------------------------------------------------------------------------------     
    
    function logout()
    {
         $this->api->log('log_logout');
         $this->session->sess_destroy();  
         redirect($this->langs.'/login');
    }
    
    #------------------------------------------------------------------------------------------------

    function out($content=null, $type=2)
    {
        $this->builder->output(array('content'=>$content,'error'=>$this->error),$type);
    }       
}
