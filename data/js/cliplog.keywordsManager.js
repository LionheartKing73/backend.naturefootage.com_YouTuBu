$(function () {
    KM.debugManager.useDebug(false);
    KM.init();
    $('#dialog-change-thumb source').attr('src', $('#dialog-change-thumb source').data('src'));
});

var KM = {
    /* Базовый контейнер */

    init: function () { /* Стартовый метод, инициализация */
        this.stateManager.init();
        this.requestManager.init();
        this.sectionsManager.init();
        this.eventManager.init();
        this.sectionListManager.init();
        this.manageListManager.init();
        this.templateManager.init();
    },
    getLoggingTemplateId: function () { /* Получить id активного Logging шаблона */
        // return $('div.header-template-name').attr('data-template-id');
        // alert($('div.header-template-name').attr('data-template-id'));
        if ($('div.header-template-name').attr('data-template-id') != '') {
            return $('div.header-template-name').attr('data-template-id') + 'customTemplate';
        } else {
            return $('#brandName').attr('data-id');
        }
    },
    getKeywordsTemplateId: function () { /* Получить id активного Keywords шаблона */
        return $('span.cliplog_Keyword_header').attr('data-keyword-id');
    },
    __saveStateToForm: function (formName) {
        // if (formKeeper.isLoggingModified()) {
        var stateString = JSON.stringify(KM.stateManager.keywordsState.byId);
        if (!formName)
            formName = '';
        $(formName + ' input[name="keywordsState"]').val(stateString);
        // }
    },
    __saveHiddenStateToForm: function (formName) {
        var stateString = JSON.stringify(KM.stateManager.keywordsHiddenState);
        if (!formName)
            formName = '';
        $(formName + ' input[name="keywordsHiddenState"]').val(stateString);
    },
    __getOldStateFromView: function () {
        //noinspection JSUnresolvedVariable
        return window.keywordsState;
    },
    __isSetOldState: function () {
        //noinspection JSUnresolvedVariable
        return (typeof window.keywordsState !== 'undefined' && Object.keys(window.keywordsState).length > 0);
    },
    __isSetKeywordsSectionsVisible: function () {
        //noinspection JSUnresolvedVariable
        if (typeof window.keywordsSectionsVisible !== 'undefined') {
            //noinspection JSUnresolvedVariable
            var sectionsString = window.keywordsSectionsVisible;
            var sectionList = sectionsString.split(',');
            if (sectionList.length > 0) {
                return true;
            }
        }
        return false;
    },
    __getKeywordsSectionsVisible: function () {
        //noinspection JSUnresolvedVariable
        return window.keywordsSectionsVisible.split(',');
    }

};

KM.debugManager = {
    /* Вывод отладочной информации в отображение */

    showDebugInfo: false,
    htmlBuffer: '',
    linePattern: '<p style="margin: 0; padding: 0; font-size: 11px; border-bottom: 1px dashed #eee; height: 16px; line-height: 16px;">%text%</p>',
    headerPattern: '<p style="margin: 3px 0; padding: 0; font-size: 11px;"><b>%text%</b></p>',
    useDebug: function (flag) {
        this.showDebugInfo = !!flag;
    },
    __showStateInView: function () {
        if (this.showDebugInfo) {
            var byId = KM.stateManager.keywordsState.byId;
            var bySection = KM.stateManager.keywordsState.bySection;
            var bySelected = KM.stateManager.keywordsState.bySelected;
            var byActive = KM.stateManager.keywordsState.byActive;
            var hiddenList = KM.stateManager.keywordsHiddenState;
            var stateString = (KM.stateManager.isNewState()) ? 'NewState' : 'OldState';
            this.__addHeader('keywordsManager: <e style="float: right;">[' + stateString + ']</e>');
            this.__addLine('Всего слов: <e style="float: right;">' + Object.keys(byId).length + '</e>');
            this.__addLine('Активные/Неактивные: <e style="float: right;">' + Object.keys(byActive[1]).length + ' / ' + Object.keys(byActive[0]).length + '</e>');
            this.__addLine('Выбранные слова (известные): <e style="float: right;">' + Object.keys(bySelected[1]).length + '</e>');
            this.__addLine('Скрытые слова: <e style="float: right;">' + Object.keys(hiddenList).length + '</e>');
            this.__addHeader('По секциям: <e style="float: right;">All / Active / Selected / Hidden</e>');
            $.each(bySection, function (sectionName, keywordList) {
                var countAll = 0;
                var countActive = 0;
                var countSelected = 0;
                var countHidden = 0;
                var sectionTitle = KM.sectionsManager.getSectionTitle(sectionName);
                $.each(hiddenList, function (i, keywordData) {
                    if (keywordData.keywordSection == sectionName)
                        countHidden++;
                });
                $.each(keywordList, function (i, keywordData) {
                    countAll++;
                    if (keywordData.isActive == 1)
                        countActive++;
                    if (keywordData.isSelected == 1)
                        countSelected++;
                });
                KM.debugManager.__addLine(sectionTitle + ' - <e style="float: right;">' + countAll + ' / ' + countActive + ' / ' + countSelected + ' / ' + countHidden + '</e>');
            });
            this.__showInView();
        }
    },
    __addLine: function (text) {
        this.htmlBuffer += this.linePattern.replace(/%text%/, text);
    },
    __addHeader: function (text) {
        this.htmlBuffer += this.headerPattern.replace(/%text%/, text);
    },
    __getHtmlBuffer: function () {
        var htmlBuffer = this.htmlBuffer;
        this.htmlBuffer = '';
        return htmlBuffer;
    },
    __showInView: function () {
        $('.keywordsManagerDebug').html(this.__getHtmlBuffer()).show();
    }

};

