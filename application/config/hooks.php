<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
include('config.php');
include('routes.php');

$config['default_controller'] = $route['default_controller'];

$hook['pre_system'] = array(
    'class'    => '',
    'function' => 'change_variables',
    'filename' => 'change_variables.php',
    'filepath' => 'hooks',
    'params' => $config
);

/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */