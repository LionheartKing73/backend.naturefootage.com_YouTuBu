<form action="<?php echo $lang ?>/settings_ratings/view" method="post" class="form-horizontal well">
    <fieldset>
        <legend>
            <?php echo 'Edit Ratings Weights'; ?> (<?php echo $this->lang->line('required_fields'); ?> <span class="mand">*</span>):
        </legend>
        <?php
        $array = array(26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40);
        foreach ($sets as $set) {

            if (in_array($set['id'], $array)) {
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



        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?php echo $this->lang->line('save') ?>" name="save">
        </div>

    </fieldset>
</form>
