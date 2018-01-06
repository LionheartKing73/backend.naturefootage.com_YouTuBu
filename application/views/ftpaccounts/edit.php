<form action="<?= $lang ?>/ftpaccounts/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT ACCOUNT'; else echo 'ADD ACCOUNT'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="tuserid">
                Username: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="userid" id="userid" value="<?=$userid?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="passwd">
                Password: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="passwd" id="passwd" value="<?=$passwd?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="order_id">
                Order ID:
            </label>
            <div class="controls">
                <input type="text" name="order_id" id="order_id" value="<?=$order_id?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
