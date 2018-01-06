<?php
require_once(FCPATH . '/scripts/aws/aws-autoloader.php');

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class Aws3_sqs_delete_resources_model extends CI_Model
{
    const JOBS_PUT_DELAY = 0;
    const JOBS_GET_DELAY = 0;
    private $queue_url = 'https://sqs.us-east-1.amazonaws.com/118796186120/s3_resources_for_removal';
    private $client;
    private $store;

    public function __construct()
    {
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $this->store = $store;
        $this->client = SqsClient::factory([
            'region' => 'us-east-1',
            'version' => '2012-11-05',
            'credentials' => [
                'key'    => $this->store['s3']['key'],
                'secret' => $this->store['s3']['secret']
            ]
        ]);
        try {
            $this->client->setQueueAttributes(array(
                'Attributes' => [
                    'ReceiveMessageWaitTimeSeconds' => self::JOBS_GET_DELAY
                ],
                'QueueUrl' => $this->queue_url,
            ));
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }

    public function get_job()
    {
        try {
            $result = $this->client->receiveMessage(array(
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => 1,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $this->queue_url, // REQUIRED
                'WaitTimeSeconds' => 0,
            ));
            if (count($result->get('Messages')) > 0) {
                $messages = $result->get('Messages');
                $this->client->deleteMessage([
                    'QueueUrl' => $this->queue_url, // REQUIRED
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'] // REQUIRED
                ]);
                return $messages[0]['Body']; // resources url to delete
            }
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
        return false;
    }

    /**
     * @param array $job
     */
    public function put_job($job)
    {
        $job['QueueUrl'] = $this->queue_url;
        $job['DelaySeconds'] = self::JOBS_PUT_DELAY;
        try {
            $result = $this->client->sendMessage($job);
            return $result->hasKey('MessageId');
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
        return false;
    }


}