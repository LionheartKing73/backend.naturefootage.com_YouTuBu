<form method="post" enctype="multipart/form-data" class="form-horizontal well">

    <fieldset>
        <legend>
            <?=$this->lang->line('banner_edit')?>:
        </legend>

        <div class="control-group">
            <label class="control-label" for="ord">
                <?=$this->lang->line('order')?>:
            </label>
            <div class="controls">
                <input type="text" name="ord" id="ord" value="<?=$banner['ord']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="name">
                <?=$this->lang->line('title')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="name" id="name" value="<?=$banner['name']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                <?=$this->lang->line('type')?>:
            </label>
            <div class="controls">
                <?=$banner['type']?>
                &nbsp;&nbsp;
                <?if ($banner['file_exists']){?>+<?}else{?>file is absent!<?}?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="clip">
                <?=$this->lang->line('file')?>:
            </label>
            <div class="controls">
                <input type="file" name="clip" id="clip" class="input-file">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="resource">
                <?=$this->lang->line('file_name')?>:
            </label>
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on">/data/upload/tb/</span><input class="input-small" type="text" name="resource" id="resource" value="<?=$banner['resource']?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
        </div>

    </fieldset>

</form>