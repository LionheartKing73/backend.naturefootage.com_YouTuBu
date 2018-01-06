<style type="text/css">
    @import url(/data/css/cliplog_clip.css);
    @import url(/data/js/videojs/video-js-3.2.css);
    @import url(/data/js/videojs/video-speed.css);
</style>

<script type="text/javascript" src="/data/js/videojs/video-3.2.js"></script>
<script type="text/javascript" src="/data/js/videojs/video-speed.js"></script>
<script type="text/javascript" src="/data/js/videojs/video-controls.js"></script>
<script type="text/javascript" src="/data/js/cliplog_view.js"></script>
<script>
    videojs.options.flash.swf = "/data/js/videojs/video-js.swf"
</script>

<div id="header">
    <div class="logo">
        <a href="http://stage.naturefootage.com">
            <img id="logo" src="/data/img/nf_header_logo.png" alt="Nature Footage">
        </a>
    </div>
</div>
<?php
$meta = json_decode($clip['metadata']);
$stream = $meta->streams[0];
if (strpos($clip['original_filename'], 'VCW') || strpos($clip['original_filename'], 'VCCW'))
    $clipRotate = true;
?>
<?php
//echo "<pre>";
//print_r($clip);
//echo "</pre>";
?>
<h1 style="color:#744c07"><?php if ($clip['description']) echo $clip['description']; ?></h1>
<hr class="footagesearch-preview-clip-divider"/>


<div id="footagesearch-clip-1" class="footagesearch-preview-clip"
     style="width: <?php echo $streamW = ($stream->width && $stream->height && (($stream->width < $stream->height && empty($clipRotate)) || ($stream->width > $stream->height && !empty($clipRotate)))) ? '360' : '640'; ?>px;">
    <div class="footagesearch-preview-clip-top">
        <h2><?php echo $clip['code']; ?></h2>
        <div class="footagesearch-preview-clip-license footagesearch-license-<?php echo $clip['license']; ?>"><img
                src="/data/img/admin/cliplog/view/license-<?php echo $clip['license']; ?>.gif"></div>
        <div class="footagesearch-preview-clip-duration"><?php echo round($clip['duration']) ?> sec</div>
        <div class="clear"></div>
    </div>
    <video id="footagesearch-preview-player<?php echo $clip['id']; ?>" class="video-js vjs-default-skin" preload="auto"
           autoplay controls width="<?php
    $streamW = ($stream->width && $stream->height && ($stream->width < $stream->height && empty($clipRotate))) ? '360' : '640';
    echo $streamW;
    ?>" height="auto" muted data-setup="{}" style="margin: 0 auto;">
        <source src="<?php echo $clip['res']; ?>" type="video/mp4"/>
    </video>
    <div class="footagesearch-preview-clip-action">
        <!--<div class="footagesearch-clip-preview-play-forward-actions">
            <img id="play_<?php echo $clip['id']; ?>" src="/data/img/admin/cliplog/view/play_icon.png" alt="" class="footagesearch-clip-preview-play-btn" style="display:none;"><img id="pause_<?php echo $clip['id']; ?>" src="/data/img/admin/cliplog/view/pause_icon.png" alt="" class="footagesearch-clip-preview-pause-btn"><img id="forward_<?php echo $clip['id']; ?>" src="/data/img/admin/cliplog/view/forward_icon.png" alt="" class="footagesearch-clip-preview-forward-btn"><img id="forward3x_<?php echo $clip['id']; ?>" src="/data/img/admin/cliplog/view/forward3x_icon.png" alt="" class="footagesearch-clip-preview-forward3x-btn">
        </div>-->
        <div class="footagesearch-clip-cart-clipbin-actions">

            <input type="hidden" name="user_login_id" value="<?php echo $this->session->userdata('uid'); ?>"
                   id="user_login_id">
            <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                <?php foreach ($clip['rating_result'] as $rating) {
                    $rating_id = $rating['id'];
                }
                ?>

                <?php
                if ($clip['current_user_like'] > 0) {

                    ?>
                    <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes ?> Like(s)</div>
                    <div class="inner_like transitiable">

                        <img onClick="deleteLikes('<?php echo $rating_id ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                             src="/data/img/cliplog/like-fill.png">

                    </div>
                <?php } else {
                    ?>
                    <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes ?> Like(s)</div>
                    <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                            onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $rating_id ?>')"><img
                                src="/data/img/cliplog/like-icon.png"></a>

                    </div> <?php
                }
                ?>

            </div>

            <? if (($clip['backend_lb_id'] && $clip['is_default']) || (!empty($_REQUEST['backend_clipbin']) && $clip['default_clipbin'] != $_REQUEST['backend_clipbin'])) { ?>
                <a href="" class="clipbin-delete-item bin"
                   data-bin-id="<?php echo (!empty($_REQUEST['backend_clipbin'])) ? $_REQUEST['backend_clipbin'] : $clip['default_clipbin']; ?>"
                   data-clip-id="<?php echo $clip['id']; ?>">
                    <!--<img src="/data/img/admin/cliplog/view/bin_remove.png">-->
                </a>
                <a href="" class="clipbin-add-item bin"
                   data-bin-id="<?php echo (!empty($_REQUEST['backend_clipbin'])) ? $_REQUEST['backend_clipbin'] : $clip['default_clipbin']; ?>"
                   data-clip-id="<?php echo $clip['id']; ?>" style="display:none;">
                    <!--<img src="/data/img/admin/cliplog/view/bin_add.png">-->
                </a>
            <? } else { ?>
                <a class="clipbin-delete-item bin" data-bin-id="<?php echo $clip['default_clipbin']; ?>"
                   data-clip-id="<?php echo $clip['id']; ?>" style="display:none;">
                    <!--<img src="/data/img/admin/cliplog/view/bin_remove.png">-->
                </a>
                <a class="clipbin-add-item bin" data-bin-id="<?php echo $clip['default_clipbin']; ?>"
                   data-clip-id="<?php echo $clip['id']; ?>">
                    <!--<img src="/data/img/admin/cliplog/view/bin_add.png">-->
                </a>
            <? } ?>
            <a target="_blank" href="<?php echo $clip['download'] . $userstring; ?>"><img
                    src="/data/img/admin/cliplog/view/download_icon.png" alt=""></a>
        </div>
        <div class="clr"></div>
    </div>
</div>
<?php if ($prev_clip_link) { ?>
    <a href="<?php echo $prev_clip_link; ?>" class="footagesearch-preview-clip-prev"><img
            src="<?php echo get_template_directory_uri(); ?>/images/prev_btn.jpg"></a>
<?php } ?>
<?php if ($next_clip_link) { ?>
    <a href="<?php echo $next_clip_link; ?>" class="footagesearch-preview-clip-next"><img
            src="<?php echo get_template_directory_uri(); ?>/images/next_btn.jpg"></a>
<?php } ?>


<hr class="footagesearch-preview-clip-divider"/>


