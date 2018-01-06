<?php

class Download_model extends CI_Model {

    private $ftp = array();

    function Download_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->store = $store;
    }

#-------------------------------------------------------------------------------

    function get_downloads_list($client_id, $lang = 'en') {
        $now = date('Y-m-d H:i:s');
        /*$list = $this->db->query(
     'SELECT loi.id id, loi.item_type, li.code item_code,
       f.code format_code, f.filetype, f.title df,
       lo.id order_id, lo.ctime, DATEDIFF("' . $now . '", lo.ctime) age
     FROM lib_orders_items loi
     INNER JOIN lib_orders lo ON lo.id = loi.order_id
     INNER JOIN lib_clips li ON li.id = loi.item_id
     INNER JOIN lib_formats f ON f.id = loi.df_id
     WHERE lo.client_id = ' . $client_id . ' AND lo.status = 3 AND loi.item_type = 2
     ORDER BY order_id DESC')->result_array();*/

        $list = $this->db->query(
            'SELECT loi.id id, loi.item_type, li.id clip_id, lic.title, cr.resource, lf.start_time, lf.end_time,
        lo.id order_id, lo.ctime, DATEDIFF("' . $now . '", lo.ctime) age
      FROM lib_orders_items loi
      INNER JOIN lib_orders lo ON lo.id = loi.order_id
      INNER JOIN lib_clips li ON li.id = loi.item_id
      INNER JOIN lib_clips_res cr ON cr.clip_id = loi.item_id AND cr.type = 2
      INNER JOIN lib_fragments lf ON loi.fragment_id = lf.id AND lf.generated = 1
      WHERE lo.client_id = ' . $client_id . ' AND lo.status = 3 AND loi.item_type = 2
      ORDER BY order_id DESC')->result_array();

        if (count($list)) {
            $download_active_days = $this->config->item('download_active_days');
            $client_folder = $this->get_client_folder($client_id);
            foreach ($list as &$row) {
                $row['active'] = $row['age'] < $download_active_days;
                if ($row['active']) {
                    if ($row['start_time'] == 0.00 && $row['end_time'] == 0.00) {
                        $basename = $row['clip_id'] . '.' . $row['resource'];
                    }
                    else{
                        $basename = $row['clip_id'] . '-' . $row['start_time'] . '-' . $row['end_time'] . '.' . $row['resource'];
                    }
                    $download_link = $client_folder . '/' . $basename;
                    if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $download_link)) {
                        $clipfile = $_SERVER['DOCUMENT_ROOT'] . '/data/upload/resources/fragments/' . $basename;
                        if (is_file($clipfile)) {
                            symlink($clipfile, $_SERVER['DOCUMENT_ROOT'] . '/' . $download_link);
                        }
                    }
                    $row['link'] = $this->config->item('base_download_url') . $download_link;

                    if($row['start_time'] == 0.00){
                        $row['start_time'] = $this->lang->line('from_start');
                    }
                    if($row['end_time'] == 0.00){
                        $row['end_time'] = $this->lang->line('to_finish');
                    }
                }
            }
        }

        return $list;
    }

