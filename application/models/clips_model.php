<?php

require_once(APPPATH . '/libraries/SorlSearchAdapter.php');
require_once(APPPATH . '/libraries/clarifai/src/ClarifaiClient.php');
require_once(APPPATH . '/libraries/PorterStemmer.php');
require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');
require_once(APPPATH . '/libraries/BooleanSearchParser/Parser.php');
require_once(APPPATH . '/libraries/BooleanSearchParser/Splitter.php');

use Aws\S3\S3Client;
use Libraries\Cliplog\Editor\KeywordsState\StateManager;
use BE\Clarifai\Sdk\ClarifaiClient;

/**
 * @property Settings_model $settings_model
 * @property Videositemap_model $videositemap_model
 * @property Editors_model $editors_model
 * @property Submissions_model $submissions_model
 * @property Groups_model $groups_model
 * @property Deliveryoptions_model $deliveryoptions_model
 */
class Clips_model extends CI_Model
{

    const CLIP_ACTION_VIEW = 1;
    const CLIP_ACTION_DOWNLOAD_PREVIEW = 2;
    const CLIP_ACTION_ORDERED = 3;
    const CLIP_ACTION_DOWNLOAD_FULL = 4;
    const DEFAULT_THUMB_TYPE = 0;

    var $img_types = array('jpg', 'jpeg', 'png', 'gif');
    var $motion_types = array('mov', 'mp4', 'flv');
    var $codecs = array(
        'YUV' => 'Uncompressed'
    );
    var $res_type;
    var $autocreate_sitemap;
    var $filter_sql;
    var $store;

    var $tables_with_resources_paths = ['lib_thumbnails_new', 'lib_thumbnails'];

    /**
     * hash value of filter_sql string
     * used to store already built filters in db
     *
     * @var string
     */
    private $filter_sql_hash;

    var $sectionList = array(
        'shot_type' => '',
        'subject_category' => '',
        'primary_subject' => '',
        'other_subject' => '',
        'appearance' => '',
        'actions' => '',
        'time' => '',
        'habitat' => '',
        'concept' => '',
        'location' => '',
        'country' => '',
        'category' => ''
    );

    /**
     * Construct
     */
    function Clips_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

        $this->res_type = array('thumb' => 0, 'preview' => 1, 'hdpreview' => 5,  'res' => 2, 'motion_thumb' => 0);

