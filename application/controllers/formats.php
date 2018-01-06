<?php
/**
 * @property Formats_model $formats_model
 */
class Formats extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Formats () {
        parent::__construct();
        $this->db_master = $this->load->database( 'master', TRUE );
        $this->load->model( 'formats_model' );
        $this->id = intval( $this->uri->segment( 4 ) );
        $this->langs = $this->uri->segment( 1 );
        $this->settings = $this->api->settings();
    }

    function index () {
        show_404();
    }

    function view () {
        show_404();
    }

    function edit () {
        show_404();
    }

    function ord () {
        show_404();
    }

    function delete () {
        show_404();
    }

    function master_view () {
        $this->path = 'Media Formats / Master Format';
        $data[ 'lang' ] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'master, camera, sort';
        $all = $this->formats_model->get_master_formats_count();
        $data[ 'formats' ] = $this->formats_model->get_master_formats_list( $limit, $order_by );
        $data[ 'paging' ] = $this->api->get_pagination( 'formats/master_view', $all, $this->settings[ 'perpage' ] );
        $content = $this->load->view( 'formats/master_view', $data, TRUE );
        $this->out( $content );
    }

    function source_view () {
        $this->path = 'Media Formats / Source Format';
        $data[ 'lang' ] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort, camera';
        $all = $this->formats_model->get_source_formats_count();
        $data[ 'formats' ] = $this->formats_model->get_source_formats_list( $limit, $order_by );
        $data[ 'paging' ] = $this->api->get_pagination( 'formats/source_view', $all, $this->settings[ 'perpage' ] );
        $content = $this->load->view( 'formats/source_view', $data, TRUE );
        $this->out( $content );
    }

    function master_edit () {
        $this->path = ( $this->id ) ? 'Media Formats / Edit Master Format' : 'Media Formats / Add Master Format';
        if ( $this->input->post( 'save' ) && $this->check_details() ) {
            $this->formats_model->save_master_format( $this->id );
            redirect( $this->langs . '/formats/master_view' );
        }
        $data = $this->input->post();
        if ( !$this->error ) {
            $data = $this->formats_model->get_master_format( $this->id );
        }
        $data[ 'lang' ] = $this->langs;
        $content = $this->load->view( 'formats/master_edit', $data, TRUE );
        $this->out( $content );
    }

    function source_edit () {
        $this->path = ( $this->id ) ? 'Media Formats / Edit Source Format' : 'Media Formats / Add Source Format';
        if ( $this->input->post( 'save' ) && $this->check_details() ) {
            $this->formats_model->save_source_format( $this->id );
            redirect( $this->langs . '/formats/source_view' );
        }
        $data = $this->input->post();
        if ( !$this->error ) {
            $data = $this->formats_model->get_source_format( $this->id );
        }
        $data[ 'lang' ] = $this->langs;
        $content = $this->load->view( 'formats/source_edit', $data, TRUE );
        $this->out( $content );
    }

    function master_ord () {
        $ids = $this->input->post( 'ord' );
        if ( is_array( $ids ) && count( $ids ) ) {
            foreach ( $ids as $id => $ord ) {
                $this->db_master->where( 'id', $id );
                $this->db_master->update( 'lib_pricing_format', array( 'sort' => intval( $ord ) ) );
            }
        }
        redirect( $this->langs . '/formats/master_view' );
    }

    function source_ord () {
        $ids = $this->input->post( 'ord' );
        if ( is_array( $ids ) && count( $ids ) ) {
            foreach ( $ids as $id => $ord ) {
                $this->db_master->where( 'id', $id );
                $this->db_master->update( 'lib_pricing_format', array( 'sort' => intval( $ord ) ) );
            }
        }
        redirect( $this->langs . '/formats/source_view' );
    }

    function master_delete () {
        $ids = ( $this->id ) ? array( $this->id ) : $this->input->post( 'id' );
        $this->formats_model->delete_formats( $ids );
        redirect( $this->langs . '/formats/master_view' );
    }

    function source_delete () {
        $ids = ( $this->id ) ? array( $this->id ) : $this->input->post( 'id' );
        $this->formats_model->delete_formats( $ids );
        redirect( $this->langs . '/formats/source_view' );
    }

    function get_limit () {
        return array( 'start' => intval( $this->uri->segment( 4 ) ), 'perpage' => $this->settings[ 'perpage' ] );
    }

    function check_details () {
        if ( !$this->input->post( 'format' ) ) {
            $this->error = $this->lang->line( 'empty_fields' );
            return FALSE;
        }
        return TRUE;
    }

    function out ( $content = NULL, $pagination = NULL, $type = 1 ) {
        $this->builder->output(
            array(
                'content'    => $content,
                'path'       => $this->path,
                'pagination' => $pagination,
                'error'      => $this->error,
                'message'    => $this->message
            ),
            $type
        );
    }
}