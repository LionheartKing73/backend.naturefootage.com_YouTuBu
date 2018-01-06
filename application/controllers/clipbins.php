<?php

/**
 * Class Clipbins
 *
 * @property clipbins_model $clipbins_model
 * @property bin_model $bin_model
 * @property groups_model $groups_model
 * @property users_model $users_model
 */
class Clipbins extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $group;
    var $uid;
    var $filter;

    function Clipbins () {
        parent::__construct();
        $this->db_master = $this->load->database( 'master', TRUE );
        $this->load->model( 'clipbins_model' );
        $this->load->model( 'bin_model' );
        $this->load->model( 'groups_model' );
        $this->load->model( 'users_model' );
        $this->id = intval( $this->uri->segment( 4 ) );
        $this->langs = $this->uri->segment( 1 );

        $this->settings = $this->api->settings();
        $this->path = 'Clips section / Clipbin Manager';
        $this->set_group();
    }

    function index () {
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
        $all = $this->clipbins_model->get_clipbins_count( $this->filter );
        $data[ 'clipbins' ] = $this->clipbins_model->get_clipbins_list( $this->filter, $limit );
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

        $check = $this->clipbins_model->get_clipbin( $this->id );
        if ( $check[ 'provider_id' ] === $this->uid || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->input->post( 'save' ) && $this->check_details() ) {
                $id = $this->clipbins_model->save_clipbin( $this->id );
                if ( $this->input->is_ajax_request() ) {
                    $res = array ( 'success' => 1 );
                    $this->output->set_content_type( 'application/json' );;
                    echo json_encode( $res );
                    exit();
                } else
                    redirect( $this->langs . '/clipbins/view' );
            }

            $data = $this->input->post();
            if ( !$this->error ) {
                $data = $this->clipbins_model->get_clipbin( $this->id );
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
        $check = $this->clipbins_model->get_clipbin( $this->id );
        if ( $check[ 'provider_id' ] === $this->uid || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->id )
                $ids[ ] = $this->id;
            else
                $ids = $this->input->post( 'id' );

            $this->clipbins_model->delete_clipbins( $ids );
        }
        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        } else
            redirect( $this->langs . '/clipbins/view' );

    }

    function items () {
        if ( $this->input->post( 'items_ids' ) && $this->id ) {
            $this->clipbins_model->add_items( $this->id, $this->input->post( 'items_ids' ) );
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
                //'field'    => 'c.code, cc.title, cc.description, cc.keywords',
                'field'    => 'c.code, c.code as title, c.description, c.keywords',
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

    function editor(){
        $this->path = 'Clips section / Edit Clipbin';
        if ( $this->group[ 'is_admin' ] ) {
            $data[ 'lang' ] = $this->langs;
            $data[ 'login' ] = $this->_validateField($this->input->post('login'));
            $data[ 'clipbin' ] = $this->_validateField($this->input->post('clipbin'),true);
            $data[ 'clip_ids' ] = $this->_validateField($this->input->post('clip_ids'));

            if(!empty($data['login']) && !empty($data[ 'clipbin' ])){
                $clipbin=$this->clipbins_model->get_clipbin_by_name($data['login'],$data['clipbin']);
                $clipbin=(empty($clipbin))?$this->clipbins_model->create_clipbin_by_name($data['login'],$data['clipbin']):$clipbin['id'];
                $itemsStr=$this->clipbins_model->itemsToFilter($data['clip_ids']);
                $idsData=$this->clips_model->get_ids_by_codes($itemsStr);
                $ids=array();
                foreach($idsData as $v){
                    $ids[]=$v['id'];
                }
                $this->clipbins_model->add_items($clipbin,$ids);
                $data['success']='<strong>'.count($ids).'</strong> clips has been added to the clipbin';
                $data['error']='Clips that has not been found: <strong>'.$this->_notExist($data['clip_ids'],$idsData).'</strong>';
            }else{
                if($this->input->post('save'))$data['error']='Please check if fields: USER, CLIPBIN are populated';
            }
            $content = $this->load->view( 'clipbins/editor', $data, TRUE );
            $this->out( $content );
        }
    }

    /**
     * @param $codesStr - str clip_ids field
     * @param $idsArr - array('id','code')
     * @return str - not exist clips
     */
    private function _notExist($codesStr,$idsArr){
        $arr=explode(',',$codesStr);
        $arr = array_flip($arr);

        foreach($idsArr as $v){
            unset($arr[$v['code']]);
        }
        $arr = array_flip($arr);
        return implode(', ',$arr);
    }
    private function _validateField($str,$space=false){
        $pattern=($space)?'/([^A-Za-z0-9, _-])/im':'/([^A-Za-z0-9,_-])/im';
        return (!empty($str))?preg_replace($pattern,'',$str):'';
    }
    //For ajax
    function get_clipbin () {
        $res = array ();
        if ( $this->input->post( 'clipbin_id' ) ) {
            $clipbin = $this->clipbins_model->get_clipbin( $this->input->post( 'clipbin_id' ) );
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
            $res[ 'count' ] = $this->bin_model->get_clipbin_items_count( $clipBinId );
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
                array( 'clips' => $this->bin_model->get_items( $clipBinId ) ),
                TRUE
            );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        }
        exit();
    }

}