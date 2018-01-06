<?php

namespace Libraries\Cliplog\Editor\Logging;

use Libraries\Cliplog\Editor\CliplogEditor;

class LoggingTemplate {

    protected $templateEmpty = array(
        'clip_description' => '',
        'clip_notes'       => '',
        'license_restrictions'      => '',
        'audio_video'      => '',
        'date_filmed'      => '',
        'collection'       => '',
        'brand'            => '',
        'add_collection'   => '',
        'price_level'      => '',
        'calc_price_level' => '',
        'license_type'     => '',
        'releases'         => '',
        'file_formats'     => '',
        'shot_type'        => '',
        'subject_category' => '',
        'primary_subject'  => '',
        'other_subject'    => '',
        'appearance'       => '',
        'actions'          => '',
        'time'             => '',
        'habitat'          => '',
        'concept'          => '',
        'location'         => '',
        'country'          => '',
        'add_formats'      => 'checked'
    );

    protected $cliplogEitor;

    public function __construct ( CliplogEditor $cliplogEditor ) {
        $this->cliplogEitor = $cliplogEditor;
    }

    public function getCliplogEditor () {
        return $this->cliplogEitor;
    }

    public function getEmptyTemplate () {
        return $this->templateEmpty;
    }

    public function setActiveTemplateId ( $templateId=false ) {
        if ( $templateId!==false ) {
            $this->getCliplogEditor()->setSessionValue( 'loggingId', $templateId );
        } else {
            //$this->getCliplogEditor()->removeSessionValue( 'loggingId' );
        }
    }

    public function getActiveTemplateId () {
        return $this->getCliplogEditor()->getSessionValue( 'loggingId' );
    }

    public function getTemplateData ( $templateId ) {
        $loggingData = array();
        if ( $templateId ) {
            /** @var \cliplog_templates_model $cliplogTemplatesModel */
            $cliplogTemplatesModel = $this->getCliplogEditor()->getModel( 'cliplog_templates_model' );
            $templateData = $cliplogTemplatesModel->getLoggingTemplate( $templateId );
            if ( $templateData && isset( $templateData[ 'json' ] ) ) {
                $jsonData = json_decode( $templateData[ 'json' ], TRUE );
                if ( isset( $jsonData[ 'sections' ] ) && is_array( $jsonData[ 'sections' ] ) ) {
                    foreach ( $jsonData[ 'sections' ] as $sectionName ) {

                        $loggingData[ $sectionName ] = '';
                    }
                }
                if ( isset( $jsonData[ 'keywords_sections_visible' ] ) ) {
                    $loggingData[ 'keywords_sections_visible' ] = $jsonData[ 'keywords_sections_visible' ];
                }
                if ( !empty( $jsonData[ 'add_formats' ] ) ) {
                    $loggingData[ 'add_formats' ] = $jsonData[ 'add_formats' ];
                }
                $loggingData[ 'cliplog_template' ] = $templateData;
            }
        }
        # Если данных нет - загружаем пустой шаблон
        if ( !$loggingData ) {
            $loggingData = $this->getEmptyTemplate();
        }
        return $loggingData;
    }

}