<!DOCTYPE html>
<html lang="<?=$lang?>" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-language" content="<?=$lang?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?=$meta['title']?> : <?=$this->config->item('title')?></title>
    <?if ($meta['desc']) {?>
    <meta name="description" content="<?=$meta['desc']?>">
    <?} if ($meta['keys']) {?>
    <meta name="keywords" content="<?=$meta['keys']?>">
    <?}?>
    <base href="<?=$this->config->item('base_url')?>">
    <? $release_date = $this->config->item('release_date') ?>
    <link rel="stylesheet" type="text/css" href="data/css/style.css?<?=$release_date?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="data/css/typography.css?<?=$release_date?>" />
    <link rel="stylesheet" type="text/css" href="data/css/forms.css?<?=$release_date?>" />
    <link rel="stylesheet" type="text/css" href="data/css/jquery-ui.css" media="screen" />
    <?
    if($add_css){
        if(is_array($add_css)){
            foreach($add_css as $css){
                ?>
                <link href="<?=$css.'?'.$release_date?>" type="text/css" rel="stylesheet">
                <?
            }
        }else{
            ?>
            <link href="<?=$add_css.'?'.$release_date?>" type="text/css" rel="stylesheet">
            <?
        }
    }
    if($visual_mode){?>
        <link rel="stylesheet" type="text/css" href="data/css/visual.css" media="screen" />
        <?}?>
    <script type="text/javascript" src="data/js/jquery.js"></script>
    <script type="text/javascript" src="data/js/jquery.i18n.properties-min-1.0.9.js"></script>
    <script type="text/javascript" src="data/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="data/js/AC_QuickTime.js"></script>
    <script type="text/javascript" src="data/js/swfobject.js"></script>
    <script type="text/javascript" src="data/js/script.js?<?=$release_date?>"></script>
    <!--[if IE]>
    <script type="text/javascript" src="data/js/ie.js"></script>
    <![endif]-->
    <?
    if($add_js){
        if(is_array($add_js)){
            foreach($add_js as $js){
                ?>
                <script src="<?=$js.'?'.$release_date?>" type="text/javascript"></script>
                <?
            }
        }else{
            ?>
            <script src="<?=$add_js.'?'.$release_date?>" type="text/javascript"></script>
            <?
        }
    }
    ?>
    <?if($visual_mode){?>
    <script type="text/javascript" src="data/js/visual.js"></script>
    <?}?>
</head>
<body>
<div id="header_container">
    <header id="site_header">
        <div id="logo">
            <a href="<?=$this->config->item('base_url'); if($lang != 'en'){echo $lang . '/index.html';}?>"><img src="data/img/logo.png" alt="<?=$this->config->item('title')?>"></a>
        </div>
        <div id="auth_info"><?=$auth?></div>
        <div id="top_nav_cont">
            <?=$top?>
        </div>
        <br class="clear_right">
        <div id="cart_info">
            <a href="<?=$lang?>/bin.html" target="_top"><?=$this->lang->line('my_clips')?></a>
            <span id="binCount"><?=$bin_count?></span>
            <a href="<?=$lang?>/cart.html" target="_top"><?=$this->lang->line('my_cart')?></a>
            <span id="cartCount"><?=$cart_count?></span>
        </div>
        <br class="clear_right">
        <div id="search_form">
            <form action="<?=$lang?>/search/words" method="post" onsubmit="return checkPhrase();">
                <input type="text" class="text" name="phrase" id="phrase" value="<? if($phrase){echo htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8');}else{echo $this->lang->line('type_some_words');}?>"
                       onclick="if(this.value == '<?=$this->lang->line('type_some_words')?>'){this.value='';};"
                       onblur="if(this.value == ''){this.value = '<?=$this->lang->line('type_some_words')?>';};">
            </form>
        </div>
        <div id="browse_nav_cont">
            <nav id="browse_nav">
                <ul>
                    <li><a href="<?=$lang?>/search.html">Browse Content</a></li>
                    <li><a href="<?=$lang?>/categories.html"">Browse Categories</a></li>
                </ul>
            </nav>
        </div>
        <div class="clear"></div>
    </header>
</div>

<div id="container">
    <div id="main_banner">
        <?=$main_banner?>
    </div>

    <h1 id="slogan">
        Responsive. Customizable. Easy to Use.
    </h1>

    <div id="home_wrapper">
        <section id="content">
            <?=$content?>
        </section>
    </div>

    <?=$features?>

    <?=$cats_nav?>

    <div class="clear"></div>

    <footer id="site_footer">
        <div id="copyright">
            Copyright &copy; <?=date('Y')?> <?=$this->config->item('vendor_name')?>. <?=$this->lang->line('all_rights_reserved')?>.
        </div>
        <?=$bottom?>
        <div class="clear"></div>
    </footer>

</div>


<?$this->load->view('main/ext/login')?>

<?if($visual_mode) {?>
    <div id="dialog" style="display: none">
        <iframe id="dlg_frame" style="border: 0px;" width="99%" height = "99%" >
    </div>
<?}?>

<script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-36331438-1']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

</script>

</body>
</html>