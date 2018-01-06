<?php

require_once(APPPATH . '/libraries/SorlSearchAdapter.php');
require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;


/**
 * @property Settings_model $settings_model
 * @property Videositemap_model $videositemap_model
 * @property Editors_model $editors_model
 * @property Submissions_model $submissions_model
 * @property Groups_model $groups_model
 * @property Deliveryoptions_model $deliveryoptions_model
 */

class Clips_model extends CI_Model {

	const CLIP_ACTION_VIEW = 1;
	const CLIP_ACTION_DOWNLOAD_PREVIEW = 2;
	const CLIP_ACTION_ORDERED = 3;
	const CLIP_ACTION_DOWNLOAD_FULL = 4;

	var $img_types = array('jpg', 'jpeg', 'png', 'gif');
	var $motion_types = array('mov', 'mp4', 'flv');
	var $codecs = array(
		'YUV' => 'Uncompressed'
	);
	var $res_type;
	var $autocreate_sitemap;
	var $filter_sql;

	function Clips_model() {
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

		$this->res_type = array('thumb' => 0, 'preview' => 1, 'res' => 2, 'motion_thumb' => 0);

		$this->load->model('editors_model');
		$this->load->model('locations_model');
		$this->load->model('videositemap_model');
		$this->load->model('settings_model');
		$this->load->model('register_model');
        $this->load->helper('timeline');
	}

    function clearSqlFilter () {
        $this->filter_sql = NULL;
    }

	function ClipLogger ( $clip_id, $provider_id, $user_login, $type ){
		$this->db_master->query( "INSERT INTO lib_clips_extra_statistic ( clip_id, provider_id, user_login, action_type ) VALUES ( ?, ?, ?, ? )", array( $clip_id, $provider_id, $user_login, $type ) );
	}

	function create_sitemap() {

		if ($this->settings_model->get_setting('video_sitemap_autocreate')) {

			$clips = $this->get_clips_list($this->config->item('default_language'), '', '', 'limit 50000');
			$this->videositemap_model->create_map($clips);
		}
	}

	function prepare_words($filter) {
		$words = $this->db->escape(preg_replace('/ +/', ' ', trim($filter['words'])));

		if (!empty($filter['search_mode'])) {
			switch ($filter['search_mode']) {
				case 1:
					$words = "'+" . str_replace(' ', ' +', substr($words, 1));
					break;
				case 2:
					$words = '\'"' . substr($words, 1, -1) . '"\'';
					break;
			}
		}

		return $words;
	}

	function build_filter_sql($filter) {
		if (empty($filter)) {
			return;
		}

		foreach ($filter as $name => $value) {
			$part = '';

			switch ($name) {
				case 'words':
					$words = $this->prepare_words($filter);
					if ($words) {
						$part = ' (MATCH(c.code, c.original_filename, cc.title, cc.creator, cc.rights, cc.subject, cc.description, cc.keywords)
							AGAINST(' . $words . ' IN BOOLEAN MODE) > 0) ';
					}
					break;
				/*case 'footage_type_id':
					$part = ' (c.footage_type_id = ' . intval($value) . ') ';
					break;*/
				/*case 'location_id':
					$part = ' (c.location_id IN (' .
						$this->locations_model->get_filter(intval($value)) . ')) ';
					break;*/
				case 'frame_rate':
					$part = ' (c.frame_rate = ' . floatval($value) . ') ';
					break;
				case 'sd_hd':
					if ($value == 'sd') {
						$part = ' (c.width < 1280 AND c.height < 720) ';
					}
					elseif ($value == 'hd') {
						$part = ' (c.width >= 1280 AND c.height >= 720) ';
					}
					break;
                case 'parent':
                    $part = ' (c.parent = ' . (int)$value . ') ';
                    break;
                case 'active':
                    if(is_array($value))
                        $part = ' (c.active IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.active = ' . (int)$value . ') ';
                    break;
                case 'collection':
                    if(is_array($value))
                        $part = ' (c.collection IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.collection = ' . (int)$value . ') ';
                    break;
                case 'client_id':
                    $part = ' (c.client_id = ' . (int)$value . ') ';
                    break;
                case 'submission_id':
					$part = ' (c.submission_id = ' . intval($value) . ') ';
					break;
                case 'id':
                    if(is_array($value))
                        $part = ' (c.id IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.id = ' . intval($value) . ') ';
                    break;
                case 'license':
                    if(is_array($value))
                        $part = ' (c.license IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.license = ' . intval($value) . ') ';
                    break;
			}

			if ($part) {
				if (!empty($this->filter_sql)) {
					$this->filter_sql .= ' AND ';
				}
				$this->filter_sql .= $part;
			}
		}

		if (!empty($this->filter_sql)) {
			$this->filter_sql = ' WHERE ' . $this->filter_sql;
		}

		if (!empty($filter['cat_id'])) {
			$this->filter_sql =
				' INNER JOIN lib_clips_cats ccs ON ccs.clip_id = c.id AND ccs.cat_id = '
				. $filter['cat_id'] . $this->filter_sql;
		}

        if (!empty($filter['sequence_id'])) {
            $this->filter_sql =
                ' INNER JOIN lib_clip_sequences csq ON csq.clip_id = c.id AND csq.sequence_id = '
                    . $filter['sequence_id'] . $this->filter_sql;
        }

        if (!empty($filter['bin_id'])) {
            $this->filter_sql =
                ' INNER JOIN lib_clip_bins cbn ON cbn.clip_id = c.id AND cbn.bin_id = '
                    . $filter['bin_id'] . $this->filter_sql;
        }

        if (!empty($filter['gallery_id'])) {
            $this->filter_sql =
                ' INNER JOIN lib_clip_galleries cgl ON cgl.clip_id = c.id AND cgl.gallery_id = '
                    . $filter['gallery_id'] . $this->filter_sql;
        }

        if (!empty($filter['clipbin_id'])) {
            $this->filter_sql =
                ' INNER JOIN lib_lb_items lbi ON lbi.item_id = c.id AND lbi.lb_id = '
                    . $filter['clipbin_id'] . $this->filter_sql;
        }

