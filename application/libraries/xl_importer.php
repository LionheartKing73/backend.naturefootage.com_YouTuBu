<?php

require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PHPExcel.php');

class ExcelDataReader {

    private $inputFileName;
    private $startRow;
    private $startColumn;
    private $objWorksheet;
    private $highestRow;
    private $highestColumn;
    private $row;
    private $headingsArray;

    public function __construct() {
        
    }

    public function setFileName($fileName) {
        $this->inputFileName = $fileName;
    }

    public function setStartRow($startRow) {
        $this->startRow = $startRow;
    }

    public function setStartColumn($startColumn) {
        $this->startColumn = $startColumn;
    }

    public function getHeadings() {

        if ($this->headingsArray == null) {
            $this->init();
        }

        return $this->headingsArray;
    }

    public function getNextRow() {

        if ($this->row > $this->highestRow) {
            return null;
        }

        $returnRow = array();

        $dataRow = $this->objWorksheet->rangeToArray($this->startColumn . $this->row . ':' . $this->highestColumn . $this->row, null, true, true, true);
        if ((isset($dataRow[$this->row][$this->startColumn])) && ($dataRow[$this->row][$this->startColumn] > '')) {
            foreach ($this->headingsArray as $columnKey => $columnHeading) {
                $returnRow[$columnHeading] = $dataRow[$this->row][$columnKey];
            }
        }

        $this->row++;

        return $returnRow;
    }

    private function init() {

        $inputFileType = PHPExcel_IOFactory::identify($this->inputFileName);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);

        $objPHPExcel = $objReader->load($this->inputFileName);

        $total_sheets = $objPHPExcel->getSheetCount();
        $allSheetName = $objPHPExcel->getSheetNames();

        $this->objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        $this->highestRow = $this->objWorksheet->getHighestRow();
        $this->highestColumn = $this->objWorksheet->getHighestColumn();

        $headingsArray = $this->objWorksheet->rangeToArray($this->startColumn . $this->startRow . ':' . $this->highestColumn . $this->startRow, null, true, true, true);
        $this->headingsArray = $headingsArray[$this->startRow];

        $this->row = $this->startRow + 1;
    }

}

abstract class FlexibleLoader {

    private $reader;
    private $columns;
    private $availableColumns;

    public function __construct(ExcelDataReader $reader) {
        $this->reader = $reader;
        $this->columns = array();
    }

    public function process() {

        $headings = $this->reader->getHeadings();

        $this->availableColumns = array();
        foreach ($headings as $columnName) {
            if (isset($this->columns[$columnName])) {
                array_push($this->availableColumns, $columnName);
            }
        }

        $this->onStart($this->availableColumns);

        while (($dataRow = $this->reader->getNextRow()) != null) {

            $filteredData = array();

            foreach ($this->availableColumns as $columnName) {
                $filteredData[$columnName] = $dataRow[$columnName];
            }

            $this->onRowBegin($filteredData);

            foreach ($this->availableColumns as $columnName) {
                $handler = $this->columns[$columnName];
                $handler($columnName, $filteredData[$columnName]);
            }
        }
    }

    protected abstract function onStart($availableColumns);

    protected abstract function onRowBegin($rowData);

    protected abstract function onRowEnd();

    protected function registerColumn($columnName, $columnHandler) {
        $this->columns[$columnName] = $columnHandler;
    }

}

class TestLoader extends FlexibleLoader {

    public static $CI;
    public static $clip_id;
    //public static $footage_types = array();
    public static $categories = array();
    public static $clips_clause;
    public static $clips_content_clause;
    public static $total_rows = 0;
    public static $imported_rows = 0;
    public static $lang = 'en';
    public static $clip_rights = array('RF' => 1, 'RM' => 2, 'RR' => 3);

