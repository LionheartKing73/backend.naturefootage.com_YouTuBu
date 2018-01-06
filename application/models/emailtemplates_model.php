<?php

class Emailtemplates_model extends CI_Model {

	function __construct () {
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
	}

	function get_templates_count () {
		return $this->db->count_all( 'lib_email_templates' );
	}

	function get_templates_list ( $limit = array(), $order_by = '' ) {
		if ( $limit ) {
			$this->db->limit( $limit[ 'perpage' ], $limit[ 'start' ] );
		}
		if ( $order_by ) {
			$this->db->order_by( $order_by );
		}
		$query = $this->db->get( 'lib_email_templates' );
		$res = $query->result_array();
		return $res;
	}

	function save_template ( $id ) {
		$data = $this->input->post();
		unset( $data[ 'save' ], $data[ 'id' ], $data['emailtype'] );

		if ( $data ) {
			if ( $id ) {
				$this->db_master->where( 'id', $id );
				$this->db_master->update( 'lib_email_templates', $data );
				return $id;
			} else {
				$this->db_master->insert( 'lib_email_templates', $data );
				return $this->db_master->insert_id();
			}
		}
	}

	function get_template ( $id ) {
		$this->db->where( 'id', $id );
		$query = $this->db->get( 'lib_email_templates' );
		$res = $query->result_array();
		return $res[ 0 ];
	}

	function delete_templates ( $ids ) {
		if ( count( $ids ) ) {
			foreach ( $ids as $id ) {
				$this->db_master->delete( 'lib_email_templates', array( 'id' => $id ) );
			}
		}
	}

}