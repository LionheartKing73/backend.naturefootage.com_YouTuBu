<?php

class Settings_ratings extends CI_Controller {

    var $error;
    var $langs;

    function Settings_ratings() {
        parent::__construct();

        $this->load->model('settings_model', 'sm');
        $this->load->model('presets_model');
        $this->langs = $this->uri->segment(1);
    }

    #------------------------------------------------------------------------------------------------

    function index() {
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function view() {
        $sets = $this->input->post('sets');

        if ($this->input->post('save') && $this->check_details($sets)) {
            $this->sm->save_settings($sets);
            $this->message = 'Saved.';
            $this->api->log('log_settings_save');
        }

        $rows = $this->sm->get_settings();

        foreach ($rows as $k => $row) {
            $rows[$k]['lang'] = $this->lang->line($row['lang_key']);
            $rows[$k]['value'] = ($this->error) ? $sets[$row['name']] : $row['value'];
            if ($row['name'] == 'q1_preview_preset')
                $data['q1_preview_preset'] = $row['value'];
            if ($row['name'] == 'q2_preview_preset')
                $data['q2_preview_preset'] = $row['value'];
            if ($row['name'] == 'still_browse_preset')
                $data['still_browse_preset'] = $row['value'];
            if ($row['name'] == 'still_search_preset')
                $data['still_search_preset'] = $row['value'];
        }

        $data['sets'] = $rows;
        $data['lang'] = $this->langs;
        $data['presets'] = $this->presets_model->get_presets_list();

        $this->path = 'Manage system / Clip Ratings settings';

        $content = $this->load->view('settings_ratings/view', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function check_details($sets) {
        foreach ($sets as $set) {
            if (empty($set)) {
                $this->error = $this->lang->line('empty_fields');
                return false;
            }
        }
        return true;
    }

    #------------------------------------------------------------------------------------------------

    function out($content = null, $pagination = null) {
        $this->builder->output(array('content' => $content, 'path' => $this->path,
            'pagination' => $pagination, 'error' => $this->error, 'message' => $this->message), 1);
    }

}
