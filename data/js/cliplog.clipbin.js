var debugErrors = true;

function actionSpoiler ( element ) {
    var find = $( element ).attr( 'data-spoiler'),
        action = $( element ).attr( 'data-spoiler-action'),
        box = ( find ) ? $( document ).find( find ) : $( element );
    if ( box ) {
        if ( !action ) {
            debugErrors && console.log( 'Spoiler: ' + find );
            if ( box.is( '.expanded' ) ) {
                $( element ).addClass( 'collapsed' ).removeClass( 'expanded' );
                box.addClass( 'collapsed' ).removeClass( 'expanded' );
            } else {
                $( element ).addClass( 'expanded' ).removeClass( 'collapsed' );
                box.addClass( 'expanded' ).removeClass( 'collapsed' );
            }
        } else {
            debugErrors && console.log( 'Spoiler [' + action + ']: ' + find );
            if ( action == 'expand' ) {
                $( element ).addClass( 'expanded' ).removeClass( 'collapsed' );
                box.addClass( 'expanded' ).removeClass( 'collapsed' );
            } else if ( action == 'collapse' ) {
                $( element ).addClass( 'collapsed' ).removeClass( 'expanded' );
                box.addClass( 'collapsed' ).removeClass( 'expanded' );
            }
        }
    }

}

function expandCollapse ( element ) {
    var group = $(element).closest('.group');
    if($(group).hasClass('collapsed')){
        $(group).removeClass('collapsed');
    }else{
        $(group).addClass('collapsed');
    }
}

function executeContextMenuAction ( id, key ) {
    if ( key && id ) {
        var execute = 'fs.backend_cb.' + key + '( ' + id + ' )';
        debugErrors && console.log( execute );
        try {
            eval( execute );
        } catch ( error ) {
            debugErrors && console.log( error );
        }
    }
}

function addClipsToClipBin( clipBinId, clipsIds ) {
    if ( clipBinId && clipsIds ) {
        $.post( '/en/backend_clipbins/items/' + clipBinId, {
                items_ids : clipsIds
            }
        );
    }
}

function refreshClipBinView ( clipBinId ) {
    if ( clipBinId ) {
        var clipBinSpan = $( '.items-count[data-clipbin-id="' + clipBinId + '"]' );
        $.post(
            '/en/backend_clipbins/get_clipbin_items_count',
            {
                clipbin_id : clipBinId
            },
            function ( data ) {
                if ( data.success && data.count ) {
                    debugErrors && console.log( 'refreshClipBinView( ' + clipBinId + ' ) => ' + data.count );
                    clipBinSpan.html( '(' + data.count + ')' );
                    if ( clipBinId === getSelectedClipBinId() ) {
                        refreshClipBinWell();
                    }
                }
            }
            ,
            'json'
        );
    }
}

function refreshClipBinWell () {
    var clipBinId = getSelectedClipBinId();
    var well = $( '.clipbin-well' );
    if ( clipBinId ) {
        $.post(
            '/en/backend_clipbins/get_clipbin_well_html',
            {
                clipbin_id : clipBinId
            },
            function ( data ) {
                if ( data.success && data.html ) {
                    debugErrors && console.log( 'refreshClipBinWell' );
                    well.html( data.html );
                    if ( data.full ) {
                        well.removeClass( 'empty' );
                    } else {
                        well.addClass( 'empty' );
                    }
                }
            },
            'json'
        );
    }
}

function getSelectedClipBinId () {
    var wellDiv = $( '.clipbin-well' );
    var clipBinId = wellDiv.attr( 'data-clipbin-id' );
    if ( clipBinId ) {
        return clipBinId;
    }
    return false;
}

function unselectSelectedClips () {
    var selected = $( '.clips-list' ).find( '.footagesearch-clip.selected' );
    selected.each( function () {
        $( this ).removeClass( 'selected' );
    } );
}



