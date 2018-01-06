<form action="<?= $lang ?>/pricingterm/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT TERM'; else echo 'ADD TERM'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="term_cat">
                Term category: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="term_cat" id="term_cat" value="<?=$term_cat?>" maxlength="5">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="territory">
                Territory: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="territory" id="territory" value="<?=$territory?>">
            </div>
        </div>

		<div class="control-group">
			<label class="control-label" for="term">
                Term: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="term" id="term" value="<?=$term?>">
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
