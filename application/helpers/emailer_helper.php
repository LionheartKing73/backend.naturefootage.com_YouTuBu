<?php if ( !defined( 'BASEPATH' ) ) die( 'Closed' );

/**
 * Class Emailer : Отправка почтовых сообщений
 *
 * Использование:
 *
 * 		$this->load->helper( 'emailer' );                               // Подключаем хэлпер с Емейлером
 *      $result = Emailer::GetInstance()
 *              ->LoadTemplate( 'registration-confirmation-user' )      // Загружаем шаблон с БД по названию
 * 			    ->TakeSenderSystem()                                    // Устанавливаем отправителем систему
 * 			    ->TakeRecipientFromLogin( 'User' )                      // Устанавливаем получателем пользователя с БД, по логину
 * 			    ->Send();                                               // Отправляем
 *      Emailer::GetInstance()->Clear();                                // Очищаем данные
 *
 * Методы:
 *
 * 		GetInstance()                               // Получить объект
 * 		Clear()                                     // Очистить все данные
 * 		LoadTemplate( $tpl_name )                   // Загрузить шаблон с БД, по названию
 * 		TakeRecipientFromId( $id )                  // Установить получателем пользователя, рпо ID
 * 		TakeRecipientFromLogin( $login )            // Установить получателем пользователя, по логину
 * 		TakeSenderFromId( $id )                     // Установить отправителем пользователя, по ID
 * 		TakeSenderFromLogin( $login )               // Установить отправителем пользователя, по логину
 * 		Send()                                      // Отправить сообщение
 * 		SetSenderEmail( $email )                    // Установить адрес отправителя
 * 		SetRecipientEmail( $email )                 // Установить адрес получателя
 * 		SetSenderFullname( $name )                  // Установить имя отправителя
 * 		SetSubject( $subject )                      // Установить тему сообщения
 * 		SetTemplate( $tpl_data )                    // Установить шаблон вручную
 * 		TakeSenderSystem()                          // Установить отправителем систему
 * 		SetTemplateValue( $group, $name, $value )   // Установить данные - SetTemplateValue( 'recepient', 'login', 'Вася Пупкин' ), это для тэга {{ recepient.login }}
 *      TakeRecipientAdmin()                        // Установить получателем администратора
 *
 */

class Emailer {

	private static $logger_enable = TRUE;
	private static $uniq_id;

	/**
	 * Таблица с данными шаблонов
	 */
	const TBL_TEMPLATES = 'lib_email_templates';
	/**
	 * Таблица с данными пользователей
	 */
	const TBL_USERS = 'lib_users';
	/**
	 * @var Emailer
	 */
	private static $emailer;
	/**
	 * @var CI_Controller
	 */
	private $CI;
	/**
	 * @var string
	 */
	private $template;
    /**
     * @var string
     */
    private $template_html;
	/**
	 * @var string
	 */
	private $sender_email;
	/**
	 * @var string
	 */
	private $recipient_email;

    /**
     * @var string
     */
    private $cc;
    /**
     * @var string
     */
    private $to;
    /**
     * @var string
     */
    private $bcc;
	/**
	 * @var string
	 */
	private $sender_name;
	/**
	 * @var string
	 */
	private $subject;
	/**
	 * @var array
	 */
	private $values = array(
		'recipient' => NULL,
		'sender'    => NULL,
		'system'    => NULL,
	);

    private $mailtype = 'text';

    private $attaches = array();

	/**
	 * __construct
	 */
	private function __construct () {
		$this->CI = & get_instance();
        /*$config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'support@footagesearch.com',
            'smtp_pass' => '810f00tage',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'wordwrap' => TRUE
        );*/
		$this->CI->load->library( 'email' );
		$this->CI->load->library( 'stwig' );
		$this->_SetSystemData();
	}

	private function __clone () {}

