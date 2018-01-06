<style type="text/css">
    @import url(/data/css/cliplog_view.new.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/clips_list.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/js/videojs/video-js.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/jquery.contextMenu.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/cliplog.clipbin.css<?php echo '?' . date('dmYh'); ?>);
    @import url(/data/css/cliplog_view.css<?php echo '?' . date('dmYh'); ?>);
</style>

<script type="text/javascript" src="/data/js/jScrollPane/jquery.jscrollpane.min.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/jScrollPane/jquery.mousewheel.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog_view.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/videojs/video.js<?php echo '?' . date('dmYh'); ?>"></script>
<script type="text/javascript" src="/data/js/videojs/video-controls.js<?php echo '?' . date('dmYhi'); ?>"></script>
<script type="text/javascript" src="/data/js/cliplog.createthumbs.js<?php echo '?' . date('dmYh'); ?>"></script>
<script>videojs.options.flash.swf = "/data/js/videojs/video-js.swf"</script>
<script type="text/javascript">
    $(document).ready(function() {
        /*$( '.clipbin-well' ).jScrollPane();
         $( '.clipbin-scroll' ).jScrollPane();
         $( '.cliplog-submissions-scroll' ).jScrollPane();*/
    });
</script>
<style>
    .clickImage{
        margin: 0px;
        padding: 0px;
        float: right;
    }
    .titleHover{
        margin: 10px 0px 0px 0px;
        padding: 0px;
        float: left;
        width: auto
    }.info-icon {
        display: inline-block;
        width: 26px;
        height: 26px;
        background-image: url('/data/img/cliplog/info.png');
        margin: 5px 0 -5px 0;
    }
