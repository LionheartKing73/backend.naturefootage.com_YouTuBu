<?php

namespace SortRating\Collector\ClipTable;

use SortRating\Collector\AbstractClipData;

/**
 * Class Q2Views
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class Q2Views extends AbstractClipData
{

    /**
     * get {$this->_stateTable} field name
     * @return string
     */
    protected function _stateField()
    {
        return 'q2_views';
    }

    /**
     * get {$this->_clipsTable} field name
     * @return string
     */
    protected function _countField()
    {
        return 'viewed';
    }
}