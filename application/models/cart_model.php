<?php
class Cart_model extends CI_Model {

    var $cart;
    var $rate;
    var $currency;
    var $settings;

    function Cart_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

        $this->load->model('images_model');
        $this->load->model('clips_model');
        $this->load->model('cats_model');
        //$this->load->model('formats_model');
        $this->load->model('currencies_model');
        $this->load->model('delivery_model');
        $this->load->model('discounts_model');
        $this->load->model('invoices_model');
        $this->load->model('register_model');
        //$this->load->library('sagepay');
        //$this->load->library('caller');
        $this->load->model('fragments_model');
        $this->load->model('timeline_model');

        $this->cart = $this->session->userdata('cart');
        $this->settings = $this->api->settings();
    }

    #------------------------------------------------------------------------------------------------

    function add_item($type, $id, $usage = '', $start_time = null, $end_time = null) {
        if(!$this->check_exist($type, $id, $start_time, $end_time)) {
            $temp['type'] = $type;
            $temp['id'] = $id;
            $temp['usage'] = $usage;
            $row = $this->db->query('SELECT price FROM lib_clips WHERE id = ' . (int)$id)->result_array();
            if($row[0]['price'] == 0.00){
                $temp['rm'] = true;
            }
            if(isset($start_time)){
                $temp['start_time'] = $start_time;
            }
            if(isset($end_time)){
                $temp['end_time'] = $end_time;
            }
            $this->cart['items'][] = $temp;
            $data['cart'] = $this->cart;
            $this->session->set_userdata($data);
            return true;
        }else{
            return false;
        }
    }

    #------------------------------------------------------------------------------------------------

    function add_timeline_item($id) {
        $temp['type'] = 4;
        $temp['id'] = $id;
        $temp['rm'] = true;
        $this->cart['items'][] = $temp;
        $data['cart'] = $this->cart;
        $this->session->set_userdata($data);
        return true;
    }

    #------------------------------------------------------------------------------------------------

    function get_default_df($id) {
        $row = $this->db->query(
            'SELECT f.hr_id
      FROM lib_formats f
      INNER JOIN lib_clips c ON c.id = ? AND c.of_id = f.id',
            array($id))->result_array();
        return $row[0]['hr_id'];
    }

    #------------------------------------------------------------------------------------------------

    function delete_item(/*$parts*/$id) {
        /*list($type, $id) = explode('-',$parts);

        foreach($this->cart['items'] as $k=>$v) {
            if($v['type']==$type && $v['id']==$id) {
                unset($this->cart['items'][$k]);
            }
        }*/
        unset($this->cart['items'][$id-1]);

        $data['cart'] = $this->cart;
        $this->session->set_userdata($data);
    }

    #------------------------------------------------------------------------------------------------

    function check_exist($type, $id, $start_time = null, $end_time = null) {
        foreach((array)$this->cart['items'] as $v) {
            //if($v['type']==$type && $v['id']==$id && $v['start_time'] === $start_time && $v['end_time'] === $end_time) return true;
            if(isset($start_time) && isset($end_time)){
                if($v['type']==$type && $v['id']==$id && $v['start_time'] == $start_time && $v['end_time'] == $end_time) return true;
            }
            elseif(isset($start_time)){
                if($v['type']==$type && $v['id']==$id && $v['start_time'] == $start_time) return true;
            }
            elseif(isset($end_time)){
                if($v['type']==$type && $v['id']==$id && $v['end_time'] == $end_time) return true;
                $duration = $this->db->query('SELECT duration FROM lib_clips WHERE id = ' . (int)$id)->result_array();
                $duration = $duration[0]['duration'];
                if(round($duration, 2) == round($end_time, 2)){
                    return true;
                }
            }
            else{
                if($v['type']==$type && $v['id']==$id) return true;
            }
        }
        return false;
    }

    #------------------------------------------------------------------------------------------------

    function get_content($lang, $rate) {
        $names = array('', 'c', 'c', 'lm', 't');

        if($this->cart['items']) {
            foreach($this->cart['items'] as $v) {
                $types[$v['type']][] = $names[$v['type']].'.id='.$v['id'];
            }

            foreach($types as $type=>$items) {
                $filter[$type] = ' AND (' . implode(' OR ', $items) . ')';
            }

            #images
            if(count($types[1])) {
                $list = $this->images_model->get_images_list($lang, $filter[1]);

                $result['items'] = $this->apply_sets($list, 1, $rate);
                $result['type'] = 1;
                $results[1] = $result;
            }

            #clips
            if(count($types[2])) {
                $list = $this->clips_model->get_clips_list($lang, $filter[2]);

                $result['items'] = $this->apply_sets($list, 2, $rate);
                $result['type'] = 2;
                $results[2] = $result;
            }

            #collections
            if(count($types[3])) {
                $this->cats_model->set_type(1);
                $list = $this->cats_model->get_cart_cols($lang, $filter[3]);
                foreach ($list as &$item) {
                    $item['thumb'] = $this->cats_model->get_image_path($item);
                    $item['price'] = $this->api->price_format(
                        floatval($this->cats_model->get_collection_price($item['id'])));
                    $item['url'] = $lang . '/search/collection/'.$item['id'].$this->config->item('url_suffix');
                }
                $result['items'] = $list;
                $result['type'] = 3;
                $results[3] = $result;
            }

            #timelines
            if(count($types[4])) {
                $list = $this->timeline_model->get_timelines_list($filter[4]);
                $result['items'] = $this->apply_sets($list, 4, $rate);;
                $result['type'] = 4;
                $results[4] = $result;
            }


        }
        return $results;
    }

    #------------------------------------------------------------------------------------------------

    /*function apply_sets($list, $type, $rate) {
        $format_prices = $this->formats_model->get_format_prices();
        $new_list = array();
        foreach($list as $k=>$v) {
            foreach($this->cart['items'] as $cart_item_id => $val) {
                if($val['id']==$v['id'] && $val['type']==$type) {
                    $list[$k]['usage'] = $val['usage'];
                    $list[$k]['cart_item_id'] = $cart_item_id + 1;
                    if(isset($val['start_time'])){
                        $list[$k]['start_time'] = $val['start_time'];
                    }
                    if(isset($val['end_time'])){
                        $list[$k]['end_time'] = $val['end_time'];
                    }
                    $list[$k]['hr_id'] = $val['hr_id'];
                    $list[$k]['df_id'] = $val['df_id'];
                    $list[$k]['price'] = $this->api->price_format(
                        $format_prices[$val['df_id']]*$rate);
                    $new_list[] = $list[$k];
                }
            }
        }
        return $new_list;
    }*/


    function apply_sets($list, $type, $rate) {
        $new_list = array();
        foreach($list as $k=>$v) {
            foreach($this->cart['items'] as $cart_item_id => $val) {
                if($val['id']==$v['id'] && $val['type']==$type) {
                    $list[$k]['usage'] = $val['usage'];
                    $list[$k]['cart_item_id'] = $cart_item_id + 1;
                    if(isset($val['start_time'])){
                        $list[$k]['start_time'] = $val['start_time'];
                    }
                    if(isset($val['end_time'])){
                        $list[$k]['end_time'] = $val['end_time'];
                    }
                    if($list[$k]['price'] != 0.00){
                        if(isset($val['start_time']) || isset($val['end_time'])){
                            $start = isset($val['start_time']) ? $val['start_time'] : 0.00;
                            $end = isset($val['end_time']) ? $val['end_time'] : $list[$k]['duration'];
                            $duration = round($end - $start, 2);
                            if($duration == round($list[$k]['duration'], 2)){
                                $list[$k]['price'] = $this->api->price_format($list[$k]['price']);
                            }
                            else{
                                $list[$k]['price'] = $this->api->price_format($duration * $list[$k]['price_per_second']);
                            }
                        }
                        else{
                            $list[$k]['price'] = $this->api->price_format($list[$k]['price']);
                        }
                    }
                    $new_list[] = $list[$k];
                }
            }
        }
        return $new_list;
    }

    #------------------------------------------------------------------------------------------------

    function clear_cart() {
        $temp['cart'] = array();
        $this->session->set_userdata($temp);
    }

    #------------------------------------------------------------------------------------------------

    function set_df() {
        $df = $this->input->post('df', true);

        if($df) {
            foreach($this->cart['items'] as $i=>&$item) {
                $item['df_id'] = $df[$item['id']];
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function set_currency() {
        $currency = $this->session->userdata('currency');

        if(!$currency['code'] || !$currency['rate']) {

            if (isset($_SERVER['GEOIP_COUNTRY_CODE'])) {
                $data = $this->currencies_model->get_country_currency($_SERVER['GEOIP_COUNTRY_CODE']);
            } else {
                $data = $this->currencies_model->get_default();
            }
            $sd['currency']['code'] = $this->currency = $data['code'];
            $sd['currency']['rate'] = $this->rate = $data['rate'];
            $this->session->set_userdata($sd);
        }
        else {
            $this->currency = $currency['code'];
            $this->rate = $currency['rate'];
        }
    }

    #------------------------------------------------------------------------------------------------

    function set_delivery() {
        $delivery = $this->input->post('delivery', true);

        if ($delivery) {
            $this->cart['delivery'] = intval($delivery);
            $data['cart'] = $this->cart;
            $this->session->set_userdata($data);
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_cart_info() {
        foreach($this->cart['items'] as $item){
            if(isset($item['rm']) && $item['rm']){
                $data['rm'] = true;
                break;
            }
        }
        $data['count'] = count($this->cart['items']);
        $data['price'] = $this->get_cart_price($this->rate);

        $data['vat'] = $this->currency == 'GBP' ? $this->settings['vat'] : 0;

        $data['discount'] = $this->discounts_model->calc_discount($data['count']);

        $client_corporate = $this->session->userdata('client_corporate');
        $corporate_discount = $this->session->userdata('corporate_discount');

        if ($client_corporate && $corporate_discount) {
            $data['discount'] += $corporate_discount;
        }

        $total = $this->get_cart_total($data['price'], $data['discount'], $data['vat']);
        $data['delivery'] = $total['delivery'];
        $data['delivery_cost'] = $total['delivery_cost'];
        $data['nett'] = $total['nett'];
        $data['total'] = $total['total'];

        return $data;
    }

    #------------------------------------------------------------------------------------------------

    function get_cart_price($rate) {
        $price = 0;

        foreach($this->cart['items'] as $item) {

            if($item['type'] == 1) {
                $price += $this->api->price_format($this->images_model->get_image_price($item['id'])*$rate);
            }
            elseif($item['type'] == 2) {
                $start_time = isset($item['start_time']) ? $item['start_time'] : null;
                $end_time = isset($item['end_time']) ? $item['end_time'] : null;
                $price += $this->clips_model->get_clip_price($item['id'], $start_time, $end_time);
                //$price += $this->api->price_format($this->formats_model->get_format_price($item['df_id'])*$rate);
            }
            elseif($item['type'] == 3) {
                $price += $this->api->price_format(
                    floatval($this->cats_model->get_collection_price($item['id'])));
            }
        }

        return $this->api->price_format($price);
    }

    #------------------------------------------------------------------------------------------------

    function get_cart_total($price, $discount, $vat) {
        $total = array('total'=>$price, 'delivery'=>'', 'delivery_cost'=>0.00);

        if ($discount) {
            $total['total'] -= $total['total'] * $discount / 100;
        }

        $delivery = $this->delivery_model->get($this->cart['delivery']);
        $total['delivery'] = $delivery['name'];
        $total['delivery_cost'] = $this->api->price_format($delivery['cost'] * $this->rate);
        $total['nett'] = $total['total'] =
            $this->api->price_format($total['total'] + $total['delivery_cost']);
        if ($vat) {
            $total['total'] = $this->api->price_format($total['nett']
                + $total['nett'] * $vat / 100);
        }

        return $total;
    }

    #------------------------------------------------------------------------------------------------

    function save_order($status = 1) {
        $info = $this->get_cart_info();

        $data['client_id'] = $this->client;
        $data['sum'] = $info['price'];
        $data['discount'] = $info['discount'];
        $data['vat'] = $info['vat'];
        $data['delivery'] = $info['delivery'];
        $data['delivery_cost'] = $info['delivery_cost'];
        $data['total'] = $info['total'];
        $data['status'] = $status;
        $data['ctime'] = date('Y-m-d H:i:s');

        $this->db_master->insert('lib_orders', $data);

        $order['id'] = $this->db_master->insert_id();
        $order['amount'] = $data['total'];

        foreach($this->cart['items'] as $item) {
            $temp = array();
            $temp['order_id'] = $order['id'];
            $temp['item_type'] = $item['type'];
            $temp['item_id'] = $item['id'];
            //$temp['df_id'] = intval($item['df_id']);
            if(!isset($item['rm'])){
                $temp['price'] = $this->get_item_price($item);
            }
            //$temp['percent'] = $this->get_item_percent($item['id'], $item['type']);
            $temp['allowed_use'] = addslashes($item['usage']);
            $start_time = isset($item['start_time']) ? (float)$item['start_time'] : 0.00;
            $end_time = isset($item['end_time']) ? (float)$item['end_time'] : 0.00;
            if($item['type'] != 4){
                $fragment = $this->fragments_model->get_fragment_by_time($item['id'], $start_time, $end_time);
                if($fragment){
                    $temp['fragment_id'] = $fragment['id'];
                }
                else{
                    $fragment_id = $this->fragments_model->save_fragment($item['id'], $start_time, $end_time);
                    $temp['fragment_id'] = $fragment_id;
                }
            }
            else{
                $temp['fragment_id'] = 0;
            }
            $this->db_master->insert('lib_orders_items', $temp);
        }
        return $order;
    }

    #------------------------------------------------------------------------------------------------

    function get_item_percent($item_id, $item_type) {

        if(is_numeric($item_id) && is_numeric($item_type)) {

            if($item_type == 1)
                $table = 'lib_images';
            elseif($item_type == 2)
                $table = 'lib_clips';

            $sql = 'SELECT r.commision FROM ' . $table . ' l INNER JOIN lib_users r ON l.client_id = r.id AND l.id = ' . $item_id . ' LIMIT 1';
            $query = $this->db->query($sql);
            if($query->num_rows() > 0) {
                $result = $query->row()->commision;
            }

        }

        if(!isset($result)) $result = 0;

        return $result;

    }

    #------------------------------------------------------------------------------------------------

    function get_item_price($item) {
        if($item['type'] == 1)
            $price = $this->images_model->get_image_price($item['id'])*$this->rate;
        elseif($item['type'] == 2){
            //$price = $this->formats_model->get_format_price($item['df_id'])*$this->rate;
            $start_time = isset($item['start_time']) ? $item['start_time'] : null;
            $end_time = isset($item['end_time']) ? $item['end_time'] : null;
            $price = $this->clips_model->get_clip_price($item['id'], $start_time, $end_time);
        }
        elseif($item['type'] == 3)
            $price = floatval($this->cats_model->get_collection_price($item['id']));

        return $this->api->price_format($price);
    }

    #-----------------------------------------------------------------------------

    function make_order_post_string($order_id) {
        $invoice = $this->invoices_model->get_invoice($order_id);
        $customer = $this->register_model->get_client($invoice['client_id']);

        $strBasket = count($invoice['items']) + 1;

        foreach ($invoice['items'] as &$item) {
            if ($invoice['discount'] > 0.00) {
                $item['price'] = number_format(
                    $item['price'] - $item['price'] * $invoice['discount'] / 100, 2);
            }
            $item['vat_component'] = number_format(
                $item['price'] * $invoice['vat'] / 100, 2);
            $item['price_inc_vat'] = number_format(
                $item['price'] + $item['vat_component'], 2);
            $strBasket .= ':' . str_replace(':', ' ', $item['code']) . ' '
                . str_replace(':', ' ', $item['caption']) . ':1:'
                . $item['price'] . ':' . $item['vat_component'] . ':'
                . $item['price_inc_vat'] . ':' . $item['price_inc_vat'];
        }

        $delivery_vat = number_format(
            $invoice['delivery_cost'] * $invoice['vat'] / 100, 2);
        $delivery_inc_vat = number_format($invoice['delivery_cost'] + $delivery_vat, 2);
        $strBasket .= ':Delivery (' . $invoice['delivery'] . '):1:'
            . $invoice['delivery_cost'] . ':' . $delivery_vat . ':' . $delivery_inc_vat
            . ':' . $delivery_inc_vat;

        $post = array(
            'VendorTxCode' => $this->api->order_format($order_id) . '-' . date("ymdHis", time()),
            'Amount' => number_format($invoice['total'], 2),
            'Currency' => $invoice['currency'],
            'Description' => $this->config->item('Sagepay_Description'),
            'SuccessURL' => $this->config->item('base_url') . 'sage/response.php',
            'FailureURL' => $this->config->item('base_url') . 'sage/response.php',
            'CustomerName' => $customer['fname'] . ' ' . $customer['lname'],
            'SendEMail' => $this->sagepay->bSendEMail,
            'CustomerEMail' => $customer['email'],
            'VendorEMail' => $this->config->item('Sagepay_VendorEMail'),
            'BillingFirstnames' => $customer['fname'],
            'BillingSurname' => $customer['lname'],
            'BillingAddress1' => $customer['address'],
            'BillingCity' => $customer['city'],
            'BillingPostCode' => $customer['postcode'],
            'BillingCountry' => $customer['country_code'],
            'DeliveryFirstnames' => $customer['fname'],
            'DeliverySurname' => $customer['lname'],
            'DeliveryAddress1' => $customer['address'],
            'DeliveryCity' => $customer['city'],
            'DeliveryPostCode' => $customer['postcode'],
            'DeliveryCountry' => $customer['country_code'],
            'Basket' => $strBasket,
            'AllowGiftAid' => 0,
            'ApplyAVSCV2' => 0,
            'Apply3DSecure' => 0
        );

        if ($customer['country_code'] == 'US') {
            $post['BillingState'] = $post['DeliveryState'] = $customer['state'];
        }

        if (!empty($customer['phone'])) {
            $post['BillingPhone'] = $post['DeliveryPhone'] = $customer['phone'];
        }
        if (strlen($this->sagepay->strPartnerID) > 0) {
            $post['ReferrerID'] = $strPartnerID;
        }

        $post_string = '';
        $amp = '';
        foreach ($post as $name=>$value) {
            $post_string .= $amp . $name . '=' . str_replace('&', ' ', $value);
            $amp = '&';
        }

        return $post_string;
    }

    #-----------------------------------------------------------------------------

    function make_gateway() {
        $order = $this->save_order();
        $this->session->set_userdata('order_id', $order['id']);

        /*$this->sagepay->strCurrency = $this->currency;
        $data = (array)$this->sagepay;

        $post_string = $this->make_order_post_string($order['id']);

        $data['strCrypt'] = $this->sagepay->encryptAndEncode($post_string);

        $sage_form = $this->load->view('main/gateways/sagepay', $data, true);

        $pp_express_form = $this->load->view('main/gateways/paypal_express_checkout',
            null, true);

        $gateway = $sage_form . '<br />' . $pp_express_form;*/

        $dummy_credit_card_processor = $this->load->view('main/gateways/dummy_credit_card_processor', null, true);
        $gateway = $dummy_credit_card_processor;

        return $gateway;
    }

    #-----------------------------------------------------------------------------

    function gateway_response() {
        $crypt = urldecode($this->uri->segment(4));

        if (strlen($crypt) == 0) {
            return;
        }

        $decoded = $this->sagepay->decodeAndDecrypt($crypt);

        $logfile = $_SERVER['DOCUMENT_ROOT'] . '/data/log/sage_response_'
            . date('YmdHis') . '.txt';
        file_put_contents($logfile, $decoded);

        $response = $this->sagepay->getToken($decoded);

        return $response;
    }

    #-----------------------------------------------------------------------------

    function set_express_checkout() {
        $client_id = $this->session->userdata('client_uid');
        $order_id = $this->session->userdata('order_id');
        if (empty($client_id) || empty($order_id) || empty($this->cart['items'])) {
            redirect('/cart');
            return;
        }

        $client = $this->register_model->get_client($id);

        $returnURL = $this->config->item('base_url') . 'paypal/review.php';
        $cancelURL = $this->config->item('base_url') . 'paypal/cancel.php';

        $nvpstr = '&REQCONFIRMSHIPPING=0&INVNUM=' . $order_id;

        $num = 0;
        $cart_info = $this->get_cart_info();
        $items_price = 0.00;
        foreach ($this->cart['items'] as $item) {
            $item_name = $this->db->query('SELECT title
        FROM lib_clips_content
        WHERE clip_id = ? AND lang = ?', array($item['id'], 'en'))->result();
            $item_name = $item_name[0]->title;

            $item_df = $this->db->query('SELECT title FROM lib_formats
        WHERE id = ?', $item['df_id'])->result();
            $item_df = $item_df[0]->title;

            $item_name .= ' (' . $item_df . ')';
            if (strlen($item_name) > 100) {
                $item_name = substr($item_name, 0, 100);
            }

            $item_price = number_format($this->get_item_price($item), 2, '.', '');
            if ($cart_info['discount']) {
                $item_price = number_format(
                    $item_price - $item_price * $cart_info['discount'] / 100, 2, '.', '');
            }
            $items_price += $item_price;

            $nvpstr .= '&L_NAME' . $num . '=' . urlencode($item_name)
                . '&L_AMT' . $num . '=' . $item_price
                . '&L_QTY' . $num . '=1';

            ++$num;
        }

        $delivery = $this->db->query('SELECT name, cost FROM lib_delivery WHERE id = ?',
            $this->cart['delivery'])->result();
        $delivery = $delivery[0];
        $delivery->cost = number_format($delivery->cost * $this->rate, 2, '.', '');

        $nvpstr .= '&L_NAME' . $num . '=Delivery: ' . urlencode($delivery->name)
            . '&L_AMT' . $num . '=' . $delivery->cost . '&L_QTY' . $num . '=1';

        $total = number_format($cart_info['total'], 2, '.', '');

        $vat = number_format($total - $items_price - $delivery->cost, 2, '.', '');
        ++$num;
        $nvpstr .= '&L_NAME' . $num . '=VAT'
            . '&L_AMT' . $num . '=' . $vat . '&L_QTY' . $num . '=1';

        $nvpstr .= '&AMT=' . $total
            . '&CURRENCYCODE=' . $this->currency
            . '&RETURNURL=' . urlencode($returnURL)
            . '&CANCELURL=' . urlencode($cancelURL);

        $nvpstr = $this->caller->nvpHeader() . $nvpstr;

        $resArray = $this->caller->hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);

        if($ack=="SUCCESS"){
            // Redirect to paypal.com here
            $token = urldecode($resArray['TOKEN']);
            $payPalURL = $this->config->item('PP_URL') . $token;
            header("Location: " . $payPalURL);
        } else  {
            //Redirecting to APIError to display errors.
            $location = '/paypal/error.php';
            header("Location: $location");
        }
    }

    #-----------------------------------------------------------------------------

    function get_express_checkout_details() {
        $nvpstr = $this->caller->nvpHeader() . '&TOKEN=' . $_COOKIE['token'];
        $resArray = $this->caller->hash_call("GetExpressCheckoutDetails", $nvpstr);
        return $resArray;
    }

    #-----------------------------------------------------------------------------

    function do_express_checkout_payment() {
        $info = $this->get_cart_info();

        $token = urlencode($_COOKIE['token']);
        $payerID = urlencode($_COOKIE['PayerID']);
        $paymentAmount = urlencode($info['total']);
        $paymentType = 'Sale';
        $currCodeType = $this->currency;
        $serverName = urlencode($_SERVER['SERVER_NAME']);

        $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType
            .'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType
            .'&IPADDRESS='.$serverName;

        $resArray = $this->caller->hash_call('DoExpressCheckoutPayment', $nvpstr);
        return $resArray;
    }

    #-----------------------------------------------------------------------------

    function forget_token() {
        setcookie('token', '', time() - 43200, '/');
        setcookie('PayerID', '', time() - 43200, '/');
    }

    #-----------------------------------------------------------------------------

    function guest_cart_save($guest_id,$cart) {
        $c=$this->db->get_where('lib_guests',array('guest_id'=>$guest_id))->result_array();
        if(!empty($c[0]['guest_id'])){
            $this->db_master->where('guest_id',$guest_id);
            $this->db_master->update('lib_guests',array('cart_serialized'=>$cart));
        }else{
            $q="INSERT INTO lib_guests (guest_id, cart_serialized) VALUES ('" . $guest_id . "', '" . $cart . "')";
            $this->db_master->query($q);
        }
    }

    #-----------------------------------------------------------------------------

    function notify_purchase($order_id) {
        $invoice = $this->invoices_model->get_invoice($order_id);
        $data = array('invoice'=>$invoice, 'host'=>$_SERVER['HTTP_HOST']);

        $admin_message = $this->load->view('main/mail/notify_admin', $data, true);
        $client_message = $this->load->view('main/mail/notify_client', $data, true);

        $client = $this->register_model->get_client($invoice['client_id']);

        $this->load->library('email');
        $config['mailtype'] = 'html';
        $config['wordwrap'] = 0;
        $this->email->initialize($config);

        $this->email->from($this->settings['email']);
        $this->email->to($client['email']);
        $this->email->subject('Purchase confirmation');
        $this->email->message($client_message);
        //$this->email->attach($this->invoices_model->make_pdf($order_id, $this->langs, true));
        $this->email->send();

        $this->email->from($client['email']);
        $this->email->to($this->settings['email']);
        $this->email->subject('Purchase confirmation from ' . $_SERVER['HTTP_HOST']);
        $this->email->message($admin_message);
        $this->email->send();
    }

    function notify_order($order_id, $rm_order = true) {
        $invoice = $this->invoices_model->get_invoice($order_id);
        $data = array('invoice' => $invoice, 'host' => $_SERVER['HTTP_HOST'], 'rm_order' => $rm_order);

        $admin_message = $this->load->view('main/mail/notify_admin', $data, true);
        $client_message = $this->load->view('main/mail/notify_client', $data, true);

        $client = $this->register_model->get_client($invoice['client_id']);

        $this->load->library('email');
        $config['mailtype'] = 'html';
        $config['wordwrap'] = 0;
        $this->email->initialize($config);

        $this->email->from($this->settings['email']);
        $this->email->to($client['email']);
        $this->email->subject('Order confirmation');
        $this->email->message($client_message);
        //$this->email->attach($this->invoices_model->make_pdf($order_id, $this->langs, true));
        $this->email->send();

        $this->email->from($client['email']);
        $this->email->to($this->settings['email']);
        $this->email->subject('Order confirmation from ' . $_SERVER['HTTP_HOST']);
        $this->email->message($admin_message);
        $this->email->send();
    }

}