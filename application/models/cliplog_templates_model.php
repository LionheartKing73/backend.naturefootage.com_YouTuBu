<?php
use Libraries\Cliplog\Editor\KeywordsState\StateManager;

/**
 * Class Cliplog_templates_model
 *
 * @property cliplog_keywords_model $cliplog_keywords_model
 * @property groups_model $groups_model
 */
class Cliplog_templates_model extends CI_Model
{

    private $currentUserData = array();

    /**
     * @var string  Название таблицы Logging шаблонов
     */
    protected $tableLoggingTemplateName = 'lib_cliplog_logging_templates';
    /**
     * @var string  Название таблицы Metadata шаблонов
     */
    protected $tableMetadataTemplateName = 'lib_cliplog_metadata_templates';

    /**
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('cliplog_keywords_model');
        $this->load->model('groups_model');
        $this->spotCurrentUser();
    }

    private function spotCurrentUser()
    {
        $fieldName = ($this->session->userdata('uid')) ? 'uid' : 'client_uid';
        $userId = $this->session->userdata($fieldName);
        $this->currentUserData = $this->groups_model->get_group_by_user($userId);
    }

    private function getCurrentUserId()
    {
        return (isset($this->currentUserData['id'])) ? $this->currentUserData['id'] : NULL;
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    function getMetadataTemplateDataFromRawData($rawData)
    {


//        $templateData = array();
//        if (isset($rawData['keywords'])) {
//
//            if (!empty($rawData['keywords'])) {
//                foreach ($rawData['keywords'] as $data) {
//                    $templateData['keywords_saved'][$data] = $data;
//                }
//            }
//        }

//        if (!empty($rawData['userKeywordsInsert'])) {
//            foreach ($rawData['userKeywordsInsert'] as $data) {
//                $query = $this->db->query("SELECT * FROM lib_clips_keywords WHERE id = '" . $data . "'");
//                $rows = $query->result_array();
//
//
//                $query = $this->db->query("SELECT * FROM lib_keywords WHERE keyword like '%" . $rows[0]['keyword'] . "%'");
//                $rows = $query->result_array();
//
//                $templateData['keywords_saved'][$rows[0]['id']] = $rows[0]['id'];
//            }
//        }

//        if (!empty($rawData['KeywordsSetsIds'])) {
//
//            $stateManager = new StateManager();
//
//            foreach ($rawData['KeywordsSetsIds'] as $data) {
//                if ($this->cliplog_keywords_model->isTemporaryKeyword($data)) {
//
//                    $temporaryKeywordData = $stateManager->getKeywordDataFromState($data);
//                    $keywordId = $this->cliplog_keywords_model->createKeyword($temporaryKeywordData);
//                    $templateData['keywords'][$keywordId] = $keywordId;
//                } else {
//
//                    $templateData['keywords'][$data] = $data;
//                }
//            }
//        }

        if (isset($rawData['newKeywordsData'])) {

            $arrayCatchUp = json_decode($rawData['newKeywordsData']);
            $templateData['keywords_save'] = $arrayCatchUp;
        }


        if (isset($rawData['sections_values'])) {
            $templateData['sections_values'] = $rawData['sections_values'];
            # Удалим название коллекции, ее не нужно сохранять
            $templateData['sections_values']['collection'] = '';
        }
        return $templateData;
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    function getLoggingTemplateDataFromRawData($rawData)
    {
        $templateData = array();
        if (isset($rawData['sections']) && is_array($rawData['sections'])) {
            foreach ($rawData['sections'] as $sectionName) {
                $templateData['sections'][] = $sectionName;
            }
        }
        if (isset($rawData['keywords_sections_visible'])) {
            $templateData['keywords_sections_visible'] = $rawData['keywords_sections_visible'];
        }
        if (isset($rawData['keywords_sections_hide_lists'])) {
            $templateData['keywords_sections_hide_lists'] = $rawData['keywords_sections_hide_lists'];
        }
        if (!empty($rawData['add_formats'])) {
            $templateData['add_formats'] = $rawData['add_formats'];
        }
        return $templateData;
    }

    /**
     * @param string $templateName
     * @param array $templateData
     *
     * @return bool|int
     */
    function createLoggingTemplate($templateName, $templateData)
    {
        if ($templateName) {
            $saveData = $this->getLoggingTemplateDataFromRawData($templateData);
            $uid = $this->session->userdata('uid');
            $this->db_master->insert(
                $this->tableLoggingTemplateName,
                array(
                    'name' => $templateName,
                    'json' => json_encode($saveData),
                    'owner_id' => $uid
                )
            );
            return $this->db_master->insert_id();
        }
        return FALSE;
    }

