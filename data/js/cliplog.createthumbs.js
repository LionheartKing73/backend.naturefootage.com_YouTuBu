$( document ).ready( function () {
    createThumbs.init();
} );

createThumbs = {

    actionOptionName : 'create_thumb',
    box : {},
    indicator : '<div id="bowlG"><div id="bowl_ringG"><div class="ball_holderG"><div class="ballG"></div></div></div></div>',
    message : '<div class="message">%text%</div>',
    error : '<div class="error">%text%</div>',
    selectedClipName : '.footagesearch-clip.selected',
    selectedClipsIds : [],

    init : function () {
        this.box = $( '#dialog-change-thumb' );
        this.bindDialog();
    },

    bindDialog : function () {
        this.box.dialog( {
            resizable : false,
            minWidth : 347,
            autoOpen : false,
            modal : true,
            buttons : {
                'Create thumbnail' : function () {
                    createThumbs.actionCreateAndGoNextClip();
                },
                'Skip' : function () {
                    $( this ).dialog( 'close' );
                    createThumbs.openDialog( createThumbs.getNextClipId() );
                },
                Cancel : function () {
                    $( this ).dialog( 'close' );
                }
            }
        } );
    },

    start : function () {
        if ( this.findSelectedClips() ) {
            var currentClipId = this.getNextClipId();
            this.openDialog( currentClipId );
            deSelectAllClips();
        }
    },

    findSelectedClips : function () {
        var that = this;
        that.selectedClipsIds = [];
        $( that.selectedClipName ).each( function () {
            var currentClip = $( this );
            that.selectedClipsIds.push( currentClip.attr( 'data-clip-id' ) );
        } );
        return !!that.selectedClipsIds.length;
    },

    getNextClipId : function () {
        return this.selectedClipsIds.shift();
    },

    closeDialog : function () {
        this.box.dialog( 'close' );
    },

    openDialog : function ( clipId ) {
        if ( clipId ) {
            createThumbs.configureBox( clipId );
            createThumbs.showLayout();
            createThumbs.disableButton( 'create' );
            jQuery.post( 'en/changethumb/index/prepareinstance/' + clipId, {},
                function ( response ) {
                    if ( response && response.status == 1 ) {
                        createThumbs.showMessage( 'Click "Create thumbnail" to create new thumbnail image' );
                        createThumbs.enableButton( 'create' );
                    } else {
                        createThumbs.showError( 'An error occured. Please try again later' );
                    }
                },
                'json'
            );
             createThumbs.box.dialog( 'option', 'position', {
             my : 'center top',
             at : 'center top+150'
             } );
            createThumbs.box.dialog( 'open' );
        }
    },

    configureBox : function ( clipId ) {
        var clipBox = $( '#footagesearch-clip-' + clipId );
        if ( clipId && clipBox ) {
            var clipData = eval( "(" + clipBox.find( '.footagesearch-clip-play-btn' ).attr( 'data-clip' ) + ")" );
            //noinspection JSUnresolvedVariable
            this.box.find( 'video' ).attr( 'src', clipData.motion_thumb );
            this.box.attr( 'data-id', clipId );
        }
    },

    showLayout : function () {
        this.box.find( '.thumb' ).html( this.indicator );
    },

    showMessage : function ( text ) {
        this.box.find( '.thumb' ).html( this.message.replace( /%text%/, text ) );
    },

    showError : function ( text ) {
        this.box.find( '.thumb' ).html( this.error.replace( /%text%/, text ) );
    },

    disableButton : function ( type ) {
        var disableButton;
        switch ( type ) {
            case 'create':
                disableButton = 1;
                break;
            case 'all':
            default:
                disableButton = 0;
                break;
        }
        var buttonNumber = 0;
        $( 'div[aria-describedby="dialog-change-thumb"] .ui-dialog-buttonset' ).find( 'button' ).each( function () {
            buttonNumber++;
            if ( disableButton == 0 || disableButton == buttonNumber ) {
                $( this ).attr( 'disabled', true ).addClass( 'ui-state-disabled' );
            }
        } );
    },

    enableButton : function ( type ) {
        var enableButton;
        switch ( type ) {
            case 'create':
                enableButton = 1;
                break;
            case 'all':
            default:
                enableButton = 0;
                break;
        }
        var buttonNumber = 0;
        $( 'div[aria-describedby="dialog-change-thumb"] .ui-dialog-buttonset' ).find( 'button' ).each( function () {
            buttonNumber++;
            if ( enableButton == 0 || enableButton == buttonNumber ) {
                $( this ).attr( 'disabled', false ).removeClass( 'ui-state-disabled' );
            }
        } );
    },

    actionCreateAndGoNextClip : function () {
        var that = this;
        that.showLayout();
        that.disableButton( 'create' );
        var clipId = that.box.attr( 'data-id' );
        var videoId = $( '#dialog-change-thumb video' ).attr( 'id' );
        var timeOffset = document.getElementById( videoId ).currentTime;
        if ( clipId ) {
            jQuery.post( 'en/changethumb/index/saveimage/' + clipId, { 'timeOffset' : timeOffset },
                function ( response ) {
                    if ( response && response.status == 1 && response.image ) {
                        that.saveImage( clipId, response.image );
                        that.closeDialog();
                        that.openDialog( that.getNextClipId() );
                    } else {
                        that.showError( 'An error occured. Please try again later' );
                        that.enableButton( 'create' );
                    }
                },
                'json'
            );
        }
    },

    showImage : function ( imageLink ) {
        this.box.find( '.thumb' ).html( '<img src="' + imageLink + '" style="width: 216px;" />' );
    },

    saveImage : function ( clipId, imageLink ) {
        $( '#footagesearch-clip-' + clipId ).find( '.footagesearch-clip-thumb img' ).attr( 'src', imageLink );
    }

};