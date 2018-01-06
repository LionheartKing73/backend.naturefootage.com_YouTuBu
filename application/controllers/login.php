<?php

class Login extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->langs = $this->uri->segment(1);
        $this->db_master = $this->load->database('master', TRUE);
        $this->error = null;
    }

    function index()
    {
        if (isset($_REQUEST['get_backend_user']))
            $this->get_backend_user();
        elseif (isset($_REQUEST['login']) && $_REQUEST['login'])
            $this->login_backend_user();
        elseif (isset($_REQUEST['backend_page']))
            redirect($this->langs . '/' . $_REQUEST['backend_page']);
        elseif (isset($_REQUEST['logout']))
            $this->logout_backend_user();

        if ($this->uri->segment(4) == 'logout') $this->logout();

        if ($this->api->permission() && !isset($_REQUEST['force'])) {
//            if(isset($_REQUEST['redirect_url'])){
//                header('Location: ' . $_REQUEST['redirect_url']);
//                exit();
//            }
//            elseif(isset($_REQUEST['backend_page'])){
//                redirect($this->langs . '/' . $_REQUEST['backend_page']);
//            }
            $data['lang'] = $this->langs;
            $content = $this->load->view('login/wellcome', $data, true);
            $this->out($content, 1);
        } elseif (isset($_REQUEST['backend_page'])) {
            redirect($this->langs . '/' . $_REQUEST['backend_page']);
        } else
            $this->form();
    }

    function form()
    {

        if ($this->input->post('login', true) && $this->input->post('password', true)) {

            $login = $this->input->post('login', true);
            $password = $this->input->post('password', true);
            $id = $this->input->post('id', true);

            if ($login && $password) {

                $user = $this->get_user_auth($login, $password, $id);

                if ($user) {
                    $this->login_user($user);

                    $path_after_login = $this->session->userdata('path_after_login');
                    $this->session->unset_userdata('path_after_login');
                    if (empty($path_after_login)) {
                        $path_after_login = $this->langs . '/login';
                    }
                    redirect($path_after_login);
                } else
                    $this->error = $this->lang->line('incorrect_login');
            } else
                $this->error = $this->lang->line('empty_login');
        }

        $this->out();
    }

    function get_user_auth($login, $password, $id = false)
    {
        if ($id) {
            // Provider
            $query = $this->db->query('
                SELECT lu.*, lug.is_editor
                FROM lib_users lu
                INNER JOIN lib_users_groups lug ON lu.group_id = lug.id
                WHERE lu.login = ' . $this->db->escape($login) .
                ' AND lu.password = ' . $this->db->escape($password) . ' AND lu.active = 1'
            );
        } else {
            // Admin or Customer
            $query = $this->db->query(
                'SELECT lu.*, lug.is_editor
                FROM lib_users lu
                INNER JOIN lib_users_groups lug ON lug.id = lu.group_id
                WHERE lu.login = ' . $this->db->escape($login) .
                ' AND lu.password = ' . $this->db->escape($password) . ' AND lu.active = 1');
        }
        if ($query->num_rows()) return $query->row();

        return false;
    }

    function login_user($user)
    {
        if ($user) {
            /*if($user->group_id !=1){
                $data['lang'] = $this->langs;
                $content = $this->load->view('login/wellcome', $data, true);
                $this->out($content, 1);
            }else {*/
                $session_data['uid'] = $user->id;
                $session_data['login'] = $user->login;
                $session_data['name'] = $user->fname . ' ' . $user->lname;
                $session_data['group'] = $user->group_id;
                if ($user->is_editor) {
                    $session_data['client_uid'] = $user->id;
                }

                $old_session = array('client_uid' => '');
                $this->session->unset_userdata($old_session);
                $this->session->set_userdata($session_data);
                $this->db_master->update('lib_users', array('last_login' => date('Y-m-d H:i:s')), array('id' => $user->id));
                $this->api->log('log_login');

                $user_id = $this->session->userdata('uid');
                //Delete Temporary Keywords
                $this->db_master->query('DELETE FROM lib_keywords_notvisible WHERE  user_id = ' . $user->id);
                //Update User Keywords Status
                $this->db_master->query('UPDATE lib_keywords SET  hidden ="0" WHERE  provider_id = ' . $user->id);
            //}
        }
    }

    /**
     *  LOGIN IS CHECKED IN UPPER CASE TO FIX FRONTEND BUG WITH lower case login stored in db
     *  THIS MUST BE TEMPORARY SOLUTION (!)
     */
    function get_user_auth_by_token($login, $token)
    {
        $query = $this->db->query('
                SELECT lu.*, lug.is_editor
                FROM lib_users lu
                INNER JOIN lib_users_groups lug ON lu.group_id = lug.id
                WHERE lu.login = ' . $this->db->escape($login) .
             ' AND lu.token = ' . $this->db->escape($token) . ' AND lu.active = 1'
        );
        if ($query->num_rows()) return $query->row();
        return false;
    }

    function logout()
    {
        $this->api->log('log_logout');
        $this->session->sess_destroy();
		$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		if(strpos($url,'backend.nfstage.com'))
		{
        	redirect('http://www.nfstage.com/login?action=frontendlogout');
		}
		if(strpos($url,'backend.naturefootage.com'))
		{
        	redirect('http://www.naturefootage.com/login?action=frontendlogout');
		}
    }

    function get_backend_user()
    {
        $user = array();
        $user_id = $this->session->userdata('uid');
        if ($user_id) {
            $query = $this->db->query('SELECT login, password FROM lib_users WHERE id = ' . $user_id);
            $user_obj = $query->row();
            if ($user_obj) {
                $user = array('login' => $user_obj->login, 'password' => $user_obj->password);
            }
        }elseif(!empty($_REQUEST['login']) and !empty($_REQUEST['email'])){
            $query = $this->db->query('SELECT login, password FROM lib_users WHERE login = "'.stripcslashes($_REQUEST['login']).'" and email = "'.stripcslashes($_REQUEST['email']).'"');
            $user_obj = $query->row();
            if ($user_obj) {
                $user = array('login' => $user_obj->login, 'password' => $user_obj->password);
            }
        }
        header('content-type: application/javascript; charset=utf-8');
        echo $_REQUEST['callback'] . '(' . json_encode($user) . ')';
        exit();
    }

    function login_backend_user()
    {
        $login = $_REQUEST['login'];
        $token = $_REQUEST['token'];
        if ($login) {
            $user = $this->get_user_auth_by_token($login, $token);
            if ($user)
                $this->login_user($user);
        }

        if (isset($_REQUEST['redirect_url'])) {
            header('Location: ' . $_REQUEST['redirect_url']);
            exit();
        } elseif (isset($_REQUEST['backend_page'])) {
            redirect($this->langs . '/' . $_REQUEST['backend_page']);
        }
    }

    function logout_backend_user()
    {
        $this->api->log('log_logout');
        $this->session->sess_destroy();
        if (isset($_REQUEST['redirect_url'])) {
            header('Location: ' . $_REQUEST['redirect_url']);
            exit();
        }
    }


    function out($content = null, $type = 2)
    {
        $this->builder->output(array('content' => $content, 'error' => $this->error), $type);
    }


}
