<?php
class Sitemap_model extends CI_Model {

  var $lang;
  var $map_file;
  var $base_url;
  var $url_suffix;
  
#------------------------------------------------------------------------------------------------

    function __construct() {
        parent::__construct();
    }

  function write_url($url) {
    fwrite($this->map_file, '  <url>');
    foreach ($url as $name=>$value) {
      fwrite($this->map_file, '<' . $name . '>' . $value . '</' . $name . '>');
    }
    fwrite($this->map_file, "</url>\r\n");
  }

#------------------------------------------------------------------------------------------------

  function write_pages() {
    $query = $this->db->query('SELECT alias1, mtime FROM lib_pages WHERE active = 1');
    $rows = $query->result_array();
    
    if (count($rows)) {
      $url = array();
      foreach ($rows as $row) {
        $url['loc'] = $this->base_url . $this->lang . '/' . $row['alias1']; #. $this->url_suffix;
        $url['lastmod'] = strftime('%Y-%m-%d', strtotime($row['mtime']));
        $url['changefreq'] = 'weekly';
        $url['priority'] = '0.50';
        
        $this->write_url($url);
      }
    }
    
    $query->free_result();
  }

#------------------------------------------------------------------------------------------------

  function update($lang='en') {
    $this->lang = $lang;
    $this->base_url = $this->config->item('base_url');
    $this->url_suffix = $this->config->item('url_suffix');

    $this->map_file = fopen($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml', 'w');
    fwrite($this->map_file, '<?xml version="1.0" encoding="UTF-8"?' . ">\r\n"
      . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n");

    $this->write_pages();

    fwrite($this->map_file,"</urlset>");
    fclose($this->map_file);
  }

}
