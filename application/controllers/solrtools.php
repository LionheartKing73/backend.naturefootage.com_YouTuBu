<?php

require_once(APPPATH.'/libraries/SorlSearchAdapter.php');

class Solrtools extends CI_Controller {

    private $solr_adapter;

    function __construct() {
        parent::__construct();
        $this->solr_adapter = new SorlSearchAdapter();
    }


    public function indexing(){

//        if(!$this->input->is_cli_request()){
//            exit();
//        }

        if($this->solr_adapter){
            $this->load->model('clips_model');
            $portion = 2000;
            $i = 0;
            $this->solr_adapter->deleteAll();
            while($idex_clips = $this->clips_model->get_clips_index_data(false, array('limit' => $portion, 'offset' => $portion * $i))){
                $this->solr_adapter->addToIndex($idex_clips, false);
                unset($idex_clips);
                $i++;
                echo 'Indexed ' . $i * $portion, PHP_EOL;
            }
            $this->solr_adapter->optimize();
        }
    }

    public function optimize(){
        if($this->solr_adapter){
            $this->solr_adapter->optimize();
        }
    }
}
?>