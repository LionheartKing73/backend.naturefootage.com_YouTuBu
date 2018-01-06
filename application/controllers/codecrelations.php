<?php
/**
 * @property Codecrelations_model $cm
 */
class Codecrelations extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct () {
        parent::__construct();
        $this->load->model( 'codecrelations_model', 'cm' );
        $this->langs = $this->uri->segment( 1 );
        $this->settings = $this->api->settings();
    }

    function submission_to_delivery () {
        if ( $this->input->post( 'save' ) && $this->input->post( 'relations' ) ) {
            $this->cm->update_submission_to_delivery_data(
                $this->input->post( 'relations' )
            );
        }
        $this->path = 'Codec Relations / Submission to Delivery';
        $data = array(
            'lang'        => $this->langs,
            'submissions' => $this->cm->get_submission_codecs(),
            'deliveries'    => $this->cm->get_pricing_category_types(),
            'relations'   => $this->cm->get_submission_to_delivery_data()
        );
        $this->out(
            $this->load->view( 'codecrelations/submission_to_delivery', $data, TRUE )
        );
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

    function ajax_submission_to_delivery () {
        $this->load->model('submission_codecs_model');
        echo json_encode(
            array(
                'status' => 'ok',
                'relations' => $this->submission_codecs_model->get_submission_to_delivery_data_for_ajax()
            )
        );
        die();
    }

}