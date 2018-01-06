<?php
/**
 * @var $clips array
 * @var $selected_bin array
 * @var $lang string
 */
?>

<?php if ( $clips ) { ?>
    <div class="clipbin-well ios-scrollable" data-clipbin-id="<?php echo \Libraries\Cliplog\Clipbin\ClipbinRequest::getInstance()->getClipbinActive()->getActiveClipbinId(); ?>">
        <?php $this->load->view( 'cliplog/clipbins_box/well_clips' ); ?>
    </div>
<?php } else { ?>
    <div class="clipbin-well empty" data-clipbin-id="<?php echo \Libraries\Cliplog\Clipbin\ClipbinRequest::getInstance()->getClipbinActive()->getActiveClipbinId(); ?>">
        <img src="/data/img/admin/cliplog/view/bin_icon.png">
        <span>Drag and drop clips into your Clipbin</span>
    </div>
<?php } ?>