</style>
<? if ($clips) { ?>
<div id="playOriginal"></div>

<div id="playPreview"></div>

<script type="text/javascript">
    lang = "<?= $lang ?>";

    function playPreview(preview) {
        createPlayer(preview, 512, 288, "playPreview", false, true);
        $("#playPreview").dialog({
            title: "Clip Preview",
            width: "auto",
            height: "auto",
            position: ["center", "center"],
            modal: true,
            resizable: false,
            buttons: {"Close": function() {
                    $(this).dialog("close");

                }},
            beforeClose: function() {
                removePlayer("playPreview");
            }
        });
    }

    function playOriginal(path, width, height) {
        var qtContent = QT_GenerateOBJECTText('<?= base_url() ?>' + path, width, height + 16, "",
                "controller", "true", "autoplay", "true", "scale", "aspect", "bgcolor",
                "black", "wmode", "opaque",
                "obj#ID", "playOriginal_qtobj", "emb#ID", "playOriginal_qtembed");

        $("#playOriginal").html(qtContent);

        $("#playOriginal").dialog({
            title: "Original Video",
            width: "auto",
            height: "auto",
            position: ["center", "center"],
            modal: true, resizable: true, buttons:
                    {"Close": function() {

                            $(this).dialog("close");
                        }},
            beforeClose: function() {
                removePlayer("playOriginal");
            }
        });
    }
</script>
<? } ?>
<div class="cliplog-filter-cont ios-scrollable">
    <div class="left-box-list">
        <div class="left-box">
            <div class="left-box-header spoiler-control " data-spoiler=".searchword-list">
                <span>Clip Search</span>
            </div>
            <div class="searchword-list expanded">
                <form method="POST" action="/en/cliplog/view" class="cliplog-search-form">
                    <div class="search-box">
                        <?php if ($selected_clipbin) { ?>
                            <input type="hidden" name="bin" value="<?php echo $selected_clipbin['id']; ?>">
                        <?php } ?>
                        <?php if ($selected_gallery) { ?>
                            <input type="hidden" name="gallery" value="<?php echo $selected_gallery['id']; ?>">
                        <?php } ?>
                        <?php if ($_REQUEST['submission']/* $selected_submission */) { ?>
                            <input type="hidden" name="submission" value="<?php echo $_REQUEST['submission']/* $selected_submission[ 'id' ] */; ?>">
                        <?php } ?>
                        <?php if ($selected_sequence) { ?>
                            <input type="hidden" name="sequence" value="<?php echo $selected_sequence['id']; ?>">
                        <?php } ?>
                        <input type="text" name="words" placeholder="Search by keyword" value="<?php echo isset($_SESSION['searchWordFilter']) ? $_SESSION['searchWordFilter'] : ''; ?>" style="margin: 0;">
                        <input type="submit" class="btn" value="Search">
                    </div>
            </div>
        </div>
    </div>
    <div class="left-box-list">
        <div class="left-box">
            <div class="left-box-header spoiler-control <?php echo ( $search_flags ) ? 'expanded' : 'collapsed'; ?>" data-spoiler=".search-list">
                <span>Search Filter</span>
            </div>
            <?php $this->load->view('cliplog/search_box'); ?>
            <input type="hidden" name="update_filter" value="1" />
            </form>
        </div>

        <br />
        <div class="left-box clipbins-widget-holder">
            <?php echo $clipbins_list; ?>
        </div>
    </div>

    <div class="cliplog-tree-cont">

        <ul class="cliplog-tree">
            <ul>
                <span class="cliplog-tree-section">Clipbins</span>
                <?php echo $clipbins_list; ?>
            </ul>
        </ul>


        <ul class="cliplog-tree left-box-list">
            <li class="left-box expanded">
                <span class="left-box-header expanded cliplog-tree-section spoiler-control" >Submissions</span>
                <div class="cliplog-list-cont">
                    <div class="cliplog-list-filter">
                        <?php if ($is_admin && $providers) { ?>
                            <select name="submissions_provider_id" class="submissions-provider-select">
                                <option value="0">Provider</option>
                                <?php foreach ($providers as $item) { ?>
                                    <option value="<?= $item['id'] ?>"<?php if ($item['id'] == $client_id) echo ' selected' ?>>
                                        <?= $item['fname'] . ' ' . $item['lname']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                        <form class="form-inline submissions-list-filter" method="GET">
                            <input type="text" placeholder="Filter list" name="words">
                            <input type="submit" name="find_by_words" value="Filter" class="btn">
                        </form>
                    </div>
                    <div class="cliplog-submissions-actions">
                        <a href="#" class="collapse-action">Collapse All</a>
                        |
                        <a href="#" class="expand-action">Expand All</a>
                    </div>
                    <div class="cliplog-submissions-scroll ios-scrollable">
                        <?php echo $submissions_list; ?>
                    </div>
                </div>
            </li>
        </ul>

    </div>

    <div id="bin-form" title="Create bin" class="cliplog-modal">
        <p class="alert"></p>
        <form>
            <div class="control-group">
                <label class="control-label" for="bin_title">Title <span class="mand">*</span></label>
                <div class="controls">
                    <input type="text" id="bin_title" name="title">
                    <input type="hidden" name="id">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="bin_code">Code</label>
                <div class="controls">
                    <input type="text" id="bin_code" name="code">
                </div>
            </div>
        </form>
    </div>

    <div id="gallery-form" title="Create gallery" class="cliplog-modal">
        <p class="alert"></p>
        <form>
            <div class="control-group">
                <label class="control-label" for="gallery_title">Title <span class="mand">*</span></label>
                <div class="controls">
                    <input type="text" id="gallery_title" name="title">
                    <input type="hidden" name="id">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="gallery_code">Code</label>
                <div class="controls">
                    <input type="text" id="gallery_code" name="code">
                </div>
            </div>
            <div class="control-group">
                <label class="checkbox" for="gallery_featured">
                    <input type="checkbox" id="gallery_featured" name="featured"> Featured
                </label>
            </div>
            <div class="gallery-clips-list"></div>
            <input type="hidden" name="add_selected_clips" value="0">
        </form>
    </div>

    <div id="submission-form" title="Create submission" class="cliplog-modal">
        <p class="alert"></p>
        <form>
            <div class="control-group">
                <label class="control-label" for="submission_code">Code <span class="mand">*</span></label>
                <div class="controls">
                    <input type="text" id="submission_code" name="code">
                    <input type="hidden" name="id">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="submission_date">Date</label>
                <div class="controls">
                    <input type="text" id="submission_date" name="date">
                </div>
            </div>
        </form>
    </div>

    <div id="sequence-form" title="Create bin" class="cliplog-modal">
        <p class="alert"></p>
        <form>
            <div class="control-group">
                <label class="control-label" for="sequence_title">Title <span class="mand">*</span></label>
                <div class="controls">
                    <input type="text" id="sequence_title" name="title">
                    <input type="hidden" name="id">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="sequence_code">Code</label>
                <div class="controls">
                    <input type="text" id="sequence_code" name="code">
                </div>
            </div>
            <input type="hidden" name="add_selected_clips" value="0">
        </form>
    </div>

</div>

<div class="clips-list">

    <form method="post" class="footagesearch-list-view-form">
        <input type="hidden" name="list_view">
    </form>
    <?php if ($selected_clipbin['is_gallery']) {//if($selected_clipbin['featured']){ ?>
        <table>
            <tr>
                <td>
                    <span class="featured-title">Displaying <?php echo ($selected_clipbin['featured']) ? 'Featured ' : ''; ?>Gallery: <?php echo $selected_clipbin['title']; ?></span>
                </td>
                <td>
                    <div class="featured-pulldown expanded" data-clipbin-id="<?php echo $selected_clipbin['id']; ?>">

                        <div style="display: inline-block" class="featured-pull">
                            <span class="featured-thumb-min">
                                <?php //if (!empty($selected_clipbin['preview_clip'])) { ?>
                                        <!--img src="<?php echo (!empty($selected_clipbin['preview_clip'])) ? urldecode($selected_clipbin['preview_clip']) : '/backend-content/profiles/no-photo.jpg'; ?>" -->
                                <?php //}  ?>
                            </span>
                            <span class="featured-thumb-title"></span>
                        </div>
                        <div class="droppablearea">
                            <div class="featured-thumb-max">
                                <?php if (!empty($selected_clipbin['preview_clip']) && $selected_clipbin['preview_clip'] != '/backend-content/profiles/no-photo.jpg') { ?>
                                    <img src="<?php echo (!empty($selected_clipbin['preview_clip'])) ? urldecode($selected_clipbin['preview_clip']) : '/backend-content/profiles/no-photo.jpg'; ?>">
                                <?php } else { ?>
                                    <div style="text-align: center;">Drag Featured Clip Here</div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    <?php } ?>
    <form name="clips" action="<?= $lang ?>/clips/view<?= $this->config->item('url_suffix') ?>" method="post">

        <table class="top-navigation">
            <tr>
                <td colspan="4">
                    <?php if ($words || $wordsin && !$_SESSION['cliplog_search_wordsin']) { ?>
                        <p>
                            Search Results: <?php echo $words . ' ' . $wordsin; ?>
                        </p>
                    <?php } ?>

                    <?php if ($_SESSION['cliplog_search_wordsin'] && !$words && !$wordsin) { ?>
                        <p>
                            Search Results: <?php echo $_SESSION['cliplog_search_wordsin']; ?>
                        </p>
                    <?php } ?>
                    <?php if (!$_SESSION['cliplog_search_wordsin'] && $_SESSION['searchWordFilter'] && !$words && !$wordsin) { ?>
                        <p>
                            Search Results: <?php echo $_SESSION['searchWordFilter']; ?>
                        </p>
                    <?php } ?>







                    <?php if ($selected_clipbin && !$selected_clipbin['featured'] && !$selected_clipbin['is_gallery'] && !$selected_clipbin['is_sequence']) { ?>
                        <p>
                            Displaying Bin: <?php echo $selected_clipbin['title']; ?>
                        </p>
                    <?php } ?>
                    <?php if ($selected_submission) { ?>
                        <p>
                            Displaying Submission: <?php echo $selected_submission['code']; ?>
                        </p>
                    <?php } ?>
                    <?php if ($selected_clipbin['is_sequence']) { ?>
                        <p>
                            Displaying Sequence: <?php echo $selected_clipbin['title']; ?>
                        </p>
                    <?php } ?>
                </td>
                <td colspan="2" style="padding-top: 8px;">
                    <div class="pagination" style="width: auto">
                        <?php if ($paging) { ?>
                            <?php echo $paging; ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <p>
                        Viewing Clips: <?php echo ($paging_count['all'] <= 0) ? 'No clips found' : $paging_count['from'] . ' to ' . $paging_count['to'] . ' of ' . $paging_count['all']; ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <!--Clipbin title-->
                </td>
                <td>
                    <?php //if ( isset( $selected_clipbin ) ) {  ?>
                    <div class="btn-group toolbar-item view-all-clips">
                        <a href="/en/cliplog/view.html?backend_clipbin=0" class="btn">View all clips</a>
                    </div>&nbsp;
                    <?php //}  ?>
                    <div class="btn-group toolbar-item">
                        <button class="btn cliplog-select-all-btn">Select All</button>
                        <button class="btn cliplog-deselect-all-btn">Unselect</button>
                    </div>
                </td>
                <td>
                    <?php
//                    echo '<pre>';
//                    echo $this->session->userdata('group').'<br>';
//                    print_r($this->permissions);
//                    echo '</pre>';
//                    die
                    ?>
                    <select name="cliplog-actions-select" class="cliplog-actions-select">
                        <option value="">Actions</option>
                        <?php ?><?php if ($this->permissions['cliplog-edit']) { ?>
                            <option value="log">Log selected</option>
                        <?php } ?><?php ?>
                        <?php if ($this->permissions['cliplog-log-selected']) { ?>
                            <option value="log_selected">Log Found Set</option>
                        <?php } ?>
                        <?php if ($this->permissions['clips-visible']) { ?>
                            <option value="move_offline">Move Offline</option>
                            <option value="move_online">Move Online</option>
                        <?php } ?>

                        <?php if ($is_admin) { ?>
                            <option value="move_archive">Archive</option>
                            <option value="unarchive">Unarchive</option>
                        <?php } ?>

                        <?php if ($this->permissions['clips-delete']) { ?>
                            <option value="delete">Delete</option>
                        <?php } ?>
                        <option value="to_clipbin">Add to Clipbin</option>
                        <option value="create_thumb">Create Thumbnail</option>
                    </select>
                </td>

                <td>
                    <select name="clips_sort_by" class="cliplog-sortby-select">
                        <option>Sort By</option>
                        <option value="id">Clip ID</option>
                        <!-- option value="rating">Star Rating</option -->
                        <option value="duration">Duration</option>
                    </select>
                </td>
                <td>
                    <div class="footagesearch-clips-list-toggle-view-cont">
                        <div class="footagesearch-clips-toggle-list-view<?php if (isset($list_view) && $list_view == 'list') echo ' active'; ?>">&nbsp;</div>
                        <div class="footagesearch-clips-toggle-grid-view<?php if (!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">&nbsp;</div>
                        <div class="clearboth"></div>
                    </div>
                </td>
                <td>
                    <?php
                    $pathToMove = '';
                    if ($this->session->userdata('backend_clipbin_id')) {
                        $pathToMove = 'en/cliplog/view?backend_clipbin=' . $this->session->userdata('backend_clipbin_id');
                    } elseif ($this->session->userdata('submissionId')) {
                        $pathToMove = 'en/cliplog/view?submission=' . $this->session->userdata('submissionId');
                    } else {
                        $pathToMove = 'en/cliplog/view';
                    }
                    ?>

                    Clips per page
                    <select name="clips_on_page" class="cliplog-onpage-select" path-redirect="<?= $pathToMove ?>" style="width: 50px;">
                        <option value="10" <?php echo ( $current_perpage == 10 ) ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo ( $current_perpage == 20 ) ? 'selected' : ''; ?>>20</option>
                        <option value="30" <?php echo ( $current_perpage == 30 ) ? 'selected' : ''; ?>>30</option>
                        <option value="40" <?php echo ( $current_perpage == 40 ) ? 'selected' : ''; ?>>40</option>
                        <option value="50" <?php echo ( $current_perpage == 50 ) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ( $current_perpage == 100 ) ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo ( $current_perpage == 200 ) ? 'selected' : ''; ?>>200</option>
                    </select>
                </td>
            </tr>
        </table>

        <input type="hidden" name="filter" value="1">

        <div style="clear: right"></div>

        <div id="footagesearch-clip-preview-dialog"></div>
        <div id="cliplog-alert-dialog"></div>

        <?php $this->load->view('cliplog/view/clips'); ?>

        <?php if ($paging) { ?>
            <div class="pagination"><?= $paging ?></div>
        <?php } ?>

        <?php if (!$hide_clips_actions) { ?>
        </form>
    <?php } ?>
    <?php if ($clips) { ?>
        <script type="text/javascript" src="/data/js/bootstrap-dropdown.js"></script>
    <?php } ?>

</div>
<div class="clr"></div>

<script type="text/javascript">
    $(document).ready(function() {
        var clipsListBox = $('.clips-list');
        clipsListBox.find('.footagesearch-clip').each(function() {
            debugErrors && console.log('Clip draggable: Enable');
            $(this).draggable('enable');
        });
    });
</script>

<!-- changeThumbBox -->
<div id="dialog-change-thumb" style="display: none;" data-id="#">
    <div class="clip">
        <div id="footagesearch-clip" class="footagesearch-preview-clip">
            <video class="video-js vjs-default-skin change-thumb-video" preload="auto" controls width="320" height="230" muted data-setup="{}">
                <source src="#" type="video/mp4" />
            </video>
        </div>
    </div>
    <div class="thumb"></div>
</div>
<!-- clipPreviewBox -->
<div id="footagesearch-clip-preview" style="display: none;">
    <!--    <h6 class="title_id" style="float: right;"></h6>
        <h6 class="title"></h6>
        <div class="clr"></div>
        <video id="" class="video-js vjs-default-skin" preload="auto" width="432" height="240" data-setup="{}">
            <source src="" type="video/mp4">
        </video>
        <p class="description"></p>-->

    <h6 class="title"></h6>
    <p class="clickImage"></p>
    <br clear="all">
    <video id="" class="video-js vjs-default-skin" preload="auto" width="432" height="240" muted data-setup="{}">
        <source src="" type="video/mp4">
    </video>
    <p class="description"></p>
    <p class="license_restrictions"></p>
    <p class="country"></p>
    <p class="price_level"></p>
    <?php if ($_SESSION['group'] == 1) { ?>
        <p class="source_format"></p>
        <p class="dilivery_options"></p>
    <?php } ?>

</div>

<?php
$page = $_SERVER["REQUEST_URI"];
$_SESSION['backPageThumb'] = $page;
?>