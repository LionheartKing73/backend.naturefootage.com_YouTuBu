<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Language extends CI_Language {

    var $currLang;

    function My_Language() {
        parent::CI_Language(); 
    }

    function __get($name) {

        if($name == 'url') {
            if($this->currLang)
               return $this->currLang;
            else {
                $CI =& get_instance();
                $this->currLang = $CI->uri->segment(1);
                return $this->currLang;
            }
        }

    }
    

}



?>
