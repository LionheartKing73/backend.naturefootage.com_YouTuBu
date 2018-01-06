<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Imager {

    var $CI;
    
    // --------------------------------------------------------------------
    /**
     * Class constructor
    */
    
    function Imager()
    {
      $this->CI = &get_instance();   
      $this->CI->load->library('image_lib');  
    }

    // --------------------------------------------------------------------
    /**
     * Create image thumbnail
    */
    
    function create_thumbnails($path)
    {
        for($i=0; $i<=1; $i++){
          $this->create_thumbnail($path,$i);
        }
    }
      
    // --------------------------------------------------------------------
    /**
     * Create image thumbnail
    */
        
    function create_thumbnail($path, $type=0, $size=null)
    {  
       $sizes = $this->calculate_image_size($path, $type, $size); 
       
       if(!$type) $config['create_thumb'] = true; 
       else $config['create_thumb'] = false;   
        
       $config['source_image'] = $path;
       $config['maintain_ratio'] = true;
       $config['width'] = $sizes['width'];
       $config['height'] = $sizes['height'];

       $this->CI->image_lib->initialize($config);
       $this->CI->image_lib->resize();
       $this->CI->image_lib->clear();
    }
    
    // --------------------------------------------------------------------
    /**
     * Calculate image thumbnail size
    */
        
    function calculate_image_size($size, $limit)
    {
      if ($size[0] >= $size[1])
      {
        $temp['width'] = $limit;
        $temp['height'] = round($size[1]*$limit/$size[0]);
      }
      else 
      {
        $temp['width'] = round($size[0]*$limit/$size[1]); 
        $temp['height'] = $limit;            
      }
      return $temp;
    }
    
    // --------------------------------------------------------------------
    /**
     * Create watermark
    */
        
    function create_watermark($path)
    {
        $config['source_image'] = $path;
        $config['wm_text'] = 'Copyright ' . date('Y') . ' - '
          . $this->config->item('title') . ' Library';
        $config['wm_type'] = 'text';
        $config['wm_font_path'] = $_SERVER['DOCUMENT_ROOT'].'/system/fonts/arial.ttf';
        $config['wm_font_size'] = '12';
        $config['wm_font_color'] = 'ffffff';
        $config['wm_vrt_alignment'] = 'bottom';
        $config['wm_hor_alignment'] = 'center';
        $config['wm_padding'] = '0';
        
        $this->CI->image_lib->initialize($config); 
        $this->CI->image_lib->watermark();
    } 
    
    // --------------------------------------------------------------------
    
    function get_iptc ($file)
    {
      if(file_exists($file)){  
        $size = getimagesize($file, $info);
        $iptc = iptcparse ($info["APP13"]);
        
        if (isset($info["APP13"])) {
          $iptc = iptcparse($info["APP13"]);
          if (is_array($iptc)) {
          
            $data['title'] = $iptc["2#005"][0];
            $data['description'] = $iptc["2#120"][0];
            $data['keywords'] = $iptc["2#025"][0];
            $data['instruction'] = $iptc["2#040"][0];
            $data['creation_date'] = $iptc["2#055"][0];
            $data['author'] = $iptc["2#080"][0];
            $data['author_title'] = $iptc["2#085"][0];
            $data['author_desc'] = $iptc["2#122"][0];
            $data['city'] = $iptc["2#090"][0];
            $data['state'] = $iptc["2#095"][0];
            $data['country'] = $iptc["2#101"][0];
            $data['otr'] = $iptc["2#103"][0];
            $data['headline'] = $iptc["2#105"][0];
            $data['creator'] = $iptc["2#110"][0];
            $data['source'] = $iptc["2#115"][0]; 
            $data['copyright'] = $iptc["2#116"][0];   
         }
       }
       return $data;
      }
   }
   
   // --------------------------------------------------------------------
    
   function upload_preview($preview)
    {      
      $size = @getimagesize($preview['file']);
      
      if ($size && (($size[0] == $this->CI->config->item('preview_image_size') &&  $size[0] >= $size[1]) || ($size[1] == $this->CI->config->item('preview_image_size') &&  $size[0] <= $size[1])))
      {
        copy($preview['file'], $preview['dest']);
      }
      else 
      {
        $scr = imagecreatefromjpeg($preview['file']);
        
        $dest_size = $this->calculate_image_size($size, $this->CI->config->item('preview_image_size'));
        
        $dst = imagecreatetruecolor($dest_size['width'], $dest_size['height']);
        //imagecopyresized($dst, $scr, 0, 0, 0, 0, $dest_size['width'], $dest_size['height'], $size[0], $size[1]);
        imagecopyresampled($dst, $scr, 0, 0, 0, 0, $dest_size['width'], $dest_size['height'], $size[0], $size[1]);
        imagejpeg($dst, $preview['dest']);
      }
    }
    
    // --------------------------------------------------------------------
    
    function upload_thumb($thumb)
    {
      $size = @getimagesize($thumb['file']);

      if ($size && (($size[0] == $this->CI->config->item('thumb_image_size') &&  $size[0] >= $size[1]) || ($size[1] == $this->CI->config->item('thumb_image_size') &&  $size[0] <= $size[1])))
      {
        
        copy($thumb['file'], $thumb['dest']);
      }
      else 
      {
        $thumb['file'] = $thumb['preview'];
        
        $scr = imagecreatefromjpeg($thumb['file']);

        $size = getimagesize($thumb['file']);

        $dest_size = $this->calculate_image_size($size, $this->CI->config->item('thumb_image_size'));
  
        $dst = imagecreatetruecolor($dest_size['width'], $dest_size['height']);
        imagecopyresampled($dst, $scr, 0, 0, 0, 0, $dest_size['width'], $dest_size['height'], $size[0], $size[1]);
        imagejpeg($dst, $thumb['dest']);
      }
    }

       
}
?>