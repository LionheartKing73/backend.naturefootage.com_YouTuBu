<?php
require(dirname(__FILE__) . '/SolrPhpClient/Apache/Solr/Service.php');

class SorlSearchAdapter {
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

    public function search_clips($filter, $offset = 0, $limit = 100, $sort = array(), $facet = array(), $is_admin = false) {
        //echo '<pre>';
        //print_r($filter);
        //exit();

        $filter['words'] = str_replace("$@", "(", $filter['words']);
        $filter['words'] = str_replace("@$", ")", $filter['words']);
        $filter['words'] = str_replace(array('<','>'), "", $filter['words']);//str_replace(array('\'','"','<','>'), "", $filter['words']);
        $filter['words'] = addslashes($filter['words']);
        if($filter['words'] == ' ' || $filter['words'] == '+') unset($filter['words']);
        $or_filters = array();

        if(isset($filter['client_id'])){
            $or_filters['client_id'] = ' client_id:' . $filter['client_id'];
        }

        if(isset($filter['brand_id']) && $filter['brand_id']){
            if(is_array($filter['brand_id']))
                $or_filters['brand'] = ' (brand:' . implode(' OR brand:', $filter['brand_id']) . ')';
            else
                $or_filters['brand'] = ' brand:' . $filter['brand_id'];
        }


        $bool = array('OR');
        $queryStr = explode("+", $filter['words']);

        /*foreach($queryStr as $value){
            if(in_array($value, $bool)){
                $query = '';
                break;

            }else{
                $query = '{!lucene q.op=AND}';
            }
        }*/

        $query = '{!lucene q.op=AND}';

        if(isset($filter['all']) && $filter['all']){
            $query .= '*:*';
        }

        if($or_filters)
            $query .= ' (' . implode(' OR ', $or_filters) . ')';
        if(isset($filter['words']) && $filter['words']){
            //$words=(strpos($filter['words'], 0x20)) ? '"'.stripslashes($filter['words']).'"' : '*'.stripslashes($filter['words']).'* OR code:*'.stripslashes($filter['words']).'*';
            $filter['words']=urldecode($filter['words']);
            $words=(strpos($filter['words'], 0x20)) ? ''.stripslashes($filter['words']).'' : stripslashes($filter['words']);

            /*
            $orStr = explode("OR", $filter['words']);
            $newWords = '';

            foreach ($orStr as  $key => &$value){
                    $inOr = explode(" ", $value);
                    if (count($inOr) == 2){
                        $value = str_replace(' ', '', $value);
                        $value = $value."*";
                        $newWords .= $value." ";

                        if($key = (count($orStr) - 1)){
                            $newWords .= "OR";
                        }
                    }
                }
            */

            $words=preg_replace('/\+|\<|\>|\?|\#|\$|\-/i',' ',$words);

            //$words = preg_replace("/\w+^(OR|AND|NOT)/", '\0*', $words);

            //$words = preg_replace("/\w+/", '\0*', $words);

            /*$words_list = explode(" ", $words);

            $skip_words = array("OR", "AND", "NOT", " ");

            /*if(count($words_list)>1){
                foreach ($words_list as &$value){
                    if (!in_array($value, $skip_words)){
                        $value .= '*';
                    }
                }
            }

            $words = implode(" ", $words_list);*/

            $bracket=(isset($filter['active']))?'(':'';
            /*$words_list = explode(" ", $words);
            if(count($words_list==1))$words='"'.$words.'"';*/
            $query .= ' text:'.$bracket. $words;
        }
        if(isset($filter['cat_id']) && $filter['cat_id']){
            $query .= ' category_id:' . (int)$filter['cat_id'];
        }
        if(isset($filter['contributor_id']) && $filter['contributor_id']){
            $query .= ' client_id:' . (int)$filter['contributor_id'];
        }
        $query .=(isset($words) && isset($filter['active']))?') ':'';
        if(isset($filter['active']) && $filter['active']){
            if(is_array($filter['active'])){
                $query .= ' (active:' . implode(' OR active:', $filter['active']) . ')';
            }
            else{
                if(isset($words)){
                    $query .= ' active:' . (int)$filter['active'];
                }else{
                    $query .= ' active:' . (int)$filter['active'];
                }
            }
        }

        if(!isset($is_admin)){
            $is_admin = false;
        }

        if(!$is_admin){
            $query .= " AND NOT (active: 2)";
        }

        if(isset($filter['license']) && $filter['license']){
            if(is_array($filter['license']))
                $query .= ' (license:' . implode(' OR license:', $filter['license']) . ')';
            else
                $query .= ' license:' . $filter['license'];
        }
        if(isset($filter['price_level']) && $filter['price_level']){
            if(is_array($filter['price_level']))
                $query .= ' (price_level:' . implode(' OR price_level:', $filter['price_level']) . ')';
            else
                $query .= ' price_level:' . $filter['price_level'];
        }
        if(isset($filter['master_format']) && $filter['master_format']){
            if(is_array($filter['master_format']))
                $query .= ' (master_format:"' . implode('" OR master_format:"', $filter['master_format']) . '")';
            else
                $query .= ' master_format:"' . $filter['master_format'] . '"';
        }
        if(isset($filter['source_format']) && $filter['source_format']){
            if(is_array($filter['source_format']))
                $query .= ' (source_format:"' . implode('" OR source_format:"', $filter['source_format']) . '")';
            else
                $query .= ' source_format:"' . $filter['source_format'] . '"';
        }
        if(isset($filter['gallery']) && $filter['gallery']){
            if(is_array($filter['gallery']))
                $query = 'gallery_id:'.$filter['gallery'][0];//' (gallery_id:' .implode(' OR gallery_id:', $filter['gallery']) . ')';
            else
                $query = 'gallery_id:' . $filter['gallery'];
        }
        if(isset($filter['format_category']) && $filter['format_category']){
            if(is_array($filter['format_category']))
                $query .= ' (format_category:"' . implode('" OR format_category:"', $filter['format_category']) . '")';
            else
                $query .= ' format_category:"' . $filter['format_category'] . '"';
        }

        $keywords_sections = array(
            'shot_type',
            'subject_category',
            'actions',
            'appearance',
            'time',
            'location',
            'habitat',
            'concept'
        );
        if(empty($facet)) $facet=$keywords_sections;


        foreach($keywords_sections as $section){
            if(isset($filter[$section]) && $filter[$section]){
                if(is_array($filter[$section]))
                    $query .= ' (' . $section . ':"' . implode('" OR ' . $section . ':"', $filter[$section])
                        . '" OR text:"' . implode('" OR text:"', $filter[$section]) . '")';
                else
                    $query .= ' ' . $section . ':"' . $filter[$section]
                        . '" OR text:"' . $filter[$section] . '"';
            }
        }

        if(isset($filter['country']) && $filter['country']){
            if(is_array($filter['country']))
                $query .= ' (country:"' . implode('" OR country:"', $filter['country']) . '")';
            else
                $query .= ' country:"' . $filter['country'] . '"';
        }

        if(isset($filter['creation_date']) && $filter['creation_date'] && $filter['creation_date'][0]){
            $filter['creation_date'] = $filter['creation_date'][0];
            $creation_date_ranges = array(
                'past_week' => '[NOW/DAY-7DAY TO NOW]',
                'past_month' => '[NOW/DAY-1MONTHS TO NOW]',
                'past_year' => '[NOW/DAY-1YEAR TO NOW]',
                'over_one_year' => '[* TO NOW/DAY-1YEAR-1DAY]'
            );
            if(isset($creation_date_ranges[$filter['creation_date']])){
                $filter['creation_date'] = $creation_date_ranges[$filter['creation_date']];
                $query .= ' creation_date:' . $filter['creation_date'];
            }
        }
        elseif(isset($filter['fresh']) && $filter['fresh'] && $filter['fresh'][0]){
            $filter['fresh'] = '[NOW/DAY-1MONTHS TO NOW]';
            $query .= ' creation_date:' . $filter['fresh'];
        }

        if(isset($filter['hot']) && $filter['hot'] && $filter['hot'][0]){
            $query .= ' like_count:[2 TO *]';
        }

        if(isset($filter['duration']) && $filter['duration'] && $filter['duration'][0]){
            $filter['duration'] = $filter['duration'][0];
            $duration_range = explode('to', $filter['duration']);
            if(!$duration_range[0])
                $duration_range[0] = '*';
            if(!$duration_range[1])
                $duration_range[1] = '*';
            $filter['duration'] = '[' . implode(' TO ', $duration_range) . ']';
            $query .= ' duration:' . $filter['duration'];
        }

        if(isset($filter['collection_id']) && $filter['collection_id']){
            if(is_array($filter['collection_id']))
                $query .= ' (collection_id:' . implode(' OR collection_id:', $filter['collection_id']) . ')';
            else
                $query .= ' collection_id:' . $filter['collection_id'];
        }


        $params = array();

        //Sort
        if($sort){
            $params['sort'] = implode(', ', $sort);
        }else{
            $params['sort'] = 'code asc';
        }
        //Facet
        if($facet && is_array($facet)){
            $params['facet']='on';
            foreach($facet as $field){
                //$query.='&facet.field='.$field;
                $params['facet.field'][]=$field;
            }
        }
        //echo $query;
//        exit();
        /*$string="\n\t".date('Y-m-d H:i:s')."\n\t";
        $string.="\n\t DEBUG: \n\t query:".json_encode($query);
        $string.="\n\t DEBUG: \n\t sort:".json_encode($sort);
        file_put_contents( FCPATH . '___rest.api.log', $string, FILE_APPEND );*/

        try{
            $response = $this->solr_service->search($query, $offset, $limit, $params);
        }
        catch (Exception $e){
            die($query.'<br>'.$e->__toString());
        }



        if ($response){
            $result= $this->parse_response($response,$facet);
            $result['query']=$query;
            // Debug query and result in root dir file
            /*$string="\n\t".date('Y-m-d H:i:s')."\n\t";
            $string.="\n\t DEBUG: \n\t query:".json_encode($query)."\n\t result:".json_encode($result)."\n\t";
            file_put_contents( FCPATH . '___rest.api.log', $string, FILE_APPEND );*/

            return $result;
        }
        else{
            return false;
        }
    }

