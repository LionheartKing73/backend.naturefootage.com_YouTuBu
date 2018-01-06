<?php
class Features extends AppController {

  var $id;
  
  var $error;

  function Features() {
      parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);

    $this->load->model('features_model','fm');

    $this->id = intval($this->uri->segment(4));
    
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    show_404();
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $this->path = 'Manage system / Features';

    
    $data['features'] = $this->fm->get_list();

    $mode = $this->uri->segment(4) ? 3 : 1;
    if ($mode == 3) {
      $data['visual'] = '/visual';
    }

    $content = $this->load->view('features/view', $data, true);

    $this->out($content, '', $mode);
  }

  #------------------------------------------------------------------------------------------------

  function upload_file() {
    $file = $this->config->item('features_dir') . $this->id . '.'
      . $this->api->get_file_ext($_FILES['image']['name']);
    if (is_file($file) && !unlink($file)) {
      $this->error = "Can't delete existing file.";
      return false;
    }
    if (!copy($_FILES['image']['tmp_name'], $file)) {
      $this->error = "Can't copy file to target directory.";
      return false;
    }
    return true;
  }

  #------------------------------------------------------------------------------------------------

  function _save() {
    $data = $_POST;
    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
      $data['resource'] = $this->api->get_file_ext($_FILES['image']['name']);
    }

    if($this->id) {
      $data['id'] = $this->id;
      $this->fm->update($data);
      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        if (!$this->upload_file()) {
          return;
        }
      }
      $redir_uri = 'features/view';
      if ($this->uri->segment(5)) {
        $redir_uri .= '/visual';
      }
      redirect($redir_uri);
    } else {
      $this->id = $this->fm->add($data);
      if ($this->id) {
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
          if (!$this->upload_file()) {
            return;
          }
        }
        $redir_uri = 'features/view';
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
    
    $data['types'] = array('category', 'collection', 'keyword');

    if($this->input->post('save') && $this->check_details()) {
      $this->_save();
    }

    if($this->id) {
      $feature = $this->fm->get($this->id);
      $data['feature'] = $feature;
      $data['action'] = 'EDIT FEATURE';
    } else {
      $data['feature'] = $_POST;
      $data['action'] = 'ADD FEATURE';
    }
    

    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Library settings / Features / ' . $action;

    $content = $this->load->view('features/edit', $data, true);

    $mode_seg = $this->id ? 5 : 4;
    $mode = $this->uri->segment($mode_seg) ? 3 : 1;
    $this->out($content, '', $mode);
  }

  #------------------------------------------------------------------------------------------------

  function visible() {
    $this->fm->change_visible($this->input->post('id'));

    $visual = $this->uri->segment(4) ? '/visual' : '';
    redirect('/features/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function ord() {
    $ids = $this->input->post('ord');

    if(is_array($ids) && count($ids)) {
      foreach($ids as $id=>$ord) {
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_features', array('ord'=>intval($ord)));
      }
    }
    $visual = $this->uri->segment(4) ? '/visual' : '';
    redirect('/features/view' . $visual);
  }

  #------------------------------------------------------------------------------------------------

  function delete() {
    $ids = $this->input->post('id');
    if (!$ids) {
      $id = intval($this->uri->segment(4));
      $ids = array($id);
    }
    if ($ids) {
      $this->fm->delete($ids);
    }
    $visual = $this->uri->segment(5) ? '/visual' : '';
    redirect('/features/view' . $visual);
  }

}
