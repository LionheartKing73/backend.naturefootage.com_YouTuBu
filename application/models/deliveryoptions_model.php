<?php

class Deliveryoptions_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    //For CRUD
    //Delivery options
    function get_deliveryoptions_count() {
        return $this->db->count_all('lib_delivery_options');
    }

    function get_deliveryoptions_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_delivery_options');
        $res = $query->result_array();
        return $res;
    }

    function save_deliveryoption($id){
        $data = $this->input->post();
        $data['conversion'] = isset($data['conversion']) ? 1 : 0;
        $data['lab_id'] = ($data['delivery'] == 'Lab') ? $data['lab_id'] : 0;
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_delivery_options', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_delivery_options', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_deliveryoption($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_delivery_options');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_deliveryoptions($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_delivery_options', array('id' => $id));
            }
        }
    }

    //RF delivery options
    function get_rf_deliveryoptions_count() {
        return $this->db->count_all('lib_rf_delivery_options');
    }

    function get_rf_deliveryoptions_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_rf_delivery_options');
        $res = $query->result_array();
        return $res;
    }

    function save_rf_deliveryoption($id){
        $data = $this->input->post();
        $data['admin_only'] = isset($data['admin_only']) ? 1 : 0;
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_rf_delivery_options', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_rf_delivery_options', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_rf_deliveryoption($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_rf_delivery_options');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_rf_deliveryoptions($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_rf_delivery_options', array('id' => $id));
            }
        }
    }

    //Delivery methods
    function get_methods_count() {
        return $this->db->count_all('lib_delivery_methods');
    }

    function get_methods_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_delivery_methods');
        $res = $query->result_array();
        return $res;
    }

    function save_method($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        $data['delivery'] = 'Download';
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_delivery_methods', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_delivery_methods', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_method($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_delivery_methods');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_method($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_delivery_methods', array('id' => $id));
            }
        }
    }

    //Delivery categories
    function get_delivery_categories_count() {
        return $this->db->count_all('lib_pricing_category_type');
    }

    function get_delivery_categories_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_category_type');
        $res = $query->result_array();
        return $res;
    }

    function save_delivery_category($id){
        $data = $this->input->post();
        unset($data['save'], $data['pk']);
        if ($id) {
            $this->db_master->where('pk', $id);
            $this->db_master->update('lib_pricing_category_type', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_pricing_category_type', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_delivery_category($id){
        $this->db->where('pk', $id);
        $query = $this->db->get('lib_pricing_category_type');
        $res = $query->result_array();
        return $res[0];
    }

    function get_delivery_category_by_code($code){
        $this->db->where('id', $code);
        $query = $this->db->get('lib_pricing_category_type');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_delivery_categories($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_category_type', array('pk' => $id));
            }
        }
    }


    // Delivery price factors
    function get_delivery_price_factors_count() {
        return $this->db->count_all('lib_delivery_price_factors');
    }

    function get_delivery_price_factors_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_delivery_price_factors');
        $res = $query->result_array();
        return $res;
    }

    function save_delivery_price_factor($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_delivery_price_factors', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_delivery_price_factors', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_delivery_price_factor($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_delivery_price_factors');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_delivery_price_factors($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_delivery_price_factors', array('id' => $id));
            }
        }
    }

    function get_delivery_by_id($license,$option_id){
        if($license == 1){
            $this->db->select('lib_rf_delivery_options.id, lib_rf_delivery_options.description, lib_rf_delivery_options.delivery, lib_rf_delivery_options.resolution, lib_delivery_price_factors.factor delivery_factor');
            $this->db->join('lib_delivery_price_factors', 'lib_rf_delivery_options.price_factor = lib_delivery_price_factors.id');
            $query = $this->db->get_where('lib_rf_delivery_options', array('lib_rf_delivery_options.id' => $option_id));
            $row = $query->result_array();
        }else{
            $this->db->select('lib_delivery_options.id, lib_delivery_options.description, lib_delivery_options.price, lib_delivery_options.delivery, lib_delivery_options.resolution, lib_delivery_options.source, lib_delivery_options.destination, lib_delivery_options.format, lib_delivery_options.conversion, lib_delivery_options.lab_id, lib_delivery_price_factors.factor delivery_factor');
            $this->db->join('lib_delivery_price_factors', 'lib_delivery_options.price_factor = lib_delivery_price_factors.id');
            $query = $this->db->get_where('lib_delivery_options', array('lib_delivery_options.id' => $option_id));
            $row = $query->result_array();
        }
        return $row[0];
    }

    //For API
    function get_delivery_option($option_id, $clip_id, $frame_rate_id = 0){

        $this->db->select('license, digital_file_frame_rate, digital_file_format, color_system'); // remove aspect
        $this->db->where('id', $clip_id);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        $clip = $res[0];

        if($clip['license'] == 1){
            $this->db->select('lib_rf_delivery_options.id, lib_rf_delivery_options.description, lib_rf_delivery_options.delivery, lib_rf_delivery_options.resolution, lib_delivery_price_factors.factor delivery_factor');
            $this->db->join('lib_delivery_price_factors', 'lib_rf_delivery_options.price_factor = lib_delivery_price_factors.id');
            $query = $this->db->get_where('lib_rf_delivery_options', array('lib_rf_delivery_options.id' => $option_id));
            $row = $query->result_array();

            $description_parts = array();
            switch($row[0]['delivery']){
                case 'Transcoded':
                    if (strpos(strtolower($row[0]['description']), 'digital file') !== false){
                        $description_parts = array($clip['digital_file_format']);
                    }
                    else {
                        $description_parts = array($row[0]['description']);
                    }
                    $description_parts[] = $row[0]['resolution'] ? '(' . $row[0]['resolution'] . ')'
                        : $clip['digital_file_frame_size'];
                    $description_parts[] = $clip['digital_file_frame_rate'];
                    break;
                case 'Lab':
                    if (strpos(strtolower($row[0]['description']), 'master file') !== false){
                        $description_parts = array($row['master_format']);
                    }
                    else {
                        $description_parts = array($row[0]['description']);
                    }
                    $description_parts[] = $clip['master_frame_size'];
                    $description_parts[] = $clip['master_frame_rate'];
                    break;
                case 'Upload Submission File':
                    if (strpos(strtolower($row[0]['description']), 'digital file') !== false){
                        $description_parts = array($clip['digital_file_format']);
                    }
                    else {
                        $description_parts = array($row[0]['description']);
                    }
                    $description_parts[] = $clip['digital_file_frame_size'];
                    $description_parts[] = $clip['digital_file_frame_rate'];
                    break;
                case 'Upload Master File':
                    if (strpos(strtolower($row[0]['description']), 'master file') !== false){
                        $description_parts = array($row['master_format']);
                    }
                    else {
                        $description_parts = array($row[0]['description']);
                    }
                    $description_parts[] = $clip['master_frame_size'];
                    $description_parts[] = $clip['master_frame_rate'];
                    break;
            }

            if ($description_parts) {
                $row[0]['description'] = implode(' ', $description_parts);
            }

//            // New
//            if(strpos(strtolower($row[0]['description']), 'digital file') !== false){
//                $row[0]['description'] = implode(' ', array($clip['digital_file_format'], $clip['digital_file_frame_size'], $clip['digital_file_frame_rate']));
//            }
//            elseif(strpos(strtolower($row[0]['description']), 'master file') !== false){
//                $row[0]['description'] = implode(' ', array($clip['master_format'], $clip['master_frame_size'], $clip['master_frame_rate']));
//            }
//            else{
//                $row[0]['description'] .= ' ' . $clip['digital_file_frame_rate'];
//            }

            return $row[0];
        }
        else{

            $this->db->select('lib_delivery_options.id, lib_delivery_options.description, lib_delivery_options.price, lib_delivery_options.delivery, lib_delivery_options.resolution, lib_delivery_options.source, lib_delivery_options.destination, lib_delivery_options.format, lib_delivery_options.conversion, lib_delivery_options.lab_id, lib_delivery_price_factors.factor delivery_factor');
            $this->db->join('lib_delivery_price_factors', 'lib_delivery_options.price_factor = lib_delivery_price_factors.id');
            $query = $this->db->get_where('lib_delivery_options', array('lib_delivery_options.id' => $option_id));
            $row = $query->result_array();

            if (strpos(strtolower($row[0]['description']), 'custom frame rate') === false) {

                $description_parts = array();
                switch($row[0]['delivery']){
                    case 'Transcoded':
                        if (strpos(strtolower($row[0]['description']), 'digital file') !== false){
                            $description_parts = array($clip['digital_file_format']);
                        }
                        else {
                            $description_parts = array($row[0]['description']);
                        }
                        $description_parts[] = $row[0]['resolution'] ? '(' . $row[0]['resolution'] . ')'
                            : $clip['digital_file_frame_size'];
                        $description_parts[] = $clip['digital_file_frame_rate'];
                        break;
                    case 'Lab':
                        if (strpos(strtolower($row[0]['description']), 'master file') !== false){
                            $description_parts = array($row['master_format']);
                        }
                        else {
                            $description_parts = array($row[0]['description']);
                        }
                        $description_parts[] = $clip['master_frame_size'];
                        $description_parts[] = $clip['master_frame_rate'];
                        break;
                    case 'Upload Submission File':
                        if (strpos(strtolower($row[0]['description']), 'digital file') !== false){
                            $description_parts = array($clip['digital_file_format']);
                        }
                        else {
                            $description_parts = array($row[0]['description']);
                        }
                        $description_parts[] = $clip['digital_file_frame_size'];
                        $description_parts[] = $clip['digital_file_frame_rate'];
                        break;
                    case 'Upload Master File':
                        if (strpos(strtolower($row[0]['description']), 'master file') !== false){
                            $description_parts = array($row['master_format']);
                        }
                        else {
                            $description_parts = array($row[0]['description']);
                        }
                        $description_parts[] = $clip['master_frame_size'];
                        $description_parts[] = $clip['master_frame_rate'];
                        break;
                }

                if ($description_parts) {
                    $row[0]['description'] = implode(' ', $description_parts);
                }


//                // New algorithm
//                if(strpos(strtolower($row[0]['description']), 'digital file') !== false){
//                    $row[0]['description'] = implode(' ', array($clip['digital_file_format'], $clip['digital_file_frame_size'], $clip['digital_file_frame_rate']));
//                }
//                elseif(strpos(strtolower($row[0]['description']), 'master file') !== false){
//                    $row[0]['description'] = implode(' ', array($clip['master_format'], $clip['master_frame_size'], $clip['master_frame_rate']));
//                }
//                else{
//                    if($row[0]['source'] == 'Tape' && $row[0]['destination'] == 'File'){
//                        $row[0]['description'] .= ' ' . $clip['master_frame_rate'];
//                    }
//                    elseif($row[0]['source'] == 'Tape' && $row[0]['destination'] == 'Tape'){
//                        $row[0]['description'] .= ' ' . $clip['master_format'] . ' ' . $clip['master_frame_rate'];
//                    }
//                }
            }
            elseif($frame_rate_id){
                $this->db->where('id', $frame_rate_id);
                $custom_frame_rate = $this->db->get('lib_pricing_custom_frame_rates')->result_array();
                $row[0]['description'] .= ' ' . $custom_frame_rate[0]['format'];
            }

            return $row[0];
        }
    }

    function get_delivery_formats($clip_id, $method_id = 0){
        $this->db->select('license, digital_file_frame_rate, digital_file_format, color_system, master_format, master_frame_rate, master_frame_size, brand');
        $this->db->where('id', $clip_id);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        $clip = $res[0];

        //Delivery formats
        //Methods
        $delivery_methods = $rf_delivery_methods = $this->get_methods_list();

        //For RM Clips
        if($clip['license'] == 1){
            $query = $this->db->query('
                    SELECT do.id, do.description, pf.factor price_factor FROM lib_rf_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$clip_id . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');
            $delivery_formats = $query->result_array();
            if(count($delivery_formats)){
                foreach($rf_delivery_methods as $key => $method){
                    if(($method_id && $method_id == $method['id']) || !$method_id)
                        foreach($delivery_formats as $format){
                            $format['delivery'] = 'Download';
                            if($format['delivery'] == $method['delivery']){
                                if(!isset($rf_delivery_methods[$key]['formats'])){
                                    $rf_delivery_methods[$key]['formats'] = array();
                                }
                                if(strpos(strtolower($format['description']), 'digital file') !== false){
                                    $format['description'] = implode(' ', array($clip['digital_file_format'], $clip['digital_file_frame_size'], $clip['digital_file_frame_rate']));
                                }
                                elseif(strpos(strtolower($format['description']), 'master file') !== false){
                                    $format['description'] = implode(' ', array($clip['master_format'], $clip['master_frame_size'], $clip['master_frame_rate']));
                                }
                                else{
                                    $format['description'] .= ' ' . $clip['digital_file_frame_rate'];
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
                return $rf_delivery_methods;
            }
        }
        else{
            $query = $this->db->query('
                    SELECT do.id, do.description, do.price, do.delivery, do.source, do.destination, do.format, do.conversion, do.resolution, pf.factor price_factor
                    FROM lib_delivery_options do
                    INNER JOIN lib_clips_delivery_formats cdf ON do.id = cdf.format_id AND cdf.clip_id = ' . (int)$clip_id . '
                    LEFT JOIN lib_delivery_price_factors pf ON do.price_factor = pf.id
                    ORDER BY do.display_order');

            $delivery_formats = $query->result_array();
            if(count($delivery_formats)){
                foreach($delivery_methods as $key => $method){
                    if(($method_id && $method_id == $method['id']) || !$method_id)
                        foreach($delivery_formats as $format){
                            $format['first_description'] = $format['description'];
                            if($format['delivery'] == $method['delivery']){
                                if(!isset($delivery_methods[$key]['formats'])){
                                    $delivery_methods[$key]['formats'] = array();
                                }
                                if(strpos(strtolower($format['description']), 'custom frame rate') === false){
                                    if(strpos(strtolower($format['description']), 'digital file') !== false){
                                        $description_parts = array($clip['digital_file_format']);
                                        if ($format['delivery'] == 'Transcoded') {
                                            if ($format['resolution']) {
                                                $description_parts[] = '(' . $format['resolution'] . ')';
                                            }
                                            else {
                                                $description_parts[] = $clip['digital_file_frame_size'];
                                            }
                                            $description_parts[] = $clip['digital_file_frame_rate'];
                                        }
                                        else {
                                            $description_parts[] = $clip['digital_file_frame_size'];
                                            $description_parts[] = $clip['digital_file_frame_rate'];
                                        }
                                        $format['description'] = implode(' ', $description_parts);
                                        $format['default'] = 1;
                                    }
                                    elseif(strpos(strtolower($format['description']), 'master file') !== false){
                                        $description_parts = array($clip['master_format']);
                                        if ($format['delivery'] == 'Transcoded') {
                                            if ($format['resolution']) {
                                                $description_parts[] = '(' . $format['resolution'] . ')';
                                            }
                                            else {
                                                $description_parts[] = $clip['master_frame_size'];
                                            }
                                            $description_parts[] = $clip['master_frame_rate'];
                                        }
                                        else {
                                            $description_parts[] = $clip['master_frame_size'];
                                            $description_parts[] = $clip['master_frame_rate'];
                                        }
                                        $format['description'] = implode(' ', $description_parts);
                                    }
                                    else{
//                                        if($format['source'] == 'Tape' && $format['destination'] == 'File'){
//                                            $format['description'] .= ' ' . $clip['master_frame_rate'];
//                                        }
//                                        elseif($format['source'] == 'Tape' && $format['destination'] == 'Tape'){
//                                            $format['description'] .= ' ' . $clip['master_format'] . ' ' . $clip['master_frame_rate'];
//                                        }
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
                return $delivery_methods;
            }
        }

        return false;
    }


    function get_delivery_option_price($option_id, $clip_id){

        $this->db->select('license');
        $this->db->where('id', $clip_id);
        $query = $this->db->get('lib_clips');
        $res = $query->result_array();
        $clip = $res[0];

        if($clip['license'] == 1){
            return 0;
        }
        else{
            $this->db->select('price');
            $query = $this->db->get_where('lib_delivery_options', array('id' => $option_id));
            $row = $query->result_array();
            return $row[0]['price'];
        }
    }
}