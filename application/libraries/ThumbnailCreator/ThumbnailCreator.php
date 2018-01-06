<?php

/** @noinspection PhpIncludeInspection */
require_once( APPPATH . '../scripts/aws/aws-autoloader.php' );

use Aws\S3\S3Client;

require_once( 'ThumbnailInstance.php' );
require_once( 'ThumbnailImage.php' );

class ThumbnailCreator {

    private $storeConfig;

    function setStoreConfig ( $storeConfig ) {
        $this->storeConfig = $storeConfig;
    }

    private function createInstanceSessionKey ( $input ) {
        if ( $input instanceof ThumbnailInstance ) {
            return $this->createInstanceSessionKey_ThumbnailInstance( $input );
        }
        return $this->createInstanceSessionKey_ClipData( $input );
    }

    private function createInstanceSessionKey_ThumbnailInstance ( ThumbnailInstance $thumbnailInstance ) {
        return md5( $thumbnailInstance->getClipId() );
    }

    private function createInstanceSessionKey_ClipData ( array $clipData ) {
        if ( isset( $clipData[ 'id' ] ) && $clipData[ 'id' ] ) {
            return md5( $clipData[ 'id' ] );
        }
        trigger_error( __METHOD__, E_USER_ERROR );
        return FALSE;
    }

    function createInstance ( $clipData ) {
        $thumbnailInstance = new ThumbnailInstance();
        $thumbnailInstance->setClipData( $clipData );
        if ( $thumbnailInstance->loadRemotePreview() ) {
            return $thumbnailInstance;
        }
        return FALSE;
    }

    function extractInstance ( $clipData ) {
        $instanceKey = $this->createInstanceSessionKey( $clipData );
        $sessionInstance = & $_SESSION[ 'ThumbnailInstance' ][ $instanceKey ];
        if ( $instanceKey && $sessionInstance ) {
            $thumbnailInstance = new ThumbnailInstance();
            $thumbnailInstance->setClipData( $clipData );
            if ( $thumbnailInstance->setSessionData( $sessionInstance ) ) {
                if ( $thumbnailInstance->checkResourceTemp() ) {
                    return $thumbnailInstance;
                }
            }
        }else{
            $thumbnailInstance = new ThumbnailInstance();
            $thumbnailInstance->setClipData( $clipData );
            if ( $thumbnailInstance->setSessionData( $sessionInstance ) ) {
                if ( $thumbnailInstance->checkResourceTemp() ) {
                    return $thumbnailInstance;
                }
            }
        }
        return FALSE;
    }

    function saveInstance ( ThumbnailInstance $thumbnailInstance ) {
        $instanceData = $thumbnailInstance->createDataForSession();
        if ( $instanceData ) {
            $instanceKey = $this->createInstanceSessionKey( $thumbnailInstance );
            $sessionInstance = & $_SESSION[ 'ThumbnailInstance' ][ $instanceKey ];
            $sessionInstance = $instanceData;
            if ( md5( json_encode( $sessionInstance ) ) === md5( json_encode( $instanceData ) ) ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function uploadImage ( ThumbnailImage $thumbnailImage ) {
        $s3Access = & $this->storeConfig[ 's3' ];
        $thumbConfig = & $this->storeConfig[ 'thumb' ];
        if ( isset( $s3Access ) && isset( $thumbConfig ) && isset( $thumbConfig[ 'bucket' ] ) ) {
            if ( ( $remoteImagePath = $this->getImageRemotePath( $thumbConfig[ 'path' ], $thumbnailImage ) ) ) {
                try {
                    $s3Client = S3Client::factory(
                        array (
                            'key'    => $s3Access[ 'key' ],
                            'secret' => $s3Access[ 'secret' ]
                        )
                    );
                    $result = $s3Client->putObject(
                        array (
                            'Bucket'     => $thumbConfig[ 'bucket' ],
                            'Key'        => $remoteImagePath,
                            'SourceFile' => $thumbnailImage->getImagePath(),
                            'ACL'        => Aws\S3\Enum\CannedAcl::PUBLIC_READ
                        )
                    );
                    if ( $result ) {
                        return TRUE;
                    }
                } catch ( Exception $exception ) {
                    echo "S3Exception: {$exception->getMessage()}";
                }
            }
        }
        return FALSE;
    }

    private function getImageRemotePath ( $folderName, ThumbnailImage $thumbnailImage ) {
        $fileName = pathinfo( $thumbnailImage->getResourceTempPath(), PATHINFO_FILENAME );
        if ( $folderName && $fileName ) {
            return "{$folderName}/{$fileName}.jpg";
        }
        return FALSE;
    }

    function getImageLinkForDb ( ThumbnailImage $thumbnailImage ) {
        $thumbConfig = & $this->storeConfig[ 'thumb' ];
        $fileName = pathinfo( $thumbnailImage->getResourceTempPath(), PATHINFO_FILENAME );
        if ( $thumbConfig && $fileName ) {
            return "{$thumbConfig['scheme']}://{$thumbConfig['bucket']}{$thumbConfig['path']}/{$fileName}.jpg";
        }
        trigger_error( __METHOD__, E_USER_ERROR );
        return FALSE;
    }

}