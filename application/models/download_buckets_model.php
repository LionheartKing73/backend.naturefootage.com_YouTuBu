<?php

error_reporting(1);
require_once(__DIR__ . '/../../scripts/aws/aws-autoloader.php');

use Aws\S3\S3Client;

class Download_buckets_model extends CI_Model {

    function Download_buckets_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    #------------------------------------------------------------------------------------------------

    function get_bucket_list() {

        $store = array();
        require(__DIR__ . '/../config/store.php');

        if (!$this->s3Client) {
            $this->s3Client = S3Client::factory(array(
                        'key' => $store['s3']['key'],
                        'secret' => $store['s3']['secret']
            ));
        }

        $bucket = 's3.footagesearch.com';
        // Instantiate the client.
        // $s3 = S3Client::factory();

        try {
            $result = $this->s3Client->listObjects(array(
                // Bucket is required
                'Bucket' => $bucket,
                'Prefix' => 'demos/testing'
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
        //  echo '<pre>';
        // print_r($result['Contents']);
        // echo '</pre>';

        return $result['Contents'];


        // Use the high-level iterators (returns ALL of your objects).
//        try {
//            $objects = $s3->getIterator('ListObjects', array(
//                'Bucket' => $bucket
//            ));
//
//            echo "Keys retrieved!\n";
//            foreach ($objects as $object) {
//                echo $object['Key'] . "\n";
//            }
//        } catch (S3Exception $e) {
//            echo $e->getMessage() . "\n";
//        }
        // Use the plain API (returns ONLY up to 1000 of your objects).
    }

    function DownloadContent($keyName) {
        $store = array();
        require(__DIR__ . '/../config/store.php');

        if (!$this->s3Client) {
            $this->s3Client = S3Client::factory(array(
                'key' => $store['s3']['key'],
                'secret' => $store['s3']['secret']
            ));
        }

        $fileUrls = array();

        foreach ($keyName as $key => $row) {
            $varFileName = explode('/', $row);
            $bucket = 's3.footagesearch.com';

            // Get a command object from the client and pass in any options
            // available in the GetObject command (e.g. ResponseContentDisposition)
            $command = $this->s3Client->getCommand('GetObject', array(
                'Bucket' => $bucket,
                'Key' => $row,
                'ResponseContentDisposition' => 'attachment; filename="'.$varFileName[2].'"'
            ));

            // Create a signed URL from the command object that will last for
            // 10 minutes from the current time
            $fileUrls[] = $signedUrl = $command->createPresignedUrl('+10 minutes');
        }

        if($fileUrls){
//            $this->downloadFilesByUrls($fileUrls);
            foreach($fileUrls as $url){
                echo "<script>window.open('".$url."','_blank');</script>";
            }
        }
    }

    function force_download($data, $file_name, $contentType) {
        header("Content-type: {$contentType}");
        header("Content-Disposition: attachment; filename=\"{$file_name}\"");
        echo $data['Body'];
    }

    function downloadFilesByUrls($fileUrls){

        if(count($fileUrls) > 0){

            $filesScriptList = "var links = [ "; $i = 0;
            foreach($fileUrls as $file){
                $i++;

                if(count($fileUrls) > 1){
                    if($i == count($fileUrls)){
                        $filesScriptList .= "'".$file."'";
                    } else {
                        $filesScriptList .= "'".$file."', ";
                    }
                } else {
                    $filesScriptList .= "'".$file."'";
                }
            }
            $filesScriptList .= ' ]';

            $downloadFunction = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>';
            $downloadFunction .= "<script>
                ".$filesScriptList."

                function downloadAll(urls) {
                    var link = document.createElement('a');

                    link.setAttribute('download', null);
                    link.style.display = 'none';

                    document.body.appendChild(link);

                    for (var i = 0; i < urls.length; i++) {
                        link.setAttribute('href', urls[i]);
                        link.click();
                    }

                    document.body.removeChild(link);
                }

                downloadAll(window.links);
            </script>";
            $fileDownloadScript = "<html><body> ";
            $fileDownloadScript .= $downloadFunction;
            $fileDownloadScript .= "</body></html> ";

            echo $fileDownloadScript;
        }
    }

}
