<?php

namespace SortRating;

use SortRating\Contracts\CollectorContainer;
use SortRating\Contracts\DataCollector;
use SortRating\Exceptions;
use SortRating\Log\Factory as LogFactory;

/**
 * Class Collector
 * @package SortRating
 * @author nikita.bunenkov
 */
class Collector implements CollectorContainer
{

    /**
     * @var array of \SortRating\Contracts\DataCollector
     */
    private $_dataCollectors;

    /**
     * create collector and attach data collectors from array of class names
     * @param array $classNames
     * @param \PDO $dbh
     * @return Collector
     */
    public static function fromArray(array $classNames, \PDO $dbh) {
        $collector = new static();
        if (!empty($classNames)) {
            foreach ($classNames as $class) {
                if (class_exists($class)) {
                    $dataCollector = new $class();
                    $dataCollector->setPDO($dbh);
                    $collector->attach($dataCollector);
                }
            }
        }

        return $collector;
    }

    /**
     * collect all changed data in a storage
     */
    public function run()
    {
        if (!empty($this->_dataCollectors)) {
            foreach ($this->_dataCollectors as $collector) {
                try {
                    $collector->collect();
                } catch (Exceptions\DataCollector $ex) {
                    // exception in one collector should not stop script execution
                    // handle exception: log write etc.
                    LogFactory::instance()->exception($ex);
                }
            }
        }
    }

    /**
     * attach new data collector
     * @param DataCollector $collector
     * @return $this
     */
    public function attach(DataCollector $collector)
    {
        if (!$this->_exists($collector)) {
            $this->_dataCollectors[] = $collector;
        }

        return $this;
    }

    /**
     * deattach collector
     * @param DataCollector $collector
     * @return $this
     */
    public function deattach(DataCollector $collector)
    {
        $index = $this->_getIndex($collector);
        if (!is_null($index)) {
            unset($this->_dataCollectors[$index]);
        }

        return $this;
    }

    /**
     * check if collector exists in collectors collection
     * @param DataCollector $collector
     * @return bool
     */
    private function _exists(DataCollector $collector) {
        return !is_null($this->_getIndex($collector));
    }

    /**
     * find collector by class name and returns its index in array
     * @param DataCollector $collector
     * @return integer|null
     */
    private function _getIndex(DataCollector $collector) {
        $key = null;

        if (!empty($this->_dataCollectors)) {
            foreach ($this->_dataCollectors as $index => $item) {
                if (get_class($collector) == get_class($item)) {
                    $key = $index;
                    break;
                }
            }
        }

        return $key;
    }
}