	private static function ___Logger ( $message ) {
		if ( self::$logger_enable == TRUE ) {
			if ( empty( self::$uniq_id ) ) {
				self::$uniq_id = rand( 1000, 9999 );
			}
			$string = PHP_EOL . self::$uniq_id . ' : ' .$message . ' : ' .  date( 'd.m.Y H:i:s' ) . PHP_EOL;
			//file_put_contents( realpath(FCPATH . '___emailer.log'), $string, FILE_APPEND );
		}
	}

	/**
	 * Получить объект
	 *
	 * @return Emailer
	 */
	static function GetInstance () {
		if ( empty( self::$emailer ) ) {
			self::$emailer = new Emailer;
		}
		self::___Logger( __METHOD__ );
		return self::$emailer;
	}
	/**
	 * Получить объект (сокращенный вариант)
	 *
	 * @return Emailer
	 */
	static function In () {
		return self::GetInstance();
	}

	/**
	 * Очистить все данные, кроме системных
	 */
	function Clear () {
		$this->template = NULL;
        $this->template_html = NULL;
		$this->sender_email = NULL;
		$this->recipient_email = NULL;
		$this->sender_name = NULL;
		$this->subject = NULL;
		$this->attaches = array();
		$values = array ();
		$values[ 'system' ] = $this->values[ 'system' ];
		$values[ 'recipient' ] = NULL;
		$values[ 'sender' ] = NULL;
		$this->values = $values;
		self::___Logger( __METHOD__ );
	}

	/**
	 * Установить шаблон из БД, по названию
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	function LoadTemplate ( $name ) {
		$template = $this->_GetTemplate( $name );
		if ( !empty( $template ) ) {
			$this->template = $template[ 'body' ];//($template[ 'is_html' ]) ?$template[ 'body_html' ]:$template[ 'body' ];
            $this->template_html =$template[ 'body_html' ];
			$this->subject = $template[ 'subject' ];
            $this->mailtype = ($template[ 'is_html' ]) ? 'html' : 'text';
            $this->bcc = $template[ 'bcc' ];
            $this->to = $template[ 'to' ];
		}
		self::___Logger( __METHOD__ . ' ' . $name );
		return $this;
	}

    /**
     * Получить распарсеный шаблон
     *
     * @param $name string
     *
     * @return $this
     */
    function GetParsedTemplate () {
        $this->_ParseTemplate();
        self::___Logger( __METHOD__ );
        return array('body' => $this->template_html, 'subject' => $this->subject);
    }

	/**
	 * Установить получателем пользователя, по ID
	 *
	 * @param $id integer
	 *
	 * @return $this
	 */
	function TakeRecipientFromId ( $id ) {
		$this->_SetRecipientData( $this->_GetUserFromId( $id ) );
		self::___Logger( __METHOD__ . ' ' . $id );
		return $this;
	}

	/**
	 * Установить получателем администратора
	 *
	 * @return $this
	 */
	function TakeRecipientAdmin () {
		self::___Logger( __METHOD__ );
		$this->recipient_email = $this->values[ 'system' ][ 'admin_email' ];
		return $this;
	}

	/**
	 * Установить получателем пользователя, по логину
	 *
	 * @param $login string
	 *
	 * @return $this
	 */
	function TakeRecipientFromLogin ( $login ) {
		self::___Logger( __METHOD__ . ' ' . $login );
		$this->_SetRecipientData( $this->_GetUserFromLogin( $login ) );
		return $this;
	}

	/**
	 * Установить отправителем пользователя, по ID
	 *
	 * @param $id integer
	 *
	 * @return $this
	 */
	function TakeSenderFromId ( $id ) {
		self::___Logger( __METHOD__ . ' ' . $id );
		$this->_SetSenderData( $this->_GetUserFromId( $id ) );
		return $this;
	}

