<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['browselists-items']){?>
        <a href="<?=$lang?>/browselists/items<?='/'.$list_id?>" class="btn">List items</a>
    <? } ?>
</div>

<br class="clr">

<form action="<?= $lang ?>/browselistitems/edit/<?=$list_id.'/'.$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT ITEM'; else echo 'ADD ITEM'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="title">
                Clip Title: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="title" id="title" value="<?=$title?>">
                <input type="hidden" name="id" value="<?=$id?>">
                <input type="hidden" name="list_id" value="<?=$list_id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="clip_id">
                Clip ID:
            </label>
            <div class="controls">
                <input type="text" name="clip_id" id="clip_id" value="<?=$clip_id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="description">
                Clip Text:
            </label>
            <div class="controls">
                <textarea name="description"><?=$description?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="url">
                URL <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="Destination URL"> :
            </label>
            <div class="controls">
                <input type="text" name="url" id="url" value="<?=$url?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="url_type">
                URL Type:
            </label>
            <div class="controls">
                <select id="url_type" name="url_type">
                    <option value="regular"<?php if($type == 'regular') echo ' selected'; ?>>Regular</option>
                    <option value="linklist"<?php if($type == 'popup') echo ' selected'; ?>>Popup</option>
                    <option value="textarea"<?php if($type == 'new window') echo ' selected'; ?>>New Window</option>
                </select>
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
