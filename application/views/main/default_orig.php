<!DOCTYPE html>
<html lang="<?=$lang?>">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?=$meta['title']?>: <?=$this->config->item('title')?></title>
<?if ($meta['desc']) {?>
    <meta name="description" content="<?=$meta['desc']?>">
<?} if ($meta['keys']) {?>
    <meta name="keywords" content="<?=$meta['keys']?>">
<?}?>
    <base href="<?=$this->config->item('base_url')?>">
    <? $release_date = $this->config->item('release_date') ?>
    <link rel="stylesheet" type="text/css" href="data/css/style.css?<?=$release_date?>" media="screen" />
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
    <script type="text/javascript" src="data/js/jquery-ui.js"></script>
    <script type="text/javascript" src="data/js/AC_QuickTime.js"></script>
    <script type="text/javascript" src="data/js/swfobject.js"></script>
    <script type="text/javascript" src="data/js/script.js?<?=$release_date?>"></script>
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
    <div id="topWrapper">
      <div id="top">
        <?=$top?>
      </div>
    </div>

    <div id="headerWrapper">
      <div id="sheader">

        <div id="searchForm">
          <a href="<?=$this->config->item('base_url')?>"><img src="data/img/logo.gif" alt="Event1Images" /></a>
          <form method="post" action="<?=$lang?>/search/words.html">
            <input id="phrase" type="text" value="<?=htmlspecialchars(urldecode($phrase), ENT_QUOTES, 'UTF-8')?>" name="phrase">
            <input type="image" src="data/img/search.gif" align="absmiddle">
          </form>
        </div>

        <div id="userActions">
          <?$this->load->view('main/ext/auth')?>
        </div>
        <br class="clear" />
      </div>
    </div>

    <div id="content">
      <?=$content?>
    </div>

    <div id="footer">
      Copyright &copy; <?=date('Y')?> <?=$this->config->item('vendor_name')?>
    </div>

<?if($visual_mode) {?>
    <div id="dialog" style="display: none">
      <iframe id="dlg_frame" style="border: 0px;" width="99%" height = "99%" >
    </div>
<?}?>

  <?$this->load->view('main/ext/login')?>

  <div id="notify">
    <div id="notifyText">Example of notification</div>
    <div id="notifyClose" onclick="hideNotify()">×</div>
  </div>
  
  </body>
  <!-- Page rendered in {elapsed_time} seconds, {memory_usage} of memory used -->
</html>
