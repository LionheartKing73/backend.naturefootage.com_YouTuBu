<div class="content_padding typography content_bg">
<?if($client):?>


<?if($error):?>
    <p class="message error"><?=$error?></p>
    <?endif;?>
<? if(isset($success)) { ?>
        <p class="message good"><?= $success ?></p>
    <? } else { ?>
    <form  action="<?= $lang . '/bin/email/' . $this->id ?>" method="post">
        <div id="binEdit">
            <table cellspacing="1" cellpadding="4" border="0">
                <tr>
                    <td><?= $this->lang->line('from_name'); ?>: <span class="mand">*</span></td>
                    <td><input type="text" name="fromname" value="<?= $fromname ?>" class="inp"></td>
                </tr>

                <tr>
                    <td><?= $this->lang->line('from_email'); ?>: <span class="mand">*</span></td>
                    <td><input type="text" name="fromemail" value="<?= $fromemail ?>" class="inp"></td>
                </tr>

                <tr>
                    <td><?= $this->lang->line('to_name'); ?>: <span class="mand">*</span></td>
                    <td><input type="text" name="toname" value="<?= $toname ?>" class="inp"></td>
                </tr>

                <tr>
                    <td><?= $this->lang->line('email'); ?>: <span class="mand">*</span></td>
                    <td><input type="text" name="email" value="<?= $email ?>" class="inp"></td>
                </tr>

                <tr>
                    <td><?= $this->lang->line('message'); ?>: <span class="mand">*</span></td>
                    <td><textarea name="message" class="inp" style="width:400px; height:80px;"><?= $message ?></textarea></td>
                </tr>

                <tr>
                    <td></td>
                    <td>
                        <button type="submit" name="send" class="action">
                            <?=$this->lang->line('send')?>
                        </button>
                    </td>
                </tr>
            </table>
        </div>
    </form>
    <? } ?>

<?else:?>
    <?=$this->lang->line('must_register');?> - <a href="<?=$lang?>/register.html"> <?=$this->lang->line('login_register');?></a>.
<?endif;?>
</div>
