<?php

/**
 * @property Clips_model $clips
 * @property Submissions_model $submissions
 * @property Users_model $users
 */

class Sendmail extends CI_Controller {

	const IDS_DELEMITER = '-';

	const TRANSCODE_COMPLETE = 'transcoding-complete';
    const TRANSCODE_ERROR = 'transcoding-error';
	const CLIPS_UNTUNED = 'clips-untuned';
	const USER_DOWNLOADS = 'user-downloads';
    const ORDER_DOWNLOADS = 'download-email';
	private $action;
	private $clip;
	private $clip_ids;
	private $provider;
	private $recipient;

	/* ****************************************************
	 * Внутренние методы
	 *
	 */

	function __construct () {
		parent::__construct();
		$this->load->helper( 'emailer' );
		$this->load->model( 'clips_model', 'clips' );
		$this->load->model( 'submissions_model', 'submissions' );
		$this->load->model( 'users_model', 'users' );
		//var_export( $this->uri->uri_to_assoc() ); die();
	}

	function _remap () {
		if ( !$this->GetRequestParams() ) {
			redirect();
		}
		switch ( $this->action ) {
			case self::TRANSCODE_COMPLETE:
				$this->Notification_TranscodeComplete();
				break;
            case self::TRANSCODE_ERROR:
                $this->Notification_TranscodeError();
                break;
			case self::CLIPS_UNTUNED:
				$this->Notification_ClipsUntuned();
				break;
			case self::USER_DOWNLOADS:
				$this->Notification_UserDownloads();
				break;
            case self::ORDER_DOWNLOADS:
                $this->Notification_OrderDownloads();
                break;
			default:
				redirect();
		}
		echo 'Done!';
		die();
	}

	function GetRequestParams () {
		$params = $this->uri->uri_to_assoc();
		if ( empty( $params ) ) {
			return FALSE;
		}
		if ( isset( $params[ 'action' ] ) ) {
			$this->action = $params[ 'action' ];
		}
		if ( isset( $params[ 'clip' ] ) ) {
			$this->clip = $params[ 'clip' ];
		}
		if ( isset( $params[ 'clips' ] ) ) {
			$this->clip_ids = $this->ParseClipsId( $params[ 'clips' ] );
		}
		if ( isset( $params[ 'provider' ] ) ) {
			$this->provider = $params[ 'provider' ];
		}
		if ( isset( $params[ 'recipient' ] ) ) {
			$this->recipient = $params[ 'recipient' ];
		}
        if ( isset( $params[ 'order' ] ) ) {
            $this->order = $params[ 'order' ];
        }
		return TRUE;
	}

	function ParseClipsId ( $ids ) {
		if ( empty( $ids ) ) {
			return array ();
		}
		$parts = explode( self::IDS_DELEMITER, $ids );
		if ( empty( $parts ) ) {
			return array ();
		}
		$result = array();
		foreach ( $parts as $value ) {
			$id = (int) $value;
			if ( $id != 0 ) {
				$result[] = $id;
			}
		}
		if ( empty( $result ) ) {
			return array ();
		}
		return array_flip( array_flip( $result ) );
	}

	function GetClipProvider ( $clip_id ) {
		$clip = $this->clips->get_clip( $clip_id );
		return $clip[ 'client_id' ];
	}

	function GetClipData ( $clip_id ) {
		return $this->clips->get_clip( $clip_id );
	}

	/* ****************************************************
	 * Методы действий
	 *
	 */

	function Notification_ClipsUntuned () {
		if ( empty( $this->clip_ids ) ) {
			return FALSE;
		}
		if ( $this->recipient == 'admin' ) {
			// Отправляем уведомление админу
			Emailer::GetInstance()
				->LoadTemplate( 'toadmin-clips-no-configured' )
				->TakeSenderSystem()
				->TakeRecipientAdmin();
			foreach ( $this->clip_ids as $id ) {
				Emailer::GetInstance()
					->SetTemplateValue( 'clip', $this->GetClipData( $id ) )
					->Send();
				usleep( 50000 );
			}
		} else {
			// Отправляем уведомление провайдеру
			Emailer::GetInstance()
				->LoadTemplate( 'toprovider-clips-no-configured' )
				->TakeSenderSystem()
				->TakeRecipientFromId( $this->recipient );
			foreach ( $this->clip_ids as $id ) {
				Emailer::GetInstance()
					->SetTemplateValue( 'clip', $this->GetClipData( $id ) )
					->Send();
				usleep( 50000 );
			}
		}
		Emailer::GetInstance()->Clear();
	}

