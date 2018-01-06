<form action="<?= $lang ?>/presets/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT'; else echo 'ADD'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="name">
                Name: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input name="name" id="name" value="<?=$name?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="resolution">
                Resolution: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input name="resolution" id="resolution" value="<?=$resolution?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_bitrate">
                Video bitrate:
            </label>
            <div class="controls">
                <input name="video_bitrate" id="video_bitrate" value="<?=$video_bitrate?>">
            </div>
        </div>
        
		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