//    function get_downloads_by_client_login($login, $provider_id, $lang = 'en') {
//        $now = date('Y-m-d H:i:s');
//        $list = $this->db->query(
//            'SELECT loi.id id, loi.item_type, loi.df_id, li.id clip_id, lic.title, cr.resource,
//        lo.id order_id, lo.ctime, DATEDIFF("' . $now . '", lo.ctime) age, lu.id client_id,
//        do.conversion, do.container
//      FROM lib_orders_items loi
//      INNER JOIN lib_orders lo ON lo.id = loi.order_id
//      INNER JOIN lib_clips li ON li.id = loi.item_id
//      LEFT JOIN lib_clips_content lic ON li.id = lic.clip_id AND lic.lang = \'' . $lang . '\'
//      INNER JOIN lib_clips_res cr ON cr.clip_id = loi.item_id AND cr.type = 2
//      INNER JOIN lib_users lu ON lo.client_id = lu.id AND lu.login = ? AND lu.provider_id = ?
//      INNER JOIN lib_delivery_options do ON loi.df_id = do.id
//      WHERE lo.status = 3 AND loi.item_type = 2
//      ORDER BY order_id DESC', array($login, $provider_id))->result_array();
//
//
//        if (count($list)) {
//            $download_active_days = $this->config->item('download_active_days');
//            $native_files = array();
//            foreach ($list as $key => &$row) {
//                $client_folder = $this->get_client_folder($row['client_id']);
//                $row['active'] = $row['age'] < $download_active_days;
//                if ($row['active']) {
//                    if (/*$row['conversion'] == 0*/false) {
//                        $basename = $row['clip_id'] . '.' . $row['resource'];
//                    }
//                    else{
//                        if(!$row['container']){
//                            $row['container'] = $row['resource'];
//                        }
//                        $basename = $row['clip_id'] . '-' . $row['df_id'] . '.' . $row['container'];
//                    }
//                    $download_link = $client_folder . '/' . $basename;
//                    if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $download_link)) {
//                        $row['link'] = $this->config->item('base_download_url') . $download_link;
//                    }
//
//                    if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $download_link)) {
//                        $clipfile = $_SERVER['DOCUMENT_ROOT'] . '/data/upload/resources/converted/' . $basename;
//                        if (is_file($clipfile)) {
//                            symlink($clipfile, $_SERVER['DOCUMENT_ROOT'] . '/' . $download_link);
//                            $row['link'] = $this->config->item('base_download_url') . $download_link;
//                        }
//                    }
//
//                    if(!isset($row['link'])){
//                        unset($list[$key]);
//                    }
//
//                    $basename = $row['clip_id'] . '.' . $row['resource'];
//                    $download_link = $client_folder . '/' . $basename;
//                    if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $download_link)) {
//                        $native_file = $row;
//                        $native_file['link'] = $this->config->item('base_download_url') . $download_link;
//                        $native_file['title'] = $native_file['title'] . ' - Native format';
//                        $native_files[] = $native_file;
//                    }
//
//                }
//            }
//            $list = array_merge($list, $native_files);
//        }
//
//        return $list;
//    }


