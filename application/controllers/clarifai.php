<?php

class Clarifai extends AppController {

    function Clarifai() {
        parent::__construct();

        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'], $request);
        $id = $request['id'];
        $token = $request['token'];

        if ($token == 'qazwsxedcrfvtgbyhn'){
            $this->load->model('clips_model');
            $keywords = $this->clips_model->generateClarifaiKeywords($id);
            echo $keywords;
            die();
        } else {
            echo 'Bad token';
            die;
        }

    }
}