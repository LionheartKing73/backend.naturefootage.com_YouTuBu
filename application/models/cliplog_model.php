<?php

require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;

/**
 * Class Cliplog_model
 */
class Cliplog_model extends CI_Model
{

    const SMALL_STILL_FOLDER = 'stills_224x136';
    const MID_STILL_FOLDER = 'stills_400x224';
    const HD_STILL_FOLDER = 'stills_hd';


    private $brandMap = [
        1 => 'Nature Footage',
        2 => 'NatureFlix',
    ];

    function __construct()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_terms_count()
    {
        return $this->db->count_all('lib_pricing_terms');
    }

    function get_terms_list($limit = array(), $order_by = '')
    {
        if ($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if ($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res;
    }

    function save_term($id)
    {
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_pricing_terms', $data);
            return $id;
        } else {
            $this->db_master->insert('lib_pricing_terms', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_term($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('lib_pricing_terms');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_terms($ids)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_pricing_terms', array('id' => $id));
            }
        }
    }

    /**
     * @param array $sectionList
     * @return string
     */
    private function get_section_names_string($sectionList = [])
    {
        $section_names_string = '';
        if (!empty($sectionList)){
            foreach ($sectionList as $sectionName => $sectionTitle){
                // I don't know why originally section names were escaped in get_keywords() but to not broke something let's do it in the same way
                $section_names_string .=  '\''.mysql_real_escape_string($sectionName) . '\',';
            }
        }
        return rtrim($section_names_string, ',');
    }

    /**
     * @param array $sectionList
     * @param string $selected
     * @param bool $show_all
     * @param string $on_match
     * @param string $collection
     * @param bool $clipIds
     * @param bool $templateId
     * @return string
     */
    private function get_where_for_sections_keywords($sectionList = [], $selected = '', $show_all = FALSE, $on_match = '', $collection = '', $clipIds = false, $templateId = false)
    {
        $where_collection = ' WHERE lk.provider_id = 0 ';
        if (!empty($this->get_section_names_string($sectionList))){
            $where_collection .= ' AND lk.section IN (' . $this->get_section_names_string($sectionList) . ') ';
        }
        if (!empty($selected))
            $where_collection .= ' AND lk.id NOT IN (' . mysql_real_escape_string($selected) . ') ';
        if (!$show_all)
            $where_collection .= ' AND lknv.keyword_id IS NULL';
        if (!empty($on_match))
            $where_collection .= ' AND lk.keyword LIKE \'%' . mysql_real_escape_string($on_match) . '%\'';
        if ($collection)
            $where_collection .= ' AND lk.collection = \'' . mysql_real_escape_string($collection) . '\' ';
        if (!empty($clipIds)) {
            $where_collection .= ' OR (lk.id IN (' . $clipIds . ')  AND lk.section = \'' . mysql_real_escape_string($section) . '\'  ) ';
        }
        if (!empty($templateId)){
            $where_collection .= ' OR (clk.templateid = ' . $templateId . ' AND lk.section=\'' . mysql_real_escape_string($section) . '\') ';
        }
        return $where_collection;
    }

    /**
     * @param array $sectionList
     * @param string $selected
     * @param bool $show_all
     * @param string $on_match
     * @param string $collection
     * @param bool $clipIds
     * @param bool $templateId
     * @return mixed
     */
    private function get_general_visible_keywords($sectionList = [], $selected = '', $show_all = FALSE, $on_match = '', $collection = '', $clipIds = false, $templateId = false)
    {
        $where_collection = $this->get_where_for_sections_keywords($sectionList, $selected , $show_all , $on_match, $collection , $clipIds , $templateId );

        $query = 'SELECT lk.*, lknv.keyword_id visible, clk.isActive FROM lib_keywords lk
            LEFT JOIN lib_keywords_notvisible lknv ON lk.id = lknv.keyword_id
            LEFT JOIN lib_cliplog_logging_keywords clk ON clk.keywordId = lk.id'
            . $where_collection . ' GROUP BY lk.keyword,lk.section ORDER BY lk.keyword';

        return $this->db->query($query)->result_array();
    }

    /**
     * @param int $user_id
     * @param array $sectionList
     * @return array
     */
    private function get_provider_keywords($user_id, $sectionList)
    {
        return $this->db->query(
            "SELECT * 
            FROM lib_keywords 
            WHERE provider_id = " . $user_id . " AND section IN (" . $this->get_section_names_string($sectionList) . ") "
        )->result_array();
    }

    /**
     * @param array $keywords_to_display
     * @param array $providers_keywords
     * @param bool $show_all
     * @return array
     */
    private function get_all_keywords_to_display($keywords_to_display, $providers_keywords, $show_all)
    {
        foreach ($providers_keywords as $keyword) {
            if (!$show_all) {
                if ($keyword['hidden'] == 0) {
                    $visible = $keyword['id'];
                } else {
                    $visible = '';
                }
            }

            array_push($keywords_to_display, [
                'id' => $keyword['id'],
                'keyword' => $keyword['keyword'],
                'section' => $keyword['section'],
                'collection' => $keyword['collection'],
                'provider_id' => $keyword['provider_id'],
                'old' => $keyword['old'],
                'basic' => $keyword['basic'],
                'visible' => $visible,
                'hidden' => $keyword['hidden']
            ]);
        }

        return $keywords_to_display;
    }

    /**
     * @param array $all_keywords_to_display
     * @return array
     */
    private function get_keywords_by_sections($all_keywords_to_display)
    {
        $keywords_by_sections = [];
        foreach ($all_keywords_to_display as $keyword_data){
            $keywords_by_sections[$keyword_data['section']] = $keyword_data;
        }
        return $keywords_by_sections;
    }

    /**
     * @param array $sectionList
     * @param string $selected
     * @param bool $show_all
     * @param string $on_match
     * @param string $collection
     * @param bool $clipIds
     * @param bool $templateId
     * @return array
     */
    public function get_keywords_for_sections($sectionList = [], $selected = '', $show_all = FALSE, $on_match = '', $collection = '', $clipIds = false, $templateId = false)
    {
        $general_keywords = $this->get_general_visible_keywords($sectionList, $selected , $show_all , $on_match, $collection , $clipIds , $templateId);

        $providers_keywords = $this->get_provider_keywords($_SESSION['uid'], $sectionList);

        $all_keywords_to_display = $this->get_all_keywords_to_display($general_keywords, $providers_keywords, $show_all);

        return $this->get_keywords_by_sections($all_keywords_to_display);
    }



    function get_keywords($section = '', $selected = '', $show_all = FALSE, $on_match = '', $collection = '', $only_new = TRUE, $clipIds = false, $templateId = false)
    {

        $user_id = $_SESSION['uid'];//($_SESSION['group']==1)?0:$_SESSION['uid'];

         $where_collection = ' WHERE 1 ';
        if (!empty($section))
            $where_collection .= ' AND lk.section = \'' . mysql_real_escape_string($section) . '\'  ';
        if (!empty($selected))
            $where_collection .= ' AND lk.id NOT IN (' . mysql_real_escape_string($selected) . ') ';
        if (!$show_all)
            $where_collection .= ' AND lknv.keyword_id IS NULL';
        if (!empty($on_match))
            $where_collection .= ' AND lk.keyword LIKE \'%' . mysql_real_escape_string($on_match) . '%\'';
        if ($collection)
            $where_collection .= ' AND lk.collection = \'' . mysql_real_escape_string($collection) . '\' ';
        if (!empty($clipIds)) {
            $where_collection .= ' OR (lk.id IN (' . $clipIds . ')  AND lk.section = \'' . mysql_real_escape_string($section) . '\'  ) ';
        } else {
            $where_collection .= ' ';
        }
        $where_collection .= ' AND lk.provider_id = "0"';

        if (!empty($templateId)){
            $where_collection .= ' OR (clk.templateid = ' . $templateId . ' AND lk.section=\'' . mysql_real_escape_string($section) . '\') ';
        }
        $query = 'SELECT lk.*, lknv.keyword_id visible, clk.isActive FROM lib_keywords lk
            LEFT JOIN lib_keywords_notvisible lknv ON lk.id = lknv.keyword_id
            LEFT JOIN lib_cliplog_logging_keywords clk ON clk.keywordId = lk.id'
            . $where_collection . ' GROUP BY lk.keyword,lk.section ORDER BY lk.keyword';

        $res = $this->db->query($query)->result_array();

        $quer = $this->db->query("SELECT * FROM lib_keywords WHERE provider_id = " . $user_id . " AND section ='" . $section . "' ")->result_array();


        foreach ($quer as $value) {
            if (!$show_all) {
                if ($value['hidden'] == 0) {
                    $visible = $value['id'];
                } else {
                    $visible = '';
                }
            }

            $mergerArray = array(
                'id' => $value['id'],
                'keyword' => $value['keyword'],
                'section' => $value['section'],
                'collection' => $value['collection'],
                'provider_id' => $value['provider_id'],
                'old' => $value['old'],
                'basic' => $value['basic'],
                'visible' => $visible,
                'hidden' => $value['hidden']
            );

            array_push($res, $mergerArray);
        }

        return $res;
    }

    /**
     * this is a new version of get_keywords method, because there is no opportunity to unserstand what the hell is going on
     */
    function getKeywordsForCliplog($section = '', $selected = '', $show_all = false, $on_match = '', $collection = '')
    {
        $selectedArr = [];
        $user_id = $_SESSION['uid'];
        $where_collection = ' WHERE 1 ';
        if (!empty($section))
            $where_collection .= ' AND lk.section = \'' . mysql_real_escape_string($section) . '\'  ';
        if (!empty($selected)) {
            $selectedArr = explode(',', $selected);
            $selectedArr = array_filter($selectedArr, function ($id) {
                return !empty($id) && is_numeric($id);
            });
            $selected = implode(',', $selectedArr);
            $where_collection .= ' AND lk.id NOT IN (' . mysql_real_escape_string($selected) . ') ';
        }
        if (!$show_all)
            $where_collection .= ' AND lknv.keyword_id IS NULL ';
        if (!empty($on_match))
            $where_collection .= ' AND lk.keyword LIKE \'%' . mysql_real_escape_string($on_match) . '%\'';

        if (is_numeric($collection) && array_key_exists($collection, $this->brandMap)) {
            $collection = $this->brandMap[$collection];
        }
        if (empty($collection)) {
            $collection = $this->brandMap[1];
        }
        $where_collection .= ' AND lk.collection = \'' . mysql_real_escape_string($collection) . '\' ';
        $where_collection .= ' AND lk.provider_id = "0"';

        $query = 'SELECT lk.*, IF(lknv.keyword_id IS NULL, 1, 0) as visible FROM lib_keywords lk
                  LEFT JOIN lib_keywords_notvisible lknv ON (lk.id = lknv.keyword_id AND lknv.user_id = ' . $user_id . ')'
            . $where_collection . ' GROUP BY lk.keyword,lk.section ORDER BY lk.keyword';

        $res = $this->db->query($query)->result_array();

        $quer = $this->db->query("SELECT * FROM lib_keywords WHERE provider_id = " . $user_id . " AND section ='" . $section . "' ")->result_array();

        foreach ($quer as $value) {
            // do not show selected keywords
            if (!empty($selectedArr) && in_array($value['id'], $selectedArr)) {
                continue;
            }

            // do not show keywords marked as hidden
            if (!$show_all && !$value['hidden']) {
                continue;
            }

            $mergerArray = array(
                'id' => $value['id'],
                'keyword' => $value['keyword'],
                'section' => $value['section'],
                'collection' => $value['collection'],
                'provider_id' => $value['provider_id'],
                'old' => $value['old'],
                'basic' => $value['basic'],
                'visible' => $value['hidden'],
                'hidden' => $value['hidden']
            );

            array_push($res, $mergerArray);
        }


        return $res;
    }

    function getKeywordsCustomList($where = NULL)
    {
        $userId = $this->session->userdata('uid');
        //$whereString = '';//" ( lib_keywords.provider_id = 0 OR lib_keywords.provider_id = {$userId} ) AND lib_keywords.hidden = 0 ";
        if ($where) {
            $where = "WHERE {$where}";
        }

        $result = $this->db->query("
            SELECT DISTINCT lib_keywords.*, lib_keywords_notvisible.keyword_id AS visible
            FROM lib_keywords
            LEFT JOIN lib_keywords_notvisible ON lib_keywords.id = lib_keywords_notvisible.keyword_id AND lib_keywords_notvisible.user_id = {$userId}
            {$where}
            ORDER BY lib_keywords.keyword;"
        );
        if ($result) {
            return $result->result_array();
        }
        return array();
    }

    function get_keyword($id)
    {
        $query = $this->db->get_where('lib_keywords', array('id' => $id));
        $rows = $query->result_array();
        return $rows[0];
    }

    function save_keyword($keyword, $section = '', $collection = '')
    {
        $keyword_id = FALSE;
        if ($keyword) {
            $keyword = array('keyword' => $keyword, 'section' => $section, 'collection' => $collection);
            if (!empty($_SESSION['uid'])) {
                $keyword['provider_id'] = $_SESSION['uid'];
            }
            $this->db_master->insert('lib_keywords', $keyword);
            $keyword_id = $this->db_master->insert_id();
        }
        return $keyword_id;
    }

    function switch_off_keyword($keyword_id)
    {
        if ($keyword_id) {
            $user_id = $this->session->userdata('uid');
            $data = array('keyword_id' => $keyword_id, 'user_id' => $user_id);
            $this->db_master->insert('lib_keywords_notvisible', $data);
        }
    }

    function switch_on_keyword($keyword_id)
    {
        if ($keyword_id) {
            $user_id = $this->session->userdata('uid');
            $data = array('keyword_id' => $keyword_id, 'user_id' => $user_id);
            $this->db_master->delete('lib_keywords_notvisible', $data);
        }
    }

    function get_section_options($section = '', $selected = '')
    {
        $options = array();
        $selected = explode(',', $selected);
        switch ($section) {
            case 'add_collection':
                $options = $this->get_collections($selected);
                break;
            case 'license_type':
                $options = $this->get_license_types($selected);
                break;
        }

        return $options;
    }

    function get_collections($selected = '')
    {
        if ($selected && is_array($selected))
            $this->db->where_not_in('id', $selected);
        $this->db->select('*, name value');
        $query = $this->db->get('lib_collections');
        $rows = $query->result_array();
        return $rows;
    }

    function get_brands()
    {
        $this->db->select('*, name value');
        $query = $this->db->get('lib_brands');
        $rows = $query->result_array();
        return $rows;
    }

    function get_license_types($selected = '')
    {
        if ($selected && is_array($selected))
            $this->db->where_not_in('id', $selected);
        $this->db->select('id, name, name value');
        $query = $this->db->get('lib_licensing');
        $rows = $query->result_array();
        return $rows;
    }

    function get_countries()
    {
        $this->db->order_by("name", "asc");
        $query = $this->db->get('lib_countries');
        $rows = $query->result_array();
        return $rows;
    }

    function get_pricing_page($lang = 'en')
    {
        $this->db->select('pc.body');
        $this->db->from('lib_pages p');
        $this->db->join('lib_pages_content pc', 'p.id = pc.page_id');
        $this->db->where('p.alias1', 'pricing');
        $this->db->where('pc.lang', $lang);
        $query = $this->db->get();
        $res = $query->result_array();
        return $res[0];
    }

    function delete_keyword($id)
    {
        if ($id) {
            $this->db_master->delete('lib_keywords', array('id' => $id));
            $this->db_master->delete('lib_keywords_notvisible', array('keyword_id' => $id));
            $this->db_master->delete('lib_clip_keywords', array('keyword_id' => $id));
        }
    }


    function get_thumbs_list($clipid)
    {
        $objects = false;

        $addr = $this->getStillsFolder($clipid);

        if ($addr) {
            $objects = $this->get_s3_thumbs_objects(parse_url($addr));
        }

        return $objects;
    }

    private function getStillsFolder($clipid)
    {
        // new transcoding + lib_thumbnails_new
        $addr = $this->get_clip_dir($clipid, 'lib_thumbnails_new', self::SMALL_STILL_FOLDER);

        if(empty($addr)){
            // old trancoding + lib_thumbnails_new
            $addr = $this->get_clip_dir($clipid, 'lib_thumbnails_new', self::MID_STILL_FOLDER);
        }

        if(empty($addr)){
            // if nothing found, check old structure: lib_thumbnails
            $addr = $this->get_clip_dir($clipid, 'lib_thumbnails');
        }
        return $addr;
    }

    private function get_clip_dir($clipId, $thumbnailTable, $holder = null)
    {
        if ($holder) {
            $sql = "SELECT path FROM $thumbnailTable as t JOIN lib_thumbnails_types as tt
              ON t.type_id = tt.id
              WHERE t.clip_id = $clipId AND tt.holder = {$this->db->escape($holder)} LIMIT 1";

        } else {
            $sql = "SELECT path FROM $thumbnailTable WHERE clip_id = $clipId LIMIT 1";
        }
        $result = $this->db->query($sql);
        $clipDir = $result->first_row('array');

        return isset($clipDir['path']) ? $clipDir['path'] : false;

    }

    /**
     * @param array $path ['host' => dir host, 'path' => path to dir]
     * @return array|false of aws objects
     */
    private function get_s3_thumbs_objects(array $path)
    {
        if (isset($path['host']) && isset($path['path'])) {
            $store = array();
            require(__DIR__ . '/../config/store.php');

            $s3Client = S3Client::factory(array(
                'key' => $store['s3']['key'],
                'secret' => $store['s3']['secret']
            ));

            $objects = $s3Client->getListObjectsIterator(array(
                // 15/07/2016: FSEARCH-1316 str_replace is a Temporary Solution
                'Bucket' => str_replace(
                    'video.naturefootage.com',
                    's3.footagesearch.com',
                    $path['host']
                ),
                'Prefix' => ltrim($path['path'], '/')
            ));

            return $objects;
        } else {
            return false;
        }
    }

    function get_active_thumbnail_path($clipid)
    {
        $clipdir = $this->db->query("SELECT location FROM lib_clips_res WHERE clip_id = '" . $clipid . "' AND resource = 'jpg' LIMIT 1")->result_array();
        return $clipdir[0]['location'];
    }

    function change_active_thumb($cid, $path)
    {
        echo $path . " || " . $cid;
        if($this->isNewStill($path) && $this->hasHdStill($cid)){
                $stillHdLocation = str_replace(self::SMALL_STILL_FOLDER, self::HD_STILL_FOLDER, $path);
                $this->db_master->query("UPDATE lib_clips_res SET location = '" . $stillHdLocation . "' WHERE clip_id = '" . $cid . "' AND resource = 'jpg' and type=5");
        }

        $this->db_master->query("UPDATE lib_clips_res SET location = '" . $path . "' WHERE clip_id = '" . $cid . "' AND resource = 'jpg' and type=0");

    }

    private function isNewStill($path)
    {
        return (strpos($path, self::SMALL_STILL_FOLDER) !== false);
    }

    private function hasHdStill($cid)
    {
         $res =  $this->db->query("SELECT id FROM lib_clips_res WHERE clip_id = '" . $cid . "' AND resource = 'jpg' and type=5")->result_array();

         return !empty($res);
    }

    public function getAllClipsByUserId($id, $submission_id = NULL, $filterClip = NULL)
    {
        $clip_bins = $filterClip['backend_clipbin_id'];
        $collection_filter = $filterClip['cliplog_search_collection'];
        $col = str_replace("'", "", $collection_filter);
        $collection_filter = $col;

        $license_filter = $filterClip['cliplog_search_license'];
        $license = str_replace("'", "", $license_filter);
        $license_filter = $license;

        $budget_filter = $filterClip['cliplog_search_price_level'];
        $budget = str_replace("'", "", $budget_filter);
        $budget_filter = $budget;

        $format_filter = $filterClip['cliplog_search_format_category'];
        $format = str_replace("'", "", $format_filter);
        $format_filter = $format;

        $active_filter = $filterClip['cliplog_search_active'];
        $active = str_replace("'", "", $active_filter);
        $active_filter = $active;

        $wordsin_filter = $filterClip['cliplog_search_wordsin'];
        $wordsin = str_replace("'", "", $wordsin_filter);
        $wordsin_filter = $wordsin;
        //$clip_bins = $filterClip;
        //echo $filterClip;exit;
        $this->db->select('*');
        $this->db->from('lib_clips');
        if (!empty($clip_bins)) {
            $query = $this->db_master->query("select * from lib_backend_lb_items where backend_lb_id='" . $clip_bins . "'");
//            echo $this->db->last_query();exit;
            $res = $query->result_array();
            foreach ($res as $key => $row) {
                $item[$key] = $row['item_id'];
            }
            //$item = implode(",", $item);
//            $new_str = ltrim ($item, " ' ");

            $this->db->where_in('id', $item);
        }
        if (!empty($collection_filter)) {
            $this->db->where('collection', $collection_filter);
        }
        if (!empty($license_filter)) {
            $this->db->where('license', $license_filter);
        }
        if (!empty($budget_filter)) {
            $this->db->where('price_level', $budget_filter);
        }
        if (!empty($format_filter)) {
            $this->db->like('source_format', $format_filter);
        }
        if (!empty($active_filter)) {
            $this->db->where('active', $active_filter);
        }
        if (!empty($submission_id)) {
            $this->db->where('submission_id', $submission_id);
        } else {
            $this->db->where('client_id', $id);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;
    }

    public function setBackendSession(){
        $where=array('user_id'=>$this->session->userdata('uid'));
        $user=$this->db->get_where('lib_cart_items',$where)->result();
        $data=serialize(array('sess'=>$_SESSION,'ci_sess'=>$this->session->userdata));
        if(!empty($user[0])){
            $this->db_master->update('lib_cart_items',array('backend_serialized'=>$data),$where);
        }else{
            $this->db_master->insert('lib_cart_items',array('backend_serialized'=>$data,'user_id'=>$this->session->userdata('uid')));
        }
    }
    public function getBackendSession(){
        $where=array('user_id'=>$this->session->userdata('uid'));
        $user=$this->db->get_where('lib_cart_items',$where)->result();
        if(!empty($user[0])){
            $back=(array)unserialize($user[0]->backend_serialized);
            if(is_array($back['sess']))
                $_SESSION=array_replace_recursive($_SESSION,$back['sess']);
            if(is_array($back['ci_sess']))
                $this->session->userdata=array_replace_recursive($this->session->userdata,$back['ci_sess']);
        }
    }

}