$( document ).ready( function () {

    var cbWidgetHolder = $( '.clipbins-widget-holder' );

    $( '.left-box' ).on( 'click', '.spoiler-control', function () {
        actionSpoiler( this );
    } );

    $( '.left-box' ).on( 'click', '.filter_label', function () {
        expandCollapse( this );
    } );

    cbWidgetHolder.on( 'click', '.clipbin-action', function () {
        var element = $( this );
        var box_folder = $( '.edit-action-folder' );
        var box_clipbin = $( '.edit-action-clipbin' );
        if ( element.is( '[data-action="folder"]' ) ) {
            if ( box_folder.is( ':visible' ) ) {
                box_folder.hide();
            } else {
                box_clipbin.hide();
                box_folder.show();
            }
        } else if ( element.is( '[data-action="clipbin"]' ) ) {
            if ( box_clipbin.is( ':visible' ) ) {
                box_clipbin.hide();
            } else {
                box_folder.hide();
                box_clipbin.show();
            }
        }
    } );

    $( '.clipbin-edit-actions form' ).submit( function () {
        var form = $( this );
        var input = form.find( 'input[type="text"]' );
        if ( input.val() == '' ) {
            event.stopPropagation();
            event.preventDefault();
            input.addClass( 'empty-error' );
        }
    } );

    cbWidgetHolder.on( 'click', '.clipbin-folder-item', function ( e ) {
        if ( !jQuery( e.target ).hasClass( 'clipbin-folder-actions' ) ) {
            event.stopPropagation();
            event.preventDefault();
            var element = $( this ).parent();
            if ( element.is( '.expanded' ) ) {
                element.removeClass( 'expanded' ).addClass( 'collapsed' );
            } else {
                element.removeClass( 'collapsed' ).addClass( 'expanded' );
            }
        }
    } );
    // Clipbin
    $.contextMenu( {
        selector : '.clipbin-item:not(.gallery):not(.sequence) .clipbin-actions',
        trigger  : 'left',
        callback : function ( key ) {
            var id = jQuery( this ).parents( '.clipbin-item' ).data( 'clipbin-id' );
            executeContextMenuAction( id, key );
        },
        items    : {
            'editClipbin'       : {name : 'Rename Clipbin'},
            'deleteClipbin'     : {name : 'Delete Clipbin'},
            'setDefaultClipbin' : {name : 'Make Active'},
            'makeGallery'       : {name : 'Make a Gallery'},
            'make_featured_gallery' : {name : 'Make Featured Gallery'},
            'makeSequence'      : {name : 'Make a Sequence'}
        }
    } );
    // Gallery
    $.contextMenu( {
        selector : '.clipbin-item.gallery:not(.featured) .clipbin-actions',
        trigger  : 'left',
        callback : function ( key ) {
            var id = jQuery( this ).parents( '.clipbin-item' ).data( 'clipbin-id' );
            executeContextMenuAction( id, key );
        },
        items    : {
            'editClipbin'       : {name : 'Rename Gallery'},
            'deleteClipbin'     : {name : 'Delete Gallery'},
            'setDefaultClipbin' : {name : 'Make Active'},
            'make_featured_gallery' : {name : 'Make Featured Gallery'},
            'make_clipbin' : {name : 'Make Clipbin'}
        }
    } );
    // Featured Gallery
    $.contextMenu( {
        selector : '.clipbin-item.gallery.featured .clipbin-actions',
        trigger  : 'left',
        callback : function ( key ) {
            var id = jQuery( this ).parents( '.clipbin-item' ).data( 'clipbin-id' );
            executeContextMenuAction( id, key );
        },
        items    : {
            'editClipbin'       : {name : 'Rename Gallery'},
            'deleteClipbin'     : {name : 'Delete Gallery'},
            'setDefaultClipbin' : {name : 'Make Active'},
            'make_ordinary_gallery' : {name : 'Make Gallery'},
            'make_clipbin' : {name : 'Make Clipbin'}


        }
    } );
    // Sequence
    $.contextMenu( {
        selector : '.clipbin-item.sequence .clipbin-actions',
        trigger  : 'left',
        callback : function ( key ) {
            var id = jQuery( this ).parents( '.clipbin-item' ).data( 'clipbin-id' );
            executeContextMenuAction( id, key );
        },
        items    : {
            'editClipbin'       : {name : 'Rename Sequence'},
            'deleteClipbin'     : {name : 'Delete Sequence'},
            'setDefaultClipbin' : {name : 'Make Active'},
            'make_clipbin' : {name : 'Make Clipbin'}
        }
    } );

    $.contextMenu( {
        selector : '.clipbin-folder-actions',
        trigger  : 'left',
        callback : function ( key ) {
            var id = jQuery( this ).parents( '.clipbin-folder-item' ).data( 'folder-id' );
            executeContextMenuAction( id, key );
        },
        items    : {
            'editFolder'   : {name : 'Rename Folder'},
            'deleteFolder' : {name : 'Delete Folder'}
        }
    } );

    cbWidgetHolder.on( 'submit', '.footagesearch-clipbin-create-clipbin-form', function () {
        var form = jQuery( this );
        var title = jQuery( '[name="clipbin_title"]', form ).val();
        var folderID = jQuery( '[name="clipbin_folder_id"]', form ).val(), clipbinID = jQuery( '[name="clipbin_id"]', form ).val();
        if ( title ) {
            fs.backend_cb.saveClipBin( title, folderID, clipbinID );
        } else {
            fs.backend_cb.showClipbinMessage( 'Enter clipbin name', true );
        }
        return false;
    } );

    cbWidgetHolder.on( 'submit', '.footagesearch-clipbin-create-folder-form', function () {
        var name = jQuery( '[name="folder_name"]' ).val();
        var folderID = jQuery( '[name="folder_id"]' ).val();
        if ( name ) {
            fs.backend_cb.saveClipBinFolder( name, folderID );
        } else {
            fs.backend_cb.showClipbinMessage( 'Enter folder name', true );
        }
        return false;
    } );

    fs.backend_cb.init();

} );

