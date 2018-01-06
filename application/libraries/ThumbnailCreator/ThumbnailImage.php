<?php

class ThumbnailImage {

    private $resourceTempPath;
    private $imagePath;
    private $timeOffset;
    private $imageWidth;
    private $imageHeight;

    function setResourceTempPath ( $resourceTempPath ) {
        if ( $resourceTempPath ) {
            $this->resourceTempPath = $resourceTempPath;
        } else {
            trigger_error( __METHOD__, E_USER_ERROR );
        }
    }

    function getResourceTempPath () {
        if ( $this->resourceTempPath ) {
            return $this->resourceTempPath;
        }
        trigger_error( __METHOD__, E_USER_ERROR );
        return FALSE;
    }

    function setImagePath ( $imagePath ) {
        $this->imagePath = $imagePath;
    }

    function getImagePath () {
        return $this->imagePath;
    }

    function setTimeOffset ( $timeOffset ) {
        $this->timeOffset = $timeOffset;
    }

    private function getTimeOffset () {
        return $this->timeOffset;
    }

    function setImageWidth ( $imageWidth ) {
        $this->imageWidth = $imageWidth;
    }

    private function getImageWidth () {
        return $this->imageWidth;
    }

    function setImageHeight ( $imageHeight ) {
        $this->imageHeight = $imageHeight;
    }

    private function getImageHeight () {
        return $this->imageHeight;
    }

    function buildImage () {
        if ( is_file( $this->getImagePath() ) ) {
            unlink( $this->getImagePath() );
        }
        # ffmpeg -i /tmp/ThumbnailCreator/343714.mp4 -f image2 -vframes 1 -s 192x128 -ss 3.123 -y /tmp/ThumbnailCreator/343714.jpg
        $commandPattern = 'ffmpeg -i %sourcePath% -f image2 -vframes 1 -s %imageWidth%x%imageHeight% -ss %offsetTime% -y %imagePath%';
        $command = str_replace(
            array (
                '%sourcePath%',
                '%imagePath%',
                '%imageWidth%',
                '%imageHeight%',
                '%offsetTime%'
            ),
            array (
                $this->getResourceTempPath(),
                $this->getImagePath(),
                $this->getImageWidth(),
                $this->getImageHeight(),
                $this->getTimeOffset()
            ),
            $commandPattern
        );
        $command && exec( $command );
        if ( is_file( $this->getImagePath() ) && filesize( $this->getImagePath() ) > 0 ) {
            return TRUE;
        }
        return FALSE;
    }

    function getImageLink () {
        $imagePath = $this->getImagePath();
        $imagePathParts = explode( 'data/ThumbnailCreator', $imagePath );
        if ( count( $imagePathParts ) == 2 ) {
            return '/data/ThumbnailCreator' . array_pop( $imagePathParts );
        }
        return FALSE;
    }

}