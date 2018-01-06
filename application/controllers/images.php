<?php

class Images extends CI_Controller {

    var $id; 
    var $langs;
    var $settings;
    var $error;
    var $currency;
    var $rate;
    var $group;
    
	function Images()
	{
		parent::__construct();	
        
        $this->load->model('images_model','im');  
        $this->load->model('sets_model','cm');  
        $this->load->model('currencies_model','cmm'); 
        $this->load->model('groups_model','gm');  
        
        $this->api->save_sort_order('images');
  
        $this->id = $this->uri->segment(4);
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings();

        $this->set_group();
        $this->save_filter_data();  
        $this->set_params(); 
	}
    
    #------------------------------------------------------------------------------------------------
        
    function set_params()
    {
       $currency = $this->session->userdata('currency');
       
       if(!$currency['code'] || !$currency['rate']){
          $data = $this->cmm->get_default();  
          $sd['currency']['code'] = $this->currency = $data['code'];   
          $sd['currency']['rate'] = $this->rate = $data['rate']; 
          $this->session->set_userdata($sd); 
       }
       else{
         $this->currency = $currency['code'];
         $this->rate = $currency['rate'];
       }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function index()
    {   
        $image_id = intval($this->uri->segment(3));
        
        $data['preview'] = $this->langs.'/images/content/'.$image_id;
        $data['continue'] =  $this->session->userdata('search_page');
        $data['image'] = $this->im->get_image_info($image_id, $this->langs);      
        $data['currency'] = $this->currency;    

        $session_data['search_page'] = $this->uri->uri_string();   
        $this->session->set_userdata($session_data);
                
        $content['title'] = $data['image']['title'];
        $content['body'] = $this->load->view('images/content', $data, true);
        $this->out($content,0,0); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $filter = $this->get_filter_data();
        $order = $this->api->get_sort_order('images');
        $limit = $this->get_limit();
        $all = $this->im->get_images_count($this->langs, $filter);

        $data['images'] = $this->im->get_images_list($this->langs, $filter, $order, $limit);  
        $data['uri'] = $this->api->prepare_uri(); 
        $data['filter'] = $this->session->userdata('filter_images');  
        $data['lang'] = $this->langs;
        
        if($this->group['is_admin'])
        $data['clients'] = $this->im->get_image_clients_list();

        $pagination = $this->api->get_pagination('images/view',$all,$this->settings['perpage']);
        $this->set_content('images/view', $data, 'Images :: Editor account', $pagination); 
    }
    
    #------------------------------------------------------------------------------------------------
        
    function visible()
    {   
        $this->im->change_visible($this->input->post('id'));
        $this->api->log('log_image_visible', $this->input->post('id'));  
        redirect($this->langs.'/images/view');
    } 

    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
      $check_image =  $this->im->get_image($this->id, $this->langs);
      if ($check_image['client_id'] == $this->session->userdata('client_uid') || $check_image['client_id'] == $this->session->userdata('uid') || $this->group['is_admin'])
      {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->im->delete_images($ids, $this->langs);
        $this->api->log('log_image_delete', $ids);  
        redirect($this->langs.'/images/view');
      }
      else 
      {
        redirect ('images/view');
      }
    } 
        
    #------------------------------------------------------------------------------------------------
    
