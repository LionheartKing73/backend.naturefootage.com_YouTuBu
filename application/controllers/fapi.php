<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/SorlSearchAdapter.php';
require_once(APPPATH . '/libraries/BooleanSearchParser/Parser.php');
require_once(APPPATH . '/libraries/BooleanSearchParser/Splitter.php');

/**
 * @property Register_model $register_model
 * @property Clips_model $clips_model
 * @property Clipbins_model $clipbins_model
 * @property Search_model $search_model
 * @property Formdata_model $formdata_model
 * @property Collections_model $collections_model
 * @property Cats_model $cats_model
 * @property Bin_model $bin_model
 * @property Tagcloud_model $tagcloud_model
 * @property Sharedpages_model $sharedpages_model
 * @property Frontends_model $frontends_model
 * @property Discounts_model $discounts_model
 * @property Pricing_model $pricing_model
 * @property Download_model $download_model
 * @property Deliveryoptions_model $deliveryoptions_model
 * @property Invoices_model $invoices_model
 * @property Galleries_model $galleries_model
 * @property Users_model $users_model
 * @property Tokens_model $download_tokens_model
 * @property Upload_tokens_model $upload_tokens_model
 * @property CI_DB_active_record $db_master
 */
class Fapi extends REST_Controller
{
    private $search_adapter;
    private $store;

    public function __construct()
    {
        parent::__construct();
        // Логгирование запроса, отладка
        //$this->___SaveRequestToLog();
        $this->load->model('clips_model');
        $this->load->model('cats_model');
		$this->load->model('download_hdvideos_model','pm');
    }
	

    function checkfromdb_get()
    {
        //header('Content-Type: application/json;');
        $hdvalue = $this->pm->get_hdvalue();
		if (!empty($hdvalue)) 
		{
            $this->response($hdvalue['hdvideochoice'], 200);
        } else {
            $this->response(NULL, 404);
        }

    }

    private function ___SaveRequestToLog()
    {
        $get = json_encode($this->get());
        $post = json_encode($this->post());
        $string = PHP_EOL . date('d.m.Y H:i:s') . ' >>> ' . microtime() . ' >> ' . $_SERVER['REQUEST_TIME'] . PHP_EOL;
        $string .= '    -    FROM: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
        $string .= '    -    URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
        $string .= '    ---- GET: ' . $get . PHP_EOL;
        $string .= '    ---- POST: ' . $post . PHP_EOL;
        file_put_contents(FCPATH . '___rest.api.log', $string, FILE_APPEND);
    }

