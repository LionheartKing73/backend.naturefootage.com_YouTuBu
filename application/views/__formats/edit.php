<form action="<?=$lang?>/formats/<?=$type?>/edit/<?=$this->id?>" method="post" class="form-horizontal well">

    <fieldset>
        <legend>
            <?=$this->lang->line('formats_edit');?>
        </legend>

        <div class="control-group">
            <label class="control-label" for="title">
                <?=$this->lang->line('title');?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="title" id="title" maxlength="255" value="<?=$title?>">
                <input type="hidden" name="type" value="<?=$type?>">
            </div>
        </div>

        <?if ($type == 'delivery') {?>

        <div class="control-group">
            <label class="control-label" for="res">
                Resolution:
            </label>
            <div class="controls">
                <select name="res" id="res">
                    <option value="1"<?if ($res==1) echo ' selected="selected"'?>>High</option>
                    <option value="2"<?if ($res==2) echo ' selected="selected"'?>>Medium</option>
                    <option value="3"<?if ($res==3) echo ' selected="selected"'?>>Low</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="filetype">
                File type:
            </label>
            <div class="controls">
                <input type="text" name="filetype" id="filetype" maxlength="8" value="<?=$filetype?>" class="input-small">
            </div>
        </div>

        <?}?>

        <div class="control-group">
            <label class="control-label" for="code">
                Code:
            </label>
            <div class="controls">
                <input type="text" name="code" id="code" value="<?=$code?>">
            </div>
        </div>

        <?if ($type == 'original') {?>
            <div class="control-group">
                <label class="control-label" for="hd_sd">
                    HD or SD:
                </label>
                <div class="controls">
                    <select name="hd_sd" id="hd_sd">
                        <option value="1"<?if ($hd_sd==1) echo ' selected="selected"'?>>HD</option>
                        <option value="2"<?if ($hd_sd==2) echo ' selected="selected"'?>>SD</option>
                    </select>
                </div>
            </div>
        <?}?>

        <?if ($type == 'delivery' || $type == 'original') {?>
            <div class="control-group">
                <label class="control-label">
                    Frame size:
                </label>
                <div class="controls">
                    <input type="text" name="width" maxlength="4" value="<?=$width?>" class="input-small" placeholder="Width"> x
                    <input type="text" name="height" maxlength="4" value="<?=$height?>" class="input-small" placeholder="Height">
                </div>
            </div>
        <?}?>

        <?if ($type == 'original') {?>

        <div class="control-group">
            <label class="control-label" for="hr_id">
                High resolution delivery format:
            </label>
            <div class="controls">
                <select name="hr_id" id="hr_id">
                    <? foreach ($hrs as $hr) { ?>
                    <option value="<?=$hr['id']?>"<?if ($hr_id==$hr['id']) echo ' selected="selected"'?>>
                        <?=$hr['title']?>
                    </option>
                    <?}?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                Enabled delivery formats:
            </label>
            <div class="controls">
                <? foreach ($dfs as $df) { ?>
                <label>
                    <input type="checkbox" name="df[]" value="<?=$df['id']?>"<?if ($df['enabled']) echo ' checked="checked"'?> />
                    <?=$df['title']?>
                </label>
                <input type="hidden" name="all_df[]" value="<?=$df['id']?>" />
                <br />
                <?}?>
            </div>
        </div>

        <?}?>

        <?if ($type == 'delivery') {?>

            <div class="control-group">
                <label class="control-label" for="delivery_method">
                    Delivery method:
                </label>
                <div class="controls">
                    <select name="delivery_method" id="delivery_method">
                    <? foreach ($delivery_methods as $method) { ?>
                        <option value="<?=$method['id']?>"<? if($delivery_method == $method['id']) echo ' selected="selected"' ?>><?=$method['name']?></option>
                    <?}?>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="price_id">
                    Price:
                </label>
                <div class="controls">
                    <select name="price_id" id="price_id">
                        <?foreach ($prices as $price) {?>
                        <option value="<?=$price['id']?>"<?if ($price_id==$price['id']) echo ' selected="selected"'?>>
                            <?=$price['code']?>
                        </option>
                        <?}?>
                    </select>
                </div>
            </div>

        <?}?>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>

</form>
