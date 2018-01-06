<?php
class Search extends CI_Controller {

    var $langs;
    var $settings;

    function Search() {
        parent::__construct();
        $this->load->model('search_model');
        $this->settings = $this->api->settings();
        $this->langs = $this->uri->segment(1);
    }

    function index() {
        if ($this->input->post('phrase')) {
            $phrase = trim($this->input->post('phrase'));
            $this->search_model->save_search_stat($phrase, $this->langs);
            redirect($this->langs . '/search/words/' . urlencode($phrase));
        }


        $page = 0;
        $filter_map = $this->uri->uri_to_assoc(3);
        if (!empty($filter_map['words'])) {
            $filter_map['words'] = urldecode($filter_map['words']);
        }

        foreach (array_keys($filter_map) as $filter_map_key) {
            if ($filter_map[$filter_map_key] === false) {
                $page = intval($filter_map_key);
                unset($filter_map[$filter_map_key]);
            }
        }

        $search_uri = $this->langs . '/search/' . $this->uri->assoc_to_uri($filter_map);

        $perpage = $this->input->post('perpage', true);
        if($perpage) {
            $session_data['search_perpage'] = $perpage;
            $this->session->set_userdata($session_data);
            redirect($search_uri);
        }
        $ses_pp = $this->session->userdata('search_perpage');
        $perpage = ($ses_pp) ? $ses_pp : $this->config->item('search_perpage');
        $content['perpage'] = $perpage;

        $short_message = $this->session->flashdata('short_message');
        if ($short_message) {
            $temp['short_message'] = $short_message;
        }

        $this->session->set_userdata('search_page', $this->uri->uri_string());

        $this->search_model->langs = $this->langs;
        $this->search_model->filter_map = $filter_map;
        $this->search_model->page = $page;
        $this->search_model->perpage = $perpage;

        $view['lang'] = $this->langs;

        $view['phrase'] = $this->search_model->filter_map['words'];
        $view['results'] = $this->search_model->get_results();
        $view['all'] = $this->search_model->all;

        $view['filters'] = $this->search_model->get_filters($this->langs);
        $view['displaying'] = $this->search_model->displaying;
        $view['perpage'] = $this->search_model->perpage;

        $view['page_navigation'] = $this->builder->page_navigation($this->search_model->all,
            $this->search_model->page, $this->search_model->perpage, $search_uri);

        $title = $this->search_model->title();
        $view['title'] = $title;
        $view['clear'] = $this->search_model->clear_uri();
        $view['dlg'] = true;

        $data['add_css'] = 'data/css/search.css';
        $data['add_js'] = array(/*'data/js/swfobject.js',*/ 'data/js/search.js');
        $data['body'] = $this->load->view('search/content', $view, true);

        if (!$title) $title = 'Browse content';
        $data['title'] = $title . ' - time lapse footage, stock footage, video library';
        $data['meta_desc'] = $title . ' - Stock footage, '
            . $this->api->get_seo_keys(3, 2);
        $data['meta_keys'] = $title . ', ' . $this->api->get_seo_keys(3, 2);

        $this->out($data, 0, 0);
    }

    #------------------------------------------------------------------------------------------------

    function out($content=null, $pagination=null, $type=1) {
        $this->builder->output(array('content'=>$content,'pagination'=>$pagination,'error'=>$this->error),$type);
    }
}
