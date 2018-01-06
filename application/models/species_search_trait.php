<?php
/**
 * Created by PhpStorm.
 * User: bahek2462774
 * Date: 5/19/17
 * Time: 16:41
 */
trait Species_search_trait
{
    /**
     * @return	array
     */
    public function getEmpty()
    {
        $query = $this->db->from($this->_table)
            ->where('result', 0)
            ->order_by('id', 'desc')
            ->get();
        $rows = $query->result();
        return $rows;
    }
}