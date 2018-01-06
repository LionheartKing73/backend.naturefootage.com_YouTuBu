<?php
class Formats_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_formats_count() {
        return $this->db->count_all('lib_pricing_format');
    }

    function get_master_formats_count () {
        $count = $this->db->get_where( 'lib_pricing_format', array( 'master' => 1 ) );
        return $count->num_rows();
    }

    function get_source_formats_count() {
        $count = $this->db->get_where( 'lib_pricing_format', array( 'master' => 0 ) );
        return $count->num_rows();
    }

    function get_formats_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_format');
        $res = $query->result_array();
        return $res;
    }

    function get_master_formats_list ( $limit = array(), $order_by = '' ) {
        if ( $limit )
            $this->db->limit( $limit[ 'perpage' ], $limit[ 'start' ] );
        if ( $order_by )
            $this->db->order_by( $order_by );
        $query = $this->db
            ->where( array( 'master' => 1 ) )
            ->get( 'lib_pricing_format' );
        $res = $query->result_array();
        return $res;
    }

    function get_source_formats_list ( $limit = array(), $order_by = '' ) {
        if ( $limit )
            $this->db->limit( $limit[ 'perpage' ], $limit[ 'start' ] );
        if ( $order_by )
            $this->db->order_by( $order_by );

        $query = $this->db
            ->where( array( 'master' => 0 ) )
            ->get( 'lib_pricing_format' );
        $res = $query->result_array();
        return $res;
    }

    function save_format($id){
        $data = $this->input->post();
        $data['master'] = isset($data['master']) ? 1 : 0;
        $data['camera'] = isset($data['camera']) ? 1 : 0;
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_pricing_format', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_pricing_format', $data);
            return $this->db_master->insert_id();
        }
    }

    function save_master_format ( $id ) {
        $data = $this->input->post();
        $data[ 'master' ] = 1;
        $data[ 'camera' ] = isset( $data[ 'camera' ] ) ? 1 : 0;
        unset( $data[ 'save' ], $data[ 'id' ] );
        if ( $id ) {
            $this->db_master->where( 'id', $id );
            $this->db_master->update( 'lib_pricing_format', $data );
            return $id;
        } else {
            $this->db_master->insert( 'lib_pricing_format', $data );
            return $this->db_master->insert_id();
        }
    }

    function save_source_format ( $id ) {
        $data = $this->input->post();
        $data[ 'master' ] = 0;
        $data[ 'camera' ] = 1;//isset( $data[ 'camera' ] ) ? 1 : 0;
        unset( $data[ 'save' ], $data[ 'id' ] );
        if ( $id ) {
            $this->db_master->where( 'id', $id );
            $this->db_master->update( 'lib_pricing_format', $data );
            return $id;
        } else {
            $this->db_master->insert( 'lib_pricing_format', $data );
            return $this->db_master->insert_id();
        }
    }

    function get_format($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_format');
        $res = $query->result_array();
        return $res[0];
    }

    function get_master_format ( $id ) {
        $this->db->where( 'id', $id );
        $this->db->where( 'master', 1 );
        $query = $this->db->get( 'lib_pricing_format' );
        $res = $query->result_array();
        return $res[ 0 ];
    }

    function get_source_format ( $id ) {
        $this->db->where( 'id', $id );
        $this->db->where( 'master', 0 );
        $query = $this->db->get( 'lib_pricing_format' );
        $res = $query->result_array();
        return $res[ 0 ];
    }

    function delete_formats($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_format', array('id' => $id));
            }
        }
    }

    //Camera chip size
    function get_camera_chip_sizes_count() {
        return $this->db->count_all('lib_camera_chip_size');
    }

    function get_camera_chip_sizes_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_camera_chip_size');
        $res = $query->result_array();
        return $res;
    }

    function save_camera_chip_size($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_camera_chip_size', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_camera_chip_size', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_camera_chip_size($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_camera_chip_size');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_camera_chip_sizes($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_camera_chip_size', array('id' => $id));
            }
        }
    }

    //Bit depths
    function get_bit_depths_count() {
        return $this->db->count_all('lib_bit_depth');
    }

    function get_bit_depths_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_bit_depth');
        $res = $query->result_array();
        return $res;
    }

    function save_bit_depth($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_bit_depth', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_bit_depth', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_bit_depth($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_bit_depth');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_bit_depths($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_bit_depth', array('id' => $id));
            }
        }
    }

    //Color spaces
    function get_color_spaces_count() {
        return $this->db->count_all('lib_color_spaces');
    }

    function get_color_spaces_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_color_spaces');
        $res = $query->result_array();
        return $res;
    }

    function save_color_space($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_color_spaces', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_color_spaces', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_color_space($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_color_spaces');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_color_spaces($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_color_spaces', array('id' => $id));
            }
        }
    }

    //Frame sizes
    function get_frame_sizes_count() {
        return $this->db->count_all('lib_frame_sizes');
    }

    function get_frame_sizes_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_frame_sizes');
        $res = $query->result_array();
        return $res;
    }

    function save_frame_size($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_frame_sizes', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_frame_sizes', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_frame_size($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_frame_sizes');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_frame_sizes($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_frame_sizes', array('id' => $id));
            }
        }
    }

    // Frame rates
    function get_frame_rates_count() {
        return $this->db->count_all('lib_frame_rates');
    }

    function get_frame_rates_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_frame_rates');
        $res = $query->result_array();
        return $res;
    }

    function save_frame_rate($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_frame_rates', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_frame_rates', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_frame_rate($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_frame_rates');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_frame_rates($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_frame_rates', array('id' => $id));
            }
        }
    }

    //Digital file formats
    function get_digital_file_formats_count() {
        return $this->db->count_all('lib_cliplog_digital_file_formats');
    }

    function get_digital_file_formats_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_cliplog_digital_file_formats');
        $res = $query->result_array();
        return $res;
    }

    function save_digital_file_format($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_cliplog_digital_file_formats', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_cliplog_digital_file_formats', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_digital_file_format($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_cliplog_digital_file_formats');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_digital_file_formats($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_cliplog_digital_file_formats', array('id' => $id));
            }
        }
    }


    //Digital file compression
    function get_file_compressions_count() {
        return $this->db->count_all('lib_file_compressions');
    }

    function get_file_compressions_list($limit = array(), $order_by = ''){
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_file_compressions');
        $res = $query->result_array();
        return $res;
    }

    function save_file_compression($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_file_compressions', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_file_compressions', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_file_compression($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_file_compressions');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_file_compressions($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_file_compressions', array('id' => $id));
            }
        }
    }



    /////
    function get_source_formats(){
        $this->db->where('camera', 1);
        $this->db->order_by('sort');
        $query = $this->db->get('lib_pricing_format');
        $res = $query->result_array();
        return $res;
    }

    function get_hd_frame_rates(){
        $this->db->order_by('sort_order');
        $query = $this->db->get('lib_hd_frame_rates');
        $res = $query->result_array();
        return $res;
    }

    function get_master_formats(){
        $this->db->where('master', 1);
        $this->db->order_by('sort');
        $query = $this->db->get('lib_pricing_format');
        $res = $query->result_array();
        return $res;
    }

    function get_digital_file_formats(){
        $this->db->order_by('sort');
        $query = $this->db->get('lib_cliplog_digital_file_formats');
        $res = $query->result_array();
        return $res;
    }

    function get_camera_chip_sizes(){
        $query = $this->db->get('lib_camera_chip_size');
        $res = $query->result_array();
        return $res;
    }

    function get_bit_depths(){
        $query = $this->db->get('lib_bit_depth');
        $res = $query->result_array();
        return $res;
    }

    function get_color_spaces(){
        $this->db->order_by('sort asc');
        $query = $this->db->get('lib_color_spaces');
        $res = $query->result_array();
        return $res;
    }

    function get_frame_sizes(){
        $query = $this->db->get('lib_frame_sizes');
        $res = $query->result_array();
        return $res;
    }

    function get_frame_rates(){
        $query = $this->db->get('lib_frame_rates');
        $res = $query->result_array();
        return $res;
    }

    function get_file_compressions(){
        $query = $this->db->get('lib_file_compressions');
        $res = $query->result_array();
        return $res;
    }

    function get_submission_codecs(){
        $query = $this->db->get('lib_submission_codecs');
        $res = $query->result_array();
        return $res;
    }

}