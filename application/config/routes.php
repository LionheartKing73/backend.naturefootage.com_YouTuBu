<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| 	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved 
| routes must come before any wildcard or regular expression routes.
|
*/

$route['playlist.html\?([0-9]+)'] = "playlist";

$route['[a-z]{2}/search/([0-9a-zA-z%+\-/]+)'] = "search/index/$1";
$route['[a-z]{2}/bin/([0-9a-zA-z%+\-/]+)'] = "bin/index/$1";
$route['[a-z]{2}/cart/([0-9a-zA-z%+\-/]+)'] = "cart/index/$1";

$route['[a-z]{2}/register/(account|profile)'] = "register/$1";
$route['[a-z]{2}/register/([0-9a-zA-z%+\-/]+)'] = "register/index/$1";

$route['[a-z]{2}/editors/(view|visible)'] = "editors/$1";
$route['[a-z]{2}/editors/(delete|details|send)/:num'] = "editors/$1/$2";
$route['[a-z]{2}/editors/([0-9a-zA-z%+\-/]+)'] = "editors/index/$1";

$route['[a-z]{2}/download'] = "download/items";
$route['[a-z]{2}/download/:num'] = "download/items/$1";
$route['[a-z]{2}/images/:num'] = "images/index/$1";
$route['[a-z]{2}/clips/([0-9a-zA-z\-]*):num'] = "clips/index/$1";

$route['[a-z]{2}/categories'] = 'cats';
$route['[a-z]{2}/categories/([0-9a-zA-z%+\-/]*)'] = "cats/content/$1";
$route['[a-z]{2}/category/([0-9a-zA-z%+\-/]*)'] = "search/index/$1";

$route['[a-z]{2}/fapi/([0-9a-zA-z%+\-/]*)'] = "fapi/$1";

$route['[a-z]{2}/([0-9a-zA-z\-/]+)'] = "$1";
$route['default_controller'] = "publication/content";
$route['scaffolding_trigger'] = "";

/**
 * Should be run via CLI
 * php index.php addSpecies index // runs XLS parser
 * php index.php searchVideos index // runs search videos by common names and families
 */
$route['[a-z]{2}/addSpecies'] = "addSpecies/index";
$route['[a-z]{2}/searchVideos'] = "searchVideo/index";

/* End of file routes.php */
/* Location: ./system/application/config/routes.php */