   function content()
   {
      $image = $this->im->get_image($this->id, $this->langs);
      $dir = $this->config->item('image_dir');
      
      $filename = $dir.$image['folder'].'/preview/'.$image['code'].'.'.$image['resource'];
      $src_img = imagecreatefromjpeg($filename);
      
      if ($this->config->config['enable_watermark'] == TRUE){
        $watermark_color = $this->config->config['watermark_color'];
        $font_size = $this->config->config['watermark_font_size'];
        $opacity = $this->config->config['watermark_opacity'];
        $padding = 10;
  
        $watermark = $this->im->get_watermark();
        $watermark_text = $watermark['text'];
        $watermark_image = 'data/upload/watermark/'.$watermark['image'];
  
        $size = getimagesize($filename);
        $width = $size[0];
        $height = $size[1];
        
        $x = 0;
        $y = 0;
            
        $wm_img = @imagecreatefromgif($watermark_image);
        
        $watermark_color    = str_replace('#', '', $watermark_color);
        
        $R = hexdec(substr($watermark_color, 0, 2));
        $G = hexdec(substr($watermark_color, 2, 2));
        $B = hexdec(substr($watermark_color, 4, 2));
        
        $color    = imagecolorclosest($src_img, $R, $G, $B);
    
        $watermark_layer = imagecreatetruecolor($width, $height);
    
        if(!$wm_img) {
          $centerwidth  = imagefontwidth($font_size);
          $centerheight = imagefontheight($font_size);
          $x += floor(($width - $centerwidth*strlen($watermark_text))/2);
          $y += ($height/2) - ($centerheight/2);
          imagestring($watermark_layer, $font_size, $x, $y, $watermark_text, $color);
        }
        else{
          $wm_size = getimagesize($watermark_image);
          $wm_width = $wm_size[0];
          $wm_height = $wm_size[1];
          
          $centerwidth  = $wm_width;
          $centerheight = $wm_height;
          $x += floor(($width - $centerwidth)/2);
          $y += (($height - $centerheight)/2);
    
          imagecopy($watermark_layer, $wm_img, $x, $y, 0, 0, $wm_width, $wm_height);
        }
        
        (ceil($centerwidth/2) != $centerwidth/2) ? $centerwidth++ : '';
        (ceil($centerheight/2) != $centerheight/2) ? $centerheight++ : '';
            
        $point_o_1_x = ($width < $height) ? $padding : (($width - $height)/2 + $padding);
        $point_o_1_y = ($width < $height) ? ($height - $width)/2 + $padding : $padding;
        $point_o_2_x = ($width < $height) ? $width - $padding : ($width - ($width - $height)/2 - $padding);
        $point_o_2_y = ($width < $height) ? ($height - $width)/2 + $padding : $padding;
        $point_o_3_x = ($width < $height) ? $padding : (($width - $height)/2 + $padding);
        $point_o_3_y = ($width < $height) ? $height - ($height - $width)/2 - $padding : $height - $padding;
        $point_o_4_x = ($width < $height) ? $width - $padding : ($width - ($width - $height)/2 - $padding);
        $point_o_4_y = ($width < $height) ? $height - ($height - $width)/2 - $padding : $height - $padding;
            
        $point_i_1_x = ($width/2) - ($centerheight/2) - $padding;
        $point_i_1_y = ($height/2) - ($centerheight/2) - $padding;
        $point_i_2_x = ($width/2) + ($centerheight/2) + $padding;
        $point_i_2_y = ($height/2) - ($centerheight/2) - $padding;
        $point_i_3_x = ($width/2) - ($centerheight/2) - $padding;
        $point_i_3_y = ($height/2) + ($centerheight/2) + $padding;
        $point_i_4_x = ($width/2) + ($centerheight/2) + $padding;
        $point_i_4_y = ($height/2) + ($centerheight/2) + $padding;
            
            
        imageline ($watermark_layer, $point_o_1_x, $point_o_1_y, $point_i_1_x, $point_i_1_y, $color);
        imageline ($watermark_layer, $point_o_2_x, $point_o_2_y, $point_i_2_x, $point_i_2_y, $color);
        imageline ($watermark_layer, $point_o_3_x, $point_o_3_y, $point_i_3_x, $point_i_3_y, $color);
        imageline ($watermark_layer, $point_o_4_x, $point_o_4_y, $point_i_4_x, $point_i_4_y, $color);
            
        imagecolortransparent($watermark_layer, imagecolorat($watermark_layer, 4, 4));
        imagecopymerge($src_img, $watermark_layer, 0, 0, 0, 0, $width, $height, $opacity);
      }
      
      header("Content-Disposition: filename={$this->source_image};");
      header("Content-Type: {$this->mime_type}");
      header('Content-Transfer-Encoding: binary');
      header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
      
      imagejpeg($src_img);  
      imagedestroy($src_img);
    }    

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
      $check_image =  $this->im->get_image($this->id, $this->langs);
      
