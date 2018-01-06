!function ($, undefined) {

    "use strict"; // jshint ;_;

    /* CLIPLOG PUBLIC CLASS DEFINITION
     * =============================== */


    jQuery.fn.idle = function (time) {
        var i = $(this);
        i.queue(function () {
            setTimeout(function () {
                i.dequeue();
            }, time);
        });
    };
    var Cliplog = function (element, options) {
        this.init(element, options)
    };
    var timer_id;
    Cliplog.prototype = {
        constructor: Cliplog,
        init: function (element, options) {
            this.$element = $(element);
            this.options = $.extend({}, $.fn.cliplog.defaults, options);
            var addKeywordsSelector = '.' + this.options.addKeywordsClass;
            var closeKeywordsSelector = '.' + this.options.closeKeywordsClass;
            var addKeywordsSelector_New = '.' + this.options.addKeywordsClass_New;
            var closeKeywordsSelector_New = '.' + this.options.closeKeywordsClass_New;
            var addKeywordToListSelector = '.' + this.options.addKeywordToListClass;
            var addKeywordToSelectedSelector = '.' + this.options.addKeywordToSelectedClass;
            var addSectionOptionsSelector = '.' + this.options.addSectionOptionsClass;
            var closeSectionOptionsSelector = '.' + this.options.closeSectionOptionsClass;
            var saveTemplateSelector = '.' + this.options.saveTemplateClass;
            var applyTemplateSelector = '.' + this.options.applyTemplateClass;
            var saveKeywordsSetSelector = '.' + this.options.saveKeywordsSetClass;
            var removeSelectedKeywordSelector = '.' + this.options.removeSelectedKeywordClass;
            var selectSubmissionCodec = '#' + this.options.selectSubmissionCodec;
            var selectDeliveryCategory = '#' + this.options.selectDeliveryCategory;
            var selectSourceFormat = '.' + this.options.selectSourceFormat;
            var selectSubmissionFrameSize = '.' + this.options.selectSubmissionFrameSize;
            $('.cliplog_keywords_input_control').hide();
            $('.cliplog_close_options').hide();
            $(document).on('click.cliplog', addKeywordsSelector, $.proxy(this.changeKeywords, this));
            $(document).on('click.cliplog', addKeywordToListSelector, $.proxy(this.addKeywordToList, this));
            $(document).on('click.cliplog', addKeywordToSelectedSelector, $.proxy(this.addKeywordToSelected, this));
            $(document).on('click.cliplog', '.cliplog_show_all_keywords', $.proxy(this.showAllKeywords, this));
            $(document).on('click.cliplog', '.cliplog_hide_extras_keywords', $.proxy(this.hideExtrasKeywords, this));
            $(document).on('click.cliplog', addSectionOptionsSelector, $.proxy(this.addSectionOptions, this));
            $(document).on('click.cliplog', closeSectionOptionsSelector, $.proxy(this.closeSectionOptions, this));
            $(document).on('click.cliplog', saveTemplateSelector, $.proxy(this.createLoggingTemplate, this));
            $(document).on('click.cliplog', saveKeywordsSetSelector, $.proxy(this.saveKeywordsSet, this));
            $(document).on('click.cliplog', removeSelectedKeywordSelector, $.proxy(this.onRemoveSelectedKeywordClick, this));
            $(document).on('click.cliplog', '.cliplog_delete_keyword', $.proxy(this.onDeleteKeywordClick, this));
            $(document).on('click.cliplog', '.cliplog_selected_keywords_list input:checkbox', $.proxy(this.deselectSelectedKeyword, this));
            $(document).on('click.cliplog', '.cliplog_options_list input:checkbox', $.proxy(this.addClickedOption, this));
            $(document).on('click.cliplog', '.cliplog_add_formats', $.proxy(this.addFormats, this));
            $(document).on('change.cliplog', 'select[name="sections_values[collection]"]', $.proxy(this.reloadKeywordsLists, this));
            $(document).on('change.cliplog', selectSubmissionCodec, $.proxy(this.changeDeliveryCategory, this));
            $(document).on('change.cliplog', selectSourceFormat, $.proxy(this.changeSecondaryCollection, this));
            $(document).on('change.cliplog', selectSubmissionFrameSize, $.proxy(this.changeSecondaryCollection, this));
            $(document).on('click.cliplog', '.cliplog_selected_keywords_list_new a.item-cross', $.proxy(this.deselectNewSelectedKeyword, this));
            $(document).on('click.cliplog', '.cliplog_delete_template', $.proxy(this.deleteSelectedTemplate, this));
            $(document).on('click.cliplog', '.cliplog_update_template', $.proxy(this.updateSelectedTemplate, this));
            $(document).on('click.cliplog', '.cliplog_save_metadata', $.proxy(this.saveSelectedTemplate, this));
            $(document).on('click.cliplog', '.save_logging_template', $.proxy(this.saveLoggingTemplate, this));
            $(document).on('submit.cliplog', '#cliplog_form', $.proxy(this.showReloadLayout, this));
            $(document).on('submit.cliplog', '.carousel_form', $.proxy(this.showReloadLayout, this));
            $(document).on('change.cliplog', '#goto-next', $.proxy(this.updateGotoNextStatus, this));
            $(document).on('click.cliplog', '.apply_logging_template', $.proxy(this.applyLoggingTemplate, this));
            $(document).on('change.cliplog', '#applied_template_id', $.proxy(this.showOrHideDeleteLoggingButton, this));
            $(document).on('change.cliplog', '#applied_template_id', $.proxy(this.showOrHideApplyLoggingButton, this));
            $(document).on('change.cliplog', '#overwrite_fields', $.proxy(this.checkOverwriteFields, this));
            $(document).on('click.cliplog', '.cliplog_keywords_list input.cliplog_keyword_checkbox', $.proxy(this.addClickedKeyword, this));
            $(document).on('click.cliplog', '.cliplog_show_all_keywords_new', $.proxy(this.showNewAllKeywords, this));
            $(document).on('click.cliplog', '.cliplog_hide_extras_keywords_new', $.proxy(this.hideNewExtrasKeywords, this));

            var sessionID = $.cookie("PHPSESSID");
            if (!_.isUndefined(sessionStorage["sessionID"]) && (sessionStorage["sessionID"] != sessionID)) {
                // this.resetSectionsSessionConfig();
            }
            sessionStorage["sessionID"] = sessionID;
            this.getLoggingTemplateList();
            this.initSectionsSwitch();
            this.initTemplateBlocks();
            this.showOrHideDeleteLoggingButton();
            this.showOrHideApplyLoggingButton();
        },
        addKeywordFromField: function (e) {
            if (e.keyCode == 13) { // Enter
                var input = $(e.currentTarget);
                if (input.val()) {
                    var isSection = input.closest('.cliplog_section').attr('id');
                    var isPopup = input.is('.is-popup');
                    if (isSection) {
                        this.addNewKeywordToList(e);
                    } else if (isPopup) {
                        this.addNewKeywordToPopupList(e);
                    }
                } else {
                    this.alertBox('Enter keyword name');
                }
            }
        },
        getLoggingTemplateList: function () {
            sessionStorage.removeItem('loggingTemplateList');
            var loggingTemplateId = $('.header-template-name').data('template-id');
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/index/gettemplatesectionlist',
                dataType: 'json',
                data: {id: loggingTemplateId},
                cache: false,
                async: false,
                success: function (data) {
                    /*formKeeper.hideReloadLayout();
                     if ( data.status == 1 && data.template.id ) {
                     templateNameField.val( '' );
                     templateHeader.attr( 'data-template-id', data.template.id );
                     templateHeaderTitle.html( data.template.name );
                     that.getTemplatesList( data.template.id );
                     that.alertBox( 'Saved' );
                     // cliplog.formkeeper.js
                     formKeeper.reinit( 'logging' );
                     KM.stateManager.createBasicState(); // Пересоздаем окружение кл.слов
                     }*/
                    sessionStorage['loggingTemplateList'] = JSON.stringify(data);
                }
            });
        },
        removeModifyLogging: function () {
            sessionStorage.removeItem('LoggingTemplateModify');
            sessionStorage.removeItem('LoggingModified');
            sessionStorage.removeItem('LoggingSaveButton');
        },
        initModifyLogging: function () {
            sessionStorage['LoggingTemplateModify'] = ' (modified)';
            sessionStorage['LoggingModified'] = 1;
            sessionStorage['LoggingSaveButton'] = 1;
        },
        hackLoggingModified: function () { // dont save to session
            /*var template = ' (modified)';
             var templateTitleBox = $( '.header-template-name span' );
             var name = templateTitleBox.html();
             var newName = name.replace( template, '' );
             templateTitleBox.html( newName + template );
             // show button
             if ( !!$( '.header-template-name' ).attr( 'data-template-id') ) {
             $( '.header-template-name input.save_logging_template' ).attr( 'data-message', 0 ).show();
             } else {
             $( '.header-template-name input.save_logging_template' ).attr( 'data-message', 1 ).show();
             }*/
        },
        multiValuesFields: {
            'shot_type': 1,
            'subject_category': 1,
            'primary_subject': 1,
            'other_subject': 1,
            'appearance': 1,
            'actions': 1,
            'time': 1,
            'habitat': 1,
            'concept': 1,
            'location': 1
        },
        initSectionsSwitch: function () {
            if (document.referrer != location.href) {
                sessionStorage.removeItem('keywordTemplateId');
                sessionStorage.removeItem('KeywordsModified');
            }
            var that = this;
            //if(sessionStorage['keywordTemplateId']!=null){
            var loggingTemlateList = $.parseJSON(sessionStorage['loggingTemplateList']);
            sessionStorage.removeItem('LoggingTemplateModify');
            // Reset Keyword template

            console.log(sessionStorage);
            //$('.control-group.cliplog_section').each(function(){
            $('.control-group').each(function () {

                var id = $(this).attr("id");
                var sectionObjVal = $($('[name^="sections_values[' + id + '"]')).val();
                var sectionName = $(this).find('input[name="sections[]"]').attr('id');
                if (id != 'Add_formats') {
                    console.log('switch: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                    if (!_.isUndefined(id) && !_.isEmpty(id)) {
                        if (!_.isUndefined(sessionStorage["cliplog_hide_sections_" + id])) {
                            var sectionInfo = sessionStorage["cliplog_hide_sections_" + id].split(",");
                            that.switchOffSection(sectionInfo[0], sectionInfo[1], true);
                            console.log('switchOff: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                        }
                        if ((!_.isUndefined(sessionStorage["cliplog_show_sections_" + id])) || (!_.isUndefined(sessionStorage['keywordTemplateId']) && !_.isEmpty(sessionStorage['keywordTemplateId'])) /*&& !_.isEmpty(loggingTemlateList['keywords_sections_visible'].indexOf(id) + 1)*/ && sectionObjVal != undefined) {
                            console.log(!_.isUndefined(sessionStorage["cliplog_show_sections_" + id]) + ' ' + !_.isUndefined(sessionStorage['keywordTemplateId']) + ' ' + !_.isEmpty(sessionStorage['keywordTemplateId']) + ' ' + /*(loggingTemlateList['keywords_sections_visible'].indexOf(id) +1)+*/' "' + sectionObjVal + '"');
                            if (!_.isUndefined(sessionStorage["cliplog_show_sections_" + id])) {
                                var sectionInfo = sessionStorage["cliplog_show_sections_" + id].split(",");
                                that.switchOnSection(sectionInfo[0], sectionInfo[1], true);
                            } else {
                                that.switchOnSection(id, sectionName, true);
                            }

                            console.log('switchOn: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                        }
                    }

                    if (!_.isUndefined(id) && !_.isEmpty(id)) {
                        if (loggingTemlateList['is_admin'] != 1 && id == 'brand') {
                            //that.switchOffSection( id, sectionName, false );
                            console.log('switchOff: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                        } else {
                            if (loggingTemlateList[id] == undefined /*&& (sectionObjVal =='' || sectionObjVal == 0 || sectionObjVal != undefined)&& !(loggingTemlateList['keywords_sections_visible'].indexOf(id) + 1)*/) {
                                // hide
                                if ((!_.isUndefined(sessionStorage["cliplog_show_sections_" + id])) /*|| (!_.isUndefined(sessionStorage['keywordTemplateId']) || !_.isEmpty(sessionStorage['keywordTemplateId'])) /*&& !_.isUndefined(that.multiValuesFields[id]) && sectionObjVal!=undefined*/) {
                                    var sectionInfo = sessionStorage["cliplog_show_sections_" + id].split(",");
                                    that.switchOnSection(sectionInfo[0], sectionInfo[1], true);
                                    console.log('switchOn: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                                } else {
                                    that.switchOffSection(id, sectionName, true);
                                    console.log('switchOff: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                                }
                            } else {
                                // show
                                if (!_.isUndefined(sessionStorage["cliplog_hide_sections_" + id]) /*&& (_.isUndefined(sectionObjVal) || _.isEmpty(sectionObjVal))*/) {
                                    var sectionInfo = sessionStorage["cliplog_hide_sections_" + id].split(",");
                                    that.switchOffSection(sectionInfo[0], sectionInfo[1], true);
                                    console.log('switchOff: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                                } else {
                                    that.switchOnSection(id, sectionName, true);
                                    console.log('switchOn: ' + id + ' <> ' + loggingTemlateList[id] + ' <> ' + sectionObjVal);
                                }
                            }
                        }
                    }
                }
            });
            that.initSessionField();
            //}
            //if(sessionStorage['LoggingTemplateModify']!='true') that.removeModifyLogging();
            $('.section-switch-cont .switch')/*.find( '.switch' )*/.live('switch-change', function (e, data) {
                var $el = $(data.el);
                var value = data.value;
                var sectionID = $el.val();
                var sectionName = $el.attr('id');
                console.log([data, value, sectionID, sectionName]);
                if (value == false && sectionID && sectionName) {
                    that.confirmBox(
                        'Do you want to hide this field?',
                        function () {
                            that.switchOffSection(sectionID, sectionName, true);
                        },
                        function () {
                            $el.prop('checked', true);
                            $('#' + sectionID + ' .section-switch-cont .switch-off').removeClass('switch-off').addClass('switch-on');
                        }
                    );
                } else if (value == true && sectionID) {
                    that.switchOnSection(sectionID, sectionName, true);
                } else {
                    that.switchOnSection(sectionID, sectionName, true);
                }

                var template = ' (modified)';
                var templateTitleBox = $('.header-template-name span');
                var name = templateTitleBox.html();
                var newName = name.replace(template, '');
                templateTitleBox.html(newName + template);

                sessionStorage.setItem("LoggingModified", template);// save Modified if refresh page

                if (!!$('.header-template-name').attr('data-template-id')) {
                    $('.header-template-name input.save_logging_template').attr('data-message', 0).show();
                    sessionStorage.setItem("LoggingSaveButton", 0);// save Button if refresh page
                } else {
                    $('.header-template-name input.save_logging_template').attr('data-message', 1).show();
                    sessionStorage.setItem("LoggingSaveButton", 1);// save Button if refresh page
                }


            });
        },
        initTemplateBlocks: function () {
            var that = this;
            var metadataButtonSave = $('.save_metadata_template');
            var loggingButtonSave = $('.save_logging_template');
            var metadataButtonApply = $('.apply_metadata_template');
            var metadataButtonDelete = $('.delete_metadata_template');
            // restore Modified if refresh page
            var LoggingModified = (sessionStorage.getItem("LoggingModified") == null) ? '' : sessionStorage.getItem("LoggingModified");
            //var KeywordsModified=(sessionStorage.getItem("KeywordsModified") == null) ? '' : sessionStorage.getItem("KeywordsModified");
            var loggingHeader = $('.cliplog_template_header').text().replace(' (modified)', '');
            $('.cliplog_template_header').text(loggingHeader + LoggingModified);
            //$('.cliplog_Keyword_header' ).append(KeywordsModified);

            that.hiddenElement(metadataButtonSave);
            // restore Save Button if refresh page
            if (sessionStorage.getItem("LoggingSaveButton") == 0 || sessionStorage.getItem("LoggingSaveButton") == 1) {
                loggingButtonSave.attr('data-message', sessionStorage.getItem("LoggingSaveButton")).show();
            }
            /*if(sessionStorage.getItem("KeywordsSaveButton") == 0 || sessionStorage.getItem("KeywordsSaveButton") == 1){
             metadataButtonSave.attr( 'data-message', sessionStorage.getItem("KeywordsSaveButton") ).show();
             }*/

            that.updateTemplateBlocks();
            $('#applied_keywords_set_id').on('change', function (event) {
                if ($(event.currentTarget).is('#applied_keywords_set_id')) {
                    that.visibleElement(metadataButtonApply, 'inline-block');
                    that.visibleElement(metadataButtonDelete, 'inline-block');
                } else {
                    //that.updateTemplateBlocks();
                    that.selectNewTemplate();
                }
            });

            $('input[name^="overwrite"]').on('change', function () {
                that.checkOverwrite(this);
            });

            // $('input[name="overwrite[add_collection][]"]').on('change', function () {
            //     if ($(this).prop("checked") != 'checked') {
            //         $('input[name="sections_values[add_collection][]"]').prop("checked", false);
            //     }
            // });

            $('#cliplog_template input[type="text"]').on('focusin', function () {
                that.focusOverwriteFields(this);
            });
            // view overwrite checkboxs and select in single clip
            if (sessionStorage.getItem('keywordTemplateId') != null) {
                $('#overwrite_fields').closest('div.control-group').css('display', 'block');
            }
            if (sessionStorage.getItem('saveCliplogEdit') != null) {
                $('#overwrite_fields').closest('div.control-group').css('visibility', 'hidden');
            }

            $('input[name^="overwrite"]').each(function (field) {
                //single clip view overwrite checkboxs
                if ($(this).parent().hasClass('single_clip_owervrite_class')) {
                    //$(this).prop('checked',true);
                    if (sessionStorage.getItem('keywordTemplateId') != null) {
                        $(this).parent().css('display', 'block');
                    }
                    if (sessionStorage.getItem('saveCliplogEdit') != null) {
                        $(this).parent().css('visibility', 'hidden');
                    }
                }
                // init overwrite checked
                /*if(sessionStorage.getItem('keywordTemplateId') == '' || sessionStorage.getItem('keywordTemplateId') == undefined){
                 $(this).prop('checked',true);
                 }else{
                 $(this).prop('checked',false);
                 }*/
            });
            that.checkOverwriteSingleField();
            sessionStorage.removeItem('saveCliplogEdit');
        },
        hiddenElement: function (element) {
            element.css('display', 'none');
        },
        visibleElement: function (element, value) {
            element.css('display', (value == undefined) ? 'block' : value);
        },
        checkOverwriteSingleField: function () {
            $('[name^="sections_values["]').each(function () {
                if (($(this).prop('tagName') == 'SELECT') || ($(this).prop('tagName') == 'INPUT' && $(this).prop('type') != 'checkbox')) { // all single fields
                    if ($(this).val() != '' && $(this).val() != 0 && $(this).attr('type') != 'radio') {
                        var name = $(this).prop('name').replace(/sections_values/, 'overwrite');
                        var name = name.replace(/\[month\]|\[year\]/, '');
                        $('input[name="' + name + '"]').prop('checked', true);
                        $('input[name="' + name + '"]').prop('disabled', true);

                    }
                    $(this).on('change', function () {
                        var name = $(this).prop('name').replace(/sections_values/, 'overwrite');
                        var name = name.replace(/\[month\]|\[year\]/, '');
                        var single = $('input[name="' + name + '"]').parent().hasClass('single_clip_owervrite_class');
                        if (!single) {
                            if ($(this).attr('type') == 'radio') {
                                $('input[name="' + name + '"]').prop('checked', true);
                            } else if ($(this).val() != '' && $(this).val() != 0) {
                                $('input[name="' + name + '"]').prop('checked', true);
                                $('input[name="' + name + '"]').prop('disabled', true);
                            } else {
                                $('input[name="' + name + '"]').prop('checked', false);
                                $('input[name="' + name + '"]').prop('disabled', false);
                            }
                        }
                    });
                }
            });
        },
        resetSectionsSessionConfig: function () {

            var toDelete = [];
            for (var i = 0; i < sessionStorage.length; i++) {
                var entryKey = sessionStorage.key(i);
                if (
                    (entryKey.indexOf("cliplog_hide_sections_") > -1) ||
                    (entryKey.indexOf("cliplog_show_sections_") > -1) ||
                    (entryKey.indexOf("cliplog_keywords_") > -1)
                ) {
                    toDelete.push(entryKey);
                }
            }

            _.each(toDelete, function (keyName) {
                delete sessionStorage[keyName];
            });
            this.removeModifyLogging();
        },
        applyLoggingTemplate: function () {

            var that = this;
            if (formKeeper.isLoggingModified()) {
                this.confirmBox('Changes to your current Logging Template will be lost.', function () {

                    that.resetSectionsSessionConfig();
                    // reset Modified and Save button on session
                    sessionStorage.removeItem('LoggingModified');
                    sessionStorage.removeItem('LoggingSaveButton');
                    that.removeSessionField();
                    that.removeModifyLogging();
                    var form = $('#cliplog_form');
                    $('#apply_template').attr('value', 1);
                    form.submit();
                });
            } else {

                that.resetSectionsSessionConfig();
                // reset Modified and Save button on session
                sessionStorage.removeItem('LoggingModified');
                sessionStorage.removeItem('LoggingSaveButton');
                that.removeSessionField();
                that.removeModifyLogging();
                var form = $('#cliplog_form');
                $('#apply_template').attr('value', 1);
                form.submit();
            }
        },
        saveForm: function (save) {
            var save = (save == undefined || save == true) ? '&save=1' : '';
            var data = $('form#cliplog_form').serialize();
            var url = $('form#cliplog_form').attr('action');
            console.log(url, data);
            $.ajax({
                type: 'POST',
                url: '/' + url,
                data: data + save,
                success: function (data) {
                    if ($('#goto-next').prop("checked"))
                        window.location = '/en/cliplog/edit/' + $('#goto-next').val();
                    $('.reload-layout').hide();
                    //$('html').html(data);
                },
                error: function (xhr, str) {
                    console.log('Error saveForm: ' + xhr.responseCode + '<br> str:' + str);
                    alert('Error: ' + xhr.responseCode);
                }
            });
        },
        alertBox: function (message) {
            var box = $('#dialog-alert');
            box.find('.message').html(message);
            box.dialog('open');
            //box.dialog( "option", "position", { my: "center top", at: "center top", of: $( event.currentTarget ) } );
        },
        confirmBox: function (message, confirmCallback, cancelCallback) {
            var box = $('#dialog-confirm');
            box.find('.message').html(message);
            box.data('confirmCallback', confirmCallback);
            box.data('cancelCallback', cancelCallback);
            box.dialog('open');
            //box.dialog( "option", "position", { my: "center top", at: "center top", of: $( event.currentTarget ) } );
        },
        showReloadLayout: function () {
            $('.reload-layout').css('display', 'block');
        },
        hideReloadLayout: function () {
            $('.reload-layout').css('display', 'none');
        },
        selectNewTemplate: function (e) {
            var that = this;
            var button = $(e.currentTarget);
            var form = $('#cliplog_form');
            var box = button.closest('.control-group');
            var type = box.attr('data-type');
            if (type && form) {
                if ($('#applied_template_id').find('option:selected')) {
                    //$( '#apply_template' ).attr( 'value', 1 );
                }
                if ($(box).find('#applied_keywords_set_id')) {
                    //if ( $( '#applied_keywords_set_id' ).find( 'option:selected' ) ) {
                    $('#apply_keywords_set').attr('value', 1);
                    //}
                }
            }
            //this.saveForm(false);
            form.append('<input type="text" name="overwrite_all" value="1">');
            form.submit();
        },
        showOrHideDeleteLoggingButton: function () {
            var button = $('.delete_logging_template');
            var select = $('#applied_template_id');
            var option = select.find('option:selected');
            if (option.length && !!option.val() && !option.is(':disabled')) {
                button.show();
            } else {
                button.hide();
            }
        },
        showOrHideApplyLoggingButton: function () {
            var button = $('.apply_logging_template');
            var select = $('#applied_template_id');
            var option = select.find('option:selected');
            if (!option.is(':disabled')) {
                button.show();
            } else {
                button.hide();
            }
        },
        deleteSelectedTemplate: function (e) {
            var that = this;
            var message = 'Do you want delete template?';
            var button = $(e.currentTarget);
            var form = $('#cliplog_form');
            var box = button.closest('.control-group');
            var type = box.attr('data-type');
            var select = box.find('select');
            var option = select.find('option:selected');
            var id = option.val();
            if (id && type && form) {
                this.confirmBox(message, function () {
                    $.ajax({
                        type: 'POST',
                        url: 'en/cliplog/index/deletetemplate',
                        dataType: 'json',
                        data: ({type: type, id: id}),
                        cache: false,
                        success: function (response) {
                            if (response.status) {

                                location.reload();
                                //option.remove();
                                //var sidebar = $( '#cliplog_sidebar_content' );
                                //sidebar.find( 'input[name="apply_template"]' ).attr( 'value', 1 );
                                //sidebar.find( 'input[name="apply_keywords_set"]' ).attr( 'value', 1 );
                                //form.submit();
                            }
                        }
                    });
                });
            }
        },
        saveLoggingTemplate: function (e) {
            if ($(e.currentTarget).attr('data-message') == 1) {
                // Отобразить лишь сообщение
                this.alertBox('The Default Logging Template can not be modified. Please save the layout as a new logging template.');
            } else {
                var that = this;
                var button = $(e.currentTarget);
                var form = $('#cliplog_form');
                var box = button.parent();
                var titleBox = box.find('span');
                var type = 'logging';
                var id = box.attr('data-template-id');
                var data = form.serialize();
                if (id && type && data) {

                    this.resetSectionsSessionConfig();
                    var requestData = $("#cliplog_form").serializeArray();
                    //console.log(requestData);
                    requestData.push({name: 'keywords_sections_visible', value: this.getExpandedKeywordSections()});
                    requestData.push({name: 'keywords_sections_hide_lists', value: this.getKeywordSectionsHideList()});
                    // Передаем состояние кл.слов формы
                    $.each(KM.stateManager.getKeywordsState(), function (index, object) {
                        // requestData.push({name: 'keywordList[]', value: JSON.stringify(object, null, 2)});
                    });
                    // requestData.push(dataUsers);
                    // requestData.push(data);
                    $.each(KM.stateManager.getKeywordsState(), function (index, object) {
                        requestData.push({name: 'keywordListImran[]', value: JSON.stringify(object, null, 2)});
                    });
                    var data = [];
                    $('.cliplog_keyword_checkbox').each(function () {
                        var id = $(this).attr("name");
                        requestData.push({name: 'keywordListMyList[]', value: id});
                    })

                    //Imran Backup
                    //   var requestData = $("#cliplog_form").serializeArray();
                    //   requestData.push({name: 'keywords_sections_visible', value: this.getExpandedKeywordSections()});
                    //   requestData.push({name: 'keywords_sections_hide_lists', value: this.getKeywordSectionsHideList()});
                    //   console.log(requestData);
                    //
                    //   $.each(KM.stateManager.getKeywordsState(), function(index, object) {
                    //       requestData.push({name: 'keywordList[]', value: JSON.stringify(object, null, 2)});
                    //   });
                    //
                    //   requestData.push({name: 'add_formats', value: $('#Add_formats').attr('checked')});
                    //   formKeeper.showReloadLayout();
                    //   console.log('requestData:');
                    //   console.log(requestData);

                    formKeeper.showReloadLayout();
                    //Imran Backup


                    // reset Modified and Save button on session
                    sessionStorage.removeItem('LoggingModified');
                    sessionStorage.removeItem('LoggingSaveButton');
                    that.removeSessionField();
                    that.removeModifyLogging();
                    $.ajax({
                        type: 'POST',
                        url: 'en/cliplog/index/updatetemplate/' + type + '/' + id,
                        dataType: 'json',
                        data: requestData,
                        cache: false,
                        async: true,
                        success: function (response) {
                            console.log(response);
                            formKeeper.hideReloadLayout();
                            if (response.status) {
                                titleBox.html(response.name);
                                that.alertBox('Saved');
                                // cliplog.formkeeper.js
                                formKeeper.reinit(type);
                                button.hide();
                                //KM.stateManager.createBasicState(); // Пересоздаем окружение кл. слов
                            }
                        }
                    });
                    //location.reload();
                }
            }
        },
        updateSelectedTemplate: function (e) { // Apply keywords button
            var that = this;
            var button = $(e.currentTarget);
            var form = $('#cliplog_form');
            var box = button.closest('.control-group');
            var type = box.attr('data-type');
            var select = box.find('select');
            var option = select.find('option:selected');
            var id = option.val();
            var data = form.serialize();
            var metadataButtonSave = $('.save_metadata_template');
            var metadataButtonApply = $('.apply_metadata_template');
            var metadataButtonDelete = $('.delete_metadata_template');
            var header = $('.cliplog_Keyword_header');
            var templateName = $('#applied_keywords_set_id').find('option:selected').text();
            if (id == '') {
                var applyMsg = 'You are about to Apply “Reset All Fields”. All fields will be dropped to default after you clicked Save.';
            } else {
                var applyMsg = 'You are about to Apply the Keyword Set “' + templateName + '”. Only keywords from this Keyword Set will be displayed on the page. These keywords will not be saved to your clip(s) until you click "Save Data" below, allowing you to review and modify the keywords to be saved. Fields with blank values will  not overwrite existing keywords. Checkbox fields will have keywords added to the existing values, so no keywords will be replaced.';
            }

            if (confirm(applyMsg)) {
                that.hiddenElement(metadataButtonSave);
                that.hiddenElement(metadataButtonApply);
                that.hiddenElement(metadataButtonDelete);
                // reset Modified and Save button on session
                sessionStorage.removeItem('KeywordsModified');
                sessionStorage.removeItem('KeywordsSaveButton');
                //header.html(templateName);
                //header.attr('data-Keyword-id',id);
                //sessionStorage['keywordTemplateId'] = id;
                that.setKeywordsSetHeader();
                that.selectNewTemplate(e);
            } else {
                $(e.currentTarget).find('option').removeAttr('selected');
                $(e.currentTarget).find('option[value="' + formKeeper.registeredSelectIds.keywords + '"]').attr('selected', true);
            }
        },
        saveSelectedTemplate: function (e) { // Save keywords button

            KM.__saveHiddenStateToForm("#cliplog_form");
            var that = this;
            var button = $(e.currentTarget);
            var form = $('#cliplog_form');
            var box = button.closest('.control-group');
            var type = box.attr('data-type');
            var select = box.find('select');
            var option = select.find('option:selected');
            var id = $('.cliplog_Keyword_header').attr('data-Keyword-id');
            var metadataButtonSave = $('.save_metadata_template');
            var metadataButtonApply = $('.apply_metadata_template');
            var metadataButtonDelete = $('.delete_metadata_template');
            if (button.attr('data-message') == 1) {
                // Отобразить лишь сообщение
                this.alertBox('The Default Keyword Template can not be modified. Please save the layout as a new keyword template.');
            } else {
                that.hiddenElement(metadataButtonSave);
                that.hiddenElement(metadataButtonApply);
                that.hiddenElement(metadataButtonDelete);
                // reset Modified and Save button on session
                sessionStorage.removeItem('KeywordsModified');
                sessionStorage.removeItem('KeywordsSaveButton');
                sessionStorage.removeItem('keywordTemplateId');
                that.setKeywordsSetHeader(2);


                if (type == 'metadata') {

                    var data = $("#cliplog_form").serializeArray();

                    var dataToPush = [];
                    $(".cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                        var data = $(this).find('.item').text();
                        var trimmedKeyword = $.trim(data);
                        var sectionId = $(this).closest('.cliplog_section').attr('id');

                        var obj = {
                            location: sectionId,
                            keyword: trimmedKeyword
                        };
                        dataToPush.push(obj);
                    })

                    data.push({name: 'newKeywordsData', value: JSON.stringify(dataToPush)});
                } else {

                    var data = form.serialize();
                }
                formKeeper.showReloadLayout();
                if (id && type && data) {
                    $.ajax({
                        type: 'POST',
                        url: 'en/cliplog/index/updatetemplate/' + type + '/' + id,
                        dataType: 'json',
                        data: data,
                        cache: false,
                        success: function (response) {
                            if (response.status) {
                                formKeeper.hideReloadLayout();
                                that.alertBox('Saved');
                                //that.setKeywordsSetHeader(2,select.find( 'option:first' ).text(),null); // Сбрасываем заголовок на Reset
                                //select.get(0).selectedIndex = 0; // Сбрасываем значение списка на Reset
                                that.hiddenElement(metadataButtonSave);
                                // cliplog.formkeeper.js
                                formKeeper.reinit(type);
                            }
                        }
                    });
                }
            }
        },
        dump: function (obj) {// Замена var_dump в php
            var out = '';
            for (var i in obj) {
                out += i + ": " + obj[i] + "\n";
            }

            alert(out);
        },
        updateTemplateBlocks: function () {
            var opacity = '.4';
            //var loggingField = $( '#applied_template_id' );
            var metadataField = $('#applied_keywords_set_id');
            //var loggingButtonSave = $( '.save_logging_template' );
            var metadataButtonSave = $('.save_metadata_template');
            //var loggingButtonDelete = $( '.delete_logging_template' );
            var metadataButtonDelete = $('.delete_metadata_template');
            /*
             if ( loggingField.find( 'option:selected' ).val() ) {
             loggingButtonSave.removeAttr( 'disabled' ).css( 'opacity', '1' );
             loggingButtonDelete.removeAttr( 'disabled' ).css( 'opacity', '1' );
             } else {
             loggingButtonSave.attr( 'disabled', 'disabled' ).css( 'opacity', opacity );
             loggingButtonDelete.attr( 'disabled', 'disabled' ).css( 'opacity', opacity );
             }
             */
            /*if ( metadataField.find( 'option:selected' ).val() ) {
             metadataButtonSave.removeAttr( 'disabled' ).css( 'opacity', '1' );
             metadataButtonDelete.removeAttr( 'disabled' ).css( 'opacity', '1' );
             } else {
             metadataButtonSave.attr( 'disabled', 'disabled' ).css( 'opacity', opacity );
             metadataButtonDelete.attr( 'disabled', 'disabled' ).css( 'opacity', opacity );
             }*/
        },
        changeNewKeywords: function (e) {
            this.newKeywordsBoxExpander(e);
            if (e && e.currentTarget && $(e.currentTarget).is('.expanded')) {
                this.addNewKeywords(e);
            }
        },
        addNewKeywords: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var section = button.closest('tr.cliplog_section');
            var sectionId;
            var keywordsList = section.find('.cliplog_keywords_list_new');
            if (section.length > 0 && (sectionId = section.attr('id'))) {
                if (keywordsList.length > 0) {
                    this.addNewSelectedKeywords(sectionId);
                } else {
                    this.showNewKeywordsList(sectionId);
                }
            }
        },
        addNewKeywordsToPopup: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var sectionId = button.attr('data-id');
            $('#dialog-keywords-manage .id').attr('data-id', sectionId);
            this.showNewKeywordsListToPopup(sectionId, true);
        },
        showNewKeywordsList: function (sectionID, showAll, onMatch) {
            var keywordPattern = '<label class="checkbox %visible%"><input type="checkbox" name="keyword-%id%" value="%keyword%" class="cliplog_keyword_checkbox">%keyword%</label>';
            var keywords;
            var keywordList = '';
            var selectedKeywordList = $('#' + sectionID).find('.cliplog_selected_keywords_list_new').first();
            var selectedKeywordsIDs = [];
            var collection = $('select[name="sections_values[brand]"]').val();
            if (selectedKeywordList.length > 0) {
                $.each(selectedKeywordList.find('input.getUserKeywordsForLogging:hidden:checked'), function () {
                    var lbkId = $(this).attr('datalb-k-id');
                    var keywordId = lbkId ? lbkId : $(this).val();
                    if (keywordId) {
                        selectedKeywordsIDs.push(keywordId)
                    }
                })
            }
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/keywords',
                dataType: 'json',
                data: ({
                    section: sectionID,
                    selected: selectedKeywordsIDs.join(','),
                    showall: showAll,
                    onmatch: onMatch,
                    collection: collection
                }),
                cache: false,
                success: function (data) {
                    $('#' + sectionID).find('.cliplog_keywords_list_new').remove();
                    keywords = data.keywords;
                    if (keywords.length > 0) {
                        showAll = (showAll) ? 'show_all_list' : '';
                        keywordList += '<div class="cliplog_keywords_list_new ' + showAll + '">';
                        $.each(keywords, function (index, value) {
                            keywordList += keywordPattern.replace(/(%[a-z]{2,7}%)/gi, function (word) {
                                switch (word) {
                                    case '%id%':
                                        return value.id;
                                    case '%keyword%':
                                        return value.keyword;
                                    case '%visible%':
                                        return (!Number(value.visible)) ? 'red' : '';
                                }
                                return undefined;
                            });
                        });
                        keywordList += '</div>';
                    }
                    keywordList = $(keywordList).appendTo($('#' + sectionID).find('.top').first());
                }
            });
        },
        showNewKeywordsListToPopup: function (sectionID, showAll, onMatch) {
            var that = this;
            var keywordPattern = '<div class="item" data-id="%id%"><label class="checkbox">' +
                '<input type="hidden" name="keyword-%id%" value="%keyword%" class="cliplog_keyword_checkbox">%keyword%' +
                '</label><div class="switch-cont"><div class="switch" data-animated="false" data-on-label="" data-off-label="">' +
                '<input type="checkbox" %visible% value="%id%"/></div></div>%delete%</div>';
            var deletePattern = '<a href="#" class="popup_delete_keyword" id="delete_keyword-%id%">' +
                '<img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a>';
            var keywords;
            var keywordList = '';
            var collection = $('select[name="sections_values[brand]"]').val();
            $('#dialog-keywords-manage').find('.title').html('Loading...');
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/keywords',
                dataType: 'json',
                data: ({section: sectionID, selected: '', showall: showAll, onmatch: onMatch, collection: collection}),
                cache: false,
                success: function (data) {
                    $('.dialog-keyword-list').find('.item').remove();
                    var title = $('#' + sectionID).find('.field_label').first().html();
                    title = title.replace(/^[\s]{1,}/im, '').replace(/[\s]{1,}$/im, '').replace(':', '');
                    $('#dialog-keywords-manage').find('.title').html('Manage Keyword List: ' + title);
                    keywords = data.keywords;
                    console.log(keywords);
                    if (keywords.length > 0) {
                        $.each(keywords, function (index, value) {
                            var deleteButton = '';
                            if (value.provider_id != 0 || data.is_admin == 1) {
                                if (value.basic == 0) {
                                    deleteButton = deletePattern.replace(/(%[a-z]{2,10}%)/gi, function (word) {
                                        switch (word) {
                                            case '%id%':
                                                return value.id;
                                        }
                                        return '';
                                    });
                                }
                            }
                            keywordList += keywordPattern.replace(/(%[a-z]{2,10}%)/gi, function (word) {
                                switch (word) {
                                    case '%id%':
                                        return value.id;
                                    case '%keyword%':
                                        return value.keyword;
                                    case '%visible%':
                                        return (!value.visible) ? 'checked' : '';
                                    case '%delete%':
                                        return deleteButton;
                                }
                                return '';
                            });
                        });
                    }
                    /*
                     keywordList = $( keywordList ).appendTo( $( '.dialog-keyword-list' ) ).parent().parent().jScrollPane();
                     */

                    keywordList = $(keywordList).appendTo($('.dialog-keyword-list'));
                    keywordList.find('.switch')['bootstrapSwitch']();
                    keywordList.find('.switch').on('switch-change', function (e, data) {
                        var el = $(data.el);
                        var value = data.value;
                        var keywordID = el.val();
                        if (value == false && keywordID) {
                            that.switchOffPopupKeyword(keywordID, sectionID);
                        } else if (value == true && keywordID) {
                            that.switchOnPopupKeyword(keywordID, sectionID);
                        }
                    });
                }
            });
        },
        addNewSelectedKeywords: function (sectionID) {
            var section = $('#' + sectionID);
            var selectedKeywordList = section.find('.cliplog_selected_keywords_list_new').first();
            var keywordList = section.find('.cliplog_keywords_list_new').first();
            var selectedKeywordPattern = '<div class="item-wrapper"><a class="item-cross"></a><div class="item"><input type="hidden" checked="checked" value="%id%" name="keywords[%id%]">%keyword%</div></div>';
            if (selectedKeywordList.length == 0) {
                selectedKeywordList = $('<div class="cliplog_selected_keywords_list_new"></div>').appendTo($('#' + sectionID).find('.bottom').first());
            }
            var commonSelectedKeywordList = $('#cliplog').find('.cliplog_commont_selected_keywords_list').first();
            if (commonSelectedKeywordList.length == 0) {
                var commonSelectedKeywordListCont = $('<div class="control-group selected-items"><label class="control-label">Keywords Added:</label></div>').appendTo($('#cliplog_sidebar_content'));
                commonSelectedKeywordList = $('<div class="cliplog_commont_selected_keywords_list"></div>').appendTo(commonSelectedKeywordListCont);
            }
            if (keywordList.length > 0) {
                var selectedKeywordListHtml = '';
                var commonSelectedKeywordListHtml = '';
                $.each(keywordList.find('input.cliplog_keyword_checkbox:checked'), function () {
                    var keywordIDArr = $(this).attr('name').split('-');
                    var keywordID = keywordIDArr[1];
                    var keywordName = $(this).val();
                    selectedKeywordListHtml += selectedKeywordPattern.replace(/(%[a-z]{2,7}%)/gi, function (word) {
                        switch (word) {
                            case '%id%':
                                return keywordID;
                            case '%keyword%':
                                return keywordName;
                        }
                        return undefined;
                    });
                    commonSelectedKeywordListHtml += '<span>' + keywordName + ' <a href="#" class="cliplog_remove_keyword" id="remove_keyword-' + keywordID + '"><img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a></span>';
                    //$( this ).parent().remove();
                });
                $(selectedKeywordListHtml).appendTo(selectedKeywordList);
                $(commonSelectedKeywordListHtml).appendTo(commonSelectedKeywordList);
            }
        },
        newKeywordsBoxExpander: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var box = button.closest('.new-section');
            if (box.is('.collapsed')) {
                box.removeClass('collapsed').addClass('expanded');
                button.removeClass('collapsed').addClass('expanded').html('Hide list');
            } else {
                box.removeClass('expanded').addClass('collapsed');
                button.removeClass('expanded').addClass('collapsed').html('Show list');
            }
        },
        deselectNewSelectedKeyword: function (e) {
            var cross = $(e.currentTarget);
            var keywordID = cross.parent().find('input:hidden').val();
            if (keywordID) {
                this.removeNewSelectedKeyword(keywordID);
                $('#remove_keyword-' + keywordID).parent().remove();
            }
        },
        removeNewSelectedKeyword: function (keywordID) {
            var selectedKeywords = $('.cliplog_selected_keywords_list_new input[value="' + keywordID + '"]');
            var that = this;
            if (selectedKeywords.length > 0) {
                $.each(selectedKeywords, function () {
                    var section = $(this).closest('.cliplog_section');
                    var keywordsList = section.find('.cliplog_keywords_list_new').first();
                    var sectionID = section.attr('id');
                    //$( this ).parent().parent().remove();
                    //if ( keywordsList.length > 0 ) {
                    //that.showNewKeywordsList( sectionID );
                    //}
                })
            }
        },
        addNewClickedKeyword: function (e) {
            var checkbox = $(e.currentTarget);
            var section = checkbox.closest('tr.cliplog_section');
            var sectionID = section.attr('id');
            this.addNewSelectedKeywords(sectionID);
        },
        addNewKeywordToList: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var section = button.closest('.cliplog_section');
            var sectionID = section.attr('id');
            var keywordInput = section.find('.cliplog_keyword_input');
            var collection = $('select[name="sections_values[collection]"]').val();
            if (section.length > 0) {
                if (keywordInput.length > 0 && keywordInput.val()) {
                    this.saveNewKeyword(keywordInput.val(), sectionID, collection);
                }
            }
        },
        addNewKeywordToPopupList: function (e) {
            e && e.preventDefault();
            //var button = $( e.currentTarget );
            //var section = button.closest( '.cliplog_section' );
            //var sectionID = section.attr( 'id' );
            var section = $('#dialog-keywords-manage');
            var sectionID = section.find('.id').attr('data-id');
            var keywordInput = section.find('.cliplog_keyword_input');
            var collection = $('select[name="sections_values[collection]"]').val();
            this.saveNewPopupKeyword(keywordInput.val(), sectionID, collection, 0);
        },
        saveNewPopupKeyword: function (keyword, sectionID, collection, addToSelected) {
            var that = this;
            var keywordInput;
            if (keyword && sectionID) {
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savekeyword',
                    dataType: 'json',
                    data: ({keyword: keyword, section: sectionID, collection: collection}),
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.keyword_id) {
                            keywordInput = $('#dialog-keywords-manage').find('.cliplog_keyword_input');
                            if (keywordInput.length > 0) {
                                keywordInput.val('');
                            }
                            that.showNewKeywordsList(sectionID);
                            that.showNewKeywordsListToPopup(sectionID, true);
                        }
                    }
                });
            }
        },
        saveNewKeyword: function (keyword, sectionID, collection, addToSelected) {
            var that = this;
            var keywordInput;
            if (keyword && sectionID) {
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savekeyword',
                    dataType: 'json',
                    data: ({keyword: keyword, section: sectionID, collection: collection}),
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.keyword_id) {
                            keywordInput = $('#' + sectionID).find('.cliplog_keyword_input');
                            if (keywordInput.length > 0) {
                                keywordInput.val('');
                            }
                            that.addNewKeywordToSelectedByID(data.keyword_id, keyword, sectionID);
                            that.showNewKeywordsList(sectionID);
                        }
                    }
                });
            }
        },
        addNewKeywordToSelectedByID: function (keywordID, keyword, sectionID) {
            var selectedKeywordList = $('#' + sectionID).find('.cliplog_selected_keywords_list_new').first();
            var selectedKeywordPattern = '<div class="item-wrapper"><a class="item-cross"></a><div class="item"><input type="hidden" checked="checked" value="%id%" name="keywords[%id%]">%keyword%</div></div>';
            if (selectedKeywordList.length == 0) {
                selectedKeywordList = $('<div class="cliplog_selected_keywords_list_new"></div>').appendTo($('#' + sectionID).find('.bottom').first());
            }
            var commonSelectedKeywordList = $('#cliplog').find('.cliplog_commont_selected_keywords_list').first();
            if (commonSelectedKeywordList.length == 0) {
                var commonSelectedKeywordListCont = $('<div class="control-group selected-items"><label class="control-label">Keywords Added:</label></div>').appendTo($('#cliplog_sidebar_content'));
                commonSelectedKeywordList = $('<div class="cliplog_commont_selected_keywords_list"></div>').appendTo(commonSelectedKeywordListCont);
            }
            if (keywordID && keyword) {
                var selectedKeywordListHtml = '';
                var commonSelectedKeywordListHtml = '';
                selectedKeywordListHtml += selectedKeywordPattern.replace(/(%[a-z]{2,7}%)/gi, function (word) {
                    switch (word) {
                        case '%id%':
                            return keywordID;
                        case '%keyword%':
                            return keyword;
                    }
                    return undefined;
                });
                commonSelectedKeywordListHtml += '<span>' + keyword + ' <a href="#" class="cliplog_remove_keyword" id="remove_keyword-' + keywordID + '"><img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a></span>';
                $(selectedKeywordListHtml).appendTo(selectedKeywordList);
                $(commonSelectedKeywordListHtml).appendTo(commonSelectedKeywordList);
            }
        },
        showNewAllKeywords: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var section = button.closest('.cliplog_section');
            var sectionID = section.attr('id');
            if (section.length > 0) {
                this.showNewKeywordsList(sectionID, true);
                button.html('Hide Extras').removeClass('cliplog_show_all_keywords_new').addClass('cliplog_hide_extras_keywords_new');
            }
            return false;
        },
        hideNewExtrasKeywords: function (e) {
            e && e.preventDefault();
            var button = $(e.currentTarget);
            var section = button.closest('.cliplog_section');
            var sectionID = section.attr('id');
            if (section.length > 0) {
                this.showNewKeywordsList(sectionID);
                button.html('Show All').removeClass('cliplog_hide_extras_keywords_new').addClass('cliplog_show_all_keywords_new');
            }
            return false;
        },
        switchOffPopupKeyword: function (keywordID, sectionID) {
            var that = this;
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/switchoffkeyword',
                dataType: 'json',
                data: ({keyword: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1)
                        that.showNewKeywordsList(sectionID)
                }
            });
        },
        switchOnPopupKeyword: function (keywordID, sectionID) {
            var that = this;
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/switchonkeyword',
                dataType: 'json',
                data: ({keyword: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1)
                        that.showNewKeywordsList(sectionID);
                }
            });
        },
        onDeletePopupKeywordClick: function (e) {
            e && e.preventDefault();
            var link = $(e.currentTarget);
            var idArr = link.attr('id').split('-');
            var keywordID = idArr[1];
            var section = link.closest('.cliplog_section');
            var sectionID = section.attr('id');
            if (keywordID) {
                this.deletePopupKeyword(keywordID, sectionID);
            }
        },
        deletePopupKeyword: function (keywordID, sectionID) {
            var that = this;
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/deletekeywords',
                dataType: 'json',
                data: ({id: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1) {
                        that.showNewKeywordsList(sectionID);
                        $('#dialog-keywords-manage').find('.item[data-id="' + keywordID + '"]').remove();
                    }
                }
            });
        },
        changeDeliveryCategory: function ($e) {
            if ($e && $e.currentTarget) {
                $.ajax({
                    type: 'POST',
                    url: 'en/codecrelations/ajax_submission_to_delivery',
                    dataType: 'json',
                    cache: false,
                    success: function ($r) {
                        if ($r && $r.status) {
                            //noinspection JSUnresolvedVariable
                            var $relations = $r.relations;
                            var $submission = $($e.currentTarget);
                            var $submission_value = $submission.find('option:selected').val();
                            var $delivery = $('#select_delivery_category');
                            var $delivery_value = $relations[$submission_value];
                            if ($delivery_value !== '' && typeof $delivery_value !== 'undefined') {
                                var $o = $delivery.find('option[value="' + $delivery_value + '"]')
                                if ($o) {
                                    $($o).first().attr('selected', 'yes');
                                }
                            }
                        }
                    }
                });
            }
        },
        changeSecondaryCollection: function (e) {
            var selectValue = $(e.currentTarget).val(),
                collection3d = $('#add_collection .cliplog_options_list input[value="3D Footage"]'),
                collectionUltraHd = $('#add_collection .cliplog_options_list input[value="Ultra HD Footage"]');
            if (selectValue.indexOf('3D') != -1 && collection3d.length > 0) {
                collection3d.trigger('click');
            }

            if (selectValue.indexOf('Ultra HD') != -1 && collectionUltraHd.length > 0) {
                collectionUltraHd.trigger('click');
            }
        },
        changeKeywords: function (e) {
            if (e && e.currentTarget && $(e.currentTarget).is('.collapsed')) {
                this.addKeywords(e);
            } else {
                this.closeKeywords(e);
            }
        },
        addKeywords: function (e) {
            e && e.preventDefault()
            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID
                , keywordsList
                , keywordInputControl

            if (section.length > 0) {
                sectionID = section.attr('id')
                keywordsList = section.find('.cliplog_keywords_list')
                keywordInputControl = section.find('.cliplog_keywords_input_control')
                if (keywordInputControl.length > 0) {
                    keywordInputControl.show();
                }
                if (keywordsList.length > 0) {
                    this.addSelectedKeywords(sectionID)
                }
                else {
                    button.addClass('expanded');
                    button.removeClass('collapsed');
                    this.showKeywordsList(sectionID)
                }
            }
        },
        closeKeywords: function (e) {
            e && e.preventDefault()
            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , keywordsList
                , keywordInputControl
                , addButton = section.find('.cliplog_add_keywords').first();
            keywordInputControl = section.find('.cliplog_keywords_input_control')
            if (keywordInputControl.length > 0) {
                keywordInputControl.hide();
            }

            if (section.length > 0) {
                keywordsList = section.find('.cliplog_keywords_list')
                if (keywordsList.length > 0) {
                    keywordsList.remove()
                    //button.hide()
                    button.removeClass('expanded');
                    button.addClass('collapsed');
                }
            }

        },
        reloadKeywordsLists: function () {
            var that = this
                , lists = $('.cliplog_keywords_list');
            lists.each(function () {
                var section = $(this).closest('.cliplog_section')
                    , sectionID = section.attr('id')

                $(this).remove();
                that.showKeywordsList(sectionID);
            });
        },
        showKeywordsList: function (sectionID, showAll, onMatch) {
            var that = this
                , keywords
                , keywordList = ''
                , selectedKeywordList = $('#' + sectionID).find('.cliplog_selected_keywords_list').first()
                , closeKeywordsBtn = $('#' + sectionID).find('.' + this.options.closeKeywordsClass).first()
                , selectedKeywordsIDs = []
                , collection = $('select[name="sections_values[brand]"]').val()

            if (selectedKeywordList.length > 0) {
                $.each(selectedKeywordList.find('input:checkbox:checked'), function () {
                    selectedKeywordsIDs.push($(this).val())
                })
            }

            $.ajax({
                type: 'POST',
                url: 'en/cliplog/keywords',
                dataType: 'json',
                data: ({
                    section: sectionID,
                    selected: selectedKeywordsIDs.join(','),
                    showall: showAll,
                    onmatch: onMatch,
                    collection: collection
                }),
                cache: false,
                success: function (data) {
                    keywords = data.keywords;
                    $('#' + sectionID).find('.cliplog_keywords_list').remove();
                    keywordList += '<div class="cliplog_keywords_list';
                    if (showAll)
                        keywordList += ' show_all_list';
                    keywordList += '">';
                    if (keywords.length > 0) {
                        var oneColumnLength = Math.ceil(keywords.length / 2)

                        keywordList += '<div class="column1"><table>';
                        $.each(keywords, function (index, value) {
                            keywordList += '<tr class="draggable_keyword"><td><label class="checkbox">' +
                                '<input type="checkbox" name="keyword-' + value.id + '" value="' + value.keyword + '" class="cliplog_keyword_checkbox"> ' + value.keyword +
                                '</label></td><td><div class="switch-cont">' +
                                '<div class="switch" data-animated="false" data-on-label="" data-off-label="">' +
                                '<input type="checkbox" ' + (!value.visible ? 'checked' : '') + ' value="' + value.id + '" /></div></div></td>';
                            if (value.provider_id != 0 || data.is_admin == 1) {
                                if (value.basic == 0) {
                                    keywordList += '<td><a href="#" class="cliplog_delete_keyword" id="delete_keyword-' + value.id + '"><img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a></td>';
                                } else {
                                    keywordList += '<td>&nbsp;</td>';
                                }
                            }

                            keywordList += '</tr>';
                            if (index + 1 == oneColumnLength)
                                keywordList += '</table></div><div class="column2"><table>';
                        });
                        if (showAll)
                            keywordList += '</table><a href="#" class="cliplog_hide_extras_keywords">Hide Extras</a></div>';
                        else
                            keywordList += '</table><a href="#" class="cliplog_show_all_keywords">Show All</a></div>';
                    }
                    keywordList += '</div>';
                    keywordList = $(keywordList).appendTo($('#' + sectionID).find('.right').first());
                    keywordList.find('.switch')['bootstrapSwitch']();
                    keywordList.find('.switch').on('switch-change', function (e, data) {
                        var $el = $(data.el)
                            , value = data.value
                            , keywordID = $el.val()
                        if (value == false && keywordID)
                            that.switchOffKeyword(keywordID, sectionID)
                        else if (value == true && keywordID)
                            that.switchOnKeyword(keywordID, sectionID)
                    });
                    $(".draggable_keyword").draggable({
                        revert: "invalid",
                        helper: "clone",
                        cursor: "move"
                    });
                    $('#' + sectionID).find('.left').droppable({
                        drop: function (event, ui) {
                            //that.deleteImage(ui.draggable);
                            //addKeywordToSelectedByID
                            var draggableCheckbox = ui.draggable.find('input.cliplog_keyword_checkbox').first()
                            if (draggableCheckbox.length > 0) {
                                var keywordIDArr = draggableCheckbox.attr('name').split('-')
                                var keywordID = keywordIDArr[1]
                                that.addKeywordToSelectedByID(keywordID, draggableCheckbox.val(), sectionID)
                                ui.draggable.remove();
                            }
                        }
                    });
                    closeKeywordsBtn.show();
                }
            });
        },
        addKeywordToSelected: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID
                , keywordInput
                , collection

            if (section.length > 0) {
                sectionID = section.attr('id')
                keywordInput = section.find('.cliplog_keyword_input')
                collection = $('select[name="sections_values[collection]"]').val();
                if (keywordInput.length > 0 && keywordInput.val()) {
                    this.saveKeyword(keywordInput.val(), sectionID, collection, true)
                }
            }
        },
        addKeywordToSelectedByID: function (keywordID, keyword, sectionID) {
            var selectedKeywordList = $('#' + sectionID).find('.cliplog_selected_keywords_list').first()
            if (selectedKeywordList.length == 0)
                selectedKeywordList = $('<div class="cliplog_selected_keywords_list"></div>').appendTo($('#' + sectionID).find('.left').first());
            var commonSelectedKeywordList = $('#cliplog').find('.cliplog_commont_selected_keywords_list').first()
            if (commonSelectedKeywordList.length == 0) {
                var commonSelectedKeywordListCont = $('<div class="control-group selected-items"><label class="control-label">Keywords Added:</label></div>').appendTo($('#cliplog_sidebar_content'));
                commonSelectedKeywordList = $('<div class="cliplog_commont_selected_keywords_list"></div>').appendTo(commonSelectedKeywordListCont);
            }

            if (keywordID && keyword) {
                var selectedKeywordListHtml = '';
                var commonSelectedKeywordListHtml = '';
                selectedKeywordListHtml += '<label class="checkbox"><input type="checkbox" name="keywords[' + keywordID + ']" value="' + keywordID + '" checked="checked"> ' + keyword + '</label>';
                commonSelectedKeywordListHtml += '<span>' + keyword + ' <a href="#" class="cliplog_remove_keyword" id="remove_keyword-' + keywordID + '"><img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a></span>';
                $(selectedKeywordListHtml).appendTo(selectedKeywordList)
                $(commonSelectedKeywordListHtml).appendTo(commonSelectedKeywordList)
            }
        },
        switchOffKeyword: function (keywordID, sectionID) {
            var that = this
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/switchoffkeyword',
                dataType: 'json',
                data: ({keyword: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1)
                        that.showKeywordsList(sectionID)
                }
            });
        },
        switchOnKeyword: function (keywordID, sectionID) {
            var that = this
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/switchonkeyword',
                dataType: 'json',
                data: ({keyword: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1)
                        that.showKeywordsList(sectionID)
                }
            });
        },
        showAllKeywords: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID

            if (section.length > 0) {
                sectionID = section.attr('id')
                this.showKeywordsList(sectionID, true)
            }
            return false;
        },
        hideExtrasKeywords: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID

            if (section.length > 0) {
                sectionID = section.attr('id')
                this.showKeywordsList(sectionID)
            }
            return false;
        },
        addSectionOptions: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID
                , sectionOptionsList

            if (section.length > 0) {
                sectionID = section.attr('id')
                sectionOptionsList = section.find('.cliplog_options_list')
                if (sectionOptionsList.length > 0) {
                    this.addSelectedSectionOptions(sectionID)
                }
                else {
                    this.showSectionOptionsList(sectionID)
                    button.addClass('expanded')
                }
            }
        },
        addFormats: function (e) {
            var that = this;
            if (e.type == 'click')
                e && e.preventDefault()

            var button = $(e.currentTarget != undefined) ? e.currentTarget : e;
            //, section = $(e.currentTarget != undefined) ? $(button).closest('.cliplog_section') : 'object';
            var ID = 'Add_formats';
            // Click
            /*if($('#'+ID).attr('checked') && e.type == 'click'){
             $('#'+ID).prop('checked', false);
             that.switchSessionField(ID, 'expanded');
             that.hackLoggingModified();
             }else if(e.type == 'click'){
             $('#'+ID).prop('checked', true);
             that.switchSessionField(ID, 'collapsed');
             that.hackLoggingModified();
             }
             // Init
             if(sessionStorage["cliplog_switch_field_"+ID] == 'collapsed' && e.type == undefined){
             $('#'+ID).prop('checked', true);
             that.hackLoggingModified();
             //this.switchSessionField(ID, 'collapsed');
             }else if(sessionStorage["cliplog_switch_field_"+ID] == 'expanded' && e.type == undefined){
             $('#'+ID).prop('checked', false);
             that.hackLoggingModified();
             //this.switchSessionField(ID, 'expanded');
             }/*else if(e.type == undefined){
             var initAddFormat = ($('#'+ID).attr('checked')) ? 'collapsed' : 'expanded';
             //that.switchSessionField(ID, initAddFormat);
             }*/
            console.log('checked:' + $('#' + ID).attr('checked') + ' switch:' + sessionStorage["cliplog_switch_field_" + ID]);
            // Check box Add_format
            var ele = $('#' + ID);
            if ($('#' + ID + ':checked').length) {
                ele.prop('checked', false);
            } else {
                ele.prop('checked', true);
            }
            that.hackLoggingModified();
            //if(section.length > 0){
            var formats = $('#file_formats').find('tr');
            formats.each(function () {
                //if(/*$(this).hasClass('collapsed') ||*/ sessionStorage["cliplog_switch_field_"+ID] == 'expanded'){
                if ($(this).hasClass('collapsed')) {
                    $(this).removeClass('collapsed');
                    $(this).addClass('expanded');
                    console.log($(this).attr('tag') + '  expanded');
                }
                //else if(/*$(this).hasClass('expanded') ||*/ sessionStorage["cliplog_switch_field_"+ID] == 'collapsed'){
                else if ($(this).hasClass('expanded')) {
                    $(this).removeClass('expanded');
                    $(this).addClass('collapsed');
                    console.log($(this).attr('tag') + '  collapsed');
                }
            });
            $(button).toggleClass('collapsed');
            $(button).toggleClass('expanded');
            //}
        },
        switchSessionField: function (ID, triger) {
            sessionStorage["cliplog_switch_field_" + ID] = triger;
        },
        initSessionField: function () {
            var that = this;
            //if (!_.isUndefined(sessionStorage["cliplog_switch_field_Add_formats"]) ) $('#Add_formats' ).prop('checked',false);
            //that.addFormats($('button.cliplog_add_formats'));
            //$('button.cliplog_add_formats' ).click();
        },
        removeSessionField: function () {
            delete sessionStorage['cliplog_switch_field_Add_formats'];
        },
        addSelectedSectionOptions: function (sectionID) {
            var selectedOptionsList = $('#' + sectionID).find('.cliplog_selected_options_list').first()
                , optionsList = $('#' + sectionID).find('.cliplog_options_list').first()
            if (selectedOptionsList.length == 0)
                selectedOptionsList = $('<div class="cliplog_selected_options_list"></div>').appendTo($('#' + sectionID).find('.left').first());
            if (optionsList.length > 0) {
                var selectedOptionsListHtml = '';
                $.each(optionsList.find('input:checkbox:checked'), function () {
                    var optionIDArr = $(this).attr('name').split('-');
                    var optionID = optionIDArr[1];
                    selectedOptionsListHtml += '<label class="checkbox"><input type="checkbox" name="sections_values[' + sectionID + '][]" value="' + optionID + '" checked="checked"> ' + $(this).val() + '</label>';
                    $(this).parent().remove()
                    selectedOptionsListHtml += '<label class="checkbox"><input type="checkbox" name="sections_values[' + sectionID + '][]" value="' + optionID + '" checked="checked"> ' + ' ' + $(this).parent().text() + '</label>';
                    //$(this).parent().remove()
                })
                $(selectedOptionsListHtml).appendTo(selectedOptionsList)
                //$(selectedOptionsListHtml).appendTo(selectedOptionsList)
            }
        },
        showSectionOptionsList: function (sectionID) {

            var that = this
                , options
                , optionsList = ''
                , selectedOptionsList = $('#' + sectionID).find('.cliplog_selected_options_list').first()
                , closeOptionsBtn = $('#' + sectionID).find('.' + this.options.closeSectionOptionsClass).first()
                , selectedOptionsIDs = []

            if (selectedOptionsList.length > 0) {
                $.each(selectedOptionsList.find('input:checkbox:checked'), function () {
                    selectedOptionsIDs.push($(this).val())
                })
            }

            $.ajax({
                type: 'POST',
                url: 'en/cliplog/sectionoptions',
                dataType: 'json',
                data: ({section: sectionID, selected: selectedOptionsIDs.join(',')}),
                cache: false,
                success: function (data) {
                    options = data;
                    $('#' + sectionID).find('.cliplog_options_list').remove();
                    optionsList += '<div class="cliplog_options_list">';
                    if (options.length > 0) {
                        $.each(options, function (index, value) {
                            optionsList += '<label class="checkbox">' +
                                '<input type="checkbox" name="option-' + value.id + '" value="' + value.value + '"> '
                                + value.value + '</label>';
                            if (value.search_term) {
                                optionsList += '<label class="checkbox">' +
                                    '<input type="checkbox" name="option-' + value.id + '" value="' + value.value + '"> '
                                    + value.search_term + '</label>';
                            }
                        });
                    }
                    optionsList += '</div>';
                    optionsList = $(optionsList).appendTo($('#' + sectionID).find('.right').first());
                    closeOptionsBtn.show();
                }
            });
        },
        closeSectionOptions: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , optionsList
                , addButton = section.find('.cliplog_add_section_options').first()

            if (section.length > 0) {
                optionsList = section.find('.cliplog_options_list')
                if (optionsList.length > 0) {
                    optionsList.remove()
                    //button.hide()
                    addButton.removeClass('expanded');
                }
            }
        },
        saveTemplate: function (e) {
            e && e.preventDefault()
            var that = this
                , templateNameField = $('.cliplog_template_name').first()
                , templateName = templateNameField.val()
                , templateHeader = $('.cliplog_template_header').first()
            console.log($("#cliplog_form").serialize());
            if (templateName) {
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savetemplate',
                    dataType: 'json',
                    data: $("#cliplog_form").serialize(),
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.template.id) {
                            templateNameField.val('');
                            templateHeader.html(data.template.name)
                            that.getTemplatesList(data.template.id)
                            that.alertBox('Saved');
                            // cliplog.formkeeper.js
                            formKeeper.reinit('');
                        }
                    }
                });
            }
            else
                that.alertBox('Enter template name');
            return false;
        },
        createLoggingTemplate: function (e) {
            e && e.preventDefault();
            var that = this;
            var templateNameField = $('.cliplog_template_name').first();
            var templateName = templateNameField.val();
            var templateHeader = $('.header-template-name');
            var templateHeaderTitle = templateHeader.find('span').first();
            if (templateName) {



//                var dataUsers = [];
//                $('.getUserKeywordsForLogging').each(function() {
//
//                    var id = $(this).attr("value");
//                    dataUsers.push(id);
//                })


                this.resetSectionsSessionConfig();
                var requestData = $("#cliplog_form").serializeArray();
                console.log(requestData);
                requestData.push({name: 'keywords_sections_visible', value: this.getExpandedKeywordSections()});
                requestData.push({name: 'keywords_sections_hide_lists', value: this.getKeywordSectionsHideList()});
                // Передаем состояние кл.слов формы
                $.each(KM.stateManager.getKeywordsState(), function (index, object) {
                    // requestData.push({name: 'keywordList[]', value: JSON.stringify(object, null, 2)});
                });
                // requestData.push(dataUsers);
                // requestData.push(data);
                // $.each(KM.stateManager.getKeywordsState(), function (index, object) {
                //     requestData.push({name: 'keywordListImran[]', value: JSON.stringify(object, null, 2)});
                // });
                var data = [];
                $('.cliplog_keyword_checkbox').each(function () {
                    var id = $(this).attr("name");
                    requestData.push({name: 'keywordListMyList[]', value: id});
                })


                var dataToPush = [];
                $(".cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                    var data = $(this).find('.item').text();
                    var trimmedKeyword = $.trim(data);
                    var sectionId = $(this).closest('.cliplog_section').attr('id');

                    var obj = {
                        section: sectionId,
                        keyword: trimmedKeyword
                    };
                    dataToPush.push(obj);
                })

                requestData.push({name: 'assignedButActive', value: JSON.stringify(dataToPush)});


                formKeeper.showReloadLayout();
                //console.log('requestData:');
                //console.log(requestData);
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savetemplate',
                    dataType: 'json',
                    data: requestData,
                    cache: false,
                    success: function (data) {
                        formKeeper.hideReloadLayout();
                        if (data.status == 1 && data.template.id) {
                            templateNameField.val('');
                            templateHeader.attr('data-template-id', data.template.id);
                            templateHeaderTitle.html(data.template.name);
                            that.getTemplatesList(data.template.id);
                            that.removeSessionField();
                            that.removeModifyLogging();
                            that.alertBox('Saved');
                            // cliplog.formkeeper.js
                            formKeeper.reinit('logging');
                            KM.stateManager.createBasicState(); // Пересоздаем окружение кл.слов
                        }
                    }
                });
            }
            else
                that.alertBox('Enter template name');
            return false;
        },
        getTemplatesList: function (seleted) {
            var that = this;
            var templatesListHtml = '<option selected disabled>- Select Template -</option><option value="">Default Template</option>'
                , templatesList = $('.cliplog_templates_list');
            if (templatesList.length > 0)
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/gettemplates',
                    dataType: 'json',
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.templates) {
                            $.each(data.templates, function (index, value) {
                                templatesListHtml += '<option value="' + value.id + '" ';
                                templatesListHtml += '>&nbsp;&nbsp;' + value.name + '</option>';
                            });
                            templatesList.html(templatesListHtml)
                            that.showOrHideApplyLoggingButton();
                            that.showOrHideDeleteLoggingButton();
                        }
                    }
                });
        },
        applyTemplate: function (e) {
            e && e.preventDefault();
            var that = this
                , templateNameField = $('.cliplog_template_name').first()
                , templateName = templateNameField.val()
                , templateHeader = $('.cliplog_template_header').first()
            if (templateName) {
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savetemplate',
                    dataType: 'json',
                    data: $("#cliplog_form").serialize(),
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.template.id) {
                            templateNameField.val('');
                            templateHeader.html(data.template.name);
                            that.getTemplatesList(data.template.id);
                            that.alertBox('Saved');
                            // cliplog.formkeeper.js
                            formKeeper.reinit('');
                        }
                    }
                });
            }
            else
                that.alertBox('Enter template name');
            return false;
        },
        saveKeywordsSet: function (e) {
            e && e.preventDefault()

            var that = this
                , keywordsSetNameField = $('.cliplog_keywords_set_name').first()
                , keywordsSetName = keywordsSetNameField.val()
            if (keywordsSetName) {
                //that.setKeywordsSetHeader(0,key)


                var requestData = $("#cliplog_form").serializeArray();

                var dataToPush = [];
                $(".cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                    var data = $(this).find('.item').text();
                    var trimmedKeyword = $.trim(data);
                    var sectionId = $(this).closest('.cliplog_section').attr('id');

                    var obj = {
                        location: sectionId,
                        keyword: trimmedKeyword
                    };
                    dataToPush.push(obj);
                })

                requestData.push({name: 'newKeywordsData', value: JSON.stringify(dataToPush)});

                //  requestData.push(dataToPush);

                // console.log(requestData);
                //return false;


                formKeeper.showReloadLayout();
                KM.__saveStateToForm("#cliplog_form");
                KM.__saveHiddenStateToForm("#cliplog_form");
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savekeywordsset',
                    dataType: 'json',
                    data: requestData,
                    cache: false,
                    async: true,
                    success: function (data) {
                        if (data.status == 1) {
                            keywordsSetNameField.val('');
                            //that.dump(data.keywords_set);
                            //that.getkeywordsSetList(data.keywords_set.id);
                            that.setKeywordsSetHeader(0, keywordsSetName, data.keywords_set.id);
                            that.getkeywordsSetList();
                            that.hiddenElement($('.save_metadata_template'));
                            formKeeper.hideReloadLayout();
                            that.alertBox('Saved');
                            // cliplog.formkeeper.js
                            formKeeper.reinit('keywords');
                            var userid = KM.stateManager.getUserId();

                            setTimeout(function () {
                                if (userid) {
                                    that.applyKeywordsSetReturn(userid);
                                }
                            }, 3000);

                        }
                    }
                });
            }
            else
                that.alertBox('Enter keywords set name');
            return false;
        },
        getkeywordsSetList: function (seleted) {
            var keywordsSetsListHtml = '<option value="">Reset All Fields</option>'
                , keywordsSetsList = $('.cliplog_keywords_sets_list')

            if (keywordsSetsList.length > 0)
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/getkeywordssets',
                    dataType: 'json',
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.keywords_sets) {
                            $.each(data.keywords_sets, function (index, value) {
                                keywordsSetsListHtml += '<option value="' + value.id + '" '
                                if (seleted && seleted == value.id)
                                    keywordsSetsListHtml += 'selected '
                                keywordsSetsListHtml += '>' + value.name + '</option>'
                            })
                            keywordsSetsList.html(keywordsSetsListHtml)
                        }
                    }
                });
        },
        applyKeywordsSetReturn: function (userid) {
            $('.cliplog_selected_keywords_list_new').html('');
            $.ajax({
                url: "ajax.php",
                data: {action: 'getMetaDataDemplateLatestUser', userid: userid},
                type: "POST",
                success: function (data) {
                    var data = JSON.parse(data);
                    console.log(data);
                    $.each(data.keywords_save, function (keywordId, value) {
                        KM.eventManager.createHiddenSeletedKeyword(value.keyword, value.location);
                    });
                }
            });
        },
        onRemoveSelectedKeywordClick: function (e) {
            e && e.preventDefault();
            var link = $(e.currentTarget);
            var idArr = link.attr('id').split('-');
            var keywordID = idArr[1];
            if (keywordID) {
                link.parent().remove();
                this.removeSelectedKeyword(keywordID);
                this.removeNewSelectedKeyword(keywordID);
            }
        },
        onDeleteKeywordClick: function (e) {
            e && e.preventDefault()

            var link = $(e.currentTarget)
                , idArr = link.attr('id').split('-')
                , keywordID = idArr[1]
                , section = link.closest('.cliplog_section')
                , sectionID = section.attr('id')

            if (keywordID) {
                this.deleteKeyword(keywordID, sectionID)
            }
        },
        deselectSelectedKeyword: function (e) {

            var checkbox = $(e.currentTarget)
                , keywordID = checkbox.val()

            if (!checkbox.is(':checked') && keywordID) {
                this.removeSelectedKeyword(checkbox.val())
                $('#remove_keyword-' + keywordID).parent().remove();
            }
        },
        removeSelectedKeyword: function (keywordID) {
            var selectedKeywords = $('.cliplog_selected_keywords_list input[value="' + keywordID + '"]')
                , that = this
            if (selectedKeywords.length > 0) {
                $.each(selectedKeywords, function () {
                    var section = $(this).closest('.cliplog_section')
                        , keywordsList = section.find('.cliplog_keywords_list').first()
                        , sectionID = section.attr('id')
                    $(this).parent().remove();
                    if (keywordsList.length > 0) {
                        that.showKeywordsList(sectionID)
                    }
                })
            }
        },
        deleteKeyword: function (keywordID, sectionID) {
            var that = this
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/deletekeywords',
                dataType: 'json',
                data: ({id: keywordID}),
                cache: false,
                success: function (data) {
                    if (data.status == 1) {
                        that.showKeywordsList(sectionID)
                    }
                }
            });
        },
        //////////////////////////////////////////////////

        addClickedKeyword: function (e) {
            var checkbox = $(e.currentTarget)
                , section = checkbox.closest('.cliplog_section')
                , sectionID = section.attr('id')
            this.addSelectedKeywords(sectionID);
        },
        addKeywordToList: function (e) {
            e && e.preventDefault()

            var that = this
                , button = $(e.currentTarget)
                , section = button.closest('.cliplog_section')
                , sectionID
                , keywordInput
                , collection
            if (section.length > 0) {
                sectionID = section.attr('id')
                keywordInput = section.find('.cliplog_keyword_input')
                collection = $('select[name="sections_values[collection]"]').val();
                if (keywordInput.length > 0 && keywordInput.val()) {
                    this.saveKeyword(keywordInput.val(), sectionID, collection);
                }
            }
        },
        addSelectedKeywords: function (sectionID) {
            var selectedKeywordList = $('#' + sectionID).find('.cliplog_selected_keywords_list').first()
                , keywordList = $('#' + sectionID).find('.cliplog_keywords_list').first()
            if (selectedKeywordList.length == 0)
                selectedKeywordList = $('<div class="cliplog_selected_keywords_list"></div>').appendTo($('#' + sectionID).find('.left').first());
            var commonSelectedKeywordList = $('#cliplog').find('.cliplog_commont_selected_keywords_list').first()
            if (commonSelectedKeywordList.length == 0) {
                var commonSelectedKeywordListCont = $('<div class="control-group selected-items"><label class="control-label">Keywords Added:</label></div>').appendTo($('#cliplog_sidebar_content'));
                commonSelectedKeywordList = $('<div class="cliplog_commont_selected_keywords_list"></div>').appendTo(commonSelectedKeywordListCont);
            }

            if (keywordList.length > 0) {
                var selectedKeywordListHtml = '';
                var commonSelectedKeywordListHtml = '';
                $.each(keywordList.find('input.cliplog_keyword_checkbox:checked'), function () {
                    var keywordIDArr = $(this).attr('name').split('-');
                    var keywordID = keywordIDArr[1];
                    selectedKeywordListHtml += '<label class="checkbox"><input type="checkbox" name="keywords[' + keywordID + ']" value="' + keywordID + '" checked="checked"> ' + $(this).val() + '</label>';
                    commonSelectedKeywordListHtml += '<span>' + $(this).val() + ' <a href="#" class="cliplog_remove_keyword" id="remove_keyword-' + keywordID + '"><img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a></span>';
                    //$( this ).parent().parent().parent().remove();
                })
                $(selectedKeywordListHtml).appendTo(selectedKeywordList)
                $(commonSelectedKeywordListHtml).appendTo(commonSelectedKeywordList)
            }
        },
        saveKeyword: function (keyword, sectionID, collection, addToSelected) {
            var that = this
                , keywordInput
            if (keyword && sectionID)
                $.ajax({
                    type: 'POST',
                    url: 'en/cliplog/savekeyword',
                    dataType: 'json',
                    data: ({keyword: keyword, section: sectionID, collection: collection}),
                    cache: false,
                    success: function (data) {
                        if (data.status == 1 && data.keyword_id) {
                            keywordInput = $('#' + sectionID).find('.cliplog_keyword_input')
                            if (keywordInput.length > 0)
                                keywordInput.val('')

                            //if ( addToSelected ) // Добавляем кейворд в выбранные сразу после сабмита
                            that.addKeywordToSelectedByID(data.keyword_id, keyword, sectionID)

                            that.showKeywordsList(sectionID)
                        }
                    }
                });
        },
        addClickedOption: function (e) {
            var checkbox = $(e.currentTarget)
                , section = checkbox.closest('.cliplog_section')
                , sectionID = section.attr('id')

            this.addSelectedSectionOptions(sectionID)
        },
        switchOffSection: function (sectionID, sectionName, safeState) {


            if (safeState) {
                if (!_.isUndefined(sessionStorage["cliplog_show_sections_" + sectionID])) {
                    // Section originally hidden, after that turned on and then turned off ( reset to the original state )
                    delete sessionStorage["cliplog_show_sections_" + sectionID];
                    sessionStorage["cliplog_hide_sections_" + sectionID] = sectionID + "," + sectionName;
                } else {
                    // Remember that this section needs to be hidden
                    sessionStorage["cliplog_hide_sections_" + sectionID] = sectionID + "," + sectionName;
                }
            }

            var that = this
                , section = $('#' + sectionID)
                , hiddenSections = $('#cliplog').find('.cliplog_hidden_sections').first()
            $('.section-switch-cont-' + sectionID).remove();
            console.log($('#cliplog').find('.cliplog_hidden_sections .section-switch-cont-' + sectionID).length);
            if (!$('#cliplog').find('.cliplog_hidden_sections .section-switch-cont-' + sectionID).length) {
                section.hide();
                if (hiddenSections.length == 0) {
                    var hiddenSectionsCont = $('<div class="control-group selected-items"><label class="control-label">Hidden Fields:</label></div>').appendTo($('#cliplog_sidebar_content'));
                    hiddenSections = $('<div class="cliplog_hidden_sections"></div>').appendTo(hiddenSectionsCont);
                }

                var sectionHtml = '<div class="section-switch-cont section-switch-cont-' + sectionID + '"><div class="switch-cont">' +
                    '<div class="switch" data-animated="false" data-on-label="" data-off-label="">' +
                    '<input type="checkbox" name="sectionsHideList[]" value="' + sectionID + '" id="' + sectionName + '"></div></div> ' + sectionName + '</div>';
                $(sectionHtml).appendTo(hiddenSections);
                $('.section-switch-cont-' + sectionID).find('.switch')['bootstrapSwitch']();

                var section = $('#' + sectionID)
                    , checkbox = section.find('.section-switch-cont').first().find('input:checkbox').first();
                checkbox.removeAttr('checked');

                /*$('.section-switch-cont-' + sectionID).find('.switch').on('switch-change', function (e, data) {
                 var $el = $(data.el)
                 , value = data.value
                 , sectionID = $el.val()
                 , sectionName = $el.attr('id')
                 if(value == false && sectionID && sectionName){
                 that.switchOffSection(sectionID, sectionName, true);
                 }else if(value == true && sectionID){
                 that.switchOffSection(sectionID, sectionName, false);
                 that.switchOnSection(sectionID, sectionName, true);
                 }
                 });*/
                /*$('.section-switch-cont-' + sectionID + ' .switch').on('click', function (e, data) {
                 var $el = $(data.el)
                 , value = data.value
                 , sectionID = $el.val()
                 , sectionName = $el.attr('id')
                 console.log(data);
                 if(value == false && sectionID && sectionName){
                 that.switchOffSection(sectionID, sectionName, true);
                 }else if(value == true && sectionID){
                 that.switchOffSection(sectionID, sectionName, false);
                 that.switchOnSection(sectionID, sectionName, true);
                 }
                 });*/
            }


        },
        switchOnSection: function (sectionID, sectionName, safeState) {
            if (safeState) {
                if (!_.isUndefined(sessionStorage["cliplog_hide_sections_" + sectionID])) {
                    // Section originally displayed, after that turned off and then turned on ( reset to the original state )
                    delete sessionStorage["cliplog_hide_sections_" + sectionID];
                    sessionStorage["cliplog_show_sections_" + sectionID] = sectionID + "," + sectionName;
                } else {
                    // Remember that this section needs to be displayed
                    sessionStorage["cliplog_show_sections_" + sectionID] = sectionID + "," + sectionName;
                }
            }

            var section = $('#' + sectionID)
                , checkbox = section.find('.section-switch-cont').first().find('input:checkbox').first();
            checkbox.attr('checked', 'checked');
            section.find('.switch-off').removeClass('switch-off').addClass('switch-on');
            $('.section-switch-cont-' + sectionID).remove();
            section.show();
        },
        openThumbnailChanger: function () {
            var changer = $('#dialog-changethumb');
            changer.data('changeCallback', this.changeClipThumbnail);
            changer.dialog('open');
        },
        changeClipThumbnail: function () {

        },
        updateGotoNextStatus: function () {
            var status = $('#goto-next').is(':checked');
            status = (status) ? 'y' : 'n';
            $.ajax({
                type: 'POST',
                url: 'en/cliplog/index/savegotonext',
                dataType: 'json',
                data: ({status: status}),
                cache: false
            });
        },
        setKeywordsSetHeader: function (modified, setname, id) {
            var that = this;
            var template = ' (modified)';
            var header = $('.cliplog_Keyword_header');
            //modified =0 - берет значение с списка
            //modified =1 - добавляет template к заголовку
            //modified =2 - убирает с текущего заголовка template
            if (modified == undefined || modified == 0) {
                var option = $('#applied_keywords_set_id').find('option:selected');
                var name = (setname == undefined) ? option.text() : setname;
                var idh = (id == undefined) ? option.val() : id;
                header.html(name);
                that.hiddenElement($('.save_metadata_template'));
            } else {
                var metadataButtonSave = $('.save_metadata_template');
                var name = (setname == undefined) ? header.html().replace(template, '') : setname;
                var idh = (id == undefined) ? header.attr('data-Keyword-id') : id;

                var templateId = $('.cliplog_Keyword_header').attr('data-keyword-id');
                if (typeof templateId === 'undefined') {
                    header.html((modified == 2) ? name : name + template);
                    that.visibleElement(metadataButtonSave, 'inline-block');
                }
            }
            // if id==null удалить параметр data-Keyword-id
            if (id == null) {
                header.removeAttr('data-Keyword-id');
            } else {
                header.attr('data-Keyword-id', idh);
            }
        },
        getExpandedKeywordSections: function () {
            /*var keywordBoxExpanded = $( 'tr.cliplog_section' ).is(":visible");//$( 'a.cliplog-expander-button.expanded' ).closest( 'tr.cliplog_section' ).is(":visible");
             var expandedSections = [];
             for ( var i in keywordBoxExpanded ) {
             if ( keywordBoxExpanded.hasOwnProperty( i ) ) {
             var box = keywordBoxExpanded[ i ];
             var boxId = $( box ).attr( 'id' );
             if ( boxId ) {
             expandedSections.push( boxId );
             }
             }
             }
             return expandedSections;*/
            var expandedSections = [];
            $('tr.cliplog_section:visible').each(function (i) {
                var boxId = $(this).attr('id');
                if (boxId != undefined) {
                    expandedSections.push(boxId);
                } else {
                    var note = $(this).find('#clip_notes:visible');
                    if (note[0] != undefined)
                        expandedSections.push('clip_notes');
                }
            });
            return expandedSections;
        },
        getKeywordSectionsHideList: function () {
            var expandedSections = [];
            $('table.new-section.expanded').each(function (i) {
                var boxId = $(this).closest('.cliplog_section').attr('id');
                expandedSections.push(boxId);
            });
            return expandedSections;
        },
        checkOverwrite: function (e) {
            sessionStorage['overwriteFieldsChecked[' + $(e).val() + ']'] = $(e).prop('checked');
        },
        checkOverwriteFields: function () {
            var that = this;
            var checkMode = parseInt($('#overwrite_fields').val());
            switch (checkMode) {
                // Скрыть
                case 0:
                    $('input[name^="overwrite"]').each(function (field) {
                        $(this).parent().css('visibility', 'hidden');
                        $(this).removeAttr('checked');
                    });
                    break;
                // Показать и выключить все
                case 1:
                    $('input[name^="overwrite"]').each(function (field) {
                        $(this).parent().css('display', 'block');
                        $(this).prop('checked', false);
                    });
                    $('#overwrite_fields').val(0);
                    break;
                // Показать и выбрать все
                case 2:
                    $('input[name^="overwrite"]').each(function (field) {
                        $(this).parent().css('display', 'block');
                        $(this).prop('checked', true);
                        sessionStorage['overwriteFieldsChecked[' + $(this).val() + ']'] = true;
                    });
                    var loggingTemlateList = $.parseJSON(sessionStorage['loggingTemplateList']);
                    var loggingModified = false;
                    $('.cliplog_hidden_sections [class^="section-switch-cont"] input').each(function () {
                        var SectionID = $(this).val();
                        if ((loggingTemlateList['is_admin'] == 0 && SectionID != 'brand') || loggingTemlateList['is_admin'] != 1) {
                            that.switchOnSection($(this).val(), '', false);
                            loggingModified = true;
                        }
                    });
                    if (loggingModified)
                        that.hackLoggingModified();
                    $('#overwrite_fields').val(0);
                    break;
                // Скрыть
                default :
                    //$('input[name^="overwrite"]' ).each(function (field){
                    //    $(this).parent().css('display','none');
                    /*$(this).parent().removeClass('switch-on');
                     $(this).parent().addClass('switch-off');*/
                    //    $(this).removeAttr('checked');
                    //});
                    break;
            }
        },
        removeAllCheckOverwrite: function () {
            $('input[name^="overwrite"]').each(function (field) {
                sessionStorage.removeItem(['overwriteFieldsChecked[' + $(this).val() + ']']);
            });
        },
        AllCheckOverwrite: function () {
            $('input[name^="overwrite"]').each(function (field) {
                sessionStorage['overwriteFieldsChecked[' + $(this).val() + ']'] = true;
            });
        },
        focusOverwriteFields: function (e) {
            var id = $(e).attr('id');
            var lField = $(e).val().length;
            var newLField = 0;
            $(e).keyup(function (event) {
                newLField = $(e).val().length;
                if (newLField != lField && newLField != 0) { // if focusin text input and chenge value and value NOT blank, check overwrite
                    $('input[name="overwrite[' + id + ']"]').prop("checked", true);
                    $('input[name="overwrite[' + id + ']"]').prop("disabled", true);
                }
                if (newLField == 0) { // if focusin text input and chenge value ON blank, uncheck overwrite
                    $('input[name="overwrite[' + id + ']"]').prop("checked", false);
                    $('input[name="overwrite[' + id + ']"]').prop("disabled", false);
                }
            });
        }

    }


    /* CLIPLOG PLUGIN DEFINITION
     * ========================= */

    $.fn.cliplog = function (option) {

        var args = arguments;
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('tooltip')
                , options = typeof option == 'object' && option

            if (!data)
                $this.data('cliplog', (data = new Cliplog(this, options)))
            if (typeof option === 'string')
                data[option].apply(data, Array.prototype.slice.call(args, 1));
        })
    }

    $.fn.cliplog.Constructor = Cliplog;
    $.fn.cliplog.defaults = {
        addKeywordsClass: 'cliplog_add_keywords',
        closeKeywordsClass: 'cliplog_add_keywords',
        addKeywordToListClass: 'cliplog_add_keyword_to_list',
        addKeywordToSelectedClass: 'cliplog_add_keyword_to_selected',
        addSectionOptionsClass: 'cliplog_add_section_options',
        closeSectionOptionsClass: 'cliplog_add_section_options',
        saveTemplateClass: 'cliplog_save_template',
        applyTemplateClass: 'cliplog_apply_template',
        saveKeywordsSetClass: 'cliplog_save_keywords_set',
        removeSelectedKeywordClass: 'cliplog_remove_keyword',
        /* Codec Relations : Submission to Delivery */
        selectSubmissionCodec: 'select_submission_codec',
        selectDeliveryCategory: 'select_delivery_category',
        selectSourceFormat: 'select_source_format',
        selectSubmissionFrameSize: 'submission_frame_size',
        /* Новый блок ключевых слов */
        addKeywordsClass_New: 'cliplog-expander-button',
        closeKeywordsClass_New: 'cliplog-expander-button'

    }

}(window.jQuery);
$(function () {
    $('body').tooltip({
        selector: ".info_icon",
        placement: "top"
    });
    $('.metadata-switcher').on('click', function (e) {
        e.preventDefault();
        var $metadataCont = $(this).siblings('.metadata-container');
        if ($metadataCont.css('display') == 'none') {
            $metadataCont.show();
            $(this).text('Hide');
        }
        else {
            $metadataCont.hide();
            $(this).text('Show');
        }
    });
    $('#cliplog').cliplog();
});

