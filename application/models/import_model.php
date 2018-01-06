<?php

set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']
    . '/application/libraries');
require_once 'PHPExcel/Reader/Excel5.php';
require_once 'PHPExcel/Reader/Excel2007.php';
require_once 'PHPExcel/Writer/Excel5.php';

class Import_model extends CI_Model
{

    var $error;
    var $import_errors;

    function Import_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function parse_xl($ext)
    {
        $fields = array('code', 'length', 'title', 'description', 'country',
            'province', 'location', 'gps', 'lighting', 'night_day', 'rights', 'date',
            'contributor', 'native_format', 'original_format', 'aspect_ratio',
            'frame_rate', 'color', 'audio', 'model', 'property', 'keywords',
            'copyright', 'set', 'price', 'hd_ext');

        $data = array();

        $objReader = $ext == 'xls' ? new PHPExcel_Reader_Excel5() : new PHPExcel_Reader_Excel2007();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($_FILES['xl']['tmp_name']);
        $objPHPExcel->setActiveSheetIndex(0);
        $objActiveSheet = $objPHPExcel->getActiveSheet();

        $empty_rows = 0;
        $col_count = count($fields);
        $start_row = 2;

        for ($row = $start_row; $empty_rows < 3; $row++) {
            $item = array();
            for ($col = 0; $col < $col_count; ++$col) {
                $item_field = $fields[$col];
                $item[$item_field] = $objActiveSheet->getCellByColumnAndRow($col, $row)->getValue();
            }
            if ($item['code']) {
                $empty_rows = 0;
                $data[] = $item;
            } else {
                $empty_rows++;
            }
        }
        return $data;
    }

    function add_clips($data)
    {
        $result = array('total' => 0, 'added' => 0, 'ignored' => 0);

        foreach ($data as $clip) {
            ++$result['total'];
            $error_msg = $this->add_clip($clip);
            if ($error_msg) {
                $this->import_errors .= $clip['code'] . ': ' . $error_msg . "\r\n";
                ++$result['ignored'];
            } else {
                ++$result['added'];
            }
        }

        return $result;
    }

    function add_clip($clip)
    {
        $clip_id = 0;

        $row = $this->db->query('SELECT id FROM lib_clips WHERE code=?', array($clip['code']))->result_array();
        if ($row) {
            $clip_id = $row[0]['id'];
        }
        $data['code'] = $clip['code'];


        $row = $this->db->query('SELECT id FROM lib_users WHERE login=? AND group_id=3', array($clip['contributor']))->result_array();
        $data['client_id'] = $row[0]['id'];
        if (!$data['client_id'])
            return 'Unknown contributor &lsquo;' . $clip['contributor'] . '&rsquo;.';

        $location = $clip['location'];
        if (!$location) {
            $location = $clip['province'];
        }
        if (!$location) {
            $location = $clip['country'];
        }
        if ($location) {
            $row = $this->db->query('SELECT location_id FROM lib_locations_content WHERE name=?', array($location))->result_array();
            $data['location_id'] = $row[0]['location_id'];
            if (!$data['location_id'])
                return 'Unknown location &lsquo;' . $location . '&rsquo;.';
        }

        $data['gps'] = $clip['gps'];
        $data['color'] = intval($clip['color']);
        $data['audio'] = intval($clip['audio']);

        if ($clip['date']) {
            $data['shoot_date'] = trim($clip['date']);
            $date_parts = explode('.', $data['shoot_date'], 3);
            if (@checkdate($date_parts[1], $date_parts[0], $date_parts[2])) {
                $data['exact_date'] =
                    implode('-', array_reverse(explode('.', trim($data['shoot_date']), 3)));
            } else {
                $data['exact_date'] = null;
            }
        }

        $data['length'] = $clip['length'];
        $data['license'] = $clip['rights'] == 'RF' ? 1 : 2;
        $data['model_release'] = intval($clip['model']);
        $data['property_release'] = intval($clip['property']);

        $row = $this->db->query('SELECT id FROM lib_formats WHERE title=? AND type=0', array($clip['native_format']))->result_array();
        $data['nf_id'] = $row[0]['id'];
        if (!$data['nf_id'])
            return 'Unknown native format &lsquo;' . $clip['native_format'] . '&rsquo;.';

        $data['format'] = $clip['original_format'];
        $data['ratio'] = $clip['aspect_ratio'] == '4:3' ? 2 : 1;
        $data['rate'] = $clip['frame_rate'];
        $data['lighting'] = (strtolower($clip['lighting']) == 'ext') ? 1 : 0;
        $data['night_day'] = (strtolower($clip['night_day']) == 'day') ? 1 : 0;
        $data['price'] = floatval($clip['price']);

        if ($clip['set']) {
            $row = $this->db->query('SELECT id FROM lib_sets WHERE code = ?', $clip['set'])->result_array();
            if (empty($row)) {
                return 'Unknown set code &lsquo;' . $clip['set'] . '&rsquo;.';
            } else {
                $data['set_id'] = $row[0]['id'];
            }
        }

        $data['type'] = 2;
        $data['active'] = 1;
        $data['ctime'] = date('Y-m-d H:i:s');

        if ($clip_id) {
            $this->db_master->update('lib_clips', $data, array('id' => $clip_id));
        } else {
            $this->db_master->insert('lib_clips', $data);
            $clip_id = $this->db_master->insert_id();
        }

        if (!$clip_id)
            return 'Database error.';

        if ($clip['hd_ext']) {
            $data = array('clip_id' => $clip_id, 'resource' => $clip['hd_ext'], 'type' => 2);
            $res_id = $this->db->query('SELECT id FROM lib_clips_res WHERE clip_id=? AND resource=? AND type=2', array($clip_id, $clip['hd_ext']))->result_array();
            $res_id = $res_id[0]['id'];
            if ($res_id) {
                $this->db_master->update('lib_clips_res', $data, array('id' => $res_id));
            } else {
                $this->db_master->insert('lib_clips_res', $data);
            }
        }

        $data = array('clip_id' => $clip_id, 'lang' => 'en', 'title' => $clip['title'],
            'description' => $clip['description'], 'keywords' => $clip['keywords'],
            'copyright' => $clip['copyright']);
        $content_id = $this->db->query("SELECT id FROM lib_clips_content WHERE clip_id=? AND lang='en'", array($clip_id))->result_array();
        $content_id = $content_id[0]['id'];
        if ($content_id) {
            $this->db_master->update('lib_clips_content', $data, array('id' => $content_id, 'lang' => 'en'));
        } else {
            $this->db_master->insert('lib_clips_content', $data);
        }
    }

