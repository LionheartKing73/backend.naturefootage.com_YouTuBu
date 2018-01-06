<?php

/**
 * @property Formdata_model $fd
 * @property Api            $api
 * @property Builder        $builder
 */

class Formdata extends CI_Controller {

    private $settings;
    private $path;
    private $error;
    private $message;
    private $langs;
    private $action;
    private $id;

    function __construct () {
        parent::__construct();
        $this->load->model( 'formdata_model', 'fd' );
        $this->settings = $this->api->settings();
        $this->langs = $this->uri->segment( 1 );
        $this->action = $this->uri->segment( 4 );
        $this->id = $this->uri->segment( 5 );
    }

    function contactus () {
        if ( $this->action == 'delete' ) {
            $this->_contactus_delete();
        }
        $content = array();
        list( $offset, $count ) = $this->_get_limit();
        $content[ 'list' ] = $this->fd->get_contactus_list( array(), $offset, $count );
        $this->_contactus_out( $content );
    }

    function shotrequest () {
        if ( $this->fd->is_admin() ) {
            $filters = array ();
        } else {
            $filters = array(
                'provider_id' => $this->fd->get_provider_id()
            );
        }
        if ( $this->action == 'delete' ) {
            $this->_shotrequest_delete();
        }
        $content = array();
        if ( $this->action == 'details' ) {
            $content = $this->fd->get_shotrequest_details( $this->id, $filters );
            $this->_shotrequest_details_out( $content );
        } else {
            list( $offset, $count ) = $this->_get_limit();
            $content[ 'list' ] = $this->fd->get_shotrequest_list( $filters, $offset, $count );
            $this->_shotrequest_list_out( $content );
        }
    }

    function _contactus_delete () {
        $id = ( $this->id ) ? array( $this->id ) : $_POST[ 'id' ];
        $this->fd->delete_contactus_items( $id );
    }

    function _shotrequest_delete () {
        $id = ( $this->id ) ? array( $this->id ) : $_POST[ 'id' ];
        $this->fd->delete_shotrequest_items( $id );
    }

    function _contactus_out ( $content ) {
        $this->path = 'Form requests / Contact Us';
        $all = $this->fd->get_contactus_list_count();
        $content[ 'lang' ] = $this->langs;
        $content[ 'paging' ] = $this->api->get_pagination( 'formdata/contactus', $all, $this->settings[ 'perpage' ] );
        $this->_out( $this->load->view( 'formdata/contactus_list', $content, TRUE ) );
    }

    function _shotrequest_list_out ( $content ) {
        $this->path = 'Form requests / Shot Request';
        $all = $this->fd->get_shotrequest_list_count();
        $content[ 'lang' ] = $this->langs;
        $content[ 'paging' ] = $this->api->get_pagination( 'formdata/shotrequest', $all, $this->settings[ 'perpage' ] );
        $this->_out( $this->load->view( 'formdata/shotrequest_list', $content, TRUE ) );
    }

    function _shotrequest_details_out ( $content ) {
        $this->path = 'Form requests / Shot Request / Details';
        $all = $this->fd->get_shotrequest_list_count();
        $content[ 'lang' ] = $this->langs;
        $content[ 'paging' ] = $this->api->get_pagination( 'formdata/shotrequest', $all, $this->settings[ 'perpage' ] );
        $this->_out( $this->load->view( 'formdata/shotrequest_details', $content, TRUE ) );
    }

    function _get_limit () {
        return array( (integer) $this->uri->segment( 4 ), $this->settings[ 'perpage' ] );
    }

    function _out ( $content = NULL, $pagination = NULL, $type = 1 ) {
        $this->builder->output( array(
            'content' => $content,
            'path' => $this->path,
            'pagination' => $pagination,
            'error'   => $this->error,
            'message' => $this->message ), $type );
    }

}