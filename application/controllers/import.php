<?php

class Import extends CI_Controller
{

    function Import()
    {
        parent::__construct();
        $this->load->model('import_model');
        $this->load->model('groups_model');
        $this->load->library('xl_importer');
        $this->langs = $this->uri->segment(1);
        $this->set_group();
    }

    function set_group()
    {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') :
            $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

    function master()
    {
        $this->load->model('import_task_model');
        $this->path = 'Clips section / Import master files information';
        $task_id = intval($this->uri->segment(4));
        $data = array();
        if ($task_id) {
            $task = $this->import_task_model->get_task($task_id);
            if ($task) {
                $task['progress'] = $task['total'] ? $task['processed'] * 100 / $task['total'] : 0;
                $data['task'] = $task;
            }
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json');
                echo json_encode($data);
                exit();
            }
        } elseif ($this->input->post('upload')) {
            if (!empty($_FILES) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                $dir = realpath(__DIR__ . '/../../data/upload/import');
                $file = $dir . '/' . date('YmdHis-') . $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $file);
                if (($handle = fopen($file, 'r')) !== false) {
                    $line = 0;
                    $valid = true;
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $line++;
                        if ($line == 1) {
                            continue;
                        }
                        if (!(count($row) == 2 && !empty($row[0]) && !empty($row[1]))) {
                            $valid = false;
                            $data['import_errors'] = 'Parse error';
                            break;
                        }
                        if ($line > 5) {
                            break;
                        }
                    }
                    fclose($handle);
                }
                if ($valid) {
                    $task_id = $this->import_task_model->create_task($file);
                    $cmd = 'php ' . realpath(__DIR__ . '/../..') . '/index.php uploadstools import_master';
                    exec($cmd . ' > /dev/null &');
                    redirect($this->langs . '/import/master/' . $task_id);
                } else {
                    @unlink($file);
                }
            } else {
                $data['import_errors'] = 'File has not been loaded.';
            }
        }

        $data['lang'] = $this->langs;
        $content = $this->load->view('import/master', $data, true);
        $this->out($content);
    }

    function upload()
    {
        $this->group = $this->groups_model->get_group_by_user($this->session->userdata('uid'));
        $this->data['lang'] = $this->langs;

        $this->path = 'Clips section / Import clips information';

        if ($this->input->post('upload')) {
            if (!empty($_FILES) && is_uploaded_file($_FILES['xl']['tmp_name'])) {
                $file_name = $_SERVER['DOCUMENT_ROOT'] . '/data/upload/import/'
                    . date('YmdHis-') . $_FILES['xl']['name'];
                move_uploaded_file($_FILES['xl']['tmp_name'], $file_name);

                $this->xl_importer->set_file_name($file_name);
                $data['file_name'] = $file_name;
                $cols_map = $this->xl_importer->get_cols_map();

                $data['cols_map'] = $cols_map;
                /*
                 * if(user_id == clip_id) 
                 * then import excel file
                 * 
                 * else
                 * 
                 * display an error
                 */
                while (($get_row = $this->xl_importer->get_row()) != NULL) {

                    $code_excel = $get_row['Code'];

                    if (strpos($code_excel, '_') !== false) {
                        $getResByCode = $this->import_model->getResultByCode($code_excel);
                        $clip_id = $getResByCode[0]->id;
                    } else {
                        $getResByCode = $this->import_model->getResultById($code_excel);
                        $clip_id = $getResByCode[0]->id;

                    }


                    $client_id = $getResByCode[0]->client_id;
                    $code_type = $get_row['Code Type'];
                    $code_break = explode("_", $code_excel);
                    $code_exp = $code_break[0];
                    $first_letter_code = substr($code_exp, 0, 2);
                    //if ($first_letter_code == 'BC') {
                    //    $clip_id = $code_break[1];
                    // }

//                    else
//                    {
//                        $clip_id = $code_excel;
//                    }
//                    if($code_type==2)
//                    {
//                        $new_clip_code = explode('_', $code_excel);
//                        $clip_id = $new_clip_code[1];
//                    }
                    if ($this->session->userdata('uid')) {
                        //$get_row = $this->xl_importer->get_row();
//                        $title = $get_row['Title'];
//                        $creator = $get_row['Creator'];
//                        $rights = $get_row['Rights'];
//                        $subject = $get_row['Subject'];
//                        $date = $get_row['Date'];
//                        $category = $get_row['Category'];
//                        $price = $get_row['Price'];
//                        $price_per_second = $get_row['Price per second'];
//                        $licence = $get_row['License'];
//                        $format = $get_row['Format'];
                        $notes = $get_row['Notes'];
                        $license_restriction = $get_row['License Restrictions'];
                        if ($license_restriction == '') {
                            $license_restriction = $get_row['License restrictions'];
                        }

                        $video_audio = $get_row['Video/Audio'];
                        $collection = $get_row['Category'];
                        $collection_exp = explode(",", $collection);

                        $month_filmed = $get_row['Month Filmed'];
                        $year_filmed = $get_row['Year Filmed'];
                        $master_format = $get_row['Master Format'];
                        $master_frame_size = $get_row['Master Frame Size'];

                        $master_frame_rate = $get_row['Master Frame Rate'];
                        $lab = $get_row['Lab'];
                        $license_type = $get_row['License Type'];
                        $price_level = $get_row['Price Level'];
                        $releases = $get_row['Releases'];
                        $description = $get_row['Description'];


                        $license_type_get_id = $this->import_model->getValuesLicenseId($license_type, 'lib_licensing');
                        $get_license_id = $license_type_get_id[0]->id;

                        if (is_numeric($get_row['Month Filmed'])) {
                            $month_filmed = $get_row['Month Filmed'];
                        } else {

                            if ($get_row['Month Filmed'] == 'January') {
                                $month_filmed = '01';
                            }
                            if ($get_row['Month Filmed'] == 'Feburary') {
                                $month_filmed = '02';
                            }
                            if ($get_row['Month Filmed'] == 'March') {
                                $month_filmed = '03';
                            }
                            if ($get_row['Month Filmed'] == 'April') {
                                $month_filmed = '04';
                            }
                            if ($get_row['Month Filmed'] == 'May') {
                                $month_filmed = '05';
                            }
                            if ($get_row['Month Filmed'] == 'June') {
                                $month_filmed = '06';
                            }
                            if ($get_row['Month Filmed'] == 'July') {
                                $month_filmed = '07';
                            }
                            if ($get_row['Month Filmed'] == 'August') {
                                $month_filmed = '08';
                            }
                            if ($get_row['Month Filmed'] == 'September') {
                                $month_filmed = '09';
                            }
                            if ($get_row['Month Filmed'] == 'October') {
                                $month_filmed = '10';
                            }
                            if ($get_row['Month Filmed'] == 'November') {
                                $month_filmed = '11';
                            }
                            if ($get_row['Month Filmed'] == 'December') {
                                $month_filmed = '12';
                            }
                        }
                        $get_date_filmed = $year_filmed . '-' . $month_filmed . '-' . '01';

                        if ($price_level == 'Budget') {
                            $get_price_level = '1';
                        }
                        if ($price_level == 'Standard') {
                            $get_price_level = '2';
                        }
                        if ($price_level == 'Premium') {
                            $get_price_level = '3';
                        }
                        if ($price_level == 'Gold') {
                            $get_price_level = '4';
                        }
                        if ($price_level == '') {
                            $get_price_level = '2';
                        }
                        //$delivery_category = $get_row['Delivery category'];
                        //Read formats from excel sheet
                        $camera_model = $get_row['Camera Model'];
                        $camera_sensor = $get_row['Camera Sensor'];
                        $source_bit_depth = $get_row['Source Bit Depth'];
                        $source_chroma_subsampling = $get_row['Source Chroma Subsampling'];
//                        $exp_chroma_subsampling = substr($source_chroma_subsampling,0,-2);
//                        print_r($source_chroma_subsampling);exit;
                        $source_format = $get_row['Source Format'];
                        $source_codec = $get_row['Source Codec'];
                        $source_frame_size = $get_row['Source Frame Size'];

                        $source_frame_rate = $get_row['Source Frame Rate'];
                        $submission_codec = $get_row['Submission Codec'];
                        $submission_data_rate = $get_row['Submission Data Rate'];
                        $submission_frame_size = $get_row['Submission Frame Size'];
                        $submission_frame_rate = $get_row['Submission Frame Rate'];
                        //Get values of keywords from excel sheet
                        $shot_type = $get_row['Shot Type'];
                        $single_shhot = explode(',', $shot_type);

                        $subject_category = $get_row['Subject Category'];
                        $sub_values = explode(',', $subject_category);

                        $primary_subject = $get_row['Primary Subject'];
                        $primry_value = explode(',', $primary_subject);

                        $other_subject = $get_row['Other Subject'];
                        $oher_values = explode(',', $other_subject);

                        $appearance = $get_row['Appearance'];
                        $apperance_values = explode((','), $appearance);

                        $actions = $get_row['Actions'];
                        $action_values = explode(',', $actions);

                        $time = $get_row['Time'];
                        $time_values = explode(',', $time);

                        $habitat = $get_row['Habitat'];
                        $habitat_values = explode(',', $habitat);

                        $concept = $get_row['Concept'];
                        $concept_values = explode(',', $concept);

                        $location = $get_row['Location'];
                        $location_values = explode(',', $location);


                        $country = $get_row['Country'];
                        $brand = $get_row['Brand'];
                        $brand_query = $this->import_model->getBrandId($brand);
                        $brand_id = $brand_query[0]->id;
                        $delivery = $get_row['Delivery'];

                        if ($clip_id) {
                            if (!empty($delivery)) {
                                $pricing_category = $delivery;
                            } else {
                                if (!empty($submission_codec)) {
                                    $this->db->select('delivery_category');
                                    $query = $this->db->get_where('lib_submission_codecs', array('name' => $submission_codec));
                                    $row = $query->result_array();
                                    if ($row[0]['delivery_category'])
                                        $pricing_category = $row[0]['delivery_category'];
                                }
                            }
                            $this->db_master->delete('lib_clips_delivery_formats', array('clip_id' => $clip_id));


                            $license = $get_license_id;

                            if ($get_license_id == '') {
                                $license = 1;
                            }
                            if ($license) {
                                $pricing_cat = $pricing_category;
                                $this->load->model('clips_model');
                                if ($license == 1) {
                                    $formatLicense = 1;
                                    $delivery_formats = $this->db->query('SELECT id, categories FROM lib_rf_delivery_options')->result_array();
                                    foreach ($delivery_formats as $format) {
                                        if ($format['categories']) {
                                            $categories = explode(' ', $format['categories']);
                                            if (in_array($pricing_cat, $categories)) {
                                                $this->clips_model->insert_with_validation_to_lib_clips_delivery_formats($clip_id, $format['id'], $formatLicense);
                                                //$this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $clip_id, 'format_id' => $format['id'], 'license' => $formatLicense));
                                            }
                                        }
                                    }
                                } else {
                                    $formatLicense = 2;
                                    $delivery_formats = $this->db->query('SELECT id, categories, collection FROM lib_delivery_options')->result_array();
                                    foreach ($delivery_formats as $format) {
                                        if ($format['categories']) {
                                            $categories = explode(' ', $format['categories']);
                                            if (in_array($pricing_cat, $categories) && $brand == $format['collection']) {
                                                $this->clips_model->insert_with_validation_to_lib_clips_delivery_formats($clip_id, $format['id']);
                                                //$this->db_master->insert('lib_clips_delivery_formats', array('clip_id' => $clip_id, 'format_id' => $format['id']));
                                            }
                                        }
                                    }
                                }

                            }
//$licence && $date && $format && $price && $price_per_second && 
                            if ($license_restriction || $video_audio || $master_format || $master_frame_size ||
                                $master_frame_rate || $lab || $get_license_id || $get_date_filmed || $get_price_level || $releases || $camera_model || $camera_sensor || $source_bit_depth ||
                                $source_chroma_subsampling || $source_format || $source_codec || $source_frame_rate || $submission_codec || $submission_data_rate || $submission_frame_size ||
                                $submission_frame_rate || $source_frame_size || $country || $brand_id
                            ) {


                                $clip_values = array();

                                if (!empty($description)) {
                                    $clip_values['description'] = $description;
                                }
                                if (!empty($notes)) {
                                    $clip_values['notes'] = $notes;
                                }
                                if (!empty($license_restriction)) {
                                    $clip_values['license_restrictions'] = $license_restriction;
                                }
                                if (!empty($video_audio)) {
                                    $clip_values['audio_video'] = $video_audio;
                                }
                                if (!empty($master_format)) {
                                    $clip_values['master_format'] = $master_format;
                                }
                                if (!empty($master_frame_size)) {
                                    $clip_values['master_frame_size'] = $master_frame_size;
                                }
                                if (!empty($master_frame_rate)) {
                                    $clip_values['master_frame_rate'] = $master_frame_rate;
                                }
                                if (!empty($lab)) {
                                    $clip_values['master_lab'] = $lab;
                                }
                                if (!empty($get_license_id)) {
                                    $clip_values['license'] = $get_license_id;
                                }
                                if (!empty($get_date_filmed) && $get_date_filmed!= '--01') {
                                    $clip_values['film_date'] = $get_date_filmed;
                                }
                                if (!empty($get_price_level)) {
                                    $clip_values['price_level'] = $get_price_level;
                                }
                                if (!empty($releases)) {
                                    $clip_values['releases'] = $releases;
                                }
                                if (!empty($camera_model)) {
                                    $clip_values['camera_model'] = $camera_model;
                                }
                                if (!empty($camera_sensor)) {
                                    $clip_values['camera_chip_size'] = $camera_sensor;
                                }
                                if (!empty($source_bit_depth)) {
                                    $clip_values['bit_depth'] = $source_bit_depth;
                                }
                                if (!empty($source_chroma_subsampling)) {
                                    $clip_values['color_space'] = $source_chroma_subsampling;
                                }
                                if (!empty($source_format)) {
                                    $clip_values['source_format'] = $source_format;
                                }
                                if (!empty($source_codec)) {
                                    $clip_values['source_codec'] = $source_codec;
                                }
                                if (!empty($source_frame_rate)) {
                                    $clip_values['source_frame_rate'] = $source_frame_rate;
                                }
                                if (!empty($submission_codec)) {
                                    $clip_values['digital_file_format'] = $submission_codec;
                                }
                                if (!empty($submission_data_rate)) {
                                    $clip_values['source_data_rate'] = $submission_data_rate;
                                }
                                if (!empty($submission_frame_size)) {
                                    $clip_values['digital_file_frame_size'] = $submission_frame_size;
                                }
                                if (!empty($submission_frame_rate)) {
                                    $clip_values['digital_file_frame_rate'] = $submission_frame_rate;
                                }
                                if (!empty($source_frame_size)) {
                                    $clip_values['source_frame_size'] = $source_frame_size;
                                }
                                if (!empty($brand_id)) {
                                    $clip_values['brand'] = $brand_id;
                                }
                                if (!empty($pricing_category)) {
                                    $clip_values['pricing_category'] = $pricing_category;
                                }

//                                $clip_values = array(
////                                    'license' => $licence,
////                                    'creation_date' => $date,
////                                    'format' => $format,
////                                    'price' => $price,
////                                    'price_per_second' => $price_per_second,
//                                    'description' => $description,
//                                    'notes' => $notes,
//                                    'license_restrictions' => $license_restriction,
//                                    'audio_video' => $video_audio,
//                                    'master_format' => $master_format,
//                                    'master_frame_size' => $master_frame_size,
//                                    'master_frame_rate' => $master_frame_rate,
//                                    'master_lab' => $lab,
//                                    'license' => $get_license_id,
//                                    'film_date' => $get_date_filmed,
//                                    'price_level' => $get_price_level,
//                                    'releases' => $releases,
//                                    'camera_model' => $camera_model,
//                                    'camera_chip_size' => $camera_sensor,
//                                    'bit_depth' => $source_bit_depth,
//                                    'color_space' => $source_chroma_subsampling,
//                                    'source_format' => $source_format,
//                                    'source_codec' => $source_codec,
//                                    'source_frame_rate' => $source_frame_rate,
//                                    'digital_file_format' => $submission_codec,
//                                    'source_data_rate' => $submission_data_rate,
//                                    'digital_file_frame_size' => $submission_frame_size,
//                                    'digital_file_frame_rate' => $submission_frame_rate,
//                                    'source_frame_size' => $source_frame_size,
//                                    'brand' => $brand_id,
//                                    'pricing_category' => $pricing_category,
//                                );


                                $this->import_model->update_clip('lib_clips', $clip_id, $clip_values);
                            }

                            //$title && $creator && $rights && $subject &&
//                            if ($notes || $description) {
//                                $content_values = array(
////                                    'title' => $title,
////                                    'creator' => $creator,
////                                    'rights' => $rights,
////                                    'subject' => $subject,
//                                    'notes' => $notes,
//                                    'description' => $description,
//                                );
//                                $this->import_model->update_clip_content('lib_clips_content', $clip_id, $content_values);
//                            }


                            if (!empty($collection)) {
                                $this->db_master->query('DELETE FROM lib_clips_keywords WHERE clip_id=' . $clip_id . ' AND section_id="category" ');
                                $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword="' . $collection . '" , clip_id=' . $clip_id . ' , section_id="category" ');
                            }

                            if ($country) {
                                $this->db_master->query('DELETE FROM lib_clips_keywords WHERE clip_id=' . $clip_id . ' AND section_id="country" ');
                                $this->db_master->query('INSERT INTO lib_clips_keywords SET keyword="' . $country . '" , clip_id=' . $clip_id . ' , section_id="country" ');
                            }


//                            if (!empty($collection_exp)) {
//
//                                foreach ($collection_exp as $coll) {
//
//                                    $collection_id = $this->import_model->getValuesId($coll, 'lib_collections');
//                                    $get_collection_id = $collection_id[0]->id;
//                                    $collection_exists = $this->import_model->getCollectionRecord($clip_id, $get_collection_id);
//                                    $clip_collection_id = $collection_exists[0]->clip_id;
//                                    $get_collection_slect_id = $collection_exists[0]->collection_id;
//                                    if (empty($collection_exists)) {
//                                        $values = array(
//                                            'clip_id' => $clip_id,
//                                            'collection_id' => $get_collection_id,
//                                        );
//                                        //print_r($values);exit;
//                                        $this->import_model->insert_clip_collection('lib_clips_collections', $clip_id, $get_collection_id);
//                                    }
//                                }
//                            }


                            if ($shot_type || $subject_category || $primary_subject || $other_subject || $appearance || $actions || $time || $habitat || $concept || $location) {
                                $overwrite = !empty($this->input->post('overwrite_keywords')) ? true : false;
                                $this->import_model->insert_clip_keywords($clip_id, 'shot_type', $single_shhot, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'subject_category', $sub_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'primary_subject', $primry_value, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'other_subject', $oher_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'appearance', $apperance_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'actions', $action_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'time', $time_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'habitat', $habitat_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'concept', $concept_values, $overwrite);
                                $this->import_model->insert_clip_keywords($clip_id, 'location', $location_values, $overwrite);


                                // DO not use $this->db (read replica) in this case because of replica lag
                                $clip_keywords = $this->db_master->query('SELECT * FROM lib_clips_keywords WHERE clip_id = ?', array($clip_id))->result_array();


                                $data_clip['location'] = '';
                                $data_clip['keywords'] = '';

                                if (!empty($clip_keywords)) {
                                    foreach ($clip_keywords as $keyword) {

                                        if (!empty($keyword)) {
                                            if ($keyword['section_id'] == 'location') {
                                                if ($data_clip['location'])
                                                    $data_clip['location'] .= ', ' . $keyword['keyword'];
                                                else
                                                    $data_clip['location'] = $keyword['keyword'] . ' , ' . $data['country'];
                                            }
                                            if ($data_clip['keywords'])
                                                $data_clip['keywords'] .= ', ' . $keyword['keyword'];
                                            else
                                                $data_clip['keywords'] = $keyword['keyword'];

                                        }
                                    }


                                    $this->db_master->where('id', $clip_id);
                                    $this->db_master->update('lib_clips', $data_clip);

                                    $querySelect = $this->db->query('select * from lib_clips  WHERE  id=' . $clip_id);
                                    $rowSelect = $querySelect->result_array();
                                    if ($rowSelect[0]['active'] != 2) {
                                        $dataUpdateArray = array();
                                        if ($rowSelect[0]['description'] == '' || $rowSelect[0]['license'] == '' || $rowSelect[0]['price_level'] == '' || $rowSelect[0]['digital_file_format'] == '' || $rowSelect[0]['digital_file_frame_size'] == '' || $rowSelect[0]['digital_file_frame_rate'] == '') {
                                            $dataUpdateArray['active'] = 0;
                                        } else {
                                            $dataUpdateArray['active'] = 1;
                                        }

                                        if ($dataUpdateArray['active'] == 1) {

                                            $query3 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $clip_id, 'section_id' => 'location'));
                                            $clip_status3 = $query3->result_array();

                                            if ($clip_status3[0]['keyword'] != '') {
                                                $dataUpdateArray['active'] = 1;
                                            } else {
                                                $dataUpdateArray['active'] = 0;
                                            }
                                        }


                                        if ($dataUpdateArray['active'] == 1) {

                                            $query2 = $this->db->get_where('lib_clips_keywords', array('clip_id' => $clip_id, 'section_id' => 'category'));
                                            $clip_status2 = $query2->result_array();
                                            if ($clip_status2[0]['keyword'] != '') {
                                                $dataUpdateArray['active'] = 1;
                                            } else {
                                                $dataUpdateArray['active'] = 0;
                                            }
                                        }

                                        $this->db_master->where('id', $clip_id);
                                        $this->db_master->update('lib_clips', $dataUpdateArray);

                                    }


                                }


                            }
                        }
                    }
                }
                $data['useful_cols'] = array_keys($cols_map);
                $data['reader'] = $this->xl_importer;
            } else {
                $data['import_errors'] = 'Excel file has not been loaded.';
            }
        } elseif ($this->input->post('import')) {
            $file_name = $this->input->post('file_name');
            $this->xl_importer->set_file_name($file_name);
            $this->xl_importer->load_data($this->langs);
            $data['import_results'] = $this->xl_importer->get_import_results();
        }
        $this->set_content('import/upload', $data, 'Import :: Editor account');
    }

    function out($content = null, $pagination = null, $type = 1)
    {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }

    function set_content($method, $data, $title = null, $pagination = null)
    {
        if (false/* $this->group['is_editor'] */) {
            $data['menu'] = $this->load->view('main/ext/editormenu', array('lang' => $this->langs, 'is_editor' => 1), true);
            $content['title'] = $title;
            $content['body'] = $this->load->view($method, $data, true);
            $content['add_js'] = 'data/js/admin.js';
            $type = 0;
        } else {
            $content = $this->load->view($method, $data, true);
            $type = 1;
        }
        $this->out($content, $pagination, $type);
    }

}
