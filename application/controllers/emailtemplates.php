<?php

/**
 * @property Emailtemplates_model $emailtemplates_model
 * @property Api $api
 * @property Builder $builder
 */

class Emailtemplates extends CI_Controller {

	var $id;
	var $langs;
	var $message;
	var $error;
	var $path;

	function __construct () {
		parent::__construct();
		$this->load->model( 'emailtemplates_model' );
		$this->id = intval( $this->uri->segment( 4 ) );
		$this->langs = $this->uri->segment( 1 );
		$this->settings = $this->api->settings();


	}

	function view () {
		$this->path = 'Email templates / Templates list';
		$data[ 'lang' ] = $this->langs;
		$limit = $this->_get_limit();
		$order_by = '';
		$all = $this->emailtemplates_model->get_templates_count();
		$data[ 'templates' ] = $this->emailtemplates_model->get_templates_list( $limit, $order_by );
		$data[ 'paging' ] = $this->api->get_pagination( 'emailtemplates/view', $all, $this->settings[ 'perpage' ] );
		$content = $this->load->view( 'emailtemplates/view', $data, TRUE );
		$this->_out( $content );
	}

	function edit () {
		if ( $this->id )
			$this->path = 'Email templates / Edit template';
		else
			$this->path = 'Email templates / Add template';
        //Debug::Dump($_POST);Debug::Dump($this->id);
		if ( $this->input->post( 'save' ) || $this->input->post( 'subject' )){//} || $this->_check_details() ) {
			$id = $this->emailtemplates_model->save_template( $this->id );
			if ( $this->id && !$this->input->post('emailtype')) {
				redirect( $this->langs . '/emailtemplates/view' );
			} else {
				redirect( $this->langs . '/emailtemplates/edit/' . $id );
			}
		}

		$data = $this->input->post();
		if ( !$this->error ) {
			$data = $this->emailtemplates_model->get_template( $this->id );
		}
		$data[ 'lang' ] = $this->langs;
		$content = $this->load->view( 'emailtemplates/edit', $data, TRUE );
		$this->_out( $content );
	}

	function delete () {
		if ( $this->id ) {
			$ids[] = $this->id;
		} else {
			$ids = $this->input->post( 'id' );
		}
		$this->emailtemplates_model->delete_templates( $ids );
		redirect( $this->langs . '/emailtemplates/view' );
	}

	function _get_limit () {
		return array( 'start' => intval( $this->uri->segment( 4 ) ), 'perpage' => $this->settings[ 'perpage' ] );
	}

	function _check_details () {
        //$body=($this->input->post('is_html'))?'body_html':'body';
        //Debug::Dump($this->input->post( 'subject' ));
		if ( !$this->input->post( 'subject' ) ){//|| !$this->input->post( $body ) ) {
			$this->error = $this->lang->line( 'empty_fields' );
			return FALSE;
		}
		return TRUE;
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