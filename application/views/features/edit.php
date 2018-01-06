<form method="post" enctype="multipart/form-data" class="form-horizontal well">
    <fieldset>
        <legend>
            <?= $this->lang->line('features_edit') ?>
        </legend>

        <div class="control-group">
            <label class="control-label" for="title">
                <?= $this->lang->line('title') ?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="name" id="title" maxlength="255" value="<?=esc($feature['name'])?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="description">
                <?= $this->lang->line('description') ?>:
            </label>
            <div class="controls">
                <textarea name="description" id="description" rows="4" cols="70"><?=esc($feature['description'])?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="code">
                Media code:
            </label>
            <div class="controls">
                <input type="text" name="code" value="<?=$feature['code']?>" id="code">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="link">
                Link:
            </label>
            <div class="controls">
                <input type="text" name="link" maxlength="255" value="<?=$feature['link']?>" id="link">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ord">
                Order:
            </label>
            <div class="controls">
                <input type="text" name="ord" id="ord" maxlength="6" value="<?=$feature['ord']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="image">
                Image:
            </label>
            <div class="controls">
                <input type="file" name="image" id="image" class="input-file">

                <?if($feature['resource']){?>

                <table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                        <td align="center">
                            <img width="150" src="<?=$this->config->item('features_path') . $feature['id'] . '.' . $feature['resource'] . '?date=' . $feature['mtime']?>">
                            <label>
                                <input type="checkbox" name="remove_image"> Remove image
                            </label>
                        </td>
                    </tr>
                </table>

                <?}?>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>
</form>