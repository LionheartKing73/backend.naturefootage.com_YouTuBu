<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/17/17
 * Time: 20:31
 */
class Species_model extends Taxonomy_model
{
    protected $_table = 'sp_species';

    function __construct()
    {
        parent::__construct();
    }

    public function getHierarchyByFamily($family_id)
    {

        $select = [
            'sp_families.name as `family_name`',
            'sp_families.id as `family_id`',
            'sp_orders.name as `order_name`',
            'sp_orders.id as `order_id`',
            'sp_classes.name as `class_name`',
            'sp_classes.id as `class_id`',
            'sp_phylums.name as `phylum_name`',
            'sp_phylums.id as `phylum_id`',
            'sp_kingdoms.name as `kingdom_name`',
            'sp_kingdoms.id as `kingdom_id`',
        ];
        $select_string = implode(',', $select);
        $species_query = $this->db
            ->from($this->getTable())
            ->where('sp_common_names.family_group_id', $family_id)
            ->select($select_string)
            ->join('sp_common_names', 'sp_species.id = sp_common_names.species_id')
            ->join('sp_families', 'sp_species.family_id = sp_families.id')
            ->join('sp_orders', 'sp_families.order_id = sp_orders.id')
            ->join('sp_classes', 'sp_orders.class_id = sp_classes.id')
            ->join('sp_phylums', 'sp_classes.phylum_id = sp_phylums.id')
            ->join('sp_kingdoms', 'sp_phylums.kingdom_id = sp_kingdoms.id')
            ->limit(1)
            ->get();
        $result = $species_query->result();
        if ($result && is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            $q = $this->db->last_query();
            self::mailDebug("Query with result = 0 \n {$q}");
            return false;
        }
    }

    public static function mailDebug($message)
    {
        $time = new \DateTime();
        $time_string = $time->format("Y-m-d H:i:s");
        mail('ee923925@gmail.com', "WordPress NF-Species ($time_string)", $message);
    }
}