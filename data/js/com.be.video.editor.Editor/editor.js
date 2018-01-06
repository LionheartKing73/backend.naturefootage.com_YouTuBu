/**
 * User: shyyko
 * Date: 11/7/11
 * Time: 4:41 PM
 */

/**
 * instance of Editor
 * @see Editor
 */
function Editor(getDroppableElementId, putAsset, setTimelineInfo, getTimelineInfo, getAssetsPanelId, getDroppableDroppableElements, prepareDroppableElements, hideDroppableElements, addToAssets, onLoaded, addElementToAssets, createEditor, onCanBeCreated, setInitParams, addExternalDropTarget, onSelectionChanged, setTimelineComposition, setAddingText) {
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
}

var loaded = false;

var videoEditorElementId = "content";


editor = new Editor(null, null, null, null, null, null, null, null, null, null, null, null, null, null);

editor.addToAssets = function (filename, startOffset, stopOffset, length, dataUrl, muted) {
    window.alert(filename + " " + startOffset + " " + stopOffset +
        " " + length + " " + dataUrl + " " + muted);
};
/*editor.onLoaded = function () {
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
    //editor.putAsset('testId', './media/clips/videoHigh.mp4', 2, 8, 12, false, './preview/preview.png', 0);
};*/

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
        editor.setInitParams(document.getElementById("left_part"), document.getElementById("timeline"),
            /*document.getElementById("header")*/null, null/*document.getElementById("leftPanel")*/,
            document.getElementById("left_part"));
        editor.setAddingText(false);
        editor.setTimelineComposition(false);
        editor.createEditor(videoEditorElementId);
    };
    if (loaded) {
        afterLoad();
    } else {
        $(window).load(afterLoad);
    }
};