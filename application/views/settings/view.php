<form action="<?php echo $lang ?>/settings/view" method="post" class="form-horizontal well">
    <fieldset>
        <legend>
            <?php echo $this->lang->line('settings_edit'); ?> (<?php echo $this->lang->line('required_fields'); ?> <span class="mand">*</span>):
        </legend>
        <?php
        $array = array(26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40);
        foreach ($sets as $set) {

            if (!in_array($set['id'], $array)) {
                if (!$set['lang'] || $set['q1_preview_preset'] || $set['q2_preview_preset'] || $set['still_browse_preset'] || $set['still_search_preset'])
                    continue;
                ?>
                <div class="control-group">
                    <label class="control-label" for="sets[<?php echo $set['name'] ?>]">
                        <?php echo $set['lang'] ?>: <span class="mand">*</span>
                    </label>
                    <div class="controls">
                        <?php if ($set['checkbox']) { ?>
                            <input type="checkbox" name="sets[<?php echo $set['name'] ?>]" name="sets[<?php echo $set['name'] ?>]" value="1" <?php echo ($set['value']) ? 'checked' : '' ?>>
                        <?php } else { ?>
                            <input type="text" name="sets[<?php echo $set['name'] ?>]" id=name="sets[<?php echo $set['name'] ?>]"
                                   maxlength="255" value="<?php echo $set['value'] ?>" style="width: 300px">
                               <?php } ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <div class="control-group">
            <label class="control-label" for="sets[q1_preview_preset]">
                Q1 Preview Preset: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="sets[q1_preview_preset]" id="sets[q1_preview_preset]">
                    <?php foreach ($presets as $preset) { ?>
                        <option value="<?php echo $preset['id'] ?>"<?php echo $preset['id'] == $q1_preview_preset ? ' selected' : ''; ?>><?php echo $preset['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="sets[q2_preview_preset]">
                Q2 Preview Preset: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="sets[q2_preview_preset]" id="sets[q2_preview_preset]">
                    <?php foreach ($presets as $preset) { ?>
                        <option value="<?php echo $preset['id'] ?>"<?php echo $preset['id'] == $q2_preview_preset ? ' selected' : ''; ?>><?php echo $preset['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="sets[still_browse_preset]">
                Still Browse Preset: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="sets[still_browse_preset]" id="sets[still_browse_preset]">
                    <?php foreach ($presets as $preset) { ?>
                        <option value="<?php echo $preset['id'] ?>"<?php echo $preset['id'] == $still_browse_preset ? ' selected' : ''; ?>><?php echo $preset['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="sets[still_search_preset]">
                Still Search Preset: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="sets[still_search_preset]" id="sets[still_search_preset]">
                    <?php foreach ($presets as $preset) { ?>
                        <option value="<?php echo $preset['id'] ?>"<?php echo $preset['id'] == $still_search_preset ? ' selected' : ''; ?>><?php echo $preset['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?php echo $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>
</form>
