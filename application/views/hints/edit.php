<form action="<?= $lang ?>/hints/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT HINT'; else echo 'ADD HINT'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="text">
                Text: <span class="mand">*</span>
            </label>
            <div class="controls">
                <textarea name="text" id="text"><?=$text?></textarea>
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="field">
                Field: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="field" id="field">
                    <option value=""></option>
                        <?php foreach ($fields as $field_item) { ?>
                            <option value="<?php echo $field_item['name']; ?>" <? if ($field_item['name'] == $field) echo 'selected' ?>>
                            <?= $field_item['title'] ?>
                            </option>
                        <? } ?>
                </select>
            </div>
        </div>
        
		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