	function Notification_TranscodeComplete () {
		$provider_id = $this->GetClipProvider( $this->clip );
		// Отправляем уведомление
		Emailer::GetInstance()
			->LoadTemplate( 'toprovider-transcode-complete' )
			->TakeSenderSystem()
			->TakeRecipientFromId( $provider_id )
			->SetTemplateValue( 'clip', $this->GetClipData( $this->clip ) )
			->Send();
		Emailer::GetInstance()->Clear();
	}

    function Notification_TranscodeError () {
        $provider_id = $this->GetClipProvider( $this->clip );
        // Отправляем уведомление
        Emailer::GetInstance()
            ->LoadTemplate( 'toprovider-transcode-error' )
            ->TakeSenderSystem()
            ->TakeRecipientFromId( $provider_id )
            ->SetTemplateValue( 'clip', $this->GetClipData( $this->clip ) )
            ->Send();
        Emailer::GetInstance()->Clear();
    }

    function Notification_OrderDownloads(){
        $this->load->model('invoices_model','im');
        $invoice = $this->im->get_invoice($this->order);
        if ($invoice['payment_method'] == 'Check'){echo false; exit();}
        $downloads=$this->im->get_download_page_generate($this->order);
        //file_put_contents( FCPATH . '___rest.api.log', __FUNCTION__.' -> '.$downloads, FILE_APPEND );
        Emailer::GetInstance()->LoadTemplate('touser-order-downloads')
            ->TakeSenderSystem()
            ->SetRecipientEmail($invoice['email'])
            ->SetTemplateValue('order', $invoice)
            ->SetTemplateValue('downloads', 'links', $downloads)
            ->SetMailType('html')
            ->Send();
        Emailer::GetInstance()->Clear();
        //file_put_contents( FCPATH . '___rest.api.log', __FUNCTION__.' -> SENT', FILE_APPEND );
        $this->im->change_download_email_status(array($invoice['id']), 'Sent');
    }

	function Notification_UserDownloads () {
		// Получить список пользователей с кол-вом загрузок
		$result = $this->db->query( "
			SELECT
				DISTINCT stat.user_login,
				( SELECT COUNT( tmp.id ) AS result FROM lib_clips_extra_statistic AS tmp WHERE tmp.user_login = stat.user_login AND tmp.action_type = 1 AND tmp.time > DATE_SUB( NOW(), INTERVAL 30 DAY ) ) AS downloads
			FROM
				lib_clips_extra_statistic AS stat
			WHERE
				stat.clip_id <> 0 AND
				stat.user_login <> ''
			ORDER BY downloads DESC"
		);
		$users = ( is_object( $result ) ) ? $result->result_array() : array();
		// Очищаем от пользователей с пустой статистикой, дополняем данные
		$this->load->model( 'users_model' );
		foreach ( $users as $key => $user ) {
			//if ( $user[ 'downloads' ] != 0 ) {
				if ( $user[ 'user_login' ] != 'guest' ) {
					$users[ $key ] = array_merge( $user, (array) $this->users->GetUserByLogin( $user[ 'user_login' ] ) );
				} else {
					$users[ $key ][ 'id' ] = '-';
					$users[ $key ][ 'login' ] = 'guest(s)';
					$users[ $key ][ 'lname' ] = '-';
					$users[ $key ][ 'fname' ] = '-';
				}
			//}
		}
		// Сформировать список для шаблона
		$list = NULL;
		foreach ( $users as $user ) {
			$id = $user[ 'id' ];
			$login = $user[ 'login' ];
			$downloads = $user[ 'downloads' ];
			$pid = ( !empty( $user[ 'provider_id' ] ) ) ? '[' . $user[ 'provider_id' ] . ']' : '[-]';
			if ( !empty( $id ) ) {
				$list .= "{$id} {$pid},\t{$login} ........................... {$downloads}" . PHP_EOL;
			}
		}
		// Отправить уведомление
		if ( empty( $list ) ) {
			return FALSE;
		}
		Emailer::GetInstance()
			->LoadTemplate( 'toadmin-user-downloads' )
			->TakeSenderSystem()
			->TakeRecipientAdmin()
			->SetTemplateValue( 'users', 'list', $list )
			->Send();
		Emailer::GetInstance()
			->Clear();
	}

}