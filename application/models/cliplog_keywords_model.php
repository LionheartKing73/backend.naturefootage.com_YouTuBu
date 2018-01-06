<?php

/**
 * Class Cliplog_keywords_model
 */
class Cliplog_keywords_model extends CI_Model
{

    protected $tableKeywordsName = 'lib_keywords';
    protected $tableClipKeywordsName = 'lib_clip_keywords';
    protected $tableLoggingKeywordsName = 'lib_cliplog_logging_keywords';
    protected $tableMetaDataTemplates = 'lib_cliplog_metadata_templates';
    protected $lastQueryString;
    protected $lastQueryResultData;
    protected $lastQueryResultCount;
    protected $currentUserData;

    function __construct()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    public function setCurrentUser($userData)
    {
        $this->currentUserData = $userData;
    }

    private function getCurrentUserId()
    {
        return (isset($this->currentUserData['id'])) ? $this->currentUserData['id'] : NULL;
    }

    private function isAdmin()
    {
        return (isset($this->currentUserData['is_admin']) && $this->currentUserData['is_admin']);
    }

    private function executeSelectQuery($select = '', $join = '', $where = '', $order = '', $limit = '')
    {
        $selectPart = ($select) ? "SELECT {$select} " . PHP_EOL : 'SELECT * ' . PHP_EOL;
        $fromPart = "FROM {$this->tableKeywordsName} " . PHP_EOL;
        $joinPart = ($join) ? "{$join} " . PHP_EOL : '';
        $wherePart = ($where) ? "WHERE 1 AND {$where} AND section <>'category' " . PHP_EOL : " " . PHP_EOL;
        $orderPart = ($order) ? "ORDER BY {$order} " . PHP_EOL : "ORDER BY {$this->tableKeywordsName}.id DESC " . PHP_EOL;
        $limitPart = ($limit) ? "LIMIT {$limit} " : 'LIMIT 0, 1000 ';
        $this->lastQueryString = "{$selectPart}{$fromPart}{$joinPart}{$wherePart}{$orderPart}{$limitPart};";
        //echo $this->lastQueryString;
        $resultQuery = $this->db->query($this->lastQueryString);
        $this->lastQueryResultData = array();
        if ($resultQuery) {
            $this->lastQueryResultData = $resultQuery->result_array();
        }
        $this->lastQueryResultCount = count($this->lastQueryResultData);
        return $this->lastQueryResultData;
    }

    private function rebuildKeywordsResultData($arrayData, $primaryKeyName = 'id')
    {
        $resultArray = array();
        if ($primaryKeyName && $arrayData && is_array($arrayData)) {
            foreach ($arrayData as $value) {
                if (isset($value[$primaryKeyName])) {
                    // $isActive = (is_null($value['isActive'])) ? 1 : $value['isActive'];

                    if (is_null($value['isActive'])) {
                        $userId = $_SESSION['uid'];
                        $check = $this->checkKeywordsActiveOrNot($value['id'], $userId);
                        if ($check == 0) {
                            $isActive = 1;
                        } else {
                            $isActive = 0;
                        }
                    } else {
                        $userId = $_SESSION['uid'];
                        $isActive = $value['isActive'];
                        if ($value['isActive'] == 0) {
                            $this->db_master->query('INSERT INTO lib_keywords_notvisible SET keyword_id ="' . $value['id'] . '" , user_id="' . $userId . '" ');
                        }

                        $userId = $_SESSION['uid'];
                        $check = $this->checkKeywordsActiveOrNot($value['id'], $userId);
                        if ($check == 0) {
                            $isActive = 1;
                        } else {
                            $isActive = 0;
                        }

                    }


                    $primaryKey = $value[$primaryKeyName];
                    $resultArray[$primaryKey] = array(
                        'keywordId' => $value['id'],
                        'keywordText' => $value['keyword'],
                        'keywordSection' => $value['section'],
                        'keywordCollection' => $value['collection'],
                        'keywordOwnerId' => $value['provider_id'],
                        'isOld' => $value['old'],
                        'isBasic' => $value['basic'],
                        'isActive' => $isActive
                    );
                }
            }
        }
        return $resultArray;
    }

    private function getCustomQueryKeywordList($select = '', $join = '', $where = '', $order = '', $limit = '')
    {
        if (!$select) {
            $select = "{$this->tableKeywordsName}.*";
        }
        $resultArray = $this->executeSelectQuery($select, $join, $where, $order, $limit);
        return $this->rebuildKeywordsResultData($resultArray);
    }

    public function getDefaultTemplateKeywords()
    {
        return $this->getCustomQueryKeywordList();
    }

