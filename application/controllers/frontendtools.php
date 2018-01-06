<?php

class Frontendtools extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function create_frontend(){

        if(!$this->input->is_cli_request()){
            exit();
        }
        $this->load->model('frontends_model');
        $frontends = $this->frontends_model->get_frontends_list_with_providers(array('f.status' => 0));
        if($frontends)
            foreach($frontends as $frontend){

                if (!file_exists('/exports/frontend/' . $frontend['host_name']))
                    mkdir('/exports/frontend/' . $frontend['host_name']);
                $wp_content_path = '/exports/frontend/' . $frontend['host_name'] . '/public_html';
                if (!file_exists($wp_content_path))
                    mkdir($wp_content_path);

                //$repo = $this->config->item('svn_frontend_repo');
                $repo = 'svn+ssh://svnuser@ops/frontend/trunk';
                //exec('svn checkout ' . $repo . ' ' . $wp_content_path . ' --force');

                $db_name = str_replace(array('.', '-'), '', $frontend['host_name']);
                $dump_path = dirname(dirname(__DIR__)) . '/application/config/frontends/dump.sql';
                $db = array();
                include(APPPATH . 'config/database.php');

                exec('mysql -h ' . $db['master']['hostname'] . ' -u ' . $db['master']['username'] . ' -p' . $db['master']['password'] . ' --execute="create database ' . $db_name . ';"');
                exec('mysql -h ' . $db['master']['hostname'] . ' -u ' . $db['master']['username'] . ' -p' . $db['master']['password'] . ' -Nse \'show tables\' ' . $db_name . ' | while read table; do mysql -h ' . $db['master']['hostname'] . ' -u ' . $db['master']['username'] . ' -p' . $db['master']['password'] . ' -e "drop table $table" ' . $db_name . '; done');
                exec('mysql -h ' . $db['master']['hostname'] . ' -u ' . $db['master']['username'] . ' -p' . $db['master']['password'] . ' ' . $db_name . ' < ' . $dump_path);
                exec('mysql -h ' . $db['master']['hostname'] . ' -u ' . $db['master']['username'] . ' -p' . $db['master']['password'] . ' --execute="USE \'' . $db_name . '\'; TRUNCATE TABLE wp_commentmeta; TRUNCATE TABLE wp_comments; TRUNCATE TABLE wp_comments; TRUNCATE TABLE wp_users; TRUNCATE TABLE wp_usermeta;"');

                $frontend_config = '';
                $frontend_config .= 'CONF_HOST_DOMAIN="' . $frontend['host_name'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_ID="' . $frontend['provider_id'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_FRONTEND_ID="' . $frontend['id'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_LOGIN="' . $frontend['login'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_PASS="' . $frontend['password'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_FIRST_NAME="' . $frontend['fname'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_LAST_NAME="' . $frontend['lname'] .'"' . PHP_EOL;
                $frontend_config .= 'CONF_PROVIDER_EMAIL="' . $frontend['email'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_SITE_TITLE="' . $frontend['name'] . '"' . PHP_EOL;
                $frontend_config .= 'CONF_DB_NAME="' . $db_name . '"';
                file_put_contents(__DIR__ . '/../config/frontends/config/' . $frontend['host_name'] . '.cfg', $frontend_config);
                exec('svn add --force ' . realpath(__DIR__ . '/../config/frontends/config/') . '/');
                exec('svn --no-auth-cache commit -m "Frontends configs" ' . realpath(__DIR__ . '/../config/frontends/config/') . '/');
                $this->frontends_model->change_status($frontend['id']);

//                $frontend['name'] = str_replace(' ', '%space%', $frontend['name']);
//                $root_dir = dirname(dirname(dirname(__FILE__)));
//                $command = $root_dir . '/scripts/createfrontend2.sh ' . $frontend['host_name'] . ' ' . $frontend['provider_id'] .  ' ' . $frontend['id'] .  ' '
//                    . $frontend['login'] . ' ' . $frontend['password'] . ' ' . $frontend['fname']
//                    . ' ' . $frontend['lname'] . ' ' . $frontend['email'] . ' \'' . $frontend['name'] . '\' >> '
//                    . $root_dir . '/scripts/logs/createfrontend.log';
//
//                //echo $command;
//                system($command, $return_var);
//                if($return_var == 0){
//                    $this->frontends_model->change_status($frontend['id']);
//                }
            }


//        // Create FileCatalyst users
//        $this->load->model('users_model');
//        $providers = $this->users_model->get_providers_list();
//        $root_dir = dirname(dirname(dirname(__FILE__)));
//        $this->config->load('file_catalyst', TRUE);
//        $fc_config = $this->config->item('file_catalyst');
//        $fc_server = $fc_config['server'];
//        $fc_remote_username = $fc_config['remote_username'];
//        $fc_remote_password = $fc_config['remote_password'];
//        $fc_remote_port = $fc_config['remote_port'];
//        foreach($providers as $provider){
//            if(!$provider['dir']){
//                $provider_dir = $root_dir . '/data/upload/providers/' . $provider['login'];
//                //$provider_dir = '/var/www/fsearch/public_html/data/upload/providers/' . $provider['login'];
//                $command = 'java -jar ' . $root_dir . '/scripts/fc_sv/FCServerAPI.jar -host ' . $fc_server . ' -port ' . $fc_remote_port . ' -user "' . $fc_remote_username . '" -passwd "' . $fc_remote_password . '" -adduser "' . $provider['login'] . '" "' . $provider['password'] . '" >> '. $root_dir . '/scripts/logs/filecatalyst.log';
//                system($command);
//                $command = 'java -jar ' . $root_dir . '/scripts/fc_sv/FCServerAPI.jar -host ' . $fc_server . ' -port ' . $fc_remote_port . ' -user "' . $fc_remote_username . '"/"' . $fc_remote_password . '" -moduser "' . $provider['login'] . '" -muHomeDir "' . $provider_dir . '" >> '. $root_dir . '/scripts/logs/filecatalyst.log';
//                system($command);
//                $this->users_model->set_dir($provider['id'], $provider_dir);
//            }
//        }

    }

    public function create_users(){
        // Create aspera users

        $this->load->model('users_model');
        $providers = $this->users_model->get_providers_list();
        $root_dir = dirname(dirname(dirname(__FILE__)));
        $store = array();
        require(__DIR__ . '/../config/store.php');
        foreach($providers as $provider){

            if(!$provider['dir']){
//                $command = $root_dir . '/scripts/create_aspera_user.sh ' . $provider['login'] . ' ' . $provider['password']
//                    .  ' >> ' . $root_dir . '/scripts/logs/create_aspera_user.log';
//                system($command, $return_var);
//                //$provider_dir = $root_dir . '/data/upload/providers/' . $provider['login'];
//                $provider_dir = '/Volumes/Data/providers/' . $provider['login'];
//                if(file_exists($provider_dir)){
//                    $this->users_model->set_dir($provider['id'], $provider_dir);
//                }

                $provider_dir = $store['uploads']['path'] . '/' . $provider['login'];

                if (!file_exists($provider_dir)) {
                    mkdir($provider_dir);
                    chown($provider_dir, 'fsprovider');
                }
                $this->users_model->set_dir($provider['id'], $provider_dir);
            }
        }
    }

    public function update_users(){
        $this->load->model('users_model');
        $providers = $this->users_model->get_providers_list();
        $root_dir = dirname(dirname(dirname(__FILE__)));
        foreach($providers as $provider){
            $command = $root_dir . '/scripts/update_aspera_user.sh ' . $provider['login'] . ' ' . $provider['password']
                .  ' >> ' . $root_dir . '/scripts/logs/update_aspera_user.log';
            system($command, $return_var);
        }
    }
}
?>