<?php

class ThumbnailInstance {

    private $clipData;

    private $tempFolderName = 'ThumbnailCreator';
    private $imageWidth = 192;
    private $imageHeight = 112;
    private $aspectImageHeight;

    private $originalWidth;
    private $originalHeight;

    private $resourceLink;
    private $resourceTempPath;

    public function setClipData ( $clipData ) {
        $this->clipData = $clipData;
        if ( !$this->checkClipData() ) {
            trigger_error( __METHOD__, E_USER_ERROR );
        }
    }

    public function getClipData () {
        return $this->clipData;
    }

    private function checkClipdata () {
        $data = & $this->clipData;
        if ( isset( $data[ 'id' ] ) && $data[ 'id' ] && isset( $data[ 'code' ] ) && $data[ 'code' ] ) {
            return TRUE;
        }
        return FALSE;
    }


    public function getClipId () {
        if ( isset( $this->clipData[ 'id' ] ) && $this->clipData[ 'id' ] ) {
            return $this->clipData[ 'id' ];
        }
        return FALSE;
    }

    private function setResourceLink ( $resourceLink ) {
        if ( $resourceLink ) {
            $this->resourceLink = $resourceLink;
        } else {
            trigger_error( __METHOD__, E_USER_ERROR );
        }
    }

    private function getResourceLink () {
        if ( !$this->resourceLink ) {
            $clipData = $this->getCLipData();
            if ( isset( $clipData[ 'motion_thumb' ] ) && $clipData[ 'motion_thumb' ] ) {
                $this->resourceLink = $clipData[ 'motion_thumb' ];
            } elseif ( isset( $clipData[ 'preview' ] ) && $clipData[ 'preview' ] ) {
                $this->resourceLink = $clipData[ 'preview' ];
            } else {
                trigger_error( __METHOD__, E_USER_ERROR );
            }
        }
        return $this->resourceLink;
    }

    private function getResourceName () {
        return pathinfo( $this->getResourceTempPath(), PATHINFO_FILENAME );
    }

    private function getImagePath () {
        $basePath = realpath( APPPATH . '../' );
        $imageFolderPath = "{$basePath}/data/ThumbnailCreator/";
        if ( !is_dir( $imageFolderPath ) ) {
            if ( !mkdir( $imageFolderPath ) ) {
                trigger_error( __METHOD__, E_USER_ERROR );
            }
        }
        $imagePath = $imageFolderPath . $this->getResourceName() . '.jpg';
        return $imagePath;
    }

    private function checkOrCreateTempFolder ( $folderPath ) {
        if ( is_dir( $folderPath ) ) {
            return TRUE;
        }
        return mkdir( $folderPath );
    }

    private function setResourceTempPath ( $resourceTempPath ) {
        if ( $resourceTempPath ) {
            $this->resourceTempPath = $resourceTempPath;
        } else {
            trigger_error( __METHOD__, E_USER_ERROR );
        }
    }

    private function getResourceTempPath () {
        if ( !$this->resourceTempPath ) {
            $tempFolderPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->tempFolderName . DIRECTORY_SEPARATOR;
            if ( $this->checkOrCreateTempFolder( $tempFolderPath ) ) {
                $tempName = pathinfo( $this->getResourceLink(), PATHINFO_BASENAME );
                $this->resourceTempPath = $tempFolderPath . $tempName;
            } else {
                trigger_error( __METHOD__, E_USER_ERROR );
            }
        }
        return $this->resourceTempPath;
    }

