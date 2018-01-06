<?php
/**
 * @var array $keywords
 */
$sectionList = array(
    'location' => 'Location',
    'shot_type' => 'Shot Type',
    'subject_category' => 'Subject Category',
    'primary_subject' => 'Primary Subject',
    'other_subject' => 'Other Subject',
    'appearance' => 'Appearance',
    'actions' => 'Actions',
    'time' => 'Time',
    'habitat' => 'Habitat',
    'concept' => 'Concept'
);
?>

<?php
//echo '<pre>';
//print_r($keywords);
//echo '</pre>';
$allKeywordList = $keywords;
$overwrite = ($clip || ($_REQUEST['applied_keywords_set_id'] === '' && $_REQUEST['overwrite_all'])) ? 1 : 0;
foreach ($sectionList as $sectionName => $sectionTitle) {
    # Значения для каждой секции
    $sectionHint = (isset($hints[$sectionName])) ? $hints[$sectionName] : FALSE;
    $sectionVisibleStyle = ( /* isset( $sectionName ) */
        strripos($loggingData['keywords_sections_visible'], $sectionName) !== false) ? '' : 'display: none;'; //'visibility: hidden;';
    $sectionVisibleChecked = ( /* isset( $sectionName ) */
        strripos($loggingData['keywords_sections_visible'], $sectionName) !== false) ? 'checked' : '';
    //$sectionSelectedKeywordList = ( isset( $keywords_by_sections[ $sectionName ] ) && $_REQUEST['applied_keywords_set_id']!=='' ) ? $keywords_by_sections[ $sectionName ] : array ();
    $sectionKeywordList = (isset($sectionsSelectedKeywords[$sectionName])) ? $sectionsSelectedKeywords[$sectionName] : array();
    $sectionSelectedKeywordList = (isset($sectionsSelectedKeywords[$sectionName])) ? $sectionsSelectedKeywords[$sectionName] : array();
    if ($_REQUEST['applied_keywords_set_id'] === '' && $_REQUEST['apply_template'] === '')
        $sectionSelectedKeywordList = array();
    //$sectionSelectedKeywordList = ( isset( $sectionsSelectedKeywords[ $sectionName ] ) ) ? $sectionsSelectedKeywords[ $sectionName ] : array ();
    //$sectionKeywordList = ( isset( $keywords_by_sections[ $sectionName ] ) ) ? $keywords_by_sections[ $sectionName ] : array ();*/
    ?>
    <?php //echo '<pre><h2>SELECT in '.$sectionName.' </h2>'; var_dump($sectionSelectedKeywordList); echo '<h2>ALL in '.$sectionName.'</h2>'; var_dump($sectionKeywordList);?>
    <tr class="control-group cliplog_section" id="<?php echo $sectionName; ?>"
        style="<?php echo $sectionVisibleStyle; ?>" data-formkeeper="keywords">
        <td>
            <table
                class="new-section <?php if (isset($keywords_sections_hide_lists[$sectionName])) { ?>expanded<?php } else { ?>collapsed<?php } ?>">
                <tr>
                    <td>
                        <span class="field_label" style="margin-left: 10px;">
                            <?php echo $sectionTitle . ' : ';
                            if ($sectionTitle == 'Location') {
                                echo '<span class="require"></span>';
                            } ?>
                            <input type="hidden" id="section_search_name" name="section_search_name"
                                   value="<?php echo $sectionName ?>">
                            <?php if ($sectionHint) { ?>
                                <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                     title="<?php echo $sectionHint; ?>">
                            <?php } ?>
                            <div class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                <input type="checkbox" name="overwrite[<?php echo $sectionName; ?>]"
                                       value="<?php echo $sectionName; ?>" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                overwrite existing values

                            </div>
                        </span>
                    </td>
                    <td>
                        <div class="content_search">
                            <div class="input_container">
                                <input type="text" value="" class="cliplog_keyword_input"
                                       onblur="hideResult('<?php echo $sectionName ?>')"
                                       onkeyup="autocomplet('<?php echo $sectionName; ?>')">

                                <div class="button-cont">
                                    <button class="button_add_green cliplog_add_keyword_to_list_new">Add</button>
                                </div>

                                <ul class="result_keyword"></ul>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (isset($keywords_sections_hide_lists[$sectionName])) { ?>
                            <a class="cliplog-expander-button expanded" data-formkeeper="logging">Hide list</a>
                        <?php } else { ?>
                            <a class="cliplog-expander-button collapsed" data-formkeeper="logging">Show list</a>
                        <?php } ?>
                    </td>
                    <td><?php //print_r($clips_Keywords);                   ?>
                        <a class="cliplog-manage-button" data-id="<?php echo $sectionName; ?>">Manage all Keywords</a>
                        <!--                        <a  data-id="<?php echo $sectionName; ?>" class='inline' href="#inline_content<?php echo $sectionName; ?>"> Manage all Keywords</a>-->
                    </td>
                    <td class="section-switch-cont">
                        <div class="switch-cont">
                            <div class="switch" data-animated="false" data-on-label="" data-off-label="">
                                <input type="checkbox" name="sections[]" id="<?php echo $sectionTitle; ?>"
                                       value="<?php echo $sectionName; ?>" <?php echo $sectionVisibleChecked; ?>
                                       data-formkeeper="logging"/>
                            </div>
                        </div>
                    </td>
                </tr>


                <tr>
                    <td colspan="5" class="top">
                        <div class="cliplog_keywords_list_new ">
                            <?php //if ($sectionKeywordList) {  ?>
                            <?php //foreach ($sectionKeywordList as $keyword) { ?>
                            <!--                                    <label class="checkbox " <?php echo ($keyword['hidden'] || !$keyword['isActive'] || @in_array($keyword['id'], $keywords)) ? 'style="opacity:0; position:absolute; left:9999px;"' : ''; ?> >
                                        <input type="checkbox" name="keyword-<?php echo $keyword['id']; ?>" value="<?php echo $keyword['keyword']; ?>" class="cliplog_keyword_checkbox" ><?php echo $keyword['keyword']; ?>
                                    </label>-->
                            <?php //}  ?>
                            <?php //} ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="middle">
                        <a class="cliplog_show_all_keywords_new">Show All</a>
                    </td>
                </tr>


                <tr>
                    <td colspan="5" class="bottom">
                        <div class="cliplog_selected_keywords_list_new">


                            <?php
//                            echo '<pre>';
//                            print_r($clips_Keywords);
//                            echo '</pre>';
                            if ($clips_Keywords) {
                                foreach ($clips_Keywords as $keyword) {
//                                    echo '<pre>';
//                                    print_r($clips_Keywords);
//                                    echo "</pre>";
                                    if ($sectionName == $keyword['section_id']) {
                                        ?>
                                        <div class="item-wrapper">
                                            <a class="item-cross"></a>

                                            <div class="item">
                                                <input type="hidden" checked="checked" class="getUserKeywordsForLogging"
                                                       value="<?php echo $keyword['id']; ?>"
                                                       name="userKeywordsInsert[<?php echo $keyword['id']; ?>]"
                                                       datadell-id="<?php echo $keyword['id']; ?>"
                                                       datalb-k-id="<?php echo $keyword['lk_id']; ?>"
                                                       datavalue-text="<?php echo $keyword['keyword']; ?>">
                                                <input type="hidden" id="section_search_name" checked="checked"
                                                       value="<?php echo $keyword['section_id']; ?>"
                                                       name="section[<?php echo $keyword['section_id']; ?>]"
                                                       datadell-id="<?php echo $keyword['id']; ?>">
                                                <input type="hidden" id="section_clip_id" name="section_search_id"
                                                       value="<?php echo $clip['id']; ?>">
                                                <?php echo $keyword['keyword']; ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                            ?>


                            <?php //if ($allKeywordList && $sectionSelectedKeywordList) {  ?>
                            <?php //foreach ($sectionSelectedKeywordList as $keyword) { ?>
                            <?php //if (in_array($keyword['id'], $keywords)) { ?>
                            <!--                                        <div class="item-wrapper">
                                                                        <a class="item-cross"></a>
                                                                        <div class="item">
                                                                            <input type="hidden"  checked="checked" value="<?php echo $keyword['id']; ?>" name="keywords[<?php echo $keyword['id']; ?>]">
                                                                            <input type="hidden"  checked="checked" value="<?php echo $keyword['section']; ?>" name="section[<?php echo $keyword['section']; ?>]">
                            <?php echo $keyword['keyword']; ?>
                                                                        </div>
                                                                    </div>-->
                            <?php //}  ?>
                            <?php //} ?>
                            <?php //} ?>


                        </div>
                    </td>
                </tr>


                <tr>
                    <td colspan="5" class="bottom">
                        <div class="cliplog_selected_keywords_list_new_users<?php echo $sectionName; ?>"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                </tr>
            </table>
        </td>
    </tr>


    <?php if ($sectionTitle == 'Location') {

        if (!empty($clip['keywords_types'])) {
            foreach ($clip['keywords_types'] as $keyword) {

                if ($keyword['section_id'] == 'country') {
                    $countryName = $keyword['keyword'];
                }
            }
        }
        ?>

        <tr class="control-group cliplog_section"
            id="country"<?php if (!isset($country)) { ?> style="display: none;"<?php } ?>>
            <td>
                <table>
                    <tr>
                        <td class="left">
                            <span class="field_label">Country:</span>
                            <?php if (isset($hints['country'])) { ?><img
                                src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon"
                                title="<?php echo $hints['country']; ?>"><?php } ?>
                            <div
                                class="overwrite-check <?php if ($clip) echo ' single_clip_owervrite_class'; ?>">
                                <input type="checkbox" name="overwrite[country]"
                                       value="country" <?php if ($overwrite) echo ' checked="checked"'; ?> />
                                overwrite existing values
                            </div>
                        </td>
                        <td class="right">
                            <select class="input-large" name="sections_values[country]"
                                    data-formkeeper="keywords" id="countryMetaDataSelect">
                                <option value=""><?php echo $defaultSelect; ?></option>
                                <?php foreach ($countries as $country_item) { ?>
                                    <option
                                        value="<?php echo $country_item['name']; ?>" <?php echo (isset($countryName) && $countryName == $country_item['name'] && !$reset) ? 'selected' : ''; ?>><?php echo $country_item['name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td class="section-switch-cont">
                            <div class="switch-cont">
                                <div class="switch" data-animated="false" data-on-label=""
                                     data-off-label="">
                                    <input type="checkbox" name="sections[]" id="Country"
                                           value="country" <?php echo isset($country) || !$cliplog_template ? 'checked' : ''; ?>
                                           data-formkeeper="logging"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>


    <?php } ?>

    <div style='display:none'>
        <div id='inline_content<?php echo $sectionName; ?>' style='padding:10px; background:#fff;'>

            <p>Keywords Management For: <strong><?php echo $sectionTitle; ?></strong></p>
            <br>
            <input type="text" value="" name="" id="addNewFeild<?php echo $sectionName; ?>" style="float:left">
            <input type="button" class="addDataUserButton" id="addNewbutton<?php echo $sectionName; ?>"
                   data-id="<?php echo $sectionName; ?>" value="Add">
            <br clear="all"><br>

            <p id="dataInsert<?php echo $sectionName; ?>"></p>
        </div>
    </div>

    <script>
        $(document).on('click', 'div.cliplog_selected_keywords_list_new_users<?php echo $sectionName; ?> a.item-cross', function () {
            $(this).closest('.item-wrapper').remove();
        });</script>

<?php } ?>

<input type="hidden" value="" name="checkingFeilddata" id="checkingFeilddata">
<input type="hidden" value="" name="deleteFeilddata" id="deleteFeilddata">
<script>
    function autocomplet(sectionName) {
        var min_length = 0; // min caracters to display the autocomplete
        var keyword = $('#' + sectionName + ' .cliplog_keyword_input').val();
        var clip_id = $('#section_clip_id').val();
        var section_search_name = sectionName;
        if (keyword.length >= min_length) {
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: {
                    keyword: keyword,
                    clip_id: clip_id,
                    section_search_name: section_search_name,
                    action: 'keyword_search'
                },
                success: function (data) {
                    if (data == '') {
                        $('#' + sectionName + ' .result_keyword').hide();
                    }
                    else {
                        $('#' + sectionName + ' .result_keyword').show();
                        $('#' + sectionName + ' .result_keyword').html(data);
                    }
                }
            });
        } else {
            $('#' + sectionName + ' .result_keyword').hide();
        }
    }

    function set_item(item, sectionName) {
        // change input value
        $('#' + sectionName + ' .cliplog_keyword_input').val(item);
        // hide proposition list
        $('#' + sectionName + ' .result_keyword').hide();
    }
    function hideResult(sectionName) {
        setTimeout(function () {
            $('#' + sectionName + ' .result_keyword').hide();
        }, 300);
    }
</script>
<style>
    .addDataUserButton {
        float: left;
        padding: 7px;
        margin-left: 10px;
    }

    .newCross {
        display: inline-block;
        border: 1px solid #cdcdcd;
        border-radius: 3px;
        margin: 3px;
        background: url(/data/img/admin/cliplog/remove_icon.jpg) 8px center no-repeat #fafafa;
        cursor: pointer;
    }

    .newCrossInner {
        padding: 2px 6px;
        margin-left: 22px !important;
        border-left: 1px solid #cdcdcd;
        letter-spacing: 0.5pt;
    }

    .input_container {
        height: 30px;
        float: left;
    }

    .input_container input {
        height: 20px;
        width: 200px;
        padding: 3px;
        border: 1px solid #cccccc;
        border-radius: 0;
    }

    .input_container ul {
        width: 206px;
        border: 1px solid #eaeaea;
        position: absolute;
        z-index: 9;
        background: #f3f3f3;
        list-style: none;
        margin-left: 0px !important;
        border: 1px solid #cccccc;
        max-height: 180px !important;
        overflow-y: scroll !important;
    }

    .input_container ul li {
        padding: 2px;
    }

    .input_container ul li:hover {
        background: #eaeaea;
    }

    .result_keyword {
        display: none;
    }
</style>