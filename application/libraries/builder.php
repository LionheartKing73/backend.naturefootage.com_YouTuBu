<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Builder {

    var $CI;
    var $settings;
    var $visual_mode;

    // --------------------------------------------------------------------
    /**
     * Class constructor
     */

    function Builder() {
        $this->CI = &get_instance();
        $this->langs = $this->CI->uri->segment(1);
        $this->languages = $this->CI->config->item('support_languages');

        $this->CI->lang->load('site', $this->languages[$this->langs]);
        $this->CI->lang->load('messages', $this->languages[$this->langs]);
        $this->CI->lang->load('logs', $this->languages[$this->langs]);

        if($this->CI->session->userdata('uid') && $this->CI->session->userdata('login'))
            $this->visual_mode = 1;
    }

    // --------------------------------------------------------------------
    /**
     * Return list of modules
     */

    function output($data, $template=0) {
        switch($template) {
            case 0: $this->output_site($data); break;
            case 1: $this->output_admin($data); break;
            case 2: $this->output_login($data); break;
            case 3: $this->output_visual($data); break;
            default: $this->output_site($data);
        }
    }

    // --------------------------------------------------------------------
    /**
     * Build login page using login template view
     */

    function output_login($data) {
        $this->CI->load->view('main/login', $data);
    }

    // --------------------------------------------------------------------
    /**
     * Build login page using login template view
     */

    function output_visual($data) {
        $this->CI->load->view('main/visual', $data);
    }

    // --------------------------------------------------------------------
    /**
     * Build admin page using admin template view
     */

    function output_admin($data) {
        $data['langs'] = $this->get_lang_list('admin');
        $data['menu'] = $this->get_admin_menu();
        $data['admin'] = $this->CI->session->userdata('name');
        $uid = ($this->CI->session->userdata('uid')) ? $this->CI->session->userdata('uid') : $this->CI->session->userdata('client_uid');
        $this->CI->load->model('groups_model');
        $group = $this->CI->groups_model->get_group_by_user($uid);
        if($group['is_editor']){
            $data['is_provider'] = true;
        }

        $this->CI->load->view('main/admin', $data);
    }

    // --------------------------------------------------------------------
    /**
     * Build site page using site template view
     */

    function output_site($data) {
        $this->CI->lang->load('site', $this->languages[$this->langs]);
        $data = $this->prepare_data($data);

        $cart = $this->CI->session->userdata('cart');
        $bin = $this->CI->session->userdata('bin');

        $data['visual_mode'] = $this->visual_mode;
        $data['auth'] = $this->get_auth();
        $data['langs'] = $this->get_lang_list('site');
        $data['lang'] = $this->langs;
        $data['phrase'] = $this->CI->session->userdata('search_phrase');
        $data['mode'] = $this->CI->session->userdata('mode');
        $data['cart_count'] = count($cart['items']);
        $data['bin_count'] = count($bin['items']);
        $data['top'] = $this->get_top_menu();
        $data['bottom'] = $this->get_bottom_menu();
        if($data['template'] == 'start'){
            $data['news'] = $this->get_top_news();
            $data['cats_nav'] = $this->get_cats_menu();
            //$data['highlights'] = $this->get_highlights();
            $data['features'] = $this->get_features();
        }

        $this->CI->load->view('main/'.$this->get_template($data['template']), $data);
    }

    // --------------------------------------------------------------------
    /**
     * Return site template
     */

    function get_template($template) {
        if (!$template) {
            $template = 'default';
        }
        return $template;
    }

    // --------------------------------------------------------------------
    /**
     * Return prepared array of data
     */

    function prepare_data($data) {
        $temp = $data['content'];

        $list['template'] = $temp['template'];

        $list['meta']['title'] = ($temp['meta_title']) ? $temp['meta_title'] : $temp['title'];
        $list['meta']['desc'] = $temp['meta_desc'];
        $list['meta']['keys'] = $temp['meta_keys'];

        $list['add_css'] = $temp['add_css'];
        $list['add_js'] = $temp['add_js'];

        $list['content'] = $temp['body'];
        $list['pagination'] = $data['pagination'];

        return $list;
    }

    // --------------------------------------------------------------------
    /**
     * Return language list section for admin part
     */

    function get_lang_list($type) {
        $list = $this->CI->config->item('support_languages');
        $parts = explode('/',trim($_SERVER['REQUEST_URI'],'/'));
        unset($parts[0]);
        $uri = implode('/', $parts);

        if($uri=='publication/content' || $uri=='index.html') $uri = '';

        $inner['type'] = $type;
        $inner['current_lang'] = $this->langs;

        foreach($list as $k=>$v) {
            $data['name'] = $v;
            $data['url'] = '';
            if($k != $inner['current_lang']){
                if(!$uri){
                    if($this->CI->config->item('default_language') == $k){
                        $data['url'] = '/';
                    }
                    else{
                        $data['url'] = $k.'/index.html';
                    }
                }
                else{
                    $data['url'] = $k . '/' . $uri;
                }
            }

            $inner['lang_list'][] = $data;
        }

        return $this->CI->load->view('main/ext/langs',$inner,true);
    }

    // --------------------------------------------------------------------
    /**
     * Return menu section for admin part
     */

    function get_admin_menu() {
        $settings = $this->CI->api->settings();
        $uri = $this->CI->uri->uri_string();

        $tree = json_decode(
            file_get_contents(dirname(__FILE__) . '/../config/admin.json' ), true);

        $uri = $_SERVER['REQUEST_URI'];
        foreach ($tree as &$menu) {
            $name = $this->CI->lang->line($menu['lang_key']);
            if ($name) {
                $menu['name'] = $name;
            }
            foreach ($menu['child'] as &$submenu) {
                if (strpos($uri, $submenu['module']) !== false) {
                    $menu['visible'] = true;
                }
                $name = $this->CI->lang->line($submenu['lang_key']);
                if ($name) {
                    $submenu['name'] = $name;
                }
            }
        }

        $inner['tree'] = $tree;
        $inner['lang'] = $this->langs;
        $inner['use_clips'] = $settings['use_cliplib'];
        $inner['use_images'] = $settings['use_imagelib'];

        return $this->CI->load->view('main/ext/adminmenu',$inner,true);
    }

    // --------------------------------------------------------------------
    /**
     * Return menu bottom section
     */

    function get_bottom_menu() {
        $query = $this->CI->db->query('select lm.*, lmc.title from lib_menu as lm left join lib_menu_content as lmc on lm.id=lmc.menu_id and lmc.lang='.$this->CI->db->escape($this->langs).' where lm.parent_id=0 and lm.type=1 and lm.active=1 order by lm.ord');
        $data['tree'] = $query->result_array();
        $data['lang'] = $this->langs;

        return $this->CI->load->view('main/ext/bottom',$data,true);
    }

    // --------------------------------------------------------------------
    /**
     * Return menu top section
     */

    function get_top_menu() {
        $query = $this->CI->db->query(
            'select lm.*, lmc.title
      from lib_menu lm
      left join lib_menu_content lmc on lm.id=lmc.menu_id and lmc.lang=?
      where lm.type=0 and lm.active=1
      order by lm.parent_id, lm.ord', $this->langs);
        $rows = $query->result_array();

        foreach($rows as $row) {
            if($row['link'] == 'index.html' && $this->langs == $this->CI->config->item('default_language')){
                $row['link'] = '';
            }
            if($row['parent_id']) $data['tree'][$row['parent_id']]['child'][] = $row;
            else $data['tree'][$row['id']] = $row;
        }

        $data['lang'] = $this->langs;
        return $this->CI->load->view('main/ext/top',$data,true);
    }

    // --------------------------------------------------------------------
    /**
     * Get top news
     */

    function get_top_news() {
        $settings = $this->CI->api->settings();
        $limit = $settings['topnews_count'];
        $this->CI->load->model('news_model');

        $query = $this->CI->db->query('select lp.id, lp.resource, lpc.title, lpc.annotation, DATE_FORMAT(ctime, \'%M\') as news_month, DATE_FORMAT(ctime, \'%e\') as news_day from lib_news as lp left join lib_news_content as lpc on lp.id=lpc.news_id and lpc.lang='.$this->CI->db->escape($this->langs).' where lp.active=1 order by lp.ctime DESC limit '.$limit);
        $rows = $query->result_array();

        if ($rows) {
            foreach ($rows as &$row) {
                $row['thumb'] =  $this->CI->news_model->get_image_path($row);
            }
        }

        $data['news'] = $rows;
        $data['lang'] = $this->langs;
        $data['visual_mode'] = $this->visual_mode;

        return $this->CI->load->view('main/ext/news',$data,true);
    }

    // --------------------------------------------------------------------
    /**
     * Get authentication
     */

    function get_auth() {

        if($this->CI->input->post('enter', true)) {
            $login =  $this->CI->input->post('login', true);
            $password = $this->CI->input->post('password', true);

            if($login && $password) {

                $user = $this->get_user_auth($login, $password);

                if($user){
                    if($user->active == 1){
                        $this->login_user($login, $password);
                    }
                    else{
                        $this->error = 'Your account has not been confirmed';
                    }
                }
                else{
                    $this->error = $this->CI->lang->line('incorrect_login');
                }

                if($this->CI->session->userdata('client_login') && $this->CI->session->userdata('client_uid') && !$this->CI->input->is_ajax_request()){
                    if($redirect_page = $this->CI->session->userdata('after_login_redirect')){
                        $this->CI->session->unset_userdata('after_login_redirect');
                        redirect($redirect_page);
                    }
                    else{
                        redirect($this->langs.'/register/account');
                    }
                }
            }
            else
                $this->error = $this->CI->lang->line('empty_login');
        }

        $data['uri'] = $this->CI->api->get_uri();;
        $data['error'] = $this->error;
        $data['lang'] = $this->langs;


        if($this->CI->session->userdata('client_uid')) {
            $data['auth'] = true;
            $data['client_name'] = $this->CI->session->userdata('client_name');
        }

        if($this->CI->input->is_ajax_request()){
            if($this->error){
                echo json_encode(array('status' => 0, 'error' => $this->error));
            }
            else{
                echo json_encode(array('status' => 1));
            }
            exit();
        }

        return $this->CI->load->view('main/ext/auth',$data,true);
    }

    // --------------------------------------------------------------------
    /**
     * Login user
     */

    function login_user($login, $password) {
        $this->db_master = $this->CI->load->database('master', TRUE);

        $query = $this->CI->db->query(
            'SELECT lc.id, lc.fname, lc.lname, lc.login, lc.corporate_active, lc.corporate_balance,
         lc.corporate_discount, cur.code, cur.rate, cc.id country
      FROM lib_users lc
      LEFT JOIN lib_countries cc ON cc.id = lc.country_id
      LEFT JOIN lib_currencies cur ON cur.code = cc.currency
      WHERE lc.login='.$this->CI->db->escape($login).' AND lc.password='.$this->CI->db->escape($password));
        $rows = $query->result_array();

        $session_data['client_uid'] = $rows[0]['id'];
        $session_data['client_login'] = $rows[0]['login'];
        $session_data['client_name'] = $rows[0]['fname'].' '.$rows[0]['lname'];
        $session_data['client_country'] = $rows[0]['country'];
        $session_data['client_corporate'] = $rows[0]['corporate_active'];
        if ($rows[0]['corporate_active']) {
            $session_data['corporate_balance'] = $rows[0]['corporate_balance'];
            $session_data['corporate_discount'] = $rows[0]['corporate_discount'];
        }
        $session_data['currency']['code'] = $rows[0]['code'];
        $session_data['currency']['rate'] = $rows[0]['rate'];

        $old_session = array ('uid'=>'','login'=>'','name'=>'');
        $this->CI->session->unset_userdata($old_session);
        $this->CI->session->set_userdata($session_data);

        $this->CI->load->model('bin_model','lm');

        if(count($this->lb['items']))
            $this->CI->session->set_userdata('unreg_bin_items', $this->lb['items']);

        $this->CI->lm->set_default_bin($rows[0]['id']);
        $time = date('Y-m-d H:i:s');
        $this->db_master->update('lib_users', array('last_login'=>$time), array('id'=>$rows[0]['id']));
        $this->CI->api->log('log_client_login');
    }

    #------------------------------------------------------------------------------------------------

    function get_user_auth($login, $password) {
        $query = $this->CI->db->query('select lu.* from lib_users as lu, lib_users_groups as lug where lug.is_admin!=1 and lug.id=lu.group_id and lu.login='.$this->CI->db->escape($login).' and lu.password='.$this->CI->db->escape($password));
        if($query->num_rows()) return $query->row();

        return false;
    }

    // --------------------------------------------------------------------
    /**
     * Get pagination navigation
     */

    function page_navigation($all, $page=0, $perpage, $link) {
        $ext = $this->CI->config->item('url_suffix');
        $pages_count = @ceil($all/$perpage);

        if($pages_count > 1) {
            $from = 0; $to = $pages_count;

            if($pages_count > 9) {
                if($page<9) $to = 10;
                else {
                    $from = $page-8;
                    if(($pages_count-$page-1))$to = $page+2;
                }
            }

            for($i=$from; $i<$to; $i++) {
                if ($i != $page) {
                    $url = $link.'/'.$i.$ext;
                    $pages[$i]['link'] = $url;
                }
                $pages[$i]['name'] = $i+1;
            }

            if($page != 0) {
                $p = 0;
                $nav['first'] = $link.'/'.$p.$ext;
            }

            if($page > 0) {
                $p = $page - 1;
                $nav['prev'] = $link.'/'.$p.$ext;
            }

            if($page < $pages_count-1) {
                $p = $page + 1;
                $nav['next'] = $link.'/'.$p.$ext;
            }

            if($page != ($pages_count-1)) {
                $p = $pages_count - 1;
                $nav['last'] = $link.'/'.$p.$ext;
            }

            $temp['pages_count'] = $pages_count;
            $temp['cur_pages'] = $page+1;
            $temp['to'] = $to;
            $temp['from'] = $from;
            $temp['pages'] = $pages;
            $temp['nav'] = $nav;
            $temp['all'] = $all;

            return $this->CI->load->view('main/ext/pages', $temp, true);
        }
        return '';
    }

    // --------------------------------------------------------------------
    /**
     * Get tagcloud
     */

    function get_tagcloud() {
        $settings = $this->CI->api->settings();
        $tagcloud_limit = $settings['tagcloud'];
        $sizes = array(10,11,12,13,14);

        $query = $this->CI->db->query('select * from lib_search where type=1 and lang='.$this->CI->db->escape($this->langs).' limit '.$tagcloud_limit);
        $temp = $query->result_array();
        $no = '';
        foreach($temp as $val) {
            $no .= ' and phrase !="'.$val['phrase'].'"';

            $data['phrase'] = $val['phrase'];
            $data['size'] = $sizes[$val['weight']];
            $data['link'] = $this->langs.'/search/words/'.urlencode($val['phrase']);
            $list[] = $data;
        }

        $num = $tagcloud_limit - count($list);

        $query = $this->CI->db->query('select * from lib_search where type=0 '.$no.' and lang='.$this->CI->db->escape($this->langs).' order by times desc limit '.$num);
        $temp = $query->result_array();
        $max = $temp[0]['times'];

        foreach($temp as $val) {
            $coef = $val['times']/$max;

            if ($coef>0.8) $weight = 4;
            elseif ($coef<0.8 && $coef>=0.6) $weight = 3;
            elseif ($coef<0.6 && $coef>=0.4) $weight = 2;
            elseif ($coef<0.4 && $coef>=0.2) $weight = 1;
            else $weight = 0;

            $data['phrase'] = $val['phrase'];
            $data['size'] = $sizes[$weight];
            $data['link'] = $this->langs.'/search/words/'.urlencode($val['phrase']);
            $list[] = $data;
        }

        //shuffle($list);
        $data['tags'] = $list;
        $data['lang'] = $this->langs;

        return $this->CI->load->view('main/ext/tagcloud',$data,true);
    }

    function get_cats_menu() {

        $this->CI->load->model('cats_model');

        $filter_active = ' AND c.active = 1 ';
        $query = $this->CI->db->query(
            'SELECT c.*, cc.title, cc.meta_desc description
			FROM lib_cats c
			LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=?
			WHERE c.parent_id = 0 AND type = 0 ' . $filter_active
                . ' ORDER BY c.ord, c.id LIMIT 3', array($this->langs));
        $rows = $query->result_array();
        $data['tree'] = array();
        if ($rows) {
            foreach ($rows as $row) {
                $data['tree'][$row['id']] = $row;
                $data['tree'][$row['id']]['title'] = ($row['title']) ? $row['title'] : '-';
                $data['tree'][$row['id']]['thumb'] = $this->CI->cats_model->get_image_path($row);
                $data['tree'][$row['id']]['uri'] = 'search/categories/' . $this->CI->cats_model->make_uri($row['title'], $row['id']);
            }
        }

        return $this->CI->load->view('main/ext/cats', $data, true);
    }

    function get_highlights() {

        $this->CI->load->model('highlights_model');
        $query = $this->CI->db->query(
            'SELECT c.*, cc.title
			FROM lib_highlights c
			LEFT JOIN lib_highlights_content cc ON c.id=cc.highlight_id AND cc.lang=?
			WHERE c.active = 1 ORDER BY c.ord, c.id LIMIT 3', array($this->langs));
        $rows = $query->result_array();

        if ($rows) {
            foreach ($rows as &$row) {
                $row['resource_file'] =  $this->CI->highlights_model->get_resource_path($row);
                if( $this->CI->api->check_ext($row['resource'], 'video')){
                    $row['resource_video'] = true;
                }
            }
        }

        $data['highlights'] = $rows;

        return $this->CI->load->view('main/ext/highlights', $data, true);
    }

    function get_features(){
        $this->CI->load->model('features_model');
        return $this->CI->load->view('main/ext/featured.php',
            array('lang' => $this->langs, 'featured'=>$this->CI->features_model->get_list($this->langs, 3, true)), true);
    }
}
?>