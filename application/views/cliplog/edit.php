<!--style type="text/css">
    @import url(/data/css/bootstrapSwitch.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/cliplog.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/clips_list.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/carousel/skins/tango/skin.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/js/videojs/video-js.css<?php echo '?' . date('dmYh'); ?>);
</style-->
<link rel="stylesheet" type="text/css" href="/data/css/bootstrapSwitch.css<?php echo '?' . date('dmYh'); ?>">
<link rel="stylesheet" type="text/css" href="/data/carousel/skins/tango/skin.css<?php echo '?' . date('dmYh'); ?>">
<link rel="stylesheet" type="text/css" href="/data/css/cliplog.css<?php echo '?' . date('dmYh'); ?>">
<link rel="stylesheet" type="text/css" href="/data/css/clips_list.css<?php echo '?' . date('dmYh'); ?>">
<link rel="stylesheet" type="text/css" href="/data/js/videojs/video-js.css<?php echo '?' . date('dmYh'); ?>">
<style>
    .jcarousel-skin-tango {
        max-height: 200px;
        overflow: hidden;
        display: none;
    }

    .formRight {
        float: right;
        margin-right: 20px;
        margin-top: 20px;
    }
</style>
<script>
    $(document).ready(function () {
        $(".jcarousel-skin-tango").fadeIn(500);
    });
</script>
<?php
//echo '<pre>';
//print_r($clip);
//die;
header('Content-Type:text/html; charset=UTF-8');
?>
<script type="text/javascript">
    // Используется в cliplog.keywordsManager.js
    var selectedKeywordIds = {
        <?php
        if ($keywords) {
            foreach ($keywords as $id)
                echo "{$id} : {$id}, ";
        }
        ?>
    };
    var keywordsState = <?php
        if ($keywordsState) {
            echo $keywordsState;
        } else {
            echo '{}';
        }
        ?>;
    var keywordsSectionsVisible = '<?php if ($keywordsSectionsVisibleString) echo $keywordsSectionsVisibleString; ?>';

</script>
<script type="text/javascript" src="/data/js/bootstrapSwitch.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/underscore-min.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/videojs/video.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/videojs/video-controls.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/jquery.md5.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.dialog.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.createthumb.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/carousel/lib/jquery.jcarousel.js"></script>
<script type="text/javascript" src="/data/js/cliplog.carouselscrolling.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript"
        src="/data/js/jScrollPane/jquery.jscrollpane.min.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript"
        src="/data/js/jScrollPane/jquery.mousewheel.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.formkeeper.js<?php echo '?' . date('dmYh'); ?>"></script>

<script>
    videojs.options.flash.swf = "/data/js/videojs/video-js.swf";
</script>

<a class="back_link" style="font-size:14px;" href="<?php echo $back_url; ?>" id="back-link"><? echo $back_title; ?></a>
<br><br>

<?php $this->load->view('cliplog/edit/clip_box'); ?>

<?php if ($clips_ids) { ?>
    <form action="<?php echo $lang; ?>/cliplog/edit/" method="post" class="carousel_form">
        <input type="hidden" value="<?php echo $clips_ids ?>" name="clips_ids">
        <input type="hidden" name="keywordsState"/>
    </form>
<?php } elseif ($clip) { ?>
    <form action="<?php echo $lang; ?>/cliplog/edit/" method="post" class="carousel_form">
        <input type="hidden" value="<?php echo $clips_ids ?>" name="clips_ids">
        <input type="hidden" name="keywordsState"/>
    </form>
<?php } ?>
<?php if ($clips) { ?>
    <?php $defaultSelect = '-Choose-'; ?>
    <div class="clips_carousel_cont jcarousel-skin-tango">
        <ul id="clips_carousel" class="jcarousel-skin-tango">
            <?php
            $selected_carousel_item = 1;
            foreach ($clips as $key => $item) {
                if ($item['id'] == $clip['id'])
                    $selected_carousel_item = $key + 1;
                ?>
                <li<?php echo ($selected_clip == $item['id']) ? ' class="active"' : ''; ?>>
                    <div class="footagesearch-clip-code"><?php echo $item['code'] ?></div>
                    <a id="footagesearch-clip-offset-1"
                       href="<?php echo $lang; ?>/cliplog/edit/<?php echo $item['id']; ?>"
                       class="carousel_link containerGridGallery" att-preview="<?php echo $item['motion_thumb'] ?>"
                       att-descp="<?php echo $item['description'] ?>" att-code="<?php echo $item['code'] ?>">
                        <img src="<?php echo $item['thumb']; ?>" class="footagesearch-clip-info-icon" width="200"
                             height="112"/>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php if ($next_clip_step) { ?>
        <script type="text/javascript">
            $(document).ready(function () {
                setTimeout(function () {
                    // Переход к следующему клипу сразу после загрузки страницы, если требуется
                    var carouselList = $('#clips_carousel');
                    var carouselItems = carouselList.find('.jcarousel-item');
                    //alert(carouselItems.length);
                    if (carouselItems.length > 1) {
                        // carouselList.find('.jcarousel-item.active').next().find('a').click();
                        $('.next-clip').click();

                    }
                }, 1000);

            });
        </script>
    <?php } ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#clips_carousel').jcarousel({
                start: <?php echo $selected_carousel_item; ?>,
                scroll: 1
            });

            /*jQuery(window).load(function() {
             jQuery('#clips_carousel').jcarousel({
             start:
            <?php echo $selected_carousel_item; ?>,
             scroll: 1
             });
             });*/

            $('.carousel_link').click(function (e) {
                e.preventDefault();
                var carousel_form = jQuery('.carousel_form');
                if (carousel_form.length > 0) {
                    carousel_form.attr('action', jQuery(this).attr('href'));
                    carousel_form.submit();
                }
            })
            function in_array(search, array) {
                for (i = 0; i < array.length; i++) {
                    if (array[i] == search) {
                        return true;
                    }
                }
                return false;
            }


            $('.moveClipOnlineOffline').click(function () {
                var that = $(this);
                var clipid = that.attr('data-id');
                var status = that.attr('data-status');


                $.ajax({
                    url: "ajax.php",
                    data: {action: 'moveClipOnlineOffline', clipid: clipid, status: status},
                    type: "POST",
                    success: function (data) {
                        alert('Status Changed Successfully');
                        if (status == 1) {
                            $('.red-point').addClass('green-point').removeClass('red-point');
                            $(".green-point").attr("title", "Published");
                        } else {
                            $('.green-point').addClass('red-point').removeClass('green-point');
                            $(".red-point").attr("title", "Unpublished");
                        }
                    }
                });
            });
