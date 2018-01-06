<form action="<?= $lang ?>/galleries/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT GALLERY'; else echo 'ADD GALLERY'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="title">
                Title: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="title" id="title" value="<?=$title?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="code">
                Code:
            </label>
            <div class="controls">
                <input type="text" name="code" id="code" value="<?=$code?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="featured">
                Featured:
            </label>
            <div class="controls">
                <input type="checkbox" id="featured" name="featured" <?if($featured){?>checked="checked"<?}?>>
            </div>
        </div>

        <?php if($clips) { ?>
        <div class="control-group">
            <label class="control-label">
                Preview:
            </label>
            <div class="controls">
                <?php foreach($clips as $clip) { ?>
                    <label class="radio inline">
                        <input type="radio" name="preview_clip_id" value="<?php echo $clip['id']; ?>"<?php if($preview_clip_id == $clip['id']) echo ' checked="checked"'; ?>>
                        <?php if($clip['thumb']) { ?>
                            <img src="<?php echo $clip['thumb']; ?>" width="70">
                        <?php } ?>
                        <?php echo $clip['code']; ?>
                    </label>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
