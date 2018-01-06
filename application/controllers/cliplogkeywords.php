<?php

/**
 * Class CliplogKeywords
 *
 * @property cliplog_keywords_model $cliplog_keywords_model
 * @property groups_model $groups_model
 */
class Cliplogkeywords extends CI_Controller
{

    private $requestData;
    private $currentUserData;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('cliplog_keywords_model');
        $this->load->model('groups_model');
        $this->spotCurrentUser();
        $this->cliplog_keywords_model->setCurrentUser($this->currentUserData);
    }

    function index()
    {
        $this->requestData = $this->input->post();
        $requestAction = $this->uri->segment(4);
        $methodName = "action_{$requestAction}";
        if ($requestAction && $this->api->is_ajax_request() && method_exists($this, $methodName)) {
            $this->$methodName();
        }
        show_404();
    }

    private function spotCurrentUser()
    {
        $fieldName = ($this->session->userdata('uid')) ? 'uid' : 'client_uid';
        $userId = $_SESSION['uid']; //$this->session->userdata( $fieldName );
        $this->currentUserData = $this->groups_model->get_group_by_user($userId);
    }

    private function getCurrentUserId()
    {
        return (isset($this->currentUserData['id'])) ? $this->currentUserData['id'] : NULL;
    }

    private function isAdmin()
    {
        return (isset($this->currentUserData['is_admin']) && $this->currentUserData['is_admin']);
    }

    private function showResponse($responseData = array())
    {
        $responseData['status'] = TRUE;
        $responseData['isAdmin'] = $this->isAdmin();
        $responseData['userId'] = $_SESSION['uid'];
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($responseData);
        die();
    }

    private function getRequestData_sectionName()
    {
        if ($this->requestData && isset($this->requestData['sectionName'])) {
            return $this->requestData['sectionName'];
        }
        return NULL;
    }

    private function getRequestData_keywordId()
    {
        if ($this->requestData && isset($this->requestData['keywordId'])) {
            return $this->requestData['keywordId'];
        }
        return NULL;
    }

    private function getRequestData_templateId()
    {
        if ($this->requestData && isset($this->requestData['templateId'])) {
            return $this->requestData['templateId'];
        }
        return NULL;
    }

    /* ******************************************** */

    private function action_getkeywordlist()
    {
        $responseData = array();
        $templateId = $this->getRequestData_templateId();
        if (strpos($templateId, 'customTemplate') !== false) {
            $templateId = str_replace('customTemplate','',$templateId);
            $responseData['keywordList'] = $this->cliplog_keywords_model->getLoggingTemplateKeywords($templateId, '1');
            $responseData['keywordListId'] = $templateId;
            $this->showResponse($responseData);
        } else {
            $responseData['keywordList'] = $this->cliplog_keywords_model->getLoggingTemplateKeywords($templateId, '0');
            $responseData['keywordListId'] = $templateId;
            $this->showResponse($responseData);
        }
    }

    private function action_getCurrentUserId()
    {
    }

    private function action_deleteKeyword()
    {
    }

}