    /**
     * @param string $templateName
     * @param array $templateData
     *
     * @return bool|int
     */
    function createMetadataTemplate($templateName, $templateData)
    {
        if ($templateName) {
            //$saveData = $this->getMetadataTemplateDataFromRawData($templateData);
            $uid = $this->session->userdata('uid');
            $this->db_master->insert(
                $this->tableMetadataTemplateName, array(
                    'name' => $templateName,
                    'json' => json_encode($templateData),
                    'owner_id' => $uid
                )
            );
            return $this->db_master->insert_id();
        }
        return FALSE;
    }

    /**
     * @param array $rawData
     *
     * @return bool|int
     */
    function createMetadataTemplateFromRawData($rawData)
    {
        if ($rawData && isset($rawData['keywords_set_name'])) {

            $stateManager = new StateManager();
            $rawData = $stateManager->processInput($rawData);

            $templateName = $rawData['keywords_set_name'];
            $templateData = $this->getMetadataTemplateDataFromRawData($rawData);
            return $this->createMetadataTemplate($templateName, $templateData);
        }
        return FALSE;
    }

    /**
     * @param int $templateId
     * @param string $selectColumns
     *
     * @return array
     */
    function getLoggingTemplate($templateId, $selectColumns = '*')
    {
        if ($templateId) {
            $this->db->select($selectColumns);
            $this->db->where('id', $templateId);
            $this->db->limit(1);
            $result = $this->db->get($this->tableLoggingTemplateName);
            if ($result) {
                return $result->row_array();
            }
        }
        return array();
    }

    /**
     * @param int $templateId
     * @param string $selectColumns
     *
     * @return array
     */
    function getMetadataTemplate($templateId, $selectColumns = '*')
    {
        if ($templateId) {
            $this->db->select($selectColumns);
            $this->db->where('id', $templateId);
            $this->db->limit(1);
            $result = $this->db->get($this->tableMetadataTemplateName);
            if ($result) {
                return $result->result_array();
            }
        }
        return array();
    }

