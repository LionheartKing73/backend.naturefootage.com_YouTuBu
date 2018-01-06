<?php

    class config
    {
        private $host='localhost';
        private $username='root';
        private $password='';
        private $dbname='khandla';

        function __construct()
        {   
            if(mysql_connect($this->host,$this->username,$this->password))
            {
                echo "connection successfully";
            }
        }
        function db()
        {
            mysql_select_db($this->$dbname);
        }
    }

?>
