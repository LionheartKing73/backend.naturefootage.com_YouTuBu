<?php

// base namespace for this scripts
define('BASE_NAMESPACE', 'SortRating');

// base directory for scripts
define('BASE_DIR', __DIR__);

// classes dir
define('SRC_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'src');

// production
define('ENV_PRODUCTION', 'production');

// staging
define('ENV_STAGING', 'staging');

// to be abble use codeigniter database config
define('BASEPATH', true);

// if script is running local
define ('IS_LOCAL', !isset($_SERVER['environment']));
