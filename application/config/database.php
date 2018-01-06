<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

//$active_group = 'default';
//$active_record = TRUE;

$active_group = "default";
$active_record = TRUE;
//$env = 'local';
//$env = 'live';


if(file_exists(dirname(__FILE__).'/database-local.php')){
    include(dirname(__FILE__).'/database-local.php');
} else {

    if($_SERVER['environment'] == 'production') {
        // used to be only for read only replica. replaced with master hostname in FSEARCH-1599
        $db['default']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['default']['username'] = 'fsmaster';
        $db['default']['password'] = 'FSdbm6512';
        $db['default']['database'] = 'fsmaster-production';
        $db['default']['dbdriver'] = 'mysql';
        $db['default']['dbprefix'] = '';
        $db['default']['pconnect'] = FALSE;
        $db['default']['db_debug'] = TRUE;
        $db['default']['cache_on'] = FALSE;
        $db['default']['cachedir'] = '';
        $db['default']['char_set'] = 'utf8';
        $db['default']['dbcollat'] = 'utf8_general_ci';
        $db['default']['swap_pre'] = '';
        $db['default']['autoinit'] = TRUE;
        $db['default']['stricton'] = FALSE;

        // Master
        $db['master']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com'; 
        $db['master']['username'] = 'fsmaster';
        $db['master']['password'] = 'FSdbm6512';
        $db['master']['database'] = 'fsmaster-production';
        $db['master']['dbdriver'] = 'mysql';
        $db['master']['dbprefix'] = '';
        $db['master']['pconnect'] = FALSE;
        $db['master']['db_debug'] = TRUE;
        $db['master']['cache_on'] = FALSE;
        $db['master']['cachedir'] = '';
        $db['master']['char_set'] = 'utf8';
        $db['master']['dbcollat'] = 'utf8_general_ci';
        $db['master']['autoinit'] = TRUE;
        $db['master']['stricton'] = FALSE;

        $db['footagesearch']['hostname'] = 'backend.footagesearch.com';
        $db['footagesearch']['username'] = 'dvf';
        $db['footagesearch']['password'] = 'xx132xx';
        $db['footagesearch']['database'] = 'dvf';
        $db['footagesearch']['dbdriver'] = "postgre";
        $db['footagesearch']['dbprefix'] = '';
        $db['footagesearch']['pconnect'] = TRUE;
        $db['footagesearch']['db_debug'] = TRUE;
        $db['footagesearch']['cache_on'] = FALSE;
        $db['footagesearch']['cachedir'] = '';
        $db['footagesearch']['char_set'] = 'utf8';
        $db['footagesearch']['dbcollat'] = 'utf8_general_ci';
        $db['footagesearch']['swap_pre'] = '';
        $db['footagesearch']['autoinit'] = TRUE;
        $db['footagesearch']['stricton'] = FALSE;

        $db['aspera_console']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['aspera_console']['username'] = 'fsmaster';
        $db['aspera_console']['password'] = 'FSdbm6512';
        $db['aspera_console']['database'] = 'aspera_console';
        $db['aspera_console']['dbdriver'] = 'mysql';
        $db['aspera_console']['dbprefix'] = '';
        $db['aspera_console']['pconnect'] = FALSE;
        $db['aspera_console']['db_debug'] = TRUE;
        $db['aspera_console']['cache_on'] = FALSE;
        $db['aspera_console']['cachedir'] = '';
        $db['aspera_console']['char_set'] = 'utf8';
        $db['aspera_console']['dbcollat'] = 'utf8_general_ci';
        $db['aspera_console']['swap_pre'] = '';
        $db['aspera_console']['autoinit'] = TRUE;
        $db['aspera_console']['stricton'] = FALSE;

    } elseif($_SERVER['environment'] == 'staging') {
        // read only replica
        $db['default']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['default']['username'] = 'fsmaster';
        $db['default']['password'] = 'FSdbm6512';
        $db['default']['database'] = 'fsmaster-nfstage';
        $db['default']['dbdriver'] = 'mysql';
        $db['default']['dbprefix'] = '';
        $db['default']['pconnect'] = FALSE;
        $db['default']['db_debug'] = TRUE;
        $db['default']['cache_on'] = FALSE;
        $db['default']['cachedir'] = '';
        $db['default']['char_set'] = 'utf8';
        $db['default']['dbcollat'] = 'utf8_general_ci';
        $db['default']['swap_pre'] = '';
        $db['default']['autoinit'] = TRUE;
        $db['default']['stricton'] = FALSE;

        $db['master']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['master']['username'] = 'fsmaster';
        $db['master']['password'] = 'FSdbm6512';
        $db['master']['database'] = 'fsmaster-nfstage';
        $db['master']['dbdriver'] = 'mysql';
        $db['master']['dbprefix'] = '';
        $db['master']['pconnect'] = FALSE;
        $db['master']['db_debug'] = TRUE;
        $db['master']['cache_on'] = FALSE;
        $db['master']['cachedir'] = '';
        $db['master']['char_set'] = 'utf8';
        $db['master']['dbcollat'] = 'utf8_general_ci';
        $db['master']['autoinit'] = TRUE;
        $db['master']['stricton'] = FALSE;

        $db['footagesearch']['hostname'] = 'backend.footagesearch.com';
        $db['footagesearch']['username'] = 'dvf';
        $db['footagesearch']['password'] = 'xx132xx';
        $db['footagesearch']['database'] = 'dvf';
        $db['footagesearch']['dbdriver'] = "postgre";
        $db['footagesearch']['dbprefix'] = '';
        $db['footagesearch']['pconnect'] = TRUE;
        $db['footagesearch']['db_debug'] = TRUE;
        $db['footagesearch']['cache_on'] = FALSE;
        $db['footagesearch']['cachedir'] = '';
        $db['footagesearch']['char_set'] = 'utf8';
        $db['footagesearch']['dbcollat'] = 'utf8_general_ci';
        $db['footagesearch']['swap_pre'] = '';
        $db['footagesearch']['autoinit'] = TRUE;
        $db['footagesearch']['stricton'] = FALSE;

        $db['aspera_console']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['aspera_console']['username'] = 'fsmaster';
        $db['aspera_console']['password'] = 'FSdbm6512';
        $db['aspera_console']['database'] = 'aspera_console';
        $db['aspera_console']['dbdriver'] = 'mysql';
        $db['aspera_console']['dbprefix'] = '';
        $db['aspera_console']['pconnect'] = FALSE;
        $db['aspera_console']['db_debug'] = TRUE;
        $db['aspera_console']['cache_on'] = FALSE;
        $db['aspera_console']['cachedir'] = '';
        $db['aspera_console']['char_set'] = 'utf8';
        $db['aspera_console']['dbcollat'] = 'utf8_general_ci';
        $db['aspera_console']['swap_pre'] = '';
        $db['aspera_console']['autoinit'] = TRUE;
        $db['aspera_console']['stricton'] = FALSE;

        /**
         * This DB is used for addLandSpecies controller.
         * Controller imports species
         */
        $db['land_species']['hostname'] = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
        $db['land_species']['username'] = 'fsmaster';
        $db['land_species']['password'] = 'FSdbm6512';
        $db['land_species']['database'] = 'land_species';
        $db['land_species']['dbdriver'] = 'mysql';
        $db['land_species']['dbprefix'] = '';
        $db['land_species']['pconnect'] = FALSE;
        $db['land_species']['db_debug'] = TRUE;
        $db['land_species']['cache_on'] = FALSE;
        $db['land_species']['cachedir'] = '';
        $db['land_species']['char_set'] = 'utf8';
        $db['land_species']['dbcollat'] = 'utf8_general_ci';
        $db['land_species']['autoinit'] = TRUE;
        $db['land_species']['stricton'] = FALSE;
    }
}

/* End of file database.php */
/* Location: ./application/config/database.php */
