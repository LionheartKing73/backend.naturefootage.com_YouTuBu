<?php

namespace SortRating\Collector\Likes;

use SortRating\Collector\AbstractLikes;

/**
 * Class UserLikes
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class UserLikes extends AbstractLikes
{

    /**
     * get value for {$_likesTable}.name field
     * @return string
     */
    protected function _name()
    {
        return 'user_rating';
    }

    /**
     * get {$_stateTable}.field_name to set value to
     * @return string
     */
    protected function _field()
    {
        return 'user_likes';
    }
}