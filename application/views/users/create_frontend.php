<form action="<?= $lang ?>/users/create_frontend/<?=$provider_id?>" method="post" class="form-horizontal well">
    <fieldset>
        <legend>
            CREATE FRONTEND
        </legend>

        <div class="control-group">
            <label class="control-label" for="name">
                Name: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="name" id="name" value="<?=$name?>">
                <input type="hidden" name="provider_id" value="<?=$provider_id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="host_name">
                Host name: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="host_name" id="host_name" value="<?=$host_name?>">
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>
</form>
