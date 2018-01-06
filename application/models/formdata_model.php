<?php

/**
 * Class Formdata_model
 *
 *
 */

class Formdata_model extends CI_Model {

    function __construct () {
        parent::__construct();
        $this->load->helper( 'emailer' );
        $this->db_master = $this->load->database( 'master', TRUE );
    }

    function save_form_request ( $table, array $data = array () ) {
        $this->db_master->insert( $table, $data );
    }

    function get_contactus_list ( $filter, $offset, $count ) {
        $this->db->order_by( 'lib_formdata_contactus.id', 'DESC' );
        $this->db->where( $filter );
        $this->db->limit( $count, $offset );
        $result = $this->db->get( 'lib_formdata_contactus' );

        $data = ( $result ) ? $result->result_array() : array ();
        $list = array ();
        foreach ( $data as $item ) {
            if ( isset( $item[ 'id' ] ) ) {
                $list[ $item[ 'id' ] ] = $item;
            }
        }
        return $list;
    }

    function get_shotrequest_list ( $filter, $offset, $count ) {
        $this->db->order_by( 'lib_formdata_shotrequest.id', 'DESC' );
        $this->db->where( $filter );
        $this->db->limit( $count, $offset );
        $result = $this->db->get( 'lib_formdata_shotrequest' );
        $data = ( $result ) ? $result->result_array() : array ();
        $list = array ();
        foreach ( $data as $item ) {
            if ( isset( $item[ 'id' ] ) ) {
                $list[ $item[ 'id' ] ] = $item;
            }
        }
        return $list;
    }

    function get_shotrequest_details ( $id, $filter ) {
        $this->db->where( array( 'id' => $id ) );
        $result = $this->db->get( 'lib_formdata_shotrequest' );
        return ( $result ) ? $result->row_array() : array ();
    }

    function get_contactus_list_count () {
        $result = $this->db->get_where( 'lib_formdata_contactus' );
        return ( $result ) ? $result->num_rows() : 0;
    }

    function get_shotrequest_list_count () {
        $result = $this->db->get_where( 'lib_formdata_shotrequest' );
        return ( $result ) ? $result->num_rows() : 0;
    }

    function delete_contactus_items ( $ids ) {
        foreach ( $ids as $id ) {
            $this->db_master->delete( 'lib_formdata_contactus', array( 'id' => $id ) );
        }
    }

    function delete_shotrequest_items ( $ids ) {
        foreach ( $ids as $id ) {
            $this->db_master->delete( 'lib_formdata_shotrequest', array( 'id' => $id ) );
        }
    }

    function is_admin () {
        $uid = ( isset( $this->session->userdata[ 'uid' ] ) && ! empty( $this->session->userdata[ 'uid' ] ) ) ? $this->session->userdata[ 'uid' ] : FALSE;
        $result = $this->db->query( "
            SELECT lib_users.id
            FROM lib_users
            JOIN lib_users_groups
                ON lib_users.group_id = lib_users_groups.id
            WHERE lib_users_groups.is_admin = 1 AND lib_users.id = {$uid}"
        );
        return ( $result && $result->num_rows() > 0 );
    }

    function get_provider_id () {
        return (integer) ( isset( $this->session->userdata[ 'uid' ] ) && ! empty( $this->session->userdata[ 'uid' ] ) ) ? $this->session->userdata[ 'uid' ] : FALSE;
    }

    function send_contactus_notification ( $provider_id, $request ) {
        Emailer::GetInstance()
            ->LoadTemplate( 'form-contactus-request' )
            ->TakeSenderSystem()
            ->TakeRecipientAdmin()
            ->SetTemplateValue( 'request', $request )
            ->Send();
        Emailer::GetInstance()->Clear();
        Emailer::GetInstance()
            ->LoadTemplate( 'form-contactus-request' )
            ->TakeSenderSystem()
            ->TakeRecipientFromId( $provider_id )
            ->SetTemplateValue( 'request', $request )
            ->Send();
        Emailer::GetInstance()->Clear();
    }

    function send_shotrequest_notification ( $provider_id, $request ) {
        Emailer::GetInstance()
            ->LoadTemplate( 'form-shotrequest-request' )
            ->TakeSenderSystem()
            ->TakeRecipientAdmin()
            ->SetTemplateValue( 'request', $request )
            ->Send();
        Emailer::GetInstance()->Clear();
        Emailer::GetInstance()
            ->LoadTemplate( 'form-shotrequest-request' )
            ->TakeSenderSystem()
            ->TakeRecipientFromId( $provider_id )
            ->SetTemplateValue( 'request', $request )
            ->Send();
        Emailer::GetInstance()->Clear();
    }


}