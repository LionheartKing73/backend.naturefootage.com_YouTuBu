<?php

/**
 * @property Clips_model $clips_model
 */
require_once FCPATH.'/scripts/aws/Psr/Log/LoggerInterface.php';
require_once FCPATH.'/scripts/aws/Monolog/Logger.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NullHandler;

class Clips_update extends CI_Controller {
    /** @var \Monolog\Logger */
    private $_monolog=null;
    public $sectionList = array (
        'shot_type'        => 'Shot Type',
        'subject_category' => 'Subject Category',
        'primary_subject'  => 'Primary Subject',
        'other_subject'    => 'Other Subject',
        'appearance'       => 'Appearance',
        'actions'          => 'Actions',
        'time'             => 'Time',
        'habitat'          => 'Habitat',
        'concept'          => 'Concept',
        'location'         => 'Location'
    );
    const LIM100=100;
    const LIM1000=1000;

    function __construct() {
        parent::__construct();
        $this->load->model('clips_model');
        $this->load->model('clips_update_model','cum');
        $this->db_master = $this->load->database('master', TRUE);
        if(empty($this->_monolog)){
            $this->_monolog=new Logger('Clips_update_logger');
            //$this->_monolog->pushHandler(new NullHandler());
            $this->_monolog->pushHandler(new StreamHandler(FCPATH.'system___debug.log', Logger::DEBUG));
        }
    }

    function index() {
        $this->_cycle('search_dublicat_keywords',0,2);
    }

    #------------------------------------------------------------------------------------------------