    private function copyResourceToTempFolder () {
        if ( ( $resourceTempPath = $this->getResourceTempPath() ) ) {
            $resourceLink = $this->getResourceLink();
            $resourceTempHandler = fopen( $resourceTempPath, 'w' );
            $resourceLinkHandler = fopen( $resourceLink, 'r' );
            if ( $resourceLinkHandler && $resourceTempHandler ) {
                while ( !feof( $resourceLinkHandler ) ) {
                    $filePart = fread( $resourceLinkHandler, 1024 );
                    fwrite( $resourceTempHandler, $filePart );
                }
                $resourceLinkHandler && fclose( $resourceLinkHandler );
                $resourceTempHandler && fclose( $resourceTempHandler );
                if ( filesize( $resourceTempPath ) > 0 ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function getImageWidth () {
        return $this->imageWidth;
    }

    private function getImageHeight () {
        return $this->imageHeight;
    }

    private function takeOrirginalSize () {
        if ( isset( $this->clipData[ 'digital_file_frame_size' ] ) && $this->clipData[ 'digital_file_frame_size' ] ) {
            $size = $this->clipData[ 'digital_file_frame_size' ];
            $sizeParts = explode( ' ', $size );
            $size = array_pop( $sizeParts );
            $size = trim( $size, '() ' );
            $sizeParts = explode( 'x', $size );
            if ( count( $sizeParts ) == 2 ) {
                $this->originalWidth = array_shift( $sizeParts );
                $this->originalHeight = array_shift( $sizeParts );
                return TRUE;
            }
        }
        trigger_error( __METHOD__, E_USER_ERROR );
        return FALSE;
    }

    private function getOriginalWidth () {
        if ( !$this->originalWidth ) {
            $this->takeOrirginalSize();
        }
        return $this->originalWidth;
    }

    private function getOriginalHeight () {
        if ( !$this->originalHeight ) {
            $this->takeOrirginalSize();
        }
        return $this->originalHeight;
    }

    private function getAspectImageHeight () { // @TODO createImage()
        if ( !$this->aspectImageHeight ) {
            $imageWidth = $this->getImageWidth();
            $originalWidth = $this->getOriginalWidth();
            $originalHeight = $this->getOriginalHeight();
            $aspect = $originalWidth / $imageWidth;
            $aspectImageHeight = (int) $originalHeight / $aspect;
            if ( $aspectImageHeight > 0 ) {
                $this->aspectImageHeight = $aspectImageHeight;
            } else {
                trigger_error( __METHOD__, E_USER_ERROR );
            }
        }
        return $this->aspectImageHeight;
    }

    private function verifySessionClipId ( $sessionClipId ) {
        return ( $sessionClipId && $sessionClipId == $this->getClipId() );
    }

    function loadRemotePreview () {
        $resourceLink = $this->getResourceLink();
        return $this->copyResourceToTempFolder( $resourceLink );
    }

    function createImage ( $timeOffset ) {
        $thumbnailImage = new ThumbnailImage();
        $thumbnailImage->setResourceTempPath( $this->getResourceTempPath() );
        $thumbnailImage->setImagePath( $this->getImagePath() );
        $thumbnailImage->setTimeOffset( $timeOffset );
        $thumbnailImage->setImageWidth( $this->getImageWidth() );
        //$thumbnailImage->setImageHeight( $this->getAspectImageHeight() ); // @TODO Нужно определять разрешение файла. Сейчас извлекается с lib_clips
        $thumbnailImage->setImageHeight( $this->getImageHeight() );
        if ( $thumbnailImage->buildImage() ) {
            return $thumbnailImage;
        }
        return FALSE;
    }

    function createDataForSession () {
        $resultData = array ();
        $resultData[ 'ResourceLink' ] = $this->getResourceLink();
        $resultData[ 'ResourceTempPath' ] = $this->getResourceTempPath();
        $resultData[ 'ClipId' ] = $this->getClipId();
        return $resultData;
    }

    function setSessionData ( $sessionData ) {
        if ( $this->verifySessionClipId( $sessionData[ 'ClipId' ] ) ) {
            $this->setResourceLink( $sessionData[ 'ResourceLink' ] );
            $this->setResourceTempPath( $sessionData[ 'ResourceTempPath' ] );
            return TRUE;
        }
        return FALSE;
    }

    function checkResourceTemp () {
        $resourceTempPath = $this->getResourceTempPath();
        return ( is_file( $resourceTempPath ) && filesize( $resourceTempPath ) > 0 );
    }

}