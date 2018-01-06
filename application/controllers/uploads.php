<?php

class Uploads extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Uploads() {
        parent::__construct();
        $this->load->model('uploads_model');
        $this->load->model('groups_model');
        $this->load->model('users_model');
        $this->id = $this->uri->segment(4);
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Clips section / Uploads';
        $data['lang'] = $this->langs;
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $provider_login = $this->session->userdata('login');
        }
        $uploads = $this->uploads_model->get_uploads_list(isset($provider_login) ? $provider_login : '');
        $data['uploads_tree'] = $this->display_tree($uploads);
        $content = $this->load->view('uploads/view', $data, true);
        $this->out($content);
    }

    function edit() {
        show_404();
    }

    function submit(){
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $provider_login = $this->session->userdata('login');
        }

        if ($this->id)
            $this->uploads_model->submit_uploads($this->id, isset($provider_login) ? $provider_login : '');

        redirect($this->langs . '/uploads/view');
    }

    function visible() {
        $this->uploads_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/uploads/view');
    }

    function delete() {

        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $provider_login = $this->session->userdata('login');
        }

        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->uploads_model->delete_uploads($ids, isset($provider_login) ? $provider_login : '');
        redirect($this->langs . '/uploads/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('title')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }

    function set_group() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

    function display_tree($items, $level = 0){
        $content = '';
        foreach($items as $item){
            $content .= '<tr>
                <td>
                    <input type="checkbox" name="id[]" value="' . $item['id'] . '">
                </td>
                <td' . ($level ? ' style="padding-left:' . ($level * 30) . 'px;"' : '') . '>' . ($item['r3d_dir'] ? $item['r3d_dir'] : $item['name']) . '</td>
                <td>' . $item['provider']['fname'] . ' ' . $item['provider']['lname'] . '</td>
                <td>' .
                    get_actions(array(
                        array('display' => $this->permissions['uploads-submit'], 'url' => 'en/uploads/submit/'.$item['id'], 'name' => 'Submit'),
                        array('display' => ($this->permissions['uploads-delete'] && $level), 'url' => 'en/uploads/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ), false) . '
                </td>
            </tr>';
            if($item['is_dir'] && $item['items'] && !$item['r3d_dir']){
                $content .= $this->display_tree($item['items'], $level+1);
            }
        }
        return $content;
    }
}