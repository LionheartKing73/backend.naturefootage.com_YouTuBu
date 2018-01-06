<?php if ($clip) {
    ?>

    <div id="footagesearch-clip-preview-dialog"></div>
    <div class="cliplog-edit-clip-box">

        <div class="footagesearch-clip draggable-clip" id="footagesearch-clip-<?php echo $clip['id']; ?>" data-clip-id="<?php echo $clip['id']; ?>">
            <div class="footagesearch-clip-wrapper">
                <div class="footagesearch-clip-top">

                    <?php
                    switch ($clip['active']) {
                        case "1":
                            echo '<span class="green-point" title="Published"></span>';
                            break;
                        case "0":
                            echo '<span class="red-point" title="Unpublished"></span>';
                            break;
                        case "2":
                            echo '<span class="white-point" title="Archived"></span>';
                            break;
                    }
                    ?>
                    <div class="footagesearch-clip-code"><?php echo $clip['code'] ?></div>
                    <?php /* if($clip['license']){ ?>
                      <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                      <img src="<?php echo '/data/img/admin/cliplog/view/license-' . $clip['license'] . '.gif'; ?>">
                      </div>
                      <?php } */ ?>
                    <?php if ($clip['license'] == 1) { ?>
                        <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">RF</div>
                    <?php } elseif ($clip['license'] == 2) { ?>
                        <?php if ($clip['price_level'] == 4) { ?>
                            <div class="footagesearch-clip-license footagesearch-license-gold">GD</div>
                        <?php } elseif ($clip['price_level'] == 3) { ?>
                            <div class="footagesearch-clip-license footagesearch-license-premium">PR</div>
                        <?php } else { ?>
                            <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">RM</div>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($clip['duration']) { ?>
                        <div class="footagesearch-clip-duration"><?php echo round($clip['duration']); ?> sec</div>
                    <?php } ?>
                    <div class="clear"></div>
                </div>
                <div class="footagesearch-clip-inner">
                    <div class="info transitiable">
                        <a id="footagesearch-clip-offset-1" href="<?php echo $lang . '/cliplog/clip/' . $clip['id']; ?>">
                            <img src="/data/img/cliplog/info.png" alt="" class="footagesearch-clip-info-icon" title="">
                        </a>
                    </div>
                    <div class="footagesearch-clip-thumb" style="overflow: hidden; width: 216px; height: 120px;">
                        <input type="hidden" value='<?php echo json_encode($clip); ?>'>
                        <img src="<?php echo $clip['thumb']; ?>" style="width: 216px; height: 120px;">
                    </div>
<!--                    , 'description' => $clip['description']-->
                    <div class="footagesearch-clip-action transitiable">
                        <div class="footagesearch-clip-play-forward-actions">
                            <img
                                id="play_<?php echo $clip['id']; ?>"
                                src="/data/img/admin/cliplog/view/play_icon.png"
                                class="footagesearch-clip-play-btn"
                                data-clip='<?php echo json_encode(array('id' => $clip['id'], 'title' => $clip['title'], 'preview' => $clip['preview'], 'motion_thumb' => $clip['motion_thumb'])); ?>'
                                >
                            <img
                                id="pause-<?php echo $clip['id']; ?>"
                                src="/data/img/admin/cliplog/view/pause_icon.png"
                                class="footagesearch-clip-pause-btn"
                                style="display: none;"
                                >
                            <img
                                id="forward-<?php echo $clip['id']; ?>"
                                src="/data/img/admin/cliplog/view/forward_icon.png"
                                class="footagesearch-clip-forward-btn"
                                >
                            <img
                                id="forward3x-<?php echo $clip['id']; ?>"
                                src="/data/img/admin/cliplog/view/forward3x_icon.png"
                                class="footagesearch-clip-forward3x-btn"
                                >

                            <input type="hidden" name="user_login_id" value="<?php echo $this->session->userdata('uid'); ?>" id="user_login_id">
                            <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                                <?php
                                if (!empty($rating)) {
                                    foreach ($rating_result as $rating) {
                                        $rating_id = $rating['id'];
                                    }
                                }
                                ?>
                                <?php
                                if ($current_user_like > 0) {
                                    ?>

                                    <div class="inner_like transitiable">

                                        <img onClick="deleteLikes('<?php echo $rating_id ?>', 'unlike', '<?php echo $clip['id'] ?>')" src="/data/img/cliplog/like-fill.png"></div>
                                <?php } else {
                                    ?>

                                    <div class="inner_like transitiable">  <a href="javascript:void(0)" onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $rating_id ?>');"><img src="/data/img/cliplog/like-icon.png"></a>
                                    </div> <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="footagesearch-clip-cart-clipbin-actions">
                            <a href="en/cliplog/index/thumbgallery/<?php echo $clip['id']; ?>">
                                <img
                                    src="/data/img/admin/cliplog/view/create_thumb_icon.png"
                                    >
                            </a>
                            <a target="_blank" href="<?php echo $clip['download']; ?>">
                                <img src="/data/img/admin/cliplog/view/download_icon_2.png" alt="">
                            </a>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="selected_clips[<?php echo $clip['id']; ?>]" value="0" class="footagesearch-clip-input">
            <input type="checkbox" value="<?php echo $clip['id']; ?>" name="id[]" style="display: none;">

        </div>

        <?php if ($clips) { ?>
            <div class="prev-clip"></div>
            <div class="next-clip"></div>
        <?php } ?>

    </div>


    <script type="text/javascript">
        function addLikes(id, action, deleteId) {
            $('.demo-table #tutorial-' + id + ' li').each(function(index) {
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
                beforeSend: function() {
                    $('#tutorial-' + id + ' .btn-likes').html("<img src='<?php echo site_url(); ?>/images/icons/LoaderIcon.gif' />");
                },
                success: function(data) {

                    var result = data.split(',');
                    var response_id = result[1];
                    //var likes = parseInt(jQuery('#likes-'+id).val());
                    var likes = result[0];
                    switch (action) {
                        case "like":
                            $('#tutorial-' + id + ' .btn-likes').html('<img src="/data/img/cliplog/like-fill.png" />');
                            $('#tutorial-' + id + ' .inner_like').html('<img style="cursor:pointer;" onClick="deleteLikes(\'' + response_id + '\',\'unlike\',\'' + id + '\')" src="/data/img/cliplog/like-fill.png" />');
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
            jQuery('.demo-table #tutorial-' + clipId + ' li').each(function(index) {
                jQuery(this).addClass('selected');
                jQuery('#tutorial-' + clipId + ' #rating').val((index + 1));
                if (index == jQuery('.demo-table #tutorial-' + clipId + ' li').index(obj)) {
                    return false;
                }
            });
            var user_login_id = jQuery('#user_login_id').val();
            var url = 'ajax.php';
            jQuery.ajax({
                url: url,
                data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id + '&clip_id=' + clipId,
                type: "POST",
                beforeSend: function() {
                    jQuery('#tutorial-' + clipId + ' .btn-likes').html("<img src='<?php echo site_url(); ?>/images/icons/LoaderIcon.gif' />");
                },
                success: function(data) {
                    var result = data.split(',');
                    //var likes = parseInt(jQuery('#likes-'+id).val());
                    var likes = result[0];
                    switch (action) {
                        case "like":
                            jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="/data/img/cliplog/like-fill.png" />');
                            jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src=/data/img/cliplog/like-fill.png" />');
                            //likes = likes+1;
                            break;
                        case "unlike":
                            jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="/data/img/cliplog/like-icon.png" />');

                            //likes = likes - 1;
                            break;
                    }
                    jQuery('#likes-' + clipId).val(likes);
                    if (likes > 0) {
                        jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                    } else {
                        jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                    }
                }
            });
        }
    </script> 
    <style>

        /*.toCustom{
            top: 50px !important;
        }*/
        .footagesearch-clip-play-forward-actions{
            float: left !important;
            min-width: 109px !important;
            text-align: left !important;
        }
        .footagesearch-clip-cart-clipbin-actions{
            margin-left: 0px !important;
        }
        .heartPosition{
            width: 18px;
            float: right;
            margin-top: 2px
        }
        .heartPosition img{
            width: 18px;
        }
        .clip_keywords{
            border: 1px solid grey;
            border-radius: 5px;
            float: left;
            font-size: 10px;
            letter-spacing: 0.5pt;
            margin-bottom: 3px;
            margin-left: 10px;
            padding: 2px 6px;
        }
    </style>

<?php } elseif ($clips) {
    ?>

    <div id="footagesearch-clip-more-one">
        <p>Modifying <strong><?php echo count($clips); ?></strong> Clips</p>
    </div>

<?php } ?>