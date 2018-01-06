<form action="<?=$lang?>/users/edit<?='/'.$id?>" method="post" class="form-horizontal well">
<div class="row">
    <fieldset>
        <legend>
            <?=$this->lang->line('users_edit')?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
        </legend>

        <div class="span6">

        <div class="control-group">
            <label class="control-label" for="fname">
                <?=$this->lang->line('fname')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="fname" id="fname" maxlength="200" value="<?=$fname?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="lname">
                <?=$this->lang->line('lname')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="lname" id="lname" maxlength="200" value="<?=$lname?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="prefix">
                Prefix
            </label>
            <div class="controls">
                <input type="text" name="prefix" id="prefix" value="<?=$prefix?>">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="zoho_id">
                Zoho ID:
            </label>
            <div class="controls">
                <input type="text" name="meta[zoho_id]" id="referral" value="<?=$meta['zoho_id']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="group_id">
                <?=$this->lang->line('groups')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="group_id" id="group_id">
                    <option value="0">
                        <?foreach($groups as $group):?>
                    <option value="<?=$group['id']?>"<?if($group['id']==$group_id) echo ' selected'?>>
                        <?=$group['title']?>
                    </option>
                    <?endforeach?>
                </select>
            </div>
        </div>

        <!--<div class="control-group">
            <label class="control-label" for="country_id">
                <?=$this->lang->line('country')?>:
            </label>
            <div class="controls">
                <select name="country_id" id="country_id">
                    <option value="0">--</option>
                    <? foreach ($countries as $country) { ?>
                        <option value="<?=$country['id']?>"<?if($country['id']==$country_id){?> selected="selected"<?}?>>
                            <?=$country['name']?>
                        </option>
                    <?}?>
                </select>
            </div>
        </div>-->


        <div class="control-group">
            <label class="control-label" for="email">
                <?=$this->lang->line('email')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="email" id="email" maxlength="255" value="<?=$email?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="login">
                <?=$this->lang->line('login')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="login" id="login" maxlength="255" value="<?=$login?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="password">
                <?=$this->lang->line('password')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="password" id="password" maxlength="255" value="<?=$password?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="exclusive">
                Exclusive:
            </label>
            <div class="controls">
                <input type="checkbox" name="exclusive" id="exclusive" value="1"<?if($exclusive){?> checked="checked"<?}?>>
            </div>
        </div>
        <?php if($hdvideos['hdvideochoice']=='forselectiveusers') { ?>
        <div class="control-group">
            <label class="control-label" for="storage_account">
                Enable HD Video Download:
            </label>
            <div class="controls">
                <input type="checkbox" name="enable_hdvideo" id="enable_hdvideo" value="1"<?if($enable_hdvideo==1){?> checked="checked"<?}?>>
            </div>
        </div>
        <?php }?>
        <div class="control-group">
            <label class="control-label" for="storage_account">
                Enable Upload/Download Account:
            </label>
            <div class="controls">
                <input type="checkbox" name="storage_account" id="storage_account" value="1"<?if($storage_account){?> checked="checked"<?}?>>
            </div>
        </div>

</div>
<div class="span6">
        <div class="control-group">
            <label class="control-label" for="company_name">
                Company Name:
            </label>
            <div class="controls">
                <input type="text" name="meta[company_name]" id="company_name" value="<?=$meta['company_name']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="phone">
                Phone:
            </label>
            <div class="controls">
                <input type="text" name="meta[phone]" id="phone" value="<?=$meta['phone']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="country">
                Country:
            </label>
            <div class="controls">
                <input type="text" name="meta[country]" id="country" value="<?=$meta['country']?>">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="description">
                Bio:
            </label>
            <div class="controls">
                <input type="text" name="meta[description]" id="description" value="<?=$meta['description']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="site">
                Site:
            </label>
            <div class="controls">
                <input type="text" name="site" id="site" value="<?=$site?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="primary_interest">
                Primary Interest:
            </label>
            <div class="controls">
                <input type="text" name="meta[primary_interest]" id="primary_interest" value="<?=$meta['primary_interest']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="referral">
                Referral:
            </label>
            <div class="controls">
                <input type="text" name="meta[referral]" id="referral" value="<?=$meta['referral']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="frontend_url">
                Frontend Domain:
            </label>
            <div class="controls">
                <input type="text" name="meta[frontend_url]" id="referral" value="<?=$meta['frontend_url']?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="provider_credits">
                Provider Credits:
            </label>
            <div class="controls">
             <input type="text" name="provider_credits" id="provider_credits" maxlength="255" value="<?=$provider_credits?>">
            </div>
        </div>
        
