<?php
/**
 * Class for converting plural to singular
 */
require_once(APPPATH . 'libraries/Inflect.php');

class AddSpecies extends CI_Controller {

    CONST SHARKS  = 4102;
    CONST WHALES  = 3948;
    CONST AMPHIBIANS  = 3741;
    CONST INSECTS  = 3844;
    CONST PLANTS  = 3882;
    CONST MARINE_PLANTS_AND_ALGAE  = 7114;
    CONST FUNGI  = 7112;
    CONST PRIMATES  = 3884;
    CONST DOLPHINS  = 3934;
    CONST FISH  = 3936;
    CONST MARINE_INVERTEBRATES  = 3939;
    CONST BIRDS  = 3768;
    CONST MARINE_BIRDS  = 3773;
    CONST MAMMALS  = 3860;
    CONST MARINE_MAMMALS  = 3868;
    CONST REPTILES  = 3888;
    CONST MARINE_REPTILES  = 4090;
    CONST BACTERIA  = 7137;

    protected $_kingdoms = [];
    protected $_phylums = [];
    protected $_classes = [];
    protected $_orders = [];
    protected $_families = [];
    protected $_species = [];
    protected $_browse_categories = [];
    protected $_family_groups = [];

    protected function getBrowsePagesList($post_id = null)
    {
        $arr =  [
            'whales' => self::WHALES,
            'sharks' => self::SHARKS,
            'insects' => self::INSECTS,
            'plants' => self::PLANTS,
            'marine_plants_and_algae' => self::MARINE_PLANTS_AND_ALGAE,
            'fungi' => self::FUNGI,
            'primates' => self::PRIMATES,
            'dolphins' => self::DOLPHINS,
            'fish' => self::FISH,
            'marine_invertebrates' => self::MARINE_INVERTEBRATES,
            'birds' => self::BIRDS,
            'marine_birds' => self::MARINE_BIRDS,
            'mammals' => self::MAMMALS,
            'marine_mammals' => self::MARINE_MAMMALS,
            'reptiles' => self::REPTILES,
            'marine_reptiles' => self::MARINE_REPTILES,
            'amphibians' => self::AMPHIBIANS,
            'bacteria' => self::BACTERIA,
        ];
        if ($post_id) {
            $key = array_search($post_id, $arr);
            return $key ? $key : null;
        } else {
            return $arr;
        }
    }
    
    
    function __construct() {
        parent::__construct();
        /**
         * Truncate data for tasting
         * For Testing
         */
        /*$this->db->truncate('sp_kingdoms');
        $this->db->truncate('sp_phylums');
        $this->db->truncate('sp_classes');
        $this->db->truncate('sp_orders');
        $this->db->truncate('sp_families');
        $this->db->truncate('sp_species');
        $this->db->truncate('sp_browse_categories');
        $this->db->truncate('sp_family_groups');
        $this->db->truncate('sp_common_names');*/


        $this->load->model('Base_model');
        $this->load->model('Taxonomy_model');
        $this->load->model('Kingdom_model');
        $this->load->model('Phylum_model');
        $this->load->model('Class_model');
        $this->load->model('Order_model');
        $this->load->model('Family_model');
        $this->load->model('Species_model');
        $this->load->model('Browse_category_model');
        $this->load->model('Family_group_model');
        $this->load->model('Common_name_model');
    }

    /**
     * Return the ID of Browse Page from WordPress
     * @return int|null
     */
    protected function _getPostId($browse_category_name)
    {
        $browse_category_name = trim($browse_category_name);
        /**
         * There are only Marine Species in the Marine Species List XLS.
         * Therefore we have the same IDS.
         */
        $array =  [
            'Birds' => self::MARINE_BIRDS,
            'Marine Birds' => self::MARINE_BIRDS,
            'Insects' => self::INSECTS,
            'Mammals' => self::MARINE_MAMMALS,
            'Marine Mammals' => self::MARINE_MAMMALS,
            'Plants' => self::MARINE_PLANTS_AND_ALGAE,
            'Plant' => self::MARINE_PLANTS_AND_ALGAE,
            'Primates' => self::PRIMATES,
            'Dolphins' => self::DOLPHINS,
            'Fish' => self::FISH,
            /**
             * There are a few mistakes in the Marine XLS file
             */
            'Marine Invertebrates' => self::MARINE_INVERTEBRATES,
            'Invertbrates' => self::MARINE_INVERTEBRATES,
            'Invertebrate' => self::MARINE_INVERTEBRATES,
            'Invertebrates' => self::MARINE_INVERTEBRATES,
            'Marine Reptiles' => self::MARINE_REPTILES,
            'Reptiles' => self::MARINE_REPTILES,
            'Reptile' => self::MARINE_REPTILES,
            'Sharks' => self::SHARKS,
            'Whales' => self::WHALES,
        ];
        if ( isset($array[$browse_category_name]) ) {
            return $array[$browse_category_name];
        } else {
            log_message('debug', "\nCategory name '{$browse_category_name}' doesn't exist\n");
            return null;
        }
    }

