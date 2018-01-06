<?php
class Register extends AppController {


    var $method;
    var $error;

    function __construct()
    {
        parent::__construct();
        $this->settings = $this->api->settings();
        $this->load->model('register_model');
        $this->load->model('groups_model');
        $this->method = $this->uri->segment(3);
    }

    #------------------------------------------------------------------------------------------------

    function index() {
        switch($this->method) {
            case 'logout': $this->logout(); break;
            case 'editor': $this->editor(); break;
            case 'sales': $this->sales(); break;
            default: $this->client();
        }
    }

    #------------------------------------------------------------------------------------------------

    function client() {
        if($this->input->post('register') && $this->check_cp_details()) {
            $this->register_model->save_client();
            $data['message'] = $this->lang->line('registered');
        }

        if($this->error) $data = $_POST;

        $data['action'] = 'register.html';
        $data['countries'] = $this->register_model->get_countries();
        $data['error'] = $this->error;
        $data['lang'] = $this->langs;


        $content['title'] = $this->lang->line('register');
        $content['body'] = $this->load->view('register/content', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function editor() {
        if($this->input->post('register') && $this->check_cp_details()) {
            $this->register_model->save_editor();
            $data['message'] = $this->lang->line('cp_registered');
        }

        if($this->error) $data = $_POST;

        $data['action'] = 'register/editor.html';
        $data['error'] = $this->error;


        $content['title'] = $this->lang->line('register');
        $content['body'] = $this->load->view('register/editor', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function account() {

        $data['menu'] = $this->load->view('main/ext/editormenu', array('lang' => $this->langs), true);
        $data['has_unreg_bin_items'] = $this->session->userdata('unreg_bin_items')? 'true' : 'false';

        $content['title'] = $this->lang->line('client_account');
        $content['body'] = $this->load->view('register/account', $data, true);


        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function profile() {
        $group = $this->session->userdata('client_uid') ?
            $this->groups_model->get_group_by_user($this->session->userdata('client_uid')) : false;

        if ($group['is_client']) {
            $this->profile_client();
        }
        elseif ($group['is_editor']) {
            $this->profile_editor();
        }
    }

    #------------------------------------------------------------------------------------------------

    function profile_client() {
        if($this->input->post('register') && $this->check_details($this->input->post('id',true))) {
            $this->register_model->save_client();
            $data['message'] = $this->lang->line('registered');

            $uri = $this->input->post('id', true) ?
                '/register/account' : $this->session->userdata('last_page');
            if (!$uri) {
                $uri = 'index';
            }
            redirect($uri);

        }

        $data = ($this->error) ? $_POST : $this->register_model->get_client($this->session->userdata('client_uid'), $this->langs);

        $data['action'] = 'register/profile.html';
        $country = $this->register_model->get_country($data['country_id']);
        $data['country_name'] = $country['name'];
        $data['error'] = $this->error;

        $data['menu'] = $this->load->view('main/ext/editormenu', null, true);

        $content['title'] = $this->lang->line('profile').' :: Client account';
        $content['body'] = $this->load->view('register/content', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function profile_editor() {
        if($this->input->post('register') && $this->check_cp_details($this->input->post('id',true))) {

            $avatar = $this->register_model->upload_avatar($this->input->post('login'));
            if ($_FILES['avatar']['tmp_name'] && !$avatar) {
                $this->error = $this->lang->line('incorrect_avatar');
            }
            else {
                $this->register_model->save_editor($avatar);
                $data['message'] = $this->lang->line('cp_registered');
                redirect('/register/account');
            }
        }
        else if($this->input->post('delete_avatar')) {
            $this->register_model->delete_avatar();
        }

        $data = ($this->error) ? $_POST : $this->register_model->get_client($this->session->userdata('client_uid'), $this->langs);

        $data['action'] = 'register/profile.html';
        $country = $this->register_model->get_country($data['country_id']);
        $data['country_name'] = $country['name'];
        $data['error'] = $this->error;

        $data['menu'] = $this->load->view('main/ext/editormenu', null, true);

        $content['title'] = $this->lang->line('profile').' :: Editor account';
        $content['body'] = $this->load->view('register/editor', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function logout() {
        $this->api->log('log_logout');
        $this->session->sess_destroy();
        $uri = $this->session->userdata('last_page');
        if (!$uri) {
            $uri = 'index';
        }
        redirect($uri);
    }

    #------------------------------------------------------------------------------------------------

    function check_details($id = null) {
        if(!$this->input->post('fname', true) || !$this->input->post('lname', true) ||
            !$this->input->post('country_id', true) || !$this->input->post('postcode', true) ||
            !$this->input->post('email', true) || !$this->input->post('city', true) ||
            !$this->input->post('pass', true) || !$this->input->post('pass2', true)) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }

        if(!$this->api->check_email($this->input->post('email', true))) {
            $this->error = $this->lang->line('incorrect_email');
            return false;
        }

        $pass = $this->input->post('pass');
        $pass2 = $this->input->post('pass2');

        if(strlen($pass) < 6 || strlen($pass2) < 6) {
            $this->error = $this->lang->line('incorrect_length');
            return false;
        }

        if($pass != $pass2) {
            $this->error = $this->lang->line('confirm_password');
            return false;
        }

        if($this->register_model->get_client_by_login($this->input->post('login', true)) && !$id) {
            $this->error = $this->lang->line('duplicate_login');
            return false;
        }

        return true;
    }

    #------------------------------------------------------------------------------------------------

    function check_cp_details($id = null) {
        if(!$this->input->post('fname', true) || !$this->input->post('lname', true)) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }

        if(!$this->api->check_email($this->input->post('email', true))) {
            $this->error = $this->lang->line('incorrect_email');
            return false;
        }

        $pass = $this->input->post('pass');
        $pass2 = $this->input->post('pass2');

        if(strlen($pass) < 6 || strlen($pass2) < 6) {
            $this->error = $this->lang->line('incorrect_length');
            return false;
        }

        if($pass != $pass2) {
            $this->error = $this->lang->line('confirm_password');
            return false;
        }

        if($this->register_model->get_user_by_login($this->input->post('login', true)) && !$id) {
            $this->error = $this->lang->line('duplicate_login');
            return false;
        }

        if(!$this->register_model->is_email_unique($this->input->post('email', true), $id)) {
            $this->error = $this->lang->line('duplicate_email');
            return false;
        }

        return true;
    }

    #------------------------------------------------------------------------------------------------

    function get_limit() {
        return ' LIMIT ' . intval($this->uri->segment(4)) . ',' . $this->settings['perpage'];
    }

    #------------------------------------------------------------------------------------------------

    function sales() {
        $this->load->model('invoices_model', 'inv_m');
        $this->load->model('users_model', 'usr_m');
        $this->load->model('images_model', 'img_m');
        $this->load->model('clips_model', 'clp_m');

        $client_id = $this->session->userdata('client_uid');

        $sales_count = $this->inv_m->get_sales_count($client_id);

        if ($sales_count) {
            $limit = $this->get_limit();

            $period = $this->prepare_period();

            $data['items'] = $this->inv_m->get_sales_stat($client_id, $limit, $period);

            $user = $this->usr_m->get_user($this->session->userdata('client_uid'));
            $item_data = array('folder'=>$user['login']);

            foreach($data['items'] as &$item) {
                $item_data['id'] = $item['item_id'];
                $item_data['code'] = $item['code'];
                switch ($item['item_type']) {
                    case 1:
                        $item['thumb'] = $this->img_m->get_image_path($item_data, 1);
                        break;
                    case 2:
                        $item['thumb'] = $this->clp_m->get_clip_thumb($item_data);
                        break;
                }
            }
            $pagination = $this->api->get_pagination(
                'register/sales', $sales_count, $this->settings['perpage']);
        }


        $data['menu'] = $this->load->view('main/ext/editormenu', null, true);
        $data['filter_menu'] = $this->get_filter_menu();
        $data['period_error'] = $this->session->userdata('period_error');

        $this->session->unset_userdata('period_error');

        $content['title'] = 'Sales statistics :: Editor account';
        $content['body'] = $this->load->view('register/sales', $data, true);
        $this->out($content, $pagination);
    }

    #------------------------------------------------------------------------------------------------

    function prepare_period() {

        $period = $this->uri->segment(4);

        $this->load->helper(array('date', 'form'));
        $this->load->library('validation');

        switch($period) {

            case 'month':
                $date_from = get_month_begin();
                $date_to = get_month_end();
                break;

            case 'year':
                $date_from = get_year_begin();
                $date_to = get_year_end();
                break;

            case 'period':
                $rules['datefrom']	= "exact_length[10]|xss_clean";
                $rules['dateto']	= "exact_length[10]|xss_clean";
                $this->validation->set_rules($rules);

                $fields['datefrom']	= 'datefrom';
                $fields['dateto']	= 'dateto';
                $this->validation->set_fields($fields);

                if ($this->validation->run() == TRUE)
                {
                    $date_from = $this->input->post('datefrom');
                    $date_to = $this->input->post('dateto');

                    if($date_from) $date_from = date(substr($date_from, 6, 4) . '-' . substr($date_from, 3, 2) . '-' . substr($date_from, 0, 2) . ' 00:00:00');
                    if($date_to) $date_to = date(substr($date_to, 6, 4) . '-' . substr($date_to, 3, 2) . '-' . substr($date_to, 0, 2) . ' 23:59:59');
                }
                else
                {
                    $this->session->set_userdata('period_error', 'Wrong period!');
                }

        }

        $sql_str = '';

        if($date_from && $date_to) {

            $sql_str = 'AND o.ctime BETWEEN
                    STR_TO_DATE(\'' . $date_from . '\', \'%Y-%m-%d %H:%i:%s\') AND
                    STR_TO_DATE(\'' . $date_to . '\', \'%Y-%m-%d %H:%i:%s\')';

        }
        elseif($date_from) {

            $sql_str = 'AND o.ctime >= STR_TO_DATE(\'' . $date_from . '\', \'%Y-%m-%d %H:%i:%s\')';

        }
        elseif($date_to) {

            $sql_str = 'AND o.ctime <= STR_TO_DATE(\'' . $date_to . '\', \'%Y-%m-%d %H:%i:%s\')';

        }

        if($period && ($period != $this->session->userdata('period_filter')))
            $this->session->set_userdata(array('period_filter'=>$period));

        return $sql_str;

    }

    #------------------------------------------------------------------------------------------------

    function get_filter_menu() {

        $menu_array = array (
            'month'     =>'Month',
            'year'      =>'Year',
            'alltime'   =>'All time'
        );

        $current_item = $this->session->userdata('period_filter');
        if($current_item == 'period')

            $current_item = ($current_item)? $current_item : 'alltime';

        if($current_item == 'period')
            $tmp[] = '<a href="javascript: void(0);" style="text-decoration: none" onClick="$(\'#period-bar\').toggle();">Period</a>';
        else
            $tmp[] = '<a href="javascript: void(0);" onClick="$(\'#period-bar\').toggle();">Period</a>';

        foreach($menu_array as $k=>$value) {
            $tmp[] = ($k == $current_item)? $value : anchor('/'.$this->uri->segment(2).'/'.$this->method.'/'.$k, $value);
        }

        $str = implode(' | ', $tmp);

        return $str;

    }

    #------------------------------------------------------------------------------------------------

    function out($content=null, $pagination=null) {
        if ($content) {
            $content['add_css'] = array('/data/css/register.css', '/data/css/calendar_black.css');
            $content['add_js'] = array(/*'/data/js/jquery.js',*/ '/data/js/calendar.js');
        }
        parent::out($content, $pagination, 0);
    }
}