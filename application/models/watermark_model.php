<?php
class Watermark_model extends CI_Model {

    function Watermark_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_watermark()
    {
        $query = $this->db->query('select * from lib_watermark');
        $row = $query->result_array();
        return $row[0];
    }
    
    #------------------------------------------------------------------------------------------------
    
    function upload_image()
    {
        if(is_uploaded_file($_FILES['image']['tmp_name'])){
          $image_dir = $this->config->item('wm_dir'); 
          
          if ($_FILES['image']['type'] == 'image/png')
          {
            $filename = 'watermark.png';
          }
          else if ($_FILES['image']['type'] == 'image/gif')
          {
            $filename = 'watermark.gif';
          }
          else 
          {
            return false;
          }

          $path = $image_dir.$filename;
          $this->db_master->where('id', 1);
          $this->db_master->update('lib_watermark', array('image'=>$filename));
        
          $path = $image_dir.$filename;
          
          @unlink($image_dir.'watermark.gif');
          @unlink($image_dir.'watermark.png');
          @copy($_FILES['image']['tmp_name'], $path);
          return true;  
        } 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function save_watermark ()
    {
      $this->db_master->where('id', 1);
      $this->db_master->update('lib_watermark', array('text'=>$this->input->post('text')));
    }
    
    #------------------------------------------------------------------------------------------------
    
    function delete_watermark ()
    {
      $image_dir = $this->config->item('wm_dir'); 
      @unlink($image_dir.'watermark.gif');
      @unlink($image_dir.'watermark.png');
          
      $this->db_master->where('id', 1);
      $this->db_master->update('lib_watermark', array('image'=>''));
    }

}
