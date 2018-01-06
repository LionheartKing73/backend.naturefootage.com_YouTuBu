<?php
require_once(__DIR__ . '/../../scripts/Notifications.php');

class Uploadstools extends CI_Controller {

    var $ignore_names = array('.cache', 'Library', '.DS_Store');
    var $providers_path;
    var $changesLog = array();
    var $store = array();
    var $uploadsLog;

    function __construct() {
        parent::__construct();
        $this->aspdb = $this->load->database('aspera_console', true);
        $this->db_master = $this->load->database('master', TRUE);
    }

    public function sync_volumes() {
        if(!$this->input->is_cli_request()){
            exit();
        }
        $this->load->model('volumes_model');
        $this->volumes_model->sync_volumes();
    }

    public function submit(){
        if(!$this->input->is_cli_request()){
            exit();
        }

        date_default_timezone_set('America/Los_Angeles');
        @set_time_limit(604800);

        $this->load->model('users_model');
        $providers = $this->users_model->get_providers_list();
        $active = $this->get_active_submits();
        if ($active && count($active) >= 2) {
            echo 'Previous', PHP_EOL;
            exit();
        }

        $pid = posix_getppid();
        $this->load->model('uploads_model');
        foreach ($providers as $provider) {
//            if ($provider['login'] !== 'ghuglin') {
//                continue;
//            }
            $uploads = $this->uploads_model->get_uploads_list($provider['login']);
            if($uploads){
                foreach($uploads as $upload){
                    if($upload['provider'] && $upload['items']){
                        $submit_log = $this->get_new_submit($provider['id']);
                        if ($submit_log) {
                            //if ($upload['provider']['login'] != 'naturefootage') continue;
                            $uncompleted_sessions = $this->get_uncompleted_sessions($upload['provider']['login']);
                            if($uncompleted_sessions) {
                                $this->log_submit_update($submit_log);
                                continue;
                            }
                            foreach($upload['items'] as $upload_item){
                                if(!in_array($upload_item['name'], $this->ignore_names)){
                                    if ($this->uploads_model->is_upload_incomplete($upload_item)) {
                                        continue;
                                    }
                                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' START SUBMIT: ' . $submit_log . ' ' . $upload_item['id'] . ' ' . $upload['provider']['login'], PHP_EOL;
                                    $this->uploads_model->submit_uploads($upload_item['id'], $upload['provider']['login']);
                                    echo $pid . ' ' . date('Y-m-d H:i:s') . ' FINISH SUBMIT: ' . $submit_log . ' ' . $upload_item['id'] . ' ' . $upload['provider']['login'], PHP_EOL;
                                }
                            }
                            $this->log_submit_update($submit_log);
                        }
                    }
                }
            }
        }
    }

    public function newSubmit()
    {
        if(!$this->input->is_cli_request()){
            exit();
        }

        date_default_timezone_set('America/Los_Angeles');
        @set_time_limit(604800);

        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->store = $store;
        $this->providers_path = $store['uploads']['path'];
        $this->uploadsLog = '/Library/Application Support/XDT/Catapult/Logs/catapult.transfer.report.txt';
        //$this->uploadsLog = '/home/ubuntu/catapult.transfer.report.txt';

        $this->load->model('users_model');
        $providers = $this->users_model->get_providers_list();
        //$provider = ['id' => 7031, 'login' => 'dbaron', 'prefix' => 'DA'];
        foreach ($providers as $provider) {
        $path = $this->providers_path . DIRECTORY_SEPARATOR . $provider['login'];
        //$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        //while (true) {

	/* Handle case when directory does not exist, by making it. :Start -JTR 2016-02-25 */
  	if ( ! is_dir($path) ) {
            mkdir($path, 0777, true);
        }
	/* Handle case when directory does not exist, by making it. :End   -JTR 2016-02-25 */	

            $objects = new DirectoryIterator($path);
            foreach ($objects as $object) {
                $this->processObject($object, $provider);
            }
        //}
        }
        $this->load->model('submissions_model');
        $this->submissions_model->delete_empty_submissions();
        sleep(60);
    }

    /**
     * @param \SplFileInfo $object
     * @param $provider
     * @return bool
     */
    protected function processObject($object, $provider)
    {
        if ($object->isDot()) return true;
        $this->load->model('uploads_model');
        if ($object->isFile()) {
            if ($this->isUploaded($object) && ($submission = $this->processUpload($object, $provider))) {
                $submissionsBackup = $this->store['submissions_backup']['path'] . DIRECTORY_SEPARATOR . $submission['code'];
                echo date('Y-m-d H:i:s') . ' BACKUP: ' . $object->getRealPath(), PHP_EOL;
                $this->backupUpload($object, $submissionsBackup, $provider);
                echo date('Y-m-d H:i:s') . ' DELETE: ' . $object->getRealPath(), PHP_EOL;
                unlink($object->getRealPath());
                return true;
            }
        } elseif ($this->isR3dDir($object)) {
            if ($this->isUploaded($object)/* && $this->isObjectNotChanging($object)*/) {
                if ($submission = $this->processUpload($object, $provider)) {
                    $submissionsBackup = $this->store['submissions_backup']['path'] . DIRECTORY_SEPARATOR . $submission['code'];
                    echo date('Y-m-d H:i:s') . ' BACKUP: ' . $object->getRealPath(), PHP_EOL;
                    $this->backupUpload($object, $submissionsBackup, $provider);
                    echo date('Y-m-d H:i:s') . ' DELETE: ' . $object->getRealPath(), PHP_EOL;
                    $this->uploads_model->rrmdir($object->getRealPath());
                    return true;
                }
            }
        } elseif ($object->isDir()) {
            $processed = true;
            foreach(new DirectoryIterator($object->getRealPath()) as $directoryObject) {
                if (!$this->processObject($directoryObject, $provider)) {
                    $processed = false;
                }
            }
            if ($processed) {
                echo date('Y-m-d H:i:s') . ' DELETE: ' . $object->getRealPath(), PHP_EOL;
                $this->uploads_model->rrmdir($object->getRealPath());
                return true;
            }
        }

        return false;
    }

