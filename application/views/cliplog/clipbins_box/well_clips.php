<?php
/**
 * @var $clips array
 */
?>

<?php foreach ( $clips as $clip ) { $cid = $clip[ 'id' ]; ?>
    <div class="small-item" data-clip-id="<?php echo $cid; ?>" title="<?php echo $clip[ 'title' ]; ?>">
        <img src="<?php echo $clip[ 'thumb' ]; ?>">
    </div>
<?php } ?>