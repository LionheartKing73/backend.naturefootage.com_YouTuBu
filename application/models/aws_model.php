<?php
//require_once(FCPATH . '/scripts/aws3/aws-autoloader.php');
require_once(FCPATH . '/scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;
class Aws_model extends CI_Model {
    public $s3;
    public $store;

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->store = $store;
        if(!$this->s3)
            $this->s3 = S3Client::factory(array(
                'key'    => $this->store['s3']['key'],
                'secret' => $this->store['s3']['secret']
            ));

    }
    /**
     * Function upload file to S3 Server
     * @param $filePath - on this computer
     * @param $s3Path - path file Example: archives/contributor
     * @param $fileName - on S3 server
     * @param $option - add options (associative) array
     * @return string S3 URL file
     */
  function upload($filePath,$s3Path,$fileName=null,$option=null,$bucketName='preview') {
      $fileName=(empty($fileName))?basename($filePath):$fileName;
      try {
          $resource = fopen($filePath, 'r');
          $res=new stdClass();
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $contentType = finfo_file($finfo, $filePath);
          $options = $contentType ? array('params' => array('ContentType' => $contentType)) : array();
          if(!empty($options)){
              foreach($option as $k => $v) $options['params'][$k]=$v;
          }
          $bucket=$this->store[$bucketName]['bucket'].'/'.$s3Path;
          $res=$this->s3->upload($bucket, $fileName , $resource, 'public-read', $options);
      } catch (Aws\S3\Exception\S3Exception $e) {
          echo "There was an error uploading the file.\n";
          return false;
      }
      $res=(empty($res['Location']))?$res['ObjectURL']:$res['Location'];
      return urldecode($res);
  }

  function get_presigned_url($key, $filename)
  {
      $command = $this->s3->getCommand('GetObject', array(
          'Bucket' => $this->store['thumb']['bucket'],
          'Key'    => $key,
          'ResponseContentDisposition' => 'attachment; filename="'.$filename.'"'
      ));

      return $command->createPresignedUrl('+20 minutes');
  }

    /**
     * @param string $local_path e.g. /tmp/SLI170618_0003_19.jpg
     * @param string $distination_path e.g. http://video.naturefootage.com/previews/SLI/SLI170618/stills_browse/SLI170618_0003/SLI170618_0003_19.jpg
     * @return mixed
     */
  function upload_resource($local_path, $distination_path)
  {
      $distination_path_array = parse_url($distination_path);

      return $this->aws_model->upload(
          $local_path,
          trim(dirname($distination_path_array['path']),'\/'),
          basename($distination_path_array['path']),
          null,
          'resources'
      );

  }

    /**
     * @param string $path
     * @return bool
     */
    public function delete_one_by_path($path)
    {
        $path_array = parse_url($path);

        if($this->isNotValidPath($path, $path_array))
            return false;
        return (bool) $this->s3->deleteObject([
            'Bucket' => $path_array['host'],
            'Key'    => trim($path_array['path'], '\/')
        ]);
    }

    private function isNotValidPath($path, $path_array)
    {
        $slashesCnt = substr_count($path, '/');
        return !(
            $path_array
            && $path
            && $slashesCnt
            && strlen($path) > $slashesCnt
        );
    }

    /**
     * @param $path
     * @return bool|int
     */
    public function delete_multiple_by_path($path)
    {
        $path_array = parse_url($path);
        if($this->isNotValidPath($path, $path_array))
            return false;
        return (bool) $this->s3->deleteMatchingObjects($path_array['host'], trim($path_array['path'],'\/'));
    }

    /**
     * @param string $path e.g. e.g. s3://s3.footagesearch.com/stills/AB009_0002.jpg
     * @return bool
     */
    public function delete_resource_one_or_many_by_path($path)
    {
        if($this->is_single_file($path)){
            return $this->delete_one_by_path($path);
        } else {
            return $this->delete_multiple_by_path($path);
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    private function is_single_file($path)
    {
        $path_array = parse_url($path);
        return (strpos($path_array['path'], '.') !== false);
    }

  #------------------------------------------------------------------------------------------------
    /**
     * @param $fileName - Example: s3://s3.footagesearch.com/archives/contributor/Default_2016-02-25.zip
     * @return mixed
     */
  function delete($fileName) {
      exec('aws s3 rm '.$fileName,$out,$ret);
      return $ret;
  }

  function download($bucket, $key, $localPath)
  {
      return $this->s3->getObject([
          'Bucket' => $bucket,
          'Key' => $key,
          'SaveAs' => $localPath
      ]);
  }

}
