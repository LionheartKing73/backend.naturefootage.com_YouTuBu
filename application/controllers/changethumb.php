<?php

error_reporting( TRUE );

/** @noinspection PhpIncludeInspection */
require_once( APPPATH . 'libraries/ThumbnailCreator/ThumbnailCreator.php' );
/**
 * Class Cliplog
 *
 * @property clips_model $clips
 */
class Changethumb extends CI_Controller {

    const ResponseStatus = 'status';
    const ResponseAction = 'action';
    const ResponseImageLink = 'image';

    const StatusOk = 1;
    const StatusError = 0;

    const ActionInstanceCreated = 1;
    const ActionInstanceExtracted = 2;
    const ActionImageCreated = 3;
    const ActionImageSaved = 4;

    /** @var ThumbnailCreator */
    private $thumbnailCreator;


    var $id;
    var $langs;

    private $storeConfig;

    private $responseData;
    private $dbMaster;
    private $clipId;
    private $clipData;

    function __construct () {
        parent::__construct();
        $this->dbMaster = $this->load->database( 'master', TRUE );
        $this->load->model( 'clips_model', 'clips' );
        $this->langs = $this->uri->segment( 1 );
        $this->settings = $this->api->settings();
        $this->setClipId();
        $this->setClipData();
        $this->loadStoreConfig();
        $this->createThumbnailCreator();
    }

    public function index () {
        if ( $this->api->is_ajax_request() ) {
            switch ( $this->uri->segment( 4 ) ) {
                case 'prepareinstance':
                    $this->prepareInstance();
                    break;
                case 'createimage':
                    $this->createImage();
                    break;
                case 'saveimage':
                    $this->saveImage();
                    break;
                default:
                    $this->setResponseFragment( self::ResponseStatus, self::StatusError );
                    break;
            }
        }
        $this->sendResponse();
    }

    /**
     * Ajax. Запрос на создание инстанса, инициализация
     *
     * @link /en/changethumb/index/prepareinstance/#clipId#
     */
    private function prepareInstance () {
        $this->createOrExtractInstance();
        $this->sendResponse();
    }

    /**
     * Ajax. Запрос на создание и выод изображения
     *
     * @link /en/changethumb/index/createimage/#clipId#
     * @post timeOffset
     */
    private function createImage () {
        if ( ( $thumbnailInstance = $this->createOrExtractInstance() ) ) {
            $thumbnailImage = $thumbnailInstance->createImage( $this->getTimeOffset() );
            if ( $thumbnailImage ) {
                $this->setResponseFragment( self::ResponseAction, self::ActionImageCreated );
                $this->setResponseFragment( self::ResponseImageLink, $thumbnailImage->getImageLink() );
            } else {
                $this->setResponseFragment( self::ResponseStatus, self::StatusError );
            }
        }
        $this->sendResponse();
    }

    /**
     * Ajax. Запрос на создание и добавление изображения к клипу
     *
     * @link /en/changethumb/index/saveimage/#clipId#
     * @post timeOffset
     */
    private function saveImage () {
        $this->dbMaster->where(array(
            'clip_id'=>$this->uri->segment( 5 ),
            'resource'=>'jpg',
            'type'=>0
        ));
        $this->load->model('clips_model');
        $this->dbMaster->update('lib_clips_res',array('time_offset' => $this->input->post('timeOffset'),'location'=>''));
        $thumb=$this->clips_model->get_clip_path($this->uri->segment( 5 ),'thumb');
        $this->setResponseFragment( self::ResponseAction, self::ActionImageSaved );
        $this->setResponseFragment( self::ResponseImageLink, $thumb );
        //$this->sendResponse();
        $thumbnailInstance = $this->createOrExtractInstance();
        if ( /*$thumbnailInstance*/ true  ) {
            $thumbnailImage = $thumbnailInstance->createImage( $this->getTimeOffset() );
            if ( $thumbnailImage && $this->getThumbnailCreator()->uploadImage( $thumbnailImage ) ) {
                if ( $this->addThumbnailImageToClip( $thumbnailImage ) ) {
                    $this->setResponseFragment( self::ResponseAction, self::ActionImageSaved );
                    $this->setResponseFragment( self::ResponseImageLink, $thumbnailImage->getImageLink() );
                } else {
                    $this->setResponseFragment( self::ResponseStatus, self::StatusError );
                }
            } else {
                $this->setResponseFragment( self::ResponseStatus, self::StatusError );
            }
        }
        $this->sendResponse();

    }

