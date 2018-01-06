<?php

/**
 * Class Uploads_model
 * @property Users_model $users_model
 * @property Clips_model $clips_model
 */
class Uploads_model extends CI_Model {

    var $base_dir;
    var $providers_path;
    var $clips_dir;
    var $video_formats = array('mov', 'mp4', 'm4v', 'mpeg', 'mpg', 'mxf', 'wmv', 'avi', 'r3d');
    var $r3d_uploads_dir_name = 'r3d_uploads';

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->base_dir = dirname(dirname(dirname(__FILE__)));
        //$this->providers_path = $this->base_dir . '/data/upload/providers';
        $this->providers_path = '/Volumes/Data/providers';
        $this->clips_dir = $this->base_dir . '/data/upload/resources/clip';
    }

    function get_uploads_list($provider_login = ''){
        $this->load->model('users_model');
        $dirs_tree = $this->dir_to_array($this->providers_path, 1, $provider_login);
        foreach($dirs_tree as &$provider_dir){
            if($provider_dir['is_dir']){
                $provider_id = $this->users_model->get_provider_by_login($provider_dir['name']);
                if($provider_id)
                    $provider_dir['provider'] = $this->users_model->get_user($provider_id);

                if($provider_dir['items']){
                    foreach($provider_dir['items'] as &$item){
                        $item = $this->parse_r3d_dirs($item);
                    }
                }


            }
        }
        return $dirs_tree;
    }

    function get_r3d_uploads_list($provider_dir){
        $this->load->model('clips_model');
        $contents = array();
        $r3d_dir = $provider_dir . $this->r3d_uploads_dir_name;
        if(is_dir($r3d_dir)){
            //process each clip r3d uploads
            foreach (scandir($r3d_dir) as $clip_dir) {
                //skip trash files, folders and 'up' folder
                if ($clip_dir == '.' || $clip_dir == '..' || !is_numeric($clip_dir)) continue;

                //folder name must match clip_id
                $clip_id = (int)$clip_dir;
                if($this->clips_model->is_r3d($clip_id)){
                    $contents[] = array(
                        'id' => $clip_id,
                        'path' => $r3d_dir . '/' . $clip_id
                    );
                }
            }
        }
        return $contents;
    }

    function submit_uploads($id, $provider_login = ''){
        $uploads = $this->get_uploads_list($provider_login);

        $upload = $this->find_upload($uploads, $id);
        if($upload){
            $this->load->model('submissions_model');
            $this->load->model('users_model');
            $user_id = 0;
            $user_prefix = '';
            if($provider_login){
                $provider_id = $this->users_model->get_provider_by_login($provider_login);
                if($provider_id){
                    $user_id = $provider_id;
                    $provider = $this->users_model->get_user($user_id);
                    if($provider['prefix']) {
                        $user_prefix = $provider['prefix'];
                    }
                    else {
                        $user_prefix = $this->users_model->create_provider_prefix($provider['id'], $provider['fname'], $provider['lname']);
                        $this->db_master->update('lib_users', array('prefix' => $user_prefix), array('id' => $provider['id']));
                    }
                }
            }
            $submission_id = $this->submissions_model->create_submission('', $user_id, $user_prefix);
            $submission = $this->submissions_model->get_submission($submission_id);
            $store = array();
            require(__DIR__ . '/../config/store.php');
            $dir = $store['original']['path'];
            $volume = $this->get_available_volume($upload['path']);
            if ($volume) {
                $dest = $store['original']['path'] . '/' . $volume . '/' . $submission['code'];
    //            if(!file_exists($dest)){
    //                mkdir($dest);
    //                $this->submissions_model->set_submission_location($submission_id, $volume);
    //            }
                $this->submissions_model->set_submission_location($submission_id, $volume);
                $this->submit_upload($upload, $dest, $submission, $user_id);
            }

        }
    }

    function find_upload($items, $id){
        $find = false;
        foreach($items as $item){
            if($item['id'] == $id)
                $find = $item;
            elseif($item['items'] && !$find)
                $find = $this->find_upload($item['items'], $id);
        }
        return $find;
    }

    function submit_upload($upload, $dest, $submission, $user_id){
        if($upload['r3d_dir']){

            if($upload['items']){
                if(!file_exists($dest . '_R3D'))
                    mkdir($dest . '_R3D');
                if(!file_exists($dest . '_R3D/' . $upload['name']))
                    mkdir($dest . '_R3D/' . $upload['name']);

                foreach($upload['items'] as $item){
                    $this->submit_r3d_upload($item, $dest . '_R3D/' . $upload['name']);
                }
            }

            $dest_file = $dest . '_R3D' . '/' . $upload['name'] . '/' . $upload['r3d_dir'];
            $this->load->model('clips_model');
            $this->clips_model->create_clip($dest_file, $submission ? $submission['code'] : '', $user_id);

            rmdir($upload['path']);
        }
        elseif($upload['is_dir']){
            if($upload['items']){
                if(!file_exists($dest))
                    mkdir($dest);
                foreach($upload['items'] as $item){
                    $this->submit_upload($item, $dest, $submission, $user_id);
                }
            }
            if(!$upload['provider'])
                rmdir($upload['path']);
        }
        else{
            $source = $upload['path'];
            if(in_array(strtolower($upload['ext']), $this->video_formats)){
                if(!file_exists($dest))
                    mkdir($dest);
                $dest_file = $dest . '/' . $upload['name'];
                copy($source, $dest_file);
                $this->load->model('clips_model');
                $this->clips_model->create_clip($dest_file, $submission ? $submission['code'] : '', $user_id);
            }
            unlink($source);

        }
    }

    function submit_r3d_upload($upload, $dest){
        if($upload['is_dir']){
            if($upload['items']){
                foreach($upload['items'] as $item){
                    $this->submit_r3d_upload($item, $dest);
                }
            }
            rmdir($upload['path']);
        }
        else{
            $this->load->model('clips_model');
            $source = $upload['path'];
            if(!file_exists($dest)){
                mkdir($dest);
            }
            $dest .= '/' . $upload['name'];
            copy($source, $dest);
            unlink($source);
        }
    }

    function delete_uploads($ids, $provider_login = ''){
        foreach($ids as $id){
            $uploads = $this->get_uploads_list($provider_login);
            $upload = $this->find_upload($uploads, $id);
            if($upload){
                $this->delete_upload($upload);
            }
        }
    }

    function delete_upload($upload){
        $source = $upload['path'];
        if(is_file($source)){
            if($upload['is_dir']){
                if(!$upload['provider'])
                    rmdir($source);
            }
            else{
                unlink($source);
            }
        }
    }

    function dir_to_array($dir, $level = 1, $provider_login = '') {
        $contents = array();
        foreach (scandir($dir) as $node) {
            if($level == 1 && $provider_login && $node != $provider_login){
                continue;
            }
            //do not include r3d_uploads!
            if ($node == '.' || $node == '..' || $node == $this->$r3d_uploads_dir_name) continue;
            if (is_dir($dir . '/' . $node)) {
                $contents[] = array(
                    'id' => str_replace('.', '_', $node) . $level,
                    'path' => $dir . '/' . $node,
                    'name' => $node,
                    'is_dir' => true,
                    'items' => $this->dir_to_array($dir . '/' . $node, $level + 1)
                );
            } else {
                $contents[] = array(
                    'id' => str_replace('.', '_', $node) . $level,
                    'path' => $dir . '/' . $node,
                    'name' => $node,
                    'ext' => pathinfo($node, PATHINFO_EXTENSION)
                );
            }
        }
        return $contents;
    }

    function parse_r3d_dirs($item){
        if($item['is_dir'] && $item['items']){
            foreach($item['items'] as &$sub_item){
                if(!$sub_item['is_dir'] && strtolower($sub_item['ext']) == 'r3d'){
                    $item['r3d_dir'] = $sub_item['name'];
                }
                if($sub_item['is_dir'] && $sub_item['items'])
                    $sub_item = $this->parse_r3d_dirs($sub_item);
            }
        }
        return $item;
    }

    function get_available_volume($submission_dir){

        $this->load->model('volumes_model');
        $volumes = $this->volumes_model->get_volumes_list();
        $volumes_by_name = array();
        foreach($volumes as $volume) {
            $volumes_by_name[$volume['name']] = $volume;
        }

        //return 'FSM10';
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $submission_size = $this->foldersize($submission_dir);
        $dir = $store['original']['path'];
        $nodes = scandir($dir);
        foreach($nodes as $node){
            if ($node == '.' || $node == '..') continue;

            if (isset($volumes_by_name[$node]) && $volumes_by_name[$node]['is_full']) continue;

            if (is_dir($dir . '/' . $node)) {
                $free = disk_free_space($dir . '/' . $node);
                $total = disk_total_space($dir . '/' . $node);
                $left = $total * 0.1;
                if($free > $left && $free > $submission_size){
                    return $node;
                }
            }
        }
        return false;
    }



    function foldersize($path) {
        $total_size = 0;
        if(!is_dir($path)) {
            return filesize($path);
        }
        $files = scandir($path);
        $cleanPath = rtrim($path, '/'). '/';

        foreach($files as $t) {
            if ($t<>"." && $t<>"..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->foldersize($currentFile);
                    $total_size += $size;
                }
                else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }
        }

        return $total_size;
    }
}