<?php
class Licensing extends CI_Controller {

    function Licensing() {
        parent::__construct();
        $this->langs = $this->uri->segment(1);
        $this->load->model('licensing_model');
    }
    #------------------------------------------------------------------------------------------------

    function index() {
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function view() {
        $this->path = 'Library Settings / Licensing';

        $data['lang'] = $this->langs;
        $data['licenses'] = $this->licensing_model->get();

        $content = $this->load->view('licensing/view', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function edit() {
        $id = intval($this->uri->segment(4));
        if (!in_array($id, array(1, 2, 3))) {
            redirect($this->langs . '/licensing/view');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->licensing_model->save($id, $_POST);
            $this->message = 'Saved';
        }

        $this->path = 'Library Settings / Licensing / Edit';

        $data['lang'] = $this->langs;
        $data['license'] = $this->licensing_model->find($id);

        $content = $this->load->view('licensing/edit', $data, true);
        $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function out($content=null, $pagination=null, $type=1) {
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
            'pagination'=>$pagination, 'error'=>$this->error, 'message'=>$this->message), $type);
    }
}
