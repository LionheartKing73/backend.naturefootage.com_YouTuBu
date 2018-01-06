<?php

class Codecrelations_model extends CI_Model {

    function __construct () {
        parent::__construct();
        $this->db_master = $this->load->database( 'master', TRUE );
    }

    ### Закрытые методы \\ Хелперы #####################################################################################

    /**
     * Получение первой строки результата выборки с БД
     *
     * @param CI_DB_result $result
     *
     * @return array
     */
    private function _get_row ( CI_DB_result $result ) {
        if ( $result instanceof CI_DB_result ) {
            return $result->row_array();
        }
        return array();
    }

    /**
     * Получить все строки рузультата выборки с БД
     *
     * @param CI_DB_result $result
     *
     * @return array
     */
    private function _get_array ( CI_DB_result $result ) {
        if ( $result instanceof CI_DB_result ) {
            return $result->result_array();
        }
        return array();
    }

    ### Методы доступа к данным ########################################################################################

    /**
     * Получить список отношений Submission Codec <=> Delivery Category
     *
     * @return array Submission Codec <=> Delivery Category
     */
    function get_submission_to_delivery_data () {
        $relations = $this->_get_array(
            $this->db->get( 'lib_submission_to_delivery' )
        );
        $formatted = array();
        foreach ( $relations as $relation ) {
            $formatted[ $relation[ 'submission_id' ] ] = $relation;
        }
        return $formatted;
    }

    /**
     * Получить список отношений Submission Codec <=> Delivery Category
     *
     * @return array Submission Codec <=> Delivery Category
     */
    function get_submission_to_delivery_data_for_ajax () {
        $result = $this->db->query( "
            SELECT lib_submission_codecs.name AS submission, lib_submission_to_delivery.delivery_id AS delivery
            FROM lib_submission_to_delivery
            JOIN lib_submission_codecs
                ON lib_submission_codecs.id = lib_submission_to_delivery.submission_id"
        );
        $relations = $this->_get_array( $result );
        $formatted = array();
        foreach ( $relations as $relation ) {
            $formatted[ $relation[ 'submission' ] ] = $relation[ 'delivery' ];
        }
        return $formatted;
    }

    /**
     * Получить список форматов Submission Codec
     *
     * @return array Submission Codec
     */
    function get_submission_codecs () {
        return $this->_get_array(
            $this->db->get( 'lib_submission_codecs' )
        );
    }

    /**
     * Получить список форматов Delivery Category
     *
     * @return array Delivery Category
     */
    function get_pricing_category_types () {
        return $this->_get_array(
            $this->db->get_where(
                'lib_pricing_category_type',
                array(
                    'id <>' => ''
                )
            )
        );
    }

    /**
     * Сохранить список отношений Submission Codec <=> Delivery Category
     *
     * @param array $relations Submission Codec <=> Delivery Category
     */
    function update_submission_to_delivery_data ( array $relations ) {
        foreach ( $relations as $submission_id => $delivery_id ) {
            $found = $this->db->get_where(
                'lib_submission_to_delivery',
                array( 'submission_id' => $submission_id
                )
            );
            if ( $found->num_rows() > 0 ) {
                $this->db_master->update(
                    'lib_submission_to_delivery',
                    array( 'delivery_id' => $delivery_id ),
                    array( 'submission_id' => $submission_id )
                );
            } else {
                $this->db_master->insert(
                    'lib_submission_to_delivery',
                    array(
                        'delivery_id'   => $delivery_id,
                        'submission_id' => $submission_id
                    )
                );
            }
        }
    }

}