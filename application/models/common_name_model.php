<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/17/17
 * Time: 20:31
 */

/**
 * Load the searchable trait
 * This trait is also used in the Family_group_model
 */
require_once('species_search_trait.php');

class Common_name_model extends Taxonomy_model
{
    use Species_search_trait;

    protected $_table = 'sp_common_names';

    function __construct()
    {
        parent::__construct();
    }
}