    /**
     * @param string $selectColumns
     * @param int $limit
     *
     * @return array
     */
    function getLoggingTemplateList($selectColumns = '*', $limit = 100)
    {
        $uid = $this->session->userdata('uid');//$this->getCurrentUserId()
        $this->db->select($selectColumns);
        $this->db->where('owner_id', $uid);
        $result = $this->db->get($this->tableLoggingTemplateName, $limit);
        if ($result) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * @param string $selectColumns
     * @param int $limit
     *
     * @return array
     */
    function getMetadataTemplateList($selectColumns = '*', $limit = 100)
    {
        $uid = $this->session->userdata('uid');
        $this->db->select($selectColumns);
        $this->db->where('owner_id', $uid);
        $result = $this->db->get($this->tableMetadataTemplateName, $limit);
        if ($result) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * @param array $rawData
     *
     * @return bool|int
     */
    function createLoggingTemplateFromRawData($rawData)
    {
        if ($rawData && isset($rawData['tempalte_name'])) {
            $templateName = $rawData['tempalte_name'];
            $templateData = $this->getLoggingTemplateDataFromRawData($rawData);
            $templateId = $this->createLoggingTemplate($templateName, $templateData);
            if ($templateId) {
                // Привязываем кл.слова к шаблону
                $keywordsState = $this->cliplog_keywords_model->getKeywordsTemporaryStateFromRequest();
                $this->cliplog_keywords_model->createKeywordsToLoggingRelations($templateId, $keywordsState);
            }
            // echo '<pre>';
            // print_r($rawData['userKeywordsInsert']);


            $myarr = array();

//            foreach ($rawData['keywordListImran'] as $key => $value) {
//
//                $getdata = json_decode($value);
//
//                if ($this->isTemporaryKeyword($getdata->keywordId)) {
//                    $myarr[$key]['section'] = $getdata->keywordSection;
//                    $myarr[$key]['value'] = $getdata->keywordText;
//                }
//            }

            $this->addLoggingKeywords($rawData['userKeywordsInsert'], $rawData['keywordListMyList'], $templateId, '', $rawData['assignedButActive']);


            return $templateId;
        }
        return FALSE;
    }

    /**
     * @param int $templateId
     * @param array $templateData
     *
     * @return bool
     */
    function addLoggingKeywords($userDataArray, $keywordsArray, $templateId, $myarr = NULL, $assignedButNotSaved = NULL)
    {


        $stateManager = new StateManager();

//        if (!empty($userDataArray)) {
//            foreach ($userDataArray as $getValue) {
//                if (!empty($getValue)) {
//
//                    $query = $this->db->query("SELECT * FROM lib_clips_keywords WHERE id ='" . $getValue . "'");
//                    $rows = $query->result_array();
//
//                    $query2 = $this->db->query("SELECT * FROM lib_keywords WHERE keyword like '%" . $rows[0]['keyword'] . "%'");
//                    $rows2 = $query2->result_array();
//
//                    $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $rows2[0]['id'] . '",  templateId="' . $templateId . '",isActive=1 ');
//                    $this->db_master->query('INSERT INTO lib_logging_template_keywords SET keyword ="' . mysql_real_escape_string($rows[0]['keyword']) . '", section_id="' . $rows[0]['section_id'] . '",logging_template_id="' . $templateId . '" ');
//
//                    // print_r($rows);
//                }
//            }
//        }


//        if (!empty($myarr)) {
//            foreach ($myarr as $getValue) {
//
//                $this->db_master->query('INSERT INTO lib_keywords SET keyword ="' . $getValue['value'] . '",  section="' . $getValue['section'] . '"');
//                $insertId = $this->db_master->insert_id();
//
//
//
//                $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $insertId . '",  templateId="' . $templateId . '",isActive=1 ');
//                $this->db_master->query('INSERT INTO lib_logging_template_keywords SET keyword ="' . mysql_real_escape_string($getValue['value']) . '", section_id="' . $getValue['section'] . '",logging_template_id="' . $templateId . '" ');
//            }
//        }
        $uid = $this->session->userdata('uid');
        $query = $this->db->query("SELECT * FROM lib_keywords_notvisible WHERE user_id ='" . $uid . "'");
        $rows = $query->result_array();


        foreach ($rows as $getValue) {
            $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $getValue['keyword_id'] . '",  templateId="' . $templateId . '",isActive=0 ');

        }

        $query = $this->db->query("SELECT * FROM lib_keywords WHERE provider_id ='" . $uid . "'");
        $rows = $query->result_array();


        foreach ($rows as $getValue) {
            $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $getValue['id'] . '",  templateId="' . $templateId . '",isActive="' . $getValue['hidden'] . '" ');

        }

        if (!empty($keywordsArray)) {
            foreach ($keywordsArray as $getValue) {

                if (!empty($getValue)) {

                    $clipGetId = explode('-', $getValue);


                    //     echo 'SELECT keyword FROM lib_keywords WHERE id ='.$ivalue.'';
                    $query = $this->db->query("SELECT * FROM lib_keywords WHERE id ='" . $clipGetId[1] . "'");
                    $rows = $query->result_array();
                    // echo $rows[0]['keyword'] . '<br>';


                    if ($rows[0]['keyword'] != '') {
                        $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $rows[0]['id'] . '",  templateId="' . $templateId . '",isActive=1 ');

                        //$this->db_master->query('INSERT INTO lib_logging_template_keywords SET keyword ="' . mysql_real_escape_string($rows[0]['keyword']) . '", section_id="' . $rows[0]['section'] . '",logging_template_id="' . $templateId . '" ');
                    }


                    // print_r($rows);
                }

                //
            }
        }


