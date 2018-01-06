<?php

class Discounts extends AppController {

	var $id;
	var $error;

	function Discounts() {
		parent::AppController();
		$this->load->model('discounts_model');
		$this->discounts_model->id = intval($this->uri->segment(4));
  }

	#-----------------------------------------------------------------------------
	 
	function index()
	{
		show_404();
	}

	#-----------------------------------------------------------------------------
	 
	function view() {
		$this->path = 'Commerce / Discounts';
		$list = $this->discounts_model->get();
		$data = array('discounts' => $list);
		$content = $this->load->view('discounts/view', $data, true);
		$this->out($content, null, 1);
	}

	#------------------------------------------------------------------------------------------------

	function active()
	{
		$this->discounts_model->active();
		redirect('/discounts/view');
	}

	#------------------------------------------------------------------------------------------------

	function delete()
	{
		$this->discounts_model->delete();
		redirect('/discounts/view');
	}
	
	#-----------------------------------------------------------------------------

	function edit() {
		$this->path = 'Commerce / Discounts';
		
		if ($this->input->post('save')) {
      $this->discounts_model->save();
      redirect('discounts/view');
		}
		
		$this->discounts_model->load();
		$data = array(
      'discount' => $this->discounts_model
    );
		$content = $this->load->view('discounts/edit', $data, true);
		$this->out($content, null, 1);
	}

}
