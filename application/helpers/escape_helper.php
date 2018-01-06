<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function esc($value, $type = 'html') {
  switch ($type) {
    case 'html':
      return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
    case 'url':
      return urlencode($value);
    case 'javascript':
      return strtr($value, array('\\' => '\\\\', "'" => "\\'",
        '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
    default:
      return $value;
  }
}
