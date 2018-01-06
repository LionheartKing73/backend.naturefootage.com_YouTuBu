<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/17/17
 * Time: 20:31
 */

/**
 * Load the searchable trait
 * This trait is also used in the Common_name_model
 */
require_once('species_search_trait.php');

class Family_group_model extends Taxonomy_model
{
    use Species_search_trait;

    protected $_table = 'sp_family_groups';

    function __construct()
    {
        parent::__construct();
    }
}