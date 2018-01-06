var gallery = {
    init: function () {
        var that = this;
        that.settings.debugNowFunc = 'init';
        that.debug('----------------------Debug gallery------------------------');
        that.clickFeaturedPulldown();
        that.enableDroppFeaturedThum();
    },
    settings: {
        debugMode: false,
        debugNowFunc: '',
        pulldown: '.featured-pulldown',
        pull: '.featured-pulldown .featured-pull',
        featuredThumbMinImg: '.featured-pulldown .featured-thumb-min img',
        featuredThumbMaxImg: '.featured-pulldown .featured-thumb-max'
    },
    clickFeaturedPulldown: function () {
        var that = this;
        $(that.settings.pull).live('click', function () {
            that.settings.debugNowFunc = 'clickFeaturedPulldown -> click';
            that.debug();
            $(this).parent().toggleClass("expanded");
        });
    },
    enableDroppFeaturedThum: function () {
        var that = this;

        $('.featured-pulldown .droppablearea').droppable({
            tolerance: 'pointer',
            hoverClass: 'active',
            accept: '.draggable-clip',
            drop: function (event, ui) {
                that.settings.debugNowFunc = 'enableDroppFeaturedThum -> drop';
                /*that.debug($(that.settings.pulldown).data('clipbin-id'));
                 that.debug($(ui.draggable ).find('.footagesearch-clip-thumb img' ).attr('src'));*/
                var imgSrc = $(ui.draggable).find('.footagesearch-clip-thumb img').attr('src');
                /*$(that.settings.featuredThumbMinImg).attr('src',imgSrc);
                 $(that.settings.featuredThumbMaxImg).attr('src',imgSrc);*/
                $(that.settings.featuredThumbMaxImg).html('<img src="' + imgSrc + '" />');
                that.backendClipbinsActionAjax($(that.settings.pulldown).data('clipbinId'), 'add_thumb_gallery', {clip_thumb: imgSrc});
            }
        });

    },
    backendClipbinsActionAjax: function (binID, action, post_data) {
        var that = this;
        $.post(
            'en/backend_clipbins/index',
            {
                'action': action,
                'post_data': post_data,
                'bin_id': binID
            },
            function (data) {
                if (data.success) {
                    that.debug(data);

                    /*if(data.clipbin_widget){
                     fs.backend_cb.refreshWidgetArea(data.clipbin_widget);
                     }*/
                }
            },
            'json'
        );
    },
    debug: function (msg) {
        if (this.settings.debugMode) {
            console.log('Gallery Action:' + this.settings.debugNowFunc);
            if (msg != undefined) {
                if ($.type(msg) == 'object' || $.type(msg) == 'array') {
                    console.log(' -   debugMsg:');
                    console.log(msg);

                } else {
                    console.log(' -   debugMsg:' + msg);
                }
            }
        }
    },
    dump: function (obj, out) {
        var out = "";
        if (obj && typeof(obj) == "object") {
            for (var i in obj) {
                out += i + ": " + obj[i] + "\n";
            }
        } else {
            out = obj;
        }
        if (out == 'alert') {
            alert(out);
        } else {
            console.log(out);
        }
    }
};
var cliplogWidget = {
    initSize: function () {
        var that = this;
        that.resizeWidget();
        $(window).resize(function () {
            that.resizeWidget();
        });//alert(top +'<>'+ $(this).scrollTop()) ;
        $(document).scroll(function () {
            //that.position.scroll();
        });
    },
    resizeWidget: function () {
        if (window != top) {//iframe
            $('div.cliplog-filter-cont').css('height', $(window).height() - 150 + 'px');
        } else {//window
            $('div.cliplog-filter-cont').css('height', $(window).height() - 270 + 'px');
        }


    },
    position: {
        settings: {
            offset: 127,
        },
        scroll: function () {
            var scroll_top = $(window).scrollTop(); // current vertical position from the top
            alert(scroll_top);
            if (scroll_top > position.settings.offset) {
                $('nav').css({'position': 'fixed', 'top': '0'});
            } else {
                $('nav').css({'position': 'absolute', 'top': position.settings.offset + 'px'});
            }
        }
    }
};
$(document).ready(function () {
    gallery.init();
    cliplogWidget.initSize();
    $('body#admin').css('overflow-x', 'hidden'); // fix scroll widget in frontend
    $('body#admin').css('overflow-y', 'auto'); // fix scroll widget in frontend
});
/* ===================== Bins ===================== */
(function ($) {
    // Bins functions
    function addClipsToBin(binID, clipsIDs) {
        if (binID && clipsIDs) {
            $.post('/en/bins/items/' + binID, {
                    items_ids: clipsIDs
                }
            );
        }
    }

    function enableBinsDroppable() {
        // Перемещено в cliplog.clipbin.js
    }

    function getBinsList() {
        $.post(
            '/en/bins/view',
            {},
            function (data) {
                if (data.success && data.bins_list) {
                    $('.cliplog-bins-list').replaceWith(data.bins_list);
                    enableBinsDroppable();
                }
            }
            ,
            'json'
        );
    }

    function saveBin(binData, success_callback, error_callback, binID) {
        var url = '/en/bins/edit';
        if (binID)
            url += '/' + binID
        $.post(
            url,
            binData,
            function (data) {
                if (data.success) {
                    success_callback();
                    getBinsList();
                }
                else {
                    error_callback(data.error);
                }
            },
            'json'
        );
    }

    function getBin(binID, handleData) {
        $.post(
            '/en/bins/get_bin',
            {bin_id: binID},
            function (data) {
                if (data.success && data.bin) {
                    handleData(data.bin);
                }
            },
            'json'
        );
    }

    function deleteBin(binID) {
        if (binID)
            $.post(
                '/en/bins/delete/' + binID,
                {},
                function (data) {
                    if (data.success) {
                        window.location.href = '/en/cliplog/view';
                    }
                },
                'json'
            );
    }

    function showSelectedItems() {
        $('.cliplog-tree ul li.selected').parents('li').addClass('expanded');
    }

    //Clip selection
    function toggleSelection(clipID) {
        var clip = $('#footagesearch-clip-' + clipID);
        if (clip.length > 0) {
            var clipInput = clip.find('.footagesearch-clip-input').first();
            var clipCheckbox = clip.find('input[name="id[]"]').first();
            clip.toggleClass('selected');
            if (clipInput.val() == 1) {
                clipInput.val(0);
                clipCheckbox.attr('checked', false);
                //clip.draggable('disable');
            }
            else {
                clipInput.val(1);
                clipCheckbox.attr('checked', true);
                clip.draggable('enable');
            }
        }
    }

    function selectAllClips() {
        var clipInputs = $('.footagesearch-clip-input');
        var clipCheckboxes = $('input[name="id[]"]');
        var clips = $('.footagesearch-clip');
        clipInputs.val(1);
        clipCheckboxes.attr('checked', true);
        clipInputs.parents('.footagesearch-clip').addClass('selected');
        //clips.draggable('enable');
    }

    function deSelectAllClips() {
        var clipInputs = $('.footagesearch-clip-input');
        var clipCheckboxes = $('input[name="id[]"]');
        var clips = $('.footagesearch-clip');
        clipInputs.val(0);
        clipCheckboxes.attr('checked', false);
        clipInputs.parents('.footagesearch-clip').removeClass('selected');
        //clips.draggable('disable');
    }

    $(function () {

        // Bins plugins
        $("#bin-form").dialog({
            autoOpen: false,
            width: 300,
            modal: true,
            buttons: {
                "Save": function () {

                    var data = $(this).find('form').serialize() + '&save=1';
                    saveBin(
                        data,
                        function () {
                            $('#bin-form').dialog("close");
                        },
                        function (error) {
                            var alert = $('#bin-form').find('.alert');
                            alert.text(error).addClass('alert-error').show();
                            setTimeout(function () {
                                alert.hide();
                            }, 3000);
                        },
                        $(this).find('[name="id"]').val()
                    )

                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                $(this).find('input').val('');
            }
        });

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                vars[key] = value;
            });
            return vars;
        }


        var clipbin = {
            init: function () {
                var that = this
                //delete item from current bin popup
                $('.footagesearch-clip-cart-clipbin-actions').on('click', '.clipbin-delete-item', function (event) {
                    event.preventDefault();
                    that.ajaxAddRemoveItem(this, 'delete_bin_item', that);
                    return false;
                });

                //add item to current bin popup
                $('.footagesearch-clip-cart-clipbin-actions').on('click', '.clipbin-add-item', function (event) {
                    event.preventDefault();
                    that.ajaxAddRemoveItem(this, 'add_items', that);
                    return false;
                });
            },
            ajaxAddRemoveItem: function (e, action, that) {
                var clipID = $(e).data('clip-id');
                var binID = $(e).data('bin-id');
                $(e).hide();
                if (action == 'add_items') {
                    $(e).prev().show();
                } else {
                    $(e).next().show();
                }
                /*console.log(jQuery('.cliplog-filter-cont .items-count["data-clipbin-id='+binID+'"]').data('clipbin-id'));
                 jQuery.each( jQuery('.cliplog-filter-cont .items-count["data-clipbin-id='+binID+'"]'), function( e ) {
                 if(this.data('clipbin-id') == binID){
                 console.log(this.data('clipbin-id'));
                 }
                 });*/
                //console.log(jQuery('.cliplog-filter-cont .items-count["data-clipbin-id='+binID+'"]').data('clipbin-id'));
                $.post(
                    'en/backend_clipbins/index',
                    {
                        'action': action,
                        'ids': [clipID],
                        'bin_id': binID
                    },
                    function (data) {
                        if (data.success) {
                            if (getUrlVars()['modal']) {
                                //window.location.reload();

                                window.opener.location.reload();
                            } else {
                                if (data.items_count) { //remove
                                    that.removeOfCurClipbin(data, clipID, that);
                                } else { //add
                                    that.addToCurClipbin(data, clipID, that);
                                }
                                window.location.reload();
                            }
                        }
                    },
                    'json'
                );
                //window.location.reload();
            },
            addToCurClipbin: function (data, clipID, that) {
                $('.clipbins-widget-holder').html(data.clipbin_widget); // rebuild widget
                var clip = 'div[id^="footagesearch-clip-' + clipID + '"]';
                $(clip + ' .green-icon').toggleClass(data.clipbin_type); // add icon clipbin
                $(clip + ' .green-icon').css('display', 'inline-block');

                $(clip + ' .clipbin-add-item').css('display', 'none');
                $(clip + ' .clipbin-delete-item').css('display', 'inline-block');
            },
            removeOfCurClipbin: function (data, clipID, that) {
                var clip = 'div[id^="footagesearch-clip-' + clipID + '"]';

                $(clip + ' .green-icon').toggleClass(data.clipbin_type); // add icon clipbin
                $(clip + ' .green-icon').css('display', 'none');

                $(clip + ' .clipbin-add-item').css('display', 'inline-block');
                $(clip + ' .clipbin-delete-item').css('display', 'none');

                //$('.cliplog-filter-cont .items-count' ).html('('+data.items_count+')');
                jQuery('.clipbin-current .items-count').text('(' + data.items_count + ')');
                jQuery('.clipbin-item.selected .items-count').text('(' + data.items_count + ')');
                $('.cliplog-filter-cont .small-item[data-clip-id="' + clipID + '"]').remove();
            }
        };
        clipbin.init();

        $('.draggable-clip').draggable({
            revert: 'invalid',
            //helper: 'clone',
            cursor: 'move',
            cursorAt: {bottom: -10, left: -10},
            start: function (event, ui) {
                ui.originalPosition.top -= $('body').scrollTop();
            },
            drag: function (event, ui) {

            },
            helper: function () {
                var selected = $('.draggable-clip.selected');
                if (selected.length === 0) {
                    selected = $(this);
                }
                var container = $('<div/>').attr('id', 'draggingContainer');
                container.append(selected.clone());
                return container;
            }
        });

        $('.draggable-clip').draggable('disable');
        $('.draggable-clip.selected').draggable('enable');


        // Bins events handlers
        $('.cliplog-tree-section').on('click', function () {
            var parentLi = $(this).parent();
            parentLi.toggleClass('expanded');
        });

        $(".add-bin-btn").click(function () {
            $("#bin-form").dialog("open");
        });

        $(".edit-bin-btn").click(function () {
            var binIDArr = $(this).attr('id').split('-');
            var binID = binIDArr[2] !== undefined ? binIDArr[2] : 0;
            getBin(binID, function (bin) {
                if (bin) {
                    var form = $('#bin-form');
                    form.find('[name="title"]').val(bin.title);
                    form.find('[name="id"]').val(bin.id);
                    form.find('[name="code"]').val(bin.code);
                    form.attr('title', 'Edit bin');
                    form.dialog("open");
                }
            });
        });

        $(".delete-bin-btn").click(function () {
            var binIDArr = $(this).attr('id').split('-');
            var binID = binIDArr[2] !== undefined ? binIDArr[2] : 0;
            deleteBin(binID);
        });

        /*$('.clipbins-widget-holder').on('submit', '.clipbins-list-filter', function(e){
         var words = $(this).find('input[name=words]').val();
         e.preventDefault();
         fs.backend_cb.filterClipbins(words);
         });*/

        $('.clipbins-widget-holder').on('keyup', '.clipbins-list-filter .clipbis-filter-words', function (e) {
            var words = $(this).val(),
                that = this;
            e.preventDefault();
            fs.delay(function () {
                fs.backend_cb.filterClipbins(words, that)
            }, 500);
        });

        // Bins onload functions
        enableBinsDroppable();
        showSelectedItems();

        $('.transitiable').on('click', function () {
            var $clip_block = $(this).parent().parent();
            //TODO fix that
            if ($clip_block.attr('id')) {
                var idArr = $clip_block.attr('id').split('-');
                var clipID = idArr[2];
            }
            if (clipID) {
                toggleSelection(clipID);
            }
        });

        // Cliplog actions
        $('.cliplog-select-all-btn').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectAllClips();
        });

        $('.cliplog-sortby-select').change(function (e) {
            change_action(document.clips, 'en/cliplog/view');
        });

        $('.cliplog-onpage-select').change(function (e) {
            var href = $('.cliplog-onpage-select').attr('path-redirect');
            change_action(document.clips, href);
        });

        $('.cliplog-actions-select').change(function (e) {
            var action = $(this).val();
            if (action) {
                switch (action) {
                    case 'log':
                        if (check_selected(document.clips, 'id[]')) {
                            var countCheck = check_selected_count(document.clips, 'id[]');
                            var selected = $('.footagesearch-clip.selected').attr('data-clip-id');
                            if (countCheck <= 1) {
                                window.location.href = '/en/cliplog/edit/' + selected;
                            } else {
                                change_action(document.clips, 'en/cliplog/edit');
                            }
                        }
                        else
                            $(this).val('');
                        break;
                    case 'log_selected':
                        change_action(document.clips, 'en/cliplog/edit');
                        break;
                    case 'move_offline':
                        if (check_selected(document.clips, 'id[]')) {
                            change_action(document.clips, 'en/clips/status/0');
                        } else
                            $(this).val('');
                        break;
                    case 'move_online':
                        if (check_selected(document.clips, 'id[]')) {
                            change_action(document.clips, 'en/clips/status/1');
                        } else
                            $(this).val('');
                        break;
                    case 'move_archive':
                        if (check_selected(document.clips, 'id[]')) {
                            change_action(document.clips, 'en/clips/status/2');
                        } else
                            $(this).val('');
                        break;
                    case 'unarchive':
                        if (check_selected(document.clips, 'id[]')) {
                            change_action(document.clips, 'en/clips/status/3');
                        } else
                            $(this).val('');
                        break;
                    case 'delete':
                        if (check_selected(document.clips, 'id[]')) {
                            if (!change_action(document.clips, 'en/clips/delete'))
                                $(this).val('');
                        }
                        else
                            $(this).val('');
                        break;
                    case 'to_clipbin':
                        if (check_selected(document.clips, 'id[]')) {
                            $('.footagesearch-clip.selected').find('.clipbin-add-item').each(function () {
                                $(this).click();
                            });
                            deSelectAllClips();
                        }
                        $(this).val('');
                        break;
                    case createThumbs.actionOptionName:
                        //createThumbs.start();
                        //$(this).val('');
                        if (check_selected(document.clips, 'id[]')) {
                            var countCheck = check_selected_count(document.clips, 'id[]');
                            var selected = $('.footagesearch-clip.selected').attr('data-clip-id');
                            if (countCheck <= 1) {
                                window.location.href = '/en/cliplog/index/thumbgallery/' + selected;
                            }else {
                                alert('Please select one clip to cahnge Thumbnail');
                                $(this).val('');
                            }
                        } else
                            $(this).val('');
                        break;
                }
            }
        });

        $('.cliplog-deselect-all-btn').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            deSelectAllClips();
        });

        //View changing
        $('.footagesearch-clips-toggle-list-view').on('click', function () {
            var listViewForm = $('.footagesearch-list-view-form').first();
            var listViewInput = listViewForm.find('input').first();
            listViewInput.val('list');
            listViewForm.submit();
        });
        $('.footagesearch-clips-toggle-grid-view').on('click', function () {
            var listViewForm = $('.footagesearch-list-view-form').first();
            var listViewInput = listViewForm.find('input').first();
            listViewInput.val('grid');
            listViewForm.submit();
        });

    })
})(jQuery);

