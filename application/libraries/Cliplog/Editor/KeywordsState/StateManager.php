<?php

namespace Libraries\Cliplog\Editor\KeywordsState;

/** @noinspection PhpIncludeInspection */
require_once( APPPATH . 'libraries/CodeIgniterAccessors.php' );

use Libraries\Accessors\CodeIgniterAccessors;

class StateManager {

    use CodeIgniterAccessors;

    private $keywordsStateBuffer = array();
    private $keywordsHiddenStateBuffer = array();

    /**
     * Process and replace state and hidden keywords info in the POST request.
     *
     * @param $postData
     * @return POST request data with the adjusted params
     */
    public function processInput(&$postData,$selectedClipId=NULL) {

        $keywordsIdList = $postData['keywords'];
        if ( $keywordsIdList && $this->hasTemporaryKeywords( $keywordsIdList ) ) {
            # Есть временные кл.слова, нужно создать
            $replacedList = $this->createKeywordsFromTemporary( $keywordsIdList );
        }
        # Также извлекаем и создаем скрытые кл.слова
        if ( isset( $replacedList ) ) {
            $keywordsIdList = $replacedList;
        }
        if ( $keywordsIdList && $this->hasHiddenKeywords( $keywordsIdList ) ) {
            # Есть скрытые слова
            $keywordsIdList = $this->createHiddenKeywordsFromTemporary( $keywordsIdList,$selectedClipId );
            $postData['keywords'] = $keywordsIdList;
        }

        return $postData;
    }

    public function isClipSaveRequest () {
        return !!$this->getPost( 'save' );
    }

    public function isStateInRequest () {
        return !!$this->getPost( 'keywordsState' );
    }

    public function getStateFromRequest () {
        if ( !$this->keywordsStateBuffer ) {
            $postData = $this->getPost( 'keywordsState' );
            if ( $postData ) {
                $this->keywordsStateBuffer = json_decode( $postData, TRUE );
            }
        }
        return $this->keywordsStateBuffer;
    }

    public function getHiddenStateFromRequest () {
        if ( !$this->keywordsHiddenStateBuffer ) {
            $postData = $this->getPost( 'keywordsHiddenState' );
            if ( $postData ) {
                $this->keywordsHiddenStateBuffer = json_decode( $postData, TRUE );
            }
        }
        return $this->keywordsHiddenStateBuffer;
    }

    public function getKeywordDataFromState ( $keywordId ) {
        if ( $keywordId ) {
            $keywordsState = $this->getStateFromRequest();
            if ( isset( $keywordsState[ $keywordId ] ) ) {
                return $keywordsState[ $keywordId ];
            }
        }
        return FALSE;
    }

    public function getKeywordDataFromHiddenState ( $keywordId ) {
        if ( $keywordId ) {
            $keywordsHiddenState = $this->getHiddenStateFromRequest();
            if ( isset( $keywordsHiddenState[ $keywordId ] ) ) {
                return $keywordsHiddenState[ $keywordId ];
            }
        }
        return FALSE;
    }

    public function hasTemporaryKeywords ( $idList ) {
        if ( $idList && is_array( $idList ) ) {
            /** @var \cliplog_keywords_model $keywordsModel */
            $keywordsModel = $this->getModel( 'cliplog_keywords_model' );
            foreach ( $idList as $keywordId ) {
                if ( $keywordsModel->isTemporaryKeyword( $keywordId ) ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function hasHiddenKeywords ( $idList ) {
        if ( $idList && is_array( $idList ) ) {
            /** @var \cliplog_keywords_model $keywordsModel */
            $keywordsModel = $this->getModel( 'cliplog_keywords_model' );
            foreach ( $idList as $keywordId ) {
                if ( $keywordsModel->isHiddenKeyword( $keywordId ) ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function createKeywordsFromTemporary ( $idList ) {
        if ( $idList && is_array( $idList ) ) {
            /** @var \cliplog_keywords_model $keywordsModel */
            $keywordsModel = $this->getModel( 'cliplog_keywords_model' );
            foreach ( $idList as $keywordIdFromForm ) {
                if ( $keywordsModel->isTemporaryKeyword( $keywordIdFromForm ) ) {
                    $temporaryKeywordData = $this->getKeywordDataFromState( $keywordIdFromForm );
                    $keywordId = $keywordsModel->createKeyword( $temporaryKeywordData );
                    unset( $idList[ $keywordIdFromForm ] );
                    $idList[ $keywordId ] = $keywordId;
                }
            }
        }
        return $idList;
    }

   public function createHiddenKeywordsFromTemporary ( $idList,$clipId=NULL ) {
        if ( $idList && is_array( $idList ) ) {
            /** @var \cliplog_keywords_model $keywordsModel */
            $keywordsModel = $this->getModel( 'cliplog_keywords_model' );
            foreach ( $idList as $keywordIdFromForm ) {
                if ( $keywordsModel->isHiddenKeyword( $keywordIdFromForm ) ) {
                    $hiddenKeywordData = $this->getKeywordDataFromHiddenState( $keywordIdFromForm );
                    $keywordId = $keywordsModel->createHiddenKeyword( $hiddenKeywordData ,$clipId);
                    unset( $idList[ $keywordIdFromForm ] );
                    $idList[ $keywordId ] = $keywordId;
                }
            }
        }
        return $idList;
    }

}