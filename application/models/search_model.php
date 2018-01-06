<?php

class Search_model extends CI_Model {

    var $langs;
    var $filter_map;
    var $page;
    var $perpage;
    var $displaying;
    var $all;
    var $licenses;

    #------------------------------------------------------------------------------------------------

    function Search_model() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('images_model');
        $this->load->model('clips_model');
        $this->load->model('cats_model');
        $this->load->model('locations_model');
        //$this->load->model('clipsettings_model');
        //$this->load->model('timeofday_model');
        //$this->load->model('shottypes_model');
        //$this->load->model('clearance_model');
        $this->load->model('bin_model');
        $this->load->model('cart_model');

        $this->settings = $this->api->settings();
        $this->licenses = array(1 => 'license_rf', 2 => 'license_rm', 3 => 'license_rr');
    }

    #------------------------------------------------------------------------------------------------

    function clear_uri() {
        if ($this->filter_map['words']) {
            $uri = '/words/' . $this->filter_map['words'];
        }
        /*
          elseif ($this->filter_map['collection']) {
          $uri = '/collection/' . $this->filter_map['collection'];
          }
          elseif ($this->filter_map['cat']) {
          $uri = '/cat/' . $this->filter_map['cat'];
          }
         */ else {
            $uri = $this->config->item('url_suffix');
        }


        return $uri ? $this->langs . '/search' . $uri : '';
    }

    #------------------------------------------------------------------------------------------------

    function title() {
        if ($this->filter_map['words']) {
            $title = $this->filter_map['words'];
        } elseif ($this->filter_map['collection']) {
            $id = $this->cats_model->id_from_uri($this->filter_map['collection']);
            $data = $this->db->query('SELECT title FROM lib_cats_content WHERE cat_id = ? AND lang = ?', array($id, $this->langs))->result_array();
            $title = 'collection &lsquo;' . htmlspecialchars($data[0]['title']) . '&rsquo;';
        } elseif ($this->filter_map['categories']) {
            $id = $this->cats_model->id_from_uri($this->filter_map['categories']);
            $data = $this->db->query('SELECT title FROM lib_cats_content WHERE cat_id = ? AND lang = ?', array($id, $this->langs))->result_array();
            $title = 'category &lsquo;' . htmlspecialchars($data[0]['title']) . '&rsquo;';
        }
        return $title;
    }

    #------------------------------------------------------------------------------------------------

    function make_phrase($words) {
        if (preg_match('/^[\'\"].+[\'\"]$/', $words)) {
            $words = '"' . addslashes(trim($words, '"\'')) . '"';
        } else {
            $words = addslashes($words);
        }
        return $words;
    }

    #------------------------------------------------------------------------------------------------

    function prepare_filter() {
        $string_params = array('words', 'date', 'collection', 'categories');
        foreach ($this->filter_map as $key => $value) {
            if (!in_array($key, $string_params)) {
                $this->filter_map[$key] = urldecode($this->filter_map[$key]);
            }
        }

        $filter = $this->filter_map;

        if ($filter['words']) {
            $this->session->set_userdata('search_phrase', $filter['words']);

            if (preg_match('/^[\'\"].+[\'\"]$/', $filter['words'])) {
                $filter['words'] = '"' . addslashes(trim($filter['words'], '"\'')) . '"';
            } else {
                $filter['words'] = addslashes(trim($filter['words']));
            }
        } else {
            $this->session->unset_userdata('search_phrase');
        }

        if ($filter['exact']) {
            $filter['words'] = '"' . trim($filter['words'], ' \'"') . '"';
        }

        return $filter;
    }

    #------------------------------------------------------------------------------------------------

    function sql_filter() {
        $filter = $this->prepare_filter();

        $sql_filter = array();

        $sql_filter['words'] = $filter['words'] ?
                " AND (c.code='" . trim($filter['words'], ' "') .
                "' OR MATCH(lc.title, lc.description, lc.keywords) AGAINST('"
                . $filter['words'] . "' IN BOOLEAN MODE) > 0) " : '';

        $simple_filters = array(
            'rights' => 'license',
            'setting' => 'setting_id',
            'lighting' => 'lighting',
            'clearance' => 'clearance_id',
            'format' => 'of_id',
            //'ar' => 'aspect',
            'shot_on' => 'nf_id',
            'shot_by' => 'client_id'
        );

        $sql_filter['simple_filters'] = ' ';

        foreach ($simple_filters as $fltr_name => $column) {
            if (isset($filter[$fltr_name])) {
                $sql_filter['simple_filters'] .= ' AND c.' . $column . ' = \'' . $filter[$fltr_name] . '\' ';
            }
        }

        $sql_filter['location'] = $filter['location'] ?
                ' AND c.location_id IN (' . $this->locations_model->get_filter($filter['location']) . ') ' : '';

        $sql_filter['timeofday'] = $filter['timeofday'] ?
                ' INNER JOIN lib_clips_timeofday ct ON ct.clip_id = c.id AND ct.tid = '
                . intval($filter['timeofday']) . ' ' : '';

        $sql_filter['shot_type'] = $filter['shot_type'] ?
                ' INNER JOIN lib_clips_shottype cst ON cst.clip_id = c.id AND cst.shottype_id = '
                . intval($filter['shot_type']) . ' ' : '';

        $sql_filter['collection'] = $filter['collection'] ?
                ' INNER JOIN lib_clips_cats cc1 ON cc1.clip_id = c.id AND cc1.cat_id = ' . $this->cats_model->id_from_uri($filter['collection'])
                . ' ' : '';

        $sql_filter['categories'] = $filter['categories'] ?
                ' INNER JOIN lib_clips_cats cc ON cc.clip_id = c.id AND cc.cat_id = ' . $this->cats_model->id_from_uri($filter['categories'])
                . ' ' : '';

        return $sql_filter;
    }

    #------------------------------------------------------------------------------------------------

    function get_results() {
        $sql_filter = $this->sql_filter();

        $sql = 'SELECT DISTINCT c.*, lc.title, lc.description, lc.keywords
      FROM lib_clips c
      LEFT JOIN lib_clips_content lc ON lc.clip_id = c.id AND lc.lang = ' . $this->db->escape($this->langs)
                . $sql_filter['timeofday']
                . $sql_filter['collection']
                . $sql_filter['categories']
                . $sql_filter['shot_type']
                . ' WHERE c.active = 1 '
                . $sql_filter['words']
                . $sql_filter['rights']
                . $sql_filter['simple_filters']
                . $sql_filter['location'];

        if ($this->filter['words']) {
            $sql .= ' ORDER BY MATCH(lc.title, lc.description, lc.keywords) AGAINST('
                    . $this->db->escape($this->filter['words']) . ' IN BOOLEAN MODE) DESC ';
        }

        return $this->get_query_results($sql);
    }

    #------------------------------------------------------------------------------------------------

    function get_query_results($sql) {
        $sql_c = str_replace('c.*, lc.title, lc.description, lc.keywords, lu.login folder', 'c.id', $sql);
        $query = $this->db->query($sql_c);
        $this->all = $query->num_rows();
        $query->free_result();

        $sql .= $this->get_limit();
        $query = $this->db->query($sql);
        $rows = $query->result_array();

        $this->displaying = count($rows);

        foreach ($rows as &$row) {
            $row['rights'] = $this->lang->line($this->licenses[$row['license']]);

            if ($row['type'] == 2) {
                //$row['thumb'] = $this->clips_model->get_clip_path($row,'thumb');
                //$row['url'] = $this->langs.'/clips/'.$this->clips_model->make_uri($row['title'], $row['id']).$this->config->item('url_suffix');
                //$row['res'] = $this->clips_model->get_clip_res($row['id'], 2);
                $row['thumb'] = $this->clips_model->get_clip_path($row, 'thumb');
                $row['motion_thumb'] = $this->clips_model->get_clip_path($row, 'motion_thumb');
                $row['url'] = '/clips/' . $this->clips_model->make_uri($row) . $this->config->item('url_suffix');
                $row['res'] = $this->clips_model->get_clip_res($row['id'], 2);
                /* $shotreel_id = $this->clips_model->get_shotreel_id($row['id']);
                  if ($shotreel_id) {
                  $row['shotreel'] = $this->langs . '/search/shotreel/' . $shotreel_id . $this->config->item('url_suffix');
                  } */

                if ($this->bin_model->check_exist(2, $row['id'])) {
                    $row['in_bin'] = true;
                }
                if ($this->cart_model->check_exist(2, $row['id'])) {
                    $row['in_cart'] = true;
                }
            } elseif ($row['type'] == 1) {
                $row['thumb'] = $this->images_model->get_image_path($row, 1);
                $row['url'] = $this->langs . '/images/' . $row['id'] . $this->config->item('url_suffix');
                $row['res'] = $this->images_model->get_image_resources($row['id']);
            }
        }

        $results = $this->load->view('main/ext/results', array('results' => $rows, 'lang' => $this->langs), true);
        return $results;
    }

    #------------------------------------------------------------------------------------------------

    function get_limit() {
        return ' LIMIT ' . intval($this->page * $this->perpage) . ',' . $this->perpage;
    }

    #------------------------------------------------------------------------------------------------

    function save_search_stat($phrase, $lang = 'en') {
        if (!$phrase) {
            return;
        }

        $this->filter_map = array('words' => $phrase);
        $this->langs = $lang;
        $this->prepare_filter();

        $words = $this->filter_map['words'];
        if ($words) {
            $query = $this->db->get_where('lib_search', array('lang' => $this->langs, 'phrase' => $words));
            $rows = $query->result_array();

            if ($query->num_rows()) {
                if ($rows[0]['type'] != 2) {
                    $data['times'] = $rows[0]['times'] + 1;

                    $this->db_master->where('id', $rows[0]['id']);
                    $this->db_master->update('lib_search', $data);
                }
            } else {
                $data['phrase'] = $words;
                $data['lang'] = $this->langs;
                $this->db_master->insert('lib_search', $data);
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_filters() {
        $filters = array(
            'exact' => array(
                'title' => 'Relevance',
                'options' => array(
                    array('name' => 'Any of these words'),
                    array('value' => 1, 'name' => 'Exact phrase')
                )
            ),
            'rights' => array(
                'title' => 'Rights',
                'options' => array(
                    array('name' => 'Any rights')
                )
            ),
            'location' => array(
                'title' => 'Location',
                'options' => array(
                    array('name' => 'Any location')
                )
            ),
            /* 'setting'=>array(
              'title'=>'Setting',
              'options'=>array(
              array('name'=>'Any setting')
              )
              ),

              'lighting'=>array(
              'title'=>'Lighting conditions',
              'options'=>array(
              array('name'=>'Any lighting')
              )
              ),

              'timeofday'=>array(
              'title'=>'Time of day',
              'options'=>array(
              array('name'=>'Any time')
              )
              ),

              'shot_type'=>array(
              'title'=>'Type of shot',
              'options'=>array(
              array('name'=>'Any type')
              )
              ),

              'clearance'=>array(
              'title'=>'Clearance Status',
              'options'=>array(
              array('name'=>'Any status')
              )
              ), */
            'format' => array(
                'title' => 'Format',
                'options' => array(
                    array('name' => 'Any format')
                )
            ),
            /*'ar' => array(
                'title' => 'Aspect ratio',
                'options' => array(
                    array('name' => 'Any aspect ratio')
                )
            ),*/
            /* 'shot_on'=>array(
              'title'=>'Shot on',
              'options'=>array(
              array('name'=>'Any camera')
              )
              ),

              'shot_by'=>array(
              'title'=>'Shot by',
              'options'=>array(
              array('name'=>'Any director')
              )
              ), */
            'collection' => array(
                'title' => 'Collection',
                'options' => array(
                    array('name' => 'Any collection')
                )
            ),
            'categories' => array(
                'title' => 'Category',
                'options' => array(
                    array('name' => 'Any category')
                )
            )
        );

        $rights = $this->db->query('SELECT DISTINCT license FROM lib_clips WHERE license > 0 ORDER BY license')
                ->result_array();
        if (count($rights)) {
            $rights_title = array(1 => 'Royalty Free', 2 => 'Rights Managed', 3 => 'Rights Ready');
            foreach ($rights as $row) {
                $filters['rights']['options'][] = array(
                    'value' => $row['license'],
                    'name' => $rights_title[$row['license']]
                );
            }
        }

        /* $rights_title = array(1=>'Royalty Free', 2=>'Rights Managed', 3=>'Rights Ready');
          $filters['rights']['options'][] = array(
          'value'=>1,
          'name'=>$rights_title[1]
          );
          $filters['rights']['options'][] = array(
          'value'=>2,
          'name'=>$rights_title[2]
          ); */

        $locations = $this->locations_model->get_all($this->langs, true);

        foreach ($locations as $country_id => $country) {
            $filters['location']['options'][] = array(
                'value' => $country_id,
                'name' => $country['name']
            );
            if ($country['locations']) {
                foreach ($country['locations'] as $location_id => $location) {
                    $filters['location']['options'][] = array(
                        'value' => $location_id,
                        'name' => $location['name'],
                        'level' => 1
                    );
                }
            }
        }

        /*
          $clipsettings = $this->clipsettings_model->get_list($this->langs, true);
          if ($clipsettings) {
          foreach ($clipsettings as $row) {
          $filters['setting']['options'][] = array(
          'value'=>$row['id'],
          'name'=>$row['name']
          );
          }
          }

          $lighting = $this->db->query('SELECT DISTINCT lighting FROM lib_clips WHERE active = 1 ORDER BY lighting')
          ->result_array();
          if (count($lighting)) {
          $lighting_title = array(1=>'Exterior', 2=>'Interior');
          foreach ($lighting as $row) {
          $filters['lighting']['options'][] = array(
          'value'=>$row['lighting'],
          'name'=>$lighting_title[$row['lighting']]
          );
          }
          }

          $timeofday = $this->timeofday_model->get_list($this->langs, true);
          if ($timeofday) {
          foreach ($timeofday as $row) {
          $option = array('value'=>$row['tid'], 'name'=>$row['name']);
          $filters['timeofday']['options'][] = $option;
          }
          }

          $shottypes = $this->shottypes_model->get_list($this->langs, true);
          if ($shottypes) {
          foreach ($shottypes as $row) {
          $filters['shot_type']['options'][] = array(
          'value'=>$row['id'],
          'name'=>$row['name']
          );
          }
          }

          $clearance = $this->clearance_model->get_list($this->langs, true);
          if ($clearance) {
          foreach ($clearance as $row) {
          $filters['clearance']['options'][] = array(
          'value'=>$row['id'],
          'name'=>$row['name']
          );
          }
          } */

        $formats = $this->db->query(
                        'SELECT DISTINCT c.of_id id, f.title
      FROM lib_clips c
      INNER JOIN lib_formats f ON f.id = c.of_id
      WHERE c.active = 1'
                )->result_array();
        if ($formats) {
            foreach ($formats as $row) {
                $filters['format']['options'][] = array(
                    'value' => $row['id'],
                    'name' => $row['title']
                );
            }
        }

        /*$ar = $this->db->query('SELECT DISTINCT aspect FROM lib_clips WHERE active = 1 ORDER BY aspect')->result_array();
        if ($ar) {
            foreach ($ar as $row) {
                $filters['ar']['options'][] = array(
                    'value' => $row['aspect'],
                    'name' => $row['aspect']
                );
            }
        }*/

        /* $shot_on = $this->db->query(
          'SELECT DISTINCT c.nf_id id, f.title
          FROM lib_clips c
          INNER JOIN lib_formats f ON f.id = c.nf_id
          WHERE c.active = 1'
          )->result_array();
          if ($shot_on) {
          foreach ($shot_on as $row) {
          $filters['shot_on']['options'][] = array(
          'value'=>$row['id'],
          'name'=>$row['title']
          );
          }
          }

          $shot_by = $this->db->query(
          'SELECT DISTINCT c.client_id id, u.fname, u.lname, u.company
          FROM lib_clips c
          INNER JOIN lib_users u ON u.id = c.client_id
          WHERE c.active = 1'
          )->result_array();
          if ($shot_by) {
          foreach ($shot_by as $row) {
          $filters['shot_by']['options'][] = array(
          'value'=>$row['id'],
          'name'=>trim(implode(', ', array($row['fname'] . ' ' . $row['lname'], $row['company'])), ' ,')
          );
          }
          } */

        $collections = $this->db->query(
                        'SELECT DISTINCT cc.cat_id id, con.title
      FROM lib_clips_cats cc
      INNER JOIN lib_clips c ON c.id = cc.clip_id AND c.active = 1
      INNER JOIN lib_cats cat ON cat.id = cc.cat_id AND cat.type = 1
      INNER JOIN lib_cats_content con ON con.cat_id = cc.cat_id AND con.lang = ?
      ORDER BY cat.ord', $this->langs
                )->result_array();
        if ($collections) {
            foreach ($collections as $row) {
                $filters['collection']['options'][] = array(
                    'value' => $this->cats_model->make_uri($row['title'], $row['id']),
                    'name' => $row['title']
                );
            }
        }

        $cats = $this->db->query(
                        'SELECT DISTINCT cc.cat_id id, con.title
      FROM lib_clips_cats cc
      INNER JOIN lib_clips c ON c.id = cc.clip_id AND c.active = 1
      INNER JOIN lib_cats cat ON cat.id = cc.cat_id AND cat.type = 0
      INNER JOIN lib_cats_content con ON con.cat_id = cc.cat_id AND con.lang = ?
      ORDER BY cat.ord', $this->langs
                )->result_array();
        if ($cats) {
            foreach ($cats as $row) {
                $filters['categories']['options'][] = array(
                    'value' => $this->cats_model->make_uri($row['title'], $row['id']),
                    'name' => $row['title']
                );
            }
        }

        /*         * ********************************************************************************************* */

        foreach ($filters as $filter_name => &$filter) {
            $f_map = $this->filter_map;
            $options = array();
            foreach ($filter['options'] as $option) {
                if (isset($option['value'])) {
                    if (!isset($this->filter_map[$filter_name]) ||
                            (isset($this->filter_map[$filter_name]) && ($this->filter_map[$filter_name] != $option['value']))) {
                        $f_map[$filter_name] = $option['value'];
                        $option['uri'] = $this->assoc_to_uri($f_map);
                    }
                } else {
                    if (isset($this->filter_map[$filter_name])) {
                        $f_all_map = $this->filter_map;
                        unset($f_all_map[$filter_name]);
                        $option['uri'] = $this->assoc_to_uri($f_all_map);
                        unset($f_all_map);
                    }
                }
                if ($option['uri']) {
                    $option['uri'] = '/' . $option['uri'];
                }

                if ($option['uri']) {
                    $options[] = $option;
                } else {
                    array_unshift($options, $option);
                }
            }

            $filter['options'] = $options;
        }

        return $filters;
    }

    function assoc_to_uri($array) {
        $temp = array();
        foreach ((array) $array as $key => $val) {
            $temp[] = $key;
            $temp[] = urlencode($val);
        }

        return implode('/', $temp);
    }

    function get_search_filters($provider_id) {
        $this->db->select('id value, code label');
        $query = $this->db->get('lib_licensing');
        $license_types = $query->result_array();

//        $this->db->select('id value, description label');
//        $query = $this->db->get('lib_pricing_category_type');
//        $pricing_categories = $query->result_array();
        //$this->db->select('name value, name label');
//        $this->db->select('search_term value, search_term label', 'orderid');
//        $this->db->where('search_term !=', '');
//        $this->db->order_by("orderid", "asc");
//        $query = $this->db->get('lib_collections');
//        $collections = $query->result_array();

        $collections = array('Land', 'Ocean', 'Adventure');

        $this->db->select('id value, id label');
        $this->db->where('client_id =', $provider_id);
        $query = $this->db->get('lib_clips');
        $clips_id = $query->result_array();
//        $this->db->select('master_format value, master_format label');
//        $this->db->distinct();
//        $this->db->where('master_format !=', '');
//        if($provider_id)
//            $this->db->where('client_id', $provider_id);
//        $query = $this->db->get('lib_clips');
//        $master_formats = $query->result_array();

        $this->db->select('source_format value, source_format label');
        $this->db->distinct();
        $this->db->where('source_format !=', '');
        /* if($provider_id)
          $this->db->where('client_id', $provider_id); */
        $query = $this->db->get('lib_clips');
        $source_formats = $query->result_array();

       /* $this->db->select('country value, country label');
        $this->db->distinct();
        $this->db->where('country !=', '');*/
        /* if($provider_id)
          $this->db->where('client_id', $provider_id); 
        $query = $this->db->get('lib_clips');
        $countries = $query->result_array();*/
        $keywords = $this->get_clip_keywords('', '', $clips_id);
//        echo "<pre>";
//        print_r($keywords);
//        echo "</pre>";
        $filters = array(
            'category' => array(
                'display' => 0
            ),
            'gallery' => array(
                'display' => 0
            ),
            'fresh' => array(
                'display' => 0
            ),
            'hot' => array(
                'display' => 0
            ),
            'category' => array(
                'label' => 'Collection',
                'type' => 'ckeckbox',
                'options' => array(
                    array('value' => 'Land', 'label' => 'Nature & Wildlife'),
                    array('value' => 'Ocean', 'label' => 'Ocean & Underwater'),
                    array('value' => 'Adventure', 'label' => 'People & Adventure')
                ),
                'additional' => 0,
                'display' => 1
            ),
            'brand' => array(
                'label' => 'Clips and Edited Videos',
                'type' => 'ckeckbox',
                'options' => array(
                    array('value' => 1, 'label' => 'Single Clips'),
                    array('value' => 2, 'label' => 'Edited Videos')
                ),
                'additional' => 0,
                'display' => 1
            ),
//            'clips_id' => array(
//                'label' => 'Clips Id',
//                'type' => 'hidden',
//                'options' => $clips_id,
//                'additional' => 0,
//                'display' => 1
//            ),
            'license' => array(
                'label' => 'License type',
                'type' => 'ckeckbox',
                'options' => $license_types,
                'additional' => 0,
                'display' => 1
            ),
            'price_level' => array(
                'label' => 'Price Level',
                'type' => 'ckeckbox',
                'options' => array(
                    array('value' => 1, 'label' => 'Budget'),
                    array('value' => 2, 'label' => 'Standard'),
                    array('value' => 3, 'label' => 'Premium'),
                    array('value' => 4, 'label' => 'Gold')
                ),
                'additional' => 0,
                'display' => 1
            ),
			
//            'master_format' => array(
//                'label' => 'Format Category',
//                'type' => 'ckeckbox',
//                'options' => $master_formats,
//                'display' => 1
//            ),
            'format_category' => $this->getFormatCategoryFilter(),
            'source_format' => array(
                'label' => 'Source Format',
                'type' => 'ckeckbox',
                'options' => $source_formats,
                'additional' => 1,
                'display' => 1
            ),
            'shot_type' => array(
                'label' => 'Shot Type',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['shot_type'],
                'additional' => 0,
                'display' => 1
            ),
            'subject_category' => array(
                'label' => 'Subject Category',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['subject_category'],
                'additional' => 0,
                'display' => 1
            ),
            'actions' => array(
                'label' => 'Action',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['actions'],
                'additional' => 0,
                'display' => 1
            ),
            'appearance' => array(
                'label' => 'Appearance',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['appearance'],
                'additional' => 0,
                'display' => 1
            ),
            'time' => array(
                'label' => 'Time',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['time'],
                'additional' => 0,
                'display' => 1
            ),
            'location' => array(
                'label' => 'Location',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['location'],
                'additional' => 0,
                'display' => 1
            ),
            'habitat' => array(
                'label' => 'Habitat',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['habitat'],
                'additional' => 0,
                'display' => 1
            ),
            'concept' => array(
                'label' => 'Concept',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $keywords['concept'],
                'additional' => 0,
                'display' => 1
            ),
            'country' => array(
                'label' => 'Country',
                'type' => 'ckeckbox',
                'collapsed' => 1,
                'options' => $countries,
                'additional' => 1,
                'display' => 1
            ),
            'creation_date' => array(
                'label' => 'Date Added',
                'type' => 'select',
                'collapsed' => 1,
                'options' => array(
                    array('value' => 'past_week', 'label' => 'Past Week'),
                    array('value' => 'past_month', 'label' => 'Past Month'),
                    array('value' => 'past_year', 'label' => 'Past Year'),
                    array('value' => 'over_one_year', 'label' => 'Over One Year'),
                ),
                'additional' => 0,
                'display' => 1
            ),
            'duration' => array(
                'label' => 'Duration',
                'type' => 'select',
                'collapsed' => 1,
                'options' => array(
                    array('value' => '1to10', 'label' => '>10 Seconds'),
                    array('value' => '1to20', 'label' => '>20 Seconds'),
                    array('value' => '1to30', 'label' => '>30 Seconds'),
                    array('value' => '1to60', 'label' => '>60 Seconds'),
                    array('value' => '61to', 'label' => '61+ Seconds'),
                ),
                'additional' => 0,
                'display' => 1
            )
        );
//        echo "<pre>";
//        print_r($filters);
//        echo "</pre>";
        return $filters;
    }

    /**
     * @param int $collectionId
     * @param $onlyKeywords - bool
     * @return array
     */
    function get_clip_keywords($collectionId = 1, $onlyKeywords = false, $clip_ids = null) {
        foreach ($clip_ids as $key => $clip) {
            $id[] = "'" . $clip['value'] . "'";
        }

        $search_filter_clip_id = (count($id)>1)?implode(",", $id):$id[0];

        /* $this->db->select('lib_keywords.keyword value, lib_keywords.keyword label, lib_keywords.section');
          $this->db->distinct();
          $this->db->join('lib_clip_keywords', 'lib_keywords.id = lib_clip_keywords.keyword_id', 'inner');
          if($section != '')
          $this->db->where('lib_keywords.section', $section);
          $query = $this->db->get('lib_keywords'); */
        /* $query = $this->db->query('SELECT DISTINCT lk.keyword as value, lk.keyword as label, lk.section FROM lib_keywords as lk
          INNER JOIN lib_clip_keywords AS lck ON lk.id=lck.keyword_id
          WHERE (SELECT json FROM lib_cliplog_metadata_templates WHERE id=2 LIMIT 1)
          LIKE CONCAT(\'%"keywords"%"\', lk.id, \'"%\') AND lk.old !=1
          '); */
//        $query = $this->db->query('SELECT DISTINCT lk.keyword as value, lk.keyword as label, lk.section FROM lib_keywords as lk
//
//            JOIN lib_collections AS c ON c.name=lk.collection AND c.id='.$collectionId.'
//        ');

        $query = $this->db->query("SELECT  DISTINCT lib_keywords.id, lib_keywords.keyword as value, lib_keywords.keyword as label, lib_keywords.section as section, lib_keywords.collection
            FROM lib_keywords
            LEFT JOIN lib_cliplog_logging_keywords ON lib_cliplog_logging_keywords.keywordId = lib_keywords.id
            WHERE 1 AND  lib_keywords.collection = 'Nature Footage'
            ORDER BY lib_keywords.keyword ASC
            LIMIT 0, 500");
        //$query = $this->db->query("SELECT id, keyword as value, keyword as label, section_id as section FROM lib_clips_keywords WHERE clip_id IN (" . $search_filter_clip_id . ") group by keyword");
        $res = $query->result_array();
        //echo $this->db->last_query();
//        echo "<pre>";
//        print_r($res);
//        echo "</pre>";
        $result = $this->keywords_to_section($res, $onlyKeywords);
//        echo "<pre>";
//        print_r($result);
//        echo "</pre>";
        return $result;
    }

    function get_clip_keywords_facet($allKeywords, $facetKeywords) {
        foreach ($facetKeywords as $section => $words) {
            //array_keys dont work
            $keysWords = array();
            foreach ($words as $k => $v) {
                if ($v > 0)
                    $keysWords[] = ucfirst($k);
            }
            $keywords[$section] = array_intersect($keysWords, $allKeywords[$section]);
            sort($keywords[$section]);
        }
        return $keywords;
    }

    /**
     * @param $keywords - array
     * @param $onlyKeywords - bool
     * @return array - array sort to section
     */
    private function keywords_to_section($keywords, $onlyKeywords = false) {
        $words = array();
        if ($onlyKeywords) {
            foreach ($keywords as $word) {
                $words[$word['section']][] = $word['value'];
                $lowerword = array_change_key_case($words, CASE_LOWER);
//                foreach ($lowerword as $key => $low) {
//                    $key = str_replace(" ", "_", $key);
//                    $lowerword_type[$key] = $key;
//                }
            }
        } else {
            foreach ($keywords as $word) {
                $words[$word['section']][] = $word;
                $lowerword = array_change_key_case($words, CASE_LOWER);
//                foreach ($lowerword as $key => $low) {
//                    $key = str_replace(" ", "_", $key);
//                    $lowerword_type[$key] = $key;
//                }
            }
        }
        return $lowerword;
        //return $words;
    }

    function update_keyword_statistic($provider_id, $keyword, $lang = 'en') {
        $query = $this->db->get_where('lib_search', array('provider_id' => $provider_id, 'phrase' => $keyword, 'lang' => $lang));
        $rows = $query->result_array();
        if ($query->num_rows()) {
            if ($rows[0]['type'] != 2) {
                $data['times'] = $rows[0]['times'] + 1;
                $this->db_master->where('id', $rows[0]['id']);
                $this->db_master->update('lib_search', $data);
            }
        } else {
            $data['provider_id'] = $provider_id;
            $data['phrase'] = $keyword;
            $data['lang'] = $lang;
            $this->db_master->insert('lib_search', $data);
        }
    }

    function SearchLogger($words, $provider, $user_login) {
        $result = $this->db->query("SELECT id FROM lib_search WHERE phrase = ?", array($words));
        $result = ( is_object($result) ) ? $result->row_array() : array();
        $sid = ( is_array($result) && isset($result['id']) ) ? $result['id'] : 0;
        $this->db_master->query("INSERT INTO lib_search_log ( provider, user_login, search_phrase_id ) VALUES ( ?, ?, ? )", array($provider, $user_login, $sid));
    }

    function GetSearchOverallLogList($filter) {
        $limit = ( $limit = $this->uri->segment(5) ) ? "LIMIT {$limit}, " . $this->settings['perpage'] : "LIMIT 0, " . $this->settings['perpage'];
        $result = $this->db->query("SELECT *, ( SELECT CONCAT( fname, ' ', lname ) FROM lib_users WHERE id = lib_search.provider_id ) AS provider FROM lib_search {$filter} ORDER BY times DESC {$limit}");
        return ( is_object($result) ) ? $result->result_array() : array();
    }

    function GetSearchRequestLogList($filter) {
        $limit = ( $limit = $this->uri->segment(5) ) ? "LIMIT {$limit}, " . $this->settings['perpage'] : "LIMIT 0, " . $this->settings['perpage'];
        $result = $this->db->query("SELECT *, ( SELECT CONCAT( fname, ' ', lname ) FROM lib_users WHERE id = log.provider ) AS provider FROM lib_search_log AS log JOIN lib_search AS s ON s.id = log.search_phrase_id {$filter} ORDER BY log.id DESC {$limit}");
        return ( is_object($result) ) ? $result->result_array() : array();
    }

    function GetSearchOverallLogListCount($filter) {
        $result = $this->db->query("SELECT COUNT( id ) AS 'count' FROM lib_search {$filter}");
        $result = ( is_object($result) ) ? $result->row_array() : array();
        return ( isset($result['count']) ) ? $result['count'] : 0;
    }

    function GetSearchRequestLogListCount($filter) {
        $result = $this->db->query("SELECT COUNT( log.id ) AS 'count' FROM lib_search_log AS log JOIN lib_search AS s ON s.id = log.search_phrase_id {$filter}");
        $result = ( is_object($result) ) ? $result->row_array() : array();
        return ( isset($result['count']) ) ? $result['count'] : 0;
    }

    function GetSearchOverallProvidersList() {
        $result = $this->db->query("
			SELECT DISTINCT
				user.id,
				CONCAT( user.fname, ' ', user.lname ) AS 'name'
			FROM
				lib_users AS user
			JOIN
				lib_search AS search
				ON
					search.provider_id = user.id"
        );
        return ( is_object($result) ) ? $result->result_array() : array();
    }

    function GetSearchRequestProvidersList() {
        $result = $this->db->query("
			SELECT DISTINCT
				user.id,
				CONCAT( user.fname, ' ', user.lname ) AS 'name'
			FROM
				lib_users AS user
			JOIN
				lib_search_log AS log
				ON
					log.provider = user.id
			JOIN
				lib_search AS search
				ON
					search.id = log.search_phrase_id"
        );
        return ( is_object($result) ) ? $result->result_array() : array();
    }

    /**
     * function to get fotrmat category available filters, so that filter value grouped in one place
     *
     * @return array
     */
    public function getFormatCategoryFilter()
    {
        return [
            'label' => 'Format Category',
            'type' => 'ckeckbox',
            'options' => [
                ['value' => 1, 'label' => 'SD'],
                ['value' => 2, 'label' => 'HD (1280 to 1440)'],
                ['value' => 3, 'label' => 'HD (1920x1080)'],
                ['value' => 4, 'label' => 'Ultra HD (3840x2160)'],
                ['value' => 5, 'label' => 'Ultra HD 4K'],
                ['value' => 6, 'label' => 'Ultra HD 5K'],
                ['value' => 7, 'label' => 'Ultra HD 6K'],
                ['value' => 8, 'label' => 'Ultra HD 8K'],

            ],
            'additional' => 0,
            'display' => 1
        ];
    }

}
