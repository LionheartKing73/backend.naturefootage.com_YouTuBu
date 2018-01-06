<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Api {
     
     var $CI;

    // --------------------------------------------------------------------
    /**
     * Class constructor
    */
         
     function Api()
     {
         $this->CI = &get_instance();
     }
     
    // --------------------------------------------------------------------
    /**
     * Check permissions
    */

    function permission () {
        if ( $this->CI->session->userdata( 'login' ) && $this->CI->session->userdata( 'uid' ) ) {
            $login = $this->CI->session->userdata( 'login' );
            $uid = $this->CI->session->userdata( 'uid' );

            $query = $this->CI->db->get_where( 'lib_users', array ( 'login' => $login, 'id' => $uid ) );

            return ( $query->num_rows() > 0 );
        }
        return FALSE;
    }

    // --------------------------------------------------------------------
    /**
     * User log
    */
  
    function log($key, $ids=null)
    {
        $this->db_master = $this->CI->load->database('master', TRUE);

       $line = $this->CI->lang->line($key);
       $mas = implode(', ', (array)$ids);
       $action = str_replace('%id%', $mas, $line);
          
       $data['user_id'] = $this->CI->session->userdata('uid');
       $data['action'] = $action;

        $this->db_master->insert('lib_log', $data);
    }
        
    // --------------------------------------------------------------------
    /**
     * Check access
    */
  
    function check_access()
    {
      if(!$this->permission()) die('Access denied');
    }

    // --------------------------------------------------------------------
    /**
     * Check email
    */
  
    function check_email($email)
    {
      return preg_match("`^[a-z0-9]+([_.-]+[a-z0-9]+)*@([a-z0-9]+([.-][a-z0-9]+)*)+\\.[a-z]{2,4}$`", $email);
    }
    
     // --------------------------------------------------------------------
    /**
     * Dump data
    */

    function dump($var)
    {
       echo "<pre>";
       print_r((array)$var);
       echo "</pre>";
    }

    // --------------------------------------------------------------------
    /**
     * Check Ajax Request
    */
    function is_ajax_request() {
        $result = isset($_SERVER['HTTP_X_REQUESTED_WITH'])?$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest' : false;
        return $result;
    }
    
    // --------------------------------------------------------------------
    /**
     * Get user settings for cms system
    */

    function settings($param=null)
    {
      if (!$this->lib_settings) {
        $query = $this->CI->db->get('lib_settings');
        $rows = $query->result_array();

        foreach($rows as $row)
          $this->lib_settings[$row['name']] = $row['value'];
      }
      
      if(empty($param)) {
        return $this->lib_settings;
      }
      else {
        return $this->lib_settings[$param];
      }
    } 
    
    // --------------------------------------------------------------------
    /**
     * Clear uri from additional data - sort and etc
    */

    function prepare_uri()
    {
       $uri = trim($this->CI->uri->uri_string(),'/');
      
       $pos = strpos($uri,'index');
       if(!$pos) $uri .= '/index';
        
       $pos = strpos($uri,'/sort');
       if($pos) $uri = substr($uri,0,$pos);
      
       return $uri;
    } 

    // --------------------------------------------------------------------
    /**
     * Get current uri
    */
        
    function get_uri()
    {  
        $uri = trim($this->CI->uri->uri_string(),'/'); 
        $uri .= $this->CI->config->item('url_suffix');
        return $uri;
    }
     
    // --------------------------------------------------------------------
    /**
     * Get sort order
    */

    function get_uri_sort($uri)
    {
       $pos1 = strpos($uri,'/sort/');
       
       if($pos1){
          $pos1+=6;  
          $pos2 = strpos($uri,'/', $pos1);
          if(!$pos2) $pos2 = strlen($uri);
       }
       return trim(substr($uri, $pos1, $pos2-$pos1),'/');
    }  
     
    // --------------------------------------------------------------------
    /**
     * Prepare time format for sql query
    */
    
    function prepare_ctime($ctime)
    {
        $date = substr($ctime, 0, 10);
        $time = substr($ctime, 11); 
        return $this->reverse_date($date).' '.$time; 
    }
    
    // --------------------------------------------------------------------
    /**
     * Prepare date format for sql
    */
    
    function reverse_date($date, $time=null)
    {
        $parts = array_reverse(explode('.', $date));
        $result = implode('-', $parts);
        return ($time) ?  $result.' 23:59:59' : $result; 
    }     
    
    // --------------------------------------------------------------------    
    /**
     * Prepare percent format for displaying
    */
    
    function percent_format($val)
    {   
       return number_format($val, 2, '.', '');
    }

    // --------------------------------------------------------------------    
    /**
     * Byte converting with formated number 
    */
        
     function byte_convert($bytes)
     { 
        $b = (int)$bytes; 
        $s = array('B', 'kB', 'MB', 'GB', 'TB'); 
     
        if($b < 0) return "0 ".$s[0]; 
     
        $con = 1024; 
        $e = (int)(log($b,$con)); 
        return number_format($b/pow($con,$e),2,'.','.').' '.$s[$e]; 
     }

    // --------------------------------------------------------------------    
    /**
     * Check extension
    */    
    
    function check_ext($ext, $type='img')
    {  
        switch($type){
          case 'video' : $dataexts  = array('swf', 'mov', 'flv', 'avi', 'wmv', 'mp4');break;
          case 'pdf' : $dataexts  = array('pdf');break;
          case 'img' : $dataexts  = array('gif', 'jpg', 'jpeg', 'swf', 'png');
        }

        if($ext && in_array($ext, $dataexts)) return true;
        return false;
    } 
            
    // --------------------------------------------------------------------    
    /**
     * Save sort order
    */
        
    function save_sort_order($section)
    {
        $sort = $this->get_uri_sort($this->CI->uri->uri_string()); 
        $sort_section = $this->CI->session->userdata('sort_'.$section);
       
        if($sort){
          if($sort_section[$sort]) $temp[$sort] = intval(!$sort_section[$sort]);
          else $temp[$sort] = 1;

          $this->CI->session->set_userdata(array('sort_'.$section=>$temp));
        }
    }

    // --------------------------------------------------------------------    
    /**
     * Save sort order
    */
        
    function get_sort_order($section)
    {
        $sort_section = $this->CI->session->userdata('sort_'.$section);
        $order = '';

        if($sort_section){
          foreach($sort_section as $k=>$v){
            $direction = ($v) ? ' ASC ' : ' DESC ';  
            $order = ' order by '.$k.$direction;
          }
        }
        return $order;
    }   

    // --------------------------------------------------------------------    
    /**
     * Get pagination list
    */

    function get_pagination ( $section, $all, $perpage = 20, $suffix = NULL ) {
        $parts = explode( '/', $section );
        $lang = $this->CI->uri->segment( 1 );

        $config[ 'base_url' ] = $lang . '/' . $section;
        $config[ 'total_rows' ] = $all;
        $config[ 'per_page' ] = $perpage;
        $config[ 'uri_segment' ] = count( $parts ) + 2;
        $config[ 'suffix' ] = $suffix;

        $config[ 'full_tag_open' ] = '<ul>';
        $config[ 'full_tag_close' ] = '</ul>';

        $config[ 'first_tag_open' ] = '<li>';
        $config[ 'first_tag_close' ] = '</li>';

        $config[ 'prev_tag_open' ] = '<li>';
        $config[ 'prev_tag_close' ] = '</li>';

        $config[ 'num_tag_open' ] = '<li>';
        $config[ 'num_tag_close' ] = '</li>';

        $config[ 'cur_tag_open' ] = '<li class="active"><a>';
        $config[ 'cur_tag_close' ] = '</a></li>';

        $config[ 'next_tag_open' ] = '<li>';
        $config[ 'next_tag_close' ] = '</li>';

        $config[ 'last_tag_open' ] = '<li>';
        $config[ 'last_tag_close' ] = '</li>';

        $this->CI->pagination->initialize( $config );
        $this->CI->pagination->num_links = 4;
        return $this->CI->pagination->create_links();
    }
    
    // --------------------------------------------------------------------    
    /**
     * Formatting price numbers
    */
    
    function price_format($price)
    {
       return number_format((float)$price, 2, '.', '');
    }
    
    // --------------------------------------------------------------------    
    /**
     * Formatting order numbers
    */
    
    function order_format($id){
       return 'FL'.str_pad($id, 5, '0', STR_PAD_LEFT);
    } 
 
    // --------------------------------------------------------------------    
    /**
     * Clear order numbers
    */
    
    function clear_order_format($ref){
      return intval(substr($ref,2));
    }
    
    // --------------------------------------------------------------------
    /**
     * Get file extension
    */

    function get_file_ext($name) {
    	$path_parts = pathinfo($name);
      return $path_parts['extension'];
    }

    // --------------------------------------------------------------------
    /**
     * Get file name with extension
    */
    function get_file_basename($path) {
    	$path_parts = pathinfo($path);
      return $path_parts['basename'];
    }
    
  // --------------------------------------------------------------------
    
  function get_seo_keys($count_hv=3, $count_lv=3) {

    $key_types = array('hv'=>$count_hv, 'lv'=>$count_lv);
    $result = '';
    $delim = '';

    foreach($key_types as $key_type=>$key_count) {
      $keys = file($this->CI->config->item('seo_dir') . $key_type . '.txt');
      $keys_count = count($keys);
      for ($i = 0; $i < $key_count; ++$i) {
        $idx = rand(0, $keys_count - 1);
        $result .= $delim . trim($keys[$idx]);
        $delim = ', ';
      }
    }

    return $result;
  }
  
  // --------------------------------------------------------------------
  
  function get_video_clip() {
    $words = array('Video Clip', 'Stock Footage', 'Video Footage', 'Stock Video');
    return $words[rand(0, 3)];
  }
  
  // --------------------------------------------------------------------
  
  function get_block($id, $lang='en') {
    $data = $this->CI->db->get_where('lib_blocks', array('id'=>$id, 'lang'=>$lang))->result_array();
    $data = $data[0];

    $lang = $this->CI->uri->segment(1);
    if (!$lang) {
      $lang = 'en';
    }
    $data['lang'] = $lang;

    $data['visual_mode'] = $this->permission();

    return $this->CI->load->view('main/ext/block', $data, true);
  }
  
}
?>