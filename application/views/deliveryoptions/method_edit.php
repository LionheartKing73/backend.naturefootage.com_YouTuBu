<form action="<?= $lang ?>/deliverymethods/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT METHOD'; else echo 'ADD METHOD'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="code">
                Code: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="code" id="code" value="<?=$code?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="title">
                Title: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="title" id="title" value="<?=$title?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>
	</fieldset>
</form>
