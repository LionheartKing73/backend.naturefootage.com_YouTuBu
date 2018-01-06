<?php
/**
 * @var $folders array
 * @var $bins array
 * @var $clips array
 * @var $active_clipbin array
 * @var $lang string
 */
?>
<div class="left-box-header spoiler-control expanded" data-spoiler=".clipbin-list">Clipbins</div>
<div class="clipbin-list expanded">
    <?php if ( $active_clipbin ) { ?>
    <div class="clipbin-current">
        <a href="<?php echo $lang; ?>/cliplog/view/?backend_clipbin=<?php echo $active_clipbin['id']; ?>">
            <?php echo $active_clipbin[ 'title' ]; ?>
            <span class="items-count" data-clipbin-id="<?php echo $active_clipbin[ 'id' ]; ?>">(<?php echo count( $active_clipbin[ 'items' ] ); ?>)</span>
        </a>
    </div>
    <?php } ?>

    <?php ### Колодец ### ?>

    <div class="clipbin-well-wrapper">
        <?php $this->load->view( 'cliplog/clipbins_box/well' ); ?>
    </div>

    <?php ### Filter clipbins ### ?>
    <form class="form-inline clipbins-list-filter" method="GET">
        <input type="text" class="clipbis-filter-words" placeholder="Filter list" name="words" value="<? echo $_SESSION['clipbins_filter']; ?>">
    </form>

    <?php ### Свернуть\Развернуть ### ?>

    <div class="clipbin-view-actions">
        <a class="spoiler-control" data-spoiler=".clipbin-folder-list" data-spoiler-action="collapse">Collapse All</a>
        |
        <a class="spoiler-control" data-spoiler=".clipbin-folder-list" data-spoiler-action="expand">Expand All</a>
    </div>

    <?php ### Управление ### ?>

    <div class="clipbin-edit-actions">
        <img src="/data/img/admin/cliplog/view/create_clipbin_icon.png" class="clipbin-action" title="Create Clipbin" data-action="clipbin">
        <img src="/data/img/admin/cliplog/view/create_folder_icon.png" class="clipbin-action" title="Create Folder" data-action="folder">
        <div class="edit-action-clipbin">
            <form method="POST" class="footagesearch-clipbin-create-clipbin-form">
                <input type="hidden" name="clipbin_id" value="">
                <input type="text" name="clipbin_title" class="text">
                <select name="clipbin_folder_id">
                    <option value="0"></option>
                    <?php if ( $folders ) { ?>
                        <?php foreach ( $folders as $fid => $folder ) { ?>
                            <option value="<?php echo $fid; ?>"><?php echo $folder[ 'name' ]; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <input type="submit" name="create_clipbin" value="Create" class="action">
            </form>
        </div>
        <div class="edit-action-folder">
            <form method="POST" class="footagesearch-clipbin-create-folder-form">
                <input type="hidden" name="folder_id" value="">
                <input type="text" name="folder_name" class="text">
                <input type="submit" name="create_folder" value="Create" class="action">
            </form>
        </div>
    </div>

    <div class="clipbin-scroll ios-scrollable">

        <?php ### Вывод клиплогов без папок ### ?>

        <?php if ( $bins ) { ?>
            <div class="no-folder">
            <?php foreach ( $bins as $bin ) { $bid = $bin[ 'id' ]; ?>
                <div
                    class="clipbin-item
                        <?php echo ( $bid == $active_clipbin[ 'id' ] ) ? 'selected' : ''; ?>
                        <?php if($bin['is_gallery']){ echo 'gallery';} ?>
                        <?php if($bin['featured']){ echo 'featured';} ?>
                        <?php if($bin['is_sequence']){ echo 'sequence';} ?>"
                    data-clipbin-id="<?php echo $bid; ?>">
                    <a href="<?php echo $lang . '/cliplog/view/?backend_clipbin=' . $bid; ?>">
                        <span class="clipbin-title"><?php echo $bin[ 'title' ]; ?></span>
                        <span class="items-count" data-clipbin-id="<?php echo $bid; ?>">(<?php echo $bin[ 'items_count' ]; ?>)</span>
                    </a>
                    <img src="/data/img/admin/cliplog/view/options_icon.png" title="Clibbin actions" class="clipbin-actions">
                </div>
            <?php } ?>
            </div>
        <?php }else{ ?>
            <div class="no-folder empty"><span>Drop bins here</span></div>
        <? } ?>

        <?php ### Вывод папок с клиплогами ### ?>

        <?php if ( $folders ) { ?>
            <div class="folder">
            <?php foreach ( $folders as $fid => $folder ) { ?>
                <div class="clipbin-folder-list collapsed" data-folder-id="<?php echo $fid; ?>">
                    <div class="clipbin-folder-item" data-folder-id="<?php echo $fid; ?>">
                        <span class="folder-title"><?php echo $folder[ 'name' ]; ?></span>
                        <span class="items-count" data-folder-id="<?php echo $fid; ?>">(<?php echo count( $folder[ 'bins' ] ); ?>)</span>
                        <img src="/data/img/admin/cliplog/view/options_icon.png" alt="Clibbin actions" title="Clibbin actions" class="clipbin-folder-actions">
                    </div>
                    <?php if ( $folder[ 'bins' ] ) { ?>
                    <div class="clipbin-folder-items">
                        <?php foreach ( $folder[ 'bins' ] as $bid => $bin ) { ?>
                            <div class="clipbin-item <?php echo ( $bid == \Libraries\Cliplog\Clipbin\ClipbinRequest::getInstance()->getClipbinActive()->getActiveClipbinId() ) ? ' selected ' : ''; ?> <?php if($bin['is_gallery']){ echo ' gallery';} ?>
                            <?php if($bin['is_sequence']){ echo ' sequence';} ?>"
                                data-clipbin-id="<?php echo $bid; ?>">
                                <a href="<?php echo $lang . '/cliplog/view/?backend_clipbin=' . $bid; ?>">
                                    <span class="clipbin-title"><?php echo $bin[ 'title' ]; ?></span>
                                    <span class="items-count" data-clipbin-id="<?php echo $bid; ?>">(<?php echo $bin[ 'items_count' ]; ?>)</span>
                                </a>
                                <img src="/data/img/admin/cliplog/view/options_icon.png" title="Clibbin actions" class="clipbin-actions">
                            </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            <?php } ?>
            </div>
        <?php } ?>

    </div>

</div>

<script type="text/javascript">
    $( document ).ready( function () {
        //$( '.clipbin-well' ).jScrollPane();
    } );
</script>