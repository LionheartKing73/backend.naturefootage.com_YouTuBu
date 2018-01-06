<?php

function fck($width=null, $height=null, $name='body'){

    $width = ($width) ? $width : 600;
    $height = ($height) ? $height : 280;

    $str = '<script type="text/javascript" src="/vendors/fck/fckeditor.js"></script>
    <script type="text/javascript">
     var oFCKeditor = new FCKeditor("' . $name .'");
     oFCKeditor.ToolbarSet="Custom";
     oFCKeditor.Width='.$width.';
     oFCKeditor.Height='.$height.';
     oFCKeditor.BasePath="/vendors/fck/";
     oFCKeditor.ReplaceTextarea();
    </script>';

    return $str;
}

function tinymce($id = 'body'){
    $str = '
        <script type="text/javascript" src="/vendors/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
        tinymce.init({
            selector: "textarea#' . $id . '",
            height: 300,
            plugins: [
                 "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                 "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                 "save table contextmenu directionality emoticons template paste textcolor"
           ],
           toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
           file_browser_callback: function(field_name, url, type, win) {
                window.KCFinder = {};
                window.KCFinder.callBack = function(url) {
                    document.getElementById(field_name).value = url;
                    tinymce.activeEditor.windowManager.close();
                    window.KCFinder = null;
                };
                tinymce.activeEditor.windowManager.open({
                    file: "/vendors/kcfinder/browse.php?opener=tinymce&type=" + type,
                    title: "File Manager",
                    width: 700,
                    height: 500,
                    resizable: "yes",
                    inline: true,
                    close_previous: "no",
                    popup_css: false
                }, {
                    window: win,
                    input: field_name
                });
           },
           relative_urls : false,
           remove_script_host : false,
           document_base_url : "http://' . $_SERVER['HTTP_HOST'] . '/"

        });
        </script>';

    return $str;
}


function tiny($class = 'body'){
    $str = '<script type="text/javascript" src="/vendors/tiny_mce/tiny_mce.js"></script>
            <script type="text/javascript">
                tinyMCE.init({
                    // General options
                    mode : "specific_textareas",
                    editor_selector : "' . $class . '",
                    theme : "advanced",
                    plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

                    // Theme options
                    theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,visualblocks",
                    theme_advanced_toolbar_location : "top",
                    theme_advanced_toolbar_align : "left",
                    theme_advanced_statusbar_location : "bottom",
                    theme_advanced_resizing : true,

                    // Example content CSS (should be your site CSS)
                    //content_css : "css/content.css",

                    // Drop lists for link/image/media/template dialogs
                    template_external_list_url : "lists/template_list.js",
                    external_link_list_url : "lists/link_list.js",
                    external_image_list_url : "lists/image_list.js",
                    media_external_list_url : "lists/media_list.js",

                    // Style formats
//                    style_formats : [
//                        {title : "Bold text", inline : "b"},
//                        {title : "Red text", inline : "span", styles : {color : "#ff0000"}},
//                        {title : "Red header", block : "h1", styles : {color : "#ff0000"}},
//                        {title : "Example 1", inline : "span", classes : "example1"},
//                        {title : "Example 2", inline : "span", classes : "example2"},
//                        {title : "Table styles"},
//                        {title : "Table row 1", selector : "tr", classes : "tablerow1"}
//                    ],

                    // Replace values for the template plugin
//                    template_replace_values : {
//                        username : "Some User",
//                        staffid : "991234"
//                    }

                    relative_urls : false,
                    remove_script_host : false,
                    document_base_url : "http://' . $_SERVER['HTTP_HOST'] . '/",
                    convert_urls : false,

                    file_browser_callback: function(field_name, url, type, win) {
                        window.KCFinder = {};
                        window.KCFinder.callBack = function(url) {
                            //win.document.forms[0].elements[field_name].value = url;
                            win.document.getElementById(field_name).value = url;
                            //tinyMCE.activeEditor.windowManager.close();
                            window.KCFinder = null;
                        };

                        tinyMCE.activeEditor.windowManager.open({
                            file : "/vendors/kcfinder/browse.php?opener=tinymce&type=" + type,
                            title : "File Browser",
                            width : 420,  // Your dimensions may differ - toy around with them!
                            height : 400,
                            resizable : "yes",
                            inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
                            close_previous : "no"
                        }, {
                            window : win,
                            input : field_name
                        });



                    }
                });
            </script>';

    return $str;
}