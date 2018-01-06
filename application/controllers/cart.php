<?php
class Cart extends AppController {

    var $method;
    var $client;
    var $currency;
    var $rate;
    var $cart;

    function Cart() {
        parent::__construct();

        $this->load->model('cart_model');
        $this->load->model('currencies_model');
        $this->load->model('bin_model');
        $this->load->model('formats_model');
        $this->load->model('register_model');

        $this->settings = $this->api->settings();
        $this->cart = $this->session->userdata('cart');
        $this->client = $this->session->userdata('client_uid');

        $this->langs = $this->uri->segment(1);
        $this->method = $this->uri->segment(3);
        $this->id = $this->uri->segment(4);

        $this->prepend();
    }

    #------------------------------------------------------------------------------------------------

    function prepend() {
        $this->cart_model->set_currency();
        $this->cart_model->set_df();
        $this->cart_model->set_delivery();
    }

    #------------------------------------------------------------------------------------------------

    function index() {
        switch($this->method) {
            case 'add': $this->add_items();
            break;
            case 'addbin': $this->add_bin();
            break;
            case 'delete': $this->delete_item();
            $out=1;
            break;
            case 'clear': $this->clear_cart();
            $out=1;
            break;
            case 'checkout': $this->checkout();
            break;
            case 'review': $this->review();
            break;
            case 'pay': $this->pay();
            break;
            case 'success': $this->success();
            break;
            case 'notify': $this->success();
            break;
            case 'cancel': $this->cancel();
            break;
            case 'info':
                $this->info();
                break;
            default: $out=1;
        }

        if($out) {
            $data = $this->content();
            $this->out($data);
        }
    }
    #-----------------------------------------------------------------------------

    function _info($data = null) {
        $cart = $this->session->userdata('cart');
        $bin = $this->session->userdata('bin');

        $content = array(
            'cart_count' => count($cart['items']),
            'bin_count'  => count($bin['items']),
        );

        if (($this->uri->segment(6) == 'refresh') || ($this->method == 'addbin')) {
            $content['refresh'] = true;
        }

        $this->load->view('cart/info', $content);
    }

    #-----------------------------------------------------------------------------

    function info() {
        $this->_info();
    }

    #-----------------------------------------------------------------------------

    function add_item() {
        $type = intval($this->uri->segment(4));
        $id = intval($this->uri->segment(5));
        $df_id = intval($this->uri->segment(6));

        if($type && $id) {
            $this->cart_model->add_item($type, $id, $df_id);
            $this->_info();
        }
    }

    #-----------------------------------------------------------------------------

    function add_items() {
        $items = $this->input->post('items');
        if(count($items)){
            $added = 0;
            foreach($items as $id => $item){
                if($item['usage'] || $item['price']){
                    if($this->cart_model->add_item(2, $id, $item['usage'], $item['start'] ? round($item['start'], 2) : null, $item['end'] ? round($item['end'], 2) : null)){
                        $added++;
                    }
                }
            }
            if($added > 0){
                echo $added;
                exit();
            }
        }
    }

    #-----------------------------------------------------------------------------

    function add_bin() {
        $bin_items = $this->bin_model->get_content($this->langs);

        foreach ($bin_items as $item) {
            $this->cart_model->add_item(2, $item['id']);
        }

        $this->_info();
    }

    #-----------------------------------------------------------------------------

    function content() {

        $list['continue'] = $this->session->userdata('search_page');

        if($this->cart['items']) {
            $list['results'] = $this->cart_model->get_content($this->langs, $this->rate);
            $list['client'] = $this->client;
            $list['currency'] = $this->currency;
            $list['delivery'] = $this->cart['delivery'];
            $list['currency_name'] = $this->currencies_model->get_currency_name($this->currency);
            $list['cart'] = $this->cart_model->get_cart_info();

            $ofs = $this->formats_model->get_formats_list(3);
            $dfs = array();
            /*foreach ($ofs as $of) {
                $dfs[$of['id']] = $this->formats_model->get_delivery_formats($of['id']);
            }*/
            $list['dfs'] = $dfs;

            if ($this->input->post('accept')) {
                $list['accept'] = true;
            }

            $this->load->model('delivery_model', 'dm');
            $list['delivery_methods'] = $this->dm->get_list($this->rate);
        }

        $list['bin_items'] = $this->bin_model->get_content($this->langs);
        $list['lang'] = $this->langs;

        $data['add_css'] = array('/data/css/cart.css', '/data/css/jquery-ui.css', '/data/css/search.css');
        //$data['add_js'] = array('/data/js/jquery.js', '/data/js/jquery-ui.js');
        $data['body'] = $this->load->view('cart/content', $list, true);
        $data['title'] = $this->lang->line('cart');

        return $data;
    }

    #------------------------------------------------------------------------------------------------

