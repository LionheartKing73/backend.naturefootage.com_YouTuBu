<?php

/**
 * Class CI_DBSessionHandler
 * based on https://github.com/JamieCressey/PHP-MySQL-Session-Handler
 */
class CI_DBSessionHandler implements \SessionHandlerInterface
{
    /**
     * a database connection resource, used for write operations,
     * used for read operations if no read connection set
     * @var resource
     */
    private $dbConnection;

    /**
     * db connection for read operations
     *
     * a database connection resource
     * @var resource
     */
    private $readDbConnection;

    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    private $dbTable;

    /**
     * session expiration time, 12 hours by default
     *
     * @var int
     */
    private $expirationTime = 43200; // 12 hours

    /**
     * Inject DB connection from outside
     * @param   object  $dbConnection   expects CI DB_Driver
     */
    public function setReadDbConnection($dbConnection)
    {
        $this->readDbConnection = $dbConnection;
    }

    /**
     * Inject write DB connection from outside, this is the main connection,
     * used for write operation and for read operations, if no readDbConnection been set
     * @param   object  $dbConnection   expects CI DB_Driver
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
        if (is_null($this->readDbConnection)) {
            $this->readDbConnection = $this->dbConnection;
        }

    }

    /**
     * @param int $seconds - time after which session data will be deleted
     */
    public function setExpirationTime($seconds)
    {
        if (!is_int($seconds) || $seconds <= 0) {
            throw new Exception('Expiration time must be positive interger value');
        }

        $this->expirationTime = $seconds;
    }
    
    /**
     * Inject DB connection from outside
     * @param   object  $dbConnection   expects MySQLi object
     */
    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
    }

    public function open($savePath, $sessionName)
    {
        // nothing to do, so just retuen true;
        return true;
    }

    public function close()
    {
        if ($this->readDbConnection !== $this->dbConnection) {
            $this->readDbConnection->close();
        }
        
        $this->dbConnection->close();
        
        return true;
    }

    public function read($id)
    {
        $query = $this->readDbConnection->get_where($this->dbTable, ['id' => $id]);
        if ($result = $query->row_array()) {
            return (string)$result['data'];
        }

        return "";
    }

    public function write($id, $data)
    {
        return (bool)$this->dbConnection->replace(
            $this->dbTable,
            [
                'id' => $id,
                'data' => $data,
                'timestamp' => time()
            ]
        );
    }

    public function destroy($id)
    {
        return (bool)$this->dbConnection->delete($this->dbTable, ['id' => $id]);
    }

    public function gc($maxlifetime)
    {
        // convert value to int
        $maxlifetime = intval($maxlifetime);

        if ($this->expirationTime) {
            // take min value between php.ini and set from config
            $maxlifetime = min($maxlifetime, intval($this->expirationTime));
        }

        // log gc activity
        $this->dbConnection->insert(
            '__log_session_gc',
            ['maxlifetime' => $maxlifetime]
        );

        return (bool) $this->dbConnection->delete($this->dbTable, ['timestamp <' => time() - $maxlifetime]);
    }
}