//            $('.copy_prev_keyqords').click(function () {
//                var carouselList = $('#clips_carousel');
//                var carouselItems = carouselList.find('.jcarousel-item');
//                //alert(carouselItems.length);
//                if (carouselItems.length > 1) {
//                    var getVal = carouselList.find('.jcarousel-item.active').prev().find('a').attr('href');
//
//                    if (getVal !== 'undefined') {
//                        var array = getVal.split("/");
//                        var KeyWordId = array[array.length - 1];
//
//
//                        $.ajax({
//                            url: "ajax.php",
//                            data: {action: 'getPrevClipIds', clipid: KeyWordId},
//                            type: "POST",
//                            success: function (data) {
//                                var res = data.split("<br>");
//                                $.each(res, function (index, value) {
//                                    $('#myTestDiv').append(value);
//                                })
//
//                                var sectionsArray = ['shot_type', 'subject_category', 'primary_subject', 'other_subject', 'appearance', 'actions', 'time', 'habitat', 'concept', 'location']
//
//
//                                $.each(sectionsArray, function (index, value) {
//
//                                    var valuearr = [];
//
//                                    $("#" + value + " .cliplog_selected_keywords_list_new .item-wrapper").each(function () {
//                                        valuearr.push($(this).find('.getUserKeywordsForLogging').attr('datavalue-text'));
//                                    })
//                                    //console.log(valuearr);
//                                    // $("#myTestDiv ." + value).each(function() {
//                                    var buttonBox = $(this);
//                                    $("#myTestDiv ." + value + " .item-wrapper").each(function () {
//                                        var CheckVal = $(this).find('input:hidden').first().attr('datavalue-text');
//                                        var embeddedHtml = '<div class="item-wrapper ">' + $(this).html() + '</div>';
//
//                                        if (in_array(CheckVal, valuearr)) {
//                                            $(this).remove();
//                                        } else {
//                                            $("#" + value + " .cliplog_selected_keywords_list_new").append(embeddedHtml);
//                                            $(this).remove();
//                                        }
//
//                                    })
//                                    //});
//                                })
//                            }
//                        });
//
//                    }
//
//
//                }
//
//
//            });
        });
    </script>
<?php } ?>

