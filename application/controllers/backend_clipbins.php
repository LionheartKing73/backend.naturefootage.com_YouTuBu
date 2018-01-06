<?php

/** @noinspection PhpIncludeInspection */
require_once( APPPATH . 'libraries/Cliplog/Clipbin/ClipbinRequest.php' );

use Libraries\Cliplog\Clipbin\ClipbinRequest;

/**
 * Class Clipbins
 *
 * @property clipbins_model    $clipbins_model
 * @property bin_model         $bin_model
 * @property Backend_bin_model $backend_bin_model
 * @property groups_model      $groups_model
 * @property users_model       $users_model
 */
class Backend_clipbins extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $group;
    var $uid;
    var $filter;

    function Backend_clipbins () {
        parent::__construct();
        $this->db_master = $this->load->database( 'master', TRUE );
        //$this->load->model( 'clipbins_model' );
        $this->load->model( 'backend_bin_model' );
        $this->load->model( 'groups_model' );
        $this->load->model( 'users_model' );
        $this->load->model('cliplog_model');
        $this->id = intval( $this->uri->segment( 4 ) );
        $this->langs = $this->uri->segment( 1 );

        $this->settings = $this->api->settings();
        $this->path = 'Clips section / Clipbin Manager';
        $this->set_group();
    }

    function index () {
        if ( $this->api->is_ajax_request() && $this->input->post( 'action' ) ) {
            $this->cliplog_model->getBackendSession();
            switch ( $this->input->post( 'action' ) ) {
                case 'save_clipbin':
                    $res = $this->save_clipbin_ajax();
                    break;
                case 'save_folder':
                    $res = $this->save_folder_ajax();
                    break;
                case 'get_clipbin':
                    $res = $this->get_clipbin_ajax();
                    break;
                case 'delete_clipbin':
                    $res = $this->delete_clipbin_ajax();
                    break;
                case 'set_default_bin':
                    $res = $this->set_default_bin_ajax();
                    break;
                case 'get_folder':
                    $res = $this->get_folder_ajax();
                    break;
                case 'delete_folder':
                    $res = $this->delete_folder_ajax();
                    break;
                case 'make_gallery':
                    $res = $this->make_gallery_ajax();
                    break;
                case 'make_featured_gallery':
                    $res = $this->make_featured_gallery_ajax();
                    break;
                case 'make_clipbin':
                    $res = $this->make_clipbin_ajax();
                    break;
                case 'add_thumb_gallery':
                    $res = $this->add_thumb_gallery_ajax();
                    break;
                case 'make_ordinary_gallery':
                    $res = $this->make_ordinary_gallery_ajax();
                    break;
                case 'make_sequence':
                    $res = $this->make_sequence_ajax();
                    break;
                case 'delete_bin_item':
                    $res = $this->delete_current_bin_items_ajax();
                    break;
                case 'add_items':
                    $res = $this->add_items_ajax();
                    break;
                case 'filter_clipbins':
                    $res = $this->filter_clipbin_ajax();
                    break;
                default:
                    $res = array ();
            }
            $this->cliplog_model->setBackendSession();
            $this->output->set_content_type( 'application/json' );
            echo json_encode( $res );
            exit();
        }
        show_404();
    }

    function view () {
        $data[ 'filters' ] = $this->get_filters();
        $data[ 'lang' ] = $this->langs;
        $limit = $this->get_limit();

        if ( $this->group[ 'is_editor' ] && $this->uid ) {
            $this->filter[ 'lb.provider_id' ] = (int) $this->uid;
        }

        if ( $this->filter )
            $this->filter = $this->prepare_mysql_filter( $this->filter );

        $all = $this->backend_bin_model->get_clipbins_count( $this->filter );
        $data[ 'clipbins' ] = $this->backend_bin_model->get_clipbins_list( $this->filter, $limit );
        $data[ 'paging' ] = $this->api->get_pagination( 'clipbins/view', $all, $this->settings[ 'perpage' ] );
        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $res[ 'clipbins_list' ] = $this->load->view( 'cliplog/clipbins_list', array ( 'clipbins' => $data[ 'clipbins' ], 'lang' => $data[ 'lang' ] ), TRUE );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        } else {
            $content = $this->load->view( 'clipbins/view', $data, TRUE );
            $this->out( $content );
        }
    }

    function edit () {

        $action = $this->id ? 'Edit' : 'Add';
        $this->path .= ' / ' . $action;

        $check = $this->backend_bin_model->get_clipbin( $this->id );
        if ( $check[ 'provider_id' ] === $this->uid || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->input->post( 'save' ) && $this->check_details() ) {
                $id = $this->backend_bin_model->save_clipbin( $this->id );
                if ( $this->input->is_ajax_request() ) {
                    $res = array ( 'success' => 1 );
                    $this->output->set_content_type( 'application/json' );
                    echo json_encode( $res );
                    exit();
                } else
                    redirect( $this->langs . '/clipbins/view' );
            }

            $data = $this->input->post();
            if ( !$this->error ) {
                $data = $this->backend_bin_model->get_clipbin( $this->id );
            }

            $data[ 'error' ] = $this->error;

            if ( $this->group[ 'is_admin' ] ) {
                $data[ 'providers' ] = $this->users_model->get_providers_list();
                $data[ 'clients' ] = $this->users_model->get_clients_list();
                $data[ 'is_admin' ] = TRUE;
            } elseif ( $this->group[ 'is_editor' ] ) {
                $data[ 'is_editor' ] = TRUE;
                $data[ 'editor_id' ] = $this->session->userdata( 'client_uid' );
            }

            $data[ 'lang' ] = $this->langs;
            $content = $this->load->view( 'clipbins/edit', $data, TRUE );
            $this->out( $content );
        } else {
            redirect( $this->langs . '/clipbins/view' );
        }
    }

    function ord () {
        $ids = $this->input->post( 'ord' );

        if ( is_array( $ids ) && count( $ids ) ) {
            foreach ( $ids as $id => $ord ) {
                $this->db_master->where( 'id', $id );
                $this->db_master->update( 'lib_clipbins', array ( 'sort' => intval( $ord ) ) );
            }
        }
        redirect( $this->langs . '/clipbins/view' );
    }

    function delete () {
        $check = $this->backend_bin_model->get_clipbin( $this->id );
        if ( $check[ 'provider_id' ] === $this->uid || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->id )
                $ids[ ] = $this->id;
            else
                $ids = $this->input->post( 'id' );

            $this->backend_bin_model->delete_clipbins( $ids );
        }
        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        } else
            redirect( $this->langs . '/clipbins/view' );

    }

    function make_gallery () {
        $check = $this->backend_bin_model->get_clipbin( $this->id );
        if ( $check[ 'provider_id' ] === $this->uid || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->id )
                $ids[ ] = $this->id;
            else
                $ids = $this->input->post( 'id' );

            $this->backend_bin_model->make_gallery( $ids );
        }
        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        } else {
            redirect( $this->langs . '/clipbins/view' );
        }
    }

    function items () {
        if ( $this->input->post( 'items_ids' ) && $this->id && $_SESSION[ 'login' ] ) {
            $this->backend_bin_model->add_items( $_SESSION[ 'login' ], $this->input->post( 'items_ids' ), $this->id );
        }

        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );
            echo json_encode( $res );
            exit();
        }
    }

    function create_clipbin () {
        if ( $this->input->post( 'items_ids' ) && $this->id && $_SESSION[ 'login' ] ) {
            $this->backend_bin_model->add_items( $_SESSION[ 'login' ], $this->input->post( 'items_ids' ), $this->id );
        }

        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );
            echo json_encode( $res );
            exit();
        }
    }

    function get_clients () {
        if ( isset( $_REQUEST[ 'term' ] ) ) {
            $this->db->like( 'fname', $_REQUEST[ 'term' ] );
            $this->db->or_like( 'lname', $_REQUEST[ 'term' ] );
        }

        $this->db->select( 'id, fname, lname' );
        $this->db->from( 'lib_users' );
        $this->db->order_by( 'fname' );
        $query = $this->db->get();
        $result = $query->result_array();

        if ( count( $result ) ) {
            $res = array ();
            foreach ( $result as $result_item ) {
                $data = array (
                    'value'     => $result_item[ 'fname' ] . ' ' . $result_item[ 'lname' ],
                    'label'     => $result_item[ 'fname' ] . ' ' . $result_item[ 'lname' ],
                    'client_id' => $result_item[ 'id' ]
                );
                $res[ ] = $data;
            }
            if ( $this->input->is_ajax_request() ) {
                $this->output->set_content_type( 'application/json' );;
                echo json_encode( $res );
                exit();
            }
        }
    }

    function get_limit () {
        return array ( 'start' => intval( $this->uri->segment( 4 ) ), 'perpage' => $this->settings[ 'perpage' ] );
    }

    function check_details () {
        if ( !$this->input->post( 'title' ) ) {
            $this->error = $this->lang->line( 'empty_fields' );
            return FALSE;
        }
        return TRUE;
    }

    function out ( $content = NULL, $pagination = NULL, $type = 1 ) {
        $this->builder->output( array ( 'content' => $content, 'path' => $this->path, 'pagination' => $pagination,
                                        'error'   => $this->error, 'message' => $this->message ), $type );
    }

    function set_group () {
        $uid = ( $this->session->userdata( 'uid' ) ) ? $this->session->userdata( 'uid' ) : $this->session->userdata( 'client_uid' );
        $this->uid = $uid;
        $this->group = $this->groups_model->get_group_by_user( $uid );
    }

    function available_filters () {
        if ( $this->group[ 'is_admin' ] ) {
            $clients = $this->users_model->get_clients_list();
        } else
            $clients = $this->users_model->get_clients_list( $this->uid );

        $owner_options = array ();
        foreach ( $clients as $client ) {
            $owner_options[ ] = array (
                'value' => $client[ 'id' ],
                'label' => $client[ 'fname' ] . ' ' . $client[ 'lname' ]
            );
        }


        $filters = array (
            'ctime_from'    => array (
                'field' => 'lb.ctime',
                'from'  => TRUE
            ),
            'ctime_to'      => array (
                'field' => 'lb.ctime',
                'to'    => TRUE
            ),
            'mtime_from'    => array (
                'field' => 'lb.mtime',
                'from'  => TRUE
            ),
            'mtime_to'      => array (
                'field' => 'lb.mtime',
                'to'    => TRUE
            ),
            'clipbin_words' => array (
                'field'    => 'lb.title, lb.description, lb.keywords',
                'label'    => 'Clipbin keywords',
                'fulltext' => TRUE
            ),
            'clip_words'    => array (
                'field'    => 'c.code, cc.title, cc.description, cc.keywords',
                'label'    => 'Clip keywords',
                'fulltext' => TRUE
            ),
            'category'      => array (
                'field' => 'lb.category',
                'label' => 'Category'
            ),
            'owner'         => array (
                'field'      => 'u.id',
                'label'      => 'Owner',
                'options'    => $owner_options,
                'in_sidebar' => 1
            ),
            'ctime'         => array (
                'field'      => 'lb.ctime',
                'label'      => 'Date Created',
                'options'    => array (
                    array (
                        'value' => 'today',
                        'label' => 'Today'
                    ),
                    array (
                        'value' => 'past7days',
                        'label' => 'Past 7 Days'
                    ),
                    array (
                        'value' => 'past30days',
                        'label' => 'Past 30 Days'
                    ),
                    array (
                        'value' => 'past90days',
                        'label' => 'Past 90 Days'
                    )
                ),
                'in_sidebar' => 1
            ),
            'mtime'         => array (
                'field'      => 'lb.mtime',
                'label'      => 'Date Modified',
                'options'    => array (
                    array (
                        'value' => 'today',
                        'label' => 'Today'
                    ),
                    array (
                        'value' => 'past7days',
                        'label' => 'Past 7 Days'
                    ),
                    array (
                        'value' => 'past30days',
                        'label' => 'Past 30 Days'
                    ),
                    array (
                        'value' => 'past90days',
                        'label' => 'Past 90 Days'
                    )
                ),
                'in_sidebar' => 1
            ),
            'categories'    => array (
                'field'      => 'lb.category',
                'label'      => 'Category',
                'options'    => array (
                    array (
                        'value' => 'Client',
                        'label' => 'Client'
                    ),
                    array (
                        'value' => 'Internal',
                        'label' => 'Internal'
                    ),
                    array (
                        'value' => 'Master',
                        'label' => 'Master'
                    )
                ),
                'in_sidebar' => 1
            ),
            'public'        => array (
                'field'      => 'lb.public',
                'label'      => 'Public/Private',
                'options'    => array (
                    array (
                        'value' => 1,
                        'label' => 'Public'
                    ),
                    array (
                        'value' => 0,
                        'label' => 'Private'
                    )
                ),
                'in_sidebar' => 1
            ),
            'display'       => array (
                'field'      => 'lb.display',
                'label'      => 'Display in lists',
                'options'    => array (
                    array (
                        'value' => 1,
                        'label' => 'Display'
                    ),
                    array (
                        'value' => 0,
                        'label' => 'Hidden'
                    )
                ),
                'in_sidebar' => 1
            )
        );
        return $filters;
    }

    function get_selected_filters () {
        $available_filters = $this->available_filters();
        $selected_filters = array ();
        if ( $available_filters ) {
            foreach ( $_REQUEST as $param => $value ) {
                if ( array_key_exists( $param, $available_filters ) ) {
                    if ( is_array( $value ) )
                        $selected_filters[ $param ] = $value;
                    elseif ( $value ) {
                        $value_arr = explode( ';', $value );
                        $selected_filters[ $param ] = $value_arr;
                    }
                }
            }
        }
        return $selected_filters;

    }

    function get_filters () {
        $available_filters = $this->available_filters();
        $selected_filters = $this->get_selected_filters();
        $base_url = 'en/clipbins/view/';

        function implode_param_values ( &$item, $key, $glue = ';' ) {
            if ( $glue == '=' )
                $item = $key . '=' . $item;
            else
                $item = implode( $glue, $item );
        }

        foreach ( $available_filters as $param_name => &$param_settings ) {

            if ( isset( $selected_filters[ $param_name ] ) && is_array( $selected_filters[ $param_name ] ) ) {
                $this->filter[ $param_name ] = $param_settings;
                $this->filter[ $param_name ][ 'selected_values' ] = $selected_filters[ $param_name ];
            }

            if ( isset( $param_settings[ 'options' ] ) ) {
                foreach ( $param_settings[ 'options' ] as &$option ) {
                    if ( isset( $selected_filters[ $param_name ] ) && in_array( $option[ 'value' ], $selected_filters[ $param_name ] ) ) {
                        $option[ 'selected' ] = 1;
                    }

                    $selected_filter_copy = $selected_filters;
                    if ( $option[ 'selected' ] ) {
                        $selected_option_id = array_search( $option[ 'value' ], $selected_filter_copy[ $param_name ] );
                        unset( $selected_filter_copy[ $param_name ][ $selected_option_id ] );
                        if ( !$selected_filter_copy[ $param_name ] )
                            unset( $selected_filter_copy[ $param_name ] );
                    } else
                        $selected_filter_copy[ $param_name ][ ] = $option[ 'value' ];

                    if ( $selected_filter_copy ) {
                        array_walk( $selected_filter_copy, 'implode_param_values' );
                        array_walk( $selected_filter_copy, 'implode_param_values', '=' );
                    }

                    $selected_filter_str = implode( '&', $selected_filter_copy );
                    $option[ 'link' ] = $base_url;
                    if ( $selected_filter_str )
                        $option[ 'link' ] .= '?' . $selected_filter_str;
                }

                if ( isset( $selected_filters[ $param_name ] ) )
                    $param_settings[ 'value_str' ] = implode( ';', $selected_filters[ $param_name ] );

            } elseif ( isset( $selected_filters[ $param_name ] ) && is_array( $selected_filters[ $param_name ] ) ) {
                $param_settings[ 'value' ] = $selected_filters[ $param_name ][ 0 ];
            }
        }

        return $available_filters;
    }

    function prepare_mysql_filter ( $filter ) {
        $mysql_filter = array ();
        foreach ( $filter as $param => $value ) {
            if ( is_array( $value ) ) {
                $field = $value[ 'field' ] ? $value[ 'field' ] : $param;
                if ( $field == 'lb.ctime' || $field == 'lb.mtime' ) {
                    if ( in_array( 'past90days', $value[ 'selected_values' ] ) ) {
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d H:i:s', strtotime( 'today - 90 days' ) ),
                            'from'  => TRUE
                        );
                    } elseif ( in_array( 'past30days', $value[ 'selected_values' ] ) ) {
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d H:i:s', strtotime( 'today - 30 days' ) ),
                            'from'  => TRUE
                        );
                    } elseif ( in_array( 'past7days', $value[ 'selected_values' ] ) ) {
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d H:i:s', strtotime( 'today - 7 days' ) ),
                            'from'  => TRUE
                        );
                    } elseif ( in_array( 'today', $value[ 'selected_values' ] ) ) {
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d' ) . ' 00:00:00',
                            'from'  => TRUE
                        );
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d' ) . ' 23:59:59',
                            'to'    => TRUE
                        );
                    } else
                        $mysql_filter[ ] = array (
                            'field' => $field,
                            'value' => date( 'Y-m-d', strtotime( $value[ 'selected_values' ][ 0 ] ) ),
                            'from'  => $value[ 'from' ] ? TRUE : FALSE,
                            'to'    => $value[ 'to' ] ? TRUE : FALSE
                        );
                } else {
                    $mysql_filter[ ] = array (
                        'field'    => $field,
                        'value'    => $value[ 'fulltext' ] ? $this->prepare_words( $value[ 'selected_values' ][ 0 ] ) : $value[ 'selected_values' ],
                        'fulltext' => $value[ 'fulltext' ] ? TRUE : FALSE
                    );
                }
            } else {
                $mysql_filter[ ] = array (
                    'field' => $param,
                    'value' => $value
                );
            }
        }
        //        echo '<pre>';
        //        print_r($mysql_filter);
        //        exit();
        return $mysql_filter;
    }

    function prepare_words ( $words, $search_mode = 1 ) {
        $words = $this->db->escape( preg_replace( '/ +/', ' ', trim( $words ) ) );

        switch ( $search_mode ) {
            case 1:
                $words = "'+" . str_replace( ' ', ' +', substr( $words, 1 ) );
                break;
            case 2:
                $words = '\'"' . substr( $words, 1, -1 ) . '"\'';
                break;
        }

        return $words;
    }

    //For ajax
    function get_clipbin () {
        $res = array ();
        if ( $this->input->post( 'clipbin_id' ) ) {
            $clipbin = $this->backend_bin_model->get_clipbin( $this->input->post( 'clipbin_id' ) );
            if ( $clipbin ) {
                $res[ 'success' ] = 1;
                $res[ 'clipbin' ] = $clipbin;
                $this->output->set_content_type( 'application/json' );;
                echo json_encode( $res );
                exit();
            }
        }
    }

    // Ajax
    function get_clipbin_items_count () {
        $res = array ();
        if ( ( $clipBinId = $this->input->post( 'clipbin_id' ) ) && $this->input->is_ajax_request() ) {
            $res[ 'success' ] = 1;
            $res[ 'count' ] = $this->backend_bin_model->get_clipbin_items_count( $clipBinId );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        }
        exit();
    }

    // Ajax
    function get_clipbin_well_html () {
        $res = array ();
        if ( ( $clipBinId = $this->input->post( 'clipbin_id' ) ) && $this->input->is_ajax_request() ) {
            $res[ 'success' ] = 1;
            $res[ 'html' ] = $this->load->view(
                'cliplog/clipbins_box/well_clips',
                array (
                    'clips' => $this->backend_bin_model->get_items( $clipBinId )
                ),
                TRUE
            );
            $res[ 'full' ] = (bool) $this->backend_bin_model->get_clipbin_items_count( $clipBinId );
            $this->output->set_content_type( 'application/json' );
            echo json_encode( $res );
            exit();
        }
        exit();
    }

    function get_clipbin_well_html_inner () {
        $clipbinRequest = ClipbinRequest::getInstance();
        # $bin_id = $_SESSION['current_backend_bin_id'];
        $bin_id = $clipbinRequest->getClipbinActive()->getActiveClipbinId();
        $res = $this->load->view(
            'cliplog/clipbins_box/well',
            array (
                'clips' => $this->backend_bin_model->get_items( $bin_id )
            ),
            TRUE
        );
        return $res;
    }

    function get_clipbin_widget () {
        $keyword = $_SESSION[ 'clipbins_filter' ];
        if ( $_SESSION[ 'uid' ] ) {
            $user_login = $_SESSION[ 'login' ];
            $clipbinRequest = ClipbinRequest::getInstance();
            $activeClipbinId = $clipbinRequest->getClipbinActive()->getActiveClipbinId();
            $clipbin_widget = $this->load->view(
                'cliplog/clipbins_box',
                array (
                    # 'selected_bin' => $this->backend_bin_model->get_bin( $_SESSION[ 'current_backend_bin_id' ] ),
                    # 'clips'        => $this->backend_bin_model->get_items( $_SESSION[ 'current_backend_bin_id' ] ),
                    'active_clipbin' => $this->backend_bin_model->get_bin( $activeClipbinId ),
                    'selected_bin' => $this->backend_bin_model->get_bin( $activeClipbinId ),
                    'clips'        => $this->backend_bin_model->get_items( $activeClipbinId ),
                    'bins'         => $this->backend_bin_model->get_no_folder_bins_list( $user_login, $keyword ),
                    'folders'      => $this->backend_bin_model->get_widget_folders_list( $user_login, $keyword ),
                    'lang'         => $this->langs,
                    'is_admin'     => $this->group[ 'is_admin' ] ? TRUE : FALSE
                ),
                TRUE
            );
            return $clipbin_widget;
        }
    }

    function userIsClipbinOwner ( $userID, $binID ) {
        if ( $userID && $binID ) {
            $clipbin = $this->backend_bin_model->get_clipbin( $binID );
            if ( $clipbin[ 'client_id' ] == $userID ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function userIsFolderOwner ( $userID, $folderID ) {
        if ( $userID && $folderID ) {
            $folder = $this->backend_bin_model->get_folder( $folderID );
            if ( $folder[ 'client_id' ] == $userID ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function save_clipbin_ajax () {
        if ( $this->input->post( 'title' ) && $_SESSION[ 'uid' ] ) {
            if ( $this->input->post( 'bin_id' ) ) {
                if ( $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
                    $this->backend_bin_model->update_backend_bin( $this->input->post( 'bin_id' ), $this->input->post( 'title' ), $this->input->post( 'folder_id' ) );
                }
            } else {
                $inserted_clipbin_id = $this->backend_bin_model->save_backend_bin( $_SESSION[ 'uid' ], $this->input->post( 'title' ), $this->input->post( 'folder_id' ) );
                $clipbinRequest = ClipbinRequest::getInstance();
                $clipbinRequest->getClipbinActive()->setActiveClipbinId($inserted_clipbin_id);

            }
            $res = array (
                'success'        => 1,
                'clipbin_widget' => $this->get_clipbin_widget()
            );

            return $res;
        }
    }

    function save_folder_ajax () {
        if ( $this->input->post( 'name' ) && $_SESSION[ 'uid' ] ) {
            if ( $this->input->post( 'folder_id' ) ) {
                $this->backend_bin_model->update_backend_folder( $this->input->post( 'folder_id' ), $this->input->post( 'name' ) );
            } else {
                $this->backend_bin_model->save_backend_folder( $this->input->post( 'name' ), $_SESSION[ 'uid' ] );
            }
            $res = array (
                'success'        => 1,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function get_clipbin_ajax () {
        if ( $this->input->post( 'bin_id' ) ) {
            $clipbin = $this->backend_bin_model->get_clipbin( $this->input->post( 'bin_id' ) );
            if ( $clipbin ) {
                $res = array (
                    'success' => TRUE,
                    'clipbin' => $clipbin
                );
                return $res;
            }
        }
    }

    function delete_clipbin_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->delete_clipbin( $this->input->post( 'bin_id' ) );
        }
        $res = array (
            'success'        => TRUE,
            'clipbin_widget' => $this->get_clipbin_widget()
        );
        return $res;
    }

    function set_default_bin_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->set_default_backend_bin( $this->input->post( 'bin_id' ), $this->session->userdata( 'uid' ) );
            # $_SESSION['current_backend_bin_id'] = $this->input->post('bin_id');
            $clipbinRequest = ClipbinRequest::getInstance();
            $clipbinRequest->getClipbinActive()->setActiveClipbinId(
                (int) $this->input->post( 'bin_id' )
            );
        }
        $res = array (
            'success'        => TRUE,
            'clipbin_widget' => $this->get_clipbin_widget()
        );
        return $res;
    }

    function get_folder_ajax () {
        if ( $this->input->post( 'folder_id' ) ) {
            $folder = $this->backend_bin_model->get_folder( $this->input->post( 'folder_id' ) );
            if ( $folder ) {
                $res = array (
                    'success' => TRUE,
                    'folder'  => $folder
                );
                return $res;
            }
        }
    }

    function delete_folder_ajax () {
        if ( $this->input->post( 'folder_id' ) && $this->userIsFolderOwner( $_SESSION[ 'uid' ], $this->input->post( 'folder_id' ) ) ) {
            $this->backend_bin_model->delete_folder( $this->input->post( 'folder_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function make_gallery_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->make_gallery( $this->input->post( 'bin_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function add_thumb_gallery_ajax(){
        if($this->input->post( 'post_data' ))
            $data=$this->input->post( 'post_data' );
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $updateData = array('preview_clip' => urlencode($data['clip_thumb']));
            $this->backend_bin_model->lib_backend_lb_update( $this->input->post( 'bin_id' ),$updateData );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function make_featured_gallery_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->make_featured_gallery( $this->input->post( 'bin_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function make_ordinary_gallery_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->make_ordinary_gallery( $this->input->post( 'bin_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function make_clipbin_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->make_clipbin( $this->input->post( 'bin_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function make_sequence_ajax () {
        if ( $this->input->post( 'bin_id' ) && $_SESSION[ 'uid' ] && $this->userIsClipbinOwner( $_SESSION[ 'uid' ], $this->input->post( 'bin_id' ) ) ) {
            $this->backend_bin_model->make_sequence( $this->input->post( 'bin_id' ) );
            $res = array (
                'success'        => TRUE,
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function delete_current_bin_items_ajax () {
        if ( $_SESSION[ 'uid' ] && $ids = $this->input->post( 'ids' ) ) {
            if ( is_array( $ids ) ) {
                # $bin_id = $_SESSION['current_backend_bin_id'];
                $clipbinRequest = ClipbinRequest::getInstance();
                $bin_id_del = (!empty($_REQUEST['bin_id']))?(int)$_REQUEST['bin_id']:$clipbinRequest->getClipbinActive()->getActiveClipbinId();
                $bin_id = $clipbinRequest->getClipbinActive()->getActiveClipbinId();
                $this->backend_bin_model->remove_items( $ids, $bin_id_del );
                $res = array (
                    'success'      => TRUE,
                    'items_count'  => $this->backend_bin_model->get_items_count( $bin_id ),
                    'clipbin_type' => $this->backend_bin_model->get_bin_type( $bin_id )
                );
                return $res;
            }
        }
    }

    function add_items_ajax () {
        if ( $_SESSION[ 'login' ] && $this->input->post( 'ids' ) && is_array( $this->input->post( 'ids' ) ) ) {
            $bin_id = $this->get_bin_id();
            $this->backend_bin_model->add_items( $_SESSION[ 'login' ], $this->input->post( 'ids' ), $bin_id );
            $res = array (
                'success'        => TRUE,
                'clipbin_type' => $this->backend_bin_model->get_bin_type( $bin_id ),
                'clipbin_widget' => $this->get_clipbin_widget()
            );
            return $res;
        }
    }

    function get_bin_id()
    {
//         due to bug FSEARCH-1628 by Nikita McKinder,13 Jul 2017 19:24
//        if ( $this->input->post( 'bin_id' ) && is_numeric( $this->input->post( 'bin_id' ) ) )
//            return $this->input->post( 'bin_id' );

        $clipbinRequest = ClipbinRequest::getInstance();
        return $clipbinRequest->getClipbinActive()->getActiveClipbinId();
    }

    function filter_clipbin_ajax () {
        $_SESSION[ 'clipbins_filter' ] = $this->input->post( 'filter' );
        $res = array (
            'success'        => TRUE,
            'clipbin_widget' => $this->get_clipbin_widget()
        );
        return $res;
    }

}