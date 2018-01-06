<?php

/**
 * Class Uploadstools
 * @property Volumes_model $volumes_model
 * @property Uploads_model $uploads_model
 * TODO: restrict regular r3d uploads
 */
class Uploadstools extends CI_Controller {

    var $ignore_names = array('.cache');

    function __construct() {
        parent::__construct();
        $this->aspdb = $this->load->database('aspera_console', true);
        $this->db_master = $this->load->database('master', TRUE);
    }

    public function submit(){
        if(!$this->input->is_cli_request()){
            exit();
        }
        if($this->is_previous_submit()){
            echo 'Previous', PHP_EOL;
            exit();
        }

        $this->load->model('volumes_model');
        $this->volumes_model->sync_volumes();

        $this->load->model('uploads_model');
        $uploads = $this->uploads_model->get_uploads_list();
        if($uploads){
            foreach($uploads as $upload){
                if($upload['provider'] && $upload['items']){
                    $uncompleted_sessions = $this->get_uncompleted_sessions($upload['provider']['login']);
                    if($uncompleted_sessions)
                        continue;
                    foreach($upload['items'] as $upload_item){
                        if(!in_array($upload_item['name'], $this->ignore_names)){
                            if(!isset($submit_log))
                                $submit_log = $this->log_submit();
                            $this->uploads_model->submit_uploads($upload_item['id'], $upload['provider']['login']);
                        }
                    }
                }
            }
        }
        if(isset($submit_log))
            $this->log_submit_update($submit_log);

    }

    private function get_uncompleted_sessions($user){
        $this->aspdb->select('id');
        $this->aspdb->where('status', 'running');
        $this->aspdb->where('user', $user);
        $query = $this->aspdb->get('fasp_sessions');
        $res = $query->result_array();
        return $res;
    }

    private function log_submit(){
        $this->db_master->insert('lib_uploads_submit', array('status' => 0));
        return $this->db_master->insert_id();
    }

    private function log_submit_update($id){
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_uploads_submit', array('status' => 1));
    }

    private function is_previous_submit() {
        $this->db->where('status', 0);
        $this->db->from('lib_uploads_submit');
        return $this->db->count_all_results();
    }

    //r3d uploads functions
    public function submit_r3d(){
        if(!$this->input->is_cli_request()){
            exit();
        }
        if($this->is_previous_submit_r3d()){
            echo 'Previous', PHP_EOL;
            exit();
        }

        $this->load->model('volumes_model');
        $this->volumes_model->sync_volumes();

        $this->load->model('uploads_model');
        $this->load->model('users_model');
        $providers = $this->users_model->get_content_providers();
        foreach($providers as $provider){
            $r3d_uploads = $this->uploads_model->get_r3d_uploads_list($provider['dir']);
            foreach($r3d_uploads as $upload){

            }
        }

        $this->load->model('uploads_model');
        $uploads = $this->uploads_model->get_r3d_uploads_list();
        if($uploads){
            foreach($uploads as $upload){
                if($upload['provider'] && $upload['items']){
                    $uncompleted_sessions = $this->get_uncompleted_sessions($upload['provider']['login']);
                    if($uncompleted_sessions)
                        continue;
                    foreach($upload['items'] as $upload_item){
                        if(!in_array($upload_item['name'], $this->ignore_names)){
                            if(!isset($submit_log))
                                $submit_log = $this->log_submit();
                            $this->uploads_model->submit_uploads($upload_item['id'], $upload['provider']['login']);
                        }
                    }
                }
            }
        }
        if(isset($submit_log))
            $this->log_submit_update($submit_log);

    }

    private function is_previous_submit_r3d() {
        $this->db->where('status', 0);
        $this->db->from('lib_uploads_submit_r3d');
        return $this->db->count_all_results();
    }

    private function log_submit_r3d(){
        $this->db_master->insert('lib_uploads_submit', array('status' => 0));
        return $this->db_master->insert_id();
    }

    private function log_submit_update_r3d($id){
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_uploads_submit', array('status' => 1));
    }
}
?>