KM.stateManager = {
    /* Менеджер состояния ключевых слов */

    keywordsState: {
        /* Список, состояние ключевых слов */
        byId: {
            1: {
                /* Шаблон, только для примера */
                isActive: '0',
                isBasic: '0',
                isOld: '0',
                keywordCollection: 'Collection Title',
                keywordId: '1',
                keywordOwnerId: '0',
                keywordSection: 'section_name',
                keywordText: 'Text'
            }
        },
        bySection: {},
        byActive: {},
        bySelected: {}
    },
    keywordsHiddenState: {}, /* Список скрытых, быстрых ключевых слов*/

    isAdmin: 0, /* Является ли пользователь администратором */
    userId: 0, /* Id пользователя */

    init: function () {
        //noinspection JSUnresolvedVariable
        if (typeof (window.selectedKeywordIds) === 'undefined') {
            throw '/view/cliplog/edit.php: отсутствует selectedKeywordIds!';
        }
        /* Проверим, передано ли старое состояние в отображение */
        if (KM.__isSetOldState()) {
            console.log('Create Old State');
            //this.createOldState();
            this.createBasicState();
            formKeeper.setLoggingModified();
        } else {
            console.log('Create New State');
            //this.createOldState();
            this.createBasicState();
        }
    },
    isNewState: function () {
        return (this.currentState === 'new');
    },
    isOldState: function () {
        return (this.currentState === 'old');
    },
    setStateNew: function () {
        this.currentState = 'new';
    },
    setStateOld: function () {
        this.currentState = 'old';
    },
    createBasicState: function () { /* Create original , baseline keywords */
        console.log('Here we go to find keywords');
        KM.requestManager.__postRequest(
            'en/cliplogkeywords/index/getkeywordlist',
            {
                templateId: KM.getLoggingTemplateId(),
                keywordSetid: KM.getKeywordsTemplateId()
            }, /*KM.getKeywordsTemplateId()*/
            KM.stateManager.__createBasicStateFromResponse
        );
        this.setStateNew();
    },
    createOldState: function () { /* Воссоздание прошлого состояния кл.слов */
        KM.stateManager.clearBasicState();
        KM.stateManager.isAdmin = KM.requestManager.responseManager.__getIsAdmin();
        KM.stateManager.userId = KM.requestManager.responseManager.__getUserId();
        var keywordsList = KM.__getOldStateFromView();
        KM.stateManager.__replaceSelectedKeywordsId(keywordsList);
        $.each(keywordsList, function (i, keywordData) {
            var keywordId = keywordData.keywordId;
            var keywordSection = keywordData.keywordSection;
            var keywordActive = keywordData.isActive;
            var keywordSelected = 0;
            if (KM.stateManager.__isSelectedKeyword(keywordId)) {
                keywordSelected = 1;
            }
            keywordData.isSelected = keywordSelected;
            KM.stateManager.keywordsState.byId[keywordId] = keywordData;
            KM.stateManager.keywordsState.bySection[keywordSection][keywordId] = keywordData;
            KM.stateManager.keywordsState.byActive[keywordActive][keywordId] = keywordData;
            KM.stateManager.keywordsState.bySelected[keywordSelected][keywordId] = keywordData;
            KM.debugManager.__showStateInView();
        });
        if (KM.__isSetKeywordsSectionsVisible()) {
            $.each(KM.__getKeywordsSectionsVisible(), function (i, sectionName) {
                KM.sectionListManager.showKeywordList(sectionName);
            });
        }
        this.setStateOld();
        this._setupSections();
    },
    _setupSections: function () {

        // Backend is unable to correctly render keywords section ( for unknown reason )
        // We will manually go thru all sections and force JS side to rerender keywords.
        // This is very ugly hack, but this is what we have at the moment :(
        KM.sectionListManager.showKeywordList('shot_type');
        KM.sectionListManager.showKeywordList('subject_category');
        KM.sectionListManager.showKeywordList('primary_subject');
        KM.sectionListManager.showKeywordList('other_subject');
        KM.sectionListManager.showKeywordList('appearance');
        KM.sectionListManager.showKeywordList('actions');
        KM.sectionListManager.showKeywordList('time');
        KM.sectionListManager.showKeywordList('habitat');
        KM.sectionListManager.showKeywordList('concept');
        KM.sectionListManager.showKeywordList('location');

        // Restore hidden/displayed keywords sections
        for (var i = 0; i < sessionStorage.length; i++) {
            var entryKey = sessionStorage.key(i);
            if (entryKey.indexOf("cliplog_keywords_") > -1) {

                var sectionName = entryKey.substr(17);
                var state = sessionStorage[entryKey];

                $(".cliplog-expander-button").each(function () {

                    var buttonBox = $(this);

                    var tableBox = buttonBox.closest('.new-section');
                    var name = buttonBox.closest('tr.cliplog_section').attr('id');

                    if (sectionName == name) {

                        if (state == "show") {
                            KM.sectionListManager.showKeywordList(sectionName);
                            console.log("Show section: " + sectionName);
                            tableBox.removeClass('collapsed').addClass('expanded');
                            buttonBox.removeClass('collapsed').addClass('expanded').html('Hide list');
                        } else {
                            KM.sectionListManager.clearKeywordList(sectionName);
                            tableBox.removeClass('expanded').addClass('collapsed');
                            buttonBox.removeClass('expanded').addClass('collapsed').html('Show list');
                        }
                    }
                });
            }
        }
    },
    clearBasicState: function () { /* Сброс первоначального, базового состояния ключевых слов */
        KM.stateManager.keywordsState.byId = {};
        KM.stateManager.keywordsState.bySelected = {};
        KM.stateManager.keywordsState.bySelected[0] = {};
        KM.stateManager.keywordsState.bySelected[1] = {};
        KM.stateManager.keywordsState.byActive = {};
        KM.stateManager.keywordsState.byActive[0] = {};
        KM.stateManager.keywordsState.byActive[1] = {};
        KM.stateManager.keywordsState.bySection = {};
        var sectionNames = KM.sectionsManager.getSectionNames();
        $.each(sectionNames, function (i, sectionName) {
            KM.stateManager.keywordsState.bySection[sectionName] = {};
        });
    },

    in_array: function (search, array) {
        for (i = 0; i < array.length; i++) {
            if (array[i] == search) {
                return true;
            }
        }
        return false;
    },
    getSectionKeywordList: function (sectionName) { /* Получить все кл.слова секции */
        if (sectionName && this.keywordsState.bySection.hasOwnProperty(sectionName)) {
            return this.keywordsState.bySection[sectionName];
        }
        return null;
    },
    getKeywordSectionName: function (keywordId) { /* Получить имя секции ключевого слова */
        if (keywordId && this.keywordsState.byId.hasOwnProperty(keywordId)) {
            var keywordData = this.keywordsState.byId[keywordId];
            if (keywordData.hasOwnProperty('keywordSection')) {
                return keywordData.keywordSection;
            }
        }
        return null;
    },
    setKeywordActive: function (keywordId) { /* Помечаем кл.слово как активное */
        if (keywordId && this.keywordsState.byId.hasOwnProperty(keywordId)) {
            this.keywordsState.byId[keywordId].isActive = 1;
            this.keywordsState.byActive[1][keywordId] = this.keywordsState.byId[keywordId];
            delete this.keywordsState.byActive[0][keywordId];
        }
    },
    setKeywordInactive: function (keywordId) { /* Помечаем кл.слово как неактивное */
        if (keywordId && this.keywordsState.byId.hasOwnProperty(keywordId)) {
            this.keywordsState.byId[keywordId].isActive = 0;
            this.keywordsState.byActive[0][keywordId] = this.keywordsState.byId[keywordId];
            delete this.keywordsState.byActive[1][keywordId];
        }
    },
    setKeywordSelected: function (keywordId) { /* Помечаем кл.слово как выбранное */
        if (keywordId && this.keywordsState.byId.hasOwnProperty(keywordId)) {
            this.keywordsState.byId[keywordId].isSelected = 1;
            this.keywordsState.bySelected[1][keywordId] = this.keywordsState.byId[keywordId];
            delete this.keywordsState.bySelected[0][keywordId];
        }
    },
    setKeywordUnselected: function (keywordId) { /* Помечаем кл.слово как не выбранное */
        if (keywordId && this.keywordsState.byId.hasOwnProperty(keywordId)) {
            this.keywordsState.byId[keywordId].isSelected = 0;
            this.keywordsState.bySelected[0][keywordId] = this.keywordsState.byId[keywordId];
            delete this.keywordsState.bySelected[1][keywordId];
        }
    },
    getActiveKeywordList: function (sectionName) {
        var keywordList = {};
        $.each(this.keywordsState.byActive[1], function (keywordId, keywordData) {
            if (keywordData.hasOwnProperty('keywordSection') && keywordData.keywordSection === sectionName) {
                keywordList[keywordId] = keywordData;
            }
        });
        return keywordList;
    },
    getSelectedKeywordList: function (sectionName) {
        var keywordList = {};
        $.each(this.keywordsState.bySelected[1], function (keywordId, keywordData) {
            if (keywordData.hasOwnProperty('keywordSection') && keywordData.keywordSection === sectionName) {
                keywordList[keywordId] = keywordData;
            }
        });
        return keywordList;
    },
    createKeyword: function (keywordText, keywordSection) {
        if (keywordText && keywordSection) {
            var keywordActive = 0;
            var keywordSelected = 0;
            var keywordId = 'temp_' + KM.stateManager.getRandom();//Date.now();
            var keywordOwnerId = (this.isUserAdmin()) ? 0 : this.getUserId();
            var keywordData = {
                isSelected: keywordSelected,
                isActive: keywordActive,
                isBasic: 0,
                isOld: 0,
                isTmp: 1,
                keywordCollection: '',
                keywordId: keywordId,
                keywordOwnerId: keywordOwnerId,
                keywordSection: keywordSection,
                keywordText: keywordText
            };
            KM.stateManager.keywordsState.byId[keywordId] = keywordData;
            KM.stateManager.keywordsState.bySection[keywordSection][keywordId] = keywordData;
            KM.stateManager.keywordsState.byActive[keywordActive][keywordId] = keywordData;
            KM.stateManager.keywordsState.bySelected[keywordSelected][keywordId] = keywordData;
            return keywordId;
        }
        return null;
    },
    getRandom: function () {
        return Math.floor(Math.random() * (10000000 - 1 + 1)) + 1;
    },
    createHiddenKeyword: function (keywordText, keywordSection) {
        if (keywordText && keywordSection) {
            var keywordId = 'hidden_' + KM.stateManager.getRandom();//Date.now();
            var keywordData = {
                isSelected: 0,
                isActive: 1,
                isBasic: 0,
                isOld: 0,
                keywordCollection: '',
                keywordId: keywordId,
                keywordOwnerId: 0,
                keywordSection: keywordSection,
                keywordText: keywordText
            };
            KM.stateManager.keywordsHiddenState[keywordId] = keywordData;
            return keywordId;
        }
        return null;
    },
    getUserId: function () {
        return this.userId;
    },
    isUserAdmin: function () {
        return this.isAdmin;
    },
    deleteKeyword: function (keywordId) { /* Удаление кл.слова из состояния */
        if (this.keywordsState.byId.hasOwnProperty(keywordId)) {
            var keywordData = this.keywordsState.byId[keywordId];
            delete this.keywordsState.byId[keywordId];
            delete this.keywordsState.byActive[keywordData.isActive][keywordId];
            delete this.keywordsState.bySelected[keywordData.isSelected][keywordId];
            delete this.keywordsState.bySection[keywordData.keywordSection][keywordId];
        }
    },
    deleteHiddenKeyword: function (keywordId) { /* Удаление скрытого кл.слова из состояния */
        if (this.keywordsHiddenState.hasOwnProperty(keywordId)) {
            delete this.keywordsHiddenState[keywordId];
        }
    },
    isKeywordActive: function (keywordId) {
        return (this.keywordsState.byId.hasOwnProperty(keywordId) && this.keywordsState.byId[keywordId].isActive == 1);
    },
    getKeywordsState: function () {
        return this.keywordsState.byId;
    },
    getKeywordData: function (keywordId) {
        return (this.keywordsState.byId.hasOwnProperty(keywordId)) ? this.keywordsState.byId[keywordId] : null;
    },
    getHiddenKeywordData: function (keywordId) {
        return (this.keywordsHiddenState.hasOwnProperty(keywordId)) ? this.keywordsHiddenState[keywordId] : null;
    },
    __createBasicStateFromResponse: function () { /* Создать первоначальное состояние используя данные Ajax-запроса */
        KM.stateManager.clearBasicState();
        KM.stateManager.isAdmin = KM.requestManager.responseManager.__getIsAdmin();
        KM.stateManager.userId = KM.requestManager.responseManager.__getUserId();
        var keywordsList = KM.requestManager.responseManager.__getKeywordList();
        KM.stateManager.__replaceSelectedKeywordsId(keywordsList);
        $.each(keywordsList, function (i, keywordData) {
            var keywordId = keywordData.keywordId;
            var keywordSection = keywordData.keywordSection;
            var keywordActive = keywordData.isActive;
            var keywordSelected = 0;

            //console.log(keywordId);
            //console.log('.cliplog_selected_keywords_list_new input[name="keywords[' + keywordId + ']"]');
            if (KM.stateManager.__isSelectedKeyword(keywordId)) {
                keywordSelected = 1;
                // console.log(keywordData);
            }
            if (keywordSelected == 0) {
                var valuearr = [];
                $("#" + keywordSection + " .cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                    valuearr.push($(this).find('.getUserKeywordsForLogging').attr('datavalue-text'));
                })
                if (KM.stateManager.in_array(keywordData.keywordText, valuearr)) {
                    keywordSelected = 1;
                }
            }
            keywordData.isSelected = keywordSelected;
            KM.stateManager.keywordsState.byId[keywordId] = keywordData;
            KM.stateManager.keywordsState.bySection[keywordSection][keywordId] = keywordData;
            KM.stateManager.keywordsState.byActive[keywordActive][keywordId] = keywordData;
            KM.stateManager.keywordsState.bySelected[keywordSelected][keywordId] = keywordData;
            if (KM.__isSetKeywordsSectionsVisible()) {
                $.each(KM.__getKeywordsSectionsVisible(), function (i, sectionName) {
                    KM.sectionListManager.showKeywordList(sectionName);
                });
            } else {

            }
            KM.debugManager.__showStateInView();
        });

        KM.stateManager._setupSections();
    },
    __isSelectedKeyword: function (keywordId) { /* Проверить, активно ли кл.слово */
        return $('.cliplog_selected_keywords_list_new input[name="keywords[' + keywordId + ']"]').is(':checked');
    },
    __replaceSelectedKeywordsId: function (keywordsList) { /* Заменяем id временных выбранных кл.слов на новые */
        var foundInputs = $('.cliplog_selected_keywords_list_new input[type="hidden"]');
        $.each(keywordsList, function (keywordId, keywordData) {
            var keywordText = keywordData.keywordText;
            var keywordSection = keywordData.keywordSection;
            foundInputs.each(function () {
                var currentText = $(this).closest('div.item').text().replace(/(^\s+)|(\s+$)/gi, '');
                var currentSection = $(this).closest('.cliplog_section').attr('id');
                if (keywordText === currentText && keywordSection === currentSection) {
                    $(this).attr('value', keywordId).attr('name', 'keywords[' + keywordId + ']');
                }
            });
        });
    }

};

