<!DOCTYPE html>
<html lang="<?=$lang?>">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Home: <?=$this->config->item('title')?></title>
    <base href="<?=$this->config->item('base_url')?>" />
    <link rel="stylesheet" type="text/css" href="data/css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="data/css/index.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="data/css/jquery-ui.css" media="screen" />
    <script type="text/javascript" src="data/js/jquery.js"></script>
    <script type="text/javascript" src="data/js/jquery-ui.js"></script>
    <script type="text/javascript" src="data/js/script.js"></script>
  </head>
  <body>
    <div id="freeClipPopup">
      <p>
        &#9658; Event 1 Images Latest FREE clip of the week in Cinema quality 4K or Broadcast HD
      </p>      
      <div id="freeClipContainer"></div>      
      <p>
        The free clip will be changed regulary so make sure you create an account.
      </p>
      <p>
        The free clip is subject to usage and distribution restrictions please see user agreement.
      </p>
    </div>

    <div id="topWrapper">
      <div id="top">
        <a href="<?=$lang?>/about.html">About Us</a>
      </div>
    </div>
    
    <div id="headerWrapper">
      <div id="header">
        <div id="searchForm">
          <a href="<?=$this->config->item('base_url')?>"><img src="data/img/logo-home.gif" alt="" /></a>
          <form method="post" action="<?=$lang?>/search/words.html">
            <input id="phrase" type="text" value="<?=htmlspecialchars(urldecode($phrase), ENT_QUOTES, 'UTF-8')?>" name="phrase">
            <input type="image" src="data/img/search-home.gif" align="absmiddle">
          </form>
          STUNNING FOOTAGE IN ANY FORMAT: 5K / 4K / 2K / HD / SD
        </div>
      </div>
    </div>
    
    <div id="content">
      <?=$content?>
      <img src="data/img/boxes-bottom.gif" alt="" align="top" />

      <?if(!$this->session->userdata('client_uid')){?>
      <div id="buttons">
        <button class="button" onclick="loginPopup()">Login</button>
        <a href="<?=$lang?>/register.html" class="button" style="float: right">Register</a>
        <?if($error){?>
        <div class="err"><?=$error?></div>
        <?}?>
      </div>
      <?$this->load->view('main/ext/login');}?>
    </div>
    
    <div id="footer">
      Copyright &copy; <?=date('Y')?> <?=$this->config->item('vendor_name')?>
    </div>

  </body>
  <!-- Page rendered in {elapsed_time} seconds, {memory_usage} of memory used -->
</html>
