<?if(!$lang) { $lang = 'en'; }?>

<? if($this->session->userdata('client_uid')){?>
    <a href="<?=$lang?>/register/account">
        <?=$this->lang->line('welcome')?> <span class="italic"><?=$client_name?></span>
    </a>
    &nbsp;&nbsp;
    <a href="<?=$lang?>/register/logout">
        <?=$this->lang->line('logout')?>
    </a>
<?} else {?>
    <a href="<?=$lang?>/register/login.html" onclick="loginPopup();return false;"><?=$this->lang->line('sign_in')?></a>
    &nbsp;&nbsp;
    <a href="<?=$lang?>/register.html"><?=$this->lang->line('register')?></a>
<?}?>