KM.requestManager = {
    /* Менеджер Ajax-запросов */

    init: function () {
    },
    __postRequest: function (requestLink, requestData, callbackFunction) { /* Выполнить Ajax-запрос */
        var runCallback;
        if (callbackFunction) {
            runCallback = function (responseData) {
                console.log('__postRequest: ' + requestLink);
                console.log('__postDataJson: ' + JSON.stringify(requestData));
                //console.log( '__callbackFunction: ' + callbackFunction );
                //console.log( '__ResponseJSON: ' + JSON.stringify(responseData) );
                KM.requestManager.responseManager.responseData = responseData;
                callbackFunction();
            };
        }
        $.post(requestLink, requestData, runCallback, 'json');
    },
    responseManager: {
        /* Менеджер данных ответов с Ajax-запросов */

        responseData: {},
        __getValue: function (fieldName) { /* Получить данные с ответа */
            if (this.responseData.hasOwnProperty(fieldName)) {
                return this.responseData[fieldName];
            }
            return {};
        },
        __getKeywordList: function () { /* Получить данные ключевых слов с ответа */
            return this.__getValue('keywordList');
        },
        __getIsAdmin: function () { /* Получить является ли администратором с ответа */
            return this.__getValue('isAdmin');
        },
        __getUserId: function () { /* Получить id пользователя с ответа */
            return this.__getValue('userId');
        }

    }

};

KM.sectionsManager = {
    /* Менеджер названий и имен секций */

    sectionNameToTitle: {
        shot_type: 'Shot Type',
        subject_category: 'Subject Category',
        primary_subject: 'Primary Subject',
        other_subject: 'Other Subject',
        appearance: 'Appearance',
        actions: 'Actions',
        time: 'Time',
        habitat: 'Habitat',
        concept: 'Concept',
        location: 'Location'
    },
    sectionTitleToName: {
        'Shot Type': 'shot_type',
        'Subject Category': 'subject_category',
        'Primary Subject': 'primary_subject',
        'Other Subject': 'other_subject',
        'Appearance': 'appearance',
        'Actions': 'actions',
        'Time': 'time',
        'Habitat': 'habitat',
        'Concept': 'concept',
        'Location': 'location'
    },
    init: function () {
    },
    getSectionNames: function () { /* Получить имена всех секций */
        return this.sectionTitleToName;
    },
    getSectionName: function (sectionTitle) { /* Получить имя секции по названию */
        if (sectionTitle && this.sectionTitleToName.hasOwnProperty(sectionTitle)) {
            return this.sectionTitleToName[sectionTitle];
        }
        return null;
    },
    getSectionTitle: function (sectionName) { /* Получить название секции по имени */
        if (sectionName && this.sectionNameToTitle.hasOwnProperty(sectionName)) {
            return this.sectionNameToTitle[sectionName];
        }
        return null;
    }

};

