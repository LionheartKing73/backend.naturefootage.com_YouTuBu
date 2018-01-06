var cartItems = new Array();
var setKey = "";
//------------------------------------------------------------------------------
function playOtherClip(obj) {
  $("#clipsFeed div").css("display", "block");
  var thumb = $(obj);
  $(thumb).parent().css("display", "none");
  var clipCode = thumb.parent().find("p").html();

  for (var i = 0; i < cartItems[setKey].items.length; ++i) {
    if (cartItems[setKey].items[i].code == clipCode) {
      playSet(cartItems[setKey].items[i].id, 1);
      return;
    }
  }
}
//------------------------------------------------------------------------------
function fillOtherClips(clipNo) {
  otherClipsCount = cartItems[setKey]["items"].length - 1;
  $("#otherClipsCount").html(otherClipsCount);
  $("#clipsFeed div").remove();
  $("#clipsFeed").css("width", otherClipsCount * 100 + "px").css("left", 0);
  for (i = 0; i < cartItems[setKey]["items"].length; ++i) {
    $("#clipsFeed").append(
      $(document.createElement('div'))
        .append(
          $(document.createElement('img'))
            .attr("src", "/data/upload/resources/clip/" +
              cartItems[setKey]["items"][i].folder + "/thumb/" +
              cartItems[setKey]["items"][i].code + ".jpg")
            .click(function(){
              playOtherClip(this);
            })
        )
        .append(
          $(document.createElement('p'))
            .html(cartItems[setKey]["items"][i].code)
        )
        .css("display", (i == clipNo) ? "none" : "block")
    );
  }
}
//------------------------------------------------------------------------------
function playSet(clipId, otherClipsStatus) {
  var clipNo = 0;

  if (clipId) {
    for (i = 0; i < cartItems[setKey]["items"].length; ++i) {
      if (cartItems[setKey]["items"][i].id == clipId) {
        clipNo = i;
        break;
      }
    }
  }
  
  fillPlayPopup(cartItems[setKey]["items"][clipNo]);
  
  $("#additionalInfo").hide();
  removePlayer("videoPlayer");
  createPlayer("/data/upload/resources/clip/" +
    cartItems[setKey]["items"][clipNo].folder +
    "/preview/" + cartItems[setKey]["items"][clipNo].code + ".mp4", 640, 360,
    "videoPlayer", false, true);
  
  if (!otherClipsStatus) {
    fillOtherClips(clipNo);
  }

  $("#playPopup").dialog({
    title: cartItems[setKey]["items"][clipNo].title,
    width: 670,
    height: "auto",
    position: ["center", 30],
    modal: true,
    resizable: false,
    beforeClose: function() {
      $("#additionalInfo").hide();
      removePlayer("videoPlayer");
      if (itemAdded) {
        window.location.href = window.location.href;
      }
    },
    dragStart: function() { $("#additionalInfo").hide(); }
  });
}
//------------------------------------------------------------------------------
function playItem(obj) {
  var itemData = eval("(" + $(obj).parent().parent().find("input:first").val() + ")");
  setKey = "set" + itemData.setId;
  var clipId = (itemData.type == 2) ? itemData.id : 0;

  if (cartItems[setKey]) {
    playSet(clipId);
  }
  else {
    var setId = (itemData.type == 3) ? itemData.id : itemData.setId
    
    $.ajax({
      type: "POST",
      url: "/" + lang + "/search",
      dataType: "json",
      data: {"set" : setId},
      success: function(data) {
        cartItems[setKey] = data;
        playSet(clipId);
      }
    });
  }
}
//------------------------------------------------------------------------------
$(document).ready(function(){
  initMoreInfo();
  initOtherClips();
  $(".itemThumb img").click(function(){
    playItem(this);
  });
});