<?php

/**
 * Class Invoices
 * @property Invoices_model $im
 * @property Customers_model $cm
 * @property Import_model $import_model
 * @property Users_model $um
 */
class Invoices extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    var $colors;
    var $statuses_map;
    var $upload_statuses_map;
    const ORDER_PDF_PATH='http://s3.naturefootage.com/invoices/invoice-';
    function Invoices()
    {
        parent::__construct();

        $this->load->model('invoices_model','im');
        $this->load->model('customers_model','cm');
        $this->load->model('import_model');
        $this->load->model('users_model', 'um');
        $this->api->save_sort_order('invoices');
        $this->id = (int)$this->uri->segment(4);
        $this->langs = $this->uri->segment(1);
        $this->settings = $this->api->settings();
        if(empty($this->session->userdata('uid'))) redirect($this->langs . '/login');
        $this->colors = array(
            'imported_status' => array(
                'Imported' => '#C7FFE4',
                'Not imported' => '#F2F3B7',
            ),
            'admin_status' => array(
                'Fillout' => '#FFE1E1',
                'Reassigned' => '#F2F3B7',
                'Accepted online' => '#C7FFE4',
                'Accepted offline' => '#C7FFE4'
            ),
            'status' => array(
                '1' => '#F2F3B7',
                '2' => '#FFE1E1',
                '3' => '#C7FFE4'
            ),
            'release_status' => array(
                'Not approved' => '#FFE1E1',
                'Approved' => '#C7FFE4',
                'Preapproved' => '#F2F3B7',
                'Preapproved no payment' => '#F2F3B7',
            ),
            'download_email_status' => array(
                'Sent' => '#C7FFE4',
                'Not sent' => '#F2F3B7'
            ),
            'ftp_instructions' => array(
                'Sent' => '#C7FFE4',
                'Not sent' => '#F2F3B7'
            ),
            'imported_status_sent' => array(
                'Imported' => '#C7FFE4',
                'Not imported' => '#F2F3B7'
            ),
            'view_email_log' => array(),
            'order_review_status' => array(),
            'not_implemented' => array(
                '0' => 'white',
            ),
            'resume_order_email_status' => array(
                'Sent' => '#C7FFE4',
                'Not sent' => '#F2F3B7'
            ),
            'invoice_email_status' => array(
                'Sent' => '#C7FFE4',
                'Not sent' => '#F2F3B7'
            ),
            'rate_quote' => array(
                '0' => 'white',
                '1' => '#FFE1E1'
            )
        );


        $this->statuses_map = array(
            'status' => array(
                '1' => 'Not paid',
                '2' => 'Failed',
                '3' => 'Paid',
            ),
            'review_status' => array(
                'Review' => 'Review',
                'Hold' => 'Hold',
                'Completed' => 'Completed',
            ),
            'imported_status' => array(
                'Imported' => 'Imported',
                'Not imported' => 'Not imported',
            ),
            'admin_status' => array(
                'Fillout' => 'Fillout',
                'Reassigned' => 'Reassigned',
                'Accepted online' => 'Accepted online',
                'Accepted offline' => 'Accepted offline',
            ),
            'release_status' => array(
                'Not approved' => 'Not approved',
                'Approved' => 'Approved',
                'Preapproved no payment' => 'Preapproved no payment',
            ),
            'download_email_status' => array(
                'Sent' => 'Sent',
                'Not sent' => 'Not sent',
            ),
            'resume_order_email_status' => array(
                'Sent' => 'Sent',
                'Not sent' => 'Not sent',
            ),
            'downloaded' => array(
                '1' => 'Downloaded',
                '0' => 'Not downloaded'
            ),
            'invoices_email_status' => array(
                'Sent' => 'Sent',
                'Not sent' => 'Not sent',
            ),
            'rate_quote' => array(
                '0' => '0',
                '1' => '1'
            )
        );
        $this->upload_statuses_map = array(
            'Created' => 'Pending Transcoding',
            'Pending' => 'Pending Transcoding',
            'Lab' => 'Processing at Lab',
            'Transcoding' => 'Transcoding',
            'Cancelled' => 'Processing Error',
            'Uploading' => 'Uploading',
            'Uploaded' => 'Ready to Download',
        );
        $this->save_filter_data();
        $this->prepend();
    }

    #------------------------------------------------------------------------------------------------

    function prepend()
    {
        $action = $this->uri->segment(4);
        $id = $this->uri->segment(5);

        if($action=='customer' && $id){
            $temp['words'] = $this->cm->get_customer_name($id);

            $this->session->set_userdata(array('filter_invoices'=>$temp));
            redirect($this->langs.'/invoices/view');
        }
    }

    #------------------------------------------------------------------------------------------------

    function index()
    {
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function show()
    {
        $filter = ' and client_id = '.$this->session->userdata('client_uid');
        $order = ' order by lo.id desc';
        $limit = $this->get_limit();

        $all = $this->im->get_invoices_count($filter);

        $data['invoices'] = $this->im->get_invoices_list($filter, $order, $limit);
        $data['uri'] = $this->api->prepare_uri();
        $data['lang'] = $this->langs;

        $content['title'] = $this->lang->line('invoices');
        $content['body'] = $this->load->view('invoices/content', $data, true);
        $pagination = $this->api->get_pagination('invoices/show',$all,$this->settings['perpage']);

        $this->out($content, $pagination, 0);
    }

    #------------------------------------------------------------------------------------------------

    function view()
    {
//        $this->im->send_order_confirmation($_REQUEST['oid']);
//        exit();
        if($this->input->post('admin_status'))
            $this->admin_status();
        if($this->input->post('client_status'))
            $this->client_status();
        if($this->input->post('status'))
            $this->paymentstatus();
        if($this->input->post('imported_status'))
            $this->imported_status();
        if($this->input->post('release_status'))
            $this->release_status();
        if($this->input->post('download_email_status'))
            $this->download_email_status();
        if($this->input->post('resume_order_email_status'))
            $this->resume_order_email_status();
        if($this->input->post('star'))
            $this->star();
        if($this->input->post('sales_rep_id'))
            $this->order_sales_rep_id();

        $filter = $this->get_filter_data();
        $order = $this->api->get_sort_order('invoices');
        $default_order = ' order by lo.sales_rep, lo.star desc, lo.id desc';
        $limit = $this->get_limit();

        $all = $this->im->get_backend_invoices_count($filter);

        $data['colors'] = $this->colors;
        $data['statuses_map'] = $this->statuses_map;
        $data['invoices'] = $this->im->get_backend_invoices_list($filter, ($order) ? $order : $default_order , $limit);
        $data['sales_reps'] = $this->um->get_sales_representatives();
        $data['uri'] = $this->api->prepare_uri();
        $data['filter'] = $this->session->userdata('filter_invoices');
        $data['lang'] = $this->langs;

        if($this->input->post('export')){
            $this->export($filter, ($order) ? $order : ' order by lo.id desc');
        }
        $order = $this->im->get_order($this->id);
        $this->im->make_pdf($order, true);
        $this->path = 'Commerce / Invoices';

        $content = $this->load->view('invoices/view', $data, true);
        $pagination = $this->api->get_pagination('invoices/view',$all,$this->settings['perpage']);

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else
            $this->out($content, $pagination);
    }

    function test_order(){
        //$lab_invoice = json_decode('{"user":"ultrahdft","frontend_id":"1","items":[{"id":"98971","quantity":1,"license_use":"18","license_duration":"10","license_term":"105","delivery_method":"666","delivery_format":"14","delivery_frame_rate":""}],"payment_method":"Wire Transfer","special_instructions":"","license_data":{"production_title":"efr","production_description":"sf","name":"Dan  Baron","company":"Footage Search","street1":"810 Cannery Roo","street2":"","city":"Mont","state":"CA","zip":"93940","country":"USA","phone":"8888888888"},"billing_data":{"name":"Dan  Baron","company":"Footage Search","street1":"810 Cannery Roo","street2":"","city":"Mont","state":"CA","zip":"93940","country":"USA","phone":"8888888888"},"shipping_data":{"name":"Dan  Baron","company":"Footage Search","street1":"810 Cannery Roo","street2":"","city":"Mont","state":"CA","zip":"93940","country":"USA","phone":"8888888888"}}', true);
        $lab_invoice = json_decode('{"meta":{"ship_company":"UltraHDFootage UltraHDFootage","bill_company":"UltraHDFootage UltraHDFootage","ship_name":"UltraHDFootage UltraHDFootage","bill_name":"UltraHDFootage UltraHDFootage","lic_phone":"123456789","lic_country":"ukraine","lic_zip":"03150","lic_city":"kiev","lic_state":"kiev","lic_street2":"","lic_street1":"bozhenko","lic_company":"UltraHDFootage UltraHDFootage","lic_name":"UltraHDFootage UltraHDFootage","bill_street1":"bozhenko","ship_street1":"bozhenko","bill_street2":"","ship_street2":"","bill_city":"kiev","ship_city":"kiev","bill_state":"kiev","ship_state":"kiev","bill_zip":"03150","ship_zip":"03150","bill_country":"ukraine","ship_country":"ukraine","bill_phone":"123456789","ship_phone":"123456789"}}');
        $order = $this->im->save_invoice(0, $lab_invoice);
        var_dump($order);
        exit();
    }

    function star(){
        $this->im->set_order_star($this->id, (int)$_REQUEST['star_val']);
    }

    function order_sales_rep_id(){
        $ids = array((int)$this->input->post('order_id'));
        $sales_rep_id = (int)$this->input->post('sales_rep_id');
        if(!empty($ids))
            $this->im->change_sales_rep($ids, $sales_rep_id);
        $sales_rep = $this->um->get_sales_representative($sales_rep_id);
        if($this->input->is_ajax_request()){
            $res = array(
                'success' => 1,
                'type' => 'sales_rep',
                'salesRep' => $sales_rep ? '<a class="sales-rep" style="background-color:' . $sales_rep['color'] . '" href="' . $this->langs . 'users/sales_representatives.html">'.$sales_rep['fname'] . ' ' . $sales_rep['lname'].'</a>' : ''
            );
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    function export($filter = null, $order = null){

        set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']
            . '/application/libraries');
        require_once 'PHPExcel/Reader/Excel5.php';
        require_once 'PHPExcel/Reader/Excel2007.php';
        require_once 'PHPExcel/Writer/Excel5.php';

        $all_invoices = $this->im->get_backend_invoices_list($filter, $order);
        if(count($all_invoices)){
            $fields_map = array(
                'ref' => 0,
                'customer' => 1,
                'total' => 2,
                'status' => 3,
                'approve' => 4,
                'ctime' => 5
            );

            $objReader = new PHPExcel_Reader_Excel5();
            $objReader->setReadDataOnly(false);

            $objPHPExcel = $objReader->load($_SERVER['DOCUMENT_ROOT'].'/data/example/export.xls');
            $objActiveSheet = $objPHPExcel->getActiveSheet();
            $xl_row = 2;

            foreach($all_invoices as $invoice){
                //$xl_col = 0;
                foreach ($invoice as $field => $value) {
                    if(isset($fields_map[$field])){
                        switch ($field) {
                            case 'status':
                                $value = (is_numeric($value) ? $this->lang->line('order_status_' . $value) : $value);
                                break;
                            case 'approve':
                                $value = $value ? 'approved' : 'not approved';
                                break;
                            case 'total':
                                $value = $value  . ' ' . $invoice['currency'];
                                break;
                        }
                        $objActiveSheet->setCellValueExplicitByColumnAndRow($fields_map[$field], $xl_row, $value, 's');
                    }
                    //++$xl_col;
                }
                ++$xl_row;
            }

            $output = $_SERVER['DOCUMENT_ROOT'].'/data/upload/export/invoices.xls';
            if (is_file($output)) {
                unlink($output);
            }
            $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
            //$objWriter->save($output);

            ob_end_clean();
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/vnd.ms-excel');
            //For PHPExcel_Writer_Excel2007
            //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="invoices.xls"');

            $objWriter->save('php://output');
        }
    }


    #------------------------------------------------------------------------------------------------

    function visible()
    {
        $this->im->change_visible($this->input->post('id'));
        $this->api->log('log_invoices_visible', $this->input->post('id'));
        redirect($this->langs.'/invoices/view');
    }

    #------------------------------------------------------------------------------------------------

    function change_notes(){
        $notes = $this->input->post('notes');
        if($notes){
            $this->im->change_notes(array($this->id), $notes);
        }
    }

    function order_pdf(){
        $id = $this->id;
        //var_dump($this->im->get_order($id));
        //TODO permissions
        if(true){
            $order = $this->im->get_order($id);
            $file=$this->im->make_pdf($order, true);
            $ref=$_SERVER['HTTP_REFERER'];

            
            //echo "<script type='text/javascript' language='Javascript'>window.open('$file');setTimeout(window.close(),200);</script>";
            echo "<script type='text/javascript' language='Javascript'>window.location.href='$file';</script>";
            /*$path = 'data/upload/invoices/invoice-' . $id . '.pdf';
            $filename = 'invoice-' . $id . '.pdf';

            /*if(is_file($path)){
                header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
                header('Accept-Ranges: bytes');  // For download resume
                header('Content-Length: ' . filesize($path));  // File size
                header('Content-Encoding: none');
                header('Content-Type: application/pdf');  // Change this mime type if the file is not PDF
                header('Content-Disposition: inline; filename=' . $filename);  // Make the browser display the Save As dialog
                @readfile($path);
                exit();
            }
            else{
                show_404();
            }*/
        }
    }

    #------------------------------------------------------------------------------------------------

    function delete()
    {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id');

        $this->im->delete_invoices($ids);
        $this->api->log('log_invoices_delete', $ids);
        redirect($this->langs.'/invoices/view');
    }

    #------------------------------------------------------------------------------------------------

    function approve()
    {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id');

        $this->im->approve_invoices($ids);
        $this->api->log('log_invoices_approve', $ids);

        if ($this->id) {
            if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                redirect($this->langs. '/invoices/view');
            }
            else{
                redirect($this->langs . '/invoices/details/' . $this->id);
            }

        } else {
            redirect($this->langs. '/invoices/view');
        }
    }

    #------------------------------------------------------------------------------------------------

    function update()
    {
//        echo '<pre>';
//        print_r($this->input->post());
//        exit();
        if($this->id){
            $data = $this->input->post();
            $this->im->update_invoice($this->id, $data);
            redirect($this->langs . '/invoices/details/' . $this->id);
        }

        if($this->input->is_ajax_request()){
            $res = $this->recalculate_order($this->input->post());
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else {
            redirect($this->langs.'/invoices/view');
        }
    }

    function recalculate_order($data) {
        $result = array(
            'total' => array('license_total' => 0, 'delivery_total' => 0, 'total' => 0),
            'items' => array()
        );
        if ($data['items']) {
            foreach($data['items'] as $id => $item) {
                $price = $item['base_price'];
                if (isset($item['discount']) && $item['discount'] > 0) {
                    $price = $price - $price / 100 * $item['discount'];
                }
                $result['items'][$id]['total_price'] = $price;
                $result['total']['license_total'] += $price;
                if (isset($item['delivery_price']) && $item['delivery_price'] > 0) {
                    $price += $item['delivery_price'];
                    $result['total']['delivery_total'] += $item['delivery_price'];
                }
                $result['items'][$id]['total'] = $price;
                $result['total']['total'] += $price;

            }
        }
        return $result;
    }

    #------------------------------------------------------------------------------------------------

    function details()
    {
        $this->load->model('pricing_model');
        if($this->input->post('delivery_process'))
            $this->delivery_process();
        if(isset($_POST['upload_status']))
            $this->upload_status();

        $sort = $this->uri->segment(5);
        $by = $this->uri->segment(6);

        $invoice = $this->im->get_order($this->id,$sort,$by);
        $data['invoice'] = $invoice;
//        echo '<pre>';
//        print_r($invoice);
//        exit();

        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['vat'] = $this->settings['vat'];
        $data['sort'] = $sort;
        $data['by'] = $by;

        $this->path = 'Commerce / Invoices / Details';
        $content = $this->load->view('invoices/details', $data, true);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else
            $this->out($content);
    }

    function get_clients () {
        if ( isset( $_REQUEST[ 'term' ] ) ) {
            $this->db->like( 'fname', $_REQUEST[ 'term' ] );
            $this->db->or_like( 'lname', $_REQUEST[ 'term' ] );
            $this->db->or_like( 'login', $_REQUEST[ 'term' ] );
        }

        $this->db->select( 'id, fname, lname, login' );
        $this->db->from( 'lib_users' );
        $this->db->order_by( 'fname' );
        $query = $this->db->get();
        $result = $query->result_array();

        if ( count( $result ) ) {
            $res = array ();
            foreach ( $result as $result_item ) {
                $data = array (
                    'value'     => $result_item[ 'fname' ] . ' ' . $result_item[ 'lname' ] . ' (' . $result_item[ 'login' ] . ')',
                    'label'     => $result_item[ 'fname' ] . ' ' . $result_item[ 'lname' ] . ' (' . $result_item[ 'login' ] . ')',
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

    #------------------------------------------------------------------------------------------------

    function modifyorder()
    {
        if($this->input->post('delivery_process'))
            $this->delivery_process();
        if(isset($_POST['upload_status']))
            $this->upload_status();

        $data['invoice'] = $this->im->get_invoice($this->id, $this->langs);

        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['vat'] = $this->settings['vat'];

        $this->path = 'Commerce / Invoices / Details';
        $content = $this->load->view('invoices/modifyorder', $data, true);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else
            $this->out($content);
    }

    function orderstatus()
    {
        $this->load->model('upload_tokens_model');

        if($this->input->post('admin_status'))
            $this->admin_status();
        if($this->input->post('client_status'))
            $this->client_status();
        if($this->input->post('status'))
            $this->paymentstatus();
        if($this->input->post('imported_status'))
            $this->imported_status();
        if($this->input->post('release_status'))
            $this->release_status();
        if($this->input->post('review_status'))
            $this->reviewstatus();
        if($this->input->post('download_email_status'))
            $this->download_email_status();
        if($this->input->post('resume_order_email_status'))
            $this->resume_order_email_status();
        if($this->input->post('sales_rep_id')){
            $this->sales_rep();
        }
        if($this->input->post('download_status')){
            $this->download_status();
        }
        if($this->input->post('rate_quote')){
            $this->rate_quote();
        }

        if($this->input->post('delivery_process'))
            $this->delivery_process();
        if(isset($_POST['upload_status']))
            $this->upload_status();

        $this->load->model('users_model', 'um');
        $this->load->model('labs_model', 'lm');

        $order = $this->im->get_order($this->id);
//        foreach($order['items'] as &$item) {
//            if ($item['upload_status']
//                && array_key_exists($item['upload_status'], $this->upload_statuses_map)) {
//                $item['upload_status'] =  $this->upload_statuses_map[$item['upload_status']];
//            }
//        }
        $data['upload_statuses_map'] = $this->upload_statuses_map;
        $data['order'] = $order;
        $data['colors'] = $this->colors;
        $data['statuses_map'] = $this->statuses_map;
        $data['sales_reps'] = $this->um->get_sales_representatives();
        $data['labs'] = $this->lm->get_labs_list();
        foreach($data['sales_reps'] as $key=>$rep){
            if($rep['id'] == $data['order']['sales_rep']){
                $data['order']['selected_sales_rep'] = $rep;
                break;
            }
        }
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['vat'] = $this->settings['vat'];
        $data['ftp_access'] = $this->get_order_ftp_access($data['order']['user']['login']);

        $data['token'] = $this->upload_tokens_model->get_token_by_order_id($this->id);

        $content = $this->load->view('invoices/orderstatus', $data, true);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
        else
            $this->out($content);
    }

    function get_order_ftp_access($user_id){
        $this->load->model('ftpaccounts_model', 'ftpm');
        $ftp_access = $this->ftpm->get_ftpaccount_by_userid($user_id);
        $store = $this->ftpm->get_store_details();
        $ftp_access['ftp_server'] = $store['user_delivery']['host'];
        return $ftp_access;
    }

    function prolongate_access(){
        if($this->id){
            $this->im->prolongate_access($this->id);
            if(isset($_REQUEST['ajax']) && $_REQUEST['ajax']){
                $expiration_date = $this->im->get_order_prop_by_name($this->id, 'access_expired');
                $this->ajax_json_response(
                    array(
                        'success'=> 1,
                        'expiration_date' => $expiration_date,
                        'callback' => 'setExpirationDate',
                        'message' => 'New expiration date is ' . $expiration_date,
                    )
                );
            }
        }
        redirect($this->langs.'/invoices/orderstatus/'.$this->id.'.html');
    }

    function expire_access(){
        if($this->id){
            $this->im->expire_access($this->id);
            if(isset($_REQUEST['ajax']) && $_REQUEST['ajax']){
                $this->ajax_json_response(
                    array(
                        'success'=> 1,
                        'expiration_date' => $this->im->get_order_prop_by_name($this->id, 'access_expired'),
                        'callback' => 'setExpirationDate',
                        'message' => 'Acces expired'
                    )
                );
            }
        }
        redirect($this->langs.'/invoices/orderstatus/'.$this->id.'.html');
    }

    function ajax_json_response($data){
        $this->output->set_content_type('application/json');
        echo json_encode($data);
        exit();
    }

    function status_ajax_response($status_name, $status, $callback = 'updateDescription'){
        $data = array(
            'success' => 1,
            'color' => $this->colors[$status_name][$status],
            'description' => $this->statuses_map[$status_name][$status],
            'callback' => $callback
        );
        $this->ajax_json_response($data);
    }

    function statuses() {

        if($this->input->post('admin_status'))
            $this->admin_status();
        if($this->input->post('client_status'))
            $this->client_status();
        if($this->input->post('status'))
            $this->paymentstatus();
        if($this->input->post('imported_status'))
            $this->imported_status();
        if($this->input->post('release_status'))
            $this->release_status();
        if($this->input->post('download_email_status'))
            $this->download_email_status();
        if($this->input->post('resume_order_email_status'))
            $this->resume_order_email_status();

        $data['invoice'] = $this->im->get_invoice($this->id, $this->langs);
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;

        $this->path = 'Commerce / Invoices / Statuses';
        $content = $this->load->view('invoices/statuses', $data, true);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else
            $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function reviewstatus()
    {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id');
        $status_name = 'review_status';
        $status = $_REQUEST[$status_name];
        if(in_array($status, array('Review', 'Hold', 'Completed'))){
            foreach($ids as $id){
                $this->im->set_review_status($id, $status);
            }
        }
        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*            $res = array(
                            'success' => 1,
                            'callback' => 'updateDescription',
                            'description' => $status
                            //'color' => $this->colors['review_status'][$status]
                        );
                        $this->output->set_content_type('application/json');
                        echo json_encode($res);
                        exit();
                        */
        }
        else{
            redirect($this->langs.'/invoices/view');
        }
    }

    #------------------------------------------------------------------------------------------------

    function paymentstatus() {
        if($this->id)
            $ids[] = $this->id;
        elseif(is_array($this->input->post('id')))
            $ids = $this->input->post('id');
        else
            $ids[] = $this->input->post('id');

        $status_name = 'status';
        $status = $this->input->post($status_name);
        if($status)
            $this->im->set_payment_status($ids, $status);
        else
            $this->im->change_payment_status($ids);

        $this->api->log('log_invoices_paymentstatus', $ids);

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*
            $res = array(
                'success' => 1,
                'color' => $this->colors['status'][$status],
                'description' => $this->statuses_map['status'][$status],
                'callback' => 'updateDescription'
            );
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function admin_status() {
        $status_name = 'admin_status';
        $status = $this->input->post($status_name);
        if($this->id) $ids[] = $this->id;
        else $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_admin_status($ids, $status);

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*            $res = array(
                            'success' => 1,
                            'color' => $this->colors['admin_status'][$status],
                            'description' => $this->statuses_map['admin_status'][$status],
                            'callback' => 'updateDescription'
                        );
                        $this->output->set_content_type('application/json');;
                        echo json_encode($res);
                        exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function client_status() {
        $status = $this->input->post('client_status');
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_client_status($ids, $status);

        if($this->input->is_ajax_request()){
            $res = array('success' => 1,
                         'color' => $this->colors['client_status'][$status]
            );
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function imported_status() {
        $status_name = 'imported_status';
        $status = $this->input->post($status_name);
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_imported_status($ids, $status);

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*            $res = array(
                            'success' => 1,
                            'color' => $this->colors['imported_status'][$status],
                            'description' => $this->statuses_map['imported_status'][$status],
                            'callback' => 'updateDescription'
                        );
                        $this->output->set_content_type('application/json');;
                        echo json_encode($res);
                        exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function release_status() {
        $status_name = 'release_status';
        $status = $this->input->post($status_name);
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_release_status($ids, $status);

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*
            $res = array(
                'success' => 1,
                'color' => $this->colors['release_status'][$status],
                'description' => $this->statuses_map['release_status'][$status],
                'callback' => 'updateDescription'
            );
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function download_email_status() {
        $status_name = 'download_email_status';
        $status = $this->input->post($status_name);
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_download_email_status($ids, $status);

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*$res = array(
                'success' => 1,
                'color' => $this->colors['download_email_status'][$status]
            );
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function resume_order_email_status() {
        $status_name = 'resume_order_email_status';
        $status = $this->input->post($status_name);
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($status && $ids)
            $this->im->change_resume_order_email_status($ids, $status);
        /*if($status == '')
            $this->download_email();*/

        if($this->input->is_ajax_request()){
            $this->status_ajax_response($status_name, $status);
            /*$res = array(
                'success' => 1,
                'color' => $this->colors['resume_order_email_status'][$status]
            );
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();*/
        }
        else{
            if ($this->id) {
                if(strpos($_SERVER['HTTP_REFERER'], 'view')){
                    redirect($this->langs. '/invoices/view');
                }
                elseif(strpos($_SERVER['HTTP_REFERER'], 'statuses')){
                    redirect($this->langs. '/invoices/statuses/' . $this->id);
                }
                else{
                    redirect($this->langs . '/invoices/details/' . $this->id);
                }
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function sales_rep(){
        $sales_rep_id = $this->input->post('sales_rep_id');
        if($this->id)
            $ids[] = $this->id;
        else
            $ids[] = $this->input->post('id');

        if($sales_rep_id && $ids)
            $this->im->change_sales_rep($ids, $sales_rep_id);

        $sales_rep = $this->um->get_sales_representative($sales_rep_id);
        if($this->input->is_ajax_request() && !empty($sales_rep)){
            $res = array(
                'success' => 1,
                'color' => $sales_rep['color'],
                'description' => $sales_rep['fname'] . ' ' . $sales_rep['lname'],
                'callback' => 'changeRepPost'
            );
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
    }

    function download_status(){
        if($this->input->post('download_status') && $this->id && $this->input->post('order_item_id')){
            $status_name = 'download_status';
            $status = $this->input->post($status_name);
            if($status == 'reset'){
                $status = '0';
            }
            $this->im->reset_download($this->input->post('order_item_id'));
            if($this->input->is_ajax_request()){
                $this->status_ajax_response('downloaded', $status, 'downloadStatusPost');
            }
        }
    }

    function rate_quote(){
        if($this->input->post('rate_quote') && $this->id){
            $status_name = 'rate_quote';
            $status = $this->input->post($status_name);
            if($status == 'reset'){
                $status = '0';
            }
            $this->im->rate_quote($this->id,$status);
            if($this->input->is_ajax_request()){
                $this->status_ajax_response('rate_quote', $status, 'updateDescription');
            }
        }
    }

    function download_email(){

        $invoice = $this->im->get_invoice($this->id);
        //if ($invoice['payment_method'] == 'Check' && !$this->input->post('modified')){echo false; exit();}
        $downloads=$this->im->get_download_page_generate($this->id);
        if($this->input->post('get_template') && $this->input->is_ajax_request() ){
            $template = false;
            if($downloads){
                $this->load->helper('emailer');
                $template = Emailer::GetInstance()->LoadTemplate('touser-order-downloads')
                    ->TakeSenderSystem()
                    ->SetRecipientEmail($invoice['email'])
                    ->SetTemplateValue('downloads', 'links', $downloads)
                    ->SetTemplateValue('order', $invoice)
                    ->SetMailType('html')
                    ->GetParsedTemplate();
                Emailer::GetInstance()->Clear();
                $template['to'] = $invoice['email'];
                $template['cc'] = ''; //TODO implement
            }
            else{
                $error = 'No downloads available';
            }
            $res = array('template' => $template, 'error' => $error, 'success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }

        if($invoice['email']){
            if(!empty($downloads)){
                $this->load->helper('emailer');
                if($this->input->post('modified')){
                    Emailer::GetInstance()->LoadTemplate('touser-order-downloads')
                        ->TakeSenderSystem()
                        ->SetRecipientEmail($this->input->post('to'))
                        ->SetTemplateValue('downloads', 'links', $downloads)
                        ->SetTemplateValue('order', $invoice)
                        ->SetCC($this->input->post('cc'))
                        ->SetTemplate($this->input->post('body'))
                        ->SetMailType('html')
                        ->Send();
                    Emailer::GetInstance()->Clear();
                }
                else{
                    Emailer::GetInstance()->LoadTemplate('touser-order-downloads')
                        ->TakeSenderSystem()
                        ->SetRecipientEmail($invoice['email'])
                        ->SetTemplateValue('downloads', 'links', $downloads)
                        ->SetTemplateValue('order', $invoice)
                        ->SetMailType('html')
                        ->Send();
                    Emailer::GetInstance()->Clear();
                }
                $success_sent = true;
                $this->im->change_download_email_status(array($invoice['id']), 'Sent');
            }
        }

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            if(isset($success_sent)){
                $res = array(
                    'message' => 'Sent',
                    'send_status'    => 'Sent',
                    'color' => $this->colors['download_email_status']['Sent'],
                );
            }
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    function download_link(){
        $link = $this->im->get_invoice_guest_download_link($this->id);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $res['link'] = $link;
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    function delete_download_link(){
        $this->im->delete_download_token_by_order_id($this->id);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    function email_download_link(){
        $invoice = $this->im->get_invoice($this->id);
        $invoice['download_link'] = $this->im->get_download_token_no_generate($this->id);

        if($this->input->post('get_template') && $this->input->is_ajax_request() ){
            $this->load->helper('emailer');
            if(true){
                $template = Emailer::GetInstance()->LoadTemplate('touser-download-link')
                    ->TakeSenderSystem()
                    ->SetTemplateValue('order', $invoice)
                    ->SetMailType('html')
                    ->GetParsedTemplate();
                Emailer::GetInstance()->Clear();
                $template['to'] = $invoice['email'];
                $template['cc'] = '';
            }
            else{
                $error = 'No resume link';
            }
            $res = array('template' => $template, 'error' => $error, 'success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }

        $this->load->helper('emailer');
        if($this->input->post('modified')){
            Emailer::GetInstance()->LoadTemplate('touser-download-link')
                ->TakeSenderSystem()
                ->SetRecipientEmail($this->input->post('to'))
                ->SetTemplateValue('order', $invoice)
                ->SetCC($this->input->post('cc'))
                ->SetTemplate($this->input->post('body'))
                ->SetMailType('html')
                ->Send();
            Emailer::GetInstance()->Clear();
        }
        else{
            Emailer::GetInstance()->LoadTemplate('touser-download-link')
                ->TakeSenderSystem()
                ->SetRecipientEmail($invoice['email'])
                ->SetTemplateValue('order', $invoice)
                ->SetMailType('html')
                ->Send();
            Emailer::GetInstance()->Clear();
        }
        $success_sent = true;

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            if(isset($success_sent)){
                $res = array(
                    'message' => 'Sent',
                );
            }
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
    }

    function resume_order_email(){
        $invoice = $this->im->get_invoice($this->id);
        $userId=$this->um->get_user_by_login($invoice['login']);
        $user=$this->um->get_user_by_id($userId);
        //$pdf_link = site_url().'data/upload/invoices/invoice-'.$this->id.'.pdf';
        $pdf_link = Invoices::ORDER_PDF_PATH.$this->id.'.pdf';
        //$pdf_link = '<a href="'.$pdf_link.'">'.$pdf_link.'</a>';

        if($this->input->post('get_template') && $this->input->is_ajax_request() ){
            $this->load->helper('emailer');
            $resume_link = $this->im->get_resume_link($invoice['id']);
            if($resume_link){
                $template = Emailer::GetInstance()->LoadTemplate('touser-order-resume')
                    ->TakeSenderSystem()
                    ->SetRecipientEmail($invoice['email'])
                    ->SetTemplateValue('order', $invoice)
                    ->SetTemplateValue('order', 'resume_link', $resume_link)
                    ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                    ->SetTemplateValue('recipient', $user)
                    ->SetMailType('html')
                    ->GetParsedTemplate();
                Emailer::GetInstance()->Clear();
                $template['to'] = $invoice['email'];
                $template['cc'] = ''; //TODO implement
            }
            else{
                $error = 'No resume link';
            }
            $res = array('template' => $template, 'error' => $error, 'success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }


        if($invoice['email']){
            $this->load->helper('emailer');
            $resume_link = $this->im->get_resume_link($invoice['id']);
            if($resume_link){
                if($this->input->post('modified')){
                    Emailer::GetInstance()->LoadTemplate('touser-order-resume')
                        ->TakeSenderSystem()
                        ->SetRecipientEmail($this->input->post('to'))
                        ->SetTemplateValue('order', $invoice)
                        ->SetTemplateValue('order', 'resume_link', $resume_link)
                        ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                        ->SetTemplateValue('recipient', $user)
                        ->SetCC($this->input->post('cc'))
                        ->SetTemplate($this->input->post('body'))
                        ->SetMailType('html')
                        ->Send();
                    Emailer::GetInstance()->Clear();

                }
                else{
                    Emailer::GetInstance()->LoadTemplate('touser-order-resume')
                        ->TakeSenderSystem()
                        ->SetRecipientEmail($invoice['email'])
                        ->SetTemplateValue('order', $invoice)
                        ->SetTemplateValue('order', 'resume_link', $resume_link)
                        ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                        ->SetTemplateValue('recipient', $user)
                        ->SetMailType('html')
                        ->Send();
                    Emailer::GetInstance()->Clear();
                }
                $success_sent = true;
                $this->im->change_resume_order_email_status(array($invoice['id']), 'Sent');
            }
        }

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            if(isset($success_sent)){
                $res = array(
                    'message' => 'Sent',
                    'send_status' => 'Sent',
                    'color' => $this->colors['resume_order_email_status']['Sent']
                );
            }
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    function invoice_email(){
        $invoice = $this->im->get_invoice($this->id);
//        $template_name = 'touser-invoice-details';
        $template_name = 'touser-new-order';

        $order = $this->im->get_order($this->id);
        $this->im->make_pdf($order, true);
        //$pdf_link = site_url().'data/upload/invoices/invoice-'.$this->id.'.pdf';
        $pdf_link = Invoices::ORDER_PDF_PATH.$this->id.'.pdf';
        //$pdf_link = '<a href="'.$pdf_link.'">'.$pdf_link.'</a>';

        if($this->input->post('get_template') && $this->input->is_ajax_request() ){
            /*$order = $this->im->get_order($this->id);
            $this->im->make_pdf($order, true);
            $pdf_link = site_url().'data/upload/invoices/invoice-'.$this->id.'.pdf';
            $pdf_link = '<a href="'.$pdf_link.'">'.$pdf_link.'</a>';*/
            $error = false;
            $this->load->helper('emailer');
            $template = Emailer::GetInstance()->LoadTemplate($template_name)
                ->TakeSenderSystem()
                ->SetRecipientEmail($invoice['email'])
                ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                ->SetTemplateValue('order', $order)
                ->SetMailType('html')
                ->GetParsedTemplate();
            Emailer::GetInstance()->Clear();
            $template['to'] = $invoice['email'];
            //$template['cc'] = ''; //TODO implement
            $res = array('template' => $template, 'error' => $error, 'success' => 1);
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }


        if($invoice['email']){
            $this->load->helper('emailer');
            if($this->input->post('modified')){
                Emailer::GetInstance()->LoadTemplate($template_name)
                    ->TakeSenderSystem()
                    ->SetRecipientEmail($this->input->post('to'))
                    ->SetCC($this->input->post('cc'))
                    ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                    ->SetTemplate($this->input->post('body'))
                    ->SetMailType('html')
                    ->Send();
                Emailer::GetInstance()->Clear();
            }
            else{
                /*$pdf_link = site_url().'data/upload/invoices/invoice-'.$this->id.'.pdf';
                $pdf_link = '<a href="'.$pdf_link.'">'.$pdf_link.'</a>';*/
                Emailer::GetInstance()->LoadTemplate($template_name)
                    ->TakeSenderSystem()
                    ->SetRecipientEmail($invoice['email'])
                    ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                    ->SetMailType('html')
                    ->Send();
                Emailer::GetInstance()->Clear();
            }
            $success_sent = true;

            if(!$this->is_admin($invoice['client_id']))
                $this->im->change_invoice_email_status(array($invoice['id']), 'Sent');
        }

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            if(isset($success_sent))
                $res = array(
                    'message' => 'Sent',
                    'send_status' => 'Sent',
                    'color' => $this->colors['invoice_email_status']['Sent'],
                );
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }

    private function is_admin($user_id)
    {
        $group = $this->groups_model->get_group_by_user($user_id);
        return (isset($group['is_admin']) && $group['is_admin']);
    }

    function delivery_process() {
        $delivery_process = $this->input->post('delivery_process');
        if(is_array($this->input->post('id')))
            $ids = $this->input->post('id');
        else
            $ids[] = $this->input->post('id');

        if($delivery_process && $ids)
            $this->im->change_delivery_process($ids, $delivery_process);

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            if ($this->id) {
                redirect($this->langs . '/invoices/details/' . $this->id);
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    function upload_status() {
        $status = $this->input->post('upload_status');
        if(is_array($this->input->post('id')))
            $ids = $this->input->post('id');
        else
            $ids[] = $this->input->post('id');

        if($ids)
            $this->im->change_upload_status($ids, $status);

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            if ($this->id) {
                redirect($this->langs . '/invoices/details/' . $this->id);
            } else {
                redirect($this->langs. '/invoices/view');
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function more()
    {
        $data['invoice'] = $this->im->get_invoice($this->id, $this->langs);
        //echo '<pre>';
        //print_r($data['invoice']);
        //exit();

        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['vat'] = $this->settings['vat'];

        $content['title'] = 'Invoice details';
        $content['body'] = $this->load->view('invoices/more', $data, true);
        $this->out($content, null, 0);
    }

    #------------------------------------------------------------------------------------------------

    function save_filter_data()
    {
        $words = $this->input->post('words');
        $ref = $this->input->post('ref');
        $status = $this->input->post('status');
        $datefrom = $this->input->post('datefrom');
        $dateto = $this->input->post('dateto');
        $customer_email  = $this->input->post('customer_email');
        $clip_code = $this->input->post('clip_code');
        $clip_id = $this->input->post('clip_id');
        $approve = $this->input->post('approve');
        $order_id = $this->input->post('order_id');
        $username = $this->input->post('username');
        $sales_rep = $this->input->post('sales_rep');
        $review_status = $this->input->post('review_status');

        if($this->input->post('filter')){
            $temp['words'] = ($words) ? $words : '';
            $temp['ref'] = ($ref) ? $ref : '';
            $temp['status'] = ($status) ? $status : '';
            $temp['datefrom'] = ($datefrom) ? $datefrom : '';
            $temp['customer_email'] = ($customer_email) ? $customer_email : '';
            $temp['clip_code'] = ($clip_code) ? $clip_code : '';
            $temp['clip_id'] = ($clip_id) ? $clip_id : '';
            $temp['approve'] = ($approve) ? $approve : '';
            $temp['order_id'] = ($order_id) ? $order_id : '';
            $temp['username'] = ($username) ? $username : '';
            $temp['sales_rep'] = ($sales_rep) ? $sales_rep : '';
            $temp['review_status'] = ($review_status) ? $review_status : '';

            $this->session->set_userdata(array('filter_invoices'=>$temp));
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_filter_data($type=null)
    {
        $filter_invoices = $this->session->userdata('filter_invoices');

        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        if($group['is_editor'] || $group['is_client']){
            //$filter_invoices['provider_id'] = $this->session->userdata('uid');
            $filter_invoices['login'] = $this->session->userdata('login');
        }

        if($filter_invoices){

            $status = $filter_invoices['status'];
            $words = $filter_invoices['words'];
            $ref = $this->api->clear_order_format($filter_invoices['ref']);
            $datefrom = $filter_invoices['datefrom'];
            $dateto = $filter_invoices['dateto'];
            $customer_email  = $filter_invoices['customer_email'];
            $clip_code = $filter_invoices['clip_code'];
            $clip_id = $filter_invoices['clip_id'];
            $approve = $filter_invoices['approve'];
            $provider_id = $filter_invoices['provider_id'];
            $order_id = $filter_invoices['order_id'];
            $username = $filter_invoices['username'];
            $sales_rep = $filter_invoices['sales_rep'];
            $review_status = $filter_invoices['review_status'];
            $login=$filter_invoices['login'];


            if($status) $where[] = ($status) ? 'lo.status='.$status : '';
            if($words) $where[] = '(concat(lc.fname," ",lc.lname) like "%'.$words.'%")';
            if($ref) $where[] = 'lp.order_id='.$ref;
            if($provider_id) $where[] = 'lc.provider_id='.$provider_id;

            if($datefrom && $dateto)
                $where[] = 'DATE_FORMAT(lo.ctime,\'%d.%m.%Y\')>="'.$datefrom.'" and DATE_FORMAT(lo.ctime,\'%d.%m.%Y\')<="'.$dateto.'"';
            elseif($datefrom)
                $where[] = 'DATE_FORMAT(lo.ctime,\'%d.%m.%Y\')>="'.$datefrom.'"';
            elseif($dateto)
                $where[] = 'DATE_FORMAT(lo.ctime,\'%d.%m.%Y\')<="'.$dateto.'"';

            if($customer_email){
                $where[] = 'lc.email=\'' . $customer_email . '\'';
            }
            if($clip_code){
                $where[] = 'loc.code=\'' . $clip_code . '\'';
            }
            if($clip_id){
                $where[] = 'loc.id=\'' . $clip_id . '\'';
            }
            if($login){
                $where[] = 'lc.login=\'' . $login . '\'';
            }

            if($approve) {
                $approve = $approve == 1 ? 1 : 0;
                $where[] = 'lo.approve = ' . $approve;
            }
            if($order_id) {
                $where[] = 'lo.id = ' . $order_id;
            }
            if($review_status) {
                $where[] = 'lo.review_status = \'' . $review_status . '\'';
            }
            if($username) {
                $name_parts = explode(' ', trim($username));
                if(isset($name_parts[1])){
                    $where[] = '(lc.fname like \'%' . $name_parts[0] . '%\' OR lc.fname like \'%' . $name_parts[1] . '%\' OR lc.lname like \'%' . $name_parts[0] . '%\' OR lc.lname like \'%' . $name_parts[1] . '%\')';
                }
                else{
                    $where[] = '(lc.fname like \'%' . $name_parts[0] . '%\' OR lc.lname like \'%' . $name_parts[0] . '%\')';
                }
            }
            if($sales_rep) {
                $where[] = '(lsr.id=' . $sales_rep . ')';
                /*$name_parts = explode(' ', trim($sales_rep));
                if(isset($name_parts[1])){
                    $where[] = '(lsr.fname like \'%' . $name_parts[0] . '%\' OR lsr.fname like \'%' . $name_parts[1] . '%\' OR lsr.lname like \'%' . $name_parts[0] . '%\' OR lsr.lname like \'%' . $name_parts[1] . '%\')';
                }
                else{
                    $where[] = '(lsr.fname like \'%' . $name_parts[0] . '%\' OR lsr.lname like \'%' . $name_parts[0] . '%\')';
                }*/
            }

            //$where[] = 'lo.review_status = \'Review\'';

            if(count($where)) return ' and '.implode(' and ',$where);
        }
        return '';
    }

    #------------------------------------------------------------------------------------------------

    function inquiries() {
        $action = $this->uri->segment(4);
        $id = $this->uri->segment(5);

        if ($id && ($action == 'delete')) {
            $this->im->del_inquiry($id);
            redirect($this->langs . '/invoices/inquiries');
            exit();
        }

        $view['lang'] = $this->langs;
        $view['inquiries'] = $this->im->get_inquiries();
        $data = $this->load->view('invoices/inquiries', $view, true);

        $this->path = 'Commerce / Inquiries';

        $this->out($data);
    }

    #------------------------------------------------------------------------------------------------

    function get_limit()
    {
        return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
    }

    #------------------------------------------------------------------------------------------------

    function out($content=null, $pagination=null, $type=1)
    {
        $this->builder->output(array('content'=>$content,'path'=>$this->path,
                                     'pagination'=>$pagination, /*'hide_bottom_pagination' => true,*/ 'error'=>$this->error),$type);
    }

    #------------------------------------------------------------------------------------------------

    function make_token(){
        $order_id  = $this->uri->segment(4);

        $this->load->model('clips_model');
        $this->load->model('upload_tokens_model');

        $owner_id = $this->im->get_invoice_owner($order_id);
        $owner = $this->um->get_user_by_id($owner_id);

        $path = "/".$owner['login'].'/order'.$order_id;
        $upload_token_id = $this->upload_tokens_model->generate_token($order_id, $path, 0, 0);

        redirect($this->langs . '/invoices/orderstatus/'.$order_id);

        //$lab_clips = $this->clips_model->get_lab_clips($order_id);

    }


    #------------------------------------------------------------------------------------------------

    function deleteitems(){
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        if($group['is_admin']){
            if(isset($_POST['itemsIds'])){
                $arr['success']='ok';
                $arr['post']=$_POST;
                $arr['idsArr']=explode(',',$_POST['itemsIds']);
                $this->im->delete_invoices_items($arr['idsArr'],$_POST['orderId'],$_POST['sum'],$_POST['delivery_cost'],$_POST['total']);
                echo json_encode($arr);
            }
        }else redirect($this->langs . '/login');
    }
    #-------------------------------------------------------------------------------------------------
    function s3sync($id){
        if($this->input->is_ajax_request()){
            $res = array('success' => $this->im->set_s3_sync($id));
            $this->output->set_content_type('application/json');
            echo json_encode($res);
            exit();
        }
    }
    /*function test(){
        $this->load->model('deliveryoptions_model');
        $z=$this->deliveryoptions_model->get_delivery_by_id(2,140);
        var_dump($z);
    }*/
}