    public function getLoggingTemplateKeywords($templateId, $check)
    {
        $selectString = NULL;
        $joinString = NULL;
        $whereString = NULL;
        $userId = $_SESSION['uid'];
        //$templateId=false;
        // if ($templateId) {
        /* $selectString = " DISTINCT {$this->tableKeywordsName}.*, {$this->tableLoggingKeywordsName}.isActive ";
          $joinString = "LEFT JOIN {$this->tableLoggingKeywordsName} ON {$this->tableLoggingKeywordsName}.keywordId = {$this->tableKeywordsName}.id";
          $whereString = "{$this->tableLoggingKeywordsName}.templateId = {$templateId} AND ";
          $whereString .= "( {$this->tableKeywordsName}.provider_id = 0 OR {$this->tableKeywordsName}.provider_id = {$userId} ) AND {$this->tableKeywordsName}.hidden = 0"; */

        //STOPPED
        //   //  $selectString = " DISTINCT {$this->tableKeywordsName}.* ";
        //  $joinString = "LEFT JOIN {$this->tableLoggingKeywordsName} ON {$this->tableLoggingKeywordsName}.keywordId = {$this->tableKeywordsName}.id  ";
        // $whereString .= "  {$this->tableLoggingKeywordsName}.templateId = {$templateId}";
        ////STOPPED
//$whereString .= " (  AND {$this->tableKeywordsName}.collection = 'Nature Footage')";
        //  } else {


        if ($check == '1') {
            //$this->db_master->query('DELETE FROM lib_keywords_notvisible WHERE user_id="' . $userId . '" ');

            $selectString = " DISTINCT {$this->tableKeywordsName}.*,{$this->tableLoggingKeywordsName}.isActive";
            $joinString = "LEFT JOIN {$this->tableLoggingKeywordsName} ON {$this->tableLoggingKeywordsName}.keywordId = {$this->tableKeywordsName}.id ";

            $whereString .= "  {$this->tableLoggingKeywordsName}.templateId = {$templateId}";
        } else {
            $selectString = " DISTINCT {$this->tableKeywordsName}.*";
            $joinString = "LEFT JOIN {$this->tableLoggingKeywordsName} ON {$this->tableLoggingKeywordsName}.keywordId = {$this->tableKeywordsName}.id ";

            $collections = $this->db->query('SELECT * FROM lib_collections WHERE id=' . $templateId . ' ')->result_array();
            if ($collections) {
                $whereString .= " {$this->tableKeywordsName}.collection ='" . $collections[0]['name'] . "' ";
            } else {
                $whereString .= " {$this->tableKeywordsName}.collection ='Nature Footage' ";
            }
        }
        // echo $whereString;
        //$filter_collection = implode(",", $collection_filter_arr);
        //  }


        $keywordList = $this->getCustomQueryKeywordList($selectString, $joinString, $whereString, "{$this->tableKeywordsName}.keyword ASC");


        /*
          IF

          SELECT  DISTINCT lib_keywords.*, lib_cliplog_logging_keywords.isActive
          FROM lib_keywords
          LEFT JOIN lib_cliplog_logging_keywords ON lib_cliplog_logging_keywords.keywordId = lib_keywords.id AND lib_cliplog_logging_keywords.templateId = 36
          WHERE lib_keywords.old = 0 AND   lib_keywords.provider_id = 0 AND lib_keywords.hidden = 0 AND lib_keywords.old =0 AND lib_keywords.collection = 'Nature Footage'
          ORDER BY lib_keywords.keyword ASC

          ELSE

          SELECT lib_keywords.*
          FROM lib_keywords
          WHERE lib_keywords.old = 0 AND   lib_keywords.provider_id = 0 AND lib_keywords.hidden = 0 AND lib_keywords.old =0 AND lib_keywords.collection = 'Nature Footage'
          ORDER BY lib_keywords.keyword ASC


         */

        if ($check == '0') {
//            foreach ($keywordList as &$keywordData) {
//                if (isset($keywordData['isActive'])) {
//                    $keywordData['isActive'] = 1;
//                }
//            }
        }
        if ($check == '0') {
            $quer = $this->db->query("SELECT * FROM " . $this->tableKeywordsName . " WHERE provider_id = " . $userId . "")->result_array();


            foreach ($quer as $value) {
                $isActive = ($value['hidden'] == 1) ? 1 : 0;

                $mergerArray = array(
                    'keywordId' => $value['id'],
                    'keywordText' => $value['keyword'],
                    'keywordSection' => $value['section'],
                    'keywordCollection' => $value['collection'],
                    'keywordOwnerId' => $value['provider_id'],
                    'isOld' => $value['old'],
                    'isBasic' => $value['basic'],
                    'isActive' => $value['hidden']
                );

                array_push($keywordList, $mergerArray);
            }
        } else {
            $quer = $this->db->query("SELECT * FROM " . $this->tableKeywordsName . " WHERE provider_id = " . $userId . "")->result_array();


            foreach ($quer as $value) {
                if (!array_key_exists($value['id'], $keywordList)) {
                    $mergerArray = array(
                        'keywordId' => $value['id'],
                        'keywordText' => $value['keyword'],
                        'keywordSection' => $value['section'],
                        'keywordCollection' => $value['collection'],
                        'keywordOwnerId' => $value['provider_id'],
                        'isOld' => $value['old'],
                        'isBasic' => $value['basic'],
                        'isActive' => 0
                    );

                    array_push($keywordList, $mergerArray);
                }
            }
        }
        return $keywordList;
    }

