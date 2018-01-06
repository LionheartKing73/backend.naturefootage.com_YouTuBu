<form action="<?= $lang ?>/discount_display_rf/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT'; else echo 'ADD'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="type">
                Type: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="type" id="type" value="<?=$type?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="body">
                Content:
            </label>
            <div class="controls">
                <textarea name="body" class="body" id="body"><?=$body?></textarea>
                <?=tiny()?>
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
