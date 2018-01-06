<div id="loginPopup" class="popup">
    <p class="message error" style="display: none;"></p>
    <form name="auth" id="auth" method="post" onsubmit="loginUser(); return false;">
        <input type="hidden" name="enter" value="1">
        <label>
            <?=$this->lang->line('username')?>
        </label>
        <div class="field">
            <input type="text" name="login" id="login" value="">
        </div>
        <label>
            <?=$this->lang->line('password')?>
        </label>
        <div class="field">
            <input type="password" name="password" id="password" value="">
        </div>
        <div class="actions">
            <button type="submit" class="action" style="margin-top: 10px"><?=$this->lang->line('login')?></button>
        </div>
    </form>
</div>