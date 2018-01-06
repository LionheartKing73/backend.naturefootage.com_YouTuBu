<?php

/**
 * Class Invoices_model
 * @property Upload_tokens_model $upload_tokens_model
 * @property Deliveryoptions_model $deliveryoptions_model
 * @property Users_model $users_model
 * @property Clips_model $clm
 * @property Aws_model $aws_model
 * @property CI_DB_active_record db_master
 */
class Invoices_model extends CI_Model {
    const ORDER_PDF_PATH='http://s3.naturefootage.com/invoices/invoice-';

    function Invoices_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('images_model','imm');
        $this->load->model('clips_model','clm');
        $this->load->model('fragments_model','frm');
        $this->load->model('timeline_model','tlm');
        $this->load->model('users_model','um');
        $this->load->helper('Emailer');
    }

    #------------------------------------------------------------------------------------------------

    function get_invoices_count($filter)
    {
        $query = $this->db->query(
            'SELECT lo.id FROM lib_users lc, lib_orders lo, lib_countries lcc
          WHERE lc.country_id=lcc.id AND lo.client_id=lc.id '.$filter);
        return $query->num_rows();
    }

    #------------------------------------------------------------------------------------------------

    function get_invoices_list($filter=null, $order=null, $limit=null)
    {
        $query = $this->db->query('select lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lc.fname,\' \',lc.lname) as customer, lcc.currency from lib_users as lc, lib_orders as lo, lib_countries as lcc where lc.country_id=lcc.id and lo.client_id=lc.id '.$filter.$order.$limit);
        $rows = $query->result_array();

        foreach($rows as $k=>$v){
            $rows[$k]['ref'] = $this->api->order_format($v['id']);
            $rows[$k]['status_text'] = (
                is_numeric($v['status'])
                ? ucfirst($this->lang->line('order_status_'.$v['status']))
                : $v['status']
            );
            //$rows[$k]['approve_status'] = $this->lang->line('order_approve_status_'.$v['approve']);
            //($v['approve'] == 1) ? 'Approved' : 'Not approved';
        }
        return $rows;
    }

    #------------------------------------------------------------------------------------------------

    function get_backend_invoices_count($filter)
    {
        $query = $this->db->query('SELECT DISTINCT lo.id
        FROM lib_orders as lo
        LEFT JOIN lib_users as lc ON lo.client_id = lc.id
        LEFT JOIN lib_orders_items loi ON lo.id = loi.order_id
        LEFT JOIN lib_clips loc ON loi.item_id = loc.id
        LEFT JOIN lib_sales_representatives lsr ON lsr.id = lo.sales_rep
        WHERE 1 ' . $filter);
        return $query->num_rows();
    }

    #------------------------------------------------------------------------------------------------

    function get_backend_invoices_list($filter=null, $order=null, $limit=null){
        $q='SELECT DISTINCT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lc.fname,\' \',lc.lname) as customer,
         lc.email, lsr.id as sales_rep_id, lol.production_title
        FROM lib_orders as lo
        LEFT JOIN lib_users as lc ON lo.client_id = lc.id
        LEFT JOIN lib_orders_items loi ON lo.id = loi.order_id
        LEFT JOIN lib_clips loc ON loi.item_id = loc.id
        LEFT JOIN lib_sales_representatives lsr ON lsr.id=lo.sales_rep
        LEFT JOIN lib_order_license lol ON lol.order_id = lo.id
        WHERE 1 ' . $filter . $order . $limit;
        $query = $this->db->query($q);
        $rows = $query->result_array();

        foreach($rows as $k=>$v){
            $rows[$k]['ref'] = $this->api->order_format($v['id']);
            if (isset($v['status']) && is_numeric($v['status'])) {
                $rows[$k]['status_text'] = (
                    is_numeric($v['status'])
                    ? ucfirst($this->lang->line('order_status_' . $v['status']))
                    : $v['status']
                );
            } else {
                $rows[$k]['status_text'] = '';
            }
            $rows[$k]['approve_status'] = ($v['approve'] == 1) ? 'Approved' : 'Not approved';
            $rows[$k]['sales_rep'] = $this->um->get_sales_representative($v['sales_rep_id']);
            $rows[$k]['downloaded_text'] = $this->get_downloaded_text($v['id']);
            $rows[$k]['uploaded_text'] = $this->get_uploaded_text($v['id']);
        }
        return $rows;
    }

    #------------------------------------------------------------------------------------------------

    function get_downloaded_text($order_id){
        $query = $this->db->query('SELECT count(*) as `count`, sum(downloaded) as downloaded FROM lib_orders_items WHERE marked=0 AND order_id='.$order_id);
        $res = $query->result_array();
        if(count($res)){
            return $res[0]['downloaded'].' of '.$res[0]['count'];
        }
        return '0 of 0';
    }

    #------------------------------------------------------------------------------------------------

    function get_uploaded_text($order_id){
        $query = $this->db->query('SELECT count(*) as `count`, sum(uploaded) as uploaded FROM lib_orders_items WHERE marked=0 AND order_id='.$order_id);
        $res = $query->result_array();
        if(count($res)){
            return $res[0]['uploaded'].' of '.$res[0]['count'];
        }
        return '0 of 0';
    }

    #------------------------------------------------------------------------------------------------

    function set_review_status($id, $val){
        $this->db_master->query('UPDATE lib_orders SET review_status=\''.$val.'\' WHERE id=\''.$id.'\'');
    }

    #------------------------------------------------------------------------------------------------

    function set_order_star($id, $val){
        $this->db_master->query('UPDATE lib_orders SET star=\''.$val.'\' WHERE id=\''.$id.'\'');
    }

    #------------------------------------------------------------------------------------------------

    function reset_download($id){
        $this->db_master->query('UPDATE lib_orders_items SET downloaded=0 WHERE id=\''.$id.'\'');
    }

    #------------------------------------------------------------------------------------------------

    function rate_quote($id,$status=0){
        $this->db_master->query('UPDATE lib_orders SET rate_quote='.$status.' WHERE id=\''.$id.'\'');
    }

    #------------------------------------------------------------------------------------------------

    function delete_invoices($ids)
    {
        if(count($ids)){
            foreach($ids as $id){
                $this->db_master->delete('lib_orders', array('id'=>$id));
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function delete_invoices_items($ids,$orderId,$sum,$delivery_cost,$total){
        if(count($ids) && !empty($orderId)){
            // delete items
            foreach($ids as $id){
                $this->db_master->delete('lib_orders_items', array('id'=>$id));
            }
            // update cash
            $this->db_master->where('id', $orderId);
            $this->db_master->update('lib_orders', array('sum'=>$sum,'delivery_cost'=>$delivery_cost,'total'=>$total));
        }
    }

    #------------------------------------------------------------------------------------------------

    function approve_invoices($ids)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_orders set approve = !approve where id=' . $id);
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function change_payment_status($ids)
    {
        //prepare_downloads
        if(is_array($ids)){
            if (count($ids)) {
                foreach ($ids as $id) {
                    $rows = $this->db->query('SELECT status FROM lib_orders WHERE id = ' . $id)->result_array();
                    $status = $rows[0]['status'];
                    if($status == 1){
                        $this->db_master->query('UPDATE lib_orders SET status = 3 WHERE id = ' . $id);
                        $this->clm->OrderLogger($id,Clips_model::CLIP_ACTION_ORDERED);
                        //$this->prepare_downloads($id);
                        //Now it is doing by TaskMonitor.php on cronjob
                    }
                    else{
                        $this->db_master->query('UPDATE lib_orders SET status = 1 WHERE id = ' . $id);
                    }
                }
            }
        }
        else{
            $this->db_master->query('UPDATE lib_orders set status = IF(status=1,3,1) where id=' . $ids);
        }

    }

    function set_payment_status($ids, $status = 1){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('status' => $status));
                if($status == 3){
                    $this->clm->OrderLogger($id,Clips_model::CLIP_ACTION_ORDERED);
                    //Now it is doing by TaskMonitor.php on cronjob
                    //$this->prepare_downloads($id);
                }
                $order = $this->get_order($id);
                $this->make_pdf($order,true);
            }
        }
    }

    function change_admin_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('admin_status' => $status));
            }
        }
    }

    function change_client_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('client_status' => $status));
            }
        }
    }

    function change_imported_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('imported_status' => $status));
            }
        }
    }

    function change_release_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('release_status' => $status));

                if ($status == 'Approved'){
                    $this->db->select('status');
                    $this->db->where('id', $id);
                    $last_status = $this->db->get('lib_orders');

                    $row = $last_status->row_array();

                    if ($row['status'] == 1){
                        $this->db_master->where('id', $id);
                        $this->db_master->update('lib_orders', array('status' => 5));
                    }
                }
            }
        }
    }

    function change_download_email_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('download_email_status' => $status));
            }
        }
    }

    function change_resume_order_email_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('resume_order_email_status' => $status));
            }
        }
    }

    function change_invoice_email_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('invoice_email_status' => $status));
            }
        }
    }

    function change_delivery_process($ids, $delivery_process = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders_items', array('delivery_process' => $delivery_process));
            }
        }
    }

    function change_upload_status($ids, $status = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders_items', array('upload_status' => $status, 'uploaded' => ($status == 'Uploaded' ? 1 : 0)));
            }
        }
    }

    function change_notes($ids, $notes = ''){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('notes' => $notes));
            }
        }
    }

    function change_sales_rep($ids, $sales_rep_id){
        if(is_array($ids) && count($ids)){
            foreach ($ids as $id) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_orders', array('sales_rep' => $sales_rep_id));
            }
        }
    }
    #------------------------------------------------------------------------------------------------

    function set_prices($prices)
    {
        if (count($prices)) {
            foreach ($prices as $id=>$items_prices) {
                if(count($items_prices)){
                    foreach($items_prices as $item_id=>$item_price){
                        $this->db_master->query('UPDATE lib_orders_items set price = ' . (float)$item_price . ' where id=' . $item_id);
                    }
                }
            }
            $this->recalc_invoice($id);
        }
    }

    function set_labs($labs)
    {
        if (count($labs)) {
            foreach ($labs as $items_labs) {
                if(count($items_labs)){
                    foreach($items_labs as $item_id=>$item_lab){
                        $this->db_master->query('UPDATE lib_orders_items set lab = \'' . $item_lab . '\' where id=' . $item_id);
                    }
                }
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function recalc_invoice($id)
    {
        $invoice = $this->get_invoice($id);

        if(count($invoice['items'])){
            $price = 0;
            foreach($invoice['items'] as $item) {
                $price += $this->api->price_format($item['price']);
            }
            $sum = $this->api->price_format($price);

            $total = $sum;

            if($invoice['discount']) {
                $total -= $total * $invoice['discount'] / 100;
            }
            if($invoice['delivery_cost']){
                $total += $invoice['delivery_cost'];
            }
            if($invoice['vat']) {
                $total = $this->api->price_format($total + $total * $invoice['vat'] / 100);
            }

            $data['sum'] = $sum;
            $data['total'] = $total;
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_orders', $data);

        }
    }

    #------------------------------------------------------------------------------------------------

    function get_usage_string($str) {
        if (!$str) {
            return 'Royalty free';
        }

        $usage = unserialize($str);
        $usage = $this->db->query(
            'SELECT name FROM lib_rm WHERE id IN(' . implode(', ', $usage) . ') ORDER BY type'
        )->result_array();
        $str = 'Usage: ' . $usage[0]['name'] . '<br>'
            . 'Territory: ' . $usage[1]['name'] . '<br>'
            . 'Time: ' . $usage[2]['name'];
        return $str;
    }

    #------------------------------------------------------------------------------------------------

    function get_invoice($id, $lang='en')
    {
        $this->load->model('deliveryoptions_model');
        $query = $this->db->query('select lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lc.fname,\' \',lc.lname) as customer, lc.email, lc.login, \'$\' currency from lib_orders lo LEFT JOIN lib_users as lc ON lo.client_id=lc.id WHERE lo.id='.$id);

        $rows = $query->result_array();
        $invoice = $rows[0];

        $items = $this->db->query('SELECT oi.*, /*fr.start_time, fr.end_time*/ do.description df,do.delivery, lpu.clip_minimum
          FROM lib_orders_items oi
          /*LEFT JOIN lib_formats f ON f.id = oi.df_id*/
          LEFT JOIN lib_delivery_options do ON oi.df_id = do.id
          LEFT JOIN lib_pricing_use lpu ON oi.license_use = lpu.id
          /*LEFT JOIN lib_fragments fr ON oi.fragment_id = fr.id*/
          WHERE order_id = ?', array($id))->result_array();

        $rights = array(1=>'RF', 2=>'RM', 3 => 'PR');

        foreach ($items as $k => $v) {

            $data = $this->clm->get_clip($v['item_id']);
            $items[$k]['thumb'] = $this->clm->get_clip_path($data['id'], 'thumb');
            $items[$k]['preview'] = $this->clm->get_clip_path($data['id'], 'preview');

            $items[$k]['code'] = strtoupper($data['code']);
            $items[$k]['rights'] = $rights[$data['license']];
            $items[$k]['license'] = $data['license'];
            $clip = $this->clm->get_clip_info($data['id'], 'en', true);
            if($data['license'] == 2) {

                if($clip['brand'] == 1)
                    $invoice['with_rm'] = true;
                elseif ($clip['brand'] == 2)
                    $invoice['with_nf'] = true;

                $invoice['license_category'] = $v['license_category'];
                $invoice['license_use'] = $v['license_use'];
                $invoice['license_term'] = $v['license_term'];
            }
            elseif($data['license'] == 1) {
                $invoice['with_rf'] = true;
                $invoice['rf_license_term'] = $v['license_term'];
            }
            $items[$k]['price'] = $this->api->price_format($v['price']);

            if (!$v['duration']) {
                $items[$k]['duration'] = (float)$data['duration'];
            }

            if($data['license'] == 2) {
                $price = (float)$v['price'] * (float)$v['duration'];
                $price = ceil($price);
                $rounder = 5;
                if ($price % $rounder != 0) {
                    $price = $price + ($rounder - ($price % $rounder));
                }
                $items[$k]['total_price'] = $price;
            }
            else {
                $items[$k]['total_price'] = (float)$v['price'];
            }

            if(($v['discount'] > 0) AND ($v['discount'] != 100)){
                $items[$k]['old_total_price'] =  (float)$items[$k]['total_price'];
                $items[$k]['total_price'] =  (float)$items[$k]['total_price'] -  (float)$items[$k]['total_price'] / 100 * (float)$v['discount'];
                $items[$k]['base_price'] =  (float)$items[$k]['price'] * 100 / (100 - $v['discount']);
            }
            else {
                $items[$k]['base_price'] =  (float)$items[$k]['price'];
            }

            $items[$k]['delivery_formats'] = array();
            $delivery_methods = $this->deliveryoptions_model->get_delivery_formats($v['item_id']);
            if ($delivery_methods) {
                foreach($delivery_methods as $method) {
                    foreach ($method['formats'] as $format) {
                        $items[$k]['delivery_formats'][$format['id']] = $format;
                    }
                }
            }

        }

        $invoice['ref'] = $this->api->order_format($invoice['id']);
        $invoice['status_text'] = (
            is_numeric($invoice['status'])
            ? ucfirst($this->lang->line('order_status_'.$invoice['status']))
            : $invoice['status']
        );
        $invoice['approve_status_text'] = $invoice['approve'] ? 'Approved' : 'Not approved';
        $invoice['items'] = $items;

        $invoice['discount_abs'] = number_format(
            (float)$invoice['sum'] * (float)$invoice['discount'] / 100, 2, '.', '');
        $invoice['nett'] = (float)$invoice['sum'] - (float)$invoice['discount_abs'] + (float)$invoice['delivery_cost'];
        $invoice['vat_abs'] = number_format(
            (float)$invoice['nett'] * (float)$invoice['vat'] / 100, 2, '.', '');

        return $invoice;
    }

    #------------------------------------------------------------------------------------------------

    function get_sales_count($client_id) {
        $row = $this->db->query(
            'SELECT COUNT(1) total
        FROM lib_orders_items oi
        INNER JOIN lib_orders o ON o.id = oi.order_id AND o.status = 3
        INNER JOIN lib_clips c ON c.id = oi.item_id AND c.client_id = ?',
            $client_id)->result_array();
        return $row[0]['total'];
    }

    #------------------------------------------------------------------------------------------------

    function get_sales_stat($client_id, $limit='', $period='') {

        $this->load->model('editors_model', 'em');

        $query = $this->db->query(
            'SELECT oi.item_type, oi.item_id, i.code, oi.price, oi.percent, c.currency, o.ctime, i.client_id
        FROM lib_orders_items oi
        INNER JOIN lib_orders o ON o.id = oi.order_id AND o.status = 3
        INNER JOIN lib_users u ON u.id = o.client_id
        INNER JOIN lib_countries c ON c.id = u.country_id
        INNER JOIN lib_images i ON i.id = oi.item_id
        WHERE oi.item_type = 1 AND i.client_id = ? ' . $period . '
        UNION
        SELECT oi.item_type, oi.item_id, i.code, oi.price, oi.percent, c.currency, o.ctime, i.client_id
        FROM lib_orders_items oi
        INNER JOIN lib_orders o ON o.id = oi.order_id AND o.status = 3
        INNER JOIN lib_users u ON u.id = o.client_id
        INNER JOIN lib_countries c ON c.id = u.country_id
        INNER JOIN lib_clips i ON i.id = oi.item_id
        WHERE oi.item_type = 2 AND i.client_id = ? ' . $period  . $limit
            , array($client_id, $client_id));

        $result = $query->result_array();

        return $query->result_array();
    }

    #------------------------------------------------------------------------------------------------

    function get_inquiries() {
        $inquires = $this->db->query('SELECT * FROM lib_inquiries ORDER BY id DESC')->result_array();
        return $inquires;
    }

    #------------------------------------------------------------------------------------------------

    function del_inquiry($id) {
        $this->db_master->delete('lib_inquiries', array('id'=>$id));
    }

    /*
     * @deprecated - Now it is doing by TaskMonitor.php on cronjob
     */
    function prepare_downloads($order_id = '') {
        $command = 'php ' . $_SERVER['DOCUMENT_ROOT']
            . '/scripts/prepare_downloads.php ' . $order_id . ' > /dev/null &';
        system($command);
    }

    function confirm_pay($id) {
        $this->db_master->update('lib_orders', array('status' => 3, 'approve' => 1), array('id'=>$id));
        //$this->prepare_downloads($id);
        //Now it is doing by TaskMonitor.php on cronjob
    }

    function make_pdf($order, $to_file = false) {
        $this->load->model('aws_model');
        if(!is_dir(realpath(__DIR__ . '/../..') . '/data/upload/invoices')) mkdir(realpath(__DIR__ . '/../..') . '/data/upload/invoices',0777);
	    $pdf_file = realpath(__DIR__ . '/../..') . '/data/upload/invoices/invoice-' . $order['id'] . '.pdf';
        if (is_file($pdf_file)) {
            unlink($pdf_file);
        }
        $html = $this->load->view('invoices/pdf', $order, true);
        require_once($_SERVER['DOCUMENT_ROOT'] . '/application/libraries/MPDF57/mpdf.php');
        $pdf = new mPDF('c','A4','','',15,15,20,20);
        $pdf->mirrorMargins = 1;
        //$pdf->SetDisplayMode('fullpage');
        $pdf->SetHTMLHeader($this->load->view('invoices/pdf_header', $order, true));
        $pdf->SetHTMLHeader($this->load->view('invoices/pdf_header_e', $order, true), 'E');
        $pdf->SetHTMLFooter($this->load->view('invoices/pdf_footer', array(), true));
        $pdf->SetHTMLFooter($this->load->view('invoices/pdf_footer_e', array(), true),'E');
        $pdf->WriteHTML($html);
        if ($to_file) {
            $pdf->Output($pdf_file, 'F');
        } else {
            $pdf_file = basename($pdf_file);
            $pdf->Output($pdf_file, 'D');
        }
        if ($to_file) {
            $file=$this->aws_model->upload($pdf_file,'invoices',null,array('CacheControl'=>'no-store, no-cache, must-revalidate, max-age=0'),'order_pdf');
            $file=preg_replace('/https:\/\/s3.amazonaws.com\/s3.naturefootage.com/im','http://s3.naturefootage.com',$file);
            unlink($pdf_file);
            return $file;//$pdf_file;
        }
    }

    function is_approved($order_id){

        $query = $this->db->query("SELECT release_status FROM lib_orders WHERE id = '".$order_id."' LIMIT 1 ")->result_array();

        foreach ($query as $row){
            $status = $row['release_status'];
        }

        if($status == 'Not approved'){
            return false;
        }else{
            return true;
        }

    }

    function save_invoice($provider_id, $data){
//        echo '<pre>';
//        print_r($data);
//        exit();
        $order = false;

        //$data['license_data']['production_title'] = $this->db_master->escape($data['license_data']['production_title']);
        //$data['production_description'] = $this->db->escape($data['production_description']);
        //$data['special_instructions'] = $this->db->escape($data['special_instructions']);

        if($data['user'] && $data['items']){
            $this->load->model('customers_model');
            $this->load->model('clips_model');
            $this->load->model('pricing_model');
            $this->load->model('licensing_model');
            $this->load->model('deliveryoptions_model');
            $this->load->model('discounts_model');
            $this->load->model('upload_tokens_model');
            $user_id = $this->customers_model->get_customer_id_by_login($data['user'], $provider_id);
            $sum = 0;
            $delivery_cost = 0;
            $total = 0;

            $rf_clips_count = 0;
            $min_duration = false;
            $total_duration = 0;
            $total_rm_duration = 0;
            $total_nf_duration = 0;

            foreach($data['items'] as &$item){
                $item['license'] = $this->clips_model->get_license($item['id']);
                if($item['license'] == 1){
                    $rf_clips_count++;
                }
                if($item['license'] == 2){

                    if(!$item['license_duration']){
                        $license_use = $this->pricing_model->get_license_use_by_id($item['license_use']);
                        $min_duration = $license_use['clip_minimum'];
                        $duration = $this->clips_model->get_duration($item['id']);
                        $item['license_duration'] = $duration > 0 ? $duration : 1;
                        if ($min_duration && $min_duration > $item['license_duration']) {
                            $item['license_duration'] = $min_duration;
                        }
                    }
                    $clip = $this->clm->get_clip_info($item['id'], 'en', true);
                    if($clip['brand'] == 1) {
                        $total_rm_duration += $item['license_duration'];
                    } else {
                        $total_nf_duration += $item['license_duration'];
                    }
                    $total_duration += $item['license_duration'];
                }
            }

            foreach($data['items'] as $key => &$item){
                $discount = false;
                $item['price'] = 0;
                if($item['license'] == 1 && $item['license_term']){
                    $license_use = $this->pricing_model->get_rf_license_use($item['license_term'], $item['id']);
                    if($license_use){
                        $item['price'] = $license_use['price'];
                        $item['license_use_description'] = $license_use['terms'];
                    }
                }
                elseif($item['license'] != 1 && $item['license_term'] && $item['license_use']){
                    $license_term = $this->pricing_model->get_license_term_by_id($item['license_term']);
                    $license_use = $this->pricing_model->get_license_use_by_id($item['license_use']);
                    $item['restrictions'] = $this->licensing_model->get_restrictions($item['license_use'], '');
                    $item['license_use_description'] = $license_use['description'] .= '; ' . $license_term['territory'] . ' ' . $license_term['term'] . '.';
                    //if ($license_use['display']) {
                    $item['price'] = $this->pricing_model->get_clip_price($item['id'], $item['license_use'], $item['license_term']);
                    //}
                }

                //Delivery formats
                if($item['delivery_format']){
                    $delivery_format = $this->deliveryoptions_model->get_delivery_option($item['delivery_format'], $item['id'], $item['delivery_frame_rate']);
                    if($delivery_format){
                        $item['delivery_cost'] = isset($delivery_format['price']) ? $delivery_format['price'] : 0;
                        $item['delivery_format_description'] = $delivery_format['description'];
                        if(isset($delivery_format['delivery_factor']) && $delivery_format['delivery_factor']){
                            $item['price'] *= $delivery_format['delivery_factor'];
                        }
                        if($delivery_format['lab_id']){
                            $data['items'][$key]['generate_upload_token'] = true;
                            $data['items'][$key]['lab_id'] = $delivery_format['lab_id'];
                            $data['items'][$key]['login'] = $data['user'];
                        }
                    }
                }

                if($item['license'] == 1){
                    if($rf_clips_count > 0){
                        $discount = $this->discounts_model->get_count_discount($rf_clips_count);
                        $count_discount = $discount['discount'];
                    }
                    if($discount){
                        $item['discount'] = $discount['discount'];
                        if ($item['price']) {
                            $new_price = $item['price'] - $item['price'] / 100 * $discount['discount'];
                            $sum += $new_price;
                        }
                    }
                    else{
                        if ($item['price']) {
                            $sum += $item['price'];
                        }
                    }
                }
                else{
//                  get discount value for item with discount type
                    $clip = $this->clm->get_clip_info($item['id'], 'en', true);
                    if($clip['brand'] == 1) {
                        $discount = $this->discounts_model->get_duration_discount($total_rm_duration, $item['discount_type']);
                    } else {
                        $discount = $this->discounts_model->get_duration_discount($total_nf_duration, $item['discount_type']);
                    }

                    if ($item['price']) {
                        $price = $item['price'] * $item['license_duration'];
                        $price = ceil($price);
                        $rounder = 5;
                        if ($price % $rounder != 0) {
                            $price = $price + ($rounder - ($price % $rounder));
                        }
                        if($discount){
                            $item['discount'] = $discount['discount'];
                            $new_price = $price * ((100 - $discount['discount'])/100);
                            $sum += $new_price;
                        }
                        else{
                            $sum += $price;
                        }
                    }
                }


                if(isset($item['delivery_cost'])){
                    $delivery_cost += $item['delivery_cost'];
                }
            }

            $total = $sum + $delivery_cost;
            if($user_id){
                $userGroup = $this->db->query("SELECT group_id FROM lib_users WHERE id = ".$user_id." LIMIT 1")->result_array();

                if($userGroup[0]['group_id'] == 1){
                    $order = array(
                        'client_id' => $user_id,
                        'sum' => $sum,
                        'delivery_cost' => $delivery_cost,
                        'count_discount' => isset($count_discount) && $count_discount ? $count_discount : 0,
                        'total' => $total,
                        'admin_status' => 'Fillout',
                        'status' => 1,
                        'ctime' => date('Y-m-d H:i:s'),
                        'frontend_id' => isset($data['frontend_id']) ? $data['frontend_id'] : 0,
                        'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
                        'special_instructions' => isset($data['special_instructions']) ? $data['special_instructions'] : '',
                        'rate_quote' => isset($data['rate_quote']) ? 1 : 0,
                        'ip' => $data['user_ip']
                    );
                }else{

                    switch ($data['payment_method']){
                        case "Check":
                            $admin_status = 'Accepted online';
                            break;

                        case "Wire Transfer":
                            $admin_status = 'Accepted online';
                            break;

                        default:
                            $admin_status = '';
                            break;
                    }

                    $order = array(
                        'client_id' => $user_id,
                        'sum' => $sum,
                        'delivery_cost' => $delivery_cost,
                        'count_discount' => isset($count_discount) && $count_discount ? $count_discount : 0,
                        'total' => $total,
                        'admin_status' => $admin_status,
                        'status' => 1,
                        'ctime' => date('Y-m-d H:i:s'),
                        'frontend_id' => isset($data['frontend_id']) ? $data['frontend_id'] : 0,
                        'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
                        'special_instructions' => isset($data['special_instructions']) ? $data['special_instructions'] : '',
                        'rate_quote' => isset($data['rate_quote']) ? 1 : 0,
                        'ip' => $data['user_ip']
                    );
                }
                $this->db_master->insert('lib_orders', $order);
                $order_id = $this->db_master->insert_id();
                $user_template = 'touser-new-order';
                $admin_template = 'toadmin-order-completed';
                sleep(1);
                if($order_id){
                    $invoice = $this->get_invoice($order_id);
                    $this->load->helper('Emailer');
                    //$pdf_link = site_url().'data/upload/invoices/invoice-'.$order_id.'.pdf';
                    $pdf_link = (!empty($order['rate_quote']))?' Rate Quote ':Invoices_model::ORDER_PDF_PATH.$order_id.'.pdf';
                    $order_mail = $this->get_order($order_id);
                    switch ($data['payment_method']){
                        case "Check":
                        case "Credit Card":
                        case "Wire Transfer":
                            $this->send_order_confirmation($order_id);
                            break;

                        default:
                            $emailer = Emailer::In();
                            $emailer->LoadTemplate($admin_template);
                            $emailer->TakeSenderSystem();
                            $emailer->SetRecipientEmail('orders@naturefootage.com');
                            $emailer->SetTemplateValue('order', $order_mail);
                            $emailer->SetTemplateValue('pdf','link',$pdf_link);
                            $emailer->SetMailType('html');
                            //$emailer->Attach($invoice_file);
                            $emailer->Send();
                            $emailer->Clear();
                            break;
                    }

                    foreach($data['items'] as $order_item){
                        $item_data = array();
                        $item_data['order_id'] = $order_id;
                        $item_data['item_type'] = 2;
                        $item_data['item_id'] = $order_item['id'];
                        $item_data['df_id'] = $order_item['delivery_format'];
                        $item_data['dm_id'] = $order_item['delivery_method'];
                        if(isset($order_item['delivery_cost']))
                            $item_data['d_price'] = $order_item['delivery_cost'];
                        $item_data['allowed_use'] = $order_item['license_use_description'];
                        if ($order_item['license'] == 2)
                            $item_data['restrictions'] = $order_item['restrictions'];
                        $item_data['df_description'] = $order_item['delivery_format_description'];
                        $item_data['price'] = $order_item['price'];
                        $item_data['license_category'] = $order_item['license_category'];
                        $item_data['license_use'] = $order_item['license_use'];
                        $item_data['license_term'] = $order_item['license_term'];
                        if ($order_item['license'] == 1)
                            $item_data['license_use'] = $order_item['license_term'];
                        if(isset($order_item['license_duration']))
                            $item_data['duration'] = $order_item['license_duration'];
                        if(isset($order_item['delivery_frame_rate']))
                            $item_data['frame_rate_id'] = $order_item['delivery_frame_rate'];
                        if(isset($order_item['discount']))
                            $item_data['discount'] = $order_item['discount'];

                        $this->db_master->insert('lib_orders_items', $item_data);

                        if(isset($order_item['generate_upload_token']) && $order_item['generate_upload_token']){

                            $path = '/' . $data['user'] . '/order' . $order_id;
                            $upload_token_id = $this->upload_tokens_model->generate_token($order_id, $path, $order_item['lab_id'], $order['frontend_id']);
                            $this->send_upload_token($upload_token_id);
                        }
                    }

                    $order['id'] = $order_id;


                    if(isset($data['license_data']) && $data['license_data']){
                        $data['license_data']['production_title'] = $data['license_data']['production_title']
                            ? $data['license_data']['production_title'] : '';
                        $data['license_data']['production_description'] = $data['license_data']['production_description']
                            ? $data['license_data']['production_description'] : '';
                        $data['license_data']['production_territory'] = $data['license_data']['production_territory']
                            ? $data['license_data']['production_territory'] : '';
                        $data['license_data']['order_id'] = $order_id;
                        $this->db_master->insert('lib_order_license', $data['license_data']);
                    }

                    if(isset($data['shipping_data']) && $data['shipping_data']){
                        $data['shipping_data']['order_id'] = $order_id;
                        $this->db_master->insert('lib_order_shipping', $data['shipping_data']);
                    }

                    if(isset($data['billing_data']) && $data['billing_data']){
                        $data['billing_data']['order_id'] = $order_id;
                        $this->db_master->insert('lib_order_billing', $data['billing_data']);
                    }
                    //$this->send_order_download($order_id);
                    return $order;
                }
            }
        }

        return $order;
    }

    function find_diff($order_id, $data){
        $order_data = $this->get_order($order_id);
        //print_r(array_diff($order_data, $data));

        //print_r($order_data);

        //check license information


        foreach($data['license'] as $key => $value){
            $license = $this->db->query("SELECT id FROM lib_order_license  WHERE
                                        id = '".$key."' AND
                                        production_title = '".$value['production_title']."' AND
                                        production_description = '".$value['production_title']."' AND
                                        additional_notes = '".$value['additional_notes']."'
                                        ");
        }


    }

    function update_invoice($order_id, $data) {
        $this->db->select('oi.id, oi.item_id, oi.d_price, oi.duration, oi.price, oi.discount, c.license');
        $this->db->from('lib_orders_items oi');
        $this->db->join('lib_clips c', 'oi.item_id = c.id');
        $this->db->where('oi.order_id', $order_id);
        $this->db->where('oi.marked', 0);
        $query = $this->db->get();

        $order_owner_id = $this->db->query("SELECT client_id FROM lib_orders WHERE id = '".$order_id."' LIMIT 1 ")->result_array();
        $order_owner = $this->um->get_user_by_id($order_owner_id[0]['client_id']);
        if(empty($data['client_id'])) $data['client_id']=$order_owner_id[0]['client_id'];
        if ($order_owner_id[0]['client_id'] != $data['client_id']){
            $client = $this->um->get_user_by_id($data['client_id']);
            $original_path = "/".$order_owner['login']."/order".$order_id;
            $destination_path = "/".$client['login']."/order".$order_id;

            $this->db_master->query("INSERT INTO lib_dirs_to_move(id, original_path, destination_path)
               VALUES ('', '".$original_path."', '".$destination_path."' )");
        }

        $order_items = $query->result_array();
        $order = array('sum' => 0, 'delivery_cost' => 0, 'total' => 0);
        foreach ($order_items as $item) {
            if (isset($data['items'][$item['id']])) {
                if (isset($data['items'][$item['id']]['duration'])) {
                    $item['duration'] = $data['items'][$item['id']]['duration'];
                }
                if (isset($data['items'][$item['id']]['discount'])) {
                    $item['discount'] = $data['items'][$item['id']]['discount'];
                }
                if (isset($data['items'][$item['id']]['delivery_format'])) {
                    $item['df_description'] = $data['items'][$item['id']]['delivery_format'];
                }
                if (isset($data['items'][$item['id']]['delivery_price'])) {
                    $item['d_price'] = $data['items'][$item['id']]['delivery_price'];
                }
                if (isset($data['items'][$item['id']]['base_price'])) {
                    if ($item['license'] == 2) {
                        $item['price'] = $data['items'][$item['id']]['base_price'] / $item['duration'];
                    }
                    else {
                        $item['price'] = $data['items'][$item['id']]['base_price'];
                    }
                    if ($item['discount']) {
                        //$item['price'] = $item['price'] - $item['price'] / 100 * $item['discount'];
                    }
                }
            }
            $clip = $this->clm->get_clip_info($item['item_id'], 'en', true);
            if($item['license'] == 2 && $clip['brand'] == 1) {
                if (isset($data['allowed_use'])) {
                    $item['allowed_use'] = $data['allowed_use'];
                }
                if (isset($data['restrictions'])) {
                    $item['restrictions'] = $data['restrictions'];
                }
                $price = $item['price'] * $item['duration'];
//                Check if clip is Nature Flix
            } elseif ($item['license'] == 2 && $clip['brand'] == 2) {
                if (isset($data['nf_allowed_use'])) {
                    $item['allowed_use'] = $data['nf_allowed_use'];
                }
                if (isset($data['nf_restrictions'])) {
                    $item['restrictions'] = $data['nf_restrictions'];
                }
                $price = $item['price'] * $item['duration'];
            } else {
                if (isset($data['rf_allowed_use'])) {
                    $item['allowed_use'] = $data['rf_allowed_use'];
                }
                $price = $item['price'];
            }

            if ($item['discount']) {
                $price = $price*((100 - $item['discount'])/100);
            }

            $order['sum'] += $price;
            $order['delivery_cost'] += $item['d_price'];
            $order['total'] += $price + $item['d_price'];

            unset($item['license']);
            $this->db_master->update('lib_orders_items', $item, array('id' => $item['id']));
        }

        //$order_owner_id = $this->db->query("SELECT client_id FROM lib_orders WHERE id = '".$order_id."' LIMIT 1 ")->result_array();

        if($order_owner_id[0]['client_id'] != $data['client_id'] ){
            foreach($this->um->get_administrators_list() as $value){
                if($value['id'] == $order_owner_id[0]['client_id']){
                    $this->change_admin_status(array($order_id), "Reassigned");
                }
            }
        }

        $order['restrictions'] = $data['restrictions'];

        if ($data['client_id'])
            $order['client_id'] = $data['client_id'];
        $this->db_master->update('lib_orders', $order, array('id' => $order_id));
        if (isset($data['license'])) {
            $triger=null;
            foreach ($data['license'] as $license_id => $license) {
                if(empty($license_id)){
                    $this->db_master->insert('lib_order_license', array('order_id'=>$order_id));
                    $triger=$this->db->get_where('lib_order_license',array('order_id'=>$order_id))->result_array();
                    $triger=$triger[0]['id'];
                }
                $license_id=(!empty($triger))?$triger:$license_id;
                $this->db_master->update('lib_order_license', $license, array('id' => $license_id));
            }
        }
        if (isset($data['billing'])) {
            $triger=null;
            foreach ($data['billing'] as $billing_id => $billing) {
                if(empty($billing_id)){
                    $this->db_master->insert('lib_order_billing', array('order_id'=>$order_id));
                    $triger=$this->db->get_where('lib_order_billing',array('order_id'=>$order_id))->result_array();
                    $triger=$triger[0]['id'];
                }
                $billing_id=(!empty($triger))?$triger:$billing_id;
                $this->db_master->update('lib_order_billing', $billing, array('id' => $billing_id));
            }
        }
        if (isset($data['shipping'])) {
            $triger=null;
            foreach ($data['shipping'] as $shipping_id => $shipping) {
                if(empty($shipping_id)){
                    $this->db_master->insert('lib_order_shipping', array('order_id'=>$order_id));
                    $triger=$this->db->get_where('lib_order_shipping',array('order_id'=>$order_id))->result_array();
                    $triger=$triger[0]['id'];
                }
                $shipping_id=(!empty($triger))?$triger:$shipping_id;
                $this->db_master->update('lib_order_shipping', $shipping, array('id' => $shipping_id));
            }
        }
        $this->make_pdf($this->get_order($order_id), true);
    }

//    function update_invoice($id, $data) {
////        echo '<pre>';
////        print_r($data);
////        exit();
//        $this->load->model('pricing_model');
//        $this->load->model('deliveryoptions_model');
//        $this->load->model('discounts_model');
//
//        $this->db->select('oi.id, oi.item_id, oi.df_id, oi.d_price, oi.duration, oi.price, oi.discount, c.license, c.duration clip_duration');
//        $this->db->from('lib_orders_items oi');
//        $this->db->join('lib_clips c', 'oi.item_id = c.id');
//        $this->db->where('oi.order_id', $id);
//        $query = $this->db->get();
//        $order_items = $query->result_array();
//
//        $rf_clips_count = 0;
//        $total_duration = 0;
//
//        foreach ($order_items as &$item) {
//            // Update fields from posted data
//            if (isset($data['delivery_formats'][$item['id']])) {
//                if ($item['df_id'] != $data['delivery_formats'][$item['id']]) {
//                    $item['delivery_format_changed'] = true;
//                }
//                $item['df_id'] = $data['delivery_formats'][$item['id']];
//            }
//            if (isset($data['delivery_prices'][$item['id']])) {
//                $item['d_price'] = $data['delivery_prices'][$item['id']];
//            }
//            if (isset($data['durations'][$item['id']])) {
//                $item['duration'] = $data['durations'][$item['id']];
//            }
//            if (isset($data['base_prices'][$item['id']])) {
//                $item['base_price'] = $data['base_prices'][$item['id']];
//            }
//            if (isset($data['discounts'][$item['id']])) {
//                $item['discount'] = $data['discounts'][$item['id']];
//            }
//
//            if ($item['license'] == 1) {
//                if (isset($data['rf_license_term'])) {
//                    $item['license_use'] = $data['rf_license_term'];
//                    $item['license_term'] = $data['rf_license_term'];
//                }
//            }
//            else {
//                if (isset($data['license_category'])) {
//                    $item['license_category'] = $data['license_category'];
//                }
//                if (isset($data['license_use'])) {
//                    $item['license_use'] = $data['license_use'];
//                }
//                if (isset($data['license_term'])) {
//                    $item['license_term'] = $data['license_term'];
//                }
//            }
//
//            // Complete empty fields
//            if (!$item['duration']) {
//                $item['duration'] = $item['clips_duration'] > 0 ? $item['clips_duration'] : 1;
//                if ($item['license'] == 2) {
//                    $license_use = $this->pricing_model->get_license_use_by_id($item['license_use']);
//                    $min_duration = $license_use['clip_minimum'];
//                    if ($min_duration && $min_duration > $item['duration']) {
//                        $item['duration'] = $min_duration;
//                    }
//                }
//            }
//
//            if ($item['license'] == 1) {
//                $rf_clips_count++;
//            }
//
//            if ($item['license'] == 2) {
//                $total_duration += $item['duration'];
//            }
//        }
//
//        // Recalculate order
//        $sum = 0;
//        $delivery_cost = 0;
//        $total = 0;
//
//        foreach ($order_items as &$item) {
//
//            if ($item['license'] == 1) {
//                if ($item['license_term']) {
//                    $license_use = $this->pricing_model->get_rf_license_use($item['license_term'], $item['id']);
//                    $item['allowed_use'] = $license_use['terms'];
//                }
//                if (isset($item['base_price'])) {
//                    if ($item['base_price'] <= 0 && $license_use) {
//                        $item['price'] = $license_use['price'];
//                    }
//                    else {
//                        $item['price'] = $item['base_price'];
//                    }
//                }
//            }
//            else {
//                if ($item['license_term'] && $item['license_use']) {
//                    $license_term = $this->pricing_model->get_license_term_by_id($item['license_term']);
//                    $license_use = $this->pricing_model->get_license_use_by_id($item['license_use']);
//                    $item['allowed_use'] = $license_use['description'] .= '; ' . $license_term['territory'] . ' ' . $license_term['term'] . '.';
//                    if (isset($item['base_price'])) {
//                        if ($item['base_price'] <= 0 && $license_use) {
//                            $item['price'] = $this->pricing_model->get_clip_price($item['item_id'], $item['license_use'], $item['license_term']);
//                        }
//                        else {
//                            $item['price'] = $item['base_price'];
//                        }
//                    }
//                }
//            }
//
//            // Delivery format
//            if ($item['df_id']) {
//                $delivery_format = $this->deliveryoptions_model->get_delivery_option($item['df_id'], $item['id']);
//                if ($delivery_format) {
//                    if (isset($item['delivery_format_changed'])) {
//                        $item['df_description'] = $delivery_format['description'];
//                    }
//                    if (isset($item['d_price']) && $item['d_price'] <= 0) {
//                        $item['d_price'] = isset($delivery_format['price']) ? $delivery_format['price'] : 0;
//                    }
//                    if (isset($delivery_format['delivery_factor']) && $delivery_format['delivery_factor']){
//                        //$item['price'] *= $delivery_format['delivery_factor'];
//                    }
//                }
//            }
//
//            // Discounts
//            if($item['license'] == 1){
//                if (isset($item['discount']) && $item['discount'] <= 0) {
//                    if ($rf_clips_count > 0) {
//                        $discount = $this->discounts_model->get_count_discount($rf_clips_count);
//                        $count_discount = $discount['discount'];
//                    }
//                }
//            }
//            else{
//                if (isset($item['discount']) && $item['discount'] <= 0) {
//                    $discount = $this->discounts_model->get_duration_discount($total_duration);
//                    if($discount){
//                        $item['discount'] = $discount['discount'];
//                    }
//                }
//            }
//            if ($item['discount'] > 0) {
//                $item['price'] = $item['price'] - $item['price'] / 100 * $item['discount'];
//            }
//
//            if ($item['license'] == 2) {
//                $sum += $item['price'] * $item['duration'];
//            }
//            else {
//                $sum += $item['price'];
//            }
//
//            if(isset($item['d_price'])){
//                $delivery_cost += $item['d_price'];
//            }
//        }
//
//        $order = array(
//            'sum' => $sum,
//            'delivery_cost' => $delivery_cost,
//            'count_discount' => isset($count_discount) && $count_discount ? $count_discount : 0,
//            'total' => $sum + $delivery_cost
//        );
//        $this->db_master->update('lib_orders', $order, array('id' => $id));
//
//        foreach ($order_items as $item) {
//            unset($item['base_price'], $item['license'], $item['clip_duration'], $item['delivery_format_changed']);
//            $this->db_master->update('lib_orders_items', $item, array('id' => $item['id']));
//        }
//    }

    /**
     * @param $token_id
     */
    function send_upload_token($token_id){
        $this->load->model('upload_tokens_model');
        $this->load->model('users_model');

        $token = $this->upload_tokens_model->get_token_with_link($token_id);
        if($token){
            $lab_users = $this->users_model->get_lab_users($token['lab_id']);
            $this->load->helper('Emailer');
            foreach($lab_users as $lab_user){
                if($lab_user['email']){
                    $emailer = Emailer::In();
                    $emailer->LoadTemplate('new-upload-token');
                    $emailer->TakeSenderSystem();
                    $emailer->SetRecipientEmail($lab_user['email']);
                    $emailer->SetTemplateValue('token_path', $token);
                    $emailer->SetMailType('html');
                    $emailer->Send();
                    $emailer->Clear();
                }
            }
        }
    }

    function get_invoices_by_client_login($login)
    {
        $this->load->model('download_model');
        $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                    WHERE lu.login = ? ORDER BY lo.id DESC', array($login));

        $rows = $query->result_array();
        $not_uploaded_orders = $this->download_model->get_not_uploaded_downloads_by_client($login);

        foreach($rows as $k=>$v){
            $rows[$k]['ref'] = $this->api->order_format($v['id']);
            $rows[$k]['status_text'] = (
                is_numeric($v['status'])
                ? $this->lang->line('order_status_'.$v['status'])
                : $v['status']
            );

            $this->db->select('id');
            $this->db->where(array('order_id' => $v['id'], 'status' => 1));
            $this->db->limit(1);
            $query = $this->db->get('lib_download_tokens');
            $res = $query->result_array();
            if($res && $res[0]['id']){
                $rows[$k]['token_id'] = $res[0]['id'];
            }

            if ($not_uploaded_orders && in_array($v['id'], $not_uploaded_orders)) {
                $rows[$k]['upload_status'] = 'Processing Order';
            }
            else {
                if($rows[$k]['release_status'] != 'Not approved'){
                    $rows[$k]['upload_status'] = 'Ready for Download';
                }
            }
        }
        return $rows;
    }

    function is_user_invoice_owner_by_login($login, $invoice_id){
        $query = $this->db->query('SELECT *
                                        FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                        WHERE lu.login = ? AND lo.id = ?', array($login, $invoice_id));
        return (bool)$query->num_rows;
    }

    function get_invoice_by_client_login($login, $invoice_id)
    {
        $order = false;
        $this->load->model('users_model');
        $user_id = $this->users_model->get_user_by_login($login);
        if ($user_id) {
            $order = $this->get_order($invoice_id);
            if ($order['client_id'] == $user_id) {
                return $order;
            }
        }
        return $order;
//        $is_provider = $this->users_model->get_provider_by_login($login);
//
//        if($login){
////            if($is_provider){
////                $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
////                                        FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
////                                        WHERE lu.provider_id = ? AND lo.id = ?', array($is_provider, $invoice_id));
////            }
////            else{
////                $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
////                                        FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
////                                        WHERE lu.login = ? AND lo.id = ?', array($login, $invoice_id));
////            }
//
//            $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
//                                        FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
//                                        WHERE lu.login = ? AND lo.id = ?', array($login, $invoice_id));
//        }
//        elseif($invoice_id){
//            $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
//                                        FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
//                                        WHERE lo.id = ?', array($invoice_id));
//        }
//
//        $rows = $query->result_array();
//        $order = $rows[0];
//
//        if($order['id']){
//            $order['ref'] = $this->api->order_format($order['id']);
//            $order['status_text'] = $this->lang->line('order_status_'.$order['id']);
//            $order_items = $this->db->query('
//                SELECT loi.*, lc.code, loi.df_description delivery_format
//                FROM lib_orders_items loi
//                LEFT JOIN lib_clips lc ON loi.item_id = lc.id
//                WHERE loi.order_id = ' . $order['id'])->result_array();
//
//            foreach($order_items as &$item){
//                if($item['duration'] > 0){
//                    $item['total_price'] = $item['price'] * $item['duration'];
//                }
//                else{
//                    $item['total_price'] = $item['price'];
//                }
//                if($item['discount'] > 0){
//                    $item['old_total_price'] = $item['total_price'];
//                    $item['total_price'] = $item['total_price'] - $item['total_price'] / 100 * $item['discount'];
//                }
//            }
//            $order['items'] = $order_items;
//        }
//        return $order;
    }

    function cancel_invoice($login, $invoice_id)
    {
        $this->load->model('users_model');
        $is_provider = $this->users_model->get_provider_by_login($login);
		
		

        if($is_provider){
            $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                    WHERE lu.provider_id = ? AND lo.id = ?', array($is_provider, $invoice_id));
        }
        else{
            $query = $this->db->query('SELECT lo.*, DATE_FORMAT(lo.ctime, \'%d.%m.%Y %T\') as ctime, concat(lu.fname,\' \',lu.lname) as customer
                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                    WHERE lu.login = ? AND lo.id = ?', array($login, $invoice_id));
        }

        $rows = $query->result_array();
        $order = $rows[0];

        if($order['id']){
            $this->db_master->where('id', $order['id']);
            $this->db_master->update('lib_orders', array('status' => 2));
            return true;
        }
        return false;
    }

    function approve_invoice($user_id, $invoice_id, $paid = null)
    {
//        $this->load->model('users_model');
//        $is_provider = $this->users_model->get_provider_by_id($user_id);
//
//        if($is_provider){
//            $query = $this->db->query('SELECT lo.id
//                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
//                                    WHERE lu.provider_id = ? AND lo.id = ?', array($is_provider, $invoice_id));
//        }
//        else{
//            $query = $this->db->query('SELECT lo.id
//                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
//                                    WHERE lu.id = ? AND lo.id = ?', array($user_id, $invoice_id));
//        }

        $query = $this->db->query('SELECT lo.id, lo.release_status
                                    FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                    WHERE lu.id = ? AND lo.id = ?', array($user_id, $invoice_id));

        $rows = $query->result_array();
        $order = $rows[0];

        if($order['id']){
            $userGroup = $this->db->query("SELECT group_id FROM lib_users WHERE id = ".$user_id." LIMIT 1")->result_array();
            // Share Download link + send email touser-order-downloads
            $dataOrder = array();
            if($order['release_status']=='Preapproved no payment'){
                $this->invoices_model->get_invoice_guest_download_link($order['id']);
                // reason: FSEARCH-1651
                //$paid = 'paid';
                $dataOrder=array('release_status' => 'Approved');
            }
            if($paid){
                $dataOrder=array('status' => 3, 'release_status' => 'Approved');
            }
            if ($userGroup[0]['group_id'] == 1){
                //$this->db_master->update('lib_orders', array('status' => 3, 'release_status' => 'Approved', 'admin_status' => 'Fillout'));
                $dataOrder['admin_status']='Fillout';
                $this->db_master->where('id', $order['id']);
                $this->db_master->update('lib_orders', $dataOrder);
            }else{
                //$this->db_master->update('lib_orders', array('status' => 3, 'release_status' => 'Approved', 'admin_status' => 'Accepted online'));
                $dataOrder['admin_status']='Accepted online';
                $this->db_master->where('id', $order['id']);
                $this->db_master->update('lib_orders', $dataOrder);
            }
            if($dataOrder['status'] == 3) $this->clm->OrderLogger($order['id'], Clips_model::CLIP_ACTION_ORDERED);
            $this->send_order_confirmation($order['id']);
            //$this->prepare_downloads($order['id']);
            //Now it is doing by TaskMonitor.php on cronjob
            return true;
        }
        return false;
    }

    function get_client_folder($client_id) {
        $folder = 'data/upload/users/' . md5($client_id
                . $this->config->item('user_folder_salt'));

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/' . $folder)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/' . $folder);
        }

        return $folder;
    }

    function save_transaction($data){
        $this->db_master->insert('lib_transactions', $data);
        $transaction_id = $this->db_master->insert_id();
        return $transaction_id;
    }

    function get_resume_link($id){
        $resume_link = '';
        $this->db->select('o.id, f.host_name');
        $this->db->from('lib_orders o');
        $this->db->join('lib_frontends f', 'o.frontend_id = f.id');
        $this->db->where('o.id', $id);
        $query = $this->db->get();
        $rows = $query->result_array();
        if($rows[0]['host_name'])
            $resume_link = 'https://' . $rows[0]['host_name'] . '/cart?action=resume&order_id=' . $rows[0]['id'];

        return $resume_link;
    }

    function get_invoice_guest_download_link($id){
        $link = '';
        $query = $this->db->query('SELECT o.id, o.frontend_id order_frontend_id, f.id frontend_id, f.host_name, u.login
            FROM lib_orders o
            INNER JOIN lib_users u ON o.client_id = u.id
            INNER JOIN lib_frontends f ON IF(o.frontend_id <> 0, o.frontend_id = f.id, u.provider_id = f.provider_id)
            WHERE o.id = ' . $id);
        $rows = $query->result_array();
        if($rows && $rows[0]['host_name'] && $rows[0]['login']){
            $query = $this->db->get_where('lib_download_tokens', array('order_id' => $rows[0]['id']));
            $res = $query->result_array();
            if($res && $res[0]['token']){
                $token = $res[0]['token'];
            }
            else{
                $path = '/' . $rows[0]['login'] . '/order' . $rows[0]['id'];
                $token = md5($path);
                $token_data = array('token' => $token, 'path' => $path, 'order_id' => $rows[0]['id']);
                $this->db_master->insert('lib_download_tokens', $token_data);
            }
            $link = 'http://' . $rows[0]['host_name'] . '/orders?action=downloads&token=' . $token;
        }
        return $link;
    }

    public function get_download_token_no_generate($id){
        $link = '';
        $query = $this->db->query('SELECT o.id, o.frontend_id order_frontend_id, f.id frontend_id, f.host_name, u.login
            FROM lib_orders o
            INNER JOIN lib_users u ON o.client_id = u.id
            INNER JOIN lib_frontends f ON IF(o.frontend_id <> 0, o.frontend_id = f.id, u.provider_id = f.provider_id)
            WHERE o.id = ' . $id);
        $rows = $query->result_array();
        if($rows && $rows[0]['host_name'] && $rows[0]['login']){
            $query = $this->db->get_where('lib_download_tokens', array('order_id' => $rows[0]['id']));
            $res = $query->result_array();
            if($res && $res[0]['token']){
                $token = $res[0]['token'];
                $link = 'http://' . $rows[0]['host_name'] . '/orders?action=downloads&token=' . $token;
            }
        }
        return $link;
    }

    public function get_download_page_generate($order_id){
        $link = '';
        $query = $this->db->query('SELECT o.id, o.frontend_id order_frontend_id, f.id frontend_id, f.host_name, u.login
            FROM lib_orders o
            INNER JOIN lib_users u ON o.client_id = u.id
            INNER JOIN lib_frontends f ON IF(o.frontend_id <> 0, o.frontend_id = f.id, u.provider_id = f.provider_id)
            WHERE o.id = ' . $order_id);
        $rows = $query->result_array();
        if($rows && $rows[0]['host_name'] && $rows[0]['login']){
            $link = 'http://' . $rows[0]['host_name'] . '/order-download?key=order' . $order_id;
        }
        return $link;
    }

    function delete_download_token_by_order_id($id){
        if($id){
            $this->db_master->where('order_id', $id);
            $this->db_master->delete('lib_download_tokens');
        }
    }

    function get_order($id, $sort=false,$by=false) {
        if(empty($sort) || empty($by)){
            $sort='code';$by='asc';
        }
        $this->db->select('*, DATE_FORMAT(ctime, (\'%m/%d/%Y\')) AS date');
        $this->db->where('id', $id);
        $query = $this->db->get('lib_orders');
        $rows = $query->result_array();
        $order = $rows[0];
        //echo 'order'; Debug::Dump($order);

        $this->db->select('fname, lname, email, login');
        $this->db->where('id', $order['client_id']);
        $query = $this->db->get('lib_users');
        $rows = $query->result_array();
        $order['user'] = $rows[0];

        $this->db->where('order_id', $id);
        $query = $this->db->get('lib_order_billing');
        $rows = $query->result_array();
        $order['billing'] = $rows[0];

        $this->db->where('order_id', $id);
        $query = $this->db->get('lib_order_shipping');
        $rows = $query->result_array();
        $order['shipping'] = $rows[0];

        $this->db->where('order_id', $id);
        $query = $this->db->get('lib_order_license');
        $rows = $query->result_array();
        $order['license'] = $rows[0];
        //echo 'license'; Debug::Dump($order['license']);
        $order['download_link'] = $this->get_download_token_no_generate($id);
        $order['download_page'] = $this->get_download_page_generate($id);

        $this->db->select('meta_key, meta_value');
        $query = $this->db->get_where('lib_users_meta', array('user_id' => $order['client_id']));
        $rows = $query->result_array();
        foreach($rows as $meta) {
            $order['user'][$meta['meta_key']] = $meta['meta_value'];
        }
        $this->db->distinct();
        //$this->db->select('oi.*, c.code, c.master_lab, c.license, c.duration clip_duration, cc.description, u.provider_credits, do.delivery as delivery_method, um.meta_value as company_name');
        //$this->db->select('oi.*, c.code, c.master_lab, c.license, c.price_level, c.duration clip_duration, cc.description, u.provider_credits, do.delivery as delivery_method, um.meta_value as company_name');
        $this->db->select('oi.*, c.code, c.master_lab, c.license, c.price_level, c.duration clip_duration, c.description, c.license_restrictions, u.provider_credits, do.delivery as delivery_method, um.meta_value as company_name');
        $this->db->from('lib_orders_items oi');
        $this->db->join('lib_clips c', 'oi.item_id = c.id', 'left');
        //$this->db->join('lib_clips_content cc', 'c.id = cc.clip_id', 'left');
        $this->db->join('lib_users u', 'c.client_id = u.id', 'left');
        $this->db->join('lib_delivery_options do', 'oi.df_id = do.id', 'left');
        $this->db->join('lib_users_meta um', 'c.client_id = um.user_id', 'left');
        $this->db->where('um.meta_key', 'company_name');
        $this->db->where('oi.marked', 0);
        $this->db->where('oi.order_id', $id);
        $this->db->order_by('c.'.$sort, $by);
        $query = $this->db->get();

        //file_put_contents('W:\domains\admin.uhdfootage.local\www\log.txt', $this->db->last_query());

        $items = $query->result_array();
        //echo 'Query:'.$query.' items:'; Debug::Dump($items);
        $this->load->model('licensing_model');
        foreach($items as &$item){
            $item['license_text'] = 'Rights Managed';
            $clip = $this->clm->get_clip_info($item['item_id'], 'en', true);
            if($item['license'] == 2 && $clip['brand'] == 1) {
                $order['with_rm'] = true;
                if(!isset($order['allowed_use'])) {
//                    $this->db->where('id', $item['license_use']);
//                    $query = $this->db->get('lib_pricing_use');
//                    $rows = $query->result_array();
//                    $order['allowed_use'] = $rows[0];
//
//                    $this->db->where('id', $item['license_term']);
//                    $query = $this->db->get('lib_pricing_terms');
//                    $rows = $query->result_array();
//                    $order['allowed_use']['territory'] = $rows[0]['territory'];
//                    $order['allowed_use']['term'] = $rows[0]['term'];
                    $order['allowed_use'] = $item['allowed_use'];
                }
                if (isset($item['restrictions']) && $item['restrictions'] != ''){
                    $order['restrictions'] = $item['restrictions'];
                } else {
                    $order['restrictions'] = $this->licensing_model->get_restrictions($item['license_use'], '');
                }
                $this->db->select('delivery');
                $this->db->where('id', $item['df_id']);
                $query = $this->db->get('lib_delivery_options');
                $rows = $query->result_array();
                if ($rows[0]) {
                    $item['delivery_method'] = $rows[0]['delivery'];
                }
//                Check if clip is Nature Flix
            } elseif ($item['license'] == 2 && $clip['brand'] == 2) {
                $item['license_text'] = 'Rights Managed NatureFlix';
                $order['with_nf'] = true;
                if(!isset($order['nf_allowed_use'])) {
                    $order['nf_allowed_use'] = $item['allowed_use'];
                }
                if (isset($item['restrictions']) && $item['restrictions'] != ''){
                    $order['nf_restrictions'] = $item['restrictions'];
                } else {
                    $order['nf_restrictions'] = $this->licensing_model->get_restrictions($item['license_use'], '');
                }
                $this->db->select('delivery');
                $this->db->where('id', $item['df_id']);
                $query = $this->db->get('lib_delivery_options');
                $rows = $query->result_array();
                if ($rows[0]) {
                    $item['delivery_method'] = $rows[0]['delivery'];
                }
            } elseif($item['license'] == 1) {
                $order['with_rf'] = true;
                $item['license_text'] = 'Royalty Free';
                if(!isset($order['rf_allowed_use'])) {
//                    $this->db->select('license, terms');
//                    $this->db->where('id', $item['license_term']);
//                    $query = $this->db->get('lib_rf_pricing');
//                    $rows = $query->result_array();
//                    $order['rf_allowed_use'] = $rows[0]['terms'];
                    $order['rf_allowed_use'] = $item['allowed_use'];
                }
                $this->db->select('delivery');
                $this->db->where('id', $item['df_id']);
                $query = $this->db->get('lib_rf_delivery_options');
                $rows = $query->result_array();
                if ($rows[0]) {
                    $item['delivery_method'] = $rows[0]['delivery'];
                }
            }
            $item['preview'] = $this->clm->get_clip_path($item['item_id'], 'preview');
            $item['thumb'] = $this->clm->get_clip_path($item['item_id'], 'thumb');
            $item['res'] = $this->clm->get_clip_path($item['item_id'], 'res');

            if($item['license'] == 2) {
                $price = round($item['price'] * $item['duration'], 2);
                $price = ceil($price);
                $rounder = 5;
                if ($price % $rounder != 0) {
                    $price = $price + ($rounder - ($price % $rounder));
                }
                $item['total_price'] = $price;
                $item['base_price'] = $price;
            }
            else {
                $item['total_price'] = $item['price'];
                $item['base_price'] = $item['price'];
            }

            if(($item['discount'] > 0) AND ($item['discount'] != 100)){
                $item['total_price'] = round($item['base_price'] * ((100 - $item['discount'])/100), 2);
                //$item['base_price'] = round($item['base_price'] * 100/(100 - $item['discount']), 2);
                //$item['base_price'] = round($item['total_price'] * (((100 + $item['discount']))/100), 2);
            }

            $item['total'] = $item['total_price'] + $item['d_price'];
        }

        $order['items'] = $items;
        $order['status_text'] = (
            is_numeric($order['status'])
            ? ucfirst($this->lang->line('order_status_' . $order['status']))
            : $order['status']
        );
        $settings = $this->api->settings();
        $order['licensor'] = array(
            'name' => $settings['invoice_name'],
            'position' => $settings['invoice_position'],
            'company' => $settings['invoice_company'],
            'federal_tax_id' => $settings['invoice_federal_tax_id']
        );

        $order['ref'] = $this->api->order_format($order['id']);

        return $order;
    }

    function has_order($order_id, $login){
        $query = $this->db->query('SELECT lo.id FROM lib_orders lo INNER JOIN lib_users lu on lo.client_id = lu.id
                                    WHERE lu.login = ? AND lo.id = ?', array($login, $order_id));
        return (bool)$query->num_rows();
    }

    function send_order_confirmation($order_id){
        $order = $this->get_order($order_id);
        $file = '/var/app/current/emaillog.txt';
        file_put_contents($file, "\r\n-----------------order--------------------\r\n", FILE_APPEND);
        file_put_contents($file, serialize($order), FILE_APPEND);
        /*$invoice = $this->im->get_invoice($this->id);
        $template_name = 'touser-invoice-details';
        if($this->input->post('get_template') && $this->input->is_ajax_request() ){
            $pdf_link = site_url().'data/upload/invoices/invoice-'.$this->id.'.pdf';
            $pdf_link = '<a href="'.$pdf_link.'">'.$pdf_link.'</a>';
            $error = false;
            $this->load->helper('emailer');
            $template = Emailer::GetInstance()->LoadTemplate($template_name)
                ->TakeSenderSystem()
                ->SetRecipientEmail($invoice['email'])
                ->SetTemplateValue('pdf', array('link'=>$pdf_link))
                ->SetMailType('html')
                ->GetParsedTemplate();
            Emailer::GetInstance()->Clear();*/
        if($order && $order['user']['email']) {
            $pdf_link = $this->make_pdf($order, true);
            //$pdf_link = site_url().'data/upload/invoices/invoice-'.$order_id.'.pdf';
            $this->load->helper('Emailer');
            $emailer = Emailer::In();
            $emailer->LoadTemplate('touser-new-order');
            $emailer->TakeSenderSystem();
            $emailer->SetRecipientEmail($order['user']['email']);
            $emailer->SetTemplateValue('order', $order);
            $emailer->SetTemplateValue('pdf','link',$pdf_link);
            $emailer->SetMailType('html');
            $emailer->Send();
                file_put_contents($file, "\r\n-----------------touser-new-order--------------------\r\n", FILE_APPEND);
                file_put_contents($file, $order['user']['email'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $order['user']['fname'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $order['user']['lname'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $order_id . "\r\n", FILE_APPEND);
                file_put_contents($file, $order['date'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $pdf_link . "\r\n", FILE_APPEND);
                file_put_contents($file, "\r\n-------------------------------------\r\n", FILE_APPEND);
            $emailer->Clear();
            // Admin send copy
            //$pdf_link = site_url().'data/upload/invoices/invoice-'.$order_id.'.pdf';
            $emailer = Emailer::In();
            $emailer->LoadTemplate('toadmin-order-completed');
            $emailer->TakeSenderSystem();
            $emailer->SetRecipientEmail('orders@naturefootage.com');
            $emailer->SetTemplateValue('order', $order);
            $emailer->SetTemplateValue('pdf','link',$pdf_link);
            $emailer->SetMailType('html');
            //$emailer->Attach($invoice_file);
            $emailer->Send();
                file_put_contents($file, "\r\n-----------------toadmin-order-completed--------------------\r\n", FILE_APPEND);
                file_put_contents($file, $order['user']['lname'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $order['user']['fname'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $order_id . "\r\n", FILE_APPEND);
                file_put_contents($file, $order['date'] . "\r\n", FILE_APPEND);
                file_put_contents($file, $pdf_link . "\r\n", FILE_APPEND);
                file_put_contents($file, "\r\n-------------------------------------\r\n", FILE_APPEND);
            $emailer->Clear();
        }
    }

    function send_clipbin($to,$body=''){

        $body=(empty($body))?'':$body;
        $this->load->helper('Emailer');
        $emailer = Emailer::In();
        $emailer->LoadTemplate('touser-clipbin-frontend');
        $emailer->TakeSenderSystem();
        $emailer->SetRecipientEmail($to);
        $emailer->SetTemplateValue('frontend','text',$body);
        $emailer->SetMailType('html');
        $emailer->Send();
        $emailer->Clear();

        return 'ok';
    }

    function prolongate_access($id){
        $this->db_master->query('UPDATE lib_orders SET access_expired = (NOW() + INTERVAL 14 DAY), needs_prolongation = 1 WHERE id=' . $id);
    }

    function expire_access($id){
        $this->db_master->query('UPDATE lib_orders SET access_expired = NOW() WHERE id=' . $id);
    }

    function get_order_prop_by_name($id, $prop_name){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_orders');
        $order = $query->result_array();
        if(count($order) > 0 && isset($order[0][$prop_name])){
            return $order[0][$prop_name];
        }
        else{
            return false;
        }
    }

    function get_lab_invoices_count(array $lab_ids){
        if(!empty($lab_ids)){
            $query = $this->db->query("
            SELECT * FROM lib_orders_items oi
            INNER JOIN lib_delivery_options do ON do.id=oi.df_id
            WHERE do.delivery='Lab' AND do.lab_id IN (" . implode(",", $lab_ids) . ") AND oi.uploaded = 0");
            return $query->num_rows();
        }
        return 0;
    }

    function get_lab_invoices_list(array $lab_ids){
        $res = array();
        if(!empty($lab_ids)){
            $query = $this->db->query("
            SELECT oi.*, o.ctime, f.host_name, ut.token as upload_token, ut.is_active as is_token_active
            FROM lib_orders_items oi
            INNER JOIN lib_upload_tokens ut ON oi.order_id=ut.order_id
            INNER JOIN lib_orders o ON o.id=oi.order_id
            INNER JOIN lib_frontends f ON o.frontend_id=f.id
            WHERE ut.lab_id IN (" . implode(",", $lab_ids) . ") "
                . "ORDER BY oi.order_id
            ");

            //var_dump($this->db->last_query());
            foreach($query->result_array() as $row){
                $res[$row['order_id']]['items'][] = $row;
                $res[$row['order_id']]['ctime'] = $row['ctime'];
                $res[$row['order_id']]['host_name'] = $row['host_name'];
                $res[$row['order_id']]['upload_token'] = $row['upload_token'];
                $res[$row['order_id']]['is_token_active'] = $row['is_token_active'];
            }
        }
        return $res;
    }

    function send_order_download($order_id,$force=false){
        $invoice = $this->get_invoice($order_id);
        //file_put_contents( FCPATH . '___rest.api.log', $invoice['email'], FILE_APPEND );
        if(!$force){
            if ($invoice['payment_method'] == 'Check')return false;
            foreach($invoice['items'] as $i){
                if($i['delivery'] == 'Transcoded' || $i['delivery'] == 'Lab')return false;
            }
        }

        $downloads=$this->get_download_page_generate($order_id);
        /*$emailer = Emailer::In();
        $emailer->LoadTemplate('touser-order-downloads');
        $emailer->TakeSenderSystem();
        $emailer->SetRecipientEmail($invoice['email']);
        $emailer->SetTemplateValue('downloads', 'links', $downloads);
        $emailer->SetMailType('html');
        $emailer->Send();
        $emailer->Clear();*/
        Emailer::GetInstance()->LoadTemplate('touser-order-downloads')
            ->TakeSenderSystem()
            ->SetRecipientEmail($invoice['email'])
            ->SetTemplateValue('downloads', 'links', $downloads)
            ->SetTemplateValue('order', $invoice)
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
        $this->change_download_email_status(array($invoice['id']), 'Sent');
        return true;
    }

    function get_invoice_owner($order_id){
        $owner_id = $this->db->query("SELECT client_id FROM lib_orders WHERE id = '".$order_id."' LIMIT 1")->result_array();
        return $owner_id[0]['client_id'];
    }

    function set_s3_sync($order_id){
        return $this->db_master->update('lib_orders',array('s3_dir'=>1,'s3_dirset'=>date("Y-m-d H:i:s")),array('id'=>$order_id));
    }

    function send_custom_email($template,$to_email,$name_data,$data){
        $this->load->helper('Emailer');
        $emailer = Emailer::In();
        $emailer->LoadTemplate($template);
        $emailer->TakeSenderSystem();
        $emailer->SetRecipientEmail($to_email);
        $emailer->SetTemplateValue($name_data, $data);
        $emailer->SetMailType('html');
        $emailer->Send();
        $emailer->Clear();
    }


}
