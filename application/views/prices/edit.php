<form method="post" class="form-horizontal well">
    <fieldset>
        <legend>
            <?=$action?>
        </legend>

        <div class="control-group">
            <label class="control-label" for="code">
                Code: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="code" id="code" maxlength="3" value="<?=$price['code']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="code">
                Price: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="price" id="price" value="<?=$price['price']?>">
            </div>
        </div>

        <input type="hidden" name="id" value="<?=$id?>" class="field">

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>
</form>
