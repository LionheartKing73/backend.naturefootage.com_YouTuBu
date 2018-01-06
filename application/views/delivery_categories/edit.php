<form action="<?= $lang ?>/delivery_categories/edit/<?=$pk?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($pk) echo 'EDIT'; else echo 'ADD'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="id">
                Code: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="id" id="id" value="<?=$id?>">
                <input type="hidden" name="pk" value="<?=$pk?>">
			</div>
		</div>
        <div class="control-group">
			<label class="control-label" for="description">
                Description: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="description" id="description" value="<?=$description?>">
			</div>
		</div>
        <div class="control-group">
            <label class="control-label" for="display_order">
                Sort:
            </label>
            <div class="controls">
                <input type="text" name="display_order" id="display_order" value="<?=$display_order?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
