<?php

/**
 * @property Clips_model $clips_model
 * @property Search_model $search_model
 * @property Currencies_model $currencies_model
 * @property Groups_model $groups_model
 * @property Formats_model $formats_model
 * @property Deliveryoptions_model $deliveryoptions_model
 * @property Bin_model $bin_m
 * @property Cart_model $cart_m
 * @property Cats_model $cats_model
 * @property Sequences_model $sequences_model
 * @property Bins_model $bins_model
 * @property Galleries_model $galleries_model
 * @property Submissions_model $submissions_model
 * @property Locations_model $locations_model
 * @property Users_model $users_model
 */
class Clips extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    var $message;
    var $currency;
    var $rate;
    var $group;

    function Clips () {
        parent::__construct();

        $this->load->model( 'clips_model' );
        $this->load->model( 'currencies_model' );
        $this->load->model( 'groups_model' );
        $this->load->model( 'formats_model' );
        $this->load->model( 'deliveryoptions_model' );
        $this->load->model( 'users_model' );

        $this->api->save_sort_order( 'clips' );

        $this->id = $this->uri->segment( 4 );
        $this->langs = $this->uri->segment( 1 );
        $this->settings = $this->api->settings();

        if ( isset( $_POST[ 'apply_filters' ] ) ) {
            $this->save_filter_data();
        }

        $this->set_params();
        $this->set_group();
    }

    #------------------------------------------------------------------------------------------------

    function get_id () {
        $words = $this->uri->segment( 3 );
        if ( !$words ) {
            return 0;
        }
        $words = explode( '-', $words );
        return intval( $words[ count( $words ) - 1 ] );
    }

    #------------------------------------------------------------------------------------------------

    function set_params () {
        $currency = $this->session->userdata( 'currency' );

        if ( !$currency[ 'code' ] || !$currency[ 'rate' ] ) {
            $data = $this->currencies_model->get_default();
            $sd[ 'currency' ][ 'code' ] = $this->currency = $data[ 'code' ];
            $sd[ 'currency' ][ 'rate' ] = $this->rate = $data[ 'rate' ];
            $this->session->set_userdata( $sd );
        } else {
            $this->currency = $currency[ 'code' ];
            $this->rate = $currency[ 'rate' ];
        }
    }

    #------------------------------------------------------------------------------------------------

    function index () {
        $this->id = $this->get_id();

        $data[ 'preview' ] = $this->langs . '/clips/content/' . $this->id;
        $data[ 'continue' ] = $this->session->userdata( 'search_page' );
        $data[ 'uri' ] = $this->uri->uri_string();

        //if($this->session->userdata('client_uid') || $this->clips_model->get_clip_public_category($this->id, $this->langs)){

        $data[ 'clip' ] = $this->clips_model->get_clip_info( $this->id, $this->langs );

        if ( $data[ 'clip' ] ) {

            $this->load->model( 'bin_model', 'bin_m' );
            $this->load->model( 'cart_model', 'cart_m' );

            if ( $this->cart_m->check_exist( 2, $data[ 'clip' ][ 'id' ] ) ) {
                $data[ 'clip' ][ 'in_cart' ] = TRUE;
            }
            if ( $this->bin_m->check_exist( 2, $data[ 'clip' ][ 'id' ] ) ) {
                $data[ 'clip' ][ 'in_bin' ] = TRUE;
            }

            /*$data['resolutions'] = $this->formats_model->get_delivery_formats($data['clip']['of_id']);
            $data['resolution_price'] = $data['resolutions'];

            $currency = $this->currencies_model->get_current_currency();
            $data['currency'] = $currency['code'];

            $coef = $currency['rate'];
            foreach($data['resolution_price'] as &$res_price) {
                $res_price['price'] *= $coef;
            }

            if ($coef != 1) {
                foreach ($data['resolutions'] as &$resolution) {
                    $resolution['price'] *= $coef;
                }
            }*/

            $this->clips_model->update_clip_statistic( $this->id );

        }

        /*}
        else{
            $data['clip']['private_clip'] = 1;
            $session_data['after_login_redirect'] = $this->langs . '/clips/' . $this->uri->segment(3);

            $this->session->set_userdata($session_data);
        }*/

        $category = $this->clips_model->get_clip_category( $this->id, $this->langs );
        if ( $category ) {
            $data[ 'clip' ][ 'category' ] = $category;
            $category = ', ' . $category;
        }

        $data[ 'lang' ] = $this->langs;

        if ( $data[ 'clip' ][ 'title' ] ) {
            $content[ 'meta_title' ] = $this->api->get_video_clip() . ' - ' . $data[ 'clip' ][ 'title' ] . ' video' . $category;
        } else {
            //$content['meta_title'] = $this->lang->line('clip_not_found');
            $content[ 'meta_title' ] = '';
        }
        $content[ 'meta_desc' ] = $this->api->get_video_clip() . ' - ' . $category . ', '
            . $data[ 'clip' ][ 'title' ] . ': ' . $data[ 'clip' ][ 'description' ];
        $content[ 'meta_keys' ] = $data[ 'clip' ][ 'meta_keys' ];

        $content[ 'add_css' ] = array (
            '/data/css/com.be.video.editor.Editor/Editor.css',
            '/data/css/jScrollPane/jquery.jscrollpane.css',
            '/data/css/preview.css'
        );
        $content[ 'add_js' ] = array (
            //'/data/js/shortmsg.js',
            '/data/js/jScrollPane/jquery.mousewheel.js',
            '/data/js/jScrollPane/jquery.jscrollpane.min.js',
            '/data/js/preview.js',
            '/data/js/com.be.video.editor.Editor/com.be.video.editor.Editor.nocache.js',
            '/data/js/com.be.video.editor.Editor/editor.js',
            '/data/js/com.be.video.editor.Editor/modernizr.video.js',
            '/data/js/com.be.video.editor.Editor/swfobject.js'
        );

        $user_group_query = $this->db->query("SELECT group_id FROM users WHERE id = '".$_SESSION['user_id']."' LIMIt 1 ")->result_array();
        $user_group_id = $user_group_query[0]['group_id'];

        if($user_group_id){
            $data['is_admin'] = 1;
        }else{
            $data['is_admin'] = 0;
        }


        $content[ 'body' ] = $this->load->view( 'clips/content', $data, TRUE );

        $this->out( $content, 0, 0 );
    }

    #------------------------------------------------------------------------------------------------

    function view () {
        $this->path = 'Clips section / Clips';

        $this->load->model( 'locations_model' );
        $this->load->model( 'footage_types_model' );
        $this->load->model( 'cats_model' );
        $this->load->model( 'sequences_model' );
        $this->load->model( 'bins_model' );
        $this->load->model( 'galleries_model' );
        $this->load->model( 'submissions_model' );
        $this->load->model( 'users_model' );

        $filter = $this->get_filter();
        $order = $this->api->get_sort_order( 'clips' );
        if ( empty( $order ) ) {
            $order = ' ORDER BY c.id DESC ';
        }

        $limit = $this->get_limit();
        $all = $this->clips_model->get_clips_count( $this->langs, $filter );

        $data[ 'filter' ] = $filter;
        //$data['footage_types'] = $this->footage_types_model->get_list($this->langs);
        $data[ 'categories' ] = $this->cats_model->get_cats_list( $this->langs );
        $data[ 'sequences' ] = $this->sequences_model->get_sequences_list();
        $data[ 'bins' ] = $this->bins_model->get_bins_list();
        $data[ 'galleries' ] = $this->galleries_model->get_galleries_list();
        $data[ 'submissions' ] = $this->submissions_model->get_submissions_list();
        //$data['locations'] = $this->locations_model->get_all($this->langs);
        $data[ 'frame_rates' ] = $this->clips_model->get_frame_rate_list();
        $data[ 'providers' ] = $this->users_model->get_providers_list();

        $data[ 'clips' ] = $this->clips_model->get_clips_list( $this->langs, $filter, $order, $limit );
        $data[ 'uri' ] = $this->api->prepare_uri();
        $data[ 'filter' ] = $this->session->userdata( 'filter_clips' );
        $data[ 'lang' ] = $this->langs;
        $data[ 'default_img' ] = $this->config->item( 'image_path' ) . 'no_image.gif';

        if ( $this->group[ 'is_admin' ] ) {
            $data[ 'is_admin' ] = TRUE;
        }
        $data[ 'is_editor' ] = $this->group[ 'is_editor' ];

        $data[ 'paging' ] = $this->api->get_pagination( 'clips/view', $all, $this->settings[ 'perpage' ] );

        $this->session->set_userdata( 'clips_view_uri', $this->uri->uri_string() );

        $this->set_content( 'clips/view', $data, 'Clips :: Editor account' );
    }

    #------------------------------------------------------------------------------------------------

    function derived () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            $this->path = 'Clips section / Clips / Derived';

            $data = array ();
            $filter = array (
                'parent' => (int) $this->id
            );
            $order = ' ORDER BY c.id DESC ';
            $all = $this->clips_model->get_clips_count( $this->langs, $filter );
            $data[ 'clips' ] = $this->clips_model->get_clips_list( $this->langs, $filter, $order );

            $data[ 'id' ] = ( $this->id ) ? $this->id : '';

            $this->set_content( 'clips/derived', $data );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    #------------------------------------------------------------------------------------------------

    function visible () {
        $this->clips_model->change_visible( $this->input->post( 'id' ) );
        $this->api->log( 'log_clip_visible', $this->input->post( 'id' ) );
        if ( strpos( $_SERVER[ 'HTTP_REFERER' ], 'cliplog' ) === FALSE )
            redirect( $this->_get_view_uri() );
        else
            redirect( $_SERVER[ 'HTTP_REFERER' ] );
    }

    function status () {
        $visible_status = $this->uri->segment( 4 );

        $this->clips_model->set_visible( $visible_status, $this->input->post( 'id' ) );
        if ( strpos( $_SERVER[ 'HTTP_REFERER' ], 'cliplog' ) === FALSE )
            redirect( $this->_get_view_uri() );
        else
            redirect( $_SERVER[ 'HTTP_REFERER' ] );
    }

    #------------------------------------------------------------------------------------------------

    function delete () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( !$this->id || ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
                || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
                || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ] )
        ) {

            if ( $this->id ) {
                $ids[ ] = $this->id;
            } else {
                $ids = $this->input->post( 'id' );
            }

            $this->clips_model->delete_clips( $ids, $this->langs );
            $this->api->log( 'log_clip_delete', $ids );
        }

        if ( strpos( $_SERVER[ 'HTTP_REFERER' ], 'cliplog' ) === FALSE )
            redirect( $this->_get_view_uri() );
        else
            redirect( $_SERVER[ 'HTTP_REFERER' ] );
    }

    #------------------------------------------------------------------------------------------------

    function content () {

        // this is because I CAN NOT put it in separate action because of permissions logic
        if (!empty($_REQUEST['no_direct_output']) && !empty($_REQUEST['download_nonhd_video'])) {
            return $this->previewForNonHdDownload();
        }
		
		// this is because I CAN NOT put it in separate action because of permissions logic
        if (!empty($_REQUEST['no_direct_output']) && !empty($_REQUEST['download_hd_video'])) {
            return $this->previewForHdDownload();
        }

        show_404();
    }

    function download()
    {
        $content = $this->clips_model->get_preview_content($this->id, 'hdpreview');

        if(!isset($content['preview'])){
            echo "<script>window.close();</script>";
            die();
        }

        $filename = (isset($content['filename'])) ? $content['filename'] : '';
        $signedUrl = $this->getDownloadUrlFromS3($content['preview'], $filename);

        header('Location: '.$signedUrl);
        die();
    }

    private function getDownloadUrlFromS3($path, $fileName = '')
    {
        $this->load->model('aws_model');

        $fileName=($fileName) ? $fileName : basename($path);

        $s3PathArray = parse_url($path);
        $key = trim($s3PathArray['path'],'\/');

        return $this->aws_model->get_presigned_url(
            $key,
            $fileName
        );
    }

    private function previewForNonHdDownload()
    {
        $isHd = false;
        return $this->clips_model->get_preview_download($this->id, $isHd);
    }
	
	private function previewForHdDownload()
    {
        $isHd = true;
        return $this->clips_model->get_preview_download($this->id, $isHd);
    }


    #------------------------------------------------------------------------------------------------

    function _get_view_uri () {
        $view_uri = str_replace( '.html', '', $this->session->userdata( 'clips_view_uri' ) );
        if ( !$view_uri ) {
            $view_uri = $this->langs . '/clips/view';
        }
        return $view_uri;
    }

    #------------------------------------------------------------------------------------------------

    function edit () {
        //$this->output->enable_profiler();

        $this->load->model( 'users_model' );
        $this->load->model( 'locations_model' );
        $this->load->model( 'footage_types_model' );

        $this->path = $this->id ? 'Clips section / Clips / Edit clip' : 'Clips section / Clips / Add clip';

        $view_uri = $this->_get_view_uri();

        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' ) || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
            || !$this->id
        ) {

            if ( $this->input->post( 'save' ) && $this->check_details() ) {
                $id = $this->clips_model->save_clip( $this->id, $this->langs );

                if ( $this->id ) {
                    $this->api->log( 'log_clip_edit', $this->id );
                    //$this->message = 'Saved.';
                } else {
                    $this->api->log( 'log_clip_new' );
                    redirect( $this->langs . '/clips/edit/' . $id );
                }
                redirect( $view_uri );
            }

            $data = ( $this->error ) ? $_POST : $this->clips_model->get_clip_for_edit( $this->id, $this->langs );

            if ( $this->group[ 'is_admin' ] ) {
                $data[ 'providers' ] = $this->users_model->get_providers_list();
                $data[ 'is_admin' ] = TRUE;
                $sets_filter = '';
            } elseif ( $this->group[ 'is_editor' ] ) {
                $data[ 'is_editor' ] = TRUE;
                $data[ 'editor_id' ] = $this->session->userdata( 'client_uid' );
                $data[ 'error' ] = $this->error;
                $sets_filter = ' AND s.client_id = ' . $data[ 'editor_id' ];
            }

            $data[ 'locations' ] = $this->locations_model->get_all( $this->langs );
            //$data['footage_types'] = $this->footage_types_model->get_list($this->langs);
            $data[ 'of' ] = $this->formats_model->get_formats_list( 3 );
            $data[ 'id' ] = ( $this->id ) ? $this->id : '';
            $data[ 'lang' ] = $this->langs;

            if ( $this->id ) {

                $clip_add_collections = $this->clips_model->get_clip_add_collections( $this->id );
                $clip_add_collections_names = array ();
                foreach ( $clip_add_collections as $clip_add_collection ) {
                    $clip_add_collections_names[ ] = $clip_add_collection[ 'name' ];
                }
                $data[ 'add_collections' ] = implode( ', ', $clip_add_collections_names );

                $preview = $this->clips_model->get_clip_res( $this->id, 1 );
                if ( $preview ) {
                    $data[ 'preview' ] = $preview[ 0 ];
                }

                $delivery_category = $this->deliveryoptions_model->get_delivery_category_by_code( $data[ 'pricing_category' ] );
                if ( $delivery_category )
                    $data[ 'pricing_category' ] = $delivery_category[ 'description' ] . '(' . $delivery_category[ 'id' ] . ')';
            }

            if ( ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Chrome' ) !== FALSE ) ||
                ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Firefox' ) !== FALSE )
            ) {
                $data[ 'runtimes' ] = 'html5';
            } else {
                $data[ 'runtimes' ] = 'flash';
            }

            $this->set_content( 'clips/edit', $data, 'Edit :: Clips :: Editor account' );
        } else {
            redirect( $view_uri );
        }
    }

    #------------------------------------------------------------------------------------------------

    function resources () {
        /*if ($this->input->post('assign')) {
            if (in_array(strtolower($this->input->post('type')), array('mov', 'mp4', 'avi', 'wmv'))) {
                $this->clips_model->set_clip_res($this->id, $this->input->post('type'));
            } else {
                $this->error = 'Legal types for delivery file: mov, mp4, avi, wmv';
            }
        }
        if ($this->input->post('assign_img')) {
            if (in_array($this->input->post('img_type'), array('jpg', 'jpeg', 'png', 'gif'))) {
                $this->clips_model->set_clip_res($this->id, $this->input->post('img_type'), 0);
            } else {
                $this->error = 'Legal types for image thumbnail: jpg, jpeg, png, gif';
            }
        }
        elseif ($this->input->post('upload')) {
            $error = $this->clips_model->upload_resource($this->id, 'thumb');
            if ($error) {
                $this->error = $error;
            }
        }*/

        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            session_start();
            $_SESSION[ 'KCFINDER' ] = array ();
            $_SESSION[ 'KCFINDER' ][ 'disabled' ] = FALSE;

            if ( $this->input->post( 'create' ) ) {
                $k = array_keys( $this->input->post( 'create' ) );
                if ( $k[ 0 ] == 'img' ) {
                    $error = $this->clips_model->create_thumb( $this->id );
                } else {
                    $error = $this->clips_model->create_resource( $this->id, $k[ 0 ] );
                }
                if ( $error ) {
                    $this->error = $error;
                }
            } elseif ( $this->input->post( 'delete' ) ) {
                $k = array_keys( $this->input->post( 'delete' ) );
                $this->clips_model->unreg_resource( $this->id, $k[ 0 ] );
            } elseif ( $this->input->post( 'save_location' ) && $this->input->post( 'location' ) ) {
                $k = array_keys( $this->input->post( 'save_location' ) );
                $error = $this->clips_model->specify_resource_location( $this->id, $k[ 0 ], $this->input->post( 'location' ) );
                if ( $error ) {
                    $this->error = $error;
                }
            }

            $this->path = 'Clips section / Clips / Edit clip / Resources';
            $data[ 'lang' ] = $this->langs;
            $data[ 'id' ] = ( $this->id ) ? $this->id : '';
            if ( $this->id ) {
                $data[ 'code' ] = $this->clips_model->get_clip_code( $this->id );
            }
            $data[ 'resources' ] = $this->clips_model->get_resources( $this->id );

            $this->set_content( 'clips/resources', $data, 'Resources :: Clips :: Editor account' );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    function create_temp_thumb () {
        $result = $this->clips_model->create_temp_thumb( $this->id, $this->input->post( 'offset' ) );
        if ( $result )
            $file = $this->config->item( 'clip_path' ) . 'temp_thumb/' . $this->id . '.jpg';
        else
            $file = FALSE;

        if ( $this->input->is_ajax_request() ) {
            if ( $file && file_exists( $file ) )
                $res = array ( 'success' => 1, 'file' => $file . '?v=' . date( 'YmdHis' ) );
            else
                $res = array ( 'success' => 0 );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        }
    }

    function set_temp_thumb () {
        $this->clips_model->set_temp_thumb( $this->id );
        if ( $this->input->is_ajax_request() ) {
            $res = array ( 'success' => 1 );
            $this->output->set_content_type( 'application/json' );;
            echo json_encode( $res );
            exit();
        }
    }

    #------------------------------------------------------------------------------------------------

    function attachments () {
        /*if ($this->input->post('assign')) {
            if (in_array(strtolower($this->input->post('type')), array('mov', 'mp4', 'avi', 'wmv'))) {
                $this->clips_model->set_clip_res($this->id, $this->input->post('type'));
            } else {
                $this->error = 'Legal types for delivery file: mov, mp4, avi, wmv';
            }
        }
        if ($this->input->post('assign_img')) {
            if (in_array($this->input->post('img_type'), array('jpg', 'jpeg', 'png', 'gif'))) {
                $this->clips_model->set_clip_res($this->id, $this->input->post('img_type'), 0);
            } else {
                $this->error = 'Legal types for image thumbnail: jpg, jpeg, png, gif';
            }
        }
        elseif ($this->input->post('upload')) {
            $error = $this->clips_model->upload_resource($this->id, 'thumb');
            if ($error) {
                $this->error = $error;
            }
        }*/

        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'upload' ) ) {
                $error = $this->clips_model->upload_attachment( $this->id );
                if ( $error ) {
                    $this->error = $error;
                }
            } elseif ( $this->input->post( 'delete' ) ) {
                $k = array_keys( $this->input->post( 'delete' ) );
                $this->clips_model->delete_attachment( $k[ 0 ] );
            }

            $this->path = 'Clips section / Clips / Edit clip / Attachments';
            $data[ 'lang' ] = $this->langs;
            $data[ 'id' ] = ( $this->id ) ? $this->id : '';
            if ( $this->id ) {
                $data[ 'code' ] = $this->clips_model->get_clip_code( $this->id );
            }
            $data[ 'attachments' ] = $this->clips_model->get_attachments( $this->id );

            $this->set_content( 'clips/attachments', $data, 'Resources :: Clips :: Editor account' );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    #-----------------------------------------------------------------------------

    function files () {
        $this->path = 'Clips section / Files';
        $data[ 'lang' ] = $this->langs;

        $data[ 'clips_count' ] = $this->clips_model->get_clips_count( $this->langs );
        $res_types = array ( 'hd', 'img', 'thumb', 'preview' );
        foreach ( $res_types as $res_type ) {
            $data[ $res_type . '_count' ] = $this->clips_model->get_resources_count( $res_type );
        }

        $data[ 'res_to_create' ] = $this->clips_model->res_to_create();

        $this->set_content( 'clips/files', $data, 'Files :: Clips :: Editor account' );
    }

    #-----------------------------------------------------------------------------

    function upload_old () {
        $this->load->model( 'submissions_model' );
        $this->path = 'Clips section / Upload';
        $data[ 'lang' ] = $this->langs;
        $submission_code = $this->submissions_model->get_last_submission_code();
        $data[ 'submission_code' ] = $submission_code ? $submission_code : 0;
        $user_id = $data[ 'user_id' ] = $this->session->userdata( 'client_uid' ) ? $this->session->userdata( 'client_uid' ) : 0;

        if ( ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Chrome' ) !== FALSE ) ||
            ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Firefox' ) !== FALSE )
        ) {
            $data[ 'runtimes' ] = 'html5';
        } else {
            $data[ 'runtimes' ] = 'flash';
        }

        $providers_group_id = $this->groups_model->get_provider_group_id();
        $this->load->model( 'users_model' );

        // For filecatalyst
        //        if($user_id){
        //            $user = $this->users_model->get_user($user_id);
        //            if($user && $user['group_id'] == $providers_group_id){
        //                $data['is_provider'] = true;
        //                $data['provider_login'] = $user['login'];
        //                $data['provider_password'] = $user['password'];
        //                $this->config->load('file_catalyst', TRUE);
        //                $fc_config = $this->config->item('file_catalyst');
        //                $data['fc_server'] = $fc_config['server'];
        //                $data['fc_port'] = $fc_config['port'];
        //            }
        //        }

        if ( $user_id ) {
            $user = $this->users_model->get_user( $user_id );
            if ( $user && $user[ 'group_id' ] == $providers_group_id ) {
                $data[ 'is_provider' ] = TRUE;
                $data[ 'provider_login' ] = $user[ 'login' ];
                $data[ 'provider_password' ] = $user[ 'password' ];
                //$this->config->load('aspera', TRUE);
                //$aspera_config = $this->config->item('aspera');
                //$data['aspera_connect_server'] = $aspera_config['connect_server'];
            }
        }

        $this->set_content( 'clips/upload', $data );
    }
    function upload () {
        $this->load->model('submissions_model');
        $this->path = 'Clips section / Upload';
        $data['lang'] = $this->langs;
        $submission_code = $this->submissions_model->get_last_submission_code();
        $data['submission_code'] = $submission_code ? $submission_code : 0;
        $user_id = $data['user_id'] = $this->session->userdata('client_uid') ? $this->session->userdata('client_uid') : 0;
        $providers_group_id = $this->groups_model->get_provider_group_id();
        $this->load->model('users_model');
        if ($user_id) {
            $user = $this->users_model->get_user($user_id);
            if ($user && $user['group_id'] == $providers_group_id) {
                $data['is_provider'] = true;
                $this->config->load('aspera', true);
                $data['aspera_config'] = $this->config->item('aspera');
                $data['home_path'] = '/' . $user['login'];
            }
        }
        $this->set_content('clips/upload', $data);
    }

    function node_api () {

        if ( $this->input->post( 'path' ) ) {
            $providers_group_id = $this->groups_model->get_provider_group_id();
            $this->load->model( 'users_model' );
            $user_id = $this->session->userdata( 'client_uid' ) ? $this->session->userdata( 'client_uid' ) : 0;
            if ( $user_id ) {
                $user = $this->users_model->get_user( $user_id );
                if ( $user && $user[ 'group_id' ] == $providers_group_id ) {
                    $this->config->load( 'aspera', TRUE );
                    $aspera_config = $this->config->item( 'aspera' );
                    $node_api_host = $aspera_config[ 'node_api_host' ];
                    $node_api_port = $aspera_config[ 'node_api_port' ];
                    $provider_login = $aspera_config[ 'node_api_provider_user' ];
                    $provider_password = $aspera_config[ 'node_api_provider_password' ];
                    $params = json_decode($this->input->post('params'), true);
                    if(isset($params['path']) && !preg_match('/^\/' . $user['login'] . '.*/', $params['path'])){
                        $params['path'] = '/' . $user['login'] . $params['path'];
                    }
                    $params = json_encode($params);
                }
            }

            if ( isset( $provider_login ) && isset( $provider_password ) && isset( $node_api_host ) ) {
                $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://' . $node_api_host . ':'
                    . ( isset( $node_api_port ) ? $node_api_port : '9092' ) . $this->input->post( 'path' ) );
                curl_setopt( $ch, CURLOPT_USERPWD, $provider_login . ":" . $provider_password );
                curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_USERAGENT, $agent );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
                curl_setopt( $ch, CURLOPT_POST, TRUE );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
                $result = curl_exec( $ch );
                $info = curl_getinfo( $ch );
                curl_close( $ch );
                if ( $info[ 'http_code' ] == 200 )
                    echo $result;
                else
                    $this->output->set_status_header( $info[ 'http_code' ] );
            }
        } else
            $this->output->set_status_header( 400 );

        exit();
    }

    #-----------------------------------------------------------------------------

    function cats () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'save' ) ) {
                $this->clips_model->save_cats( $this->id, $this->input->post( 'id' ) );
                //$this->message = 'Saved.';
                redirect( $this->_get_view_uri() );
            }

            $this->path = 'Clips section / Clips / Clip Categories';

            $data = $this->clips_model->get_cats_clip( $this->id, $this->langs );
            $data[ 'column_count' ] = ceil( $data[ 'total' ] / 5 );
            $data[ 'id' ] = $this->id;
            $data[ 'lang' ] = $this->langs;

            $this->set_content( 'clips/cats', $data, 'Cats :: Clips :: Editor account' );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    function sequences () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'save' ) ) {
                $this->clips_model->save_sequences( $this->id, $this->input->post( 'id' ) );
                redirect( $this->_get_view_uri() );
            }

            $this->path = 'Clips section / Clips / Clip Sequences';

            $data[ 'sequences' ] = $this->clips_model->get_clip_sequences( $this->id, $this->langs );
            $data[ 'id' ] = $this->id;
            $data[ 'lang' ] = $this->langs;

            $this->set_content( 'clips/sequences', $data );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    function bins () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'save' ) ) {
                $this->clips_model->save_bins( $this->id, $this->input->post( 'id' ) );
                redirect( $this->_get_view_uri() );
            }

            $this->path = 'Clips section / Clips / Clip Bins';

            $data[ 'bins' ] = $this->clips_model->get_clip_bins( $this->id, $this->langs );
            $data[ 'id' ] = $this->id;
            $data[ 'lang' ] = $this->langs;

            $this->set_content( 'clips/bins', $data );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    function galleries () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'save' ) ) {
                $this->clips_model->save_galleries( $this->id, $this->input->post( 'id' ) );
                redirect( $this->_get_view_uri() );
            }

            $this->path = 'Clips section / Clips / Clip Galleries';

            $data[ 'galleries' ] = $this->clips_model->get_clip_galleries( $this->id, $this->langs );
            $data[ 'id' ] = $this->id;
            $data[ 'lang' ] = $this->langs;

            $this->set_content( 'clips/galleries', $data );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    function clipbins () {
        $check_clip = $this->clips_model->get_clip( $this->id );
        if ( $check_clip[ 'client_id' ] === $this->session->userdata( 'client_uid' )
            || $check_clip[ 'client_id' ] === $this->session->userdata( 'uid' )
            || $this->group[ 'is_admin' ] || $this->group[ 'is_beditor' ]
        ) {

            if ( $this->input->post( 'save' ) ) {
                $this->clips_model->save_clipbins( $this->id, $this->input->post( 'id' ) );
                redirect( $this->_get_view_uri() );
            }

            $this->path = 'Clips section / Clips / Clip Clipbins';

            $data[ 'clipbins' ] = $this->clips_model->get_clip_clipbins( $this->id, $this->langs );
            $data[ 'id' ] = $this->id;
            $data[ 'lang' ] = $this->langs;

            $this->set_content( 'clips/clipbins', $data );
        } else {
            redirect( $this->_get_view_uri() );
        }
    }

    #------------------------------------------------------------------------------------------------

    function CheckClipStatisticsAccess ( $clip ) {
        if ( $this->group[ 'is_admin' ] ) {
            return TRUE;
        } elseif ( $this->group[ 'is_beditor' ] ) {
            return TRUE;
        } elseif ( $clip[ 'client_id' ] === $this->session->userdata( 'client_uid' ) ) {
            return TRUE;
        } elseif ( $clip[ 'client_id' ] === $this->session->userdata( 'uid' ) ) {
            return TRUE;
        }
        return FALSE;
    }

    function GetStatisticAccessType () {
        //var_export(  $this->group );
        if ( $this->group[ 'is_admin' ] ) {
            return 1;
        }
        if ( $this->group[ 'is_editor' ] ) {
            return 2;
        }
        return FALSE;
    }

    function PrepareStatisticsTemplateData () {
        $this->path = 'Clips section / Clips / Clip Statistics';
        $data[ 'lang' ] = $this->langs;
        $data[ 'id' ] = ( (int) $this->id ) ? $this->id : 0;
        $data[ 'filter' ] = $this->session->userdata( 'filter_statistics' );
        $data[ 'user' ] = $this->input->post( 'user' );
        return $data;
    }

    function CreateStatisticsFilter ( $is_provider = FALSE ) {
        $clip_id = (int) $this->id;
        $filter = $this->input->post( 'userfilter', TRUE );
        $login = $this->input->post( 'user', TRUE );
        $this->session->set_userdata( 'statfilterlogin', $login );
        if(!empty($login)) {
            $login = $this->users_model->get_user_by_login($login);
        }
        $session_name = $this->clips_model->GetSessionStatisticFilterName();
        $pieces = array ();
        if ( $is_provider ) {
            $pieces[ ] = "stat.provider_id = " . (int) $this->session->userdata( 'uid' );
            //$pieces[ ] = "stat.user_login = '" . $this->session->userdata('login') . "'";
        }
        if ( $clip_id ) {
            // Статиcтика по конкретному клипу
            $pieces[ ] = "stat.clip_id = '{$clip_id}'";
        }
        // Action type
        if($_REQUEST['action_type']=='all'){
            unset($_REQUEST['action_type']);
            unset($_SESSION['stat_action_type']);
        }
        if(!empty($_REQUEST['action_type'])){
            $pieces[ ] = "actions.type =".$_REQUEST['action_type'];
            $_SESSION['stat_action_type']=$_REQUEST['action_type'];
        }elseif(!empty($_SESSION['stat_action_type'])){
            $pieces[ ] = "actions.type =".$_SESSION['stat_action_type'];
            $_REQUEST['action_type']=$_SESSION['stat_action_type'];
        }
        // CP don't view Clip Licensed
        if(empty($_REQUEST['action_type']) && isset($_SESSION['uid']) && $_SESSION['group'] !=1) $pieces[ ] = "actions.type IN (1,2)";
        // Date from/to
        if(!empty($_REQUEST['view']) && $_REQUEST['period'] =='all'){
            unset($_REQUEST['datefrom']);
            unset($_REQUEST['dateto']);
            unset($_SESSION['stat_datefrom']);
            unset($_SESSION['stat_dateto']);
        }
        if(!empty($_REQUEST['view']) && !empty($_REQUEST['datefrom']) && !empty($_REQUEST['dateto'])){
            $pieces[ ] = "stat.time BETWEEN STR_TO_DATE('{$_REQUEST['datefrom']} 00:00:00', '%d.%m.%Y %H:%i:%s')
  AND STR_TO_DATE('{$_REQUEST['dateto']} 23:59:59', '%d.%m.%Y %H:%i:%s')";
            $_SESSION['stat_datefrom']=$_REQUEST['datefrom'];
            $_SESSION['stat_dateto']=$_REQUEST['dateto'];
            $_SESSION['stat_period']=$_REQUEST['period'];
        }elseif(!empty($_SESSION['stat_datefrom']) && !empty($_SESSION['stat_dateto'])){
            $pieces[ ] = "stat.time BETWEEN STR_TO_DATE('{$_SESSION['stat_datefrom']} 00:00:00', '%d.%m.%Y %H:%i:%s')
  AND STR_TO_DATE('{$_SESSION['stat_dateto']} 23:59:59', '%d.%m.%Y %H:%i:%s')";
            $_REQUEST['datefrom']=$_SESSION['stat_datefrom'];
            $_REQUEST['dateto']=$_SESSION['stat_dateto'];
            $_REQUEST['period']=$_SESSION['stat_period'];
        }else{
            $pieces[]="stat.time BETWEEN '".date('Y-m-d')." 00:00:00' AND '".date('Y-m-d')." 23:59:59'";
        }
        if ( $filter ) {
            // Задан новый фильтр
            if ( $login ) {
                // Передан логин
                $pieces[ ] = "stat.provider_id = '{$login}'";
                // Сохраняем логин в сессию
                $this->session->set_userdata( $session_name, $login );
            } else {
                // Логин сброшен, очищаем сессию
                $this->session->unset_userdata( $session_name );
            }
        } else {
            // Читаем фильтр с сессии
            $session_login = $this->session->userdata( $session_name );
            if ( $session_login ) {
                $pieces[ ] = "stat.provider_id = '{$session_login}'";
            }
        }
        if ( !empty( $pieces ) ) {
            $query = 'WHERE ';
            $query .= implode( ' AND ', $pieces );
            return $query;
        }
        return NULL;
    }

    function CreateStatisticsFilterNew ( $is_provider = FALSE ) {
        $clip_id = (int) $this->id;
        $filter = $this->input->post( 'userfilter', TRUE );
        $login = $this->input->post( 'user', TRUE );
        $this->session->set_userdata( 'statfilterlogin', $login );
        if(!empty($login)) {
            $login = $this->users_model->get_user_by_login($login);
        }
        $session_name = $this->clips_model->GetSessionStatisticFilterName();
        $pieces = array ();
        if ( $is_provider ) {
            $pieces[ ] = "stat.provider_id = " . (int) $this->session->userdata( 'uid' );
            //$pieces[ ] = "stat.user_login = '" . $this->session->userdata('login') . "'";
        }
        if ( $clip_id ) {
            // Статиcтика по конкретному клипу
            $pieces[ ] = "stat.clip_id = '{$clip_id}'";
        }
        // Action type
        if($_REQUEST['action_type']=='all'){
            unset($_REQUEST['action_type']);
            unset($_SESSION['stat_action_type']);
        }
        if(!empty($_REQUEST['action_type'])){
            $pieces[ ] = "stat.action_type =".$_REQUEST['action_type'];
            $_SESSION['stat_action_type']=$_REQUEST['action_type'];
        }elseif(!empty($_SESSION['stat_action_type'])){
            $pieces[ ] = "stat.action_type =".$_SESSION['stat_action_type'];
            $_REQUEST['action_type']=$_SESSION['stat_action_type'];
        }
        // CP don't view Clip Licensed
        //if(empty($_REQUEST['action_type']) && isset($_SESSION['uid']) && $_SESSION['group'] !=1) $pieces[ ] = "actions.type IN (1,2)";
        // Date from/to
        if(!empty($_REQUEST['view']) && $_REQUEST['period'] =='all'){
            //unset($_REQUEST['datefrom']);
            //unset($_REQUEST['dateto']);
            $_REQUEST['datefrom']='01.01.1900';
            $_REQUEST['dateto']=date('d.m.Y');
            //unset($_SESSION['stat_datefrom']);
            //unset($_SESSION['stat_dateto']);
        }
        if(!empty($_REQUEST['view']) && !empty($_REQUEST['datefrom']) && !empty($_REQUEST['dateto'])){
            $pieces[ ] = "stat.time BETWEEN STR_TO_DATE('{$_REQUEST['datefrom']} 00:00:00', '%d.%m.%Y %H:%i:%s')
  AND STR_TO_DATE('{$_REQUEST['dateto']} 23:59:59', '%d.%m.%Y %H:%i:%s')";
            $_SESSION['stat_datefrom']=$_REQUEST['datefrom'];
            $_SESSION['stat_dateto']=$_REQUEST['dateto'];
            $_SESSION['stat_period']=$_REQUEST['period'];
        }elseif(!empty($_SESSION['stat_datefrom']) && !empty($_SESSION['stat_dateto'])){
            $pieces[ ] = "stat.time BETWEEN STR_TO_DATE('{$_SESSION['stat_datefrom']} 00:00:00', '%d.%m.%Y %H:%i:%s')
  AND STR_TO_DATE('{$_SESSION['stat_dateto']} 23:59:59', '%d.%m.%Y %H:%i:%s')";
            $_REQUEST['datefrom']=$_SESSION['stat_datefrom'];
            $_REQUEST['dateto']=$_SESSION['stat_dateto'];
            $_REQUEST['period']=$_SESSION['stat_period'];
        }else{
            $pieces[]="stat.time BETWEEN '".date('Y-m-d')." 00:00:00' AND '".date('Y-m-d')." 23:59:59'";
        }
        if ( $filter ) {
            // Задан новый фильтр
            if ( $login ) {
                // Передан логин
                $pieces[ ] = "stat.provider_id = '{$login}'";
                // Сохраняем логин в сессию
                $this->session->set_userdata( $session_name, $login );
            } else {
                // Логин сброшен, очищаем сессию
                $this->session->unset_userdata( $session_name );
            }
        } else {
            // Читаем фильтр с сессии
            $session_login = $this->session->userdata( $session_name );
            if ( $session_login ) {
                $pieces[ ] = "stat.provider_id = '{$session_login}'";
            }
        }
        if ( !empty( $pieces ) ) {
            $query = 'WHERE ';
            $query .= implode( ' AND ', $pieces );
            return $query;
        }
        return NULL;
    }

    function CreateStatisticsTopFilter ( $is_provider = FALSE ) {
        $pieces = array ();
        if ( $is_provider ) {
            $pieces[ ] = 'provider_id = ' . (int) $this->session->userdata( 'uid' );
        }


        if ( !empty( $pieces ) ) {
            $query = 'WHERE clip_id <> 0 AND ';
            $query .= implode( ' AND ', $pieces );
            return $query;
        }
        return 'WHERE clip_id <> 0';
    }

    function CreateStatisticsLimit () {
        return $this->CreateStatisticsTopLimit();
    }

    function CreateStatisticsTopLimit () {
        $from = $this->uri->segment( 5 );
        if($from=='user') $from = $this->uri->segment( 7 );
        //$this->settings[ 'perpage' ];
        $_SESSION['stat_perpage']=(isset($_SESSION['stat_perpage']))?$_SESSION['stat_perpage']:50;
        $perpage=(isset($_REQUEST['perpage']))?(int)$_REQUEST['perpage']:$_SESSION['stat_perpage'];
        $_SESSION['stat_perpage']=$perpage;
        if ( (int) $from ) {
            return 'LIMIT ' . $from . ', ' . $perpage;
        } else {
            return 'LIMIT 0, ' . $perpage;
        }
    }

    function CreateStatisticsTopOrder () {
        $default = "ORDER BY clip_id DESC";
        $segment = $this->uri->segment( 2 );
        if ( !$segment ) {
            return $default;
        }
        $sort = $this->session->userdata( 'sort_' . $segment );
        if ( empty( $sort ) && !is_array( $sort ) ) {
            return $default;
        }
        $order = "ORDER BY ";
        foreach ( $sort as $name => $type ) {
            $order .= (string) $name;
            if ( $type == 1 ) {
                $order .= ' ASC';
            } else {
                $order .= ' DESC';
            }
        }
        return $order;
    }

    function statistics () {
        if ( $this->uri->segment( 4 ) == 'raw' ) {
            //$this->_statistics_top();
            $this->_statistics_raw();
        } else {
            $access = $this->GetStatisticAccessType();
            if($access==1){
                $this->_statistics_all_admin();
            }elseif($access==2){
                $this->_statistics_all_cp();
            }
            //$this->_statistics_all();
        }
    }

    function _statistics_top () {
        $access = $this->GetStatisticAccessType();
        //var_export( $access );
        if ( $access === FALSE ) {
            redirect( 'en/login' );
        } elseif ( $access == 1 ) {
            $filter = $this->CreateStatisticsTopFilter();
        } else {
            $filter = $this->CreateStatisticsTopFilter( TRUE );
        }

        $all = $this->clips_model->GetStatisticTopItemsCount( $filter );
        $limit = $this->CreateStatisticsTopLimit();
        $order = $this->CreateStatisticsTopOrder();
        $statistic = $this->clips_model->GetStatisticTopItems( $filter, $order, $limit );
        $array = array ();
        foreach ( $statistic as $key => $clip ) {
            $clip_data = $this->clips_model->get_clip( $clip[ 'clip_id' ] );
            if ( !empty( $clip_data ) ) {
                $array[ $key ] = $clip;
                $array[ $key ][ 'data' ] = $clip_data;
            }
        }
        $statistic = $array;

        $data = $this->PrepareStatisticsTemplateData();
        $data[ 'statistic' ] = $statistic;
        $data[ 'type' ] = $this->uri->segment( 4 );
        $data[ 'url' ] = "/{$this->langs}/clips/statistics/top/";

        $segment = $this->uri->segment( 4 );
        $segment = ( empty( $segment ) ) ? 'top' : $segment;

        $paginator = $this->api->get_pagination( 'clips/statistics/' . $segment, $all, $this->settings[ 'perpage' ] );
        $this->set_content( 'clips/statistics', $data, NULL, $paginator );
    }
    function _statistics_raw () {
        $access = $this->GetStatisticAccessType();
        if ( $access != 1 ) redirect( 'en/login' );

        $filter=$this->CreateStatisticsFilterNew();
        $this->save_statistics_filter();
        $limit = $this->CreateStatisticsLimit();
        $order=$this->orderBy();
        if ( $this->input->post( 'export' ) ) {
            //$this->get_statistics_csv();
            $headers=array('Action','Clip','User','Date');
            $dataKeys=array('action_type','code','user_login','time');
            $statistic=$this->clips_model->GetRawStatistics ( $filter, ' LIMIT 0,9999999', $order );
            $this->get_data_to_csv($headers,$statistic,$dataKeys);
        }
        $all=count($this->clips_model->GetRawStatistics ( $filter, ' LIMIT 0,9999999' ));
        $statistic=$this->clips_model->GetRawStatistics ( $filter, $limit, $order );
        $data = $this->PrepareStatisticsTemplateData();
        $data[ 'statistic' ] = $statistic;
        $data[ 'type' ] = $this->uri->segment( 4 );
        $data[ 'url' ] = "/{$this->langs}/clips/statistics/raw/";
        $data[ 'actions_types' ] = $this->clips_model->getActions_types();
        $data[ 'perpage' ] = $_SESSION['stat_perpage'];

        $paginator = $this->api->get_pagination( 'clips/statistics/raw', $all, $_SESSION['stat_perpage'] );
        $this->set_content( 'clips/statistics_raw', $data, NULL, $paginator );
    }

    function _statistics_all_admin () {
        $filter=$this->CreateStatisticsFilterNew();
        $limit = $this->CreateStatisticsLimit();
        $order=$this->orderBy();
        $urlSfx='';
        if($this->uri->segment( 5 ) == 'user')
            $urlSfx='/'.$this->uri->segment(5).'/'.$this->uri->segment(6);
        if ( $this->input->post( 'export' ) ) {
            if($this->uri->segment( 5 ) == 'user'){
                $headers=array('Date','Clip ID');
                $dataKeys=array('time','code');
            }else{
                $headers=array('Users','Downloads');
                $dataKeys=array('user_login','count');
            }
            //Debug::Dump($order); die();
            $statistic=$this->clips_model->GetAllAdminStatistics ( $filter, ' LIMIT 0,9999999', $order );
            $this->get_data_to_csv($headers,$statistic,$dataKeys);
        }
        $all=count($this->clips_model->GetAllAdminStatistics ( $filter, ' LIMIT 0,9999999' ));
        $statistic=$this->clips_model->GetAllAdminStatistics ( $filter, $limit, $order );
        $data = $this->PrepareStatisticsTemplateData();
        $data[ 'statistic' ] = $statistic;
        $data['all']=$all;
        $data[ 'url' ] = "/{$this->langs}/clips/statistics/all/";
        $data[ 'perpage' ]=$_SESSION['stat_perpage'];
        $data[ 'actions_types' ] = $this->clips_model->getActions_types();
        $paginator = $this->api->get_pagination( 'clips/statistics/all'.$urlSfx, $all, $_SESSION['stat_perpage'] );
        if($this->uri->segment( 5 ) == 'user'){
            $data[ 'access' ]=1;
            $this->set_content( 'clips/statistics_cp', $data, NULL, $paginator );
        }else{
            $this->set_content( 'clips/statistics', $data, NULL, $paginator );
        }
    }

    function _statistics_all_cp(){
        $filter='';$this->CreateStatisticsFilterNew();
        $limit = $this->CreateStatisticsLimit();
        $order=$this->orderBy();
        $urlSfx='';
        if($this->uri->segment( 5 ) == 'user')
            $urlSfx='/'.$this->uri->segment(5).'/'.$this->uri->segment(6);
        if ( $this->input->post( 'export' ) ) {
            if($this->uri->segment( 5 ) == 'user'){
                $headers=array('Date','Clip ID');
                $dataKeys=array('time','code');
            }else{
                $headers=array('Clip ID','Views','Downloads');
                $dataKeys=array('code','viewed','downloaded');
            }
            $statistic=$this->clips_model->GetAllCPStatistics ( $filter, ' LIMIT 0,99999', $order );
            //Debug::Dump([$headers,$statistic,$dataKeys]); die();
            $this->get_data_to_csv($headers,$statistic,$dataKeys);
        }
        $allStat=$this->clips_model->GetAllCPStatistics ( $filter, ' LIMIT 0,9999999','',' , SUM( stat.viewed ) AS allV, SUM( stat.downloaded ) AS allD, COUNT( stat.id ) AS allcount' );
        $all=$allStat[0]['allcount'];
        $statistic=$this->clips_model->GetAllCPStatistics ( $filter, $limit, $order );
        $data = $this->PrepareStatisticsTemplateData();
        $data[ 'statistic' ] = $statistic;
        $data[ 'allStat' ] = $allStat;
        $data[ 'all' ] = $all;
        $data[ 'perpage' ]=$_SESSION['stat_perpage'];
        $data[ 'actions_types' ] = $this->clips_model->getActions_types();
        $paginator = $this->api->get_pagination( 'clips/statistics/all'.$urlSfx, $all, $_SESSION['stat_perpage'] );
        $this->set_content( 'clips/statistics_cp', $data, NULL, $paginator );
    }

    function _statistics_all () {
        $clip_id = ( (int) $this->id ) ? $this->id : 0;
        $clip = $this->clips_model->get_clip( $clip_id );

        /*
        if ( !$this->CheckClipStatisticsAccess( $clip ) ) {
            redirect( $this->_get_view_uri() );
        }
        */

        $access = $this->GetStatisticAccessType();
        //var_export( $access );
        if ( $access === FALSE ) {
            redirect( 'en/login' );
        } elseif ( $access == 1 ) {
            $filter = $this->CreateStatisticsFilter();
        } else {
            $filter = $this->CreateStatisticsFilter( TRUE );
        }

        $this->save_statistics_filter();
        if ( $this->input->post( 'export' ) ) {
            $this->get_statistics_csv();
        }

        $all = $this->clips_model->GetStatisticItems( $filter, 'LIMIT 99999999' );//$this->clips_model->GetStatisticItemsCount( $filter );
        $limit = $this->CreateStatisticsLimit();
        $order=false;
        if(!empty($_SESSION['stat_order']) && !empty($_SESSION['stat_by'])){
            $order=$_SESSION['stat_order'].' '.$_SESSION['stat_by'];
        }
        if(!empty($_REQUEST['order']) && !empty($_REQUEST['by'])){
            $order=$_REQUEST['order'].' '.$_REQUEST['by'];
            $_SESSION['stat_order']=$_REQUEST['order'];
            $_SESSION['stat_by']=$_REQUEST['by'];
        }
        $logs = $this->clips_model->GetStatisticItems( $filter, $limit, $order );

        $data = $this->PrepareStatisticsTemplateData();
        $data[ 'logs' ] = $logs;
        $data[ 'type' ] = $this->uri->segment( 4 );
        $data[ 'user' ] = ($access>1)?$this->session->userdata('login'):$this->session->userdata( 'statfilterlogin' );
        $data[ 'access'] = $access;
        $data[ 'perpage' ]=$_SESSION['stat_perpage'];
        $data[ 'actions_types' ] = $this->clips_model->getActions_types();
        if($this->session->userdata)

        $segment = ( $clip_id == 0 ) ? 'all' : $clip_id;

        $paginator = $this->api->get_pagination( 'clips/statistics/' . $segment, count($all), $data[ 'perpage' ] );
        $this->set_content( 'clips/statistics', $data, NULL, $paginator );
    }

    function orderBy(){
        $protocol=($_SERVER['REMOTE_PORT']!=443)?'http://':'https://';
        $url=$protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if($url != $_SERVER['HTTP_REFERER']){
            unset($_REQUEST['order']);
            unset($_REQUEST['by']);
            unset($_SESSION['stat_order']);
            unset($_SESSION['stat_by']);
        }
        if(!empty($_SESSION['stat_order']) && !empty($_SESSION['stat_by'])){
            $order=' ORDER BY '.$_SESSION['stat_order'].' '.$_SESSION['stat_by'];
        }
        if(!empty($_REQUEST['order']) && !empty($_REQUEST['by'])){
            $order=' ORDER BY '.$_REQUEST['order'].' '.$_REQUEST['by'];
            $_SESSION['stat_order']=$_REQUEST['order'];
            $_SESSION['stat_by']=$_REQUEST['by'];
        }
        return (empty($order))?false:$order;
    }
    #-----------------------------------------------------------------------------

    function csv () {
        $this->clips_model->get_csv();
    }

    #-----------------------------------------------------------------------------

    function check_details () {
        if ( !$this->input->post( 'title' ) || !$this->input->post( 'code' ) ) {
            $this->error = $this->lang->line( 'empty_fields' );
            return FALSE;
        }
        return TRUE;
    }

    #------------------------------------------------------------------------------------------------

    function save_filter_data () {
        $filter = array (
            'words'         => $this->input->post( 'words' ),
            'search_mode'   => intval( $this->input->post( 'search_mode' ) ),
            'cat_id'        => intval( $this->input->post( 'cat_id' ) ),
            'sequence_id'   => intval( $this->input->post( 'sequence_id' ) ),
            'bin_id'        => intval( $this->input->post( 'bin_id' ) ),
            'gallery_id'    => intval( $this->input->post( 'gallery_id' ) ),
            'submission_id' => intval( $this->input->post( 'submission_id' ) ),
            //'footage_type_id' => intval($this->input->post('footage_type_id')),
            //'location_id' => intval($this->input->post('location_id')),
            'frame_rate'    => floatval( $this->input->post( 'frame_rate' ) ),
            'sd_hd'         => $this->input->post( 'sd_hd' ),
            'client_id'     => intval( $this->input->post( 'client_id' ) )
        );

        $this->session->set_userdata( array ( 'filter_clips' => $filter ) );
    }

    #------------------------------------------------------------------------------------------------

    function get_filter () {
        $filter = $this->session->userdata( 'filter_clips' );
        if ( $this->group[ 'is_editor' ] ) {
            $filter[ 'client_id' ] = $this->session->userdata( 'uid' );
        }
        if ( empty( $filter ) ) {
            return NULL;
        }
        $keys = array_keys( $filter );
        foreach ( $keys as $key ) {
            if ( empty( $filter[ $key ] ) ) {
                unset( $filter[ $key ] );
            }
        }
        return $filter;
    }

    #------------------------------------------------------------------------------------------------

    function set_group () {
        $uid = ( $this->session->userdata( 'uid' ) ) ? $this->session->userdata( 'uid' ) : $this->session->userdata( 'client_uid' );
        $this->group = $this->groups_model->get_group_by_user( $uid );
    }

    #------------------------------------------------------------------------------------------------

    function get_limit () {
        $limit_start = intval( $this->uri->segment( 4 ) );
        return ' limit ' . $limit_start . ',' . $this->settings[ 'perpage' ];
    }

    #------------------------------------------------------------------------------------------------

    function out ( $content = NULL, $pagination = NULL, $type = 1 ) {
        $this->builder->output( array ( 'content' => $content, 'path' => $this->path, 'pagination' => $pagination,
                                        'error'   => $this->error, 'message' => $this->message ), $type );
    }

    #------------------------------------------------------------------------------------------------

    function set_content ( $method, $data, $title = NULL, $pagination = NULL ) {
        if ( FALSE /*$this->group['is_editor']*/ ) {
            $data[ 'menu' ] = $this->load->view( 'main/ext/editormenu', array ( 'lang' => $this->langs, 'is_editor' => 1, 'current_step' => 'edit' ), TRUE );
            $content[ 'title' ] = $title;
            $content[ 'body' ] = $this->load->view( $method, $data, TRUE );
            $content[ 'add_js' ] = 'data/js/admin.js';
            $type = 0;
        } else {
            $content = $this->load->view( $method, $data, TRUE );
            $type = 1;
        }
        $this->out( $content, $pagination, $type );
    }

    function info () {
        $data[ 'clip' ] = $this->clips_model->get_clip_info( $this->id, $this->langs );
        $content[ 'body' ] = $this->load->view( 'clips/info', $data, TRUE );
        echo $content[ 'body' ];
    }

    function get_statistics_filter () {
        $filter_statistics = $this->session->userdata( 'filter_statistics' );
        if ( $filter_statistics ) {
            $datefrom = $filter_statistics[ 'datefrom' ];
            $dateto = $filter_statistics[ 'dateto' ];
        }else{
            $datefrom = $dateto = date('d.m.Y');
        }
        if ( $datefrom && $dateto )
            $where[ ] = 'DATE_FORMAT( time, \'%d.%m.%Y\') >= \'' . $datefrom . '\' AND DATE_FORMAT( time, \'%d.%m.%Y\') <= \'' . $dateto . '\'';
        elseif ( $datefrom )
            $where[ ] = 'DATE_FORMAT( time, \'%d.%m.%Y\') >= \'' . $datefrom . '\'';
        elseif ( $dateto )
            $where[ ] = 'DATE_FORMAT( time, \'%d.%m.%Y\') <= \'' . $dateto . '\'';

        if ( count( $where ) ) return ' ' . implode( ' AND ', $where );

        return '';
    }


    function save_statistics_filter () {
        $datefrom = $this->input->post( 'datefrom' );
        $dateto = $this->input->post( 'dateto' );

        if ( $this->input->post( 'filter' ) ) {
            $temp[ 'datefrom' ] = ( $datefrom ) ? $datefrom : date('Y-m-d').' 00:00:00';
            $temp[ 'dateto' ] = ( $dateto ) ? $dateto : date('Y-m-d').' 23:59:59';
            $this->session->set_userdata( array ( 'filter_statistics' => $temp ) );
        }
    }

    function get_statistics_csv () {
        $filter = $this->get_statistics_filter();
        $statistics = $this->clips_model->get_clip_statistic( NULL /*$this->id*/, $filter );
        if ( $statistics ) {
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s T', 0 ) );
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s T' ) );
            header( 'Cache-Control: private' );
            header( 'Cache-Control: no-store, no-cache, must-revalidate' );
            header( 'Cache-Control: post-check=0, pre-check=0', FALSE );
            header( 'Pragma: no-cache' );
            header( 'Content-type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="statistic.csv"' );

            echo '"User","Action","Date"', "\r\n";

            foreach ( $statistics as $item ) {
                echo '"', $item[ 'user_login' ], '","', $item[ 'action' ], '","', $item[ 'time' ], "\"\r\n";
            }
            exit();
        }
    }

    /**
     * @param array $headers
     * @param array $data - arrays
     * @param array $dataKeys
     */
    function get_data_to_csv (Array $headers,Array $data, Array $dataKeys=array()) {
        if ( !empty($data) ) {
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s T', 0 ) );
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s T' ) );
            header( 'Cache-Control: private' );
            header( 'Cache-Control: no-store, no-cache, must-revalidate' );
            header( 'Cache-Control: post-check=0, pre-check=0', FALSE );
            header( 'Pragma: no-cache' );
            header( 'Content-type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="statistic.csv"' );

            $header='';
            $datas='';
            foreach($headers as $k=>$head){
                $header.= '"'.$head.'";';
                /*if(empty($dataKeys)){
                    foreach($data[$k] as $item){
                        $datas.='"'.$item.'";';
                    }
                }else{
                    foreach($data[$dataKeys[$k]] as $item){
                        $datas.='"'.$item.'";';
                    }
                }*/
            }
            if(empty($dataKeys))
                foreach($data as $k=>$subdata){
                    foreach($subdata as $item)
                        $datas.='"'.$item.'",';
                    $datas.="\r\n";
                }
            else
                foreach($data as $subdata){
                    foreach($dataKeys as $key)
                        $datas.='"'.$subdata[$key].'",';
                    $datas.="\r\n";
                }

            $header=substr($header,0,-1);
            $datas=substr($datas,0,-1);
            $header.="\r\n";
            $datas.="\r\n";

            echo $header.$datas;
            exit();
        }
    }

    function GetSearchStatisticSessionName ( $name ) {
        $type = $this->uri->segment( 4 );
        if ( $type == 'overall' ) {
            return 'search-statistic-overall-' . (string) $name;
        }
        return 'search-statistic-request-' . (string) $name;
    }

    function PrepareSearchStatisticFilter () {
        $providerfilter = $this->input->post( 'providerfilter', TRUE );
        $userfilter = $this->input->post( 'userfilter', TRUE );
        $datefilter = $this->input->post( 'datefilter', TRUE );
        $provider = $this->input->post( 'provider', TRUE );
        $user = $this->input->post( 'user', TRUE );
        $datefrom = $this->input->post( 'datefrom', TRUE );
        $dateto = $this->input->post( 'dateto', TRUE );
        if ( $userfilter ) {
            if ( !empty( $user ) ) {
                $this->session->set_userdata( $this->GetSearchStatisticSessionName( 'user' ), $user );
            } else {
                $this->session->unset_userdata( $this->GetSearchStatisticSessionName( 'user' ) );
            }
        }
        if ( $providerfilter ) {
            if ( !empty( $provider ) ) {
                $this->session->set_userdata( $this->GetSearchStatisticSessionName( 'provider' ), $provider );
            } else {
                $this->session->unset_userdata( $this->GetSearchStatisticSessionName( 'provider' ) );
            }
        }
        if ( $datefilter ) {
            if ( !empty( $datefrom ) ) {
                $this->session->set_userdata( $this->GetSearchStatisticSessionName( 'datefrom' ), $datefrom );
            } else {
                $this->session->unset_userdata( $this->GetSearchStatisticSessionName( 'datefrom' ) );
            }
            if ( !empty( $dateto ) ) {
                $this->session->set_userdata( $this->GetSearchStatisticSessionName( 'dateto' ), $dateto );
            } else {
                $this->session->unset_userdata( $this->GetSearchStatisticSessionName( 'dateto' ) );
            }
        }
    }

    function CreateSearchStatisticFilter () {
        $type = $this->uri->segment( 4 );
        $user = $this->session->userdata( $this->GetSearchStatisticSessionName( 'user' ) );
        $provider = $this->session->userdata( $this->GetSearchStatisticSessionName( 'provider' ) );
        $datefrom = $this->session->userdata( $this->GetSearchStatisticSessionName( 'datefrom' ) );
        $dateto = $this->session->userdata( $this->GetSearchStatisticSessionName( 'dateto' ) );
        $pieces = array ();
        if ( $provider ) {
            $pieces[ ] = "provider_id = '{$provider}'";
        }
        if ( $user ) {
            $pieces[ ] = "user_login = '{$user}'";
        }
        if ( $datefrom && $type != 'overall' ) {
            $date = date( 'Y-m-d', strtotime( $datefrom ) );
            $pieces[ ] = "'{$date}' <= DATE_FORMAT( log.ctime, '%Y-%m-%d' )";
        }
        if ( $dateto && $type != 'overall' ) {
            $date = date( 'Y-m-d', strtotime( $dateto ) );
            $pieces[ ] = "DATE_FORMAT( log.ctime, '%Y-%m-%d' ) <= '{$date}'";
        }
        if ( !empty( $pieces ) ) {
            $query = 'WHERE ';
            $query .= implode( ' AND ', $pieces );
            return $query;
        }
        return NULL;
    }

    function SearchStatistic () {
        $this->load->model( 'search_model' );
        $type = $this->uri->segment( 4 );
        $this->PrepareSearchStatisticFilter();
        $data[ 'type' ] = $type;
        $data[ 'lang' ] = $this->langs;
        $filter = $this->CreateSearchStatisticFilter();
        if ( $type == 'overall' ) {
            // Отображаем общую статистику по кол-ву запросов
            $data[ 'logs' ] = $this->search_model->GetSearchOverallLogList( $filter );
            $all = $this->search_model->GetSearchOverallLogListCount( $filter );
            $data[ 'providers' ] = $this->search_model->GetSearchOverallProvidersList();

        } else {
            // Отображаем статистику по всем запросам
            $data[ 'logs' ] = $this->search_model->GetSearchRequestLogList( $filter );
            $all = $this->search_model->GetSearchRequestLogListCount( $filter );
            $data[ 'providers' ] = $this->search_model->GetSearchRequestProvidersList();
            $data[ 'filter' ][ 'datefrom' ] = $this->session->userdata( $this->GetSearchStatisticSessionName( 'datefrom' ) );
            $data[ 'filter' ][ 'dateto' ] = $this->session->userdata( $this->GetSearchStatisticSessionName( 'dateto' ) );
            $data[ 'filter' ][ 'user' ] = $this->session->userdata( $this->GetSearchStatisticSessionName( 'user' ) );
        }
        $data[ 'filter' ][ 'provider' ] = $this->session->userdata( $this->GetSearchStatisticSessionName( 'provider' ) );
        $this->path = 'Clips section / Search statistic';
        $paginator = $this->api->get_pagination( "clips/searchstatistic/" . $type, $all, $this->settings[ 'perpage' ] );
        $this->set_content( 'clips/searchstatistic', $data, NULL, $paginator );
    }

}