<?php

class Feedback_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

  function get_errors() {
    $errors = array();
    
    $fields = array('name', 'email', 'message');
    
    foreach ($fields as $field) {
      if (empty($_POST[$field])) {
        $errors[$field] = 'Required field';
      }
    }

    if (!$errors['email'] && !$this->api->check_email($this->input->post('email'))) {
      $errors['email'] = 'Invalid email address';
    }

    #$captcha_keystring = $this->session->userdata('captcha_keystring');

    //session_start();
    //$captcha_keystring = $_SESSION['captcha_keystring'];
    //session_unset();
    
    //if (!$errors['captcha'] && ($this->input->post('captcha')!=$captcha_keystring)) {
    //  $errors['captcha'] = 'Wrong confirmation code';
    //}
    
    if (count($errors)) {
      return $errors;
    }
  }
  
  #------------------------------------------------------------------------------------------------
  
  function submit() {
  
    $data = array(
      'name'=>$_POST['name'],
      'email'=>$_POST['email'],
      'company'=>$_POST['company'],
      'phone'=>$_POST['phone'],
      'message'=>$_POST['message']
    );

    $company = !empty($data['company'])? 'Company: ' . $data['company'] . "\r\n" : '';

    $this->load->library('email');
    
    $this->email->from($data['email'], $data['name']);
    $this->email->to($this->api->settings('email'));
    $this->email->subject('Message from feedback form');
    $message = 'Message from feedback form on '
      . $_SERVER['HTTP_HOST'] . "\r\n\r\n"
      . 'Name: ' . $data['name'] . "\r\n"
      . 'Email: ' . $data['email'] . "\r\n"
      . $company
      . 'Phone: ' . $data['phone'] . "\r\n"
      . 'Message: ' . $data['message'] . "\r\n";
      
    $this->email->message($message);
    $this->email->send();

    
    $data['name'] = htmlspecialchars($data['name']);
    $data['phone'] = htmlspecialchars($data['phone']);
    $data['message'] = htmlspecialchars($data['message']);
    
    $this->db_master->insert('lib_feedback', $data);
  }
  
  #------------------------------------------------------------------------------------------------
  
  function get_list() {
    $list = $this->db->query('SELECT * FROM lib_feedback ORDER BY id DESC')->result_array();
    return $list;
  }
  
  #------------------------------------------------------------------------------------------------
  
  function delete($ids) {
    $ids = implode(', ', $ids);
    $this->db_master->query('DELETE FROM lib_feedback WHERE id IN (' . $ids . ')');
  }
}
