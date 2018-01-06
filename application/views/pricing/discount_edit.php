<form action="<?= $lang ?>/pricingdiscount/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT DISCOUNT'; else echo 'ADD DISCOUNT'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="duration">
                Duration: <span class="mand">*</span>
			</label>
			<div class="controls">
                <div class="input-append">
				    <input type="text" name="duration" id="duration" value="<?=$duration?>"><span class="add-on">s</span>
                </div>
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
