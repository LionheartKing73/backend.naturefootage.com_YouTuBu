<?php

class Banners extends CI_Controller {

  var $id;
  var $langs;
  var $error;

  function Banners() {
    parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);

    $this->load->model('banners_model','bm');

    $this->id = intval($this->uri->segment(4));
    $this->langs = $this->uri->segment(1);
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    show_404();
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $this->path = 'Manage system / Banners';

    $data['lang'] = $this->langs;
    $data['banners'] = $this->bm->get_banners_list();
    $data['banners_sort'] = $this->bm->get_banners_sort();
    
    $mode = $this->uri->segment(4) ? 3 : 1;
    if ($mode == 3) {
      $data['visual'] = '/visual';
    }

    $content = $this->load->view('banners/view', $data, true);
    
    $this->out($content, '', $mode);
  }

  #------------------------------------------------------------------------------------------------

  function upload_file() {
    $file = $this->config->config['banner_dir'] . $_FILES['clip']['name'];
    if (is_file($file) && !unlink($file)) {
      $this->error = "Can't delete existing file.";
      return false;
    }
    if (!copy($_FILES['clip']['tmp_name'], $file)) {
      $this->error = "Can't copy file to target directory.";
      return false;
    }
    $this->api->log('log_banners_upload', $this->id);
    return true;
  }

  #------------------------------------------------------------------------------------------------

  function _save() {
    if (is_uploaded_file($_FILES['clip']['tmp_name'])) {
      if (!$this->upload_file()) {
        return;
      }
    }

    $data = $_POST;
    if (is_uploaded_file($_FILES['clip']['tmp_name'])) {
      $data['resource'] = $_FILES['clip']['name'];
    }

    if($data['resource']){
        $data['type'] = $this->api->get_file_ext($data['resource']);
    }

    if($this->id) {      
      $data['id'] = $this->id;
      $this->bm->update_banner($data);
      $this->api->log('log_banners_update', $this->id);
      $redir_uri = $this->langs . '/banners/view';
      if ($this->uri->segment(5)) {
        $redir_uri .= '/visual';
      }
      redirect($redir_uri);
    } else {
      $this->id = $this->bm->add_banner($data);
      if ($this->id) {
        $this->api->log('log_banners_add', $this->id);
        $redir_uri = $this->langs . '/banners/view';
        if ($this->uri->segment(4)) {
          $redir_uri .= '/visual';
        }
        redirect($redir_uri);
      } else {
        $this->error = 'Database error occured. Please try again.';
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function check_details() {
    if(!$this->input->post('name')) {
      $this->error = $this->lang->line('empty_fields');
      return false;
    }
    return true;
  }

  #------------------------------------------------------------------------------------------------

  function edit() {
    $this->id = intval($this->uri->segment(4));

    if($this->input->post('save') && $this->check_details()) {
      $this->_save();
    }

    if($this->id) {
      $banner = $this->bm->get_banner($this->id);
      if($banner['type']) {
        $filename = $this->config->config['banner_dir'] . $banner['resource'];
        $banner['file_exists'] = is_file($filename);
      }
      $data['banner'] = $banner;
      $data['action'] = 'EDIT BANNER';
    } else {
      $data['banner'] = $_POST;
      $data['action'] = 'ADD BANNER';
    }
    $data['lang'] = $this->langs;
    
    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Manage system / Banners / ' . $action;

    $content = $this->load->view('banners/edit', $data, true);

    $mode_seg = $this->id ? 5 : 4;
    $mode = $this->uri->segment($mode_seg) ? 3 : 1;
    $this->out($content, '', $mode);
  }

  #------------------------------------------------------------------------------------------------

  function visible() {
    $this->bm->change_visible($this->input->post('id'));
    $this->api->log('log_banners_visible', $this->input->post('id'));

    $visual = $this->uri->segment(4) ? '/visual' : '';
    redirect($this->langs.'/banners/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function ord() {
    $ids = $this->input->post('ord');

    if(is_array($ids) && count($ids)) {
      foreach($ids as $id=>$ord) {
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_banners', array('ord'=>intval($ord)));
      }
      $this->api->log('log_banners_ord');
    }
    $visual = $this->uri->segment(4) ? '/visual' : '';
    redirect($this->langs.'/banners/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function sort() {
    $banners_sort = $this->input->post('banners_sort');
    if ($banners_sort) {
      $this->bm->set_banners_sort($banners_sort);
      $this->api->log('log_banners_sort');
    }
    $visual = $this->uri->segment(4) ? '/visual' : '';
    redirect($this->langs.'/banners/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function delete() {
    $ids = $this->input->post('id');
    if (!$ids) {
      $id = intval($this->uri->segment(4));
      $ids = array($id);
    }
    if ($ids) {
      $this->api->log('log_banners_delete', $ids);
      $this->bm->delete_banner($ids);
    }
    $visual = $this->uri->segment(5) ? '/visual' : '';
    redirect($this->langs.'/banners/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null, $type=1) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path,
      'pagination'=>$pagination, 'error'=>$this->error), $type);
  }
}
