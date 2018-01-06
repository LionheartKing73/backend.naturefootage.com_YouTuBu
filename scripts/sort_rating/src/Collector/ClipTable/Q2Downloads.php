<?php

namespace SortRating\Collector\ClipTable;

use SortRating\Collector\AbstractClipData;

/**
 * Class Q2Downloads
 * @package SortRating\Collector
 * @author nikita.bunenkov
 */
class Q2Downloads extends AbstractClipData
{

    /**
     * get {$this->_stateTable} field name
     * @return string
     */
    protected function _stateField()
    {
        return 'q2_downloads';
    }

    /**
     * get {$this->_clipsTable} field name
     * @return string
     */
    protected function _countField()
    {
        return 'downloaded';
    }
}