<?php
if ($path)
    $title = end(explode('/', $path));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Footage Search <?= $title ?></title>
        <base href="<?=$this->config->item('base_url')?>">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link rel="stylesheet" type="text/css" href="data/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="data/css/admin_bootstrap.css">
        <link rel="stylesheet" type="text/css" href="data/css/calendar.css">
        <link rel="stylesheet" type="text/css" href="data/css/jquery-ui.css" media="screen">
        <script type="text/javascript" src="data/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="data/js/jquery-migrate-1.2.1.js"></script>
        <script type="text/javascript" src="data/js/jquery.i18n.properties-min-1.0.9.js"></script>
        <script type="text/javascript" src="data/js/jquery-ui.js"></script>
        <script type="text/javascript" src="data/js/jquery.cookie.js"></script>
        <script type="text/javascript" src="data/js/swfobject.js"></script>
        <script type="text/javascript" src="data/js/AC_QuickTime.js"></script>
        <script type="text/javascript" src="data/js/script.js"></script>
        <script type="text/javascript" src="data/js/admin.js"></script>
        <script type="text/javascript" src="data/js/calendar.js"></script>
        <link rel="stylesheet" type="text/css" href="data/datepicker/css/datepicker.css">
        <script type="text/javascript" src="data/datepicker/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="/data/js/jquery.fixedheadertable.min.js"></script>
        <script type="text/javascript" src="data/js/colpick/js/colpick.js" ></script>
        <script type="text/javascript" src="data/js/jquery.stickytableheaders.min.js" ></script>
        <link rel="stylesheet" href="/data/js/colpick/css/colpick.css" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="/data/css/fixedHeaderTable.css">
        <script type="text/javascript" src="/data/js/date.min.js"></script>
        <link rel="stylesheet" href="/data/css/admin.css?<?php echo date('Ymdhi'); ?>">
        <?php if ($add_js) { ?>
            <?php if (is_array($add_js)) { ?>
                <?php foreach ($add_js as $js) { ?>
                    <script type="text/javascript" src="<?php echo $js; ?>"></script><?php echo PHP_EOL; ?>
                <?php } ?>
            <?php } ?>
        <?php } //else { ?>
            <!--<script type="text/javascript" src="<?php echo $add_js; ?>"></script><?php echo PHP_EOL; ?>-->
        <?php //} ?>
        
    </head>

    <body id="admin" >
        <?php //if(!$is_provider && !isset($_REQUEST['modal'])) { ?>
        <!--        <h2 class="admin-name">Footage Search</h2>-->

		
        <div id="header">
            <div class="logo">

                <a href="/login">
                    <img id="logo" src="//cdn.naturefootage.com/wp-content/uploads/2016/02/11183128/logonature1.jpg" alt="Nature Footage">
                </a>
            </div>
            <!--            <div class="admin-auth">
            <?= $this->lang->line('logged_as') ?>:
            <?= $admin ?>
                            <a href="<?= $lang ?>/login/index/logout">
            <?= $this->lang->line('logout') ?>
                            </a>
                        </div>-->
        </div>


        <div class="clearfix"></div>
        <?php //} ?>

        <div>
            <?if($error){?><p class="mand"><?= $error ?></p><?}?>
            <?if($message){?><p class="message"><?= $message ?></p><?}?>
        </div>

        <div>
            <?php if (!$is_provider && !isset($_REQUEST['modal'])) { ?>
                <?= $menu; ?>
            <?php } else { ?>
            <?php $this->groups_model->get_provider_group_id();?>
                <div class="admin-nav-cont">
           
                   <!--<ul class="admin-nav">
                      <li> <img src="data/img/admin/menu/tab.gif" width="16" height="16" alt=""> Clips section
                        <ul>
                          <li> <a href="en/cliplog/view.html">ClipLog</a> </li>
                        </ul>
                      </li>
                      <li> <img src="data/img/admin/menu/users.gif" width="16" height="16" alt=""> Account
                        <ul>
                          <li> <a href="http://www.nfstage.com/" target="_blank"> Main Site </a> </li>
                          <li> <a href="<?= $lang ?>/login/index/logout">
                            <?= $this->lang->line('logout') ?>
                            </a> </li>
                        </ul>
                      </li>
                    </ul>-->
            		
                    <?php 
					$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
					
					if($this->groups_model->get_provider_group_id()=='13' && strpos($url,'backend.nfstage.com'))
					{
					?>
                    <style type="text/css">
                    .admin-nav li
					{
						background: none !important;
						padding:14px !important;
						font-size:13px !important;
					}
					.admin-nav li a
					{
						float:left !important;
					}
                    </style>
                    
                   <ul class="admin-nav" >
                   		<li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/homen.png" style="float:left;">
                        <a href="http://www.nfstage.com/"> Main Site </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/profilen.png" style="float:left;">
                        <a href="http://www.nfstage.com/login?action=profile"> Profile </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/clipn.png" style="float:left;">
                        <a href="<?= $lang ?>/cliplog/view.html"> ClipLog </a> 
                        </li>
                        <li> <img width="16" height="16" alt="" src="data/img/admin/menu/uploadn.png" style="float:left;">
                        <a href="<?= $lang ?>/clips/upload.html"> Upload Clips </a> 
                        </li>
                        <li> <img width="16" height="16" alt="" src="data/img/admin/menu/statsn.png" style="float:left;">
                        <a href="<?= $lang ?>/clips/statistics/all.html"> View Clip Statistics </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/helpn.png" style="float:left;">
                        <a href="https://contributors.naturefootage.com/portal/kb" target="_blank"> Contributor Help </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/logoutn.png" style="float:left;">
                        <a href="<?= $lang ?>/login/index/logout"><?= $this->lang->line('logout') ?></a> 
                        </li>
                   </ul>
              	   <?php } ?>
                   <?php 
					
					if($this->groups_model->get_provider_group_id()=='13' && strpos($url,'backend.naturefootage.com'))
					{
					?>
                    <style type="text/css">
                    .admin-nav li
					{
						background: none !important;
						padding:14px !important;
						font-size:13px !important;
					}
					.admin-nav li a
					{
						float:left !important;
					}
                    </style>
                    
                   <ul class="admin-nav" >
                   		<li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/homen.png" style="float:left;">
                        <a href="http://www.naturefootage.com/"> Main Site </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/profilen.png" style="float:left;">
                        <a href="http://www.naturefootage.com/login?action=profile"> Profile </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/clipn.png" style="float:left;">
                        <a href="<?= $lang ?>/cliplog/view.html"> ClipLog </a> 
                        </li>
                        <li> <img width="16" height="16" alt="" src="data/img/admin/menu/uploadn.png" style="float:left;">
                        <a href="<?= $lang ?>/clips/upload.html"> Upload Clips </a> 
                        </li>
                        <li> <img width="16" height="16" alt="" src="data/img/admin/menu/statsn.png" style="float:left;">
                        <a href="<?= $lang ?>/clips/statistics/all.html"> View Clip Statistics </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/helpn.png" style="float:left;">
                        <a href="https://contributors.naturefootage.com/portal/kb" target="_blank"> Contributor Help </a> 
                        </li>
                        <li> 
                        <img width="16" height="16" alt="" src="data/img/admin/menu/logoutn.png" style="float:left;">
                        <a href="<?= $lang ?>/login/index/logout"><?= $this->lang->line('logout') ?></a> 
                        </li>
                      
                   </ul>
              		<?php } ?>
                     
                <div class="clearfix"></div>
                </div>
            <?php } ?>
            <? if ($title) {?>
            <h1 class="admin-title <?= ($title_class) ? $title_class : ''; ?>"><?= $title ?></h1>
            <?}?>
            <? if ($path && (!$is_provider && !isset($_REQUEST['modal'])) ) {?>
            <ul class="breadcrumb">
                <li><img src="data/img/admin/home_icon.jpg" alt=""> <?= $path ?></li>
            </ul>
            <?}?>

            <?if ($pagination) {?>
            <div class="pagination">
                <?= $pagination ?>
            </div>
            <?}?>
            <?= $content ?>
            <?if ($pagination && !($hide_bottom_pagination)) {?>
            <div class="pagination">
                <?= $pagination ?>
            </div>
            <?}?>
        </div>
        <script type="text/javascript">
            // Фикс для body > oferflow:hidden, если во фрейме
            var isFramed = false;
            try {
                isFramed = window != window.top || document != top.document || self.location != top.location;
            } catch (e) {
                isFramed = true;
            }
            /*var cliplogView=location.pathname.match('cliplog\/view');
             var cliplogEdit=location.pathname.match('cliplog\/edit');*/
            var cliplog = location.pathname.match('cliplog');
            if (isFramed) {
                if (cliplog == null) {
                    $('body#admin').css('overflow', 'hidden');
                    //console.log( 'Framed body!' );
                } else {
                    $('.admin-title').addClass('frontend');
                    $('.cliplog-filter-cont').addClass('frontend');
                    $('.clips-list').addClass('frontend');
                }
            }
        </script>
    <style>
        #header {
            width: 100%;
            height: 3rem;
            top: 0px;
            left: 0px;
            background-repeat:no-repeat;
            background-size:cover;
            background-position:center center;
            /*            background-image: url("data/img/nf_header_background.jpg");*/
        }
        #header .logo{
            width: 30%;
            float:left;
            padding:10px;
        }
        #header .logo img {
            height: 28px;
        }
    </style>
</body>
</html>
