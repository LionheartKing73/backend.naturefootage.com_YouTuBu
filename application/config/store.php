<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// AVS ftp
//$ftp['host'] = '54.208.133.139';
//$ftp['port'] = '21';
//$ftp['username'] = 'www-data';
//$ftp['password'] = 'fjhWkdTySpa';

$store['transcode_location'] = '810 Office';


$store['s3']['key'] = 'AKIAJ4LE6SMVOSPF4DPA';
$store['s3']['secret'] = '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';
$store['s3']['region'] = 'us-east-1';
$store['s3']['region-cloudfront'] = 'us-west-2';
// Store setting relatively transcoder server
//$store['original']['scheme'] = 'ftp';
//$store['original']['host'] = '63.249.65.98';
//$store['original']['port'] = '21';
//$store['original']['username'] = 'www-data';
//$store['original']['password'] = 'WwData881';
//$store['original']['path'] = '/storage';

$store['original']['path'] = '/storage';

//$store['proxies']['scheme'] = 'ftp';
//$store['proxies']['host'] = '63.249.65.98';
//$store['proxies']['port'] = '21';
//$store['proxies']['username'] = 'www-data';
//$store['proxies']['password'] = 'WwData881';
//$store['proxies']['path'] = '/var/www/fsearch/public_html/data/upload/resources/clip/proxies';

$store['proxies']['path'] = '/storage/FTP/transcodertemp/proxies';

//$store['add_res']['scheme'] = 'ftp';
//$store['add_res']['host'] = '63.249.65.98';
//$store['add_res']['port'] = '21';
//$store['add_res']['username'] = 'www-data';
//$store['add_res']['password'] = 'WwData881';
//$store['add_res']['path'] = '/var/www/fsearch/public_html/data/upload/resources/clip/add_res';

//$store['watermark']['scheme'] = 'ftp';
//$store['watermark']['host'] = '54.208.133.139';
//$store['watermark']['port'] = '21';
//$store['watermark']['username'] = 'www-data';
//$store['watermark']['password'] = 'fjhWkdTySpa';
//$store['watermark']['path'] = '/var/www/fsearch/public_html/data/upload/watermark';

$store['watermark']['path'] = '/storage/FTP/transcodertemp/watermark';

$store['preview']['scheme'] = 's3';
$store['preview']['bucket'] = 's3.footagesearch.com';
$store['preview']['path'] = '/fspreview';

$store['thumb']['scheme'] = 's3';
$store['thumb']['bucket'] = 's3.footagesearch.com';
$store['thumb']['path'] = '/fsstils';

$store['motion_thumb']['scheme'] = 's3';
$store['motion_thumb']['bucket'] = 's3.footagesearch.com';
$store['motion_thumb']['path'] = '/fsthumb';

$store['hdpreview']['scheme'] = 's3';
$store['hdpreview']['bucket'] = 's3.footagesearch.com';
$store['hdpreview']['path'] = '/hd-previews';

$store['resources']['scheme'] = 's3';
$store['resources']['bucket'] = 's3.footagesearch.com';
$store['resources']['path'] = '/previews';

$store['releases_file']['scheme'] = 's3';
$store['releases_file']['bucket'] = 's3.footagesearch.com';
$store['releases_file']['path'] = '/releases';

$store['archives']['scheme'] = 's3';
$store['archives']['bucket'] = 'orders.naturefootage.com';
$store['archives']['path'] = '/archives';

$store['order_pdf']['scheme'] = 's3';
$store['order_pdf']['bucket'] = 's3.naturefootage.com';
$store['order_pdf']['path'] = '/orderpdf';

//$store['delivery']['scheme'] = 'ftp';
//$store['delivery']['host'] = '63.249.65.98';
//$store['delivery']['port'] = '21';
//$store['delivery']['username'] = 'www-data';
//$store['delivery']['password'] = 'WwData881';
//$store['delivery']['path'] = '/var/www/fsearch/public_html/data/upload/resources/converted';

$store['delivery']['path'] = '/storage/FTP/transcodertemp/converted';

$store['user_delivery']['scheme'] = 'ftp';
$store['user_delivery']['host'] = 'upload.naturefootage.com';
$store['user_delivery']['port'] = '21';
$store['user_delivery']['username'] = 'www-data';
$store['user_delivery']['password'] = 'WwData881';
$store['user_delivery']['path'] = '/Volumes/Data/providers/fsdownload';
$store['user_delivery']['web_root'] = '/var/www';

$store['users_storage']['path'] = '/Volumes/Data/providers/fsusersstorage';
//$store['users_storage']['path'] = '/home/www-data/www/fsearch/data/upload/providers/fsusersstorage';
$store['users_storage']['user'] = 'fsusersstorage';
$store['users_storage']['group'] = 'fsusersstorage';
//$store['users_storage']['user'] = 'ivan';
//$store['users_storage']['group'] = 'iavn';

$store['media_server']['host'] = '10.0.0.12';
$store['media_server']['username'] = 'admn';

$store['submissions_backup']['path'] = '/Volumes/Data/Submissions';

$store['uploads']['path'] = '/Volumes/Data/providers/fsprovider';
$store['aws']['elb'] = 'Testing';
$store['debug_email'] = 'alexander.dychka@boldendeavours.com';

$store['thumb']['browse_page']['type'] = 6;
$store['thumb']['browse_page']['width'] = 320;
$store['thumb']['browse_page']['height'] = 180;
$store['thumb']['hd']['type'] = 5;
$store['thumb']['default']['type'] = 0;
$store['thumb']['hd']['folder'] = 'stills_hd';
$store['thumb']['browse_page']['folder'] = 'stills_browse';
$store['thumb']['filetype'] = 'jpg';

