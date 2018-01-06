<?php
function change_variables($config){

  $lang_list = array_keys($config['support_languages']);
  $default_lang = $config['default_language'];
  $default_controller = $config['default_controller'];

  $clear_request = trim($_SERVER['REQUEST_URI'],'/');

  if(preg_match("/\/".$default_lang."\/index\.html/", $_SERVER['REQUEST_URI'], $matches)) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /');
  }
  if($clear_request=='')
    $_SERVER['REQUEST_URI'] = implode('/', array('/',$default_lang,$default_controller));
  elseif(in_array($clear_request, $lang_list))
    $_SERVER['REQUEST_URI'] = implode('/', array('/',$clear_request,$default_controller));
  elseif(!preg_match("/\/(".implode('|',$lang_list).")\//", $_SERVER['REQUEST_URI'], $matches)) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /'.$default_lang.$_SERVER['REQUEST_URI']);
  }
}
