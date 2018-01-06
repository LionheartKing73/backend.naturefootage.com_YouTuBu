<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?= $title ?></title>
        <base href="<?=$this->config->item('base_url')?>">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="stylesheet" type="text/css" href="data/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="data/css/admin_bootstrap.css">
        <link rel="stylesheet" type="text/css" href="data/css/calendar.css">
        <link rel="stylesheet" type="text/css" href="data/css/jquery-ui.css" media="screen">
        <script type="text/javascript" src="data/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="data/js/jquery.i18n.properties-min-1.0.9.js"></script>
        <script type="text/javascript" src="data/js/jquery-ui.js"></script>
        <script type="text/javascript" src="data/js/swfobject.js"></script>
        <script type="text/javascript" src="data/js/AC_QuickTime.js"></script>
        <script type="text/javascript" src="data/js/script.js"></script>
        <script type="text/javascript" src="data/js/admin.js"></script>
        <script type="text/javascript" src="data/js/calendar.js"></script>
    </head>

    <body id="admin">

        <table cellpadding="0" cellspacing="0" width="100%" border="0">
            <tr>
                <td width="210" height="65" align="center"><a href="<?= $lang ?>/login"><img src="data/img/admin/adminlogo.gif" alt=""></a></td>
                <td width="1" bgcolor="#efefef"></td>
                <td style="padding-right: 5px; text-align: right; vertical-align: top">
                    <b><?=$this->lang->line('logged_as')?>:</b>
                    <?=$admin?>
                    <br>
                    <a href="<?= $lang ?>/login/index/logout">
                      <?=$this->lang->line('logout')?>
                    </a>
                    <br><br>
                    <?=$langs?>
                <? if ($path) {?>
                    <ul class="breadcrumb">
                        <li><?=$path?></li>
                    </ul>
                <?}?>
                </td>
            </tr>

            <tr id="blue_row">
                <td>
                    <a href="<?= $lang ?>/login">about</a> |
                    <a href="<?= $lang ?>/help">help</a> |
                    <a href="<?= $lang ?>/login/index/logout">logout</a>
                </td>
                <td class="delimiter"></td>
                <td></td>
            </tr>

            <tr id="green_row">
                <td>&nbsp;</td>
                <td class="delimiter"></td>
                <td>
                  <?if($error){?><p class="mand"><?=$error?></p><?}?>
                  <?if($message){?><p class="message"><?=$message?></p><?}?>
                </td>
            </tr> 
        </table>

        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="210" valign="top">
                    <?= $menu; ?>
                </td>
                <td width="1" bgcolor="#efefef"></td>
                <td valign="top" style="padding: 5px">
                <?if ($pagination) {?>
                    <div class="pagination">
                      <?=$pagination?>
                    </div>
                <?}?>
                    <?=$content?>
                <?if ($pagination) {?>
                    <div class="pagination">
                      <?=$pagination?>
                    </div>
                <?}?>
                </td>
            </tr>
            <tr>
                <td height="15"></td>
                <td bgcolor="#efefef"></td>
                <td></td>
            </tr>
            <tr>
                <td height="1" bgcolor="#efefef"></td>
                <td bgcolor="#efefef"></td>
                <td bgcolor="#efefef"></td>
            </tr>
        </table>

    </body>
</html>