        $this->load->model('editors_model');
        $this->load->model('locations_model');
        $this->load->model('videositemap_model');
        $this->load->model('settings_model');
        $this->load->model('register_model');
        $this->load->model('aws_model');
        $this->load->model('aws3_sqs_delete_resources_model');
        $this->load->helper('timeline');
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->store = $store;
        //$this->load->library('duration_logger');
    }

    /**
     * Clear WHERE filrers for SQL query which build in functions
     * @see build_filter_sql()
     * @see build_filter_sql_count()
     *
     */
    function clearSqlFilter()
    {
        $this->filter_sql = NULL;
        $this->filter_sql_hash = NULL;
    }

    /**
     * just return hash value of filter_sql string
     *
     * @return string|null
     */
    public function getFilterSqlHash()
    {
        return $this->filter_sql_hash;
    }


    public function get_preview_data($preview_params)
    {
        if (!empty($preview_params['no_direct_output']) && !empty($preview_params['download_hd_video'])) {
            $isHdPreview = true;
            return $this->get_preview_download($preview_params['clip_id'], $isHdPreview);
        }

        return $this->get_preview_download($preview_params['clip_id']);
    }

    public function get_preview_download($clip_id, $isHdPreview = false)
    {
        header('Content-Type: application/json;');
        $content_type = $isHdPreview ? 'hdpreview' : 'preview';
        echo json_encode($this->get_preview_content($clip_id, $content_type));
        die();
    }

    /**
     * Process download preview:
     * find file, update statistic
     *
     *
     * @return false | [
     *      'preview' => '' // path to file
     *      'headers' => [], // array with necessary headers to output file,
     * ];
     * return false if no file found, or array with headrs and path to file
     */
    public function get_preview_content($clip_id, $content_type = 'preview')
    {
        $preview =  $this->clips_model->get_clip_path( $clip_id, $content_type );
        if ( $preview ) {
            $user_id = $this->session->userdata('uid');
            if(!$user_id) {$this->clips_model->set_check_ip($this->clips_model->get_client_ip());}
            $user_provider = 0;
            if ( FALSE !== $this->uri->segment( 5 ) ) {
                $user_login = $this->uri->segment( 5 );
                $this->load->model( 'users_model' );
                $user = $this->users_model->GetUserByLogin( $user_login );
                if ( $user ) {
                    $user_provider = $user[ 'provider_id' ];
                }
            } else {
                $user_login = 'guest';
            }
            $clip = $this->clips_model->get_clip( $clip_id );
            $provider_id = $clip[ 'client_id' ];
            $this->clips_model->ClipLogger( $clip_id, $provider_id, $user_login, Clips_model::CLIP_ACTION_DOWNLOAD_PREVIEW );

            $this->clips_model->update_download_statistic( $clip_id, $user_provider, $_SERVER[ 'REMOTE_ADDR' ] );

            $filename = substr( pathinfo( $preview, PATHINFO_BASENAME ), 0, strpos( pathinfo( $preview, PATHINFO_BASENAME ), '?' ) );
            $ext = pathinfo( $preview, PATHINFO_EXTENSION );
            $filename = $clip['code'] ? $clip['code'] : pathinfo( $filename, PATHINFO_FILENAME );
            $price_levels = array(3 => 'PR', 4 => 'GD');
            $licenses = array(1 => 'RF', 2 => 'RM');
            $sufix = '';
            if (array_key_exists($clip['price_level'], $price_levels)) {
                $sufix = $price_levels[$clip['price_level']];
            }
            elseif(array_key_exists($clip['license'], $licenses)) {
                $sufix = $licenses[$clip['license']];
            }
            if ($sufix) {
                $filename .= '_' . $sufix;
            }
            $filename .= '.' . $ext;

            $headers = [
                'Content-Type: application/force-download; name=' . $filename,
                'Content-Disposition: attachment; filename=' . $filename
            ];

            return compact('preview', 'headers', 'filename');
        }

        return false;
    }



    /**
     * view html block on page with all SQL Querys
     */
    function dbQuerys()
    {
        //executed queries:
        $qs = $this->db->queries;
        //time executed queries:
        $qts = $this->db->query_times;
        ob_start();
        echo '<div style="background: #ccc;position: absolute;width: 400px;height: 200px;
                overflow: auto;border: #00008B 1px solid; border-radius: 10px;padding: 10px;box-shadow: #0000ee 2px; z-index: 9;"><pre>';
        foreach ($qs as $k => $q) {
            echo 'Q[' . $k . ']:' . $q . '<br>T[' . $k . ']:' . $qts[$k] . '<hr>';
        }
        echo '</pre></div>';
        ob_end_flush();
    }

    /**
     * Insert to DB statistics viewed and downloaded clips
     * @see DB Tables: lib_clips_extra_statistic, lib_clips_download_statistic, lib_clips
     * @param $clip_id
     * @param $provider_id
     * @param $user_login
     * @param $type 1 - viewedClip | 2 - downloadedClip
     */
    function ClipLogger($clip_id, $provider_id, $user_login, $type)
    {
        $clip = $this->get_clip_by_id($clip_id);
        $this->db_master->query("INSERT INTO lib_clips_extra_statistic ( clip_id, provider_id, user_login, action_type ) VALUES ( ?, ?, ?, ? )", array($clip_id, $clip['client_id'], $user_login, $type));
        switch ($type) {
            case 1:
                $this->viewedClip($clip_id);
                break;
            case 2:
                $this->downloadedClip($clip_id);
                $this->db_master->query("INSERT INTO lib_clips_download_statistic ( clip_id, provider_id, user_login, action_type ) VALUES ( ?, ?, ?, ? )", array($clip_id, $clip['client_id'], $user_login, $type));
                break;
        }
    }

    /**
     * Insert to DB statistics viewed clips and increment count
     * @see ClipLogger(), DB Table: lib_clips
     * @param $clipId
     */
    function viewedClip($clipId)
    {
        $this->db_master->query('UPDATE `lib_clips` set `viewed` = `viewed` + 1 WHERE id=' . (int)$clipId);
    }

    /**
     * Insert to DB statistics downloaded clips and increment count
     * @see ClipLogger(), DB Table: lib_clips
     * @param $clipId
     */
    function downloadedClip($clipId)
    {
        $this->db_master->query('UPDATE `lib_clips` set `downloaded` = `downloaded` + 1 WHERE id=' . (int)$clipId);
    }

    /**
     * Insert to DB statistics viewed/downloaded clips and increment count
     * @see ClipLogger()
     * @param $order_id
     * @param $type 1 - viewedClip | 2 - downloadedClip
     */
    function OrderLogger($order_id, $type)
    {
        $items = $this->db->query('SELECT oi.*,c.client_id as user_id,u.login FROM `lib_orders_items` AS oi
            JOIN lib_orders as o ON o.id=oi.order_id
            JOIN lib_clips as c ON c.id=oi.item_id
            JOIN lib_users as u ON u.id=o.client_id
            WHERE oi.order_id= ' . (int)$order_id)->result_array();
        foreach ($items as $item) {
            $this->ClipLogger($item['item_id'], $item['user_id'], $item['login'], $type);
        }
    }

    /**
     * If exist in DB Table: lib_settings paramm "video_sitemap_filepath" create sitemap XML file.
     */
    function create_sitemap()
    {
        if ($this->settings_model->get_setting('video_sitemap_autocreate')) {
            $clips = $this->get_clips_list($this->config->item('default_language'), '', '', 'limit 50000');
            $this->videositemap_model->create_map($clips);
        }
    }

    /**
     * Look at $filter['words'] and $filter['wordsin'] and create SQl query part
     * @param $filter
     * @return mixed|string
     */
    function prepare_words($filter)
    {
        $words = (strpos(trim($filter['words']), 0x20) !== false) ? $filter['words'] : $filter['words'] . '*';
        if ($words != '*') {
            $words = (strpos(trim($filter['wordsin']), 0x20) !== false) ? $words . ' ' . $filter['wordsin'] : $words . ' ' . $filter['wordsin'] . '*';
        } else {
            $words = (strpos(trim($filter['wordsin']), 0x20) !== false) ? $filter['wordsin'] : $filter['wordsin'] . '*';
        }
        $words = $this->db->escape(preg_replace('/ +/', ' ', trim($words)));

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

    /**
     *
     * get built filter string by its hash,
     * $filter['filter_hash'] value is being unset at the end, if exists (!)
     *
     * @param mixed $filter - any value accepted, the only sense will be if an only if $filter['filter_hash'] exists
     *
     * @return void - if filter exists, values of $this->filter_sql and $this->filter_sql_hash will be updated
     */
    private function restoreFilterSqlFromDb(&$filter)
    {
        if (!empty($this->filter_sql) // nothing to do here if filter_sql is not empty already
            || empty($filter)
            || !is_array($filter)
            || !array_key_exists('filter_hash', $filter)
        ) {
            return;
        }

        // to prevent hardly detected errors when filter sql is null and hash is not, etc.
        $this->clearSqlFilter();

        $this->load->model('search_filters_model');

        if ($this->filter_sql = $this->search_filters_model->getFIlter($filter['filter_hash'])) {
            // save hash also, if filter was found
            $this->filter_sql_hash = $filter['filter_hash'];
        }

        // no more need in this value
        unset($filter['filter_hash']);

    }

    /**
     * save build filter data
     * Save filter string save to db, and its hash to $filter_sql_hash so it can be used later
     *
     * @return void
     */
    private function saveFilterSql()
    {
        if (empty($this->filter_sql)) {
            // nothing to do here on emty filter_sql string
            return ;
        }
        
        $this->load->model('search_filters_model');
        
        // save value to db, or get existed one and save hash to filter_sql_hash for proper use
        $this->filter_sql_hash = $this->search_filters_model->saveFilterOrGetExisted($this->filter_sql);

    }

    /**
     * Look at all filters and create WHERE part query from get_clips_list
     * @see get_clips_list
     * @param array $filter
     * @param bool|FALSE $isFront
     */
    function build_filter_sql($filter, $isFront = FALSE)
    {
        if (empty($filter) || !is_array($filter)) {
            return;
        }

        // try to restore filter from db
        $this->restoreFilterSqlFromDb($filter);
        // --

        // filter sql already built, skip
        if (!empty($this->filter_sql)) {
            return;
        }

        if ($this->session->userdata('searchWordFilter') != '' && empty($filter['search_in'])) {
            $filter['words'] = $this->session->userdata('searchWordFilter');
        } elseif ($this->session->userdata('searchWordFilter') != '') {
            $filter['words'] = $this->session->userdata('searchWordFilter');
        }
        if (!isset($filter["active"])) $filter["active"] = 1;
        foreach ($filter as $name => $value) {
            $part = '';
            switch ($name) {
                case 'active':
                    if (is_array($value))
                        $part = ' (c.active IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.active = ' . (int)$value . ') ';
                    break;

                case 'parsedwords':
                    if (!empty($filter['parsedwords'])) {
                        $part = $this->getSqlConstraintForFullTextSearch($this->db->escape($filter['parsedwords']));
                    }
                    break;

                case 'duration':
                    if (isset($filter['duration']) && $filter['duration'][0] == '1to10') {
                        $part = ' c.duration >= 10';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to20')) {
                        $part = ' c.duration >= 20';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to30')) {
                        $part = ' c.duration >= 30';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to60')) {
                        $part = ' c.duration >= 60';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '61to')) {
                        $part = ' c.duration >= 61';
                    }
                    break;

                case 'creation_date':
                    if (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_week') {
                        $currDate = date('Y-m-d', strtotime("-1 week"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_month') {

                        $currDate = date('Y-m-d', strtotime("-1 month"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_year') {

                        $currDate = date('Y-m-d', strtotime("-1 year"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'over_one_year') {

                    }
                    break;

                case 'license':
                    if (is_array($value)) {
                        $tmp_license_arr = array();
                        foreach ($value as $tmplicense) {
                            $tmp_license_arr[] = intval($tmplicense);
                        }
                        if (!empty($tmp_license_arr)) $part = ' (c.license IN (' . implode(',', $tmp_license_arr) . ')) ';
                    } else
                        $part = ' (c.license = ' . intval($value) . ') ';
                    break;

                case 'price_level':
                    if (is_array($value))
                        $part = ' (c.price_level IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.price_level = ' . intval($value) . ') ';
                    break;

                case 'format_category':
                    if (is_array($value)) {
                        $part = ' (c.sort_format IN(' . implode(",", $value) . ')) ';
                    } else {
                        $part = ' (c.sort_format = ' . $value . ') ';
                    }
                    break;
                case 'brand':
                    if (is_array($value)) {
                        $tmp_brand_arr = array();
                        foreach ($value as $tmpbrand) {
                            $tmp_brand_arr[] = intval($tmpbrand);
                        }
                        if (!empty($tmp_brand_arr)) $part = ' (c.brand IN (' . implode(',', $tmp_brand_arr) . ')) ';
                    } else
                        $part = ' (c.brand = ' . intval($value) . ') ';
                    break;

                case 'category':
                case 'shot_type':
                case 'subject_category':
                case 'actions':
                case 'appearance':
                case 'concept':
                case 'habitat':
                case 'location':
                case 'time':
                // 03.10.2016 updated frontend logic: category is used instead of collection
                // as collection is same as a category (but added by script according to last search term value)
                // this last key 'collection' most likely is not used in frontend filters
                case 'collection':
                    if (!empty($value)) {
                        // for collection filter use category section
                        $sectionId = $name == 'collection' ? 'category' : $name;

                        $part = ' c.id IN (SELECT `clip_id` FROM lib_clips_keywords WHERE section_id = "'
                            . $sectionId . '"';

                        if (is_array($value)) {
                            array_walk(
                                $value,
                                function (&$item, $_key) {
                                    $item = $this->db->escape($item);
                                }
                            );
                            $value = implode(',', $value);
                            $part .= ' AND keyword IN (' . $value . '))';
                        } else {
                            $part .= ' AND keyword = ' . $this->db->escape($value) . ')';
                        }
                    }

                    break;


                /*                             case 'sd_hd':
                                                    if ($value == 'sd') {
                                                        $part = ' (c.width < 1280 AND c.height < 720) ';
                                                    } elseif ($value == 'hd') {
                                                        $part = ' (c.width >= 1280 AND c.height >= 720) ';
                                                    }
                                                    break;
                                                case 'parent':
                                                    $part = ' (c.parent = ' . (int)$value . ') ';
                                                    break;*/

                case 'client_id':
                    if ($isFront == false) {
                        $part = ' (c.client_id = ' . (int)$value . ') ';
                    }
                    break;

                /*case 'submission_id':
                    $part = ' (c.submission_id = ' . intval($value) . ') ';
                    break;

                case 'id':
                    if (is_array($value))
                        $part = ' (c.id IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.id = ' . intval($value) . ') ';
                    break;

                case 'admin_action_filter':
                    if (is_array($value))
                        $part = ' (c.admin_action IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.admin_action = ' . ($value) . ') ';
                    break;

                case 'collection_filter':
                    if (isset($filter['collection_filter'])) {
                        $part = ' (c.collection IN (' . $value . '))';
                    }
                    break;
                case 'brand_filter':
                    if (isset($filter['brand_filter'])) {
                        $part = ' (c.brand IN (' . $value . '))';
                    }
                    break;
                case 'license_filter':
                    if (isset($filter['license_filter'])) {
                        $part = ' (c.license IN (' . $value . '))';
                    }
                    break;
                case 'price_level_filter':
                    if (isset($filter['price_level_filter'])) {
                        $part = ' (c.price_level IN (' . $value . '))';
                    }
                    break;
                case 'filter_active':
                    if (isset($filter['filter_active'])) {
                        $part = ' (c.active IN (' . $value . '))';
                    }
                    break;


                case 'search_in':
                    if (isset($filter['search_in'])) {


                        $part = ' (cc.title LIKE "%' . $value . '%" OR cc.description LIKE "%' . $value . '%") ';
                    }
                    break; */
            }

            if (!empty($part)) {
                if (!empty($this->filter_sql)) {
                    $this->filter_sql .= ' AND ';
                }
                $this->filter_sql .= $part;
            }
        }

        if (!empty($this->filter_sql)) {
            $this->filter_sql = ' WHERE ' . $this->filter_sql;
        }

        /*                case 'category':
                    if (!is_array($value)) {
                        $tmp = $value;
                        $value = array();
                        $value[0] = $tmp;
                    }
                    foreach ($value as $col_filter) {
                        if ($col_filter == 'Land') {
                            $col_filter = 'Nature Footage';
                        } elseif ($col_filter == 'Ocean') {
                            $col_filter = 'Ocean Footage';
                        } elseif ($col_filter = 'Adventure') {
                            $col_filter = 'Adventure Footage';

                        }
                        $col_filter_arr[] = "'" . $col_filter . "'";
                    }
                    $col_filter = implode(",", $col_filter_arr);
                    $part = ' (c.collection IN (' . $col_filter . ')) ';
                    break;*/

        /* if (!empty($filter['category'])) {
            if (is_array($filter['category'])) {
                foreach ($filter['category'] as &$tmpcategory) {
                    $tmpcategory = $this->db->escape($tmpcategory);
                }
                $category = implode(",", $filter['category']);
            } else {
                $category = $this->db->escape($filter['category']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords category ON category.clip_id = c.id AND category.section_id = 'category' AND category.keyword IN (" . $category . ")" . $this->filter_sql;
        }

        if (!empty($filter['shot_type'])) {
            if (is_array($filter['shot_type'])) {
                foreach ($filter['shot_type'] as &$tmpshot_type) {
                    $tmpshot_type = $this->db->escape($tmpshot_type);
                }
                $shot_type = implode(",", $filter['shot_type']);
            } else {
                $shot_type = $this->db->escape($filter['shot_type']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords shot_type ON shot_type.clip_id = c.id AND shot_type.section_id = 'shot_type' AND shot_type.keyword IN (" . $shot_type . ")" . $this->filter_sql;
        }

        if (!empty($filter['subject_category'])) {
            if (is_array($filter['subject_category'])) {
                foreach ($filter['subject_category'] as &$tmpsubject_category) {
                    $tmpsubject_category = $this->db->escape($tmpsubject_category);
                }
                $subject_category = implode(",", $filter['subject_category']);
            } else {
                $subject_category = $this->db->escape($filter['subject_category']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords subject_category ON subject_category.clip_id = c.id AND subject_category.section_id = 'subject_category' AND subject_category.keyword IN (" . $subject_category . ")" . $this->filter_sql;
        }

        if (!empty($filter['actions'])) {
            if (is_array($filter['actions'])) {
                foreach ($filter['actions'] as &$tmpactions) {
                    $tmpactions = $this->db->escape($tmpactions);
                }
                $actions = implode(",", $filter['actions']);
            } else {
                $actions = $this->db->escape($filter['actions']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords actions ON actions.clip_id = c.id AND actions.section_id = 'actions' AND actions.keyword IN (" . $actions . ")" . $this->filter_sql;
        }

        if (!empty($filter['appearance'])) {
            if (is_array($filter['appearance'])) {
                foreach ($filter['appearance'] as &$tmpappearance) {
                    $tmpappearance = $this->db->escape($tmpappearance);
                }
                $appearance = implode(",", $filter['appearance']);
            } else {
                $appearance = $this->db->escape($filter['appearance']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords appearance ON appearance.clip_id = c.id AND appearance.section_id = 'appearance' AND appearance.keyword IN (" . $appearance . ")" . $this->filter_sql;
        }

        if (!empty($filter['concept'])) {
            if (is_array($filter['concept'])) {
                foreach ($filter['concept'] as &$tmpconcept) {
                    $tmpconcept = $this->db->escape($tmpconcept);
                }
                $concept = implode(",", $filter['concept']);
            } else {
                $concept = $this->db->escape($filter['concept']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords concept ON concept.clip_id = c.id AND concept.section_id = 'concept' AND concept.keyword IN (" . $concept . ")" . $this->filter_sql;
        }

        if (!empty($filter['habitat'])) {
            if (is_array($filter['habitat'])) {
                foreach ($filter['habitat'] as &$tmphabitat) {
                    $tmphabitat = $this->db->escape($tmphabitat);
                }
                $habitat = implode(",", $filter['habitat']);
            } else {
                $habitat = $this->db->escape($filter['habitat']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords habitat ON habitat.clip_id = c.id AND habitat.section_id = 'habitat' AND habitat.keyword IN (" . $habitat . ")" . $this->filter_sql;
        }

        if (!empty($filter['location'])) {
            if (is_array($filter['location'])) {
                foreach ($filter['location'] as &$tmplocation) {
                    $tmplocation = $this->db->escape($tmplocation);
                }
                $location = implode(",", $filter['location']);
            } else {
                $location = $this->db->escape($filter['location']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords location ON location.clip_id = c.id AND location.section_id = 'location' AND location.keyword IN (" . $location . ")" . $this->filter_sql;
        }

        if (!empty($filter['time'])) {
            if (is_array($filter['time'])) {
                foreach ($filter['time'] as &$tmptime) {
                    $tmptime = $this->db->escape($tmptime);
                }
                $time = implode(",", $filter['time']);
            } else {
                $time = $this->db->escape($filter['time']);
            }
            $this->filter_sql = " INNER JOIN lib_clips_keywords time ON time.clip_id = c.id AND time.section_id = 'time' AND time.keyword IN (" . $time . ")" . $this->filter_sql;
        } */

        if (!empty($filter['cat_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clips_cats ccs ON ccs.clip_id = c.id AND ccs.cat_id = '
                . $filter['cat_id'] . $this->filter_sql;
        }

        if (!empty($filter['sequence_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clip_sequences csq ON csq.clip_id = c.id AND csq.sequence_id = '
                . $filter['sequence_id'] . $this->filter_sql;
        }

        if (!empty($filter['bin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clip_bins cbn ON cbn.clip_id = c.id AND cbn.bin_id = '
                . $filter['bin_id'] . $this->filter_sql;
        }

        if (!empty($filter['gallery'])) {
            $this->filter_sql = ' INNER JOIN lib_backend_lb_items cgl ON cgl.item_id = c.id AND cgl.backend_lb_id = '
                . $filter['gallery'][0] . $this->filter_sql;
        }

        if (!empty($filter['clipbin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_lb_items lbi ON lbi.item_id = c.id AND lbi.lb_id = '
                . $filter['clipbin_id'] . $this->filter_sql;
        }

        if (!empty($filter['backend_clipbin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_backend_lb_items blbi ON blbi.item_id = c.id AND blbi.backend_lb_id = '
                . $filter['backend_clipbin_id'] . $this->filter_sql;
        }
        /*
               //      if (!empty($filter['collection'])) {
               //  $this->filter_sql = ' INNER JOIN lib_collections lc ON lc.name=c.collection '
               //          . $this->filter_sql;
                       }


                       //BLOCKED BY IMRAN////
               //        if (!empty($filter['shot_type'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['subject_category'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['actions'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['appearance'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['time'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['location'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['habitat'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //        if (!empty($filter['concept'])) {
               //            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
               //                    . $this->filter_sql;
               //        }
               //  mail('imranmani.numl@gmail.com', 'Test', $this->filter_sql); */

        // save built filter string
        $this->saveFilterSql();
    }

    private function getSqlConstraintForFullTextSearch($words)
    {
        return " (MATCH(c.keywords, c.description, c.code_search) AGAINST(" . $this->db->escape($words) . " IN BOOLEAN MODE) > 0) ";
    }

    function build_filter_sql_backend($filter, $isFront = FALSE)
    {

        if (empty($filter)) {
            return;
        }
        $parser = new \DuncanOgle\BooleanSearchParser\Parser();
        $words = $filter['words'];
        $words = str_replace("+", " ", $words);
        $words = trim($words);
        $parsedwords = $parser->parse($words);
        $filter['words'] = $words;
        $filter['parsedwords'] = $parsedwords;

        $join = '';

        foreach ($filter as $name => $value) {
            $part = '';

            switch ($name) {
                case 'parsedwords':
                    if (!empty($filter['parsedwords'])) {
                        $part = $this->getSqlConstraintForFullTextSearch($this->db->escape($filter['parsedwords']));

                        /*
                        if (stripos($words, ' not ')) {
                            $words = str_ireplace(" not ", " NOT ", $words);
                        }
                        if (stripos($words, ' and ')) {
                            $words = str_ireplace(" and ", " AND ", $words);
                        }
                        if (stripos($words, ' or ')) {
                            $words = str_ireplace(" or ", " OR ", $words);
                        }

                        $wordsSecondCheck = $words;

                        if (stripos($words, ' NOT ')) {
                            $words = strstr($words, ' NOT ', true);
                            $words = $words . "*'";
                            $part = ' (MATCH(c.code, cc.title, cc.subject, cc.description,cc.keywords)
                            AGAINST(' . $words . ' IN BOOLEAN MODE) > 0) ';

                            $getWord = str_replace(str_split("\\/:*?'<>|"), ' ', $wordsSecondCheck);

                            $getWord = explode(' NOT ', $getWord);
                            $getWord2 = $getWord;
                            $getWord3 = trim($getWord[1]);
                            $getWord = trim($getWord[1]) . ' ' . trim($getWord[0]);
                            $getWord2 = trim($getWord2[0]) . ' ' . trim($getWord2[1]);
                            $part .= " AND NOT((c.code LIKE '%" . trim($getWord) . "%' or cc.title LIKE '%" . trim($getWord) . "%'  or cc.subject LIKE '%" . trim($getWord) . "%' or cc.description LIKE '%" . trim($getWord) . "%'))";
                            $part .= " AND NOT((c.code LIKE '%" . trim($getWord2) . "%' or cc.title LIKE '%" . trim($getWord2) . "%' or cc.subject LIKE '%" . trim($getWord2) . "%' or cc.description LIKE '%" . trim($getWord2) . "%'))";
                            $part .= " AND NOT((c.code LIKE '%" . trim($getWord3) . "%' or cc.title LIKE '%" . trim($getWord3) . "%' or cc.subject LIKE '%" . trim($getWord3) . "%' or cc.description LIKE '%" . trim($getWord3) . "%'))";
                        } elseif (stripos($words, ' AND ')) {

                            $getWord = str_replace(str_split("\\/:*?'<>|"), ' ', $wordsSecondCheck);
                            $getWord = str_replace(' AND ', " ", $getWord);
                            $getWordArr = explode(" ", $getWord);

                            $wordsInsert = '';
                            foreach ($getWordArr as $dataWords) {
                                if ($dataWords != '') {
                                    $wordsInsert .= '+' . $dataWords . '* ';
                                }
                            }
                            $wordsInsert = "'" . $wordsInsert . "'";
                            $part = ' (MATCH(c.code,cc.keywords, cc.title, cc.subject, cc.description)
                            AGAINST(' . $wordsInsert . ' IN BOOLEAN MODE) > 0) ';
                        } elseif (stripos($words, ' OR ')) {

                            $words2 = str_replace(' OR ', " ", str_replace(str_split("\\/:*?'<>|"), '', $words));
                            $words = explode(' OR ', $words);


                            $combinedReverseSearch = str_replace(str_split("\\/:*?'<>|"), '', $words[1]) . ' ' . str_replace(str_split("\\/:*?'<>|"), '', $words[0]);

                            $combinedReverseSearch = $combinedReverseSearch;

                            $searchTerm1 = str_replace(str_split("\\/:*?'<>|"), '', $words[0]);
                            $searchTerm2 = str_replace(str_split("\\/:*?'<>|"), '', $words[1]);

                            $part = " (MATCH(c.code,cc.title,cc.keywords, cc.subject, cc.description)
                            AGAINST('" . $searchTerm1 . "*' IN BOOLEAN MODE) > 0) ";


                            $part .= "  OR ((c.code LIKE '%" . trim($searchTerm2) . "%'   or cc.title LIKE '%" . trim($searchTerm2) . "%'or cc.subject LIKE '%" . trim($searchTerm2) . "%' or cc.description LIKE '%" . trim($searchTerm2) . "%'))";
                            $part .= "  OR ((c.code LIKE '%" . trim($words2) . "%'  or cc.title LIKE '%" . trim($words2) . "%'  or cc.subject LIKE '%" . trim($words2) . "%' or cc.description LIKE '%" . trim($words2) . "%'))";
                            $part .= "  OR ((c.code LIKE '%" . trim($combinedReverseSearch) . "%'   or cc.title LIKE '%" . trim($combinedReverseSearch) . "%'  or cc.subject LIKE '%" . trim($combinedReverseSearch) . "%' or cc.description LIKE '%" . trim($combinedReverseSearch) . "%'))";
                        } elseif ((strpos($words, '"'))) {

                            $words = preg_replace('/\+|\<|\>|\?|\#|\$|\-/i', ' ', $words);
                            $words = str_replace("'", "", $words);
                            $words = str_replace('"', "", $words);
                            $words = str_replace('\\', "", $words);
                            $words = str_replace("*", "", $words);

                            $words = str_replace('_RF', "", $words);
                            $words = str_replace('_RM', "", $words);
                            $words = str_replace('_PR', "", $words);
                            $words = str_replace('_GD', "", $words);

                            $part = " ((c.code LIKE '%" . trim($words) . "%'  or cc.title LIKE '%" . trim($words) . "%' or cc.subject LIKE '%" . trim($words) . "%' or cc.description LIKE '%" . trim($words) . "%' or cc.keywords LIKE '%" . trim($words) . "%'))";
                        } else {
                            $words = str_replace(str_split("\\/:*?'<>|"), '', $words);
                            $words = explode(" ", $words);

                            foreach ($words as $key => $words) {
                                $words = str_replace('_RF', "", $words);
                                $words = str_replace('_RM', "", $words);
                                $words = str_replace('_PR', "", $words);
                                $words = str_replace('_GD', "", $words);

                                if (strpos($words, "_")) {
                                    $word_exp[$key] = $words;
                                } else {
                                    $porter = new PorterStemmer();
                                    $word_exp[$key] = $porter->Stem($words);
                                }
                            }
                            $getWordArr = array_filter($word_exp);
                            $wordsInsert = '';

                            if (sizeof($getWordArr) >= 2) {
                                foreach ($getWordArr as $dataWords) {
                                    if ($dataWords != '') {
                                        $wordsInsert .= '+' . $dataWords . '* ';
                                    }
                                }


                                $wordsInsert = "'" . str_replace('++', '+', $wordsInsert) . "'";
                                $part = ' (MATCH(c.code,cc.title,cc.keywords,cc.subject,cc.description)
                            AGAINST(' . $wordsInsert . ' IN BOOLEAN MODE) > 0) ';


                            } else {

                                foreach ($getWordArr as $dataWords) {
                                    if ($dataWords != '') {

                                        //Imran Changes//
                                        $wordsInsert .= $dataWords . ' ';
                                        //Imran Changes//

                                        // $wordsInsert .= '+' . $dataWords . '* ';

                                    }
                                }


                                //Imran Changes//
                                $part = " ((c.code LIKE '%" . trim($wordsInsert) . "%'  or cc.title LIKE '%" . trim($wordsInsert) . "%' or cc.subject LIKE '%" . trim($wordsInsert) . "%' or cc.description LIKE '%" . trim($wordsInsert) . "%' or cc.keywords LIKE '%" . trim($wordsInsert) . "%'))";


                            }


                        }
                        */
                    }
                    break;

                case 'frame_rate':
                    $part = ' (c.frame_rate = ' . floatval($value) . ') ';
                    break;
                case 'sd_hd':
                    if ($value == 'sd') {
                        $part = ' (c.width < 1280 AND c.height < 720) ';
                    } elseif ($value == 'hd') {
                        $part = ' (c.width >= 1280 AND c.height >= 720) ';
                    }
                    break;
                case 'parent':
                    $part = ' (c.parent = ' . (int)$value . ') ';
                    break;
                case 'active':
                    //if (is_array($value))
                    //   $part = ' (c.active IN (' . implode(',', $value) . ')) ';
                    //else
                    //    $part = ' (c.active = ' . (int)$value . ') ';
                    break;
                case 'category':
//                    if (is_array($value)) {
//                        $col_filter = array();
//                        foreach ($value as $col_filter) {
//                            if ($col_filter == 'Land') {
//                                $col_filter = 'Nature Footage';
//                            } elseif ($col_filter == 'Ocean') {
//                                $col_filter = 'Ocean Footage';
//                            } elseif ($col_filter = 'Adventure') {
//                                $col_filter = 'Adventure Footage';
//
//                            }
//                            $col_filter_arr[] = "'" . $col_filter . "'";
//                        }
//                        $col_filter = implode(",", $col_filter_arr);
//
//
//                        $part = ' (c.collection IN (' . $col_filter . ')) ';
//                    } else {
//                        $part = ' (c.collection = ' . $value . ') ';
//                    }
                    break;
                case 'client_id':
                    if ($isFront == false) {
                        $part = ' (c.client_id = ' . (int)$value . ') ';
                    }
                    break;
                case 'submission_id':
                    $part = ' (c.submission_id = ' . intval($value) . ') ';
                    break;
                case 'id':
                    if (is_array($value))
                        $part = ' (c.id IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.id = ' . intval($value) . ') ';
                    break;
                case 'license':
                    if (is_array($value))
                        $part = ' (c.license IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.license = ' . intval($value) . ') ';
                    break;
                case 'price_level':
                    if (is_array($value))
                        $part = ' (c.price_level IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.price_level = ' . intval($value) . ') ';
                    break;
                case 'format_category':
                    if (is_array($value)) {
                        $part = ' (c.sort_format IN(' . implode(",", $value) . ')) ';
                    } else {
                        $part = ' (c.sort_format = ' . $value . ') ';
                    }
                    break;
                case 'brand':
                    if (is_array($value))
                        $part = ' (c.brand IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.brand = ' . intval($value) . ') ';
                    break;
                case 'admin_action_filter':
                    if (is_array($value))
                        $part = ' (c.admin_action IN (' . implode(',', $value) . ')) ';
                    else
                        $part = ' (c.admin_action = ' . ($value) . ') ';
                    break;
                case 'shot_type':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }
                    break;
                case 'subject_category':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }
//


                    break;
                case 'actions':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'appearance':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'time':
                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'location':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'habitat':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'concept':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'collection_filter':
                    if (isset($filter['collection_filter'])) {
                        //$part = ' (c.collection IN (' . $value . '))';
                    }
                    break;
                case 'brand_filter':
                    if (isset($filter['brand_filter'])) {
                        $part = ' (c.brand IN (' . $value . '))';
                    }
                    break;
                case 'license_filter':
                    if (isset($filter['license_filter'])) {
                        $part = ' (c.license IN (' . $value . '))';
                    }
                    break;
                case 'price_level_filter':
                    if (isset($filter['price_level_filter'])) {
                        $part = ' (c.price_level IN (' . $value . '))';
                    }
                    break;
                case 'filter_active':
                    if (isset($filter['filter_active'])) {
                        $part = ' (c.active IN (' . $value . '))';
                    }
                    break;

                case 'duration':
                    if (isset($filter['duration']) && $filter['duration'][0] == '1to10') {
                        $part = ' c.duration >= 10';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to20')) {
                        $part = ' c.duration >= 20';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to30')) {
                        $part = ' c.duration >= 30';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to60')) {
                        $part = ' c.duration >= 60';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '61to')) {
                        $part = ' c.duration >= 61';
                    }
                    break;
                case 'creation_date':
                    if (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_week') {
                        $currDate = date('Y-m-d', strtotime("-1 week"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_month') {

                        $currDate = date('Y-m-d', strtotime("-1 month"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_year') {

                        $currDate = date('Y-m-d', strtotime("-1 year"));
                        $part = " c.ctime >='" . $currDate . "'";
                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'over_one_year') {
                        $currDate = date('Y-m-d', strtotime("-1 year"));
                        $part = " c.ctime <='" . $currDate . "'";
                    }
                    break;

                case 'search_in':
                    if (isset($filter['search_in'])) {


                        $part = ' (c.code LIKE "%' . $value . '%" OR c.description LIKE "%' . $value . '%" OR c.keywords LIKE "%' . $value . '%") ';
                    }
                    break;
                case 'collection_id':
                    if (isset($filter['collection_id'])) {
                        $join .= ' INNER JOIN lib_clips_keywords AS ck ON c.id=ck.clip_id ';
                        if (is_array($value)) {
                            $tmp = '';
                            foreach ($value as $collection)
                                $tmp .= ' ck.keyword LIKE "' . $collection . '" OR';
                            $tmp = substr($tmp, 0, -2);
                            $part = ' (ck.section_id LIKE  "category" AND (' . $tmp . ')) ';
                        } else
                            $part = ' (ck.section_id LIKE  "category" AND ck.keyword LIKE "' . $value . '") ';
                    }
                    break;
            }

            if ($part) {
                if (!empty($this->filter_sql)) {
                    $this->filter_sql .= ' AND ';
                }
                $this->filter_sql .= $part;
            }
        }

        if (!empty($this->filter_sql) && !strpos($this->filter_sql, 'WHERE')) {
            $this->filter_sql = ' WHERE ' . $this->filter_sql;
        }

        if (!empty($filter['cat_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clips_cats ccs ON ccs.clip_id = c.id AND ccs.cat_id = '
                . $filter['cat_id'] . $this->filter_sql;
        }

        if (!empty($filter['sequence_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clip_sequences csq ON csq.clip_id = c.id AND csq.sequence_id = '
                . $filter['sequence_id'] . $this->filter_sql;
        }

        if (!empty($filter['bin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_clip_bins cbn ON cbn.clip_id = c.id AND cbn.bin_id = '
                . $filter['bin_id'] . $this->filter_sql;
        }

        if (!empty($filter['gallery'])) {
            $this->filter_sql = ' INNER JOIN lib_backend_lb_items cgl ON cgl.item_id = c.id AND cgl.backend_lb_id = '
                . $filter['gallery'][0] . $this->filter_sql;
        }

        if (!empty($filter['clipbin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_lb_items lbi ON lbi.item_id = c.id AND lbi.lb_id = '
                . $filter['clipbin_id'] . $this->filter_sql;
        }

        if (!empty($filter['backend_clipbin_id'])) {
            $this->filter_sql = ' INNER JOIN lib_backend_lb_items blbi ON blbi.item_id = c.id AND blbi.backend_lb_id = '
                . $filter['backend_clipbin_id'] . $this->filter_sql;
        }

        if (!empty($this->filter_sql)) {
            $this->filter_sql = $join . ' ' . $this->filter_sql;
        }
        if (!empty($filter['collection'])) {
//  $this->filter_sql = ' INNER JOIN lib_collections lc ON lc.name=c.collection '
//          . $this->filter_sql;
        }


        //BLOCKED BY IMRAN////
//        if (!empty($filter['shot_type'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['subject_category'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['actions'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['appearance'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['time'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['location'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['habitat'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//        if (!empty($filter['concept'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_keywords kw ON kw.clip_id=c.id '
//                    . $this->filter_sql;
//        }
//  mail('imranmani.numl@gmail.com', 'Test', $this->filter_sql);
    }

    /**
     * Look at all filters and create WHERE part query from get_clips_count
     * @see get_clips_list
     * @param $filter
     * @param bool|FALSE $isFront
     */
    function build_filter_sql_count($filter, $isFront = FALSE)
    {

        if (empty($filter)) {
            return;
        }
        if ($this->session->userdata('searchWordFilter') != '' && empty($filter['search_in'])) {
            $filter['words'] = $this->session->userdata('searchWordFilter');
        } elseif ($this->session->userdata('searchWordFilter') != '') {
            $filter['words'] = $this->session->userdata('searchWordFilter');
        }
        print_r($filter);
        foreach ($filter as $name => $value) {
            $part = '';
            switch ($name) {
                case 'parsedwords':
                    if (!empty($filter['parsedwords'])) {
                        $part = " (c.code = " . $this->db->escape($filter['words']) . " OR  "
                        .$this->getSqlConstraintForFullTextSearch($this->db->escape($filter['parsedwords']));
                    }
                    break;

//                case 'frame_rate':
//                    $part = ' (c.frame_rate = ' . floatval($value) . ') ';
//                    break;
//                case 'sd_hd':
//                    if ($value == 'sd') {
//                        $part = ' (c.width < 1280 AND c.height < 720) ';
//                    } elseif ($value == 'hd') {
//                        $part = ' (c.width >= 1280 AND c.height >= 720) ';
//                    }
//                    break;
//                case 'parent':
//                    $part = ' (c.parent = ' . (int)$value . ') ';
//                    break;
//                case 'active':
//                    if (is_array($value))
//                        $part = ' (c.active IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.active = ' . (int)$value . ') ';
//                    break;
                case 'category':
                    if (is_array($value)) {
                        $col_filter = array();
                        foreach ($value as $col_filter) {
                            if ($col_filter == 'Land') {
                                $col_filter = 'Nature Footage';
                            } elseif ($col_filter == 'Ocean') {
                                $col_filter = 'Ocean Footage';
                            } elseif ($col_filter = 'Adventure') {
                                $col_filter = 'Adventure Footage';
                            }
                            $col_filter_arr[] = "'" . $col_filter . "'";
                        }
                        $col_filter = implode(",", $col_filter_arr);
                        $part = ' (c.collection IN (' . $col_filter . ')) ';
                    } else {
                        $part = ' (c.collection = ' . $value . ') ';
                    }
                    break;
//                case 'client_id':
//                    if ($isFront == false) {
//                        $part = ' (c.client_id = ' . (int)$value . ') ';
//                    }
//                    break;
//                case 'submission_id':
//                    $part = ' (c.submission_id = ' . intval($value) . ') ';
//                    break;
//                case 'id':
//                    if (is_array($value))
//                        $part = ' (c.id IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.id = ' . intval($value) . ') ';
//                    break;
//                case 'license':
//                    if (is_array($value))
//                        $part = ' (c.license IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.license = ' . intval($value) . ') ';
//                    break;
//                case 'price_level':
//                    if (is_array($value))
//                        $part = ' (c.price_level IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.price_level = ' . intval($value) . ') ';
//                    break;
//                case 'format_category':
//                    if (is_array($value))
//                        $part = ' (c.source_frame_size LIKE "%' . implode('%', $value) . '%" OR c.master_frame_size LIKE "%' . implode('%', $value) . '%" OR c.digital_file_frame_size LIKE "%' . implode('%', $value) . '%") ';
//                    else
//                        $part = ' (c.source_frame_size LIKE "%' . $value . '%" OR c.master_frame_size LIKE "%' . $value . '%" OR c.digital_file_frame_size LIKE "%' . $value . '%") ';
//                    break;
//                case 'brand':
//                    if (is_array($value))
//                        $part = ' (c.brand IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.brand = ' . intval($value) . ') ';
//                    break;
//                case 'admin_action_filter':
//                    if (is_array($value))
//                        $part = ' (c.admin_action IN (' . implode(',', $value) . ')) ';
//                    else
//                        $part = ' (c.admin_action = ' . ($value) . ') ';
//                    break;
                case 'shot_type':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }
                    break;
                case 'subject_category':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }
//


                    break;
                case 'actions':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'appearance':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'time':
                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'location':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'habitat':
                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
                case 'concept':

                    if (is_array($value)) {

                        $shot_filter = array();
                        $t = 1;
                        foreach ($value as $shot_filter) {
                            if ($t <= 1) {
                                $part = " (cc.keywords like '%" . $shot_filter . "%')";
                            } else {
                                $part .= " AND (cc.keywords like '%" . $shot_filter . "%')";
                            }

                            $t++;
                        }
                    } else {
                        $part = " (cc.shot_type like '%" . $value . "%')";
                    }

                    break;
//                case 'collection_filter':
//                    if (isset($filter['collection_filter'])) {
//                        $part = ' (c.collection IN (' . $value . '))';
//                    }
//                    break;
//                case 'brand_filter':
//                    if (isset($filter['brand_filter'])) {
//                        $part = ' (c.brand IN (' . $value . '))';
//                    }
//                    break;
//                case 'license_filter':
//                    if (isset($filter['license_filter'])) {
//                        $part = ' (c.license IN (' . $value . '))';
//                    }
//                    break;
//                case 'price_level_filter':
//                    if (isset($filter['price_level_filter'])) {
//                        $part = ' (c.price_level IN (' . $value . '))';
//                    }
//                    break;
//                case 'filter_active':
//                    if (isset($filter['filter_active'])) {
//                        $part = ' (c.active IN (' . $value . '))';
//                    }
//                    break;

                case 'duration':
                    if (isset($filter['duration']) && $filter['duration'][0] == '1to10') {
                        $part = ' c.duration >= 10';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to20')) {
                        $part = ' c.duration >= 20';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to30')) {
                        $part = ' c.duration >= 30';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '1to60')) {
                        $part = ' c.duration >= 60';
                    } elseif ((isset($filter['duration']) && $filter['duration'][0] == '61to')) {
                        $part = ' c.duration >= 61';
                    }
                    break;
//                case 'creation_date':
//                    if (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_week') {
//                        $currDate = date('Y-m-d', strtotime("-1 week"));
//                        $part = " c.creation_date >='" . $currDate . "'";
//                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_month') {
//
//                        $currDate = date('Y-m-d', strtotime("-1 month"));
//                        $part = " c.creation_date >='" . $currDate . "'";
//                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'past_year') {
//
//                        $currDate = date('Y-m-d', strtotime("-1 year"));
//                        $part = " c.creation_date >='" . $currDate . "'";
//                    } elseif (isset($filter['creation_date']) && $filter['creation_date'][0] == 'over_one_year') {
//
//                    }
//                    break;
                case 'search_in':
                    if (isset($filter['search_in'])) {
                        $part = ' (cc.title LIKE "%' . $value . '%" OR cc.description LIKE "%' . $value . '%") ';
                    }
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

//        if (!empty($filter['cat_id'])) {
//            $this->filter_sql = ' INNER JOIN lib_clips_cats ccs ON ccs.clip_id = c.id AND ccs.cat_id = '
//                . $filter['cat_id'] . $this->filter_sql;
//        }
//
//        if (!empty($filter['sequence_id'])) {
//            $this->filter_sql = ' INNER JOIN lib_clip_sequences csq ON csq.clip_id = c.id AND csq.sequence_id = '
//                . $filter['sequence_id'] . $this->filter_sql;
//        }
//
//        if (!empty($filter['bin_id'])) {
//            $this->filter_sql = ' INNER JOIN lib_clip_bins cbn ON cbn.clip_id = c.id AND cbn.bin_id = '
//                . $filter['bin_id'] . $this->filter_sql;
//        }
//
//        if (!empty($filter['gallery'])) {
//            $this->filter_sql = ' INNER JOIN lib_backend_lb_items cgl ON cgl.item_id = c.id AND cgl.backend_lb_id = '
//                . $filter['gallery'][0] . $this->filter_sql;
//        }
//
//        if (!empty($filter['clipbin_id'])) {
//            $this->filter_sql = ' INNER JOIN lib_lb_items lbi ON lbi.item_id = c.id AND lbi.lb_id = '
//                . $filter['clipbin_id'] . $this->filter_sql;
//        }
//
//        if (!empty($filter['backend_clipbin_id'])) {
//            $this->filter_sql = ' INNER JOIN lib_backend_lb_items blbi ON blbi.item_id = c.id AND blbi.backend_lb_id = '
//                . $filter['backend_clipbin_id'] . $this->filter_sql;
//        }

        if (!empty($filter['collection'])) {
//  $this->filter_sql = ' INNER JOIN lib_collections lc ON lc.name=c.collection '
//          . $this->filter_sql;
        }


//  mail('imranmani.numl@gmail.com', 'Test', $this->filter_sql);
    }

    /**
     * @param string $lang = 'en'
     * @param null|array $filter
     * @param bool|FALSE $checkFront
     * @return string
     */
    function get_clips_count($lang = 'en', $filter = NULL, $checkFront = FALSE)
    {
        //$this->duration_logger->start('bfsc');
        //$this->build_filter_sql_count($filter, $checkFront);
        $this->build_filter_sql($filter, $checkFront);
        //$this->duration_logger->save('bfsc', 'clips_model::build_filter_sql_count');
        $langCheck = '';
        //for lang check
        /* if (empty($this->filter_sql)) {
            $langCheck = ' WHERE cc.lang=? ';
        } else {
            $langCheck = ' and cc.lang=? ';
        }*/

        $sql = "SELECT COUNT(distinct(c.id)) AS Total FROM `lib_clips` c "
            . (!empty($this->filter_sql) ? $this->filter_sql : ' WHERE 1 ')
            . " and c.active = 1";

        /*$sql = "SELECT COUNT(distinct(id))  AS Total
                FROM lib_clips_content cc " . $this->filter_sql . " " . $langCheck ;//and c.active = 1";*/

        //$this->duration_logger->start('exec');
        $row = $this->db->query($sql, $lang)->result_array();
        //$this->duration_logger->save('exec', 'clips_model::get_clips_count|queryexec', array('sql' => $sql));
        return $row[0]['Total'];


        /*

          SELECT COUNT(distinct(c.id)) total
          FROM lib_clips c
          LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang='en'
          LEFT JOIN lib_collections lc ON lc.name=c.collection
          WHERE  (MATCH(c.code, c.original_filename, cc.title, cc.creator, cc.rights, cc.subject, cc.description, cc.keywords)
          AGAINST('+diver*' IN BOOLEAN MODE) > 0)  AND  (c.license IN (1))  AND  (lc.id IN (1))

         * */
    }

    /**
     * Get clips for Search Clips
     * @used \Clips::view, \Fapi::clips_post, \Cliplog::view, \Cliplog::edit
     * @param string $lang
     * @param string $filter
     * @param string $order
     * @param null $limit
     * @param bool|false $for_api
     * @return mixed
     */
    function get_clips_count_backend($lang = 'en', $filter = NULL, $checkFront = FALSE)
    {
        //$this->build_filter_sql_backend($filter, $checkFront);
        $sql = "SELECT COUNT(distinct(c.id)) AS Total FROM `lib_clips` c " . $this->filter_sql;
        $row = $this->db->query($sql, $lang)->result_array();
        return $row[0]['Total'];
    }

    function get_clips_list($lang = 'en', $filter = '', $order = '', $limit = null, $for_api = false)
    {
        // determine $checkFron flag
        if (is_array($filter) && array_key_exists('checkFront', $filter)) {
            $checkFront = $filter['checkFront'];
            unset($filter['checkFront']);
        } else {
            $checkFront = false;
        }

        if (is_array($filter)) {
            // this have sense only if $filter contain array of filter, but not for already composed where string
            $this->build_filter_sql($filter, $checkFront);
        }

        // check front is used only in build_filter_sql
        unset($checkFront);

        if ($this->filter_sql || empty($filter) || is_array($filter)) {
            $filter = '';
        }
        if (empty($this->filter_sql)) {
            $this->filter_sql = ' WHERE 1 ';
        }

        //$order = " GROUP BY c.id ".$order;
        if (!$for_api) {
            /*$sql = "
                SELECT
                    lib_clips.*,
                    lib_clips.code AS title,
                    lib_clips.description,
                    lib_clips_content.location,
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
                    WHERE lib_clips_content.lang = ?";*/
            $sql = "
                SELECT
                    lib_clips.*,
                    lib_clips.code AS title,
                    lib_users.fname,
                    lib_users.lname,
                   ( SELECT ces.time FROM lib_clips_extra_statistic AS ces WHERE ces.clip_id = lib_clips.id ORDER BY ces.time DESC LIMIT 1 ) AS activity
                FROM lib_clips
                JOIN (
                    SELECT c.id
                    FROM lib_clips AS c
                    LEFT JOIN lib_users AS u ON c.client_id = u.id
                    {$this->filter_sql}
                    {$filter}
                    {$order}
                    {$limit}
                ) AS ids ON ids.id = lib_clips.id
                    LEFT JOIN lib_users ON lib_clips.client_id = lib_users.id
                    WHERE 1";

            $query = $this->db->query($sql);
        } else {
            $sql = "SELECT c.id,c.client_id,c.code,c.license,c.duration,c.active,c.ctime,c.creation_date,c.brand,c.price_level,c.pricing_category,c.source_format,c.source_frame_size,c.master_frame_size,c.digital_file_frame_size,c.source_frame_rate,c.license_restrictions,c.admin_action, c.code AS title, c.description,c.location FROM lib_clips c " . $this->filter_sql . " " . $filter . " " . $order . " " . $limit;
            $query = $this->db->query($sql);
        }
        //return $sql;
        $rows = $query->result_array();

        /* move filter to ajax
        $query = $this->db->query("SELECT GROUP_CONCAT(DISTINCT price_level SEPARATOR ' ') AS price_level,
GROUP_CONCAT(DISTINCT license SEPARATOR ' ') AS license,
GROUP_CONCAT(DISTINCT sort_format SEPARATOR ' ') AS sort_format,
GROUP_CONCAT(DISTINCT brand SEPARATOR ' ') AS brand
FROM lib_clips c
WHERE " . $match);
        $filter_default = $query->result_array();
        $filter_default = $filter_default[0];

        $filter_default["license"] = explode(" ", $filter_default["license"]);
        $filter_default["sort_format"] = explode(" ", $filter_default["sort_format"]);
        $filter_default["price_level"] = explode(" ", $filter_default["price_level"]);
        $filter_default["brand"] = explode(" ", $filter_default["brand"]);

        //License available
        if (in_array("2", $filter_default["license"])) {
            $filter_arr['license_rm'] = 1;
        } else {
            $filter_arr['license_rm'] = 0;
        }

        if (in_array("1", $filter_default["license"])) {
            $filter_arr['license_rf'] = 1;
        } else {
            $filter_arr['license_rf'] = 0;
        }

        //Price level available
        if (in_array("1", $filter_default["price_level"])) {
            $filter_arr['budget'] = 1;
        } else {
            $filter_arr['budget'] = 0;
        }

        if (in_array("2", $filter_default["price_level"])) {
            $filter_arr['standard'] = 1;
        } else {
            $filter_arr['standard'] = 0;
        }

        if (in_array("3", $filter_default["price_level"])) {
            $filter_arr['premium'] = 1;
        } else {
            $filter_arr['premium'] = 0;
        }

        if (in_array("4", $filter_default["price_level"])) {
            $filter_arr['gold'] = 1;
        } else {
            $filter_arr['gold'] = 0;
        }


        //Format available
        if (in_array("3", $filter_default["sort_format"])) {
            $filter_arr['ultra_hd'] = 1;
        } else {
            $filter_arr['ultra_hd'] = 0;
        }

        if (in_array("2", $filter_default["sort_format"])) {
            $filter_arr['hd'] = 1;
        } else {
            $filter_arr['hd'] = 0;
        }

        if (in_array("1", $filter_default["sort_format"])) {
            $filter_arr['sd'] = 1;
        } else {
            $filter_arr['sd'] = 0;
        }


        //Brand available
        $filter_arr['brand_filter_name'] = array();
        if (in_array("1", $filter_default["brand"])) {
            $filter_arr['brand_filter_name'][] = "Nature Footage";
        }

        if (in_array("2", $filter_default["brand"])) {
            $filter_arr['brand_filter_name'][] = "NatureFlix";
        } */

        $filter_arr = array();
        $filter_arr['collection_filter'] = 1;
        $filter_arr['brand_filter'] = 1;
        $filter_arr['license_rm'] = 1;
        $filter_arr['license_rf'] = 1;
        $filter_arr['budget'] = 1;
        $filter_arr['standard'] = 1;
        $filter_arr['premium'] = 1;
        $filter_arr['gold'] = 1;
        $filter_arr['ultra_hd'] = 1;
        $filter_arr['hd'] = 1;
        $filter_arr['sd'] = 1;
        $filter_arr['offline'] = 1;
        $filter_arr['online'] = 1;
        $filter_arr['nature_footage'] = 1;
        $filter_arr['natureflix'] = 1;
        $filter_arr['clips_ids'] = array();

        $filter_arr['shot_type_arr'] = array();
        $filter_arr['subject_category_arr'] = array();
        $filter_arr['primary_type_arr'] = array();
        $filter_arr['other_subject_arr'] = array();
        $filter_arr['actions_arr'] = array();
        $filter_arr['time_arr'] = array();
        $filter_arr['concept_arr'] = array();
        $filter_arr['location_arr'] = array();
        $filter_arr['habitat_arr'] = array();
        $filter_arr['appearance_arr'] = array();

        $query = $this->db->query("SELECT id, keyword, section FROM lib_keywords lk WHERE lk.collection = 'Nature Footage'  ORDER BY keyword ASC");
        $filter_additional = $query->result_array();

        foreach ($filter_additional as $ft) {
            $filter_arr[$ft["section"] . '_arr'][] = $ft["id"] . "|" . $ft["keyword"];
        }


        $base_url = $this->config->base_url();

        $clipsRes = $this->get_clips_path(
            array_column($rows, 'id'),
            ($for_api ? ['thumb', 'preview', 'motion_thumb'] : ['thumb', 'preview', 'motion_thumb', 'res'])
        );

        foreach ($rows as &$row) {
            //$row['sql'] = htmlentities($sql);
            //$row['rating_result'] = $this->getRatingData($row['id'], $row['client_id']);
            //$keywords = $this->clips_model->getAllKeywordsByClipId($row['id']);
            //$row['keywords_types'] = $keywords;
            if ($for_api) {
                $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
            } else {
                $row['url'] = '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['download'] = $lang . '/clips/content/' . $row['id'];
                $row['metadata'] = $this->parseMetadata($row['metadata']);
            }

            // add resource location to result array
            if (!empty($clipsRes[$row['id']])) {
                $row += $clipsRes[$row['id']];
            }

            /* Dan ask to comment the license code
            $delivery_methods = $rf_delivery_methods = array(
                array(
                    'id' => 666,
                    'code' => 'Formats Container',
                    'title' => 'Formats Container',
                    'delivery' => 'Download',
                )
            );
            //For RF Clips
                        if ($row['license'] == 1) {
                            $query = $this->db->query('
                                SELECT do.id, do.description, do.delivery, do.resolution, pf.factor price_factor FROM lib_rf_delivery_options do
                                INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                                LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                                ORDER BY do.display_order');
                            $delivery_formats = $query->result_array();
                            if (count($delivery_formats)) {
                                foreach ($rf_delivery_methods as $key => $method) {
                                    foreach ($delivery_formats as $format) {
                                        $format['delivery'] = 'Download';
                                        if ($format['delivery'] == $method['delivery']) {
                                            if (!isset($rf_delivery_methods[$key]['formats'])) {
                                                $rf_delivery_methods[$key]['formats'] = array();
                                            }

                                            if (strpos(strtolower($format['description']), 'digital file') !== false) {
                                                $format['default'] = 1;
                                            }

                                            $description_parts = array();
                                            $description_parts = $this->_delivery_methods($row, $format);

                                            if ($description_parts) {
                                                $format['description'] = implode(' ', $description_parts);
                                            }
                                            $rf_delivery_methods[0]['formats'][0] = $format;
                                        }
                                    }
                                }
                                foreach ($rf_delivery_methods as $key => $method) {
                                    if (!isset($method['formats'])) {
                                        unset($rf_delivery_methods[$key]);
                                    }
                                }
                                $row['delivery_methods'] = $rf_delivery_methods;
                            } else {
                                $description_parts = array();
                                $description_parts = $this->_delivery_methods($row, $format);
                                if ($description_parts) {
                                    $format['description'] = $description_parts[0]; //implode(' ', $description_parts);
                                }

                                $rf_delivery_methods[0]['formats'][] = $format;
                                $row['delivery_methods'] = $rf_delivery_methods;
                            }
                        } //RM clips
                        else {
                            $query = $this->db->query('
                                SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
                                FROM lib_delivery_options do
                                INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                                LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                                ORDER BY do.display_order');
                            $delivery_formats = $query->result_array();
                            if (count($delivery_formats)) {
                                foreach ($delivery_methods as $key => $method) {
                                    foreach ($delivery_formats as $format) {
                                        $format['first_description'] = $format['description'];
                                        if (true || $format['delivery'] == $method['delivery']) {
                                            if (!isset($delivery_methods[$key]['formats'])) {
                                                $delivery_methods[$key]['formats'] = array();
                                            }
                                            if (strpos(strtolower($format['description']), 'custom frame rate') === false) {

                                                if (strpos(strtolower($format['description']), 'digital file') !== false) {
                                                    $format['default'] = 1;
                                                }

                                                $description_parts = array();
                                                $description_parts = $this->_delivery_methods($row, $format);

                                                if ($description_parts) {
                                                    $format['description'] = implode(' ', $description_parts);
                                                }
                                            } else {
                                                if (!isset($custom_frame_rates)) {
                                                    $custom_frame_rates = array();
                                                    if (!isset($custom_frame_rates[$format['destination']])) {
                                                        $this->db->where('media', $format['destination']);
                                                        $custom_frame_rates[$format['destination']] = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
                                                    }
                                                }
                                                $format['custom_frame_rates'] = $custom_frame_rates[$format['destination']];
                                            }
                                            if (!trim($format['description']))
                                                $format['description'] = $format['first_description'];
                                            $delivery_methods[$key]['formats'][] = $format;
                                        }
                                    }
                                }
                                foreach ($delivery_methods as $key => $method) {
                                    if (!isset($method['formats'])) {
                                        unset($delivery_methods[$key]);
                                    }
                                }
                                $row['delivery_methods'] = $delivery_methods;
                            } else {
                                $description_parts = array();
                                $description_parts = $this->_delivery_methods($row, $format);
                                if ($description_parts) {
                                    $format['description'] = implode(' ', $description_parts);
                                }
                                if (isset($format['description']))
                                    $rf_delivery_methods[]['formats'][] = $format;
                                $row['delivery_methods'] = $rf_delivery_methods;
                            }
                        }
            end Dan ask to comment the license code */

            /*            $col_filter = '';
                        if ($row['collection'] == 'Nature Footage') {
                            $col_filter = 'Land';
                        } elseif ($row['collection'] == 'Ocean Footage') {
                            $col_filter = 'Ocean';
                        } elseif ($row['collection'] = 'Adventure Footage') {
                            $col_filter = 'Adventure';
                        }

                        if (!in_array($col_filter, $filter_arr['collection_filter_name'])) {
                            array_push($filter_arr['collection_filter_name'], $col_filter);
                        }*/

            /*            if (!in_array($row['brand'], $filter_arr['brand_filter_name'])) {
                            array_push($filter_arr['brand_filter_name'], $row['brand']);
                        }*/
            /*
                        $arrayShot = explode(', ', $row['shot_type']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'shot_type')) {
                                    array_push($filter_arr['shot_type_arr'], $value);
                                }
                            }
                        }


                        $arrayShot = explode(', ', $row['subject_category']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'subject_category')) {
                                    array_push($filter_arr['subject_cat_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['primary_subject']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'primary_subject')) {
                                    array_push($filter_arr['primary_type_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['other_subject']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'other_subject')) {
                                    array_push($filter_arr['other_subject_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['appearance']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'appearance')) {
                                    array_push($filter_arr['appearance_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['actions']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'actions')) {
                                    array_push($filter_arr['actions_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['time']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'time')) {
                                    array_push($filter_arr['time_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['concept']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'concept')) {
                                    array_push($filter_arr['concept_arr'], $value);
                                }
                            }
                        }


                        $arrayShot = explode(', ', $row['location']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'location')) {
                                    array_push($filter_arr['loctaion_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['habitat']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'habitat')) {
                                    array_push($filter_arr['habitat_arr'], $value);
                                }
                            }
                        }


                        if ($row['license'] == '2' && !empty($row['license'])) {
                            $filter_arr['license_rm'] = 1;
                        }
                        if ($row['license'] == '1' && !empty($row['license'])) {
                            $filter_arr['license_rf'] = 1;
                        }
                        if ($row['price_level'] == '1' && !empty($row['price_level'])) {
                            $filter_arr['budget'] = 1;
                        }
                        if ($row['price_level'] == '2' && !empty($row['price_level'])) {
                            $filter_arr['standard'] = 1;
                        }
                        if ($row['price_level'] == '3' && !empty($row['price_level'])) {
                            $filter_arr['premium'] = 1;
                        }
                        if ($row['price_level'] == '4' && !empty($row['price_level'])) {
                            $filter_arr['gold'] = 1;
                        }
                        if (strpos($row['master_frame_size'], '3D') !== false && !empty($row['master_frame_size'])) {
                            $filter_arr['3d'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'Ultra HD') !== false || strpos($row['master_frame_size'], 'Ultra HD') !== false || strpos($row['digital_file_frame_size'], 'Ultra HD') !== false) {
                            $filter_arr['ultra_hd'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'HD') !== false || strpos($row['master_frame_size'], 'HD') !== false || strpos($row['digital_file_frame_size'], 'HD') !== false) {
                            $filter_arr['hd'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'SD') !== false || strpos($row['master_frame_size'], 'SD') !== false || strpos($row['digital_file_frame_size'], 'SD') !== false) {
                            $filter_arr['sd'] = 1;
                        }
                        if ($row['active'] == '1' && !empty($row['active'])) {
                            $filter_arr['online'] = 1;
                        }
                        if ($row['active'] == '0' && !empty($row['active'])) {
                            $filter_arr['offline'] = 1;
                        }
            */

            $row['returnFiltersResults'] = $filter_arr;
        }
        //$this->duration_logger->save('foreach', 'clips_model::get_clips_list|foreach');
        return $rows;
    }

    function get_clips_list_backend($lang = 'en', $filter = '', $order = '', $limit = null, $for_api = false)
    {
//        echo '<pre>';
//        print_r($filter);
//        echo '</pre>';

        $this->build_filter_sql_backend($filter);
        $this->load->model('users_model');
        if ($this->filter_sql || empty($filter) || is_array($filter)) {
            $filter = '';
        }
        if (empty($this->filter_sql)) {
            $this->filter_sql = ' WHERE 1 ';
        }

        $order = " GROUP BY c.id " . $order;
        if (!$for_api) {

            $sql = "SELECT c.id,c.client_id,c.code,c.license,c.duration,c.audio_video,c.metadata,c.original_filename,c.release_file,
                    c.creation_date,c.film_date,c.type,c.active,c.ctime,c.brand,
                    c.price_level,c.pricing_category,c.source_format,c.source_frame_size,c.master_frame_size,c.digital_file_frame_size,c.source_frame_rate,c.digital_file_frame_rate,c.digital_file_format,
                    c.license_restrictions,c.admin_action, c.code AS title, c.description,c.location " .
                //,cc.shot_type,cc.subject_category,cc.actions,cc.appearance,cc.time,cc.habitat,cc.concept
                "FROM lib_clips c " . $this->filter_sql . " " . $filter . " " . $order . " " . $limit;
            $query = $this->db->query($sql, $lang);
        } else {
            $sql = "SELECT c.id,c.client_id,c.code,c.license,c.duration,c.audio_video,c.metadata,c.original_filename,c.release_file,
                    c.creation_date,c.film_date,c.type,c.active,c.ctime,c.brand,
                    c.price_level,c.pricing_category,c.source_format,c.source_frame_size,c.master_frame_size,c.digital_file_frame_size,c.source_frame_rate,c.digital_file_frame_rate,c.digital_file_format,
                    c.license_restrictions,c.admin_action, c.code AS title, c.description,c.location " .
                //,cc.shot_type,cc.subject_category,cc.actions,cc.appearance,cc.time,cc.habitat,cc.concept
                "FROM lib_clips c " . $this->filter_sql . " " . $filter . " " . $order . " " . $limit;
            $query = $this->db->query($sql, $lang);
        }
        //return $sql;
        $rows = $query->result_array();

        $filter_arr = array();
        $filter_arr['collection_filter'] = 1;
        $filter_arr['brand_filter'] = 1;
        $filter_arr['license_rm'] = 1;
        $filter_arr['license_rf'] = 1;
        $filter_arr['budget'] = 1;
        $filter_arr['standard'] = 1;
        $filter_arr['premium'] = 1;
        $filter_arr['gold'] = 1;
        $filter_arr['3d'] = 1;
        $filter_arr['ultra_hd'] = 1;
        $filter_arr['hd'] = 1;
        $filter_arr['sd'] = 1;
        $filter_arr['offline'] = 1;
        $filter_arr['online'] = 1;
        $filter_arr['collection_filter_name'] = array();
        $filter_arr['brand_filter_name'] = array();
        $filter_arr['clips_ids'] = array();

        $filter_arr['shot_type_arr'] = array();
        $filter_arr['subject_cat_arr'] = array();
        $filter_arr['primary_type_arr'] = array();
        $filter_arr['other_subject_arr'] = array();
        $filter_arr['actions_arr'] = array();
        $filter_arr['time_arr'] = array();
        $filter_arr['concept_arr'] = array();
        $filter_arr['loctaion_arr'] = array();
        $filter_arr['habitat_arr'] = array();
        $filter_arr['appearance_arr'] = array();


        $base_url = $this->config->base_url();

        $clipsRes = $this->get_clips_path(
            array_column($rows, 'id'),
            ($for_api ? ['thumb', 'preview', 'motion_thumb'] : ['thumb', 'preview', 'motion_thumb', 'res'])
        );

        foreach ($rows as &$row) {
            $row['sql'] = htmlentities($sql);
            $row['rating_result'] = $this->getRatingData($row['id'], $row['client_id']);
            $keywords = $this->clips_model->getAllKeywordsByClipId($row['id']);
            $row['keywords_types'] = $keywords;

            $user_data = $this->users_model->get_user($row['client_id']);
            $row['fname'] = $user_data['fname'];
            $row['lname'] = $user_data['lname'];
            if ($for_api) {
                $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
                $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
            } else {
                $row['url'] = '/clips/' . $row['id'] . $this->config->item('url_suffix');
                //$row['download'] = $lang . '/clips/content/' . $row['id'];
                $row['download'] = $lang . '/clips/download/' . $row['id'];
                $row['metadata'] = $this->parseMetadata($row['metadata']);
            }

            // add resource location to result array
            if (!empty($clipsRes[$row['id']])) {
                $row += $clipsRes[$row['id']];
            }
//
//            $delivery_methods = $rf_delivery_methods = array(
//                array(
//                    'id' => 666,
//                    'code' => 'Formats Container',
//                    'title' => 'Formats Container',
//                    'delivery' => 'Download',
//                )
//            );
//
////For RF Clips
//            if ($row['license'] == 1) {
//                $query = $this->db->query('
//                    SELECT do.id, do.description, do.delivery, do.resolution, pf.factor price_factor FROM lib_rf_delivery_options do
//                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
//                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
//                    ORDER BY do.display_order');
//                $delivery_formats = $query->result_array();
//                if (count($delivery_formats)) {
//                    foreach ($rf_delivery_methods as $key => $method) {
//                        foreach ($delivery_formats as $format) {
//                            $format['delivery'] = 'Download';
//                            if ($format['delivery'] == $method['delivery']) {
//                                if (!isset($rf_delivery_methods[$key]['formats'])) {
//                                    $rf_delivery_methods[$key]['formats'] = array();
//                                }
//
//                                if (strpos(strtolower($format['description']), 'digital file') !== false) {
//                                    $format['default'] = 1;
//                                }
//
//                                $description_parts = array();
//                                $description_parts = $this->_delivery_methods($row, $format);
//
//                                if ($description_parts) {
//                                    $format['description'] = implode(' ', $description_parts);
//                                }
//                                $rf_delivery_methods[0]['formats'][0] = $format;
//                            }
//                        }
//                    }
//                    foreach ($rf_delivery_methods as $key => $method) {
//                        if (!isset($method['formats'])) {
//                            unset($rf_delivery_methods[$key]);
//                        }
//                    }
//                    $row['delivery_methods'] = $rf_delivery_methods;
//                } else {
//                    $description_parts = array();
//                    $description_parts = $this->_delivery_methods($row, $format);
//                    if ($description_parts) {
//                        $format['description'] = $description_parts[0]; //implode(' ', $description_parts);
//                    }
//
//                    $rf_delivery_methods[0]['formats'][] = $format;
//                    $row['delivery_methods'] = $rf_delivery_methods;
//                }
//            } //RM clips
//            else {
//                $query = $this->db->query('
//                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
//                    FROM lib_delivery_options do
//                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
//                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
//                    ORDER BY do.display_order');
//                $delivery_formats = $query->result_array();
//                if (count($delivery_formats)) {
//                    foreach ($delivery_methods as $key => $method) {
//                        foreach ($delivery_formats as $format) {
//                            $format['first_description'] = $format['description'];
//                            if (true || $format['delivery'] == $method['delivery']) {
//                                if (!isset($delivery_methods[$key]['formats'])) {
//                                    $delivery_methods[$key]['formats'] = array();
//                                }
//                                if (strpos(strtolower($format['description']), 'custom frame rate') === false) {
//
//                                    if (strpos(strtolower($format['description']), 'digital file') !== false) {
//                                        $format['default'] = 1;
//                                    }
//
//                                    $description_parts = array();
//                                    $description_parts = $this->_delivery_methods($row, $format);
//
//                                    if ($description_parts) {
//                                        $format['description'] = implode(' ', $description_parts);
//                                    }
//                                } else {
//                                    if (!isset($custom_frame_rates)) {
//                                        $custom_frame_rates = array();
//                                        if (!isset($custom_frame_rates[$format['destination']])) {
//                                            $this->db->where('media', $format['destination']);
//                                            $custom_frame_rates[$format['destination']] = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
//                                        }
//                                    }
//                                    $format['custom_frame_rates'] = $custom_frame_rates[$format['destination']];
//                                }
//                                if (!trim($format['description']))
//                                    $format['description'] = $format['first_description'];
//                                $delivery_methods[$key]['formats'][] = $format;
//                            }
//                        }
//                    }
//                    foreach ($delivery_methods as $key => $method) {
//                        if (!isset($method['formats'])) {
//                            unset($delivery_methods[$key]);
//                        }
//                    }
//                    $row['delivery_methods'] = $delivery_methods;
//                } else {
//                    $description_parts = array();
//                    $description_parts = $this->_delivery_methods($row, $format);
//                    if ($description_parts) {
//                        $format['description'] = implode(' ', $description_parts);
//                    }
//                    if (isset($format['description']))
//                        $rf_delivery_methods[]['formats'][] = $format;
//                    $row['delivery_methods'] = $rf_delivery_methods;
//                }
//            }
//
//            $col_filter = '';
//            if ($row['collection'] == 'Nature Footage') {
//                $col_filter = 'Land';
//            } elseif ($row['collection'] == 'Ocean Footage') {
//                $col_filter = 'Ocean';
//            } elseif ($row['collection'] = 'Adventure Footage') {
//                $col_filter = 'Adventure';
//            }
//
//
//            if (!in_array($col_filter, $filter_arr['collection_filter_name'])) {
//                array_push($filter_arr['collection_filter_name'], $col_filter);
//            }
//            if (!in_array($row['brand'], $filter_arr['brand_filter_name'])) {
//                array_push($filter_arr['brand_filter_name'], $row['brand']);
//            }
            /*
                        $arrayShot = explode(', ', $row['shot_type']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'shot_type')) {
                                    array_push($filter_arr['shot_type_arr'], $value);
                                }
                            }
                        }


                        $arrayShot = explode(', ', $row['subject_category']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'subject_category')) {
                                    array_push($filter_arr['subject_cat_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['primary_subject']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'primary_subject')) {
                                    array_push($filter_arr['primary_type_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['other_subject']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'other_subject')) {
                                    array_push($filter_arr['other_subject_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['appearance']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'appearance')) {
                                    array_push($filter_arr['appearance_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['actions']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'actions')) {
                                    array_push($filter_arr['actions_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['time']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'time')) {
                                    array_push($filter_arr['time_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['concept']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'concept')) {
                                    array_push($filter_arr['concept_arr'], $value);
                                }
                            }
                        }


                        $arrayShot = explode(', ', $row['location']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'location')) {
                                    array_push($filter_arr['loctaion_arr'], $value);
                                }
                            }
                        }

                        $arrayShot = explode(', ', $row['habitat']);
                        if (!empty($arrayShot)) {
                            foreach ($arrayShot as $value) {
                                if ($val = $this->checkIfKeywordExistsInMaster($value, 'habitat')) {
                                    array_push($filter_arr['habitat_arr'], $value);
                                }
                            }
                        }


                        if ($row['license'] == '2' && !empty($row['license'])) {
                            $filter_arr['license_rm'] = 1;
                        }
                        if ($row['license'] == '1' && !empty($row['license'])) {
                            $filter_arr['license_rf'] = 1;
                        }
                        if ($row['price_level'] == '1' && !empty($row['price_level'])) {
                            $filter_arr['budget'] = 1;
                        }
                        if ($row['price_level'] == '2' && !empty($row['price_level'])) {
                            $filter_arr['standard'] = 1;
                        }
                        if ($row['price_level'] == '3' && !empty($row['price_level'])) {
                            $filter_arr['premium'] = 1;
                        }
                        if ($row['price_level'] == '4' && !empty($row['price_level'])) {
                            $filter_arr['gold'] = 1;
                        }
                        if (strpos($row['master_frame_size'], '3D') !== false && !empty($row['master_frame_size'])) {
                            $filter_arr['3d'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'Ultra HD') !== false || strpos($row['master_frame_size'], 'Ultra HD') !== false || strpos($row['digital_file_frame_size'], 'Ultra HD') !== false) {
                            $filter_arr['ultra_hd'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'HD') !== false || strpos($row['master_frame_size'], 'HD') !== false || strpos($row['digital_file_frame_size'], 'HD') !== false) {
                            $filter_arr['hd'] = 1;
                        }
                        if (strpos($row['source_frame_size'], 'SD') !== false || strpos($row['master_frame_size'], 'SD') !== false || strpos($row['digital_file_frame_size'], 'SD') !== false) {
                            $filter_arr['sd'] = 1;
                        }
                        if ($row['active'] == '1' && !empty($row['active'])) {
                            $filter_arr['online'] = 1;
                        }
                        if ($row['active'] == '0' && !empty($row['active'])) {
                            $filter_arr['offline'] = 1;
                        }
            */

            $row['returnFiltersResults'] = $filter_arr;
        }
        //$this->duration_logger->save('foreach', 'clips_model::get_clips_list|foreach');
        return $rows;
    }

    /**
     * Get clips filters for Search Clips
     * @used \Fapi::clips_post
     * @deprecated Not used !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * @param string $lang
     * @param string $filter
     * @param string $order
     * @param null $limit
     * @param bool|false $for_api
     * @return array
     */
    function get_clips_list_filters($lang = 'en', $filter = '', $order = '', $limit = null, $for_api = false)
    {
        if ($this->filter_sql || empty($filter)) {
            $filter = '';
        }

        $queryEmail = 'SELECT DISTINCT  c.id,c.license,c.price_level,c.master_frame_size,c.active, cc.title, cc.description, cc.location,cc.shot_type,cc.subject_category,cc.actions,cc.appearance,cc.time,cc.habitat,cc.concept

                FROM lib_clips c
                INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=? '
            . $this->filter_sql . $filter;

//   mail("imranmani.numl@gmail.com", "Query Success", $queryEmail);


        $query = $this->db->query('SELECT DISTINCT  c.collection,c.id,c.license,c.price_level,c.master_frame_size,c.active, cc.title, cc.description, cc.location,cc.shot_type,cc.subject_category,cc.actions,cc.appearance,cc.time,cc.habitat,cc.concept

                FROM lib_clips c
                INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=? '
            . $this->filter_sql . $filter . $limit . ' ', $lang);
//$order;

        $rows = $query->result_array();
        print_r($rows);
//echo $this->db->last_query();
        /* $query = $this->db->query('SELECT distinct(li.id), li.*, DATE_FORMAT(li.ctime, \'%d.%m.%Y %T\') ctime,
          lc.title, lc.description, lc.keywords, lu.login folder
          FROM lib_users lu, lib_clips_cats lic
          RIGHT JOIN lib_clips li ON li.id=lic.clip_id
          LEFT JOIN lib_clips_content lc ON li.id=lc.clip_id AND lc.lang=' . $this->db->escape($lang) . '
          WHERE lu.id=li.client_id ' . $filter . $order . $limit);
          $rows = $query->result_array(); */
        $base_url = $this->config->base_url();


        $filter_arr = array();
        $filter_arr['collection_filter'] = 0;
        $filter_arr['brand_filter'] = 0;
        $filter_arr['license_rm'] = 0;
        $filter_arr['license_rf'] = 0;
        $filter_arr['budget'] = 0;
        $filter_arr['standard'] = 0;
        $filter_arr['premium'] = 0;
        $filter_arr['gold'] = 0;
        $filter_arr['3d'] = 0;
        $filter_arr['ultra_hd'] = 0;
        $filter_arr['hd'] = 0;
        $filter_arr['sd'] = 0;
        $filter_arr['offline'] = 0;
        $filter_arr['online'] = 0;
        $filter_arr['collection_filter_name'] = array();
        $filter_arr['brand_filter_name'] = array();
        $filter_arr['clips_ids'] = array();

        $filter_arr['shot_type_arr'] = array();
        $filter_arr['subject_cat_arr'] = array();
        $filter_arr['primary_type_arr'] = array();
        $filter_arr['other_subject_arr'] = array();
        $filter_arr['actions_arr'] = array();
        $filter_arr['time_arr'] = array();
        $filter_arr['concept_arr'] = array();
        $filter_arr['loctaion_arr'] = array();
        $filter_arr['habitat_arr'] = array();
        $filter_arr['appearance_arr'] = array();


        foreach ($rows as $key => $row) {

//array_push($filter_arr['clips_ids'], "'" . $row['id'] . "'");
//            $arrayShot = explode(',', $row['shot_type']);
//            if (!empty($arrayShot)) {
//                foreach ($arrayShot as $rowData) {
//                    if (!in_array($rowData, $filter_arr['shot_type_arr']) && $rowData != '') {
//                        array_push($filter_arr['shot_type_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot1 = explode(',', $row['subject_category']);
//            if (!empty($arrayShot1)) {
//                foreach ($arrayShot1 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['subject_cat_arr']) && $rowData != '') {
//                        array_push($filter_arr['subject_cat_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot2 = explode(',', $row['primary_subject']);
//            if (!empty($arrayShot2)) {
//                foreach ($arrayShot2 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['primary_type_arr']) && $rowData != '') {
//                        array_push($filter_arr['primary_type_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot3 = explode(',', $row['other_subject']);
//            if (!empty($arrayShot3)) {
//                foreach ($arrayShot3 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['other_subject_arr']) && $rowData != '') {
//                        array_push($filter_arr['other_subject_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot4 = explode(',', $row['appearance']);
//            if (!empty($arrayShot4)) {
//                foreach ($arrayShot4 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['appearance_arr']) && $rowData != '') {
//                        array_push($filter_arr['appearance_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot5 = explode(',', $row['actions']);
//            if (!empty($arrayShot5)) {
//                foreach ($arrayShot5 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['actions_arr']) && $rowData != '') {
//                        array_push($filter_arr['actions_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot6 = explode(',', $row['time']);
//            if (!empty($arrayShot6)) {
//                foreach ($arrayShot6 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['time_arr']) && $rowData != '') {
//                        array_push($filter_arr['time_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot7 = explode(',', $row['concept']);
//            if (!empty($arrayShot7)) {
//                foreach ($arrayShot7 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['concept_arr']) && $rowData != '') {
//                        array_push($filter_arr['concept_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot8 = explode(',', $row['location']);
//            if (!empty($arrayShot8)) {
//                foreach ($arrayShot8 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['location_arr']) && $rowData != '') {
//                        array_push($filter_arr['location_arr'], $rowData);
//                    }
//                }
//            }
//
//            $arrayShot9 = explode(',', $row['habitat']);
//            if (!empty($arrayShot9)) {
//                foreach ($arrayShot9 as $rowData) {
//                    if (!in_array($rowData, $filter_arr['habitat_arr']) && $rowData != '') {
//                        array_push($filter_arr['habitat_arr'], $rowData);
//                    }
//                }
//            }$arrayShot = explode(', ', $row['subject_category']);
//            $filter_arr['subject_cat_arr'] = array_unique(array_merge($arrayShot, $filter_arr['subject_cat_arr']), SORT_REGULAR);
//
//
//            $arrayShot = explode(',', $row['primary_subject']);
//            $filter_arr['primary_type_arr'] = array_unique(array_merge($arrayShot, $filter_arr['primary_type_arr']), SORT_REGULAR);
//            $arrayShot = explode(', ', $row['other_subject']);
//            $filter_arr['other_subject_arr'] = array_unique(array_merge($arrayShot, $filter_arr['other_subject_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['appearance']);
//            $filter_arr['appearance_arr'] = array_unique(array_merge($arrayShot, $filter_arr['appearance_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['actions']);
//            $filter_arr['actions_arr'] = array_unique(array_merge($arrayShot, $filter_arr['actions_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['time']);
//            $filter_arr['time_arr'] = array_unique(array_merge($arrayShot, $filter_arr['time_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['concept']);
//            $filter_arr['concept_arr'] = array_unique(array_merge($arrayShot, $filter_arr['concept_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['location']);
//            $filter_arr['loctaion_arr'] = array_unique(array_merge($arrayShot, $filter_arr['loctaion_arr']), SORT_REGULAR);
//            $arrayShot = explode(',', $row['habitat']);
//            $filter_arr['habitat_arr'] = array_unique(array_merge($arrayShot, $filter_arr['habitat_arr']), SORT_REGULAR);


            if (!in_array($row['collection'], $filter_arr['collection_filter_name'])) {
                array_push($filter_arr['collection_filter_name'], $row['collection']);
            }
            if (!in_array($row['brand'], $filter_arr['brand_filter_name'])) {
                array_push($filter_arr['brand_filter_name'], $row['brand']);
            }

            $arrayShot = explode(',', $row['shot_type']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'shot_type')) {
                    array_push($filter_arr['shot_type_arr'], $value);
                }
            }


            $arrayShot = explode(',', $row['subject_category']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'subject_category')) {
                    array_push($filter_arr['subject_cat_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['primary_subject']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'primary_subject')) {
                    array_push($filter_arr['primary_type_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['other_subject']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'other_subject')) {
                    array_push($filter_arr['other_subject_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['appearance']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'appearance')) {
                    array_push($filter_arr['appearance_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['actions']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'actions')) {
                    array_push($filter_arr['actions_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['time']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'time')) {
                    array_push($filter_arr['time_arr'], $value);
                }
            }

            $arrayShot = explode(',', $row['concept']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'concept')) {
                    array_push($filter_arr['concept_arr'], $value);
                }
            }


            $arrayShot = explode(',', $row['location']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'location')) {
                    array_push($filter_arr['loctaion_arr'], $value);
                }
            }


            $arrayShot = explode(',', $row['habitat']);
            foreach ($arrayShot as $value) {
                if ($val = $this->checkIfKeywordExistsInMaster($value, 'habitat')) {
                    array_push($filter_arr['habitat_arr'], $value);
                }
            }


            if ($row['license'] == '2' && !empty($row['license'])) {
                $filter_arr['license_rm'] = 1;
            }
            if ($row['license'] == '1' && !empty($row['license'])) {
                $filter_arr['license_rf'] = 1;
            }
            if ($row['price_level'] == '1' && !empty($row['price_level'])) {
                $filter_arr['budget'] = 1;
            }
            if ($row['price_level'] == '2' && !empty($row['price_level'])) {
                $filter_arr['standard'] = 1;
            }
            if ($row['price_level'] == '3' && !empty($row['price_level'])) {
                $filter_arr['premium'] = 1;
            }
            if ($row['price_level'] == '4' && !empty($row['price_level'])) {
                $filter_arr['gold'] = 1;
            }
            if (strpos($row['master_frame_size'], '3D') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['3d'] = 1;
            }
            if (strpos($row['master_frame_size'], 'Ultra HD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['ultra_hd'] = 1;
            }
            if (strpos($row['master_frame_size'], 'HD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['hd'] = 1;
            }
            if (strpos($row['master_frame_size'], 'SD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['sd'] = 1;
            }
            if ($row['active'] == '1' && !empty($row['active'])) {
                $filter_arr['online'] = 1;
            }
            if ($row['active'] == '0' && !empty($row['active'])) {
                $filter_arr['offline'] = 1;
            }
        }


        return $filter_arr;
    }

    /**
     * @deprecated - Not use !!!!
     * @param array $filter
     * @return array
     */
    function get_clips_ids($filter = array())
    {
        $ids = array();
        $this->db->select('id');
        foreach ($filter as $name => $value) {
            switch ($name) {
                case 'id':
                    if (is_array($value))
                        $this->db->where_in('id', $value);
                    else
                        $this->db->where('id', (int)$value);
                    break;
            }
        }
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        if ($res) {
            foreach ($res as $clip) {
                $ids[] = $clip['id'];
            }
        }
        return $ids;
    }

    function get_active_clips_count()
    {
        $sql = "SELECT COUNT(id) AS total FROM `lib_clips` WHERE active=1";
        $count = $this->db->query($sql)->result_array();
        return $count;
    }

    function get_clips_xml($limit, $offset)
    {
//        $this->db->select('id, code, description, keywords, creation_date, digital_file_frame_size, source_format, duration');
//        $this->db->where('active', '1');
//        $this->db->limit($limit, $offset);
//        $query = $this->db->get('lib_clips');
//        $res = $query->result_array();
        $sql = "SELECT c.id, c.code, c.description, c.keywords, c.creation_date, c.digital_file_frame_size, c.source_format, c.duration, cr_p.location AS res, cr_t.location AS thumb FROM lib_clips AS c
LEFT JOIN lib_clips_res AS cr_p ON c.id=cr_p.clip_id
LEFT JOIN lib_clips_res AS cr_t ON c.id=cr_t.clip_id
JOIN (SELECT id FROM lib_clips WHERE active=1 ORDER BY id ASC LIMIT " . $offset . ", " . $limit . ") AS lim ON lim.id = c.id
WHERE c.active=1 AND cr_p.type=1 AND cr_t.type=0 AND cr_t.resource='jpg'";
        $res = $this->db->query($sql)->result_array();

        return $res;
    }

    function get_new_clips_xml($creation_date)
    {
        $sql = "SELECT c.id, c.code, cr_p.location, cr_t.location FROM lib_clips AS c 
LEFT JOIN lib_clips_res AS cr_p ON c.id=cr_p.clip_id
LEFT JOIN lib_clips_res AS cr_t ON c.id=cr_t.clip_id
WHERE c.active=0 AND cr_p.type=1 AND cr_t.type=0 AND cr_t.resource='jpg' AND c.creation_date > '" . $creation_date . "'";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    /**
     * @deprecated Not Used !!!
     * @return mixed
     */
    function get_clip_clients_list()
    {
        $query = $this->db->query(
            'select DISTINCT u.id as id, u.login as name from lib_clips as l
      inner join lib_users as u on l.client_id=u.id');
        $rows = $query->result_array();

        return $rows;
    }

    /**
     * Active/Deactive Clips in DB Table: lib_clips
     * @param $ids
     */
    function change_visible($ids)
    {
        if (count($ids))
            foreach ($ids as $id)
                $this->db_master->query('UPDATE lib_clips set active = !active where id=' . $id);
    }

    /**
     * Set Active/Deactive Clips And Update SOLR in DB Table: lib_clips
     * @param $visible_status
     * @param $ids
     */
    function set_visible($visible_status, $ids)
    {

        $ids_reindex = array();

        foreach ($ids as $id) {

            $clip_status = $this->db->query("SELECT active FROM lib_clips WHERE id = '" . $id . "' LIMIT 1")->result_array();


            if ($visible_status == 3) {

                $querySelect = $this->db->query('select * from lib_clips  WHERE  id=' . $id);
                $rowSelect = $querySelect->result_array();
                $dataUpdateArray = array();

                if ($rowSelect[0]['description'] == '' || $rowSelect[0]['license'] == '' || $rowSelect[0]['price_level'] == '' || $rowSelect[0]['digital_file_format'] == '' || $rowSelect[0]['digital_file_frame_size'] == '' || $rowSelect[0]['digital_file_frame_rate'] == '') {
                    $dataUpdateArray['active'] = 0;
                } else {
                    $dataUpdateArray['active'] = 1;
                }

                if ($dataUpdateArray['active'] == 1) {

                    $query3 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $id, 'section_id' => 'location'));
                    $clip_status3 = $query3->result_array();

                    if ($clip_status3[0]['keyword'] != '') {
                        $dataUpdateArray['active'] = 1;
                    } else {
                        $dataUpdateArray['active'] = 0;
                    }
                }


                if ($dataUpdateArray['active'] == 1) {

                    $query2 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $id, 'section_id' => 'category'));
                    $clip_status2 = $query2->result_array();
                    if ($clip_status2[0]['keyword'] != '') {
                        $dataUpdateArray['active'] = 1;
                    } else {
                        $dataUpdateArray['active'] = 0;
                    }


                }

                $this->db_master->where('id', $id);
                $this->db_master->update('lib_clips', $dataUpdateArray);
            } else {


                /* if((($clip_status[0]['active'] == 2) AND (($visible_status == 0) OR ($visible_status == 1)))
                  OR (($visible_status == 4) AND ($clip_status[0]['active'] != 2))){

                  }else{

                  if(($clip_status[0]['active'] == 2) AND ($visible_status == 4)){
                  $visible_status_var = 1;
                  }else{
                  $visible_status_var = $visible_status;
                  }


                  $this->db_master->where_in('id', $id);
                  $this->db_master->update('lib_clips', array('active' => $visible_status_var));
                  $ids_reindex[] = $id;
                  } */
                $visible_status_var = (intval($visible_status) && (intval($visible_status) % 2)) ? 1 : 0;
                $visible_status_var = ($visible_status == 2) ? 2 : $visible_status_var;
                if ($clip_status[0]['active'] != $visible_status_var) {
                    $this->db_master->where_in('id', $id);
                    $this->db_master->update('lib_clips', array('active' => $visible_status_var));
                    $ids_reindex[] = $id;
                }

            }


        }

        if (count($ids_reindex) != 0) {
            $this->add_to_index($ids_reindex);
        }
        //if($visible_status)
        //else
        //    $this->delete_from_index($ids);

        /*
          $this->db_master->where_in('id', $ids);
          $this->db_master->update('lib_clips', array('active' => $visible_status));
          $this->add_to_index($ids);
         */
    }

    /**
     * Remove clips
     * @used \Clips::delete
     * @param $ids
     * @param $lang
     */
    function delete_clips($ids, $lang)
    {
        if (count($ids)) {

            try{
                $this->load->model('aws3_sqs_delete_resources_model');
                foreach ($ids as $id) {
                    $this->delete_resource($id, '*');
                    $this->delete_thumbs_from_s3($id);
                }
                // these tables have different key type: lib_clips.id - unsigned int, clip_id's on these tables - int
                $this->delete_clips_data_in_database($ids);
            } catch (\Exception $e)
            {
                error_log($e->getMessage());
            }
        }
        $this->delete_from_index($ids);
    }

    /**
     * @param array $ids
     */
    private function delete_clips_data_in_database($ids)
    {
        $this->db_master->where_in('clip_id', $ids);
        $this->db_master->delete(['lib_clips_res', 'lib_clips_delivery_formats', 'lib_clips_extra_statistic', 'lib_clips_keywords', 'lib_rank_clips', 'lib_thumbnails', 'lib_thumbnails_new']);

        if($codes = $this->get_clip_codes($ids)){
            $this->db_master->where_in('code', $codes);
            $this->db_master->delete('lib_clip_rating');
        }
        $this->db_master->where_in('id', $ids);
        $this->db_master->delete(['lib_clips_metadata', 'lib_clips']);

    }

    /**
     * Remove frames clip by ID
     * @see \Clips_model::create_resource
     * @param $id
     */
    function delete_frames($id)
    {
        $this->db_master->update('lib_clips', array('frames_count' => 0), array('id' => $id));
        $dir = $this->config->item('clip_dir');
        $frames_dir = $dir . 'frames/clip_' . $id;
        if (is_dir($frames_dir)) {
            foreach (glob($frames_dir . '/*') as $dir_file) {
                if (is_dir($dir_file))
                    rmdir($dir_file);
                else
                    unlink($dir_file);
            }
            rmdir($frames_dir);
        }
    }

    function delete_keywording_fragment($id)
    {
        $this->db_master->delete('lib_keywording_fragment', array('clip_id' => $id));
    }

    function get_clip_code($id)
    {
        $row = $this->db->query('SELECT code FROM lib_clips WHERE id = ?', array($id))->result_array();
        return $row[0]['code'];
    }

    /**
     * @param array $ids
     * @return mixed
     */
    function get_clip_codes($ids)
    {
        $this->db->select('code');

        $query = $this->db->get('lib_clips', ['id' => $ids]);

        $codes = [];

        foreach ($query->result() as $row)
        {
            $codes[] = $row->code;
        }
        return $codes;
    }

    function get_clip_id_by_code($code)
    {
        $row = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', array($code))->result_array();
        return $row[0]['id'];
    }

    function get_ids_by_codes($codesStr)
    {
        $rows = $this->db->query('SELECT id,code FROM lib_clips WHERE code IN (' . $codesStr . ')')->result_array();
        return $rows;
    }

    function get_thumb_type_by_place($place)
    {
        if(isset($place) && isset($this->store['thumb'][$place]['type']))
            return $this->store['thumb'][$place]['type'];

        return self::DEFAULT_THUMB_TYPE;
    }

    /**
     * Get clip by ID Full
     * @param $id
     * @return mixed
     */
    function get_clip($id)
    {
        $query = 'SELECT c.*,lb.is_sequence, lb.is_default,lb.title AS sequence_name, lbi.backend_lb_id, (SELECT id FROM lib_backend_lb lb WHERE client_id=? AND is_default=1) AS default_clipbin
          FROM lib_clips c
          LEFT JOIN lib_backend_lb_items lbi ON c.id=lbi.item_id
          LEFT JOIN lib_backend_lb lb ON lb.id=lbi.backend_lb_id
          WHERE c.id=? ORDER BY lb.is_default DESC LIMIT 1';
        $list = $this->db->query($query, array($this->session->userdata('uid'), $id))->result_array();
        $this->get_keywords_sections($list[0]['id'], $list[0]);
        return $list[0];
    }

    /**
     * Get clip by ID Light
     * @param $id
     * @return mixed
     */
    function get_clip_by_id($id)
    {
        $query = 'SELECT * FROM lib_clips WHERE id=?';
        $list = $this->db->query($query, array($id))->result_array();
        $this->get_keywords_sections($list[0]['id'], $list[0]);
        return $list[0];
    }

    function sort_nf_clips($ids)
    {
        $query = 'SELECT id FROM lib_clips WHERE id IN (' . implode(',',$ids) . ') AND brand = 2';
        $row = $this->db->query($query)->result_array();
        $result = array();
        foreach ($row as $item){
            $result[] = $item['id'];
        }
        return $result;
    }

    /**
     * Get clip by Code
     * @param $code
     * @return mixed
     */
    function get_clip_by_code($code)
    {
        $query = 'SELECT * FROM lib_clips WHERE code=?';
        $list = $this->db->query($query, array($code))->result_array();
        $this->get_keywords_sections($list[0]['id'], $list[0]);
        return $list[0];
    }

    function get_clips_by_codeMask($code, $offset = 0, $limit = 50)
    {
        $list = $this->db->query(
            'SELECT c.*, cc.title, cc.creator, cc.rights, cc.description, cc.keywords, cc.subject, cc.primary_subject, cc.other_subject,
            cc.shot_type, cc.actions, cc.location, cc.subject_category, cc.appearance, cc.actions, cc.time, cc.habitat, cc.concept, cc.location
  FROM lib_clips c
  LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id
  WHERE c.code LIKE "%?%" ORDER BY code ASC  LIMIT ' . $offset . ',' . $limit, array($code))->result_array();
        return $list;
    }

    function get_clipsIds_by_codeMask($code, $offset = 0, $limit = 50)
    {
        $list = $this->db->query('SELECT id  FROM lib_clips WHERE code LIKE "%' . $code . '%" ORDER BY code LIMIT ' . $offset . ',' . $limit)->result_array();
        return $list;
    }

    function get_clip_for_edit($id, $lang = 'en')
    {
        /*$query='SELECT c.*, cc.title, cc.description, cc.keywords, cc.shot_type, cc.subject_category,
            cc.primary_subject, cc.other_subject, cc.appearance, cc.actions, cc.time, cc.habitat, cc.concept, cc.location, cc.notes
      FROM lib_clips c
      LEFT JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang=?
      WHERE c.id=?';
        $list = $this->db->query($query, array($lang, intval($id)))->result_array();*/
        $query = 'SELECT * FROM lib_clips WHERE id=?';
        $list = $this->db->query($query, array(intval($id)))->result_array();
        $this->get_keywords_sections($id, $list[0]);
        $list[0]['thumb'] = $this->get_clip_path($list[0]['id'], 'thumb');
        $list[0]['motion_thumb'] = $this->get_clip_path($list[0]['id'], 'motion_thumb');
        $list[0]['preview'] = $this->get_clip_path($list[0]['id'], 'preview');
        $list[0]['download'] = $lang . '/clips/content/' . $list[0]['id'];
        return $list[0];
    }

    function get_keywords_sections($clipId, &$buildArr)
    {
        $query = 'SELECT section_id,GROUP_CONCAT(keyword SEPARATOR ",") AS keywords FROM lib_clips_keywords WHERE clip_id=? GROUP BY section_id';
        $list = $this->db->query($query, array(intval($clipId)))->result_array();
        $listOut = array();
        array_map(function ($value) use (&$listOut) {
            $listOut[$value['section_id']] = $value['keywords'];
        }, $list);

        if (is_array($buildArr)) {
            $buildArr = array_merge($buildArr, $listOut);
        }
        foreach ($this->sectionList as $field => $v) {
            if (empty($buildArr[$field])) $buildArr[$field] = '';
        }
        $buildArr['title'] = $buildArr['code'];
        /*
            actions+
            appearance+
            category
            concept+
            country
            habitat+
            location+
            other_subject+
            primary_subject+
            shot_type+
            subject_category+
            time+
         */
        /*var $sectionList = array(
            'shot_type' => '',
            'subject_category' => '',
            'primary_subject' => '',
            'other_subject' => '',
            'appearance' => '',
            'actions' => '',
            'time' => '',
            'habitat' => '',
            'concept' => '',
            'location' => ''
        );*/
    }

    /**
     * Get clip By ID OR Code Full MAXimum
     * @param $id
     * @param string $lang
     * @param bool|false $for_api
     * @return mixed
     */
    function get_clip_info($id, $lang = 'en', $for_api = false)
    {
        if (is_numeric($id))
            $clip = $this->get_clip($id);
        else
            $clip = $this->get_clip_by_code($id);

        if (empty($clip['id'])) {
            // nothing find, return null
            return null;
        }

        $base_url = $this->config->base_url();
        $clip['res'] = $this->get_clip_path($clip['id'], 'preview');
        $clip['frames'] = array(
            'count' => $clip['frames_count'],
            'path' => '/' . $this->config->item('clip_path') . 'frames/clip_' . $id,
            'first_frame' => 'frame_1.jpg'
        );
        $clip['thumb'] = $this->get_clip_path($clip['id'], 'thumb');
        $clip['motion_thumb'] = $this->get_clip_path($clip['id'], 'motion_thumb');
        $clip['preview'] = $this->get_clip_path($clip['id'], 'preview');
        $clip['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $clip['id'];
        //$clip['location'] = $this->locations_model->get_location_string($clip['location_id']);

        $clip['meta_keys'] = $clip['keywords'];
        $clip['keywords'] = trim($clip['keywords'], ',');
        if (!$for_api)
            $clip['keywords'] = $this->get_linked_keywords($lang, $clip['keywords']);
        $clip['owner'] = $this->editors_model->get_profile($clip['client_id']);
        // this is outputed in video microformats, db format is correct, no need to truncate hours/minutes details
//        $clip['creation_date'] = date('d.m.Y', strtotime($clip['creation_date']));

        return $clip;
    }

    /**
     * Save Clip
     * @used \Libraries\Cliplog\Editor\CliplogEditor::saveFormDataToClips
     * @param $id
     * @param $data
     * @param string $lang
     * @param checkData Check if batch Cliplogging
     */
    function save_clip_data($id, $data, $lang = 'en', $checkData)
    {
        // ÃÅ¸ÃÂµÃ‘â‚¬ÃÂµÃÂ´ÃÂ°ÃÂµÃÂ¼ Ã‘â€žÃÂ»ÃÂ°ÃÂ³, Ã‘â€¡Ã‘â€šÃÂ¾ ÃÂºÃÂ»ÃÂ¸ÃÂ¿ ÃÂ±Ã‘â€¹ÃÂ» ÃÂ½ÃÂ°Ã¯Â¿Â½?Ã‘â€šÃ‘â‚¬ÃÂ¾ÃÂµÃÂ½
        $data_clip['untuned'] = 0;
        //$data_clip['active'] = 1;
        // ÃÅ¸ÃÂµÃ‘â‚¬ÃÂµÃÂ·ÃÂ°ÃÂ¿ÃÂ¸Ã¯Â¿Â½?Ã‘â€¹ÃÂ²ÃÂ°ÃÂµÃÂ¼Ã‘â€¹ÃÂµ Ã¯Â¿Â½?ÃÂµÃÂºÃ‘â€ ÃÂ¸ÃÂ¸
        $overwrite_sections = $data['overwrite'];
        // ÃÂ¡ÃÂ±Ã‘â‚¬ÃÂ¾Ã¯Â¿Â½? ÃÂ²Ã¯Â¿Â½?ÃÂµÃ‘â€¦ ÃÂ¿ÃÂ¾ÃÂ»ÃÂµÃÂ¹
        $reset_all_fields = $data['reset_all_fields'];

        if (!empty($data['license_restrictions']) || !empty($overwrite_sections['license_restrictions']) || $reset_all_fields) {
            $data_clip['license_restrictions'] = $data['license_restrictions'];
        }
        if (!empty($data['audio_video']) || !empty($overwrite_sections['audio_video']) || $reset_all_fields) {
            $data_clip['audio_video'] = $data['audio_video'];
        }
        if ((!empty($data['date_filmed']['month']) && $data['date_filmed']['month'] && !empty($data['date_filmed']['year']) && $data['date_filmed']['year']) || !empty($overwrite_sections['date_filmed']) || $reset_all_fields) {
            $data_clip['film_date'] = (!empty($data['date_filmed']['month']) && $data['date_filmed']['month'] && !empty($data['date_filmed']['year']) && $data['date_filmed']['year']) ? date('Y-m-d', mktime(0, 0, 0, (int)$data['date_filmed']['month'], 1, (int)$data['date_filmed']['year'])) : '0000-00-00';
        }
//        if ($data['date_filmed']['month'] == "" && $data['date_filmed']['year'] == "" || !empty($overwrite_sections['date_filmed']) || $reset_all_fields) {
//            $data_clip['film_date'] = (empty($data['date_filmed']['month']) && $data['date_filmed']['month'] && empty($data['date_filmed']['year']) && $data['date_filmed']['year']) ? date('Y-m-d', mktime(0, 0, 0, (int)$data['date_filmed']['month'], 1, (int)$data['date_filmed']['year'])) : '0000-00-00';
//            echo 'in here';
//        }

        if (!empty($data['collection']) || !empty($overwrite_sections['add_collection']) || $reset_all_fields) {
            // $data_clip['collection'] = $data['collection'];
        }

        if ($this->group['is_admin']) {
            if (
                !empty($data['brand'])
                || $data['brand'] === 0
                || !empty($overwrite_sections['brand'])
                || $reset_all_fields
            ) {
                $data_clip['brand'] = $data['brand'];
            }

        }

        if (!empty($data['price_level']) || !empty($overwrite_sections['price_level']) || $reset_all_fields) {
            $data_clip['price_level'] = $data['price_level'];
        }
        if (!empty($data['calc_price_level']) || !empty($overwrite_sections['calc_price_level']) || $reset_all_fields) {
            $data_clip['calc_price_level'] = $data['calc_price_level'];
        }
        if ((!empty($data['license_type']) && $data['license_type'] != '') || !empty($overwrite_sections['license_type']) || $reset_all_fields) {
            $data_clip['license'] = $data['license_type'];
        }
        if (!empty($data['releases']) || !empty($overwrite_sections['releases']) || $reset_all_fields) {
            $data_clip['releases'] = $data['releases'];
        }

        if (!empty($data['file_formats']['camera_model']) || !empty($overwrite_sections['file_formats']['camera_model']) || $reset_all_fields) {
            $data_clip['camera_model'] = $data['file_formats']['camera_model'];
        }
        if (!empty($data['file_formats']['camera_chip_size']) || !empty($overwrite_sections['file_formats']['camera_chip_size']) || $reset_all_fields) {
            $data_clip['camera_chip_size'] = $data['file_formats']['camera_chip_size'];
        }
        if (!empty($data['file_formats']['bit_depth']) || !empty($overwrite_sections['file_formats']['bit_depth']) || $reset_all_fields) {
            $data_clip['bit_depth'] = $data['file_formats']['bit_depth'];
        }
        if (!empty($data['file_formats']['color_space']) || !empty($overwrite_sections['file_formats']['color_space']) || $reset_all_fields) {
            $data_clip['color_space'] = $data['file_formats']['color_space'];
        }
        if (!empty($data['file_formats']['source_format']) || !empty($overwrite_sections['file_formats']['source_format']) || $reset_all_fields) {
            $data_clip['source_format'] = $data['file_formats']['source_format'];
        }
        if (!empty($data['file_formats']['source_codec']) || !empty($overwrite_sections['file_formats']['source_codec']) || $reset_all_fields) {
            $data_clip['source_codec'] = $data['file_formats']['source_codec'];
        }
        if (!empty($data['file_formats']['source_frame_size']) || !empty($overwrite_sections['file_formats']['source_frame_size']) || $reset_all_fields) {
            $data_clip['source_frame_size'] = $data['file_formats']['source_frame_size'];
        }

        if (!empty($data['file_formats']['source_frame_rate']) || !empty($overwrite_sections['file_formats']['source_frame_rate']) || $reset_all_fields) {
            $data_clip['source_frame_rate'] = $data['file_formats']['source_frame_rate'];
        }

        if (!empty($data['file_formats']['digital_file_format']) || !empty($overwrite_sections['file_formats']['digital_file_format']) || $reset_all_fields) {
            $data_clip['digital_file_format'] = $data['file_formats']['digital_file_format'];
        }

        if (!empty($data['file_formats']['source_data_rate']) || !empty($overwrite_sections['file_formats']['source_data_rate']) || $reset_all_fields) {
            $data_clip['source_data_rate'] = $data['file_formats']['source_data_rate'];
        }
        if (!empty($data['file_formats']['digital_file_frame_size']) || !empty($overwrite_sections['file_formats']['digital_file_frame_size']) || $reset_all_fields) {
            $data_clip['digital_file_frame_size'] = $data['file_formats']['digital_file_frame_size'];
        }
        if (!empty($data['file_formats']['digital_file_frame_rate']) || !empty($overwrite_sections['file_formats']['digital_file_frame_rate']) || $reset_all_fields) {
            $data_clip['digital_file_frame_rate'] = $data['file_formats']['digital_file_frame_rate'];
        }
        if (!empty($data['file_formats']['file_size_mb']) || !empty($overwrite_sections['file_formats']['file_size_mb']) || $reset_all_fields) {
            $data_clip['file_size_mb'] = $data['file_formats']['file_size_mb'];
        }
        if (!empty($data['file_formats']['file_wrapper']) || !empty($overwrite_sections['file_formats']['file_wrapper']) || $reset_all_fields) {
            $data_clip['file_wrapper'] = $data['file_formats']['file_wrapper'];
        }

        //OLD VALUES
        if (!empty($data['file_formats']['master_format']) || !empty($overwrite_sections['file_formats']['master_format']) || $reset_all_fields) {
            $data_clip['master_format'] = $data['file_formats']['master_format'];
        }
        if (!empty($data['file_formats']['master_frame_size']) || !empty($overwrite_sections['file_formats']['master_frame_size']) || $reset_all_fields) {
            $data_clip['master_frame_size'] = $data['file_formats']['master_frame_size'];
        }
        if (!empty($data['file_formats']['master_frame_rate']) || !empty($overwrite_sections['file_formats']['master_frame_rate']) || $reset_all_fields) {
            $data_clip['master_frame_rate'] = $data['file_formats']['master_frame_rate'];
        }
        //OLD VALUES


        if (!empty($_FILES) && is_uploaded_file($_FILES['upload_release']['tmp_name'])) {

            $code_name = $this->db_master->select('code')->get_where('lib_clips', array('id' => $id))->result_array();
            $nameCode = $code_name[0]['code'];

            $ext = pathinfo($_FILES['upload_release']['name'], PATHINFO_EXTENSION);
            $destination = 's3://' . $this->store['releases_file']['bucket'] . rtrim($this->store['releases_file']['path'], '/')
                . '/Release-' . $nameCode . '.' . $ext;

            if (!$this->s3Client) {
                $this->s3Client = S3Client::factory(array(
                    'key' => $this->store['s3']['key'],
                    'secret' => $this->store['s3']['secret']
                ));
            }

            $data_clip['release_file'] = $destination;
            $destination = parse_url($destination);

            try {

                $params = array(
                    'Bucket' => $destination['host'],
                    'Key' => trim($destination['path'], '/'),
                    'SourceFile' => $_FILES['upload_release']['tmp_name']
                );
                $params['ACL'] = 'public-read';
                $this->s3Client->putObject($params);
            } catch (Aws\Exception\S3Exception $e) {
                echo "There was an error uploading the file.\n";
            }


// move_uploaded_file($_FILES['upload_release']['tmp_name'], $file);
        }


        if($this->group['is_admin']){
            if (!isset($data['file_formats']['pricing_category']) /* || !empty($overwrite_sections['file_formats']) */ || $reset_all_fields) {
                // Set Delivery Category based on Submission Codec
                if (!empty($data['file_formats']['digital_file_format'])) {
                    $this->db->select('delivery_category');
                    $query = $this->db->get_where('lib_submission_codecs', array('name' => $data['file_formats']['digital_file_format']));
                    $row = $query->result_array();
                    if ($row[0]['delivery_category'])
                        $data['file_formats']['pricing_category'] = $row[0]['delivery_category'];
                }
            }

            if (!empty($data['file_formats']['pricing_category']) || !empty($overwrite_sections['file_formats']) || $reset_all_fields) {
                $data_clip['pricing_category'] = $data['file_formats']['pricing_category'];
                $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $id));
                $oldClipClipData = $this->db_master->select('pricing_category,brand')->get_where('lib_clips', array('id' => $id))->result_array();

                $oldPricingCategory = $oldClipClipData[0]['pricing_category'];
                $oldBrand = $oldClipClipData[0]['brand'];

                if (!empty($data_clip['pricing_category'])) {
                    $data_clip['pricing_category'] = $data_clip['pricing_category'];
                } else {
                    $data_clip['pricing_category'] = $oldPricingCategory;
                }

                $brand = (!empty($data['brand'])) ? $data['brand'] : $oldBrand;

                if (!empty($data['license_type'])) {
                    $license = $data['license_type'];
                } else {
                    $license = $this->get_license($id);
                }
                if ($license) {
                    $pricing_cat = $data_clip['pricing_category'];
//                Save relation to lib_clips_delivery_formats table
                    $this->save_clip_delivery_format($id, $license, $brand, $pricing_cat);
                }
            }
        }



        /* if(!isset($data['file_formats']['master_lab']) || !$data['file_formats']['master_lab']){
          $data_clip['master_lab'] = 'Deluxe Media (Digital Files Only)';
          }else{
          $data_clip['master_lab'] = $data['file_formats']['master_lab'];
          } */
        if (!empty($data['file_formats']['master_lab']) || !empty($overwrite_sections['file_formats']) || $reset_all_fields) {
            if (empty($data['keywords_set_id'])) {
                $data_clip['master_lab'] = $data['file_formats']['master_lab'];
            }
        }
        if (!empty($data['country']) || !empty($overwrite_sections['country']) || $reset_all_fields) {
            if (empty($data['keywords_set_id'])) {
                $this->checkAndUpdateTheCountryInClips($id, $data['country']);
            }
        }
        $data_content = array();
        if (!empty($data['clip_description']) || !empty($overwrite_sections['clip_description']) || $reset_all_fields) {
            $data_clip['description'] = htmlspecialchars($data['clip_description']);
        }

        if (!empty($data['clip_notes']) || !empty($overwrite_sections['clip_notes']) || $reset_all_fields) {
            $data_clip['notes'] = htmlspecialchars($data['clip_notes']);
        }

        //Check For the Batch And single Clip Logging to Avoide Data Loose

        if ($checkData == 0) {
            if (empty($data['license_restrictions']) || !empty($overwrite_sections['license_restrictions']) || $reset_all_fields) {
                $data_clip['license_restrictions'] = $data['license_restrictions'];
            }
            if (empty($data['audio_video']) || !empty($overwrite_sections['audio_video']) || $reset_all_fields) {
                $data_clip['audio_video'] = $data['audio_video'];
            }
            if ($data['date_filmed']['month'] == "" && $data['date_filmed']['year'] == "") {
                $data_clip['film_date'] = (empty($data['date_filmed']['month']) && $data['date_filmed']['month'] && empty($data['date_filmed']['year']) && $data['date_filmed']['year']) ? date('Y-m-d', mktime(0, 0, 0, (int)$data['date_filmed']['month'], 1, (int)$data['date_filmed']['year'])) : '0000-00-00';
            }
            if (empty($data['clip_description']) || !empty($overwrite_sections['clip_description']) || $reset_all_fields) {
                $data_clip['description'] = $data['clip_description'];
            }
            if (empty($data['clip_notes']) || !empty($overwrite_sections['clip_notes']) || $reset_all_fields) {
                $data_clip['notes'] = $data['clip_notes'];
            }
            if (isset($data['add_collection']) || !empty($overwrite_sections['add_collection']) || $reset_all_fields) {
                $this->checkAndUpdateTheCategoryInClips($id, $data['add_collection'], '1');
            } else {
                $this->checkAndUpdateTheCategoryInClips($id, $data['add_collection'], '0');
            }
            if (empty($data['country']) || !empty($overwrite_sections['country']) || $reset_all_fields) {
                if (empty($data['keywords_set_id'])) {
                    $this->checkAndUpdateTheCountryInClips($id, $data['country']);
                }
            }
            if (empty($data['file_formats']['camera_model']) || !empty($overwrite_sections['file_formats']['camera_model']) || $reset_all_fields) {
                $data_clip['camera_model'] = $data['file_formats']['camera_model'];
            }
            if (empty($data['file_formats']['camera_chip_size']) || !empty($overwrite_sections['file_formats']['camera_chip_size']) || $reset_all_fields) {
                $data_clip['camera_chip_size'] = $data['file_formats']['camera_chip_size'];
            }
            if (empty($data['file_formats']['bit_depth']) || !empty($overwrite_sections['file_formats']['bit_depth']) || $reset_all_fields) {
                $data_clip['bit_depth'] = $data['file_formats']['bit_depth'];
            }
            if (empty($data['file_formats']['color_space']) || !empty($overwrite_sections['file_formats']['color_space']) || $reset_all_fields) {
                $data_clip['color_space'] = $data['file_formats']['color_space'];
            }
            if (empty($data['file_formats']['source_format']) || !empty($overwrite_sections['file_formats']['source_format']) || $reset_all_fields) {
                $data_clip['source_format'] = $data['file_formats']['source_format'];
            }
            if (empty($data['file_formats']['source_codec']) || !empty($overwrite_sections['file_formats']['source_codec']) || $reset_all_fields) {
                $data_clip['source_codec'] = $data['file_formats']['source_codec'];
            }
            if (empty($data['file_formats']['source_frame_size']) || !empty($overwrite_sections['file_formats']['source_frame_size']) || $reset_all_fields) {
                $data_clip['source_frame_size'] = $data['file_formats']['source_frame_size'];
            }

            if (empty($data['file_formats']['source_frame_rate']) || !empty($overwrite_sections['file_formats']['source_frame_rate']) || $reset_all_fields) {
                $data_clip['source_frame_rate'] = $data['file_formats']['source_frame_rate'];
            }

            if (empty($data['file_formats']['digital_file_format']) || !empty($overwrite_sections['file_formats']['digital_file_format']) || $reset_all_fields) {
                $data_clip['digital_file_format'] = $data['file_formats']['digital_file_format'];
            }

            if (empty($data['file_formats']['source_data_rate']) || !empty($overwrite_sections['file_formats']['source_data_rate']) || $reset_all_fields) {
                $data_clip['source_data_rate'] = $data['file_formats']['source_data_rate'];
            }
            if (empty($data['file_formats']['digital_file_frame_size']) || !empty($overwrite_sections['file_formats']['digital_file_frame_size']) || $reset_all_fields) {
                $data_clip['digital_file_frame_size'] = $data['file_formats']['digital_file_frame_size'];
            }
            if (empty($data['file_formats']['digital_file_frame_rate']) || !empty($overwrite_sections['file_formats']['digital_file_frame_rate']) || $reset_all_fields) {
                $data_clip['digital_file_frame_rate'] = $data['file_formats']['digital_file_frame_rate'];
            }

        } else {

            if (empty($data['license_restrictions']) && !empty($overwrite_sections['license_restrictions']) || $reset_all_fields) {
                $data_clip['license_restrictions'] = $data['license_restrictions'];
            }
            if (empty($data['audio_video']) && !empty($overwrite_sections['audio_video']) || $reset_all_fields) {
                $data_clip['audio_video'] = $data['audio_video'];
            }
            if ($data['date_filmed']['month'] == "" && $data['date_filmed']['year'] == "" && !empty($overwrite_sections['date_filmed'])) {
                $data_clip['film_date'] = (empty($data['date_filmed']['month']) && $data['date_filmed']['month'] && empty($data['date_filmed']['year']) && $data['date_filmed']['year']) ? date('Y-m-d', mktime(0, 0, 0, (int)$data['date_filmed']['month'], 1, (int)$data['date_filmed']['year'])) : '0000-00-00';
            }
            if (empty($data['clip_description']) && !empty($overwrite_sections['clip_description']) || $reset_all_fields) {
                $data_clip['description'] = $data['clip_description'];
            }
            if (isset($data['add_collection']) && !empty($overwrite_sections['add_collection']) || $reset_all_fields) {
                $this->checkAndUpdateTheCategoryInClipsBatch($id, $data['add_collection'], '1');
            }
            if (empty($data['country']) && !empty($overwrite_sections['country']) || $reset_all_fields) {
                if (empty($data['keywords_set_id'])) {
                    $this->checkAndUpdateTheCountryInClips($id, $data['country']);
                }
            }
            if (empty($data['file_formats']['camera_model']) && !empty($overwrite_sections['file_formats']['camera_model']) || $reset_all_fields) {
                $data_clip['camera_model'] = $data['file_formats']['camera_model'];
            }
            if (empty($data['file_formats']['camera_chip_size']) && !empty($overwrite_sections['file_formats']['camera_chip_size']) || $reset_all_fields) {
                $data_clip['camera_chip_size'] = $data['file_formats']['camera_chip_size'];
            }
            if (empty($data['file_formats']['bit_depth']) && !empty($overwrite_sections['file_formats']['bit_depth']) || $reset_all_fields) {
                $data_clip['bit_depth'] = $data['file_formats']['bit_depth'];
            }
            if (empty($data['file_formats']['color_space']) && !empty($overwrite_sections['file_formats']['color_space']) || $reset_all_fields) {
                $data_clip['color_space'] = $data['file_formats']['color_space'];
            }
            if (empty($data['file_formats']['source_format']) && !empty($overwrite_sections['file_formats']['source_format']) || $reset_all_fields) {
                $data_clip['source_format'] = $data['file_formats']['source_format'];
            }
            if (empty($data['file_formats']['source_codec']) && !empty($overwrite_sections['file_formats']['source_codec']) || $reset_all_fields) {
                $data_clip['source_codec'] = $data['file_formats']['source_codec'];
            }
            if (empty($data['file_formats']['source_frame_size']) && !empty($overwrite_sections['file_formats']['source_frame_size']) || $reset_all_fields) {
                $data_clip['source_frame_size'] = $data['file_formats']['source_frame_size'];
            }

            if (empty($data['file_formats']['source_frame_rate']) && !empty($overwrite_sections['file_formats']['source_frame_rate']) || $reset_all_fields) {
                $data_clip['source_frame_rate'] = $data['file_formats']['source_frame_rate'];
            }

            if (empty($data['file_formats']['digital_file_format']) && !empty($overwrite_sections['file_formats']['digital_file_format']) || $reset_all_fields) {
                $data_clip['digital_file_format'] = $data['file_formats']['digital_file_format'];
            }

            if (empty($data['file_formats']['source_data_rate']) && !empty($overwrite_sections['file_formats']['source_data_rate']) || $reset_all_fields) {
                $data_clip['source_data_rate'] = $data['file_formats']['source_data_rate'];
            }
            if (empty($data['file_formats']['digital_file_frame_size']) && !empty($overwrite_sections['file_formats']['digital_file_frame_size']) || $reset_all_fields) {
                $data_clip['digital_file_frame_size'] = $data['file_formats']['digital_file_frame_size'];
            }
            if (empty($data['file_formats']['digital_file_frame_rate']) && !empty($overwrite_sections['file_formats']['digital_file_frame_rate']) || $reset_all_fields) {
                $data_clip['digital_file_frame_rate'] = $data['file_formats']['digital_file_frame_rate'];
            }


        }


        if ($id) {

//            else{
//                // Ãâ€¢Ã¯Â¿Â½?ÃÂ»ÃÂ¸ ÃÂ½ÃÂµ ÃÂ·ÃÂ°ÃÂ¿ÃÂ¾ÃÂ»ÃÂ½ÃÂµÃÂ½ÃÂ° add collection ÃÂ½ÃÂµ ÃÂ¿Ã‘Æ’ÃÂ±ÃÂ»ÃÂ¸ÃÂºÃ‘Æ’ÃÂµÃÂ¼ ÃÂºÃÂ»ÃÂ¸ÃÂ¿
//                $data_clip['active'] = 0;
//                if(isset($data['file_formats']['source_format']) && stripos($data['file_formats']['source_format'], '3D') !== false){
//                    $this->db->select('id');
//                    $query = $this->db->get_where('lib_collections', array('name' => '3D Footage'));
//                    $row = $query->result_array();
//                    if($row[0]['id']){
//                        $this->db_master->delete('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
//                        $this->db_master->insert('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
//                    }
//                }
//                if(isset($data['file_formats']['digital_file_frame_size']) && stripos($data['file_formats']['digital_file_frame_size'], 'Ultra HD') !== false){
//                    $this->db->select('id');
//                    $query = $this->db->get_where('lib_collections', array('name' => 'Ultra HD Footage'));
//                    $row = $query->result_array();
//                    if($row[0]['id']){
//                        $this->db_master->delete('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
//                        $this->db_master->insert('lib_clips_collections', array('clip_id' => $id, 'collection_id' => $row[0]['id']));
//                    }
//                }
//            }
//            if(isset($data['license_type']) && is_array($data['license_type'])){
//                $this->db_master->delete('lib_clip_license_types', array('clip_id' => $id));
//                foreach($data['license_type'] as $license_id){
//                    $this->db_master->insert('lib_clip_license_types', array('clip_id' => $id, 'license_id' => $license_id));
//                }
//            }

            if (!empty($data['keywords']) || !empty($overwrite_sections) || $reset_all_fields) {
                if (empty($data['keywords'])) {
                    $keywordsList = array();
                    $postSectionsKeywords = array();
                } else {
                    // Ã ÃÂ°ÃÂ·ÃÂ±ÃÂ¸ÃÂ²ÃÂ°ÃÂµÃÂ¼ ÃÂºÃÂµÃÂ¹ÃÂ²ÃÂ¾Ã‘â‚¬ÃÂ´Ã‘â€¹ ÃÂ¿ÃÂ¾ Ã¯Â¿Â½?ÃÂµÃÂºÃ‘â€ ÃÂ¸Ã¯Â¿Â½?ÃÂ¼.
//                    $keywordsIdsString = implode(', ', array_filter(array_keys($data['keywords']), function ($val) {
//                        return !empty($val);
//                    }));
//                    $keywordsList = $this->cliplog_model->getKeywordsCustomList("lib_keywords.id IN ( {$keywordsIdsString} )");
//                    if ($keywordsList && is_array($keywordsList)) {
//                        foreach ($keywordsList as $keyword) {
//                            $selectedSectionsKeywords[$keyword['section']][] = $keyword;
//                            $postSectionsKeywords[$keyword['section']][$keyword['id']] = $keyword;
//                            $selectedKeywords[] = $keyword;
//                        }
//                    }
                    // Ãâ€¢Ã¯Â¿Â½?ÃÂ»ÃÂ¸ ÃÂ²Ã‘â€¹ÃÂ±Ã‘â‚¬ÃÂ°ÃÂ½ Ã‘Ë†ÃÂ°ÃÂ±ÃÂ»ÃÂ¾ÃÂ½ Reset All Fields ÃÂ¿ÃÂµÃ‘â‚¬ÃÂµÃÂ·ÃÂ°ÃÂ¿ÃÂ¸Ã¯Â¿Â½?Ã‘â€¹ÃÂ²ÃÂ°ÃÂµÃÂ¼ ÃÂ²Ã¯Â¿Â½?ÃÂµ Ã¯Â¿Â½?ÃÂµÃÂºÃ‘â€ ÃÂ¸ÃÂ¸
                    /* if($reset_all_fields){
                      $overwrite_sections['shot_type'] = 'shot_type';
                      $overwrite_sections['subject_category'] = 'subject_category';
                      $overwrite_sections['primary_subject'] = 'primary_subject';
                      $overwrite_sections['other_subject'] = 'other_subject';
                      $overwrite_sections['appearance'] = 'appearance';
                      $overwrite_sections['actions'] = 'actions';
                      $overwrite_sections['time'] = 'time';
                      $overwrite_sections['habitat'] = 'habitat';
                      $overwrite_sections['concept'] = 'concept';
                      $overwrite_sections['location'] = 'location';
                      $overwrite_sections['keywords'] = 'keywords';
                      } */
                }


                // Ãâ€ÃÂ¾Ã¯Â¿Â½?Ã‘â€šÃÂ°ÃÂµÃÂ¼ Ã‘â€šÃÂµÃÂºÃ‘Æ’Ã‘â€°ÃÂ¸ÃÂµ ÃÂºÃÂµÃÂ¹ÃÂ²ÃÂ¾Ã‘â‚¬ÃÂ´Ã‘â€¹ ÃÂºÃÂ»ÃÂ¸ÃÂ¿ÃÂ° ÃÂ¸ Ã‘â‚¬ÃÂ°ÃÂ·ÃÂ±ÃÂ¸ÃÂ²ÃÂ°ÃÂµÃÂ¼ ÃÂ¿ÃÂ¾ Ã¯Â¿Â½?ÃÂµÃÂºÃ‘â€ ÃÂ¸Ã¯Â¿Â½?ÃÂ¼
//                $clip_keywords = $this->db->query('SELECT id, keyword, section FROM lib_keywords lk
//                    INNER JOIN lib_clip_keywords lck ON lk.id = lck.keyword_id AND lck.clip_id = ?', array($id))->result_array();

//                $data_content['shot_type'] = '';
//                $data_content['subject_category'] = '';
//                $data_content['primary_subject'] = '';
//                $data_content['other_subject'] = '';
//                $data_content['appearance'] = '';
//                $data_content['actions'] = '';
//                $data_content['time'] = '';
//                $data_content['habitat'] = '';
//                $data_content['concept'] = '';
                $data_content['location'] = '';
                $data_content['keywords'] = '';

                // ÃÅ¾ÃÂ±Ã‘Å ÃÂµÃÂ´ÃÂ¸ÃÂ½Ã¯Â¿Â½?ÃÂµÃÂ¼ Ã¯Â¿Â½?Ã‘â€šÃÂ°Ã‘â‚¬Ã‘â€¹ÃÂµ ÃÂ¸ ÃÂ½ÃÂ¾ÃÂ²Ã‘â€¹ÃÂµ Ã¯Â¿Â½?ÃÂ»ÃÂ¾ÃÂ²ÃÂ°
//                $clip_keywords = array_merge($clip_keywords, $keywordsList);
//                $insertLib_clip_keywords = array();
//                if (!empty($clip_keywords)) {
//                    foreach ($clip_keywords as $keyword) {
//                        $keyword = (!empty($overwrite_sections[$keyword['section']]) || $reset_all_fields) ? $postSectionsKeywords[$keyword['section']][$keyword['id']] : $keyword;
//                        if (!empty($keyword)) {
//                            if ($data_clip['keywords'])
//                                $data_clip['keywords'] .= ', ' . $keyword['keyword'];
//                            else
//                                $data_clip['keywords'] = $keyword['keyword'];
//
//                            $insertLib_clip_keywords[$keyword['id']] = $keyword['id'];
//                        }
//                    }
//                }

//              Do not use "$this->db->query" because sometimes data in db doesnt have time to update before reading
                $clip_keywords = $this->db_master->query('SELECT * FROM lib_clips_keywords WHERE clip_id = ?', array($id))->result_array();


                $data_clip['location'] = '';
                $data_clip['keywords'] = '';

// ??????????? ??????? ? ????? ??????
                if (!empty($clip_keywords)) {
                    foreach ($clip_keywords as $keyword) {

                        if (!empty($keyword)) {
                            if ($keyword['section_id'] == 'location') {
                                if ($data_clip['location'])
                                    $data_clip['location'] .= ', ' . $keyword['keyword'];
                                else
                                    $data_clip['location'] = $keyword['keyword'] . ' , ' . $data['country'];
                            }
                            if ($data_clip['keywords'])
                                $data_clip['keywords'] .= ', ' . $keyword['keyword'];
                            else
                                $data_clip['keywords'] = $keyword['keyword'];

//  $insertLib_clip_keywords[$keyword['id']] = $keyword['id'];
                        }
                    }
                }

//                if(!empty($data['clarifai_keywords'])) {
//                $data_clip['keywords'] = $this->generateClarifaiKeywords($id);
//                }

                // ÃÅ¸ÃÂµÃ‘â‚¬ÃÂµÃÂ·ÃÂ°ÃÂ¿ÃÂ¸Ã¯Â¿Â½?Ã‘â€¹ÃÂ²ÃÂ°ÃÂµÃÂ¼ Ã¯Â¿Â½?ÃÂµÃÂºÃ‘â€ ÃÂ¸ÃÂ¸ ÃÂºÃÂµÃÂ¹ÃÂ²ÃÂ¾Ã‘â‚¬ÃÂ´ÃÂ¾ÃÂ² ÃÂºÃÂ»ÃÂ¸ÃÂ¿ÃÂ° Ã¯Â¿Â½? ÃÂ´ÃÂ°ÃÂ½ÃÂ½Ã‘â€¹ÃÂ¼ÃÂ¸ Ã¯Â¿Â½? POST

//                $this->db_master->delete('lib_clip_keywords', array('clip_id' => $id));
//                $insertLibDelDuplicates = array_unique($insertLib_clip_keywords);
//                foreach ($insertLibDelDuplicates as $keyword_id) {
//                    $this->db_master->insert('lib_clip_keywords', array('clip_id' => $id, 'keyword_id' => $keyword_id));
//                }
                // ÃÂ£ÃÂ´ÃÂ°ÃÂ»Ã¯Â¿Â½?ÃÂµÃÂ¼ ÃÂ´Ã‘Æ’ÃÂ±ÃÂ»ÃÂ¸ÃÂºÃÂ°Ã‘â€šÃ‘â€¹ Ã¯Â¿Â½?ÃÂ»ÃÂ¾ÃÂ² ÃÂ¿ÃÂ¾Ã¯Â¿Â½?ÃÂ»ÃÂµ Ã¯Â¿Â½?ÃÂ»ÃÂ¸Ã¯Â¿Â½?ÃÂ½ÃÂ¸Ã¯Â¿Â½?
                //explode + array_unique + implode

                foreach ($data_content as $field => $words) {
                    $words = preg_replace("#(,| |\"|') *#i", "\\1", $words);
                    $wordsArr = explode(',', $words);
                    $delDuplicates = array_unique($wordsArr);
                    $data_content[$field] = implode(',', $delDuplicates);
                }
            }

//            // Ã¯Â¿Â½?ÃÂµ ÃÂ¿Ã‘Æ’ÃÂ±ÃÂ»ÃÂ¸ÃÂºÃ‘Æ’ÃÂµÃÂ¼ ÃÂºÃÂ»ÃÂ¸ÃÂ¿ ÃÂµÃ¯Â¿Â½?ÃÂ»ÃÂ¸ ÃÂ½ÃÂµ ÃÂ·ÃÂ°ÃÂ¿ÃÂ¾ÃÂ»ÃÂ½ÃÂµÃÂ½Ã‘â€¹ ÃÅ¾ÃÂ±Ã¯Â¿Â½?ÃÂ·ÃÂ°Ã‘â€šÃÂµÃÂ»Ã‘Å’ÃÂ½Ã‘â€¹ÃÂµ ÃÂ¿ÃÂ¾ÃÂ»Ã¯Â¿Â½?
//            $query = $this->db->get_where('lib_clips', array('id' => $id));
//            $clip_status = $query->result_array();
//            // If isset value in DB OR in filds
//            if (($clip_status[0]['digital_file_format'] != '' || $data_clip['digital_file_format'] != '') &&
//                ($clip_status[0]['digital_file_frame_size'] != '' || $data_clip['digital_file_frame_size'] != '') &&
//                ($clip_status[0]['digital_file_frame_rate'] != '' || $data_clip['digital_file_frame_rate'] != '') &&
//                ($clip_status[0]['price_level'] != '' || $data_clip['price_level'] != '') &&
//                ($clip_status[0]['license'] != '' || $data_clip['license'] != '')
//            ) {
//                if (($data_clip['digital_file_format'] == '' && !empty($overwrite_sections['file_formats']['digital_file_format'])) ||
//                    ($data_clip['digital_file_frame_size'] == '' && !empty($overwrite_sections['file_formats']['digital_file_frame_size'])) ||
//                    ($data_clip['digital_file_frame_rate'] == '' && !empty($overwrite_sections['file_formats']['digital_file_frame_rate'])) ||
//                    ($data_clip['price_level'] == '' && !empty($overwrite_sections['price_level'])) ||
//                    ($data_clip['license'] == '' && !empty($overwrite_sections['license_type']))
//                ) {
//                    // If empty value in some filds AND select overwrite checkbox
//                    $data_clip['active'] = 0;
//                } else {
//                    $data_clip['active'] = 1;
//                }
//            } else {
//                $data_clip['active'] = 0;
//            }


            if ($data_clip['film_date'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['film_date'])) {
                $data_clip['film_date'] = $clip_status[0]['film_date'];
            }
            if ($data_clip['collection'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['collection'])) {
                $data_clip['collection'] = $clip_status[0]['collection'];
            }

            if ($this->group['is_admin']) {
                if ($data_clip['brand'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['brand'])) {
                    $data_clip['brand'] = $clip_status[0]['brand'];
                }
            }
            if ($data_clip['price_level'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['price_level'])) {
                $data_clip['price_level'] = $clip_status[0]['price_level'];
            }
            if ($data_clip['calc_price_level'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['calc_price_level'])) {
                $data_clip['calc_price_level'] = $clip_status[0]['calc_price_level'];
            }
            if ($data_clip['license'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['license'])) {
                $data_clip['license'] = $clip_status[0]['license'];
            }
            if ($data_clip['releases'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['releases'])) {
                $data_clip['releases'] = $clip_status[0]['releases'];
            }
            if ($data_clip['master_lab'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['master_lab'])) {
                $data_clip['master_lab'] = $clip_status[0]['master_lab'];
            }
            if ($data_clip['country'] == '' && !empty($data['keywords_set_id']) && !empty($clip_status[0]['country'])) {
                $this->checkAndUpdateTheCountryInClips($id, $data['country']);
            }

            // Check old status
            /* $this->db->select('active');
              $query = $this->db->get_where('lib_clips', array('id' => $id));
              $clip_status = $query->result_array();
              if ($clip_status && $clip_status[0]['active'] > 1)
              $data_clip['active'] = $clip_status[0]['active']; */

            // Add +10 rank clip if he online
            //clear empty element array
            //$data_clip = array_diff($data_clip, array('',null));
            //$data_content = array_diff($data_content, array('',null));

            // do not allow to overwrite pricing_category to empty value on batch update
            // checkData is a flag of batch update
            if ($checkData
                && array_key_exists('pricing_category', $data_clip)
                && empty($data_clip['pricing_category'])
            ) {
                unset($data_clip['pricing_category']);
            }
            // --

            // restrict some updates for non admin users
            if (!$this->isAdminUser()) {
                $data_clip = array_diff_key($data_clip, array_flip($this->adminOnlyEditableFields()));
            }
            //

            $this->db_master->where('id', $id);
            $this->db_master->update('lib_clips', $data_clip);
            $dataChecking = $this->db_master->affected_rows();

//            if (!empty($data_content)) {
//                $query = $this->db->get_where('lib_clips_content', array('clip_id' => $id, 'lang' => $lang));
//                $row = $query->result_array();
//                if (count($row)) {
//                    $this->db_master->where('id', $row[0]['id']);
//                    if ($data_content['description'] == '' && !empty($data['keywords_set_id']) && !empty($row[0]['description'])) {
//                        $data_content['description'] = $row[0]['description'];
//                    }
//                    if ($data_content['notes'] == '' && !empty($data['keywords_set_id']) && !empty($row[0]['notes'])) {
//                        $data_content['notes'] = $row[0]['notes'];
//                    }
//                    $this->db_master->update('lib_clips_content', $data_content);
//                } else
//                    $this->db_master->insert('lib_clips_content', $data_content);
//            }

// echo 'save_clip_data HERE';
//::::::UPDATE THE LATEST KEYWORDS IN lib_:::::://

            $clip_id_inserted = $id;


            //MOve Clip Online Or Offline::
            //sleep(1);

//return $rowSelect[0]['login'];


            $query = $this->db_master->query("DELETE FROM lib_clips_keywords WHERE keyword='' AND clip_id =" . $id . "");
//            if ($data_clip['description'] == '' && !empty($data['keywords_set_id']) && !empty($row[0]['description'])) {
//                $data_clip['description'] = $row[0]['description'];
//            }
//            if ($data_clip['notes'] == '' && !empty($data['keywords_set_id']) && !empty($row[0]['notes'])) {
//                $data_clip['notes'] = $row[0]['notes'];
//            }
            foreach ($data_clip as $key => $value) {
                if (is_null($value) || $value == '')
                    unset($data_clip[$key]);
            }
//            if (!empty($data_content)) {
//                $this->db_master->where('id', $id);
//                $this->db_master->update('lib_clips_content', $data_content);
//            }
        }
    }

    /**
     * true if admin, false if not
     *
     * @return bool
     */
    public function isAdminUser()
    {
        return (bool) $this->group['is_admin'];
    }

    /**
     * fields editable only by admin users
     *
     * @return array
     */
    public function adminOnlyEditableFields()
    {
        return ['digital_file_format', 'digital_file_frame_rate', 'digital_file_frame_size'];
    }

    /**
     * Save clip
     * @used \Clips::edit
     * @param $id
     * @param string $lang
     * @return mixed
     */
    function save_clip($id, $lang = 'en')
    {

        $client_id = $this->input->post('client_id');
        if ($client_id) {
            $data_clip['client_id'] = $client_id;
        } elseif ($this->session->userdata('client_uid')) {
            $data_clip['client_id'] = $this->session->userdata('client_uid');
        }

        $data_clip['code'] = $this->input->post('code');
        $data_clip['creation_date'] = $this->input->post('creation_date');
        if (empty($data_clip['creation_date'])) {
            unset($data_clip['creation_date']);
        }
        //$data_clip['aspect'] = $this->input->post('aspect');
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

        $data_clip['description'] = $this->input->post('description');
        $data_clip['keywords'] = $this->input->post('keywords');

        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_clips', $data_clip);

            $this->db->select('brand');
            $query = $this->db->get_where('lib_clips', array('id' => $id));
            $row = $query->result_array();
            $brand = $row[0]['brand'];

            /*$query = $this->db->get_where('lib_clips_content', array('clip_id' => $id, 'lang' => $lang));
            $row = $query->result_array();*/

            // if (count($row)) {
            //     $this->db_master->where('id', $row[0]['id']);
            //     $this->db_master->update('lib_clips_content', $data_content);
            //  } else
            //      $this->db_master->insert('lib_clips_content', $data_content);

            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $id));
            if ($this->input->post('pricing_category') && $data_clip['license']) {
                $pricing_categories = explode(',', $this->input->post('pricing_category'));
                foreach ($pricing_categories as $key => $cat) {
                    $pricing_categories[$key] = trim($cat);
                }
                // Save relation to lib_clips_delivery_formats table
                $this->save_clip_delivery_format($id, $data_clip['license'], $brand, $data_clip['pricing_category']);
            }

            $this->create_sitemap();
            return $id;
        } else {
            $data_clip['ctime'] = date('Y-m-d H:i:s');

            $this->db_master->insert('lib_clips', $data_clip);

            $data_content['clip_id'] = $this->db_master->insert_id();
            //$this->db_master->insert('lib_clips_content', $data_content);

//            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $data_content['clip_id']));
//            if ($this->input->post('delivery_formats')) {
//                $delivery_formats = explode(',', $this->input->post('delivery_formats'));
//                foreach ($delivery_formats as $key => $format) {
//                    $delivery_formats[$key] = trim($format);
//                }
//                $query = $this->db->query('SELECT id FROM lib_delivery_options WHERE code IN (\'' . implode('\',\'', $delivery_formats) . '\')');
//                $delivery_formats_ids = $query->result_array();
//                if (count($delivery_formats_ids)) {
//                    foreach ($delivery_formats_ids as $format_id) {
//                        $this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $data_content['clip_id'], 'format_id' => $format_id['id']));
//                    }
//                }
//            }

            $this->create_sitemap();

            return $data_content['clip_id'];
        }
    }

    function get_code($id)
    {
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $row = $query->result_array();
        return $row[0]['code'];
    }

    /**
     * Get "login" user by Clip ID
     * @param $id
     * @return mixed
     */
    function get_user_folder($id)
    {
        $query = $this->db->query('select lu.login from lib_users as lu, lib_clips as lc where lc.client_id=lu.id and lc.id=' . $id);
        $row = $query->result_array();
        return $row[0]['login'];
    }

    /**
     * Get license by Clip ID
     * @param $id
     * @return mixed
     */
    function get_license($id)
    {
        $this->db->select('license');
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $row = $query->result_array();
        return $row[0]['license'];
    }

    /**
     * Get duration by Clip ID
     * @param $id
     * @return mixed
     */
    function get_duration($id)
    {
        $this->db->select('duration');
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $row = $query->result_array();
        return $row[0]['duration'];
    }

    /**
     * Insert or Update lib_clips_res by Clip ID
     * @param $id
     * @param $filetype
     * @param int $type
     * @param string $location
     */
    function set_clip_res($id, $filetype, $type = 2, $location = '')
    {

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

    function get_res_filter($res_type)
    {
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

    /**
     * Remove lib_clips_res records by Clip ID and type
     * @param $id
     * @param $res_type
     */
    function unreg_resource($id, $res_type)
    {
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
                if ($res['type'] == 1) {
                    $this->db_master->update('lib_clips', array('frames_count' => 0), array('id' => $id));
                }
            }
        }
        /////

        $query = $this->db_master->query('DELETE FROM lib_clips_res WHERE clip_id = ? AND type = ? AND resource IN ('
            . $filter['resource_in'] . ')', array($id, $filter['type']));
    }

    /**
     * Update and Get metadata by Clip ID and filePath
     * @param $id
     * @param $file
     * @return array
     */
    function metadata1($id, $file)
    {

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
        $clip_data['duration'] = $val[0] / 1000;

        //$val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Recorded_date');
        //$clip_data['creation_date'] = (string)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="General"]/Encoded_date');
        preg_match('/[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])/', $val[0], $matches);
        if ($matches[0]) {
            $clip_data['creation_date'] = $matches[0];
        }

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Width');
        $clip_data['width'] = (int)$val[0];

        $val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Height');
        $clip_data['height'] = (int)$val[0];

        /*$val = $xml->xpath('/Mediainfo/File/track[@type="Video"]/Display_aspect_ratio');
        $clip_data['aspect'] = (string)$val[1];*/

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
        } elseif (!empty($this->codecs[$clip_data['codec']])) {
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

        if (!empty($clip_data))
            $this->db_master->update('lib_clips', $clip_data, array('id' => $id));
        if (!empty($clip_content_data))
            $this->db_master->update('lib_clips_content', $clip_content_data, array('clip_id' => $id, 'lang' => 'en'));

        return $clip_data;
    }

    /**
     * @param $str
     * @return array
     */
    function parse_metadata($str)
    {
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

    /**
     * Update and Get metadata by Clip ID and filePath
     * @param $id
     * @param $src
     */
    function metadata($id, $src)
    {
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
            //$data['aspect'] = $aspect;
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

        if (!empty($data))
            $this->db_master->update('lib_clips', $data, array('id' => $id));
    }

    /**
     * @param int $clip_id
     * @return bool
     */
    function create_browse_page_thumb($clip_id)
    {
        $hd_thumb_location = $this->get_clip_thumb($clip_id, 'hd');
        $browse_page_thumb_location = $this->get_aws_browse_still_path($hd_thumb_location);

        $result = $this->resize_and_save_to_s3(
            $hd_thumb_location,
            $browse_page_thumb_location,
            $this->store['thumb']['browse_page']['width'],
            $this->store['thumb']['browse_page']['height']
        );

        if(!$result)
            return false;

        // save to lib_clips_res
        $this->set_clip_res(
            $clip_id,
            $this->store['thumb']['filetype'],
            $this->store['thumb']['browse_page']['type'],
            $browse_page_thumb_location
        );

        return $browse_page_thumb_location;

    }

    /**
     * @param string $source_thumb_location
     * @param string $destination_thumb_location
     * @param int $width
     * @param int $height
     * @return bool
     */
    private function resize_and_save_to_s3($source_thumb_location, $destination_thumb_location, $width, $height)
    {
        $aws_path_array = parse_url($source_thumb_location);
        $image_name = basename($aws_path_array['path']);

        $source_image_path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$image_name;

        try{
            $this->aws_model->download(
                $this->store['resources']['bucket'],
                trim($aws_path_array['path'], '\/'),
                $source_image_path
            );
        } catch (\Exception $e){
            return false;
        }

        if(!($resized_image_path = $this->resize_image($source_image_path , $width, $height)))
            return false;

        return $this->aws_model->upload_resource($resized_image_path, $destination_thumb_location);
    }

    /**
     * @param string $source_image_path
     * @param int $width
     * @param int $height
     * @return bool|string
     */
    private function resize_image($source_image_path, $width, $height)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source_image_path;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']     = $width;
        $config['height']   = $height;
        $this->load->library('image_lib', $config);

        if(!$this->image_lib->resize()){
            return false;
        }
        $source_path_array = pathinfo($source_image_path);

        return $source_path_array['dirname'].DIRECTORY_SEPARATOR.$source_path_array['filename'].'_thumb.'.$source_path_array['extension'];
    }

    /**
     * @param string $hdStillPath
     * @return mixed
     */
    private function get_aws_browse_still_path($hdStillPath)
    {
        return str_replace(
            $this->store['thumb']['hd']['folder'],
            $this->store['thumb']['browse_page']['folder'],
            trim($hdStillPath, '\/')
        );
    }

    /**
     * Create new resource lib_clips_res thumb clip
     * @param $id
     * @param null $clip_data
     * @return string
     */
    function create_thumb($id, $clip_data = null)
    {
        $width = 200;
        $height = 112;

        if (empty($clip_data['duration'])) {
            $ss = 0;
        } elseif ($clip_data['duration'] >= 10) {
            $ss = 10;
        } else {
            $ss = number_format($clip_data['duration'] / 2, 2, '.', '');
        }

        /*if (!empty($clip_data['aspect'])) {
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
        }*/

        $src_type = $this->db->query(
            'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = 2', $id)->result_array();
        $src_type = $src_type[0]['resource'];
        if (!$src_type) {
            return 'Delivery file is not registered.';
        }

        $dir = $this->config->item('clip_dir');

        if (strcasecmp($src_type, 'r3d') == 0) {
            $src = $this->config->item('converted_clips') . $id . '.mov';
        } else {
            $src = $dir . 'res/' . $id . '.' . $src_type;
        }


        if (!is_file($src)) {
            return 'Error: HD file not found.';
        }
        $dest = $dir . 'thumb/' . $id . '.jpg';
        if (is_file($dest)) {
            unlink($dest);
        }

        $command = '/usr/local/bin/ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
            . ' -s ' . $width . 'x' . $height . ' -y ' . $dest;

        //For Windows
        /* $command = 'C:\ffmpeg\bin\ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
          . ' -s ' . $width . 'x' . $height . ' -y ' . $dest; */

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

    /**
     * Ajax tmp clip thumb offset seconds
     * @used \Clips::create_temp_thumb
     * @param $id
     * @param int $offset
     * @return bool
     */
    function create_temp_thumb($id, $offset = 0)
    {
        $width = 200;
        $height = 112;

        $this->db->select('duration');
        $query = $this->db->get_where('lib_clips', array('id' => $id));
        $rows = $query->result_array();
        $clip_data = $rows[0];

        if ($offset)
            $ss = $offset;
        else {
            if (empty($clip_data['duration'])) {
                $ss = 0;
            } elseif ($clip_data['duration'] >= 10) {
                $ss = 10;
            } else {
                $ss = number_format($clip_data['duration'] / 2, 2, '.', '');
            }
        }

        /*if (!empty($clip_data['aspect'])) {
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
        }*/

        $src_type = $this->db->query('SELECT resource, location FROM lib_clips_res WHERE clip_id = ? AND type = 1', $id)->result_array();
        if (!$src_type[0]['resource']) {
            return false;
        }
        $dir = $this->config->item('clip_dir');

        if (strcasecmp($src_type, 'r3d') == 0) {
            $src = $this->config->item('converted_clips') . $id . '.mov';
        } else {
            $src = $dir . 'res/' . $id . '.' . $src_type;
        }


        if (!is_file($src)) {
            return false;
        }
        $dest = $dir . 'temp_thumb/' . $id . '.jpg';
        if (is_file($dest)) {
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

    /**
     * Save clip thumb by Clip ID
     * @used \Clips::set_temp_thumb \Clips_model::set_clip_res
     * @param $id
     * @return bool
     */
    function set_temp_thumb($id)
    {
        $dir = $this->config->item('clip_dir');
        $temp_file = $dir . 'temp_thumb/' . $id . '.jpg';
        $dest = $dir . 'thumb/' . $id . '.jpg';
        if (is_file($temp_file) && filesize($temp_file)) {
            if (copy($temp_file, $dest)) {
                unlink($temp_file);
                $this->set_clip_res($id, 'jpg', 0);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

#-----------------------------------------------------------------------------

    function faststart($file)
    {
        $temp = str_replace('.mp4', '_.mp4', $file);
        exec('/usr/local/bin/qt-faststart ' . $file . ' ' . $temp);
        if (is_file($temp) && (filesize($temp) > 0)) {
            unlink($file);
            rename($temp, $file);
        }
    }

#-----------------------------------------------------------------------------
    /**
     * Create resource by Clip ID and res_type
     * @used \Clips::resources
     * @see \Clips_model::delete_frames, \Clips_model::faststart, \Clips_model::set_clip_res
     * @param $id
     * @param $res_type - thumb | preview |
     * @param null $clip_data
     * @return string
     */
    function create_resource($id, $res_type, $clip_data = null)
    {
        $subdir = '';
        $ext = '';
        $resolution = '';
        $type = 3;
        $dir = $this->config->item('clip_dir');

        switch ($res_type) {
            /* case 'thumb':
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
              break; */
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

        /*if (!empty($clip_data['aspect'])) {
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
        }*/

        if (!$subdir) {
            return 'Error: unknown type of resource.';
        }

        //File type of HD-file
        $src_type = $this->db->query(
            'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = 2', $id)->result_array();
        $src_type = $src_type[0]['resource'];
        if (!$src_type) {
            return 'Delivery file is not registered.';
        }

        $src = $dir . 'res/' . $id . '.' . $src_type;
        if (!is_file($src)) {
            return 'Error: HD file not found.';
        }
        $dest = $dir . $subdir . $id . '.' . $ext;
        if (is_file($dest)) {
            unlink($dest);
        }

        /* $command = ($res_type == 'thumb') ?
          '/usr/local/bin/ffmpeg -i ' . $src . ' -f image2 -vframes 1 -ss ' . $ss
          . ' -s ' . $width . 'x' . $height . ' -y ' . $dest :
          '/usr/local/bin/ffmpeg -i "' . $src . '" -y -vcodec libx264 -s '
          . $width . 'x' . $height . ' -vb 768k ' . '"' . $dest . '"'; */

        $command = '/usr/local/bin/ffmpeg -i "' . $src .
            '" -vcodec libx264 -pix_fmt yuv420p -vb ' . $vb .
            ' -s ' . $width . 'x' . $height .
            ' -acodec aac -strict experimental -y "' . $dest . '"';

        // For Windows
        /* $command = 'C:\ffmpeg\bin\ffmpeg -i "' . $src .
          '" -vcodec libx264 -pix_fmt yuv420p -vb ' . $vb .
          ' -s ' . $width . 'x' . $height .
          ' -acodec aac -strict experimental -y "' . $dest . '"'; */

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
    /**
     * Remove resource by Clip ID and type
     * @param $id
     * @param string $type. type=* to delete records of all types
     */
    function delete_resource($id, $type = 'thumb')
    {
        $constraints = ['clip_id' => $id];

        if($type != '*'){
            $constraints['type'] = $this->res_type[$type];
        }

        $query = $this->db->get_where('lib_clips_res', $constraints);
        $rows = $query->result_array();

        if ($rows) {
            foreach ($rows as $res) {
                $file = $this->config->item('clip_dir') . $type . '/' .
                    $id . '.' . $res['resource'];
                if (is_file($file)) {
                    unlink($file);
                }
                if($res['location']){
                    if($s3Path = $this->getS3Path($res['location'])){
                        $this->aws3_sqs_delete_resources_model->put_job(['MessageBody' => $s3Path]);
                    }
                }
            }
        }
    }

    /**
     * @param $id
     * @return void
     */
    private function delete_thumbs_from_s3($id)
    {
        $resourcesPathsToDelete = $this->getThumbsPaths($id);

        if($resourcesPathsToDelete){
            foreach ($resourcesPathsToDelete as $path) {
                $this->aws3_sqs_delete_resources_model->put_job(['MessageBody' => $this->getS3Path($path)]);
                //$this->aws_model->delete_multiple_by_prefix($pathArray['host'],trim($pathArray['path'], '\/'));
            }
        }
    }

    /**
     * @param int $id
     * @return array
     */
    private function getThumbsPaths($id)
    {
        $resourcesPaths = [];
        foreach($this->tables_with_resources_paths as $table){
            $thumpPaths = $this->getThumbsPathsFromTable($table, $id);
            if($thumpPaths){
                foreach($thumpPaths as $thumb){
                    $resourcesPaths[] = $thumb['path'];
                }
            }
        }
        return $resourcesPaths;
    }

    /**
     * @param $tableName
     * @param $id
     * @return mixed
     */
    private function getThumbsPathsFromTable($tableName, $id)
    {
        $query = $this->db->query('SELECT path FROM '.$tableName.' WHERE clip_id=?', $id);
        return $query->result_array();
    }


    /** e.g. from http://video.naturefootage.com/previews/TSC/TSC160621/q3/TSC160621_0003.mp4
     * we'll get s3://s3.footagesearch.com/previews/TSC/TSC160621/q3/TSC160621_0003.mp4
     * @param $location
     * @return mixed
     */
    private function getS3Path($location)
    {
        $locationArray = parse_url($location);
        $viewHost = (isset($locationArray['scheme'])) ? $locationArray['scheme'].'://' : '//';
        $viewHost .= $locationArray['host'];
        $s3Host = $this->store['resources']['scheme'].'://'.$this->store['resources']['bucket'];
        $s3Path = str_replace($viewHost, $s3Host, $location);
        if(strpos($s3Path, $this->store['resources']['scheme']) !== false)
            return $s3Path;
        return false;
    }

    /**
     * Get clip path (to S3) by Clipd ID and type
     * @param $id
     * @param string $mode - thumb | motion_thumb | preview | res
     * @return string
     */
    function get_clip_path($id, $mode = 'thumb')
    {
        if (is_array($id)) {
            $id = intval($id['id']);
        }

        switch ($mode) {
            case 'thumb':
                $type = 0;
                $res = 'jpg';
                $this->db->where_in('resource', $this->img_types);
                $dir = 'thumb';
                break;
            case 'motion_thumb':
                $type = 0;
                $res = 'mp4';
                $dir = 'thumb';
                $this->db->where_in('resource', $this->motion_types);
                break;
            case 'preview':
                $type = 1;
                $res = 'mp4';
                $dir = 'preview';
                break;
			case 'hdpreview':
                $type = 5;
                $res = 'mp4';
                $dir = 'previews';
                break;
            case 'res':
                $type = 2;
                $dir = 'res';
                break;
            default:
                $type = 0;
        }

        $query_params = ['clip_id' => $id, 'type' => $this->res_type[$mode]];

        if(isset($res)){
            $query_params['resource'] = $res;
        }

        $row = $this->get_data_from_lib_clips_res($query_params);

        if ($row) {
            return $row[0]['location'];//$path;
        }
        if($type!== 0){
            $query_params['type'] = 0;
            $row = $this->get_data_from_lib_clips_res($query_params);
        }
        if ($row) {
            return $row[0]['location'];//$path;
        }
        return false;
        //return $this->getFilePath($id, $res, $mode);
    }

    /**
     * @param array $query_params
     * @return bool
     */
    private function get_data_from_lib_clips_res($query_params = [])
    {
        if(!$query_params)
            return false;

        $query = $this->db->get_where('lib_clips_res', $query_params);

        return $query->result_array();
    }

    /**
     * get  clips path to resource location, by clips id and mode
     *
     * @see get_clip_path -> same but get data by all clips and res within one sql query
     *
     * @param array $clipsId array of clips id
     * @param array $mode array of mode to get, possible values are (@see $this->res_type init in construct method)
     * [thumb, motion_thumb, preview, res]
     *
     * @return array in format [
     *      ...
     *      'clip_id' => ['thumb' => 'location', 'preview' => location, ...]
     *      ...
     * ]
     */
    public function get_clips_path(array $clipsId, array $mode)
    {
        if (empty($clipsId)) {
            return [];
        }

        // filter type from input $mode array
        $types = array_intersect_key($this->res_type, array_flip($mode));

        if (empty($types)) {
            return [];
        }

        $query = $this->db->from('lib_clips_res')
            ->where_in('clip_id', $clipsId)
            ->where_in('type', array_unique($types));

        // db result rows
        $rows = $query->get()->result_array();

        $result = [];

        // convert db result to array structure where rows are grouped by clip id
        array_walk($rows, function($item) use (&$result, $types) {
            $type = array_search($item['type'], $types);
            if ($type && in_array($type, ['thumb', 'motion_thumb'])) {
                $type = $this->determineTypeByResource($item['resource']);
            }

            if (!$type) {
                return;
            }
            
            if (!array_key_exists($item['clip_id'], $result)) {
                $result[$item['clip_id']] = [];
            }
            $result[$item['clip_id']][$type] = $item['location'];
        });

        return $result;
    }

    /**
     * get string resource and check img_types, motion_types array to determine which type is it
     *
     * @param $resource
     *
     * @return string | null;
     */
    private function determineTypeByResource($resource)
    {
        if (in_array($resource, $this->img_types)) {
            return 'thumb';
        }

        if (in_array($resource, $this->motion_types)) {
            return 'motion_thumb';
        }

        return null;
    }

    /**
     * @param $resourceKey - path   preview/AB/2/AB01_001.mov
     * @param string $cloudDomain |orders|video|- subdomain for CloudFront
     * @param int $expire - time live link - 30min (1800)
     * @return string
     */
    public function cloudFrontGetLink($resourceKey, $cloudDomain = 'video', $expire = 1800)
    {
        $resourceKey = substr($resourceKey, 1);
        $cloudFront = \Aws\CloudFront\CloudFrontClient::factory(
            array(
                'credentials' => array(
                    'key' => $this->store['s3']['key'],
                    'secret' => $this->store['s3']['secret']
                ),
                'region' => $this->store['s3']['region-cloudfront'],
                'version' => '2014-11-06'
            )
        );

        // Setup parameter values for the resource
        //$streamHostUrl = 'https://'.$this->_randomStr().'.'.$cloudDomain.'.naturefootage.com';
        $streamHostUrl = '' . $cloudDomain . '.naturefootage.com';
        $expires = time() + $expire;
        //return $resourceKey;
        // Create a signed URL for the resource using the canned policy
        $signedUrlCannedPolicy = $cloudFront->getSignedUrl([
            'url' => $streamHostUrl . '/' . $resourceKey,
            'expires' => $expires,
            'private_key' => FCPATH . 'scripts/cert/pk-APKAJDLDWSS527DLAPWA-private.pem',
            'key_pair_id' => 'APKAJDLDWSS527DLAPWA'
        ]);

        return $signedUrlCannedPolicy;
    }

    /**
     * Get path
     * @used \Clips_model::get_clip_path
     * @param $resourceName
     * @param $resourceExtension
     * @param $resourceType
     * @return string
     */
    private
    function getFilePath($resourceName, $resourceExtension, $resourceType)
    {
        if (isset($this->store[$resourceType]['scheme'])) {
            switch ($this->store[$resourceType]['scheme']) {
                case 'ftp':
                    $path = 'ftp://' . $this->store[$resourceType]['username'] . ':'
                        . $this->store[$resourceType]['password'] . '@'
                        . $this->store[$resourceType]['host'] . ':' . $this->store[$resourceType]['port']
                        . rtrim($this->store[$resourceType]['path'], '/') . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
                    break;
                case 's3':
                    $path = '//' . $this->store[$resourceType]['bucket'] . rtrim($this->store[$resourceType]['path'], '/')
                        . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
                    break;
                default:
                    $path = rtrim($this->store[$resourceType]['path'], '/') . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
            }
        } else {
            $path = rtrim($this->store[$resourceType]['path'], '/') . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
        }
        return $path;
    }

    function get_clip_res($id, $type = 0)
    {
        $query = $this->db->get_where('lib_clips_res', array('clip_id' => $id, 'type' => $type));
        $rows = $query->result_array();

        return $rows;
    }

    /**
     * Return which (int) resources have Clip
     * @param $res_type
     * @return mixed
     */
    function get_resources_count($res_type)
    {
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

    /**
     * Return array with resources by Clip ID
     * @used \Clips::resources
     * @param $id
     * @return array  - array('hd','img','thumb','view');
     */
    function get_resources($id)
    {
        $query = $this->db->query('SELECT resource, type FROM lib_clips_res WHERE clip_id=?', $id);
        $rows = $query->result_array();

        $resources = array(
            'hd' => array('Delivery', null),
            'img' => array('Image thumbnail', null),
            'thumb' => array('Video thumbnail', null),
            'preview' => array('Preview', null)
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

    /**
     * @param $data
     * @return string
     */
    function get_clip_thumbs($data)
    {
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

//    function get_clip_thumb($data) {
//        $path = $this->config->item('clip_path');
//
//        $query = $this->db->query(
//            "SELECT * FROM lib_clips_res WHERE clip_id=? AND type=0 AND resource IN('"
//            . implode("','", $this->img_types) . "')", array($data['id']));
//        $rows = $query->result_array();
//
//        if (count($rows)) {
//            $file = $path . $data['folder'] . '/thumb/' . $data['code'] . '.' . $rows[0]['resource'];
//        } else {
//            $file = $path . 'no_image.gif';
//        }
//
//        return $file;
//    }

    function get_clip_thumb($clip_id, $place = '')
    {
        $type = $this->get_thumb_type_by_place($place);

        $this->db->select('location');
        $query = $this->db->get_where('lib_clips_res', array('clip_id' => $clip_id, 'type' => $type, 'resource' => 'jpg'));
        $rows = $query->result_array();
        if ($rows)
            return $rows[0]['location'];
        else
            return false;
    }

    function get_img($id)
    {
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

    /**
     * @used
     * @see \Clips_model::get_clips_count,\Clips_model::get_clips_list
     * @param $lang
     * @param $filter
     * @param $limit
     * @return mixed
     */
    function search($lang, $filter, $limit)
    {
        $data['all'] = $this->get_clips_count($lang, $filter);
        $data['results'] = $this->get_clips_list($lang, $filter, $limit);

        return $data;
    }

    function get_linked_keywords($lang, $keywords)
    {
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

    /**
     * Order Itms count
     * @param $id
     * @return mixed   - int
     */
    function get_sale_count($id)
    {
        $row = $this->db->query(
            'SELECT COUNT(1) total
FROM lib_orders_items oi
INNER JOIN lib_orders o ON o.id = oi.order_id AND o.status = 3
WHERE oi.item_id = ? AND oi.item_type = 2', array($id))->result_array();
        return $row[0]['total'];
    }

    function get_thumb_by_code($code)
    {
        $data = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', array($code))->result();

        if (empty($data)) {
            return NULL;
        }

        $id = $data[0]->id;
        $thumb = $this->get_clip_path($id, 'thumb');

        return $thumb;
    }

    /**
     * @used \Uploadstools::submitUpload
     * @param $file
     * @param string $submission_code
     * @param int $user_id
     * @return mixed
     */
    function create_clip($file, $submission_code = '', $user_id = 0)
    {
        $this->load->model('submissions_model');
        $submission = false;
        if ($submission_code) {
            $submission_id = $this->submissions_model->create_submission($submission_code, $user_id);
            $submission = $this->submissions_model->get_submission($submission_id);
        }
        $hd_type = $this->api->get_file_ext($file);
        //$clip_code = basename($file, '.' . $hd_type);
        if ($submission) {
            $last_code = $this->get_submission_last_clip_code($submission['id']);
            if ($last_code) {
                $parts = explode('_', $last_code);
                $clips_count = (int)$parts[1];
            } else {
                $clips_count = 0;
            }
            //$clips_count = $this->get_clips_count_by_submission($submission['id']);
            $clip_code = $submission['code'] . '_' . str_pad($clips_count + 1, 4, 0, STR_PAD_LEFT);
        } else {
            $clips_count = $this->get_clips_count();
            $clip_code = str_pad($clips_count + 1, 4, 0, STR_PAD_LEFT);
        }

        $hostname = gethostname();

        $transcode_option = 'Cruzio';
        if ($hostname == "NF-MacPro.local") {
            $transcode_option = '810 Office';
        }

        $clip = array(
            'code' => $clip_code,
            'ctime' => date('Y-m-d H:i:s'),
            'client_id' => $user_id,
            'submission_id' => $submission ? $submission['id'] : 0,
            //'original_filename' => str_replace('tmp_', '', basename($file))
            'original_filename' => basename($file),
            'transcode_location' => $transcode_option
        );

        $this->db_master->insert('lib_clips', $clip);
        $clip_id = $this->db_master->insert_id();
        //$filename = str_replace($clip_code, $clip_id, $file);
        //$filename = str_replace(basename($file, '.' . $hd_type), $clip_id, $file);
        if (strtolower($hd_type) != 'r3d') {
            $filename = str_replace(basename($file, '.' . $hd_type), $clip_code, $file);
            rename($file, $filename);
        } else
            $filename = $file;

        $this->set_clip_res($clip_id, $hd_type, 2, $filename);

        /*$clip_content = array(
            'clip_id' => $clip_id,
            'lang' => 'en',
            'title' => $clip_code
        );
        $this->db_master->insert('lib_clips_content', $clip_content);*/

        //$clip_data = $this->metadata($clip_id, $filename);
        //$this->create_thumb($clip_id, $clip_data);

        return $clip_id;
    }

    function is_code_exists($filename)
    {
        $code = substr($filename, 0, strrpos($filename, '.'));
        $row = $this->db->query('SELECT id FROM lib_clips WHERE code = ?', $code)
            ->result();
        return !empty($row);
    }

    function get_frame_rate_list()
    {
        $query = $this->db->
        distinct()->
        select('digital_file_frame_rate')->
        from('lib_clips')->
        order_by('digital_file_frame_rate')->get();

        $rows = $query->result();
        $result = array();

        if ($rows) {
            foreach ($rows as $row) {
                $result[] = $row->frame_rate;
            }
        }

        return $result;
    }

    function get_cats_clip($id, $lang = 'en')
    {
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

    function get_clip_sequences($id, $lang = 'en')
    {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if ($group['is_editor'] && $uid) {
            $filter = ' WHERE s.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT s.*, cs.clip_id checked FROM lib_sequences s
            LEFT JOIN lib_clip_sequences cs ON s.id = cs.sequence_id AND cs.clip_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_backend_lb_sequece($id)
    {

        $query = $this->db->query('SELECT c.backend_lb_id,c.type,d.title,d.is_sequence FROM lib_backend_lb_items c LEFT JOIN lib_backend_lb d ON c.backend_lb_id=d.id WHERE c.item_id=' . $id . '');
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_bins($id, $lang = 'en')
    {
//        $this->load->model('groups_model');
//        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
//        $group = $this->groups_model->get_group_by_user($uid);
//        $filter = '';
//        if ($group['is_editor'] && $uid) {
//            $filter = ' WHERE b.provider_id = ' . (int)$uid;
//        }
//        $query = $this->db->query('SELECT b.*, cb.clip_id checked FROM lib_bins b
//            LEFT JOIN lib_clip_bins cb ON b.id = cb.bin_id AND cb.clip_id = ?' . $filter, array($id));
//        $rows = $query->result_array();
        $rows = $this->get_clip_clipbins($id);
        return $rows;
    }

    function get_clip_galleries($id, $lang = 'en')
    {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if ($group['is_editor'] && $uid) {
            $filter = ' WHERE g.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT g.*, cg.clip_id checked FROM lib_galleries g
            LEFT JOIN lib_clip_galleries cg ON g.id = cg.gallery_id AND cg.clip_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_submissions($id, $lang = 'en')
    {
        $query = $this->db->query('SELECT s.*, c.id checked FROM lib_submissions s
            LEFT JOIN lib_clips c ON s.id = c.submission_id AND c.id = ?', array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clip_clipbins($id, $lang = 'en')
    {
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $filter = '';
        if ($group['is_editor'] && $uid) {
            $filter = ' WHERE lb.provider_id = ' . (int)$uid;
        }
        $query = $this->db->query('SELECT lb.*, lbi.item_id checked FROM lib_lb lb
            LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id AND lbi.item_id = ?' . $filter, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function get_clipIds_by_BackclipbinId($id, $offset = 0, $limit = 999999)
    {
        $query = $this->db->query('SELECT item_id AS id FROM lib_backend_lb_items WHERE backend_lb_id= ? LIMIT ' . $offset . ',' . $limit, array($id));
        $rows = $query->result_array();
        return $rows;
    }

    function save_cats($id, $ids)
    {
        $this->db_master->delete('lib_clips_cats', array('clip_id' => $id));

        foreach ((array)$ids as $cat_id) {
            if ($cat_id) {
                $data['clip_id'] = $id;
                $data['cat_id'] = $cat_id;

                $this->db_master->insert('lib_clips_cats', $data);
            }
        }
    }

    function save_sequences($id, $ids)
    {
        $this->db_master->delete('lib_clip_sequences', array('clip_id' => $id));

        foreach ((array)$ids as $item_id) {
            if ($item_id) {
                $data['clip_id'] = $id;
                $data['sequence_id'] = $item_id;
                $this->db_master->insert('lib_clip_sequences', $data);
            }
        }
    }

    function save_bins($id, $ids)
    {
        $this->db_master->delete('lib_clip_bins', array('clip_id' => $id));

        foreach ((array)$ids as $item_id) {
            if ($item_id) {
                $data['clip_id'] = $id;
                $data['bin_id'] = $item_id;
                $this->db_master->insert('lib_clip_bins', $data);
            }
        }
    }

    function save_galleries($id, $ids)
    {
        $this->db_master->delete('lib_clip_galleries', array('clip_id' => $id));

        foreach ((array)$ids as $item_id) {
            if ($item_id) {
                $data['clip_id'] = $id;
                $data['gallery_id'] = $item_id;
                $this->db_master->insert('lib_clip_galleries', $data);
            }
        }
    }

    function save_clipbins($id, $ids)
    {
        $this->db_master->delete('lib_lb_items', array('item_id' => $id));

        foreach ((array)$ids as $item_id) {
            if ($item_id) {
                $data['item_id'] = $id;
                $data['lb_id'] = $item_id;
                $this->db_master->insert('lib_lb_items', $data);
            }
        }
    }

#-----------------------------------------------------------------------------

    function upload_attachment($id)
    {

        $exts = array('jpg', 'jpeg', 'gif', 'png', 'pdf');

        if (is_uploaded_file($_FILES['attachment']['tmp_name'])) {
            $dest_dir = $this->config->item('attachments_dir');
            $ext = strtolower($this->api->get_file_ext($_FILES['attachment']['name']));

            if (in_array($ext, $exts)) {
                $source = $_FILES['attachment']['tmp_name'];
                $attachment_id = 1;
                $dest = $dest_dir . $id . '-' . $attachment_id . '.' . $ext;
                while (is_file($dest)) {
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

                if (count($row)) {
                    $this->db_master->where('id', $row[0]['id']);
                    $this->db_master->update('lib_clips_attachments', $data);
                } else
                    $this->db_master->insert('lib_clips_attachments', $data);
            } else {
                return 'Wrong file type, must be one of: ' . implode(', ', $exts) . '.';
            }
        } else {
            return 'File not selected.';
        }
    }

    function get_attachments($id)
    {
        $query = $this->db->query('SELECT id, file FROM lib_clips_attachments WHERE clip_id=?', $id);
        $attachments = $query->result_array();
        $images_exts = array('jpg', 'jpeg', 'gif', 'png');

        if (count($attachments)) {
            foreach ($attachments as $key => $attachment) {
                $ext = $this->api->get_file_ext($attachment['file']);
                $attachments[$key]['filetype'] = $ext;
                $attachments[$key]['filepath'] = $this->config->item('attachments_path') . $attachment['file'];
                if (in_array($ext, $images_exts)) {
                    $attachments[$key]['is_image'] = 1;
                }
            }
        }

        return $attachments;
    }

#------------------------------------------------------------------------------------------------

    function delete_attachment($id)
    {

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

    function get_clip_category($id, $lang = 'en')
    {
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

    function get_clip_public_category($id, $lang = 'en')
    {
        $row = $this->db->query(
            'SELECT c.id
      FROM lib_cats_content cc
      INNER JOIN lib_cats c ON c.id = cc.cat_id
      INNER JOIN lib_clips_cats clc ON clc.cat_id = cc.cat_id AND clc.clip_id = ?
      WHERE cc.lang = ? AND c.private = 0 LIMIT 1', array($id, $lang))->result_array();
        return $row[0]['id'];
    }

#------------------------------------------------------------------------------------------------

    function make_uri($item)
    {
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

    function update_clip_statistic($clip_id)
    {
        $where['date'] = date('Y-m-d');
        $where['clip_id'] = (int)$clip_id;
        $uid = $this->session->userdata('client_uid') ? (int)$this->session->userdata('client_uid') : 0;
        $where['user_id'] = $uid;


        $query = $this->db->get_where('lib_clips_statistic', $where);

        $row = $query->result_array();

        if (count($row)) {
            $views_count = $row[0]['views_count'] + 1;
            $this->db_master->update('lib_clips_statistic', array('views_count' => $views_count), array('id' => $row[0]['id']));
        } else {
            $data = array(
                'clip_id' => $clip_id,
                'user_id' => $uid,
                'views_count' => 1,
                'date' => date('Y-m-d')
            );
            $this->db_master->insert('lib_clips_statistic', $data);
        }
    }

    function get_clip_statistic($clip_id = null, $filter = null, $lang = 'en')
    {
        $clip_filter = '';
        if ($clip_id) {
            $clip_filter = 'clip_id = ' . $clip_id;
            if ($filter) {
                $filter .= ' AND ' . $clip_filter;
            } else {
                $filter = $clip_filter;
            }
        }
        $where = $filter ? ' WHERE ' . $filter : '';
        $result = $this->db->query("SELECT *, ( SELECT name FROM lib_extra_statistic_actions AS act WHERE act.type = stat.action_type ) AS 'action' FROM lib_clips_extra_statistic AS stat {$where}", array($clip_id));
        return (is_object($result)) ? $result->result_array() : array();
    }

#-----------------------------------------------------------------------------

    function specify_resource_location($id, $res_type, $location)
    {
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
            /* case 'preview':
              $subdir = 'preview/';
              $ext = 'mp4';
              $width = 512;
              $height = 288;
              $type = 1;
              $vb = '1024k';
              break; */
        }

        if (!$subdir) {
            return 'Error: unknown type of resource.';
        }

        $ext = pathinfo($location, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $exts)) {
            return 'Error: incorrect file type.';
        }

        $src_type = $this->db->query(
            'SELECT resource FROM lib_clips_res WHERE clip_id = ? AND type = ?', array($id, $type))->result_array();
        $src_type = $src_type[0]['resource'];
        if ($src_type) {
            $src = $dir . 'res/' . $id . '.' . $src_type;
            if ($src == $location) {
                return 'Error: this location already specified.';
            } else {
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

    function get_clip_price($id, $start_time = null, $end_time = null)
    {
        $row = $this->db->query('SELECT price, price_per_second, duration FROM lib_clips WHERE id = ? LIMIT 1', array((int)$id))->result_array();
        $price = false;
        if ($row[0]['price'] != 0.00) {
            if ($start_time || $end_time) {
                $start = $start_time ? $start_time : 0.00;
                $end = $end_time ? $end_time : $row[0]['duration'];
                $duration = round($end - $start, 2);
                if ($duration == round($row[0]['duration'], 2)) {
                    $price = $this->api->price_format($row[0]['price']);
                } else {
                    $price = $this->api->price_format($duration * $row[0]['price_per_second']);
                }
            } else {
                $price = $this->api->price_format($row[0]['price']);
            }
        }
        return $price;
    }

    public function add_to_index($clip_id, $optimize = false)
    {
//        $index_data = $this->get_clips_index_data($clip_id);
//        if (!$this->solr_adapter)
//            $this->solr_adapter = new SorlSearchAdapter();
//        if ($index_data) {
//            $this->solr_adapter->addToIndex($index_data, $optimize);
//            $this->check_solr_optimize();
//        }
    }

    public function solr_optimize()
    {
//        if (!$this->solr_adapter)
//            $this->solr_adapter = new SorlSearchAdapter();
//        $this->solr_adapter->optimize();
    }

    /**
     * DB Table lib_settings insert/update param solr_optimize = 1
     */
    public function check_solr_optimize()
    {
        $query = $this->db->get_where('lib_settings', array('name' => 'solr_optimize'));
        $row = $query->result_array();

        if (count($row)) {
            $this->db_master->where('name', 'solr_optimize');
            $this->db_master->update('lib_settings', array('value' => 1));
        } else {
            $this->db_master->insert('lib_settings', array('name' => 'solr_optimize', 'value' => 1));
        }
    }

    public function delete_from_index($clip_id)
    {
//        if (!$this->solr_adapter)
//            $this->solr_adapter = new SorlSearchAdapter();
//        if (is_array($clip_id))
//            $this->solr_adapter->deleteByMultipleIds($clip_id);
//        else
//            $this->solr_adapter->deleteById($clip_id);
    }

    /**
     * Get clip data by ID for SOLR
     * @param int $id
     * @param array $limit
     * @return mixed
     */
    public function get_clips_index_data($id = 0, $limit = array())
    {
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
        if ($id) {
            if (is_array($id))
                $this->db->where_in('c.id', $id);
            else
                $this->db->where('c.id', (int)$id);
        }
        if ($limit) {
            if (isset($limit['limit']) && isset($limit['offset']))
                $this->db->limit($limit['limit'], $limit['offset']);
            elseif (isset($limit['limit']))
                $this->db->limit($limit['limit']);
        }
        $this->db->select('c.id, c.code, c.active, c.client_id, c.collection, c.brand, c.license, c.price_level, c.master_format,
        c.master_frame_size, c.source_format, c.source_frame_size, c.digital_file_frame_size, c.creation_date, c.duration, c.like_count, c.code as title, c.description, c.keywords, rc.weight');
        $this->db->from('lib_clips c');
        $this->db->join('lib_rank_clips rc', 'c.id = rc.clip_id', 'left');
        $query = $this->db->get();
        $rows = $query->result_array();
        foreach ($rows as $id => $row) {
            $rows[$id]['weight'] = (is_null($row['weight'])) ? 0 : $row['weight'];
            if (!empty($rows[$id]['country']))
                $rows[$id]['keywords'] .= ',' . $rows[$id]['country'];
            $cats = $this->db->query('SELECT ctc.cat_id, ctc.title
            FROM lib_cats_content ctc
			INNER JOIN lib_clips_cats clc ON ctc.cat_id = clc.cat_id AND clc.clip_id = ' . $row['id'])->result_array();
            if ($cats) {
                foreach ($cats as $cat) {
                    $rows[$id]['category'][] = $cat['title'];
                    $rows[$id]['category_id'][] = $cat['cat_id'];
                }
            }

            /*
              if($rows[$id]['active'] == 2){
              $rows[$id]['active'] = 0;
              }
             */


            if ($rows[$id]['active'] == 1) {
                $rows[$id]['active'] = true;
            } elseif ($rows[$id]['active'] == 0) {
                $rows[$id]['active'] = false;
            }


            /* $galleries = $this->db->query('SELECT g.id, g.title
              FROM lib_galleries g
              INNER JOIN lib_clip_galleries cg ON g.id = cg.gallery_id AND cg.clip_id = ' . $row['id'])->result_array(); */
            $galleries = $this->db->query('SELECT i.backend_lb_id  FROM lib_backend_lb_items AS i INNER JOIN lib_backend_lb AS l ON i.backend_lb_id=l.id WHERE i.item_id=' . $row['id'] . ' AND l.is_gallery !=0')->result_array();
            if ($galleries) {
                foreach ($galleries as $gallery) {
                    //$rows[$id]['category'][] = $cat['title'];
                    $rows[$id]['gallery_id'][] = $gallery['backend_lb_id'];
                }
            }

            $keywords = $this->get_clip_keywords($row['id']);
            if ($keywords) {
                foreach ($keywords as $keyword) {
                    if (in_array($keyword['section'], $keywords_sections_for_indexing)) {
                        $rows[$id][$keyword['section']][] = $keyword['keyword'];
                    }
                }
            }


//            $collections = $this->db->query('SELECT c.id
//            FROM lib_collections c
//			INNER JOIN lib_clips_collections cc ON c.id = cc.collection_id AND cc.clip_id = ' . $row['id'])->result_array();
//            if ($collections) {
//                foreach ($collections as $collection) {
//                    $rows[$id]['collection_id'][] = $collection['id'];
//                }
//            }

            // Format categories
            if (stripos($row['source_format'], '3D') !== false) {
                $rows[$id]['format_category'][] = '3D';
            }
            if (stripos($row['source_frame_size'], 'Ultra HD') !== false || stripos($row['master_frame_size'], 'Ultra HD') !== false || stripos($row['digital_file_frame_size'], 'Ultra HD') !== false) {
                $rows[$id]['format_category'][] = 'Ultra HD';
            }
            if (stripos($row['source_frame_size'], 'HD') !== false || stripos($row['master_frame_size'], 'HD') !== false || stripos($row['digital_file_frame_size'], 'HD') !== false) {
                $rows[$id]['format_category'][] = 'HD';
            }
            if (stripos($row['source_frame_size'], 'SD') !== false || stripos($row['master_frame_size'], 'SD') !== false || stripos($row['digital_file_frame_size'], 'SD') !== false) {
                $rows[$id]['format_category'][] = 'SD';
                $rows[$id]['format_sort'][] = 1;
            } else {
                $rows[$id]['format_sort'][] = 0;
            }
            unset($rows[$id]['source_frame_size'], $rows[$id]['master_frame_size'], $rows[$id]['digital_file_frame_size']);

            $creation_timestamp = strtotime($rows[$id]['creation_date']);
            $rows[$id]['creation_date'] = date('Y-m-d', $creation_timestamp) . 'T' . date('H:i:s', $creation_timestamp) . 'Z';
            $rows[$id]['duration'] = (int)$rows[$id]['duration'];
        }

        return $rows;
    }

    public function get_clips_by_ids($ids, $sort = array(), $lang = 'en')
    {

        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $sort_str = '';
        if (!empty($ids)) {
            $filter = ' WHERE c.id IN (' . implode(',', $ids) . ')';
            $sort_str = ' ORDER BY FIELD(c.id,' . implode(',', $ids) . ') ';
        }

//        if($sort){
//            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
//        }

        $query = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $filter . $sort_str);
        $rows = $query->result_array();
        $base_url = $this->config->base_url();
        foreach ($rows as &$row) {
            $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
            $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
            $row['preview'] = $this->get_clip_path($row['id'], 'preview');
            $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
            $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
            $row['description'] = htmlspecialchars($row['description']);

            $source_format_display = array();
            if ($row['source_format']) {
                $source_format_display[] = $row['source_format'] . ($row['camera_chip_size'] ? ' (' . $row['camera_chip_size'] . ')' : '');
            }
            if ($row['source_frame_size']) {
                $source_format_display[] = $row['source_frame_size'];
            }
            if ($row['source_frame_rate']) {
                $source_format_display[] = $row['source_frame_rate'];
            }
            if ($row['source_codec']) {
                $source_format_display[] = $row['source_codec'];
            }
            if ($row['bit_depth']) {
                $source_format_display[] = $row['bit_depth'];
            }
            if ($row['color_space']) {
                $source_format_display[] = $row['color_space'];
            }
            if ($source_format_display)
                $row['source_format_display'] = implode(', ', $source_format_display);
        }

        return $rows;
    }

/////?????IMR@N?????/////
    public function get_filtered_clips($filter, $offset = 0, $limit = 100, $sort = array(), $facet = array(), $is_admin = false, $lang = 'en')
    {

        if ($sort) {
            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
        }
        if ($offset !== false) {
            $sort_str .= ' LIMIT ' . $offset . ',' . $limit . ' ';
        }
//        echo 'SELECT c.*, cc.title, cc.description, cc.location, rc.weight
//			FROM lib_clips c
//			INNER JOIN lib_clips_content cc ON c.id=cc.clip_id AND cc.lang = ?
//			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $or_filters . $sort_str;

        $this->build_filter_sql_backend($filter, '');


        $query = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $this->filter_sql . $sort_str);
//echo $this->db->last_query();
        $rows = $query->result_array();
        $base_url = $this->config->base_url();
        foreach ($rows as &$row) {
            $row['url'] = rtrim($base_url, '/') . '/clips/' . $row['id'] . $this->config->item('url_suffix');
            $row['thumb'] = $this->get_clip_path($row['id'], 'thumb');
            $row['preview'] = $this->get_clip_path($row['id'], 'preview');
            $row['motion_thumb'] = $this->get_clip_path($row['id'], 'motion_thumb');
            $row['download'] = rtrim($base_url, '/') . '/' . $lang . '/clips/content/' . $row['id'];
            $row['keywords'] = $this->getAllKeywordsByClipId($row['id']);
            $row['keywords_types'] = $this->getAllKeywordsByClipId($row['id']);
            $row['rating_result'] = $this->getRatingData($row['id'], $row['client_id']);
//            echo file_put_contents('test.txt', $row['keywords'], FILE_APPEND);
            $row['total_likes'] = $this->getClipTotalLikes($row['id']);
            $row['current_user_like'] = $this->getClipCurrentUserLike($row['id']);

            $source_format_display = array();
            if ($row['source_format']) {
                $source_format_display[] = $row['source_format'] . ($row['camera_chip_size'] ? ' (' . $row['camera_chip_size'] . ')' : '');
            }
            if ($row['source_frame_size']) {
                $source_format_display[] = $row['source_frame_size'];
            }
            if ($row['source_frame_rate']) {
                $source_format_display[] = $row['source_frame_rate'];
            }
            if ($row['source_codec']) {
                $source_format_display[] = $row['source_codec'];
            }
            if ($row['bit_depth']) {
                $source_format_display[] = $row['bit_depth'];
            }
            if ($row['color_space']) {
                $source_format_display[] = $row['color_space'];
            }
            if ($source_format_display)
                $row['source_format_display'] = implode(', ', $source_format_display);

            $delivery_methods = $rf_delivery_methods = array(
                array(
                    'id' => 666,
                    'code' => 'Formats Container',
                    'title' => 'Formats Container',
                    'delivery' => 'Download',
                )
            );

//For RF Clips
            if ($row['license'] == 1) {
                $query = $this->db->query('
                    SELECT do.id, do.description, do.delivery, do.resolution, pf.factor price_factor FROM lib_rf_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
                $delivery_formats = $query->result_array();
                if (count($delivery_formats)) {
                    foreach ($rf_delivery_methods as $key => $method) {
                        foreach ($delivery_formats as $format) {
                            $format['delivery'] = 'Download';
                            if ($format['delivery'] == $method['delivery']) {
                                if (!isset($rf_delivery_methods[$key]['formats'])) {
                                    $rf_delivery_methods[$key]['formats'] = array();
                                }

                                if (strpos(strtolower($format['description']), 'digital file') !== false) {
                                    $format['default'] = 1;
                                }

                                $description_parts = array();
                                $description_parts = $this->_delivery_methods($row, $format);

                                if ($description_parts) {
                                    $format['description'] = implode(' ', $description_parts);
                                }

//

                                $rf_delivery_methods[0]['formats'][0] = $format;
                            }
                        }
                    }
                    foreach ($rf_delivery_methods as $key => $method) {
                        if (!isset($method['formats'])) {
                            unset($rf_delivery_methods[$key]);
                        }
                    }
                    $row['delivery_methods'] = $rf_delivery_methods;
                } else {
                    $description_parts = array();
                    $description_parts = $this->_delivery_methods($row);
                    if ($description_parts) {
                        $format['description'] = $description_parts[0]; //implode(' ', $description_parts);
                    }

                    if (isset($format['description']))
                        $rf_delivery_methods[0]['formats'][] = $format;
                    $row['delivery_methods'] = $rf_delivery_methods;
                }
            } //RM clips
            else {
                $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
                    FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
                $delivery_formats = $query->result_array();
                if (count($delivery_formats)) {
                    foreach ($delivery_methods as $key => $method) {
                        foreach ($delivery_formats as $format) {
                            $format['first_description'] = $format['description'];
                            if (true || $format['delivery'] == $method['delivery']) {
                                if (!isset($delivery_methods[$key]['formats'])) {
                                    $delivery_methods[$key]['formats'] = array();
                                }
                                if (strpos(strtolower($format['description']), 'custom frame rate') === false) {

                                    if (strpos(strtolower($format['description']), 'digital file') !== false) {
                                        $format['default'] = 1;
                                    }

                                    $description_parts = array();

                                    $description_parts = $this->_delivery_methods($row, $format);

                                    if ($description_parts) {
                                        $format['description'] = implode(' ', $description_parts);
                                    }
                                } else {
                                    if (!isset($custom_frame_rates)) {
                                        $custom_frame_rates = array();
                                        if (!isset($custom_frame_rates[$format['destination']])) {
                                            $this->db->where('media', $format['destination']);
                                            $custom_frame_rates[$format['destination']] = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
                                        }
                                    }
                                    $format['custom_frame_rates'] = $custom_frame_rates[$format['destination']];
                                }
                                if (!trim($format['description']))
                                    $format['description'] = $format['first_description'];
                                $delivery_methods[$key]['formats'][] = $format;
                            }
                        }
                    }
                    foreach ($delivery_methods as $key => $method) {
                        if (!isset($method['formats'])) {
                            unset($delivery_methods[$key]);
                        }
                    }
                    $row['delivery_methods'] = $delivery_methods;
                } else {
                    $description_parts = array();
                    $description_parts = $this->_delivery_methods($row);
                    if ($description_parts) {
                        $format['description'] = implode(' ', $description_parts);
                    }
                    if (isset($format['description']))
                        $rf_delivery_methods[0]['formats'][] = $format;
                    $row['delivery_methods'] = $rf_delivery_methods;
                }
            }
        }
        return $rows;
    }

    function get_search_filtered_clips($filter, $offset = 0, $limit = 100, $sort = array(), $facet = array(), $is_admin = false, $lang = 'en')
    {
        //$this->build_filter_sql_backend($filter);
        if ($sort) {
            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
        }


        if ($offset !== false && $this->group['is_admin']) {
            $sort_str .= ' LIMIT ' . $offset . ',' . $limit . ' ';
        }
        // $this->build_filter_sql_backend($filter, '');
        $query = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $this->filter_sql . $sort_str);
        //echo $this->db->last_query();
        $rows = $query->result_array();
        $query_col = $this->db->query('select id,name from lib_collections where name="' . $rows[0]['collection'] . '"');
        $col_res = $query_col->result_array();
        $collection_selected_id = $col_res[0]['id'];
        $collection_selected_name = $col_res[0]['name'];
        $base_url = $this->config->base_url();
        $filter_arr = array();
        $filter_arr['collection_filter'] = 0;
        $filter_arr['brand_filter'] = 0;
        $filter_arr['license_rm'] = 0;
        $filter_arr['license_rf'] = 0;
        $filter_arr['budget'] = 0;
        $filter_arr['standard'] = 0;
        $filter_arr['premium'] = 0;
        $filter_arr['gold'] = 0;
        $filter_arr['3d'] = 0;
        $filter_arr['ultra_hd'] = 0;
        $filter_arr['hd'] = 0;
        $filter_arr['sd'] = 0;
        $filter_arr['offline'] = 0;
        $filter_arr['online'] = 0;
        $filter_arr['collection_filter_name'] = array();
        $filter_arr['brand_filter_name'] = array();
        $filter_arr['adminAction_filter_name'] = array();
        foreach ($rows as $row) {
            if (!in_array($row['collection'], $filter_arr['collection_filter_name'])) {
                array_push($filter_arr['collection_filter_name'], $row['collection']);
            }
            if (!in_array($row['brand'], $filter_arr['brand_filter_name'])) {
                array_push($filter_arr['brand_filter_name'], $row['brand']);
            }

            if (!in_array($row['admin_action'], $filter_arr['adminAction_filter_name']) && $row['admin_action'] != '0') {
                array_push($filter_arr['adminAction_filter_name'], $row['admin_action']);
            }

            if ($row['license'] == '2' && !empty($row['license'])) {
                $filter_arr['license_rm'] = 1;
            }
            if ($row['license'] == '1' && !empty($row['license'])) {
                $filter_arr['license_rf'] = 1;
            }
            if ($row['price_level'] == '1' && !empty($row['price_level'])) {
                $filter_arr['budget'] = 1;
            }
            if ($row['price_level'] == '2' && !empty($row['price_level'])) {
                $filter_arr['standard'] = 1;
            }
            if ($row['price_level'] == '3' && !empty($row['price_level'])) {
                $filter_arr['premium'] = 1;
            }
            if ($row['price_level'] == '4' && !empty($row['price_level'])) {
                $filter_arr['gold'] = 1;
            }
            if (strpos($row['master_frame_size'], '3D') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['3d'] = 1;
            }
            if (strpos($row['master_frame_size'], 'Ultra HD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['ultra_hd'] = 1;
            }
            if (strpos($row['master_frame_size'], 'HD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['hd'] = 1;
            }
            if (strpos($row['master_frame_size'], 'SD') !== false && !empty($row['master_frame_size'])) {
                $filter_arr['sd'] = 1;
            }
            if ($row['active'] == '1' && !empty($row['active'])) {
                $filter_arr['online'] = 1;
            }
            if ($row['active'] == '0' && !empty($row['active'])) {
                $filter_arr['offline'] = 1;
            }
        }
//$filter_arr['collection_test'];

        return $filter_arr;
    }

    /**
     * ??
     * @param $clipId
     * @return mixed
     */
    function getClipCurrentUserLike($clipId)
    {
        $query = $this->db->query("select * from lib_clip_rating where user_id ='" . $this->session->userdata('uid') . "' and code='" . $clipId . "'");
        return $query->num_rows();
    }

    /**
     * ???
     * @param $clipId
     * @return mixed
     */
    function getClipTotalLikes($clipId)
    {
        $query = $this->db->query("select code from lib_clip_rating where code = '" . $clipId . "' and (name = 'user_rating' or name = 'admin_rating' or name = 'ip_rating')");
        return $query->num_rows();
    }

    public function get_cart_clips($ids, $lang = 'en')
    {
        $this->load->model('deliveryoptions_model');
        $rows = array();
        $filter = '';
        //$filter[] = 'c.client_id = ' . (int)$provider;
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if ($ids) {
            $filter[] = 'c.id IN (' . implode(',', $ids) . ')';
        }
        if ($filter) {
            $filter = 'WHERE ' . implode(' AND ', $filter);
        }


        $query = $this->db->query('
                SELECT c.id, c.code, c.license, c.duration, c.digital_file_frame_rate, c.digital_file_frame_size, c.digital_file_format, c.color_system,
                c.master_format, c.master_frame_size, c.master_frame_rate, c.price_level, c.license_restrictions,
                c.code as title, c.description, c.brand, c.pricing_category
                FROM lib_clips c ' . $filter); // remove c.aspect,
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
            if ($row['license'] == 1) {
                $query = $this->db->query('
                    SELECT do.id, do.description, do.delivery, do.resolution, pf.factor price_factor FROM lib_rf_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
                $delivery_formats = $query->result_array();
                if (count($delivery_formats)) {
                    $row['delivery_methods'] = $this->get_clip_delivery_format($delivery_methods, $delivery_formats, $row);
                } else {
                    $pricing_cat = $row['pricing_category'];
                    if (!empty($pricing_cat)){

                        $this->save_clip_delivery_format($row['id'], $row['license'], $row['brand'], $pricing_cat);

                        $query = $this->db->query('
                        SELECT do.id, do.description, do.delivery, do.resolution, pf.factor price_factor FROM lib_rf_delivery_options do
                        INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                        LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                        ORDER BY do.display_order');

                        $delivery_formats = $query->result_array();

                        if (count($delivery_formats)) {
                            $row['delivery_methods'] = $this->get_clip_delivery_format($delivery_methods, $delivery_formats, $row);
                        }
                    } else {
                        $description_parts = array();
                        $description_parts = $this->_delivery_methods($row);
                        if ($description_parts) {
                            $format['description'] = $description_parts[0]; //implode(' ', $description_parts);
                        }

                        if (isset($format['description']))
                            $rf_delivery_methods[0]['formats'][] = $format;
                        $row['delivery_methods'] = $rf_delivery_methods;
                    }
                }
            } //RM clips
            else {
                    $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
                    FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');

                $delivery_formats = $query->result_array();
                if (count($delivery_formats)) {
                    $row['delivery_methods'] = $this->get_clip_delivery_format($delivery_methods, $delivery_formats, $row);
                } else {
                    $pricing_cat = $row['pricing_category'];

                    if (!empty($pricing_cat)){

                        $this->save_clip_delivery_format($row['id'], $row['license'], $row['brand'], $pricing_cat);

                        $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
                    FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$row['id'] . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');

                        $delivery_formats = $query->result_array();

                        if (count($delivery_formats)) {
                            $row['delivery_methods'] = $this->get_clip_delivery_format($delivery_methods, $delivery_formats, $row);
                        }
                    } else {
                        $description_parts = array();
                        $description_parts = $this->_delivery_methods($row);
                        if ($description_parts) {
                            $format['description'] = implode(' ', $description_parts);
                        }
                        if (isset($format['description']))
                            $rf_delivery_methods[0]['formats'][] = $format;
                        $row['delivery_methods'] = $rf_delivery_methods;
                    }
                }
            }
        }

        return $rows;
    }

    public function insert_with_validation_to_lib_clips_delivery_formats($clip_id, $format_id = null, $license = null)
    {
        $insert_data = ['clip_id' => $clip_id];
        if($format_id) {
            $insert_data['format_id'] = $format_id;
        }
        if($license) {
            $insert_data['license'] = $license;
        }

        try{
            $this->db_master->trans_start();
            $this->db_master->delete('lib_clips_delivery_formats', $insert_data);
            $this->db_master->insert('lib_clips_delivery_formats', $insert_data);
        } catch (\Exception $e){
            error_log($e->getMessage(), 0);
        } finally {
            $this->db_master->trans_complete();
        }
    }

    private function save_clip_delivery_format($id, $license, $brand, $pricing_cat){
        if ($license == 1) {
            $delivery_formats = $this->db->query('SELECT id, categories FROM lib_rf_delivery_options')->result_array();
            foreach ($delivery_formats as $format) {
                if ($format['categories']) {
                    $categories = explode(' ', $format['categories']);
                    if (in_array($pricing_cat, $categories)) {
                        $this->insert_with_validation_to_lib_clips_delivery_formats($id, $format['id'], $license);
                        //$this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $id, 'format_id' => $format['id'], 'license' => $license));
                    }
                }
            }
        } else {
            $delivery_formats = $this->db->query('SELECT id, categories, collection FROM lib_delivery_options')->result_array();
            foreach ($delivery_formats as $format) {
                if ($format['categories']) {
                    $categories = explode(' ', $format['categories']);
                    if (in_array($pricing_cat, $categories) && $brand == $format['collection']) {
                        $this->insert_with_validation_to_lib_clips_delivery_formats($id, $format['id']);
                        //$this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $id, 'format_id' => $format['id']));
                    }
                }
            }
        }
    }

    private function get_clip_delivery_format($delivery_methods, $delivery_formats, $row){
        foreach ($delivery_methods as $key => $method) {
            foreach ($delivery_formats as $format) {
                $format['first_description'] = $format['description'];
                if (true || $format['delivery'] == $method['delivery']) {
                    if (!isset($delivery_methods[$key]['formats'])) {
                        $delivery_methods[$key]['formats'] = array();
                    }
                    if (strpos(strtolower($format['description']), 'custom frame rate') === false) {

                        if (strpos(strtolower($format['description']), 'digital file') !== false) {
                            $format['default'] = 1;
                        }

                        $description_parts = array();
                        $description_parts = $this->_delivery_methods($row, $format);

                        if ($description_parts) {
                            $format['description'] = implode(' ', $description_parts);
                        }

                    } else {
                        if (!isset($custom_frame_rates)) {
                            $custom_frame_rates = array();
                            if (!isset($custom_frame_rates[$format['destination']])) {
                                $this->db->where('media', $format['destination']);
                                $custom_frame_rates[$format['destination']] = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
                            }
                        }
                        $format['custom_frame_rates'] = $custom_frame_rates[$format['destination']];
                    }
                    if (!trim($format['description']))
                        $format['description'] = $format['first_description'];
                    $delivery_methods[$key]['formats'][] = $format;
                }
            }
        }
        foreach ($delivery_methods as $key => $method) {
            if (!isset($method['formats'])) {
                unset($delivery_methods[$key]);
            }
        }
        return $delivery_methods;
    }

    private
    function _delivery_methods($row, $format = NULL)
    {
        $description_parts = array();
        if (isset($format)){
            switch ($format['delivery']) {
                case 'Transcoded':
                    if (strpos(strtolower($format['description']), 'digital file') !== false || empty($format['description'])) {
                        $description_parts = array($row['digital_file_format']);
                    } else {
                        $description_parts = array($format['description']);
                    }
                    $description_parts[] = $format['resolution'] ? '(' . $format['resolution'] . ')' : $row['digital_file_frame_size'];
                    $description_parts[] = $row['digital_file_frame_rate'];
                    break;
                case 'Lab':
                    if (strpos(strtolower($format['description']), 'master file') !== false || empty($format['description'])) {
                        $description_parts = array($row['master_format']);
                    } else {
                        $description_parts = array($format['description']);
                    }
                    $description_parts[] = $row['master_frame_size'];
                    $description_parts[] = $row['master_frame_rate'];
                    break;
                case 'Upload Submission File':
                    if (strpos(strtolower($format['description']), 'digital file') !== false || empty($format['description'])) {
                        $description_parts = array($row['digital_file_format']);
                    } else {
                        $description_parts = array($format['description']);
                    }
                    $description_parts[] = $row['digital_file_frame_size'];
                    $description_parts[] = $row['digital_file_frame_rate'];
                    break;
                case 'Upload Master File':
                    if (strpos(strtolower($format['description']), 'master file') !== false || empty($format['description'])) {
                        $description_parts = array($row['master_format']);
                    } else {
                        $description_parts = array($format['description']);
                    }
                    $description_parts[] = $row['master_frame_size'];
                    $description_parts[] = $row['master_frame_rate'];
                    break;
                default :
                    $description_parts[] = $row['digital_file_frame_size'];
                    $description_parts[] = $row['digital_file_frame_rate'];
                    if (empty($description_parts)) {
                        $description_parts[] = $row['master_frame_size'];
                        $description_parts[] = $row['master_frame_rate'];
                    }
                    if (empty($description_parts))
                        $description_parts[] = $row['digital_file_format'];
                    $description_parts = array_filter($description_parts, function ($el) {
                        return !empty($el);
                    });
                    $description_parts = array_unique($description_parts);
                    break;
            }
        } else {
            $description_parts[] = $row['digital_file_frame_size'];
            $description_parts[] = $row['digital_file_frame_rate'];
            if (empty($description_parts)) {
                $description_parts[] = $row['master_frame_size'];
                $description_parts[] = $row['master_frame_rate'];
            }
            if (empty($description_parts))
                $description_parts[] = $row['digital_file_format'];
            $description_parts = array_filter($description_parts, function ($el) {
                return !empty($el);
            });
            $description_parts = array_unique($description_parts);
        }

        return $description_parts;
    }

    public function get_clipbin_clips($ids, $sort = array(), $lang = 'en', $offset = false, $perpage = 20)
    {
        $rows = array();
        $filter = '';
        //$filter[] = 'c.client_id = ' . (int)$provider;
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if ($ids) {
            $filter[] = ' c.id IN (' . implode(',', $ids) . ') AND c.active=1';
        }
        if ($filter) {
            $filter = ' WHERE ' . implode(' AND ', $filter);
        }
        $sort_str = '';
        if ($sort) {
            $priceLevelKey = array_search('price_level', $sort);
            if ($priceLevelKey !== false) {
                array_unshift($sort, 'license');
            }
            $sort_str = ' ORDER BY c.' . implode(', c.', $sort) . ' ';
        }
        if ($offset !== false) {
            $sort_str .= ' LIMIT ' . $offset . ',' . $perpage . ' ';
        }

        $query = $this->db->query('
            SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id ' . $filter . $sort_str);
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

    function get_clip_add_collections($clip_id)
    {
        // $query = $this->db->query('SELECT lc.* FROM lib_collections lc
        //             INNER JOIN lib_clips_collections lcac ON lc.id = lcac.collection_id AND lcac.clip_id = ?', $clip_id);
        // $result = $query->result_array();
        // return $result;
    }

//    function get_clip_license_types($clip_id){
//        $query = $this->db->query('SELECT ll.* FROM lib_licensing ll
//                    INNER JOIN lib_clip_license_types lclt ON ll.id = lclt.license_id AND lclt.clip_id = ?', $clip_id);
//        $result = $query->result_array();
//        return $result;
//    }

    function get_clip_keywords($clip_id)
    {
        $query = $this->db->query('SELECT lk.* FROM lib_keywords lk
                    INNER JOIN lib_clip_keywords lck ON lk.id = lck.keyword_id AND lck.clip_id = ?', $clip_id);
        $result = $query->result_array();
        return $result;
    }

    function get_Clips_keywords($clip_id)
    {
        $query = $this->db->query('SELECT * FROM lib_clips_keywords WHERE clip_id = ' . $clip_id . '');
        $result = $query->result_array();
        return $result;
    }

    function get_clips_count_by_submission($submission_id)
    {
        $this->db->where('submission_id', $submission_id);
        $query = $this->db->get('lib_clips');
        return $query->num_rows();
    }

    function get_submission_last_clip_code($submission_id)
    {
        $this->db->select('code');
        $this->db->limit(1);
        $this->db->order_by('id', 'DESC');
        $this->db->where('submission_id', $submission_id);
        $query = $this->db->get('lib_clips');
        $result = $query->result_array();
        return $result ? $result[0]['code'] : false;
    }

    function update_download_statistic($id, $provider_id, $remote_addr)
    {
        $query = $this->db->get_where('lib_preview_downloads_statistic', array('clip_id' => $id, 'provider_id' => $provider_id, 'remote_addr' => $remote_addr), 1);
        $res = $query->result_array();
        if ($res) {
            $this->db_master->set('count', 'count + 1', FALSE);
            $this->db_master->where('id', $res[0]['id']);
            $this->db_master->update('lib_preview_downloads_statistic');
        } else {
            $data = array(
                'clip_id' => $id,
                'provider_id' => $provider_id,
                'remote_addr' => $remote_addr,
                'count' => 1
            );
            $this->db_master->insert('lib_preview_downloads_statistic', $data);
        }
    }

    function get_downloads_count($provider_id = 0)
    {
        $this->db->select('clip_id');
        if ($provider_id)
            $this->db->where('provider_id', (int)$provider_id);
        $this->db->get('lib_preview_downloads_statistic');
        return $this->db->count_all_results();
    }

    function GetStatisticItemsCount($filter)
    {
        $result = $this->db->query("SELECT COUNT( id ) AS 'count' FROM lib_clips_extra_statistic AS stat {$filter}");
        $result = (is_object($result)) ? $result->row_array() : array();
        return (isset($result['count'])) ? $result['count'] : 0;
    }

    function GetStatisticTopItemsCount($filter)
    {
        $result = $this->db->query("
			SELECT
				DISTINCT( clip_id ) AS cid,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 1 AND cid = clip_id ) AS type_1,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 2 AND cid = clip_id ) AS type_2,
				( SELECT COUNT( id ) FROM lib_clips_extra_statistic WHERE action_type = 3 AND cid = clip_id ) AS type_3
			FROM
				lib_clips_extra_statistic
			{$filter}"
        );
        return (is_object($result)) ? $result->num_rows() : 0;
    }

    function GetStatisticItems($filter, $limit, $order = false)
    {
        $order = (!$order) ? 'id DESC' : $order;
        $result = $this->db->query("
				SELECT
					stat.*,
					DATE_FORMAT( stat.time, '%d.%m.%Y - %H:%i' ) AS 'date',
					actions.name AS 'action',
					c.code AS clip_code
				FROM
					lib_clips_extra_statistic AS stat
				JOIN
					lib_extra_statistic_actions AS actions
					ON
						actions.type = stat.action_type AND
						actions.lang = 'en'
			    JOIN lib_clips as c ON c.id=stat.clip_id
				{$filter}
				ORDER BY {$order}
				{$limit}"
        );

        return (is_object($result)) ? $result->result_array() : array();
    }

    function GetStatisticTopItems($filter, $order, $limit)
    {
        $result = $this->db->query("
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
        return (is_object($result)) ? $result->result_array() : array();
    }

    function GetRawStatistics($filter, $limit = '', $order = '')
    {
        $result = $this->db->query("SELECT DISTINCT stat.*,c.code FROM lib_clips_extra_statistic AS stat JOIN lib_clips as c ON c.id=stat.clip_id {$filter} {$order} {$limit}");
        return (is_object($result)) ? $result->result_array() : array();
    }

    function GetAllAdminStatistics($filter, $limit = '', $order = '')
    {
        $table = 'lib_clips_download_statistic';
        if (empty($_REQUEST['period']) || $_REQUEST['pediod'] == 'today' || $_REQUEST['pediod'] == 'week' || $_REQUEST['pediod'] == 'month')
            $table = 'lib_clips_extra_statistic';
        $user = $this->uri->segment(6);
        if (empty($filter)) {
            $filter = ' WHERE stat.action_type=2 ';
        } else {
            $filter .= ' AND stat.action_type=2 ';
        }
        if ($this->uri->segment(5) == 'user' && !empty($user)) {
            $filter .= ' AND user_login="' . $user . '"';
            $result = $this->db->query("SELECT stat.*,c.code FROM {$table} AS stat JOIN lib_clips as c ON c.id=stat.clip_id {$filter} {$order} {$limit}");
        } else {
            $result = $this->db->query("SELECT stat.*, count(stat.user_login) as count FROM {$table} AS stat {$filter} GROUP BY stat.user_login {$order} {$limit}");
        }
        return (is_object($result)) ? $result->result_array() : array();
    }

    function GetAllCPStatistics($filter, $limit = '', $order = '', $fields = '')
    {
        if (empty($filter)) {
            $filter = ' WHERE stat.client_id=' . $this->session->userdata('uid') . ' AND (stat.viewed !=0 OR stat.downloaded !=0)';
        } else {
            $filter .= ' AND stat.client_id=' . $this->session->userdata('uid') . ' AND (stat.viewed !=0 OR stat.downloaded !=0)';
        }
        //Debug::Dump("SELECT * {$fields} FROM lib_clips AS stat {$filter} {$order} {$limit}");
        $result = $this->db->query("SELECT * {$fields} FROM lib_clips AS stat {$filter} {$order} {$limit}");
        return (is_object($result)) ? $result->result_array() : array();
    }

    function GetSessionStatisticFilterName()
    {
        $clip_id = (int)$this->id;
        return ($clip_id) ? "clip-{$clip_id}-statistic-user" : "clip-statistic-user";
    }

    function SendCommentToProvider($provider_id, $user_login, $message, $clip_id)
    {
        $this->load->helper('Emailer');
        $clip = $this->get_clip($clip_id);
        $emailer = Emailer::In();
        $emailer->LoadTemplate('toprovider-clip-comment');
        $emailer->TakeSenderSystem();
        $emailer->TakeRecipientAdmin();
        //$emailer->TakeRecipientFromId( $provider_id );
        $emailer->SetTemplateValue('system', 'message', base64_decode($message));
        $emailer->SetTemplateValue('system', 'user', $user_login);
        $emailer->SetTemplateValue('clip', 'id', $clip['id']);
        $emailer->SetTemplateValue('clip', 'code', $clip['code']);
        $emailer->SetTemplateValue('clip', 'title', $clip['title']);
        $emailer->Send();
        $emailer->Clear();
    }

    function SaveCommentToLog($provider_id, $user_login, $mesaage, $clip_id)
    {
        $data = array(
            'provider_id' => $provider_id,
            'user_login' => $user_login,
            'clip_id' => $clip_id,
            'message' => base64_decode($mesaage)
        );
        $this->db_master->insert('lib_clips_comments', $data);
    }

    function getPrevClipsIds($fromClipId, $count, $userId = null)
    {
        $resultArray = array();
        $this->db->select('id');
        $this->db->where('id <', $fromClipId);
        if (!empty($userId))
            $this->db->where('client_id', $userId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($count);
        $result = $this->db->get('lib_clips');
        if ($result) {
            $resultArray = $result->result_array();
            asort($resultArray);
        }
        return $resultArray;
    }

    function getNextClipsIds($fromClipId, $count, $userId = 0)
    {
        $resultArray = array();
        $this->db->select('id');
        $this->db->where('id >', $fromClipId);
        if (!empty($userId))
            $this->db->where('client_id', $userId);
        $this->db->order_by('id', 'ASC');
        $this->db->limit($count);
        $result = $this->db->get('lib_clips');
        if ($result) {
            $resultArray = $result->result_array();
            asort($resultArray);
        }
        return $resultArray;
    }

    function exists($clip_id)
    {
        $this->db->where('id', $clip_id);
        $res = $this->db->get('lib_clips');
        return (bool)$res->num_rows();
    }

    function is_r3d($id)
    {
        $this->db->select('lib_clips.id');
        $this->db->join('lib_clips_res', 'lib_clips_res.clip_id = lib_clips.id', 'inner');
        $this->db->where('lib_clips.id', $id);
        $this->db->where('lib_clips_res.type', '2');
        $this->db->like('lib_clips_res.location', 'R3D', 'before');
        $res = $this->db->get('lib_clips');
        return (bool)$res->num_rows();
    }

    function provider_cloud_tags($client_id = 0)
    {
        $query = $this->db->query("
            SELECT lc.id, lck.keyword, lck.id, count(lck.id) AS count
			FROM lib_clips lc
			INNER JOIN lib_clips_keywords lck ON lc.id=lck.clip_id AND lck.section_id = 'primary_subject'
			WHERE lc.client_id={$client_id} GROUP BY lck.keyword ORDER BY lck.keyword ASC, count DESC"
        );

        $res = $query->result_array();
        return $res;
    }

    function parseMetadata($metadata)
    {
        $data = array();
        if ($metadata) {
            $metadata = json_decode($metadata, true);
            //        $metadata = '{"streams":[{"index":0,"codec_name":"prores","codec_long_name":"ProRes","codec_type":"video","codec_time_base":"1\/25","codec_tag_string":"apch","codec_tag":"0x68637061","width":1920,"height":1080,"has_b_frames":0,"sample_aspect_ratio":"1:1","display_aspect_ratio":"16:9","pix_fmt":"yuv422p10le","level":-99,"r_frame_rate":"25\/1","avg_frame_rate":"25\/1","time_base":"1\/25","start_pts":0,"start_time":"0.000000","duration_ts":397,"duration":"15.880000","bit_rate":"177946148","nb_frames":"397","disposition":{"default":1,"dub":0,"original":0,"comment":0,"lyrics":0,"karaoke":0,"forced":0,"hearing_impaired":0,"visual_impaired":0,"clean_effects":0,"attached_pic":0},"tags":{"creation_time":"2014-08-21 12:17:49","language":"und","handler_name":"Core Media Data Handler","encoder":"Apple ProRes 422 HQ","timecode":"12:36:24:23"}},{"index":1,"codec_type":"data","codec_time_base":"1\/25","codec_tag_string":"tmcd","codec_tag":"0x64636d74","r_frame_rate":"0\/0","avg_frame_rate":"0\/0","time_base":"1\/600","start_pts":0,"start_time":"0.000000","duration_ts":9528,"duration":"15.880000","bit_rate":"2","nb_frames":"1","disposition":{"default":1,"dub":0,"original":0,"comment":0,"lyrics":0,"karaoke":0,"forced":0,"hearing_impaired":0,"visual_impaired":0,"clean_effects":0,"attached_pic":0},"tags":{"creation_time":"2014-08-21 12:17:49","language":"und","handler_name":"Core Media Data Handler","timecode":"12:36:24:23"}}],"format":{"filename":"\/storage\/FSM12\/DA140831\/DA140831_0107.mov","nb_streams":2,"nb_programs":0,"format_name":"mov,mp4,m4a,3gp,3g2,mj2","format_long_name":"QuickTime \/ MOV","start_time":"0.000000","duration":"15.880000","size":"353227674","bit_rate":"177948450","probe_score":100,"tags":{"major_brand":"qt  ","minor_version":"0","compatible_brands":"qt  ","creation_time":"2014-08-21 12:17:49"}}}';
            //        $metadata = json_decode($metadata, true);
            //        $metadata = "Clip Name,File Path,File Name,ReelID,AltReelID,CamReelID,Camera,Camera Model,Camera Model ID,Camera Network Name,Camera PIN,Dropped Frame Count,Media Serial Number,Sensor Name,Sensor ID,Reel,Clip,Date,Timestamp,Frame Width,Frame Height,FPS,Record FPS,Total Frames,Abs TC,Edge TC,End Abs TC,End Edge TC,Color Space,Gamma Space,Kelvin,Tint,ISO,Exposure,Saturation,Contrast,Brightness,Red Gain,Green Gain,Blue Gain,Luma Curve: Black X,Luma Curve: Black Y,Luma Curve: Toe X,Luma Curve: Toe Y,Luma Curve: Mid X,Luma Curve: Mid Y,Luma Curve: Knee X,Luma Curve: Knee Y,Luma Curve: White X,Luma Curve: White Y,Shutter (ms),Shutter (1/sec),Shutter (deg),Firmware Version,Firmware Revision,Camera Audio Channels,File Segments,Flip Horizontal,Flip Vertical,Rotation,HDR Mode,HDR Stops Over,HDR Blend Mode,HDR Blend Bias,Aperture,Focal Length,Focus Distance,Lens Mount,Lens,Clip In,Clip Out,Notes,Owner,Project,Scene,Shot,Take,Unit,Production Name,Location,Director Of Photography,Director,Camera Operator,Circle,Copyright,Script Notes,Camera Notes,Edit Notes,Post Notes,Lens,Lens Height,Aperture,Focal Length,Focal Distance,Filter,Scene Description,Audio Timecode,Audio,Audio Slate,Video Slate,Frame Guide X,Frame Guide Y,Frame Guide Width,Frame Guide Height,Aspect Ratio,Aspect Ratio Numerator,Aspect Ratio Denominator,Frame Guide Name,Genlock Setting,Jamsync Setting,Linked Camera Setup,Pixel Aspect Ratio,Stereo Setup,REDCODE,Look Name,DRX,FLUT,Shadow,LGG: Lift: Red,LGG Lift: Green,LGG Lift: Blue,LGG Gamma: Red,LGG Gamma: Green,LGG Gamma: Blue,LGG Gain: Red,LGG Gain: Green,LGG Gain: Blue,Red Curve: Black X,Red Curve: Black Y,Red Curve: Toe X,Red Curve: Toe Y,Red Curve: Mid X,Red Curve: Mid Y,Red Curve: Knee X,Red Curve: Knee Y,Red Curve: White X,Red Curve: White Y,Green Curve: Black X,Green Curve: Black Y,Green Curve: Toe X,Green Curve: Toe Y,Green Curve: Mid X,Green Curve: Mid Y,Green Curve: Knee X,Green Curve: Knee Y,Green Curve: White X,Green Curve: White Y,Blue Curve: Black X,Blue Curve: Black Y,Blue Curve: Toe X,Blue Curve: Toe Y,Blue Curve: Mid X,Blue Curve: Mid Y,Blue Curve: Knee X,Blue Curve: Knee Y,Blue Curve: White X,Blue Curve: White Y,Detail,OLPF Compensation,Denoise
            //A031_C041_0128OB,Z:/home/ivan/video/A031_C041_0128OB.RDC/A031_C041_0128OB_001.R3D,A031_C041_0128OB_001.R3D,031,A031_C041_0128OB,A031,A,EPIC-X,5,EPIC,102-968-4EB,0,203686490,MYSTERIUM-X S35,1,031,041,20130128,043436,4800,2700,23.976,95,904,04:34:36:01,01:48:54:14,04:35:13:16,01:49:32:05,18,29,5500,-1.943,800,0,1.9,-0.1,0,1,1,1,0.00000,0.00000,0.25000,0.25000,0.50000,0.50000,0.75000,0.75000,1.00000,1.00000,1,2000,17.1,3.3.14,50353,0,1,,,0.000000,No HDR,,Off,Off,F8,400mm,342985mm,Canon,,0,903,,,,GH,,366,,,,,,,,,,,,,,,F8,400mm,,,,,,,0,0,0,1,1,1.78,16,9,,Not Genlocked,Internal Clock,Not Linked,1,Not Stereo,REDcode 10:1,,0,-0.3,0,0,0,0,1,1,1,1,1,1,0.00000,0.00000,0.25000,0.25000,0.50000,0.50000,0.75000,0.75000,1.00000,1.00000,0.00000,0.00000,0.25000,0.25000,0.50000,0.50000,0.75000,0.75000,1.00000,1.00000,0.00000,0.00000,0.25000,0.25000,0.50000,0.50000,0.75000,0.75000,1.00000,1.00000,High,Off,Off";
            //        $arr = explode("\n", $metadata);
            //        $metadata = array_combine(str_getcsv($arr[0], ','), str_getcsv($arr[1], ','));
            // FFprobe
            if (isset($metadata['streams'])) {
                $videoStream = -1;
                foreach ($metadata['streams'] as $key => $stream) {
                    if (isset($stream['codec_type']) && $stream['codec_type'] == 'video') {
                        $videoStream = $key;
                    }
                }
                if ($videoStream != -1) {
                    $data['video_codec'] = $metadata['streams'][$videoStream]['codec_long_name'];
                    if (isset($metadata['streams'][$videoStream]['tags']) && isset($metadata['streams'][$videoStream]['tags']['encoder'])) {
                        $data['video_encoder'] = $metadata['streams'][$videoStream]['tags']['encoder'];
                    }
                    $data['width'] = $metadata['streams'][$videoStream]['width'];
                    $data['height'] = $metadata['streams'][$videoStream]['height'];
                    $data['aspect_ratio'] = $metadata['streams'][$videoStream]['display_aspect_ratio'];
                    $data['pixel_format'] = $metadata['streams'][$videoStream]['pix_fmt'];
                    //$data['frame_rate'] = explode('/', $metadata['streams'][$videoStream]['r_frame_rate'])[0] . ' fps';
                    list($numerator, $denominator) = explode('/', $metadata['streams'][$videoStream]['r_frame_rate']);
                    $numerator = intval($numerator);
                    $denominator = intval($denominator);
                    if ($numerator && $denominator) {
                        $data['frame_rate'] = round($numerator / $denominator,2) . ' fps';
//                        $frame_rate = number_format($numerator / $denominator, 2, '.', '');
//                        if (substr($frame_rate, -3) == '.00') {
//                            $data['frame_rate'] = substr($frame_rate, 0, -3) . ' fps';
//                        }
                    }

                    if (isset($metadata['streams'][$videoStream]['tags']) && isset($metadata['streams'][$videoStream]['tags']['timecode'])) {
                        $data['timecode'] = $metadata['streams'][$videoStream]['tags']['timecode'];
                    }
                }
                $data['filename_location'] = $metadata['format']['filename'];
                $data['file_wrapper'] = $metadata['format']['format_long_name'];
                $data['file_size_(MB)'] = (round((int)$metadata['format']['size'] / (1024 * 1024), 2)) . '';
                if (!empty($metadata['streams'][0]['bit_rate'])) {
                    $data['data_rate_(Mbps)'] = (round((int)$metadata['streams'][0]['bit_rate']/1024/1024, 2)) . '';
                }

            } // Redline
            else {
                $keysToImport = array(
                    'Camera Model',
                    'Sensor Name',
                    'Date',
                    'Timestamp',
                    'Frame Width',
                    'Frame Height',
                    'FPS',
                    'Record FPS',
                    'Total Frames',
                    'Abs TC',
                    'Edge TC',
                    'End Abs TC',
                    'End Edge TC',
                    'Color Space',
                    'Gamma Space',
                    'Kelvin',
                    'Tint',
                    'ISO',
                    'Shutter (1/sec)',
                    'Aperture',
                    'Focal Length',
                    'Focus Distance',
                    'Lens Mount',
                    'Aspect Ratio',
                    'Aspect Ratio Numerator',
                    'Aspect Ratio Denominator',
                    'REDCODE',
                    'Denoise',
                );
                foreach ($keysToImport as $key) {
                    if ($metadata[$key] == 'FPS') {
                        $data[$key] = round($metadata[$key],2);
                    }
                    if (isset($metadata[$key])) {
                        $data[$key] = $metadata[$key];
                    }
                }
            }
        }
        return $data;
    }

    function is_clip_owner($uid, $clipid)
    {

        $result = $this->db->query("SELECT client_id FROM lib_clips WHERE id = '" . $clipid . "' LIMIT 1")->result_array();

        if ($result[0]['client_id'] == $uid) {
            return true;
        } else {
            return false;
        }
    }

    function get_next_clip($uid, $clipid, $searchArr = null)
    {
        if (empty($searchArr)) {
            $this->load->model('users_model');

            $user_data = $this->users_model->get_user($this->session->userdata('uid'));

            if ($user_data['group_id'] == 1) {
                $result = $this->db->query("SELECT id, code FROM lib_clips WHERE id > '" . $clipid . "' LIMIT 1")->result_array();
            } else {
                $result = $this->db->query("SELECT id, code FROM lib_clips WHERE client_id = '" . $uid . "' AND id > '" . $clipid . "' LIMIT 1")->result_array();
            }
        } else {
            $max = count($searchArr) - 1;
            $cur = array_search($clipid, $searchArr);
            if ($cur == $max)
                return false;
            $result = $this->db->query("SELECT id, code FROM lib_clips WHERE id = '" . $searchArr[$cur + 1] . "' LIMIT 1")->result_array();
        }
        return $result;
    }

    function get_previous_clip($uid, $clipid, $searchArr = null)
    {
        if (empty($searchArr)) {
            $this->load->model('users_model');

            $user_data = $this->users_model->get_user($this->session->userdata('uid'));

            if ($user_data['group_id'] == 1) {
                $result = $this->db->query("SELECT id, code FROM lib_clips WHERE id < '" . $clipid . "' ORDER BY id DESC LIMIT 1")->result_array();
            } else {
                $result = $this->db->query("SELECT id, code FROM lib_clips WHERE client_id = '" . $uid . "' AND id < '" . $clipid . "' ORDER BY id DESC LIMIT 1")->result_array();
            }
        } else {
            $min = 0;
            $cur = array_search($clipid, $searchArr);
            if ($cur == $min)
                return false;
            $result = $this->db->query("SELECT id, code FROM lib_clips WHERE id = '" . $searchArr[$cur - 1] . "' LIMIT 1")->result_array();
        }
        return $result;
    }

    function get_lab_clips($order_id)
    {
        $clips = $this->db->query("SELECT item_id FROM lib_orders_items WHERE delivery_process = 'Manual' ")->result_array();
        return $clips;
    }

    function isOwner($clip_id, $uid)
    {
        $ownerId = $this->db->query("SELECT client_id FROM lib_clips WHERE id =" . (int)$clip_id . " LIMIT 1")->result_array();
        return ($ownerId[0]['client_id'] == $uid || $uid == 0) ? true : false;
    }

    function clipClient($clip_id)
    {
        $ownerId = $this->db->query("SELECT client_id FROM lib_clips WHERE id =" . (int)$clip_id . " LIMIT 1")->result_array();
        return $ownerId[0]['client_id'];
    }

    function getActions_types()
    {
        if (!empty($_SESSION['actions_types'])) {
            return $_SESSION['actions_types'];
        } else {
            $not = 4;
            if (isset($_SESSION['uid']) && $_SESSION['group'] != 1)
                $not .= ',3';
            $_SESSION['actions_types'] = $this->db->query("SELECT * FROM lib_extra_statistic_actions WHERE type NOT IN (" . $not . ")")->result_array();
            return $_SESSION['actions_types'];
        }
    }

    public function getCarouselClipsIds($selectedClipId, $userId, $filters = false, $offset = 0, $limit = 50)
    {

        $carouselClipsIds = array();

        if (isset($filters['backend_clipbin_id'])) {
            $clips = $this->get_clipIds_by_BackclipbinId($filters['backend_clipbin_id'], $offset, $limit);
        } else {


//Getting The Filters
            unset($filters['backend_clipbin_id']);
//unset($filters['submission_id']);

            if (!$this->group['is_admin']) {
                $filters['client_id'] = $userId;
            }
            if (!empty($filters['wordsin']))
                $filters['words'] = $filters['words'] . ' ' . $filters['wordsin'];
            if ($filters['brand'] != NULL)
                $filters['brand_id'] = $filters['brand'];
            if ($filters['collection_id'] != NULL)
                $filters['collection'] = $filters['collection_id'];
            if ($_SESSION['cliplog_search_filter_words'])
                $filters['words'] = (empty($filters['words'])) ? $_SESSION['cliplog_search_filter_words'] : $filters['words'];
            if (empty($filters))
                $filters['all'] = 'all';

//NEED TO REVERT......IMR@N
            $clipsIds = $this->getClipIdWithoutSolr($filters, $offset, $limit, array(), '', $this->group['is_admin'], '', $selectedClipId);
        }
        return $clipsIds;
    }


    public function getClipIdWithoutSolr($filter, $offset = 0, $limit = 100, $sort = array(), $facet = array(), $is_admin = false, $lang = 'en', $selectedClipId = NULL, $perpage = 10)
    {


        $client_id = $filter['client_id'];
        $filter = array();
        ////Added Imran Filters
        $filter['client_id'] = $client_id;
        $filter['backend_clipbin_id'] = $this->session->userdata('backend_clipbin_id');
        if ($this->session->userdata('submissionId') != 0) {
            $filter['submission_id'] = $this->session->userdata('submissionId');
        }
        if (!empty($filter['backend_clipbin_id']) || !empty($filter['submission_id'])) {
            // $limit = $this->get_clips_limit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                //$filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
                // $filter = $this->renameFilter($filter, 'brand', 'brand_id');
                // $filter = $this->renameFilter($filter, 'collection', 'collection_id');
                if (!empty($this->session->userdata('cliplog_search_collection'))) {
                    $filter['collection_filter'] = $this->session->userdata('cliplog_search_collection');
                }
            if (!empty($this->session->userdata('cliplog_search_brand'))) {
                $filter['brand_filter'] = $this->session->userdata('cliplog_search_brand');
            }
            if (!empty($this->session->userdata('cliplog_search_license'))) {
                $filter['license_filter'] = $this->session->userdata('cliplog_search_license');
            }
            if (!empty($this->session->userdata('cliplog_search_price_level'))) {
                $filter['price_level_filter'] = $this->session->userdata('cliplog_search_price_level');
            }
            if (!empty($this->session->userdata('cliplog_search_format_category'))) {
                $filter['filter_format'] = $this->session->userdata('cliplog_search_format_category');
            }
            if (!empty($this->session->userdata('cliplog_search_active'))) {
                $filter['filter_active'] = $this->session->userdata('cliplog_search_active');
            }
            if (!empty($this->session->userdata('cliplog_search_wordsin'))) {
                $filter['search_in'] = $this->session->userdata('cliplog_search_wordsin');
            }
            if (!empty($this->session->userdata('cliplog_adminAction_filter_name'))) {
                $filter['admin_action_filter'] = $this->session->userdata('cliplog_adminAction_filter_name');
            }
            if (!empty($this->session->userdata('cliplog_duration_filter'))) {
                $filter['duration'] = $this->session->userdata('cliplog_duration_filter');
            }
            if (!empty($this->session->userdata('cliplog_creation_date'))) {
                $filter['creation_date'] = $this->session->userdata('cliplog_creation_date');
            }
            if ($this->session->userdata('searchWordFilter') != '') {
                $filter['words'] = $this->session->userdata('searchWordFilter') . ' ' . $filter['wordsin'];
            }
        } else {

            //////// Changes To Stop SOLR Imran  //////////
            unset($filter['backend_clipbin_id']);
            //$this->session->unset_userdata('backend_clipbin_id');
            // $solrLimit = $this->get_clips_Solrlimit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                $filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
            //$filter = $this->renameFilter($filter, 'brand', 'brand_id');
            //$filter = $this->renameFilter($filter, 'collection', 'collection_id');
            if (!empty($this->session->userdata('cliplog_search_collection'))) {
                $filter['collection_filter'] = $this->session->userdata('cliplog_search_collection');
            }
            if (!empty($this->session->userdata('cliplog_search_brand'))) {
                $filter['brand_filter'] = $this->session->userdata('cliplog_search_brand');
            }
            if (!empty($this->session->userdata('cliplog_search_license'))) {
                $filter['license_filter'] = $this->session->userdata('cliplog_search_license');
            }
            if (!empty($this->session->userdata('cliplog_search_price_level'))) {
                $filter['price_level_filter'] = $this->session->userdata('cliplog_search_price_level');
            }
            if (!empty($this->session->userdata('cliplog_search_format_category'))) {
                $filter['filter_format'] = $this->session->userdata('cliplog_search_format_category');
            }
            if (!empty($this->session->userdata('cliplog_search_active'))) {
                $filter['filter_active'] = $this->session->userdata('cliplog_search_active');
            }
            if (!empty($this->session->userdata('searchInImran'))) {
                $filter['search_in'] = $this->session->userdata('searchInImran');
            }
            if (!empty($this->session->userdata('cliplog_adminAction_filter_name'))) {
                $filter['admin_action_filter'] = $this->session->userdata('cliplog_adminAction_filter_name');
            }
            if (!empty($this->session->userdata('cliplog_duration_filter'))) {
                $filter['duration'] = $this->session->userdata('cliplog_duration_filter');
            }
            if (!empty($this->session->userdata('cliplog_creation_date'))) {
                $filter['creation_date'] = $this->session->userdata('cliplog_creation_date');
            }
            if ($this->session->userdata('searchWordFilter') != '') {
                $filter['words'] = $this->session->userdata('searchWordFilter') . ' ' . $_SESSION['searchInImran'];
            }
        }

        if ($this->group['is_admin']) {
            unset($filter['client_id']);
        }

        $sort_str = " ORDER BY c.code ASC";

        // echo $_SESSION['searchInImran'].'----ssss';
        //  $_SESSION['cliplog_search_wordsin'] && $_SESSION['searchWordFilter']
        //$filter['words'] = $this->session->userdata('searchWordFilter');

        $this->build_filter_sql_backend($filter);


        //$limit = 100;
        $oneLessImran = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $this->filter_sql . $sort_str . ' LIMIT ' . $offset . ',' . $limit)->result_array();

        $i = 1;
        foreach ($oneLessImran as $data) {
            if ($data['id'] == $selectedClipId) {
                break;
            }
            $i++;
        }

        if (($perpage = $this->session->userdata('clipbin-clips-perpage')) && $perpage > 0) {
        } else {
            $perpage = 10;
        }

        $query = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $this->filter_sql . $sort_str . ' LIMIT ' . $offset . ',' . $limit);
        $rows = $query->result_array();

        foreach ($rows as $ikey => $ivalue) {
            $rowsIds[$ikey] = $ivalue['id'];
        }

        return $rowsIds;
    }

    public function getNextCarouselClipIdsOrderedByCode($selectedClipId, $userId = null, $limit = 1){
        // finding next clip code after clip with id of $selectedClipId
        $localFilters = ' WHERE c.code > (SELECT c.code FROM lib_clips c WHERE c.id= ' . $selectedClipId.')';
        $sort_str = ' ORDER BY c.code ASC ';
        if (!$this->group['is_admin'] && $userId) {
            $localFilters .= ' AND c.client_id ='.$userId;
        }
        $query_string = 'SELECT c.id
			FROM lib_clips c'
            //.'LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id'
            . $localFilters . $sort_str . ' LIMIT ' . $limit;
        $rows = $this->db->query($query_string)->result_array();
        $ids = [];
        if(!empty($rows)){
            foreach($rows as $row){
                $ids[] = $row['id'];
            }
        }
        return $ids;
    }


    public function getCarouselClipsIdsStills($filter, $offset = 0, $limit = 100, $sort = array(), $facet = array(), $is_admin = false, $lang = 'en', $selectedClipId = NULL, $perpage = 10)
    {

        if ($offset == '') {
            $offset = 0;
        }


        $client_id = $filter['client_id'];
        $filter = array();
        ////Added Imran Filters
        $filter['client_id'] = $client_id;
        $filter['backend_clipbin_id'] = $this->session->userdata('backend_clipbin_id');
        if ($this->session->userdata('submissionId') != 0) {
            $filter['submission_id'] = $this->session->userdata('submissionId');
        }
        if (!empty($filter['backend_clipbin_id']) || !empty($filter['submission_id'])) {
            // $limit = $this->get_clips_limit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                //$filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
                // $filter = $this->renameFilter($filter, 'brand', 'brand_id');
                // $filter = $this->renameFilter($filter, 'collection', 'collection_id');
                if (!empty($this->session->userdata('cliplog_search_collection'))) {
                    $filter['collection_filter'] = $this->session->userdata('cliplog_search_collection');
                }
            if (!empty($this->session->userdata('cliplog_search_brand'))) {
                $filter['brand_filter'] = $this->session->userdata('cliplog_search_brand');
            }
            if (!empty($this->session->userdata('cliplog_search_license'))) {
                $filter['license_filter'] = $this->session->userdata('cliplog_search_license');
            }
            if (!empty($this->session->userdata('cliplog_search_price_level'))) {
                $filter['price_level_filter'] = $this->session->userdata('cliplog_search_price_level');
            }
            if (!empty($this->session->userdata('cliplog_search_format_category'))) {
                $filter['filter_format'] = $this->session->userdata('cliplog_search_format_category');
            }
            if (!empty($this->session->userdata('cliplog_search_active'))) {
                $filter['filter_active'] = $this->session->userdata('cliplog_search_active');
            }
            if (!empty($this->session->userdata('cliplog_search_wordsin'))) {
                $filter['search_in'] = $this->session->userdata('cliplog_search_wordsin');
            }
            if (!empty($this->session->userdata('cliplog_adminAction_filter_name'))) {
                $filter['admin_action_filter'] = $this->session->userdata('cliplog_adminAction_filter_name');
            }
            if (!empty($this->session->userdata('cliplog_duration_filter'))) {
                $filter['duration'] = $this->session->userdata('cliplog_duration_filter');
            }
            if (!empty($this->session->userdata('cliplog_creation_date'))) {
                $filter['creation_date'] = $this->session->userdata('cliplog_creation_date');
            }
            if ($this->session->userdata('searchWordFilter') != '') {
                $filter['words'] = $this->session->userdata('searchWordFilter') . ' ' . $filter['wordsin'];
            }
        } else {

            //////// Changes To Stop SOLR Imran  //////////
            unset($filter['backend_clipbin_id']);
            //$this->session->unset_userdata('backend_clipbin_id');
            // $solrLimit = $this->get_clips_Solrlimit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                $filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
            //$filter = $this->renameFilter($filter, 'brand', 'brand_id');
            //$filter = $this->renameFilter($filter, 'collection', 'collection_id');
            if (!empty($this->session->userdata('cliplog_search_collection'))) {
                $filter['collection_filter'] = $this->session->userdata('cliplog_search_collection');
            }
            if (!empty($this->session->userdata('cliplog_search_brand'))) {
                $filter['brand_filter'] = $this->session->userdata('cliplog_search_brand');
            }
            if (!empty($this->session->userdata('cliplog_search_license'))) {
                $filter['license_filter'] = $this->session->userdata('cliplog_search_license');
            }
            if (!empty($this->session->userdata('cliplog_search_price_level'))) {
                $filter['price_level_filter'] = $this->session->userdata('cliplog_search_price_level');
            }
            if (!empty($this->session->userdata('cliplog_search_format_category'))) {
                $filter['filter_format'] = $this->session->userdata('cliplog_search_format_category');
            }
            if (!empty($this->session->userdata('cliplog_search_active'))) {
                $filter['filter_active'] = $this->session->userdata('cliplog_search_active');
            }
            if (!empty($this->session->userdata('searchInImran'))) {
                $filter['search_in'] = $this->session->userdata('searchInImran');
            }
            if (!empty($this->session->userdata('cliplog_adminAction_filter_name'))) {
                $filter['admin_action_filter'] = $this->session->userdata('cliplog_adminAction_filter_name');
            }
            if (!empty($this->session->userdata('cliplog_duration_filter'))) {
                $filter['duration'] = $this->session->userdata('cliplog_duration_filter');
            }
            if (!empty($this->session->userdata('cliplog_creation_date'))) {
                $filter['creation_date'] = $this->session->userdata('cliplog_creation_date');
            }
            if ($this->session->userdata('searchWordFilter') != '') {
                $filter['words'] = $this->session->userdata('searchWordFilter') . ' ' . $_SESSION['searchInImran'];
            }
        }

        if ($this->group['is_admin']) {
            unset($filter['client_id']);
        } else {
            $filter['client_id'] = $_SESSION['uid'];
        }

        $sort_str = " AND c.client_id  <> 0 ORDER BY c.code ASC";

        // echo $_SESSION['searchInImran'].'----ssss';
        //  $_SESSION['cliplog_search_wordsin'] && $_SESSION['searchWordFilter']
        //$filter['words'] = $this->session->userdata('searchWordFilter');

        $this->build_filter_sql_backend($filter);

        $limit = 10000;
        $query = $this->db->query('SELECT c.*, c.code as title, rc.weight
			FROM lib_clips c
			LEFT JOIN lib_rank_clips rc ON c.id=rc.clip_id' . $this->filter_sql . $sort_str . ' LIMIT ' . $offset . ',' . $limit);
        $rows = $query->result_array();

        foreach ($rows as $ikey => $ivalue) {
            $rowsIds[$ikey] = $ivalue['id'];
        }

        return $rowsIds;
    }

    public function deleteClipsKeywords($clipIds)
    {

        $idsArray = explode(",", $clipIds);

//        foreach ($idsArray as $value) {
//            $query = $this->db->query("SELECT * FROM lib_clips_keywords WHERE id ='" . $value . "'");
//            $rows = $query->result_array();
//            $delKeywordVal = $rows[0]['keyword'];
//            $query = $this->db_master->query("DELETE FROM lib_clips_keywords WHERE keyword ='" . mysql_real_escape_string($delKeywordVal) . "' AND clip_id='" . $rows[0]['clip_id'] . "' ");
//        }
        $this->db->where_in('id', $idsArray);
        $this->db->delete('lib_clips_keywords');
    }

    public function addClipsKeywords($clipsArr, $uId, $clipId, $userKeywords = NULL, $fromHiddenState = NULL)
    {
//print_r($clipsArr);

        $this->load->model('cliplog_keywords_model');
        $stateManager = new StateManager();
        if (!empty($userKeywords)) {
            foreach ($userKeywords as $getValue) {


                if (!empty($getValue)) {

                    $clipGetId = explode('_', $getValue);
                    if ($this->cliplog_keywords_model->isTemporaryKeyword($getValue)) {
                        $temporaryKeywordData = $stateManager->getKeywordDataFromState($getValue);
                        $keywordId = $this->cliplog_keywords_model->createKeyword($temporaryKeywordData);
                        $clipGetId = $keywordId;

                    } else {
                        $clipGetId = explode('_', $getValue);
                        $clipGetId = $clipGetId[1];
                    }

//     echo 'SELECT keyword FROM lib_keywords WHERE id ='.$ivalue.'';
                    if (!empty($clipGetId[1])) {
                        $query = $this->db->query("SELECT * FROM lib_keywords WHERE id ='" . $clipGetId[1] . "'");
                        $rows = $query->result_array();
// echo $rows[0]['keyword'] . '<br>';

                        // $getChec = $this->checkKeywordsExistsClipKeywords($rows[0]['keyword'], $rows[0]['section_id'], $clipId);

                        //  if ($getChec != 1) {
                        $this->db_master->query('INSERT ignore INTO lib_clips_keywords SET keyword ="' . mysql_real_escape_string($rows[0]['keyword']) . '", section_id="' . $rows[0]['section_id'] . '",clip_id="' . $clipId . '" ');
                    }
                    //  }
// print_r($rows);
                }
            }
        }

        if (!empty($clipsArr)) {

            $ivalues = [];
            foreach ($clipsArr as $ivalue) {
                if (!empty($ivalue)) {
                    if ($this->cliplog_keywords_model->isTemporaryKeyword($ivalue)) {
                        $temporaryKeywordData = $stateManager->getKeywordDataFromState($ivalue);
                        $keywordId = $this->cliplog_keywords_model->createKeyword($temporaryKeywordData);
                        $ivalue = $keywordId;
                    }
                    $ivalues[] = $ivalue;

                }
            }
            if($ivalues){
                $this->db->select('keyword,section');
                $this->db->where_in('id', $ivalues);
                $this->db->from('lib_keywords');
                $keywordResults = $this->db->get();
            }

            if(!empty($keywordResults)){
                $this->db->trans_start();
                foreach($keywordResults->result() as $keywordResult){
                    $insert_query = $this->db->insert_string('lib_clips_keywords', [
                        'keyword' => $keywordResult->keyword,
                        'section_id' => $keywordResult->section,
                        'clip_id' => $clipId
                    ]);
                    $insert_query = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);
                    $this->db->query($insert_query);
                }
                $this->db->trans_complete();
            }

        }

        if (!empty($fromHiddenState)) {
            //sleep(2);
            $fromHiddenState = json_decode($fromHiddenState);
            foreach ($fromHiddenState as $KewrodData) {
                // $getChec = $this->checkKeywordsExistsClipKeywords($KewrodData->keywordText, $KewrodData->keywordSection, $clipId);
                // if ($getChec != 1) {
                $this->db_master->query('INSERT ignore INTO lib_clips_keywords SET keyword ="' . mysql_real_escape_string($KewrodData->keywordText) . '", section_id="' . $KewrodData->keywordSection . '",clip_id="' . $clipId . '" ');
                //  }
            }
        }

        //$this->updateLibClipsClipIds($clipId);
    }

    public function addClipsKeywordsMultiple($keywords, $uId, $clipIds, $userKeywords = NULL, $overwrite = NULL, $fromHiddenState = NULL)
    {

        $this->load->model('cliplog_keywords_model');
        $stateManager = new StateManager();

        $this->db_master->trans_start();

        if (!empty($overwrite)) {
            foreach ($overwrite as $fieldToOverwrite) {
                foreach ($clipIds as $clipId) {
                    if (!is_array($fieldToOverwrite)) {
                        $this->db_master->delete('lib_clips_keywords', array('section_id' => $fieldToOverwrite, 'clip_id' => $clipId));
                    }
                }
            }
        }
        if (!empty($userKeywords)) {
            foreach ($userKeywords as $getValue) {

                if (!empty($getValue)) {

                    if ($this->cliplog_keywords_model->isTemporaryKeyword($getValue)) {
                        $temporaryKeywordData = $stateManager->getKeywordDataFromState($getValue);
                        $keywordId = $this->cliplog_keywords_model->createKeyword($temporaryKeywordData);
                        $clipGetId = $keywordId;

                    } else {
                        $clipGetId = explode('_', $getValue);
                        $clipGetId = $clipGetId[1];
                    }


                    $query = $this->db->query("SELECT * FROM lib_keywords WHERE id ='" . $clipGetId[1] . "' AND provider_id='" . $_SESSION['uid'] . "'");
                    $rows = $query->result_array();

                    if (empty($rows)) {
                        $query = $this->db->query("SELECT * FROM lib_keywords WHERE id ='" . $clipGetId[1] . "' ");
                        $rows = $query->result_array();
                    }

                    foreach ($clipIds as $clipId) {
                        $this->db_master->query('INSERT ignore INTO lib_clips_keywords SET keyword ="' . htmlspecialchars($rows[0]['keyword']) . '", section_id="' . $rows[0]['section_id'] . '",clip_id="' . $clipId . '" ');
                    }
                }
            }
        }

        if (!empty($keywords)) {
            foreach ($keywords as $ivalue) {

                if (!empty($ivalue)) {
                    if ($this->cliplog_keywords_model->isTemporaryKeyword($ivalue)) {
                        $temporaryKeywordData = $stateManager->getKeywordDataFromState($ivalue);
                        $keywordId = $this->cliplog_keywords_model->createKeyword($temporaryKeywordData);
                        $ivalue = $keywordId;
                    }

                    $query = $this->db->query("SELECT keyword,section FROM lib_keywords WHERE id ='" . $ivalue . "'");
                    $rows = $query->result_array();

                    foreach ($clipIds as $clipId) {
                        $this->db_master->query('INSERT INTO lib_clips_keywords (keyword, section_id, clip_id)
                        VALUES ("'. htmlspecialchars($rows[0]['keyword']).'", "'.$rows[0]['section'].'", "'.$clipId.'")
                        ON DUPLICATE KEY UPDATE id=id');
                    }

                }
            }

        }


        if (!empty($fromHiddenState)) {

            $fromHiddenState = json_decode($fromHiddenState);
            foreach ($fromHiddenState as $KewrodData) {

                foreach ($clipIds as $clipId) {
                    $this->db_master->query('INSERT ignore INTO lib_clips_keywords SET keyword ="' . htmlspecialchars($KewrodData->keywordText) . '", section_id="' . $KewrodData->keywordSection . '",clip_id="' . $clipId . '" ');
                }

            }

        }

        $this->db_master->trans_complete();

    }

    public function checkKeywordsExistsClipKeywords($keyword, $section, $clipId)
    {
        $query = $this->db->query('SELECT * FROM lib_clips_keywords WHERE keyword= "' . mysql_real_escape_string($keyword) . '" AND section_id="' . $section . '" AND clip_id ="' . $clipId . '" ');
        $rows = $query->result_array();
        if ($rows) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getAllKeywordsByClipId($clip_id)
    {
        $query = $this->db->query('select * from lib_clips_keywords where clip_id="' . $clip_id . '"');
        $rows = $query->result_array();
        return $rows;
    }

    public function set_check_ip($ip)
    {
        if ($this->get_check_ip($ip)) {
            $ip = preg_replace('/[a-z]/i', '', $ip);
            $this->db_master->query('INSERT INTO checkip (ip) VALUES ("' . $ip . '") ON DUPLICATE KEY UPDATE quantity=quantity+1;');
        }
    }

    public function get_check_ip($ip)
    {
        $ip = preg_replace('/[a-z]/i', '', $ip);
        $ip = $this->db->get_where('checkip', array('ip' => $ip))->result_array();
        if (empty($ip))
            return 'empty';
        return (empty($ip) || $ip[0]['quantity'] < 5) ? $ip[0]['quantity'] : false;
    }

    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function getRatingData($clipID, $clientID)
    {
        $query = $this->db->query("select * from lib_clip_rating where code='" . $clipID . "' and (name='ip_rating' or name='user_rating' or name='admin_rating')");
        $rows = $query->result_array();
        return $rows;
    }

    public function checkIfKeywordExistsInMaster($keyword, $section)
    {
        $keyword = $this->db->escape($keyword);
        $section = $this->db->escape($section);
        $query = $this->db->query("select id from lib_keywords where keyword=" . $keyword . " AND section=" . $section);
        $rows = $query->result_array();
        return $rows;
    }

    function get_rm_pricing_details()
    {

        $result = $this->db->query("SELECT lib_pricing_use.category AS 'Category',
                                lib_pricing_use.use AS 'Use',
                                lib_pricing_terms.territory AS 'Territory',
                                lib_pricing_terms.term AS 'Term',
                                lib_pricing_use.description AS 'Description',
                                lib_pricing_use.exclusions AS 'Exclusions',
                                lib_pricing_use.clip_minimum AS 'Clip Minimum',
                                lib_pricing_use.budgete_rate*lib_pricing_terms.factor*lib_pricing_use.clip_minimum AS 'Budget',
                                lib_pricing_use.standard_rate*lib_pricing_terms.factor*lib_pricing_use.clip_minimum AS 'Standard',
                                lib_pricing_use.premium_rate*lib_pricing_terms.factor*lib_pricing_use.clip_minimum AS 'Premium',
                                lib_pricing_use.exclusive_rate*lib_pricing_terms.factor*lib_pricing_use.clip_minimum AS 'Gold'

                                From lib_pricing_use

                                Inner Join lib_pricing_terms

                                On lib_pricing_use.terms_cat=lib_pricing_terms.term_cat

                                Where lib_pricing_use.display = 1

                                order by lib_pricing_use.category, lib_pricing_terms.sort")->result_array();
        return $result;
    }

    function get_rf_pricing_details()
    {

        $result = $this->db->query("Select
                        license AS 'License',
                        terms AS 'Terms',
                        budgete_rate AS 'Budget',
                        standard_rate AS 'Standard',
                        premium_rate AS 'Premium',
                        exclusive_rate AS 'Exclusive'
                        From lib_rf_pricing")->result_array();
        return $result;
    }

    /**
     * @param array $paramArr
     * @return bool
     */
    function is_frontend($paramArr)
    {
        $result = $this->db->get_where('lib_frontends', $paramArr)->result_array();
        return (!empty($result));
    }

    function checkAndUpdateTheCategoryInClips($cliId, $collectionsData, $dellCheck)
    {
        //  if ($dellCheck == '1') {
        $this->db_master->query('DELETE FROM lib_clips_keywords WHERE clip_id=' . $cliId . ' AND section_id="category" ');
        //  }
        if (!empty($collectionsData)) {

            foreach ($collectionsData as $k => $dataCategoryInsert) {
                $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword="' . $dataCategoryInsert . '" , clip_id=' . $cliId . ' , section_id="category" ');
            }
        }
    }

    function checkAndUpdateTheCategoryInClipsBatch($cliId, $collectionsData, $dellCheck)
    {
        if ($dellCheck == '1') {
            $this->db_master->query('DELETE FROM lib_clips_keywords WHERE clip_id=' . $cliId . ' AND section_id="category" ');
        }
        if (!empty($collectionsData)) {

            foreach ($collectionsData as $k => $dataCategoryInsert) {
                $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword="' . $dataCategoryInsert . '" , clip_id=' . $cliId . ' , section_id="category" ');
            }
        }
    }

    function checkAndUpdateTheCountryInClips($clipid, $country)
    {
        $this->db_master->query('DELETE FROM lib_clips_keywords WHERE clip_id=' . $clipid . ' AND section_id="country" ');

        if ($country) {

            $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword="' . $country . '" , clip_id=' . $clipid . ' , section_id="country" ');

        }
    }

    function delete_not_visible_keywords($uiD)
    {
        if ($uiD != '') {
            $this->db_master->query('DELETE FROM lib_keywords_notvisible WHERE user_id =' . $uiD . '');
            $this->db_master->query('DELETE FROM lib_keywords_notvisible WHERE user_id =0');
            $this->db_master->query('UPDATE lib_keywords SET hidden=0 WHERE provider_id =' . $uiD . '');
        }
    }


    function updateLibClipsClipIds($uiD)
    {

        $query = $this->db->query("select * from lib_clips_keywords where clip_id=" . $uiD . "  ");
        $rows = $query->result_array();
        if (!empty($rows)) {
            foreach ($rows as $rowData) {
                $querydat = $this->db->query("select * from lib_keywords where keyword='" . mysql_real_escape_string($rowData['keyword']) . "' AND section ='" . $rowData['section_id'] . "' AND collection='' ");
                $rowsData = $querydat->result_array();
                // print_r($rowsData);
                if (!empty($rowsData)) {
                    if (count($rowsData) > 1) {
                        $querydat2 = $this->db->query("select * from lib_keywords where keyword='" . mysql_real_escape_string($rowData['keyword']) . "' AND section ='" . $rowData['section_id'] . "' AND provider_id='" . $_SESSION['uid'] . "'");
                        $rowsData2 = $querydat2->result_array();
                        //print_r($rowsData2);
                        if ($rowsData2[0]['id'] != '') {
                            $this->db_master->query("UPDATE  lib_clips_keywords SET lk_id='" . $rowsData2[0]['id'] . "' WHERE id ='" . $rowData['id'] . "' ");
                        }

                    } else {
                        if ($rowsData[0]['id'] != '') {
                            $this->db_master->query("UPDATE  lib_clips_keywords SET lk_id='" . $rowsData[0]['id'] . "' WHERE id ='" . $rowData['id'] . "' ");
                        }
                    }
                }


            }
        }
    }


    function updateClipstatus($id)
    {
        //usleep(2000000);
        if (is_array($id)) {
            foreach ($id as $clipId) {
                $querySelect = $this->db->query('select * from lib_clips  WHERE  id=' . $clipId);
                $rowSelect = $querySelect->result_array();
                if ($rowSelect[0]['active'] != 2) {
                    $dataUpdateArray = array();
                    if ($rowSelect[0]['description'] == '' || $rowSelect[0]['license'] == '' || $rowSelect[0]['price_level'] == '' || $rowSelect[0]['digital_file_format'] == '' || $rowSelect[0]['digital_file_frame_size'] == '' || $rowSelect[0]['digital_file_frame_rate'] == '') {
                        $dataUpdateArray['active'] = 0;
                    } else {
                        $dataUpdateArray['active'] = 1;
                    }

                    if ($dataUpdateArray['active'] == 1) {

                        $query3 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $clipId, 'section_id' => 'location'));
                        $clip_status3 = $query3->result_array();

                        if ($clip_status3[0]['keyword'] != '') {
                            $dataUpdateArray['active'] = 1;
                        } else {
                            $dataUpdateArray['active'] = 0;
                        }
                    }


                    if ($dataUpdateArray['active'] == 1) {

                        $query2 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $clipId, 'section_id' => 'category'));
                        $clip_status2 = $query2->result_array();
                        if ($clip_status2[0]['keyword'] != '') {
                            $dataUpdateArray['active'] = 1;
                        } else {
                            $dataUpdateArray['active'] = 0;
                        }
                    }

                    $this->db_master->where('id', $clipId);
                    $this->db_master->update('lib_clips', $dataUpdateArray);

                }

                if ($dataUpdateArray['active'] && $dataUpdateArray['active'] != 0) {
                    $this->load->model('rank_clips_model');
                    $weight = $this->rank_clips_model->ADD_CLIP_RANK;
                    $this->rank_clips_model->set_rank($clipId, $weight, '+');
                }

            }
        } else {
            $querySelect = $this->db->query('select * from lib_clips  WHERE  id=' . $id);
            $rowSelect = $querySelect->result_array();
            if ($rowSelect[0]['active'] != 2) {
                $dataUpdateArray = array();
                if ($rowSelect[0]['description'] == '' || $rowSelect[0]['license'] == '' || $rowSelect[0]['price_level'] == '' || $rowSelect[0]['digital_file_format'] == '' || $rowSelect[0]['digital_file_frame_size'] == '' || $rowSelect[0]['digital_file_frame_rate'] == '') {
                    $dataUpdateArray['active'] = 0;
                } else {
                    $dataUpdateArray['active'] = 1;
                }

                if ($dataUpdateArray['active'] == 1) {

                    $query3 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $id, 'section_id' => 'location'));
                    $clip_status3 = $query3->result_array();

                    if ($clip_status3[0]['keyword'] != '') {
                        $dataUpdateArray['active'] = 1;
                    } else {
                        $dataUpdateArray['active'] = 0;
                    }
                }


                if ($dataUpdateArray['active'] == 1) {

                    $query2 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $id, 'section_id' => 'category'));
                    $clip_status2 = $query2->result_array();
                    if ($clip_status2[0]['keyword'] != '') {
                        $dataUpdateArray['active'] = 1;
                    } else {
                        $dataUpdateArray['active'] = 0;
                    }
                }

                $this->db_master->where('id', $id);
                $this->db_master->update('lib_clips', $dataUpdateArray);

            }

            if ($dataUpdateArray['active'] && $dataUpdateArray['active'] != 0) {
                $this->load->model('rank_clips_model');
                $weight = $this->rank_clips_model->ADD_CLIP_RANK;
                $this->rank_clips_model->set_rank($id, $weight, '+');
            }
        }


    }

    /**
     * @param $userId id or ip user to get likes for
     * @param array $clipIds - ids of clips to get likes for
     *
     * @return array in format ['clip_id' => 'like_id']
     * as for user can be more then on like per clip, not important which to use,
     *  according to method implementation, the like with lowest id (first one) will be taken
     */
    public function getClipsLikesByUser($userId, array $clipIds)
    {
        $rating = [];

        if (empty($clipIds) || empty($userId)) {
            return $rating;
        }

        $queryResult = $this->db
            ->from('lib_clip_rating')
            ->select(['id', 'code'])
            ->where('user_id', $userId)
            ->where_in('code', $clipIds)
            ->get()
            ->result_array();
        
        if (!empty($queryResult)) {
            array_walk($queryResult, function($row, $_key) use (&$rating) {
                if (!isset($rating[$row['code']])) {
                    $rating[$row['code']] = intval($row['id']);
                }
            });
        }

        return $rating;
    }

    /**
     * @param $id - clip id
     * @param $currentKeywords - list of keywords, that are already present
     *
     * @return string
     * Generate keywords for clip, using the clarifai SDK
     */
    public function generateClarifaiKeywords($id, $currentKeywords = ''){
        $clip = $this->get_clip_info($id);

        if (isset($clip['preview']) && $clip['preview'] != ''){
            if (strpos($clip['preview'], 'http') === false){
                $clip['preview'] = 'http:' . $clip['preview'];
            }

            if ($currentKeywords == '') {
                $query = $this->db->get_where('lib_clips', array('id' => $id));
                $rows = $query->result_array();
                $currentKeywords = $rows[0]['keywords'];
            }

            $clientId = $this->config->item('clientId');
            $clientSecrect = $this->config->item('clientSecrect');
            $minAccurate = $this->config->item('minAccurate');

            $client = new ClarifaiClient();
            $client->setCredentials($clientId, $clientSecrect);
            $client->processAuthentication();

            $response = $client->tag()->predict($clip['preview']);
            $tag = $response->getResults()[0]->getTag();
            $probs = $tag->getProbs();
            $classes = $tag->getClasses();
            $keywords = array();

            if (is_array($classes[0])){
                foreach ($probs as $frameKey=>$frame){
                    foreach ($frame as $probKey=>$prob){
                        if ($prob >= $minAccurate/100){
                            $keywords[] = $classes[$frameKey][$probKey];
                        }
                    }
                }
            } else {
                foreach ($probs as $key=>$prob){
                    if ($prob > $minAccurate/100){
                        $keywords[] = $classes[$key];
                    }
                }
            }
            $keywordsCount = array_count_values($keywords);
            $keywords = array_unique($keywords);

            $newKeywords = '';
            foreach ($keywords as $keyword) {
                if (stripos($currentKeywords, $keyword) === false && $keywordsCount[$keyword] > 1){
                    if ($newKeywords != '') {
                        $newKeywords .= ', ' . $keyword;
                    } else {
                        $newKeywords = $keyword;
                    }
                }
            }
            return ucwords($newKeywords);
        } else {
            return 'No preview link for ' . $clip['code'] . ' clip.';
        }
    }

    /**
     * @param $keyword - keyword, that was rejected by user
     *
     * @return string
     * Send request to Clarifai API with keyword, that was rejected by user
     */
    public function rejectClarifaiKeyword($keyword){

//            $clientId = $this->config->item('clientId');
//            $clientSecrect = $this->config->item('clientSecrect');
//            $minAccurate = $this->config->item('minAccurate');
//
//            $client = new ClarifaiClient();
//            $client->setCredentials($clientId, $clientSecrect);
//            $client->processAuthentication();
//
//            $response = $client->tag()->predict($clip['preview']);
//            $tag = $response->getResults()[0]->getTag();
//            $probs = $tag->getProbs();
//            $classes = $tag->getClasses();
//            $keywords = array();
//
            return 'done';
    }

    /**
     * @param $keywords - list of keywords, that were accepted by user
     *
     * @return string
     * Send request to Clarifai API with list of keywords, that were accepted by user
     */
    public function approveClarifaiKeyword($keywords){

//            $clientId = $this->config->item('clientId');
//            $clientSecrect = $this->config->item('clientSecrect');
//            $minAccurate = $this->config->item('minAccurate');
//
//            $client = new ClarifaiClient();
//            $client->setCredentials($clientId, $clientSecrect);
//            $client->processAuthentication();
//
//            $response = $client->tag()->predict($clip['preview']);
//            $tag = $response->getResults()[0]->getTag();
//            $probs = $tag->getProbs();
//            $classes = $tag->getClasses();
//            $keywords = array();
//
        return 'done';
    }

}
