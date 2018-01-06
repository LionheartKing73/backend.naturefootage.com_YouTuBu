<div class="content_padding content_bg">
<?if($error):?><p class="message error"><?=$error?></p><?endif;?>
<?if($message):?><p class="message good"><?=$message?></p>

<?else:?>

<?if($id): ?>
    <?if($menu) echo $menu;?>
    <?endif;?>
<div class="typography">
    <form action="<?=$lang?>/<?=$action?>" method="post" id="register_form">
        <?if(!$id): ?>
        <p><?=$this->lang->line('register_info')?></p>
        <?endif;?>
        <table cellspacing="0" cellpadding="5" border="0">
            <tr>
                <td valign="top">

                    <table cellspacing="3" cellpadding="5" border="0" style="width: 450px;">
                        <tr>
                            <td width="160"><?=$this->lang->line('fname')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="fname" value="<?=$fname?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('lname')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="lname" value="<?=$lname?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('company')?>: </td>
                            <td><input type="text" name="company" value="<?=$company?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('position')?>: </td>
                            <td><input type="text" name="position" value="<?=$position?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('address')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="address" value="<?=$address?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('city')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="city" value="<?=$city?>" class="field" style="width:85px"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('state')?>:</td>
                            <td><input type="text" name="state" value="<?=$state?>" maxlength="2"
                                       class="field" style="width:85px"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('postcode')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="postcode" value="<?=$postcode?>" class="field" style="width:85px"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('country')?>: <span class="mand">*</span></td>
                            <td>
                                <? if($id): ?>
                                <?=$country_name?>
                                <input type="hidden" name="country_id" value="<?=$country_id?>" class="field">
                                <? else: ?>
                                <select name="country_id">
                                    <?foreach($countries as $country):?>
                                    <option value="<?=$country['id']?>" <?if($country_id==$country['id']) echo "selected"?>><?=$country['name']?></option>
                                    <?endforeach;?>
                                </select>
                                <? endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('vat')?>:</td>
                            <td><input type="text" name="vat_no" value="<?=$vat_no?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('phone')?>: </td>
                            <td><input type="text" name="phone" value="<?=$phone?>" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('email')?>: <span class="mand">*</span></td>
                            <td><input type="text" name="email" value="<?=$email?>" class="field"></td>
                        </tr>

                    </table>
                </td>

                <td>&nbsp;&nbsp;</td>

                <td valign="top">

                    <table cellspacing="3" cellpadding="1" border="0">
                        <tr>
                            <td width="160"><?=$this->lang->line('login')?>: <span class="mand">*</span></td>
                            <td>
                                <? if($id): ?>
                                <?=$login?>
                                <input type="hidden" name="login" value="<?=$login?>" class="field">
                                <? else: ?>
                                <input type="text" name="login" value="<?=$login?>" class="field">
                                <? endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('password')?>: <span class="mand">*</span></td>
                            <td><input type="password" name="pass" class="field"></td>
                        </tr>

                        <tr>
                            <td><?=$this->lang->line('confirm')?>: <span class="mand">*</span></td>
                            <td><input type="password" name="pass2" class="field"></td>
                        </tr>

                        <tr><td colspan="2" height="20">
                            <?=$this->lang->line('apply_corporate')?> <input type="checkbox" name="want_corp" value="1" <?if($want_corp) echo "checked";?> onclick="$('#warning').toggle();">
                        </td></tr>
                    </table>

                    <table cellspacing="3" cellpadding="1" border="0">
                        <tr id="warning"><td colspan="2">
                            <?=$this->lang->line('auth_corp_account')?>
                        </td></tr>

                        <tr><td colspan="2" height="20">
                            <?=$this->lang->line('newsletter_signup')?> <input type="checkbox" name="want_subs" value="1" <?if($want_subs) echo "checked";?>>
                        </td></tr>

                        <tr><td colspan="2" height="30"> <br>
                            <i><?=$this->lang->line('keeping_details')?></i><br><br>
                        </td></tr>

                        <tr><td colspan="2" height="30" class="mand">
                            <?=$this->lang->line('register_conditions')?>
                        </td></tr>
                    </table>

                </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td>
                    <button type="submit" name="register" value="1" class="action">
                        <?if($id) {?>
                        <?=$this->lang->line('save')?>
                        <? } else {?>
                        <?=$this->lang->line('register')?>
                        <?}?>
                    </button>
                </td>
            </tr>

        </table>
        <? if ($id) echo '<input type="hidden" name="id" value="'.$id.'" class="field">'; ?>
    </form>
</div>
<?endif;?>
<div>
