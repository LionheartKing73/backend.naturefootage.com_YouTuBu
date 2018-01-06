<?php

class Cats_model extends CI_Model {

    var $type = 0;
    var $cat_path;
	var $cat_dir;

	function __construct() {
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

		$this->cat_dir = $this->config->item('cat_dir');
		$this->cat_path = $this->config->item('cat_path');
		$this->load->model('clips_model');
	}

    function set_type($type) {
        $this->type = $type;
    }

	function get_cats_list($lang = 'en', $active_only = false) {
		$filter_active = $active_only ? ' AND c.active = 1 ' : '';
		$query = $this->db->query(
			'SELECT c.*, cc.title, cc.meta_desc
			FROM lib_cats c
			LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=?
			WHERE c.id > 0 ' . $filter_active . ' AND c.type=' . $this->type
			. ' ORDER BY c.parent_id, c.ord', array($lang));
		$rows = $query->result_array();

		$tree = array();
        $base_url = $this->config->base_url();
		if ($rows) {
			foreach ($rows as $row) {
				$row['title'] = ($row['title']) ? $row['title'] : '-';
				$row['thumb'] = $this->get_image_path($row);
                if($for_api && $row['thumb']){
                    $row['thumb'] = rtrim($base_url, '/') . '/' . $row['thumb'];
                }
				$row['uri'] = $this->make_uri($row['title'], $row['id']);
				if ($row['parent_id'])
					$tree[$row['parent_id']]['child'][] = $row;
				else {
					$tree[$row['id']] = $row;
				}
			}
		}
		return $tree;
	}

