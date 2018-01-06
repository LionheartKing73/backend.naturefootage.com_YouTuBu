<?php

namespace Libraries\Cliplog\Clipbin;

/** @noinspection PhpIncludeInspection */
require_once( APPPATH . 'libraries/CodeIgniterAccessors.php' );
require_once( APPPATH . 'libraries/Cliplog/Clipbin/ClipbinRequestAccessors.php' );
require_once( APPPATH . 'libraries/Cliplog/Clipbin/ClipbinActive.php' );
require_once( APPPATH . 'libraries/Cliplog/Clipbin/ClipbinSelected.php' );


use Libraries\Accessors\CodeIgniterAccessors;

class ClipbinRequest {

    use CodeIgniterAccessors;
    use ClipbinRequestAccessors;

    protected $tableClipbinName = 'lib_backend_lb';
    protected $clipbinRequestSegment = 'backend_clipbin';

    public function isChangeClipbinRequest () {
        return isset ( $_REQUEST[ $this->clipbinRequestSegment ] );
    }

    public function getClipbinIdFromRequest () {
        if ( $this->isChangeClipbinRequest() ) {
            return (int) $_REQUEST[ $this->clipbinRequestSegment ];
        }
        return FALSE;
    }

    public function isSelectClipbinRequest () {
        if ( $this->isChangeClipbinRequest() ) {
            return ( $this->getClipbinIdFromRequest() > 0 );
        }
        return FALSE;
    }

    public function isUnselectClipbinRequest () {
        if ( $this->isChangeClipbinRequest() ) {
            return ( $this->getClipbinIdFromRequest() <= 0 );
        }
        return FALSE;
    }

    public function getUserDefaultClipbinId ( $userId ) {
        if ( $userId ) {
            $result = $this->getDatabase( 'default' )->get_where(
                $this->tableClipbinName,
                array (
                    'client_id' => $userId,
                    'is_default' => 1
                ),
                1
            );
            if ( $result ) {
                $resultData = $result->row_array();
                if ( isset( $resultData[ 'id' ] ) ) {
                    return (int) $resultData[ 'id' ];
                }
            }
        }
        return FALSE;
    }

}