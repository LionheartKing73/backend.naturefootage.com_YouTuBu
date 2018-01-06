<?php
/**
 * Manage keywords specified in the keywording tool.
 *
 */
class Keywording extends AppController {

    function Keywording()
    {
        parent::__construct();
        $this->load->model('keywording_model');
        $this->load->model('clips_model');
    }

    function load($clipID) {

        $keywordsFile = $this->keywording_model->getKeywordsFilePath($clipID);
        if(file_exists($keywordsFile)){
            $keywordsData = file_get_contents($keywordsFile);
        }
        else{
            $clip = $this->clips_model->get_clip($clipID);
            if($clip){
                $keywordsData = '{"fps":' . $clip['frame_rate'] . ', "keywords":{}}';
            }
        }

        $fileinfo = $this->keywording_model->getFileInfo($clipID);

        $data = '{"keywords_data":' . $keywordsData . ', "file_info":' . json_encode($fileinfo) . '}';

        $this->output->set_output($data);
        $this->output->set_header("Content-Type: application/json\r\n");
    }

    function save($clipID) {

        $keywordsData = $this->input->post('keywords');

        $keywordsFile = $this->keywording_model->getKeywordsFilePath($clipID);

        file_put_contents($keywordsFile, $keywordsData);
    }

    function slice($clipID) {
        if($keywordsData = $this->input->post('keywords')){
            $keywordsFile = $this->keywording_model->getKeywordsFilePath($clipID);
            file_put_contents($keywordsFile, $keywordsData);
        }
        $this->keywording_model->slice_video($clipID, $this->langs);

    }

    function status($clipID){
        $current = $this->keywording_model->get_current_processing($clipID);
        $total = $this->keywording_model->get_fragments_count($clipID);
        $processed_count = 0;
        $current_count = 0;
        $left_count = 0;
        foreach($total as $total_item){
            switch ($total_item['status']) {
                case 0:
                    $left_count = $total_item['total'];
                    break;
                case 1:
                    $current_count = $total_item['total'];
                    break;
                case 2:
                    $processed_count = $total_item['total'];
                    break;
            }
        }
        $total_count = $processed_count + $current_count + $left_count;
        $processed_percent = (100 / $total_count) * $processed_count;
        $result = array('percent' => $processed_percent);
        if($current){
            $result['keyword'] = $current['keywords'];
        }
        else{
            $result['keyword'] = '';
        }
        echo json_encode($result);
        exit();
    }

    function clips($clipID){
        $data = array();
        $filter = array(
            'parent' => (int)$clipID
        );
        $order = ' ORDER BY c.id DESC ';
        $all = $this->clips_model->get_clips_count($this->langs, $filter);
        $data['clips'] = $this->clips_model->get_clips_list($this->langs, $filter, $order);
        return $this->load->view('keywording/view', $data);
    }

}