    /**
     * @param \SplFileInfo $object
     * @param string $provider
     * @return bool
     */
    protected function processUpload($object, $provider)
    {
        if ($object && $provider) {
            $this->load->model('submissions_model');
            $this->load->model('uploads_model');
            $objectPath = $object->getRealPath();
            $submissionId = $this->submissions_model->create_submission('', $provider['id'], $provider['prefix']);
            $submission = $this->submissions_model->get_submission($submissionId);
            if ($submission) {
                $submissionPath = false;
                if ($submission['location']) {
                    $volumePath = $this->store['original']['path']  . DIRECTORY_SEPARATOR . $submission['location'];
                    if ($this->uploads_model->is_enough_space_on_volume($volumePath, $objectPath)) {
                        $submissionPath = $volumePath;
                    } else {
                        echo 'NO SPACE FOR ' . $objectPath . ' ON ' . $volumePath;
                    }
                } else {
                    $volume = $this->uploads_model->get_available_volume($objectPath);
                    if ($volume) {
                        $this->submissions_model->set_submission_location($submission['id'], $volume);
                        $submissionPath = $this->store['original']['path']  . DIRECTORY_SEPARATOR . $volume;
                        $submission['location'] = $volume;
                    }
                }
                if ($submissionPath) {
                    $submissionPath .= DIRECTORY_SEPARATOR . $submission['code'];
                    if ($this->submitUpload($object, $submissionPath, $submission, $provider)) {
                        return $submission;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param \SplFileInfo $object
     * @param string $destination
     * @param array $submission
     * @param array $user
     * @return bool
     */
    protected function submitUpload($object, $destination, $submission, $user)
    {
        if ($object->isFile()) {
            $this->load->model('uploads_model');
            $this->load->model('clips_model');
            $fileName = $object->getFilename();
            $objectPath = $object->getRealPath();
            if (in_array(strtolower($object->getExtension()), $this->uploads_model->video_formats) && $fileName[0] != '.') {
                echo date('Y-m-d H:i:s') . ' SUBMIT: ' . $objectPath . ' => ' . $destination, PHP_EOL;
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $file = $destination . DIRECTORY_SEPARATOR . $object->getBasename();
                echo date('Y-m-d H:i:s') . ' COPY: ' . $objectPath . ' => ' . $file, PHP_EOL;
                if ($this->copyUpload($object, $destination)) {
                    echo date('Y-m-d H:i:s') . ' COPY SUCCESS', PHP_EOL;
                    $this->load->model('clips_model');
                    echo date('Y-m-d H:i:s') . ' CREATE CLIP FROM FILE ' . $file, PHP_EOL;
                    $clipId = $this->clips_model->create_clip($file, $submission ? $submission['code'] : '', $user['id']);
                    echo date('Y-m-d H:i:s') . ' CREATED CLIP ' . $clipId, PHP_EOL;
                    Solr::addClipToIndex($clipId);
                    echo date('Y-m-d H:i:s') . ' INDEXED CLIP ' . $clipId, PHP_EOL;
                    return (bool) $clipId;
                } else {
                    echo date('Y-m-d H:i:s') . ' COPY FAILURE', PHP_EOL;
                }
            } else {
                return true;
            }
        } elseif ($object->isDir()) {
            $this->load->model('clips_model');
            $objectPath = $object->getRealPath();
            if ($this->isR3dDir($object)) {
                echo date('Y-m-d H:i:s') . ' SUBMIT: ' . $objectPath . ' => ' . $destination, PHP_EOL;
                $destination .= '_R3D' . DIRECTORY_SEPARATOR . $object->getFilename();
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                echo date('Y-m-d H:i:s') . ' COPY: ' . $objectPath . ' => ' . $destination, PHP_EOL;
                if ($this->copyUpload($object, $destination)) {
                    if ($r3dFile = $this->getR3dFile($destination)) {
                        $file = $r3dFile->getRealPath();
                        echo date('Y-m-d H:i:s') . ' CREATE CLIP FROM FILE ' . $file, PHP_EOL;
                        $clipId = $this->clips_model->create_clip($file, $submission ? $submission['code'] : '', $user['id']);
                        echo date('Y-m-d H:i:s') . ' CREATED CLIP ' . $clipId, PHP_EOL;
                        Solr::addClipToIndex($clipId);
                        echo date('Y-m-d H:i:s') . ' INDEXED CLIP ' . $clipId, PHP_EOL;
                        return (bool) $clipId;
                    }
                }
            } else {
                $uploaded = true;
                foreach(new DirectoryIterator($object->getRealPath()) as $directoryObject) {
                    if (!$this->submitUpload($directoryObject, $destination, $submission, $user)) {
                        $uploaded = false;
                    }
                }
                return $uploaded;
            }
        }
        return false;
    }

    /**
     * @param \SplFileInfo $object
     * @param string $destination
     * @param array $provider
     * @return bool
     */
    protected function backupUpload($object, $destination, $provider)
    {
        if ($object->isDot()) return true;
        $this->load->model('uploads_model');
        if ($object->isFile()) {
            $destination = $destination . str_replace($this->providers_path . DIRECTORY_SEPARATOR . $provider['login'], '', $object->getRealPath());
            $dir = pathinfo($destination, PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        } elseif ($object->isDir()) {
            $destination = $destination . str_replace($this->providers_path . DIRECTORY_SEPARATOR . $provider['login'], '', $object->getRealPath());
            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }
        }

        return $this->uploads_model->rcopy($object->getRealPath(), $destination);
    }

    /**
     * @param \SplFileInfo $object
     * @param string $destination
     * @return bool
     */
    protected function copyUpload($object, $destination)
    {
        if ($object->isFile()) {
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $destinationFile = $destination . DIRECTORY_SEPARATOR . $object->getBasename();
            $source = $object->getRealPath();
            return copy($source, $destinationFile) && filesize($source) == filesize($destinationFile);
        } elseif ($object->isDir()) {
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($object->getRealPath(), RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $item) {
                $result = true;
                if ($item->isDir()) {
                    $dirPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                    if (!file_exists($dirPath)) {
                        $result = mkdir($dirPath);
                    }
                } else {
                    $destinationFile = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                    $result = copy($item, $destinationFile) && filesize($item) == filesize($destinationFile);
                }
                if (!$result) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * @param \SplFileInfo $object
     * @return bool
     */
    protected function isR3dDir($object)
    {
        if (!$object->isDir()) {
            return false;
        }
        foreach(new DirectoryIterator($object->getRealPath()) as $directoryObject) {
            if ($directoryObject->isFile()) {
                /** \SplFileInfo $directoryObject */
                $ext = strtolower($directoryObject->getExtension());
                if ($ext == 'r3d' || $ext == 'rmd') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $dir
     * @return SplFileInfo|null
     */
    protected function getR3dFile($dir)
    {
        /** @var \SplFileInfo $object */
        foreach(new DirectoryIterator($dir) as $object) {
            if (strtolower($object->getExtension()) == 'r3d') {
                return $object;
            }
        }

        return null;
    }

    /**
     * @param \SplFileInfo $object
     * @return bool
     */
    protected function isUploaded($object)
    {
        if ($object->isDot()) return true;
        if ($object->isFile()) {
            return strpos($object->getFilename(), 'catapult_part') === false && $object->getExtension() != 'aspx' && $object->getExtension() != 'haspx'
            && !is_file($object->getRealPath() . '.haspx') && !is_file($object->getRealPath() . '.aspx');
        } elseif ($this->isR3dDir($object)) {
            $files = $this->getDirectoryFiles($object->getRealPath());
            if ($files) {
                foreach ($files as $file) {
                    if (!is_file($file)) {
                        return false;
                    }
                }
            } else {
                return false;
            }
            return true;
        } elseif ($object->isDir()) {
            foreach(new DirectoryIterator($object->getRealPath()) as $innerObject) {
                if (!$this->isUploaded($innerObject)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $path
     * @return array
     */
    protected function getDirectoryFiles($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $files = array();
        if ($this->uploadsLog) {
            foreach(new SplFileObject($this->uploadsLog) as $line) {
                if (strpos($line, 'Start pending') === 0) {
                    $parts = explode(', ', $line);
                    if ($parts[3] == 'upload') {
                        $started = $parts[1];
                        $GUID = $parts[2];
                    }
                }
                if (strpos($line, 'File') === 0 && isset($started, $GUID)) {
                    $parts = explode(', ', $line);
                    $file = trim($parts[1]);
                    $dir = pathinfo($file, PATHINFO_DIRNAME);
                    if (strpos($dir, $path) === 0) {
                        if ($files && ($last = end($files)) && ($last[1] !== $started || $last[2] !== $GUID)) {
                            $files = array();
                        }
                        $files[$file] = array($file, $started, $GUID);
                    }
                }
                if (strpos($line, 'End pending') === 0) {
                    $parts = explode(', ', $line);
                    if ($parts[2] == 'upload' && isset($started, $GUID)) {
                        unset($started, $GUID);
                    }
                }
            }
        }

        $files = array_map(function ($item) { return is_array($item) ? $item[0] : $item; }, $files);

        return $files;
    }

    /**
     * @param \SplFileInfo $object
     * @param int $time
     * @return bool
     */
    protected function isObjectNotChanging($object, $time = 5)
    {
        if ($object->isDot()) return true;
        if (isset($this->changesLog[$object->getRealPath()])) {
            $size1 = $this->changesLog[$object->getRealPath()][0];
            $time1 = $this->changesLog[$object->getRealPath()][1];
            if ((microtime(true) - $time1) < $time) {
                return false;
            } else {
                $size2 = $this->getSize($object);
                unset($this->changesLog[$object->getRealPath()]);
                if ($size1 === $size2) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $this->changesLog[$object->getRealPath()] = [$this->getSize($object), microtime(true)];
            return false;
        }
    }

    /**
     * @param \SplFileInfo $object
     * @return int
     */
    protected function getSize($object)
    {
        $totalSize = 0;
        if ($object->isFile()) {
            return $object->getSize();
        } elseif ($object->isDir()) {
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($object->getRealPath(), RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach($objects as $directoryObject) {
                $totalSize += $this->getSize($directoryObject);
            }
        }

        return $totalSize;
    }

    public function check_submissions() {
        if(!$this->input->is_cli_request()){
            exit();
        }
        $this->load->model('submissions_model');
        $submissions = $this->submissions_model->get_empty_submissions_list();
        if ($submissions) {
            $ids = array();
            foreach ($submissions as $submission) {
                $ids[] = $submission['id'];
            }
            $this->db_master->where_in('id', $ids);
            $this->db_master->delete('lib_submissions');
        }
    }

    public function register_missing_clips() {
//        $submissions = array(
//            '/storage/FSM13/MS150311',
//            '/storage/FSM13/MS150309',
//            '/storage/FSM13/MS150308',
//            '/storage/FSM13/JR150305',
//            '/storage/FSM13/JR150311',
//            '/storage/FSM13/JR150310'
//        );
//        $submissions = array(
//            '/storage/FSM13/JR150415',
//            '/storage/FSM13/JR150414',
//            '/storage/FSM13/MS150411',
//            '/storage/FSM13/JR150311',
//            '/storage/FSM13/JR150310',
//            '/storage/FSM13/MS150311',
//            '/storage/FSM13/MS150308',
//            '/storage/FSM13/MS150410'
//        );
//        $submissions = array(
//            '/storage/FSM13/CFI150421',
//            '/storage/FSM13/CBO150421',
//            '/storage/FSM13/CFI150418',
//            '/storage/FSM13/CFI150417',
//            '/storage/FSM13/JR150415',
//            '/storage/FSM13/JR150330',
//            '/storage/FSM13/JR150310',
//            '/storage/FSM13/JR150305',
//        );
//        $submissions = array(
//            '/storage/FSM13/CFI150428',
//            '/storage/FSM13/CFI150429',
//            '/storage/FSM13/CFI150424',
//            '/storage/FSM13/CFI150421',
//            '/storage/FSM13/CBO150421',
//            '/storage/FSM13/CFI150418',
//            '/storage/FSM13/CFI150417',
//            '/storage/FSM13/JR150415',
//            '/storage/FSM13/JR150330',
//            '/storage/FSM13/JR150311',
//            '/storage/FSM13/JR150310',
//            '/storage/FSM13/JR150305',
//            '/storage/FSM13/GG150227',
//        );
//        $submissions = array(
//            '/storage/FSM13/CFI150429',
//            '/storage/FSM13/CBO150421',
//            '/storage/FSM13/GLO150405'
//        );
//        $submissions = array(
//            '/storage/FSM13/MS150311'
//        );
//        $submissions = array(
//            '/storage/FSM14/AGI150616'
//        );
        $submissions = array(
            '/Volumes/FSM15/OFE150824',
            '/Volumes/FSM13/UK150427'
        );
        foreach ($submissions as $submission_path) {
            $submission_code = pathinfo($submission_path, PATHINFO_BASENAME);
            $this->load->model('submissions_model');
            $submissions = $this->submissions_model->get_submissions_list(['code' => $submission_code]);
            if ($submissions) {
                $submission = $submissions[0];
                foreach (glob($submission_path . '/' . $submission['code'] . '*') as $filename) {
                    echo $filename, PHP_EOL;
                    $this->load->model('clips_model');
                    $clip_code = pathinfo($filename, PATHINFO_FILENAME);
                    $clip = $this->clips_model->get_clip_id_by_code($clip_code);
                    if (!$clip) {
                        echo 'Missing clip:' . $clip_code, PHP_EOL;
                        //$clip_id = $this->clips_model->create_clip($filename, $submission['code'], $submission['provider_id']);

                        $clip = array(
                            'code' => $clip_code,
                            'ctime' => date('Y-m-d H:i:s'),
                            'client_id' => $submission['provider_id'],
                            'submission_id' => $submission['id'],
                            'original_filename' => basename($filename)
                        );
                        $this->db_master->insert('lib_clips', $clip);
                        $clip_id = $this->db_master->insert_id();

                        $this->clips_model->set_clip_res($clip_id, pathinfo($filename, PATHINFO_EXTENSION), 2, $filename);

                        $clip_content = array(
                            'clip_id' => $clip_id,
                            'lang' => 'en',
                            'title' => $clip_code
                        );
                        $this->db_master->insert('lib_clips_content', $clip_content);

                        if ($clip_id) {
                            Solr::addClipToIndex($clip_id);
                        }
                    } else {
                        Solr::addClipToIndex($clip);
                    }
                }
            }
            else {
                echo 'Missing submission: ' . $submission_code, PHP_EOL;
            }
        }
    }
    public function register_missing_r3d_clips_by_master() {
        $file = '/private/tmp/1.csv';
        if(!empty($file)){
            $codes = array();
            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($data[0]) {
                        $codes[] = $data[0];
                    }
                }
                fclose($handle);
            }
            if($codes) $submissions =$codes;
        }else{
            /*$submissions = array(
                'A003_C178_0306WF_001.R3D',
                'A003_C179_0306NJ_001.R3D',
                'A001_C304_0204DU_001.R3D',
                'A001_C305_0204FG_001.R3D',
                'A001_C027_0316O2_001.R3D'
            );*/
        }
        if(empty($submissions)) die('Not Submissions for script');
        $this->load->model('submissions_model');
        $this->load->model('clips_model');
        foreach ($submissions as $item) {
            $out=array();
            $ret=null;
            $command='find /Volumes -name "'.$item.'" 2>&1|grep  -v "Permission denied"';
            //echo $command.PHP_EOL;
            exec($command,$out,$ret);
            if(empty($out[0])){
                echo 'Missing Master File: ' . $item, PHP_EOL;
            }elseif(count($out)>1){
                echo 'Many Master Files Path: '.PHP_EOL;
                var_dump($out);
                continue;
            }else {
                $pathArr = explode('/', $out[0]);
                $submission_code = preg_replace('/_R3D/i', '', $pathArr[3]);
                $filename = $out[0];
                //echo '------------'.PHP_EOL.'Submission:'.$submission_code.PHP_EOL.'Path'.$filename.PHP_EOL;
                $submissions = $this->submissions_model->get_submissions_list(['code' => $submission_code]);
                if ($submissions) {
                    $clips_count = count($this->clips_model->get_clipsIds_by_codeMask($submission_code, 0, 999999));
                    $submission = $submissions[0];
                    $query = $this->db->get_where('lib_clips_res', ['location' => $filename, 'type' => 2]);
                    $resource = $query->result_array();
                    if (!$resource) {
                        echo 'Missing clip:' . $filename, PHP_EOL;
                        $clips_count++;
                        $clip_code = $submission['code'] . '_' . str_pad($clips_count, 4, 0, STR_PAD_LEFT);
                        $clip = array(
                            'code' => $clip_code,
                            'ctime' => date('Y-m-d H:i:s'),
                            'client_id' => $submission['provider_id'],
                            'submission_id' => $submission['id'],
                            'original_filename' => basename($filename)
                        );
                        var_dump($clip);
                        $this->db_master->insert('lib_clips', $clip);
                        $clip_id = $this->db_master->insert_id();

                        $this->clips_model->set_clip_res($clip_id, pathinfo($filename, PATHINFO_EXTENSION), 2, $filename);

                        $clip_content = array(
                            'clip_id' => $clip_id,
                            'lang' => 'en',
                            'title' => $clip_code
                        );
                        $this->db_master->insert('lib_clips_content', $clip_content);

                        if ($clip_id) {
                            Solr::addClipToIndex($clip_id);
                        }
                    }
                } else {
                    echo 'Missing submission: ' . $submission_code, PHP_EOL;
                }
            }
        }
    }

    public function register_missing_r3d_clips() {
        /*$submissions = array(
            'DZ150703' => array(
                '/Volumes/FSM15/DZ150703_R3D/A015_C126_08201B Copy.RDC/A015_C126_08201B_001.R3D',
                '/Volumes/FSM15/DZ150703_R3D/A015_C126_08201B.RDC/A015_C126_08201B_001.R3D',
                '/Volumes/FSM15/DZ150703_R3D/A015_C127_08202D Copy.RDC/A015_C127_08202D_001.R3D',
                '/Volumes/FSM15/DZ150703_R3D/A015_C127_08202D.RDC/A015_C127_08202D_001.R3D',
                '/Volumes/FSM15/DZ150703_R3D/A015_C128_0820HF Copy Copy.RDC/A015_C128_0820HF_001.R3D',
            )
        );*/
        $submissions = array(
            'BG128' => array(
                '/Volumes/FSU04/BG128_R3D/A003_C178_0306WF.RDC/A003_C178_0306WF_001.R3D',
                '/Volumes/FSU04/BG128_R3D/A003_C179_0306NJ.RDC/A003_C179_0306NJ_001.R3D'
            ),
            'BG120' => array(
                '/Volumes/FSU02/BG120_R3D/A001_C304_0204DU.RDC/A001_C304_0204DU_001.R3D',
                '/Volumes/FSU02/BG120_R3D/A001_C305_0204FG.RDC/A001_C305_0204FG_001.R3D'
            ),
            'BG115' => array(
                '/Volumes/FSU02/BG115_R3D/A004_C038_0225PS.RDC/A004_C038_0225PS_001.R3D',
                '/Volumes/FSU02/BG115_R3D/A004_C039_0225JU.RDC/A004_C039_0225JU_001.R3D'
            ),
            'BG113' => array(
                '/Volumes/FSU02/BG113_R3D/A004_C272_0115ED.RDC/A004_C272_0115ED_001.R3D',
                '/Volumes/FSU02/BG113_R3D/A004_C273_0115XW.RDC/A004_C273_0115XW_001.R3D'
            ),
            'BG104b' => array(
                '/Volumes/FSU02/BG104_R3D/A023_C131_0408EX.RDC/A023_C131_0408EX_001.R3D',
                '/Volumes/FSU02/BG104_R3D/A023_C132_0408MH.RDC/A023_C132_0408MH_001.R3D'
            ),
            'BG93' => array(
                '/Volumes/FSU02/BG93_R3D/A001_C156_0824FL.RDC/A001_C156_0824FL_001.R3D',
                '/Volumes/FSU02/BG93_R3D/A001_C154_0824E7.RDC/A001_C154_0824E7_001.R3D'
            ),
            'RHO151019' => array(
                '/Volumes/FSM16/RHO151019_R3D/A001_C027_0316O2.RDC/A001_C027_0316O2_001.R3D'
            )
        );

        //$clips_count = 1164;
        $this->load->model('submissions_model');
        $this->load->model('clips_model');
        foreach ($submissions as $submission_code => $files) {
            $submissions = $this->submissions_model->get_submissions_list(['code' => $submission_code]);
            if ($submissions) {
                $clips_count = count($this->clips_model->get_clipsIds_by_codeMask($submission_code,0,999999));
                $submission = $submissions[0];
                foreach ($files as $filename) {
                    $query = $this->db->get_where('lib_clips_res', ['location' => $filename, 'type' => 2]);
                    $resource = $query->result_array();
                    if (!$resource) {
                        echo 'Missing clip:' . $filename, PHP_EOL;
                        $clips_count++;
                        $clip_code = $submission['code'] . '_' . str_pad($clips_count, 4, 0, STR_PAD_LEFT);
                        $clip = array(
                            'code' => $clip_code,
                            'ctime' => date('Y-m-d H:i:s'),
                            'client_id' => $submission['provider_id'],
                            'submission_id' => $submission['id'],
                            'original_filename' => basename($filename)
                        );

                        $this->db_master->insert('lib_clips', $clip);
                        $clip_id = $this->db_master->insert_id();

                        $this->clips_model->set_clip_res($clip_id, pathinfo($filename, PATHINFO_EXTENSION), 2, $filename);

                        $clip_content = array(
                            'clip_id' => $clip_id,
                            'lang' => 'en',
                            'title' => $clip_code
                        );
                        $this->db_master->insert('lib_clips_content', $clip_content);

                        if ($clip_id) {
                            Solr::addClipToIndex($clip_id);
                        }
                    }
                }
            }
            else {
                echo 'Missing submission: ' . $submission_code, PHP_EOL;
            }
        }
    }

    public function register_missing_clip() {
//        $this->load->model('clips_model');
////        $clip = array(
////            'code' => 'GHU150609_1561',
////            'ctime' => date('Y-m-d H:i:s'),
////            'client_id' => 124,
////            'submission_id' => 3341,
////            'original_filename' => 'A005_C010_0920XO_001.R3D'
////        );
////
////        $this->db_master->insert('lib_clips', $clip);
////        $clip_id = $this->db_master->insert_id();
////
//        //$this->clips_model->set_clip_res(463356, 'R3D', 2, '/storage/FSM14/GHU150609_R3D/A005_C010_0920XO.RDC/A005_C010_0920XO_001.R3D');
//
//        $clip_content = array(
//            'clip_id' => 463356,
//            'lang' => 'en',
//            'title' => 'GHU150609_1561'
//        );
////
//        $this->db_master->insert('lib_clips_content', $clip_content);
////
////        if ($clip_id) {
////            Solr::addClipToIndex($clip_id);
////        }

        Solr::addClipToIndex(461924);
        Solr::addClipToIndex(461928);
    }

    public function register_missing_resources($delete_resources = 0) {
        $submissions = array(
            //'OFE030' => '/storage/FSM14/OFE030'
            'JB011' => '/Volumes/FSM10/JB011'
        );
        $this->load->model('submissions_model');
        foreach ($submissions as $submission_code => $submission_path) {
            $submissions = $this->submissions_model->get_submissions_list(['code' => $submission_code]);
            if ($submissions) {
                $submission = $submissions[0];
                $files = scandir($submission_path);
                $this->db->select('id, code');
                $this->db->where('submission_id', $submission['id']);
                $query = $this->db->get('lib_clips');
                $clips = $query->result_array();
                foreach ($clips as $clip) {
                    $query = $this->db->get_where('lib_clips_res', ['clip_id' => $clip['id'], 'type' => 2]);
                    $resource = $query->result_array();
                    if (!$resource) {
                        if ($delete_resources) {
                            $this->db_master->delete('lib_clips_res', array('clip_id' => $clip['id']));
                        }
                        $master_files = array_filter($files, function($file) use ($clip) {
                            return pathinfo($file, PATHINFO_FILENAME) == $clip['code'];
                        });
                        if ($master_files) {
                            $master_file = $submission_path . '/' . array_shift($master_files);
                            $resource = [
                                'clip_id' => $clip['id'],
                                'resource' => pathinfo($master_file, PATHINFO_EXTENSION),
                                'type' => 2,
                                'location' => $master_file
                            ];
                            $this->db_master->insert('lib_clips_res', $resource);
                            $this->db_master->where('id', $clip['id']);
                            $this->db_master->update('lib_clips', array('original_filename' => pathinfo($master_file, PATHINFO_BASENAME)));
                        } else {
                            echo 'Missing master resource: ' . $clip['code'], PHP_EOL;
                        }
                    }
                }
            }
            else {
                echo 'Missing submission: ' . $submission_code, PHP_EOL;
            }
        }
    }

    public function link_master() {
        $file = __DIR__ . '/master.csv';
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[1] && $data[2] !== 'DELETE') {
                    $code = $data[1];
                    $volume = $data[2];
                    $code_parts = explode('_', $code);
                    $submission = $code_parts[0];
                    $path = '/Volumes/' . $volume . '/' . $submission . '/' . $code;
                    $files = glob($path . '.*');
                    if (!$files) {
                        echo $path, PHP_EOL;
                        file_put_contents('/tmp/missing_master_on_disk.txt', $path . "\n", FILE_APPEND);
                    } else {
                        //echo $files[0], PHP_EOL;
                    }
                }
            }
            fclose($handle);
        }
    }

    public function rename_clips() {
        $this->db->where('id >', 447140);
        $query = $this->db->get('lib_clips');
        $clips = $query->result_array();
        foreach ($clips as $clip) {
            $query = $this->db->get_where('lib_clips_res', ['clip_id' => $clip['id'], 'type' => 2]);
            $resource = $query->result_array();
            $resource = $resource[0];
            $from = $resource['location'];
            $to = pathinfo($from, PATHINFO_DIRNAME) . '/' . $clip['original_filename'];
            if ($from !== $to) {
                echo $from, PHP_EOL;
                echo $to, PHP_EOL;
                echo PHP_EOL;
                copy($from, $to);
                $this->db_master->where('id', $resource['id']);
                $this->db_master->update('lib_clips_res', array('location' => $to));

                $this->db_master->where('id', $clip['id']);
                $this->db_master->update('lib_clips', array('code' => pathinfo($to, PATHINFO_FILENAME)));
            }
            Solr::addClipToIndex($clip['id']);
        }
    }

    public function delete_duplicates() {
        $duplicates = $this->db->query('
            SELECT cr1.clip_id, cr1.location
            FROM lib_clips_res cr1
            INNER JOIN lib_clips_res cr2 ON cr1.location = cr2.location
            AND cr1.clip_id <> cr2.clip_id
            WHERE cr1.type = 2 AND cr1.clip_id > 434849
        ')->result_array();

        $deleted = array();

        foreach ($duplicates as $duplicate) {

            if (!in_array($duplicate['clip_id'], $deleted)) {

                $this->db->select('clip_id, location');
                $this->db->where('clip_id !=', $duplicate['clip_id']);
                $this->db->where('location', $duplicate['location']);
                $query = $this->db->get('lib_clips_res');
                $same_clips = $query->result_array();

                foreach ($same_clips as $same_clip) {
                    //echo $same_clip['clip_id'], PHP_EOL;
                    $this->db_master->delete('lib_clips', array('id' => $same_clip['clip_id']));
                    $this->db_master->delete('lib_clips_content', array('clip_id' => $same_clip['clip_id']));
                    $this->db_master->delete('lib_clips_res', array('clip_id' => $same_clip['clip_id']));
                    $this->db_master->delete('lib_clips_res_tasks', array('clip_id' => $same_clip['clip_id']));
                    $deleted[] = $same_clip['clip_id'];
                }

                echo $duplicate['clip_id'] . ' => ' .  implode(' : ', array_map(function($clip) { return $clip['clip_id']; }, $same_clips)), PHP_EOL;
            }
        }
    }

    public function delete_clips() {
        $file = __DIR__ . '/to_delete.csv';
        $codes = array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                //if ($row == 1) continue;
                if ($data[1] && $data[2] === 'DELETE') {
                    $codes[] = $data[1];
                }
            }
            fclose($handle);
        }
//        $codes = array(
//
//        );
        if ($codes) {
            $this->db->select('id');
            $this->db->where_in('code', $codes);
            $query = $this->db->get('lib_clips');
            $clips = $query->result_array();
            if ($clips) {
                echo count($clips), PHP_EOL;
                $this->load->model('clips_model');
                $clips = array_map(function($clip) { return $clip['id']; }, $clips);
                $this->db_master->where_in('id', $clips);
                $this->db_master->delete('lib_clips');
                $this->db_master->where_in('clip_id', $clips);
                $this->db_master->delete('lib_clips_content');
                $this->db_master->where_in('clip_id', $clips);
                $this->db_master->delete('lib_clips_res');
                $this->db_master->where_in('clip_id', $clips);
                $this->db_master->delete('lib_clips_res_tasks');
                $this->clips_model->delete_from_index($clips);
            }
        }
    }

    public function delete_clips_by_submission() {
        $submissions = array(3408);
        $this->db->select('id, code');
        $this->db->where_in('submission_id', $submissions);
        $query = $this->db->get('lib_clips');
        $clips = $query->result_array();

        if ($clips) {
            echo count($clips), PHP_EOL;
            $this->load->model('clips_model');
            $clips = array_map(function($clip) { return $clip['id']; }, $clips);
            $this->db_master->where_in('id', $clips);
            $this->db_master->delete('lib_clips');
            $this->db_master->where_in('clip_id', $clips);
            $this->db_master->delete('lib_clips_content');
            $this->db_master->where_in('clip_id', $clips);
            $this->db_master->delete('lib_clips_res');
            $this->db_master->where_in('clip_id', $clips);
            $this->db_master->delete('lib_clips_res_tasks');
            $this->clips_model->delete_from_index($clips);
        }
    }

    public function resubmit() {
//        $files = array(
//            '/home/ivan/r3d1.csv',
//            '/home/ivan/r3d2.csv',
//            '/home/ivan/r3d3.csv',
//            '/home/ivan/r3d4.csv',
//            '/home/ivan/r3d5.csv',
//            '/home/ivan/r3d6.csv'
//        );
        $files = array(
            '/home/ubuntu/r3d1.csv',
            '/home/ubuntu/r3d2.csv'
        );
        $files = array('/home/ubuntu/1.csv');

        foreach ($files as $file) {
            $codes = array();
            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    //if ($row < 5) continue;
                    if ($data[0]) {
                        $codes[] = $data[0];
                    }
                }
                fclose($handle);
            }
            if ($codes) {
                echo 'Script will be resubmit '.count($codes).' clips'.PHP_EOL;
                $this->db->select('id');
                $this->db->where_in('code', $codes);
                $query = $this->db->get('lib_clips');
                $clips = $query->result_array();
                if ($clips) {
                    $this->load->model('clips_model');
                    $clips = array_map(function($clip) { return $clip['id']; }, $clips);

                    $this->db_master->where_in('clip_id', $clips);
                    $this->db_master->where('type !=', 2);
                    $this->db_master->delete('lib_clips_res');

                    $this->db_master->where_in('clip_id', $clips);
                    $this->db_master->delete('lib_clips_res_tasks');
                }
            }
        }

        /* $codes = array(
        );

        if ($codes) {
            $this->db->select('id');
            $this->db->where_in('code', $codes);
            $query = $this->db->get('lib_clips');
            $clips = $query->result_array();
            if ($clips) {
                $this->load->model('clips_model');
                $clips = array_map(function($clip) { return $clip['id']; }, $clips);

                $this->db_master->where_in('clip_id', $clips);
                $this->db_master->where('type !=', 2);
                $this->db_master->delete('lib_clips_res');

                $this->db_master->where_in('clip_id', $clips);
                $this->db_master->delete('lib_clips_res_tasks');
            }
        }*/
    }

    public function resubmit_submissions() {

        $submissions = array(
            'JA150504'
        );

        $this->db->select('id');
        $this->db->where_in('code', $submissions);
        $query = $this->db->get('lib_submissions');
        $submissions = $query->result_array();
        $submissions = array_map(function($submission) { return $submission['id']; }, $submissions);

        $this->db->select('id, code');
        $this->db->where_in('submission_id', $submissions);
        $query = $this->db->get('lib_clips');
        $clips = $query->result_array();

        if ($clips) {
            echo 'Script will be resubmit '.count($clips).' clips'.PHP_EOL;
            $this->load->model('clips_model');
            $clips = array_map(function($clip) { return $clip['id']; }, $clips);

            $this->db_master->where_in('clip_id', $clips);
            $this->db_master->where('type !=', 2);
            $this->db_master->delete('lib_clips_res');

            $this->db_master->where_in('clip_id', $clips);
            $this->db_master->delete('lib_clips_res_tasks');
        }
    }

    public function import_master() {
        $this->load->model('import_task_model');
        $tasks = $this->import_task_model->get_tasks(array('status' => 0));
        if ($tasks) {
            $dir = realpath(__DIR__ . '/../../data/upload/import');
            foreach ($tasks as $task) {
                $file = $dir . '/' . $task['file'];
                $lines = $this->get_lines_count($file);
                if ($lines > 0) {
                    $this->import_task_model->update_task($task['id'], array('total' => $lines, 'status' => 1));
                    if (($handle = fopen($file, 'r')) !== false) {
                        $line = 0;
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            $line++;
                            if ($line == 1) {
                                continue;
                            }
                            if (count($data) == 2 && !empty($data[0]) && !empty($data[1])) {
                                $this->db->select('id');
                                $this->db->where('code', $data[0]);
                                $query = $this->db->get('lib_clips');
                                $clip = $query->result_array();
                                if ($clip) {
                                    $clip = $clip[0];
                                    $this->db_master->where('clip_id', $clip['id']);
                                    $this->db_master->where('type', 3);
                                    $this->db_master->delete('lib_clips_res');
                                    $master_resource = [
                                        'clip_id' => $clip['id'],
                                        'resource' => pathinfo($data[1], PATHINFO_EXTENSION),
                                        'type' => 3,
                                        'location' => $data[1]
                                    ];
                                    $this->db_master->insert('lib_clips_res', $master_resource);
                                }
                            }
                            if (!($line % 10) && $line >= 10) {
                                $this->import_task_model->update_task($task['id'], array('processed' => $line));
                            }
                        }
                        $this->import_task_model->update_task($task['id'], array('processed' => $line, 'status' => 2));
                        fclose($handle);
                    }
                }
                @unlink($file);
            }
        }
    }

    public function check_submit_processes() {
        $this->db->where('status', 0);
        $query = $this->db->get('lib_uploads_submit');
        $res = $query->result_array();
        if ($res) {
            foreach ($res as $item) {
                if ($item['pid'] && !posix_getpgid($item['pid'])) {
                    $this->db_master->where('id', $item['id']);
                    $this->db_master->delete('lib_uploads_submit');
                }
            }
        }
    }

    public function check_master_files() {
        $i = 0;
        $stop = false;
        $portion = 10000;
        $checked = 0;
        $fp = fopen('/tmp/missing_master_on_disk.csv', 'w');
        while (!$stop) {
            $from = $portion * $i;
            $this->db->limit($portion, $from);
            $this->db->select('c.id, c.code, cr.location');
            $this->db->from('lib_clips c');
            $this->db->join('lib_clips_res cr', 'c.id = cr.clip_id AND cr.type = 2', 'inner');
            $query = $this->db->get();
            $res = $query->result_array();
            if($res) {
                foreach ($res as $item) {
                    if (!file_exists($item['location'])) {
                        fputcsv($fp, array($item['id'], $item['code'], $item['location']));
                    }
                    $checked++;
                }
            } else {
                $stop = true;
            }
            $i++;
            echo $checked, PHP_EOL;
        }
        fclose($fp);
    }

    public function fix_double_underscore($submission=false){
        if(!$submission) die('----'.PHP_EOL.'ERROR:'.PHP_EOL.'Enter submission. Example'.PHP_EOL.'php index.php uploadstools fix_double_underscore MHAN004'.PHP_EOL.'----'.PHP_EOL);
        //FIX DOUBLE UNDERSCORE IN CLIP CODE
        $this->db->select('id,code');
        $this->db->like('code', $submission);
        $query = $this->db->get('lib_clips');
        $clips = $query->result_array();
        if($clips){
            echo 'Script will be replace '.count($clips).' clips'.PHP_EOL;
            foreach ($clips as $clip) {
                $code=preg_replace('/__/i','_',$clip['code']);
                //lib_clips
                $this->db_master->where('id', $clip['id']);
                $this->db_master->update('lib_clips', array('code' => $code));
                //lib_clips_content
                $this->db_master->where('clip_id', $clip['id']);
                $this->db_master->update('lib_clips_content', array('title' => $code));
                Solr::addClipToIndex($clip['id']);
            }
        }
    }

    public function test() {
        $this->load->model('uploads_model');
        $files = $this->uploads_model->dir_to_array('/Volumes/Data/Submissions', 1, 'TSC150804');
        print_r($files);
    }

    public function add_to_solr($id,$action='eq'){
        $id=(int)$id;
        switch($action){
            case 'more':
                $this->db->select('id');
                $this->db->where('id >',$id);
                $query = $this->db->get('lib_clips');
                $clips = $query->result_array();
                if($clips){
                    echo 'Add '.count($clips).' clips'.PHP_EOL;
                    foreach ($clips as $clip)
                        Solr::addClipToIndex($clip['id']);
                }
                break;
            case 'eq':
            default : Solr::addClipToIndex($id);
        }
    }

    private function get_lines_count($file) {
        $count = 0;
        $handle = fopen($file, 'r');
        while (!feof($handle)) {
            fgets($handle);
            $count++;
        }
        fclose($handle);
        return $count;
    }

    private function get_uncompleted_sessions($user){
        $this->aspdb->select('id');
        $this->aspdb->where('status', 'running');
        $this->aspdb->where('dest_path', '/Volumes/Data/providers/fsprovider/' . $user);
        $query = $this->aspdb->get('fasp_sessions');
        $res = $query->result_array();
        return $res;
    }

    private function log_submit(){
        $this->db_master->insert('lib_uploads_submit', array('status' => 0));
        return $this->db_master->insert_id();
    }

    private function log_submit_update($id){
//        $this->db_master->where('id', $id);
//        $this->db_master->update('lib_uploads_submit', array('status' => 1));
        $this->db_master->where('id', $id);
        $this->db_master->delete('lib_uploads_submit');
    }

    private function is_previous_submit($provider) {
        $this->db->where('status', 0);
        $this->db->from('lib_uploads_submit');
        if ($this->db->count_all_results() < 2) {
            $this->db->where('status', 0);
            $this->db->where('provider_id', $provider);
            $this->db->from('lib_uploads_submit');
            return $this->db->count_all_results();
        } else {
            return true;
        }
    }

    private function get_active_submits() {
        $this->db->where('status', 0);
        $query = $this->db->get('lib_uploads_submit');
        $res = $query->result_array();
        return $res;
    }

    private function get_new_submit($provider) {
        $submit = false;
        $this->db_master->query('LOCK TABLES lib_uploads_submit WRITE');
        $this->db_master->where('status', 0);
        $this->db_master->where('provider_id', $provider);
        $this->db_master->from('lib_uploads_submit');
        if (!$this->db_master->count_all_results()) {
            $this->db_master->insert('lib_uploads_submit', array('status' => 0, 'provider_id' => $provider, 'pid' => getmypid()));
            $submit = $this->db_master->insert_id();
        }
        $this->db_master->query('UNLOCK TABLES');
        return $submit;
    }
}
?>