    function format_date($date)
    {
        $parts = explode('.', $date);
        $parts = array_reverse($parts);
        return implode('-', $parts);
    }

    function import_clips()
    {
        $filename = $_FILES['xl']['tmp_name'];

        if (!empty($_FILES) && is_uploaded_file($filename)) {
            $path_parts = pathinfo($_FILES['xl']['name']);
            $ext = strtolower($path_parts['extension']);
            if (($ext != 'xls') && ($ext != 'xlsx')) {
                $this->error = 'Wrong file type, must be ".xls" or ".xlsx"';
                return;
            }

            $clips_data = $this->parse_xl($ext);
            $result = $this->add_clips($clips_data);

            $this->message = 'Import completed.';
            return $result;
        } else {
            $this->error = 'The file is not selected.';
        }
    }

    public function getResultByCode($id)
    {
        $this->db->select('*');
        $this->db->from('lib_clips');
        $this->db->where('code', $id);

        $query = $this->db->get();
        //echo $this->db->last_query();
        $result = $query->result();

        return $result;
    }

    public function getResultById($id)
    {
        $this->db->select('*');
        $this->db->from('lib_clips');
        $this->db->where('id', $id);

        $query = $this->db->get();
        //echo $this->db->last_query();
        $result = $query->result();

        return $result;
    }

    public function insert_clip_keywords($clip_id, $section_heading, $keywords, $overwrite = false)
    {
        // can overwrite existing keywords, but don't delete existing if new ones not supplied
        if($overwrite && !empty($keywords) && is_array($keywords) && $this->notAllElementsEmpty($keywords)){
            $this->db_master->delete('lib_clips_keywords', [
                'clip_id' => $clip_id,
                'section_id' => $section_heading
            ]);
        }

        foreach ($keywords as $key) {
            if (!empty($key)) {
                // check before insert to db_master should be applyed to db_master because of replication lag
                $query = $this->db_master->query("select * from lib_clips_keywords where clip_id='" . $clip_id . "' and section_id='" . $section_heading . "' and keyword='" . mysql_real_escape_string($key) . "'");
                $row = $query->result_array();
                if (empty($row)) {
                    $data = array(
                        'clip_id' => $clip_id,
                        'section_id' => $section_heading,
                        'keyword' => $key
                    );
                    $this->db_master->insert('lib_clips_keywords', $data);
                }
            }
        }
    }

    private function notAllElementsEmpty($keywords)
    {
        foreach($keywords as $keyword){
            if(!empty($keyword))
                return true;
        }
        return false;
    }

    public function update_clip($table, $id, $data)
    {
        $this->db_master->where('id', $id);
        $this->db_master->update($table, $data);

    }

    public function update_clip_content($table, $id, $data)
    {
        $this->db_master->where('clip_id', $id);
        $this->db_master->update($table, $data);
    }

    public function getValuesId($name, $table_name)
    {
        $this->db->select('*');
        $this->db->from($table_name);
        $this->db->where('search_term', $name);

        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }

    public function getValuesLicenseId($name, $table_name)
    {
        $this->db->select('*');
        $this->db->from($table_name);
        $this->db->where('name', $name);

        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }

    public function getCollectionRecord($id, $collection)
    {
        $this->db->select('*');
        $this->db->from('lib_clips_collections');
        $this->db->where('clip_id', $id);
        $this->db->where('collection_id', $collection);

        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }

    public function insert_clip_collection($table_name, $id, $collection_id)
    {

        $data = array(
            'clip_id' => $id,
            'collection_id' => $collection_id,
        );
        $this->db_master->insert($table_name, $data);
    }

    public function getBrandId($name)
    {
        $this->db->select('*');
        $this->db->from('lib_brands');
        $this->db->where('name', $name);

        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

}
