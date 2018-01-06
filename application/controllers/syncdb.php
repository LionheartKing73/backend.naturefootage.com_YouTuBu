<?php

class Syncdb extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->db_from = $this->load->database('live', TRUE);
        $this->db_to = $this->load->database('master', TRUE);
    }

    public function index() {
        show_404();
    }

    public function sync() {
        $this->syncUsers();
        $this->syncClips();
    }

    private function syncUsers() {
        $query = $this->db_from->get_where('lib_users', array('sync' => 0));
        $users = $query->result_array();
        if ($users) {
            foreach ($users  as $userFrom) {
                $userId = $userFrom['id'];
                $this->db_to->select('id');
                $query = $this->db_to->get_where('lib_users', array('login' => $userFrom['login']));
                $userTo = $query->result_array();
                if ($userTo[0]['id']) {
                    $this->db_from->update('lib_users', array('sync' => 1), array('id' => $userId));
                    continue;
                }

                $this->db_to->select('meta_key, meta_value');
                $query = $this->db_to->get_where('lib_users_meta', array('user_id' => $userId));
                $userMeta = $query->result_array();

                unset($userFrom['id']);
                $userFrom['sync'] = 1;
                $this->db_to->insert('lib_users', $userFrom);
                $newUserId = $this->db_to->insert_id();

                if ($userMeta) {
                    foreach ($userMeta as $meta) {
                        $meta['user_id'] = $newUserId;
                        $this->db_to->insert('lib_users', $meta);
                    }
                }

                $this->db_from->update('lib_users', array('sync' => 1), array('id' => $userId));
            }
        }
    }

    private function syncClips() {
        $this->db_from->order_by('id', 'asc');
        $query = $this->db_from->get_where('lib_clips', array('sync' => 0));
        $clips = $query->result_array();
        if ($clips) {
            foreach ($clips  as $clipFrom) {
                $clipId = $clipFrom['id'];
                $query = $this->db_from->get_where('lib_clips_res', array('clip_id' => $clipId));
                $clipRes = $query->result_array();
                if (count($clipRes) >= 4) {

                    $this->db_to->where('id', $clipId);
                    $this->db_to->delete('lib_clips');
                    $this->db_to->where('clip_id', $clipId);
                    $this->db_to->delete('lib_clips_content');
                    $this->db_to->where('clip_id', $clipId);
                    $this->db_to->delete('lib_clips_res');
                    $this->db_to->where('clip_id', $clipId);
                    $this->db_to->delete('lib_clips_res_tasks');
//            $this->db_to->where('clip_id', $clipId);
//            $this->db_to->delete('lib_clips_delivery_formats');
//            $this->db_to->where('clip_id', $clipId);
//            $this->db_to->delete('lib_clip_add_collections');
//            $this->db_to->where('clip_id', $clipId);
//            $this->db_to->delete('lib_clip_keywords');
                    $this->db_to->where('clip_id', $clipId);
                    $this->db_to->delete('lib_thumbnails');

                    if ($clipFrom['client_id']) {
                        $this->db_from->select('login');
                        $query = $this->db_from->get_where('lib_users', array('id' => $clipFrom['client_id']));
                        $userFrom = $query->result_array();
                        unset($clipFrom['client_id']);
                        if ($userFrom) {
                            $this->db_to->select('id');
                            $query = $this->db_to->get_where('lib_users', array('login' => $userFrom[0]['login']));
                            $userTo = $query->result_array();
                            if ($userTo) {
                                $clipFrom['client_id'] = $userTo[0]['id'];
                            }
                        }
                    }

                    $clipFrom['sync'] = 1;
                    $this->db_to->insert('lib_clips', $clipFrom);

                    $query = $this->db_from->get_where('lib_clips_content', array('clip_id' => $clipId));
                    $clipContent = $query->result_array();
                    unset($clipContent[0]['id']);
                    $this->db_to->insert('lib_clips_content', $clipContent[0]);

                    foreach ($clipRes as $res) {
                        unset($res['id']);
                        $this->db_to->insert('lib_clips_res', $res);
                    }

                    $query = $this->db_from->get_where('lib_clips_res_tasks', array('clip_id' => $clipId));
                    $clipResTasks = $query->result_array();
                    foreach ($clipResTasks as $resTask) {
                        unset($resTask['id']);
                        $this->db_to->insert('lib_clips_res_tasks', $resTask);
                    }

                    $query = $this->db_from->get_where('lib_thumbnails', array('clip_id' => $clipId));
                    $clipThumbnails = $query->result_array();
                    foreach ($clipThumbnails as $thumbnail) {
                        unset($thumbnail['id']);
                        $this->db_to->insert('lib_thumbnails', $thumbnail);
                    }
                }
                $this->db_from->update('lib_clips', array('sync' => 1), array('id' => $clipId));
            }
        }
    }

}