// BY: IMR@N
//$('.section-switch-cont .switch')/*.find( '.switch' )*/.live('switch-change', function(e, data) {
//
//    createTemporaryLoggingTemplate();
//})


window.onbeforeunload = function (e) {


    if (!!$('.header-template-name').attr('data-template-id')) {
    } else {
        //  createTemporaryLoggingTemplate();
    }


};


function createTemporaryLoggingTemplate() {

    //var cehck = sessionStorage['temporaroryLoggingTemplateData'];


    var cehck = $('#myHiddenVal').val();
    var cehckNot = $('.header-template-name').attr("data-template-id");

    $.ajax({
        url: "ajax.php",
        data: {action: 'deleteTempLoggingTemplate', templateId: cehck, cehckNot: cehckNot},
        type: "POST",
        success: function (data) {
            createTempLoggingTemplate();

        }
    });

//
//    if (cehck ) {
//        $.ajax({
//            url: "ajax.php",
//            data: {action: 'deleteTempLoggingTemplate', templateId: cehck},
//            type: "POST",
//            success: function(data) {
//
//                createTempLoggingTemplate();
//            }
//        });
//    } else {
//        createTempLoggingTemplate()
//
//    }
}


function createTempLoggingTemplate() {
    var that = this;
    var templateNameField = $('.cliplog_template_name').first();
    var templateName = 'tempUserTemplate';
    var templateHeader = $('.header-template-name');
    var templateHeaderTitle = templateHeader.find('span').first();
    // //                var dataUsers = [];
//                $('.getUserKeywordsForLogging').each(function() {
//
//                    var id = $(this).attr("value");
//                    dataUsers.push(id);
//                })


    var requestData = $("#cliplog_form").serializeArray();
    requestData.push({name: 'keywords_sections_visible', value: getExpandedKeywordSections()});
    requestData.push({name: 'keywords_sections_hide_lists', value: getKeywordSectionsHideList()});
    requestData.push({name: 'tempalte_name', value: 'tempUserTemplate'});
    var data = [];
    $('.cliplog_keyword_checkbox').each(function () {
        var id = $(this).attr("name");
        requestData.push({name: 'keywordListMyList[]', value: id});
    })

    $.ajax({
        type: 'POST',
        url: 'en/cliplog/savetemplate',
        dataType: 'json',
        data: requestData,
        cache: false,
        success: function (data) {

            if (data.status == 1 && data.template.id) {
                templateHeader.attr('data-template-id', data.template.id);

                //  sessionStorage['temporaroryLoggingTemplateData'] = data.template.id;
            }
        }
    });
}


