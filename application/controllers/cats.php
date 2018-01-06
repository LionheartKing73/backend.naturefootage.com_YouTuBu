<?php

class Cats extends CI_Controller {

	var $id;
	var $types;
	var $type;
	var $langs;
	var $error;

	function __construct() {
        parent::__construct();

        $this->modules = array('categories', 'collections', 'shotreels');
        $this->types = array_flip($this->modules);
        $this->paths = array('Categories', 'Collections', 'Shot reels');

        $this->load->model('cats_model');

        $this->path = 'Library settings / Categories';
        $this->id = $this->uri->segment(4);
        $this->langs = $this->uri->segment(1);

        $this->type = $this->types[$this->uri->segment(2)];
        $this->module = $this->modules[$this->type];

        //$this->settings = $this->api->settings();
        $this->load->model('groups_model');
        $this->set_group();
    }

    function index() {

        $data['cats'] = $this->cats_model->get_cats_list($this->langs, true);
        $data['module'] = $this->module;
        $headers = array('Categories', 'Collection groups', 'Shot Reels');
        $type_names = array('categories', 'collections');
        $data['header'] = $headers[$this->type];
        $data['type_name'] = $type_names[$this->type];
        $data['lang'] = 'en';
        $content['title'] = $headers[$this->type] . ' - stock footage, video library';
        $content['meta_desc'] = $headers[$this->type] . ' - stock footage, '
            . $this->api->get_seo_keys(3, 2);
        $content['meta_keys'] = $headers[$this->type] . ', '
            . $this->api->get_seo_keys(3, 2);

        $content['body'] = $this->load->view('cats/content', $data, true);
        $content['add_css'] = '/data/css/cats.css';
        $this->out($content,null,0);
    }

    #------------------------------------------------------------------------------------------------

    function content() {
        $uri_parts = explode('-', $this->uri->segment(3));
        $this->id = count($uri_parts) ? intval($uri_parts[count($uri_parts) - 1]) : 0;

        $data['cats'] = $this->cats_model->get_subcats($this->id, $this->langs, true);
        $data['module'] = $this->module;

        $headers = array('Category', 'Collection group', 'Shot Reel');
        $backs = array('categories', 'collection groups', 'shot reels');
        $cat = $this->cats_model->get_cat($this->id, $this->langs);

        $data['header'] = $headers[$this->type] . ' ' . $cat['title'];
        $data['cat_description'] = $cat['description'];

        $data['back'] = $backs[$this->type];
        $data['subcats'] = 1;
        $data['lang'] = $this->langs;

        $content['title'] = $this->lang->line('categ');
        $content['title'] = $data['header'] . ' - stock footage, video library';
        $content['meta_desc'] = $data['header'] . ' - stock footage, '
            . $this->api->get_seo_keys(3, 2);
        $content['meta_keys'] = $data['header'] . ', '
            . $this->api->get_seo_keys(3, 2);

        if ($this->module == 'cats') {
            $rand_subcats = $this->cats_model->get_subcats($this->id, $this->langs, true, 3);
            if (count($rand_subcats)) {
                shuffle($rand_subcats);
                foreach ($rand_subcats as $subcat) {
                    $content['meta_desc'] .= ', ' . $subcat['title'];
                    $content['meta_keys'] .= ', ' . $subcat['title'];
                }
            }
        }

        $content['body'] = $this->load->view('cats/content', $data, true);
        $content['add_css'] = '/data/css/cats.css';
        $this->out($content,null,0);
    }

    #------------------------------------------------------------------------------------------------


    function view() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        if($group['is_editor'] && $uid){
            $data['cats'] = $this->cats_model->get_cats_list($this->langs, false, false, (int)$uid);
        }
        else{
		    $data['cats'] = $this->cats_model->get_cats_list($this->langs);
        }
		$data['uri'] = $this->api->prepare_uri();
		$data['lang'] = $this->langs;

		$content = $this->load->view('cats/view', $data, true);
		$this->out($content);
	}

	function get_limit() {
		return ' limit ' . intval($this->uri->segment(4)) . ',' . $this->settings['perpage'];
	}

	function ord() {
		$this->cats_model->change_ord($this->input->post('ord'));
		$this->api->log('log_cats_ord');
		redirect($this->langs . '/cats/view');
	}

	function visible() {
		$this->cats_model->change_visible($this->input->post('id'));
		$this->api->log('log_cats_visible', $this->input->post('id'));
		redirect($this->langs . '/cats/view');
	}

	function delete() {
		if ($this->id) {
			$ids[] = $this->id;
		} else {
			$ids = $this->input->post('id');
		}

		$this->api->log('log_cats_delete', $ids);
		$this->cats_model->delete_cat($ids);
		redirect($this->langs . '/cats/view');
	}

	function edit() {
		$cat_id = intval($this->uri->segment(4));

        $check = $this->cats_model->get_cat($cat_id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if($this->input->post('delete')) {
                $this->cats_model->delete_image($this->input->post('sid'));
            }

            if ($this->input->post('save') && $this->check_details()) {
                $sub_id = $this->cats_model->save_cat($this->id, $this->langs);

                if ($this->id) {
                    $this->api->log('log_cats_edit', $this->id);
                } else {
                    $this->api->log('log_cats_new');
                }

                $this->cats_model->upload_image($sub_id);

                if (!$this->error) {
                    redirect($this->langs . '/cats/view');
                }
            }

            $data = $_POST;

            if (!$this->error) {
                $data = $this->cats_model->get_cat($this->id, $this->langs);
                $data['picture'] = $this->cats_model->get_image_path($data);
            }

            $data['parents'] = $this->cats_model->get_parents($this->id, $this->langs, $filter);
            $data['id'] = ($this->id) ? $this->id : '';
            $data['lang'] = $this->langs;

            $action = $this->id ? 'Edit' : 'Add';
            $this->path .= ' / ' . $action;

            $content = $this->load->view('cats/edit', $data, true);
            $this->out($content);
        }
        else{
            redirect($this->langs . '/cats/view');
        }
	}

	function items() {
		if ($this->input->post('add')) {
			$result = $this->cats_model->add_item($this->id, $this->input->post('code'));
			if ($result) {
				$this->message = 'Added.';
			}
		} elseif ($this->input->post('del')) {
			$this->cats_model->remove_items($this->id, $this->input->post('id'));
		} elseif ($this->input->post('order')) {
			$this->cats_model->save_items_order($this->id, $this->input->post('ord'));
		}

		$data['items'] = $this->cats_model->get_items($this->id);
		$data['id'] = $this->id;
		$data['lang'] = $this->langs;

		$this->path .= ' / Items';

		$content = $this->load->view('cats/items', $data, true);
		$this->out($content);
	}

	function check_details() {
		if (!$this->input->post('title')) {
			$this->error = $this->lang->line('empty_fields');
			return false;
		}
		return true;
	}

	function out($content = null, $pagination = null, $type = 1) {
		$this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
			'error' => $this->error, 'message' => $this->message), $type);
	}

    function set_group() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

}
