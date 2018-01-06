<form action="<?= $lang ?>/formats/source_edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT'; else echo 'ADD'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="format">
                Format: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="format" id="format" value="<?=$format?>">
				<input type="hidden" name="id" value="<?=$id?>">
                <input type="hidden" name="camera" value="1">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="sort">
                Sort:
            </label>
            <div class="controls">
                <input type="text" name="sort" id="sort" value="<?=$sort?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