    public function __construct(ExcelDataReader $reader, $lang = 'en') {

        parent::__construct($reader);

        self::$CI = &get_instance();
        self::$CI->load->database();
        //self::$CI->load->model('locations_model');
        self::$lang = $lang;

        $this->registerColumn('Code', function($columnName, $columnData) {
            if (empty($columnData)) {
                return;
            }
        });

        $this->registerColumn("Title", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            TestLoader::$CI->db->update('lib_clips_content', array('title' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Creator", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            TestLoader::$CI->db->update('lib_clips_content', array('creator' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Rights", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            TestLoader::$CI->db->update('lib_clips_content', array('rights' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Subject", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            TestLoader::$CI->db->update('lib_clips_content', array('subject' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Description", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }

            TestLoader::$CI->db->update('lib_clips_content', array('description' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Keywords", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }
            TestLoader::$CI->db->update('lib_clips_content', array('keywords' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Date", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            $columnData = PHPExcel_Style_NumberFormat::toFormattedString(
                            $columnData, 'YYYY-MM-DD');
            TestLoader::$CI->db->update('lib_clips', array('creation_date' => $columnData), TestLoader::$clips_clause);
        });


        $this->registerColumn("Category", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id) || empty($columnData)) {
                return;
            }

            $categories_codes = explode(',', $columnData);
            if (!count($categories_codes)) {
                return;
            }

            $categories_ids = array();
            foreach ($categories_codes as $code) {
                if (!empty(TestLoader::$categories[$code])) {
                    $categories_ids[] = TestLoader::$categories[$code];
                } else {
                    $data = TestLoader::$CI->db->
                                    select('id')->
                                    from('lib_cats')->
                                    where(array('code' => trim($code)))->
                                    get()->result();

                    if ($data) {
                        $categories_ids[] = TestLoader::$categories[$code] = $data[0]->id;
                    }
                }
            }

            if (!empty($categories_ids)) {
                foreach ($categories_ids as $cat_id) {
                    $data = array('clip_id' => TestLoader::$clip_id, 'cat_id' => $cat_id);
                    $row = TestLoader::$CI->db->
                                    select('id')->
                                    from('lib_clips_cats')->
                                    where($data)->
                                    get()->result();

                    if (empty($row)) {
                        TestLoader::$CI->db->insert('lib_clips_cats', $data);
                    }
                }
            }
        });


        $this->registerColumn("Price", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }
            TestLoader::$CI->db->update('lib_clips', array('price' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("Price per second", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }
            TestLoader::$CI->db->update('lib_clips', array('price_per_second' => $columnData), TestLoader::$clips_content_clause);
        });

        $this->registerColumn("License", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }
            $license_id = TestLoader::$clip_rights[strtoupper(trim($columnData))];
            TestLoader::$CI->db->update('lib_clips', array('license' => $license_id), TestLoader::$clips_content_clause);
        });

        #Video format
        $this->registerColumn("Format", function($columnName, $columnData) {
            if (empty(TestLoader::$clip_id)) {
                return;
            }
            $format_data = array('title' => $columnData, 'type' => 3);
            $row = TestLoader::$CI->db->
                            select('id')->
                            from('lib_formats')->
                            where($format_data)->
                            get()->result();

            if ($row[0]) {
                TestLoader::$CI->db->update('lib_clips', array('of_id' => $row[0]->id), TestLoader::$clips_content_clause);
            }
        });
    }

    public function onStart($availableColumns) {
        return;
    }