        if (!empty($filter['backend_clipbin_id'])) {
            $this->filter_sql =
                ' INNER JOIN lib_backend_lb_items blbi ON blbi.item_id = c.id AND blbi.backend_lb_id = '
                . $filter['backend_clipbin_id'] . $this->filter_sql;
        }
	}

    function get_clips_count ( $lang = 'en', $filter = NULL ) {
        $this->build_filter_sql( $filter );
        $row = $this->db->query(
                'SELECT COUNT(distinct(c.id)) total
            FROM lib_clips c
            LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=? '
                . $this->filter_sql, $lang)->result_array();
        return $row[ 0 ][ 'total' ];
    }

	function get_clips_list($lang = 'en', $filter = '', $order = '', $limit = null, $for_api = false) {
        if ( $this->filter_sql || empty( $filter ) ) {
            $filter = '';
        }
        if ( !$for_api ) {
            # Старый, медленный запрос
            /*
            $query = $this->db->query('SELECT c.*, cc.title, cc.description, u.fname, u.lname,
                ( SELECT time FROM lib_clips_extra_statistic AS ex WHERE ex.clip_id = c.id ORDER BY time DESC LIMIT 1 ) AS activity
                FROM lib_clips c
                INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?
                LEFT JOIN lib_users u ON c.client_id = u.id'
                . $this->filter_sql . $filter . $order . $limit, $lang);
            */
            # Новый, оптимизированный запрос. Тесты показали прирост скорости в 2 раза
            # Сперва производится выборка c.id на основе фильтров, после чего для найденых c.id делается выборка остальных данных
            # "cc.lang = ?" в подзапросе значительно замедляет посик c.id, в 3-4 раза, временно размещен за пределами подзапроса
            # Если клипы будут всегда на одном языке - en, то проблем быть не должно
            $query = $this->db->query( "
                SELECT
                    lib_clips.*,
                    lib_clips_content.title,
                    lib_clips_content.description,
                    lib_users.fname,
                    lib_users.lname,
                   ( SELECT ces.time FROM lib_clips_extra_statistic AS ces WHERE ces.clip_id = lib_clips.id ORDER BY ces.time DESC LIMIT 1 ) AS activity
                FROM lib_clips
                JOIN (
                    SELECT c.id
                    FROM lib_clips AS c
                    INNER JOIN lib_clips_content AS cc ON c.id = cc.clip_id
                    LEFT JOIN lib_users AS u ON c.client_id = u.id
                    {$this->filter_sql}
                    {$filter}
                    {$order}
                    {$limit}
                ) AS ids ON ids.id = lib_clips.id
                    INNER JOIN lib_clips_content ON lib_clips.id = lib_clips_content.clip_id
                    LEFT JOIN lib_users ON lib_clips.client_id = lib_users.id
                    WHERE lib_clips_content.lang = ?",
                $lang
            );
        } else {
            $query = $this->db->query( 'SELECT c.*, cc.title, cc.description
                FROM lib_clips c
                INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?'
                . $this->filter_sql . $filter . $order . $limit, $lang );
        }
        $rows = $query->result_array();

        /*$query = $this->db->query('SELECT distinct(li.id), li.*, DATE_FORMAT(li.ctime, \'%d.%m.%Y %T\') ctime,
      lc.title, lc.description, lc.keywords, lu.login folder
      FROM lib_users lu, lib_clips_cats lic
      RIGHT JOIN lib_clips li ON li.id=lic.clip_id
      LEFT JOIN lib_clips_content lc ON li.id=lc.clip_id AND lc.lang=' . $this->db->escape($lang) . '
      WHERE lu.id=li.client_id ' . $filter . $order . $limit);
        $rows = $query->result_array();*/

        $base_url = $this->config->base_url();
        foreach ($rows as &$row) {
			if($for_api){
                $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
                $row['preview'] = $this->get_clip_path($row['id'], 'preview');
                $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
                $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
            }
            else{
                $row['url'] = '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['res'] = $this->get_clip_path($row['id'], 'res');
                $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
                $row['preview'] = $this->get_clip_path($row['id'], 'preview');
                $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
                $row['download'] = $lang . '/clips/content/' . $row['id'];
            }
		}

		return $rows;
	}

    function get_clips_ids($filter = array()){
        $ids = array();
        $this->db->select('id');
        foreach ($filter as $name => $value) {
            switch ($name) {
                case 'id':
                    if(is_array($value))
                        $this->db->where_in('id', $value);
                    else
                        $this->db->where('id', (int)$value);
                    break;
            }
        }
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        if($res) {
            foreach($res as $clip){
                $ids[] = $clip['id'];
            }
        }
        return $ids;
    }

	function get_clip_clients_list() {
		$query = $this->db->query(
			'select DISTINCT u.id as id, u.login as name from lib_clips as l
      inner join lib_users as u on l.client_id=u.id');
		$rows = $query->result_array();

		return $rows;
	}

	function change_visible($ids) {
		if (count($ids)) {
			foreach ($ids as $id) {
				$this->db_master->query('UPDATE lib_clips set active = !active where id=' . $id);
			}
		}
	}

    function set_visible($visible_status, $ids) {
        $this->db_master->where_in('id', $ids);
        $this->db_master->update('lib_clips', array('active' => $visible_status));
        if($visible_status)
            $this->add_to_index($ids);
        else
            $this->delete_from_index($ids);
    }

	function delete_clips($ids, $lang) {
		if (count($ids)) {
			foreach ($ids as $id) {
				$this->delete_resource($id, 'thumb');
				$this->delete_resource($id, 'preview');
				$this->delete_resource($id, 'res');
                //$this->delete_frames($id);
                //$this->delete_keywording_fragment($id);

				$this->db_master->delete('lib_clips', array('id' => $id));
				$this->db_master->delete('lib_clips_content', array('clip_id' => $id));
			}
		}
        $this->delete_from_index($ids);
	}

    function delete_frames($id){
        $this->db_master->update('lib_clips', array('frames_count' => 0), array('id' => $id));
        $dir = $this->config->item('clip_dir');
        $frames_dir = $dir . 'frames/clip_' . $id;
        if(is_dir($frames_dir)){
            foreach(glob($frames_dir . '/*') as $dir_file) {
                if(is_dir($dir_file))
                    rmdir($dir_file);
                else
                    unlink($dir_file);
            }
            rmdir($frames_dir);
        }
    }

    function delete_keywording_fragment($id){
        $this->db_master->delete('lib_keywording_fragment', array('clip_id' => $id));
    }

	function get_clip_code($id) {
		$row = $this->db->query('SELECT code FROM lib_clips WHERE id = ?', array($id))->result_array();
		return $row[0]['code'];
	}

	function get_clip($id, $lang = 'en') {
		$list = $this->db->query(
				'SELECT c.*, cc.title, cc.creator, cc.rights, cc.description, cc.keywords, cc.subject, cc.primary_subject, cc.other_subject,
                cc.shot_type, cc.actions, cc.location/*, cc.subject_category, cc.appearance, cc.actions, cc.time, cc.habitat, cc.concept, cc.location*/
      FROM lib_clips c
      LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?
      WHERE c.id=?', array($lang, intval($id)))->result_array();
		return $list[0];
	}

    function get_clip_by_code($code, $lang = 'en') {
        $list = $this->db->query(
            'SELECT c.*, cc.title, cc.creator, cc.rights, cc.description, cc.keywords, cc.subject, cc.primary_subject, cc.other_subject,
            cc.shot_type, cc.actions, cc.location, cc.subject_category, cc.appearance, cc.actions, cc.time, cc.habitat, cc.concept, cc.location
  FROM lib_clips c
  LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?
  WHERE c.code=?', array($lang, $code))->result_array();
        return $list[0];
    }

    function get_clip_for_edit($id, $lang = 'en') {
        $list = $this->db->query(
            'SELECT c.*, cc.title, cc.description, cc.keywords, cc.shot_type, cc.subject_category,
            cc.primary_subject, cc.other_subject, cc.appearance, cc.actions, cc.time, cc.habitat, cc.concept, cc.location, cc.notes
      FROM lib_clips c
      LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?
      WHERE c.id=?', array($lang, intval($id)))->result_array();

        $list[0]['thumb'] = $this->get_clip_path($list[0]['id'], 'thumb');
        $list[0]['motion_thumb'] = $this->get_clip_path($list[0]['id'], 'motion_thumb');
        $list[0]['preview'] = $this->get_clip_path($list[0]['id'], 'preview');
        $list[0]['download'] = $lang . '/clips/content/' . $list[0]['id'];
        return $list[0];
    }

	function get_clip_info($id, $lang = 'en', $for_api = false, $user = false) {
        if(is_numeric($id))
		    $clip = $this->get_clip($id, $lang);
        else
            $clip = $this->get_clip_by_code($id, $lang);

//		if ($clip['active'] != 1) {
//			return null;
//		}

        $base_url = $this->config->base_url();
        if($user)
            $clip['res'] = $this->get_clip_path($clip['id'], 'preview');
        else
            $clip['res'] = $this->get_clip_path($clip['id'], 'motion_thumb');

        $clip['frames'] = array(
            'count' => $clip['frames_count'],
            'path' => '/' . $this->config->item('clip_path') . 'frames/clip_' . $id,
            'first_frame' => 'frame_1.jpg'
        );
        $clip['thumb'] = $this->get_clip_path($clip['id'], 'thumb');
        $clip['motion_thumb'] = $this->get_clip_path($clip['id'], 'motion_thumb');
        $clip['preview'] = $this->get_clip_path($clip['id'], 'preview', $user);
        $clip['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $clip['id'];
		//$clip['location'] = $this->locations_model->get_location_string($clip['location_id']);

		$clip['meta_keys'] = $clip['keywords'];
        $clip['keywords'] = trim($clip['keywords'], ',');
        if(!$for_api)
		    $clip['keywords'] = $this->get_linked_keywords($lang, $clip['keywords']);
		$clip['owner'] = $this->editors_model->get_profile($clip['client_id']);
        $clip['creation_date'] = date('d.m.Y', strtotime($clip['creation_date']));

		return $clip;
	}

	function save_clip_data($id, $data, $lang = 'en') {

        if(isset($data['date_filmed']['month']) && $data['date_filmed']['month'] && isset($data['date_filmed']['year']) && $data['date_filmed']['year'])
            $data_clip['film_date'] = date('Y-m-d', mktime(0, 0, 0, (int)$data['date_filmed']['month'], 1, (int)$data['date_filmed']['year']));

        if(isset($data['collection']))
            $data_clip['collection'] = $data['collection'];

        if(isset($data['price_level']))
            $data_clip['price_level'] = $data['price_level'];

        if(isset($data['calc_price_level']))
            $data_clip['calc_price_level'] = $data['calc_price_level'];

        if(isset($data['license_type']))
            $data_clip['license'] = $data['license_type'];

        if(isset($data['releases']))
            $data_clip['releases'] = $data['releases'];

        if(isset($data['file_formats']['camera_model']))
            $data_clip['camera_model'] = $data['file_formats']['camera_model'];

        if(isset($data['file_formats']['camera_chip_size']))
            $data_clip['camera_chip_size'] = $data['file_formats']['camera_chip_size'];

        if(isset($data['file_formats']['bit_depth']))
            $data_clip['bit_depth'] = $data['file_formats']['bit_depth'];

        if(isset($data['file_formats']['color_space']))
            $data_clip['color_space'] = $data['file_formats']['color_space'];

        if(isset($data['file_formats']['source_frame_size']))
            $data_clip['source_frame_size'] = $data['file_formats']['source_frame_size'];

        if(isset($data['file_formats']['source_frame_rate']))
            $data_clip['source_frame_rate'] = $data['file_formats']['source_frame_rate'];

        if(isset($data['file_formats']['source_data_rate']))
            $data_clip['source_data_rate'] = $data['file_formats']['source_data_rate'];

        if(isset($data['file_formats']['source_codec']))
            $data_clip['source_codec'] = $data['file_formats']['source_codec'];

        if(isset($data['file_formats']['source_format']))
            $data_clip['source_format'] = $data['file_formats']['source_format'];

        if(isset($data['file_formats']['master_format']))
            $data_clip['master_format'] = $data['file_formats']['master_format'];

        if(isset($data['file_formats']['master_frame_size']))
            $data_clip['master_frame_size'] = $data['file_formats']['master_frame_size'];

        if(isset($data['file_formats']['master_frame_rate']))
            $data_clip['master_frame_rate'] = $data['file_formats']['master_frame_rate'];

        if(isset($data['file_formats']['digital_file_format']))
            $data_clip['digital_file_format'] = $data['file_formats']['digital_file_format'];

        if(isset($data['file_formats']['digital_file_frame_size']))
            $data_clip['digital_file_frame_size'] = $data['file_formats']['digital_file_frame_size'];

        if(isset($data['file_formats']['digital_file_frame_rate']))
            $data_clip['digital_file_frame_rate'] = $data['file_formats']['digital_file_frame_rate'];

        if(isset($data['file_formats']['source_codec']))
            $data_clip['source_codec'] = $data['file_formats']['source_codec'];

        if(!isset($data['file_formats']['pricing_category'])){
            // Set Delivery Category based on Submission Codec
            if(isset($data['file_formats']['digital_file_format'])) {
                $this->db->select('delivery_category');
                $query = $this->db->get_where('lib_submission_codecs', array('name' => $data['file_formats']['digital_file_format']));
                $row = $query->result_array();
                if($row[0]['delivery_category'])
                    $data['file_formats']['pricing_category'] = $row[0]['delivery_category'];
            }
        }

        if(isset($data['file_formats']['pricing_category'])){
            $data_clip['pricing_category'] = $data['file_formats']['pricing_category'];
            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $id));
            if(isset($data['license_type'])){
                $license = $data['license_type'];
            }
            else{
                $license = $this->get_license($id);
            }
            if($license){
                $pricing_cat = $data['file_formats']['pricing_category'];
                if($license == 1){
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_rf_delivery_options')->result_array();
                }
                else{
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_delivery_options')->result_array();
                }
                foreach($delivery_formats as $format){
                    if($format['categories']){
                        $categories = explode(' ', $format['categories']);
                        if(in_array($pricing_cat, $categories)){
                            $this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $id, 'format_id' => $format['id']));
                        }
                    }
                }
            }
        }

        if(!isset($data['file_formats']['master_lab']) || !$data['file_formats']['master_lab'])
            $data_clip['master_lab'] = 'Deluxe Media (Digital Files Only)';
        else
            $data_clip['master_lab'] = $data['file_formats']['master_lab'];

        if(isset($data['country']))
            $data_clip['country'] = $data['country'];

        $data_content = array();
        if(isset($data['clip_description']))
            $data_content['description'] = $data['clip_description'];
        if(isset($data['clip_notes']))
            $data_content['notes'] = $data['clip_notes'];

		if($id){

            if(isset($data['add_collection'])){
                $this->db_master->delete('lib_clips_collections', array('clip_id' => $id));
                if(is_array($data['add_collection'])){
                    foreach($data['add_collection'] as $collection_id){
                        $this->db_master->insert('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $collection_id));
                    }
                }
            }
            else{
                if(isset($data['file_formats']['source_format']) && stripos($data['file_formats']['source_format'], '3D') !== false){
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_collections', array('name' => '3D Footage'));
                    $row = $query->result_array();
                    if($row[0]['id']){
                        $this->db_master->delete('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
                        $this->db_master->insert('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
                    }
                }
                if(isset($data['file_formats']['digital_file_frame_size']) && stripos($data['file_formats']['digital_file_frame_size'], 'Ultra HD') !== false){
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_collections', array('name' => 'Ultra HD Footage'));
                    $row = $query->result_array();
                    if($row[0]['id']){
                        $this->db_master->delete('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
                        $this->db_master->insert('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
                    }
                }
            }

//            if(isset($data['license_type']) && is_array($data['license_type'])){
//                $this->db_master->delete('lib_clip_license_types', array('clip_id' => $id));
//                foreach($data['license_type'] as $license_id){
//                    $this->db_master->insert('lib_clip_license_types', array('clip_id' => $id, 'license_id' => $license_id));
//                }
//            }

            if(isset($data['keywords'])){
                $this->db_master->delete('lib_clip_keywords', array('clip_id' => $id));
                foreach($data['keywords'] as $keyword_id){
                    $this->db_master->insert('lib_clip_keywords', array('clip_id' => $id, 'keyword_id' => $keyword_id));
                }
                $clip_keywords = $this->db->query('SELECT keyword, section FROM lib_keywords lk
                    INNER JOIN lib_clip_keywords lck ON lk.id = lck.keyword_id AND lck.clip_id = ?', array($id))->result_array();
                $data_content['shot_type'] = '';
                $data_content['subject_category'] = '';
                $data_content['primary_subject'] = '';
                $data_content['other_subject'] = '';
                $data_content['appearance'] = '';
                $data_content['actions'] = '';
                $data_content['time'] = '';
                $data_content['habitat'] = '';
                $data_content['concept'] = '';
                $data_content['location'] = '';
                $data_content['keywords'] = '';
                if($clip_keywords){
                    foreach($clip_keywords as $keyword){
                        if($data_content[$keyword['section']])
                            $data_content[$keyword['section']] .= ', ' . $keyword['keyword'];
                        else
                            $data_content[$keyword['section']] = $keyword['keyword'];

                        if($data_content['keywords'])
                            $data_content['keywords'] .= ', ' . $keyword['keyword'];
                        else
                            $data_content['keywords'] = $keyword['keyword'];
                    }
                }

            }

			// Передаем флаг, что клип был настроен
			$data_clip[ 'untuned' ] = 0;
            $data_clip['active'] = 1;

			$this->db_master->where('id', $id);
			$this->db_master->update('lib_clips', $data_clip);
			if($data_content){
                $query = $this->db->get_where('lib_clips_content', array('clip_id' => $id, 'lang' => $lang));
                $row = $query->result_array();
                if (count($row)) {
                    $this->db_master->where('id', $row[0]['id']);
                    $this->db_master->update('lib_clips_content', $data_content);
                }
                else
                    $this->db_master->insert('lib_clips_content', $data_content);
            }

		}
	}

    function save_clip($id, $lang = 'en') {

        $client_id = $this->input->post('client_id');
        if ($client_id) {
            $data_clip['client_id'] = $client_id;
        } elseif($this->session->userdata('client_uid')) {
            $data_clip['client_id'] = $this->session->userdata('client_uid');
        }

        $data_clip['code'] = $this->input->post('code');
        $data_clip['creation_date'] = $this->input->post('creation_date');
        if (empty($data_clip['creation_date'])) {
            unset($data_clip['creation_date']);
        }
        $data_clip['aspect'] = $this->input->post('aspect');
        //$data_clip['frame_rate'] = $this->input->post('frame_rate');
        //$data_clip['codec'] = $this->input->post('codec');
        //$data_clip['format'] = $this->input->post('format');
        $data_clip['location_id'] = intval($this->input->post('location_id'));
        //$data_clip['footage_type_id'] = intval($this->input->post('footage_type_id'));
        $data_clip['price'] = floatval($this->input->post('price'));
        $data_clip['price_per_second'] = floatval($this->input->post('price_per_second'));
        //$data_clip['width'] = $this->input->post('width');
        //$data_clip['height'] = $this->input->post('height');
        $data_clip['license'] = $this->input->post('license');
        if (!$data_clip['license'])
            $data_clip['license'] = 1;

        $data_clip['of_id'] = intval($this->input->post('of_id'));
        $data_clip['pricing_category'] = $this->input->post('pricing_category');
        $data_clip['calc_price_level'] = $this->input->post('calc_price_level');
        $data_clip['price_level'] = $this->input->post('price_level');
        $data_clip['master_frame_rate'] = $this->input->post('master_frame_rate');
        $data_clip['digital_file_frame_rate'] = $this->input->post('digital_file_frame_rate');
        $data_clip['digital_file_format'] = $this->input->post('digital_file_format');
        $data_clip['color_system'] = $this->input->post('color_system');

        $data_content['clip_id'] = $id;
        $data_content['lang'] = $lang;
        $data_content['title'] = $this->input->post('title');
        $data_content['creator'] = $this->input->post('creator');
        $data_content['rights'] = $this->input->post('rights');
        $data_content['subject'] = $this->input->post('subject');
        $data_content['description'] = $this->input->post('description');
        $data_content['keywords'] = $this->input->post('keywords');

        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_clips', $data_clip);

            $query = $this->db->get_where('lib_clips_content', array('clip_id' => $id, 'lang' => $lang));
            $row = $query->result_array();

            if (count($row)) {
                $this->db_master->where('id', $row[0]['id']);
                $this->db_master->update('lib_clips_content', $data_content);
            }
            else
                $this->db_master->insert('lib_clips_content', $data_content);

            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $id));
            if($this->input->post('pricing_category') &&  $data_clip['license']){
                $pricing_categories = explode(',', $this->input->post('pricing_category'));
                foreach($pricing_categories as $key => $cat){
                    $pricing_categories[$key] = trim($cat);
                }
                if($data_clip['license'] == 1){
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_rf_delivery_options')->result_array();
                }
                else{
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_delivery_options')->result_array();
                }
                foreach($delivery_formats as $format){
                    if($format['categories']){
                        $categories = explode(' ', $format['categories']);
                        foreach($pricing_categories as $pricing_cat){
                            if(in_array($pricing_cat, $categories)){
                                $this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $id, 'format_id' => $format['id']));
                            }
                        }
                    }
                }
            }

            $this->create_sitemap();
            return $id;
        }
        else {
            $data_clip['ctime'] = date('Y-m-d H:i:s');

            $this->db_master->insert('lib_clips', $data_clip);

            $data_content['clip_id'] = $this->db_master->insert_id();
            $this->db_master->insert('lib_clips_content', $data_content);

            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $data_content['clip_id']));
            if($this->input->post('delivery_formats')){
                $delivery_formats = explode(',', $this->input->post('delivery_formats'));
                foreach($delivery_formats as $key => $format){
                    $delivery_formats[$key] = trim($format);
                }
                $query = $this->db->query('SELECT id FROM lib_delivery_options WHERE code IN (\'' . implode('\',\'', $delivery_formats) .'\')');
                $delivery_formats_ids = $query->result_array();
                if(count($delivery_formats_ids)){
                    foreach($delivery_formats_ids as $format_id){
                        $this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $data_content['clip_id'], 'format_id' => $format_id['id']));
                    }
                }
            }

            $this->create_sitemap();

            return $data_content['clip_id'];
        }
    }

	function get_code($id) {
		$query = $this->db->get_where('lib_clips', array('id' => $id));
		$row = $query->result_array();
		return $row[0]['code'];
	}

	function get_user_folder($id) {
		$query = $this->db->query('select lu.login from lib_users as lu, lib_clips as lc where lc.client_id=lu.id and lc.id=' . $id);
		$row = $query->result_array();
		return $row[0]['login'];
	}

	function get_license($id) {
        $this->db->select('license');
		$query = $this->db->get_where('lib_clips', array('id' => $id));
		$row = $query->result_array();
		return $row[0]['license'];
	}

    function get_duration($id) {
        $this->db->select('duration');
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $row = $query->result_array();
        return $row[0]['duration'];
    }

	function set_clip_res($id, $filetype, $type = 2, $location = '') {

		$exts_filter = '';
		switch ($type) {
			case 0:
				$exts_filter = in_array($filetype, $this->motion_types) ?
					" AND resource IN ('" . implode("','", $this->motion_types) . "') " :
					" AND resource IN ('" . implode("','", $this->img_types) . "') ";
				break;
		}

		$query = $this->db->query('SELECT id FROM lib_clips_res WHERE clip_id = ? AND type = ? '
			. $exts_filter, array($id, $type));
		$row = $query->result_array();

		if (count($row)) {
			$this->db_master->update('lib_clips_res', array('resource' => $filetype, 'location' => $location), array('id' => $row[0]['id']));
		} else {
			$this->db_master->insert('lib_clips_res', array('clip_id' => $id, 'resource' => $filetype, 'type' => $type, 'location' => $location));
		}
	}

	function get_res_filter($res_type) {
		$filter = array('type' => 3, 'resource_in' => '');

		switch ($res_type) {
			case 'hd':
				$filter['type'] = 2;
				$filter['resource_in'] = "'mov', 'mp4', 'avi', 'r3d'";
				break;
			case 'thumb':
				$filter['type'] = 0;
				$filter['resource_in'] = "'mp4'";
				break;
			case 'preview':
				$filter['type'] = 1;
				$filter['resource_in'] = "'mov', 'mp4'";
				break;
            case 'img':
                $filter['type'] = 0;
                $filter['resource_in'] = "'jpg', 'jpeg', 'gif', 'png'";
                break;
		}
		return $filter;
	}

	function unreg_resource($id, $res_type) {
		$filter = $this->get_res_filter($res_type);

        /// For symlinks
        $query = $this->db->query('SELECT * FROM lib_clips_res WHERE clip_id = ? AND type = ? AND resource IN ('
            . $filter['resource_in'] . ')', array($id, $filter['type']));

        $rows = $query->result_array();
        $types_dirs = array(
            0 => 'thumb',
            1 => 'preview',
            2 => 'res'
        );
        if ($rows) {
            foreach ($rows as $res) {
                $file = $this->config->item('clip_dir') . $types_dirs[$res['type']] . '/' .
                    $id . '.' . $res['resource'];
                if (is_link($file)) {
                    unlink($file);
                }

                //Delete frames count
                if($res['type'] == 1){
                    $this->db_master->update('lib_clips', array('frames_count' => 0), array('id' => $id));
                }
            }
        }
        /////

		$query = $this->db_master->query('DELETE FROM lib_clips_res WHERE clip_id = ? AND type = ? AND resource IN ('
			. $filter['resource_in'] . ')', array($id, $filter['type']));
	}


    function metadata1($id, $file) {

        ob_start();
        $command = '/usr/bin/mediainfo -f --Output=XML "' . $file . '" 2>&1';
        if (PATH_SEPARATOR == ';') {
            $command = str_replace("\\", '/', $command);
        }
        passthru($command);
        $data = ob_get_contents();
        ob_end_clean();

        $xml = simplexml_load_string($data);

        $clip_data = array();
        $clip_content_data = array();

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Title');
        $clip_content_data['title'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Performer');
        $clip_content_data['creator'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Copyright');
        $clip_content_data['rights'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Title_More');
        $clip_content_data['description'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Keywords');
        $clip_content_data['keywords'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Duration');
        $clip_data['duration'] = $val[0]/1000;

        //$val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Recorded_date');
        //$clip_data['creation_date'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Encoded_date');
        preg_match('/[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])/', $val[0], $matches);
        if($matches[0]){
            $clip_data['creation_date'] = $matches[0];
        }

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Width');
        $clip_data['width'] = (int)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Height');
        $clip_data['height'] = (int)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Display_aspect_ratio');
        $clip_data['aspect'] = (string)$val[1];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Frame_rate');
        $clip_data['frame_rate'] = (float)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Bit_rate');
        $bit_rate = (int)$val[0];
        $clip_data['bit_rate'] = $bit_rate;

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Bit_rate_mode');
        $clip_data['bit_rate_mode'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Format');
        $clip_data['codec'] = (string)$val[0];
        if ($clip_data['codec'] == 'ProRes') {
            $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Chroma_subsampling');
            $Chroma_subsampling = (string)$val[0];
            $clip_data['codec'] .= ' ' . str_replace(':', '', $Chroma_subsampling);

            if ($Chroma_subsampling == '4:2:2') {
                if ($bit_rate > 175000000) {
                    $clip_data['codec'] .= ' HQ';
                }
            }
        }
        elseif (!empty($this->codecs[$clip_data['codec']])) {
            $clip_data['codec'] = $this->codecs[$clip_data['codec']];
        }

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Format');
        $clip_data['format'] = (string)$val[0];


        $fields = array_keys($clip_data);
        foreach ($fields as $field) {
            if (empty($clip_data[$field])) {
                unset($clip_data[$field]);
            }
        }

        $fields = array_keys($clip_content_data);
        foreach ($fields as $field) {
            if (empty($clip_content_data[$field])) {
                unset($clip_content_data[$field]);
            }
        }

        if(!empty($clip_data))
            $this->db_master->update('lib_clips', $clip_data, array('id' => $id));
        if(!empty($clip_content_data))
            $this->db_master->update('lib_clips_content', $clip_content_data, array('clip_id' => $id, 'lang' => 'en'));

        return $clip_data;
    }

    function parse_metadata($str) {
        $lines = explode("\n", $str);
        $data = array();
        $key = '';
        foreach ($lines as &$line) {
            $line = trim($line, " \r\n");
            if ((substr($line, 0, 7) == '[STREAM') || ($line == '[FORMAT]')) {
                $key = trim($line, '[]');
                continue;
            }
            $pair = explode('=', $line);
            $data[$key][$pair[0]] = $pair[1];
        }
        return $data;
    }

    function metadata($id, $src) {
        ob_start();
        $command = '/usr/local/bin/ffprobe -show_format -show_streams "' . $src . '" 2>&1';
        if (PATH_SEPARATOR == ';') {
            $command = str_replace("\\", '/', $command);
        }
        passthru($command);
        $data = ob_get_contents();
        ob_end_clean();

        $data = substr($data, strpos($data, '[STREAM]'), strpos($data, '[/FORMAT]'));
        $data = str_replace('[/STREAM]', '', $data);
        $i = 0;
        $replaced = 0;
        do {
            $data = preg_replace('/\[STREAM\]/', '[STREAM' . $i++ . ']', $data, 1, $replaced);
        } while ($replaced);
        $metadata = $this->parse_metadata($data);

        $duration = $metadata['FORMAT']['duration'];
        $bitrate = $metadata['FORMAT']['bit_rate'];
        $format = $metadata['FORMAT']['format_name'];

        $i = 0;
        $video_stream = -1;
        while (($video_stream == -1) && !empty($metadata['STREAM' . $i])) {
            if ($metadata['STREAM' . $i]['codec_type'] == 'video') {
                $video_stream = $i;
            }
            ++$i;
        }

        if ($video_stream != -1) {
            $width = $metadata['STREAM' . $video_stream]['width'];
            $height = $metadata['STREAM' . $video_stream]['height'];
            $codec = $metadata['STREAM' . $video_stream]['codec_name'];
            $display_aspect_ratio = $metadata['STREAM' . $video_stream]['display_aspect_ratio'];
            if (!empty($display_aspect_ratio) && strpos($display_aspect_ratio, ':')) {
                $aspect = $display_aspect_ratio;
            }
            list($numerator, $denominator) = explode('/', $metadata['STREAM' . $video_stream]['avg_frame_rate']);
            $numerator = intval($numerator);
            $denominator = intval($denominator);
            if ($numerator && $denominator) {
                $frame_rate = number_format($numerator / $denominator, 2, '.', '');
                if (substr($frame_rate, -3) == '.00') {
                    $frame_rate = substr($frame_rate, 0, -3);
                }
            }
        }

        $data = array();
        if (!empty($format)) {
            $data['format'] = $format;
        }
        if (!empty($duration)) {
            $data['duration'] = $duration;
        }
        if (isset($aspect) && !empty($aspect)) {
            $data['aspect'] = $aspect;
        }
        if (!empty($bitrate)) {
            $data['bit_rate'] = $bitrate;
        }
        if (!empty($frame_rate)) {
            $data['frame_rate'] = $frame_rate;
        }
        if (!empty($width)) {
            $data['width'] = $width;
        }
        if (!empty($height)) {
            $data['height'] = $height;
        }
        if (!empty($codec)) {
            $data['codec'] = $codec;
        }

        if(!empty($data))
            $this->db_master->update('lib_clips', $data, array('id' => $id));
    }

	function create_thumb($id, $clip_data = null) {
		$width = 200;
		$height = 112;

		if (empty($clip_data['duration'])) {
			$ss = 0;
		} elseif ($clip_data['duration'] >= 10) {
			$ss = 10;
		} else {
			$ss = number_format($clip_data['duration'] / 2, 2, '.', '');
		}

		if (!empty($clip_data['aspect'])) {
			if (strpos($clip_data['aspect'], ':')) {
				$aspects = explode(':', $clip_data['aspect']);
				if (!empty($aspects[1])) {
					$aspect = floatval($aspects[0]) / floatval($aspects[1]);
				}
			} else {
				$aspect = floatval($clip_data['aspect']);
			}

			if ($aspect) {
				$height = intval($width / $aspect);
				if ($height % 2) {
					++$height;
				}
			}
		}

		$src_type = $this->db->query(
				'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = 2', $id)->result_array();
		$src_type = $src_type[0]['resource'];
		if (!$src_type) {
			return 'Delivery file is not registered.';
		}

		$dir = $this->config->item('clip_dir');

        if (strcasecmp($src_type, 'r3d') == 0) {
            $src = $this->config->item('converted_clips') . $id . '.mov';
        }
        else {
            $src = $dir . 'res/' . $id . '.' . $src_type;
        }


		if (!is_file($src)) {
			return 'Error: HD file not found.';
		}
		$dest = $dir . 'thumb/' . $id . '.jpg';
        if(is_file($dest)){
            unlink($dest);
        }

		$command = '/usr/local/bin/ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
			. ' -s ' . $width . 'x' . $height . ' -y ' . $dest;

        //For Windows
        /*$command = 'C:\ffmpeg\bin\ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
              . ' -s ' . $width . 'x' . $height . ' -y ' . $dest;*/

		if (DIRECTORY_SEPARATOR == '\\') {
			$command = str_replace('\\', '/', $command);
		}

		if (!is_file($dest) || !filesize($dest)) {
			$error = exec($command);
		}

		if (is_file($dest) && filesize($dest)) {
			$this->set_clip_res($id, 'jpg', 0);
		} else {
			return 'Error occured while executing ' . $command;
		}
	}

    function create_temp_thumb($id, $offset = 0) {
		$width = 200;
		$height = 112;

        $this->db->select('duration, aspect');
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $rows = $query->result_array();
        $clip_data = $rows[0];

        if($offset)
            $ss = $offset;
        else{
            if (empty($clip_data['duration'])) {
                $ss = 0;
            } elseif ($clip_data['duration'] >= 10) {
                $ss = 10;
            } else {
                $ss = number_format($clip_data['duration'] / 2, 2, '.', '');
            }
        }

		if (!empty($clip_data['aspect'])) {
			if (strpos($clip_data['aspect'], ':')) {
				$aspects = explode(':', $clip_data['aspect']);
				if (!empty($aspects[1])) {
					$aspect = floatval($aspects[0]) / floatval($aspects[1]);
				}
			} else {
				$aspect = floatval($clip_data['aspect']);
			}

			if ($aspect) {
				$height = intval($width / $aspect);
				if ($height % 2) {
					++$height;
				}
			}
		}

		$src_type = $this->db->query('SELECT resource, location FROM lib_clips_res WHERE clip_id = ? AND type = 1', $id)->result_array();
		if (!$src_type[0]['resource']) {
			return false;
		}
        //$store = array();
        //require(__DIR__ . '/../config/store.php');
		$dir = $this->config->item('clip_dir');

        if (strcasecmp($src_type, 'r3d') == 0) {
            $src = $this->config->item('converted_clips') . $id . '.mov';
        }
        else {
            $src = $dir . 'res/' . $id . '.' . $src_type;
        }


		if (!is_file($src)) {
			return false;
		}
		$dest = $dir . 'temp_thumb/' . $id . '.jpg';
        if(is_file($dest)){
            unlink($dest);
        }

		$command = '/usr/bin/ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
			. ' -s ' . $width . 'x' . $height . ' -y ' . $dest;

		if (DIRECTORY_SEPARATOR == '\\') {
			$command = str_replace('\\', '/', $command);
		}

        exec($command);

		if (is_file($dest) && filesize($dest)) {
			return true;
		} else {
			return false;
		}
	}

    function set_temp_thumb($id){
        $dir = $this->config->item('clip_dir');
        $temp_file = $dir . 'temp_thumb/' . $id . '.jpg';
        $dest = $dir . 'thumb/' . $id . '.jpg';
        if(is_file($temp_file) && filesize($temp_file)){
            if(copy($temp_file, $dest)){
                unlink($temp_file);
                $this->set_clip_res($id, 'jpg', 0);
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    #-----------------------------------------------------------------------------

    function faststart($file) {
        $temp = str_replace('.mp4', '_.mp4', $file);
        exec('/usr/local/bin/qt-faststart ' . $file . ' ' . $temp);
        if (is_file($temp) && (filesize($temp) > 0)) {
            unlink($file);
            rename($temp, $file);
        }
    }

    #-----------------------------------------------------------------------------

    function create_resource($id, $res_type, $clip_data = null) {
        $subdir = '';
        $ext = '';
        $resolution = '';
        $type = 3;
        $dir = $this->config->item('clip_dir');

        switch ($res_type) {
            /*case 'thumb':
                $subdir = 'thumb/';
                $ext = 'jpg';
                $width = 192;
                $height = 108;
                if (empty($clip_data['duration'])) {
                    $ss = 0;
                } elseif ($clip_data['duration'] >= 10) {
                    $ss = 10;
                } else {
                    $ss = number_format($clip_data['duration'] / 2, 2, '.', '');
                }
                $type = 0;
                break;*/
            case 'thumb':
                $subdir = 'thumb/';
                $ext = 'mp4';
                $width = 200;
                $height = 112;
                $type = 0;
                $vb = '192k';
                break;
            case 'preview':
                $subdir = 'preview/';
                $ext = 'mp4';
                $width = 512;
                $height = 288;
                $type = 1;
                $vb = '1024k';
                $this->delete_frames($id);
                break;
        }

        if (!empty($clip_data['aspect'])) {
            if (strpos($clip_data['aspect'], ':')) {
                $aspects = explode(':', $clip_data['aspect']);
                if (!empty($aspects[1])) {
                    $aspect = floatval($aspects[0]) / floatval($aspects[1]);
                }
            }
            else {
                $aspect = floatval($clip_data['aspect']);
            }

            if ($aspect) {
                $height = intval($width / $aspect);
                if ($height % 2) {
                    ++$height;
                }
            }
        }

        if (!$subdir) {
            return 'Error: unknown type of resource.';
        }

        //File type of HD-file
        $src_type = $this->db->query(
            'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = 2',
            $id)->result_array();
        $src_type = $src_type[0]['resource'];
        if (!$src_type) {
            return 'Delivery file is not registered.';
        }

        $src = $dir . 'res/' . $id . '.' . $src_type;
        if (!is_file($src)) {
            return 'Error: HD file not found.';
        }
        $dest = $dir . $subdir . $id . '.' . $ext;
        if(is_file($dest)){
            unlink($dest);
        }

        /*$command = ($res_type == 'thumb') ?
            '/usr/local/bin/ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
                . ' -s ' . $width . 'x' . $height . ' -y ' . $dest :
            '/usr/local/bin/ffmpeg -i "' . $src . '" -y -vcodec libx264 -s '
                . $width . 'x' . $height . ' -vb 768k ' . '"' . $dest . '"';*/

        $command = '/usr/local/bin/ffmpeg -i "' . $src .
            '" -vcodec libx264 -pix_fmt yuv420p -vb ' . $vb .
            ' -s ' . $width . 'x' . $height .
            ' -acodec aac -strict experimental -y "' . $dest . '"';

        // For Windows
        /*$command = 'C:\ffmpeg\bin\ffmpeg -i "' . $src .
            '" -vcodec libx264 -pix_fmt yuv420p -vb ' . $vb .
            ' -s ' . $width . 'x' . $height .
            ' -acodec aac -strict experimental -y "' . $dest . '"';*/

        if (DIRECTORY_SEPARATOR == '\\') {
            $command = str_replace('\\', '/', $command);
        }

        if (!is_file($dest) || !filesize($dest)) {
            $error = exec($command);
        }

        if ($ext == 'mp4') {
            $this->faststart($dest);
        }

        if (is_file($dest) && filesize($dest)) {
            $this->set_clip_res($id, $ext, $type);
        } else {
            return 'Error occured while executing ' . $command;
        }
    }

    #------------------------------------------------------------------------------------------------

	function delete_resource($id, $type = 'thumb') {

		$query = $this->db->get_where('lib_clips_res', array('clip_id' => $id,
			'type' => $this->res_type[$type]));
		$rows = $query->result_array();

		if ($rows) {
			foreach ($rows as $res) {
				$file = $this->config->item('clip_dir') . $type . '/' .
					$id . '.' . $res['resource'];
				if (is_file($file)) {
					unlink($file);
				}
				$this->db_master->delete('lib_clips_res', array('id' => $res['id']));
			}
		}
	}

	function get_clip_path($id, $mode = 'thumb') {
		if (is_array($id)) {
			$id = intval($id['id']);
		}

		switch ($mode) {
			case 'thumb':
				$type = 0;
                $this->db->where_in('resource', $this->img_types);
                $dir = 'thumb';
				break;
			case 'motion_thumb':
				$type = 0;
                $dir = 'thumb';
                $this->db->where_in('resource', $this->motion_types);
				break;
			case 'preview':
				$type = 1;
                $dir = 'preview';
				break;
			case 'res':
				$type = 2;
                $dir = 'res';
				break;
			default: $type = 0;
		}

		$query = $this->db->get_where('lib_clips_res', array('clip_id' => $id, 'type' => $this->res_type[$mode]));
		$row = $query->result_array();

		if ($row) {
			$path = '/' . $this->config->item('clip_path') . $dir . '/' . $id . '.'
				. $row[0]['resource'];
			if ($mode == 'thumb') {
				$path .= '?m=' . strftime('%Y%m%d%H%M', strtotime($row[0]['ctime']));
			}

            if($row[0]['location']){
                $location_info = parse_url($row[0]['location']);
                if(isset($location_info['scheme'])){
                    switch ($location_info['scheme']) {
                        case 's3':
                            $s3_host = $this->config->item('s3_host');
                            if($s3_host) {
                                if($mode == 'preview') {
                                    $store = array();
                                    require(__DIR__ . '/../config/store.php');
                                    $s3Client = S3Client::factory(array(
                                        'key'    => $store['s3']['key'],
                                        'secret' => $store['s3']['secret']
                                    ));
                                    $path = $s3Client->getObjectUrl($location_info['host'], $location_info['path'], '+15 minutes');
                                }
                                else {
                                    $path = 'http://' . $s3_host . '/' . $location_info['host'] . $location_info['path'];
                                }
                            }
                            break;
                    }
                }
            }


			return $path;
		}
	}

	function get_clip_res($id, $type = 0) {
		$query = $this->db->get_where('lib_clips_res', array('clip_id' => $id, 'type' => $type));
		$rows = $query->result_array();

		return $rows;
	}

	function get_resources_count($res_type) {
		$filter = $this->get_res_filter($res_type);

		$query = $this->db->query(
			"SELECT COUNT(cr.id) total
      FROM lib_clips_res cr
      INNER JOIN lib_clips c ON c.id = cr.clip_id
      WHERE cr.type = ? AND cr.resource IN ( "
			. $filter['resource_in'] . ")", $filter['type']);

		$row = $query->result_array();

		return $row[0]['total'];
	}

    function get_resources($id) {
        $query = $this->db->query('SELECT resource, type FROM lib_clips_res WHERE clip_id=?', $id);
        $rows = $query->result_array();

        $resources = array(
            'hd'=>array('Delivery', null),
            'img'=>array('Image thumbnail', null),
            'thumb'=>array('Video thumbnail', null),
            'preview'=>array('Preview', null)
        );

        if (count($rows)) {
            foreach ($rows as $row) {
                switch ($row['type']) {
                    case 0:
                        if (($row['resource'] == 'flv') || ($row['resource'] == 'mp4') || ($row['resource'] == 'mov')) {
                            $resources['thumb'][1] = $row['resource'];
                        } else {
                            $resources['img'][1] = $row['resource'];
                        }
                        break;
                    case 1:
                        $resources['preview'][1] = $row['resource'];
                        break;
                    case 2:
                        $resources['hd'][1] = $row['resource'];
                        break;
                }
            }
        }

        return $resources;
    }

    function get_clip_thumbs($data) {
        $path = $this->config->item('clip_path');

        $query = $this->db->query(
            "SELECT * FROM lib_clips_res WHERE clip_id=? AND type=0 AND resource IN('"
            . implode("','", $this->img_types) . "')", array($data['id']));
        $rows = $query->result_array();

        if (count($rows)) {
            $file = $path . $data['folder'] . '/thumb/' . $data['code'] . '.' . $rows[0]['resource'];
        } else {
            $file = $path . 'no_image.gif';
        }

        return $file;
    }

	function get_clip_thumb($data) {
		$path = $this->config->item('clip_path');

		$query = $this->db->query(
			"SELECT * FROM lib_clips_res WHERE clip_id=? AND type=0 AND resource IN('"
			. implode("','", $this->img_types) . "')", array($data['id']));
		$rows = $query->result_array();

		if (count($rows)) {
			$file = $path . $data['folder'] . '/thumb/' . $data['code'] . '.' . $rows[0]['resource'];
		} else {
			$file = $path . 'no_image.gif';
		}

		return $file;
	}

	function get_img($id) {
		$data = $this->db->query("SELECT c.code, u.login, r.resource
      FROM lib_clips c
      INNER JOIN lib_users u ON u.id = c.client_id
      INNER JOIN lib_clips_res r ON r.clip_id = c.id AND r.type = 0 AND r.resource IN ('"
				. implode("','", $this->img_types) . "')
      WHERE c.id = ?", $id)->result();
		$data = $data[0];
		if ($data) {
			return $this->config->item('clip_path') . $data->login . '/thumb/'
				. $data->code . '.' . $data->resource;
		}
	}

	function search($lang, $filter, $limit) {
		$data['all'] = $this->get_clips_count($lang, $filter);
		$data['results'] = $this->get_clips_list($lang, $filter, $limit);

		return $data;
	}

	function get_linked_keywords($lang, $keywords) {
		$words = explode(',', $keywords);
		$ext = $this->config->item('url_suffix');
		shuffle($words);

		foreach ($words as $k => $v) {
			$v = trim($v);
			$temp[$k] = '<a href="' . $lang . '/search/words/' . urlencode($v) . $ext . '">' . $v . '</a>';
			if ($k > 30)
				break;
		}

		return implode(' ', $temp);
	}

	function get_sale_count($id) {
		$row = $this->db->query(
				'SELECT COUNT(1) total
    FROM lib_orders_items oi
    INNER JOIN lib_orders o ON o.id = oi.order_id AND o.status = 3
    WHERE oi.item_id = ? AND oi.item_type = 2', array($id))->result_array();
		return $row[0]['total'];
	}

	function get_thumb_by_code($code) {
		$data = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', array($code))->result();

		if (empty($data)) {
			return NULL;
		}

		$id = $data[0]->id;
		$thumb = $this->get_clip_path($id, 'thumb');

		return $thumb;
	}

	function create_clip($file, $submission_code = '', $user_id = 0) {
        $this->load->model('submissions_model');
        $submission = false;
        if($submission_code){
            $submission_id = $this->submissions_model->create_submission($submission_code, $user_id);
            $submission = $this->submissions_model->get_submission($submission_id);
        }
		$hd_type = $this->api->get_file_ext($file);
		//$clip_code = basename($file, '.' . $hd_type);
		if($submission){
            $clips_count = $this->get_clips_count_by_submission($submission['id']);
            $clip_code = $submission['code'] . '_' . str_pad($clips_count + 1, 4, 0, STR_PAD_LEFT);
        }
        else{
            $clips_count = $this->get_clips_count();
            $clip_code = str_pad($clips_count + 1, 4, 0, STR_PAD_LEFT);
        }

		$clip = array(
			'code' => $clip_code,
			'ctime' => date('Y-m-d H:i:s'),
            'client_id' => $user_id,
            'submission_id' => $submission ? $submission['id'] : 0,
            //'original_filename' => str_replace('tmp_', '', basename($file))
            'original_filename' => basename($file)
		);

		$this->db_master->insert('lib_clips', $clip);
		$clip_id = $this->db_master->insert_id();
		//$filename = str_replace($clip_code, $clip_id, $file);
		//$filename = str_replace(basename($file, '.' . $hd_type), $clip_id, $file);
        if(strtolower($hd_type) != 'r3d'){
            $filename = str_replace(basename($file, '.' . $hd_type), $clip_code, $file);
            rename($file, $filename);
        }
        else
            $filename = $file;

		$this->set_clip_res($clip_id, $hd_type, 2, $filename);

		$clip_content = array(
			'clip_id' => $clip_id,
			'lang' => 'en',
			'title' => $clip_code
		);
		$this->db_master->insert('lib_clips_content', $clip_content);

		//$clip_data = $this->metadata($clip_id, $filename);
		//$this->create_thumb($clip_id, $clip_data);

        return $clip_id;
	}

	function is_code_exists($filename) {
		$code = substr($filename, 0, strrpos($filename, '.'));
		$row = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', $code)
			->result();
		return !empty($row);
	}

	function get_frame_rate_list() {
		$query = $this->db->
				distinct()->
				select('frame_rate')->
				from('lib_clips')->
				order_by('frame_rate')->get();

		$rows = $query->result();
		$result = array();

		if ($rows) {
			foreach ($rows as $row) {
				$result[] = $row->frame_rate;
			}
		}

		return $result;
	}

	function get_cats_clip($id, $lang = 'en') {
		$query = $this->db->query('SELECT c.id, c.parent_id, cc.title, clc.clip_id checked
		FROM lib_cats c
		LEFT JOIN lib_cats_content cc ON c.id=cc.cat_id AND cc.lang=?
		LEFT JOIN lib_clips_cats clc ON c.id=clc.cat_id AND clc.clip_id=?
		WHERE c.id > 0
		ORDER BY c.parent_id, c.ord', array($lang, $id));
		$rows = $query->result_array();
		$data['total'] = $query->num_rows();

		foreach ($rows as $row) {
			$row['title'] = ($row['title']) ? $row['title'] : '-';

			if ($row['parent_id'])
				$data['cats'][$row['parent_id']]['child'][] = $row;
			else
				$data['cats'][$row['id']] = $row;
		}
		return $data;
	}



    function get_clip_sequences($id, $lang = 'en') {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if($group['is_editor'] && $uid){
            $filter = ' WHERE s.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT s.*, cs.clip_id checked FROM lib_sequences s
            LEFT JOIN lib_clip_sequences cs ON s.id = cs.sequence_id AND cs.clip_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_bins($id, $lang = 'en') {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if($group['is_editor'] && $uid){
            $filter = ' WHERE b.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT b.*, cb.clip_id checked FROM lib_bins b
            LEFT JOIN lib_clip_bins cb ON b.id = cb.bin_id AND cb.clip_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_galleries($id, $lang = 'en') {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if($group['is_editor'] && $uid){
            $filter = ' WHERE g.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT g.*, cg.clip_id checked FROM lib_galleries g
            LEFT JOIN lib_clip_galleries cg ON g.id = cg.gallery_id AND cg.clip_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_submissions($id, $lang = 'en') {
        $query = $this->db->query('SELECT s.*, c.id checked FROM lib_submissions s
            LEFT JOIN lib_clips c ON s.id = c.submission_id AND c.id = ?', array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_clipbins($id, $lang = 'en') {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if($group['is_editor'] && $uid){
            $filter = ' WHERE lb.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT lb.*, lbi.item_id checked FROM lib_lb lb
            LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id AND lbi.item_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

	function save_cats($id, $ids) {
		$this->db_master->delete('lib_clips_cats', array('clip_id' => $id));

        foreach ((array) $ids as $cat_id) {
            if($cat_id){
                $data['clip_id'] = $id;
                $data['cat_id'] = $cat_id;

                $this->db_master->insert('lib_clips_cats', $data);
            }
        }
	}

    function save_sequences($id, $ids) {
        $this->db_master->delete('lib_clip_sequences', array('clip_id' => $id));

        foreach ((array) $ids as $item_id) {
            if($item_id){
                $data['clip_id'] = $id;
                $data['sequence_id'] = $item_id;
                $this->db_master->insert('lib_clip_sequences', $data);
            }
        }
    }

    function save_bins($id, $ids) {
        $this->db_master->delete('lib_clip_bins', array('clip_id' => $id));

        foreach ((array) $ids as $item_id) {
            if($item_id){
                $data['clip_id'] = $id;
                $data['bin_id'] = $item_id;
                $this->db_master->insert('lib_clip_bins', $data);
            }
        }
    }

    function save_galleries($id, $ids) {
        $this->db_master->delete('lib_clip_galleries', array('clip_id' => $id));

        foreach ((array) $ids as $item_id) {
            if($item_id){
                $data['clip_id'] = $id;
                $data['gallery_id'] = $item_id;
                $this->db_master->insert('lib_clip_galleries', $data);
            }
        }
    }

    function save_clipbins($id, $ids) {
        $this->db_master->delete('lib_lb_items', array('item_id' => $id));

        foreach ((array) $ids as $item_id) {
            if($item_id){
                $data['item_id'] = $id;
                $data['lb_id'] = $item_id;
                $this->db_master->insert('lib_lb_items', $data);
            }
        }
    }

    #-----------------------------------------------------------------------------

    function upload_attachment($id) {

        $exts = array('jpg', 'jpeg', 'gif', 'png', 'pdf');

        if(is_uploaded_file($_FILES['attachment']['tmp_name'])) {
            $dest_dir = $this->config->item('attachments_dir');
            $ext = strtolower($this->api->get_file_ext($_FILES['attachment']['name']));

            if(in_array($ext, $exts)) {
                $source = $_FILES['attachment']['tmp_name'];
                $attachment_id = 1;
                $dest = $dest_dir . $id . '-' . $attachment_id . '.' . $ext;
                while(is_file($dest)){
                    $attachment_id++;
                    $dest = $dest_dir . $id . '-' . $attachment_id . '.' . $ext;
                }
                if (!@copy($source, $dest)) {
                    return "Can't copy the file.";
                }

                $filename = $id . '-' . $attachment_id . '.' . $ext;

                $query = $this->db->get_where('lib_clips_attachments', array('clip_id' => $id, 'file' => $filename));
                $row = $query->result_array();

                $data['clip_id'] = $id;
                $data['file'] = $filename;

                if(count($row)) {
                    $this->db_master->where('id', $row[0]['id']);
                    $this->db_master->update('lib_clips_attachments', $data);
                }
                else
                    $this->db_master->insert('lib_clips_attachments', $data);
            } else {
                return 'Wrong file type, must be one of: ' . implode(', ', $exts) . '.';
            }
        } else {
            return 'File not selected.';
        }
    }

    function get_attachments($id) {
        $query = $this->db->query('SELECT id, file FROM lib_clips_attachments WHERE clip_id=?', $id);
        $attachments = $query->result_array();
        $images_exts = array('jpg', 'jpeg', 'gif', 'png');

        if (count($attachments)) {
            foreach ($attachments as $key => $attachment) {
                $ext = $this->api->get_file_ext($attachment['file']);
                $attachments[$key]['filetype'] = $ext;
                $attachments[$key]['filepath'] = $this->config->item('attachments_path') . $attachment['file'];
                if(in_array($ext, $images_exts)){
                    $attachments[$key]['is_image'] = 1;
                }
            }
        }

        return $attachments;
    }

    #------------------------------------------------------------------------------------------------

    function delete_attachment($id) {

        $query = $this->db->get_where('lib_clips_attachments', array('id' => $id));
        $rows = $query->result_array();

        if ($rows) {
            foreach ($rows as $res) {
                $file = $this->config->item('attachments_dir') . $res['file'];
                if (is_file($file)) {
                    unlink($file);
                }
                $this->db_master->delete('lib_clips_attachments', array('id' => $res['id']));
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_clip_category($id, $lang='en') {
        $row = $this->db->query(
            'SELECT cc.title
      FROM lib_cats_content cc
      INNER JOIN lib_cats c ON c.id = cc.cat_id
      INNER JOIN lib_clips_cats clc ON clc.cat_id = cc.cat_id AND clc.clip_id = ?
      WHERE cc.lang = ?
      LIMIT 1', array($id, $lang))
            ->result_array();
        return $row[0]['title'];
    }

    function get_clip_public_category($id, $lang='en') {
        $row = $this->db->query(
            'SELECT c.id
      FROM lib_cats_content cc
      INNER JOIN lib_cats c ON c.id = cc.cat_id
      INNER JOIN lib_clips_cats clc ON clc.cat_id = cc.cat_id AND clc.clip_id = ?
      WHERE cc.lang = ? AND c.private = 0 LIMIT 1', array($id, $lang))->result_array();
        return $row[0]['id'];
    }

    #------------------------------------------------------------------------------------------------

    function make_uri($item) {
        $uri = str_replace(array(',', ' '), '-', strtolower(trim($item['title'])));
        $uri = preg_replace('/[\-]+/', '-', $uri);
        $uri = preg_replace('/[^\w\-]+/', '', $uri);
        $uri = urlencode($uri);
        if (strpos($uri, '%')) {
            $uri = strtolower(str_replace('%', '', $uri));
        }
        $uri .= '-' . $item['id'];

        return $uri;
    }

    function update_clip_statistic($clip_id){
        $where['date'] = date('Y-m-d');
        $where['clip_id'] = (int)$clip_id;
        $uid = $this->session->userdata('client_uid') ? (int)$this->session->userdata('client_uid') : 0;
        $where['user_id'] = $uid;


        $query = $this->db->get_where('lib_clips_statistic', $where);

        $row = $query->result_array();

        if (count($row)) {
            $views_count = $row[0]['views_count'] + 1;
            $this->db_master->update('lib_clips_statistic', array('views_count' => $views_count), array('id' => $row[0]['id']));
        }
        else {
            $data = array(
                'clip_id' => $clip_id,
                'user_id' => $uid,
                'views_count' => 1,
                'date' => date('Y-m-d')
            );
            $this->db_master->insert('lib_clips_statistic', $data);
        }
    }

    function get_clip_statistic($clip_id = null, $filter = null, $lang = 'en'){
        $clip_filter = '';
        if($clip_id){
            $clip_filter = 'clip_id = ' . $clip_id;
            if($filter){
                $filter .= ' AND ' . $clip_filter;
            }
            else{
                $filter = $clip_filter;
            }
        }
        $where = $filter ? ' WHERE ' . $filter : '';
	    $result = $this->db->query( "SELECT *, ( SELECT name FROM lib_extra_statistic_actions AS act WHERE act.type = stat.action_type ) AS 'action' FROM lib_clips_extra_statistic AS stat {$where}", array( $clip_id ) );
        return ( is_object( $result ) ) ? $result->result_array() : array();
    }

    #-----------------------------------------------------------------------------

    function specify_resource_location($id, $res_type, $location) {
        $exts = array();
        $subdir = '';
        $dir = $this->config->item('clip_dir');
        $location = $_SERVER['DOCUMENT_ROOT'] . $location;

        if (!is_file($location)) {
            return 'Error: File not found.';
        }

        switch ($res_type) {
            case 'hd':
                $subdir = 'res/';
                $res_type_name = 'res';
                $type = 2;
                $exts = array('mov', 'mp4', 'mxf');
                break;
            /*case 'preview':
                $subdir = 'preview/';
                $ext = 'mp4';
                $width = 512;
                $height = 288;
                $type = 1;
                $vb = '1024k';
                break;*/
        }

        if (!$subdir) {
            return 'Error: unknown type of resource.';
        }

        $ext = pathinfo($location, PATHINFO_EXTENSION);
        if(!in_array(strtolower($ext), $exts)){
            return 'Error: incorrect file type.';
        }

        $src_type = $this->db->query(
            'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = ?',
            array($id, $type))->result_array();
        $src_type = $src_type[0]['resource'];
        if ($src_type) {
            $src = $dir . 'res/' . $id . '.' . $src_type;
            if($src == $location){
                return 'Error: this location already specified.';
            }
            else{
                $this->delete_resource($id, $res_type_name);
            }
        }
        $dest = $dir . $subdir . $id . '.' . $ext;
        if (is_file($dest)) {
            unlink($dest);
        }
        symlink($location, $dest);

        if (is_file($dest)) {
            $this->set_clip_res($id, $ext, $type);
            $clip_data = $this->metadata($id, $dest);
            $this->create_thumb($id, $clip_data);
        } else {
            return 'Error occured while creating symlink';
        }
    }

    function get_clip_price($id, $start_time = null, $end_time = null){
        $row = $this->db->query('SELECT price, price_per_second, duration FROM lib_clips WHERE id = ? LIMIT 1', array((int)$id))->result_array();
        $price = false;
        if($row[0]['price'] != 0.00){
            if($start_time || $end_time){
                $start = $start_time ? $start_time : 0.00;
                $end = $end_time ? $end_time : $row[0]['duration'];
                $duration = round($end - $start, 2);
                if($duration == round($row[0]['duration'], 2)){
                    $price = $this->api->price_format($row[0]['price']);
                }
                else{
                    $price = $this->api->price_format($duration * $row[0]['price_per_second']);
                }
            }
            else{
                $price = $this->api->price_format($row[0]['price']);
            }
        }
        return $price;
    }

    public function add_to_index($clip_id){
        $index_data = $this->get_clips_index_data($clip_id);
        if(!$this->solr_adapter)
            $this->solr_adapter = new SorlSearchAdapter();
        if($index_data){
            $this->solr_adapter->addToIndex($index_data);
        }
    }

    public function delete_from_index($clip_id){
        if(!$this->solr_adapter)
            $this->solr_adapter = new SorlSearchAdapter();
        if(is_array($clip_id))
            $this->solr_adapter->deleteByMultipleIds($clip_id);
        else
            $this->solr_adapter->deleteById($clip_id);
    }


    public function get_clips_index_data($id = 0, $limit = array()){
        $keywords_sections_for_indexing = array(
            'shot_type',
            'subject_category',
            'actions',
            'appearance',
            'time',
            'location',
            'habitat',
            'concept'
        );
        if($id){
            if(is_array($id))
                $this->db->where_in('c.id', $id);
            elseif(is_integer($id))
                $this->db->where('c.id', $id);
        }
        if($limit){
            if(isset($limit['limit']) && isset($limit['offset']))
                $this->db->limit($limit['limit'], $limit['offset']);
            elseif(isset($limit['limit']))
                $this->db->limit($limit['limit']);
        }
        $this->db->select('c.id, c.code, c.active, c.client_id, c.collection, c.license, c.price_level, c.master_format,
        c.master_frame_size, c.source_format, c.source_frame_size, c.digital_file_frame_size, c.country, c.creation_date, c.duration, c.like_count, cc.title, cc.description, cc.keywords');
        $this->db->from('lib_clips c');
        $this->db->join('lib_clips_content cc', 'c.id = cc.clip_id AND cc.lang = \'en\'');
        $query = $this->db->get();
        $rows = $query->result_array();

        foreach($rows as $id => $row){
            $cats = $this->db->query('SELECT ctc.cat_id, ctc.title
            FROM lib_cats_content ctc
			INNER JOIN lib_clips_cats clc ON ctc.cat_id = clc.cat_id AND clc.clip_id = ' . $row['id'])->result_array();
            if($cats){
                foreach($cats as $cat){
                    $rows[$id]['category'][] = $cat['title'];
                    $rows[$id]['category_id'][] = $cat['cat_id'];
                }
            }

            $galleries = $this->db->query('SELECT g.id, g.title
            FROM lib_galleries g
			INNER JOIN lib_clip_galleries cg ON g.id = cg.gallery_id AND cg.clip_id = ' . $row['id'])->result_array();
            if($galleries){
                foreach($galleries as $gallery){
                    //$rows[$id]['category'][] = $cat['title'];
                    $rows[$id]['gallery_id'][] = $gallery['id'];
                }
            }

            $keywords = $this->get_clip_keywords($row['id']);
            if($keywords){
                foreach($keywords as $keyword){
                    if(in_array($keyword['section'], $keywords_sections_for_indexing)){
                        $rows[$id][$keyword['section']][] = $keyword['keyword'];
                    }
                }
            }


            $collections = $this->db->query('SELECT c.id
            FROM lib_collections c
			INNER JOIN lib_clips_collections cc ON c.id = cc.collection_id AND cc.clip_id = ' . $row['id'])->result_array();
            if($collections){
                foreach($collections as $collection){
                    $rows[$id]['collection_id'][] = $collection['id'];
                }
            }

            // Format categories
            if (stripos($row['source_format'], '3D') !== false) {
                $rows[$id]['format_category'][] = '3D';
            }
            if (stripos($row['source_frame_size'], 'Ultra HD') !== false
                || stripos($row['master_frame_size'], 'Ultra HD') !== false
                || stripos($row['digital_file_frame_size'], 'Ultra HD') !== false) {
                $rows[$id]['format_category'][] = 'Ultra HD';
            }
            if (stripos($row['source_frame_size'], 'HD') !== false
                || stripos($row['master_frame_size'], 'HD') !== false
                || stripos($row['digital_file_frame_size'], 'HD') !== false) {
                $rows[$id]['format_category'][] = 'HD';
            }
            if (stripos($row['source_frame_size'], 'SD') !== false
                || stripos($row['master_frame_size'], 'SD') !== false
                || stripos($row['digital_file_frame_size'], 'SD') !== false) {
                $rows[$id]['format_category'][] = 'SD';
            }
            unset($rows[$id]['source_frame_size'], $rows[$id]['master_frame_size'], $rows[$id]['digital_file_frame_size']);


            $creation_timestamp = strtotime($rows[$id]['creation_date']);
            $rows[$id]['creation_date'] = date('Y-m-d', $creation_timestamp) . 'T' . date('H:i:s', $creation_timestamp) . 'Z';
            $rows[$id]['duration'] = (int)$rows[$id]['duration'];
        }
        return $rows;
    }

    public function get_clips_by_ids($ids, $sort = array(), $lang = 'en'){

        if(!is_array($ids)){
            $ids = array($ids);
        }
        if($ids){
            $filter = ' WHERE c.id IN (' . implode(',', $ids) . ')';
        }
        $sort_str = '';
//        if($sort){
//            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
//        }
        $sort_str = ' ORDER BY FIELD(c.id,' . implode(',', $ids) . ') ';
        $query = $this->db->query('SELECT c.*, cc.title, cc.description, cc.location
			FROM lib_clips c
			INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang = ?' . $filter . $sort_str, $lang);
        $rows = $query->result_array();
        $base_url = $this->config->base_url();
        foreach ($rows as &$row) {
            $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
            $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
            $row['preview'] = $this->get_clip_path($row['id'], 'preview');
            $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
            $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];

            $source_format_display = array();
            if($row['source_format']){
                $source_format_display[] = $row['source_format'] . ($row['camera_chip_size'] ? ' (' . $row['camera_chip_size'] . ')' : '');
            }
            if($row['source_frame_size']){
                $source_format_display[] = $row['source_frame_size'];
            }
            if($row['source_frame_rate']){
                $source_format_display[] = $row['source_frame_rate'];
            }
            if($row['source_codec']){
                $source_format_display[] = $row['source_codec'];
            }
            if($row['bit_depth']){
                $source_format_display[] = $row['bit_depth'];
            }
            if($row['color_space']){
                $source_format_display[] = $row['color_space'];
            }
            if ($source_format_display)
                $row['source_format_display'] = implode(', ', $source_format_display);
        }

        return $rows;
    }

    public function get_cart_clips($ids, $lang = 'en'){
        $this->load->model('deliveryoptions_model');
        $rows = array();
        $filter = '';
            //$filter[] = 'c.client_id = ' . (int)$provider;
            if(!is_array($ids)){
                $ids = array($ids);
            }
            if($ids){
                $filter[] = 'c.id IN (' . implode(',', $ids) . ')';
            }
            if($filter){
                $filter = 'WHERE ' . implode(' AND ', $filter);
            }


            $query = $this->db->query('
                SELECT c.id, c.code, c.license, c.duration, c.digital_file_frame_rate, c.digital_file_format, c.color_system, c.aspect,
                cc.title, cc.description
                FROM lib_clips c
                INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang = ?' . $filter, $lang);
            $rows = $query->result_array();
            $base_url = $this->config->base_url();
            foreach ($rows as &$row) {
                $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
                $row['preview'] = $this->get_clip_path($row['id'], 'preview');
                $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
                $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];

                //Delivery formats
                //Methods

                //frontend delivery options fix
                //$delivery_methods = $rf_delivery_methods = $this->deliveryoptions_model->get_methods_list();
                $delivery_methods = $rf_delivery_methods = array(
                    array(
                        'id' => 666,
                        'code' => 'Formats Container',
                        'title' => 'Formats Container',
                        'delivery' => 'Download',
                    )
                );

                //For RF Clips
                if($row['license'] == 1){
                    $query = $this->db->query('
                    SELECT do.id, do.description, pf.factor price_factor FROM lib_rf_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
                    $delivery_formats = $query->result_array();
                    if(count($delivery_formats)){
                        foreach($rf_delivery_methods as $key => $method){
                            foreach($delivery_formats as $format){
                                $format['delivery'] = 'Download';
                                if($format['delivery'] == $method['delivery']){
                                    if(!isset($rf_delivery_methods[$key]['formats'])){
                                        $rf_delivery_methods[$key]['formats'] = array();
                                    }
//                                    if(strpos(strtolower($format['description']), 'digital file') === false){
//                                        $format['description'] .= ' ' . $row['digital_file_frame_rate'];
//                                    }
//                                    else{
//                                        $format['description'] = $row['digital_file_format'] . ' ' . $row['digital_file_frame_rate'];
//                                    }
                                    // New
                                    if(strpos(strtolower($format['description']), 'digital file') !== false){
                                        $format['description'] = implode(' ', array($row['digital_file_format'], $row['digital_file_frame_size'], $row['digital_file_frame_rate']));
                                    }
                                    elseif(strpos(strtolower($format['description']), 'master file') !== false){
                                        $format['description'] = implode(' ', array($row['master_format'], $row['master_frame_size'], $row['master_frame_rate']));
                                    }
                                    else{
                                        $format['description'] .= ' ' . $row['digital_file_frame_rate'];
                                    }

                                    $rf_delivery_methods[$key]['formats'][] = $format;
                                }
                            }
                        }
                        foreach($rf_delivery_methods as $key => $method){
                            if(!isset($method['formats'])){
                                unset($rf_delivery_methods[$key]);
                            }
                        }
                        $row['delivery_methods'] = $rf_delivery_methods;
                    }
                }
                //RM clips
                else{
                    $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, pf.factor price_factor
                    FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
                    $delivery_formats = $query->result_array();
                    if(count($delivery_formats)){
                        foreach($delivery_methods as $key => $method){
                            foreach($delivery_formats as $format){
                                $format['first_description'] = $format['description'];
                                if(true || $format['delivery'] == $method['delivery']){
                                    if(!isset($delivery_methods[$key]['formats'])){
                                        $delivery_methods[$key]['formats'] = array();
                                    }
                                    if(strpos(strtolower($format['description']), 'custom frame rate') === false){
//                                        if($format['format'] == 'SD'){
//                                            if($format['conversion'] == 1){
//                                                if($row['color_system'] == 'NTSC'){
//                                                    $row['color_system'] = 'PAL';
//                                                }
//                                                else{
//                                                    $row['color_system'] = 'NTSC';
//                                                }
//                                            }
//                                            $format['description'] .= ' ' . $row['color_system'] . ' ' . $row['aspect'];
//                                        }
//                                        elseif(($format['source'] == 'File' && $format['destination'] == 'Tape')
//                                            || ($format['source'] == 'File' && $format['destination'] == 'File')){
//                                            if(strpos(strtolower($format['description']), 'digital file') === false){
//                                                $format['description'] .= ' ' . $row['digital_file_frame_rate'];
//                                            }
//                                            else{
//                                                $format['description'] = $row['digital_file_format'] . ' ' . $row['digital_file_frame_rate'];
//                                            }
//                                        }
//                                        elseif(($format['source'] == 'Tape' && $format['destination'] == 'File')){
//                                            $format['description'] .= ' ' . $row['master_format'] . ' ' . $row['master_frame_rate'];
//                                        }
//                                        elseif(($format['source'] == 'Tape' && $format['destination'] == 'Tape')){
//                                            $format['description'] .= ' ' . $row['master_frame_rate'];
//                                        }

                                        // New algorithm
                                        if(strpos(strtolower($format['description']), 'digital file') !== false){
                                            $format['description'] = implode(' ', array($row['digital_file_format'], $row['digital_file_frame_size'], $row['digital_file_frame_rate']));
                                        }
                                        elseif(strpos(strtolower($format['description']), 'master file') !== false){
                                            $format['description'] = implode(' ', array($row['master_format'], $row['master_frame_size'], $row['master_frame_rate']));
                                        }
                                        else{
                                            if($format['source'] == 'Tape' && $format['destination'] == 'File'){
                                                $format['description'] .= ' ' . $row['master_frame_rate'];
                                            }
                                            elseif($format['source'] == 'Tape' && $format['destination'] == 'Tape'){
                                                $format['description'] .= ' ' . $row['master_format'] . ' ' . $row['master_frame_rate'];
                                            }
                                        }
                                    }
                                    else{
                                        if(!isset($custom_frame_rates)){
                                            $custom_frame_rates = array();
                                            if(!isset($custom_frame_rates[$format['destination']])){
                                                $this->db->where('media', $format['destination']);
                                                $custom_frame_rates[$format['destination']] = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
                                            }
                                        }
                                        $format['custom_frame_rates'] = $custom_frame_rates[$format['destination']];
                                    }
                                    if(!trim($format['description']))
                                        $format['description'] = $format['first_description'];
                                    $delivery_methods[$key]['formats'][] = $format;
                                }
                            }
                        }
                        foreach($delivery_methods as $key => $method){
                            if(!isset($method['formats'])){
                                unset($delivery_methods[$key]);
                            }
                        }
                        $row['delivery_methods'] = $delivery_methods;
                    }
                }

            }

        return $rows;
    }

    public function get_clipbin_clips($ids, $sort = array(), $lang = 'en'){
        $rows = array();
        $filter = '';
        //$filter[] = 'c.client_id = ' . (int)$provider;
        if(!is_array($ids)){
            $ids = array($ids);
        }
        if($ids){
            $filter[] = 'c.id IN (' . implode(',', $ids) . ')';
        }
        if($filter){
            $filter = 'WHERE ' . implode(' AND ', $filter);
        }
        $sort_str = '';
        if($sort){
            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
        }

        $query = $this->db->query('
            SELECT c.id, c.code, c.license, c.duration, cc.title, cc.description
            FROM lib_clips c
            INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang = ?' . $filter . $sort_str, $lang);
        $rows = $query->result_array();
        $base_url = $this->config->base_url();
        foreach ($rows as &$row) {
            $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
            $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
            $row['preview'] = $this->get_clip_path($row['id'], 'preview');
            $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
            $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
        }

        return $rows;
    }

    function get_clip_add_collections($clip_id){
        $query = $this->db->query('SELECT lc.* FROM lib_collections lc
                    INNER JOIN lib_clips_collections lcac ON lc.id = lcac.collection_id AND lcac.clip_id = ?', $clip_id);
        $result = $query->result_array();
        return $result;
    }

//    function get_clip_license_types($clip_id){
//        $query = $this->db->query('SELECT ll.* FROM lib_licensing ll
//                    INNER JOIN lib_clip_license_types lclt ON ll.id = lclt.license_id AND lclt.clip_id = ?', $clip_id);
//        $result = $query->result_array();
//        return $result;
//    }

    function get_clip_keywords($clip_id){
        $query = $this->db->query('SELECT lk.* FROM lib_keywords lk
                    INNER JOIN lib_clip_keywords lck ON lk.id = lck.keyword_id AND lck.clip_id = ?', $clip_id);
        $result = $query->result_array();
        return $result;
    }


    function get_clips_count_by_submission($submission_id){
        $this->db->where('submission_id', $submission_id);
        $query = $this->db->get('lib_clips');
        return $query->num_rows();
    }

    function update_download_statistic($id, $provider_id, $remote_addr){
        $query = $this->db->get_where('lib_preview_downloads_statistic', array('clip_id' => $id, 'provider_id' => $provider_id, 'remote_addr' => $remote_addr), 1);
        $res = $query->result_array();
        if($res){
            $this->db_master->set('count', 'count + 1', FALSE);
            $this->db_master->where('id', $res[0]['id']);
            $this->db_master->update('lib_preview_downloads_statistic');
        }
        else{
            $data = array(
                'clip_id' => $id,
                'provider_id' => $provider_id,
                'remote_addr' => $remote_addr,
                'count' => 1
            );
            $this->db_master->insert('lib_preview_downloads_statistic', $data);
        }
    }

    function get_downloads_count($provider_id = 0){
        $this->db->select('clip_id');
        if($provider_id)
            $this->db->where('provider_id', (int)$provider_id);
        $this->db->get('lib_preview_downloads_statistic');
        return $this->db->count_all_results();
    }

	function GetStatisticItemsCount ( $filter ) {
		$result = $this->db->query( "SELECT COUNT( id ) AS 'count' FROM lib_clips_extra_statistic AS stat {$filter}" );
		$result = ( is_object( $result ) ) ? $result->row_array() : array ();
		return ( isset( $result[ 'count' ] ) ) ? $result[ 'count' ] : 0;
	}

	function GetStatisticTopItemsCount ( $filter ) {
		$result = $this->db->query( "
			SELECT
				DISTINCT( clip_id ) AS cid,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 1 AND cid = clip_id ) AS type_1,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 2 AND cid = clip_id ) AS type_2,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 3 AND cid = clip_id ) AS type_3
			FROM
				lib_clips_extra_statistic
			{$filter}"
		);
		return ( is_object( $result ) ) ? $result->num_rows() : 0;
	}

	function GetStatisticItems ( $filter, $limit ) {
		$result = $this->db->query( "
				SELECT
					stat.*,
					DATE_FORMAT( stat.time, '%d.%m.%Y - %H:%i' ) AS 'date',
					actions.name AS 'action',
					( SELECT code FROM lib_clips WHERE id = stat.clip_id LIMIT 1 ) AS clip_code
				FROM
					lib_clips_extra_statistic AS stat
				JOIN
					lib_extra_statistic_actions AS actions
					ON
						actions.type = stat.action_type AND
						actions.lang = 'en'
				{$filter}
				ORDER BY id DESC
				{$limit}"
		);
		return ( is_object( $result ) ) ? $result->result_array() : array ();
	}

	function GetStatisticTopItems ( $filter, $order, $limit ) {
		$result = $this->db->query( "
			SELECT
				DISTINCT( clip_id ) AS cid,
				clip_id,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 1 AND cid = clip_id ) AS type_1,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 2 AND cid = clip_id ) AS type_2,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 3 AND cid = clip_id ) AS type_3
			FROM
				lib_clips_extra_statistic
			{$filter}
			{$order}
			{$limit}"
		);
		return ( is_object( $result ) ) ? $result->result_array() : array();
	}

	function GetSessionStatisticFilterName () {
		$clip_id = (int) $this->id;
		return ( $clip_id ) ? "clip-{$clip_id}-statistic-user" : "clip-statistic-user";
	}

	function SendCommentToProvider ( $provider_id, $user_login, $message, $clip_id ) {
		$this->load->helper( 'Emailer' );
		$clip = $this->get_clip( $clip_id );
		$emailer = Emailer::In();
		$emailer->LoadTemplate( 'toprovider-clip-comment' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientFromId( $provider_id );
		$emailer->SetTemplateValue( 'system', 'message', base64_decode( $message ) );
		$emailer->SetTemplateValue( 'system', 'user', $user_login );
		$emailer->SetTemplateValue( 'clip', 'id', $clip[ 'id' ] );
		$emailer->SetTemplateValue( 'clip', 'code', $clip[ 'code' ] );
		$emailer->SetTemplateValue( 'clip', 'title', $clip[ 'title' ] );
		$emailer->Send();
		$emailer->Clear();
	}

	function SaveCommentToLog ( $provider_id, $user_login, $mesaage, $clip_id ) {
		$data = array (
			'provider_id' => $provider_id,
			'user_login' => $user_login,
			'clip_id' => $clip_id,
			'message' => base64_decode( $mesaage )
		);
		$this->db_master->insert( 'lib_clips_comments', $data );
	}

    function getPrevClipsIds ( $fromClipId, $count ) {
        $resultArray = array ();
        $this->db->select( 'id' );
        $this->db->where( 'id <', $fromClipId );
        $this->db->order_by( 'id', 'DESC' );
        $this->db->limit( $count );
        $result = $this->db->get( 'lib_clips' );
        if ( $result ) {
            $resultArray = $result->result_array();
            asort( $resultArray );
        }
        return $resultArray;
    }

    function getNextClipsIds ( $fromClipId, $count ) {
        $resultArray = array ();
        $this->db->select( 'id' );
        $this->db->where( 'id >', $fromClipId );
        $this->db->order_by( 'id', 'ASC' );
        $this->db->limit( $count );
        $result = $this->db->get( 'lib_clips' );
        if ( $result ) {
            $resultArray = $result->result_array();
            asort( $resultArray );
        }
        return $resultArray;
    }

    function exists($clip_id){
        $this->db->where('id', $clip_id);
        $res = $this->db->get('lib_clips');
        return (bool)$res->num_rows();
    }

    function is_r3d($id){
        $this->db->select('lib_clips.id');
        $this->db->join('lib_clips_res', 'lib_clips_res.clip_id = lib_clips.id', 'inner');
        $this->db->where('lib_clips.id', $id);
        $this->db->where('lib_clips_res.type', '2');
        $this->db->like('lib_clips_res.location', 'R3D', 'before');
        $res = $this->db->get('lib_clips');
        return (bool)$res->num_rows();
    }
}
