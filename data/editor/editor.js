/**
 * User: shyyko
 * Date: 11/7/11
 * Time: 4:41 PM
 */

/**
 * instance of Editor
 * @see Editor
 */
function Editor(getDroppableElementId, putAsset, setTimelineInfo, getTimelineInfo, getAssetsPanelId, getDroppableDroppableElements, prepareDroppableElements, hideDroppableElements, addToAssets, onLoaded, addElementToAssets, createEditor, onCanBeCreated, setInitParams, addExternalDropTarget, onSelectionChanged, setTimelineComposition, setAddingText, resizeToFillParent, setUpload, addExternalControlButton, selectTab) {
    this.getDroppableElementId = getDroppableElementId;
    this.putAsset = putAsset;
    this.setTimelineInfo = setTimelineInfo;
    this.getTimelineInfo = getTimelineInfo;
    this.getAssetsPanelId = getAssetsPanelId;
    this.getDroppableDroppableElements = getDroppableDroppableElements;
    this.prepareDroppableElements = prepareDroppableElements;
    this.hideDroppableElements = hideDroppableElements;
    this.addToAssets = addToAssets;
    this.onLoaded = onLoaded;
    this.addElementToAssets = addElementToAssets;
    this.createEditor = createEditor;
    this.onCanBeCreated = onCanBeCreated;
    this.setInitParams = setInitParams;
    this.addExternalDropTarget = addExternalDropTarget;//(dropTarget, functionToCall)
    this.onSelectionChanged = onSelectionChanged;
    this.setTimelineComposition = setTimelineComposition;
    this.setAddingText = setAddingText;
    this.setUpload = setUpload;
    this.resizeToFillParent = resizeToFillParent;
    this.addExternalControlButton = addExternalControlButton;//(String text,String css)
    this.selectTab = selectTab;//(int numberOfTab)
}

var loaded = false;
var clipsInfo;
var flatAssets;
var timelineAssets;
var timelineType = "timeline";
var flatType = "flat";
var nextId = 0;


editor = new Editor(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);

$(window).resize(function () {
    setTimeout(function () {
        if (editor.resizeToFillParent) {
            editor.resizeToFillParent();
        }
    }, 150);
});

editor.addToAssets = function (filename, startOffset, stopOffset, length, dataUrl, muted) {
    //todo implement
    window.alert(filename + " " + startOffset + " " + stopOffset +
        " " + length + " " + dataUrl + " " + muted);
};
var labelElement = null;
editor.onSelectionChanged = function (isEnabled, selectedPart) {
    if (selectedPart != null) {
        if (labelElement == null) {
            labelElement = document.createElement('div');
            document.body.appendChild(labelElement);
            //document.getElementById(editor.getAssetsPanelId()).appendChild(divElement);
        }
        labelElement.innerHTML = isEnabled + " " +
            selectedPart.id + " " +
            selectedPart.preview + " " +
            selectedPart.filename + " " +
            selectedPart.length_ + " " +
            selectedPart.muted + " " +
            selectedPart.startOffset + " " +
            selectedPart.stopOffset;
    }
};
editor.onLoaded = function () {
    if (document.getElementById("uploadPanel")) {
        document.getElementById("uploadPanel").innerHTML += "<div id=\"fileupload\">" +
            "<form action=\"./upload\" method=\"POST\" enctype=\"multipart/form-data\">" +
            "<div class=\"fileupload-buttonbar\">" +
            "<label class=\"fileinput-button\">" +
            "<span>Add files...</span>" +
            "<input type=\"file\" name=\"files[]\" multiple>" +
            "</label></div></form>" +
            "<div class=\"fileupload-content\"><table class=\"files\"></table>" +
            "<div id=\"fileupload-progressbar\" class=\"fileupload-progressbar\" style=\"width: 400px; height: 50px\">" +
            "</div></div></div>" +
            "<button id=\"saveTimelineButton\" style=\"width:60px; height: 20px;\">Save</button>" +
            "<div>Local files</div><input id=\"localFilesSelector\" type=\"file\" accept=\"video/*\"/>";
        $("#fileupload-progressbar").progressbar();
        $("#fileupload-progressbar").fadeOut();
        createUpload();
        createSaveButton();
    }
    flatAssets = [];
    timelineAssets = [];
    $.ajaxSetup({ cache:false });
    $.getJSON('en/editor/flatassets', function (data) {
        flatAssets = data;
        var type = flatType;
        for (var i = 0; i < data.length; i++) {
            createDiv(type + i, type, flatAssets[i].preview);
            makeDraggable(type + i);
        }
        editor.resizeToFillParent();
    });
    /*$.getJSON('./getTimelineAssets', function (data) {
        timelineAssets = data;
        var type = timelineType;
        for (var i = 0; i < data.length; i++) {
            createDiv(type + i, type);
            makeDraggable(type + i);
        }
        editor.resizeToFillParent();
    });*/
    createSaveButton2();
    disableSelection(document.body);
    editor.resizeToFillParent();
    editor.addExternalDropTarget(document.getElementById("testDropTarget"), function (dropTarget, timelineElement) {
        window.alert(dropTarget);
        window.alert(timelineElement.preview + " " +
            timelineElement.id + " " +
            timelineElement.filename + " " +
            timelineElement.length_ + " " +
            timelineElement.muted + " " +
            timelineElement.startOffset + " " +
            timelineElement.stopOffset
        );
    });
    editor.selectTab(0);
    setTimeout(function () {
        editor.resizeToFillParent();
    }, 500);
    //editor.putAsset('testId', './media/clips/videoHigh.mp4', 2, 8, 12, true, './preview/preview.png', 0);
};