function getExpandedKeywordSections() {
    /*var keywordBoxExpanded = $( 'tr.cliplog_section' ).is(":visible");//$( 'a.cliplog-expander-button.expanded' ).closest( 'tr.cliplog_section' ).is(":visible");
     var expandedSections = [];
     for ( var i in keywordBoxExpanded ) {
     if ( keywordBoxExpanded.hasOwnProperty( i ) ) {
     var box = keywordBoxExpanded[ i ];
     var boxId = $( box ).attr( 'id' );
     if ( boxId ) {
     expandedSections.push( boxId );
     }
     }
     }
     return expandedSections;*/
    var expandedSections = [];
    $('tr.cliplog_section:visible').each(function (i) {
        var boxId = $(this).attr('id');
        if (boxId != undefined) {
            expandedSections.push(boxId);
        } else {
            var note = $(this).find('#clip_notes:visible');
            if (note[0] != undefined)
                expandedSections.push('clip_notes');
        }
    });
    return expandedSections;
}


function getKeywordSectionsHideList() {
    var expandedSections = [];
    $('table.new-section.expanded').each(function (i) {
        var boxId = $(this).closest('.cliplog_section').attr('id');
        expandedSections.push(boxId);
    });
    return expandedSections;
}


$(document).ready(function () {
    if ($('div.header-template-name').attr('data-template-id') != '') {
        var templateId = $('div.header-template-name').attr('data-template-id');
        $.ajax({
            url: "ajax.php",
            data: {action: 'getKeywordTemplateData', templateId: templateId},
            type: "POST",
            success: function (data) {
                if (data) {
                    var data = JSON.parse(data);
                    $.each(data, function (keywordId, value) {
                        if (keywordId == 'keywords_sections_hide_lists') {
                            var value = value;
                            var str_array = value.split(',');
                            $(".cliplog-expander-button").each(function () {
                                var buttonBox = $(this);
                                var tableBox = buttonBox.closest('.new-section');
                                var name = buttonBox.closest('tr.cliplog_section').attr('id');
                                if ($.inArray(name, str_array) < 0) {
                                    tableBox.removeClass('expanded').addClass('collapsed');
                                    buttonBox.removeClass('expanded').addClass('collapsed').html('Show list');
                                }

                            });
                            // console.log(value + 'Imran Is Here');
                        }
                    });

                }
            }
        });

    }
    //if ($('.cliplog_Keyword_header').attr('data-keyword-id') != '') {
    //    var templateId = $('.cliplog_Keyword_header').attr('data-keyword-id');
    //
    //    $.ajax({
    //        url: "ajax.php",
    //        data: {action: 'getMetaDataDemplate', templateId: templateId},
    //        type: "POST",
    //        success: function (data) {
    //            var data = JSON.parse(data);
    //            $.each(data.sections_values.add_collection, function (keywordId, value) {
    //                $('input:checkbox[value="' + value + '"]').attr('checked', true);
    //            });
    //            $("#countryMetaDataSelect").val(data.sections_values.country);
    //        }
    //    });
    //}


});

