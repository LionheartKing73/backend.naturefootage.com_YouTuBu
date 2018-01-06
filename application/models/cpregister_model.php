<?php if ( !defined( 'BASEPATH' ) ) die( 'Closed' );

/**
 * @property register_model $register
 * @property users_model    $users
 */

class cpregister_model extends CI_Model {

	private $mandatory_fileds = array( 'login', 'name', 'surname', 'email', 'sitename', 'sitedomain');
	private $form_data = array();
	private $confirm_data = array();
	private $mask_email = "/[a-z0-9.-_]+(@)[a-z0-9]+(.)[a-z]+/i";
	private $mask_domain = '/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/i';
	private $mask_other = "/[^a-z0-9.-_]+/i";
	private $errors = array();

	function __construct () {
		parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
		$this->load->model( 'users_model', 'users' );
		$this->load->helper( 'emailer' );
	}

	function GetErrors () {
		return $this->errors;
	}

	function CheckPostRequest () {
		$submit = $this->input->post( 'register' );
		if ( $submit ) {
			return TRUE;
		}
		$this->SetError( 'no-request' );
		return FALSE;
	}

	function CheckFormData () {
		$result = TRUE;
		// Проверяем наличие данных
		foreach ( $this->GetFormData() as $field => $value ) {
			if ( empty( $value ) ) {
				$this->SetError( 'empty-field', $field );
				$result = FALSE;
			}
			$this->form_data[ $field ] = $value;
		}
		if ( !$result ) return FALSE;
		// Проверяем спецсимволы
		foreach ( $this->GetFormData() as $name => $value ) {
			switch ( $name ) {
				case 'email':
					if ( 0 == preg_match( $this->mask_email, $value ) ) {
						$this->SetError( 'wrong-email', $name );
						$result = FALSE;
						break 1;
					}
					break;
				case 'sitedomain':
                    if ( 0 == preg_match( $this->mask_domain, $value ) ) {
                        $this->SetError( 'wrong-sitedomain', $name );
                        $result = FALSE;
                        break 1;
                    }
                    break;
                case 'sitename':
                    break;
				default:
					if ( 0 < preg_match( $this->mask_other, $value ) ) {
						$this->SetError( 'illegal-characters', $name );
						$result = FALSE;
						break 1;
					}
					break;
			}
		}
		if ( !$result ) return FALSE;
		// Все в порядке
		return TRUE;
	}

	function GetFeedbackFormData () {
		return $this->GetFormData();
	}

	function ValidateProviderData () {
		$result = TRUE;
		if ( !$this->IsAvailableEmail( $this->form_data[ 'email' ] ) ) {
			$this->SetError( 'email-busy', 'email' );
			$result = FALSE;
		}
		if ( !$this->IsAvailableLogin( $this->form_data[ 'login' ] ) ) {
			$this->SetError( 'login-busy', 'login' );
			$result = FALSE;
		}
		if ( !$this->IsAvailableSitename( $this->form_data[ 'sitename' ] ) ) {
			$this->SetError( 'sitename-busy', 'sitename' );
			$result = FALSE;
		}
        if ( !$this->IsAvailableSitedomain( $this->form_data[ 'sitedomain' ] ) ) {
            $this->SetError( 'sitedomain-busy', 'sitedomain' );
            $result = FALSE;
        }
		return $result;
	}

