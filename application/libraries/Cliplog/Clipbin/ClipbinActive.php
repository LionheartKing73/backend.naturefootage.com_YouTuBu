<?php

namespace Libraries\Cliplog\Clipbin;

use Libraries\Accessors\CodeIgniterAccessors;

class ClipbinActive {

    use CodeIgniterAccessors;

    /** @var ClipbinRequest */
    protected $clipbinRequest;

    protected $sessionName = 'cliplog-clipbin-active';

    public function __construct ( $clipbinRequest ) {
        $this->clipbinRequest = $clipbinRequest;
    }

    public function getClipbinRequest () {
        return $this->clipbinRequest;
    }

    public function setActiveClipbinId ( $clipbinId ) {
        $this->getClipbinRequest()->setSessionValue( $this->sessionName, $clipbinId );
    }

    public function unsetActiveClipbinId () {
        $this->getClipbinRequest()->removeSessionValue( $this->sessionName );
    }

    public function getActiveClipbinId () {
        return $this->getClipbinRequest()->getSessionValue( $this->sessionName );
    }

    public function isSetActiveClipbinId () {
        return !!$this->getActiveClipbinId();
    }

}