<?php

/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/Cliplog/Editor/CliplogEditor.php');
/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/Cliplog/Clipbin/ClipbinRequest.php');
/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/Cliplog/Editor/KeywordsState/StateManager.php');
require_once(APPPATH . 'libraries/SorlSearchAdapter.php');

use Libraries\Cliplog\Editor\CliplogEditor;
use Libraries\Cliplog\Clipbin\ClipbinRequest;
use Libraries\Cliplog\Editor\KeywordsState\StateManager;

/**
 * Class Cliplog
 *
 * @property cliplog_model $cliplog_model
 * @property cliplog_templates_model $cliplog_templates_model
 * @property cliplog_keywords_model $cliplog_keywords_model
 * @property formats_model $formats_model
 * @property deliveryoptions_model $deliveryoptions_model
 * @property groups_model $groups_model
 * @property clips_model $clips_model
 * @property bins_model $bins_model
 * @property Backend_bin_model $backend_bin_model
 * @property galleries_model $galleries_model
 * @property submissions_model $submissions_model
 * @property sequences_model $sequences_model
 * @property clipbins_model $clipbins_model
 * @property hints_model $hints_model
 * @property labs_model $labs_model
 * @property users_model $users_model
 * @property pricing_model $pricing_model
 * @property collections_model $collections_model
 * @property builder $builder
 */
class Cliplog extends CI_Controller
{
    const AMOUNT_OF_CAROUSEL_ITEMS = 200;
    var $id;
    var $uid;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $group;
    var $search_adapter;
    var $sectionList = array(
        'shot_type' => 'Shot Type',
        'subject_category' => 'Subject Category',
        'primary_subject' => 'Primary Subject',
        'other_subject' => 'Other Subject',
        'appearance' => 'Appearance',
        'actions' => 'Actions',
        'time' => 'Time',
        'habitat' => 'Habitat',
        'concept' => 'Concept',
        'location' => 'Location'
    );

    const EDIT_CLIPLOG_URL = 'en/cliplog/edit/';