        $decodedArray = json_decode($assignedButNotSaved);
        if (!empty($decodedArray)) {
            foreach ($decodedArray as $dataArr) {
                $sectionName = $dataArr->section;
                $keyword = $dataArr->keyword;

                $query = $this->db->query("SELECT * FROM lib_keywords WHERE keyword ='" . $keyword . "' AND section ='" . $sectionName . "'");
                $rows = $query->result_array();

                if (!empty($rows)) {
                    $keywrodsId = $rows[0]['id'];
                    $query2 = $this->db->query("SELECT * FROM lib_cliplog_logging_keywords WHERE keywordId ='" . $keywrodsId . "' AND templateId ='" . $templateId . "'");
                    $rows2 = $query2->result_array();
                    if (empty($rows2)) {
                        $query3 = $this->db->query("SELECT * FROM  lib_keywords_notvisible WHERE keyword_id ='" . $keywrodsId . "' AND user_id ='" . $uid . "'");
                        $rows3 = $query3->result_array();
                        if (empty($rows3)) {
                            $active = '1';
                        } else {
                            $active = '0';
                        }
                        $this->db_master->query('INSERT INTO lib_cliplog_logging_keywords SET keywordId ="' . $keywrodsId . '",  templateId="' . $templateId . '",isActive=' . $active . ' ');


                    }

                }
            }
        }


    }

    function updateLoggingTemplate($templateId, $templateData)
    {
        if ($templateId) {
            $saveData = $this->getLoggingTemplateDataFromRawData($templateData);
            //$this->db_master->where( 'id', $templateId );
            if ($templateId) {
                // Привязываем кл.слова к шаблону
                $keywordsState = $this->cliplog_keywords_model->getKeywordsTemporaryStateFromRequest();
                $this->cliplog_keywords_model->createKeywordsToLoggingRelations($templateId, $keywordsState);
            }
            $this->db_master->where('id', $templateId);
            return !!$this->db_master->update(
                $this->tableLoggingTemplateName,
                array('json' => json_encode($saveData))
            );
        }
        return FALSE;
    }

    /*
    private function executeKeywordActions ( $actionsList ) {
        if ( $actionsList && is_array( $actionsList ) ) {
            foreach ( $actionsList as $key => $action ) {
                $actionsList[ $key ] = json_decode( $action, TRUE );
            }
            $templateId = $this->uri->segment( 6 );
            foreach ( $actionsList as $action ) {
                $keywordId = $action[ 'keywordId' ];
                switch ( $action[ 'actionType' ] ) {
                    case 'Create':

                        break;
                    case 'Delete':
                        $this->executeKeywordDeleteAction( $action[ 'keywordId' ], $templateId );
                        break;
                    case 'Enable':
                        $this->executeKeywordEnableAction( $action[ 'keywordId' ], $templateId );
                        break;
                    case 'Disable':
                        $this->executeKeywordDisableAction( $action[ 'keywordId' ], $templateId );
                        break;
                }
            }
        }
    }
    */

    /**
     * @param int $templateId
     * @param array $templateData
     *
     * @return bool
     */
    function updateMetadataTemplate($templateId, $templateData)
    {


//        $keywordsIdList = $templateData['keywords'];
//        $stateManager = new StateManager();
//
//        if ($keywordsIdList && $stateManager->hasTemporaryKeywords($keywordsIdList)) {
//            # Есть временные кл.слова, нужно создать
//            $replacedList = $stateManager->createKeywordsFromTemporary($keywordsIdList);
//        }
//        # Также извлекаем и создаем скрытые кл.слова
//        if (isset($replacedList)) {
//            $keywordsIdList = $replacedList;
//        }
//        if ($keywordsIdList && $stateManager->hasHiddenKeywords($keywordsIdList)) {
//            # Есть скрытые слова
//            $keywordsIdList = $stateManager->createHiddenKeywordsFromTemporary($keywordsIdList);
//            $templateData['keywords'] = $keywordsIdList;
//        }


        if ($templateId) {
            $saveData = $this->getMetadataTemplateDataFromRawData($templateData);
            $this->db_master->where('id', $templateId);
            return !!$this->db_master->update(
                $this->tableMetadataTemplateName,
                array('json' => json_encode($saveData))
            );
        }
        return FALSE;
    }

    /**
     * @param int $templateId
     *
     * @return bool
     */
    function deleteLoggingTemplate($templateId)
    {
        if ($templateId) {
            $this->cliplog_keywords_model->deleteAllKeywordsToLoggingRelations($templateId);
            return !!$this->db_master->delete(
                $this->tableLoggingTemplateName,
                array('id' => $templateId)
            );
        }
        return FALSE;
    }

    /**
     * @param int $templateId
     *
     * @return bool
     */
    function deleteMetadataTemplate($templateId)
    {
        if ($templateId) {
            return !!$this->db_master->delete(
                $this->tableMetadataTemplateName,
                array('id' => $templateId)
            );
        }
        return FALSE;
    }

    public function isTemporaryKeyword($keywordId)
    {
        return (strpos($keywordId, 'temp_', 0) === 0);
    }

}