var fs = {};

fs.delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

fs.backend_cb = {

    expandedFolders       : [],
    binListScrollTop      : 0,
    focusedElementSelector        : null,

    init                  : function () {
        fs.backend_cb.refreshWidgetArea();
        fs.backend_cb.initEventListeners();
    },

    initEventListeners : function(){
        var clipID,
            $clipsList = jQuery('.clips-list');
        //delete item from current bin
        $clipsList.on('click', '.clipbin-delete-item', function(){
            clipID = jQuery(this).parents('.footagesearch-clip').data('clip-id');
            fs.backend_cb.removeItem(clipID);
            return false;
        });

        //add item to current bin
        $clipsList.on('click', '.clipbin-add-item', function(){
            clipID = jQuery(this).parents('.footagesearch-clip').data('clip-id');
            fs.backend_cb.addItemsToBin(null, [clipID]);
            return false;
        });
    },

    setExpandedFoldersIDs : function () {
        fs.backend_cb.expandedFolders = [];
        jQuery( '.clipbin-folder-list.expanded' ).each( function () {
            fs.backend_cb.expandedFolders.push( jQuery( this ).data( 'folder-id' ) );
        } );
    },

    getExpandedFoldersIDs : function () {
        return fs.backend_cb.expandedFolders;
    },

    refreshWidgetArea     : function ( html ) {
        if ( html ) {
            fs.backend_cb.setExpandedFoldersIDs();
            jQuery( '.clipbins-widget-holder' ).html( html );
            jQuery(fs.backend_cb.getExpandedFoldersIDs()).each(function(index, val){
                jQuery('.clipbin-folder-list[data-folder-id="' + val + '"]').removeClass('collapsed').addClass('expanded');
            });
        }
        if(fs.backend_cb.focusedElementSelector){
            jQuery(fs.backend_cb.focusedElementSelector).setSelection();
            fs.backend_cb.focusedElementSelector = null;
        }
        fs.backend_cb.enableClipbinsDroppable();
        fs.backend_cb.enableClipbinsDraggable();
        fs.backend_cb.enableFoldersDroppable();
        fs.backend_cb.enableNoFolderDroppable();
    },

    refreshDroppableArea : function ( droppable_area ) {
        if ( droppable_area.isFull ) {
            jQuery( '.footagesearch-clipbin-droppablearea' ).addClass( 'full' );
        } else {
            jQuery( '.footagesearch-clipbin-droppablearea' ).removeClass( 'full' );
        }
        $( '.footagesearch-clipbin-droppablearea' ).html( droppable_area.html );
    },

    enableClipbinsDroppable : function () {
        $( '.clipbin-well, .clipbin-item' ).droppable( {
            tolerance  : 'pointer',
            hoverClass : 'drop-active',
            accept     : '.draggable-clip, .small-item',
            drop       : function ( event, ui ) {
                debugErrors && console.log( 'Drop' );
                var clips = ui.helper.find( '.footagesearch-clip.selected' );
                if ( clips.length <= 0 ) {
                    clips = jQuery( ui.draggable );
                }
                if ( clips.length > 0 ) {
                    debugErrors && console.log( 'Selected: ' + clips.length );
                    var clipBinId = $( this ).attr( 'data-clipbin-id' );
                    if ( clipBinId ) {
                        var clipsIds = [];
                        clips.each( function () {
                            var clipId = $( this ).attr( 'data-clip-id' );
                            if ( clipId ) {
                                clipsIds.push( clipId );
                            }
                        } );
                        if ( clipsIds ) {
                            debugErrors && console.log( 'addClipsToClipbin( ' + clipBinId + ', ' + clipsIds + ' )' );
                            fs.backend_cb.addItemsToBin(clipBinId, clipsIds);
                            //addClipsToClipBin( clipBinId, clipsIds );
                            unselectSelectedClips();
                        }
                        //refreshClipBinView( clipBinId );
                    } else {
                        debugErrors && console.log( 'Drop error. Clipbin ID empty!' );
                    }
                } else {
                    debugErrors && console.log( 'Drop error. No selected clips!' );
                }
            },
            out        : function ( event, ui ) {
                var self = ui;
                ui.helper.off( 'mouseup' ).on( 'mouseup', function ( ) {
                    if ( !jQuery( '.clipbin-well' ).hasClass( 'drop-active' ) ) {
                        fs.backend_cb.removeItem( jQuery( this ).data( 'clip-id' ) );
                        jQuery( this ).remove();
                        self.draggable.remove();
                    }
                } );
            }
        } ).sortable( {
            items : ".small-item",
            sort  : function () {
                // gets added unintentionally by droppable interacting with sortable
                // using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
                jQuery( this ).removeClass( "ui-state-default" );
            }
        } );
    },

    enableClipbinsDraggable : function () {
        jQuery( '.clipbin-item' ).draggable( {
            appendTo : 'body',
            helper   : 'clone',
            cursorAt : {top : 0, left : 0}
        } );
    },

    enableFoldersDroppable : function () {
        jQuery( '.clipbins-widget-holder .clipbin-folder-item' ).droppable( {
            tolerance  : 'pointer',
            hoverClass : 'clipbin-state-active',
            accept     : '.clipbin-item',
            drop       : function ( event, ui ) {
                var folderID = jQuery(this).data('folder-id'),
                    binID = ui.draggable.data('clipbin-id'),
                    parentID = ui.draggable.parents('.clipbin-folder-item').data( 'folder-id'),
                    title = jQuery('.clipbin-title', ui.draggable).text();
                if ( binID && folderID && title && parentID != folderID ) {
                    ui.draggable.remove();
                    fs.backend_cb.saveClipBin( title, folderID, binID );
                }
            }
        } );
    },

    enableNoFolderDroppable : function () {
        jQuery( '.clipbins-widget-holder .no-folder' ).droppable( {
            tolerance   : 'pointer',
            hoverClass  : 'clipbin-state-hover',
            activeClass : 'clipbin-state-active',
            accept      : '.clipbin-item',
            drop        : function ( event, ui ) {
                var binID = ui.draggable.data('clipbin-id'),
                    title = jQuery('.clipbin-title', ui.draggable).text(),
                    selfParent = ui.draggable.parents('.no-folder');
                if ( binID && title && !selfParent.length ) {
                    ui.draggable.remove();
                    fs.backend_cb.saveClipBin( title, '', binID );
                }
            }
        } );
    },

    showSelectedClipbin : function () {
        jQuery( '.footagesearch-clipbin-folders-list .selected' ).parents( '.collapsed' ).toggleClass( 'expanded' );
    },

    addItemsToBin: function (binID, clipsIDs) {
        if (clipsIDs instanceof Array) {
            var container;
            jQuery.post(
                'en/backend_clipbins/index',
                {
                    'action': 'add_items',
                    'ids': clipsIDs,
                    'bin_id': binID
                },
                function (data) {
                    if (data.success) {
                        jQuery(clipsIDs).each(function (index, val) {
                            container = jQuery('#footagesearch-clip-' + val);
                            jQuery('.clipbin-add-item', container).hide();
                            jQuery('.clipbin-delete-item', container).removeClass().addClass('clipbin-delete-item ' + data.clipbin_type).show();
                            jQuery('.green-icon', container).removeClass().addClass('green-icon ' + data.clipbin_type).show();
                        });

                        if(data.clipbin_widget){
                            fs.backend_cb.refreshWidgetArea(data.clipbin_widget);
                        }
                    }
                },
                'json'
            );
        }
    },

    removeItem: function (clipID) {
        var container = jQuery('#footagesearch-clip-' + clipID);
        var binID = jQuery('#footagesearch-clip-'+clipID+' .clipbin-delete-item').data('bin-id');
        console.log(jQuery('.items-count').data('clipbin-id',binID));
        jQuery.each( jQuery('.items-count').data('clipbin-id',binID), function( e ) {
            console.log(e);
        });
        fs.backend_cb.clickPauseBtn(jQuery('.footagesearch-clip-pause-btn', container));
        jQuery.post(
            'en/backend_clipbins/index',
            {
                'action': 'delete_bin_item',
                'ids': [clipID],
                'bin_id': binID
            },
            function (data) {
                var request;
                if (data.success) {
                    if(fs.backend_cb.isBackendClipbinPage()){
                        container.remove();
                    }else{
                        jQuery('.clipbin-delete-item', container).hide();
                        jQuery('.clipbin-add-item', container).removeClass().addClass('clipbin-add-item ' + data.clipbin_type);
                        jQuery('.clipbin-add-item', container).show();
                        jQuery('.green-icon', container).hide();
                    }

                    jQuery('.clipbin-current .items-count').text('(' + data.items_count + ')');
                    jQuery('.clipbin-item.selected .items-count').text( '(' + data.items_count + ')');
                    jQuery('.clipbin-well .small-item[data-clip-id=' + clipID + ']').remove();

                    /*if (data.add_button) {
                     link.replaceWith(data.add_button);
                     jQuery('.bin-green', container).hide();
                     }
                     if (data.delete_item) {
                     jQuery('.footagesearch-clipbin-item-' + data.delete_item).remove();
                     }
                     if ((data.items_count || data.items_count == 0))
                     jQuery('.footagesearch-clipbin-count-' + data.clipbin_id).html('(' + data.items_count + ')');
                     if (data.droppable_area) {
                     fs.backend_cb.refreshDroppableArea(data.droppable_area);
                     }
                     if (link.data('delete')) {
                     jQuery('#footagesearch-clip-preview-dialog').dialog('close');
                     container.remove();
                     }
                     if (jQuery('.footagesearch-clip').length <= 0) {
                     window.location.href = window.location.href;
                     }*/
                }
            },
            'json'
        );

    },
    clickPauseBtn: function($element){
        var isOpen = $('#footagesearch-clip-preview-dialog').dialog('isOpen');
        if(isOpen)
            $('#footagesearch-clip-preview-dialog').dialog('close');

        $element.hide();
        var playBtn = $element.parent().find('.footagesearch-clip-play-btn');
        playBtn.show();
    },
    saveClipBin : function ( title, folderID, binID ) {
        jQuery.post( 'en/backend_clipbins/index', {
                action    : "save_clipbin",
                title     : title,
                folder_id : folderID,
                bin_id    : binID
            }, function ( data ) {
                if ( data.success ) {
                    fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                } else
                    fs.backend_cb.showClipbinMessage( 'Error', true );
            }, 'json' );
    },

    showClipbinMessage : function ( message, isError ) {
        var messageContainer = jQuery( '.footagesearch_clipbin_message' );
        if ( messageContainer.length > 0 ) {
            if ( isError ) {
                messageContainer.addClass( 'error' );
                messageContainer.removeClass( 'success' );
            } else {
                messageContainer.addClass( 'success' );
                messageContainer.removeClass( 'error' );
            }

            messageContainer.html( message );
            messageContainer.slideDown();
            setTimeout( function () {
                fs.backend_cb.hideClipbinMessage();
            }, 5000 );
        }
    },

    hideClipbinMessage : function () {
        var messageContainer = jQuery( '.footagesearch_clipbin_message' );
        if ( messageContainer.length > 0 ) {
            messageContainer.slideUp();
        }
    },

    logInAlert : function () {
        jQuery( "<div>Please Register / Sign In to save the contents of your Clipbin/Cart.<div>" ).dialog( {
            resizable : false,
            modal     : true,
            buttons   : [
                { text   : "", click : function () {
                    window.location.href = '/login';
                }, class : "btn-primary"},
                { text   : "", click : function () {
                    jQuery( this ).dialog( "close" );
                }, class : 'btn'}
            ],
            create    : function () {
                jQuery( '.ui-dialog-titlebar', jQuery( this ).parents( '.ui-dialog' ) ).hide();
                jQuery( '.ui-dialog-titlebar .ui-dialog-titlebar-close', jQuery( this ).parents( '.ui-dialog' ) ).css( {'border' : 'none'} );
            }
        } );
    },

    getSelectedClips : function () {
        var selected = jQuery( '.footagesearch-clip.selected' );
        var selectedIDs = [];
        if ( selected.length > 0 ) {
            selected.each( function ( index ) {
                var idArr = jQuery( this ).attr( 'id' ).split( '-' );
                if ( idArr[2] !== undefined )
                    selectedIDs.push( idArr[2] );
            } );
        }
        return selectedIDs;
    },

    moveItems : function ( binID ) {
        var selectedIDs = fs.backend_cb.getSelectedClips();
        if ( binID && selectedIDs ) {
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : "move_items",
                    items_ids      : selectedIDs,
                    bin_id         : binID
                }, function ( data ) {
                    if ( data.success && data.clipbin_content ) {
                        jQuery( '.footagesearch-clipbin-content' ).replaceWith( data.clipbin_content );
                    }
                } );
        }
    },

    copyItems : function ( binID ) {
        var selectedIDs = fs.backend_cb.getSelectedClips();
        if ( binID && selectedIDs ) {
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : "copy_items",
                    items_ids      : selectedIDs,
                    bin_id         : binID
                }, function ( data ) {
                    if ( data.success ) {
                        alert( 'Copied' );
                    }
                } );
        }
    },

    removeItems : function ( binID ) {
        var selectedIDs = fs.backend_cb.getSelectedClips();
        if ( binID && selectedIDs ) {
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : "remove_items",
                    items_ids      : selectedIDs,
                    bin_id         : binID
                }, function ( data ) {
                    if ( data.success && data.clipbin_content ) {
                        jQuery( '.footagesearch-clipbin-content' ).replaceWith( data.clipbin_content );
                    }
                    if ( data.droppable_area ) {
                        fs.backend_cb.refreshDroppableArea( data.droppable_area );
                    }
                } );
        }
    },

    viewBin : function ( binID ) {
        var clipBinUrl = jQuery( 'input[name="footagesearch_clipbin_link"]' ).val();
        if ( !clipBinUrl )
            clipBinUrl = '/clipbin/'
        clipBinUrl += '?bin=' + binID;
        window.location.href = clipBinUrl;
    },

    saveClipBinFolder : function ( name, folderID ) {
        if ( name ) {
            jQuery.post( 'en/backend_clipbins/index', {
                    action    : "save_folder",
                    name      : name,
                    folder_id : folderID
                }, function ( data ) {
                    if ( data.success ) {
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    } else
                        fs.backend_cb.showClipbinMessage( 'Error', true );
                }, 'json' );
        }
    },

    renameClipBin : function () {
        var title = jQuery( '.footagesearch-clipbin-new-title' ).val();
        if ( title ) {
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : "rename_clipbin",
                    title          : title
                }, function ( data ) {
                    if ( data.success ) {
                        jQuery( '.footagesearch_clipbin_current_bin' ).html( title );
                    }
                } );
        }
    },

    copyClipBin : function () {
        var title = jQuery( '.footagesearch-clipbin-copy-title' ).val();
        if ( title ) {
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : "copy_clipbin",
                    title          : title
                }, function ( data ) {
                    if ( data.success && data.bins_select ) {
                        alert( 'Copied' );
                        jQuery( '.footagesearch-clipbin-id' ).replaceWith( data.bins_select )
                    }
                } );
        }
    },

    filterBinsList: function(keyword) {
        fs.backend_cb.setExpandedFoldersIDs();
        jQuery.post('/index.php?ajax=true', {
                clipbin_action: "clipbins_list",
                keyword: keyword
            },
            function (data) {
                if (data.success && data.bins_list) {
                    jQuery('.footagesearch-clipbin-folders-cont').html(data.bins_list);
                    jQuery(fs.backend_cb.getExpandedFoldersIDs()).each(function (index, value) {
                        jQuery('#' + value).addClass('expanded');
                    });
                    jQuery('.footagesearch-clipbin-filter-form').on('submit', function (e) {
                        e.preventDefault();
                        var keyword = jQuery(this).find('input.text').val();
                        if (keyword == 'Filter Clipbin list')
                            keyword = '';
                        fs.backend_cb.filterBinsList(keyword);
                        return false;
                    });
                }
            }
        );
    },

    getClipbin : function ( binID, handleData ) {
        jQuery.post( 'en/backend_clipbins/index', {
                action : 'get_clipbin',
                bin_id : binID
            }, function ( data ) {
                if ( data.success && data.clipbin ) {
                    handleData( data.clipbin );
                }
            }, 'json' );
    },

    getFolder : function ( folderID, handleData ) {
        jQuery.post( 'en/backend_clipbins/index', {
                action    : 'get_folder',
                folder_id : folderID
            }, function ( data ) {
                if ( data.success && data.folder ) {
                    handleData( data.folder );
                }
            }, 'json' );
    },

    saveWidgetStatus : function ( status ) {
        if ( status )
            jQuery.post( '/index.php?ajax=true', {
                    clipbin_action : 'save_widget_status',
                    status         : status
                } );
    },

    editClipbin : function ( binID ) {
        fs.backend_cb.getClipbin( binID, function ( clipbin ) {
            if ( clipbin ) {
                var holder = jQuery( '.clipbins-widget-holder' );
                var form = jQuery( '.footagesearch-clipbin-create-clipbin-form', holder );
                var formCont = form.parents( '.edit-action-clipbin' );
                form.find( '[name="clipbin_title"]' ).val( clipbin.title );
                form.find( '[name="clipbin_folder_id"]' ).val( clipbin.backend_folder_id );
                form.find( '[name="clipbin_id"]' ).val( clipbin.id );
                form.find( '[name="create_clipbin"]' ).val( 'Save' );
                if ( formCont.css( 'display' ) == 'none' ) {
                    jQuery( '.footagesearch-clipbin-create-folder-form-cont' ).hide();
                    formCont.slideDown();
                }
            }
        } )
    },

    deleteClipbin : function ( binID ) {
        if ( binID ) {
            jQuery( '<div>All clips in the current clipbin will be deleted. Continue?</div>' ).dialog( {
                resizable : false,
                modal     : true,
                buttons   : [
                    {
                        text  : "Continue", click : function () {
                        jQuery( this ).dialog( "close" );
                        jQuery.post( 'en/backend_clipbins/index', {
                                action : "delete_clipbin",
                                bin_id : binID
                            }, function ( data ) {
                                if ( data.success ) {
                                    fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                                } else
                                    fs.backend_cb.showClipbinMessage( 'Error', true );
                            }, 'json' );
                    },
                        class : 'btn'
                    },
                    {text    : "Cancel", click : function () {
                        jQuery( this ).dialog( "close" );
                    }, class : "btn-primary"}
                ],
                create    : function () {
                    jQuery( '.ui-dialog-titlebar', jQuery( this ).parents( '.ui-dialog' ) ).hide();
                    jQuery( '.ui-dialog-titlebar .ui-dialog-titlebar-close', jQuery( this ).parents( '.ui-dialog' ) ).css( {'border' : 'none'} );
                }
            } );
        }
    },

    emailClipbin : function ( binID ) {
        var link = window.location.host + '/clipbin?action=view&bin=' + binID;
        jQuery( '' +
            '<div style="width:400px;">' +
            '<span>To:</span><input type="text" class="to" style="width: 335px;" value="">' +
            '<br>' +
            '<textarea  class="body" style="width: 354px; resize: none; overflow-y: auto; height:300px;">' + link + '</textarea>' +
            '</div>' +
            '' ).dialog( {
            width       : '400',
            dialogClass : 'email-dialog',
            modal       : true,
            draggable   : true,
            buttons     : [
                { text   : "Cancel", click : function () {
                    jQuery( this ).dialog( "close" );
                }, class : "btn" },
                {
                    text  : "Send",
                    click : function () {
                        var $dialog = jQuery( this ), data = {
                                'to'   : jQuery( 'input.to', jQuery( this ) ).val(),
                                'body' : jQuery( 'textarea.body', jQuery( this ) ).val()
                            };
                        jQuery.post( 'clipbin?clipbin_action=email_clipbin_ajax&ajax=true', data, function ( data ) {
                            if ( data.errors.length > 0 ) {
                                alert( data.errors.join( '</br>' ) );
                            } else {
                                if ( data.message ) {
                                    alert( data.message );
                                }
                                $dialog.dialog( "close" );
                            }
                        }, 'json' ).fail( function () {
                                alert( 'error' );
                            } );
                    },
                    class : "btn-primary" }
            ],
            create      : function ( event, ui ) {
                jQuery( '.ui-dialog-titlebar' ).hide();
            }
        } );
    },

    setDefaultClipbin : function ( binID ) {
        if ( binID ){
            //window.location.href = '/en/cliplog/view/?backend_clipbin=' + binID;
            jQuery.post(
                '/en/backend_clipbins',
                {
                    action : 'set_default_bin',
                    bin_id : binID
                },
                function ( data ) {
                    if ( data.success ) {
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    }
                },
                'json'
            );
        }
    },

    make_featured_gallery : function ( binID ) {
        if ( binID ){
            //window.location.href = '/en/cliplog/view/?backend_clipbin=' + binID;
            jQuery.post(
                '/en/backend_clipbins',
                {
                    action : 'make_featured_gallery',
                    bin_id : binID
                },
                function ( data ) {
                    location.reload();
                    if ( data.success ) {
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    }
                },
                'json'
            );
        }
    },

    make_ordinary_gallery : function ( binID ) {
        if ( binID ){
            //window.location.href = '/en/cliplog/view/?backend_clipbin=' + binID;
            jQuery.post(
                '/en/backend_clipbins',
                {
                    action : 'make_ordinary_gallery',
                    bin_id : binID
                },
                function ( data ) {
                    location.reload();
                    if ( data.success ) {
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    }
                },
                'json'
            );
        }
    },

    make_clipbin : function ( binID ) {
        if ( binID ){
            //window.location.href = '/en/cliplog/view/?backend_clipbin=' + binID;
            jQuery.post(
                '/en/backend_clipbins',
                {
                    action : 'make_clipbin',
                    bin_id : binID
                },
                function ( data ) {
                    location.reload();
                    if ( data.success ) {
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    }
                },
                'json'
            );
        }
    },

    editFolder : function ( folderID ) {
        fs.backend_cb.getFolder( folderID, function ( folder ) {
            if ( folder ) {
                var holder = jQuery( '.clipbins-widget-holder' ), form = jQuery( '.footagesearch-clipbin-create-folder-form', holder ), formCont = form.parents( '.edit-action-folder' );
                form.find( '[name="folder_name"]' ).val( folder.name );
                form.find( '[name="folder_id"]' ).val( folder.id );
                form.find( '[name="create_folder"]' ).val( 'Save' );

                if ( formCont.css( 'display' ) == 'none' ) {
                    jQuery( '.footagesearch-clipbin-create-clipbin-form-cont' ).hide();
                    formCont.slideDown();
                }
            }
        } )
    },

    deleteFolder : function ( folderID ) {
        if ( folderID ) {
            jQuery( '<div>All clips and clipbins in the current folder will be deleted. Continue?</div>' ).dialog( {
                resizable : false,
                modal     : true,
                buttons   : [
                    {
                        text  : "Continue",
                        click : function () {
                            jQuery( this ).dialog( "close" );
                            jQuery.post( 'en/backend_clipbins/index', {
                                    action    : "delete_folder",
                                    folder_id : folderID
                                }, function ( data ) {
                                    if ( data.success ) {
                                        jQuery( '.clipbins-widget-holder' ).html( data.clipbin_widget );
                                    } else
                                        fs.backend_cb.showClipbinMessage( 'Error', true );
                                }, 'json' );
                        },
                        class : 'btn'
                    },
                    { text   : "Cancel", click : function () {
                        jQuery( this ).dialog( "close" );
                    }, class : "btn-primary"}
                ],
                create    : function () {
                    jQuery( '.ui-dialog-titlebar', jQuery( this ).parents( '.ui-dialog' ) ).hide();
                    jQuery( '.ui-dialog-titlebar .ui-dialog-titlebar-close', jQuery( this ).parents( '.ui-dialog' ) ).css( {'border' : 'none'} );
                }
            } );
        }
    },

    setAfterAddButtons : function ( data ) {
        var container = [];
        jQuery.each( data.delete_buttons, function ( key, value ) {
            container = jQuery( '#footagesearch-clip-' + key );
            jQuery( '.bin-green', container ).show();
            jQuery( '.footagesearch_clipbin_button_form', container ).replaceWith( value );
        } )
    },

    makeGallery : function ( binID ) {
        jQuery.post( 'en/backend_clipbins/index', {
                action : "make_gallery",
                bin_id : binID
            }, function ( data ) {
                location.reload();
                if ( data.success ) {
                    fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                } else
                    fs.backend_cb.showClipbinMessage( 'Error', true );
            }, 'json' );
    },

    makeSequence : function ( binID ) {
        jQuery.post( 'en/backend_clipbins/index', {
                action : "make_sequence",
                bin_id : binID
            }, function ( data ) {
                location.reload();
                if ( data.success ) {
                    fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                } else{
                    fs.backend_cb.showClipbinMessage( 'Error', true );
                }
            }, 'json' );
    },

    isBackendClipbinPage: function(){
        if(window.location.href.indexOf('?backend_clipbin') > -1 || window.location.href.indexOf('&backend_clipbin') > -1){
            return true;
        }
        return false;
    },

    filterClipbins: function(filter, elem){
        var data = {
                'action': 'filter_clipbins',
                'filter': filter
            },
            current_filter;
        fs.backend_cb.focusedElementSelector = '.clipbins-widget-holder .clipbis-filter-words';
        $.post(
            '/en/backend_clipbins/',
            data,
            function(data){
                if ( data.success ) {
                    current_filter = jQuery(elem).val();
                    if(current_filter == filter){
                        fs.backend_cb.refreshWidgetArea( data.clipbin_widget );
                    }
                } else {
                    fs.backend_cb.showClipbinMessage( 'Error', true );
                }
            },
            'json'
        );
    }
};

//deselect text on focus
(function() {
    var fieldSelection = {
        setSelection: function() {
            var e = (this.jquery) ? this[0] : this, len = this.val().length || 0;
            var args = arguments[0] || {"start":len, "end":len};
            /* mozilla / dom 3.0 */
            if ('selectionStart' in e) {
                if (args.start != undefined) {
                    e.selectionStart = args.start;
                }
                if (args.end != undefined) {
                    e.selectionEnd = args.end;
                }
                e.focus();
            }
            /* exploder */
            else if (document.selection) {
                e.focus();
                var range = document.selection.createRange();
                if (args.start != undefined) {
                    range.moveStart('character', args.start);
                    range.collapse();
                }
                if (args.end != undefined) {
                    range.moveEnd('character', args.end);
                }
                range.select();
            }
            return this;
        }
    };
    jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });
})();