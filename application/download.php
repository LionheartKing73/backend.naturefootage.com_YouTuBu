<?php
class Download extends Controller {

  var $langs;
  var $error;

  function Download() {
    parent::Controller();

    $this->load->model('download_model','dm');
    $this->langs = $this->uri->segment(1);
    $this->id = $this->uri->segment(3);
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    show_404();
  }

  #------------------------------------------------------------------------------------------------

  function items() {
    if ($this->id) $this->get_file();
    $downloads = $this->dm->get_downloads_list($this->session->userdata('client_uid'));

    $num = 0;

    foreach ($downloads as $key => $value) {
      $num++;
      $downloads[$key]['num'] = $num;
      $downloads[$key]['order_code'] = $this->api->order_format($value['order_id']);
    }

    $data['downloads'] = $downloads;
    $data['lang'] = $this->langs;

    $content['title'] = $this->lang->line('downloads');
    $content['body'] = $this->load->view('download/content', $data, true);
    $this->out($content);
  }

  #------------------------------------------------------------------------------------------------

  function get_file() {
    $file = $this->dm->get_download_file($this->session->userdata('client_uid'), $this->id);

    if ($file) {
      $path = ($file['type']==1) ? $this->config->item('image_dir') : $this->config->item('clip_dir');

      $dir = $path.$file['info']['folder'].'/';
      $filename = $file['info']['code'].'.HR.'.$file['info']['resource'];
      $path = $dir.'res/'.$filename;
      
      if ($file['type'] == 2) {
        $hr = $file['info']['res'] == 1;
        if (!$hr) {
          $filename = $file['info']['code'].'.'.$file['info']['width'].'x'.$file['info']['height'].'.'.$file['info']['filetype'];
          $path = $this->config->item('converted_clips') . $filename;
          if (!is_file($path)) {
            $result = $this->dm->convert_clip(
              $dir.'res/'.$file['info']['code'].'.HR.'.$file['info']['resource'],
              $path,
              $file['info']['width'].'x'.$file['info']['height']
            );
            if (!is_file($path)) {
              exit($result);
            }
          }
        }
      }

      $this->dm->update_item($this->id);
      header('Content-Type: application/force-download; name=' . $filename);
      header('Content-Transfer-Encoding: Binary');
      header('Content-Disposition: attachment; filename=' . $filename);
      header('Content-Length: ' . filesize($path));
      readfile($path);
      exit;
    }
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null) {
    $this->builder->output(array('content'=>$content,'error'=>$this->error),$type);
  }
}