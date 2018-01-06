<form action="<?= $lang ?>/pricingrfprice/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT RF PRICE'; else echo 'ADD RF PRICE'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="license">
                License: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="license" id="license" value="<?=$license?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="terms">
                Terms: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="terms" id="terms" value="<?=$terms?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="budgete_rate">
                Budget: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="budgete_rate" id="budgete_rate" value="<?=$budgete_rate?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="standard_rate">
                Standard: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="standard_rate" id="standard_rate" value="<?=$standard_rate?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="premium_rate">
                Premium: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="premium_rate" id="premium_rate" value="<?=$premium_rate?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="exclusive_rate">
                Exclusive: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="exclusive_rate" id="exclusive_rate" value="<?=$exclusive_rate?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="discount_display">
                Discount display:
            </label>
            <div class="controls">
                <select name="discount_display" id="discount_display" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
                    <?foreach($discount_display_types as $type):?>
                        <option value="<?=$type?>" <?if($discount_display == $type) echo 'selected';?>><?=$type?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>

        <div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
