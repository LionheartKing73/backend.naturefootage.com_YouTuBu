<?php

/**
 * Class Bin_model
 * @property Clips_model $clips_model
 */
class Bin_model extends CI_Model
{

    var $lb;
    var $client;

    function Bin_model()
    {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);

        $this->load->model('images_model', 'im');
        $this->load->model('clips_model', 'mm');
        $this->load->model('cart_model', 'crtm');

        $this->set_current_bin();
        $this->lb = $this->session->userdata('bin');

        if (!$this->lb)
            $this->create_default();

        $this->client = $this->session->userdata('client_uid');
    }

    #------------------------------------------------------------------------------------------------
//    function add_item($type, $id) {
//
//        $result = 0;
//
//        if(!$this->check_exist($type, $id)) {
//            $temp['type'] = $type;
//            $temp['id'] = $id;
//
//            if($this->client) {
//                $lb_id = ($this->lb['id']) ? $this->lb['id'] : $this->save_bin();
//
//                $data['lb_id'] = $this->lb['id'];
//                $data['type'] = $type;
//                $data['item_id'] = $id;
//
//                $this->db_master->insert('lib_lb_items', $data);
//                $result = $this->db_master->insert_id();
//
//                if(is_numeric($result) && $result > 0)
//                    $this->lb['items'][] = $temp;
//
//            } else {
//                $this->lb['items'][] = $temp;
//                $this->session->set_userdata('unreg_bin_items', $this->lb['items']);
//                $result = 1;
//            }
//
//            $data['bin'] = $this->lb;
//            $this->session->set_userdata($data);
//        }
//
//        return $result;
//    }

    function add_items($login, $clips, $bin_id = 0)
    {
        if ($login) {

            $this->load->model('customers_model');
            $loggedinUserId = $this->customers_model->get_customer_id_by_login($login);
            if (!$bin_id) {
                $bin = $this->get_defaul_bin($login);
                $bin_id = $bin['id'];
            }
            if ($bin_id) {
                if (!is_array($clips))
                    $clips = array($clips);
                foreach ($clips as $item_id) {
                    if ($this->get_user_id_by_clip_id($item_id) != $loggedinUserId) {
                        $this->db_master->query("insert into lib_clip_rating (name,description,code,weight) values('clipbins_rating','Rating by Clipbins','" . $item_id . "','" . $this->get_setting('clipbinsRating') . "')");
                    }
                    $this->db->select('id');
                    $query = $this->db->get_where('lib_lb_items', array('lb_id' => $bin_id, 'item_id' => $item_id), 1);
                    $rows = $query->result_array();
                    if (!$rows)
                        $this->db_master->insert('lib_lb_items', array('lb_id' => $bin_id, 'type' => 2, 'item_id' => $item_id));
                }
            }
        }

        return $bin_id;
    }

    function add_items_login($login, $clips)
    {
        if ($login) {
            $this->load->model('customers_model');
            $user_id = $this->customers_model->get_customer_id_by_login($login);
            if ($user_id) {
                $number = $this->db->query('
                  SELECT max(CAST(Right(title, 1) AS UNSIGNED))+1 as number FROM lib_lb lb
                  WHERE title REGEXP "^My Clipbin[ ]*[[:digit:]]*$" AND client_id=?', array($user_id))->row()->number;
                $title = is_null($number) ? "My Clipbin" : "My Clipbin " . $number;
                $bin_id = $this->create_clipbin($login, $title);
                if ($bin_id) {
                    if (!is_array($clips))
                        $clips = array($clips);
                    foreach ($clips as $item_id) {
                        $this->db->select('id');
                        $query = $this->db->get_where('lib_lb_items', array('lb_id' => $bin_id, 'item_id' => $item_id), 1);
                        $rows = $query->result_array();
                        if (!$rows)
                            $this->db_master->insert('lib_lb_items', array('lb_id' => $bin_id, 'type' => 2, 'item_id' => $item_id));
                    }
                }
            }
        }

        return $bin_id;
    }

    function remove_items($clips, $bin_id)
    {
        if (!is_array($clips))
            $clips = array($clips);
        $this->db_master->where_in('item_id', $clips);
        $this->db_master->where('lb_id', $bin_id);
        $this->db_master->delete('lib_lb_items', array('lb_id' => $bin_id));
        return true;
    }

    function remove_all_items($bin_id)
    {
        $this->db_master->where('lb_id', $bin_id);
        $this->db_master->delete('lib_lb_items', array('lb_id' => $bin_id));
        return true;
    }

    function get_defaul_bin($login)
    {
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        $bin = false;
        if ($user_id) {
            $query = $this->db->get_where('lib_lb', array('client_id' => $user_id, 'is_default' => 1), 1);
            $rows = $query->result_array();
            $bin = $rows[0];
            if (!$bin['id']) {
                $query = $this->db->get_where('lib_lb', array('client_id' => $user_id), 1);
                $rows = $query->result_array();
                if ($rows[0]['id']) {
                    $this->db_master->where('id', $rows[0]['id']);
                    $this->db_master->update('lib_lb', array('is_default' => 1));
                } else {
                    $bin = $this->create_defaul_bin($user_id);
                }
            }
        }
        return $bin;
    }

    function create_defaul_bin($user_id)
    {
        $bin = false;
        $data = array(
            'title' => 'Default',
            'client_id' => $user_id,
            'is_default' => 1
        );
        $this->db_master->insert('lib_lb', $data);
        $id = $this->db_master->insert_id();
        if ($id) {
            $data['id'] = $id;
            $bin = $data;
        }
        return $bin;
    }

    function get_items_by_ids($clips_ids, $sort = array(), $perpage = 20, $page = 1)
    {
        $offset = (int)$perpage * ((int)$page - 1);
        $this->load->model('clips_model');
        $res = array();
        $total = $clips_ids;

        /* foreach($clips_ids as $k=>$item){
          if($k>=$offset && $perpage){
          $items_ids[] = $item;
          $perpage--;
          }
          }
          /*if($items_ids)
          $res = $this->clips_model->get_clipbin_clips($items_ids, $sort, $offset=0,$perpage=20); */
        $res = $this->clips_model->get_clipbin_clips($clips_ids, $sort, 'en', $offset, $perpage);
        $res['bins'] = $res;
        $res['total'] = count($total);
        return $res;
    }

    function get_items($bin_id, $sort = array(), $perpage = 20, $page = 1)
    {
        $offset = (int)$perpage * ((int)$page - 1);
        $this->load->model('clips_model');
        $res = array();
        $this->db->select('item_id');
        $total = $this->db->get_where('lib_lb_items', array('lb_id' => $bin_id));
        $total = $total->result_array();

        /* $this->db->select('item_id');
          $query = $this->db->get_where('lib_lb_items', array('lb_id' => $bin_id), $perpage, $offset);
          $rows = $query->result_array(); */
        $items_ids = array();
        foreach ($total as $item) {
            $items_ids[] = $item['item_id'];
        }
        if ($items_ids)
            //$res = $this->clips_model->get_clipbin_clips($items_ids, $sort);*/
            $res = $this->clips_model->get_clipbin_clips($items_ids, $sort, 'en', $offset, $perpage);
        $res['bins'] = $res;
        $res['total'] = count($total);
        return $res;
    }

    function get_items_sequence($bin_id, $sort = array(), $perpage = 20, $page = 1)
    {
        $offset = (int)$perpage * ((int)$page - 1);
        $this->load->model('clips_model');
        $res = array();
        $this->db->select('item_id');
        $total = $this->db->get_where('lib_backend_lb_items', array('backend_lb_id' => $bin_id));
        $total = $total->result_array();

        /* $this->db->select('item_id');
          $query = $this->db->get_where('lib_lb_items', array('lb_id' => $bin_id), $perpage, $offset);
          $rows = $query->result_array(); */
        $items_ids = array();
        foreach ($total as $item) {
            $items_ids[] = $item['item_id'];
        }
        if ($items_ids)
            //$res = $this->clips_model->get_clipbin_clips($items_ids, $sort);*/
            $res = $this->clips_model->get_clipbin_clips($items_ids, $sort, 'en', $offset, $perpage);
        $res['bins'] = $res;
        $res['total'] = count($total);
        return $res;
    }

    #------------------------------------------------------------------------------------------------

    function remove_items_old($ids)
    {
        foreach ((array)$ids as $parts) {
            list($type, $id) = explode('-', $parts);

            foreach ($this->lb['items'] as $k => $v) {
                if ($v['type'] == $type && $v['id'] == $id) {
                    unset($this->lb['items'][$k]);

                    if ($this->client) {
                        $lb_id = ($this->lb['id']) ? $this->lb['id'] : $this->save_bin();
                        $this->db_master->delete('lib_lb_items', array('lb_id' => $lb_id, 'type' => $type, 'item_id' => $id));
                    }
                }
            }
        }

        $data['bin'] = $this->lb;
        $this->session->set_userdata($data);
    }

    #------------------------------------------------------------------------------------------------

    function create_default()
    {
        $data['bin']['id'] = 0;
        $data['bin']['name'] = 'Default';
        $data['bin']['items'] = array();

        $this->lb = $data['bin'];
        $this->session->set_userdata($data);
    }

    #------------------------------------------------------------------------------------------------

    function check_exist($type, $id)
    {
        foreach ($this->lb['items'] as $v) {
            if ($v['type'] == $type && $v['id'] == $id)
                return true;
        }
        return false;
    }

    #------------------------------------------------------------------------------------------------

    function get_content($lang)
    {
        $names = array('', 'li', 'c');

        if ($this->lb['items']) {
            foreach ($this->lb['items'] as $v)
                $types[$v['type']][] = $names[$v['type']] . '.id=' . $v['id'];

            foreach ($types as $type => $items)
                $filter[$type] = 'and (' . implode(' or ', $items) . ')';

            if (count($types[1]))
                $results[1] = $this->im->get_images_list($lang, $filter[1]);

            if (count($types[2])) {
                $results[2] = $this->mm->get_clips_list($lang, $filter[2]);
            }
        }

        if (!is_array($results[1])) {
            return $results[2];
        } elseif (!is_array($results[2])) {
            return $results[1];
        } else {
            return array_merge($results[1], $results[2]);
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_client_bins($id)
    {
        if ($id) {
            $query = $this->db->get_where('lib_lb', array('client_id' => $id));
            $query = $this->db->query('select * from lib_lb where client_id=' . $this->client . ' order by title');
            $rows = $query->result_array();

            foreach ($rows as $row) {
                $list[$row['id']] = $row['title'];
            }

            return $list;
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_client_default_bin($id)
    {
        if ($id) {
            $query = $this->db->get_where('lib_lb', array('client_id' => $id, 'is_default' => 1), 1);
            $row = $query->row_array();
            return $row;
        }
    }

    #------------------------------------------------------------------------------------------------

    function save_bin($id = null)
    {
        if (isset($_POST['save'])) {

            $title = $this->input->post('title', true);
            $description = $this->input->post('description', true);

            $data['client_id'] = $this->client;
            $data['title'] = $title;
            $data['description'] = $description;
        } else {
            $data['client_id'] = $this->client;
            $data['title'] = $this->lb['name'];
            $data['is_default'] = ($this->lb['name'] == 'Default') ? 1 : 0;
        }

        if ($id) {
            $this->db_master->where('id', $id);
            $this->db_master->update('lib_lb', $data);
        } else {
            $this->db_master->insert('lib_lb', $data);
            $id = $this->db_master->insert_id();

            if (!$this->lb)
                $this->save_items($id);
            $this->set_current_bin($id);
        }

        $temp['bin']['id'] = $id;
        $temp['bin']['name'] = $data['title'];
        $temp['bin']['items'] = $this->lb['items'];

        $this->session->set_userdata($temp);

        return $id;
    }

    #------------------------------------------------------------------------------------------------

    function save_items($id)
    {
        if ($this->lb['items']) {

            $this->db_master->delete('lib_lb_items', array('lb_id' => $id));

            foreach ($this->lb['items'] as $v) {
                $data['lb_id'] = $id;
                $data['type'] = $v['type'];
                $data['item_id'] = $v['id'];

                $this->db_master->insert('lib_lb_items', $data);
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_bin($id)
    {
        if ($id) {
            $query = $this->db->get_where('lib_lb', array('id' => $id));
            $this->delete_duplicates($id);
            $rows = $query->result_array();
            $data = $rows[0];

            $query = $this->db->query('select type, item_id as id from lib_lb_items where lb_id=' . $id);
            $data['items'] = $query->result_array();
        }

        return $data;
    }

    #---------------------------------------------------------------------------------#

    function delete_duplicates($lb_id)
    {

        $this->db->select('item_id, MAX(id) as row_id, COUNT(item_id) as count');
        $this->db->from('lib_lb_items');
        $this->db->where(array('lb_id' => $lb_id));
        $this->db->group_by('type, item_id');
        $this->db->order_by('COUNT(item_id)', 'desc');

        $duplicates = $this->db->get();

        if ($duplicates->num_rows() > 0) {

            foreach ($duplicates->result_array() as $row) {

                if ($row['count'] > 1) {

                    $this->db->limit(1)->delete('lib_lb_items', array('id' => $row['row_id']));
                } else {

                    break;
                }
            }
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_email_bin($id)
    {
        if ($id) {
            $query = $this->db->get_where('lib_email_lb', array('id' => $id));
            $rows = $query->result_array();
            $data = $rows[0];

            $query = $this->db->query('select type, item_id as id from lib_email_lb_items where lb_id=' . $id);
            $data['items'] = $query->result_array();
        }

        return $data;
    }

    #------------------------------------------------------------------------------------------------

    function make_clipbin_default($bin_id)
    {
        $rows = $this->db->get_where('lib_lb', array('id', $bin_id))->result_array();
        if (isset($rows[0]['client_id'])) {
            $this->db->set('is_default', 0);
            $this->db->where('client_id', $rows[0]['client_id']);
            $this->db->update('lib_lb');

            $this->db->set('is_default', 1);
            $this->db->where('id', $bin_id);
            $this->db->update('lib_lb');
        }
    }

    #------------------------------------------------------------------------------------------------

    function set_default_bin($uid)
    {
        $query = $this->db->get_where('lib_lb', array('client_id' => $uid, 'is_default' => 1));
        $rows = $query->result_array();

        $id = ($rows[0]['id']) ? $rows[0]['id'] : $this->save_bin();

//    if(count($this->lb['items'])) {
//      $lb = $this->lb;
//      $this->set_current_bin($id);
//
//      $this->lb['items'] = array_merge($lb['items'], $this->lb['items']);
//      $this->save_items($id);
//    }

        $this->set_current_bin($id);
    }

    #------------------------------------------------------------------------------------------------

    function set_current_bin($id = null)
    {
        $id = ($id) ? $id : $this->input->post('bin', true);

        if ($id) {
            $data = $this->get_bin($id);

            $temp['bin']['id'] = $id;
            $temp['bin']['name'] = $data['title'];
            $temp['bin']['items'] = $data['items'];
            $temp['bin']['ctime'] = $data['ctime'];

            $this->session->set_userdata($temp);
            $this->lb = $temp['bin'];
        }
    }

    #------------------------------------------------------------------------------------------------

    function clear_bin_copy()
    {

        $date = date('Y-m-d H:i:s', time() - 7776000); // older then 90 days

        $this->db->where('ctime <=', $date);
        $query = $this->db->get('lib_email_lb');

        if ($query->num_rows() > 0) {

            $rows = $query->result();

            foreach ($rows as $row)
                $this->db_master->query('DELETE FROM lib_email_lb_items WHERE lb_id=' . $row->id);

            $this->db_master->query('DELETE FROM lib_email_lb WHERE id=' . $row->id . ' LIMIT 1');
        }
    }

    #------------------------------------------------------------------------------------------------

    function create_bin_copy($id)
    {

        // get lib info
        $this->db->where('id', $id);
        $this->db->select('client_id, title, description');
        $query = $this->db->get('lib_lb', 1);

        if ($query->num_rows() > 0) {

            $data = $query->row_array();

            $this->db->set('client_id', $data['client_id']);
            $this->db->set('title', $data['title']);
            $this->db->set('description', $data['description']);
            $this->db->set('ctime', 'NOW()', FALSE);

            $this->db_master->insert('lib_email_lb');
            $lb_id = $this->db_master->insert_id();

            // get lib items
            $this->db->where('lb_id', $id);
            $query = $this->db->get('lib_lb_items');

            if ($query->num_rows() > 0) {

                $items = $query->result_array();

                foreach ($items as $item) {
                    $this->db_master->insert('lib_email_lb_items', array(
                        'lb_id' => $lb_id,
                        'type' => $item['type'],
                        'item_id' => $item['item_id']));
                }
            }

            return $lb_id;
        }
    }

    #------------------------------------------------------------------------------------------------

    function get_bin_link($id, $lang)
    {

        $this->db->where('id', $id);
        $this->db->select('title, ctime');
        $query = $this->db->get('lib_email_lb', 1);

        if ($query->num_rows() > 0) {

            $row = $query->row_array();

            $link = base_url() . 'bin/link/' . $id . '/' . md5($row['title'] . $row['ctime']);

            return $link;
        }
    }

    #------------------------------------------------------------------------------------------------

    function exec_bin($id, $checksum)
    {

        $bin = $this->get_email_bin($id);

        if (md5($bin['title'] . $bin['ctime']) == $checksum)
            return $bin;

        return false;
    }

    #------------------------------------------------------------------------------------------------

    function delete_bin()
    {
        $id = $this->lb['id'];
        $this->db_master->delete('lib_lb', array('id' => $id));
        $this->db_master->delete('lib_lb_items', array('lb_id' => $id));

        $query = $this->db->query('select id from lib_lb where client_id=' . $this->client . ' order by title limit 1');
        $rows = $query->result_array();
        $this->set_current_bin($rows[0]['id']);
    }

    #------------------------------------------------------------------------------------------------

    function email_bin($data)
    {
        $this->load->library('email');

        $config['mailtype'] = 'html';
        $config['wordwrap'] = 0;
        $this->email->initialize($config);

        $this->email->from($data['fromemail'], $data['fromname']);
        $this->email->subject($data['subject']);
        $this->email->message($data['body']);
        $this->email->to($data['email']);
        $this->email->send();
    }

    #------------------------------------------------------------------------------------------------

    function move_items($to, $ids)
    {
        foreach ((array)$ids as $parts) {
            list($type, $id) = explode('-', $parts);
            $rows = $this->db->query('SELECT id FROM lib_lb_items WHERE lb_id = ? AND type = ? AND item_id = ?', array($to, $type, $id))->result_array();
            if (!count($rows)) {
                $this->db_master->update('lib_lb_items', array('lb_id' => $to), array('type' => $type, 'item_id' => $id));
            }
        }

        $this->set_current_bin($to);
    }

    #------------------------------------------------------------------------------------------------

    function cart_items($ids)
    {
        foreach ((array)$ids as $parts) {
            list($type, $id) = explode('-', $parts);
            $this->crtm->add_item($type, $id);
        }
    }

    function get_bins_list($login)
    {
        $bins = array();
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id) {
            $bins = $this->db->query('SELECT lb.*, COUNT(lbi.id) items_count FROM lib_lb lb
              LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id WHERE lb.client_id = ? GROUP BY lb.id', array($user_id))->result_array();
        }
        return $bins;
    }

    function create_clipbin($login, $clipbin_title, $folder_id = 0)
    {
        $clipbin_id = 0;
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id && $clipbin_title) {
            $data['client_id'] = $user_id;
            $data['title'] = $clipbin_title;
            $data['folder_id'] = $folder_id;
            $this->db_master->insert('lib_lb', $data);
            $clipbin_id = $this->db_master->insert_id();
            // reset default Clipbin
            $this->db_master->where('is_default', 1);
            $this->db_master->where('client_id', $user_id);
            $this->db_master->update('lib_lb', array('is_default' => 0));

            $this->db_master->where('id', $clipbin_id);
            $this->db_master->update('lib_lb', array('is_default' => 1));
        }
        return $clipbin_id;
    }

    function save_clipbin($login, $clipbin_title = '', $folder_id = 0, $bin_id)
    {
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id && $bin_id) {
            $data['client_id'] = $user_id;
            if ($clipbin_title)
                $data['title'] = $clipbin_title;
            $data['folder_id'] = $folder_id;
            $this->db_master->where('id', $bin_id);
            $this->db_master->update('lib_lb', $data);
            // reset default Clipbin
            $this->db_master->where('is_default', 1);
            $this->db_master->where('client_id', $user_id);
            $this->db_master->update('lib_lb', array('is_default' => 0));

            $this->db_master->where('id', $bin_id);
            $this->db_master->update('lib_lb', array('is_default' => 1));
        }
        return $bin_id;
    }

    function create_folder($login, $folder_name)
    {
        $folder_id = 0;
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id && $folder_name) {
            $data['client_id'] = $user_id;
            $data['name'] = $folder_name;
            $this->db_master->insert('lib_lb_folders', $data);
            $folder_id = $this->db_master->insert_id();
        }
        return $folder_id;
    }

    function save_folder($login, $folder_name, $folder_id)
    {
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id && $folder_id) {
            $data['client_id'] = $user_id;
            if ($folder_name)
                $data['name'] = $folder_name;
            $this->db_master->where('id', $folder_id);
            $this->db_master->update('lib_lb_folders', $data);
        }
        return $folder_id;
    }

    function rename_clipbin($clipbin_title, $clipbin_id)
    {
        $this->db_master->where('id', $clipbin_id);
        $this->db_master->update('lib_lb', array('title' => $clipbin_title));
        return true;
    }

    function copy_clipbin($login, $clipbin_title, $clipbin_id)
    {
        $new_clipbin_id = 0;
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id && $clipbin_title && $clipbin_id) {
            $data['client_id'] = $user_id;
            $data['title'] = $clipbin_title;
            $this->db_master->insert('lib_lb', $data);
            $new_clipbin_id = $this->db_master->insert_id();
            $clipbin_items = $this->db->get_where('lib_lb_items', array('lb_id' => $clipbin_id))->result_array();
            if ($new_clipbin_id && $clipbin_items) {
                foreach ($clipbin_items as $item) {
                    unset($item['id']);
                    $item['lb_id'] = $new_clipbin_id;
                    $this->db_master->insert('lib_lb_items', $item);
                }
            }
        }
        return $new_clipbin_id;
    }

    function delete_clipbin($clipbin_id)
    {
        if ($clipbin_id) {
            $clipbin = $this->db->get_where('lib_lb', array('id' => $clipbin_id))->result_array();
            $clipbin = $clipbin[0];
            $this->db_master->delete('lib_lb', array('id' => $clipbin_id));
            $this->db_master->delete('lib_lb_items', array('lb_id' => $clipbin_id));
            if ($clipbin && $clipbin['is_default']) {
                $user_clipbins = $this->db->get_where('lib_lb', array('client_id' => $clipbin['client_id']), 1)->result_array();
                if ($user_clipbins) {
                    $this->db_master->where('id', $user_clipbins[0]['id']);
                    $this->db_master->update('lib_lb', array('is_default' => 1));
                }
            }
        }
        return true;
    }

    function delete_folder($folder_id)
    {
        if ($folder_id) {
            $this->db_master->delete('lib_lb_folders', array('id' => $folder_id));
            $clipbins = $this->db_master->get_where('lib_lb', array('folder_id' => $folder_id))->result_array();
            if ($clipbins) {
                foreach ($clipbins as $clipbin) {
                    $this->delete_clipbin($clipbin['id']);
                }
            }
        }
        return true;
    }

    function move_clipbin_items($from_bin, $to_bin, $items_ids)
    {
        $res = true;
        if ($from_bin && $to_bin && $items_ids) {
            $this->db->where_in('item_id', $items_ids);
            $this->db->where('lb_id', $from_bin);
            $source_items = $this->db->get('lib_lb_items')->result_array();
            if ($source_items) {
                foreach ($source_items as $item) {
                    $row = $this->db->get_where('lib_lb_items', array('lb_id' => $to_bin, 'item_id' => $item['item_id']))->result_array();
                    if (!$row) {
                        unset($item['id']);
                        $item['lb_id'] = $to_bin;
                        $this->db_master->insert('lib_lb_items', $item);
                    }
                }
                $this->db_master->where_in('item_id', $items_ids);
                $this->db_master->where('lb_id', $from_bin);
                $this->db_master->delete('lib_lb_items');
            }
        }
        return $res;
    }

    function copy_clipbin_items($from_bin, $to_bin, $items_ids)
    {
        $res = true;
        if ($from_bin && $to_bin && $items_ids) {
            $this->db->where_in('item_id', $items_ids);
            $this->db->where('lb_id', $from_bin);
            $source_items = $this->db->get('lib_lb_items')->result_array();
            if ($source_items) {
                foreach ($source_items as $item) {
                    $row = $this->db->get_where('lib_lb_items', array('lb_id' => $to_bin, 'item_id' => $item['item_id']))->result_array();
                    if (!$row) {
                        unset($item['id']);
                        $item['lb_id'] = $to_bin;
                        $this->db_master->insert('lib_lb_items', $item);
                    }
                }
            }
        }
        return $res;
    }

    function get_widget_bins_list($login, $keyword = '')
    {
        $bins = array();
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id) {
            if ($keyword) {
                $bins = $this->db->query('
                  SELECT lbf.name, lb.*, COUNT(lbi.id) items_count FROM lib_lb lb
                  LEFT JOIN lib_lb_folders lbf ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lb.client_id = ? AND lb.title LIKE ? GROUP BY lb.id', array($user_id, '%' . $keyword . '%'))->result_array();
            } else {
                $bins = $this->db->query('
                  SELECT lbf.name, lb.*, COUNT(lbi.id) items_count FROM lib_lb lb
                  LEFT JOIN lib_lb_folders lbf ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lb.client_id = ? GROUP BY lb.id', array($user_id))->result_array();
            }
        }
        return $bins;
    }

    function get_widget_folders_list($login, $keyword = '')
    {
        $folders = array();
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id) {
            if ($keyword) {
                $res = $this->db->query('
                  SELECT lbf.name, lbf.id folder_id, lbf.expanded, lb.id, lb.is_default, lb.title, lbi.id item_id FROM lib_lb_folders lbf
                  LEFT JOIN lib_lb lb ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lbf.client_id = ? AND lb.title LIKE ?', array($user_id, '%' . $keyword . '%'))->result_array();
            } else {
                $res = $this->db->query('
                  SELECT lbf.name, lbf.id folder_id, lbf.expanded, lb.id, lb.is_default, lb.title, lbi.id item_id FROM lib_lb_folders lbf
                  LEFT JOIN lib_lb lb ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lbf.client_id = ?', array($user_id))->result_array();
            }

            if ($res) {
                foreach ($res as $item) {
                    if (!isset($folders[$item['folder_id']]))
                        $folders[$item['folder_id']] = array(
                            'id' => $item['folder_id'],
                            'name' => $item['name'],
                            'expanded' => $item['expanded'],
                            'bins' => array()
                        );
                    if ($item['id']) {
                        if (!isset($folders[$item['folder_id']]['bins'][$item['id']]))
                            $folders[$item['folder_id']]['bins'][$item['id']] = array(
                                'id' => $item['id'],
                                'title' => $item['title'],
                                'items_count' => 0,
                                'folder_id' => $item['folder_id'],
                                'is_default' => $item['is_default']
                            );

                        if ($item['item_id'])
                            $folders[$item['folder_id']]['bins'][$item['id']]['items_count'] += 1;
                    }
                }
            }
        }
        return $folders;
    }

    function get_clipbin($id)
    {
        return $this->db->get_where('lib_lb', array('id' => $id))->result_array();
    }

    function get_clipbin_sequence($id)
    {
        return $this->db->get_where('lib_backend_lb', array('id' => $id))->result_array();
    }

    function get_folder($id)
    {
        return $this->db->get_where('lib_lb_folders', array('id' => $id))->result_array();
    }

    function get_clipbin_items_count($id)
    {
        $res = $this->db->query('
              SELECT COUNT(lbi.id) items_count FROM lib_lb lb
              INNER JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
              WHERE lb.id = ?', array($id))->result_array();
        return $res[0]['items_count'];
    }

    function get_no_folder_bins_list($login, $keyword = '')
    {
        $bins = array();
        $this->load->model('customers_model');
        $user_id = $this->customers_model->get_customer_id_by_login($login);
        if ($user_id) {
            if ($keyword) {
                $bins = $this->db->query('
                  SELECT lbf.name, lb.*, COUNT(lbi.id) items_count FROM lib_lb lb
                  LEFT JOIN lib_lb_folders lbf ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lb.client_id = ? AND lb.folder_id = 0 AND lb.title LIKE ? GROUP BY lb.id', array($user_id, '%' . $keyword . '%'))->result_array();
            } else {
                $bins = $this->db->query('
                  SELECT lbf.name, lb.*, COUNT(lbi.id) items_count FROM lib_lb lb
                  LEFT JOIN lib_lb_folders lbf ON lb.folder_id = lbf.id
                  LEFT JOIN lib_lb_items lbi ON lb.id = lbi.lb_id
                  WHERE lb.client_id = ? AND lb.folder_id = 0 GROUP BY lb.id', array($user_id))->result_array();
            }
        }
        return $bins;
    }

    function get_bin_items($bin_id)
    {
        $this->db->where('lb_id', $bin_id);
        $query = $this->db->get('lib_lb_items');
        $res = $query->result_array();
        return $res;
    }

    function get_setting($name)
    {
        $searchin_filter_query = $this->db->query("SELECT value FROM lib_settings WHERE name = '" . $name . "'")->result_array();
        $result = $searchin_filter_query[0]['value'];
        return $result;
    }

    function get_user_id_by_clip_id($clipId)
    {
        $row = $this->db->query('SELECT client_id FROM lib_clips WHERE id = ?', array($clipId))->result_array();
        return $row[0]['id'];
    }

    function guest_bin_save($guest_id, $bin = '')
    {
        $c = $this->db->get_where('lib_guests', array('guest_id' => $guest_id))->result_array();
        if (!empty($c[0]['guest_id'])) {
            $this->db_master->where('guest_id', $guest_id);
            $this->db_master->update('lib_guests', array('clipbin_serialized' => $bin));
        } else {
            $q = "INSERT INTO lib_guests (guest_id, clipbin_serialized) VALUES ('" . $guest_id . "', '" . $bin . "')";
            $this->db_master->query($q);
        }
        return (!empty($bin)) ? count(unserialize($bin)) : 0;
    }

    function guest_bin_get($guest_id)
    {
        $c = $this->db->get_where('lib_guests', array('guest_id' => $guest_id))->result_array();
        if (!empty($c[0]['guest_id'])) {
            return $c[0]['clipbin_serialized'];
        } else return '';
    }

    function set_guest_data($guest_id, $other = '')
    {
        $c = $this->db->get_where('lib_guests', array('guest_id' => $guest_id))->result_array();
        if (!empty($c[0]['guest_id'])) {
            $this->db_master->where('guest_id', $guest_id);
            $this->db_master->update('lib_guests', array('other_serialized' => $other));
        } else {
            $q = "INSERT INTO lib_guests (guest_id, other_serialized) VALUES ('" . $guest_id . "', '" . $other . "')";
            $this->db_master->query($q);
        }
        return $other;
    }

    function get_guest_data($guest_id)
    {
        $c = $this->db->get_where('lib_guests', array('guest_id' => $guest_id))->result_array();
        if (!empty($c[0]['guest_id'])) {
            return $c[0]['other_serialized'];
        } else return '';
    }

    /**
     * @param string $login login
     * @param int $folderId folder id
     * @param int $expanded 0,1 value for expanded flag
     *
     * @return bool true on success update, false if not
     */
    public function updateFolderExpandedFlag($login, $folderId, $expanded)
    {
        if (empty($login) || empty($folderId)) {
            return false;
        }

        $this->load->model('customers_model');
        $userId = $this->customers_model->get_customer_id_by_login($login);

        if (!$userId) {
            return false;
        }

        // convert expanded to [0|1] value
        $expanded = (int)(bool) $expanded;
        
        $this->db_master->update(
            'lib_lb_folders',
            ['expanded' => $expanded],
            ['id' => $folderId, 'client_id' => $userId]
        );
        
        return true;
    }

    /**
     * @param string $login login
     * @param int $expanded 0,1 value for expanded flag
     *
     * @return bool true on success update, false if not
     */
    public function updateAllUsersFoldersExpandedFlag($login, $expanded)
    {
        if (empty($login)) {
            return false;
        }

        $this->load->model('customers_model');
        $userId = $this->customers_model->get_customer_id_by_login($login);

        if (!$userId) {
            return false;
        }

        // convert expanded to [0|1] value
        $expanded = (int)(bool) $expanded;

        $this->db_master->update(
            'lib_lb_folders',
            ['expanded' => $expanded],
            ['client_id' => $userId]
        );

        return true;
    }
}