$(window).load(function () {
    loaded = true;
});

editor.onCanBeCreated = function () {
    var afterLoad = function () {
        /**
         *
         * @param editorVideoContainer
         * @param timelineView
         * @param headerPanel
         * @param assetsPanel
         * @param controlPanel
         */
        editor.setInitParams(document.getElementById("footer"),
            document.getElementById("header"), /*null*/document.getElementById("leftPanel"),
            document.getElementById("bottomPanel"));
        editor.setAddingText(true);
        editor.setTimelineComposition(true);
        /*editor*/
        editor.createEditor(videoEditorElementId);
    };
    if (loaded) {
        afterLoad();
    } else {
        $(window).load(afterLoad);
    }
};

/**
 * Returns color for asset
 * @param type type of asset
 */
function getColor(type) {
    if (type == timelineType) {
        return "#6699ff";
    }
    if (type == flatType) {
        return "#ccc";
    }
    return "#ff0000";
}

function disableSelection(target) {
    if (typeof target.onselectstart != "undefined") //IE
        target.onselectstart = function () {
            return false
        };
    else if (typeof target.style.MozUserSelect != "undefined") //Firefox
        target.style.MozUserSelect = "none";
    else //All other ie: Opera
        target.onmousedown = function () {
            return false
        };
    target.style.cursor = "default"
}

/**
 * Creates save button for saving timeline in asset
 */
function createSaveButton() {
    $("#saveTimelineButton").click(function () {
        var timeline = editor.getTimelineInfo();
        var timelineElementsJson = $.parseJSON(timeline["timelineElementsJson"]);
        var texts = $.parseJSON(timeline["timelineTextElementsJson"]);
        $.post('./save', JSON.stringify({timeline: timelineElementsJson, texts: texts}), function (data) {
            console.log(data);
        });
        var type = timelineType;
        timelineAssets.push(timelineElementsJson);
        var clipID = type + (timelineAssets.length - 1);
        createDiv(clipID, type);
        makeDraggable(clipID);
    });
}

function createSaveButton2() {
    $("#saveTimelineButton").click(function () {
        var timeline = editor.getTimelineInfo();
        var timelineElementsJson = $.parseJSON(timeline["timelineElementsJson"]);
        var texts = $.parseJSON(timeline["timelineTextElementsJson"]);
        $.post('en/editor/save', {timeline: JSON.stringify({timeline:timelineElementsJson, texts:texts})}, function (data) {
            //console.log(data);
            var cartCount = document.getElementById('cartCount');
            cartCount.innerHTML = parseInt(cartCount.innerHTML) + 1;
            showNotify('Timeline has been successfuly added to your cart');
        });
        /*var type = timelineType;
         timelineAssets.push(timelineElementsJson);
         var clipID = type + (timelineAssets.length - 1);
         createDiv(clipID, type);
         makeDraggable(clipID);*/
    });
}

/**
 * Creates upload button for uploading clips.
 */
function createUpload() {
    $('#fileupload').fileupload({
        done: function (e, data) {
            var result = data["result"];
            if (result != "err") {
                var type = flatType;
                var clip = $.parseJSON(result);
                flatAssets.push(clip);
                var clipID = type + (flatAssets.length - 1);
                createDiv(clipID, type, clip.preview);
                makeDraggable(clipID);
            }
        }, progress: function (a, b) {
            $("#fileupload-progressbar").progressbar("value", parseInt(b.loaded / b.total * 100, 10));
        }, start: function () {
            $("#fileupload-progressbar").progressbar("value", 0).fadeIn();
        }, stop: function () {
            $(".fileupload-progressbar").fadeOut();
        }
    });

    // Load existing files:
    $.getJSON($('#fileupload form').prop('action'), function (files) {
        var fu = $('#fileupload').data('fileupload');
        fu._renderDownload(files)
            .appendTo($('#fileupload .files'))
            .fadeIn(function () {
                // Fix for IE7 and lower:
                $(this).show();
            });
    });

    // Open download dialogs via iframes,
    // to prevent aborting current uploads:
    $('#fileupload .files').delegate(
        'a:not([target^=_blank])',
        'click',
        function (e) {
            e.preventDefault();
            $('<iframe style="display:none;"></iframe>')
                .prop('src', this.href)
                .appendTo('body');
        }
    );

    /*for local files*/
    var playSelectedFile = function playSelectedFileInit(event) {
            var file = this.files[0];
            /*var type = file.type;*/
            /*var canPlay = videoNode.canPlayType(type);
             canPlay = (canPlay === '' ? 'no' : canPlay);
             var message = 'Can play type "' + type + '": ' + canPlay;
             var isError = canPlay === 'no';*/
            var fileURL = null;
            if (window.webkitURL) {
                fileURL = window.webkitURL.createObjectURL(file);
            }
            if (window.URL) {
                fileURL = window.URL.createObjectURL(file);
            }
            if (fileURL) {
                //todo it's for testing
                var filename = fileURL;
                var startOffset = 1;
                var stopOffset = 3500;
                var length = 3600;
                var muted = false;
                var preview = './preview/preview.png';
                editor.putAsset(nextId, filename, startOffset, stopOffset, length, muted, preview, 0);
                nextId++;
            } else {

            }
        },
        inputNode = document.getElementById('localFilesSelector');


    inputNode.addEventListener('change', playSelectedFile, false);
}

