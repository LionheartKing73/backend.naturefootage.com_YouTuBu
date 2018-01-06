<?php
/**
 * Class for converting plural to singular
 */
require_once(APPPATH . 'libraries/Inflect.php');

class AddLandSpecies extends CI_Controller {

    CONST RANK_KINGDOM = 10;
    CONST RANK_PHYLUM = 30;
    CONST RANK_CLASS = 60;
    CONST RANK_ORDER = 100;
    CONST RANK_FAMILY = 140;
    CONST RANK_GENUS = 180;
    CONST RANK_SPECIES = 220;

    /**
     * Table land_species
     */
    CONST TABLE_TAXONOMIC_UNITS = 'taxonomic_units';
    CONST TABLE_HIERARCHY = 'hierarchy';
    CONST TABLE_VERNACULARS = 'vernaculars';
    CONST FIELD_RANK_ID = 'rank_id';
    CONST LAST_COMMON_NAME_MARINE_INSERTED_ID = 8452;
    CONST LAST_SPECIES_MARINE_INSERTED_ID = 6192;

    /**
     * The main table
     */
    CONST TABLE_COMMON_NAMES = 'sp_common_names';
    CONST TABLE_SPECIES = 'sp_species';
    CONST TABLE_BROWSE_CATEGORIES = 'sp_browse_categories';

    protected $_kingdoms = [];
    protected $_phylums = [];
    protected $_classes = [];
    protected $_orders = [];
    protected $_families = [];
    protected $_species = [];
    protected $_browse_categories = [];
    protected $_family_groups = [];

    protected $landDB = null;

    /**
     * Return array of types or specific type by RANK
     * @param int|null $rank
     * @return array|bool|string
     */
    public static function getTypeByRank($rank = null)
    {
        $arr = [
            'kingdom' => self::RANK_KINGDOM,
            'phylum' => self::RANK_PHYLUM,
            'class' => self::RANK_CLASS,
            'order' => self::RANK_ORDER,
            'family' => self::RANK_FAMILY,
            'genus' => self::RANK_GENUS,
            'species' => self::RANK_SPECIES,
        ];
        if ($rank) {
            if ( in_array($rank, $arr) ) {
                return array_search($rank, $arr);
            } else {
                return false;
            }
        } else {
            return $arr;
        }
    }

    /**
     * cache tsn info
     * @var array
     */
    protected $_tsnInfo = [];
    /**
     * Return DB instance
     * @return object
     */
    public function getLandDB()
    {
        if ( ! $this->landDB ) {
            $this->landDB = $this->load->database('land_species', TRUE);
        }
        return $this->landDB;
    }

