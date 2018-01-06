<?php

class Publication extends CI_Controller {

  var $id;
  var $langs;
  var $settings;
  var $error;
  
  function Publication() {
    parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);

    $this->load->model('publication_model');
    $this->api->save_sort_order('publication');

    $this->id = $this->uri->segment(4);
    $this->mode = $this->uri->segment(5);
    $this->langs = $this->uri->segment(1);
    $this->settings = $this->api->settings();

    $this->save_filter_data();
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    $this->content();
  }

  #------------------------------------------------------------------------------------------------

  function visual() {
    $this->path = 'Manage system / Visual editing';
    $content = $this->load->view('publication/visual', $data, true);
    $this->out($content);
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $this->path = 'Manage system / Pages';
  
    $filter = $this->get_filter_data();
    $order = $this->api->get_sort_order('publication');
    $limit = $this->get_limit();

    $all = $this->publication_model->get_pages_count($this->langs, $filter);

    $data['pages'] = $this->publication_model->get_pages_list($this->langs, $filter, $order, $limit);
    $data['url_suffix'] = $this->config->item('url_suffix');
    $data['uri'] = $this->api->prepare_uri();
    $data['filter'] = $this->session->userdata('filter_publication');
    $data['lang'] = $this->langs;

    $content = $this->load->view('publication/view', $data, true);
    $pagination = $this->api->get_pagination('publication/view',$all,$this->settings['perpage']);

    $this->out($content, $pagination);
  }

  #------------------------------------------------------------------------------------------------

  function visible() {
    $this->publication_model->change_visible($this->input->post('id'));
    $this->api->log('log_page_visible', $this->input->post('id'));
    redirect($this->langs.'/publication/view');
  }

  #------------------------------------------------------------------------------------------------

  function delete() {
    if($this->id) $ids[] = $this->id;
    else $ids = $this->input->post('id');

    $this->publication_model->delete_pages($ids);
    $this->api->log('log_page_delete', $ids);
    redirect($this->langs.'/publication/view');
  }

  #------------------------------------------------------------------------------------------------

    function content() {
//        $row = 1;
//        if (($handle = fopen('D:\WebServers\home\fsearch\www\pricing.csv', "r")) !== FALSE) {
//            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
//                if($row > 1){
//                    $ndata = array();
//                    $ndata['admin'] = $data[0];
//                    $ndata['owner'] = $data[1];
//                    $ndata['rate_categories'] = $data[2];
//                    $ndata['use'] = $data[3];
//                    $ndata['territory'] = $data[4];
//                    $ndata['term'] = $data[5];
//                    $ndata['pl1'] = str_replace('$', '', $data[6]);
//                    $ndata['pl2'] = str_replace('$', '', $data[7]);
//                    $ndata['pl3'] = str_replace('$', '', $data[8]);
//                    $ndata['pl4'] = str_replace('$', '', $data[9]);
//                    $ndata['pl5'] = str_replace('$', '', $data[10]);
//                    $ndata['description'] = $data[11];
//                    $ndata['exclusions'] = $data[12];
//                    $ndata['clip_minimum'] = $data[13];
//                    $ndata['display'] = ((bool)$data[14]) ? 1 : 0;
//                    $this->db_master->insert('lib_license_use', $ndata);
//                }
//                else{
//                    $row++;
//                }
//            }
//            fclose($handle);
//        }
//        exit();
//        if (($handle = fopen('D:\WebServers\home\fsearch\www\delivery_options.csv', "r")) !== FALSE) {
//            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
//                $ndata = array();
//                $ndata['code'] = trim($data[1]);
//                $ndata['categories'] = trim($data[2]);
//                $ndata['format'] = trim($data[3]);
//                $ndata['source'] = trim($data[4]);
//                $ndata['destination'] = trim($data[5]);
//                $ndata['conversion'] = (trim($data[6]) == 'FALSE') ? 0 : 1;
//                $ndata['workflow'] = (int)trim($data[7]);
//                $ndata['description'] = trim($data[8]);
//                $ndata['price'] = trim(str_replace('$', '', $data[9]));
//                $ndata['cost_extra_clips'] = trim(str_replace('$', '', $data[10]));
//                $ndata['delivery'] = trim($data[11]);
//                $ndata['timedelay'] = trim($data[12]);
//                $ndata['method'] = trim($data[13]);
//                $ndata['display_order'] = (int)trim($data[14]);
//                $this->db_master->insert('lib_delivery_options', $ndata);
//            }
//            fclose($handle);
//        }
//        exit();
        $content = $this->publication_model->get_page_content($this->id, $this->langs);

        if (!$content['meta_title']) {
            $content['meta_title'] = $content['title'];
        }
        if(!$content['meta_desc']) {
            $content['meta_desc'] = $content['meta_title'];
        }
        if(!$content['meta_keys']) {
            $content['meta_keys'] = $content['meta_title'];
        }

        $content['meta_title'] = $content['meta_title'] . ', stock footage, video library';
        $content['meta_desc'] = $content['meta_desc'] . ', stock footage, ' . $this->api->get_seo_keys(3, 2);
        $content['meta_keys'] = $content['meta_keys'] . ', ' . $this->api->get_seo_keys(3, 2);

        $data['body'] = $content['body'];
        $data['pageid'] = $content['page_id'];

        $data['visual_mode'] = ($this->api->permission()) ? 1 : 0;

        if ($content['page_id'] == 1)
        {
            //$data['step1'] = $this->api->get_block(1, $this->langs);
            //$data['step2'] = $this->api->get_block(2, $this->langs);
            //$data['step3'] = $this->api->get_block(3, $this->langs);

            /*if ($this->config->item('use_qt')) {
                $this->load->model('banners_model');
                $data['banners'] = $this->banners_model->get_playlist();
            }*/
            $this->load->model('banners_model');
            $data['main_banner'] = $this->load->view('main/ext/mainbanner', array('banners' => $this->banners_model->get_playlist(), 'visual_mode' => $data['visual_mode']), true);

            $content['template'] = 'start';
            $content['body'] = $this->load->view('publication/home', $data, true);
            //$content['add_css'] = '/data/css/home.css';
            //$content['add_js'] = array('/data/js/swfobject.js');
        }
        else
        {
            $content['body'] = $this->load->view('publication/content', $data, true);
        }

        if (substr($this->uri->segment(2), 0, 1) == '_') {
            $content['template'] = 'blank';
            $content['add_css'] = '/data/css/dialog.css';
        }

        $this->out($content, 0, 0);
    }

  #------------------------------------------------------------------------------------------------

  function edit() {
    $mode = ($this->mode) ? 3 : 1;
    
    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Manage system / Pages / ' . $action;
    

    if($this->input->post('save') && $this->check_details()) {
      $this->publication_model->save_page($this->id, $this->langs);

      if($this->id) $this->api->log('log_page_edit', $this->id);
      else $this->api->log('log_page_new');

      if(!$this->mode)
        redirect($this->langs.'/publication/view');
    }

    $data = ($this->error) ? $_POST : $this->publication_model->get_page($this->id,$this->langs);
    $data['id'] = ($this->id) ? $this->id : '';
    $data['lang'] = $this->langs;
    $data['visual'] = ($this->mode=='visual');

    $content = $this->load->view('publication/edit', $data, true);
    $this->out($content,'',$mode);
  }

  #------------------------------------------------------------------------------------------------

  function check_details() {
    if(!$this->input->post('title') || !$this->input->post('body')) {
      $this->error = $this->lang->line('empty_fields');
      return false;
    }
    return true;
  }

  #------------------------------------------------------------------------------------------------

  function save_filter_data() {
    $words = $this->input->post('words');
    $active = $this->input->post('active');

    if($this->input->post('filter')) {
      $temp['words'] = ($words) ? $words : '';
      $temp['active'] = ($active) ? $active : '';

      $this->session->set_userdata(array('filter_publication'=>$temp));
    }
  }

  #------------------------------------------------------------------------------------------------

  function get_filter_data($type=null) {
    $filter_publication = $this->session->userdata('filter_publication');

    if($filter_publication) {

      $active = $filter_publication['active'];
      $words = $filter_publication['words'];

      if($active) $where[] = ($active==1) ? 'lp.active=1' : 'lp.active=0';
      if($words) $where[] = '(lpc.title like "%'.$words.'%" or lpc.body like "%'.$words.'%" or lp.alias1 like "%'.$words.'%" or lp.alias2 like "%'.$words.'%")';

      if(count($where)) return ' and '.implode(' and ',$where);
    }
    return '';
  }

  #------------------------------------------------------------------------------------------------

  function get_limit() {
    return ' limit '.intval($this->uri->segment(4)).','.$this->settings['perpage'];
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null, $type=1) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path,
      'pagination'=>$pagination, 'error'=>$this->error), $type);
  }
}
