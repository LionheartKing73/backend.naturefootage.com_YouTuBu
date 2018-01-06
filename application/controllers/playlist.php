<?php

class Playlist extends CI_Controller {

  function index() {
    $this->load->model('banners_model');

    $data['banner_path'] = $this->config->item('base_url') . $this->config->item('banner_path');
    $data['banners'] = $this->banners_model->get_playlist();
    $datasource = $this->load->view('playlist/view', $data, true);

    header('Content-type: application/xml');
    echo $datasource;
  }

}