<?php

/**
 * Class Rank_clips_model
 * @autor Klovak Dmitry
 */
class Rank_clips_model extends CI_Model {

    private $_table='lib_rank_clips';
    private $_maxLimit=100;
    private $_minLimit=0;
    public $ADD_CLIP_RANK=10;

    function Rank_clips_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }
    
    #------------------------------------------------------------------------------------------------
    /**
     * @param $clipId
     * @return int weight
     */
    function get_rank($clipId)
    {
        $query = $this->db->get_where($this->_table, array('clip_id'=>$clipId),1);
        $row = $query->result_array();
        return $row[0]['weight'];
    }
    
    #------------------------------------------------------------------------------------------------
    /**
     * @param $clipId
     * @param $weight
     * @param string $action arifmetic (+,-,/,*)
     * @return bool
     */
    function set_rank($clipId,$weight,$action='+')
    {
        $action= ($action !='+' and $action !='-' and $action !='*' and $action !='/') ? '+' : $action;

        $this->load->model( 'clips_model' );

        $rank=$this->get_rank($clipId);
        if($rank==null){
            $res= $this->db_master->insert($this->_table,array('clip_id'=>$clipId,'weight'=>$weight,'create_time'=>date('Y-m-d H:i:s')));
        }else{
            $weight=$this->arifmetic($rank,$weight,$action);
            $res= $this->db_master->query('UPDATE '.$this->_table.' SET `weight` = '.intval($weight).' WHERE clip_id='.intval($clipId));
        }
        //$this->clips_model->add_to_index( $clipId, false );
        return $res;
    }
    private function arifmetic($oldRank,$newRank,$action){
        switch($action){
            case '+': return ($oldRank+$newRank >=$this->_maxLimit) ? $this->_maxLimit : $oldRank+$newRank; break;
            case '-': return ($oldRank-$newRank <=$this->_minLimit) ? $this->_minLimit : $oldRank-$newRank; break;
            default : return ($oldRank+$newRank >=$this->_maxLimit) ? $this->_maxLimit : $oldRank+$newRank; break;
        }
    }
    
    #------------------------------------------------------------------------------------------------
}
