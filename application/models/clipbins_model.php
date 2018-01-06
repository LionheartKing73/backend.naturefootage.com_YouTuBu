<?php
/**
 * @property Users_model $users_model
 */
class Clipbins_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_clipbins_count($filter = array()) {
        if($filter){
            foreach($filter as $filter_settings){

                if(is_array($filter_settings)){
                    if($filter_settings['field'] == 'c.code, c.code as title, c.description, c.keywords'){
                        $this->db->join('lib_lb_items lbi', 'lb.id = lbi.lb_id');
                        $this->db->join('lib_clips c', 'lbi.item_id = c.id');
                    }

                    if($filter_settings['from'])
                        $this->db->where($filter_settings['field'] . ' >=', is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    elseif($filter_settings['to'])
                        $this->db->where($filter_settings['field'] . ' <=', is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    elseif($filter_settings['fulltext']){
                        $this->db->where('MATCH (' . $filter_settings['field'] . ') AGAINST (' . (is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']) . ' IN BOOLEAN MODE)', NULL, FALSE);
                    }
                    elseif(is_array($filter_settings['value']) && count($filter_settings['value']) > 1)
                        $this->db->where_in($filter_settings['field'], $filter_settings['value']);
                    else{
                        $this->db->where($filter_settings['field'], is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    }
                }
                else
                    $this->db->where($filter, $filter_settings);

            }
            $this->db->from('lib_lb lb');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_lb');
    }

    function get_clipbins_list($filter = array(), $limit = array(), $order_by = ''){

        $this->db->select('lb.*, u.fname, u.lname');
        $this->db->select("DATE_FORMAT(lb.ctime, '%d-%m-%Y %H:%i:%s') date", FALSE );
        $this->db->from('lib_lb lb');
        $this->db->join('lib_users u', 'lb.client_id = u.id', 'left');

        //Filtering
        if($filter){
            foreach($filter as $filter_settings){

                if(is_array($filter_settings)){
                    if($filter_settings['field'] == 'c.code, c.code as title, c.description, c.keywords'){
                        $this->db->join('lib_lb_items lbi', 'lb.id = lbi.lb_id');
                        $this->db->join('lib_clips c', 'lbi.item_id = c.id');
                    }

                    if($filter_settings['from'])
                        $this->db->where($filter_settings['field'] . ' >=', is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    elseif($filter_settings['to'])
                        $this->db->where($filter_settings['field'] . ' <=', is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    elseif($filter_settings['fulltext']){
                        $this->db->where('MATCH (' . $filter_settings['field'] . ') AGAINST (' . (is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']) . ' IN BOOLEAN MODE)', NULL, FALSE);
                    }
                    elseif(is_array($filter_settings['value']) && count($filter_settings['value']) > 1)
                        $this->db->where_in($filter_settings['field'], $filter_settings['value']);
                    else{
                        $this->db->where($filter_settings['field'], is_array($filter_settings['value']) ? $filter_settings['value'][0] : $filter_settings['value']);
                    }
                }
                else
                    $this->db->where($filter, $filter_settings);

            }
        }

        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get();

        $res = $query->result_array();
//        echo $this->db->last_query();
//        exit();
        return $res;
    }

    function save_clipbin($id){
        $data = $this->input->post();
        if($this->session->userdata('client_uid')) {
            $data['provider_id'] = $this->session->userdata('client_uid');
        }
        if($data['ctime'])
            $data['ctime'] = date('Y-m-d H:i:s', strtotime($data['ctime']));
        else
            $data['ctime'] = date('Y-m-d H:i:s');
        unset($data['save'], $data['id'], $data['client_name']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_lb', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_lb', $data);
            return $this->db_master->insert_id();
        }
    }

    function create_clipbin_by_name($login,$title){
        $data['ctime'] = date('Y-m-d H:i:s');
        $this->db->select('id');
        $this->db->from('lib_users');
        $this->db->where('login', $login);
        $query = $this->db->get();
        $res = $query->result_array();
        if(isset($res[0])){
            $data['client_id']=$res[0]['id'];
            $data['title']=$title;
            $this->db_master->insert('lib_lb', $data);
            return $this->db_master->insert_id();
        }
        return false;
    }

    function get_clipbin($id){
        $this->db->select('*');
        $this->db->select("DATE_FORMAT(ctime, '%d-%m-%Y') date", FALSE);
        $this->db->from('lib_lb');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $res = $query->result_array();
        return $res[0];
    }
    function get_clipbin_items_ids($id){
        $this->db->select('item_id');
        $this->db->from('lib_lb_items');
        $this->db->where('lb_id', $id);
        $query = $this->db->get();
        $res = $query->result_array();
        $result=array();
        foreach($res as $v)$result=array_merge($result,array($v['item_id']));
        return $result;
    }

    function save_previews_archive($user_login,$ids,$bin_id=0){
        // LIMIT for unauthorized users TODAY
        $limitToday=10;
        $this->load->model('users_model');
        $client_id=$this->users_model->get_user_by_login($user_login);
        if(!$client_id) return false; // Not user
        $date=date('Y-m-d');
        $datetime=date('Y-m-d H:i:s');
        $data=array('clip_ids'=>json_encode($ids),'client_id'=>$client_id,'c_date'=>$datetime,'u_date'=>$datetime);
        if(empty($bin_id)){
            $cart=$this->db->query('SELECT count(*) AS cnt FROM lib_preview_archives WHERE name LIKE "Cart_'.$date.'%" AND client_id='.$client_id)->result_array();
            $cart=$cart[0];
            if($cart['cnt']>=$limitToday) return false; // This user have limit Cart archived today
            $count=(!empty($cart['cnt']))?'_'.$cart['cnt']:'';
            $data['name']='Cart_'.$date.$count;
            $data['type']='cart';
        }else{
            $bin=$this->db->get_where('lib_lb',array('id'=>$bin_id))->result_array();
            $title=$clipbin=preg_replace('/[^a-zA-Z0-9]/i','',$bin[0]['title']);
            $clipbin=$this->db->query('SELECT count(*) AS cnt FROM lib_preview_archives WHERE name LIKE "'.$title.'_'.$date.'%" AND client_id='.$client_id)->result_array();
            $clipbin=$clipbin[0];
            if($clipbin['cnt']>=$limitToday) return false; // This user have limit Clipbin archived today
            $count=(!empty($clipbin['cnt']))?'_'.$clipbin['cnt']:'';
            $data['name']=$title.'_'.$date.$count;
            $data['type']='clipbin';
        }
        $this->db_master->insert('lib_preview_archives', $data);
        return true;
    }
    function get_previews_archive($user_login,$id=null){
        $this->load->model('users_model');
        $user_id=$this->users_model->get_id_and_login($user_login,'id');
        $andWhere=(empty($id))?'':' AND id='.$id;
        $archives=$this->db->query('SELECT * FROM lib_preview_archives WHERE client_id='.$user_id.$andWhere)->result_array();
        return (empty($id))?$archives:$archives[0];
    }

    /**
     * @param $login - login user have clipbinName
     * @param $clipbinName - name clipbin
     * @return mixed
     */
    function get_clipbin_by_name($login,$clipbinName){
        $this->db->select('lb.*');
        $this->db->from('lib_lb lb');
        $this->db->join('lib_users u', 'u.id = lb.client_id','right');
        $this->db->where('u.login', $login);
        $this->db->where('lb.title', $clipbinName);
        $query = $this->db->get();
        $res = $query->result_array();
        return $res[0];
    }

    function delete_clipbins($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $check = $this->get_clipbin($id);
                //if ($check['provider_id'] === $this->session->userdata('client_uid')
                    //|| $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']) {
                    $this->db_master->delete('lib_lb', array('id' => $id));
                    $this->db_master->delete('lib_lb_items', array('lb_id' => $id));
                //}
            }
        }
    }

    function itemsToFilter($str){
        $arr=explode(',',$str);
        $ret='';
        foreach($arr as $v){
            $ret.='"'.$v.'",';
        }
        return substr($ret,0,-1);
    }

    function add_items ( $id, $items_ids ) {
        if ( $items_ids && is_array( $items_ids ) ) {
            foreach ( $items_ids as $item_id ) {
                $this->db_master->delete( 'lib_lb_items', array ( 'lb_id' => $id, 'item_id' => $item_id ) );
                $this->db_master->insert( 'lib_lb_items', array ( 'lb_id' => $id, 'type' => 2, 'item_id' => $item_id ) );
            }
        }
    }
}
