<?php

class Highlights_model extends CI_Model {

	var $highlights_path;
	var $highlights_dir;

	function __construct() {
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

		$this->highlights_dir = $this->config->item('highlights_dir');
		$this->highlights_path = $this->config->item('highlights_path');
	}

	function get_highlights_list($lang = 'en', $active_only = false) {
		$filter_active = $active_only ? ' AND c.active = 1 ' : '';
		$query = $this->db->query(
			'SELECT c.*, cc.title
			FROM lib_highlights c
			LEFT JOIN lib_highlights_content cc ON c.id=cc.highlight_id AND cc.lang=?
			WHERE c.id > 0 ' . $filter_active
			. ' ORDER BY c.ord', array($lang));

        $rows = $query->result_array();

		if ($rows) {
			foreach ($rows as &$row) {
				$row['title'] = ($row['title']) ? $row['title'] : '-';
                if($this->api->check_ext($row['resource'], 'video')){
                    $row['thumb'] = $this->get_resource_path(array('id' => 'video_icon', 'resource' => 'png'));
                }
                else{
				    $row['thumb'] = $this->get_resource_path($row);
                }
			}
		}

		return $rows;
	}

	function change_visible($ids) {
		if (count($ids)) {
			foreach ($ids as $id) {
				$this->db_master->query('UPDATE lib_highlights set active = !active where id=' . $id);
			}
		}
	}

	function change_ord($ids) {
		if (is_array($ids) && count($ids)) {
			foreach ($ids as $id => $ord) {
				$this->db_master->where('id', $id);
				$this->db_master->update('lib_highlights', array('ord' => $ord));
			}
		}
	}

	function delete_highlights($ids) {
		if (count($ids)) {
			foreach ($ids as $highlight_id) {
                $this->delete_resource($highlight_id);
				$this->db_master->delete('lib_highlights', array('id' => $highlight_id));
				$this->db_master->delete('lib_highlights_content', array('highlight_id' => $highlight_id));
			}
		}
	}

	function get_highlight($id, $lang = 'en') {
		$query = $this->db->query('SELECT c.*, cc.title
      FROM lib_highlights c
      LEFT JOIN lib_highlights_content cc ON c.id=cc.highlight_id AND cc.lang=' . $this->db->escape($lang) .
			'WHERE c.id=' . intval($id));
		$row = $query->result_array();
        $row[0]['resource_file'] = $this->get_resource_path($row[0]);
        if($this->api->check_ext($row[0]['resource'], 'video')){
            $row[0]['resource_video'] = true;
        }
		return $row[0];
	}

	function update_resource($id, $resource = '') {
		$this->db_master->where('id', $id);
		$this->db_master->update('lib_highlights', array('resource' => $resource));
	}

	function save_highlight($id, $lang='en') {
		$data_highlight['ord'] = intval($this->input->post('ord'));
        $data_highlight['link'] = preg_replace('/[^a-zA-Z0-9\.]+/', '', $this->input->post('link'));

		$data_content['highlight_id'] = $id;
		$data_content['lang'] = $lang;
		$data_content['title'] = $this->input->post('title');

		if ($id) {
			$this->db_master->where('id', $id);
			$this->db_master->update('lib_highlights', $data_highlight);

			$query = $this->db->get_where('lib_highlights_content', array('highlight_id' => $id, 'lang' => $lang));
			$row = $query->result_array();

			if (count($row)) {
				$this->db_master->where('id', $row[0]['id']);
				$this->db_master->update('lib_highlights_content', $data_content);
			}
			else
				$this->db_master->insert('lib_highlights_content', $data_content);
		}
		else {
			$data_highlight['ctime'] = date('Y-m-d H:i:s');
			$this->db_master->insert('lib_highlights', $data_highlight);
			$id = $this->db_master->insert_id();

			$data_content['highlight_id'] = $this->db_master->insert_id();
			$this->db_master->insert('lib_highlights_content', $data_content);
		}
		return $id;
	}

    function get_resource_path($data) {
        if($data['resource']) {
            return $this->highlights_path . $data['id'] . '.' . $data['resource'];
        }
        else {
            return '';
        }
    }

    #------------------------------------------------------------------------------------------------

    function upload_resource($highlight_id) {
        if(is_uploaded_file($_FILES['mresource']['tmp_name'])) {
            $ext = $this->api->get_file_ext($_FILES['mresource']['name']);

            if($this->api->check_ext($ext, 'img') || $this->api->check_ext($ext, 'video')) {
                $this->delete_resource($highlight_id);
                @copy($_FILES['mresource']['tmp_name'], $this->highlights_dir . $highlight_id . '.' . $ext);
                $this->update_resource($highlight_id, $ext);
            }
            else {
                $this->errors = $this->lang->line('incorrect_filetype');
            }

            $this->api->log('log_highlight_upload', $highlight_id);
        }
    }

    #------------------------------------------------------------------------------------------------

    function delete_resource($id) {

        $query = $this->db->get_where('lib_highlights', array('id' => $id));
        $row = $query->result_array();
        $row = $row[0];

        @unlink($this->highlights_dir . $id . '.' . $row['resource']);
        $this->update_resource($id);

        $this->api->log('log_highlights_unlink', $id);
    }

    function get_links($lang)
    {
        $query = $this->db->query('select lp.*, lpc.title from lib_pages as lp left join lib_pages_content as lpc on lp.id=lpc.page_id and lpc.lang='.$this->db->escape($lang).' where lp.id > 0');
        $rows = $query->result_array();
        return $rows;
    }

}