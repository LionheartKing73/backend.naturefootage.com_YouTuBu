/* Select clip */
function selectClip(obj) {
  var chk = $(obj);
  if(chk.attr("checked")){
    chk.parent().addClass("selected");
  } else {
    chk.parent().removeClass("selected");
  }
}


/* Play motion thumbnail */
function playMotionThumb(obj) {
    var jObj = $(obj);

    var clipInfo = eval("(" + jObj.parent().find("input:first").val() + ")");
    var thumbPlayerId = "thumbPlayer" + clipInfo.id;

    createPlayer(clipInfo.motion_thumb, 200, 112, thumbPlayerId, true, true);

}

/* Show clip info */

windowInnerSize = null;

function getWindowInnerSize() {
    if (!windowInnerSize) {
        var myWidth = 0, myHeight = 0;
        if( typeof( window.innerWidth ) == 'number' ) {
            //Non-IE
            myWidth = window.innerWidth;
            myHeight = window.innerHeight;
        } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
            //IE 6+ in 'standards compliant mode'
            myWidth = document.documentElement.clientWidth;
            myHeight = document.documentElement.clientHeight;
        } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
            //IE 4 compatible
            myWidth = document.body.clientWidth;
            myHeight = document.body.clientHeight;
        }
        windowInnerSize = new Array(myWidth, myHeight);
    }
    return windowInnerSize;
}

var timerId = 0;
var clipDescriptions = new Array();

function showClipInfo(e, id, lang) {
    if(!lang){
        var lang = 'en';
    }
    clearTimeout(timerId);
    var popup = document.getElementById("infoPopup");

    if (!popup) {
        popup = document.createElement("DIV");
        popup.id = "infoPopup";
        popup.style.position = "absolute";
        popup.style.display = "none";
        document.body.appendChild(popup);
    }

    if (!clipDescriptions[id]) {
        $.ajax({
            url: lang + '/clips/info/'+ id,
            type:'GET',
            success: function (data, textStatus) {
                clipDescriptions[id] = data;

                popup.innerHTML = clipDescriptions[id];

                if (popup.style.display == "none") {
                    var posx = 0, posy = 0;

                    if (e.pageX || e.pageY) {
                        posx = e.pageX;
                        posy = e.pageY;
                    }
                    else if (e.clientX || e.clientY) {
                        posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                        posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
                    }

                    posx += 10;
                    posy += 10;

                    if ((posy + 220) > getWindowInnerSize()[1]) {
                        posy -= 220;
                    }

                    popup.style.left = posx + "px";
                    popup.style.top = posy + "px";
                    popup.style.display = "block";
                }
            }
        });
    }
    else{

        popup.innerHTML = clipDescriptions[id];

        if (popup.style.display == "none") {
            var posx = 0, posy = 0;

            if (e.pageX || e.pageY) {
                posx = e.pageX;
                posy = e.pageY;
            }
            else if (e.clientX || e.clientY) {
                posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
            }

            posx += 10;
            posy += 10;

            if ((posy + 220) > getWindowInnerSize()[1]) {
                posy -= 220;
            }

            popup.style.left = posx + "px";
            popup.style.top = posy + "px";
            popup.style.display = "block";
        }
    }
}

function hidePopup() {
    var popup = document.getElementById("infoPopup");
    if (popup) {
        popup.style.display = "none";
    }
}


function hideClipInfo() {
    clearTimeout(timerId);
    var popup = document.getElementById("infoPopup");
    if (popup) {
        timerId = window.setTimeout('hidePopup()', 500);
    }
}

//------------------------------------------------------------------------------
$(document).ready(function () {

    $(".clipMask")
        .click(function() {
            removePlayer($(this).parent().find(".imgWrapper").find("div").attr("id"));
            var clipInfo = eval("(" + $(this).parent().find("input:first").val() + ")");
            window.location = '/' + clipInfo.url;
        })
        .hover(
        function () {
            playMotionThumb(this);
        },
        function () {
            removePlayer($(this).parent().find(".imgWrapper").find("div").attr("id"));
        }
    );

    // Add to cart and bin events
    $(".toBin").click(function(){

        addToBin($(this), $(this).attr("href"));

        return false;

    });

    $(".toCart").click(function(){

        //addToCart($(this), $(this).attr("href"));
        addToCart($(this));

        return false;

    });

    /*
    if($('#filters') && $('#results_cont')){
        var filter_height = $('#filters').height();
        var content_height = $('#results_cont').height();
        if(content_height > filter_height){
            $('#filters').height(content_height);
        }
        else{
            $('#results_cont').height(filter_height);
        }
    }*/

});





