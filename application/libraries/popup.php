<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Popup {

  var $CI;

  var $body;
  var $submit;
  var $callback;
  var $buttons;

  function Popup() {

    $this->CI = &get_instance();
    $this->langs = $this->CI->uri->segment(1);

  }

  function get_popup_code() {

      $code = '<script>';
      //$code .= '$(document).ready(function(){ ';
      $code .= '$.prompt(' . $this->body . ', {';

      if($this->callback)
        $code .= 'callback: ' . $this->callback . ',';
      elseif($this->submit)
        $code .= 'submit: ' . $this->submit . ',';

      $code .= 'buttons: {' . $this->get_buttons_code() . '},';
      $code .= 'prefix: \'extended\'';

      $code .= '}); ';
      $code .= '</script>';

      return $code;
      
  }

  function get_buttons_code() {

      $tmp = array();

      foreach ($this->buttons as $name=>$value)
          $tmp[] = $name . ' : ' . $value;

      $code = implode(', ', $tmp);

      return $code;

  }

}

?>
