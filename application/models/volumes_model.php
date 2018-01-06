<?php

class Volumes_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function get_volumes_count($filter = array()) {
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
            $this->db->from('lib_volumes');
            return $this->db->count_all_results();
        }
        else
            return $this->db->count_all('lib_volumes');
    }

    function get_volumes_list($filter = array(), $limit = array(), $order_by = ''){
        if($filter){
            foreach($filter as $param => $value){
                $this->db->where($param, $value);
            }
        }
        if($limit)
            $this->db->limit($limit['perpage'], $limit['start']);
        if($order_by)
            $this->db->order_by($order_by);

        $query = $this->db->get('lib_volumes');
        $res = $query->result_array();
        return $res;
    }

    function save_volume($id){
        $data = $this->input->post();
        unset($data['save'], $data['id']);
        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_volumes', $data);
            return $id;
        }
        else {
            $this->db_master->insert('lib_volumes', $data);
            return $this->db_master->insert_id();
        }
    }

    function get_volume($id){
        $this->db->where('id', $id);
        $query = $this->db->get('lib_volumes');
        $res = $query->result_array();
        return $res[0];
    }

    function get_volume_by_name($name){
        $this->db->where('name', $name);
        $query = $this->db->get('lib_volumes');
        $res = $query->result_array();
        return $res[0];
    }

    function delete_volumes($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->delete('lib_volumes', array('id' => $id));
            }
        }
    }

    function change_status($ids){
        if (count($ids)) {
            foreach ($ids as $id) {
                $this->db_master->query('UPDATE lib_volumes set is_full = !is_full where id=' . $id);
            }
        }
    }

    function humanize_size($bytes) {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes, $base) , count($si_prefix) - 1);
        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    function get_dir_size($path) {
        exec('df -k ' . $path, $output);
        preg_match("/\\s(\\d+)\\s(\\d+)\\s(\\d+)\\s/", implode(' ', $output), $matches);
        array_shift($matches);
        $matches[0] = $matches[0] * 1024;
        $matches[1] = $matches[1] * 1024;
        $matches[2] = $matches[2] * 1024;
        return $matches;
    }

    function sync_volumes() {
        //$this->db_master->truncate('lib_volumes');
        $this->db_master->update('lib_volumes', array('active' => 0));
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $dir = $store['original']['path'];
        $nodes = scandir($dir);
        $exports_file = __DIR__ . '/../../temp/exports';
        /*$get_exports_file_command = 'scp ' . $store['media_server']['username'] . '@' . $store['media_server']['host']
            . ':/etc/exports ' . $exports_file;*/
        $get_exports_file_command = 'cp /etc/exports ' . $exports_file;
        system($get_exports_file_command, $return_var);
        if ((int)$return_var == 0) {

            // Get exported volumes from file
            $exports_arr = file($exports_file);
            $exported_volumes = array();
            if ($exports_arr) {
                foreach ($exports_arr as $line) {
                    if ($line[0] == '#') continue;
                    $exported_volumes[] = pathinfo(explode(' ', $line)[0] ,PATHINFO_BASENAME);
                }
            }


            if ($exported_volumes) {
                foreach ($exported_volumes as $volume) {
                    $volume_path = $dir . '/' . $volume;
                    $data = array();
                    if ($this->is_mounted($volume_path)) {
                        if (is_dir($volume_path)) {
                            $size = $this->get_dir_size($volume_path);
                            $total_bytes = $size[0];
                            $used_bytes = $size[1];
                            $data = array('name' => $volume, 'size' => $total_bytes, 'used' => $used_bytes, 'active' => 1);
                        }
                    }
                    else {
                        $data = array('name' => $volume, 'size' => 0, 'used' => 0, 'active' => 0);
                    }

                    if($data) {
                        $exist = $this->get_volume_by_name($volume);
                        if($exist) {
                            $this->db_master->where('id', $exist['id']);
                            $this->db_master->update('lib_volumes', $data);
                        }
                        else {
                            $this->db_master->insert('lib_volumes', $data);
                        }
                    }
                }
            }

//            foreach($nodes as $node){
//                if ($node == '.' || $node == '..' || $node == 'Library') continue;
//                $node_path = $dir . '/' . $node;
//                //system('timeout 5s df ' . $node_path . ' > /dev/null 2>&1', $return_var);
//                system('(df ' . $node_path . ' > /dev/null 2>&1)  & sleep 2; kill $! 2> /dev/null || :', $return_var);
//                echo $node_path, PHP_EOL;
//                echo $return_var, PHP_EOL;
//                $data = array();
//                if((int)$return_var == 0) {
//                    if (is_dir($node_path)) {
//    //                    $total_bytes = disk_total_space($node_path);
//    //                    $used_bytes = $total_bytes - disk_free_space($node_path);
//                        $size = $this->get_dir_size($node_path);
//                        $total_bytes = $size[0];
//                        $used_bytes = $size[1];
//                        $data = array('name' => $node, 'size' => $total_bytes, 'used' => $used_bytes, 'active' => 1);
//                    }
//                }
//                else {
//                    $data = array('name' => $node, 'size' => 0, 'used' => 0, 'active' => 1);
//                }
//                if($data) {
//                    $exist = $this->get_volume_by_name($node);
//                    if($exist) {
//                        $this->db_master->where('id', $exist['id']);
//                        $this->db_master->update('lib_volumes', $data);
//                    }
//                    else {
//                        $this->db_master->insert('lib_volumes', $data);
//                    }
//                }
//            }
        }
    }

    function is_mounted($volume) {
        system('if [ `df | grep ' . $volume . '| wc -l` = 0 ]; then exit 1; else exit 0; fi', $return_var);
        if (!$return_var) {
            system('df ' . $volume . ' 2>&1 | grep Stale', $return_var2);
            if ((int)$return_var2 == 0) {
                return false;
            }
        }
        return !$return_var;
    }
}