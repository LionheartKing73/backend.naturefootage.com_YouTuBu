<?php

class Ccode extends CI_Controller {

  function index() {
    $this->load->library('captcha');
    $captcha_keystring = $this->captcha->getKeyString();

    #$this->session->set_userdata('captcha_keystring', $captcha_keystring);

    session_start();
    $_SESSION['captcha_keystring'] = $captcha_keystring;
  }

}