KM.eventManager = {
    /* Менеджер действий и событий */

    init: function () {
        this.bindEvents();
    },
    bindEvents: function () { /* Привзяать действия к событиям */
        // Клик по "Manage all Keywords"
        $(document).on('click', 'a.cliplog-manage-button', function () {
            var sectionName = $(this).attr('data-id');
            KM.eventManager.showManageList(sectionName);
        });
        // Включение\Выключение кл. слов в окне "Manage all Keywords"
        $(document).on('switch-change', 'div.dialog-keyword-list div.switch', function (e, data) {
            var keywordId = $(data.el).closest('.item').attr('data-keywordId');
            var switchStatus = data.value;


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


            if (switchStatus) {
                KM.eventManager.switchOnKeyword(keywordId);
            } else {
                KM.eventManager.switchOffKeyword(keywordId);
            }
        });
        // Удаление кл. слов в окне "Manage all Keywords"
        $(document).on('click', 'a.popup_delete_keyword', function (e) {
            e.preventDefault();
            var keywordId = $(this).attr('data-keywordId');

            var keywordtext = $(this).attr('data-keywordname');
            var keywordSection = $(this).attr('data-keywordsection');

            KM.eventManager.confirmBox('Confirm Delete', function () {
                $.ajax({
                    url: "ajax.php",
                    data: {action: 'deleteuserKeyword', keywordId: keywordId},
                    type: "POST",
                    success: function (data) {
                    }
                });
                KM.eventManager.deleteKeyword(keywordId);
            });

        });
        // Добавление нового кл.слова в окне "Manage all Keywords"
        $(document).on('click', 'button.cliplog_add_keyword_to_popup_list_new', function () {
            var keywordInput = $('input.cliplog_keyword_input.is-popup');
            var keywordText = keywordInput.val();
            var UserId = KM.stateManager.getUserId();
            var sectionName = KM.manageListManager.getSectionName();

            keywordText = keywordText.replace(/(^\s+)|(\s+$)/gi, '');

            var templateId = $('div.header-template-name').attr('data-template-id');

            if ($('div.header-template-name').attr('data-template-id') != '') {
                var actionSend = 'addUserKeywordsOldieTemplate';
            } else {
                var actionSend = 'addUserKeywordsOldie';
            }


            if (keywordText) {

                formKeeper.setLoggingModified();
                $('.reload-layout').css('display', 'block');


                $.ajax({
                    url: "ajax.php",
                    data: {
                        action: actionSend,
                        sectionName: sectionName,
                        userid: UserId,
                        keyword: keywordText,
                        templateId: templateId
                    },
                    type: "POST",
                    success: function (data) {
                        KM.requestManager.__postRequest(
                            'en/cliplogkeywords/index/getkeywordlist',
                            {
                                templateId: KM.getLoggingTemplateId(),
                                keywordSetid: KM.getKeywordsTemplateId()
                            }, /*KM.getKeywordsTemplateId()*/
                            KM.stateManager.__createBasicStateFromResponse
                        );

                        setTimeout(function () {
                            KM.eventManager.showManageList(sectionName);

                            $('.reload-layout').css('display', 'none');
                        }, 3000);
                    }
                });
                keywordInput.val('');
                //
                // KM.eventManager.createKeyword(keywordText, KM.manageListManager.getSectionName());


            }


            $('#checkingValData').val('1');
        });
        $(document).on('keypress', 'input.cliplog_keyword_input.is-popup', function (e) {
            if (e.keyCode === 13) {
                $('button.cliplog_add_keyword_to_popup_list_new').click();
            }
        });

        $(document).on('change', '#brandName', function () {

            var tampId = this.value;
            $('#brandName').attr('data-id', tampId)
            setTimeout(function () {
                KM.stateManager.createBasicState();
            }, 450);

        });


        // Открытие\Закрытие списка кл.слов для добавления
        $(document).on('click', 'a.cliplog-expander-button', function (e) {
            var buttonBox = $(e.currentTarget);
            var tableBox = buttonBox.closest('.new-section');
            var sectionName = buttonBox.closest('tr.cliplog_section').attr('id');
            if (tableBox.is('.collapsed')) {

                sessionStorage["cliplog_keywords_" + sectionName] = "show";

                KM.sectionListManager.showKeywordList(sectionName);
                tableBox.removeClass('collapsed').addClass('expanded');
                buttonBox.removeClass('collapsed').addClass('expanded').html('Hide list');
            } else {

                sessionStorage["cliplog_keywords_" + sectionName] = "hide";

                KM.sectionListManager.clearKeywordList(sectionName);
                tableBox.removeClass('expanded').addClass('collapsed');
                buttonBox.removeClass('expanded').addClass('collapsed').html('Show list');
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
        // Включение\Выключение выбраного кл.слова
        $(document).on('click', 'div.cliplog_keywords_list_new label.checkbox', function () {
            var sectionName = $(this).closest('tr.cliplog_section').attr('id');
            var keywordName = $(this).find('.cliplog_keyword_checkbox').attr('name');
            var keywordNameVal = $(this).find('.cliplog_keyword_checkbox').val();
            var keywordId = keywordName.split('keyword-')[1];
            var keywordValue = $(this).find('.cliplog_keyword_checkbox').val();
            //Check THE Logging Rules for have and not have(getKeywordMust & getKeywordNotMust)
            $.ajax({
                url: "ajax.php",
                data: {action: 'getKeywordMust', sectionName: sectionName, keywordName: keywordNameVal},
                type: "POST",
                success: function (data) {
                    if (data) {
                        var data = JSON.parse(data);
                        $.each(data, function (keywordId, value) {
                            $('#' + sectionName + " input:checkbox[value='" + value + "']").click();
                        });

                    }
                }
            });

            $.ajax({
                url: "ajax.php",
                data: {action: 'getKeywordNotMust', sectionName: sectionName, keywordName: keywordNameVal},
                type: "POST",
                success: function (data) {
                    var data = JSON.parse(data);
                    if (data) {

                        $.each(data, function (keywordId, value2) {
                            $.each($('#' + sectionName + ' .cliplog_selected_keywords_list_new .item-wrapper'), function (keywordId, value) {
                                //var test = this.closest('.item-wrapper').html();
                                var test = $(this).html();
                                var checkIfValueExists = test.search(value2);

                                if (checkIfValueExists > '0') {
                                    $(this).find('a').click();
                                    //console.log($(this).closest('a.item-cross').find('a'));
                                }

                            });
                        });
                    }
                }
            });


            KM.eventManager.setSeletedKeyword(keywordId, sectionName);
            $('#checkingValData').val('1');
        });
        $(document).on('click', 'div.cliplog_selected_keywords_list_new a.item-cross', function () {
            if (!$(this).is('.is-hidden')) {
                var sectionName = $(this).closest('tr.cliplog_section').attr('id');
                var keywordId = $(this).closest('.item-wrapper').find('input[type="hidden"]').attr('datadell-id');
                var keywordId2 = $(this).closest('.item-wrapper').find('input[type="hidden"]').attr('datalb-k-id');
                var keywordIdVal = $(this).closest('.item-wrapper').find('input[type="hidden"]').val();
                KM.eventManager.setUnseletedKeyword(keywordIdVal, sectionName);
                KM.eventManager.setUnseletedKeyword(keywordId2, sectionName);
                $(this).closest('.item-wrapper').remove();
                //alert(keywordId);
                ///Creating The Del Data
                if (typeof keywordId !== 'undefined') {
                    var currentVal = $('#deleteFeilddata').val();
                    if (currentVal == '') {
                        var InseertVal = keywordId;
                    } else {
                        var InseertVal = currentVal + ',' + keywordId;
                    }
                    $('#deleteFeilddata').val(InseertVal);
                }
            }

            $('#checkingValData').val('1');
        });


        // ******************************* CLARIFAI BLOCK ******************************* //

        // Generate keywords for clip, using the clarifai API
        $(document).on('click', 'input#generate_clarifai_keywords', function (e) {
            e.preventDefault();
            var id = $('.footagesearch-clip').attr('data-clip-id');
            $.ajax({
                type: 'POST',
                dataType: 'text',
                url: '/en/cliplog/generate_keywords',
                data: {id: id},
                beforeSend: function(){
                    $('.info_message').hide();
                    $('.loading_message').show();
                },
                success: function(data) {
                    data = data.replace(/,\s+/g, ',');
                    window.keywordsString = data;
                    $('#auto_generated').find('input').val(data);
                    $('#auto_generated').find('button.cliplog_add_keyword_to_list_new').click();
                    $('.loading_message').hide();
                    $('.success_message').show();
                }
            });
        });

        // Delete clarifai keywords and send
        $('#auto_generated').on('click', '.item-cross', function () {
            var keyword = $(this).parent().find('input').attr("datavalue-text");
            $.ajax({
                type: 'POST',
                dataType: 'text',
                url: '/en/cliplog/reject_keyword',
                data: {keyword: keyword},
                success: function(data) {
                }
            });
        });

        // Approve keywords for clip, which was generated by clarifai API
        $(document).on('click', '#approve_keywords', function (e) {
            e.preventDefault();
            var items = $('#auto_generated').find('.item').find('input');
            var data = window.keywordsString + ',';
            $.ajax({
                type: 'POST',
                dataType: 'text',
                url: '/en/cliplog/approve_keywords',
                data: {keywords: data},
                success: function(res) {
                }
            });
            var arr = data.split(",");
            $.each(arr, function (index, item) {
                if($("input:checkbox[value='"+item+"']").length){
                    data = data.replace(item+',', '');
                    $("input:checkbox[value='"+item+"']").parent().trigger( "click" );
                } else {
                    if (item.match("ing$")){
                        itemRoot = item.replace(/ing$/, '');
                        if($("input:checkbox[value='"+itemRoot+"']").length){
                            $("input:checkbox[value='"+itemRoot+"']").parent().trigger( "click" );
                            data = data.replace(item+',', '');
                        }
                    }
                    if (item.match("s$")){
                        itemRoot = item.replace(/s$/, '');
                        if($("input:checkbox[value='"+itemRoot+"']").length){
                            $("input:checkbox[value='"+itemRoot+"']").parent().trigger( "click" );
                            data = data.replace(item+',', '');
                        }
                    }
                }
            });
            $('#other_subject').find('input').val(data);
            $('#other_subject').find('button.cliplog_add_keyword_to_list_new').click();

            items.each(function() {
                $(this).parents(".item-wrapper").remove();
            });
        });

        // ******************************* END OF CLARIFAI BLOCK ******************************* //

        // Добавление скрытого, быстрого кл.слова
        $(document).on('click', 'button.cliplog_add_keyword_to_list_new', function (e) {
            e.preventDefault();
            var sectionBox = $(this).closest('.cliplog_section');
            var inputBox = sectionBox.find('.cliplog_keyword_input');
            var inputValue = inputBox.val().replace(/(^\s+)|(\s+$)/gi, '');

            var splitString = inputValue.split(',');
            if (typeof splitString !== 'undefined') {
                for (var i = 0; i < splitString.length; i++) {
                    var stringPart = splitString[i];
                    var sectionName = sectionBox.attr('id');
                    if (stringPart) {
                        inputBox.val('');
                        KM.eventManager.createHiddenSeletedKeyword(stringPart, sectionName);
                    }
                }
            } else {
                var sectionName = sectionBox.attr('id');
                if (inputValue) {
                    inputBox.val('');
                    KM.eventManager.createHiddenSeletedKeyword(inputValue, sectionName);
                }

            }


            $('#checkingValData').val('1');
        });
        $(document).on('keypress', '.new-section input.cliplog_keyword_input', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                var sectionName = $(this).closest('.cliplog_section').attr('id');
                $('.cliplog_section[id="' + sectionName + '"] button.cliplog_add_keyword_to_list_new').click();
            }
        });
        // Удаление скрытого, быстрого кл.слова
        $(document).on('click', 'div.cliplog_selected_keywords_list_new a.item-cross.is-hidden', function () {
            var sectionName = $(this).closest('tr.cliplog_section').attr('id');
            var keywordId = $(this).closest('.item-wrapper').find('input[type="hidden"]').val();
            KM.eventManager.deleteHiddenSeletedKeyword(keywordId, sectionName);
            $(this).closest('.item-wrapper').remove();
        });
        // Сабмиты форм, прикрепление данных состояния к запросам
        $(document).on('submit', '.carousel_form', function () {
            KM.__saveStateToForm('.carousel_form');
        });
        $(document).on('submit', '#cliplog_form', function () {
            KM.__saveStateToForm('#cliplog_form');
            KM.__saveHiddenStateToForm('#cliplog_form');
            //KM.eventManager.saveForm();
            //return false;
        });
    },
    saveForm: function () {
        var data = $('form#cliplog_form').serialize();
        var url = $('form#cliplog_form').attr('action');
        console.log(url, data);

        $.ajax({
            type: 'POST',
            url: '/' + url,
            data: data + '&save=1',
            success: function (data) {
                if ($('#goto-next').prop("checked")) {
                    console.log('Submit form and GoToNext clip');
                    window.location = '/en/cliplog/edit/' + $('#goto-next').val();
                } else {
                    console.log('Submit form and Reload page');
                    setTimeout(function () {
                        window.location.reload();
                    }, 450);
                }
                $('.reload-layout').hide();
            },
            error: function (xhr, str) {
                alert('Error: ' + xhr.status + ' ' + xhr.statusText);
                console.log(xhr);
            }
        });
    },
    showManageList: function (sectionName) { /* Отбразить попап управления кл.словами */
        KM.manageListManager.setSectionName(sectionName);
        KM.manageListManager.clearKeywordList();
        var popupTitle = 'Manage Keyword List: ' + KM.sectionsManager.getSectionTitle(sectionName);
        KM.manageListManager.setTitle(popupTitle);
        var sectionKeywordList = KM.stateManager.getSectionKeywordList(sectionName);
        var keywordOwnerId = KM.requestManager.responseManager.__getUserId();
        KM.manageListManager.pushKeywordList(sectionKeywordList);
    },
    switchOnKeyword: function (keywordId) { /* Включить кл.слово */
        KM.stateManager.setKeywordActive(keywordId);
        console.log('Active: ' + keywordId);
        var sectionName = KM.stateManager.getKeywordSectionName(keywordId);
        KM.sectionListManager.showKeywordList(sectionName);
        formKeeper.setLoggingModified();
        KM.debugManager.__showStateInView();
        var userid = KM.stateManager.getUserId();
        var templateId = $('div.header-template-name').attr('data-template-id');
        if ($('div.header-template-name').attr('data-template-id') != '') {
            $.ajax({
                url: "ajax.php",
                data: {action: 'enableKeyword', keywordId: keywordId, userid: userid, templateId: templateId},
                type: "POST",
                success: function (data) {
                }
            });
        } else {
            $.ajax({
                url: "ajax.php",
                data: {action: 'enableKeyword', keywordId: keywordId, userid: userid},
                type: "POST",
                success: function (data) {
                }
            });
        }
    },
    switchOffKeyword: function (keywordId) { /* Отключить кл.слово */
        KM.stateManager.setKeywordInactive(keywordId);
        console.log('Inactive: ' + keywordId);
        var userid = KM.stateManager.getUserId();
        var templateId = $('div.header-template-name').attr('data-template-id');
        if ($('div.header-template-name').attr('data-template-id') != '') {
            $.ajax({
                url: "ajax.php",
                data: {action: 'disableKeyword', keywordId: keywordId, userid: userid, templateId: templateId},
                type: "POST",
                success: function (data) {
                }
            });
        } else {
            $.ajax({
                url: "ajax.php",
                data: {action: 'disableKeyword', keywordId: keywordId, userid: userid},
                type: "POST",
                success: function (data) {
                }
            });
        }


        var sectionName = KM.stateManager.getKeywordSectionName(keywordId);
        KM.sectionListManager.showKeywordList(sectionName);
        formKeeper.setLoggingModified();
        KM.debugManager.__showStateInView();
    },
    createKeyword: function (keywordText, keywordSection) { /* Создать временное кл.слово */
        var keywordId = KM.stateManager.createKeyword(keywordText, keywordSection);
        this.showManageList(keywordSection);
        console.log('Create: ' + keywordId);
        // включить слово
        KM.eventManager.switchOnKeyword(keywordId);
        //KM.eventManager.setSeletedKeyword( keywordId, keywordSection );
        // включить переключатель
        $('input[value="' + keywordId + '"]').closest('.switch-off').removeClass('switch-off').addClass('switch-on');
        formKeeper.setLoggingModified();
        KM.debugManager.__showStateInView();
    },
    deleteKeyword: function (keywordId) { /* Удалить кл.слово */
        var sectionName = KM.stateManager.getKeywordSectionName(keywordId);
        KM.stateManager.deleteKeyword(keywordId);
        KM.manageListManager.removeKeywordFromList(keywordId);
        console.log('Delete: ' + keywordId);
        formKeeper.setLoggingModified();
        KM.sectionListManager.showKeywordList(sectionName);
        KM.debugManager.__showStateInView();
    },
    confirmBox: function (message, confirmCallback, cancelCallback) {
        var box = $('#dialog-confirm');
        box.find('.message').html(message);
        box.data('confirmCallback', confirmCallback);
        box.data('cancelCallback', cancelCallback);
        box.dialog('open');
        //box.dialog( "option", "position", { my: "center top", at: "center top", of: $( event.currentTarget ) } );
    },
    setSeletedKeyword: function (keywordId, sectionName) {
        KM.stateManager.setKeywordSelected(keywordId);
        KM.sectionListManager.addKeywordToSelectedList(keywordId, sectionName);
        KM.sectionListManager.showKeywordList(sectionName);
        KM.debugManager.__showStateInView();
    },
    setUnseletedKeyword: function (keywordId, sectionName) {
        KM.stateManager.setKeywordUnselected(keywordId);
        KM.sectionListManager.showKeywordList(sectionName);
        KM.debugManager.__showStateInView();
    },
    createHiddenSeletedKeyword: function (keywordText, keywordSection) {
        var keywordId = KM.stateManager.createHiddenKeyword(keywordText, keywordSection);
        KM.sectionListManager.addHiddenKeywordToSelectedList(keywordId, keywordSection);
        console.log('Create Hidden: ' + keywordId);
        KM.debugManager.__showStateInView();
    },
    deleteHiddenSeletedKeyword: function (keywordId) {
        KM.stateManager.deleteHiddenKeyword(keywordId);
        console.log('Delete Hidden: ' + keywordId);
        KM.debugManager.__showStateInView();
    }

};

KM.sectionListManager = {
    /* Управление кл.словами в секциях */

    init: function () {
    },
    showKeywordList: function (sectionName) { /* Отобразить кл.слова для секции */
        var showKeywordList = {};
        var activeKeywordList = KM.stateManager.getActiveKeywordList(sectionName);
        var selectedKeywordList = KM.stateManager.getSelectedKeywordList(sectionName);
        $.each(activeKeywordList, function (keywordId, keywordData) {
            if (!selectedKeywordList.hasOwnProperty(keywordId)) {
                showKeywordList[keywordId] = keywordData;
            }
        });
        var keywordsHtml = '';
        var forSort = [];
        /* Сортируем кл.слова по алфавиту */
        $.each(showKeywordList, function (keywordId, keywordData) {
            forSort.push(keywordData);
        });
        forSort.sort(function (a, b) {
            if (a.keywordText.toLowerCase() > b.keywordText.toLowerCase())
                return 1;
            if (a.keywordText.toLowerCase() < b.keywordText.toLowerCase())
                return -1;
            return 0;
        });
        $.each(forSort, function (i, keywordData) {
            keywordsHtml += KM.templateManager.buildKeywordHtmlForSectionList(keywordData);
            keywordsHtml += KM.templateManager.buildKeywordHtmlForSectionListImran(keywordData);
        });
        $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_keywords_list_new ').html('');
        $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_keywords_list_new ').css('visibility', 'visible');
        $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_keywords_list_new ').html(keywordsHtml);
    },
    clearKeywordList: function (sectionName) { /* Очистить секцию от кл.слов */
        $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_keywords_list_new ').css('visibility', 'hidden');
    },
    addKeywordToSelectedList: function (keywordId, sectionName) {
        var keywordData = KM.stateManager.getKeywordData(keywordId);
        var keywordsHtml = KM.templateManager.buildKeywordHtmlForSectionSelectedList(keywordData);
        var sectionListBox = $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_selected_keywords_list_new');
        sectionListBox.html(sectionListBox.html() + keywordsHtml);
    },
    addHiddenKeywordToSelectedList: function (keywordId, sectionName) {
        var keywordData = KM.stateManager.getHiddenKeywordData(keywordId);
        var keywordsHtml = KM.templateManager.buildHiddenKeywordHtmlForSectionSelectedList(keywordData);
        var sectionListBox = $('tr.cliplog_section[id="' + sectionName + '"]').find('div.cliplog_selected_keywords_list_new');
        sectionListBox.html(sectionListBox.html() + keywordsHtml);
    }

};

KM.manageListManager = {
    /* Управление кл.словами в попапе */

    managePopupBox: null,
    managePopupListBox: null,
    init: function () {
        this.initBoxes();
    },
    initBoxes: function () { /* Назначить елементы */
        this.managePopupBox = $('div#dialog-keywords-manage');
        this.managePopupListBox = this.managePopupBox.find('div.dialog-keyword-list');
    },
    setSectionName: function (sectionName) { /* Задать имя секции для попапа */
        this.managePopupBox.find('.id').attr('data-id', sectionName);
    },
    getSectionName: function () { /* Получить имя секции попапа */
        return this.managePopupBox.find('.id').attr('data-id');
    },
    setTitle: function (text) { /* Установить тайтл попапа */
        this.managePopupBox.find('.title').html(text);
    },
    clearKeywordList: function () { /* Очистить список кл.слов в попапе */
        this.managePopupListBox.html('');
    },
    pushKeywordList: function (keywordList) { /* Разместить список кл.слов в попапе */
        var keywordsHtml = '';
        var forSort = [];
        /* Сортируем кл.слова по алфавиту */
        $.each(keywordList, function (keywordId, keywordData) {

            forSort.push(keywordData);
        });
        forSort.sort(function (a, b) {
            if (a.keywordText.toLowerCase() > b.keywordText.toLowerCase())
                return 1;
            if (a.keywordText.toLowerCase() < b.keywordText.toLowerCase())
                return -1;
            return 0;
        });
        $.each(forSort, function (i, keywordData) {
            keywordsHtml += KM.templateManager.buildKeywordHtmlForManageList(keywordData);
        });
        if (!keywordsHtml) {
            keywordsHtml = KM.templateManager.getEmptyManageListPattern();
        }
        this.managePopupListBox.html(this.managePopupListBox.html() + keywordsHtml).find('.switch')['bootstrapSwitch']();
    },
    removeKeywordFromList: function (keywordId) { /* Удалить кл.слово в попапе */
        this.managePopupListBox.find('.item[data-keywordId="' + keywordId + '"]').remove();
    }

};

KM.templateManager = {
    /* Парсер шаблонов */

    popupKeywordPattern: '<div class="item" data-keywordId="%keywordId%"><label class="checkbox" title="%keywordText%">' +
    '<input type="hidden" name="%keywordId%" value="%keywordText%" class="cliplog_keyword_checkbox">%keywordTextDisplay%' +
    '</label><div class="switch-cont"><div class="switch" data-animated="false" data-on-label="" data-off-label="">' +
    '<input type="checkbox" %keywordEnabled% value="%keywordId%"/></div></div>%deleteItem%</div>',
    popupDeletePattern: '<a href="#" class="popup_delete_keyword" id="delete_keyword-%keywordId%" data-keywordId="%keywordId%" title="%keywordName%" data-keywordName="%keywordName%" data-keywordSection="%sectionVal%"> ' +
    '<img src="/data/img/admin/cliplog/remove_icon.jpg" alt="" title="remove"></a>',
    popupEmptyPattern: '<p style="text-align: center; margin-top: 80px;">No Keywords!</p>',
    activeKeywordPattern: '<label class="checkbox "><input type="checkbox" name="keyword-%keywordId%" value="%keywordText%" class="cliplog_keyword_checkbox">%keywordText%</label>',
    activeKeywordPatternImran: '<input type="hidden" name="KeywordsSetsIds[]" value="%keywordId%" class="cliplog_keyword_checkbox_imran">',
    selectedKeywordPattern: '<div class="item-wrapper"><a class="item-cross"></a><div class="item"><input type="hidden" checked="checked" class="getUserKeywordsForLogging" value="%keywordId%" name="keywords[%keywordId%]" datavalue-text="%keywordText%">%keywordText%</div></div>',
    selectedHiddenKeywordPattern: '<div class="item-wrapper"><a class="item-cross is-hidden"></a><div class="item"><input type="hidden" checked="checked" value="%keywordId%" name="keywords[%keywordId%]" datavalue-text="%keywordText%">%keywordText%</div></div>',
    init: function () {
    },
    getEmptyManageListPattern: function () { /* Шаблон для попапа, если нет кл.слов */
        return this.popupEmptyPattern;
    },
    buildKeywordHtmlForManageList: function (keywordData) { /* Получить HTML кл.слова для попапа */
        // console.log(keywordData);
        var keywordHtml = this.popupKeywordPattern;
        var keywordId = keywordData.keywordId;
        var keywordText = keywordData.keywordText;
        var keywordActive = (KM.stateManager.isKeywordActive(keywordId)) ? 'checked' : '';
        var keywordSection = keywordData.keywordSection;
        //  console.log(keywordText.length);
        var deleteItem = '';

        if (keywordText.length > 30) {
            var keywordTextDisplay = keywordText.substring(0, 20);
            keywordTextDisplay = keywordTextDisplay + '...';
            // console.log(keywordTextDisplay);

        } else {
            keywordTextDisplay = keywordText;
        }


        if (!_.isUndefined(keywordData.isTmp) && keywordData.isTmp) {
            deleteItem = this.popupDeletePattern.replace(/(%keywordId%)/gi, keywordId);
        }

        /*if ( KM.stateManager.isUserAdmin() && keywordData.keywordOwnerId == 0 ) {
         deleteItem = this.popupDeletePattern.replace( /(%keywordId%)/gi, keywordId );
         } else*/
        if (/*!KM.stateManager.isUserAdmin() &&*/ keywordData.keywordOwnerId == KM.stateManager.getUserId()) {
            deleteItem = this.popupDeletePattern.replace(/(%keywordName%)/gi, keywordText).replace(/(%sectionVal%)/gi, keywordSection).replace(/(%keywordId%)/gi, keywordId);

            keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        }


        keywordHtml = keywordHtml.replace(/(%keywordId%)/gi, keywordId);
        keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        keywordHtml = keywordHtml.replace(/(%keywordTextDisplay%)/gi, keywordTextDisplay);
        keywordHtml = keywordHtml.replace(/(%keywordEnabled%)/gi, keywordActive);
        keywordHtml = keywordHtml.replace(/(%deleteItem%)/gi, deleteItem);
        return keywordHtml;
    },
    buildKeywordHtmlForSectionList: function (keywordData) { /* Получить HTML кл.слова для секции */
        var keywordHtml = this.activeKeywordPattern;
        var keywordId = keywordData.keywordId;
        var keywordText = keywordData.keywordText;
        keywordHtml = keywordHtml.replace(/(%keywordId%)/gi, keywordId);
        keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        return keywordHtml;
    },
    buildKeywordHtmlForSectionListImran: function (keywordData) { /* Получить HTML кл.слова для секции */
        var keywordHtml = this.activeKeywordPatternImran;
        var keywordId = keywordData.keywordId;
        var keywordText = keywordData.keywordText;
        keywordHtml = keywordHtml.replace(/(%keywordId%)/gi, keywordId);
        keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        return keywordHtml;
    },
    buildKeywordHtmlForSectionSelectedList: function (keywordData) { /* Получить HTML активного кл.слова для секции */
        var keywordHtml = this.selectedKeywordPattern;
        var keywordId = keywordData.keywordId;
        var keywordText = keywordData.keywordText;
        keywordHtml = keywordHtml.replace(/(%keywordId%)/gi, keywordId);
        keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        return keywordHtml;
    },
    buildHiddenKeywordHtmlForSectionSelectedList: function (keywordData) { /* Получить HTML активного кл.слова для секции */
        var keywordHtml = this.selectedHiddenKeywordPattern;
        var keywordId = keywordData.keywordId;
        var keywordText = keywordData.keywordText;
        keywordHtml = keywordHtml.replace(/(%keywordId%)/gi, keywordId);
        keywordHtml = keywordHtml.replace(/(%keywordText%)/gi, keywordText);
        return keywordHtml;
    }

};


$(document).ready(function () {
    function in_array(search, array) {
        for (i = 0; i < array.length; i++) {
            if (array[i] == search) {
                return true;
            }
        }
        return false;
    }

    var templateId = $('.cliplog_Keyword_header').attr('data-keyword-id');
    if (typeof templateId === 'undefined') {
        $('.cliplog_Keyword_header').empty();
        $('.save_metadata_template').css('display', 'none');
    }
    setTimeout(function () {


        //Check if the Keywords tempalte is applied then make the required changes according to the template.
        if ($('.cliplog_Keyword_header').attr('data-keyword-id') != '') {
            var templateId = $('.cliplog_Keyword_header').attr('data-keyword-id');
            $.ajax({
                url: "ajax.php",
                data: {action: 'getMetaDataDemplate', templateId: templateId},
                type: "POST",
                success: function (data) {
                    if (data) {
                        var data = JSON.parse(data);
                    } else {
                        return false;
                    }
                    //console.log(data);
                    $('#checkingValData').val('1');
                    if (data.sections_values.add_collection) {
                        $.each(data.sections_values.add_collection, function (keywordId, value) {
                            $('input:radio[value="' + value + '"]').attr('checked', true);
                            $("input[name=\"sections_values[add_collection][]\"]").closest("tr").find(":checkbox").prop("checked", true);
                        });

                    }

                    if (data.sections_values.country.length != 0) {
                        $("#countryMetaDataSelect").val(data.sections_values.country);
                        $("input[name=\"sections_values[country][]\"]").closest("tr").find(":checkbox").prop("checked", true);
                    }

                    $.each(data.keywords_save, function (keywordId, value) {
                        var valuearr = [];
                        var requestData = [];

                        $('.cliplog_keyword_checkbox').each(function () {
                            var id = $(this).attr("value");
                            requestData.push(id);
                        })
                        $("#" + value.location + " .cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                            valuearr.push($(this).find('.getUserKeywordsForLogging').attr('datavalue-text'));
                        })
                        //console.log(valuearr);
                        //console.log(requestData);
                        if (!in_array(value.keyword, valuearr)) {
                            if (in_array(value.keyword, requestData)) {
                                $('#' + value.location + " input:checkbox[value='" + value.keyword + "']").click();
                            } else {
                                KM.eventManager.createHiddenSeletedKeyword(value.keyword, value.location);
                            }
                        }
                        //sessionStorage.removeItem('KeywordsModified');
                        //sessionStorage.removeItem('KeywordsSaveButton');


                        //var HtmlToChange = $('.cliplog_Keyword_header').html().replace("(modified)", "");
                        //$('.cliplog_Keyword_header').html(HtmlToChange);
                        //$('.cliplog_save_metadata').hide();

                    });
                    var loggingHeader = $('.cliplog_Keyword_header').text().replace(' (modified)', '');
                    $('.cliplog_Keyword_header').text(loggingHeader);
                    $('.save_metadata_template').css('display', 'none');
                    sessionStorage.removeItem('KeywordsModified');
                    sessionStorage.removeItem('KeywordsSaveButton');
                    formKeeper.reinit('keywords');

                }
            });


        }

        //Check IF Rest All Feilds keywords templates is applied then remove the
        // keywords and also add them to the hidden feild in case if
        //User wants to delete them permanentaly(Save Data)
        var checkRest = $('#keywords_set_id_reset').val();
        if (checkRest == 'reset') {
            $(".cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                var DelId = $(this).find('.getUserKeywordsForLogging').attr('datadell-id');
                $(this).find('.item-cross').click();
            })
            $('.cliplog_selected_keywords_list_new').html('');
            $('input[name^="sections_values"]').each(function () {
                $('input:radio[value="' + $(this).val() + '"]').attr('checked', false);
            });
            $("select#countryMetaDataSelect option").removeAttr("selected");
        }


    }, 3000);

    //Useed to copy keywords from the previous Clip
    $('.copy_prev_keyqords').click(function () {
        $('.reload-layout').css('display', 'block');
        var carouselList = $('#clips_carousel');
        var carouselItems = carouselList.find('.jcarousel-item');
        var getVal = carouselList.find('.jcarousel-item.active').prev().find('a').attr('href');

        if (getVal !== 'undefined') {
            // $('.cliplog_selected_keywords_list_new').html('');
            var array = getVal.split("/");
            var KeyWordId = array[array.length - 1];
            $.ajax({
                url: "ajax.php",
                data: {action: 'getPrevClipIds', clipid: KeyWordId},
                type: "POST",
                success: function (data) {
                    var data = JSON.parse(data);

                    //Setting the Values//
                    if (data.clipData.description) {
                        $("#clip_description").val(data.clipData.description);
                    }
                    if (data.clipData.notes) {
                        $("input[name='sections_values[clip_notes]']").val(data.clipData.notes);
                    }
                    if (data.clipData.license_restrictions) {
                        $("input[name='sections_values[license_restrictions]']").val(data.clipData.license_restrictions);
                    }
                    if (data.clipData.audio_video) {
                        $("#Audio_Videos").val(data.clipData.audio_video);
                    }

                    if (data.clipData.category) {
                            $('input:radio[value="' + data.clipData.category + '"]').attr('checked', true);
                    }
                    if (data.clipData.license) {
                        $("select[name='sections_values[license_type]']").val(data.clipData.license);
                    }
                    if (data.clipData.price_level) {
                        $("select[name='sections_values[price_level]']").val(data.clipData.price_level);
                    }
                    if (data.clipData.releases) {
                        $("select[name='sections_values[releases]']").val(data.clipData.releases);
                    }

                    if (data.clipData.film_month) {
                        $("select[name='sections_values[date_filmed][month]']").val(data.clipData.film_month);
                    }
                    if (data.clipData.film_year) {
                        $("select[name='sections_values[date_filmed][year]']").val(data.clipData.film_year);
                    }
                    //Setting File Formates//****//Setting File Formates//
                    if (data.clipData.camera_model) {
                        $("input[name='sections_values[file_formats][camera_model]']").val(data.clipData.camera_model);
                    }
                    if (data.clipData.camera_chip_size) {
                        $("select[name='sections_values[file_formats][camera_chip_size]']").val(data.clipData.camera_chip_size);
                    }
                    if (data.clipData.bit_depth) {
                        $("select[name='sections_values[file_formats][bit_depth]']").val(data.clipData.bit_depth);
                    }
                    if (data.clipData.color_space) {
                        $("select[name='sections_values[file_formats][color_space]']").val(data.clipData.color_space);
                    }
                    if (data.clipData.source_format) {
                        $("select[name='sections_values[file_formats][source_format]']").val(data.clipData.source_format);
                    }
                    if (data.clipData.source_codec) {
                        $("select[name='sections_values[file_formats][source_codec]']").val(data.clipData.source_codec);
                    }
                    if (data.clipData.master_format) {
                        $("select[name='sections_values[file_formats][master_format]']").val(data.clipData.master_format);
                    }

                    if (data.clipData.master_frame_size) {
                        $("select[name='sections_values[file_formats][master_frame_size]']").val(data.clipData.master_frame_size);
                    }
                    if (data.clipData.master_frame_rate) {
                        $("select[name='sections_values[file_formats][master_frame_rate]']").val(data.clipData.master_frame_rate);
                    }
                    if (data.clipData.pricing_category) {
                        $("select[name='sections_values[file_formats][pricing_category]']").val(data.clipData.pricing_category);
                    }


                    if (data.clipData.source_frame_size) {
                        $("select[name='sections_values[file_formats][source_frame_size]']").val(data.clipData.source_frame_size);
                    }
                    if (data.clipData.source_frame_rate) {
                        $("select[name='sections_values[file_formats][source_frame_rate]']").val(data.clipData.source_frame_rate);
                    }
                    if (data.clipData.digital_file_format) {
                        $("select[name='sections_values[file_formats][digital_file_format]']").val(data.clipData.digital_file_format);
                    }
                    if (data.clipData.source_data_rate) {
                        $("input[name='sections_values[file_formats][source_data_rate]']").val(data.clipData.source_data_rate);
                    }
                    if (data.clipData.digital_file_frame_rate) {
                        $("select[name='sections_values[file_formats][digital_file_frame_rate]']").val(data.clipData.digital_file_frame_rate);
                    }
                    if (data.clipData.digital_file_frame_size) {
                        $("select[name='sections_values[file_formats][digital_file_frame_size]']").val(data.clipData.digital_file_frame_size);
                    }
                    if (data.clipData.country) {
                        $("select[name='sections_values[country]']").val(data.clipData.country);
                    }

                    //Setting the sections keywords//
                    $.each(data.keywords, function (keywordId, value) {
                        var valuearr = [];
                        $("#" + value.section_id + " .cliplog_selected_keywords_list_new .item-wrapper").each(function () {
                            valuearr.push($(this).find('.getUserKeywordsForLogging').attr('datavalue-text'));
                        })
                        if (!in_array(value.keyword, valuearr)) {
                            KM.eventManager.createHiddenSeletedKeyword(value.keyword, value.section_id);
                        }
                    });
                    $('#checkingValData').val('1');
                    $('.reload-layout').css('display', 'none');
                }
            });
        }
    });
    $('.next-clip').click(function () {
        //var selected = $('#nextClipId').val()
        //var carouselList = $('#clips_carousel');
        //var carouselItems = carouselList.find('.jcarousel-item');
        //carouselList.find('.jcarousel-item.active').next().find('a').click();


        var carouselList = $('#clips_carousel');
        var carouselItems = carouselList.find('.jcarousel-item');
        var clipId = carouselList.find('.jcarousel-item.active').find('a').attr('href');
        if (typeof clipId == 'undefined') {
            clipId = window.location.href;
        }
        clipId = clipId.replace(/\D/g, '');
        console.log(clipId);
        $.ajax({
            url: 'en/cliplog/index/getNextClipPath/' + clipId,
            data: {clipId: clipId},
            type: "POST",
            success: function (data) {
                window.location = '/en/cliplog/edit/' + data;

            }
        });

        //$('.jcarousel-item').each(function () {
        //    var test = $('.footagesearch-clip-code').text();
        //    var test2 = $($(this)).find('a').attr('att-code');
        //    if (test == test2) {
        //        console.log($($(this)).next().find('a'));
        //        $($(this)).next().find('a').click();
        //    }
        //});


    });
    $('.prev-clip').click(function () {
        //var selected = $('#nextClipId').val()
        //var carouselList = $('#clips_carousel');
        //var carouselItems = carouselList.find('.jcarousel-item');
        //carouselList.find('.jcarousel-item.active').prev().find('a').click();


        var carouselList = $('#clips_carousel');
        var carouselItems = carouselList.find('.jcarousel-item');
        var clipId = carouselList.find('.jcarousel-item.active').find('a').attr('href');
        if (typeof clipId == 'undefined') {
            clipId = window.location.href;
        }
        clipId = clipId.replace(/\D/g, '');
        console.log(clipId);
        $.ajax({
            url: 'en/cliplog/index/getPrevClipPath/' + clipId,
            data: {clipId: clipId},
            type: "POST",
            success: function (data) {
                window.location = '/en/cliplog/edit/' + data;

            }
        });

    });
});