    function __construct() {
        parent::__construct();
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
     * @param $row
     * @return int
     * @throws Exception
     */
    protected function saveKingdom($row)
    {
        $kingdom_name = $row['kingdom'];
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
     * @param $row
     * @param int $kingdom_id
     * @return int
     * @throws Exception
     */
    protected function savePhylum($row, $kingdom_id)
    {
        $phylum_name = $row['phylum'];
        if ( false && isset($this->_phylums[$phylum_name]) ) {
            $phylum_id = $this->_phylums[$phylum_name];
        } else {
            $phylum_model = new Phylum_model();
            $phylum_id = $phylum_model->firstOrNew('name', $phylum_name, ['kingdom_id' => $kingdom_id]);
            //$this->_phylums[$phylum_name] = $phylum_id;
        }
        return $phylum_id;
    }

    /**
     * @param $row
     * @param int $phylum_id
     * @return int
     * @throws Exception
     */
    protected function saveClass($row, $phylum_id)
    {
        $class_name = $row['class'];
        if ( false && isset($this->_classes[$class_name]) ) {
            $class_id = $this->_classes[$class_name];
        } else {
            $class_model = new Class_model();
            $class_id = $class_model->firstOrNew('name', $class_name, ['phylum_id' => $phylum_id]);
            //$this->_classes[$class_name] = $class_id;
        }
        return $class_id;
    }

    /**
     * @param $row
     * @param int $class_id
     * @return int
     * @throws Exception
     */
    protected function saveOrder($row, $class_id)
    {
        $order_name = $row['order'];
        if ( false && isset($this->_orders[$order_name]) ) {
            $order_id = $this->_orders[$order_name];
        } else {
            $order_model = new Order_model();
            $order_id = $order_model->firstOrNew('name', $order_name, ['class_id' => $class_id]);
            //$this->_orders[$order_name] = $order_id;
        }
        return $order_id;
    }

    /**
     * @param $row
     * @param int $order_id
     * @return int
     * @throws Exception
     */
    protected function saveFamily($row, $order_id)
    {
        $family_name = $row['family'];
        if ( false && isset($this->_families[$family_name]) ) {
            $family_id = $this->_families[$family_name];
        } else {
            $family_model = new Family_model();
            $family_id = $family_model->firstOrNew('name', $family_name, ['order_id' => $order_id]);
            //$this->_families[$family_name] = $family_id;
        }
        return $family_id;
    }

    /**
     * @param $row
     * @param int $family_id
     * @return int
     * @throws Exception
     */
    protected function saveSpecies($row, $family_id)
    {
        $species_name = $row['species'];
        $genus_name = $row['genus'];
        $family_model = new Species_model();
        $species_id = $family_model->firstOrNew('name', $species_name, [
            'family_id' => $family_id,
            'genus_name' => $genus_name,
            'from_land_db' => 1,
        ]);
        return $species_id;
    }

    protected  function getBrowseCategoryIdByPostId($postId)
    {
        if ( ! isset($this->_browse_categories[$postId]) ) {
            $query = $this->db->get_where(self::TABLE_BROWSE_CATEGORIES, [
                'post_id' => $postId,
            ]);
            $rows = $query->result();
            $this->_browse_categories[$postId] = $rows[0]->id;
        }
        return $this->_browse_categories[$postId];
    }

    /**
     * @param $row
     * @param int $browse_category_id
     * @return int
     * @throws Exception
     */
    protected function saveFamilyGroup($row, $postId)
    {
        if ($postId) {
            $browse_id = $this->getBrowseCategoryIdByPostId($postId);
        }

        $family_group_name = $row['family_group'];
        if ( false &&  isset($this->_family_groups[$family_group_name]) ) {
            $family_group_id = $this->_family_groups[$family_group_name];
        } else {
            $family_group_model = new Family_group_model();
            $family_group_id = $family_group_model->firstOrNew('name', $family_group_name, [
                'browse_category_id' => $postId,
                'from_land_db' => 1,
            ]);
            //$this->_family_groups[$family_group_name] = $family_group_id;
        }
        if ($family_group_id && $postId) {
            //echo "New Family Group  = `{$family_group_name}` id=`{$family_group_id}` browse_id=`{$browse_id}`  postId = `{$postId}`\n";
        }
        return $family_group_id;
    }

    /**
     * @param $row
     * @param int $family_group_id
     * @param int $species_id
     * @return int mixed
     * @throws Exception
     */
    protected function saveCommonName($row, $family_group_id, $species_id)
    {
        $common_name = $row['common_name'];
        $common_name_model = new Common_name_model();
        $common_name_id = $common_name_model->firstOrNew('name', $common_name, [
            'family_group_id' => $family_group_id,
            'species_id' => $species_id,
            'from_land_db' => 1,
        ]);
        return $common_name_id;
    }

    /**
     * Gets hierarchy sting from the table `hierarchy`
     * @param int $tsn
     * @return object
     */
    protected function getParents($tsn)
    {
        $db = $this->getLandDB();
        $query = $db->get_where(self::TABLE_HIERARCHY, [
            'TSN' => $tsn,
        ], 1);
        $rows = $query->result();
        return $rows[0];
    }

    /**
     * Gets info from the table `taxon_units`.
     * If info is not cached goes to the DB
     * @param int $tsn
     * @return mixed
     */
    protected function getInfoByTsn($tsn)
    {
        if ( ! isset($this->_tsnInfo[$tsn]) ) {
            $db = $this->getLandDB();
            $query = $db->get_where(self::TABLE_TAXONOMIC_UNITS, [
                'tsn' => $tsn,
            ], 1);
            $rows = $query->result();
            $this->_tsnInfo[$tsn] =  $rows[0];
        }
        return $this->_tsnInfo[$tsn];
    }

    /**
     * Tries fo get english name form the table `vernaculars`
     * Not all of species has vernacular english name
     * @param int $tsn
     * @return string|null
     */
    protected function getVernacular($tsn)
    {
        $db = $this->getLandDB();
        $query = $db->get_where(self::TABLE_VERNACULARS, [
            'tsn' => $tsn,
            'language' => 'English',
        ], 1);
        $rows = $query->result();
        if ( $rows && is_array($rows) && count($rows) ) {
            return $rows[0]->vernacular_name;
        } else {
            return null;
        }
    }

    /**
     * According to task tries to bind species with the POSTS in WordPress
     * @param array $row
     * @return bool|int
     */
    protected function findBrowseCategory($row)
    {
        $row_phylum = strtolower( trim( $row['phylum'] ) );
        $row_class = strtolower( trim( $row['class'] ) );
        $row_order = strtolower( trim( $row['order'] ) );
        $row_kingdom = strtolower( trim( $row['kingdom'] ) );
        $row_family = strtolower( trim( $row['family'] ) );
        $row_genus = strtolower( trim( $row['genus'] ) );
        $row_species = strtolower( trim( $row['species'] ) );
        if (
            $row_phylum == 'arthropoda'
            || $row_phylum == 'mollusca'
            || $row_phylum == 'annelida'
            || $row_phylum == 'onychophora'
            || $row_phylum == 'nematoda'
            || $row_phylum == 'nematomorpha'
            || $row_phylum == 'nemertea'
            || $row_phylum == 'platyhelminthes'
        ) {
            return AddSpecies::INSECTS;
        } else if ($row_class == 'aves') {
            return AddSpecies::BIRDS;
        } else if ($row_kingdom == 'Chromista') {
            return AddSpecies::MARINE_PLANTS_AND_ALGAE;
        } else if ($row_kingdom == 'bacteria') {
            return AddSpecies::BACTERIA;
        } else if (
            $row_class == 'actinopterygii'
            || $row_class == 'sarcopterygii'
            || $row_class == 'chondrichthyes'
        ) {
            return AddSpecies::FISH;
        } else if ($row_kingdom == 'fungi') {
            return AddSpecies::FUNGI;
        } else if ($row_class == 'amphibia') {
            return AddSpecies::AMPHIBIANS;
        } else if ($row_class == 'mammalia') {
            return AddSpecies::MAMMALS;
        } else if ($row_kingdom == 'plantae') {
            return AddSpecies::PLANTS;
        } else if ($row_class == 'reptilia') {
            return AddSpecies::REPTILES;
        } else if (
            $row_phylum == 'cnidaria'
            || $row_phylum == 'echinodermata'
            || $row_phylum == 'porifera'
            || $row_phylum == 'chaetognatha'
            || $row_phylum == 'gnathostomulida'
            || $row_phylum == 'hemichordata'
            || $row_phylum == 'phoronida'
            || $row_phylum == 'sipuncula'
            //|| $row_phylum == 'platyhelminthes'
            || $row_phylum == 'brachiopoda'
            || $row_phylum == 'bryozoa'
            || $row_phylum == 'ctenophora'
            || $row_phylum == 'gastrotricha'
            || $row_phylum == 'entoprocta'
            || $row_phylum == 'kinorhyncha'
            || $row_phylum == 'loricifera'
            || $row_phylum == 'orthonectida'
            || $row_phylum == 'xenacoelomorpha'
            || $row_phylum == 'placozoa'
            || $row_phylum == 'priapulida'
            /**
             * Orders
             */
            || $row_order == 'myzostomida'
            || $row_order == 'xiphosura'
            /**
             * Classes
             */
            || $row_class == 'pycnogonida'
            || $row_class == 'ascidiacea'
            || $row_class == 'thaliacea'
            || $row_class == 'appendicularia'
            || $row_class == 'turbellaria'
            || $row_class == 'bivalvia'
            || $row_class == 'cephalopoda'
            || $row_class == 'trematoda' // Monogenea - is SubClass. It belongs to Trematoda
            /**
             * Families
             */
            || $row_family == 'aglajidae'
            || $row_family == 'arminidae'
            || $row_family == 'chromodorididae'
            || $row_family == 'costasiellidae'
            || $row_family == 'cypraeidae'
            || $row_family == 'cymbuliidae'
            || $row_family == 'ovulidae'
            || $row_family == 'convolutidae'
            || $row_family == 'diopisthoporidae'
            || $row_family == 'otocelididae'
            || $row_family == 'proporidae'
            || $row_family == 'actinoposthiidae' // doesn't exist
            || $row_family == 'anaperidae' // doesn't exist
            || $row_family == 'antigonariidae' // doesn't exist
            || $row_family == 'antroposthiidae' // doesn't exist
            || $row_family == 'dakuidae' // doesn't exist
            || $row_family == 'hallangiidae' // doesn't exist
            || $row_family == 'haploposthiidae' // doesn't exist
            || $row_family == 'hofsteniidae' // doesn't exist
            || $row_family == 'isodiametridae' // doesn't exist
            || $row_family == 'mecynostomidae' // doesn't exist
            || $row_family == 'nadinidae' // doesn't exist
            || $row_family == 'paratomellidae' // doesn't exist
            || $row_family == 'polycanthiidae' // doesn't exist
            || $row_family == 'sagittiferidae' // doesn't exist
            || $row_family == 'solenofilomorphidae' // doesn't exist
            || $row_family == 'taurididae' // doesn't exist
            /**
             * Genus
             */
            || $row_genus == 'childia' // doesn't exist
        ) {
            return AddSpecies::MARINE_INVERTEBRATES;
        } else {
            log_message('debug', "Cannot define which browse page it belongs to");
            return false;
        }
    }

    protected function saveRow($row)
    {
        /**
         * Compare to common_names
         */
        $query = $this->db->get_where(self::TABLE_COMMON_NAMES, [
            'name' => $row['common_name']
        ], 1);
        $common_name_exists_already = $query->num_rows();
        if ($common_name_exists_already) { return false; }

        $kingdom_id = $this->saveKingdom($row);
        $phylum_id = $this->savePhylum($row, $kingdom_id);
        $class_id = $this->saveClass($row, $phylum_id);
        $order_id = $this->saveOrder($row, $class_id);
        $family_id = $this->saveFamily($row, $order_id);
        $species_id = $this->saveSpecies($row, $family_id);

        /**
         * Try to find Browse Category
         */
        $browse_category_id = $this->findBrowseCategory($row);
        $browse_category_id = $browse_category_id ? $browse_category_id : 0;
        $family_group_id = $this->saveFamilyGroup($row, $browse_category_id);
        $result = $common_name_id = $this->saveCommonName($row, $family_group_id, $species_id);
        return $result;
    }

    public function checkIfSuchRecordExists($complete_name)
    {
        $arr = explode(' ', $complete_name);

        $query = $this->db->from(self::TABLE_SPECIES);
        $query->where('genus_name', $arr[0]);
        $query->where('name', $arr[1]);
        $query->limit(1);
        $result = $query->count_all_results();
        if ($result) {
            echo "`$complete_name` - already exists\n";
        }
        return $result;
    }

    public function index($startIndex = 0) {
        ini_set("memory_limit",-1);
        require_once('addSpecies.php');
        $start = new \DateTime();
        $db = $this->getLandDB();
        $limit = 1000;

        $select = [
            self::TABLE_TAXONOMIC_UNITS . '.tsn',
            self::TABLE_TAXONOMIC_UNITS . '.complete_name',
            self::TABLE_TAXONOMIC_UNITS . '.name_usage',
            self::TABLE_TAXONOMIC_UNITS . '.rank_id',
            self::TABLE_TAXONOMIC_UNITS . '.kingdom_id',

            self::TABLE_HIERARCHY . '.hierarchy_string',
        ];
        $select_string = implode(',', $select);

        $num_rows = $db->from(self::TABLE_TAXONOMIC_UNITS)
            ->select($select_string)
            ->where([
                self::FIELD_RANK_ID => self::RANK_SPECIES,
            ])
            ->where(self::TABLE_HIERARCHY . '.hierarchy_string IS NOT NULL', null, false)
            ->join(self::TABLE_HIERARCHY, self::TABLE_HIERARCHY . '.TSN = ' . self::TABLE_TAXONOMIC_UNITS . '.tsn')
            ->where_in(self::TABLE_TAXONOMIC_UNITS . '.name_usage', ['valid', 'accepted'])
            ->order_by(self::TABLE_TAXONOMIC_UNITS . ".tsn", "desc")
            ->count_all_results();
        $total_iteration = (int) ($num_rows / $limit) + 1;
        $startIndex = (int) $startIndex;
        for (
                $i = $startIndex && $startIndex < $total_iteration ? $startIndex : 0;
                $i < $total_iteration;
                $i++
        ) {
            echo "{$i}*1000\n";
            self::mailDebug("RUNNING {$i}*1000/{$num_rows}");
            $query = $db->from(self::TABLE_TAXONOMIC_UNITS)
                ->select($select_string)
                ->where([
                    self::FIELD_RANK_ID => self::RANK_SPECIES,
                ])
                ->where(self::TABLE_HIERARCHY . '.hierarchy_string IS NOT NULL', null, false)
                ->join(self::TABLE_HIERARCHY, self::TABLE_HIERARCHY . '.TSN = ' . self::TABLE_TAXONOMIC_UNITS . '.tsn')
                ->where_in(self::TABLE_TAXONOMIC_UNITS . '.name_usage', ['valid', 'accepted'])
                ->limit($limit, $i*$limit)
                ->order_by(self::TABLE_TAXONOMIC_UNITS . ".tsn", "desc")
                ->get();

            //echo $db->last_query();
            //die();

            /*SELECT u.tsn, u.complete_name, u.name_usage, u.rank_id, h.hierarchy_string FROM `taxonomic_units` as u
JOIN hierarchy as h ON h.TSN = u.tsn
WHERE h.TSN is not null
LIMIT 0, 1000*/

            $rows = $query->result();
            foreach ($rows as $row) {
                $exists = $this->checkIfSuchRecordExists($row->complete_name);
                if ($exists) {
                    echo "continue row->complete_name=`{$row->complete_name}` tsn=`{$row->tsn}`\n";
                    continue;
                }

                /**
                 * without join hierarchy
                 */
                /*$parents = $this->getParents($row->tsn);
                if ( ! $parents ) {
                    self::mailDebug("parents don't exist tsn={$row->tsn}\n");
                    log_message('debug', "parents don't exist tsn={$row->tsn}\n");
                    continue;
                }*/
                $parents_array = explode('-', $row->hierarchy_string);
                $full_row_info = [];
                foreach ($parents_array as $parent_tsn) {
                    $info = $this->getInfoByTsn($parent_tsn);
                    $type = $this->getTypeByRank($info->rank_id);
                    if ($type) {
                        $full_row_info[$type] = $info->complete_name;
                    }
                }
                /**
                 * Check whether all parents exist
                 */
                foreach ($this->getTypeByRank() as $type => $rank) {
                    if ( ! isset($full_row_info[$type]) ) {
                        /**
                         * Skip species if one of the parents missed
                         */
                        continue 2;
                    }
                }
                /**
                 * Get common name
                 */
                $english_name = $this->getVernacular($row->tsn);
                $full_row_info['common_name'] =  $english_name ? Inflect::singularize($english_name) : $row->complete_name;
                $family_group_array = explode(' ', $full_row_info['common_name']);
                /**
                 * If vernacular_name exists take the last word of vernacular_name.
                 * If not, take the first word of the species name
                 */
                $full_row_info['family_group'] = $english_name ? Inflect::singularize( end($family_group_array) ) : $full_row_info['family'];

                /**
                 * Starting saving data into DB
                 */
                $result = $this->saveRow($full_row_info);
            }
            gc_collect_cycles();
            //$db->flush_cache();
        }
        $end = new \DateTime();
        $diff = $end->diff($start);
        $time_string = "Time: " . $diff->format("addLandSpecies::index lasted: %H hours, %I minutes, %S seconds");
        self::mailDebug($time_string);
        log_message('debug', $time_string);
        echo "\n========================================\n",
        $time_string,
        "\n========================================\n";
    }

    public static function mailDebug($message)
    {
        mail('ee923925@gmail.com', 'addLandSpecies', $message);
    }
}