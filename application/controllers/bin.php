<?php
class Bin extends AppController {


    var $method;
    var $client;
    var $error;

    function Bin() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

        $this->load->model('bin_model');
        $this->client = $this->session->userdata('client_uid');
        $this->langs = $this->uri->segment(1);
        $this->method = $this->uri->segment(3);
        $this->id = $this->uri->segment(4);
        $this->extra = $this->uri->segment(5);

    }

    #------------------------------------------------------------------------------------------------

    function index() {
        switch($this->method) {
            case 'add': $this->add_item();
            break;
            case 'remove': $this->remove_items();
            break;
            case 'check': $this->check_exist();
            break;
            case 'tocart': $this->cart_items();
            break;
            case 'move': $this->move_items();
            break;
            case 'edit': $this->edit_bin();
            break;
            case 'delete': $this->delete_bin();
            break;
            case 'email': $this->email_bin();
            break;
            case 'link': $this->link_bin();
            break;
            case 'unreg': $this->unreg_items();
            break;
        }

        if($this->method != 'edit' && $this->method != 'email') {
            $data = $this->content();
            $this->out($data);
        }
    }

    #------------------------------------------------------------------------------------------------

    function content() {
        $bin = $this->session->userdata('bin');
        $bin_count = count($bin['items']);
        $current_bin = $bin['id'];

        $temp['results'] = $this->bin_model->get_content($this->langs);
        $temp['checks'] = 1;
        $temp['lang'] = $this->langs;
        $list['items'] = $this->load->view('main/ext/results', $temp, true);

        $list['uri'] = $this->api->prepare_uri();
        $list['continue'] = $this->session->userdata('search_page');
        $list['bin_count'] = $bin_count;
        $list['current_bin'] = $current_bin;
        $list['client'] = $this->client;
        $list['bins'] = $this->bin_model->get_client_bins($this->client);
        $list['lang'] = $this->langs;

        //$data['add_css'] = '/data/css/bin.css';
        $data['add_css'] = array('/data/css/bin.css', '/data/css/search.css');
        //$data['add_js'] = '/data/js/swfobject.js';
        $data['add_js'] = array('/data/js/search.js');
        $data['body'] = $this->load->view('bin/content', $list, true);
        $data['title'] = $this->lang->line('bin');

        return $data;
    }

    #------------------------------------------------------------------------------------------------

    function unreg_items() {

        if ($this->id == 1) {

            $default_bin = $this->bin_model->get_client_default_bin($this->client);
            $default_bin = $this->bin_model->get_bin($default_bin['id']);
            $unreg_items = $this->session->userdata('unreg_bin_items');

            foreach ($unreg_items as $item) {
                $exist = false;
                foreach ($default_bin['items'] as $exist_item) {
                    if ($exist_item['id'] == $item['id'])
                        $exist = true;
                }

                if (!$exist)
                    $default_bin['items'][] = $item;
            }

            $this->bin_model->lb = $default_bin;
            $this->bin_model->save_items($default_bin['id']);
            $this->bin_model->set_current_bin($default_bin['id']);
        } elseif ($this->id == 2) {

            $bin_name = urldecode($this->extra);

            $new_bin['name'] = addslashes($bin_name);
            $this->bin_model->lb = $new_bin;
            $id = $this->bin_model->save_bin();

            $new_bin['items'] = $this->session->userdata('unreg_bin_items');
            $this->bin_model->lb = $new_bin;
            $this->bin_model->save_items($id);

            $this->bin_model->set_current_bin($id);
        }

        $this->session->unset_userdata('unreg_bin_items');

        redirect('/bin');
    }

    #------------------------------------------------------------------------------------------------

    function add_item() {
        /*$type = intval($this->uri->segment(4));
        $id = intval($this->uri->segment(5));

        if($type && $id) {
            $this->bin_model->add_item($type, $id);
            redirect('/cart/info');
        }*/

        $type = intval($this->uri->segment(4));
        $id = intval($this->uri->segment(5));
        $async = intval($this->uri->segment(6));

        if($type && $id) {
            $item_id = $this->bin_model->add_item($type, $id);
            if($async) {
                $this->session->set_flashdata('no_messages', true);
                echo $item_id;
                exit;
            }
        }
        else{
            $redirect = $this->input->post('redirect');
            if (!$redirect) {
                $redirect = $this->session->userdata('search_page');
            }
            redirect($redirect);
        }
    }

    #------------------------------------------------------------------------------------------------

    function link_bin() {

        $id = intval($this->uri->segment(4));
        $checksum = $this->uri->segment(5);
        $replace = $this->uri->segment(6);

        $bin = $this->bin_model->exec_bin($id, $checksum);

        if($bin) {

            if($this->client) {

                $client_bins = $this->bin_model->get_client_bins($this->client);

                $new_bin_title = $this->get_new_bin_name($bin['title'], $client_bins);

                $data['title'] = $new_bin_title;
                $data['client_id'] = $this->client;
                $data['description'] = $bin['description'];
                $data['is_default'] = 0;

                $this->db_master->insert('lib_lb', $data);
                $id = $this->db_master->insert_id();

                $this->bin_model->lb['items'] = $bin['items'];
                $this->bin_model->save_items($id);
                $this->bin_model->set_current_bin($id);

                redirect('/bin');


            } else {

                $this->bin_model->lb['items'] = array();
                foreach($bin['items'] as $item)
                    $this->bin_model->add_item($item['type'], $item['id']);

            }

        }


    }

    #---------------------------------------------------------------------------------#

    function get_new_bin_name($title, $bins_array, $i=0) {

        if($i>0) {
            $new_title .= $title . ' ('.$i.')';
        } else {
            $new_title = $title;
        }

        if(array_search($new_title, $bins_array)) {

            return $this->get_new_bin_name($title, $bins_array, ++$i);

        } else {

            return $new_title;

        }

    }

    #------------------------------------------------------------------------------------------------

    function check_exist() {

        $type = intval($this->uri->segment(4));
        $id = intval($this->uri->segment(5));

        // ajax
        if ($this->bin_model->check_exist($type, $id))
            echo 1;
        else
            echo 0;
        exit;
    }

    #------------------------------------------------------------------------------------------------

    function remove_items() {
        $ids = $this->input->post('id', true);
        $this->bin_model->remove_items($ids);
    }

    #------------------------------------------------------------------------------------------------

    function move_items() {
        $ids = $this->input->post('id', true);
        $to = $this->input->post('to', true);

        $this->bin_model->move_items($to, $ids);
    }

    #------------------------------------------------------------------------------------------------

    function cart_items() {
        $ids = $this->input->post('id', true);
        $this->bin_model->cart_items($ids);
    }

    #------------------------------------------------------------------------------------------------

    function delete_bin() {
        $this->bin_model->delete_bin();
    }

    #------------------------------------------------------------------------------------------------

    function edit_bin() {
        if(isset($_POST['save']) && $this->check_details()) {
            $this->bin_model->save_bin($this->id);
            redirect('/bin');
        }

        $temp = $this->bin_model->get_bin($this->id);
        $temp['error'] = $this->error;

        $temp['client'] = $this->client;
        $temp['lang'] = $this->langs;
        $data['body'] = $this->load->view('bin/edit', $temp, true);
        $data['add_css'] = '/data/css/bin.css';
        $data['title'] = $this->lang->line('bin_edit');

        $this->out($data);
    }

    #------------------------------------------------------------------------------------------------

    function email_bin() {

        $temp['fromname'] = $this->input->post('fromname', true);
        $temp['fromemail'] = $this->input->post('fromemail', true);
        $temp['email'] = $this->input->post('email', true);
        $temp['toname'] = $this->input->post('toname', true);
        $temp['subject'] = $this->input->post('fromname', true) . ' sent you clips for review.';
        $temp['message'] = $this->input->post('message', true);
        $temp['lang'] = $this->langs;

        if(isset($_REQUEST['send']) && $this->check_email_details()) {

            $lib_id = $this->bin_model->create_bin_copy($this->id);

            $temp['link'] = $this->bin_model->get_bin_link($lib_id, $this->langs);

            $temp['body'] = $this->load->view('main/mail/bin', $temp, true);

            $this->bin_model->email_bin($temp);

            $temp['success'] = 'Your clip bin has been sent.';

            $this->bin_model->clear_bin_copy();

        }

        $temp['error'] = $this->error;

        $temp['client'] = $this->client;
        $data['body'] = $this->load->view('bin/email', $temp, true);

        $data['title'] = $this->lang->line('bin_email');
        $data['add_css'] = '/data/css/bin.css';
        $this->out($data);
    }

    #------------------------------------------------------------------------------------------------

    function check_details() {
        if(!$this->input->post('title')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    #------------------------------------------------------------------------------------------------

    function check_email_details() {
        if(!$this->input->post('fromname') || !$this->input->post('email') || !$this->input->post('toname') || !$this->input->post('message')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }

        if(!$this->api->check_email($this->input->post('email')) || !$this->api->check_email($this->input->post('fromemail'))) {
            $this->error = $this->lang->line('incorrect_email');
            return false;
        }

        return true;
    }

}
