<!doctype html>
<!-- The DOCTYPE declaration above will set the    -->
<!-- browser's rendering engine into               -->
<!-- "Standards Mode". Replacing this declaration  -->
<!-- with a "Quirks Mode" doctype may lead to some -->
<!-- differences in layout.                        -->
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=8"/>

    <link rel="stylesheet" href="/data/editor/tabPanel.css" type="text/css"/>
    <link media="all" type="text/css" href="/data/editor/jquery-ui-1.7.3.custom.css" rel="stylesheet">
    <link rel="stylesheet" href="/data/editor/Editor.css" type="text/css"/>
    <link href='http://fonts.googleapis.com/css?family=Princess+Sofia' rel='stylesheet' type='text/css'>
    <!-- styles needed by jScrollPane-->
    <link type="text/css" href="/data/editor/jquery.jscrollpane.css" rel="stylesheet" media="all"/>

    <script src="/data/editor/jquery-1.7.1.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="/data/editor/jquery-ui-1.8.21.custom.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="/data/editor/jquery.iframe-transport.js"></script>
    <script src="/data/editor/jquery.fileupload.js"></script>
    <script src="/data/editor/jquery.ui.touch-punch.min.js"></script>
    <!-- the mousewheel plugin -->
    <!--<script type="text/javascript" src="jquery.mousewheel.js"></script> -->
    <!-- the jScrollPane script -->
    <script type="text/javascript" src="/data/editor/jquery.jscrollpane.js"></script>

    <!--Colorpicker-->
    <script type="text/javascript" src="/data/editor/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="/data/editor/colorpicker/js/eye.js"></script>
    <script type="text/javascript" src="/data/editor/colorpicker/js/utils.js"></script>
    <script type="text/javascript" src="/data/editor/colorpicker/js/layout.js?ver=1.0.2"></script>
    <link rel="stylesheet" href="/data/editor/colorpicker/css/colorpicker.css" type="text/css"/>

    <!--video-js library -->
    <!--<link href="video-js/video-js.css" rel="stylesheet" type="text/css">
    <script src="video-js/video.js"></script>-->

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <!--Html5 video detecting lib-->
    <script type="text/javascript" src="/data/editor/modernizr.video.js"></script>
    <script type="text/javascript" src="/data/editor/srt.js"></script>
    <!-- Unless using the CDN hosted version, update the URL to the Flash SWF -->
    <script type="text/javascript">
        /*_V_.options.flash.swf = "video-js/video-js.swf";
         _V_.options.techOrder = ["flash"];
         var TOLLERANCE = 1.5;*/

        function onJavaScriptBridgeCreated(playerId) {
            window.alert("I'm in" + playerId);
            /*if (player == null) {
             player = document.getElementById(playerId);

             // Add event listeners that will update the
             player.addEventListener("currentTimeChange", "onCurrentTimeChange");
             player.addEventListener("durationChange", "onDurationChange");

             // Pause/Resume the playback when we click the Play/Pause link
             document.getElementById("play-pause").onclick = function() {
             var state = player.getState();
             if (state == "ready" || state == "paused") {
             player.play2();
             }
             else
             if (state == "playing") {
             player.pause();
             }
             return false;
             };
             }*/
        }
    </script>


    <!--Colorpicker-->
    <!--                                                               -->
    <!-- Consider inlining CSS to reduce the number of requested files -->
    <!--                                                               -->
    <title>GWTVideo</title>
    <!--                                           -->
    <!-- This script loads your compiled module.   -->
    <!-- If you add any GWT meta tags, they must   -->
    <!-- be added before this line.                -->
    <!--                                           -->
    <script type="text/javascript" language="javascript"
            src="/data/editor/com.be.video.editor.Editor/com.be.video.editor.Editor.nocache.js"></script>
    <!--                                           -->
    <!-- This script enable JQuery D&D feature     -->
    <!--                                           -->

    <script>
        var videoEditorElementId = "videoEditorElementId7";
    </script>
    <script type="text/javascript" language="javascript"
            src="/data/editor/editor.js"></script>
</head>
<body>
<!-- RECOMMENDED if your web app will not function without JavaScript enabled -->
<noscript>
    <div style="width: 22em; position: absolute; left: 50%; margin-left: -11em; color: red; background-color: white; border: 1px solid red; padding: 4px; font-family: sans-serif">
        Your web browser must have JavaScript enabled
        in order for this application to display correctly.
    </div>
</noscript>
<div id="toTest" style="width: 400px;">

</div>

<!--
<div id="fileupload">
    <form action="./upload" method="POST" enctype="multipart/form-data">
        <div class="fileupload-buttonbar">
            <label class="fileinput-button">
                <span>Add files...</span>
                <input type="file" name="files[]" multiple>
            </label>
        </div>
    </form>
    <div class="fileupload-content">
        <table class="files"></table>
        <div id="fileupload-progressbar" class="fileupload-progressbar" style="width: 400px; height: 50px"></div>
    </div>
</div>
-->

<button id="saveTimelineButton" style="width:60px; height: 20px;">Save</button>
<div id="videoEditorElementId7" style="display: none;"></div>
<div id="assetsPanel"></div>

<div>
    <div id="header"></div>
    <div id="centralPanel">
        <div id="leftPanel"></div>
        <div id="rightPanel"></div>
    </div>
    <div id="bottomPanel"></div>
    <div id="footer"></div>
</div>

<!--<div>Local files</div><input id="localFilesSelector" type="file" accept="video/*"/>-->
<div id="testDropTarget" style="width: 400px; height: 10px; background-color: #A5CD4E; display: none;"></div>
​
</body>
</html>