    function delete_item() {
        $this->cart_model->delete_item($this->id);
        redirect('/cart/');
    }

    #------------------------------------------------------------------------------------------------

    function clear_cart() {
        $this->cart_model->clear_cart();
        redirect('/cart/');
    }

    #------------------------------------------------------------------------------------------------

    function checkout() {

        $this->cart_model->forget_token();

        /*if($this->client) {
            if($this->session->userdata('client_corporate')) {
                $list['corporate'] = true;
                $corporate_balance = $this->session->userdata('corporate_balance');
                $cart_info = $this->cart_model->get_cart_info();

                if ($corporate_balance >= $cart_info['total']) {
                    $order = $this->cart_model->save_order(3);
                    $this->load->model('users_model', 'users_m');
                    $corporate_balance -= $cart_info['total'];
                    $this->users_m->set_corporate_balance($this->client, $corporate_balance);
                    $this->session->set_userdata('corporate_balance', $corporate_balance);
                    $this->cart_model->clear_cart();
                    $this->cart_model->notify_purchase($order['id']);
                    $list['order_thx'] = true;
                } else {
                    $currency = $this->session->userdata('currency');
                    $list['currency'] = $currency['code'];
                    $list['total'] = $cart_info['total'];
                    $list['balance'] = $corporate_balance;
                }
            }
            else {
                $list['gateway'] = $this->cart_model->make_gateway();
            }
        }*/



        /*if($this->client) {
            $order = $this->cart_model->save_order();
            $this->cart_model->notify_order($order['id']);
            $this->cart_model->clear_cart();
            //$this->cart_model->notify_purchase($order['id']);
            $list['order_thx'] = true;
        }*/

        if($this->client) {
            $cart_info = $this->cart_model->get_cart_info();
            if(isset($cart_info['rm']) && $cart_info['rm']){
                $order = $this->cart_model->save_order();
                $this->cart_model->notify_order($order['id'], true);
                $this->cart_model->clear_cart();
                $list['order_thx'] = true;
                $list['rm_order'] = true;
            }
            else{
                $list['gateway'] = $this->cart_model->make_gateway();
            }
        }

        $list['client'] = $this->client;

        $data['body'] = $this->load->view('cart/checkout', $list, true);
        $data['title'] = $this->lang->line('checkout');

        $this->out($data);
    }

#-------------------------------------------------------------------------------
    function review() {

        if ($_COOKIE['token']) {
            $resArray = $this->cart_model->get_express_checkout_details();
            $ack = strtoupper($resArray['ACK']);

            if ($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {

                $data = array('resArray' => $resArray);

                $content['body'] = $this->load->view('cart/review', $data, true);
                $content['title'] = 'Review Order';
                $this->out($content);
            } else {
                $location = '/paypal/error.php';
                header("Location: $location");
            }
        } else {
            $this->cart_model->set_express_checkout();
        }
    }
#-------------------------------------------------------------------------------
    function success() {

        $order_id = $this->session->userdata('order_id');
        $this->cart_model->invoices_model->confirm_pay($order_id);
        $this->cart_model->notify_order($order_id, false);
        $this->cart_model->clear_cart();
        $this->session->unset_userdata('order_id');
        $this->cart_model->forget_token();

        /*$response = $this->cart_model->gateway_response();

        if (empty($response)) {
            redirect('/cart');
            return;
        }

        if ($response['Status'] == 'OK') {
            $order_id = $this->api->clear_order_format($response['VendorTxCode']);

            $this->cart_model->invoices_model->confirm_pay($order_id);
            $this->cart_model->notify_purchase($order_id);
            $this->cart_model->clear_cart();

            $view = 'cart/success';
        } else {
            $view = 'cart/cancel';
        }*/

        $view = 'cart/success';
        $data['lang'] = $this->langs;
        $content['body'] = $this->load->view($view, $data, true);
        $content['title'] = $this->lang->line('cart_success');

        $this->out($content);
    }
#-------------------------------------------------------------------------------
    function pay() {
        $resArray = $this->cart_model->do_express_checkout_payment();
        $ack = strtoupper($resArray['ACK']);
        if ($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
            $order_id = $this->session->userdata('order_id');
            $this->cart_model->invoices_model->confirm_pay($order_id);
            $this->cart_model->notify_purchase($order_id);
            $this->cart_model->clear_cart();
            $this->session->unset_userdata('order_id');
            $this->cart_model->forget_token();

            $content['body'] = $this->load->view('cart/success', null, true);
            $content['title'] = $this->lang->line('cart_success');
            $this->out($content);
        } else {
            $log_file = $_SERVER['DOCUMENT_ROOT'] . '/data/log/pay.log';
            $log_data = print_r($resArray, true);
            file_put_contents($log_file, $log_data);
            redirect('cart');
        }
    }

}