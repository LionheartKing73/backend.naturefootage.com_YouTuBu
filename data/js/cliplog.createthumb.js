$( document ).ready( function () {
    createThumb.init();
} );

createThumb = {

    box : {},
    button : {},
    indicator : '<div id="bowlG"><div id="bowl_ringG"><div class="ball_holderG"><div class="ballG"></div></div></div></div>',
    message : '<div class="message">%text%</div>',
    error : '<div class="error">%text%</div>',

    init : function () {
        this.box = $( '#dialog-change-thumb' );
        this.button = $( '#change-thumb-button' );
        this.bindButton();
    },

    bindButton : function () {
        this.box.dialog( {
            resizable : false,
            minWidth : 347,
            autoOpen : false,
            modal : true,
            buttons : {
                'Create thumbnail' : function () {
                    createThumb.actionCreateAndSaveThumbnail();
                },
                Cancel : function () {
                    $( this ).dialog( 'close' );
                }
            }
        } );
        this.button.on( 'click', this.openDialog );
    },

    closeDialog : function () {
        this.box.dialog( 'close' );
    },

    openDialog : function () {
        createThumb.showLayout();
        createThumb.disableButton( 'create' );
        var clipId = createThumb.box.attr( 'data-id' );
        if ( clipId ) {
            jQuery.post( 'en/changethumb/index/prepareinstance/' + clipId, {},
                function ( response ) {
                    if ( response && response.status == 1 ) {
                        createThumb.showMessage( 'Click "Create thumbnail" to create new thumbnail image' );
                        createThumb.enableButton( 'create' );
                    } else {
                        createThumb.showError( 'An error occured. Please try again later' );
                    }
                },
                'json'
            );
        }
        createThumb.box.dialog( 'option', 'position', {
            my : 'left top',
            at : 'right+100 top',
            of : $( '.cliplog-edit-clip-box' )
        } );
        createThumb.box.dialog( 'open' );
    },

    actionCreateAndSaveThumbnail : function () {
        var that = this;
        that.showLayout();
        that.disableButton( 'create' );
        var clipId = that.box.attr( 'data-id' );
        /*var videoId = $( '#dialog-change-thumb video' ).attr( 'id' );
        var timeOffset=$(#dialog-change-thumb)
        alert(videoId);*/
        //var timeOffset = document.getElementById( videoId ).currentTime;
        var timeOffset=$( '#dialog-change-thumb .vjs-current-time-display' ).text().match(/(\d*:)?\d+/gi );
        timeOffset=timeOffset[0]; // 0:02  format
        if ( clipId ) {
            jQuery.post( 'en/changethumb/index/saveimage/' + clipId, { 'timeOffset' : timeOffset },
                function ( response ) {
                    if ( response && (response.status == 1 || response.action == 4) && response.image ) {
                        //that.showImage( response.image );
                        that.saveImage( response.image );
                        //that.enableButton( 'create' );
                        that.closeDialog();
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

    saveImage : function ( imageLink ) {
        $( '.footagesearch-clip-thumb img' ).replaceWith( '<img src="' + imageLink + '" style="width: 216px;" />' );
        $( 'li.jcarousel-item.active img' ).attr('src',imageLink);
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
    }

};