    public function getkeyworsSetsKeywords($templateSetId)
    {
        //echo "SELECT json FROM " . $this->tableMetaDataTemplates . " WHERE id = " . $templateSetId . "";die;
        $quer = $this->db->query("SELECT json FROM " . $this->tableMetaDataTemplates . " WHERE id = " . $templateSetId . "")->result_array();
        // echo '<pre>';
        $new_arr = json_decode($quer[0]['json']);

        $i = 0;
        $valueString = '';
        foreach ($new_arr->keywords as $value) {

            if ($i > 0) {
                $valueString .= ',' . $value;
            } else {
                $valueString .= $value;
            }

            $i++;
        }
        //echo $valueString;

        $quer2 = $this->db->query("SELECT * FROM " . $this->tableKeywordsName . " WHERE id IN( " . $valueString . ")")->result_array();

        return $this->rebuildKeywordsResultData($quer2);
        //die;
    }

    public function getKeywordsTemporaryStateFromRequest()
    {
        $postData = $this->input->post('keywordList');
        if ($postData && is_array($postData)) {
            $decodedData = array();
            foreach ($postData as $jsonString) {
                $jsonData = json_decode($jsonString, TRUE);
                $keywordId = $jsonData['keywordId'];
                $decodedData[$keywordId] = $jsonData;
            }
            return $decodedData;
        }
        return array();
    }

    public function createKeywordsToLoggingRelations($templateId, $keywordsState)
    {
        if ($templateId && $keywordsState && is_array($keywordsState)) {
            $this->deleteAllKeywordsToLoggingRelations($templateId);
            foreach ($keywordsState as $keywordData) {
                $keywordId = $keywordData['keywordId'];
                $isActiveKeyword = $keywordData['isActive'];
                if ($this->isTemporaryKeyword($keywordId)) {
                    $keywordId = $this->createKeyword($keywordData);
                }
                $this->createKeywordToLoggingRelation($templateId, $keywordId, $isActiveKeyword);
            }
        }
    }

