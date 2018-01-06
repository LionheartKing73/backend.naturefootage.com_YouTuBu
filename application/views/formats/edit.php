<form action="<?= $lang ?>/formats/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT FORMAT'; else echo 'ADD FORMAT'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="format">
                Format: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="format" id="format" value="<?=$format?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <!--
        <div class="control-group">
            <label class="control-label" for="price_level">
                Price level: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="price_level" id="price_level" value="<?=$price_level?>">
            </div>
        </div>-->

        <!--
        <div class="control-group">
            <label class="control-label" for="type">
                Type:
            </label>
            <div class="controls">
                <input type="text" name="type" id="type" value="<?=$type?>">
            </div>
        </div>-->

        <div class="control-group">
            <label class="control-label" for="master">
                Master:
            </label>
            <div class="controls">
                <input type="checkbox" id="master" name="master" <?if($master){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="camera">
                Source:
            </label>
            <div class="controls">
                <input type="checkbox" id="camera" name="camera" <?if($camera){?>checked="checked"<?}?>>
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
