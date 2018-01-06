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
                Title: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="title" id="title" value="<?=$title?>">
                <input type="hidden" name="id" value="<?=$id?>">
                <input type="hidden" name="list_id" value="<?=$list_id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="content">
                Section Text <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="This is for the section Text in this area, this is for blog style entries"> :
            </label>
            <div class="controls">
                <textarea name="content"><?=$content?></textarea>
                <?=fck(750, 200, 'content')?>
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
