<?php

class Videositemap extends CI_Controller {

    var $langs;
    var $error;
    var $method;
    var $message;

    function Videositemap()
    {

        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

        $this->load->model('videositemap_model','vs');
        $this->load->model('settings_model','sm');
        $this->load->model('clips_model', 'cm');

        $this->langs = $this->uri->segment(1);
        $this->method = $this->uri->segment(3);

    }

   #---------------------------------------------------------------------------------#

   function index () {

      $this->view();

   }

   #---------------------------------------------------------------------------------#

   function view () {

        if(isset($_REQUEST['save'])) {
            $this->save();
        } else if (isset($_REQUEST['create'])) {
            $this->save();
            $this->create();
        } else if (isset($_REQUEST['delete'])) {
            $this->delete();
        }

        $data['lang'] = $this->langs;

        $this->path = 'Manage system / Video sitemap';

        $data['video_sitemap_filepath'] = $this->sm->get_setting('video_sitemap_filepath');
        $data['video_sitemap_autocreate'] = $this->sm->get_setting('video_sitemap_autocreate');
        $data['video_sitemap_www'] = $this->sm->get_setting('video_sitemap_www');

        $data['filemap_info'] = $this->vs->get_filemap_info();

        $content = $this->load->view('videositemap/view', $data, true);
        $this->out($content);

   }

   #---------------------------------------------------------------------------------#

   function save () {

       $this->db_master->where('name', 'video_sitemap_filepath');
       $this->db_master->update('lib_settings', array('value'=>$this->input->post('video_sitemap_filepath')));

       $this->db_master->where('name', 'video_sitemap_autocreate');
       $this->db_master->update('lib_settings', array('value'=>(boolean)$this->input->post('video_sitemap_autocreate')));

       $this->db_master->where('name', 'video_sitemap_www');
       $this->db_master->update('lib_settings', array('value'=>(boolean)$this->input->post('video_sitemap_www')));

   }

   #---------------------------------------------------------------------------------#

   function create() {
      
        $clips = $this->cm->get_clips_list($this->config->item('default_language'), '', '', 'limit 50000');

        $error = $this->vs->create_map($clips);

        if($error)
            $this->error = $error;
        else
            $this->message = 'File has been created.';

   }

   #---------------------------------------------------------------------------------#

   function delete() {

        $error = $this->vs->delete_map();

        if($error)
            $this->error = $error;
        else
            $this->message = 'File has been deleted.';

   }

   #---------------------------------------------------------------------------------#

    function out($content=null, $pagination=null)
    {
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error, 'message'=>$this->message), 1);
    }


}

?>
