<div class="cliplog-sidebar-box sidebar-padding">

    <div class="control-group" data-type="metadata">
        <div class="cliplog_sidebar_header cliplog_sidebar_header-keyword">
            <h1>Keyword sets:</h1>
            <div class="header-Keyword-name">
                <span class="cliplog_Keyword_header"
                <?php
                if (empty($cliplog_keywords_sets)) {
                    echo '>Reset All Fields';
                } else {
                    foreach ($cliplog_keywords_sets as $keyword_set) {
                        $keywords_set_id = $this->session->userdata('applied_keywords_set_id');
                        if (empty($cliplog_keyword_set['id']) && empty($keywords_set_id) && $keywords_set_id != 'reset') {
                            echo '>Reset All Fields';
                            ?> <input type="hidden" id="keywords_set_id" name="keywords_set_id">
                            <?php
                            break;
                        } elseif (empty($cliplog_keyword_set['id']) && $keywords_set_id == 'reset') {
                            echo '>Reset All Fields';
                            ?> <input type="hidden" id="keywords_set_id" name="keywords_set_id">
                            <?php
                            break;
                        } elseif ($this->session->userdata('applied_keywords_set_id') == $keyword_set['id']) {
                            echo ' data-Keyword-id="' . $keyword_set['id'] . '">' . $keyword_set['name'];
                            ?> <input type="hidden" id="keywords_set_id" value="<?php echo $keyword_set['id'] ?>"
                                      name="keywords_set_id"> <?php
                            break;
                        } elseif ($cliplog_keyword_set['id'] == $keyword_set['id']) {
                            echo ' data-Keyword-id="' . $keyword_set['id'] . '">' . $keyword_set['name'];
                            ?> <input type="hidden" id="keywords_set_id" value="<?php echo $keyword_set['id'] ?>"
                                      name="keywords_set_id"> <?php
                            break;
                        }
                    }
                }
                ?>

                </span>
                <?php
                if ($check_reset == '1') {
                    ?>
                    <input type="hidden" id="keywords_set_id_reset"
                           value="<?php echo $this->session->userdata('applied_keywords_set_id'); ?>"
                           name="keywords_set_id_reset">

                <?php }
                ?>
                <input type="button" class="action save_metadata_template cliplog_save_metadata"
                       name="save_metadata_template" value="Save"/>
            </div>
        </div>


        <select name="applied_keywords_set_id" id="applied_keywords_set_id" class="cliplog_keywords_sets_list">
            <option value="reset">Reset All Fields</option>
            <?php foreach ($cliplog_keywords_sets as $keyword_set) {
                ?>
                <option
                    value="<?php echo $keyword_set['id']; ?>"<?php //if ( $cliplog_keyword_set[ 'id' ] == $keyword_set[ 'id' ] ) echo ' selected="selected"';                                                                                                                                     ?>><?php echo $keyword_set['name']; ?></option>
            <?php } ?>
        </select>
        <input type="hidden" id="apply_keywords_set" name="apply_keywords_set">
        <input type="button" class="action apply_metadata_template cliplog_update_template"
               name="apply_metadata_template" value="Apply" style="width: 40px;">
        <input type="button" class="action delete_metadata_template cliplog_delete_template" value="x"
               style="width: 18px;" data-type="metadata">
    </div>

    <div class="control-group" <?php if ($clip) echo ' style="display:none;"'; ?>>
        <label class="control-label" for="keywords_set_name">
            Overwrite fields:
        </label>
        <select name="overwrite_fields" id="overwrite_fields" class="cliplog_overwrite_fields">
            <option value="1">—Select—</option>
            <option value="2">Overwrite Existing Values</option>
            <option
                value="1" <?php //if($clip) echo ' selected="selected"';                                                                                                                                      ?> >
                Retain Existing Values
            </option>
        </select>
    </div>

    <div class="control-group">
        <label class="control-label" for="keywords_set_name">
            Save Keywords as New Keyword Set:
        </label>
        <input type="text" name="keywords_set_name" id="keywords_set_name" class="cliplog_keywords_set_name">
        <input type="submit" class="action cliplog_save_keywords_set" value="Create" name="save_keywords_set">
    </div>

</div>

<input type="hidden" value="0" id="checkingValData">
<script type="text/javascript">
    $(document).ready(function () {

        setTimeout(function () {
            function in_array(search, array) {
                for (i = 0; i < array.length; i++) {
                    if (array[i] == search) {
                        return true;
                    }
                }
                return false;
            }


//            var check = $('#keywords_set_id').val();
//            if (check) {
//                $.ajax({
//                    url: "ajax.php",
//                    data: {action: 'getSelectedKeywordsOrSet', setId: check},
//                    type: "POST",
//                    success: function(data) {
//                        var res = data.split("<br>");
//                        $.each(res, function(index, value) {
//                            $('#myTestDiv').append(value);
//                        })
//
//                        var sectionsArray = ['shot_type', 'subject_category', 'primary_subject', 'other_subject', 'appearance', 'actions', 'time', 'habitat', 'concept', 'location']
//
//
//                        $.each(sectionsArray, function(index, value) {
//
//                            var valuearr = [];
//
//                            $("#" + value + " .cliplog_selected_keywords_list_new .item-wrapper").each(function() {
//                                valuearr.push($(this).find('.getUserKeywordsForLogging').attr('datavalue-text'));
//                            })
//                            console.log(valuearr);
//                            // $("#myTestDiv ." + value).each(function() {
//                            var buttonBox = $(this);
//                            $("#myTestDiv ." + value + " .item-wrapper").each(function() {
//                                var CheckVal = $(this).find('input:hidden').first().attr('datavalue-text');
//                                var embeddedHtml = '<div class="item-wrapper ">' + $(this).html() + '</div>';
//
//                                if (in_array(CheckVal, valuearr)) {
//                                } else {
//                                    $("#" + value + " .cliplog_selected_keywords_list_new").append(embeddedHtml);
//                                    $(this).remove();
//                                }
//
//                            })
//                            //});
//                        })
//
//
//
//                    }
//                });
//            }


//            var checkRest = $('#keywords_set_id_reset').val();
//
//            if (checkRest == 'reset') {
//                $(".cliplog_selected_keywords_list_new .item-wrapper").each(function () {
//
//                    var DelId = $(this).find('.getUserKeywordsForLogging').attr('datadell-id');
//
//
//                    var currentVal = $('#deleteFeilddata').val();
//                    if (currentVal == '') {
//                        var InseertVal = DelId;
//                    } else {
//                        var InseertVal = currentVal + ',' + DelId;
//                    }
//
//                    $('#deleteFeilddata').val(InseertVal);
//
//                })
//                $('.cliplog_selected_keywords_list_new').html('');
//                $('input[name^="sections_values"]').each(function () {
//                    $('input:radio[value="' + $(this).val() + '"]').attr('checked', false);
//                });
//                $("select#countryMetaDataSelect option").removeAttr("selected");
//            }


        }, 2000);
    });
</script>