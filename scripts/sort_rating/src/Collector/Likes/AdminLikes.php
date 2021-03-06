<?php

namespace SortRating\Collector\Likes;

use SortRating\Collector\AbstractLikes;
/**
 * Class AdminLikes
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class AdminLikes extends AbstractLikes
{

    /**
     * get value for {$_likesTable}.name field
     * @return string
     */
    protected function _name()
    {
        return 'admin_rating';
    }

    /**
     * get {$_stateTable}.field_name to set value to
     * @return string
     */
    protected function _field()
    {
        return 'admin_likes';
    }
}