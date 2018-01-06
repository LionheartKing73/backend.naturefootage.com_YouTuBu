<?php
/*$body='test <div style="color:#f55">mail</div>';
$res = mail('dmitriy.klovak@boldendeavours.com', 'My Subject', $body);
if ($res) {
    echo 'me=1<br>';
}
else {
    echo 'me=2<br>';
}

$body='test <div style="color:#f55">mail</div>';
$res = mail('dan@footagesearch.com', 'My Subject', $body);
if ($res) {
    echo 'den=1<br>';
}
else {
    echo 'den=2<br>';
}

$body='test <div style="color:#f55">mail</div>';
$res = mail('support@naturefootage.com', 'My Subject', $body);
if ($res) {
    echo 'support=1<br>';
}
else {
    echo 'support=2<br>';
}*/
//phpinfo();



/* TEST FUNCTIONAL FROM VIEW RESULT SOLR */
if($_REQUEST['key']=='natureDANfootage15'){
    /* START CLASS SOLR */
    require(dirname(__FILE__) . '/application/libraries/SolrPhpClient/Apache/Solr/Service.php');
    class SorlTestAdapter {
        private $solr_service;
        private $solr_documents;

        function __construct() {
            $this->solr_service = new Apache_Solr_Service('localhost', '8983', '/solr/');

            if(!$this->solr_service->ping()) {
                echo 'Solr service is not available';
                exit();
            }
        }
        public function ping(){return $this->solr_service->ping();}

        public function search($query, $offset = 0, $limit = 10, $sort = array(), $facet = array()) {
            if(empty($query)) return false;
            if(empty($offset)) $offset=0;
            if(empty($limit)) $limit=10;
            if(empty($sort)) $sort=array();
            if(empty($facet)) $facet=array();
            //Sort
            if($sort){
                $params['sort'] = implode(', ', $sort);
            }
            //Facet
            if($facet && is_array($facet)){
                $params['facet']='on';
                foreach($facet as $field){
                    //$query.='&facet.field='.$field;
                    $params['facet.field'][]=$field;
                }
            }
            try{
                $response = $this->solr_service->search($query, $offset, $limit);
            }
            catch (Exception $e){
                die($e->__toString());
            }

            if ($response){
                $result= $this->parse_response($response,$facet);
                return $result;
            }
            else{
                return false;
            }
        }

        private function parse_response($response,$facet=array()){
            if($response->getHttpStatus() == 200){
                if ($response->response->numFound > 0) {
                    foreach ($response->response->docs as $doc) {
                        $result['clips'][] = $doc;
                    }
                }
                $result['total'] = $response->response->numFound;
                //facet
                if(!empty($facet)){
                    foreach($facet as $field){
                        $result['facet'][$field]=$response->facet_counts->facet_fields->$field;
                    }
                }
                return $result;
            }
            else{
                return false;
            }
        }
    }
    /* END CLASS SOLR */

    $slr=new SorlTestAdapter();
    $res=$slr->search(@$_REQUEST['q'],@$_REQUEST['offset'], @$_REQUEST['limit'], @$_REQUEST['sort'], @$_REQUEST['facet']);
    echo '<h3 style="color:#55aa55;">Total Clips Found:   '.$res['total'].'</h3>';
    echo '<pre>'; var_dump($res['clips']); echo '</pre>';
}
 