</div>
</div>
<div class="row">
    <div class="span6">
        <input type="button" class="btn" name="billing_same_license" id="billing_same_license" value="Billing Same as Licensee" onclick="make_billing_as_license()">
    </div>
</div>
<div class="row">
    <div class="span6">
        <h4>Licensee Information:</h4>
        <div class="control-group">
            <label class="control-label" for="lic_name">
                Name:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_name]" id="lic_name" value="<?=$meta['lic_name']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_company">
                Company (if applicable):
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_company]" id="lic_company" value="<?=$meta['lic_company']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_company">
                Street:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_street1]" id="lic_street1" value="<?=$meta['lic_street1']?>">
            </div>
            <div class="controls">
                <input type="text" name="meta[lic_street2]" id="lic_street2" value="<?=$meta['lic_street2']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_city">
                City:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_city]" id="lic_city" value="<?=$meta['lic_city']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_state">
                State or Province:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_state]" id="lic_state" value="<?=$meta['lic_state']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_zip">
                Postal Code/Zip Code:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_zip]" id="lic_zip" value="<?=$meta['lic_zip']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_country">
                Country:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_country]" id="lic_country" value="<?=$meta['lic_country']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="lic_phone">
                Phone:
            </label>
            <div class="controls">
                <input type="text" name="meta[lic_phone]" id="lic_phone" value="<?=$meta['lic_phone']?>">
            </div>
        </div>
    </div>
    <div class="span6">
        <h4>Billing Information:</h4>
        <div class="control-group">
            <label class="control-label" for="bill_name">
                Name:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_name]" id="bill_name" value="<?=$meta['bill_name']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_company">
                Company (if applicable):
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_company]" id="bill_company" value="<?=$meta['bill_company']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_company">
                Street:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_street1]" id="bill_street1" value="<?=$meta['bill_street1']?>">
            </div>
            <div class="controls">
                <input type="text" name="meta[bill_street2]" id="bill_street2" value="<?=$meta['bill_street2']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_city">
                City:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_city]" id="bill_city" value="<?=$meta['bill_city']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_state">
                State or Province:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_state]" id="bill_state" value="<?=$meta['bill_state']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_zip">
                Postal Code/Zip Code:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_zip]" id="bill_zip" value="<?=$meta['bill_zip']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_country">
                Country:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_country]" id="bill_country" value="<?=$meta['bill_country']?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bill_phone">
                Phone:
            </label>
            <div class="controls">
                <input type="text" name="meta[bill_phone]" id="bill_phone" value="<?=$meta['bill_phone']?>">
            </div>
        </div>
    </div>
</div>
        <?php foreach($meta_map as $meta_key => $meta_label) { ?>
            <?php if(isset($meta[$meta_key])) { ?>
                <div class="control-group">
                    <label class="control-label" for="<?php echo $meta_key; ?>">
                        <?php echo $meta_label; ?>
                    </label>
                    <div class="controls">
                        <input type="text" name="meta[<?php echo $meta_key; ?>]" id="<?php echo $meta_key; ?>" value="<?php echo $meta[$meta_key]; ?>">
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
        </div>
    </fieldset>

</form>
