<?php
/**
 * Created by PhpStorm.
 * Date: 01.04.15
 */
require_once(APPPATH . '/libraries/SorlSearchAdapter.php');
require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;


/**
 * Class Clips_update_model
 */
class Clips_update_model extends CI_Model {
    var $img_types = array('jpg', 'jpeg', 'png', 'gif');
    var $motion_types = array('mov', 'mp4', 'flv');
    var $codecs = array('YUV' => 'Uncompressed');

    function Clips_update_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_online_require_clips(){
        return $this->db->query("
            SELECT *
            FROM  lib_clips
            WHERE  license !=0
            AND  active =0
            AND  brand !=0
            AND  price_level !=0
            AND  digital_file_frame_rate !=  ''
            AND  digital_file_format !=  ''
            AND  digital_file_frame_size !=  ''
        ")->result_array();
    }
    function back_dublicate_keywords($offset,$limit){
        $query="SELECT  `id` ,  `keyword` ,  `section` ,  `old` , provider_id, collection, basic, hidden, COUNT( * )
                FROM `lib_keywords` WHERE old=0
                GROUP BY  `keyword`
                HAVING COUNT( * ) >1
                ORDER BY  `lib_keywords`.`hidden` DESC ";
        return $this->_select($offset,$limit,$query);
    }

    /**
     * @param string $keywordsIds
     * @return mixed
     */
    function keywords_users($keywordsIds){
        return $this->db->query('SELECT DISTINCT c.client_id as id FROM `lib_clips` AS c INNER JOIN `lib_clip_keywords` AS ck ON c.id=ck.clip_id
                WHERE ck.`keyword_id` IN ('.$keywordsIds.')')->result_array();
    }
    function user_clips_keyword($userId,$keywordId){
        return $this->db->query('SELECT c.id FROM `lib_clips` AS c INNER JOIN `lib_clip_keywords` AS ck ON c.id=ck.clip_id
                WHERE ck.`keyword_id` = '.$keywordId.' AND c.client_id='.$userId)->result_array();
    }

    function get_old_keywords($offset,$limit){
        //$query="SELECT * FROM lib_keywords_old ";
        $query="SELECT * FROM lib_keywords WHERE old !=0 ";
        return $this->_select($offset,$limit,$query);
    }

    /**
     * @param array $keyword
     * @param array $usersId
     */
    function replace_keyword($keyword,$usersId){
        foreach($usersId as $k=>$user){
            if($k==0){
                $this->db_master->where('id', $keyword['id']);
                $this->db_master->update('lib_keywords',array('provider_id'=>$user['id'],'old'=>0,'hidden'=>1));
                echo $k." user updated \r\n";
            }else{
                $data = array(
                    'keyword' => $keyword['keyword'],
                    'section' => $keyword['section'],
                    'collection' => $keyword['collection'],
                    'basic' => $keyword['basic'],
                    'provider_id' => $user['id'],
                    'hidden'=>1
                );
                $this->db_master->insert('lib_keywords', $data);
                $id=$this->db_master->insert_id();
                echo $id." insert keyword \r\n";
                //replace keywords sets
                $keywordsSets=$this->db->query("SELECT * FROM lib_cliplog_metadata_templates WHERE json LIKE '%".$keyword['id']."%'")->result_array();
                if(count($keywordsSets)>0){
                    foreach($keywordsSets as $keywordsSet){
                        $keywordsSet['json']=preg_replace('/'.$keyword['id'].'/',$id,$keywordsSet['json']);
                        $this->db_master->where('id', $keywordsSet['id']);
                        $this->db_master->update('lib_cliplog_metadata_templates',array('json'=>$keywordsSet['json']));
                        echo $k." keywordsSet:".$keywordsSet['id']." updated \r\n";
                    }
                }
                //replace clip keywords
                $userClips=$this->user_clips_keyword($user['id'],$keyword['id']);
                $userClipsIds=array();
                foreach($userClips as $userClip) $userClipsIds[]=$userClip['id'];
                $this->db_master->where('keyword_id', $keyword['id']);
                $this->db_master->where_in('clip_id', $userClipsIds);
                $this->db_master->update('lib_clip_keywords',array('keyword_id'=>$id));
                echo $k." userClips:".json_encode($userClipsIds)." updated \r\n";
            }
            echo "-----------------------------------------\r\n";
        }
    }
    function replace_keyword_new($keywordOld){
        $keyword=$this->db->query('SELECT * FROM `lib_keywords` WHERE keyword = "'.$keywordOld['keyword'].'"')->result_array();
        if(count($keyword)<1){
            $data = array(
                'keyword' => $keywordOld['keyword'],
                'section' => $keywordOld['section'],
                'collection' => $keywordOld['collection'],
                'basic' => ($keywordOld['collection']=='Nature Footage'),
                'hidden' => $keywordOld['hidden']
            );
            $this->db_master->insert('lib_keywords', $data);
            $id=$this->db_master->insert_id();
        }
        //create user keywords
        $users=$this->keywords_users($keywordOld['id']);
        if(count($users)>0){
            foreach($users as $user){
                $userKeywords=$this->db->query('SELECT * FROM `lib_user_keywords` WHERE keyword_id = '.$keyword['id'].' AND user_id = '.$user['id'].' LIMIT 1')->result_array();
                if(count($userKeywords)<1) {
                    $data = array(
                        'keyword_id' => $keyword['id'] ,
                        'user_id' => $user['client_id']
                    );
                    $this->db_master->insert('lib_user_keywords', $data);
                }
            }
        }
        // replace keywords sets
        $keywordSets=$this->db->query('SELECT * FROM `lib_cliplog_metadata_templates` WHERE json LIKE "%'.$keywordOld['id'].'%"')->result_array();
        if(count($keywordSets)>0){
            foreach($keywordSets as $keywordSet){
                $keywordSet['json']=preg_replace('/'.$keywordOld['id'].'/',$keyword['id'],$keywordSet['json']);
                $this->db_master->where('id', $keywordSet['id']);
                $this->db_master->update('lib_cliplog_metadata_templates',array('json'=>$keywordSet['json']));
            }
        }
        //replace clip keywords
        $this->db_master->where('keyword_id', $keywordOld['id']);
        $this->db_master->update('lib_clip_keywords',array('keyword_id'=>$keyword['id']));
    }

    private function _select($offset,$limit,$query){
        $count=$offset+$limit;
        $offset = (empty($offset))?0:$offset;
        $limit = (empty($limit))?'':' LIMIT '.$offset.','.$limit;
        $rows=$this->db->query($query.$limit)->result_array();
        $count.='-'.count($rows);
        $data=(count($rows)>0)?$rows:false;
        return array('count'=>$count,'data'=>$data);
    }
}
