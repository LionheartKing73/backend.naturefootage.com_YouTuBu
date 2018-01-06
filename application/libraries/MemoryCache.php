<?php

/**
 * Class MemoryCache
 *
 * @author nikita.bunenkov
 */
class CI_MemoryCache
{
    private $storage = [];


    /**
     * clear cache array
     *
     * @return  void
     */
    public function clear()
    {
        $this->cache = [];
    }

    /**
     * get value from cache by id
     *
     * @param $id
     *
     * @return string|null
     */
    public function get($id)
    {
        return isset($this->cache[$id]) ? $this->cache['id'] : null;
    }

    /**
     * set data to cache
     *
     * @param $id
     * @param $data
     *
     * @return void
     */
    public function set($id, $data)
    {
        $this->cache[$id] = $data;
    }

    /**
     * remove data from cache
     *
     * @param $id
     *
     * @return void
     */
    public function remove($id)
    {
        unset($this->cache[$id]);
    }
}
