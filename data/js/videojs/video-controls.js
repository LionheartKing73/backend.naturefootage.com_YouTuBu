(function ($) {

    var preview_opens = 0;

    function clickPlayBtn(element) {
        var isOpen = $('#footagesearch-clip-preview-dialog').dialog('isOpen');
        if (isOpen) {
            //$( '#footagesearch-clip-preview-dialog' ).dialog( 'close' );
        } else {
            if (element.is('[data-clip]')) {
                preview_opens++;
                var clipInfo = eval("(" + element.attr('data-clip') + ")"),
                    clipPreview = '<div class="footagesearch-clip-preview">',
                    description = '',
                    parent = element.parents('.footagesearch-clip');
                clipPreview += '<h1 class="footagesearch-clip-preview-title">' + clipInfo.title + '</h1>';
                clipPreview += '<video id="footagesearch-preview-player' + clipInfo.id + '_' + preview_opens + '" class="video-js vjs-default-skin" preload="auto" muted width="432" height="240" data-setup="{}">';
                clipPreview += '<source src="' + clipInfo.motion_thumb + '" type="video/mp4" />';
                clipPreview += '</video>';

                if (clipInfo.description)
                    description += '<p class="footagesearch-clip-preview-description">' + clipInfo.description + '</p>';
                clipPreview += description;
                clipPreview += '</div>';
                $('#footagesearch-clip-preview-dialog').html(clipPreview);

                $('#footagesearch-clip-preview-dialog').dialog('option', 'position', {
                    my: "middle bottom - 15",
                    at: "middle top",
                    using: function (pos, ui) {
                        var parentMiddle = parent.offset().top + (parent.height() / 2),
                            parentRight = parent.offset().left + parent.width(),
                            parentLeft = parent.offset().left,
                            height = $(this).height(),
                            width = $(this).width();

                        if (pos.top + height - 15 > parent.offset().top) {
                            pos.top = parentMiddle - (height / 2);
                            pos.left = parentRight + 1;
                        }
                        if ((pos.top < $(window).scrollTop())) {
                            pos.top = $(window).scrollTop();
                        }
                        if (pos.left + width > $(window).width()) {
                            pos.left = parentLeft - width;
                        }
                        alert('parentMiddle:' + parentMiddle + ' parentRight:' + parentRight + ' parentLeft:' + parentLeft + ' Height:' + height + ' width:' + width + ' pos.top:' + pos.top + ' pos.left:' + pos.left);
                        $(this).css(pos);
                    },
                    of: element.parents('.footagesearch-clip')
                }).dialog('open').siblings('.ui-dialog-titlebar').remove();
                playPlayer('footagesearch-preview-player' + clipInfo.id + '_' + preview_opens);
            }
            element.hide();
            var pauseBtn = element.parent().find('.footagesearch-clip-pause-btn');
            pauseBtn.show();
        }
    }

    function clickPauseBtn($element) {
        var isOpen = $('#footagesearch-clip-preview-dialog').dialog('isOpen');
        if (isOpen) {
            $('#footagesearch-clip-preview-dialog').dialog('close');
        }
        $element.hide();
        var playBtn = $element.parent().find('.footagesearch-clip-play-btn');
        playBtn.show();
    }

    function playPlayer(playerID) {
        try {
            var player = videojs(playerID);
            player.play();
        } catch (e) {
            console.log(e.name);
        }
    }

    function pausePlayer(playerID) {
        var player = videojs(playerID);
        player.pause();
    }

    function forwardPlayer(playerID, speed) {
        var video = document.getElementById(playerID + '_html5_api');
        if (video) {
            if (!speed) {
                var speeds = [1.0, 2.0, 4.0];
                var currentSpeed = video.playbackRate;
                var speed = 1.0;
                for (var i = 0; i < speeds.length; i++) {
                    if (speeds[i] == currentSpeed && speeds[i + 1] !== undefined) {
                        speed = speeds[i + 1];
                        break;
                    }
                }
            }
            video.playbackRate = speed;
        }
    }

    $(document).ready(function () {

        //$('.footagesearch-clip-info-icon').on('click', function(e){
        $('.footagesearch-clip-inner .info a').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var href = $(this).attr('href') + '?modal=1&backend_clipbin=' + $(this).data('bin-id');//$(this).parent().attr('href') + '?modal=1';
            var windowWith = 660;
            var windowHeight = 700;
            var leftPosition = (screen.width - windowWith) / 2;
            var topPosition = (screen.height - windowHeight) / 2;
            var clipWindow = window.open(
                href,
                'ClipInfo',
                'width=' + windowWith + ',height=' + windowHeight + ',top=' + topPosition + ',left=' + leftPosition + ',resizable=yes,scrollbars=yes,status=yes'
            );
            //Hide preview box if click on clip
            $('#footagesearch-clip-preview').parent().hide();
            clipWindow.focus();
        });

        $('.footagesearch-clip').on('touchend', '.footagesearch-clip-inner', function () {
            var href = $(this).find('.footagesearch-clip-info-icon').parent().attr('href') + '?modal=1';
            window.location = href;
            e.preventDefault();
            e.stopPropagation();
        });

        $('.footagesearch-clip-preview-play-btn').on('click', function (e) {
            e.stopPropagation();
            $(this).hide();
            var pauseBtn = $(this).parent().find('.footagesearch-clip-preview-pause-btn');
            pauseBtn.show();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            playPlayer(id);
        });

        $('.footagesearch-clip-preview-pause-btn').on('click', function (e) {
            e.stopPropagation();
            $(this).hide();
            var playBtn = $(this).parent().find('.footagesearch-clip-preview-play-btn');
            playBtn.show();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            pausePlayer(id);
            //console.log(videojs(id).controlBar.hide());
        });

        $('.footagesearch-clip-preview-forward-btn').on('click', function (e) {
            e.stopPropagation();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            forwardPlayer(id, 2.0);
        });

        $('.footagesearch-clip-preview-forward3x-btn').on('click', function (e) {
            e.stopPropagation();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            forwardPlayer(id, 3.0);
        });

    });

})(jQuery);