/* ===================== Galleries ===================== */
(function ($) {


    // Galleries functions
    function addClipsToGallery(galleryID, clipsIDs) {
        if (galleryID && clipsIDs) {
            $.post(
                '/en/galleries/items/' + galleryID,
                {items_ids: clipsIDs},
                function (data) {
                    if (data.success) {
                        var alert = $('#cliplog-alert-dialog');
                        alert.html('<div class="alert alert-success">' + clipsIDs.length + ' clips added to Gallery</div>');
                        alert.dialog();
                    }
                },
                'json'
            );
        }
    }

    function enableGalleriesDroppable() {
        $('.cliplog-galleries-list li').droppable({
            tolerance: 'pointer',
            hoverClass: 'active',
            accept: '.draggable-clip',
            drop: function (event, ui) {
                var selected = ui.helper.find('.footagesearch-clip.selected');
                var selectedIDs = [];
                var galleryIDArr = $(this).attr('id').split('-');
                var galleryID = galleryIDArr[2] !== undefined ? galleryIDArr[2] : 0;
                if (selected.length > 0) {
                    selected.each(function (index) {
                        var idArr = $(this).attr('id').split('-');
                        if (idArr[2] !== undefined)
                            selectedIDs.push(idArr[2]);
                    });
                }
                if (galleryID && selectedIDs.length > 0) {
                    addClipsToGallery(galleryID, selectedIDs);
                }
            }
        });
    }

    function getGalleriesList() {
        $.post(
            '/en/galleries/view',
            {},
            function (data) {
                if (data.success && data.galleries_list) {
                    $('.cliplog-galleries-list').replaceWith(data.galleries_list);
                    enableGalleriesDroppable();
                }
            }
            ,
            'json'
        );
    }

    function saveGallery(galleryData, success_callback, error_callback, galleryID) {
        var url = '/en/galleries/edit';
        if (galleryID)
            url += '/' + galleryID
        $.post(
            url,
            galleryData,
            function (data) {
                if (data.success) {
                    success_callback();
                    getGalleriesList();
                }
                else {
                    error_callback(data.error);
                }
            },
            'json'
        );
    }

    function getGallery(galleryID, handleData) {
        $.post(
            '/en/galleries/get_gallery',
            {gallery_id: galleryID},
            function (data) {
                if (data.success && data.gallery) {
                    handleData(data.gallery);
                }
            },
            'json'
        );
    }

    function deleteGallery(galleryID) {
        if (galleryID)
            $.post(
                '/en/galleries/delete/' + galleryID,
                {},
                function (data) {
                    if (data.success) {
                        window.location.href = '/en/cliplog/view';
                    }
                },
                'json'
            );
    }

    $(function () {

        // Galleries plugins
        $("#gallery-form").dialog({
            autoOpen: false,
            width: 300,
            modal: true,
            buttons: {
                "Save": function () {
                    var form = $(this).find('form');
                    var data = form.serialize() + '&save=1';
                    if (form.find('input[name="add_selected_clips"]').val()) {
                        var checkedClips = [];
                        $('input[name="id[]"]:checked').each(function () {
                            checkedClips.push($(this).val());
                        });
                        if (checkedClips.length > 0)
                            data += '&clips=' + checkedClips.join(',');
                    }

                    saveGallery(
                        data,
                        function () {
                            $('#gallery-form').dialog("close");
                            if (checkedClips.length > 0) {
                                var alert = $('#cliplog-alert-dialog');
                                alert.html('<div class="alert alert-success">' + checkedClips.length + ' clips added to Gallery</div>');
                                alert.dialog();
                            }
                        },
                        function (error) {
                            var alert = $('#gallery-form').find('.alert');
                            alert.text(error).addClass('alert-error').show();
                            setTimeout(function () {
                                alert.hide();
                            }, 3000);
                        },
                        $(this).find('[name="id"]').val()
                    )
                    deSelectAllClips();
                },
                Cancel: function () {
                    $(this).dialog("close");
                    deSelectAllClips();
                }
            },
            close: function () {
                $(this).find('form')[0].reset();
                $(this).find('.gallery-clips-list').html('');
                $(this).find('input[name="add_selected_clips"]').val(0);
                $('.cliplog-actions-select').val('');
            }
        });

        // Galleries events handlers
        $(".add-gallery-btn").click(function () {
            $("#gallery-form").dialog("open");
        });

        $(".edit-gallery-btn").click(function () {
            var galleryIDArr = $(this).attr('id').split('-');
            var galleryID = galleryIDArr[2] !== undefined ? galleryIDArr[2] : 0;
            getGallery(galleryID, function (gallery) {
                if (gallery) {
                    var clips_list = '';
                    if (gallery.clips.length > 0) {
                        clips_list += '<div class="control-group"><label class="control-label">Preview</label><div class="controls">';
                        $.each(gallery.clips, function (index, clip) {
                            clips_list += '<label class="radio inline">';
                            clips_list += '<input type="radio" name="preview_clip_id" value="' + clip.id + '"';
                            if (gallery.preview_clip_id == clip.id)
                                clips_list += ' checked="checked"';
                            clips_list += '>';
                            if (clip.thumb)
                                clips_list += '<img src="' + clip.thumb + '" width="70">';
                            clips_list += clip.code + '</label>';
                        });
                        clips_list += '</div></div>';
                    }

                    var form = $('#gallery-form');
                    form.find('[name="title"]').val(gallery.title);
                    form.find('[name="id"]').val(gallery.id);
                    form.find('[name="code"]').val(gallery.code);
                    if (gallery.featured == 1)
                        form.find('[name="featured"]').attr('checked', true);
                    else
                        form.find('[name="featured"]').attr('checked', false);
                    form.find('.gallery-clips-list').html(clips_list);
                    form.attr('title', 'Edit gallery');
                    form.dialog("open");
                }
            });
        });

        $(".delete-gallery-btn").click(function () {
            var galleryIDArr = $(this).attr('id').split('-');
            var galleryID = galleryIDArr[2] !== undefined ? galleryIDArr[2] : 0;
            deleteGallery(galleryID);
        });

        // Galleries onload functions
        enableGalleriesDroppable();

    })
})(jQuery);

