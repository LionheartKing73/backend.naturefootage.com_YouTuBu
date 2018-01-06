<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Editor extends AppController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        //$this->load->view('editor/index2');
        $this->content();
    }

    function content() {
        $content['meta_title'] = 'Editor';
        $content['title'] = 'Editor';
        $content['add_css'] = array(
            '/data/editor/tabPanel.css',
            //'/data/editor/jquery-ui-1.7.3.custom.css',
            '/data/editor/Editor.css',
            'http://fonts.googleapis.com/css?family=Princess+Sofia',
            '/data/editor/jquery.jscrollpane.css',
            '/data/editor/colorpicker/css/colorpicker.css'
        );
        $content['add_js'] = array(
            //'/data/editor/jquery-1.7.1.min.js',
            //'/data/editor/jquery-ui-1.8.21.custom.min.js',
            '/data/editor/jquery.iframe-transport.js',
            '/data/editor/jquery.fileupload.js',
            '/data/editor/jquery.ui.touch-punch.min.js',
            '/data/editor/jquery.jscrollpane.js',
            '/data/editor/jquery.lazyload.js',
            '/data/editor/jquery.scrollstop.js',
            '/data/editor/colorpicker/js/colorpicker.js',
            '/data/editor/colorpicker/js/eye.js',
            '/data/editor/colorpicker/js/utils.js',
            '/data/editor/colorpicker/js/layout.js?ver=1.0.2',
            'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
            '/data/editor/modernizr.video.js',
            //'/data/editor/srt.js',
            '/data/editor/com.be.video.editor.Editor/com.be.video.editor.Editor.nocache.js',
            '/data/editor/editor.js'

        );
        $data = array();
        $content['body'] = $this->load->view('editor/index', $data, true);
        $this->out($content);
    }

    /*public function resources() {
        $clips = $this->clip_model->getList();
        $timelines = $this->timeline_model->getList();
        $resources = array_merge($clips, $timelines);
        $this->output->set_output(json_encode($resources));
    }

    public function upload() {
        $clip = $this->clip_model->upload();
        $this->output->set_output(json_encode($clip));
    }*/

    public function save() {
        /*$t = '{
            "timeline":[
                        {"NON_MUTABLE_ID":1,"id":"0","filename":"upload/resources/clip/preview/2.mp4","length_":12.779,"startOffset":0,"stopOffset":4.26256324,"preview":"/data/upload/resources/clip/frames/clip_2/","muted":false,"type":"flat"},
                        {
                            "leftElement":{"NON_MUTABLE_ID":2,"id":"0","filename":"upload/resources/clip/preview/2.mp4","length_":12.779,"startOffset":4.26256324,"stopOffset":12.779,"preview":"/data/upload/resources/clip/frames/clip_2/","muted":false,"type":"flat"},
                            "rightElement":{"NON_MUTABLE_ID":1,"id":"0","filename":"upload/resources/clip/preview/2.mp4","length_":12.779,"startOffset":0,"stopOffset":4.26256324,"preview":"/data/upload/resources/clip/frames/clip_2/","muted":false,"type":"flat"},
                            "length":2,
                            "type":"transition",
                            "transitionType":"BARN_DOOR_WIPE"
                        },
                        {"NON_MUTABLE_ID":2,"id":"0","filename":"upload/resources/clip/preview/2.mp4","length_":12.779,"startOffset":4.26256324,"stopOffset":12.779,"preview":"/data/upload/resources/clip/frames/clip_2/","muted":false,"type":"flat"}
            ],
            "texts":[]
        }';*/
        $this->load->model('timeline_model');
        $this->load->model('cart_model');
        //$_POST['user_iduser_iduser_id'] = $t;

        if($this->input->post('timeline')){
            if($timeline_id = $this->timeline_model->save()){
                $this->cart_model->add_timeline_item($timeline_id);
            }
        }
    }

    public function build() {
        $response = $this->timeline_model->build();
        $this->output->set_output($response);
    }

    public function building($id) {
        $this->timeline_model->building($id);
    }

    public function flatassets(){
        $this->load->model('bin_model');
        $bin = $this->bin_model->get_content($this->langs);
        $flat_assets = array();
        if($bin){
            foreach($bin as $item){
                $flat_assets[] = array(
                    'filename' => '/data/upload/resources/clip/preview/' . $item['id'] . '.mp4',
                    'startOffset' => 0,
                    'stopOffset' => $item['duration'],
                    'length_' => $item['duration'],
                    'muted' => 0,
                    'preview' => '/data/upload/resources/clip/frames/clip_' . $item['id'] . '/'
                );
            }
        }

        echo json_encode($flat_assets);
        exit();
    }

}
