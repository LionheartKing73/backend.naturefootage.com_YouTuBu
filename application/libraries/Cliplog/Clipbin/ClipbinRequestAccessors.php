<?php

namespace Libraries\Cliplog\Clipbin;

trait ClipbinRequestAccessors {

    /** @var ClipbinRequest */
    protected static $selfInstance;

    protected $clipbinSelected;
    protected $clipbinActive;

    public function getClipbinSelected () {
        if ( !$this->clipbinSelected ) {
            $this->clipbinSelected = new ClipbinSelected( $this );
        }
        return $this->clipbinSelected;
    }

    public function getClipbinActive () {
        if ( !$this->clipbinActive ) {
            $this->clipbinActive = new ClipbinActive( $this );
        }
        return $this->clipbinActive;
    }

    public static function getInstance () {
        if ( !self::$selfInstance ) {
            self::$selfInstance = new self();
        }
        return self::$selfInstance;
    }

}