    function Cliplog()
    {
        parent::__construct();
        $this->path = 'ClipLog';
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('cliplog_model');
        $this->load->model('cliplog_keywords_model');
        $this->load->model('cliplog_templates_model');
        $this->load->model('formats_model');
        $this->load->model('deliveryoptions_model');
        $this->load->model('groups_model');
        $this->load->model('clips_model');
        //$this->load->model('bins_model');
        $this->load->model('backend_bin_model');
        $this->load->model('galleries_model');
        $this->load->model('submissions_model');
        $this->load->model('sequences_model');
        $this->load->model('clipbins_model');
        $this->load->model('hints_model');
        $this->load->model('labs_model');
        $this->load->model('collections_model');
        $this->clip_id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);
        $this->settings = $this->api->settings();
        $this->set_group();
        $this->uid = ($this->session->userdata('group') == 1) ? 0 : ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
    }

    /**
     * ?????? ???? ajax
     */
    function index()
    {
        $requestAction = $this->uri->segment(4);
        if ($requestAction && $this->api->is_ajax_request()) {
            switch ($requestAction) {
                case 'updatetemplate':
                    $this->updatetemplate();
                    break;
                case 'deletetemplate':
                    $this->deletetemplate();
                    break;
                case 'savegotonext':
                    $this->savegotonext();
                    break;
                case 'gettemplatesectionlist':
                    $this->getTemplateSectionList();
                    break;
                case 'thumbgallery':
                    $this->thumbgallery();
                    break;
                case 'changethumb':
                    $this->changethumb();
                    break;
                case 'loadthumbs':
                    $this->loadthumbs();
                    break;
                case 'getcarouselitems':
                    $this->getcarouselitems();
                    break;
                case 'getNextClipPath':
                    $this->getNextClipPath($this->uri->segment(5));
                    break;
                case 'getPrevClipPath':
                    $this->getPrevClipPath($this->uri->segment(5));
                    break;
                case 'getSessionAutoNextPageThumbgallery':
                    $this->getSessionAutoNextPageThumbgallery();
                    break;
            }
        } elseif ($requestAction) {
            switch ($requestAction) {
                case 'thumbgallery':
                    $this->thumbgallery();
                    break;
                case 'pricingDetails':
                    $this->pricing();
                    break;
            }
        } else
            show_404();
    }

    function dbQuerys()
    {
        //??????????? ????????:
        $qs = $this->db->queries;
        //?????? ??????????? ?????????:
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

    function msort($array, $key, $sort_flags = SORT_REGULAR)
    {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    function view()
    {
        $beforeSession = json_encode($_SESSION);
        $this->cliplog_model->getBackendSession();
        $startSession = json_encode($_SESSION);
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $is_admin = $this->group['is_admin'];
        # ??????? ?? ????????? ?? LoggingTemplateId ????? ?? ?????
        //->session->unset_userdata('loggingTemplateId');
        //echo $_REQUEST['submission'];
        $cliplogEditor = new CliplogEditor();
        $cliplogLogging = $cliplogEditor->getCliplogEditorLoggingTemplate();
        $cliplogLogging->setActiveTemplateId();
        # ??????? ?????? ?? ?????????
        $clipbinRequest = new ClipbinRequest();
        $clipbinActive = $clipbinRequest->getClipbinActive();
        $clipbinSelected = $clipbinRequest->getClipbinSelected();
        # ???????????, ??? ?? ??????? ?????? ????????
        if ($clipbinRequest->isChangeClipbinRequest()) {
            //$this->session->set_userdata( 'cliplog_search_filter_words' );
            if ($clipbinRequest->isSelectClipbinRequest()) {
                # ??????? ???????? ? ???????
                $newClipbinId = $clipbinRequest->getClipbinIdFromRequest();
                $clipbinActive->setActiveClipbinId($newClipbinId);
                $clipbinSelected->setSelectedClipbinId($newClipbinId);
            } elseif ($clipbinRequest->isUnselectClipbinRequest()) {
                # ??????? ?????? ?? ????????
                $clipbinSelected->unsetSelectedClipbinId();
            }
        }
        # ??????????, ?????? ?? ???????? ???????
        //if ( !$clipbinActive->isSetActiveClipbinId() ) {
        # ??? ??????, ????????????? ?????????
        if(!$clipbinActive->getActiveClipbinId()){
            $clipbinActive->setActiveClipbinId(
                $clipbinRequest->getUserDefaultClipbinId($uid)
            );
        }
        //}
        $this->updateFilters(); // $_REQUEST -> session
        $this->setClipbinClipsOrder();
        $this->setClipbinClipsPerPage();
        $user_login = $this->session->userdata('login');
        $this->session->set_userdata('cliplog_back_url', $_SERVER['REQUEST_URI']);
        $this->session->set_userdata('cliplog_back_title', 'Back To Search');

        $this->path = 'ClipLog: List View'; //'Clips section > ClipLog';
        $data['lang'] = $this->langs;
        $data['uid'] = $uid;
        $filter = $this->getFilters();
        // Reset all filrers

        if (($_REQUEST['backend_clipbin'] == 0 && $_REQUEST['backend_clipbin'] != null)) {
            //echo 'i am here ';die;
            unset($filter);
            $this->session->set_userdata('cliplog_search_filter_words');
            $this->session->set_userdata('cliplog_clipbin_filter', array());
            $this->session->set_userdata('cliplog_other_filter', array());
            $this->session->set_userdata('cliplog_search_filter', array());
            $clipbinSelected->unsetSelectedClipbinId(); // reset clipbin from next search
            unset($_REQUEST['backend_clipbin']);
            $this->session->unset_userdata('backend_clipbin_id');
            $this->session->unset_userdata('submissionId');
            $this->session->set_userdata('cliplog_clipbin_filter');
            $this->session->unset_userdata('cliplog_search_collection');
            $this->session->unset_userdata('cliplog_search_brand');
            $this->session->unset_userdata('cliplog_search_license');
            $this->session->unset_userdata('cliplog_search_price_level');
            $this->session->unset_userdata('cliplog_search_format_category');
            $this->session->unset_userdata('cliplog_search_active');
            $this->session->unset_userdata('cliplog_search_wordsin');
            $this->session->unset_userdata('cliplog_adminAction_filter_name');
            $this->session->unset_userdata('cliplog_duration_filter');
            $this->session->unset_userdata('cliplog_creation_date');
            $this->session->unset_userdata('searchWordFilter');

            //unset($_REQUEST['backend_clipbin']);
        }
        //Reset filter if 'words' search
        if ($this->input->post('words')) {

            $filter['words'] = $this->input->post('words');
        }

        if (isset($filter['words']) && $this->session->userdata('searchWordFilter') == '') {

            $filter = array('words' => $filter['words']);
            $this->session->set_userdata('cliplog_search_filter_words');
            $this->session->set_userdata('cliplog_clipbin_filter', array());
            $this->session->set_userdata('cliplog_other_filter', array());
            $this->session->set_userdata('cliplog_search_filter', array());

            $clipbinSelected->unsetSelectedClipbinId(); // reset clipbin from next search
            //del submission
            unset($_REQUEST['submission']);
            unset($_REQUEST['backend_clipbin']);
            $this->session->unset_userdata('backend_clipbin_id');
            $this->session->unset_userdata('cliplog_search_collection');
            $this->session->unset_userdata('cliplog_search_brand');
            $this->session->unset_userdata('cliplog_search_license');
            $this->session->unset_userdata('cliplog_search_price_level');
            $this->session->unset_userdata('cliplog_search_format_category');
            $this->session->unset_userdata('cliplog_search_active');
            $this->session->unset_userdata('cliplog_adminAction_filter_name');
            $this->session->set_userdata('cliplog_search_wordsin');
            $this->session->unset_userdata('cliplog_duration_filter');
            $this->session->unset_userdata('cliplog_creation_date');
            $this->session->unset_userdata('submissionId');
        }
        //Reset filter if Submission
        if (isset($_REQUEST['submission'])) {
            $filter = array('submission_id' => (int)$_REQUEST['submission']);
            $this->session->set_userdata('submissionId', $_REQUEST['submission']);
            $this->session->set_userdata('cliplog_search_filter', array());
            $this->session->set_userdata('cliplog_clipbin_filter', array());
            $clipbinSelected->unsetSelectedClipbinId();
            $this->session->set_userdata('cliplog_search_filter_words');
            $this->session->unset_userdata('backend_clipbin');
            $this->session->unset_userdata('backend_clipbin_id');
            $this->session->unset_userdata('cliplog_search_collection');
            $this->session->unset_userdata('cliplog_search_brand');
            $this->session->unset_userdata('cliplog_search_license');
            $this->session->unset_userdata('cliplog_search_price_level');
            $this->session->unset_userdata('cliplog_search_format_category');
            $this->session->unset_userdata('cliplog_search_active');
            $this->session->unset_userdata('cliplog_adminAction_filter_name');
            $this->session->unset_userdata('cliplog_search_wordsin');
            $this->session->unset_userdata('cliplog_duration_filter');
            $this->session->unset_userdata('cliplog_creation_date');
            $this->session->unset_userdata('searchWordFilter');
            unset($_REQUEST['submission']);
        }
        //Reset filter if Clipbin
        if (isset($_REQUEST['backend_clipbin'])) {

            // echo $_REQUEST['backend_clipbin'];
            $filter = array('backend_clipbin_id' => $_REQUEST['backend_clipbin']);
            $this->session->set_userdata('backend_clipbin_id', $_REQUEST['backend_clipbin']);
            $this->session->set_userdata('cliplog_search_filter', array());
            $this->session->set_userdata('cliplog_other_filter', array());
            $this->session->set_userdata('cliplog_clipbin_filter', array());
            $this->session->set_userdata('cliplog_search_filter_words');
            $this->session->unset_userdata('cliplog_search_collection');
            $this->session->unset_userdata('cliplog_search_brand');
            $this->session->unset_userdata('cliplog_search_license');
            $this->session->unset_userdata('cliplog_search_price_level');
            $this->session->unset_userdata('cliplog_search_format_category');
            $this->session->unset_userdata('cliplog_search_active');
            $this->session->unset_userdata('cliplog_adminAction_filter_name');
            $this->session->unset_userdata('cliplog_search_wordsin');
            $this->session->unset_userdata('submissionId');
            $this->session->unset_userdata('cliplog_duration_filter');
            $this->session->unset_userdata('cliplog_creation_date');
            $this->session->unset_userdata('searchWordFilter');
            unset($_REQUEST['backend_clipbin']);
        }

        if (isset($_REQUEST['filter'])) {

            if ($this->input->post('collection')) {
                $collection = $this->input->post('collection');
            } else {
                $collection = $filter['collection'];
            }

            if ($this->input->post('license')) {
                $licence = $this->input->post('license');
            } else {
                $licence = $filter['license'];
            }

            if ($this->input->post('price_level')) {
                $price_level = $this->input->post('price_level');
            } else {
                $price_level = $filter['price_level'];
            }
            if ($this->input->post('format_category')) {
                $format_category = $this->input->post('format_category');
            } else {
                $format_category = $filter['format_category'];
            }
            if ($this->input->post('active')) {
                $active = $this->input->post('active');
            } else {
                $active = $filter['active'];
            }
            if ($this->input->post('wordsin')) {
                $search_in = $this->input->post('wordsin');
            } else {
                $search_in = $filter['wordsin'];
            }
            if ($this->input->post('brand')) {
                $brands_val = $this->input->post('brand');
            } else {
                $brands_val = $filter['brand'];
            }
            if ($this->input->post('actionAdmin')) {
                $actionAdmin = $this->input->post('actionAdmin');
            } else {
                $actionAdmin = $filter['actionAdmin'];
            }
            if ($this->input->post('duration')) {
                $duration = $this->input->post('duration');
            } else {
                $duration = $filter['duration'];
            }
            if ($this->input->post('creation_date')) {
                $creation_date = $this->input->post('creation_date');
            } else {
                $creation_date = $filter['creation_date'];
            }


            if (isset($search_in) && !empty($search_in)) {
                $filter_searchin['search_in'] = $search_in;
            }
            $filter_duration['duration'] = array($duration);
            $filter_creation_date['creation_date'] = array($creation_date);


            if (isset($collection) && !empty($collection)) {
                $collection_filter_arr = array();
                foreach ($collection as $key => $col) {
//                    $collection_id = $col;
//                    $col_query = $this->db->query("SELECT name FROM lib_collections WHERE id = '" . $collection_id . "'")->result_array();
//                    $col_name = $col_query[0]['name'];
                    //$collection_filter[$key] = $col_name;
                    $collection_filter_arr[$key] = "'" . $col . "'";
                }
                // foreach ($collection_filter as $sub_pins) {
                //     $collection_filter_arr[] = "'" . $sub_pins . "'";
                // }
                $filter_collection['collection_filter'] = implode(",", $collection_filter_arr);
            }
            if (isset($brands_val) && !empty($brands_val) && $is_admin) {
                $brand_filter = array();
                foreach ($brands_val as $key => $brand) {
//                    $brand_id = $brand[0];
                    $brand_filter[$key] = $brand;
                }
                foreach ($brand_filter as $br_filter) {
                    $brand_filter_arr[] = "'" . $br_filter . "'";
                }
                $filter_brand['brand_filter'] = implode(",", $brand_filter_arr);
            }

            if (isset($actionAdmin) && !empty($actionAdmin) && $is_admin) {
                $action_admin_filter = array();
                foreach ($actionAdmin as $key => $action) {
                    $action_id = $action[0];
                    $action_admin_filter[$key] = "'" . $action_id . "'";
                }
                $action_admin_filter['admin_action_filter'] = implode(",", $action_admin_filter);
            }

            if (isset($licence) && !empty($licence)) {
                $license_filter = array();
                foreach ($licence as $key => $lic) {
                    $lic_id = $lic[0];
                    $license_filter[$key] = $lic_id;
                }
                foreach ($license_filter as $lic_filter) {
                    $lic_filter_arr[] = "'" . $lic_filter . "'";
                }
                $filter_license['license_filter'] = implode(",", $lic_filter_arr);
            }

            if (isset($price_level) && !empty($price_level)) {
                $price_filter = array();
                foreach ($price_level as $key => $price) {
                    $price_id = $price[0];
                    $price_filter[$key] = $price_id;
                }
                foreach ($price_filter as $pr_filter) {
                    $price_filter_arr[] = "'" . $pr_filter . "'";
                }
                $filter_price['price_level_filter'] = implode(",", $price_filter_arr);
            }

            if (isset($active) && !empty($active)) {
                $active_filter = array();
                foreach ($active as $key => $act) {
                    $active_id = $act[0];
                    $active_filter[$key] = $active_id;
                }
                foreach ($active_filter as $act_filter) {
                    $active_filter_arr[] = "'" . $act_filter . "'";
                }
                $filter_active['filter_active'] = implode(",", $active_filter_arr);
            }

            if (isset($format_category) && !empty($format_category)) {
                $format_filter = array();
                foreach ($format_category as $key => $filter_cat) {
                    $format_filter['filter_format'][$key] = $filter_cat;
                }
            }
        }
        if (isset($_REQUEST['filter'])) {
//            $this->session->unset_userdata('clipbin-clips-perpage');
//            $perPage = $this->input->post('clips_on_page');
            $this->session->set_userdata('cliplog_search_collection', $filter_collection['collection_filter']);
            $this->session->set_userdata('cliplog_search_license', $filter_license['license_filter']);
            $this->session->set_userdata('cliplog_search_price_level', $filter_price['price_level_filter']);
            $this->session->set_userdata('cliplog_search_format_category', $format_filter['filter_format']);
            $this->session->set_userdata('cliplog_search_active', $filter_active['filter_active']);
            $this->session->set_userdata('cliplog_search_wordsin', $filter_searchin['search_in']);
            $this->session->set_userdata('cliplog_search_brand', $filter_brand['brand_filter']);
            $this->session->set_userdata('cliplog_adminAction_filter_name', $action_admin_filter['admin_action_filter']);

            //  echo 'here'.$filter_duration['duration'];
            $this->session->set_userdata('cliplog_duration_filter', $filter_duration['duration']);
            $this->session->set_userdata('cliplog_creation_date', $filter_creation_date['creation_date']);


            // $this->session->set_userdata('cliplog_search_filter', $_REQUEST['filter']);
//            $this->session->set_userdata('clipbin-clips-perpage', $perPage);
            $this->session->set_userdata('cliplog_clipbin_filter', array());
            $this->session->set_userdata('cliplog_other_filter', array());
            // $this->session->unset_userdata('submissionId');
            // $this->session->unset_userdata('backend_clipbin_id');
            //unset($_REQUEST['backend_clipbin']);
            if (empty($collection)) {
                $this->session->unset_userdata('cliplog_search_collection');
            }
            if (empty($duration)) {
                $this->session->unset_userdata('cliplog_duration_filter');
            }
            if (empty($creation_date)) {
                $this->session->unset_userdata('cliplog_creation_date');
            }

            if (empty($brands_val)) {
                $this->session->unset_userdata('cliplog_search_brand');
            }
            if (empty($actionAdmin)) {
                $this->session->unset_userdata('cliplog_adminAction_filter_name');
            }
            if (empty($licence)) {
                $this->session->unset_userdata('cliplog_search_license');
            }
            if (empty($price_level)) {
                $this->session->unset_userdata('cliplog_search_price_level');
            }
            if (empty($format_category)) {
                $this->session->unset_userdata('cliplog_search_format_category');
            }
            if (empty($active)) {
                $this->session->unset_userdata('cliplog_search_active');
            }
            if (empty($search_in)) {
                $this->session->unset_userdata('cliplog_search_wordsin');
            }
        }


        //In session words
        //$this->session->unset_userdata['backend_clipbin_id'];

        if ($this->input->post('words')) {
            // echo $_REQUEST['backend_clipbin'];
            $this->session->unset_userdata('backend_clipbin_id');
            $this->session->unset_userdata('submissionId');

            unset($_REQUEST['backend_clipbin']);
            $word = $this->input->post('words');
            $this->session->set_userdata('searchWordFilter', $word);
        } else {

            $filter['words'] = $this->getSearchWords();
        }

        if (empty($filter['words']))
            unset($filter['words']);
        // Fix Client Id
        if (empty($filter['client_id']) && !$this->group['is_admin']) {
            $filter['contributor_id'] = $this->session->userdata('uid');
            $filter['client_id'] = $this->session->userdata('client_uid');
            $filter['brand_id'] = 1;
            $filter['backend_clipbin_id'] = $this->session->userdata('backend_clipbin_id');
        }

        $order = $this->getClipbinClipsOrder(); //$this->api->get_sort_order( 'clips' );
        if (empty($order)) {
            //if ( ! $order = $this->getClipbinClipsOrder() ) {
            $order = ' ORDER BY c.code ASC';
            //}
        }
        // Refactoring $all (count) and $data['clips'] mysql to SOLR for search --------------------------------
        // echo $filter['backend_clipbin_id'];
        //echo $filter['submission_id'];

        if ($this->session->userdata('submissionId')) {

            $filter['submission_id'] = $this->session->userdata('submissionId');
        }


        $all = 0;
        if (!empty($filter['backend_clipbin_id']) || !empty($filter['submission_id'])) {
            $limit = $this->get_clips_limit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                //$filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
                $filter = $this->renameFilter($filter, 'brand', 'brand_id');
            $filter = $this->renameFilter($filter, 'collection', 'collection_id');
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
            //$this->clips_model->build_filter_sql_backend($filter);
            $data['clips'] = $this->clips_model->get_clips_list_backend($this->langs, $filter, $order, $limit);

            $all = $this->clips_model->get_clips_count_backend($this->langs, $filter);

//            $solrLimit = $this->get_clips_Solrlimit();
//            $order = $this->getSolrOrder();
//            $data['filtered_search_clips'] = $this->clips_model->get_search_filtered_clips($filter, $solrLimit['offset'], $solrLimit['limit'], $order, '', $this->group['is_admin'], $this->langs);
//            if (!empty($data['clips'][0]['id'])) {
//                foreach ($data['clips'] as $key => $val) {
//                    $filter_clips_id[$key] = $data['clips'][0]['id'];
//                }
//
//                foreach ($filter_clips_id as $sub_clip) {
//                    $search_filter_arr[] = "'" . $sub_clip . "'";
//                }
//            }
        } else {

            //////// Changes To Stop SOLR Imran  //////////
            unset($filter['backend_clipbin_id']);
            //$this->session->unset_userdata('backend_clipbin_id');
            $solrLimit = $this->get_clips_Solrlimit();
            if (empty($filter))
                $filter['all'] = 1;
            if (!empty($filter['wordsin']))
                $filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
            $filter = $this->renameFilter($filter, 'brand', 'brand_id');
            $filter = $this->renameFilter($filter, 'collection', 'collection_id');
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

            $solrLimit = $this->get_clips_Solrlimit();
            $limit = $this->get_clips_limit();

            $data['clips'] = $this->clips_model->get_clips_list_backend($this->langs, $filter, $order, $limit);

            $order = $this->getSolrOrder();

            // echo '<pre>';
            // print_r($filter);
            // echo '</pre>';
            // die();
            $data['filtered_search_clips'] = $this->clips_model->get_search_filtered_clips($filter, $solrLimit['offset'], $solrLimit['limit'], $order, '', $this->group['is_admin'], $this->langs);
            if (!empty($data['clips'][0]['id'])) {
                foreach ($data['clips'] as $key => $val) {
                    $filter_clips_id[$key] = $data['clips'][0]['id'];
                }

                foreach ($filter_clips_id as $sub_clip) {
                    $search_filter_arr[] = "'" . $sub_clip . "'";
                }

                $search_filter_clip_id = implode(",", $search_filter_arr);
                $searchin_filter_query = $this->db->query("SELECT * FROM lib_clips_keywords WHERE clip_id IN (" . $search_filter_clip_id . ")")->result_array();
                $shot_type_arr = array();
                $subject_cat_arr = array();
                $primary_type_arr = array();
                $other_subject_arr = array();
                $actions_arr = array();
                $time_arr = array();
                $concept_arr = array();
                $loctaion_arr = array();
                $habitat_arr = array();
                foreach ($searchin_filter_query as $key => $search_value) {
                    $searchin_filter_keyword[$key] = $search_value['keyword'];
                    $search_filter_section_type[$key] = $search_value['section_id'];
                    if ($search_value['section_id'] == 'shot_type') {
                        $shot_type_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'subject_category') {
                        $subject_cat_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'primary_subject') {
                        $primary_type_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'other_subject') {
                        $other_subject_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'actions') {
                        $actions_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'time') {
                        $time_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'habitat') {
                        $habitat_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'concept') {
                        $concept_arr[$key] = $search_value['keyword'];
                    }
                    if ($search_value['section_id'] == 'location') {
                        $loctaion_arr[$key] = $search_value['keyword'];
                    }
                }
            }

//$data['keywords'] = $this->clips_model->getAllKeywordsByClipId($clip_id);

            if ($this->session->userdata('searchWordFilter') == '' && $filter['search_in'] == '') {
                unset($filter['words']);
                unset($filter['wordsin']);
            }
            // $this->clips_model->build_filter_sql_backend($filter);
            $all = $this->clips_model->get_clips_count_backend($this->langs, $filter);
//$data['result_filter'] = count($all);
            //////// Changes To Stop SOLR Imran  //////////
            //
            //
            //
            //////// OLD SOLR CODE ////////
//            $this->search_adapter = new SorlSearchAdapter();
//            if ($this->search_adapter->ping()) {
//                unset($filter['backend_clipbin_id']);
//                $order = $this->getSolrOrder();
//                $solrLimit = $this->get_clips_Solrlimit();
//                if (empty($filter))
//                    $filter['all'] = 1;
//                if (!empty($filter['wordsin']))
//                    $filter['words'] = $filter['words'] . ' ' . $filter['wordsin'];
//                $filter = $this->renameFilter($filter, 'brand', 'brand_id');
//                $filter = $this->renameFilter($filter, 'collection', 'collection_id');
//
//                $search_result = $this->search_adapter->search_clips($filter, $solrLimit['offset'], $solrLimit['limit'], $order, '', $this->group['is_admin']);
//                //echo '<!--<pre>';print_r([$search_result]);echo '<pre>-->';
//                if ($search_result['total'] > 0) {
//                    $all = $search_result['total'];
//                    $data['clips'] = $this->clips_model->get_clips_by_ids($search_result['clips']); //array_reverse();
//                } else {
//                    $data['clips'] = array();
//                }
//            }
            //////// OLD SOLR CODE ////////
        }


        /* switch($this->session->userdata('clipbin-clips-order')){
          case "id":
          $data['clips'] = $this->msort($data['clips'], array('code'));
          break;
          case "duration":
          $data['clips'] = $this->msort($data['clips'], array('duration'));
          break;
          } */

        $data['is_admin'] = FALSE;
        $data['providers'] = array();
        if ($this->group['is_admin']) {
            $this->load->model('users_model');
            $data['is_admin'] = TRUE;
            $data['providers'] = $this->users_model->get_providers_list_filtered();
        }
        $provider_filter = array();
        if ($this->group['is_editor'] && $uid) {
            $provider_filter['provider_id'] = (int)$uid;
            $clipbins_provider_filter['lb.provider_id'] = (int)$uid;
        }

        //$bins = $this->bins_model->get_bins_list($provider_filter);
        // $data['bins_list'] = $this->load->view('cliplog/bins_list', array('bins' => $bins, 'lang' => $data['lang']), TRUE);

        $galleries = $this->galleries_model->get_galleries_list($provider_filter);
        $data['galleries_list'] = $this->load->view('cliplog/galleries_list', array('galleries' => $galleries, 'lang' => $data['lang']), TRUE);
        if (isset($_REQUEST['gallery'])) {
            $gallery = $this->galleries_model->get_gallery((int)$_REQUEST['gallery']);
            $data['selected_gallery'] = $gallery;
        }

        $submissions = $this->submissions_model->get_submissions_tree($provider_filter, array(), 'date DESC, id DESC', $this->session->userdata('submission_id')); //$_REQUEST[ 'submission' ]);

        $data['submissions_list'] = $this->load->view('cliplog/submissions_tree', array('submissions' => $submissions, 'lang' => $data['lang']), TRUE);
        if (isset($filter['submission_id'])) {
            $submission = $this->submissions_model->get_submission((int)$filter['submission_id']);
            $data['selected_submission'] = $submission;
        }

        $sequences = $this->sequences_model->get_sequences_list($provider_filter);
        $data['sequences_list'] = $this->load->view('cliplog/sequences_list', array('sequences' => $sequences, 'lang' => $data['lang']), TRUE);
        if (isset($_REQUEST['sequence'])) {
            $sequence = $this->sequences_model->get_sequence((int)$_REQUEST['sequence']);
            $data['selected_sequence'] = $sequence;
        }

        $data['clipbins_list'] = $this->get_clipbin_widget(
            $clipbinActive->getActiveClipbinId(), $user_login, $data['lang']
        );

        $params = array();
        if (isset($_REQUEST['bin']) && $_REQUEST['bin'])
            $params[] = 'bin=' . (int)$_REQUEST['bin'];
        if (isset($_REQUEST['gallery']) && $_REQUEST['gallery'])
            $params[] = 'gallery=' . (int)$_REQUEST['gallery'];
        if (isset($_REQUEST['submission']) && $_REQUEST['submission'])
            $params[] = 'submission=' . (int)$_REQUEST['submission'];
        if (isset($_REQUEST['sequence']) && $_REQUEST['sequence'])
            $params[] = 'sequence=' . (int)$_REQUEST['sequence'];
        if (isset($_REQUEST['words']) && $_REQUEST['words'])
            $params[] = 'words=' . $_REQUEST['words'];
        if (isset($_REQUEST['wordsin']) && $_REQUEST['wordsin'])
            $params[] = 'wordsin=' . $_REQUEST['wordsin'];

        $data['active_filter_url'] = $this->langs . '/cliplog/view/?' . implode('&', array_merge($params, array('active=1')));
        $data['not_active_filter_url'] = $this->langs . '/cliplog/view/?' . implode('&', array_merge($params, array('active=0')));


        $list_views = array('list', 'grid');
        if (isset($_REQUEST['list_view']) && in_array($_REQUEST['list_view'], $list_views)) {
            $this->session->set_userdata('list_view', $_REQUEST['list_view']);
        }
        if ($this->session->userdata('list_view')) {
            $data['list_view'] = $this->session->userdata('list_view');
        } else {
            $data['list_view'] = 'grid';
            $this->session->set_userdata('list_view', 'grid');
        }

        $data['drag_and_drop_message'] = FALSE;

        if (!isset($_COOKIE['drag_and_drop_message'])) {
            setcookie('drag_and_drop_message', 1, time() + 604800, '/');
            $data['drag_and_drop_message'] = TRUE;
        }

        # ?????? ????????? ????????
        if ($clipbinActive->isSetActiveClipbinId()) {
            $data['active_clipbin'] = $this->backend_bin_model->get_bin($clipbinActive->getActiveClipbinId());
        }
        if ($data['active_clipbin']) {
            $data['active_clipbin']['items_ids'] = array();
            foreach ($data['active_clipbin']['items'] as $item) {
                $data['active_clipbin']['items_ids'][$item['id']] = $item['id'];
            }
            if ($data['active_clipbin']['is_gallery']) {
                $data['active_clipbin']['type'] = 'gallery';
            } elseif ($data['active_clipbin']['is_sequence']) {
                $data['active_clipbin']['type'] = 'sequence';
            } else {
                $data['active_clipbin']['type'] = 'bin';
            }
        }
        # ?????? ?????????? ????????
        if ($clipbinSelected->isSetSelectedClipbinId()) {
            $data['selected_clipbin'] = $this->backend_bin_model->get_bin($clipbinSelected->getSelectedClipbinId());
            if ($data['selected_clipbin']) {
                $data['selected_clipbin']['items_ids'] = array();
                foreach ($data['selected_clipbin']['items'] as $item) {
                    $data['selected_clipbin']['items_ids'][$item['id']] = $item['id'];
                }
                if ($data['selected_clipbin']['is_gallery']) {
                    $data['selected_clipbin']['type'] = 'gallery';
                } elseif ($data['selected_clipbin']['is_sequence']) {
                    $data['selected_clipbin']['type'] = 'sequence';
                } else {
                    $data['selected_clipbin']['type'] = 'bin';
                }
            }
        }

        $data['add_js'] = array(
            '/data/js/jquery.contextMenu.js',
            '/data/js/cliplog.clipbin.js'
        );

        $data['current_perpage'] = $this->getClipbinClipsPerPage();
        $data['paging'] = $this->api->get_pagination(
            'cliplog/view', $all, $this->getClipbinClipsPerPage()
        );
        $data['search_flags'] = $this->getAdvancedSearchFlags();
        $data['format_category_filter'] = $this->getFormatCategoryFilter();
        $data['brands'] = $this->cliplog_model->get_brands();
        $data['collections'] = $this->collections_model->get_collections_list(array('search_term !=' => ''));
        $data['words'] = $this->getSearchWords();
        $data['wordsin'] = $filter['wordsin'];
        $from = ($this->uri->segment(4)) ? $this->uri->segment(4) : 1;
        $to = ($from > 1) ? $from + $this->getClipbinClipsPerPage() : $this->getClipbinClipsPerPage();
        $to = ($to > $all) ? $all : $to;
        $data['paging_count'] = array('all' => $all, 'from' => $from, 'to' => $to);
        $data['shot_type_filter'] = $shot_type_arr;
        $data['subject_category_filter'] = $subject_cat_arr;
        $data['primary_type_filter'] = $primary_type_arr;
        $data['other_type_filter'] = $other_subject_arr;
        $data['action_type_filter'] = $active_filter_arr;
        $data['time_type_filter'] = $time_arr;
        $data['concept_type_filter'] = $concept_arr;
        $data['location_type_filter'] = $loctaion_arr;
        $data['habitat_type_filter'] = $habitat_arr;
        $afterSession = json_encode($_SESSION);
        /*if($uid==1 || $uid==300)
            mail('dmitriy.klovak@boldendeavours.com','TEST:SESSION','BEFORE: '.$beforeSession."\n<br>START: ".$startSession."\n<br>AFTER: ".$afterSession);*/
        $this->cliplog_model->setBackendSession();
        $content = $this->load->view('cliplog/view', $data, TRUE);
        $this->out($content);
    }

    /**
     * Function rename filter key
     * @param array $filter - array filters
     * @param string $oldName - old key filter
     * @param string $newName - new key filter
     *
     * @return array  - new renamed array filters
     */
    function renameFilter($filter, $oldName, $newName)
    {
        if (isset($filter[$oldName])) {
            $filter[$newName] = $filter[$oldName];
            unset($filter[$oldName]);
        }
        return $filter;
    }

    function get_clipbin_widget($selected_clipbin_id, $user_login, $lang)
    {
        $keyword = $_SESSION['clipbins_filter'];
        $clipbin_widget = $this->load->view(
            'cliplog/clipbins_box', array(
            'active_clipbin' => $this->backend_bin_model->get_bin(ClipbinRequest::getInstance()->getClipbinActive()->getActiveClipbinId()),
            'clips' => $this->backend_bin_model->get_items(ClipbinRequest::getInstance()->getClipbinActive()->getActiveClipbinId()),
            'bins' => $this->backend_bin_model->get_no_folder_bins_list($user_login, $keyword),
            'folders' => $this->backend_bin_model->get_widget_folders_list($user_login, $keyword),
            'lang' => $lang,
            'is_admin' => $this->group['is_admin'] ? TRUE : FALSE
        ), TRUE
        );
        return $clipbin_widget;
    }

    /**
     * @param $clipId
     * @return string
     */
    protected function editClipUrl($clipId)
    {
        return self::EDIT_CLIPLOG_URL.$clipId;
    }

    function edit()
    {
        $this->cliplog_model->getBackendSession();
        //Changes for Carousal
        unset($_SESSION['firstLeftCrousal']);
        $cliplog_page = $this->session->userdata('cliplog_page');
        $cliplog_page['offset'] = (isset($cliplog_page['offset'])) ? $cliplog_page['offset'] : 0;
        $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
        $this->session->set_userdata('cliplog_page_carusel', $cliplog_page);
        $this->session->set_userdata('cliplog_page_carusel_left', $cliplog_page);

        //Changes for Carousal


        $uid = ($this->session->userdata('group') == 1) ? 0 : ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
# Objects kliplog
        $cliplogEditor = new CliplogEditor();
        $cliplogRequest = $cliplogEditor->getCliplogEditorRequest();
        $cliplogLogging = $cliplogEditor->getCliplogEditorLoggingTemplate();
        $cliplogMetadata = $cliplogEditor->getCliplogEditorMetadataTemplate();
        $selected_log = $this->input->post('cliplog-actions-select');
        //$filters_submission = $this->getFilters();
        $submission_id = $this->session->userdata('submissionId');
        if ($this->session->userdata('backend_clipbin_id')) {
            $filterClip['backend_clipbin_id'] = $this->session->userdata('backend_clipbin_id');
        }
        if ($this->session->userdata('cliplog_search_collection')) {
            $filterClip['cliplog_search_collection'] = $this->session->userdata('cliplog_search_collection');
        }
        if ($this->session->userdata('cliplog_search_license')) {
            $filterClip['cliplog_search_license'] = $this->session->userdata('cliplog_search_license');
        }
        if ($this->session->userdata('cliplog_search_price_level')) {
            $filterClip['cliplog_search_price_level'] = $this->session->userdata('cliplog_search_price_level');
        }
        if ($this->session->userdata('cliplog_search_active')) {
            $filterClip['cliplog_search_active'] = $this->session->userdata('cliplog_search_active');
        }
        if ($this->session->userdata('cliplog_search_format_category')) {
            $filterClip['cliplog_search_format_category'] = $this->session->userdata('cliplog_search_format_category');
        }
//        if($this->session->userdata('cliplog_search_wordsin')){
//            $filterClip['cliplog_search_wordsin'] = $this->session->userdata('cliplog_search_wordsin');
//        }
        if ($selected_log == 'log_selected') {
            $allClips = $this->cliplog_model->getAllClipsByUserId($uid, $submission_id, $filterClip);
            $clips_arr_all = array();
            // print_r($allClips);
            foreach ($allClips as $key => $value) {
                $clips_arr_all[$key] = $value->id;
            }
            //  print_r($clips_arr_all);
            $editorClipsIds = $clips_arr_all;
        } else {
            $editorClipsIds = $cliplogRequest->getClipsIds();
            //echo $this->db->last_query();
        }

        # Determine id clips
        $selectedClipId = $cliplogRequest->getSelectedClipId();

        $clipId = ($selectedClipId) ? $selectedClipId : $editorClipsIds[0];
        $isOwner = $this->clips_model->isOwner($clipId, $uid);

        # If no Id clip - Redirects in clilog / view
        if (!$selectedClipId && !$editorClipsIds || !$isOwner) {
            redirect($this->langs . '/cliplog/view');
        }

        if (isset($selectedClipId) && $selectedClipId != '') {
            unset($editorClipsIds);
            $this->clips_model->updateLibClipsClipIds($selectedClipId);
          //  $this->clips_model->updateClipstatus($selectedClipId);
        }
        # Save the clip to display the Id
        $cliplogEditor->pushSelectedClipIdToTemplateData($selectedClipId);
        $cliplogEditor->pushEditorClipsIdsToTemplateData($editorClipsIds);

        $stateManager = new StateManager();
//        if ($stateManager->isClipSaveRequest()) {
//            echo '<pre>';
//            print_r($_POST);
//            die;
//        }

        if (!empty($editorClipsIds)) {

            $postData = $stateManager->processInput($_POST, $editorClipsIds);
           // $this->clips_model->updateClipstatus($editorClipsIds);
        } else {

            $postData = $stateManager->processInput($_POST, $selectedClipId);
        }

        # Determine whether you want to convey the state of the settings cl. words in the display
        if (!$cliplogRequest->isChangeLoggingTemplateRequest()) {
            # Change Logging template was not
            if ($stateManager->isStateInRequest()) {
                # Able to get along with the request

                if (!$cliplogRequest->isChangeKeywordsTemplateRequest()) {

                    $keywordsState = $stateManager->getStateFromRequest();
                    if ($stateManager->isClipSaveRequest()) {
                        # Prompt to save kl.slov clip
                        // $postData = &$this->input->post();
                        // $postData = $stateManager->processInput($_POST);


                        /* $keywordsIdList = $this->input->post( 'keywords' );
                          if ( $keywordsIdList && $stateManager->hasTemporaryKeywords( $keywordsIdList ) ) {
                          # ????? ????????? ??.??????, ????? ????????
                          $replacedList = $stateManager->createKeywordsFromTemporary( $keywordsIdList );
                          }
                          # ????? ????????? ? ???????? ???????? ??.??????
                          if ( isset( $replacedList ) ) {
                          $keywordsIdList = $replacedList;
                          }
                          if ( $keywordsIdList && $stateManager->hasHiddenKeywords( $keywordsIdList ) ) {
                          # ????? ???????? ??????
                          $replacedList = $stateManager->createHiddenKeywordsFromTemporary( $keywordsIdList );
                          } */
                    }
                    $cliplogEditor->replaceTemplateData(array(
                        'keywordsState' => json_encode($keywordsState)
                    ));
                }
            }
        }

        # ????? ??????? ?? ??????????? ??????
        if ($cliplogRequest->isSaveRequest() && !$cliplogRequest->getChangedLoggingTemplateId() && !$cliplogRequest->isChangeKeywordsTemplateRequest()) {
            # ???????? ???? ?????? ?????
            $formData = $cliplogRequest->getFormDataForSave();


            $dataUserKeywords = $this->input->post('userKeywordsInsert');
//            echo '<pre>' . $selectedClipId;
//            print_r($dataUserKeywords);
//            //print_r($formData['keywords']);
//            die;

            if (!empty($editorClipsIds)) {
                $addClipKeywords = $this->clips_model->addClipsKeywordsMultiple($formData['keywords'], $uid, $editorClipsIds, $dataUserKeywords, $formData['overwrite'], $_POST['keywordsHiddenState']);
            } else {
                $addClipKeywords = $this->clips_model->addClipsKeywords($formData['keywords'], $uid, $selectedClipId, $dataUserKeywords, $_POST['keywordsHiddenState']);
            }
            $this->db_master->query("DELETE FROM lib_keywords WHERE collection ='' AND provider_id = 0");

            $deleteFeilddata = $this->input->post('deleteFeilddata');
            $this->clips_model->deleteClipsKeywords($deleteFeilddata);


//            echo "<pre>";
//            print_r($formData);
//            echo "</pre>";
//            exit;
            $replacedList = $this->createHiddenAndTempKeywords();
            # ??? ????????? id ??.?????, ?? ????????? ?? ?????????????? ??????????
            if (isset($replacedList) && $replacedList) {
                $formData['keywords'] = $replacedList;
            }
            if ($selectedClipId) {
                # ????? ?????? ???????? ???? - ??????????? ?????? ???? ????
                $cliplogEditor->saveFormDataToClips($selectedClipId, $formData, false);
            } else {
                # ????? ??????????? ???? ?????
                $cliplogEditor->saveFormDataToClips($editorClipsIds, $formData, false);
            }

            if (isset($selectedClipId) && $selectedClipId != '') {
                unset($editorClipsIds);
                $this->clips_model->updateClipstatus($selectedClipId);
            }
            if (!empty($editorClipsIds)) {
                $this->clips_model->updateClipstatus($editorClipsIds);
            }
            if ($_REQUEST['applied_keywords_set_id'] === 'reset')
                unset($_REQUEST['applied_keywords_set_id']);
            //echo 'saved'; return 'saved'; exit();
        }

        # ??????? Id ????????? Logging ???????, ????? ??? ??????? ??? ????? ? ???????? ??
        //$loggingTemplateId=$this->session->userdata('loginTemplateId');
        //if ( $cliplogRequest->isChangeLoggingTemplateRequest() || !empty($loggingTemplateId)) {
        $loggingTemplateId = /* $cliplogRequest->getChangedLoggingTemplateId(); */
            ($cliplogRequest->getChangedLoggingTemplateId() !== false) ? $cliplogRequest->getChangedLoggingTemplateId() : $cliplogLogging->getActiveTemplateId();
        //if($loggingTemplateId != $cliplogLogging->getActiveTemplateId()){
        if ($cliplogRequest->isChangeLoggingTemplateRequest()  /* && $cliplogRequest->getChangedLoggingTemplateId() == 0*/) {
            $this->clips_model->delete_not_visible_keywords($this->session->userdata('uid'));
        }
        $cliplogLogging->setActiveTemplateId(
            $loggingTemplateId
        );


        if ($cliplogRequest->isNextClipRequest() && $cliplogRequest->isSaveRequest()) {

            if (!empty($nextClipId = $postData['next_clip_id'])) {
                $this->cliplog_model->setBackendSession();
                redirect(
                    $this->editClipUrl($nextClipId),
                    null, // $method = 'location'
                    null, // $http_response_code = 302
                    true // $skip_url_suffix = false
                );
            }
        }

        //}
        //}
        # ?????????? ?????? ????????? Logging ??????? ???? ????????????
        $cliplogEditor->pushTemplateData(
            $cliplogLogging->getTemplateData(
                $cliplogLogging->getActiveTemplateId()
            )
        );

        # ?????????? ?????? Metadata ??????? ???? ????????????, ?? ?????????
        $metadataData = array();
        if ($cliplogRequest->isDiscardLoggingChangesRequest()) {
            # ??????? ???????? ?????????????? ?????? Logging - ?????????? ?????? ?????
            $metadataData = $cliplogRequest->getFormMetadata();
        } else {
            if ($editorClipsIds && !$selectedClipId) {
                # ?????????????? ?????????? ??????
                if ($cliplogRequest->isSaveRequest() && !$cliplogRequest->getChangedLoggingTemplateId() && !$cliplogRequest->isChangeKeywordsTemplateRequest()) {
                    # ?????????? ?????? - ????????? ?????? ?????
                    $metadataData = $cliplogMetadata->getEmptyTemplate(); //$cliplogRequest->getFormMetadata();
                } elseif ($cliplogRequest->isChangeKeywordsTemplateRequest()) {
                    # ???????????? ??????? Keywords - ????????? ?????? ???????
                    $metadataData = $cliplogMetadata->addKeywordsToTemplate(
                        $cliplogMetadata->getTemplateData(
                            $cliplogRequest->getChangedKeywordsTemplateId()
                        )//,$this->createHiddenAndTempKeywords()
                    );;
                } elseif ($cliplogRequest->isChangeLoggingTemplateRequest()) {
                    # ???????????? Logging ??????? - ????????? ?????? ?????
                    $metadataData = $cliplogRequest->getFormMetadata();
                }
            } elseif ($selectedClipId) {
                # ?????????????? ?????????? ?????
                if ($cliplogRequest->isSaveRequest()) {
                    # ?????????? ?????? - ????????? ?????? ?????
                    $metadataData = $cliplogEditor->getClipSavedMetadata($selectedClipId);
                } elseif ($cliplogRequest->isChangeKeywordsTemplateRequest()) {
                    # ???????????? Keywords ??????? - ????????? ?????? ???????
                    $metadataData = $cliplogMetadata->addKeywordsToTemplate(
                        $cliplogMetadata->getTemplateData(
                            $cliplogRequest->getChangedKeywordsTemplateId()
                        ), $this->createHiddenAndTempKeywords()
                    );
                } elseif ($cliplogRequest->isChangeLoggingTemplateRequest()) {
                    # ???????????? Logging ??????? - ?????????? ?????? ?????
                    $metadataData = $cliplogRequest->getFormMetadata();
                } else {
                    # ???????????? ??? ???????? ????? - ????????? ?????? ?????
                    $metadataData = $cliplogEditor->getClipSavedMetadata($selectedClipId);
                }
            }
        }

# ???????? ?????? Metadata ? ???????????, ?????? ????? ???????? ???????? ? Logging ???????
        $newMetadataData = array();
        if (isset($metadataData['sections_values']) && is_array($metadataData)) {
            #$loggingData = $cliplogLogging->getTemplateData( $cliplogLogging->getActiveTemplateId() );
            $loggingData = $cliplogEditor->getTemplateData();
            $keywordTemplateId = $cliplogRequest->getChangedKeywordsTemplateId();
            $clipData = $this->clips_model->get_clip_for_edit($selectedClipId);

            if ($metadataData['sections_values']['clip_description'] == '') {
                $metadataData['sections_values']['clip_description'] = $clipData['description'];
            }
            if ($metadataData['sections_values']['clip_notes'] == '') {
                $metadataData['sections_values']['clip_notes'] = $clipData['notes'];
            }
            if ($metadataData['sections_values']['license_restrictions'] == '') {
                $metadataData['sections_values']['license_restrictions'] = $clipData['license_restrictions'];
            }
            if ($metadataData['sections_values']['audio_video'] == '') {
                $metadataData['sections_values']['audio_video'] = $clipData['audio_video'];
            }
            if (isset($metadataData['sections_values']['date_filmed']['year'])) {
                if ($metadataData['sections_values']['date_filmed']['year'] == '') {
                    if ($clipData['film_date'] != '0000-00-00' && isset($clipData['film_date'])) {
                        $metadataData['sections_values']['date_filmed']['year'] = date('Y', strtotime($clipData['film_date']));
                    }
                }
            }
            if (isset($metadataData['sections_values']['date_filmed']['month'])) {
                if ($metadataData['sections_values']['date_filmed']['month'] == '') {
                    if ($clipData['film_date'] != '0000-00-00' && isset($clipData['film_date'])) {
                        $metadataData['sections_values']['date_filmed']['month'] = date('m', strtotime($clipData['film_date']));
                    }
                }
            }
            if ($metadataData['sections_values']['license_type'] == '') {
                $metadataData['sections_values']['license_type'] = $clipData['license'];
            }
            if ($metadataData['sections_values']['price_level'] == '') {
                $metadataData['sections_values']['price_level'] = $clipData['price_level'];
            }
            if ($metadataData['sections_values']['releases'] == '') {
                $metadataData['sections_values']['releases'] = $clipData['releases'];
            }
            if ($metadataData['sections_values']['file_formats']['camera_model'] == '') {
                $metadataData['sections_values']['file_formats']['camera_model'] = $clipData['camera_model'];
            }
            if ($metadataData['sections_values']['file_formats']['camera_chip_size'] == '') {
                $metadataData['sections_values']['file_formats']['camera_chip_size'] = $clipData['camera_chip_size'];
            }
            if ($metadataData['sections_values']['file_formats']['bit_depth'] == '') {
                $metadataData['sections_values']['file_formats']['bit_depth'] = $clipData['bit_depth'];
            }
            if ($metadataData['sections_values']['file_formats']['color_space'] == '') {
                $metadataData['sections_values']['file_formats']['color_space'] = $clipData['color_space'];
            }
            if ($metadataData['sections_values']['file_formats']['source_frame_size'] == '') {
                $metadataData['sections_values']['file_formats']['source_frame_size'] = $clipData['source_frame_size'];
            }
            if ($metadataData['sections_values']['file_formats']['source_frame_rate'] == '') {
                $metadataData['sections_values']['file_formats']['source_frame_rate'] = $clipData['source_frame_rate'];
            }
            if ($metadataData['sections_values']['file_formats']['source_data_rate'] == '') {
                $metadataData['sections_values']['file_formats']['source_data_rate'] = $clipData['source_data_rate'];
            }
            if ($metadataData['sections_values']['file_formats']['source_codec'] == '') {
                $metadataData['sections_values']['file_formats']['source_codec'] = $clipData['source_codec'];
            }
            if ($metadataData['sections_values']['file_formats']['source_format'] == '') {
                $metadataData['sections_values']['file_formats']['source_format'] = $clipData['source_format'];
            }
            if ($metadataData['sections_values']['file_formats']['digital_file_format'] == '') {
                $metadataData['sections_values']['file_formats']['digital_file_format'] = $clipData['digital_file_format'];
            }
            if ($metadataData['sections_values']['file_formats']['digital_file_frame_size'] == '') {
                $metadataData['sections_values']['file_formats']['digital_file_frame_size'] = $clipData['digital_file_frame_size'];
            }
            if ($metadataData['sections_values']['file_formats']['digital_file_frame_rate'] == '') {
                $metadataData['sections_values']['file_formats']['digital_file_frame_rate'] = $clipData['digital_file_frame_rate'];
            }
            if ($metadataData['sections_values']['file_formats']['file_size_mb'] == '') {
                $metadataData['sections_values']['file_formats']['file_size_mb'] = $clipData['file_size_mb'];
            }
            if ($metadataData['sections_values']['file_formats']['file_wrapper'] == '') {
                $metadataData['sections_values']['file_formats']['file_wrapper'] = $clipData['file_wrapper'];
            }
            if ($metadataData['sections_values']['country'] == '') {
                $metadataData['sections_values']['country'] = $clipData['country'];
            }


            foreach ($metadataData['sections_values'] as $sectionName => $sectionValue) {
                if (isset($loggingData[$sectionName]) || 1/* (!empty($keywordTemplateId) || $keywordTemplateId!='' ) */) {//!empty($sectionValue) /*$cliplogRequest->isChangeKeywordsTemplateRequest()*/ ) {
                    /* if(!empty($sectionValue) || isset($loggingData[ $sectionName ])){
                      if(!isset($loggingData[ $sectionName ]) && !empty($sectionValue)){
                      $loggingModified=1;
                      } */

                    $newMetadataData[$sectionName] = $sectionValue;
                }
            }
            $newMetadataData['cliplog_keyword_set'] = $metadataData['cliplog_keyword_set'];
            $newMetadataData['keywords'] = $metadataData['keywords'];
        }
        $cliplogEditor->replaceTemplateData($newMetadataData);

# ????????? ? ???????? ? ????? ????????? ??????? ??.?????(??????\??????)
        $cliplogTmpData = $cliplogLogging->getTemplateData(
            $cliplogLogging->getActiveTemplateId()
        );

# ???????? ?????? ???? ???????
        $sectionList = array(
            'shot_type' => 'Shot Type',
            'subject_category' => 'Subject Category',
            'primary_subject' => 'Primary Subject',
            'other_subject' => 'Other Subject',
            'appearance' => 'Appearance',
            'actions' => 'Actions',
            'time' => 'Time',
            'habitat' => 'Habitat',
            'concept' => 'Concept',
            'location' => 'Location'
        );
# ?????????? ???????
        $otherSectionList = array(
            'clip_notes' => 'Notes',
            'add_collection' => 'Collection',
            'license_restrictions' => 'License Restrictions',
            'audio_video' => 'Audio / Video',
            'date_filmed' => 'Date Filmed',
            'license_type' => 'License Type',
            'price_level' => 'Price Level',
            'releases' => 'Releases',
            'file_formats' => 'File formats',
            'country' => '?ountry'
        );

        if (!$cliplogLogging->getActiveTemplateId()) {
            $keywordsSectionsVisibleList = array();
            $keywordsSectionsHideLists = array();
            foreach ($sectionList as $sectionName => $sectionValue) {
                $keywordsSectionsVisibleList[$sectionName] = TRUE;
                $keywordsSectionsHideLists[$sectionName] = TRUE;
            }
            $cliplogEditor->replaceTemplateData(
                array(
                    'keywordsSectionsVisibleString' => implode(",", $keywordsSectionsVisibleList),
                    'keywordsSectionsVisibleList' => $keywordsSectionsVisibleList,
                    'keywords_sections_hide_listsString' => implode(",", $keywordsSectionsHideLists),
                    'keywords_sections_hide_lists' => $keywordsSectionsHideLists
                )
            );
        } elseif (isset($cliplogTmpData['keywords_sections_visible'])) {
            $keywordsSectionsVisibleString = $cliplogTmpData['keywords_sections_visible'];
            $keywordsSectionsVisibleArray = explode(',', $keywordsSectionsVisibleString);
            $keywordsSectionsVisibleList = array();
            foreach ($keywordsSectionsVisibleArray as $sectionName) {
                $keywordsSectionsVisibleList[$sectionName] = TRUE;
            }
            $cliplogEditor->replaceTemplateData(
                array(
                    'keywordsSectionsVisibleString' => $keywordsSectionsVisibleString,
                    'keywordsSectionsVisibleList' => $keywordsSectionsVisibleList,
                )
            );
        } else {
            $keywordsSectionsVisibleList = array();
            foreach ($sectionList as $sectionName => $sectionValue) {
                $keywordsSectionsVisibleList[$sectionName] = TRUE;
            }
            $cliplogEditor->replaceTemplateData(
                array(
                    'keywordsSectionsVisibleString' => implode(",", $keywordsSectionsVisibleList),
                    'keywordsSectionsVisibleList' => $keywordsSectionsVisibleList,
                )
            );
        }
// Keywords Sections Hide list
        if (isset($cliplogTmpData['keywords_sections_hide_lists'])) {
            $keywords_sections_hide_listsString = $cliplogTmpData['keywords_sections_hide_lists'];
            $keywords_sections_hide_listsArray = explode(',', $keywords_sections_hide_listsString);
            $keywords_sections_hide_lists = array();
            foreach ($keywords_sections_hide_listsArray as $sectionName) {
                $keywords_sections_hide_lists[$sectionName] = TRUE;
            }
            $cliplogEditor->replaceTemplateData(
                array(
                    'keywords_sections_hide_listsString' => implode(",", $keywords_sections_hide_lists),
                    'keywords_sections_hide_lists' => $keywords_sections_hide_lists
                )
            );
        } else {
            $keywords_sections_hide_lists = array();
            foreach ($sectionList as $sectionName => $sectionValue) {
                $keywords_sections_hide_lists[$sectionName] = TRUE;
            }
            $cliplogEditor->replaceTemplateData(
                array(
                    'keywords_sections_hide_listsString' => implode(",", $keywords_sections_hide_lists),
                    'keywords_sections_hide_lists' => $keywords_sections_hide_lists,
                )
            );
        }
# ????????? ?????? ?????? ???? ????????????
        $clipsData = array();
        if ($editorClipsIds) {
            # ?????????? ?????? - ????????? ?????? ??????, ??????? ???????????????
            $this->clips_model->build_filter_sql_backend(array('id' => $editorClipsIds));
            $clipsData['clips'] = $this->clips_model->get_clips_list_backend('en', NULL, 'ORDER BY c.code ASC'); //DESC
            $this->clips_model->clearSqlFilter();
        } else {

            # ????????? ?????????????? - ????????? ?????????? 25 ? ?????????? 25 ???? ?????????
            $filters = $this->getFilters();
            $dataSession = $this->session->userdata('submissionId');

            if (!empty($dataSession)) {
                $filters['submission_id'] = $dataSession;
            }
            if ($uid != 0) {
                $filters['client_id'] = $uid;
            } else {
                $filters['client_id'] = $this->clips_model->clipClient($selectedClipId);
            }

            $cliplog_page = $this->session->userdata('cliplog_page');
            $cliplog_page['offset'] = ($cliplog_page['offset']) ? $cliplog_page['offset'] : 0;
            $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 500;

            // reset amount of carousel items to display
            $cliplog_page['limit'] = $this::AMOUNT_OF_CAROUSEL_ITEMS;

            $carouselClipsIds = $this->clips_model->getCarouselClipsIds($selectedClipId, $uid, $filters, $cliplog_page['offset'], $cliplog_page['limit']);
//            echo '<pre>';
//            print_r($carouselClipsIds);die;

            $this->clips_model->build_filter_sql_backend(array('id' => $carouselClipsIds));

            //Need To Revert
            $clipsData['clips'] = $this->clips_model->get_clips_list_backend('en', NULL, 'ORDER BY c.code ASC'); // id
            $this->clips_model->clearSqlFilter();
        }
# ????? ???????? ????
        if ($selectedClipId) {
            $this->clips_model->build_filter_sql_backend(array('id' => $selectedClipId));
            $queryData = $this->clips_model->get_clips_list_backend();
            $this->clips_model->clearSqlFilter();
            $clipsData['clip'] = array_shift($queryData);
        }
        $cliplogEditor->replaceTemplateData($clipsData);

# ???????????, ????? ?? ??????? ?? ?????????? ????
        if ($cliplogRequest->isNextClipRequest() && $cliplogRequest->isSaveRequest()) {
            # ???????? ????, ??? ????? ???????? ??????? ?? ?????????? ????
            $cliplogEditor->pushTemplateData(
                array('next_clip_step' => TRUE)
            );
        }

        $next_clip_ids = $this->clips_model->getNextCarouselClipIdsOrderedByCode(
            $clipId,
            $this->uid, //userId
            1 //limit
        );

        if (!empty($next_clip_ids)){
            $cliplogEditor->replaceTemplateData(
                array('next_clip_id' => $next_clip_ids[0])
            );
        } else {
            $cliplogEditor->replaceTemplateData(
                // if no next clip id found use current clip id
                array('next_clip_id' => $clipId)
            );
        }

# ??????? ???????\???????? ???????? ???????? ?? ?????. ????
        $cliplogEditor->pushTemplateData(
            array('goto_next_active' => $cliplogEditor->getGotoNextStatus())
        );

# ???????? ??????????????? ?????? ???? ?????
        $viewData = /* ($_REQUEST['applied_keywords_set_id']==='')?array(): */
            $cliplogEditor->getTemplateData();
        $viewData['loggingData'] = $loggingData;
        if ($viewData['collection'] == '') {
            $viewData['collection'] = $this->collections_model->getDefaultClipCollectionName();
        }

# ????????? ?????????? ?? ??
        $hintsList = $this->hints_model->get_hints_list();
        if ($hintsList && is_array($hintsList)) {
            foreach ($hintsList as $hint) {
                $viewData['hints'][$hint['field']] = $hint['text'];
            }
        }

# ???????? ?????? ???????? ????? ?? ??
        if ($viewData['keywords']) {
            $selectedSectionsKeywords = array();
            $selectedKeywords = array();
            $keywordsIdsString = implode(', ', array_filter(array_keys($viewData['keywords']), function ($val) {
                return !empty($val);
            }));
            if ($keywordsIdsString != '') {
                $keywordsList = $this->cliplog_model->getKeywordsCustomList("lib_keywords.id IN ( {$keywordsIdsString} )");
            }
            if ($keywordsList && is_array($keywordsList)) {
                foreach ($keywordsList as $keyword) {
                    $selectedSectionsKeywords[$keyword['section']][] = $keyword;
                    $selectedKeywords[] = $keyword;
                }
            }
            $viewData['keywords_by_sections'] = $selectedSectionsKeywords;
            $viewData['all_keywords'] = $selectedKeywords;
        }
// Build started keywords
        $selectedClipId = $cliplogRequest->getSelectedClipId();
        $editorClipsIds = $cliplogRequest->getClipsIds();
        $clipId = ($selectedClipId) ? $selectedClipId : $editorClipsIds[0];
        $selectedKeywordsIds = (!empty($keywordsIdsString)) ? $keywordsIdsString : false;
        //TODO: refactor
//        foreach ($sectionList as $sectionName => $sectionTitle) {
//            $viewData['sectionsSelectedKeywords'][$sectionName] = $this->cliplog_model->get_keywords($sectionName, '', FALSE, '', $this->collections_model->getDefaultClipCollectionName(), true, $selectedKeywordsIds, $cliplogLogging->getActiveTemplateId());
//        }
        $viewData['sectionsSelectedKeywords'] = $this->cliplog_model->get_keywords_for_sections(
            $sectionList, // $sectionList
            '', //$selected
            FALSE, // $show_all
            '', // $on_match
            $this->collections_model->getDefaultClipCollectionName(), // $collection
            $selectedKeywordsIds, // $clipIds
            $cliplogLogging->getActiveTemplateId() //$templateId
        );

//Debug::Export( $this->cliplog_keywords_model->getDefaultTemplateKeywords() );
//Debug::Export( $this->cliplog_keywords_model->getDefaultTemplateKeywordsBySection( 'shot_type' ) );
# ???????? ??????????
        if (!empty($selectedClipId)) {
            $viewData['clips_Keywords'] = $this->clips_model->get_Clips_keywords($selectedClipId);
            $viewData['current_user_like'] = $this->clips_model->getClipCurrentUserLike($selectedClipId);
            $viewData['rating_result'] = $this->clips_model->getRatingData($selectedClipId, $uid);
        }


        $carouselClipsIds = $this->clips_model->getCarouselClipsIdsStills($filters, $cliplog_page['offset'], $cliplog_page['limit'], '', '', '', '', $selectedClipId);
        $viewData['nextclip'] = $this->clips_model->get_next_clip($this->session->userdata('uid'), $clipid, $carouselClipsIds);
        $viewData['previos_clip'] = $this->clips_model->get_previous_clip($this->session->userdata('uid'), $clipid, $carouselClipsIds);


        $viewData['collections'] = $this->cliplog_model->get_collections();
        $viewData['brands'] = $this->cliplog_model->get_brands();
        $viewData['clipBrand'] = $this->get_clip_brand($selectedClipId);
        $viewData['add_collection_list'] = $this->getCliplogAddCollections();
        $viewData['license_types'] = $this->cliplog_model->get_license_types();
        $viewData['source_formats'] = $this->formats_model->get_source_formats();
        $viewData['frame_rates'] = $this->formats_model->get_frame_rates();
        $viewData['master_formats'] = $this->formats_model->get_master_formats();
        $viewData['digital_file_formats'] = $this->formats_model->get_digital_file_formats();
        $viewData['delivery_categories'] = $this->deliveryoptions_model->get_delivery_categories_list('', 'description');
        $viewData['countries'] = $this->cliplog_model->get_countries();
        $viewData['cliplog_templates'] = $this->cliplog_templates_model->getLoggingTemplateList('id, name');
        $viewData['cliplog_keywords_sets'] = $this->cliplog_templates_model->getMetadataTemplateList('id, name');
        $viewData['camera_chip_sizes'] = $this->formats_model->get_camera_chip_sizes();
        $viewData['bit_depths'] = $this->formats_model->get_bit_depths();
        $viewData['color_spaces'] = $this->formats_model->get_color_spaces();
        $viewData['frame_sizes'] = $this->formats_model->get_frame_sizes();
        $viewData['check_reset'] = $this->input->post('overwrite_all');
        $viewData['file_compressions'] = $this->formats_model->get_file_compressions();
        $viewData['submission_codecs'] = $this->formats_model->get_submission_codecs();
        $viewData['labs'] = $this->labs_model->get_labs_list();
        $viewData['lang'] = $this->langs;
        $viewData['is_admin'] = $this->group['is_admin'];
        $viewData['only_admin_editable_fields'] = $this->clips_model->adminOnlyEditableFields();
        $viewData['back_url'] = ($this->session->userdata('cliplog_back_url')) ? $this->session->userdata('cliplog_back_url') : '/en/cliplog/view/';
        $viewData['back_title'] = ($this->session->userdata('cliplog_back_title')) ? $this->session->userdata('cliplog_back_title') : 'Back To Search';
// ?????????? ?? ???????? ???????? ?????


        //Change ClipStatus
        //


        $viewData['sequences'] = $this->sequences_model->get_sequences_list($provider_filter);
        $this->session->set_userdata('applied_keywords_set_id', $this->input->post('applied_keywords_set_id'));
        $this->path = 'ClipLog: Log Clip';
        $viewData['title_class'] = 'paddingdel';
        $this->cliplog_model->setBackendSession();
        $this->out($this->load->view('cliplog/edit', $viewData, TRUE));
    }

    private
    function getCliplogAddCollections()
    {
        $newCollectionList = array();
        $collectionList = $this->cliplog_model->get_collections();
        if ($collectionList) {
            foreach ($collectionList as $collection) {
                if (isset($collection['search_term']) && $collection['search_term']) {
                    $newCollectionList[] = $collection;
                }
            }
        }
        return $newCollectionList;
    }

    private
    function createHiddenAndTempKeywords()
    {
        $keywordsIdList = $this->input->post('keywords');
        $stateManager = new StateManager();
        if ($keywordsIdList && $stateManager->hasTemporaryKeywords($keywordsIdList)) {
            # ????? ????????? ??.??????, ????? ????????
            $replacedList = $stateManager->createKeywordsFromTemporary($keywordsIdList);
        }
        # ????? ????????? ? ???????? ???????? ??.??????
        if (isset($replacedList)) {
            $keywordsIdList = $replacedList;
        }
        if ($keywordsIdList && $stateManager->hasHiddenKeywords($keywordsIdList)) {
            # ????? ???????? ??????
            $keywordsIdList = $stateManager->createHiddenKeywordsFromTemporary($keywordsIdList);
        }
        return $keywordsIdList;
    }

    function clip()
    {
        if ($this->clip_id) {
            $clip = $this->clips_model->get_clip_info($this->clip_id, 'en', TRUE);
            $clip['rating_result'] = $this->clips_model->getRatingData($clip['id'], $clip['client_id']);
            $clipSequences = $this->clips_model->get_backend_lb_sequece($this->clip_id, 'en');
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

            $total_likes = $this->clips_model->getClipCurrentUserLike($this->clip_id);
            $keywords = $this->clips_model->getAllKeywordsByClipId($this->clip_id);
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
//            $sections = $this->sectionList;
//            foreach ($sections as $section => $title) {
//                $clip[$section] = $clip[$section] ? explode(',', $clip[$section]) : '';
//            }
            /* $clip[ 'primary_subject' ] = $clip[ 'primary_subject' ] ? explode( ',', $clip[ 'primary_subject' ] ) : '';
              $clip[ 'shot_type' ] = $clip[ 'shot_type' ] ? explode( ',', $clip[ 'shot_type' ] ) : '';
              $clip[ 'actions' ] = $clip[ 'actions' ] ? explode( ',', $clip[ 'actions' ] ) : '';
              $clip[ 'location' ] = $clip[ 'location' ] ? explode( ',', $clip[ 'location' ] ) : '';
              $clip[ 'other_subject' ] = $clip[ 'other_subject' ] ? explode( ',', $clip[ 'other_subject' ] ) : ''; */
            $content = $this->load->view('cliplog/clip', array('clip' => $clip, 'sequence' => $clipSequences, 'arrayClipbin' => $arrayClipbin, 'arraySequecne' => $arraySequecne, 'total_likes' => $total_likes), TRUE);
            $this->out($content);
        }
    }

    function ord()
    {
        $ids = $this->input->post('ord');

        if (is_array($ids) && count($ids)) {
            foreach ($ids as $id => $ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_pricing_terms', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/pricingterm/view');
    }

    function delete()
    {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->pricing_model->delete_terms($ids);
        redirect($this->langs . '/pricingterm/view');
    }

    function pricing()
    {
        $data = array();
        $this->path = 'Cliplog / Pricing';
        $rmData = $this->clips_model->get_rm_pricing_details();
        $rfData = $this->clips_model->get_rf_pricing_details();

        $data = array(
            'rmData' => $rmData,
            'rfData' => $rfData
        );
        $content = $this->load->view('cliplog/pricing', $data, TRUE);
        $this->out($content);
    }

    function get_limit()
    {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    /*
     * Use Clarifai SDK to generate keywords for clip
     */
    function generate_keywords()
    {
        $keywords = $this->clips_model->generateClarifaiKeywords($_REQUEST['id']);
        echo $keywords;
    }

    /*
    * Send rejecting request for current keyword to clarifai
    */
    function reject_keyword()
    {
        $keyword = $this->clips_model->rejectClarifaiKeyword($_REQUEST['keyword']);
        echo $keyword;
    }

    /*
    * Send approving request for keywords in list to clarifai
    */
    function approve_keywords()
    {
        $keywords = $this->clips_model->approveClarifaiKeyword($_REQUEST['keywords']);
        echo $keywords;
    }

    function check_details()
    {
        if (!$this->input->post('term_cat') ||
            !$this->input->post('territory') ||
            !$this->input->post('term') ||
            !$this->input->post('factor')
        ) {
            $this->error = $this->lang->line('empty_fields');
            return FALSE;
        }
        return TRUE;
    }

    function out($content = NULL, $pagination = NULL, $type = 1)
    {
        $this->builder->output(
            array(
                'content' => $content,
                'path' => $this->path,
                'pagination' => $pagination,
                'error' => $this->error,
                'message' => $this->message
            ), $type
        );
    }

    private
    function getFilters()
    {
        $filterParts = array();
        $filterParts[] = $this->session->userdata('cliplog_search_filter');
        $filterParts[] = $this->session->userdata('submissionId');
        $filterParts[] = $this->session->userdata('cliplog_clipbin_filter');
        $filterParts[] = $this->session->userdata('cliplog_other_filter');
        $filterParts[] = $this->session->userdata('cliplog_search_collection');
        $filterParts[] = $this->session->userdata('cliplog_search_license');
        $filterParts[] = $this->session->userdata('cliplog_search_price_level');
        $filterParts[] = $this->session->userdata('cliplog_search_format_category');
        $filterParts[] = $this->session->userdata('cliplog_search_active');
        $filterParts[] = $this->session->userdata('cliplog_search_wordsin');
        $filterParts[] = $this->session->userdata('cliplog_duration_filter');
        $filterParts[] = $this->session->userdata('cliplog_creation_date');
        $filter = array();
        foreach ($filterParts as $filterPart) {
            if ($filterPart && is_array($filterPart)) {
                $filter = array_merge($filter, $filterPart);
            }
        }

        unset($filterParts);
        return $filter;
    }

    private
    function updateFilters()
    {
        # ?????? ????? ???????
        $searchFilter = $this->session->userdata('cliplog_search_filter');
        if (isset($_REQUEST['update_filter'])) {
            $searchFilter = array();
            if ($_REQUEST['words']) {
                if (is_numeric($_REQUEST['words'])) {
                    $searchFilter['id'] = (int)$_REQUEST['words'];
                } else {
                    $searchFilter['words'] = $_REQUEST['words'];
                    $searchFilter['search_mode'] = 1;
                }
            }
            if ($_REQUEST['wordsin']) {
                if (is_numeric($_REQUEST['wordsin'])) {
                    $searchFilter['id'] = (int)$_REQUEST['wordsin'];
                } else {
                    $searchFilter['wordsin'] = $_REQUEST['wordsin'];
                    $searchFilter['search_mode'] = 1;
                }
            }
            if (!empty($searchFilter['words'])) {
                $this->session->set_userdata('cliplog_search_filter_words', $searchFilter['words']);
            }
            if (isset($_REQUEST['active'])) {
                $searchFilter['active'] = $_REQUEST['active'];
            }
            if (isset($_REQUEST['license'])) {
                $searchFilter['license'] = $_REQUEST['license'];
            }
            if (isset($_REQUEST['collection'])) {
                $searchFilter['collection'] = $_REQUEST['collection'];
            }
            if (isset($_REQUEST['price_level'])) {
                $searchFilter['price_level'] = $_REQUEST['price_level'];
            }
            if (isset($_REQUEST['format_category'])) {
                $searchFilter['format_category'] = $_REQUEST['format_category'];
            }
            if (isset($_REQUEST['actionAdmin'])) {
                $searchFilter['actionAdmin'] = $_REQUEST['actionAdmin'];
            }
            if (isset($_REQUEST['brand'])) {
                $searchFilter['brand'] = $_REQUEST['brand'];
            }
        }
        if ($this->group['is_editor'] && empty($searchFilter['client_id'])) {
            $searchFilter['client_id'] = $this->session->userdata('uid');
        }
        if (!empty($searchFilter) && isset($searchFilter)) {
            $filterKeys = array_keys($searchFilter);
            foreach ($filterKeys as $filterKey) {
                if (empty($searchFilter[$filterKey]) && $searchFilter[$filterKey] != 0) {
                    unset($searchFilter[$filterKey]);
                }
            }
        }
        $this->session->set_userdata('cliplog_search_filter', $searchFilter);

        # ?????? ????????
        $clipbinFilter = array();
        $clipbinRequest = new ClipbinRequest();
        if ($clipbinRequest->getClipbinSelected()->isSetSelectedClipbinId()) {
            $clipbinFilter['backend_clipbin_id'] = $clipbinRequest->getClipbinSelected()->getSelectedClipbinId();
            //$this->session->set_userdata( 'cliplog_search_filter_words');
        }
        $this->session->set_userdata('cliplog_clipbin_filter', $clipbinFilter);

        # ?????????? ???????
        $otherFilter = array();
        if ($_REQUEST['gallery']) {
            $otherFilter['gallery_id'] = (int)$_REQUEST['gallery'];
        }
        if ($_REQUEST['collection']) {
            $otherFilter['collection'] = $_REQUEST['collection'];
        }
        $sub = $this->session->userdata('cliplog_other_filter');
        $sub = $sub['submission_id'];
        if ($_REQUEST['submission'] || $sub) {
            $otherFilter['submission_id'] = ($_REQUEST['submission']) ? (int)$_REQUEST['submission'] : $sub;
        }
        if ($_REQUEST['sequence']) {
            $otherFilter['sequence_id'] = (int)$_REQUEST['sequence'];
        }
        if ($otherFilter) {
            $filterKeys = array_keys($otherFilter);
            foreach ($filterKeys as $filterKey) {
                if (empty($otherFilter[$filterKey]) && $otherFilter[$filterKey] != 0) {
                    unset($otherFilter[$filterKey]);
                }
            }
        }
        $this->session->set_userdata('cliplog_other_filter', $otherFilter);
    }

    function get_clips_limit()
    {
        $limit_start = intval($this->uri->segment(4));
        if (($sessionPerPage = $this->getClipbinClipsPerpage()) && $sessionPerPage > 0) {
            $perpage = $sessionPerPage;
        } else {
            $perpage = $this->settings['perpage'];
        }
        $this->session->set_userdata('cliplog_page', array('offset' => $limit_start, 'limit' => $perpage));
        return ' LIMIT ' . $limit_start . ', ' . $perpage;
    }

    function get_clips_Solrlimit()
    {
        $limit_start = intval($this->uri->segment(4));
        if (($sessionPerPage = $this->getClipbinClipsPerpage()) && $sessionPerPage > 0) {
            $perpage = $sessionPerPage;
        } else {
            $perpage = $this->settings['perpage'];
        }
        $this->session->set_userdata('cliplog_page', array('offset' => $limit_start, 'limit' => $perpage));
        return array('offset' => $limit_start, 'limit' => $perpage);
    }

    function set_group()
    {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

    function thumbgallery()
    {

        $came_from = parse_url($_SERVER['HTTP_REFERER']);


        $clipid = $this->uri->segment(5);
        $cliplog_page = $this->session->userdata('cliplog_page');
        $cliplog_page['offset'] = ($cliplog_page['offset']) ? $cliplog_page['offset'] : 0;
        $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
        if (!$page) {
            $page = 1;
        }

        if (!$per_page) {
            $per_page = 25;
        }

        $this->load->model('users_model');

        $user_data = $this->users_model->get_user($this->session->userdata('uid'));

        if (($this->clips_model->is_clip_owner($this->session->userdata('uid'), $clipid)) OR ($user_data['group_id'] == 1)) {

            $filters = $this->getFilters();

            // limit doesn't work
            $carouselClipsIds = $this->clips_model->getCarouselClipsIdsStills($filters, 0, $cliplog_page['limit'], '', '', '', '', $clipid);

            $clips['came_from'] = $came_from['path'];
            $clips['page'] = $page;
            $clips['per_page'] = $per_page;
            $clips['nextclip'] = $this->clips_model->get_next_clip($this->session->userdata('uid'), $clipid, $carouselClipsIds);
            $clips['previos_clip'] = $this->clips_model->get_previous_clip($this->session->userdata('uid'), $clipid, $carouselClipsIds);
            $clips['active_thumbnail'] = $this->cliplog_model->get_active_thumbnail_path($clipid);
            $clips['cid'] = $clipid;

            $thumbs = $this->cliplog_model->get_thumbs_list($clipid);
            $thumbs_array = array();
            $tmp_array = array();
            $tmp_array_sorted = array();

            if ($thumbs) {
                foreach ($thumbs as $k => $object) {
                    $tmp_array[] = $object['Key'];
                    $tmp = false;
                    //preg_match('/([^\/]+).jpg$/', $object['Key'], $tmp);
                    preg_match('/(\d*).jpg$/im', $object['Key'], $tmp);
                    if (isset($tmp[1]))
                        $tmp_array_sorted[$k] = $tmp[1];
                }
                asort($tmp_array_sorted, SORT_NUMERIC);
                foreach ($tmp_array_sorted as $k => $v) {
                    $thumbs_array[] = $tmp_array[$k];
                }
                $clip_dur = $this->clips_model->get_duration($clipid);

                if ($clip_dur == 0) {
                    $fps = 1;
                } else {
                    $fps = round(count($thumbs_array) / $clip_dur);
                }

                $filtered_thumbs = array();
                for ($i = 0; $i < count($thumbs_array); $i++) {
                    if ($i % $fps == 0) {
                        $filtered_thumbs[] = $thumbs_array[$i];
                    }
                }
            } else {
                $filtered_thumbs[] = '';
            }


            $clipdir = $this->db->query("SELECT location FROM lib_clips_res WHERE clip_id = '" . $clipid . "' AND resource = 'jpg' LIMIT 1")->result_array();

            $clipdir_addr = parse_url($clipdir[0]['location']);

            if ($clipdir_addr ['host']) {
                $location = ltrim($clipdir_addr['path'], "/");
            } else {
                $location = $clipdir[0]['location'];
            }

            if (!in_array($location, $filtered_thumbs)) {
                array_unshift($filtered_thumbs, $location);
            }

            $clips['clips'] = $filtered_thumbs;
            $clips['clip_code'] = $this->clips_model->get_clip_code($clipid);

            $this->out($this->load->view('cliplog/thumbgallery', $clips, TRUE));
        } else {
            show_404();
        }
    }

    public
    function loadthumbs()
    {
        $per_page = $this->input->post('per_page');
        $clipid = $this->input->post('cid');
        $page = $this->input->post('page_num');
        $path = $this->cliplog_model->get_active_thumbnail_path($clipid);

        if (!$page) {
            $page = 1;
        }

        if (!$per_page) {
            $per_page = 25;
        }

        $this->load->model('users_model');

        $user_data = $this->users_model->get_user($this->session->userdata('uid'));

        if (($this->clips_model->is_clip_owner($this->session->userdata('uid'), $clipid)) OR ($user_data['group_id'] == 1)) {


            $clips['page'] = $page;
            $clips['per_page'] = $per_page;
            $clips['nextclip'] = $this->clips_model->get_next_clip($this->session->userdata('uid'), $clipid);
            $clips['previos_clip'] = $this->clips_model->get_previous_clip($this->session->userdata('uid'), $clipid);
            $clips['active_thumbnail'] = $this->cliplog_model->get_active_thumbnail_path($clipid);
            $clips['cid'] = $clipid;
            $clips['path'] = $path;

            $thumbs = $this->cliplog_model->get_thumbs_list($clipid);

            $thumbs_array = array();

            foreach ($thumbs as $object) {
                $thumbs_array[] = $object['Key'];
            }

            $clip_dur = $this->clips_model->get_duration($clipid);

            $fps = round(count($thumbs_array) / $clip_dur);

            $filtered_thumbs = array();

            for ($i = 0; $i < count($thumbs_array); $i++) {
                if ($i % $fps == 0) {
                    $filtered_thumbs[] = $thumbs_array[$i];
                }
            }

            $clipdir = $this->db->query("SELECT location FROM lib_clips_res WHERE clip_id = '" . $clipid . "' AND resource = 'jpg' LIMIT 1")->result_array();

            if (!in_array($clipdir[0]['location'], $filtered_thumbs)) {
                $filtered_thumbs[] = $clipdir[0]['location'];
            }

            $clips['clips'] = $filtered_thumbs;
            $clips['clip_code'] = $this->clips_model->get_clip_code($clipid);

            $this->load->view('cliplog/thumbgallery_ajax', $clips);
        } else {
            show_404();
        }
    }

    public
    function changethumb()
    {

        $data = $this->input->post();

        $this->load->model('users_model');

        $user_data = $this->users_model->get_user($this->session->userdata('uid'));

        if ($this->clips_model->is_clip_owner($this->session->userdata('uid'), $data['cid']) OR ($user_data['group_id'] == 1)) {
            $this->cliplog_model->change_active_thumb($data['cid'], $data['path']);
        }
    }

//For ajax
    function keywords()
    {
        $keywords = $this->cliplog_model->getKeywordsForCliplog($this->input->post('section'), $this->input->post('selected'), $this->input->post('showall'), $this->input->post('onmatch'), $this->input->post('collection'));
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        $data['keywords'] = $keywords;
        $data['is_admin'] = $this->group['is_admin'];
        echo json_encode($data);
        exit();
    }

    function savekeyword()
    {
        $res = $this->cliplog_model->save_keyword($this->input->post('keyword'), $this->input->post('section'), $this->input->post('collection'));
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => $res ? 1 : 0, 'keyword_id' => $res));
        exit();
    }

    function switchoffkeyword()
    {
        $this->cliplog_model->switch_off_keyword($this->input->post('keyword'));
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => 1));
        exit();
    }

    function switchonkeyword()
    {
        $this->cliplog_model->switch_on_keyword($this->input->post('keyword'));
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => 1));
        exit();
    }

    function deletekeywords()
    {
        if (($ids = $this->input->post('id'))) {
            if (!is_array($ids))
                $ids = array($ids);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $check = $this->cliplog_model->get_keyword($id);
                    if ($check['provider_id'] === $this->session->userdata('client_uid') || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
                    ) {
                        $this->cliplog_model->delete_keyword($id);
                    }
                }
            }
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    function sectionoptions()
    {
        $options = $this->cliplog_model->get_section_options($this->input->post('section'), $this->input->post('selected'));
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($options);
        exit();
    }

    function savetemplate()
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8');

        $templateId = $this->cliplog_templates_model->createLoggingTemplateFromRawData($this->input->post());
        if ($templateId) {
            $cliplogEditor = new CliplogEditor();
            $cliplogEditor->getCliplogEditorLoggingTemplate()->setActiveTemplateId(
                $templateId
            );
            $templateData = $this->cliplog_templates_model->getLoggingTemplate($templateId, 'id, name');
            echo json_encode(array('status' => 1, 'template' => $templateData));
        } else {
            echo json_encode(array('status' => 0));
        }
        exit();
    }

    function gettemplates()
    {
        $templates = $this->cliplog_templates_model->getLoggingTemplateList('id, name');
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => $templates ? 1 : 0, 'templates' => $templates));
        exit();
    }

    /**
     * This handler called when user is click on the "Create" or "Save" button for keyword set on the cliplog Web UI
     *
     */
    function savekeywordsset()
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8');

        $templateData = $this->input->post();
        // echo '<pre>';
        // print_r($templateData);
        // die;

        $stateManager = new StateManager();
        $templateData = $stateManager->processInput($_POST);

        $templateData['keywords'] = $this->createHiddenAndTempKeywords();


        $templateId = $this->cliplog_templates_model->createMetadataTemplateFromRawData($templateData);
        if ($templateId) {
            $templateData = $this->cliplog_templates_model->getMetadataTemplate($templateId, 'id, name');
            echo json_encode(array('status' => 1, 'keywords_set' => $templateData));
        } else {
            echo json_encode(array('status' => 0));
        }
        exit();
    }

    function getkeywordssets()
    {
        $keywords_sets = $this->cliplog_templates_model->getMetadataTemplateList('id, name');
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => $keywords_sets ? 1 : 0, 'keywords_sets' => $keywords_sets));
        exit();
    }

    function getNextClipPath($clipId)
    {
        $filter = array();
        $filter['client_id'] = $_SESSION['uid'];
        $cliplog_page = $this->session->userdata('cliplog_page');
        $cliplog_page['offset'] = ($cliplog_page['offset']) ? $cliplog_page['offset'] : 0;
        $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 500;
        $carouselClipsIds = $this->clips_model->getCarouselClipsIdsStills($filter, 0, $cliplog_page['limit'], '', '', '', '', $clipId);

        $viewData['nextclip'] = $this->clips_model->get_next_clip($this->session->userdata('uid'), $clipId, $carouselClipsIds);

        echo $viewData['nextclip'][0]['id'];
    }

    function getPrevClipPath($clipId)
    {
        $filter = array();
        $filter['client_id'] = $_SESSION['uid'];
        $cliplog_page = $this->session->userdata('cliplog_page');
        $cliplog_page['offset'] = ($cliplog_page['offset']) ? $cliplog_page['offset'] : 0;
        $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 500;
        $carouselClipsIds = $this->clips_model->getCarouselClipsIdsStills($filter, 0, $cliplog_page['limit'], '', '', '', '', $clipId);

        $viewData['previos_clip'] = $this->clips_model->get_previous_clip($this->session->userdata('uid'), $clipId, $carouselClipsIds);
        echo $viewData['previos_clip'][0]['id'];
    }

    function getSessionAutoNextPageThumbgallery()
    {
        $data = $this->input->post();
        if ($data['status'] == 1) {
            $_SESSION['autoMoveCheck'] = 1;
        } else {
            $_SESSION['autoMoveCheck'] = '';
            unset($_SESSION['autoMoveCheck']);

        }
    }


    function getcarouselitems()
    {
        $beforeSession = json_encode($this->session->userdata);
        $this->cliplog_model->getBackendSession();
        //mail('dmitriy.klovak@boldendeavours.com','TEST:SESSION','BEFORE: '.$beforeSession."\n<br>AFTER: ".json_encode($this->session->userdata));
        $clipid = $this->uri->segment(5);
        if (($_REQUEST['side'] == 'left')) {
            $cliplog_page = $this->session->userdata('cliplog_page_carusel_left');
        } else {
            $cliplog_page = $this->session->userdata('cliplog_page_carusel');
        }
        if (!empty($_REQUEST['reset']))
            unset($cliplog_page);
        if (empty($_REQUEST['reset']) && !empty($cliplog_page)) {
            $cliplog_page['offset'] = (isset($cliplog_page['offset'])) ? ($_REQUEST['side'] == 'left') ? $cliplog_page['offset'] - $cliplog_page['limit'] : $cliplog_page['offset'] + $cliplog_page['limit'] : 0;
            $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
        } elseif (!empty($_REQUEST['reset'])) {
            $cliplog_page = $this->session->userdata('cliplog_page');
            $cliplog_page['offset'] = (isset($cliplog_page['offset'])) ? $cliplog_page['offset'] : 0;
            $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
        } else {
            $d[] = 2;
            $cliplog_page = $this->session->userdata('cliplog_page');
            $cliplog_page['offset'] = (isset($cliplog_page['offset'])) ? ($_REQUEST['side'] == 'left') ? $cliplog_page['offset'] - $cliplog_page['limit'] : $cliplog_page['offset'] + $cliplog_page['limit'] : 0;
            $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
        }

        if (($_REQUEST['side'] == 'left')) {
            $cliplog_page = $this->session->userdata('cliplog_page_carusel_left');

            if (isset($_SESSION['firstLeftCrousal'])) {

                $cliplog_page['offset'] = $cliplog_page['offset'] - $cliplog_page['limit'];
                $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;
            } else {
                $cliplog_page['offset'] = $cliplog_page['offset'];
                $cliplog_page['limit'] = ($cliplog_page['limit']) ? $cliplog_page['limit'] : 50;

            }
            $_SESSION['firstLeftCrousal'] = 1;
        }
        if ($cliplog_page['offset'] < 0) {
            $cliplog_page['offset'] = 0;
            $_REQUEST['reset'] = 'reset';
        }

        if ($_REQUEST['side'] == 'left') {
            $this->session->set_userdata('cliplog_page_carusel_left', $cliplog_page);
        } else {
            $this->session->set_userdata('cliplog_page_carusel', $cliplog_page);
        }
        $this->cliplog_model->setBackendSession();
        if ($cliplog_page['offset'] >= 0 && empty($_REQUEST['reset'])) {
            $filters = $this->getFilters();
            $offsetNew = $cliplog_page['offset'];
//            if ($cliplog_page['offset'] < 100) {
//                $offsetNew = $cliplog_page['offset'] . '0';
//            } else {
//                $offsetNew = $cliplog_page['offset']. '0'+100;
//            }


            $carouselClipsIds = $this->clips_model->getCarouselClipsIds($clipid, $this->session->userdata('uid'), $filters, $offsetNew, $cliplog_page['limit']);
            if (empty($carouselClipsIds)) {
                echo json_encode(array('status' => 0, 'debug' => json_encode([$cliplog_page, 'ids' => $carouselClipsIds])));
            } else {
                $items = $this->clips_model->get_clips_by_ids($carouselClipsIds);
                echo json_encode(array('status' => 1, 'limitImi' => $cliplog_page['limit'], 'items' => $items, 'debug' => json_encode([$cliplog_page, 'ids' => $carouselClipsIds])));
            }
        } else {
            echo json_encode(array('status' => 0, 'debug' => json_encode([$cliplog_page])));
        }
        exit();
    }

    private
    function setClipbinClipsOrder()
    {
        if (($orderType = $this->input->post('clips_sort_by'))) {
            switch ($orderType) {
                case 'id':
                    /*
                      case 'rating':
                     */
                case 'duration':
                    $this->session->set_userdata('clipbin-clips-order', $orderType);
            }
        }
    }

    private
    function getClipbinClipsOrder()
    {
        if (($orderType = $this->session->userdata('clipbin-clips-order'))) {
            switch ($orderType) {
                case 'id':
                    return ' ORDER BY c.code ASC'; //' ORDER BY c.id DESC';
                /*
                  case 'rating':
                  return ' ORDER BY c.duration ASC';
                 */
                case 'duration':
                    return ' ORDER BY c.duration ASC';
            }
        }
        return FALSE;
    }

    private
    function getSolrOrder()
    {
        if (($orderType = $this->session->userdata('clipbin-clips-order'))) {
            switch ($orderType) {
                case 'id':
                    //return array('_docid_ desc');
                    return array('code asc');
                case 'duration':
                    return array('duration asc');
                default :
                    array('code asc'); //return array('_docid_ desc');
            }
        }
        return array('code asc'); //array('_docid_ desc');
    }

    private
    function setClipbinClipsPerPage()
    {
        if (($perPage = $this->input->post('clips_on_page')) && $perPage > 0) {
            $this->session->set_userdata('clipbin-clips-perpage', $perPage);
        }
    }

    private
    function getClipbinClipsPerPage()
    {
        if (($perPage = $this->session->userdata('clipbin-clips-perpage')) && $perPage > 0) {
            return $perPage;
        }
        return 10;
    }

    /**
     * ????? ????????? ???????? ??? ????????? /cliplog/edit/*
     *
     * @ajax
     *
     * @return void
     */
    private
    function deletetemplate()
    {
        $response = array('status' => 0);
        $templateType = $this->input->post('type');
        $templateId = $this->input->post('id');
        if ($templateType && $templateId) {
            if ($templateType == 'logging') {
                $this->cliplog_templates_model->deleteLoggingTemplate($templateId);
                $response['status'] = 1;
            } elseif ($templateType == 'metadata') {
                $this->cliplog_templates_model->deleteMetadataTemplate($templateId);
                $response['status'] = 1;
            }
        }
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        die();
    }

    /**
     * ?????????? ?????? ??????? ??? ????????? /cliplog/edit/*
     *
     * @ajax
     *
     * @return void
     */
    private
    function updatetemplate()
    {
        $response = array('status' => 0);
        $templateType = $this->uri->segment(5);
        $templateId = $this->uri->segment(6);

        if ($templateType && $templateId) {
            if ($templateType == 'logging') {
                $this->cliplog_templates_model->updateLoggingTemplate($templateId, $this->input->post());
                $response = $this->cliplog_templates_model->getLoggingTemplate($templateId, 'id, name');
                $response['status'] = 1;
            } elseif ($templateType == 'metadata') {

                $templateData = $this->input->post();


                $stateManager = new StateManager();
                $templateData = $stateManager->processInput($_POST);

                $templateData['keywords'] = $this->createHiddenAndTempKeywords();

                $this->cliplog_templates_model->updateMetadataTemplate($templateId, $templateData);
                $response = $this->cliplog_templates_model->getMetadataTemplate($templateId, 'id, name');
                $response['status'] = 1;
            }
        }
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        die();
    }

    private
    function getAdvancedSearchFlags()
    {
        $valueList = array();
        $sessionData = $this->session->userdata('cliplog_search_filter');


        if ($sessionData) {
            $elementList = array('collection', 'active', 'license', 'price_level', 'format_category', 'brand', 'actionAdmin', 'wordsin');
            foreach ($elementList as $elementName) {
                if (isset($sessionData[$elementName]) && ($sessionData[$elementName] != '')) {
                    $valueList[$elementName] = $sessionData[$elementName];
                }
            }
        }

        return $valueList;
    }

    private
    function getSearchWords()
    {
        $filter = $this->session->userdata('cliplog_search_filter_words');
        if (isset($filter)) {
            return $filter;
        }
        return NULL;
    }

    private
    function getSerchWithinWords()
    {
        $filter = $this->getFilters();
        if (isset($filter['wordsin'])) {
            return $filter['wordsin'];
        }
        return NULL;
    }

    private
    function savegotonext()
    {
        $status = $this->input->post('status');
        switch ($status) {
            case 'y':
            case 'n':
                $cliplogEditor = new CliplogEditor();
                $cliplogEditor->setGotoNextStatus(($status === 'y') ? TRUE : FALSE);
                break;
        }
        $this->cliplog_model->setBackendSession();
        die();
    }

    private
    function getTemplateSectionList()
    {
        $templateId = $this->input->post('id');
        $cliplogEditor = new CliplogEditor();
        $cliplogLogging = $cliplogEditor->getCliplogEditorLoggingTemplate();
        $response = $cliplogLogging->getTemplateData($templateId);
        $is_admin = $this->group['is_admin'];
        $response['is_admin'] = $is_admin;
        if (!$is_admin)
            unset($response['brand']);
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        die();
    }

    public
    function get_archived_clips_provider_id($provider_id)
    {
        $clips = $this->db->query('SELECT id FROM lib_clips WHERE client_id = "' . $provider_id . '" AND active = 2 ')->result_array();

        $this->load->model('users_model');
        $user = $this->users_model->get_user_by_id($provider_id);

        if ($user['group_id'] == 1) {
            return array();
        } else {
            return $clips;
        }
    }

    public
    function get_provider_clips_count($provider_id)
    {
        $this->load->model('users_model');
        $user = $this->users_model->get_user_by_id($provider_id);

        if ($user['group_id'] == 1) {
            $result = $this->db->query("SELECT id FROM lib_clips ")->num_rows();
        } else {
            $result = $this->db->query("SELECT id FROM lib_clips WHERE client_id = '" . $provider_id . "' AND active  IN (0,1)")->num_rows();
        }

        return $result;
    }

    public
    function get_clip_brand($id)
    {
        if ($id) {
            return $this->db->query("SELECT brand FROM lib_clips WHERE id = " . $id . "")->result_array();
        } else {
            return 1;
        }
    }

    private function getFormatCategoryFilter()
    {
        $this->load->model('search_model');

        return $this->search_model->getFormatCategoryFilter();
    }

}
