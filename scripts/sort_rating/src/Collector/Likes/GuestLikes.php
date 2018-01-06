<?php

namespace SortRating\Collector\Likes;

use SortRating\Collector\AbstractLikes;

/**
 * Class GuestLikes
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class GuestLikes extends AbstractLikes
{

    /**
     * get value for {$_likesTable}.name field
     * @return string
     */
    protected function _name()
    {
        return 'ip_rating';
    }

    /**
     * get {$_stateTable}.field_name to set value to
     * @return string
     */
    protected function _field()
    {
        return 'guest_likes';
    }
}