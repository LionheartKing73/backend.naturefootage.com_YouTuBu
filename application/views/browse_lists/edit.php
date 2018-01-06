<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['browselists-items']){?>
        <a href="<?=$lang?>/browselists/items<?='/'.$id?>" class="btn">Items</a>
    <? } ?>
</div>

<br class="clr">

<form action="<?= $lang ?>/browselists/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT LIST'; else echo 'ADD LIST'; ?>
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

        <div class="control-group">
            <label class="control-label" for="type">
                Section Type:
            </label>
            <div class="controls">
                <select id="type" name="type">
                    <option value="cliplist"<?php if($type == 'cliplist') echo ' selected'; ?>>Graphical Clip list</option>
                    <option value="linklist"<?php if($type == 'linklist') echo ' selected'; ?>>Text Link List</option>
                    <option value="textarea"<?php if($type == 'textarea') echo ' selected'; ?>>Blog style Text Area for graphics and text</option>
                </select>
            </div>
        </div>

        <? if($is_admin && $providers) { ?>
            <div class="control-group">
                <label class="control-label" for="provider_id">
                    Provider
                </label>
                <div class="controls">
                    <select name="provider_id" id="provider_id">
                        <option value="0"></option>
                        <? foreach ($providers as $item) { ?>
                            <option value="<?=$item['id']?>"<?if ($item['id'] == $provider_id) echo ' selected'?>>
                                <?= $item['fname'] . ' ' . $item['lname']; ?>
                            </option>
                        <?}?>
                    </select>
                </div>
            </div>
        <? } ?>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
