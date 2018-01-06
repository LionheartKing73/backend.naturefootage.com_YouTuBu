<?php

namespace Libraries\Cliplog\Clipbin;

use Libraries\Accessors\CodeIgniterAccessors;

class ClipbinSelected {

    use CodeIgniterAccessors;

    /** @var ClipbinRequest */
    protected $clipbinRequest;

    protected $sessionName = 'cliplog-clipbin-selected';

    public function __construct ( $clipbinRequest ) {
        $this->clipbinRequest = $clipbinRequest;
    }

    public function getClipbinRequest () {
        return $this->clipbinRequest;
    }

    public function setSelectedClipbinId ( $clipbinId ) {
        $this->getClipbinRequest()->setSessionValue( $this->sessionName, $clipbinId );
    }

    public function unsetSelectedClipbinId () {
        $this->getClipbinRequest()->removeSessionValue( $this->sessionName );
    }

    public function getSelectedClipbinId () {
        return $this->getClipbinRequest()->getSessionValue( $this->sessionName );
    }

    public function isSetSelectedClipbinId () {
        return !!$this->getSelectedClipbinId();
    }

}