    /**
     * @param $name string
     * @return string
     * @throws Exception
     */
    protected function _getColumnLetterByName($name)
    {

        $array =  [
            /**
             * According to XLS structure
             */
            /*
            'A' =>  'common_name',
            'B' =>  'family_name',
            'C' =>  'browse_category',
            'D' =>  'Kingdom',
            'E' =>  'Phylum',
            'F' =>  'Class',
            'G' =>  'Order',
            'H' =>  'Family',
            'I' =>  'Genus species',
            'J' =>  'Genus',
            'K' =>  'Species',*/
            'kingdom_name' => 'D',
            'common_name' => 'A',
            'family_group_name' => 'B',
            'browse_category_name' => 'C',
            'phylum_name' => 'E',
            'class_name' => 'F',
            'order_name' => 'G',
            'family_name' => 'H',
            'genus_name' => 'J',
            'species_name' => 'I',
        ];
        if ( isset($array[$name]) ) {
            return $array[$name];
        } else {
            log_message('error', "Column name '{$name}' doesn't exist");
            throw new Exception("Column name '{$name}' doesn't exist");
        }
    }

    function index() {
        $start = new \DateTime();

        /**
         * Save browse pages list
         */
        $this->saveEntireBrowsePagesList();

        $speciesXls = FCPATH . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'species.xls';
        $objPHPExcel = PHPExcel_IOFactory::load($speciesXls);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        foreach ($objWorkSheet->getRowIterator(2) as $row) {
            $row_array = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $row_array[$cell->getColumn()] = $cell->getValue();
            }
            $this->saveRow($row_array);
        }
        $end = new \DateTime();
        $diff = $end->diff($start);
        $time_string = "Time: " . $diff->format("addSpecies::index lasted: %H hours, %I minutes, %S seconds");
        log_message('debug', $time_string);
        echo "\n========================================\n",
        $time_string,
        "\n========================================\n";
    }

    public function saveEntireBrowsePagesList()
    {
        $list = $this->getBrowsePagesList();
        foreach ($list as $name => $post_id) {
            $browse_category_model = new Browse_Category_model();
            $browse_category_id = $browse_category_model->firstOrNew('name', $name, ['post_id' => $post_id]);
            $this->_browse_categories[$name] = $browse_category_id;
        }
    }

    /**
     * @param array $row
     * @return int
     * @throws Exception
     */
    protected function saveKingdom(array $row)
    {
        $letter = $this->_getColumnLetterByName('kingdom_name');
        $kingdom_name = $row[$letter];
        if ( isset($this->_kingdoms[$kingdom_name]) ) {
            $kingdom_id = $this->_kingdoms[$kingdom_name];
        } else {
            $kingdom_model = new Kingdom_model();
            $kingdom_id = $kingdom_model->firstOrNew('name', $kingdom_name, []);
            $this->_kingdoms[$kingdom_name] = $kingdom_id;
        }
        return $kingdom_id;
    }

    /**
     * @param array $row
     * @param int $kingdom_id
     * @return int
     * @throws Exception
     */
    protected function savePhylum(array $row, $kingdom_id)
    {
        $letter = $this->_getColumnLetterByName('phylum_name');
        $phylum_name = $row[$letter];
        if ( isset($this->_phylums[$phylum_name]) ) {
            $phylum_id = $this->_phylums[$phylum_name];
        } else {
            $phylum_model = new Phylum_model();
            $phylum_id = $phylum_model->firstOrNew('name', $phylum_name, ['kingdom_id' => $kingdom_id]);
            $this->_phylums[$phylum_name] = $phylum_id;
        }
        return $phylum_id;
    }

    /**
     * @param array $row
     * @param int $phylum_id
     * @return int
     * @throws Exception
     */
    protected function saveClass(array $row, $phylum_id)
    {
        $letter = $this->_getColumnLetterByName('class_name');
        $class_name = $row[$letter];
        if ( isset($this->_classes[$class_name]) ) {
            $class_id = $this->_classes[$class_name];
        } else {
            $class_model = new Class_model();
            $class_id = $class_model->firstOrNew('name', $class_name, ['phylum_id' => $phylum_id]);
            $this->_classes[$class_name] = $class_id;
        }
        return $class_id;
    }

    /**
     * @param array $row
     * @param int $class_id
     * @return int
     * @throws Exception
     */
    protected function saveOrder(array $row, $class_id)
    {
        $letter = $this->_getColumnLetterByName('order_name');
        $order_name = $row[$letter];


        if ( isset($this->_orders[$order_name]) ) {
            $order_id = $this->_orders[$order_name];
        } else {
            $order_model = new Order_model();
            $order_id = $order_model->firstOrNew('name', $order_name, ['class_id' => $class_id]);
            $this->_orders[$order_name] = $order_id;
        }
        return $order_id;
    }

    /**
     * @param array $row
     * @param int $order_id
     * @return int
     * @throws Exception
     */
    protected function saveFamily(array $row, $order_id)
    {
        $letter = $this->_getColumnLetterByName('family_name');
        $family_name = $row[$letter];


        if ( isset($this->_families[$family_name]) ) {
            $family_id = $this->_families[$family_name];
        } else {
            $family_model = new Family_model();
            $family_id = $family_model->firstOrNew('name', $family_name, ['order_id' => $order_id]);
            $this->_families[$family_name] = $family_id;
        }
        return $family_id;
    }

    /**
     * @param array $row
     * @param int $family_id
     * @return int
     * @throws Exception
     */
    protected function saveSpecies(array $row, $family_id)
    {
        $species_name_letter = $this->_getColumnLetterByName('species_name');
        $species_name = $row[$species_name_letter];
        $genus_name_letter = $this->_getColumnLetterByName('genus_name');
        $genus_name = $row[$genus_name_letter];
        $family_model = new Species_model();
        $species_id = $family_model->firstOrNew('name', $species_name, ['family_id' => $family_id, 'genus_name' => $genus_name ]);
        return $species_id;
    }

    /**
     * @param array $row
     * @return int|null
     * @throws Exception
     */
    protected function saveBrowseCategory(array $row)
    {
        $letter = $this->_getColumnLetterByName('browse_category_name');
        $browse_category_name = $row[$letter];
        $post_id = $this->_getPostId($browse_category_name);
        $name_saved = $this->getBrowsePagesList($post_id);
        
        if ( isset($this->_browse_categories[$name_saved]) ) {
            $browse_category_id = $this->_browse_categories[$name_saved];
        } else {
            if ( ! $post_id ) {
                return null;
            }
            $browse_category_model = new Browse_Category_model();
            $browse_category_id = $browse_category_model->firstOrNew('name', $name_saved, ['post_id' => $post_id]);
            $this->_browse_categories[$name_saved] = $browse_category_id;
        }
        return $browse_category_id;
    }

    /**
     * @param array $row
     * @param int $browse_category_id
     * @return int
     * @throws Exception
     */
    protected function saveFamilyGroup(array $row, $browse_category_id)
    {
        $letter = $this->_getColumnLetterByName('family_group_name');
        $family_group_name = $row[$letter];

        /**
         * Plural to Singular
         */
        $family_group_name = Inflect::singularize($family_group_name);


        if ( isset($this->_family_groups[$family_group_name]) ) {
            $family_group_id = $this->_family_groups[$family_group_name];
        } else {
            $family_group_model = new Family_group_model();
            $family_group_id = $family_group_model->firstOrNew('name', $family_group_name, ['browse_category_id' => $browse_category_id]);
            $this->_family_groups[$family_group_name] = $family_group_id;
        }
        return $family_group_id;
    }

    /**
     * @param array $row
     * @param int $family_group_id
     * @param int $species_id
     * @return int mixed
     * @throws Exception
     */
    protected function saveCommonName(array $row, $family_group_id, $species_id)
    {
        $letter = $this->_getColumnLetterByName('common_name');
        $common_name = $row[$letter];

        /**
         * Plural to Singular
         */
        $common_name = Inflect::singularize($common_name);

        $common_name_model = new Common_name_model();
        $common_name_id = $common_name_model->firstOrNew('name', $common_name, ['family_group_id' => $family_group_id, 'species_id' => $species_id]);
        return $common_name_id;
    }

    /**
     * @param array $row
     * @return bool|int
     */
    protected function saveRow(array $row)
    {
        $kingdom_id = $this->saveKingdom($row);
        $phylum_id = $this->savePhylum($row, $kingdom_id);
        $class_id = $this->saveClass($row, $phylum_id);
        $order_id = $this->saveOrder($row, $class_id);
        $family_id = $this->saveFamily($row, $order_id);
        $species_id = $this->saveSpecies($row, $family_id);

        /**
         * Save searchable data
         */
        $browse_category_id = $this->saveBrowseCategory($row);
        if ( ! $browse_category_id ) {
            return false;
        }  else {
            $family_group_id = $this->saveFamilyGroup($row, $browse_category_id);
            return $common_name_id = $this->saveCommonName($row, $family_group_id, $species_id);
        }
    }
}