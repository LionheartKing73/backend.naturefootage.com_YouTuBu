var ktool;

function loadKeywords(clipID) {
    // window.alert(ktool.getKeywords());
    /*var fragments = $.parseJSON(ktool.getKeywords());*/
    /*for (var keyVar in fragments) {
     alert(keyVar);
     }*/
    // var fragments = '{"fps":25, "keywords":{"this is bad":{"fragments":[{"in":1200, "out":1281},{"in":635, "out":896}]}, "Lost and forgotten":{"fragments":[{"in":516, "out":1281}]}, "This is true lost":{"fragments":[{"in":250, "out":1281}]}, "lost":{"fragments":[{"in":75, "out":1281}]}}}';
    // var jsonData = $.parseJSON(fragments);
    // ktool.setKeywords(jsonData);

    $.ajax({
        type: "GET",
        cache: false,
        url: "/en/keywording/load/" + clipID,
        data: {},
        dataType: "json",
        success: function (result) {
            if(result.file_info.duration && result.file_info.frame_rate){
                ktool = KToolUi.createKToolUi("testDIV");
                var ext = (window.navigator.userAgent.indexOf("Firefox") != -1) ? ".webm" : ".mp4";
                var videoPath = "/data/upload/resources/clip/preview/" + clipID + ext;
                ktool.setVideoElement(videoPath, result.file_info.duration, result.file_info.frame_rate);
                ktool.setKeywords(result.keywords_data);
            }
        }
    });

}

function saveKeywords() {

    var parentUri = window.parent.location.pathname;
    var clipID = parentUri.substr(parentUri.lastIndexOf("/") + 1).replace(".html", "");
    var keywords = ktool.getKeywords();

    $.ajax({
        type: "POST",
        cache: false,
        url: "/en/keywording/save/" + clipID,
        data: {
            keywords: keywords
        },
        success: function (result) {
            // alert(result);
            // var jsonData = $.parseJSON(result);
            // ktool.setKeywords(result);
            $("#keywordsSavedAlert").show("fast");
            $("#keywordsSavedAlert").alert();

            setTimeout(function() {
                $("#keywordsSavedAlert").hide("fast");
            }, 2000);
        }
    });

}

function sliceVideo() {

    var parentUri = window.parent.location.pathname;
    var clipID = parentUri.substr(parentUri.lastIndexOf("/") + 1).replace(".html", "");
    var keywords = ktool.getKeywords();

    $.ajax({
        type: "POST",
        cache: false,
        url: "/en/keywording/slice/" + clipID,
        data: {
            keywords: keywords
        },
        success: function (result) {
            $('#progressbarCont').show();
            getProgress(clipID);
        }
    });

}


function getProgress(clipID) {
    if(clipID){
        $.ajax({
            url: '/en/keywording/status/' + clipID,
            dataType : 'json',
            cache: false,
            type: 'GET',
            success: function (data) {
                if(data && data.percent){
                    $('#progressbarPercentValue').html(data.percent);
                    $('#progressbarKeyword').html(data.keyword);
                    $('#ktool_progressbar').progressbar("value", data.percent);
                    //$('#ktool_progressbar').animate_progressbar(data.percent);
                }
                if (!data || data.percent < 100) {
                    setTimeout(function(){getProgress(clipID)}, 1000);
                } else {
                    isDone = true;
                    $('#progressbarCont').hide();
                    getChildClips(clipID);
                }
            }
        });
    }
}

function getChildClips(clipID){
    if(clipID){
        $.ajax({
            url: '/en/keywording/clips/' + clipID,
            type: 'GET',
            cache: false,
            success: function (data) {
                if(data){
                    $('#clipsList', window.parent.document).html(data).show();
                }
            }
        });
    }
}

$(window).load(function() {
    setTimeout(function() {
		var parentUri = window.parent.location.pathname;
        loadKeywords(parentUri.substr(parentUri.lastIndexOf("/") + 1).replace(".html", ""));
    }, 1000);
});

