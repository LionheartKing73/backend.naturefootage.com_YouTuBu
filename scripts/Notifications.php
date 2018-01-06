<?php
    class Notifications {
        protected $db=NULL;
        protected $db_master=NULL;

        private $config;

        public function __construct(){

            $db = array();
            require(__DIR__ . '/../application/config/database.php');
            require(__DIR__ . '/../application/config/config.php');

            $this->config['dbhost'] = $db['default']['hostname'];
            $this->config['dbname'] = $db['default']['database'];
            $this->config['dbuser'] = $db['default']['username'];
            $this->config['dbpassword'] = $db['default']['password'];

            $this->config['master_dbhost'] = $db['master']['hostname'];
            $this->config['master_dbname'] = $db['master']['database'];
            $this->config['master_dbuser'] = $db['master']['username'];
            $this->config['master_dbpassword'] = $db['master']['password'];
            $this->_connectDb();
        }

        private function _connectDb() {
            try {
                if($this->db == NULL)
                $this->db = new PDO(
                    'mysql:host=' . $this->config['dbhost'] . ';dbname=' . $this->config['dbname'],
                    $this->config['dbuser'],
                    $this->config['dbpassword']
                );

                if($this->db_master == NULL)
                $this->db_master = new PDO(
                    'mysql:host=' . $this->config['master_dbhost'] . ';dbname=' . $this->config['master_dbname'],
                    $this->config['master_dbuser'],
                    $this->config['master_dbpassword']
                );

            } catch (PDOException $e) {
                exit('Database connection failed: ' . $e->getMessage());
            }
        }

        public static function api_request($params){
            $backend_url = ($_SERVER['USER']=='hdkr')?'http://dan.admin.uhdfootage.local/':'http://backend.naturefootage.com/';
            if(!empty($params) && isset($params['method']) && $params['method'] && $backend_url){
                $lang = 'en';
                $apiurl = $backend_url . $lang . '/fapi/' . $params['method'] . '/';
                if(!empty($params['query_params'])){
                    if(isset($params['query_params']['limit']) && $params['query_params']['limit'] === false){
                        return false;
                    }
                    $query_params = array();
                    foreach($params['query_params'] as $param => $value){
                        $query_params[] = $param . '/' . urlencode($value);
                    }
                    $apiurl .= '/' . implode('/', $query_params);
                }

                $post_params = array();
                if(!empty($params['post_params'])){
                    foreach($params['post_params'] as $param => $value){
                        if(is_array($value)){
                            foreach($value as $value_key => $value_item){
                                $post_params[] = $param . '[' . $value_key . ']=' . urlencode($value_item);
                            }
                        }
                        else{
                            $post_params[] = $param . '=' . urlencode($value);
                        }
                    }
                    $post_params = implode('&', $post_params);
                }
                $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiurl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, '60');
                if($post_params || $params['method'] == 'clips'){
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
                }
                else{
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                }
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);

                //DEBUG
                //curl_setopt($ch, CURLOPT_COOKIE, "XDEBUG_SESSION=PHPSTORM");


                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if($params['method'] == 'clips'){
                    /*echo '<pre>';
                    print_r($apiurl);echo '<br>POST: ';
                    print_r($post_params);echo '<br>';
                    print_r($result);
                    echo $http_status;
                    echo '</pre>';
                    exit();*/
                }
                return $http_status == 200 ? json_decode($result, true) : false;
            }
            else{
                return false;
            }
        }

        public static function debug($class,$function,$str,$date=false){
            $string = ($date) ? PHP_EOL . date( 'd.m.Y H:i:s' ) . ' >>> ' . microtime() . ' >> ' . $_SERVER[ 'REQUEST_TIME' ] . PHP_EOL : ''. PHP_EOL;
            $string .= '    -    __CLASS__: ' . $class . PHP_EOL;
            $string .= '    -    __FUNCTION__: ' . $function . PHP_EOL;
            $string .= '    -    __DEBUGmsg: ' . $str . PHP_EOL;
            $string .= '    ---------------------------------------'. PHP_EOL;
            file_put_contents( realpath("../___rest.api.log"), $string, FILE_APPEND );
        }

    }

    class Solr extends Notifications {
        public function __construct(){
            parent::__construct();
        }

        public static function addClipToIndex($id){
            $params = array('method' => 'add_clip_to_solr');
            $params['post_params']['clip_id'] = (int)$id;
            Notifications::api_request($params);
            //Notifications::debug(__CLASS__,__FUNCTION__,json_encode([$params,$ret]));
        }
    }

    class Email extends Notifications {
        public function __construct(){
            parent::__construct();
        }

        public static function orderDownload($order_id){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'download-email';
            $params['post_params']['order_id'] = (int)$order_id;
            Notifications::api_request($params);
        }

        public static function submissionFinished($code,$provider_id){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'submission-finished';
            $params['post_params']['code'] = $code;
            $params['post_params']['provider_id'] = $provider_id;
            Notifications::api_request($params);
            //Notifications::debug(__CLASS__,__FUNCTION__,json_encode([$params,$ret]));
        }

        public static function issetOfflineClips($provider_id){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'isset-offline-clips';
            $params['post_params']['provider_id'] = $provider_id;
            Notifications::api_request($params);
        }

        public static function previewsArchived($to,$name_data,$data){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'touser-archived-previews';
            $params['post_params']['data'] = $data;
            $params['post_params']['name_data'] = $name_data;
            $params['post_params']['to_email'] = $to;
            Notifications::api_request($params);
        }

        public static function preapproved($order_id){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'preapproved';
            $params['post_params']['order_id'] = (int)$order_id;
            $ret=Notifications::api_request($params);
            //Notifications::debug(__CLASS__,__FUNCTION__,json_encode([$params,$ret]));
        }

        public static function registerUser($id,$data){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'register-user';
            $params['post_params']['user_id'] = (int)$id;
            $params['post_params']['user_data'] = $data;
            return Notifications::api_request($params);
        }
        public static function customEmail($to='dmitriy.klovak@boldendeavours.com',$text='testEmail'){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = 'touser-clipbin-frontend';
            $params['post_params']['to_email'] = $to;
            $params['post_params']['name_data'] = 'frontend';
            $params['post_params']['data'] = array('text'=>$text);
            $ret=Notifications::api_request($params);
            //Notifications::debug(__CLASS__,__FUNCTION__,json_encode([$params,$ret]));
        }
        public static function custom($action,$to,$name_data,$data){
            $params = array('method' => 'send_email');
            $params['post_params']['action'] = $action;
            $params['post_params']['to_email'] = $to;
            $params['post_params']['name_data'] = $name_data;
            $params['post_params']['data'] = $data;
            $ret=Notifications::api_request($params);
            //Notifications::debug(__CLASS__,__FUNCTION__,json_encode([$params,$ret]));
        }
    }
?>