    public function deleteAll(){
        $this->solr_service->deleteByQuery('*:*');
        $this->solr_service->commit();
    }

    public function addToIndex($index_data, $optimize = true){
        $this->solr_documents = array();
        foreach($index_data as $fields) {

            $part = new Apache_Solr_Document();

            foreach ($fields as $key => $value) {
                if (is_array($value)){
                    foreach ($value as $data) {
                        $part->addField($key, $data);
                    }
                }
                else {
                    $part->addField($key, $value);
                }
            }

            $this->solr_documents[] = $part;
        }

        $this->solr_service->addDocuments($this->solr_documents);
        $this->solr_service->commit(false, false);
        // Long time operation
        if($optimize)
            $this->solr_service->optimize();
    }

    public function deleteById($id){
        $this->solr_service->deleteById($id);
        $this->solr_service->commit();
    }

    public function deleteByMultipleIds($ids){
        $this->solr_service->deleteByMultipleIds($ids);
        $this->solr_service->commit();
    }

    public function optimize(){
        $this->solr_service->optimize();
    }

    private function parse_response($response,$facet=array()){
        if($response->getHttpStatus() == 200){
            if ($response->response->numFound > 0) {
                foreach ($response->response->docs as $doc) {
                    $result['clips'][] = $doc->id;
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