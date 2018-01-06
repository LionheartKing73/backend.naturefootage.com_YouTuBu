<?php

require_once(__DIR__ . '/../../scripts/Notifications.php');

/**
 * Class Uploads_model
 * @property Users_model $users_model
 * @property Clips_model $clips_model
 * @property Submissions_model $submissions_model
 */
class Uploads_model extends CI_Model
{

    var $base_dir;
    var $providers_path;
    var $clips_dir;
    var $video_formats = array('mov', 'mp4', 'm4v', 'mpeg', 'mpg', 'mxf', 'wmv', 'avi', 'r3d', 'mts', 'm2t');
    var $r3d_uploads_dir_name = 'r3d_uploads';
    var $ignore_names = array('.cache', 'Library', '.DS_Store', '.subversion', 'Desktop', 'Documents', 'Downloads');

    function __construct()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->base_dir = dirname(dirname(dirname(__FILE__)));
        //$this->providers_path = $this->base_dir . '/data/upload/providers';
        //$this->providers_path = '/Volumes/Data/providers';
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->providers_path = $store['uploads']['path'];
        $this->clips_dir = $this->base_dir . '/data/upload/resources/clip';
    }

    function get_uploads_list($provider_login = '')
    {
        $this->load->model('users_model');
        $dirs_tree = $this->dir_to_array($this->providers_path, 1, $provider_login);
        foreach ($dirs_tree as &$provider_dir) {
            if ($provider_dir['is_dir']) {
                $provider_id = $this->users_model->get_provider_by_login($provider_dir['name']);
                if ($provider_id)
                    $provider_dir['provider'] = $this->users_model->get_user($provider_id);

                if ($provider_dir['items']) {
                    foreach ($provider_dir['items'] as &$item) {
                        $item = $this->parse_r3d_dirs($item);
                    }
                }


            }
        }
        return $dirs_tree;
    }

    function get_r3d_uploads_list($provider_dir)
    {
        $this->load->model('clips_model');
        $contents = array();
        $r3d_dir = $provider_dir . $this->r3d_uploads_dir_name;
        if (is_dir($r3d_dir)) {
            //process each clip r3d uploads
            foreach (scandir($r3d_dir) as $clip_dir) {
                //skip trash files, folders and 'up' folder
                if ($clip_dir == '.' || $clip_dir == '..' || !is_numeric($clip_dir)) continue;

                //folder name must match clip_id
                $clip_id = (int)$clip_dir;
                if ($this->clips_model->is_r3d($clip_id)) {
                    $contents[] = array(
                        'id' => $clip_id,
                        'path' => $r3d_dir . '/' . $clip_id
                    );
                }
            }
        }
        return $contents;
    }

    function submit_uploads($id, $provider_login = '')
    {
        $uploads = $this->get_uploads_list($provider_login);

        $upload = $this->find_upload($uploads, $id);
        if ($upload) {
            $this->load->model('submissions_model');
            $this->load->model('users_model');
            $user_id = 0;
            $user_prefix = '';
            //для чего этот блок?
            if ($provider_login) {
                $provider_id = $this->users_model->get_provider_by_login($provider_login);
                if ($provider_id) {
                    $user_id = $provider_id;
                    $provider = $this->users_model->get_user($user_id);
                    if ($provider['prefix']) {
                        $user_prefix = $provider['prefix'];
                    } else {
                        $user_prefix = $this->users_model->create_provider_prefix($provider['id'], $provider['fname'], $provider['lname']);
                        $this->db_master->update('lib_users', array('prefix' => $user_prefix), array('id' => $provider['id']));
                    }
                }
            }
            $log = $this->get_upload_log($upload, $user_id);
            if ($log && $log['submission_id']) {
                $submission_id = $log['submission_id'];
            } else {
                $submission_id = $this->submissions_model->create_submission('', $user_id, $user_prefix);
                $log = array();
                $log['id'] = $this->log_upload($upload, $user_id, $submission_id);
            }
            $submission = $this->submissions_model->get_submission($submission_id);

            if ($submission) {
                $store = array();
                require(__DIR__ . '/../config/store.php');
                $dir = $store['original']['path'];

                if ($submission['location']) {
                    $volume = $submission['location'];
                } else {
                    $volume = $this->get_available_volume($upload['path']);
                }

                if ($volume) {
                    $this->load->model('volumes_model');
                    $mounted = $this->volumes_model->is_mounted($store['original']['path']  . '/' . $volume);

                    if ($mounted) {
                        $dest = $store['original']['path'] . '/' . $volume . '/' . $submission['code'];
                        $submissions_backup_dest = $store['submissions_backup']['path'] . '/' . $submission['code'];
                        $this->submissions_model->set_submission_location($submission_id, $volume);
                        if ($this->backup_upload($upload, $submissions_backup_dest)) {
                            $this->submit_upload($upload, $dest, $submission, $user_id);
                            $this->submissions_model->set_sync($submission_id, 0);
                        }

                        if ($log) {
                            $this->delete_upload_log($log['id']);
                        }
                    }
                }
            }
        }
    }

    function find_upload($items, $id)
    {
        $find = false;
        foreach ($items as $item) {
            if ($item['id'] == $id)
                $find = $item;
            elseif ($item['items'] && !$find)
                $find = $this->find_upload($item['items'], $id);
        }
        return $find;
    }

    function submit_upload($upload, $dest, $submission, $user_id)
    {
        $pid = posix_getppid();
        if ($upload['r3d_dir']) {

            if ($upload['items']) {
                if (!file_exists($dest . '_R3D'))
                    mkdir($dest . '_R3D');
                if (!file_exists($dest . '_R3D/' . $upload['name']))
                    mkdir($dest . '_R3D/' . $upload['name']);

                foreach ($upload['items'] as $item) {
                    $this->submit_r3d_upload($item, $dest . '_R3D/' . $upload['name']);
                }
            }

            $dest_file = $dest . '_R3D' . '/' . $upload['name'] . '/' . $upload['r3d_dir'];
            $this->load->model('clips_model');
            $clip_id = $this->clips_model->create_clip($dest_file, $submission ? $submission['code'] : '', $user_id);
            echo $pid . ' ' . date('Y-m-d H:i:s') . ' CREATED CLIP ' . $clip_id, PHP_EOL;
            Solr::addClipToIndex($clip_id);
            echo $pid . ' ' . date('Y-m-d H:i:s') . ' TO INDEX CLIP ' . $clip_id, PHP_EOL;
            $this->rrmdir($upload['path']);
        } elseif ($upload['is_dir']) {
            if ($upload['items']) {
                if (!file_exists($dest))
                    mkdir($dest);
                foreach ($upload['items'] as $item) {
                    $this->submit_upload($item, $dest, $submission, $user_id);
                }
            }
            if (!$upload['provider'])
                $this->rrmdir($upload['path']);
        } else {
            $source = $upload['path'];
            if (in_array(strtolower($upload['ext']), $this->video_formats) && $upload['name'][0] != '.') {
                echo $pid . ' ' . date('Y-m-d H:i:s') . ' SUBMIT: ' . $upload['path'] . ' => ' . $dest, PHP_EOL;
                if (!file_exists($dest))
                    mkdir($dest);
                $dest_file = $dest . '/' . $upload['name'];
                echo $pid . ' ' . date('Y-m-d H:i:s') . ' COPY: ' . $source . ' => ' . $dest_file, PHP_EOL;
                if (copy($source, $dest_file) && filesize($source) == filesize($dest_file)) {
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' COPY SUCCESS', PHP_EOL;
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE: ' . $source, PHP_EOL;
                    if (unlink($source)) {
                        echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE SUCCESS' . $source, PHP_EOL;
                    } else {
                        echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE FAILURE' . $source, PHP_EOL;
                    }
                    $this->load->model('clips_model');
                    $clip_id = $this->clips_model->create_clip($dest_file, $submission ? $submission['code'] : '', $user_id);
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' CREATED CLIP ' . $clip_id, PHP_EOL;
                    Solr::addClipToIndex($clip_id);
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' TO INDEX CLIP ' . $clip_id, PHP_EOL;
                } else {
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' COPY FAILURE', PHP_EOL;
                }
            } else {
                unlink($source);
            }


        }
    }

    function submit_r3d_upload($upload, $dest)
    {
        $pid = posix_getppid();
        if ($upload['is_dir']) {
            if ($upload['items']) {
                foreach ($upload['items'] as $item) {
                    $this->submit_r3d_upload($item, $dest);
                }
            }
            rmdir($upload['path']);
        } else {
            $this->load->model('clips_model');
            $source = $upload['path'];
            if (!file_exists($dest)) {
                mkdir($dest);
            }
            $dest .= '/' . $upload['name'];
            echo $pid . ' ' . date('Y-m-d H:i:s') . ' COPY: ' . $source . ' => ' . $dest, PHP_EOL;
            if (copy($source, $dest) && filesize($source) == filesize($dest)) {
                echo $pid . ' ' . date('Y-m-d H:i:s') . ' COPY SUCCESS', PHP_EOL;
                echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE: ' . $source, PHP_EOL;
                if (unlink($source)) {
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE SUCCESS' . $source, PHP_EOL;
                } else {
                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' DELETE FAILURE' . $source, PHP_EOL;
                }
            }
        }
    }

    function delete_uploads($ids, $provider_login = '')
    {
        foreach ($ids as $id) {
            $uploads = $this->get_uploads_list($provider_login);
            $upload = $this->find_upload($uploads, $id);
            if ($upload) {
                $this->delete_upload($upload);
            }
        }
    }

    function delete_upload($upload)
    {
        $source = $upload['path'];
        if (is_file($source)) {
            if ($upload['is_dir']) {
                if (!$upload['provider'])
                    rmdir($source);
            } else {
                unlink($source);
            }
        }
    }

    function dir_to_array($dir, $level = 1, $provider_login = '')
    {
        $contents = array();
        foreach (scandir($dir) as $node) {
            if ($level == 1 && $provider_login && $node != $provider_login) {
                continue;
            }
            //do not include r3d_uploads!
            if ($node == '.' || $node == '..' || $node == $this->r3d_uploads_dir_name || in_array($node, $this->ignore_names)) continue;
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

    function parse_r3d_dirs($item)
    {
        if ($item['is_dir'] && $item['items']) {
            foreach ($item['items'] as &$sub_item) {
                if (!$sub_item['is_dir'] && strtolower($sub_item['ext']) == 'r3d') {
                    $item['r3d_dir'] = $sub_item['name'];
                }
                if ($sub_item['is_dir'] && $sub_item['items'])
                    $sub_item = $this->parse_r3d_dirs($sub_item);
            }
        }
        return $item;
    }

    function get_available_volume($submission_dir)
    {

        $this->load->model('volumes_model');
        $volumes = $this->volumes_model->get_volumes_list();
        $volumes_by_name = array();
        foreach ($volumes as $volume) {
            $volumes_by_name[$volume['name']] = $volume;
        }

        //return 'FSM10';
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $dir = $store['original']['path'];
        $nodes = scandir($dir);
        foreach ($nodes as $node) {
            if ($node == '.' || $node == '..') continue;

            if (isset($volumes_by_name[$node])) {

                if ($volumes_by_name[$node]['is_full']) continue;

                //if (!$this->volumes_model->is_mounted($dir . '/' . $node)) continue;

                if ($this->is_enough_space_on_volume($dir . '/' . $node, $submission_dir)) {
                    return $node;
                }
            }
        }
        return false;
    }

    function is_enough_space_on_volume($volume_path, $submission_path) {
        if (is_dir($volume_path)) {
            $submission_size = $this->foldersize($submission_path);
            $free = disk_free_space($volume_path);
            $total = disk_total_space($volume_path);
            $left = $total * 0.1;
            if($free > $left && $free > $submission_size){
                return true;
            }
        }
        return false;
    }


    function foldersize($path)
    {
        $total_size = 0;
        if (!is_dir($path)) {
            return filesize($path);
        }
        $files = scandir($path);
        $cleanPath = rtrim($path, '/') . '/';

        foreach ($files as $t) {
            if ($t <> "." && $t <> "..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->foldersize($currentFile);
                    $total_size += $size;
                } else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }
        }

        return $total_size;
    }

    function is_upload_incomplete($upload)
    {
        if ($upload['is_dir'] && $upload['items']) {
            foreach ($upload['items'] as $upload_item) {
                if ($this->is_upload_incomplete($upload_item)) {
                    return true;
                }
            }
        } elseif ($upload['ext'] == 'aspx' || is_file($upload['path'] . '.aspx')) {
            return true;
        }
        return false;
    }

    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") $this->rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function backup_upload($upload, $dest)
    {
        echo date('Y-m-d H:i:s') . ' BACKUP: ' . $upload['path'], PHP_EOL;
        if ($upload['is_dir'] || $upload['r3d_dir']) {
            if ($upload['r3d_dir']) {
                $dest .= '_R3D';
            }
            if (!is_dir($dest)) {
                mkdir($dest);
            }
        } else {
            if (!is_dir($dest)) {
                mkdir($dest);
            }
            $dest .= '/' . $upload['name'];
        }

        return $this->rcopy($upload['path'], $dest);
    }

    function rcopy($src, $dst)
    {
//        if (file_exists($dst)) {
//            $this->rrmdir($dst);
//        }
        $res = true;
        if (is_dir($src)) {
            if (!file_exists($dst)) {
                mkdir($dst);
            }
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..")
                    $res = $this->rcopy("$src/$file", "$dst/$file") ? $res : false;
            }
        } elseif (file_exists($src)) {
            if (is_file($dst) && filesize($src) == filesize($dst)) {
                $res = true;
            } else {
                $res = copy($src, $dst) && filesize($src) == filesize($dst);
            }
        }

        return $res;
    }

    function log_upload($upload, $provider_id, $submission_id) {
        $this->db_master->insert('lib_uploads_log', array(
            'upload_id' => $upload['id'],
            'upload_path_hash' => md5($upload['path']),
            'provider_id' => $provider_id,
            'submission_id' => $submission_id
        ));

        return $this->db_master->insert_id();
    }

    function get_upload_log($upload, $provider_id) {
        $this->db->where('upload_id', $upload['id']);
        $this->db->where('upload_path_hash', md5($upload['path']));
        $this->db->where('provider_id', $provider_id);
        $query = $this->db->get('lib_uploads_log');
        $res = $query->result_array();
        if ($res) {
            return $res[0];
        } else {
            return false;
        }
    }

    function delete_upload_log($id) {
        $this->db_master->where('id', $id);
        $this->db_master->delete('lib_uploads_log');
    }
}