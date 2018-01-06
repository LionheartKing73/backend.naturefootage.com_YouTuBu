<?php

namespace Libraries\Accessors;

/**
 * Class CodeIgniterAccessors
 *
 * Используется для внедрения зависимостей в классы через подключение трейта
 * Для подключения к БД и загрузки моделей используется отложенная инициализация
 *
 * @package Libraries\Accessors
 */
trait CodeIgniterAccessors {

    /**
     * @var string  Хэш имени класса, который подключил трейт. Используется для уникализации имен значений в сессии
     */
    protected $classNameHash;
    /**
     * @var \CI_Controller
     */
    protected $ciInstance;
    /**
     * @var array   Список подключенных баз данных
     */
    protected $ciDatabaseList = array ();
    /**
     * @var array   Список подключенных моделей
     */
    protected $ciModelList = array ();

    public function __construct () {
        $this->codeIgniterAccessorsInit();
    }

    /**
     * Создание хэша для уникализации имен значений в сессии
     */
    protected function codeIgniterAccessorsInit () {
        $this->classNameHash = hash( 'crc32', __CLASS__ );
    }

    /**
     * @return \CI_Controller
     */
    public function getCiInstance () {
        if ( !$this->ciInstance ) {
            $this->ciInstance = get_instance();
        }
        return $this->ciInstance;
    }

    /**
     * @param string $dbName
     *
     * @return \CI_DB_active_record|NULL
     */
    public function getDatabase ( $dbName = 'default' ) {
        if ( !isset( $this->ciDatabaseList[ $dbName ] ) ) {
            $this->ciDatabaseList[ $dbName ] = $this->getCiInstance()->load->database( $dbName, TRUE );
        }
        return $this->ciDatabaseList[ $dbName ];
    }

    /**
     * @return \CI_Input
     */
    public function getInput () {
        return $this->getCiInstance()->input;
    }

    /**
     * @param string $name
     * @param bool   $xssClean
     *
     * @return string
     */
    public function getGet ( $name, $xssClean = FALSE ) {
        return $this->getInput()->get( $name, $xssClean );
    }

    /**
     * @param string $name
     * @param bool   $xssClean
     *
     * @return mixed
     */
    public function getPost ( $name, $xssClean = FALSE ) {
        return $this->getInput()->post( $name, $xssClean );
    }

    /**
     * @return \CI_URI
     */
    public function getUri () {
        return $this->getCiInstance()->uri;
    }

    /**
     * @param string $number
     * @param mixed  $defaultResult
     *
     * @return string
     */
    public function getUriSegment ( $number, $defaultResult = FALSE ) {
        return $this->getUri()->segment( $number, $defaultResult );
    }

    /**
     * @return \CI_Session
     */
    public function getSession () {
        return $this->getCiInstance()->session;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setSessionValue ( $name, $value ) {
        $this->getSession()->set_userdata( $this->classNameHash . '_' . $name, $value );
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getSessionValue ( $name ) {
        return $this->getSession()->userdata( $this->classNameHash . '_' . $name );
    }

    /**
     * @param string $name
     */
    public function removeSessionValue ( $name ) {
        $this->getSession()->unset_userdata( $this->classNameHash . '_' . $name );
    }

    /**
     * @param string $modelName
     *
     * @return {$modelName}
     */
    public function getModel ( $modelName ) {
        if ( !isset( $this->ciModelList[ $modelName ] ) ) {
            $this->getCiInstance()->load->model( $modelName );
            $this->ciModelList[ $modelName ] =& $this->getCiInstance()->$modelName;
        }
        return $this->ciModelList[ $modelName ];
    }

}