<div class="footagesearch-preview-clip-details">
    <h2>Clip Details</h2>
    <table>
        <?php
        $clip['source_format_display'] = array();
        if ($clip['source_format']) {
            $clip['source_format_display'][] = $clip['source_format'] . ($clip['camera_chip_size'] ? ' (' . $clip['camera_chip_size'] . ')' : '');
        }
        if ($clip['source_frame_size']) {
            $clip['source_format_display'][] = $clip['source_frame_size'];
        }
        if ($clip['source_frame_rate']) {
            $clip['source_format_display'][] = $clip['source_frame_rate'];
        }
        if ($clip['source_codec']) {
            $clip['source_format_display'][] = $clip['source_codec'];
        }
        if ($clip['bit_depth']) {
            $clip['source_format_display'][] = $clip['bit_depth'];
        }
        if ($clip['color_space']) {
            $clip['source_format_display'][] = $clip['color_space'];
        }
        ?>

        <?php
        if (!empty($arraySequecne)) {
            ?>
            <tr>

                <td width="100">Sequence</td>
                <td> <?php foreach ($arraySequecne as $key => $data) { ?>
                        <a href="/en/cliplog/view.html?backend_clipbin=<?php echo $data['id']; ?>">
                            <?php
                            echo $data['title'];
                            echo '&nbsp;&nbsp;&nbsp;';
                            ?>

                        </a>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }


        if (!empty($arrayClipbin)) {
            ?>
            <tr>

                <td width="100">Clip bin</td>
                <td> <?php foreach ($arrayClipbin as $key => $data) { ?>
                        <a href="/en/cliplog/view.html?backend_clipbin=<?php echo $data['id']; ?>">
                            <?php
                            echo $data['title'];
                            echo '&nbsp;&nbsp;&nbsp;';
                            ?>

                        </a>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>


        <tr>
            <td width="100">Description</td>
            <td><?php if ($clip['description']) { ?><?php echo $clip['description']; ?><?php } ?></td>
        </tr>


            <tr>
                <td>Source Format</td>
                <td>  <?php if ($clip['source_format_display']) { ?><?php echo implode(', ', $clip['source_format_display']); ?><?php } ?></td>
            </tr>

        <?php if ($clip['shot_type_keyword'] || (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00')) { ?>
            <tr>
                <td>The Shot</td>
                <td>
                    <?php
                    if ($clip['shot_type_keyword']) {
                        if (is_array($clip['shot_type_keyword'])) {
                            foreach ($clip['shot_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['shot_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php if (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00') { ?>
                        <div
                            class="footagesearch-preview-clip-keyword"><?php echo date('d.m.Y', strtotime($clip['film_date'])); ?></div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
        <?php if ($clip['subject_category_keyword'] || $clip['primary_type_keyword'] || $clip['other_type_keyword'] || $clip['appereance_type_keyword'] || $clip['concept_type_keyword']) { ?>
            <tr>
                <td>Subject</td>
                <td>
                    <?php
                    if ($clip['subject_category_keyword']) {
                        if (is_array($clip['subject_category_keyword'])) {
                            foreach ($clip['subject_category_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['subject_category_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['primary_type_keyword']) {
                        if (is_array($clip['primary_type_keyword'])) {
                            foreach ($clip['primary_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['primary_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['other_type_keyword']) {
                        if (is_array($clip['other_type_keyword'])) {
                            foreach ($clip['other_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['other_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['appereance_type_keyword']) {
                        if (is_array($clip['appereance_type_keyword'])) {
                            foreach ($clip['appereance_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['appereance_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['concept_type_keyword']) {
                        if (is_array($clip['concept_type_keyword'])) {
                            foreach ($clip['concept_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['concept_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>

        <?php if ($clip['action_type_keyword']) { ?>
            <tr>
                <td>Action</td>
                <td>
                    <?php
                    if ($clip['action_type_keyword']) {
                        if (is_array($clip['action_type_keyword'])) {
                            foreach ($clip['action_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['action_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
        <?php if ($clip['time_type_keyword'] || $clip['habitat_type_keyword'] || $clip['location_type_keyword'] || $clip['country']) { ?>
            <tr>
                <td>Environment</td>
                <td>
                    <?php
                    if ($clip['time_type_keyword']) {
                        if (is_array($clip['time_type_keyword'])) {
                            foreach ($clip['time_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['time_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['habitat_type_keyword']) {
                        if (is_array($clip['habitat_type_keyword'])) {
                            foreach ($clip['habitat_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['habitat_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php
                    if ($clip['location_type_keyword']) {
                        if (is_array($clip['location_type_keyword'])) {
                            foreach ($clip['location_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <div class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></div>
                                    <?php
                                }
                            }
                        } else {
                            ?>
                            <div
                                class="footagesearch-preview-clip-keyword"><?php echo $clip['location_type_keyword']; ?></div><?php
                        }
                    }
                    ?>
                    <?php if ($clip['country']) { ?>
                        <div class="footagesearch-preview-clip-keyword"><?php echo $clip['country']; ?></div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<script type="text/javascript">
    function addLikes(id, action, deleteId) {
        $('.demo-table #tutorial-' + id + ' li').each(function (index) {
            $(this).addClass('selected');
            $('#tutorial-' + id + ' #rating').val((index + 1));
            if (index == $('.demo-table #tutorial-' + id + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = $('#user_login_id').val();
        var url = 'ajax.php';
        $.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            type: "POST",
            beforeSend: function () {
                $('#tutorial-' + id + ' .btn-likes').html("<img src='<?php echo site_url(); ?>/images/icons/LoaderIcon.gif' />");
            },
            success: function (data) {

                var result = data.split(',');
                var response_id = result[1];
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = result[0];

                switch (action) {
                    case "like":
                        $('#tutorial-' + id + ' .btn-likes').html('<img src="/data/img/cliplog/like-fill.png" />');
                        $('#tutorial-' + id + ' .inner_like').html('<img style="cursor:pointer;" onClick="deleteLikes(\'' + response_id + '\',\'unlike\',\'' + id + '\')" src="/data/img/cliplog/like-fill.png" />');
                        window.opener.location.reload(true);

                        //likes = likes+1;
                        break;
                    case "unlike":
                        $('#tutorial-' + id + ' .btn-likes').html('<input type="button" title="Like" class="like"  onClick="addLikes(' + id + ',\'like\')" />')
                        likes = likes - 1;
                        break;
                }
                $('#likes-' + id).val(likes);
                if (likes > 0) {
                    $('#tutorial-' + id + ' .label-likes').html(likes + " Like(s)");
                } else {
                    $('#tutorial-' + id + ' .label-likes').html('');
                }
            }
        });
    }

    function deleteLikes(id, action, clipId) {
        $('.demo-table #tutorial-' + clipId + ' li').each(function (index) {
            $(this).addClass('selected');
            $('#tutorial-' + clipId + ' #rating').val((index + 1));
            if (index == $('.demo-table #tutorial-' + clipId + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = $('#user_login_id').val();
        var url = 'ajax.php';
        $.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id + '&clip_id=' + clipId,
            type: "POST",
            beforeSend: function () {
                $('#tutorial-' + clipId + ' .btn-likes').html("<img src='<?php echo site_url(); ?>/images/icons/LoaderIcon.gif' />");
            },
            success: function (data) {

                //var result = data.split(',');
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = data;
                switch (action) {
                    case "like":
                        $('#tutorial-' + clipId + ' .btn-likes').html('<img src="/data/img/cliplog/like-fill.png" />');
                        $('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src=/data/img/cliplog/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        $('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="/data/img/cliplog/like-icon.png" />');

                        //likes = likes - 1;
                        break;
                }
                $('#likes-' + clipId).val(likes);

                if (likes > 0) {
                    $('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                } else {
                    $('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                }
            }
        });
    }
</script>
<style>

    .toCustom {
        top: 50px !important;
    }

    .footagesearch-clip-play-forward-actions {
        float: left !important;
        min-width: 109px !important;
        text-align: left !important;
    }

    .footagesearch-clip-cart-clipbin-actions {
        margin-left: 0px !important;
    }

    .heartPosition {
        width: 30px;
        float: left;
        margin-right: 60px;
        margin-top: 3px;
    }

    .inner_like.transitiable img {
        width: 18px;

    }

    #label_likes_grid {
        float: right;
        margin-right: -49px;
        margin-top: 3px;
    }
</style>