    private function createThumbnailCreator () {
        $this->thumbnailCreator = new ThumbnailCreator();
        $this->thumbnailCreator->setStoreConfig( $this->getStoreConfig() );
    }

    private function getThumbnailCreator () {
        return $this->thumbnailCreator;
    }

    private function createOrExtractInstance () {
        $thumbnailInstance = $this->getThumbnailCreator()->extractInstance( $this->getClipData() );
        if ( $thumbnailInstance === true ) {
            $this->setResponseFragment( self::ResponseStatus, self::StatusOk );
            $this->setResponseFragment( self::ResponseAction, self::ActionInstanceExtracted );
        } else {
            $thumbnailInstance = $this->getThumbnailCreator()->createInstance( $this->getClipData() );
            if ( $thumbnailInstance ) {
                $thumbnailInstance->setClipData( $this->getClipData() );
                $this->getThumbnailCreator()->saveInstance( $thumbnailInstance );
                $this->setResponseFragment( self::ResponseStatus, self::StatusOk );
                $this->setResponseFragment( self::ResponseAction, self::ActionInstanceCreated );
            } else {
                $this->setResponseFragment( self::ResponseStatus, self::StatusError );
            }
        }
        return $thumbnailInstance;
    }

    private function addThumbnailImageToClip ( ThumbnailImage $thumbnailImage ) {
        $imageLinkForDb = $this->getThumbnailCreator()->getImageLinkForDb( $thumbnailImage );
        if ( $imageLinkForDb ) {
            $this->dbMaster->select( 'id' );
            $this->dbMaster->where(
                array (
                    'type'     => 0,
                    'resource' => 'jpg',
                    'clip_id'  => $this->getClipId()
                )
            );
            $result = $this->dbMaster->get( 'lib_clips_res' );
            /** @noinspection PhpUndefinedMethodInspection */
            if ( $result && $result->num_rows() > 0 ) {
                $this->dbMaster->where(
                    array (
                        'clip_id'  => $this->getClipId(),
                        'resource' => 'jpg',
                        'type'     => 0
                    )
                );
                $this->dbMaster->update(
                    'lib_clips_res',
                    array (
                        'location' => $imageLinkForDb
                    )
                );
            } else {
                $this->dbMaster->insert(
                    'lib_clips_res',
                    array (
                        'clip_id'  => $this->getClipId(),
                        'resource' => 'jpg',
                        'type'     => 0,
                        'location' => $imageLinkForDb
                    )
                );
            }
            return TRUE;
        }
        return FALSE;
    }

    private function setClipData () {
        $clipData = $this->clips->get_clip_info( $this->getClipId(), $this->langs );
        if ( $clipData ) {
            $this->clipData = $clipData;
        } else {
            trigger_error( 'Empty clipData', E_USER_ERROR );
        }
    }

    private function getClipData () {
        return $this->clipData;
    }

    private function setClipId () {
        if ( ( $clipId = (int) $this->uri->segment( 5 ) ) ) {
            $this->clipId = $clipId;
        } else {
            trigger_error( 'Empty clipId', E_USER_ERROR );
        }
    }

    private function getClipId () {
        return $this->clipId;
    }

    private function setResponseFragment ( $fragmentName, $fragmentValue ) {
        $this->responseData[ $fragmentName ] = $fragmentValue;
    }

    private function getResponse () {
        return $this->responseData;
    }

    private function sendResponse () {
        $this->output->set_content_type( 'application/json' );
        $jsonData = json_encode( $this->getResponse() );
        echo $jsonData;
        die();
    }

    private function getTimeOffset () {
        $tiemOffset = $this->input->post( 'timeOffset' );
        if ( !empty($tiemOffset) && $tiemOffset !='' ) {
            return $tiemOffset;
        }
        return 0;
    }

    private function loadStoreConfig () {
        /** @noinspection PhpIncludeInspection */
        include( APPPATH . 'config/store.php' );
        if ( isset( $store ) ) {
            $this->storeConfig = $store;
        } else {
            trigger_error( __METHOD__, E_USER_ERROR );
        }
    }

    private function getStoreConfig () {
        return $this->storeConfig;
    }

}