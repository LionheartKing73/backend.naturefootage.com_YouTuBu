<?php

class File_manager_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function send_upload_notification($login, $paths, $destination_root) {
        if($paths) {
            $uploads = array();
            foreach($paths as $path) {
                $uploads[] = '/' . trim($destination_root, '/') . '/' . pathinfo($path, PATHINFO_BASENAME);
            }
            $this->load->model('users_model');
            $user = $this->users_model->GetUserByLogin($login);
            $data['user'] = $user;
            $data['paths'] = $uploads;

            $this->load->helper('emailer' );
            $emailer = Emailer::GetInstance();
            $emailer->LoadTemplate('toadmin-upload-notification');
            $emailer->SetTemplateValue('user', $user);
            $emailer->SetTemplateValue('paths', $uploads);
            $emailer->TakeSenderSystem();
            $emailer->TakeRecipientAdmin();
            $emailer->SetMailType('html');
            $emailer->Send();
            $emailer->Clear();
        }
    }
}