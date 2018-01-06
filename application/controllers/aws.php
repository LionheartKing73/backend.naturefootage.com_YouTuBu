<?php
require_once(FCPATH . '/scripts/aws3/aws-autoloader.php');

//use Aws\S3\S3Client;
/**
 * Class Aws
 * @property PDO dbh
 * @property PDO dbh_master
 */
class Aws extends CI_Controller{
    private $s3Client;
    private $s3TransferTos3;
    private $s3TransferFroms3;
    private $_start=0;
    private $_lap=0;
    private $_sourceUp = '/tmp/s3u/';
    private $_sourceDw = '/tmp/s3d/';
    private $_dest = 's3://s3.footagesearch.com/transtest';

    private function dump($comment = '', $arg = ''){
        if($this->mode == 'dev'){
            echo PHP_EOL . '-----' . PHP_EOL;
            if($arg) var_dump($arg);
            echo $comment;
            echo PHP_EOL . '-----' . PHP_EOL;
        }
    }
    private function connectDb() {
        $this->dbMaster = $this->load->database( 'master', TRUE );
    }
    public function __construct() {
        parent::__construct();
        define('BASEPATH', true);

        $db = array();
        $config = array();
        $store = array();

        require(FCPATH . '/application/config/database.php');
        require(FCPATH . '/application/config/config.php');
        require(FCPATH . '/application/config/store.php');

        /*$this->config['dbhost'] = $db['default']['hostname'];
        $this->config['dbname'] = $db['default']['database'];
        $this->config['dbuser'] = $db['default']['username'];
        $this->config['dbpassword'] = $db['default']['password'];

        $this->config['master_dbhost'] = $db['master']['hostname'];
        $this->config['master_dbname'] = $db['master']['database'];
        $this->config['master_dbuser'] = $db['master']['username'];
        $this->config['master_dbpassword'] = $db['master']['password'];

        $this->config['userFolderSalt'] = $config['user_folder_salt'];*/

        $this->store = $store;
        if(!$this->s3Client){
            $this->s3Client = new \Aws\S3\S3Client([
                'version'     => 'latest',
                'region'      => $this->store['s3']['region'],
                'credentials' => [
                    'key'    => $this->store['s3']['key'],
                    'secret' => $this->store['s3']['secret']
                ]
            ]);
        }
        $this->s3ClientFactory = \Aws\S3\S3Client::factory(array(
            'version'     => 'latest',
            'region'      => $this->store['s3']['region'],
            'credentials' => [
                'key'    => $this->store['s3']['key'],
                'secret' => $this->store['s3']['secret']
            ]
        ));

        if(!is_dir($this->_sourceDw)) mkdir($this->_sourceDw,0777);
        if(!is_dir($this->_sourceUp)) mkdir($this->_sourceUp,0777);
        if(!$this->s3TransferTos3){
            $this->s3TransferTos3 = new \Aws\S3\Transfer($this->s3Client, $this->_sourceUp, $this->_dest,array('debug'=>true));
        }
        if(!$this->s3TransferFroms3){
            $this->s3TransferFroms3 = new \Aws\S3\Transfer($this->s3Client, $this->_dest, $this->_sourceDw,array('debug'=>true));
        }
        $this->connectDb();
    }
    private function timerStart(){$this->_start=$this->_lap= microtime(true);}
    private function timerStop($action){
        $time = microtime(true) - $this->_start;
        printf('Script Worked %.4F sec.'.PHP_EOL, $time);
        $this->dbh_master->query("INSERT INTO `transfertest` (`action`,`time`) VALUES ('$action','$time')");
    }
    private function timerLap(){
        $lap=microtime(true);
        $time = $lap - $this->_lap;
        $this->_lap=$lap;
        printf('Lap worked %.4F sec.'.PHP_EOL, $time);
    }

    public function s3Upload($filePath,$fileName=null){
        $bucket=$this->_dest;
        try {
            $resource = fopen($filePath, 'r');
            if(!$this->s3Client->doesBucketExist($bucket)) $this->s3Client->createBucket($bucket);
            $res=new stdClass();
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $contentType = finfo_file($finfo, $filePath);
            $options = $contentType ? array('params' => array('ContentType' => $contentType)) : array();
            $res=$this->s3Client->upload($bucket, $fileName , $resource, 'public-read', $options);
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file $filePath .\n";
            return false;
        }
        $res=(empty($res['Location']))?$res['ObjectURL']:$res['Location'];
        return urldecode($res);
    }
    public function alls3Upload(){
        $idir = new DirectoryIterator($this->_source);
        foreach($idir as $file){
            if ($file != '.' && $file != '..'){
                $this->s3Upload($file->getPathname(),$file->getFilename());
            }
        }
    }

    public function tos3(){
        $this->timerStart();
        $this->s3TransferTos3->transfer();
        $this->timerStop(__FUNCTION__);
    }
    public function froms3(){
        $this->timerStart();
        $this->s3TransferFroms3->transfer();
        $this->timerStop(__FUNCTION__);
    }
    public function download($all=false){
        $user_id = $this->session->userdata('uid');
        if($user_id) {
            $val=(!empty($_REQUEST['custom-path']))?$_REQUEST['custom-path']:'';
            $view='<a href="'.$this->getUrlByPath( 'transtest/A006_C080_0317HX_001.R3D', 35).'">transtest/A006_C080_0317HX_001.R3D</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/BC01_093_ProRes422.mov', 35).'">transtest/BC01_093_ProRes422.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/BF094_0015_Cineform.mov', 35).'">transtest/BF094_0015_Cineform.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/BG144_0001_ProRes4444.mov', 35).'">transtest/BG144_0001_ProRes4444.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/CFI005_PJPEG.mov', 35).'">transtest/CFI005_PJPEG.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/DF001_0001_Cineform.mov', 35).'">transtest/DF001_0001_Cineform.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/DZ02_119_ProRes.mov', 35).'">transtest/DZ02_119_ProRes.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/GK150828_0005_H264-50Mbps.mov', 35).'">transtest/GK150828_0005_H264-50Mbps.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/JKL01_050_SD-Uncompressed.mov', 35).'">transtest/JKL01_050_SD-Uncompressed.mov</a><br>';
            $view.='<a href="'.$this->getUrlByPath( 'transtest/NK150707_0001_H264-HD.MOV', 35).'">transtest/NK150707_0001_H264-HD.MOV</a><br>';
            $view.='<form action=""><input type="text" name="custom-path" placeholder="Enter custom path" value="'.$val.'">';
            $view.='<input type="submit" value="Send"></form><br>';
            $view.=(!empty($_REQUEST['custom-path']))?'<a href="'.$this->getUrlByPath( $val, 35).'">'.$val.'</a><br>':'';
            echo $view;
        }else{echo 'You have to log in';}

    }
    function getUrlByPath($path,$min=15) {
        $cmd = $this->s3ClientFactory->getCommand('GetObject', [
            'Bucket' => 's3.footagesearch.com',
            'Key'    => $path
        ]);

        $request = $this->s3ClientFactory->createPresignedRequest($cmd, '+'.$min.' minutes');
        return (string) $request->getUri();
    }
    function getSizeByPath($path) {
        $cmd = $this->s3ClientFactory->getCommand('HeadObject', [
            'Bucket' => 's3.footagesearch.com',
            'Key'    => $path
        ]);

        $request = $this->s3ClientFactory->execute($cmd);
        return $request;
    }
}