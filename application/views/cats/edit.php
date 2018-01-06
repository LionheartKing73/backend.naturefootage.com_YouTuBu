<? if ($id && $this->permissions['cats-items']) { ?>
<strong class="toolbar-item">
	<?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
	<a href="<?=$lang?>/cats/items/<?=$id?>" class="btn">Items</a>
</div>

<br class="clr">
<? } ?>

<form action="<?= $lang ?>/cats/edit/<?=$id?>" method="post" enctype="multipart/form-data" class="form-horizontal well">
	<fieldset>
		<legend>
			<?= $this->lang->line('cats_edit') ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="title">
				<?= $this->lang->line('title') ?>: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="title" id="title" maxlength="255" value="<?=esc($title)?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="ord">
				<?= $this->lang->line('order') ?>:
			</label>
			<div class="controls">
				<input type="text" name="ord" id="ord" maxlength="6" value="<?=$ord?>">
			</div>
		</div>

	<? if ($parents) { ?>
		<div class="control-group">
			<label class="control-label" for="parent_id">
				<?= $this->lang->line('parent') ?>:				
			</label>
			<div class="controls">
				<select name="parent_id" id="parent_id">
					<option value="0">
				<? foreach ($parents as $parent) { ?>
					<option value="<?= $parent['id'] ?>" <? if ($parent['id'] == $parent_id) echo 'selected' ?>>
						<?= $parent['title'] ?>
					</option>
				<? } ?>
				</select>				
			</div>
		</div>
	<? } ?>

        <div class="control-group">
            <label class="control-label" for="meta_title">
                <?= $this->lang->line('meta_title') ?>:
            </label>
            <div class="controls">
                <input type="text" name="meta_title" id="meta_title" maxlength="255" value="<?= $meta_title ?>">
            </div>
        </div>

		<div class="control-group">
			<label class="control-label" for="meta_desc">
				<?= $this->lang->line('meta_desc') ?>:
			</label>
			<div class="controls">
				<textarea name="meta_desc" id="meta_desc"><?= $meta_desc ?></textarea>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="meta_keys">
                <?= $this->lang->line('meta_keys') ?>:
			</label>
			<div class="controls">
				<textarea name="meta_keys" id="meta_keys"><?= $meta_keys ?></textarea>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="code">
				<?= $this->lang->line('assigned_code') ?>:
			</label>
			<div class="controls">
				<input type="text" name="code" id="code" maxlength="255" value="<?= $code ?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="item_code">
				Clip code:
			</label>
			<div class="controls">
				<input type="text" name="item_code" id="item_code" maxlength="50"
					   value="<?= $item_code ?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="mimg">
                <?=$this->lang->line('picture')?>:
            </label>
            <div class="controls">
                <input type="file" name="mimg" id="mimg" class="input-file">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="private">
                Private:
            </label>
            <div class="controls">
                <input type="checkbox" id="private" name="private" <?if($private){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="prepare_downloads">
                Prepare downloads:
            </label>
            <div class="controls">
                <input type="checkbox" id="prepare_downloads" name="prepare_downloads" <?if($prepare_downloads){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="preview_length">
                Preview length:
            </label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="preview_length" id="preview_length"
                           value="<?= ($preview_length ? $preview_length : '') ?>"><span class="add-on">min</span>
                </div>
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

        <?if($picture){?>

        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
                <td align="center">
                    <img src="<?=$picture?>?date=<?=$mtime?>" border="0" style="border:solid 1px #efefef; width: 200px">
                </td>
            </tr>
            <?if (!$item_code) {?>
            <tr>
                <td align="center">
                    <input type="hidden" name="sid" value="<?=$id?>">
                    <input type="submit" value="<?=$this->lang->line('delete')?>" class="btn btn-danger" name="delete">
                </td>
            </tr>
            <?}?>
        </table>

        <?}?>

	</fieldset>
</form>
