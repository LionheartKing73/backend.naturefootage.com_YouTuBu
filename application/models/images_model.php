<?php
class Images_model extends CI_Model {
    
    function Images_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        
        $this->load->library('imager', $imager); 
        $this->load->model('editors_model','em');
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_images_count($lang, $filter)
    {
        $query = $this->db->query('select count(distinct(li.id)) as total from lib_users as lu, lib_images_cats as lic right join lib_images as li on li.id=lic.image_id left join lib_images_content as lc on li.id=lc.image_id and lc.lang='.$this->db->escape($lang).' where lu.id=li.client_id and li.id >0 '.$filter.$order.$limit);
        $row = $query->result_array();

        return $row[0]['total'];
    }

    #------------------------------------------------------------------------------------------------
    
    function get_images_list($lang, $filter, $order=null, $limit=null)
    {  
        $query = $this->db->query('select distinct(li.id), li.*, DATE_FORMAT(li.ctime, \'%d.%m.%Y %T\') as ctime, lc.title, lc.description, lc.keywords, lc.copyright, lu.login as folder from lib_users as lu, lib_images_cats as lic right join lib_images as li on li.id=lic.image_id left join lib_images_content as lc on li.id=lc.image_id and lc.lang='.$this->db->escape($lang).' where lu.id=li.client_id and li.active!=2 '.$filter.$order.$limit);
        $rows = $query->result_array();     

        foreach($rows as $k=>$v){
           $rows[$k]['rights'] = ($v['license']==1) ? $this->lang->line('license_rf') : $this->lang->line('license_rm');
           $rows[$k]['thumb'] = $this->get_image_path($v, 1);
           $rows[$k]['url'] = $lang.'/images/'.$v['id'].$this->config->item('url_suffix');
           $rows[$k]['res'] = $this->get_image_resources($rows[$k]['id']);
        }
        
        return $rows;
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_image_clients_list()
    {  
        $query = $this->db->query('select DISTINCT u.id as id, u.login as name from lib_images as l inner join lib_users as u on l.client_id=u.id');
        $rows = $query->result_array();     
        
        return $rows;
    }
    
    #------------------------------------------------------------------------------------------------
    
    function change_visible($ids)
    {     
        if(count($ids)){      
            foreach($ids as $id){
               $this->db_master->query('UPDATE lib_images set active = !active where id='.$id);
            }
        }
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function delete_images($ids, $lang)
    {    
      if(count($ids)){
        foreach((array)$ids as $id)
        {
          $this->delete_thumbs($id, $lang);
          $this->delete_resource($id);
          
          $this->db_master->delete('lib_images', array('id'=>$id));
          $this->db_master->delete('lib_images_content', array('image_id'=>$id));
        }
      }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_image($id, $lang)
    {      
        $query = $this->db->query('select li.*, lic.title, lic.description, lic.keywords, lic.copyright, lu.login as folder from lib_users as lu, lib_images as li left join lib_images_content as lic on li.id=lic.image_id and lic.lang='.$this->db->escape($lang).' where lu.id=li.client_id and li.id='.intval($id));
        $row = $query->result_array();
        return $row[0]; 
    }    

    #------------------------------------------------------------------------------------------------
    
    function get_image_info($id, $lang)
    {          
        $image = $this->get_image($id, $lang);
  
        $image['res'] = $this->get_image_resources($id);
        $image['color'] = ($image['color']) ? $this->lang->line('colored') : $this->lang->line('black_white');
        $image['rights'] = ($image['license']) ? 'rm' : 'rf';
        $image['keywords'] = $this->get_linked_keywords($lang, $image['keywords']);
        $image['price'] = $this->api->price_format($image['price']*$this->rate);
        $image['owner'] = $this->em->get_profile($image['client_id']);

        return $image;
    }  
        
    #------------------------------------------------------------------------------------------------
    
    function save_image($id, $lang)
    {
        $data_image['client_id'] = $this->input->post('client_id');
        $data_image['code'] = $this->input->post('code');
        $data_image['license'] = $this->input->post('license'); 
        $data_image['color'] = $this->input->post('color'); 
        $data_image['price'] = $this->input->post('price'); 
        $data_image['width'] = $this->input->post('width'); 
        $data_image['height'] = $this->input->post('height'); 

        $data_content['image_id'] = $id;
        $data_content['lang'] = $lang;
        $data_content['title'] = $this->input->post('title');
        $data_content['description'] = $this->input->post('description');
        $data_content['keywords'] = $this->input->post('keywords');
        $data_content['copyright'] = $this->input->post('copyright');
     
        if($id){
          $this->db_master->where('id', $id);
          $this->db_master->update('lib_images', $data_image);
              
          $query = $this->db->get_where('lib_images_content', array('image_id'=>$id,'lang'=>$lang));
          $row = $query->result_array();
              
          if(count($row)){
             $this->db_master->where('id', $row[0]['id']);
             $this->db_master->update('lib_images_content', $data_content);
          }
          else
             $this->db_master->insert('lib_images_content', $data_content);
        }
        else{  
            
           if($this->session->userdata('uid')){
             $data_image['client_id'] = $this->session->userdata('uid');
             $data_image['active'] = 1;
           }
           else{
             $data_image['client_id'] = $this->session->userdata('client_uid');
             $data_image['active'] = 2;
           }
            
           $data_image['ctime'] = date('Y-m-d H:i:s');  
           $this->db_master->insert('lib_images', $data_image);
            
           $data_content['image_id'] = $this->db_master->insert_id();
           $this->db_master->insert('lib_images_content', $data_content);
        } 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_cats_image($id, $lang)
    {  
        $query = $this->db->query('select lc.id, lc.parent_id, lcc.title, lic.image_id as checked from lib_cats as lc left join lib_cats_content as lcc on lc.id=lcc.cat_id and lcc.lang='.$this->db->escape($lang).' left join lib_images_cats as lic on lc.id=lic.cat_id and lic.image_id='.$id.' where lc.id>0'.$filter.' order by lc.parent_id, lc.ord');
        $rows = $query->result_array();
        $data['total'] = $query->num_rows(); 
        
        foreach($rows as $row){
          $row['title'] = ($row['title']) ? $row['title'] : '-'; 
           
          if($row['parent_id']) $data['cats'][$row['parent_id']]['child'][] = $row;
          else $data['cats'][$row['id']] = $row;
        }
        return $data; 
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function save_cats($id, $ids)
    {  
        $this->db_master->delete('lib_images_cats', array('image_id'=>$id));
        
        foreach((array)$ids as $cat_id){
          $data['image_id'] = $id;
          $data['cat_id'] = $cat_id;
          
          $this->db_master->insert('lib_images_cats', $data);
        }
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_code($id)
    {       
        $query = $this->db->get_where('lib_images',array('id'=>$id));
        $row = $query->result_array();
        return $row[0]['code'];  
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_user_folder($id)
    {       
        $query = $this->db->query('select lu.login from lib_users as lu, lib_images as li where li.client_id=lu.id and li.id='.$id);
        $row = $query->result_array();
        return $row[0]['login'];  
    } 
    
    #------------------------------------------------------------------------------------------------
    
    function get_license($id)
    {       
        $query = $this->db->get_where('lib_images',array('id'=>$id));
        $row = $query->result_array();
        return $row[0]['license'];  
    }    
        
    #------------------------------------------------------------------------------------------------
    
    function upload_thumbs($id)
    {
        if(is_uploaded_file($_FILES['mimg']['tmp_name'])){

          $code = $this->get_code($id);
          $folder = $this->get_user_folder($id);  
          $dest_dir = $this->config->item('image_dir').$folder.'/'; 
          $ext = strtolower(substr($_FILES['mimg']['name'],-3));
          
          $preview['file'] = $_FILES['mimg']['tmp_name'];
          $preview['dest'] = $dest_dir.'preview/'.$code.'.'.$ext;
          $this->imager->upload_preview($preview);

          $thumb['dest'] = $dest_dir.'thumb/'.$code.'.'.$ext;
          $thumb['preview'] = $dest_dir.'preview/'.$code.'.'.$ext;
          $this->imager->upload_thumb($thumb);
          
          $this->im->update_resource($id, $ext);
        } 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function update_resource($id, $resource='')
    {  
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_images', array('resource'=>$resource));
    }

    #------------------------------------------------------------------------------------------------
    
    function get_image_thumbs($id, $lang)
    { 
        $data = $this->get_image($id, $lang); 

        $temp['thumb'] = $this->get_image_path($data, 1);
        $temp['preview'] = $this->get_image_path($data, 0);

        return $temp;
    }
    
    #------------------------------------------------------------------------------------------------
    
    function delete_thumbs($id, $lang)
    {  
       $data = $this->get_image($id, $lang);   
       
       $temp['thumb'] = $this->get_image_path($data, 1, 1);
       $temp['preview'] = $this->get_image_path($data, 0, 1);               
      
       if($temp['thumb']) @unlink($temp['thumb']);
       if($temp['preview']) @unlink($temp['preview']);
       
       $this->im->update_resource($id);
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_image_path($data, $is_thumb=null, $get_dir=0)
    {
        $type = ($is_thumb) ? 'thumb' : 'preview';
        $path = $this->config->item('image_path');
        $dir = $this->config->item('image_dir');    
        
        $file = $data['folder'].'/'.$type.'/'.$data['code'].'.'.$data['resource']; 
        
        if($get_dir){
           if(file_exists($dir.$file)) return $dir.$file;
           else return 0; 
        }
        else {
           if(file_exists($dir.$file)) return $path.$file;
           else return $path.'no_image.gif'; 
        }
    }

    #------------------------------------------------------------------------------------------------
    
    function get_image_resources($id)
    { 
        $query = $this->db->query('select *, DATE_FORMAT(ctime, \'%d.%m.%Y %T\') as ctime from lib_images_res where image_id='.$id);
        return $query->result_array();     
    } 

    #------------------------------------------------------------------------------------------------
    
    function get_resource($id)
    { 
        $query = $this->db->query('select * from lib_images_res where image_id='.$id);
        $row = $query->result_array(); 
        return $row[0];    
    }  

    #------------------------------------------------------------------------------------------------
        
    function save_resource($id)
    {
        $data_content['image_id'] = $id;

        if($this->get_resource($id))
        {
           $this->db_master->where('id', $item_id);
           $this->db_master->update('lib_images_res', $data_content);
        }
        else
        {   
           $data_content['ctime'] = date('Y-m-d H:i:s');  
           $this->db_master->insert('lib_images_res', $data_content);
        } 
        
        $this->upload_resource($id);
    }
    
    #------------------------------------------------------------------------------------------------
        
    function delete_resource($id)
    {
        $dest_dir = $this->config->item('image_dir').$this->session->userdata('login').'/';
        $data = $this->get_resource($id);
        $code = $this->get_code($id);

        $path = $dest_dir.'res/'.$code.'.'.$data['resource'];
        @unlink($path);  
        $this->db_master->delete('lib_images_res', array('image_id'=>$id));
    }  

    #------------------------------------------------------------------------------------------------
        
    function upload_resource($id)
    {
        if(is_uploaded_file($_FILES['mimg']['tmp_name'])){
          $dest_dir = $this->config->item('image_dir').$this->session->userdata('login').'/';
          $code = $this->get_code($id);
          $ext = strtolower(substr($_FILES['mimg']['name'],-3));
          $path = $dest_dir.'res/'.$code.'.'.$ext;

          @copy($_FILES['mimg']['tmp_name'], $path);  
          
          $this->db_master->where('id', $item_id);
          $this->db_master->update('lib_images_res', array('resource'=>$ext));
        }  
    } 

    #------------------------------------------------------------------------------------------------
        
    function search($lang, $filter, $limit)
    {   
        $data['all'] = $this->get_images_count($lang, $filter);         
        $data['results'] = $this->get_images_list($lang, $filter, $limit);         
          
        return $data;
    }

    #------------------------------------------------------------------------------------------------
    
    function get_watermark()
    {   
      $query = $this->db->query('select * from lib_watermark');
      $row = $query->result_array(); 
      return $row[0]; 
    }
    
    #------------------------------------------------------------------------------------------------------------   

    function get_linked_keywords($lang, $keywords)
    {
       $words = explode(',', $keywords);
       $ext = $this->config->item('url_suffix');
       shuffle($words);
    
       foreach($words as $k=>$v){
         $v = trim($v);
         $temp[$k] = '<a href="'.$lang.'/search/words/'.urlencode($v).$ext.'">'.$v.'</a>'; 
         if($k > 30) break; 
      }
    
      return implode(', ', $temp);  
    }
    
    #------------------------------------------------------------------------------------------------------------   

    function get_disks_image($id, $lang)
    {   
        $query = $this->db->query('select ld.*, ldc.title, ldi.disk_id as checked from lib_disks as ld left join lib_disks_content as ldc on ld.id=ldc.disk_id and ldc.lang='.$this->db->escape($lang).' left join lib_disks_items as ldi on ld.id=ldi.disk_id and ldi.item_type=1 and ldi.item_id='.$id.' where ld.id>0');
        return $query->result_array();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function get_image_price($id)
    {  
        $query = $this->db->query('select price from lib_images where id='.$id);
        $row = $query->result_array();
        return $row[0]['price']; 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function save_disks($item_id, $ids)
    {  
        $this->db_master->delete('lib_disks_items', array('item_id'=>$item_id,'item_type'=>1));
        
        foreach((array)$ids as $disk_id){
          $data['disk_id'] = $disk_id; 
          $data['item_id'] = $item_id;  
          $data['item_type'] = 1;  
          
          $this->db_master->insert('lib_disks_items', $data);
        }
    }      
}
