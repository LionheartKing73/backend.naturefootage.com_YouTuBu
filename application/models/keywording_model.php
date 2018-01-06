<?php

class Keywording_model extends CI_Model {

    function Keywording_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

    function slice_video($clipID, $lang = 'en'){
        $keywordsFile = $this->keywording_model->getKeywordsFilePath($clipID);
        $keywordsData = json_decode(file_get_contents($keywordsFile));

        $timeline_points = array();
        $keyword = array();
        $i = 0;
        foreach($keywordsData->keywords as $keyword => $params){
            foreach($params->fragments as $fragment){
                $i++;
                // Заносим в массив фрагменты одиночных ключевых слов
                $keywords[$i] = array(
                    'in' => $fragment->in,
                    'out' => $fragment->out,
                    'keywords' => array($i => $keyword)
                );

                /*
                 * Заносим в массив все точки на таймлайне(начальные и конечные) для всех фрагментов
                 */
                $timeline_points[$fragment->in . '-in-' . $i] = array(
                    'fragment_id' => $i,
                    'in' => $fragment->in,
                    'keyword' => $keyword,
                );
                $timeline_points[$fragment->out . '-out-' . $i] = array(
                    'fragment_id' => $i,
                    'out' => $fragment->out,
                    'keyword' => $keyword,
                );
            }
        }

        // Сортируем точки таймлайна по их следованию на таймлайне
        ksort($timeline_points, SORT_NUMERIC);

        $intersections = array();
        $started = array();
        // Перебираем точки таймлайна и находим пересечения фрагментов
        foreach($timeline_points as $point){
            // Если точка начальная для какого-то фрагмента - отмечаем этот фрагмент как текущий на таймлайне
            if(isset($point['in'])){
                $started['fragment-' . $point['fragment_id']] = array(
                    'point' => $point['in'],
                    'keyword' => $point['keyword'],
                    'fragment_id' => $point['fragment_id']
                );
            }

            /*
             * Если точка конечная для какого-то фрагмента(фрагмент на таймлайне заканчивается) -
             * находим для этого фрагмента пересечения со всеми текущими фрагментами на таймлайне
             */
            if(isset($point['out']) && !empty($started)){
                $point_fragment_id = 'fragment-' . $point['fragment_id'];
                $this->get_intersections($started, $intersections, $point_fragment_id, $point['out']);
                // Удаляем фрагмент с массива текущих
                unset($started['fragment-' . $point['fragment_id']]);
            }
        }

        // Объединяем фрагменты одиночных ключевых слов с фрагментами пересечений
        $keywords = $keywords + $intersections;

        // Объединяем ключевые слова для фрагментов, в которых совпадают начальные и конечные точки
        $filtered_keywords = array();
        foreach($keywords as $keyword){
            if(!isset($filtered_keywords[$keyword['in'] . '-' . $keyword['out']])){
                $filtered_keywords[$keyword['in'] . '-' . $keyword['out']] = array();
                $filtered_keywords[$keyword['in'] . '-' . $keyword['out']]['in'] = $keyword['in'];
                $filtered_keywords[$keyword['in'] . '-' . $keyword['out']]['out'] = $keyword['out'];
                $filtered_keywords[$keyword['in'] . '-' . $keyword['out']]['keywords'] = $keyword['keywords'];
            }
            else{
                foreach($keyword['keywords'] as $keyword_key => $keyword_word){
                    if(!isset($filtered_keywords[$keyword['in'] . '-' . $keyword['out']]['keywords'][$keyword_key])){
                        $filtered_keywords[$keyword['in'] . '-' . $keyword['out']]['keywords'][$keyword_key] = $keyword_word;
                    }
                }
            }

        }

        foreach($filtered_keywords as $keyword){
            $keywords_str = implode(',', $keyword['keywords']);

            $row = $this->db->query(
                'SELECT id, clip_id FROM lib_keywording_fragment
                WHERE parent_clip_id = ' . intval($clipID) . ' AND `in` = \'' . $keyword['in'] . '\' AND `out` = \'' . $keyword['out'] . '\'')->result_array();

            if (count($row)) {
                $this->db_master->where('id', $row[0]['id']);
                $this->db_master->update('lib_keywording_fragment', array('keywords' => $keywords_str));
                if($row[0]['clip_id']){
                    $this->db_master->update('lib_clips_content', array('keywords' => $keywords_str), array('clip_id' => $row[0]['clip_id'], 'lang' => $lang));
                }
            }
            else{
                $keyword['parent_clip_id'] = $clipID;
                $keyword['keywords'] = $keywords_str;
                $keyword['fps'] = $keywordsData->fps;
                $this->db_master->insert('lib_keywording_fragment', $keyword);
            }
        }
        $this->generate_keywording_fragments($clipID, $lang);
    }

    function get_intersections($started, &$intersections, $point_fragment_id, $out){
        $started_copy = $started;
        foreach($started as $fragment_id => $params){
            if($fragment_id == $point_fragment_id){
                continue;
            }
            $in = false;
            $first_started_key = key($started_copy);
            $max_in_point = $started_copy[$first_started_key]['point'];
            $max_fragment_id = $first_started_key;
            $started_keywords = array();
            $intersection_parts = array();
            foreach($started_copy as $fragment_id_copy => $params_copy){
                if($params_copy['point'] > $max_in_point){
                    $max_fragment_id = $fragment_id_copy;
                }
                $started_keywords[$params_copy['fragment_id']] = $params_copy['keyword'];
                $intersection_parts[] = $params_copy['fragment_id'];

            }
            if($max_fragment_id){
                $in = $started_copy[$max_fragment_id]['point'];
            }
            unset($started_copy[$fragment_id]);
            if($in !== false && $out && count($started_keywords) > 1){
                if(!isset($intersections[implode('-', $intersection_parts)])){
                    $intersections[implode('-', $intersection_parts)] = array(
                        'in' => $in,
                        'out' => $out,
                        'keywords' => $started_keywords
                    );
                }
            }
            $this->get_intersections($started_copy, $intersections, $point_fragment_id, $out);
            $started_copy = $started;
        }
    }

    function get_fragments_count($clipID){

        $total = $this->db->query(
            'SELECT COUNT(id) total, status
            FROM lib_keywording_fragment
			WHERE parent_clip_id = ' . (int)$clipID . ' GROUP BY status')->result_array();

        return $total;
    }

    function get_current_processing($clipID){
        $query = $this->db->get_where('lib_keywording_fragment', array('parent_clip_id' => (int)$clipID, 'status' => 1));
        $row = $query->result_array();
        return $row[0];
    }

    function getKeywordsFilePath($clipID) {

        $docRoot = $_SERVER["DOCUMENT_ROOT"];
        $keywordsPath = $docRoot."/data/keywords/";
        $keywordsFile = $keywordsPath."/".$clipID.".keywords";

        return $keywordsFile;
    }

    function generate_keywording_fragments($clip_id, $lang = 'en') {
//        $command = 'php ' . $_SERVER['DOCUMENT_ROOT']
//            . '/scripts/keywording_fragments.php ' . $clip_id . ' > /dev/null &';
        $command = 'php ' . $_SERVER['DOCUMENT_ROOT']
            . '/scripts/keywording_fragments.php ' . $clip_id . ' ' . $lang . ' > /dev/null &';
        system($command);
    }

    function getFileInfo($clipID){
        $row = $this->db->query('SELECT c.duration, c.frame_rate, r.resource FROM lib_clips c
            INNER JOIN lib_clips_res r ON c.id = r.clip_id AND r.type = 2
            WHERE c.id = ' . intval($clipID))->result_array();
        return $row[0];
    }

}