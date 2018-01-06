<?php

class Aws_jobs extends CI_Controller
{
    const AMOUNT_OF_SECONDS_TO_SLEEP_AFTER_DELETION = 60;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('aws3_sqs_delete_resources_model');
        $this->load->model('aws_model');
    }

    public function delete_s3_resources_in_sqs()
    {
        while(true){
            while($job = $this->aws3_sqs_delete_resources_model->get_job()){
                $this->aws_model->delete_resource_one_or_many_by_path($job);
            }
            sleep(self::AMOUNT_OF_SECONDS_TO_SLEEP_AFTER_DELETION);
        }

    }
}