<?php

class Pricing_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    //For CRUD
    //Uses
    function get_uses_count() {
        return $this->db->count_all('lib_pricing_use');
    }

    function get_uses_list($limit = array(), $order_by = ''){
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);

        if($group['is_editor'] && $uid){
            $this->db->select('lib_pricing_use.*');
        }

        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res;
    }

    function save_use($id){
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $data = array();
        $provider_data = array();
        if ($group['is_admin']) {
            $data = $this->input->post();
            $data['admin_only'] = isset($data['admin_only']) ? 1 : 0;
        }
        elseif($group['is_editor'] && $id){
            $provider_data['provider_id'] = $uid;
            $provider_data['exclusive_rate'] = $this->input->post('exclusive_rate');
            $provider_data['use_id'] = $id;
        }
        $data['display'] = isset($data['display']) ? 1 : 0;
        unset($data['save'], $data['id']);
        if($data){
            if ($id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_pricing_use', $data);
                return $id;
            }
            else {
                $this->db_master->insert('lib_pricing_use', $data);
                return $this->db_master->insert_id();
            }
        }
        if($provider_data && isset($provider_data['provider_id']) && isset($provider_data['use_id'])){
            $row = $this->db->get_where('lib_provider_exclusive_rate', array('provider_id' => $provider_data['provider_id'], 'use_id' => $provider_data['use_id']))->result_array();
            if($row[0]){
                if($provider_data['exclusive_rate']){
                    $this->db_master->where('provider_id', $provider_data['provider_id']);
                    $this->db_master->where('use_id', $provider_data['use_id']);
                    $this->db_master->update('lib_provider_exclusive_rate', $provider_data);
                }
                else{
                    $this->db_master->delete('lib_provider_rf_exclusive_rate', array('provider_id' => $provider_data['provider_id'], 'use_id' => $provider_data['use_id']));
                }
            }
            elseif($provider_data['exclusive_rate']){
                $this->db_master->insert('lib_provider_exclusive_rate', $provider_data);
            }
        }
    }

    function get_use($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_uses($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_use', array('id' => $id));
            }
        }
    }

    function change_uses_visible($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_pricing_use SET display = !display where id = ' . $id);
            }
        }
    }

    //Terms
    function get_terms_count() {
        return $this->db->count_all('lib_pricing_terms');
    }

    function get_terms_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res;
    }

    function save_term($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_pricing_terms', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_pricing_terms', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_term($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_terms($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_terms', array('id' => $id));
            }
        }
    }

    //Levels
    function get_levels_count() {
        return $this->db->count_all('lib_pricing_level');
    }

    function get_levels_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_level');
        $res = $query->result_array();
        return $res;
    }

    function save_level($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_pricing_level', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_pricing_level', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_level($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_level');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_levels($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_level', array('id' => $id));
            }
        }
    }

    //RF prices
    function get_rfprices_count() {
        return $this->db->count_all('lib_rf_pricing');
    }

    function get_rfprices_list($limit = array(), $order_by = ''){
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);

        if($group['is_editor'] && $uid){
            $this->db->select('lib_rf_pricing.*, lib_provider_rf_exclusive_rate.exclusive_rate provider_exclusive_rate');
            $this->db->join('lib_provider_rf_exclusive_rate', 'lib_rf_pricing.id = lib_provider_rf_exclusive_rate.use_id AND lib_provider_rf_exclusive_rate.provider_id = '. (int)$uid, 'left');
        }

        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_rf_pricing');
        $res = $query->result_array();
        return $res;
    }

    function save_rfprice($id){
        $this->load->model('groups_model');
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        $data = array();
        $provider_data = array();
        if ($group['is_admin']) {
            $data = $this->input->post();
        }
        elseif($group['is_editor'] && $id){
            $provider_data['provider_id'] = $uid;
            $provider_data['exclusive_rate'] = $this->input->post('exclusive_rate');
            $provider_data['use_id'] = $id;
        }
        unset($data['save'], $data['id']);
        if($data){
            if ($id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_rf_pricing', $data);
                return $id;
            }
            else {
                $this->db_master->insert('lib_rf_pricing', $data);
                return $this->db_master->insert_id();
            }
        }
        if($provider_data && isset($provider_data['provider_id']) && isset($provider_data['use_id'])){
            $row = $this->db->get_where('lib_provider_rf_exclusive_rate', array('provider_id' => $provider_data['provider_id'], 'use_id' => $provider_data['use_id']))->result_array();
            if($row[0]){
                if($provider_data['exclusive_rate']){
                    $this->db_master->where('provider_id', $provider_data['provider_id']);
                    $this->db_master->where('use_id', $provider_data['use_id']);
                    $this->db_master->update('lib_provider_rf_exclusive_rate', $provider_data);
                }
                else{
                    $this->db_master->delete('lib_provider_rf_exclusive_rate', array('provider_id' => $provider_data['provider_id'], 'use_id' => $provider_data['use_id']));
                }
            }
            elseif($provider_data['exclusive_rate']){
                $this->db_master->insert('lib_provider_rf_exclusive_rate', $provider_data);
            }
        }
    }

    function get_rfprice($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_rf_pricing');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_rfprices($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_rf_pricing', array('id' => $id));
            }
        }
    }

    //Discounts
    function get_discounts_count() {
        return $this->db->count_all('lib_pricing_discounts');
    }

    function get_discounts_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_discounts');
        $res = $query->result_array();
        return $res;
    }

    function save_discount($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_pricing_discounts', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_pricing_discounts', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_discount($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_discounts');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_discounts($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_discounts', array('id' => $id));
            }
        }
    }

    //RF Discounts
    function get_rf_discounts_count() {
        return $this->db->count_all('lib_rf_pricing_discounts');
    }

    function get_rf_discounts_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_rf_pricing_discounts');
        $res = $query->result_array();
        return $res;
    }

    function save_rf_discount($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_rf_pricing_discounts', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_rf_pricing_discounts', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_rf_discount($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_rf_pricing_discounts');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_rf_discounts($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_rf_pricing_discounts', array('id' => $id));
            }
        }
    }


    //For API
    function get_license_categories($is_admin = false){
        $this->db->distinct();
        $this->db->select('category');
        if(!$is_admin) {
            $this->db->where('admin_only', 0);
        }
        $this->db->order_by('category');
        $this->db->where('collection', 'Nature Footage');
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res;
    }

    function get_natflix_license_categories($is_admin = false){
        $this->db->distinct();
        $this->db->select('category');
        if(!$is_admin) {
            $this->db->where('admin_only', 0);
        }
        $this->db->where('collection', 'NatureFlix');
        $this->db->order_by('category');
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res;
    }

    function get_license_use($category = '', $collection = '', $is_admin = false){
        $this->db->distinct();
        $this->db->select('id, use, description, display, admin_only, discount_display');
        $this->db->order_by('use');
        if(!$is_admin) {
            $this->db->where('admin_only', 0);
        }
        if($category){
            $this->db->where('category', $category);
        }
        if($collection){
            $this->db->where('collection', $collection);
        }
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res;
    }

    function get_license_term($use){

        $this->db->where('id', $use);
        $this->db->select('terms_cat');
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        $terms_cat = $res[0]['terms_cat'];

        $this->db->select('id, territory, term');
        $this->db->order_by('sort');
        if($use){
            $this->db->where('term_cat', $terms_cat);
        }
        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res;
    }

    function get_license_use_min_duration($use){
        $this->db->select('clip_minimum');
        $this->db->where('id', $use);
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res[0]['clip_minimum'];
    }

    function get_license_term_by_id($id){
        $this->db->select('id, territory, term');
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res[0];
    }

    function get_license_use_by_id($id){
        $this->db->select('id, description, clip_minimum, display');
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_use');
        $res = $query->result_array();
        return $res[0];
    }

    function get_clip_price($clip_id, $license_use, $license_term, $delivery_format = 0){

        $price_level_rate_map = array(
            1 => 'budgete',
            2 => 'standard',
            3 => 'premium',
            4 => 'exclusive'
        );

        $clip_price = false;
        $this->db->select('price_level, client_id');
        $this->db->where('id', $clip_id);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        $clip = $res[0];

        $provider_id = $clip['client_id'];

        if($clip && $clip['price_level'] && isset($price_level_rate_map[$clip['price_level']])){
            $rate_type = $price_level_rate_map[$clip['price_level']] . '_rate';
            if($provider_id){
                $this->db->select('lib_pricing_use.budgete_rate, lib_pricing_use.standard_rate, lib_pricing_use.premium_rate, lib_pricing_use.exclusive_rate, lib_pricing_use.price_level_category');
            }
            else{
                $this->db->select('budgete_rate, standard_rate, premium_rate, exclusive_rate, price_level_category');
            }
            $this->db->where('lib_pricing_use.id', $license_use);
            $query = $this->db->get('lib_pricing_use');
            $res = $query->result_array();
            if($price_level_rate_map[$clip['price_level']] == 'exclusive' && isset($res[0]['provider_exclusive_rate']) && $res[0]['provider_exclusive_rate'])
                $rate = $res[0]['provider_exclusive_rate'];
            elseif(isset($res[0][$rate_type]))
                $rate = $res[0][$rate_type];
            $price_level_category = $res[0]['price_level_category'];

            $this->db->select('factor');
            $this->db->where('id', $license_term);
            $query = $this->db->get('lib_pricing_terms');
            $res = $query->result_array();
            $term_factor = $res[0]['factor'];

//            $this->db->select('factor');
//            $this->db->where('price_level', $clip['calc_price_level']);
//            $this->db->where('category', $price_level_category);
//            $query = $this->db->get('lib_pricing_level');
//            $res = $query->result_array();
//            $price_level_factor = $res[0]['factor'];
            
            $clip_price = $rate * $term_factor;


            if($delivery_format){

                $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, pf.factor price_factor FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$clip_id . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    WHERE do.id = ' . (int)$delivery_format);

                $delivery_formats = $query->result_array();
                if($delivery_formats && $delivery_formats[0]['price_factor'])
                    $clip_price = $clip_price * $delivery_formats[0]['price_factor'];

            }
        }

        return $clip_price;
    }


    function get_rf_clip_price($clip_id, $license_use, $delivery_format = 0){

        $clip_price = false;
        $license_use = $this->get_rf_license_use($license_use, $clip_id);
        $clip_price = $license_use['price'];
        if($delivery_format){
            $query = $this->db->query('
                    SELECT do.id, do.description, pf.factor price_factor FROM lib_rf_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$clip_id . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    WHERE do.id = ' . (int)$delivery_format);
            $delivery_formats = $query->result_array();
            if($delivery_formats && $delivery_formats[0]['price_factor'])
                $clip_price = $clip_price * $delivery_formats[0]['price_factor'];

        }
        return $clip_price;
    }

    function get_rf_license_use($id, $clip_id){
        $price_level_rate_map = array(
            1 => 'budgete',
            2 => 'standard',
            3 => 'premium',
            4 => 'exclusive'
        );

        $provider_id = 0;
        if($clip_id){
            $this->db->select('price_level, client_id');
            $this->db->where('id', $clip_id);
            $query = $this->db->get('lib_clips');
            $res = $query->result_array();
            $provider_id = $res[0]['client_id'];
        }

        if($provider_id){
            $this->db->select('lib_rf_pricing.*, lib_provider_rf_exclusive_rate.exclusive_rate provider_exclusive_rate');
            $this->db->join('lib_provider_rf_exclusive_rate', 'lib_rf_pricing.id = lib_provider_rf_exclusive_rate.use_id AND lib_provider_rf_exclusive_rate.provider_id = '. (int)$provider_id, 'left');
        }
        else{
            $this->db->select('lib_rf_pricing.*');
        }

        $query = $this->db->get_where('lib_rf_pricing', array('lib_rf_pricing.id' => $id));
        $row = $query->result_array();

        if($clip_id){

            $rate_type = $price_level_rate_map[$res[0]['price_level']] . '_rate';

            if($price_level_rate_map[$res[0]['price_level']] == 'exclusive' && isset($row[0]['provider_exclusive_rate']) && $row[0]['provider_exclusive_rate'])
                $row[0]['price'] = $row[0]['provider_exclusive_rate'];
            elseif(isset($row[0][$rate_type]))
                $row[0]['price'] = $row[0][$rate_type];
        }

        return $row[0];
    }

    function get_rf_license_uses($clip_id = 0, $provider_id = 0){

        $price_level_rate_map = array(
            1 => 'budgete',
            2 => 'standard',
            3 => 'premium',
            4 => 'exclusive'
        );

        if($clip_id){
            $this->db->select('price_level');
            $this->db->where('id', $clip_id);
            $query = $this->db->get('lib_clips');
            $res = $query->result_array();

            if($provider_id){
                $this->db->select('lib_rf_pricing.*');
            }
            else{
                $this->db->select('lib_rf_pricing.*');
            }

            $rate_type = $price_level_rate_map[$res[0]['price_level']] . '_rate';
            //$this->db->select('id, license, ' . $rate_type . ' ' . 'price');
        }
        $query = $this->db->get('lib_rf_pricing');
        $res = $query->result_array();
        if($res){
            foreach($res as &$item){
                if(isset($item['provider_exclusive_rate']) && $item['provider_exclusive_rate'])
                    $item['exclusive_rate'] = $item['provider_exclusive_rate'];
                if($clip_id && isset($rate_type) && $rate_type && isset($item[$rate_type]))
                    $item['price'] = $item[$rate_type];
            }
        }
        return $res;
    }

    function get_use_discount_display($use_id) {
        $discount_display = '';
        $this->db->select('dd.body');
        $this->db->from('lib_pricing_use pu');
        $this->db->join('lib_discount_displays dd', 'pu.discount_display = dd.type', 'left');
        $this->db->where('pu.id', $use_id);
        $query = $this->db->get();
        $res = $query->result_array();
        if($res[0]['body'])
            $discount_display = $res[0]['body'];

        return $discount_display;
    }

    function get_rf_use_discount_display($use_id) {
        $discount_display = '';
        $this->db->select('dd.body');
        $this->db->from('lib_rf_pricing rfp');
        $this->db->join('lib_discount_displays_rf dd', 'rfp.discount_display = dd.type', 'left');
        $this->db->where('rfp.id', $use_id);
        $query = $this->db->get();
        $res = $query->result_array();
        if($res[0]['body'])
            $discount_display = $res[0]['body'];

        return $discount_display;
    }
}