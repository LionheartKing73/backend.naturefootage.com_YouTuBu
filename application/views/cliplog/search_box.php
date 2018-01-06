<div class="search-list <?php echo ($search_flags) ? 'expanded' : 'collapsed'; ?>">
    <div class="search-box">
        <input type="text" id="searchin" name="wordsin" placeholder="Search within result"
               value="<?php echo isset($wordsin) ? $wordsin : ''; ?>" style="margin: 0;">
        <input type="submit" id="search_btn" name="filter" class="btn" value="Filter">
    </div>
    <?php if (isset($wordsin)) {
        $_SESSION['searchInImran'] = $wordsin;
    } else {
        $_SESSION['searchInImran'] = $wordsin;
    } ?>
    <div class="group">
        <label class="filter_label">Category</label>


        <?php //if ($_SESSION['cliplog_search_collection']) {
        ?>
        <?php //if (in_array('Nature Footage', $filtered_search_clips['collection_filter_name'])) { ?>
        <label class="checkbox">
            <input type="checkbox" value="Land"
                   name="collection[]" <?php if ($search_flags['collection'] && in_array('Land', $search_flags['collection'])) echo 'checked'; ?>>Land
        </label>
        <?php //} ?>

        <?php //if (in_array('Ocean Footage', $filtered_search_clips['collection_filter_name'])) { ?>
        <label class="checkbox">
            <input type="checkbox" value="Ocean"
                   name="collection[]" <?php if ($search_flags['collection'] && in_array('Ocean', $search_flags['collection'])) echo 'checked'; ?>>Ocean
        </label>
        <?php //} ?>

        <?php //if (in_array('Adventure Footage', $filtered_search_clips['collection_filter_name'])) { ?>
        <!--        <label class="checkbox">-->
        <!--            <input type="checkbox" value="Adventure Footage"-->
        <!--                   name="collection[]" -->
        <?php //if ($search_flags['collection'] && in_array('Adventure Footage', $search_flags['collection'])) echo 'checked'; ?><!-->
        <!--      Adventure  </label>-->
        <?php
        // }
        // }else {
        ?>
        <!--            <label class="checkbox">-->
        <!--                <input type="checkbox" value="Nature Footage" name="collection[]" >Land-->
        <!--            </label>-->
        <!---->
        <!--            <label class="checkbox">-->
        <!--                <input type="checkbox" value="Ocean Footage" name="collection[]" >Ocean-->
        <!--            </label>-->
        <!---->
        <!--            <label class="checkbox">-->
        <!--                <input type="checkbox" value="Adventure Footage" name="collection[]" >Adventure-->
        <!--            </label>-->


        <?php // } ?>


        <?php
        //        foreach ($collections as $collection) {
        //            $cid = $collection['id'];
        //            $ctitle = $collection['search_term'];
        //            $cname = $collection['name'];
        ?>
        <!--            <label class="checkbox">
        <?php if ($_SESSION['cliplog_search_collection']) { ?>
            <?php if (in_array($cname, $filtered_search_clips['collection_filter_name'])) { ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <input type="checkbox" value="<?php echo $cid; ?>" name="collection[]" <?php if ($search_flags['collection'] && in_array($cid, $search_flags['collection'])) echo 'checked'; ?>>
                <?php echo $ctitle; ?>
                <?php
        }
        } else {
            ?>
                                                                                                                                                                                                                                                                                                                <input type="checkbox" value="<?php echo $cid; ?>" name="collection[]" <?php if ($search_flags['collection'] && in_array($cid, $search_flags['collection'])) echo 'checked'; ?>>
            <?php echo $ctitle; ?>
        <?php } ?>
                    </label>-->
        <?php //}     ?>

    </div>

    <div class="group">
        <label class="filter_label">License type</label>
        <?php //if ($filtered_search_clips['license_rf'] == '1') { ?>
        <label class="checkbox"><input type="checkbox" value="1"
                                       name="license[]" <?php if ($search_flags['license'] && in_array(1, $search_flags['license'])) echo 'checked'; ?>>
            RF</label>
        <?php
        // }
        // if ($filtered_search_clips['license_rm'] == '1') {
        ?>
        <label class="checkbox"><input type="checkbox" value="2"
                                       name="license[]" <?php if ($search_flags['license'] && in_array(2, $search_flags['license'])) echo 'checked'; ?>>
            RM</label>
        <?php //} ?>
    </div>

    <div class="group">
        <label class="filter_label">Price Level</label>
        <?php
        //  if ($filtered_search_clips['budget'] == '1') {
        ?>
        <label class="checkbox"><input type="checkbox" value="1"
                                       name="price_level[]" <?php if ($search_flags['price_level'] && in_array(1, $search_flags['price_level'])) echo 'checked'; ?>>
            Budget</label>
        <?php
        // }
        // if ($filtered_search_clips['standard'] == '1') {
        ?>
        <label class="checkbox"><input type="checkbox" value="2"
                                       name="price_level[]" <?php if ($search_flags['price_level'] && in_array(2, $search_flags['price_level'])) echo 'checked'; ?>>
            Standard</label>
        <?php
        //   }
        //   if ($filtered_search_clips['premium'] == '1') {
        ?>
        <label class="checkbox"><input type="checkbox" value="3"
                                       name="price_level[]" <?php if ($search_flags['price_level'] && in_array(3, $search_flags['price_level'])) echo 'checked'; ?>>
            Premium</label>
        <?php
        //  }
        //  if ($filtered_search_clips['gold'] == '1') {
        ?>
        <label class="checkbox"><input type="checkbox" value="4"
                                       name="price_level[]" <?php if ($search_flags['price_level'] && in_array(4, $search_flags['price_level'])) echo 'checked'; ?>>
            Gold</label>
        <?php // } ?>
    </div>

    <div class="group">
        <label class="filter_label"><?php echo $format_category_filter['label']; ?></label>
        <?php foreach ($format_category_filter['options'] as $option) {
            ?>
            <label class="checkbox">
                <input type="checkbox"
                       value="<?php echo $option['value']; ?>"
                       name="format_category[]"
                    <?php if ($search_flags['format_category'] && in_array($option['value'], $search_flags['format_category'])) { ?>checked <?php } ?>
                >
                <?php echo $option['label']; ?>
            </label>
            <?php
        } ?>
    </div>

    <div class="group">
        <label class="filter_label">Online - Offline</label>
        <label class="checkbox"><input type="checkbox" value="1"
                                       name="active[]" <?php if ($search_flags['active'] && in_array(1, $search_flags['active'])) echo 'checked'; ?>>
            Online</label>
        <label class="checkbox"><input type="checkbox" value="0"
                                       name="active[]" <?php if ($search_flags['active'] && in_array(0, $search_flags['active'])) echo 'checked'; ?>>
            Offline</label>

    </div>

    <?php if ($is_admin) { ?>
        <div class="group">
            <label class="filter_label">Collection</label>


            <?php
            foreach ($collections as $collection) {
                $cid = $collection['id'];
                $ctitle = $collection['search_term'];
                $cname = $collection['name'];
                // print_r($filtered_search_clips['brand_filter_name']);
                ?>

                <label class="checkbox">
                    <?php
                    // if ($_SESSION['cliplog_search_brand']) {
                    ?>
                    <?php //if (in_array($cid, $filtered_search_clips['brand_filter_name'])) { ?>
                    <input type="checkbox" value="<?php echo $cid; ?>"
                           name="brand[]" <?php if ($search_flags['brand'] && in_array($cid, $search_flags['brand'])) echo 'checked'; ?>>
                    <?php echo $cname; ?>
                    <?php
                    //}
                    // } else {
                    ?>
                    <!--                        <input type="checkbox" value="--><?php //echo $cid; ?><!--"-->
                    <!--                               name="brand[]" -->
                    <?php //if ($search_flags['brand'] && in_array($cid, $search_flags['brand'])) echo 'checked'; ?><!-->
                    <!--                        --><?php //echo $cname; ?>
                    <?php //} ?>


                </label>
                <?php
            }

            //  if ($_SESSION['cliplog_search_brand']) {
            // if (in_array(0, $filtered_search_clips['brand_filter_name'])) {
            ?>
            <label class="checkbox"> <input type="checkbox" value="0"
                                            name="brand[]" <?php if ($search_flags['brand'] && in_array(0, $search_flags['brand'])) echo 'checked'; ?>>Custom
                Site Only</label>

            <?php
            //  }
            // } else {
            ?>
            <!--                <label class="checkbox"> <input type="checkbox" value="0" name="brand[]">Custom Site Only</label>-->
            <?php
            // }
            //        foreach ($brands as $brand_item) {
            //        $brand_id = $brand_item['id'];
            //        $brand_name = $brand_item['name'];
            ?>
            <!--        <label class="checkbox">
            <?php if ($_SESSION['cliplog_search_brand']) { ?>
                <?php if (in_array($brand_id, $filtered_search_clips['brand_filter_name'])) { ?>
                                                                                                                                                                                                                    <input type="checkbox" value="<?php echo $brand_id; ?>" name="brand[]" <?php if ($search_flags['brand'] && in_array($brand_id, $search_flags['brand'])) echo 'checked'; ?>> 
                    <?php echo $brand_name; ?>
                    <?php
            }
            } else {
                ?>
                                                                                                                                                                                                                <input type="checkbox" value="<?php echo $brand_id ?>" name="brand[]" <?php if ($search_flags['brand'] && in_array($brand_id, $search_flags['brand'])) echo 'checked'; ?>>
                <?php echo $brand_name; ?>
            <?php } ?>
                    </label>-->
            <?php //}       ?>
        </div>


        <div class="group">
            <label class="filter_label">Actions</label>

            <?php

            // if ($_SESSION['cliplog_adminAction_filter_name']) { ?>

            <?php //if (in_array(1, $filtered_search_clips['adminAction_filter_name'])) { ?>

            <label class="checkbox">
                <input type="checkbox" value="1"
                       name="actionAdmin[]" <?php if ($search_flags['actionAdmin'] && in_array(1, $search_flags['actionAdmin'])) echo 'checked'; ?>>
                <?php echo 'Added for deletion'; ?>
            </label>
            <?php
            //   }elseif (in_array(2, $filtered_search_clips['adminAction_filter_name'])) {
            ?>
            <label class="checkbox">
                <input type="checkbox" value="2"
                       name="actionAdmin[]" <?php if ($search_flags['actionAdmin'] && in_array(2, $search_flags['actionAdmin'])) echo 'checked'; ?>>
                <?php echo 'Added for quality review'; ?>
            </label>
            <?php
            // }elseif (in_array(3, $filtered_search_clips['adminAction_filter_name'])) {
            ?>
            <label class="checkbox">
                <input type="checkbox" value="3"
                       name="actionAdmin[]" <?php if ($search_flags['actionAdmin'] && in_array(3, $search_flags['actionAdmin'])) echo 'checked'; ?>>
                <?php echo 'Added for keywords review'; ?>
            </label>

            <?php
            //  }
            //}else {
            ?>
            <!--                <label class="checkbox"> -->
            <!--                    <input type="checkbox" value="1" name="actionAdmin[]" > -->
            <!--                    --><?php //echo 'Added for deletion'; ?>
            <!--                </label>-->
            <!--                <label class="checkbox"> -->
            <!--                    <input type="checkbox" value="2" name="actionAdmin[]" > -->
            <!--                    --><?php //echo 'Added for quality review'; ?>
            <!--                </label>-->
            <!--                <label class="checkbox"> -->
            <!--                    <input type="checkbox" value="3" name="actionAdmin[]" > -->
            <!--                    --><?php //echo 'Added for keywords review'; ?>
            <!--                </label>-->
            <?php
            // }
            ?>
        </div>
    <?php } ?>


    <div class="group">
        <label class="filter_label">Date Added</label>


        <select name="creation_date" id="select_name" class="select">
            <option value="">Select Date Added</option>
            <option
                value="past_week" <?php echo ($_SESSION['cliplog_creation_date'][0] == 'past_week') ? 'selected="selected"' : ''; ?>>
                Past Week
            </option>
            <option
                value="past_month" <?php echo ($_SESSION['cliplog_creation_date'][0] == 'past_month') ? 'selected="selected"' : ''; ?>>
                Past Month
            </option>
            <option
                value="past_year" <?php echo ($_SESSION['cliplog_creation_date'][0] == 'past_year') ? 'selected="selected"' : ''; ?>>
                Past Year
            </option>
            <option
                value="over_one_year" <?php echo ($_SESSION['cliplog_creation_date'][0] == 'over_one_year') ? 'selected="selected"' : ''; ?>>
                Over One Year
            </option>
        </select>

        </label>
    </div>


    <div class="group">
        <label class="filter_label">Duration</label>

        <label class="">
            <select name="duration">
                <option value="">Select Duration</option>
                <option
                    value="1to10" <?php echo ($_SESSION['cliplog_duration_filter'][0] == '1to10') ? 'selected="selected"' : ''; ?>>
                    &gt;10 Seconds
                </option>
                <option
                    value="1to20" <?php echo ($_SESSION['cliplog_duration_filter'][0] == '1to20') ? 'selected="selected"' : ''; ?>>
                    &gt;20 Seconds
                </option>
                <option
                    value="1to30" <?php echo ($_SESSION['cliplog_duration_filter'][0] == '1to30') ? 'selected="selected"' : ''; ?>>
                    &gt;30 Seconds
                </option>
                <option
                    value="1to60" <?php echo ($_SESSION['cliplog_duration_filter'][0] == '1to40') ? 'selected="selected"' : ''; ?>>
                    &gt;60 Seconds
                </option>
                <option
                    value="61to" <?php echo ($_SESSION['cliplog_duration_filter'][0] == '61to') ? 'selected="selected"' : ''; ?>>
                    61+ Seconds
                </option>
            </select>
        </label>
    </div>


    <div class="controls">
        <!--input type="button" class="btn cancel cliplog-search-form-cancel" value="Cancel"-->
        <input type="submit" id="search_btn" name="filter" class="btn" value="Filter">
    </div>
</div>

<script type="text/javascript">
    function SearchByKey(value) {
        var test = $('#searchin').val(value);
        //var form = $('.cliplog-search-form');
        $('#search_btn').click();
    }


    $(document).ready(function () {
        $('.cliplog-search-form-cancel').click(function () {
            var form = $('.cliplog-search-form');
            form.find('input[type="checkbox"]').each(function () {
                $(this).removeAttr('checked');
            });
            form.find('input[type="text"]').attr('value', '');
            form.submit();
        });
    });

</script>