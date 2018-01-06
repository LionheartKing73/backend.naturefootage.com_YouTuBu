<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title><?=$title?></title>
  <base href="<?=$this->config->item('base_url')?>">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="data/css/admin.css" type="text/css">
  <script type="text/javascript">
    window.onload = function() {
      var login = document.getElementById("login");
      if (login) login.focus();
    };
    var isFramed = false;
    try {
        isFramed = window != window.top || document != top.document || self.location != top.location;
    } catch ( e ) {
        isFramed = true;
    }
  </script>
</head>

<body>
<div id="backbody">
    <br><br>
    <div align="center">
        <b><?=$this->lang->line('login');?></b><br><br>
        <?=$this->lang->line('login_message');?><br><br>

        <form action="en/login" method="post">
            <table border="0" cellspacing="0" cellpadding="2" align="center">
                <tr>
                  <td align="center">
                    <a href=""><img src="//cdn.naturefootage.com/wp-content/uploads/2016/02/11183128/logonature1.jpg" width="200"></a>
                  </td>
                </tr>
                <?if($error){?><tr><td class="error" height="30"><?=$error;?></td></tr><?}?>
                <tr><td>

                    <table cellpadding="2" class="tab">
                      <tr><td></td></tr>
                      <tr><td class="tablabel"><?=$this->lang->line('login');?>:</td><td><input type="text" name="login" id="login" size="20" maxlength="100" value="" class="field"></td></tr>
                      <tr><td class="tablabel"><?=$this->lang->line('password');?>:</td><td><input type="password" name="password" size="20" maxlength="20" class="field"></td></tr>
                      <tr><td colspan="2" align="center">
                      <input type="submit" value="<?=$this->lang->line('submit');?>" class="sub" name="enter">&nbsp;<input type="reset" value="<?=$this->lang->line('clear');?>" class="sub"></td></tr>
                    </table>

                </td></tr>
                <tr><td align="center" height="30">&copy; <?=date('Y')?> Bold Endeavours</td></tr>
            </table>
        </form>
    </div>
</div><div id="noauth"></div>
<script type="text/javascript">
    if(isFramed){
        document.getElementsById("backbody").style.display='none';
        document.getElementsById("noauth").innerHTML='We are afraid your session has timed out.<br> Please sign in once again into the system.';
        alert('We are afraid your session has timed out. Please sign in once again into the system.');
        parent.location.reload();
    }
</script>
</body>
</html>