<div class="clear"></div>
<?php
// ------------------------- SETTINGS ------------------------- //
$overwrite = ($clip || ($_REQUEST['applied_keywords_set_id'] === '' && $_REQUEST['overwrite_all'])) ? 1 : 0;
$reset = ($_REQUEST['applied_keywords_set_id'] === '' && $_REQUEST['overwrite_all']) ? 1 : 0;
//var_dump($_REQUEST);
// ----------------------- END SETTINGS ----------------------- //
?>
<form action="<?= $lang ?>/cliplog/edit<?php echo (isset($clip['id'])) ? '/' . $clip['id'] : ''; ?>" method="post"
      id="cliplog_form" enctype="multipart/form-data">
    <input type="hidden" name="keywordsState"/>
    <input type="hidden" name="keywordsHiddenState"/>

    <div class="form-actions">
        <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        <!-- input type="submit" class="btn btn-primary" value="Show clip metadata" name="show-clip-metadata" -->
        <?php if ($next_clip_id) { ?>
            <label for="goto-next" class="label-goto-next">
                <input type="checkbox" id="goto-next" class="goto-next" name="next_clip_id"
                       value="<?php echo $next_clip_id; ?>" <?php if ($goto_next_active) echo 'checked="checked"'; ?> />
                View Next Clip
            </label>
        <?php } ?>
        <span class="formRight">
            <a href="javascript:;" class="moveClipOnlineOffline"
               data-id="<?php echo ($clips_ids != '') ? $clips_ids : $clip['id']; ?>" data-status="1">Move
                Online</a>&nbsp;| &nbsp;
            <a href="javascript:;" class="moveClipOnlineOffline"
               data-id="<?php echo ($clips_ids != '') ? $clips_ids : $clip['id']; ?>" data-status="0">Move
                Offline</a>
            <?php if ($clips_ids == '') { ?>
                <span class="copy_prev_keyqords">&nbsp;|&nbsp;</span>
                <a href="javascript:;" class="copy_prev_keyqords">Copy Keywords From Previous Clip</a>
            <?php } ?>
        </span>


    </div>


    <?php if ($clips_ids) { ?>
        <input type="hidden" value="<?php echo $clips_ids ?>" name="clips_ids">
    <?php } elseif ($clip) { ?>
        <input type="hidden" value="<?php echo $clips_ids ?>" name="clips_ids">
    <?php } ?>
    <div id="cliplog">
        <div id="cliplog_sidebar">
            <div id="cliplog_sidebar_content">

                <?php $this->load->view('cliplog/edit/logging_box'); ?>

                <?php $this->load->view('cliplog/edit/metadata_box'); ?>

                <div class="cliplog-sidebar-box sidebar-padding" style="overflow: hidden;">
                    <div class="control-group" data-type="metadata">
                        <div class="cliplog_sidebar_header cliplog_sidebar_header-keyword">
                            <h1>Use Clarifai keywords generator:</h1>
                            <span style="width: 186px; float: left; margin-bottom: 9px;">
                                Generate keywords for current clip, and display keywords in "AutoGenerated" section.
                                <br>
                                <br>
                                <div class="info_message">
                                    <strong>It may take some time.</strong>
                                </div>
                                <div class="loading_message">
                                    <strong>LOADING</strong> <br>
                                    <strong>please wait...</strong>
                                </div>
                                <div class="success_message">
                                    <strong>...DONE</strong>
                                </div>
                            </span>
                            <input type="submit" class="action" value="Generate" name="generate_clarifai_keywords" id="generate_clarifai_keywords">
                        </div>
                    </div>
                </div>

                <div class="cliplog-sidebar-box">

                    <div class="control-group selected-items">
                        <label class="control-label">
                            Hidden Fields:
                        </label>

                        <div class="cliplog_hidden_sections">
                            <?php if (!isset($clip_notes)) { ?>

                                <div class="section-switch-cont section-switch-cont-clip_notes">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="clip_notes" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Notes
                                </div>
                            <?php } ?>

                            <?php if (!isset($audio_video)) { ?>

                                <div class="section-switch-cont section-switch-cont-audio_video">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="audio_video" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Audio Video
                                </div>
                            <?php } ?>


                            <?php if (!isset($date_filmed)) { ?>
                                <div class="section-switch-cont section-switch-cont-date_filmed">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="date_filmed" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Date Filmed
                                </div>
                            <?php } ?>
                            <?php if (!isset($brand) && $is_admin) { ?>
                                <div class="section-switch-cont section-switch-cont-brand">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="brand" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Brand
                                </div>
                            <?php } ?>
                            <?php if (!isset($add_collection) /* && $is_admin */) { ?>
                                <div class="section-switch-cont section-switch-cont-add_collection">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="add_collection" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Collection
                                </div>
                            <?php } ?>





                            <?php if (!isset($license_type)) { ?>
                                <div class="section-switch-cont section-switch-cont-license_type">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="license_type" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    License Type
                                </div>
                            <?php } ?>
                            <?php if (!isset($price_level)) { ?>
                                <div class="section-switch-cont section-switch-cont-price_level">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="price_level" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Price Level
                                </div>
                            <?php } ?>
                            <?php if (!isset($releases)) { ?>
                                <div class="section-switch-cont section-switch-cont-releases">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="releases" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Releases
                                </div>
                            <?php } ?>
                            <?php if (!isset($file_formats)) { ?>
                                <div class="section-switch-cont section-switch-cont-file_formats">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="file_formats" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    File formats
                                </div>
                            <?php } ?>
                            <?php if (!isset($shot_type)) { ?>
                                <div class="section-switch-cont section-switch-cont-shot_type">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="shot_type" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Shot Type
                                </div>
                            <?php } ?>
                            <?php if (!isset($subject_category)) { ?>
                                <div class="section-switch-cont section-switch-cont-subject_category">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="subject_category" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Subject Category
                                </div>
                            <?php } ?>
                            <?php if (!isset($primary_subject)) { ?>
                                <div class="section-switch-cont section-switch-cont-primary_subject">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="primary_subject" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Primary Subject
                                </div>
                            <?php } ?>
                            <?php if (!isset($other_subject)) { ?>
                                <div class="section-switch-cont section-switch-cont-other_subject">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="other_subject" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Other Subject
                                </div>
                            <?php } ?>
                            <?php if (!isset($appearance)) { ?>
                                <div class="section-switch-cont section-switch-cont-appearance">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="appearance" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Appearance
                                </div>
                            <?php } ?>
                            <?php if (!isset($actions)) { ?>
                                <div class="section-switch-cont section-switch-cont-actions">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="actions" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Actions
                                </div>
                            <?php } ?>
                            <?php if (!isset($time)) { ?>
                                <div class="section-switch-cont section-switch-cont-time">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="time" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Time
                                </div>
                            <?php } ?>
                            <?php if (!isset($habitat)) { ?>
                                <div class="section-switch-cont section-switch-cont-habitat">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="habitat" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Habitat
                                </div>
                            <?php } ?>
                            <?php if (!isset($concept)) { ?>
                                <div class="section-switch-cont section-switch-cont-concept">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="concept" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Concept
                                </div>
                            <?php } ?>
                            <?php if (!isset($location)) { ?>
                                <div class="section-switch-cont section-switch-cont-location">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="location" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Location
                                </div>
                            <?php } ?>
                            <?php if (!isset($country)) { ?>
                                <div class="section-switch-cont section-switch-cont-country">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" value="country" data-formkeeper="logging">
                                        </div>
                                    </div>
                                    Country
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div style="visibility: hidden" id="myTestDiv"></div>
                </div>

            </div>
        </div>

        <div id="cliplog_template">
            <table>
                <?php if ($is_admin) { ?>

                    <tr class="control-group cliplog_section" id="auto_generated" style="" data-formkeeper="keywords">
                        <td>
                            <table class="new-section expanded">
                                <tr>
                                    <td colspan="4">
                                        <span class="field_label" style="margin-left: 10px;">
                                            Auto Generated Keywords:
                                            <input type="hidden" id="section_search_name" name="section_search_name" value="auto_generated">
                                        </span>
                                    </td>
                                    <td style="display: none">
                                        <div class="content_search">
                                            <div class="input_container">
                                                <input type="text" value="" class="cliplog_keyword_input"
                                                       onblur="hideResult('auto_generated')"
                                                       onkeyup="autocomplet('auto_generated')">

                                                <div class="button-cont">
                                                    <button class="button_add_green cliplog_add_keyword_to_list_new">Add</button>
                                                </div>

                                                <ul class="result_keyword"></ul>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="section-switch-cont">
                                        <div class="switch-cont">
                                            <div class="switch" data-animated="false" data-on-label=""
                                                 data-off-label="">
                                                <input type="checkbox" name="sections[]" id="auto_generated" value="auto_generated" checked data-formkeeper="logging"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="bottom">
                                        <div class="cliplog_selected_keywords_list_new">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" style="text-align: right; padding-right: 15px !important;">
                                        <div class="button-cont">
                                            <button class="button_add_green" id="approve_keywords" style="width: 100px">Accept All</button>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="control-group cliplog_section" id="brand" data-formkeeper="keywords">
                        <td>
                            <table>
                                <tr>
                                    <td class="left">
                                        <span class="field_label">Collection:<span class="require"></span></span>
                                        <?php if (isset($hints['brand'])) { ?><img
                                            src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                            title="<?php echo $hints['brand']; ?>"><?php } ?>
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[brand]"
                                                   value="brand" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select class="input-large" id="brandName" name="sections_values[brand]"
                                                data-formkeeper="keywords" data-id="<?= ($brand) ? $brand : '1'; ?>">
                                            <option value="" <?php echo ($clips_ids) ? " selected " : ""; ?>>

                                                <?php echo $defaultSelect; ?>
                                            </option>

                                            <?php foreach ($add_collection_list as $collection_item): ?>
                                                <option
                                                    value="<?php echo $collection_item['id']; ?>"
                                                    <?php
                                                    echo (
                                                            (isset($brand) && $brand == $collection_item['id'])
                                                            || (empty($brand) && $collection_item['id'] == 1 && !$clips_ids && !$reset)
                                                    ) ? 'selected' : '';
                                                    ?>
                                                    data-formkeeper="keywords"><?php echo $collection_item['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option
                                                value="0"
                                                <?php echo (isset($brand) && $brand === 0) ? 'selected' : ''; ?>
                                            >
                                                <?php echo 'Custom Site Only '; ?>
                                            </option>
                                        </select>
                                        <?php if ($clips_ids) {
                                            $brand = 1;
                                        }
                                        ?>
                                    </td>
                                    <td class="section-switch-cont">
                                        <div class="switch-cont">
                                            <div class="switch" data-animated="false" data-on-label=""
                                                 data-off-label="">
                                                <input type="checkbox" name="sections[]" id="Brand"
                                                       value="brand" <?php echo isset($brand) || !$cliplog_template ? 'checked' : ''; ?>
                                                       data-formkeeper="logging"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                <?php } else { ?>
                    <input type="hidden" id="brandName"
                           data-id="<?php echo (is_array($clipBrand)) ? $clipBrand[0]['brand'] : $clipBrand; ?>">
                <?php } ?>
                <tr class="control-group cliplog_section" data-formkeeper="keywords">
                    <td>
                        <table style="width: 100%;">
                            <tr>
                                <td class="left">
                                    <div class="control-group">
                                        <label class="control-label" for="clip_description">
                                            Clip Description: <span
                                                class="require"></span> <?php if (isset($hints['clip_description'])) { ?>
                                                <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                                     title="<?php echo $hints['clip_description']; ?>"><?php } ?>
                                            <div
                                                class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                                <input type="checkbox" name="overwrite[clip_description]"
                                                       value="clip_description" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                                overwrite existing values
                                            </div>
                                        </label>

                                        <div class="controls">
                                            <input type="text" name="sections_values[clip_description]"
                                                   id="clip_description"
                                                   value="<?php echo isset($clip_description) && $clip_description && !$reset ? $clip_description : ''; ?>"
                                                   class="input-xlarge"><br>
                                            <input type="hidden" name="sections[]" value="clip_description">
                                        </div>
                                    </div>
                                </td>
                                <td class="right" style="width: 442px;">
                                    <div class="control-group"
                                         id="clip_notes" <?php if (!isset($clip_notes)) { ?> style="display: none;"<?php } ?>>
                                        <label class="control-label" for="clip_notes">
                                            Notes:
                                            <div class="switch-cont  section-switch-cont">
                                                <div class="switch" data-animated="false" data-on-label=""
                                                     data-off-label="">
                                                    <input type="checkbox" name="sections[]" id="Notes"
                                                           value="clip_notes" <?php echo isset($clip_notes) || !$cliplog_template ? 'checked' : ''; ?>
                                                           data-formkeeper="logging"/>
                                                </div>
                                            </div>
                                            <?php if (isset($hints['clip_notes'])) { ?><img
                                                src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                                title="<?php echo $hints['clip_notes']; ?>"><?php } ?>
                                            <div
                                                class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                                <input type="checkbox" name="overwrite[clip_notes]"
                                                       value="clip_notes" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                                overwrite existing values
                                            </div>
                                        </label>

                                        <div class="controls">
                                            <input type="text" name="sections_values[clip_notes]" id="clip_notes"
                                                   value="<?php echo isset($clip_notes) && $clip_notes && !$reset ? $clip_notes : ''; ?>"
                                                   class="input-xlarge" data-formkeeper="keywords"><br>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="control-group cliplog_section"
                    id="license_restrictions" <?php if (!isset($license_restrictions)) { ?> style="display: none;"<?php } ?>
                    data-formkeeper="keywords">
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">License Restrictions:</span>
                                    <?php if (isset($hints['license_restrictions'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['license_restrictions']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[license_restrictions]"
                                               value="license_restrictions" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">

                                    <input type="text" name="sections_values[license_restrictions]"
                                           id="license_restrictions"
                                           value="<?php echo isset($license_restrictions) ? $license_restrictions : ''; ?>"
                                           class="input-xlarge" data-formkeeper="keywords"><br>

                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="License Restrictions"
                                                   value="license_restrictions" <?php echo isset($license_restrictions) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="audio_video"<?php if (!isset($audio_video)) { ?> style="display: none;"<?php } ?>
                    data-formkeeper="keywords">
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Audio/Video:</span>
                                    <?php if (isset($hints['audio_video'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['audio_video']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[audio_video]"
                                               value="audio_video" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[audio_video]" id="Audio_Videos"
                                            data-formkeeper="logging">
                                        <option value="">-Choose-</option>
                                        <option value="Video Only" <?php
                                        if ($audio_video == 'Video Only') {
                                            echo 'selected';
                                        } else {
                                            echo '';
                                        }
                                        ?>>Video Only
                                        </option>
                                        <option value="Audio + Video" <?php
                                        if ($audio_video == 'Audio + Video') {
                                            echo 'selected';
                                        } else {
                                            echo '';
                                        }
                                        ?>>Audio + Video
                                        </option>
                                    </select>

                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="Audio Video"
                                                   value="audio_video" <?php echo isset($audio_video) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="keywords"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="add_collection"<?php if (!isset($add_collection)) { ?> style="display: none;"<?php } ?>
                    data-formkeeper="keywords">
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Category: <span class="require"></span></span>

                                    <?php if (isset($hints['add_collection'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['add_collection']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[add_collection][]"
                                               value="add_collection" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">

                                    <div class="cliplog_selected_options_list">
                                        <?php
                                        //foreach ($add_collection_list as $collection_item) {
                                        $checkArrCategory = array();
                                        if ($clip['keywords_types']) {
                                            foreach ($clip['keywords_types'] as $keyword) {
                                                if ($keyword['section_id'] == 'category') {
                                                    array_push($checkArrCategory, $keyword['keyword']);
                                                } elseif ($keyword['section_id'] == 'country') {
                                                    $countryName = $keyword['keyword'];
                                                }
                                            }
                                        }
                                        ?>
                                        <label class="radio"><input
                                                type="radio"<?php if (
                                                        !empty($clip) // nothing checked for batch update
                                                        && isset($checkArrCategory)
                                                        && is_array($checkArrCategory)
                                                        && (empty($checkArrCategory) || in_array('Land', $checkArrCategory))
                                                    ) { ?>
                                                checked="checked" <?php } ?>value="Land"
                                                name="sections_values[add_collection][]" data-formkeeper="keywords">
                                            Nature & Wildlife</label>
                                        <label class="radio"><input
                                                type="radio"<?php if (
                                                        !empty($clip) // nothing checked for batch update
                                                        && isset($checkArrCategory)
                                                        && (is_array($checkArrCategory)
                                                        && in_array('Ocean', $checkArrCategory))
                                                    ) { ?>
                                                checked="checked" <?php } ?>value="Ocean"
                                                name="sections_values[add_collection][]" data-formkeeper="keywords">
                                            Ocean & Underwater</label>

                                        <!--                                        <label class="checkbox"><input-->
                                        <!--                                                type="checkbox"--><?php //if (isset($checkArrCategory) && (is_array($checkArrCategory) && in_array('Adventure', $checkArrCategory))) { ?>
                                        <!--                                                checked="checked" -->
                                        <?php //} ?><!--value="Adventure"-->
                                        <!--                                                name="sections_values[add_collection][]" data-formkeeper="keywords">Adventure-->
                                        <!--                                        </label>-->

                                        <!--                                            <label class="checkbox"><input type="checkbox"<?php if (isset($add_collection) && $add_collection && (is_array($add_collection) && in_array($collection_item['id'], $add_collection))) { ?> checked="checked"<?php } ?>value="<?php echo $collection_item['id']; ?>" name="sections_values[add_collection][]" data-formkeeper="keywords"> <?php echo $collection_item['search_term']; ?></label>-->
                                        <?php // }  ?>
                                    </div>

                                    <!--div class="button-cont" style="display: none;"><button class="button_add cliplog_add_section_options expanded">Add Collection</button></div>
                                    <div class="button-cont"><button class="button_close cliplog_close_options" style="display: inline-block;"></button></div-->
                                    <!--<div class="cliplog_options_list">
                                    <?php if ($add_collection) { ?>
                                        <?php foreach ($add_collection_list as $collection_item) { ?>
                                            <?php if (!isset($add_collection) || (is_array($add_collection) && !in_array($collection_item['id'], $add_collection))) { ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <label class="checkbox"><input type="checkbox" value="<?php echo $collection_item['name']; ?>" name="option-<?php echo $collection_item['id']; ?>" data-formkeeper="keywords"> <?php echo $collection_item['search_term']; ?></label>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php foreach ($add_collection_list as $collection_item) { ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <label class="checkbox"><input type="checkbox" value="<?php echo $collection_item['name']; ?>" name="option-<?php echo $collection_item['id']; ?>" data-formkeeper="keywords"> <?php echo $collection_item['search_term']; ?></label>
                                        <?php } ?>
                                    <?php } ?>
    
                                    </div>-->
                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="Collection"
                                                   value="add_collection" <?php echo isset($add_collection) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!--                <tr class="control-group">
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Sequence:</span>
                                </td>
                                <td class="right">
                                    <select name="sections_values[sequence_id]">
                                        <option value="">Select Sequence</option>
                <?php //foreach ($sequences as $value) {
                ?>
                                                                                                                                                                                                                                                                                                                                                                                                        <option <?php //echo ($clip['sequence_id'] == $value['id']) ? 'selected="selected"' : '' ?> value="<?php //$value['id']; ?>"><?php //$value['title']; ?></option>
                <?php // }
                ?>
                                    </select>                                   
                                </td>
                                <td class="section-switch-cont">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>-->
                <tr class="control-group cliplog_section"
                    id="date_filmed"<?php if (!isset($date_filmed)) { ?> style="display: none;"<?php } ?>>
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Date Filmed:</span>
                                    <?php if (isset($hints['date_filmed'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['date_filmed']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[date_filmed]"
                                               value="date_filmed" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select class="input-small" name="sections_values[date_filmed][month]"
                                            data-formkeeper="keywords">
                                        <option value="">Month</option>
                                        <option
                                            value="1" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 1 && !$reset) ? 'selected' : ''; ?>>
                                            January
                                        </option>
                                        <option
                                            value="2" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 2 && !$reset) ? 'selected' : ''; ?>>
                                            February
                                        </option>
                                        <option
                                            value="3" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 3 && !$reset) ? 'selected' : ''; ?>>
                                            March
                                        </option>
                                        <option
                                            value="4" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 4 && !$reset) ? 'selected' : ''; ?>>
                                            April
                                        </option>
                                        <option
                                            value="5" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 5 && !$reset) ? 'selected' : ''; ?>>
                                            May
                                        </option>
                                        <option
                                            value="6" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 6 && !$reset) ? 'selected' : ''; ?>>
                                            June
                                        </option>
                                        <option
                                            value="7" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 7 && !$reset) ? 'selected' : ''; ?>>
                                            July
                                        </option>
                                        <option
                                            value="8" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 8 && !$reset) ? 'selected' : ''; ?>>
                                            August
                                        </option>
                                        <option
                                            value="9" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 9 && !$reset) ? 'selected' : ''; ?>>
                                            September
                                        </option>
                                        <option
                                            value="10" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 10 && !$reset) ? 'selected' : ''; ?>>
                                            October
                                        </option>
                                        <option
                                            value="11" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 11 && !$reset) ? 'selected' : ''; ?>>
                                            November
                                        </option>
                                        <option
                                            value="12" <?php echo (isset($date_filmed['month']) && $date_filmed['month'] == 12 && !$reset) ? 'selected' : ''; ?>>
                                            December
                                        </option>
                                    </select>
                                    <select class="input-small" name="sections_values[date_filmed][year]"
                                            data-formkeeper="keywords">
                                        <option value="">Year</option>
                                        <?php for ($i = (int)date('Y');
                                                   $i >= 1970;
                                                   $i--) { ?>
                                            <option
                                                value="<?php echo $i; ?>" <?php echo (isset($date_filmed['year']) && $date_filmed['year'] == $i && !$reset) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="Date Filmed"
                                                   value="date_filmed" <?php echo isset($date_filmed) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="license_type"<?php if (!isset($license_type)) { ?> style="display: none;"<?php } ?>>
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">License Type:<span class="require"></span></span>
                                    <?php if (isset($hints['license_type'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['license_type']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[license_type]"
                                               value="license_type" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[license_type]" data-formkeeper="keywords">
                                        <option value=""><?php echo $defaultSelect; ?></option>
                                        <?php foreach ($license_types as $license_type_item) { ?>
                                            <option
                                                value="<?php echo $license_type_item['id']; ?>" <?php echo (isset($license_type) && $license_type == $license_type_item['id'] && !$reset) ? 'selected' : ''; ?>><?php echo $license_type_item['name']; ?></option>
                                        <?php } ?>
                                    </select>

                                    <?php /* if($license_type == ''){ ?>
                                      <label class="radio inline"><input type="radio" checked="checked" name="sections_values[license_type]" value="" data-formkeeper="keywords"><?php echo $defaultSelect;?></label>
                                      <?php foreach($license_types as $license_type_item) { ?>
                                      <label class="radio inline"><input type="radio" value="<?php echo $license_type_item['id']; ?>" name="sections_values[license_type]" data-formkeeper="keywords"> <?php echo $license_type_item['name']; ?></label>
                                      <?php }
                                      }else{ ?>
                                      <label class="radio inline"><input type="radio" name="sections_values[license_type]" value="" data-formkeeper="keywords"><?php echo $defaultSelect;?></label>
                                      <?php foreach($license_types as $license_type_item) { ?>
                                      <label class="radio inline"><input type="radio"<?php if(isset($license_type) && $license_type == $license_type_item['id']) echo ' checked="checked" ';?>value="<?php echo $license_type_item['id']; ?>" name="sections_values[license_type]" data-formkeeper="keywords"> <?php echo $license_type_item['name']; ?></label>
                                      <?php }
                                      } */ ?>
                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="License Type"
                                                   value="license_type" <?php echo isset($license_type) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="price_level"<?php if (!isset($price_level)) { ?> style="display: none;"<?php } ?>>
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Price Level:<span class="require"></span></span>
                                    <?php if (isset($hints['price_level'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['price_level']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[price_level]"
                                               value="price_level" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[price_level]" class="input-small"
                                            data-formkeeper="keywords">
                                        <option
                                            value="" <?php // echo (!$this->uri->segment( 4 )) ? 'selected' : '';                                                                                                                                                                                                       ?>><?php echo $defaultSelect; ?></option>
                                        <option
                                            value="1" <?php echo (isset($price_level) && $price_level == 1 && !$reset) ? 'selected' : ''; ?>>
                                            Budget
                                        </option>
                                        <option
                                            value="2" <?php echo ((isset($price_level) && $price_level == 2) || ((!isset($price_level) || !$price_level) && $this->uri->segment(4))) ? 'selected' : ''; ?>>
                                            Standard
                                        </option>
                                        <option
                                            value="3" <?php echo (isset($price_level) && $price_level == 3 && !$reset) ? 'selected' : ''; ?>>
                                            Premium
                                        </option>
                                        <option
                                            value="4" <?php echo (isset($price_level) && $price_level == 4 && !$reset) ? 'selected' : ''; ?>>
                                            Gold
                                        </option>
                                    </select>
                                    &nbsp;&nbsp;
                                    <a href="en/cliplog/index/pricingDetails/" target="_blank">View Pricing</a>
                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="Price Level"
                                                   value="price_level" <?php echo isset($price_level) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="releases"<?php if (!isset($releases)) { ?> style="display: none;"<?php } ?>>
                    <td>
                        <table>
                            <tr>
                                <td class="left">
                                    <span class="field_label">Releases:</span>
                                    <?php if (isset($hints['releases'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['releases']; ?>"><?php } ?>
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[releases]"
                                               value="releases" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[releases]" data-formkeeper="keywords">
                                        <option value=""><?php echo $defaultSelect; ?></option>
                                        <option
                                            value="Model Released" <?php echo (isset($releases) && $releases == 'Model Released' && !$reset) ? 'selected' : ''; ?>>
                                            Model Released
                                        </option>
                                        <option
                                            value="Property Released" <?php echo (isset($releases) && $releases == 'Property Released' && !$reset) ? 'selected' : ''; ?>>
                                            Property Released
                                        </option>
                                        <option
                                            value="Model and Property Released" <?php echo (isset($releases) && $releases == 'Model and Property Released' && !$reset) ? 'selected' : ''; ?>>
                                            Model and Property Released
                                        </option>
                                    </select>
                                    <br>

                                    <input type="file" name="upload_release">
                                    <br>
                                    <?php
                                    if ($clip['release_file']) {
                                        $location_info = parse_url($clip['release_file']);
                                        $s3_host = $this->config->item('s3_host');
                                        $path = 'http://' . $s3_host . '/' . $location_info['host'] . $location_info['path'];
                                        ?>
                                        <a href="<?php echo $path; ?>" target="_blank">Download File</a>

                                    <?php } ?>

                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="Releases"
                                                   value="releases" <?php echo isset($releases) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="control-group cliplog_section"
                    id="file_formats"<?php if (!isset($file_formats)) { ?> style="display: none;"<?php } ?>
                    data-formkeeper="keywords">
                    <td>
                        <table>


                            <tr style="display: table-row !important;">
                                <td class="left">
                                    <span class="field_label">File formats:<span class="require"></span></span>
                                    <?php if (isset($hints['file_formats'])) { ?><img
                                        src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                        title="<?php echo $hints['file_formats']; ?>"><?php } ?>
                                <td class="right">
                                    <input type="checkbox" name="sections[]" id="Add_formats"
                                           value="add_formats" <?php echo (isset($add_formats)) ? ' checked="checked"' : ''; ?>
                                           data-formkeeper="logging" style="display: none;"/>

                                    <div class="button-cont">
                                        <button
                                            class="button_add <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> cliplog_add_formats">
                                            Add formats
                                        </button>
                                    </div>
                                    <div class="button-cont">
                                        <button class="button_close cliplog_close_formats"></button>
                                    </div>
                                    <br><br>
                                </td>
                                <td class="section-switch-cont">
                                    <div class="switch-cont">
                                        <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                            <input type="checkbox" name="sections[]" id="File formats"
                                                   value="file_formats" <?php echo isset($file_formats) || !$cliplog_template ? 'checked' : ''; ?>
                                                   data-formkeeper="logging"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($clip && $clip['metadata']) { ?>
                                <tr class="<?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Format Metadata
                                    </td>
                                    <td class="right" colspan="2">
                                        <!--<a href="#" class="metadata-switcher">Show</a>-->
                                        <div class="metadata-container">
                                            <?php foreach ($clip['metadata'] as $key => $value) { ?>
                                                <?php echo ucwords(str_replace('_', ' ', $key)) . ': ' . $value; ?><br>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if ($clip) { ?>
                                <tr class="<?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Submission Filename
                                    </td>
                                    <td class="right" colspan="2">
                                        <?php echo $clip['original_filename']; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Camera Model
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][camera_model]"
                                               value="file_formats][camera_model" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <input type="text" name="sections_values[file_formats][camera_model]"
                                           value="<?php echo (isset($file_formats['camera_model']) && !$reset) ? $file_formats['camera_model'] : ''; ?>">
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Camera Sensor
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][camera_chip_size]"
                                               value="file_formats][camera_chip_size" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][camera_chip_size]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($camera_chip_sizes as $size) { ?>
                                            <option
                                                value="<?php echo esc($size['name']); ?>" <?php echo (isset($file_formats['camera_chip_size']) && $file_formats['camera_chip_size'] == $size['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $size['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Bit Depth
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][bit_depth]"
                                               value="file_formats][bit_depth" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][bit_depth]" data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($bit_depths as $bit_depth) { ?>
                                            <option
                                                value="<?php echo $bit_depth['name']; ?>" <?php echo (isset($file_formats['bit_depth']) && $file_formats['bit_depth'] == $bit_depth['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $bit_depth['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Chroma Subsampling
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][color_space]"
                                               value="file_formats][color_space" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][color_space]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($color_spaces as $color_space) { ?>
                                            <option
                                                value="<?php echo $color_space['name']; ?>" <?php echo (isset($file_formats['color_space']) && $file_formats['color_space'] == $color_space['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $color_space['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Format
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][source_format]"
                                               value="file_formats][source_format" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][source_format]"
                                            class="select_source_format" data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($source_formats as $format) { ?>
                                            <option
                                                value="<?php echo $format['format']; ?>" <?php echo (isset($file_formats['source_format']) && $file_formats['source_format'] == $format['format'] && !$reset) ? 'selected' : ''; ?>><?php echo $format['format']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Codec
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][source_codec]"
                                               value="file_formats][source_codec" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][source_codec]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($file_compressions as $file_compression) { ?>
                                            <option
                                                value="<?php echo $file_compression['name']; ?>" <?php echo (isset($file_formats['source_codec']) && $file_formats['source_codec'] == $file_compression['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $file_compression['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Frame Size
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][source_frame_size]"
                                               value="file_formats][source_frame_size" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][source_frame_size]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($frame_sizes as $frame_size) { ?>
                                            <option
                                                value="<?php echo $frame_size['name']; ?>" <?php echo (isset($file_formats['source_frame_size']) && $file_formats['source_frame_size'] == $frame_size['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $frame_size['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Source Frame Rate
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][source_frame_rate]"
                                               value="file_formats][source_frame_rate" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select name="sections_values[file_formats][source_frame_rate]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($frame_rates as $rate) { ?>
                                            <option
                                                value="<?php echo $rate['name']; ?>" <?php echo (isset($file_formats['source_frame_rate']) && $file_formats['source_frame_rate'] == $rate['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $rate['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php if ($is_admin) { ?>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Master Format
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][master_format]"
                                                   value="file_formats][master_format" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select name="sections_values[file_formats][master_format]"
                                                data-formkeeper="keywords">
                                            <option value="">--Select--</option>
                                            <?php foreach ($master_formats as $format) { ?>
                                                <option
                                                    value="<?php echo $format['format']; ?>" <?php echo (isset($file_formats['master_format']) && $file_formats['master_format'] == $format['format'] && !$reset) ? 'selected' : ''; ?>><?php echo $format['format']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Master Frame Size
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][master_frame_size]"
                                                   value="file_formats][master_frame_size" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select name="sections_values[file_formats][master_frame_size]"
                                                data-formkeeper="keywords">
                                            <option value="">--Select--</option>
                                            <?php foreach ($frame_sizes as $frame_size) { ?>
                                                <option
                                                    value="<?php echo $frame_size['name']; ?>" <?php echo (isset($file_formats['master_frame_size']) && $file_formats['master_frame_size'] == $frame_size['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $frame_size['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Master Frame Rate
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][master_frame_rate]"
                                                   value="file_formats][master_frame_rate" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select name="sections_values[file_formats][master_frame_rate]"
                                                data-formkeeper="keywords">
                                            <option value="">--Select--</option>
                                            <?php foreach ($frame_rates as $rate) { ?>
                                                <option
                                                    value="<?php echo $rate['name']; ?>" <?php echo (isset($file_formats['master_frame_rate']) && $file_formats['master_frame_rate'] == $rate['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $rate['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php } ?>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Submission Codec <span class="require"></span>

                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][digital_file_format]"
                                               value="file_formats][digital_file_format" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select
                                        <?php if (!$is_admin && in_array('digital_file_format', $only_admin_editable_fields)) {?> disabled="disabled" <?} ?>
                                            name="sections_values[file_formats][digital_file_format]"
                                            id="select_submission_codec" data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($submission_codecs as $codec) { ?>
                                            <option
                                                value="<?php echo $codec['name']; ?>" <?php echo (isset($file_formats['digital_file_format']) && $file_formats['digital_file_format'] == $codec['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $codec['name']; ?></option>
                                        <?php } ?>
                                        <?php if(!empty($file_formats['digital_file_format']) and array_search($file_formats['digital_file_format'], array_column($submission_codecs, 'name')) === false){ ?>
                                            <option value="<?php echo $file_formats['digital_file_format']; ?>" selected ><?php echo $file_formats['digital_file_format']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Submission Data Rate (Mbps)
                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][source_data_rate]"
                                               value="file_formats][source_data_rate" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <input type="text" name="sections_values[file_formats][source_data_rate]"
                                           value="<?php echo (isset($file_formats['source_data_rate']) && !$reset) ? $file_formats['source_data_rate'] : ''; ?>">
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Submission Frame Size <span class="require"></span>

                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][digital_file_frame_size]"
                                               value="file_formats][digital_file_frame_size" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select
                                            <?php if (!$is_admin && in_array('digital_file_frame_size', $only_admin_editable_fields)) {?> disabled="disabled" <?} ?>
                                            name="sections_values[file_formats][digital_file_frame_size]"
                                            class="submission_frame_size" data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($frame_sizes as $frame_size) { ?>
                                            <option
                                                value="<?php echo $frame_size['name']; ?>" <?php echo (isset($file_formats['digital_file_frame_size']) && $file_formats['digital_file_frame_size'] == $frame_size['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $frame_size['name']; ?></option>
                                        <?php } ?>
                                        <?php if(!empty($file_formats['digital_file_frame_size']) and array_search($file_formats['digital_file_frame_size'], array_column($frame_sizes, 'name')) === false){ ?>
                                            <option value="<?php echo $file_formats['digital_file_frame_size']; ?>" selected ><?php echo $file_formats['digital_file_frame_size']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                <td class="left">
                                    Submission Frame Rate <span class="require"></span>

                                    <div
                                        class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                        <input type="checkbox" name="overwrite[file_formats][digital_file_frame_rate]"
                                               value="file_formats][digital_file_frame_rate" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                        overwrite existing values
                                    </div>
                                </td>
                                <td class="right">
                                    <select
                                        <?php if (!$is_admin && in_array('digital_file_frame_rate', $only_admin_editable_fields)) {?> disabled="disabled" <?} ?>
                                            name="sections_values[file_formats][digital_file_frame_rate]"
                                            data-formkeeper="keywords">
                                        <option value="">--Select--</option>
                                        <?php foreach ($frame_rates as $rate) { ?>
                                            <option
                                                value="<?php echo $rate['name']; ?>" <?php echo (isset($file_formats['digital_file_frame_rate']) && $file_formats['digital_file_frame_rate'] == $rate['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $rate['name']; ?></option>
                                        <?php } ?>
                                        <?php if(!empty($file_formats['digital_file_frame_rate']) and array_search($file_formats['digital_file_frame_rate'], array_column($frame_rates, 'name')) === false){ ?>
                                            <option value="<?php echo $file_formats['digital_file_frame_rate']; ?>" selected ><?php echo $file_formats['digital_file_frame_rate']; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php if ($is_admin) { ?>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Delivery Category
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][pricing_category]"
                                                   value="file_formats][pricing_category" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select name="sections_values[file_formats][pricing_category]"
                                                id="select_delivery_category" data-formkeeper="keywords">
                                            <option value="">--Select--</option>
                                            <?php foreach ($delivery_categories as $cat) { ?>
                                                <option
                                                    value="<?php echo $cat['id']; ?>" <?php echo (isset($file_formats['pricing_category']) && $file_formats['pricing_category'] == $cat['id'] && !$reset) ? 'selected' : ''; ?>><?php echo $cat['description'] . '(' . $cat['id'] . ')'; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        Lab
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][master_lab]"
                                                   value="file_formats][master_lab" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <select name="sections_values[file_formats][master_lab]"
                                                data-formkeeper="keywords">
                                            <option value="">--Select--</option>
                                            <?php if (empty($file_formats['master_lab'])) $file_formats['master_lab'] = 'Deluxe Media (Digital Files Only)'; ?>
                                            <?php foreach ($labs as $lab) { ?>
                                                <option
                                                    value="<?php echo $lab['name']; ?>" <?php echo (isset($file_formats['master_lab']) && $file_formats['master_lab'] == $lab['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $lab['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        File Size (MB)
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][file_size_mb]"
                                                   value="file_formats][file_size_mb" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <input type="text" name="sections_values[file_formats][file_size_mb]"
                                               value="<?php echo (isset($file_formats['file_size_mb']) && !$reset) ? $file_formats['file_size_mb'] : ''; ?>">
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr class=" <?php echo (isset($add_formats)) ? 'collapsed' : 'expanded'; ?> ">
                                    <td class="left">
                                        File Wrapper
                                        <div
                                            class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                            <input type="checkbox" name="overwrite[file_formats][file_wrapper]"
                                                   value="file_formats][file_wrapper" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                            overwrite existing values
                                        </div>
                                    </td>
                                    <td class="right">
                                        <input type="text" name="sections_values[file_formats][file_wrapper]"
                                               value="<?php echo (isset($file_formats['file_wrapper']) && !$reset) ? $file_formats['file_wrapper'] : ''; ?>">
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php } ?>
                            <!---->

                        </table>
                    </td>
                </tr>


                <?php $this->load->view('cliplog/edit/sections/keywords_sections'); ?>


            </table>
        </div>
        <div class="clearfix"></div>
    </div>

    <div id="dialog-keywords-manage" style="overflow: hidden;">
        <span class="id" data-id=""></span>

        <div class="form">
            <p class="title">Manage All Keywords</p>

            <div>
                <input type="text" value="" class="cliplog_keyword_input is-popup">

                <div class="button-wrap">
                    <button class="button_add_green cliplog_add_keyword_to_popup_list_new">Add</button>
                </div>
            </div>
        </div>
        <div class="die-ipad-die" style="height: 196px; overflow-x: hidden; overflow-y: auto; width: 490px;">
            <div class="dialog-keyword-list"></div>
        </div>
    </div>

    <?php if ($clips_ids) { ?>
        <input type="hidden" value="<?php echo $clips_ids ?>" name="clips_ids">
    <?php } ?>
    <br/><br/><br/><br/><br/>

    <div class="form-actions">
        <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        <!-- input type="submit" class="btn btn-primary" value="Show clip metadata" name="show-clip-metadata" -->
        <?php if ($next_clip_id) { ?>

            <label for="goto-next" class="label-goto-next">
                <input type="checkbox" id="goto-next" class="goto-next" name="next_clip_id"
                       value="<?php echo $next_clip_id; ?>" <?php if ($goto_next_active) echo 'checked="checked"'; ?> />
                View Next Clip
            </label>
        <?php } ?>

        <span class="formRight">
            <a href="javascript:;" class="moveClipOnlineOffline"
               data-id="<?php echo ($clips_ids != '') ? $clips_ids : $clip['id']; ?>" data-status="1">Move
                Online</a>&nbsp;| &nbsp;
            <a href="javascript:;" class="moveClipOnlineOffline"
               data-id="<?php echo ($clips_ids != '') ? $clips_ids : $clip['id']; ?>" data-status="0">Move
                Offline</a>
            <?php if ($clips_ids == '') { ?>
                &nbsp;|&nbsp;
                <a href="javascript:;" class="copy_prev_keyqords">Copy Keywords From Previous Clip</a>
            <?php } ?>
        </span>
    </div>

</form>
<!--carousel js-->

<!-- Reload -->
<div class="reload-layout">
    <div class="image">
        <div id="bowlG">
            <div id="bowl_ringG">
                <div class="ball_holderG">
                    <div class="ballG">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- dialogBox() && alertBox() -->
<div id="dialog-confirm" style="display: none;">
    <p class="message"></p>
</div>
<div id="dialog-alert" style="display: none;">
    <p class="message"></p>
</div>
<!-- changeThumbBox -->
<div id="dialog-change-thumb" style="display: none;" data-id="<?php echo $clip['id']; ?>">
    <div class="clip">
        <div id="footagesearch-clip" class="footagesearch-preview-clip">
            <video class="video-js vjs-default-skin change-thumb-video" preload="auto" controls width="320" height="230"
                   muted
                   data-setup="{}">
                <source src="<?php echo $clip['motion_thumb']; ?>" type="video/mp4"/>
            </video>
        </div>
    </div>
    <div class="thumb"></div>
</div>
<!-- clipPreviewBox -->
<div id="footagesearch-clip-preview" style="display: none;">
    <h6 class="title"></h6>
    <video id="" class="video-js vjs-default-skin" preload="auto" width="432" height="240" muted data-setup="{}">
        <source src="" type="video/mp4">
    </video>
    <p class="description"></p>
</div>

<script type="text/javascript" src="/data/js/cliplog.keywordsManager.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.savingnotice.js<?php echo '?' . date('dmYh'); ?>"></script>

<!-- cliplog.keywordsManager.js -->
<div class="keywordsManagerDebug"
     style="display: none; position: fixed; bottom: 0; left: 0; width: 300px; height: 286px; background: #fff; border: 1px solid #ccc; padding: 6px; overflow-x: hidden; overflow-y: auto; tab-size: 4; font-size: 11px; font-family: monospace; color: #666;"></div>
<script type="text/javascript">
    $(function () {
        $(".keywordsManagerDebug").draggable();
    });  //NEw Script By IMRAN


    //Getting the Keywords For User Against each Section;
    //        $(document).on('click', 'a.inline', function() {
    //            var sectionName = $(this).attr('data-id');
    //            var currentVal = $('#checkingFeilddata').val();
    //
    //            $.ajax({
    //                url: "ajax.php",
    //                data: {action: 'getUserKeywords', sectionName: sectionName, userid: <?php echo $this->session->userdata('uid'); ?>, currentVal: currentVal},
    //                type: "POST",
    //                success: function(data) {
    //
    //                    $('#dataInsert' + sectionName).html(data)
    //                    console.log(data);
    //                }
    //            });
    //
    //        });


    //Adding the Keywords For User Against each Section;
    //        $(document).on('click', 'input.addDataUserButton', function() {
    //
    //            var sectionName = $(this).attr('data-id');
    //            var keyword = $('#addNewFeild' + sectionName).val();
    //
    //            var currentVal = $('#checkingFeilddata').val();
    //            $.ajax({
    //                url: "ajax.php",
    //                data: {action: 'addUserKeywords', sectionName: sectionName, userid: <?php echo $this->session->userdata('uid'); ?>, keyword: keyword, currentVal: currentVal},
    //                type: "POST",
    //                success: function(data) {
    //                    var dataHtml = $('#dataInsert' + sectionName).html();
    //                    $('#addNewFeild' + sectionName).val('');
    //                    $('#dataInsert' + sectionName).html(data + dataHtml);
    //                }
    //            });
    //
    //        });

    //        $(document).on('click', 'input.checkBoxId', function() {
    //
    //            if ((this).checked) {
    //                var idInsert = $(this).attr('value');
    //                var keywordText = $(this).attr('data-text');
    //                var keywordSection = $(this).attr('data-sect');
    //
    //
    //                var currentVal = $('#checkingFeilddata').val();
    //                if (currentVal == '') {
    //                    var InseertVal = idInsert;
    //                } else {
    //                    var InseertVal = currentVal + ',' + idInsert;
    //                }
    //
    //                $('#checkingFeilddata').val(InseertVal);
    //
    //
    //                var HTML = '<div class="item-wrapper">\n\
    //            <a class="item-cross is-hidden"></a>\n\
    //            <div class="item">\n\
    //            <input type="hidden" checked="checked" value="' + idInsert + '" name="userKeywordsInsert[' + idInsert + ']">' + keywordText + '\n\
    //            </div>\n\
    //            </div>';
    //
    //                $('.cliplog_selected_keywords_list_new_users' + keywordSection).append(HTML);
    //
    //            } else {
    //
    //                var idInsert = $(this).attr('value');
    //                var currentVal = $('#checkingFeilddata').val();
    //
    //                var NewInsertVal = currentVal.replace(idInsert, "");
    //                $('#checkingFeilddata').val(NewInsertVal);
    //
    //            }
    //        });


    //DELETE THE USer Keyword\


    //        function deleteData(dataId) {
    //
    //            var deleteDataId = dataId;
    //
    //            $.ajax({
    //                url: "ajax.php",
    //                data: {action: 'deleteUserKeywords', clipId: deleteDataId},
    //                type: "POST",
    //                success: function(data) {
    //                    $('.newCrossDel' + dataId).html('');
    //                }
    //            });
    //        }
    //checkingFeilddata

    //'<div class="item-wrapper"><a class="item-cross is-hidden"></a><div class="item"><input type="hidden" checked="checked" value="%keywordId%" name="keywords[%keywordId%]">%keywordText%</div></div>',

</script>

<script>
    $(document).ready(function () {



        //var HtmlToChange = $('.cliplog_template_header').html().replace("(modified)", "");
        //$('.cliplog_template_header').html(HtmlToChange);


        $('body').prepend('<div class="videohover"></div>').on('mousemove', function () {
            var isFirefox = typeof InstallTrigger !== 'undefined';
            if (!isFirefox) {
                if (!e)
                    var e = window.event;
                if (e.pageX || e.pageY) {
                    $('.videohover').css({'left': e.pageX + 20, 'top': e.pageY - 232});
                }
            }

        });
        $('.containerGridGallery').on('mouseover', function () {
            $('.videohover').css('opacity', 1);
            var videourl = $(this).attr('att-preview');
            var code = $(this).attr('att-code');
            var descp = $(this).attr('att-descp');
            var videoembed = '<div style="width:100%;border: 1px solid #ccc;padding:10px;background-color:#ffffff;border-radius:5px;float:left"><h6 class="title">' + code + '</h6><video width="100%" height="100%" poster="http://backend.nfstage.com/data/img/loading2-video.gif" preload="none" autoplay loop muted><p>Sorry, your browser does not support HTML5 video.</p><source src="' + videourl + '" type="video/mp4"></video><span>' + descp + '</span></div>';
            //console.log(videoembed);
            $('.videohover').html(videoembed);
            if (window.stop !== undefined) {
                // window.stop();
            } else if (document.execCommand !== undefined) {
                document.execCommand("Stop", false);
            }

        }).on('mouseout', function () {
            $('.videohover').css('opacity', 0).empty();
        });
    });

    $('.goto-next').click(function () {
        if ($(this).is(':checked'))
            $(".goto-next").prop("checked", true);
        else
            $(".goto-next").prop("checked", false);
    });
</script>
<style>
    .videohover {
        width: 300px;
        height: 216px;
        z-index: 1000;
        background-color: transparent;
        position: absolute;
        right: 0px;
        left: 0px;
        opacity: 0;
        /*        transition: opacity 0.2s, transform 0.2s; */
    }


</style>

<input type="hidden" value="<?php echo $this->session->userdata('uid'); ?>" name="myHiddenVal" id="myHiddenVal">
<input type="hidden" value="" name="hiddenInactiveTemplate" id="hiddenInactiveTemplate">
<div style="visibility: hidden" id="myTestDiv"></div>
<input type="hidden" value="0" name="crousalCheckFirst" id="crousalCheckFirst">
