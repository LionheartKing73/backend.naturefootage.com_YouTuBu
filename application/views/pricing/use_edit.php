<form action="<?= $lang ?>/pricinguse/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT USE'; else echo 'ADD USE'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="category">
                Category: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="category" id="category" value="<?=$category?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="use">
                Use: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="use" id="use" value="<?=$use?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
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

        <? if($is_admin) { ?>
        <div class="control-group">
            <label class="control-label" for="admin_only">
                Admin only:
            </label>
            <div class="controls">
                <input type="checkbox" id="admin_only" name="admin_only" <?if($admin_only){?>checked="checked"<?}?>>
            </div>
        </div>
        <? } ?>

        <div class="control-group">
            <label class="control-label" for="display">
                Display:
            </label>
            <div class="controls">
                <input type="checkbox" id="display" name="display" <?if($display){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="terms_cat">
                Terms cat: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="terms_cat" id="terms_cat" value="<?=$terms_cat?>" maxlength="5" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="exclusions">
                Exclusions:
            </label>
            <div class="controls">
                <input type="text" name="exclusions" id="exclusions" value="<?=$exclusions?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="description">
                Description: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="description" id="description" value="<?=$description?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="clip_minimum">
                Clip minimum:
            </label>
            <div class="controls">
                <input type="text" name="clip_minimum" id="clip_minimum" value="<?=$clip_minimum?>" <? if(!$is_admin) echo 'readonly="readonly"'; ?>>
            </div>
        </div>

        <!--
        <div class="control-group">
            <label class="control-label" for="price_level_category">
                Price level category: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="price_level_category" id="price_level_category" value="<?=$price_level_category?>">
            </div>
        </div>-->

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
