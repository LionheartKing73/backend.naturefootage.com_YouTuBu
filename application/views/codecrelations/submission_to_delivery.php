<style type="text/css">
    .control-label {
        width: 340px !important;
        text-align: left !important;
    }
</style>

<form action="<?= $lang ?>/codecrelations/submission_to_delivery" method="post" class="form-horizontal well">
    <fieldset>
        <legend>
            EDIT CODEC RELATIONS
        </legend>

<?php foreach ( $submissions as $submission ) { ?>

        <div class="control-group">
            <label class="control-label" for="format">
                Submission Codec: <strong><?php echo $submission[ 'name' ]; ?></strong>
            </label>
            <div class="controls">
                <select name="relations[<?php echo $submission[ 'id' ]; ?>]">
                    <option value="">
                        -- no added --
                    </option>

                <?php foreach ( $deliveries as $delivery ) { ?>

                    <option value="<?php echo $delivery[ 'id' ]; ?>"<?php if ( $relations[ $submission[ 'id' ] ][ 'delivery_id' ] === $delivery[ 'id' ] ) echo " selected"; ?>>
                        <?php echo $delivery[ 'id' ]; ?> :: <?php echo $delivery[ 'description' ]; ?>
                    </option>

                <?php } ?>

                </select>
            </div>
        </div>

<?php } ?>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>
    </fieldset>
</form>