    public function createKeywordToLoggingRelation($templateId, $keywordId, $isActiveKeyword = TRUE)
    {
        if ($templateId && $keywordId) {
            $isActiveKeyword = (int)$isActiveKeyword;
            /* $rows=$this->db->query( "SELECT * FROM {$this->tableKeywordsName} WHERE keyword = '{$keywordText}' AND section = '{$keywordSection}' AND
              provider_id = {$keywordOwnerId} AND collection = '{$keywordCollection}' AND old= {$isOld} AND basic= {$isBasic}")->result_array(); */
            $rows = $this->db->query("SELECT id FROM {$this->tableKeywordsName} WHERE id=" . $keywordId)->result_array();

            /* if(count($rows)>0){
              return $rows[0]['id'];
              }else{
              $this->db_master->query( "
              INSERT INTO {$this->tableLoggingKeywordsName} ( templateId, keywordId, isActive )
              VALUES ( {$templateId}, {$keywordId}, {$isActiveKeyword} );
              " );
              return $this->db_master->insert_id();
              } */
            if (count($rows) > 0) {
                $this->db_master->query("
                    INSERT INTO {$this->tableLoggingKeywordsName} ( templateId, keywordId, isActive )
                    VALUES ( {$templateId}, {$keywordId}, {$isActiveKeyword} );
                ");
                return $this->db_master->insert_id();
            }
        }
        return FALSE;
    }

    public function deleteAllKeywordsToLoggingRelations($templateId)
    {
        if ($templateId) {
            return $this->db_master->query("
                DELETE
                FROM {$this->tableLoggingKeywordsName}
                WHERE templateId = {$templateId};"
            );
        }
        return FALSE;
    }

    public function isTemporaryKeyword($keywordId)
    {
        return (strpos($keywordId, 'temp_', 0) === 0);
    }

    public function isHiddenKeyword($keywordId)
    {
        return (strpos($keywordId, 'hidden_', 0) === 0);
    }

    public function createKeyword($keywordData)
    {
        if ($keywordData && is_array($keywordData)) {
            $keywordCollection = $keywordData['keywordCollection'];
            $keywordOwnerId = ($keywordData['keywordOwnerId']) ? (int)$keywordData['keywordOwnerId'] : ($_SESSION['uid']) ? $_SESSION['uid'] : 0;
            $keywordText = addslashes($keywordData['keywordText']);
            $keywordSection = $keywordData['keywordSection'];
            $isBasic = (int)$keywordData['isBasic'];
            $isOld = (int)$keywordData['isOld'];
            $rows = $this->db->query("SELECT * FROM {$this->tableKeywordsName} WHERE keyword = '{$keywordText}' AND section = '{$keywordSection}' AND collection = '{$keywordCollection}' ")->result_array();

            //AND old= {$isOld} AND basic= {$isBasic}
            if (count($rows) > 0) {
                return $rows[0]['id'];
            } else {
//                $this->db_master->query("
//                INSERT INTO {$this->tableKeywordsName} ( keyword, section, provider_id, collection, old, basic )
//                VALUES ( '{$keywordText}', '{$keywordSection}', {$keywordOwnerId}, '{$keywordCollection}', {$isOld}, {$isBasic} );"
//                );


                $this->db_master->query("
                INSERT INTO {$this->tableKeywordsName} ( keyword, section,collection)
                VALUES ( '{$keywordText}', '{$keywordSection}', '{$keywordCollection}' );"
                );


                return $this->db_master->insert_id();
            }
        }
        return FALSE;
    }

    public function createHiddenKeyword($keywordData, $clipId = NULL)
    {


        if ($keywordData && is_array($keywordData)) {
            $keywordCollection = $keywordData['keywordCollection'];
            $keywordOwnerId = ($keywordData['keywordOwnerId']) ? (int)$keywordData['keywordOwnerId'] : $_SESSION['uid'];
            $keywordText = $keywordData['keywordText'];
            $keywordTextSearch = mysql_real_escape_string($keywordData['keywordText']);
            $keywordSection = $keywordData['keywordSection'];
            $isBasic = (int)$keywordData['isBasic'];
            $isOld = (int)$keywordData['isOld'];

            /* $rows=$this->db->query( "SELECT * FROM {$this->tableKeywordsName} WHERE keyword = '{$keywordText}' AND section = '{$keywordSection}' AND
              provider_id = {$keywordOwnerId} AND collection = '{$keywordCollection}' AND old= {$isOld} AND basic= {$isBasic}")->result_array(); */

            if (count($clipId) > 1) {
                // if (!empty($clipId)) {
                //   foreach ($clipId as $dataClipId) {
                //       $getChec = $this->checkKeywordsExistsClipKeywords($keywordText, $keywordSection, $dataClipId);

                //       if ($getChec != 1) {
                //          $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword ="' . $keywordText . '", section_id="' . $keywordSection . '",clip_id="' . $dataClipId . '" ');
                //      }
                //  }
                // }
            } else {
                //$getChec = $this->checkKeywordsExistsClipKeywords($keywordText, $keywordSection, $clipId);

                //if ($getChec != 1) {
                // $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword ="' . $keywordText . '", section_id="' . $keywordSection . '",clip_id="' . $clipId . '" ');
                //}
            }


            $rows = $this->db->query("SELECT * FROM {$this->tableKeywordsName} WHERE keyword = '{$keywordTextSearch}' AND section = '{$keywordSection}'  AND collection = '{$keywordCollection}' ")->result_array();
            if (count($rows) > 0) {
                return $rows[0]['id'];
            } else {
             //   $this->db_master->query("
             //   INSERT INTO {$this->tableKeywordsName} ( keyword, section, collection )
             //   VALUES ( '" . mysql_real_escape_string($keywordText) . "', '{$keywordSection}', '{$keywordCollection}' );"
            //    );
             //   return $this->db_master->insert_id();
            }
        }
        return FALSE;
    }

    public function checkKeywordsExistsClipKeywords($keyword, $section, $clipId)
    {


//        $query = $this->db->query('SELECT * FROM lib_clips_keywords WHERE keyword ="' . $keyword . '" AND section_id="' . $section . '" AND clip_id ="' . $clipId . '" ');
//        $rows = $query->result_array();
//        if ($rows) {
//            return 1;
//        } else {
//            return 0;
//        }
    }

    public function checkKeywordsActiveOrNot($keywordId, $userId)
    {
        $query = $this->db->query('SELECT * FROM lib_keywords_notvisible WHERE keyword_id ="' . $keywordId . '" AND user_id="' . $userId . '" ');
        $rows = $query->result_array();
        if ($rows) {
            return 1;
        } else {
            return 0;
        }
    }

}