// Новый обработчик для превью клипов

$(document).ready(function () {
    clipPreview.init();
});

var clipPreview = {

    init: function () {
        this.initEnvironment();
        this.bindPreviewBox();
        this.bindPlayButtons();
        this.bindPauseButtons();
        this.bindSpeedButtons();
    },

    showPreviewTimeout: null,

    previewBoxName: '#footagesearch-clip-preview',
    previewBox: null,

    clipsInnerBoxesName: '.footagesearch-clip-inner',
    clipsPlayButtonsName: '.footagesearch-clip-play-btn',
    clipsPlayX2ButtonsName: '.footagesearch-clip-forward-btn',
    clipsPlayX3ButtonsName: '.footagesearch-clip-forward3x-btn',
    clipsPauseButtonsName: '.footagesearch-clip-pause-btn',
    clipsInnerBoxes: null,
    clipsPlayButtons: null,
    clipsPlayX2Buttons: null,
    clipsPlayX3Buttons: null,
    clipsPauseButtons: null,

    previeBoxVideoIdPrefix: 'footagesearch-preview-player',
    previewBoxVideo: null,
    previewBoxTitle: null,
    previewBoxDescription: null,
    previewBoxId: null,

    activeClipBox: null,
    activeClipData: {},
    videoContainer: {},

    initEnvironment: function () {
        this.previewBox = $(this.previewBoxName);
        this.clipsInnerBoxes = $(this.clipsInnerBoxesName);
        this.clipsPlayButtons = $(this.clipsPlayButtonsName);
        this.clipsPlayX2Buttons = $(this.clipsPlayX2ButtonsName);
        this.clipsPlayX3Buttons = $(this.clipsPlayX3ButtonsName);
        this.clipsPauseButtons = $(this.clipsPauseButtonsName);
        this.previewBoxVideo = this.previewBox.find('video');
        this.previewBoxTitle = this.previewBox.find('.title');
        this.previewBoxID = this.previewBox.find('.title_id');
        this.previewBoxDescription = this.previewBox.find('.description');
    },

    bindPreviewBox: function () {
        var that = this;
        that.previewBox.dialog({
            autoOpen: false,
            width: 458,
            show: {effect: 'fadeIn', duration: 100},
            close: function () {
            },
            open: function () {
                $(this).siblings('.ui-dialog-titlebar').remove();
            }
        });
        that.clipsInnerBoxes.on('mouseenter', function (event) {
            that.showPreviewTimeout = setTimeout(function () {
                if (!that.isPreviewOpen()) {
                    that.findActiveClipBox(event);
                    that.showPreviewBox();
                    that.hidePlayButton();
                    that.showPauseButton();
                    that.playPreview();
                }
            }, 400);
        }).on('mouseleave', function (event) {
            clearTimeout(that.showPreviewTimeout);
            that.findActiveClipBox(event);
            that.hidePauseButton();
            that.showPlayButton();
            that.hidePreviewBox();
            that.pausePreview();
            $('.description').html('');
        });
    },

    bindSpeedButtons: function () {
        var that = this;
        that.clipsPlayX2Buttons.on('click', function (event) {
            that.findActiveClipBox(event);
            if (!that.isPreviewOpen()) {
                that.showPreviewBox();
            }
            that.hidePlayButton();
            that.showPauseButton();
            that.playPreview();
            that.setVideoSpeed(2.0);
        });
        that.clipsPlayX3Buttons.on('click', function (event) {
            that.findActiveClipBox(event);
            if (!that.isPreviewOpen()) {
                that.showPreviewBox();
            }
            that.hidePlayButton();
            that.showPauseButton();
            that.playPreview();
            that.setVideoSpeed(3.0);
        });
    },

    bindPlayButtons: function () {
        var that = this;
        that.clipsPlayButtons.on('click', function (event) {
            that.findActiveClipBox(event);
            that.playPreview();
            that.hidePlayButton();
            that.showPauseButton();
            if (!that.isPreviewOpen()) {
                that.showPreviewBox();
            }
        });
    },

    bindPauseButtons: function () {
        var that = this;
        that.clipsPauseButtons.on('click', function (event) {
            that.findActiveClipBox(event);
            that.hidePauseButton();
            that.showPlayButton();
            that.pausePreview();
        });
    },

    findActiveClipBox: function (event) {
        this.activeClipBox = $(event.currentTarget).closest('.footagesearch-clip');
        this.activeClipData = eval(
            "(" + this.activeClipBox.find(this.clipsPlayButtonsName).attr('data-clip') + ")"
        );
    },

    isPreviewOpen: function () {
        return !!this.previewBox.dialog('isOpen')
    },

    showPlayButton: function () {
        this.activeClipBox.find(this.clipsPlayButtonsName).show();
    },

    hidePlayButton: function () {
        this.activeClipBox.find(this.clipsPlayButtonsName).hide();
    },

    showPauseButton: function () {
        this.activeClipBox.find(this.clipsPauseButtonsName).show();
    },

    hidePauseButton: function () {
        this.activeClipBox.find(this.clipsPauseButtonsName).hide();
    },

    playPreview: function () {
        try {
            this.setVideoSpeed(1);
            this.videoContainer.play();
        } catch (e) {
        }
    },

    pausePreview: function () {
        try {
            this.videoContainer.pause();
        } catch (e) {
        }
    },

    showPreviewBox: function () {
        this.previewBoxVideo.attr('id', this.previeBoxVideoIdPrefix + this.activeClipData.id);
        this.previewBoxTitle.html(this.activeClipData.title);
        this.previewBoxID.html(this.activeClipData.id);
        $.ajax({
            url: "ajax.php",
            data: {action: 'getClipDescription', clipId: this.activeClipData.id},
            type: "POST",
            success: function (data) {
                $('.description').html('');
                $('.description').html(data);
            }
        });
        //noinspection JSUnresolvedVariable
        this.previewBoxVideo.attr('src', this.activeClipData.motion_thumb);
        try {
            this.videoContainer = videojs(this.previeBoxVideoIdPrefix + this.activeClipData.id);
        } catch (e) {
        }
        this.previewBox.dialog('open');
        this.setPreviewPosition();
    },

    hidePreviewBox: function () {
        this.previewBox.dialog('close');
    },

    setPreviewPosition: function () {
        var that = this;
        //noinspection JSValidateTypes
        var windowScrollTop = $(window).scrollTop();
        that.previewBox.dialog('option', 'position', {
            my: 'middle bottom-15',
            at: 'middle top',
            using: function (pos) {
                var parentMiddle = that.activeClipBox.offset().top + ( that.activeClipBox.height() / 2 );
                var parentRight = that.activeClipBox.offset().left + that.activeClipBox.width();
                var parentLeft = that.activeClipBox.offset().left;
                var height = $(this).height();
                var width = $(this).width();


                var isFramed = false;
                try {
                    isFramed = window != window.top || document != top.document || self.location != top.location;
                } catch (e) {
                    isFramed = true;
                }
                var frameOffset = (isFramed) ? 60 : 0;
                if (pos.top + height - 15 > that.activeClipBox.offset().top) {
                    pos.top = parentMiddle - (height / 2) + frameOffset;
                    pos.left = parentRight + 1;
                }
                if ((pos.top < windowScrollTop) && !isFramed) {

                    pos.top = windowScrollTop - frameOffset;
                }

                if (pos.left + width > $(window).width()) {
                    pos.left = parentLeft - width + frameOffset;
                }
                /*if ( isFramed ) {
                 pos.top = parentMiddle - (height / 2) + 50;
                 pos.left = parentRight + 1;
                 }
                 if ( isFramed && (pos.left + width > $( window ).width())) {
                 pos.top = parentMiddle - (height / 2) + 50;
                 pos.left = parentLeft - width;
                 }*/
                $(this).css(pos);
            },
            of: that.activeClipBox
        });
    },

    setVideoSpeed: function (speed) {
        var video = document.getElementById(this.previeBoxVideoIdPrefix + this.activeClipData.id);
        if (video) {
            if (!speed) {
                speed = 1;
            }
            video.playbackRate = speed;
        }
    }

};