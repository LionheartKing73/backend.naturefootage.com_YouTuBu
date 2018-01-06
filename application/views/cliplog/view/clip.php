<?php
foreach ($clips as $key => $clip) {
//    echo "<pre>";
//    print_r($clip);
//    echo "</pre>";
    ?>
    <?php if ($list_view == 'list') { ?>
        <tr class="tdata1 draggable-clip">
        <td>
    <?php } ?>
    <div class="footagesearch-clip draggable-clip" id="footagesearch-clip-<?php echo $clip['id']; ?>"
         data-clip-id="<?php echo $clip['id']; ?>">
        <div class="footagesearch-clip-wrapper">
            <div class="footagesearch-clip-top heightCustom">
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

                <? if ($active_clipbin && in_array($clip['id'], $active_clipbin['items_ids'])) { ?>
                    <a href="/en/cliplog/view.html?backend_clipbin=<?php echo $active_clipbin['id']; ?>">
                        <div class="<? echo $active_clipbin['type']; ?> green-icon"></div>
                    </a>
                <? } else { ?>
                    <div class="green-icon" style="display:none;"></div>
                <? } ?>

                <?php /* if ( $clip[ 'license' ] ) { ?>
                          <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip[ 'license' ]; ?>">
                          <img src="<?php echo '/data/img/admin/cliplog/view/license-' . $clip[ 'license' ] . '.gif'; ?>">
                          </div>
                          <?php } */ ?>
                <?php if ($clip['license'] == 1) { ?>
                    <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">RF
                    </div>
                <?php } elseif ($clip['license'] == 2) { ?>
                    <?php if ($clip['price_level'] == 4) { ?>
                        <div class="footagesearch-clip-license footagesearch-license-gold">GD</div>
                    <?php } elseif ($clip['price_level'] == 3) { ?>
                        <div class="footagesearch-clip-license footagesearch-license-premium">PR</div>
                    <?php } else { ?>
                        <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                            RM
                        </div>
                    <?php } ?>
                <?php } ?>
                <?php if ($clip['duration']) { ?>
                    <div class="footagesearch-clip-duration"><?php echo round($clip['duration']); ?> sec</div>
                <?php } ?>


                <div class="clear"></div>
                <!--                        <div class="footagesearch-clip-code likes_count"><?php echo $clip['total_likes'] . ' Likes(s)'; ?></div>-->
            </div>


            <div class="check transitiable toCustom"></div>
            <div class="footagesearch-clip-inner">


                <div class="info transitiable">
                    <a id="footagesearch-clip-offset-1" href="<?php echo $lang . '/cliplog/clip/' . $clip['id']; ?>"
                       data-bin-id="<?php echo (!empty($_REQUEST['backend_clipbin'])) ? $_REQUEST['backend_clipbin'] : $active_clipbin['id']; ?>">
                        <img src="/data/img/cliplog/info.png" alt="" class="footagesearch-clip-info-icon" title="">
                    </a>
                </div>

                <div class="footagesearch-clip-thumb"
                     style="overflow: hidden; width: 216px; height: 120px;background: #000;">
                    <input type="hidden" value="<?php echo json_encode($clip); ?>">
                    <img src="<?php echo $clip['thumb']; ?>" style="max-width: 216px; height: 120px;">
                </div>


                <div class="footagesearch-clip-action transitiable">
                    <div class="footagesearch-clip-play-forward-actions">
                        <?php
                        $createdate = explode('-', $clip['creation_date']);
                        //print_r($createdate);

                        $newClipNum = 'BC' . substr($createdate[0], 2) . $createdate[1] . substr($createdate[2], 0, 2) . '_' . $clip['id'];
                        ?>

                        <?php
                        if (!empty($clip['price_level'])) {
                            if ($clip['price_level'] == 1) {
                                $priceLevelDisplay = 'Budget';
                            }
                            if ($clip['price_level'] == 2) {
                                $priceLevelDisplay = 'Standard';
                            }
                            if ($clip['price_level'] == 3) {
                                $priceLevelDisplay = 'Premium';
                            }
                            if ($clip['price_level'] == 4) {
                                $priceLevelDisplay = 'Gold';
                            }
                        }


                        $clipDivileryMethodData = '';

                        if ($clip['delivery_methods']) {
                            if (count($clip['delivery_methods']) > 1) {

                                if (!empty($clip['delivery_methods'])) {

                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    foreach ($clip['delivery_methods'] as $key => $method) {
                                        if (isset($method['formats'])) {
                                            $clipDivileryMethodData .= $method['title'] . " <br> ";
                                        }
                                    }
                                    ?>


                                    <?php
                                    if (!empty($clip['delivery_methods'][$selected_method]['formats'])) {

                                        foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) {
                                            $clipDivileryMethodData .= $format['description'] . " <br> ";
//                                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
//                                                                echo ' ($' . $format['price'] . ')';
                                        }
                                    }
                                }
                                ?>
                                </select>
                                <?php
                            } else {
                                if (!empty($clip['delivery_methods'])) {
                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    ?>
                                    <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]"
                                           value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">

                                    <?php
                                    if (!empty($clip['delivery_methods'][$selected_method]['formats'])) {
                                        foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) {
                                            $clipDivileryMethodData .= $format['description'] . " <br> ";
                                        }
                                    }
                                }
                                ?>

                                <?php
                            }
                        }
                        ?>
                        <!--                        , 'description' => $clip['description']-->
                        <!--                        'location' => $clip['location'],-->
                        <img
                            id="play_<?php echo $clip['id']; ?>"
                            src="/data/img/admin/cliplog/view/play_icon.png"
                            alt=""
                            class="footagesearch-clip-play-btn"
                            data-clip='<?php echo json_encode(array('id' => $clip['id'], 'title' => $clip['title'], 'preview' => $clip['preview'], 'motion_thumb' => $clip['motion_thumb'], 'new_id' => $newClipNum, 'source_format' => $clip['source_format'] . ' ' . $clip['source_frame_size'] . ' ' . $clip['source_frame_rate'], 'country' => $clip['country'], 'dilivery_options' => $clipDivileryMethodData, 'price_level' => $priceLevelDisplay, 'license_restrictions' => $clip['license_restrictions'])); ?>'
                        >
                        <img
                            id="pause-<?php echo $clip['id']; ?>"
                            src="/data/img/admin/cliplog/view/pause_icon.png"
                            alt=""
                            class="footagesearch-clip-pause-btn"
                            style="display: none;"
                        >
                        <img
                            id="forward-<?php echo $clip['id']; ?>"
                            src="/data/img/admin/cliplog/view/forward_icon.png"
                            alt=""
                            class="footagesearch-clip-forward-btn"
                        >
                        <img
                            id="forward3x-<?php echo $clip['id']; ?>"
                            src="/data/img/admin/cliplog/view/forward3x_icon.png"
                            alt="" class="footagesearch-clip-forward3x-btn"
                        >

                        <input type="hidden" name="user_login_id" value="<?php echo $this->session->userdata('uid'); ?>"
                               id="user_login_id">
                        <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                            <?php
                            if (!empty($clip['rating_result'])) {
                                foreach ($clip['rating_result'] as $rating) {
                                    $rating_id = $rating['id'];
                                }
                            }
                            ?>
                            <?php
                            if ($clip['current_user_like'] > 0) {
                                ?>

                                <div class="inner_like transitiable">

                                    <img
                                        onClick="deleteLikes('<?php echo $rating_id ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                        src="/data/img/cliplog/like-fill.png"></div>
                            <?php } else {
                                ?>

                                <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                        onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $rating_id ?>');"><img
                                            src="/data/img/cliplog/like-icon.png"></a>
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
                        <? if (($active_clipbin && in_array($clip['id'], $active_clipbin['items_ids'])) || !empty($_REQUEST['backend_clipbin'])) { ?>
                            <a href="" class="clipbin-delete-item <? echo $active_clipbin['type']; ?>"
                               data-clip-id="<? echo $clip['id']; ?>"
                               data-bin-id="<?php echo (!empty($_REQUEST['backend_clipbin'])) ? $_REQUEST['backend_clipbin'] : $active_clipbin['id']; ?>">
                                <!--<img src="/data/img/admin/cliplog/view/bin_remove.png">-->
                            </a>
                            <a href="" class="clipbin-add-item <? echo $active_clipbin['type']; ?>"
                               data-clip-id="<? echo $clip['id']; ?>"
                               data-bin-id="<?php echo (!empty($_REQUEST['backend_clipbin'])) ? $_REQUEST['backend_clipbin'] : $active_clipbin['id']; ?>"
                               style="display:none;">
                                <!--<img src="/data/img/admin/cliplog/view/bin_add.png">-->
                            </a>
                        <? } else { ?>
                            <a class="clipbin-delete-item <? echo $active_clipbin['type'];//$current_clipbin['type']; ?>"
                               data-clip-id="<? echo $clip['id']; ?>" data-bin-id="<?php echo $active_clipbin['id']; ?>"
                               style="display:none;">
                                <!--<img src="/data/img/admin/cliplog/view/bin_remove.png">-->
                            </a>
                            <a class="clipbin-add-item <? echo $active_clipbin['type'];//$current_clipbin['type']; ?>"
                               data-clip-id="<? echo $clip['id']; ?>"
                               data-bin-id="<?php echo $active_clipbin['id']; ?>">
                                <!--<img src="/data/img/admin/cliplog/view/bin_add.png">-->
                            </a>
                        <? } ?>
                        <a href="<?php echo $lang . '/cliplog/edit/' . $clip['id']; ?>">
                            <img src="/data/img/admin/cliplog/view/edit.png">
                        </a>
                        <a target="_blank" href="<?php echo $clip['download']; ?>">
                            <img src="/data/img/admin/cliplog/view/download_icon_2.png">
                        </a>
                    </div>
                    <div class="clear"></div>
                </div>

            </div>
        </div>

        <input type="hidden" name="selected_clips[<?php echo $clip['id']; ?>]" value="0"
               class="footagesearch-clip-input">
        <input type="checkbox" value="<?php echo $clip['id']; ?>" name="id[]" style="display: none;">
    </div>

    <?php if ($list_view == 'list') { ?>
        </td>
        <!--            <td>
            <?php //echo esc($clip['description'])        ?>
            </td>-->
        <td>
            <?php echo esc($clip['code']) ?>
        </td>

        <td style="max-width:50%; width:35%">
            <div style="float:left; margin-right: 10px;"><b>Description:</b></div>
            <?php
            if (!empty($clip['description'])) {
                echo htmlspecialchars_decode($clip['description']);
            }
            ?>
            <br>
            <?php
            $brand_id = $clip['brand'];
            if ($brand_id == 0) {
                $brand_name = 'Custom Site Only';
            } else {

                $query = $this->db->query("select name from lib_brands where id ='" . $brand_id . "'")->result_array();
                $brand_name = $query[0]['name'];
            }
            ?>
            <div style="float:left; margin-right: 10px;"><b>Collection:</b></div>
            <?php
            if (!empty($clip['brand']) || $clip['brand']==0) {
                echo esc($brand_name);
            }
            ?>
            <br>

            <div style="float:left; margin-right: 10px;"><b>Category:</b></div>
            <?php


            foreach ($clip['keywords_types'] as $keyword) {
                $checkVar--;
                if ($checkVar == 0) {
                    $delimeter = ', ';
                } else {
                    $delimeter = ' ';
                }

                if ($keyword['section_id'] == 'category') {
                    echo $keyword['keyword'] . $delimeter;
                }
            }
            ?>
            <br>

            <div style="float:left; margin-right: 10px;"><b>License:</b></div>
            <?php
            if (!empty($clip['license'])) {
                if ($clip['license'] == 1) {
                    echo 'RF';
                }
                if ($clip['license'] == 2) {
                    echo 'RM';
                }
            }
            ?>
            <br>

            <div style="float:left; margin-right: 10px;"><b>Price Level:</b></div>
            <?php
            if (!empty($clip['price_level'])) {
                if ($clip['price_level'] == 1) {
                    echo 'Budget';
                }
                if ($clip['price_level'] == 2) {
                    echo 'Standard';
                }
                if ($clip['price_level'] == 3) {
                    echo 'Premium';
                }
                if ($clip['price_level'] == 4) {
                    echo 'Gold';
                }
            }
            ?>
            <br>
            <div style="float:left; margin-right: 10px;"><b>File Formats:</b></div>
            <?php
            if (!empty($clip['digital_file_frame_size']) || !empty($clip['digital_file_format']) || !empty($clip['digital_file_frame_rate'])) {
                echo esc($clip['digital_file_format']) . " " . esc($clip['digital_file_frame_size']) . " " . esc($clip['digital_file_frame_rate']);
            }
            ?>
            <br>
            <div style="float:left; margin-right: 10px;"><b>Keywords:</b></div>
            <?php
            if (!empty($clip['keywords_types'])) {
                foreach ($clip['keywords_types'] as $clip_key) {
                    if (!empty($clip_key['section_id'])) {
                        ?>
                        <div class="clip_keywords">
                            <?php echo $clip_key['keyword']; ?>
                        </div>

                        <?php
                    }
                }
            }
            if (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00') {
                ?>
                <div class="clip_keywords">
                    <?php echo date('d.m.Y', strtotime($clip['film_date'])); ?>
                </div>

                <?php
            }

            if (empty($clip['keywords_types']) && $clip['film_date'] == '0000-00-00') {
                echo "No keywords found";
            }


            if (!empty($clip['country'])) {
                ?>
                <div class="clip_keywords">
                    <?php
                    echo esc($clip['country']);
                    ?>
                </div>
            <?php } ?>

        </td>

        <td>
            <?php echo esc($clip['original_filename']) ?>
        </td>
        <?php if ($is_admin) { ?>
            <td>
                <?php echo $clip['fname'] . ' ' . $clip['lname'] ?>
            </td>
        <?php } ?>
        <td>
            <?php
            if ($clip['active'] == 1)
                echo 'published';
            else
                echo 'unpublished';
            ?>
        </td>
        <td>
            Preview: <?php echo $clip['preview'] ? 'Ready' : 'Not ready'; ?><br>
            Thumbnail: <?php echo $clip['thumb'] ? 'Ready' : 'Not ready'; ?><br>
            Motion thumbnail: <?php echo $clip['motion_thumb'] ? 'Ready' : 'Not ready'; ?><br>
        </td>
        <td>
            <?php echo $clip['ctime'] ?>
        </td>
        </tr>
    <?php } ?>

<?php } ?>
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
        jQuery('.demo-table #tutorial-' + clipId + ' li').each(function (index) {
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
            beforeSend: function () {
                jQuery('#tutorial-' + clipId + ' .btn-likes').html("<img src='<?php echo site_url(); ?>/images/icons/LoaderIcon.gif' />");
            },
            success: function (data) {
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
    .footagesearch-clip-play-forward-actions {
        float: left !important;
        min-width: 109px !important;
        text-align: left !important;
    }

    .footagesearch-clip-cart-clipbin-actions {
        margin-left: 0px !important;
    }

    .heartPosition {
        width: 18px;
        float: right;
        margin-top: 2px
    }

    .heartPosition img {
        width: 18px;
    }

    .clip_keywords {
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