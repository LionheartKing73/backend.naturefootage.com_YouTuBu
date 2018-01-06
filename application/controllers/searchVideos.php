<?php

class SearchVideos extends CI_Controller
{
    CONST PROVIDER = 354;
    CONST HOST = 'http://backend.naturefootage.com/';
    CONST DEBUG_DATA_FORMAT = "Y-m-d H:i:s";

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Base_model');
        $this->load->model('Taxonomy_model');
        $this->load->model('Browse_category_model');
        $this->load->model('Family_group_model');
        $this->load->model('Common_name_model');
    }

    /**
     * @var GuzzleHttp\Client
     */
    protected $_client = null;

    /**
     * @return \GuzzleHttp\Client
     */
    protected function _getClient()
    {
        if ( ! $this->_client ) {
            /**
             * If there is no result for search we get STATUS = 400,
             * that's why we have to disable exceptions
             */
            $this->_client = new GuzzleHttp\Client([
                'base_uri' => self::HOST,
                'exceptions' => false,
            ]);
        }
        return $this->_client;
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function _filterWords($string)
    {
        $string = preg_replace('/[^-a-zA-Z\s]/', '', $string);
        $string = str_replace(' ', '+', $string);
        $string = str_replace('-', '+', $string);
        return $string;
    }

    /**
     * @param $filteredString string
     * @return string
     */
    protected function _makeUrl($filteredString, $needTobeQuoted = false)
    {
        if ($needTobeQuoted) {
            $filteredString = "\"{$filteredString}\"";
        }
        return 'en/fapi/clips/provider/' . self::PROVIDER . '/frontend/46/words/'
        . $filteredString
        . '/limit/1/from/0';
    }

    protected function _searchFamilies()
    {
        $message = "_searchFamilies() started";
        self::mailDebug($message);
        log_message('debug', "\n searchVideos::_searchFamilies();");
        $family_model = new Family_group_model();
        $this->_checkResult($family_model);
    }

    protected function _searchCommonNames()
    {
        $message = "_searchCommonNames() started";
        self::mailDebug($message);
        log_message('debug', "\n searchVideos::_searchCommonNames();");
        $common_name_model = new Common_name_model();
        $this->_checkResult($common_name_model);
    }

    public function getSearchTerm($model, $row, $isCommonName = false)
    {
        if ( ! $isCommonName ) {
            $b = new Browse_category_model();
            $q = $this->db->get_where($b->getTable(), ['id' => $row->browse_category_id]);
            $rows = $q->result();
            if ($rows && is_array($rows) && isset($rows[0])) {
                return $rows[0]->search_term;
            }
        } else {
            /*$f = new Family_group_model();
            $q = $this->db->get_where($f->getTable(), ['id' => $row->family_group_id]);
            $rows = $q->result();
            if ($rows && is_array($rows) && isset($rows[0])) {
                $browse_id = $rows[0]->browse_category_id;
                $b = new Browse_category_model();
                $q = $this->db->get_where($b->getTable(), ['id' => $browse_id]);
                $rows = $q->result();
                if ($rows && is_array($rows) && isset($rows[0])) {
                    return $rows[0]->search_term;
                } else {
                    return false;
                }
            } else {
                return false;
            }*/
            return false;
        }
        return false;
    }

    /**
     * @param $model Common_name_model|Family_group_model
     * $model must have getEmpty method which returns array of DB rows
     */
    protected function  _checkResult($model)
    {
        $isCommonName = (int) (get_class($model) === 'Common_name_model');
        $needTobeQuoted = $isCommonName;
        $rows = $model->getEmpty();
        foreach ($rows as $index => $row) {
            /**
             * Debug
             */
            if (($index % 1000) === 0) {
                $table = $model->getTable();
                $message = "SearchVideos::_checkResult({$table}) index={$index} needTobeQuoted = {$needTobeQuoted}";
                self::mailDebug($message);
            }
            $search_term = $this->getSearchTerm($model, $row, $isCommonName);

            $search_string = $this->_filterWords($row->name);
            if ($search_term) {
                $search_string = "$search_string-{$search_term}";
            }
            $url = $this->_makeUrl($search_string, $needTobeQuoted);
            $client = $this->_getClient();
            $response = $client->request('POST', $url);
            if ($response->getStatusCode() !== 200) {
                log_message('debug', "\n There is no result for: '{$row->name}'");
            } else {
                $body = $response->getBody();
                $body_s = (string)$body;
                $json = \GuzzleHttp\json_decode($body_s, true);
                if ($json && isset($json['data']) && is_array($json['data']) && count($json['data'])) {
                    $row->result = 1;
                    if ($json['data'] && isset($json['data'])) {
                        $row->first_code = $json['data'][0]['code'];
                    }
                    $this->db->where('id', $row->id);
                    $this->db->update($model->getTable(), $row);
                } else {
                    log_message('debug', "\n THERE IS RESULT for: '{$row->name}'");
                }
            }
        }
    }

    public function index()
    {
        $start = new \DateTime();
        $message = "Started {$start->format(self::DEBUG_DATA_FORMAT)}";
        self::mailDebug($message);
        $this->_searchFamilies();
        $this->_searchCommonNames();
        $end = new \DateTime();
        $diff = $end->diff($start);
        $time_string = "Time: " . $diff->format("searchVideos::index lasted: %H hours, %I minutes, %S seconds");
        self::mailDebug($time_string);
        log_message('debug', $time_string);
        echo "\n========================================\n",
        $time_string,
        "\n========================================\n";
    }

    public static function mailDebug($message)
    {
        $time = new \DateTime();
        $time_string = $time->format(self::DEBUG_DATA_FORMAT);
        mail('ee923925@gmail.com', "SearchVideos($time_string)", $message);
    }

}