    public static function debugLog($str, $date = true)
    {
        if (is_array($str) || is_object($str)) $str = json_encode($str);
        $string = ($date) ? PHP_EOL . date('d.m.Y H:i:s') . ' >>> ' . microtime() . ' >> ' . $_SERVER['REQUEST_TIME'] . PHP_EOL : '' . PHP_EOL;
        $string .= '    -    str: ' . $str . PHP_EOL;
        file_put_contents(FCPATH . '___rest.api.log', $string, FILE_APPEND);
    }
    function debug_post(){
        $this->load->model('users_model');
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }else{
            $this->users_model->debug($this->post('file'),$this->post('line'),$this->post('msg'),$this->post('note').$this->get('provider'));
        }
        $this->response(array('data'=>'ok'), 200);
    }

    public function test_get()
    {
        $query = $this->db->query("SELECT * FROM s1;");
        echo 1;
    }

    function filter_post()
    {
        $this->filter_get();
    }

    function filter_get()
    {
        $words = null;
        $owner = null;
        if ($this->get('words') || $this->post('words') || $this->post('owner')) {
            $words = $this->get('words') ? $this->get('words') : $this->post('words');
            $words = addslashes($words);
            $words = str_replace("+", " ", $words);
            $words = trim($words);

            $parsedwords = '';
            if (!empty($words)) {
                $parser = new \DuncanOgle\BooleanSearchParser\Parser();
                $parsedwords = $parser->parse($words);
                unset($words);
                unset($parser);
            }

                // -- determine contributor
                $this->load->model('users_model');
                $owner = $this->get('owner') ? $this->get('owner') : $this->post('owner');
                $user = !empty($owner) ? $this->users_model->GetUserByLogin($owner) : null;
                unset($owner);
                // --

                //secondary filter
                $sql = "SELECT lk.id
                        FROM lib_keywords lk
                        LEFT JOIN (
                        SELECT DISTINCT(lk_id)
                        FROM lib_clips_keywords lck
                        INNER JOIN lib_clips c ON c.id = lck.clip_id
                        WHERE c.active = 1
                        " . (!empty($parsedwords)
                            ? " AND lk_id IS NOT NULL AND MATCH(c.keywords, c.description, c.code_search) AGAINST(" . $this->db->escape($parsedwords) . " IN BOOLEAN MODE)"
                            : ''
                        )
                        . (!empty($user) ? " AND c.client_id = $user[id]" : '') . "
                        ) tmp ON tmp.lk_id = lk.id
                        WHERE tmp.lk_id IS NULL AND lk.collection = 'Nature Footage'";
                $query = $this->db->query($sql);
                $data = $query->result_array();


                $sql = "SELECT GROUP_CONCAT(DISTINCT price_level SEPARATOR ' ') AS price_level,
                            GROUP_CONCAT(DISTINCT license SEPARATOR ' ') AS license,
                            GROUP_CONCAT(DISTINCT sort_format SEPARATOR ' ') AS sort_format,
                            GROUP_CONCAT(DISTINCT brand SEPARATOR ' ') AS brand
                            FROM lib_clips c
                            WHERE
                            c.active = 1";
                if (!empty($parsedwords)) {
                    $sql .= " AND MATCH(c.keywords, c.description, c.code_search) 
                                  AGAINST (" . $this->db->escape($parsedwords) . " IN BOOLEAN MODE)";
                }
                if (!empty($user)) {
                    $sql .= " AND c.client_id = $user[id]";
                }
                $query = $this->db->query($sql);
                $filter_default = $query->result_array();
                $filter_default = $filter_default[0];

                $filter_default["license"] = explode(" ", $filter_default["license"]);
                $filter_default["sort_format"] = explode(" ", $filter_default["sort_format"]);
                $filter_default["price_level"] = explode(" ", $filter_default["price_level"]);
                $filter_default["brand"] = explode(" ", $filter_default["brand"]);

                $filter_arr = array();
                $filter_arr['collection_filter'] = 1;
                $filter_arr['brand_filter'] = 1;

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
                $this->load->model('search_model');
                foreach ($this->search_model->getFormatCategoryFilter()['options'] as $option) {
                    $filter_arr['format_category_' . $option['value']] =
                        (int) in_array($option['value'], $filter_default["sort_format"]);
                }
                // --


                //Brand available
                $filter_arr['brand_filter_name'] = array();
                if (in_array("1", $filter_default["brand"])) {
                    $filter_arr['nature_footage'] = 1;
                } else {
                    $filter_arr['nature_footage'] = 0;
                }

                if (in_array("2", $filter_default["brand"])) {
                    $filter_arr['natureflix'] = 1;
                } else {
                    $filter_arr['natureflix'] = 0;
                }

                foreach ($filter_arr as $key => $value) {
                    if ($value == 0) {
                        $data[]["id"] = "_" . $key;
                    }
                }

                $this->response($data, 200);
        }
        $this->response(NULL, 404);
    }

    function clip_post()
    {
        $this->clip_get();
    }

    function get_clips_count_get()
    {
        $count = $this->clips_model->get_active_clips_count();
        echo json_encode($count[0]);
    }

    function clipXML_post()
    {
        $clips = $this->clips_model->get_clips_xml($this->post('limit'), $this->post('offset'));
        foreach ($clips as $clip) {
            if (is_array($clip) && !empty($clip['id'])) {
                $id = $clip['id'];
                $contentSize = explode('x', $clip['digital_file_frame_size']);
                $data[$id]['id'] = $id;
                $data[$id]['link'] = 'http://www.naturefootage.com/video-clips/' . $clip['code'];
                $data[$id]['description'] = $clip['description'];
                $data[$id]['keywords'] = $clip['keywords'];
                $data[$id]['code'] = $clip['code'];
                $data[$id]['rating'] = '';
                $data[$id]['pubDate'] = date('D, d M Y H:i:s T', strtotime($clip['creation_date']));
                $data[$id]['content']['url'] = $clip['res'];
                $data[$id]['content']['type'] = $clip['source_format'];
                $data[$id]['content']['height'] = $contentSize[1];
                $data[$id]['content']['width'] = $contentSize[0];
                $data[$id]['content']['duration'] = round($clip['duration']);
                $data[$id]['thumbnail']['url'] = $clip['thumb'];
            }
        }
        $this->response($data, 200);
    }

    function newClipXML_post()
    {
        $clips = $this->clips_model->get_new_clips_xml($this->post('date'));
        foreach ($clips as $clip) {
            if (is_array($clip) && !empty($clip['id'])) {
                $id = $clip['id'];
                $contentSize = explode('x', $clip['digital_file_frame_size']);
                $data[$id]['id'] = $id;
                $data[$id]['link'] = 'http://www.naturefootage.com/video-clips/' . $clip['code'];
                $data[$id]['description'] = $clip['description'];
                $data[$id]['keywords'] = $clip['keywords'];
                $data[$id]['code'] = $clip['code'];
                $data[$id]['rating'] = '';
                $data[$id]['pubDate'] = date('D, d M Y H:i:s T', strtotime($clip['creation_date']));
                $data[$id]['content']['url'] = $clip['res'];
                $data[$id]['content']['type'] = $clip['source_format'];
                $data[$id]['content']['height'] = $contentSize[1];
                $data[$id]['content']['width'] = $contentSize[0];
                $data[$id]['content']['duration'] = round($clip['duration']);
                $data[$id]['thumbnail']['url'] = $clip['thumb'];
            }
        }
        $this->response($data, 200);
    }

    function clip_get()
    {
        if (!$this->get('id') || !$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $user_login = ($this->post('user_login')) ? $this->post('user_login') : 'guest';
        //$clip_id = is_numeric($this->get('id')) ? (int)$this->get('id') : $this->_clip_id($this->get('id'));
        $clip = $this->clips_model->get_clip_info($this->get('id'), 'en', true);
        if (is_array($clip) && !empty($clip['id'])) {
            $this->clips_model->ClipLogger($clip['id'], $this->get('provider'), $user_login, Clips_model::CLIP_ACTION_VIEW);

            $clipSequences = $this->clips_model->get_backend_lb_sequece($clip['id'], 'en');


            $arraySequecne = array();
            $arrayClipbin = array();
            foreach ($clipSequences as $key => $dataGet) {

                if ($dataGet['is_sequence'] == '1') {
                    $arraySequecne[$key]['id'] = $dataGet['backend_lb_id'];
                    $arraySequecne[$key]['title'] = $dataGet['title'];
                } else {
                    $arrayClipbin[$key]['id'] = $dataGet['backend_lb_id'];
                    $arrayClipbin[$key]['title'] = $dataGet['title'];
                }
            }

            $relatedClips = $this->post('related_clips') ?: [];
            if (!in_array($clip['id'], $relatedClips)) {
                $relatedClips[] = $clip['id'];
            }
            $clip['user_likes'] = $this->getClipsLikesByUser($relatedClips);
            unset($relatedClips);

            $keywords = $this->clips_model->getAllKeywordsByClipId($clip['id']);
            $shot_type_key_arr = array();
            $subject_type_key_arr = array();
            $primary_type_key_arr = array();
            $other_sub_type_arr = array();
            $action_type_key_arr = array();
            $time_type_key_arr = array();
            $concept_type_key_arr = array();
            $location_type_key_arr = array();
            $habitat_type_key_arr = array();
            $appereance_type_arr = array();
            foreach ($keywords as $section => $value) {
                if ($value['section_id'] == 'shot_type') {
                    $shot_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'primary_subject') {
                    $primary_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'subject_category') {
                    $subject_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'other_subject') {
                    $other_sub_type_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'actions') {
                    $action_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'time') {
                    $time_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'concept') {
                    $concept_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'location') {
                    $location_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'habitat') {
                    $habitat_type_key_arr[$section] = $value['keyword'];
                }
                if ($value['section_id'] == 'appearance') {
                    $appereance_type_arr[$section] = $value['keyword'];
                }
            }

            $clip['shot_type_keyword'] = $shot_type_key_arr;
            $clip['subject_category_keyword'] = $subject_type_key_arr;
            $clip['primary_type_keyword'] = $primary_type_key_arr;
            $clip['other_type_keyword'] = $other_sub_type_arr;
            $clip['time_type_keyword'] = $time_type_key_arr;
            $clip['concept_type_keyword'] = $concept_type_key_arr;
            $clip['location_type_keyword'] = $location_type_key_arr;
            $clip['habitat_type_keyword'] = $habitat_type_key_arr;
            $clip['appereance_type_keyword'] = $appereance_type_arr;
            $clip['action_type_keyword'] = $action_type_key_arr;

            $clip['arrayClipbin'] = $arrayClipbin;
            $clip['arraySequecne'] = $arraySequecne;

            $data['data'] = $clip;
            $data['method'] = 'clip';

            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_clip_brand_post()
    {
        if (!$this->post('clip_id')) {
            $this->response(NULL, 400);
        }
        $clip_id = (int)$this->post('clip_id');
        $clip = $this->clips_model->get_clip_by_id($clip_id);
        if (!empty($clip)) {
            $this->response($clip['brand'], 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_NF_clips_post(){
        if (!$this->post('clips')) {
            $this->response(NULL, 400);
        }
        $clips = $this->post('clips');
        $result = $this->clips_model->sort_nf_clips($clips);

        if (!empty($result)) {
            $this->response($result, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function clips_thumbs_post()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }

        $ids = $this->post('ids');
        $thumbs = array();
        foreach ($ids as $id) {
            $thumbs[$id] = $this->clips_model->get_clip_path($id, 'thumb');
        }
        if (!empty($thumbs)) {
            $this->response($thumbs, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function confirm_download_post()
    {

        if (isset($_POST['user']) and isset($_POST['file'])) {
            $user = $_POST['user'];
            $this->load->model('download_model');
            $this->download_model->confirm_download($user, $_POST['file']);
        }
    }

    //function clips_get(){$this->clips_post();}
    function clips_post()
    {
        if (!$this->get('provider') || !$this->get('frontend')) {
            $this->response(NULL, 400);
        }
//        $this->search_adapter = new SorlSearchAdapter();
        $this->load->model('search_model');
        $this->load->model('users_model');
        $post = $this->post();
        /*$collections_ids = array();
        if ($post['collection']) {
            $this->load->model('collections_model');
            $collections = $this->collections_model->get_collections_list(array('search_term' => $post['collection']));
            if ($collections) {
                foreach ($collections as $collection) {
                    $collections_ids[] = $collection['id'];
                }
            }
            unset($post['collection']);
        }*/
        
        $owner = $this->post('owner');
        if (!empty($owner)) {
            // try to find user id
            $client_id = $this->users_model->get_id_and_login($owner, 'id');
        }
        if (empty($client_id)) {
            // if client id is still empty, get it from request params
            $client_id = $this->get('provider');
        }

        // init filter
        $filter = array_merge_recursive(array('active' => 1, 'client_id' => $client_id), $post);


        // brand
        if (!empty($post['brand'])) $filter['brand'] = $post['brand'];
        // license
        if (!empty($post['license'])) $filter['license'] = $post['license'];

        if ($this->get('words')) {
            $filter['words'] = addslashes($this->get('words'));

            $this->search_model->update_keyword_statistic($client_id, $filter['words']);

            $user_login = ($this->post('user_login')) ? $this->post('user_login') : 'guest';
            $words = $filter['words'];
            $this->search_model->SearchLogger($words, $client_id, $user_login);
        }

//Parsing keyword for relevance search -- conankid
        $words = $filter['words'];
        unset($filter['words']);
        $words = str_replace("+", " ", $words);
        $words = trim($words);
        $words = stripcslashes($words);
        $parsedwords = '';

        if (!empty($words)) {
            $parser = new \DuncanOgle\BooleanSearchParser\Parser();
            $parsedwords = $parser->parse($words);
            $filter['words'] = $words;
            $filter['parsedwords'] = $parsedwords;
        }
//End keywword parse

        $order = '';//' ORDER BY UUID() ';
        $limit = $this->_limit();
        $order = '';

        $orderpart = array();
        if ($this->post('sort')) {
            $sorts = $this->post('sort');
            foreach ($sorts as $sort) {
                if ($sort == 'code asc' || $sort == 'duration asc' || $sort == 'price_level asc' || $sort == 'code desc' || $sort == 'duration desc' || $sort == 'price_level desc') {
                    $orderpart[] = 'c.' . $sort;
                } elseif ($sort == 'weight desc' && !empty($filter['parsedwords'])) {
                    $orderpart[] = "sort_rating desc, sort_format desc, sort_age desc";
                }
            }
            if (count($orderpart) > 0) {
                $order = " ORDER BY " . implode(", ", $orderpart) . " ";
            }
            unset($filter['sort']);
        }
        //default order
        if (empty($order)) {
            $order = " ORDER BY sort_rating desc, sort_format desc, sort_age desc ";
        }


        if ($this->get('category')) {
            $cat_id = is_numeric($this->get('category')) ? (int)$this->get('category') : $this->_category_id($this->get('category'));
            if ($cat_id) {
                $filter['cat_id'] = $cat_id;
            }
        }

//        $msg = $this->post('clip_id');
//        mail("faisal.supple@gmail.com","Success Query",$msg);
//        if ($this->search_adapter->ping()) {
//            $offset = $this->get('from') ? $this->get('from') : 0;
//            $limit = $this->get('limit') ? $this->get('limit') : 100;
//            $search_result = $this->search_adapter->search_clips($filter, $offset, $limit, $sort);
//            mail('imranmani.numl@gmail.com', 'test', $search_result);
//            if ($search_result['total'] > 0) {
//
//
//                $clips = $this->clips_model->get_clips_by_ids($search_result['clips']);
//                $data['data'] = $clips;
//                $data['total'] = $search_result['total'];
//                $data['from'] = $offset + 1;
//                $data['to'] = $offset + count($search_result['clips']);
//                $data['method'] = 'clips';
//                $data['solrdata'] = $search_result;
//                $data['solrkeywords'] = $this->search_model->get_clip_keywords_facet($this->search_model->get_clip_keywords(1, 1), $search_result['facet']);
//            }
//        } else {

        //  mail("imranmani.numl@gmail.com", "Query Success", 'test');
        //$limit = ' LIMIT 0,10 ';
        $is_Front = $this->clips_model->is_frontend(array('provider_id' => $client_id, 'brand' => 'naturefootage'));
        $data['is_front'] = $is_Front;

        // 8888888888888 count query was moved to separate api method
//        $this->benchmark->mark('c');
//        $total = $this->clips_model->get_clips_count('en', $filter, $is_Front);
        // -- 8888888888888 -- //

        $this->benchmark->mark('ec');
        $clips = $this->clips_model->get_clips_list('en', $filter + ['checkFront' => $is_Front], $order, $limit, true);
        $this->benchmark->mark('el');

        // save hash of last used filters
        $data['filter_hash'] = $this->clips_model->getFilterSqlHash();

        $data['user_likes'] = $this->getClipsLikesByUser(array_column($clips, 'id'));

        //$clipsFilters = $this->clips_model->get_clips_list_filters('en', $filter, $order, $limit, true);
        //$data['filtered_search_clips'] = $this->clips_model->get_search_filtered_clips($filter, '', '', $order, '', $this->group['is_admin'], $this->langs);
        // mail('imranmani.numl@gmail.com', 'TEST', print_r($clips[sizeof($clips)-1]['filters'], true));
        $data['clips_filters_result'] = $clips[0]['returnFiltersResults'];
        unset ($clips[0]['returnFiltersResults']);

        //   mail("imranmani.numl@gmail.com", "Query Success", print_r( $clips, TRUE));
        // if (!empty($clips[0]['id'])) {
//            foreach ($clipsFilters['clips_ids'] as $key => $val) {
//                $filter_clips_id[$key] = $val;
//            }
//                foreach ($filter_clips_id as $sub_clip) {
//                    $search_filter_arr[] = "'" . $sub_clip . "'";
//                }
        //  mail('imranmani.numl@gmail.com','sadasd',$filter_clips_id);
        //    $search_filter_clip_id = implode(",", $clipsFilters['clips_ids']);
//            $searchin_filter_query = $this->db->query("SELECT keyword_id FROM lib_clip_keywords WHERE clip_id IN (" . $search_filter_clip_id . ")")->result_array();
//
//            //mail('imranmani.numl@gmail.com','sdfdsf',"SELECT keyword_id FROM lib_clip_keywords WHERE clip_id IN (" . $search_filter_clip_id . ")");
//            foreach ($searchin_filter_query as $key => $val) {
//                $filter_keywords_id[$key] = "'" . $val['keyword_id'] . "'";
//            }
//
//            $search_filter_clip_id = implode(",", $filter_keywords_id);
//
//            $searchin_filter_query = $this->db->query("SELECT * FROM lib_keywords WHERE id IN (" . $search_filter_clip_id . ") Group By keyword")->result_array();
        // mail('imranmani.numl@gmail.com','sdfdsf',"SELECT keyword_id.c,keyword.d,section.d FROM lib_clip_keywords c WHERE c.clip_id IN (" . $search_filter_clip_id . ") LEFT JOIN ON lib_keywords d WHERE c.keyword_id=d.id Group By d.keyword");
//            $searchin_filter_query = $this->db->query("SELECT c.keyword_id,d.keyword,d.section,d.id FROM lib_clip_keywords c  LEFT JOIN lib_keywords d ON c.keyword_id=d.id WHERE c.clip_id IN (" . $search_filter_clip_id . ") Group By d.keyword")->result_array();
//
//
//            $shot_type_arr = array();
//            $subject_cat_arr = array();
//            $primary_type_arr = array();
//            $other_subject_arr = array();
//            $actions_arr = array();
//            $time_arr = array();
//            $concept_arr = array();
//            $loctaion_arr = array();
//            $habitat_arr = array();
//            foreach ($searchin_filter_query as $key => $search_value) {
//                $searchin_filter_keyword[$key] = $search_value['keyword'];
//                $search_filter_section_type[$key] = $search_value['section'];
//                if ($search_value['section'] == 'shot_type') {
//                    $shot_type_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'subject_category') {
//                    $subject_cat_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'primary_subject') {
//                    $primary_type_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'other_subject') {
//                    $other_subject_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'actions') {
//                    $actions_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'appearance') {
//                    $appearance_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'time') {
//                    $time_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'habitat') {
//                    $habitat_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'concept') {
//                    $concept_arr[$key] = $search_value['keyword'];
//                }
//                if ($search_value['section'] == 'location') {
//                    $loctaion_arr[$key] = $search_value['keyword'];
//                }
//            }
//        }


        $data['keywords_for_filters'] = array(
            'shot_type' => array(
                'label' => 'Shot Type',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['shot_type_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'subject_category' => array(
                'label' => 'Subject Category',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['subject_category_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'actions' => array(
                'label' => 'Action',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['actions_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'appearance' => array(
                'label' => 'Appearance',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['appearance_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'time' => array(
                'label' => 'Time',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['time_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'location' => array(
                'label' => 'Location',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['location_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'habitat' => array(
                'label' => 'Habitat',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['habitat_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'concept' => array(
                'label' => 'Concept',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['concept_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'category' => array(
                'label' => 'Collection',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $data['clips_filters_result']['category_arr'],
                'additional' => 0,
                'display' => 1
            ),
            'brand' => array(
                'label' => 'Clips and Edited Videos',
                'type' => 'ckeckbox',
                'options' => array(
                    array('value' => 1, 'label' => 'Single Clips'),
                    array('value' => 2, 'label' => 'Edited Videos')
                ),
                'additional' => 0,
                'display' => 1
            ),
            'creation_date' => array(
                'label' => 'Date Added',
                'type' => 'select',
                'collapsed' => 1,
                'options' => array(
                    array('value' => 'past_week', 'label' => 'Past Week'),
                    array('value' => 'past_month', 'label' => 'Past Month'),
                    array('value' => 'past_year', 'label' => 'Past Year'),
                    array('value' => 'over_one_year', 'label' => 'Over One Year'),
                ),
                'additional' => 0,
                'display' => 1
            ),
            'duration' => array(
                'label' => 'Duration',
                'type' => 'select',
                'collapsed' => 1,
                'options' => array(
                    array('value' => '1to10', 'label' => '>10 Seconds'),
                    array('value' => '1to20', 'label' => '>20 Seconds'),
                    array('value' => '1to30', 'label' => '>30 Seconds'),
                    array('value' => '1to60', 'label' => '>60 Seconds'),
                    array('value' => '61to', 'label' => '61+ Seconds'),
                ),
                'additional' => 0,
                'display' => 1
            )
        );
        unset($data['clips_filters_result']['shot_type_arr']);
        unset($data['clips_filters_result']['subject_category_arr']);
        unset($data['clips_filters_result']['actions_arr']);
        unset($data['clips_filters_result']['appearance_arr']);
        unset($data['clips_filters_result']['time_arr']);
        unset($data['clips_filters_result']['location_arr']);
        unset($data['clips_filters_result']['habitat_arr']);
        unset($data['clips_filters_result']['concept_arr']);
        unset($data['clips_filters_result']['category_arr']);


//        $data['shot_type_filter'] = $shot_type_arr;
//        $data['subject_category_filter'] = $subject_cat_arr;
//        $data['primary_type_filter'] = $primary_type_arr;
//        $data['other_type_filter'] = $other_subject_arr;
//        $data['action_type_filter'] = $active_filter_arr;
//        $data['time_type_filter'] = $time_arr;
//        $data['concept_type_filter'] = $concept_arr;
//        $data['location_type_filter'] = $loctaion_arr;
//        $data['habitat_type_filter'] = $habitat_arr;


        $data['data'] = $clips;
        // 8888888888888 count query was moved to separate api method
//        $data['total'] = $total;
//        $data['time'] = 'COUNT:' . $this->benchmark->elapsed_time('c', 'ec') . ' CLIPS:' . $this->benchmark->elapsed_time('ec', 'el');
        // -- 8888888888888 -- //
        $data['time'] = ' CLIPS:' . $this->benchmark->elapsed_time('ec', 'el');


        $offset = $this->get('from') ? $this->get('from') : 0;
        $data['from'] = $offset + 1;
        $data['to'] = $offset + count($clips);

        $data['method'] = 'clips';
        $data['filter'] = $filter;
        //$this->response($data, 200);
        // }
        if (!empty($data['data'])) {
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }


    /**
     * return count value by filter hash of already build filter
     */
    function clips_total_post()
    {
        $fitlerHash = $this->post('filter_hash');

        if (!empty($fitlerHash)) {
            $total = $this->clips_model->get_clips_count('en', ['filter_hash' => $fitlerHash], true);
            $this->response(compact('total'), 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    /**
     * @deprecated since 1/11/2016
     */
    function next_prev_clip_post()
    {
        die("deprecated");
        if (!$this->get('provider') || !$this->post('clip_id') || !$this->post('clip_offset') || !$this->get('frontend')) {
            $this->response(NULL, 400);
        }

        $this->search_adapter = new SorlSearchAdapter();

        $post = $this->post();
        $collections_ids = array();
        if ($post['collection']) {
            $this->load->model('collections_model');
            $collections = $this->collections_model->get_collections_list(array('search_term' => $post['collection']));
            if ($collections) {
                foreach ($collections as $collection) {
                    $collections_ids[] = $collection['id'];
                }
            }
            unset($post['collection']);
        }

        $brands_ids = array();
        $this->load->model('brands_model');
        $frontend_brands = $this->brands_model->get_brands_list(array('frontend_id' => $this->get('frontend')));
        if ($frontend_brands) {
            foreach ($frontend_brands as $frontend_brand) {
                $brands_ids[] = $frontend_brand['id'];
            }
        }

        $filter = array_merge_recursive(array('active' => 1, 'client_id' => $this->get('provider'),
            'collection_id' => $collections_ids, 'brand_id' => $brands_ids), $post);

        $sort = array();
        if ($this->post('sort')) {
            $sort = $this->post('sort');
            unset($filter['sort']);
        }
        if ($this->get('category')) {
            $cat_id = is_numeric($this->get('category')) ? (int)$this->get('category') : $this->_category_id($this->get('category'));
            if ($cat_id) {
                $filter['cat_id'] = $cat_id;
            }
        }
        if ($this->get('words')) {
            $this->load->model('search_model');
            $filter['words'] = $this->get('words');
        }

        if ($this->search_adapter) {
            $clip_id = (int)$this->post('clip_id');
            $offset = (int)$this->post('clip_offset') - 1;
            if ($offset != 0) {
                $offset--;
                $limit = 3;
            } else {
                $limit = 2;
            }
            $search_result = $this->search_adapter->search_clips($filter, $offset, $limit, $sort);
            if ($search_result['total'] > 0) {
                if (count($search_result['clips']) == 1 || !count($search_result['clips']))
                    $data['data'] = false;
                elseif (count($search_result['clips']) == 2) {
                    if ($search_result['clips'][0] == $clip_id)
                        $data['data'] = array('prev_clip_id' => 0, 'next_clip_id' => $search_result['clips'][1]);
                    elseif ($search_result['clips'][1] == $clip_id)
                        $data['data'] = array('prev_clip_id' => $search_result['clips'][0], 'next_clip_id' => 0);

                } elseif (count($search_result['clips']) == 3)
                    $data['data'] = array('prev_clip_id' => $search_result['clips'][0], 'next_clip_id' => $search_result['clips'][2]);

            }
            foreach ($data['data'] as $key => $item) {
                if ($key == 'prev_clip_id')
                    $data['data']['prev_clip_code'] = $item ? $this->clips_model->get_clip_code($item) : '';
                elseif ($key == 'next_clip_id')
                    $data['data']['next_clip_code'] = $item ? $this->clips_model->get_clip_code($item) : '';
            }
        }

        if ($data['data']) {
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }

    }

    function categories_get()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $filter = array(
            'c.active' => 1,
            'c.provider_id' => $this->get('provider')
        );
        if ($this->get('featured')) {
            $filter['c.featured'] = 1;
        }
        $order = 'c.parent_id, c.ord';
        $limit = $this->_limit();

        if ($this->get('category')) {
            $cat_id = is_numeric($this->get('category')) ? (int)$this->get('category') : $this->_category_id($this->get('category'));
            $filter['c.parent_id'] = $cat_id;
        }

        $total = $this->cats_model->get_api_cats_count($filter);
        $cats = $this->cats_model->get_api_cats_list($filter, array(), $order);

        $data['data'] = $cats;
        $data['total'] = $total;
        $data['method'] = 'categories';
        if ($cats) {
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }

    }

    function adduser_post()
    {
        $this->load->model('register_model');
        $this->load->model('users_model');
        //Fapi::debugLog(__CLASS__.'->'.__FUNCTION__.' START',true);
        if ($this->post('login') && $this->post('pass') && $this->post('email') && $this->post('provider_id')) {
            $existLogin = $this->users_model->is_username_exists($this->post('login'));
            $existEmail = $this->users_model->is_email_exists($this->post('email'));
            //$validLogin=$this->users_model->is_username_valid($this->post( 'login' ));
            if (!empty($existLogin) && $existLogin > 0) {
                $this->response(array('method' => 'adduser', 'status' => 'existlogin', 'data' => array('status' => 'existlogin')), 200);
            } elseif (!empty($existEmail) && $existEmail > 0) {
                $this->response(array('method' => 'adduser', 'status' => 'existemail', 'data' => array('status' => 'existemail')), 200);
            }/*elseif(!$validLogin){
                $this->response(array ('method' => 'adduser','status' => 'error','data' => array('status'=>'error')),200);
            }*/
            //Fapi::debugLog(__CLASS__.'->'.__FUNCTION__.PHP_EOL.json_encode($_REQUEST).PHP_EOL.PHP_EOL.PHP_EOL);
            $addUser = $this->register_model->save_client();

            if ($addUser) {
                $this->response(
                    array(
                        'method' => 'adduser',
                        'status' => 'ok',
                        'data' => array('status' => 'ok')
                    ),
                    200
                );
            } else {
                $this->response(
                    array(
                        'method' => 'adduser',
                        'status' => 'error',
                        'data' => array('status' => 'error')
                    ),
                    200
                );
            }
        } else {
            $this->response(NULL, 404);
        }
    }

    function updateuser_post()
    {
        $this->load->model('register_model');
        if ($this->post('login') && $this->post('provider_id')) {
            $this->register_model->update_client();
            $this->response(array('method' => 'updateuser'), 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function update_provider_views_post()
    {
        if (!$this->post('provider') || !$this->post('remote_addr')) {
            $this->response(NULL, 400);
        }
        $this->load->model('register_model');
        $this->register_model->update_views($this->post('provider'), $this->post('remote_addr'));
        $views_count = $this->register_model->get_views_count($this->post('provider'));
        if ($views_count || $views_count == 0) {
            $data['data'] = $views_count;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function update_metauser_post()
    {
        if (!$this->post('id')) {
            $this->response(NULL, 400);
        }
        $id = $this->post('id');
        $meta = json_decode($this->post('meta'));
        if ($meta) {
            $metadata = array();
            foreach ($meta as $item) {
                switch ($item->meta_key) {
                    case 'company_name':
                    case 'country':
                    case 'description':
                    case 'email_updates':
                    case 'frontend_url':
                    case 'phone':
                    case 'referral':
                    case 'contrib-main-image':
                    case 'contrib-main-video':
                    case 'avatar':
                        $metadata[$item->meta_key] = $item->meta_value;
                    default:
                        break;
                }
            }
            $this->load->model('users_model');
            $this->users_model->update_meta($id, $metadata);
            $user = $this->users_model->get_user($id);
        }
        if (!empty($user)) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function add_provider_follower_post()
    {
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('register_model');
        $this->register_model->add_follower($this->post('user'), $this->get('provider'));
        $followers_count = $this->register_model->get_followers_count($this->get('provider'));
        if ($followers_count || $followers_count == 0) {
            $data['data'] = $followers_count;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_provider_statistic_post()
    {
        if (!$this->post('provider')) {
            $this->response(NULL, 400);
        }
        $this->load->model('register_model');
        $statistic = array();
        $statistic['views_count'] = $this->register_model->get_views_count($this->post('provider'));
        $statistic['followers_count'] = $this->register_model->get_followers_count($this->post('provider'));
        $statistic['likes_count'] = $this->register_model->get_likes_count($this->post('provider'));
        $statistic['purchases_count'] = $this->register_model->get_purchases_count($this->post('provider'));
        $statistic['downloads_count'] = $this->clips_model->get_downloads_count($this->post('provider'));
        if ($statistic) {
            $data['data'] = $statistic;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_user_token_post()
    {
        /*$login =  $this->input->post('login', true);
        $password = $this->input->post('password', true);*/
        $params = $this->input->post('params', true);
        $login = $params['login'];
        $password = $_REQUEST['params']['password'];
        $token = false;
        if ($login && $password) {
            $token = $this->get_user_token($login, $password);
            $data['token'] = $token;
        }
        if ($token) {
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function check_user_token_post()
    {
        $login = $this->post('login');
        if (!$login) {
            $this->response(NULL, 400);
        }
        $token = $this->check_user_token($login, $this->post('token'));
        $data['token'] = $token;
        if ($token) {
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    private function check_user_token($login, $token = '')
    {
        $this->db = $this->load->database('default', TRUE);
        $query = $this->db->query('SELECT token FROM lib_users WHERE login = ' . $this->db->escape($login) . ' AND token =' . $this->db->escape($token));
        $user = $query->result_array();
        return (isset($user[0]['token']));
    }

    //Cart
    function cartclips_post()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $clips_ids = $this->post('cart_clips');
        if (!$clips_ids) {
            $this->response(NULL, 400);
        }
        $clips = $this->clips_model->get_cart_clips($clips_ids);
        if ($clips) {
            $data['data'] = $clips;
            $data['user_likes'] = $this->getClipsLikesByUser($clips_ids);
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function cartclip_post()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $clips_id = $this->post('clip_id');
        if (!$clips_id) {
            $this->response(NULL, 400);
        }
        $clips = $this->clips_model->get_cart_clips(array($clips_id));
        if ($clips) {
            $data['data'] = $clips[0];
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_clip_code_post(){
        $data['data'] =$this->clips_model->get_clip_code($this->post('id'));
        $this->response($data, 200);
    }

    function license_categories_get()
    {
        $this->load->model('pricing_model');
        $categories = $this->pricing_model->get_license_categories($this->get('is_admin'));
        if ($categories) {
            $data['data'] = $categories;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function natflix_license_categories_get()
    {
        $this->load->model('pricing_model');
        $categories = $this->pricing_model->get_natflix_license_categories($this->get('is_admin'));
        if ($categories) {
            $data['data'] = $categories;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function license_use_post()
    {
        $this->load->model('pricing_model');
        if ($this->post('id'))
            $uses = $this->pricing_model->get_use((int)$this->post('id'));
        else
            $uses = $this->pricing_model->get_license_use($this->post('category'), $this->post('collection'), $this->post('is_admin'));
        if ($uses) {
            $data['data'] = $uses;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function license_term_post()
    {
        $this->load->model('pricing_model');
        if ($this->post('id'))
            $terms = $this->pricing_model->get_license_term_by_id($this->post('id'));
        else
            $terms = $this->pricing_model->get_license_term($this->post('use'));
        if ($terms) {
            $data['data'] = $terms;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function license_use_min_duration_post()
    {
        $this->load->model('pricing_model');
        $duration = $this->pricing_model->get_license_use_min_duration($this->post('use'));
        if ($duration) {
            $data['data'] = $duration;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function clip_price_post()
    {
        $this->load->model('pricing_model');
        $this->load->model('deliveryoptions_model');
        $clip_id = $this->post('clip_id');
        $license_use = $this->post('license_use');
        $license_term = $this->post('license_term');
        $delivery_format = $this->post('delivery_format');
        if (!$this->get('provider') || !$clip_id || !$license_use || !$license_term) {
            $this->response(NULL, 404);
        }
        $price = $this->pricing_model->get_clip_price($clip_id, $license_use, $license_term, $delivery_format);
        if ($delivery_format)
            $data['data']['delivery_price'] = $this->deliveryoptions_model->get_delivery_option_price($delivery_format, $clip_id);

        if ($price !== false) {
            $data['data']['license_free_price'] = $price;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function rf_clip_price_post()
    {
        $this->load->model('pricing_model');
        $clip_id = $this->post('clip_id');
        $license_use = $this->post('license_use');
        $delivery_format = $this->post('delivery_format');
        if (!$this->get('provider') || !$clip_id || !$license_use) {
            $this->response(NULL, 404);
        }
        $price = $this->pricing_model->get_rf_clip_price($clip_id, $license_use, $delivery_format);
        if ($price !== false) {
            $data['data'] = $price;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function delivery_formats_post()
    {
        $this->load->model('deliveryoptions_model');
        $clip_id = $this->post('clip_id');
        $method_id = $this->post('method_id');
        if (!$this->get('provider') || !$clip_id) {
            $this->response(NULL, 404);
        }
        $formats = $this->deliveryoptions_model->get_delivery_formats($clip_id, $method_id);
        if ($formats) {
            $data['data'] = $formats;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function save_order_post()
    {

        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }

        $order_id = false;
        $order = preg_replace('/\\\\/im', '', $this->post('order'));

        //$order=stripslashes($this->post('order'));
        //Fapi::debugLog($order,true);
        $order = json_decode($order, true);

        if ($order['user'] && $order['items']) {
            $this->load->model('invoices_model');
            $order_data = $this->invoices_model->save_invoice($this->get('provider'), $order);
            if (is_array($order['items'])) {
                foreach ($order['items'] as $item) {
                    $clip_id = $item['id'];
                    //$this->clips_model->ClipLogger( $clip_id, $this->get('provider'), $order[ 'user' ], Clips_model::CLIP_ACTION_ORDERED );
                }
            }
        } else {
            $this->response(NULL, 404);
        }
        if ($order_data) {
            $order = $this->invoices_model->get_order($order_data['id']);
            $this->invoices_model->make_pdf($order, true);
            $data['data'] = $order_data;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function save_transaction_post()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $transaction = json_decode($this->post('transaction'), true);
        $transaction_id = $this->invoices_model->save_transaction($transaction);
        if ($transaction_id) {
            $data['data'] = $transaction_id;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function is_approved_post()
    {
        $data = $this->input->post();

        $this->load->model('invoices_model');

        $this->response(json_encode($this->invoices_model->is_approved($data['order_id'])), 200);

    }


    function get_orders_post()
    {
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $this->load->model('clipbins_model');
        $invoices = $this->invoices_model->get_invoices_by_client_login($this->post('user'));
        $archives = $this->clipbins_model->get_previews_archive($this->post('user'));
        if ($invoices || $archives) {
            $data['data']['orders'] = $invoices;
            $data['data']['archives'] = $archives;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_order_post()
    {
        if (!$this->get('provider') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get_invoice_by_client_login($this->post('user'), $this->post('order_id'));
        if ($invoice) {
            $data['data'] = $invoice;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_order_client_post()
    {
        if (!$this->get('provider') || !$this->post('order_id') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $hasOrder = $this->invoices_model->has_order($this->post('order_id'), $this->post('user'));
        if (!$hasOrder) {
            $this->response(NULL, 404);
        }
        $order = $this->invoices_model->get_order($this->post('order_id'));
        if ($order) {
            $data['data'] = $order;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function has_order_post()
    {
        if (!$this->post('order_id') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $has_order = $this->invoices_model->has_order($this->post('order_id'), $this->post('user'));
        if ($has_order) {
            $order = $this->invoices_model->get_order($this->post('order_id'));
            $has_order = $this->invoices_model->make_pdf($order, true);
            $this->response(array('has_order' => $has_order), 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function disable_downloads_access_post()
    {
        if (!$this->get('provider') || !$this->post('order_id') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $this->load->model('tokens_model');
        $invoice = $this->invoices_model->get_invoice_by_client_login($this->post('user'), $this->post('order_id'));
        if ($invoice) {
            $this->tokens_model->disable_order_tokens($this->post('order_id'));
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function cancel_order_post()
    {
        if (!$this->get('provider') || !$this->post('user') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $res = $this->invoices_model->cancel_invoice($this->post('user'), $this->post('order_id'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function approve_order_post()
    {
        if (!$this->get('provider') || !$this->post('user') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $res = $this->invoices_model->approve_invoice($this->post('user'), $this->post('order_id'), $this->post('paid'));
        $this->invoices_model->change_invoice_email_status(array($this->post('order_id')), 'Sent');
        //$this->_debugLog(__FUNCTION__.$this->post('user').'='.$this->post('order_id').'='.$this->post('paid').'='.json_encode($res));
        if ($res) {
            $this->response(array('data' => 'ok'), 200);
        } else {
            $this->response(NULL, 404);
        }
    }


    function get_downloads_post()
    {
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('download_model');
        $downloads = $this->download_model->get_downloads_by_client($this->post('user'), $this->get('provider'));
        if ($downloads) {
            $data['data'] = $downloads;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_not_uploaded_downloads_post()
    {
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('download_model');
        $downloads = $this->download_model->get_not_uploaded_downloads_by_client($this->post('user'), $this->get('provider'));
        if ($downloads) {
            $data['data'] = $downloads;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function rf_license_uses_get()
    {
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $this->load->model('pricing_model');
        $uses = $this->pricing_model->get_rf_license_uses($this->get('clip'), $this->get('provider'));
        if ($uses) {
            $data['data'] = $uses;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function duration_discounts_get()
    {
        $this->load->model('discounts_model');
        $discounts = $this->discounts_model->get_duration_discounts();
        if ($discounts) {
            $data['data'] = $discounts;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function count_discounts_get()
    {
        $this->load->model('discounts_model');
        $discounts = $this->discounts_model->get_count_discounts();
        if ($discounts) {
            $data['data'] = $discounts;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function search_filters_get()
    {
        $this->load->model('search_model');
        $filters = $this->search_model->get_search_filters($this->get('provider'));
        if ($filters) {
            $data['data'] = $filters;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function add_to_bin_login_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('clips')) {
            $this->response(NULL, 400);
        }
        $bin_id = $this->bin_model->add_items_login($this->post('user'), $this->post('clips'));
        if ($bin_id) {
            $data['data']['bin_id'] = $bin_id;
            $data['data']['items_count'] = $this->bin_model->get_clipbin_items_count($bin_id);
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function add_to_bin_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $count = $this->bin_model->guest_bin_save($this->post('guest_id'), $this->post('clips'));
            $data['data']['items_count'] = $count;
            $this->response($data, 200);
        } else {
            if (!$this->get('provider') || !$this->post('user') || !$this->post('clips')) {
                $this->response(NULL, 400);
            }
            $bin_id = $this->bin_model->add_items($this->post('user'), $this->post('clips'), $this->post('bin_id'));
            if ($bin_id) {
                $data['data']['bin_id'] = $bin_id;
                $data['data']['items_count'] = $this->bin_model->get_clipbin_items_count($bin_id);
                $this->response($data, 200);
            } else {
                $this->response(NULL, 404);
            }
        }
    }

    function get_guest_bin_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $bin = $this->bin_model->guest_bin_get($this->post('guest_id'));
            $data['data']['clipbin_serialized'] = $bin;
            $this->response($data, 200);
        }
        $this->response(NULL, 404);
    }

    function set_guest_data_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $ses = $this->bin_model->set_guest_data($this->post('guest_id'), $this->post('ses'));
            $data['data']['other'] = $ses;
            $this->response($data, 200);
        }
        $this->response(NULL, 404);
    }

    function get_guest_data_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $ses = $this->bin_model->get_guest_data($this->post('guest_id'));
            $data['data']['other'] = $ses;
            $this->response($data, 200);
        }
        $this->response(NULL, 404);
    }

    function set_guest_bin_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $count = $this->bin_model->guest_bin_save($this->post('guest_id'), $this->post('clips'));
            $data['data']['items_count'] = $count;
            $this->response($data, 200);
        }
        $this->response(NULL, 404);
    }

    function remove_from_bin_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $count = $this->bin_model->guest_bin_save($this->post('guest_id'), $this->post('clips'));
            $data['data']['items_count'] = $count;
            $this->response($data, 200);
        } else {
            if (!$this->get('provider') || !$this->post('clips') || !$this->post('bin_id')) {
                $this->response(NULL, 400);
            }
            $res = $this->bin_model->remove_items($this->post('clips'), $this->post('bin_id'));
            if ($res) {
                $data['data']['items_count'] = $this->bin_model->get_clipbin_items_count($this->post('bin_id'));
                $this->response($data, 200);
            } else {
                $this->response(NULL, 404);
            }
        }
    }

    function remove_all_from_bin_post()
    {
        $this->load->model('bin_model');
        if ($this->post('guest_id')) {
            $count = $this->bin_model->guest_bin_save($this->post('guest_id'));
            $data['data']['items_count'] = $count;
            $this->response($data, 200);
        } else {
            if (!$this->get('provider') || !$this->post('bin_id')) {
                $this->response(NULL, 400);
            }
            $res = $this->bin_model->remove_all_items($this->post('bin_id'));
            if ($res) {
                $data['data']['items_count'] = $this->bin_model->get_clipbin_items_count($this->post('bin_id'));
                $this->response($data, 200);
            } else {
                $this->response(NULL, 404);
            }
        }
    }

    function clipbin_content_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || (!$this->post('clipbin_clips') && !$this->post('bin_id'))) {
            $this->response(NULL, 400);
        }
        $perpage = $this->post('perpage');
        $page = $this->post('page');
        $perpage = (empty($perpage)) ? 99 : $perpage;
        $page = (empty($page)) ? 1 : $page;
        if ($this->post('clipbin_clips') && !$this->post('bin_id')) {
            $bins = $this->bin_model->get_items_by_ids($this->post('clipbin_clips'), $this->post('sort'), $perpage, $page);
        } else {
            if (isset($_REQUEST['sequence_id']) && !empty($_REQUEST['sequence_id'])) {
                $bins = $this->bin_model->get_items_sequence($this->post('bin_id'), $this->post('sort'), $perpage, $page);
            } else {
                $bins = $this->bin_model->get_items($this->post('bin_id'), $this->post('sort'), $perpage, $page);
            }
        }

        if ($bins) {
            $this->load->model('users_model');
            $data['data'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_bin_items_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $data['data'] = $this->bin_model->get_bin_items($this->post('bin_id'));
        $this->response($data, 200);
    }

    function default_bin_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $bin = $this->bin_model->get_defaul_bin($this->post('user'));
        if ($bin) {
            $data['data'] = $bin;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_bins_list_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $bins = $this->bin_model->get_bins_list($this->post('user'));
        if ($bins) {
            $data['data'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_widget_bins_list_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $bins = $this->bin_model->get_widget_bins_list($this->post('user'), $this->post('keyword'));
        if ($bins) {
            $data['data'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_widget_folders_list_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $folders = $this->bin_model->get_widget_folders_list($this->post('user'), $this->post('keyword'));
        if ($folders) {
            $data['data'] = $folders;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    /**
     * Update users foldes expanded flag value
     */
    public function update_widget_folders_expanded_flag_post()
    {
        $login = $this->post('login');

        // $login is required
        if (empty($login)) {
            $this->response(null, 400);
        }

        $expanded = $this->post('expanded');
        $folderId = $this->post('folder_id');
        $allFoldersUpdateFlag = (bool) $this->post('apply_to_all');

        $this->load->model('bin_model');

        $response = false;
       if ($allFoldersUpdateFlag) {
            // update all user folders
           $response = $this->bin_model->updateAllUsersFoldersExpandedFlag($login, $expanded);
        } elseif ($folderId) {
           // update particular folder
           $response = $this->bin_model->updateFolderExpandedFlag($login, $folderId, $expanded);
       } else {
            // no fodlerId, not allFolderUpdateFlag => bad request
            $this->response(null, 400);
        }

        $this->response(compact('response'), 200);
    }

    function create_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('title')) {
            $this->response(NULL, 400);
        }
        $clipbin_id = $this->bin_model->create_clipbin($this->post('user'), $this->post('title'), $this->post('folder_id'));
        if ($clipbin_id) {
            $data['data']['clipbin_id'] = $clipbin_id;
            $bins = $this->bin_model->get_widget_bins_list($this->post('user'));
            if ($bins)
                $data['data']['clipbins_list'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function save_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $clipbin_id = $this->bin_model->save_clipbin($this->post('user'), $this->post('title'), $this->post('folder_id'), $this->post('bin_id'));
        if ($clipbin_id) {
            $data['data']['clipbin_id'] = $clipbin_id;
            $bins = $this->bin_model->get_widget_bins_list($this->post('user'));
            if ($bins)
                $data['data']['clipbins_list'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function create_folder_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('name')) {
            $this->response(NULL, 400);
        }
        $folder_id = $this->bin_model->create_folder($this->post('user'), $this->post('name'));
        if ($folder_id) {
            $data['data']['folder_id'] = $folder_id;
            $folders = $this->bin_model->get_widget_folders_list($this->post('user'));
            if ($folders)
                $data['data']['folders_list'] = $folders;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function save_folder_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('folder_id')) {
            $this->response(NULL, 400);
        }
        $folder_id = $this->bin_model->save_folder($this->post('user'), $this->post('name'), $this->post('folder_id'));
        if ($folder_id) {
            $data['data']['folder_id'] = $folder_id;
            $folders = $this->bin_model->get_widget_folders_list($this->post('user'));
            if ($folders)
                $data['data']['folders_list'] = $folders;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_preview_post()
    {
        if (!$this->post('clip_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('clips_model');
        $res = $this->clips_model->get_preview_data($this->post());
        if ($res) {
            $this->response($res, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function rename_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->post('title') || !$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->rename_clipbin($this->post('title'), $this->post('bin_id'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function make_clipbin_default()
    {
        $this->load->model('bin_model');
        if (!$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $this->bin_model->set_default_bin($this->post('bin_id'));
    }

    function copy_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('user') || !$this->post('title') || !$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $clipbin_id = $this->bin_model->copy_clipbin($this->post('user'), $this->post('title'), $this->post('bin_id'));
        if ($clipbin_id) {
            $data['data']['clipbin_id'] = $clipbin_id;
            $bins = $this->bin_model->get_bins_list($this->post('user'));
            if ($bins)
                $data['data']['clipbins_list'] = $bins;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function preview_download_rating_post()
    {

        $clipId = $_REQUEST['clip_id'];
        $userid = $_REQUEST['user_id'];
        $this->db_master->query("insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $userid . "','preview_download','Preview Download Rating','" . $clipId . "','" . $this->get_setting('previewDownload') . "')");
        $this->response('Data Added Succesfully', 200);
    }

    function delete_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->post('bin_id')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->delete_clipbin($this->post('bin_id'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function delete_folder_post()
    {
        $this->load->model('bin_model');
        if (!$this->post('folder_id')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->delete_folder($this->post('folder_id'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function move_clipbin_items_post()
    {
        $this->load->model('bin_model');
        if (!$this->post('from_bin_id') && !$this->post('to_bin_id') && !$this->post('items_ids')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->move_clipbin_items($this->post('from_bin_id'), $this->post('to_bin_id'), $this->post('items_ids'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function copy_clipbin_items_post()
    {
        $this->load->model('bin_model');
        if (!$this->post('from_bin_id') && !$this->post('to_bin_id') && !$this->post('items_ids')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->copy_clipbin_items($this->post('from_bin_id'), $this->post('to_bin_id'), $this->post('items_ids'));
        if ($res) {
            $this->response(NULL, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_clipbin_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('bin_id')) {
            $this->response(NULL, 400);
        }

        if ($_REQUEST['sequence_id']) {
            $res = $this->bin_model->get_clipbin_sequence($this->post('bin_id'));
        } else {
            $res = $this->bin_model->get_clipbin($this->post('bin_id'));
        }

        if ($res) {
            $data['data'] = $res[0];
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_folder_post()
    {
        $this->load->model('bin_model');
        if (!$this->get('provider') || !$this->post('folder_id')) {
            $this->response(NULL, 400);
        }
        $res = $this->bin_model->get_folder($this->post('folder_id'));
        if ($res) {
            $data['data'] = $res[0];
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function provider_galleries_get()
    {
        $this->load->model('galleries_model');
        $this->load->model('clips_model');
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $filter = array('client_id' => $this->get('user_id'));
        if ($this->get('featured')) {
            $filter['featured'] = 1;
        }
        $filter['is_gallery'] = 1;
        $res = $this->galleries_model->get_galleries_list($filter);
        $cloud_tags = $this->clips_model->provider_cloud_tags($filter['client_id']);
        if ($res || $cloud_tags) {
            $data['data'] = $res;
            $data['cloud_tags'] = $cloud_tags;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function provider_gallery_get()
    {
        if (!$this->get('gallery_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('galleries_model');
        $res = $this->galleries_model->get_gallery($this->get('gallery_id'));
        if ($res) {
            $data['data'] = $res;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function top_keywords_post()
    {
        $this->load->model('tagcloud_model');
        if (!$this->get('provider')) {
            $this->response(NULL, 400);
        }
        $keywords = $this->tagcloud_model->get_top_keywords($this->get('provider'), $this->post('limit'));
        if ($keywords) {
            $data['data'] = $keywords;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function shared_pages_post()
    {
        $this->load->model('sharedpages_model');
        $filter = array('status' => 1);
        if ($this->post('type'))
            $filter['type'] = $this->post('type');

        $pages = $this->sharedpages_model->get_shared_pages_list($filter, array(), 'sort, id desc');
        if ($pages) {
            $data['data'] = $pages;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function shared_pages_types_get()
    {
        $this->load->model('sharedpages_model');
        $types = $this->sharedpages_model->get_shared_pages_types();
        if ($types) {
            $data['data'] = $types;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function shared_page_post()
    {
        if (!$this->post('url')) {
            $this->response(NULL, 400);
        }
        $this->load->model('sharedpages_model');
        $page = $this->sharedpages_model->get_shared_page_by_url($this->post('url'));
        if ($page) {
            $data['data'] = $page;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }

    }

    function get_frontends_post()
    {
        if (!$this->post('provider_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('frontends_model');
        $frontends = $this->frontends_model->get_frontends_list_with_providers(array(
            'f.provider_id' => $this->post('provider_id'),
            'f.status' => 1
        ));
        if ($frontends) {
            $data['data'] = $frontends;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function download_token_path_post()
    {
        if (!$this->get('provider') || !$this->post('token')) {
            $this->response(NULL, 400);
        }
        $this->load->model('tokens_model');
        $path = $this->tokens_model->get_token_path($this->post('token'));
        if ($path) {
            $data['data'] = $path;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function upload_token_path_post()
    {
        if (!$this->get('provider') || !$this->post('token')) {
            $this->response(NULL, 400);
        }
        $this->load->model('upload_tokens_model');
        $path = $this->upload_tokens_model->get_token_path($this->post('token'));
        if ($path) {
            $data['data'] = $path;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function use_discount_display_post()
    {
        if (!$this->get('provider') || !$this->post('use')) {
            $this->response(NULL, 400);
        }
        $this->load->model('pricing_model');
        $discount_display = $this->pricing_model->get_use_discount_display($this->post('use'));
        if ($discount_display) {
            $data['data'] = $discount_display;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function rf_use_discount_display_post()
    {
        if (!$this->get('provider') || !$this->post('use')) {
            $this->response(NULL, 400);
        }
        $this->load->model('pricing_model');
        $discount_display = $this->pricing_model->get_rf_use_discount_display($this->post('use'));
        if ($discount_display) {
            $data['data'] = $discount_display;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    private function _limit()
    {
        if ($limit = $this->get('limit')) {
            return ' LIMIT ' . (($this->get('from')) ? ($this->get('from') . ', ') : '') . $limit;
        } else {
            return '';
        }
    }

    private function _clip_id($url)
    {
        if (!$url) {
            return 0;
        }
        $uri_parts = explode('-', $url);
        return (count($uri_parts) ? intval($uri_parts[count($uri_parts) - 1]) : 0);
    }

    private function _category_id($url)
    {
        if (!$url) {
            return 0;
        }
        $uri_parts = explode('-', $url);
        return (count($uri_parts) ? intval($uri_parts[count($uri_parts) - 1]) : 0);
    }

    private function get_user_token($login, $password)
    {
        $this->db_master = $this->load->database('master', TRUE);
        $token = '';
        $query = $this->db->query('SELECT id,last_login,token FROM lib_users WHERE login = ' . $this->db->escape($login)
            . ' AND password = ' . $this->db->escape($password) . ' AND active = 1');
        $user = $query->result_array();
        $login_time_update = (strtotime($user[0]['last_login']) < strtotime(time() - 172800));
        if ($user[0]['id'] && $login_time_update && empty($user[0]['token'])) {
            $token = md5(uniqid() . microtime() . rand());
            $this->db_master->where('id', $user[0]['id']);
            $this->db_master->update('lib_users', array('token' => $token));
        } else $token = $user[0]['token'];
        return $token;
    }

    /**
     * Сохранение и отправка провайдеру сообщения от пользователя
     */
    function send_clip_comment_post()
    {
        $provider_id = $this->get('provider');
        $user_login = $this->post('user');
        $mesaage = $this->post('message');
        $clip_id = $this->post('clip_id');
        $this->clips_model->SendCommentToProvider($provider_id, $user_login, $mesaage, $clip_id);
        $this->clips_model->SaveCommentToLog($provider_id, $user_login, $mesaage, $clip_id);
    }

    function update_resume_billing_data_post()
    {

        $this->load->model('licensing_model');

        $order_id = $_POST['order_id'];
        $license_data = json_decode($_POST['data']);

        if (isset($license_data->bill_name)) {
            $data['name'] = $license_data->bill_name;
        } else {
            $data['name'] = '';
        }


        if (isset($license_data->bill_company)) {
            $data['company'] = $license_data->bill_company;
        } else {
            $data['company'] = '';
        }

        if (isset($license_data->bill_street1)) {
            $data['street1'] = $license_data->bill_street1;
        } else {
            $data['street1'] = '';
        }


        if (isset($license_data->bill_street2)) {
            $data['street2'] = $license_data->bill_street2;
        } else {
            $data['street2'] = '';
        }


        if (isset($license_data->bill_city)) {
            $data['city'] = $license_data->bill_city;
        } else {
            $data['city'] = '';
        }

        if (isset($license_data->bill_state)) {
            $data['state'] = $license_data->bill_state;
        } else {
            $data['state'] = '';
        }

        if (isset($license_data->bill_zip)) {
            $data['zip'] = $license_data->bill_zip;
        } else {
            $data['zip'] = '';
        }

        if (isset($license_data->bill_country)) {
            $data['country'] = $license_data->bill_country;
        } else {
            $data['country'] = '';
        }

        if (isset($license_data->bill_phone)) {
            $data['phone'] = $license_data->bill_phone;
        } else {
            $data['phone'] = '';
        }

        $this->licensing_model->update_billing_by_order_id($order_id, $data);

        $this->response(array('status' => 'ok'), 200);

    }

    function update_resume_license_data_post()
    {

        $this->load->model('licensing_model');

        $order_id = $_POST['order_id'];
        $license_data = json_decode($_POST['data']);

        $data = array();

        if (isset($license_data->production_title)) {
            $data['production_title'] = $license_data->production_title;
        } else {
            $data['production_title'] = '';
        }

        if (isset($license_data->production_description)) {
            $data['production_description'] = $license_data->production_description;
        } else {
            $data['production_description'] = '';
        }

        if (isset($license_data->production_territory)) {
            $data['production_territory'] = $license_data->production_territory;
        } else {
            $data['production_territory'] = '';
        }


        if (isset($license_data->additional_notes)) {
            $data['additional_notes'] = $license_data->additional_notes;
        } else {
            $data['additional_notes'] = '';
        }

        if (isset($license_data->lic_name)) {
            $data['name'] = $license_data->lic_name;
        } else {
            $data['name'] = '';
        }


        if (isset($license_data->lic_company)) {
            $data['company'] = $license_data->lic_company;
        } else {
            $data['company'] = '';
        }

        if (isset($license_data->lic_street1)) {
            $data['street1'] = $license_data->lic_street1;
        } else {
            $data['street1'] = '';
        }


        if (isset($license_data->lic_street2)) {
            $data['street2'] = $license_data->lic_street2;
        } else {
            $data['street2'] = '';
        }


        if (isset($license_data->lic_city)) {
            $data['city'] = $license_data->lic_city;
        } else {
            $data['city'] = '';
        }

        if (isset($license_data->lic_state)) {
            $data['state'] = $license_data->lic_state;
        } else {
            $data['state'] = '';
        }

        if (isset($license_data->lic_zip)) {
            $data['zip'] = $license_data->lic_zip;
        } else {
            $data['zip'] = '';
        }

        if (isset($license_data->lic_country)) {
            $data['country'] = $license_data->lic_country;
        } else {
            $data['country'] = '';
        }

        if (isset($license_data->lic_phone)) {
            $data['phone'] = $license_data->lic_phone;
        } else {
            $data['phone'] = '';
        }

        $this->licensing_model->update_license_by_order_id($order_id, $data);

        $this->response(array('status' => 'ok'), 200);
    }

    function get_resume_billing_data_post()
    {
        $order_id = $this->post('order_id');

        $this->load->model('licensing_model');

        $data = json_encode($this->licensing_model->get_order_billing_info($order_id));

        $this->response($data, 200);
    }


    function send_form_contactus_post()
    {
        $this->load->model('formdata_model');
        $data = $this->_get_post_data(
            array(
                "firstname", "lastname", "companyname", "phone",
                "email", "inquiry", "user_id", "user_login", "timestamp"
            )
        );
        $data['provider_id'] = $this->get('provider');
        $data['frontend_id'] = $this->get('frontend');
        $this->formdata_model->send_contactus_notification($this->get('provider'), $data);
        $this->formdata_model->save_form_request('lib_formdata_contactus', $data);
        $this->response(array('status' => 'ok'), 200);
    }

    function send_form_shotrequest_post()
    {
        $this->load->model('formdata_model');
        $data = $this->_get_post_data(
            array(
                "firstname", "lastname", "companyname", "companytype",
                "jobtitle", "state", "phone", "website",
                "email", "production_description", "footage_details",
                "format", "preview_deadline", "master_deadline",
                "license", "budget", "user_id", "user_login", "timestamp"
            )
        );
        $data['provider_id'] = $this->get('provider');
        $data['frontend_id'] = $this->get('frontend');
        $this->formdata_model->send_shotrequest_notification($this->get('provider'), $data);
        $this->formdata_model->save_form_request('lib_formdata_shotrequest', $data);
        $this->response(array('status' => 'ok'), 200);
    }

    function _get_post_data(array $list)
    {
        $result = array();
        foreach ($list as $field) {
            $result[$field] = $this->post($field);
        }
        return $result;
    }

    function get_wp_userdata_post()
    {
        $this->load->model('users_model');
        $response = array(
            'status' => 'not-found'
        );
        $query = array(
            'login' => $this->post('username'),
            'password' => addslashes(preg_replace('/\\\\/i', '', $this->input->post('password'))),
            'active' => 1
        );
        $userdata = $this->users_model->get_wp_userdata($query);
        $metadata = $otherdata = array();
        if ($userdata['id']) {
            $metadata = $this->users_model->get_wp_user_metadata($userdata['id'], $this->get('provider'));
            $otherdata = $this->users_model->get_wp_user_otherdata($userdata['id']);
            unset($userdata['id']);
        }
        if ($userdata) {
            $response = array(
                'status' => 'ok',
                'userdata' => $userdata,
                'metadata' => $metadata,
                'otherdata' => $otherdata
            );
        }
        $this->response($response, 200);
    }


    function get_license_term_restrictions_post()
    {
        if (!$this->input->post('id')) {
            $this->response(NULL, 400);
        }
        $id = $this->input->post('id');
        $this->load->model('licensing_model');
        $restrictions = $this->licensing_model->get_restrictions($id, '-1');
        //$this->_debugLog('RESTRACTIONS: '.json_encode($restrictions),true);
        $data = array('status' => 'ok', 'data' => $restrictions);
        $this->response($data, 200);

    }

    function update_wp_userdata_post()
    {
        $this->load->model('users_model');
        $data = array();
        if ($this->post('first_name') != null) {
            $data['fname'] = $this->post('first_name');
        }
        if ($this->post('first_name') != null) {
            $data['lname'] = $this->post('last_name');
        }
        if ($this->post('email') != null) {
            $data['email'] = $this->post('email');
        }
        if ($this->post('url') != null) {
            $data['site'] = $this->post('url');
        }
        if ($this->post('meta') != null) {
            $data['meta'] = $this->post('meta');
        }
        if ($this->post('is_provider') != null) {
            $data['is_provider'] = $this->post('is_provider');
        }
        $password = preg_replace('/\\\\/im', '', $this->input->post('password'));
        if ($password && !empty($password)) {
            $data['password'] = $password;
        }
        $this->users_model->update_wp_userdata($this->post('username'), $data);
        $response = array(
            'status' => 'ok'
        );
        $this->response($response, 200);
    }

    function update_wp_meta_post()
    {
        if (!$this->post('username')) {
            $this->response(NULL, 400);
        }
        $this->load->model('users_model');
        $data = array(
            'meta' => $this->post('meta'),
            'is_provider' => $this->post('is_provider')
        );
        $this->users_model->update_wp_userdata($this->post('username'), $data);
        $response = array(
            'status' => 'ok'
        );
        $this->response($response, 200);
    }

    function username_exists_post()
    {
        if (!$this->post('username')) {
            $this->response(NULL, 400);
        }
        $this->load->model('users_model');
        $res = $this->users_model->is_username_exists($this->post('username'));
        $data['data'] = $res;
        $this->response($data, 200);
    }

    function email_exists_post()
    {
        if (!$this->post('email')) {
            $this->response(NULL, 400);
        }
        $this->load->model('users_model');
        $res = $this->users_model->is_email_exists($this->post('email'));
        $data['data'] = $res;
        $this->response($data, 200);
    }

    function generate_download_link_post()
    {
        if (!$this->get('provider') || !$this->post('user') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $hasOrder = $this->invoices_model->has_order($this->post('order_id'), $this->post('user'));
        if ($hasOrder) {
            $data['link'] = $this->invoices_model->get_invoice_guest_download_link($this->post('order_id'));
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function delete_download_link_post()
    {
        if (!$this->get('provider') || !$this->post('user') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $hasOrder = $this->invoices_model->has_order($this->post('order_id'), $this->post('user'));
        if ($hasOrder) {
            $this->invoices_model->delete_download_token_by_order_id($this->post('order_id'));
            $this->response(array(), 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function user_storage_account_post()
    {
        if (!$this->get('provider') || !$this->post('user')) {
            $this->response(NULL, 400);
        }
        $this->load->model('users_model');
        $res = $this->users_model->get_storage_account($this->post('user'));
        if ($res) {
            $data['data'] = $res;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function upload_notification_post()
    {
        if (!$this->post('user') || !$this->post('paths')) {
            $this->response(NULL, 400);
        }
        $this->load->model('file_manager_model');
        $this->file_manager_model->send_upload_notification($this->post('user'), $this->post('paths'), $this->post('destination_root'));
        $this->response(array(), 200);
    }

    function users_list_post()
    {
        $id = (!$this->post('group_id')) ? 13 : $this->post('group_id');
        $this->load->model('users_model');
        $users = $this->users_model->getUsersListByGroupId($id, $this->post('latter'));
        if ($users) {
            $data['data'] = $users;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_user_by_id_post()
    {
        if (!$this->post('id')) $this->response(NULL, 404);
        $id = $this->post('id');
        $this->load->model('users_model');
        $user = $this->users_model->get_user($id);
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_user_by_login_post()
    {
        if (!$this->post('login')) $this->response(NULL, 404);
        $login = $this->post('login');
        $this->load->model('users_model');
        $user = $this->users_model->GetUserByLogin($login);
        //$this->_debugLog(__FUNCTION__.$login.json_encode($user))
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_user_by_email_post()
    {
        if (!$this->post('email')) $this->response(NULL, 404);
        $email = $this->post('email');
        $this->load->model('users_model');
        $user = $this->users_model->GetUserByEmail($email);
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function save_user_activation_key_post()
    {
        if (!$this->post('login')) $this->response(NULL, 404);
        if (!$this->post('activation_key')) $this->response(NULL, 404);
        if (!$this->post('frontend_url')) $this->response(NULL, 404);
        $this->load->model('users_model');
        $user = $this->users_model->SaveActivationKey($this->post('login'), $this->post('activation_key'), $this->post('frontend_url'));
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function get_user_activation_key_post()
    {
        if (!$this->post('login')) $this->response(NULL, 404);
        if (!$this->post('activation_key')) $this->response(NULL, 404);
        $this->load->model('users_model');
        $user = $this->users_model->GetUserByActivationKey($this->post('login'), $this->post('activation_key'));
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function reset_user_pass_post()
    {
        /*if(!$this->post('login'))  $this->response(NULL, 404);
        if(!$this->post('pass'))  $this->response(NULL, 404);*/
        if (!$this->post('params')) $this->response(NULL, 404);
        $params = $this->input->post('params');
        $login = $params['login'];
        $pass = $params['pass'];
        $this->load->model('users_model');
        $user = $this->users_model->ResetUserPass($login, $pass);
        if ($user) {
            $data['data'] = $user;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function test_post()
    {
        $id = (int)$this->post('clipId');
        $weight = (int)$this->post('weight');
        $action = $this->post('action');
        $this->load->model('rank_clips_model');
        $this->rank_clips_model->set_rank($id, $weight, $action);
        $data['data'] = $_POST;
        $this->response($data, 200);
    }

    function getRank_post()
    {
        $id = $this->post('clipId');

        $this->load->model('rank_clips_model');
        $res = $this->rank_clips_model->get_rank($id);
        $data['data'] = $res;
        $this->response($data, 200);
    }

    function setRank_post()
    {
        $id = (int)$this->post('clipId');
        $weight = (int)$this->post('weight');
        $action = $this->post('action');
        $this->load->model('rank_clips_model');
        $this->rank_clips_model->set_rank($id, $weight, $action);
        $data['data'] = $id . $weight . $action;
        $this->response($data, 200);
    }

    function clip_code_by_id_post()
    {
        if (!$this->post('id')) {
            $this->response(NULL, 400);
        }
        $code = $this->clips_model->get_clip_code((int)$this->post('id'));
        if ($code) {
            $data['data'] = $code;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function clip_id_by_code_post()
    {
        if (!$this->post('code')) {
            $this->response(NULL, 400);
        }
        $id = $this->clips_model->get_clip_id_by_code($this->post('code'));
        if ($id) {
            $data['data'] = $id;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }


    private function get_clip_id_from_post()
    {
        if (!$this->post('id') && !$this->post('code')) {
            return false;
        }
        if($this->post('id')){
            $clip_id = $this->post('id');
        } elseif ($this->post('code')){
            $clip_id = $this->clips_model->get_clip_id_by_code($this->post('code'));
        }

        if(isset($clip_id))
            return (int)$clip_id;

        return false;
    }

    private function get_thumb_from_post($clip_id)
    {
        if($this->post('place')){
            return $this->clips_model->get_clip_thumb($clip_id, $this->post('place'));
        } else {
            return $this->clips_model->get_clip_thumb($clip_id);
        }
    }

    function create_thumb_post()
    {
        if (!$clip_id = $this->get_clip_id_from_post()) {
            $this->response(NULL, 400);
        }

        if(!$thumb = $this->get_thumb_from_post($clip_id)){
            $thumb = $this->clips_model->create_browse_page_thumb($clip_id);
        }

        return $this->make_response($thumb);
    }

    /**
     * @param mixed $response_data
     */
    private function make_response($response_data)
    {
        if ($response_data) {
            $data['data'] = $response_data;
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function clip_thumb_post()
    {
        if (!$clip_id = $this->get_clip_id_from_post()) {
            $this->response(NULL, 400);
        }

        $thumb = $this->get_thumb_from_post($clip_id);

        return $this->make_response($thumb);
    }

    function send_registercp_post()
    {
        if (!$this->get('provider') || !$this->post('user') || !$this->post('order_id')) {
            $this->response(NULL, 400);
        }
        $this->load->model('invoices_model');
        $hasOrder = $this->invoices_model->has_order($this->post('order_id'), $this->post('user'));
        if ($hasOrder) {
            $this->invoices_model->delete_download_token_by_order_id($this->post('order_id'));
            $this->response(array(), 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function add_clip_to_solr_post()
    {
        if (!$this->post('clip_id')) {
            $this->response(NULL, 400);
        }
        $this->clips_model->add_to_index($this->post('clip_id'));
        $this->response(array('data' => 'ok'), 200);
    }

    function send_email_post()
    {
        if (!$this->post('action')) {
            $this->response(NULL, 404);
        }
        //Fapi::debugLog(__FUNCTION__.' -> '.$this->post('action'));
        $this->load->model('invoices_model');
        switch ($this->post('action')) {
            case 'download-email':
                if (!$this->post('order_id')) $this->response(NULL, 404);
                $this->invoices_model->send_order_download($this->post('order_id'), true);
                $this->response(array('data' => 'ok'), 200);
                break;
            case 'clipbin':
                if (!$this->post('to')) $this->response(NULL, 404);
                //$this->_debugLog(__FUNCTION__.' -> '.$this->post('to').'->'.$this->post('body'));
                $this->invoices_model->send_clipbin($this->post('to'), $this->post('body'));
                $this->response(array('data' => 'ok'), 200);
                break;
            case 'submission-finished':
                if (!$this->post('code') || !$this->post('provider_id')) $this->response(NULL, 404);
                $this->load->model('submissions_model');
                $this->submissions_model->SendNotificationProvider_NewSubmission($this->post('code'), $this->post('provider_id'));
                $this->response(array('data' => 'ok'), 200);
                break;
            case 'isset-offline-clips':
                if (!$this->post('provider_id')) $this->response(NULL, 404);
                $this->load->model('users_model');
                $this->users_model->sendOfflineClips($this->post('provider_id'));
                $this->response(array('data' => 'ok'), 200);
                break;
            case 'preapproved':
                if (!$this->post('order_id')) $this->response(NULL, 404);
                //$this->invoices_model->send_order_download($this->post('order_id'), true);
                $this->response(array('data' => 'ok'), 200);
                break;
            case 'register-user':
                if (!$this->post('user_id') || !$this->post('user_data')) $this->response(NULL, 404);
                $this->load->model('register_model');
                $ret = $this->register_model->register_email($this->post('user_id'), $this->post('user_data'));
                $this->response(array('data' => $ret), 200);
                break;
            case 'email-reset':
                if (!$this->post('user_login') || !$this->post('user_email') ) $this->response(NULL, 404);
                $this->load->model('users_model');
                $this->users_model->sendEmailChanged($this->post('user_login'), $this->post('user_email'));
                break;
            case 'password-reset':
                if (!$this->post('user_login') ) $this->response(NULL, 404);
                $this->load->model('users_model');
                $this->users_model->sendPasswordChanged($this->post('user_login'));
                break;
            default:
                if (!$this->post('name_data') || !$this->post('data') || !$this->post('to_email')) $this->response(NULL, 404);
                $this->load->model('invoices_model');
                $this->invoices_model->send_custom_email($this->post('action'), $this->post('to_email'), $this->post('name_data'), $this->post('data'));
                $this->response(array('data' => 'ok'), 200);
                break;
        }

    }

    function is_lab_user_post()
    {
        $user_login = $this->post('login');

        $result = $this->db->query("SELECT group_id FROM lib_users WHERE login = '" . $user_login . "' LIMIT 1")->result_array();
        $group_id = $result[0]['group_id'];

        if ($group_id == 14) {
            $this->response(array('data' => true), 200);
        } else {
            $this->response(array('data' => false), 200);
        }
    }

    function clipbin_set_active_post()
    {
        $bin_id = $_POST['id'];
        $bin_owner = $this->db->query("SELECT client_id FROM lib_lb WHERE id = '" . $bin_id . "' LIMIT 1")->result_array();
        $own = $bin_owner[0]['client_id'];
        $this->db_master->query("UPDATE lib_lb SET is_default = '0' WHERE client_id = '" . $own . "' ");
        $this->db_master->query("UPDATE lib_lb SET is_default = '1' WHERE id = '" . $bin_id . "' ");

        $this->response(array('data' => true), 200);
    }

    function clips_count_post()
    {
        $this->load->model('clips_model', 'cl');
        $count = $this->cl->get_clips_count();

        echo json_encode($count);
    }

    function update_cart_post()
    {
        $user_login = $this->input->post('user_login');
        $guest_id = $this->input->post('guest_id');
        $cart = $this->input->post('cart');
        //Fapi::debugLog($cart, true);
        if ($user_login) {
            $this->load->model('users_model', 'um');
            $user = $this->um->GetUserByLogin($user_login);

            $q = "  INSERT INTO lib_cart_items (id, user_id, cart_serialized)
                            VALUES ('', '" . $user['id'] . "', '" . $cart . "')
                            ON DUPLICATE KEY UPDATE cart_serialized = '" . $cart . "'
                        ";
            $this->db_master->query($q);
            $this->response(array('data' => $q), 200);
        } elseif ($guest_id) {
            $this->load->model('cart_model');
            $this->cart_model->guest_cart_save($guest_id, $cart);
            $this->response(array('data' => $cart), 200);
        }
    }

    function get_user_cart_post()
    {
        $user_login = $this->input->post('user_login');
        $guest_id = $this->input->post('guest_id');
        if ($user_login) {
            $this->load->model('users_model', 'um');
            $user = $this->um->GetUserByLogin($user_login);
            $cart = $this->db->query("SELECT cart_serialized FROM lib_cart_items WHERE user_id = '" . $user['id'] . "' LIMIT 1 ")->result_array();
            $this->response(array('data' => $cart), 200);
        } elseif ($guest_id) {
            $cart = $this->db->query("SELECT cart_serialized FROM lib_guests WHERE guest_id = '" . $guest_id . "' LIMIT 1 ")->result_array();
            $this->response(array('data' => $cart), 200);
        }
    }

    function merge_carts_post()
    {
        $user_login = $this->input->post('user_login');
        $guest_id = $this->input->post('guest_id');
        $user_cart = $this->input->post('cart');
        if ($user_login) {
            $this->load->model('users_model', 'um');
            $user = $this->um->GetUserByLogin($user_login);
            $cart = $this->db->query("SELECT cart_serialized FROM lib_cart_items WHERE user_id = '" . $user['id'] . "' LIMIT 1 ")->result_array();
        } elseif ($guest_id) {
            $cart = $this->db->query("SELECT cart_serialized FROM lib_guests WHERE guest_id = '" . $guest_id . "' LIMIT 1 ")->result_array();
        }
        $cartu = unserialize($user_cart);
        $cartClear = array();
        if (!empty($cartu)) {
            foreach ($cartu as $k => $item)
                $cartClear[$item['id']] = array(
                    'id' => $item['id'],
                    'title' => 'Clip ID ' . $item['code'],
                    'quantity' => 1,
                );
        }
        $cart = unserialize($cart[0]['cart_serialized']);
        if (!empty($cart)) {
            foreach ($cart as $k => $item)
                if (empty($cartClear[$item['id']]))
                    $cartClear[] = array(
                        'id' => $item['id'],
                        'title' => 'Clip ID ' . $item['code'],
                        'quantity' => 1,
                    );
        }
        $new_cart = serialize($cartClear);


        /*if(unserialize($cart[0]['cart_serialized']) > 0){
            $new_cart = serialize(array_merge(unserialize($user_cart), unserialize($cart[0]['cart_serialized'])));
        }else{
            $new_cart = $user_cart;
        }*/
        if ($user_login) {
            $this->db_master->query("  INSERT INTO lib_cart_items (id, user_id, cart_serialized)
                            VALUES ('', '" . $user['id'] . "', '" . $new_cart . "')
                            ON DUPLICATE KEY UPDATE cart_serialized = '" . $new_cart . "'
                        ");
        } elseif ($guest_id) {
            $this->load->model('cart_model');
            $this->cart_model->guest_cart_save($guest_id, $new_cart);
        }


        $this->response(array('data' => $new_cart), 200);
    }

    function download_previews_cart_post()
    {
        $this->load->model('clipbins_model');
        $user_login = $this->input->post('user_login');
        $ids = $this->input->post('ids');
        $ret = $this->clipbins_model->save_previews_archive($user_login, $ids);
        if ($ret == false) $this->response(NULL, 404);
        $this->response(array('data' => json_encode($ret)), 200);
    }

    function download_previews_bin_post()
    {
        $this->load->model('clipbins_model');
        $bin_id = $this->input->post('bin_id');
        $user_login = $this->input->post('user_login');
        $ids = $this->clipbins_model->get_clipbin_items_ids($bin_id);
        $ret = $this->clipbins_model->save_previews_archive($user_login, $ids, $bin_id);
        if ($ret == false) $this->response(NULL, 404);
        $this->response(array('data' => json_encode($ret)), 200);
    }

    function download_preview_post()
    {
        $count = $this->clips_model->get_check_ip($this->post('ip'));
        if ($count)
            $clip = $this->clips_model->get_clip_info($this->post('clip_id'));
        else $clip = false;
        $popup = $this->load->view('frontends/popup_preview_download.php', array('clip' => $clip, 'count' => $count, 'referer' => $this->post('referer')), true);
        $this->response(array('data' => $popup, 'limit' => ($count)), 200);
    }

    /**
     * ge clip list by id | code
     */
    function clips_by_id_post()
    {
        $data = [];
        
        $clipsId = $this->post('clips_id');
        
        if (!empty($clipsId) && is_array($clipsId)) {

            // determine how to find, by id, or by clip code
            // if first value is string => by code, else => by id
            reset($clipsId);
            $byCode = is_string(current($clipsId));

            // escape values
            array_walk($clipsId, function (&$item) {
                $item = $this->db->escape($item);
            });

            $filter = ' AND ' . ($byCode ? 'c.code' : 'c.id') . ' IN (' . implode(',', $clipsId) . ')';

            $data['clips'] = $this->clips_model->get_clips_list('en', $filter, '', '', true);
        }
        
        if (!empty($data['clips'])) {
            // mail('imranmani.numl@gmail.com', 'Code Success', 'Code Success');
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    /**
     * get user|ip likes for requested clips ids
     */
    function clips_likes_by_user_post()
    {
        $clipsId = $this->post('clips_id');
        
        if ($clipsId) {
            $data = [];
            $this->load->model('users_model');
            $data['user_likes'] = $this->getClipsLikesByUser($clipsId);;
            $data['user'] = $this->users_model->GetUserByLogin($this->post('user_login'));
            $this->response($data, 200);
        } else {
            $this->response(NULL, 404);
        }
    }

    function ajax_functions_post()
    {

        switch ($_POST["action"]) {
            case "like":
                if (!empty($_POST["userID"])) {

                    $searchin_filter_query = $this->db->query("SELECT group_id FROM lib_users WHERE id = '" . $_POST["userID"] . "'")->result_array();
                    $resultGroup = $searchin_filter_query[0]['group_id'];

                    if ($resultGroup == 1) {
                        $this->db_master->query("insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $_POST['userID'] . "','admin_rating','Rating by Admin','" . $_POST['id'] . "','" . $this->get_setting('adminrating') . "')");
                        $admin_rating = $this->db_master->insert_id();
                        $data1 = $admin_rating;
                        $count_likes_ip = $this->db_master->query("select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')");
                        $data = $count_likes_ip->num_rows();
                    } elseif ($resultGroup == 13) {
                        $this->db_master->query("insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $_POST['userID'] . "','contributer_rating','Rating by Contributer','" . $_POST['id'] . "','" . $this->get_setting('contributerRating') . "')");
                        $admin_rating = $this->db_master->insert_id();
                        $data1 = $admin_rating;
                        $count_likes_ip = $this->db_master->query("select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')");
                        $data = $count_likes_ip->num_rows();
                    } else {
                        $this->db_master->query("insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $_POST['userID'] . "','user_rating','Rating by Registered User','" . $_POST['id'] . "','" . $this->get_setting('registeredUser') . "')");
                        $admin_rating = $this->db_master->insert_id();
                        $data1 = $admin_rating;
                        $count_likes_ip = $this->db_master->query("select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')");
                        $data = $count_likes_ip->num_rows();
                    }
                } else {
                    $value = $this->get_setting('ipRating');
                    $this->db_master->query("insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $_POST['ip'] . "','ip_rating','Rating by Non-Registered User','" . $_POST['id'] . "','" . $value . "')");

                    // $query_user = "insert into lib_clip_rating (user_id,name,description,code,weight) values('" . $ip . "','ip_rating','Rating by Non-Registered User','" . $_POST['id'] . "','" . $value . "')";
                    $ip_rating = $this->db_master->insert_id();
                    $data1 = $ip_rating;
                    // $count_likes_ip = "select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')";
                    $count_likes_ip = $this->db_master->query("select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')");
                    $data = $count_likes_ip->num_rows();
                }
                $this->response(['num_likes' => $data, 'inserted_id' => $data1]);
                break;
            case "unlike":
                $this->db_master->query("DELETE FROM lib_clip_rating WHERE id = '" . $_POST["id"] . "'");

                $count_likes_ip = $this->db_master->query("select * from lib_clip_rating where code='" . $_POST['id'] . "' and (name='ip_rating' or name='user_rating' or name='admin_rating' or name='contributer_rating')");
                $data = $count_likes_ip->num_rows();;
//            if (!empty($result)) {
//                $query = "UPDATE tutorial SET likes = likes - 1 WHERE id='" . $_POST["id"] . "' and likes > 0";
//                $result = $db_handle->updateQuery($query);
//            }
                break;
            case "updateAdminAction":

                $this->db_master->query("UPDATE lib_clips SET admin_action=" . $_POST['UpdateType'] . " WHERE id = '" . $_POST['clipId'] . "' ");

                break;
        }
        //var_dump($data1);
        return $data . "," . $data1;
    }


    function get_setting($name)
    {
        $searchin_filter_query = $this->db->query("SELECT value FROM lib_settings WHERE name = '" . $name . "'")->result_array();
        $result = $searchin_filter_query[0]['value'];
        return $result;
    }

    /**
     * zencoder video previews ready post request come here, after transcoding have finished
     */
    public function zencoder_preview_ready_post()
    {
        $json = file_get_contents('php://input');

        $responce = json_decode($json, true);

        $jobId = isset($responce['job']['id']) ? (int) $responce['job']['id'] : -1;
        $state = isset($responce['job']['state']) ? $responce['job']['state'] : '';

        // log responce
        $responceId = $this->zencoderLogResponse($jobId, $state, $json);

        if ($state == 'finished') {
            $holder = isset($responce['output']['label']) ? $responce['output']['label'] : '';

            $this->zencoderCreateExportMessage($jobId, $holder, $responceId);
        }
    }

    /**
     * zencoder stills ready post request come here, after transcoding have finished
     */
    public function zencoder_stills_ready_post()
    {
        $json = file_get_contents('php://input');

        $responce = json_decode($json, true);

        $jobId = isset($responce['job']['id']) ? (int) $responce['job']['id'] : -1;
        $state = isset($responce['job']['state']) ? $responce['job']['state'] : '';

        $responceId = $this->zencoderLogResponse($jobId, $state, $json);

        if ($state == 'finished' && !empty($responce['output']['thumbnails'])) {
            $thumbnails = $responce['output']['thumbnails'];
            foreach ($thumbnails as $thumbnail) {
                if (!empty($thumbnail['label'])) {
                    $this->zencoderCreateExportMessage($jobId, $thumbnail['label'], $responceId);
                }
            }
        }
    }

    /**
     * @param $jobId
     * @param $state
     * @param $response
     *
     * @return int resonseId
     */
    private function zencoderLogResponse($jobId, $state, $json)
    {
        $this->db_master->insert(
            'zencoder_response_log',
            [
                'job_id' => $jobId,
                'state' => $state,
                'result_response' => $json
            ]
        );

        return $this->db_master->insert_id();
    }

    /**
     * preapre and insert export message
     *
     * @param $jobId
     * @param $holder
     *
     * @param int $responceId
     */
    private function zencoderCreateExportMessage($jobId, $holder, $responceId = 0)
    {
        // get code export url from zencoder_job_log by jon id
        $queryResult = $this->db->query(
            "SELECT clip_code as code, resource_holder as holder, import_url as import_url, export_url as path 
              FROM zencoder_job_log WHERE job_id = $jobId AND resource_holder = " . $this->db->escape($holder)
        )->result_array();
        $message = isset($queryResult[0]) ? $queryResult[0] : [];
        $message['response_id'] = $responceId;
        unset($queryResult);
        unset($responceId);
        // --


        // add new message to queue
        return $this->db_master->insert('zencoder_queue_export', ['message' => json_encode($message)]);
        // --
    }


    /**
     *
     * get pairs clipsId => likeId for current user id to use on clips search result page
     *
     * @param $clipsIds
     *
     * @return array
     */
    private function getClipsLikesByUser($clipsIds)
    {
        $clipsLikesByUser = [];
        if (!empty($clipsIds)) {

            $userId = $this->post('user_login')
                ? $this->users_model->get_user_by_login($this->post('user_login'))
                : null;

            $clipsLikesByUser = $this->clips_model->getClipsLikesByUser(
                $userId ?: $this->post('client_ip'),
                $clipsIds
            );
        }

        return $clipsLikesByUser;
    }

    public function getSpeciesList_get()
    {
        /**
         * Load necessary models
         */
        $this->load->model('Base_model');
        $this->load->model('Taxonomy_model');
        $this->load->model('Browse_category_model');
        $this->load->model('Family_group_model');
        $this->load->model('Common_name_model');
        $this->load->model('Species_model');


        /**
         * Raw query for family_groups
         */
        $query_fg = "SELECT f.id,  f.first_code, f.name, f.browse_category_id, SUM(f.result) as family_result, "
            . "SUM(c.result) as common_names_result, "
            . "SUM(c.result + f.result) as total_result "
            . "FROM `sp_family_groups` f "
            . "JOIN sp_common_names c on c.family_group_id = f.id "
            . "WHERE f.browse_category_id = %d "
            . "GROUP by f.id "
            . "HAVING total_result > 0";

        $browse_category_model = new Browse_category_model();
        $common_names_model = new Common_name_model();
        $species_model = new Species_model();
        $browse_categories = $this->db->get( $browse_category_model->getTable() )->result();

        $select = [
            'sp_common_names.id as `id`',
            'sp_common_names.first_code as `first_code`',
            'sp_common_names.name as `name`',
            'sp_common_names.family_group_id as `family_group_id`',
            'sp_common_names.species_id as `species_id`',
            'sp_species.name as `species_name`',
            'sp_species.id as `species_id`',
            'sp_species.family_id as `family_id`',
            'sp_species.genus_name as `genus_name`',
        ];
        $select_string = implode(',', $select);

        foreach ($browse_categories as &$category) {
            $families = $this->db->query(sprintf($query_fg, $category->id), false)->result();
            foreach ($families as &$family) {
                $common_names_query = $this->db
                    ->from($common_names_model->getTable())
                    ->where([
                        'family_group_id' => $family->id,
                        'result >' => 0,
                    ])
                    ->select($select_string)
                    ->join($species_model->getTable(), 'sp_species.id = sp_common_names.species_id')
                    ->get();
                $result_common_names = $common_names_query->result();
                $family->common_names = $result_common_names;
                /**
                 * Taking hierarchy
                 */
                $speciesObject = new Species_model();
                $hierarchy = $speciesObject->getHierarchyByFamily( $family->id );
                $family->hierarchy = $hierarchy;
            }
            $category->families = $families;
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode(['data' => $browse_categories]));
    }

    public function getCommonNameInfo_get($commonName)
    {
        $callback= $this->get('callback');
        $this->load->model('Base_model');
        $this->load->model('Taxonomy_model');
        $this->load->model('Kingdom_model');
        $this->load->model('Phylum_model');
        $this->load->model('Class_model');
        $this->load->model('Order_model');
        $this->load->model('Family_model');
        $this->load->model('Species_model');
        $this->load->model('Browse_category_model');
        $this->load->model('Family_group_model');
        $this->load->model('Common_name_model');

        $Kingdom_model = new Kingdom_model;
        $Phylum_model = new Phylum_model;
        $Class_model = new Class_model;
        $Order_model = new Order_model;
        $Family_model = new Family_model;
        $Species_model = new Species_model;
        $Browse_category_model = new Browse_category_model;
        $Family_group_model = new Family_group_model;
        $Common_name_model = new Common_name_model;

        $select = [
            $Family_model->getTable() . '.name as family_name',
            $Species_model->getTable() . '.name as species_name',
            $Order_model->getTable() . '.name as order_name',
            $Class_model->getTable() . '.name as class_name',
            $Phylum_model->getTable() . '.name as phylum_name',
            $Kingdom_model->getTable() . '.name as kingdom_name',
            $Common_name_model->getTable() . '.description as description',
        ];
        $select_string = implode(',', $select);

        $commonName = str_replace('-', ' ', $commonName);
        $query = $this->db->from($Species_model->getTable())
            ->select($select_string)
            ->join($Family_model->getTable(), $Family_model->getTable() . '.id = ' . $Species_model->getTable() . '.family_id')
            ->join($Order_model->getTable(), $Order_model->getTable() . '.id = ' . $Family_model->getTable() . '.order_id')
            ->join($Class_model->getTable(), $Class_model->getTable() . '.id = ' . $Order_model->getTable() . '.class_id')
            ->join($Phylum_model->getTable(), $Phylum_model->getTable() . '.id = ' . $Class_model->getTable() . '.phylum_id')
            ->join($Kingdom_model->getTable(), $Kingdom_model->getTable() . '.id = ' . $Phylum_model->getTable() . '.kingdom_id')
            ->join($Common_name_model->getTable(), $Common_name_model->getTable() . '.species_id = ' . $Species_model->getTable() . '.id')
            ->where( $Species_model->getTable() . '.name', $commonName)
            ->limit(1)
            ->get();

        $query_result = $query->result();
        $result = ['status' => 'success'];

        if ( ! is_array($query_result) || ! count($query_result) ) {
            $result['status'] = 'error';
            $result['error'] = 'Error DB request';
        } else {
            $r = $query_result[0];
            foreach ($r as &$prop) {
                $prop = ucfirst($prop);
            }
            $result['data'] = $r;
            $result['data']->string = "Kingdom: {$r->kingdom_name} > Phylum: {$r->phylum_name} > Class: {$r->class_name} > Order: {$r->order_name} > Family: {$r->family_name} > Species: {$r->species_name}";
        }
        $this->output->set_content_type('application/json');
        $output = json_encode($result);

        /**
         * Implementing JSONP
         */
        if ($callback) {
            $output = "{$callback}({$output});";
        }
        $this->output->set_output($output);
    }
}