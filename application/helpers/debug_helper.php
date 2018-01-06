<?php  if ( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

if ( !class_exists( 'Debug' ) ) {

    class Debug {

        static function Export ( $data,$title='',$colorline=false ) {
            echo '<pre style="margin: 2px 0; padding: 6px; border: 1px solid #ccc; background: #eee; color: #33; font-size: 11px; font-family: monospace; tab-size: 4; max-height: 300px; overflow: auto;">';
            if($colorline) echo '<hr style="border-color: cornflowerblue;">';
            echo ($title!='') ? '<h3 style="color:dodgerblue;display:inline-block;">'.$title.'</h3> (<time style="display:inline-block;color:lightseagreen;">'.date('Y:m:d H:i:s').'</time>):</br>': '';
            var_export( $data );
            if($colorline) echo '<hr style="border-color: gray;">';
            echo '</pre>';
        }

        static function Dump ( $data,$title='',$colorline=false ) {
            echo '<pre style="margin: 2px 0; padding: 6px; border: 1px solid #ccc; background: #eee; color: #33; font-size: 11px; font-family: monospace; tab-size: 4; max-height: 300px; overflow: auto;">';
            if($colorline) echo '<hr style="border-color: cornflowerblue;">';
            echo ($title!='') ? '<hr><h3 style="color:dodgerblue;display:inline-block;">'.$title.'</h3> (<time style="display:inline-block;color:lightseagreen;">'.date('Y:m:d H:i:s').'</time>):</br>': '';
            var_dump( $data );
            if($colorline) echo '<hr style="border-color: gray;">';
            echo '</pre>';
        }

        static function ExportToFile ( $data, $filepath = NULL, $clear_file = FALSE ) {
            if ( $filepath ) {
                $filepath = str_replace( '::', '_', $filepath );
                $directory = pathinfo( $filepath, PATHINFO_DIRNAME );
                if ( ! $directory || $directory == '.' ) {
                    $directory = FCPATH;
                }
                $filename = pathinfo( $filepath, PATHINFO_BASENAME );
                $position = strpos( $filename, '___' );
                if ( $position === FALSE || $position > 2 ) {
                    $filename = '___' . $filename;
                }
                $extension = pathinfo( $filepath, PATHINFO_EXTENSION );
                if ( $extension != 'log' ) {
                    $filename .= '.log';
                }
                $filepath = $directory . $filename;
            } else {
                //$filepath = FCPATH . '___' . get_called_class() . '.log';
                $filepath = BASEPATH . '___debug.log';
            }
            if ( is_array( $data ) || is_object( $data ) ) {
                $data = json_encode( $data );
            }
            $date = date( 'Y-m-d H:i:s' );
            $microtime = str_replace( '.', NULL, microtime( TRUE ) );
            $string = "{$date} :: {$microtime}" . PHP_EOL;
            $string .= "    > {$data}" . PHP_EOL . PHP_EOL;
            $file_append = ( $clear_file ) ? NULL : FILE_APPEND;
            file_put_contents( $filepath, $string, $file_append );
        }

    }

}
