$(document).ready(function () {

    formKeeper.init();

    $('#cliplog_form :input').on('change', function (e) {
        $('#checkingValData').val('1');
    });

    $('#back-link, .jcarousel-item a.carousel_link, a.carousel_link > img').on('click', function (e) {

        //  if ( formKeeper.getKeywordsState_forNotice() ) {
        var checkingValData = $('#checkingValData').val();

        if (checkingValData == '1') {
            if (!confirm('You are about to leave this page. Your changes will be unsaved. Are you sure you would like to quit?')) {

                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();

                return true;
            } else {
                formKeeper.resetKeywordsState_forNotice();
            }
        }
    });
});

formKeeper = {

    loggingModified: false,
    keywordsModified: false,
    closeWindowTimeout: null,

    keywordsStateNotice: false,

    registeredSelectIds: {},

    bodyName: 'body#admin',
    body: {},
    formBoxName: 'form#cliplog_form',
    formBox: {},
    loggingElementFlag: '[data-formkeeper="logging"]',
    formLoggingElements: {},
    keywordsElementFlag: '[data-formkeeper="keywords"]',
    formKeywordsElements: {},
    selectLoggingName: '#applied_template_id',
    selectKeywordsName: '#applied_keywords_set_id',

    keywordsElementsEvents: {
        'a.item-cross': 'click',
        '.cliplog_keywords_list_new label.checkbox': 'click',
        'input.cliplog_keyword_input': 'keyup',
        'button.cliplog_add_keyword_to_list_new': 'click',
        'input#clip_description': 'keyup',
        'input#license_restrictions': 'keyup',
        'input#clip_notes': 'keyup',
        'input[name="sections_values[file_formats][camera_model]"]': 'keyup',
        'input[name="sections_values[file_formats][source_data_rate]"]': 'keyup',
        'select[name="sections_values[collection]"]': 'change',
        '#add_collection input[type="radio"]': 'change',
        'input[name="sections_values[license_type]"]': 'change',
        'select[name^="sections_values[date_filmed]"]': 'change',
        'select[name="sections_values[releases]"]': 'change',
        'select[name="sections_values[license_type]"]': 'change',
        'select[name="sections_values[country]"]': 'change',
        'select[name="sections_values[audio_video]"]': 'change',
        'select[name="sections_values[price_level]"]': 'change',
        'select[name^="sections_values[file_formats]"]': 'change'
    },
    keywordsElementsVerification: {},
    loggingElementsEvents: {
        'a.cliplog-expander-button': 'click'
    },
    loggingElementsVerification: {},

    closeWindowMessage: 'Some data have been changed. Are you sure you would like to quite with no saving?\r\n' +
    'Clicking "Leave this Page" you will quit without saving, clicking "Stay on this Page" you will stay on the page so you can save your changes',

    init: function () {
        this.registerSelectIds();
        this.loggingModified = false;
        this.keywordsModified = false;
        this.body = $(this.bodyName);
        this.formBox = $(this.formBoxName);
        this.addEventsVerifications();
        this.formLoggingElements = this.body.find(this.loggingElementFlag);
        this.bindLoggingElements();

        if (this.keywordsTemplateNotDefault()) {
            this.setKeywordsState_forNotice();
        }
        // modified login template if view hide field
        if (sessionStorage['LoggingTemplateModify'] == 'true') {
            this.setLoggingModified();
        }
        //if ( this.keywordsTemplateNotDefault() ) {
        this.formKeywordsElements = this.body.find(this.keywordsElementFlag);
        this.bindKeywordsElements();
        //}

        if (this.isActiveState()) {
            /*
             * Не требуется больше.
             *
             * this.bindCloseWindow() - функциz предупреждения о несохраненных шаблонах, при попытке уйти со страницы.
             * this.bindSelectChange() - функция, которая возвражает положения селекта шаблона в прежнее состояние, если было
             *                           предупреждение о несохраненном шаблоне и пользователь остался на странице.
             */
            //this.bindCloseWindow();
            //this.bindSelectChange();
        }
    },

    bindSelectChange: function () {
        this.registerSelectIds();
        $(document).on('change', this.selectLoggingName, function () {
            formKeeper.registeredSelectIds.type = 'logging';
        });
        $(document).on('change', this.selectKeywordsName, function () {
            formKeeper.registeredSelectIds.type = 'keywords';
        });
    },

    registerSelectIds: function () {
        this.registeredSelectIds.logging = $(this.selectLoggingName).val();
        this.registeredSelectIds.keywords = $(this.selectKeywordsName).val();
        this.registeredSelectIds.type = '';
    },

    bindCloseWindow: function () {
        // Регистрация событий ухода со страницы
        //noinspection FunctionWithInconsistentReturnsJS
        $(window).on('beforeunload', function () {
            if (formKeeper.isFormModified()) {
                formKeeper.hideReloadLayout();
                formKeeper.showReloadLayout();
                formKeeper.closeWindowTimeout = setTimeout(function () {
                    // Отключение фона загрузки, если пользователь остался на странице
                    formKeeper.hideReloadLayout();
                    // Возвращаем select в прежнее положение
                    formKeeper.restoreSelectChange();
                }, 300);
                return formKeeper.getCloseWindowMessage();
            }
        });
        $(window).on('unload', function () {
            // Не отключать фон загрузки, пользователь покинул страницу
            clearTimeout(formKeeper.closeWindowTimeout);
        });
    },

    // Внешний перезапуск, когда изменен шаблон без перезагрузки страницы
    reinit: function (type) {
        switch (type) {
            case 'logging':
                this.loggingModified = false;
                break;
            case 'metadata':
            case 'keywords':
                this.keywordsModified = false;
                break;
            default:
                this.loggingModified = false;
                this.keywordsModified = false;
                break;
        }
        this.registerSelectIds();
    },

    isActiveState: function () {
        return ( this.loggingTemplateNotDefault() || this.keywordsTemplateNotDefault() );
    },

    loggingTemplateNotDefault: function () {
        return !!$(this.selectLoggingName).val();
    },

    keywordsTemplateNotDefault: function () {
        return !!$(this.selectKeywordsName).val();
    },
    keywordsHeaderTemplateNotDefault: function () {
        return !!$('.cliplog_Keyword_header').attr('data-Keyword-id');
    },

    addEventsVerifications: function () {
        // Задаем предварительные проверки для событий елементов формы
        this.keywordsElementsVerification['input.cliplog_keyword_input'] = function (element) {
            // Только если input содержит данные и нажата кнопка Enter
            return ( !!$(element).closest('td').find('input.cliplog_keyword_input').val() && event.charCode == 13 );
        };
        this.keywordsElementsVerification['button.cliplog_add_keyword_to_list_new'] = function (element) {
            // Только если input содержит данные
            return !!$(element).closest('td').find('input.cliplog_keyword_input').val();
        };
    },

    bindLoggingElements: function () {
        this.formLoggingElements.each(function () {
            $(this).on('change.formKeeper', function () {
                formKeeper.setLoggingModified();
            });
        });
        for (var elementSelector in this.loggingElementsEvents) {
            if (this.loggingElementsEvents.hasOwnProperty(elementSelector)) {
                var elementEvent = this.loggingElementsEvents[elementSelector];
                $(document).on(elementEvent + '.formKeeper', elementSelector, elementSelector, function (e) {
                    if (e.data) {
                        var currentSelector = e.data;
                        var currentTarget = e.currentTarget;
                        if (formKeeper.verifyLoggingEvent(currentTarget, currentSelector)) {
                            formKeeper.setLoggingModified();
                        }
                    }
                });
            }
        }
    },

    bindKeywordsElements: function () {
        for (var elementSelector in this.keywordsElementsEvents) {
            if (this.keywordsElementsEvents.hasOwnProperty(elementSelector)) {
                var elementEvent = this.keywordsElementsEvents[elementSelector];
                $(document).on(elementEvent + '.formKeeper', elementSelector, elementSelector, function (e) {
                    if (e.data) {
                        var currentSelector = e.data;
                        var currentTarget = e.currentTarget;
                        if (formKeeper.verifyKeywordsEvent(currentTarget, currentSelector)) {
                            //if ( formKeeper.keywordsHeaderTemplateNotDefault() ) {
                            formKeeper.setKeywordsModified();
                            //}
                            $('.apply_metadata_template').css('display', 'inline-block');
                            formKeeper.setKeywordsState_forNotice()
                        }
                    }
                });
            }
        }
    },

    verifyKeywordsEvent: function (target, selector) {
        if (formKeeper.keywordsElementsVerification.hasOwnProperty(selector)) {
            var verifyFunction = formKeeper.keywordsElementsVerification[selector];
            return verifyFunction(target);
        }
        return true;
    },

    verifyLoggingEvent: function (target, selector) {
        if (formKeeper.loggingElementsVerification.hasOwnProperty(selector)) {
            var verifyFunction = formKeeper.loggingElementsVerification[selector];
            return verifyFunction(target);
        }
        return true;
    },
    setLoggingModified: function () {
        if (!this.loggingModified) {
            var template = ' (modified)';
            var templateTitleBox = $('.header-template-name span');
            var name = templateTitleBox.html();
            var newName = name.replace(template, '');
            templateTitleBox.html(newName + template);
            this.loggingModified = true;
            sessionStorage.setItem("LoggingModified", template);// save Modified if refresh page
            this.showLoggingTemplateSaveButton();
        }
    },

    setKeywordsModified: function () {
        if (!this.keywordsModified) {

            var templateId = $('.cliplog_Keyword_header').attr('data-keyword-id');
            if (typeof templateId !== 'undefined') {
                var template = ' (modified)';
                //var option = $( this.selectKeywordsName ).find( 'option:selected' );
                var header = $('.cliplog_Keyword_header');
                //alert(option.val());
                sessionStorage.setItem("KeywordsModified", template);// save Modified if refresh page
                var name = header.html();
                var newName = name.replace(template, '');
                header.html(newName + template);
                this.keywordsModified = true;
                this.showKeywordsSaveButton();
                $('.apply_metadata_template').css('display', 'inline-block');
            }
        }
    },

    showLoggingTemplateSaveButton: function () {
        if (!!$('.header-template-name').attr('data-template-id')) {
            $('.header-template-name input.save_logging_template').attr('data-message', 0).show();
            sessionStorage.setItem("LoggingSaveButton", 0);// save Button if refresh page
        } else {
            $('.header-template-name input.save_logging_template').attr('data-message', 1).show();
            sessionStorage.setItem("LoggingSaveButton", 1);// save Button if refresh page
        }
    },
    showKeywordsSaveButton: function () {
        var metadataButtonSave = $('.save_metadata_template');
        if (!!$('.cliplog_Keyword_header').attr('data-Keyword-id')) {
            //metadataButtonSave.css( 'display', 'inline-block' );
            metadataButtonSave.attr('data-message', 0).show();
            sessionStorage.setItem("KeywordsSaveButton", 0);// save Button if refresh page
        } else {
            //metadataButtonSave.css( 'display', 'none' );
            metadataButtonSave.attr('data-message', 1).show();
            sessionStorage.setItem("KeywordsSaveButton", 1);// save Button if refresh page
        }
    },

    isLoggingModified: function () {
        return this.loggingModified;
    },

    isKeywordsModified: function () {
        return this.keywordsModified;
    },

    isFormModified: function () {
        return ( this.isLoggingModified() || this.isKeywordsModified() );
    },

    getCloseWindowMessage: function () {
        return this.closeWindowMessage;
    },

    showReloadLayout: function () {
        $('.reload-layout').css('display', 'block');
    },

    hideReloadLayout: function () {
        $('.reload-layout').css('display', 'none');
    },

    restoreSelectChange: function () {
        if (this.registeredSelectIds.type) {
            var select;
            var id = '';
            switch (this.registeredSelectIds.type) {
                case 'logging':
                    select = $(this.selectLoggingName);
                    id = this.registeredSelectIds.logging;
                    break;
                case 'metadata':
                case 'keywords':
                    select = $(this.selectKeywordsName);
                    id = this.registeredSelectIds.keywords;
                    break;
            }
            if (select) {
                select.find('option').removeAttr('selected');
                select.find('option[value="' + id + '"]').attr('selected', true);
            }
        }
    },

    setKeywordsState_forNotice: function () {
        this.keywordsStateNotice = true;
    },

    resetKeywordsState_forNotice: function () {
        this.keywordsStateNotice = false;
    },

    getKeywordsState_forNotice: function () {
        return !!this.keywordsStateNotice;
    }

};