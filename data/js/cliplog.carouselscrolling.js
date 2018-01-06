$(document).ready(function () {
    // Кнопки для переключения не след. и пред. клип
    var carouselList = $('#clips_carousel');
    var carouselItems = carouselList.find('.jcarousel-item');
    if (carouselItems.length > 1) {
        var clipBox = $('.cliplog-edit-clip-box');
        var prevButton = clipBox.find('.prev-clip');
        var nextButton = clipBox.find('.next-clip');
        if (carouselItems.last().is('.active')) {
            nextButton.addClass('disabled');
            //carouselDinamic.nextAdd();
        } else {
            nextButton.click(function () {
                carouselList.find('.jcarousel-item.active').next().find('a').click();
            });
        }
        if (carouselItems.first().is('.active')) {
            prevButton.addClass('disabled');
            //carouselDinamic.prevAdd();
        } else {
            prevButton.click(function () {
                carouselList.find('.jcarousel-item.active').prev().find('a').click();
            });
        }
    }


    $('.jcarousel-scrolling').click(function () {
        $('#crousalCheckFirst').val('2');
    });
    $('.jcarousel-prev').click(function () {
        $('#crousalCheckFirst').val('2');

    });
    $('.jcarousel-next').click(function () {
        $('#crousalCheckFirst').val('2');
    });


});

$(window).load(function () {
    var ignoreScrollingTrigger = false;
    var scrollingPattern = '<div class="jcarousel-scrolling ios-scrollable"><div class="indicator"></div></div>';
    var carouselContainer = $('.jcarousel-container');
    var carouselList = $('#clips_carousel');
    var carouselItems = carouselList.find('.jcarousel-item');
    var carouselItemsCount = carouselItems.length;
    var showScrolling = (carouselItemsCount && carouselItemsCount > 3) ? true : false;
    // Нужен ли скроллинг
    if (showScrolling) {
        // Добавляем скроллинг
        carouselContainer.append(scrollingPattern);
        var carouselInstance = carouselList;
        var carouselScrolling = $('.jcarousel-scrolling');
        var carouselScrollingIndicator = carouselScrolling.find('.indicator');
        // Определяем смещение карусели
        var carouselClipWidth = carouselItems.first().innerWidth();
        var clipsOffsetCounter = 0;
        var carouselClipsOffset = 0;
        var findActiveClip = true;
        carouselItems.each(function () {
            if (findActiveClip) {
                if ($(this).is('.active')) {
                    findActiveClip = false;
                    carouselClipsOffset = clipsOffsetCounter;
                }
                clipsOffsetCounter++;
            }
        });
        // Задаем ширину индикатора
        var scrollingWidth = carouselScrolling.width();
        var clipsOneWidth = scrollingWidth / 3;
        carouselScrollingIndicator.width(clipsOneWidth * carouselItemsCount);
        // Перемещаем скроллинг
        if (carouselClipsOffset > 0) {
            carouselScrolling.scrollLeft(carouselClipsOffset * carouselClipWidth);
        }
        // Определяем полную ширину скролла
        var scrollingActualWidth = (clipsOneWidth * carouselItemsCount) - scrollingWidth;
        // Определяем позиции клипов в карусели
        var clipActualWidth = scrollingActualWidth / (carouselItemsCount - 1);
        // Обработчик для прокрутки скроллинга
        carouselScrolling.on('scroll', function () {
            if (!ignoreScrollingTrigger) {
                var moveToTimeout;
                var lastPosition = carouselScrolling.scrollLeft();
                moveToTimeout = setTimeout(function () {
                    var currentPosition = carouselScrolling.scrollLeft();
                    if (currentPosition == lastPosition) {
                        var showClip = Math.round(carouselScrolling.scrollLeft() / clipActualWidth);
                        carouselInstance.jcarousel('scroll', showClip);
                        clearTimeout(moveToTimeout);
                    } else {
                        clearTimeout(moveToTimeout);
                    }
                }, 300);
            } else {
                ignoreScrollingTrigger = false;
            }
        });
        // Обработчик для прокрутки карусели
        var carouselStartPosition = carouselInstance.offset().left;
        $('.jcarousel-next, .jcarousel-prev').on('click', function () {
            setTimeout(function () {
                ignoreScrollingTrigger = true;
                var clipsOffset = carouselInstance.offset();
                var scrollTo = (carouselStartPosition - clipsOffset.left) + (carouselClipWidth * carouselClipsOffset);
                carouselScrolling.scrollLeft(scrollTo);
            }, 400);
        });
    }
    // Dinamic add items to the carousel
    carouselDinamic.init();

    //Move the scroll to the selected Point
    setTimeout(function () {
        var clipsOffsetCounter = 0;
        var carouselClipsOffset = 0;
        var findActiveClip = true;
        carouselItems.each(function () {
            if (findActiveClip) {
                if ($(this).is('.active')) {
                    findActiveClip = false;
                    carouselClipsOffset = clipsOffsetCounter;
                }
                clipsOffsetCounter++;
            }
        });
        $('#clips_carousel').jcarousel('scroll', carouselClipsOffset);
    }, 3000);
});

