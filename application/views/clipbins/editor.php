<?php if(!empty($success)){ ?><div class="error" style="color: #5c5;background: #efe;"><?=$success; ?></div><?php } ?>
<?php if(!empty($error)){ ?><div class="error" style="color: #f55;background: #fee;">ERROR: <?=$error; ?></div><?php } ?>
<form action="<?= $lang ?>/clipbins/editor" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? 'EDIT CLIPBIN'; ?>
		</legend>

        <div class="halfForm">

            <div class="control-group">
                <label class="control-label" for="title">
                    USER: <span class="mand">*</span>
                </label>
                <div class="controls">
                    <input type="text" name="login" id="login" value="<?=$login?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="title">
                    CLIPBIN: <span class="mand">*</span>
                </label>
                <div class="controls">
                    <input type="text" name="clipbin" id="clipbin" value="<?=$clipbin?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="title">
                    Products:
                </label>
                <div class="controls">
                    <textarea name="clip_ids" id="clip_ids" rows="10"><?=$clip_ids?></textarea>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
<script type="text/javascript">
    $('.datepicker').datepicker();
</script>
