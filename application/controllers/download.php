<?php
class Download extends AppController {

    var $error;

    function Download() {
        parent::__construct();

        $this->load->model('download_model');

        $this->id = $this->uri->segment(3);
    }

    #------------------------------------------------------------------------------------------------

    function index() {
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function items() {
        $downloads = $this->download_model->get_downloads_list($this->session->userdata('client_uid'), $this->langs);

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

}