    function move_online_require_clips() {
        $rows=$this->cum->get_online_require_clips();
        if(count($rows)>0){
            $clipsIds = array();
            foreach ( $rows as $row ) {
                $clipsIds[]=$row['id'];
            }
            $clipsIdsStr=implode(',',$clipsIds);
            var_dump($clipsIds);
            $this->db_master->query("UPDATE lib_clips SET active = '1' WHERE id IN (".$clipsIdsStr.") ");
            $clipsIds = array();
            foreach ( $rows as $row ) {
                $this->clips_model->add_to_index($row['id']);
            }
        }
    }
    function del_dublicat_keywords_frontend_all($j=0){
        $limit=1000;
        for($i=$j;$i<320;$i++){
            $offset=$i*$limit;
            $this->del_dublicat_keywords_frontend($offset,$limit);
            sleep(2);
        }
    }
    function del_dublicat_keywords_frontend($offset,$limit){
        $count=$offset+$limit;
        $offset = (empty($offset))?0:$offset;
        $limit = (empty($limit))?'':' limit '.$offset.','.$limit;
        $rows=$this->db->query("SELECT `id`,`clip_id`,`keywords`,`shot_type`,`subject_category`,
                     `primary_subject`,`other_subject`,`appearance`,`actions`,
                     `time`,`habitat`,`concept`,`location` FROM `lib_clips_content` ".$limit)->result_array();
        $count.='-'.count($rows);
        if(count($rows)>0){

            foreach ( $rows as $key=>$row ) {
                foreach($row as $field=>$words){
                    $words=preg_replace("#(,| ) *#i", "\\1",$words);
                    $wordsArr=explode(',',$words);
                    $delDuplicates=array_unique($wordsArr);
                    $rows[$key][$field]=implode(',',$delDuplicates);
                }
                $clipsIds[]=$rows[$key]['id'];
                $this->db_master->update('lib_clips_content',$rows[$key],array('id'=>$rows[$key]['id']));
                $this->clips_model->add_to_index($rows[$key]['id']);
                echo $rows[$key]['id']."/".$count."-updated \r\n<br>";
            }
            /*foreach ( $rows as $k=>$v ) {
                $this->clips_model->add_to_index($v[$k]['id']);
            }*/
        }
    }

    /**
     * Search all keywords having > 1
     * @param $offset
     * @param $limit
     */
    function search_dublicat_keywords($offset,$limit){
        $rows=$this->cum->back_dublicate_keywords($offset,$limit);
        $this->_monolog->addDebug(__FUNCTION__.'->$rows',$rows);
        if($rows['data']){
            foreach($rows['data'] as $k=>$keyword){
                echo 'ID: '.$keyword['id'].'|'.$keyword['keyword'].'/'.$rows['count']."-update start \r\n<br>";
                foreach($this->sectionList as $section=>$section_title){
                    $this->_replace_dublicat_keywords($keyword['id'],$keyword['keyword'],$section,$keyword['provider_id']);
                }
                $this->_monolog->addDebug(__FUNCTION__.'->ID: '.$keyword['id'].'|'.$keyword['keyword'].'/'.$rows['count'].'-updated !',[]);
                echo '<br>###ID: '.$keyword['id'].'|'.$keyword['keyword'].'/'.$rows['count']."-updated! ---------------- \r\n\r\n<br>";
            }
        }else{echo $rows['count']."-Updated !\r\n";}
    }

    private function _replace_dublicat_keywords($id,$keyword,$section,$provider_id=0){
        $keywords=$this->db->query("SELECT * FROM `lib_keywords` WHERE `keyword` LIKE '".$keyword."' AND `section` LIKE '".$section."' AND old = 0 AND id !=".$id)->result_array();
        if(count($keywords)>0){
            // DEL DUBLICATE KEYWORDS
            $this->_monolog->addDebug(__FUNCTION__.'->QUERY',["SELECT * FROM `lib_keywords` WHERE `keyword` LIKE '".$keyword."' AND `section` LIKE '".$section."' AND id !=".$id]);
            $this->_monolog->addDebug(__FUNCTION__.'->$keywords',$keywords);
            $this->db_master->query("DELETE FROM `lib_keywords` WHERE `keyword` LIKE '".$keyword."' AND `section` LIKE '".$section."' AND old = 0 AND id !=".$id);
            $keywordsIds='';
            foreach($keywords as $k=>$item){
                $keywordsIds.=$item['id'].',';
            }
            $keywordsIds=substr($keywordsIds, 0, -1);
            echo '   DUBLICATE IDS: '.$keywordsIds." -replaced \r\n<br>";

            // SELECT USERS ID
            $users=$this->cum->keywords_users($keywordsIds);
            echo '   USERS: '.json_encode($users)."\r\n";
            $this->_monolog->addDebug(__FUNCTION__.'->$userArr',$users);

            // UPDATE KEYWORDS
            if(count($users)==1 || (count($users)>1 && (empty($provider_id) || $provider_id == 0))){
                $this->db_master->where_in('keyword_id', explode(',', $keywordsIds));
                $this->db_master->update('lib_clip_keywords',array('keyword_id'=>$id));

                $this->db_master->where_in('keywordId', explode(',', $keywordsIds));
                $this->db_master->update('lib_cliplog_logging_keywords',array('keywordId'=>$id));
                $this->_replace_dublicat_metadata_templates($id,$keywordsIds);
                echo "x+x+x+x+x+x+ UPDATE KEYWORDS IN \"".$section."\"+x+x+x+x+x+x\r\n";
            }
            if(count($users)==1 && (empty($provider_id) || $provider_id == 0)){
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_keywords',array('provider_id'=>$users[0]['client_id']));
                echo "x+x+x+x+x+x+ REPLACE PROVIDER IN \"".$section."\"+x+x+x+x+x+x\r\n";
            }
        }else{echo "===DUBLICATE NOT HAVE IN \"".$section."\"===> \r\n";}
    }
    private function _replace_dublicat_metadata_templates($id,$ids){
        $ids=explode(',', $ids);
        $where = ' WHERE ';
        $pattern='/';
        foreach($ids as $v){
            $where.=" `json` LIKE '%".$v."%' OR";
            $pattern.=$v.'|';
        }
        $where=substr($where, 0, -2);
        $pattern=substr($pattern, 0, -1);
        $pattern.='/i';
        $query="SELECT * FROM `lib_cliplog_metadata_templates` ".$where;

        $this->_monolog->addDebug(__FUNCTION__.'->$metadata_query',[$query]);
        $dublicats=$this->db->query($query)->result_array();
        $this->_monolog->addDebug(__FUNCTION__.'->$metadata_dublicats',$dublicats);
        if(count($dublicats)>0){
            foreach($dublicats as $item){
                $this->_monolog->addDebug(__FUNCTION__.'->$json',[$item['json']]);
                $item['json']=preg_replace($pattern,$id,$item['json']);
                $this->_monolog->addDebug(__FUNCTION__.'->$jsonNEW',[$item['json']]);
                $this->db_master->where('id', $item['id']);
                $this->db_master->update('lib_cliplog_metadata_templates',array('json'=>$item['json']));
            }
            echo "<br>   METADATA: Updated \r\n<br>";
        }else{echo "<br>   METADATA: NO ANY UPDATES \r\n<br>";}
    }

    /**
     * Connect keywords with user and clips
     */
    function connect_old_keywords($offset,$limit=1000){
        $rows=$this->cum->get_old_keywords($offset,$limit);
        if(count($rows['data'])>0 && $rows['data'] !=false){
            foreach($rows['data'] as $k=>$keyword){
                $users=$this->cum->keywords_users($keyword['id']);
                echo $k."\r\nSTART ".$keyword['id']." \r\n";
                if(count($users)>0){
                    $this->cum->replace_keyword($keyword,$users);
                }else{echo "NO USERS------------------- \r\n";}
            }
        }

        echo $rows['count']."-Updated !\r\n";
    }
    // -------------- GENERAL Private functions --------------------------------------------

    private function _cycle($func,$start=0,$end=999,$sleep=1,$limit=1000){
        for($i=$start;$i<$end;$i++){
            $offset=$i*$limit;
            $this->$func($offset,$limit);
            sleep($sleep);
        }
    }
    public function cycle($func,$start=0,$end=999,$sleep=1,$limit=1000){$this->_cycle($func,$start,$end,$sleep,$limit);}
    private function _select($offset,$limit,$query){
        $count=$offset+$limit;
        $offset = (empty($offset))?0:$offset;
        $limit = (empty($limit))?'':' limit '.$offset.','.$limit;
        $rows=$this->db->query($query.$limit)->result_array();
        $count.='-'.count($rows);
        $data=(count($rows)>0)?$rows:false;
        return array('count'=>$count,'data'=>$data);
    }
}