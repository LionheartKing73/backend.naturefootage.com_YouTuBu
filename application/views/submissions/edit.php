<form action="<?= $lang ?>/submissions/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT SUBMISSION'; else echo 'ADD SUBMISSION'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="code">
                Code: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="code" id="code" value="<?=$code?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="date">
                Date:
            </label>
            <div class="controls">
                <input type="text" name="date" id="date" value="<?=$date?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