#-------------------------------------------------------------------------------


    function confirm_download($user, $file){

        $file_data = explode("-",substr($file, 0, strpos($file, ".")));
        $order_id = $file_data[0];
        $clip_code = $file_data[1];
        $status = (int) 1;

        $updateStatus = $this->db_master->query("UPDATE lib_orders_items loi
                                            JOIN lib_clips lc
                                                ON lc.id = loi.item_id
                                            JOIN  lib_orders lo
                                                ON lo.id = '".$order_id."'
                                            JOIN lib_users lu
                                                ON lu.login = '".$user."'
                                                SET loi.downloaded = '1'
                                            WHERE loi.order_id = '".$order_id."' AND lc.code = '".$clip_code."' AND lu.id = lo.client_id");


/*
        $updateStatus = $this->db_master->query("UPDATE lib_orders_items loi
                                            JOIN lib_clips lc
                                                ON lc.id = loi.item_id
                                            JOIN  lib_orders lo
                                                ON lo.id = '30000128'
                                            JOIN lib_users lu
                                                ON lu.login = 'nata123'
                                                SET loi.downloaded = 1
                                            WHERE loi.order_id = '30000128' AND lc.code = 'BMU01_537' AND lu.id = lo.client_id");
*/

    }


#-------------------------------------------------------------------------------


    function get_downloads_by_client($user, $provider_id = 0, $order_id = 0, $lang = 'en') {
        $now = date('Y-m-d H:i:s');
        $list = $this->db->query(
            'SELECT loi.id id, loi.df_id, li.id clip_id, lic.title, cr.resource, cr.location,
        lo.id order_id, lo.ctime, DATEDIFF("' . $now . '", lo.ctime) age, lu.id client_id,
        do.conversion, do.container
      FROM lib_orders_items loi
      INNER JOIN lib_orders lo ON lo.id = loi.order_id
      INNER JOIN lib_clips li ON li.id = loi.item_id
      INNER JOIN lib_clips_res cr ON cr.clip_id = loi.item_id AND cr.type = 2
      INNER JOIN lib_users lu ON lo.client_id = lu.id AND ' . (is_numeric($user) ? 'lu.id = ?' : 'lu.login = ?') .
      ' ' . ($provider_id ? 'AND lu.provider_id = ?' : '') . '
      INNER JOIN lib_delivery_options do ON loi.df_id = do.id
      WHERE lo.status = 3 AND loi.item_type = 2' . ($order_id ? ' AND lo.id = ' . $order_id : '') . '
      ORDER BY order_id DESC', array($user, $provider_id))->result_array();


        if (count($list)) {
            $download_active_days = $this->config->item('download_active_days');
            $native_files = array();

            foreach ($list as $key => &$row) {

                $client_folder = $this->getFilePath(md5($row['client_id'] . $this->config->item('user_folder_salt')), '', 'user_delivery');
                $client_order_folder = $client_folder . '/order' . $row['order_id'];
                $row['active'] = $row['age'] < $download_active_days;
                if ($row['active']) {
                    if (/*$row['conversion'] == 0*/false) {
                        $basename = $row['clip_id'] . '.' . $row['resource'];
                    }
                    else{
                        if(!$row['container']){
                            $row['container'] = $row['resource'];
                        }
                        $basename = $row['order_id'] . '-' . $row['clip_id'] . '-' . $row['df_id'] . '.' . $row['container'];
                    }
                    $download_link = $client_order_folder . '/' . $basename;
                    if ($this->isFileExists($download_link)) {
                        $path_info = parse_url($download_link);
                        $row['link'] = 'http://' . $path_info['host'] . str_replace($this->store['user_delivery']['web_root'], '', $path_info['path']);
                    }

                    if(!isset($row['link'])){
                        unset($list[$key]);
                    }


                    $isOriginalR3D = strtolower($row['resource']) == 'r3d';
                    if($isOriginalR3D){
                        $originalDir = basename(dirname($row['location']));
                        $originalFile = basename($row['location']);
                        $download_link = $client_order_folder . '/' . $originalDir . '/' . $originalFile;
                        if($this->isFileExists($download_link)){
                            $files = $this->scanDir($client_order_folder . '/' . $originalDir);
                            if($files){
                                foreach($files as $file){
                                    $download_link = $client_order_folder . '/' . $originalDir . '/' . basename($file);
                                    $path_info = parse_url($download_link);
                                    $native_file = $row;
                                    $native_file['link'] = 'http://' . $path_info['host'] . str_replace($this->store['user_delivery']['web_root'], '', $path_info['path']);
                                    $native_file['title'] = $native_file['title'] . ' - Native format';
                                    $native_files[] = $native_file;
                                }
                            }
                        }
                    }
                    else{
                        $originalFile = basename($row['location']);
                        $download_link = $client_order_folder . '/' . $originalFile;
                        if($this->isFileExists($download_link)){
                            $path_info = parse_url($download_link);
                            $native_file = $row;
                            $native_file['link'] = 'http://' . $path_info['host'] . str_replace($this->store['user_delivery']['web_root'], '', $path_info['path']);
                            $native_file['title'] = $native_file['title'] . ' - Native format';
                            $native_files[] = $native_file;
                        }
                    }


//                    // Add master file
//                    $basename = $row['clip_id'] . '.' . $row['resource'];
//                    $download_link = $client_order_folder . '/' . $basename;
//                    if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $download_link)) {
//                        $native_file = $row;
//                        $native_file['link'] = $this->config->item('base_download_url') . $download_link;
//                        $native_file['title'] = $native_file['title'] . ' - Native format';
//                        $native_files[] = $native_file;
//                    }

                }
            }
            $list = array_merge($list, $native_files);
        }

        foreach($this->ftp as $resource){
            if(is_resource($resource))
                ftp_close($resource);
        }

        return $list;
    }

#-------------------------------------------------------------------------------

    function update_item($id) {
        $data['downloaded'] = 1;

        $this->db_master->where('id', $id);
        $this->db_master->update('lib_orders_items', $data);
    }

#-------------------------------------------------------------------------------

    function get_client_folder($client_id) {
        $folder = 'data/upload/users/' . md5($client_id
            . $this->config->item('user_folder_salt'));

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/' . $folder)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/' . $folder);
        }

        return $folder;
    }

    private function getFilePath($resourceName, $resourceExtension, $resourceType){
        switch ($this->store[$resourceType]['scheme']) {
            case 'ftp':
                $path = 'ftp://' . $this->store[$resourceType]['username'] . ':'
                    . $this->store[$resourceType]['password'] . '@'
                    . $this->store[$resourceType]['host'] . ':' . $this->store[$resourceType]['port']
                    . rtrim($this->store[$resourceType]['path'], '/') . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
                break;
            case 's3':
                $path = 's3://' . $this->store[$resourceType]['bucket'] . rtrim($this->store[$resourceType]['path'], '/')
                    . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
                break;
            default:
                $path = rtrim($this->store[$resourceType]['path'], '/') . '/' . $resourceName . ($resourceExtension ? '.' . $resourceExtension : '');
        }
        return $path;
    }

    private function isFileExists($filePath){
        $pathInfo = parse_url($filePath);
        if(isset($pathInfo['scheme'])){
            $fileExists = false;
            switch ($pathInfo['scheme']) {
                case 'ftp':
                    if(!isset($this->ftp[md5($pathInfo['host'] . $pathInfo['port'])])){
                        $this->ftp[md5($pathInfo['host'] . $pathInfo['port'])] = (empty($pathInfo['port']) ?
                            ftp_connect($pathInfo['host']) :
                            ftp_connect($pathInfo['host'], $pathInfo['port']));
                    }
                    $ftp = $this->ftp[md5($pathInfo['host'] . $pathInfo['port'])];
//                    $ftp = empty($pathInfo['port']) ?
//                        ftp_connect($pathInfo['host']) :
//                        ftp_connect($pathInfo['host'], $pathInfo['port']);
                    if($ftp && $pathInfo['user'] && $pathInfo['pass']) {
                        if(!isset($this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])])){
                            $this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])] = ftp_login($ftp, $pathInfo['user'], $pathInfo['pass']);
                        }
                        $login = $this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])];
                        //$login = ftp_login($ftp, $pathInfo['user'], $pathInfo['pass']);
                        if($login) {
                            $res = ftp_size($ftp, $pathInfo['path']);
                            if ($res != -1)
                                $fileExists = true;
                        }
                        //ftp_close($ftp);
                    }