	function SendEmail () {
		$data = $this->GetFormData();
		$emailer = Emailer::GetInstance();
		$emailer->LoadTemplate( 'toprovider-email-verification' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientFromLogin( $data[ 'login' ] );
		$link = $this->CreateActivateLink();
		$emailer->SetTemplateValue( 'verification', 'link', $link );
		$emailer->Send();
		$emailer->Clear();
	}

	function AddNewTemporaryProvider () {
		$data = $this->GetFormData();
		$userdata = array();
		$userdata[ 'provider_id' ] = 0;
		$userdata[ 'group_id' ] = $this->GetContentProviderGroupId();
		$userdata[ 'login' ] = $data[ 'login' ];
		$userdata[ 'fname' ] = $data[ 'name' ];
		$userdata[ 'lname' ] = $data[ 'surname' ];
		$userdata[ 'email' ] = $data[ 'email' ];
		$userdata[ 'sitename' ] = $data[ 'sitename' ];
		$userdata[ 'sitedomain' ] = $data[ 'sitedomain' ];
		$userdata[ 'prefix' ] = $this->CreateProviderPrefix();
		$userdata[ 'password' ] = $this->CreatePassword();
		$this->db_master->insert( 'lib_users', $userdata );
	}

	function CheckEmailConfirmRequest () {
		$data = $this->GetEmailConfirmData();
		if ( $data[ 'method' ] != 'index' ) return FALSE;
		if ( $data[ 'type' ] != 'active' ) return FALSE;
		if ( empty( $data[ 'id' ] ) ) return FALSE;
		if ( empty( $data[ 'hash' ] ) ) return FALSE;
		return TRUE;
	}

	function ConfirmEmail () {
		$data = $this->GetEmailConfirmData();
		if ( $this->CheckProviderActivateHash( $data[ 'id' ], $data[ 'hash' ] ) ) {
			$this->ActivateProvider( $data[ 'id' ], $data[ 'hash' ] );
			$this->SendAdminEmail();
			//$this->SendProviderEmail();
			return TRUE;
		}
		return FALSE;
	}

	private function GetContentProviderGroupId () {
		$result = $this->db->query( "SELECT id FROM lib_users_groups WHERE active = 1 AND is_editor = 1 AND is_backend = 1 LIMIT 1" );
		return ( is_object( $result ) && $data = $result->row_array() ) ? $data[ 'id' ] : FALSE;
	}

	private function IsAvailableSitename ( $sitename ) {
		$result = $this->db->where( 'sitename', $sitename )->get( 'lib_users', 1 );
		$result = ( is_object( $result ) ) ? $result->row_array() : array();
		return empty( $result );
	}

    private function IsAvailableSitedomain ( $sitename ) {
		$result = $this->db->where( 'sitedomain', $sitename )->get( 'lib_users', 1 );
		$result = ( is_object( $result ) ) ? $result->row_array() : array();
		return empty( $result );
	}

	private function IsAvailableEmail ( $email ) {
		$result = $this->db->where( 'email', $email )->get( 'lib_users', 1 );
		$result = ( is_object( $result ) ) ? $result->row_array() : array();
		return empty( $result );
	}

	private function IsAvailableLogin ( $login ) {
		$result = $this->db->where( 'login', $login )->get( 'lib_users', 1 );
		$result = ( is_object( $result ) ) ? $result->row_array() : array();
		return empty( $result );
	}

	private function IsAvailablePrefix ( $prefix ) {
		$result = $this->db->where( 'prefix', $prefix )->get( 'lib_users', 1 );
		$result = ( is_object( $result ) ) ? $result->row_array() : array();
		return empty( $result );
	}

	private function CreateProviderPrefix () {
		$data = $this->GetFormData();
		$name = $data[ 'name' ];
		$surname = $data[ 'surname' ];
		$first = $name{0};
		$second = $surname{0};
		if ( $this->IsAvailablePrefix( $first . $second ) ) {
			return strtoupper( $first . $second );
		}
		$first .= $name{1};
		if ( $this->IsAvailablePrefix( $first . $second ) ) {
			return strtoupper( $first . $second );
		}
		$second .= $surname{1};
		if ( $this->IsAvailablePrefix( $first . $second ) ) {
			return strtoupper( $first . $second );
		}
		$first .= $name{2};
		if ( $this->IsAvailablePrefix( $first . $second ) ) {
			return strtoupper( $first . $second );
		}
		$first{2} = NULL;
		$second .= $surname{2};
		if ( $this->IsAvailablePrefix( $first . $second ) ) {
			return strtoupper( $first . $second );
		}
		throw new Exception( 'CP prefix creation error' );
	}

	function CreateActivateLink ($id=false) {
		if(empty($id)){
			$data = $this->GetFormData();
			$provider = $this->users->GetUserByLogin( $data[ 'login' ] );
			$id = $provider[ 'id' ];
		}
		$hash = md5( $id . microtime() );
		$link = base_url( "/en/cpregister/index/active/{$id}/{$hash}" );
		$this->SaveActivateHash( $id, $hash );
		return $link;
	}

	private function SaveActivateHash ( $provider_id, $hash ) {
		$this->db_master->insert( 'lib_provider_email_hash', array( 'hash' => $hash, 'provider_id' => $provider_id ) );
	}

	private function CheckProviderActivateHash ( $provider_id, $hash ) {
		$result = $this->db->query( "SELECT id FROM lib_provider_email_hash WHERE provider_id = {$provider_id} AND hash = '{$hash}' LIMIT 1" );
		if ( is_object( $result ) ) {
			$data = $result->row_array();
			if ( !empty( $data[ 'id' ] ) ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	private function CreatePassword () {
		$symbols = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
			'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
			'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0' );
		$password = NULL;
		for ( $i = 0; $i < 12; $i++ ) $password .= $symbols[ rand( 0, count( $symbols ) - 1 ) ];
		return $password;
	}

	private function SetError ( $code, $element = FALSE ) {
		$error = array();
		$error[ 'code' ] = $code;
		if ( $element !== FALSE ) {
			$error[ 'element' ] = $element;
		}
		$this->errors[ ] = $error;
	}

	private function GetFormData () {
		if ( empty( $this->form_data ) ) {
			foreach ( $this->mandatory_fileds as $field ) {
				$value = $this->input->post( $field, TRUE );
				if ( empty( $value ) ) {
					$this->form_data[ $field ] = NULL;
				} else {
					$this->form_data[ $field ] = $value;
				}
			}
		}
		return $this->form_data;
	}

	private function GetEmailConfirmData () {
		if ( empty( $this->confirm_data ) ) {
			$this->confirm_data[ 'method' ] = strtolower( (string) $this->uri->segment( 3 ) );
			$this->confirm_data[ 'type' ] = strtolower( (string) $this->uri->segment( 4 ) );
			$this->confirm_data[ 'id' ] = (int) $this->uri->segment( 5 );
			$this->confirm_data[ 'hash' ] = strtolower( (string) $this->uri->segment( 6 ) );
		}
		return $this->confirm_data;
	}

	private function ActivateProvider ( $provider_id, $hash ) {
		$this->db_master->query("DELETE FROM lib_provider_email_hash WHERE provider_id = {$provider_id} AND hash = '{$hash}'" );
		$this->db_master->query("UPDATE lib_users SET active = 1 WHERE id = {$provider_id}" );
	}

	private function SendAdminEmail () {
		$data = $this->GetEmailConfirmData();
		$emailer = Emailer::GetInstance();
		$emailer->LoadTemplate( 'toadmin-provider-registered' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientAdmin();
		$emailer->SetTemplateValue( 'provider', $this->users->get_user( $data[ 'id' ] ) );
        $emailer->SetTemplateValue( 'link','edit', 'http://'.$_SERVER['HTTP_HOST'].'/en/users/edit/'.$data[ 'id' ] );
		$emailer->Send();
		$emailer->Clear();
	}

	private function SendProviderEmail () {
		$data = $this->GetEmailConfirmData();
		$emailer = Emailer::GetInstance();
		$emailer->LoadTemplate( 'toprovider-registered' );
		$emailer->TakeSenderSystem();
		$emailer->TakeRecipientFromId( $data[ 'id' ] );
		$emailer->SetTemplateValue( 'provider', $this->users->get_user( $data[ 'id' ] ) );
		$emailer->Send();
		$emailer->Clear();
	}

}