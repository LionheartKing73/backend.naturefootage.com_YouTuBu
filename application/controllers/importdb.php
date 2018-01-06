<?php

require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;

class Importdb extends CI_Controller {
    const LIMIT=1000;
    const CLIP_LOOPS=322;
    public $csv=NULL;
    private $_XL_Importer=NULL;
    private $_curXLSfile=NULL;

    //public $res_types = array('thumb' => 0, 'preview' => 1, 'res' => 2, 'motion_thumb' => 0);
    public function dump($comment = '', $arg = ''){
        echo PHP_EOL . '-----' . PHP_EOL;
        echo $comment. PHP_EOL;
        if($arg) var_dump($arg);
        echo PHP_EOL . '-----' . PHP_EOL;
    }

    public $imported_options = array(
        'Composition' => 'shot_type',
        'Special' => 'shot_type',

        'Subject Categories' => 'subject_category',
        'Grouping' => 'subject_category',

        'Subject Browsing' => 'primary_subject',

        'Species and Other Subject Keywords' => 'other_subject',

        'Action Categories' => 'actions',
        'Other Action Keywords' => 'actions',

        'Color' => 'appearance',
        'Pattern' => 'appearance',

        'Time of Day' => 'time',
        'Season' => 'time',

        'Location' => 'location',
        'Region' => 'location',

        'Habitat' => 'habitat',
        'Topside-Underwater' => 'habitat',
        'Other Environmental Keywords' => 'habitat',

        'Concept' => 'concept',

        'Country' => 'country'
    );



    function __construct() {
        mail(
            'nikita.bunenkov@boldendeavours.com',
            'importdb controller ' . (isset($_SERVER['environment']) ? $_SERVER['environment'] : ''),
            'Request: ' . print_r($_REQUEST, true) . PHP_EOL
            . 'Server: ' . print_r($_SERVER, true) . PHP_EOL
        );
        parent::__construct();
//        $this->langs = $this->uri->segment(1);
//        $this->method = $this->uri->segment(3);
//        $this->load->model('clips_model');
//        $this->db_fs = $this->load->database('footagesearch', TRUE);
        $this->db_master = $this->load->database('master', TRUE);
//        $store = array();
//        require(__DIR__ . '/../config/store.php');
//        $this->store = $store;
//        $this->load->library('xl_importer');
    }

    function index() {
        show_404();
    }

