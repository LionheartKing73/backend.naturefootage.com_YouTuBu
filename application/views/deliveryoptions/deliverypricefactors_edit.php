<form action="<?= $lang ?>/deliverypricefactors/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT PRICE FACTOR'; else echo 'ADD PRICE FACTOR'; ?>
		</legend>

        <div class="halfForm">
		
		<div class="control-group">
			<label class="control-label" for="format">
                Format: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="format" id="format" value="<?=$format?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
			<label class="control-label" for="factor">
                Factor: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="factor" id="factor" value="<?=$factor?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>
	</fieldset>
</form>
