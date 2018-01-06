<?php

class Users extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    
	function Users()
	{
		parent::__construct();	
          
        $this->load->model('users_model');
        $this->load->model('groups_model');
        $this->load->model('frontends_model');
        $this->api->save_sort_order('users');
        $this->load->model('download_hdvideos_model');
        $this->langs = $this->uri->segment(1); 
        $this->id = $this->uri->segment(4);  
        $this->settings = $this->api->settings();
        
        $this->save_filter_data();
	}
	
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
        show_404();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    {
        $filter = $this->get_filter_data();
        $order = $this->api->get_sort_order('users');
        $limit = $this->get_limit();
        $all = $this->users_model->get_users_count($filter);  
        
        $users = $this->users_model->get_users_list($filter, $order, $limit);
        $providers_group_id = $this->groups_model->get_provider_group_id();
        foreach($users as &$user){
            if($providers_group_id && $user['group_id'] == $providers_group_id){
                $user['is_provider'] = true;
            }
            //$frontends = $this->frontends_model->get_frontends_list(array('provider_id' => $user['id']), array('perpage' => 1));
            //if($frontends)
                //$user['frontend_id'] = $frontends[0]['id'];

        }
        $data['users'] = $users;
        $data['uri'] = $this->api->prepare_uri();
        $data['filter'] = $this->session->userdata('filter_users');  
        $data['lang'] = $this->langs;
        $data['groups'] = $this->users_model->get_users_groups();
        
        $this->path = 'Permission manager / User accounts';
         
        $content = $this->load->view('users/view', $data, true);
        $pagination = $this->api->get_pagination('users/view',$all,$this->settings['perpage']);
        
        $this->out($content, $pagination);    
    }
    
    #------------------------------------------------------------------------------------------------

    function sales_representatives(){
        $data = array();
        if($_POST){
            $this->users_model->save_sales_representative();
        }
        $data['lang'] = $this->langs;
        $data['admin_users'] = $this->users_model->get_administrators_list();
        $data['sales_representatives'] = $this->users_model->get_sales_representatives();
        $content = $this->load->view('users/sales_representatives', $data, true);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1, 'message' => 'success');
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
        else{
            $this->out($content);
        }
    }

    #------------------------------------------------------------------------------------------------

    function delete_rep(){
        $id = $this->id;
        if($id){
            $this->users_model->delete_rep(array($id));
        }
        redirect($this->langs.'/users/sales_representatives');
    }

    #------------------------------------------------------------------------------------------------

    function visible()
    {   
        $this->users_model->change_visible($this->input->post('id'));
        $this->api->log('log_users_visible', $this->input->post('id'));    
        redirect($this->langs.'/users/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id');
 
        $this->api->log('log_users_delete', $ids);  
                 
        $this->users_model->delete_users($ids);
        redirect($this->langs.'/users/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
//        $query = $this->db->get('lib_users');
//        $users = $query->result_array();
//        foreach($users as $user) {
//            $meta = array();
//            $meta['description'] = $user['bio'];
//            $meta['company_name'] = $user['company'];
//            $meta['country'] = $user['country'];
//            $meta['phone'] = $user['phone'];
//            foreach($meta as $meta_key => $meta_value) {
//                if($meta_value)
//                    $this->db_master->insert('lib_users_meta', array('user_id' => $user['id'], 'meta_key' => $meta_key, 'meta_value' => $meta_value));
//            }
//        }
//        exit();

        if($this->input->post('save') && $this->check_details($this->id)){
          $this->users_model->save_user($this->id);
          
          if($this->id) $this->api->log('log_users_edit', $this->id);   
          else $this->api->log('log_users_new');
          
          redirect($this->langs.'/users/view'); 
        }

        $data = ($this->error) ? $_POST : $this->users_model->get_user($this->id);
        $data['countries'] = $this->users_model->get_countries();
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['groups'] = $this->users_model->get_users_groups();
        if($this->id)
            $data['meta'] = $this->users_model->get_user_meta($this->id);

        /*$data['meta_map']  =array(
            'question1' => 'Please describe the subjects and locations in your collection',
            'question2' => 'How many hours of marketable selects are in your collection?',
            'question3' => 'How many hours of marketable selects do you already have assembled?',
            'question4' => 'How many years have you been filming?',
            'question5' => 'Is cinematography your profession or hobby?',
            'question6' => 'Have you sold stock footage before?',
            'question7' => 'Is your stock footage collection currently represented by a stock footage agency?',
            'question8' => 'If yes, please list the agencies representing your footage, and whether these agreements are exclusive or non-exclusive',
            'question9' => 'What camera formats do you use?',
            'question10' => 'What editing system do you use?',
            'question11' => 'How soon do you anticipate submitting footage for online sales?',
            'question12' => 'How do you prefer we contact you?'
        );*/
		$data['hdvideos'] = $this->download_hdvideos_model->get_hdvalue();
		$data['meta_map']  =array(
            'question1' => 'Please describe the subjects and locations in your collection',
            'question2' => 'How many years have you been filming?',
            'question3' => 'Is cinematography your profession or hobby?',
            'question4' => 'Have you sold stock footage before?' ,
            'question5' => 'Is your stock footage collection currently represented by a stock footage agency?',
            'question6' => 'If yes, please list the agencies representing your footage, and whether these agreements are exclusive or non-exclusive:',
            'question7' => 'What camera formats do you use?',
            'question8' => 'What editing system do you use?',
            'question9' => 'How soon do you anticipate submitting footage for online sales?',
            'question10' => 'Please provide a link to view samples of your stock footage collection. If you would like to share your reel with us via other means, please let us know.',
            'question11' => 'How do you prefer we contact you?'
        );
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Permission manager / User accounts / ' . $action;
         
        $content = $this->load->view('users/edit', $data, true);  
        $this->out($content); 
    }

    #------------------------------------------------------------------------------------------------
    
    function logs()
    {
        $limit = $this->get_log_limit();
        $all = $this->users_model->get_logs_count($this->id);
        
        $data['logs'] = $this->users_model->get_logs($this->id, $limit); 
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        
        $this->path = 'Permission manager / User accounts / Logs';

        $content = $this->load->view('users/logs', $data, true); 
        $pagination = $this->api->get_pagination('users/logs/'.$this->id,$all,$this->settings['perpage']); 
         
        $this->out($content, $pagination); 
    }
            
    #------------------------------------------------------------------------------------------------
            
    function check_details($id=null)
    {  
       if(!$this->input->post('fname') || !$this->input->post('lname') || !$this->input->post('group_id') || !$this->input->post('email') || !$this->input->post('login') || !$this->input->post('password')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       
       if(!$this->users_model->check_unique_login($id)){
          $this->error = $this->lang->line('notunique_login');
          return false;
       }

        if (($this->input->post('group_id') == 13) AND ($this->input->post('prefix') == '' )){
            if(!$this->users_model->check_prefix_variants($id)){
                $this->error = $this->lang->line('prefix_novariants');
                return false;
            }
        }

        if($this->input->post('prefix') != ''){
            if(!$this->users_model->check_unique_prefix($id)){
                $this->error = $this->lang->line('notunique_prefix');
                return false;
            }
        }

        return true;
    }
        
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {  
       $words = $this->input->post('words');
       $active = $this->input->post('active');
       $group = $this->input->post('group');

       if($this->input->post('filter')){
         $temp['words'] = ($words) ? $words : '';
         $temp['active'] = ($active) ? $active : '';
         $temp['group'] = ($group) ? $group : '';

         $this->session->set_userdata(array('filter_users'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_users = $this->session->userdata('filter_users');

        if($filter_users){
 
          $active = $filter_users['active'];  
          $words = $filter_users['words'];  
          $group = $filter_users['group'];  
          
          if($active) $where[] = ($active==1) ? 'lu.active=1' : 'lu.active=0';
          if($group) $where[] = ($group) ? 'lu.group_id='.$group : '';
          if($words) $where[] = '(lu.fname like "%'.$words.'%" or lu.lname like "%'.$words.'%" or lu.email like "%'.$words.'%" or lu.login like "%'.$words.'%")';
          
          if(count($where)) return ' and '.implode(' and ',$where);
        }
        return '';
    } 
    
    #------------------------------------------------------------------------------------------------
        
    function get_limit()
    {
        return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
    }

    #------------------------------------------------------------------------------------------------
        
    function get_log_limit()
    {
        return ' limit '.intval($this->uri->segment(5)).','.$this->settings['perpage'];
    }
          
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content,'path'=>$this->path,'pagination'=>$pagination,
          'error'=>$this->error),1);
    }

    function create_frontend(){

        if($this->input->post('save') && $this->check_frontend_details()){
            $this->frontends_model->save_frontend();
            redirect($this->langs.'/users/view');
        }

        $data = ($this->error) ? $this->input->post()  : array();
        $data['lang'] = $this->langs;
        $data['provider_id'] = $this->id;

	    $query = $this->db->get_where('lib_users_meta', array('user_id' => $this->id, 'meta_key' => 'frontend_url'));
        $result = $query->row_array();
	    if($result && $result['meta_value'])
            $data['host_name'] = str_replace('http://', '', $result['meta_value']);

        $action = 'Create frontend';
        $this->path = 'Permission manager / User accounts / ' . $action;

        $content = $this->load->view('users/create_frontend', $data, true);
        $this->out($content);
    }

    function check_frontend_details(){
        if(!$this->input->post('name') || !$this->input->post('host_name')){
            $this->error = $this->lang->line('empty_fields');
            return false;
        }

        return true;
    }

    function get_user() {
        $user_data = $this->users_model->get_user($this->id);
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json');
            echo json_encode($user_data);
            exit();
        }
    }
}
