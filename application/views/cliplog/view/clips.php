<?php if ( $list_view == 'list' ) { ?>
    <table class="table table-striped">
    <tr>
    <?php if ( $hide_clips_actions ) { ?>
        <th><?php echo $this->lang->line( 'thumbnail' ); ?></th>
        <!--<th><?php //echo $this->lang->line( 'desc' ); ?></th>-->
        <th><?php echo $this->lang->line( 'code' ); ?></th>
        <th>Details</th>
        <th>Original filename</th>
        <?php if ( $is_admin ) { ?>
        <th>Provider</th>
        <?php } ?>
        <th><?php echo $this->lang->line( 'status' ); ?></th>
        <th>Transcoding status</th>
        <th><?php echo $this->lang->line( 'date' ); ?></th>
        <th></th>
    <?php } else { ?>
        <th><?php echo $this->lang->line( 'thumbnail' ); ?></th>
        <!--<th><a href="<?php //echo $uri; ?>/sort/title" class="title"><?php //echo $this->lang->line( 'desc' ); ?></a></th>-->
        <th><a href="<?php echo $uri; ?>/sort/code" class="title"><?php echo $this->lang->line( 'code' ); ?></a></th>
        <th>Details</th>
        <th>Original filename</th>
        <?php if ( $is_admin ) { ?>
            <th><a href="<?php echo $uri; ?>/sort/client_id" class="title">Provider</a></th>
        <?php } ?>
        <th><a href="<?php echo $uri; ?>/sort/active" class="title"><?php echo $this->lang->line( 'status' ); ?></a></th>
        <th>Transcoding status</th>
        <th><a href="<?php echo $uri; ?>/sort/ctime" class="title"><?php echo $this->lang->line( 'date' ); ?></a></th>
    <?php } ?>
    </tr>
<?php } ?>

<?php if($list_view == 'list') { ?>
    <div class="footagesearch-clips-<?php echo $list_view; ?>">
<?php } else { ?>
    <div class="clearfix">
<?php } ?>

<?php $this->load->view( 'cliplog/view/clip' ); ?>

<?php if ( $list_view == 'grid' ) { ?>
            <div class="clearfix"></div>
        </div>
    </div>
<?php } else { ?>
    </table>
<?php } ?>