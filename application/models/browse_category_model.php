<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/17/17
 * Time: 20:31
 */
class Browse_category_model extends Taxonomy_model
{
    protected $_table = 'sp_browse_categories';

    function __construct()
    {
        parent::__construct();
    }
}