/* ===================== Submissions ===================== */
(function ($) {

    // Submissions functions
    function getSubmissionsList(data) {
        if (!data)
            data = {};
        $.post(
            '/en/submissions/view',
            data,
            function (data) {
                if (data.success && data.submissions_list) {
                    $('.cliplog-submissions-list').replaceWith(data.submissions_list);
                }
            }
            ,
            'json'
        );
    }

    function saveSubmission(submissionData, success_callback, error_callback, submissionID) {
        var url = '/en/submissions/edit';
        if (submissionID)
            url += '/' + submissionID
        $.post(
            url,
            submissionData,
            function (data) {
                if (data.success) {
                    success_callback();
                    getSubmissionsList();
                }
                else {
                    error_callback(data.error);
                }
            },
            'json'
        );
    }

    function getSubmission(submissionID, handleData) {
        $.post(
            '/en/submissions/get_submission',
            {submission_id: submissionID},
            function (data) {
                if (data.success && data.submission) {
                    handleData(data.submission);
                }
            },
            'json'
        );
    }

    function deleteSubmission(submissionID) {
        if (submissionID)
            $.post(
                '/en/submissions/delete/' + submissionID,
                {},
                function (data) {
                    if (data.success) {
                        window.location.href = '/en/cliplog/view';
                    }
                },
                'json'
            );
    }

    $(function () {

        // Submissions plugins
        $("#submission-form").dialog({
            autoOpen: false,
            width: 300,
            modal: true,
            buttons: {
                "Save": function () {

                    var data = $(this).find('form').serialize() + '&save=1';
                    saveSubmission(
                        data,
                        function () {
                            $('#submission-form').dialog("close");
                        },
                        function (error) {
                            var alert = $('#submission-form').find('.alert');
                            alert.text(error).addClass('alert-error').show();
                            setTimeout(function () {
                                alert.hide();
                            }, 3000);
                        },
                        $(this).find('[name="id"]').val()
                    )

                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                $(this).find('input').val('');
            }
        });

        // Submissions events handlers
        $(".edit-submission-btn").click(function () {
            var submissionIDArr = $(this).attr('id').split('-');
            var submissionID = submissionIDArr[2] !== undefined ? submissionIDArr[2] : 0;
            getSubmission(submissionID, function (submission) {
                if (submission) {
                    var form = $('#submission-form');
                    form.find('[name="code"]').val(submission.code);
                    form.find('[name="date"]').val(submission.date);
                    form.find('[name="id"]').val(submission.id);
                    form.attr('title', 'Edit submission');
                    form.dialog("open");
                }
            });
        });

        $(".delete-submission-btn").click(function () {
            var submissionIDArr = $(this).attr('id').split('-');
            var submissionID = submissionIDArr[2] !== undefined ? submissionIDArr[2] : 0;
            deleteSubmission(submissionID);
        });

        $('.submissions-provider-select').change(function () {
            getSubmissionsList({provider_id: $(this).val()});
        });

        $('.submissions-provider-select').change(function () {
            getSubmissionsList({provider_id: $(this).val()});
        });

        $('.submissions-list-filter').on('submit', function (e) {
            e.preventDefault();
            var data = {};
            var provider_id = $('.submissions-provider-select').val();
            var words = $(this).find('input[name=words]').val();
            if (provider_id) {
                data.provider_id = provider_id;
            }
            if (words) {
                data.words = words;
            }
            getSubmissionsList(data);
        });


        $(".cliplog-submissions-scroll").on('click', 'li', function (e) {
            $(this).toggleClass('collapsed');
            e.stopPropagation();
        });

        $(".cliplog-submissions-actions").on('click', '.collapse-action', function (e) {
            $('.cliplog-submissions-scroll').find('li').addClass('collapsed');
            e.stopPropagation();
            e.preventDefault();
        });

        $(".cliplog-submissions-actions").on('click', '.expand-action', function (e) {
            $('.cliplog-submissions-scroll').find('li').removeClass('collapsed');
            e.stopPropagation();
            e.preventDefault();
        });

    })
})(jQuery);


/* ===================== Sequences ===================== */
(function ($) {

    // Sequences functions
    function addClipsToSequence(sequenceID, clipsIDs) {
        if (sequenceID && clipsIDs) {
            $.post(
                '/en/sequences/items/' + sequenceID,
                {items_ids: clipsIDs},
                function (data) {
                    if (data.success) {
                        var alert = $('#cliplog-alert-dialog');
                        alert.html('<div class="alert alert-success">' + clipsIDs.length + ' clips added to Sequence</div>');
                        alert.dialog();
                    }
                },
                'json'
            );
        }
    }

    function enableSequencesDroppable() {
        $('.cliplog-sequences-list li').droppable({
            tolerance: 'pointer',
            hoverClass: 'active',
            accept: '.draggable-clip',
            drop: function (event, ui) {
                var selected = ui.helper.find('.footagesearch-clip.selected');
                var selectedIDs = [];
                var sequenceIDArr = $(this).attr('id').split('-');
                var sequenceID = sequenceIDArr[2] !== undefined ? sequenceIDArr[2] : 0;
                if (selected.length > 0) {
                    selected.each(function (index) {
                        var idArr = $(this).attr('id').split('-');
                        if (idArr[2] !== undefined)
                            selectedIDs.push(idArr[2]);
                    });
                }
                if (sequenceID && selectedIDs.length > 0) {
                    addClipsToSequence(sequenceID, selectedIDs);
                }
            }
        });
    }

    function getSequencesList() {
        $.post(
            '/en/sequences/view',
            {},
            function (data) {
                if (data.success && data.sequences_list) {
                    $('.cliplog-sequences-list').replaceWith(data.sequences_list);
                    enableSequencesDroppable();
                }
            }
            ,
            'json'
        );
    }

    function saveSequence(sequenceData, success_callback, error_callback, sequenceID) {
        var url = '/en/sequences/edit';
        if (sequenceID)
            url += '/' + sequenceID
        $.post(
            url,
            sequenceData,
            function (data) {
                if (data.success) {
                    success_callback();
                    getSequencesList();
                }
                else {
                    error_callback(data.error);
                }
            },
            'json'
        );
    }

    function getSequence(sequenceID, handleData) {
        $.post(
            '/en/sequences/get_sequence',
            {sequence_id: sequenceID},
            function (data) {
                if (data.success && data.sequence) {
                    handleData(data.sequence);
                }
            },
            'json'
        );
    }

    function deleteBin(sequenceID) {
        if (sequenceID)
            $.post(
                '/en/sequences/delete/' + sequenceID,
                {},
                function (data) {
                    if (data.success) {
                        window.location.href = '/en/cliplog/view';
                    }
                },
                'json'
            );
    }

    $(function () {

        // Sequences plugins
        $("#sequence-form").dialog({
            autoOpen: false,
            width: 300,
            modal: true,
            buttons: {
                "Save": function () {
                    var form = $(this).find('form');
                    var data = form.serialize() + '&save=1';
                    if (form.find('input[name="add_selected_clips"]').val()) {
                        var checkedClips = [];
                        $('input[name="id[]"]:checked').each(function () {
                            checkedClips.push($(this).val());
                        });
                        if (checkedClips.length > 0)
                            data += '&clips=' + checkedClips.join(',');
                    }
                    saveSequence(
                        data,
                        function () {
                            $('#sequence-form').dialog("close");
                            if (checkedClips.length > 0) {
                                var alert = $('#cliplog-alert-dialog');
                                alert.html('<div class="alert alert-success">' + checkedClips.length + ' clips added to Sequence</div>');
                                alert.dialog();
                            }
                        },
                        function (error) {
                            var alert = $('#sequence-form').find('.alert');
                            alert.text(error).addClass('alert-error').show();
                            setTimeout(function () {
                                alert.hide();
                            }, 3000);
                        },
                        $(this).find('[name="id"]').val()
                    )
                    deSelectAllClips();
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                $(this).find('input').val('');
                $(this).find('input[name="add_selected_clips"]').val(0);
                $('.cliplog-actions-select').val('');
            }
        });

        // Sequences events handlers
        $(".add-sequence-btn").click(function () {
            $("#sequence-form").dialog("open");
        });

        $(".edit-sequence-btn").click(function () {
            var sequenceIDArr = $(this).attr('id').split('-');
            var sequenceID = sequenceIDArr[2] !== undefined ? sequenceIDArr[2] : 0;
            getSequence(sequenceID, function (sequence) {
                if (sequence) {
                    var form = $('#sequence-form');
                    form.find('[name="title"]').val(sequence.title);
                    form.find('[name="id"]').val(sequence.id);
                    form.find('[name="code"]').val(sequence.code);
                    form.attr('title', 'Edit sequence');
                    form.dialog("open");
                }
            });
        });

        $(".delete-sequence-btn").click(function () {
            var sequenceIDArr = $(this).attr('id').split('-');
            var sequenceID = sequenceIDArr[2] !== undefined ? sequenceIDArr[2] : 0;
            deleteBin(sequenceID);
        });

        // Sequences onload functions
        enableSequencesDroppable();

    })
})(jQuery);

/* ===================== Clipbins ===================== */
(function ($) {

    // Clipbins functions
    function addClipsToClipbin(clipbinID, clipsIDs) {
        if (clipbinID && clipsIDs) {
            $.post('/en/clipbins/items/' + clipbinID, {
                    items_ids: clipsIDs
                }
            );
        }
    }

    function enableClipbinsDroppable() {
        /* Перемещено в cliplog.clipbin.js
         $('.cliplog-clipbins-list li').droppable({
         tolerance: 'pointer',
         hoverClass: 'active',
         accept: '.draggable-clip',
         drop: function(event, ui) {
         var selected = ui.helper.find('.footagesearch-clip.selected');
         var selectedIDs = [];
         var clipbinIDArr = $(this).attr('id').split('-');
         var clipbinID = clipbinIDArr[2] !== undefined ? clipbinIDArr[2] : 0;
         if(selected.length > 0){
         selected.each(function(index){
         var idArr = $(this).attr('id').split('-');
         if(idArr[2] !== undefined)
         selectedIDs.push(idArr[2]);
         });
         }
         if(clipbinID && selectedIDs.length > 0){
         addClipsToClipbin(clipbinID, selectedIDs);
         }
         }
         });
         */
    }

    function getClipbinsList() {
        $.post(
            '/en/clipbins/view',
            {},
            function (data) {
                if (data.success && data.clipbins_list) {
                    $('.cliplog-clipbins-list').replaceWith(data.clipbins_list);
                    enableClipbinsDroppable();
                }
            }
            ,
            'json'
        );
    }

    function saveClipbin(clipbinData, success_callback, error_callback, clipbinID) {
        var url = '/en/clipbins/edit';
        if (clipbinID)
            url += '/' + clipbinID
        $.post(
            url,
            clipbinData,
            function (data) {
                if (data.success) {
                    success_callback();
                    getClipbinsList();
                }
                else {
                    error_callback(data.error);
                }
            },
            'json'
        );
    }

    function getClipbin(clipbinID, handleData) {
        $.post(
            '/en/clipbins/get_clipbin',
            {clipbin_id: clipbinID},
            function (data) {
                if (data.success && data.clipbin) {
                    handleData(data.clipbin);
                }
            },
            'json'
        );
    }

    function deleteClipbin(clipbinID) {
        if (clipbinID)
            $.post(
                '/en/clipbins/delete/' + clipbinID,
                {},
                function (data) {
                    if (data.success) {
                        window.location.href = '/en/cliplog/view';
                    }
                },
                'json'
            );
    }

    $(function () {

        // Clipbins plugins
//        $("#clipbin-form" ).dialog({
//            autoOpen: false,
//            width: 300,
//            modal: true,
//            buttons: {
//                "Save": function() {
//
//                    var data = $(this).find('form').serialize() + '&save=1';
//                    saveClipbin(
//                        data,
//                        function(){
//                            $('#clipbin-form').dialog("close");
//                        },
//                        function(error){
//                            var alert = $('#clipbin-form').find('.alert');
//                            alert.text(error).addClass('alert-error').show();
//                            setTimeout(function() {
//                                alert.hide();
//                            }, 3000);
//                        },
//                        $(this).find('[name="id"]').val()
//                    )
//
//                },
//                Cancel: function() {
//                    $(this).dialog("close");
//                }
//            },
//            close: function() {
//                $(this).find('input').val('');
//            }
//        });

        // Clipbins events handlers
//        $(".add-clipbin-btn").click(function() {
//            $("#clipbin-form").dialog("open");
//        });
//
//        $(".edit-clipbin-btn").click(function() {
//            var clipbinIDArr = $(this).attr('id').split('-');
//            var clipbinID = clipbinIDArr[2] !== undefined ? clipbinIDArr[2] : 0;
//            getClipbin(clipbinID, function(clipbin){
//                if(clipbin){
//                    var form = $('#clipbin-form');
//                    form.find('[name="title"]').val(clipbin.title);
//                    form.find('[name="id"]').val(clipbin.id);
//                    form.find('[name="code"]').val(clipbin.code);
//                    form.attr('title', 'Edit clipbin');
//                    form.dialog("open");
//                }
//            });
//        });
//
//        $(".delete-clipbin-btn").click(function() {
//            var clipbinIDArr = $(this).attr('id').split('-');
//            var clipbinID = clipbinIDArr[2] !== undefined ? clipbinIDArr[2] : 0;
//            deleteClipbin(clipbinID);
//        });

        // Clipbins onload functions
        enableClipbinsDroppable();

    })
})(jQuery);


/* ===================== Advanced Search ===================== */
(function ($) {

    $(function () {

        // Advanced search
        $("#advanced-search-form").dialog({
            autoOpen: false,
            width: 300,
            modal: true,
            buttons: {
                "Search": function () {
                    $(this).find('form')[0].submit();
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                $(this).find('form')[0].reset();
            }
        });

        $(".advanced-search-btn").click(function (e) {
            e.preventDefault();
            $("#advanced-search-form").dialog("open");
        });

    })
})(jQuery);