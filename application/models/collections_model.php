<?php

class Collections_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_collections_count() {
        return $this->db->count_all('lib_collections');
    }

    function get_collections_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                if (is_array($value)) {
                    $this->db->where_in($param, $value);
                }
                else {
                    $this->db->where($param, $value);
                }
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_collections');
        $res = $query->result_array();
        return $res;
    }

    function save_collection($id){
        $data = $this->input->post();
        $data['search_term'] = $data['name'];
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_collections', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_collections', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_collection($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_collections');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_collections($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_collections', array('id' => $id));
            }
        }
    }

    /**
     * Получить имя коллекции по умолчанию для клипа
     *
     * @return array
     */
    function getDefaultClipCollectionName () {
        $this->db->where( 'default', '1' );
        $this->db->limit( 1 );
        $query = $this->db->get( 'lib_collections' );
        $row = $query->row_array();
        if ( $row ) {
            if ( isset( $row[ 'name' ] ) ) {
                return $row[ 'name' ];
            }
        }
        return NULL;
    }

}