//                    if(file_exists($filePath)){
//                        $fileExists = true;
//                    }
                    break;
                case 's3':
                    if(!$this->s3Client){
                        $this->s3Client = S3Client::factory(array(
                            'key'    => $this->store['s3']['key'],
                            'secret' => $this->store['s3']['secret']
                        ));
                    }
                    $this->s3Client->registerStreamWrapper();
                    if (file_exists($filePath)) {
                        $fileExists = true;
                    }
                    break;
            }
            return $fileExists;
        }
        else
            return is_file($filePath);
    }

    private function scanDir($path){
        $files = array();
        $pathInfo = parse_url($path);
        if(isset($pathInfo['scheme'])){
            switch ($pathInfo['scheme']) {
                case 'ftp':
                    if(!isset($this->ftp[md5($pathInfo['host'] . $pathInfo['port'])])){
                        $this->ftp[md5($pathInfo['host'] . $pathInfo['port'])] = (empty($pathInfo['port']) ?
                            ftp_connect($pathInfo['host']) :
                            ftp_connect($pathInfo['host'], $pathInfo['port']));
                    }
                    $ftp = $this->ftp[md5($pathInfo['host'] . $pathInfo['port'])];
                    if($ftp && $pathInfo['user'] && $pathInfo['pass']) {
                        if(!isset($this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])])){
                            $this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])] = ftp_login($ftp, $pathInfo['user'], $pathInfo['pass']);
                        }
                        $login = $this->ftp[md5($pathInfo['host'] . $pathInfo['port'] . $pathInfo['user'] . $pathInfo['pass'])];
                        if($login) {
                            ftp_pasv($ftp, true);
                            $files = ftp_nlist($ftp, $pathInfo['path']);
                        }
                        //ftp_close($ftp);
                    }
                    break;
            }
        }
        else
            $files = scandir($path);

        return $files;
    }

    function get_not_uploaded_downloads_by_client($user) {
        $downloads = array();
        $this->db->select('o.id, oi.upload_status');
        $this->db->from('lib_orders o');
        $this->db->join('lib_users u', 'o.client_id = u.id AND u.login = \'' . $user  . '\'');
        $this->db->join('lib_orders_items oi', 'o.id = oi.order_id AND oi.uploaded = 0');
        //$this->db->group_by('oi.order_id');
        $query = $this->db->get();
        $result = $query->result_array();
        if ($result) {
            foreach ($result as $item) {
                if ($item['upload_status'] == 'Lab') {
                    $this->db->select('id');
                    $this->db->where('order_id', $item['id']);
                    $this->db->where('uploaded', 1);
                    $query = $this->db->get('lib_orders_items');
                    $result2 = $query->result_array();
                    if (!$result2) {
                        $downloads[] = $item['id'];
                    }
                }
                else {
                    $downloads[] = $item['id'];
                }
            }
        }
        return $downloads;

    }

}