    function get_cats_list_total($lang = 'en', $active_only = false) {
        $filter_active = $active_only ? ' AND c.active = 1 ' : '';
        $query = $this->db->query(
            'SELECT COUNT(distinct(c.id)) total FROM lib_cats c
			LEFT JOIN lib_cats_content cc ON c.id = cc.cat_id AND cc.lang = ?
			WHERE c.id > 0 AND c.parent_id = 0 ' . $filter_active, array($lang));
        $rows = $query->result_array();
        return $rows[0]['total'];
    }

    function get_api_cats_count($filter = array(), $lang = 'en') {
        $this->db->where('cc.lang', $lang);
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->select('c.id');
            $this->db->from('lib_cats c');
            $this->db->join('lib_cats_content cc', 'c.id = cc.cat_id', 'left');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_cats');
    }

    function get_api_cats_list($filter = array(), $limit = array(), $order_by = '', $lang = 'en'){
        $this->db->where('cc.lang', $lang);
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $this->db->select('c.*, cc.title, cc.meta_desc');
        $this->db->from('lib_cats c');
        $this->db->join('lib_cats_content cc', 'c.id = cc.cat_id', 'left');
        $query = $this->db->get();
        $rows = $query->result_array();

        $tree = array();
        $base_url = $this->config->base_url();
        if ($rows) {
            foreach ($rows as $row) {
                $this->db->select('c.id');
                $this->db->from('lib_clips c');
                $this->db->join('lib_clips_cats cc', 'c.id = cc.clip_id AND cc.cat_id = ' . $row['id']);
                $row['count'] = $this->db->count_all_results();

                $row['title'] = ($row['title']) ? $row['title'] : '-';
                $row['thumb'] = $this->get_image_path($row);
                if($row['thumb'])
                    $row['thumb'] = rtrim($base_url, '/') . '/' . $row['thumb'];
                $row['uri'] = $this->make_uri($row['title'], $row['id']);
                if ($row['parent_id'])
                    $tree[$row['parent_id']]['child'][] = $row;
                else {
                    $tree[$row['id']] = $row;
                }
            }
        }
        return $tree;
    }

	function get_subcats($id, $lang = 'en', $active_only = false) {
		$filter_active = $active_only ? ' AND c.active = 1 ' : '';
		$query = $this->db->query(
			'SELECT c.*, ctime, cc.title
      FROM lib_cats c
      LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=?
      WHERE c.parent_id = ? ' . $filter_active . $filter . ' ORDER BY c.ord', array($lang, $id));
		$rows = $query->result_array();

        $base_url = $this->config->base_url();
        foreach ($rows as $row) {
			$row['title'] = ($row['title']) ? $row['title'] : '-';
			$row['thumb'] = $this->get_image_path($row);
            $row['uri'] = $this->make_uri($row['title'], $row['id']);
			$tree[$row['id']] = $row;
		}
		return $tree;
	}

    function get_subcats_total($id, $lang = 'en', $active_only = false) {
        $filter_active = $active_only ? ' AND c.active = 1 ' : '';
        $query = $this->db->query(
            'SELECT COUNT(distinct(c.id)) total FROM lib_cats c
            LEFT JOIN lib_cats_content cc ON c.id = cc.cat_id AND cc.lang = ?
            WHERE c.parent_id = ? ' . $filter_active, array($lang, $id));
        $rows = $query->result_array();
        return $rows[0]['total'];
    }

	function change_visible($ids) {
		if (count($ids)) {
			foreach ($ids as $id) {
				$this->db_master->query('UPDATE lib_cats set active = !active where id=' . $id);
			}
		}
	}

	function change_ord($ids) {
		if (is_array($ids) && count($ids)) {
			foreach ($ids as $id => $ord) {
				$this->db_master->where('id', $id);
				$this->db_master->update('lib_cats', array('ord' => $ord));
			}
		}
	}

	function delete_cat($ids) {
		if (count($ids)) {
			foreach ($ids as $cat_id) {
				$query = $this->db->get_where('lib_cats', array('parent_id' => $cat_id));
				$rows = $query->result_array();

				if (count($rows)) {
					foreach ($rows as $row) {
						$this->db_master->delete('lib_cats', array('id' => $row['id']));
						$this->db_master->delete('lib_cats_content', array('cat_id' => $row['id']));
						$this->db_master->delete('lib_clips_cats', array('cat_id' => $row['id']));
					}
				}
				$this->db_master->delete('lib_cats', array('id' => $cat_id));
				$this->db_master->delete('lib_cats_content', array('cat_id' => $cat_id));
				$this->db_master->delete('lib_clips_cats', array('cat_id' => $row['id']));
			}
		}
	}

	function get_cat($id, $lang = 'en') {
		$query = $this->db->query('SELECT c.*, cc.title, cc.meta_title, cc.meta_desc, cc.meta_keys
      FROM lib_cats c
      LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=' . $this->db->escape($lang) .
			'WHERE c.id=' . intval($id));
		$row = $query->result_array();
		return $row[0];
	}

	function get_parents($id, $lang) {
		$query = $this->db->query(
			'SELECT c.id, cc.title
			FROM lib_cats c
			LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=?
			WHERE c.parent_id=0 AND c.id!=?', array($lang, $id));
		return $query->result_array();
	}

	function update_resource($id, $resource = '') {
		$this->db_master->where('id', $id);
		$this->db_master->update('lib_cats', array('resource' => $resource));
	}

	function get_content($id) {
		$query = $this->db->get_where('lib_cats_content', array('cat_id' => $id));
		$row = $query->result_array();
		return $row[0];
	}

	function save_cat($id, $lang='en') {
		$data_cat['parent_id'] = intval($this->input->post('parent_id'));
		$data_cat['ord'] = intval($this->input->post('ord'));
		$data_cat['code'] = $this->input->post('code');
		$data_cat['item_code'] = $this->input->post('item_code');
        $data_cat['private'] = $this->input->post('private') != null ? 1 : 0;
        $data_cat['prepare_downloads'] = $this->input->post('prepare_downloads') != null ? 1 : 0;
        $data_cat['preview_length'] = $this->input->post('preview_length');

		$data_content['cat_id'] = $id;
		$data_content['lang'] = $lang;
		$data_content['title'] = $this->input->post('title');
		$data_content['meta_title'] = $this->input->post('meta_title');
		$data_content['meta_desc'] = '' . $this->input->post('meta_desc');
		$data_content['meta_keys'] = '' . $this->input->post('meta_keys');

		if ($id) {
			$this->db_master->where('id', $id);
			$this->db_master->update('lib_cats', $data_cat);

			$query = $this->db->get_where('lib_cats_content', array('cat_id' => $id, 'lang' => $lang));
			$row = $query->result_array();

			if (count($row)) {
				$this->db_master->where('id', $row[0]['id']);
				$this->db_master->update('lib_cats_content', $data_content);
			}
			else
				$this->db_master->insert('lib_cats_content', $data_content);
		}
		else {
			$data_cat['ctime'] = date('Y-m-d H:i:s');
			$this->db_master->insert('lib_cats', $data_cat);
			$id = $this->db_master->insert_id();

			$data_content['cat_id'] = $this->db_master->insert_id();
			$this->db_master->insert('lib_cats_content', $data_content);
		}
		return $id;
	}

    function get_image_path($data) {
        if($data['item_code']) {
            return $this->clips_model->get_thumb_by_code($data['item_code']);
        }
        if($data['resource']) {
            return $this->cat_path . $data['id'] . '.' . $data['resource'];
        }
        else {
            return '';
        }
    }

	function get_items($id) {
		$list = $this->db->query('SELECT cc.clip_id id, c.code, cc.ord
			FROM lib_clips_cats cc
			INNER JOIN lib_clips c ON c.id = cc.clip_id
			WHERE cat_id = ?
			ORDER BY cc.ord', array($id))->result_array();

		if (!count($list)) {
			return;
		}

		foreach ($list as &$item) {
			$item['thumb'] = $this->clips_model->get_clip_path($item, 'thumb');
		}

		return $list;
	}

	function add_item($id, $item_code) {
		$item_id = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', array($item_code))
			->result_array();
		$item_id = $item_id[0]['id'];

		if (!$item_id) {
			return;
		}

		$row = $this->db->query(
			'SELECT id FROM lib_clips_cats WHERE cat_id = ? AND clip_id = ?',
			array($id, $item_id))->result_array();

		if (count($row)) {
			return;
		}

		$this->db_master->insert('lib_clips_cats', array('clip_id' => $item_id, 'cat_id' => $id));
		return true;
	}

	function remove_items($id, $ids) {
		$id_set = implode(', ', $ids);
		$this->db_master->query('DELETE FROM lib_clips_cats WHERE cat_id = ? AND clip_id IN('
			. $id_set . ')', array($id));
	}

	function save_items_order($id, $ord) {
		foreach ($ord as $clip_id => $ord) {
			$this->db_master->query('UPDATE lib_clips_cats SET ord = ? WHERE cat_id = ? AND clip_id = ?',
				array($ord, $id, $clip_id));
		}
	}

	function get_feature_params($code, $lang = 'en') {
		$item = $this->db->query('SELECT id, item_code, resource FROM lib_cats WHERE code = ?',
			array($code))->result_array();
		$item = $item[0];

		$image = $item['item_code'] ?
			$this->clips_model->get_thumb_by_code($item['item_code']) :
			$this->get_image_path($item);

		$module = array('cat', 'collection');
		$link = $item['id'] ? $lang . '/search/cat/' . $item['id'] . '.html' : 'index.html';

		return array('image' => $image, 'link' => $link);
	}

	function get_cat_ids($str_list) {
		$ids = array();

		if (count($str_list)) {
			$params = substr(str_repeat('?,', count($str_list)), 0, -1);
			$sql =
				'SELECT cc.cat_id id
				FROM lib_cats_content cc
				INNER JOIN lib_cats c ON c.id = cc.cat_id
				WHERE cc.title IN(' . $params . ')';
			$rows = $this->db->query($sql, $str_list)->result_array();
			if ($rows) {
				foreach ($rows as $row) {
					$ids[] = $row['id'];
				}
			}
		}

		return $ids;
	}

	function make_uri($title, $id) {
		$uri = trim(str_replace(array('"', "'"), '', strtolower(trim($title))));
		$uri = trim(str_replace(array(',', '.', ';', ':', '&', ' '), '-', $uri));
		$uri = preg_replace('/[\-]+/', '-', $uri);
		$uri = $uri . '-' . $id;

		return $uri;
	}

	function id_from_uri($uri_segment) {
		$pos = strrpos($uri_segment, '-');
		$id = ($pos === false) ? $uri_segment : substr($uri_segment, $pos + 1);
		$id = intval($id);
		return $id;
	}

    #------------------------------------------------------------------------------------------------

    function upload_image($cat_id) {
        if(is_uploaded_file($_FILES['mimg']['tmp_name'])) {
            $ext = $this->api->get_file_ext($_FILES['mimg']['name']);

            if($this->api->check_ext($ext,'img')) {
                @copy($_FILES['mimg']['tmp_name'], $this->cat_dir.$cat_id.'.'.$ext);
                $this->update_resource($cat_id, $ext);
            }
            else {
                $this->errors = $this->lang->line('incorrect_image');
            }

            $this->api->log('log_cats_upload', $cat_id);
        }
    }

    #------------------------------------------------------------------------------------------------

    function delete_image($id) {

        $query = $this->db->get_where('lib_cats', array('id' => $id));
        $row = $query->result_array();
        $row = $row[0];

        @unlink($this->cat_dir.$id.'.'.$row['resource']);
        $this->update_resource($id);

        $this->api->log('log_cats_unlink', $id);
    }

}