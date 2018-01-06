<form action="<?= $lang ?>/pricingrfdiscount/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT DISCOUNT'; else echo 'ADD DISCOUNT'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="count">
                Count: <span class="mand">*</span>
			</label>
			<div class="controls">
                <input type="text" name="count" id="count" value="<?=$count?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="discount">
                Discount: <span class="mand">*</span>
            </label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="discount" id="discount" value="<?=$discount?>"><span class="add-on">%</span>
                </div>
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
