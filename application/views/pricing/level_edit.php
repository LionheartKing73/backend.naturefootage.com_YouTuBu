<form action="<?= $lang ?>/pricinglevel/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT LEVEL'; else echo 'ADD LEVEL'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="category">
                Category: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="category" id="category" value="<?=$category?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="price_level">
                Price level: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="price_level" id="price_level" value="<?=$price_level?>">
            </div>
        </div>

		<div class="control-group">
			<label class="control-label" for="factor">
                Factor: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="factor" id="factor" value="<?=$factor?>">
			</div>
		</div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
