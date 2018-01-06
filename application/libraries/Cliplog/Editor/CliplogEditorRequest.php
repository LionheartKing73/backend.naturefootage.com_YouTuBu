<?php

namespace Libraries\Cliplog\Editor;

class CliplogEditorRequest {

    protected $cliplogEitor;

    public function __construct ( CliplogEditor $cliplogEditor ) {
        $this->cliplogEitor = $cliplogEditor;
    }

    public function getCliplogEditor () {
        return $this->cliplogEitor;
    }

    public function getSelectedClipId () {
        return $this->getCliplogEditor()->getUriSegment( 4, FALSE );
    }

    public function getClipsIds () {
        $clipsIdsString = $this->getCliplogEditor()->getPost( 'clips_ids' );
        if ( $clipsIdsString ) {
            $clipsIds = explode( ',', $clipsIdsString );
        } else {
            $clipsIds = $this->getCliplogEditor()->getPost( 'id' );
        }
        # Сортируем Id клипов, чтобы всегда были по порядку
        if ( is_array( $clipsIds ) ) {
            asort( $clipsIds );
        }
        return $clipsIds;
    }

    public function isSaveRequest () {
        return !!$this->getCliplogEditor()->getPost( 'save' );
    }

    public function getFormData () {
        $formData = array ();
        $sectionValues = $this->getCliplogEditor()->getPost( 'sections_values' );
        if ( $sectionValues ) {
            $formData[ 'sections_values' ] = $sectionValues;
        }
        $keywords = $this->getCliplogEditor()->getPost( 'keywords' );
        if ( $keywords ) {
            $formData[ 'keywords' ] = $keywords;
        }
        return $formData;
    }

    public function getFormDataForSave () {
        $formData = array ();
        $sectionValues = $this->getCliplogEditor()->getPost( 'sections_values' );
        if ( $sectionValues ) {
            $formData = $sectionValues;
        }
        $keywords = $this->getCliplogEditor()->getPost( 'keywords' );
        if ( $keywords ) {
            $formData[ 'keywords' ] = $keywords;
        }
        $overwrite = ($this->getCliplogEditor()->getPost( 'overwrite_fields' )) ? $this->getCliplogEditor()->getPost( 'overwrite' ) : false;
        if ( $overwrite ) {
            $formData[ 'overwrite' ] = $overwrite;
        }
        $keywords_set_id=$this->getCliplogEditor()->getPost( 'keywords_set_id' );

        /*if($this->getSelectedClipId()){
            // одиночное редактирование клипа
            $formData[ 'reset_all_fields' ] = empty($keywords_set_id);
            $formData[ 'overwrite' ] = (empty($keywords_set_id)) ? $this->getCliplogEditor()->getPost( 'overwrite' ) : false;
        }else{
            //множественное редактирование
            $formData[ 'reset_all_fields' ] = (empty($overwrite) || $overwrite!==false) ? false : empty($keywords_set_id);
        }*/

        return $formData;
    }


    public function getFormMetadata () {
        $formData = array ();
        $sectionsValues = $this->getCliplogEditor()->getPost( 'sections_values' );
        $sectionsValues[ 'sections_values' ] = $sectionsValues;
        if ( $sectionsValues) {
            $formData = $sectionsValues;
        }
        $keywords = $this->getCliplogEditor()->getPost( 'keywords' );
        if ( $keywords ) {
            $formData[ 'keywords' ] = $keywords;
        }
        return $formData;
    }

    public function getFormLoggingData () {
        $formData = array ();
        $sections = $this->getCliplogEditor()->getPost( 'sections' );
        if ( $sections && is_array( $sections ) ) {
            foreach ( $sections as $sectionName ) {
                $formData[ $sectionName ] = '';
                $formData[ 'sections' ][ $sectionName ] = '';
            }
        }
        return $formData;
    }

    public function isDiscardLoggingChangesRequest () {
        return !!$this->getCliplogEditor()->getPost( 'discard_logging_changes' );
    }

    public function isChangeLoggingTemplateRequest () {
        return $this->getCliplogEditor()->getPost( 'apply_template' );
    }

    public function getChangedLoggingTemplateId () {
        return $this->getCliplogEditor()->getPost( 'applied_template_id' );
    }

    public function isChangeKeywordsTemplateRequest () {
        return $this->getCliplogEditor()->getPost( 'apply_keywords_set' );
    }

    public function getChangedKeywordsTemplateId () {
        return $this->getCliplogEditor()->getPost( 'applied_keywords_set_id' );
    }

    public function isNextClipRequest () {
        return !!$this->getCliplogEditor()->getPost( 'next_clip_id' );
    }

    public function getNextClipId () {
        return $this->getCliplogEditor()->getPost( 'next_clip_id' );
    }

    public function isSetFormPostLoggingData () {
        return $this->getCliplogEditor()->getPost( 'sections' );
    }

}