      if($check_image['client_id'] == $this->session->userdata('uid') || $check_image['client_id'] == $this->session->userdata('client_uid') || $this->group['is_admin'] || !$this->id){
          if($this->input->post('save') && $this->check_details()){
            $this->im->save_image($this->id, $this->langs);
            
            if($this->id) $this->api->log('log_image_edit', $this->id);   
            else $this->api->log('log_image_new');
             
            redirect($this->langs.'/images/view');  
          }
            
          $data = ($this->error) ? $_POST : $this->im->get_image($this->id, $this->langs);  

          $data['id'] = ($this->id) ? $this->id : '';
          $data['lang'] = $this->langs;
          
          $this->set_content('images/edit', $data, 'Edit :: Images :: Editor account');  
      }
      else 
      {
        redirect ('images/view');
      }
    }

    #------------------------------------------------------------------------------------------------
        
    function disks()
    {
        if($this->input->post('save')) $this->im->save_disks($this->id, $this->input->post('id'));
    
        $data['disks'] = $this->im->get_disks_image($this->id, $this->langs);
        $data['column_count'] = 10;         
        $data['id'] = ($this->id) ? $this->id : ''; 

        $this->set_content('images/disks', $data, 'Disks :: Images :: Editor account');         
    }    
    
    #------------------------------------------------------------------------------------------------
        
    function thumbs()
    {
        if($this->input->post('save')) $this->im->upload_thumbs($this->id);
        if($this->input->post('delete')) $this->im->delete_thumbs($this->id, $this->langs);
    
        $data['res'] = $this->im->get_image_thumbs($this->id, $this->langs);
        $data['id'] = ($this->id) ? $this->id : ''; 
        
        $this->set_content('images/thumbs', $data, 'Thumbs :: Images :: Editor account'); 
    }

    #------------------------------------------------------------------------------------------------
        
    function resources()
    {
        $action = $this->uri->segment(5);   
        $item_id = $this->uri->segment(6);   
        
        if($action == 'edit') $data['res']  = $this->im->get_resource($this->id);
        if($action == 'delete') $this->im->delete_resource($this->id);
        if($this->input->post('save') && $this->check_res_details()) $this->im->save_resource($this->id);
    
        $data['resources'] = $this->im->get_image_resources($this->id);
        $data['id'] = ($this->id) ? $this->id : ''; 
        
        $this->set_content('images/resources', $data, 'Resources :: Images :: Editor account');
    }
               
    #------------------------------------------------------------------------------------------------
    
    function cats()
    {
        if($this->input->post('save')){
           $this->im->save_cats($this->id, $this->input->post('id'));
           //redirect($this->langs.'/images/view');
        }
         
        $data = $this->im->get_cats_image($this->id, $this->langs);
        $data['column_count'] = ceil($data['total']/5);
        $data['id'] = $this->id;

        $this->set_content('images/cats', $data, 'Cats :: Images :: Editor account');
    }
        
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('code') || !$this->input->post('price') || !$this->input->post('width') || !$this->input->post('height')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }
    
    #------------------------------------------------------------------------------------------------
            
    function check_res_details()
    {  
       if(!is_uploaded_file($_FILES['mimg']['tmp_name'])){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    } 
           
    #------------------------------------------------------------------------------------------------
            
    function save_filter_data()
    {  
       $words = $this->input->post('words');
       $active = $this->input->post('active');
       $rights = $this->input->post('rights');
       $client = $this->input->post('client');

       if($this->input->post('filter')){
         $temp['words'] = ($words) ? $words : '';
         $temp['active'] = ($active) ? $active : '';
         $temp['rights'] = ($rights) ? $rights : '';
         $temp['client'] = ($client) ? $client : '';
         
         $this->session->set_userdata(array('filter_images'=>$temp));
       }
    }
    
    #------------------------------------------------------------------------------------------------
            
    function get_filter_data($type=null)
    {  
        $filter_images = $this->session->userdata('filter_images');

        if($this->group['is_editor'])
        $where[] = 'li.client_id='.$this->session->userdata('client_uid');

        if($filter_images){
          $active = $filter_images['active'];  
          $words = $filter_images['words'];  
          $rights = $filter_images['rights'];  
          
          if($active) $where[] = ($active==1) ? 'li.active=1' : 'li.active=0';  
          if($rights) $where[] = ($rights) ? 'li.license='.$rights : 'li.license=0';
          if($words) $where[] = '(li.code like "%'.$words.'%" or lc.title like "%'.$words.'%" or lc.description like "%'.$words.'%" or lc.keywords like "%'.$words.'%")';  
          
          if($this->group['is_admin']){
            $client = $filter_images['client'];
            if($client) $where[] = 'li.client_id='.$client; 
          }
        }
        
        return count($where) ? ' and '.implode(' and ',$where) : '';
    } 
    
    #------------------------------------------------------------------------------------------------
        
    function set_group()
    {       
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->gm->get_group_by_user($uid); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_limit()
    {    
        return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
    }
        
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null, $type=1)
    {        
        $this->builder->output(array('content'=>$content,'pagination'=>$pagination,'error'=>$this->error),$type);    
    }
    
    #------------------------------------------------------------------------------------------------
    
    function set_content($method, $data, $title=null, $pagination=null)
    {
       if($this->group['is_editor']){   
          $data['menu'] = $this->load->view('main/ext/editormenu', array('lang'=>$this->langs), true);  
          $content['title'] = $title;
          $content['body'] = $this->load->view($method, $data, true);
          $type = 0;
        }
        else{
          $content = $this->load->view($method, $data, true);                  
          $type = 1;
        }
        $this->out($content, $pagination, $type); 
    }           
}
?>