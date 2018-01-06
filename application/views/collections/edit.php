<form action="<?= $lang ?>/collections/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT COLLECTION'; else echo 'ADD COLLECTION'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="name">
                Name: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="name" id="name" value="<?=$name?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="frontend_id">
                Frontend
            </label>
            <div class="controls">
                <select name="frontend_id" id="frontend_id">
                    <option value="0"></option>
                    <?php foreach($frontends as $item) { ?>
                        <option value="<?php echo $item['id']; ?>"<?php if($item['id'] == $frontend_id) echo ' selected'?>><?php echo $item['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        
		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
