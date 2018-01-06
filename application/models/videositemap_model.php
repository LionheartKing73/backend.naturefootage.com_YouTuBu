<?php

    class Videositemap_model extends CI_Model {

        #------------------------------------------------------------------------------------------------

        function Videositemap_model() {

            parent::__construct();            
                        
            $this->load->model('settings_model','sm');

        }

       #---------------------------------------------------------------------------------#

        function get_filemap_info() {

            $filename = $this->sm->get_setting('video_sitemap_filepath');

            $filename = $filename . '.xml';

            if(!file_exists($filename)) {
                return false;
            }

            $result = array(

                'size' => filesize($filename),
                'last_update' => date('j F Y H:i', filemtime($filename))

            );

            return $result;

        }

        #---------------------------------------------------------------------------------#

        function create_map($clips) {

            $filepath = $this->sm->get_setting('video_sitemap_filepath');

            if(empty($filepath))
                return "Empty file name!";

            // create xml file
            $filepath = $filepath . '.xml';

            $file = fopen($filepath, 'w+');

            if(!$file)
                return "Can't create file ".$filepath."!";

           // create map 

           $include_www = $this->sm->get_setting('video_sitemap_www');

           if($clips) {

               $doc  = '<?xml version="1.0" encoding="utf-8"?>'."\r\n";
               $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
               $doc .=         ' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">'."\r\n";
               
               foreach($clips as $clip) {

                    if(isset($clip['prew']['f4v']))
                        $content_loc = $clip['prew']['f4v'];
                    elseif(isset($clip['prew']['mov']))
                        $content_loc = $clip['prew']['mov'];
                    else
                        continue;
                    
                    $page_url       = $this->proccess_url( base_url().htmlentities($clip['url']),   $include_www);
                    $thumb_url      = $this->proccess_url( $clip['thumb']['jpeg'],                  $include_www);
                    $content_loc    = $this->proccess_url( $content_loc,                            $include_www);
                   
                    $doc .= '<url>'."\r\n";
                    
                    $doc .= '<loc>'. $page_url .'</loc>'."\r\n";

                    $doc .= '<video:video>'."\r\n";
                    $doc .= '<video:thumbnail_loc>'.    htmlentities($thumb_url) .'</video:thumbnail_loc>'."\r\n";
                    $doc .= '<video:title>'.            htmlentities($clip['title']) .'</video:title>'."\r\n";
                    $doc .= '<video:description>'.      htmlentities($clip['description']) .'</video:description>'."\r\n";
                    $doc .= '<video:content_loc>'.      htmlentities($content_loc) .'</video:content_loc>'."\r\n";
                    $doc .= '</video:video>'."\r\n";

                    $doc .= '</url>'."\r\n";

               }

               $doc .= '</urlset>'."\r\n";

               $result = fwrite($file, $doc);

               if(!$result)
                    return "Can't write data to file ".$filepath."!";

               fclose($file);


           }

        }

        #---------------------------------------------------------------------------------#

        function delete_map() {

            $filepath = $this->sm->get_setting('video_sitemap_filepath');

            $filepath = $filepath . '.xml';

            $result = unlink($filepath);

            if(!$result)
                return "Can't delete file ".$filepath."!";
            
        }

        #---------------------------------------------------------------------------------#

        function proccess_url($url, $include_www=true) {

            $has_www = preg_match('`^(http://)*?(www\.)`', $url);
            
            if($include_www && !$has_www) {
                $result = preg_replace('`^(http://)?(.*)`', 'http://www.$2', $url);
            } elseif (!$include_www && $has_www) {
                $result = preg_replace('`^(http://)?(www\.)(.*)`', 'http://$3', $url);
            } else {
                $result = $url;
            }

            return $result;

        }


    }