/**
 * Creates div element, which represents video element
 * @param id
 */
function createDiv(id, type, preview) {
    var divElement = document.createElement('div');
    divElement.setAttribute("id", id);
    divElement.setAttribute("class", "videoElementImitator");
    if (preview != undefined) {
        var image = document.createElement('img');
        image.setAttribute('src', preview + "frame_1.jpg");
        divElement.appendChild(image);
        var showingPreview = false;
        var onMouseOver = function (event) {
            /*if (!showingPreview) {*/
            if (type == flatType) {
                showingPreview = true;
                var element = flatAssets[id.replace(type, "")];
                var amountOfPictures = Math.ceil((element.stopOffset - element.startOffset) / 0.5);
                var i = 1;
                var intervalNumber;
                intervalNumber = setInterval(function () {
                    if (i <= amountOfPictures) {
                        var number = i.toString();
                        image.setAttribute('src', preview + "frame_" + number + ".jpg");
                    }
                    i++;
                    if (i > amountOfPictures) {
                        clearInterval(intervalNumber);
                        showingPreview = false;
                    }
                }, 100);
                image.onmouseout = function () {
                    clearInterval(intervalNumber);
                    showingPreview = false;
                }
            }
            if (type == timelineType) {

            }
            /*}*/
        };
        image.onmouseover = onMouseOver;
    }
    //document.getElementById(editor.getAssetsPanelId()).appendChild(divElement);
    editor.addElementToAssets(divElement);
}

/**
 * make div with id == droppableId droppable and define its reaction on drop event
 * @param droppableId
 */
function makeDroppable(droppableId, dropNumber) {
    $(droppableId).droppable({
        drop: function (event, ui) {
            var draggableId = ui.draggable.attr('id');
            var type = null;
            var clipInfo = null;
            if (draggableId.indexOf(timelineType) != -1) {
                type = timelineType;
                draggableId = draggableId.replace(type, "");
                clipInfo = timelineAssets[draggableId];
            }
            if (draggableId.indexOf(flatType) != -1) {
                type = flatType;
                draggableId = draggableId.replace(type, "");
                clipInfo = flatAssets[draggableId];
            }

            if (type == timelineType) {
                editor.setTimelineInfo(timelineAssets[draggableId]);
            }
            if (type == flatType) {
                var filename = clipInfo["filename"];
                var startOffset = Number(clipInfo["startOffset"]);
                var stopOffset = Number(clipInfo["stopOffset"]);
                var length = Number(clipInfo["length_"]);
                var muted = (1 == clipInfo["muted"]);
                var preview = clipInfo["preview"];
                editor.putAsset(nextId, filename, startOffset, stopOffset, length, muted, preview, dropNumber);
                nextId++;
            }
        }, tolerance: "touch"
    });
}

/**
 * remove droppable feature from div with id == droppableId
 * @param droppableId
 */
function removeDroppable(droppableId) {
    $(droppableId).droppable("destroy");
}

/**
 * makes div with id == draggableId draggable and define its
 * reaction on start and stop dragging events
 * @param draggableId
 */
function makeDraggable(draggableId) {
    $("#" + draggableId).draggable({
        revert: "invalid",
        helper: "clone",
        cursor: "move",
        opacity: 0.35,
        revertDuration: 1500,
        scroll: false,
        appendTo: "body",
        start: function (event, ui) {
            ui.helper[0].style.zIndex = 5;
            //var droppableId = editor.getDroppableElementId();
            //makeDroppable("#" + droppableId);
            editor.prepareDroppableElements();
            var droppables = editor.getDroppableDroppableElements();
            for (var drop = 0; drop < droppables.length; drop++) {
                makeDroppable(droppables[drop], drop);
            }
        },
        stop: function (event, ui) {
            /*var droppables = editor.getDroppableDroppableElements();
             for (var drop = 0; drop < droppables.length; drop++) {
             removeDroppable(droppables[drop]);
             }*/
            editor.hideDroppableElements();
        }
    });
}

function testServer() {
    $.getJSON("./getTimelineAssets",
        function (data) {
            alert(data);
        });
}

function testServer1() {
    $.getJSON("./getFlatAssets",
        function (data) {
            alert(data);
        });
}

function testServer2(params) {
    $.getJSON("./getDuration?" + params,
        function (data) {
            alert(data);
        });
}