var carouselDinamic = {
    settings: {
        items: 0,
        current: 0
    },
    init: function () {
        var that = this;
        if (!$('#footagesearch-clip-more-one').length) {
            this.settings.items = $('.jcarousel-item').length;
            this.settings.current = $('.jcarousel-item.active').attr('jcarouselindex');
            console.log(this.settings);
            var clipId = $('#clips_carousel .carousel_link').last().attr('href');
            if (typeof clipId == 'undefined') {
                clipId = window.location.href;
            }
            clipId = clipId.replace(/\D/g, '');
            this.getItems(clipId, 'left', 'reset');
            setInterval(function () {
                var prev = $('.jcarousel-prev');
                var next = $('.jcarousel-next');
                if (prev.hasClass('jcarousel-prev-disabled')) {
                    prev.removeClass('jcarousel-prev-disabled');
                    prev.removeClass('jcarousel-prev-disabled-horizontal');
                    prev.removeProp('disabled');
                    prev.removeAttr('disabled');
                    that.prevAdd();
                } else if (next.hasClass('jcarousel-next-disabled')) {
                    next.removeClass('jcarousel-next-disabled');
                    next.removeClass('jcarousel-next-disabled-horizontal');
                    next.removeProp('disabled');
                    prev.removeAttr('disabled');
                    that.nextAdd();
                }
            }, 1000);
            this.hideCopyKeywordsWhenNoPrevClip();
            this.copyClipIdFromNextClipToForm();
        }
    },
    copyClipIdFromNextClipToForm: function (){
        var nextClipUrl = $('#clips_carousel').find('.jcarousel-item.active').next().find('a').attr('href');
        if (nextClipUrl){
            var nextClipId = this.getClipIdFromUrl(nextClipUrl);
            if(nextClipId){
                $('.goto-next').val(nextClipId);
            }
        } else {
            var curClipUrl = window.location.href;
            var curClipId = this.getClipIdFromUrl(curClipUrl);
            if(curClipId){
                $('.goto-next').val(curClipId);
            }
        }

    },
    getClipIdFromUrl: function(url){
        var clipUrlArray = url.split('/');
        var clipId = Number(clipUrlArray[clipUrlArray.length - 1]);
        if (clipId > 0)
        {
            return clipId;
        } else {
            return false;
        }
    },
    nextAdd: function () {
        var clipId = $('#clips_carousel .carousel_link').last().attr('href');
        if (typeof clipId == 'undefined') {
            clipId = window.location.href;
        }
        clipId = clipId.replace(/\D/g, '');
        this.getItems(clipId, 'right');
    },
    hideCopyKeywordsWhenNoPrevClip: function (){
        var carouselList = $("#clips_carousel");
        var activeCarouselItem = carouselList.find(".jcarousel-item.active");
        var prevItem = activeCarouselItem.prev().find("a").attr("href");
        if (!prevItem){
            $('.copy_prev_keyqords').hide();
        } else {
            $('.copy_prev_keyqords').show();
        }
    },
    prevAdd: function () {
        var clipId = $('#clips_carousel .carousel_link').first().attr('href');
        if (typeof clipId == 'undefined') {
            clipId = window.location.href;
        }
        clipId = clipId.replace(/\D/g, '');
        this.getItems(clipId, 'left');
    },
    getItems: function (clipId, side, reset) {
        var that = this;
        var data = (reset == undefined) ? {
            side: side
        } : {
            side: side,
            reset: 1
        };
        //console.log('getItems ' + clipId + ' ' + side + ' ' + reset);
        $.ajax({
            type: 'POST',
            url: 'en/cliplog/index/getcarouselitems/' + clipId,
            dataType: 'json',
            data: data,
            cache: false,
            async: true,
            success: function (response) {
                // console.log(response);
                if (response.status) {
                    var html = '';
                    var htmlNew = '';
                    var start = 0;
                    // old items
                    $('.jcarousel-item').each(function () {
                        var activeClass = ($(this).hasClass('active')) ? ' class="active"' : '';
                        html += '<li' + activeClass + '>' + $(this).html() + '</li>';
                        start = start + 1;
                    });

                    //var valuearr = [];
                    //
                    //$('.jcarousel-item').each(function () {
                    //    var test = $($(this)).find('a').attr('att-code');
                    //    valuearr.push(test);
                    //
                    //});
                    //
                    //console.log(valuearr);

                    $.each(response.items, function (k, v) {

                        ///  if ($.inArray(v.code, valuearr)) {
                        // start = start - 1;
                        //    console.log('exits in array');
                        //     return true;

                        // } else {
                        var code = v.code;

                        htmlNew += '<li><div class="footagesearch-clip-code">' + code + '</div><a href="en/cliplog/edit/' + v.id + '" class="carousel_link containerGridGallery" att-preview="' + v.motion_thumb + '" att-descp="' + v.description + '" att-code="' + code + '">' +
                            '<img src="' + v.thumb + '" width="200" height="112"></a></li>';

                        //           }

                    });

                    switch (side) {
                        case 'left':
                            html = htmlNew + html;
                            break;
                        case 'right':
                            html = html + htmlNew;
                            break;
                    }
                    $('.clips_carousel_cont').html('<ul id="clips_carousel" class="jcarousel-skin-tango" >' + html + '</ul>');
                    $('.clips_carousel_cont #clips_carousel').jcarousel({
                        start: start,
                        scroll: 1,
                        itemFallbackDimension: 200
                    });

                    $('.containerGridGallery').on('mouseover', function () {
                        $('.videohover').css('opacity', 1);
                        var videourl = $(this).attr('att-preview');
                        var code = $(this).attr('att-code');
                        var descp = $(this).attr('att-descp');

                        var videoembed = '<div style="width:100%;border: 1px solid #ccc;padding:10px;background-color:#ffffff;border-radius:5px;float:left"><h6 class="title">' + code + '</h6><video width="100%" height="100%" poster="http://backend.nfstage.com/data/img/loading2-video.gif" preload="none" autoplay muted><p>Sorry, your browser does not support HTML5 video.</p><source src="' + videourl + '" type="video/mp4"></video><span>' + descp + '</span></div>';
                        // console.log(videoembed);
                        $('.videohover').html(videoembed);
                        if (window.stop !== undefined) {
                            // window.stop();
                        } else if (document.execCommand !== undefined) {
                            document.execCommand("Stop", false);
                        }

                    }).on('mouseout', function () {
                        $('.videohover').css('opacity', 0).empty();
                    });


                    console.log('Start:' + (start));
                    that.scrollBar(start);


                    if ($('#crousalCheckFirst').val() > 1) {
                        setTimeout(function () {
                            var carouselList = $('#clips_carousel');
                            var carouselItems = carouselList.find('.jcarousel-item');
                            var carouselItemsCount = carouselItems.length;
                            var clipsOffsetCounter = 0;
                            var carouselClipsOffset = 0;
                            var findActiveClip = true;
                            carouselItems.each(function () {
                                if (findActiveClip) {
                                    if ($(this).is('.active')) {
                                        findActiveClip = false;
                                        carouselClipsOffset = clipsOffsetCounter;
                                    }
                                    clipsOffsetCounter++;
                                }
                            });
                            $('#clips_carousel').jcarousel('scroll', carouselClipsOffset);
                        }, 3000);
                    }
                    if (side == 'left') {
                        //console.log('Start:' + (response.limitImi));
                        //that.scrollBar(response.limitImi);
                        //setTimeout(function () {
                        //    var clipsOffsetCounter = 0;
                        //    var carouselClipsOffset = 0;
                        //    var findActiveClip = true;
                        //    $('#clips_carousel').jcarousel('scroll', response.limitImi);
                        //}, 1000);
                    } else {
                        //console.log('Start:' + (start));
                        //that.scrollBar(start);
                        setTimeout(function () {
                            var clipsOffsetCounter = 0;
                            var carouselClipsOffset = 0;
                            var findActiveClip = true;
                            $('#clips_carousel').jcarousel('scroll', start);
                        }, 1000);
                    }


                }
            }
        });
    },
    scrollBar: function (curClip) {
        var ignoreScrollingTrigger = false;
        var scrollingPattern = '<div class="jcarousel-scrolling ios-scrollable"><div class="indicator"></div></div>';
        var carouselContainer = $('.jcarousel-container');
        var carouselList = $('#clips_carousel');
        var carouselItems = carouselList.find('.jcarousel-item');
        var carouselItemsCount = carouselItems.length;
        var showScrolling = (carouselItemsCount && carouselItemsCount > 3) ? true : false;
        // Нужен ли скроллинг
        if (showScrolling) {
            // Добавляем скроллинг
            carouselContainer.append(scrollingPattern);
            var carouselInstance = carouselList;
            var carouselScrolling = $('.jcarousel-scrolling');
            var carouselScrollingIndicator = carouselScrolling.find('.indicator');
            // Определяем смещение карусели
            var carouselClipWidth = carouselItems.first().innerWidth();
            var clipsOffsetCounter = 0;
            var carouselClipsOffset = 0;
            var findActiveClip = true;
            if (curClip == undefined) {
                carouselItems.each(function () {
                    if (findActiveClip) {
                        if ($(this).is('.active')) {
                            findActiveClip = false;
                            carouselClipsOffset = clipsOffsetCounter;
                        }
                        clipsOffsetCounter++;
                    }
                });
            } else {
                carouselClipsOffset = clipsOffsetCounter = curClip;
            }
            // Задаем ширину индикатора
            var scrollingWidth = carouselScrolling.width();
            var clipsOneWidth = scrollingWidth / 3;
            carouselScrollingIndicator.width(clipsOneWidth * carouselItemsCount);
            // Перемещаем скроллинг
            if (carouselClipsOffset > 0) {
                carouselScrolling.scrollLeft(carouselClipsOffset * carouselClipWidth);
            }
            // Определяем полную ширину скролла
            var scrollingActualWidth = (clipsOneWidth * carouselItemsCount) - scrollingWidth;
            // Определяем позиции клипов в карусели
            var clipActualWidth = scrollingActualWidth / (carouselItemsCount - 1);
            // Обработчик для прокрутки скроллинга
            carouselScrolling.on('scroll', function () {
                if (!ignoreScrollingTrigger) {
                    var moveToTimeout;
                    var lastPosition = carouselScrolling.scrollLeft();
                    moveToTimeout = setTimeout(function () {
                        var currentPosition = carouselScrolling.scrollLeft();
                        if (currentPosition == lastPosition) {
                            var showClip = Math.round(carouselScrolling.scrollLeft() / clipActualWidth);
                            console.log(showClip);
                            carouselInstance.jcarousel('scroll', showClip);
                            clearTimeout(moveToTimeout);
                        } else {
                            clearTimeout(moveToTimeout);
                        }
                    }, 300);
                } else {
                    ignoreScrollingTrigger = false;
                }
            });
            // Обработчик для прокрутки карусели
            var carouselStartPosition = carouselInstance.offset().left;
            $('.jcarousel-next, .jcarousel-prev').on('click', function () {
                setTimeout(function () {
                    ignoreScrollingTrigger = true;
                    var clipsOffset = carouselInstance.offset();
                    var scrollTo = (carouselStartPosition - clipsOffset.left) + (carouselClipWidth * carouselClipsOffset);
                    carouselScrolling.scrollLeft(scrollTo);
                }, 400);
            });
        }
    }
};
