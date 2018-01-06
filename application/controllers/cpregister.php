<?php if ( !defined( 'BASEPATH' ) ) die( 'Closed' );

/** @property cpregister_model $cpregister */

class CPRegister extends CI_Controller {

	private $_lang;
	private $error;
	private $settings;
	private $data = array(
		'title' => 'Content Provider registration'
	);
	private $template = 'form';
	private $templates = array(
		'form'       => 'cpregister/form',
		'complete'   => 'cpregister/complete',
		'confirm' => 'cpregister/confirm'
	);

	function __construct () {
		parent::__construct();
		$this->settings = $this->api->settings();
		$this->data[ 'lang' ] = $this->_lang = $this->uri->segment( 1 );
		$this->load->model( 'cpregister_model', 'cpregister' );
	}

	function Index () {
        $id=(int) $this->uri->segment( 5 );
        $this->load->model('users_model');
        $frontend=$this->users_model->GetFrontendByUserId($id);
		if ( $this->cpregister->CheckEmailConfirmRequest() ) {
			if ( $this->cpregister->ConfirmEmail() ) {
				$this->_ShowConfirmPage();
                header( 'Location: http://'.$frontend['host_name'].'/login?action=login&confirm=1', true, 302 );
			}
		} else {
            header( 'Location: http://'.$frontend['host_name'].'/login', true, 302 );
			/*if ( $this->cpregister->CheckPostRequest() ) {
				$this->_ProcessForm();
			}*/
		}
        header( 'Location: http://'.$frontend['host_name'].'/login', true, 302 );
		//$this->_Out();
	}

	function _ProcessForm () {
		if ( $this->cpregister->CheckFormData() ) {
			if ( $this->cpregister->ValidateProviderData() ) {
				$this->cpregister->AddNewTemporaryProvider();
				$this->cpregister->SendEmail();
				$this->_ShowCompletePage();
			} else {
				$this->error = TRUE;
			}
		} else {
			$this->error = TRUE;
		}
	}

	function _ShowConfirmPage () {
		$this->template = 'confirm';
	}

	function _ShowCompletePage () {
		$this->template = 'complete';
	}

	function _Out () {
		if ( TRUE === $this->error ) {
			if ( $this->template == 'form' ) {
				$this->data[ 'form' ] = $this->cpregister->GetFeedbackFormData();
				$this->data[ 'errors' ] = $this->cpregister->GetErrors();
				$this->data[ 'errors_json' ] = json_encode( $this->cpregister->GetErrors() );
				// echo '<pre>';
				// var_export( $this->cpregister->GetErrors() );
				// echo '</pre>';
			}
		}
		if ( isset( $this->templates[ $this->template ] ) ) {
			$this->load->view( $this->templates[ $this->template ], $this->data );
		}
	}

}