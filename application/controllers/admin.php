<?php

class Admin extends CI_Controller {
	
	public function control_menu(){
		$this->load->model('groups_model');
		$providers_group_id = $this->groups_model->get_provider_group_id();
			
	}
	
}