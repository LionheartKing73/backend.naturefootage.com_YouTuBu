<?if($mode=='admin'):?>
  <b><?=$this->lang->line('avail_langs');?>:</b>
<?endif;?>

<? foreach ($lang_list as $k=>$v){?>
  <? if($v['url']) {?>
    <a href="<?=$v['url']?>"><img src="data/img/<?=$v['name']?>_flag.jpg" alt=""></a>
  <?} else {?>
    <img src="data/img/<?=$v['name']?>_flag_active.jpg" alt="">
  <?}?>
<?}?>