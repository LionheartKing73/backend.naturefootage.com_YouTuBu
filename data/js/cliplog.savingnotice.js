$( document ).ready( function () {
    savingNotice.init();
} );

savingNotice = {

    manyClipsBoxSelector : 'div#footagesearch-clip-more-one',
    saveButtonSelector : 'form#cliplog_form .form-actions input[type="submit"]',
    manyClipsBox : {},
    saveButton : {},
    noticeMessage : 'You are applying these changes to many clips. Would you like to proceed?',

    init : function () {
        this.manyClipsBox = $( this.manyClipsBoxSelector );
        this.saveButton = $( this.saveButtonSelector );
        if ( this.isActiveState() ) {
            // multi clip save
            this.bindSubmitButton();
        }else{
            // one clip save
            this.bindSubmitButtonOneClip();
        }

    },

    isActiveState : function () {
        return ( this.manyClipsBox && this.manyClipsBox.length );
    },

    bindSubmitButton : function () {
        this.saveButton.on( 'click', function () {
            if ( !confirm( savingNotice.noticeMessage ) ) {
                // reset Modified and Save button on session
                sessionStorage.removeItem('KeywordsModified');
                sessionStorage.removeItem('KeywordsSaveButton');
                sessionStorage.removeItem('keywordTemplateId');
                $('input[name^="overwrite"]' ).each(function (field){
                    sessionStorage.removeItem(['overwriteFieldsChecked['+ $(this).val() +']']);
                });
                event.preventDefault();
                event.stopPropagation();
            }
        } );
    },

    bindSubmitButtonOneClip : function () {
        this.saveButton.on( 'click', function () {
            sessionStorage['saveCliplogEdit']=true;
        } );
    }

};