$(document).on('click', '.prev-clip', function (e) {

    var carouselList = $('#clips_carousel');
    var carouselItems = carouselList.find('.jcarousel-item');

    var checkingValData = $('#checkingValData').val();
    if (checkingValData == '1') {
        if (!confirm('You are about to leave this page. Your changes will be unsaved. Are you sure you would like to quit?')) {
            return true;
        } else {
            var getVal = carouselList.find('.jcarousel-item.active').prev().find('a').click();
        }
    } else {
        var getVal = carouselList.find('.jcarousel-item.active').prev().find('a').click();

    }

});


$(document).on('click', '.next-clip', function (e) {

    var carouselList = $('#clips_carousel');
    var carouselItems = carouselList.find('.jcarousel-item');

    var checkingValData = $('#checkingValData').val();
    if (checkingValData == '1') {
        if (!confirm('You are about to leave this page. Your changes will be unsaved. Are you sure you would like to quit?')) {
            return true;
        } else {
            var getVal = carouselList.find('.jcarousel-item.active').next().find('a').click();
        }
    } else {
        var getVal = carouselList.find('.jcarousel-item.active').next().find('a').click();

    }

});
$(document).on('click', 'a.cliplog-expander-button', function (e) {

    setTimeout(function () {
        if ($('div.header-template-name').attr('data-template-id') != '') {
            var templateId = $('div.header-template-name').attr('data-template-id');
            var expandedSections = [];
            $('table.new-section.expanded').each(function (i) {
                var boxId = $(this).closest('.cliplog_section').attr('id');
                expandedSections.push(boxId);
            });

            $.ajax({
                url: "ajax.php",
                data: {action: 'updateTemplateData', templateId: templateId, arrayUpdateList: expandedSections},
                type: "POST",
                success: function (data) {
                }
            });
        }
    }, 100);


});