<?php

namespace Libraries\Cliplog\Editor;

use Libraries\Cliplog\Editor\Metadata\MetadataTemplate;
use Libraries\Cliplog\Editor\Logging\LoggingTemplate;

trait CliplogEditorAccessors {

    protected $cliplogEditorMetadataTemplate;
    protected $cliplogEditorLoggingTemplate;
    protected $cliplogEditorForm;
    protected $cliplogEditorRequest;

    public function getCliplogEditorMetadataTemplate () {
        if ( !$this->cliplogEditorMetadataTemplate ) {
            $this->cliplogEditorMetadataTemplate = new MetadataTemplate( $this );
        }
        return $this->cliplogEditorMetadataTemplate;
    }

    public function getCliplogEditorLoggingTemplate () {
        if ( !$this->cliplogEditorLoggingTemplate ) {
            $this->cliplogEditorLoggingTemplate = new LoggingTemplate( $this );
        }
        return $this->cliplogEditorLoggingTemplate;
    }

    public function getCliplogEditorRequest () {
        if ( !$this->cliplogEditorRequest ) {
            $this->cliplogEditorRequest = new CliplogEditorRequest( $this );
        }
        return $this->cliplogEditorRequest;
    }

}