<form action="<?= $lang ?>/sharedpages/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT PAGE'; else echo 'ADD PAGE'; ?>
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
            <label class="control-label" for="url">
                URL:
            </label>
            <div class="controls">
                <input type="text" name="url" id="url" value="<?=$url?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_title">
                Meta title:
            </label>
            <div class="controls">
                <input type="text" name="meta_title" id="meta_title" value="<?=$meta_title?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_keywords">
                Meta keywords:
            </label>
            <div class="controls">
                <input type="text" name="meta_keywords" id="meta_keywords" value="<?=$meta_keywords?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_description">
                Meta description:
            </label>
            <div class="controls">
                <textarea name="meta_description" id="meta_description"><?=$meta_description?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="type">
                Type:
            </label>
            <div class="controls">
                <input type="text" name="type" id="type" value="<?=$type?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="body">
                Content:
            </label>
            <div class="controls">
                <textarea name="body" class="body" id="body"><?=$body?></textarea>
                <?=tiny()?>
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

        <div class="control-group">
            <label class="control-label" for="status">
                Status
            </label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="status" value="1"<?if($status == 1 || !isset($status)) echo ' checked';?>>Active
                </label>
                <label class="radio inline">
                    <input type="radio" name="status" value="0"<?if(isset($status) && $status == 0) echo ' checked';?>>Inactive
                </label>
            </div>
        </div>


		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