	/**
	 * Установить отправителем пользователя, по логину
	 *
	 * @param $login string
	 *
	 * @return $this
	 */
	function TakeSenderFromLogin ( $login ) {
		self::___Logger( __METHOD__ . ' ' . $login );
		$this->_SetSenderData( $this->_GetUserFromLogin( $login ) );
		return $this;
	}

	/**
	 * Отправить письмо
	 *
	 * @return bool
	 */
	function Send () {
		$this->_ParseTemplate();
		if ( $this->_CheckSendingPossibility() ) {
			$this->_Send();
			self::___Logger( __METHOD__ . ' OK' );
			return TRUE;
		}
		self::___Logger( __METHOD__ . ' ERROR' );
		return FALSE;
	}

	/**
	 * Установить адрес отправителя
	 *
	 * @param $email string
	 *
	 * @return $this
	 */
	function SetSenderEmail ( $email ) {
		self::___Logger( __METHOD__ . ' ' . $email );
		$this->sender_email = $email;
		return $this;
	}

	/**
	 * Установить адрес получателя
	 *
	 * @param $email string
	 *
	 * @return $this
	 */
	function SetRecipientEmail ( $email ) {
		self::___Logger( __METHOD__ . ' ' . $email );
		$this->recipient_email = $email;
		return $this;
	}

    /**
     * Установить адреса Carbon Copied получателей
     *
     * @param $email string
     *
     * @return $this
     */
    function SetCC ( $cc ) {
        self::___Logger( __METHOD__ . ' ' . $cc );
        $this->cc = $cc;

        return $this;
    }

