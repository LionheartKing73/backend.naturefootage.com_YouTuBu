<?php

/** @property Clips_model $clips_model */

class Submissions_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->helper( 'emailer' );
    }

    function get_submissions_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                if($param == 'words') {
                    $this->db->like('code', $value);
                }
                else {
                    $this->db->where($param, $value);
                }
            }
            $this->db->from('lib_submissions');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_submissions');
    }

    function get_submissions_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                if($param == 'words') {
                    $this->db->like('code', $value);
                }
                else {
                    $this->db->where($param, $value);
                }
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_submissions');
        $res = $query->result_array();
        return $res;
    }

    function get_empty_submissions_list(){
        $this->db->select('s.*');
        $this->db->join('lib_clips c', 's.id = c.submission_id', 'left');
        $this->db->where('c.id', null);
        $query = $this->db->get('lib_submissions s');
        $res = $query->result_array();
        return $res;
    }

    public function delete_empty_submissions() {
        $submissions = $this->get_empty_submissions_list();
        if ($submissions) {
            $ids = array();
            foreach ($submissions as $submission) {
                $ids[] = $submission['id'];
            }
            $this->db_master->where_in('id', $ids);
            $this->db_master->delete('lib_submissions');
        }
    }

    function save_submission($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_submissions', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_submissions', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_submission($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_submissions');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_submissions($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_submissions', array('id' => $id));
                $this->db_master->where('submission_id', $id);
                $this->db_master->update('lib_clips', array('submission_id' => 0));
            }
        }
    }

    function get_last_submission_code($prefix = ''){
        if(!$prefix)
            $prefix = 'FS';
        $today = date('Y-m-d');
        $this->db->where('date', $today);
        $query = $this->db->get('lib_submissions');
        $row_count = $query->num_rows();
        $code = $prefix . date('ymd');
        if($row_count > 0)
            $code .= '-' . $row_count;
        return $code;
    }

    function create_submission($code = '', $provider_id = 0, $prefix = ''){

        if(!$code){
            if(!$prefix)
                $prefix = 'FS';
            $code = $prefix . date('ymd');
            $this->db->where('code', $code);
            $query = $this->db->get('lib_submissions');
//            $row_count = $query->num_rows();
//            if($row_count > 0){
//                $code = $this->get_last_submission_code($prefix);
//            }
            $res = $query->result_array();
            if(count($res) > 0)
                return $res[0]['id'];

            $date = date('Y-m-d');
            $data = array(
                'code' => $code,
                'date' => $date,
                'provider_id' => $provider_id
            );
            $this->db_master->insert('lib_submissions', $data);
	        $this->SendNotification_NewSubmission( $data );
            return $this->db_master->insert_id();
        }
        else{
            $this->db->where('code', $code);
            $this->db->limit(1);
            $query = $this->db->get('lib_submissions');
            $res = $query->result_array();
            if(count($res) > 0)
                return $res[0]['id'];
            else{
                $date_time = DateTime::createFromFormat('ymd', substr($code, 2, 6));
                $date = $date_time->format('Y-m-d');
                $data = array(
                    'code' => $code,
                    'date' => $date,
                    'provider_id' => $provider_id
                );
                $this->db_master->insert('lib_submissions', $data);
	            $this->SendNotification_NewSubmission( $data );
                return $this->db_master->insert_id();
            }
        }
    }

    function set_submission_location($id, $location){
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_submissions', array('location' => $location));
            return $id;
        }
    }


    function set_sync($id, $sync){
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_submissions', array('sync' => $sync));
            return $id;
        }
    }

    function get_submissions_tree($filter = array(), $limit = array(), $order_by = '', $selected = 0) {
        $submissions = $this->get_submissions_list($filter, $limit, $order_by);
        $tree = array();
        foreach($submissions as $submission) {
            $date = strtotime($submission['date']);
            $year = date('Y', $date);
            $month = date('m', $date);
            if($selected) {
                if($submission['id'] == $selected) {
                    $tree[$year]['selected'] = true;
                    $tree[$year]['months'][$month]['selected'] = true;
                }
            }
            elseif($year == date('Y')){
                $tree[$year]['selected'] = true;
            }
            $tree[$year]['months'][$month]['submissions'][] = $submission;
        }
//        echo '<pre>';
//        print_r($tree);
//        exit();
        return $tree;
    }

	private function SendNotification_NewSubmission ( $data ) {
		// Уведомляем администратора, что создан новый сабмишн
		$emailer = Emailer::In();
		$emailer->LoadTemplate( 'toadmin-new-submission' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientAdmin();
		$emailer->SetTemplateValue( 'submission', $data );
		$emailer->Send();
		$emailer->Clear();
	}

    public function SendNotificationProvider_NewSubmission($code,$provider_id){
        // Уведомляем провайдера, что создан новый сабмишн
		$emailer = Emailer::In();
		$emailer->LoadTemplate( 'toprovider-new-submission' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientFromId( $provider_id );
		$emailer->SetTemplateValue( 'submission','code', $code );
		$emailer->Send();
		$emailer->Clear();
    }
}