    public function onRowBegin($rowData) {
        ++TestLoader::$total_rows;
        $clien_id = TestLoader::$CI->session->userdata('client_uid') ? TestLoader::$CI->session->userdata('client_uid') : 0;

        $data = TestLoader::$CI->db->
                        select('id')->
                        from('lib_clips')->
                        where(array('code' => $rowData['Code'], 'client_id' => $clien_id))->
                        get()->result();

        if ($data) {
            TestLoader::$clip_id = $data[0]->id;
            TestLoader::$clips_clause = array('id' => TestLoader::$clip_id);

            $content_data = TestLoader::$CI->db->
                            select('id')->
                            from('lib_clips_content')->
                            where(array('clip_id' => TestLoader::$clip_id, 'lang' => self::$lang))->
                            get()->result();
            if (empty($content_data)) {
                $this->db_master->insert('lib_clips_content', array(
                    'clip_id' => TestLoader::$clip_id, 'lang' => self::$lang));
                $clip_content_id = $this->db_master->insert_id();
            } else {
                $clip_content_id = $content_data[0]->id;
            }
            TestLoader::$clips_content_clause = array('id' => $clip_content_id);

            ++TestLoader::$imported_rows;
        }

        return;
    }

    public function onRowEnd() {
        return;
    }

}

class XL_Importer {

    private $input_file_name;
    private $start_row = 1;
    private $start_column = 'A';
    private $reader;

    public function set_file_name($file_name) {
        $this->input_file_name = $file_name;
        $this->reader = new ExcelDataReader();
        $this->reader->setFileName($this->input_file_name);
        $this->reader->setStartColumn($this->start_column);
        $this->reader->setStartRow($this->start_row);
        $this->reader->getHeadings();
    }

    public function get_cols_map() {
        return array(
            'Code' => 'Code',
//                        'Code Type' => 'Code Type',
//                        'Title' => 'Title',
//                        'Creator' => 'Creator',
//                        'Rights' => 'Rights',
//                        'Subject' => 'Subject',
//                        
//                        'Date' => 'Date',
//                        'Category' => 'Category',
//                        'Price' => 'Price',
//                        'Price per second' => 'Price per second',
//                        'License' => 'License',
//                        'Format' => 'Format',
            'Notes' => 'Notes',
            'License restrictions' => 'License restrictions',
            'Video/Audio' => 'Video/Audio',
            'Collection' => 'Collection',
            'Month filmed' => 'Month filmed',
            'Year filmed' => 'Year filmed',
            'Master format' => 'Master format',
            'Master frame size' => 'Master frame size',
            'Master frame rate' => 'Master frame rate',
            'Lab' => 'Lab',
            'License type' => 'License type',
            'Price level' => 'Price level',
            'Releases' => 'Releases',
            'Description' => 'Description',
            //'Delivery category' => 'Delivery category',
            'Camera model' => 'Camera model',
            'Camera sensor' => 'Camera sensor',
            'Source bit depth' => 'Source bit depth',
            'Source chroma subsampling' => 'Source chroma subsampling',
            'Source format' => 'Source format',
            'Source codec' => 'Source codec',
            'Source frame size' => 'Source frame size',
            'Source frame rate' => 'Source frame rate',
            'Submission codec' => 'Submission codec',
            'Submission data rate' => 'Submission data rate',
            'Submission frame size' => 'Submission frame size',
            'Submission frame rate' => 'Submission frame rate',
            'Shot type' => 'Shot type',
            'Subject category' => 'Subject category',
            'Primary subject' => 'Primary subject',
            'Other subject' => 'Other subject',
            'Appearance' => 'Appearance',
            'Actions' => 'Actions',
            'Time' => 'Time',
            'Habitat' => 'Habitat',
            'Concept' => 'Concept',
            'Location' => 'Location',
            'Country' => 'Country',
            'Brand' => 'Brand',
        );
    }

    public function get_row() {
        $dataRow = $this->reader->getNextRow();
        if ($dataRow) {
            $dataRow['Date'] = PHPExcel_Style_NumberFormat::toFormattedString(
                            $dataRow['Date'], 'DD-MMM-YYYY');
        }
        return $dataRow;
    }

    public function load_data($lang = 'en') {
        $loader = new TestLoader($this->reader, $lang);
        $loader->process();
    }

    public function get_import_results() {
        return array(
            'total' => TestLoader::$total_rows,
            'imported' => TestLoader::$imported_rows
        );
    }

}