	/**
	 * Установить имя отправителя
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	function SetSenderFullname ( $name ) {
		self::___Logger( __METHOD__ . ' ' . $name );
		$this->sender_name = $name;
		return $this;
	}

	/**
	 * Установить тему письма
	 *
	 * @param $subject string
	 *
	 * @return $this
	 */
	function SetSubject ( $subject ) {
		self::___Logger( __METHOD__ . ' ' . $subject );
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Установить шаблон для письма
	 *
	 * @param $template string
	 *
	 * @return $this
	 */
	function SetTemplate ( $template ) {
		self::___Logger( __METHOD__ . ' ' . $template );
		$this->template_html = $template;
		return $this;
	}

	/**
	 * Установить отправителем систему
	 *
	 * @return $this
	 */
	function TakeSenderSystem () {
		self::___Logger( __METHOD__ );
		$this->sender_email = $this->values[ 'system' ][ 'email' ];
		$this->sender_name = $this->values[ 'system' ][ 'brand' ];
		return $this;
	}

	/**
	 * Установить данные для шаблона
	 *
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 *
	 * @return $this
	 */
	function SetTemplateValue ( $param1, $param2 = NULL, $param3 = NULL ) {
		self::___Logger( __METHOD__ );
		/*
		 * Прямая установка массива данных
		 *
		 *  $param1 = array (
		 *      'group1' => array (
		 *          'name1' => $value1,
		 *          'name2' => $value2
		 *      ),
		 *      'group2' => ...
		 *  );
		 */
		if ( is_array( $param1 ) && !empty( $param1 ) ) {
			$this->_SetTemplateArray( $param1 );
		} /*
		 * Установка определенного массива данных
		 *
		 *  $param1 = 'group';
		 *  $param2 = array (
		 *      'name1' => $value1,
		 *      'name2' => $value2
		 *  );
		 *
		 */
		elseif ( !empty( $param1 ) && is_array( $param2 ) && !empty( $param2 ) ) {
			$this->_SetTemplateGroupArray( $param1, $param2 );
		} /*
		 * Установка значения определенного массива данных
		 *
		 *  $param1 = 'group';
		 *  $param2 = 'name';
		 *  $param3 = $value;
		 */
		elseif ( !empty( $param1 ) && !empty( $param2 ) && !empty( $param3 ) ) {
			$this->_SetTemplateValue( $param1, $param2, $param3 );
		} else {
			return FALSE;
		}
		return $this;
	}

    public function SetMailType($type = 'text') {
        $this->mailtype = ($type == 'html') ? 'html' : 'text';
        return $this;
    }

    public function Attach($file) {
        $this->attaches[] = $file;
        return $this;
    }

	/**
	 * Прямая установка массива данных
	 *
	 * @param array $array
	 */
	private function _SetTemplateArray ( $array ) {
		$this->values = array_merge( $this->values, $array );
	}

	/**
	 * Установка определенного массива данных
	 *
	 * @param string $group
	 * @param array $array
	 */
	private function _SetTemplateGroupArray ( $group, $array ) {
		$this->values[ $group ] = $array;
	}

	/**
	 * Установка значения определенного массива данных
	 *
	 * @param string $group
	 * @param string $name
	 * @param mixed $value
	 */
	private function _SetTemplateValue ( $group, $name, $value ) {
		$this->values[ $group ][ $name ] = $value;
	}

	/**
	 * Проверить, есть ли все данные для отправки письма
	 *
	 * @return bool
	 */
	private function _CheckSendingPossibility () {
		if ( empty ( $this->sender_email ) ) {
			return FALSE;
		}
		if ( empty ( $this->recipient_email ) ) {
			return FALSE;
		}
		if ( empty ( $this->subject ) ) {
			return FALSE;
		}
		if ( empty ( $this->template ) ) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Отправляем сообщение
	 */
	private function _Send () {
        //TEXT--------------------------------------
		/*$this->CI->email->from( $this->sender_email, $this->sender_name );
		$this->CI->email->to( $this->recipient_email );
		$this->CI->email->subject( $this->subject );
		$this->CI->email->message( $this->template."<br>\n\t".$this->template_html );
		$this->CI->email->set_mailtype( 'text' );//$this->mailtype );
        if($this->cc){
            $this->CI->email->cc( $this->cc );
        }
        if($this->attaches) {
            foreach($this->attaches as $attach) {
                $this->CI->email->attach($attach);
            }
        }
		$this->CI->email->send();*/
        //HTML---------------------------------------
        /*$this->CI->email->from( $this->sender_email, $this->sender_name );
        //$this->CI->email->to( $this->recipient_email.",support@naturefootage.com" );
        $this->CI->email->to( $this->recipient_email );
        //$this->CI->email->to( $this->recipient_email );
        $this->CI->email->subject( $this->subject );
        $this->CI->email->message( $this->template_html );
        $this->CI->email->set_mailtype( 'html' );


        if($this->cc){
            $this->CI->email->cc( $this->cc );
        }
        if($this->attaches) {
            foreach($this->attaches as $attach) {
                $this->CI->email->attach($attach);
            }
        }
        $this->CI->email->send();

        // Send Additional recipients emails
        $this->_sendAdditionalRecipients();

        $this->CI->email->from( $this->sender_email, $this->sender_name );
        //$this->CI->email->to( $this->recipient_email.",support@naturefootage.com" );
        //$this->CI->email->to( "support@naturefootage.com" );
        $this->CI->email->to( "confirmation@naturefootage.com                                                                              " );
        //$this->CI->email->to( $this->recipient_email );
        $this->CI->email->subject( $this->subject );
        $this->CI->email->message( $this->template_html );
        $this->CI->email->set_mailtype( 'html' );


        if($this->cc){
            $this->CI->email->cc( $this->cc );
        }
        if($this->attaches) {
            foreach($this->attaches as $attach) {
                $this->CI->email->attach($attach);
            }
        }
        $this->CI->email->send();*/

        // New send ----------------------------------------------------------------------------------
        $this->_buildRecipients();
        if(!empty($this->to)){
            $recipients=explode(',',$this->to);
			$testEmail='RECIPIENTS:'.json_encode($recipients).PHP_EOL;
            foreach($recipients as $recipient){
                $this->CI->email->from( $this->sender_email, $this->sender_name );
                $this->CI->email->to( trim($recipient) );
                $this->CI->email->subject( $this->subject );
                $this->CI->email->message( $this->template_html );
                $this->CI->email->set_mailtype( 'html' );
                if($this->cc){
                    $this->CI->email->cc( $this->cc );
                }
                if($this->attaches) {
                    foreach($this->attaches as $attach) {
                        $this->CI->email->attach($attach);
                    }
                }
                $status=$this->CI->email->send();
				$status=($status)?'ok':'fail';
				$testEmail.='TO:'.trim($recipient).' SEND STATUS:'.$status.PHP_EOL;
            }
			$testEmail.='--------------------------------------------------------------'.PHP_EOL;
			//file_put_contents( FCPATH . '___rest.api.log', $testEmail.PHP_EOL, FILE_APPEND );
			//mail('dmitriy.klovak@boldendeavours.com','testEmail',$testEmail);
        }

	}

	/**
	 * Парсим данные шаблонов
	 */
	private function _ParseTemplate () {
		$this->template = $this->CI->stwig->render( $this->template, $this->values );
        $this->template_html = $this->CI->stwig->render( $this->template_html, $this->values );
		$this->subject = $this->CI->stwig->render( $this->subject, $this->values );
	}

    private function _buildRecipients(){
        $this->to=preg_replace('/\{\{.*?recipient.email.*?\}\}/i',$this->recipient_email,$this->to);
        if(!empty($this->bcc)) $this->to.=','.$this->bcc;
    }


	/**
	 * Установить данные получателя с данных пользователя
	 *
	 * @param $data array
	 */
	private function _SetRecipientData ( $data ) {
		$this->recipient_email = $data[ 'email' ];
		$this->values[ 'recipient' ] = $data;
	}

	/**
	 * Установить данные отправителя с данных пользователя
	 *
	 * @param $data array
	 */
	private function _SetSenderData ( $data ) {
		$this->sender_email = $data[ 'email' ];
		$fname = ( !empty( $data[ 'fname' ] ) ) ? $data[ 'fname' ] : NULL;
		$lname = ( !empty( $data[ 'lname' ] ) ) ? $data[ 'lname' ] : NULL;
		if ( !empty( $fname ) || !empty( $lname ) ) {
			$this->sender_name = trim( $fname . ' ' . $lname );
		}
		$this->values[ 'sender' ] = $data;
	}

	/**
	 * Получить данные пользователя по логину
	 *
	 * @param $login string
	 *
	 * @return array
	 */
	private function _GetUserFromLogin ( $login ) {
		return $this->CI->db->where( 'login', $login )->get( Emailer::TBL_USERS, 1 )->row_array();
	}

	/**
	 * Порлучить данные пользователя по ID
	 *
	 * @param $id integer
	 *
	 * @return array
	 */
	private function _GetUserFromId ( $id ) {
		return $this->CI->db->where( 'id', $id )->get( Emailer::TBL_USERS, 1 )->row_array();
	}

	/**
	 * Получить шаблон письма с БД
	 *
	 * @param $name string
	 *
	 * @return string
	 */
	private function _GetTemplate ( $name ) {
		return $this->CI->db->where( 'name', $name )->get( Emailer::TBL_TEMPLATES, 1 )->row_array();
	}

	/**
	 * Получить данные системы
	 */
	private function _SetSystemData () {
		$this->values[ 'system' ] = $this->CI->api->settings();
	}

    /**
     * @param array $user
     * @param string $template
     */
	public static function sendHtmlEmailUsingTemplate($user, $template, $recipient_email = '')
    {
        if(!$recipient_email){
            $recipient_email = $user['email'];
        }
        self::GetInstance()->LoadTemplate($template)
            ->TakeSenderSystem()
            ->SetRecipientEmail($recipient_email)
            ->SetTemplateValue('recipient', $user)
            ->SetMailType('html')
            ->Send();
        self::GetInstance()->Clear();
        return true;
    }

}