    function clips(){
        echo 'Start', PHP_EOL;

        //$last_imported = 433799;
        $last_imported = 434849;
        //$last_imported_submission = 2908;
        $last_imported_submission = 3020;

//        $this->db->truncate('lib_clips');
//        $this->db->truncate('lib_clips_content');
//        $this->db->truncate('lib_clips_res');
//        $this->db->truncate('lib_clips_res_tasks');
//        $this->db->truncate('lib_clips_delivery_formats');
//        $this->db->truncate('lib_submissions');
//        $this->db->truncate('lib_clip_add_collections');
//        $this->db->truncate('lib_clip_keywords');

        //DROP TABLE lib_clips;
        //DROP TABLE lib_clips_content;
        //DROP TABLE lib_clips_res;
        //DROP TABLE lib_clips_res_tasks;
        //DROP TABLE lib_clips_delivery_formats;
        //DROP TABLE lib_submissions;
        //DROP TABLE lib_clip_add_collections;
        //DROP TABLE lib_clip_keywords;
        //DROP TABLE lib_keywords;
        //DROP TABLE lib_keywords_notvisible;
        //DROP TABLE lib_keywords_sets;
        //DROP TABLE lib_cliplog_metadata_templates;
        //DROP TABLE lib_cliplog_logging_keywords;
        //lib_backend_lb
        //lib_backend_lb_folders
        //lib_backend_lb_items


        // Delete last maybe not complete clip
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        $last_id = $res[0]['id'];

//        $this->db_master->delete('lib_clips', array('id' => $last_id));
//        $this->db_master->delete('lib_clips_content', array('clip_id' => $last_id));
//        $this->db_master->delete('lib_clips_res', array('clip_id' => $last_id));
//        $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $last_id));
//        $this->db_master->delete('lib_clip_add_collections', array('clip_id' => $last_id));
//        $this->db_master->delete('lib_clip_keywords', array('clip_id' => $last_id));

//        $this->db_master->where('id >', $last_imported);
//        $this->db_master->delete('lib_clips');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clips_content');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clips_res');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clips_res_tasks');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clips_delivery_formats');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clip_add_collections');
//        $this->db_master->where('clip_id >', $last_imported);
//        $this->db_master->delete('lib_clip_keywords');
//        $this->db_master->where('id >', $last_imported_submission);
//        $this->db_master->delete('lib_submissions');

        $time_start = microtime(1);
        $portion = 1000;
        $limit = 350000;
        $stop = false;
        $i = 0;
        $imported = 0;
        while(!$stop){
            $from = $portion * $i;
            $this->db_fs->limit($portion, $from);

            // All
            //$this->db_fs->where('id', 287551);
            $this->db_fs->where('id >', $last_imported);
            $this->db_fs->order_by('id', 'asc');
            $query = $this->db_fs->get('products');


            // Ultra HD Footage - only in additional collections
            // Collection ID 10
//            $this->db_fs->select('p.*');
//            $this->db_fs->from('products p');
//            $this->db_fs->join('product_collections pc', 'p.id = pc.product_id AND pc.collection_id = 10');
//            $this->db_fs->where('p.royalty_free', 't');
//            $this->db_fs->order_by('p.id', 'asc');
//            $query = $this->db_fs->get();


            // Ocean Footage
//            $this->db_fs->order_by('id', 'asc');
//            $this->db_fs->where('collection', 'Ocean Footage');
//            $query = $this->db_fs->get('products');

            // Ocean Footage
//            $this->db_fs->order_by('id', 'asc');
//            $this->db_fs->where('collection', 'Nature Footage');
//            $query = $this->db_fs->get('products');

            // Clips with different formats
//            $codes = array('ABE01', 'AC01', 'AG33', 'BC01', 'DF001', 'DG001', 'DZ02', 'JFR002', 'JHE01', 'JKL01', 'NZ47', 'PC05', 'PC12',
//                'PRE01', 'RS02', 'TO01', 'TW001', 'FSM05', 'WM01');
//            $this->db_fs->order_by('id', 'asc');
//            foreach ($codes as $key => $code) {
//                if ($key == 0)
//                    $this->db_fs->like('product', $code);
//                else
//                    $this->db_fs->or_like('product', $code);
//            }
//            $query = $this->db_fs->get('products');
//            $res = $query->result_array();

            // Clips of OFE
//            $this->db_fs->where('id >', 416464);
//            $this->db_fs->where('owner', 'offthefenceprod');
//            $this->db_fs->order_by('id', 'asc');
//            $query = $this->db_fs->get('products');

            $res = $query->result_array();

            if($res){
                foreach($res as $item){
                    if($this->save_clip($item)){
                        $imported++;
                        if(isset($limit) && $imported == $limit)
                            $stop = true;
                    }
                }
            }
            else{
                $stop = true;
            }
            $i++;
            echo $imported, PHP_EOL;
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;

        echo 'Finish: imported ' . $imported . ' clips, duration ' . $time, PHP_EOL;
    }
    function clipsByCode($code){
        $this->db_fs->limit(1);
        $this->db_fs->where('product', $code);
        $item = $this->db_fs->get('products')->result_array();
        $this->save_clip($item[0]);
        $this->dump(__FUNCTION__.' Clip "'.$code.'" saved to DB');
    }
    function clipsBySubmission($code){
        $this->db_fs->like('product', $code, 'after');
        $items = $this->db_fs->get('products')->result_array();
        $this->dump(__FUNCTION__.' FIND: '.count($items).' clips');
        if($items)
            foreach($items as $item){
                $this->save_clip($item);
                $this->dump(__FUNCTION__.' Clip "'.$item['product'].'" saved to DB');
            }else $this->dump(__FUNCTION__.' Submission '.$code.' not have clips');
    }

    function clips_update(){
        @set_time_limit(345600);
        $db_debug = $this->db_master->db_debug;
        $this->db_master->db_debug = FALSE;
        echo 'Start', PHP_EOL;
        $time_start = microtime(1);
        $portion = 1000;
        $stop = false;
        $i = 0;
        $updated = 0;
        while(!$stop){
            $from = $portion * $i;
            $this->db_fs->limit($portion, $from);
            // All
            $this->db_fs->order_by('id', 'asc');
            $this->db_fs->where('id >', 261672);
            $query = $this->db_fs->get('products');
            $res = $query->result_array();

            if($res){
                foreach($res as $item){
                    if($this->update_clip($item)){
                        $updated++;
                    }
                }
                echo $updated, PHP_EOL;
            }
            else{
                $stop = true;
            }
            $i++;
            echo $updated, '-', $item['id'], PHP_EOL;
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;
        $this->db_master->db_debug = $db_debug;
        echo 'Finish: imported ' . $updated . ' clips, duration ' . $time, PHP_EOL;
    }

    function providers(){
        echo 'Start', PHP_EOL;

        //$this->db->truncate('lib_users');

        $time_start = microtime(1);

        $this->db_fs->select('s.*, u.*');
        $this->db_fs->from('sellers s');
        $this->db_fs->join('users u', 's.username = u.username');
        $query = $this->db_fs->get();
        $res = $query->result_array();

        foreach($res as $provider){

            $this->db->select('id');
            $query = $this->db->get_where('lib_users', array('login' => $provider['username']));
            $check_res = $query->result_array();
            if($check_res[0]['id'])
                continue;

            $provider_opt = array();
            $query = $this->db_fs->get_where('user_opt', array('username' => $provider['username']));
            $res = $query->result_array();
            if($res){
                foreach($res as $opt){
                    $provider_opt[$opt['key']] = $opt['value'];
                }
            }

            $provider_data = array();
            $provider_data['group_id'] = 13; // Providers
            $name_parts = explode(' ', $provider['displayname']);
            $provider_data['fname'] = array_shift($name_parts);
            if($name_parts)
                $provider_data['lname'] = implode(' ', $name_parts);
            //$provider_data['bio'] = $provider['bio'];
            //$provider_data['company'] = isset($provider_opt['registration_company']) ? $provider_opt['registration_company'] : '';
            $provider_data['site'] = $provider['website'];
            //$provider_data['country'] = isset($provider_opt['registration_country']) ? $provider_opt['registration_country'] : '';
            $provider_data['email'] = $provider['realemail'];
            //$provider_data['phone'] = isset($provider_opt['registration_phone']) ? $provider_opt['registration_phone'] : '';
            $provider_data['login'] = $provider['username'];
            $provider_data['password'] = $provider['passwd'];
            $provider_data['active'] = $provider['live'];
            $provider_data['ctime'] = date('Y-m-d H:i:s', $provider['create_date']);
            $provider_data['last_login'] = date('Y-m-d H:i:s', $provider['last_date']);
            $provider_data['exclusive'] = $provider['exclusive'];
            foreach($provider_data as $key => $value){
                if($value == 'NULL' || empty($value))
                    unset($provider_data[$key]);
            }

            //$db_debug = $this->db_master->db_debug;
            //$this->db_master->db_debug = FALSE;
            $this->db_master->insert('lib_users', $provider_data);
            //$this->db_master->db_debug = $db_debug;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: duration ' . $time, PHP_EOL;
    }

    function frontends(){
        echo 'Start', PHP_EOL;

        //$this->db->truncate('lib_frontends');

        $time_start = microtime(1);

        $this->db_fs->select('bw.*, b.*');
        $this->db_fs->from('brands b');
        $this->db_fs->join('brand_websites bw', 'bw.brand = b.brand', 'left');
        $query = $this->db_fs->get();
        $res = $query->result_array();

        foreach($res as $item){

            if($item['brand_search']){
                if(substr($item['brand_search'], 0, 6) == 'owner='){
                    $brand_search_parts = explode('=', $item['brand_search']);
                    if(isset($brand_search_parts[1])){
                        $owner = $brand_search_parts[1];
                        $this->db->select('id');
                        $query = $this->db->get_where('lib_users', array('login' => $owner));
                        $res = $query->result_array();
                        if($res[0]['id']){
                            $item_data = array();
                            $item_data['provider_id'] = $res[0]['id'];
                            $item_data['host_name'] = $item['website_name'] ? $item['website_name'] : str_replace('http://', '', $item['brand_home']);
                            $item_data['name'] = $item['display_name'];
                            $item_data['status'] = 1;
                            $item_data['brand'] = $item['brand'];
                            foreach($item_data as $key => $value){
                                if($value == 'NULL' || empty($value))
                                    unset($item_data[$key]);
                            }
                            $this->db_master->insert('lib_frontends', $item_data);
                        }
                        else{
                            echo 'Frontend without owner', PHP_EOL;
                            print_r($item);
                        }
                    }
                }
                elseif(substr($item['brand_search'], 0, 11) == 'collection='){

                    $host_name = str_replace('www.', '', str_replace('http://', '', $item['brand_home']));
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_frontends', array('host_name' => $host_name));
                    $res2 = $query->result_array();
                    if(!$res2){
                        //$brand_search_parts = explode('=', $item['brand_search']);
                        //$collection_name =
                        $user_data = array();
                        $user_data['group_id'] = 13;
                        $user_data['fname'] = $item['display_name'];
                        $user_data['login'] = $item['brand'];
                        $user_data['password'] = $item['brand'];
                        $user_data['active'] = 1;
                        $this->db_master->insert('lib_users', $user_data);


                        $item_data['provider_id'] = $this->db_master->insert_id();
                        $item_data['host_name'] = $host_name;
                        $item_data['name'] = $item['display_name'];
                        $item_data['status'] = 1;
                        $item_data['brand'] = $item['brand'];
                        foreach($item_data as $key => $value){
                            if($value == 'NULL' || empty($value))
                                unset($item_data[$key]);
                        }
                        $this->db_master->insert('lib_frontends', $item_data);
                    }
                }
            }
            elseif($item['brand'] == 'footagesearch'){

                $host_name = str_replace('www.', '', str_replace('http://', '', $item['brand_home']));
                $this->db->select('id');
                $query = $this->db->get_where('lib_frontends', array('host_name' => $host_name));
                $res2 = $query->result_array();
                if(!$res2){
                    $user_data = array();
                    $user_data['group_id'] = 13;
                    $user_data['fname'] = $item['display_name'];
                    $user_data['login'] = $item['brand'];
                    $user_data['password'] = $item['brand'];
                    $user_data['active'] = 1;
                    $this->db_master->insert('lib_users', $user_data);


                    $item_data['provider_id'] = $this->db_master->insert_id();
                    $item_data['host_name'] = $host_name;
                    $item_data['name'] = $item['display_name'];
                    $item_data['status'] = 1;
                    $item_data['brand'] = $item['brand'];
                    foreach($item_data as $key => $value){
                        if($value == 'NULL' || empty($value))
                            unset($item_data[$key]);
                    }
                    $this->db_master->insert('lib_frontends', $item_data);
                }
            }
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: duration ' . $time, PHP_EOL;
    }

    function collections(){
        echo 'Start', PHP_EOL;

        $this->db->truncate('lib_collections');

        $time_start = microtime(1);

        $query = $this->db_fs->get_where('collections', array('active' => 't'));
        $res = $query->result_array();

        $imported = 0;
        foreach($res as $item){
            $item_data = array();
            $item_data['id'] = $item['collection_id'];
            $item_data['name'] = $item['collection'];
            $item_data['abbr'] = $item['collection_abbr'];
            $item_data['active'] = 1;
            foreach($item_data as $key => $value){
                if($value == 'NULL' || empty($value))
                    unset($item_data[$key]);
            }
            $this->db_master->insert('lib_collections', $item_data);
            $imported++;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function users(){
        echo 'Start', PHP_EOL;

        //$this->db->truncate('lib_users');

        $time_start = microtime(1);

        $this->db_fs->select('u.*, s.*, s.username seller_username, u.username username');
        $this->db_fs->from('users u');
        $this->db_fs->join('sellers s', 's.username = u.username', 'left');
        //$this->db_fs->where('u.username', 'ferraro');
        //$this->db_fs->limit(1);
        $query = $this->db_fs->get();
        $res = $query->result_array();

        $imported = 0;
        foreach($res as $user){

            // Only customers
            if(!$user['seller_username'] && $user['username']){

                // Import only not existen users
                $this->db->select('id');
                $query = $this->db->get_where('lib_users', array('login' => $user['username']));
                $check_res = $query->result_array();
                if($check_res[0]['id'])
                    continue;

                $user_opt = array();
                $query = $this->db_fs->get_where('user_opt', array('username' => $user['username']));
                $res = $query->result_array();
                if($res){
                    foreach($res as $opt){
                        if($opt['value'])
                            $user[$opt['key']] = $opt['value'];
                    }
                }

                $user_data = array();
                $user_data['group_id'] = 4;
                $user_data['fname'] = isset($user['registration_fname']) ? $user['registration_fname'] : '';
                $user_data['lname'] = isset($user['registration_lname']) ? $user['registration_lname'] : '';
                $user_data['bio'] = $user['bio'];
                $user_data['company'] = isset($user_opt['registration_company']) ? $user_opt['registration_company'] : '';
                $user_data['site'] = $user['website'];
                $user_data['country'] = isset($user_opt['registration_country']) ? $user_opt['registration_country'] : '';
                $user_data['email'] = $user['realemail'];
                $user_data['phone'] = isset($user_opt['registration_phone']) ? $user_opt['registration_phone'] : '';
                $user_data['login'] = $user['username'];
                $user_data['password'] = $user['passwd'];
                $user_data['active'] = $user['live'];
                $user_data['ctime'] = date('Y-m-d H:i:s', $user['create_date']);
                $user_data['last_login'] = date('Y-m-d H:i:s', $user['last_date']);
                $user_data['exclusive'] = $user['exclusive'];
                $user_data['imported']  = $user['fmpimport'] == 'imported' ? 1 : 0;

                if(isset($user['registerbrand']) && $user['registerbrand']) {
                    $this->db->select('id, provider_id');
                    $query = $this->db->get_where('lib_frontends', array('brand' => $user['registerbrand']));
                    $res2 = $query->result_array();
                    if($res2){
                        $user_data['provider_id'] = $res2[0]['provider_id'];
                        $user_data['register_frontend'] = $res2[0]['id'];
                    };
                }
                else{
                    echo 'Customer without provider', PHP_EOL;
                    print_r($user);
                }

                foreach($user_data as $key => $value){
                    if($value == 'NULL' || empty($value))
                        unset($user_data[$key]);
                }
                //$db_debug = $this->db_master->db_debug;
                //$this->db_master->db_debug = FALSE;
                $this->db_master->insert('lib_users', $user_data);
                //$this->db_master->db_debug = $db_debug;
                $imported++;
                echo $imported, PHP_EOL;
            }
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function mark_imported_users() {
        echo 'Start', PHP_EOL;
        $time_start = microtime(1);
        $this->db_fs->select('username, fmpimport');
        $query = $this->db_fs->get('users');
        $res = $query->result_array();

        $imported = 0;
        foreach ($res as $user) {
            $this->db->select('id');
            $query2 = $this->db->get_where('lib_users', array('login' => $user['username']));
            $res2 = $query2->result_array();
            if(!$res2[0]['id'])
                continue;

            $this->db_master->where('id', $res2[0]['id']);
            $this->db_master->update('lib_users', array('imported' => ($user['fmpimport'] == 'imported' ? 1 : 0)));

            $imported++;
            echo $imported, PHP_EOL;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function mark_imported_providers() {
        $questions_map = array(
            'q_subjects' => 'question1',
            'q_hrs_select' => 'question2',
            'q_hrs_assembled' => 'question3',
            'q_yrs_filming' => 'question4',
            'q_professional' => 'question5',
            'q_sold_stock' => 'question6',
            'q_agency' => 'question7',
            'q_agency_list' => 'question8',
            'q_camera' => 'question9',
            'q_editing' => 'question10',
            'q_time_submit' => 'question11',
            'q_contact_method' => 'question12'
        );
        echo 'Start', PHP_EOL;
        $time_start = microtime(1);
        $this->db_fs->select('username, fmpimport, response_id');
        $this->db_fs->where('formname', 'survey-provider');
        $query = $this->db_fs->get('form_responses');
        $res = $query->result_array();

        $imported = 0;
        foreach ($res as $user) {
            $this->db->select('id');
            $query2 = $this->db->get_where('lib_users', array('login' => $user['username']));
            $res2 = $query2->result_array();
            if(!$res2[0]['id'])
                continue;

            $query3 = $this->db_fs->get_where('form_responses_data', array('response_id' => $user['response_id']));
            $res3 = $query3->result_array();
            foreach ($res3 as $meta) {
                if (array_key_exists($meta['field_name'], $questions_map) && $meta['field_value']) {
                    $this->db_master->insert('lib_users_meta', array(
                        'user_id' => $res2[0]['id'],
                        'meta_key' => $questions_map[$meta['field_name']],
                        'meta_value' => $meta['field_value'],
                    ));
                }
            }


            $this->db_master->where('id', $res2[0]['id']);
            $this->db_master->update('lib_users', array('provider_imported' => ($user['fmpimport'] == 'imported' ? 1 : 0)));

            $imported++;
            echo $imported, PHP_EOL;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function orders(){

        $payment_statuses_map = array(
            'PAID' => 3,
            'NOT PAID' => 1,
            'FAILED' => 2
        );

        $admin_statuses_map = array(
            'REASSIGNED' => 'Reassigned',
            'ACCEPTED' => 'Accepted online',
            'ACCEPTED_OFFLINE' => 'Accepted offline',
            'FILLOUT' => 'Fillout'
        );

        $client_statuses_map = array(
            'review' => 1,
            'complete' => 2,
            'hold' => 3
        );

        $release_statuses_map = array(
            'PREAPPROVED NO PAYMENT' => 'Preapproved no payment',
            'PREPPROVE NO PAYMENT' => 'Preapproved no payment',
            'PREAPPROVED' => 'Preapproved',
            'APPROVED' => 'Approved',
            'NOT APPROVED' => 'Not approved'
        );

        echo 'Start', PHP_EOL;

        $last_imported = 26468243;

//        $this->db_master->truncate('lib_orders');
//        $this->db_master->truncate('lib_order_license');
//        $this->db_master->truncate('lib_order_shipping');
//        $this->db_master->truncate('lib_order_billing');
//        $this->db_master->truncate('lib_orders_items');

        $time_start = microtime(1);

        $this->db_master->where('id >', $last_imported);
        $this->db_master->delete('lib_orders');
        $this->db_master->where('order_id >', $last_imported);
        $this->db_master->delete('lib_order_license');
        $this->db_master->where('order_id >', $last_imported);
        $this->db_master->delete('lib_order_shipping');
        $this->db_master->where('order_id >', $last_imported);
        $this->db_master->delete('lib_order_billing');
        $this->db_master->where('order_id >', $last_imported);
        $this->db_master->delete('lib_orders_items');

        $this->db_fs->where('orderid >', $last_imported);
        $query = $this->db_fs->get('orders');
        $res = $query->result_array();

        $imported = 0;
        foreach($res as $item){

            $order_opt = array();
            $query = $this->db_fs->get_where('order_options', array('orderid' => $item['orderid']));
            $res2 = $query->result_array();
            if($res2){
                foreach($res2 as $opt){
                    if($opt['value'])
                        $order_opt[$opt['name']] = $opt['value'];
                }
            }

            $item_data = array();
            $item_data['id'] = $item['orderid'];
            // Customer
            if($item['username']) {
                $this->db->select('id');
                $query = $this->db->get_where('lib_users', array('login' => $item['username']));
                $res3 = $query->result_array();
                if($res3)
                    $item_data['client_id'] = $res3[0]['id'];
            }
            // Statuses
            if(isset($payment_statuses_map[$item['payment_status']]))
                $item_data['status'] = $payment_statuses_map[$item['payment_status']];
            if(isset($admin_statuses_map[$item['status']]))
                $item_data['admin_status'] = $admin_statuses_map[$item['status']];
            if(isset($client_statuses_map[$item['review_status']]))
                $item_data['client_status'] = $client_statuses_map[$item['review_status']];
            if(isset($release_statuses_map[$item['approval_status']]))
                $item_data['release_status'] = $release_statuses_map[$item['approval_status']];

            $item_data['download_email_status'] = $item['ftp_instructions_sent'] == 'yes' ? 'Sent' : 'Not sent';
            $item_data['resume_order_email_status'] = $item['resume_instructions_sent'] == 'yes' ? 'Sent' : 'Not sent';

            // Sum and Total
            if(isset($order_opt['clip_subtotal']))
                $item_data['sum'] = $order_opt['clip_subtotal'];
            if(isset($order_opt['lab_subtotal']))
                $item_data['delivery_cost'] = $order_opt['lab_subtotal'];
            if(isset($order_opt['order_total_cost']))
                $item_data['total'] = $order_opt['order_total_cost'];

            $item_data['ctime'] = date('Y-m-d H:i:s', $item['date']);

            if($item['brand']) {
                $this->db->select('id');
                $query = $this->db->get_where('lib_frontends', array('brand' => $item['brand']));
                $res4 = $query->result_array();
                if($res4)
                    $item_data['frontend_id'] = $res4[0]['id'];
            }

            // Billing
            $query = $this->db_fs->get_where('order_billing', array('orderid' => $item['orderid']));
            $billing = $query->result_array();
            if ($billing) {
                $billing_data['order_id'] = $billing[0]['orderid'];
                $billing_data['name'] = $billing[0]['name'] ? $billing[0]['name'] : '';
                $billing_data['company'] = $billing[0]['company'] ? $billing[0]['company'] : '';
                $billing_data['street1'] = $billing[0]['street1'] ? $billing[0]['street1'] : '';
                $billing_data['street2'] = $billing[0]['street2'] ? $billing[0]['street2'] : '';
                $billing_data['city'] = $billing[0]['city'] ? $billing[0]['city'] : '';
                $billing_data['state'] = $billing[0]['state'] ? $billing[0]['state'] : '';
                $billing_data['zip'] = $billing[0]['zip'] ? $billing[0]['zip'] : '';
                $billing_data['country'] = $billing[0]['country'] ? $billing[0]['country'] : '';
                $billing_data['phone'] = $billing[0]['phone'] ? $billing[0]['phone'] : '';
                $this->db_master->insert('lib_order_billing', $billing_data);
            }

            // Shipping
            $query = $this->db_fs->get_where('order_shipping', array('orderid' => $item['orderid']));
            $shipping = $query->result_array();
            if ($shipping) {
                $shipping_data['order_id'] = $shipping[0]['orderid'];
                $shipping_data['name'] = $shipping[0]['name'] ? $shipping[0]['name'] : '';
                $shipping_data['company'] = $shipping[0]['company'] ? $shipping[0]['company'] : '';
                $shipping_data['street1'] = $shipping[0]['street1'] ? $shipping[0]['street1'] : '';
                $shipping_data['street2'] = $shipping[0]['street2'] ? $shipping[0]['street2'] : '';
                $shipping_data['city'] = $shipping[0]['city'] ? $shipping[0]['city'] : '';
                $shipping_data['state'] = $shipping[0]['state'] ? $shipping[0]['state'] : '';
                $shipping_data['zip'] = $shipping[0]['zip'] ? $shipping[0]['zip'] : '';
                $shipping_data['country'] = $shipping[0]['country'] ? $shipping[0]['country'] : '';
                $shipping_data['phone'] = $shipping[0]['phone'] ? $shipping[0]['phone'] : '';
                $this->db_master->insert('lib_order_shipping', $shipping_data);
            }

            // License
            $query = $this->db_fs->get_where('order_licensee', array('orderid' => $item['orderid']));
            $license = $query->result_array();
            $this->db_fs->select('production_title, production_description, production_territory');
            $query = $this->db_fs->get_where('order_select', array('orderid' => $item['orderid']));
            $production = $query->result_array();
            if ($license) {
                $license_data['order_id'] = $license[0]['orderid'];
                $license_data['name'] = $license[0]['name'] ? $license[0]['name'] : '';
                $license_data['company'] = $license[0]['company'] ? $license[0]['company'] : '';
                $license_data['street1'] = $license[0]['street1'] ? $license[0]['street1'] : '';
                $license_data['street2'] = $license[0]['street2'] ? $license[0]['street2'] : '';
                $license_data['city'] = $license[0]['city'] ? $license[0]['city'] : '';
                $license_data['state'] = $license[0]['state'] ? $license[0]['state'] : '';
                $license_data['zip'] = $license[0]['zip'] ? $license[0]['zip'] : '';
                $license_data['country'] = $license[0]['country'] ? $license[0]['country'] : '';
                $license_data['phone'] = $license[0]['phone'] ? $license[0]['phone'] : '';
                if ($production) {
                    $license_data['production_title'] = $production[0]['production_title'] ? $production[0]['production_title'] : '';
                    $license_data['production_description'] = $production[0]['production_description'] ? $production[0]['production_description'] : '';
                    $license_data['production_territory'] = $production[0]['production_territory'] ? $production[0]['production_territory'] : '';
                }
                $this->db_master->insert('lib_order_license', $license_data);
            }

            $this->db_master->insert('lib_orders', $item_data);
            $imported++;
            echo $imported, PHP_EOL;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function order_items(){

        $delivery_methods_map = array(
            'DOWNLOAD' => 1,
            'TAPE' => 2,
            'HARDDRIVE' => 3
        );

        $delivery_processes_map = array(
            'Lab' => 'Manual',
            'Download' => 'Manual',
            'Compressor' => 'Automated'
        );

        echo 'Start', PHP_EOL;
        $last_imported = 26468243;
        //$this->db_master->truncate('lib_orders_items');

        $time_start = microtime(1);

        $this->db_fs->where('orderid >', $last_imported);
        $this->db_fs->order_by('orderid', 'asc');
        $query = $this->db_fs->get('order_items');
        $res = $query->result_array();

        $imported = 0;
        foreach($res as $item){

            $order_item_opt = array();
            $query = $this->db_fs->get_where('order_item_options', array('orderid' => $item['orderid'], 'product' => $item['product']));
            $res2 = $query->result_array();
            if($res2){
                foreach($res2 as $opt){
                    if($opt['value'])
                        $order_item_opt[$opt['name']] = $opt['value'];
                }
            }

            $item_data = array();
            $item_data['order_id'] = $item['orderid'] ? $item['orderid'] : 0;
            // TODO Clip
            if($item['product']) {
                $this->db->select('id');
                $query = $this->db->get_where('lib_clips', array('code' => $item['product']));
                $res3 = $query->result_array();
                if($res3)
                    $item_data['item_id'] = $res3[0]['id'];
            }

            if(isset($order_item_opt['delivery_method']) && isset($delivery_methods_map[$order_item_opt['delivery_method']]))
                $item_data['dm_id'] = $delivery_methods_map[$order_item_opt['delivery_method']];

            if(isset($order_item_opt['delivery_option']))
                $item_data['df_id'] = $order_item_opt['delivery_option'];

            if(isset($order_item_opt['display_delivery_option']) && $order_item_opt['display_delivery_option'])
                $item_data['df_description'] = $order_item_opt['display_delivery_option'];
            elseif(isset($order_item_opt['delivery_format_text']) && $order_item_opt['delivery_format_text'])
                $item_data['df_description'] = $order_item_opt['delivery_format_text'];

            if(isset($order_item_opt['discount']))
                $item_data['discount'] = $order_item_opt['discount'];

            if(isset($order_item_opt['display_pricing_description_1']))
                $item_data['allowed_use'] = $order_item_opt['display_pricing_description_1'];

            if(isset($order_item_opt['license_category']) && isset($order_item_opt['license_use_1'])
                && isset($order_item_opt['license_territory_1']) && isset($order_item_opt['license_term_1'])){

                $this->db->select('terms_cat');
                $query = $this->db->get_where('lib_pricing_use', array(
                    'category' => $order_item_opt['license_category'],
                    'use' => $order_item_opt['license_use_1']
                ));
                $res6 = $query->result_array();
                if($res6){
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_pricing_terms', array(
                        'term_cat' => $res6[0]['terms_cat'],
                        'territory' => $order_item_opt['license_territory_1'],
                        'term' => $order_item_opt['license_term_1']
                    ));
                    $res7 = $query->result_array();
                    if($res7)
                        $item_data['license_use'] = $res7[0]['id'];
                }
            }

            if(isset($order_item_opt['orderware_framerate_text'])){
                $this->db->select('id');
                $query = $this->db->get_where('lib_pricing_custom_frame_rates', array('format' => $order_item_opt['orderware_framerate_text'], 'media' => 'File'));
                $res4 = $query->result_array();
                if($res4)
                    $item_data['frame_rate_id'] = $res4[0]['id'];
            }

            $item_data['duration'] = $item['select_duration'];

            $query = $this->db_fs->get_where('order_fullfillment', array('orderid' => $item['orderid'], 'product' => $item['product']));
            $res5 = $query->result_array();
            if($res5){
                $item_data['upload_status'] = ucfirst(strtolower($res5[0]['status']));
                $item_data['downloaded'] = $res5[0]['download_status'] == 'DOWNLOADED' ? 1 : 0;
                if(isset($delivery_processes_map[$res5[0]['method']]))
                    $item_data['delivery_process'] = $delivery_processes_map[$res5[0]['method']];
            }

            $item_data['uploaded'] = 1;

            if($item['clip_cost'] && $item['select_duration'])
                $item_data['price'] = $item['clip_cost'] / $item['select_duration'];

            $this->db_master->insert('lib_orders_items', $item_data);
            $imported++;
            echo $imported, PHP_EOL;
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    function clip_keywords(){

        echo 'Start', PHP_EOL;

        //$last_imported_clip = 433799;
        $last_imported_clip = 434849;
        //$last_keyword_id = 142678;

//        $this->db->truncate('lib_clip_keywords');
//        $this->db->truncate('lib_keywords');
//        $this->db->truncate('lib_keywords_notvisible');
//        $this->db->truncate('lib_keywords_sets');
//        $this->db->truncate('lib_cliplog_metadata_templates');
//        $this->db->truncate('lib_cliplog_logging_keywords');

        $time_start = microtime(1);

        $this->db_fs->select('key');
        $this->db_fs->where('product_id >', $last_imported_clip);
        $this->db_fs->distinct();
        $query = $this->db_fs->get('product_opt');
        $res = $query->result_array();

        $keywords = array();

        foreach($res as $item){
            if($item['key'] && array_key_exists($item['key'], $this->imported_options)) {
                $this->db_fs->select('value');
                $this->db_fs->distinct();
                $query = $this->db_fs->get_where('product_opt', array('key' => $item['key']));
                $res2 = $query->result_array();
                foreach($res2 as $item_value){
                    if($item['key'] == 'Country'){
                        $this->db->select('id');
                        $query = $this->db->get_where('lib_countries', array('name' => $item_value['value']));
                        $res3 = $query->result_array();
                        if(!$res3){
                            $this->db_master->insert('lib_countries', array('name' => $item_value['value']));
                        }
                    }
                    else{
                        $sub_keywords = array();
                        if(strpos($item_value['value'], ';') !== false){
                            $sub_keywords = explode(';', $item_value['value']);
                        }
                        elseif(strpos($item_value['value'], ',') !== false){
                            $sub_keywords = explode(',', $item_value['value']);
                        }
                        if($sub_keywords){
                            foreach($sub_keywords as $sub_keyword){
                                $keywords[$this->imported_options[$item['key']]][] = trim($sub_keyword);
                            }
                        }
                        else
                            $keywords[$this->imported_options[$item['key']]][] = trim($item_value['value']);
                    }
                }
            }
        }

        foreach($keywords as $section => $section_keywords){
            $unique_section_keywords = array_unique($section_keywords);
            foreach($unique_section_keywords as $keyword){
                $this->db->select('id');
                $query = $this->db->get_where('lib_keywords', array('section' => $section, 'keyword' => $keyword, 'old' => 1));
                $res4 = $query->result_array();
                if (!$res4)
                    $this->db_master->insert('lib_keywords', array('section' => $section, 'keyword' => $keyword, 'old' => 1));
            }
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: duration ' . $time, PHP_EOL;
    }

    public function doc_keywords(){
        $keywords['concept']['All'] = "Affectionate
Aggressive
Bloody
Busy
Calm
Cautious
Competitive
Cooperative
Cuddly
Curious
Cute
Cycle
Death
Decay
Defeat
Desolate
Destructive
Determination
Diversity
Dominance
Expansive
Exploration
Family
Fast
Fatigue
Fearful
Fun
Graceful
Growth
History
Humorous
Indulgence
Intimacy
Learning
Leisure
Loss
Love
Lush
Mysterious
Nurture
Old
Pain
Partnership
Powerful
Precision
Pristine
Protective
Pursuit
Religion
Repetition
Risk
Science
Slow
Somber
Struggle
Success
Tool Use
Tradition
Ugly";

        foreach($keywords as $section_key => $section){
            foreach($section as $collection_key => $collection){
                $keywords_arr = explode("\n", $collection);
                foreach($keywords_arr as $keyword){
                    $this->db_master->insert('lib_keywords', array('section' => $section_key, 'collection' => ($collection_key == 'All' ? '' : $collection_key), 'keyword' => $keyword));
                }
            }
        }

    }

    public function provider_credits() {
        $time_start = microtime(1);

        $this->db_fs->select('username, provider_credits');
        $query = $this->db_fs->get('sellers');
        $res = $query->result_array();
        foreach($res as $item){
            $this->db->select('id, provider_credits');
            $query = $this->db->get_where('lib_users', array('login' => $item['username']));
            $res2 = $query->result_array();
            if($res2 && !$res2[0]['provider_credits']){
                $this->db_master->where('id', $res2[0]['id']);
                $this->db_master->update('lib_users', array('provider_credits' => $item['provider_credits']));
            }
        }

        $time_end = microtime(1);
        $time = $time_end - $time_start;
        echo 'Finish: duration ' . $time, PHP_EOL;
    }

    private function save_clip($item){

        $query = $this->db->get_where('lib_clips', array('id' => $item['id']));
        $res = $query->result_array();
        if($res[0]['id'])
            return;

//        echo '<pre>';
//        preg_match('/^(.*) (\d+)(i|p|fps)$/', '4K HD (3840x2160) 30fps', $matches);
//        print_r($matches);
//        preg_match('/^(\d+)(i|p)(\d+)/', '723p50', $matches);
//        print_r($matches);
//        exit();

        $data = array();
        $user_id = 0;
        // Get owner
        $this->db_fs->select('realemail');
        $query = $this->db_fs->get_where('users', array('username' => $item['owner']));
        $res = $query->result_array();
        $this->db->select('id');
        if($res[0]['realemail']){
            $res=$this->db->get_where('lib_users', array('email' => $res[0]['realemail']))->result_array();
            if(empty($res[0]['id']))
                $res=$this->db->get_where('lib_users', array('login' => $item['owner']))->result_array();
        }else
            $res=$this->db->get_where('lib_users', array('login' => $item['owner']))->result_array();

        if($res[0]['id']){
            $user_id = $res[0]['id'];
            $data['client_id'] = $user_id;
        }
        else{
            echo 'Clip without owner', PHP_EOL;
            //print_r($item);
            $this->dump(__FUNCTION__,$item);
            die();
        }

//        $data['client_id'] = '1000005';


        //Save submission
        $submission_date_arr = explode(' ', $item['created']);
        $data['submission_id'] = $this->save_submission($item['tape'], $user_id, $submission_date_arr[0]);

        $data['id'] = $item['id']; //
        $data['code'] = $item['product']; //
        $data['license'] = $item['royalty_free'] == 'f' ? 2 : 1; //
        $data['duration'] = $item['duration']; //
        $data['creation_date'] = date('Y-m-d H:i:s', $item['add_date']); //
        $film_date_parts = explode('/', $item['film_date']);
        if(count($film_date_parts) == 2 && is_numeric($film_date_parts[0]) && is_numeric($film_date_parts[1]))
            $data['film_date'] = date('Y-m-d', mktime(0, 0, 0, $film_date_parts[0], 1, $film_date_parts[1])); //
        $data['aspect'] = $item['aspect']; //
        $data['viewed'] = $item['views']; //
        //$data['active'] = $item['status'] == 'online' ? 1 : 0; //
        switch ($item['status']) {
            case 'offline':
                $data['active'] = 0;
                break;
            case 'online':
                $data['active'] = 1;
                break;
            case 'archive':
                $data['active'] = 2;
                break;
            case 'deleted':
                $data['active'] = 3;
                break;
            default:
                $data['active'] = 0;
        }
        $ctime_arr = explode('.', $item['created']);
        $data['ctime'] = $ctime_arr[0]; //
        $data['collection'] = $item['collection']; //
        $price_level = $item['calc_price_level'] - 1;
        if($price_level)
            $data['price_level'] = $price_level; //
        else
            $data['price_level'] = 1; //
        $data['pricing_category'] = $item['pricing_category']; //


        // SOURCE FORMAT
        $data['source_format'] = $item['camera_format']; //
        // On the Source Formats, let's get rid of the HD in the name
        $data['source_format'] = str_replace(array(' HD', 'HD ', ' HD '), '', $data['source_format']);
        switch (strtolower($data['source_format'])) {
            case 'XDCam 1/2"':
                $data['source_format'] = 'XDCam';
                $data['camera_chip_size'] = '1/2"';
                break;
            case 'XDCam 2/3"':
                $data['source_format'] = 'XDCam';
                $data['camera_chip_size'] = '2/3"';
                break;
            case 'XDCam 4:2:2 50Mbps':
                $data['source_format'] = 'XDCam';
                $data['color_space'] = '4:2:2';
                break;
            case 'XDCam 4:2:2 100Mbps':
                $data['source_format'] = 'XDCam';
                $data['color_space'] = '4:2:2';
                break;
        }
        // Get frame size and rate from camera_frame_rate
        preg_match('/^(\d+)(i|p)(\d+)/', $item['camera_frame_rate'], $matches);
        if($matches[1] && $matches[2] && $matches[3]){
            if($matches[2] == 'i'){
                $data['source_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                if($matches[3] == '60'){
                    $data['source_frame_rate'] = '29.97 FPS Interlaced';
                }
                elseif($matches[3] == '50'){
                    $data['source_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['source_frame_rate'] = $matches[3] . ' FPS Progressive';
                if($matches[1] == '1080' && $matches[3] == '24'){
                    $data['source_frame_rate'] = '23.98 FPS Progressive';
                }
            }
            if($matches[1] == '1080')
                $data['source_frame_size'] = 'HD (1920x1080)';
            elseif($matches[1] == '720')
                $data['source_frame_size'] = 'HD (1280x720)';
        }
        else{
            preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['camera_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[3] == 'i')
                    $data['source_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                else
                    $data['source_frame_rate'] = $matches[2] . ' FPS Progressive';
                $data['source_frame_size'] = $matches[1];
                $data['source_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['source_frame_size'])));
                if(stripos($data['source_frame_size'], '4K') !== false
                    || stripos($data['source_frame_size'], '4.5K') !== false
                    || stripos($data['source_frame_size'], '5K') !== false){
                    $data['source_frame_size'] = 'Ultra HD ' . $data['source_frame_size'];
                }
                else{
                    $data['source_frame_size'] = 'HD ' . $data['source_frame_size'];
                }
            }
        }

        // If blank
        if(!$data['source_frame_rate']){
            //if($item['camera_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $data['source_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    else{
                        $data['source_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['source_frame_rate'] = ''; //???
                }
            }
            elseif($item['camera_format'] && strpos($item['camera_format'], 'Film') !== false){
                $data['source_frame_rate'] = '24 FPS Progressive';
            }
            elseif($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                if($item['color_system'] == 'NTSC'){
                    $data['source_frame_rate'] = '29.97 FPS Interlaced';
                }
                else{
                    $data['source_frame_rate'] = '25 FPS Interlaced';
                }
            }
        }
        if(!$data['source_frame_size']){
            //if($item['camera_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    $parts = array('SD');
                    if ($item['aspect'])
                        $parts[] = $item['aspect'];
                    $parts[] = '(720x480)';
                    $data['source_frame_size'] = implode(' ', $parts);
                }
            }
            if($item['camera_format'] && $item['camera_format'] == 'HDV'){
                $data['source_frame_size'] = 'HD (1440x1080)';
            }
            if($item['camera_format'] && strpos($item['camera_format'], 'Film') !== false){
                $data['source_frame_size'] = '';
            }
            else{
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    //$data['source_frame_size'] = 'SD (720x480)';
                    $data['source_frame_size'] = ''; // FIX FSEARCH-594
                }
            }
        }


        // MASTER FORMAT
        $master_formats_for_import = array(
            'betacam sp',
            'digital betacam',
            'hdcam',
            'hdcam sr',
            'd1',
            'd5 hd',
            'redcode raw r3d'
        );
        if(in_array(strtolower($item['master_format']), $master_formats_for_import)){
            $data['master_format'] = $item['master_format']; //

            // Get frame size and rate from master_frame_rate
            preg_match('/^(\d+)(i|p)(\d+)/', $item['master_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[2] == 'i'){
                    $data['master_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                    if($matches[3] == '60'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    elseif($matches[3] == '50'){
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = $matches[3] . ' FPS Progressive';
                    if($matches[1] == '1080' && $matches[3] == '24'){
                        $data['master_frame_rate'] = '23.98 FPS Progressive';
                    }
                }
                if($matches[1] == '1080')
                    $data['master_frame_size'] = 'HD (1920x1080)';
                elseif($matches[1] == '720')
                    $data['master_frame_size'] = 'HD (1280x720)';
            }
            else{
                preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['master_frame_rate'], $matches);
                if($matches[1] && $matches[2] && $matches[3]){
                    if($matches[3] == 'i')
                        $data['master_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                    else
                        $data['master_frame_rate'] = $matches[2] . ' FPS Progressive';
                    $data['master_frame_size'] = $matches[1];
                    $data['master_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['master_frame_size'])));
                    if(stripos($data['master_frame_size'], '4K') !== false
                        || stripos($data['master_frame_size'], '4.5K') !== false
                        || stripos($data['master_frame_size'], '5K') !== false){
                        $data['master_frame_size'] = 'Ultra HD ' . $data['master_frame_size'];
                    }
                    else{
                        $data['master_frame_size'] = 'HD ' . $data['master_frame_size'];
                    }
                }
            }

            // If blank
            if(!$data['master_frame_rate']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    else{
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = ''; //???
                }
            }
            if(!$data['master_frame_size']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    //$data['master_frame_size'] = 'SD (720x480)';
                    $data['master_frame_size'] = ''; // FIX FSEARCH-594
                }
            }

        }

        // DIGITAL FORMAT
        $data['digital_file_format'] = $item['digital_file_format']; //
        // Get frame size and rate from digital_file_frame_rate
        preg_match('/^(\d+)(i|p)(\d+)/', $item['digital_file_frame_rate'], $matches);
        if($matches[1] && $matches[2] && $matches[3]){
            if($matches[2] == 'i'){
                $data['digital_file_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                if($matches[3] == '60'){
                    $data['digital_file_frame_rate'] = '29.97 FPS Interlaced';
                }
                elseif($matches[3] == '50'){
                    $data['digital_file_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['digital_file_frame_rate'] = $matches[3] . ' FPS Progressive';
                if($matches[1] == '1080' && $matches[3] == '24'){
                    $data['digital_file_frame_rate'] = '23.98 FPS Progressive';
                }
            }
            if($matches[1] == '1080')
                $data['digital_file_frame_size'] = 'HD (1920x1080)';
            elseif($matches[1] == '720')
                $data['digital_file_frame_size'] = 'HD (1280x720)';
        }
        else{
            preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['digital_file_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[3] == 'i')
                    $data['digital_file_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                else
                    $data['digital_file_frame_rate'] = $matches[2] . ' FPS Progressive';
                $data['digital_file_frame_size'] = $matches[1];
                $data['digital_file_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['digital_file_frame_size'])));
                if(stripos($data['digital_file_frame_size'], '4K') !== false
                    || stripos($data['digital_file_frame_size'], '4.5K') !== false
                    || stripos($data['digital_file_frame_size'], '5K') !== false){
                    $data['digital_file_frame_size'] = 'Ultra HD ' . $data['digital_file_frame_size'];
                }
                else{
                    $data['digital_file_frame_size'] = 'HD ' . $data['digital_file_frame_size'];
                }
            }
        }

        // If blank
        if(!$data['digital_file_frame_rate']){
            if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                if($item['color_system'] == 'NTSC'){
                    $data['digital_file_frame_rate'] = '29.97 FPS Interlaced';
                }
                else{
                    $data['digital_file_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['digital_file_frame_rate'] = ''; //???
            }
        }
        if(!$data['digital_file_frame_size']){
            //if($item['digital_file_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['digital_file_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['digital_file_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    $parts = array('SD');
                    if ($item['aspect'])
                        $parts[] = $item['aspect'];
                    $parts[] = '(720x480)';
                    $data['digital_file_frame_size'] = implode(' ', $parts);
                }
            }
            elseif($item['digital_file_format'] && $item['digital_file_format'] == 'HDV'){
                $data['digital_file_frame_size'] = 'HD (1440x1080)';
            }
            elseif(!$item['digital_file_frame_rate'] || $item['digital_file_frame_rate'] == 'balnk'){
                //$data['digital_file_frame_size'] = 'SD (720x480)';
                $data['digital_file_frame_size'] = ''; // FIX FSEARCH-594
            }

        }

        $data['master_lab'] = $item['master_lab'];
        $data['color_system'] = $item['color_system'];

        foreach($data as $key => $value){
            if($value == 'NULL' || empty($value))
                unset($data[$key]);
        }


        // KEYWORDS
        $this->db_fs->select('key, value');
        $query = $this->db_fs->get_where('product_opt', array('product_id' => $item['id']));
        $res2 = $query->result_array();
        $clip_options = array();
        foreach($res2 as $option){
            if($option['value'])
                $clip_options[$option['key']][] = trim($option['value']);
        }
        $clip_keywords = array();
        foreach($clip_options as $key => $values){
            if(array_key_exists($key, $this->imported_options)) {
                foreach($values as $option_value){
                    if($key == 'Country'){
                        $data['country'] = $option_value;
                    }
                    else{
                        $clip_keywords[$this->imported_options[$key]][] = $option_value;
                    }
                }
            }
        }


        $this->db_master->insert('lib_clips', $data);
        $clip_id = $this->db_master->insert_id();

        if($clip_id){

            // Save keywords
            $this->db_master->delete('lib_clip_keywords', array('clip_id' => $clip_id));
            foreach($clip_keywords as $section => $section_keywords){
                $unique_section_keywords = array_unique($section_keywords);
                $data_content[$section] = implode(', ', $unique_section_keywords);
                foreach($unique_section_keywords as $keyword){
                    /*$this->db->select('id');
                    $query = $this->db->get_where('lib_keywords', array('section' => $section, 'keyword' => $keyword));
                    $res4 = $query->result_array();*/
                    $keyword=addslashes($keyword);
                    $res4=$this->db->query('SELECT id FROM lib_keywords WHERE section = "'.$section.'" AND keyword LIKE "'.$keyword.'"
                    AND (provider_id=0 OR provider_id='.$user_id.') ORDER BY provider_id LIMIT 1')->result_array();
                    $res4=$res4[0]['id'];
                    if(empty($res4)){
                        $this->db_master->insert('lib_keywords', array(
                            'keyword'=>$keyword,
                            'section'=>$section,
                            'provider_id'=>$user_id,
                            'hidden'=>1
                        ));
                        $res4 = $this->db_master->insert_id();
                    }
                    if($res4){
                        $this->db_master->insert('lib_clip_keywords', array('clip_id' => $clip_id, 'keyword_id' => $res4));
                    }
                }
            }

            $data_content['clip_id'] = $clip_id;
            $data_content['title'] = $data['code'];
            $data_content['description'] = $item['description']; //
            $data_content['notes'] = isset($item['notes']) ? $item['notes'] : ''; //
            $keywords_arr  = array_map('trim', explode('|', $item['lcdescription']));
            $data_content['keywords'] = trim(implode(', ', $keywords_arr), ', ');
            //$data_content['location'] = $item['display_location'];

            foreach($data_content as $key => $value){
                if($value == 'NULL' || empty($value))
                    unset($data_content[$key]);
            }

            $this->db_master->insert('lib_clips_content', $data_content);

            if($data['license'] && $data['pricing_category'])
                $this->save_delivery_formats($clip_id, $data['pricing_category'], $data['license']);


            $this->save_resource($data['code'], $clip_id, 'thumb');
            $this->save_resource($data['code'], $clip_id, 'motion_thumb');
            $this->save_resource($data['code'], $clip_id, 'preview');
            $this->save_resource($data['code'], $clip_id, 'stills');
            //$this->save_resource($data['code'], $clip_id, 'res');

//            // Get clip tape volume
//            $query = $this->db->get_where('lib_clips_res', array('clip_id' => $item['id'], 'type' => 2));
//            $res = $query->result_array();
//            if($res[0]['id'])
//                return;
//            $query = $this->db_fs->get_where('product_media', array('tape' => $item['tape']));
//            $volumes = $query->result_array();
//            if(!$this->save_master_resource($item['product'], $item['id'], $volumes)) {
//                echo $item['id'], PHP_EOL;
//            }

            // Save additional collections
            $this->db_fs->select('c.collection, c.collection_id, pc.product_id');
            $this->db_fs->from('collections c');
            $this->db_fs->join('product_collections pc', 'c.collection_id = pc.collection_id AND pc.product_id = ' . $item['id'], 'left');
            $query = $this->db_fs->get();
            //$query = $this->db_fs->get_where('product_collections', array('product_id' => $item['id']));
            $res3 = $query->result_array();

            $is_3d_collection = (stripos($item['camera_format'], '3D') === false) ? false : true;
            $is_ocean_collection = $item['collection'] == 'Ocean Footage' ? true : false;
            $is_nature_collection = $item['collection'] == 'Nature Footage' ? true : false;
            $is_adventure_collection = $item['collection'] == 'Adventure Footage' ? true : false;

            foreach($res3 as $collection){
                if($collection['collection'] == 'Nature Footage' && $collection['product_id']){
                    $is_nature_collection = true;
                }
                elseif($collection['collection'] == 'Ocean Footage' && $collection['product_id']){
                    $is_ocean_collection = true;
                }
                elseif($collection['collection'] == 'Adventure Footage' && $collection['product_id']){
                    $is_adventure_collection = true;
                }
                elseif($collection['collection'] == '3D Footage' && $collection['product_id']){
                    $is_3d_collection = true;
                }
            }
            if($is_ocean_collection){
                foreach($res3 as $key => $collection){
                    if($collection['collection'] == 'Nature Footage'){
                        $res3[$key]['product_id'] = $item['id'];
                        $is_nature_collection = true;
                        break;
                    }
                }
            }

            foreach($res3 as $collection){
                if($collection['collection'] == 'Footage Search' && $item['collection'] != 'Footage Search'
                    && !$is_ocean_collection && !$is_nature_collection && !$is_adventure_collection){
                    $collection['product_id'] = $item['id'];
                }
                if($collection['collection'] == '3D Footage' && $is_3d_collection){
                    $collection['product_id'] = $item['id'];
                }
                if($collection['product_id']){
                    $collection_data = array();
                    $collection_data['clip_id'] = $clip_id;
                    $collection_data['collection_id'] = $collection['collection_id'];
                    $this->db_master->insert('lib_clip_add_collections', $collection_data);
                }
            }
//            Mixed
//Nature Footage
//Footage Search
//Nature Footage 3D
//jhfestival
//Ocean Footage
//Adventure Footage
//00:00:26;18
//00:00:10:01
//3D Footage
//Natuer Footage
//
//
//            Test Collection	TC	NULL	f
//5
//	jhfestival	jhf	NULL	f
//1
//	Nature Footage	NF	naturefootage	t
//2
//	Ocean Footage	OF	oceanfootage	t
//3
//	Adventure Footage	AF	adventurefootage	t
//7
//	3D Footage	3D	3Dfootage	t
//6
//	Nature Footage 3D	NF3D	naturefootage3D	t
//8
//	RED Footage	RED	redfootage	t
//9
//	Footage Search	FS	footagesearch	t
//10
//	Ultra HD Footage	UHD	ultrahdfootage	t


        }
        $this->clips_model->add_to_index($clip_id,false);
        return $clip_id;
    }

    private function update_clip($item){
        $clip_id = $item['id'];
        $data['license'] = $item['royalty_free'] == 'f' ? 2 : 1;
        $data['duration'] = $item['duration'];
        $data['creation_date'] = date('Y-m-d H:i:s', $item['add_date']);
        $film_date_parts = explode('/', $item['film_date']);
        if(count($film_date_parts) == 2 && is_numeric($film_date_parts[0]) && is_numeric($film_date_parts[1]))
            $data['film_date'] = date('Y-m-d', mktime(0, 0, 0, $film_date_parts[0], 1, $film_date_parts[1])); //
        $data['aspect'] = $item['aspect'];
        $data['viewed'] = $item['views'];
        switch ($item['status']) {
            case 'offline':
                $data['active'] = 0;
                break;
            case 'online':
                $data['active'] = 1;
                break;
            case 'archive':
                $data['active'] = 2;
                break;
            case 'deleted':
                $data['active'] = 3;
                break;
            default:
                $data['active'] = 0;
        }
        $ctime_arr = explode('.', $item['created']);
        $data['ctime'] = $ctime_arr[0]; //
        $data['collection'] = $item['collection'];
        $price_level = $item['calc_price_level'] - 1;
        if($price_level)
            $data['price_level'] = $price_level;
        else
            $data['price_level'] = 1;
        $data['pricing_category'] = $item['pricing_category'];

        // SOURCE FORMAT
        $data['source_format'] = $item['camera_format']; //
        // On the Source Formats, let's get rid of the HD in the name
        $data['source_format'] = str_replace(array(' HD', 'HD ', ' HD '), '', $data['source_format']);
        switch (strtolower($data['source_format'])) {
            case 'XDCam 1/2"':
                $data['source_format'] = 'XDCam';
                $data['camera_chip_size'] = '1/2"';
                break;
            case 'XDCam 2/3"':
                $data['source_format'] = 'XDCam';
                $data['camera_chip_size'] = '2/3"';
                break;
            case 'XDCam 4:2:2 50Mbps':
                $data['source_format'] = 'XDCam';
                $data['color_space'] = '4:2:2';
                break;
            case 'XDCam 4:2:2 100Mbps':
                $data['source_format'] = 'XDCam';
                $data['color_space'] = '4:2:2';
                break;
        }
        // Get frame size and rate from camera_frame_rate
        preg_match('/^(\d+)(i|p)(\d+)/', $item['camera_frame_rate'], $matches);
        if($matches[1] && $matches[2] && $matches[3]){
            if($matches[2] == 'i'){
                $data['source_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                if($matches[3] == '60'){
                    $data['source_frame_rate'] = '29.97 FPS Interlaced';
                }
                elseif($matches[3] == '50'){
                    $data['source_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['source_frame_rate'] = $matches[3] . ' FPS Progressive';
                if($matches[1] == '1080' && $matches[3] == '24'){
                    $data['source_frame_rate'] = '23.98 FPS Progressive';
                }
            }
            if($matches[1] == '1080')
                $data['source_frame_size'] = 'HD (1920x1080)';
            elseif($matches[1] == '720')
                $data['source_frame_size'] = 'HD (1280x720)';
        }
        else{
            preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['camera_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[3] == 'i')
                    $data['source_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                else
                    $data['source_frame_rate'] = $matches[2] . ' FPS Progressive';
                $data['source_frame_size'] = $matches[1];
                $data['source_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['source_frame_size'])));
                if(stripos($data['source_frame_size'], '4K') !== false
                    || stripos($data['source_frame_size'], '4.5K') !== false
                    || stripos($data['source_frame_size'], '5K') !== false){
                    $data['source_frame_size'] = 'Ultra HD ' . $data['source_frame_size'];
                }
                else{
                    $data['source_frame_size'] = 'HD ' . $data['source_frame_size'];
                }
            }
        }

        // If blank
        if(!$data['source_frame_rate']){
            //if($item['camera_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $data['source_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    else{
                        $data['source_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['source_frame_rate'] = ''; //???
                }
            }
            elseif($item['camera_format'] && strpos($item['camera_format'], 'Film') !== false){
                $data['source_frame_rate'] = '24 FPS Progressive';
            }
            elseif($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                if($item['color_system'] == 'NTSC'){
                    $data['source_frame_rate'] = '29.97 FPS Interlaced';
                }
                else{
                    $data['source_frame_rate'] = '25 FPS Interlaced';
                }
            }
        }
        if(!$data['source_frame_size']){
            //if($item['camera_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    $parts = array('SD');
                    if ($item['aspect'])
                        $parts[] = $item['aspect'];
                    $parts[] = '(720x480)';
                    $data['source_frame_size'] = implode(' ', $parts);
                }
            }
            if($item['camera_format'] && $item['camera_format'] == 'HDV'){
                $data['source_frame_size'] = 'HD (1440x1080)';
            }
            if($item['camera_format'] && strpos($item['camera_format'], 'Film') !== false){
                $data['source_frame_size'] = '';
            }
            else{
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['source_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    //$data['source_frame_size'] = 'SD (720x480)';
                    $data['source_frame_size'] = ''; // FIX FSEARCH-594
                }
            }
        }

        // MASTER FORMAT
        $master_formats_for_import = array(
            'betacam sp',
            'digital betacam',
            'hdcam',
            'hdcam sr',
            'd1',
            'd5 hd',
            'redcode raw r3d'
        );
        if(in_array(strtolower($item['master_format']), $master_formats_for_import)){
            $data['master_format'] = $item['master_format']; //

            // Get frame size and rate from master_frame_rate
            preg_match('/^(\d+)(i|p)(\d+)/', $item['master_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[2] == 'i'){
                    $data['master_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                    if($matches[3] == '60'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    elseif($matches[3] == '50'){
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = $matches[3] . ' FPS Progressive';
                    if($matches[1] == '1080' && $matches[3] == '24'){
                        $data['master_frame_rate'] = '23.98 FPS Progressive';
                    }
                }
                if($matches[1] == '1080')
                    $data['master_frame_size'] = 'HD (1920x1080)';
                elseif($matches[1] == '720')
                    $data['master_frame_size'] = 'HD (1280x720)';
            }
            else{
                preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['master_frame_rate'], $matches);
                if($matches[1] && $matches[2] && $matches[3]){
                    if($matches[3] == 'i')
                        $data['master_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                    else
                        $data['master_frame_rate'] = $matches[2] . ' FPS Progressive';
                    $data['master_frame_size'] = $matches[1];
                    $data['master_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['master_frame_size'])));
                    if(stripos($data['master_frame_size'], '4K') !== false
                        || stripos($data['master_frame_size'], '4.5K') !== false
                        || stripos($data['master_frame_size'], '5K') !== false){
                        $data['master_frame_size'] = 'Ultra HD ' . $data['master_frame_size'];
                    }
                    else{
                        $data['master_frame_size'] = 'HD ' . $data['master_frame_size'];
                    }
                }
            }

            // If blank
            if(!$data['master_frame_rate']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    else{
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = ''; //???
                }
            }
            if(!$data['master_frame_size']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    //$data['master_frame_size'] = 'SD (720x480)';
                    $data['master_frame_size'] = ''; // FIX FSEARCH-594
                }
            }

        }

        // DIGITAL FORMAT
        $data['digital_file_format'] = $item['digital_file_format']; //
        // Get frame size and rate from digital_file_frame_rate
        preg_match('/^(\d+)(i|p)(\d+)/', $item['digital_file_frame_rate'], $matches);
        if($matches[1] && $matches[2] && $matches[3]){
            if($matches[2] == 'i'){
                $data['digital_file_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                if($matches[3] == '60'){
                    $data['digital_file_frame_rate'] = '29.97 FPS Interlaced';
                }
                elseif($matches[3] == '50'){
                    $data['digital_file_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['digital_file_frame_rate'] = $matches[3] . ' FPS Progressive';
                if($matches[1] == '1080' && $matches[3] == '24'){
                    $data['digital_file_frame_rate'] = '23.98 FPS Progressive';
                }
            }
            if($matches[1] == '1080')
                $data['digital_file_frame_size'] = 'HD (1920x1080)';
            elseif($matches[1] == '720')
                $data['digital_file_frame_size'] = 'HD (1280x720)';
        }
        else{
            preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['digital_file_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[3] == 'i')
                    $data['digital_file_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                else
                    $data['digital_file_frame_rate'] = $matches[2] . ' FPS Progressive';
                $data['digital_file_frame_size'] = $matches[1];
                $data['digital_file_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['digital_file_frame_size'])));
                if(stripos($data['digital_file_frame_size'], '4K') !== false
                    || stripos($data['digital_file_frame_size'], '4.5K') !== false
                    || stripos($data['digital_file_frame_size'], '5K') !== false){
                    $data['digital_file_frame_size'] = 'Ultra HD ' . $data['digital_file_frame_size'];
                }
                else{
                    $data['digital_file_frame_size'] = 'HD ' . $data['digital_file_frame_size'];
                }
            }
        }

        // If blank
        if(!$data['digital_file_frame_rate']){
            if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                if($item['color_system'] == 'NTSC'){
                    $data['digital_file_frame_rate'] = '29.97 FPS Interlaced';
                }
                else{
                    $data['digital_file_frame_rate'] = '25 FPS Interlaced';
                }
            }
            else{
                $data['digital_file_frame_rate'] = ''; //???
            }
        }
        if(!$data['digital_file_frame_size']){
            //if($item['digital_file_frame_rate'] == 'SD'){
            if($item['pricing_category'][0] == 'S'){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['digital_file_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['digital_file_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    $parts = array('SD');
                    if ($item['aspect'])
                        $parts[] = $item['aspect'];
                    $parts[] = '(720x480)';
                    $data['digital_file_frame_size'] = implode(' ', $parts);
                }
            }
            elseif($item['digital_file_format'] && $item['digital_file_format'] == 'HDV'){
                $data['digital_file_frame_size'] = 'HD (1440x1080)';
            }
            elseif(!$item['digital_file_frame_rate'] || $item['digital_file_frame_rate'] == 'balnk'){
                //$data['digital_file_frame_size'] = 'SD (720x480)';
                $data['digital_file_frame_size'] = ''; // FIX FSEARCH-594
            }

        }

        $data['master_lab'] = $item['master_lab'];
        $data['color_system'] = $item['color_system'];

        foreach($data as $key => $value){
            if($value == 'NULL' || empty($value))
                unset($data[$key]);
        }

        // KEYWORDS
        $this->db_fs->select('key, value');
        $query = $this->db_fs->get_where('product_opt', array('product_id' => $item['id']));
        $res2 = $query->result_array();
        $clip_options = array();
        foreach($res2 as $option){
            if($option['value'])
                $clip_options[$option['key']][] = trim($option['value']);
        }
        $clip_keywords = array();
        foreach($clip_options as $key => $values){
            if(array_key_exists($key, $this->imported_options)) {
                foreach($values as $option_value){
                    if($key == 'Country'){
                        $data['country'] = $option_value;
                    }
                    else{
                        $clip_keywords[$this->imported_options[$key]][] = $option_value;
                    }
                }
            }
        }

        $this->db_master->update('lib_clips', $data, array('id' => $clip_id));

        if($clip_id){

            // Save keywords
            $this->db_master->delete('lib_clip_keywords', array('clip_id' => $clip_id));
            foreach($clip_keywords as $section => $section_keywords){
                $unique_section_keywords = array_unique($section_keywords);
                $data_content[$section] = implode(', ', $unique_section_keywords);
                foreach($unique_section_keywords as $keyword){
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_keywords', array('section' => $section, 'keyword' => $keyword));
                    $res4 = $query->result_array();
                    if(!$res4){
                        $this->db_master->insert('lib_keywords', array('section' => $section, 'keyword' => $keyword, 'old' => 1));
                        $keyword_id = $this->db_master->insert_id();
                    }
                    else
                        $keyword_id = $res4[0]['id'];

                    $this->db_master->insert('lib_clip_keywords', array('clip_id' => $clip_id, 'keyword_id' => $keyword_id));
                }
            }

            $data_content['title'] = $data['code'];
            $data_content['description'] = $item['description']; //
            $data_content['notes'] = isset($item['notes']) ? $item['notes'] : ''; //
            $keywords_arr  = array_map('trim', explode('|', $item['lcdescription']));
            $data_content['keywords'] = trim(implode(', ', $keywords_arr), ', ');
            //$data_content['location'] = $item['display_location'];

            foreach($data_content as $key => $value){
                if($value == 'NULL' || empty($value))
                    unset($data_content[$key]);
            }

            $this->db_master->update('lib_clips_content', $data_content, array('clip_id' => $clip_id));

            if($data['license'] && $data['pricing_category'])
                $this->save_delivery_formats($clip_id, $data['pricing_category'], $data['license']);
        }
        return $clip_id;
    }

    private function update_clip_old($item){

        $data = array();

        // Update MASTER FORMAT
        $master_formats_for_import = array(
            'betacam sp',
            'digital betacam',
            'hdcam',
            'hdcam sr',
            'd1',
            'd5 hd',
            'redcode raw r3d'
        );
        if(in_array(strtolower($item['master_format']), $master_formats_for_import)){
            $data['master_format'] = $item['master_format']; //

            // Get frame size and rate from master_frame_rate
            preg_match('/^(\d+)(i|p)(\d+)/', $item['master_frame_rate'], $matches);
            if($matches[1] && $matches[2] && $matches[3]){
                if($matches[2] == 'i'){
                    $data['master_frame_rate'] = ($matches[3] / 2) . ' FPS Interlaced';
                    if($matches[3] == '60'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    elseif($matches[3] == '50'){
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = $matches[3] . ' FPS Progressive';
                    if($matches[1] == '1080' && $matches[3] == '24'){
                        $data['master_frame_rate'] = '23.98 FPS Progressive';
                    }
                }
                if($matches[1] == '1080')
                    $data['master_frame_size'] = 'HD (1920x1080)';
                elseif($matches[1] == '720')
                    $data['master_frame_size'] = 'HD (1280x720)';
            }
            else{
                preg_match('/^(.*) (\d+)(i|p|fps)$/', $item['master_frame_rate'], $matches);
                if($matches[1] && $matches[2] && $matches[3]){
                    if($matches[3] == 'i')
                        $data['master_frame_rate'] = ($matches[2] / 2) . ' FPS Interlaced';
                    else
                        $data['master_frame_rate'] = $matches[2] . ' FPS Progressive';
                    $data['master_frame_size'] = $matches[1];
                    $data['master_frame_size'] = preg_replace('/\s{2,}/', ' ', str_ireplace('WS', '', str_ireplace('HD', '', $data['master_frame_size'])));
                    if(stripos($data['master_frame_size'], '4K') !== false
                        || stripos($data['master_frame_size'], '4.5K') !== false
                        || stripos($data['master_frame_size'], '5K') !== false){
                        $data['master_frame_size'] = 'Ultra HD ' . $data['master_frame_size'];
                    }
                    else{
                        $data['master_frame_size'] = 'HD ' . $data['master_frame_size'];
                    }
                }
            }

            // If blank
            if(!$data['master_frame_rate']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $data['master_frame_rate'] = '29.97 FPS Interlaced';
                    }
                    else{
                        $data['master_frame_rate'] = '25 FPS Interlaced';
                    }
                }
                else{
                    $data['master_frame_rate'] = ''; //???
                }
            }
            if(!$data['master_frame_size']){
                if($item['color_system'] && ($item['color_system'] == 'NTSC' || $item['color_system'] == 'PAL')){
                    if($item['color_system'] == 'NTSC'){
                        $parts = array('SD', 'NTSC');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x486)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                    else{
                        $parts = array('SD', 'PAL');
                        if ($item['aspect'])
                            $parts[] = $item['aspect'];
                        $parts[] = '(720x576)';
                        $data['master_frame_size'] = implode(' ', $parts);
                    }
                }
                else{
                    $parts = array('SD');
                    if ($item['aspect'])
                        $parts[] = $item['aspect'];
                    $parts[] = '(720x480)';
                    $data['master_frame_size'] = implode(' ', $parts);
                }
            }
        }

        if ($data) {
            $this->db_master->update('lib_clips', $data, array('id' => $item['id']));
            return true;
        }
        else {
            return false;
        }
    }

    public function map_collections() {
        $time_start = microtime(1);

        //$this->db_master->truncate('lib_clips_collections');
        $last_imported_clip = 433799;
        $this->db_master->where('clip_id >', $last_imported_clip);
        $this->db_master->delete('lib_clips_collections');

        $portion = 1000;
        $stop = false;
        $i = 0;
        $imported = 0;
        while(!$stop){
            $from = $portion * $i;
            $this->db->limit($portion, $from);
            $this->db->where('id >', $last_imported_clip);
            $query = $this->db->get('lib_clips');
            $res = $query->result_array();
            if($res){
                foreach($res as $clip){
                    $this->db->get_where('lib_clip_add_collections', array('clip_id' => $clip['id']));
                    $collections = $query->result_array();
                    $new_collections = array();
                    switch ($clip['collection']) {
//                        case '3D Footage':
//                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 7);
//                            break;
                        case 'Adventure Footage':
                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 3);
                            $is_adventure = true;
                            break;
//                        case 'Footage Search':
//                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 9);
//                            break;
                        case 'Nature Footage':
                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 1);
                            break;
//                        case 'Nature Footage 3D':
//                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => '');
//                            break;
                        case 'Ocean Footage':
                            $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 2);
                            break;
                    }
                    if (!isset($is_adventure)) {
                        foreach($collections as $collection) {
                            if ($collection['collection_id'] == 3) { // Adventure
                                $new_collections[] = array('clip_id' => $clip['id'], 'collection_id' => 3);
                            }
                        }
                    }
                    if ($new_collections) {
                        foreach($new_collections as $collection) {
                            $this->db_master->insert('lib_clips_collections', $collection);
                        }
                    }
                    $imported++;
                }
            }
            else{
                $stop = true;
            }
            $i++;
            echo $imported, PHP_EOL;
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;

        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    private function save_submission($code, $provider_id, $date){
        $this->db->where('code', $code);
        $this->db->limit(1);
        $query = $this->db->get('lib_submissions');
        $res = $query->result_array();
        if($res)
            return $res[0]['id'];
        else{
            $data = array(
                'code' => $code,
                'provider_id' => $provider_id,
                'date' => $date
            );
            $this->db_master->insert('lib_submissions', $data);
            return $this->db_master->insert_id();
        }
    }

    private function save_delivery_formats($clip_id, $pricing_category, $license){
        if($clip_id && $pricing_category && $license){
            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $clip_id));
            if($license){
                if($license == 1){
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_rf_delivery_options')->result_array();
                }
                else{
                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_delivery_options')->result_array();
                }
                $this->load->model('clips_model');
                foreach($delivery_formats as $format){
                    if($format['categories']){
                        $categories = explode(' ', $format['categories']);
                        if(in_array($pricing_category, $categories)){
                            $this->clips_model->insert_with_validation_to_lib_clips_delivery_formats($clip_id, $format['id']);
                            //$this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $clip_id, 'format_id' => $format['id']));
                        }
                    }
                }
            }
        }
    }

    private function save_resource($code, $id, $res_type){
        $resources_url = '//video.naturefootage.com/';
        //$resources_url = 's3://s3.footagesearch.com/';
        $dest = $this->config->item('clip_dir');
        $code_arr = explode('_', $code);
        preg_match('/^[A-Za-z]+/', $code, $matches);
        switch ($res_type) {
            case 'res':
                $type = 2;
                $ext = 'mov';
                $dir = 'preview/' . $matches[0] . '/' . $code_arr[0] . '/2/';
                $dest .= 'res/';
                break;
            case 'motion_thumb':
                $type = 0;
                $ext = 'mov';
                $dir = 'preview/' . $matches[0] . '/' . $code_arr[0] . '/1/';
                $dest .= 'thumb/';
                break;
            case 'preview':
                $type = 1;
                $ext = 'mov';
                $dir = 'preview/' . $matches[0] . '/' . $code_arr[0] . '/2/';
                $dest .= 'preview/';
                break;
            case 'stills':
                $type = 1;
                $ext = 'mov';
                $dir = 'preview/' . $matches[0] . '/' . $code_arr[0] . '/Images/';
                $dest .= 'preview/';
                break;
            case 'thumb':
                $type = 0;
                $ext = 'jpg';
                $dir = 'stills/';
                $dest .= 'thumb/';
                break;
        }

        $dir .= $code . '.' . $ext;
//        $dest .= $id . '.' . $ext;
//        $this->upload_resource($resources_url . $dir, $dest);
//        if(file_exists($dest)){
//            $res_data = array(
//                'clip_id' => $id,
//                'resource' => $ext,
//                'type' => $type
//            );
//            $this->db_master->insert('lib_clips_res', $res_data);
//        }

        if($res_type == 'res'){
//            $res_data = array(
//                'clip_id' => $id,
//                'resource' => $ext,
//                'type' => $type
//            );
//            $this->db_master->insert('lib_clips_res', $res_data);
        }
        elseif($res_type == 'stills'){
            $dir = 'preview/' . $matches[0] . '/' . $code_arr[0] . '/Images/' . $code;
            $res_data = array(
                'clip_id' => $id,
                'path' => $resources_url . $dir
            );
            $this->db_master->insert('lib_thumbnails', $res_data);
        }
        else{
            $res_data = array(
                'clip_id' => $id,
                'resource' => $ext,
                'type' => $type,
                'location' => $resources_url . $dir
            );
            $this->db_master->insert('lib_clips_res', $res_data);
        }
    }
    private function save_master_resource($code, $id, $volumes){
        foreach($volumes as $volume){
            $file_path = '/storage/' . trim($volume['media_dv'], '/') . '/' . $volume['tape'] . '/' . $code . '.' . trim($volume['file_extension'], '.');
            if(is_file($file_path)){
                $res_data = array(
                    'clip_id' => $id,
                    'resource' => trim($volume['file_extension'], '.'),
                    'type' => 2,
                    'location' => $file_path
                );
                $this->db_master->insert('lib_clips_res', $res_data);
                return true;
            }
            elseif (is_file('/storage/' . trim($volume['media_dv'], '/') . '/' . $volume['tape'] . '/' . $code . 'a.' . trim($volume['file_extension'], '.'))) {
                //echo $file_path, PHP_EOL;
            }
            elseif (is_file('/storage/' . trim($volume['media_dv'], '/') . '/' . $volume['tape'] . '/' . $code . 'b.' . trim($volume['file_extension'], '.'))) {
                //echo $file_path, PHP_EOL;
            }
            else{
                echo $file_path, PHP_EOL;
            }
        }
        return false;
    }

    private function upload_resource($source, $dest){
        //$this->db_master->insert('lib_clips', $data);
        //http://s3.footagesearch.com/preview/DZ/DZ18/1/DZ18_048.mov
        //http://s3.footagesearch.com/stills/DZ18_048.jpg

        //http://s3.footagesearch.com/preview/PC/PC07/1/PC07_014.mov
        //http://s3.footagesearch.com/stills/PC07_014.jpg

        //http://s3.footagesearch.com/preview/CF/CF11/1/CF11_028.mov
        //http://s3.footagesearch.com/stills/CF11_028.jpg

        //http://s3.footagesearch.com/stills/AF003_0325.jpg
        //http://s3.footagesearch.com/preview/AF/AF003/1/AF003_0325.mov

        //http://s3.footagesearch.com/stills/CF015_0123.jpg
        //http://s3.footagesearch.com/preview/CF/CF015/1/CF015_0123.mov

        //http://s3.footagesearch.com/preview/DDEL/DDEL003/1/DDEL003_0308.mov
        //http://s3.footagesearch.com/preview/JWI/JWI048/1/JWI048_0220.mov

        if(!file_exists($dest)){
            $source_data = @file_get_contents($source);
            if($source_data)
                file_put_contents($dest, $source_data);
        }
    }

    private function upload_resource_by_ftp($source, $dest){

    }

    public function replace_thumbs() {
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $new_thumbs = '/home/ivan/Загрузки/stills/400';
        $files = scandir($new_thumbs);
        $resourceType = 'thumb';
        foreach ($files as $file) {
            if ($file == '.' || $file == '..'){
                continue;
            }
            $code = pathinfo($file, PATHINFO_FILENAME);
            $this->db->select('id');
            $query = $this->db->get_where('lib_clips', array('code' => $code));
            $res = $query->result_array();
            if ($res) {
                $id = $res['0']['id'];
                $src = $new_thumbs . '/' . $file;
                /*$dest = 's3://' . $store[$resourceType]['bucket'] . rtrim($store[$resourceType]['path'], '/')
                    . '/' . $id . '.jpg';*/
                $dest = 'https://video.naturefootage.com'.rtrim($store[$resourceType]['path'],'/').'/'.$id.'.jpg';
                $res = $this->upload_to_s3($src, $dest);
                if ($res) {
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_clips_res', array('clip_id' => $id, 'type' => 0, 'resource' => 'jpg'));
                    $res = $query->result_array();
                    if ($res) {
                        $thumb_id = $res[0]['id'];
                        $this->db_master->update('lib_clips_res', array('location' => $dest), array('id' => $thumb_id));
                    }
                    else {
                        $res_data = array(
                            'clip_id' => $id,
                            'resource' => 'jpg',
                            'type' => 0,
                            'location' => $dest
                        );
                        $this->db_master->insert('lib_clips_res', $res_data);
                    }
                }
                else {
                    echo 'Error:' . $dest, PHP_EOL;
                }
            }
            else {
                echo $code, PHP_EOL;
            }
        }
    }

    public function providers_prefixes() {
        echo 'Start', PHP_EOL;
        $time_start = microtime(1);
        $query = $this->db_fs->get('log_tapepre');
        $res = $query->result_array();
        $imported = 0;
        if($res){
            foreach($res as $item){
                $this->db->select('id');
                $query = $this->db->get_where('lib_users', array('login' => $item['owner']));
                $res = $query->result_array();
                if ($res[0]['id']) {
                    $this->db_master->where('id', $res[0]['id']);
                    $this->db_master->update('lib_users', array('prefix' => $item['pre']));
                    $imported++;
                }
            }
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;

        echo 'Finish: imported ' . $imported . 'items, duration ' . $time, PHP_EOL;
    }

    public function master_file() {
        $i = 0;
        $stop = false;
        $portion = 5000;
        while(!$stop){
            $from = $portion * $i;
            $query = $this->db->query("SELECT c.id, c.code FROM lib_clips c LEFT JOIN lib_clips_res cr ON c.id = cr.clip_id AND cr.type = 2 WHERE cr.location IS NULL LIMIT $from, $portion");
            $rows = $query->result_array();
            if ($rows) {
                foreach ($rows as $clip) {
                    $this->db_fs->select('tape');
                    $query = $this->db_fs->get_where('products', array('id' => $clip['id']));
                    $res = $query->result_array();
                    if(!$res[0]['tape']) {
                        //echo $clip['id'], PHP_EOL;
                        continue;
                    }
                    $query = $this->db_fs->get_where('product_media', array('tape' => $res[0]['tape']));
                    $volumes = $query->result_array();
                    if(!$this->save_master_resource($clip['code'], $clip['id'], $volumes)) {
                        //echo $clip['id'], PHP_EOL;
                    }
                }
            }
            else {
                $stop = true;
            }
            $i++;
        }
    }

    function stills() {
        $this->db_master->truncate('lib_thumbnails');
        $i = 0;
        $stop = false;
        $portion = 10000;
        while(!$stop){
            $from = $portion * $i;
            $query = $this->db->query("SELECT id, code FROM lib_clips LIMIT $from, $portion");
            $rows = $query->result_array();
            if ($rows) {
                foreach ($rows as $clip) {
                    $this->save_resource($clip['code'], $clip['id'], 'stills');
                }
            }
            else {
                $stop = true;
            }
            $i++;
        }
    }

    public function owners() {
        $query = $this->db->query("SELECT id, code FROM lib_clips WHERE client_id = 0");
        $rows = $query->result_array();
        $i = 0;
        foreach ($rows as $clip) {
            $this->db_fs->select('owner');
            $query = $this->db_fs->get_where('products', array('product' => $clip['code']));
            $res = $query->result_array();
            if ($res[0]['owner']) {
                $this->db_fs->select('username, realemail');
                $query = $this->db_fs->get_where('users', array('username' => $res[0]['owner']));
                $res2 = $query->result_array();
                $this->db->select('id');
                if ($res2[0]['username'])
                    $query = $this->db->get_where('lib_users', array('login' => $res2[0]['username']));
                else
                    $query = $this->db->get_where('lib_users', array('email' => $res2[0]['realemail']));
                $res3 = $query->result_array();
                if ($res3[0]['id']) {
                    $this->db_master->where('id', $clip['id']);
                    $this->db_master->update('lib_clips', array('client_id' => $res3[0]['id']));
                }
            }
            $i++;
            echo $i, PHP_EOL;
        }
    }

    private function upload_to_s3($source, $destination) {

        if(!$this->s3Client){
            $store = array();
            require(__DIR__ . '/../config/store.php');
            $this->s3Client = S3Client::factory(array(
                'key'    => $store['s3']['key'],
                'secret' => $store['s3']['secret']
            ));
        }
        $destination = parse_url($destination);
        $params = array(
            'Bucket'     => $destination['host'],
            'Key'        => trim($destination['path'], '/'),
            'SourceFile' => $source
        );
        $params['ACL'] = 'public-read';
        return $this->s3Client->putObject($params);
    }

    public function clipbins() {
        @set_time_limit(345600);
        echo 'Start', PHP_EOL;
//        $this->db_master->truncate('lib_lb');
//        $this->db_master->truncate('lib_lb_folders');
//        $this->db_master->truncate('lib_lb_items');

        $time_start = microtime(1);
        $portion = 50;
        $stop = false;
        $i = 0;
        $imported = 0;
        while(!$stop){
            $from = $portion * $i;
            $this->db_fs->limit($portion, $from);
            $query = $this->db_fs->get('clipbins');
            $res = $query->result_array();

            if($res){
                foreach($res as $item){
                    if($this->save_clipbin($item)){
                        $imported++;
                    }
                }
            }
            else{
                $stop = true;
            }
            $i++;
            echo $imported, PHP_EOL;
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;

        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    private function save_clipbin($clipbin) {
        if ($clipbin['clipbin'] != '_pricing' && $clipbin['clipbin'] != 'Order') {

            $this->db->select('id');
            $query = $this->db->get_where('lib_users', array('login' => $clipbin['username']));
            $user = $query->result_array();
            if(!$user)
                return false;

            $data['client_id'] = $user[0]['id'];
            $data['is_default'] = $clipbin['clipbin'] == 'Default' ? 1 : 0;
            $data['title'] = $clipbin['clipbin'];
            $data['ctime'] = $clipbin['lasttime'];
            $data['mtime'] = $clipbin['createdate'];

            $this->db->select('id');
            $query = $this->db->get_where('lib_lb', array('client_id' => $data['client_id'], 'title' => $data['title']));
            $exists = $query->result_array();
            if($exists)
                return false;

            $this->db_master->insert('lib_lb', $data);
            $id = $this->db_master->insert_id();

            $this->db->select('id');
            $query = $this->db_fs->get_where('clip', array('username' => $clipbin['username']));
            $clips = $query->result_array();
            if ($clips) {
                foreach ($clips as $item) {
                    if ($item['clip'] == $clipbin['clipbin']) {
                        $this->db->select('id');
                        $query = $this->db->get_where('lib_clips', array('code' => $item['product']));
                        $clip = $query->result_array();
                        if ($clip) {
                            $this->db_master->insert('lib_lb_items', array(
                                'lb_id' => $id,
                                'type' => 2,
                                'item_id' => $clip[0]['id']
                            ));
                        }
                    }
                }
            }
        }
        else{
            return false;
        }
    }

    public function sequences() {
        @set_time_limit(345600);
        echo 'Start', PHP_EOL;
        $this->db_master->truncate('lib_lb');
        $this->db_master->truncate('lib_lb_folders');
        $this->db_master->truncate('lib_lb_items');

        $time_start = microtime(1);
        $portion = 1000;
        $stop = false;
        $i = 0;
        $imported = 0;
        while(!$stop){
            $from = $portion * $i;
            $this->db_fs->limit($portion, $from);
            $query = $this->db_fs->get('clipbins');
            $res = $query->result_array();

            if($res){
                foreach($res as $item){
                    if($this->save_sequence($item)){
                        $imported++;
                    }
                }
            }
            else{
                $stop = true;
            }
            $i++;
            echo $imported, PHP_EOL;
        }
        $time_end = microtime(1);
        $time = $time_end - $time_start;

        echo 'Finish: imported ' . $imported . ', duration ' . $time, PHP_EOL;
    }

    public function order_owners() {
        $query = $this->db->query("SELECT id FROM lib_orders WHERE client_id = 0");
        $rows = $query->result_array();
        foreach ($rows as $order) {
            $this->db_fs->where('orderid', $order['id']);
            $query = $this->db_fs->get('orders');
            $item = $query->result_array();
            if ($item) {
                $item = $item[0];
                if($item['username']) {
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_users', array('login' => $item['username']));
                    $res3 = $query->result_array();
                    if($res3) {
                        $this->db_master->where('id', $order['id']);
                        $this->db_master->update('lib_orders', array('client_id' => $res3[0]['id']));
                    }
                }
            }
        }
    }

    public function check_clips(){
        for($i=0;$i<Importdb::CLIP_LOOPS;$i++){
            $offset=$i*Importdb::LIMIT;
            $all=Importdb::CLIP_LOOPS*Importdb::LIMIT;
            $this->db_fs->select('products.*,product_media.media_dv,product_media.file_extension');
            $this->db_fs->join('product_media', 'product_media.tape = products.tape');
            $oldClips = $this->db_fs->get('products',Importdb::LIMIT,$offset)->result_array();
            if(!empty($oldClips)){
                foreach($oldClips as $k=>$v){
                    $file_path = '/storage/' . trim($v['media_dv'], '/') . '/' . $v['tape'] . '/' . $v['product'] . '.' . trim($v['file_extension'], '.');
                    if(is_file($file_path)){
                        $item=$this->db->get_where('lib_clips',array('id'=>$v['id']))->result_array();
                        if(empty($item)){
                            $string = $file_path.PHP_EOL;
                            echo $string."\t\n";
                            file_put_contents( FCPATH . '___check_clips.log', $string, FILE_APPEND );
                        }
                    }
                }
                $this->dump(__FUNCTION__.' '.$offset.'/'.$all);
            }else{$this->dump(__FUNCTION__.' NO ITEMS');}
        }
    }

    public function check_files_list($listPath='___check_clips.log'){
        if(is_file($listPath)){
            $file = file_get_contents($listPath);
            $aFile=explode("\n",$file);
            if(empty($aFile)) die('Empty list!');
            foreach($aFile as $path){
                if(!is_file($path)) echo $path.' NOT exist'.PHP_EOL;
            }
        }else{echo 'Not exist filelist!';}
    }

    public function check_keywords(){
        $this->_tocsv(array('Clip Code','section','keyword','action'),1,FCPATH.'___keywords.csv');
        for($i=0;$i<1/*Importdb::CLIP_LOOPS*/;$i++){
            $offset=$i*Importdb::LIMIT;
            $all=Importdb::CLIP_LOOPS*Importdb::LIMIT;
            $clips = $this->db->get('lib_clips',Importdb::LIMIT,$offset)->result_array();

            $keywords = array();
            if(!empty($clips)){
                foreach($clips as $clip){
                    $oldClipData = $this->db_fs->get_where('product_opt', array('product' => $clip['code']))->result_array();
                    if(empty($oldClipData)){
                        $this->dump(__FUNCTION__.' '.$clip['code'].'NOT KEYWORDS'); continue;
                    }
                    foreach($oldClipData as $oldClip){
                        if($oldClip['key'] && array_key_exists($oldClip['key'], $this->imported_options) && $oldClip['key'] !='Country') {
                            if(!empty($oldClip['value'])){
                                $keywords=explode(',', $oldClip['value']);
                                foreach($keywords as $word){
                                    $word=addslashes($word);
                                    $action='';
                                    $keyword=$this->db->query('SELECT * FROM lib_keywords WHERE
                                    section = "'.$this->imported_options[$oldClip['key']].'" AND keyword="'.$word.'" AND
                                    (provider_id='.$clip['client_id'].' OR provider_id=0)')->result_array();
                                    // Get Keyword(s) ID(s) $kk ---------------
                                    $kk=array();
                                    if(empty($keyword)){
                                        // Add new keyword to DB
                                        $this->db_master->insert('lib_keywords',array(
                                            'section' => $this->imported_options[$oldClip['key']],
                                            'keyword' => $word,
                                            'provider_id' => $clip['client_id'],
                                            'hidden' => 1
                                        ));
                                        $keyword=$this->db->get_where('lib_keywords',array(
                                            'section' => $this->imported_options[$oldClip['key']],
                                            'keyword' => $word,
                                            'provider_id' => $clip['client_id']
                                        ))->result_array();
                                        $this->dump(__FUNCTION__.' '.$word.'NOT EXIST IN DB. Added.');
                                        $action='ADD NEW KEYWORD, ';
                                    }
                                    foreach($keyword as $keyw)
                                        $kk[]=$keyw['id'];
                                    // -------------------------------------
                                    if(!empty($kk[0])){
                                        $this->db->where_in('keyword_id', $kk);
                                        $this->db->where('clip_id',$clip['id']);
                                        $res=$this->db->get('lib_clip_keywords')->result_array();
                                        if(empty($res)){
                                            //Add keyword to clip
                                            $this->db_master->query('UPDATE lib_clips_content SET '.$this->imported_options[$oldClip['key']].'=concat('.
                                                $this->imported_options[$oldClip['key']].',",'.addslashes($keyword[0]['keyword']).'") WHERE clip_id='.$clip['id']);
                                            $this->db_master->insert('lib_clip_keywords',array(
                                                'clip_id' => $clip['id'],
                                                'keyword_id' => $keyword[0]['keyword']
                                            ));
                                            $this->_tocsv(array($clip['code'],$this->imported_options[$oldClip['key']],$keyword[0]['keyword'],$action.'ASSIGN KEYWORD'));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $this->dump(__FUNCTION__.' '.$offset.'/'.$all);
            }else{$this->dump(__FUNCTION__.' NO ITEMS');}
        }
        $this->_tocsv(array('Clip Code','section','keyword','action'),2);
    }

    /**
     * Function delete dublicates with lib_clip_keywords
     */
    public function del_dublicate_clip_keywords(){
        $code='
            $kwds=$this->db->query("SELECT *,count(keyword_id) FROM `lib_clip_keywords` WHERE clip_id=".$item["id"]." GROUP BY keyword_id having count(keyword_id) > 1")->result_array();
            if(!empty($kwds)){
                //var_dump(array(count($kwds),json_encode($kwds)));
                foreach($kwds as $kwd){
                    $this->db_master->delete("lib_clip_keywords",array("clip_id"=>$kwd["clip_id"],"keyword_id"=>$kwd["keyword_id"]));
                    $this->db_master->insert("lib_clip_keywords",array("clip_id"=>$kwd["clip_id"],"keyword_id"=>$kwd["keyword_id"]));
                }

            }
        ';
        $this->_cicle('select * from lib_clips ',$code);
    }

    /**
     * Function delete dublicates keywords and replace in Clips and Keywords Sets. Except general Keyword ID $gKeywordId
     */
    public function del_dublicate_keywords(){
        $this->_tocsv(array('id','Keyword','Section','Updated keywords IDs','Updated Clips','Updated Logging Templates','Updated Keyword Sets'),1,FCPATH.'___del_dbl_kwdsV2.csv');
        $code='
            $item["keyword"]=strtolower(trim($item["keyword"]));
            $item["keyword"]=addslashes($item["keyword"]);
            $gKeyword=$db->query("SELECT * FROM lib_keywords WHERE TRIM(LOWER(keyword)) LIKE \"".$item["keyword"]."\" AND section=\"".$item["section"]."\" AND provider_id=0 ORDER BY old ASC")->result_array();
            if(!empty($gKeyword[0]["id"])){
                $this->_del_dublicate_keywords_update($item,$gKeyword[0]["id"]);
            }else{
                $gpKeywords=$db->query("SELECT * FROM lib_keywords WHERE TRIM(LOWER(keyword)) LIKE \"".$item["keyword"]."\" AND section=\"".$item["section"]."\" GROUP BY provider_id HAVING COUNT(*) >1")->result_array();
                if(!empty($gpKeywords[0]["id"])){
                    foreach($gpKeywords as $gpKeyword){
                        $this->_del_dublicate_keywords_update($item,$gpKeyword["id"],$gpKeyword["provider_id"]);
                    }
                }
            }
        ';
        // ------------------------- CICLE -------------------------------
        $sections=$this->imported_options;
        unset($sections["Country"]);

        foreach($sections as $section){
            $query='SELECT * , COUNT( * ) AS all_count
            FROM  `lib_keywords`
            WHERE section =  "'.$section.'" AND provider_id=0
            GROUP BY keyword
            HAVING COUNT( * ) >1  ';
            $this->dump(__FUNCTION__.'   ------------------'.$section);
            $this->_cicle($query,$code,$this->db,16);
        }
        $this->_tocsv(array('id','Keyword','Section','Update keywords IDs','Updated Clips','Updated Logging Templates','Updated Keyword Sets'),2);
    }
    public function del_dublicate_keywords_frontend(){
        $this->_tocsv(array('code','section','before','after'),1,FCPATH.'___del_dbl_kwdsFront.csv');
        $query='SELECT * FROM lib_clips_content ';
        $code='
            $sections=$this->imported_options;
            unset($sections["Country"]);
            $nItem=array();
            foreach($sections as $section){
                if(!empty($item[$section])){
                    $tmp=explode(",", $item[$section]);
                    foreach($tmp as $k=>$v){
                        $tmp[$k]=trim($v);
                        if(empty($tmp[$k])) unset($tmp[$k]);
                    }

                    if(!empty($tmp)){
                        $nItem[$section]=implode(",", array_unique(array_map("strtolower", $tmp)));
                        $tmp2=explode(",",$nItem[$section]);
                        if(count($tmp)>count($tmp2))
                            $this->_tocsv(array($item["title"],$section,$item[$section],$nItem[$section]));
                    }

                }
            }
            if(!empty($nItem)){
                $this->db_master->where("id",$item["id"]);
                $this->db_master->update("lib_clips_content",$nItem);
            }
        ';
        $this->_cicle($query,$code);
        $this->_tocsv(array('code','section','before','after'),2);
    }

    /**
     * Function delete dublicates keywords and replace in Clips and Keywords Sets. Except general Keyword ID $gKeywordId
     * @param $item - array Cur Keyword
     * @param $gKeywordId - general keyword ID NOT DELETE
     * @param bool $provider - search by Provider ID or all Provider
     */
    private function _del_dublicate_keywords_update($item,$gKeywordId,$provider=false){
        $where=(!$provider)?' ':' AND provider_id ='.$provider;
        $item["keyword"]=addslashes(stripslashes($item["keyword"]));
        $keywords=$this->db->query("SELECT * FROM lib_keywords WHERE TRIM(LOWER(keyword)) LIKE \"".$item["keyword"]."\" AND section=\"".$item["section"]."\" ".$where." ")->result_array();
        if(!empty($keywords)){
            $aKeywords=$aClips=$aTemplates=$aSets=array();
            foreach($keywords as $word)
                $aKeywords[]=$word['id'];
            if(!empty($aKeywords)){
                $sKeywords=implode(',',$aKeywords);
                $clips=$this->db->query('SELECT * FROM lib_clip_keywords WHERE keyword_id IN('.$sKeywords.') GROUP BY clip_id')->result_array();
                if(!empty($clips))
                    foreach($clips as $clp)
                        $aClips[]=$clp['clip_id'];
                // Update Clips
                $this->db_master->where_in('keyword_id',$aKeywords);
                $this->db_master->update('lib_clip_keywords',array('keyword_id'=>$gKeywordId));
                // Update Logging Templates keywords
                $tmplts=$this->db->query('SELECT * FROM  lib_cliplog_logging_keywords WHERE keywordId IN('.$sKeywords.') GROUP BY templateId')->result_array();
                if(!empty($tmplts))
                    foreach($tmplts as $tmplt)
                        $aTemplates[]=$tmplt['templateId'];
                $this->db_master->where_in('keywordId',$aKeywords);
                $this->db_master->update('lib_cliplog_logging_keywords',array('keywordId'=>$gKeywordId));
                // Update Keyword Sets keywords
                foreach($aKeywords as $kwd){
                    $sets=$this->db->query('SELECT * FROM lib_cliplog_metadata_templates WHERE json LIKE "%'.$kwd.'%"')->result_array();
                    if(!empty($sets)){
                        foreach($sets as $set){
                            $aSets[]=$set['id'];
                            $json=json_decode($set['json']);
                            unset($json->keywords->$kwd);
                            $json->keywords->$gKeywordId=$gKeywordId;
                            $this->db_master->where('id',$set['id']);
                            $this->db_master->update('lib_cliplog_metadata_templates',array('json'=>json_encode($json)));
                        }
                    }
                }

                $this->_tocsv(array($gKeywordId,$item["keyword"],$item["section"],json_encode($aKeywords),json_encode($aClips),json_encode($aTemplates),json_encode($aSets)));
                //$this->dump(__FUNCTION__,'UPDATE PROVIDER:'.$provider.' KEYS:'.json_encode($aKeywords).' TO:'.$gKeywordId);
            }
            //$this->dump(__FUNCTION__,"DELETE FROM lib_keywords WHERE keyword LIKE \"".$item["keyword"]."\" AND section=\"".$item["section"]."\" AND provider_id ".$where." AND id !=".$gKeywordId);
            $this->db_master->query("DELETE FROM lib_keywords WHERE TRIM(LOWER(keyword)) LIKE \"".$item["keyword"]."\" AND section=\"".$item["section"]."\" ".$where." AND id !=".$gKeywordId);
        }
    }
    public function replace_multi_keywords(){
        $this->_tocsv(array('id','Keyword','Section','Split','Used clips'),1,FCPATH.'___rplc_mlt_kwds.csv');
        $query='SELECT * FROM lib_keywords WHERE keyword LIKE ';
        $commas=$this->db->query($query.' "%,%"')->result_array();
        $semicolons=$this->db->query($query.' "%;%"')->result_array();
        $this->dump(__FUNCTION__.'---------- COMMA spliting... ----------');
        $this->_rplc_mlt_kwds($commas,',');
        $this->dump(__FUNCTION__.'---------- SemicolonS spliting... ----------');
        $this->_rplc_mlt_kwds($semicolons,';');
        $this->_tocsv(array('id','Keyword','Section','Split','Used clips'),2);
    }
    private function _rplc_mlt_kwds($arr,$separate){
        foreach($arr as $item){
            $clpKwds=$this->db->get_where('lib_clip_keywords',array('keyword_id'=>$item['id']))->result_array();
            $clps=array();
            //$tmpltKwds=$this->db->get_where('lib_cliplog_logging_keywords',array('keywordId'=>$item['id']))->result_array();
            //$setKwds=$this->db->query('SELECT * FROM lib_cliplog_metadata_templates WHERE json LIKE "\"'.$item['id'].'\""')->result_array();

            if(!empty($clpKwds[0])){
                $kwds=explode($separate,addslashes($item['keyword']));
                $gKwds=array();
                $split=array();
                foreach($kwds as $kwd){
                    $kwd=strtolower(trim($kwd));
                    if(!empty($kwd)){
                        $gKwd=$this->db->query('SELECT id,keyword FROM lib_keywords WHERE LOWER(keyword) LIKE "'.$kwd.'" AND
                        section ="'.$item['section'].'" AND (provider_id=0 OR provider_id='.$item['provider_id'].') ORDER BY provider_id ASC LIMIT 1')->result_array();
                        if(empty($gKwd[0])){
                            $this->db_master->insert('lib_keywords',array(
                                'keyword'=>$kwd,
                                'section'=>$item['section'],
                                'provider_id'=>$item['provider_id'],
                                'old'=>$item['old'],
                                'collection'=>$item['collection'],
                                'basic'=>$item['basic'],
                                'hidden'=>$item['hidden']
                            ));
                            $gKwd=$this->db->query('SELECT id,keyword FROM lib_keywords WHERE keyword LIKE "'.$kwd.'" AND
                            section ="'.$item['section'].'"')->result_array();
                            $this->dump(__FUNCTION__.' ADD KWD "'.addslashes($gKwd[0]['keyword']).'" => '.$gKwd[0]['id'].' =>'.json_encode($item));
                        }
                        if(!empty($gKwd[0]['id'])){
                            $gKwds[]=$gKwd[0]['id'];
                            $split[]=$gKwd;
                        }
                    }
                }
                foreach($clpKwds as $clp){
                    foreach($gKwds as $insertKwd){
                        $isset=$this->db->get_where('lib_clip_keywords',array('keyword_id'=>$insertKwd,'clip_id'=>$clp['clip_id']))->result_array();
                        if(empty($isset))
                            $this->db_master->insert('lib_clip_keywords',array('keyword_id'=>$insertKwd,'clip_id'=>$clp['clip_id']));
                    }
                    $clpCode=$this->db->query('SELECT code FROM lib_clips WHERE id='.$clp['clip_id'])->result_array();
                    $clps[]=$clpCode[0]['code'];
                }

                /*foreach($tmpltKwds as $tmplt)
                    foreach($gKwds as $insertKwd){
                        $isset=$this->db->get_where('lib_cliplog_logging_keywords',array('keywordId'=>$insertKwd))->result_array();
                        if(empty($isset))
                            $this->db_master->insert('lib_cliplog_logging_keywords',array('keywordId'=>$insertKwd,'templateId'=>$tmplt['templateId'],'isActive'=>$tmplt['isActive']));
                    }*/

                $this->db_master->delete('lib_clip_keywords',array('keyword_id'=>$item['id']));
                //$this->db_master->delete('lib_cliplog_logging_keywords',array('keywordId'=>$item['id']));
                $this->_tocsv(array($item['id'],$item['keyword'],$item['section'],json_encode($split),json_encode($clps)));
            }
            $this->db_master->delete('lib_keywords',array('id'=>$item['id']));
        }
    }


    public function import_clips_by_codelist(){
        $arr=$this->_txtFilePars(FCPATH.'___check_clips.log');
        $all=count($arr);

        if(!empty($arr))
            foreach($arr as $k=>$code){
                $this->clipsByCode($code);
                $clip=$this->clips_model->get_clip_by_code($code);
                if(!empty($clip['id'])){
                    $this->clips_model->add_to_index($clip['id'],false);
                    $this->dump(__FUNCTION__.' Finished:'.$k.'/'.$all);
                }
            }
        $this->clips_model->solr_optimize();
    }

    public function set_delivery_formats()
    {
        $this->db->select('id, license, pricing_category');
        $this->db->where('client_id', 1011979);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        foreach ($res as $item) {
            $this->save_delivery_formats($item['id'], $item['pricing_category'], $item['license']);
        }
    }

    // ------------------ ADDITIONS ------------------------
    /**
     * @param string $query
     * @param string $code - Code executable for every $item
     * @param bool $db - db ($this->db)
     * @param bool $loops - cicles
     */
    private function _cicle($query,$code,$db=false,$loops=false){
        $loops=(!$loops)?Importdb::CLIP_LOOPS:$loops;
        $all=$loops*Importdb::LIMIT;
        if(!$db) $db=$this->db;
        for($i=0;$i<$loops;$i++){
            $offset=$i*Importdb::LIMIT;
            $items=$db->query($query.' LIMIT '.$offset.','.Importdb::LIMIT)->result_array();
            if(!empty($items)){
                foreach($items as $key=>$item)
                    eval($code);
                $this->dump(__FUNCTION__.' '.$offset.'/'.$all);
            }else $this->dump(__FUNCTION__.' NO ITEMS');
        }
    }
    /**
     * @param $arr - data
     * @param int $action 0-write,1-open+write,2-write+close
     * @param null $filename
     */
    private function _tocsv($arr,$action=0,$filename=null){
        if($action==1 && !empty($filename)) $this->csv = fopen($filename, 'w');
        fputcsv($this->csv,$arr);
        if($action==2) fclose($this->csv);
    }
    /**
     * @param $file - name
     * @param $arr - rewrite to result
     */
    private function _xlsPars($file,&$arr){
        if(empty($this->_XL_Importer)) $this->_XL_Importer=new XL_Importer();
        if(empty($this->_curXLSfile)){
            $this->_curXLSfile=$file;
            $this->_XL_Importer->set_file_name($this->_curXLSfile);
        }
        while($row=$this->_XL_Importer->get_row()){
            $arr[]=$row;
            $this->_xlsPars($file,$arr);
        }
        $this->_curXLSfile=NULL;
    }
    /**
     * @param $file
     * @return array
     */
    private function _txtFilePars($file){
        $handle = fopen($file, "r");
        $aContent=array();
        while (!feof($handle))
            $aContent[] = str_replace(array("\r\n", "\r", "\n"), '', fgets($handle, 4096));
        fclose($handle);
        return $aContent;
    }
}
