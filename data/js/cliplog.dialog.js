$( function () {

    /**
     * Попап "Manage All Keywords"
     */
    $( '#dialog-keywords-manage' ).dialog( {
        autoOpen : false,
        width : 520,
        //minHeight : 120,
        height : 350,
        modal : true,
        title : false,
        buttons : {
            Close : function () {
                $( this ).dialog( 'close' );
            }
        }
    } );

    /**
     * Вызов и очистка попапа "Manage All Keywords"
     */
    $( '.cliplog-manage-button' ).click( function () {
        $( '#dialog-keywords-manage' ).dialog( "option", "position", { my: "left top", at: "left top-100", of: $( this ).closest( '.cliplog_section' ) } );
        $( '#dialog-keywords-manage' ).dialog( 'open' );
        $( '.dialog-keyword-list' ).find( '.item' ).remove();
    } );

    /**
     * Метод вызова confirmBox() в Cliplog.js
     */
    $( '#dialog-confirm' ).dialog( {
        resizable : false,
        minHeight : 100,
        maxHeight : 200,
        autoOpen  : false,
        modal     : true,
        buttons   : {
            'Confirm' : function () {
                $( this ).dialog( 'close' );
                var callback = $( this ).data( 'confirmCallback' );
                if ( callback ) {
                    callback();
                }
            },
            Cancel    : function () {
                $( this ).dialog( 'close' );
                var callback = $( this ).data( 'cancelCallback' );
                if ( callback ) {
                    callback();
                }
            }
        }
    } );

    /**
     * Метод вызова alertBox() в Cliplog.js
     */
    $( '#dialog-alert' ).dialog( {
        resizable : false,
        minHeight : 100,
        maxHeight : 200,
        autoOpen  : false,
        modal     : true,
        buttons   : {
            'Ok' : function () {
                $( this ).dialog( 'close' );
            }
        }
    } );

} );