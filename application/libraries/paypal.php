<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Paypal {

  var $CI;
  var $pp_site;
  var $pp_vendor;
  var $messages = '';

  // --------------------------------------------------------------------
  /**
   * Class constructor
   */

  function Paypal() {
      $this->CI = &get_instance();
      $this->pp_site = $this->CI->config->item('pp_site');
      $this->pp_vendor = $this->CI->config->item('pp_vendor');
  }

  // --------------------------------------------------------------------
  /**
   * Check paypal response
   */

  function check_response() {
    $postdata = '';

    foreach($_POST as $key=>$value)
      $postdata.=$key.'='.urlencode($value).'&';

    $postdata .= 'cmd=_notify-validate';

    $curl = curl_init($this->pp_site);
    curl_setopt ($curl, CURLOPT_HEADER, 0);
    curl_setopt ($curl, CURLOPT_POST, 1);
    curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1);
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response=='VERIFIED') {
      $this->messages .= "Check response passed.\r\n";
      return true;
    } else {
      $this->messages .= "Check response failed (" . $response . ").\r\n";
      return false;
    }
  }

  // --------------------------------------------------------------------
  /**
   * Check transaction receiver and payment type
   */

  function check_receiver() {
    $receiver_email = $this->CI->input->post('receiver_email');
    $txn_type = $this->CI->input->post('txn_type');

    if ($receiver_email!=$this->pp_vendor || $txn_type!='web_accept') {
      $this->messages .= "Check receiver failed.\r\n";
      return false;
    } else {
      $this->messages .= "Check receiver passed.\r\n";
      return true;
    }
  }

  // --------------------------------------------------------------------
  /**
   * Check process transaction
   */

  function check_processed() {
    $txn_id = $this->CI->input->post('txn_id');
    $item_number = $this->CI->input->post('item_number');

    #$query = $this->CI->db->get_where('lib_payments', array('txn_id'=>$txn_id, 'order_id'=>$item_number));
    if ($query->num_rows()) {
      $this->messages .= "Check processed passed.\r\n";
      return true;
    } else {
      $this->messages .= "Check processed failed.\r\n";
      return false;
    }
  }

  // --------------------------------------------------------------------
  /**
   * Check transaction sum and currency
   */

  function check_sum($sum, $currency) {
    $mc_gross = $this->CI->input->post('mc_gross');
    $mc_currency = $this->CI->input->post('mc_currency');

    if ($mc_gross!=$sum || $mc_currency!=$currency) {
      $this->messages .= "Check sum failed.\r\n";
      return false;
    } else {
      $this->messages .= "Check sum passed.\r\n";
      return true;
    }
  }

  // --------------------------------------------------------------------
  /**
   * Check payment
   */

  function check_payment() {
    $order_id = $this->CI->input->post('item_number');

    $query = $this->CI->db->query('select lo.total, lcc.currency
      from lib_orders as lo, lib_users as lc, lib_countries as lcc
      where lcc.id=lc.country_id and lc.id=lo.client_id and lo.id='.$order_id);
    $rows = $query->result_array();

    $sum = $rows[0]['total'];
    $currency = $rows[0]['currency'];
/*
    if(!$this->check_response()) {
      return false;
    }
    if(!$this->check_receiver()) {
      return false;
    }
*/
    if(!$this->check_sum($sum, $currency)) {
      return false;
    }

    return true;
  }
}